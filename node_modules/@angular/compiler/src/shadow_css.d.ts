/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * This file is a port of shadowCSS from webcomponents.js to TypeScript.
 *
 * Please make sure to keep to edits in sync with the source file.
 *
 * Source:
 * https://github.com/webcomponents/webcomponentsjs/blob/4efecd7e0e/src/ShadowCSS/ShadowCSS.js
 *
 * The original file level comment is reproduced below
 */
export declare class ShadowCss {
    strictStyling: boolean;
    constructor();
    shimCssText(cssText: string, selector: string, hostSelector?: string): string;
    private _insertDirectives;
    private _insertPolyfillDirectivesInCssText;
    private _insertPolyfillRulesInCssText;
    private _scopeCssText;
    private _extractUnscopedRulesFromCssText;
    private _convertColonHost;
    private _convertColonHostContext;
    private _convertShadowDOMSelectors;
    private _scopeSelectors;
    private _scopeSelector;
    private _selectorNeedsScoping;
    private _makeScopeMatcher;
    private _applySelectorScope;
    private _applySimpleSelectorScope;
    private _insertPolyfillHostInCssText;
}
export declare class CssRule {
    selector: string;
    content: string;
    constructor(selector: string, content: string);
}
export declare function processRules(input: string, ruleCallback: (rule: CssRule) => CssRule): string;
/**
 * Mutate the given `groups` array so that there are `multiples` clones of the original array
 * stored.
 *
 * For example `repeatGroups([a, b], 3)` will result in `[a, b, a, b, a, b]` - but importantly the
 * newly added groups will be clones of the original.
 *
 * @param groups An array of groups of strings that will be repeated. This array is mutated
 *     in-place.
 * @param multiples The number of times the current groups should appear.
 */
export declare function repeatGroups<T>(groups: string[][], multiples: number): void;
