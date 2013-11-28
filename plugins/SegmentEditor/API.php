<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package SegmentEditor
 */
namespace Piwik\Plugins\SegmentEditor;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Segment;

/**
 * The SegmentEditor API lets you add, update, delete custom Segments, and list saved segments.a
 *
 * @package SegmentEditor
 * @method static \Piwik\Plugins\SegmentEditor\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const DEACTIVATE_SEGMENT_EVENT = 'SegmentEditor.deactivate';

    protected function checkSegmentValue($definition, $idSite)
    {
        // unsanitize so we don't record the HTML entitied segment
        $definition = Common::unsanitizeInputValue($definition);
        $definition = str_replace("#", '%23', $definition); // hash delimiter
        $definition = str_replace("'", '%27', $definition); // not encoded in JS
        $definition = str_replace("&", '%26', $definition);

        try {
            $segment = new Segment($definition, $idSite);
            $segment->getHash();
        } catch (Exception $e) {
            throw new Exception("The specified segment is invalid: " . $e->getMessage());
        }
        return $definition;
    }

    protected function checkSegmentName($name)
    {
        if (empty($name)) {
            throw new Exception("Invalid name for this custom segment.");
        }
    }

    protected function checkEnabledAllUsers($enabledAllUsers)
    {
        $enabledAllUsers = (int)$enabledAllUsers;
        if ($enabledAllUsers
            && !Piwik::isUserIsSuperUser()
        ) {
            throw new Exception("enabledAllUsers=1 requires Super User access");
        }
        return $enabledAllUsers;
    }

    protected function checkIdSite($idSite)
    {
        if (empty($idSite)) {
            if (!Piwik::isUserIsSuperUser()) {
                throw new Exception("idSite is required, unless you are Super User and can create the segment across all websites");
            }
        } else {
            if (!is_numeric($idSite)) {
                throw new Exception("idSite should be a numeric value");
            }
            Piwik::checkUserHasViewAccess($idSite);
        }
        $idSite = (int)$idSite;
        return $idSite;
    }

    protected function checkAutoArchive($autoArchive, $idSite)
    {
        $autoArchive = (int)$autoArchive;
        if ($autoArchive) {
            $exception = new Exception("To prevent abuse, autoArchive=1 requires Super User or ControllerAdmin access.");
            if (empty($idSite)) {
                if (!Piwik::isUserIsSuperUser()) {
                    throw $exception;
                }
            } else {
                if (!Piwik::isUserHasAdminAccess($idSite)) {
                    throw $exception;
                }
            }
        }
        return $autoArchive;
    }

    protected function getSegmentOrFail($idSegment)
    {
        $segment = $this->get($idSegment);

        if (empty($segment)) {
            throw new Exception("Requested segment not found");
        }
        return $segment;
    }

    protected function checkUserIsNotAnonymous()
    {
        if (Piwik::isUserIsAnonymous()) {
            throw new Exception("To create, edit or delete Custom Segments, please sign in first.");
        }
    }

    /**
     * Deletes a stored segment.
     *
     * @param $idSegment
     * @return bool
     */
    public function delete($idSegment)
    {
        $this->checkUserIsNotAnonymous();

        $this->sendSegmentDeactivationEvent($idSegment);

        $this->getSegmentOrFail($idSegment);

        $db = Db::get();
        $db->delete(Common::prefixTable('segment'), 'idsegment = ' . $idSegment);
        return true;
    }

    /**
     * Modifies an existing stored segment.
     *
     * @param int $idSegment The ID of the stored segment to modify.
     * @param string $name The new name of the segment.
     * @param string $definition The new definition of the segment.
     * @param bool $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     *
     * @return bool
     */
    public function update($idSegment, $name, $definition, $idSite = false, $autoArchive = false, $enabledAllUsers = false)
    {
        $this->checkUserIsNotAnonymous();
        $segment = $this->getSegmentOrFail($idSegment);

        $idSite = $this->checkIdSite($idSite);
        $this->checkSegmentName($name);
        $definition = $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        if ($this->segmentVisibilityIsReduced($idSite, $enabledAllUsers, $segment)) {
            $this->sendSegmentDeactivationEvent($idSegment);
        }

        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'enable_all_users'   => $enabledAllUsers,
            'enable_only_idsite' => $idSite,
            'auto_archive'       => $autoArchive,
            'ts_last_edit'       => Date::now()->getDatetime(),
        );

        $db = Db::get();
        $db->update(Common::prefixTable("segment"),
            $bind,
            "idsegment = $idSegment"
        );
        return true;
    }

    /**
     * Adds a new stored segment.
     *
     * @param string $name The new name of the segment.
     * @param string $definition The new definition of the segment.
     * @param bool $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     *
     * @return int The newly created segment Id
     */
    public function add($name, $definition, $idSite = false, $autoArchive = false, $enabledAllUsers = false)
    {
        $this->checkUserIsNotAnonymous();
        $idSite = $this->checkIdSite($idSite);
        $this->checkSegmentName($name);
        $definition = $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        $db = Db::get();
        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'login'              => Piwik::getCurrentUserLogin(),
            'enable_all_users'   => $enabledAllUsers,
            'enable_only_idsite' => $idSite,
            'auto_archive'       => $autoArchive,
            'ts_created'         => Date::now()->getDatetime(),
            'deleted'            => 0,
        );
        $db->insert(Common::prefixTable("segment"), $bind);
        return $db->lastInsertId();
    }

    /**
     * Returns a stored segment by ID
     *
     * @param $idSegment
     * @throws Exception
     * @return bool
     */
    public function get($idSegment)
    {
        Piwik::checkUserHasSomeViewAccess();
        if (!is_numeric($idSegment)) {
            throw new Exception("idSegment should be numeric.");
        }
        $segment = Db::get()->fetchRow("SELECT * " .
            " FROM " . Common::prefixTable("segment") .
            " WHERE idsegment = ?", $idSegment);

        if (empty($segment)) {
            return false;
        }
        try {

            if (!$segment['enable_all_users']) {
                Piwik::checkUserIsSuperUserOrTheUser($segment['login']);
            }

        } catch (Exception $e) {
            throw new Exception("You can only edit the custom segments you have created yourself. This segment was created and 'shared with you' by the Super User. " .
                "To modify this segment, you can first create a new one by clicking on 'Add new segment'. Then you can customize the segment's definition.");
        }

        if ($segment['deleted']) {
            throw new Exception("This segment is marked as deleted. ");
        }
        return $segment;
    }

    /**
     * Returns all stored segments.
     *
     * @param bool $idSite Whether to return stored segments that are only auto-archived for a specific idSite, or all of them. If supplied, must be a valid site ID.
     * @param bool $returnOnlyAutoArchived Whether to only return stored segments that are auto-archived or not.
     * @return array
     */
    public function getAll($idSite = false, $returnOnlyAutoArchived = false)
    {
        if (!empty($idSite)) {
            Piwik::checkUserHasViewAccess($idSite);
        } else {
            Piwik::checkUserHasSomeViewAccess();
        }
        $bind = array();

        // Build basic segment filtering
        $whereIdSite = '';
        if (!empty($idSite)) {
            $whereIdSite = 'enable_only_idsite = ? OR ';
            $bind[] = $idSite;
        }

        $bind[] = Piwik::getCurrentUserLogin();

        $extraWhere = '';
        if ($returnOnlyAutoArchived) {
            $extraWhere = ' AND auto_archive = 1';
        }

        // Query
        $sql = "SELECT * " .
            " FROM " . Common::prefixTable("segment") .
            " WHERE ($whereIdSite enable_only_idsite = 0)
                        AND  (enable_all_users = 1 OR login = ?)
                        AND deleted = 0
                        $extraWhere
                      ORDER BY name ASC";
        $segments = Db::get()->fetchAll($sql, $bind);

        return $segments;
    }

    /**
     * When deleting or making a segment invisible, allow plugins to throw an exception or propagate the action
     *
     * @param $idSegment
     */
    private function sendSegmentDeactivationEvent($idSegment)
    {
        /**
         * Triggered before a segment is deleted or made invisible.
         * 
         * This event can be used by plugins to throw an exception
         * or do something else.
         * 
         * @param int $idSegment The ID of the segment being deleted.
         */
        Piwik::postEvent(self::DEACTIVATE_SEGMENT_EVENT, array($idSegment));
    }

    /**
     * @param $idSiteNewValue
     * @param $enableAllUserNewValue
     * @param $segment
     * @return bool
     */
    private function segmentVisibilityIsReduced($idSiteNewValue, $enableAllUserNewValue, $segment)
    {
        $allUserVisibilityIsDropped = $segment['enable_all_users'] && !$enableAllUserNewValue;
        $allWebsiteVisibilityIsDropped = !isset($segment['idSite']) && $idSiteNewValue;

        return $allUserVisibilityIsDropped || $allWebsiteVisibilityIsDropped;
    }
}
