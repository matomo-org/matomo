/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as o from '../output/output_ast';
import { R3DependencyMetadata } from './r3_factory';
import { R3Reference } from './util';
import { R3PipeDef } from './view/api';
export interface R3PipeMetadata {
    /**
     * Name of the pipe type.
     */
    name: string;
    /**
     * An expression representing a reference to the pipe itself.
     */
    type: R3Reference;
    /**
     * An expression representing the pipe being compiled, intended for use within a class definition
     * itself.
     *
     * This can differ from the outer `type` if the class is being compiled by ngcc and is inside an
     * IIFE structure that uses a different name internally.
     */
    internalType: o.Expression;
    /**
     * Number of generic type parameters of the type itself.
     */
    typeArgumentCount: number;
    /**
     * Name of the pipe.
     */
    pipeName: string;
    /**
     * Dependencies of the pipe's constructor.
     */
    deps: R3DependencyMetadata[] | null;
    /**
     * Whether the pipe is marked as pure.
     */
    pure: boolean;
}
export declare function compilePipeFromMetadata(metadata: R3PipeMetadata): R3PipeDef;
export declare function createPipeType(metadata: R3PipeMetadata): o.Type;
