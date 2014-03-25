#!/usr/bin/php
<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../config');

// 共通関数読み込み
include_once('Common.php');
include_once('Zend/Http/Client.php');

$pnum = 0;
if(isset($argv[1]) == true)
{
   $pnum = intval($argv[1]);
} else {
  echo "not argument";
  exit();
}

$runFileName = '';
$datFileName = '';
$pdbFileName = '';
$minFileName = '';
$xzFileName = '';

$datFile = '';
$pdbFile = '';
$minFile = '';
$outFile = '';

for(;;)
{
  if(WillItStop($pnum) == true)
  {
    exit();
  }

  if(is_dir($pnum) == false) { mkdir($pnum); }
  if(is_dir($pnum.'/input') == false) { mkdir($pnum.'/input'); }
  if(is_dir($pnum.'/output') == false) { mkdir($pnum.'/output'); }
  if(is_dir($pnum.'/io') == false) { mkdir($pnum.'/io'); }

  // DB接続
  $db = DbManager::getConnection();

  if($db !== false)
  {
    $db->beginTransaction();
    try
    {
      $sql = 'SELECT * FROM divided_run WHERE di_status = 0 ORDER BY di_id ASC LIMIT 1  FOR UPDATE';
      $row = $db->fetchRow($sql);

      if($row !== false)
      {
        StartDividedRun($db, $row['di_id']);

        $db->commit();

        $id = $row['di_id'];

        $runFileName = 'run_'.$id;
        $outFileName = 'out_'.$id;
        $xzFileName = $outFileName.'.xz';

        $script = GetAnalyzedScript($db, $row['di_ru_id']);
        $ioFileAry = GetIOFileNameByScript($script);
        //chdir('exe');
        //exec('echo "'.$script.'" >> executer_input_before');

        for($i = 0; $i < count($ioFileAry['input']); $i++)
        {
          CreateInputFile($db, $ioFileAry['input'][$i], $pnum.'/input');
                    $script = str_replace($ioFileAry['input'][$i], $pnum.'/input/'.$ioFileAry['input'][$i], $script);
        }

        for($i = 0; $i < count($ioFileAry['output']); $i++)
        {
          $script = str_replace($ioFileAry['output'][$i], $pnum.'/output/'.$ioFileAry['output'][$i], $script);
        }

        for($i = 0; $i < count($ioFileAry['io']); $i++)
        {
          $script = str_replace($ioFileAry['io'][$i], $pnum.'/io/'.$ioFileAry['io'][$i], $script);
        }

        $varRow = SpritVariable($row['di_variable']);
        for($i = 0; $i < count($varRow); $i++)
        {
          if(count($varRow[$i]) == 2)
          {
            $script = str_replace('#'.$varRow[$i][0], $varRow[$i][1].'.', $script);
          }
        }
        //exec('echo "'.$script.'" >> executer_input_after');

        $db->closeConnection();

        $script = stripslashes($script);

        $fp = fopen($runFileName, 'w');
        fwrite($fp, $script);
        fclose($fp);
        exec('dos2unix '.$runFileName);

        if($row['di_out'] == 0) {
          $exe = COMMAND_CHARMM.' < '.$runFileName.' | awk \'!/^ *$/ && !/^ *CHARMM> *$/ && !/^ *CHARMM> *!.*$/\' > '.$outFileName;
          exec($exe);
          $exe = 'xz '.$outFileName;
          exec($exe);
        } elseif($row['di_out'] == 1) {
          // not save output file
          $exe = COMMAND_CHARMM.' < '.$runFileName.' > /dev/null';
          exec($exe);
        } else {
          $exe = '';
          exec($exe);
        }
        //exec('echo "'.$exe.'">> exec.log');
        //exec($exe);

        $db = DbManager::getConnection();

        unlink($runFileName);
        EndDividedRun($db, $id, $pnum, $row['di_ru_id']);

        if($row['di_out'] == 0) {
          UploadOutfile($id, $xzFileName);
          unlink($xzFileName);
        }

        RmDirAndFile($pnum.'/input');
        RmDirAndFile($pnum.'/output');
        RmDirAndFile($pnum.'/io');
        RmDirAndFile($pnum);
      }
      else
      {
        $db->commit();
        sleep(60);
      }
    }
    catch(Exception $e)
    {
      $db->rollBack();
      echo $e->getMessage();
    }
  }
  else
  {
    sleep(60);
  }
}

