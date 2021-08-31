<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Translation\Weblate;

use Exception;
use Piwik\Cache;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Http;

class API
{
    protected $apiUrl = 'https://hosted.weblate.org/api/';
    protected $apiToken = '';
    protected $projectSlug = '';

    public function __construct($apiToken, $project = 'matomo')
    {
        $this->apiToken = $apiToken;
        $this->projectSlug = $project;
    }

    /**
     * Returns all resources available on Weblate project
     *
     * @return array
     */
    public function getAvailableResources()
    {
        $cache = Cache::getTransientCache();
        $cacheId = 'weblate_resources_' . $this->projectSlug;
        $result = $cache->fetch($cacheId);

        if (empty($result)) {
            $apiPath = 'projects/' . $this->projectSlug . '/components/';
            $resources = $this->getApiResults($apiPath);
            $result = [];

            while($resources->results) {
                $result = array_merge($result, $resources->results);

                if ($resources->next) {
                    $apiPath = str_replace($this->apiUrl, '', $resources->next);
                    $resources = $this->getApiResults($apiPath);
                } else {
                    break;
                }
            }

            $cache->save($cacheId, $result);
        }

        return $result;
    }

    /**
     * Checks if the given resource exists in Weblate project
     *
     * @param string $resource
     * @return bool
     */
    public function resourceExists($resource)
    {
        $resources = $this->getAvailableResources();
        foreach ($resources as $res) {
            if ($res->slug == $resource) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns all language codes the Weblate project is available for
     *
     * @return array
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function getAvailableLanguageCodes()
    {
        $cache = Cache::getTransientCache();
        $cacheId = 'weblate_languagescodes_' . $this->projectSlug;
        $languageCodes = $cache->fetch($cacheId);

        if (empty($languageCodes)) {
            $apiData = $this->getApiResults('projects/' . $this->projectSlug . '/languages/');
            foreach ($apiData as $languageData) {
                $languageCodes[] = $languageData->code;
            }
            $cache->save($cacheId, $languageCodes);
        }
        return $languageCodes;
    }

    /**
     * Return the translations for the given resource and language
     *
     * @param string $resource e.g. piwik-base, piwik-plugin-api,...
     * @param string $language e.g. de, pt_BR, hy,...
     * @param bool $raw if true plain response will be returned (unparsed json)
     * @return mixed
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function getTranslations($resource, $language, $raw = false)
    {
        if ($this->resourceExists($resource)) {
            $apiPath = 'translations/' . $this->projectSlug . '/' . $resource . '/' . $language . '/file/';
            return $this->getApiResults($apiPath, $raw);
        }
        return null;
    }

    /**
     * Returns response for API request with given path
     *
     * @param $apiPath
     * @param bool $raw
     * @return mixed
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    protected function getApiResults($apiPath, $raw = false)
    {
        $apiUrl = $this->apiUrl . $apiPath;

        $response = Http::sendHttpRequestBy(Http::getTransportMethod(), $apiUrl, 60, null, null, null, 5, false,
            false, false, true, 'GET', null, null, null,
            ['Authorization: Token ' . $this->apiToken]);

        $httpStatus = $response['status'];
        $response = $response['data'];

        if ($httpStatus == 401) {
            throw new AuthenticationFailedException();
        } elseif ($httpStatus != 200) {
            throw new Exception('Error while getting API results', $httpStatus);
        }

        return $raw ? $response : json_decode($response);
    }
}
