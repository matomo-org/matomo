/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from './compile_reflector';
export declare enum LifecycleHooks {
    OnInit = 0,
    OnDestroy = 1,
    DoCheck = 2,
    OnChanges = 3,
    AfterContentInit = 4,
    AfterContentChecked = 5,
    AfterViewInit = 6,
    AfterViewChecked = 7
}
export declare const LIFECYCLE_HOOKS_VALUES: LifecycleHooks[];
export declare function hasLifecycleHook(reflector: CompileReflector, hook: LifecycleHooks, token: any): boolean;
export declare function getAllLifecycleHooks(reflector: CompileReflector, token: any): LifecycleHooks[];
