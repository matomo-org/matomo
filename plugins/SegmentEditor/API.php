<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SegmentEditor
 */

/**
 * The SegmentEditor API lets you add, update, delete custom Segments, and list saved segments.
 *
 * @package Piwik_SegmentEditor
 */
class Piwik_SegmentEditor_API
{
    static private $instance = null;

    /**
     * @return Piwik_SegmentEditor_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function checkSegmentValue($definition, $idSite)
    {
        try {
            $segment = new Piwik_Segment($definition, $idSite);
            $segment->getHash();
        } catch (Exception $e) {
            throw new Exception("The specified segment is invalid: " . $e->getMessage());
        }
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
            throw new Exception("&enabledAllUsers=1 requires Super User access");
        }
        return $enabledAllUsers;
    }


    /**
     * @param $idSite
     * @throws Exception
     */
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
    }

    protected function checkAutoArchive($autoArchive, $idSite)
    {
        $autoArchive = (int)$autoArchive;
        if ($autoArchive) {
            $exception = new Exception("To prevent abuse, autoArchive=1 requires Super User or Admin access.");
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

    public function delete($idSegment)
    {
        $segment = $this->getSegmentOrFail($idSegment);
        $db = Zend_Registry::get('db');
        $db->delete(Piwik_Common::prefixTable('segment'), 'idsegment = ' . $idSegment);
        return true;
    }

    public function update($idSegment, $name, $definition, $idSite = false, $autoArchive = false, $enabledAllUsers = false)
    {
        $this->checkIdSite($idSite);
        $this->checkSegmentName($name);
        $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        $segment = $this->getSegmentOrFail($idSegment);
        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'enable_all_users'   => $enabledAllUsers,
            'enable_only_idsite' => $idSite,
            'auto_archive'       => $autoArchive,
            'ts_last_edit'       => Piwik_Date::now()->getDatetime(),
        );

        $db = Zend_Registry::get('db');
        $db->update(Piwik_Common::prefixTable("segment"),
            $bind,
            "idsegment = $idSegment"
        );
        return true;
    }


    public function add($name, $definition, $idSite = false, $autoArchive = false, $enabledAllUsers = false)
    {
        Piwik::checkUserIsNotAnonymous();
        $this->checkIdSite($idSite);
        $this->checkSegmentName($name);
        $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        $db = Zend_Registry::get('db');
        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'login'              => Piwik::getCurrentUserLogin(),
            'enable_all_users'   => $enabledAllUsers,
            'enable_only_idsite' => $idSite,
            'auto_archive'       => $autoArchive,
            'ts_created'         => Piwik_Date::now()->getDatetime(),
            'deleted'            => 0,
        );
        $db->insert(Piwik_Common::prefixTable("segment"), $bind);
        return $db->lastInsertId();
    }

    public function getSegmentsToAutoArchive($idSite = false)
    {
        Piwik::checkUserIsSuperUser();

        $sqlRestrictSite = '';
        $bind = array();
        if ($idSite) {
            $sqlRestrictSite = 'OR enable_only_idsite = ?';
            $bind = array($idSite);
        }
        $segments = Zend_Registry::get('db')->fetchAll("SELECT *
                                 FROM " . Piwik_Common::prefixTable("segment") . "
                                 WHERE auto_archive = 1
                                    AND deleted = 0
                                    AND (enable_only_idsite IS NULL " . $sqlRestrictSite . " )", $bind
        );
        return $segments;
    }

    public function get($idSegment)
    {
        Piwik::checkUserHasSomeViewAccess();
        if (!is_numeric($idSegment)) {
            throw new Exception("idSegment should be numeric.");
        }
        $segment = Zend_Registry::get('db')->fetchRow("SELECT * " .
            " FROM " . Piwik_Common::prefixTable("segment") .
            " WHERE idsegment = ?", $idSegment);

        if (empty($segment)) {
            return false;
        }
        try {
            Piwik::checkUserIsSuperUserOrTheUser($segment['login']);
        } catch (Exception $e) {
            throw new Exception("You can only manage your own segments (unless you are Super User).");
        }

        if ($segment['deleted']) {
            throw new Exception("This segment is marked as deleted.");
        }
        return $segment;
    }

    /**
     * @param $idSegment
     * @throws Exception
     */
    protected function getSegmentOrFail($idSegment)
    {
        $segment = $this->get($idSegment);

        if (empty($segment)) {
            throw new Exception("Requested segment not found");
        }
        return $segment;
    }

    public function getAll($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $sql = "SELECT * " .
            " FROM " . Piwik_Common::prefixTable("segment") .
            " WHERE (enable_only_idsite = ? OR enable_only_idsite IS NULL)
                AND  (enable_all_users = 1 OR login = ?)
                AND deleted = 0
              ORDER BY name ASC";
        $bind = array($idSite, Piwik::getCurrentUserLogin());
        $segments = Zend_Registry::get('db')->fetchAll($sql, $bind);

        return $segments;
    }
}
