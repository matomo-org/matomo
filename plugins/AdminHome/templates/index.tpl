<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html>
<head>
</head>
<body>
<link rel="stylesheet" type="text/css" href="plugins/AdminHome/templates/menu.css">

<div id="menu">
{include file="AdminHome/templates/menu.tpl"}
</div>

<div style="clear:both;">
</div>

<div id='content'>
{if $content}{$content}{/if}
</div>

