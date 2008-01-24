{literal}
<script type="text/javascript" src="http://widgets.clearspring.com/launchpad/include.js"></script>
<h2>Test Iframe > embed in netvibes / igoogle / vista dashboard / mac dashboard / etc. </h2>
<div id="PiwikWidgetTest">
<iframe 
 width="400" height="420" 
 src="{/literal}{$url1}{literal}" 
 scrolling="no" frameborder="0" marginheight="0" marginwidth="0">
</iframe>
</div>

<script type="text/javascript">
$Launchpad.ShowButton({userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik test_widget", source: "PiwikWidgetTest"});
</script>

<h2>Test JS include  > embed in netvibes / igoogle / vista dashboard / mac dashboard / etc. </h2>
<div id="PiwikWidgetTestJs">
<script type="text/javascript" src="{/literal}{$url2}{literal}"></script>
</div>

<script type="text/javascript">
$Launchpad.ShowButton({userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik test_widgetJs", source: "PiwikWidgetTestJs"});
</script>

{/literal}