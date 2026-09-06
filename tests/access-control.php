<?php
declare(strict_types=1);
require __DIR__.'/../public/dashboard/_common.php';
function check(bool $v,string $label):void {if(!$v){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
foreach(shan_role_presets() as $role=>$preset){
    $u=['role'=>$role,'permissions'=>json_encode($preset['grants']),'active'=>1,'must_change_password'=>0];
    foreach(array_keys(shan_permission_catalog()) as $p){check(shan_user_can($u,$p)===(shan_is_admin($u)||in_array($p,$preset['grants'],true)),$role.' '.$p);}
    check(!shan_user_can($u,'unknown.permission'),'deny unknown permission');
    $u['active']=0;check(!shan_user_can($u,'messages'),'disabled denied');
    $u['active']=1;$u['must_change_password']=1;check(!shan_user_can($u,'messages'),'temporary password gated');
}
$u=['role'=>'custom','permissions'=>'["job.edit","users.manage"]','active'=>1];check(!shan_user_can($u,'job.edit'),'actions require view');check(!shan_user_can($u,'users.manage'),'staff cannot grant administration');
$u['permissions']='invalid';check(!shan_user_can($u,'messages'),'invalid grants deny');
check(!shan_password_valid('short') && shan_password_valid(str_repeat('a',12)) && !shan_password_valid(str_repeat('a',73)) && !shan_password_valid("invalid\0password"),'password bounds');
$pdo=new PDO('sqlite::memory:');$pdo->exec('CREATE TABLE shan_submissions (id INTEGER,form_type TEXT,workflow_status TEXT);CREATE TABLE shan_submission_trash (submission_id INTEGER);INSERT INTO shan_submissions VALUES(1,"job","new"),(2,"business","new"),(3,"job","new");INSERT INTO shan_submission_trash VALUES(3);');
foreach([[['job'],false,1],[['business'],false,1],[[],false,0],[['job'],true,1],[['job','business'],false,2]] as [$scope,$trash,$expected]){[$sql,$params]=dashboard_query(dashboard_filters([]),$scope,$trash);$q=$pdo->prepare('SELECT COUNT(*) FROM shan_submissions'.$sql);$q->execute($params);check((int)$q->fetchColumn()===$expected,'SQL scope and trash '.json_encode([$scope,$trash]));}
