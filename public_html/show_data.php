<?php
ini_set("memory_limit","512M");

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// initial settings
$showLimit = 10000;

// getting data form URL
$ouRow = array();
$ruRow = array();

$showMode = false;
$ou_file_name = '';
if(isset($_GET['filename'])) {
  $showMode = true;
  $ou_file_name = htmlentities($_GET['filename']);
}
$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}

$ou_file_names = array();
$ou_contents = '';

$sql = 'SELECT ru_id, ru_file_name FROM run WHERE ru_id ='.$ru_id;
$ruRow = $db->fetchRow($sql);

if($showRun === false) {
	COMRedirect('/');
} else {
    if($showMode === false) {
/*        $sql = 'SELECT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id;
        $ouRow = $db->fetchAll($sql);
        for($i = 0; $i < count($ouRow); $i++) {
            $ou_file_buf[] = $ouRow[$i]['ou_file_name'];
        }
        $ou_file_name = array_unique($ou_file_buf);
 */      
        $sql = 'SELECT di_id FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id LIMIT 1';
        $diCountRow = $db->fetchRow($sql);
        $diFirst = intval($diCountRow['di_id']);
        

        //$sql = 'SELECT DISTINCT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id.' limit '.$showLimit;
        $sql = 'SELECT ou_di_id, ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id.' order by ou_di_id ASC limit '.$showLimit;
        $ouRow = $db->fetchAll($sql);

        for($i = 0; $i < count($ouRow); $i++) {
            $sql = 'SELECT di_variable FROM divided_run WHERE di_id = '.$ouRow[$i]['ou_di_id'].' LIMIT 1';
            $diRow = $db->fetchRow($sql);
            $ou_file_names[] = array(
              'no' => intval($ouRow[$i]['ou_di_id']) - $diFirst + 1,
              'di_id' => $ouRow[$i]['ou_di_id'],
              'variable' => $diRow['di_variable'],
              'name' => $ouRow[$i]['ou_file_name']
            );
        }
 
    } else {
    
        $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\'';
        $ouRow = $db->fetchAll($sql);

        for($i = 0; $i < count($ouRow); $i++) {
            $ou_contents .= $ouRow[$i]['ou_contents'];
        }

    }
    $smarty = new Smarty();
	$smarty->left_delimiter = '{{{';
    $smarty->right_delimiter = '}}}';
    $smarty->assign('ruRow', $ruRow);
    $smarty->assign('showMode',$showMode);
	$smarty->assign('ru_id', $ru_id);
	$smarty->assign('ou_contents', $ou_contents);
	$smarty->assign('ou_file_name', $ou_file_name);
	$smarty->assign('ou_file_names', $ou_file_names);
    $smarty->display(TEMPLATE.'/show_data.html');
}

