(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/render3/partial/component", ["require", "exports", "tslib", "@angular/compiler/src/core", "@angular/compiler/src/ml_parser/interpolation_config", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/parse_util", "@angular/compiler/src/render3/r3_identifiers", "@angular/compiler/src/render3/view/compiler", "@angular/compiler/src/render3/view/util", "@angular/compiler/src/render3/partial/directive", "@angular/compiler/src/render3/partial/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createComponentDefinitionMap = exports.compileDeclareComponentFromMetadata = void 0;
    var tslib_1 = require("tslib");
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var core = require("@angular/compiler/src/core");
    var interpolation_config_1 = require("@angular/compiler/src/ml_parser/interpolation_config");
    var o = require("@angular/compiler/src/output/output_ast");
    var parse_util_1 = require("@angular/compiler/src/parse_util");
    var r3_identifiers_1 = require("@angular/compiler/src/render3/r3_identifiers");
    var compiler_1 = require("@angular/compiler/src/render3/view/compiler");
    var util_1 = require("@angular/compiler/src/render3/view/util");
    var directive_1 = require("@angular/compiler/src/render3/partial/directive");
    var util_2 = require("@angular/compiler/src/render3/partial/util");
    /**
     * Compile a component declaration defined by the `R3ComponentMetadata`.
     */
    function compileDeclareComponentFromMetadata(meta, template) {
        var definitionMap = createComponentDefinitionMap(meta, template);
        var expression = o.importExpr(r3_identifiers_1.Identifiers.declareComponent).callFn([definitionMap.toLiteralMap()]);
        var type = compiler_1.createComponentType(meta);
        return { expression: expression, type: type };
    }
    exports.compileDeclareComponentFromMetadata = compileDeclareComponentFromMetadata;
    /**
     * Gathers the declaration fields for a component into a `DefinitionMap`.
     */
    function createComponentDefinitionMap(meta, template) {
        var definitionMap = directive_1.createDirectiveDefinitionMap(meta);
        definitionMap.set('template', getTemplateExpression(template));
        if (template.isInline) {
            definitionMap.set('isInline', o.literal(true));
        }
        definitionMap.set('styles', util_2.toOptionalLiteralArray(meta.styles, o.literal));
        definitionMap.set('directives', compileUsedDirectiveMetadata(meta));
        definitionMap.set('pipes', compileUsedPipeMetadata(meta));
        definitionMap.set('viewProviders', meta.viewProviders);
        definitionMap.set('animations', meta.animations);
        if (meta.changeDetection !== undefined) {
            definitionMap.set('changeDetection', o.importExpr(r3_identifiers_1.Identifiers.ChangeDetectionStrategy)
                .prop(core.ChangeDetectionStrategy[meta.changeDetection]));
        }
        if (meta.encapsulation !== core.ViewEncapsulation.Emulated) {
            definitionMap.set('encapsulation', o.importExpr(r3_identifiers_1.Identifiers.ViewEncapsulation).prop(core.ViewEncapsulation[meta.encapsulation]));
        }
        if (meta.interpolation !== interpolation_config_1.DEFAULT_INTERPOLATION_CONFIG) {
            definitionMap.set('interpolation', o.literalArr([o.literal(meta.interpolation.start), o.literal(meta.interpolation.end)]));
        }
        if (template.preserveWhitespaces === true) {
            definitionMap.set('preserveWhitespaces', o.literal(true));
        }
        return definitionMap;
    }
    exports.createComponentDefinitionMap = createComponentDefinitionMap;
    function getTemplateExpression(template) {
        if (typeof template.template === 'string') {
            if (template.isInline) {
                // The template is inline but not a simple literal string, so give up with trying to
                // source-map it and just return a simple literal here.
                return o.literal(template.template);
            }
            else {
                // The template is external so we must synthesize an expression node with the appropriate
                // source-span.
                var contents = template.template;
                var file = new parse_util_1.ParseSourceFile(contents, template.templateUrl);
                var start = new parse_util_1.ParseLocation(file, 0, 0, 0);
                var end = computeEndLocation(file, contents);
                var span = new parse_util_1.ParseSourceSpan(start, end);
                return o.literal(contents, null, span);
            }
        }
        else {
            // The template is inline so we can just reuse the current expression node.
            return template.template;
        }
    }
    function computeEndLocation(file, contents) {
        var length = contents.length;
        var lineStart = 0;
        var lastLineStart = 0;
        var line = 0;
        do {
            lineStart = contents.indexOf('\n', lastLineStart);
            if (lineStart !== -1) {
                lastLineStart = lineStart + 1;
                line++;
            }
        } while (lineStart !== -1);
        return new parse_util_1.ParseLocation(file, length, line, length - lastLineStart);
    }
    /**
     * Compiles the directives as registered in the component metadata into an array literal of the
     * individual directives. If the component does not use any directives, then null is returned.
     */
    function compileUsedDirectiveMetadata(meta) {
        var wrapType = meta.declarationListEmitMode !== 0 /* Direct */ ?
            generateForwardRef :
            function (expr) { return expr; };
        return util_2.toOptionalLiteralArray(meta.directives, function (directive) {
            var dirMeta = new util_1.DefinitionMap();
            dirMeta.set('type', wrapType(directive.type));
            dirMeta.set('selector', o.literal(directive.selector));
            dirMeta.set('inputs', util_2.toOptionalLiteralArray(directive.inputs, o.literal));
            dirMeta.set('outputs', util_2.toOptionalLiteralArray(directive.outputs, o.literal));
            dirMeta.set('exportAs', util_2.toOptionalLiteralArray(directive.exportAs, o.literal));
            return dirMeta.toLiteralMap();
        });
    }
    /**
     * Compiles the pipes as registered in the component metadata into an object literal, where the
     * pipe's name is used as key and a reference to its type as value. If the component does not use
     * any pipes, then null is returned.
     */
    function compileUsedPipeMetadata(meta) {
        var e_1, _a;
        if (meta.pipes.size === 0) {
            return null;
        }
        var wrapType = meta.declarationListEmitMode !== 0 /* Direct */ ?
            generateForwardRef :
            function (expr) { return expr; };
        var entries = [];
        try {
            for (var _b = tslib_1.__values(meta.pipes), _c = _b.next(); !_c.done; _c = _b.next()) {
                var _d = tslib_1.__read(_c.value, 2), name_1 = _d[0], pipe = _d[1];
                entries.push({ key: name_1, value: wrapType(pipe), quoted: true });
            }
        }
        catch (e_1_1) { e_1 = { error: e_1_1 }; }
        finally {
            try {
                if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
            }
            finally { if (e_1) throw e_1.error; }
        }
        return o.literalMap(entries);
    }
    function generateForwardRef(expr) {
        return o.importExpr(r3_identifiers_1.Identifiers.forwardRef).callFn([o.fn([], [new o.ReturnStatement(expr)])]);
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29tcG9uZW50LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3JlbmRlcjMvcGFydGlhbC9jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7OztJQUFBOzs7Ozs7T0FNRztJQUNILGlEQUFtQztJQUNuQyw2RkFBa0Y7SUFDbEYsMkRBQTZDO0lBQzdDLCtEQUFpRjtJQUNqRiwrRUFBb0Q7SUFFcEQsd0VBQXFEO0lBRXJELGdFQUEyQztJQUczQyw2RUFBeUQ7SUFDekQsbUVBQThDO0lBRzlDOztPQUVHO0lBQ0gsU0FBZ0IsbUNBQW1DLENBQy9DLElBQXlCLEVBQUUsUUFBd0I7UUFDckQsSUFBTSxhQUFhLEdBQUcsNEJBQTRCLENBQUMsSUFBSSxFQUFFLFFBQVEsQ0FBQyxDQUFDO1FBRW5FLElBQU0sVUFBVSxHQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsNEJBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLGFBQWEsQ0FBQyxZQUFZLEVBQUUsQ0FBQyxDQUFDLENBQUM7UUFDNUYsSUFBTSxJQUFJLEdBQUcsOEJBQW1CLENBQUMsSUFBSSxDQUFDLENBQUM7UUFFdkMsT0FBTyxFQUFDLFVBQVUsWUFBQSxFQUFFLElBQUksTUFBQSxFQUFDLENBQUM7SUFDNUIsQ0FBQztJQVJELGtGQVFDO0lBRUQ7O09BRUc7SUFDSCxTQUFnQiw0QkFBNEIsQ0FBQyxJQUF5QixFQUFFLFFBQXdCO1FBRTlGLElBQU0sYUFBYSxHQUNmLHdDQUE0QixDQUFDLElBQUksQ0FBQyxDQUFDO1FBRXZDLGFBQWEsQ0FBQyxHQUFHLENBQUMsVUFBVSxFQUFFLHFCQUFxQixDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFDL0QsSUFBSSxRQUFRLENBQUMsUUFBUSxFQUFFO1lBQ3JCLGFBQWEsQ0FBQyxHQUFHLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztTQUNoRDtRQUVELGFBQWEsQ0FBQyxHQUFHLENBQUMsUUFBUSxFQUFFLDZCQUFzQixDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7UUFDNUUsYUFBYSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEVBQUUsNEJBQTRCLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztRQUNwRSxhQUFhLENBQUMsR0FBRyxDQUFDLE9BQU8sRUFBRSx1QkFBdUIsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1FBQzFELGFBQWEsQ0FBQyxHQUFHLENBQUMsZUFBZSxFQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQztRQUN2RCxhQUFhLENBQUMsR0FBRyxDQUFDLFlBQVksRUFBRSxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUM7UUFFakQsSUFBSSxJQUFJLENBQUMsZUFBZSxLQUFLLFNBQVMsRUFBRTtZQUN0QyxhQUFhLENBQUMsR0FBRyxDQUNiLGlCQUFpQixFQUNqQixDQUFDLENBQUMsVUFBVSxDQUFDLDRCQUFFLENBQUMsdUJBQXVCLENBQUM7aUJBQ25DLElBQUksQ0FBQyxJQUFJLENBQUMsdUJBQXVCLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQztTQUNwRTtRQUNELElBQUksSUFBSSxDQUFDLGFBQWEsS0FBSyxJQUFJLENBQUMsaUJBQWlCLENBQUMsUUFBUSxFQUFFO1lBQzFELGFBQWEsQ0FBQyxHQUFHLENBQ2IsZUFBZSxFQUNmLENBQUMsQ0FBQyxVQUFVLENBQUMsNEJBQUUsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsQ0FBQztTQUMxRjtRQUNELElBQUksSUFBSSxDQUFDLGFBQWEsS0FBSyxtREFBNEIsRUFBRTtZQUN2RCxhQUFhLENBQUMsR0FBRyxDQUNiLGVBQWUsRUFDZixDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztTQUM3RjtRQUVELElBQUksUUFBUSxDQUFDLG1CQUFtQixLQUFLLElBQUksRUFBRTtZQUN6QyxhQUFhLENBQUMsR0FBRyxDQUFDLHFCQUFxQixFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztTQUMzRDtRQUVELE9BQU8sYUFBYSxDQUFDO0lBQ3ZCLENBQUM7SUF0Q0Qsb0VBc0NDO0lBRUQsU0FBUyxxQkFBcUIsQ0FBQyxRQUF3QjtRQUNyRCxJQUFJLE9BQU8sUUFBUSxDQUFDLFFBQVEsS0FBSyxRQUFRLEVBQUU7WUFDekMsSUFBSSxRQUFRLENBQUMsUUFBUSxFQUFFO2dCQUNyQixvRkFBb0Y7Z0JBQ3BGLHVEQUF1RDtnQkFDdkQsT0FBTyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQzthQUNyQztpQkFBTTtnQkFDTCx5RkFBeUY7Z0JBQ3pGLGVBQWU7Z0JBQ2YsSUFBTSxRQUFRLEdBQUcsUUFBUSxDQUFDLFFBQVEsQ0FBQztnQkFDbkMsSUFBTSxJQUFJLEdBQUcsSUFBSSw0QkFBZSxDQUFDLFFBQVEsRUFBRSxRQUFRLENBQUMsV0FBVyxDQUFDLENBQUM7Z0JBQ2pFLElBQU0sS0FBSyxHQUFHLElBQUksMEJBQWEsQ0FBQyxJQUFJLEVBQUUsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQztnQkFDL0MsSUFBTSxHQUFHLEdBQUcsa0JBQWtCLENBQUMsSUFBSSxFQUFFLFFBQVEsQ0FBQyxDQUFDO2dCQUMvQyxJQUFNLElBQUksR0FBRyxJQUFJLDRCQUFlLENBQUMsS0FBSyxFQUFFLEdBQUcsQ0FBQyxDQUFDO2dCQUM3QyxPQUFPLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxFQUFFLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQzthQUN4QztTQUNGO2FBQU07WUFDTCwyRUFBMkU7WUFDM0UsT0FBTyxRQUFRLENBQUMsUUFBUSxDQUFDO1NBQzFCO0lBQ0gsQ0FBQztJQUVELFNBQVMsa0JBQWtCLENBQUMsSUFBcUIsRUFBRSxRQUFnQjtRQUNqRSxJQUFNLE1BQU0sR0FBRyxRQUFRLENBQUMsTUFBTSxDQUFDO1FBQy9CLElBQUksU0FBUyxHQUFHLENBQUMsQ0FBQztRQUNsQixJQUFJLGFBQWEsR0FBRyxDQUFDLENBQUM7UUFDdEIsSUFBSSxJQUFJLEdBQUcsQ0FBQyxDQUFDO1FBQ2IsR0FBRztZQUNELFNBQVMsR0FBRyxRQUFRLENBQUMsT0FBTyxDQUFDLElBQUksRUFBRSxhQUFhLENBQUMsQ0FBQztZQUNsRCxJQUFJLFNBQVMsS0FBSyxDQUFDLENBQUMsRUFBRTtnQkFDcEIsYUFBYSxHQUFHLFNBQVMsR0FBRyxDQUFDLENBQUM7Z0JBQzlCLElBQUksRUFBRSxDQUFDO2FBQ1I7U0FDRixRQUFRLFNBQVMsS0FBSyxDQUFDLENBQUMsRUFBRTtRQUUzQixPQUFPLElBQUksMEJBQWEsQ0FBQyxJQUFJLEVBQUUsTUFBTSxFQUFFLElBQUksRUFBRSxNQUFNLEdBQUcsYUFBYSxDQUFDLENBQUM7SUFDdkUsQ0FBQztJQUVEOzs7T0FHRztJQUNILFNBQVMsNEJBQTRCLENBQUMsSUFBeUI7UUFDN0QsSUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLHVCQUF1QixtQkFBbUMsQ0FBQyxDQUFDO1lBQzlFLGtCQUFrQixDQUFDLENBQUM7WUFDcEIsVUFBQyxJQUFrQixJQUFLLE9BQUEsSUFBSSxFQUFKLENBQUksQ0FBQztRQUVqQyxPQUFPLDZCQUFzQixDQUFDLElBQUksQ0FBQyxVQUFVLEVBQUUsVUFBQSxTQUFTO1lBQ3RELElBQU0sT0FBTyxHQUFHLElBQUksb0JBQWEsRUFBMkIsQ0FBQztZQUM3RCxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQU0sRUFBRSxRQUFRLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7WUFDOUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxVQUFVLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztZQUN2RCxPQUFPLENBQUMsR0FBRyxDQUFDLFFBQVEsRUFBRSw2QkFBc0IsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1lBQzNFLE9BQU8sQ0FBQyxHQUFHLENBQUMsU0FBUyxFQUFFLDZCQUFzQixDQUFDLFNBQVMsQ0FBQyxPQUFPLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7WUFDN0UsT0FBTyxDQUFDLEdBQUcsQ0FBQyxVQUFVLEVBQUUsNkJBQXNCLENBQUMsU0FBUyxDQUFDLFFBQVEsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztZQUMvRSxPQUFPLE9BQU8sQ0FBQyxZQUFZLEVBQUUsQ0FBQztRQUNoQyxDQUFDLENBQUMsQ0FBQztJQUNMLENBQUM7SUFFRDs7OztPQUlHO0lBQ0gsU0FBUyx1QkFBdUIsQ0FBQyxJQUF5Qjs7UUFDeEQsSUFBSSxJQUFJLENBQUMsS0FBSyxDQUFDLElBQUksS0FBSyxDQUFDLEVBQUU7WUFDekIsT0FBTyxJQUFJLENBQUM7U0FDYjtRQUVELElBQU0sUUFBUSxHQUFHLElBQUksQ0FBQyx1QkFBdUIsbUJBQW1DLENBQUMsQ0FBQztZQUM5RSxrQkFBa0IsQ0FBQyxDQUFDO1lBQ3BCLFVBQUMsSUFBa0IsSUFBSyxPQUFBLElBQUksRUFBSixDQUFJLENBQUM7UUFFakMsSUFBTSxPQUFPLEdBQUcsRUFBRSxDQUFDOztZQUNuQixLQUEyQixJQUFBLEtBQUEsaUJBQUEsSUFBSSxDQUFDLEtBQUssQ0FBQSxnQkFBQSw0QkFBRTtnQkFBNUIsSUFBQSxLQUFBLDJCQUFZLEVBQVgsTUFBSSxRQUFBLEVBQUUsSUFBSSxRQUFBO2dCQUNwQixPQUFPLENBQUMsSUFBSSxDQUFDLEVBQUMsR0FBRyxFQUFFLE1BQUksRUFBRSxLQUFLLEVBQUUsUUFBUSxDQUFDLElBQUksQ0FBQyxFQUFFLE1BQU0sRUFBRSxJQUFJLEVBQUMsQ0FBQyxDQUFDO2FBQ2hFOzs7Ozs7Ozs7UUFDRCxPQUFPLENBQUMsQ0FBQyxVQUFVLENBQUMsT0FBTyxDQUFDLENBQUM7SUFDL0IsQ0FBQztJQUVELFNBQVMsa0JBQWtCLENBQUMsSUFBa0I7UUFDNUMsT0FBTyxDQUFDLENBQUMsVUFBVSxDQUFDLDRCQUFFLENBQUMsVUFBVSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFFLEVBQUUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUN2RixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5pbXBvcnQgKiBhcyBjb3JlIGZyb20gJy4uLy4uL2NvcmUnO1xuaW1wb3J0IHtERUZBVUxUX0lOVEVSUE9MQVRJT05fQ09ORklHfSBmcm9tICcuLi8uLi9tbF9wYXJzZXIvaW50ZXJwb2xhdGlvbl9jb25maWcnO1xuaW1wb3J0ICogYXMgbyBmcm9tICcuLi8uLi9vdXRwdXQvb3V0cHV0X2FzdCc7XG5pbXBvcnQge1BhcnNlTG9jYXRpb24sIFBhcnNlU291cmNlRmlsZSwgUGFyc2VTb3VyY2VTcGFufSBmcm9tICcuLi8uLi9wYXJzZV91dGlsJztcbmltcG9ydCB7SWRlbnRpZmllcnMgYXMgUjN9IGZyb20gJy4uL3IzX2lkZW50aWZpZXJzJztcbmltcG9ydCB7RGVjbGFyYXRpb25MaXN0RW1pdE1vZGUsIFIzQ29tcG9uZW50RGVmLCBSM0NvbXBvbmVudE1ldGFkYXRhLCBSM1VzZWREaXJlY3RpdmVNZXRhZGF0YX0gZnJvbSAnLi4vdmlldy9hcGknO1xuaW1wb3J0IHtjcmVhdGVDb21wb25lbnRUeXBlfSBmcm9tICcuLi92aWV3L2NvbXBpbGVyJztcbmltcG9ydCB7UGFyc2VkVGVtcGxhdGV9IGZyb20gJy4uL3ZpZXcvdGVtcGxhdGUnO1xuaW1wb3J0IHtEZWZpbml0aW9uTWFwfSBmcm9tICcuLi92aWV3L3V0aWwnO1xuXG5pbXBvcnQge1IzRGVjbGFyZUNvbXBvbmVudE1ldGFkYXRhfSBmcm9tICcuL2FwaSc7XG5pbXBvcnQge2NyZWF0ZURpcmVjdGl2ZURlZmluaXRpb25NYXB9IGZyb20gJy4vZGlyZWN0aXZlJztcbmltcG9ydCB7dG9PcHRpb25hbExpdGVyYWxBcnJheX0gZnJvbSAnLi91dGlsJztcblxuXG4vKipcbiAqIENvbXBpbGUgYSBjb21wb25lbnQgZGVjbGFyYXRpb24gZGVmaW5lZCBieSB0aGUgYFIzQ29tcG9uZW50TWV0YWRhdGFgLlxuICovXG5leHBvcnQgZnVuY3Rpb24gY29tcGlsZURlY2xhcmVDb21wb25lbnRGcm9tTWV0YWRhdGEoXG4gICAgbWV0YTogUjNDb21wb25lbnRNZXRhZGF0YSwgdGVtcGxhdGU6IFBhcnNlZFRlbXBsYXRlKTogUjNDb21wb25lbnREZWYge1xuICBjb25zdCBkZWZpbml0aW9uTWFwID0gY3JlYXRlQ29tcG9uZW50RGVmaW5pdGlvbk1hcChtZXRhLCB0ZW1wbGF0ZSk7XG5cbiAgY29uc3QgZXhwcmVzc2lvbiA9IG8uaW1wb3J0RXhwcihSMy5kZWNsYXJlQ29tcG9uZW50KS5jYWxsRm4oW2RlZmluaXRpb25NYXAudG9MaXRlcmFsTWFwKCldKTtcbiAgY29uc3QgdHlwZSA9IGNyZWF0ZUNvbXBvbmVudFR5cGUobWV0YSk7XG5cbiAgcmV0dXJuIHtleHByZXNzaW9uLCB0eXBlfTtcbn1cblxuLyoqXG4gKiBHYXRoZXJzIHRoZSBkZWNsYXJhdGlvbiBmaWVsZHMgZm9yIGEgY29tcG9uZW50IGludG8gYSBgRGVmaW5pdGlvbk1hcGAuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVDb21wb25lbnREZWZpbml0aW9uTWFwKG1ldGE6IFIzQ29tcG9uZW50TWV0YWRhdGEsIHRlbXBsYXRlOiBQYXJzZWRUZW1wbGF0ZSk6XG4gICAgRGVmaW5pdGlvbk1hcDxSM0RlY2xhcmVDb21wb25lbnRNZXRhZGF0YT4ge1xuICBjb25zdCBkZWZpbml0aW9uTWFwOiBEZWZpbml0aW9uTWFwPFIzRGVjbGFyZUNvbXBvbmVudE1ldGFkYXRhPiA9XG4gICAgICBjcmVhdGVEaXJlY3RpdmVEZWZpbml0aW9uTWFwKG1ldGEpO1xuXG4gIGRlZmluaXRpb25NYXAuc2V0KCd0ZW1wbGF0ZScsIGdldFRlbXBsYXRlRXhwcmVzc2lvbih0ZW1wbGF0ZSkpO1xuICBpZiAodGVtcGxhdGUuaXNJbmxpbmUpIHtcbiAgICBkZWZpbml0aW9uTWFwLnNldCgnaXNJbmxpbmUnLCBvLmxpdGVyYWwodHJ1ZSkpO1xuICB9XG5cbiAgZGVmaW5pdGlvbk1hcC5zZXQoJ3N0eWxlcycsIHRvT3B0aW9uYWxMaXRlcmFsQXJyYXkobWV0YS5zdHlsZXMsIG8ubGl0ZXJhbCkpO1xuICBkZWZpbml0aW9uTWFwLnNldCgnZGlyZWN0aXZlcycsIGNvbXBpbGVVc2VkRGlyZWN0aXZlTWV0YWRhdGEobWV0YSkpO1xuICBkZWZpbml0aW9uTWFwLnNldCgncGlwZXMnLCBjb21waWxlVXNlZFBpcGVNZXRhZGF0YShtZXRhKSk7XG4gIGRlZmluaXRpb25NYXAuc2V0KCd2aWV3UHJvdmlkZXJzJywgbWV0YS52aWV3UHJvdmlkZXJzKTtcbiAgZGVmaW5pdGlvbk1hcC5zZXQoJ2FuaW1hdGlvbnMnLCBtZXRhLmFuaW1hdGlvbnMpO1xuXG4gIGlmIChtZXRhLmNoYW5nZURldGVjdGlvbiAhPT0gdW5kZWZpbmVkKSB7XG4gICAgZGVmaW5pdGlvbk1hcC5zZXQoXG4gICAgICAgICdjaGFuZ2VEZXRlY3Rpb24nLFxuICAgICAgICBvLmltcG9ydEV4cHIoUjMuQ2hhbmdlRGV0ZWN0aW9uU3RyYXRlZ3kpXG4gICAgICAgICAgICAucHJvcChjb3JlLkNoYW5nZURldGVjdGlvblN0cmF0ZWd5W21ldGEuY2hhbmdlRGV0ZWN0aW9uXSkpO1xuICB9XG4gIGlmIChtZXRhLmVuY2Fwc3VsYXRpb24gIT09IGNvcmUuVmlld0VuY2Fwc3VsYXRpb24uRW11bGF0ZWQpIHtcbiAgICBkZWZpbml0aW9uTWFwLnNldChcbiAgICAgICAgJ2VuY2Fwc3VsYXRpb24nLFxuICAgICAgICBvLmltcG9ydEV4cHIoUjMuVmlld0VuY2Fwc3VsYXRpb24pLnByb3AoY29yZS5WaWV3RW5jYXBzdWxhdGlvblttZXRhLmVuY2Fwc3VsYXRpb25dKSk7XG4gIH1cbiAgaWYgKG1ldGEuaW50ZXJwb2xhdGlvbiAhPT0gREVGQVVMVF9JTlRFUlBPTEFUSU9OX0NPTkZJRykge1xuICAgIGRlZmluaXRpb25NYXAuc2V0KFxuICAgICAgICAnaW50ZXJwb2xhdGlvbicsXG4gICAgICAgIG8ubGl0ZXJhbEFycihbby5saXRlcmFsKG1ldGEuaW50ZXJwb2xhdGlvbi5zdGFydCksIG8ubGl0ZXJhbChtZXRhLmludGVycG9sYXRpb24uZW5kKV0pKTtcbiAgfVxuXG4gIGlmICh0ZW1wbGF0ZS5wcmVzZXJ2ZVdoaXRlc3BhY2VzID09PSB0cnVlKSB7XG4gICAgZGVmaW5pdGlvbk1hcC5zZXQoJ3ByZXNlcnZlV2hpdGVzcGFjZXMnLCBvLmxpdGVyYWwodHJ1ZSkpO1xuICB9XG5cbiAgcmV0dXJuIGRlZmluaXRpb25NYXA7XG59XG5cbmZ1bmN0aW9uIGdldFRlbXBsYXRlRXhwcmVzc2lvbih0ZW1wbGF0ZTogUGFyc2VkVGVtcGxhdGUpOiBvLkV4cHJlc3Npb24ge1xuICBpZiAodHlwZW9mIHRlbXBsYXRlLnRlbXBsYXRlID09PSAnc3RyaW5nJykge1xuICAgIGlmICh0ZW1wbGF0ZS5pc0lubGluZSkge1xuICAgICAgLy8gVGhlIHRlbXBsYXRlIGlzIGlubGluZSBidXQgbm90IGEgc2ltcGxlIGxpdGVyYWwgc3RyaW5nLCBzbyBnaXZlIHVwIHdpdGggdHJ5aW5nIHRvXG4gICAgICAvLyBzb3VyY2UtbWFwIGl0IGFuZCBqdXN0IHJldHVybiBhIHNpbXBsZSBsaXRlcmFsIGhlcmUuXG4gICAgICByZXR1cm4gby5saXRlcmFsKHRlbXBsYXRlLnRlbXBsYXRlKTtcbiAgICB9IGVsc2Uge1xuICAgICAgLy8gVGhlIHRlbXBsYXRlIGlzIGV4dGVybmFsIHNvIHdlIG11c3Qgc3ludGhlc2l6ZSBhbiBleHByZXNzaW9uIG5vZGUgd2l0aCB0aGUgYXBwcm9wcmlhdGVcbiAgICAgIC8vIHNvdXJjZS1zcGFuLlxuICAgICAgY29uc3QgY29udGVudHMgPSB0ZW1wbGF0ZS50ZW1wbGF0ZTtcbiAgICAgIGNvbnN0IGZpbGUgPSBuZXcgUGFyc2VTb3VyY2VGaWxlKGNvbnRlbnRzLCB0ZW1wbGF0ZS50ZW1wbGF0ZVVybCk7XG4gICAgICBjb25zdCBzdGFydCA9IG5ldyBQYXJzZUxvY2F0aW9uKGZpbGUsIDAsIDAsIDApO1xuICAgICAgY29uc3QgZW5kID0gY29tcHV0ZUVuZExvY2F0aW9uKGZpbGUsIGNvbnRlbnRzKTtcbiAgICAgIGNvbnN0IHNwYW4gPSBuZXcgUGFyc2VTb3VyY2VTcGFuKHN0YXJ0LCBlbmQpO1xuICAgICAgcmV0dXJuIG8ubGl0ZXJhbChjb250ZW50cywgbnVsbCwgc3Bhbik7XG4gICAgfVxuICB9IGVsc2Uge1xuICAgIC8vIFRoZSB0ZW1wbGF0ZSBpcyBpbmxpbmUgc28gd2UgY2FuIGp1c3QgcmV1c2UgdGhlIGN1cnJlbnQgZXhwcmVzc2lvbiBub2RlLlxuICAgIHJldHVybiB0ZW1wbGF0ZS50ZW1wbGF0ZTtcbiAgfVxufVxuXG5mdW5jdGlvbiBjb21wdXRlRW5kTG9jYXRpb24oZmlsZTogUGFyc2VTb3VyY2VGaWxlLCBjb250ZW50czogc3RyaW5nKTogUGFyc2VMb2NhdGlvbiB7XG4gIGNvbnN0IGxlbmd0aCA9IGNvbnRlbnRzLmxlbmd0aDtcbiAgbGV0IGxpbmVTdGFydCA9IDA7XG4gIGxldCBsYXN0TGluZVN0YXJ0ID0gMDtcbiAgbGV0IGxpbmUgPSAwO1xuICBkbyB7XG4gICAgbGluZVN0YXJ0ID0gY29udGVudHMuaW5kZXhPZignXFxuJywgbGFzdExpbmVTdGFydCk7XG4gICAgaWYgKGxpbmVTdGFydCAhPT0gLTEpIHtcbiAgICAgIGxhc3RMaW5lU3RhcnQgPSBsaW5lU3RhcnQgKyAxO1xuICAgICAgbGluZSsrO1xuICAgIH1cbiAgfSB3aGlsZSAobGluZVN0YXJ0ICE9PSAtMSk7XG5cbiAgcmV0dXJuIG5ldyBQYXJzZUxvY2F0aW9uKGZpbGUsIGxlbmd0aCwgbGluZSwgbGVuZ3RoIC0gbGFzdExpbmVTdGFydCk7XG59XG5cbi8qKlxuICogQ29tcGlsZXMgdGhlIGRpcmVjdGl2ZXMgYXMgcmVnaXN0ZXJlZCBpbiB0aGUgY29tcG9uZW50IG1ldGFkYXRhIGludG8gYW4gYXJyYXkgbGl0ZXJhbCBvZiB0aGVcbiAqIGluZGl2aWR1YWwgZGlyZWN0aXZlcy4gSWYgdGhlIGNvbXBvbmVudCBkb2VzIG5vdCB1c2UgYW55IGRpcmVjdGl2ZXMsIHRoZW4gbnVsbCBpcyByZXR1cm5lZC5cbiAqL1xuZnVuY3Rpb24gY29tcGlsZVVzZWREaXJlY3RpdmVNZXRhZGF0YShtZXRhOiBSM0NvbXBvbmVudE1ldGFkYXRhKTogby5MaXRlcmFsQXJyYXlFeHByfG51bGwge1xuICBjb25zdCB3cmFwVHlwZSA9IG1ldGEuZGVjbGFyYXRpb25MaXN0RW1pdE1vZGUgIT09IERlY2xhcmF0aW9uTGlzdEVtaXRNb2RlLkRpcmVjdCA/XG4gICAgICBnZW5lcmF0ZUZvcndhcmRSZWYgOlxuICAgICAgKGV4cHI6IG8uRXhwcmVzc2lvbikgPT4gZXhwcjtcblxuICByZXR1cm4gdG9PcHRpb25hbExpdGVyYWxBcnJheShtZXRhLmRpcmVjdGl2ZXMsIGRpcmVjdGl2ZSA9PiB7XG4gICAgY29uc3QgZGlyTWV0YSA9IG5ldyBEZWZpbml0aW9uTWFwPFIzVXNlZERpcmVjdGl2ZU1ldGFkYXRhPigpO1xuICAgIGRpck1ldGEuc2V0KCd0eXBlJywgd3JhcFR5cGUoZGlyZWN0aXZlLnR5cGUpKTtcbiAgICBkaXJNZXRhLnNldCgnc2VsZWN0b3InLCBvLmxpdGVyYWwoZGlyZWN0aXZlLnNlbGVjdG9yKSk7XG4gICAgZGlyTWV0YS5zZXQoJ2lucHV0cycsIHRvT3B0aW9uYWxMaXRlcmFsQXJyYXkoZGlyZWN0aXZlLmlucHV0cywgby5saXRlcmFsKSk7XG4gICAgZGlyTWV0YS5zZXQoJ291dHB1dHMnLCB0b09wdGlvbmFsTGl0ZXJhbEFycmF5KGRpcmVjdGl2ZS5vdXRwdXRzLCBvLmxpdGVyYWwpKTtcbiAgICBkaXJNZXRhLnNldCgnZXhwb3J0QXMnLCB0b09wdGlvbmFsTGl0ZXJhbEFycmF5KGRpcmVjdGl2ZS5leHBvcnRBcywgby5saXRlcmFsKSk7XG4gICAgcmV0dXJuIGRpck1ldGEudG9MaXRlcmFsTWFwKCk7XG4gIH0pO1xufVxuXG4vKipcbiAqIENvbXBpbGVzIHRoZSBwaXBlcyBhcyByZWdpc3RlcmVkIGluIHRoZSBjb21wb25lbnQgbWV0YWRhdGEgaW50byBhbiBvYmplY3QgbGl0ZXJhbCwgd2hlcmUgdGhlXG4gKiBwaXBlJ3MgbmFtZSBpcyB1c2VkIGFzIGtleSBhbmQgYSByZWZlcmVuY2UgdG8gaXRzIHR5cGUgYXMgdmFsdWUuIElmIHRoZSBjb21wb25lbnQgZG9lcyBub3QgdXNlXG4gKiBhbnkgcGlwZXMsIHRoZW4gbnVsbCBpcyByZXR1cm5lZC5cbiAqL1xuZnVuY3Rpb24gY29tcGlsZVVzZWRQaXBlTWV0YWRhdGEobWV0YTogUjNDb21wb25lbnRNZXRhZGF0YSk6IG8uTGl0ZXJhbE1hcEV4cHJ8bnVsbCB7XG4gIGlmIChtZXRhLnBpcGVzLnNpemUgPT09IDApIHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIGNvbnN0IHdyYXBUeXBlID0gbWV0YS5kZWNsYXJhdGlvbkxpc3RFbWl0TW9kZSAhPT0gRGVjbGFyYXRpb25MaXN0RW1pdE1vZGUuRGlyZWN0ID9cbiAgICAgIGdlbmVyYXRlRm9yd2FyZFJlZiA6XG4gICAgICAoZXhwcjogby5FeHByZXNzaW9uKSA9PiBleHByO1xuXG4gIGNvbnN0IGVudHJpZXMgPSBbXTtcbiAgZm9yIChjb25zdCBbbmFtZSwgcGlwZV0gb2YgbWV0YS5waXBlcykge1xuICAgIGVudHJpZXMucHVzaCh7a2V5OiBuYW1lLCB2YWx1ZTogd3JhcFR5cGUocGlwZSksIHF1b3RlZDogdHJ1ZX0pO1xuICB9XG4gIHJldHVybiBvLmxpdGVyYWxNYXAoZW50cmllcyk7XG59XG5cbmZ1bmN0aW9uIGdlbmVyYXRlRm9yd2FyZFJlZihleHByOiBvLkV4cHJlc3Npb24pOiBvLkV4cHJlc3Npb24ge1xuICByZXR1cm4gby5pbXBvcnRFeHByKFIzLmZvcndhcmRSZWYpLmNhbGxGbihbby5mbihbXSwgW25ldyBvLlJldHVyblN0YXRlbWVudChleHByKV0pXSk7XG59XG4iXX0=