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
use Piwik\Url;

class ChallengeBrowseMarketplace extends Challenge
{

    public function getName()
    {
        return Piwik::translate('Tour_BrowseMarketplace');
    }

    public function getDescription()
    {
        return Piwik::translate('Marketplace_PluginDescription');
    }

    public function getId()
    {
        return 'browse_marketplace';
    }

    public function getUrl()
    {
        return 'index.php' . Url::getCurrentQueryStringWithParametersModified(array('module' => 'Marketplace', 'action' => 'overview', 'widget' => false));
    }



}