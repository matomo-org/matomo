/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { Component } from './core';
import * as o from './output/output_ast';
/**
 * Provides access to reflection data about symbols that the compiler needs.
 */
export declare abstract class CompileReflector {
    abstract parameters(typeOrFunc: any): any[][];
    abstract annotations(typeOrFunc: any): any[];
    abstract shallowAnnotations(typeOrFunc: any): any[];
    abstract tryAnnotations(typeOrFunc: any): any[];
    abstract propMetadata(typeOrFunc: any): {
        [key: string]: any[];
    };
    abstract hasLifecycleHook(type: any, lcProperty: string): boolean;
    abstract guards(typeOrFunc: any): {
        [key: string]: any;
    };
    abstract componentModuleUrl(type: any, cmpMetadata: Component): string;
    abstract resolveExternalReference(ref: o.ExternalReference): any;
}
