import { isProceduralRenderer } from '../interfaces/renderer';
/**
 * Assigns all attribute values to the provided element via the inferred renderer.
 *
 * This function accepts two forms of attribute entries:
 *
 * default: (key, value):
 *  attrs = [key1, value1, key2, value2]
 *
 * namespaced: (NAMESPACE_MARKER, uri, name, value)
 *  attrs = [NAMESPACE_MARKER, uri, name, value, NAMESPACE_MARKER, uri, name, value]
 *
 * The `attrs` array can contain a mix of both the default and namespaced entries.
 * The "default" values are set without a marker, but if the function comes across
 * a marker value then it will attempt to set a namespaced value. If the marker is
 * not of a namespaced value then the function will quit and return the index value
 * where it stopped during the iteration of the attrs array.
 *
 * See [AttributeMarker] to understand what the namespace marker value is.
 *
 * Note that this instruction does not support assigning style and class values to
 * an element. See `elementStart` and `elementHostAttrs` to learn how styling values
 * are applied to an element.
 * @param renderer The renderer to be used
 * @param native The element that the attributes will be assigned to
 * @param attrs The attribute array of values that will be assigned to the element
 * @returns the index value that was last accessed in the attributes array
 */
export function setUpAttributes(renderer, native, attrs) {
    const isProc = isProceduralRenderer(renderer);
    let i = 0;
    while (i < attrs.length) {
        const value = attrs[i];
        if (typeof value === 'number') {
            // only namespaces are supported. Other value types (such as style/class
            // entries) are not supported in this function.
            if (value !== 0 /* NamespaceURI */) {
                break;
            }
            // we just landed on the marker value ... therefore
            // we should skip to the next entry
            i++;
            const namespaceURI = attrs[i++];
            const attrName = attrs[i++];
            const attrVal = attrs[i++];
            ngDevMode && ngDevMode.rendererSetAttribute++;
            isProc ?
                renderer.setAttribute(native, attrName, attrVal, namespaceURI) :
                native.setAttributeNS(namespaceURI, attrName, attrVal);
        }
        else {
            // attrName is string;
            const attrName = value;
            const attrVal = attrs[++i];
            // Standard attributes
            ngDevMode && ngDevMode.rendererSetAttribute++;
            if (isAnimationProp(attrName)) {
                if (isProc) {
                    renderer.setProperty(native, attrName, attrVal);
                }
            }
            else {
                isProc ?
                    renderer.setAttribute(native, attrName, attrVal) :
                    native.setAttribute(attrName, attrVal);
            }
            i++;
        }
    }
    // another piece of code may iterate over the same attributes array. Therefore
    // it may be helpful to return the exact spot where the attributes array exited
    // whether by running into an unsupported marker or if all the static values were
    // iterated over.
    return i;
}
/**
 * Test whether the given value is a marker that indicates that the following
 * attribute values in a `TAttributes` array are only the names of attributes,
 * and not name-value pairs.
 * @param marker The attribute marker to test.
 * @returns true if the marker is a "name-only" marker (e.g. `Bindings`, `Template` or `I18n`).
 */
export function isNameOnlyAttributeMarker(marker) {
    return marker === 3 /* Bindings */ || marker === 4 /* Template */ ||
        marker === 6 /* I18n */;
}
export function isAnimationProp(name) {
    // Perf note: accessing charCodeAt to check for the first character of a string is faster as
    // compared to accessing a character at index 0 (ex. name[0]). The main reason for this is that
    // charCodeAt doesn't allocate memory to return a substring.
    return name.charCodeAt(0) === 64 /* AT_SIGN */;
}
/**
 * Merges `src` `TAttributes` into `dst` `TAttributes` removing any duplicates in the process.
 *
 * This merge function keeps the order of attrs same.
 *
 * @param dst Location of where the merged `TAttributes` should end up.
 * @param src `TAttributes` which should be appended to `dst`
 */
