<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

//ini_set('display_errors', 1);

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// setting initial paramter
$limit = 1000;

$mode = '';
if(isset($_GET['m'])) {
  $mode = htmlentities($_GET['m']);
}

$ru_id = 0;
if(isset($_GET['run'])) { 
  $ru_id = intval($_GET['run']);
}

$di_id = 0;
if(isset($_GET['di'])) { 
  $di_id = intval($_GET['di']);
}

$certificate = "";
if(isset($_GET['c'])) { 
  $certificate = htmlentities($_GET['c']);
}

$sql = 'select * FROM run WHERE ru_id = '.$ru_id;
//echo $sql;
$ruRow = $db->fetchRow($sql);

// to create certification code
$uid = getCertNum($db, $ru_id);

//echo $certificate."<br />\n".$uid;
//exit();

// if certification was validated.
switch($mode) {
  case 'run':
    if($certificate == $uid) {
      if($ru_id !== 0) {
    
        // to delete files
        $sql = 'select di_id FROM divided_run WHERE di_ru_id = '.$ru_id;
        $diRow = $db->fetchRow($sql);
    		if(!empty($diRow)) {
    		  $nDividedBegin = $diRow[0]['di_id'];
    		  $nDividedEnd = $diRow[count($diRow)-1]['di_id'];
      
    		  deleteOutputFile($nDevidedBegin, $nDevidedEnd);
    		}
    		
    	  // to delete database record
        $sql = 'delete FROM run WHERE ru_id ='.$ru_id;
        $resultDel = $db->fetchRow($sql);
      
        $sql = 'delete FROM output_file WHERE ou_ru_id ='.$ru_id;
        $resultDel = $db->fetchRow($sql);
      
        $sql = 'delete FROM divided_run WHERE di_ru_id ='.$ru_id;
        $resultDel = $db->fetchRow($sql);
    
        COMRedirect(SITE_URL);
        exit();
      }
    } else {
    
      // if certification was faild, check form show.
      $smarty = new Smarty();
      $smarty->left_delimiter = '{{{';
      $smarty->right_delimiter = '}}}';
      $smarty->assign('cert', $uid);
      $smarty->assign('ruRow', $ruRow);
      $smarty->display(TEMPLATE.'/delete.html');
    }
    break;
  case 'di':
    if($di_id !== 0 && $ru_id !== 0) {
      // to delete files
      deleteOutputFile($di_id, $di_id);
    	
      // to reset database record
      $sql = 'delete FROM output_file WHERE ou_di_id = '.$di_id.' and  ou_ru_id = '.$ru_id;
      $resultDel = $db->fetchRow($sql);
    
      $sql = 'update divided_run SET di_status = 0 WHERE di_id = '.$di_id.' and di_ru_id = '.$ru_id;
      $resultReset = $db->fetchRow($sql);
    
      $sql = 'update divided_run SET di_start_datetime = \'\' WHERE di_id = '.$di_id.' and di_ru_id = '.$ru_id;
      $resultReset = $db->fetchRow($sql);
    
      $sql = 'update divided_run SET di_end_datetime = \'\' WHERE di_id = '.$di_id.' and di_ru_id = '.$ru_id;
      $resultReset = $db->fetchRow($sql);
    
      $sql = 'update divided_run SET di_calc_time = 0 WHERE di_id = '.$di_id.' and di_ru_id = '.$ru_id;
      $resultReset = $db->fetchRow($sql);
    
      COMRedirect(SITE_URL."/run.php?run=".$ru_id);
      exit();
    }
    break;
  default:
    COMRedirect(SITE_URL);
    break;
}


function getCertNum($db, $ru_id) {
  $sql = 'select * FROM run WHERE ru_id = '.$ru_id;
  //echo $sql;
  $ruRow = $db->fetchRow($sql);
  //print_r($ruRow);
  return md5($ru_id.$ruRow["ru_datetime"].$ru_id);
}
function deleteOutputFile($begin, $end) {
  $return = '';
  for($i=$begin; $i < $end; $i++) {
	  $dirname = "out_files/".floor($i / 1000);
    $filename = "out_$i.xz";
	  $cmd = "rm $dirname/$filename";
		//printf($cmd."<br />\n");
	  exec($cmd, $return);
	}
}
