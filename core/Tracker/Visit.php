<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Tracker;
use UserAgentParser;

/**
 * Class used to handle a Visit.
 * A visit is either NEW or KNOWN.
 * - If a visit is NEW then we process the visitor information (settings, referrers, etc.) and save
 * a new line in the log_visit table.
 * - If a visit is KNOWN then we update the visit row in the log_visit table, updating the number of pages
 * views, time spent, etc.
 *
 * Whether a visit is NEW or KNOWN we also save the action in the DB.
 * One request to the piwik.php script is associated to one action.
 *
 */
class Visit implements VisitInterface
{
    const UNKNOWN_CODE = 'xx';

    /**
     * @var GoalManager
     */
    protected $goalManager;

    /**
     * @var  Request
     */
    protected $request;

    protected $visitorInfo = array();
    protected $userSettingsInformation = null;
    protected $visitorCustomVariables = array();
    protected $visitorKnown;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     *    Main algorithm to handle the visit.
     *
     *  Once we have the visitor information, we have to determine if the visit is a new or a known visit.
     *
     * 1) When the last action was done more than 30min ago,
     *      or if the visitor is new, then this is a new visit.
     *
     * 2) If the last action is less than 30min ago, then the same visit is going on.
     *    Because the visit goes on, we can get the time spent during the last action.
     *
     * NB:
     *  - In the case of a new visit, then the time spent
     *    during the last action of the previous visit is unknown.
     *
     *    - In the case of a new visit but with a known visitor,
     *    we can set the 'returning visitor' flag.
     *
     * In all the cases we set a cookie to the visitor with the new information.
     */
    public function handle()
    {
        // the IP is needed by isExcluded() and GoalManager->recordGoals()
        $ip = $this->request->getIp();
        $this->visitorInfo['location_ip'] = $ip;

        $excluded = new VisitExcluded($this->request, $ip);
        if ($excluded->isExcluded()) {
            return;
        }

        /**
         * Triggered after visits are tested for exclusion so plugins can modify the IP address
         * persisted with a visit.
         * 
         * This event is primarily used by the **PrivacyManager** plugin to anonymize IP addresses.
         * 
         * @param string &$ip The visitor's IP address.
         */
        Piwik::postEvent('Tracker.setVisitorIp', array(&$this->visitorInfo['location_ip']));

        $this->visitorCustomVariables = $this->request->getCustomVariables($scope = 'visit');
        if (!empty($this->visitorCustomVariables)) {
            Common::printDebug("Visit level Custom Variables: ");
            Common::printDebug($this->visitorCustomVariables);
        }

        $this->goalManager = new GoalManager($this->request);

        $visitIsConverted = false;
        $action = null;

        $requestIsManualGoalConversion = ($this->goalManager->idGoal > 0);
        $requestIsEcommerce = $this->goalManager->requestIsEcommerce;
        if ($requestIsEcommerce) {
            $someGoalsConverted = true;

            // Mark the visit as Converted only if it is an order (not for a Cart update)
            if ($this->goalManager->isGoalAnOrder) {
                $visitIsConverted = true;
            }
        } // this request is from the JS call to piwikTracker.trackGoal()
        elseif ($requestIsManualGoalConversion) {
            $someGoalsConverted = $this->goalManager->detectGoalId($this->request->getIdSite());
            $visitIsConverted = $someGoalsConverted;
            // if we find a idgoal in the URL, but then the goal is not valid, this is most likely a fake request
            if (!$someGoalsConverted) {
                throw new \Exception('Invalid goal tracking request for goal id = ' . $this->goalManager->idGoal);
            }
        } // normal page view, potentially triggering a URL matching goal
        else {
            $action = Action::factory($this->request);

            $action->writeDebugInfo();

            $someGoalsConverted = $this->goalManager->detectGoalsMatchingUrl($this->request->getIdSite(), $action);
            $visitIsConverted = $someGoalsConverted;

            $action->loadIdsFromLogActionTable();
        }

        // the visitor and session
        $this->recognizeTheVisitor();

        $isLastActionInTheSameVisit = $this->isLastActionInTheSameVisit();

        if (!$isLastActionInTheSameVisit) {
            Common::printDebug("Visitor detected, but last action was more than 30 minutes ago...");
        }
        // Known visit when:
        // ( - the visitor has the Piwik cookie with the idcookie ID used by Piwik to match the visitor
        //   OR
        //   - the visitor doesn't have the Piwik cookie but could be match using heuristics @see recognizeTheVisitor()
        // )
        // AND
        // - the last page view for this visitor was less than 30 minutes ago @see isLastActionInTheSameVisit()
        if ($this->isVisitorKnown()
            && $isLastActionInTheSameVisit
        ) {
            $idReferrerActionUrl = $this->visitorInfo['visit_exit_idaction_url'];
            $idReferrerActionName = $this->visitorInfo['visit_exit_idaction_name'];
            try {
                $this->handleExistingVisit($action, $visitIsConverted);
                if (!is_null($action)) {
                    $action->record($this->visitorInfo['idvisit'],
                        $this->visitorInfo['idvisitor'],
                        $idReferrerActionUrl,
                        $idReferrerActionName,
                        $this->visitorInfo['time_spent_ref_action']
                    );
                }
            } catch (VisitorNotFoundInDb $e) {

                // There is an edge case when:
                // - two manual goal conversions happen in the same second
                // - which result in handleExistingVisit throwing the exception
                //   because the UPDATE didn't affect any rows (one row was found, but not updated since no field changed)
                // - the exception is caught here and will result in a new visit incorrectly
                // In this case, we cancel the current conversion to be recorded:
                if ($requestIsManualGoalConversion
                    || $requestIsEcommerce
                ) {
                    $someGoalsConverted = $visitIsConverted = false;
                } // When the row wasn't found in the logs, and this is a pageview or
                // goal matching URL, we force a new visitor
                else {
                    $this->visitorKnown = false;
                }
            }
        }

        // New visit when:
        // - the visitor has the Piwik cookie but the last action was performed more than 30 min ago @see isLastActionInTheSameVisit()
        // - the visitor doesn't have the Piwik cookie, and couldn't be matched in @see recognizeTheVisitor()
        // - the visitor does have the Piwik cookie but the idcookie and idvisit found in the cookie didn't match to any existing visit in the DB
        if (!$this->isVisitorKnown()
            || !$isLastActionInTheSameVisit
        ) {
            $this->handleNewVisit($action, $visitIsConverted);
            if (!is_null($action)) {
                $action->record($this->visitorInfo['idvisit'], $this->visitorInfo['idvisitor'], 0, 0, 0);
            }
        }

        // update the cookie with the new visit information
        $this->request->setThirdPartyCookie($this->visitorInfo['idvisitor']);

        // record the goals if applicable
        if ($someGoalsConverted) {
            $this->goalManager->recordGoals(
                $this->request->getIdSite(),
                $this->visitorInfo,
                $this->visitorCustomVariables,
                $action
            );
        }
        unset($this->goalManager);
        unset($action);
    }

    /**
     * In the case of a known visit, we have to do the following actions:
     *
     * 1) Insert the new action
     * 2) Update the visit information
     *
     * @param Action $action
     * @param $visitIsConverted
     * @throws VisitorNotFoundInDb
     */
    protected function handleExistingVisit($action, $visitIsConverted)
    {
        Common::printDebug("Visit is known (IP = " . IP::N2P($this->getVisitorIp()) . ")");

        $valuesToUpdate = $this->getExistingVisitFieldsToUpdate($action, $visitIsConverted);

        $this->visitorInfo['time_spent_ref_action'] = $this->getTimeSpentReferrerAction();

        $this->request->overrideLocation($valuesToUpdate);

        // update visitorInfo
        foreach ($valuesToUpdate AS $name => $value) {
            $this->visitorInfo[$name] = $value;
        }

        /**
         * Triggered before a [visit entity](/guides/persistence-and-the-mysql-backend#visits) is updated when
         * tracking an action for an existing visit.
         * 
         * This event can be used to modify the visit properties that will be updated before the changes
         * are persisted.
         * 
         * @param array &$valuesToUpdate Visit entity properties that will be updated.
         * @param array $visit The entire visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits)
         *                     to see what it contains.
         */
        Piwik::postEvent('Tracker.existingVisitInformation', array(&$valuesToUpdate, $this->visitorInfo));

        $this->updateExistingVisit($valuesToUpdate);
    }

    /**
     * @return int Time in seconds
     */
    protected function getTimeSpentReferrerAction()
    {
        $timeSpent = $this->request->getCurrentTimestamp() - $this->visitorInfo['visit_last_action_time'];
        if ($timeSpent < 0
            || $timeSpent > Config::getInstance()->Tracker['visit_standard_length']
        ) {
            $timeSpent = 0;
        }
        return $timeSpent;
    }

    /**
     * In the case of a new visit, we have to do the following actions:
     *
     * 1) Insert the new action
     *
     * 2) Insert the visit information
     *
     * @param Action $action
     * @param bool $visitIsConverted
     */
    protected function handleNewVisit($action, $visitIsConverted)
    {
        Common::printDebug("New Visit (IP = " . IP::N2P($this->getVisitorIp()) . ")");

        $this->visitorInfo = $this->getNewVisitorInformation($action);

        // Add Custom variable key,value to the visitor array
        $this->visitorInfo = array_merge($this->visitorInfo, $this->visitorCustomVariables);

        $this->visitorInfo['visit_goal_converted'] = $visitIsConverted ? 1 : 0;

        $this->visitorInfo['referer_name'] = substr($this->visitorInfo['referer_name'], 0, 70);
        $this->visitorInfo['referer_keyword'] = substr($this->visitorInfo['referer_keyword'], 0, 255);
        $this->visitorInfo['config_resolution'] = substr($this->visitorInfo['config_resolution'], 0, 9);

        /**
         * Triggered before a new [visit entity](/guides/persistence-and-the-mysql-backend#visits) is persisted.
         * 
         * This event can be used to modify the visit entity or add new information to it before it is persisted.
         * The UserCountry plugin, for example, uses this event to add location information for each visit.
         *
         * @param array &$visit The visit entity. Read [this](/guides/persistence-and-the-mysql-backend#visits) to see
         *                      what information it contains.
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         */
        Piwik::postEvent('Tracker.newVisitorInformation', array(&$this->visitorInfo, $this->request));

        $this->request->overrideLocation($this->visitorInfo);
        $this->printVisitorInformation();

        $idVisit = $this->insertNewVisit( $this->visitorInfo );

        $this->visitorInfo['idvisit'] = $idVisit;
        $this->visitorInfo['visit_first_action_time'] = $this->request->getCurrentTimestamp();
        $this->visitorInfo['visit_last_action_time'] = $this->request->getCurrentTimestamp();

    }

    static private function cleanupVisitTotalTime($t)
    {
        $t = (int)$t;
        if ($t < 0) {
            $t = 0;
        }
        $smallintMysqlLimit = 65534;
        if ($t > $smallintMysqlLimit) {
            $t = $smallintMysqlLimit;
        }
        return $t;
    }

    /**
     *  Returns visitor cookie
     *
     * @return string  binary
     */
    protected function getVisitorIdcookie()
    {
        if ($this->isVisitorKnown()) {
            return $this->visitorInfo['idvisitor'];
        }
        // If the visitor had a first party ID cookie, then we use this value
        if (!empty($this->visitorInfo['idvisitor'])
            && strlen($this->visitorInfo['idvisitor']) == Tracker::LENGTH_BINARY_ID
        ) {
            return $this->visitorInfo['idvisitor'];
        }
        return Common::hex2bin($this->generateUniqueVisitorId());
    }

    /**
     * @return string returns random 16 chars hex string
     */
    static public function generateUniqueVisitorId()
    {
        $uniqueId = substr(Common::generateUniqId(), 0, Tracker::LENGTH_HEX_ID_STRING);
        return $uniqueId;
    }

    /**
     * Returns the visitor's IP address
     *
     * @return string
     */
    protected function getVisitorIp()
    {
        return $this->visitorInfo['location_ip'];
    }

    /**
     * This methods tries to see if the visitor has visited the website before.
     *
     * We have to split the visitor into one of the category
     * - Known visitor
     * - New visitor
     */
    protected function recognizeTheVisitor()
    {
        $this->visitorKnown = false;

        $userInfo = $this->getUserSettingsInformation();
        $configId = $userInfo['config_id'];

        $idVisitor = $this->request->getVisitorId();
        $isVisitorIdToLookup = !empty($idVisitor);

        if ($isVisitorIdToLookup) {
            $this->visitorInfo['idvisitor'] = $idVisitor;
            Common::printDebug("Matching visitors with: visitorId=" . bin2hex($this->visitorInfo['idvisitor']) . " OR configId=" . bin2hex($configId));
        } else {
            Common::printDebug("Visitor doesn't have the piwik cookie...");
        }

        $selectCustomVariables = '';
        // No custom var were found in the request, so let's copy the previous one in a potential conversion later
        if (!$this->visitorCustomVariables) {
            $selectCustomVariables = ',
            custom_var_k1, custom_var_v1,
            custom_var_k2, custom_var_v2,
            custom_var_k3, custom_var_v3,
            custom_var_k4, custom_var_v4,
            custom_var_k5, custom_var_v5';
        }

        $persistedVisitAttributes = $this->getVisitFieldsPersist();

        $selectFields = implode(", ", $persistedVisitAttributes);

        $select = "SELECT
                        visit_last_action_time,
                        visit_first_action_time,
                        $selectFields
                        $selectCustomVariables
    ";
        $from = "FROM " . Common::prefixTable('log_visit');

        list($timeLookBack, $timeLookAhead) = $this->getWindowLookupThisVisit();

        $shouldMatchOneFieldOnly = $this->shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup);

        // Two use cases:
        // 1) there is no visitor ID so we try to match only on config_id (heuristics)
        // 		Possible causes of no visitor ID: no browser cookie support, direct Tracking API request without visitor ID passed,
        //        importing server access logs with import_logs.py, etc.
        // 		In this case we use config_id heuristics to try find the visitor in tahhhe past. There is a risk to assign
        // 		this page view to the wrong visitor, but this is better than creating artificial visits.
        // 2) there is a visitor ID and we trust it (config setting trust_visitors_cookies, OR it was set using &cid= in tracking API),
        //      and in these cases, we force to look up this visitor id
        $whereCommon = "visit_last_action_time >= ? AND visit_last_action_time <= ? AND idsite = ?";
        $bindSql = array(
            $timeLookBack,
            $timeLookAhead,
            $this->request->getIdSite()
        );

        if ($shouldMatchOneFieldOnly) {
            if ($isVisitorIdToLookup) {
                $whereCommon .= ' AND idvisitor = ?';
                $bindSql[] = $this->visitorInfo['idvisitor'];
            } else {
                $whereCommon .= ' AND config_id = ?';
                $bindSql[] = $configId;
            }

            $sql = "$select
            $from
            WHERE " . $whereCommon . "
            ORDER BY visit_last_action_time DESC
            LIMIT 1";
        } // We have a config_id AND a visitor_id. We match on either of these.
        // 		Why do we also match on config_id?
        //		we do not trust the visitor ID only. Indeed, some browsers, or browser addons,
        // 		cause the visitor id from the 1st party cookie to be different on each page view!
        // 		It is not acceptable to create a new visit every time such browser does a page view,
        // 		so we also backup by searching for matching config_id.
        // We use a UNION here so that each sql query uses its own INDEX
        else {
            // will use INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time)
            $where = ' AND config_id = ?';
            $bindSql[] = $configId;
            $sqlConfigId = "$select ,
                0 as priority
                $from
                WHERE $whereCommon $where
                ORDER BY visit_last_action_time DESC
                LIMIT 1
        ";

            // will use INDEX index_idsite_idvisitor (idsite, idvisitor)
            $bindSql[] = $timeLookBack;
            $bindSql[] = $timeLookAhead;
            $bindSql[] = $this->request->getIdSite();
            $where = ' AND idvisitor = ?';
            $bindSql[] = $this->visitorInfo['idvisitor'];
            $sqlVisitorId = "$select ,
                1 as priority
                $from
                WHERE $whereCommon $where
                ORDER BY visit_last_action_time DESC
                LIMIT 1
        ";

            // We join both queries and favor the one matching the visitor_id if it did match
            $sql = " ( $sqlConfigId )
                UNION
                ( $sqlVisitorId )
                ORDER BY priority DESC
                LIMIT 1";
        }

        $visitRow = Tracker::getDatabase()->fetch($sql, $bindSql);

        $isNewVisitForced = $this->request->getParam('new_visit');
        $isNewVisitForced = !empty($isNewVisitForced);
        $newVisitEnforcedAPI = $isNewVisitForced
            && ($this->request->isAuthenticated()
                || !Config::getInstance()->Tracker['new_visit_api_requires_admin']);
        $enforceNewVisit = $newVisitEnforcedAPI || Config::getInstance()->Debug['tracker_always_new_visitor'];

        if (!$enforceNewVisit
            && $visitRow
            && count($visitRow) > 0
        ) {
            // These values will be used throughout the request
            $this->visitorInfo['visit_last_action_time'] = strtotime($visitRow['visit_last_action_time']);
            $this->visitorInfo['visit_first_action_time'] = strtotime($visitRow['visit_first_action_time']);

            foreach($persistedVisitAttributes as $field) {
                $this->visitorInfo[$field] = $visitRow[$field];
            }

            // Custom Variables copied from Visit in potential later conversion
            if (!empty($selectCustomVariables)) {
                for ($i = 1; $i <= Tracker::MAX_CUSTOM_VARIABLES; $i++) {
                    if (isset($visitRow['custom_var_k' . $i])
                        && strlen($visitRow['custom_var_k' . $i])
                    ) {
                        $this->visitorInfo['custom_var_k' . $i] = $visitRow['custom_var_k' . $i];
                    }
                    if (isset($visitRow['custom_var_v' . $i])
                        && strlen($visitRow['custom_var_v' . $i])
                    ) {
                        $this->visitorInfo['custom_var_v' . $i] = $visitRow['custom_var_v' . $i];
                    }
                }
            }

            $this->visitorKnown = true;
            Common::printDebug("The visitor is known (idvisitor = " . bin2hex($this->visitorInfo['idvisitor']) . ",
                    config_id = " . bin2hex($configId) . ",
                    idvisit = {$this->visitorInfo['idvisit']},
                    last action = " . date("r", $this->visitorInfo['visit_last_action_time']) . ",
                    first action = " . date("r", $this->visitorInfo['visit_first_action_time']) . ",
                    visit_goal_buyer' = " . $this->visitorInfo['visit_goal_buyer'] . ")");
            //Common::printDebug($this->visitorInfo);
        } else {
            Common::printDebug("The visitor was not matched with an existing visitor...");
        }
    }

    /**
     * By default, we look back 30 minutes to find a previous visitor (for performance reasons).
     * In some cases, it is useful to look back and count unique visitors more accurately. You can set custom lookback window in
     * [Tracker] window_look_back_for_visitor
     *
     * The returned value is the window range (Min, max) that the matched visitor should fall within
     *
     * @return array( datetimeMin, datetimeMax )
     */
    protected function getWindowLookupThisVisit()
    {
        $visitStandardLength = Config::getInstance()->Tracker['visit_standard_length'];
        $lookBackNSecondsCustom = Config::getInstance()->Tracker['window_look_back_for_visitor'];

        $lookAheadNSeconds = $visitStandardLength;
        $lookBackNSeconds = $visitStandardLength;
        if ($lookBackNSecondsCustom > $lookBackNSeconds) {
            $lookBackNSeconds = $lookBackNSecondsCustom;
        }

        $timeLookBack = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() - $lookBackNSeconds);
        $timeLookAhead = date('Y-m-d H:i:s', $this->request->getCurrentTimestamp() + $lookAheadNSeconds);

        return array($timeLookBack, $timeLookAhead);
    }

    protected function shouldLookupOneVisitorFieldOnly($isVisitorIdToLookup)
    {
        // This setting would be enabled for Intranet websites, to ensure that visitors using all the same computer config, same IP
        // are not counted as 1 visitor. In this case, we want to enforce and trust the visitor ID from the cookie.
        $trustCookiesOnly = Config::getInstance()->Tracker['trust_visitors_cookies'];

        // If a &cid= was set, we force to select this visitor (or create a new one)
        $isForcedVisitorIdMustMatch = ($this->request->getForcedVisitorId() != null);

        $shouldMatchOneFieldOnly = (($isVisitorIdToLookup && $trustCookiesOnly)
            || $isForcedVisitorIdMustMatch
            || !$isVisitorIdToLookup);
        return $shouldMatchOneFieldOnly;
    }

    /**
     * Gets the UserSettings information and returns them in an array of name => value
     *
     * @return array
     */
    protected function getUserSettingsInformation()
    {
        // we already called this method before, simply returns the result
        if (is_array($this->userSettingsInformation)) {
            return $this->userSettingsInformation;
        }
        require_once PIWIK_INCLUDE_PATH . '/libs/UserAgentParser/UserAgentParser.php';

        list($plugin_Flash, $plugin_Java, $plugin_Director, $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF,
            $plugin_WindowsMedia, $plugin_Gears, $plugin_Silverlight, $plugin_Cookie) = $this->request->getPlugins();

        $resolution = $this->request->getParam('res');
        $userAgent = $this->request->getUserAgent();
        $aBrowserInfo = UserAgentParser::getBrowser($userAgent);

        $browserName = ($aBrowserInfo !== false && $aBrowserInfo['id'] !== false) ? $aBrowserInfo['id'] : 'UNK';
        $browserVersion = ($aBrowserInfo !== false && $aBrowserInfo['version'] !== false) ? $aBrowserInfo['version'] : '';

        $os = UserAgentParser::getOperatingSystem($userAgent);
        $os = $os === false ? 'UNK' : $os['id'];

        $browserLang = substr($this->request->getBrowserLanguage(), 0, 20); // limit the length of this string to match db
        $configurationHash = $this->getConfigHash(
            $os,
            $browserName,
            $browserVersion,
            $plugin_Flash,
            $plugin_Java,
            $plugin_Director,
            $plugin_Quicktime,
            $plugin_RealPlayer,
            $plugin_PDF,
            $plugin_WindowsMedia,
            $plugin_Gears,
            $plugin_Silverlight,
            $plugin_Cookie,
            $this->getVisitorIp(),
            $browserLang);

        $this->userSettingsInformation = array(
            'config_id'              => $configurationHash,
            'config_os'              => $os,
            'config_browser_name'    => $browserName,
            'config_browser_version' => $browserVersion,
            'config_resolution'      => $resolution,
            'config_pdf'             => $plugin_PDF,
            'config_flash'           => $plugin_Flash,
            'config_java'            => $plugin_Java,
            'config_director'        => $plugin_Director,
            'config_quicktime'       => $plugin_Quicktime,
            'config_realplayer'      => $plugin_RealPlayer,
            'config_windowsmedia'    => $plugin_WindowsMedia,
            'config_gears'           => $plugin_Gears,
            'config_silverlight'     => $plugin_Silverlight,
            'config_cookie'          => $plugin_Cookie,
            'location_browser_lang'  => $browserLang,
        );
        return $this->userSettingsInformation;
    }

    /**
     * Returns true if the last action was done during the last 30 minutes
     * @return bool
     */
    protected function isLastActionInTheSameVisit()
    {
        return isset($this->visitorInfo['visit_last_action_time'])
        && ($this->visitorInfo['visit_last_action_time']
            > ($this->request->getCurrentTimestamp() - Config::getInstance()->Tracker['visit_standard_length']));
    }

    /**
     * Returns true if the recognizeTheVisitor() method did recognize the visitor
     * @return bool
     */
    protected function isVisitorKnown()
    {
        return $this->visitorKnown === true;
    }

    /**
     * Returns a 64-bit hash of all the configuration settings
     * @param $os
     * @param $browserName
     * @param $browserVersion
     * @param $plugin_Flash
     * @param $plugin_Java
     * @param $plugin_Director
     * @param $plugin_Quicktime
     * @param $plugin_RealPlayer
     * @param $plugin_PDF
     * @param $plugin_WindowsMedia
     * @param $plugin_Gears
     * @param $plugin_Silverlight
     * @param $plugin_Cookie
     * @param $ip
     * @param $browserLang
     * @return string
     */
    protected function getConfigHash($os, $browserName, $browserVersion, $plugin_Flash, $plugin_Java, $plugin_Director, $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF, $plugin_WindowsMedia, $plugin_Gears, $plugin_Silverlight, $plugin_Cookie, $ip, $browserLang)
    {
        $hash = md5($os . $browserName . $browserVersion . $plugin_Flash . $plugin_Java . $plugin_Director . $plugin_Quicktime . $plugin_RealPlayer . $plugin_PDF . $plugin_WindowsMedia . $plugin_Gears . $plugin_Silverlight . $plugin_Cookie . $ip . $browserLang, $raw_output = true);
        return substr($hash, 0, Tracker::LENGTH_BINARY_ID);
    }

    /**
     * Returns either
     * - "-1" for a known visitor
     * - at least 16 char identifier in hex @see Common::generateUniqId()
     * @return int|string
     */
    protected function getVisitorUniqueId()
    {
        if ($this->isVisitorKnown()) {
            return -1;
        }
        return Common::generateUniqId();
    }

    // is the referrer host any of the registered URLs for this website?
    static public function isHostKnownAliasHost($urlHost, $idSite)
    {
        $websiteData = Cache::getCacheWebsiteAttributes($idSite);
        if (isset($websiteData['hosts'])) {
            $canonicalHosts = array();
            foreach ($websiteData['hosts'] as $host) {
                $canonicalHosts[] = str_replace('www.', '', mb_strtolower($host, 'UTF-8'));
            }
            $canonicalHost = str_replace('www.', '', mb_strtolower($urlHost, 'UTF-8'));
            if (in_array($canonicalHost, $canonicalHosts)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function insertNewVisit($visit)
    {
        $fields = implode(", ", array_keys($visit));
        $values = Common::getSqlStringFieldsArray($visit);

        $sql = "INSERT INTO " . Common::prefixTable('log_visit') . " ($fields) VALUES ($values)";
        $bind = array_values($visit);
        Tracker::getDatabase()->query($sql, $bind);

        $idVisit = Tracker::getDatabase()->lastInsertId();
        return $idVisit;
    }

    /**
     * @param $valuesToUpdate
     * @throws VisitorNotFoundInDb
     */
    protected function updateExistingVisit($valuesToUpdate)
    {
        $sqlQuery = "UPDATE " . Common::prefixTable('log_visit') . "
                    SET %s
                    WHERE idsite = ?
                        AND idvisit = ?";
        // build sql query
        $updateParts = $sqlBind = array();
        foreach ($valuesToUpdate AS $name => $value) {
            // Case where bind parameters don't work
            if(strpos($value, $name) !== false) {
                //$name = 'visit_total_events'
                //$value = 'visit_total_events + 1';
                $updateParts[] = " $name = $value ";
            } else {
                $updateParts[] = $name . " = ?";
                $sqlBind[] = $value;
            }
        }
        $sqlQuery = sprintf($sqlQuery, implode($updateParts, ', ') );
        array_push($sqlBind, $this->request->getIdSite(), (int)$this->visitorInfo['idvisit']);

        $result = Tracker::getDatabase()->query($sqlQuery, $sqlBind);

        $this->visitorInfo['visit_last_action_time'] = $this->request->getCurrentTimestamp();

        // Debug output
        if (isset($valuesToUpdate['idvisitor'])) {
            $valuesToUpdate['idvisitor'] = bin2hex($valuesToUpdate['idvisitor']);
        }
        Common::printDebug('Updating existing visit: ' . var_export($valuesToUpdate, true));

        if (Tracker::getDatabase()->rowCount($result) == 0) {
            Common::printDebug("Visitor with this idvisit wasn't found in the DB.");
            Common::printDebug("$sqlQuery --- ");
            Common::printDebug($sqlBind);
            throw new VisitorNotFoundInDb(
                "The visitor with idvisitor=" . bin2hex($this->visitorInfo['idvisitor']) . " and idvisit=" . $this->visitorInfo['idvisit']
                . " wasn't found in the DB, we fallback to a new visitor");
        }
    }

    protected function printVisitorInformation()
    {
        $debugVisitInfo = $this->visitorInfo;
        $debugVisitInfo['idvisitor'] = bin2hex($debugVisitInfo['idvisitor']);
        $debugVisitInfo['config_id'] = bin2hex($debugVisitInfo['config_id']);
        Common::printDebug($debugVisitInfo);
    }

    protected function getNewVisitorInformation($action)
    {
        $actionType = $idActionName = $idActionUrl = false;
        if($action) {
            $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
            $actionType = $action->getActionType();
        }

        $daysSinceFirstVisit = $this->request->getDaysSinceFirstVisit();
        $visitCount = $this->request->getVisitCount();
        $daysSinceLastVisit = $this->request->getDaysSinceLastVisit();

        $daysSinceLastOrder = $this->request->getDaysSinceLastOrder();
        $isReturningCustomer = ($daysSinceLastOrder !== false);

        if ($daysSinceLastOrder === false) {
            $daysSinceLastOrder = 0;
        }

        // User settings
        $userInfo = $this->getUserSettingsInformation();

        // Referrer data
        $referrer = new Referrer();
        $referrerUrl = $this->request->getParam('urlref');
        $currentUrl = $this->request->getParam('url');
        $referrerInfo = $referrer->getReferrerInformation($referrerUrl, $currentUrl, $this->request->getIdSite());

        $visitorReturning = $isReturningCustomer
            ? 2 /* Returning customer */
            : ($visitCount > 1 || $this->isVisitorKnown() || $daysSinceLastVisit > 0
                ? 1 /* Returning */
                : 0 /* New */);
        $defaultTimeOnePageVisit = Config::getInstance()->Tracker['default_time_one_page_visit'];

        return array(
            'idsite'                    => $this->request->getIdSite(),
            'visitor_localtime'         => $this->request->getLocalTime(),
            'idvisitor'                 => $this->getVisitorIdcookie(),
            'visitor_returning'         => $visitorReturning,
            'visitor_count_visits'      => $visitCount,
            'visitor_days_since_last'   => $daysSinceLastVisit,
            'visitor_days_since_order'  => $daysSinceLastOrder,
            'visitor_days_since_first'  => $daysSinceFirstVisit,
            'visit_first_action_time'   => Tracker::getDatetimeFromTimestamp($this->request->getCurrentTimestamp()),
            'visit_last_action_time'    => Tracker::getDatetimeFromTimestamp($this->request->getCurrentTimestamp()),
            'visit_entry_idaction_url'  => (int)$idActionUrl,
            'visit_entry_idaction_name' => (int)$idActionName,
            'visit_exit_idaction_url'   => (int)$idActionUrl,
            'visit_exit_idaction_name'  => (int)$idActionName,
            'visit_total_actions'       => in_array($actionType,
                    array(Action::TYPE_PAGE_URL,
                          Action::TYPE_DOWNLOAD,
                          Action::TYPE_OUTLINK,
                          Action::TYPE_SITE_SEARCH,
                          Action::TYPE_EVENT))
                    ? 1 : 0, // if visit starts with something else (e.g. ecommerce order), don't record as an action
            'visit_total_searches'      => $actionType == Action::TYPE_SITE_SEARCH ? 1 : 0,
            'visit_total_events'        => $actionType == Action::TYPE_EVENT ? 1 : 0,
            'visit_total_time'          => self::cleanupVisitTotalTime($defaultTimeOnePageVisit),
            'visit_goal_buyer'          => $this->goalManager->getBuyerType(),
            'referer_type'              => $referrerInfo['referer_type'],
            'referer_name'              => $referrerInfo['referer_name'],
            'referer_url'               => $referrerInfo['referer_url'],
            'referer_keyword'           => $referrerInfo['referer_keyword'],
            'config_id'                 => $userInfo['config_id'],
            'config_os'                 => $userInfo['config_os'],
            'config_browser_name'       => $userInfo['config_browser_name'],
            'config_browser_version'    => $userInfo['config_browser_version'],
            'config_resolution'         => $userInfo['config_resolution'],
            'config_pdf'                => $userInfo['config_pdf'],
            'config_flash'              => $userInfo['config_flash'],
            'config_java'               => $userInfo['config_java'],
            'config_director'           => $userInfo['config_director'],
            'config_quicktime'          => $userInfo['config_quicktime'],
            'config_realplayer'         => $userInfo['config_realplayer'],
            'config_windowsmedia'       => $userInfo['config_windowsmedia'],
            'config_gears'              => $userInfo['config_gears'],
            'config_silverlight'        => $userInfo['config_silverlight'],
            'config_cookie'             => $userInfo['config_cookie'],
            'location_ip'               => $this->getVisitorIp(),
            'location_browser_lang'     => $userInfo['location_browser_lang'],
        );
    }

    /**
     * Gather fields=>values that needs to be updated for the existing visit in log_visit
     *
     * @param $action
     * @param $visitIsConverted
     * @return array
     */
    protected function getExistingVisitFieldsToUpdate($action, $visitIsConverted)
    {
        $valuesToUpdate = array();

        if ($action) {
            $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();
            $idActionName = $action->getIdActionNameForEntryAndExitIds();
            $actionType = $action->getActionType();

            if ($idActionName !== false) {
                $valuesToUpdate['visit_exit_idaction_name'] = $idActionName;
            }

            $incrementActions = false;
            if ($idActionUrl !== false) {
                $valuesToUpdate['visit_exit_idaction_url'] = $idActionUrl;
                $incrementActions = true;
            }
            if ($actionType == Action::TYPE_SITE_SEARCH) {
                $valuesToUpdate['visit_total_searches'] = 'visit_total_searches + 1';
                $incrementActions = true;
            } else if ($actionType == Action::TYPE_EVENT) {
                $valuesToUpdate['visit_total_events'] = 'visit_total_events + 1';
                $incrementActions = true;
            }

            if ($incrementActions) {
                $valuesToUpdate['visit_total_actions'] = 'visit_total_actions + 1';
            }
        }

        $datetimeServer = Tracker::getDatetimeFromTimestamp($this->request->getCurrentTimestamp());
        $valuesToUpdate['visit_last_action_time'] = $datetimeServer;

        // Add 1 so it's always > 0
        $visitTotalTime = 1 + $this->request->getCurrentTimestamp() - $this->visitorInfo['visit_first_action_time'];
        $valuesToUpdate['visit_total_time'] = self::cleanupVisitTotalTime($visitTotalTime);

        // Goal conversion
        if ($visitIsConverted) {
            $valuesToUpdate['visit_goal_converted'] = 1;
            // If a pageview and goal conversion in the same second, with previously a goal conversion recorded
            // the request would not "update" the row since all values are the same as previous
            // therefore the request below throws exception, instead we make sure the UPDATE will affect the row
            $valuesToUpdate['visit_total_time'] = self::cleanupVisitTotalTime(
                $valuesToUpdate['visit_total_time']
                + $this->goalManager->idGoal
                // +2 to offset idgoal=-1 and idgoal=0
                + 2);
        }

        // Might update the idvisitor when it was forced or overwritten for this visit
        if (strlen($this->visitorInfo['idvisitor']) == Tracker::LENGTH_BINARY_ID) {
            $valuesToUpdate['idvisitor'] = $this->visitorInfo['idvisitor'];
        }

        // Ecommerce buyer status
        $valuesToUpdate['visit_goal_buyer'] = $this->goalManager->getBuyerType($this->visitorInfo['visit_goal_buyer']);

        // Custom Variables overwrite previous values on each page view
        $valuesToUpdate = array_merge($valuesToUpdate, $this->visitorCustomVariables);
        return $valuesToUpdate;
    }

    /**
     * @return array
     */
    public static function getVisitFieldsPersist()
    {
        $fields = array(
            'idvisitor',
            'idvisit',
            'visit_exit_idaction_url',
            'visit_exit_idaction_name',
            'visitor_returning',
            'visitor_days_since_first',
            'visitor_days_since_order',
            'visitor_count_visits',
            'visit_goal_buyer',

            'location_country',
            'location_region',
            'location_city',
            'location_latitude',
            'location_longitude',

            'referer_name',
            'referer_keyword',
            'referer_type',
        );

        /**
         * Triggered when checking if the current action being tracked belongs to an existing visit.
         * 
         * This event collects a list of [visit entity]() properties that should be loaded when reading
         * the existing visit. Properties that appear in this list will be available in other tracking
         * events such as {@hook Tracker.newConversionInformation} and {@hook Tracker.newVisitorInformation}.
         * 
         * Plugins can use this event to load additional visit entity properties for later use during tracking.
         * When you add fields to this $fields array, they will be later available in Tracker.newConversionInformation
         * 
         * **Example**
         * 
         *     Piwik::addAction('Tracker.getVisitFieldsToPersist', function (&$fields) {
         *         $fields[] = 'custom_visit_property';
         *     });
         * 
         * @param array &$fields The list of visit properties to load.
         */
        Piwik::postEvent('Tracker.getVisitFieldsToPersist', array(&$fields));

        return $fields;
    }
}
