/*!
 * Matomo - free/libre analytics platform
 *
 * Unit tests for offline-service-worker.js syncQueue() — focused on:
 *   - bulk JSON request bodies get `cdo` stamped on every inner request,
 *     not just the first one
 *   - the promise returned by syncQueue() resolves only after all
 *     fetches/deletes settle (so event.waitUntil keeps the SW alive)
 *   - too-old entries are dropped without being fetched
 *   - successful fetches delete from the queue; failed ones don't
 *
 * No real IndexedDB, no real fetch — a minimal fake IDB matching just the
 * surface that syncQueue() uses (open/openCursor/delete/count) is good enough.
 */

const matomoAnalytics = require('../../offline-service-worker');

// ---- Minimal IDB fake ------------------------------------------------------
//
// syncQueue() only needs:
//   indexedDB.open(name) -> request with onerror/onupgradeneeded/onsuccess
//   db.transaction(store, mode).objectStore(store) -> store
//   store.openCursor() -> request whose onsuccess is assigned; each invocation
//       of the cursor (cursor.continue()) re-fires onsuccess with the next entry
//   store.delete(id) -> request
//   store.count() -> request with onsuccess + .result
//
// All "requests" here invoke onsuccess synchronously via queueMicrotask so the
// shape mirrors IndexedDB's async behavior (handlers attached *before* dispatch).

function makeFakeIDB(initialEntries) {
    const entries = (initialEntries || []).map((e, i) => ({
        id: e.id != null ? e.id : i + 1,
        ...e,
    }));

    function makeStore() {
        return {
            openCursor() {
                const req = {};
                const snapshot = entries.slice();
                Promise.resolve().then(() => {
                    let i = 0;
                    function fire() {
                        let cursor = null;
                        if (i < snapshot.length) {
                            const value = snapshot[i];
                            cursor = {
                                key: value.id,
                                value,
                                continue() {
                                    i += 1;
                                    Promise.resolve().then(fire);
                                },
                            };
                        }
                        req.onsuccess({ target: { result: cursor } });
                    }
                    fire();
                });
                return req;
            },
            delete(id) {
                const idx = entries.findIndex((e) => e.id === id);
                if (idx >= 0) entries.splice(idx, 1);
                const req = {};
                Promise.resolve().then(() => req.onsuccess && req.onsuccess());
                return req;
            },
            count() {
                const req = {};
                Promise.resolve().then(() => req.onsuccess && req.onsuccess({ result: entries.length }));
                return req;
            },
            add(entry) {
                entries.push(entry);
                const req = {};
                Promise.resolve().then(() => req.onsuccess && req.onsuccess());
                return req;
            },
        };
    }

    const idb = {
        open() {
            const req = {};
            Promise.resolve().then(() => {
                req.onsuccess({
                    target: {
                        result: {
                            transaction() {
                                return { objectStore: () => makeStore() };
                            },
                            objectStoreNames: { contains: () => true },
                        },
                    },
                });
            });
            return req;
        },
        _entries: () => entries,
    };
    return idb;
}

// ---- Test helpers ----------------------------------------------------------

function fixedNow() { return 1_700_000_000_000; }

function initialize(extraOpts) {
    return matomoAnalytics.initialize({
        navigator: { onLine: true },
        now: fixedNow,
        self: null, // skip SW event-listener registration
        ...extraOpts,
    });
}

// ---- Specs -----------------------------------------------------------------

