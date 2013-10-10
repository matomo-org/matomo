<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleRssWidget
 */

namespace Piwik\Plugins\ExampleRssWidget;

use Exception;
use Piwik\Piwik;

/**
 *
 * @package ExampleRssWidget
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function rssPiwik()
    {
        try {
            $rss = new RssRenderer('http://feeds.feedburner.com/Piwik');
            $rss->showDescription(true);
            echo $rss->get();
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function rssChangelog()
    {
        try {
            $rss = new RssRenderer('http://feeds.feedburner.com/PiwikReleases');
            $rss->setCountPosts(1);
            $rss->showDescription(false);
            $rss->showContent(true);
            echo $rss->get();
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    /**
     * @param \Exception $e
     */
    protected function error($e)
    {
        echo '<div class="pk-emptyDataTable">'
            . Piwik::translate('General_ErrorRequest')
            . ' - ' . $e->getMessage() . '</div>';
    }
}
