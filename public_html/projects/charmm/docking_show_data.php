<?php
set_time_limit(180);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// initial settings
$showLineLimit = 50; // when it's shown output file contents with limit.
$showLineLimitShort = 5;

// getting parameter from url
$ouRow = array();

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
$fileType = '';
$fileTypeFlag = false;
if(isset($_GET['type'])) {
    $fileType = addslashes($_GET['type']);
    $fileTypeFlag = true;
}

$ruRow = getRunInfo($db, $ru_id);

// initialize parameter
$ou_file_names = array();
$ou_di_id = 0;
$ou_contents = '';
$fileExtArray = array();
$fileExtArrayUni = array();

if($showRun === false) {
  COMRedirect('../');
} else {
    if($showMode === false) {
        $sql = 'SELECT DISTINCT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id;
        $ouRow = $db->fetchAll($sql);


        for($i = 0; $i < count($ouRow); $i++) {
            $fileExt = pathinfo($ouRow[$i]['ou_file_name'], PATHINFO_EXTENSION);
            $fileExtArray[] = $fileExt;
            if($fileTypeFlag === true) {
              if($fileExt == $fileType) {
                $sql = 'SELECT count(*) FROM output_file WHERE ou_ru_id = '.$ru_id.' and ou_file_name = '."'".$ouRow[$i]['ou_file_name']."'";
                $ouCountRow = $db->fetchRow($sql);
                if(intval($ouCountRow['count']) > 1) {
                  // get output file contents
                  $sql = 'SELECT ou_di_id, ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ouRow[$i]['ou_file_name'].'\' order by ou_di_id ASC Limit 1';
                  $ouRowCont = $db->fetchRow($sql);

                  $ou_contents = '';
                  $ouLine = explode("\n", trim($ouRowCont['ou_contents']));
                  for($k = 0; $k < count($ouLine); $k++) {
                    $ou_contents .= $ouLine[$k]."\n";
                    if($k >= $showLineLimitShort) break;
                  }
                  // result
                  $ou_file_names[] = array(
                    'name' => $ouRow[$i]['ou_file_name'], 
                    'count' => $ouCountRow['count'],
                    'contents' => $ou_contents
                  );
                }
              }

            } elseif($fileTypeFlag === false) {
                $ou_file_names[] = array('name' => $ouRow[$i]['ou_file_name']);
            }
        }
        //$fileExtArrayUni = array_unique($fileExtArray);
        $fileExtArrayUni = array_count_values($fileExtArray);
        /*
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
        */
    } else {
        // get output file contents
        $sql = 'SELECT ou_di_id, ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' order by ou_di_id ASC';
        $ouRow = $db->fetchRow($sql);

        $ou_contents = '';
        $ouLine = explode("\n", trim($ouRow['ou_contents']));
        for($k = 0; $k < count($ouLine); $k++) {
          $ou_contents .= $ouLine[$k]."\n";
          if($k >= $showLineLimit) break;
        }

        // number of divided outfile
        $ou_di_id = $ouRow['ou_di_id'];

        //$ou_contents .= $ouRow['ou_contents'];
        //for($i = 0; $i < count($ouRow); $i++) {
        //    $ou_contents .= $ouRow[$i]['ou_contents'];
        //}

    }
    $smarty = new Smarty();
    $smarty->left_delimiter = '{{{';
    $smarty->right_delimiter = '}}}';
    $smarty->assign('showMode',$showMode);
    $smarty->assign('ru_id', $ru_id);
    $smarty->assign('ruRow', $ruRow);
    $smarty->assign('ou_contents', $ou_contents);
    $smarty->assign('fileTypeFlag', $fileTypeFlag);
    $smarty->assign('fileType', $fileType);
    $smarty->assign('ou_file_name', $ou_file_name);
    $smarty->assign('ou_di_id', $ou_di_id);
    $smarty->assign('ou_file_names', $ou_file_names);
    $smarty->assign('fileExtArray', $fileExtArrayUni);
    $smarty->display('docking_show_data.html');
}

