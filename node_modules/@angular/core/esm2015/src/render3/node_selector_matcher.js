/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import '../util/ng_dev_mode';
import { assertDefined, assertEqual, assertNotEqual } from '../util/assert';
import { unusedValueExportToPlacateAjd as unused1 } from './interfaces/node';
import { unusedValueExportToPlacateAjd as unused2 } from './interfaces/projection';
import { classIndexOf } from './styling/class_differ';
import { isNameOnlyAttributeMarker } from './util/attrs_utils';
const unusedValueToPlacateAjd = unused1 + unused2;
const NG_TEMPLATE_SELECTOR = 'ng-template';
/**
 * Search the `TAttributes` to see if it contains `cssClassToMatch` (case insensitive)
 *
 * @param attrs `TAttributes` to search through.
 * @param cssClassToMatch class to match (lowercase)
 * @param isProjectionMode Whether or not class matching should look into the attribute `class` in
 *    addition to the `AttributeMarker.Classes`.
 */
function isCssClassMatching(attrs, cssClassToMatch, isProjectionMode) {
    // TODO(misko): The fact that this function needs to know about `isProjectionMode` seems suspect.
    // It is strange to me that sometimes the class information comes in form of `class` attribute
    // and sometimes in form of `AttributeMarker.Classes`. Some investigation is needed to determine
    // if that is the right behavior.
    ngDevMode &&
        assertEqual(cssClassToMatch, cssClassToMatch.toLowerCase(), 'Class name expected to be lowercase.');
    let i = 0;
    while (i < attrs.length) {
        let item = attrs[i++];
        if (isProjectionMode && item === 'class') {
            item = attrs[i];
            if (classIndexOf(item.toLowerCase(), cssClassToMatch, 0) !== -1) {
                return true;
            }
        }
        else if (item === 1 /* Classes */) {
            // We found the classes section. Start searching for the class.
            while (i < attrs.length && typeof (item = attrs[i++]) == 'string') {
                // while we have strings
                if (item.toLowerCase() === cssClassToMatch)
                    return true;
            }
            return false;
        }
    }
    return false;
}
/**
 * Checks whether the `tNode` represents an inline template (e.g. `*ngFor`).
 *
 * @param tNode current TNode
 */
export function isInlineTemplate(tNode) {
    return tNode.type === 4 /* Container */ && tNode.value !== NG_TEMPLATE_SELECTOR;
}
/**
 * Function that checks whether a given tNode matches tag-based selector and has a valid type.
 *
 * Matching can be performed in 2 modes: projection mode (when we project nodes) and regular
 * directive matching mode:
 * - in the "directive matching" mode we do _not_ take TContainer's tagName into account if it is
 * different from NG_TEMPLATE_SELECTOR (value different from NG_TEMPLATE_SELECTOR indicates that a
 * tag name was extracted from * syntax so we would match the same directive twice);
 * - in the "projection" mode, we use a tag name potentially extracted from the * syntax processing
 * (applicable to TNodeType.Container only).
 */
function hasTagAndTypeMatch(tNode, currentSelector, isProjectionMode) {
    const tagNameToCompare = tNode.type === 4 /* Container */ && !isProjectionMode ? NG_TEMPLATE_SELECTOR : tNode.value;
    return currentSelector === tagNameToCompare;
}
/**
 * A utility function to match an Ivy node static data against a simple CSS selector
 *
 * @param node static data of the node to match
 * @param selector The selector to try matching against the node.
 * @param isProjectionMode if `true` we are matching for content projection, otherwise we are doing
 * directive matching.
 * @returns true if node matches the selector.
 */
export function isNodeMatchingSelector(tNode, selector, isProjectionMode) {
    ngDevMode && assertDefined(selector[0], 'Selector should have a tag name');
    let mode = 4 /* ELEMENT */;
    const nodeAttrs = tNode.attrs || [];
    // Find the index of first attribute that has no value, only a name.
    const nameOnlyMarkerIdx = getNameOnlyMarkerIndex(nodeAttrs);
    // When processing ":not" selectors, we skip to the next ":not" if the
    // current one doesn't match
    let skipToNextSelector = false;
    for (let i = 0; i < selector.length; i++) {
        const current = selector[i];
        if (typeof current === 'number') {
            // If we finish processing a :not selector and it hasn't failed, return false
            if (!skipToNextSelector && !isPositive(mode) && !isPositive(current)) {
                return false;
            }
            // If we are skipping to the next :not() and this mode flag is positive,
            // it's a part of the current :not() selector, and we should keep skipping
            if (skipToNextSelector && isPositive(current))
                continue;
            skipToNextSelector = false;
            mode = current | (mode & 1 /* NOT */);
            continue;
        }
        if (skipToNextSelector)
            continue;
        if (mode & 4 /* ELEMENT */) {
            mode = 2 /* ATTRIBUTE */ | mode & 1 /* NOT */;
            if (current !== '' && !hasTagAndTypeMatch(tNode, current, isProjectionMode) ||
                current === '' && selector.length === 1) {
                if (isPositive(mode))
                    return false;
                skipToNextSelector = true;
            }
        }
        else {
            const selectorAttrValue = mode & 8 /* CLASS */ ? current : selector[++i];
            // special case for matching against classes when a tNode has been instantiated with
            // class and style values as separate attribute values (e.g. ['title', CLASS, 'foo'])
            if ((mode & 8 /* CLASS */) && tNode.attrs !== null) {
                if (!isCssClassMatching(tNode.attrs, selectorAttrValue, isProjectionMode)) {
                    if (isPositive(mode))
                        return false;
                    skipToNextSelector = true;
                }
                continue;
            }
            const attrName = (mode & 8 /* CLASS */) ? 'class' : current;
            const attrIndexInNode = findAttrIndexInNode(attrName, nodeAttrs, isInlineTemplate(tNode), isProjectionMode);
            if (attrIndexInNode === -1) {
                if (isPositive(mode))
                    return false;
                skipToNextSelector = true;
                continue;
            }
            if (selectorAttrValue !== '') {
                let nodeAttrValue;
                if (attrIndexInNode > nameOnlyMarkerIdx) {
                    nodeAttrValue = '';
                }
                else {
                    ngDevMode &&
                        assertNotEqual(nodeAttrs[attrIndexInNode], 0 /* NamespaceURI */, 'We do not match directives on namespaced attributes');
                    // we lowercase the attribute value to be able to match
                    // selectors without case-sensitivity
                    // (selectors are already in lowercase when generated)
                    nodeAttrValue = nodeAttrs[attrIndexInNode + 1].toLowerCase();
                }
                const compareAgainstClassName = mode & 8 /* CLASS */ ? nodeAttrValue : null;
                if (compareAgainstClassName &&
                    classIndexOf(compareAgainstClassName, selectorAttrValue, 0) !== -1 ||
                    mode & 2 /* ATTRIBUTE */ && selectorAttrValue !== nodeAttrValue) {
                    if (isPositive(mode))
                        return false;
                    skipToNextSelector = true;
                }
            }
        }
    }
    return isPositive(mode) || skipToNextSelector;
}
function isPositive(mode) {
    return (mode & 1 /* NOT */) === 0;
}
/**
 * Examines the attribute's definition array for a node to find the index of the
 * attribute that matches the given `name`.
 *
 * NOTE: This will not match namespaced attributes.
 *
 * Attribute matching depends upon `isInlineTemplate` and `isProjectionMode`.
 * The following table summarizes which types of attributes we attempt to match:
 *
 * ===========================================================================================================
 * Modes                   | Normal Attributes | Bindings Attributes | Template Attributes | I18n
 * Attributes
 * ===========================================================================================================
 * Inline + Projection     | YES               | YES                 | NO                  | YES
 * -----------------------------------------------------------------------------------------------------------
 * Inline + Directive      | NO                | NO                  | YES                 | NO
 * -----------------------------------------------------------------------------------------------------------
 * Non-inline + Projection | YES               | YES                 | NO                  | YES
 * -----------------------------------------------------------------------------------------------------------
 * Non-inline + Directive  | YES               | YES                 | NO                  | YES
 * ===========================================================================================================
 *
 * @param name the name of the attribute to find
 * @param attrs the attribute array to examine
 * @param isInlineTemplate true if the node being matched is an inline template (e.g. `*ngFor`)
 * rather than a manually expanded template node (e.g `<ng-template>`).
 * @param isProjectionMode true if we are matching against content projection otherwise we are
 * matching against directives.
 */
