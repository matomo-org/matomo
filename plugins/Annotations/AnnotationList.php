<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Annotations;

use Exception;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Site;

/**
 * This class can be used to query & modify annotations for multiple sites
 * at once.
 *
 * Example use:
 *   $annotations = new AnnotationList($idSites = "1,2,5");
 *   $annotation = $annotations->get($idSite = 1, $idNote = 4);
 *   // do stuff w/ annotation
 *   $annotations->update($idSite = 2, $idNote = 4, $note = "This is the new text.");
 *   $annotations->save($idSite);
 *
 * Note: There is a concurrency issue w/ this code. If two users try to save
 * an annotation for the same site, it's possible one of their changes will
 * never get made (as it will be overwritten by the other's).
 *
 */
class AnnotationList
{
    const ANNOTATION_COLLECTION_OPTION_SUFFIX = '_annotations';

    /**
     * List of site IDs this instance holds annotations for.
     *
     * @var array
     */
    private $idSites;

    /**
     * Array that associates lists of annotations with site IDs.
     *
     * @var array
     */
    private $annotations;

    /**
     * Constructor. Loads annotations from the database.
     *
     * @param string|int $idSites The list of site IDs to load annotations for.
     */
    public function __construct($idSites)
    {
        $this->idSites = Site::getIdSitesFromIdSitesString($idSites);
        $this->annotations = $this->getAnnotationsForSite();
    }

    /**
     * Returns the list of site IDs this list contains annotations for.
     *
     * @return array
     */
    public function getIdSites()
    {
        return $this->idSites;
    }

    /**
     * Creates a new annotation for a site. This method does not perist the result.
     * To save the new annotation in the database, call $this->save.
     *
     * @param int $idSite The ID of the site to add an annotation to.
     * @param string $date The date the annotation is in reference to.
     * @param string $note The text of the new annotation.
     * @param int $starred Either 1 or 0. If 1, the new annotation has been starred,
     *                     otherwise it will start out unstarred.
     * @return array The added annotation.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     */
    public function add($idSite, $date, $note, $starred = 0)
    {
        $this->checkIdSiteIsLoaded($idSite);
        $date = Date::factory($date)->toString('Y-m-d');

        $this->annotations[$idSite][] = self::makeAnnotation($date, $note, $starred);

        // get the id of the new annotation
        end($this->annotations[$idSite]);
        $newNoteId = key($this->annotations[$idSite]);

        return $this->get($idSite, $newNoteId);
    }

    /**
     * Persists the annotations list for a site, overwriting whatever exists.
     *
     * @param int $idSite The ID of the site to save annotations for.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     */
    public function save($idSite)
    {
        $this->checkIdSiteIsLoaded($idSite);

        $optionName = self::getAnnotationCollectionOptionName($idSite);
        Option::set($optionName, serialize($this->annotations[$idSite]));
    }

    /**
     * Modifies an annotation in this instance's collection of annotations.
     *
     * Note: This method does not perist the change in the DB. The save method must
     * be called for that.
     *
     * @param int $idSite The ID of the site whose annotation will be updated.
     * @param int $idNote The ID of the note.
     * @param string|null $date The new date of the annotation, eg '2012-01-01'. If
     *                          null, no change is made.
     * @param string|null $note The new text of the annotation. If null, no change
     *                          is made.
     * @param int|null $starred Either 1 or 0, whether the annotation should be
     *                          starred or not. If null, no change is made.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     * @throws Exception if $idNote does not refer to valid note for the site.
     */
    public function update($idSite, $idNote, $date = null, $note = null, $starred = null)
    {
        $this->checkIdSiteIsLoaded($idSite);
        $this->checkNoteExists($idSite, $idNote);

        $annotation =& $this->annotations[$idSite][$idNote];
        if ($date !== null) {
            $annotation['date'] = Date::factory($date)->toString('Y-m-d');
        }
        if ($note !== null) {
            $annotation['note'] = $note;
        }
        if ($starred !== null) {
            $annotation['starred'] = $starred;
        }
    }

    /**
     * Removes a note from a site's collection of annotations.
     *
     * Note: This method does not perist the change in the DB. The save method must
     * be called for that.
     *
     * @param int $idSite The ID of the site whose annotation will be updated.
     * @param int $idNote The ID of the note.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     * @throws Exception if $idNote does not refer to valid note for the site.
     */
    public function remove($idSite, $idNote)
    {
        $this->checkIdSiteIsLoaded($idSite);
        $this->checkNoteExists($idSite, $idNote);

        unset($this->annotations[$idSite][$idNote]);
    }

