/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { areAllEquivalent } from '../output/output_ast';
import { TypeScriptEmitter } from '../output/ts_emitter';
export class GeneratedFile {
    constructor(srcFileUrl, genFileUrl, sourceOrStmts) {
        this.srcFileUrl = srcFileUrl;
        this.genFileUrl = genFileUrl;
        if (typeof sourceOrStmts === 'string') {
            this.source = sourceOrStmts;
            this.stmts = null;
        }
        else {
            this.source = null;
            this.stmts = sourceOrStmts;
        }
    }
    isEquivalent(other) {
        if (this.genFileUrl !== other.genFileUrl) {
            return false;
        }
        if (this.source) {
            return this.source === other.source;
        }
        if (other.stmts == null) {
            return false;
        }
        // Note: the constructor guarantees that if this.source is not filled,
        // then this.stmts is.
        return areAllEquivalent(this.stmts, other.stmts);
    }
}
export function toTypeScript(file, preamble = '') {
    if (!file.stmts) {
        throw new Error(`Illegal state: No stmts present on GeneratedFile ${file.genFileUrl}`);
    }
    return new TypeScriptEmitter().emitStatements(file.genFileUrl, file.stmts, preamble);
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2VuZXJhdGVkX2ZpbGUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvYW90L2dlbmVyYXRlZF9maWxlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBQyxnQkFBZ0IsRUFBWSxNQUFNLHNCQUFzQixDQUFDO0FBQ2pFLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBRXZELE1BQU0sT0FBTyxhQUFhO0lBSXhCLFlBQ1csVUFBa0IsRUFBUyxVQUFrQixFQUFFLGFBQWlDO1FBQWhGLGVBQVUsR0FBVixVQUFVLENBQVE7UUFBUyxlQUFVLEdBQVYsVUFBVSxDQUFRO1FBQ3RELElBQUksT0FBTyxhQUFhLEtBQUssUUFBUSxFQUFFO1lBQ3JDLElBQUksQ0FBQyxNQUFNLEdBQUcsYUFBYSxDQUFDO1lBQzVCLElBQUksQ0FBQyxLQUFLLEdBQUcsSUFBSSxDQUFDO1NBQ25CO2FBQU07WUFDTCxJQUFJLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQztZQUNuQixJQUFJLENBQUMsS0FBSyxHQUFHLGFBQWEsQ0FBQztTQUM1QjtJQUNILENBQUM7SUFFRCxZQUFZLENBQUMsS0FBb0I7UUFDL0IsSUFBSSxJQUFJLENBQUMsVUFBVSxLQUFLLEtBQUssQ0FBQyxVQUFVLEVBQUU7WUFDeEMsT0FBTyxLQUFLLENBQUM7U0FDZDtRQUNELElBQUksSUFBSSxDQUFDLE1BQU0sRUFBRTtZQUNmLE9BQU8sSUFBSSxDQUFDLE1BQU0sS0FBSyxLQUFLLENBQUMsTUFBTSxDQUFDO1NBQ3JDO1FBQ0QsSUFBSSxLQUFLLENBQUMsS0FBSyxJQUFJLElBQUksRUFBRTtZQUN2QixPQUFPLEtBQUssQ0FBQztTQUNkO1FBQ0Qsc0VBQXNFO1FBQ3RFLHNCQUFzQjtRQUN0QixPQUFPLGdCQUFnQixDQUFDLElBQUksQ0FBQyxLQUFNLEVBQUUsS0FBSyxDQUFDLEtBQU0sQ0FBQyxDQUFDO0lBQ3JELENBQUM7Q0FDRjtBQUVELE1BQU0sVUFBVSxZQUFZLENBQUMsSUFBbUIsRUFBRSxXQUFtQixFQUFFO0lBQ3JFLElBQUksQ0FBQyxJQUFJLENBQUMsS0FBSyxFQUFFO1FBQ2YsTUFBTSxJQUFJLEtBQUssQ0FBQyxvREFBb0QsSUFBSSxDQUFDLFVBQVUsRUFBRSxDQUFDLENBQUM7S0FDeEY7SUFDRCxPQUFPLElBQUksaUJBQWlCLEVBQUUsQ0FBQyxjQUFjLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxJQUFJLENBQUMsS0FBSyxFQUFFLFFBQVEsQ0FBQyxDQUFDO0FBQ3ZGLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHthcmVBbGxFcXVpdmFsZW50LCBTdGF0ZW1lbnR9IGZyb20gJy4uL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7VHlwZVNjcmlwdEVtaXR0ZXJ9IGZyb20gJy4uL291dHB1dC90c19lbWl0dGVyJztcblxuZXhwb3J0IGNsYXNzIEdlbmVyYXRlZEZpbGUge1xuICBwdWJsaWMgc291cmNlOiBzdHJpbmd8bnVsbDtcbiAgcHVibGljIHN0bXRzOiBTdGF0ZW1lbnRbXXxudWxsO1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHNyY0ZpbGVVcmw6IHN0cmluZywgcHVibGljIGdlbkZpbGVVcmw6IHN0cmluZywgc291cmNlT3JTdG10czogc3RyaW5nfFN0YXRlbWVudFtdKSB7XG4gICAgaWYgKHR5cGVvZiBzb3VyY2VPclN0bXRzID09PSAnc3RyaW5nJykge1xuICAgICAgdGhpcy5zb3VyY2UgPSBzb3VyY2VPclN0bXRzO1xuICAgICAgdGhpcy5zdG10cyA9IG51bGw7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMuc291cmNlID0gbnVsbDtcbiAgICAgIHRoaXMuc3RtdHMgPSBzb3VyY2VPclN0bXRzO1xuICAgIH1cbiAgfVxuXG4gIGlzRXF1aXZhbGVudChvdGhlcjogR2VuZXJhdGVkRmlsZSk6IGJvb2xlYW4ge1xuICAgIGlmICh0aGlzLmdlbkZpbGVVcmwgIT09IG90aGVyLmdlbkZpbGVVcmwpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgaWYgKHRoaXMuc291cmNlKSB7XG4gICAgICByZXR1cm4gdGhpcy5zb3VyY2UgPT09IG90aGVyLnNvdXJjZTtcbiAgICB9XG4gICAgaWYgKG90aGVyLnN0bXRzID09IG51bGwpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgLy8gTm90ZTogdGhlIGNvbnN0cnVjdG9yIGd1YXJhbnRlZXMgdGhhdCBpZiB0aGlzLnNvdXJjZSBpcyBub3QgZmlsbGVkLFxuICAgIC8vIHRoZW4gdGhpcy5zdG10cyBpcy5cbiAgICByZXR1cm4gYXJlQWxsRXF1aXZhbGVudCh0aGlzLnN0bXRzISwgb3RoZXIuc3RtdHMhKTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gdG9UeXBlU2NyaXB0KGZpbGU6IEdlbmVyYXRlZEZpbGUsIHByZWFtYmxlOiBzdHJpbmcgPSAnJyk6IHN0cmluZyB7XG4gIGlmICghZmlsZS5zdG10cykge1xuICAgIHRocm93IG5ldyBFcnJvcihgSWxsZWdhbCBzdGF0ZTogTm8gc3RtdHMgcHJlc2VudCBvbiBHZW5lcmF0ZWRGaWxlICR7ZmlsZS5nZW5GaWxlVXJsfWApO1xuICB9XG4gIHJldHVybiBuZXcgVHlwZVNjcmlwdEVtaXR0ZXIoKS5lbWl0U3RhdGVtZW50cyhmaWxlLmdlbkZpbGVVcmwsIGZpbGUuc3RtdHMsIHByZWFtYmxlKTtcbn1cbiJdfQ==