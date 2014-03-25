<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$showMode = isset($_GET['filename']);
$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}
$ncolumn = 0;
if(isset($_GET['ncol']) === true) $ncolumn = intval($_GET['ncol']);
$ener = 1000;
if(isset($_GET['ener'])) $ener = intval($_GET['ener']);
if(isset($_GET['ft'])) $fileType = $_GET['ft'];

$ou_file_names = array();
$ou_file_name = '';
$ou_contents = '';

if($showRun === false) {
  COMRedirect('/');
} else {
  if($showMode === false) {
    COMRedirect('/');
  } else {
    $ou_file_name = $_GET['filename'];
    if($fileType != "") $down_file_name = ereg_replace("\..+$", ".".$fileType, $ou_file_name);

    $sql = 'SELECT di_id FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id ASC';
    $di_id = $db->fetchAll($sql);

    $temp_name = tempnam("../../data_files", "docking_df_");
    $df_handle = fopen($temp_name, "w");
    foreach($di_id as $value) {
      $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' AND ou_di_id = '.$value['di_id'];
      $ouRow = $db->fetchAll($sql);
      $ou_buf = $ouRow[0]['ou_contents'];
      $ou_buf = trim($ou_buf);
      $ou_buf = trim($ou_buf, '\n');

      $ou_row = explode("\n",$ou_buf);
      for($i = 0; $i < count($ou_row); $i++) {
        $ou_row[$i] = trim($ou_row[$i]);
        $ou_col = split(" +", trim($ou_row[$i]));
        $ou_col_float = floatval($ou_col[$ncolumn]); // "ncolumn" come from GET parameter
        if($ou_col_float < $ener) {
          if($fileType == "csv") {
            $ou_row[$i] = ereg_replace("\x20+", ",", $ou_row[$i]);
          }
          if($ou_buf !== "") fwrite($df_handle, $ou_row[$i]."\n");
        }
      }
    }
    fclose($df_handle);
  }

  $file = $temp_name;
  //echo "<pre>";
  //$fh = fopen($file, 'r');
  //echo fread($fh, filesize($file));
  //fclose($fh);
  //echo "</pre>";
  //exit();
  if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$down_file_name);
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

  unlink($temp_name);
  COMRedirect('docking_download.php?run='.$ru_id);
}

