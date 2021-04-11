/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertDefined, assertEqual, assertNotEqual } from '../util/assert';
import { assertFirstCreatePass } from './assert';
import { NgOnChangesFeatureImpl } from './features/ng_onchanges_feature';
import { FLAGS, PREORDER_HOOK_FLAGS } from './interfaces/view';
import { isInCheckNoChangesMode } from './state';
/**
 * Adds all directive lifecycle hooks from the given `DirectiveDef` to the given `TView`.
 *
 * Must be run *only* on the first template pass.
 *
 * Sets up the pre-order hooks on the provided `tView`,
 * see {@link HookData} for details about the data structure.
 *
 * @param directiveIndex The index of the directive in LView
 * @param directiveDef The definition containing the hooks to setup in tView
 * @param tView The current TView
 */
export function registerPreOrderHooks(directiveIndex, directiveDef, tView) {
    ngDevMode && assertFirstCreatePass(tView);
    const { ngOnChanges, ngOnInit, ngDoCheck } = directiveDef.type.prototype;
    if (ngOnChanges) {
        const wrappedOnChanges = NgOnChangesFeatureImpl(directiveDef);
        (tView.preOrderHooks || (tView.preOrderHooks = [])).push(directiveIndex, wrappedOnChanges);
        (tView.preOrderCheckHooks || (tView.preOrderCheckHooks = []))
            .push(directiveIndex, wrappedOnChanges);
    }
    if (ngOnInit) {
        (tView.preOrderHooks || (tView.preOrderHooks = [])).push(0 - directiveIndex, ngOnInit);
    }
    if (ngDoCheck) {
        (tView.preOrderHooks || (tView.preOrderHooks = [])).push(directiveIndex, ngDoCheck);
        (tView.preOrderCheckHooks || (tView.preOrderCheckHooks = [])).push(directiveIndex, ngDoCheck);
    }
}
/**
 *
 * Loops through the directives on the provided `tNode` and queues hooks to be
 * run that are not initialization hooks.
 *
 * Should be executed during `elementEnd()` and similar to
 * preserve hook execution order. Content, view, and destroy hooks for projected
 * components and directives must be called *before* their hosts.
 *
 * Sets up the content, view, and destroy hooks on the provided `tView`,
 * see {@link HookData} for details about the data structure.
 *
 * NOTE: This does not set up `onChanges`, `onInit` or `doCheck`, those are set up
 * separately at `elementStart`.
 *
 * @param tView The current TView
 * @param tNode The TNode whose directives are to be searched for hooks to queue
 */
export function registerPostOrderHooks(tView, tNode) {
    ngDevMode && assertFirstCreatePass(tView);
    // It's necessary to loop through the directives at elementEnd() (rather than processing in
    // directiveCreate) so we can preserve the current hook order. Content, view, and destroy
    // hooks for projected components and directives must be called *before* their hosts.
    for (let i = tNode.directiveStart, end = tNode.directiveEnd; i < end; i++) {
        const directiveDef = tView.data[i];
        ngDevMode && assertDefined(directiveDef, 'Expecting DirectiveDef');
        const lifecycleHooks = directiveDef.type.prototype;
        const { ngAfterContentInit, ngAfterContentChecked, ngAfterViewInit, ngAfterViewChecked, ngOnDestroy } = lifecycleHooks;
        if (ngAfterContentInit) {
            (tView.contentHooks || (tView.contentHooks = [])).push(-i, ngAfterContentInit);
        }
        if (ngAfterContentChecked) {
            (tView.contentHooks || (tView.contentHooks = [])).push(i, ngAfterContentChecked);
            (tView.contentCheckHooks || (tView.contentCheckHooks = [])).push(i, ngAfterContentChecked);
        }
        if (ngAfterViewInit) {
            (tView.viewHooks || (tView.viewHooks = [])).push(-i, ngAfterViewInit);
        }
        if (ngAfterViewChecked) {
            (tView.viewHooks || (tView.viewHooks = [])).push(i, ngAfterViewChecked);
            (tView.viewCheckHooks || (tView.viewCheckHooks = [])).push(i, ngAfterViewChecked);
        }
        if (ngOnDestroy != null) {
            (tView.destroyHooks || (tView.destroyHooks = [])).push(i, ngOnDestroy);
        }
    }
}
/**
 * Executing hooks requires complex logic as we need to deal with 2 constraints.
 *
 * 1. Init hooks (ngOnInit, ngAfterContentInit, ngAfterViewInit) must all be executed once and only
 * once, across many change detection cycles. This must be true even if some hooks throw, or if
 * some recursively trigger a change detection cycle.
 * To solve that, it is required to track the state of the execution of these init hooks.
 * This is done by storing and maintaining flags in the view: the {@link InitPhaseState},
 * and the index within that phase. They can be seen as a cursor in the following structure:
 * [[onInit1, onInit2], [afterContentInit1], [afterViewInit1, afterViewInit2, afterViewInit3]]
 * They are are stored as flags in LView[FLAGS].
 *
 * 2. Pre-order hooks can be executed in batches, because of the select instruction.
 * To be able to pause and resume their execution, we also need some state about the hook's array
 * that is being processed:
 * - the index of the next hook to be executed
 * - the number of init hooks already found in the processed part of the  array
 * They are are stored as flags in LView[PREORDER_HOOK_FLAGS].
 */
/**
 * Executes pre-order check hooks ( OnChanges, DoChanges) given a view where all the init hooks were
 * executed once. This is a light version of executeInitAndCheckPreOrderHooks where we can skip read
 * / write of the init-hooks related flags.
 * @param lView The LView where hooks are defined
 * @param hooks Hooks to be run
 * @param nodeIndex 3 cases depending on the value:
 * - undefined: all hooks from the array should be executed (post-order case)
 * - null: execute hooks only from the saved index until the end of the array (pre-order case, when
 * flushing the remaining hooks)
 * - number: execute hooks only from the saved index until that node index exclusive (pre-order
 * case, when executing select(number))
 */
