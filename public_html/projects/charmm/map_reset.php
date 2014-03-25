<?php
// "resetting map information in divided_run table" script

//set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$ru_id = 0;
if(isset($_GET['run']) === true) { $ru_id = intval($_GET['run']); }

$flagReset = false;
if(isset($_GET['reset']) === true ) { 
  $flagReset = true;
}

$sql = 'SELECT * FROM divided_run WHERE di_ru_id ='.$ru_id.'ORDER BY di_id ASC';
$diRow = $db->fetchAll($sql);
//print_r($diRow);
//$sql = 'SELECT COUNT(*) FROM divided_run WHERE di_min = \'\' and di_ru_id = '.$ru_id;
//$chkRow = $db->fetchRow($sql);

if($diRow == false) {
	COMRedirect('/run.php?run='.$ru_id);
  exit();
}
if($flagReset === true) {
  for($i = 0; $i < count($diRow); $i++) {
    $where = $db->quoteInto('di_id = ?', $diRow[$i]['di_id']);
    $update = array('di_min' => '');
    //echo $i."===".$where."<br />\n";
    $db->update('divided_run', $update, $where);
  }
	COMRedirect('/run.php?run='.$ru_id);
}
