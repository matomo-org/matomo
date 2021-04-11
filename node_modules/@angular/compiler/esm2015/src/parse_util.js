/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as chars from './chars';
import { identifierModuleUrl, identifierName } from './compile_metadata';
export class ParseLocation {
    constructor(file, offset, line, col) {
        this.file = file;
        this.offset = offset;
        this.line = line;
        this.col = col;
    }
    toString() {
        return this.offset != null ? `${this.file.url}@${this.line}:${this.col}` : this.file.url;
    }
    moveBy(delta) {
        const source = this.file.content;
        const len = source.length;
        let offset = this.offset;
        let line = this.line;
        let col = this.col;
        while (offset > 0 && delta < 0) {
            offset--;
            delta++;
            const ch = source.charCodeAt(offset);
            if (ch == chars.$LF) {
                line--;
                const priorLine = source.substr(0, offset - 1).lastIndexOf(String.fromCharCode(chars.$LF));
                col = priorLine > 0 ? offset - priorLine : offset;
            }
            else {
                col--;
            }
        }
        while (offset < len && delta > 0) {
            const ch = source.charCodeAt(offset);
            offset++;
            delta--;
            if (ch == chars.$LF) {
                line++;
                col = 0;
            }
            else {
                col++;
            }
        }
        return new ParseLocation(this.file, offset, line, col);
    }
    // Return the source around the location
    // Up to `maxChars` or `maxLines` on each side of the location
    getContext(maxChars, maxLines) {
        const content = this.file.content;
        let startOffset = this.offset;
        if (startOffset != null) {
            if (startOffset > content.length - 1) {
                startOffset = content.length - 1;
            }
            let endOffset = startOffset;
            let ctxChars = 0;
            let ctxLines = 0;
            while (ctxChars < maxChars && startOffset > 0) {
                startOffset--;
                ctxChars++;
                if (content[startOffset] == '\n') {
                    if (++ctxLines == maxLines) {
                        break;
                    }
                }
            }
            ctxChars = 0;
            ctxLines = 0;
            while (ctxChars < maxChars && endOffset < content.length - 1) {
                endOffset++;
                ctxChars++;
                if (content[endOffset] == '\n') {
                    if (++ctxLines == maxLines) {
                        break;
                    }
                }
            }
            return {
                before: content.substring(startOffset, this.offset),
                after: content.substring(this.offset, endOffset + 1),
            };
        }
        return null;
    }
}
export class ParseSourceFile {
    constructor(content, url) {
        this.content = content;
        this.url = url;
    }
}
export class ParseSourceSpan {
    /**
     * Create an object that holds information about spans of tokens/nodes captured during
     * lexing/parsing of text.
     *
     * @param start
     * The location of the start of the span (having skipped leading trivia).
     * Skipping leading trivia makes source-spans more "user friendly", since things like HTML
     * elements will appear to begin at the start of the opening tag, rather than at the start of any
     * leading trivia, which could include newlines.
     *
     * @param end
     * The location of the end of the span.
     *
     * @param fullStart
     * The start of the token without skipping the leading trivia.
     * This is used by tooling that splits tokens further, such as extracting Angular interpolations
     * from text tokens. Such tooling creates new source-spans relative to the original token's
     * source-span. If leading trivia characters have been skipped then the new source-spans may be
     * incorrectly offset.
     *
     * @param details
     * Additional information (such as identifier names) that should be associated with the span.
     */
    constructor(start, end, fullStart = start, details = null) {
        this.start = start;
        this.end = end;
        this.fullStart = fullStart;
        this.details = details;
    }
    toString() {
        return this.start.file.content.substring(this.start.offset, this.end.offset);
    }
}
export var ParseErrorLevel;
(function (ParseErrorLevel) {
    ParseErrorLevel[ParseErrorLevel["WARNING"] = 0] = "WARNING";
    ParseErrorLevel[ParseErrorLevel["ERROR"] = 1] = "ERROR";
})(ParseErrorLevel || (ParseErrorLevel = {}));
export class ParseError {
    constructor(span, msg, level = ParseErrorLevel.ERROR) {
        this.span = span;
        this.msg = msg;
        this.level = level;
    }
    contextualMessage() {
        const ctx = this.span.start.getContext(100, 3);
        return ctx ? `${this.msg} ("${ctx.before}[${ParseErrorLevel[this.level]} ->]${ctx.after}")` :
            this.msg;
    }
    toString() {
        const details = this.span.details ? `, ${this.span.details}` : '';
        return `${this.contextualMessage()}: ${this.span.start}${details}`;
    }
}
export function typeSourceSpan(kind, type) {
    const moduleUrl = identifierModuleUrl(type);
    const sourceFileName = moduleUrl != null ? `in ${kind} ${identifierName(type)} in ${moduleUrl}` :
        `in ${kind} ${identifierName(type)}`;
    const sourceFile = new ParseSourceFile('', sourceFileName);
    return new ParseSourceSpan(new ParseLocation(sourceFile, -1, -1, -1), new ParseLocation(sourceFile, -1, -1, -1));
}
/**
 * Generates Source Span object for a given R3 Type for JIT mode.
 *
 * @param kind Component or Directive.
 * @param typeName name of the Component or Directive.
 * @param sourceUrl reference to Component or Directive source.
 * @returns instance of ParseSourceSpan that represent a given Component or Directive.
 */
