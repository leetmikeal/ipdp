<?php
set_time_limit(180);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$isFilename = isset($_GET['filename']);
$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
  $showRun = true;
  $ru_id = intval($_GET['run']);
}
$fileType = '';
$fileTypeFlag = false;
if(isset($_GET['type'])) {
  $fileType = addslashes($_GET['type']);
  $fileTypeFlag = true;
}

$ruRow = getRunInfo($db, $ru_id);
$ouRow = array();

$ou_file_names = array();
$ou_file_name = '';
$ou_contents = '';
$fileExtArray = array();
$fileExtArrayUni = array();

if($showRun === false) {
  COMRedirect('../');
} else {
  if($isFilename === false) {
    $sql = 'SELECT DISTINCT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id;
    $ouRow = $db->fetchAll($sql);

    for($i = 0; $i < count($ouRow); $i++) {
      $fileExt = pathinfo($ouRow[$i]['ou_file_name'], PATHINFO_EXTENSION);
      $fileExtArray[] = $fileExt;
      if($fileTypeFlag === true) {
        if($fileExt == $fileType) {
          $ou_file_names[] = $ouRow[$i]['ou_file_name'];
        }
      } elseif($fileTypeFlag === false) {
        $ou_file_names[] = $ouRow[$i]['ou_file_name'];
      }
    }
    $fileExtArrayUni = array_unique($fileExtArray);
  } else {
    $ou_file_name = $_GET['filename'];
  
    $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\'';
    $ouRow = $db->fetchAll($sql);

    for($i = 0; $i < count($ouRow); $i++) {
      $ou_contents .= $ouRow[$i]['ou_contents'];
    }
  }
  $smarty = new Smarty();
  $smarty->left_delimiter = '{{{';
  $smarty->right_delimiter = '}}}';
  $smarty->assign('isFilename',$isFilename);
  $smarty->assign('ru_id', $ru_id);
	$smarty->assign('ruRow', $ruRow);
  $smarty->assign('ou_contents', $ou_contents);
  $smarty->assign('fileTypeFlag', $fileTypeFlag);
  $smarty->assign('ou_file_name', $ou_file_name);
  $smarty->assign('ou_file_names', $ou_file_names);
  $smarty->assign('fileExtArray', $fileExtArrayUni);
  $smarty->display('docking_pdb.html');
}

