<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Http;
use Piwik\Session;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class FrontControllerTest extends IntegrationTestCase
{
    public function test_fatalErrorStackTracesReturned()
    {
        $url = Fixture::getRootUrl() . '/tests/resources/trigger-fatal.php?format=json';
        $response = Http::sendHttpRequest($url, self::isCIEnvironment() ? 5 : 20);

        $response = json_decode($response, $isAssoc = true);
        $response['message'] = $this->cleanMessage($response['message']);

        $this->assertEquals('error', $response['result']);

        $expectedFormat = <<<FORMAT
Allowed memory size of %s bytes exhausted (tried to allocate %s bytes) on {includePath}/tests/resources/trigger-fatal.php(23) #0 {includePath}/tests/resources/trigger-fatal.php(36): MyClass-&gt;triggerError(arg1=&quot;argval&quot;, arg2=&quot;another&quot;) #1 {includePath}/tests/resources/trigger-fatal.php(52): MyDerivedClass::staticMethod() #2 {includePath}/tests/resources/trigger-fatal.php(58): myFunction()
FORMAT;

        $this->assertStringMatchesFormat($expectedFormat, $response['message']);
    }

    public function test_thrownExceptionInFrontControllerPrintsBacktrace()
    {
        $url = Fixture::getRootUrl() . '/tests/resources/trigger-fatal-exception.php?format=json';
        $response = Http::sendHttpRequest($url, self::isCIEnvironment() ? 5 : 20);

        $response = json_decode($response, $isAssoc = true);
        $response['message'] = $this->cleanMessage($response['message']);

        $this->assertEquals('error', $response['result']);

        $expectedFormat = <<<FORMAT
test message on {includePath}/tests/resources/trigger-fatal-exception.php(23) #0 [internal function]: {closure}('CoreHome', 'index', Array) #1 {includePath}/core/EventDispatcher.php(141): call_user_func_array(Object(Closure), Array) #2 {includePath}/core/Piwik.php(845): Piwik\EventDispatcher-&gt;postEvent('Request.dispatc...', Array, false, Array) #3 {includePath}/core/FrontController.php(606): Piwik\Piwik::postEvent('Request.dispatc...', Array) #4 {includePath}/core/FrontController.php(168): Piwik\FrontController-&gt;doDispatch('CoreHome', 'index', Array) #5 {includePath}/tests/resources/trigger-fatal-exception.php(31): Piwik\FrontController-&gt;dispatch('CoreHome', 'index') #6 {main}
FORMAT;

        //remove all the numbers
        $expectedFormat = preg_replace('/[0-9]+/', 'x', $expectedFormat);
        $expectedFormat = preg_replace('/".*?"|\'.*?\'/', 'xxx', $expectedFormat);

        $actualFormat = preg_replace('/[0-9]+/', 'x', $response['message']);
        $actualFormat = preg_replace('/".*?"|\'.*?\'/', 'xxx', $actualFormat);

        $this->assertStringMatchesFormat($expectedFormat, $actualFormat);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_authImplementationConfigured_EvenIfSessionAuthSucceeds()
    {
        Session::start();

        Access::getInstance()->setSuperUserAccess(false);

        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize('superUserLogin', Fixture::getTokenAuth());

        FrontController::getInstance()->init();

        /** @var \Piwik\Plugins\Login\Auth $auth */
        $auth = StaticContainer::get(Auth::class);
        $this->assertInstanceOf(\Piwik\Plugins\Login\Auth::class, $auth);

        $this->assertEquals('superUserLogin', $auth->getLogin());
        $this->assertEquals(Fixture::getTokenAuth(), $auth->getTokenAuth());
    }

    public function test_ifHealthCheckParamPassedInUrl_ReturnsHealthCheckResponse(): void
    {
        $_GET['healthCheck'] = 1;

        FrontController::clearAll();
        ob_start();
        FrontController::getInstance()->init();
        $output = ob_get_clean();

        $this->assertEquals('{"status":"PASS","checks":[{"name":"dashboardAvailable","status":"PASS"}]}', $output);

        unset($_GET['healthCheck']);
    }

    private function cleanMessage($message)
    {
        $message = trim($message);
        $message = str_replace(PIWIK_INCLUDE_PATH, '{includePath}', $message);
        return $message;
    }

    /**
     * @param Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
