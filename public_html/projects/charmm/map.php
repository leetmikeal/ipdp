<?php


function prjGetHtml($db, $ruRow, $diRow, $auguments = array())
{
  //$sql = 'SELECT ru_software, ru_project FROM run WHERE ru_id = '.$ruRow['ru_id'];
  //$prRow = $db->fetchRow($sql);
  $prRow = getRunInfo($db, $ruRow['ru_id']);
  // e.g.) projects/charmm/docking.php
  $project_path = DIRECTORY_SEPARATOR.DIR_PROJECT.DIRECTORY_SEPARATOR.$prRow['ru_software'].DIRECTORY_SEPARATOR.$prRow['ru_project']; // ~~~.php

  //print_r($auguments);
  $html = "";
  if(isset($auguments["pj"]) && $auguments["pj"]) {
    
  	return $html;
  }
  $sql = "SELECT COUNT(*) FROM divided_run WHERE di_min = '' and di_ru_id = ".$ruRow['ru_id'];
  $chkRow = $db->fetchRow($sql);
  
  //$sql = 'SELECT COUNT(*) FROM map WHERE ma_ru_id ='.$ruRow['ru_id'];
  //$maRow = $db->fetchRow($sql);
  //$maCount = intval($row['count']);
  
  $varAry = array();
  
  if(count($diRow) > 0)
  {
  	$varAry = SpritVariable($diRow[0]['di_variable']);
  }
  
  $html .= '<hr />'."\n";

  if($chkRow['count'] != 0) {
  //if($maCount == 0) {
//    $html .= <<<_EOT_
//<form name="" method="get" action="${project_path}_starter.php">
//_EOT_;
    if( IsMaperRun() ) {
        $html .= '現在集計中です。';
    } else {
      $default_filename = "cta-energy.dat";
        //foreach($varAry as $var) {
        //    $html .= '<input type="checkbox" name="'.$var[0].'" id="'.$var[0].'" value="'.$var[0].'" /> '.$var[0].'&emsp;'."\n";
        //}
        //$html .= '<br />'."\n";
        //$html .= '<h3>集計を行う</h3>'."\n";
        //$html .= 'Energy Column (0 start): <input type="text" name="column" id="column" value="10"><br />'."\n";
        //$html .= 'Name of output file with energy values: <input type="text" name="file" id="file" value="'.$default_filename.'" /><br />'."\n";
        //$html .= '<input type="hidden" name="run" id="run" value="'.$ruRow['ru_id'].'" />'."\n";
        //$html .= '<input type="submit" value="集計開始" />'."\n";
        $html .= '<a href="'.$project_path.'_select.php?run='.$ruRow['ru_id'].'">集計を行う(出力ファイルを選択)</a>'."\n";
    }
    $html .= '<hr />'."\n";
    $html .= <<<_EOT_
<hr />
_EOT_;
  } else {
    $html .= '<a href="'.$project_path.'_show.php?run='.$ruRow['ru_id'].'">集計結果を表示</a>      |        '."\n";
    $html .= '<a href="'.$project_path.'_reset.php?run='.$ruRow['ru_id'].'&reset=0">消去</a>'."\n";
    $html .= '<hr />'."\n";
  }
  
  $html .= '<ul><li><a href="'.$project_path.'_showdata.php?run='.$ruRow['ru_id'].'">分割保存された出力ファイルを一括で取得</a></li></ul>';
  
  return $html;

}

function getGnuplotScript($sets = array(), $type = "surface") {
  $dirpath = ''; if(!empty($sets['dirpath'])) $dirpath = $sets['dirpath'];
  $filename = ''; if(!empty($sets['filename'])) $filename = $sets['filename'];
  $format = ''; if(!empty($sets['format'])) $format = $sets['format'];
  $title = ''; if(!empty($sets['title'])) $title = $sets['title'];
  $range = ''; if(!empty($sets['range'])) $range = $sets['range'];
  $spline = ''; if(!empty($sets['spline'])) $spline = $sets['spline'];
  $zrange_max = ''; if(!empty($sets['zrange_max'])) $zrange_max = $sets['zrange_max'];
  $zrange_min = ''; if(!empty($sets['zrange_min'])) $zrange_min = $sets['zrange_min'];
  
  if ($type == "surface") {
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
  } else if ($type == "contour") {
    $zrange_max = $zrange_min + 14;
    $script = <<<_EOT_
#set pm3d interpolate $spline, $spline map
set pm3d map
#set view 0,0
#set size 1,1
#set size ratio 1.0
#set size square 0.5, 0.5
set terminal $format font "Verdana" 16 size 600,600
set out "$dirpath$filename.$format"
set title "$title"
#set palette rgbformulae 3,2,2
#set palette model HSV rgbformulae 3,2,2
#set palette rgbformulae "blue", "green", "red"
#set palette defined ( 0 "#ff0000", 1 "#ffff00", 2 "#00ff00", 3 "#00ffff", 4 "#0000ff")
#set palette defined ( 0 "#0000ff", 1 "#00ffff", 2 "#00ff00", 3 "#ffff00", 4 "#ff0000")
set xtics $range
set xrange [0:360]
set xlabel "phi"
set ytics $range
set yrange [0:360]
set ylabel "psi"
set zrange [$zrange_min:$zrange_max]
set contour base
unset surface
set cntrparam cubicspline
set cntrparam levels increment $zrange_min,2.0,$zrange_max
unset key
#set key outside
unset colorbox
#set offset 0, 0, 0, 0
splot '$dirpath$filename.dat' title "$title" w pm3d lw 1
_EOT_;
  }
  return $script;
}

