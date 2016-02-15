<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PiwikPro;

class Promo
{
    protected $linkTitles = array('Read more', 'Learn more');

    protected $content = array(
        array(
            'campaignContent' => 'discoverPower',
            'text' => 'Discover the power of open-source combined with enterprise-grade support and premium functionalities.'
        ),
        array(
            'campaignContent' => 'bringEnterpriseLevel',
            'text' => 'Bring your analytics to enterprise level. Upgrade your Piwik platform and receive access to numerous premium features and assistance from our experts.'
        ),
        array(
            'campaignContent' => 'funnelAnalytics',
            'text' => 'Want Funnel Analytics? Get Premium features and enterprise-grade support from the makers of Piwik.'
        ),
        array(
            'campaignContent' => 'monitoringAndIncident',
            'text' => 'Do you need 24/7 Monitoring and Incident Handling for your Piwik? Get Premium features and enterprise-grade support from the makers of Piwik.'
        ),
        array(
            'campaignContent' => 'slowingDown',
            'text' => 'Is your Piwik slowing down? The Piwik makers can help with your server setup!'
        ),
        array(
            'campaignContent' => 'excitingFeatures',
            'text' => 'Want to know how to use all the exciting features in Piwik? Try our User training to be up to speed with working with Piwik.'
        ),
        array(
            'campaignContent' => 'slowingDown',
            'text' => 'Did you know you can adjust the look and feel of Piwik to your brand, and even replace "Piwik" with your product name? Try our White Label product!',
        ),
        array(
            'campaignContent' => 'metaSites',
            'text' => 'Did you know you can aggregate the tracked data across hundreds of sites and display it in a single dashboard? Get Premium features and enterprise-grade support.',
        ),
    );

    public function getLinkTitle()
    {
        $titles = $this->linkTitles;
        shuffle($titles);

        return array_shift($titles);
    }

    public function getContent()
    {
        $content = $this->content;
        shuffle($content);

        return array_shift($content);
    }
}
