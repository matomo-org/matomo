
<html>
<head>
<title>Piwik &raquo; {'Installation_Installation'|translate}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
</head>
<body>

{literal}
<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script>
$(document).ready( function(){
	$('#toFade').fadeOut(4000, function(){ $(this).css('display', 'hidden'); } ); 
});
</script>

<style>
DIV.both {
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
	padding:30;
}

h1 {
	font-size:20px;
}

h3 {
	margin-top:10px;
	font-size:17px;
	color:#3F5163;
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
	color:#ff5502;
	font-size:130%;
	font-weight:bold;
	padding:10;	
	border: 1px solid #ff5502;
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

#generalInstall UL {
	list-style-type: decimal;
}
LI.futureStep {
	color: #d3d3d3;
}
LI.actualStep {
	font-weight: bold;
}
LI.pastStep {
	color: #008000;
}

P.nextStep A {
	font-weight: bold;
	padding: 0.5em;
	color: #ae0000;
	text-decoration: underline;
	float:right;
	font-size:35px;
}

#installPercent {
	width: 100%;
	height: 1.5em;
	margin: 0;
	padding: 0;
	background-color: #eee;
	border: 1px solid #ddd;
}
#installPercent P {
	height: 1.5em;
	background-color: #8aaecc;
	margin: 0;
	padding: 0;
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
<div id="main">
	<div id="content">
		<div id="logo">
			<span id="title">Piwik</span> &nbsp;&nbsp;&nbsp;<span id="subtitle"># open source web analytics</span>
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
		
		<h3>{'Installation_InstallationStatus'|translate}</h3>
		
		<div id="installPercent">
		<p style="width: {$percentDone}%;"></p>
	</div>
	
	{'Installation_PercentDone'|translate:$percentDone} 
</div>
