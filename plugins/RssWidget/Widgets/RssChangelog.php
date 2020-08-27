<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\RssWidget\Widgets;

use Piwik\Piwik;
use Piwik\Widget\WidgetConfig;
use Piwik\Plugins\RssWidget\RssRenderer;

class RssChangelog extends \Piwik\Widget\Widget
{
    public static function getCategory()
    {
        return 'About Matomo';
    }

    public static function getName()
    {
        return 'Matomo Changelog';
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId(self::getCategory());
        $config->setName(self::getName());
    }

    private function getFeed($URL) {
        $rss = new RssRenderer($URL);
        $rss->setCountPosts(1);
        $rss->showDescription(true);
        $rss->showContent(false);
        return $rss->get();
    }

    public function render()
    {   
        try {
            return $this->getFeed('https://matomo.org/changelog/feed/');
        } catch (\Exception $e) {
            return $this->error($e);
        }  
    }

    /**
     * @param \Exception $e
     * @return string
     */
    private function error($e)
    {
        return '<div class="pk-emptyDataTable">'
             . Piwik::translate('General_ErrorRequest', array('', ''))
             . ' - ' . $e->getMessage() . '</div>';
    }
}
