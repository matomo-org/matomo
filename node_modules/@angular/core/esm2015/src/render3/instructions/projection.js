/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { newArray } from '../../util/array_utils';
import { DECLARATION_COMPONENT_VIEW, HEADER_OFFSET, T_HOST } from '../interfaces/view';
import { applyProjection } from '../node_manipulation';
import { getProjectAsAttrValue, isNodeMatchingSelectorList, isSelectorInSelectorList } from '../node_selector_matcher';
import { getLView, getTView, setCurrentTNodeAsNotParent } from '../state';
import { getOrCreateTNode } from './shared';
/**
 * Checks a given node against matching projection slots and returns the
 * determined slot index. Returns "null" if no slot matched the given node.
 *
 * This function takes into account the parsed ngProjectAs selector from the
 * node's attributes. If present, it will check whether the ngProjectAs selector
 * matches any of the projection slot selectors.
 */
export function matchingProjectionSlotIndex(tNode, projectionSlots) {
    let wildcardNgContentIndex = null;
    const ngProjectAsAttrVal = getProjectAsAttrValue(tNode);
    for (let i = 0; i < projectionSlots.length; i++) {
        const slotValue = projectionSlots[i];
        // The last wildcard projection slot should match all nodes which aren't matching
        // any selector. This is necessary to be backwards compatible with view engine.
        if (slotValue === '*') {
            wildcardNgContentIndex = i;
            continue;
        }
        // If we ran into an `ngProjectAs` attribute, we should match its parsed selector
        // to the list of selectors, otherwise we fall back to matching against the node.
        if (ngProjectAsAttrVal === null ?
            isNodeMatchingSelectorList(tNode, slotValue, /* isProjectionMode */ true) :
            isSelectorInSelectorList(ngProjectAsAttrVal, slotValue)) {
            return i; // first matching selector "captures" a given node
        }
    }
    return wildcardNgContentIndex;
}
/**
 * Instruction to distribute projectable nodes among <ng-content> occurrences in a given template.
 * It takes all the selectors from the entire component's template and decides where
 * each projected node belongs (it re-distributes nodes among "buckets" where each "bucket" is
 * backed by a selector).
 *
 * This function requires CSS selectors to be provided in 2 forms: parsed (by a compiler) and text,
 * un-parsed form.
 *
 * The parsed form is needed for efficient matching of a node against a given CSS selector.
 * The un-parsed, textual form is needed for support of the ngProjectAs attribute.
 *
 * Having a CSS selector in 2 different formats is not ideal, but alternatives have even more
 * drawbacks:
 * - having only a textual form would require runtime parsing of CSS selectors;
 * - we can't have only a parsed as we can't re-construct textual form from it (as entered by a
 * template author).
 *
 * @param projectionSlots? A collection of projection slots. A projection slot can be based
 *        on a parsed CSS selectors or set to the wildcard selector ("*") in order to match
 *        all nodes which do not match any selector. If not specified, a single wildcard
 *        selector projection slot will be defined.
 *
 * @codeGenApi
 */
export function ɵɵprojectionDef(projectionSlots) {
    const componentNode = getLView()[DECLARATION_COMPONENT_VIEW][T_HOST];
    if (!componentNode.projection) {
        // If no explicit projection slots are defined, fall back to a single
        // projection slot with the wildcard selector.
        const numProjectionSlots = projectionSlots ? projectionSlots.length : 1;
        const projectionHeads = componentNode.projection =
            newArray(numProjectionSlots, null);
        const tails = projectionHeads.slice();
        let componentChild = componentNode.child;
        while (componentChild !== null) {
            const slotIndex = projectionSlots ? matchingProjectionSlotIndex(componentChild, projectionSlots) : 0;
            if (slotIndex !== null) {
                if (tails[slotIndex]) {
                    tails[slotIndex].projectionNext = componentChild;
                }
                else {
                    projectionHeads[slotIndex] = componentChild;
                }
                tails[slotIndex] = componentChild;
            }
            componentChild = componentChild.next;
        }
    }
}
/**
 * Inserts previously re-distributed projected nodes. This instruction must be preceded by a call
 * to the projectionDef instruction.
 *
 * @param nodeIndex
 * @param selectorIndex:
 *        - 0 when the selector is `*` (or unspecified as this is the default value),
 *        - 1 based index of the selector from the {@link projectionDef}
 *
 * @codeGenApi
 */