function findAttrIndexInNode(name, attrs, isInlineTemplate, isProjectionMode) {
    if (attrs === null)
        return -1;
    let i = 0;
    if (isProjectionMode || !isInlineTemplate) {
        let bindingsMode = false;
        while (i < attrs.length) {
            const maybeAttrName = attrs[i];
            if (maybeAttrName === name) {
                return i;
            }
            else if (maybeAttrName === 3 /* Bindings */ || maybeAttrName === 6 /* I18n */) {
                bindingsMode = true;
            }
            else if (maybeAttrName === 1 /* Classes */ || maybeAttrName === 2 /* Styles */) {
                let value = attrs[++i];
                // We should skip classes here because we have a separate mechanism for
                // matching classes in projection mode.
                while (typeof value === 'string') {
                    value = attrs[++i];
                }
                continue;
            }
            else if (maybeAttrName === 4 /* Template */) {
                // We do not care about Template attributes in this scenario.
                break;
            }
            else if (maybeAttrName === 0 /* NamespaceURI */) {
                // Skip the whole namespaced attribute and value. This is by design.
                i += 4;
                continue;
            }
            // In binding mode there are only names, rather than name-value pairs.
            i += bindingsMode ? 1 : 2;
        }
        // We did not match the attribute
        return -1;
    }
    else {
        return matchTemplateAttribute(attrs, name);
    }
}
export function isNodeMatchingSelectorList(tNode, selector, isProjectionMode = false) {
    for (let i = 0; i < selector.length; i++) {
        if (isNodeMatchingSelector(tNode, selector[i], isProjectionMode)) {
            return true;
        }
    }
    return false;
}
export function getProjectAsAttrValue(tNode) {
    const nodeAttrs = tNode.attrs;
    if (nodeAttrs != null) {
        const ngProjectAsAttrIdx = nodeAttrs.indexOf(5 /* ProjectAs */);
        // only check for ngProjectAs in attribute names, don't accidentally match attribute's value
        // (attribute names are stored at even indexes)
        if ((ngProjectAsAttrIdx & 1) === 0) {
            return nodeAttrs[ngProjectAsAttrIdx + 1];
        }
    }
    return null;
}
function getNameOnlyMarkerIndex(nodeAttrs) {
    for (let i = 0; i < nodeAttrs.length; i++) {
        const nodeAttr = nodeAttrs[i];
        if (isNameOnlyAttributeMarker(nodeAttr)) {
            return i;
        }
    }
    return nodeAttrs.length;
}
function matchTemplateAttribute(attrs, name) {
    let i = attrs.indexOf(4 /* Template */);
    if (i > -1) {
        i++;
        while (i < attrs.length) {
            const attr = attrs[i];
            // Return in case we checked all template attrs and are switching to the next section in the
            // attrs array (that starts with a number that represents an attribute marker).
            if (typeof attr === 'number')
                return -1;
            if (attr === name)
                return i;
            i++;
        }
    }
    return -1;
}
/**
 * Checks whether a selector is inside a CssSelectorList
 * @param selector Selector to be checked.
 * @param list List in which to look for the selector.
 */
export function isSelectorInSelectorList(selector, list) {
    selectorListLoop: for (let i = 0; i < list.length; i++) {
        const currentSelectorInList = list[i];
        if (selector.length !== currentSelectorInList.length) {
            continue;
        }
        for (let j = 0; j < selector.length; j++) {
            if (selector[j] !== currentSelectorInList[j]) {
                continue selectorListLoop;
            }
        }
        return true;
    }
    return false;
}
function maybeWrapInNotSelector(isNegativeMode, chunk) {
    return isNegativeMode ? ':not(' + chunk.trim() + ')' : chunk;
}
function stringifyCSSSelector(selector) {
    let result = selector[0];
    let i = 1;
    let mode = 2 /* ATTRIBUTE */;
    let currentChunk = '';
    let isNegativeMode = false;
    while (i < selector.length) {
        let valueOrMarker = selector[i];
        if (typeof valueOrMarker === 'string') {
            if (mode & 2 /* ATTRIBUTE */) {
                const attrValue = selector[++i];
                currentChunk +=
                    '[' + valueOrMarker + (attrValue.length > 0 ? '="' + attrValue + '"' : '') + ']';
            }
            else if (mode & 8 /* CLASS */) {
                currentChunk += '.' + valueOrMarker;
            }
            else if (mode & 4 /* ELEMENT */) {
                currentChunk += ' ' + valueOrMarker;
            }
        }
        else {
            //
            // Append current chunk to the final result in case we come across SelectorFlag, which
            // indicates that the previous section of a selector is over. We need to accumulate content
            // between flags to make sure we wrap the chunk later in :not() selector if needed, e.g.
            // ```
            //  ['', Flags.CLASS, '.classA', Flags.CLASS | Flags.NOT, '.classB', '.classC']
            // ```
            // should be transformed to `.classA :not(.classB .classC)`.
            //
            // Note: for negative selector part, we accumulate content between flags until we find the
            // next negative flag. This is needed to support a case where `:not()` rule contains more than
            // one chunk, e.g. the following selector:
            // ```
            //  ['', Flags.ELEMENT | Flags.NOT, 'p', Flags.CLASS, 'foo', Flags.CLASS | Flags.NOT, 'bar']
            // ```
            // should be stringified to `:not(p.foo) :not(.bar)`
            //
            if (currentChunk !== '' && !isPositive(valueOrMarker)) {
                result += maybeWrapInNotSelector(isNegativeMode, currentChunk);
                currentChunk = '';
            }
            mode = valueOrMarker;
            // According to CssSelector spec, once we come across `SelectorFlags.NOT` flag, the negative
            // mode is maintained for remaining chunks of a selector.
            isNegativeMode = isNegativeMode || !isPositive(mode);
        }
        i++;
    }
    if (currentChunk !== '') {
        result += maybeWrapInNotSelector(isNegativeMode, currentChunk);
    }
    return result;
}
/**
 * Generates string representation of CSS selector in parsed form.
 *
 * ComponentDef and DirectiveDef are generated with the selector in parsed form to avoid doing
 * additional parsing at runtime (for example, for directive matching). However in some cases (for
 * example, while bootstrapping a component), a string version of the selector is required to query
 * for the host element on the page. This function takes the parsed form of a selector and returns
 * its string representation.
 *
 * @param selectorList selector in parsed form
 * @returns string representation of a given selector
 */
export function stringifyCSSSelectorList(selectorList) {
    return selectorList.map(stringifyCSSSelector).join(',');
}
/**
 * Extracts attributes and classes information from a given CSS selector.
 *
 * This function is used while creating a component dynamically. In this case, the host element
 * (that is created dynamically) should contain attributes and classes specified in component's CSS
 * selector.
 *
 * @param selector CSS selector in parsed form (in a form of array)
 * @returns object with `attrs` and `classes` fields that contain extracted information
 */
