<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Config;
use Piwik\Segment;
use Piwik\Cache;
use Piwik\Url;

/**
 * The SegmentEditor API lets you add, update, delete custom Segments, and list saved segments.
 *
 * @method static \Piwik\Plugins\SegmentEditor\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var SegmentArchiving
     */
    private $segmentArchiving;

    private $processNewSegmentsFrom;

    protected $autoSanitizeInputParams = false;

    public function __construct(Model $model, SegmentArchiving $segmentArchiving)
    {
        $this->model = $model;
        $this->segmentArchiving = $segmentArchiving;
        $this->processNewSegmentsFrom = StaticContainer::get('ini.General.process_new_segments_from');
    }

    protected function checkSegmentValue(string $definition, ?int $idSite): string
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

    protected function checkSegmentName(string $name): void
    {
        if (empty($name)) {
            throw new Exception("Invalid name for this custom segment.");
        }
    }

    protected function checkEnabledAllUsers(bool $enabledAllUsers): bool
    {
        if (
            $enabledAllUsers
            && !Piwik::hasUserSuperUserAccess()
        ) {
            throw new Exception("enabledAllUsers=1 requires Super User access");
        }
        return $enabledAllUsers;
    }

    protected function checkIdSite($idSite): ?int
    {
        if (empty($idSite)) {
            if (!Piwik::hasUserSuperUserAccess()) {
                throw new Exception($this->getMessageCannotEditSegmentCreatedBySuperUser());
            }

            return null;
        } else {
            if (!is_numeric($idSite)) {
                throw new Exception("idSite should be a numeric value");
            }

            Piwik::checkUserHasViewAccess($idSite);
        }
        return (int) $idSite;
    }

    protected function checkAutoArchive(bool $autoArchive, ?int $idSite): bool
    {
        // Segment 'All websites' and pre-processed requires Super User
        if (null === $idSite && $autoArchive) {
            if (!Piwik::hasUserSuperUserAccess()) {
                throw new Exception(
                    "Please contact Support to make these changes on your behalf. " .
                    " To modify a pre-processed segment for all websites, a user must have super user access. "
                );
            }
        }

        // if real-time segments are disabled, then allow user to create pre-processed report
        $realTimeSegmentsEnabled = SegmentEditor::isCreateRealtimeSegmentsEnabled();
        if (!$realTimeSegmentsEnabled && !$autoArchive) {
            throw new Exception(
                "Real time segments are disabled. You need to enable auto archiving."
            );
        }

        if ($autoArchive) {
            if (Rules::isBrowserTriggerEnabled()) {
                $message = "Pre-processed segments can only be created if browser triggered archiving is disabled.";
                if (Piwik::hasUserSuperUserAccess()) {
                    $message .= " To disable browser archiving follow the instructions here: " . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/setup-auto-archiving/');
                }
                throw new Exception($message);
            }

            Piwik::checkUserHasViewAccess($idSite);
        }

        return $autoArchive;
    }

    protected function getSegmentOrFail(int $idSegment): array
    {
        $segment = $this->get($idSegment);

        if (empty($segment)) {
            throw new Exception("Requested segment not found");
        }

        return $segment;
    }

    protected function checkUserCanAddNewSegment(?int $idSite): void
    {
        if (
            null === $idSite
            && !SegmentEditor::isAddingSegmentsForAllWebsitesEnabled()
        ) {
            throw new Exception(Piwik::translate('SegmentEditor_AddingSegmentForAllWebsitesDisabled'));
        }

        if (!$this->isUserCanAddNewSegment($idSite)) {
            throw new Exception(Piwik::translate('SegmentEditor_YouDontHaveAccessToCreateSegments'));
        }
    }

    public function isUserCanAddNewSegment(?int $idSite): bool
    {
        if (Piwik::isUserIsAnonymous()) {
            return false;
        }

        $requiredAccess = Config::getInstance()->General['adding_segment_requires_access'];

        $authorized =
            ($requiredAccess == 'view' && Piwik::isUserHasViewAccess($idSite)) ||
            ($requiredAccess == 'admin' && Piwik::isUserHasAdminAccess($idSite)) ||
            ($requiredAccess == 'write' && Piwik::isUserHasWriteAccess($idSite)) ||
            ($requiredAccess == 'superuser' && Piwik::hasUserSuperUserAccess())
        ;

        return $authorized;
    }

    protected function checkUserCanEditOrDeleteSegment(array $segment): void
    {
        if (Piwik::hasUserSuperUserAccess()) {
            return;
        }

        Piwik::checkUserIsNotAnonymous();

        if ($segment['login'] !== Piwik::getCurrentUserLogin()) {
            throw new Exception($this->getMessageCannotEditSegmentCreatedBySuperUser());
        }

        if ((int) $segment['enable_only_idsite'] === 0 && !Piwik::hasUserSuperUserAccess()) {
            throw new Exception(Piwik::translate('SegmentEditor_UpdatingAllSitesSegmentPermittedToSuperUser'));
        }
    }

    /**
     * Deletes a stored segment.
     *
     * @param int $idSegment
     */
    public function delete(int $idSegment): void
    {
        $segment = $this->getSegmentOrFail($idSegment);
        $this->checkUserCanEditOrDeleteSegment($segment);

        /**
         * Triggered before a segment is deleted or made invisible.
         *
         * This event can be used by plugins to throw an exception
         * or do something else.
         *
         * @param int $idSegment The ID of the segment being deleted.
         */
        Piwik::postEvent('SegmentEditor.deactivate', array($idSegment));

        $this->getModel()->deleteSegment($idSegment);

        Cache::getEagerCache()->flushAll();
    }

    private function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Modifies an existing stored segment.
     *
     * @param int $idSegment The ID of the stored segment to modify.
     * @param string $name The new name of the segment.
     * @param string $definition The new definition of the segment.
     * @param int|string|null $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     */
    public function update(
        int $idSegment,
        string $name,
        string $definition,
        $idSite = null,
        bool $autoArchive = false,
        bool $enabledAllUsers = false
    ): void {
        $segment = $this->getSegmentOrFail($idSegment);
        $this->checkUserCanEditOrDeleteSegment($segment);

        $idSite = $this->checkIdSite($idSite);
        $name = Common::sanitizeInputValue($name);
        $this->checkSegmentName($name);
        $definition = Common::sanitizeInputValue($definition);
        $definition = $this->checkSegmentValue($definition, $idSite);

        // only check param if value is changed
        // this ensure that a segment from a user with lower permission can still be changed by them
        // if a superuser updated the segment to be available for all users
        if ((int) $segment['enable_all_users'] !== (int) $enabledAllUsers && !Piwik::hasUserSuperUserAccess()) {
            throw new Exception('Changing value for enabledAllUsers is permitted to super users only.');
        }

        $autoArchive     = $this->checkAutoArchive($autoArchive, $idSite);

        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'enable_all_users'   => (int) $enabledAllUsers,
            'enable_only_idsite' => (int) $idSite,
            'auto_archive'       => (int) $autoArchive,
            'ts_last_edit'       => Date::now()->getDatetime(),
        );

        /**
         * Triggered before a segment is modified.
         *
         * This event can be used by plugins to throw an exception
         * or do something else.
         *
         * @param int $idSegment The ID of the segment which visibility is reduced.
         */
        Piwik::postEvent('SegmentEditor.update', array($idSegment, $bind));

        $this->getModel()->updateSegment($idSegment, $bind);

        $segmentDefinitionChanged = $segment['definition'] !== $definition;

        if ($segmentDefinitionChanged && $autoArchive && !Rules::isBrowserTriggerEnabled()) {
            $updatedSegment = $this->getModel()->getSegment($idSegment);
            $this->segmentArchiving->reArchiveSegment($updatedSegment);
        }

        Cache::getEagerCache()->flushAll();
    }

    /**
     * Adds a new stored segment.
     *
     * @param string $name The new name of the segment.
     * @param string $definition The new definition of the segment.
     * @param null|string|int $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     *
     * @return int The newly created segment Id
     */
    public function add(
        string $name,
        string $definition,
        $idSite = null,
        bool $autoArchive = false,
        bool $enabledAllUsers = false
    ): int {
        $idSite = $this->checkIdSite($idSite);
        $this->checkUserCanAddNewSegment($idSite);
        $name = Common::sanitizeInputValue($name);
        $this->checkSegmentName($name);
        $definition = Common::sanitizeInputValue($definition);
        $definition = $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        $bind = array(
            'name'               => $name,
            'definition'         => $definition,
            'login'              => Piwik::getCurrentUserLogin(),
            'enable_all_users'   => (int) $enabledAllUsers,
            'enable_only_idsite' => (int) $idSite,
            'auto_archive'       => (int) $autoArchive,
            'ts_created'         => Date::now()->getDatetime(),
            'deleted'            => 0,
        );

        $id = $this->getModel()->createSegment($bind);

        Cache::getEagerCache()->flushAll();

        if (
            $autoArchive
            && !Rules::isBrowserTriggerEnabled()
            && $this->processNewSegmentsFrom != SegmentArchiving::CREATION_TIME
        ) {
            $addedSegment = $this->getModel()->getSegment($id);
            $this->segmentArchiving->reArchiveSegment($addedSegment);
        }

        return $id;
    }

    /**
     * Returns a stored segment by ID
     *
     * @param int $idSegment
     * @throws Exception
     * @return array|null
     */
    public function get(int $idSegment): ?array
    {
        Piwik::checkUserHasSomeViewAccess();

        $segment = $this->getModel()->getSegment($idSegment);

        if (empty($segment)) {
            return null;
        }
        try {
            if (!$segment['enable_all_users']) {
                Piwik::checkUserHasSuperUserAccessOrIsTheUser($segment['login']);
            }
        } catch (Exception $e) {
            throw new Exception($this->getMessageCannotEditSegmentCreatedBySuperUser());
        }

        if ($segment['deleted']) {
            throw new Exception("This segment is marked as deleted. ");
        }

        return $segment;
    }

    /**
     * Returns all stored segments.
     *
     * @param null|string|int $idSite Whether to return stored segments for a specific idSite, or all of them. If supplied, must be a valid site ID.
     * @return array
     */
    public function getAll($idSite = null): array
    {
        if (!empty($idSite)) {
            Piwik::checkUserHasViewAccess($idSite);
        } else {
            Piwik::checkUserHasSomeViewAccess();
        }

        $userLogin = Piwik::getCurrentUserLogin();

        $model = $this->getModel();
        if (Piwik::hasUserSuperUserAccess()) {
            $segments = $model->getAllSegmentsForAllUsers($idSite);
        } else {
            if (empty($idSite)) {
                $segments = $model->getAllSegments($userLogin);
            } else {
                $segments = $model->getAllSegmentsForSite($idSite, $userLogin);
            }
        }

        $segments = $this->filterSegmentsWithDisabledElements($segments, $idSite);
        $segments = $this->sortSegmentsCreatedByUserFirst($segments);

        return $segments;
    }

    /**
     * Filter out any segments which cannot be initialized due to disable plugins or features
     *
     * @param array $segments
     * @param null|string|int $idSite
     *
     * @return array
     */
    private function filterSegmentsWithDisabledElements(array $segments, $idSite = null): array
    {
        $idSites = empty($idSite) ? [] : [$idSite];

        foreach ($segments as $k => $segment) {
            if (!Segment::isAvailable($segment['definition'], $idSites)) {
                unset($segments[$k]);
            }
        }
        return $segments;
    }

    /**
     * Sorts segment in a particular order:
     *
     *  1) my segments
     *  2) segments created by the super user that were shared with all users
     *  3) segments created by other users (which are visible to all super users)
     *
     * @param array $segments
     * @return array
     */
    private function sortSegmentsCreatedByUserFirst(array $segments): array
    {
        $orderedSegments = array();
        foreach ($segments as $id => &$segment) {
            if ($segment['login'] == Piwik::getCurrentUserLogin()) {
                $orderedSegments[] = $segment;
                unset($segments[$id]);
            }
        }
        foreach ($segments as $id => &$segment) {
            if ($segment['enable_all_users'] == 1) {
                $orderedSegments[] = $segment;
                unset($segments[$id]);
            }
        }
        foreach ($segments as $id => &$segment) {
            $orderedSegments[] = $segment;
        }
        return $orderedSegments;
    }

    /**
     * @return string
     */
    private function getMessageCannotEditSegmentCreatedBySuperUser(): string
    {
        return Piwik::translate('SegmentEditor_UpdatingForeignSegmentPermittedToSuperUser');
    }
}
