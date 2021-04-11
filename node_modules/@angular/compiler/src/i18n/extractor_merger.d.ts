/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as html from '../ml_parser/ast';
import { InterpolationConfig } from '../ml_parser/interpolation_config';
import { ParseTreeResult } from '../ml_parser/parser';
import * as i18n from './i18n_ast';
import { I18nError } from './parse_util';
import { TranslationBundle } from './translation_bundle';
/**
 * Extract translatable messages from an html AST
 */
export declare function extractMessages(nodes: html.Node[], interpolationConfig: InterpolationConfig, implicitTags: string[], implicitAttrs: {
    [k: string]: string[];
}): ExtractionResult;
export declare function mergeTranslations(nodes: html.Node[], translations: TranslationBundle, interpolationConfig: InterpolationConfig, implicitTags: string[], implicitAttrs: {
    [k: string]: string[];
}): ParseTreeResult;
export declare class ExtractionResult {
    messages: i18n.Message[];
    errors: I18nError[];
    constructor(messages: i18n.Message[], errors: I18nError[]);
}
