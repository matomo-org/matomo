/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Defines template and style encapsulation options available for Component's {@link Component}.
 *
 * See {@link Component#encapsulation encapsulation}.
 *
 * @usageNotes
 * ### Example
 *
 * {@example core/ts/metadata/encapsulation.ts region='longform'}
 *
 * @publicApi
 */
export var ViewEncapsulation;
(function (ViewEncapsulation) {
    /**
     * Emulate `Native` scoping of styles by adding an attribute containing surrogate id to the Host
     * Element and pre-processing the style rules provided via {@link Component#styles styles} or
     * {@link Component#styleUrls styleUrls}, and adding the new Host Element attribute to all
     * selectors.
     *
     * This is the default option.
     */
    ViewEncapsulation[ViewEncapsulation["Emulated"] = 0] = "Emulated";
    // Historically the 1 value was for `Native` encapsulation which has been removed as of v11.
    /**
     * Don't provide any template or style encapsulation.
     */
    ViewEncapsulation[ViewEncapsulation["None"] = 2] = "None";
    /**
     * Use Shadow DOM to encapsulate styles.
     *
     * For the DOM this means using modern [Shadow
     * DOM](https://developer.mozilla.org/en-US/docs/Web/Web_Components/Using_shadow_DOM) and
     * creating a ShadowRoot for Component's Host Element.
     */
    ViewEncapsulation[ViewEncapsulation["ShadowDom"] = 3] = "ShadowDom";
})(ViewEncapsulation || (ViewEncapsulation = {}));
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidmlldy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL21ldGFkYXRhL3ZpZXcudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUg7Ozs7Ozs7Ozs7O0dBV0c7QUFDSCxNQUFNLENBQU4sSUFBWSxpQkEwQlg7QUExQkQsV0FBWSxpQkFBaUI7SUFDM0I7Ozs7Ozs7T0FPRztJQUNILGlFQUFZLENBQUE7SUFFWiw0RkFBNEY7SUFFNUY7O09BRUc7SUFDSCx5REFBUSxDQUFBO0lBRVI7Ozs7OztPQU1HO0lBQ0gsbUVBQWEsQ0FBQTtBQUNmLENBQUMsRUExQlcsaUJBQWlCLEtBQWpCLGlCQUFpQixRQTBCNUIiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBEZWZpbmVzIHRlbXBsYXRlIGFuZCBzdHlsZSBlbmNhcHN1bGF0aW9uIG9wdGlvbnMgYXZhaWxhYmxlIGZvciBDb21wb25lbnQncyB7QGxpbmsgQ29tcG9uZW50fS5cbiAqXG4gKiBTZWUge0BsaW5rIENvbXBvbmVudCNlbmNhcHN1bGF0aW9uIGVuY2Fwc3VsYXRpb259LlxuICpcbiAqIEB1c2FnZU5vdGVzXG4gKiAjIyMgRXhhbXBsZVxuICpcbiAqIHtAZXhhbXBsZSBjb3JlL3RzL21ldGFkYXRhL2VuY2Fwc3VsYXRpb24udHMgcmVnaW9uPSdsb25nZm9ybSd9XG4gKlxuICogQHB1YmxpY0FwaVxuICovXG5leHBvcnQgZW51bSBWaWV3RW5jYXBzdWxhdGlvbiB7XG4gIC8qKlxuICAgKiBFbXVsYXRlIGBOYXRpdmVgIHNjb3Bpbmcgb2Ygc3R5bGVzIGJ5IGFkZGluZyBhbiBhdHRyaWJ1dGUgY29udGFpbmluZyBzdXJyb2dhdGUgaWQgdG8gdGhlIEhvc3RcbiAgICogRWxlbWVudCBhbmQgcHJlLXByb2Nlc3NpbmcgdGhlIHN0eWxlIHJ1bGVzIHByb3ZpZGVkIHZpYSB7QGxpbmsgQ29tcG9uZW50I3N0eWxlcyBzdHlsZXN9IG9yXG4gICAqIHtAbGluayBDb21wb25lbnQjc3R5bGVVcmxzIHN0eWxlVXJsc30sIGFuZCBhZGRpbmcgdGhlIG5ldyBIb3N0IEVsZW1lbnQgYXR0cmlidXRlIHRvIGFsbFxuICAgKiBzZWxlY3RvcnMuXG4gICAqXG4gICAqIFRoaXMgaXMgdGhlIGRlZmF1bHQgb3B0aW9uLlxuICAgKi9cbiAgRW11bGF0ZWQgPSAwLFxuXG4gIC8vIEhpc3RvcmljYWxseSB0aGUgMSB2YWx1ZSB3YXMgZm9yIGBOYXRpdmVgIGVuY2Fwc3VsYXRpb24gd2hpY2ggaGFzIGJlZW4gcmVtb3ZlZCBhcyBvZiB2MTEuXG5cbiAgLyoqXG4gICAqIERvbid0IHByb3ZpZGUgYW55IHRlbXBsYXRlIG9yIHN0eWxlIGVuY2Fwc3VsYXRpb24uXG4gICAqL1xuICBOb25lID0gMixcblxuICAvKipcbiAgICogVXNlIFNoYWRvdyBET00gdG8gZW5jYXBzdWxhdGUgc3R5bGVzLlxuICAgKlxuICAgKiBGb3IgdGhlIERPTSB0aGlzIG1lYW5zIHVzaW5nIG1vZGVybiBbU2hhZG93XG4gICAqIERPTV0oaHR0cHM6Ly9kZXZlbG9wZXIubW96aWxsYS5vcmcvZW4tVVMvZG9jcy9XZWIvV2ViX0NvbXBvbmVudHMvVXNpbmdfc2hhZG93X0RPTSkgYW5kXG4gICAqIGNyZWF0aW5nIGEgU2hhZG93Um9vdCBmb3IgQ29tcG9uZW50J3MgSG9zdCBFbGVtZW50LlxuICAgKi9cbiAgU2hhZG93RG9tID0gM1xufVxuIl19