/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * A token representing the a reference to a static type.
 *
 * This token is unique for a filePath and name and can be used as a hash table key.
 */
export declare class StaticSymbol {
    filePath: string;
    name: string;
    members: string[];
    constructor(filePath: string, name: string, members: string[]);
    assertNoMembers(): void;
}
/**
 * A cache of static symbol used by the StaticReflector to return the same symbol for the
 * same symbol values.
 */
export declare class StaticSymbolCache {
    private cache;
    get(declarationFile: string, name: string, members?: string[]): StaticSymbol;
}
