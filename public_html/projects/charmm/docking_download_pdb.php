<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

// get parameter settings
$isRunId = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
  $isRunId = true;
  $ru_id = intval($_GET['run']);
}
$ener = 0;
if(isset($_GET['ener'])) $ener = intval($_GET['ener']);
$pdbResid = "";
if(isset($_GET['resid']) === true) {
  $pdbResid = strtoupper($_GET['resid']);
}
$pdbPrefix = "";
if(isset($_GET['pdbprefix']) === true) {
  $pdbPrefix = htmlentities($_GET['pdbprefix']);
}
$energy_data = '';
if(isset($_GET['enefile']) === true) {
  $energy_data = htmlentities($_GET['enefile']);
}
$energy_column = 0;
if(isset($_GET['column']) === true) {
  $energy_column = intval($_GET['clumn']);
}

// initial parameter settings
$ou_file_names = array();
$ou_file_name = '';
$dat_file_name = '';
$ou_contents = '';

// special parameter
//$pdbResid = 'DECR';
//$pdb_resid_prefix = 'S';
//$pdb_count = 0;


if($isRunId === false || $pdbResid === "") {
  COMRedirect('/');
} else {
  //$sql = 'SELECT ru_file_name FROM run WHERE ru_id = '.$ru_id;
  //$ruRow = $db->fetchRow($sql);
  //$ru_file_name = $ruRow['ru_file_name'];

  //$dat_file_name = getNameByExt($db, $ru_id, $data_ext);
  if(isExistDatafile($db, $ru_id, $energy_data)) {
    $dat_file_name = $energy_data;
  } else {
    exit();
  }

  // To get file contents each divided jobs.
  $sql = 'SELECT di_id FROM divided_run WHERE di_ru_id = '.$ru_id.' ORDER BY di_id ASC';
  //echo $sql."\n"; // debug
  $di_id = $db->fetchAll($sql);

  $pdbNames = array();
  foreach($di_id as $value) {
    $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$dat_file_name.'\' AND ou_di_id = '.$value['di_id'];
    //echo $sql."\n"; // debug
    $ouRow = $db->fetchRow($sql);
    $ou_buf = $ouRow['ou_contents'];
    $ou_buf = trim($ou_buf);
    $ou_buf = trim($ou_buf, '\n');
    //echo $ou_buf; // debug
    //fwrite($df_handle, $ou_buf);
    //fwrite($df_handle, combinePdbFiles($db, $ou_buf, $pdbResid, $ener)); 
    //echo combinePdbFiles($db, $ou_buf, $pdbResid, $ener); // debug
    //echo "*****************";

    // [note:] pdbNames is reference
    gatherLowEnerPdbName($pdbNames, $ou_buf, $ener, $energy_column, $pdbPrefix);
    // break; // debug
  }

  // sort 
  $arrsort = array();
  for($i = 0; $i < count($pdbNames); $i++) {
    $arrsort[] = $pdbNames[$i][0];
  }
  array_multisort($arrsort, SORT_ASC, $pdbNames);

  // OUTPUT
  // To get output filename
  $temp_ener_filepath = tempnam(DIR_BASE.DIR_DATA, "docking_ener_");
  $ener_handle = fopen($temp_ener_filepath, "w");
  //print_r($pdbNames);
  $temp_filepath = tempnam(DIR_BASE.DIR_DATA, "docking_df_");
  $df_handle = fopen($temp_filepath, "w");

  // write
  for($i = 0; $i < count($pdbNames); $i++) {
    fwrite($ener_handle, (string)$pdbNames[$i][2]."\n");
    fwrite($df_handle, combinePdbFiles($db, $ru_id, $pdbNames[$i][1], $pdbResid, $i+1));
    // echo combinePdbFiles($db, $ru_id, $pdbNames[$i][1], $pdbResid, $i+1);
  }

  fclose($ener_handle);
  fclose($df_handle);

  // download
  $isDownload = downloadFile(DIR_DATA, $temp_filepath);
  if (!$isDownload) {
    echo "Error !! Download was failed !!\n";
  }
  rename($temp_ener_filepath, DIR_BASE.DIR_DATA."/"."ener_".$pdbResid."_lt".$ener.".log");

  unlink($temp_filepath);
}


