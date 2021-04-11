/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { UrlResolver } from '../url_resolver';
import { AotCompiler } from './compiler';
import { AotCompilerHost } from './compiler_host';
import { AotCompilerOptions } from './compiler_options';
import { StaticReflector } from './static_reflector';
export declare function createAotUrlResolver(host: {
    resourceNameToFileName(resourceName: string, containingFileName: string): string | null;
}): UrlResolver;
/**
 * Creates a new AotCompiler based on options and a host.
 */
export declare function createAotCompiler(compilerHost: AotCompilerHost, options: AotCompilerOptions, errorCollector?: (error: any, type?: any) => void): {
    compiler: AotCompiler;
    reflector: StaticReflector;
};
