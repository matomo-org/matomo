/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector, core, NgModuleResolver } from '@angular/compiler';
export declare class MockNgModuleResolver extends NgModuleResolver {
    private _ngModules;
    constructor(reflector: CompileReflector);
    /**
     * Overrides the {@link NgModule} for a module.
     */
    setNgModule(type: core.Type, metadata: core.NgModule): void;
    /**
     * Returns the {@link NgModule} for a module:
     * - Set the {@link NgModule} to the overridden view when it exists or fallback to the
     * default
     * `NgModuleResolver`, see `setNgModule`.
     */
    resolve(type: core.Type, throwIfNotFound?: boolean): core.NgModule;
}
