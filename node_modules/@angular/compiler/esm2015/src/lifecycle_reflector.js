/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export var LifecycleHooks;
(function (LifecycleHooks) {
    LifecycleHooks[LifecycleHooks["OnInit"] = 0] = "OnInit";
    LifecycleHooks[LifecycleHooks["OnDestroy"] = 1] = "OnDestroy";
    LifecycleHooks[LifecycleHooks["DoCheck"] = 2] = "DoCheck";
    LifecycleHooks[LifecycleHooks["OnChanges"] = 3] = "OnChanges";
    LifecycleHooks[LifecycleHooks["AfterContentInit"] = 4] = "AfterContentInit";
    LifecycleHooks[LifecycleHooks["AfterContentChecked"] = 5] = "AfterContentChecked";
    LifecycleHooks[LifecycleHooks["AfterViewInit"] = 6] = "AfterViewInit";
    LifecycleHooks[LifecycleHooks["AfterViewChecked"] = 7] = "AfterViewChecked";
})(LifecycleHooks || (LifecycleHooks = {}));
export const LIFECYCLE_HOOKS_VALUES = [
    LifecycleHooks.OnInit, LifecycleHooks.OnDestroy, LifecycleHooks.DoCheck, LifecycleHooks.OnChanges,
    LifecycleHooks.AfterContentInit, LifecycleHooks.AfterContentChecked, LifecycleHooks.AfterViewInit,
    LifecycleHooks.AfterViewChecked
];
export function hasLifecycleHook(reflector, hook, token) {
    return reflector.hasLifecycleHook(token, getHookName(hook));
}
export function getAllLifecycleHooks(reflector, token) {
    return LIFECYCLE_HOOKS_VALUES.filter(hook => hasLifecycleHook(reflector, hook, token));
}
function getHookName(hook) {
    switch (hook) {
        case LifecycleHooks.OnInit:
            return 'ngOnInit';
        case LifecycleHooks.OnDestroy:
            return 'ngOnDestroy';
        case LifecycleHooks.DoCheck:
            return 'ngDoCheck';
        case LifecycleHooks.OnChanges:
            return 'ngOnChanges';
        case LifecycleHooks.AfterContentInit:
            return 'ngAfterContentInit';
        case LifecycleHooks.AfterContentChecked:
            return 'ngAfterContentChecked';
        case LifecycleHooks.AfterViewInit:
            return 'ngAfterViewInit';
        case LifecycleHooks.AfterViewChecked:
            return 'ngAfterViewChecked';
        default:
            // This default case is not needed by TypeScript compiler, as the switch is exhaustive.
            // However Closure Compiler does not understand that and reports an error in typed mode.
            // The `throw new Error` below works around the problem, and the unexpected: never variable
            // makes sure tsc still checks this code is unreachable.
            const unexpected = hook;
            throw new Error(`unexpected ${unexpected}`);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibGlmZWN5Y2xlX3JlZmxlY3Rvci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9saWZlY3ljbGVfcmVmbGVjdG9yLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUlILE1BQU0sQ0FBTixJQUFZLGNBU1g7QUFURCxXQUFZLGNBQWM7SUFDeEIsdURBQU0sQ0FBQTtJQUNOLDZEQUFTLENBQUE7SUFDVCx5REFBTyxDQUFBO0lBQ1AsNkRBQVMsQ0FBQTtJQUNULDJFQUFnQixDQUFBO0lBQ2hCLGlGQUFtQixDQUFBO0lBQ25CLHFFQUFhLENBQUE7SUFDYiwyRUFBZ0IsQ0FBQTtBQUNsQixDQUFDLEVBVFcsY0FBYyxLQUFkLGNBQWMsUUFTekI7QUFFRCxNQUFNLENBQUMsTUFBTSxzQkFBc0IsR0FBRztJQUNwQyxjQUFjLENBQUMsTUFBTSxFQUFFLGNBQWMsQ0FBQyxTQUFTLEVBQUUsY0FBYyxDQUFDLE9BQU8sRUFBRSxjQUFjLENBQUMsU0FBUztJQUNqRyxjQUFjLENBQUMsZ0JBQWdCLEVBQUUsY0FBYyxDQUFDLG1CQUFtQixFQUFFLGNBQWMsQ0FBQyxhQUFhO0lBQ2pHLGNBQWMsQ0FBQyxnQkFBZ0I7Q0FDaEMsQ0FBQztBQUVGLE1BQU0sVUFBVSxnQkFBZ0IsQ0FDNUIsU0FBMkIsRUFBRSxJQUFvQixFQUFFLEtBQVU7SUFDL0QsT0FBTyxTQUFTLENBQUMsZ0JBQWdCLENBQUMsS0FBSyxFQUFFLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO0FBQzlELENBQUM7QUFFRCxNQUFNLFVBQVUsb0JBQW9CLENBQUMsU0FBMkIsRUFBRSxLQUFVO0lBQzFFLE9BQU8sc0JBQXNCLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsZ0JBQWdCLENBQUMsU0FBUyxFQUFFLElBQUksRUFBRSxLQUFLLENBQUMsQ0FBQyxDQUFDO0FBQ3pGLENBQUM7QUFFRCxTQUFTLFdBQVcsQ0FBQyxJQUFvQjtJQUN2QyxRQUFRLElBQUksRUFBRTtRQUNaLEtBQUssY0FBYyxDQUFDLE1BQU07WUFDeEIsT0FBTyxVQUFVLENBQUM7UUFDcEIsS0FBSyxjQUFjLENBQUMsU0FBUztZQUMzQixPQUFPLGFBQWEsQ0FBQztRQUN2QixLQUFLLGNBQWMsQ0FBQyxPQUFPO1lBQ3pCLE9BQU8sV0FBVyxDQUFDO1FBQ3JCLEtBQUssY0FBYyxDQUFDLFNBQVM7WUFDM0IsT0FBTyxhQUFhLENBQUM7UUFDdkIsS0FBSyxjQUFjLENBQUMsZ0JBQWdCO1lBQ2xDLE9BQU8sb0JBQW9CLENBQUM7UUFDOUIsS0FBSyxjQUFjLENBQUMsbUJBQW1CO1lBQ3JDLE9BQU8sdUJBQXVCLENBQUM7UUFDakMsS0FBSyxjQUFjLENBQUMsYUFBYTtZQUMvQixPQUFPLGlCQUFpQixDQUFDO1FBQzNCLEtBQUssY0FBYyxDQUFDLGdCQUFnQjtZQUNsQyxPQUFPLG9CQUFvQixDQUFDO1FBQzlCO1lBQ0UsdUZBQXVGO1lBQ3ZGLHdGQUF3RjtZQUN4RiwyRkFBMkY7WUFDM0Ysd0RBQXdEO1lBQ3hELE1BQU0sVUFBVSxHQUFVLElBQUksQ0FBQztZQUMvQixNQUFNLElBQUksS0FBSyxDQUFDLGNBQWMsVUFBVSxFQUFFLENBQUMsQ0FBQztLQUMvQztBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuL2NvbXBpbGVfcmVmbGVjdG9yJztcblxuZXhwb3J0IGVudW0gTGlmZWN5Y2xlSG9va3Mge1xuICBPbkluaXQsXG4gIE9uRGVzdHJveSxcbiAgRG9DaGVjayxcbiAgT25DaGFuZ2VzLFxuICBBZnRlckNvbnRlbnRJbml0LFxuICBBZnRlckNvbnRlbnRDaGVja2VkLFxuICBBZnRlclZpZXdJbml0LFxuICBBZnRlclZpZXdDaGVja2VkXG59XG5cbmV4cG9ydCBjb25zdCBMSUZFQ1lDTEVfSE9PS1NfVkFMVUVTID0gW1xuICBMaWZlY3ljbGVIb29rcy5PbkluaXQsIExpZmVjeWNsZUhvb2tzLk9uRGVzdHJveSwgTGlmZWN5Y2xlSG9va3MuRG9DaGVjaywgTGlmZWN5Y2xlSG9va3MuT25DaGFuZ2VzLFxuICBMaWZlY3ljbGVIb29rcy5BZnRlckNvbnRlbnRJbml0LCBMaWZlY3ljbGVIb29rcy5BZnRlckNvbnRlbnRDaGVja2VkLCBMaWZlY3ljbGVIb29rcy5BZnRlclZpZXdJbml0LFxuICBMaWZlY3ljbGVIb29rcy5BZnRlclZpZXdDaGVja2VkXG5dO1xuXG5leHBvcnQgZnVuY3Rpb24gaGFzTGlmZWN5Y2xlSG9vayhcbiAgICByZWZsZWN0b3I6IENvbXBpbGVSZWZsZWN0b3IsIGhvb2s6IExpZmVjeWNsZUhvb2tzLCB0b2tlbjogYW55KTogYm9vbGVhbiB7XG4gIHJldHVybiByZWZsZWN0b3IuaGFzTGlmZWN5Y2xlSG9vayh0b2tlbiwgZ2V0SG9va05hbWUoaG9vaykpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gZ2V0QWxsTGlmZWN5Y2xlSG9va3MocmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yLCB0b2tlbjogYW55KTogTGlmZWN5Y2xlSG9va3NbXSB7XG4gIHJldHVybiBMSUZFQ1lDTEVfSE9PS1NfVkFMVUVTLmZpbHRlcihob29rID0+IGhhc0xpZmVjeWNsZUhvb2socmVmbGVjdG9yLCBob29rLCB0b2tlbikpO1xufVxuXG5mdW5jdGlvbiBnZXRIb29rTmFtZShob29rOiBMaWZlY3ljbGVIb29rcyk6IHN0cmluZyB7XG4gIHN3aXRjaCAoaG9vaykge1xuICAgIGNhc2UgTGlmZWN5Y2xlSG9va3MuT25Jbml0OlxuICAgICAgcmV0dXJuICduZ09uSW5pdCc7XG4gICAgY2FzZSBMaWZlY3ljbGVIb29rcy5PbkRlc3Ryb3k6XG4gICAgICByZXR1cm4gJ25nT25EZXN0cm95JztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLkRvQ2hlY2s6XG4gICAgICByZXR1cm4gJ25nRG9DaGVjayc7XG4gICAgY2FzZSBMaWZlY3ljbGVIb29rcy5PbkNoYW5nZXM6XG4gICAgICByZXR1cm4gJ25nT25DaGFuZ2VzJztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLkFmdGVyQ29udGVudEluaXQ6XG4gICAgICByZXR1cm4gJ25nQWZ0ZXJDb250ZW50SW5pdCc7XG4gICAgY2FzZSBMaWZlY3ljbGVIb29rcy5BZnRlckNvbnRlbnRDaGVja2VkOlxuICAgICAgcmV0dXJuICduZ0FmdGVyQ29udGVudENoZWNrZWQnO1xuICAgIGNhc2UgTGlmZWN5Y2xlSG9va3MuQWZ0ZXJWaWV3SW5pdDpcbiAgICAgIHJldHVybiAnbmdBZnRlclZpZXdJbml0JztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLkFmdGVyVmlld0NoZWNrZWQ6XG4gICAgICByZXR1cm4gJ25nQWZ0ZXJWaWV3Q2hlY2tlZCc7XG4gICAgZGVmYXVsdDpcbiAgICAgIC8vIFRoaXMgZGVmYXVsdCBjYXNlIGlzIG5vdCBuZWVkZWQgYnkgVHlwZVNjcmlwdCBjb21waWxlciwgYXMgdGhlIHN3aXRjaCBpcyBleGhhdXN0aXZlLlxuICAgICAgLy8gSG93ZXZlciBDbG9zdXJlIENvbXBpbGVyIGRvZXMgbm90IHVuZGVyc3RhbmQgdGhhdCBhbmQgcmVwb3J0cyBhbiBlcnJvciBpbiB0eXBlZCBtb2RlLlxuICAgICAgLy8gVGhlIGB0aHJvdyBuZXcgRXJyb3JgIGJlbG93IHdvcmtzIGFyb3VuZCB0aGUgcHJvYmxlbSwgYW5kIHRoZSB1bmV4cGVjdGVkOiBuZXZlciB2YXJpYWJsZVxuICAgICAgLy8gbWFrZXMgc3VyZSB0c2Mgc3RpbGwgY2hlY2tzIHRoaXMgY29kZSBpcyB1bnJlYWNoYWJsZS5cbiAgICAgIGNvbnN0IHVuZXhwZWN0ZWQ6IG5ldmVyID0gaG9vaztcbiAgICAgIHRocm93IG5ldyBFcnJvcihgdW5leHBlY3RlZCAke3VuZXhwZWN0ZWR9YCk7XG4gIH1cbn1cbiJdfQ==