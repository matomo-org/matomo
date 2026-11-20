<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\API\ParameterValidator;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Log\LoggerInterface;
use Exception;

/**
 * The base class of all API singletons.
 *
 * Plugins that want to expose functionality through the Reporting API should create a class
 * that extends this one. Every public method in that class that is not annotated with **@ignore**
 * will be callable through Matomo's Web API.
 *
 * _Note: If your plugin calculates and stores reports, they should be made available through the API._
 *
 * ### Examples
 *
 * **Defining an API for a plugin**
 *
 *     class API extends \Piwik\Plugin\API
 *     {
 *         public function myMethod($idSite, $period, $date, $segment = false)
 *         {
 *             $dataTable = // ... get some data ...
 *             return $dataTable;
 *         }
 *     }
 *
 * **Linking to an API method**
 *
 *     <a href="?module=API&method=MyPlugin.myMethod&idSite=1&period=day&date=2013-10-23">Link</a>
 *
 * @api
 */
abstract class API
{
    private static $instances;

    /** @var bool */
    protected $autoSanitizeInputParams = true;

    /**
     * Returns the singleton instance for the derived class. If the singleton instance
     * has not been created, this method will create it.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            $container = StaticContainer::getContainer();

            $refl = new \ReflectionClass($class);

            if (!$refl->getConstructor() || $refl->getConstructor()->isPublic()) {
                self::$instances[$class] = $container->get($class);
            } else {
                /** @var LoggerInterface $logger */
                $logger = $container->get(LoggerInterface::class);

                // BC with API defining a protected constructor
                $logger->notice('The API class {class} defines a protected constructor which is deprecated, make the constructor public instead', ['class' => $class]);
                self::$instances[$class] = new $class();
            }
        }

        return self::$instances[$class];
    }

    /**
     * Used in tests only
     * @ignore
     * @internal
     */
    public static function unsetInstance()
    {
        $class = get_called_class();
        unset(self::$instances[$class]);
    }

    /**
     * Used in tests only
     * @ignore
     * @internal
     */
    public static function unsetAllInstances()
    {
        self::$instances = [];
    }

    /**
     * Sets the singleton instance. For testing purposes.
     * @ignore
     * @internal
     */
    public static function setSingletonInstance($instance)
    {
        $class = get_called_class();
        self::$instances[$class] = $instance;
    }

    /**
     * Verifies if the given password matches the current users password
     *
     * @param $passwordConfirmation
     * @throws Exception
     */
    protected function confirmCurrentUserPassword(
        #[\SensitiveParameter]
        $passwordConfirmation
    ) {
        $loginCurrentUser = Piwik::getCurrentUserLogin();

        if (!Piwik::doesUserRequirePasswordConfirmation($loginCurrentUser)) {
            return; // password confirmation disabled for user
        }

        if (empty($passwordConfirmation)) {
            throw new Exception(Piwik::translate('UsersManager_ConfirmWithReAuthentication'));
        }

        try {
            if (
                !StaticContainer::get(PasswordVerifier::class)->isPasswordCorrect(
                    $loginCurrentUser,
                    $passwordConfirmation
                )
            ) {
                throw new Exception(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'));
            }
        } catch (Exception $e) {
            // in case of any error (e.g. the provided password is too weak)
            throw new Exception(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'));
        }
    }

    /**
     * Runs the validator attributes of the calling method's parameters (any validator in
     * `Piwik\Validators` that is marked as an attribute, for example `#[NumberRange(0)]`). Call it as the
     * method's first statement:
     *
     *     public function getUsers(#[NumberRange(0)] ?int $limit = null)
     *     {
     *         $this->checkParameters(__METHOD__, get_defined_vars());
     *         // ...
     *     }
     *
     * It covers every caller: HTTP requests and `Request::processRequest()` as well as direct PHP calls.
     * Parameters whose value is `null` are skipped. `__METHOD__` names the declaring class, so the
     * validators keep applying when a subclass overrides the method and calls `parent::`.
     *
     * @param string $method the calling method as `Class::method`, see `__METHOD__`
     * @param array<string, mixed> $arguments the calling method's arguments by name, see `get_defined_vars()`
     * @throws \Piwik\API\ParameterValidationException listing every parameter that failed (HTTP 400)
     */
    protected function checkParameters(string $method, array $arguments): void
    {
        StaticContainer::get(ParameterValidator::class)->validate($method, $arguments);
    }

    /**
     * @return bool
     * @internal
     */
    public function usesAutoSanitizeInputParams()
    {
        return $this->autoSanitizeInputParams;
    }
}
