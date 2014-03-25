<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

$executerNum = 0;
$pnumAry = array();

$psAry = GetPsAry();
for( $i = 0; $i < count($psAry); $i++ )
{
	if(strpos($psAry[$i], './executer.php') !== false)
	{
		$pnumAry[] = GetPnum($psAry[$i]);
	}
}

$i = 1;
$index = 0;
while($index !== false)
{
	$index = array_search($i, $pnumAry);
	if($index === false)
	{
		$executerNum = $i;
	}

	$i++;
}

chdir('exe');
$exe = './executer.php '.$executerNum.' > executer'.$executerNum.'_out &';
exec($exe);
chdir('..');

COMRedirect('/');

