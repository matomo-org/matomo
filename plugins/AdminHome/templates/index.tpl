<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html>
<head>
</head>
<body>

<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>

<link rel="stylesheet" type="text/css" href="plugins/AdminHome/templates/menu.css">
<link rel="stylesheet" href="themes/default/common-admin.css">
<div id="menu">
{include file="AdminHome/templates/menu.tpl"}
</div>

<div style="clear:both;">
</div>

<div id='content'>
{if $content}{$content}{/if}
</div>

<div id="footer" style="border-top:1px solid gray; margin-top:20px;padding-top:10px;">
<a href='?module=Home'>Back to Piwik homepage</a>

</div>
