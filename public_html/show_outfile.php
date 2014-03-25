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

    $dirPath = 'out_files';
    $num = floor($di_id / 1000) + 1;
    $fileName = 'out_'.$di_id;
    $filePathCom = $dirPath.'/'.$num.'/'.$fileName;
    $filePathTxt = $dirPath.'/temp/'.$fileName;
    try
    {
        exec('xz -dk '.$filePathCom.'.xz');
        exec('mv '.$filePathCom.' '.$filePathTxt);
        $script = @file_get_contents($filePathTxt);
        
        $smarty = new Smarty();
	    $smarty->left_delimiter = '{{{';
	    $smarty->right_delimiter = '}}}';
	    $smarty->assign('file_name', '出力ファイル '.$fileName);
	    $smarty->assign('script', $script);
        $smarty->display(TEMPLATE.'/show_script.html');
    }
    catch(Exception $e)
    {
	    echo $e->getMessage();
    }

    unlink($filePathTxt);
}

