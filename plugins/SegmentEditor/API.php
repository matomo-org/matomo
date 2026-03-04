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
use Piwik\Access;
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

    /**
     * @var string
     */
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
            $segment = new Segment($definition, $idSite ? [$idSite] : []);
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

    protected function checkIdSite(?int $idSite): void
    {
        if (empty($idSite)) {
            if (!Piwik::hasUserSuperUserAccess()) {
                throw new Exception($this->getMessageCannotEditSegmentCreatedBySuperUser());
            }
        } else {
            Piwik::checkUserHasViewAccess($idSite);
        }
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

        if (Piwik::hasUserSuperUserAccess()) {
            return true; // super user can always edit
        }

        if (empty($idSite)) {
            return false; // only super user can add a segment without a site
        }

        $requiredAccess = Config\GeneralConfig::getConfigValue('adding_segment_requires_access', $idSite);

        $authorized =
            ($requiredAccess == 'view' && Piwik::isUserHasViewAccess($idSite)) ||
            ($requiredAccess == 'admin' && Piwik::isUserHasAdminAccess($idSite)) ||
            ($requiredAccess == 'write' && Piwik::isUserHasWriteAccess($idSite))
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

        if ((int) $segment['enable_only_idsite'] === 0) {
            throw new Exception(Piwik::translate('SegmentEditor_UpdatingAllSitesSegmentPermittedToSuperUser'));
        }
    }

    /**
     * Deletes a stored segment.
     *
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
        Piwik::postEvent('SegmentEditor.deactivate', [$idSegment]);

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
     * @param int|null $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     */
    public function update(
        int $idSegment,
        string $name,
        string $definition,
        ?int $idSite = null,
        bool $autoArchive = false,
        bool $enabledAllUsers = false
    ): void {
        $segment = $this->getSegmentOrFail($idSegment);
        $this->checkUserCanEditOrDeleteSegment($segment);

        $this->checkIdSite($idSite);
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

        if ((int)$segment['enable_only_idsite'] !== (int)$idSite && !$this->isUserCanAddNewSegment($idSite)) {
            throw new Exception('Changing value for enable_only_idsite requires permission to add segments for the target site.');
        }

        $autoArchive     = $this->checkAutoArchive($autoArchive, $idSite);

        $bind = [
            'name'               => $name,
            'definition'         => $definition,
            'enable_all_users'   => (int) $enabledAllUsers,
            'enable_only_idsite' => (int) $idSite,
            'auto_archive'       => (int) $autoArchive,
            'ts_last_edit'       => Date::now()->getDatetime(),
        ];

        /**
         * Triggered before a segment is modified.
         *
         * This event can be used by plugins to throw an exception
         * or do something else.
         *
         * @param int $idSegment The ID of the segment which visibility is reduced.
         */
        Piwik::postEvent('SegmentEditor.update', [$idSegment, $bind]);

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
     * @param null|int $idSite If supplied, associates the stored segment with as single site.
     * @param bool $autoArchive Whether to automatically archive data with the segment or not.
     * @param bool $enabledAllUsers Whether the stored segment is viewable by all users or just the one that created it.
     *
     * @return int The newly created segment Id
     */
    public function add(
        string $name,
        string $definition,
        ?int $idSite = null,
        bool $autoArchive = false,
        bool $enabledAllUsers = false
    ): int {
        $this->checkIdSite($idSite);
        $this->checkUserCanAddNewSegment($idSite);
        $name = Common::sanitizeInputValue($name);
        $this->checkSegmentName($name);
        $definition = Common::sanitizeInputValue($definition);
        $definition = $this->checkSegmentValue($definition, $idSite);
        $enabledAllUsers = $this->checkEnabledAllUsers($enabledAllUsers);
        $autoArchive = $this->checkAutoArchive($autoArchive, $idSite);

        $bind = [
            'name'               => $name,
            'definition'         => $definition,
            'login'              => Piwik::getCurrentUserLogin(),
            'enable_all_users'   => (int) $enabledAllUsers,
            'enable_only_idsite' => (int) $idSite,
            'auto_archive'       => (int) $autoArchive,
            'ts_created'         => Date::now()->getDatetime(),
            'starred'            => 0,
            'starred_by'         => null,
            'deleted'            => 0,
        ];

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
     * Stars a stored segment.
     *
     * @return array{result: boolean, starred_by: string}
     * @throws Exception if the user is not logged in or does not have the required permissions.
     */
    public function star(int $idSegment): array
    {
        Piwik::checkUserHasSomeViewAccess();
        $segment = $this->getSegmentOrFail($idSegment);
        $this->checkUserCanEditOrDeleteSegment($segment);
        $login = Piwik::getCurrentUserLogin();
        $bind = [
            'starred' => 1,
            'starred_by' => $login,
        ];

        $result = $this->getModel()->updateSegment($idSegment, $bind);

        return [
            'result' => $result,
            'starred_by' => $login,
        ];
    }

    /**
     * Unstars a stored segment.
     *
     * @return array{result: boolean}
     * @throws Exception if the user is not logged in or does not have the required permissions.
     */
    public function unstar(int $idSegment): array
    {
        Piwik::checkUserHasSomeViewAccess();
        $segment = $this->getSegmentOrFail($idSegment);
        $this->checkUserCanEditOrDeleteSegment($segment);
        $bind = [
            'starred' => 0,
            'starred_by' => null,
        ];

        $result = $this->getModel()->updateSegment($idSegment, $bind);

        return [
            'result' => $result,
        ];
    }

    /**
     * Returns a stored segment by ID
     *
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
        $this->checkUserHasViewAccessToSegmentSite($segment);

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
     * @param null|int $idSite Whether to return stored segments for a specific idSite, or all of them. If supplied, must be a valid site ID.
     * @return array
     */
    public function getAll(?int $idSite = null): array
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

        if (empty($idSite)) {
            $segments = $this->filterSegmentsWithoutSiteAccess($segments);
        }

        $segments = $this->filterSegmentsWithDisabledElements($segments, $idSite);
        $segments = $this->sortSegmentsCreatedByUserFirst($segments);

        return $segments;
    }

    /**
     * Filter out any segments which cannot be initialized due to disable plugins or features
     *
     * @param array<array> $segments
     *
     * @return array<array>
     */
    private function filterSegmentsWithDisabledElements(array $segments, ?int $idSite = null): array
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
     * @param array<array> $segments
     * @return array<array>
     */
    private function filterSegmentsWithoutSiteAccess(array $segments): array
    {
        if (Piwik::hasUserSuperUserAccess()) {
            return $segments;
        }

        $idSitesWithViewAccess = Access::getInstance()->getSitesIdWithAtLeastViewAccess();

        foreach ($segments as $key => $segment) {
            $segmentSiteId = (int) $segment['enable_only_idsite'];
            if ($segmentSiteId !== 0 && !in_array($segmentSiteId, $idSitesWithViewAccess, true)) {
                unset($segments[$key]);
            }
        }

        return $segments;
    }

    private function checkUserHasViewAccessToSegmentSite(array $segment): void
    {
        if (Piwik::hasUserSuperUserAccess()) {
            return;
        }

        $segmentSiteId = (int) $segment['enable_only_idsite'];
        if ($segmentSiteId !== 0) {
            Piwik::checkUserHasViewAccess($segmentSiteId);
        }
    }

    /**
     * Sorts segment in a particular order:
     *
     *  1) my segments
     *  2) segments created by the super user that were shared with all users
     *  3) segments created by other users (which are visible to all super users)
     *
     * @param array<array> $segments
     * @return array<array>
     */
    private function sortSegmentsCreatedByUserFirst(array $segments): array
    {
        $orderedSegments = [];
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

    private function getMessageCannotEditSegmentCreatedBySuperUser(): string
    {
        return Piwik::translate('SegmentEditor_UpdatingForeignSegmentPermittedToSuperUser');
    }
}
