<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ExampleRssWidget
 */

/**
 *
 * @package Piwik_ExampleRssWidget
 */
class Piwik_ExampleRssWidget_Controller extends Piwik_Controller
{
    public function rssPiwik()
    {
        try {
            $rss = new Piwik_ExampleRssWidget_Rss('http://feeds.feedburner.com/Piwik');
            $rss->showDescription(true);
            echo $rss->get();
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function rssChangelog()
    {
        try {
            $rss = new Piwik_ExampleRssWidget_Rss('http://feeds.feedburner.com/PiwikReleases');
            $rss->setCountPosts(1);
            $rss->showDescription(false);
            $rss->showContent(true);
            echo $rss->get();
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    protected function error($e)
    {
        echo '<div class="pk-emptyDataTable">'
            . Piwik_Translate('General_ErrorRequest')
            . ' - ' . $e->getMessage() . '</div>';
    }
}
