<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Translation\Transifex;

use Exception;
use Piwik\Cache;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Http;

class API
{
    protected $apiUrl = 'https://www.transifex.com/api/2/';
    protected $username = '';
    protected $password = '';
    protected $projectSlug = '';

    public function __construct($username, $password, $project = 'piwik')
    {
        $this->username = $username;
        $this->password = $password;
        $this->projectSlug = $project;
    }

    /**
     * Returns all resources available on Transifex project
     *
     * @return array
     */
    public function getAvailableResources()
    {
        $cache = Cache::getTransientCache();
        $cacheId = 'transifex_resources_' . $this->projectSlug;
        $resources = $cache->fetch($cacheId);

        if (empty($resources)) {
            $apiPath = 'project/' . $this->projectSlug . '/resources';
            $resources = $this->getApiResults($apiPath);
            $cache->save($cacheId, $resources);
        }

        return $resources;
    }

    /**
     * Checks if the given resource exists in Transifex project
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
     * Returns all language codes the transifex project is available for
     *
     * @return array
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function getAvailableLanguageCodes()
    {
        $cache = Cache::getTransientCache();
        $cacheId = 'transifex_languagescodes_' . $this->projectSlug;
        $languageCodes = $cache->fetch($cacheId);

        if (empty($languageCodes)) {
            $apiData = $this->getApiResults('project/' . $this->projectSlug . '/languages');
            foreach ($apiData as $languageData) {
                $languageCodes[] = $languageData->language_code;
            }
            $cache->save($cacheId, $languageCodes);
        }
        return $languageCodes;
    }

    /**
     * Returns statistic data for the given resource
     *
     * @param string $resource e.g. piwik-base, piwik-plugin-api,...
     * @return array
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function getStatistics($resource)
    {
        return $this->getApiResults('project/' . $this->projectSlug . '/resource/' . $resource . '/stats/');
    }

    /**
     * Return the translations for the given resource and language
     *
     * @param string $resource e.g. piwik-base, piwik-plugin-api,...
     * @param string $language e.g. de, pt_BR, hy,...
     * @param bool $raw if true plain response wil be returned (unparsed json)
     * @return mixed
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function getTranslations($resource, $language, $raw = false)
    {
        if ($this->resourceExists($resource)) {
            $apiPath = 'project/' . $this->projectSlug . '/resource/' . $resource . '/translation/' . $language . '/?mode=onlytranslated&file';
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

        $response = Http::sendHttpRequest($apiUrl, 1000, null, null, 5, false, false, true, 'GET', $this->username, $this->password);

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