function gatherLowEnerPdbName(&$pdbNames, $contents, $ener, $energy_column, $prefix = "") {
  //$pdbNames = array();
  $ou_row = explode("\n",$contents);

  for($i = 0; $i < count($ou_row); $i++) {
    $ou_row[$i] = trim($ou_row[$i]);
    $ou_col = split(" +", trim($ou_row[$i]));
    $ou_col_float = floatval($ou_col[$energy_column]);
    if($ou_col_float < $ener) {
      $temp_pdb_name_format = $prefix.'x%1$s-y%2$s-z%3$s-zd%4$s-yd%5$s-zd%6$s.pdb';
      //$temp_pdb_name = "hsa_de-ss_"
      //    ."x".$ou_col[0]
      //    ."-y".$ou_col[1]
      //    ."-z".$ou_col[2]
      //    ."-zd".$ou_col[3]
      //    ."-yd".$ou_col[4]
      //    ."-zd".$ou_col[5].".pdb";
      $temp_pdb_name = sprintf($temp_pdb_name_format,
      $ou_col[0], $ou_col[1], $ou_col[2],
        $ou_col[3], $ou_col[4], $ou_col[5]);
      //echo $temp_pdb_name."\n"; // debug
      $pdbNames[] = array($ou_col_float, $temp_pdb_name, $ou_row[$i]);
      // break; // debug
    }
  }
  //return $pdbNames;
}

function combinePdbFiles($db, $ru_id,  $filename, $overlapResid, $resid) {
  $sql = "SELECT ou_contents FROM output_file WHERE ou_ru_id = ".$ru_id." AND ou_file_name = '".$filename."'";
  //echo $sql."\n"; // debug
  $ouContentRow = $db->fetchRow($sql);
  //echo $ouContentRow['ou_contents']; // debug
  return extractPdbByResname($ouContentRow['ou_contents'], $overlapResid, $resid);
}

function extractPdbByResname($contents, $overlapResid, $resid) {
  static $atomnum = 0;
  $text = '';
  $contRow = explode("\n",$contents);
  for($j = 0; $j < count($contRow); $j++) {
    $columnArray = toPdbRowArray($contRow[$j]);
    //$contCol = split(" +", trim($contRow[$j]));
    //echo $contCol[3]."------".$contRow[$j]."\n"; // debug
    //echo $contCol[3]."------".$overlapResid."\n"; // debug
    //print_r($columnArray);
    //$text .= toPdbRowText($columnArray);
    if($columnArray[0] == "ATOM" && $columnArray[4] == $overlapResid) {
      //echo $contRow[$j]."\n"; // debug
      $atomnum += 1;
      $columnArray[1] = $atomnum;
      $columnArray[6] = $resid;
      $text .= toPdbRowText($columnArray);
      //$text .= $contRow[$j]."\n"; // raw data
    }
  }
  //if($ou_buf !== "") fwrite($df_handle, $ou_row[$i]."\n");
  return $text;
}

function toPdbRowArray($row) {
  $column = array();
  if(strlen($row) >= 70) {
    $column[0]  = trim(substr($row,  0, 6));
    $column[1]  = (int)substr($row,  6, 5);
    $column[2]  = trim(substr($row, 12, 4));
    $column[3]  = trim(substr($row, 16, 1));
    $column[4]  = trim(substr($row, 17, 4));
    $column[5]  = trim(substr($row, 21, 1));
    $column[6]  = (int)substr($row, 22, 4);
    $column[7]  = trim(substr($row, 26, 1));
    $column[8]  = (float)substr($row, 30, 8);
    $column[9]  = (float)substr($row, 38, 8);
    $column[10] = (float)substr($row, 46, 8);
    $column[11] = (float)substr($row, 54, 6);
    $column[12] = (float)substr($row, 60, 6);
    $column[13] = trim(substr($row, 66, 2));
    $column[14] = trim(substr($row, 68, 2));
  }
  return $column;
}

function toPdbRowText($column) {
  //$format = "[%-6s][%5d][%4s][%1s][%3s] [%1s][%4d][%1s]   [%8.3f][%8.3f][%8.3f][%6.2f][%6.2][%2s][%2s]\n";
  $format = "%-6s% 5d%4s%1s%-4s%1s% 4d%1s   %8.3f%8.3f%8.3f%6.2f%6.2f%2s%2s\n";
  if(count($column) != 15) return "";
  return sprintf($format
      , $column[0]
      , $column[1] // atom serial number
      , $column[2] // atom name
      , $column[3] 
      , $column[4] // residue name
      , $column[5] 
      , $column[6] // residue sequence number
      , $column[7] // x coordinate
      , $column[8] // y coordinate
      , $column[9] // z coordinate
      , $column[10]
      , $column[11]
      , $column[12]
      , $column[13]
      , $column[14]
  );
}

function isExistDatafile($db, $ru_id, $filename) {
  $sql = 'SELECT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id.' and ou_file_name = '."'".$filename."'";
  $ouRow = $db->fetchRow($sql);
  if($ouRow == false) {
    return false;
  } else {
    return true;
  }
}

