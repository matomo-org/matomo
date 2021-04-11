/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertEqual, assertLessThanOrEqual } from './assert';
/**
 * Equivalent to ES6 spread, add each item to an array.
 *
 * @param items The items to add
 * @param arr The array to which you want to add the items
 */
export function addAllToArray(items, arr) {
    for (let i = 0; i < items.length; i++) {
        arr.push(items[i]);
    }
}
/**
 * Determines if the contents of two arrays is identical
 *
 * @param a first array
 * @param b second array
 * @param identityAccessor Optional function for extracting stable object identity from a value in
 *     the array.
 */
export function arrayEquals(a, b, identityAccessor) {
    if (a.length !== b.length)
        return false;
    for (let i = 0; i < a.length; i++) {
        let valueA = a[i];
        let valueB = b[i];
        if (identityAccessor) {
            valueA = identityAccessor(valueA);
            valueB = identityAccessor(valueB);
        }
        if (valueB !== valueA) {
            return false;
        }
    }
    return true;
}
/**
 * Flattens an array.
 */
export function flatten(list, dst) {
    if (dst === undefined)
        dst = list;
    for (let i = 0; i < list.length; i++) {
        let item = list[i];
        if (Array.isArray(item)) {
            // we need to inline it.
            if (dst === list) {
                // Our assumption that the list was already flat was wrong and
                // we need to clone flat since we need to write to it.
                dst = list.slice(0, i);
            }
            flatten(item, dst);
        }
        else if (dst !== list) {
            dst.push(item);
        }
    }
    return dst;
}
export function deepForEach(input, fn) {
    input.forEach(value => Array.isArray(value) ? deepForEach(value, fn) : fn(value));
}
export function addToArray(arr, index, value) {
    // perf: array.push is faster than array.splice!
    if (index >= arr.length) {
        arr.push(value);
    }
    else {
        arr.splice(index, 0, value);
    }
}
export function removeFromArray(arr, index) {
    // perf: array.pop is faster than array.splice!
    if (index >= arr.length - 1) {
        return arr.pop();
    }
    else {
        return arr.splice(index, 1)[0];
    }
}
export function newArray(size, value) {
    const list = [];
    for (let i = 0; i < size; i++) {
        list.push(value);
    }
    return list;
}
/**
 * Remove item from array (Same as `Array.splice()` but faster.)
 *
 * `Array.splice()` is not as fast because it has to allocate an array for the elements which were
 * removed. This causes memory pressure and slows down code when most of the time we don't
 * care about the deleted items array.
 *
 * https://jsperf.com/fast-array-splice (About 20x faster)
 *
 * @param array Array to splice
 * @param index Index of element in array to remove.
 * @param count Number of items to remove.
 */
export function arraySplice(array, index, count) {
    const length = array.length - count;
    while (index < length) {
        array[index] = array[index + count];
        index++;
    }
    while (count--) {
        array.pop(); // shrink the array
    }
}
/**
 * Same as `Array.splice(index, 0, value)` but faster.
 *
 * `Array.splice()` is not fast because it has to allocate an array for the elements which were
 * removed. This causes memory pressure and slows down code when most of the time we don't
 * care about the deleted items array.
 *
 * @param array Array to splice.
 * @param index Index in array where the `value` should be added.
 * @param value Value to add to array.
 */
export function arrayInsert(array, index, value) {
    ngDevMode && assertLessThanOrEqual(index, array.length, 'Can\'t insert past array end.');
    let end = array.length;
    while (end > index) {
        const previousEnd = end - 1;
        array[end] = array[previousEnd];
        end = previousEnd;
    }
    array[index] = value;
}
/**
 * Same as `Array.splice2(index, 0, value1, value2)` but faster.
 *
 * `Array.splice()` is not fast because it has to allocate an array for the elements which were
 * removed. This causes memory pressure and slows down code when most of the time we don't
 * care about the deleted items array.
 *
 * @param array Array to splice.
 * @param index Index in array where the `value` should be added.
 * @param value1 Value to add to array.
 * @param value2 Value to add to array.
 */
export function arrayInsert2(array, index, value1, value2) {
    ngDevMode && assertLessThanOrEqual(index, array.length, 'Can\'t insert past array end.');
    let end = array.length;
    if (end == index) {
        // inserting at the end.
        array.push(value1, value2);
    }
    else if (end === 1) {
        // corner case when we have less items in array than we have items to insert.
        array.push(value2, array[0]);
        array[0] = value1;
    }
    else {
        end--;
        array.push(array[end - 1], array[end]);
        while (end > index) {
            const previousEnd = end - 2;
            array[end] = array[previousEnd];
            end--;
        }
        array[index] = value1;
        array[index + 1] = value2;
    }
}
/**
 * Insert a `value` into an `array` so that the array remains sorted.
 *
 * NOTE:
 * - Duplicates are not allowed, and are ignored.
 * - This uses binary search algorithm for fast inserts.
 *
 * @param array A sorted array to insert into.
 * @param value The value to insert.
 * @returns index of the inserted value.
 */
export function arrayInsertSorted(array, value) {
    let index = arrayIndexOfSorted(array, value);
    if (index < 0) {
        // if we did not find it insert it.
        index = ~index;
        arrayInsert(array, index, value);
    }
    return index;
}
/**
 * Remove `value` from a sorted `array`.
 *
 * NOTE:
 * - This uses binary search algorithm for fast removals.
 *
 * @param array A sorted array to remove from.
 * @param value The value to remove.
 * @returns index of the removed value.
 *   - positive index if value found and removed.
 *   - negative index if value not found. (`~index` to get the value where it should have been
 *     inserted)
 */