export function mergeHostAttrs(dst, src) {
    if (src === null || src.length === 0) {
        // do nothing
    }
    else if (dst === null || dst.length === 0) {
        // We have source, but dst is empty, just make a copy.
        dst = src.slice();
    }
    else {
        let srcMarker = -1 /* ImplicitAttributes */;
        for (let i = 0; i < src.length; i++) {
            const item = src[i];
            if (typeof item === 'number') {
                srcMarker = item;
            }
            else {
                if (srcMarker === 0 /* NamespaceURI */) {
                    // Case where we need to consume `key1`, `key2`, `value` items.
                }
                else if (srcMarker === -1 /* ImplicitAttributes */ ||
                    srcMarker === 2 /* Styles */) {
                    // Case where we have to consume `key1` and `value` only.
                    mergeHostAttribute(dst, srcMarker, item, null, src[++i]);
                }
                else {
                    // Case where we have to consume `key1` only.
                    mergeHostAttribute(dst, srcMarker, item, null, null);
                }
            }
        }
    }
    return dst;
}
/**
 * Append `key`/`value` to existing `TAttributes` taking region marker and duplicates into account.
 *
 * @param dst `TAttributes` to append to.
 * @param marker Region where the `key`/`value` should be added.
 * @param key1 Key to add to `TAttributes`
 * @param key2 Key to add to `TAttributes` (in case of `AttributeMarker.NamespaceURI`)
 * @param value Value to add or to overwrite to `TAttributes` Only used if `marker` is not Class.
 */
