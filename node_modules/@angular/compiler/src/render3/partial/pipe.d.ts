import { R3PipeMetadata } from '../r3_pipe_compiler';
import { R3PipeDef } from '../view/api';
import { DefinitionMap } from '../view/util';
import { R3DeclarePipeMetadata } from './api';
/**
 * Compile a Pipe declaration defined by the `R3PipeMetadata`.
 */
export declare function compileDeclarePipeFromMetadata(meta: R3PipeMetadata): R3PipeDef;
/**
 * Gathers the declaration fields for a Pipe into a `DefinitionMap`. This allows for reusing
 * this logic for components, as they extend the Pipe metadata.
 */
export declare function createPipeDefinitionMap(meta: R3PipeMetadata): DefinitionMap<R3DeclarePipeMetadata>;