export function extractAttrsAndClassesFromSelector(selector) {
    const attrs = [];
    const classes = [];
    let i = 1;
    let mode = 2 /* ATTRIBUTE */;
    while (i < selector.length) {
        let valueOrMarker = selector[i];
        if (typeof valueOrMarker === 'string') {
            if (mode === 2 /* ATTRIBUTE */) {
                if (valueOrMarker !== '') {
                    attrs.push(valueOrMarker, selector[++i]);
                }
            }
            else if (mode === 8 /* CLASS */) {
                classes.push(valueOrMarker);
            }
        }
        else {
            // According to CssSelector spec, once we come across `SelectorFlags.NOT` flag, the negative
            // mode is maintained for remaining chunks of a selector. Since attributes and classes are
            // extracted only for "positive" part of the selector, we can stop here.
            if (!isPositive(mode))
                break;
            mode = valueOrMarker;
        }
        i++;
    }
    return { attrs, classes };
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibm9kZV9zZWxlY3Rvcl9tYXRjaGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zcmMvcmVuZGVyMy9ub2RlX3NlbGVjdG9yX21hdGNoZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUgsT0FBTyxxQkFBcUIsQ0FBQztBQUU3QixPQUFPLEVBQUMsYUFBYSxFQUFFLFdBQVcsRUFBRSxjQUFjLEVBQUMsTUFBTSxnQkFBZ0IsQ0FBQztBQUUxRSxPQUFPLEVBQWlELDZCQUE2QixJQUFJLE9BQU8sRUFBQyxNQUFNLG1CQUFtQixDQUFDO0FBQzNILE9BQU8sRUFBOEMsNkJBQTZCLElBQUksT0FBTyxFQUFDLE1BQU0seUJBQXlCLENBQUM7QUFDOUgsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLHdCQUF3QixDQUFDO0FBQ3BELE9BQU8sRUFBQyx5QkFBeUIsRUFBQyxNQUFNLG9CQUFvQixDQUFDO0FBRTdELE1BQU0sdUJBQXVCLEdBQUcsT0FBTyxHQUFHLE9BQU8sQ0FBQztBQUVsRCxNQUFNLG9CQUFvQixHQUFHLGFBQWEsQ0FBQztBQUUzQzs7Ozs7OztHQU9HO0FBQ0gsU0FBUyxrQkFBa0IsQ0FDdkIsS0FBa0IsRUFBRSxlQUF1QixFQUFFLGdCQUF5QjtJQUN4RSxpR0FBaUc7SUFDakcsOEZBQThGO0lBQzlGLGdHQUFnRztJQUNoRyxpQ0FBaUM7SUFDakMsU0FBUztRQUNMLFdBQVcsQ0FDUCxlQUFlLEVBQUUsZUFBZSxDQUFDLFdBQVcsRUFBRSxFQUFFLHNDQUFzQyxDQUFDLENBQUM7SUFDaEcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQ1YsT0FBTyxDQUFDLEdBQUcsS0FBSyxDQUFDLE1BQU0sRUFBRTtRQUN2QixJQUFJLElBQUksR0FBRyxLQUFLLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQztRQUN0QixJQUFJLGdCQUFnQixJQUFJLElBQUksS0FB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QUMsQ0FBQyxDQUFDO1FBQ2hDLElBQUksT0FBTyxhQUFhLEtBQUssUUFBUSxFQUFFO1lBQ3JDLElBQUksSUFBSSxzQkFBNEIsRUFBRTtnQkFDcEMsSUFBSSxhQUFhLEtBQUssRUFBRSxFQUFFO29CQUN4QixLQUFLLENBQUMsSUFBSSxDQUFDLGFBQWEsRUFBRSxRQUFRLENBQUMsRUFBRSxDQUFDLENBQVcsQ0FBQyxDQUFDO2lCQUNwRDthQUNGO2lCQUFNLElBQUksSUFBSSxrQkFBd0IsRUFBRTtnQkFDdkMsT0FBTyxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQzthQUM3QjtTQUNGO2FBQU07WUFDTCw0RkFBNEY7WUFDNUYsMEZBQTBGO1lBQzFGLHdFQUF3RTtZQUN4RSxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQztnQkFBRSxNQUFNO1lBQzdCLElBQUksR0FBRyxhQUFhLENBQUM7U0FDdEI7UUFDRCxDQUFDLEVBQUUsQ0FBQztLQUNMO0lBQ0QsT0FBTyxFQUFDLEtBQUssRUFBRSxPQUFPLEVBQUMsQ0FBQztBQUMxQixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAnLi4vdXRpbC9uZ19kZXZfbW9kZSc7XG5cbmltcG9ydCB7YXNzZXJ0RGVmaW5lZCwgYXNzZXJ0RXF1YWwsIGFzc2VydE5vdEVxdWFsfSBmcm9tICcuLi91dGlsL2Fzc2VydCc7XG5cbmltcG9ydCB7QXR0cmlidXRlTWFya2VyLCBUQXR0cmlidXRlcywgVE5vZGUsIFROb2RlVHlwZSwgdW51c2VkVmFsdWVFeHBvcnRUb1BsYWNhdGVBamQgYXMgdW51c2VkMX0gZnJvbSAnLi9pbnRlcmZhY2VzL25vZGUnO1xuaW1wb3J0IHtDc3NTZWxlY3RvciwgQ3NzU2VsZWN0b3JMaXN0LCBTZWxlY3RvckZsYWdzLCB1bnVzZWRWYWx1ZUV4cG9ydFRvUGxhY2F0ZUFqZCBhcyB1bnVzZWQyfSBmcm9tICcuL2ludGVyZmFjZXMvcHJvamVjdGlvbic7XG5pbXBvcnQge2NsYXNzSW5kZXhPZn0gZnJvbSAnLi9zdHlsaW5nL2NsYXNzX2RpZmZlcic7XG5pbXBvcnQge2lzTmFtZU9ubHlBdHRyaWJ1dGVNYXJrZXJ9IGZyb20gJy4vdXRpbC9hdHRyc191dGlscyc7XG5cbmNvbnN0IHVudXNlZFZhbHVlVG9QbGFjYXRlQWpkID0gdW51c2VkMSArIHVudXNlZDI7XG5cbmNvbnN0IE5HX1RFTVBMQVRFX1NFTEVDVE9SID0gJ25nLXRlbXBsYXRlJztcblxuLyoqXG4gKiBTZWFyY2ggdGhlIGBUQXR0cmlidXRlc2AgdG8gc2VlIGlmIGl0IGNvbnRhaW5zIGBjc3NDbGFzc1RvTWF0Y2hgIChjYXNlIGluc2Vuc2l0aXZlKVxuICpcbiAqIEBwYXJhbSBhdHRycyBgVEF0dHJpYnV0ZXNgIHRvIHNlYXJjaCB0aHJvdWdoLlxuICogQHBhcmFtIGNzc0NsYXNzVG9NYXRjaCBjbGFzcyB0byBtYXRjaCAobG93ZXJjYXNlKVxuICogQHBhcmFtIGlzUHJvamVjdGlvbk1vZGUgV2hldGhlciBvciBub3QgY2xhc3MgbWF0Y2hpbmcgc2hvdWxkIGxvb2sgaW50byB0aGUgYXR0cmlidXRlIGBjbGFzc2AgaW5cbiAqICAgIGFkZGl0aW9uIHRvIHRoZSBgQXR0cmlidXRlTWFya2VyLkNsYXNzZXNgLlxuICovXG5mdW5jdGlvbiBpc0Nzc0NsYXNzTWF0Y2hpbmcoXG4gICAgYXR0cnM6IFRBdHRyaWJ1dGVzLCBjc3NDbGFzc1RvTWF0Y2g6IHN0cmluZywgaXNQcm9qZWN0aW9uTW9kZTogYm9vbGVhbik6IGJvb2xlYW4ge1xuICAvLyBUT0RPKG1pc2tvKTogVGhlIGZhY3QgdGhhdCB0aGlzIGZ1bmN0aW9uIG5lZWRzIHRvIGtub3cgYWJvdXQgYGlzUHJvamVjdGlvbk1vZGVgIHNlZW1zIHN1c3BlY3QuXG4gIC8vIEl0IGlzIHN0cmFuZ2UgdG8gbWUgdGhhdCBzb21ldGltZXMgdGhlIGNsYXNzIGluZm9ybWF0aW9uIGNvbWVzIGluIGZvcm0gb2YgYGNsYXNzYCBhdHRyaWJ1dGVcbiAgLy8gYW5kIHNvbWV0aW1lcyBpbiBmb3JtIG9mIGBBdHRyaWJ1dGVNYXJrZXIuQ2xhc3Nlc2AuIFNvbWUgaW52ZXN0aWdhdGlvbiBpcyBuZWVkZWQgdG8gZGV0ZXJtaW5lXG4gIC8vIGlmIHRoYXQgaXMgdGhlIHJpZ2h0IGJlaGF2aW9yLlxuICBuZ0Rldk1vZGUgJiZcbiAgICAgIGFzc2VydEVxdWFsKFxuICAgICAgICAgIGNzc0NsYXNzVG9NYXRjaCwgY3NzQ2xhc3NUb01hdGNoLnRvTG93ZXJDYXNlKCksICdDbGFzcyBuYW1lIGV4cGVjdGVkIHRvIGJlIGxvd2VyY2FzZS4nKTtcbiAgbGV0IGkgPSAwO1xuICB3aGlsZSAoaSA8IGF0dHJzLmxlbmd0aCkge1xuICAgIGxldCBpdGVtID0gYXR0cnNbaSsrXTtcbiAgICBpZiAoaXNQcm9qZWN0aW9uTW9kZSAmJiBpdGVtID09PSAnY2xhc3MnKSB7XG4gICAgICBpdGVtID0gYXR0cnNbaV0gYXMgc3RyaW5nO1xuICAgICAgaWYgKGNsYXNzSW5kZXhPZihpdGVtLnRvTG93ZXJDYXNlKCksIGNzc0NsYXNzVG9NYXRjaCwgMCkgIT09IC0xKSB7XG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAoaXRlbSA9PT0gQXR0cmlidXRlTWFya2VyLkNsYXNzZXMpIHtcbiAgICAgIC8vIFdlIGZvdW5kIHRoZSBjbGFzc2VzIHNlY3Rpb24uIFN0YXJ0IHNlYXJjaGluZyBmb3IgdGhlIGNsYXNzLlxuICAgICAgd2hpbGUgKGkgPCBhdHRycy5sZW5ndGggJiYgdHlwZW9mIChpdGVtID0gYXR0cnNbaSsrXSkgPT0gJ3N0cmluZycpIHtcbiAgICAgICAgLy8gd2hpbGUgd2UgaGF2ZSBzdHJpbmdzXG4gICAgICAgIGlmIChpdGVtLnRvTG93ZXJDYXNlKCkgPT09IGNzc0NsYXNzVG9NYXRjaCkgcmV0dXJuIHRydWU7XG4gICAgICB9XG4gICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuICB9XG4gIHJldHVybiBmYWxzZTtcbn1cblxuLyoqXG4gKiBDaGVja3Mgd2hldGhlciB0aGUgYHROb2RlYCByZXByZXNlbnRzIGFuIGlubGluZSB0ZW1wbGF0ZSAoZS5nLiBgKm5nRm9yYCkuXG4gKlxuICogQHBhcmFtIHROb2RlIGN1cnJlbnQgVE5vZGVcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGlzSW5saW5lVGVtcGxhdGUodE5vZGU6IFROb2RlKTogYm9vbGVhbiB7XG4gIHJldHVybiB0Tm9kZS50eXBlID09PSBUTm9kZVR5cGUuQ29udGFpbmVyICYmIHROb2RlLnZhbHVlICE9PSBOR19URU1QTEFURV9TRUxFQ1RPUjtcbn1cblxuLyoqXG4gKiBGdW5jdGlvbiB0aGF0IGNoZWNrcyB3aGV0aGVyIGEgZ2l2ZW4gdE5vZGUgbWF0Y2hlcyB0YWctYmFzZWQgc2VsZWN0b3IgYW5kIGhhcyBhIHZhbGlkIHR5cGUuXG4gKlxuICogTWF0Y2hpbmcgY2FuIGJlIHBlcmZvcm1lZCBpbiAyIG1vZGVzOiBwcm9qZWN0aW9uIG1vZGUgKHdoZW4gd2UgcHJvamVjdCBub2RlcykgYW5kIHJlZ3VsYXJcbiAqIGRpcmVjdGl2ZSBtYXRjaGluZyBtb2RlOlxuICogLSBpbiB0aGUgXCJkaXJlY3RpdmUgbWF0Y2hpbmdcIiBtb2RlIHdlIGRvIF9ub3RfIHRha2UgVENvbnRhaW5lcidzIHRhZ05hbWUgaW50byBhY2NvdW50IGlmIGl0IGlzXG4gKiBkaWZmZXJlbnQgZnJvbSBOR19URU1QTEFURV9TRUxFQ1RPUiAodmFsdWUgZGlmZmVyZW50IGZyb20gTkdfVEVNUExBVEVfU0VMRUNUT1IgaW5kaWNhdGVzIHRoYXQgYVxuICogdGFnIG5hbWUgd2FzIGV4dHJhY3RlZCBmcm9tICogc3ludGF4IHNvIHdlIHdvdWxkIG1hdGNoIHRoZSBzYW1lIGRpcmVjdGl2ZSB0d2ljZSk7XG4gKiAtIGluIHRoZSBcInByb2plY3Rpb25cIiBtb2RlLCB3ZSB1c2UgYSB0YWcgbmFtZSBwb3RlbnRpYWxseSBleHRyYWN0ZWQgZnJvbSB0aGUgKiBzeW50YXggcHJvY2Vzc2luZ1xuICogKGFwcGxpY2FibGUgdG8gVE5vZGVUeXBlLkNvbnRhaW5lciBvbmx5KS5cbiAqL1xuZnVuY3Rpb24gaGFzVGFnQW5kVHlwZU1hdGNoKFxuICAgIHROb2RlOiBUTm9kZSwgY3VycmVudFNlbGVjdG9yOiBzdHJpbmcsIGlzUHJvamVjdGlvbk1vZGU6IGJvb2xlYW4pOiBib29sZWFuIHtcbiAgY29uc3QgdGFnTmFtZVRvQ29tcGFyZSA9XG4gICAgICB0Tm9kZS50eXBlID09PSBUTm9kZVR5cGUuQ29udGFpbmVyICYmICFpc1Byb2plY3Rpb25Nb2RlID8gTkdfVEVNUExBVEVfU0VMRUNUT1IgOiB0Tm9kZS52YWx1ZTtcbiAgcmV0dXJuIGN1cnJlbnRTZWxlY3RvciA9PT0gdGFnTmFtZVRvQ29tcGFyZTtcbn1cblxuLyoqXG4gKiBBIHV0aWxpdHkgZnVuY3Rpb24gdG8gbWF0Y2ggYW4gSXZ5IG5vZGUgc3RhdGljIGRhdGEgYWdhaW5zdCBhIHNpbXBsZSBDU1Mgc2VsZWN0b3JcbiAqXG4gKiBAcGFyYW0gbm9kZSBzdGF0aWMgZGF0YSBvZiB0aGUgbm9kZSB0byBtYXRjaFxuICogQHBhcmFtIHNlbGVjdG9yIFRoZSBzZWxlY3RvciB0byB0cnkgbWF0Y2hpbmcgYWdhaW5zdCB0aGUgbm9kZS5cbiAqIEBwYXJhbSBpc1Byb2plY3Rpb25Nb2RlIGlmIGB0cnVlYCB3ZSBhcmUgbWF0Y2hpbmcgZm9yIGNvbnRlbnQgcHJvamVjdGlvbiwgb3RoZXJ3aXNlIHdlIGFyZSBkb2luZ1xuICogZGlyZWN0aXZlIG1hdGNoaW5nLlxuICogQHJldHVybnMgdHJ1ZSBpZiBub2RlIG1hdGNoZXMgdGhlIHNlbGVjdG9yLlxuICovXG5leHBvcnQgZnVuY3Rpb24gaXNOb2RlTWF0Y2hpbmdTZWxlY3RvcihcbiAgICB0Tm9kZTogVE5vZGUsIHNlbGVjdG9yOiBDc3NTZWxlY3RvciwgaXNQcm9qZWN0aW9uTW9kZTogYm9vbGVhbik6IGJvb2xlYW4ge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0RGVmaW5lZChzZWxlY3RvclswXSwgJ1NlbGVjdG9yIHNob3VsZCBoYXZlIGEgdGFnIG5hbWUnKTtcbiAgbGV0IG1vZGU6IFNlbGVjdG9yRmxhZ3MgPSBTZWxlY3RvckZsYWdzLkVMRU1FTlQ7XG4gIGNvbnN0IG5vZGVBdHRycyA9IHROb2RlLmF0dHJzIHx8IFtdO1xuXG4gIC8vIEZpbmQgdGhlIGluZGV4IG9mIGZpcnN0IGF0dHJpYnV0ZSB0aGF0IGhhcyBubyB2YWx1ZSwgb25seSBhIG5hbWUuXG4gIGNvbnN0IG5hbWVPbmx5TWFya2VySWR4ID0gZ2V0TmFtZU9ubHlNYXJrZXJJbmRleChub2RlQXR0cnMpO1xuXG4gIC8vIFdoZW4gcHJvY2Vzc2luZyBcIjpub3RcIiBzZWxlY3RvcnMsIHdlIHNraXAgdG8gdGhlIG5leHQgXCI6bm90XCIgaWYgdGhlXG4gIC8vIGN1cnJlbnQgb25lIGRvZXNuJ3QgbWF0Y2hcbiAgbGV0IHNraXBUb05leHRTZWxlY3RvciA9IGZhbHNlO1xuXG4gIGZvciAobGV0IGkgPSAwOyBpIDwgc2VsZWN0b3IubGVuZ3RoOyBpKyspIHtcbiAgICBjb25zdCBjdXJyZW50ID0gc2VsZWN0b3JbaV07XG4gICAgaWYgKHR5cGVvZiBjdXJyZW50ID09PSAnbnVtYmVyJykge1xuICAgICAgLy8gSWYgd2UgZmluaXNoIHByb2Nlc3NpbmcgYSA6bm90IHNlbGVjdG9yIGFuZCBpdCBoYXNuJ3QgZmFpbGVkLCByZXR1cm4gZmFsc2VcbiAgICAgIGlmICghc2tpcFRvTmV4dFNlbGVjdG9yICYmICFpc1Bvc2l0aXZlKG1vZGUpICYmICFpc1Bvc2l0aXZlKGN1cnJlbnQpKSB7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIH1cbiAgICAgIC8vIElmIHdlIGFyZSBza2lwcGluZyB0byB0aGUgbmV4dCA6bm90KCkgYW5kIHRoaXMgbW9kZSBmbGFnIGlzIHBvc2l0aXZlLFxuICAgICAgLy8gaXQncyBhIHBhcnQgb2YgdGhlIGN1cnJlbnQgOm5vdCgpIHNlbGVjdG9yLCBhbmQgd2Ugc2hvdWxkIGtlZXAgc2tpcHBpbmdcbiAgICAgIGlmIChza2lwVG9OZXh0U2VsZWN0b3IgJiYgaXNQb3NpdGl2ZShjdXJyZW50KSkgY29udGludWU7XG4gICAgICBza2lwVG9OZXh0U2VsZWN0b3IgPSBmYWxzZTtcbiAgICAgIG1vZGUgPSAoY3VycmVudCBhcyBudW1iZXIpIHwgKG1vZGUgJiBTZWxlY3RvckZsYWdzLk5PVCk7XG4gICAgICBjb250aW51ZTtcbiAgICB9XG5cbiAgICBpZiAoc2tpcFRvTmV4dFNlbGVjdG9yKSBjb250aW51ZTtcblxuICAgIGlmIChtb2RlICYgU2VsZWN0b3JGbGFncy5FTEVNRU5UKSB7XG4gICAgICBtb2RlID0gU2VsZWN0b3JGbGFncy5BVFRSSUJVVEUgfCBtb2RlICYgU2VsZWN0b3JGbGFncy5OT1Q7XG4gICAgICBpZiAoY3VycmVudCAhPT0gJycgJiYgIWhhc1RhZ0FuZFR5cGVNYXRjaCh0Tm9kZSwgY3VycmVudCwgaXNQcm9qZWN0aW9uTW9kZSkgfHxcbiAgICAgICAgICBjdXJyZW50ID09PSAnJyAmJiBzZWxlY3Rvci5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgaWYgKGlzUG9zaXRpdmUobW9kZSkpIHJldHVybiBmYWxzZTtcbiAgICAgICAgc2tpcFRvTmV4dFNlbGVjdG9yID0gdHJ1ZTtcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgY29uc3Qgc2VsZWN0b3JBdHRyVmFsdWUgPSBtb2RlICYgU2VsZWN0b3JGbGFncy5DTEFTUyA/IGN1cnJlbnQgOiBzZWxlY3RvclsrK2ldO1xuXG4gICAgICAvLyBzcGVjaWFsIGNhc2UgZm9yIG1hdGNoaW5nIGFnYWluc3QgY2xhc3NlcyB3aGVuIGEgdE5vZGUgaGFzIGJlZW4gaW5zdGFudGlhdGVkIHdpdGhcbiAgICAgIC8vIGNsYXNzIGFuZCBzdHlsZSB2YWx1ZXMgYXMgc2VwYXJhdGUgYXR0cmlidXRlIHZhbHVlcyAoZS5nLiBbJ3RpdGxlJywgQ0xBU1MsICdmb28nXSlcbiAgICAgIGlmICgobW9kZSAmIFNlbGVjdG9yRmxhZ3MuQ0xBU1MpICYmIHROb2RlLmF0dHJzICE9PSBudWxsKSB7XG4gICAgICAgIGlmICghaXNDc3NDbGFzc01hdGNoaW5nKHROb2RlLmF0dHJzLCBzZWxlY3RvckF0dHJWYWx1ZSBhcyBzdHJpbmcsIGlzUHJvamVjdGlvbk1vZGUpKSB7XG4gICAgICAgICAgaWYgKGlzUG9zaXRpdmUobW9kZSkpIHJldHVybiBmYWxzZTtcbiAgICAgICAgICBza2lwVG9OZXh0U2VsZWN0b3IgPSB0cnVlO1xuICAgICAgICB9XG4gICAgICAgIGNvbnRpbnVlO1xuICAgICAgfVxuXG4gICAgICBjb25zdCBhdHRyTmFtZSA9IChtb2RlICYgU2VsZWN0b3JGbGFncy5DTEFTUykgPyAnY2xhc3MnIDogY3VycmVudDtcbiAgICAgIGNvbnN0IGF0dHJJbmRleEluTm9kZSA9XG4gICAgICAgICAgZmluZEF0dHJJbmRleEluTm9kZShhdHRyTmFtZSwgbm9kZUF0dHJzLCBpc0lubGluZVRlbXBsYXRlKHROb2RlKSwgaXNQcm9qZWN0aW9uTW9kZSk7XG5cbiAgICAgIGlmIChhdHRySW5kZXhJbk5vZGUgPT09IC0xKSB7XG4gICAgICAgIGlmIChpc1Bvc2l0aXZlKG1vZGUpKSByZXR1cm4gZmFsc2U7XG4gICAgICAgIHNraXBUb05leHRTZWxlY3RvciA9IHRydWU7XG4gICAgICAgIGNvbnRpbnVlO1xuICAgICAgfVxuXG4gICAgICBpZiAoc2VsZWN0b3JBdHRyVmFsdWUgIT09ICcnKSB7XG4gICAgICAgIGxldCBub2RlQXR0clZhbHVlOiBzdHJpbmc7XG4gICAgICAgIGlmIChhdHRySW5kZXhJbk5vZGUgPiBuYW1lT25seU1hcmtlcklkeCkge1xuICAgICAgICAgIG5vZGVBdHRyVmFsdWUgPSAnJztcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBuZ0Rldk1vZGUgJiZcbiAgICAgICAgICAgICAgYXNzZXJ0Tm90RXF1YWwoXG4gICAgICAgICAgICAgICAgICBub2RlQXR0cnNbYXR0ckluZGV4SW5Ob2RlXSwgQXR0cmlidXRlTWFya2VyLk5hbWVzcGFjZVVSSSxcbiAgICAgICAgICAgICAgICAgICdXZSBkbyBub3QgbWF0Y2ggZGlyZWN0aXZlcyBvbiBuYW1lc3BhY2VkIGF0dHJpYnV0ZXMnKTtcbiAgICAgICAgICAvLyB3ZSBsb3dlcmNhc2UgdGhlIGF0dHJpYnV0ZSB2YWx1ZSB0byBiZSBhYmxlIHRvIG1hdGNoXG4gICAgICAgICAgLy8gc2VsZWN0b3JzIHdpdGhvdXQgY2FzZS1zZW5zaXRpdml0eVxuICAgICAgICAgIC8vIChzZWxlY3RvcnMgYXJlIGFscmVhZHkgaW4gbG93ZXJjYXNlIHdoZW4gZ2VuZXJhdGVkKVxuICAgICAgICAgIG5vZGVBdHRyVmFsdWUgPSAobm9kZUF0dHJzW2F0dHJJbmRleEluTm9kZSArIDFdIGFzIHN0cmluZykudG9Mb3dlckNhc2UoKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGNvbXBhcmVBZ2FpbnN0Q2xhc3NOYW1lID0gbW9kZSAmIFNlbGVjdG9yRmxhZ3MuQ0xBU1MgPyBub2RlQXR0clZhbHVlIDogbnVsbDtcbiAgICAgICAgaWYgKGNvbXBhcmVBZ2FpbnN0Q2xhc3NOYW1lICYmXG4gICAgICAgICAgICAgICAgY2xhc3NJbmRleE9mKGNvbXBhcmVBZ2FpbnN0Q2xhc3NOYW1lLCBzZWxlY3RvckF0dHJWYWx1ZSBhcyBzdHJpbmcsIDApICE9PSAtMSB8fFxuICAgICAgICAgICAgbW9kZSAmIFNlbGVjdG9yRmxhZ3MuQVRUUklCVVRFICYmIHNlbGVjdG9yQXR0clZhbHVlICE9PSBub2RlQXR0clZhbHVlKSB7XG4gICAgICAgICAgaWYgKGlzUG9zaXRpdmUobW9kZSkpIHJldHVybiBmYWxzZTtcbiAgICAgICAgICBza2lwVG9OZXh0U2VsZWN0b3IgPSB0cnVlO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG5cbiAgcmV0dXJuIGlzUG9zaXRpdmUobW9kZSkgfHwgc2tpcFRvTmV4dFNlbGVjdG9yO1xufVxuXG5mdW5jdGlvbiBpc1Bvc2l0aXZlKG1vZGU6IFNlbGVjdG9yRmxhZ3MpOiBib29sZWFuIHtcbiAgcmV0dXJuIChtb2RlICYgU2VsZWN0b3JGbGFncy5OT1QpID09PSAwO1xufVxuXG4vKipcbiAqIEV4YW1pbmVzIHRoZSBhdHRyaWJ1dGUncyBkZWZpbml0aW9uIGFycmF5IGZvciBhIG5vZGUgdG8gZmluZCB0aGUgaW5kZXggb2YgdGhlXG4gKiBhdHRyaWJ1dGUgdGhhdCBtYXRjaGVzIHRoZSBnaXZlbiBgbmFtZWAuXG4gKlxuICogTk9URTogVGhpcyB3aWxsIG5vdCBtYXRjaCBuYW1lc3BhY2VkIGF0dHJpYnV0ZXMuXG4gKlxuICogQXR0cmlidXRlIG1hdGNoaW5nIGRlcGVuZHMgdXBvbiBgaXNJbmxpbmVUZW1wbGF0ZWAgYW5kIGBpc1Byb2plY3Rpb25Nb2RlYC5cbiAqIFRoZSBmb2xsb3dpbmcgdGFibGUgc3VtbWFyaXplcyB3aGljaCB0eXBlcyBvZiBhdHRyaWJ1dGVzIHdlIGF0dGVtcHQgdG8gbWF0Y2g6XG4gKlxuICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqIE1vZGVzICAgICAgICAgICAgICAgICAgIHwgTm9ybWFsIEF0dHJpYnV0ZXMgfCBCaW5kaW5ncyBBdHRyaWJ1dGVzIHwgVGVtcGxhdGUgQXR0cmlidXRlcyB8IEkxOG5cbiAqIEF0dHJpYnV0ZXNcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4gKiBJbmxpbmUgKyBQcm9qZWN0aW9uICAgICB8IFlFUyAgICAgICAgICAgICAgIHwgWUVTICAgICAgICAgICAgICAgICB8IE5PICAgICAgICAgICAgICAgICAgfCBZRVNcbiAqIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gKiBJbmxpbmUgKyBEaXJlY3RpdmUgICAgICB8IE5PICAgICAgICAgICAgICAgIHwgTk8gICAgICAgICAgICAgICAgICB8IFlFUyAgICAgICAgICAgICAgICAgfCBOT1xuICogLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cbiAqIE5vbi1pbmxpbmUgKyBQcm9qZWN0aW9uIHwgWUVTICAgICAgICAgICAgICAgfCBZRVMgICAgICAgICAgICAgICAgIHwgTk8gICAgICAgICAgICAgICAgICB8IFlFU1xuICogLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cbiAqIE5vbi1pbmxpbmUgKyBEaXJlY3RpdmUgIHwgWUVTICAgICAgICAgICAgICAgfCBZRVMgICAgICAgICAgICAgICAgIHwgTk8gICAgICAgICAgICAgICAgICB8IFlFU1xuICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqXG4gKiBAcGFyYW0gbmFtZSB0aGUgbmFtZSBvZiB0aGUgYXR0cmlidXRlIHRvIGZpbmRcbiAqIEBwYXJhbSBhdHRycyB0aGUgYXR0cmlidXRlIGFycmF5IHRvIGV4YW1pbmVcbiAqIEBwYXJhbSBpc0lubGluZVRlbXBsYXRlIHRydWUgaWYgdGhlIG5vZGUgYmVpbmcgbWF0Y2hlZCBpcyBhbiBpbmxpbmUgdGVtcGxhdGUgKGUuZy4gYCpuZ0ZvcmApXG4gKiByYXRoZXIgdGhhbiBhIG1hbnVhbGx5IGV4cGFuZGVkIHRlbXBsYXRlIG5vZGUgKGUuZyBgPG5nLXRlbXBsYXRlPmApLlxuICogQHBhcmFtIGlzUHJvamVjdGlvbk1vZGUgdHJ1ZSBpZiB3ZSBhcmUgbWF0Y2hpbmcgYWdhaW5zdCBjb250ZW50IHByb2plY3Rpb24gb3RoZXJ3aXNlIHdlIGFyZVxuICogbWF0Y2hpbmcgYWdhaW5zdCBkaXJlY3RpdmVzLlxuICovXG5mdW5jdGlvbiBmaW5kQXR0ckluZGV4SW5Ob2RlKFxuICAgIG5hbWU6IHN0cmluZywgYXR0cnM6IFRBdHRyaWJ1dGVzfG51bGwsIGlzSW5saW5lVGVtcGxhdGU6IGJvb2xlYW4sXG4gICAgaXNQcm9qZWN0aW9uTW9kZTogYm9vbGVhbik6IG51bWJlciB7XG4gIGlmIChhdHRycyA9PT0gbnVsbCkgcmV0dXJuIC0xO1xuXG4gIGxldCBpID0gMDtcblxuICBpZiAoaXNQcm9qZWN0aW9uTW9kZSB8fCAhaXNJbmxpbmVUZW1wbGF0ZSkge1xuICAgIGxldCBiaW5kaW5nc01vZGUgPSBmYWxzZTtcbiAgICB3aGlsZSAoaSA8IGF0dHJzLmxlbmd0aCkge1xuICAgICAgY29uc3QgbWF5YmVBdHRyTmFtZSA9IGF0dHJzW2ldO1xuICAgICAgaWYgKG1heWJlQXR0ck5hbWUgPT09IG5hbWUpIHtcbiAgICAgICAgcmV0dXJuIGk7XG4gICAgICB9IGVsc2UgaWYgKFxuICAgICAgICAgIG1heWJlQXR0ck5hbWUgPT09IEF0dHJpYnV0ZU1hcmtlci5CaW5kaW5ncyB8fCBtYXliZUF0dHJOYW1lID09PSBBdHRyaWJ1dGVNYXJrZXIuSTE4bikge1xuICAgICAgICBiaW5kaW5nc01vZGUgPSB0cnVlO1xuICAgICAgfSBlbHNlIGlmIChcbiAgICAgICAgICBtYXliZUF0dHJOYW1lID09PSBBdHRyaWJ1dGVNYXJrZXIuQ2xhc3NlcyB8fCBtYXliZUF0dHJOYW1lID09PSBBdHRyaWJ1dGVNYXJrZXIuU3R5bGVzKSB7XG4gICAgICAgIGxldCB2YWx1ZSA9IGF0dHJzWysraV07XG4gICAgICAgIC8vIFdlIHNob3VsZCBza2lwIGNsYXNzZXMgaGVyZSBiZWNhdXNlIHdlIGhhdmUgYSBzZXBhcmF0ZSBtZWNoYW5pc20gZm9yXG4gICAgICAgIC8vIG1hdGNoaW5nIGNsYXNzZXMgaW4gcHJvamVjdGlvbiBtb2RlLlxuICAgICAgICB3aGlsZSAodHlwZW9mIHZhbHVlID09PSAnc3RyaW5nJykge1xuICAgICAgICAgIHZhbHVlID0gYXR0cnNbKytpXTtcbiAgICAgICAgfVxuICAgICAgICBjb250aW51ZTtcbiAgICAgIH0gZWxzZSBpZiAobWF5YmVBdHRyTmFtZSA9PT0gQXR0cmlidXRlTWFya2VyLlRlbXBsYXRlKSB7XG4gICAgICAgIC8vIFdlIGRvIG5vdCBjYXJlIGFib3V0IFRlbXBsYXRlIGF0dHJpYnV0ZXMgaW4gdGhpcyBzY2VuYXJpby5cbiAgICAgICAgYnJlYWs7XG4gICAgICB9IGVsc2UgaWYgKG1heWJlQXR0ck5hbWUgPT09IEF0dHJpYnV0ZU1hcmtlci5OYW1lc3BhY2VVUkkpIHtcbiAgICAgICAgLy8gU2tpcCB0aGUgd2hvbGUgbmFtZXNwYWNlZCBhdHRyaWJ1dGUgYW5kIHZhbHVlLiBUaGlzIGlzIGJ5IGRlc2lnbi5cbiAgICAgICAgaSArPSA0O1xuICAgICAgICBjb250aW51ZTtcbiAgICAgIH1cbiAgICAgIC8vIEluIGJpbmRpbmcgbW9kZSB0aGVyZSBhcmUgb25seSBuYW1lcywgcmF0aGVyIHRoYW4gbmFtZS12YWx1ZSBwYWlycy5cbiAgICAgIGkgKz0gYmluZGluZ3NNb2RlID8gMSA6IDI7XG4gICAgfVxuICAgIC8vIFdlIGRpZCBub3QgbWF0Y2ggdGhlIGF0dHJpYnV0ZVxuICAgIHJldHVybiAtMTtcbiAgfSBlbHNlIHtcbiAgICByZXR1cm4gbWF0Y2hUZW1wbGF0ZUF0dHJpYnV0ZShhdHRycywgbmFtZSk7XG4gIH1cbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGlzTm9kZU1hdGNoaW5nU2VsZWN0b3JMaXN0KFxuICAgIHROb2RlOiBUTm9kZSwgc2VsZWN0b3I6IENzc1NlbGVjdG9yTGlzdCwgaXNQcm9qZWN0aW9uTW9kZTogYm9vbGVhbiA9IGZhbHNlKTogYm9vbGVhbiB7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgc2VsZWN0b3IubGVuZ3RoOyBpKyspIHtcbiAgICBpZiAoaXNOb2RlTWF0Y2hpbmdTZWxlY3Rvcih0Tm9kZSwgc2VsZWN0b3JbaV0sIGlzUHJvamVjdGlvbk1vZGUpKSB7XG4gICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG4gIH1cblxuICByZXR1cm4gZmFsc2U7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBnZXRQcm9qZWN0QXNBdHRyVmFsdWUodE5vZGU6IFROb2RlKTogQ3NzU2VsZWN0b3J8bnVsbCB7XG4gIGNvbnN0IG5vZGVBdHRycyA9IHROb2RlLmF0dHJzO1xuICBpZiAobm9kZUF0dHJzICE9IG51bGwpIHtcbiAgICBjb25zdCBuZ1Byb2plY3RBc0F0dHJJZHggPSBub2RlQXR0cnMuaW5kZXhPZihBdHRyaWJ1dGVNYXJrZXIuUHJvamVjdEFzKTtcbiAgICAvLyBvbmx5IGNoZWNrIGZvciBuZ1Byb2plY3RBcyBpbiBhdHRyaWJ1dGUgbmFtZXMsIGRvbid0IGFjY2lkZW50YWxseSBtYXRjaCBhdHRyaWJ1dGUncyB2YWx1ZVxuICAgIC8vIChhdHRyaWJ1dGUgbmFtZXMgYXJlIHN0b3JlZCBhdCBldmVuIGluZGV4ZXMpXG4gICAgaWYgKChuZ1Byb2plY3RBc0F0dHJJZHggJiAxKSA9PT0gMCkge1xuICAgICAgcmV0dXJuIG5vZGVBdHRyc1tuZ1Byb2plY3RBc0F0dHJJZHggKyAxXSBhcyBDc3NTZWxlY3RvcjtcbiAgICB9XG4gIH1cbiAgcmV0dXJuIG51bGw7XG59XG5cbmZ1bmN0aW9uIGdldE5hbWVPbmx5TWFya2VySW5kZXgobm9kZUF0dHJzOiBUQXR0cmlidXRlcykge1xuICBmb3IgKGxldCBpID0gMDsgaSA8IG5vZGVBdHRycy5sZW5ndGg7IGkrKykge1xuICAgIGNvbnN0IG5vZGVBdHRyID0gbm9kZUF0dHJzW2ldO1xuICAgIGlmIChpc05hbWVPbmx5QXR0cmlidXRlTWFya2VyKG5vZGVBdHRyKSkge1xuICAgICAgcmV0dXJuIGk7XG4gICAgfVxuICB9XG4gIHJldHVybiBub2RlQXR0cnMubGVuZ3RoO1xufVxuXG5mdW5jdGlvbiBtYXRjaFRlbXBsYXRlQXR0cmlidXRlKGF0dHJzOiBUQXR0cmlidXRlcywgbmFtZTogc3RyaW5nKTogbnVtYmVyIHtcbiAgbGV0IGkgPSBhdHRycy5pbmRleE9mKEF0dHJpYnV0ZU1hcmtlci5UZW1wbGF0ZSk7XG4gIGlmIChpID4gLTEpIHtcbiAgICBpKys7XG4gICAgd2hpbGUgKGkgPCBhdHRycy5sZW5ndGgpIHtcbiAgICAgIGNvbnN0IGF0dHIgPSBhdHRyc1tpXTtcbiAgICAgIC8vIFJldHVybiBpbiBjYXNlIHdlIGNoZWNrZWQgYWxsIHRlbXBsYXRlIGF0dHJzIGFuZCBhcmUgc3dpdGNoaW5nIHRvIHRoZSBuZXh0IHNlY3Rpb24gaW4gdGhlXG4gICAgICAvLyBhdHRycyBhcnJheSAodGhhdCBzdGFydHMgd2l0aCBhIG51bWJlciB0aGF0IHJlcHJlc2VudHMgYW4gYXR0cmlidXRlIG1hcmtlcikuXG4gICAgICBpZiAodHlwZW9mIGF0dHIgPT09ICdudW1iZXInKSByZXR1cm4gLTE7XG4gICAgICBpZiAoYXR0ciA9PT0gbmFtZSkgcmV0dXJuIGk7XG4gICAgICBpKys7XG4gICAgfVxuICB9XG4gIHJldHVybiAtMTtcbn1cblxuLyoqXG4gKiBDaGVja3Mgd2hldGhlciBhIHNlbGVjdG9yIGlzIGluc2lkZSBhIENzc1NlbGVjdG9yTGlzdFxuICogQHBhcmFtIHNlbGVjdG9yIFNlbGVjdG9yIHRvIGJlIGNoZWNrZWQuXG4gKiBAcGFyYW0gbGlzdCBMaXN0IGluIHdoaWNoIHRvIGxvb2sgZm9yIHRoZSBzZWxlY3Rvci5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGlzU2VsZWN0b3JJblNlbGVjdG9yTGlzdChzZWxlY3RvcjogQ3NzU2VsZWN0b3IsIGxpc3Q6IENzc1NlbGVjdG9yTGlzdCk6IGJvb2xlYW4ge1xuICBzZWxlY3Rvckxpc3RMb29wOiBmb3IgKGxldCBpID0gMDsgaSA8IGxpc3QubGVuZ3RoOyBpKyspIHtcbiAgICBjb25zdCBjdXJyZW50U2VsZWN0b3JJbkxpc3QgPSBsaXN0W2ldO1xuICAgIGlmIChzZWxlY3Rvci5sZW5ndGggIT09IGN1cnJlbnRTZWxlY3RvckluTGlzdC5sZW5ndGgpIHtcbiAgICAgIGNvbnRpbnVlO1xuICAgIH1cbiAgICBmb3IgKGxldCBqID0gMDsgaiA8IHNlbGVjdG9yLmxlbmd0aDsgaisrKSB7XG4gICAgICBpZiAoc2VsZWN0b3Jbal0gIT09IGN1cnJlbnRTZWxlY3RvckluTGlzdFtqXSkge1xuICAgICAgICBjb250aW51ZSBzZWxlY3Rvckxpc3RMb29wO1xuICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gdHJ1ZTtcbiAgfVxuICByZXR1cm4gZmFsc2U7XG59XG5cbmZ1bmN0aW9uIG1heWJlV3JhcEluTm90U2VsZWN0b3IoaXNOZWdhdGl2ZU1vZGU6IGJvb2xlYW4sIGNodW5rOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gaXNOZWdhdGl2ZU1vZGUgPyAnOm5vdCgnICsgY2h1bmsudHJpbSgpICsgJyknIDogY2h1bms7XG59XG5cbmZ1bmN0aW9uIHN0cmluZ2lmeUNTU1NlbGVjdG9yKHNlbGVjdG9yOiBDc3NTZWxlY3Rvcik6IHN0cmluZyB7XG4gIGxldCByZXN1bHQgPSBzZWxlY3RvclswXSBhcyBzdHJpbmc7XG4gIGxldCBpID0gMTtcbiAgbGV0IG1vZGUgPSBTZWxlY3RvckZsYWdzLkFUVFJJQlVURTtcbiAgbGV0IGN1cnJlbnRDaHVuayA9ICcnO1xuICBsZXQgaXNOZWdhdGl2ZU1vZGUgPSBmYWxzZTtcbiAgd2hpbGUgKGkgPCBzZWxlY3Rvci5sZW5ndGgpIHtcbiAgICBsZXQgdmFsdWVPck1hcmtlciA9IHNlbGVjdG9yW2ldO1xuICAgIGlmICh0eXBlb2YgdmFsdWVPck1hcmtlciA9PT0gJ3N0cmluZycpIHtcbiAgICAgIGlmIChtb2RlICYgU2VsZWN0b3JGbGFncy5BVFRSSUJVVEUpIHtcbiAgICAgICAgY29uc3QgYXR0clZhbHVlID0gc2VsZWN0b3JbKytpXSBhcyBzdHJpbmc7XG4gICAgICAgIGN1cnJlbnRDaHVuayArPVxuICAgICAgICAgICAgJ1snICsgdmFsdWVPck1hcmtlciArIChhdHRyVmFsdWUubGVuZ3RoID4gMCA/ICc9XCInICsgYXR0clZhbHVlICsgJ1wiJyA6ICcnKSArICddJztcbiAgICAgIH0gZWxzZSBpZiAobW9kZSAmIFNlbGVjdG9yRmxhZ3MuQ0xBU1MpIHtcbiAgICAgICAgY3VycmVudENodW5rICs9ICcuJyArIHZhbHVlT3JNYXJrZXI7XG4gICAgICB9IGVsc2UgaWYgKG1vZGUgJiBTZWxlY3RvckZsYWdzLkVMRU1FTlQpIHtcbiAgICAgICAgY3VycmVudENodW5rICs9ICcgJyArIHZhbHVlT3JNYXJrZXI7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vXG4gICAgICAvLyBBcHBlbmQgY3VycmVudCBjaHVuayB0byB0aGUgZmluYWwgcmVzdWx0IGluIGNhc2Ugd2UgY29tZSBhY3Jvc3MgU2VsZWN0b3JGbGFnLCB3aGljaFxuICAgICAgLy8gaW5kaWNhdGVzIHRoYXQgdGhlIHByZXZpb3VzIHNlY3Rpb24gb2YgYSBzZWxlY3RvciBpcyBvdmVyLiBXZSBuZWVkIHRvIGFjY3VtdWxhdGUgY29udGVudFxuICAgICAgLy8gYmV0d2VlbiBmbGFncyB0byBtYWtlIHN1cmUgd2Ugd3JhcCB0aGUgY2h1bmsgbGF0ZXIgaW4gOm5vdCgpIHNlbGVjdG9yIGlmIG5lZWRlZCwgZS5nLlxuICAgICAgLy8gYGBgXG4gICAgICAvLyAgWycnLCBGbGFncy5DTEFTUywgJy5jbGFzc0EnLCBGbGFncy5DTEFTUyB8IEZsYWdzLk5PVCwgJy5jbGFzc0InLCAnLmNsYXNzQyddXG4gICAgICAvLyBgYGBcbiAgICAgIC8vIHNob3VsZCBiZSB0cmFuc2Zvcm1lZCB0byBgLmNsYXNzQSA6bm90KC5jbGFzc0IgLmNsYXNzQylgLlxuICAgICAgLy9cbiAgICAgIC8vIE5vdGU6IGZvciBuZWdhdGl2ZSBzZWxlY3RvciBwYXJ0LCB3ZSBhY2N1bXVsYXRlIGNvbnRlbnQgYmV0d2VlbiBmbGFncyB1bnRpbCB3ZSBmaW5kIHRoZVxuICAgICAgLy8gbmV4dCBuZWdhdGl2ZSBmbGFnLiBUaGlzIGlzIG5lZWRlZCB0byBzdXBwb3J0IGEgY2FzZSB3aGVyZSBgOm5vdCgpYCBydWxlIGNvbnRhaW5zIG1vcmUgdGhhblxuICAgICAgLy8gb25lIGNodW5rLCBlLmcuIHRoZSBmb2xsb3dpbmcgc2VsZWN0b3I6XG4gICAgICAvLyBgYGBcbiAgICAgIC8vICBbJycsIEZsYWdzLkVMRU1FTlQgfCBGbGFncy5OT1QsICdwJywgRmxhZ3MuQ0xBU1MsICdmb28nLCBGbGFncy5DTEFTUyB8IEZsYWdzLk5PVCwgJ2JhciddXG4gICAgICAvLyBgYGBcbiAgICAgIC8vIHNob3VsZCBiZSBzdHJpbmdpZmllZCB0byBgOm5vdChwLmZvbykgOm5vdCguYmFyKWBcbiAgICAgIC8vXG4gICAgICBpZiAoY3VycmVudENodW5rICE9PSAnJyAmJiAhaXNQb3NpdGl2ZSh2YWx1ZU9yTWFya2VyKSkge1xuICAgICAgICByZXN1bHQgKz0gbWF5YmVXcmFwSW5Ob3RTZWxlY3Rvcihpc05lZ2F0aXZlTW9kZSwgY3VycmVudENodW5rKTtcbiAgICAgICAgY3VycmVudENodW5rID0gJyc7XG4gICAgICB9XG4gICAgICBtb2RlID0gdmFsdWVPck1hcmtlcjtcbiAgICAgIC8vIEFjY29yZGluZyB0byBDc3NTZWxlY3RvciBzcGVjLCBvbmNlIHdlIGNvbWUgYWNyb3NzIGBTZWxlY3RvckZsYWdzLk5PVGAgZmxhZywgdGhlIG5lZ2F0aXZlXG4gICAgICAvLyBtb2RlIGlzIG1haW50YWluZWQgZm9yIHJlbWFpbmluZyBjaHVua3Mgb2YgYSBzZWxlY3Rvci5cbiAgICAgIGlzTmVnYXRpdmVNb2RlID0gaXNOZWdhdGl2ZU1vZGUgfHwgIWlzUG9zaXRpdmUobW9kZSk7XG4gICAgfVxuICAgIGkrKztcbiAgfVxuICBpZiAoY3VycmVudENodW5rICE9PSAnJykge1xuICAgIHJlc3VsdCArPSBtYXliZVdyYXBJbk5vdFNlbGVjdG9yKGlzTmVnYXRpdmVNb2RlLCBjdXJyZW50Q2h1bmspO1xuICB9XG4gIHJldHVybiByZXN1bHQ7XG59XG5cbi8qKlxuICogR2VuZXJhdGVzIHN0cmluZyByZXByZXNlbnRhdGlvbiBvZiBDU1Mgc2VsZWN0b3IgaW4gcGFyc2VkIGZvcm0uXG4gKlxuICogQ29tcG9uZW50RGVmIGFuZCBEaXJlY3RpdmVEZWYgYXJlIGdlbmVyYXRlZCB3aXRoIHRoZSBzZWxlY3RvciBpbiBwYXJzZWQgZm9ybSB0byBhdm9pZCBkb2luZ1xuICogYWRkaXRpb25hbCBwYXJzaW5nIGF0IHJ1bnRpbWUgKGZvciBleGFtcGxlLCBmb3IgZGlyZWN0aXZlIG1hdGNoaW5nKS4gSG93ZXZlciBpbiBzb21lIGNhc2VzIChmb3JcbiAqIGV4YW1wbGUsIHdoaWxlIGJvb3RzdHJhcHBpbmcgYSBjb21wb25lbnQpLCBhIHN0cmluZyB2ZXJzaW9uIG9mIHRoZSBzZWxlY3RvciBpcyByZXF1aXJlZCB0byBxdWVyeVxuICogZm9yIHRoZSBob3N0IGVsZW1lbnQgb24gdGhlIHBhZ2UuIFRoaXMgZnVuY3Rpb24gdGFrZXMgdGhlIHBhcnNlZCBmb3JtIG9mIGEgc2VsZWN0b3IgYW5kIHJldHVybnNcbiAqIGl0cyBzdHJpbmcgcmVwcmVzZW50YXRpb24uXG4gKlxuICogQHBhcmFtIHNlbGVjdG9yTGlzdCBzZWxlY3RvciBpbiBwYXJzZWQgZm9ybVxuICogQHJldHVybnMgc3RyaW5nIHJlcHJlc2VudGF0aW9uIG9mIGEgZ2l2ZW4gc2VsZWN0b3JcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHN0cmluZ2lmeUNTU1NlbGVjdG9yTGlzdChzZWxlY3Rvckxpc3Q6IENzc1NlbGVjdG9yTGlzdCk6IHN0cmluZyB7XG4gIHJldHVybiBzZWxlY3Rvckxpc3QubWFwKHN0cmluZ2lmeUNTU1NlbGVjdG9yKS5qb2luKCcsJyk7XG59XG5cbi8qKlxuICogRXh0cmFjdHMgYXR0cmlidXRlcyBhbmQgY2xhc3NlcyBpbmZvcm1hdGlvbiBmcm9tIGEgZ2l2ZW4gQ1NTIHNlbGVjdG9yLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gaXMgdXNlZCB3aGlsZSBjcmVhdGluZyBhIGNvbXBvbmVudCBkeW5hbWljYWxseS4gSW4gdGhpcyBjYXNlLCB0aGUgaG9zdCBlbGVtZW50XG4gKiAodGhhdCBpcyBjcmVhdGVkIGR5bmFtaWNhbGx5KSBzaG91bGQgY29udGFpbiBhdHRyaWJ1dGVzIGFuZCBjbGFzc2VzIHNwZWNpZmllZCBpbiBjb21wb25lbnQncyBDU1NcbiAqIHNlbGVjdG9yLlxuICpcbiAqIEBwYXJhbSBzZWxlY3RvciBDU1Mgc2VsZWN0b3IgaW4gcGFyc2VkIGZvcm0gKGluIGEgZm9ybSBvZiBhcnJheSlcbiAqIEByZXR1cm5zIG9iamVjdCB3aXRoIGBhdHRyc2AgYW5kIGBjbGFzc2VzYCBmaWVsZHMgdGhhdCBjb250YWluIGV4dHJhY3RlZCBpbmZvcm1hdGlvblxuICovXG5leHBvcnQgZnVuY3Rpb24gZXh0cmFjdEF0dHJzQW5kQ2xhc3Nlc0Zyb21TZWxlY3RvcihzZWxlY3RvcjogQ3NzU2VsZWN0b3IpOlxuICAgIHthdHRyczogc3RyaW5nW10sIGNsYXNzZXM6IHN0cmluZ1tdfSB7XG4gIGNvbnN0IGF0dHJzOiBzdHJpbmdbXSA9IFtdO1xuICBjb25zdCBjbGFzc2VzOiBzdHJpbmdbXSA9IFtdO1xuICBsZXQgaSA9IDE7XG4gIGxldCBtb2RlID0gU2VsZWN0b3JGbGFncy5BVFRSSUJVVEU7XG4gIHdoaWxlIChpIDwgc2VsZWN0b3IubGVuZ3RoKSB7XG4gICAgbGV0IHZhbHVlT3JNYXJrZXIgPSBzZWxlY3RvcltpXTtcbiAgICBpZiAodHlwZW9mIHZhbHVlT3JNYXJrZXIgPT09ICdzdHJpbmcnKSB7XG4gICAgICBpZiAobW9kZSA9PT0gU2VsZWN0b3JGbGFncy5BVFRSSUJVVEUpIHtcbiAgICAgICAgaWYgKHZhbHVlT3JNYXJrZXIgIT09ICcnKSB7XG4gICAgICAgICAgYXR0cnMucHVzaCh2YWx1ZU9yTWFya2VyLCBzZWxlY3RvclsrK2ldIGFzIHN0cmluZyk7XG4gICAgICAgIH1cbiAgICAgIH0gZWxzZSBpZiAobW9kZSA9PT0gU2VsZWN0b3JGbGFncy5DTEFTUykge1xuICAgICAgICBjbGFzc2VzLnB1c2godmFsdWVPck1hcmtlcik7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vIEFjY29yZGluZyB0byBDc3NTZWxlY3RvciBzcGVjLCBvbmNlIHdlIGNvbWUgYWNyb3NzIGBTZWxlY3RvckZsYWdzLk5PVGAgZmxhZywgdGhlIG5lZ2F0aXZlXG4gICAgICAvLyBtb2RlIGlzIG1haW50YWluZWQgZm9yIHJlbWFpbmluZyBjaHVua3Mgb2YgYSBzZWxlY3Rvci4gU2luY2UgYXR0cmlidXRlcyBhbmQgY2xhc3NlcyBhcmVcbiAgICAgIC8vIGV4dHJhY3RlZCBvbmx5IGZvciBcInBvc2l0aXZlXCIgcGFydCBvZiB0aGUgc2VsZWN0b3IsIHdlIGNhbiBzdG9wIGhlcmUuXG4gICAgICBpZiAoIWlzUG9zaXRpdmUobW9kZSkpIGJyZWFrO1xuICAgICAgbW9kZSA9IHZhbHVlT3JNYXJrZXI7XG4gICAgfVxuICAgIGkrKztcbiAgfVxuICByZXR1cm4ge2F0dHJzLCBjbGFzc2VzfTtcbn1cbiJdfQ==