export function r3JitTypeSourceSpan(kind, typeName, sourceUrl) {
    const sourceFileName = `in ${kind} ${typeName} in ${sourceUrl}`;
    const sourceFile = new ParseSourceFile('', sourceFileName);
    return new ParseSourceSpan(new ParseLocation(sourceFile, -1, -1, -1), new ParseLocation(sourceFile, -1, -1, -1));
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGFyc2VfdXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9wYXJzZV91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUNILE9BQU8sS0FBSyxLQUFLLE1BQU0sU0FBUyxDQUFDO0FBQ2pDLE9BQU8sRUFBNEIsbUJBQW1CLEVBQUUsY0FBYyxFQUFDLE1BQU0sb0JBQW9CLENBQUM7QUFFbEcsTUFBTSxPQUFPLGFBQWE7SUFDeEIsWUFDVyxJQUFxQixFQUFTLE1BQWMsRUFBUyxJQUFZLEVBQ2pFLEdBQVc7UUFEWCxTQUFJLEdBQUosSUFBSSxDQUFpQjtRQUFTLFdBQU0sR0FBTixNQUFNLENBQVE7UUFBUyxTQUFJLEdBQUosSUFBSSxDQUFRO1FBQ2pFLFFBQUcsR0FBSCxHQUFHLENBQVE7SUFBRyxDQUFDO0lBRTFCLFFBQVE7UUFDTixPQUFPLElBQUksQ0FBQyxNQUFNLElBQUksSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFJLElBQUksQ0FBQyxJQUFJLElBQUksSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztJQUMzRixDQUFDO0lBRUQsTUFBTSxDQUFDLEtBQWE7UUFDbEIsTUFBTSxNQUFNLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUM7UUFDakMsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLE1BQU0sQ0FBQztRQUMxQixJQUFJLE1BQU0sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDO1FBQ3pCLElBQUksSUFBSSxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUM7UUFDckIsSUFBSSxHQUFHLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQztRQUNuQixPQUFPLE1BQU0sR0FBRyxDQUFDLElBQUksS0FBSyxHQUFHLENB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QyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztBQUM1RixDQUFDO0FBRUQ7Ozs7Ozs7R0FPRztBQUNILE1BQU0sVUFBVSxtQkFBbUIsQ0FDL0IsSUFBWSxFQUFFLFFBQWdCLEVBQUUsU0FBaUI7SUFDbkQsTUFBTSxjQUFjLEdBQUcsTUFBTSxJQUFJLElBQUksUUFBUSxPQUFPLFNBQVMsRUFBRSxDQUFDO0lBQ2hFLE1BQU0sVUFBVSxHQUFHLElBQUksZUFBZSxDQUFDLEVBQUUsRUFBRSxjQUFjLENBQUMsQ0FBQztJQUMzRCxPQUFPLElBQUksZUFBZSxDQUN0QixJQUFJLGFBQWEsQ0FBQyxVQUFVLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxJQUFJLGFBQWEsQ0FBQyxVQUFVLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO0FBQzVGLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCAqIGFzIGNoYXJzIGZyb20gJy4vY2hhcnMnO1xuaW1wb3J0IHtDb21waWxlSWRlbnRpZmllck1ldGFkYXRhLCBpZGVudGlmaWVyTW9kdWxlVXJsLCBpZGVudGlmaWVyTmFtZX0gZnJvbSAnLi9jb21waWxlX21ldGFkYXRhJztcblxuZXhwb3J0IGNsYXNzIFBhcnNlTG9jYXRpb24ge1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyBmaWxlOiBQYXJzZVNvdXJjZUZpbGUsIHB1YmxpYyBvZmZzZXQ6IG51bWJlciwgcHVibGljIGxpbmU6IG51bWJlcixcbiAgICAgIHB1YmxpYyBjb2w6IG51bWJlcikge31cblxuICB0b1N0cmluZygpOiBzdHJpbmcge1xuICAgIHJldHVybiB0aGlzLm9mZnNldCAhPSBudWxsID8gYCR7dGhpcy5maWxlLnVybH1AJHt0aGlzLmxpbmV9OiR7dGhpcy5jb2x9YCA6IHRoaXMuZmlsZS51cmw7XG4gIH1cblxuICBtb3ZlQnkoZGVsdGE6IG51bWJlcik6IFBhcnNlTG9jYXRpb24ge1xuICAgIGNvbnN0IHNvdXJjZSA9IHRoaXMuZmlsZS5jb250ZW50O1xuICAgIGNvbnN0IGxlbiA9IHNvdXJjZS5sZW5ndGg7XG4gICAgbGV0IG9mZnNldCA9IHRoaXMub2Zmc2V0O1xuICAgIGxldCBsaW5lID0gdGhpcy5saW5lO1xuICAgIGxldCBjb2wgPSB0aGlzLmNvbDtcbiAgICB3aGlsZSAob2Zmc2V0ID4gMCAmJiBkZWx0YSA8IDApIHtcbiAgICAgIG9mZnNldC0tO1xuICAgICAgZGVsdGErKztcbiAgICAgIGNvbnN0IGNoID0gc291cmNlLmNoYXJDb2RlQXQob2Zmc2V0KTtcbiAgICAgIGlmIChjaCA9PSBjaGFycy4kTEYpIHtcbiAgICAgICAgbGluZS0tO1xuICAgICAgICBjb25zdCBwcmlvckxpbmUgPSBzb3VyY2Uuc3Vic3RyKDAsIG9mZnNldCAtIDEpLmxhc3RJbmRleE9mKFN0cmluZy5mcm9tQ2hhckNvZGUoY2hhcnMuJExGKSk7XG4gICAgICAgIGNvbCA9IHByaW9yTGluZSA+IDAgPyBvZmZzZXQgLSBwcmlvckxpbmUgOiBvZmZzZXQ7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBjb2wtLTtcbiAgICAgIH1cbiAgICB9XG4gICAgd2hpbGUgKG9mZnNldCA8IGxlbiAmJiBkZWx0YSA+IDApIHtcbiAgICAgIGNvbnN0IGNoID0gc291cmNlLmNoYXJDb2RlQXQob2Zmc2V0KTtcbiAgICAgIG9mZnNldCsrO1xuICAgICAgZGVsdGEtLTtcbiAgICAgIGlmIChjaCA9PSBjaGFycy4kTEYpIHtcbiAgICAgICAgbGluZSsrO1xuICAgICAgICBjb2wgPSAwO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgY29sKys7XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiBuZXcgUGFyc2VMb2NhdGlvbih0aGlzLmZpbGUsIG9mZnNldCwgbGluZSwgY29sKTtcbiAgfVxuXG4gIC8vIFJldHVybiB0aGUgc291cmNlIGFyb3VuZCB0aGUgbG9jYXRpb25cbiAgLy8gVXAgdG8gYG1heENoYXJzYCBvciBgbWF4TGluZXNgIG9uIGVhY2ggc2lkZSBvZiB0aGUgbG9jYXRpb25cbiAgZ2V0Q29udGV4dChtYXhDaGFyczogbnVtYmVyLCBtYXhMaW5lczogbnVtYmVyKToge2JlZm9yZTogc3RyaW5nLCBhZnRlcjogc3RyaW5nfXxudWxsIHtcbiAgICBjb25zdCBjb250ZW50ID0gdGhpcy5maWxlLmNvbnRlbnQ7XG4gICAgbGV0IHN0YXJ0T2Zmc2V0ID0gdGhpcy5vZmZzZXQ7XG5cbiAgICBpZiAoc3RhcnRPZmZzZXQgIT0gbnVsbCkge1xuICAgICAgaWYgKHN0YXJ0T2Zmc2V0ID4gY29udGVudC5sZW5ndGggLSAxKSB7XG4gICAgICAgIHN0YXJ0T2Zmc2V0ID0gY29udGVudC5sZW5ndGggLSAxO1xuICAgICAgfVxuICAgICAgbGV0IGVuZE9mZnNldCA9IHN0YXJ0T2Zmc2V0O1xuICAgICAgbGV0IGN0eENoYXJzID0gMDtcbiAgICAgIGxldCBjdHhMaW5lcyA9IDA7XG5cbiAgICAgIHdoaWxlIChjdHhDaGFycyA8IG1heENoYXJzICYmIHN0YXJ0T2Zmc2V0ID4gMCkge1xuICAgICAgICBzdGFydE9mZnNldC0tO1xuICAgICAgICBjdHhDaGFycysrO1xuICAgICAgICBpZiAoY29udGVudFtzdGFydE9mZnNldF0gPT0gJ1xcbicpIHtcbiAgICAgICAgICBpZiAoKytjdHhMaW5lcyA9PSBtYXhMaW5lcykge1xuICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIGN0eENoYXJzID0gMDtcbiAgICAgIGN0eExpbmVzID0gMDtcbiAgICAgIHdoaWxlIChjdHhDaGFycyA8IG1heENoYXJzICYmIGVuZE9mZnNldCA8IGNvbnRlbnQubGVuZ3RoIC0gMSkge1xuICAgICAgICBlbmRPZmZzZXQrKztcbiAgICAgICAgY3R4Q2hhcnMrKztcbiAgICAgICAgaWYgKGNvbnRlbnRbZW5kT2Zmc2V0XSA9PSAnXFxuJykge1xuICAgICAgICAgIGlmICgrK2N0eExpbmVzID09IG1heExpbmVzKSB7XG4gICAgICAgICAgICBicmVhaztcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgcmV0dXJuIHtcbiAgICAgICAgYmVmb3JlOiBjb250ZW50LnN1YnN0cmluZyhzdGFydE9mZnNldCwgdGhpcy5vZmZzZXQpLFxuICAgICAgICBhZnRlcjogY29udGVudC5zdWJzdHJpbmcodGhpcy5vZmZzZXQsIGVuZE9mZnNldCArIDEpLFxuICAgICAgfTtcbiAgICB9XG5cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgUGFyc2VTb3VyY2VGaWxlIHtcbiAgY29uc3RydWN0b3IocHVibGljIGNvbnRlbnQ6IHN0cmluZywgcHVibGljIHVybDogc3RyaW5nKSB7fVxufVxuXG5leHBvcnQgY2xhc3MgUGFyc2VTb3VyY2VTcGFuIHtcbiAgLyoqXG4gICAqIENyZWF0ZSBhbiBvYmplY3QgdGhhdCBob2xkcyBpbmZvcm1hdGlvbiBhYm91dCBzcGFucyBvZiB0b2tlbnMvbm9kZXMgY2FwdHVyZWQgZHVyaW5nXG4gICAqIGxleGluZy9wYXJzaW5nIG9mIHRleHQuXG4gICAqXG4gICAqIEBwYXJhbSBzdGFydFxuICAgKiBUaGUgbG9jYXRpb24gb2YgdGhlIHN0YXJ0IG9mIHRoZSBzcGFuIChoYXZpbmcgc2tpcHBlZCBsZWFkaW5nIHRyaXZpYSkuXG4gICAqIFNraXBwaW5nIGxlYWRpbmcgdHJpdmlhIG1ha2VzIHNvdXJjZS1zcGFucyBtb3JlIFwidXNlciBmcmllbmRseVwiLCBzaW5jZSB0aGluZ3MgbGlrZSBIVE1MXG4gICAqIGVsZW1lbnRzIHdpbGwgYXBwZWFyIHRvIGJlZ2luIGF0IHRoZSBzdGFydCBvZiB0aGUgb3BlbmluZyB0YWcsIHJhdGhlciB0aGFuIGF0IHRoZSBzdGFydCBvZiBhbnlcbiAgICogbGVhZGluZyB0cml2aWEsIHdoaWNoIGNvdWxkIGluY2x1ZGUgbmV3bGluZXMuXG4gICAqXG4gICAqIEBwYXJhbSBlbmRcbiAgICogVGhlIGxvY2F0aW9uIG9mIHRoZSBlbmQgb2YgdGhlIHNwYW4uXG4gICAqXG4gICAqIEBwYXJhbSBmdWxsU3RhcnRcbiAgICogVGhlIHN0YXJ0IG9mIHRoZSB0b2tlbiB3aXRob3V0IHNraXBwaW5nIHRoZSBsZWFkaW5nIHRyaXZpYS5cbiAgICogVGhpcyBpcyB1c2VkIGJ5IHRvb2xpbmcgdGhhdCBzcGxpdHMgdG9rZW5zIGZ1cnRoZXIsIHN1Y2ggYXMgZXh0cmFjdGluZyBBbmd1bGFyIGludGVycG9sYXRpb25zXG4gICAqIGZyb20gdGV4dCB0b2tlbnMuIFN1Y2ggdG9vbGluZyBjcmVhdGVzIG5ldyBzb3VyY2Utc3BhbnMgcmVsYXRpdmUgdG8gdGhlIG9yaWdpbmFsIHRva2VuJ3NcbiAgICogc291cmNlLXNwYW4uIElmIGxlYWRpbmcgdHJpdmlhIGNoYXJhY3RlcnMgaGF2ZSBiZWVuIHNraXBwZWQgdGhlbiB0aGUgbmV3IHNvdXJjZS1zcGFucyBtYXkgYmVcbiAgICogaW5jb3JyZWN0bHkgb2Zmc2V0LlxuICAgKlxuICAgKiBAcGFyYW0gZGV0YWlsc1xuICAgKiBBZGRpdGlvbmFsIGluZm9ybWF0aW9uIChzdWNoIGFzIGlkZW50aWZpZXIgbmFtZXMpIHRoYXQgc2hvdWxkIGJlIGFzc29jaWF0ZWQgd2l0aCB0aGUgc3Bhbi5cbiAgICovXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHN0YXJ0OiBQYXJzZUxvY2F0aW9uLCBwdWJsaWMgZW5kOiBQYXJzZUxvY2F0aW9uLFxuICAgICAgcHVibGljIGZ1bGxTdGFydDogUGFyc2VMb2NhdGlvbiA9IHN0YXJ0LCBwdWJsaWMgZGV0YWlsczogc3RyaW5nfG51bGwgPSBudWxsKSB7fVxuXG4gIHRvU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgcmV0dXJuIHRoaXMuc3RhcnQuZmlsZS5jb250ZW50LnN1YnN0cmluZyh0aGlzLnN0YXJ0Lm9mZnNldCwgdGhpcy5lbmQub2Zmc2V0KTtcbiAgfVxufVxuXG5leHBvcnQgZW51bSBQYXJzZUVycm9yTGV2ZWwge1xuICBXQVJOSU5HLFxuICBFUlJPUixcbn1cblxuZXhwb3J0IGNsYXNzIFBhcnNlRXJyb3Ige1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyBzcGFuOiBQYXJzZVNvdXJjZVNwYW4sIHB1YmxpYyBtc2c6IHN0cmluZyxcbiAgICAgIHB1YmxpYyBsZXZlbDogUGFyc2VFcnJvckxldmVsID0gUGFyc2VFcnJvckxldmVsLkVSUk9SKSB7fVxuXG4gIGNvbnRleHR1YWxNZXNzYWdlKCk6IHN0cmluZyB7XG4gICAgY29uc3QgY3R4ID0gdGhpcy5zcGFuLnN0YXJ0LmdldENvbnRleHQoMTAwLCAzKTtcbiAgICByZXR1cm4gY3R4ID8gYCR7dGhpcy5tc2d9IChcIiR7Y3R4LmJlZm9yZX1bJHtQYXJzZUVycm9yTGV2ZWxbdGhpcy5sZXZlbF19IC0+XSR7Y3R4LmFmdGVyfVwiKWAgOlxuICAgICAgICAgICAgICAgICB0aGlzLm1zZztcbiAgfVxuXG4gIHRvU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgY29uc3QgZGV0YWlscyA9IHRoaXMuc3Bhbi5kZXRhaWxzID8gYCwgJHt0aGlzLnNwYW4uZGV0YWlsc31gIDogJyc7XG4gICAgcmV0dXJuIGAke3RoaXMuY29udGV4dHVhbE1lc3NhZ2UoKX06ICR7dGhpcy5zcGFuLnN0YXJ0fSR7ZGV0YWlsc31gO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiB0eXBlU291cmNlU3BhbihraW5kOiBzdHJpbmcsIHR5cGU6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGEpOiBQYXJzZVNvdXJjZVNwYW4ge1xuICBjb25zdCBtb2R1bGVVcmwgPSBpZGVudGlmaWVyTW9kdWxlVXJsKHR5cGUpO1xuICBjb25zdCBzb3VyY2VGaWxlTmFtZSA9IG1vZHVsZVVybCAhPSBudWxsID8gYGluICR7a2luZH0gJHtpZGVudGlmaWVyTmFtZSh0eXBlKX0gaW4gJHttb2R1bGVVcmx9YCA6XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgaW4gJHtraW5kfSAke2lkZW50aWZpZXJOYW1lKHR5cGUpfWA7XG4gIGNvbnN0IHNvdXJjZUZpbGUgPSBuZXcgUGFyc2VTb3VyY2VGaWxlKCcnLCBzb3VyY2VGaWxlTmFtZSk7XG4gIHJldHVybiBuZXcgUGFyc2VTb3VyY2VTcGFuKFxuICAgICAgbmV3IFBhcnNlTG9jYXRpb24oc291cmNlRmlsZSwgLTEsIC0xLCAtMSksIG5ldyBQYXJzZUxvY2F0aW9uKHNvdXJjZUZpbGUsIC0xLCAtMSwgLTEpKTtcbn1cblxuLyoqXG4gKiBHZW5lcmF0ZXMgU291cmNlIFNwYW4gb2JqZWN0IGZvciBhIGdpdmVuIFIzIFR5cGUgZm9yIEpJVCBtb2RlLlxuICpcbiAqIEBwYXJhbSBraW5kIENvbXBvbmVudCBvciBEaXJlY3RpdmUuXG4gKiBAcGFyYW0gdHlwZU5hbWUgbmFtZSBvZiB0aGUgQ29tcG9uZW50IG9yIERpcmVjdGl2ZS5cbiAqIEBwYXJhbSBzb3VyY2VVcmwgcmVmZXJlbmNlIHRvIENvbXBvbmVudCBvciBEaXJlY3RpdmUgc291cmNlLlxuICogQHJldHVybnMgaW5zdGFuY2Ugb2YgUGFyc2VTb3VyY2VTcGFuIHRoYXQgcmVwcmVzZW50IGEgZ2l2ZW4gQ29tcG9uZW50IG9yIERpcmVjdGl2ZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHIzSml0VHlwZVNvdXJjZVNwYW4oXG4gICAga2luZDogc3RyaW5nLCB0eXBlTmFtZTogc3RyaW5nLCBzb3VyY2VVcmw6IHN0cmluZyk6IFBhcnNlU291cmNlU3BhbiB7XG4gIGNvbnN0IHNvdXJjZUZpbGVOYW1lID0gYGluICR7a2luZH0gJHt0eXBlTmFtZX0gaW4gJHtzb3VyY2VVcmx9YDtcbiAgY29uc3Qgc291cmNlRmlsZSA9IG5ldyBQYXJzZVNvdXJjZUZpbGUoJycsIHNvdXJjZUZpbGVOYW1lKTtcbiAgcmV0dXJuIG5ldyBQYXJzZVNvdXJjZVNwYW4oXG4gICAgICBuZXcgUGFyc2VMb2NhdGlvbihzb3VyY2VGaWxlLCAtMSwgLTEsIC0xKSwgbmV3IFBhcnNlTG9jYXRpb24oc291cmNlRmlsZSwgLTEsIC0xLCAtMSkpO1xufVxuIl19