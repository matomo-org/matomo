/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertNumber, assertString } from '../../util/assert';
import { ELEMENT_MARKER, I18nCreateOpCode, ICU_MARKER } from '../interfaces/i18n';
import { getInstructionFromIcuCreateOpCode, getParentFromIcuCreateOpCode, getRefFromIcuCreateOpCode } from './i18n_util';
/**
 * Converts `I18nCreateOpCodes` array into a human readable format.
 *
 * This function is attached to the `I18nCreateOpCodes.debug` property if `ngDevMode` is enabled.
 * This function provides a human readable view of the opcodes. This is useful when debugging the
 * application as well as writing more readable tests.
 *
 * @param this `I18nCreateOpCodes` if attached as a method.
 * @param opcodes `I18nCreateOpCodes` if invoked as a function.
 */
export function i18nCreateOpCodesToString(opcodes) {
    const createOpCodes = opcodes || (Array.isArray(this) ? this : []);
    let lines = [];
    for (let i = 0; i < createOpCodes.length; i++) {
        const opCode = createOpCodes[i++];
        const text = createOpCodes[i];
        const isComment = (opCode & I18nCreateOpCode.COMMENT) === I18nCreateOpCode.COMMENT;
        const appendNow = (opCode & I18nCreateOpCode.APPEND_EAGERLY) === I18nCreateOpCode.APPEND_EAGERLY;
        const index = opCode >>> I18nCreateOpCode.SHIFT;
        lines.push(`lView[${index}] = document.${isComment ? 'createComment' : 'createText'}(${JSON.stringify(text)});`);
        if (appendNow) {
            lines.push(`parent.appendChild(lView[${index}]);`);
        }
    }
    return lines;
}
/**
 * Converts `I18nUpdateOpCodes` array into a human readable format.
 *
 * This function is attached to the `I18nUpdateOpCodes.debug` property if `ngDevMode` is enabled.
 * This function provides a human readable view of the opcodes. This is useful when debugging the
 * application as well as writing more readable tests.
 *
 * @param this `I18nUpdateOpCodes` if attached as a method.
 * @param opcodes `I18nUpdateOpCodes` if invoked as a function.
 */
export function i18nUpdateOpCodesToString(opcodes) {
    const parser = new OpCodeParser(opcodes || (Array.isArray(this) ? this : []));
    let lines = [];
    function consumeOpCode(value) {
        const ref = value >>> 2 /* SHIFT_REF */;
        const opCode = value & 3 /* MASK_OPCODE */;
        switch (opCode) {
            case 0 /* Text */:
                return `(lView[${ref}] as Text).textContent = $$$`;
            case 1 /* Attr */:
                const attrName = parser.consumeString();
                const sanitizationFn = parser.consumeFunction();
                const value = sanitizationFn ? `(${sanitizationFn})($$$)` : '$$$';
                return `(lView[${ref}] as Element).setAttribute('${attrName}', ${value})`;
            case 2 /* IcuSwitch */:
                return `icuSwitchCase(${ref}, $$$)`;
            case 3 /* IcuUpdate */:
                return `icuUpdateCase(${ref})`;
        }
        throw new Error('unexpected OpCode');
    }
    while (parser.hasMore()) {
        let mask = parser.consumeNumber();
        let size = parser.consumeNumber();
        const end = parser.i + size;
        const statements = [];
        let statement = '';
        while (parser.i < end) {
            let value = parser.consumeNumberOrString();
            if (typeof value === 'string') {
                statement += value;
            }
            else if (value < 0) {
                // Negative numbers are ref indexes
                // Here `i` refers to current binding index. It is to signify that the value is relative,
                // rather than absolute.
                statement += '${lView[i' + value + ']}';
            }
            else {
                // Positive numbers are operations.
                const opCodeText = consumeOpCode(value);
                statements.push(opCodeText.replace('$$$', '`' + statement + '`') + ';');
                statement = '';
            }
        }
        lines.push(`if (mask & 0b${mask.toString(2)}) { ${statements.join(' ')} }`);
    }
    return lines;
}
/**
 * Converts `I18nCreateOpCodes` array into a human readable format.
 *
 * This function is attached to the `I18nCreateOpCodes.debug` if `ngDevMode` is enabled. This
 * function provides a human readable view of the opcodes. This is useful when debugging the
 * application as well as writing more readable tests.
 *
 * @param this `I18nCreateOpCodes` if attached as a method.
 * @param opcodes `I18nCreateOpCodes` if invoked as a function.
 */
