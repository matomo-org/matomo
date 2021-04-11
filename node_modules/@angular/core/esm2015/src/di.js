/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * This file should not be necessary because node resolution should just default to `./di/index`!
 *
 * However it does not seem to work and it breaks:
 *  - //packages/animations/browser/test:test_web_chromium-local
 *  - //packages/compiler-cli/test:extract_i18n
 *  - //packages/compiler-cli/test:ngc
 *  - //packages/compiler-cli/test:perform_watch
 *  - //packages/compiler-cli/test/diagnostics:check_types
 *  - //packages/compiler-cli/test/transformers:test
 *  - //packages/compiler/test:test
 *  - //tools/public_api_guard:core_api
 *
 * Remove this file once the above is solved or wait until `ngc` is deleted and then it should be
 * safe to delete this file.
 */
export * from './di/index';
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGkuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9kaS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSDs7Ozs7Ozs7Ozs7Ozs7O0dBZUc7QUFFSCxjQUFjLFlBQVksQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG4vKipcbiAqIFRoaXMgZmlsZSBzaG91bGQgbm90IGJlIG5lY2Vzc2FyeSBiZWNhdXNlIG5vZGUgcmVzb2x1dGlvbiBzaG91bGQganVzdCBkZWZhdWx0IHRvIGAuL2RpL2luZGV4YCFcbiAqXG4gKiBIb3dldmVyIGl0IGRvZXMgbm90IHNlZW0gdG8gd29yayBhbmQgaXQgYnJlYWtzOlxuICogIC0gLy9wYWNrYWdlcy9hbmltYXRpb25zL2Jyb3dzZXIvdGVzdDp0ZXN0X3dlYl9jaHJvbWl1bS1sb2NhbFxuICogIC0gLy9wYWNrYWdlcy9jb21waWxlci1jbGkvdGVzdDpleHRyYWN0X2kxOG5cbiAqICAtIC8vcGFja2FnZXMvY29tcGlsZXItY2xpL3Rlc3Q6bmdjXG4gKiAgLSAvL3BhY2thZ2VzL2NvbXBpbGVyLWNsaS90ZXN0OnBlcmZvcm1fd2F0Y2hcbiAqICAtIC8vcGFja2FnZXMvY29tcGlsZXItY2xpL3Rlc3QvZGlhZ25vc3RpY3M6Y2hlY2tfdHlwZXNcbiAqICAtIC8vcGFja2FnZXMvY29tcGlsZXItY2xpL3Rlc3QvdHJhbnNmb3JtZXJzOnRlc3RcbiAqICAtIC8vcGFja2FnZXMvY29tcGlsZXIvdGVzdDp0ZXN0XG4gKiAgLSAvL3Rvb2xzL3B1YmxpY19hcGlfZ3VhcmQ6Y29yZV9hcGlcbiAqXG4gKiBSZW1vdmUgdGhpcyBmaWxlIG9uY2UgdGhlIGFib3ZlIGlzIHNvbHZlZCBvciB3YWl0IHVudGlsIGBuZ2NgIGlzIGRlbGV0ZWQgYW5kIHRoZW4gaXQgc2hvdWxkIGJlXG4gKiBzYWZlIHRvIGRlbGV0ZSB0aGlzIGZpbGUuXG4gKi9cblxuZXhwb3J0ICogZnJvbSAnLi9kaS9pbmRleCc7XG4iXX0=