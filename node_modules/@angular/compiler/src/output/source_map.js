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
        define("@angular/compiler/src/output/source_map", ["require", "exports", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.toBase64String = exports.SourceMapGenerator = void 0;
    var util_1 = require("@angular/compiler/src/util");
    // https://docs.google.com/document/d/1U1RGAehQwRypUTovF1KRlpiOFze0b-_2gc6fAH0KY0k/edit
    var VERSION = 3;
    var JS_B64_PREFIX = '# sourceMappingURL=data:application/json;base64,';
    var SourceMapGenerator = /** @class */ (function () {
        function SourceMapGenerator(file) {
            if (file === void 0) { file = null; }
            this.file = file;
            this.sourcesContent = new Map();
            this.lines = [];
            this.lastCol0 = 0;
            this.hasMappings = false;
        }
        // The content is `null` when the content is expected to be loaded using the URL
        SourceMapGenerator.prototype.addSource = function (url, content) {
            if (content === void 0) { content = null; }
            if (!this.sourcesContent.has(url)) {
                this.sourcesContent.set(url, content);
            }
            return this;
        };
        SourceMapGenerator.prototype.addLine = function () {
            this.lines.push([]);
            this.lastCol0 = 0;
            return this;
        };
        SourceMapGenerator.prototype.addMapping = function (col0, sourceUrl, sourceLine0, sourceCol0) {
            if (!this.currentLine) {
                throw new Error("A line must be added before mappings can be added");
            }
            if (sourceUrl != null && !this.sourcesContent.has(sourceUrl)) {
                throw new Error("Unknown source file \"" + sourceUrl + "\"");
            }
            if (col0 == null) {
                throw new Error("The column in the generated code must be provided");
            }
            if (col0 < this.lastCol0) {
                throw new Error("Mapping should be added in output order");
            }
            if (sourceUrl && (sourceLine0 == null || sourceCol0 == null)) {
                throw new Error("The source location must be provided when a source url is provided");
            }
            this.hasMappings = true;
            this.lastCol0 = col0;
            this.currentLine.push({ col0: col0, sourceUrl: sourceUrl, sourceLine0: sourceLine0, sourceCol0: sourceCol0 });
            return this;
        };
        Object.defineProperty(SourceMapGenerator.prototype, "currentLine", {
            /**
             * @internal strip this from published d.ts files due to
             * https://github.com/microsoft/TypeScript/issues/36216
             */
            get: function () {
                return this.lines.slice(-1)[0];
            },
            enumerable: false,
            configurable: true
        });
        SourceMapGenerator.prototype.toJSON = function () {
            var _this = this;
            if (!this.hasMappings) {
                return null;
            }
            var sourcesIndex = new Map();
            var sources = [];
            var sourcesContent = [];
            Array.from(this.sourcesContent.keys()).forEach(function (url, i) {
                sourcesIndex.set(url, i);
                sources.push(url);
                sourcesContent.push(_this.sourcesContent.get(url) || null);
            });
            var mappings = '';
            var lastCol0 = 0;
            var lastSourceIndex = 0;
            var lastSourceLine0 = 0;
            var lastSourceCol0 = 0;
            this.lines.forEach(function (segments) {
                lastCol0 = 0;
                mappings += segments
                    .map(function (segment) {
                    // zero-based starting column of the line in the generated code
                    var segAsStr = toBase64VLQ(segment.col0 - lastCol0);
                    lastCol0 = segment.col0;
                    if (segment.sourceUrl != null) {
                        // zero-based index into the “sources” list
                        segAsStr +=
                            toBase64VLQ(sourcesIndex.get(segment.sourceUrl) - lastSourceIndex);
                        lastSourceIndex = sourcesIndex.get(segment.sourceUrl);
                        // the zero-based starting line in the original source
                        segAsStr += toBase64VLQ(segment.sourceLine0 - lastSourceLine0);
                        lastSourceLine0 = segment.sourceLine0;
                        // the zero-based starting column in the original source
                        segAsStr += toBase64VLQ(segment.sourceCol0 - lastSourceCol0);
                        lastSourceCol0 = segment.sourceCol0;
                    }
                    return segAsStr;
                })
                    .join(',');
                mappings += ';';
            });
            mappings = mappings.slice(0, -1);
            return {
                'file': this.file || '',
                'version': VERSION,
                'sourceRoot': '',
                'sources': sources,
                'sourcesContent': sourcesContent,
                'mappings': mappings,
            };
        };
        SourceMapGenerator.prototype.toJsComment = function () {
            return this.hasMappings ? '//' + JS_B64_PREFIX + toBase64String(JSON.stringify(this, null, 0)) :
                '';
        };
        return SourceMapGenerator;
    }());
    exports.SourceMapGenerator = SourceMapGenerator;
    function toBase64String(value) {
        var b64 = '';
        var encoded = util_1.utf8Encode(value);
        for (var i = 0; i < encoded.length;) {
            var i1 = encoded[i++];
            var i2 = i < encoded.length ? encoded[i++] : null;
            var i3 = i < encoded.length ? encoded[i++] : null;
            b64 += toBase64Digit(i1 >> 2);
            b64 += toBase64Digit(((i1 & 3) << 4) | (i2 === null ? 0 : i2 >> 4));
            b64 += i2 === null ? '=' : toBase64Digit(((i2 & 15) << 2) | (i3 === null ? 0 : i3 >> 6));
            b64 += i2 === null || i3 === null ? '=' : toBase64Digit(i3 & 63);
        }
        return b64;
    }
    exports.toBase64String = toBase64String;
    function toBase64VLQ(value) {
        value = value < 0 ? ((-value) << 1) + 1 : value << 1;
        var out = '';
        do {
            var digit = value & 31;
            value = value >> 5;
            if (value > 0) {
                digit = digit | 32;
            }
            out += toBase64Digit(digit);
        } while (value > 0);
        return out;
    }
    var B64_DIGITS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    function toBase64Digit(value) {
        if (value < 0 || value >= 64) {
            throw new Error("Can only encode value in the range [0, 63]");
        }
        return B64_DIGITS[value];
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic291cmNlX21hcC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9vdXRwdXQvc291cmNlX21hcC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxtREFBbUM7SUFFbkMsdUZBQXVGO0lBQ3ZGLElBQU0sT0FBTyxHQUFHLENBQUMsQ0FBQztJQUVsQixJQUFNLGFBQWEsR0FBRyxrREFBa0QsQ0FBQztJQWtCekU7UUFNRSw0QkFBb0IsSUFBd0I7WUFBeEIscUJBQUEsRUFBQSxXQUF3QjtZQUF4QixTQUFJLEdBQUosSUFBSSxDQUFvQjtZQUxwQyxtQkFBYyxHQUE2QixJQUFJLEdBQUcsRUFBRSxDQUFDO1lBQ3JELFVBQUssR0FBZ0IsRUFBRSxDQUFDO1lBQ3hCLGFBQVEsR0FBVyxDQUFDLENBQUM7WUFDckIsZ0JBQVcsR0FBRyxLQUFLLENBQUM7UUFFbUIsQ0FBQztRQUVoRCxnRkFBZ0Y7UUFDaEYsc0NBQVMsR0FBVCxVQUFVLEdBQVcsRUFBRSxPQUEyQjtZQUEzQix3QkFBQSxFQUFBLGNBQTJCO1lBQ2hELElBQUksQ0FBQyxJQUFJLENBQUMsY0FBYyxDQUFDLEdBQUcsQ0FBQyxHQUFHLENBQUMsRUFBRTtnQkFDakMsSUFBSSxDQUFDLGNBQWMsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLE9BQU8sQ0FBQyxDQUFDO2FBQ3ZDO1lBQ0QsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBRUQsb0NBQU8sR0FBUDtZQUNFLElBQUksQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDO1lBQ3BCLElBQUksQ0FBQyxRQUFRLEdBQUcsQ0FBQyxDQUFDO1lBQ2xCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUVELHVDQUFVLEdBQVYsVUFBVyxJQUFZLEVBQUUsU0FBa0IsRUFBRSxXQUFvQixFQUFFLFVBQW1CO1lB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Q29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7dXRmOEVuY29kZX0gZnJvbSAnLi4vdXRpbCc7XG5cbi8vIGh0dHBzOi8vZG9jcy5nb29nbGUuY29tL2RvY3VtZW50L2QvMVUxUkdBZWhRd1J5cFVUb3ZGMUtSbHBpT0Z6ZTBiLV8yZ2M2ZkFIMEtZMGsvZWRpdFxuY29uc3QgVkVSU0lPTiA9IDM7XG5cbmNvbnN0IEpTX0I2NF9QUkVGSVggPSAnIyBzb3VyY2VNYXBwaW5nVVJMPWRhdGE6YXBwbGljYXRpb24vanNvbjtiYXNlNjQsJztcblxudHlwZSBTZWdtZW50ID0ge1xuICBjb2wwOiBudW1iZXIsXG4gIHNvdXJjZVVybD86IHN0cmluZyxcbiAgc291cmNlTGluZTA/OiBudW1iZXIsXG4gIHNvdXJjZUNvbDA/OiBudW1iZXIsXG59O1xuXG5leHBvcnQgdHlwZSBTb3VyY2VNYXAgPSB7XG4gIHZlcnNpb246IG51bWJlcixcbiAgZmlsZT86IHN0cmluZyxcbiAgICAgIHNvdXJjZVJvb3Q6IHN0cmluZyxcbiAgICAgIHNvdXJjZXM6IHN0cmluZ1tdLFxuICAgICAgc291cmNlc0NvbnRlbnQ6IChzdHJpbmd8bnVsbClbXSxcbiAgICAgIG1hcHBpbmdzOiBzdHJpbmcsXG59O1xuXG5leHBvcnQgY2xhc3MgU291cmNlTWFwR2VuZXJhdG9yIHtcbiAgcHJpdmF0ZSBzb3VyY2VzQ29udGVudDogTWFwPHN0cmluZywgc3RyaW5nfG51bGw+ID0gbmV3IE1hcCgpO1xuICBwcml2YXRlIGxpbmVzOiBTZWdtZW50W11bXSA9IFtdO1xuICBwcml2YXRlIGxhc3RDb2wwOiBudW1iZXIgPSAwO1xuICBwcml2YXRlIGhhc01hcHBpbmdzID0gZmFsc2U7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSBmaWxlOiBzdHJpbmd8bnVsbCA9IG51bGwpIHt9XG5cbiAgLy8gVGhlIGNvbnRlbnQgaXMgYG51bGxgIHdoZW4gdGhlIGNvbnRlbnQgaXMgZXhwZWN0ZWQgdG8gYmUgbG9hZGVkIHVzaW5nIHRoZSBVUkxcbiAgYWRkU291cmNlKHVybDogc3RyaW5nLCBjb250ZW50OiBzdHJpbmd8bnVsbCA9IG51bGwpOiB0aGlzIHtcbiAgICBpZiAoIXRoaXMuc291cmNlc0NvbnRlbnQuaGFzKHVybCkpIHtcbiAgICAgIHRoaXMuc291cmNlc0NvbnRlbnQuc2V0KHVybCwgY29udGVudCk7XG4gICAgfVxuICAgIHJldHVybiB0aGlzO1xuICB9XG5cbiAgYWRkTGluZSgpOiB0aGlzIHtcbiAgICB0aGlzLmxpbmVzLnB1c2goW10pO1xuICAgIHRoaXMubGFzdENvbDAgPSAwO1xuICAgIHJldHVybiB0aGlzO1xuICB9XG5cbiAgYWRkTWFwcGluZyhjb2wwOiBudW1iZXIsIHNvdXJjZVVybD86IHN0cmluZywgc291cmNlTGluZTA/OiBudW1iZXIsIHNvdXJjZUNvbDA/OiBudW1iZXIpOiB0aGlzIHtcbiAgICBpZiAoIXRoaXMuY3VycmVudExpbmUpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgQSBsaW5lIG11c3QgYmUgYWRkZWQgYmVmb3JlIG1hcHBpbmdzIGNhbiBiZSBhZGRlZGApO1xuICAgIH1cbiAgICBpZiAoc291cmNlVXJsICE9IG51bGwgJiYgIXRoaXMuc291cmNlc0NvbnRlbnQuaGFzKHNvdXJjZVVybCkpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgVW5rbm93biBzb3VyY2UgZmlsZSBcIiR7c291cmNlVXJsfVwiYCk7XG4gICAgfVxuICAgIGlmIChjb2wwID09IG51bGwpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgVGhlIGNvbHVtbiBpbiB0aGUgZ2VuZXJhdGVkIGNvZGUgbXVzdCBiZSBwcm92aWRlZGApO1xuICAgIH1cbiAgICBpZiAoY29sMCA8IHRoaXMubGFzdENvbDApIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgTWFwcGluZyBzaG91bGQgYmUgYWRkZWQgaW4gb3V0cHV0IG9yZGVyYCk7XG4gICAgfVxuICAgIGlmIChzb3VyY2VVcmwgJiYgKHNvdXJjZUxpbmUwID09IG51bGwgfHwgc291cmNlQ29sMCA9PSBudWxsKSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBUaGUgc291cmNlIGxvY2F0aW9uIG11c3QgYmUgcHJvdmlkZWQgd2hlbiBhIHNvdXJjZSB1cmwgaXMgcHJvdmlkZWRgKTtcbiAgICB9XG5cbiAgICB0aGlzLmhhc01hcHBpbmdzID0gdHJ1ZTtcbiAgICB0aGlzLmxhc3RDb2wwID0gY29sMDtcbiAgICB0aGlzLmN1cnJlbnRMaW5lLnB1c2goe2NvbDAsIHNvdXJjZVVybCwgc291cmNlTGluZTAsIHNvdXJjZUNvbDB9KTtcbiAgICByZXR1cm4gdGhpcztcbiAgfVxuXG4gIC8qKlxuICAgKiBAaW50ZXJuYWwgc3RyaXAgdGhpcyBmcm9tIHB1Ymxpc2hlZCBkLnRzIGZpbGVzIGR1ZSB0b1xuICAgKiBodHRwczovL2dpdGh1Yi5jb20vbWljcm9zb2Z0L1R5cGVTY3JpcHQvaXNzdWVzLzM2MjE2XG4gICAqL1xuICBwcml2YXRlIGdldCBjdXJyZW50TGluZSgpOiBTZWdtZW50W118bnVsbCB7XG4gICAgcmV0dXJuIHRoaXMubGluZXMuc2xpY2UoLTEpWzBdO1xuICB9XG5cbiAgdG9KU09OKCk6IFNvdXJjZU1hcHxudWxsIHtcbiAgICBpZiAoIXRoaXMuaGFzTWFwcGluZ3MpIHtcbiAgICAgIHJldHVybiBudWxsO1xuICAgIH1cblxuICAgIGNvbnN0IHNvdXJjZXNJbmRleCA9IG5ldyBNYXA8c3RyaW5nLCBudW1iZXI+KCk7XG4gICAgY29uc3Qgc291cmNlczogc3RyaW5nW10gPSBbXTtcbiAgICBjb25zdCBzb3VyY2VzQ29udGVudDogKHN0cmluZ3xudWxsKVtdID0gW107XG5cbiAgICBBcnJheS5mcm9tKHRoaXMuc291cmNlc0NvbnRlbnQua2V5cygpKS5mb3JFYWNoKCh1cmw6IHN0cmluZywgaTogbnVtYmVyKSA9PiB7XG4gICAgICBzb3VyY2VzSW5kZXguc2V0KHVybCwgaSk7XG4gICAgICBzb3VyY2VzLnB1c2godXJsKTtcbiAgICAgIHNvdXJjZXNDb250ZW50LnB1c2godGhpcy5zb3VyY2VzQ29udGVudC5nZXQodXJsKSB8fCBudWxsKTtcbiAgICB9KTtcblxuICAgIGxldCBtYXBwaW5nczogc3RyaW5nID0gJyc7XG4gICAgbGV0IGxhc3RDb2wwOiBudW1iZXIgPSAwO1xuICAgIGxldCBsYXN0U291cmNlSW5kZXg6IG51bWJlciA9IDA7XG4gICAgbGV0IGxhc3RTb3VyY2VMaW5lMDogbnVtYmVyID0gMDtcbiAgICBsZXQgbGFzdFNvdXJjZUNvbDA6IG51bWJlciA9IDA7XG5cbiAgICB0aGlzLmxpbmVzLmZvckVhY2goc2VnbWVudHMgPT4ge1xuICAgICAgbGFzdENvbDAgPSAwO1xuXG4gICAgICBtYXBwaW5ncyArPSBzZWdtZW50c1xuICAgICAgICAgICAgICAgICAgICAgIC5tYXAoc2VnbWVudCA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyB6ZXJvLWJhc2VkIHN0YXJ0aW5nIGNvbHVtbiBvZiB0aGUgbGluZSBpbiB0aGUgZ2VuZXJhdGVkIGNvZGVcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzZWdBc1N0ciA9IHRvQmFzZTY0VkxRKHNlZ21lbnQuY29sMCAtIGxhc3RDb2wwKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGxhc3RDb2wwID0gc2VnbWVudC5jb2wwO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoc2VnbWVudC5zb3VyY2VVcmwgIT0gbnVsbCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAvLyB6ZXJvLWJhc2VkIGluZGV4IGludG8gdGhlIOKAnHNvdXJjZXPigJ0gbGlzdFxuICAgICAgICAgICAgICAgICAgICAgICAgICBzZWdBc1N0ciArPVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdG9CYXNlNjRWTFEoc291cmNlc0luZGV4LmdldChzZWdtZW50LnNvdXJjZVVybCkhIC0gbGFzdFNvdXJjZUluZGV4KTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgbGFzdFNvdXJjZUluZGV4ID0gc291cmNlc0luZGV4LmdldChzZWdtZW50LnNvdXJjZVVybCkhO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAvLyB0aGUgemVyby1iYXNlZCBzdGFydGluZyBsaW5lIGluIHRoZSBvcmlnaW5hbCBzb3VyY2VcbiAgICAgICAgICAgICAgICAgICAgICAgICAgc2VnQXNTdHIgKz0gdG9CYXNlNjRWTFEoc2VnbWVudC5zb3VyY2VMaW5lMCEgLSBsYXN0U291cmNlTGluZTApO1xuICAgICAgICAgICAgICAgICAgICAgICAgICBsYXN0U291cmNlTGluZTAgPSBzZWdtZW50LnNvdXJjZUxpbmUwITtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLy8gdGhlIHplcm8tYmFzZWQgc3RhcnRpbmcgY29sdW1uIGluIHRoZSBvcmlnaW5hbCBzb3VyY2VcbiAgICAgICAgICAgICAgICAgICAgICAgICAgc2VnQXNTdHIgKz0gdG9CYXNlNjRWTFEoc2VnbWVudC5zb3VyY2VDb2wwISAtIGxhc3RTb3VyY2VDb2wwKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgbGFzdFNvdXJjZUNvbDAgPSBzZWdtZW50LnNvdXJjZUNvbDAhO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gc2VnQXNTdHI7XG4gICAgICAgICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICAgICAgICAgICAuam9pbignLCcpO1xuICAgICAgbWFwcGluZ3MgKz0gJzsnO1xuICAgIH0pO1xuXG4gICAgbWFwcGluZ3MgPSBtYXBwaW5ncy5zbGljZSgwLCAtMSk7XG5cbiAgICByZXR1cm4ge1xuICAgICAgJ2ZpbGUnOiB0aGlzLmZpbGUgfHwgJycsXG4gICAgICAndmVyc2lvbic6IFZFUlNJT04sXG4gICAgICAnc291cmNlUm9vdCc6ICcnLFxuICAgICAgJ3NvdXJjZXMnOiBzb3VyY2VzLFxuICAgICAgJ3NvdXJjZXNDb250ZW50Jzogc291cmNlc0NvbnRlbnQsXG4gICAgICAnbWFwcGluZ3MnOiBtYXBwaW5ncyxcbiAgICB9O1xuICB9XG5cbiAgdG9Kc0NvbW1lbnQoKTogc3RyaW5nIHtcbiAgICByZXR1cm4gdGhpcy5oYXNNYXBwaW5ncyA/ICcvLycgKyBKU19CNjRfUFJFRklYICsgdG9CYXNlNjRTdHJpbmcoSlNPTi5zdHJpbmdpZnkodGhpcywgbnVsbCwgMCkpIDpcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICcnO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiB0b0Jhc2U2NFN0cmluZyh2YWx1ZTogc3RyaW5nKTogc3RyaW5nIHtcbiAgbGV0IGI2NCA9ICcnO1xuICBjb25zdCBlbmNvZGVkID0gdXRmOEVuY29kZSh2YWx1ZSk7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgZW5jb2RlZC5sZW5ndGg7KSB7XG4gICAgY29uc3QgaTEgPSBlbmNvZGVkW2krK107XG4gICAgY29uc3QgaTIgPSBpIDwgZW5jb2RlZC5sZW5ndGggPyBlbmNvZGVkW2krK10gOiBudWxsO1xuICAgIGNvbnN0IGkzID0gaSA8IGVuY29kZWQubGVuZ3RoID8gZW5jb2RlZFtpKytdIDogbnVsbDtcbiAgICBiNjQgKz0gdG9CYXNlNjREaWdpdChpMSA+PiAyKTtcbiAgICBiNjQgKz0gdG9CYXNlNjREaWdpdCgoKGkxICYgMykgPDwgNCkgfCAoaTIgPT09IG51bGwgPyAwIDogaTIgPj4gNCkpO1xuICAgIGI2NCArPSBpMiA9PT0gbnVsbCA/ICc9JyA6IHRvQmFzZTY0RGlnaXQoKChpMiAmIDE1KSA8PCAyKSB8IChpMyA9PT0gbnVsbCA/IDAgOiBpMyA+PiA2KSk7XG4gICAgYjY0ICs9IGkyID09PSBudWxsIHx8IGkzID09PSBudWxsID8gJz0nIDogdG9CYXNlNjREaWdpdChpMyAmIDYzKTtcbiAgfVxuXG4gIHJldHVybiBiNjQ7XG59XG5cbmZ1bmN0aW9uIHRvQmFzZTY0VkxRKHZhbHVlOiBudW1iZXIpOiBzdHJpbmcge1xuICB2YWx1ZSA9IHZhbHVlIDwgMCA/ICgoLXZhbHVlKSA8PCAxKSArIDEgOiB2YWx1ZSA8PCAxO1xuXG4gIGxldCBvdXQgPSAnJztcbiAgZG8ge1xuICAgIGxldCBkaWdpdCA9IHZhbHVlICYgMzE7XG4gICAgdmFsdWUgPSB2YWx1ZSA+PiA1O1xuICAgIGlmICh2YWx1ZSA+IDApIHtcbiAgICAgIGRpZ2l0ID0gZGlnaXQgfCAzMjtcbiAgICB9XG4gICAgb3V0ICs9IHRvQmFzZTY0RGlnaXQoZGlnaXQpO1xuICB9IHdoaWxlICh2YWx1ZSA+IDApO1xuXG4gIHJldHVybiBvdXQ7XG59XG5cbmNvbnN0IEI2NF9ESUdJVFMgPSAnQUJDREVGR0hJSktMTU5PUFFSU1RVVldYWVphYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5ejAxMjM0NTY3ODkrLyc7XG5cbmZ1bmN0aW9uIHRvQmFzZTY0RGlnaXQodmFsdWU6IG51bWJlcik6IHN0cmluZyB7XG4gIGlmICh2YWx1ZSA8IDAgfHwgdmFsdWUgPj0gNjQpIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoYENhbiBvbmx5IGVuY29kZSB2YWx1ZSBpbiB0aGUgcmFuZ2UgWzAsIDYzXWApO1xuICB9XG5cbiAgcmV0dXJuIEI2NF9ESUdJVFNbdmFsdWVdO1xufVxuIl19