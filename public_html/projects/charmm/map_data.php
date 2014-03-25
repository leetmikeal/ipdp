<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// map common library
include_once('map.php');

// DB接続
$db = DbManager::getConnection();

$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}
$type = "";
if(isset($_GET['type']) === true) {
  $type = htmlentities($_GET['type']);
}

$dirpath = '../../data_files';

$sql = 'SELECT ru_id, ru_file_name FROM run WHERE ru_id = '.$ru_id;
$ruRow = $db->fetchRow($sql);
$ma_file_name = $ru_id."_map_".$ruRow['ru_file_name'];

if($showRun === false) {
	COMRedirect(SITE_URL);
} else {
    $temp_name = tempnam($dirpath, "map");
    $mf_handle = fopen($temp_name, "w");

    $sql = 'SELECT di_id, di_variable, di_min FROM divided_run WHERE di_ru_id = '.$ru_id.'ORDER BY di_id ASC';
    $diRow = $db->fetchAll($sql);

    fwrite($mf_handle, "#  x   y  min\n");

    $varPrev = 0;
    $m = 0;
    $n = 0;
    for($i = 0; $i < count($diRow); $i++) {
        $varCol = explode(" ", $diRow[$i]['di_variable']);
        
        for($j = 0; $j < count($varCol); $j++) {
            $varArr[$j] = explode("=", $varCol[$j]);
        }
        if($i != 0) {
            if($varArr[0][1] != $varPrev) {
                $n = 0;
                $m++;
            }
        }
        for($j = 0; $j < count($varCol); $j++) {
            $table[$m][$n][$j] = $varArr[$j][1];
        }
        $table[$m][$n][2] = $diRow[$i]['di_min'];

        $varPrev = $varArr[0][1];
        $n++;
    }

    // cyclic
    $col_max[0] = count($table)-1;
    $col_max[1] = count($table[0])-1;

    $cyclicFlag1 = true;
    for($i = 0; $i < count($table); $i++ ) {
      //echo $table[$i][0][0]."<br />\n";
      if(intval($table[$i][0][0]) >= 360) {
        $cyclicFlag1 = false;
      }
    }
    $cyclicFlag2 = true;
    for($i = 0; $i < count($table); $i++ ) {
      //echo $table[0][$i][1]."<br />\n";
      if(intval($table[0][$i][1]) >= 360) {
        $cyclicFlag2 = false;
      }
    }
    //echo $cyclicFlag1." ".$cyclicFlag2;
    //exit();
    if($cyclicFlag1) {
      $val1 = $table[0][$col_max[1]][1];
      $val2 = $table[0][$col_max[1]-1][1];
      $val = $val1 + ($val1 - $val2);
      for($i = 0; $i < $col_max[0]; $i++) {
          $table[$i][$col_max[1]][0] = $table[$i][0][0];
          $table[$i][$col_max[1]][1] = strval($val);
          $table[$i][$col_max[1]][2] = $table[$i][0][2];
      }
    }
    if($cyclicFlag2) {
      $val1 = $table[$col_max[0]][0][0];
      $val2 = $table[$col_max[0]-1][0][0];
      $val = $val1 + ($val1 - $val2);
      for($i = 0; $i < $col_max[1]; $i++) {
        $col_max_m1 = $col_max[1] - 1;
        $table[$col_max[0]][$i][0] = strval($val);
        $table[$col_max[0]][$i][1] = $table[0][$i][1];
        $table[$col_max[0]][$i][2] = $table[0][$i][2];
      }
    }
    if($cyclicFlag1 && $cyclicFlag2) {
      $table[$col_max[0]][$col_max[1]][0] = $table[$col_max[0]][0][0];
      $table[$col_max[0]][$col_max[1]][1] = $table[0][$col_max[1]][1];
      $table[$col_max[0]][$col_max[1]][2] = $table[0][0][2];
    }
    //var_dump($table);
    //exit();

    // table to col
    $col_max[0] = count($table);
    $col_max[1] = count($table[0]);

    $count = 0;
    for($i = 0; $i < ($col_max[0]); $i++) {
        for($j = 0; $j < ($col_max[1]); $j++) {
            $col[0][$count] = $table[$i][$j][0];
            $col[1][$count] = $table[$i][$j][1];
            $col[2][$count] = $table[$i][$j][2];
            $count++;
        }
        $col[0][$count] = 0;
        $col[1][$count] = 0;
        $col[2][$count] = "";
        $count++;
    }
//    var_dump($table);
//    var_dump($col);

    // number of array
    $col1_arr = array_unique($col[0]);
    sort($col1_arr, SORT_NUMERIC);
    $col1_max = count($col1_arr);
    $col2_arr = array_unique($col[1]);
    sort($col2_arr, SORT_NUMERIC);
    $col2_max = count($col2_arr);

    // getting maximum and minimum
    $di_max = 10000;
    $di_min = -10000;

    $colval_arr = $col[2];
    sort($colval_arr, SORT_NUMERIC);
    $ca_val = array_shift($colval_arr);
    while ($ca_val == "") { // delete empty column
      $ca_val = array_shift($colval_arr);
    }
    //if($colval_arr[0] > $di_min) $di_min = $colval_arr[0];
    if($ca_val > $di_min) $di_min = $ca_val;
    //rsort($colval_arr, SORT_NUMERIC);
    if($colval_arr[count($colval_arr)-1] < $di_max) $di_max = $colval_arr[count($colval_arr)-1];

    $count = 0;
    for($i = 0; $i < ($col1_max + 1); $i++) {
        for($j = 0; $j < ($col2_max + 1); $j++) {
            if($col[2][$count] != "") {
                // fix value
                if($col[2][$count] > $di_max) $col[2][$count] = $di_max;
                if($col[2][$count] < $di_min) $col[2][$count] = $di_min;

                $ma_buf = $col[0][$count]." ".$col[1][$count]." ".$col[2][$count];
            } else {
                $ma_buf = "";
            }
            fwrite($mf_handle, $ma_buf."\n");
            $count++;
        }
        //fwrite($mf_handle, "\n");
    }

    fclose($mf_handle);

    //$ma_file_path = getcwd().'$dirpath/'.$ma_file_name.'.dat';
    $ma_file_path = $dirpath.'/'.$ma_file_name.'.dat';
    exec('mv '.$temp_name.' '.$ma_file_path);
    
    $gp_file = tempnam($dirpath, "df");
    $gp_handle = fopen($gp_file, "w");
    //$title = $ruRow['ru_file_name'];
    $format = 'png';
    //$range = 90;
    //$zrange_min = $di_min;
    //$zrange_max = $di_max;
    //$spline = 5;
    if(!in_array($type, array('surface', 'contour'))) {
      $type = "surface";
    }
    $script = getGnuplotScript(
      array(
        'dirpath' => $dirpath.'/',
        'filename' => $ma_file_name,
        'format' => 'png',
        //'title' => $ruRow['ru_file_name'],
        'title' => $ma_file_name,
        'range' => 30,
        'spline' => 5,
        'zrange_max' => $di_max,
        'zrange_min' => $di_min
      ),
      $type // surface
    );
    fwrite($gp_handle, $script);
    fclose($gp_handle);
    //echo $script;
    exec('gnuplot '.$gp_file);
    unlink($gp_file);
    header('Content-type: image/'.$format);
    readfile($dirpath.'/'.$ma_file_name.'.'.$format);
}

