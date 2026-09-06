// Integration tests: requires isolated cPanel staging, never the production database.
import fs from 'node:fs';
import assert from 'node:assert/strict';
import {randomBytes} from 'node:crypto';
const config=JSON.parse(fs.readFileSync(process.env.SHAN_QA_CONFIG || '/private/tmp/shan-rbac-qa.json'));
const base=config.base;
assert.match(base,/\/dashboard\/qa-rbac-[a-z0-9]+\/dashboard\/$/);
const job='11111111-1111-4111-8111-111111111111', business='22222222-2222-4222-8222-222222222222';
const token=(page,name='csrf')=>page.text.match(new RegExp(`name="${name}" value="([^"]*)"`))?.[1];
const password=()=>randomBytes(18).toString('base64url');
class Client{
  cookie='';
  async request(path='',body){
    const data=body?new URLSearchParams():null;
    if(body)for(const [key,value] of Object.entries(body)){if(Array.isArray(value)){value.forEach(v=>data.append(key,v));}else data.set(key,String(value));}
    const r=await fetch(base+path,{method:body?'POST':'GET',redirect:'manual',headers:{Cookie:this.cookie,...(body?{'Content-Type':'application/x-www-form-urlencoded'}:{})},body:data});
    if(r.headers.getSetCookie().length)this.cookie=r.headers.getSetCookie().map(x=>x.split(';')[0]).join('; ');
    return {status:r.status,text:await r.text(),location:r.headers.get('location'),headers:r.headers};
  }
  async login(email,pass){const p=await this.request();return this.request('',{csrf:token(p),action:'login',username:email,password:pass});}
  async change(old,next){const p=await this.request('account.php');return this.request('account.php',{csrf:token(p),current_password:old,new_password:next,confirm_password:next});}
}
const admin=new Client();assert.equal((await admin.login(config.email,config.password)).status,302);
const anon=new Client();
for(const path of ['users.php','messages.php','audit.php','account.php','trash.php','edit.php?id='+job,'review.php?id='+job,'export.php','download.php?id='+job])assert.equal((await anon.request(path)).status,302,path+' anonymous');
assert.equal((await admin.request('users.php',{csrf:'bad'})).status,403);
const suffix=Date.now().toString(36);
async function create(label,role,grants){
  const temp=password();const email=`${label}-${suffix}@example.invalid`;
  const form=await admin.request('users.php');
  const result=await admin.request('users.php',{csrf:token(form),id:0,display_name:'QA '+label,email,role,'permissions[]':grants,active:1,new_password:temp,confirm_password:temp,admin_password:config.password});
  assert.equal(result.status,303,result.text.slice(-600));
  const id=Number(result.location.match(/id=(\d+)/)[1]);const client=new Client();assert.equal((await client.login(email,temp)).status,302);
  assert.match((await client.request()).location,/account.php/,'forced temporary password');
  assert.match((await client.request('export.php')).location,/account.php/);
  const permanent=password();assert.equal((await client.change(temp,permanent)).status,303);
  return {client,id,email,password:permanent,label,role,grants};
}
const hr=await create('HR','hr',['job.view','job.edit','job.status','job.cv','job.export','job.delete','messages']);
const reception=await create('Reception','receptionist',['business.view','business.status','messages']);
const viewer=await create('Viewer','custom',['job.view','messages']);
const sub=await create('Subadmin','sub_admin',[]);
const jobPage=await hr.client.request();assert.match(jobPage.text,/job@example.invalid/);assert.doesNotMatch(jobPage.text,/business@example.invalid|Google Sheet ↗|Users &amp; access/);
assert.doesNotMatch((await hr.client.request('?q=business&type=business')).text,/business@example.invalid/);
assert.equal((await hr.client.request('review.php?id='+business)).status,404);
assert.equal((await reception.client.request('review.php?id='+job)).status,404);
for(const path of ['users.php','audit.php'])assert.equal((await hr.client.request(path)).status,403);
assert.equal((await hr.client.request('',{csrf:token(jobPage),action:'sync'})).status,403);
assert.equal((await reception.client.request('export.php')).status,403);
assert.equal((await viewer.client.request('download.php?id='+job)).status,403);
assert.equal((await hr.client.request('download.php?id='+job)).text,'QA CV fixture');
const csv=await hr.client.request('export.php');assert.match(csv.text,/job@example.invalid/);assert.doesNotMatch(csv.text,/business@example.invalid/);
assert.equal((await viewer.client.request('edit.php?id='+job)).status,403);
let p=await viewer.client.request('review.php?id='+job);assert.equal((await viewer.client.request('review.php?id='+job,{csrf:token(p),workflow_status:'closed',revision:token(p,'revision')})).status,403);
p=await reception.client.request('review.php?id='+business);assert.equal((await reception.client.request('review.php?id='+business,{csrf:token(p),workflow_status:'contacted',admin_notes:'forbidden',revision:token(p,'revision')})).status,403);
assert.equal((await reception.client.request('review.php?id='+business,{csrf:token(p),workflow_status:'contacted',revision:token(p,'revision')})).status,303);
p=await hr.client.request('edit.php?id='+job);const edit={csrf:token(p),revision:token(p,'revision'),full_name:'QA job edited '+suffix,email:'job@example.invalid',phone:'0123456789',role_name:'QA role',experience:'QA',availability:'QA',message:'QA fixture edited'};
assert.equal((await hr.client.request('edit.php?id='+job,edit)).status,303);
assert.equal((await hr.client.request('edit.php?id='+job,edit)).status,422,'stale edit blocked');
p=await hr.client.request('review.php?id='+job);assert.equal((await hr.client.request('trash.php',{csrf:token(p),id:job,action:'trash'})).status,303);
assert.equal((await hr.client.request('review.php?id='+job)).status,404);assert.equal((await hr.client.request('download.php?id='+job)).status,404);assert.doesNotMatch((await hr.client.request('export.php')).text,/job@example.invalid/);
p=await hr.client.request('trash.php');assert.match(p.text,/job@example.invalid/);assert.equal((await hr.client.request('trash.php',{csrf:token(p),id:job,action:'restore'})).status,303);
assert.equal((await hr.client.request('review.php?id='+job)).status,200);
console.log('PASS section scoping, forged URLs, exports, CVs, edits, status-only access, conflicts, Trash and restore');
let msg=await admin.request('messages.php?user='+hr.id);const nonce=token(msg,'nonce');const body={csrf:token(msg),nonce,message:'QA private thread & "quoted"'};
let sent=await admin.request('messages.php?user='+hr.id,body);assert.equal(sent.status,303,sent.text.slice(0,600));assert.equal((await admin.request('messages.php?user='+hr.id,body)).status,303);
msg=await hr.client.request('messages.php?user=1');assert.match(msg.text,/QA private thread &amp; &quot;quoted&quot;/);assert.equal((msg.text.match(/QA private thread/g)||[]).length,1,'duplicate message not duplicated');
assert.doesNotMatch((await viewer.client.request('messages.php?user='+hr.id+'&sender_id=1')).text,/QA private thread/);
assert.equal((await hr.client.request('messages.php?user=1',{csrf:token(msg),nonce:token(msg,'nonce'),message:'QA reply'})).status,303);
assert.match((await admin.request('messages.php?user='+hr.id)).text,/QA reply/);
console.log('PASS private messages, replies, escaped content, participant-only reads and idempotent sends');
const other=new Client();assert.equal((await other.login(viewer.email,viewer.password)).status,302);
const updated=password();assert.equal((await viewer.client.change(viewer.password,updated)).status,303);viewer.password=updated;
assert.match((await other.request('review.php?id='+job)).location,/\/dashboard\/$/,'password revokes old session');
const own=await admin.request('users.php?id=1');const self={csrf:token(own),id:1,version:token(own,'version'),display_name:'QA Admin',email:config.email,role:'custom','permissions[]':['messages'],admin_password:config.password};
assert.equal((await admin.request('users.php?id=1',self)).status,422,'cannot disable/demote self');
async function update(user,extra){const form=await admin.request('users.php?id='+user.id);return admin.request('users.php?id='+user.id,{csrf:token(form),id:user.id,version:token(form,'version'),display_name:'QA '+user.label,email:user.email,role:user.role,'permissions[]':user.grants,active:1,admin_password:config.password,...extra});}
const reset=password();assert.equal((await update(hr,{new_password:reset,confirm_password:reset})).status,303);assert.equal((await hr.client.request('review.php?id='+job)).status,302);
assert.equal((await new Client().login(hr.email,hr.password)).status,401);assert.equal((await hr.client.login(hr.email,reset)).status,302);assert.match((await hr.client.request()).location,/account.php/);
const finalPass=password();assert.equal((await hr.client.change(reset,finalPass)).status,303);hr.password=finalPass;
assert.equal((await update(hr,{'permissions[]':['business.view','messages']})).status,303);assert.equal((await hr.client.request('review.php?id='+job)).status,302);
assert.equal((await hr.client.login(hr.email,hr.password)).status,302);assert.equal((await hr.client.request('review.php?id='+job)).status,404);assert.equal((await hr.client.request('review.php?id='+business)).status,200);
assert.equal((await sub.client.request('users.php')).status,200,'same-level subadmin');
// Disable a QA account and verify sign-in rejection; fixture accounts remain isolated.
const form=await admin.request('users.php?id='+sub.id);assert.equal((await admin.request('users.php?id='+sub.id,{csrf:token(form),id:sub.id,version:token(form,'version'),display_name:'QA Subadmin',email:sub.email,role:'sub_admin',admin_password:config.password})).status,303);
assert.equal((await sub.client.request()).status,200);assert.match((await sub.client.request()).text,/Welcome back/);assert.equal((await new Client().login(sub.email,sub.password)).status,401);
fs.writeFileSync('/private/tmp/shan-rbac-test-users.json',JSON.stringify({hr,reception,viewer},(key,val)=>key==='client'?undefined:val),{mode:0o600});
console.log('PASS password changes, reset gating, revoked sessions, access changes, subadmin access and disabled accounts');
