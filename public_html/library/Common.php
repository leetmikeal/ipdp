<?php
include_once 'config.php';

include_once 'Zend/Db.php';
include_once 'Smarty/libs/Smarty.class.php';
include_once 'DbManager.class.php';
include_once 'di_lib.php';
include_once 'in_lib.php';

date_default_timezone_set('Asia/Tokyo');

function COMGetFormatDateTime($dateTime)
{
  if($dateTime == '')
  {
    return '';
  }

  $dateTimeStr = substr($dateTime, 0, 4).'/';
  $dateTimeStr = $dateTimeStr.substr($dateTime, 4, 2).'/';
  $dateTimeStr = $dateTimeStr.substr($dateTime, 6, 2).' ';
  $dateTimeStr = $dateTimeStr.substr($dateTime, 8, 2).':';
  $dateTimeStr = $dateTimeStr.substr($dateTime, 10, 2); //.':';
  //$dateTimeStr = $dateTimeStr.substr($dateTime, 12, 2);
  return date($dateTimeStr);
}

function COMGetNextId($inDb, $colName, $tableName)
{
  $sql = 'SELECT MAX('.$colName.') FROM '.$tableName;
  $row = $inDb->fetchRow($sql);
  return (intval($row['max']) + 1);
}

function COMRedirect($path)
{
  header('Location: '.$path);
}

function COMGetDateTimeOffset($startDateTime, $endDateTime)
{
  if(($startDateTime == '') || ($endDateTime == ''))
  {
    return 0;
  }

  $startTimeStamp = COMMkTime($startDateTime);
  $endTimeStamp = COMMkTime($endDateTime);
  return $endTimeStamp - $startTimeStamp;
}

function COMMkTime($dateTime)
{
  $year =substr($dateTime, 0, 4);
  $month = substr($dateTime, 4, 2);
  $day = substr($dateTime, 6, 2);
  $hour = substr($dateTime, 8, 2);
  $minute = substr($dateTime, 10, 2);
  $second = substr($dateTime, 12, 2);

  return mktime($hour, $minute, $second, $month, $day, $year);
}

function COMGetFormatSecond($second)
{
  if($second == 0)
  {
    return '';
  }

  $str = '';
  $hour = floor($second / 3600);
  if($hour != 0)
  {
    $str .= strval($hour).'h ';
    $subSecond = $second - ($hour * 3600);

    $minute = floor($subSecond / 60);
    $str .= strval($minute).'m ';

    $str .= ($subSecond % 60).'s('.$second.'s)';
  }
  else
  {
    $minute = floor($second / 60);
    if($minute != 0)
    {
      $str .= strval($minute).'m ';
    }

    $str .= ($second % 60).'s('.$second.'s)';
  }

  return $str;
}

function SpritVariable($di_variable)
{
  if($di_variable == '')
  {
    return array();
  }

  $retAry = array();
  $tempAry = explode(' ', $di_variable);
  for($i = 0; $i < count($tempAry); $i++)
  {
    $retAry[] = explode('=', $tempAry[$i]);
  }

  return $retAry;
}

function SpritVariableOnIndex($di_variable)
{
  if($di_variable == '')
  {
    return array();
  }

  $retAry = array();
  $tempAry1 = explode(' ', $di_variable);
  for($i = 0; $i < count($tempAry1); $i++)
  {
    $tempAry2 = explode('=', $tempAry1[$i]);

    if(count($tempAry2) == 2)
    {
      $retAry[$tempAry2[0]] = $tempAry2[1];
    }
  }

  return $retAry;
}

function GetPsAry()
{
  $output = array();
  $exe = 'ps ax';
  exec($exe, $output);
  return $output;
}

function GetPnum($psStr)
{
  $ary = explode(' ', $psStr);

  $index = array_search('./executer.php', $ary);

  if($index === false)
  {
    return 0;
  }

  if(count($ary) > $index + 1)
  {
    return intval($ary[$index + 1]);
  }

  return 0;
}

function IsMaperRun()
{
  $psAry = GetPsAry();
  for($i = 0; $i < count($psAry); $i++)
  {
    if(strpos($psAry[$i], './maper.php') !== false)
    {
      return true;
    }
  }

  return false;
}