export function executeCheckHooks(lView, hooks, nodeIndex) {
    callHooks(lView, hooks, 3 /* InitPhaseCompleted */, nodeIndex);
}
/**
 * Executes post-order init and check hooks (one of AfterContentInit, AfterContentChecked,
 * AfterViewInit, AfterViewChecked) given a view where there are pending init hooks to be executed.
 * @param lView The LView where hooks are defined
 * @param hooks Hooks to be run
 * @param initPhase A phase for which hooks should be run
 * @param nodeIndex 3 cases depending on the value:
 * - undefined: all hooks from the array should be executed (post-order case)
 * - null: execute hooks only from the saved index until the end of the array (pre-order case, when
 * flushing the remaining hooks)
 * - number: execute hooks only from the saved index until that node index exclusive (pre-order
 * case, when executing select(number))
 */
export function executeInitAndCheckHooks(lView, hooks, initPhase, nodeIndex) {
    ngDevMode &&
        assertNotEqual(initPhase, 3 /* InitPhaseCompleted */, 'Init pre-order hooks should not be called more than once');
    if ((lView[FLAGS] & 3 /* InitPhaseStateMask */) === initPhase) {
        callHooks(lView, hooks, initPhase, nodeIndex);
    }
}
export function incrementInitPhaseFlags(lView, initPhase) {
    ngDevMode &&
        assertNotEqual(initPhase, 3 /* InitPhaseCompleted */, 'Init hooks phase should not be incremented after all init hooks have been run.');
    let flags = lView[FLAGS];
    if ((flags & 3 /* InitPhaseStateMask */) === initPhase) {
        flags &= 2047 /* IndexWithinInitPhaseReset */;
        flags += 1 /* InitPhaseStateIncrementer */;
        lView[FLAGS] = flags;
    }
}
/**
 * Calls lifecycle hooks with their contexts, skipping init hooks if it's not
 * the first LView pass
 *
 * @param currentView The current view
 * @param arr The array in which the hooks are found
 * @param initPhaseState the current state of the init phase
 * @param currentNodeIndex 3 cases depending on the value:
 * - undefined: all hooks from the array should be executed (post-order case)
 * - null: execute hooks only from the saved index until the end of the array (pre-order case, when
 * flushing the remaining hooks)
 * - number: execute hooks only from the saved index until that node index exclusive (pre-order
 * case, when executing select(number))
 */
function callHooks(currentView, arr, initPhase, currentNodeIndex) {
    ngDevMode &&
        assertEqual(isInCheckNoChangesMode(), false, 'Hooks should never be run when in check no changes mode.');
    const startIndex = currentNodeIndex !== undefined ?
        (currentView[PREORDER_HOOK_FLAGS] & 65535 /* IndexOfTheNextPreOrderHookMaskMask */) :
        0;
    const nodeIndexLimit = currentNodeIndex != null ? currentNodeIndex : -1;
    const max = arr.length - 1; // Stop the loop at length - 1, because we look for the hook at i + 1
    let lastNodeIndexFound = 0;
    for (let i = startIndex; i < max; i++) {
        const hook = arr[i + 1];
        if (typeof hook === 'number') {
            lastNodeIndexFound = arr[i];
            if (currentNodeIndex != null && lastNodeIndexFound >= currentNodeIndex) {
                break;
            }
        }
        else {
            const isInitHook = arr[i] < 0;
            if (isInitHook)
                currentView[PREORDER_HOOK_FLAGS] += 65536 /* NumberOfInitHooksCalledIncrementer */;
            if (lastNodeIndexFound < nodeIndexLimit || nodeIndexLimit == -1) {
                callHook(currentView, initPhase, arr, i);
                currentView[PREORDER_HOOK_FLAGS] =
                    (currentView[PREORDER_HOOK_FLAGS] & 4294901760 /* NumberOfInitHooksCalledMask */) + i +
                        2;
            }
            i++;
        }
    }
}
/**
 * Execute one hook against the current `LView`.
 *
 * @param currentView The current view
 * @param initPhaseState the current state of the init phase
 * @param arr The array in which the hooks are found
 * @param i The current index within the hook data array
 */
