/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from '../compile_reflector';
import * as o from './output_ast';
export declare function interpretStatements(statements: o.Statement[], reflector: CompileReflector): {
    [key: string]: any;
};
