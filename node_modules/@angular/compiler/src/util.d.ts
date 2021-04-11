/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { ConstantPool } from './constant_pool';
import * as o from './output/output_ast';
import { ParseError } from './parse_util';
export declare function dashCaseToCamelCase(input: string): string;
export declare function splitAtColon(input: string, defaultValues: string[]): string[];
export declare function splitAtPeriod(input: string, defaultValues: string[]): string[];
export declare function visitValue(value: any, visitor: ValueVisitor, context: any): any;
export declare function isDefined(val: any): boolean;
export declare function noUndefined<T>(val: T | undefined): T;
export interface ValueVisitor {
    visitArray(arr: any[], context: any): any;
    visitStringMap(map: {
        [key: string]: any;
    }, context: any): any;
    visitPrimitive(value: any, context: any): any;
    visitOther(value: any, context: any): any;
}
export declare class ValueTransformer implements ValueVisitor {
    visitArray(arr: any[], context: any): any;
    visitStringMap(map: {
        [key: string]: any;
    }, context: any): any;
    visitPrimitive(value: any, context: any): any;
    visitOther(value: any, context: any): any;
}
export declare type SyncAsync<T> = T | Promise<T>;
export declare const SyncAsync: {
    assertSync: <T>(value: SyncAsync<T>) => T;
    then: <T_1, R>(value: SyncAsync<T_1>, cb: (value: T_1) => SyncAsync<R>) => SyncAsync<R>;
    all: <T_2>(syncAsyncValues: SyncAsync<T_2>[]) => SyncAsync<T_2[]>;
};
export declare function error(msg: string): never;
export declare function syntaxError(msg: string, parseErrors?: ParseError[]): Error;
export declare function isSyntaxError(error: Error): boolean;
export declare function getParseErrors(error: Error): ParseError[];
export declare function escapeRegExp(s: string): string;
export declare type Byte = number;
export declare function utf8Encode(str: string): Byte[];
export interface OutputContext {
    genFilePath: string;
    statements: o.Statement[];
    constantPool: ConstantPool;
    importExpr(reference: any, typeParams?: o.Type[] | null, useSummaries?: boolean): o.Expression;
}
export declare function stringify(token: any): string;
/**
 * Lazily retrieves the reference value from a forwardRef.
 */
export declare function resolveForwardRef(type: any): any;
/**
 * Determine if the argument is shaped like a Promise
 */
export declare function isPromise<T = any>(obj: any): obj is Promise<T>;
export declare class Version {
    full: string;
    readonly major: string;
    readonly minor: string;
    readonly patch: string;
    constructor(full: string);
}
export interface Console {
    log(message: string): void;
    warn(message: string): void;
}
declare const _global: {
    [name: string]: any;
};
export { _global as global };
export declare function newArray<T = any>(size: number): T[];
export declare function newArray<T>(size: number, value: T): T[];
/**
 * Partitions a given array into 2 arrays, based on a boolean value returned by the condition
 * function.
 *
 * @param arr Input array that should be partitioned
 * @param conditionFn Condition function that is called for each item in a given array and returns a
 * boolean value.
 */
export declare function partitionArray<T, F = T>(arr: (T | F)[], conditionFn: (value: T | F) => boolean): [T[], F[]];
