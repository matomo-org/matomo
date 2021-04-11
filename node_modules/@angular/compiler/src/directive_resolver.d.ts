/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from './compile_reflector';
import { Directive, Type } from './core';
export declare class DirectiveResolver {
    private _reflector;
    constructor(_reflector: CompileReflector);
    isDirective(type: Type): boolean;
    /**
     * Return {@link Directive} for a given `Type`.
     */
    resolve(type: Type): Directive;
    resolve(type: Type, throwIfNotFound: true): Directive;
    resolve(type: Type, throwIfNotFound: boolean): Directive | null;
    private _mergeWithPropertyMetadata;
    private _extractPublicName;
    private _dedupeBindings;
    private _merge;
}
export declare function findLast<T>(arr: T[], condition: (value: T) => boolean): T | null;
