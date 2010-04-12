<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Piwik &rsaquo; {'Installation_Installation'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<link rel="stylesheet" type="text/css" href="themes/default/common.css" />
<link rel="stylesheet" type="text/css" href="libs/jquery/themes/base/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="themes/default/styles.css" />

<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="libs/jquery/jquery-ui.js"></script>
<script type="text/javascript" src="libs/jquery/fdd2div-modified.js"></script>

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

{literal}
<style type="text/css">
div.both {
	clear: both;
}

body {
	background-color: #F9F9F9;
	text-align: center;
	font-family:Georgia,"Times New Roman",Times,serif;
	font-size:19px;
}

#title{
	font-size:50px;
	color:#284F92;
}

#subtitle{
	font-size:30px;
	color:#C7D8D2;
}

#logo {
	padding:30px;
}

h1 {
	font-size:20px;
	color:#666666;
	border-bottom:1px solid #DADADA;
	padding:0 0 7px;
}

h3 {
	margin-top:10px;
	font-size:17px;
	color:#3F5163;
}

.topBarElem {
	font-family:arial,sans-serif !important;
	font-size:13px;
	line-height:1.33;
}

.error {
	color:red;
	font-size:100%;
	font-weight:bold;
	border: 1px solid red;
	width: 550px;
	padding:20;
}
.error img {
	border:0;
	float:right;
	margin:10;
}
.success {
	color:#26981C;
	font-size:130%;
	font-weight:bold;
	padding:10;	
}
.warning {
	margin:10px;
	color:#ff5502;
	font-size:130%;
	font-weight:bold;
	padding:10px 20px 10px 30px;	
	border: 1px solid #ff5502;
}

.warning ul {
	list-style:disc;
}

.success img, .warning img {
	border:0;
	vertical-align:bottom;
}

#detailInstall {
	width:70%;
	float: right;
}

/* Cadre general */
#main {
	margin: 5px;
	margin-top:30px;
	text-align: left;
}

#content {
	font-size: 90%;
	line-height: 1.4em;
	width: 860px;
	border: 1px solid #3B62AF;
	text-align: $rightouleft;
	margin: auto;
	background: #FFFFFF;
	padding: 0.2em 2em 2em 2em;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
}
/* form errors */
#adminErrors {
	color:#FF6E46;
	font-size:120%;
}
/* listing all the steps */
#generalInstall {
	width: 30%;
	float: left;
	font-size:90%;
}

#generalInstall ul {
	list-style-type: decimal;
}
li.futureStep {
	color: #d3d3d3;
}
li.actualStep {
	font-weight: bold;
}
li.pastStep {
	color: #008000;
}

p.nextStep a {
	font-weight: bold;
	padding: 0.5em;
	color: #ae0000;
	text-decoration: underline;
	float:right;
	font-size:35px;
	line-height:1em;
}

td {
	border-color:#FFFFFF rgb(198, 205, 216)  rgb(198, 205, 216) rgb(198, 205, 216) ;
	border-style:solid;
	border-width:1px;
	color:#203276;
	padding:0.5em 0.5em 0.5em 0.8em;
}

.submit {
	text-align:center;
}
.submit input{
	margin-top:15px;
	background:transparent url(./themes/default/images/background-submit.png) repeat scroll 0%;
	font-size:1.4em;
	border-color:#CCCCCC rgb(153, 153, 153) rgb(153, 153, 153) rgb(204, 204, 204);
	border-style:double;
	border-width:3px;
	color:#333333;
	padding:0.15em;
}

input {
	font-size:1.1em;
	border-color:#CCCCCC rgb(153, 153, 153) rgb(153, 153, 153) rgb(204, 204, 204);
	border-width:1px;
	color:#3A2B16;
	padding:0.15em;
}
</style>
{/literal}
</head>
<body>

<div id="main">
	<div id="content">
		<div id="logo">
			<span id="title">Piwik</span> &nbsp;&nbsp;&nbsp;<span id="subtitle"># {'General_OpenSourceWebAnalytics'|translate}</span>
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
