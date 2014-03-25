<?php
// "docking data" script
// This script can make you download a/some file.
// If file name was set, this file can be downloaded.
// Without setting file name, You can get list of unique filenames.

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// setting initial paramter
$fileRequ = isset($_GET['filename']);
$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}

$ruRow = getRunInfo($db, $ru_id);
$ouRow = array();

$isSetCoorLeast = false;
$isSetEnough = false;
if(isset($_GET['rot1']) 
    || isset($_GET['rot2']) 
    || isset($_GET['rot3']) 
    || isset($_GET['x']) 
    || isset($_GET['y']) 
    || isset($_GET['z'])) {
    $isSetCoorLeast = true; 
}
if(isset($_GET['rot1']) 
    && isset($_GET['rot2']) 
    && isset($_GET['rot3']) 
    && isset($_GET['x']) 
    && isset($_GET['y']) 
    && isset($_GET['z'])) {
    $isSetEnough = true;
}

$anlzMode = null;
if(isset($_GET['m'])) { $anlzMode = intval($_GET['m']); }

if(($isSetCoorLeast && !$isSetEnough)) {
  $redirectPath = $_SERVER['PHP_SELF'].'?run='.$ru_id;
  $redirectPath .= isset($anlzMode) ? '&m='.$anlzMode : '';
  COMRedirect($_SERVER['PHP_SELF'].'?run='.$ru_id);
	exit();
}

$ou_file_names = array();
$ou_file_name = '';
$ou_contents = '';

if($showRun === false) {
  COMRedirect('/');
} else {
  if($fileRequ === false) {
    $sql = 'SELECT DISTINCT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id;
    $ouRow = $db->fetchAll($sql);

    for($i = 0; $i < count($ouRow); $i++) {
      $ou_file_names[] = $ouRow[$i]['ou_file_name'];
    }
  } else {
    switch($anlzMode)
    {
    case 0:
        break;
    default:
    }
    $ou_file_name = $_GET['filename'];

    $sql = 'SELECT di_id FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id ASC';
    $di_id = $db->fetchAll($sql);

    $temp_name = tempnam("data_files", "df");
    $df_handle = fopen($temp_name, "w");
    foreach($di_id as $value) {
      $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' AND ou_di_id = '.$value['di_id'];
      $ouRow = $db->fetchAll($sql);
      $ou_buf = $ouRow[0]['ou_contents'];
      $ou_buf = trim($ou_buf);
      $ou_buf = trim($ou_buf, '\r\n');
      if($ou_buf !== "") fwrite($df_handle, $ou_buf."\n");
    }
    fclose($df_handle);

    $ou_file_name = $_GET['filename'];
  
    $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\'';
    $ouRow = $db->fetchAll($sql);

    //var_dump($ouRow);
    for($i = 0; $i < count($ouRow); $i++) {
        $ou_contents .= $ouRow[$i]['ou_contents'];
    }

  }
  $smarty = new Smarty();
  $smarty->left_delimiter = '{{{';
  $smarty->right_delimiter = '}}}';
  $smarty->assign('showMode',$fileRequ);
  $smarty->assign('ru_id', $ru_id);
	$smarty->assign('ruRow', $ruRow);
  $smarty->assign('ou_contents', $ou_contents);
  $smarty->assign('ou_file_name', $ou_file_name);
  $smarty->assign('ou_file_names', $ou_file_names);
  $smarty->display('docking_data.html');
}

