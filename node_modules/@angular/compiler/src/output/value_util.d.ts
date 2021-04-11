/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { OutputContext } from '../util';
import * as o from './output_ast';
export declare const QUOTED_KEYS = "$quoted$";
export declare function convertValueToOutputAst(ctx: OutputContext, value: any, type?: o.Type | null): o.Expression;
