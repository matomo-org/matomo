/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from './compile_reflector';
import { Pipe, Type } from './core';
/**
 * Resolve a `Type` for {@link Pipe}.
 *
 * This interface can be overridden by the application developer to create custom behavior.
 *
 * See {@link Compiler}
 */
export declare class PipeResolver {
    private _reflector;
    constructor(_reflector: CompileReflector);
    isPipe(type: Type): boolean;
    /**
     * Return {@link Pipe} for a given `Type`.
     */
    resolve(type: Type, throwIfNotFound?: boolean): Pipe | null;
}
