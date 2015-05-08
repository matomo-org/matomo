<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Widgets;

use Piwik\Plugin\Widget;
use Piwik\Translation\Translator;
use Piwik\View;

class GetPromoVideo extends Widget
{
    protected $category = 'Example Widgets';
    protected $name     = 'Installation_Welcome';
    protected $order    = 10;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function render()
    {
        $view = new View('@CoreHome/getPromoVideo');
        $view->shareText     = $this->translator->translate('CoreHome_SharePiwikShort');
        $view->shareTextLong = $this->translator->translate('CoreHome_SharePiwikLong');
        $view->promoVideoUrl = 'https://www.youtube.com/watch?v=OslfF_EH81g';

        return $view->render();
    }
}