<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleRssWidget\Widgets;

use Piwik\Piwik;
use Piwik\Plugins\ExampleRssWidget\RssRenderer;

class RssPiwik extends \Piwik\Plugin\Widget
{
    protected $category = 'Example Widgets';
    protected $name = 'Piwik.org Blog';

    public function render()
    {
        try {
            $rss = new RssRenderer('http://feeds.feedburner.com/Piwik');
            $rss->showDescription(true);

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
