/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { keyValueArrayIndexOf } from '../../util/array_utils';
import { assertEqual, assertIndexInRange, assertNotEqual } from '../../util/assert';
import { assertFirstUpdatePass } from '../assert';
import { getTStylingRangeNext, getTStylingRangePrev, setTStylingRangeNext, setTStylingRangeNextDuplicate, setTStylingRangePrev, setTStylingRangePrevDuplicate, toTStylingRange } from '../interfaces/styling';
import { getTView } from '../state';
/**
 * NOTE: The word `styling` is used interchangeably as style or class styling.
 *
 * This file contains code to link styling instructions together so that they can be replayed in
 * priority order. The file exists because Ivy styling instruction execution order does not match
 * that of the priority order. The purpose of this code is to create a linked list so that the
 * instructions can be traversed in priority order when computing the styles.
 *
 * Assume we are dealing with the following code:
 * ```
 * @Component({
 *   template: `
 *     <my-cmp [style]=" {color: '#001'} "
 *             [style.color]=" #002 "
 *             dir-style-color-1
 *             dir-style-color-2> `
 * })
 * class ExampleComponent {
 *   static ngComp = ... {
 *     ...
 *     // Compiler ensures that `ɵɵstyleProp` is after `ɵɵstyleMap`
 *     ɵɵstyleMap({color: '#001'});
 *     ɵɵstyleProp('color', '#002');
 *     ...
 *   }
 * }
 *
 * @Directive({
 *   selector: `[dir-style-color-1]',
 * })
 * class Style1Directive {
 *   @HostBinding('style') style = {color: '#005'};
 *   @HostBinding('style.color') color = '#006';
 *
 *   static ngDir = ... {
 *     ...
 *     // Compiler ensures that `ɵɵstyleProp` is after `ɵɵstyleMap`
 *     ɵɵstyleMap({color: '#005'});
 *     ɵɵstyleProp('color', '#006');
 *     ...
 *   }
 * }
 *
 * @Directive({
 *   selector: `[dir-style-color-2]',
 * })
 * class Style2Directive {
 *   @HostBinding('style') style = {color: '#007'};
 *   @HostBinding('style.color') color = '#008';
 *
 *   static ngDir = ... {
 *     ...
 *     // Compiler ensures that `ɵɵstyleProp` is after `ɵɵstyleMap`
 *     ɵɵstyleMap({color: '#007'});
 *     ɵɵstyleProp('color', '#008');
 *     ...
 *   }
 * }
 *
 * @Directive({
 *   selector: `my-cmp',
 * })
 * class MyComponent {
 *   @HostBinding('style') style = {color: '#003'};
 *   @HostBinding('style.color') color = '#004';
 *
 *   static ngComp = ... {
 *     ...
 *     // Compiler ensures that `ɵɵstyleProp` is after `ɵɵstyleMap`
 *     ɵɵstyleMap({color: '#003'});
 *     ɵɵstyleProp('color', '#004');
 *     ...
 *   }
 * }
 * ```
 *
 * The Order of instruction execution is:
 *
 * NOTE: the comment binding location is for illustrative purposes only.
 *
 * ```
 * // Template: (ExampleComponent)
 *     ɵɵstyleMap({color: '#001'});   // Binding index: 10
 *     ɵɵstyleProp('color', '#002');  // Binding index: 12
 * // MyComponent
 *     ɵɵstyleMap({color: '#003'});   // Binding index: 20
 *     ɵɵstyleProp('color', '#004');  // Binding index: 22
 * // Style1Directive
 *     ɵɵstyleMap({color: '#005'});   // Binding index: 24
 *     ɵɵstyleProp('color', '#006');  // Binding index: 26
 * // Style2Directive
 *     ɵɵstyleMap({color: '#007'});   // Binding index: 28
 *     ɵɵstyleProp('color', '#008');  // Binding index: 30
 * ```
 *
 * The correct priority order of concatenation is:
 *
 * ```
 * // MyComponent
 *     ɵɵstyleMap({color: '#003'});   // Binding index: 20
 *     ɵɵstyleProp('color', '#004');  // Binding index: 22
 * // Style1Directive
 *     ɵɵstyleMap({color: '#005'});   // Binding index: 24
 *     ɵɵstyleProp('color', '#006');  // Binding index: 26
 * // Style2Directive
 *     ɵɵstyleMap({color: '#007'});   // Binding index: 28
 *     ɵɵstyleProp('color', '#008');  // Binding index: 30
 * // Template: (ExampleComponent)
 *     ɵɵstyleMap({color: '#001'});   // Binding index: 10
 *     ɵɵstyleProp('color', '#002');  // Binding index: 12
 * ```
 *
 * What color should be rendered?
 *
 * Once the items are correctly sorted in the list, the answer is simply the last item in the
 * concatenation list which is `#002`.
 *
 * To do so we keep a linked list of all of the bindings which pertain to this element.
 * Notice that the bindings are inserted in the order of execution, but the `TView.data` allows
 * us to traverse them in the order of priority.
 *
 * |Idx|`TView.data`|`LView`          | Notes
 * |---|------------|-----------------|--------------
 * |...|            |                 |
 * |10 |`null`      |`{color: '#001'}`| `ɵɵstyleMap('color', {color: '#001'})`
 * |11 |`30 | 12`   | ...             |
 * |12 |`color`     |`'#002'`         | `ɵɵstyleProp('color', '#002')`
 * |13 |`10 | 0`    | ...             |
 * |...|            |                 |
 * |20 |`null`      |`{color: '#003'}`| `ɵɵstyleMap('color', {color: '#003'})`
 * |21 |`0 | 22`    | ...             |
 * |22 |`color`     |`'#004'`         | `ɵɵstyleProp('color', '#004')`
 * |23 |`20 | 24`   | ...             |
 * |24 |`null`      |`{color: '#005'}`| `ɵɵstyleMap('color', {color: '#005'})`
 * |25 |`22 | 26`   | ...             |
 * |26 |`color`     |`'#006'`         | `ɵɵstyleProp('color', '#006')`
 * |27 |`24 | 28`   | ...             |
 * |28 |`null`      |`{color: '#007'}`| `ɵɵstyleMap('color', {color: '#007'})`
 * |29 |`26 | 30`   | ...             |
 * |30 |`color`     |`'#008'`         | `ɵɵstyleProp('color', '#008')`
 * |31 |`28 | 10`   | ...             |
 *
 * The above data structure allows us to re-concatenate the styling no matter which data binding
 * changes.
 *
 * NOTE: in addition to keeping track of next/previous index the `TView.data` also stores prev/next
 * duplicate bit. The duplicate bit if true says there either is a binding with the same name or
 * there is a map (which may contain the name). This information is useful in knowing if other
 * styles with higher priority need to be searched for overwrites.
 *
 * NOTE: See `should support example in 'tnode_linked_list.ts' documentation` in
 * `tnode_linked_list_spec.ts` for working example.
 */
let __unused_const_as_closure_does_not_like_standalone_comment_blocks__;
/**
 * Insert new `tStyleValue` at `TData` and link existing style bindings such that we maintain linked
 * list of styles and compute the duplicate flag.
 *
 * Note: this function is executed during `firstUpdatePass` only to populate the `TView.data`.
 *
 * The function works by keeping track of `tStylingRange` which contains two pointers pointing to
 * the head/tail of the template portion of the styles.
 *  - if `isHost === false` (we are template) then insertion is at tail of `TStylingRange`
 *  - if `isHost === true` (we are host binding) then insertion is at head of `TStylingRange`
 *
 * @param tData The `TData` to insert into.
 * @param tNode `TNode` associated with the styling element.
 * @param tStylingKey See `TStylingKey`.
 * @param index location of where `tStyleValue` should be stored (and linked into list.)
 * @param isHostBinding `true` if the insertion is for a `hostBinding`. (insertion is in front of
 *               template.)
 * @param isClassBinding True if the associated `tStylingKey` as a `class` styling.
 *                       `tNode.classBindings` should be used (or `tNode.styleBindings` otherwise.)
 */
