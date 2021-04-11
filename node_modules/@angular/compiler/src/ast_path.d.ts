/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * A path is an ordered set of elements. Typically a path is to  a
 * particular offset in a source file. The head of the list is the top
 * most node. The tail is the node that contains the offset directly.
 *
 * For example, the expression `a + b + c` might have an ast that looks
 * like:
 *     +
 *    / \
 *   a   +
 *      / \
 *     b   c
 *
 * The path to the node at offset 9 would be `['+' at 1-10, '+' at 7-10,
 * 'c' at 9-10]` and the path the node at offset 1 would be
 * `['+' at 1-10, 'a' at 1-2]`.
 */
export declare class AstPath<T> {
    private path;
    position: number;
    constructor(path: T[], position?: number);
    get empty(): boolean;
    get head(): T | undefined;
    get tail(): T | undefined;
    parentOf(node: T | undefined): T | undefined;
    childOf(node: T): T | undefined;
    first<N extends T>(ctor: {
        new (...args: any[]): N;
    }): N | undefined;
    push(node: T): void;
    pop(): T;
}
