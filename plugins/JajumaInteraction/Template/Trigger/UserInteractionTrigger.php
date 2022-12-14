<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\JajumaInteraction\Template\Trigger;

use Piwik\Plugins\TagManager\Template\Trigger\BaseTrigger;

class UserInteractionTrigger extends BaseTrigger
{
    public function getCategory()
    {
        return self::CATEGORY_USER_ENGAGEMENT;
    }

    public function getName()
    {
        return parent::getName();
    }

    public function getDescription()
    {
        return parent::getDescription();
    }

    public function getHelp()
    {
        return parent::getHelp();
    }

    public function getIcon()
    {
        return 'plugins/JajumaInteraction/images/icons/interaction.svg';
    }

    public function getParameters()
    {
        return array();
    }

}