    /**
     * Removes all notes for a single site.
     *
     * Note: This method does not perist the change in the DB. The save method must
     * be called for that.
     *
     * @param int $idSite The ID of the site to get an annotation for.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     */
    public function removeAll($idSite)
    {
        $this->checkIdSiteIsLoaded($idSite);

        $this->annotations[$idSite] = array();
    }

    /**
     * Retrieves an annotation by ID.
     *
     * This function returns an array with the following elements:
     *  - idNote: The ID of the annotation.
     *  - date: The date of the annotation.
     *  - note: The text of the annotation.
     *  - starred: 1 or 0, whether the annotation is stared;
     *  - user: (unless current user is anonymous) The user that created the annotation.
     *  - canEditOrDelete: True if the user can edit/delete the annotation.
     *
     * @param int $idSite The ID of the site to get an annotation for.
     * @param int $idNote The ID of the note to get.
     * @throws Exception if $idSite is not an ID that was supplied upon construction.
     * @throws Exception if $idNote does not refer to valid note for the site.
     */
    public function get($idSite, $idNote)
    {
        $this->checkIdSiteIsLoaded($idSite);
        $this->checkNoteExists($idSite, $idNote);

        $annotation = $this->annotations[$idSite][$idNote];
        $this->augmentAnnotationData($idSite, $idNote, $annotation);
        return $annotation;
    }

    /**
     * Returns all annotations within a specific date range. The result is
     * an array that maps site IDs with arrays of annotations within the range.
     *
     * Note: The date range is inclusive.
     *
     * @see self::get for info on what attributes stored within annotations.
     *
     * @param Date|bool $startDate The start of the date range.
     * @param Date|bool $endDate The end of the date range.
     * @param array|bool|int|string $idSite IDs of the sites whose annotations to
     *                                       search through.
     * @return array Array mapping site IDs with arrays of annotations, eg:
     *               array(
     *                 '5' => array(
     *                          array(...), // annotation
     *                          array(...), // annotation
     *                          ...
     *                        ),
     *                 '6' => array(
     *                          array(...), // annotation
     *                          array(...), // annotation
     *                          ...
     *                        ),
     *               )
     */
    public function search($startDate, $endDate, $idSite = false)
    {
        if ($idSite) {
            $idSites = Site::getIdSitesFromIdSitesString($idSite);
        } else {
            $idSites = array_keys($this->annotations);
        }

        // collect annotations that are within the right date range & belong to the right
        // site
        $result = array();
        foreach ($idSites as $idSite) {
            if (!isset($this->annotations[$idSite])) {
                continue;
            }

            foreach ($this->annotations[$idSite] as $idNote => $annotation) {
                if ($startDate !== false) {
                    $annotationDate = Date::factory($annotation['date']);
                    if ($annotationDate->getTimestamp() < $startDate->getTimestamp()
                        || $annotationDate->getTimestamp() > $endDate->getTimestamp()
                    ) {
                        continue;
                    }
                }

                $this->augmentAnnotationData($idSite, $idNote, $annotation);
                $result[$idSite][] = $annotation;
            }

            // sort by annotation date
            if (!empty($result[$idSite])) {
                uasort($result[$idSite], array($this, 'compareAnnotationDate'));
            }
        }
        return $result;
    }

    /**
     * Counts annotations & starred annotations within a date range and returns
     * the counts. The date range includes the start date, but not the end date.
     *
     * @param int $idSite The ID of the site to count annotations for.
     * @param string|false $startDate The start date of the range or false if no
     *                                range check is desired.
     * @param string|false $endDate The end date of the range or false if no
     *                              range check is desired.
     * @return array eg, array('count' => 5, 'starred' => 2)
     */
    public function count($idSite, $startDate, $endDate)
    {
        $this->checkIdSiteIsLoaded($idSite);

        // search includes end date, and count should not, so subtract one from the timestamp
        $annotations = $this->search($startDate, Date::factory($endDate->getTimestamp() - 1));

        // count the annotations
        $count = $starred = 0;
        if (!empty($annotations[$idSite])) {
            $count = count($annotations[$idSite]);
            foreach ($annotations[$idSite] as $annotation) {
                if ($annotation['starred']) {
                    ++$starred;
                }
            }
        }

        return array('count' => $count, 'starred' => $starred);
    }

