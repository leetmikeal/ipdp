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
$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}
if(isset($_GET['di']) == true) $di_id = intval($_GET['di']);

$ou_file_name = htmlentities($_GET['filename']);

$ou_contents = '';

if($showRun === false) {
	COMRedirect('/');
} else {
    if($showMode === false) {
	    COMRedirect('/');
    } else {
        $temp_name = tempnam("data_files", "df");
        $df_handle = fopen($temp_name, "w");

        if(!isset($_GET['di'])) {
            $sql = 'SELECT count(*) FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\'';
            $ouCountRow = $db->fetchRow($sql);
            if(intval($ouCountRow['count']) == 0) { // if there is single outfile.
              $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\'';
              $ouRow = $db->fetchRow($sql);
              $ou_buf = $ouRow['ou_contents'];
              $ou_buf = trim($ou_buf);
              if($ou_buf !== "") if($ou_buf !== null) fwrite($df_handle, $ou_buf."\n");
            } elseif(intval($ouCountRow['count']) > 0) { // if there are multiple outfiles.
              $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' order by ou_di_id ASC';
              $ouRow = $db->fetchAll($sql);
              for($i = 0; $i < count($ouRow); $i++) {
                $ou_buf = $ouRow[$i]['ou_contents'];
                $ou_buf = trim($ou_buf);
                $ou_buf = trim($ou_buf, '\r\n');
                if($ou_buf !== "") if($ou_buf !== null) fwrite($df_handle, $ou_buf."\n");
              }
            }
            
            //$sql = 'SELECT di_id FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id ASC';
            //$di_id_ar = $db->fetchAll($sql);
            //foreach($di_id_ar as $value) {
            //    $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' AND ou_di_id = '.$value['di_id'];
            //    $ouRow = $db->fetchAll($sql);
            //    $ou_buf = $ouRow[0]['ou_contents'];
            //    $ou_buf = trim($ou_buf);
            //    $ou_buf = trim($ou_buf, '\r\n');
            //    if($ou_buf !== "") if($ou_buf !== null) fwrite($df_handle, $ou_buf."\n");
            //}
        } else {
            $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' AND ou_di_id = '.$di_id;
            $ouRow = $db->fetchRow($sql);
            $ou_buf = $ouRow['ou_contents'];
            if($ou_buf !== "") fwrite($df_handle, $ou_buf);
        }
        fclose($df_handle);
    }

    $file = $temp_name;
    if (file_exists($file)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.$ou_file_name);
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
}

