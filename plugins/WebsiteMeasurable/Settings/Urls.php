<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable\Settings;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Exception;
use Piwik\UrlHelper;

class Urls extends \Piwik\Settings\Measurable\MeasurableProperty
{

    public function __construct($idSite)
    {
        $name = 'urls';
        $pluginName = 'WebsiteMeasurable';
        $defaultValue = array();
        $type = FieldConfig::TYPE_ARRAY;

        parent::__construct($name, $defaultValue, $type, $pluginName, $idSite);
    }

    public function configureField()
    {
        if ($this->config) {
            return $this->config;
        }

        $config = new FieldConfig();
        $config->title = Piwik::translate('SitesManager_Urls');
        $config->inlineHelp = Piwik::translate('SitesManager_AliasUrlHelp');
        $config->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
        $config->uiControlAttributes = array(
          'cols' => '25',
          'rows' => '3',
          'placeholder' => "http://example.com/\nhttps://www.example.org/",
        );

        $self = $this;
        $config->validate = function ($urls) use ($self) {
            $self->checkUrls($urls);
            $self->checkAtLeastOneUrl($urls);
        };

        $config->transform = function ($urls) use ($self) {
            return $this->cleanParameterUrls($urls);
        };

        $this->config = $config;
        return $this->config;
    }

    /**
     * Checks that the array has at least one element
     *
     * @param array $urls
     * @throws Exception
     */
    public function checkAtLeastOneUrl($urls)
    {
        $urls = $this->cleanParameterUrls($urls);

        if (!is_array($urls)
            || count($urls) == 0
        ) {
            throw new Exception(Piwik::translate('SitesManager_ExceptionNoUrl'));
        }
    }

    /**
     * Check that the array of URLs are valid URLs
     *
     * @param array $urls
     * @throws Exception if any of the urls is not valid
     */
    public function checkUrls($urls)
    {
        $urls = $this->cleanParameterUrls($urls);

        foreach ($urls as $url) {
            if (!UrlHelper::isLookLikeUrl($url)) {
                throw new Exception(sprintf(Piwik::translate('SitesManager_ExceptionInvalidUrl'), $url));
            }
        }
    }

    /**
     * Clean the parameter URLs:
     * - if the parameter is a string make it an array
     * - remove the trailing slashes if found
     *
     * @param string|array urls
     * @return array the array of cleaned URLs
     */
    public function cleanParameterUrls($urls)
    {
        if (!is_array($urls)) {
            $urls = array($urls);
        }

        $urls = array_filter($urls);
        $urls = array_map('urldecode', $urls);

        foreach ($urls as &$url) {
            $url = $this->removeTrailingSlash($url);
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (empty($scheme)
                && strpos($url, '://') === false
            ) {
                if (strpos($url, '//') === 0) {
                    $url = 'http:' . $url;
                } else {
                    $url = 'http://' . $url;
                }
            }
            $url = trim($url);
            $url = Common::sanitizeInputValue($url);
        }

        $urls = array_unique($urls);
        return $urls;
    }

    /**
     * Remove the final slash in the URLs if found
     *
     * @param string $url
     * @return string the URL without the trailing slash
     */
    private function removeTrailingSlash($url)
    {
        // if there is a final slash, we take the URL without this slash (expected URL format)
        if (strlen($url) > 5
            && $url[strlen($url) - 1] == '/'
        ) {
            $url = substr($url, 0, strlen($url) - 1);
        }

        return $url;
    }
}