export function arrayRemoveSorted(array, value) {
    const index = arrayIndexOfSorted(array, value);
    if (index >= 0) {
        arraySplice(array, index, 1);
    }
    return index;
}
/**
 * Get an index of an `value` in a sorted `array`.
 *
 * NOTE:
 * - This uses binary search algorithm for fast removals.
 *
 * @param array A sorted array to binary search.
 * @param value The value to look for.
 * @returns index of the value.
 *   - positive index if value found.
 *   - negative index if value not found. (`~index` to get the value where it should have been
 *     located)
 */
export function arrayIndexOfSorted(array, value) {
    return _arrayIndexOfSorted(array, value, 0);
}
/**
 * Set a `value` for a `key`.
 *
 * @param keyValueArray to modify.
 * @param key The key to locate or create.
 * @param value The value to set for a `key`.
 * @returns index (always even) of where the value vas set.
 */
export function keyValueArraySet(keyValueArray, key, value) {
    let index = keyValueArrayIndexOf(keyValueArray, key);
    if (index >= 0) {
        // if we found it set it.
        keyValueArray[index | 1] = value;
    }
    else {
        index = ~index;
        arrayInsert2(keyValueArray, index, key, value);
    }
    return index;
}
/**
 * Retrieve a `value` for a `key` (on `undefined` if not found.)
 *
 * @param keyValueArray to search.
 * @param key The key to locate.
 * @return The `value` stored at the `key` location or `undefined if not found.
 */
export function keyValueArrayGet(keyValueArray, key) {
    const index = keyValueArrayIndexOf(keyValueArray, key);
    if (index >= 0) {
        // if we found it retrieve it.
        return keyValueArray[index | 1];
    }
    return undefined;
}
/**
 * Retrieve a `key` index value in the array or `-1` if not found.
 *
 * @param keyValueArray to search.
 * @param key The key to locate.
 * @returns index of where the key is (or should have been.)
 *   - positive (even) index if key found.
 *   - negative index if key not found. (`~index` (even) to get the index where it should have
 *     been inserted.)
 */
export function keyValueArrayIndexOf(keyValueArray, key) {
    return _arrayIndexOfSorted(keyValueArray, key, 1);
}
/**
 * Delete a `key` (and `value`) from the `KeyValueArray`.
 *
 * @param keyValueArray to modify.
 * @param key The key to locate or delete (if exist).
 * @returns index of where the key was (or should have been.)
 *   - positive (even) index if key found and deleted.
 *   - negative index if key not found. (`~index` (even) to get the index where it should have
 *     been.)
 */
export function keyValueArrayDelete(keyValueArray, key) {
    const index = keyValueArrayIndexOf(keyValueArray, key);
    if (index >= 0) {
        // if we found it remove it.
        arraySplice(keyValueArray, index, 2);
    }
    return index;
}
/**
 * INTERNAL: Get an index of an `value` in a sorted `array` by grouping search by `shift`.
 *
 * NOTE:
 * - This uses binary search algorithm for fast removals.
 *
 * @param array A sorted array to binary search.
 * @param value The value to look for.
 * @param shift grouping shift.
 *   - `0` means look at every location
 *   - `1` means only look at every other (even) location (the odd locations are to be ignored as
 *         they are values.)
 * @returns index of the value.
 *   - positive index if value found.
 *   - negative index if value not found. (`~index` to get the value where it should have been
 * inserted)
 */
