/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export declare enum TokenType {
    Character = 0,
    Identifier = 1,
    Keyword = 2,
    String = 3,
    Operator = 4,
    Number = 5,
    Error = 6
}
export declare class Lexer {
    tokenize(text: string): Token[];
}
export declare class Token {
    index: number;
    end: number;
    type: TokenType;
    numValue: number;
    strValue: string;
    constructor(index: number, end: number, type: TokenType, numValue: number, strValue: string);
    isCharacter(code: number): boolean;
    isNumber(): boolean;
    isString(): boolean;
    isOperator(operator: string): boolean;
    isIdentifier(): boolean;
    isKeyword(): boolean;
    isKeywordLet(): boolean;
    isKeywordAs(): boolean;
    isKeywordNull(): boolean;
    isKeywordUndefined(): boolean;
    isKeywordTrue(): boolean;
    isKeywordFalse(): boolean;
    isKeywordThis(): boolean;
    isError(): boolean;
    toNumber(): number;
    toString(): string | null;
}
export declare const EOF: Token;
export declare function isIdentifier(input: string): boolean;
export declare function isQuote(code: number): boolean;
