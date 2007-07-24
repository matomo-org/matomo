<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$title}</title>
	<link rel="stylesheet" type="text/css" href="{$subdir}media/style.css">
	<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
</head>
<body>

<table border="0" cellspacing="0" cellpadding="0" height="48" width="100%">
  <tr>
	<td class="header-top-left"><img src="{$subdir}media/logo.png" border="0" alt="phpDocumentor {$phpdocver}" /></td>
    <td class="header-top-right">{$package}<br /><div class="header-top-right-subpackage">{$subpackage}</div></td>
  </tr>
  <tr><td colspan="2" class="header-line"><img src="{$subdir}media/empty.png" width="1" height="1" border="0" alt=""  /></td></tr>
  <tr>
    <td colspan="2" class="header-menu">
  		  [ <a href="{$subdir}classtrees_{$package}.html" class="menu">class tree: {$package}</a> ]
		  [ <a href="{$subdir}elementindex_{$package}.html" class="menu">index: {$package}</a> ]
		  [ <a href="{$subdir}elementindex.html" class="menu">all elements</a> ]
    </td>
  </tr>
  <tr><td colspan="2" class="header-line"><img src="{$subdir}media/empty.png" width="1" height="1" border="0" alt=""  /></td></tr>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr valign="top">
    <td width="195" class="menu">
		<div class="package-title">{$package}</div>
{if count($ric) >= 1}
  <div class="package">
	<div id="ric">
		{section name=ric loop=$ric}
			<p><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></p>
		{/section}
	</div>
	</div>
{/if}
{if $hastodos}
  <div class="package">
	<div id="todolist">
			<p><a href="{$subdir}{$todolink}">Todo List</a></p>
	</div>
	</div>
{/if}
      <b>Packages:</b><br />
  <div class="package">
      {section name=packagelist loop=$packageindex}
        <a href="{$subdir}{$packageindex[packagelist].link}">{$packageindex[packagelist].title}</a><br />
      {/section}
	</div>
      <br />
{if $tutorials}
		<b>Tutorials/Manuals:</b><br />
  <div class="package">
		{if $tutorials.pkg}
			<strong>Package-level:</strong>
			{section name=ext loop=$tutorials.pkg}
				{$tutorials.pkg[ext]}
			{/section}
		{/if}
		{if $tutorials.cls}
			<strong>Class-level:</strong>
			{section name=ext loop=$tutorials.cls}
				{$tutorials.cls[ext]}
			{/section}
		{/if}
		{if $tutorials.proc}
			<strong>Procedural-level:</strong>
			{section name=ext loop=$tutorials.proc}
				{$tutorials.proc[ext]}
			{/section}
	</div>
		{/if}
{/if}
      {if !$noleftindex}{assign var="noleftindex" value=false}{/if}
      {if !$noleftindex}
      {if $compiledfileindex}
      <b>Files:</b><br />
      {eval var=$compiledfileindex}
      {/if}
      <br />
      {if $compiledinterfaceindex}
      <b>Interfaces:</b><br />
      {eval var=$compiledinterfaceindex}
      {/if}
      {if $compiledclassindex}
      <b>Classes:</b><br />
      {eval var=$compiledclassindex}
      {/if}
      {/if}
    </td>
    <td>
      <table cellpadding="10" cellspacing="0" width="100%" border="0"><tr><td valign="top">

{if !$hasel}{assign var="hasel" value=false}{/if}
{if $eltype == 'class' && $is_interface}{assign var="eltype" value="interface"}{/if}
{if $hasel}
<h1>{$eltype|capitalize}: {$class_name}</h1>
Source Location: {$source_location}<br /><br />
{/if}