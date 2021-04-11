/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as i18n from '../../../i18n/i18n_ast';
import * as html from '../../../ml_parser/ast';
import * as o from '../../../output/output_ast';
import * as t from '../../r3_ast';
/**
 * Prefix for non-`goog.getMsg` i18n-related vars.
 * Note: the prefix uses lowercase characters intentionally due to a Closure behavior that
 * considers variables like `I18N_0` as constants and throws an error when their value changes.
 */
export declare const TRANSLATION_VAR_PREFIX = "i18n_";
/** Name of the i18n attributes **/
export declare const I18N_ATTR = "i18n";
export declare const I18N_ATTR_PREFIX = "i18n-";
/** Prefix of var expressions used in ICUs */
export declare const I18N_ICU_VAR_PREFIX = "VAR_";
/** Prefix of ICU expressions for post processing */
export declare const I18N_ICU_MAPPING_PREFIX = "I18N_EXP_";
/** Placeholder wrapper for i18n expressions **/
export declare const I18N_PLACEHOLDER_SYMBOL = "\uFFFD";
export declare function isI18nAttribute(name: string): boolean;
export declare function isI18nRootNode(meta?: i18n.I18nMeta): meta is i18n.Message;
export declare function isSingleI18nIcu(meta?: i18n.I18nMeta): boolean;
export declare function hasI18nMeta(node: t.Node & {
    i18n?: i18n.I18nMeta;
}): boolean;
export declare function hasI18nAttrs(element: html.Element): boolean;
export declare function icuFromI18nMessage(message: i18n.Message): i18n.IcuPlaceholder;
export declare function wrapI18nPlaceholder(content: string | number, contextId?: number): string;
export declare function assembleI18nBoundString(strings: string[], bindingStartIndex?: number, contextId?: number): string;
export declare function getSeqNumberGenerator(startsAt?: number): () => number;
export declare function placeholdersToParams(placeholders: Map<string, string[]>): {
    [name: string]: o.LiteralExpr;
};
export declare function updatePlaceholderMap(map: Map<string, any[]>, name: string, ...values: any[]): void;
export declare function assembleBoundTextPlaceholders(meta: i18n.I18nMeta, bindingStartIndex?: number, contextId?: number): Map<string, any[]>;
/**
 * Format the placeholder names in a map of placeholders to expressions.
 *
 * The placeholder names are converted from "internal" format (e.g. `START_TAG_DIV_1`) to "external"
 * format (e.g. `startTagDiv_1`).
 *
 * @param params A map of placeholder names to expressions.
 * @param useCamelCase whether to camelCase the placeholder name when formatting.
 * @returns A new map of formatted placeholder names to expressions.
 */
export declare function i18nFormatPlaceholderNames(params: {
    [name: string]: o.Expression;
} | undefined, useCamelCase: boolean): {
    [key: string]: o.Expression;
};
/**
 * Converts internal placeholder names to public-facing format
 * (for example to use in goog.getMsg call).
 * Example: `START_TAG_DIV_1` is converted to `startTagDiv_1`.
 *
 * @param name The placeholder name that should be formatted
 * @returns Formatted placeholder name
 */
export declare function formatI18nPlaceholderName(name: string, useCamelCase?: boolean): string;
/**
 * Generates a prefix for translation const name.
 *
 * @param extra Additional local prefix that should be injected into translation var name
 * @returns Complete translation const prefix
 */
export declare function getTranslationConstPrefix(extra: string): string;
/**
 * Generate AST to declare a variable. E.g. `var I18N_1;`.
 * @param variable the name of the variable to declare.
 */
export declare function declareI18nVariable(variable: o.ReadVarExpr): o.Statement;
