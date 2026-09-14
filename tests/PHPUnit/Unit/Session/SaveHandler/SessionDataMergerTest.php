<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Session\SaveHandler;

use Piwik\Notification;
use Piwik\Session\SaveHandler\SessionDataMerger;
use Piwik\Session\SessionFingerprint;
use PHPUnit\Framework\TestCase;

class SessionDataMergerTest extends TestCase
{
    /**
     * @var SessionDataMerger
     */
    private $merger;

    public function setUp(): void
    {
        parent::setUp();
        $this->merger = new SessionDataMerger();
    }

    public function testDecodeReadsBackWhatEncodeWrote()
    {
        $session = [
            SessionFingerprint::USER_NAME_SESSION_VAR_NAME => 'chip',
            SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME => ['expiration' => 123],
            'Login.login' => ['nonce' => 'abc'],
            'notification' => ['notifications' => ['welcome' => new Notification('hello')]],
            '__ZF' => ['Login.login' => ['ENVT' => ['nonce' => 456]]],
        ];

        $this->assertEquals($session, $this->merger->decode($this->merger->encode($session)));
    }

    public function testDecodeTreatsNothingStoredAsAnEmptySession()
    {
        $this->assertSame([], $this->merger->decode(''));
    }

    /**
     * @dataProvider getUnreadableData
     */
    public function testDecodeReturnsNullForDataItCannotRead($data)
    {
        $this->assertNull($this->merger->decode($data));
    }

    public function getUnreadableData()
    {
        return [
            'not serialized at all' => ['firstdata'],
            'serialized but not the envelope' => [serialize(['user.name' => 'chip'])],
            'envelope with extra keys' => [serialize(['data' => 'x', 'other' => 1])],
            'envelope holding something other than a string' => [serialize(['data' => ['x']])],
            'envelope holding data that is not a session' => [serialize(['data' => base64_encode(serialize('x'))])],
        ];
    }

    /**
     * @dataProvider getMergeCases
     */
    public function testMergeArraysResolvesEachCase($base, $mine, $theirs, $expected)
    {
        $this->assertSame($expected, $this->merger->mergeArrays($base, $mine, $theirs));
    }

    public function getMergeCases()
    {
        $value = ['k' => 'base'];
        $changed = ['k' => 'mine'];
        $otherChanged = ['k' => 'theirs'];

        return [
            'both left it alone' => [
                ['a' => $value], ['a' => $value], ['a' => $value], ['a' => $value],
            ],
            'both made the same change' => [
                ['a' => $value], ['a' => $changed], ['a' => $changed], ['a' => $changed],
            ],
            'both removed it' => [
                ['a' => $value], [], [], [],
            ],
            'only they changed it' => [
                ['a' => $value], ['a' => $value], ['a' => $otherChanged], ['a' => $otherChanged],
            ],
            'only they removed it' => [
                ['a' => $value], ['a' => $value], [], [],
            ],
            'only I changed it' => [
                ['a' => $value], ['a' => $changed], ['a' => $value], ['a' => $changed],
            ],
            'only I removed it' => [
                ['a' => $value], [], ['a' => $value], [],
            ],
            'I added it' => [
                [], ['a' => $changed], [], ['a' => $changed],
            ],
            'they added it' => [
                [], [], ['a' => $otherChanged], ['a' => $otherChanged],
            ],
            'each of us added a different key' => [
                [], ['a' => $changed], ['b' => $otherChanged], ['a' => $changed, 'b' => $otherChanged],
            ],
            'we both changed the same value' => [
                ['a' => 'base'], ['a' => 'mine'], ['a' => 'theirs'], ['a' => 'mine'],
            ],
            'I removed it while they changed it' => [
                ['a' => $value], [], ['a' => $otherChanged], [],
            ],
            'they removed it while I changed it' => [
                ['a' => $value], ['a' => $changed], [], [],
            ],
        ];
    }

    public function testMergeArraysKeepsEntriesBothRequestsAddedUnderTheirOwnKey()
    {
        $base = ['notification' => ['notifications' => ['first' => 'a']]];
        $mine = ['notification' => ['notifications' => ['first' => 'a', 'mine' => 'b']]];
        $theirs = ['notification' => ['notifications' => ['first' => 'a', 'theirs' => 'c']]];

        $this->assertSame(
            ['notification' => ['notifications' => ['first' => 'a', 'mine' => 'b', 'theirs' => 'c']]],
            $this->merger->mergeArrays($base, $mine, $theirs)
        );
    }

    public function testMergeArraysKeepsTheNewestListInsteadOfCombiningEntriesByPosition()
    {
        $base = ['ns' => ['items' => ['a']]];
        $mine = ['ns' => ['items' => ['a', 'mine']]];
        $theirs = ['ns' => ['items' => ['a', 'theirs']]];

        $this->assertSame(
            ['ns' => ['items' => ['a', 'mine']]],
            $this->merger->mergeArrays($base, $mine, $theirs)
        );
    }

    public function testMergeArraysKeepsANonceIssuedToReplaceTheOneThisRequestUsed()
    {
        $base = ['Login.login' => ['nonce' => 'used']];
        $mine = [];
        $theirs = ['Login.login' => ['nonce' => 'fresh']];

        $this->assertSame(
            ['Login.login' => ['nonce' => 'fresh']],
            $this->merger->mergeArrays($base, $mine, $theirs)
        );
    }

