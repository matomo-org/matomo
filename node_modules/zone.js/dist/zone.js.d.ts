/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="angular/packages/zone.js/lib/zone" />
/**
 * Suppress closure compiler errors about unknown 'global' variable
 * @fileoverview
 * @suppress {undefinedVars}
 */
/**
 * Zone is a mechanism for intercepting and keeping track of asynchronous work.
 *
 * A Zone is a global object which is configured with rules about how to intercept and keep track
 * of the asynchronous callbacks. Zone has these responsibilities:
 *
 * 1. Intercept asynchronous task scheduling
 * 2. Wrap callbacks for error-handling and zone tracking across async operations.
 * 3. Provide a way to attach data to zones
 * 4. Provide a context specific last frame error handling
 * 5. (Intercept blocking methods)
 *
 * A zone by itself does not do anything, instead it relies on some other code to route existing
 * platform API through it. (The zone library ships with code which monkey patches all of the
 * browsers's asynchronous API and redirects them through the zone for interception.)
 *
 * In its simplest form a zone allows one to intercept the scheduling and calling of asynchronous
 * operations, and execute additional code before as well as after the asynchronous task. The rules
 * of interception are configured using [ZoneConfig]. There can be many different zone instances in
 * a system, but only one zone is active at any given time which can be retrieved using
 * [Zone#current].
 *
 *
 *
 * ## Callback Wrapping
 *
 * An important aspect of the zones is that they should persist across asynchronous operations. To
 * achieve this, when a future work is scheduled through async API, it is necessary to capture, and
 * subsequently restore the current zone. For example if a code is running in zone `b` and it
 * invokes `setTimeout` to scheduleTask work later, the `setTimeout` method needs to 1) capture the
 * current zone and 2) wrap the `wrapCallback` in code which will restore the current zone `b` once
 * the wrapCallback executes. In this way the rules which govern the current code are preserved in
 * all future asynchronous tasks. There could be a different zone `c` which has different rules and
 * is associated with different asynchronous tasks. As these tasks are processed, each asynchronous
 * wrapCallback correctly restores the correct zone, as well as preserves the zone for future
 * asynchronous callbacks.
 *
 * Example: Suppose a browser page consist of application code as well as third-party
 * advertisement code. (These two code bases are independent, developed by different mutually
 * unaware developers.) The application code may be interested in doing global error handling and
 * so it configures the `app` zone to send all of the errors to the server for analysis, and then
 * executes the application in the `app` zone. The advertising code is interested in the same
 * error processing but it needs to send the errors to a different third-party. So it creates the
 * `ads` zone with a different error handler. Now both advertising as well as application code
 * create many asynchronous operations, but the [Zone] will ensure that all of the asynchronous
 * operations created from the application code will execute in `app` zone with its error
 * handler and all of the advertisement code will execute in the `ads` zone with its error handler.
 * This will not only work for the async operations created directly, but also for all subsequent
 * asynchronous operations.
 *
 * If you think of chain of asynchronous operations as a thread of execution (bit of a stretch)
 * then [Zone#current] will act as a thread local variable.
 *
 *
 *
 * ## Asynchronous operation scheduling
 *
 * In addition to wrapping the callbacks to restore the zone, all operations which cause a
 * scheduling of work for later are routed through the current zone which is allowed to intercept
 * them by adding work before or after the wrapCallback as well as using different means of
 * achieving the request. (Useful for unit testing, or tracking of requests). In some instances
 * such as `setTimeout` the wrapping of the wrapCallback and scheduling is done in the same
 * wrapCallback, but there are other examples such as `Promises` where the `then` wrapCallback is
 * wrapped, but the execution of `then` is triggered by `Promise` scheduling `resolve` work.
 *
 * Fundamentally there are three kinds of tasks which can be scheduled:
 *
 * 1. [MicroTask] used for doing work right after the current task. This is non-cancelable which is
 *    guaranteed to run exactly once and immediately.
 * 2. [MacroTask] used for doing work later. Such as `setTimeout`. This is typically cancelable
 *    which is guaranteed to execute at least once after some well understood delay.
 * 3. [EventTask] used for listening on some future event. This may execute zero or more times, with
 *    an unknown delay.
 *
 * Each asynchronous API is modeled and routed through one of these APIs.
 *
 *
 * ### [MicroTask]
 *
 * [MicroTask]s represent work which will be done in current VM turn as soon as possible, before VM
 * yielding.
 *
 *
 * ### [MacroTask]
 *
 * [MacroTask]s represent work which will be done after some delay. (Sometimes the delay is
 * approximate such as on next available animation frame). Typically these methods include:
 * `setTimeout`, `setImmediate`, `setInterval`, `requestAnimationFrame`, and all browser specific
 * variants.
 *
 *
 * ### [EventTask]
 *
 * [EventTask]s represent a request to create a listener on an event. Unlike the other task
 * events they may never be executed, but typically execute more than once. There is no queue of
 * events, rather their callbacks are unpredictable both in order and time.
 *
 *
 * ## Global Error Handling
 *
 *
 * ## Composability
 *
 * Zones can be composed together through [Zone.fork()]. A child zone may create its own set of
 * rules. A child zone is expected to either:
 *
 * 1. Delegate the interception to a parent zone, and optionally add before and after wrapCallback
 *    hooks.
 * 2. Process the request itself without delegation.
 *
 * Composability allows zones to keep their concerns clean. For example a top most zone may choose
 * to handle error handling, while child zones may choose to do user action tracking.
 *
 *
 * ## Root Zone
 *
 * At the start the browser will run in a special root zone, which is configured to behave exactly
 * like the platform, making any existing code which is not zone-aware behave as expected. All
 * zones are children of the root zone.
 *
 */
