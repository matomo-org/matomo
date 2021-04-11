/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { MissingTranslationStrategy, ViewEncapsulation } from './core';
export declare class CompilerConfig {
    defaultEncapsulation: ViewEncapsulation | null;
    useJit: boolean;
    jitDevMode: boolean;
    missingTranslation: MissingTranslationStrategy | null;
    preserveWhitespaces: boolean;
    strictInjectionParameters: boolean;
    constructor({ defaultEncapsulation, useJit, jitDevMode, missingTranslation, preserveWhitespaces, strictInjectionParameters }?: {
        defaultEncapsulation?: ViewEncapsulation;
        useJit?: boolean;
        jitDevMode?: boolean;
        missingTranslation?: MissingTranslationStrategy | null;
        preserveWhitespaces?: boolean;
        strictInjectionParameters?: boolean;
    });
}
export declare function preserveWhitespacesDefault(preserveWhitespacesOption: boolean | null, defaultSetting?: boolean): boolean;