export function insertTStylingBinding(tData, tNode, tStylingKeyWithStatic, index, isHostBinding, isClassBinding) {
    ngDevMode && assertFirstUpdatePass(getTView());
    let tBindings = isClassBinding ? tNode.classBindings : tNode.styleBindings;
    let tmplHead = getTStylingRangePrev(tBindings);
    let tmplTail = getTStylingRangeNext(tBindings);
    tData[index] = tStylingKeyWithStatic;
    let isKeyDuplicateOfStatic = false;
    let tStylingKey;
    if (Array.isArray(tStylingKeyWithStatic)) {
        // We are case when the `TStylingKey` contains static fields as well.
        const staticKeyValueArray = tStylingKeyWithStatic;
        tStylingKey = staticKeyValueArray[1]; // unwrap.
        // We need to check if our key is present in the static so that we can mark it as duplicate.
        if (tStylingKey === null ||
            keyValueArrayIndexOf(staticKeyValueArray, tStylingKey) > 0) {
            // tStylingKey is present in the statics, need to mark it as duplicate.
            isKeyDuplicateOfStatic = true;
        }
    }
    else {
        tStylingKey = tStylingKeyWithStatic;
    }
    if (isHostBinding) {
        // We are inserting host bindings
        // If we don't have template bindings then `tail` is 0.
        const hasTemplateBindings = tmplTail !== 0;
        // This is important to know because that means that the `head` can't point to the first
        // template bindings (there are none.) Instead the head points to the tail of the template.
        if (hasTemplateBindings) {
            // template head's "prev" will point to last host binding or to 0 if no host bindings yet
            const previousNode = getTStylingRangePrev(tData[tmplHead + 1]);
            tData[index + 1] = toTStylingRange(previousNode, tmplHead);
            // if a host binding has already been registered, we need to update the next of that host
            // binding to point to this one
            if (previousNode !== 0) {
                // We need to update the template-tail value to point to us.
                tData[previousNode + 1] =
                    setTStylingRangeNext(tData[previousNode + 1], index);
            }
            // The "previous" of the template binding head should point to this host binding
            tData[tmplHead + 1] = setTStylingRangePrev(tData[tmplHead + 1], index);
        }
        else {
            tData[index + 1] = toTStylingRange(tmplHead, 0);
            // if a host binding has already been registered, we need to update the next of that host
            // binding to point to this one
            if (tmplHead !== 0) {
                // We need to update the template-tail value to point to us.
                tData[tmplHead + 1] = setTStylingRangeNext(tData[tmplHead + 1], index);
            }
            // if we don't have template, the head points to template-tail, and needs to be advanced.
            tmplHead = index;
        }
    }
    else {
        // We are inserting in template section.
        // We need to set this binding's "previous" to the current template tail
        tData[index + 1] = toTStylingRange(tmplTail, 0);
        ngDevMode &&
            assertEqual(tmplHead !== 0 && tmplTail === 0, false, 'Adding template bindings after hostBindings is not allowed.');
        if (tmplHead === 0) {
            tmplHead = index;
        }
        else {
            // We need to update the previous value "next" to point to this binding
            tData[tmplTail + 1] = setTStylingRangeNext(tData[tmplTail + 1], index);
        }
        tmplTail = index;
    }
    // Now we need to update / compute the duplicates.
    // Starting with our location search towards head (least priority)
    if (isKeyDuplicateOfStatic) {
        tData[index + 1] = setTStylingRangePrevDuplicate(tData[index + 1]);
    }
    markDuplicates(tData, tStylingKey, index, true, isClassBinding);
    markDuplicates(tData, tStylingKey, index, false, isClassBinding);
    markDuplicateOfResidualStyling(tNode, tStylingKey, tData, index, isClassBinding);
    tBindings = toTStylingRange(tmplHead, tmplTail);
    if (isClassBinding) {
        tNode.classBindings = tBindings;
    }
    else {
        tNode.styleBindings = tBindings;
    }
}
/**
 * Look into the residual styling to see if the current `tStylingKey` is duplicate of residual.
 *
 * @param tNode `TNode` where the residual is stored.
 * @param tStylingKey `TStylingKey` to store.
 * @param tData `TData` associated with the current `LView`.
 * @param index location of where `tStyleValue` should be stored (and linked into list.)
 * @param isClassBinding True if the associated `tStylingKey` as a `class` styling.
 *                       `tNode.classBindings` should be used (or `tNode.styleBindings` otherwise.)
 */
function markDuplicateOfResidualStyling(tNode, tStylingKey, tData, index, isClassBinding) {
    const residual = isClassBinding ? tNode.residualClasses : tNode.residualStyles;
    if (residual != null /* or undefined */ && typeof tStylingKey == 'string' &&
        keyValueArrayIndexOf(residual, tStylingKey) >= 0) {
        // We have duplicate in the residual so mark ourselves as duplicate.
        tData[index + 1] = setTStylingRangeNextDuplicate(tData[index + 1]);
    }
}
/**
 * Marks `TStyleValue`s as duplicates if another style binding in the list has the same
 * `TStyleValue`.
 *
 * NOTE: this function is intended to be called twice once with `isPrevDir` set to `true` and once
 * with it set to `false` to search both the previous as well as next items in the list.
 *
 * No duplicate case
 * ```
 *   [style.color]
 *   [style.width.px] <<- index
 *   [style.height.px]
 * ```
 *
 * In the above case adding `[style.width.px]` to the existing `[style.color]` produces no
 * duplicates because `width` is not found in any other part of the linked list.
 *
 * Duplicate case
 * ```
 *   [style.color]
 *   [style.width.em]
 *   [style.width.px] <<- index
 * ```
 * In the above case adding `[style.width.px]` will produce a duplicate with `[style.width.em]`
 * because `width` is found in the chain.
 *
 * Map case 1
 * ```
 *   [style.width.px]
 *   [style.color]
 *   [style]  <<- index
 * ```
 * In the above case adding `[style]` will produce a duplicate with any other bindings because
 * `[style]` is a Map and as such is fully dynamic and could produce `color` or `width`.
 *
 * Map case 2
 * ```
 *   [style]
 *   [style.width.px]
 *   [style.color]  <<- index
 * ```
 * In the above case adding `[style.color]` will produce a duplicate because there is already a
 * `[style]` binding which is a Map and as such is fully dynamic and could produce `color` or
 * `width`.
 *
 * NOTE: Once `[style]` (Map) is added into the system all things are mapped as duplicates.
 * NOTE: We use `style` as example, but same logic is applied to `class`es as well.
 *
 * @param tData `TData` where the linked list is stored.
 * @param tStylingKey `TStylingKeyPrimitive` which contains the value to compare to other keys in
 *        the linked list.
 * @param index Starting location in the linked list to search from
 * @param isPrevDir Direction.
 *        - `true` for previous (lower priority);
 *        - `false` for next (higher priority).
 */
function markDuplicates(tData, tStylingKey, index, isPrevDir, isClassBinding) {
    const tStylingAtIndex = tData[index + 1];
    const isMap = tStylingKey === null;
    let cursor = isPrevDir ? getTStylingRangePrev(tStylingAtIndex) : getTStylingRangeNext(tStylingAtIndex);
    let foundDuplicate = false;
    // We keep iterating as long as we have a cursor
    // AND either:
    // - we found what we are looking for, OR
    // - we are a map in which case we have to continue searching even after we find what we were
    //   looking for since we are a wild card and everything needs to be flipped to duplicate.
    while (cursor !== 0 && (foundDuplicate === false || isMap)) {
        ngDevMode && assertIndexInRange(tData, cursor);
        const tStylingValueAtCursor = tData[cursor];
        const tStyleRangeAtCursor = tData[cursor + 1];
        if (isStylingMatch(tStylingValueAtCursor, tStylingKey)) {
            foundDuplicate = true;
            tData[cursor + 1] = isPrevDir ? setTStylingRangeNextDuplicate(tStyleRangeAtCursor) :
                setTStylingRangePrevDuplicate(tStyleRangeAtCursor);
        }
        cursor = isPrevDir ? getTStylingRangePrev(tStyleRangeAtCursor) :
            getTStylingRangeNext(tStyleRangeAtCursor);
    }
    if (foundDuplicate) {
        // if we found a duplicate, than mark ourselves.
        tData[index + 1] = isPrevDir ? setTStylingRangePrevDuplicate(tStylingAtIndex) :
            setTStylingRangeNextDuplicate(tStylingAtIndex);
    }
}
/**
 * Determines if two `TStylingKey`s are a match.
 *
 * When computing whether a binding contains a duplicate, we need to compare if the instruction
 * `TStylingKey` has a match.
 *
 * Here are examples of `TStylingKey`s which match given `tStylingKeyCursor` is:
 * - `color`
 *    - `color`    // Match another color
 *    - `null`     // That means that `tStylingKey` is a `classMap`/`styleMap` instruction
 *    - `['', 'color', 'other', true]` // wrapped `color` so match
 *    - `['', null, 'other', true]`       // wrapped `null` so match
 *    - `['', 'width', 'color', 'value']` // wrapped static value contains a match on `'color'`
 * - `null`       // `tStylingKeyCursor` always match as it is `classMap`/`styleMap` instruction
 *
 * @param tStylingKeyCursor
 * @param tStylingKey
 */