interface Zone {
    /**
     *
     * @returns {Zone} The parent Zone.
     */
    parent: Zone | null;
    /**
     * @returns {string} The Zone name (useful for debugging)
     */
    name: string;
    /**
     * Returns a value associated with the `key`.
     *
     * If the current zone does not have a key, the request is delegated to the parent zone. Use
     * [ZoneSpec.properties] to configure the set of properties associated with the current zone.
     *
     * @param key The key to retrieve.
     * @returns {any} The value for the key, or `undefined` if not found.
     */
    get(key: string): any;
    /**
     * Returns a Zone which defines a `key`.
     *
     * Recursively search the parent Zone until a Zone which has a property `key` is found.
     *
     * @param key The key to use for identification of the returned zone.
     * @returns {Zone} The Zone which defines the `key`, `null` if not found.
     */
    getZoneWith(key: string): Zone | null;
    /**
     * Used to create a child zone.
     *
     * @param zoneSpec A set of rules which the child zone should follow.
     * @returns {Zone} A new child zone.
     */
    fork(zoneSpec: ZoneSpec): Zone;
    /**
     * Wraps a callback function in a new function which will properly restore the current zone upon
     * invocation.
     *
     * The wrapped function will properly forward `this` as well as `arguments` to the `callback`.
     *
     * Before the function is wrapped the zone can intercept the `callback` by declaring
     * [ZoneSpec.onIntercept].
     *
     * @param callback the function which will be wrapped in the zone.
     * @param source A unique debug location of the API being wrapped.
     * @returns {function(): *} A function which will invoke the `callback` through [Zone.runGuarded].
     */
    wrap<F extends Function>(callback: F, source: string): F;
    /**
     * Invokes a function in a given zone.
     *
     * The invocation of `callback` can be intercepted by declaring [ZoneSpec.onInvoke].
     *
     * @param callback The function to invoke.
     * @param applyThis
     * @param applyArgs
     * @param source A unique debug location of the API being invoked.
     * @returns {any} Value from the `callback` function.
     */
    run<T>(callback: Function, applyThis?: any, applyArgs?: any[], source?: string): T;
    /**
     * Invokes a function in a given zone and catches any exceptions.
     *
     * Any exceptions thrown will be forwarded to [Zone.HandleError].
     *
     * The invocation of `callback` can be intercepted by declaring [ZoneSpec.onInvoke]. The
     * handling of exceptions can be intercepted by declaring [ZoneSpec.handleError].
     *
     * @param callback The function to invoke.
     * @param applyThis
     * @param applyArgs
     * @param source A unique debug location of the API being invoked.
     * @returns {any} Value from the `callback` function.
     */
    runGuarded<T>(callback: Function, applyThis?: any, applyArgs?: any[], source?: string): T;
    /**
     * Execute the Task by restoring the [Zone.currentTask] in the Task's zone.
     *
     * @param task to run
     * @param applyThis
     * @param applyArgs
     * @returns {any} Value from the `task.callback` function.
     */
    runTask<T>(task: Task, applyThis?: any, applyArgs?: any): T;
    /**
     * Schedule a MicroTask.
     *
     * @param source
     * @param callback
     * @param data
     * @param customSchedule
     */
    scheduleMicroTask(source: string, callback: Function, data?: TaskData, customSchedule?: (task: Task) => void): MicroTask;
    /**
     * Schedule a MacroTask.
     *
     * @param source
     * @param callback
     * @param data
     * @param customSchedule
     * @param customCancel
     */
    scheduleMacroTask(source: string, callback: Function, data?: TaskData, customSchedule?: (task: Task) => void, customCancel?: (task: Task) => void): MacroTask;
    /**
     * Schedule an EventTask.
     *
     * @param source
     * @param callback
     * @param data
     * @param customSchedule
     * @param customCancel
     */
    scheduleEventTask(source: string, callback: Function, data?: TaskData, customSchedule?: (task: Task) => void, customCancel?: (task: Task) => void): EventTask;
    /**
     * Schedule an existing Task.
     *
     * Useful for rescheduling a task which was already canceled.
     *
     * @param task
     */
    scheduleTask<T extends Task>(task: T): T;
    /**
     * Allows the zone to intercept canceling of scheduled Task.
     *
     * The interception is configured using [ZoneSpec.onCancelTask]. The default canceler invokes
     * the [Task.cancelFn].
     *
     * @param task
     * @returns {any}
     */
    cancelTask(task: Task): any;
}
interface ZoneType {
    /**
     * @returns {Zone} Returns the current [Zone]. The only way to change
     * the current zone is by invoking a run() method, which will update the current zone for the
     * duration of the run method callback.
     */
    current: Zone;
    /**
     * @returns {Task} The task associated with the current execution.
     */
    currentTask: Task | null;
    /**
     * Verify that Zone has been correctly patched. Specifically that Promise is zone aware.
     */
    assertZonePatched(): void;
    /**
     *  Return the root zone.
     */
    root: Zone;
    /**
     * load patch for specified native module, allow user to
     * define their own patch, user can use this API after loading zone.js
     */
    __load_patch(name: string, fn: _PatchFn, ignoreDuplicate?: boolean): void;
    /**
     * Zone symbol API to generate a string with __zone_symbol__ prefix
     */
    __symbol__(name: string): string;
}
/**
 * Patch Function to allow user define their own monkey patch module.
 */
