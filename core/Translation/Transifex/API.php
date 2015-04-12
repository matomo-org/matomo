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
use Piwik\Exception\AuthenticationFailedException;

class API
{
    protected $apiUrl = 'https://www.transifex.com/api/2/';
    protected $username = '';
    protected $password = '';
    protected $projectSlug = '';

    public function __construct($username, $password, $project='piwik')
    {
        $this->username = $username;
        $this->password = $password;
        $this->projectSlug = $project;
    }

    public function getAvailableResources()
    {
        static $resources;

        if (empty($resources)) {
            $apiPath = 'project/' . $this->projectSlug . '/resources';
            $resources = $this->getApiResults($apiPath);
        }

        return $resources;
    }

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

    public function getAvailableLanguageCodes()
    {
        static $languageCodes = array();
        if (empty($languageCodes)) {
            $apiData = $this->getApiResults('project/' . $this->projectSlug . '/languages');
            foreach ($apiData as $languageData) {
                $languageCodes[] = $languageData->language_code;
            }
        }
        return $languageCodes;
    }

    public function getTranslations($resource, $language, $raw=false)
    {
        if ($this->resourceExists($resource)) {
            $apiPath = 'project/' . $this->projectSlug . '/resource/' . $resource . '/translation/' . $language . '/?mode=onlytranslated&file';
            return $this->getApiResults($apiPath, $raw);
        }
        return null;
    }

    protected function getApiResults($apiPath, $raw=false)
    {
        $apiUrl = $this->apiUrl . $apiPath;

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf("%s:%s", $this->username, $this->password));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpStatus == 401) {
            throw new AuthenticationFailedException();
        } else if ($httpStatus != 200) {
            throw new Exception('Error while getting API results', $httpStatus);
        }

        return $raw ? $response : json_decode($response);
    }
}