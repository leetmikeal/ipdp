<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$showMode = isset($_GET['filename']);


$in_file_names = array();
$in_file_name = '';
$in_contents = '';

if($showMode === false) {
    COMRedirect('/');
} else {
    $in_file_name = $_GET['filename'];

    $temp_name = tempnam("data_files", "df");
    $df_handle = fopen($temp_name, "w");
    
    $sql = 'SELECT in_contents FROM input_file WHERE in_file_name = \''.$in_file_name.'\'';
    $inRow = $db->fetchAll($sql);
    $in_buf = $inRow[0]['in_contents'];
    $in_buf = trim($in_buf);
    $in_buf = trim($in_buf, '\r\n');
    if($in_buf !== "") fwrite($df_handle, $in_buf."\n");
    fclose($df_handle);
}

$file = $temp_name;
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$in_file_name);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit();
}
unlink($df_handle);

