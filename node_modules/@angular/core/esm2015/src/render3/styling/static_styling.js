/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { concatStringsWithSpace } from '../../util/stringify';
import { assertFirstCreatePass } from '../assert';
import { getTView } from '../state';
/**
 * Compute the static styling (class/style) from `TAttributes`.
 *
 * This function should be called during `firstCreatePass` only.
 *
 * @param tNode The `TNode` into which the styling information should be loaded.
 * @param attrs `TAttributes` containing the styling information.
 * @param writeToHost Where should the resulting static styles be written?
 *   - `false` Write to `TNode.stylesWithoutHost` / `TNode.classesWithoutHost`
 *   - `true` Write to `TNode.styles` / `TNode.classes`
 */
export function computeStaticStyling(tNode, attrs, writeToHost) {
    ngDevMode &&
        assertFirstCreatePass(getTView(), 'Expecting to be called in first template pass only');
    let styles = writeToHost ? tNode.styles : null;
    let classes = writeToHost ? tNode.classes : null;
    let mode = 0;
    if (attrs !== null) {
        for (let i = 0; i < attrs.length; i++) {
            const value = attrs[i];
            if (typeof value === 'number') {
                mode = value;
            }
            else if (mode == 1 /* Classes */) {
                classes = concatStringsWithSpace(classes, value);
            }
            else if (mode == 2 /* Styles */) {
                const style = value;
                const styleValue = attrs[++i];
                styles = concatStringsWithSpace(styles, style + ': ' + styleValue + ';');
            }
        }
    }
    writeToHost ? tNode.styles = styles : tNode.stylesWithoutHost = styles;
    writeToHost ? tNode.classes = classes : tNode.classesWithoutHost = classes;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3RhdGljX3N0eWxpbmcuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL3N0eWxpbmcvc3RhdGljX3N0eWxpbmcudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUgsT0FBTyxFQUFDLHNCQUFzQixFQUFDLE1BQU0sc0JBQXNCLENBQUM7QUFDNUQsT0FBTyxFQUFDLHFCQUFxQixFQUFDLE1BQU0sV0FBVyxDQUFDO0FBRWhELE9BQU8sRUFBQyxRQUFRLEVBQUMsTUFBTSxVQUFVLENBQUM7QUFFbEM7Ozs7Ozs7Ozs7R0FVRztBQUNILE1BQU0sVUFBVSxvQkFBb0IsQ0FDaEMsS0FBWSxFQUFFLEtBQXVCLEVBQUUsV0FBb0I7SUFDN0QsU0FBUztRQUNMLHFCQUFxQixDQUFDLFFBQVEsRUFBRSxFQUFFLG9EQUFvRCxDQUFDLENBQUM7SUFDNUYsSUFBSSxNQUFNLEdBQWdCLFdBQVcsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDO0lBQzVELElBQUksT0FBTyxHQUFnQixXQUFXLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUM5RCxJQUFJLElBQUksR0FBc0IsQ0FBQyxDQUFDO0lBQ2hDLElBQUksS0FBSyxLQUFLLElBQUksRUFBRTtRQUNsQixLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsS0FBSyxDQUFDLE1BQU0sRUFBRSxDQUFDLEVBQUUsRUFBRTtZQUNyQyxNQUFNLEtBQUssR0FBRyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFDdkIsSUFBSSxPQUFPLEtBQUssS0FBSyxRQUFRLEVBQUU7Z0JBQzdCLElBQUksR0FBRyxLQUFLLENBQUM7YUFDZDtpQkFBTSxJQUFJLElBQUksbUJBQTJCLEVBQUU7Z0JBQzFDLE9BQU8sR0FBRyxzQkFBc0IsQ0FBQyxPQUFPLEVBQUUsS0FBZSxDQUFDLENBQUM7YUFDNUQ7aUJBQU0sSUFBSSxJQUFJLGtCQUEwQixFQUFFO2dCQUN6QyxNQUFNLEtBQUssR0FBRyxLQUFlLENBQUM7Z0JBQzlCLE1BQU0sVUFBVSxHQUFHLEtBQUssQ0FBQyxFQUFFLENBQUMsQ0FBVyxDQUFDO2dCQUN4QyxNQUFNLEdBQUcsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEtBQUssR0FBRyxJQUFJLEdBQUcsVUFBVSxHQUFHLEdBQUcsQ0FBQyxDQUFDO2FBQzFFO1NBQ0Y7S0FDRjtJQUNELFdBQVcsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE1BQU0sR0FBRyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxpQkFBaUIsR0FBRyxNQUFNLENBQUM7SUFDdkUsV0FBVyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxHQUFHLE9BQU8sQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLGtCQUFrQixHQUFHLE9BQU8sQ0FBQztBQUM3RSxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Y29uY2F0U3RyaW5nc1dpdGhTcGFjZX0gZnJvbSAnLi4vLi4vdXRpbC9zdHJpbmdpZnknO1xuaW1wb3J0IHthc3NlcnRGaXJzdENyZWF0ZVBhc3N9IGZyb20gJy4uL2Fzc2VydCc7XG5pbXBvcnQge0F0dHJpYnV0ZU1hcmtlciwgVEF0dHJpYnV0ZXMsIFROb2RlfSBmcm9tICcuLi9pbnRlcmZhY2VzL25vZGUnO1xuaW1wb3J0IHtnZXRUVmlld30gZnJvbSAnLi4vc3RhdGUnO1xuXG4vKipcbiAqIENvbXB1dGUgdGhlIHN0YXRpYyBzdHlsaW5nIChjbGFzcy9zdHlsZSkgZnJvbSBgVEF0dHJpYnV0ZXNgLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gc2hvdWxkIGJlIGNhbGxlZCBkdXJpbmcgYGZpcnN0Q3JlYXRlUGFzc2Agb25seS5cbiAqXG4gKiBAcGFyYW0gdE5vZGUgVGhlIGBUTm9kZWAgaW50byB3aGljaCB0aGUgc3R5bGluZyBpbmZvcm1hdGlvbiBzaG91bGQgYmUgbG9hZGVkLlxuICogQHBhcmFtIGF0dHJzIGBUQXR0cmlidXRlc2AgY29udGFpbmluZyB0aGUgc3R5bGluZyBpbmZvcm1hdGlvbi5cbiAqIEBwYXJhbSB3cml0ZVRvSG9zdCBXaGVyZSBzaG91bGQgdGhlIHJlc3VsdGluZyBzdGF0aWMgc3R5bGVzIGJlIHdyaXR0ZW4/XG4gKiAgIC0gYGZhbHNlYCBXcml0ZSB0byBgVE5vZGUuc3R5bGVzV2l0aG91dEhvc3RgIC8gYFROb2RlLmNsYXNzZXNXaXRob3V0SG9zdGBcbiAqICAgLSBgdHJ1ZWAgV3JpdGUgdG8gYFROb2RlLnN0eWxlc2AgLyBgVE5vZGUuY2xhc3Nlc2BcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbXB1dGVTdGF0aWNTdHlsaW5nKFxuICAgIHROb2RlOiBUTm9kZSwgYXR0cnM6IFRBdHRyaWJ1dGVzfG51bGwsIHdyaXRlVG9Ib3N0OiBib29sZWFuKTogdm9pZCB7XG4gIG5nRGV2TW9kZSAmJlxuICAgICAgYXNzZXJ0Rmlyc3RDcmVhdGVQYXNzKGdldFRWaWV3KCksICdFeHBlY3RpbmcgdG8gYmUgY2FsbGVkIGluIGZpcnN0IHRlbXBsYXRlIHBhc3Mgb25seScpO1xuICBsZXQgc3R5bGVzOiBzdHJpbmd8bnVsbCA9IHdyaXRlVG9Ib3N0ID8gdE5vZGUuc3R5bGVzIDogbnVsbDtcbiAgbGV0IGNsYXNzZXM6IHN0cmluZ3xudWxsID0gd3JpdGVUb0hvc3QgPyB0Tm9kZS5jbGFzc2VzIDogbnVsbDtcbiAgbGV0IG1vZGU6IEF0dHJpYnV0ZU1hcmtlcnwwID0gMDtcbiAgaWYgKGF0dHJzICE9PSBudWxsKSB7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBhdHRycy5sZW5ndGg7IGkrKykge1xuICAgICAgY29uc3QgdmFsdWUgPSBhdHRyc1tpXTtcbiAgICAgIGlmICh0eXBlb2YgdmFsdWUgPT09ICdudW1iZXInKSB7XG4gICAgICAgIG1vZGUgPSB2YWx1ZTtcbiAgICAgIH0gZWxzZSBpZiAobW9kZSA9PSBBdHRyaWJ1dGVNYXJrZXIuQ2xhc3Nlcykge1xuICAgICAgICBjbGFzc2VzID0gY29uY2F0U3RyaW5nc1dpdGhTcGFjZShjbGFzc2VzLCB2YWx1ZSBhcyBzdHJpbmcpO1xuICAgICAgfSBlbHNlIGlmIChtb2RlID09IEF0dHJpYnV0ZU1hcmtlci5TdHlsZXMpIHtcbiAgICAgICAgY29uc3Qgc3R5bGUgPSB2YWx1ZSBhcyBzdHJpbmc7XG4gICAgICAgIGNvbnN0IHN0eWxlVmFsdWUgPSBhdHRyc1srK2ldIGFzIHN0cmluZztcbiAgICAgICAgc3R5bGVzID0gY29uY2F0U3RyaW5nc1dpdGhTcGFjZShzdHlsZXMsIHN0eWxlICsgJzogJyArIHN0eWxlVmFsdWUgKyAnOycpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuICB3cml0ZVRvSG9zdCA/IHROb2RlLnN0eWxlcyA9IHN0eWxlcyA6IHROb2RlLnN0eWxlc1dpdGhvdXRIb3N0ID0gc3R5bGVzO1xuICB3cml0ZVRvSG9zdCA/IHROb2RlLmNsYXNzZXMgPSBjbGFzc2VzIDogdE5vZGUuY2xhc3Nlc1dpdGhvdXRIb3N0ID0gY2xhc3Nlcztcbn1cbiJdfQ==