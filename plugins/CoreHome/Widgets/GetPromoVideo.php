<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Translation\Translator;
use Piwik\View;

class GetPromoVideo extends Widget
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Installation_Welcome');
        $config->setOrder(10);
    }

    public function render()
    {
        $view = new View('@CoreHome/getPromoVideo');
        $view->shareText     = $this->translator->translate('CoreHome_SharePiwikShort');
        $view->shareTextLong = $this->translator->translate('CoreHome_SharePiwikLong');
        $view->promoVideoUrl = 'https://matomo.org/docs/videos/';

        return $view->render();
    }
}