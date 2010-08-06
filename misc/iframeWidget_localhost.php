<?php
$date = date('Y-m-d');
$period = 'month';
$idSite = 1;
?>
<html>
<body>
<h3 style="color:#143974">Embedding the Piwik Country widget in an Iframe</h3>
<p>Loads a widget from localhost/trunk/ with login=root, pwd=test</p>
<div id="widgetIframe"><iframe width="500" height="350" 
src="http://localhost/trunk/index.php?token_auth=0b809661490d605bfd77f57ed11f0b14&module=Widgetize&action=iframe&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=<?=$idSite;?>&period=<?=$period;?>&date=<?=$date;?>&disableLink=1" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>

</body></html>
