<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\Tour\Dao\DataFinder;

class ChallengeCustomLogo extends Challenge
{
    /**
     * @var DataFinder
     */
    private $finder;

    /**
     * @var null|bool
     */
    private $completed = null;

    public function __construct(DataFinder $dataFinder)
    {
        $this->finder = $dataFinder;
    }

    public function getName()
    {
        return Piwik::translate('Tour_UploadLogo');
    }

    public function getId()
    {
        return 'setup_branding';
    }

    public function isCompleted()
    {
        if (!isset($this->completed)) {
            $logo = new CustomLogo();
            $this->completed = $logo->isEnabled();
        }
        return $this->completed;
    }

    public function getInAppLink()
    {
        return array('module' => 'CoreAdminHome', 'action' => 'generalSettings', 'widget' => false);
    }

    public function getInAppLinkHash()
    {
        return 'useCustomLogo';
    }


}