function getNameByExt($db, $ru_id, $data_ext) {
  $sql = 'SELECT DISTINCT ou_file_name FROM output_file WHERE ou_ru_id = '.$ru_id;
  //echo $sql."\n"; // debug
  $ouDistinctName = $db->fetchAll($sql);
  //$dat_file_name = $ouDistinctName[0]['ou_file_name']; // debug

  for($i = 0; $i < count($ouDistinctName); $i++) {
    $fileExt = pathinfo($ouDistinctName[$i]['ou_file_name'], PATHINFO_EXTENSION);
    if($fileExt == $data_ext) {
      return $ouDistinctName[$i]['ou_file_name'];
      //$dat_file_name = $ouDistinctName[$i]['ou_file_name'];
      //break;
    }
  }
}

function downloadFile($dir, $file) {
  $path = $dir."/".$file;
  if (file_exists($file)) {
    $handle = fopen($file, "r");
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$file);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    while(!feof($handle)) {
      $bytes = fread($handle, 1000*1024);
      echo $bytes;
      flush();
      //usleep(100000); // 100 ms
      //sleep(1); // argument type only allow int
    }
    return true; 
  } else {
    return false;
  }
}


/**
 * *******************************************************
 *  Function Backup
 * *******************************************************
 */

function combinePdbFiles_old($db, $contents, $overlapResid) {
    $ou_row = explode("\n",$contents);

    for($i = 0; $i < count($ou_row); $i++) {
        $ou_row[$i] = trim($ou_row[$i]);
        $ou_col = split(" +", trim($ou_row[$i]));
        $ou_col_float = floatval($ou_col[6]);
        if($ou_col_float < $ener) {
            echo $ou_row[$i]."\n";
            echo $ou_col_float."\n";
            $temp_pdb_name = "cb9-gt-s-mepho_".
                (string)((int)$ou_col[0])."-".
                (string)((int)$ou_col[1])."-".
                (string)((int)$ou_col[2])."-zd".
                (string)((int)$ou_col[3])."-yd".
                (string)((int)$ou_col[4])."-zd".
                (string)((int)$ou_col[5])."d.pdb";
            echo $temp_pdb_name."\n";
            $sql = "SELECT ou_contents FROM output_file WHERE ou_file_name = '".$temp_pdb_name."'";
            //echo $sql."\n";
            $ouContentAll = $db->fetchRow($sql);
            $in_cont = $ouContentAll['in_contents'];
            $in_cont_row = explode("\n",$in_cont);
            for($j = 0; $j < count($in_cont_row); $j++) {
                $in_cont_col = split(" +", trim($in_cont_row[$j]));
                    echo $in_cont_col[3]."------".$in_cont_row[$j]."\n";
                if($in_cont_col[3] == $overlapResid) {
                    //$in_cont_row[$j] = str_replace($overlapResid, $pdb_resid_prefix.$pdb_count, $in_cont_row[$j]);
                    fwrite($df_hadle, $in_cont_row[$j]."\n");
                }
            }
            //if($ou_buf !== "") fwrite($df_handle, $ou_row[$i]."\n");
            //$pdb_count++;
        }
    }
}
function combinePdbFiles_old2($db, $contents, $overlapResid, $ener) {
    $text = '';
    $ou_row = explode("\n",$contents);

    for($i = 0; $i < count($ou_row); $i++) {
        $ou_row[$i] = trim($ou_row[$i]);
        $ou_col = split(" +", trim($ou_row[$i]));
        $ou_col_float = floatval($ou_col[9]);
        if($ou_col_float < $ener) {
      //$text .= $ou_row[$i]."\n";

            //echo $ou_row[$i]."\n"; // debug
            //echo $ou_col_float."\n"; //debug
            //$temp_pdb_name = "cb9-gt-s-mepho_".
            //    (string)((int)$ou_col[0])."-".
            //    (string)((int)$ou_col[1])."-".
            //    (string)((int)$ou_col[2])."-zd".
            //    (string)((int)$ou_col[3])."-yd".
            //    (string)((int)$ou_col[4])."-zd".
            //    (string)((int)$ou_col[5])."d.pdb";
            $temp_pdb_name = "hsa_de-rr_"
                ."x".$ou_col[0]
                ."-y".$ou_col[1]
                ."-z".$ou_col[2]
                ."-zd".$ou_col[3]
                ."-yd".$ou_col[4]
                ."-zd".$ou_col[5].".pdb";
            //echo $temp_pdb_name."\n"; // debug
            $sql = "SELECT ou_contents FROM output_file WHERE ou_file_name = '".$temp_pdb_name."'";
            //echo $sql."\n"; // debug
            $ouContentRow = $db->fetchRow($sql);
      //echo $ouContentRow['ou_contents']; // debug
      $text .= extractPdbByResname($ouContentRow['ou_contents'], $overlapResid)."\n";

            //$pdb_count++;
        }
    }
    return $text;
}

