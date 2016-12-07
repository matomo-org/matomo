<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\ProfessionalServices\Advertising;

class Promo
{
    protected $linkTitles = array('Read more', 'Learn more');

    protected $content = array();

    public function __construct()
    {
        $this->content = array(
            // A/B Testing
            array(
                'campaignContent' => 'abTesting',
                'url' => 'https://piwik.org/recommends/ab-testing-learn-more',
                'text' => 'Ever wondered what A/B testing is and why it is so important? Check out how you can increase conversions and sales on your website and app.'
            ),
            array(
                'campaignContent' => 'abTesting',
                'url' => ' https://piwik.org/recommends/ab-testing-website',
                'text' => 'Increase revenue and conversions by comparing different versions of your websites or apps, ad detect the winning variation that will grow your business!'
            ),

            // Media Analytics
            array(
                'campaignContent' => 'mediaAnalytics',
                'url' => 'https://piwik.org/recommends/media-analytics-website',
                'text' => 'Ever wondered how people interact and engage with videos or audios on your website? Well now you can, and it integrates perfectly into your Piwik.',
            ),

            array(
                'campaignContent' => 'mediaAnalytics',
                'url' => 'https://piwik.org/recommends/media-analytics',
                'text' => 'Get powerful insights into how your audience watches your videos and listens to your audio. Media Analytics is now available on the Marketplace.',
            ),

            // Funnels
            array(
                'campaignContent' => 'funnels',
                'url' => 'https://piwik.org/recommends/conversion-funnels',
                'text' => 'Increase your conversions, sales and revenue by understanding your conversion funnels and where your visitors drop off with Funnels for Piwik.'
            ),

            // Piwik training
            array(
                'campaignContent' => 'userTraining',
                'url' => 'https://piwik.org/training/?pk_campaign=' . Advertising::CAMPAIGN_NAME_PROFESSIONAL_SERVICES . '&pk_source=Piwik_App',
                'text' => 'Want to know how to use all the exciting features in Piwik? Try a User training to be up to speed with working with Piwik.'
            ),



            // Piwik PRO
            // https://piwik.org/recommends/piwik-pro-from-app
            array(
                'campaignContent' => 'discoverPower',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Discover the power of open-source combined with enterprise-grade support and premium functionalities.'
            ),
            array(
                'campaignContent' => 'bringEnterpriseLevel',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Bring your analytics to enterprise level. Upgrade your Piwik platform and receive access to numerous premium features and assistance from experts.'
            ),
            array(
                'campaignContent' => 'monitoringAndIncident',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Do you need 24/7 Monitoring and Incident Handling for your Piwik? Get Premium features and enterprise-grade support.'
            ),
            array(
                'campaignContent' => 'slowingDown',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Is your Piwik slowing down? Piwik experts can help with your server setup!'
            ),
            array(
                'campaignContent' => 'whiteLabel',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Did you know you can adjust the look and feel of Piwik to your brand, and even replace "Piwik" with your product name? Try the White Label product!',
            ),
            array(
                'campaignContent' => 'metaSites',
                'url' => 'https://piwik.org/recommends/piwik-pro-from-app',
                'text' => 'Did you know you can aggregate the collected data across hundreds of sites and display it in a single dashboard? Get Premium features and enterprise-grade support.',
            )
        );
    }

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
