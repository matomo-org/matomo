<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Annotations
 */

/**
 * Controller for the Annotations plugin.
 *
 * @package Piwik_Annotations
 */
class Piwik_Annotations_Controller extends Piwik_Controller
{
    /**
     * Controller action that returns HTML displaying annotations for a site and
     * specific date range.
     *
     * Query Param Input:
     *  - idSite: The ID of the site to get annotations for. Only one allowed.
     *  - date: The date to get annotations for. If lastN is not supplied, this is the start date,
     *          otherwise the start date in the last period.
     *  - period: The period type.
     *  - lastN: If supplied, the last N # of periods will be included w/ the range specified
     *           by date + period.
     *
     * Output:
     *  - HTML displaying annotations for a specific range.
     *
     * @param bool $fetch True if the annotation manager should be returned as a string,
     *                    false if it should be echo-ed.
     * @param string $date Override for 'date' query parameter.
     * @param string $period Override for 'period' query parameter.
     * @param string $lastN Override for 'lastN' query parameter.
     * @return string|void
     */
    public function getAnnotationManager($fetch = false, $date = false, $period = false, $lastN = false)
    {
        $idSite = Piwik_Common::getRequestVar('idSite');

        if ($date === false) {
            $date = Piwik_Common::getRequestVar('date', false);
        }

        if ($period === false) {
            $period = Piwik_Common::getRequestVar('period', 'day');
        }

        if ($lastN === false) {
            $lastN = Piwik_Common::getRequestVar('lastN', false);
        }

        // create & render the view
        $view = Piwik_View::factory('annotationManager');

        $allAnnotations = Piwik_API_Request::processRequest(
            'Annotations.getAll', array('date' => $date, 'period' => $period, 'lastN' => $lastN));
        $view->annotations = empty($allAnnotations[$idSite]) ? array() : $allAnnotations[$idSite];

        $view->period = $period;
        $view->lastN = $lastN;

        list($startDate, $endDate) = Piwik_Annotations_API::getDateRangeForPeriod($date, $period, $lastN);
        $view->startDate = $startDate->toString();
        $view->endDate = $endDate->toString();

        $dateFormat = Piwik_Translate('CoreHome_ShortDateFormatWithYear');
        $view->startDatePretty = $startDate->getLocalized($dateFormat);
        $view->endDatePretty = $endDate->getLocalized($dateFormat);

        $view->canUserAddNotes = Piwik_Annotations_AnnotationList::canUserAddNotesFor($idSite);

        if ($fetch) {
            return $view->render();
        } else {
            echo $view->render();
        }
    }

    /**
     * Controller action that modifies an annotation and returns HTML displaying
     * the modified annotation.
     *
     * Query Param Input:
     *  - idSite: The ID of the site the annotation belongs to. Only one ID is allowed.
     *  - idNote: The ID of the annotation.
     *  - date: The new date value for the annotation. (optional)
     *  - note: The new text for the annotation. (optional)
     *  - starred: Either 1 or 0. Whether the note should be starred or not. (optional)
     *
     * Output:
     *  - HTML displaying modified annotation.
     *
     * If an optional query param is not supplied, that part of the annotation is
     * not modified.
     */
    public function saveAnnotation()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();

            $view = Piwik_View::factory('annotation');

            // NOTE: permissions checked in API method
            // save the annotation
            $view->annotation = Piwik_API_Request::processRequest("Annotations.save");

            echo $view->render();
        }
    }

    /**
     * Controller action that adds a new annotation for a site and returns new
     * annotation manager HTML for the site and date range.
     *
     * Query Param Input:
     *  - idSite: The ID of the site to add an annotation to.
     *  - date: The date for the new annotation.
     *  - note: The text of the annotation.
     *  - starred: Either 1 or 0, whether the annotation should be starred or not.
     *             Defaults to 0.
     *  - managerDate: The date for the annotation manager. If a range is given, the start
     *          date is used for the new annotation.
     *  - managerPeriod: For rendering the annotation manager. @see self::getAnnotationManager
     *            for more info.
     *  - lastN: For rendering the annotation manager. @see         self::getAnnotationManager
     *           for more info.
     * Output:
     *  - @see                                                      self::getAnnotationManager
     */
    public function addAnnotation()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();

            // the date used is for the annotation manager HTML that gets echo'd. we
            // use this date for the new annotation, unless it is a date range, in
            // which case we use the first date of the range.
            $date = Piwik_Common::getRequestVar('date');
            if (strpos($date, ',') !== false) {
                $date = reset(explode(',', $date));
            }

            // add the annotation. NOTE: permissions checked in API method
            Piwik_API_Request::processRequest("Annotations.add", array('date' => $date));

            $managerDate = Piwik_Common::getRequestVar('managerDate', false);
            $managerPeriod = Piwik_Common::getRequestVar('managerPeriod', false);
            echo $this->getAnnotationManager($fetch = true, $managerDate, $managerPeriod);
        }
    }

    /**
     * Controller action that deletes an annotation and returns new annotation
     * manager HTML for the site & date range.
     *
     * Query Param Input:
     *  - idSite: The ID of the site this annotation belongs to.
     *  - idNote: The ID of the annotation to delete.
     *  - date: For rendering the annotation manager. @see   self::getAnnotationManager
     *          for more info.
     *  - period: For rendering the annotation manager. @see self::getAnnotationManager
     *            for more info.
     *  - lastN: For rendering the annotation manager. @see  self::getAnnotationManager
     *           for more info.
     *
     * Output:
     *  - @see                                               self::getAnnotationManager
     */
    public function deleteAnnotation()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();

            // delete annotation. NOTE: permissions checked in API method
            Piwik_API_Request::processRequest("Annotations.delete");

            echo $this->getAnnotationManager($fetch = true);
        }
    }

    /**
     * Controller action that echo's HTML that displays marker icons for an
     * evolution graph's x-axis. The marker icons still need to be positioned
     * by the JavaScript.
     *
     * Query Param Input:
     *  - idSite: The ID of the site this annotation belongs to. Only one is allowed.
     *  - date: The date to check for annotations. If lastN is not supplied, this is
     *          the start of the date range used to check for annotations. If supplied,
     *          this is the start of the last period in the date range.
     *  - period: The period type.
     *  - lastN: If supplied, the last N # of periods are included in the date range
     *           used to check for annotations.
     *
     * Output:
     *  - HTML that displays marker icons for an evolution graph based on the
     *    number of annotations & starred annotations in the graph's date range.
     */
    public function getEvolutionIcons()
    {
        // get annotation the count
        $annotationCounts = Piwik_API_Request::processRequest(
            "Annotations.getAnnotationCountForDates", array('getAnnotationText' => 1));

        // create & render the view
        $view = Piwik_View::factory('evolutionAnnotations');
        $view->annotationCounts = reset($annotationCounts); // only one idSite allowed for this action

        echo $view->render();
    }
}
