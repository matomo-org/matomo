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
        define("@angular/core/schematics/utils/line_mappings", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.computeLineStartsMap = exports.getLineAndCharacterFromPosition = void 0;
    const LF_CHAR = 10;
    const CR_CHAR = 13;
    const LINE_SEP_CHAR = 8232;
    const PARAGRAPH_CHAR = 8233;
    /** Gets the line and character for the given position from the line starts map. */
    function getLineAndCharacterFromPosition(lineStartsMap, position) {
        const lineIndex = findClosestLineStartPosition(lineStartsMap, position);
        return { character: position - lineStartsMap[lineIndex], line: lineIndex };
    }
    exports.getLineAndCharacterFromPosition = getLineAndCharacterFromPosition;
    /**
     * Computes the line start map of the given text. This can be used in order to
     * retrieve the line and character of a given text position index.
     */
    function computeLineStartsMap(text) {
        const result = [0];
        let pos = 0;
        while (pos < text.length) {
            const char = text.charCodeAt(pos++);
            // Handles the "CRLF" line break. In that case we peek the character
            // after the "CR" and check if it is a line feed.
            if (char === CR_CHAR) {
                if (text.charCodeAt(pos) === LF_CHAR) {
                    pos++;
                }
                result.push(pos);
            }
            else if (char === LF_CHAR || char === LINE_SEP_CHAR || char === PARAGRAPH_CHAR) {
                result.push(pos);
            }
        }
        result.push(pos);
        return result;
    }
    exports.computeLineStartsMap = computeLineStartsMap;
    /** Finds the closest line start for the given position. */
    function findClosestLineStartPosition(linesMap, position, low = 0, high = linesMap.length - 1) {
        while (low <= high) {
            const pivotIdx = Math.floor((low + high) / 2);
            const pivotEl = linesMap[pivotIdx];
            if (pivotEl === position) {
                return pivotIdx;
            }
            else if (position > pivotEl) {
                low = pivotIdx + 1;
            }
            else {
                high = pivotIdx - 1;
            }
        }
        // In case there was no exact match, return the closest "lower" line index. We also
        // subtract the index by one because want the index of the previous line start.
        return low - 1;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibGluZV9tYXBwaW5ncy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy9saW5lX21hcHBpbmdzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILE1BQU0sT0FBTyxHQUFHLEVBQUUsQ0FBQztJQUNuQixNQUFNLE9BQU8sR0FBRyxFQUFFLENBQUM7SUFDbkIsTUFBTSxhQUFhLEdBQUcsSUFBSSxDQUFDO0lBQzNCLE1BQU0sY0FBYyxHQUFHLElBQUksQ0FBQztJQUU1QixtRkFBbUY7SUFDbkYsU0FBZ0IsK0JBQStCLENBQUMsYUFBdUIsRUFBRSxRQUFnQjtRQUN2RixNQUFNLFNBQVMsR0FBRyw0QkFBNEIsQ0FBQyxhQUFhLEVBQUUsUUFBUSxDQUFDLENBQUM7UUFDeEUsT0FBTyxFQUFDLFNBQVMsRUFBRSxRQUFRLEdBQUcsYUFBYSxDQUFDLFNBQVMsQ0FBQyxFQUFFLElBQUksRUFBRSxTQUFTLEVBQUMsQ0FBQztJQUMzRSxDQUFDO0lBSEQsMEVBR0M7SUFFRDs7O09BR0c7SUFDSCxTQUFnQixvQkFBb0IsQ0FBQyxJQUFZO1FBQy9DLE1BQU0sTUFBTSxHQUFhLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDN0IsSUFBSSxHQUFHLEdBQUcsQ0FBQyxDQUFDO1FBQ1osT0FBTyxHQUFHLEdBQUcsSUFBSSxDQUFDLE1BQU0sRUFBRTtZQUN4QixNQUFNLElBQUksR0FBRyxJQUFJLENBQUMsVUFBVSxDQUFDLEdBQUcsRUFBRSxDQUFDLENBQUM7WUFDcEMsb0VBQW9FO1lBQ3BFLGlEQUFpRDtZQUNqRCxJQUFJLElBQUksS0FBSyxPQUFPLEVBQUU7Z0JBQ3BCLElBQUksSUFBSSxDQUFDLFVBQVUsQ0FBQyxHQUFHLENBQUMsS0FBSyxPQUFPLEVBQUU7b0JBQ3BDLEdBQUcsRUFBRSxDQUFDO2lCQUNQO2dCQUNELE1BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUM7YUFDbEI7aUJBQU0sSUFBSSxJQUFJLEtBQUssT0FBTyxJQUFJLElBQUksS0FBSyxhQUFhLElBQUksSUFBSSxLQUFLLGNBQWMsRUFBRTtnQkFDaEYsTUFBTSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQzthQUNsQjtTQUNGO1FBQ0QsTUFBTSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUNqQixPQUFPLE1BQU0sQ0FBQztJQUNoQixDQUFDO0lBbEJELG9EQWtCQztJQUVELDJEQUEyRDtJQUMzRCxTQUFTLDRCQUE0QixDQUNqQyxRQUFhLEVBQUUsUUFBVyxFQUFFLEdBQUcsR0FBRyxDQUFDLEVBQUUsSUFBSSxHQUFHLFFBQVEsQ0FBQyxNQUFNLEdBQUcsQ0FBQztRQUNqRSxPQUFPLEdBQUcsSUFBSSxJQUFJLEVBQUU7WUFDbEIsTUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEdBQUcsR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztZQUM5QyxNQUFNLE9BQU8sR0FBRyxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUM7WUFFbkMsSUFBSSxPQUFPLEtBQUssUUFBUSxFQUFFO2dCQUN4QixPQUFPLFFBQVEsQ0FBQzthQUNqQjtpQkFBTSxJQUFJLFFBQVEsR0FBRyxPQUFPLEVBQUU7Z0JBQzdCLEdBQUcsR0FBRyxRQUFRLEdBQUcsQ0FBQyxDQUFDO2FBQ3BCO2lCQUFNO2dCQUNMLElBQUksR0FBRyxRQUFRLEdBQUcsQ0FBQyxDQUFDO2FBQ3JCO1NBQ0Y7UUFFRCxtRkFBbUY7UUFDbkYsK0VBQStFO1FBQy9FLE9BQU8sR0FBRyxHQUFHLENBQUMsQ0FBQztJQUNqQixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmNvbnN0IExGX0NIQVIgPSAxMDtcbmNvbnN0IENSX0NIQVIgPSAxMztcbmNvbnN0IExJTkVfU0VQX0NIQVIgPSA4MjMyO1xuY29uc3QgUEFSQUdSQVBIX0NIQVIgPSA4MjMzO1xuXG4vKiogR2V0cyB0aGUgbGluZSBhbmQgY2hhcmFjdGVyIGZvciB0aGUgZ2l2ZW4gcG9zaXRpb24gZnJvbSB0aGUgbGluZSBzdGFydHMgbWFwLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldExpbmVBbmRDaGFyYWN0ZXJGcm9tUG9zaXRpb24obGluZVN0YXJ0c01hcDogbnVtYmVyW10sIHBvc2l0aW9uOiBudW1iZXIpIHtcbiAgY29uc3QgbGluZUluZGV4ID0gZmluZENsb3Nlc3RMaW5lU3RhcnRQb3NpdGlvbihsaW5lU3RhcnRzTWFwLCBwb3NpdGlvbik7XG4gIHJldHVybiB7Y2hhcmFjdGVyOiBwb3NpdGlvbiAtIGxpbmVTdGFydHNNYXBbbGluZUluZGV4XSwgbGluZTogbGluZUluZGV4fTtcbn1cblxuLyoqXG4gKiBDb21wdXRlcyB0aGUgbGluZSBzdGFydCBtYXAgb2YgdGhlIGdpdmVuIHRleHQuIFRoaXMgY2FuIGJlIHVzZWQgaW4gb3JkZXIgdG9cbiAqIHJldHJpZXZlIHRoZSBsaW5lIGFuZCBjaGFyYWN0ZXIgb2YgYSBnaXZlbiB0ZXh0IHBvc2l0aW9uIGluZGV4LlxuICovXG5leHBvcnQgZnVuY3Rpb24gY29tcHV0ZUxpbmVTdGFydHNNYXAodGV4dDogc3RyaW5nKTogbnVtYmVyW10ge1xuICBjb25zdCByZXN1bHQ6IG51bWJlcltdID0gWzBdO1xuICBsZXQgcG9zID0gMDtcbiAgd2hpbGUgKHBvcyA8IHRleHQubGVuZ3RoKSB7XG4gICAgY29uc3QgY2hhciA9IHRleHQuY2hhckNvZGVBdChwb3MrKyk7XG4gICAgLy8gSGFuZGxlcyB0aGUgXCJDUkxGXCIgbGluZSBicmVhay4gSW4gdGhhdCBjYXNlIHdlIHBlZWsgdGhlIGNoYXJhY3RlclxuICAgIC8vIGFmdGVyIHRoZSBcIkNSXCIgYW5kIGNoZWNrIGlmIGl0IGlzIGEgbGluZSBmZWVkLlxuICAgIGlmIChjaGFyID09PSBDUl9DSEFSKSB7XG4gICAgICBpZiAodGV4dC5jaGFyQ29kZUF0KHBvcykgPT09IExGX0NIQVIpIHtcbiAgICAgICAgcG9zKys7XG4gICAgICB9XG4gICAgICByZXN1bHQucHVzaChwb3MpO1xuICAgIH0gZWxzZSBpZiAoY2hhciA9PT0gTEZfQ0hBUiB8fCBjaGFyID09PSBMSU5FX1NFUF9DSEFSIHx8IGNoYXIgPT09IFBBUkFHUkFQSF9DSEFSKSB7XG4gICAgICByZXN1bHQucHVzaChwb3MpO1xuICAgIH1cbiAgfVxuICByZXN1bHQucHVzaChwb3MpO1xuICByZXR1cm4gcmVzdWx0O1xufVxuXG4vKiogRmluZHMgdGhlIGNsb3Nlc3QgbGluZSBzdGFydCBmb3IgdGhlIGdpdmVuIHBvc2l0aW9uLiAqL1xuZnVuY3Rpb24gZmluZENsb3Nlc3RMaW5lU3RhcnRQb3NpdGlvbjxUPihcbiAgICBsaW5lc01hcDogVFtdLCBwb3NpdGlvbjogVCwgbG93ID0gMCwgaGlnaCA9IGxpbmVzTWFwLmxlbmd0aCAtIDEpIHtcbiAgd2hpbGUgKGxvdyA8PSBoaWdoKSB7XG4gICAgY29uc3QgcGl2b3RJZHggPSBNYXRoLmZsb29yKChsb3cgKyBoaWdoKSAvIDIpO1xuICAgIGNvbnN0IHBpdm90RWwgPSBsaW5lc01hcFtwaXZvdElkeF07XG5cbiAgICBpZiAocGl2b3RFbCA9PT0gcG9zaXRpb24pIHtcbiAgICAgIHJldHVybiBwaXZvdElkeDtcbiAgICB9IGVsc2UgaWYgKHBvc2l0aW9uID4gcGl2b3RFbCkge1xuICAgICAgbG93ID0gcGl2b3RJZHggKyAxO1xuICAgIH0gZWxzZSB7XG4gICAgICBoaWdoID0gcGl2b3RJZHggLSAxO1xuICAgIH1cbiAgfVxuXG4gIC8vIEluIGNhc2UgdGhlcmUgd2FzIG5vIGV4YWN0IG1hdGNoLCByZXR1cm4gdGhlIGNsb3Nlc3QgXCJsb3dlclwiIGxpbmUgaW5kZXguIFdlIGFsc29cbiAgLy8gc3VidHJhY3QgdGhlIGluZGV4IGJ5IG9uZSBiZWNhdXNlIHdhbnQgdGhlIGluZGV4IG9mIHRoZSBwcmV2aW91cyBsaW5lIHN0YXJ0LlxuICByZXR1cm4gbG93IC0gMTtcbn1cbiJdfQ==