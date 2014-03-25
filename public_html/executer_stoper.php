<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

chdir('exe');

$pnumAry = array();
$stopPnum = 0;

$psAry = GetPsAry();
for( $i = 0; $i < count($psAry); $i++ )
{
	if(strpos($psAry[$i], './executer.php') !== false)
	{
		$pnumAry[] = GetPnum($psAry[$i]);
	}
}

rsort($pnumAry);

for($i = 0; $i < count($pnumAry); $i++)
{
	$fileName = 'executer_stoper'.$pnumAry[$i];
	if(is_file($fileName) == false)
	{
		$stopPnum = $pnumAry[$i];
		break;
	}
}

if($stopPnum != 0)
{
	$fileName = 'executer_stoper'.$stopPnum;
	$fp = fopen($fileName, 'w');
	fwrite($fp, 'stop');
	fclose($fp);
}

chdir('..');

COMRedirect('/');

