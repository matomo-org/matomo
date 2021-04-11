/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as o from './output/output_ast';
import { R3DependencyMetadata } from './render3/r3_factory';
import { R3Reference } from './render3/util';
export interface InjectableDef {
    expression: o.Expression;
    type: o.Type;
    statements: o.Statement[];
}
export interface R3InjectableMetadata {
    name: string;
    type: R3Reference;
    internalType: o.Expression;
    typeArgumentCount: number;
    providedIn: o.Expression;
    useClass?: o.Expression;
    useFactory?: o.Expression;
    useExisting?: o.Expression;
    useValue?: o.Expression;
    userDeps?: R3DependencyMetadata[];
}
export declare function compileInjectable(meta: R3InjectableMetadata): InjectableDef;
