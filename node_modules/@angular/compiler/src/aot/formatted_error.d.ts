/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export interface Position {
    fileName: string;
    line: number;
    column: number;
}
export interface FormattedMessageChain {
    message: string;
    position?: Position;
    next?: FormattedMessageChain[];
}
export declare type FormattedError = Error & {
    chain: FormattedMessageChain;
    position?: Position;
};
export declare function formattedError(chain: FormattedMessageChain): FormattedError;
export declare function isFormattedError(error: Error): error is FormattedError;