export function ɵɵprojection(nodeIndex, selectorIndex = 0, attrs) {
    const lView = getLView();
    const tView = getTView();
    const tProjectionNode = getOrCreateTNode(tView, HEADER_OFFSET + nodeIndex, 16 /* Projection */, null, attrs || null);
    // We can't use viewData[HOST_NODE] because projection nodes can be nested in embedded views.
    if (tProjectionNode.projection === null)
        tProjectionNode.projection = selectorIndex;
    // `<ng-content>` has no content
    setCurrentTNodeAsNotParent();
    if ((tProjectionNode.flags & 64 /* isDetached */) !== 64 /* isDetached */) {
        // re-distribution of projectable nodes is stored on a component's view level
        applyProjection(tView, lView, tProjectionNode);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicHJvamVjdGlvbi5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3JlbmRlcjMvaW5zdHJ1Y3Rpb25zL3Byb2plY3Rpb24udHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBQ0gsT0FBTyxFQUFDLFFBQVEsRUFBQyxNQUFNLHdCQUF3QixDQUFDO0FBR2hELE9BQU8sRUFBQywwQkFBMEIsRUFBRSxhQUFhLEVBQUUsTUFBTSxFQUFDLE1BQU0sb0JBQW9CLENBQUM7QUFDckYsT0FBTyxFQUFDLGVBQWUsRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBQ3JELE9BQU8sRUFBQyxxQkFBcUIsRUFBRSwwQkFBMEIsRUFBRSx3QkFBd0IsRUFBQyxNQUFNLDBCQUEwQixDQUFDO0FBQ3JILE9BQU8sRUFBQyxRQUFRLEVBQUUsUUFBUSxFQUFFLDBCQUEwQixFQUFDLE1BQU0sVUFBVSxDQUFDO0FBQ3hFLE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLFVBQVUsQ0FBQztBQUkxQzs7Ozs7OztHQU9HO0FBQ0gsTUFBTSxVQUFVLDJCQUEyQixDQUFDLEtBQVksRUFBRSxlQUFnQztJQUV4RixJQUFJLHNCQUFzQixHQUFHLElBQUksQ0FBQztJQUNsQyxNQUFNLGtCQUFrQixHQUFHLHFCQUFxQixDQUFDLEtBQUssQ0FBQyxDQUFDO0lBQ3hELEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxlQUFlLENBQUMsTUFBTSxFQUFFLENBQUMsRUFBRSxFQUFFO1FBQy9DLE1BQU0sU0FBUyxHQUFHLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUNyQyxpRkFBaUY7UUFDakYsK0VBQStFO1FBQy9FLElBQUksU0FBUyxLQUFLLEdBQUcsRUFBRTtZQUNyQixzQkFBc0IsR0FBRyxDQUFDLENBQUM7WUFDM0IsU0FBUztTQUNWO1FBQ0QsaUZBQWlGO1FBQ2pGLGlGQUFpRjtRQUNqRixJQUFJLGtCQUFrQixLQUFLLElBQUksQ0FBQyxDQUFDO1lBQ3pCLDBCQUEwQixDQUFDLEtBQUssRUFBRSxTQUFTLEVBQUUsc0JBQXNCLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztZQUMzRSx3QkFBd0IsQ0FBQyxrQkFBa0IsRUFBRSxTQUFTLENBQUMsRUFBRTtZQUMvRCxPQUFPLENBQUMsQ0FBQyxDQUFFLGtEQUFrRDtTQUM5RDtLQUNGO0lBQ0QsT0FBTyxzQkFBc0IsQ0FBQztBQUNoQyxDQUFDO0FBRUQ7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUNILE1BQU0sVUFBVSxlQUFlLENBQUMsZUFBaUM7SUFDL0QsTUFBTSxhQUFhLEdBQUcsUUFBUSxFQUFFLENBQUMsMEJBQTBCLENBQUMsQ0FBQyxNQUFNLENBQWlCLENBQUM7SUFFckYsSUFBSSxDQUFDLGFBQWEsQ0FBQyxVQUFVLEVBQUU7UUFDN0IscUVBQXFFO1FBQ3JFLDhDQUE4QztRQUM5QyxNQUFNLGtCQUFrQixHQUFHLGVBQWUsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ3hFLE1BQU0sZUFBZSxHQUFtQixhQUFhLENBQUMsVUFBVTtZQUM1RCxRQUFRLENBQUMsa0JBQWtCLEVBQUUsSUFBYyxDQUFDLENBQUM7UUFDakQsTUFBTSxLQUFLLEdBQW1CLGVBQWUsQ0FBQyxLQUFLLEVBQUUsQ0FBQztRQUV0RCxJQUFJLGNBQWMsR0FBZSxhQUFhLENBQUMsS0FBSyxDQUFDO1FBRXJELE9BQU8sY0FBYyxLQUFLLElBQUksRUFBRTtZQUM5QixNQUFNLFNBQVMsR0FDWCxlQUFlLENBQUMsQ0FBQyxDQUFDLDJCQUEyQixDQUFDLGNBQWMsRUFBRSxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBRXZGLElBQUksU0FBUyxLQUFLLElBQUksRUFBRTtnQkFDdEIsSUFBSSxLQUFLLENBQUMsU0FBUyxDQUFDLEVBQUU7b0JBQ3BCLEtBQUssQ0FBQyxTQUFTLENBQUUsQ0FBQyxjQUFjLEdBQUcsY0FBYyxDQUFDO2lCQUNuRDtxQkFBTTtvQkFDTCxlQUFlLENBQUMsU0FBUyxDQUFDLEdBQUcsY0FBYyxDQUFDO2lCQUM3QztnQkFDRCxLQUFLLENBQUMsU0FBUyxDQUFDLEdBQUcsY0FBYyxDQUFDO2FBQ25DO1lBRUQsY0FBYyxHQUFHLGNBQWMsQ0FBQyxJQUFJLENBQUM7U0FDdEM7S0FDRjtBQUNILENBQUM7QUFHRDs7Ozs7Ozs7OztHQVVHO0FBQ0gsTUFBTSxVQUFVLFlBQVksQ0FDeEIsU0FBaUIsRUFBRSxnQkFBd0IsQ0FBQyxFQUFFLEtBQW1CO0lBQ25FLE1BQU0sS0FBSyxHQUFHLFFBQVEsRUFBRSxDQUFDO0lBQ3pCLE1BQU0sS0FBSyxHQUFHLFFBQVEsRUFBRSxDQUFDO0lBQ3pCLE1BQU0sZUFBZSxHQUNqQixnQkFBZ0IsQ0FBQyxLQUFLLEVBQUUsYUFBYSxHQUFHLFNBQVMsdUJBQXdCLElBQUksRUFBRSxLQUFLLElBQUksSUFBSSxDQUFDLENBQUM7SUFFbEcsNkZBQTZGO0lBQzdGLElBQUksZUFBZSxDQUFDLFVBQVUsS0FBSyxJQUFJO1FBQUUsZUFBZSxDQUFDLFVBQVUsR0FBRyxhQUFhLENBQUM7SUFFcEYsZ0NBQWdDO0lBQ2hDLDBCQUEwQixFQUFFLENBQUM7SUFFN0IsSUFBSSxDQUFDLGVBQWUsQ0FBQyxLQUFLLHNCQUF3QixDQUFDLHdCQUEwQixFQUFFO1FBQzdFLDZFQUE2RTtRQUM3RSxlQUFlLENBQUMsS0FBSyxFQUFFLEtBQUssRUFBRSxlQUFlLENBQUMsQ0FBQztLQUNoRDtBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCB7bmV3QXJyYXl9IGZyb20gJy4uLy4uL3V0aWwvYXJyYXlfdXRpbHMnO1xuaW1wb3J0IHtUQXR0cmlidXRlcywgVEVsZW1lbnROb2RlLCBUTm9kZSwgVE5vZGVGbGFncywgVE5vZGVUeXBlfSBmcm9tICcuLi9pbnRlcmZhY2VzL25vZGUnO1xuaW1wb3J0IHtQcm9qZWN0aW9uU2xvdHN9IGZyb20gJy4uL2ludGVyZmFjZXMvcHJvamVjdGlvbic7XG5pbXBvcnQge0RFQ0xBUkFUSU9OX0NPTVBPTkVOVF9WSUVXLCBIRUFERVJfT0ZGU0VULCBUX0hPU1R9IGZyb20gJy4uL2ludGVyZmFjZXMvdmlldyc7XG5pbXBvcnQge2FwcGx5UHJvamVjdGlvbn0gZnJvbSAnLi4vbm9kZV9tYW5pcHVsYXRpb24nO1xuaW1wb3J0IHtnZXRQcm9qZWN0QXNBdHRyVmFsdWUsIGlzTm9kZU1hdGNoaW5nU2VsZWN0b3JMaXN0LCBpc1NlbGVjdG9ySW5TZWxlY3Rvckxpc3R9IGZyb20gJy4uL25vZGVfc2VsZWN0b3JfbWF0Y2hlcic7XG5pbXBvcnQge2dldExWaWV3LCBnZXRUVmlldywgc2V0Q3VycmVudFROb2RlQXNOb3RQYXJlbnR9IGZyb20gJy4uL3N0YXRlJztcbmltcG9ydCB7Z2V0T3JDcmVhdGVUTm9kZX0gZnJvbSAnLi9zaGFyZWQnO1xuXG5cblxuLyoqXG4gKiBDaGVja3MgYSBnaXZlbiBub2RlIGFnYWluc3QgbWF0Y2hpbmcgcHJvamVjdGlvbiBzbG90cyBhbmQgcmV0dXJucyB0aGVcbiAqIGRldGVybWluZWQgc2xvdCBpbmRleC4gUmV0dXJucyBcIm51bGxcIiBpZiBubyBzbG90IG1hdGNoZWQgdGhlIGdpdmVuIG5vZGUuXG4gKlxuICogVGhpcyBmdW5jdGlvbiB0YWtlcyBpbnRvIGFjY291bnQgdGhlIHBhcnNlZCBuZ1Byb2plY3RBcyBzZWxlY3RvciBmcm9tIHRoZVxuICogbm9kZSdzIGF0dHJpYnV0ZXMuIElmIHByZXNlbnQsIGl0IHdpbGwgY2hlY2sgd2hldGhlciB0aGUgbmdQcm9qZWN0QXMgc2VsZWN0b3JcbiAqIG1hdGNoZXMgYW55IG9mIHRoZSBwcm9qZWN0aW9uIHNsb3Qgc2VsZWN0b3JzLlxuICovXG5leHBvcnQgZnVuY3Rpb24gbWF0Y2hpbmdQcm9qZWN0aW9uU2xvdEluZGV4KHROb2RlOiBUTm9kZSwgcHJvamVjdGlvblNsb3RzOiBQcm9qZWN0aW9uU2xvdHMpOiBudW1iZXJ8XG4gICAgbnVsbCB7XG4gIGxldCB3aWxkY2FyZE5nQ29udGVudEluZGV4ID0gbnVsbDtcbiAgY29uc3QgbmdQcm9qZWN0QXNBdHRyVmFsID0gZ2V0UHJvamVjdEFzQXR0clZhbHVlKHROb2RlKTtcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBwcm9qZWN0aW9uU2xvdHMubGVuZ3RoOyBpKyspIHtcbiAgICBjb25zdCBzbG90VmFsdWUgPSBwcm9qZWN0aW9uU2xvdHNbaV07XG4gICAgLy8gVGhlIGxhc3Qgd2lsZGNhcmQgcHJvamVjdGlvbiBzbG90IHNob3VsZCBtYXRjaCBhbGwgbm9kZXMgd2hpY2ggYXJlbid0IG1hdGNoaW5nXG4gICAgLy8gYW55IHNlbGVjdG9yLiBUaGlzIGlzIG5lY2Vzc2FyeSB0byBiZSBiYWNrd2FyZHMgY29tcGF0aWJsZSB3aXRoIHZpZXcgZW5naW5lLlxuICAgIGlmIChzbG90VmFsdWUgPT09ICcqJykge1xuICAgICAgd2lsZGNhcmROZ0NvbnRlbnRJbmRleCA9IGk7XG4gICAgICBjb250aW51ZTtcbiAgICB9XG4gICAgLy8gSWYgd2UgcmFuIGludG8gYW4gYG5nUHJvamVjdEFzYCBhdHRyaWJ1dGUsIHdlIHNob3VsZCBtYXRjaCBpdHMgcGFyc2VkIHNlbGVjdG9yXG4gICAgLy8gdG8gdGhlIGxpc3Qgb2Ygc2VsZWN0b3JzLCBvdGhlcndpc2Ugd2UgZmFsbCBiYWNrIHRvIG1hdGNoaW5nIGFnYWluc3QgdGhlIG5vZGUuXG4gICAgaWYgKG5nUHJvamVjdEFzQXR0clZhbCA9PT0gbnVsbCA/XG4gICAgICAgICAgICBpc05vZGVNYXRjaGluZ1NlbGVjdG9yTGlzdCh0Tm9kZSwgc2xvdFZhbHVlLCAvKiBpc1Byb2plY3Rpb25Nb2RlICovIHRydWUpIDpcbiAgICAgICAgICAgIGlzU2VsZWN0b3JJblNlbGVjdG9yTGlzdChuZ1Byb2plY3RBc0F0dHJWYWwsIHNsb3RWYWx1ZSkpIHtcbiAgICAgIHJldHVybiBpOyAgLy8gZmlyc3QgbWF0Y2hpbmcgc2VsZWN0b3IgXCJjYXB0dXJlc1wiIGEgZ2l2ZW4gbm9kZVxuICAgIH1cbiAgfVxuICByZXR1cm4gd2lsZGNhcmROZ0NvbnRlbnRJbmRleDtcbn1cblxuLyoqXG4gKiBJbnN0cnVjdGlvbiB0byBkaXN0cmlidXRlIHByb2plY3RhYmxlIG5vZGVzIGFtb25nIDxuZy1jb250ZW50PiBvY2N1cnJlbmNlcyBpbiBhIGdpdmVuIHRlbXBsYXRlLlxuICogSXQgdGFrZXMgYWxsIHRoZSBzZWxlY3RvcnMgZnJvbSB0aGUgZW50aXJlIGNvbXBvbmVudCdzIHRlbXBsYXRlIGFuZCBkZWNpZGVzIHdoZXJlXG4gKiBlYWNoIHByb2plY3RlZCBub2RlIGJlbG9uZ3MgKGl0IHJlLWRpc3RyaWJ1dGVzIG5vZGVzIGFtb25nIFwiYnVja2V0c1wiIHdoZXJlIGVhY2ggXCJidWNrZXRcIiBpc1xuICogYmFja2VkIGJ5IGEgc2VsZWN0b3IpLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gcmVxdWlyZXMgQ1NTIHNlbGVjdG9ycyB0byBiZSBwcm92aWRlZCBpbiAyIGZvcm1zOiBwYXJzZWQgKGJ5IGEgY29tcGlsZXIpIGFuZCB0ZXh0LFxuICogdW4tcGFyc2VkIGZvcm0uXG4gKlxuICogVGhlIHBhcnNlZCBmb3JtIGlzIG5lZWRlZCBmb3IgZWZmaWNpZW50IG1hdGNoaW5nIG9mIGEgbm9kZSBhZ2FpbnN0IGEgZ2l2ZW4gQ1NTIHNlbGVjdG9yLlxuICogVGhlIHVuLXBhcnNlZCwgdGV4dHVhbCBmb3JtIGlzIG5lZWRlZCBmb3Igc3VwcG9ydCBvZiB0aGUgbmdQcm9qZWN0QXMgYXR0cmlidXRlLlxuICpcbiAqIEhhdmluZyBhIENTUyBzZWxlY3RvciBpbiAyIGRpZmZlcmVudCBmb3JtYXRzIGlzIG5vdCBpZGVhbCwgYnV0IGFsdGVybmF0aXZlcyBoYXZlIGV2ZW4gbW9yZVxuICogZHJhd2JhY2tzOlxuICogLSBoYXZpbmcgb25seSBhIHRleHR1YWwgZm9ybSB3b3VsZCByZXF1aXJlIHJ1bnRpbWUgcGFyc2luZyBvZiBDU1Mgc2VsZWN0b3JzO1xuICogLSB3ZSBjYW4ndCBoYXZlIG9ubHkgYSBwYXJzZWQgYXMgd2UgY2FuJ3QgcmUtY29uc3RydWN0IHRleHR1YWwgZm9ybSBmcm9tIGl0IChhcyBlbnRlcmVkIGJ5IGFcbiAqIHRlbXBsYXRlIGF1dGhvcikuXG4gKlxuICogQHBhcmFtIHByb2plY3Rpb25TbG90cz8gQSBjb2xsZWN0aW9uIG9mIHByb2plY3Rpb24gc2xvdHMuIEEgcHJvamVjdGlvbiBzbG90IGNhbiBiZSBiYXNlZFxuICogICAgICAgIG9uIGEgcGFyc2VkIENTUyBzZWxlY3RvcnMgb3Igc2V0IHRvIHRoZSB3aWxkY2FyZCBzZWxlY3RvciAoXCIqXCIpIGluIG9yZGVyIHRvIG1hdGNoXG4gKiAgICAgICAgYWxsIG5vZGVzIHdoaWNoIGRvIG5vdCBtYXRjaCBhbnkgc2VsZWN0b3IuIElmIG5vdCBzcGVjaWZpZWQsIGEgc2luZ2xlIHdpbGRjYXJkXG4gKiAgICAgICAgc2VsZWN0b3IgcHJvamVjdGlvbiBzbG90IHdpbGwgYmUgZGVmaW5lZC5cbiAqXG4gKiBAY29kZUdlbkFwaVxuICovXG5leHBvcnQgZnVuY3Rpb24gybXJtXByb2plY3Rpb25EZWYocHJvamVjdGlvblNsb3RzPzogUHJvamVjdGlvblNsb3RzKTogdm9pZCB7XG4gIGNvbnN0IGNvbXBvbmVudE5vZGUgPSBnZXRMVmlldygpW0RFQ0xBUkFUSU9OX0NPTVBPTkVOVF9WSUVXXVtUX0hPU1RdIGFzIFRFbGVtZW50Tm9kZTtcblxuICBpZiAoIWNvbXBvbmVudE5vZGUucHJvamVjdGlvbikge1xuICAgIC8vIElmIG5vIGV4cGxpY2l0IHByb2plY3Rpb24gc2xvdHMgYXJlIGRlZmluZWQsIGZhbGwgYmFjayB0byBhIHNpbmdsZVxuICAgIC8vIHByb2plY3Rpb24gc2xvdCB3aXRoIHRoZSB3aWxkY2FyZCBzZWxlY3Rvci5cbiAgICBjb25zdCBudW1Qcm9qZWN0aW9uU2xvdHMgPSBwcm9qZWN0aW9uU2xvdHMgPyBwcm9qZWN0aW9uU2xvdHMubGVuZ3RoIDogMTtcbiAgICBjb25zdCBwcm9qZWN0aW9uSGVhZHM6IChUTm9kZXxudWxsKVtdID0gY29tcG9uZW50Tm9kZS5wcm9qZWN0aW9uID1cbiAgICAgICAgbmV3QXJyYXkobnVtUHJvamVjdGlvblNsb3RzLCBudWxsISBhcyBUTm9kZSk7XG4gICAgY29uc3QgdGFpbHM6IChUTm9kZXxudWxsKVtdID0gcHJvamVjdGlvbkhlYWRzLnNsaWNlKCk7XG5cbiAgICBsZXQgY29tcG9uZW50Q2hpbGQ6IFROb2RlfG51bGwgPSBjb21wb25lbnROb2RlLmNoaWxkO1xuXG4gICAgd2hpbGUgKGNvbXBvbmVudENoaWxkICE9PSBudWxsKSB7XG4gICAgICBjb25zdCBzbG90SW5kZXggPVxuICAgICAgICAgIHByb2plY3Rpb25TbG90cyA/IG1hdGNoaW5nUHJvamVjdGlvblNsb3RJbmRleChjb21wb25lbnRDaGlsZCwgcHJvamVjdGlvblNsb3RzKSA6IDA7XG5cbiAgICAgIGlmIChzbG90SW5kZXggIT09IG51bGwpIHtcbiAgICAgICAgaWYgKHRhaWxzW3Nsb3RJbmRleF0pIHtcbiAgICAgICAgICB0YWlsc1tzbG90SW5kZXhdIS5wcm9qZWN0aW9uTmV4dCA9IGNvbXBvbmVudENoaWxkO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHByb2plY3Rpb25IZWFkc1tzbG90SW5kZXhdID0gY29tcG9uZW50Q2hpbGQ7XG4gICAgICAgIH1cbiAgICAgICAgdGFpbHNbc2xvdEluZGV4XSA9IGNvbXBvbmVudENoaWxkO1xuICAgICAgfVxuXG4gICAgICBjb21wb25lbnRDaGlsZCA9IGNvbXBvbmVudENoaWxkLm5leHQ7XG4gICAgfVxuICB9XG59XG5cblxuLyoqXG4gKiBJbnNlcnRzIHByZXZpb3VzbHkgcmUtZGlzdHJpYnV0ZWQgcHJvamVjdGVkIG5vZGVzLiBUaGlzIGluc3RydWN0aW9uIG11c3QgYmUgcHJlY2VkZWQgYnkgYSBjYWxsXG4gKiB0byB0aGUgcHJvamVjdGlvbkRlZiBpbnN0cnVjdGlvbi5cbiAqXG4gKiBAcGFyYW0gbm9kZUluZGV4XG4gKiBAcGFyYW0gc2VsZWN0b3JJbmRleDpcbiAqICAgICAgICAtIDAgd2hlbiB0aGUgc2VsZWN0b3IgaXMgYCpgIChvciB1bnNwZWNpZmllZCBhcyB0aGlzIGlzIHRoZSBkZWZhdWx0IHZhbHVlKSxcbiAqICAgICAgICAtIDEgYmFzZWQgaW5kZXggb2YgdGhlIHNlbGVjdG9yIGZyb20gdGhlIHtAbGluayBwcm9qZWN0aW9uRGVmfVxuICpcbiAqIEBjb2RlR2VuQXBpXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiDJtcm1cHJvamVjdGlvbihcbiAgICBub2RlSW5kZXg6IG51bWJlciwgc2VsZWN0b3JJbmRleDogbnVtYmVyID0gMCwgYXR0cnM/OiBUQXR0cmlidXRlcyk6IHZvaWQge1xuICBjb25zdCBsVmlldyA9IGdldExWaWV3KCk7XG4gIGNvbnN0IHRWaWV3ID0gZ2V0VFZpZXcoKTtcbiAgY29uc3QgdFByb2plY3Rpb25Ob2RlID1cbiAgICAgIGdldE9yQ3JlYXRlVE5vZGUodFZpZXcsIEhFQURFUl9PRkZTRVQgKyBub2RlSW5kZXgsIFROb2RlVHlwZS5Qcm9qZWN0aW9uLCBudWxsLCBhdHRycyB8fCBudWxsKTtcblxuICAvLyBXZSBjYW4ndCB1c2Ugdmlld0RhdGFbSE9TVF9OT0RFXSBiZWNhdXNlIHByb2plY3Rpb24gbm9kZXMgY2FuIGJlIG5lc3RlZCBpbiBlbWJlZGRlZCB2aWV3cy5cbiAgaWYgKHRQcm9qZWN0aW9uTm9kZS5wcm9qZWN0aW9uID09PSBudWxsKSB0UHJvamVjdGlvbk5vZGUucHJvamVjdGlvbiA9IHNlbGVjdG9ySW5kZXg7XG5cbiAgLy8gYDxuZy1jb250ZW50PmAgaGFzIG5vIGNvbnRlbnRcbiAgc2V0Q3VycmVudFROb2RlQXNOb3RQYXJlbnQoKTtcblxuICBpZiAoKHRQcm9qZWN0aW9uTm9kZS5mbGFncyAmIFROb2RlRmxhZ3MuaXNEZXRhY2hlZCkgIT09IFROb2RlRmxhZ3MuaXNEZXRhY2hlZCkge1xuICAgIC8vIHJlLWRpc3RyaWJ1dGlvbiBvZiBwcm9qZWN0YWJsZSBub2RlcyBpcyBzdG9yZWQgb24gYSBjb21wb25lbnQncyB2aWV3IGxldmVsXG4gICAgYXBwbHlQcm9qZWN0aW9uKHRWaWV3LCBsVmlldywgdFByb2plY3Rpb25Ob2RlKTtcbiAgfVxufVxuIl19