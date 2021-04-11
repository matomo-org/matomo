/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertEqual } from '../../util/assert';
import { setI18nHandling } from '../node_manipulation';
import { getInsertInFrontOfRNodeWithI18n, processI18nInsertBefore } from '../node_manipulation_i18n';
/**
 * Add `tNode` to `previousTNodes` list and update relevant `TNode`s in `previousTNodes` list
 * `tNode.insertBeforeIndex`.
 *
 * Things to keep in mind:
 * 1. All i18n text nodes are encoded as `TNodeType.Element` and are created eagerly by the
 *    `ɵɵi18nStart` instruction.
 * 2. All `TNodeType.Placeholder` `TNodes` are elements which will be created later by
 *    `ɵɵelementStart` instruction.
 * 3. `ɵɵelementStart` instruction will create `TNode`s in the ascending `TNode.index` order. (So a
 *    smaller index `TNode` is guaranteed to be created before a larger one)
 *
 * We use the above three invariants to determine `TNode.insertBeforeIndex`.
 *
 * In an ideal world `TNode.insertBeforeIndex` would always be `TNode.next.index`. However,
 * this will not work because `TNode.next.index` may be larger than `TNode.index` which means that
 * the next node is not yet created and therefore we can't insert in front of it.
 *
 * Rule1: `TNode.insertBeforeIndex = null` if `TNode.next === null` (Initial condition, as we don't
 *        know if there will be further `TNode`s inserted after.)
 * Rule2: If `previousTNode` is created after the `tNode` being inserted, then
 *        `previousTNode.insertBeforeNode = tNode.index` (So when a new `tNode` is added we check
 *        previous to see if we can update its `insertBeforeTNode`)
 *
 * See `TNode.insertBeforeIndex` for more context.
 *
 * @param previousTNodes A list of previous TNodes so that we can easily traverse `TNode`s in
 *     reverse order. (If `TNode` would have `previous` this would not be necessary.)
 * @param newTNode A TNode to add to the `previousTNodes` list.
 */
