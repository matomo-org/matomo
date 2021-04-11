/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector, core, PipeResolver } from '@angular/compiler';
export declare class MockPipeResolver extends PipeResolver {
    private _pipes;
    constructor(refector: CompileReflector);
    /**
     * Overrides the {@link Pipe} for a pipe.
     */
    setPipe(type: core.Type, metadata: core.Pipe): void;
    /**
     * Returns the {@link Pipe} for a pipe:
     * - Set the {@link Pipe} to the overridden view when it exists or fallback to the
     * default
     * `PipeResolver`, see `setPipe`.
     */
    resolve(type: core.Type, throwIfNotFound?: boolean): core.Pipe;
}