declare type _PatchFn = (global: Window, Zone: ZoneType, api: _ZonePrivate) => void;
/**
 * _ZonePrivate interface to provide helper method to help user implement
 * their own monkey patch module.
 */
interface _ZonePrivate {
    currentZoneFrame: () => _ZoneFrame;
    symbol: (name: string) => string;
    scheduleMicroTask: (task?: MicroTask) => void;
    onUnhandledError: (error: Error) => void;
    microtaskDrainDone: () => void;
    showUncaughtError: () => boolean;
    patchEventTarget: (global: any, apis: any[], options?: any) => boolean[];
    patchOnProperties: (obj: any, properties: string[] | null, prototype?: any) => void;
    patchThen: (ctro: Function) => void;
    patchMethod: (target: any, name: string, patchFn: (delegate: Function, delegateName: string, name: string) => (self: any, args: any[]) => any) => Function | null;
    bindArguments: (args: any[], source: string) => any[];
    patchMacroTask: (obj: any, funcName: string, metaCreator: (self: any, args: any[]) => any) => void;
    patchEventPrototype: (_global: any, api: _ZonePrivate) => void;
    isIEOrEdge: () => boolean;
    ObjectDefineProperty: (o: any, p: PropertyKey, attributes: PropertyDescriptor & ThisType<any>) => any;
    ObjectGetOwnPropertyDescriptor: (o: any, p: PropertyKey) => PropertyDescriptor | undefined;
    ObjectCreate(o: object | null, properties?: PropertyDescriptorMap & ThisType<any>): any;
    ArraySlice(start?: number, end?: number): any[];
    patchClass: (className: string) => void;
    wrapWithCurrentZone: (callback: any, source: string) => any;
    filterProperties: (target: any, onProperties: string[], ignoreProperties: any[]) => string[];
    attachOriginToPatched: (target: any, origin: any) => void;
    _redefineProperty: (target: any, callback: string, desc: any) => void;
    patchCallbacks: (api: _ZonePrivate, target: any, targetName: string, method: string, callbacks: string[]) => void;
    getGlobalObjects: () => {
        globalSources: any;
        zoneSymbolEventNames: any;
        eventNames: string[];
        isBrowser: boolean;
        isMix: boolean;
        isNode: boolean;
        TRUE_STR: string;
        FALSE_STR: string;
        ZONE_SYMBOL_PREFIX: string;
        ADD_EVENT_LISTENER_STR: string;
        REMOVE_EVENT_LISTENER_STR: string;
    } | undefined;
}
/**
 * _ZoneFrame represents zone stack frame information
 */
