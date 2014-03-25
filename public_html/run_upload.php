<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$errorMsg = '';
$ruAry = array();
$psAry = array();
$executerNum = 0;
$executerAry = array();

if(isset($_FILES['run_file']) == true)
{
	if(InsertRun($db, $_FILES['run_file'], $errorMsg) === true)
	{
		COMRedirect('/');
	}
}

$sql = 'SELECT ru_id, ru_file_name, ru_datetime FROM run ORDER BY ru_id DESC';
$row = $db->fetchAll($sql);
if($row !== false)
{
	for($i = 0; $i < count($row); $i++)
	{
		$ruAry[] = array(	'ru_id' => $row[$i]['ru_id'],
							'ru_file_name' => $row[$i]['ru_file_name'],
							'ru_datetime' => $row[$i]['ru_datetime'],
							'alldi' => DiGetAllCount($db, $row[$i]['ru_id']),
							'enddi' => DiGetCountByStatus($db, $row[$i]['ru_id'], 2));
	}
}

chdir('exe');
$psAry = GetPsAry();
for( $i = 0; $i < count($psAry); $i++ )
{
	if(strpos($psAry[$i], './executer.php') !== false)
	{
		$executerNum++;
		$fileName = 'executer_stoper'.GetPnum($psAry[$i]);
		if(is_file($fileName) == true)
		{
			$executerAry[] = $psAry[$i].' 停止中';
		}
		else
		{
			$executerAry[] = $psAry[$i];
		}
	}
}
chdir('..');

$smarty = new Smarty();
$smarty->left_delimiter = '{{{';
$smarty->right_delimiter = '}}}';
$smarty->assign('errorMsg', $errorMsg);
$smarty->assign('ruAry', $ruAry);
$smarty->assign('executerNum', $executerNum);
$smarty->assign('executerAry', $executerAry);
$smarty->display(TEMPLATE.'/index.html');

function InsertRun($db, $fileAry, &$errorMsg)
{
	$error = false;
	$errorMsg = '';

	if($fileAry['error'] == UPLOAD_ERR_OK)
	{

	}
	else if($fileAry['error'] == UPLOAD_ERR_NO_FILE)
	{
		$errorMsg = 'ファイルを選択してください。';
		$error = true;
	}
	else
	{
		$errorMsg = 'ファイルアップロードでエラーが発生しました。';
		$error = true;
	}

	if($error === false)
	{
		$id = COMGetNextId($db, 'ru_id', 'run');

		$file = file_get_contents($fileAry['tmp_name']);

        $file = stripslashes($file);

		$row = array(	'ru_id' => $id,
						'ru_file_name' => $fileAry['name'],
						'ru_original_script' => $file,
						'ru_analyzed_script' => '',
						'ru_divided' => 0,
						'ru_datetime' => date('YmdHis'));

		$db->insert('run', $row);

		if(IsDividerRun() === false)
		{
			chdir('exe');
			$exe = './divider.php > divider_out &';
			exec($exe);
			chdir('..');
		}
	}

	return !$error;
}

function IsDividerRun()
{
	$psAry = GetPsAry();

	for( $i = 0; $i < count($psAry); $i++ )
	{
		if(strpos($psAry[$i], './divider.php') !== false)
		{
			return true;
		}
	}

	return false;
}

