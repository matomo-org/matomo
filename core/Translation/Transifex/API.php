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
    protected $apiUrl = 'https://www.transifex.com/api/2/project/piwik/';
    protected $username = '';
    protected $password = '';
    protected $projectSlug = '';

    public function __construct($username, $password, $project='piwik')
    {
        $this->username = $username;
        $this->password = $password;
        $this->projectSlug = $project;
    }

    public function getAvailableLanguageCodes()
    {
        $languageCodes = array();
        $apiData = $this->getApiResults('languages');
        foreach ($apiData as $languageData) {
            $languageCodes[] = $languageData->language_code;
        }

        return $languageCodes;
    }

    public function getTranslations($resource, $language, $raw=false)
    {
        $apiPath = 'resource/'.$resource.'/translation/'.$language.'/?mode=onlytranslated&file';
        return $this->getApiResults($apiPath, $raw);
    }

    protected function getApiResults($apiPath, $raw=false)
    {
        $apiUrl = $this->apiUrl . $apiPath;

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf("%s:%s", $this->username, $this->password));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpStatus == 401) {
            throw new AuthenticationFailedException();
        } else if ($httpStatus != 200) {
            throw new Exception();
        }

        return $raw ? $response : json_decode($response);
    }
}