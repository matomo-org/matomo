<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Widgets;

use Piwik\Plugins\Tour\Engagement\Parts;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Piwik;

class GetEngagement extends Widget
{
    /**
     * @var Parts
     */
    private $parts;

    /**
     * GetEngagement constructor.
     * @param Parts $parts
     */
    public function __construct(Parts $parts)
    {
        $this->parts = $parts;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Getting Started');
        $config->setOrder(99);

        if (!Piwik::hasUserSuperUserAccess()) {
            $config->disable();
        }
    }

    public function render()
    {
        $part = $this->parts->getCurrentPart();

        if (empty($part)) {
            return '<p class="widgetBody tourEngagement"><strong class="completed">' . Piwik::translate('Tour_CompletionTitle') .'</strong> ' . Piwik::translate('Tour_CompletionMessage') . '<br /><br /></p>';
        }

        $steps = $part->getSteps();

        return $this->renderTemplate('engagement', array(
            'steps' => $steps,
            'description' => $part->getDescription()
        ));
    }

}