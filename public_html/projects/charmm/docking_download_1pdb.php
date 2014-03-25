<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// get parameter settings
$isRunId = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $isRunId = true;
    $ru_id = intval($_GET['run']);
}
$fileName = "";
if(isset($_GET['name']) === true) {
    $fileName = $_GET['name'];
}

// initial parameter settings


if($isRunId === false || $fileName === "") {
	COMRedirect('/');
} else {
    $content = getFileContents($db, $ru_id, $fileName);

    if($content) {

    $fileHandler = fopen($fileName, "w");
    fwrite($fileHandler, $content);
    fclose($fileHandler);


    $isDownload = downloadFile(DIR_DATA, $fileName);
    if($isDownload) {
        echo "Error";
    }
        
    } else {
        echo "Error ! File '".$fileName."' is not found !\n";
    }

}


function getFileContents($db, $ru_id,  $filename) {
    $sql = "SELECT ou_contents FROM output_file WHERE ou_ru_id = ".$ru_id." AND ou_file_name = '".$filename."'";
    //echo $sql."\n"; // debug
    $ouContentRow = $db->fetchRow($sql);
    //echo $ouContentRow['ou_contents']; // debug
    return $ouContentRow['ou_contents'];
}

function downloadFile($dir, $file) {
    $path = $dir."/".$file;
    if (file_exists($file)) {
      $handle = fopen($file, "r");
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.$file);
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      while(!feof($handle)) {
        $bytes = fread($handle, 1000*1024);
        echo $bytes;
        flush();
	//usleep(100000); // 100 ms
        //sleep(1); // argument type only allow int
      }
      return true; 
    } else {
      return false;
    }
}

