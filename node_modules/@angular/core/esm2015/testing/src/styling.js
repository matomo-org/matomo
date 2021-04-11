/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Returns element classes in form of a stable (sorted) string.
 *
 * @param element HTML Element.
 * @returns Returns element classes in form of a stable (sorted) string.
 */
export function getSortedClassName(element) {
    const names = Object.keys(getElementClasses(element));
    names.sort();
    return names.join(' ');
}
/**
 * Returns element classes in form of a map.
 *
 * @param element HTML Element.
 * @returns Map of class values.
 */
export function getElementClasses(element) {
    const classes = {};
    if (element.nodeType === Node.ELEMENT_NODE) {
        const classList = element.classList;
        for (let i = 0; i < classList.length; i++) {
            const key = classList[i];
            classes[key] = true;
        }
    }
    return classes;
}
/**
 * Returns element styles in form of a stable (sorted) string.
 *
 * @param element HTML Element.
 * @returns Returns element styles in form of a stable (sorted) string.
 */
export function getSortedStyle(element) {
    const styles = getElementStyles(element);
    const names = Object.keys(styles);
    names.sort();
    let sorted = '';
    names.forEach(key => {
        const value = styles[key];
        if (value != null && value !== '') {
            if (sorted !== '')
                sorted += ' ';
            sorted += key + ': ' + value + ';';
        }
    });
    return sorted;
}
/**
 * Returns element styles in form of a map.
 *
 * @param element HTML Element.
 * @returns Map of style values.
 */
