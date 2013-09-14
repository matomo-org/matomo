<?php
use Piwik\FrontController;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\WidgetsList;

exit;
$date = date('Y-m-d');
$period = 'month';
$idSite = 1;
$url = "http://localhost/trunk/index.php?token_auth=0b809661490d605bfd77f57ed11f0b14&module=Widgetize&action=iframe&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=$idSite&period=$period&date=$date&disableLink=1";

?>
<html>
<body>
<h3 style="color:#143974">Embedding the Piwik Country widget in an Iframe</h3>

<p>Loads a widget from localhost/trunk/ with login=root, pwd=test. <a href='<?= $url ?>'>Widget URL</a></p>

<div id="widgetIframe">
    <iframe width="500" height="350"
            src="<?php echo $url; ?>" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe>
</div>

<br/>

<?php
$_GET['idSite'] = $idSite;
define('PIWIK_INCLUDE_PATH', '../..');
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";

FrontController::getInstance()->init();
$widgets = WidgetsList::get();
foreach ($widgets as $category => $widgetsInCategory) {
    echo '<h2>' . $category . '</h2>';
    foreach ($widgetsInCategory as $widget) {
        echo '<h3>' . $widget['name'] . '</h3>';
        $widgetUrl = UrlHelper::getArrayFromQueryString($url);
        $widgetUrl['moduleToWidgetize'] = $widget['parameters']['module'];
        $widgetUrl['actionToWidgetize'] = $widget['parameters']['action'];
        $parameters = $widget['parameters'];
        unset($parameters['module']);
        unset($parameters['action']);
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $value = current($value);
            }
            $widgetUrl[$name] = $value;
        }
        $widgetUrl = Url::getQueryStringFromParameters($widgetUrl);

        echo '<div id="widgetIframe"><iframe width="500" height="350"
			src="' . $widgetUrl . '" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';

    }
}
?>
</body>
</html>
