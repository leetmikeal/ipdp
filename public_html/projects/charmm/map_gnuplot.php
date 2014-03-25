<?php
//set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// map common library
include_once('map.php');

// DB接続
$db = DbManager::getConnection();

$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}

$type = "";
if(isset($_GET['type']) === true) {
  $type = htmlentities($_GET['type']);
}

$sql = 'SELECT ru_id, ru_file_name FROM run WHERE ru_id = '.$ru_id;
$ruRow = $db->fetchRow($sql);
$ma_file_name = $ru_id."_map_".$ruRow['ru_file_name'];

if($showRun === false) {
	COMRedirect(SITE_URL);
} else {

  // maximum and minimum
  $di_max = 10000;
  $di_min = -10000;

  $format = 'png';
  if(!in_array($type, array('surface', 'contour'))) {
    $type = "surface";
  }

  $script = getGnuplotScript(
    array(
      'dirpath' => '',
      'filename' => $ma_file_name,
      'format' => 'png',
      'title' => $ruRow['ru_file_name'],
      'range' => 90,
      'spline' => 5,
      'zrange_max' => $di_max,
      'zrange_min' => $di_min
    ),
    $type
  );

	header('Content-Disposition: attachment; filename="'.$ruRow['ru_file_name'].'.gnuplot"');
	header('Content-Type: text/plain');
	echo($script);

}