function isStylingMatch(tStylingKeyCursor, tStylingKey) {
    ngDevMode &&
        assertNotEqual(Array.isArray(tStylingKey), true, 'Expected that \'tStylingKey\' has been unwrapped');
    if (tStylingKeyCursor === null || // If the cursor is `null` it means that we have map at that
        // location so we must assume that we have a match.
        tStylingKey == null || // If `tStylingKey` is `null` then it is a map therefor assume that it
        // contains a match.
        (Array.isArray(tStylingKeyCursor) ? tStylingKeyCursor[1] : tStylingKeyCursor) ===
            tStylingKey // If the keys match explicitly than we are a match.
    ) {
        return true;
    }
    else if (Array.isArray(tStylingKeyCursor) && typeof tStylingKey === 'string') {
        // if we did not find a match, but `tStylingKeyCursor` is `KeyValueArray` that means cursor has
        // statics and we need to check those as well.
        return keyValueArrayIndexOf(tStylingKeyCursor, tStylingKey) >=
            0; // see if we are matching the key
    }
    return false;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3R5bGVfYmluZGluZ19saXN0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zcmMvcmVuZGVyMy9zdHlsaW5nL3N0eWxlX2JpbmRpbmdfbGlzdC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLEVBQWdCLG9CQUFvQixFQUFDLE1BQU0sd0JBQXdCLENBQUM7QUFDM0UsT0FBTyxFQUFDLFdBQVcsRUFBRSxrQkFBa0IsRUFBRSxjQUFjLEVBQUMsTUFBTSxtQkFBbUIsQ0FBQztBQUNsRixPQUFPLEVBQUMscUJBQXFCLEVBQUMsTUFBTSxXQUFXLENBQUM7QUFFaEQsT0FBTyxFQUFDLG9CQUFvQixFQUFFLG9CQUFvQixFQUFFLG9CQUFvQixFQUFFLDZCQUE2QixFQUFFLG9CQUFvQixFQUFFLDZCQUE2QixFQUFFLGVBQWUsRUFBbUQsTUFBTSx1QkFBdUIsQ0FBQztBQUU5UCxPQUFPLEVBQUMsUUFBUSxFQUFDLE1BQU0sVUFBVSxDQUFDO0FBR2xDOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdKRztBQUNILElBQUksbUVBQThFLENBQUM7QUFFbkY7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0FtQkc7QUFDSCxNQUFNLFVBQVUscUJBQXFCLENBQ2pDLEtBQVksRUFBRSxLQUFZLEVBQUUscUJBQWtDLEVBQUUsS0FBYSxFQUM3RSxhQUFzQixFQUFFLGNBQXVCO0lBQ2pELFNBQVMsSUFBSSxxQkFBcUIsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxDQUFDO0lBQy9DLElBQUksU0FBUyxHQUFHLGNBQWMsQ0FBQyxDQUFDLENBQUMsS0FBSyxD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RUFBRTtRQUM5RSwrRkFBK0Y7UUFDL0YsOENBQThDO1FBQzlDLE9BQU8sb0JBQW9CLENBQUMsaUJBQWlCLEVBQUUsV0FBVyxDQUFDO1lBQ3ZELENBQUMsQ0FBQyxDQUFFLGlDQUFpQztLQUMxQztJQUNELE9BQU8sS0FBSyxDQUFDO0FBQ2YsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0tleVZhbHVlQXJyYXksIGtleVZhbHVlQXJyYXlJbmRleE9mfSBmcm9tICcuLi8uLi91dGlsL2FycmF5X3V0aWxzJztcbmltcG9ydCB7YXNzZXJ0RXF1YWwsIGFzc2VydEluZGV4SW5SYW5nZSwgYXNzZXJ0Tm90RXF1YWx9IGZyb20gJy4uLy4uL3V0aWwvYXNzZXJ0JztcbmltcG9ydCB7YXNzZXJ0Rmlyc3RVcGRhdGVQYXNzfSBmcm9tICcuLi9hc3NlcnQnO1xuaW1wb3J0IHtUTm9kZX0gZnJvbSAnLi4vaW50ZXJmYWNlcy9ub2RlJztcbmltcG9ydCB7Z2V0VFN0eWxpbmdSYW5nZU5leHQsIGdldFRTdHlsaW5nUmFuZ2VQcmV2LCBzZXRUU3R5bGluZ1JhbmdlTmV4dCwgc2V0VFN0eWxpbmdSYW5nZU5leHREdXBsaWNhdGUsIHNldFRTdHlsaW5nUmFuZ2VQcmV2LCBzZXRUU3R5bGluZ1JhbmdlUHJldkR1cGxpY2F0ZSwgdG9UU3R5bGluZ1JhbmdlLCBUU3R5bGluZ0tleSwgVFN0eWxpbmdLZXlQcmltaXRpdmUsIFRTdHlsaW5nUmFuZ2V9IGZyb20gJy4uL2ludGVyZmFjZXMvc3R5bGluZyc7XG5pbXBvcnQge1REYXRhfSBmcm9tICcuLi9pbnRlcmZhY2VzL3ZpZXcnO1xuaW1wb3J0IHtnZXRUVmlld30gZnJvbSAnLi4vc3RhdGUnO1xuXG5cbi8qKlxuICogTk9URTogVGhlIHdvcmQgYHN0eWxpbmdgIGlzIHVzZWQgaW50ZXJjaGFuZ2VhYmx5IGFzIHN0eWxlIG9yIGNsYXNzIHN0eWxpbmcuXG4gKlxuICogVGhpcyBmaWxlIGNvbnRhaW5zIGNvZGUgdG8gbGluayBzdHlsaW5nIGluc3RydWN0aW9ucyB0b2dldGhlciBzbyB0aGF0IHRoZXkgY2FuIGJlIHJlcGxheWVkIGluXG4gKiBwcmlvcml0eSBvcmRlci4gVGhlIGZpbGUgZXhpc3RzIGJlY2F1c2UgSXZ5IHN0eWxpbmcgaW5zdHJ1Y3Rpb24gZXhlY3V0aW9uIG9yZGVyIGRvZXMgbm90IG1hdGNoXG4gKiB0aGF0IG9mIHRoZSBwcmlvcml0eSBvcmRlci4gVGhlIHB1cnBvc2Ugb2YgdGhpcyBjb2RlIGlzIHRvIGNyZWF0ZSBhIGxpbmtlZCBsaXN0IHNvIHRoYXQgdGhlXG4gKiBpbnN0cnVjdGlvbnMgY2FuIGJlIHRyYXZlcnNlZCBpbiBwcmlvcml0eSBvcmRlciB3aGVuIGNvbXB1dGluZyB0aGUgc3R5bGVzLlxuICpcbiAqIEFzc3VtZSB3ZSBhcmUgZGVhbGluZyB3aXRoIHRoZSBmb2xsb3dpbmcgY29kZTpcbiAqIGBgYFxuICogQENvbXBvbmVudCh7XG4gKiAgIHRlbXBsYXRlOiBgXG4gKiAgICAgPG15LWNtcCBbc3R5bGVdPVwiIHtjb2xvcjogJyMwMDEnfSBcIlxuICogICAgICAgICAgICAgW3N0eWxlLmNvbG9yXT1cIiAjMDAyIFwiXG4gKiAgICAgICAgICAgICBkaXItc3R5bGUtY29sb3ItMVxuICogICAgICAgICAgICAgZGlyLXN0eWxlLWNvbG9yLTI+IGBcbiAqIH0pXG4gKiBjbGFzcyBFeGFtcGxlQ29tcG9uZW50IHtcbiAqICAgc3RhdGljIG5nQ29tcCA9IC4uLiB7XG4gKiAgICAgLi4uXG4gKiAgICAgLy8gQ29tcGlsZXIgZW5zdXJlcyB0aGF0IGDJtcm1c3R5bGVQcm9wYCBpcyBhZnRlciBgybXJtXN0eWxlTWFwYFxuICogICAgIMm1ybVzdHlsZU1hcCh7Y29sb3I6ICcjMDAxJ30pO1xuICogICAgIMm1ybVzdHlsZVByb3AoJ2NvbG9yJywgJyMwMDInKTtcbiAqICAgICAuLi5cbiAqICAgfVxuICogfVxuICpcbiAqIEBEaXJlY3RpdmUoe1xuICogICBzZWxlY3RvcjogYFtkaXItc3R5bGUtY29sb3ItMV0nLFxuICogfSlcbiAqIGNsYXNzIFN0eWxlMURpcmVjdGl2ZSB7XG4gKiAgIEBIb3N0QmluZGluZygnc3R5bGUnKSBzdHlsZSA9IHtjb2xvcjogJyMwMDUnfTtcbiAqICAgQEhvc3RCaW5kaW5nKCdzdHlsZS5jb2xvcicpIGNvbG9yID0gJyMwMDYnO1xuICpcbiAqICAgc3RhdGljIG5nRGlyID0gLi4uIHtcbiAqICAgICAuLi5cbiAqICAgICAvLyBDb21waWxlciBlbnN1cmVzIHRoYXQgYMm1ybVzdHlsZVByb3BgIGlzIGFmdGVyIGDJtcm1c3R5bGVNYXBgXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDUnfSk7XG4gKiAgICAgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwNicpO1xuICogICAgIC4uLlxuICogICB9XG4gKiB9XG4gKlxuICogQERpcmVjdGl2ZSh7XG4gKiAgIHNlbGVjdG9yOiBgW2Rpci1zdHlsZS1jb2xvci0yXScsXG4gKiB9KVxuICogY2xhc3MgU3R5bGUyRGlyZWN0aXZlIHtcbiAqICAgQEhvc3RCaW5kaW5nKCdzdHlsZScpIHN0eWxlID0ge2NvbG9yOiAnIzAwNyd9O1xuICogICBASG9zdEJpbmRpbmcoJ3N0eWxlLmNvbG9yJykgY29sb3IgPSAnIzAwOCc7XG4gKlxuICogICBzdGF0aWMgbmdEaXIgPSAuLi4ge1xuICogICAgIC4uLlxuICogICAgIC8vIENvbXBpbGVyIGVuc3VyZXMgdGhhdCBgybXJtXN0eWxlUHJvcGAgaXMgYWZ0ZXIgYMm1ybVzdHlsZU1hcGBcbiAqICAgICDJtcm1c3R5bGVNYXAoe2NvbG9yOiAnIzAwNyd9KTtcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA4Jyk7XG4gKiAgICAgLi4uXG4gKiAgIH1cbiAqIH1cbiAqXG4gKiBARGlyZWN0aXZlKHtcbiAqICAgc2VsZWN0b3I6IGBteS1jbXAnLFxuICogfSlcbiAqIGNsYXNzIE15Q29tcG9uZW50IHtcbiAqICAgQEhvc3RCaW5kaW5nKCdzdHlsZScpIHN0eWxlID0ge2NvbG9yOiAnIzAwMyd9O1xuICogICBASG9zdEJpbmRpbmcoJ3N0eWxlLmNvbG9yJykgY29sb3IgPSAnIzAwNCc7XG4gKlxuICogICBzdGF0aWMgbmdDb21wID0gLi4uIHtcbiAqICAgICAuLi5cbiAqICAgICAvLyBDb21waWxlciBlbnN1cmVzIHRoYXQgYMm1ybVzdHlsZVByb3BgIGlzIGFmdGVyIGDJtcm1c3R5bGVNYXBgXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDMnfSk7XG4gKiAgICAgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwNCcpO1xuICogICAgIC4uLlxuICogICB9XG4gKiB9XG4gKiBgYGBcbiAqXG4gKiBUaGUgT3JkZXIgb2YgaW5zdHJ1Y3Rpb24gZXhlY3V0aW9uIGlzOlxuICpcbiAqIE5PVEU6IHRoZSBjb21tZW50IGJpbmRpbmcgbG9jYXRpb24gaXMgZm9yIGlsbHVzdHJhdGl2ZSBwdXJwb3NlcyBvbmx5LlxuICpcbiAqIGBgYFxuICogLy8gVGVtcGxhdGU6IChFeGFtcGxlQ29tcG9uZW50KVxuICogICAgIMm1ybVzdHlsZU1hcCh7Y29sb3I6ICcjMDAxJ30pOyAgIC8vIEJpbmRpbmcgaW5kZXg6IDEwXG4gKiAgICAgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwMicpOyAgLy8gQmluZGluZyBpbmRleDogMTJcbiAqIC8vIE15Q29tcG9uZW50XG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDMnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjBcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA0Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAyMlxuICogLy8gU3R5bGUxRGlyZWN0aXZlXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDUnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjRcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA2Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAyNlxuICogLy8gU3R5bGUyRGlyZWN0aXZlXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDcnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjhcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA4Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAzMFxuICogYGBgXG4gKlxuICogVGhlIGNvcnJlY3QgcHJpb3JpdHkgb3JkZXIgb2YgY29uY2F0ZW5hdGlvbiBpczpcbiAqXG4gKiBgYGBcbiAqIC8vIE15Q29tcG9uZW50XG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDMnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjBcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA0Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAyMlxuICogLy8gU3R5bGUxRGlyZWN0aXZlXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDUnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjRcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA2Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAyNlxuICogLy8gU3R5bGUyRGlyZWN0aXZlXG4gKiAgICAgybXJtXN0eWxlTWFwKHtjb2xvcjogJyMwMDcnfSk7ICAgLy8gQmluZGluZyBpbmRleDogMjhcbiAqICAgICDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA4Jyk7ICAvLyBCaW5kaW5nIGluZGV4OiAzMFxuICogLy8gVGVtcGxhdGU6IChFeGFtcGxlQ29tcG9uZW50KVxuICogICAgIMm1ybVzdHlsZU1hcCh7Y29sb3I6ICcjMDAxJ30pOyAgIC8vIEJpbmRpbmcgaW5kZXg6IDEwXG4gKiAgICAgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwMicpOyAgLy8gQmluZGluZyBpbmRleDogMTJcbiAqIGBgYFxuICpcbiAqIFdoYXQgY29sb3Igc2hvdWxkIGJlIHJlbmRlcmVkP1xuICpcbiAqIE9uY2UgdGhlIGl0ZW1zIGFyZSBjb3JyZWN0bHkgc29ydGVkIGluIHRoZSBsaXN0LCB0aGUgYW5zd2VyIGlzIHNpbXBseSB0aGUgbGFzdCBpdGVtIGluIHRoZVxuICogY29uY2F0ZW5hdGlvbiBsaXN0IHdoaWNoIGlzIGAjMDAyYC5cbiAqXG4gKiBUbyBkbyBzbyB3ZSBrZWVwIGEgbGlua2VkIGxpc3Qgb2YgYWxsIG9mIHRoZSBiaW5kaW5ncyB3aGljaCBwZXJ0YWluIHRvIHRoaXMgZWxlbWVudC5cbiAqIE5vdGljZSB0aGF0IHRoZSBiaW5kaW5ncyBhcmUgaW5zZXJ0ZWQgaW4gdGhlIG9yZGVyIG9mIGV4ZWN1dGlvbiwgYnV0IHRoZSBgVFZpZXcuZGF0YWAgYWxsb3dzXG4gKiB1cyB0byB0cmF2ZXJzZSB0aGVtIGluIHRoZSBvcmRlciBvZiBwcmlvcml0eS5cbiAqXG4gKiB8SWR4fGBUVmlldy5kYXRhYHxgTFZpZXdgICAgICAgICAgIHwgTm90ZXNcbiAqIHwtLS18LS0tLS0tLS0tLS0tfC0tLS0tLS0tLS0tLS0tLS0tfC0tLS0tLS0tLS0tLS0tXG4gKiB8Li4ufCAgICAgICAgICAgIHwgICAgICAgICAgICAgICAgIHxcbiAqIHwxMCB8YG51bGxgICAgICAgfGB7Y29sb3I6ICcjMDAxJ31gfCBgybXJtXN0eWxlTWFwKCdjb2xvcicsIHtjb2xvcjogJyMwMDEnfSlgXG4gKiB8MTEgfGAzMCB8IDEyYCAgIHwgLi4uICAgICAgICAgICAgIHxcbiAqIHwxMiB8YGNvbG9yYCAgICAgfGAnIzAwMidgICAgICAgICAgfCBgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwMicpYFxuICogfDEzIHxgMTAgfCAwYCAgICB8IC4uLiAgICAgICAgICAgICB8XG4gKiB8Li4ufCAgICAgICAgICAgIHwgICAgICAgICAgICAgICAgIHxcbiAqIHwyMCB8YG51bGxgICAgICAgfGB7Y29sb3I6ICcjMDAzJ31gfCBgybXJtXN0eWxlTWFwKCdjb2xvcicsIHtjb2xvcjogJyMwMDMnfSlgXG4gKiB8MjEgfGAwIHwgMjJgICAgIHwgLi4uICAgICAgICAgICAgIHxcbiAqIHwyMiB8YGNvbG9yYCAgICAgfGAnIzAwNCdgICAgICAgICAgfCBgybXJtXN0eWxlUHJvcCgnY29sb3InLCAnIzAwNCcpYFxuICogfDIzIHxgMjAgfCAyNGAgICB8IC4uLiAgICAgICAgICAgICB8XG4gKiB8MjQgfGBudWxsYCAgICAgIHxge2NvbG9yOiAnIzAwNSd9YHwgYMm1ybVzdHlsZU1hcCgnY29sb3InLCB7Y29sb3I6ICcjMDA1J30pYFxuICogfDI1IHxgMjIgfCAyNmAgICB8IC4uLiAgICAgICAgICAgICB8XG4gKiB8MjYgfGBjb2xvcmAgICAgIHxgJyMwMDYnYCAgICAgICAgIHwgYMm1ybVzdHlsZVByb3AoJ2NvbG9yJywgJyMwMDYnKWBcbiAqIHwyNyB8YDI0IHwgMjhgICAgfCAuLi4gICAgICAgICAgICAgfFxuICogfDI4IHxgbnVsbGAgICAgICB8YHtjb2xvcjogJyMwMDcnfWB8IGDJtcm1c3R5bGVNYXAoJ2NvbG9yJywge2NvbG9yOiAnIzAwNyd9KWBcbiAqIHwyOSB8YDI2IHwgMzBgICAgfCAuLi4gICAgICAgICAgICAgfFxuICogfDMwIHxgY29sb3JgICAgICB8YCcjMDA4J2AgICAgICAgICB8IGDJtcm1c3R5bGVQcm9wKCdjb2xvcicsICcjMDA4JylgXG4gKiB8MzEgfGAyOCB8IDEwYCAgIHwgLi4uICAgICAgICAgICAgIHxcbiAqXG4gKiBUaGUgYWJvdmUgZGF0YSBzdHJ1Y3R1cmUgYWxsb3dzIHVzIHRvIHJlLWNvbmNhdGVuYXRlIHRoZSBzdHlsaW5nIG5vIG1hdHRlciB3aGljaCBkYXRhIGJpbmRpbmdcbiAqIGNoYW5nZXMuXG4gKlxuICogTk9URTogaW4gYWRkaXRpb24gdG8ga2VlcGluZyB0cmFjayBvZiBuZXh0L3ByZXZpb3VzIGluZGV4IHRoZSBgVFZpZXcuZGF0YWAgYWxzbyBzdG9yZXMgcHJldi9uZXh0XG4gKiBkdXBsaWNhdGUgYml0LiBUaGUgZHVwbGljYXRlIGJpdCBpZiB0cnVlIHNheXMgdGhlcmUgZWl0aGVyIGlzIGEgYmluZGluZyB3aXRoIHRoZSBzYW1lIG5hbWUgb3JcbiAqIHRoZXJlIGlzIGEgbWFwICh3aGljaCBtYXkgY29udGFpbiB0aGUgbmFtZSkuIFRoaXMgaW5mb3JtYXRpb24gaXMgdXNlZnVsIGluIGtub3dpbmcgaWYgb3RoZXJcbiAqIHN0eWxlcyB3aXRoIGhpZ2hlciBwcmlvcml0eSBuZWVkIHRvIGJlIHNlYXJjaGVkIGZvciBvdmVyd3JpdGVzLlxuICpcbiAqIE5PVEU6IFNlZSBgc2hvdWxkIHN1cHBvcnQgZXhhbXBsZSBpbiAndG5vZGVfbGlua2VkX2xpc3QudHMnIGRvY3VtZW50YXRpb25gIGluXG4gKiBgdG5vZGVfbGlua2VkX2xpc3Rfc3BlYy50c2AgZm9yIHdvcmtpbmcgZXhhbXBsZS5cbiAqL1xubGV0IF9fdW51c2VkX2NvbnN0X2FzX2Nsb3N1cmVfZG9lc19ub3RfbGlrZV9zdGFuZGFsb25lX2NvbW1lbnRfYmxvY2tzX186IHVuZGVmaW5lZDtcblxuLyoqXG4gKiBJbnNlcnQgbmV3IGB0U3R5bGVWYWx1ZWAgYXQgYFREYXRhYCBhbmQgbGluayBleGlzdGluZyBzdHlsZSBiaW5kaW5ncyBzdWNoIHRoYXQgd2UgbWFpbnRhaW4gbGlua2VkXG4gKiBsaXN0IG9mIHN0eWxlcyBhbmQgY29tcHV0ZSB0aGUgZHVwbGljYXRlIGZsYWcuXG4gKlxuICogTm90ZTogdGhpcyBmdW5jdGlvbiBpcyBleGVjdXRlZCBkdXJpbmcgYGZpcnN0VXBkYXRlUGFzc2Agb25seSB0byBwb3B1bGF0ZSB0aGUgYFRWaWV3LmRhdGFgLlxuICpcbiAqIFRoZSBmdW5jdGlvbiB3b3JrcyBieSBrZWVwaW5nIHRyYWNrIG9mIGB0U3R5bGluZ1JhbmdlYCB3aGljaCBjb250YWlucyB0d28gcG9pbnRlcnMgcG9pbnRpbmcgdG9cbiAqIHRoZSBoZWFkL3RhaWwgb2YgdGhlIHRlbXBsYXRlIHBvcnRpb24gb2YgdGhlIHN0eWxlcy5cbiAqICAtIGlmIGBpc0hvc3QgPT09IGZhbHNlYCAod2UgYXJlIHRlbXBsYXRlKSB0aGVuIGluc2VydGlvbiBpcyBhdCB0YWlsIG9mIGBUU3R5bGluZ1JhbmdlYFxuICogIC0gaWYgYGlzSG9zdCA9PT0gdHJ1ZWAgKHdlIGFyZSBob3N0IGJpbmRpbmcpIHRoZW4gaW5zZXJ0aW9uIGlzIGF0IGhlYWQgb2YgYFRTdHlsaW5nUmFuZ2VgXG4gKlxuICogQHBhcmFtIHREYXRhIFRoZSBgVERhdGFgIHRvIGluc2VydCBpbnRvLlxuICogQHBhcmFtIHROb2RlIGBUTm9kZWAgYXNzb2NpYXRlZCB3aXRoIHRoZSBzdHlsaW5nIGVsZW1lbnQuXG4gKiBAcGFyYW0gdFN0eWxpbmdLZXkgU2VlIGBUU3R5bGluZ0tleWAuXG4gKiBAcGFyYW0gaW5kZXggbG9jYXRpb24gb2Ygd2hlcmUgYHRTdHlsZVZhbHVlYCBzaG91bGQgYmUgc3RvcmVkIChhbmQgbGlua2VkIGludG8gbGlzdC4pXG4gKiBAcGFyYW0gaXNIb3N0QmluZGluZyBgdHJ1ZWAgaWYgdGhlIGluc2VydGlvbiBpcyBmb3IgYSBgaG9zdEJpbmRpbmdgLiAoaW5zZXJ0aW9uIGlzIGluIGZyb250IG9mXG4gKiAgICAgICAgICAgICAgIHRlbXBsYXRlLilcbiAqIEBwYXJhbSBpc0NsYXNzQmluZGluZyBUcnVlIGlmIHRoZSBhc3NvY2lhdGVkIGB0U3R5bGluZ0tleWAgYXMgYSBgY2xhc3NgIHN0eWxpbmcuXG4gKiAgICAgICAgICAgICAgICAgICAgICAgYHROb2RlLmNsYXNzQmluZGluZ3NgIHNob3VsZCBiZSB1c2VkIChvciBgdE5vZGUuc3R5bGVCaW5kaW5nc2Agb3RoZXJ3aXNlLilcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGluc2VydFRTdHlsaW5nQmluZGluZyhcbiAgICB0RGF0YTogVERhdGEsIHROb2RlOiBUTm9kZSwgdFN0eWxpbmdLZXlXaXRoU3RhdGljOiBUU3R5bGluZ0tleSwgaW5kZXg6IG51bWJlcixcbiAgICBpc0hvc3RCaW5kaW5nOiBib29sZWFuLCBpc0NsYXNzQmluZGluZzogYm9vbGVhbik6IHZvaWQge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0Rmlyc3RVcGRhdGVQYXNzKGdldFRWaWV3KCkpO1xuICBsZXQgdEJpbmRpbmdzID0gaXNDbGFzc0JpbmRpbmcgPyB0Tm9kZS5jbGFzc0JpbmRpbmdzIDogdE5vZGUuc3R5bGVCaW5kaW5ncztcbiAgbGV0IHRtcGxIZWFkID0gZ2V0VFN0eWxpbmdSYW5nZVByZXYodEJpbmRpbmdzKTtcbiAgbGV0IHRtcGxUYWlsID0gZ2V0VFN0eWxpbmdSYW5nZU5leHQodEJpbmRpbmdzKTtcblxuICB0RGF0YVtpbmRleF0gPSB0U3R5bGluZ0tleVdpdGhTdGF0aWM7XG4gIGxldCBpc0tleUR1cGxpY2F0ZU9mU3RhdGljID0gZmFsc2U7XG4gIGxldCB0U3R5bGluZ0tleTogVFN0eWxpbmdLZXlQcmltaXRpdmU7XG4gIGlmIChBcnJheS5pc0FycmF5KHRTdHlsaW5nS2V5V2l0aFN0YXRpYykpIHtcbiAgICAvLyBXZSBhcmUgY2FzZSB3aGVuIHRoZSBgVFN0eWxpbmdLZXlgIGNvbnRhaW5zIHN0YXRpYyBmaWVsZHMgYXMgd2VsbC5cbiAgICBjb25zdCBzdGF0aWNLZXlWYWx1ZUFycmF5ID0gdFN0eWxpbmdLZXlXaXRoU3RhdGljIGFzIEtleVZhbHVlQXJyYXk8YW55PjtcbiAgICB0U3R5bGluZ0tleSA9IHN0YXRpY0tleVZhbHVlQXJyYXlbMV07ICAvLyB1bndyYXAuXG4gICAgLy8gV2UgbmVlZCB0byBjaGVjayBpZiBvdXIga2V5IGlzIHByZXNlbnQgaW4gdGhlIHN0YXRpYyBzbyB0aGF0IHdlIGNhbiBtYXJrIGl0IGFzIGR1cGxpY2F0ZS5cbiAgICBpZiAodFN0eWxpbmdLZXkgPT09IG51bGwgfHxcbiAgICAgICAga2V5VmFsdWVBcnJheUluZGV4T2Yoc3RhdGljS2V5VmFsdWVBcnJheSwgdFN0eWxpbmdLZXkgYXMgc3RyaW5nKSA+IDApIHtcbiAgICAgIC8vIHRTdHlsaW5nS2V5IGlzIHByZXNlbnQgaW4gdGhlIHN0YXRpY3MsIG5lZWQgdG8gbWFyayBpdCBhcyBkdXBsaWNhdGUuXG4gICAgICBpc0tleUR1cGxpY2F0ZU9mU3RhdGljID0gdHJ1ZTtcbiAgICB9XG4gIH0gZWxzZSB7XG4gICAgdFN0eWxpbmdLZXkgPSB0U3R5bGluZ0tleVdpdGhTdGF0aWM7XG4gIH1cbiAgaWYgKGlzSG9zdEJpbmRpbmcpIHtcbiAgICAvLyBXZSBhcmUgaW5zZXJ0aW5nIGhvc3QgYmluZGluZ3NcblxuICAgIC8vIElmIHdlIGRvbid0IGhhdmUgdGVtcGxhdGUgYmluZGluZ3MgdGhlbiBgdGFpbGAgaXMgMC5cbiAgICBjb25zdCBoYXNUZW1wbGF0ZUJpbmRpbmdzID0gdG1wbFRhaWwgIT09IDA7XG4gICAgLy8gVGhpcyBpcyBpbXBvcnRhbnQgdG8ga25vdyBiZWNhdXNlIHRoYXQgbWVhbnMgdGhhdCB0aGUgYGhlYWRgIGNhbid0IHBvaW50IHRvIHRoZSBmaXJzdFxuICAgIC8vIHRlbXBsYXRlIGJpbmRpbmdzICh0aGVyZSBhcmUgbm9uZS4pIEluc3RlYWQgdGhlIGhlYWQgcG9pbnRzIHRvIHRoZSB0YWlsIG9mIHRoZSB0ZW1wbGF0ZS5cbiAgICBpZiAoaGFzVGVtcGxhdGVCaW5kaW5ncykge1xuICAgICAgLy8gdGVtcGxhdGUgaGVhZCdzIFwicHJldlwiIHdpbGwgcG9pbnQgdG8gbGFzdCBob3N0IGJpbmRpbmcgb3IgdG8gMCBpZiBubyBob3N0IGJpbmRpbmdzIHlldFxuICAgICAgY29uc3QgcHJldmlvdXNOb2RlID0gZ2V0VFN0eWxpbmdSYW5nZVByZXYodERhdGFbdG1wbEhlYWQgKyAxXSBhcyBUU3R5bGluZ1JhbmdlKTtcbiAgICAgIHREYXRhW2luZGV4ICsgMV0gPSB0b1RTdHlsaW5nUmFuZ2UocHJldmlvdXNOb2RlLCB0bXBsSGVhZCk7XG4gICAgICAvLyBpZiBhIGhvc3QgYmluZGluZyBoYXMgYWxyZWFkeSBiZWVuIHJlZ2lzdGVyZWQsIHdlIG5lZWQgdG8gdXBkYXRlIHRoZSBuZXh0IG9mIHRoYXQgaG9zdFxuICAgICAgLy8gYmluZGluZyB0byBwb2ludCB0byB0aGlzIG9uZVxuICAgICAgaWYgKHByZXZpb3VzTm9kZSAhPT0gMCkge1xuICAgICAgICAvLyBXZSBuZWVkIHRvIHVwZGF0ZSB0aGUgdGVtcGxhdGUtdGFpbCB2YWx1ZSB0byBwb2ludCB0byB1cy5cbiAgICAgICAgdERhdGFbcHJldmlvdXNOb2RlICsgMV0gPVxuICAgICAgICAgICAgc2V0VFN0eWxpbmdSYW5nZU5leHQodERhdGFbcHJldmlvdXNOb2RlICsgMV0gYXMgVFN0eWxpbmdSYW5nZSwgaW5kZXgpO1xuICAgICAgfVxuICAgICAgLy8gVGhlIFwicHJldmlvdXNcIiBvZiB0aGUgdGVtcGxhdGUgYmluZGluZyBoZWFkIHNob3VsZCBwb2ludCB0byB0aGlzIGhvc3QgYmluZGluZ1xuICAgICAgdERhdGFbdG1wbEhlYWQgKyAxXSA9IHNldFRTdHlsaW5nUmFuZ2VQcmV2KHREYXRhW3RtcGxIZWFkICsgMV0gYXMgVFN0eWxpbmdSYW5nZSwgaW5kZXgpO1xuICAgIH0gZWxzZSB7XG4gICAgICB0RGF0YVtpbmRleCArIDFdID0gdG9UU3R5bGluZ1JhbmdlKHRtcGxIZWFkLCAwKTtcbiAgICAgIC8vIGlmIGEgaG9zdCBiaW5kaW5nIGhhcyBhbHJlYWR5IGJlZW4gcmVnaXN0ZXJlZCwgd2UgbmVlZCB0byB1cGRhdGUgdGhlIG5leHQgb2YgdGhhdCBob3N0XG4gICAgICAvLyBiaW5kaW5nIHRvIHBvaW50IHRvIHRoaXMgb25lXG4gICAgICBpZiAodG1wbEhlYWQgIT09IDApIHtcbiAgICAgICAgLy8gV2UgbmVlZCB0byB1cGRhdGUgdGhlIHRlbXBsYXRlLXRhaWwgdmFsdWUgdG8gcG9pbnQgdG8gdXMuXG4gICAgICAgIHREYXRhW3RtcGxIZWFkICsgMV0gPSBzZXRUU3R5bGluZ1JhbmdlTmV4dCh0RGF0YVt0bXBsSGVhZCArIDFdIGFzIFRTdHlsaW5nUmFuZ2UsIGluZGV4KTtcbiAgICAgIH1cbiAgICAgIC8vIGlmIHdlIGRvbid0IGhhdmUgdGVtcGxhdGUsIHRoZSBoZWFkIHBvaW50cyB0byB0ZW1wbGF0ZS10YWlsLCBhbmQgbmVlZHMgdG8gYmUgYWR2YW5jZWQuXG4gICAgICB0bXBsSGVhZCA9IGluZGV4O1xuICAgIH1cbiAgfSBlbHNlIHtcbiAgICAvLyBXZSBhcmUgaW5zZXJ0aW5nIGluIHRlbXBsYXRlIHNlY3Rpb24uXG4gICAgLy8gV2UgbmVlZCB0byBzZXQgdGhpcyBiaW5kaW5nJ3MgXCJwcmV2aW91c1wiIHRvIHRoZSBjdXJyZW50IHRlbXBsYXRlIHRhaWxcbiAgICB0RGF0YVtpbmRleCArIDFdID0gdG9UU3R5bGluZ1JhbmdlKHRtcGxUYWlsLCAwKTtcbiAgICBuZ0Rldk1vZGUgJiZcbiAgICAgICAgYXNzZXJ0RXF1YWwoXG4gICAgICAgICAgICB0bXBsSGVhZCAhPT0gMCAmJiB0bXBsVGFpbCA9PT0gMCwgZmFsc2UsXG4gICAgICAgICAgICAnQWRkaW5nIHRlbXBsYXRlIGJpbmRpbmdzIGFmdGVyIGhvc3RCaW5kaW5ncyBpcyBub3QgYWxsb3dlZC4nKTtcbiAgICBpZiAodG1wbEhlYWQgPT09IDApIHtcbiAgICAgIHRtcGxIZWFkID0gaW5kZXg7XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vIFdlIG5lZWQgdG8gdXBkYXRlIHRoZSBwcmV2aW91cyB2YWx1ZSBcIm5leHRcIiB0byBwb2ludCB0byB0aGlzIGJpbmRpbmdcbiAgICAgIHREYXRhW3RtcGxUYWlsICsgMV0gPSBzZXRUU3R5bGluZ1JhbmdlTmV4dCh0RGF0YVt0bXBsVGFpbCArIDFdIGFzIFRTdHlsaW5nUmFuZ2UsIGluZGV4KTtcbiAgICB9XG4gICAgdG1wbFRhaWwgPSBpbmRleDtcbiAgfVxuXG4gIC8vIE5vdyB3ZSBuZWVkIHRvIHVwZGF0ZSAvIGNvbXB1dGUgdGhlIGR1cGxpY2F0ZXMuXG4gIC8vIFN0YXJ0aW5nIHdpdGggb3VyIGxvY2F0aW9uIHNlYXJjaCB0b3dhcmRzIGhlYWQgKGxlYXN0IHByaW9yaXR5KVxuICBpZiAoaXNLZXlEdXBsaWNhdGVPZlN0YXRpYykge1xuICAgIHREYXRhW2luZGV4ICsgMV0gPSBzZXRUU3R5bGluZ1JhbmdlUHJldkR1cGxpY2F0ZSh0RGF0YVtpbmRleCArIDFdIGFzIFRTdHlsaW5nUmFuZ2UpO1xuICB9XG4gIG1hcmtEdXBsaWNhdGVzKHREYXRhLCB0U3R5bGluZ0tleSwgaW5kZXgsIHRydWUsIGlzQ2xhc3NCaW5kaW5nKTtcbiAgbWFya0R1cGxpY2F0ZXModERhdGEsIHRTdHlsaW5nS2V5LCBpbmRleCwgZmFsc2UsIGlzQ2xhc3NCaW5kaW5nKTtcbiAgbWFya0R1cGxpY2F0ZU9mUmVzaWR1YWxTdHlsaW5nKHROb2RlLCB0U3R5bGluZ0tleSwgdERhdGEsIGluZGV4LCBpc0NsYXNzQmluZGluZyk7XG5cbiAgdEJpbmRpbmdzID0gdG9UU3R5bGluZ1JhbmdlKHRtcGxIZWFkLCB0bXBsVGFpbCk7XG4gIGlmIChpc0NsYXNzQmluZGluZykge1xuICAgIHROb2RlLmNsYXNzQmluZGluZ3MgPSB0QmluZGluZ3M7XG4gIH0gZWxzZSB7XG4gICAgdE5vZGUuc3R5bGVCaW5kaW5ncyA9IHRCaW5kaW5ncztcbiAgfVxufVxuXG4vKipcbiAqIExvb2sgaW50byB0aGUgcmVzaWR1YWwgc3R5bGluZyB0byBzZWUgaWYgdGhlIGN1cnJlbnQgYHRTdHlsaW5nS2V5YCBpcyBkdXBsaWNhdGUgb2YgcmVzaWR1YWwuXG4gKlxuICogQHBhcmFtIHROb2RlIGBUTm9kZWAgd2hlcmUgdGhlIHJlc2lkdWFsIGlzIHN0b3JlZC5cbiAqIEBwYXJhbSB0U3R5bGluZ0tleSBgVFN0eWxpbmdLZXlgIHRvIHN0b3JlLlxuICogQHBhcmFtIHREYXRhIGBURGF0YWAgYXNzb2NpYXRlZCB3aXRoIHRoZSBjdXJyZW50IGBMVmlld2AuXG4gKiBAcGFyYW0gaW5kZXggbG9jYXRpb24gb2Ygd2hlcmUgYHRTdHlsZVZhbHVlYCBzaG91bGQgYmUgc3RvcmVkIChhbmQgbGlua2VkIGludG8gbGlzdC4pXG4gKiBAcGFyYW0gaXNDbGFzc0JpbmRpbmcgVHJ1ZSBpZiB0aGUgYXNzb2NpYXRlZCBgdFN0eWxpbmdLZXlgIGFzIGEgYGNsYXNzYCBzdHlsaW5nLlxuICogICAgICAgICAgICAgICAgICAgICAgIGB0Tm9kZS5jbGFzc0JpbmRpbmdzYCBzaG91bGQgYmUgdXNlZCAob3IgYHROb2RlLnN0eWxlQmluZGluZ3NgIG90aGVyd2lzZS4pXG4gKi9cbmZ1bmN0aW9uIG1hcmtEdXBsaWNhdGVPZlJlc2lkdWFsU3R5bGluZyhcbiAgICB0Tm9kZTogVE5vZGUsIHRTdHlsaW5nS2V5OiBUU3R5bGluZ0tleSwgdERhdGE6IFREYXRhLCBpbmRleDogbnVtYmVyLCBpc0NsYXNzQmluZGluZzogYm9vbGVhbikge1xuICBjb25zdCByZXNpZHVhbCA9IGlzQ2xhc3NCaW5kaW5nID8gdE5vZGUucmVzaWR1YWxDbGFzc2VzIDogdE5vZGUucmVzaWR1YWxTdHlsZXM7XG4gIGlmIChyZXNpZHVhbCAhPSBudWxsIC8qIG9yIHVuZGVmaW5lZCAqLyAmJiB0eXBlb2YgdFN0eWxpbmdLZXkgPT0gJ3N0cmluZycgJiZcbiAgICAgIGtleVZhbHVlQXJyYXlJbmRleE9mKHJlc2lkdWFsLCB0U3R5bGluZ0tleSkgPj0gMCkge1xuICAgIC8vIFdlIGhhdmUgZHVwbGljYXRlIGluIHRoZSByZXNpZHVhbCBzbyBtYXJrIG91cnNlbHZlcyBhcyBkdXBsaWNhdGUuXG4gICAgdERhdGFbaW5kZXggKyAxXSA9IHNldFRTdHlsaW5nUmFuZ2VOZXh0RHVwbGljYXRlKHREYXRhW2luZGV4ICsgMV0gYXMgVFN0eWxpbmdSYW5nZSk7XG4gIH1cbn1cblxuXG4vKipcbiAqIE1hcmtzIGBUU3R5bGVWYWx1ZWBzIGFzIGR1cGxpY2F0ZXMgaWYgYW5vdGhlciBzdHlsZSBiaW5kaW5nIGluIHRoZSBsaXN0IGhhcyB0aGUgc2FtZVxuICogYFRTdHlsZVZhbHVlYC5cbiAqXG4gKiBOT1RFOiB0aGlzIGZ1bmN0aW9uIGlzIGludGVuZGVkIHRvIGJlIGNhbGxlZCB0d2ljZSBvbmNlIHdpdGggYGlzUHJldkRpcmAgc2V0IHRvIGB0cnVlYCBhbmQgb25jZVxuICogd2l0aCBpdCBzZXQgdG8gYGZhbHNlYCB0byBzZWFyY2ggYm90aCB0aGUgcHJldmlvdXMgYXMgd2VsbCBhcyBuZXh0IGl0ZW1zIGluIHRoZSBsaXN0LlxuICpcbiAqIE5vIGR1cGxpY2F0ZSBjYXNlXG4gKiBgYGBcbiAqICAgW3N0eWxlLmNvbG9yXVxuICogICBbc3R5bGUud2lkdGgucHhdIDw8LSBpbmRleFxuICogICBbc3R5bGUuaGVpZ2h0LnB4XVxuICogYGBgXG4gKlxuICogSW4gdGhlIGFib3ZlIGNhc2UgYWRkaW5nIGBbc3R5bGUud2lkdGgucHhdYCB0byB0aGUgZXhpc3RpbmcgYFtzdHlsZS5jb2xvcl1gIHByb2R1Y2VzIG5vXG4gKiBkdXBsaWNhdGVzIGJlY2F1c2UgYHdpZHRoYCBpcyBub3QgZm91bmQgaW4gYW55IG90aGVyIHBhcnQgb2YgdGhlIGxpbmtlZCBsaXN0LlxuICpcbiAqIER1cGxpY2F0ZSBjYXNlXG4gKiBgYGBcbiAqICAgW3N0eWxlLmNvbG9yXVxuICogICBbc3R5bGUud2lkdGguZW1dXG4gKiAgIFtzdHlsZS53aWR0aC5weF0gPDwtIGluZGV4XG4gKiBgYGBcbiAqIEluIHRoZSBhYm92ZSBjYXNlIGFkZGluZyBgW3N0eWxlLndpZHRoLnB4XWAgd2lsbCBwcm9kdWNlIGEgZHVwbGljYXRlIHdpdGggYFtzdHlsZS53aWR0aC5lbV1gXG4gKiBiZWNhdXNlIGB3aWR0aGAgaXMgZm91bmQgaW4gdGhlIGNoYWluLlxuICpcbiAqIE1hcCBjYXNlIDFcbiAqIGBgYFxuICogICBbc3R5bGUud2lkdGgucHhdXG4gKiAgIFtzdHlsZS5jb2xvcl1cbiAqICAgW3N0eWxlXSAgPDwtIGluZGV4XG4gKiBgYGBcbiAqIEluIHRoZSBhYm92ZSBjYXNlIGFkZGluZyBgW3N0eWxlXWAgd2lsbCBwcm9kdWNlIGEgZHVwbGljYXRlIHdpdGggYW55IG90aGVyIGJpbmRpbmdzIGJlY2F1c2VcbiAqIGBbc3R5bGVdYCBpcyBhIE1hcCBhbmQgYXMgc3VjaCBpcyBmdWxseSBkeW5hbWljIGFuZCBjb3VsZCBwcm9kdWNlIGBjb2xvcmAgb3IgYHdpZHRoYC5cbiAqXG4gKiBNYXAgY2FzZSAyXG4gKiBgYGBcbiAqICAgW3N0eWxlXVxuICogICBbc3R5bGUud2lkdGgucHhdXG4gKiAgIFtzdHlsZS5jb2xvcl0gIDw8LSBpbmRleFxuICogYGBgXG4gKiBJbiB0aGUgYWJvdmUgY2FzZSBhZGRpbmcgYFtzdHlsZS5jb2xvcl1gIHdpbGwgcHJvZHVjZSBhIGR1cGxpY2F0ZSBiZWNhdXNlIHRoZXJlIGlzIGFscmVhZHkgYVxuICogYFtzdHlsZV1gIGJpbmRpbmcgd2hpY2ggaXMgYSBNYXAgYW5kIGFzIHN1Y2ggaXMgZnVsbHkgZHluYW1pYyBhbmQgY291bGQgcHJvZHVjZSBgY29sb3JgIG9yXG4gKiBgd2lkdGhgLlxuICpcbiAqIE5PVEU6IE9uY2UgYFtzdHlsZV1gIChNYXApIGlzIGFkZGVkIGludG8gdGhlIHN5c3RlbSBhbGwgdGhpbmdzIGFyZSBtYXBwZWQgYXMgZHVwbGljYXRlcy5cbiAqIE5PVEU6IFdlIHVzZSBgc3R5bGVgIGFzIGV4YW1wbGUsIGJ1dCBzYW1lIGxvZ2ljIGlzIGFwcGxpZWQgdG8gYGNsYXNzYGVzIGFzIHdlbGwuXG4gKlxuICogQHBhcmFtIHREYXRhIGBURGF0YWAgd2hlcmUgdGhlIGxpbmtlZCBsaXN0IGlzIHN0b3JlZC5cbiAqIEBwYXJhbSB0U3R5bGluZ0tleSBgVFN0eWxpbmdLZXlQcmltaXRpdmVgIHdoaWNoIGNvbnRhaW5zIHRoZSB2YWx1ZSB0byBjb21wYXJlIHRvIG90aGVyIGtleXMgaW5cbiAqICAgICAgICB0aGUgbGlua2VkIGxpc3QuXG4gKiBAcGFyYW0gaW5kZXggU3RhcnRpbmcgbG9jYXRpb24gaW4gdGhlIGxpbmtlZCBsaXN0IHRvIHNlYXJjaCBmcm9tXG4gKiBAcGFyYW0gaXNQcmV2RGlyIERpcmVjdGlvbi5cbiAqICAgICAgICAtIGB0cnVlYCBmb3IgcHJldmlvdXMgKGxvd2VyIHByaW9yaXR5KTtcbiAqICAgICAgICAtIGBmYWxzZWAgZm9yIG5leHQgKGhpZ2hlciBwcmlvcml0eSkuXG4gKi9cbmZ1bmN0aW9uIG1hcmtEdXBsaWNhdGVzKFxuICAgIHREYXRhOiBURGF0YSwgdFN0eWxpbmdLZXk6IFRTdHlsaW5nS2V5UHJpbWl0aXZlLCBpbmRleDogbnVtYmVyLCBpc1ByZXZEaXI6IGJvb2xlYW4sXG4gICAgaXNDbGFzc0JpbmRpbmc6IGJvb2xlYW4pIHtcbiAgY29uc3QgdFN0eWxpbmdBdEluZGV4ID0gdERhdGFbaW5kZXggKyAxXSBhcyBUU3R5bGluZ1JhbmdlO1xuICBjb25zdCBpc01hcCA9IHRTdHlsaW5nS2V5ID09PSBudWxsO1xuICBsZXQgY3Vyc29yID1cbiAgICAgIGlzUHJldkRpciA/IGdldFRTdHlsaW5nUmFuZ2VQcmV2KHRTdHlsaW5nQXRJbmRleCkgOiBnZXRUU3R5bGluZ1JhbmdlTmV4dCh0U3R5bGluZ0F0SW5kZXgpO1xuICBsZXQgZm91bmREdXBsaWNhdGUgPSBmYWxzZTtcbiAgLy8gV2Uga2VlcCBpdGVyYXRpbmcgYXMgbG9uZyBhcyB3ZSBoYXZlIGEgY3Vyc29yXG4gIC8vIEFORCBlaXRoZXI6XG4gIC8vIC0gd2UgZm91bmQgd2hhdCB3ZSBhcmUgbG9va2luZyBmb3IsIE9SXG4gIC8vIC0gd2UgYXJlIGEgbWFwIGluIHdoaWNoIGNhc2Ugd2UgaGF2ZSB0byBjb250aW51ZSBzZWFyY2hpbmcgZXZlbiBhZnRlciB3ZSBmaW5kIHdoYXQgd2Ugd2VyZVxuICAvLyAgIGxvb2tpbmcgZm9yIHNpbmNlIHdlIGFyZSBhIHdpbGQgY2FyZCBhbmQgZXZlcnl0aGluZyBuZWVkcyB0byBiZSBmbGlwcGVkIHRvIGR1cGxpY2F0ZS5cbiAgd2hpbGUgKGN1cnNvciAhPT0gMCAmJiAoZm91bmREdXBsaWNhdGUgPT09IGZhbHNlIHx8IGlzTWFwKSkge1xuICAgIG5nRGV2TW9kZSAmJiBhc3NlcnRJbmRleEluUmFuZ2UodERhdGEsIGN1cnNvcik7XG4gICAgY29uc3QgdFN0eWxpbmdWYWx1ZUF0Q3Vyc29yID0gdERhdGFbY3Vyc29yXSBhcyBUU3R5bGluZ0tleTtcbiAgICBjb25zdCB0U3R5bGVSYW5nZUF0Q3Vyc29yID0gdERhdGFbY3Vyc29yICsgMV0gYXMgVFN0eWxpbmdSYW5nZTtcbiAgICBpZiAoaXNTdHlsaW5nTWF0Y2godFN0eWxpbmdWYWx1ZUF0Q3Vyc29yLCB0U3R5bGluZ0tleSkpIHtcbiAgICAgIGZvdW5kRHVwbGljYXRlID0gdHJ1ZTtcbiAgICAgIHREYXRhW2N1cnNvciArIDFdID0gaXNQcmV2RGlyID8gc2V0VFN0eWxpbmdSYW5nZU5leHREdXBsaWNhdGUodFN0eWxlUmFuZ2VBdEN1cnNvcikgOlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZXRUU3R5bGluZ1JhbmdlUHJldkR1cGxpY2F0ZSh0U3R5bGVSYW5nZUF0Q3Vyc29yKTtcbiAgICB9XG4gICAgY3Vyc29yID0gaXNQcmV2RGlyID8gZ2V0VFN0eWxpbmdSYW5nZVByZXYodFN0eWxlUmFuZ2VBdEN1cnNvcikgOlxuICAgICAgICAgICAgICAgICAgICAgICAgIGdldFRTdHlsaW5nUmFuZ2VOZXh0KHRTdHlsZVJhbmdlQXRDdXJzb3IpO1xuICB9XG4gIGlmIChmb3VuZER1cGxpY2F0ZSkge1xuICAgIC8vIGlmIHdlIGZvdW5kIGEgZHVwbGljYXRlLCB0aGFuIG1hcmsgb3Vyc2VsdmVzLlxuICAgIHREYXRhW2luZGV4ICsgMV0gPSBpc1ByZXZEaXIgPyBzZXRUU3R5bGluZ1JhbmdlUHJldkR1cGxpY2F0ZSh0U3R5bGluZ0F0SW5kZXgpIDpcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2V0VFN0eWxpbmdSYW5nZU5leHREdXBsaWNhdGUodFN0eWxpbmdBdEluZGV4KTtcbiAgfVxufVxuXG4vKipcbiAqIERldGVybWluZXMgaWYgdHdvIGBUU3R5bGluZ0tleWBzIGFyZSBhIG1hdGNoLlxuICpcbiAqIFdoZW4gY29tcHV0aW5nIHdoZXRoZXIgYSBiaW5kaW5nIGNvbnRhaW5zIGEgZHVwbGljYXRlLCB3ZSBuZWVkIHRvIGNvbXBhcmUgaWYgdGhlIGluc3RydWN0aW9uXG4gKiBgVFN0eWxpbmdLZXlgIGhhcyBhIG1hdGNoLlxuICpcbiAqIEhlcmUgYXJlIGV4YW1wbGVzIG9mIGBUU3R5bGluZ0tleWBzIHdoaWNoIG1hdGNoIGdpdmVuIGB0U3R5bGluZ0tleUN1cnNvcmAgaXM6XG4gKiAtIGBjb2xvcmBcbiAqICAgIC0gYGNvbG9yYCAgICAvLyBNYXRjaCBhbm90aGVyIGNvbG9yXG4gKiAgICAtIGBudWxsYCAgICAgLy8gVGhhdCBtZWFucyB0aGF0IGB0U3R5bGluZ0tleWAgaXMgYSBgY2xhc3NNYXBgL2BzdHlsZU1hcGAgaW5zdHJ1Y3Rpb25cbiAqICAgIC0gYFsnJywgJ2NvbG9yJywgJ290aGVyJywgdHJ1ZV1gIC8vIHdyYXBwZWQgYGNvbG9yYCBzbyBtYXRjaFxuICogICAgLSBgWycnLCBudWxsLCAnb3RoZXInLCB0cnVlXWAgICAgICAgLy8gd3JhcHBlZCBgbnVsbGAgc28gbWF0Y2hcbiAqICAgIC0gYFsnJywgJ3dpZHRoJywgJ2NvbG9yJywgJ3ZhbHVlJ11gIC8vIHdyYXBwZWQgc3RhdGljIHZhbHVlIGNvbnRhaW5zIGEgbWF0Y2ggb24gYCdjb2xvcidgXG4gKiAtIGBudWxsYCAgICAgICAvLyBgdFN0eWxpbmdLZXlDdXJzb3JgIGFsd2F5cyBtYXRjaCBhcyBpdCBpcyBgY2xhc3NNYXBgL2BzdHlsZU1hcGAgaW5zdHJ1Y3Rpb25cbiAqXG4gKiBAcGFyYW0gdFN0eWxpbmdLZXlDdXJzb3JcbiAqIEBwYXJhbSB0U3R5bGluZ0tleVxuICovXG5mdW5jdGlvbiBpc1N0eWxpbmdNYXRjaCh0U3R5bGluZ0tleUN1cnNvcjogVFN0eWxpbmdLZXksIHRTdHlsaW5nS2V5OiBUU3R5bGluZ0tleVByaW1pdGl2ZSkge1xuICBuZ0Rldk1vZGUgJiZcbiAgICAgIGFzc2VydE5vdEVxdWFsKFxuICAgICAgICAgIEFycmF5LmlzQXJyYXkodFN0eWxpbmdLZXkpLCB0cnVlLCAnRXhwZWN0ZWQgdGhhdCBcXCd0U3R5bGluZ0tleVxcJyBoYXMgYmVlbiB1bndyYXBwZWQnKTtcbiAgaWYgKFxuICAgICAgdFN0eWxpbmdLZXlDdXJzb3IgPT09IG51bGwgfHwgIC8vIElmIHRoZSBjdXJzb3IgaXMgYG51bGxgIGl0IG1lYW5zIHRoYXQgd2UgaGF2ZSBtYXAgYXQgdGhhdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIGxvY2F0aW9uIHNvIHdlIG11c3QgYXNzdW1lIHRoYXQgd2UgaGF2ZSBhIG1hdGNoLlxuICAgICAgdFN0eWxpbmdLZXkgPT0gbnVsbCB8fCAgLy8gSWYgYHRTdHlsaW5nS2V5YCBpcyBgbnVsbGAgdGhlbiBpdCBpcyBhIG1hcCB0aGVyZWZvciBhc3N1bWUgdGhhdCBpdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLy8gY29udGFpbnMgYSBtYXRjaC5cbiAgICAgIChBcnJheS5pc0FycmF5KHRTdHlsaW5nS2V5Q3Vyc29yKSA/IHRTdHlsaW5nS2V5Q3Vyc29yWzFdIDogdFN0eWxpbmdLZXlDdXJzb3IpID09PVxuICAgICAgICAgIHRTdHlsaW5nS2V5ICAvLyBJZiB0aGUga2V5cyBtYXRjaCBleHBsaWNpdGx5IHRoYW4gd2UgYXJlIGEgbWF0Y2guXG4gICkge1xuICAgIHJldHVybiB0cnVlO1xuICB9IGVsc2UgaWYgKEFycmF5LmlzQXJyYXkodFN0eWxpbmdLZXlDdXJzb3IpICYmIHR5cGVvZiB0U3R5bGluZ0tleSA9PT0gJ3N0cmluZycpIHtcbiAgICAvLyBpZiB3ZSBkaWQgbm90IGZpbmQgYSBtYXRjaCwgYnV0IGB0U3R5bGluZ0tleUN1cnNvcmAgaXMgYEtleVZhbHVlQXJyYXlgIHRoYXQgbWVhbnMgY3Vyc29yIGhhc1xuICAgIC8vIHN0YXRpY3MgYW5kIHdlIG5lZWQgdG8gY2hlY2sgdGhvc2UgYXMgd2VsbC5cbiAgICByZXR1cm4ga2V5VmFsdWVBcnJheUluZGV4T2YodFN0eWxpbmdLZXlDdXJzb3IsIHRTdHlsaW5nS2V5KSA+PVxuICAgICAgICAwOyAgLy8gc2VlIGlmIHdlIGFyZSBtYXRjaGluZyB0aGUga2V5XG4gIH1cbiAgcmV0dXJuIGZhbHNlO1xufVxuIl19