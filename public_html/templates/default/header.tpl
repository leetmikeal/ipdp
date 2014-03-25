<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="ja" />
<title>{{{$title}}}</title>
<link href="{{{$smarty.const.TEMPLATE_URL}}}main.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="wrapper">
    <div id="header">
        <div class="inner">
            <h1><img src="{{{$smarty.const.TEMPLATE_URL}}}images/titlelogo.gif" alt="Input File Preprocesser and Distributed Processing" /></h1>
            <div id="headnavi">
                <ul>
                    {{{assign var=naviclass_$menu value=' class="active"' }}}
                    <li><a href="{{{$smarty.const.SITE_URL}}}"{{{$naviclass_1}}}><span><span>トップ</span></span></a></li>
                    <li><a href="{{{$smarty.const.SITE_URL}}}new.php"{{{$naviclass_2}}}><span><span>新規プロジェクト</span></span></a></li>
                    <li><a href="{{{$smarty.const.SITE_URL}}}inputfile.php"{{{$naviclass_3}}}><span><span>入力ファイル</span></span></a></li>
                    <li><a href="{{{$smarty.const.SITE_URL}}}configure.php"{{{$naviclass_4}}}><span><span>環境設定</span></span></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="middle">
