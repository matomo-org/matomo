<?php

require_once '../../core/Url.php';
$url = Piwik_Url::getCurrentUrlWithoutFileName();
$urlPiwik = join("/", array_slice(split("/", $url), 0, -3));
?>

<div>
You should update the piwik javascript code at the bottom of this page if needed.<br />
And test the tracker by clicking all the links below, with several browsers...<br />
<br />
</div>

<script type="text/javascript">
<!--
piwik_ignore_classes = ["no-tracking"];
//-->
</script>
<h1>Ignore classes</h1>
<a href="http://www.yahoo.com">Expecting a yahoo.com outlink</a> <br />
<a href="http://piwik.org" class="piwik_ignore">Ignore this piwik.org outlink</a> <br />
<a href="http://dev.piwik.org" class="no-tracking">Ignore this dev.piwik.org outlink</a> <br />

<script type="text/javascript">
<!--
piwik_download_extensions = ".zip";
//-->
</script>
<style type="text/css">
a.boldlink {font-weight: bold}
</style>
<h1>Multiple classes</h1>
<a href="./test.pdf" class="piwik_download">Track this download pdf (rel) </a> <br />
<a href="./test.jpg" class="boldlink piwik_download">Track this download jpg (rel) </a> <br />
<a href="./test.zip" class="boldlink no-tracking">Ignore this download zip (rel) </a> <br />

<a href="./index.php"> Prev (rel)</a> <br />
<a href="<?php echo $url; ?>index.php"> Prev (abs)</a> <br />

<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="<?php echo $urlPiwik; ?>/piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
piwik_action_name = '';
piwik_idsite = 1;
piwik_url = '<?php echo $urlPiwik; ?>/piwik.php';
piwik_log(piwik_action_name, piwik_idsite,piwik_url);
//-->
</script><object>
<noscript><p>Web analytics <img src="<?php echo $urlPiwik; ?>/piwik.php" style="border:0" alt="piwik"/></p>
</noscript></object></a>
<!-- /Piwik -->

<script type="text/javascript">

var testPkIsSiteHostname = false;
if(testPkIsSiteHostname) {
	// automated testing
	_pk_hosts_alias = ["*.example.com"];
	
	if (_pk_is_site_hostname("localhost")) alert("failed: localhost does not match");
	if (_pk_is_site_hostname("google.com")) alert("failed: google.com does not match");
	if (!_pk_is_site_hostname("example.com")) alert("failed: example.com does match");
	if (!_pk_is_site_hostname("www.example.com")) alert("failed: www.example.com does match");
	if (!_pk_is_site_hostname("www.sub.example.com")) alert("failed: www.sub.example.com does match");
}
</script>