describe('offline-service-worker syncQueue', function () {

    it('stamps cdo on URL when the request is a GET-style singleton', async function () {
        const idb = makeFakeIDB([{
            id: 1,
            url: 'https://example.org/matomo.php?idsite=1&rec=1',
            method: 'GET',
            headers: {},
            created: fixedNow() - 30_000, // 30s ago
        }]);
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        const sw = initialize({ indexedDB: idb, fetch: fetchMock });
        await sw.syncQueue();

        expect(fetchMock).toHaveBeenCalledTimes(1);
        const calledUrl = fetchMock.mock.calls[0][0];
        expect(calledUrl).toBe('https://example.org/matomo.php?idsite=1&rec=1&cdo=30');
    });

    it('stamps cdo on EVERY inner request in a bulk JSON body', async function () {
        const bulkBody = JSON.stringify({
            requests: ['?idsite=1&rec=1&e_c=a', '?idsite=1&rec=1&e_c=b', '?idsite=1&rec=1&e_c=c'],
            send_image: 0,
        });
        const idb = makeFakeIDB([{
            id: 1,
            url: 'https://example.org/matomo.php', // no '?' -> body path
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: bulkBody,
            created: fixedNow() - 45_000,
        }]);
        const fetchMock = jest.fn().mockResolvedValue({ status: 204 });

        const sw = initialize({ indexedDB: idb, fetch: fetchMock });
        await sw.syncQueue();

        expect(fetchMock).toHaveBeenCalledTimes(1);
        const init = fetchMock.mock.calls[0][1];
        const sentBody = JSON.parse(init.body);
        expect(sentBody.requests).toEqual([
            '?idsite=1&rec=1&e_c=a&cdo=45',
            '?idsite=1&rec=1&e_c=b&cdo=45',
            '?idsite=1&rec=1&e_c=c&cdo=45',
        ]);
        expect(sentBody.send_image).toBe(0);
    });

    it('resolves the returned promise only AFTER all fetches settle', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?a=1', method: 'GET', headers: {}, created: fixedNow() - 1000 },
            { id: 2, url: 'https://example.org/matomo.php?b=2', method: 'GET', headers: {}, created: fixedNow() - 1000 },
            { id: 3, url: 'https://example.org/matomo.php?c=3', method: 'GET', headers: {}, created: fixedNow() - 1000 },
        ]);

        // Hold all fetches open until we let them resolve.
        const fetchResolvers = [];
        const fetchMock = jest.fn().mockImplementation(() => new Promise((resolve) => {
            fetchResolvers.push(resolve);
        }));

        const sw = initialize({ indexedDB: idb, fetch: fetchMock });
        const syncPromise = sw.syncQueue();

        // Deterministic "did syncPromise resolve early?" check: race it against
        // a sentinel that resolves AFTER all microtasks/macrotasks have drained.
        // If syncPromise wins the race, it resolved too early — bug.
        function racedAgainstSettled(promise) {
            const sentinel = new Promise((resolve) => setImmediate(() => resolve('SENTINEL')));
            return Promise.race([promise.then(() => 'SYNC'), sentinel]);
        }

        // All three fetches should be dispatched, but the outer promise must
        // not resolve while any fetch is still pending.
        let winner = await racedAgainstSettled(syncPromise);
        expect(fetchMock).toHaveBeenCalledTimes(3);
        expect(winner).toBe('SENTINEL');

        // Resolve two of three; outer promise must still wait.
        fetchResolvers[0]({ status: 200 });
        fetchResolvers[1]({ status: 200 });
        winner = await racedAgainstSettled(syncPromise);
        expect(winner).toBe('SENTINEL');

        // Resolve the last; only now should the outer promise settle.
        fetchResolvers[2]({ status: 200 });
        await expect(syncPromise).resolves.toBeDefined();
    });

    it('does nothing (no fetches) when navigator.onLine is false', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?a=1', method: 'GET', headers: {}, created: fixedNow() - 1000 },
            { id: 2, url: 'https://example.org/matomo.php?b=2', method: 'GET', headers: {}, created: fixedNow() - 1000 },
        ]);
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        const sw = initialize({
            indexedDB: idb,
            fetch: fetchMock,
            navigator: { onLine: false },
        });
        await sw.syncQueue();

        expect(fetchMock).not.toHaveBeenCalled();
        // Entries must stay in the queue, ready for the next sync attempt.
        expect(idb._entries().map((e) => e.id)).toEqual([1, 2]);
    });

    it('stamps cdo into a form-encoded body via the &idsite= replace path', async function () {
        // The 3rd branch in the cdo-stamping ladder: body present, no '?' in
        // the URL, and the body is NOT a bulk JSON `requests` envelope. This
        // matches the legacy form-encoded singleton-via-body case.
        const idb = makeFakeIDB([{
            id: 1,
            url: 'https://example.org/matomo.php',
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'rec=1&idsite=7&action_name=Home',
            created: fixedNow() - 12_000,
        }]);
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        const sw = initialize({ indexedDB: idb, fetch: fetchMock });
        await sw.syncQueue();

        const init = fetchMock.mock.calls[0][1];
        expect(init.body).toBe('rec=1&cdo=12&idsite=7&action_name=Home');
    });

    it('deletes expired entries (older than maxTimeLimit) without fetching them', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?old=1', method: 'GET', headers: {}, created: fixedNow() - (2 * 24 * 60 * 60 * 1000) }, // 2 days old
            { id: 2, url: 'https://example.org/matomo.php?fresh=1', method: 'GET', headers: {}, created: fixedNow() - 10_000 },
        ]);
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        const sw = initialize({ indexedDB: idb, fetch: fetchMock, timeLimit: 60 * 60 * 24 });
        await sw.syncQueue();

        expect(fetchMock).toHaveBeenCalledTimes(1);
        expect(fetchMock.mock.calls[0][0]).toContain('fresh=1');
        // Both entries should be gone: stale was deleted, fresh was sent + deleted.
        expect(idb._entries()).toEqual([]);
    });

    it('keeps the entry in the queue when the server returns 5xx', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?ok=1',  method: 'GET', headers: {}, created: fixedNow() - 1000 },
            { id: 2, url: 'https://example.org/matomo.php?fail=1', method: 'GET', headers: {}, created: fixedNow() - 1000 },
        ]);
        const fetchMock = jest.fn()
            .mockResolvedValueOnce({ status: 200 }) // first entry succeeds
            .mockResolvedValueOnce({ status: 500 }); // second fails

        const sw = initialize({ indexedDB: idb, fetch: fetchMock });
        await sw.syncQueue();

        const remaining = idb._entries().map((e) => e.id);
        expect(remaining).toEqual([2]); // the failing one survives for retry
    });

    // Regression guard: WHATWG fetch in a real WorkerGlobalScope throws
    // "TypeError: Illegal invocation" when its `this` is anything other than
    // the global. A naive `deps.fetch(...)` call detaches the receiver and
    // silently passes jest.fn() (which doesn't enforce its receiver) while
    // breaking in real browsers. Assert here that fetch is invoked with
    // `this === deps.self` (the SW global), not `this === deps`.
    it('invokes fetch with the SW global as the receiver, not the deps object', async function () {
        const idb = makeFakeIDB([{
            id: 1,
            url: 'https://example.org/matomo.php?check=receiver',
            method: 'GET',
            headers: {},
            created: fixedNow() - 1000,
        }]);

        // In production, self IS the WorkerGlobalScope; the test stub stands in.
        const fakeSelf = { addEventListener() {} };

        let observedThis = null;
        function fetchSpy() {
            observedThis = this;
            return Promise.resolve({ status: 200 });
        }

        const sw = matomoAnalytics.initialize({
            indexedDB: idb,
            fetch: fetchSpy,
            now: fixedNow,
            navigator: { onLine: true },
            self: fakeSelf,
        });
        await sw.syncQueue();

        expect(observedThis).toBe(fakeSelf);
        // Catch the specific regression directly: fetch must NOT be called as
        // a method of the internal `deps` bag.
        expect(observedThis && observedThis.fetch).toBeUndefined();
    });

    // The 'sync' event listener is what production actually invokes. The two
    // fixes in this PR are wired through it: the bulk-replay cdo stamping
    // runs inside syncQueue (already covered above), and event.waitUntil keeps
    // the SW alive for the full replay. Assert both here by dispatching a
    // real-shape sync event at a stub self and inspecting what waitUntil saw.
    it('dispatches sync event with tag=matomoSync to waitUntil(syncQueue())', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?a=1', method: 'GET', headers: {}, created: fixedNow() - 5000 },
            { id: 2, url: 'https://example.org/matomo.php?b=2', method: 'GET', headers: {}, created: fixedNow() - 5000 },
        ]);

        // Capture registered listeners so the test can dispatch a sync event.
        const listeners = {};
        const fakeSelf = {
            addEventListener(name, fn) { listeners[name] = fn; },
        };
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        matomoAnalytics.initialize({
            indexedDB: idb,
            fetch: fetchMock,
            now: fixedNow,
            navigator: { onLine: true },
            self: fakeSelf,
        });

        expect(typeof listeners.sync).toBe('function');

        // Build a fake SyncEvent. waitUntilArg captures the promise so the
        // test can await it — which is exactly what the browser does.
        let waitUntilArg = null;
        const syncEvent = {
            tag: 'matomoSync',
            waitUntil(p) { waitUntilArg = p; },
        };

        listeners.sync(syncEvent);
        expect(waitUntilArg).not.toBeNull();
        expect(typeof waitUntilArg.then).toBe('function');

        // The promise passed to waitUntil must resolve only AFTER all queued
        // fetches/deletes settle (the contract that keeps the SW alive).
        await waitUntilArg;
        expect(fetchMock).toHaveBeenCalledTimes(2);
        expect(idb._entries()).toEqual([]);
    });

    it('ignores sync events whose tag is not matomoSync', async function () {
        const idb = makeFakeIDB([
            { id: 1, url: 'https://example.org/matomo.php?a=1', method: 'GET', headers: {}, created: fixedNow() - 1000 },
        ]);
        const listeners = {};
        const fakeSelf = { addEventListener(name, fn) { listeners[name] = fn; } };
        const fetchMock = jest.fn().mockResolvedValue({ status: 200 });

        matomoAnalytics.initialize({
            indexedDB: idb,
            fetch: fetchMock,
            now: fixedNow,
            navigator: { onLine: true },
            self: fakeSelf,
        });

        let waitUntilCalled = false;
        listeners.sync({
            tag: 'somethingElse',
            waitUntil() { waitUntilCalled = true; },
        });

        expect(waitUntilCalled).toBe(false);
        expect(fetchMock).not.toHaveBeenCalled();
    });
});
