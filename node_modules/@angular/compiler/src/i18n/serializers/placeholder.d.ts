/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Creates unique names for placeholder with different content.
 *
 * Returns the same placeholder name when the content is identical.
 */
export declare class PlaceholderRegistry {
    private _placeHolderNameCounts;
    private _signatureToName;
    getStartTagPlaceholderName(tag: string, attrs: {
        [k: string]: string;
    }, isVoid: boolean): string;
    getCloseTagPlaceholderName(tag: string): string;
    getPlaceholderName(name: string, content: string): string;
    getUniquePlaceholder(name: string): string;
    private _hashTag;
    private _hashClosingTag;
    private _generateUniqueName;
}
