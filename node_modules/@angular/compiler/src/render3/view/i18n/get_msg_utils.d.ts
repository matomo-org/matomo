/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as i18n from '../../../i18n/i18n_ast';
import * as o from '../../../output/output_ast';
export declare function createGoogleGetMsgStatements(variable: o.ReadVarExpr, message: i18n.Message, closureVar: o.ReadVarExpr, params: {
    [name: string]: o.Expression;
}): o.Statement[];
export declare function serializeI18nMessageForGetMsg(message: i18n.Message): string;
