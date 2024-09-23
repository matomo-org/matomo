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
 * @see plugins/Annotations/AnnotationList.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Annotations/AnnotationList.php';

/**
 * API for annotations plugin. Provides methods to create, modify, delete & query
 * annotations.
 *
 * @method static \Piwik\Plugins\Annotations\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    // do not automatically apply `Common::sanitizeInputValue` to all API parameters
    protected $autoSanitizeInputParams = false;

    /**
     * Create a new annotation for a site.
     *
     * @param int $idSite The site ID to add the annotation to.
     * @param string $date The date the annotation is attached to.
     * @param string $note The text of the annotation (max 255 chars).
     * @param boolean $starred Whether the annotation should be starred.
     * @return array Returns an array of two elements. The first element (indexed by
     *               'annotation') is the new annotation. The second element (indexed
     *               by 'idNote' is the new note's ID).
     */
    public function add(int $idSite, string $date, string $note, bool $starred = false): array
    {
        $this->checkUserCanAddNotesFor($idSite);
        $this->checkSiteExists($idSite);
        $this->checkDateIsValid($date);

        $note = $this->filterNote($note);

        // add, save & return a new annotation
        $annotations = new AnnotationList($idSite);

        $newAnnotation = $annotations->add($idSite, $date, $note, $starred);
        $annotations->save($idSite);

        return $newAnnotation;
    }

    /**
     * Modifies an annotation for a site and returns the modified annotation
     * and its ID.
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
     * @return array Returns an array of two elements. The first element (indexed by
     *               'annotation') is the new annotation. The second element (indexed
     *               by 'idNote' is the new note's ID).
     */
    public function save(int $idSite, int $idNote, ?string $date = null, ?string $note = null, ?bool $starred = null): array
    {
        $this->checkSiteExists($idSite);
        $this->checkDateIsValid($date, $canBeNull = true);

        // get the annotations for the site
        $annotations = new AnnotationList($idSite);

        // check permissions
        $this->checkUserCanModifyOrDelete($annotations->get($idSite, $idNote));

        $note = $this->filterNote($note);

        // modify the annotation, and save the whole list
        $annotations->update($idSite, $idNote, $date, $note, $starred);
        $annotations->save($idSite);

        return $annotations->get($idSite, $idNote);
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

        $annotations = new AnnotationList($idSite);

        // check permissions
        $this->checkUserCanModifyOrDelete($annotations->get($idSite, $idNote));

        // remove the note & save the list
        $annotations->remove($idSite, $idNote);
        $annotations->save($idSite);
    }

    /**
     * Removes all annotations for a single site. Only super users can use this method.
     *
     * @param int $idSite The ID of the site to remove annotations for.
     */
    public function deleteAll(int $idSite): void
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkSiteExists($idSite);

        $annotations = new AnnotationList($idSite);

        // remove the notes & save the list
        $annotations->removeAll($idSite);
        $annotations->save($idSite);
    }

    /**
     * Returns a single note for one site.
     *
     * @param int $idSite The site ID to add the annotation to.
     * @param int $idNote The ID of the note to get.
     * @return array The annotation. It will contain the following properties:
     *               - date: The date the annotation was recorded for.
     *               - note: The note text.
     *               - starred: Whether the note is starred or not.
     *               - user: The user that created the note.
     *               - canEditOrDelete: Whether the user that called this method can edit or
     *                                  delete the annotation returned.
     */
    public function get(int $idSite, int $idNote): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $this->checkSiteExists($idSite);

        // get single annotation
        $annotations = new AnnotationList($idSite);
        return $annotations->get($idSite, $idNote);
    }

    /**
     * Returns every annotation for a specific site within a specific date range.
     * The date range is specified by a date, the period type (day/week/month/year)
     * and an optional number of N periods in the past to include.
     *
     * @param string $idSite The site ID to get annotations for. Can be one ID or
     *                       a list of site IDs.
     * @param null|string $date The date of the period.
     * @param string $period The period type.
     * @param null|int $lastN Whether to include the last N number of periods in the
     *                         date range or not.
     * @return array An array that indexes arrays of annotations by site ID. ie,
     *               array(
     *                 5 => array(
     *                   array(...), // annotation #1
     *                   array(...), // annotation #2
     *                 ),
     *                 8 => array(...)
     *               )
     */
    public function getAll(string $idSite, ?string $date = null, string $period = 'day', ?int $lastN = null): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $annotations = new AnnotationList($idSite);

        // if date/period are supplied, determine start/end date for search
        list($startDate, $endDate) = Annotations::getDateRangeForPeriod($date ?? false, $period, $lastN ?? false);

        return $annotations->search($startDate, $endDate);
    }

    /**
     * Returns the count of annotations for a list of periods, including the count of
     * starred annotations.
     *
     * @param string $idSite The site ID(s) to get the annotation count for.
     * @param string $date The date of the period.
     * @param string $period The period type.
     * @param int|bool $lastN Whether to get counts for the last N number of periods or not.
     * @param bool $getAnnotationText
     * @return array An array mapping site IDs to arrays holding dates & the count of
     *               annotations made for those dates. eg,
     *               array(
     *                 5 => array(
     *                   array('2012-01-02', array('count' => 4, 'starred' => 2)),
     *                   array('2012-01-03', array('count' => 0, 'starred' => 0)),
     *                   array('2012-01-04', array('count' => 2, 'starred' => 0)),
     *                 ),
     *                 6 => array(
     *                   array('2012-01-02', array('count' => 1, 'starred' => 0)),
     *                   array('2012-01-03', array('count' => 4, 'starred' => 3)),
     *                   array('2012-01-04', array('count' => 2, 'starred' => 0)),
     *                 ),
     *                 ...
     *               )
     */
    public function getAnnotationCountForDates(
        string $idSite,
        string $date,
        string $period,
        ?int $lastN = null,
        bool $getAnnotationText = false
    ): array {
        Piwik::checkUserHasViewAccess($idSite);

        // get start & end date for request. lastN is ignored if $period == 'range'
        list($startDate, $endDate) = Annotations::getDateRangeForPeriod($date, $period, $lastN ?? false);
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

        // get annotations for the site
        $annotations = new AnnotationList($idSite);

        // create result w/ 0-counts
        $result = [];
        for ($i = 0; $i != count($dates) - 1; ++$i) {
            $date = $dates[$i];
            $nextDate = $dates[$i + 1];
            $strDate = $date->toString();

            foreach ($annotations->getIdSites() as $idSite) {
                $result[$idSite][$strDate] = $annotations->count($idSite, $date, $nextDate);

                // if only one annotation, return the one annotation's text w/ the counts
                if (
                    $getAnnotationText
                    && $result[$idSite][$strDate]['count'] == 1
                ) {
                    $annotationsForSite = $annotations->search(
                        $date,
                        Date::factory($nextDate->getTimestamp() - 1),
                        $idSite
                    );
                    $annotation = reset($annotationsForSite[$idSite]);

                    $result[$idSite][$strDate]['note'] = $annotation['note'];
                }
            }
        }

        // convert associative array into array of pairs (so it can be traversed by index)
        $pairResult = [];
        foreach ($result as $idSite => $counts) {
            foreach ($counts as $date => $count) {
                $pairResult[$idSite][] = [$date, $count];
            }
        }
        return $pairResult;
    }

    /**
     * Throws if the current user is not allowed to modify or delete an annotation.
     *
     * @param array $annotation The annotation.
     * @throws Exception if the current user is not allowed to modify/delete $annotation.
     */
    private function checkUserCanModifyOrDelete($annotation): void
    {
        if (!$annotation['canEditOrDelete']) {
            throw new Exception(Piwik::translate('Annotations_YouCannotModifyThisNote'));
        }
    }

    /**
     * Throws if the current user is not allowed to create annotations for a site.
     *
     * @param int $idSite The site ID.
     * @throws Exception if the current user is anonymous or does not have view access
     *                   for site w/ id=$idSite.
     */
    private static function checkUserCanAddNotesFor($idSite): void
    {
        if (!Piwik::isUserHasViewAccess($idSite) || Piwik::isUserIsAnonymous()) {
            throw new Exception("The current user is not allowed to add notes for site #$idSite.");
        }
    }

    /**
     * Throws an exception if the given $idSite does not exist.
     *
     * @param int $idSite
     * @return void
     * @throws \Piwik\Exception\UnexpectedWebsiteFoundException
     */
    private function checkSiteExists(int $idSite): void
    {
        new Site($idSite);
    }

    /**
     * Utility function, makes sure date string is valid date, and throws if
     * otherwise.
     */
    private function checkDateIsValid($date, $canBeNull = false): void
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

        // @todo store message unsanitized, sanitize on output instead.
        // can be changed when migrating annotations to a separate table.
        return Common::sanitizeInputValue($note);
    }
}