interface _ZoneFrame {
    parent: _ZoneFrame | null;
    zone: Zone;
}
interface UncaughtPromiseError extends Error {
    zone: Zone;
    task: Task;
    promise: Promise<any>;
    rejection: any;
    throwOriginal?: boolean;
}
/**
 * Provides a way to configure the interception of zone events.
 *
 * Only the `name` property is required (all other are optional).
 */
interface ZoneSpec {
    /**
     * The name of the zone. Useful when debugging Zones.
     */
    name: string;
    /**
     * A set of properties to be associated with Zone. Use [Zone.get] to retrieve them.
     */
    properties?: {
        [key: string]: any;
    };
    /**
     * Allows the interception of zone forking.
     *
     * When the zone is being forked, the request is forwarded to this method for interception.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param zoneSpec The argument passed into the `fork` method.
     */
    onFork?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, zoneSpec: ZoneSpec) => Zone;
    /**
     * Allows interception of the wrapping of the callback.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param delegate The argument passed into the `wrap` method.
     * @param source The argument passed into the `wrap` method.
     */
    onIntercept?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, delegate: Function, source: string) => Function;
    /**
     * Allows interception of the callback invocation.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param delegate The argument passed into the `run` method.
     * @param applyThis The argument passed into the `run` method.
     * @param applyArgs The argument passed into the `run` method.
     * @param source The argument passed into the `run` method.
     */
    onInvoke?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, delegate: Function, applyThis: any, applyArgs?: any[], source?: string) => any;
    /**
     * Allows interception of the error handling.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param error The argument passed into the `handleError` method.
     */
    onHandleError?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, error: any) => boolean;
    /**
     * Allows interception of task scheduling.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param task The argument passed into the `scheduleTask` method.
     */
    onScheduleTask?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, task: Task) => Task;
    onInvokeTask?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, task: Task, applyThis: any, applyArgs?: any[]) => any;
    /**
     * Allows interception of task cancellation.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param task The argument passed into the `cancelTask` method.
     */
    onCancelTask?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, task: Task) => any;
    /**
     * Notifies of changes to the task queue empty status.
     *
     * @param parentZoneDelegate Delegate which performs the parent [ZoneSpec] operation.
     * @param currentZone The current [Zone] where the current interceptor has been declared.
     * @param targetZone The [Zone] which originally received the request.
     * @param hasTaskState
     */
    onHasTask?: (parentZoneDelegate: ZoneDelegate, currentZone: Zone, targetZone: Zone, hasTaskState: HasTaskState) => void;
}
/**
 *  A delegate when intercepting zone operations.
 *
 *  A ZoneDelegate is needed because a child zone can't simply invoke a method on a parent zone. For
 *  example a child zone wrap can't just call parent zone wrap. Doing so would create a callback
 *  which is bound to the parent zone. What we are interested in is intercepting the callback before
 *  it is bound to any zone. Furthermore, we also need to pass the targetZone (zone which received
 *  the original request) to the delegate.
 *
 *  The ZoneDelegate methods mirror those of Zone with an addition of extra targetZone argument in
 *  the method signature. (The original Zone which received the request.) Some methods are renamed
 *  to prevent confusion, because they have slightly different semantics and arguments.
 *
 *  - `wrap` => `intercept`: The `wrap` method delegates to `intercept`. The `wrap` method returns
 *     a callback which will run in a given zone, where as intercept allows wrapping the callback
 *     so that additional code can be run before and after, but does not associate the callback
 *     with the zone.
 *  - `run` => `invoke`: The `run` method delegates to `invoke` to perform the actual execution of
 *     the callback. The `run` method switches to new zone; saves and restores the `Zone.current`;
 *     and optionally performs error handling. The invoke is not responsible for error handling,
 *     or zone management.
 *
 *  Not every method is usually overwritten in the child zone, for this reason the ZoneDelegate
 *  stores the closest zone which overwrites this behavior along with the closest ZoneSpec.
 *
 *  NOTE: We have tried to make this API analogous to Event bubbling with target and current
 *  properties.
 *
 *  Note: The ZoneDelegate treats ZoneSpec as class. This allows the ZoneSpec to use its `this` to
 *  store internal state.
 */
