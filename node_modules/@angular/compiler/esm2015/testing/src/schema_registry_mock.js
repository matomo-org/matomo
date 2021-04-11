/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { core } from '@angular/compiler';
export class MockSchemaRegistry {
    constructor(existingProperties, attrPropMapping, existingElements, invalidProperties, invalidAttributes) {
        this.existingProperties = existingProperties;
        this.attrPropMapping = attrPropMapping;
        this.existingElements = existingElements;
        this.invalidProperties = invalidProperties;
        this.invalidAttributes = invalidAttributes;
    }
    hasProperty(tagName, property, schemas) {
        const value = this.existingProperties[property];
        return value === void 0 ? true : value;
    }
    hasElement(tagName, schemaMetas) {
        const value = this.existingElements[tagName.toLowerCase()];
        return value === void 0 ? true : value;
    }
    allKnownElementNames() {
        return Object.keys(this.existingElements);
    }
    securityContext(selector, property, isAttribute) {
        return core.SecurityContext.NONE;
    }
    getMappedPropName(attrName) {
        return this.attrPropMapping[attrName] || attrName;
    }
    getDefaultComponentElementName() {
        return 'ng-component';
    }
    validateProperty(name) {
        if (this.invalidProperties.indexOf(name) > -1) {
            return { error: true, msg: `Binding to property '${name}' is disallowed for security reasons` };
        }
        else {
            return { error: false };
        }
    }
    validateAttribute(name) {
        if (this.invalidAttributes.indexOf(name) > -1) {
            return {
                error: true,
                msg: `Binding to attribute '${name}' is disallowed for security reasons`
            };
        }
        else {
            return { error: false };
        }
    }
    normalizeAnimationStyleProperty(propName) {
        return propName;
    }
    normalizeAnimationStyleValue(camelCaseProp, userProvidedProp, val) {
        return { error: null, value: val.toString() };
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2NoZW1hX3JlZ2lzdHJ5X21vY2suanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci90ZXN0aW5nL3NyYy9zY2hlbWFfcmVnaXN0cnlfbW9jay50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLEVBQUMsSUFBSSxFQUF3QixNQUFNLG1CQUFtQixDQUFDO0FBRTlELE1BQU0sT0FBTyxrQkFBa0I7SUFDN0IsWUFDVyxrQkFBNEMsRUFDNUMsZUFBd0MsRUFDeEMsZ0JBQTBDLEVBQVMsaUJBQWdDLEVBQ25GLGlCQUFnQztRQUhoQyx1QkFBa0IsR0FBbEIsa0JBQWtCLENBQTBCO1FBQzVDLG9CQUFlLEdBQWYsZUFBZSxDQUF5QjtRQUN4QyxxQkFBZ0IsR0FBaEIsZ0JBQWdCLENBQTBCO1FBQVMsc0JBQWlCLEdBQWpCLGlCQUFpQixDQUFlO1FBQ25GLHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBZTtJQUFHLENBQUM7SUFFL0MsV0FBVyxDQUFDLE9BQWUsRUFBRSxRQUFnQixFQUFFLE9BQThCO1FBQzNFLE1BQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxrQkFBa0IsQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUNoRCxPQUFPLEtBQUssS0FBSyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUM7SUFDekMsQ0FBQztJQUVELFVBQVUsQ0FBQyxPQUFlLEVBQUUsV0FBa0M7UUFDNUQsTUFBTSxLQUFLLEdBQUcsSUFBSSxDQUFDLGdCQUFnQixDQUFDLE9BQU8sQ0FBQyxXQUFXLEVBQUUsQ0FBQyxDQUFDO1FBQzNELE9BQU8sS0FBSyxLQUFLLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQztJQUN6QyxDQUFDO0lBRUQsb0JBQW9CO1FBQ2xCLE9BQU8sTUFBTSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsZ0JBQWdCLENBQUMsQ0FBQztJQUM1QyxDQUFDO0lBRUQsZUFBZSxDQUFDLFFBQWdCLEVBQUUsUUFBZ0IsRUFBRSxXQUFvQjtRQUN0RSxPQUFPLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDO0lBQ25DLENBQUM7SUFFRCxpQkFBaUIsQ0FBQyxRQUFnQjtRQUNoQyxPQUFPLElBQUksQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDLElBQUksUUFBUSxDQUFDO0lBQ3BELENBQUM7SUFFRCw4QkFBOEI7UUFDNUIsT0FBTyxjQUFjLENBQUM7SUFDeEIsQ0FBQztJQUVELGdCQUFnQixDQUFDLElBQVk7UUFDM0IsSUFBSSxJQUFJLENBQUMsaUJBQWlCLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFFO1lBQzdDLE9BQU8sRUFBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLEdBQUcsRUFBRSx3QkFBd0IsSUFBSSxzQ0FBc0MsRUFBQyxDQUFDO1NBQy9GO2FBQU07WUFDTCxPQUFPLEVBQUMsS0FBSyxFQUFFLEtBQUssRUFBQyxDQUFDO1NBQ3ZCO0lBQ0gsQ0FBQztJQUVELGlCQUFpQixDQUFDLElBQVk7UUFDNUIsSUFBSSxJQUFJLENBQUMsaUJBQWlCLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFFO1lBQzdDLE9BQU87Z0JBQ0wsS0FBSyxFQUFFLElBQUk7Z0JBQ1gsR0FBRyxFQUFFLHlCQUF5QixJQUFJLHNDQUFzQzthQUN6RSxDQUFDO1NBQ0g7YUFBTTtZQUNMLE9BQU8sRUFBQyxLQUFLLEVBQUUsS0FBSyxFQUFDLENBQUM7U0FDdkI7SUFDSCxDQUFDO0lBRUQsK0JBQStCLENBQUMsUUFBZ0I7UUFDOUMsT0FBTyxRQUFRLENBQUM7SUFDbEIsQ0FBQztJQUNELDRCQUE0QixDQUFDLGFBQXFCLEVBQUUsZ0JBQXdCLEVBQUUsR0FBa0I7UUFFOUYsT0FBTyxFQUFDLEtBQUssRUFBRSxJQUFLLEVBQUUsS0FBSyxFQUFFLEdBQUcsQ0FBQyxRQUFRLEVBQUUsRUFBQyxDQUFDO0lBQy9DLENBQUM7Q0FDRiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge2NvcmUsIEVsZW1lbnRTY2hlbWFSZWdpc3RyeX0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXInO1xuXG5leHBvcnQgY2xhc3MgTW9ja1NjaGVtYVJlZ2lzdHJ5IGltcGxlbWVudHMgRWxlbWVudFNjaGVtYVJlZ2lzdHJ5IHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBwdWJsaWMgZXhpc3RpbmdQcm9wZXJ0aWVzOiB7W2tleTogc3RyaW5nXTogYm9vbGVhbn0sXG4gICAgICBwdWJsaWMgYXR0clByb3BNYXBwaW5nOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSxcbiAgICAgIHB1YmxpYyBleGlzdGluZ0VsZW1lbnRzOiB7W2tleTogc3RyaW5nXTogYm9vbGVhbn0sIHB1YmxpYyBpbnZhbGlkUHJvcGVydGllczogQXJyYXk8c3RyaW5nPixcbiAgICAgIHB1YmxpYyBpbnZhbGlkQXR0cmlidXRlczogQXJyYXk8c3RyaW5nPikge31cblxuICBoYXNQcm9wZXJ0eSh0YWdOYW1lOiBzdHJpbmcsIHByb3BlcnR5OiBzdHJpbmcsIHNjaGVtYXM6IGNvcmUuU2NoZW1hTWV0YWRhdGFbXSk6IGJvb2xlYW4ge1xuICAgIGNvbnN0IHZhbHVlID0gdGhpcy5leGlzdGluZ1Byb3BlcnRpZXNbcHJvcGVydHldO1xuICAgIHJldHVybiB2YWx1ZSA9PT0gdm9pZCAwID8gdHJ1ZSA6IHZhbHVlO1xuICB9XG5cbiAgaGFzRWxlbWVudCh0YWdOYW1lOiBzdHJpbmcsIHNjaGVtYU1ldGFzOiBjb3JlLlNjaGVtYU1ldGFkYXRhW10pOiBib29sZWFuIHtcbiAgICBjb25zdCB2YWx1ZSA9IHRoaXMuZXhpc3RpbmdFbGVtZW50c1t0YWdOYW1lLnRvTG93ZXJDYXNlKCldO1xuICAgIHJldHVybiB2YWx1ZSA9PT0gdm9pZCAwID8gdHJ1ZSA6IHZhbHVlO1xuICB9XG5cbiAgYWxsS25vd25FbGVtZW50TmFtZXMoKTogc3RyaW5nW10ge1xuICAgIHJldHVybiBPYmplY3Qua2V5cyh0aGlzLmV4aXN0aW5nRWxlbWVudHMpO1xuICB9XG5cbiAgc2VjdXJpdHlDb250ZXh0KHNlbGVjdG9yOiBzdHJpbmcsIHByb3BlcnR5OiBzdHJpbmcsIGlzQXR0cmlidXRlOiBib29sZWFuKTogY29yZS5TZWN1cml0eUNvbnRleHQge1xuICAgIHJldHVybiBjb3JlLlNlY3VyaXR5Q29udGV4dC5OT05FO1xuICB9XG5cbiAgZ2V0TWFwcGVkUHJvcE5hbWUoYXR0ck5hbWU6IHN0cmluZyk6IHN0cmluZyB7XG4gICAgcmV0dXJuIHRoaXMuYXR0clByb3BNYXBwaW5nW2F0dHJOYW1lXSB8fCBhdHRyTmFtZTtcbiAgfVxuXG4gIGdldERlZmF1bHRDb21wb25lbnRFbGVtZW50TmFtZSgpOiBzdHJpbmcge1xuICAgIHJldHVybiAnbmctY29tcG9uZW50JztcbiAgfVxuXG4gIHZhbGlkYXRlUHJvcGVydHkobmFtZTogc3RyaW5nKToge2Vycm9yOiBib29sZWFuLCBtc2c/OiBzdHJpbmd9IHtcbiAgICBpZiAodGhpcy5pbnZhbGlkUHJvcGVydGllcy5pbmRleE9mKG5hbWUpID4gLTEpIHtcbiAgICAgIHJldHVybiB7ZXJyb3I6IHRydWUsIG1zZzogYEJpbmRpbmcgdG8gcHJvcGVydHkgJyR7bmFtZX0nIGlzIGRpc2FsbG93ZWQgZm9yIHNlY3VyaXR5IHJlYXNvbnNgfTtcbiAgICB9IGVsc2Uge1xuICAgICAgcmV0dXJuIHtlcnJvcjogZmFsc2V9O1xuICAgIH1cbiAgfVxuXG4gIHZhbGlkYXRlQXR0cmlidXRlKG5hbWU6IHN0cmluZyk6IHtlcnJvcjogYm9vbGVhbiwgbXNnPzogc3RyaW5nfSB7XG4gICAgaWYgKHRoaXMuaW52YWxpZEF0dHJpYnV0ZXMuaW5kZXhPZihuYW1lKSA+IC0xKSB7XG4gICAgICByZXR1cm4ge1xuICAgICAgICBlcnJvcjogdHJ1ZSxcbiAgICAgICAgbXNnOiBgQmluZGluZyB0byBhdHRyaWJ1dGUgJyR7bmFtZX0nIGlzIGRpc2FsbG93ZWQgZm9yIHNlY3VyaXR5IHJlYXNvbnNgXG4gICAgICB9O1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4ge2Vycm9yOiBmYWxzZX07XG4gICAgfVxuICB9XG5cbiAgbm9ybWFsaXplQW5pbWF0aW9uU3R5bGVQcm9wZXJ0eShwcm9wTmFtZTogc3RyaW5nKTogc3RyaW5nIHtcbiAgICByZXR1cm4gcHJvcE5hbWU7XG4gIH1cbiAgbm9ybWFsaXplQW5pbWF0aW9uU3R5bGVWYWx1ZShjYW1lbENhc2VQcm9wOiBzdHJpbmcsIHVzZXJQcm92aWRlZFByb3A6IHN0cmluZywgdmFsOiBzdHJpbmd8bnVtYmVyKTpcbiAgICAgIHtlcnJvcjogc3RyaW5nLCB2YWx1ZTogc3RyaW5nfSB7XG4gICAgcmV0dXJuIHtlcnJvcjogbnVsbCEsIHZhbHVlOiB2YWwudG9TdHJpbmcoKX07XG4gIH1cbn1cbiJdfQ==