<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{literal}
<style>
#h1, #h1 a {
	color: #136F8B;
	font-size: 45px;
	font-weight: lighter;
	text-decoration : none;
	margin:5px;
}

#subh1 {
	color: #879dbd;
	font-size: 25px;
	font-weight: lighter;
}
</style>
{/literal}
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>

<link rel="stylesheet" type="text/css" href="plugins/AdminHome/templates/menu.css">
<link rel="stylesheet" href="themes/default/common-admin.css">

</head>
<body>
<span id="h1">Piwik admin</span> &nbsp;
<span><a href='index.php'>Back to Piwik</a></span>
<br /><br />	

<div id="menu">
{include file="AdminHome/templates/menu.tpl"}
</div>

<div style="clear:both;">
</div>

<div id='content'>
{if $content}{$content}{/if}
</div>

<div id="footer" style="border-top:1px solid gray; margin-top:20px;padding-top:10px;">
<a href='?module=Home'>{'General_BackToHomepage'|translate}</a>

</div>
