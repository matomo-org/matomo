/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from '../compile_reflector';
import * as o from '../output/output_ast';
/**
 * Implementation of `CompileReflector` which resolves references to @angular/core
 * symbols at runtime, according to a consumer-provided mapping.
 *
 * Only supports `resolveExternalReference`, all other methods throw.
 */
export declare class R3JitReflector implements CompileReflector {
    private context;
    constructor(context: {
        [key: string]: any;
    });
    resolveExternalReference(ref: o.ExternalReference): any;
    parameters(typeOrFunc: any): any[][];
    annotations(typeOrFunc: any): any[];
    shallowAnnotations(typeOrFunc: any): any[];
    tryAnnotations(typeOrFunc: any): any[];
    propMetadata(typeOrFunc: any): {
        [key: string]: any[];
    };
    hasLifecycleHook(type: any, lcProperty: string): boolean;
    guards(typeOrFunc: any): {
        [key: string]: any;
    };
    componentModuleUrl(type: any, cmpMetadata: any): string;
}