export function icuCreateOpCodesToString(opcodes) {
    const parser = new OpCodeParser(opcodes || (Array.isArray(this) ? this : []));
    let lines = [];
    function consumeOpCode(opCode) {
        const parent = getParentFromIcuCreateOpCode(opCode);
        const ref = getRefFromIcuCreateOpCode(opCode);
        switch (getInstructionFromIcuCreateOpCode(opCode)) {
            case 0 /* AppendChild */:
                return `(lView[${parent}] as Element).appendChild(lView[${lastRef}])`;
            case 1 /* Attr */:
                return `(lView[${ref}] as Element).setAttribute("${parser.consumeString()}", "${parser.consumeString()}")`;
        }
        throw new Error('Unexpected OpCode: ' + getInstructionFromIcuCreateOpCode(opCode));
    }
    let lastRef = -1;
    while (parser.hasMore()) {
        let value = parser.consumeNumberStringOrMarker();
        if (value === ICU_MARKER) {
            const text = parser.consumeString();
            lastRef = parser.consumeNumber();
            lines.push(`lView[${lastRef}] = document.createComment("${text}")`);
        }
        else if (value === ELEMENT_MARKER) {
            const text = parser.consumeString();
            lastRef = parser.consumeNumber();
            lines.push(`lView[${lastRef}] = document.createElement("${text}")`);
        }
        else if (typeof value === 'string') {
            lastRef = parser.consumeNumber();
            lines.push(`lView[${lastRef}] = document.createTextNode("${value}")`);
        }
        else if (typeof value === 'number') {
            const line = consumeOpCode(value);
            line && lines.push(line);
        }
        else {
            throw new Error('Unexpected value');
        }
    }
    return lines;
}
/**
 * Converts `I18nRemoveOpCodes` array into a human readable format.
 *
 * This function is attached to the `I18nRemoveOpCodes.debug` if `ngDevMode` is enabled. This
 * function provides a human readable view of the opcodes. This is useful when debugging the
 * application as well as writing more readable tests.
 *
 * @param this `I18nRemoveOpCodes` if attached as a method.
 * @param opcodes `I18nRemoveOpCodes` if invoked as a function.
 */