function _arrayIndexOfSorted(array, value, shift) {
    ngDevMode && assertEqual(Array.isArray(array), true, 'Expecting an array');
    let start = 0;
    let end = array.length >> shift;
    while (end !== start) {
        const middle = start + ((end - start) >> 1); // find the middle.
        const current = array[middle << shift];
        if (value === current) {
            return (middle << shift);
        }
        else if (current > value) {
            end = middle;
        }
        else {
            start = middle + 1; // We already searched middle so make it non-inclusive by adding 1
        }
    }
    return ~(end << shift);
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXJyYXlfdXRpbHMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy91dGlsL2FycmF5X3V0aWxzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBQyxXQUFXLEVBQUUscUJBQXFCLEVBQUMsTUFBTSxVQUFVLENBQUM7QUFFNUQ7Ozs7O0dBS0c7QUFDSCxNQUFNLFVBQVUsYUFBYSxDQUFDLEtBQVksRUFBRSxHQUFVO0lBQ3BELEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxLQUFLLENBQUMsTUFBTSxFQUFFLENBQUMsRUFBRSxFQUFFO1FBQ3JDLEdBQUcsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7S0FDcEI7QUFDSCxDQUFDO0FBRUQ7Ozs7Ozs7R0FPRztBQUNILE1BQU0sVUFBVSxXQUFXLENBQUksQ0FBTSxFQUFFLENBQU0sRUFBRSxnQkFBd0M7SUFDckYsSUFBSSxDQUFDLENBQUMsTUFBTSxLQUFLLENBQUMsQ0FBQyxNQUFNO1FBQUUsT0FBTyxLQUFLLENBQUM7SUFDeEMsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7UUFDakMsSUFBSSxNQUFNLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ2xCLElBQUksTUFBTSxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUNsQixJQUFJLGdCQUFnQixFQUFFO1lBQ3BCLE1BQU0sR0FBRyxnQkFBZ0IsQ0FBQyxNQUFNLENBQVEsQ0FBQztZQUN6QyxNQUFNLEdBQUcsZ0JBQWdCLENBQUMsTUFBTSxDQUFRLENBQUM7U0FDMUM7UUFDRCxJQUFJLE1BQU0sS0FBSyxNQUFNLEVBQUU7WUFDckIsT0FBTyxLQUFLLENBQUM7U0FD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Ii8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge2Fzc2VydEVxdWFsLCBhc3NlcnRMZXNzVGhhbk9yRXF1YWx9IGZyb20gJy4vYXNzZXJ0JztcblxuLyoqXG4gKiBFcXVpdmFsZW50IHRvIEVTNiBzcHJlYWQsIGFkZCBlYWNoIGl0ZW0gdG8gYW4gYXJyYXkuXG4gKlxuICogQHBhcmFtIGl0ZW1zIFRoZSBpdGVtcyB0byBhZGRcbiAqIEBwYXJhbSBhcnIgVGhlIGFycmF5IHRvIHdoaWNoIHlvdSB3YW50IHRvIGFkZCB0aGUgaXRlbXNcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGFkZEFsbFRvQXJyYXkoaXRlbXM6IGFueVtdLCBhcnI6IGFueVtdKSB7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgaXRlbXMubGVuZ3RoOyBpKyspIHtcbiAgICBhcnIucHVzaChpdGVtc1tpXSk7XG4gIH1cbn1cblxuLyoqXG4gKiBEZXRlcm1pbmVzIGlmIHRoZSBjb250ZW50cyBvZiB0d28gYXJyYXlzIGlzIGlkZW50aWNhbFxuICpcbiAqIEBwYXJhbSBhIGZpcnN0IGFycmF5XG4gKiBAcGFyYW0gYiBzZWNvbmQgYXJyYXlcbiAqIEBwYXJhbSBpZGVudGl0eUFjY2Vzc29yIE9wdGlvbmFsIGZ1bmN0aW9uIGZvciBleHRyYWN0aW5nIHN0YWJsZSBvYmplY3QgaWRlbnRpdHkgZnJvbSBhIHZhbHVlIGluXG4gKiAgICAgdGhlIGFycmF5LlxuICovXG5leHBvcnQgZnVuY3Rpb24gYXJyYXlFcXVhbHM8VD4oYTogVFtdLCBiOiBUW10sIGlkZW50aXR5QWNjZXNzb3I/OiAodmFsdWU6IFQpID0+IHVua25vd24pOiBib29sZWFuIHtcbiAgaWYgKGEubGVuZ3RoICE9PSBiLmxlbmd0aCkgcmV0dXJuIGZhbHNlO1xuICBmb3IgKGxldCBpID0gMDsgaSA8IGEubGVuZ3RoOyBpKyspIHtcbiAgICBsZXQgdmFsdWVBID0gYVtpXTtcbiAgICBsZXQgdmFsdWVCID0gYltpXTtcbiAgICBpZiAoaWRlbnRpdHlBY2Nlc3Nvcikge1xuICAgICAgdmFsdWVBID0gaWRlbnRpdHlBY2Nlc3Nvcih2YWx1ZUEpIGFzIGFueTtcbiAgICAgIHZhbHVlQiA9IGlkZW50aXR5QWNjZXNzb3IodmFsdWVCKSBhcyBhbnk7XG4gICAgfVxuICAgIGlmICh2YWx1ZUIgIT09IHZhbHVlQSkge1xuICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cbiAgfVxuICByZXR1cm4gdHJ1ZTtcbn1cblxuXG4vKipcbiAqIEZsYXR0ZW5zIGFuIGFycmF5LlxuICovXG5leHBvcnQgZnVuY3Rpb24gZmxhdHRlbihsaXN0OiBhbnlbXSwgZHN0PzogYW55W10pOiBhbnlbXSB7XG4gIGlmIChkc3QgPT09IHVuZGVmaW5lZCkgZHN0ID0gbGlzdDtcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBsaXN0Lmxlbmd0aDsgaSsrKSB7XG4gICAgbGV0IGl0ZW0gPSBsaXN0W2ldO1xuICAgIGlmIChBcnJheS5pc0FycmF5KGl0ZW0pKSB7XG4gICAgICAvLyB3ZSBuZWVkIHRvIGlubGluZSBpdC5cbiAgICAgIGlmIChkc3QgPT09IGxpc3QpIHtcbiAgICAgICAgLy8gT3VyIGFzc3VtcHRpb24gdGhhdCB0aGUgbGlzdCB3YXMgYWxyZWFkeSBmbGF0IHdhcyB3cm9uZyBhbmRcbiAgICAgICAgLy8gd2UgbmVlZCB0byBjbG9uZSBmbGF0IHNpbmNlIHdlIG5lZWQgdG8gd3JpdGUgdG8gaXQuXG4gICAgICAgIGRzdCA9IGxpc3Quc2xpY2UoMCwgaSk7XG4gICAgICB9XG4gICAgICBmbGF0dGVuKGl0ZW0sIGRzdCk7XG4gICAgfSBlbHNlIGlmIChkc3QgIT09IGxpc3QpIHtcbiAgICAgIGRzdC5wdXNoKGl0ZW0pO1xuICAgIH1cbiAgfVxuICByZXR1cm4gZHN0O1xufVxuXG5leHBvcnQgZnVuY3Rpb24gZGVlcEZvckVhY2g8VD4oaW5wdXQ6IChUfGFueVtdKVtdLCBmbjogKHZhbHVlOiBUKSA9PiB2b2lkKTogdm9pZCB7XG4gIGlucHV0LmZvckVhY2godmFsdWUgPT4gQXJyYXkuaXNBcnJheSh2YWx1ZSkgPyBkZWVwRm9yRWFjaCh2YWx1ZSwgZm4pIDogZm4odmFsdWUpKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGFkZFRvQXJyYXkoYXJyOiBhbnlbXSwgaW5kZXg6IG51bWJlciwgdmFsdWU6IGFueSk6IHZvaWQge1xuICAvLyBwZXJmOiBhcnJheS5wdXNoIGlzIGZhc3RlciB0aGFuIGFycmF5LnNwbGljZSFcbiAgaWYgKGluZGV4ID49IGFyci5sZW5ndGgpIHtcbiAgICBhcnIucHVzaCh2YWx1ZSk7XG4gIH0gZWxzZSB7XG4gICAgYXJyLnNwbGljZShpbmRleCwgMCwgdmFsdWUpO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiByZW1vdmVGcm9tQXJyYXkoYXJyOiBhbnlbXSwgaW5kZXg6IG51bWJlcik6IGFueSB7XG4gIC8vIHBlcmY6IGFycmF5LnBvcCBpcyBmYXN0ZXIgdGhhbiBhcnJheS5zcGxpY2UhXG4gIGlmIChpbmRleCA+PSBhcnIubGVuZ3RoIC0gMSkge1xuICAgIHJldHVybiBhcnIucG9wKCk7XG4gIH0gZWxzZSB7XG4gICAgcmV0dXJuIGFyci5zcGxpY2UoaW5kZXgsIDEpWzBdO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBuZXdBcnJheTxUID0gYW55PihzaXplOiBudW1iZXIpOiBUW107XG5leHBvcnQgZnVuY3Rpb24gbmV3QXJyYXk8VD4oc2l6ZTogbnVtYmVyLCB2YWx1ZTogVCk6IFRbXTtcbmV4cG9ydCBmdW5jdGlvbiBuZXdBcnJheTxUPihzaXplOiBudW1iZXIsIHZhbHVlPzogVCk6IFRbXSB7XG4gIGNvbnN0IGxpc3Q6IFRbXSA9IFtdO1xuICBmb3IgKGxldCBpID0gMDsgaSA8IHNpemU7IGkrKykge1xuICAgIGxpc3QucHVzaCh2YWx1ZSEpO1xuICB9XG4gIHJldHVybiBsaXN0O1xufVxuXG4vKipcbiAqIFJlbW92ZSBpdGVtIGZyb20gYXJyYXkgKFNhbWUgYXMgYEFycmF5LnNwbGljZSgpYCBidXQgZmFzdGVyLilcbiAqXG4gKiBgQXJyYXkuc3BsaWNlKClgIGlzIG5vdCBhcyBmYXN0IGJlY2F1c2UgaXQgaGFzIHRvIGFsbG9jYXRlIGFuIGFycmF5IGZvciB0aGUgZWxlbWVudHMgd2hpY2ggd2VyZVxuICogcmVtb3ZlZC4gVGhpcyBjYXVzZXMgbWVtb3J5IHByZXNzdXJlIGFuZCBzbG93cyBkb3duIGNvZGUgd2hlbiBtb3N0IG9mIHRoZSB0aW1lIHdlIGRvbid0XG4gKiBjYXJlIGFib3V0IHRoZSBkZWxldGVkIGl0ZW1zIGFycmF5LlxuICpcbiAqIGh0dHBzOi8vanNwZXJmLmNvbS9mYXN0LWFycmF5LXNwbGljZSAoQWJvdXQgMjB4IGZhc3RlcilcbiAqXG4gKiBAcGFyYW0gYXJyYXkgQXJyYXkgdG8gc3BsaWNlXG4gKiBAcGFyYW0gaW5kZXggSW5kZXggb2YgZWxlbWVudCBpbiBhcnJheSB0byByZW1vdmUuXG4gKiBAcGFyYW0gY291bnQgTnVtYmVyIG9mIGl0ZW1zIHRvIHJlbW92ZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGFycmF5U3BsaWNlKGFycmF5OiBhbnlbXSwgaW5kZXg6IG51bWJlciwgY291bnQ6IG51bWJlcik6IHZvaWQge1xuICBjb25zdCBsZW5ndGggPSBhcnJheS5sZW5ndGggLSBjb3VudDtcbiAgd2hpbGUgKGluZGV4IDwgbGVuZ3RoKSB7XG4gICAgYXJyYXlbaW5kZXhdID0gYXJyYXlbaW5kZXggKyBjb3VudF07XG4gICAgaW5kZXgrKztcbiAgfVxuICB3aGlsZSAoY291bnQtLSkge1xuICAgIGFycmF5LnBvcCgpOyAgLy8gc2hyaW5rIHRoZSBhcnJheVxuICB9XG59XG5cbi8qKlxuICogU2FtZSBhcyBgQXJyYXkuc3BsaWNlKGluZGV4LCAwLCB2YWx1ZSlgIGJ1dCBmYXN0ZXIuXG4gKlxuICogYEFycmF5LnNwbGljZSgpYCBpcyBub3QgZmFzdCBiZWNhdXNlIGl0IGhhcyB0byBhbGxvY2F0ZSBhbiBhcnJheSBmb3IgdGhlIGVsZW1lbnRzIHdoaWNoIHdlcmVcbiAqIHJlbW92ZWQuIFRoaXMgY2F1c2VzIG1lbW9yeSBwcmVzc3VyZSBhbmQgc2xvd3MgZG93biBjb2RlIHdoZW4gbW9zdCBvZiB0aGUgdGltZSB3ZSBkb24ndFxuICogY2FyZSBhYm91dCB0aGUgZGVsZXRlZCBpdGVtcyBhcnJheS5cbiAqXG4gKiBAcGFyYW0gYXJyYXkgQXJyYXkgdG8gc3BsaWNlLlxuICogQHBhcmFtIGluZGV4IEluZGV4IGluIGFycmF5IHdoZXJlIHRoZSBgdmFsdWVgIHNob3VsZCBiZSBhZGRlZC5cbiAqIEBwYXJhbSB2YWx1ZSBWYWx1ZSB0byBhZGQgdG8gYXJyYXkuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBhcnJheUluc2VydChhcnJheTogYW55W10sIGluZGV4OiBudW1iZXIsIHZhbHVlOiBhbnkpOiB2b2lkIHtcbiAgbmdEZXZNb2RlICYmIGFzc2VydExlc3NUaGFuT3JFcXVhbChpbmRleCwgYXJyYXkubGVuZ3RoLCAnQ2FuXFwndCBpbnNlcnQgcGFzdCBhcnJheSBlbmQuJyk7XG4gIGxldCBlbmQgPSBhcnJheS5sZW5ndGg7XG4gIHdoaWxlIChlbmQgPiBpbmRleCkge1xuICAgIGNvbnN0IHByZXZpb3VzRW5kID0gZW5kIC0gMTtcbiAgICBhcnJheVtlbmRdID0gYXJyYXlbcHJldmlvdXNFbmRdO1xuICAgIGVuZCA9IHByZXZpb3VzRW5kO1xuICB9XG4gIGFycmF5W2luZGV4XSA9IHZhbHVlO1xufVxuXG4vKipcbiAqIFNhbWUgYXMgYEFycmF5LnNwbGljZTIoaW5kZXgsIDAsIHZhbHVlMSwgdmFsdWUyKWAgYnV0IGZhc3Rlci5cbiAqXG4gKiBgQXJyYXkuc3BsaWNlKClgIGlzIG5vdCBmYXN0IGJlY2F1c2UgaXQgaGFzIHRvIGFsbG9jYXRlIGFuIGFycmF5IGZvciB0aGUgZWxlbWVudHMgd2hpY2ggd2VyZVxuICogcmVtb3ZlZC4gVGhpcyBjYXVzZXMgbWVtb3J5IHByZXNzdXJlIGFuZCBzbG93cyBkb3duIGNvZGUgd2hlbiBtb3N0IG9mIHRoZSB0aW1lIHdlIGRvbid0XG4gKiBjYXJlIGFib3V0IHRoZSBkZWxldGVkIGl0ZW1zIGFycmF5LlxuICpcbiAqIEBwYXJhbSBhcnJheSBBcnJheSB0byBzcGxpY2UuXG4gKiBAcGFyYW0gaW5kZXggSW5kZXggaW4gYXJyYXkgd2hlcmUgdGhlIGB2YWx1ZWAgc2hvdWxkIGJlIGFkZGVkLlxuICogQHBhcmFtIHZhbHVlMSBWYWx1ZSB0byBhZGQgdG8gYXJyYXkuXG4gKiBAcGFyYW0gdmFsdWUyIFZhbHVlIHRvIGFkZCB0byBhcnJheS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGFycmF5SW5zZXJ0MihhcnJheTogYW55W10sIGluZGV4OiBudW1iZXIsIHZhbHVlMTogYW55LCB2YWx1ZTI6IGFueSk6IHZvaWQge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0TGVzc1RoYW5PckVxdWFsKGluZGV4LCBhcnJheS5sZW5ndGgsICdDYW5cXCd0IGluc2VydCBwYXN0IGFycmF5IGVuZC4nKTtcbiAgbGV0IGVuZCA9IGFycmF5Lmxlbmd0aDtcbiAgaWYgKGVuZCA9PSBpbmRleCkge1xuICAgIC8vIGluc2VydGluZyBhdCB0aGUgZW5kLlxuICAgIGFycmF5LnB1c2godmFsdWUxLCB2YWx1ZTIpO1xuICB9IGVsc2UgaWYgKGVuZCA9PT0gMSkge1xuICAgIC8vIGNvcm5lciBjYXNlIHdoZW4gd2UgaGF2ZSBsZXNzIGl0ZW1zIGluIGFycmF5IHRoYW4gd2UgaGF2ZSBpdGVtcyB0byBpbnNlcnQuXG4gICAgYXJyYXkucHVzaCh2YWx1ZTIsIGFycmF5WzBdKTtcbiAgICBhcnJheVswXSA9IHZhbHVlMTtcbiAgfSBlbHNlIHtcbiAgICBlbmQtLTtcbiAgICBhcnJheS5wdXNoKGFycmF5W2VuZCAtIDFdLCBhcnJheVtlbmRdKTtcbiAgICB3aGlsZSAoZW5kID4gaW5kZXgpIHtcbiAgICAgIGNvbnN0IHByZXZpb3VzRW5kID0gZW5kIC0gMjtcbiAgICAgIGFycmF5W2VuZF0gPSBhcnJheVtwcmV2aW91c0VuZF07XG4gICAgICBlbmQtLTtcbiAgICB9XG4gICAgYXJyYXlbaW5kZXhdID0gdmFsdWUxO1xuICAgIGFycmF5W2luZGV4ICsgMV0gPSB2YWx1ZTI7XG4gIH1cbn1cblxuLyoqXG4gKiBJbnNlcnQgYSBgdmFsdWVgIGludG8gYW4gYGFycmF5YCBzbyB0aGF0IHRoZSBhcnJheSByZW1haW5zIHNvcnRlZC5cbiAqXG4gKiBOT1RFOlxuICogLSBEdXBsaWNhdGVzIGFyZSBub3QgYWxsb3dlZCwgYW5kIGFyZSBpZ25vcmVkLlxuICogLSBUaGlzIHVzZXMgYmluYXJ5IHNlYXJjaCBhbGdvcml0aG0gZm9yIGZhc3QgaW5zZXJ0cy5cbiAqXG4gKiBAcGFyYW0gYXJyYXkgQSBzb3J0ZWQgYXJyYXkgdG8gaW5zZXJ0IGludG8uXG4gKiBAcGFyYW0gdmFsdWUgVGhlIHZhbHVlIHRvIGluc2VydC5cbiAqIEByZXR1cm5zIGluZGV4IG9mIHRoZSBpbnNlcnRlZCB2YWx1ZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGFycmF5SW5zZXJ0U29ydGVkKGFycmF5OiBzdHJpbmdbXSwgdmFsdWU6IHN0cmluZyk6IG51bWJlciB7XG4gIGxldCBpbmRleCA9IGFycmF5SW5kZXhPZlNvcnRlZChhcnJheSwgdmFsdWUpO1xuICBpZiAoaW5kZXggPCAwKSB7XG4gICAgLy8gaWYgd2UgZGlkIG5vdCBmaW5kIGl0IGluc2VydCBpdC5cbiAgICBpbmRleCA9IH5pbmRleDtcbiAgICBhcnJheUluc2VydChhcnJheSwgaW5kZXgsIHZhbHVlKTtcbiAgfVxuICByZXR1cm4gaW5kZXg7XG59XG5cbi8qKlxuICogUmVtb3ZlIGB2YWx1ZWAgZnJvbSBhIHNvcnRlZCBgYXJyYXlgLlxuICpcbiAqIE5PVEU6XG4gKiAtIFRoaXMgdXNlcyBiaW5hcnkgc2VhcmNoIGFsZ29yaXRobSBmb3IgZmFzdCByZW1vdmFscy5cbiAqXG4gKiBAcGFyYW0gYXJyYXkgQSBzb3J0ZWQgYXJyYXkgdG8gcmVtb3ZlIGZyb20uXG4gKiBAcGFyYW0gdmFsdWUgVGhlIHZhbHVlIHRvIHJlbW92ZS5cbiAqIEByZXR1cm5zIGluZGV4IG9mIHRoZSByZW1vdmVkIHZhbHVlLlxuICogICAtIHBvc2l0aXZlIGluZGV4IGlmIHZhbHVlIGZvdW5kIGFuZCByZW1vdmVkLlxuICogICAtIG5lZ2F0aXZlIGluZGV4IGlmIHZhbHVlIG5vdCBmb3VuZC4gKGB+aW5kZXhgIHRvIGdldCB0aGUgdmFsdWUgd2hlcmUgaXQgc2hvdWxkIGhhdmUgYmVlblxuICogICAgIGluc2VydGVkKVxuICovXG5leHBvcnQgZnVuY3Rpb24gYXJyYXlSZW1vdmVTb3J0ZWQoYXJyYXk6IHN0cmluZ1tdLCB2YWx1ZTogc3RyaW5nKTogbnVtYmVyIHtcbiAgY29uc3QgaW5kZXggPSBhcnJheUluZGV4T2ZTb3J0ZWQoYXJyYXksIHZhbHVlKTtcbiAgaWYgKGluZGV4ID49IDApIHtcbiAgICBhcnJheVNwbGljZShhcnJheSwgaW5kZXgsIDEpO1xuICB9XG4gIHJldHVybiBpbmRleDtcbn1cblxuXG4vKipcbiAqIEdldCBhbiBpbmRleCBvZiBhbiBgdmFsdWVgIGluIGEgc29ydGVkIGBhcnJheWAuXG4gKlxuICogTk9URTpcbiAqIC0gVGhpcyB1c2VzIGJpbmFyeSBzZWFyY2ggYWxnb3JpdGhtIGZvciBmYXN0IHJlbW92YWxzLlxuICpcbiAqIEBwYXJhbSBhcnJheSBBIHNvcnRlZCBhcnJheSB0byBiaW5hcnkgc2VhcmNoLlxuICogQHBhcmFtIHZhbHVlIFRoZSB2YWx1ZSB0byBsb29rIGZvci5cbiAqIEByZXR1cm5zIGluZGV4IG9mIHRoZSB2YWx1ZS5cbiAqICAgLSBwb3NpdGl2ZSBpbmRleCBpZiB2YWx1ZSBmb3VuZC5cbiAqICAgLSBuZWdhdGl2ZSBpbmRleCBpZiB2YWx1ZSBub3QgZm91bmQuIChgfmluZGV4YCB0byBnZXQgdGhlIHZhbHVlIHdoZXJlIGl0IHNob3VsZCBoYXZlIGJlZW5cbiAqICAgICBsb2NhdGVkKVxuICovXG5leHBvcnQgZnVuY3Rpb24gYXJyYXlJbmRleE9mU29ydGVkKGFycmF5OiBzdHJpbmdbXSwgdmFsdWU6IHN0cmluZyk6IG51bWJlciB7XG4gIHJldHVybiBfYXJyYXlJbmRleE9mU29ydGVkKGFycmF5LCB2YWx1ZSwgMCk7XG59XG5cblxuLyoqXG4gKiBgS2V5VmFsdWVBcnJheWAgaXMgYW4gYXJyYXkgd2hlcmUgZXZlbiBwb3NpdGlvbnMgY29udGFpbiBrZXlzIGFuZCBvZGQgcG9zaXRpb25zIGNvbnRhaW4gdmFsdWVzLlxuICpcbiAqIGBLZXlWYWx1ZUFycmF5YCBwcm92aWRlcyBhIHZlcnkgZWZmaWNpZW50IHdheSBvZiBpdGVyYXRpbmcgb3ZlciBpdHMgY29udGVudHMuIEZvciBzbWFsbFxuICogc2V0cyAofjEwKSB0aGUgY29zdCBvZiBiaW5hcnkgc2VhcmNoaW5nIGFuIGBLZXlWYWx1ZUFycmF5YCBoYXMgYWJvdXQgdGhlIHNhbWUgcGVyZm9ybWFuY2VcbiAqIGNoYXJhY3RlcmlzdGljcyB0aGF0IG9mIGEgYE1hcGAgd2l0aCBzaWduaWZpY2FudGx5IGJldHRlciBtZW1vcnkgZm9vdHByaW50LlxuICpcbiAqIElmIHVzZWQgYXMgYSBgTWFwYCB0aGUga2V5cyBhcmUgc3RvcmVkIGluIGFscGhhYmV0aWNhbCBvcmRlciBzbyB0aGF0IHRoZXkgY2FuIGJlIGJpbmFyeSBzZWFyY2hlZFxuICogZm9yIHJldHJpZXZhbC5cbiAqXG4gKiBTZWU6IGBrZXlWYWx1ZUFycmF5U2V0YCwgYGtleVZhbHVlQXJyYXlHZXRgLCBga2V5VmFsdWVBcnJheUluZGV4T2ZgLCBga2V5VmFsdWVBcnJheURlbGV0ZWAuXG4gKi9cbmV4cG9ydCBpbnRlcmZhY2UgS2V5VmFsdWVBcnJheTxWQUxVRT4gZXh0ZW5kcyBBcnJheTxWQUxVRXxzdHJpbmc+IHtcbiAgX19icmFuZF9fOiAnYXJyYXktbWFwJztcbn1cblxuLyoqXG4gKiBTZXQgYSBgdmFsdWVgIGZvciBhIGBrZXlgLlxuICpcbiAqIEBwYXJhbSBrZXlWYWx1ZUFycmF5IHRvIG1vZGlmeS5cbiAqIEBwYXJhbSBrZXkgVGhlIGtleSB0byBsb2NhdGUgb3IgY3JlYXRlLlxuICogQHBhcmFtIHZhbHVlIFRoZSB2YWx1ZSB0byBzZXQgZm9yIGEgYGtleWAuXG4gKiBAcmV0dXJucyBpbmRleCAoYWx3YXlzIGV2ZW4pIG9mIHdoZXJlIHRoZSB2YWx1ZSB2YXMgc2V0LlxuICovXG5leHBvcnQgZnVuY3Rpb24ga2V5VmFsdWVBcnJheVNldDxWPihcbiAgICBrZXlWYWx1ZUFycmF5OiBLZXlWYWx1ZUFycmF5PFY+LCBrZXk6IHN0cmluZywgdmFsdWU6IFYpOiBudW1iZXIge1xuICBsZXQgaW5kZXggPSBrZXlWYWx1ZUFycmF5SW5kZXhPZihrZXlWYWx1ZUFycmF5LCBrZXkpO1xuICBpZiAoaW5kZXggPj0gMCkge1xuICAgIC8vIGlmIHdlIGZvdW5kIGl0IHNldCBpdC5cbiAgICBrZXlWYWx1ZUFycmF5W2luZGV4IHwgMV0gPSB2YWx1ZTtcbiAgfSBlbHNlIHtcbiAgICBpbmRleCA9IH5pbmRleDtcbiAgICBhcnJheUluc2VydDIoa2V5VmFsdWVBcnJheSwgaW5kZXgsIGtleSwgdmFsdWUpO1xuICB9XG4gIHJldHVybiBpbmRleDtcbn1cblxuLyoqXG4gKiBSZXRyaWV2ZSBhIGB2YWx1ZWAgZm9yIGEgYGtleWAgKG9uIGB1bmRlZmluZWRgIGlmIG5vdCBmb3VuZC4pXG4gKlxuICogQHBhcmFtIGtleVZhbHVlQXJyYXkgdG8gc2VhcmNoLlxuICogQHBhcmFtIGtleSBUaGUga2V5IHRvIGxvY2F0ZS5cbiAqIEByZXR1cm4gVGhlIGB2YWx1ZWAgc3RvcmVkIGF0IHRoZSBga2V5YCBsb2NhdGlvbiBvciBgdW5kZWZpbmVkIGlmIG5vdCBmb3VuZC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGtleVZhbHVlQXJyYXlHZXQ8Vj4oa2V5VmFsdWVBcnJheTogS2V5VmFsdWVBcnJheTxWPiwga2V5OiBzdHJpbmcpOiBWfHVuZGVmaW5lZCB7XG4gIGNvbnN0IGluZGV4ID0ga2V5VmFsdWVBcnJheUluZGV4T2Yoa2V5VmFsdWVBcnJheSwga2V5KTtcbiAgaWYgKGluZGV4ID49IDApIHtcbiAgICAvLyBpZiB3ZSBmb3VuZCBpdCByZXRyaWV2ZSBpdC5cbiAgICByZXR1cm4ga2V5VmFsdWVBcnJheVtpbmRleCB8IDFdIGFzIFY7XG4gIH1cbiAgcmV0dXJuIHVuZGVmaW5lZDtcbn1cblxuLyoqXG4gKiBSZXRyaWV2ZSBhIGBrZXlgIGluZGV4IHZhbHVlIGluIHRoZSBhcnJheSBvciBgLTFgIGlmIG5vdCBmb3VuZC5cbiAqXG4gKiBAcGFyYW0ga2V5VmFsdWVBcnJheSB0byBzZWFyY2guXG4gKiBAcGFyYW0ga2V5IFRoZSBrZXkgdG8gbG9jYXRlLlxuICogQHJldHVybnMgaW5kZXggb2Ygd2hlcmUgdGhlIGtleSBpcyAob3Igc2hvdWxkIGhhdmUgYmVlbi4pXG4gKiAgIC0gcG9zaXRpdmUgKGV2ZW4pIGluZGV4IGlmIGtleSBmb3VuZC5cbiAqICAgLSBuZWdhdGl2ZSBpbmRleCBpZiBrZXkgbm90IGZvdW5kLiAoYH5pbmRleGAgKGV2ZW4pIHRvIGdldCB0aGUgaW5kZXggd2hlcmUgaXQgc2hvdWxkIGhhdmVcbiAqICAgICBiZWVuIGluc2VydGVkLilcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGtleVZhbHVlQXJyYXlJbmRleE9mPFY+KGtleVZhbHVlQXJyYXk6IEtleVZhbHVlQXJyYXk8Vj4sIGtleTogc3RyaW5nKTogbnVtYmVyIHtcbiAgcmV0dXJuIF9hcnJheUluZGV4T2ZTb3J0ZWQoa2V5VmFsdWVBcnJheSBhcyBzdHJpbmdbXSwga2V5LCAxKTtcbn1cblxuLyoqXG4gKiBEZWxldGUgYSBga2V5YCAoYW5kIGB2YWx1ZWApIGZyb20gdGhlIGBLZXlWYWx1ZUFycmF5YC5cbiAqXG4gKiBAcGFyYW0ga2V5VmFsdWVBcnJheSB0byBtb2RpZnkuXG4gKiBAcGFyYW0ga2V5IFRoZSBrZXkgdG8gbG9jYXRlIG9yIGRlbGV0ZSAoaWYgZXhpc3QpLlxuICogQHJldHVybnMgaW5kZXggb2Ygd2hlcmUgdGhlIGtleSB3YXMgKG9yIHNob3VsZCBoYXZlIGJlZW4uKVxuICogICAtIHBvc2l0aXZlIChldmVuKSBpbmRleCBpZiBrZXkgZm91bmQgYW5kIGRlbGV0ZWQuXG4gKiAgIC0gbmVnYXRpdmUgaW5kZXggaWYga2V5IG5vdCBmb3VuZC4gKGB+aW5kZXhgIChldmVuKSB0byBnZXQgdGhlIGluZGV4IHdoZXJlIGl0IHNob3VsZCBoYXZlXG4gKiAgICAgYmVlbi4pXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBrZXlWYWx1ZUFycmF5RGVsZXRlPFY+KGtleVZhbHVlQXJyYXk6IEtleVZhbHVlQXJyYXk8Vj4sIGtleTogc3RyaW5nKTogbnVtYmVyIHtcbiAgY29uc3QgaW5kZXggPSBrZXlWYWx1ZUFycmF5SW5kZXhPZihrZXlWYWx1ZUFycmF5LCBrZXkpO1xuICBpZiAoaW5kZXggPj0gMCkge1xuICAgIC8vIGlmIHdlIGZvdW5kIGl0IHJlbW92ZSBpdC5cbiAgICBhcnJheVNwbGljZShrZXlWYWx1ZUFycmF5LCBpbmRleCwgMik7XG4gIH1cbiAgcmV0dXJuIGluZGV4O1xufVxuXG5cbi8qKlxuICogSU5URVJOQUw6IEdldCBhbiBpbmRleCBvZiBhbiBgdmFsdWVgIGluIGEgc29ydGVkIGBhcnJheWAgYnkgZ3JvdXBpbmcgc2VhcmNoIGJ5IGBzaGlmdGAuXG4gKlxuICogTk9URTpcbiAqIC0gVGhpcyB1c2VzIGJpbmFyeSBzZWFyY2ggYWxnb3JpdGhtIGZvciBmYXN0IHJlbW92YWxzLlxuICpcbiAqIEBwYXJhbSBhcnJheSBBIHNvcnRlZCBhcnJheSB0byBiaW5hcnkgc2VhcmNoLlxuICogQHBhcmFtIHZhbHVlIFRoZSB2YWx1ZSB0byBsb29rIGZvci5cbiAqIEBwYXJhbSBzaGlmdCBncm91cGluZyBzaGlmdC5cbiAqICAgLSBgMGAgbWVhbnMgbG9vayBhdCBldmVyeSBsb2NhdGlvblxuICogICAtIGAxYCBtZWFucyBvbmx5IGxvb2sgYXQgZXZlcnkgb3RoZXIgKGV2ZW4pIGxvY2F0aW9uICh0aGUgb2RkIGxvY2F0aW9ucyBhcmUgdG8gYmUgaWdub3JlZCBhc1xuICogICAgICAgICB0aGV5IGFyZSB2YWx1ZXMuKVxuICogQHJldHVybnMgaW5kZXggb2YgdGhlIHZhbHVlLlxuICogICAtIHBvc2l0aXZlIGluZGV4IGlmIHZhbHVlIGZvdW5kLlxuICogICAtIG5lZ2F0aXZlIGluZGV4IGlmIHZhbHVlIG5vdCBmb3VuZC4gKGB+aW5kZXhgIHRvIGdldCB0aGUgdmFsdWUgd2hlcmUgaXQgc2hvdWxkIGhhdmUgYmVlblxuICogaW5zZXJ0ZWQpXG4gKi9cbmZ1bmN0aW9uIF9hcnJheUluZGV4T2ZTb3J0ZWQoYXJyYXk6IHN0cmluZ1tdLCB2YWx1ZTogc3RyaW5nLCBzaGlmdDogbnVtYmVyKTogbnVtYmVyIHtcbiAgbmdEZXZNb2RlICYmIGFzc2VydEVxdWFsKEFycmF5LmlzQXJyYXkoYXJyYXkpLCB0cnVlLCAnRXhwZWN0aW5nIGFuIGFycmF5Jyk7XG4gIGxldCBzdGFydCA9IDA7XG4gIGxldCBlbmQgPSBhcnJheS5sZW5ndGggPj4gc2hpZnQ7XG4gIHdoaWxlIChlbmQgIT09IHN0YXJ0KSB7XG4gICAgY29uc3QgbWlkZGxlID0gc3RhcnQgKyAoKGVuZCAtIHN0YXJ0KSA+PiAxKTsgIC8vIGZpbmQgdGhlIG1pZGRsZS5cbiAgICBjb25zdCBjdXJyZW50ID0gYXJyYXlbbWlkZGxlIDw8IHNoaWZ0XTtcbiAgICBpZiAodmFsdWUgPT09IGN1cnJlbnQpIHtcbiAgICAgIHJldHVybiAobWlkZGxlIDw8IHNoaWZ0KTtcbiAgICB9IGVsc2UgaWYgKGN1cnJlbnQgPiB2YWx1ZSkge1xuICAgICAgZW5kID0gbWlkZGxlO1xuICAgIH0gZWxzZSB7XG4gICAgICBzdGFydCA9IG1pZGRsZSArIDE7ICAvLyBXZSBhbHJlYWR5IHNlYXJjaGVkIG1pZGRsZSBzbyBtYWtlIGl0IG5vbi1pbmNsdXNpdmUgYnkgYWRkaW5nIDFcbiAgICB9XG4gIH1cbiAgcmV0dXJuIH4oZW5kIDw8IHNoaWZ0KTtcbn1cbiJdfQ==