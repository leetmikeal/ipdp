<?php /* Smarty version 2.6.26, created on 2013-03-08 21:56:31
         compiled from map_show.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'COMGetFormatDateTime', 'map_show.html', 7, false),array('modifier', 'COMGetFormatSecond', 'map_show.html', 9, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../templates/".(@TEMPLATE)."/header.tpl", 'smarty_include_vars' => array('title' => "map show | ".($this->_tpl_vars['ruRow']['ru_file_name']),'menu' => '0')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<div class="container">
		<table>
			<tr><td>ID</td><td><?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
</td></tr>
			<tr><td>ファイル名</td><td><?php echo $this->_tpl_vars['ruRow']['ru_file_name']; ?>
</td></tr>
			<tr><td>日時</td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['ruRow']['ru_datetime'])) ? $this->_run_mod_handler('COMGetFormatDateTime', true, $_tmp) : COMGetFormatDateTime($_tmp)); ?>
</td></tr>
			<tr><td>終了/分割</td><td><?php echo $this->_tpl_vars['enddi']; ?>
/<?php echo $this->_tpl_vars['alldi']; ?>
</td></tr>
			<tr><td>総計算時間</td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['allTime'])) ? $this->_run_mod_handler('COMGetFormatSecond', true, $_tmp) : COMGetFormatSecond($_tmp)); ?>
</td></tr>
			<tr><td>平均計算時間</td><td><?php echo $this->_tpl_vars['averageTime']; ?>
s</td></tr>
			<tr><td>終了までの残り計算時間<br />（平均計算時間×残り）</td>
				<td><?php echo ((is_array($_tmp=$this->_tpl_vars['estimateTime'])) ? $this->_run_mod_handler('COMGetFormatSecond', true, $_tmp) : COMGetFormatSecond($_tmp)); ?>
<br /> 
					※計算が終了してから、次の計算に移るまでの時間を一秒として算出しています。
				</td>
			</tr>
			<tr><td>終了まであと</td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['nokoriKeikaTime'])) ? $this->_run_mod_handler('COMGetFormatSecond', true, $_tmp) : COMGetFormatSecond($_tmp)); ?>
</td></tr>
			<tr><td>終了日時</td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['endDateTime'])) ? $this->_run_mod_handler('COMGetFormatDateTime', true, $_tmp) : COMGetFormatDateTime($_tmp)); ?>
</td></tr>
			<tr><td>&emsp;</td>
				<td>
					<a href="show_original_script.php?id=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
" target="_blank">オリジナルrunファイルを見る</a>
					&emsp;&emsp;&emsp;&emsp;
					<a href="show_analyzed_script.php?id=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
" target="_blank">解析済runファイルを見る</a>
				</td>
			</tr>
		</table>
    </div>
    <div class="container" style="display:none;">
    <br />
		<form name="" method="post" action="maper_starter.php">
			<table>
				<tr>
					<td>
<?php if (IsMaperRun ( ) == true): ?>
					現在集計中です。
<?php else: ?>
	<?php $_from = $this->_tpl_vars['varAry']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['var']):
?>
						<input type="checkbox" name="<?php echo $this->_tpl_vars['var'][0]; ?>
" id="<?php echo $this->_tpl_vars['var'][0]; ?>
" value="<?php echo $this->_tpl_vars['var'][0]; ?>
" /> <?php echo $this->_tpl_vars['var'][0]; ?>
&emsp;
	<?php endforeach; endif; unset($_from); ?>
						<input type="hidden" name="run" id="run" value="<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
" />
						<input type="submit" value="再集計する" />
<?php endif; ?>
					</td>
				</tr>
			</table>
		</form>
	</div>

    <div class="container">
        <h3>GNUPlotで描画</h3>
        <a href="map_data.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&type=surface" target="_blank">Surface マップを表示</a><br />
        <a href="map_data.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&type=contour" target="_blank">Contour マップを表示</a>
    </div>

    <div class="container">
        <h3>ファイルダウンロード</h3>
        データファイルとGNUPlotスクリプトファイルをダウンロードし、以下のコマンドを実行するとPNG形式の画像ファイルが作成されます。
<pre>
gnuplot [script_file]</pre>
        デザインの変更やファイル形式の変更が必要な場合は、スクリプトファイルを書き換えてください。<br />
        <a href="map_download.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&type=csv&form=1">データファイル</a><br />
        <a href="map_gnuplot.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&type=surface">GNUPlot用スクリプト(Surafce)ファイル</a><br />
        <a href="map_gnuplot.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&type=contour">GNUPlot用スクリプト(Contour)ファイル</a><br />
                    </div>

	<div class="container">
        		<br />
		<table>
			<th>ID</th>
			<th>変数</th>
			<th>min</th>
			<th>&emsp;</th>
<?php $_from = $this->_tpl_vars['diRow']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['di']):
?>
			<tr>
				<td><?php echo $this->_tpl_vars['di']['di_id']; ?>
</td>
				<td><?php echo $this->_tpl_vars['di']['di_variable']; ?>
</td>
				<td><?php echo $this->_tpl_vars['di']['di_min']; ?>
</td>
                <td><a href="../../divided_run.php?run=<?php echo $this->_tpl_vars['ruRow']['ru_id']; ?>
&di=<?php echo $this->_tpl_vars['di']['di_id']; ?>
" target="_blank">このエネルギーを出した計算を見る</a></td>
			</tr>
<?php endforeach; endif; unset($_from); ?>
		</table>
	</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../templates/".(@TEMPLATE)."/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>