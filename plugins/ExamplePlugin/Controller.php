<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExamplePlugin
 */
namespace Piwik\Plugins\ExamplePlugin;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\View;

/**
 *
 * @package ExamplePlugin
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Go to /piwik/?module=ExamplePlugin&action=helloWorld to execute this method
     */
    public function helloWorld()
    {
        echo "<p>Hello world! <br />";
        echo "Happy coding with Piwik :)</p>";
    }

    /**
     * See the result on piwik/?module=ExamplePlugin&action=exampleWidget
     * or in the dashboard > Add a new widget
     */
    public function exampleWidget()
    {
        echo "<p>Hello world! <br /> You can output whatever you want in widgets, and put them on dashboard or everywhere on the web (in your blog, website, etc.).
		<br />Widgets can include graphs, tables, flash, text, images, etc.
		<br />It's very easy to create a new plugin and widgets in Piwik. Have a look at this example file (/plugins/ExamplePlugin/ExamplePlugin.php).
		<div id='happycoding'><em>Happy coding!</em></div>
		<div id='jsenabled'>You can easily use Jquery in widgets</div>
		<p>
		<script type=\"text/javascript\">$('#happycoding').hide().fadeIn(5000);$('#jsenabled').hide().css({'color':'red'}).fadeIn(10000);</script>";
    }

    public function photostreamMatt()
    {
        echo '<object width="400" height="400"> <param name="flashvars" value="offsite=true&lang=en-us&page_show_url=%2Fphotos%2Fmatthieu-aubry%2Fsets%2F72157602308487455%2Fshow%2F&page_show_back_url=%2Fphotos%2Fmatthieu-aubry%2Fsets%2F72157602308487455%2F&set_id=72157602308487455&jump_to=" /> <param name="movie" value="http://www.flickr.com/apps/slideshow/show.swf?v=109615" /> <param name="allowFullScreen" value="true" /><embed type="application/x-shockwave-flash" src="http://www.flickr.com/apps/slideshow/show.swf?v=109615" allowFullScreen="true" flashvars="offsite=true&lang=en-us&page_show_url=%2Fphotos%2Fmatthieu-aubry%2Fsets%2F72157602308487455%2Fshow%2F&page_show_back_url=%2Fphotos%2Fmatthieu-aubry%2Fsets%2F72157602308487455%2F&set_id=72157602308487455&jump_to=" width="400" height="400"></embed></object>';
    }

    /**
     * this widgets shows how to make a remote API request to piwik.org
     * you find the main JS code in templates/piwikDownloads.twig
     */
    public function piwikDownloads()
    {
        $view = new View('@ExamplePlugin/piwikDownloads');
        $this->setGeneralVariablesView($view);
        echo $view->render();
    }

    /**
     * This method displays a text containing an help about "How to build plugins for Piwik".
     * This help is then used on http://piwik.org/docs/plugins/functions
     */
    public function index()
    {
        $out = '';
        $out .= '<em>This page aims to list the different functions you can use when programming plugins for Piwik.</em><br />';
        $out .= '<strong>Be careful, the following APIs may change in the near future as Piwik is still in development.</strong><br />';

        $out .= '<h2>General</h2>';
        $out .= '<h3>Accessible from your plugin controller</h3>';

        $out .= '<code>$this->date</code> = current selected <strong>Date</strong> object (<a href="https://github.com/piwik/piwik/blob/master/core/Date.php">class</a>)<br />';
        $out .= '<code>$period = Common::getRequestVar("period");</code> - Get the current selected period<br />';
        $out .= '<code>$idSite = Common::getRequestVar("idSite");</code> - Get the selected idSite<br />';
        $out .= '<code>$site = new Site($idSite);</code> - Build the Site object (<a href="https://github.com/piwik/piwik/tree/master/core/Site.php">class</a>)<br />';
        $out .= '<code>$this->str_date</code> = current selected date in YYYY-MM-DD format<br />';

        $out .= '<h3>Misc</h3>';
        $out .= '<code>\Piwik\Menu\Main::getInstance()->add( $mainMenuName, $subMenuName, $url );</code> - Adds an entry to the menu in the Piwik interface (See the example in the <a href="https://github.com/piwik/piwik/blob/1.0/plugins/UserCountry/UserCountry.php#L76">UserCountry Plugin file</a>)<br />';
        $out .= '<code>WidgetsList::add( $widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array());</code> - Adds a widget that users can add in the dashboard, or export using the Widgets link at the top of the screen. See the example in the <a href="https://github.com/piwik/piwik/blob/1.0/plugins/UserCountry/UserCountry.php#L70">UserCountry Plugin file</a> or any other plugin)<br />';
        $out .= '<code>Common::prefixTable("site")</code> = <b>' . Common::prefixTable("site") . '</b><br />';

        $out .= '<h2>User access</h2>';
        $out .= '<code>Piwik::getCurrentUserLogin()</code> = <b>' . Piwik::getCurrentUserLogin() . '</b><br />';
        $out .= '<code>Piwik::isUserHasSomeAdminAccess()</code> = <b>' . self::boolToString(Piwik::isUserHasSomeAdminAccess()) . '</b><br />';
        $out .= '<code>Piwik::isUserHasAdminAccess( array $idSites = array(1,2) )</code> = <b>' . self::boolToString(Piwik::isUserHasAdminAccess(array(1, 2))) . '</b><br />';
        $out .= '<code>Piwik::isUserHasViewAccess( array $idSites = array(1) ) </code> = <b>' . self::boolToString(Piwik::isUserHasViewAccess(array(1))) . '</b><br />';
        $out .= '<code>Piwik::isUserIsSuperUser()</code> = <b>' . self::boolToString(Piwik::isUserIsSuperUser()) . '</b><br />';

        $out .= '<h2>Execute SQL queries</h2>';
        $txtQuery = "SELECT token_auth FROM " . Common::prefixTable('user') . " WHERE login = ?";
        $result = Db::fetchOne($txtQuery, array('anonymous'));
        $out .= '<code>Db::fetchOne("' . $txtQuery . '", array("anonymous"))</code> = <b>' . var_export($result, true) . '</b><br />';
        $out .= '<br />';

        $query = Db::query($txtQuery, array('anonymous'));
        $fetched = $query->fetch();
        $token_auth = $fetched['token_auth'];

        $out .= '<code>$query = Db::query("' . $txtQuery . '", array("anonymous"))</code><br />';
        $out .= '<code>$fetched = $query->fetch();</code><br />';
        $out .= 'At this point, we have: <code>$fetched[\'token_auth\'] == <b>' . var_export($token_auth, true) . '</b></code><br />';

        $out .= '<h2>Example Sites information API</h2>';
        $out .= '<code>API::getInstance()->getSitesWithViewAccess()</code> = <b><pre>' . var_export(API::getInstance()->getSitesWithViewAccess(), true) . '</pre></b><br />';
        $out .= '<code>API::getInstance()->getSitesWithAdminAccess()</code> = <b><pre>' . var_export(API::getInstance()->getSitesWithAdminAccess(), true) . '</pre></b><br />';

        $out .= '<h2>Example API  Users information</h2>';
        $out .= 'View the list of API methods you can call on <a href="http://piwik.org/docs/analytics-api/reference">API reference</a><br />';
        $out .= 'For example you can try <code>API::getInstance()->getUsersSitesFromAccess("view");</code> or <code>API::getInstance()->deleteUser("userToDelete");</code><br />';

        $out .= '<h2>Javascript in Piwik</h2>';
        $out .= '<h3>i18n internationalization</h3>';
        $out .= 'In order to translate strings within Javascript code, you can use the javascript function _pk_translate( token );.
				<ul><li>The "token" parameter is the string unique key found in the translation file. For this token string to be available in Javascript, you must
				load it using the Translate.getClientSideTranslationKeys hook.
					</li></ul>';

        $out .= '<h3>Reload a widget in the dashboard</h3>';
        $out .= 'It is sometimes useful to reload one widget in the dashboard (for example, every 20 seconds for a real time widget, or after a setting change).
					You can easily force your widget to reload in the dashboard by calling the helper function <code>$(this).parents(\'[widgetId]\').dashboardWidget(\'reload\');</code>.';

        $out .= '<h2>Twig plugins</h2>';
        $out .= 'There are some builtin plugins for Twig especially developped for Piwik. <br />
				You can find them on the <a href="https://github.com/piwik/piwik/tree/master/core/SmartyPlugins">Git at /core/TwigPlugins</a>. <br />
				More documentation to come about Twig plugins.<br />';

        echo $out;
    }

    private static function boolToString($bool)
    {
        return $bool ? "true" : "false";
    }
}
