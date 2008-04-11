<?php

require_once '../../modules/Url.php';
$url = Piwik_Url::getCurrentUrlWithoutFileName();
$urlPiwik = join("/", array_slice(split("/", $url), 0, -3));

?>

<div>
You should update the piwik javascript code at the bottom of this page if needed.<br />
And test the tracker by clicking all the links below, with several browsers...<br />
<br />
</div>

<a href="mailto:test@test.com"> mailto test@test.com</a> <br />
<a href="http://www.yahoo.fr"> yahoo france website</a> <br />
<a href="http://www.yahoo.fr/index?test=test2&p_______=idugiduagi8*&*$&%(*^"> yahoo france website</a> <br />
<a href="http://www.google.com"> google world website </a> <br />
<a href="ftp://parcftp.xerox.com"> FTP xerox</a> <br />
<a href="news://news.eclipse.org"> News::eclipse</a> <br />
<a href="http://piwik.org"> piwik website </a> <br />
<a href="http://www.testWithImageAndText.com"><img border=0 src="../../plugins/UserCountry/flags/fr.png"> Test with image + text </a> <br />
<a href="http://www.testWithImageOnly.com"><img border=0 src="../../plugins/UserCountry/flags/pl.png"></a> <br />

<br />
<a class="piwik_ignore" href="./THIS_PDF_SHOULD_NOT_BE_COUNTED.pdf"> PDF wthdownload pdf </a> <br />
<a href="./test.pdf"> download pdf (rel) </a> <br />
<a href="./dir_test/test.pdf"> download pdf + directory (rel) </a> <br />
<a href="../testJavascriptTracker/dir_test/test.pdf"> download pdf + parent directory (rel) </a> <br />
<a href="./test.jpg"> download jpg (rel) </a> <br />
<a href="./test.zip"> download zip (rel) </a> <br />
<a href="./test.php?fileToDownload=test.zip"> download strange URL ?file=test.zip</a> <br />
<a href="
<?php echo $url; ?>
test.rar"> download rar (abs) </a> <br />
<br />
<a href="./page2.php"> Next (rel)</a> <br />
<a href="<?php echo $url; ?>page2.php"> Next (abs)</a> <br />



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