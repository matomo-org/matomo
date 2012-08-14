<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; {'Installation_Installation'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" type="text/css" href="themes/default/common.css" />
<link rel="stylesheet" type="text/css" href="libs/jquery/themes/base/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="themes/default/styles.css" />

<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-ui.js"></script>

{literal}
<script type="text/javascript">
$(document).ready( function(){
	$('#toFade').fadeOut(4000, function(){ $(this).css('display', 'hidden'); } );
	$('input:first').focus();
	$('#progressbar').progressbar({
{/literal}
		value: {$percentDone}
{literal}
	});
});
</script>
{/literal}

<link rel="stylesheet" type="text/css" href="plugins/Installation/templates/install.css" />
{if 'General_LayoutDirection'|translate =='rtl'}
<link rel="stylesheet" type="text/css" href="themes/default/rtl.css" />
{/if}
</head>
<body>
{include file="default/ie6.tpl"}
<div id="main">
	<div id="content">
		<div id="logo">
			<img id="title" width='160' src="themes/default/images/logo.png"/> &nbsp;&nbsp;&nbsp;<span id="subtitle"># {'General_OpenSourceWebAnalytics'|translate}</span>
		</div>
		<div style="float:right" id="topRightBar">
		<br />
		{postEvent name="template_topBar"}
		</div>
		<div class="both"></div>

		<div id="generalInstall">
			{include file="Installation/templates/allSteps.tpl"}
		</div>
		
		<div id="detailInstall">
			{if isset($showNextStepAtTop) && $showNextStepAtTop}
				<p class="nextStep">
					<a href="{url action=$nextModuleName}">{'General_Next'|translate} &raquo;</a>
				</p>
			{/if}
			{include file="$subTemplateToLoad"}
			{if $showNextStep}
				<p class="nextStep">
					<a href="{url action=$nextModuleName}">{'General_Next'|translate} &raquo;</a>
				</p>
			{/if}
		</div>
		
		<div class="both"></div>
		
		<br />
		<br />
		<h3>{'Installation_InstallationStatus'|translate}</h3>
		
		<div id="progressbar"></div>
		{'Installation_PercentDone'|translate:$percentDone} 
	</div>
</div>
</body>
</html>
