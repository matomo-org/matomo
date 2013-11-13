<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

/**
 * Describes a UI notification.
 * 
 * UI notifications are messages displayed to the user near the top of the screen.
 * Notifications consist of a message, a context (the message type), a priority
 * and a display type.
 * 
 * A notification's context will affect the way the message looks, but not how it
 * is displayed. A notification's display type will determine how the message is
 * displayed. The priority determines where it is shown in the list of
 * all displayed notifications.
 * 
 * ### Examples
 * 
 * **Display an error message**
 * 
 *     $notification = new Notification('My Error Message');
 *     $notification->context = Notification::CONTEXT_ERROR;
 *     Notification\Manager::notify('myUniqueNotificationId', $notification);
 * 
 * **Display a temporary success message**
 * 
 *     $notification = new Notificiation('Success');
 *     $notification->context = Notification::CONTEXT_SUCCESS;
 *     $notification->type = Notification::TYPE_TOAST;
 *     Notification\Manager::notify('myUniqueNotificationId', $notification);
 * 
 * **Display a message near the top of the screen**
 * 
 *     $notification = new Notification('Urgent: Your password has expired!');
 *     $notification->context = Notification::CONTEXT_INFO;
 *     $notification->type = Notification::TYPE_PERSISTENT;
 *     $notification->priority = Notification::PRIORITY_MAX;
 * 
 * @package Piwik
 * @subpackage Notification
 * @api
 */
class Notification
{
    const CONTEXT_SUCCESS = 'success';
    const CONTEXT_ERROR   = 'error';
    const CONTEXT_INFO    = 'info';
    const CONTEXT_WARNING = 'warning';

    /**
     * Lowest priority value.
     */
    const PRIORITY_MIN    = 1;

    /**
     * Lower priority value.
     */
    const PRIORITY_LOW    = 25;

    /**
     * Higher priority value.
     */
    const PRIORITY_HIGH   = 50;

    /**
     * Highest priority value.
     */
    const PRIORITY_MAX    = 100;

    /**
     * If this flag applied, no close icon will be displayed. _Note: persistent notifications always have a close
     * icon._
     * 
     * See [flags](#flags).
     */
    const FLAG_NO_CLEAR   = 1;

    /**
     * Notifications of this type will be displayed for a few seconds and then faded out.
     */
    const TYPE_TOAST      = 'toast';

    /**
     * Notifications of this type will be displayed until the new user explicitly closes the notification.
     * The notifications will display even if the user reloads the page.
     */
    const TYPE_PERSISTENT = 'persistent';

    /**
     * Notifications of this type will be displayed only once. They will disappear after a page reload or
     * change.
     */
    const TYPE_TRANSIENT  = 'transient';

    /**
     * The notification title. The title is optional and is displayed directly before the message content.
     * 
     * @var string
     */
    public $title;

    /**
     * The notification message. Must be set.
     * 
     * @var string
     */
    public $message;

    /**
     * Contains extra display options.
     * 
     * Usage: `$notification->flags = Notification::FLAG_BAR | Notification::FLAG_FOO`.
     * 
     * @var int
     */
    public $flags = self::FLAG_NO_CLEAR;

    /**
     * The notification's display type. See `TYPE_*` constants in [this class](#).
     * 
     * @var string
     */
    public $type = self::TYPE_TRANSIENT;

    /**
     * The notification's context (message type). See `CONTEXT_*` constants in [this class](#).
     * 
     * A notification's context determines how it will be styled.
     * 
     * @var string
     */
    public $context = self::CONTEXT_INFO;

    /**
     * The notification's priority. The higher the priority, the higher the order. See `PRIORITY_*`
     * constants in [this class](#) to see possible priority values.
     * 
     * @var int
     */
    public $priority;

    /**
     * If true, the message will not be escaped before being outputted as HTML. If you set this to
     * true, make sure you escape text yourself in order to avoid any possible XSS vulnerabilities.
     * 
     * @var bool
     */
    public $raw = false;

    /**
     * Constructor.
     * 
     * @param  string $message   The notification message.
     * @throws \Exception        If the message is empty.
     */
    public function __construct($message)
    {
        if (empty($message)) {
            throw new \Exception('No notification message given');
        }

        $this->message = $message;
    }

    /**
     * Returns `1` if the notification will be displayed without a close button, `0` if otherwise.
     * 
     * @return int `1` or `0`.
     */
    public function hasNoClear()
    {
        if ($this->flags & self::FLAG_NO_CLEAR) {
            return 1;
        }

        return 0;
    }

    /**
     * Returns the notification's priority. If no priority has been set, a priority will be set based
     * on the notification's context.
     * 
     * @return int
     */
    public function getPriority()
    {
        if (!isset($this->priority)) {
            $typeToPriority = array(static::CONTEXT_ERROR   => static::PRIORITY_MAX,
                                    static::CONTEXT_WARNING => static::PRIORITY_HIGH,
                                    static::CONTEXT_SUCCESS => static::PRIORITY_MIN,
                                    static::CONTEXT_INFO    => static::PRIORITY_LOW);

            if (array_key_exists($this->context, $typeToPriority)) {
                return $typeToPriority[$this->context];
            }

            return static::PRIORITY_LOW;
        }

        return $this->priority;
    }
}