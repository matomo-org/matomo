<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleRssWidget;

use Piwik\Piwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Example Widgets';

    protected function init()
    {
        $this->addWidget('Piwik.org Blog', 'rssPiwik');
        $this->addWidget('Piwik Changelog', 'rssChangelog');
    }

    public function rssPiwik()
    {
        try {
            $rss = new RssRenderer('http://feeds.feedburner.com/Piwik');
            $rss->showDescription(true);

            return $rss->get();

        } catch (\Exception $e) {

            return $this->error($e);
        }
    }

    public function rssChangelog()
    {
        try {
            $rss = new RssRenderer('http://feeds.feedburner.com/PiwikReleases');
            $rss->setCountPosts(1);
            $rss->showDescription(true);
            $rss->showContent(false);

            return $rss->get();

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