function callHook(currentView, initPhase, arr, i) {
    const isInitHook = arr[i] < 0;
    const hook = arr[i + 1];
    const directiveIndex = isInitHook ? -arr[i] : arr[i];
    const directive = currentView[directiveIndex];
    if (isInitHook) {
        const indexWithintInitPhase = currentView[FLAGS] >> 11 /* IndexWithinInitPhaseShift */;
        // The init phase state must be always checked here as it may have been recursively updated.
        if (indexWithintInitPhase <
            (currentView[PREORDER_HOOK_FLAGS] >> 16 /* NumberOfInitHooksCalledShift */) &&
            (currentView[FLAGS] & 3 /* InitPhaseStateMask */) === initPhase) {
            currentView[FLAGS] += 2048 /* IndexWithinInitPhaseIncrementer */;
            hook.call(directive);
        }
    }
    else {
        hook.call(directive);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaG9va3MuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL2hvb2tzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUdILE9BQU8sRUFBQyxhQUFhLEVBQUUsV0FBVyxFQUFFLGNBQWMsRUFBQyxNQUFNLGdCQUFnQixDQUFDO0FBQzFFLE9BQU8sRUFBQyxxQkFBcUIsRUFBQyxNQUFNLFVBQVUsQ0FBQztBQUMvQyxPQUFPLEVBQUMsc0JBQXNCLEVBQUMsTUFBTSxpQ0FBaUMsQ0FBQztBQUd2RSxPQUFPLEVBQUMsS0FBSyxFQUErQyxtQkFBbUIsRUFBMkIsTUFBTSxtQkFBbUIsQ0FBQztBQUNwSSxPQUFPLEVBQUMsc0JBQXNCLEVBQUMsTUFBTSxTQUFTLENBQUM7QUFJL0M7Ozs7Ozs7Ozs7O0dBV0c7QUFDSCxNQUFNLFVBQVUscUJBQXFCLENBQ2pDLGNBQXNCLEVBQUUsWUFBK0IsRUFBRSxLQUFZO0lBQ3ZFLFNBQVMsSUFBSSxxQkFBcUIsQ0FBQyxLQUFLLENBQUMsQ0FBQztJQUMxQyxNQUFNLEVBQUMsV0FBVyxFQUFFLFFBQVEsRUFBRSxTQUFTLEVBQUMsR0FDcEMsWUFBWSxDQUFDLElBQUksQ0FBQyxTQUF5QyxDQUFDO0lBRWhFLElBQUksV0FBbUMsRUFBRTtRQUN2QyxNQUFNLGdCQUFnQixHQUFHLHNCQUFzQixDQUFDLFlBQVksQ0FBQyxDQUFDO1FBQzlELENBQUMsS0FBSyxDQUFDLGFBQWEsSUFBSSxDQUFDLEtBQUssQ0FBQyxhQUFhLEdBQUcsRUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsY0FBYyxFQUFFLGdCQUFnQixDQUFDLENBQUM7UUFDM0YsQ0FBQyxLQUFLLENBQUMsa0JBQWtCLElBQUksQ0FBQyxLQUFLLENBQUMsa0JBQWtCLEdBQUcsRUFBRSxDQUFDLENBQUM7YUFDeEQsSUFBSSxDQUFDLGNBQWMsRUFBRSxnQkFBZ0Is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ICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7QWZ0ZXJDb250ZW50Q2hlY2tlZCwgQWZ0ZXJDb250ZW50SW5pdCwgQWZ0ZXJWaWV3Q2hlY2tlZCwgQWZ0ZXJWaWV3SW5pdCwgRG9DaGVjaywgT25DaGFuZ2VzLCBPbkRlc3Ryb3ksIE9uSW5pdH0gZnJvbSAnLi4vaW50ZXJmYWNlL2xpZmVjeWNsZV9ob29rcyc7XG5pbXBvcnQge2Fzc2VydERlZmluZWQsIGFzc2VydEVxdWFsLCBhc3NlcnROb3RFcXVhbH0gZnJvbSAnLi4vdXRpbC9hc3NlcnQnO1xuaW1wb3J0IHthc3NlcnRGaXJzdENyZWF0ZVBhc3N9IGZyb20gJy4vYXNzZXJ0JztcbmltcG9ydCB7TmdPbkNoYW5nZXNGZWF0dXJlSW1wbH0gZnJvbSAnLi9mZWF0dXJlcy9uZ19vbmNoYW5nZXNfZmVhdHVyZSc7XG5pbXBvcnQge0RpcmVjdGl2ZURlZn0gZnJvbSAnLi9pbnRlcmZhY2VzL2RlZmluaXRpb24nO1xuaW1wb3J0IHtUTm9kZX0gZnJvbSAnLi9pbnRlcmZhY2VzL25vZGUnO1xuaW1wb3J0IHtGTEFHUywgSG9va0RhdGEsIEluaXRQaGFzZVN0YXRlLCBMVmlldywgTFZpZXdGbGFncywgUFJFT1JERVJfSE9PS19GTEFHUywgUHJlT3JkZXJIb29rRmxhZ3MsIFRWaWV3fSBmcm9tICcuL2ludGVyZmFjZXMvdmlldyc7XG5pbXBvcnQge2lzSW5DaGVja05vQ2hhbmdlc01vZGV9IGZyb20gJy4vc3RhdGUnO1xuXG5cblxuLyoqXG4gKiBBZGRzIGFsbCBkaXJlY3RpdmUgbGlmZWN5Y2xlIGhvb2tzIGZyb20gdGhlIGdpdmVuIGBEaXJlY3RpdmVEZWZgIHRvIHRoZSBnaXZlbiBgVFZpZXdgLlxuICpcbiAqIE11c3QgYmUgcnVuICpvbmx5KiBvbiB0aGUgZmlyc3QgdGVtcGxhdGUgcGFzcy5cbiAqXG4gKiBTZXRzIHVwIHRoZSBwcmUtb3JkZXIgaG9va3Mgb24gdGhlIHByb3ZpZGVkIGB0Vmlld2AsXG4gKiBzZWUge0BsaW5rIEhvb2tEYXRhfSBmb3IgZGV0YWlscyBhYm91dCB0aGUgZGF0YSBzdHJ1Y3R1cmUuXG4gKlxuICogQHBhcmFtIGRpcmVjdGl2ZUluZGV4IFRoZSBpbmRleCBvZiB0aGUgZGlyZWN0aXZlIGluIExWaWV3XG4gKiBAcGFyYW0gZGlyZWN0aXZlRGVmIFRoZSBkZWZpbml0aW9uIGNvbnRhaW5pbmcgdGhlIGhvb2tzIHRvIHNldHVwIGluIHRWaWV3XG4gKiBAcGFyYW0gdFZpZXcgVGhlIGN1cnJlbnQgVFZpZXdcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHJlZ2lzdGVyUHJlT3JkZXJIb29rcyhcbiAgICBkaXJlY3RpdmVJbmRleDogbnVtYmVyLCBkaXJlY3RpdmVEZWY6IERpcmVjdGl2ZURlZjxhbnk+LCB0VmlldzogVFZpZXcpOiB2b2lkIHtcbiAgbmdEZXZNb2RlICYmIGFzc2VydEZpcnN0Q3JlYXRlUGFzcyh0Vmlldyk7XG4gIGNvbnN0IHtuZ09uQ2hhbmdlcywgbmdPbkluaXQsIG5nRG9DaGVja30gPVxuICAgICAgZGlyZWN0aXZlRGVmLnR5cGUucHJvdG90eXBlIGFzIE9uQ2hhbmdlcyAmIE9uSW5pdCAmIERvQ2hlY2s7XG5cbiAgaWYgKG5nT25DaGFuZ2VzIGFzIEZ1bmN0aW9uIHwgdW5kZWZpbmVkKSB7XG4gICAgY29uc3Qgd3JhcHBlZE9uQ2hhbmdlcyA9IE5nT25DaGFuZ2VzRmVhdHVyZUltcGwoZGlyZWN0aXZlRGVmKTtcbiAgICAodFZpZXcucHJlT3JkZXJIb29rcyB8fCAodFZpZXcucHJlT3JkZXJIb29rcyA9IFtdKSkucHVzaChkaXJlY3RpdmVJbmRleCwgd3JhcHBlZE9uQ2hhbmdlcyk7XG4gICAgKHRWaWV3LnByZU9yZGVyQ2hlY2tIb29rcyB8fCAodFZpZXcucHJlT3JkZXJDaGVja0hvb2tzID0gW10pKVxuICAgICAgICAucHVzaChkaXJlY3RpdmVJbmRleCwgd3JhcHBlZE9uQ2hhbmdlcyk7XG4gIH1cblxuICBpZiAobmdPbkluaXQpIHtcbiAgICAodFZpZXcucHJlT3JkZXJIb29rcyB8fCAodFZpZXcucHJlT3JkZXJIb29rcyA9IFtdKSkucHVzaCgwIC0gZGlyZWN0aXZlSW5kZXgsIG5nT25Jbml0KTtcbiAgfVxuXG4gIGlmIChuZ0RvQ2hlY2spIHtcbiAgICAodFZpZXcucHJlT3JkZXJIb29rcyB8fCAodFZpZXcucHJlT3JkZXJIb29rcyA9IFtdKSkucHVzaChkaXJlY3RpdmVJbmRleCwgbmdEb0NoZWNrKTtcbiAgICAodFZpZXcucHJlT3JkZXJDaGVja0hvb2tzIHx8ICh0Vmlldy5wcmVPcmRlckNoZWNrSG9va3MgPSBbXSkpLnB1c2goZGlyZWN0aXZlSW5kZXgsIG5nRG9DaGVjayk7XG4gIH1cbn1cblxuLyoqXG4gKlxuICogTG9vcHMgdGhyb3VnaCB0aGUgZGlyZWN0aXZlcyBvbiB0aGUgcHJvdmlkZWQgYHROb2RlYCBhbmQgcXVldWVzIGhvb2tzIHRvIGJlXG4gKiBydW4gdGhhdCBhcmUgbm90IGluaXRpYWxpemF0aW9uIGhvb2tzLlxuICpcbiAqIFNob3VsZCBiZSBleGVjdXRlZCBkdXJpbmcgYGVsZW1lbnRFbmQoKWAgYW5kIHNpbWlsYXIgdG9cbiAqIHByZXNlcnZlIGhvb2sgZXhlY3V0aW9uIG9yZGVyLiBDb250ZW50LCB2aWV3LCBhbmQgZGVzdHJveSBob29rcyBmb3IgcHJvamVjdGVkXG4gKiBjb21wb25lbnRzIGFuZCBkaXJlY3RpdmVzIG11c3QgYmUgY2FsbGVkICpiZWZvcmUqIHRoZWlyIGhvc3RzLlxuICpcbiAqIFNldHMgdXAgdGhlIGNvbnRlbnQsIHZpZXcsIGFuZCBkZXN0cm95IGhvb2tzIG9uIHRoZSBwcm92aWRlZCBgdFZpZXdgLFxuICogc2VlIHtAbGluayBIb29rRGF0YX0gZm9yIGRldGFpbHMgYWJvdXQgdGhlIGRhdGEgc3RydWN0dXJlLlxuICpcbiAqIE5PVEU6IFRoaXMgZG9lcyBub3Qgc2V0IHVwIGBvbkNoYW5nZXNgLCBgb25Jbml0YCBvciBgZG9DaGVja2AsIHRob3NlIGFyZSBzZXQgdXBcbiAqIHNlcGFyYXRlbHkgYXQgYGVsZW1lbnRTdGFydGAuXG4gKlxuICogQHBhcmFtIHRWaWV3IFRoZSBjdXJyZW50IFRWaWV3XG4gKiBAcGFyYW0gdE5vZGUgVGhlIFROb2RlIHdob3NlIGRpcmVjdGl2ZXMgYXJlIHRvIGJlIHNlYXJjaGVkIGZvciBob29rcyB0byBxdWV1ZVxuICovXG5leHBvcnQgZnVuY3Rpb24gcmVnaXN0ZXJQb3N0T3JkZXJIb29rcyh0VmlldzogVFZpZXcsIHROb2RlOiBUTm9kZSk6IHZvaWQge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0Rmlyc3RDcmVhdGVQYXNzKHRWaWV3KTtcbiAgLy8gSXQncyBuZWNlc3NhcnkgdG8gbG9vcCB0aHJvdWdoIHRoZSBkaXJlY3RpdmVzIGF0IGVsZW1lbnRFbmQoKSAocmF0aGVyIHRoYW4gcHJvY2Vzc2luZyBpblxuICAvLyBkaXJlY3RpdmVDcmVhdGUpIHNvIHdlIGNhbiBwcmVzZXJ2ZSB0aGUgY3VycmVudCBob29rIG9yZGVyLiBDb250ZW50LCB2aWV3LCBhbmQgZGVzdHJveVxuICAvLyBob29rcyBmb3IgcHJvamVjdGVkIGNvbXBvbmVudHMgYW5kIGRpcmVjdGl2ZXMgbXVzdCBiZSBjYWxsZWQgKmJlZm9yZSogdGhlaXIgaG9zdHMuXG4gIGZvciAobGV0IGkgPSB0Tm9kZS5kaXJlY3RpdmVTdGFydCwgZW5kID0gdE5vZGUuZGlyZWN0aXZlRW5kOyBpIDwgZW5kOyBpKyspIHtcbiAgICBjb25zdCBkaXJlY3RpdmVEZWYgPSB0Vmlldy5kYXRhW2ldIGFzIERpcmVjdGl2ZURlZjxhbnk+O1xuICAgIG5nRGV2TW9kZSAmJiBhc3NlcnREZWZpbmVkKGRpcmVjdGl2ZURlZiwgJ0V4cGVjdGluZyBEaXJlY3RpdmVEZWYnKTtcbiAgICBjb25zdCBsaWZlY3ljbGVIb29rczogQWZ0ZXJDb250ZW50SW5pdCZBZnRlckNvbnRlbnRDaGVja2VkJkFmdGVyVmlld0luaXQmQWZ0ZXJWaWV3Q2hlY2tlZCZcbiAgICAgICAgT25EZXN0cm95ID0gZGlyZWN0aXZlRGVmLnR5cGUucHJvdG90eXBlO1xuICAgIGNvbnN0IHtcbiAgICAgIG5nQWZ0ZXJDb250ZW50SW5pdCxcbiAgICAgIG5nQWZ0ZXJDb250ZW50Q2hlY2tlZCxcbiAgICAgIG5nQWZ0ZXJWaWV3SW5pdCxcbiAgICAgIG5nQWZ0ZXJWaWV3Q2hlY2tlZCxcbiAgICAgIG5nT25EZXN0cm95XG4gICAgfSA9IGxpZmVjeWNsZUhvb2tzO1xuXG4gICAgaWYgKG5nQWZ0ZXJDb250ZW50SW5pdCkge1xuICAgICAgKHRWaWV3LmNvbnRlbnRIb29rcyB8fCAodFZpZXcuY29udGVudEhvb2tzID0gW10pKS5wdXNoKC1pLCBuZ0FmdGVyQ29udGVudEluaXQpO1xuICAgIH1cblxuICAgIGlmIChuZ0FmdGVyQ29udGVudENoZWNrZWQpIHtcbiAgICAgICh0Vmlldy5jb250ZW50SG9va3MgfHwgKHRWaWV3LmNvbnRlbnRIb29rcyA9IFtdKSkucHVzaChpLCBuZ0FmdGVyQ29udGVudENoZWNrZWQpO1xuICAgICAgKHRWaWV3LmNvbnRlbnRDaGVja0hvb2tzIHx8ICh0Vmlldy5jb250ZW50Q2hlY2tIb29rcyA9IFtdKSkucHVzaChpLCBuZ0FmdGVyQ29udGVudENoZWNrZWQpO1xuICAgIH1cblxuICAgIGlmIChuZ0FmdGVyVmlld0luaXQpIHtcbiAgICAgICh0Vmlldy52aWV3SG9va3MgfHwgKHRWaWV3LnZpZXdIb29rcyA9IFtdKSkucHVzaCgtaSwgbmdBZnRlclZpZXdJbml0KTtcbiAgICB9XG5cbiAgICBpZiAobmdBZnRlclZpZXdDaGVja2VkKSB7XG4gICAgICAodFZpZXcudmlld0hvb2tzIHx8ICh0Vmlldy52aWV3SG9va3MgPSBbXSkpLnB1c2goaSwgbmdBZnRlclZpZXdDaGVja2VkKTtcbiAgICAgICh0Vmlldy52aWV3Q2hlY2tIb29rcyB8fCAodFZpZXcudmlld0NoZWNrSG9va3MgPSBbXSkpLnB1c2goaSwgbmdBZnRlclZpZXdDaGVja2VkKTtcbiAgICB9XG5cbiAgICBpZiAobmdPbkRlc3Ryb3kgIT0gbnVsbCkge1xuICAgICAgKHRWaWV3LmRlc3Ryb3lIb29rcyB8fCAodFZpZXcuZGVzdHJveUhvb2tzID0gW10pKS5wdXNoKGksIG5nT25EZXN0cm95KTtcbiAgICB9XG4gIH1cbn1cblxuLyoqXG4gKiBFeGVjdXRpbmcgaG9va3MgcmVxdWlyZXMgY29tcGxleCBsb2dpYyBhcyB3ZSBuZWVkIHRvIGRlYWwgd2l0aCAyIGNvbnN0cmFpbnRzLlxuICpcbiAqIDEuIEluaXQgaG9va3MgKG5nT25Jbml0LCBuZ0FmdGVyQ29udGVudEluaXQsIG5nQWZ0ZXJWaWV3SW5pdCkgbXVzdCBhbGwgYmUgZXhlY3V0ZWQgb25jZSBhbmQgb25seVxuICogb25jZSwgYWNyb3NzIG1hbnkgY2hhbmdlIGRldGVjdGlvbiBjeWNsZXMuIFRoaXMgbXVzdCBiZSB0cnVlIGV2ZW4gaWYgc29tZSBob29rcyB0aHJvdywgb3IgaWZcbiAqIHNvbWUgcmVjdXJzaXZlbHkgdHJpZ2dlciBhIGNoYW5nZSBkZXRlY3Rpb24gY3ljbGUuXG4gKiBUbyBzb2x2ZSB0aGF0LCBpdCBpcyByZXF1aXJlZCB0byB0cmFjayB0aGUgc3RhdGUgb2YgdGhlIGV4ZWN1dGlvbiBvZiB0aGVzZSBpbml0IGhvb2tzLlxuICogVGhpcyBpcyBkb25lIGJ5IHN0b3JpbmcgYW5kIG1haW50YWluaW5nIGZsYWdzIGluIHRoZSB2aWV3OiB0aGUge0BsaW5rIEluaXRQaGFzZVN0YXRlfSxcbiAqIGFuZCB0aGUgaW5kZXggd2l0aGluIHRoYXQgcGhhc2UuIFRoZXkgY2FuIGJlIHNlZW4gYXMgYSBjdXJzb3IgaW4gdGhlIGZvbGxvd2luZyBzdHJ1Y3R1cmU6XG4gKiBbW29uSW5pdDEsIG9uSW5pdDJdLCBbYWZ0ZXJDb250ZW50SW5pdDFdLCBbYWZ0ZXJWaWV3SW5pdDEsIGFmdGVyVmlld0luaXQyLCBhZnRlclZpZXdJbml0M11dXG4gKiBUaGV5IGFyZSBhcmUgc3RvcmVkIGFzIGZsYWdzIGluIExWaWV3W0ZMQUdTXS5cbiAqXG4gKiAyLiBQcmUtb3JkZXIgaG9va3MgY2FuIGJlIGV4ZWN1dGVkIGluIGJhdGNoZXMsIGJlY2F1c2Ugb2YgdGhlIHNlbGVjdCBpbnN0cnVjdGlvbi5cbiAqIFRvIGJlIGFibGUgdG8gcGF1c2UgYW5kIHJlc3VtZSB0aGVpciBleGVjdXRpb24sIHdlIGFsc28gbmVlZCBzb21lIHN0YXRlIGFib3V0IHRoZSBob29rJ3MgYXJyYXlcbiAqIHRoYXQgaXMgYmVpbmcgcHJvY2Vzc2VkOlxuICogLSB0aGUgaW5kZXggb2YgdGhlIG5leHQgaG9vayB0byBiZSBleGVjdXRlZFxuICogLSB0aGUgbnVtYmVyIG9mIGluaXQgaG9va3MgYWxyZWFkeSBmb3VuZCBpbiB0aGUgcHJvY2Vzc2VkIHBhcnQgb2YgdGhlICBhcnJheVxuICogVGhleSBhcmUgYXJlIHN0b3JlZCBhcyBmbGFncyBpbiBMVmlld1tQUkVPUkRFUl9IT09LX0ZMQUdTXS5cbiAqL1xuXG5cbi8qKlxuICogRXhlY3V0ZXMgcHJlLW9yZGVyIGNoZWNrIGhvb2tzICggT25DaGFuZ2VzLCBEb0NoYW5nZXMpIGdpdmVuIGEgdmlldyB3aGVyZSBhbGwgdGhlIGluaXQgaG9va3Mgd2VyZVxuICogZXhlY3V0ZWQgb25jZS4gVGhpcyBpcyBhIGxpZ2h0IHZlcnNpb24gb2YgZXhlY3V0ZUluaXRBbmRDaGVja1ByZU9yZGVySG9va3Mgd2hlcmUgd2UgY2FuIHNraXAgcmVhZFxuICogLyB3cml0ZSBvZiB0aGUgaW5pdC1ob29rcyByZWxhdGVkIGZsYWdzLlxuICogQHBhcmFtIGxWaWV3IFRoZSBMVmlldyB3aGVyZSBob29rcyBhcmUgZGVmaW5lZFxuICogQHBhcmFtIGhvb2tzIEhvb2tzIHRvIGJlIHJ1blxuICogQHBhcmFtIG5vZGVJbmRleCAzIGNhc2VzIGRlcGVuZGluZyBvbiB0aGUgdmFsdWU6XG4gKiAtIHVuZGVmaW5lZDogYWxsIGhvb2tzIGZyb20gdGhlIGFycmF5IHNob3VsZCBiZSBleGVjdXRlZCAocG9zdC1vcmRlciBjYXNlKVxuICogLSBudWxsOiBleGVjdXRlIGhvb2tzIG9ubHkgZnJvbSB0aGUgc2F2ZWQgaW5kZXggdW50aWwgdGhlIGVuZCBvZiB0aGUgYXJyYXkgKHByZS1vcmRlciBjYXNlLCB3aGVuXG4gKiBmbHVzaGluZyB0aGUgcmVtYWluaW5nIGhvb2tzKVxuICogLSBudW1iZXI6IGV4ZWN1dGUgaG9va3Mgb25seSBmcm9tIHRoZSBzYXZlZCBpbmRleCB1bnRpbCB0aGF0IG5vZGUgaW5kZXggZXhjbHVzaXZlIChwcmUtb3JkZXJcbiAqIGNhc2UsIHdoZW4gZXhlY3V0aW5nIHNlbGVjdChudW1iZXIpKVxuICovXG5leHBvcnQgZnVuY3Rpb24gZXhlY3V0ZUNoZWNrSG9va3MobFZpZXc6IExWaWV3LCBob29rczogSG9va0RhdGEsIG5vZGVJbmRleD86IG51bWJlcnxudWxsKSB7XG4gIGNhbGxIb29rcyhsVmlldywgaG9va3MsIEluaXRQaGFzZVN0YXRlLkluaXRQaGFzZUNvbXBsZXRlZCwgbm9kZUluZGV4KTtcbn1cblxuLyoqXG4gKiBFeGVjdXRlcyBwb3N0LW9yZGVyIGluaXQgYW5kIGNoZWNrIGhvb2tzIChvbmUgb2YgQWZ0ZXJDb250ZW50SW5pdCwgQWZ0ZXJDb250ZW50Q2hlY2tlZCxcbiAqIEFmdGVyVmlld0luaXQsIEFmdGVyVmlld0NoZWNrZWQpIGdpdmVuIGEgdmlldyB3aGVyZSB0aGVyZSBhcmUgcGVuZGluZyBpbml0IGhvb2tzIHRvIGJlIGV4ZWN1dGVkLlxuICogQHBhcmFtIGxWaWV3IFRoZSBMVmlldyB3aGVyZSBob29rcyBhcmUgZGVmaW5lZFxuICogQHBhcmFtIGhvb2tzIEhvb2tzIHRvIGJlIHJ1blxuICogQHBhcmFtIGluaXRQaGFzZSBBIHBoYXNlIGZvciB3aGljaCBob29rcyBzaG91bGQgYmUgcnVuXG4gKiBAcGFyYW0gbm9kZUluZGV4IDMgY2FzZXMgZGVwZW5kaW5nIG9uIHRoZSB2YWx1ZTpcbiAqIC0gdW5kZWZpbmVkOiBhbGwgaG9va3MgZnJvbSB0aGUgYXJyYXkgc2hvdWxkIGJlIGV4ZWN1dGVkIChwb3N0LW9yZGVyIGNhc2UpXG4gKiAtIG51bGw6IGV4ZWN1dGUgaG9va3Mgb25seSBmcm9tIHRoZSBzYXZlZCBpbmRleCB1bnRpbCB0aGUgZW5kIG9mIHRoZSBhcnJheSAocHJlLW9yZGVyIGNhc2UsIHdoZW5cbiAqIGZsdXNoaW5nIHRoZSByZW1haW5pbmcgaG9va3MpXG4gKiAtIG51bWJlcjogZXhlY3V0ZSBob29rcyBvbmx5IGZyb20gdGhlIHNhdmVkIGluZGV4IHVudGlsIHRoYXQgbm9kZSBpbmRleCBleGNsdXNpdmUgKHByZS1vcmRlclxuICogY2FzZSwgd2hlbiBleGVjdXRpbmcgc2VsZWN0KG51bWJlcikpXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBleGVjdXRlSW5pdEFuZENoZWNrSG9va3MoXG4gICAgbFZpZXc6IExWaWV3LCBob29rczogSG9va0RhdGEsIGluaXRQaGFzZTogSW5pdFBoYXNlU3RhdGUsIG5vZGVJbmRleD86IG51bWJlcnxudWxsKSB7XG4gIG5nRGV2TW9kZSAmJlxuICAgICAgYXNzZXJ0Tm90RXF1YWwoXG4gICAgICAgICAgaW5pdFBoYXNlLCBJbml0UGhhc2VTdGF0ZS5Jbml0UGhhc2VDb21wbGV0ZWQsXG4gICAgICAgICAgJ0luaXQgcHJlLW9yZGVyIGhvb2tzIHNob3VsZCBub3QgYmUgY2FsbGVkIG1vcmUgdGhhbiBvbmNlJyk7XG4gIGlmICgobFZpZXdbRkxBR1NdICYgTFZpZXdGbGFncy5Jbml0UGhhc2VTdGF0ZU1hc2spID09PSBpbml0UGhhc2UpIHtcbiAgICBjYWxsSG9va3MobFZpZXcsIGhvb2tzLCBpbml0UGhhc2UsIG5vZGVJbmRleCk7XG4gIH1cbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGluY3JlbWVudEluaXRQaGFzZUZsYWdzKGxWaWV3OiBMVmlldywgaW5pdFBoYXNlOiBJbml0UGhhc2VTdGF0ZSk6IHZvaWQge1xuICBuZ0Rldk1vZGUgJiZcbiAgICAgIGFzc2VydE5vdEVxdWFsKFxuICAgICAgICAgIGluaXRQaGFzZSwgSW5pdFBoYXNlU3RhdGUuSW5pdFBoYXNlQ29tcGxldGVkLFxuICAgICAgICAgICdJbml0IGhvb2tzIHBoYXNlIHNob3VsZCBub3QgYmUgaW5jcmVtZW50ZWQgYWZ0ZXIgYWxsIGluaXQgaG9va3MgaGF2ZSBiZWVuIHJ1bi4nKTtcbiAgbGV0IGZsYWdzID0gbFZpZXdbRkxBR1NdO1xuICBpZiAoKGZsYWdzICYgTFZpZXdGbGFncy5Jbml0UGhhc2VTdGF0ZU1hc2spID09PSBpbml0UGhhc2UpIHtcbiAgICBmbGFncyAmPSBMVmlld0ZsYWdzLkluZGV4V2l0aGluSW5pdFBoYXNlUmVzZXQ7XG4gICAgZmxhZ3MgKz0gTFZpZXdGbGFncy5Jbml0UGhhc2VTdGF0ZUluY3JlbWVudGVyO1xuICAgIGxWaWV3W0ZMQUdTXSA9IGZsYWdzO1xuICB9XG59XG5cbi8qKlxuICogQ2FsbHMgbGlmZWN5Y2xlIGhvb2tzIHdpdGggdGhlaXIgY29udGV4dHMsIHNraXBwaW5nIGluaXQgaG9va3MgaWYgaXQncyBub3RcbiAqIHRoZSBmaXJzdCBMVmlldyBwYXNzXG4gKlxuICogQHBhcmFtIGN1cnJlbnRWaWV3IFRoZSBjdXJyZW50IHZpZXdcbiAqIEBwYXJhbSBhcnIgVGhlIGFycmF5IGluIHdoaWNoIHRoZSBob29rcyBhcmUgZm91bmRcbiAqIEBwYXJhbSBpbml0UGhhc2VTdGF0ZSB0aGUgY3VycmVudCBzdGF0ZSBvZiB0aGUgaW5pdCBwaGFzZVxuICogQHBhcmFtIGN1cnJlbnROb2RlSW5kZXggMyBjYXNlcyBkZXBlbmRpbmcgb24gdGhlIHZhbHVlOlxuICogLSB1bmRlZmluZWQ6IGFsbCBob29rcyBmcm9tIHRoZSBhcnJheSBzaG91bGQgYmUgZXhlY3V0ZWQgKHBvc3Qtb3JkZXIgY2FzZSlcbiAqIC0gbnVsbDogZXhlY3V0ZSBob29rcyBvbmx5IGZyb20gdGhlIHNhdmVkIGluZGV4IHVudGlsIHRoZSBlbmQgb2YgdGhlIGFycmF5IChwcmUtb3JkZXIgY2FzZSwgd2hlblxuICogZmx1c2hpbmcgdGhlIHJlbWFpbmluZyBob29rcylcbiAqIC0gbnVtYmVyOiBleGVjdXRlIGhvb2tzIG9ubHkgZnJvbSB0aGUgc2F2ZWQgaW5kZXggdW50aWwgdGhhdCBub2RlIGluZGV4IGV4Y2x1c2l2ZSAocHJlLW9yZGVyXG4gKiBjYXNlLCB3aGVuIGV4ZWN1dGluZyBzZWxlY3QobnVtYmVyKSlcbiAqL1xuZnVuY3Rpb24gY2FsbEhvb2tzKFxuICAgIGN1cnJlbnRWaWV3OiBMVmlldywgYXJyOiBIb29rRGF0YSwgaW5pdFBoYXNlOiBJbml0UGhhc2VTdGF0ZSxcbiAgICBjdXJyZW50Tm9kZUluZGV4OiBudW1iZXJ8bnVsbHx1bmRlZmluZWQpOiB2b2lkIHtcbiAgbmdEZXZNb2RlICYmXG4gICAgICBhc3NlcnRFcXVhbChcbiAgICAgICAgICBpc0luQ2hlY2tOb0NoYW5nZXNNb2RlKCksIGZhbHNlLFxuICAgICAgICAgICdIb29rcyBzaG91bGQgbmV2ZXIgYmUgcnVuIHdoZW4gaW4gY2hlY2sgbm8gY2hhbmdlcyBtb2RlLicpO1xuICBjb25zdCBzdGFydEluZGV4ID0gY3VycmVudE5vZGVJbmRleCAhPT0gdW5kZWZpbmVkID9cbiAgICAgIChjdXJyZW50Vmlld1tQUkVPUkRFUl9IT09LX0ZMQUdTXSAmIFByZU9yZGVySG9va0ZsYWdzLkluZGV4T2ZUaGVOZXh0UHJlT3JkZXJIb29rTWFza01hc2spIDpcbiAgICAgIDA7XG4gIGNvbnN0IG5vZGVJbmRleExpbWl0ID0gY3VycmVudE5vZGVJbmRleCAhPSBudWxsID8gY3VycmVudE5vZGVJbmRleCA6IC0xO1xuICBjb25zdCBtYXggPSBhcnIubGVuZ3RoIC0gMTsgIC8vIFN0b3AgdGhlIGxvb3AgYXQgbGVuZ3RoIC0gMSwgYmVjYXVzZSB3ZSBsb29rIGZvciB0aGUgaG9vayBhdCBpICsgMVxuICBsZXQgbGFzdE5vZGVJbmRleEZvdW5kID0gMDtcbiAgZm9yIChsZXQgaSA9IHN0YXJ0SW5kZXg7IGkgPCBtYXg7IGkrKykge1xuICAgIGNvbnN0IGhvb2sgPSBhcnJbaSArIDFdIGFzIG51bWJlciB8ICgoKSA9PiB2b2lkKTtcbiAgICBpZiAodHlwZW9mIGhvb2sgPT09ICdudW1iZXInKSB7XG4gICAgICBsYXN0Tm9kZUluZGV4Rm91bmQgPSBhcnJbaV0gYXMgbnVtYmVyO1xuICAgICAgaWYgKGN1cnJlbnROb2RlSW5kZXggIT0gbnVsbCAmJiBsYXN0Tm9kZUluZGV4Rm91bmQgPj0gY3VycmVudE5vZGVJbmRleCkge1xuICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgY29uc3QgaXNJbml0SG9vayA9IGFycltpXSA8IDA7XG4gICAgICBpZiAoaXNJbml0SG9vaylcbiAgICAgICAgY3VycmVudFZpZXdbUFJFT1JERVJfSE9PS19GTEFHU10gKz0gUHJlT3JkZXJIb29rRmxhZ3MuTnVtYmVyT2ZJbml0SG9va3NDYWxsZWRJbmNyZW1lbnRlcjtcbiAgICAgIGlmIChsYXN0Tm9kZUluZGV4Rm91bmQgPCBub2RlSW5kZXhMaW1pdCB8fCBub2RlSW5kZXhMaW1pdCA9PSAtMSkge1xuICAgICAgICBjYWxsSG9vayhjdXJyZW50VmlldywgaW5pdFBoYXNlLCBhcnIsIGkpO1xuICAgICAgICBjdXJyZW50Vmlld1tQUkVPUkRFUl9IT09LX0ZMQUdTXSA9XG4gICAgICAgICAgICAoY3VycmVudFZpZXdbUFJFT1JERVJfSE9PS19GTEFHU10gJiBQcmVPcmRlckhvb2tGbGFncy5OdW1iZXJPZkluaXRIb29rc0NhbGxlZE1hc2spICsgaSArXG4gICAgICAgICAgICAyO1xuICAgICAgfVxuICAgICAgaSsrO1xuICAgIH1cbiAgfVxufVxuXG4vKipcbiAqIEV4ZWN1dGUgb25lIGhvb2sgYWdhaW5zdCB0aGUgY3VycmVudCBgTFZpZXdgLlxuICpcbiAqIEBwYXJhbSBjdXJyZW50VmlldyBUaGUgY3VycmVudCB2aWV3XG4gKiBAcGFyYW0gaW5pdFBoYXNlU3RhdGUgdGhlIGN1cnJlbnQgc3RhdGUgb2YgdGhlIGluaXQgcGhhc2VcbiAqIEBwYXJhbSBhcnIgVGhlIGFycmF5IGluIHdoaWNoIHRoZSBob29rcyBhcmUgZm91bmRcbiAqIEBwYXJhbSBpIFRoZSBjdXJyZW50IGluZGV4IHdpdGhpbiB0aGUgaG9vayBkYXRhIGFycmF5XG4gKi9cbmZ1bmN0aW9uIGNhbGxIb29rKGN1cnJlbnRWaWV3OiBMVmlldywgaW5pdFBoYXNlOiBJbml0UGhhc2VTdGF0ZSwgYXJyOiBIb29rRGF0YSwgaTogbnVtYmVyKSB7XG4gIGNvbnN0IGlzSW5pdEhvb2sgPSBhcnJbaV0gPCAwO1xuICBjb25zdCBob29rID0gYXJyW2kgKyAxXSBhcyAoKSA9PiB2b2lkO1xuICBjb25zdCBkaXJlY3RpdmVJbmRleCA9IGlzSW5pdEhvb2sgPyAtYXJyW2ldIDogYXJyW2ldIGFzIG51bWJlcjtcbiAgY29uc3QgZGlyZWN0aXZlID0gY3VycmVudFZpZXdbZGlyZWN0aXZlSW5kZXhdO1xuICBpZiAoaXNJbml0SG9vaykge1xuICAgIGNvbnN0IGluZGV4V2l0aGludEluaXRQaGFzZSA9IGN1cnJlbnRWaWV3W0ZMQUdTXSA+PiBMVmlld0ZsYWdzLkluZGV4V2l0aGluSW5pdFBoYXNlU2hpZnQ7XG4gICAgLy8gVGhlIGluaXQgcGhhc2Ugc3RhdGUgbXVzdCBiZSBhbHdheXMgY2hlY2tlZCBoZXJlIGFzIGl0IG1heSBoYXZlIGJlZW4gcmVjdXJzaXZlbHkgdXBkYXRlZC5cbiAgICBpZiAoaW5kZXhXaXRoaW50SW5pdFBoYXNlIDxcbiAgICAgICAgICAgIChjdXJyZW50Vmlld1tQUkVPUkRFUl9IT09LX0ZMQUdTXSA+PiBQcmVPcmRlckhvb2tGbGFncy5OdW1iZXJPZkluaXRIb29rc0NhbGxlZFNoaWZ0KSAmJlxuICAgICAgICAoY3VycmVudFZpZXdbRkxBR1NdICYgTFZpZXdGbGFncy5Jbml0UGhhc2VTdGF0ZU1hc2spID09PSBpbml0UGhhc2UpIHtcbiAgICAgIGN1cnJlbnRWaWV3W0ZMQUdTXSArPSBMVmlld0ZsYWdzLkluZGV4V2l0aGluSW5pdFBoYXNlSW5jcmVtZW50ZXI7XG4gICAgICBob29rLmNhbGwoZGlyZWN0aXZlKTtcbiAgICB9XG4gIH0gZWxzZSB7XG4gICAgaG9vay5jYWxsKGRpcmVjdGl2ZSk7XG4gIH1cbn1cbiJdfQ==