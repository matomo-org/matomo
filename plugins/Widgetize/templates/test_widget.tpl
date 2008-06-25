{literal}
<script type="text/javascript" src="http://widgets.clearspring.com/launchpad/include.js"></script>
<h1>Testing piwik widgets in Iframe</h1>
<p>Now testing to embed piwik Iframe in other services such as Netvibes, Igoogle, mac dashboard, vista dashboard, etc.</p>
<p><a href='http://piwik.org/'>piwik is an open source web analytics software</a></p>
<p>This feature is made possible using the <a href='http://clearspring.com'>clearspring.com</a> widget converter.</p>
<h2>Test table Iframe</h2>
<div id="PiwikWidgetTest1">
<iframe 
 width="500" height="270" 
 src="{/literal}{$url1}{literal}" 
 scrolling="no" frameborder="0" marginheight="0" marginwidth="0">
</iframe>
</div>

<script type="text/javascript">
$Launchpad.ShowButton({userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik example", source: "PiwikWidgetTest1"});
</script>
<br /><br /><br />
<br /><br /><br />
<h2>Test flash graph </h2>
<div id="PiwikWidgetTest2">
<iframe 
 width="500" height="260" 
 src="{/literal}{$url2}{literal}" 
 scrolling="no" frameborder="0" marginheight="0" marginwidth="0">
</iframe>
</div>

<script type="text/javascript">
$Launchpad.ShowButton({userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik example", source: "PiwikWidgetTest2"});
</script>

<h2>Test tag cloud </h2>
<div id="PiwikWidgetTest3">
<iframe 
 width="500" height="150" 
 src="{/literal}{$url3}{literal}" 
 scrolling="no" frameborder="0" marginheight="0" marginwidth="0">
</iframe>
</div>

<script type="text/javascript">
$Launchpad.ShowButton({userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik example", source: "PiwikWidgetTest3"});
</script>
{/literal}