function GetIOFileNameByScript($file)
{
  $retAry = array(  'input' => array(),
            'output' => array(),
            'io' => array());

  $inputAry = array();
  $outputAry = array();

    $file = stripslashes($file);
  $file = str_replace("\r\n", "\n", $file);
  $file = str_replace("\r", "\n", $file);
  $fileLineAry = explode("\n", $file);
  $count = count($fileLineAry);


    // tamaki
    //$tfp = @fopen("temp.txt", "w");
    //$tfp1 = @fopen("temp1.txt", "w");


    for($i = 0; $i < $count; $i++)
  {
    $tempStr = $fileLineAry[$i];
    //$tempStr = str_replace("\t", ' ', $tempStr);
    //$tempStr = str_replace('  ', ' ', $tempStr);

        // tamaki
        //fputs($tfp, $fileLineAry[$i]);
        //fputs($tfp, "\n");

    $pos = strpos($tempStr, '!');
    if($pos !== false)
    {
      $tempStr = substr($tempStr, 0, $pos);
    }

    if(stripos($tempStr, 'OPEN') !== false)
    {
      if(stripos($tempStr, 'READ') !== false)
      {
        $targetStr = stristr($tempStr, 'NAME');
        $targetStr = substr($targetStr, 4);
        $targetStr = trim($targetStr);
        $targetStr = trim($targetStr, '"');
        if($targetStr == '-')
        {
          $tempStr = $fileLineAry[$i + 1];
          $targetStr = trim($tempStr);
          $targetStr = trim($targetStr, '"');
        }
        $inputAry[] = $targetStr;
      }

      if(stripos($tempStr, 'WRITE') !== false)
      {
        $targetStr = stristr($tempStr, 'NAME');
        $targetStr = substr($targetStr, 4);
        $targetStr = trim($targetStr);
        $targetStr = trim($targetStr, '"');
        if($targetStr == '-')
        {
          $tempStr = $fileLineAry[$i + 1];
          $targetStr = trim($tempStr);
          $targetStr = trim($targetStr, '"');
        }
        $outputAry[] = $targetStr;
      }
    }

    // tamaki
    //fputs($tfp1, $fileLineAry[$i]);
    //fputs($tfp1, "\n");

    }
    // tamaki
    //fclose($tfp);

  $retAry['io'] = array_intersect($inputAry, $outputAry);
  $retAry['input'] = array_diff($inputAry, $retAry['io']);
  $retAry['output'] = array_diff($outputAry, $retAry['io']);

  $retAry['io'] = array_values($retAry['io']);
  $retAry['input'] = array_values($retAry['input']);
  $retAry['output'] = array_values($retAry['output']);

  return $retAry;
}

function CheckInputFile($db, $inputFileAry)
{
  $retAry = array();
  $errMsg = '';

  $count = count($inputFileAry);
  for($i = 0; $i < $count; $i++)
  {
    $errMsg = '';

    if($errMsg == '')
    {
      if(strpos($inputFileAry[$i], '@') !== false)
      {
        $errMsg = '変数を含む入力ファイル名には対応していません。';
      }
    }

    if($errMsg == '')
    {
      if(InFileExists($db, $inputFileAry[$i]) === false)
      {
        $errMsg = 'このファイル名の入力ファイルは登録されていません。';
      }
    }

    $retAry[] = array(  'file' => $inputFileAry[$i],
              'err_msg' => $errMsg);
  }

  return $retAry;
}

function CountInputFileErr($inputFileAry)
{
  $retCount = 0;

  $count = count($inputFileAry);
  for($i = 0; $i < $count; $i++)
  {
    if($inputFileAry[$i]['err_msg'] !== '')
    {
      $retCount++;
    }
  }
  return $retCount;
}

function CheckSoftware($cu_software)
{
    global $software;
    foreach($software as $value)
    {
        if($value['name'] == $cu_software) return true;
    }
    return false;
}

function GetProject($cu_software)
{
    global $software;
    foreach($software as $value)
    {
        if($value['name'] == $cu_software)
        {
            return array_merge(array('normal') , $value['project']);
        }
    }
    return array('normal');
}

function isNumber($str) {
  if(strval($str) === strval(intval($str))) {
    return True;
  } else {
    return False;
  }
}

function getRunInfo($db, $ru_id) {
  $sql = 'SELECT * FROM run WHERE ru_id = '.$ru_id;
  return $db->fetchRow($sql);
}
