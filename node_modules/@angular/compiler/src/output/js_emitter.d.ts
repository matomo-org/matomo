/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { OutputEmitter } from './abstract_emitter';
import * as o from './output_ast';
export declare class JavaScriptEmitter implements OutputEmitter {
    emitStatements(genFilePath: string, stmts: o.Statement[], preamble?: string): string;
}
