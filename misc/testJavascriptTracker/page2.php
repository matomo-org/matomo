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

<a href="http://www.google.fr"> Site web de google france </a> <br />
<a href="http://www.yahoo.fr"> Site web de yahoo france </a> <br />
<a href="http://www.google.com"> Site web de google monde </a> <br />
<a href="http://maps.google.fr"> Site web de google maps </a> <br />
<a href="http://piwik.org"> Site web de piwik </a> <br />
<a href="http://piwik.org/blog"> Site web de piwik </a> <br />
<br />
<a href="./test.pdf"> download pdf (rel) </a> <br />
<a href="./test.jpg"> download jpg (rel) </a> <br />
<a href="./test.zip"> download zip (rel) </a> <br />
<a href="
<?php echo $url; ?>
test.rar"> download rar (abs) </a> <br />
<br />
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