function StartDividedRun($db, $id)
{
  $upRow = array(  'di_status' => 1,
          'di_start_datetime' => date('YmdHis'));
  $db->update('divided_run', $upRow, 'di_id = '.$id);
}

function EndDividedRun($db, $id, $pnum, $ru_id)
{
  $sql = 'SELECT * FROM divided_run WHERE di_id = '.$id;
  $row = $db->fetchRow($sql);
  $endDateTime = date('YmdHis');
  $calc_time = 0;
  if($row !== false)
  {
    $calc_time = COMGetDateTimeOffset($row['di_start_datetime'], $endDateTime);
  }  

  $diRow = array(  'di_status' => 2,
          'di_end_datetime' => $endDateTime,
          'di_calc_time' => $calc_time);
  $db->update('divided_run', $diRow, 'di_id = '.$id);

  $fileAry = scandir($pnum.'/output');
  if($fileAry !== false)
  {
    for($i = 0; $i < count($fileAry); $i++)
    {
      if(($fileAry[$i] != '.') && ($fileAry[$i] != '..'))
      {
        if(strpos($fileAry[$i], '_nosave') !== 0)
        {
          $file = file_get_contents($pnum.'/output/'.$fileAry[$i]);
          $ouRow = array(  'ou_di_id' => $id,
                          'ou_ru_id' => $ru_id,
                  'ou_file_name' => $fileAry[$i],
                  'ou_contents' => $file);
          $db->insert('output_file', $ouRow);
        }
      }
    }
  }
}

function GetAnalyzedScript($db, $id)
{
  $script = '';

  $sql = 'SELECT * FROM run WHERE ru_id = '.$id;
  $row = $db->fetchRow($sql);
  if($row !== false)
  {
    $script = $row['ru_analyzed_script'];
  }

  return $script;
}

function GetAndRmFile($fileName)
{
  $file = '';

  if(is_file($fileName) === true)
  {
    $file = file_get_contents($fileName);
    unlink($fileName);
  }

  return $file;
}

function UploadOutfile($id, $fileName)
{
  $client = new Zend_Http_Client(FILE_HOST, array('maxredirects' => 0, 'timeout' => 300));
  $client->setParameterPost('id', $id);
  $client->setFileUpload($fileName, 'out_file');
  $client->request('POST');
}

function WillItStop($pnum)
{
  if($pnum == 0)
  {
    return false;
  }

  $fileName = 'executer_stoper'.$pnum;

  if(is_file($fileName) == true)
  {
    unlink($fileName);
    return true;
  }

  return false;
}

function RmDirAndFile($dirPath)
{
  $fileAry = scandir($dirPath);

  if($fileAry !== false)
  {
    for($i = 0; $i < count($fileAry); $i++)
    {
      if(($fileAry[$i] != '.') && ($fileAry[$i] != '..'))
      {
        unlink($dirPath.'/'.$fileAry[$i]);
      }
    }
  }
  rmdir($dirPath);
}

function CreateInputFile($db, $fileName, $dirPath)
{
  $quoteFileName = $db->quote($fileName);
  $sql = 'SELECT * FROM input_file WHERE in_file_name = '.$quoteFileName;
  $row = $db->fetchRow($sql);
  if($row !== false)
  {
    $inputFile = $row['in_contents'];

    $fp = fopen($dirPath.'/'.$fileName, 'w');
    fwrite($fp, $inputFile);
    fclose($fp);
  }
}

