<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\EventDispatcher;
use Piwik\EventDispatcher\EventDispatcher;
use Piwik\EventDispatcher\SubscriberInterface;
use Piwik\EventDispatcher\SubscriberProviderInterface;

/**
 * @group Core
 * @covers \Piwik\EventDispatcher\EventDispatcher
 */
class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchToObservers()
    {
        $dispatcher = new EventDispatcher($this->createSubscriberProvider());

        $called = $this->getMockForCallable();
        $called->expects($this->once())
            ->method('__invoke')
            ->with('bar');
        $notCalled = $this->getMockForCallable();
        $notCalled->expects($this->never())
            ->method('__invoke');

        $dispatcher->addObserver('foo', $called);
        $dispatcher->addObserver('bar', $notCalled);

        $dispatcher->postEvent('foo', array('bar'));
    }

    public function testDispatchToSubscribers()
    {
        $subscriber = new TestSubscriber();

        $dispatcher = new EventDispatcher($this->createSubscriberProvider(array($subscriber)));

        $dispatcher->postEvent('foo', array());

        $this->assertEquals(array('foo'), $subscriber->calledMethods);
    }

    public function testDispatchToExplicitSubscribers()
    {
        $subscriber1 = new TestSubscriber();
        $subscriber2 = new TestSubscriber();

        $dispatcher = new EventDispatcher($this->createSubscriberProvider(array($subscriber1)));

        $dispatcher->postEvent('foo', array(), false, array($subscriber2));

        $this->assertEquals(array(), $subscriber1->calledMethods);
        $this->assertEquals(array('foo'), $subscriber2->calledMethods);
    }

    public function testPostPendingEvents()
    {
        $dispatcher = new EventDispatcher($this->createSubscriberProvider());
        $dispatcher->postEvent('foo', array(), true);

        $subscriber = new TestSubscriber();
        $dispatcher->postPendingEventsTo($subscriber);

        $this->assertEquals(array('foo'), $subscriber->calledMethods);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|callable
     */
    private function getMockForCallable()
    {
        return $this->getMock('Piwik\Tests\Framework\Mock\CallableMock');
    }

    /**
     * @param array $subscribers
     * @return SubscriberProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSubscriberProvider(array $subscribers = array())
    {
        $mock = $this->getMockForAbstractClass('Piwik\EventDispatcher\SubscriberProviderInterface');
        $mock->expects($this->any())
            ->method('getEventSubscribers')
            ->willReturnCallback(function (array $eventSubscribers = array()) use ($subscribers) {
                return empty($eventSubscribers) ? $subscribers : $eventSubscribers;
            });
        return $mock;
    }
}

class TestSubscriber implements SubscriberInterface
{
    public $calledMethods = array();

    public function getListHooksRegistered()
    {
        return array(
            'foo' => 'foo',
            'bar' => 'bar',
        );
    }

    public function foo()
    {
        $this->calledMethods[] = __FUNCTION__;
    }

    public function bar()
    {
        $this->calledMethods[] = __FUNCTION__;
    }
}
