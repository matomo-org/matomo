/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/convert_directive_metadata", ["require", "exports", "@angular/compiler", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.convertDirectiveMetadataToExpression = exports.UnexpectedMetadataValueError = void 0;
    const compiler_1 = require("@angular/compiler");
    const ts = require("typescript");
    /** Error that will be thrown if an unexpected value needs to be converted. */
    class UnexpectedMetadataValueError extends Error {
    }
    exports.UnexpectedMetadataValueError = UnexpectedMetadataValueError;
    /**
     * Converts a directive metadata object into a TypeScript expression. Throws
     * if metadata cannot be cleanly converted.
     */
    function convertDirectiveMetadataToExpression(metadata, resolveSymbolImport, createImport, convertProperty) {
        if (typeof metadata === 'string') {
            return ts.createStringLiteral(metadata);
        }
        else if (Array.isArray(metadata)) {
            return ts.createArrayLiteral(metadata.map(el => convertDirectiveMetadataToExpression(el, resolveSymbolImport, createImport, convertProperty)));
        }
        else if (typeof metadata === 'number') {
            return ts.createNumericLiteral(metadata.toString());
        }
        else if (typeof metadata === 'boolean') {
            return metadata ? ts.createTrue() : ts.createFalse();
        }
        else if (typeof metadata === 'undefined') {
            return ts.createIdentifier('undefined');
        }
        else if (typeof metadata === 'bigint') {
            return ts.createBigIntLiteral(metadata.toString());
        }
        else if (typeof metadata === 'object') {
            // In case there is a static symbol object part of the metadata, try to resolve
            // the import expression of the symbol. If no import path could be resolved, an
            // error will be thrown as the symbol cannot be converted into TypeScript AST.
            if (metadata instanceof compiler_1.StaticSymbol) {
                const resolvedImport = resolveSymbolImport(metadata);
                if (resolvedImport === null) {
                    throw new UnexpectedMetadataValueError();
                }
                return createImport(resolvedImport, metadata.name);
            }
            const literalProperties = [];
            for (const key of Object.keys(metadata)) {
                const metadataValue = metadata[key];
                let propertyValue = null;
                // Allows custom conversion of properties in an object. This is useful for special
                // cases where we don't want to store the enum values as integers, but rather use the
                // real enum symbol. e.g. instead of `2` we want to use `ViewEncapsulation.None`.
                if (convertProperty) {
                    propertyValue = convertProperty(key, metadataValue);
                }
                // In case the property value has not been assigned to an expression, we convert
                // the resolved metadata value into a TypeScript expression.
                if (propertyValue === null) {
                    propertyValue = convertDirectiveMetadataToExpression(metadataValue, resolveSymbolImport, createImport, convertProperty);
                }
                literalProperties.push(ts.createPropertyAssignment(getPropertyName(key), propertyValue));
            }
            return ts.createObjectLiteral(literalProperties, true);
        }
        throw new UnexpectedMetadataValueError();
    }
    exports.convertDirectiveMetadataToExpression = convertDirectiveMetadataToExpression;
    /**
     * Gets a valid property name from the given text. If the text cannot be used
     * as unquoted identifier, the name will be wrapped in a string literal.
     */
    function getPropertyName(name) {
        // Matches the most common identifiers that do not need quotes. Constructing a
        // regular expression that matches the ECMAScript specification in order to determine
        // whether quotes are needed is out of scope for this migration. For those more complex
        // property names, we just always use quotes (when constructing AST from metadata).
        if (/^[a-zA-Z_$]+$/.test(name)) {
            return name;
        }
        return ts.createStringLiteral(name);
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29udmVydF9kaXJlY3RpdmVfbWV0YWRhdGEuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy91bmRlY29yYXRlZC1jbGFzc2VzLXdpdGgtZGkvZGVjb3JhdG9yX3Jld3JpdGUvY29udmVydF9kaXJlY3RpdmVfbWV0YWRhdGEudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsZ0RBQStDO0lBQy9DLGlDQUFpQztJQUVqQyw4RUFBOEU7SUFDOUUsTUFBYSw0QkFBNkIsU0FBUSxLQUFLO0tBQUc7SUFBMUQsb0VBQTBEO0lBRTFEOzs7T0FHRztJQUNILFNBQWdCLG9DQUFvQyxDQUNoRCxRQUFhLEVBQUUsbUJBQTRELEVBQzNFLFlBQWlFLEVBQ2pFLGVBQW1FO1FBQ3JFLElBQUksT0FBTyxRQUFRLEtBQUssUUFBUSxFQUFFO1lBQ2hDLE9BQU8sRUFBRSxDQUFDLG1CQUFtQixDQUFDLFFBQVEsQ0FBQyxDQUFDO1NBQ3pDO2FBQU0sSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFFO1lBQ2xDLE9BQU8sRUFBRSxDQUFDLGtCQUFrQixDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQ3JDLEVBQUUsQ0FBQyxFQUFFLENBQUMsb0NBQW9DLENBQ3RDLEVBQUUsRUFBRSxtQkFBbUIsRUFBRSxZQUFZLEVBQUUsZUFBZSxDQUFDLENBQUMsQ0FBQyxDQUFDO1NBQ25FO2FBQU0sSUFBSSxPQUFPLFFBQVEsS0FBSyxRQUFRLEVBQUU7WUFDdkMsT0FBTyxFQUFFLENBQUMsb0JBQW9CLENBQUMsUUFBUSxDQUFDLFFBQVEsRUFBRSxDQUFDLENBQUM7U0FDckQ7YUFBTSxJQUFJLE9BQU8sUUFBUSxLQUFLLFNBQVMsRUFBRTtZQUN4QyxPQUFPLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLFVBQVUsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsV0FBVyxFQUFFLENBQUM7U0FDdEQ7YUFBTSxJQUFJLE9BQU8sUUFBUSxLQUFLLFdBQVcsRUFBRTtZQUMxQyxPQUFPLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxXQUFXLENBQUMsQ0FBQztTQUN6QzthQUFNLElBQUksT0FBTyxRQUFRLEtBQUssUUFBUSxFQUFFO1lBQ3ZDLE9BQU8sRUFBRSxDQUFDLG1CQUFtQixDQUFDLFFBQVEsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxDQUFDO1NBQ3BEO2FBQU0sSUFBSSxPQUFPLFFBQVEsS0FBSyxRQUFRLEVBQUU7WUFDdkMsK0VBQStFO1lBQy9FLCtFQUErRTtZQUMvRSw4RUFBOEU7WUFDOUUsSUFBSSxRQUFRLFlBQVksdUJBQVksRUFBRTtnQkFDcEMsTUFBTSxjQUFjLEdBQUcsbUJBQW1CLENBQUMsUUFBUSxDQUFDLENBQUM7Z0JBQ3JELElBQUksY0FBYyxLQUFLLElBQUksRUFBRTtvQkFDM0IsTUFBTSxJQUFJLDRCQUE0QixFQUFFLENBQUM7aUJBQzFDO2dCQUNELE9BQU8sWUFBWSxDQUFDLGNBQWMsRUFBRSxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUM7YUFDcEQ7WUFFRCxNQUFNLGlCQUFpQixHQUE0QixFQUFFLENBQUM7WUFFdEQsS0FBSyxNQUFNLEdBQUcsSUFBSSxNQUFNLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxFQUFFO2dCQUN2QyxNQUFNLGFBQWEsR0FBRyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUM7Z0JBQ3BDLElBQUksYUFBYSxHQUF1QixJQUFJLENBQUM7Z0JBRTdDLGtGQUFrRjtnQkFDbEYscUZBQXFGO2dCQUNyRixpRkFBaUY7Z0JBQ2pGLElBQUksZUFBZSxFQUFFO29CQUNuQixhQUFhLEdBQUcsZUFBZSxDQUFDLEdBQUcsRUFBRSxhQUFhLENBQUMsQ0FBQztpQkFDckQ7Z0JBRUQsZ0ZBQWdGO2dCQUNoRiw0REFBNEQ7Z0JBQzVELElBQUksYUFBYSxLQUFLLElBQUksRUFBRTtvQkFDMUIsYUFBYSxHQUFHLG9DQUFvQyxDQUNoRCxhQUFhLEVBQUUsbUJBQW1CLEVBQUUsWUFBWSxFQUFFLGVBQWUsQ0FBQyxDQUFDO2lCQUN4RTtnQkFFRCxpQkFBaUIsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLHdCQUF3QixDQUFDLGVBQWUsQ0FBQyxHQUFHLENBQUMsRUFBRSxhQUFhLENBQUMsQ0FBQyxDQUFDO2FBQzFGO1lBRUQsT0FBTyxFQUFFLENBQUMsbUJBQW1CLENBQUMsaUJBQWlCLEVBQUUsSUFBSSxDQUFDLENBQUM7U0FDeEQ7UUFFRCxNQUFNLElBQUksNEJBQTRCLEVBQUUsQ0FBQztJQUMzQyxDQUFDO0lBekRELG9GQXlEQztJQUVEOzs7T0FHRztJQUNILFNBQVMsZUFBZSxDQUFDLElBQVk7UUFDbkMsOEVBQThFO1FBQzlFLHFGQUFxRjtRQUNyRix1RkFBdUY7UUFDdkYsbUZBQW1GO1FBQ25GLElBQUksZUFBZSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBRTtZQUM5QixPQUFPLElBQUksQ0FBQztTQUNiO1FBQ0QsT0FBTyxFQUFFLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDLENBQUM7SUFDdEMsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge1N0YXRpY1N5bWJvbH0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXInO1xuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbi8qKiBFcnJvciB0aGF0IHdpbGwgYmUgdGhyb3duIGlmIGFuIHVuZXhwZWN0ZWQgdmFsdWUgbmVlZHMgdG8gYmUgY29udmVydGVkLiAqL1xuZXhwb3J0IGNsYXNzIFVuZXhwZWN0ZWRNZXRhZGF0YVZhbHVlRXJyb3IgZXh0ZW5kcyBFcnJvciB7fVxuXG4vKipcbiAqIENvbnZlcnRzIGEgZGlyZWN0aXZlIG1ldGFkYXRhIG9iamVjdCBpbnRvIGEgVHlwZVNjcmlwdCBleHByZXNzaW9uLiBUaHJvd3NcbiAqIGlmIG1ldGFkYXRhIGNhbm5vdCBiZSBjbGVhbmx5IGNvbnZlcnRlZC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbnZlcnREaXJlY3RpdmVNZXRhZGF0YVRvRXhwcmVzc2lvbihcbiAgICBtZXRhZGF0YTogYW55LCByZXNvbHZlU3ltYm9sSW1wb3J0OiAoc3ltYm9sOiBTdGF0aWNTeW1ib2wpID0+IHN0cmluZyB8IG51bGwsXG4gICAgY3JlYXRlSW1wb3J0OiAobW9kdWxlTmFtZTogc3RyaW5nLCBuYW1lOiBzdHJpbmcpID0+IHRzLkV4cHJlc3Npb24sXG4gICAgY29udmVydFByb3BlcnR5PzogKGtleTogc3RyaW5nLCB2YWx1ZTogYW55KSA9PiB0cy5FeHByZXNzaW9uIHwgbnVsbCk6IHRzLkV4cHJlc3Npb24ge1xuICBpZiAodHlwZW9mIG1ldGFkYXRhID09PSAnc3RyaW5nJykge1xuICAgIHJldHVybiB0cy5jcmVhdGVTdHJpbmdMaXRlcmFsKG1ldGFkYXRhKTtcbiAgfSBlbHNlIGlmIChBcnJheS5pc0FycmF5KG1ldGFkYXRhKSkge1xuICAgIHJldHVybiB0cy5jcmVhdGVBcnJheUxpdGVyYWwobWV0YWRhdGEubWFwKFxuICAgICAgICBlbCA9PiBjb252ZXJ0RGlyZWN0aXZlTWV0YWRhdGFUb0V4cHJlc3Npb24oXG4gICAgICAgICAgICBlbCwgcmVzb2x2ZVN5bWJvbEltcG9ydCwgY3JlYXRlSW1wb3J0LCBjb252ZXJ0UHJvcGVydHkpKSk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIG1ldGFkYXRhID09PSAnbnVtYmVyJykge1xuICAgIHJldHVybiB0cy5jcmVhdGVOdW1lcmljTGl0ZXJhbChtZXRhZGF0YS50b1N0cmluZygpKTtcbiAgfSBlbHNlIGlmICh0eXBlb2YgbWV0YWRhdGEgPT09ICdib29sZWFuJykge1xuICAgIHJldHVybiBtZXRhZGF0YSA/IHRzLmNyZWF0ZVRydWUoKSA6IHRzLmNyZWF0ZUZhbHNlKCk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIG1ldGFkYXRhID09PSAndW5kZWZpbmVkJykge1xuICAgIHJldHVybiB0cy5jcmVhdGVJZGVudGlmaWVyKCd1bmRlZmluZWQnKTtcbiAgfSBlbHNlIGlmICh0eXBlb2YgbWV0YWRhdGEgPT09ICdiaWdpbnQnKSB7XG4gICAgcmV0dXJuIHRzLmNyZWF0ZUJpZ0ludExpdGVyYWwobWV0YWRhdGEudG9TdHJpbmcoKSk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIG1ldGFkYXRhID09PSAnb2JqZWN0Jykge1xuICAgIC8vIEluIGNhc2UgdGhlcmUgaXMgYSBzdGF0aWMgc3ltYm9sIG9iamVjdCBwYXJ0IG9mIHRoZSBtZXRhZGF0YSwgdHJ5IHRvIHJlc29sdmVcbiAgICAvLyB0aGUgaW1wb3J0IGV4cHJlc3Npb24gb2YgdGhlIHN5bWJvbC4gSWYgbm8gaW1wb3J0IHBhdGggY291bGQgYmUgcmVzb2x2ZWQsIGFuXG4gICAgLy8gZXJyb3Igd2lsbCBiZSB0aHJvd24gYXMgdGhlIHN5bWJvbCBjYW5ub3QgYmUgY29udmVydGVkIGludG8gVHlwZVNjcmlwdCBBU1QuXG4gICAgaWYgKG1ldGFkYXRhIGluc3RhbmNlb2YgU3RhdGljU3ltYm9sKSB7XG4gICAgICBjb25zdCByZXNvbHZlZEltcG9ydCA9IHJlc29sdmVTeW1ib2xJbXBvcnQobWV0YWRhdGEpO1xuICAgICAgaWYgKHJlc29sdmVkSW1wb3J0ID09PSBudWxsKSB7XG4gICAgICAgIHRocm93IG5ldyBVbmV4cGVjdGVkTWV0YWRhdGFWYWx1ZUVycm9yKCk7XG4gICAgICB9XG4gICAgICByZXR1cm4gY3JlYXRlSW1wb3J0KHJlc29sdmVkSW1wb3J0LCBtZXRhZGF0YS5uYW1lKTtcbiAgICB9XG5cbiAgICBjb25zdCBsaXRlcmFsUHJvcGVydGllczogdHMuUHJvcGVydHlBc3NpZ25tZW50W10gPSBbXTtcblxuICAgIGZvciAoY29uc3Qga2V5IG9mIE9iamVjdC5rZXlzKG1ldGFkYXRhKSkge1xuICAgICAgY29uc3QgbWV0YWRhdGFWYWx1ZSA9IG1ldGFkYXRhW2tleV07XG4gICAgICBsZXQgcHJvcGVydHlWYWx1ZTogdHMuRXhwcmVzc2lvbnxudWxsID0gbnVsbDtcblxuICAgICAgLy8gQWxsb3dzIGN1c3RvbSBjb252ZXJzaW9uIG9mIHByb3BlcnRpZXMgaW4gYW4gb2JqZWN0LiBUaGlzIGlzIHVzZWZ1bCBmb3Igc3BlY2lhbFxuICAgICAgLy8gY2FzZXMgd2hlcmUgd2UgZG9uJ3Qgd2FudCB0byBzdG9yZSB0aGUgZW51bSB2YWx1ZXMgYXMgaW50ZWdlcnMsIGJ1dCByYXRoZXIgdXNlIHRoZVxuICAgICAgLy8gcmVhbCBlbnVtIHN5bWJvbC4gZS5nLiBpbnN0ZWFkIG9mIGAyYCB3ZSB3YW50IHRvIHVzZSBgVmlld0VuY2Fwc3VsYXRpb24uTm9uZWAuXG4gICAgICBpZiAoY29udmVydFByb3BlcnR5KSB7XG4gICAgICAgIHByb3BlcnR5VmFsdWUgPSBjb252ZXJ0UHJvcGVydHkoa2V5LCBtZXRhZGF0YVZhbHVlKTtcbiAgICAgIH1cblxuICAgICAgLy8gSW4gY2FzZSB0aGUgcHJvcGVydHkgdmFsdWUgaGFzIG5vdCBiZWVuIGFzc2lnbmVkIHRvIGFuIGV4cHJlc3Npb24sIHdlIGNvbnZlcnRcbiAgICAgIC8vIHRoZSByZXNvbHZlZCBtZXRhZGF0YSB2YWx1ZSBpbnRvIGEgVHlwZVNjcmlwdCBleHByZXNzaW9uLlxuICAgICAgaWYgKHByb3BlcnR5VmFsdWUgPT09IG51bGwpIHtcbiAgICAgICAgcHJvcGVydHlWYWx1ZSA9IGNvbnZlcnREaXJlY3RpdmVNZXRhZGF0YVRvRXhwcmVzc2lvbihcbiAgICAgICAgICAgIG1ldGFkYXRhVmFsdWUsIHJlc29sdmVTeW1ib2xJbXBvcnQsIGNyZWF0ZUltcG9ydCwgY29udmVydFByb3BlcnR5KTtcbiAgICAgIH1cblxuICAgICAgbGl0ZXJhbFByb3BlcnRpZXMucHVzaCh0cy5jcmVhdGVQcm9wZXJ0eUFzc2lnbm1lbnQoZ2V0UHJvcGVydHlOYW1lKGtleSksIHByb3BlcnR5VmFsdWUpKTtcbiAgICB9XG5cbiAgICByZXR1cm4gdHMuY3JlYXRlT2JqZWN0TGl0ZXJhbChsaXRlcmFsUHJvcGVydGllcywgdHJ1ZSk7XG4gIH1cblxuICB0aHJvdyBuZXcgVW5leHBlY3RlZE1ldGFkYXRhVmFsdWVFcnJvcigpO1xufVxuXG4vKipcbiAqIEdldHMgYSB2YWxpZCBwcm9wZXJ0eSBuYW1lIGZyb20gdGhlIGdpdmVuIHRleHQuIElmIHRoZSB0ZXh0IGNhbm5vdCBiZSB1c2VkXG4gKiBhcyB1bnF1b3RlZCBpZGVudGlmaWVyLCB0aGUgbmFtZSB3aWxsIGJlIHdyYXBwZWQgaW4gYSBzdHJpbmcgbGl0ZXJhbC5cbiAqL1xuZnVuY3Rpb24gZ2V0UHJvcGVydHlOYW1lKG5hbWU6IHN0cmluZyk6IHN0cmluZ3x0cy5TdHJpbmdMaXRlcmFsIHtcbiAgLy8gTWF0Y2hlcyB0aGUgbW9zdCBjb21tb24gaWRlbnRpZmllcnMgdGhhdCBkbyBub3QgbmVlZCBxdW90ZXMuIENvbnN0cnVjdGluZyBhXG4gIC8vIHJlZ3VsYXIgZXhwcmVzc2lvbiB0aGF0IG1hdGNoZXMgdGhlIEVDTUFTY3JpcHQgc3BlY2lmaWNhdGlvbiBpbiBvcmRlciB0byBkZXRlcm1pbmVcbiAgLy8gd2hldGhlciBxdW90ZXMgYXJlIG5lZWRlZCBpcyBvdXQgb2Ygc2NvcGUgZm9yIHRoaXMgbWlncmF0aW9uLiBGb3IgdGhvc2UgbW9yZSBjb21wbGV4XG4gIC8vIHByb3BlcnR5IG5hbWVzLCB3ZSBqdXN0IGFsd2F5cyB1c2UgcXVvdGVzICh3aGVuIGNvbnN0cnVjdGluZyBBU1QgZnJvbSBtZXRhZGF0YSkuXG4gIGlmICgvXlthLXpBLVpfJF0rJC8udGVzdChuYW1lKSkge1xuICAgIHJldHVybiBuYW1lO1xuICB9XG4gIHJldHVybiB0cy5jcmVhdGVTdHJpbmdMaXRlcmFsKG5hbWUpO1xufVxuIl19