export function mergeHostAttribute(dst, marker, key1, key2, value) {
    let i = 0;
    // Assume that new markers will be inserted at the end.
    let markerInsertPosition = dst.length;
    // scan until correct type.
    if (marker === -1 /* ImplicitAttributes */) {
        markerInsertPosition = -1;
    }
    else {
        while (i < dst.length) {
            const dstValue = dst[i++];
            if (typeof dstValue === 'number') {
                if (dstValue === marker) {
                    markerInsertPosition = -1;
                    break;
                }
                else if (dstValue > marker) {
                    // We need to save this as we want the markers to be inserted in specific order.
                    markerInsertPosition = i - 1;
                    break;
                }
            }
        }
    }
    // search until you find place of insertion
    while (i < dst.length) {
        const item = dst[i];
        if (typeof item === 'number') {
            // since `i` started as the index after the marker, we did not find it if we are at the next
            // marker
            break;
        }
        else if (item === key1) {
            // We already have same token
            if (key2 === null) {
                if (value !== null) {
                    dst[i + 1] = value;
                }
                return;
            }
            else if (key2 === dst[i + 1]) {
                dst[i + 2] = value;
                return;
            }
        }
        // Increment counter.
        i++;
        if (key2 !== null)
            i++;
        if (value !== null)
            i++;
    }
    // insert at location.
    if (markerInsertPosition !== -1) {
        dst.splice(markerInsertPosition, 0, marker);
        i = markerInsertPosition + 1;
    }
    dst.splice(i++, 0, key1);
    if (key2 !== null) {
        dst.splice(i++, 0, key2);
    }
    if (value !== null) {
        dst.splice(i++, 0, value);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXR0cnNfdXRpbHMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL3V0aWwvYXR0cnNfdXRpbHMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBVUEsT0FBTyxFQUFDLG9CQUFvQixFQUFpQyxNQUFNLHdCQUF3QixDQUFDO0FBSzVGOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQTBCRztBQUNILE1BQU0sVUFBVSxlQUFlLENBQUMsUUFBbUIsRUFBRSxNQUFnQixFQUFFLEtBQWtCO0lBQ3ZGLE1BQU0sTUFBTSxHQUFHLG9CQUFvQixDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBRTlDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztJQUNWLE9BQU8sQ0FBQyxHQUFHLEtBQUssQ0FBQyxNQUFNLEVBQUU7UUFDdkIsTUFBTSxLQUFLLEdBQUcsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ3ZCLElBQUksT0FBTyxLQUFLLEtBQUssUUFBUSxFQUFFO1lBQzdCLHdFQUF3RTtZQUN4RSwrQ0FBK0M7WUFDL0MsSUFBSSxLQUFLLHlCQUFpQyxFQUFFO2dCQUMxQyxNQUFNO2FBQ1A7WUFFRCxtREFBbUQ7WUFDbkQsbUNBQW1DO1lBQ25DLENBQUMsRUFBRSxDQUFDO1lBRUosTUFBTSxZQUFZLEdBQUcsS0FBSyxDQUFDLENBQUMsRUFBRSxDQUFXLENBQUM7WUFDMUMsTUFBTSxRQUFRLEdBQUcsS0FBSyxDQUFDLENBQUMsRUFBRSxDQUFXLENBQUM7WUFDdEMsTUFBTSxPQUFPLEdBQUcsS0FBSyxDQUFDLENBQUMsRUFBRSxDQUFXLENBQUM7WUFDckMsU0FBUyxJQUFJLFNBQVMsQ0FBQyxvQkFBb0IsRUFBRSxDQUFDO1lBQzlDLE1BQU0sQ0FBQyxDQUFDO2dCQUNILFFBQWdDLENBQUMsWUFBWSxDQUFDLE1BQU0sRUFBRSxRQUFRLEVBQUUsT0FBTyxFQUFFLFlBQVksQ0FBQyxDQUFD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RyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBRTtnQkFDOUIsR0FBRyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBRyxLQUFNLENBQUM7Z0JBQ3BCLE9BQU87YUFDUjtTQUNGO1FBQ0QscUJBQXFCO1FBQ3JCLENBQUMsRUFBRSxDQUFDO1FBQ0osSUFBSSxJQUFJLEtBQUssSUFBSTtZQUFFLENBQUMsRUFBRSxDQUFDO1FBQ3ZCLElBQUksS0FBSyxLQUFLLElBQUk7WUFBRSxDQUFDLEVBQUUsQ0FBQztLQUN6QjtJQUVELHNCQUFzQjtJQUN0QixJQUFJLG9CQUFvQixLQUFLLENBQUMsQ0FBQyxFQUFFO1FBQy9CLEdBQUcsQ0FBQyxNQUFNLENBQUMsb0JBQW9CLEVBQUUsQ0FBQyxFQUFFLE1BQU0sQ0FBQyxDQUFDO1FBQzVDLENBQUMsR0FBRyxvQkFBb0IsR0FBRyxDQUFDLENBQUM7S0FDOUI7SUFDRCxHQUFHLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FBQztJQUN6QixJQUFJLElBQUksS0FBSyxJQUFJLEVBQUU7UUFDakIsR0FBRyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUM7S0FDMUI7SUFDRCxJQUFJLEtBQUssS0FBSyxJQUFJLEVBQUU7UUFDbEIsR0FBRyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBRSxDQUFDLEVBQUUsS0FBSyxDQUFDLENBQUM7S0FDM0I7QUFDSCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5pbXBvcnQge0NoYXJDb2RlfSBmcm9tICcuLi8uLi91dGlsL2NoYXJfY29kZSc7XG5pbXBvcnQge0F0dHJpYnV0ZU1hcmtlciwgVEF0dHJpYnV0ZXN9IGZyb20gJy4uL2ludGVyZmFjZXMvbm9kZSc7XG5pbXBvcnQge0Nzc1NlbGVjdG9yfSBmcm9tICcuLi9pbnRlcmZhY2VzL3Byb2plY3Rpb24nO1xuaW1wb3J0IHtpc1Byb2NlZHVyYWxSZW5kZXJlciwgUHJvY2VkdXJhbFJlbmRlcmVyMywgUmVuZGVyZXIzfSBmcm9tICcuLi9pbnRlcmZhY2VzL3JlbmRlcmVyJztcbmltcG9ydCB7UkVsZW1lbnR9IGZyb20gJy4uL2ludGVyZmFjZXMvcmVuZGVyZXJfZG9tJztcblxuXG5cbi8qKlxuICogQXNzaWducyBhbGwgYXR0cmlidXRlIHZhbHVlcyB0byB0aGUgcHJvdmlkZWQgZWxlbWVudCB2aWEgdGhlIGluZmVycmVkIHJlbmRlcmVyLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gYWNjZXB0cyB0d28gZm9ybXMgb2YgYXR0cmlidXRlIGVudHJpZXM6XG4gKlxuICogZGVmYXVsdDogKGtleSwgdmFsdWUpOlxuICogIGF0dHJzID0gW2tleTEsIHZhbHVlMSwga2V5MiwgdmFsdWUyXVxuICpcbiAqIG5hbWVzcGFjZWQ6IChOQU1FU1BBQ0VfTUFSS0VSLCB1cmksIG5hbWUsIHZhbHVlKVxuICogIGF0dHJzID0gW05BTUVTUEFDRV9NQVJLRVIsIHVyaSwgbmFtZSwgdmFsdWUsIE5BTUVTUEFDRV9NQVJLRVIsIHVyaSwgbmFtZSwgdmFsdWVdXG4gKlxuICogVGhlIGBhdHRyc2AgYXJyYXkgY2FuIGNvbnRhaW4gYSBtaXggb2YgYm90aCB0aGUgZGVmYXVsdCBhbmQgbmFtZXNwYWNlZCBlbnRyaWVzLlxuICogVGhlIFwiZGVmYXVsdFwiIHZhbHVlcyBhcmUgc2V0IHdpdGhvdXQgYSBtYXJrZXIsIGJ1dCBpZiB0aGUgZnVuY3Rpb24gY29tZXMgYWNyb3NzXG4gKiBhIG1hcmtlciB2YWx1ZSB0aGVuIGl0IHdpbGwgYXR0ZW1wdCB0byBzZXQgYSBuYW1lc3BhY2VkIHZhbHVlLiBJZiB0aGUgbWFya2VyIGlzXG4gKiBub3Qgb2YgYSBuYW1lc3BhY2VkIHZhbHVlIHRoZW4gdGhlIGZ1bmN0aW9uIHdpbGwgcXVpdCBhbmQgcmV0dXJuIHRoZSBpbmRleCB2YWx1ZVxuICogd2hlcmUgaXQgc3RvcHBlZCBkdXJpbmcgdGhlIGl0ZXJhdGlvbiBvZiB0aGUgYXR0cnMgYXJyYXkuXG4gKlxuICogU2VlIFtBdHRyaWJ1dGVNYXJrZXJdIHRvIHVuZGVyc3RhbmQgd2hhdCB0aGUgbmFtZXNwYWNlIG1hcmtlciB2YWx1ZSBpcy5cbiAqXG4gKiBOb3RlIHRoYXQgdGhpcyBpbnN0cnVjdGlvbiBkb2VzIG5vdCBzdXBwb3J0IGFzc2lnbmluZyBzdHlsZSBhbmQgY2xhc3MgdmFsdWVzIHRvXG4gKiBhbiBlbGVtZW50LiBTZWUgYGVsZW1lbnRTdGFydGAgYW5kIGBlbGVtZW50SG9zdEF0dHJzYCB0byBsZWFybiBob3cgc3R5bGluZyB2YWx1ZXNcbiAqIGFyZSBhcHBsaWVkIHRvIGFuIGVsZW1lbnQuXG4gKiBAcGFyYW0gcmVuZGVyZXIgVGhlIHJlbmRlcmVyIHRvIGJlIHVzZWRcbiAqIEBwYXJhbSBuYXRpdmUgVGhlIGVsZW1lbnQgdGhhdCB0aGUgYXR0cmlidXRlcyB3aWxsIGJlIGFzc2lnbmVkIHRvXG4gKiBAcGFyYW0gYXR0cnMgVGhlIGF0dHJpYnV0ZSBhcnJheSBvZiB2YWx1ZXMgdGhhdCB3aWxsIGJlIGFzc2lnbmVkIHRvIHRoZSBlbGVtZW50XG4gKiBAcmV0dXJucyB0aGUgaW5kZXggdmFsdWUgdGhhdCB3YXMgbGFzdCBhY2Nlc3NlZCBpbiB0aGUgYXR0cmlidXRlcyBhcnJheVxuICovXG5leHBvcnQgZnVuY3Rpb24gc2V0VXBBdHRyaWJ1dGVzKHJlbmRlcmVyOiBSZW5kZXJlcjMsIG5hdGl2ZTogUkVsZW1lbnQsIGF0dHJzOiBUQXR0cmlidXRlcyk6IG51bWJlciB7XG4gIGNvbnN0IGlzUHJvYyA9IGlzUHJvY2VkdXJhbFJlbmRlcmVyKHJlbmRlcmVyKTtcblxuICBsZXQgaSA9IDA7XG4gIHdoaWxlIChpIDwgYXR0cnMubGVuZ3RoKSB7XG4gICAgY29uc3QgdmFsdWUgPSBhdHRyc1tpXTtcbiAgICBpZiAodHlwZW9mIHZhbHVlID09PSAnbnVtYmVyJykge1xuICAgICAgLy8gb25seSBuYW1lc3BhY2VzIGFyZSBzdXBwb3J0ZWQuIE90aGVyIHZhbHVlIHR5cGVzIChzdWNoIGFzIHN0eWxlL2NsYXNzXG4gICAgICAvLyBlbnRyaWVzKSBhcmUgbm90IHN1cHBvcnRlZCBpbiB0aGlzIGZ1bmN0aW9uLlxuICAgICAgaWYgKHZhbHVlICE9PSBBdHRyaWJ1dGVNYXJrZXIuTmFtZXNwYWNlVVJJKSB7XG4gICAgICAgIGJyZWFrO1xuICAgICAgfVxuXG4gICAgICAvLyB3ZSBqdXN0IGxhbmRlZCBvbiB0aGUgbWFya2VyIHZhbHVlIC4uLiB0aGVyZWZvcmVcbiAgICAgIC8vIHdlIHNob3VsZCBza2lwIHRvIHRoZSBuZXh0IGVudHJ5XG4gICAgICBpKys7XG5cbiAgICAgIGNvbnN0IG5hbWVzcGFjZVVSSSA9IGF0dHJzW2krK10gYXMgc3RyaW5nO1xuICAgICAgY29uc3QgYXR0ck5hbWUgPSBhdHRyc1tpKytdIGFzIHN0cmluZztcbiAgICAgIGNvbnN0IGF0dHJWYWwgPSBhdHRyc1tpKytdIGFzIHN0cmluZztcbiAgICAgIG5nRGV2TW9kZSAmJiBuZ0Rldk1vZGUucmVuZGVyZXJTZXRBdHRyaWJ1dGUrKztcbiAgICAgIGlzUHJvYyA/XG4gICAgICAgICAgKHJlbmRlcmVyIGFzIFByb2NlZHVyYWxSZW5kZXJlcjMpLnNldEF0dHJpYnV0ZShuYXRpdmUsIGF0dHJOYW1lLCBhdHRyVmFsLCBuYW1lc3BhY2VVUkkpIDpcbiAgICAgICAgICBuYXRpdmUuc2V0QXR0cmlidXRlTlMobmFtZXNwYWNlVVJJLCBhdHRyTmFtZSwgYXR0clZhbCk7XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vIGF0dHJOYW1lIGlzIHN0cmluZztcbiAgICAgIGNvbnN0IGF0dHJOYW1lID0gdmFsdWUgYXMgc3RyaW5nO1xuICAgICAgY29uc3QgYXR0clZhbCA9IGF0dHJzWysraV07XG4gICAgICAvLyBTdGFuZGFyZCBhdHRyaWJ1dGVzXG4gICAgICBuZ0Rldk1vZGUgJiYgbmdEZXZNb2RlLnJlbmRlcmVyU2V0QXR0cmlidXRlKys7XG4gICAgICBpZiAoaXNBbmltYXRpb25Qcm9wKGF0dHJOYW1lKSkge1xuICAgICAgICBpZiAoaXNQcm9jKSB7XG4gICAgICAgICAgKHJlbmRlcmVyIGFzIFByb2NlZHVyYWxSZW5kZXJlcjMpLnNldFByb3BlcnR5KG5hdGl2ZSwgYXR0ck5hbWUsIGF0dHJWYWwpO1xuICAgICAgICB9XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBpc1Byb2MgP1xuICAgICAgICAgICAgKHJlbmRlcmVyIGFzIFByb2NlZHVyYWxSZW5kZXJlcjMpLnNldEF0dHJpYnV0ZShuYXRpdmUsIGF0dHJOYW1lLCBhdHRyVmFsIGFzIHN0cmluZykgOlxuICAgICAgICAgICAgbmF0aXZlLnNldEF0dHJpYnV0ZShhdHRyTmFtZSwgYXR0clZhbCBhcyBzdHJpbmcpO1xuICAgICAgfVxuICAgICAgaSsrO1xuICAgIH1cbiAgfVxuXG4gIC8vIGFub3RoZXIgcGllY2Ugb2YgY29kZSBtYXkgaXRlcmF0ZSBvdmVyIHRoZSBzYW1lIGF0dHJpYnV0ZXMgYXJyYXkuIFRoZXJlZm9yZVxuICAvLyBpdCBtYXkgYmUgaGVscGZ1bCB0byByZXR1cm4gdGhlIGV4YWN0IHNwb3Qgd2hlcmUgdGhlIGF0dHJpYnV0ZXMgYXJyYXkgZXhpdGVkXG4gIC8vIHdoZXRoZXIgYnkgcnVubmluZyBpbnRvIGFuIHVuc3VwcG9ydGVkIG1hcmtlciBvciBpZiBhbGwgdGhlIHN0YXRpYyB2YWx1ZXMgd2VyZVxuICAvLyBpdGVyYXRlZCBvdmVyLlxuICByZXR1cm4gaTtcbn1cblxuLyoqXG4gKiBUZXN0IHdoZXRoZXIgdGhlIGdpdmVuIHZhbHVlIGlzIGEgbWFya2VyIHRoYXQgaW5kaWNhdGVzIHRoYXQgdGhlIGZvbGxvd2luZ1xuICogYXR0cmlidXRlIHZhbHVlcyBpbiBhIGBUQXR0cmlidXRlc2AgYXJyYXkgYXJlIG9ubHkgdGhlIG5hbWVzIG9mIGF0dHJpYnV0ZXMsXG4gKiBhbmQgbm90IG5hbWUtdmFsdWUgcGFpcnMuXG4gKiBAcGFyYW0gbWFya2VyIFRoZSBhdHRyaWJ1dGUgbWFya2VyIHRvIHRlc3QuXG4gKiBAcmV0dXJucyB0cnVlIGlmIHRoZSBtYXJrZXIgaXMgYSBcIm5hbWUtb25seVwiIG1hcmtlciAoZS5nLiBgQmluZGluZ3NgLCBgVGVtcGxhdGVgIG9yIGBJMThuYCkuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpc05hbWVPbmx5QXR0cmlidXRlTWFya2VyKG1hcmtlcjogc3RyaW5nfEF0dHJpYnV0ZU1hcmtlcnxDc3NTZWxlY3Rvcikge1xuICByZXR1cm4gbWFya2VyID09PSBBdHRyaWJ1dGVNYXJrZXIuQmluZGluZ3MgfHwgbWFya2VyID09PSBBdHRyaWJ1dGVNYXJrZXIuVGVtcGxhdGUgfHxcbiAgICAgIG1hcmtlciA9PT0gQXR0cmlidXRlTWFya2VyLkkxOG47XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBpc0FuaW1hdGlvblByb3AobmFtZTogc3RyaW5nKTogYm9vbGVhbiB7XG4gIC8vIFBlcmYgbm90ZTogYWNjZXNzaW5nIGNoYXJDb2RlQXQgdG8gY2hlY2sgZm9yIHRoZSBmaXJzdCBjaGFyYWN0ZXIgb2YgYSBzdHJpbmcgaXMgZmFzdGVyIGFzXG4gIC8vIGNvbXBhcmVkIHRvIGFjY2Vzc2luZyBhIGNoYXJhY3RlciBhdCBpbmRleCAwIChleC4gbmFtZVswXSkuIFRoZSBtYWluIHJlYXNvbiBmb3IgdGhpcyBpcyB0aGF0XG4gIC8vIGNoYXJDb2RlQXQgZG9lc24ndCBhbGxvY2F0ZSBtZW1vcnkgdG8gcmV0dXJuIGEgc3Vic3RyaW5nLlxuICByZXR1cm4gbmFtZS5jaGFyQ29kZUF0KDApID09PSBDaGFyQ29kZS5BVF9TSUdOO1xufVxuXG4vKipcbiAqIE1lcmdlcyBgc3JjYCBgVEF0dHJpYnV0ZXNgIGludG8gYGRzdGAgYFRBdHRyaWJ1dGVzYCByZW1vdmluZyBhbnkgZHVwbGljYXRlcyBpbiB0aGUgcHJvY2Vzcy5cbiAqXG4gKiBUaGlzIG1lcmdlIGZ1bmN0aW9uIGtlZXBzIHRoZSBvcmRlciBvZiBhdHRycyBzYW1lLlxuICpcbiAqIEBwYXJhbSBkc3QgTG9jYXRpb24gb2Ygd2hlcmUgdGhlIG1lcmdlZCBgVEF0dHJpYnV0ZXNgIHNob3VsZCBlbmQgdXAuXG4gKiBAcGFyYW0gc3JjIGBUQXR0cmlidXRlc2Agd2hpY2ggc2hvdWxkIGJlIGFwcGVuZGVkIHRvIGBkc3RgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBtZXJnZUhvc3RBdHRycyhkc3Q6IFRBdHRyaWJ1dGVzfG51bGwsIHNyYzogVEF0dHJpYnV0ZXN8bnVsbCk6IFRBdHRyaWJ1dGVzfG51bGwge1xuICBpZiAoc3JjID09PSBudWxsIHx8IHNyYy5sZW5ndGggPT09IDApIHtcbiAgICAvLyBkbyBub3RoaW5nXG4gIH0gZWxzZSBpZiAoZHN0ID09PSBudWxsIHx8IGRzdC5sZW5ndGggPT09IDApIHtcbiAgICAvLyBXZSBoYXZlIHNvdXJjZSwgYnV0IGRzdCBpcyBlbXB0eSwganVzdCBtYWtlIGEgY29weS5cbiAgICBkc3QgPSBzcmMuc2xpY2UoKTtcbiAgfSBlbHNlIHtcbiAgICBsZXQgc3JjTWFya2VyOiBBdHRyaWJ1dGVNYXJrZXIgPSBBdHRyaWJ1dGVNYXJrZXIuSW1wbGljaXRBdHRyaWJ1dGVzO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgc3JjLmxlbmd0aDsgaSsrKSB7XG4gICAgICBjb25zdCBpdGVtID0gc3JjW2ldO1xuICAgICAgaWYgKHR5cGVvZiBpdGVtID09PSAnbnVtYmVyJykge1xuICAgICAgICBzcmNNYXJrZXIgPSBpdGVtO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKHNyY01hcmtlciA9PT0gQXR0cmlidXRlTWFya2VyLk5hbWVzcGFjZVVSSSkge1xuICAgICAgICAgIC8vIENhc2Ugd2hlcmUgd2UgbmVlZCB0byBjb25zdW1lIGBrZXkxYCwgYGtleTJgLCBgdmFsdWVgIGl0ZW1zLlxuICAgICAgICB9IGVsc2UgaWYgKFxuICAgICAgICAgICAgc3JjTWFya2VyID09PSBBdHRyaWJ1dGVNYXJrZXIuSW1wbGljaXRBdHRyaWJ1dGVzIHx8XG4gICAgICAgICAgICBzcmNNYXJrZXIgPT09IEF0dHJpYnV0ZU1hcmtlci5TdHlsZXMpIHtcbiAgICAgICAgICAvLyBDYXNlIHdoZXJlIHdlIGhhdmUgdG8gY29uc3VtZSBga2V5MWAgYW5kIGB2YWx1ZWAgb25seS5cbiAgICAgICAgICBtZXJnZUhvc3RBdHRyaWJ1dGUoZHN0LCBzcmNNYXJrZXIsIGl0ZW0gYXMgc3RyaW5nLCBudWxsLCBzcmNbKytpXSBhcyBzdHJpbmcpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIC8vIENhc2Ugd2hlcmUgd2UgaGF2ZSB0byBjb25zdW1lIGBrZXkxYCBvbmx5LlxuICAgICAgICAgIG1lcmdlSG9zdEF0dHJpYnV0ZShkc3QsIHNyY01hcmtlciwgaXRlbSBhcyBzdHJpbmcsIG51bGwsIG51bGwpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG4gIHJldHVybiBkc3Q7XG59XG5cbi8qKlxuICogQXBwZW5kIGBrZXlgL2B2YWx1ZWAgdG8gZXhpc3RpbmcgYFRBdHRyaWJ1dGVzYCB0YWtpbmcgcmVnaW9uIG1hcmtlciBhbmQgZHVwbGljYXRlcyBpbnRvIGFjY291bnQuXG4gKlxuICogQHBhcmFtIGRzdCBgVEF0dHJpYnV0ZXNgIHRvIGFwcGVuZCB0by5cbiAqIEBwYXJhbSBtYXJrZXIgUmVnaW9uIHdoZXJlIHRoZSBga2V5YC9gdmFsdWVgIHNob3VsZCBiZSBhZGRlZC5cbiAqIEBwYXJhbSBrZXkxIEtleSB0byBhZGQgdG8gYFRBdHRyaWJ1dGVzYFxuICogQHBhcmFtIGtleTIgS2V5IHRvIGFkZCB0byBgVEF0dHJpYnV0ZXNgIChpbiBjYXNlIG9mIGBBdHRyaWJ1dGVNYXJrZXIuTmFtZXNwYWNlVVJJYClcbiAqIEBwYXJhbSB2YWx1ZSBWYWx1ZSB0byBhZGQgb3IgdG8gb3ZlcndyaXRlIHRvIGBUQXR0cmlidXRlc2AgT25seSB1c2VkIGlmIGBtYXJrZXJgIGlzIG5vdCBDbGFzcy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIG1lcmdlSG9zdEF0dHJpYnV0ZShcbiAgICBkc3Q6IFRBdHRyaWJ1dGVzLCBtYXJrZXI6IEF0dHJpYnV0ZU1hcmtlciwga2V5MTogc3RyaW5nLCBrZXkyOiBzdHJpbmd8bnVsbCxcbiAgICB2YWx1ZTogc3RyaW5nfG51bGwpOiB2b2lkIHtcbiAgbGV0IGkgPSAwO1xuICAvLyBBc3N1bWUgdGhhdCBuZXcgbWFya2VycyB3aWxsIGJlIGluc2VydGVkIGF0IHRoZSBlbmQuXG4gIGxldCBtYXJrZXJJbnNlcnRQb3NpdGlvbiA9IGRzdC5sZW5ndGg7XG4gIC8vIHNjYW4gdW50aWwgY29ycmVjdCB0eXBlLlxuICBpZiAobWFya2VyID09PSBBdHRyaWJ1dGVNYXJrZXIuSW1wbGljaXRBdHRyaWJ1dGVzKSB7XG4gICAgbWFya2VySW5zZXJ0UG9zaXRpb24gPSAtMTtcbiAgfSBlbHNlIHtcbiAgICB3aGlsZSAoaSA8IGRzdC5sZW5ndGgpIHtcbiAgICAgIGNvbnN0IGRzdFZhbHVlID0gZHN0W2krK107XG4gICAgICBpZiAodHlwZW9mIGRzdFZhbHVlID09PSAnbnVtYmVyJykge1xuICAgICAgICBpZiAoZHN0VmFsdWUgPT09IG1hcmtlcikge1xuICAgICAgICAgIG1hcmtlckluc2VydFBvc2l0aW9uID0gLTE7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIH0gZWxzZSBpZiAoZHN0VmFsdWUgPiBtYXJrZXIpIHtcbiAgICAgICAgICAvLyBXZSBuZWVkIHRvIHNhdmUgdGhpcyBhcyB3ZSB3YW50IHRoZSBtYXJrZXJzIHRvIGJlIGluc2VydGVkIGluIHNwZWNpZmljIG9yZGVyLlxuICAgICAgICAgIG1hcmtlckluc2VydFBvc2l0aW9uID0gaSAtIDE7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvLyBzZWFyY2ggdW50aWwgeW91IGZpbmQgcGxhY2Ugb2YgaW5zZXJ0aW9uXG4gIHdoaWxlIChpIDwgZHN0Lmxlbmd0aCkge1xuICAgIGNvbnN0IGl0ZW0gPSBkc3RbaV07XG4gICAgaWYgKHR5cGVvZiBpdGVtID09PSAnbnVtYmVyJykge1xuICAgICAgLy8gc2luY2UgYGlgIHN0YXJ0ZWQgYXMgdGhlIGluZGV4IGFmdGVyIHRoZSBtYXJrZXIsIHdlIGRpZCBub3QgZmluZCBpdCBpZiB3ZSBhcmUgYXQgdGhlIG5leHRcbiAgICAgIC8vIG1hcmtlclxuICAgICAgYnJlYWs7XG4gICAgfSBlbHNlIGlmIChpdGVtID09PSBrZXkxKSB7XG4gICAgICAvLyBXZSBhbHJlYWR5IGhhdmUgc2FtZSB0b2tlblxuICAgICAgaWYgKGtleTIgPT09IG51bGwpIHtcbiAgICAgICAgaWYgKHZhbHVlICE9PSBudWxsKSB7XG4gICAgICAgICAgZHN0W2kgKyAxXSA9IHZhbHVlO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybjtcbiAgICAgIH0gZWxzZSBpZiAoa2V5MiA9PT0gZHN0W2kgKyAxXSkge1xuICAgICAgICBkc3RbaSArIDJdID0gdmFsdWUhO1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG4gICAgfVxuICAgIC8vIEluY3JlbWVudCBjb3VudGVyLlxuICAgIGkrKztcbiAgICBpZiAoa2V5MiAhPT0gbnVsbCkgaSsrO1xuICAgIGlmICh2YWx1ZSAhPT0gbnVsbCkgaSsrO1xuICB9XG5cbiAgLy8gaW5zZXJ0IGF0IGxvY2F0aW9uLlxuICBpZiAobWFya2VySW5zZXJ0UG9zaXRpb24gIT09IC0xKSB7XG4gICAgZHN0LnNwbGljZShtYXJrZXJJbnNlcnRQb3NpdGlvbiwgMCwgbWFya2VyKTtcbiAgICBpID0gbWFya2VySW5zZXJ0UG9zaXRpb24gKyAxO1xuICB9XG4gIGRzdC5zcGxpY2UoaSsrLCAwLCBrZXkxKTtcbiAgaWYgKGtleTIgIT09IG51bGwpIHtcbiAgICBkc3Quc3BsaWNlKGkrKywgMCwga2V5Mik7XG4gIH1cbiAgaWYgKHZhbHVlICE9PSBudWxsKSB7XG4gICAgZHN0LnNwbGljZShpKyssIDAsIHZhbHVlKTtcbiAgfVxufVxuIl19