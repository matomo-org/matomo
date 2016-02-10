<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\Piwik;

class Provider extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Live.getAllVisitorDetails' => 'extendVisitorDetails'
        );
    }

    public function install()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER TABLE `" . Common::prefixTable('log_visit') . "` ADD `location_provider` VARCHAR( 100 ) NULL";

        // if the column already exist do not throw error. Could be installed twice...
        try {
            Db::exec($query);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['provider']     = $instance->getProvider();
        $visitor['providerName'] = $instance->getProviderName();
        $visitor['providerUrl']  = $instance->getProviderUrl();
    }

    public function uninstall()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER TABLE `" . Common::prefixTable('log_visit') . "` DROP `location_provider`";
        Db::exec($query);
    }

    public function postLoad()
    {
        Piwik::addAction('Template.footerUserCountry', array('Piwik\Plugins\Provider\Provider', 'footerUserCountry'));
    }

    public static function footerUserCountry(&$out)
    {
        $out .= '<h2 piwik-enriched-headline>' . Piwik::translate('Provider_WidgetProviders') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('Provider', 'getProvider');
    }

    /**
     * Returns the hostname extension (site.co.jp in fvae.VARG.ceaga.site.co.jp)
     * given the full hostname looked up from the IP
     *
     * @param string $hostname
     *
     * @return string
     */
    public static function getCleanHostname($hostname)
    {
        $extToExclude = array(
            'com', 'net', 'org', 'co'
        );

        $off = strrpos($hostname, '.');
        $ext = substr($hostname, $off);

        if (empty($off) || is_numeric($ext) || strlen($hostname) < 5) {
            return 'Ip';
        } else {
            $cleanHostname = null;

            /**
             * Triggered when prettifying a hostname string.
             *
             * This event can be used to customize the way a hostname is displayed in the
             * Providers report.
             *
             * **Example**
             *
             *     public function getCleanHostname(&$cleanHostname, $hostname)
             *     {
             *         if ('fvae.VARG.ceaga.site.co.jp' == $hostname) {
             *             $cleanHostname = 'site.co.jp';
             *         }
             *     }
             *
             * @param string &$cleanHostname The hostname string to display. Set by the event
             *                               handler.
             * @param string $hostname The full hostname.
             */
            Piwik::postEvent('Provider.getCleanHostname', array(&$cleanHostname, $hostname));
            if ($cleanHostname !== null) {
                return $cleanHostname;
            }

            $e = explode('.', $hostname);
            $s = sizeof($e);

            // if extension not correct
            if (isset($e[$s - 2]) && in_array($e[$s - 2], $extToExclude)) {
                return $e[$s - 3] . "." . $e[$s - 2] . "." . $e[$s - 1];
            } else {
                return $e[$s - 2] . "." . $e[$s - 1];
            }
        }
    }

}
