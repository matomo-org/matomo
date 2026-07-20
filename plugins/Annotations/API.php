<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Annotations;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Site;

/**
 * Provides API methods to create, update, delete, and query annotations.
 *
 * @phpstan-type Annotation array{id:int, idNote:int, idsite:int, date:string, note:string, starred:int, user:string, canEditOrDelete:bool}
 *
 * @method static \Piwik\Plugins\Annotations\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    // do not automatically apply `Common::sanitizeInputValue` to all API parameters
    protected $autoSanitizeInputParams = false;

    /**
     * Creates a new annotation for a site.
     *
     * @param int $idSite The site ID to add the annotation to.
     * @param string $date The date the annotation is attached to.
     * @param string $note The text of the annotation (max 255 chars).
     * @param bool $starred Whether the annotation should be starred.
     * @return Annotation
     */
    public function add(int $idSite, string $date, string $note, bool $starred = false): array
    {
        $this->checkUserCanAddNotesFor($idSite);
        $this->checkSiteExists($idSite);
        $this->checkDateIsValid($date);
        $date = Date::factory($date)->toString(); // ensure we handle today/yesterday correctly

        $annotation = [
            'idsite' => $idSite,
            'date' => $date,
            'note' => $this->filterNote($note),
            'starred' => (int) $starred,
            'user' => Piwik::getCurrentUserLogin(),
        ];

        $model = new Model();
        $idNote = $model->createAnnotation($annotation);
        $annotation['id'] = $idNote;
        $this->decorateAnnotation($annotation);

        return $annotation;
    }

    /**
     * Updates an annotation for a site and returns the updated annotation.
     *
     * If the current user is not allowed to modify an annotation, an exception
     * will be thrown. A user can modify a note if:
     *  - the user has admin access for the site, OR
     *  - the user has view access, is not the anonymous user and is the user that
     *    created the note
     *
     * @param int $idSite The site ID to add the annotation to.
     * @param int $idNote The ID of the note.
     * @param string|null $date The date the annotation is attached to. If null, the annotation's
     *                          date is not modified.
     * @param string|null $note The text of the annotation (max 255 chars).
     *                          If null, the annotation's text is not modified.
     * @param bool|null $starred Whether the annotation should be starred.
     *                           If null, the annotation is not starred/un-starred, so the current state won't change.
     * @return Annotation
     */
    public function save(int $idSite, int $idNote, ?string $date = null, ?string $note = null, ?bool $starred = null): array
    {
        $this->checkSiteExists($idSite);
        $this->checkDateIsValid($date, $canBeNull = true);
        if (null !== $date) {
            $date = Date::factory($date)->toString(); // ensure we handle today/yesterday correctly
        }

        $originalAnnotation = $this->get($idSite, $idNote);

        // check if current user has the right to update the annotation
        $this->checkUserCanModifyOrDelete($originalAnnotation);

        if (isset($starred)) {
            $starred = intval($starred);
        }

        $updatedValues = array_diff_assoc(
            array_filter(
                [
                    'date' => $date,
                    'note' => $this->filterNote($note),
                    'starred' => $starred,
                ],
                function ($value) {
                    return isset($value);
                }
            ),
            $originalAnnotation
        );

        if (!empty($updatedValues)) {
            $model = new Model();
            $originalAnnotation = $model->updateAnnotation($idNote, $idSite, $updatedValues);
        }
        $this->decorateAnnotation($originalAnnotation);

        return $originalAnnotation;
    }

    /**
     * Removes an annotation from a site's list of annotations.
     *
     * If the current user is not allowed to delete the annotation, an exception
     * will be thrown. A user can delete a note if:
     *  - the user has admin access for the site, OR
     *  - the user has view access, is not the anonymous user and is the user that
     *    created the note
     *
     * @param int $idSite The site ID to add the annotation to.
     * @param int $idNote The ID of the note to delete.
     */
    public function delete(int $idSite, int $idNote): void
    {
        $this->checkSiteExists($idSite);

        $annotation = $this->get($idSite, $idNote);
        // check permissions
        $this->checkUserCanModifyOrDelete($annotation);

        $model = new Model();
        $model->deleteAnnotation($idNote, $idSite);
    }

    /**
     * Removes all annotations for a single site. Only superusers can use this method.
     *
     * @param int $idSite The ID of the site to remove annotations for.
     */
    public function deleteAll(int $idSite): void
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkSiteExists($idSite);

        $model = new Model();
        $model->deleteAllAnnotationsForSite($idSite);
    }

    /**
     * Returns a single annotation for one site.
     *
     * @param int $idSite The site ID the annotation is linked to.
     * @param int $idNote The ID of the annotation to get.
     * @return Annotation
     */
    public function get(int $idSite, int $idNote): array
    {
        Piwik::checkUserHasViewAccess($idSite);
        $this->checkSiteExists($idSite);

        $model = new Model();
        $annotation = $model->getAnnotation($idNote, $idSite);
        if (empty($annotation)) {
            throw new Exception("There is no note with id '$idNote' for site with id '$idSite'.");
        }
        $this->decorateAnnotation($annotation);

        return $annotation;
    }

    /**
     * Returns every annotation for a specific site within a date range.
     * The date range is derived from a date, period, and optional number of past periods.
     *
     * @param string $idSite One site ID or a comma-separated list of site IDs.
     * @param string|null $date The date of the period.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period type.
     * @param int|null $lastN Whether to include the last N periods in the date range.
     * @return array<int, array<int, Annotation>>
     */
    public function getAll(string $idSite, ?string $date = null, string $period = 'day', ?int $lastN = null): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $ids = array_map('intval', Site::getIdSitesFromIdSitesString($idSite, false, true));
        $model = new Model();
        $annotations = [];
        foreach ($ids as $id) {
            [$startDate, $endDate] = Annotations::getDateRangeForPeriod($date ?: false, $period, $lastN ?? false, $id);

            $annotations[$id] = $model->getAllAnnotationsForSiteInRange(
                $id,
                $startDate ? $startDate->toString() : null,
                $endDate ? $endDate->toString() : null
            );
            for ($i = 0; $i < count($annotations[$id]); $i++) {
                $this->decorateAnnotation($annotations[$id][$i]);
            }
        }

        return $annotations;
    }

    /**
     * Returns the count of annotations for a list of periods, including the count of
     * starred annotations.
     *
     * @param string $idSite The site ID(s) to get the annotation count for.
     * @param string $date The date of the period.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period type.
     * @param int|null $lastN Whether to get counts for the last N number of periods or not.
     * @param bool $getAnnotationText Whether to include the note text when exactly one annotation exists for a date.
     * @return array<int, array<int, array{0:string, 1:array{count:int, starred:int, note?:string}}>>
     */
    public function getAnnotationCountForDates(
        string $idSite,
        string $date,
        string $period,
        ?int $lastN = null,
        bool $getAnnotationText = false
    ): array {
        Piwik::checkUserHasViewAccess($idSite);

        $siteIds = array_map('intval', Site::getIdSitesFromIdSitesString($idSite, false, true));
        if (empty($siteIds)) {
            return [];
        }

        // get start & end date for request. lastN is ignored if $period == 'range'
        [$startDate, $endDate] = Annotations::getDateRangeForPeriod($date, $period, $lastN ?? false, $siteIds[0]);

        if (!($startDate && $endDate)) {
            return [];
        }

        if ($period == 'range') {
            $period = 'day';
        }

        // create list of dates
        $dates = [];
        for (; $startDate->getTimestamp() <= $endDate->getTimestamp(); $startDate = $startDate->addPeriod(1, $period)) {
            $dates[] = $startDate;
        }
        // we add one for the end of the last period (used in for loop below to bound annotation dates)
        $dates[] = $startDate;

        $model = new Model();
        $result = [];
        foreach ($siteIds as $siteId) {
            $result[$siteId] = [];
            for ($i = 0; $i < count($dates) - 1; $i++) {
                $date = $dates[$i];
                $nextDate = $dates[$i + 1];
                $strDate = $date->toString();
                $strNextDate = $nextDate->toString();

                [$totalCount, $starredCount] = $model->getCountAnnotationsForSiteInRange($siteId, $strDate, $strNextDate);

                $result[$siteId][] = [
                    $strDate,
                    [
                        'count' => $totalCount,
                        'starred' => $starredCount,
                    ],
                ];

                if ($getAnnotationText && $totalCount === 1) {
                    [$annotation] = $model->getAllAnnotationsForSiteInRange($siteId, $strDate, $strNextDate, 1);
                    // 1 for the second array to add the note to
                    $result[$siteId][$i][1]['note'] = (string)$annotation['note'];
                }
            }
        }

        return $result;
    }

    /**
     * @param array{id:int, idsite:int, date:string, note:string, starred:int, user:string} $annotation
     */
    private function checkUserCanModifyOrDelete(array $annotation): void
    {
        if (!Annotations::canUserModifyOrDelete($annotation)) {
            throw new Exception("The current user is not allowed to modify or delete notes for site #{$annotation['idsite']}");
        }
    }

    private static function checkUserCanAddNotesFor(int $idSite): void
    {
        if (!Piwik::isUserHasViewAccess($idSite) || Piwik::isUserIsAnonymous()) {
            throw new Exception("The current user is not allowed to add notes for site #$idSite.");
        }
    }

    private function checkSiteExists(int $idSite): void
    {
        new Site($idSite);
    }

    private function checkDateIsValid(?string $date, bool $canBeNull = false): void
    {
        if (
            $date === null
            && $canBeNull
        ) {
            return;
        }

        Date::factory($date);
    }

    private function filterNote(?string $note): ?string
    {
        if (empty($note)) {
            return $note;
        }

        // shorten note if longer than 255 characters
        if (mb_strlen($note) > 255) {
            $note = mb_substr($note, 0, 254) . '…';
        }

        return $note;
    }

    /**
     * @param array{id:int, idsite:int, date:string, note:string, starred:int, user:string} $annotation
     */
    private function decorateAnnotation(array &$annotation): void
    {
        $annotation['date'] = substr($annotation['date'], 0, 10);
        $annotation['note'] = Common::sanitizeInputValue($annotation['note']);
        $annotation['canEditOrDelete'] = Annotations::canUserModifyOrDelete($annotation);
        $annotation['idNote'] = $annotation['id']; // for API backward compatibility
    }
}
