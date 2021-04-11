/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileNgModuleMetadata } from '../compile_metadata';
import { StaticReflector } from './static_reflector';
import { StaticSymbol } from './static_symbol';
export interface LazyRoute {
    module: StaticSymbol;
    route: string;
    referencedModule: StaticSymbol;
}
export declare function listLazyRoutes(moduleMeta: CompileNgModuleMetadata, reflector: StaticReflector): LazyRoute[];
export declare function parseLazyRoute(route: string, reflector: StaticReflector, module?: StaticSymbol): LazyRoute;