interface ZoneDelegate {
    zone: Zone;
    fork(targetZone: Zone, zoneSpec: ZoneSpec): Zone;
    intercept(targetZone: Zone, callback: Function, source: string): Function;
    invoke(targetZone: Zone, callback: Function, applyThis?: any, applyArgs?: any[], source?: string): any;
    handleError(targetZone: Zone, error: any): boolean;
    scheduleTask(targetZone: Zone, task: Task): Task;
    invokeTask(targetZone: Zone, task: Task, applyThis?: any, applyArgs?: any[]): any;
    cancelTask(targetZone: Zone, task: Task): any;
    hasTask(targetZone: Zone, isEmpty: HasTaskState): void;
}
declare type HasTaskState = {
    microTask: boolean;
    macroTask: boolean;
    eventTask: boolean;
    change: TaskType;
};
/**
 * Task type: `microTask`, `macroTask`, `eventTask`.
 */
declare type TaskType = 'microTask' | 'macroTask' | 'eventTask';
/**
 * Task type: `notScheduled`, `scheduling`, `scheduled`, `running`, `canceling`, 'unknown'.
 */
declare type TaskState = 'notScheduled' | 'scheduling' | 'scheduled' | 'running' | 'canceling' | 'unknown';
/**
 */
interface TaskData {
    /**
     * A periodic [MacroTask] is such which get automatically rescheduled after it is executed.
     */
    isPeriodic?: boolean;
    /**
     * Delay in milliseconds when the Task will run.
     */
    delay?: number;
    /**
     * identifier returned by the native setTimeout.
     */
    handleId?: number;
}
/**
 * Represents work which is executed with a clean stack.
 *
 * Tasks are used in Zones to mark work which is performed on clean stack frame. There are three
 * kinds of task. [MicroTask], [MacroTask], and [EventTask].
 *
 * A JS VM can be modeled as a [MicroTask] queue, [MacroTask] queue, and [EventTask] set.
 *
 * - [MicroTask] queue represents a set of tasks which are executing right after the current stack
 *   frame becomes clean and before a VM yield. All [MicroTask]s execute in order of insertion
 *   before VM yield and the next [MacroTask] is executed.
 * - [MacroTask] queue represents a set of tasks which are executed one at a time after each VM
 *   yield. The queue is ordered by time, and insertions can happen in any location.
 * - [EventTask] is a set of tasks which can at any time be inserted to the end of the [MacroTask]
 *   queue. This happens when the event fires.
 *
 */
interface Task {
    /**
     * Task type: `microTask`, `macroTask`, `eventTask`.
     */
    type: TaskType;
    /**
     * Task state: `notScheduled`, `scheduling`, `scheduled`, `running`, `canceling`, `unknown`.
     */
    state: TaskState;
    /**
     * Debug string representing the API which requested the scheduling of the task.
     */
    source: string;
    /**
     * The Function to be used by the VM upon entering the [Task]. This function will delegate to
     * [Zone.runTask] and delegate to `callback`.
     */
    invoke: Function;
    /**
     * Function which needs to be executed by the Task after the [Zone.currentTask] has been set to
     * the current task.
     */
    callback: Function;
    /**
     * Task specific options associated with the current task. This is passed to the `scheduleFn`.
     */
    data?: TaskData;
    /**
     * Represents the default work which needs to be done to schedule the Task by the VM.
     *
     * A zone may choose to intercept this function and perform its own scheduling.
     */
    scheduleFn?: (task: Task) => void;
    /**
     * Represents the default work which needs to be done to un-schedule the Task from the VM. Not all
     * Tasks are cancelable, and therefore this method is optional.
     *
     * A zone may chose to intercept this function and perform its own un-scheduling.
     */
    cancelFn?: (task: Task) => void;
    /**
     * @type {Zone} The zone which will be used to invoke the `callback`. The Zone is captured
     * at the time of Task creation.
     */
    readonly zone: Zone;
    /**
     * Number of times the task has been executed, or -1 if canceled.
     */
    runCount: number;
    /**
     * Cancel the scheduling request. This method can be called from `ZoneSpec.onScheduleTask` to
     * cancel the current scheduling interception. Once canceled the task can be discarded or
     * rescheduled using `Zone.scheduleTask` on a different zone.
     */
    cancelScheduleRequest(): void;
}
interface MicroTask extends Task {
    type: 'microTask';
}
interface MacroTask extends Task {
    type: 'macroTask';
}
interface EventTask extends Task {
    type: 'eventTask';
}
declare const Zone: ZoneType;
