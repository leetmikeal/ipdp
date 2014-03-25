<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

if((isset($_POST['run_file']) == true) && (isset($_POST['file_name']) == true))
{
	$fileName = $_POST['file_name'];
  $file = $_POST['run_file'];
  $software = $_POST['software'];
  $project = $_POST['project'];
  $saveout = 0;
  if(isset($_POST['no_out'])) $saveout = 1;

	$id = COMGetNextId($db, 'ru_id', 'run');

    $file = stripslashes($file);

    $row = array(	'ru_id' => $id,
                    'ru_software' => $software,
                    'ru_project' => $project,
					'ru_file_name' => $fileName,
					'ru_original_script' => $file,
					'ru_analyzed_script' => '',
					'ru_divided' => 0,
					'ru_datetime' => date('YmdHis'),
          'ru_out' => $saveout
    );

	$db->insert('run', $row);

	if(IsDividerRun() === false)
	{
		chdir('exe');
		$exe = './divider.php > divider_out &';
		exec($exe);
		chdir('..');
	}
}

COMRedirect(SITE_URL);

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

