/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/utils/schematics_prompt", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getInquirer = exports.supportsPrompt = void 0;
    let resolvedInquirerModule;
    try {
        // "inquirer" is the prompt module also used by the devkit schematics CLI
        // in order to show prompts for schematics. We transitively depend on this
        // module, but don't want to throw an exception if the module is not
        // installed for some reason. In that case prompts are just not supported.
        resolvedInquirerModule = require('inquirer');
    }
    catch (e) {
        resolvedInquirerModule = null;
    }
    /** Whether prompts are currently supported. */
    function supportsPrompt() {
        return !!resolvedInquirerModule && !!process.stdin.isTTY;
    }
    exports.supportsPrompt = supportsPrompt;
    /**
     * Gets the resolved instance of "inquirer" which can be used to programmatically
     * create prompts.
     */
    function getInquirer() {
        return resolvedInquirerModule;
    }
    exports.getInquirer = getInquirer;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2NoZW1hdGljc19wcm9tcHQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvdXRpbHMvc2NoZW1hdGljc19wcm9tcHQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBSUgsSUFBSSxzQkFBcUMsQ0FBQztJQUUxQyxJQUFJO1FBQ0YseUVBQXlFO1FBQ3pFLDBFQUEwRTtRQUMxRSxvRUFBb0U7UUFDcEUsMEVBQTBFO1FBQzFFLHNCQUFzQixHQUFHLE9BQU8sQ0FBQyxVQUFVLENBQUMsQ0FBQztLQUM5QztJQUFDLE9BQU8sQ0FBQyxFQUFFO1FBQ1Ysc0JBQXNCLEdBQUcsSUFBSSxDQUFDO0tBQy9CO0lBRUQsK0NBQStDO0lBQy9DLFNBQWdCLGNBQWM7UUFDNUIsT0FBTyxDQUFDLENBQUMsc0JBQXNCLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDO0lBQzNELENBQUM7SUFGRCx3Q0FFQztJQUVEOzs7T0FHRztJQUNILFNBQWdCLFdBQVc7UUFDekIsT0FBTyxzQkFBdUIsQ0FBQztJQUNqQyxDQUFDO0lBRkQsa0NBRUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxudHlwZSBJbnF1aXJlciA9IHR5cGVvZiBpbXBvcnQoJ2lucXVpcmVyJyk7XG5cbmxldCByZXNvbHZlZElucXVpcmVyTW9kdWxlOiBJbnF1aXJlcnxudWxsO1xuXG50cnkge1xuICAvLyBcImlucXVpcmVyXCIgaXMgdGhlIHByb21wdCBtb2R1bGUgYWxzbyB1c2VkIGJ5IHRoZSBkZXZraXQgc2NoZW1hdGljcyBDTElcbiAgLy8gaW4gb3JkZXIgdG8gc2hvdyBwcm9tcHRzIGZvciBzY2hlbWF0aWNzLiBXZSB0cmFuc2l0aXZlbHkgZGVwZW5kIG9uIHRoaXNcbiAgLy8gbW9kdWxlLCBidXQgZG9uJ3Qgd2FudCB0byB0aHJvdyBhbiBleGNlcHRpb24gaWYgdGhlIG1vZHVsZSBpcyBub3RcbiAgLy8gaW5zdGFsbGVkIGZvciBzb21lIHJlYXNvbi4gSW4gdGhhdCBjYXNlIHByb21wdHMgYXJlIGp1c3Qgbm90IHN1cHBvcnRlZC5cbiAgcmVzb2x2ZWRJbnF1aXJlck1vZHVsZSA9IHJlcXVpcmUoJ2lucXVpcmVyJyk7XG59IGNhdGNoIChlKSB7XG4gIHJlc29sdmVkSW5xdWlyZXJNb2R1bGUgPSBudWxsO1xufVxuXG4vKiogV2hldGhlciBwcm9tcHRzIGFyZSBjdXJyZW50bHkgc3VwcG9ydGVkLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHN1cHBvcnRzUHJvbXB0KCk6IGJvb2xlYW4ge1xuICByZXR1cm4gISFyZXNvbHZlZElucXVpcmVyTW9kdWxlICYmICEhcHJvY2Vzcy5zdGRpbi5pc1RUWTtcbn1cblxuLyoqXG4gKiBHZXRzIHRoZSByZXNvbHZlZCBpbnN0YW5jZSBvZiBcImlucXVpcmVyXCIgd2hpY2ggY2FuIGJlIHVzZWQgdG8gcHJvZ3JhbW1hdGljYWxseVxuICogY3JlYXRlIHByb21wdHMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRJbnF1aXJlcigpOiBJbnF1aXJlciB7XG4gIHJldHVybiByZXNvbHZlZElucXVpcmVyTW9kdWxlITtcbn1cbiJdfQ==