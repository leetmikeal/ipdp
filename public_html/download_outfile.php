<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$di_id = 0;
if(isset($_GET['di']) === true) { $di_id = intval($_GET['di']); }
$showRun = false;

if($di_id === 0) {
	COMRedirect('/');
} elseif($di_id !== 0) {
    $sql = 'SELECT * FROM divided_run WHERE di_id = '.$di_id;
    $row = $db->fetchAll($sql);

    $outDirPath = 'out_files';
    $dirPath = 'data_files';
    $num = floor($di_id / 1000) + 1;
    $fileName = 'out_'.$di_id;
    $filePathCom = $outDirPath.'/'.$num.'/'.$fileName;
    $filePathTxt = $dirPath.'/'.$fileName;
    exec('xz -dk '.$filePathCom.'.xz');
    //exec('mv '.$filePathCom.' '.$filePathTxt);

    //if (file_exists($filePathTxt)) {
    if (file_exists($filePathCom)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.$fileName);
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($filePathCom));
      ob_clean();
      flush();
      readfile($filePathCom);
      exit();
    }
    unlink($filePathCom);
}

