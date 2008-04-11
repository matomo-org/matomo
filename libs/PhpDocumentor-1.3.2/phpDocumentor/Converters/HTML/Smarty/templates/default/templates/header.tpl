<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>{$title}</title>
	<link rel="stylesheet" type="text/css" id="layout" href="{$subdir}media/layout.css" media="screen">
	<link rel="stylesheet" type="text/css" href="{$subdir}media/style.css" media="all">
	<link rel="stylesheet" type="text/css" href="{$subdir}media/print.css" media="print">
</head>

<body>
<div id="header">
	<div id="navLinks">
		[ <a href="{$subdir}classtrees_{$package}.html">Class Tree: {$package}</a> ]
		[ <a href="{$subdir}elementindex_{$package}.html">Index: {$package}</a> ]
		[ <a href="{$subdir}elementindex.html">All elements</a> ]
	</div>
	<div id="packagePosition">
		<div id="packageTitle2">{$package}</div>
		<div id="packageTitle">{$package}</div>
		<div id="elementPath">{$subpackage} &middot; {$current}</div>
	</div>
</div>

<div id="nav" class="small">
{if count($ric) >= 1}
	<div id="ric">
		{section name=ric loop=$ric}
			<p><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></p>
		{/section}
	</div>
{/if}
{if $hastodos}
	<div id="todolist">
			<p><a href="{$subdir}{$todolink}">Todo List</a></p>
	</div>
{/if}
	<div id="packages">
		Packages:
		{section name=packagelist loop=$packageindex}
			<p><a href="{$subdir}{$packageindex[packagelist].link}">{$packageindex[packagelist].title}</a></p>
		{/section}
	</div>
{if $tutorials}
	<div id="tutorials">
		Tutorials/Manuals:<br />
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
		{/if}
	</div>
{/if}

	{if !$noleftindex}{assign var="noleftindex" value=false}{/if}
	{if !$noleftindex}
		<div id="index">
			<div id="files">
				{if $compiledfileindex}
				Files:<br>
				{eval var=$compiledfileindex}{/if}
			</div>
			<div id="interfaces">
				{if $compiledinterfaceindex}Interfaces:<br>
				{eval var=$compiledinterfaceindex}{/if}
			</div>
			<div id="classes">
				{if $compiledclassindex}Classes:<br>
				{eval var=$compiledclassindex}{/if}
			</div>
		</div>
	{/if}
</div>

<div id="body">
	{if !$hasel}{assign var="hasel" value=false}{/if}
    {if $eltype == 'class' && $is_interface}{assign var="eltype" value="interface"}{/if}
	{if $hasel}
	<h1>{$eltype|capitalize}: {$class_name}</h1>
	<p style="margin: 0px;">Source Location: {$source_location}</p>
	{/if}
