<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Tracker\Visit\VisitProperties;

/**
 * Base class for all tracker RequestProcessors. RequestProcessors handle and respond to tracking
 * requests.
 *
 * ## Concept: Request Metadata
 *
 * RequestProcessors take a Tracker\Request object and based on its information, set request metadata.
 *
 * Request metadata is information about the current tracking request, for example, whether
 * the request is for an existing visit or new visit, or whether the current visitor is a known
 * visitor, etc. It is used to control tracking behavior.
 *
 * Request metadata is shared between RequestProcessors, so RequestProcessors can tweak each others
 * behavior, and thus, the behavior of the Tracker. Request metadata can be set and get using the
 * {@link Request::setMetadata()} and {@link Request::getMetadata()}
 * methods.
 *
 * Each RequestProcessor lists the request metadata it computes and exposes in its class
 * documentation.
 *
 * ## The Tracking Process
 *
 * When Piwik handles a single tracking request, it gathers all available RequestProcessors and
 * invokes their methods in sequence.
 *
 * The first method called is {@link self::manipulateRequest()}. By default this is a no-op, but
 * RequestProcessors can use it to manipulate tracker requests before they are processed.
 *
 * The second method called is {@link self::processRequestParams()}. RequestProcessors should use
 * this method to compute request metadata and set visit properties using the tracking request.
 * An example includes the ActionRequestProcessor, which uses this method to determine the action
 * being tracked.
 *
 * The third method called is {@link self::afterRequestProcessed()}. RequestProcessors should
 * use this method to either compute request metadata/visit properties using other plugins'
 * request metadata, OR override other plugins' request metadata to tweak tracker behavior.
 * An example of the former can be seen in the GoalsRequestProcessor which uses the action
 * detected by the ActionsRequestProcessor to see if there are any action-matching goal
 * conversions. An example of the latter can be seen in the PingRequestProcessor, which on
 * ping requests, aborts conversion recording and new visit recording.
 *
 * After these methods are called, either {@link self::onNewVisit()} or {@link self::onExistingVisit()}
 * is called. Generally, plugins should favor defining Dimension classes instead of using these methods,
 * however sometimes it is not possible (as is the case with the CustomVariables plugin).
 *
 * Finally, the {@link self::recordLogs()} method is called. In this method, RequestProcessors
 * should use the request metadata that was set (and maybe overridden) to insert whatever log data
 * they want.
 *
 * ## Extending The Piwik Tracker
 *
 * Plugins that want to change the tracking process in order to track new data or change how
 * existing data is tracked can create RequestProcessors to accomplish.
 *
 * _Note: If you only want to add tracked data to visits, actions or conversions, you should create
 * a {@link Dimension} class._
 *
 * To create a new RequestProcessor, create a new class that derives from this one, and implement the
 * methods you need. Then put this class inside the `Tracker` directory of your plugin.
 *
 * Final note: RequestProcessors are shared between tracking requests, and so, should ideally be
 * stateless. They are stored in DI, so they can contain references to other objects in DI, but
 * they shouldn't contain data that might change between tracking requests.
 */
abstract class RequestProcessor
{
    /**
     * This is the first method called when processing a tracker request.
     *
     * Derived classes can use this method to manipulate a tracker request before the request
     * is handled. Plugins could change the URL, add custom variables, etc.
     *
     * @param Request $request
     */
    public function manipulateRequest(Request $request)
    {
        // empty
    }

    /**
     * This is the second method called when processing a tracker request.
     *
     * Derived classes should use this method to set request metadata based on the tracking
     * request alone. They should not try to access request metadata from other plugins,
     * since they may not be set yet.
     *
     * When this method is called, `$visitProperties->visitorInfo` will be empty.
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     * @return bool If `true` the tracking request will be aborted.
     */
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        return false;
    }

    /**
     * This is the third method called when processing a tracker request.
     *
     * Derived classes should use this method to set request metadata that needs request metadata
     * from other plugins, or to override request metadata from other plugins to change
     * tracking behavior.
     *
     * When this method is called, you can assume all available request metadata from all plugins
     * will be initialized (but not at their final value). Also, `$visitProperties->visitorInfo`
     * will contain the values of the visitor's last known visit (if any).
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     * @return bool If `true` the tracking request will be aborted.
     */
    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        return false;
    }

    /**
     * This method is called before recording a new visit. You can set/change visit information here
     * to change what gets inserted into `log_visit`.
     *
     * Only implement this method if you cannot use a Dimension for the same thing.
     * 
     * Please note that the `onNewAction` hook in an action dimension is executed after this method.
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        // empty
    }

    /**
     * This method is called before updating an existing visit. You can set/change visit information
     * here to change what gets recorded in `log_visit`.
     *
     * Only implement this method if you cannot use a Dimension for the same thing.
     *
     * Please note that the `onNewAction` hook in an action dimension is executed before this method.
     *
     * @param array &$valuesToUpdate
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        // empty
    }

    /**
     * This method is called last. Derived classes should use this method to insert log data. They
     * should also only read request metadata, and not set it.
     *
     * When this method is called, you can assume all request metadata have their final values. Also,
     * `$visitProperties->visitorInfo` will contain the properties of the visitor's current visit (in
     * other words, the values in the array were persisted to the DB before this method was called).
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // empty
    }
}