export function addTNodeAndUpdateInsertBeforeIndex(previousTNodes, newTNode) {
    // Start with Rule1
    ngDevMode &&
        assertEqual(newTNode.insertBeforeIndex, null, 'We expect that insertBeforeIndex is not set');
    previousTNodes.push(newTNode);
    if (previousTNodes.length > 1) {
        for (let i = previousTNodes.length - 2; i >= 0; i--) {
            const existingTNode = previousTNodes[i];
            // Text nodes are created eagerly and so they don't need their `indexBeforeIndex` updated.
            // It is safe to ignore them.
            if (!isI18nText(existingTNode)) {
                if (isNewTNodeCreatedBefore(existingTNode, newTNode) &&
                    getInsertBeforeIndex(existingTNode) === null) {
                    // If it was created before us in time, (and it does not yet have `insertBeforeIndex`)
                    // then add the `insertBeforeIndex`.
                    setInsertBeforeIndex(existingTNode, newTNode.index);
                }
            }
        }
    }
}
function isI18nText(tNode) {
    return !(tNode.type & 64 /* Placeholder */);
}
function isNewTNodeCreatedBefore(existingTNode, newTNode) {
    return isI18nText(newTNode) || existingTNode.index > newTNode.index;
}
function getInsertBeforeIndex(tNode) {
    const index = tNode.insertBeforeIndex;
    return Array.isArray(index) ? index[0] : index;
}
function setInsertBeforeIndex(tNode, value) {
    const index = tNode.insertBeforeIndex;
    if (Array.isArray(index)) {
        // Array is stored if we have to insert child nodes. See `TNode.insertBeforeIndex`
        index[0] = value;
    }
    else {
        setI18nHandling(getInsertInFrontOfRNodeWithI18n, processI18nInsertBefore);
        tNode.insertBeforeIndex = value;
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaTE4bl9pbnNlcnRfYmVmb3JlX2luZGV4LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zcmMvcmVuZGVyMy9pMThuL2kxOG5faW5zZXJ0X2JlZm9yZV9pbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLEVBQUMsV0FBVyxFQUFDLE1BQU0sbUJBQW1CLENBQUM7QUFFOUMsT0FBTyxFQUFDLGVBQWUsRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBQ3JELE9BQU8sRUFBQywrQkFBK0IsRUFBRSx1QkFBdUIsRUFBQyxNQUFNLDJCQUEyQixDQUFDO0FBRW5HOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQTZCRztBQUNILE1BQU0sVUFBVSxrQ0FBa0MsQ0FBQyxjQUF1QixFQUFFLFFBQWU7SUFDekYsbUJBQW1CO0lBQ25CLFNBQVM7UUFDTCxXQUFXLENBQUMsUUFBUSxDQUFDLGlCQUFpQixFQUFFLElBQUksRUFBRSw2Q0FBNkMsQ0FBQyxDQUFDO0lBRWpHLGNBQWMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7SUFDOUIsSUFBSSxjQUFjLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtRQUM3QixLQUFLLElBQUksQ0FBQyxHQUFHLGNBQWMsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLEVBQUU7WUFDbkQsTUFBTSxhQUFhLEdBQUcsY0FBYyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQ3hDLDBGQUEwRjtZQUMxRiw2QkFBNkI7WUFDN0IsSUFBSSxDQUFDLFVBQVUsQ0FBQyxhQUFhLENBQUMsRUFBRTtnQkFDOUIsSUFBSSx1QkFBdUIsQ0FBQyxhQUFhLEVBQUUsUUFBUSxDQUFDO29CQUNoRCxvQkFBb0IsQ0FBQyxhQUFhLENBQUMsS0FBSyxJQUFJLEVBQUU7b0JBQ2hELHNGQUFzRjtvQkFDdEYsb0NBQW9DO29CQUNwQyxvQkFBb0IsQ0FBQyxhQUFhLEVBQUUsUUFBUSxDQUFDLEtBQUssQ0FBQyxDQUFDO2lCQUNyRDthQUNGO1NBQ0Y7S0FDRjtBQUNILENBQUM7QUFFRCxTQUFTLFVBQVUsQ0FBQyxLQUFZO0lBQzlCLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLHVCQUF3QixDQUFDLENBQUM7QUFDL0MsQ0FBQztBQUVELFNBQVMsdUJBQXVCLENBQUMsYUFBb0IsRUFBRSxRQUFlO0lBQ3BFLE9BQU8sVUFBVSxDQUFDLFFBQVEsQ0FBQyxJQUFJLGFBQWEsQ0FBQyxLQUFLLEdBQUcsUUFBUSxDQUFDLEtBQUssQ0FBQztBQUN0RSxDQUFDO0FBRUQsU0FBUyxvQkFBb0IsQ0FBQyxLQUFZO0lBQ3hDLE1BQU0sS0FBSyxHQUFHLEtBQUssQ0FBQyxpQkFBaUIsQ0FBQztJQUN0QyxPQUFPLEtBQUssQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDO0FBQ2pELENBQUM7QUFFRCxTQUFTLG9CQUFvQixDQUFDLEtBQVksRUFBRSxLQUFhO0lBQ3ZELE1BQU0sS0FBSyxHQUFHLEtBQUssQ0FBQyxpQkFBaUIsQ0FBQztJQUN0QyxJQUFJLEtBQUssQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEVBQUU7UUFDeEIsa0ZBQWtGO1FBQ2xGLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBRyxLQUFLLENBQUM7S0FDbEI7U0FBTTtRQUNMLGVBQWUsQ0FBQywrQkFBK0IsRUFBRSx1QkFBdUIsQ0FBQyxDQUFDO1FBQzFFLEtBQUssQ0FBQyxpQkFBaUIsR0FBRyxLQUFLLENBQUM7S0FDakM7QUFDSCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7YXNzZXJ0RXF1YWx9IGZyb20gJy4uLy4uL3V0aWwvYXNzZXJ0JztcbmltcG9ydCB7VE5vZGUsIFROb2RlVHlwZX0gZnJvbSAnLi4vaW50ZXJmYWNlcy9ub2RlJztcbmltcG9ydCB7c2V0STE4bkhhbmRsaW5nfSBmcm9tICcuLi9ub2RlX21hbmlwdWxhdGlvbic7XG5pbXBvcnQge2dldEluc2VydEluRnJvbnRPZlJOb2RlV2l0aEkxOG4sIHByb2Nlc3NJMThuSW5zZXJ0QmVmb3JlfSBmcm9tICcuLi9ub2RlX21hbmlwdWxhdGlvbl9pMThuJztcblxuLyoqXG4gKiBBZGQgYHROb2RlYCB0byBgcHJldmlvdXNUTm9kZXNgIGxpc3QgYW5kIHVwZGF0ZSByZWxldmFudCBgVE5vZGVgcyBpbiBgcHJldmlvdXNUTm9kZXNgIGxpc3RcbiAqIGB0Tm9kZS5pbnNlcnRCZWZvcmVJbmRleGAuXG4gKlxuICogVGhpbmdzIHRvIGtlZXAgaW4gbWluZDpcbiAqIDEuIEFsbCBpMThuIHRleHQgbm9kZXMgYXJlIGVuY29kZWQgYXMgYFROb2RlVHlwZS5FbGVtZW50YCBhbmQgYXJlIGNyZWF0ZWQgZWFnZXJseSBieSB0aGVcbiAqICAgIGDJtcm1aTE4blN0YXJ0YCBpbnN0cnVjdGlvbi5cbiAqIDIuIEFsbCBgVE5vZGVUeXBlLlBsYWNlaG9sZGVyYCBgVE5vZGVzYCBhcmUgZWxlbWVudHMgd2hpY2ggd2lsbCBiZSBjcmVhdGVkIGxhdGVyIGJ5XG4gKiAgICBgybXJtWVsZW1lbnRTdGFydGAgaW5zdHJ1Y3Rpb24uXG4gKiAzLiBgybXJtWVsZW1lbnRTdGFydGAgaW5zdHJ1Y3Rpb24gd2lsbCBjcmVhdGUgYFROb2RlYHMgaW4gdGhlIGFzY2VuZGluZyBgVE5vZGUuaW5kZXhgIG9yZGVyLiAoU28gYVxuICogICAgc21hbGxlciBpbmRleCBgVE5vZGVgIGlzIGd1YXJhbnRlZWQgdG8gYmUgY3JlYXRlZCBiZWZvcmUgYSBsYXJnZXIgb25lKVxuICpcbiAqIFdlIHVzZSB0aGUgYWJvdmUgdGhyZWUgaW52YXJpYW50cyB0byBkZXRlcm1pbmUgYFROb2RlLmluc2VydEJlZm9yZUluZGV4YC5cbiAqXG4gKiBJbiBhbiBpZGVhbCB3b3JsZCBgVE5vZGUuaW5zZXJ0QmVmb3JlSW5kZXhgIHdvdWxkIGFsd2F5cyBiZSBgVE5vZGUubmV4dC5pbmRleGAuIEhvd2V2ZXIsXG4gKiB0aGlzIHdpbGwgbm90IHdvcmsgYmVjYXVzZSBgVE5vZGUubmV4dC5pbmRleGAgbWF5IGJlIGxhcmdlciB0aGFuIGBUTm9kZS5pbmRleGAgd2hpY2ggbWVhbnMgdGhhdFxuICogdGhlIG5leHQgbm9kZSBpcyBub3QgeWV0IGNyZWF0ZWQgYW5kIHRoZXJlZm9yZSB3ZSBjYW4ndCBpbnNlcnQgaW4gZnJvbnQgb2YgaXQuXG4gKlxuICogUnVsZTE6IGBUTm9kZS5pbnNlcnRCZWZvcmVJbmRleCA9IG51bGxgIGlmIGBUTm9kZS5uZXh0ID09PSBudWxsYCAoSW5pdGlhbCBjb25kaXRpb24sIGFzIHdlIGRvbid0XG4gKiAgICAgICAga25vdyBpZiB0aGVyZSB3aWxsIGJlIGZ1cnRoZXIgYFROb2RlYHMgaW5zZXJ0ZWQgYWZ0ZXIuKVxuICogUnVsZTI6IElmIGBwcmV2aW91c1ROb2RlYCBpcyBjcmVhdGVkIGFmdGVyIHRoZSBgdE5vZGVgIGJlaW5nIGluc2VydGVkLCB0aGVuXG4gKiAgICAgICAgYHByZXZpb3VzVE5vZGUuaW5zZXJ0QmVmb3JlTm9kZSA9IHROb2RlLmluZGV4YCAoU28gd2hlbiBhIG5ldyBgdE5vZGVgIGlzIGFkZGVkIHdlIGNoZWNrXG4gKiAgICAgICAgcHJldmlvdXMgdG8gc2VlIGlmIHdlIGNhbiB1cGRhdGUgaXRzIGBpbnNlcnRCZWZvcmVUTm9kZWApXG4gKlxuICogU2VlIGBUTm9kZS5pbnNlcnRCZWZvcmVJbmRleGAgZm9yIG1vcmUgY29udGV4dC5cbiAqXG4gKiBAcGFyYW0gcHJldmlvdXNUTm9kZXMgQSBsaXN0IG9mIHByZXZpb3VzIFROb2RlcyBzbyB0aGF0IHdlIGNhbiBlYXNpbHkgdHJhdmVyc2UgYFROb2RlYHMgaW5cbiAqICAgICByZXZlcnNlIG9yZGVyLiAoSWYgYFROb2RlYCB3b3VsZCBoYXZlIGBwcmV2aW91c2AgdGhpcyB3b3VsZCBub3QgYmUgbmVjZXNzYXJ5LilcbiAqIEBwYXJhbSBuZXdUTm9kZSBBIFROb2RlIHRvIGFkZCB0byB0aGUgYHByZXZpb3VzVE5vZGVzYCBsaXN0LlxuICovXG5leHBvcnQgZnVuY3Rpb24gYWRkVE5vZGVBbmRVcGRhdGVJbnNlcnRCZWZvcmVJbmRleChwcmV2aW91c1ROb2RlczogVE5vZGVbXSwgbmV3VE5vZGU6IFROb2RlKSB7XG4gIC8vIFN0YXJ0IHdpdGggUnVsZTFcbiAgbmdEZXZNb2RlICYmXG4gICAgICBhc3NlcnRFcXVhbChuZXdUTm9kZS5pbnNlcnRCZWZvcmVJbmRleCwgbnVsbCwgJ1dlIGV4cGVjdCB0aGF0IGluc2VydEJlZm9yZUluZGV4IGlzIG5vdCBzZXQnKTtcblxuICBwcmV2aW91c1ROb2Rlcy5wdXNoKG5ld1ROb2RlKTtcbiAgaWYgKHByZXZpb3VzVE5vZGVzLmxlbmd0aCA+IDEpIHtcbiAgICBmb3IgKGxldCBpID0gcHJldmlvdXNUTm9kZXMubGVuZ3RoIC0gMjsgaSA+PSAwOyBpLS0pIHtcbiAgICAgIGNvbnN0IGV4aXN0aW5nVE5vZGUgPSBwcmV2aW91c1ROb2Rlc1tpXTtcbiAgICAgIC8vIFRleHQgbm9kZXMgYXJlIGNyZWF0ZWQgZWFnZXJseSBhbmQgc28gdGhleSBkb24ndCBuZWVkIHRoZWlyIGBpbmRleEJlZm9yZUluZGV4YCB1cGRhdGVkLlxuICAgICAgLy8gSXQgaXMgc2FmZSB0byBpZ25vcmUgdGhlbS5cbiAgICAgIGlmICghaXNJMThuVGV4dChleGlzdGluZ1ROb2RlKSkge1xuICAgICAgICBpZiAoaXNOZXdUTm9kZUNyZWF0ZWRCZWZvcmUoZXhpc3RpbmdUTm9kZSwgbmV3VE5vZGUpICYmXG4gICAgICAgICAgICBnZXRJbnNlcnRCZWZvcmVJbmRleChleGlzdGluZ1ROb2RlKSA9PT0gbnVsbCkge1xuICAgICAgICAgIC8vIElmIGl0IHdhcyBjcmVhdGVkIGJlZm9yZSB1cyBpbiB0aW1lLCAoYW5kIGl0IGRvZXMgbm90IHlldCBoYXZlIGBpbnNlcnRCZWZvcmVJbmRleGApXG4gICAgICAgICAgLy8gdGhlbiBhZGQgdGhlIGBpbnNlcnRCZWZvcmVJbmRleGAuXG4gICAgICAgICAgc2V0SW5zZXJ0QmVmb3JlSW5kZXgoZXhpc3RpbmdUTm9kZSwgbmV3VE5vZGUuaW5kZXgpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG59XG5cbmZ1bmN0aW9uIGlzSTE4blRleHQodE5vZGU6IFROb2RlKTogYm9vbGVhbiB7XG4gIHJldHVybiAhKHROb2RlLnR5cGUgJiBUTm9kZVR5cGUuUGxhY2Vob2xkZXIpO1xufVxuXG5mdW5jdGlvbiBpc05ld1ROb2RlQ3JlYXRlZEJlZm9yZShleGlzdGluZ1ROb2RlOiBUTm9kZSwgbmV3VE5vZGU6IFROb2RlKTogYm9vbGVhbiB7XG4gIHJldHVybiBpc0kxOG5UZXh0KG5ld1ROb2RlKSB8fCBleGlzdGluZ1ROb2RlLmluZGV4ID4gbmV3VE5vZGUuaW5kZXg7XG59XG5cbmZ1bmN0aW9uIGdldEluc2VydEJlZm9yZUluZGV4KHROb2RlOiBUTm9kZSk6IG51bWJlcnxudWxsIHtcbiAgY29uc3QgaW5kZXggPSB0Tm9kZS5pbnNlcnRCZWZvcmVJbmRleDtcbiAgcmV0dXJuIEFycmF5LmlzQXJyYXkoaW5kZXgpID8gaW5kZXhbMF0gOiBpbmRleDtcbn1cblxuZnVuY3Rpb24gc2V0SW5zZXJ0QmVmb3JlSW5kZXgodE5vZGU6IFROb2RlLCB2YWx1ZTogbnVtYmVyKTogdm9pZCB7XG4gIGNvbnN0IGluZGV4ID0gdE5vZGUuaW5zZXJ0QmVmb3JlSW5kZXg7XG4gIGlmIChBcnJheS5pc0FycmF5KGluZGV4KSkge1xuICAgIC8vIEFycmF5IGlzIHN0b3JlZCBpZiB3ZSBoYXZlIHRvIGluc2VydCBjaGlsZCBub2Rlcy4gU2VlIGBUTm9kZS5pbnNlcnRCZWZvcmVJbmRleGBcbiAgICBpbmRleFswXSA9IHZhbHVlO1xuICB9IGVsc2Uge1xuICAgIHNldEkxOG5IYW5kbGluZyhnZXRJbnNlcnRJbkZyb250T2ZSTm9kZVdpdGhJMThuLCBwcm9jZXNzSTE4bkluc2VydEJlZm9yZSk7XG4gICAgdE5vZGUuaW5zZXJ0QmVmb3JlSW5kZXggPSB2YWx1ZTtcbiAgfVxufVxuIl19