    /**
     * Utility function. Creates a new annotation.
     *
     * @param string $date
     * @param string $note
     * @param int $starred
     * @return array
     */
    private function makeAnnotation($date, $note, $starred = 0)
    {
        return array('date'    => $date,
                     'note'    => $note,
                     'starred' => (int)$starred,
                     'user'    => Piwik::getCurrentUserLogin());
    }

    /**
     * Retrieves annotations from the database for the sites supplied to the
     * constructor.
     *
     * @return array Lists of annotations mapped by site ID.
     */
    private function getAnnotationsForSite()
    {
        $result = array();
        foreach ($this->idSites as $id) {
            $optionName = self::getAnnotationCollectionOptionName($id);
            $serialized = Option::get($optionName);

            if ($serialized !== false) {
                $result[$id] = @unserialize($serialized);
                if (empty($result[$id])) {
                    // in case unserialize failed
                    $result[$id] = array();
                }
            } else {
                $result[$id] = array();
            }
        }
        return $result;
    }

    /**
     * Utility function that checks if a site ID was supplied and if not,
     * throws an exception.
     *
     * We can only modify/read annotations for sites that we've actually
     * loaded the annotations for.
     *
     * @param int $idSite
     * @throws Exception
     */
    private function checkIdSiteIsLoaded($idSite)
    {
        if (!in_array($idSite, $this->idSites)) {
            throw new Exception("This AnnotationList was not initialized with idSite '$idSite'.");
        }
    }

    /**
     * Utility function that checks if a note exists for a site, and if not,
     * throws an exception.
     *
     * @param int $idSite
     * @param int $idNote
     * @throws Exception
     */
    private function checkNoteExists($idSite, $idNote)
    {
        if (empty($this->annotations[$idSite][$idNote])) {
            throw new Exception("There is no note with id '$idNote' for site with id '$idSite'.");
        }
    }

    /**
     * Returns true if the current user can modify or delete a specific annotation.
     *
     * A user can modify/delete a note if the user has admin access for the site OR
     * the user has view access, is not the anonymous user and is the user that
     * created the note in question.
     *
     * @param int $idSite The site ID the annotation belongs to.
     * @param array $annotation The annotation.
     * @return bool
     */
    public static function canUserModifyOrDelete($idSite, $annotation)
    {
        // user can save if user is admin or if has view access, is not anonymous & is user who wrote note
        $canEdit = Piwik::isUserHasAdminAccess($idSite)
            || (!Piwik::isUserIsAnonymous()
                && Piwik::getCurrentUserLogin() == $annotation['user']);
        return $canEdit;
    }

    /**
     * Adds extra data to an annotation, including the annotation's ID and whether
     * the current user can edit or delete it.
     *
     * Also, if the current user is anonymous, the user attribute is removed.
     *
     * @param int $idSite
     * @param int $idNote
     * @param array $annotation
     */
    private function augmentAnnotationData($idSite, $idNote, &$annotation)
    {
        $annotation['idNote'] = $idNote;
        $annotation['canEditOrDelete'] = self::canUserModifyOrDelete($idSite, $annotation);

        // we don't supply user info if the current user is anonymous
        if (Piwik::isUserIsAnonymous()) {
            unset($annotation['user']);
        }
    }

    /**
     * Utility function that compares two annotations.
     *
     * @param array $lhs An annotation.
     * @param array $rhs An annotation.
     * @return int -1, 0 or 1
     */
    public function compareAnnotationDate($lhs, $rhs)
    {
        if ($lhs['date'] == $rhs['date']) {
            return $lhs['idNote'] <= $rhs['idNote'] ? -1 : 1;
        }

        return $lhs['date'] < $rhs['date'] ? -1 : 1; // string comparison works because date format should be YYYY-MM-DD
    }

    /**
     * Returns true if the current user can add notes for a specific site.
     *
     * @param int $idSite The site to add notes to.
     * @return bool
     */
    public static function canUserAddNotesFor($idSite)
    {
        return Piwik::isUserHasViewAccess($idSite)
        && !Piwik::isUserIsAnonymous();
    }

    /**
     * Returns the option name used to store annotations for a site.
     *
     * @param int $idSite The site ID.
     * @return string
     */
    public static function getAnnotationCollectionOptionName($idSite)
    {
        return $idSite . self::ANNOTATION_COLLECTION_OPTION_SUFFIX;
    }
}