    public function testMergeArraysStillRemovesANamespaceThatChangedButKeptTheUsedNonce()
    {
        $base = ['Login.login' => ['nonce' => 'used']];
        $mine = [];
        $theirs = ['Login.login' => ['nonce' => 'used', 'other' => 1]];

        $this->assertSame([], $this->merger->mergeArrays($base, $mine, $theirs));
    }

    public function testMergeArraysKeepsIdentityKeysRemovedWhenAnotherRequestChangedThem()
    {
        $base = [
            SessionFingerprint::USER_NAME_SESSION_VAR_NAME => 'chip',
            SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME => ['expiration' => 100],
        ];
        // this request logged out
        $mine = [];
        // the other one refreshed how long the session lasts
        $theirs = [
            SessionFingerprint::USER_NAME_SESSION_VAR_NAME => 'chip',
            SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME => ['expiration' => 200],
        ];

        $this->assertSame([], $this->merger->mergeArrays($base, $mine, $theirs));
    }

    public function testMergeArraysKeepsAnIdentityKeyRemovedEvenWhenItLooksLikeANonce()
    {
        $key = SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME;

        $base = [$key => ['nonce' => 'one']];
        $mine = [];
        $theirs = [$key => ['nonce' => 'two']];

        $this->assertSame([], $this->merger->mergeArrays($base, $mine, $theirs));
    }

    public function testMergeKeepsARealNonceWhenTheOtherRequestOnlyLookedAtIt()
    {
        // reading a nonce that is not set stores it as null, which must not count as a value
        $base = $this->merger->encode([]);
        $mine = $this->merger->encode(['Login.login' => ['nonce' => null]]);
        $theirs = $this->merger->encode(['Login.login' => ['nonce' => 'abc']]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertSame(['Login.login' => ['nonce' => 'abc']], $merged);
    }

    public function testMergeDropsAWholeNamespaceAnotherRequestOnlyLookedAt()
    {
        // reading one value of a namespace that is not set stores the namespace as well
        $base = $this->merger->encode(['user.name' => 'chip']);
        $mine = $this->merger->encode(['user.name' => 'chip', 'siteWithoutData' => ['ignoreMessage' => null]]);
        $theirs = $this->merger->encode(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertSame(['user.name' => 'chip', 'Dashboard' => ['layout' => 'x']], $merged);
    }

    public function testMergeKeepsANullThatWasAlreadyStored()
    {
        // this one is not something a read added, it is a value someone stored on purpose
        $base = $this->merger->encode(['prefs' => ['lastReport' => null]]);
        $mine = $this->merger->encode(['prefs' => ['lastReport' => null], 'fromMe' => 1]);
        $theirs = $this->merger->encode(['prefs' => ['lastReport' => null], 'fromThem' => 1]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertSame(
            ['prefs' => ['lastReport' => null], 'fromMe' => 1, 'fromThem' => 1],
            $merged
        );
    }

    public function testMergeLetsARequestStoreANullOverAValueItRead()
    {
        $base = $this->merger->encode(['prefs' => ['lastReport' => 'yesterday']]);
        $mine = $this->merger->encode(['prefs' => ['lastReport' => null]]);
        $theirs = $this->merger->encode(['prefs' => ['lastReport' => 'yesterday'], 'fromThem' => 1]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertSame(['prefs' => ['lastReport' => null], 'fromThem' => 1], $merged);
    }

    public function testMergeKeepsMetadataEachRequestStampedForADifferentNamespace()
    {
        $base = $this->merger->encode(['__ZF' => []]);
        $mine = $this->merger->encode(['__ZF' => ['A' => ['ENVT' => ['nonce' => 1]]]]);
        $theirs = $this->merger->encode(['__ZF' => ['B' => ['ENVT' => ['nonce' => 2]]]]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertSame(
            ['__ZF' => ['A' => ['ENVT' => ['nonce' => 1]], 'B' => ['ENVT' => ['nonce' => 2]]]],
            $merged
        );
    }

    public function testMergeComparesStoredObjectsByWhatTheyHoldRatherThanIdentity()
    {
        $base = $this->merger->encode(['notification' => ['notifications' => ['a' => new Notification('hello')]]]);
        // the same notification, rebuilt - this request did not really change it
        $mine = $this->merger->encode(['notification' => ['notifications' => ['a' => new Notification('hello')]]]);
        $theirs = $this->merger->encode([
            'notification' => ['notifications' => ['a' => new Notification('hello'), 'b' => new Notification('later')]],
        ]);

        $merged = $this->merger->decode($this->merger->merge($base, $mine, $theirs));

        $this->assertEquals(
            ['a' => new Notification('hello'), 'b' => new Notification('later')],
            $merged['notification']['notifications']
        );
    }

    public function testMergeReturnsNullWhenAnyOfTheValuesCannotBeRead()
    {
        $readable = $this->merger->encode(['user.name' => 'chip']);

        $this->assertNull($this->merger->merge('firstdata', $readable, $readable));
        $this->assertNull($this->merger->merge($readable, 'seconddata', $readable));
        $this->assertNull($this->merger->merge($readable, $readable, 'thirddata'));
    }
}