/*function getGnuplotScript($sets = array()) {
  $dirpath = ''; if(!empty($sets['dirpath'])) $dirpath = $sets['dirpath'];
  $filename = ''; if(!empty($sets['filename'])) $filename = $sets['filename'];
  $format = ''; if(!empty($sets['format'])) $format = $sets['format'];
  $title = ''; if(!empty($sets['title'])) $title = $sets['title'];
  $range = ''; if(!empty($sets['range'])) $range = $sets['range'];
  $spline = ''; if(!empty($sets['spline'])) $spline = $sets['spline'];
  $zrange_max = ''; if(!empty($sets['zrange_max'])) $zrange_max = $sets['zrange_max'];
  $zrange_min = ''; if(!empty($sets['zrange_min'])) $zrange_min = $sets['zrange_min'];
  $script = <<<_EOT_
set pm3d interpolate $spline, $spline map
set terminal $format font "Verdana" 15 size 600,600
set out "$dirpath$filename.$format"
#set title "$title"
#set palette rgbformulae 3,2,2
#set palette model HSV rgbformulae 3,2,2
#set palette rgbformulae "blue", "green", "red"
#set palette defined ( 0 "#ff0000", 1 "#ffff00", 2 "#00ff00", 3 "#00ffff", 4 "#0000ff")
set palette defined ( 0 "#0000ff", 1 "#00ffff", 2 "#00ff00", 3 "#ffff00", 4 "#ff0000")
set size ratio 1
set xtics $range
set xrange [0:360]
set xlabel "phi"
set ytics $range
set yrange [0:360]
set ylabel "psi"
set zrange [$zrange_min:$zrange_max]
splot '$dirpath$filename.dat' title "$title" w pm3d
_EOT_;
  return $script;
}
*/