export function getElementStyles(element) {
    const styles = {};
    if (element.nodeType === Node.ELEMENT_NODE) {
        const style = element.style;
        // reading `style.color` is a work around for a bug in Domino. The issue is that Domino has
        // stale value for `style.length`. It seems that reading a property from the element causes the
        // stale value to be updated. (As of Domino v 2.1.3)
        style.color;
        for (let i = 0; i < style.length; i++) {
            const key = style.item(i);
            const value = style.getPropertyValue(key);
            if (value !== '') {
                // Workaround for IE not clearing properties, instead it just sets them to blank value.
                styles[key] = value;
            }
        }
    }
    return styles;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3R5bGluZy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvdGVzdGluZy9zcmMvc3R5bGluZy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSDs7Ozs7R0FLRztBQUNILE1BQU0sVUFBVSxrQkFBa0IsQ0FBQyxPQUFnQjtJQUNqRCxNQUFNLEtBQUssR0FBYSxNQUFNLENBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7SUFDaEUsS0FBSyxDQUFDLElBQUksRUFBRSxDQUFDO0lBQ2IsT0FBTyxLQUFLLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0FBQ3pCLENBQUM7QUFFRDs7Ozs7R0FLRztBQUNILE1BQU0sVUFBVSxpQkFBaUIsQ0FBQyxPQUFnQjtJQUNoRCxNQUFNLE9BQU8sR0FBMEIsRUFBRSxDQUFDO0lBQzFDLElBQUksT0FBTyxDQUFDLFFBQVEsS0FBSyxJQUFJLENBQUMsWUFBWSxFQUFFO1FBQzFDLE1BQU0sU0FBUyxHQUFHLE9BQU8sQ0FBQyxTQUFTLENBQUM7UUFDcEMsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLFNBQVMsQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7WUFDekMsTUFBTSxHQUFHLEdBQUcsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQ3pCLE9BQU8sQ0FBQyxHQUFHLENBQUMsR0FBRyxJQUFJLENBQUM7U0FDckI7S0FDRjtJQUNELE9BQU8sT0FBTyxDQUFDO0FBQ2pCLENBQUM7QUFFRDs7Ozs7R0FLRztBQUNILE1BQU0sVUFBVSxjQUFjLENBQUMsT0FBZ0I7SUFDN0MsTUFBTSxNQUFNLEdBQUcsZ0JBQWdCLENBQUMsT0FBTyxDQUFDLENBQUM7SUFDekMsTUFBTSxLQUFLLEdBQWEsTUFBTSxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztJQUM1QyxLQUFLLENBQUMsSUFBSSxFQUFFLENBQUM7SUFDYixJQUFJLE1BQU0sR0FBRyxFQUFFLENBQUM7SUFDaEIsS0FBSyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBRTtRQUNsQixNQUFNLEtBQUssR0FBRyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7UUFDMUIsSUFBSSxLQUFLLElBQUksSUFBSSxJQUFJLEtBQUssS0FBSyxFQUFFLEVBQUU7WUFDakMsSUFBSSxNQUFNLEtBQUssRUFBRTtnQkFBRSxNQUFNLElBQUksR0FBRyxDQUFDO1lBQ2pDLE1BQU0sSUFBSSxHQUFHLEdBQUcsSUFBSSxHQUFHLEtBQUssR0FBRyxHQUFHLENBQUM7U0FDcEM7SUFDSCxDQUFDLENBQUMsQ0FBQztJQUNILE9BQU8sTUFBTSxDQUFDO0FBQ2hCLENBQUM7QUFFRDs7Ozs7R0FLRztBQUNILE1BQU0sVUFBVSxnQkFBZ0IsQ0FBQyxPQUFnQjtJQUMvQyxNQUFNLE1BQU0sR0FBNEIsRUFBRSxDQUFDO0lBQzNDLElBQUksT0FBTyxDQUFDLFFBQVEsS0FBSyxJQUFJLENBQUMsWUFBWSxFQUFFO1FBQzFDLE1BQU0sS0FBSyxHQUFJLE9BQXVCLENBQUMsS0FBSyxDQUFDO1FBQzdDLDJGQUEyRjtRQUMzRiwrRkFBK0Y7UUFDL0Ysb0RBQW9EO1FBQ3BELEtBQUssQ0FBQyxLQUFLLENBQUM7UUFDWixLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsS0FBSyxDQUFDLE1BQU0sRUFBRSxDQUFDLEVBQUUsRUFBRTtZQUNyQyxNQUFNLEdBQUcsR0FBRyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQzFCLE1BQU0sS0FBSyxHQUFHLEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQyxHQUFHLENBQUMsQ0FBQztZQUMxQyxJQUFJLEtBQUssS0FBSyxFQUFFLEVBQUU7Z0JBQ2hCLHVGQUF1RjtnQkFDdkYsTUFBTSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEtBQUssQ0FBQzthQUNyQjtTQUNGO0tBQ0Y7SUFDRCxPQUFPLE1BQU0sQ0FBQztBQUNoQixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogUmV0dXJucyBlbGVtZW50IGNsYXNzZXMgaW4gZm9ybSBvZiBhIHN0YWJsZSAoc29ydGVkKSBzdHJpbmcuXG4gKlxuICogQHBhcmFtIGVsZW1lbnQgSFRNTCBFbGVtZW50LlxuICogQHJldHVybnMgUmV0dXJucyBlbGVtZW50IGNsYXNzZXMgaW4gZm9ybSBvZiBhIHN0YWJsZSAoc29ydGVkKSBzdHJpbmcuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRTb3J0ZWRDbGFzc05hbWUoZWxlbWVudDogRWxlbWVudCk6IHN0cmluZyB7XG4gIGNvbnN0IG5hbWVzOiBzdHJpbmdbXSA9IE9iamVjdC5rZXlzKGdldEVsZW1lbnRDbGFzc2VzKGVsZW1lbnQpKTtcbiAgbmFtZXMuc29ydCgpO1xuICByZXR1cm4gbmFtZXMuam9pbignICcpO1xufVxuXG4vKipcbiAqIFJldHVybnMgZWxlbWVudCBjbGFzc2VzIGluIGZvcm0gb2YgYSBtYXAuXG4gKlxuICogQHBhcmFtIGVsZW1lbnQgSFRNTCBFbGVtZW50LlxuICogQHJldHVybnMgTWFwIG9mIGNsYXNzIHZhbHVlcy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldEVsZW1lbnRDbGFzc2VzKGVsZW1lbnQ6IEVsZW1lbnQpOiB7W2tleTogc3RyaW5nXTogdHJ1ZX0ge1xuICBjb25zdCBjbGFzc2VzOiB7W2tleTogc3RyaW5nXTogdHJ1ZX0gPSB7fTtcbiAgaWYgKGVsZW1lbnQubm9kZVR5cGUgPT09IE5vZGUuRUxFTUVOVF9OT0RFKSB7XG4gICAgY29uc3QgY2xhc3NMaXN0ID0gZWxlbWVudC5jbGFzc0xpc3Q7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBjbGFzc0xpc3QubGVuZ3RoOyBpKyspIHtcbiAgICAgIGNvbnN0IGtleSA9IGNsYXNzTGlzdFtpXTtcbiAgICAgIGNsYXNzZXNba2V5XSA9IHRydWU7XG4gICAgfVxuICB9XG4gIHJldHVybiBjbGFzc2VzO1xufVxuXG4vKipcbiAqIFJldHVybnMgZWxlbWVudCBzdHlsZXMgaW4gZm9ybSBvZiBhIHN0YWJsZSAoc29ydGVkKSBzdHJpbmcuXG4gKlxuICogQHBhcmFtIGVsZW1lbnQgSFRNTCBFbGVtZW50LlxuICogQHJldHVybnMgUmV0dXJucyBlbGVtZW50IHN0eWxlcyBpbiBmb3JtIG9mIGEgc3RhYmxlIChzb3J0ZWQpIHN0cmluZy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldFNvcnRlZFN0eWxlKGVsZW1lbnQ6IEVsZW1lbnQpOiBzdHJpbmcge1xuICBjb25zdCBzdHlsZXMgPSBnZXRFbGVtZW50U3R5bGVzKGVsZW1lbnQpO1xuICBjb25zdCBuYW1lczogc3RyaW5nW10gPSBPYmplY3Qua2V5cyhzdHlsZXMpO1xuICBuYW1lcy5zb3J0KCk7XG4gIGxldCBzb3J0ZWQgPSAnJztcbiAgbmFtZXMuZm9yRWFjaChrZXkgPT4ge1xuICAgIGNvbnN0IHZhbHVlID0gc3R5bGVzW2tleV07XG4gICAgaWYgKHZhbHVlICE9IG51bGwgJiYgdmFsdWUgIT09ICcnKSB7XG4gICAgICBpZiAoc29ydGVkICE9PSAnJykgc29ydGVkICs9ICcgJztcbiAgICAgIHNvcnRlZCArPSBrZXkgKyAnOiAnICsgdmFsdWUgKyAnOyc7XG4gICAgfVxuICB9KTtcbiAgcmV0dXJuIHNvcnRlZDtcbn1cblxuLyoqXG4gKiBSZXR1cm5zIGVsZW1lbnQgc3R5bGVzIGluIGZvcm0gb2YgYSBtYXAuXG4gKlxuICogQHBhcmFtIGVsZW1lbnQgSFRNTCBFbGVtZW50LlxuICogQHJldHVybnMgTWFwIG9mIHN0eWxlIHZhbHVlcy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldEVsZW1lbnRTdHlsZXMoZWxlbWVudDogRWxlbWVudCk6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9IHtcbiAgY29uc3Qgc3R5bGVzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSA9IHt9O1xuICBpZiAoZWxlbWVudC5ub2RlVHlwZSA9PT0gTm9kZS5FTEVNRU5UX05PREUpIHtcbiAgICBjb25zdCBzdHlsZSA9IChlbGVtZW50IGFzIEhUTUxFbGVtZW50KS5zdHlsZTtcbiAgICAvLyByZWFkaW5nIGBzdHlsZS5jb2xvcmAgaXMgYSB3b3JrIGFyb3VuZCBmb3IgYSBidWcgaW4gRG9taW5vLiBUaGUgaXNzdWUgaXMgdGhhdCBEb21pbm8gaGFzXG4gICAgLy8gc3RhbGUgdmFsdWUgZm9yIGBzdHlsZS5sZW5ndGhgLiBJdCBzZWVtcyB0aGF0IHJlYWRpbmcgYSBwcm9wZXJ0eSBmcm9tIHRoZSBlbGVtZW50IGNhdXNlcyB0aGVcbiAgICAvLyBzdGFsZSB2YWx1ZSB0byBiZSB1cGRhdGVkLiAoQXMgb2YgRG9taW5vIHYgMi4xLjMpXG4gICAgc3R5bGUuY29sb3I7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBzdHlsZS5sZW5ndGg7IGkrKykge1xuICAgICAgY29uc3Qga2V5ID0gc3R5bGUuaXRlbShpKTtcbiAgICAgIGNvbnN0IHZhbHVlID0gc3R5bGUuZ2V0UHJvcGVydHlWYWx1ZShrZXkpO1xuICAgICAgaWYgKHZhbHVlICE9PSAnJykge1xuICAgICAgICAvLyBXb3JrYXJvdW5kIGZvciBJRSBub3QgY2xlYXJpbmcgcHJvcGVydGllcywgaW5zdGVhZCBpdCBqdXN0IHNldHMgdGhlbSB0byBibGFuayB2YWx1ZS5cbiAgICAgICAgc3R5bGVzW2tleV0gPSB2YWx1ZTtcbiAgICAgIH1cbiAgICB9XG4gIH1cbiAgcmV0dXJuIHN0eWxlcztcbn1cbiJdfQ==