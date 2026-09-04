import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';

function integration() {
  const rows = {'Business Inquiries': [], 'Job Applications': []};
  let locks = 0;
  const context = vm.createContext({
    console: {error() {}},
    PropertiesService: {getScriptProperties: () => ({getProperty: key => ({SHAN_WEBHOOK_SECRET:'test-secret',SHAN_SPREADSHEET_ID:'test-sheet'}[key])})},
    LockService: {getScriptLock: () => ({waitLock() { locks++; },releaseLock() { locks--; }})},
    ContentService: {MimeType:{JSON:'json'},createTextOutput: text => ({setMimeType: () => JSON.parse(text)})},
    SpreadsheetApp: {openById: () => ({getSheetByName: name => !rows[name] ? null : ({
      getLastRow: () => rows[name].length,
      appendRow: row => rows[name].push([...row]),
      getRange: (row) => ({
        setValues: values => {rows[name][row-1] = [...values[0]];},
        createTextFinder: id => ({matchEntireCell: () => ({useRegularExpression: () => ({findNext: () => {
          const index=rows[name].findIndex(r=>r[0]===id);
          return index<0 ? null : {getRow:()=>index+1};
        }})})}),
      }),
    })})},
  });
  vm.runInContext(fs.readFileSync(new URL('../deployment/google-apps-script/Code.gs',import.meta.url),'utf8'),context);
  return {rows, post: payload => context.doPost({postData:{contents:JSON.stringify(payload)}}), locks:()=>locks};
}
const record={secret:'test-secret',publicId:'11111111-1111-4111-8111-111111111111',formType:'business',fullName:'Test',workflowStatus:'new',message:'=HYPERLINK("bad")'};
test('unauthorized and malformed requests never write rows',()=>{
  const app=integration();
  assert.equal(app.post({...record,secret:'wrong'}).success,false);
  assert.equal(app.post({...record,publicId:'bad'}).success,false);
  assert.equal(app.rows['Business Inquiries'].length,0);
});
test('retries update one row and preserve formula safety',()=>{
  const app=integration();
  assert.equal(app.post(record).publicId,record.publicId);
  app.post(record);
  app.post({...record,workflowStatus:'contacted'});
  assert.equal(app.rows['Business Inquiries'].length,2);
  assert.equal(app.rows['Business Inquiries'][1][2],'contacted');
  assert.equal(app.rows['Business Inquiries'][1][7],"'"+record.message);
  assert.equal(app.locks(),0);
});
test('jobs go to their own tab; unavailable tabs fail visibly',()=>{
  const app=integration();
  app.post({...record,formType:'job'});
  assert.equal(app.rows['Job Applications'].length,2);
  assert.equal(app.rows['Business Inquiries'].length,0);
  delete app.rows['Job Applications'];
  assert.equal(app.post({...record,formType:'job'}).success,false);
});