export function i18nRemoveOpCodesToString(opcodes) {
    const removeCodes = opcodes || (Array.isArray(this) ? this : []);
    let lines = [];
    for (let i = 0; i < removeCodes.length; i++) {
        const nodeOrIcuIndex = removeCodes[i];
        if (nodeOrIcuIndex > 0) {
            // Positive numbers are `RNode`s.
            lines.push(`remove(lView[${nodeOrIcuIndex}])`);
        }
        else {
            // Negative numbers are ICUs
            lines.push(`removeNestedICU(${~nodeOrIcuIndex})`);
        }
    }
    return lines;
}
class OpCodeParser {
    constructor(codes) {
        this.i = 0;
        this.codes = codes;
    }
    hasMore() {
        return this.i < this.codes.length;
    }
    consumeNumber() {
        let value = this.codes[this.i++];
        assertNumber(value, 'expecting number in OpCode');
        return value;
    }
    consumeString() {
        let value = this.codes[this.i++];
        assertString(value, 'expecting string in OpCode');
        return value;
    }
    consumeFunction() {
        let value = this.codes[this.i++];
        if (value === null || typeof value === 'function') {
            return value;
        }
        throw new Error('expecting function in OpCode');
    }
    consumeNumberOrString() {
        let value = this.codes[this.i++];
        if (typeof value === 'string') {
            return value;
        }
        assertNumber(value, 'expecting number or string in OpCode');
        return value;
    }
    consumeNumberStringOrMarker() {
        let value = this.codes[this.i++];
        if (typeof value === 'string' || typeof value === 'number' || value == ICU_MARKER ||
            value == ELEMENT_MARKER) {
            return value;
        }
        assertNumber(value, 'expecting number, string, ICU_MARKER or ELEMENT_MARKER in OpCode');
        return value;
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaTE4bl9kZWJ1Zy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3JlbmRlcjMvaTE4bi9pMThuX2RlYnVnLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBQyxZQUFZLEVBQUUsWUFBWSxFQUFDLE1BQU0sbUJBQW1CLENBQUM7QUFDN0QsT0FBTyxFQUFDLGNBQWMsRUFBRSxnQkFBZ0IsRUFBNkUsVUFBVSxFQUFvQyxNQUFNLG9CQUFvQixDQUFDO0FBRTlMLE9BQU8sRUFBQyxpQ0FBaUMsRUFBRSw0QkFBNEIsRUFBRSx5QkFBeUIsRUFBQyxNQUFNLGFBQWEsQ0FBQztBQUd2SDs7Ozs7Ozs7O0dBU0c7QUFDSCxNQUFNLFVBQVUseUJBQXlCLENBQ1AsT0FBMkI7SUFDM0QsTUFBTSxhQUFhLEdBQXNCLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBUyxDQUFDLENBQUM7SUFDN0YsSUFBSSxLQUFLLEdBQWEsRUFBRSxDQUFDO0lBQ3pCLEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxhQUFhLENBQUMsTUFBTSxFQUFFLENBQUMsRUFBRSxFQUFFO1FBQzdDLE1BQU0sTUFBTSxHQUFHLGFBQWEsQ0FBQyxDQUFDLEVBQUUsQ0FBUSxDQUFDO1FBQ3pDLE1BQU0sSUFBSSxHQUFHLGFBQWEsQ0FBQyxDQUFDLENBQVcsQ0FBQztRQUN4QyxNQUFNLFNBQVMsR0FBRyxDQUFDLE1BQU0sR0FBRyxnQkFBZ0IsQ0FBQyxPQUFPLENBQUMsS0FBSyxnQkFBZ0IsQ0FBQyxPQUFPLENBQUM7UUFDbkYsTUFBTSxTQUFTLEdBQ1gsQ0FBQyxNQUFNLEdBQUcsZ0JBQWdCLENBQUMsY0FBYyxDQUFDLEtBQUssZ0JBQWdCLENBQUMsY0FB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QUFJLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQztRQUNqQyxJQUFJLEtBQUssS0FBSyxJQUFJLElBQUksT0FBTyxLQUFLLEtBQUssVUFBVSxFQUFFO1lBQ2pELE9BQU8sS0FBSyxDQUFDO1NBQ2Q7UUFDRCxNQUFNLElBQUksS0FBSyxDQUFDLDhCQUE4QixDQUFDLENBQUM7SUFDbEQsQ0FBQztJQUVELHFCQUFxQjtRQUNuQixJQUFJLEtBQUssR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ2pDLElBQUksT0FBTyxLQUFLLEtBQUssUUFBUSxFQUFFO1lBQzdCLE9BQU8sS0FBSyxDQUFDO1NBQ2Q7UUFDRCxZQUFZLENBQUMsS0FBSyxFQUFFLHNDQUFzQyxDQUFDLENBQUM7UUFDNUQsT0FBTyxLQUFLLENBQUM7SUFDZixDQUFDO0lBRUQsMkJBQTJCO1FBQ3pCLElBQUksS0FBSyxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7UUFDakMsSUFBSSxPQUFPLEtBQUssS0FBSyxRQUFRLElBQUksT0FBTyxLQUFLLEtBQUssUUFBUSxJQUFJLEtBQUssSUFBSSxVQUFVO1lBQzdFLEtBQUssSUFBSSxjQUFjLEVBQUU7WUFDM0IsT0FBTyxLQUFLLENBQUM7U0FDZDtRQUNELFlBQVksQ0FBQyxLQUFLLEVBQUUsa0VBQWtFLENBQUMsQ0FBQztRQUN4RixPQUFPLEtBQUssQ0FBQztJQUNmLENBQUM7Q0FDRiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge2Fzc2VydE51bWJlciwgYXNzZXJ0U3RyaW5nfSBmcm9tICcuLi8uLi91dGlsL2Fzc2VydCc7XG5pbXBvcnQge0VMRU1FTlRfTUFSS0VSLCBJMThuQ3JlYXRlT3BDb2RlLCBJMThuQ3JlYXRlT3BDb2RlcywgSTE4blJlbW92ZU9wQ29kZXMsIEkxOG5VcGRhdGVPcENvZGUsIEkxOG5VcGRhdGVPcENvZGVzLCBJQ1VfTUFSS0VSLCBJY3VDcmVhdGVPcENvZGUsIEljdUNyZWF0ZU9wQ29kZXN9IGZyb20gJy4uL2ludGVyZmFjZXMvaTE4bic7XG5cbmltcG9ydCB7Z2V0SW5zdHJ1Y3Rpb25Gcm9tSWN1Q3JlYXRlT3BDb2RlLCBnZXRQYXJlbnRGcm9tSWN1Q3JlYXRlT3BDb2RlLCBnZXRSZWZGcm9tSWN1Q3JlYXRlT3BDb2RlfSBmcm9tICcuL2kxOG5fdXRpbCc7XG5cblxuLyoqXG4gKiBDb252ZXJ0cyBgSTE4bkNyZWF0ZU9wQ29kZXNgIGFycmF5IGludG8gYSBodW1hbiByZWFkYWJsZSBmb3JtYXQuXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBhdHRhY2hlZCB0byB0aGUgYEkxOG5DcmVhdGVPcENvZGVzLmRlYnVnYCBwcm9wZXJ0eSBpZiBgbmdEZXZNb2RlYCBpcyBlbmFibGVkLlxuICogVGhpcyBmdW5jdGlvbiBwcm92aWRlcyBhIGh1bWFuIHJlYWRhYmxlIHZpZXcgb2YgdGhlIG9wY29kZXMuIFRoaXMgaXMgdXNlZnVsIHdoZW4gZGVidWdnaW5nIHRoZVxuICogYXBwbGljYXRpb24gYXMgd2VsbCBhcyB3cml0aW5nIG1vcmUgcmVhZGFibGUgdGVzdHMuXG4gKlxuICogQHBhcmFtIHRoaXMgYEkxOG5DcmVhdGVPcENvZGVzYCBpZiBhdHRhY2hlZCBhcyBhIG1ldGhvZC5cbiAqIEBwYXJhbSBvcGNvZGVzIGBJMThuQ3JlYXRlT3BDb2Rlc2AgaWYgaW52b2tlZCBhcyBhIGZ1bmN0aW9uLlxuICovXG5leHBvcnQgZnVuY3Rpb24gaTE4bkNyZWF0ZU9wQ29kZXNUb1N0cmluZyhcbiAgICB0aGlzOiBJMThuQ3JlYXRlT3BDb2Rlc3x2b2lkLCBvcGNvZGVzPzogSTE4bkNyZWF0ZU9wQ29kZXMpOiBzdHJpbmdbXSB7XG4gIGNvbnN0IGNyZWF0ZU9wQ29kZXM6IEkxOG5DcmVhdGVPcENvZGVzID0gb3Bjb2RlcyB8fCAoQXJyYXkuaXNBcnJheSh0aGlzKSA/IHRoaXMgOiBbXSBhcyBhbnkpO1xuICBsZXQgbGluZXM6IHN0cmluZ1tdID0gW107XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgY3JlYXRlT3BDb2Rlcy5sZW5ndGg7IGkrKykge1xuICAgIGNvbnN0IG9wQ29kZSA9IGNyZWF0ZU9wQ29kZXNbaSsrXSBhcyBhbnk7XG4gICAgY29uc3QgdGV4dCA9IGNyZWF0ZU9wQ29kZXNbaV0gYXMgc3RyaW5nO1xuICAgIGNvbnN0IGlzQ29tbWVudCA9IChvcENvZGUgJiBJMThuQ3JlYXRlT3BDb2RlLkNPTU1FTlQpID09PSBJMThuQ3JlYXRlT3BDb2RlLkNPTU1FTlQ7XG4gICAgY29uc3QgYXBwZW5kTm93ID1cbiAgICAgICAgKG9wQ29kZSAmIEkxOG5DcmVhdGVPcENvZGUuQVBQRU5EX0VBR0VSTFkpID09PSBJMThuQ3JlYXRlT3BDb2RlLkFQUEVORF9FQUdFUkxZO1xuICAgIGNvbnN0IGluZGV4ID0gb3BDb2RlID4+PiBJMThuQ3JlYXRlT3BDb2RlLlNISUZUO1xuICAgIGxpbmVzLnB1c2goYGxWaWV3WyR7aW5kZXh9XSA9IGRvY3VtZW50LiR7aXNDb21tZW50ID8gJ2NyZWF0ZUNvbW1lbnQnIDogJ2NyZWF0ZVRleHQnfSgke1xuICAgICAgICBKU09OLnN0cmluZ2lmeSh0ZXh0KX0pO2ApO1xuICAgIGlmIChhcHBlbmROb3cpIHtcbiAgICAgIGxpbmVzLnB1c2goYHBhcmVudC5hcHBlbmRDaGlsZChsVmlld1ske2luZGV4fV0pO2ApO1xuICAgIH1cbiAgfVxuICByZXR1cm4gbGluZXM7XG59XG5cbi8qKlxuICogQ29udmVydHMgYEkxOG5VcGRhdGVPcENvZGVzYCBhcnJheSBpbnRvIGEgaHVtYW4gcmVhZGFibGUgZm9ybWF0LlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gaXMgYXR0YWNoZWQgdG8gdGhlIGBJMThuVXBkYXRlT3BDb2Rlcy5kZWJ1Z2AgcHJvcGVydHkgaWYgYG5nRGV2TW9kZWAgaXMgZW5hYmxlZC5cbiAqIFRoaXMgZnVuY3Rpb24gcHJvdmlkZXMgYSBodW1hbiByZWFkYWJsZSB2aWV3IG9mIHRoZSBvcGNvZGVzLiBUaGlzIGlzIHVzZWZ1bCB3aGVuIGRlYnVnZ2luZyB0aGVcbiAqIGFwcGxpY2F0aW9uIGFzIHdlbGwgYXMgd3JpdGluZyBtb3JlIHJlYWRhYmxlIHRlc3RzLlxuICpcbiAqIEBwYXJhbSB0aGlzIGBJMThuVXBkYXRlT3BDb2Rlc2AgaWYgYXR0YWNoZWQgYXMgYSBtZXRob2QuXG4gKiBAcGFyYW0gb3Bjb2RlcyBgSTE4blVwZGF0ZU9wQ29kZXNgIGlmIGludm9rZWQgYXMgYSBmdW5jdGlvbi5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGkxOG5VcGRhdGVPcENvZGVzVG9TdHJpbmcoXG4gICAgdGhpczogSTE4blVwZGF0ZU9wQ29kZXN8dm9pZCwgb3Bjb2Rlcz86IEkxOG5VcGRhdGVPcENvZGVzKTogc3RyaW5nW10ge1xuICBjb25zdCBwYXJzZXIgPSBuZXcgT3BDb2RlUGFyc2VyKG9wY29kZXMgfHwgKEFycmF5LmlzQXJyYXkodGhpcykgPyB0aGlzIDogW10pKTtcbiAgbGV0IGxpbmVzOiBzdHJpbmdbXSA9IFtdO1xuXG4gIGZ1bmN0aW9uIGNvbnN1bWVPcENvZGUodmFsdWU6IG51bWJlcik6IHN0cmluZyB7XG4gICAgY29uc3QgcmVmID0gdmFsdWUgPj4+IEkxOG5VcGRhdGVPcENvZGUuU0hJRlRfUkVGO1xuICAgIGNvbnN0IG9wQ29kZSA9IHZhbHVlICYgSTE4blVwZGF0ZU9wQ29kZS5NQVNLX09QQ09ERTtcbiAgICBzd2l0Y2ggKG9wQ29kZSkge1xuICAgICAgY2FzZSBJMThuVXBkYXRlT3BDb2RlLlRleHQ6XG4gICAgICAgIHJldHVybiBgKGxWaWV3WyR7cmVmfV0gYXMgVGV4dCkudGV4dENvbnRlbnQgPSAkJCRgO1xuICAgICAgY2FzZSBJMThuVXBkYXRlT3BDb2RlLkF0dHI6XG4gICAgICAgIGNvbnN0IGF0dHJOYW1lID0gcGFyc2VyLmNvbnN1bWVTdHJpbmcoKTtcbiAgICAgICAgY29uc3Qgc2FuaXRpemF0aW9uRm4gPSBwYXJzZXIuY29uc3VtZUZ1bmN0aW9uKCk7XG4gICAgICAgIGNvbnN0IHZhbHVlID0gc2FuaXRpemF0aW9uRm4gPyBgKCR7c2FuaXRpemF0aW9uRm59KSgkJCQpYCA6ICckJCQnO1xuICAgICAgICByZXR1cm4gYChsVmlld1ske3JlZn1dIGFzIEVsZW1lbnQpLnNldEF0dHJpYnV0ZSgnJHthdHRyTmFtZX0nLCAke3ZhbHVlfSlgO1xuICAgICAgY2FzZSBJMThuVXBkYXRlT3BDb2RlLkljdVN3aXRjaDpcbiAgICAgICAgcmV0dXJuIGBpY3VTd2l0Y2hDYXNlKCR7cmVmfSwgJCQkKWA7XG4gICAgICBjYXNlIEkxOG5VcGRhdGVPcENvZGUuSWN1VXBkYXRlOlxuICAgICAgICByZXR1cm4gYGljdVVwZGF0ZUNhc2UoJHtyZWZ9KWA7XG4gICAgfVxuICAgIHRocm93IG5ldyBFcnJvcigndW5leHBlY3RlZCBPcENvZGUnKTtcbiAgfVxuXG5cbiAgd2hpbGUgKHBhcnNlci5oYXNNb3JlKCkpIHtcbiAgICBsZXQgbWFzayA9IHBhcnNlci5jb25zdW1lTnVtYmVyKCk7XG4gICAgbGV0IHNpemUgPSBwYXJzZXIuY29uc3VtZU51bWJlcigpO1xuICAgIGNvbnN0IGVuZCA9IHBhcnNlci5pICsgc2l6ZTtcbiAgICBjb25zdCBzdGF0ZW1lbnRzOiBzdHJpbmdbXSA9IFtdO1xuICAgIGxldCBzdGF0ZW1lbnQgPSAnJztcbiAgICB3aGlsZSAocGFyc2VyLmkgPCBlbmQpIHtcbiAgICAgIGxldCB2YWx1ZSA9IHBhcnNlci5jb25zdW1lTnVtYmVyT3JTdHJpbmcoKTtcbiAgICAgIGlmICh0eXBlb2YgdmFsdWUgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgIHN0YXRlbWVudCArPSB2YWx1ZTtcbiAgICAgIH0gZWxzZSBpZiAodmFsdWUgPCAwKSB7XG4gICAgICAgIC8vIE5lZ2F0aXZlIG51bWJlcnMgYXJlIHJlZiBpbmRleGVzXG4gICAgICAgIC8vIEhlcmUgYGlgIHJlZmVycyB0byBjdXJyZW50IGJpbmRpbmcgaW5kZXguIEl0IGlzIHRvIHNpZ25pZnkgdGhhdCB0aGUgdmFsdWUgaXMgcmVsYXRpdmUsXG4gICAgICAgIC8vIHJhdGhlciB0aGFuIGFic29sdXRlLlxuICAgICAgICBzdGF0ZW1lbnQgKz0gJyR7bFZpZXdbaScgKyB2YWx1ZSArICddfSc7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICAvLyBQb3NpdGl2ZSBudW1iZXJzIGFyZSBvcGVyYXRpb25zLlxuICAgICAgICBjb25zdCBvcENvZGVUZXh0ID0gY29uc3VtZU9wQ29kZSh2YWx1ZSk7XG4gICAgICAgIHN0YXRlbWVudHMucHVzaChvcENvZGVUZXh0LnJlcGxhY2UoJyQkJCcsICdgJyArIHN0YXRlbWVudCArICdgJykgKyAnOycpO1xuICAgICAgICBzdGF0ZW1lbnQgPSAnJztcbiAgICAgIH1cbiAgICB9XG4gICAgbGluZXMucHVzaChgaWYgKG1hc2sgJiAwYiR7bWFzay50b1N0cmluZygyKX0pIHsgJHtzdGF0ZW1lbnRzLmpvaW4oJyAnKX0gfWApO1xuICB9XG4gIHJldHVybiBsaW5lcztcbn1cblxuLyoqXG4gKiBDb252ZXJ0cyBgSTE4bkNyZWF0ZU9wQ29kZXNgIGFycmF5IGludG8gYSBodW1hbiByZWFkYWJsZSBmb3JtYXQuXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBhdHRhY2hlZCB0byB0aGUgYEkxOG5DcmVhdGVPcENvZGVzLmRlYnVnYCBpZiBgbmdEZXZNb2RlYCBpcyBlbmFibGVkLiBUaGlzXG4gKiBmdW5jdGlvbiBwcm92aWRlcyBhIGh1bWFuIHJlYWRhYmxlIHZpZXcgb2YgdGhlIG9wY29kZXMuIFRoaXMgaXMgdXNlZnVsIHdoZW4gZGVidWdnaW5nIHRoZVxuICogYXBwbGljYXRpb24gYXMgd2VsbCBhcyB3cml0aW5nIG1vcmUgcmVhZGFibGUgdGVzdHMuXG4gKlxuICogQHBhcmFtIHRoaXMgYEkxOG5DcmVhdGVPcENvZGVzYCBpZiBhdHRhY2hlZCBhcyBhIG1ldGhvZC5cbiAqIEBwYXJhbSBvcGNvZGVzIGBJMThuQ3JlYXRlT3BDb2Rlc2AgaWYgaW52b2tlZCBhcyBhIGZ1bmN0aW9uLlxuICovXG5leHBvcnQgZnVuY3Rpb24gaWN1Q3JlYXRlT3BDb2Rlc1RvU3RyaW5nKFxuICAgIHRoaXM6IEljdUNyZWF0ZU9wQ29kZXN8dm9pZCwgb3Bjb2Rlcz86IEljdUNyZWF0ZU9wQ29kZXMpOiBzdHJpbmdbXSB7XG4gIGNvbnN0IHBhcnNlciA9IG5ldyBPcENvZGVQYXJzZXIob3Bjb2RlcyB8fCAoQXJyYXkuaXNBcnJheSh0aGlzKSA/IHRoaXMgOiBbXSkpO1xuICBsZXQgbGluZXM6IHN0cmluZ1tdID0gW107XG5cbiAgZnVuY3Rpb24gY29uc3VtZU9wQ29kZShvcENvZGU6IG51bWJlcik6IHN0cmluZyB7XG4gICAgY29uc3QgcGFyZW50ID0gZ2V0UGFyZW50RnJvbUljdUNyZWF0ZU9wQ29kZShvcENvZGUpO1xuICAgIGNvbnN0IHJlZiA9IGdldFJlZkZyb21JY3VDcmVhdGVPcENvZGUob3BDb2RlKTtcbiAgICBzd2l0Y2ggKGdldEluc3RydWN0aW9uRnJvbUljdUNyZWF0ZU9wQ29kZShvcENvZGUpKSB7XG4gICAgICBjYXNlIEljdUNyZWF0ZU9wQ29kZS5BcHBlbmRDaGlsZDpcbiAgICAgICAgcmV0dXJuIGAobFZpZXdbJHtwYXJlbnR9XSBhcyBFbGVtZW50KS5hcHBlbmRDaGlsZChsVmlld1ske2xhc3RSZWZ9XSlgO1xuICAgICAgY2FzZSBJY3VDcmVhdGVPcENvZGUuQXR0cjpcbiAgICAgICAgcmV0dXJuIGAobFZpZXdbJHtyZWZ9XSBhcyBFbGVtZW50KS5zZXRBdHRyaWJ1dGUoXCIke3BhcnNlci5jb25zdW1lU3RyaW5nKCl9XCIsIFwiJHtcbiAgICAgICAgICAgIHBhcnNlci5jb25zdW1lU3RyaW5nKCl9XCIpYDtcbiAgICB9XG4gICAgdGhyb3cgbmV3IEVycm9yKCdVbmV4cGVjdGVkIE9wQ29kZTogJyArIGdldEluc3RydWN0aW9uRnJvbUljdUNyZWF0ZU9wQ29kZShvcENvZGUpKTtcbiAgfVxuXG4gIGxldCBsYXN0UmVmID0gLTE7XG4gIHdoaWxlIChwYXJzZXIuaGFzTW9yZSgpKSB7XG4gICAgbGV0IHZhbHVlID0gcGFyc2VyLmNvbnN1bWVOdW1iZXJTdHJpbmdPck1hcmtlcigpO1xuICAgIGlmICh2YWx1ZSA9PT0gSUNVX01BUktFUikge1xuICAgICAgY29uc3QgdGV4dCA9IHBhcnNlci5jb25zdW1lU3RyaW5nKCk7XG4gICAgICBsYXN0UmVmID0gcGFyc2VyLmNvbnN1bWVOdW1iZXIoKTtcbiAgICAgIGxpbmVzLnB1c2goYGxWaWV3WyR7bGFzdFJlZn1dID0gZG9jdW1lbnQuY3JlYXRlQ29tbWVudChcIiR7dGV4dH1cIilgKTtcbiAgICB9IGVsc2UgaWYgKHZhbHVlID09PSBFTEVNRU5UX01BUktFUikge1xuICAgICAgY29uc3QgdGV4dCA9IHBhcnNlci5jb25zdW1lU3RyaW5nKCk7XG4gICAgICBsYXN0UmVmID0gcGFyc2VyLmNvbnN1bWVOdW1iZXIoKTtcbiAgICAgIGxpbmVzLnB1c2goYGxWaWV3WyR7bGFzdFJlZn1dID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudChcIiR7dGV4dH1cIilgKTtcbiAgICB9IGVsc2UgaWYgKHR5cGVvZiB2YWx1ZSA9PT0gJ3N0cmluZycpIHtcbiAgICAgIGxhc3RSZWYgPSBwYXJzZXIuY29uc3VtZU51bWJlcigpO1xuICAgICAgbGluZXMucHVzaChgbFZpZXdbJHtsYXN0UmVmfV0gPSBkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZShcIiR7dmFsdWV9XCIpYCk7XG4gICAgfSBlbHNlIGlmICh0eXBlb2YgdmFsdWUgPT09ICdudW1iZXInKSB7XG4gICAgICBjb25zdCBsaW5lID0gY29uc3VtZU9wQ29kZSh2YWx1ZSk7XG4gICAgICBsaW5lICYmIGxpbmVzLnB1c2gobGluZSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcignVW5leHBlY3RlZCB2YWx1ZScpO1xuICAgIH1cbiAgfVxuXG4gIHJldHVybiBsaW5lcztcbn1cblxuLyoqXG4gKiBDb252ZXJ0cyBgSTE4blJlbW92ZU9wQ29kZXNgIGFycmF5IGludG8gYSBodW1hbiByZWFkYWJsZSBmb3JtYXQuXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBhdHRhY2hlZCB0byB0aGUgYEkxOG5SZW1vdmVPcENvZGVzLmRlYnVnYCBpZiBgbmdEZXZNb2RlYCBpcyBlbmFibGVkLiBUaGlzXG4gKiBmdW5jdGlvbiBwcm92aWRlcyBhIGh1bWFuIHJlYWRhYmxlIHZpZXcgb2YgdGhlIG9wY29kZXMuIFRoaXMgaXMgdXNlZnVsIHdoZW4gZGVidWdnaW5nIHRoZVxuICogYXBwbGljYXRpb24gYXMgd2VsbCBhcyB3cml0aW5nIG1vcmUgcmVhZGFibGUgdGVzdHMuXG4gKlxuICogQHBhcmFtIHRoaXMgYEkxOG5SZW1vdmVPcENvZGVzYCBpZiBhdHRhY2hlZCBhcyBhIG1ldGhvZC5cbiAqIEBwYXJhbSBvcGNvZGVzIGBJMThuUmVtb3ZlT3BDb2Rlc2AgaWYgaW52b2tlZCBhcyBhIGZ1bmN0aW9uLlxuICovXG5leHBvcnQgZnVuY3Rpb24gaTE4blJlbW92ZU9wQ29kZXNUb1N0cmluZyhcbiAgICB0aGlzOiBJMThuUmVtb3ZlT3BDb2Rlc3x2b2lkLCBvcGNvZGVzPzogSTE4blJlbW92ZU9wQ29kZXMpOiBzdHJpbmdbXSB7XG4gIGNvbnN0IHJlbW92ZUNvZGVzID0gb3Bjb2RlcyB8fCAoQXJyYXkuaXNBcnJheSh0aGlzKSA/IHRoaXMgOiBbXSk7XG4gIGxldCBsaW5lczogc3RyaW5nW10gPSBbXTtcblxuICBmb3IgKGxldCBpID0gMDsgaSA8IHJlbW92ZUNvZGVzLmxlbmd0aDsgaSsrKSB7XG4gICAgY29uc3Qgbm9kZU9ySWN1SW5kZXggPSByZW1vdmVDb2Rlc1tpXSBhcyBudW1iZXI7XG4gICAgaWYgKG5vZGVPckljdUluZGV4ID4gMCkge1xuICAgICAgLy8gUG9zaXRpdmUgbnVtYmVycyBhcmUgYFJOb2RlYHMuXG4gICAgICBsaW5lcy5wdXNoKGByZW1vdmUobFZpZXdbJHtub2RlT3JJY3VJbmRleH1dKWApO1xuICAgIH0gZWxzZSB7XG4gICAgICAvLyBOZWdhdGl2ZSBudW1iZXJzIGFyZSBJQ1VzXG4gICAgICBsaW5lcy5wdXNoKGByZW1vdmVOZXN0ZWRJQ1UoJHt+bm9kZU9ySWN1SW5kZXh9KWApO1xuICAgIH1cbiAgfVxuXG4gIHJldHVybiBsaW5lcztcbn1cblxuXG5jbGFzcyBPcENvZGVQYXJzZXIge1xuICBpOiBudW1iZXIgPSAwO1xuICBjb2RlczogYW55W107XG5cbiAgY29uc3RydWN0b3IoY29kZXM6IGFueVtdKSB7XG4gICAgdGhpcy5jb2RlcyA9IGNvZGVzO1xuICB9XG5cbiAgaGFzTW9yZSgpIHtcbiAgICByZXR1cm4gdGhpcy5pIDwgdGhpcy5jb2Rlcy5sZW5ndGg7XG4gIH1cblxuICBjb25zdW1lTnVtYmVyKCk6IG51bWJlciB7XG4gICAgbGV0IHZhbHVlID0gdGhpcy5jb2Rlc1t0aGlzLmkrK107XG4gICAgYXNzZXJ0TnVtYmVyKHZhbHVlLCAnZXhwZWN0aW5nIG51bWJlciBpbiBPcENvZGUnKTtcbiAgICByZXR1cm4gdmFsdWU7XG4gIH1cblxuICBjb25zdW1lU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgbGV0IHZhbHVlID0gdGhpcy5jb2Rlc1t0aGlzLmkrK107XG4gICAgYXNzZXJ0U3RyaW5nKHZhbHVlLCAnZXhwZWN0aW5nIHN0cmluZyBpbiBPcENvZGUnKTtcbiAgICByZXR1cm4gdmFsdWU7XG4gIH1cblxuICBjb25zdW1lRnVuY3Rpb24oKTogRnVuY3Rpb258bnVsbCB7XG4gICAgbGV0IHZhbHVlID0gdGhpcy5jb2Rlc1t0aGlzLmkrK107XG4gICAgaWYgKHZhbHVlID09PSBudWxsIHx8IHR5cGVvZiB2YWx1ZSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgcmV0dXJuIHZhbHVlO1xuICAgIH1cbiAgICB0aHJvdyBuZXcgRXJyb3IoJ2V4cGVjdGluZyBmdW5jdGlvbiBpbiBPcENvZGUnKTtcbiAgfVxuXG4gIGNvbnN1bWVOdW1iZXJPclN0cmluZygpOiBudW1iZXJ8c3RyaW5nIHtcbiAgICBsZXQgdmFsdWUgPSB0aGlzLmNvZGVzW3RoaXMuaSsrXTtcbiAgICBpZiAodHlwZW9mIHZhbHVlID09PSAnc3RyaW5nJykge1xuICAgICAgcmV0dXJuIHZhbHVlO1xuICAgIH1cbiAgICBhc3NlcnROdW1iZXIodmFsdWUsICdleHBlY3RpbmcgbnVtYmVyIG9yIHN0cmluZyBpbiBPcENvZGUnKTtcbiAgICByZXR1cm4gdmFsdWU7XG4gIH1cblxuICBjb25zdW1lTnVtYmVyU3RyaW5nT3JNYXJrZXIoKTogbnVtYmVyfHN0cmluZ3xJQ1VfTUFSS0VSfEVMRU1FTlRfTUFSS0VSIHtcbiAgICBsZXQgdmFsdWUgPSB0aGlzLmNvZGVzW3RoaXMuaSsrXTtcbiAgICBpZiAodHlwZW9mIHZhbHVlID09PSAnc3RyaW5nJyB8fCB0eXBlb2YgdmFsdWUgPT09ICdudW1iZXInIHx8IHZhbHVlID09IElDVV9NQVJLRVIgfHxcbiAgICAgICAgdmFsdWUgPT0gRUxFTUVOVF9NQVJLRVIpIHtcbiAgICAgIHJldHVybiB2YWx1ZTtcbiAgICB9XG4gICAgYXNzZXJ0TnVtYmVyKHZhbHVlLCAnZXhwZWN0aW5nIG51bWJlciwgc3RyaW5nLCBJQ1VfTUFSS0VSIG9yIEVMRU1FTlRfTUFSS0VSIGluIE9wQ29kZScpO1xuICAgIHJldHVybiB2YWx1ZTtcbiAgfVxufVxuIl19