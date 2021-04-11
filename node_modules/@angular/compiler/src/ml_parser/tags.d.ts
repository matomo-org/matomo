/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export declare enum TagContentType {
    RAW_TEXT = 0,
    ESCAPABLE_RAW_TEXT = 1,
    PARSABLE_DATA = 2
}
export interface TagDefinition {
    closedByParent: boolean;
    implicitNamespacePrefix: string | null;
    isVoid: boolean;
    ignoreFirstLf: boolean;
    canSelfClose: boolean;
    preventNamespaceInheritance: boolean;
    isClosedByChild(name: string): boolean;
    getContentType(prefix?: string): TagContentType;
}
export declare function splitNsName(elementName: string): [string | null, string];
export declare function isNgContainer(tagName: string): boolean;
export declare function isNgContent(tagName: string): boolean;
export declare function isNgTemplate(tagName: string): boolean;
export declare function getNsPrefix(fullName: string): string;
export declare function getNsPrefix(fullName: null): null;
export declare function mergeNsAndName(prefix: string, localName: string): string;
export declare const NAMED_ENTITIES: {
    [k: string]: string;
};
export declare const NGSP_UNICODE = "\uE500";
