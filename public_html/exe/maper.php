#!/usr/bin/php
<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$row = array();
$h = 0;

$index = 0;

$v = array();

$tempAry = array();

$ru_id = intval($argv[1]);

for($i = 2; $i < count($argv); $i++)
{
	$v[] = $argv[$i];
}

$vary = array();
$tempAry2 = array();

$db->delete('map', 'ma_ru_id = '.$ru_id);

while($row !== false)
{
	$sql = 'SELECT * FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id ASC LIMIT 1000 OFFSET '.$h;
	$row = $db->fetchAll($sql);
	for($s = 0; $s < count($row); $s++)
	{
		$tempAry2 = array();

		$tempAry = SpritVariableOnIndex($row[$s]['di_variable']);
		for($i = 0; $i < count($v); $i++)
		{
			if(array_key_exists($v[$i], $tempAry))
			{
				$tempAry2[$v[$i]] = $tempAry[$v[$i]];
			}
		}

		$tempAry2['value'] = floatval($row[$s]['di_min']);
		$tempAry2['di_id'] = $row[$s]['di_id'];

		$isTrue = 0;
		$isFalse = 0;
		$isSame = false;
		$sameIndex = 0;
		for($i = 0; $i < count($vary); $i++)
		{
			$isTrue = 0;
			$isFalse = 0;

			for($k = 0; $k < count($v); $k++)
			{
				if($vary[$i][$v[$k]] == $tempAry2[$v[$k]])
				{
					$isTrue++;
				}
				else
				{
					$isFalse++;
				}
			}

			if($isFalse == 0)
			{
				$isSame = true;
				$sameIndex = $i;
				break;
			}
		}

		if($isSame == false)
		{
			$vary[] = $tempAry2;
		}
		else
		{
			if($tempAry2['value'] < $vary[$sameIndex]['value'])
			{
				$vary[$sameIndex]['value'] = $tempAry2['value'];
				$vary[$sameIndex]['di_id'] = $tempAry2['di_id'];
			}
		}
	}

	if(count($row) == 0)
	{
		$row = false;
	}

	$h += 1000;
}

for($i = 0; $i < count($vary); $i++)
{
	$id = COMGetNextId($db, 'ma_id', 'map');

	$varStr = '';
	for($k = 0; $k < count($v); $k++)
	{
		$varStr .= $v[$k].'='.$vary[$i][$v[$k]].' ';
	}
	$varStr = trim($varStr);

	$row = array(	'ma_id' => $id,
					'ma_ru_id' => $ru_id,
					'ma_di_id' => $vary[$i]['di_id'],
					'ma_variable' => $varStr,
					'ma_min' => $vary[$i]['value']);
	$db->insert('map', $row);
}

