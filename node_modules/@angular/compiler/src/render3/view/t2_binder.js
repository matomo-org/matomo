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
        define("@angular/compiler/src/render3/view/t2_binder", ["require", "exports", "tslib", "@angular/compiler/src/expression_parser/ast", "@angular/compiler/src/render3/r3_ast", "@angular/compiler/src/render3/view/template", "@angular/compiler/src/render3/view/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.R3BoundTarget = exports.R3TargetBinder = void 0;
    var tslib_1 = require("tslib");
    var ast_1 = require("@angular/compiler/src/expression_parser/ast");
    var r3_ast_1 = require("@angular/compiler/src/render3/r3_ast");
    var template_1 = require("@angular/compiler/src/render3/view/template");
    var util_1 = require("@angular/compiler/src/render3/view/util");
    /**
     * Processes `Target`s with a given set of directives and performs a binding operation, which
     * returns an object similar to TypeScript's `ts.TypeChecker` that contains knowledge about the
     * target.
     */
    var R3TargetBinder = /** @class */ (function () {
        function R3TargetBinder(directiveMatcher) {
            this.directiveMatcher = directiveMatcher;
        }
        /**
         * Perform a binding operation on the given `Target` and return a `BoundTarget` which contains
         * metadata about the types referenced in the template.
         */
        R3TargetBinder.prototype.bind = function (target) {
            if (!target.template) {
                // TODO(alxhub): handle targets which contain things like HostBindings, etc.
                throw new Error('Binding without a template not yet supported');
            }
            // First, parse the template into a `Scope` structure. This operation captures the syntactic
            // scopes in the template and makes them available for later use.
            var scope = Scope.apply(target.template);
            // Use the `Scope` to extract the entities present at every level of the template.
            var templateEntities = extractTemplateEntities(scope);
            // Next, perform directive matching on the template using the `DirectiveBinder`. This returns:
            //   - directives: Map of nodes (elements & ng-templates) to the directives on them.
            //   - bindings: Map of inputs, outputs, and attributes to the directive/element that claims
            //     them. TODO(alxhub): handle multiple directives claiming an input/output/etc.
            //   - references: Map of #references to their targets.
            var _a = DirectiveBinder.apply(target.template, this.directiveMatcher), directives = _a.directives, bindings = _a.bindings, references = _a.references;
            // Finally, run the TemplateBinder to bind references, variables, and other entities within the
            // template. This extracts all the metadata that doesn't depend on directive matching.
            var _b = TemplateBinder.apply(target.template, scope), expressions = _b.expressions, symbols = _b.symbols, nestingLevel = _b.nestingLevel, usedPipes = _b.usedPipes;
            return new R3BoundTarget(target, directives, bindings, references, expressions, symbols, nestingLevel, templateEntities, usedPipes);
        };
        return R3TargetBinder;
    }());
    exports.R3TargetBinder = R3TargetBinder;
    /**
     * Represents a binding scope within a template.
     *
     * Any variables, references, or other named entities declared within the template will
     * be captured and available by name in `namedEntities`. Additionally, child templates will
     * be analyzed and have their child `Scope`s available in `childScopes`.
     */
    var Scope = /** @class */ (function () {
        function Scope(parentScope, template) {
            this.parentScope = parentScope;
            this.template = template;
            /**
             * Named members of the `Scope`, such as `Reference`s or `Variable`s.
             */
            this.namedEntities = new Map();
            /**
             * Child `Scope`s for immediately nested `Template`s.
             */
            this.childScopes = new Map();
        }
        Scope.newRootScope = function () {
            return new Scope(null, null);
        };
        /**
         * Process a template (either as a `Template` sub-template with variables, or a plain array of
         * template `Node`s) and construct its `Scope`.
         */
        Scope.apply = function (template) {
            var scope = Scope.newRootScope();
            scope.ingest(template);
            return scope;
        };
        /**
         * Internal method to process the template and populate the `Scope`.
         */
        Scope.prototype.ingest = function (template) {
            var _this = this;
            if (template instanceof r3_ast_1.Template) {
                // Variables on an <ng-template> are defined in the inner scope.
                template.variables.forEach(function (node) { return _this.visitVariable(node); });
                // Process the nodes of the template.
                template.children.forEach(function (node) { return node.visit(_this); });
            }
            else {
                // No overarching `Template` instance, so process the nodes directly.
                template.forEach(function (node) { return node.visit(_this); });
            }
        };
        Scope.prototype.visitElement = function (element) {
            var _this = this;
            // `Element`s in the template may have `Reference`s which are captured in the scope.
            element.references.forEach(function (node) { return _this.visitReference(node); });
            // Recurse into the `Element`'s children.
            element.children.forEach(function (node) { return node.visit(_this); });
        };
        Scope.prototype.visitTemplate = function (template) {
            var _this = this;
            // References on a <ng-template> are defined in the outer scope, so capture them before
            // processing the template's child scope.
            template.references.forEach(function (node) { return _this.visitReference(node); });
            // Next, create an inner scope and process the template within it.
            var scope = new Scope(this, template);
            scope.ingest(template);
            this.childScopes.set(template, scope);
        };
        Scope.prototype.visitVariable = function (variable) {
            // Declare the variable if it's not already.
            this.maybeDeclare(variable);
        };
        Scope.prototype.visitReference = function (reference) {
            // Declare the variable if it's not already.
            this.maybeDeclare(reference);
        };
        // Unused visitors.
        Scope.prototype.visitContent = function (content) { };
        Scope.prototype.visitBoundAttribute = function (attr) { };
        Scope.prototype.visitBoundEvent = function (event) { };
        Scope.prototype.visitBoundText = function (text) { };
        Scope.prototype.visitText = function (text) { };
        Scope.prototype.visitTextAttribute = function (attr) { };
        Scope.prototype.visitIcu = function (icu) { };
        Scope.prototype.maybeDeclare = function (thing) {
            // Declare something with a name, as long as that name isn't taken.
            if (!this.namedEntities.has(thing.name)) {
                this.namedEntities.set(thing.name, thing);
            }
        };
        /**
         * Look up a variable within this `Scope`.
         *
         * This can recurse into a parent `Scope` if it's available.
         */
        Scope.prototype.lookup = function (name) {
            if (this.namedEntities.has(name)) {
                // Found in the local scope.
                return this.namedEntities.get(name);
            }
            else if (this.parentScope !== null) {
                // Not in the local scope, but there's a parent scope so check there.
                return this.parentScope.lookup(name);
            }
            else {
                // At the top level and it wasn't found.
                return null;
            }
        };
        /**
         * Get the child scope for a `Template`.
         *
         * This should always be defined.
         */
        Scope.prototype.getChildScope = function (template) {
            var res = this.childScopes.get(template);
            if (res === undefined) {
                throw new Error("Assertion error: child scope for " + template + " not found");
            }
            return res;
        };
        return Scope;
    }());
    /**
     * Processes a template and matches directives on nodes (elements and templates).
     *
     * Usually used via the static `apply()` method.
     */
    var DirectiveBinder = /** @class */ (function () {
        function DirectiveBinder(matcher, directives, bindings, references) {
            this.matcher = matcher;
            this.directives = directives;
            this.bindings = bindings;
            this.references = references;
        }
        /**
         * Process a template (list of `Node`s) and perform directive matching against each node.
         *
         * @param template the list of template `Node`s to match (recursively).
         * @param selectorMatcher a `SelectorMatcher` containing the directives that are in scope for
         * this template.
         * @returns three maps which contain information about directives in the template: the
         * `directives` map which lists directives matched on each node, the `bindings` map which
         * indicates which directives claimed which bindings (inputs, outputs, etc), and the `references`
         * map which resolves #references (`Reference`s) within the template to the named directive or
         * template node.
         */
        DirectiveBinder.apply = function (template, selectorMatcher) {
            var directives = new Map();
            var bindings = new Map();
            var references = new Map();
            var matcher = new DirectiveBinder(selectorMatcher, directives, bindings, references);
            matcher.ingest(template);
            return { directives: directives, bindings: bindings, references: references };
        };
        DirectiveBinder.prototype.ingest = function (template) {
            var _this = this;
            template.forEach(function (node) { return node.visit(_this); });
        };
        DirectiveBinder.prototype.visitElement = function (element) {
            this.visitElementOrTemplate(element.name, element);
        };
        DirectiveBinder.prototype.visitTemplate = function (template) {
            this.visitElementOrTemplate('ng-template', template);
        };
        DirectiveBinder.prototype.visitElementOrTemplate = function (elementName, node) {
            var _this = this;
            // First, determine the HTML shape of the node for the purpose of directive matching.
            // Do this by building up a `CssSelector` for the node.
            var cssSelector = template_1.createCssSelector(elementName, util_1.getAttrsForDirectiveMatching(node));
            // Next, use the `SelectorMatcher` to get the list of directives on the node.
            var directives = [];
            this.matcher.match(cssSelector, function (_, directive) { return directives.push(directive); });
            if (directives.length > 0) {
                this.directives.set(node, directives);
            }
            // Resolve any references that are created on this node.
            node.references.forEach(function (ref) {
                var dirTarget = null;
                // If the reference expression is empty, then it matches the "primary" directive on the node
                // (if there is one). Otherwise it matches the host node itself (either an element or
                // <ng-template> node).
                if (ref.value.trim() === '') {
                    // This could be a reference to a component if there is one.
                    dirTarget = directives.find(function (dir) { return dir.isComponent; }) || null;
                }
                else {
                    // This should be a reference to a directive exported via exportAs.
                    dirTarget =
                        directives.find(function (dir) { return dir.exportAs !== null && dir.exportAs.some(function (value) { return value === ref.value; }); }) ||
                            null;
                    // Check if a matching directive was found.
                    if (dirTarget === null) {
                        // No matching directive was found - this reference points to an unknown target. Leave it
                        // unmapped.
                        return;
                    }
                }
                if (dirTarget !== null) {
                    // This reference points to a directive.
                    _this.references.set(ref, { directive: dirTarget, node: node });
                }
                else {
                    // This reference points to the node itself.
                    _this.references.set(ref, node);
                }
            });
            var setAttributeBinding = function (attribute, ioType) {
                var dir = directives.find(function (dir) { return dir[ioType].hasBindingPropertyName(attribute.name); });
                var binding = dir !== undefined ? dir : node;
                _this.bindings.set(attribute, binding);
            };
            // Node inputs (bound attributes) and text attributes can be bound to an
            // input on a directive.
            node.inputs.forEach(function (input) { return setAttributeBinding(input, 'inputs'); });
            node.attributes.forEach(function (attr) { return setAttributeBinding(attr, 'inputs'); });
            if (node instanceof r3_ast_1.Template) {
                node.templateAttrs.forEach(function (attr) { return setAttributeBinding(attr, 'inputs'); });
            }
            // Node outputs (bound events) can be bound to an output on a directive.
            node.outputs.forEach(function (output) { return setAttributeBinding(output, 'outputs'); });
            // Recurse into the node's children.
            node.children.forEach(function (child) { return child.visit(_this); });
        };
        // Unused visitors.
        DirectiveBinder.prototype.visitContent = function (content) { };
        DirectiveBinder.prototype.visitVariable = function (variable) { };
        DirectiveBinder.prototype.visitReference = function (reference) { };
        DirectiveBinder.prototype.visitTextAttribute = function (attribute) { };
        DirectiveBinder.prototype.visitBoundAttribute = function (attribute) { };
        DirectiveBinder.prototype.visitBoundEvent = function (attribute) { };
        DirectiveBinder.prototype.visitBoundAttributeOrEvent = function (node) { };
        DirectiveBinder.prototype.visitText = function (text) { };
        DirectiveBinder.prototype.visitBoundText = function (text) { };
        DirectiveBinder.prototype.visitIcu = function (icu) { };
        return DirectiveBinder;
    }());
    /**
     * Processes a template and extract metadata about expressions and symbols within.
     *
     * This is a companion to the `DirectiveBinder` that doesn't require knowledge of directives matched
     * within the template in order to operate.
     *
     * Expressions are visited by the superclass `RecursiveAstVisitor`, with custom logic provided
     * by overridden methods from that visitor.
     */
    var TemplateBinder = /** @class */ (function (_super) {
        tslib_1.__extends(TemplateBinder, _super);
        function TemplateBinder(bindings, symbols, usedPipes, nestingLevel, scope, template, level) {
            var _this = _super.call(this) || this;
            _this.bindings = bindings;
            _this.symbols = symbols;
            _this.usedPipes = usedPipes;
            _this.nestingLevel = nestingLevel;
            _this.scope = scope;
            _this.template = template;
            _this.level = level;
            _this.pipesUsed = [];
            // Save a bit of processing time by constructing this closure in advance.
            _this.visitNode = function (node) { return node.visit(_this); };
            return _this;
        }
        // This method is defined to reconcile the type of TemplateBinder since both
        // RecursiveAstVisitor and Visitor define the visit() method in their
        // interfaces.
        TemplateBinder.prototype.visit = function (node, context) {
            if (node instanceof ast_1.AST) {
                node.visit(this, context);
            }
            else {
                node.visit(this);
            }
        };
        /**
         * Process a template and extract metadata about expressions and symbols within.
         *
         * @param template the nodes of the template to process
         * @param scope the `Scope` of the template being processed.
         * @returns three maps which contain metadata about the template: `expressions` which interprets
         * special `AST` nodes in expressions as pointing to references or variables declared within the
         * template, `symbols` which maps those variables and references to the nested `Template` which
         * declares them, if any, and `nestingLevel` which associates each `Template` with a integer
         * nesting level (how many levels deep within the template structure the `Template` is), starting
         * at 1.
         */
        TemplateBinder.apply = function (template, scope) {
            var expressions = new Map();
            var symbols = new Map();
            var nestingLevel = new Map();
            var usedPipes = new Set();
            // The top-level template has nesting level 0.
            var binder = new TemplateBinder(expressions, symbols, usedPipes, nestingLevel, scope, template instanceof r3_ast_1.Template ? template : null, 0);
            binder.ingest(template);
            return { expressions: expressions, symbols: symbols, nestingLevel: nestingLevel, usedPipes: usedPipes };
        };
        TemplateBinder.prototype.ingest = function (template) {
            if (template instanceof r3_ast_1.Template) {
                // For <ng-template>s, process only variables and child nodes. Inputs, outputs, templateAttrs,
                // and references were all processed in the scope of the containing template.
                template.variables.forEach(this.visitNode);
                template.children.forEach(this.visitNode);
                // Set the nesting level.
                this.nestingLevel.set(template, this.level);
            }
            else {
                // Visit each node from the top-level template.
                template.forEach(this.visitNode);
            }
        };
        TemplateBinder.prototype.visitElement = function (element) {
            // Visit the inputs, outputs, and children of the element.
            element.inputs.forEach(this.visitNode);
            element.outputs.forEach(this.visitNode);
            element.children.forEach(this.visitNode);
        };
        TemplateBinder.prototype.visitTemplate = function (template) {
            // First, visit inputs, outputs and template attributes of the template node.
            template.inputs.forEach(this.visitNode);
            template.outputs.forEach(this.visitNode);
            template.templateAttrs.forEach(this.visitNode);
            // References are also evaluated in the outer context.
            template.references.forEach(this.visitNode);
            // Next, recurse into the template using its scope, and bumping the nesting level up by one.
            var childScope = this.scope.getChildScope(template);
            var binder = new TemplateBinder(this.bindings, this.symbols, this.usedPipes, this.nestingLevel, childScope, template, this.level + 1);
            binder.ingest(template);
        };
        TemplateBinder.prototype.visitVariable = function (variable) {
            // Register the `Variable` as a symbol in the current `Template`.
            if (this.template !== null) {
                this.symbols.set(variable, this.template);
            }
        };
        TemplateBinder.prototype.visitReference = function (reference) {
            // Register the `Reference` as a symbol in the current `Template`.
            if (this.template !== null) {
                this.symbols.set(reference, this.template);
            }
        };
        // Unused template visitors
        TemplateBinder.prototype.visitText = function (text) { };
        TemplateBinder.prototype.visitContent = function (content) { };
        TemplateBinder.prototype.visitTextAttribute = function (attribute) { };
        TemplateBinder.prototype.visitIcu = function (icu) {
            var _this = this;
            Object.keys(icu.vars).forEach(function (key) { return icu.vars[key].visit(_this); });
            Object.keys(icu.placeholders).forEach(function (key) { return icu.placeholders[key].visit(_this); });
        };
        // The remaining visitors are concerned with processing AST expressions within template bindings
        TemplateBinder.prototype.visitBoundAttribute = function (attribute) {
            attribute.value.visit(this);
        };
        TemplateBinder.prototype.visitBoundEvent = function (event) {
            event.handler.visit(this);
        };
        TemplateBinder.prototype.visitBoundText = function (text) {
            text.value.visit(this);
        };
        TemplateBinder.prototype.visitPipe = function (ast, context) {
            this.usedPipes.add(ast.name);
            return _super.prototype.visitPipe.call(this, ast, context);
        };
        // These five types of AST expressions can refer to expression roots, which could be variables
        // or references in the current scope.
        TemplateBinder.prototype.visitPropertyRead = function (ast, context) {
            this.maybeMap(context, ast, ast.name);
            return _super.prototype.visitPropertyRead.call(this, ast, context);
        };
        TemplateBinder.prototype.visitSafePropertyRead = function (ast, context) {
            this.maybeMap(context, ast, ast.name);
            return _super.prototype.visitSafePropertyRead.call(this, ast, context);
        };
        TemplateBinder.prototype.visitPropertyWrite = function (ast, context) {
            this.maybeMap(context, ast, ast.name);
            return _super.prototype.visitPropertyWrite.call(this, ast, context);
        };
        TemplateBinder.prototype.visitMethodCall = function (ast, context) {
            this.maybeMap(context, ast, ast.name);
            return _super.prototype.visitMethodCall.call(this, ast, context);
        };
        TemplateBinder.prototype.visitSafeMethodCall = function (ast, context) {
            this.maybeMap(context, ast, ast.name);
            return _super.prototype.visitSafeMethodCall.call(this, ast, context);
        };
        TemplateBinder.prototype.maybeMap = function (scope, ast, name) {
            // If the receiver of the expression isn't the `ImplicitReceiver`, this isn't the root of an
            // `AST` expression that maps to a `Variable` or `Reference`.
            if (!(ast.receiver instanceof ast_1.ImplicitReceiver)) {
                return;
            }
            // Check whether the name exists in the current scope. If so, map it. Otherwise, the name is
            // probably a property on the top-level component context.
            var target = this.scope.lookup(name);
            if (target !== null) {
                this.bindings.set(ast, target);
            }
        };
        return TemplateBinder;
    }(ast_1.RecursiveAstVisitor));
    /**
     * Metadata container for a `Target` that allows queries for specific bits of metadata.
     *
     * See `BoundTarget` for documentation on the individual methods.
     */
    var R3BoundTarget = /** @class */ (function () {
        function R3BoundTarget(target, directives, bindings, references, exprTargets, symbols, nestingLevel, templateEntities, usedPipes) {
            this.target = target;
            this.directives = directives;
            this.bindings = bindings;
            this.references = references;
            this.exprTargets = exprTargets;
            this.symbols = symbols;
            this.nestingLevel = nestingLevel;
            this.templateEntities = templateEntities;
            this.usedPipes = usedPipes;
        }
        R3BoundTarget.prototype.getEntitiesInTemplateScope = function (template) {
            var _a;
            return (_a = this.templateEntities.get(template)) !== null && _a !== void 0 ? _a : new Set();
        };
        R3BoundTarget.prototype.getDirectivesOfNode = function (node) {
            return this.directives.get(node) || null;
        };
        R3BoundTarget.prototype.getReferenceTarget = function (ref) {
            return this.references.get(ref) || null;
        };
        R3BoundTarget.prototype.getConsumerOfBinding = function (binding) {
            return this.bindings.get(binding) || null;
        };
        R3BoundTarget.prototype.getExpressionTarget = function (expr) {
            return this.exprTargets.get(expr) || null;
        };
        R3BoundTarget.prototype.getTemplateOfSymbol = function (symbol) {
            return this.symbols.get(symbol) || null;
        };
        R3BoundTarget.prototype.getNestingLevel = function (template) {
            return this.nestingLevel.get(template) || 0;
        };
        R3BoundTarget.prototype.getUsedDirectives = function () {
            var set = new Set();
            this.directives.forEach(function (dirs) { return dirs.forEach(function (dir) { return set.add(dir); }); });
            return Array.from(set.values());
        };
        R3BoundTarget.prototype.getUsedPipes = function () {
            return Array.from(this.usedPipes);
        };
        return R3BoundTarget;
    }());
    exports.R3BoundTarget = R3BoundTarget;
    function extractTemplateEntities(rootScope) {
        var e_1, _a, e_2, _b;
        var entityMap = new Map();
        function extractScopeEntities(scope) {
            if (entityMap.has(scope.template)) {
                return entityMap.get(scope.template);
            }
            var currentEntities = scope.namedEntities;
            var templateEntities;
            if (scope.parentScope !== null) {
                templateEntities = new Map(tslib_1.__spread(extractScopeEntities(scope.parentScope), currentEntities));
            }
            else {
                templateEntities = new Map(currentEntities);
            }
            entityMap.set(scope.template, templateEntities);
            return templateEntities;
        }
        var scopesToProcess = [rootScope];
        while (scopesToProcess.length > 0) {
            var scope = scopesToProcess.pop();
            try {
                for (var _c = (e_1 = void 0, tslib_1.__values(scope.childScopes.values())), _d = _c.next(); !_d.done; _d = _c.next()) {
                    var childScope = _d.value;
                    scopesToProcess.push(childScope);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_d && !_d.done && (_a = _c.return)) _a.call(_c);
                }
                finally { if (e_1) throw e_1.error; }
            }
            extractScopeEntities(scope);
        }
        var templateEntities = new Map();
        try {
            for (var entityMap_1 = tslib_1.__values(entityMap), entityMap_1_1 = entityMap_1.next(); !entityMap_1_1.done; entityMap_1_1 = entityMap_1.next()) {
                var _e = tslib_1.__read(entityMap_1_1.value, 2), template = _e[0], entities = _e[1];
                templateEntities.set(template, new Set(entities.values()));
            }
        }
        catch (e_2_1) { e_2 = { error: e_2_1 }; }
        finally {
            try {
                if (entityMap_1_1 && !entityMap_1_1.done && (_b = entityMap_1.return)) _b.call(entityMap_1);
            }
            finally { if (e_2) throw e_2.error; }
        }
        return templateEntities;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidDJfYmluZGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3JlbmRlcjMvdmlldy90Ml9iaW5kZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVILG1FQUErSztJQUUvSywrREFBMEo7SUFHMUosd0VBQTZDO0lBQzdDLGdFQUFvRDtJQUdwRDs7OztPQUlHO0lBQ0g7UUFDRSx3QkFBb0IsZ0JBQTZDO1lBQTdDLHFCQUFnQixHQUFoQixnQkFBZ0IsQ0FBNkI7UUFBRyxDQUFDO1FBRXJFOzs7V0FHRztRQUNILDZCQUFJLEdBQUosVUFBSyxNQUFjO1lBQ2pCLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxFQUFFO2dCQUNwQiw0RUFBNEU7Z0JBQzVFLE1BQU0sSUFBSSxLQUFLLENBQUMsOENBQThDLENBQUMsQ0FBQzthQUNqRTtZQUVELDRGQUE0RjtZQUM1RixpRUFBaUU7WUFDakUsSUFBTSxLQUFLLEdBQUcsS0FBSyxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUM7WUFHM0Msa0ZBQWtGO1lBQ2xGLElBQU0sZ0JBQWdCLEdBQUcsdUJBQXVCLENBQUMsS0FBSyxDQUFDLENBQUM7WUFFeEQsOEZBQThGO1lBQzlGLG9GQUFvRjtZQUNwRiw0RkFBNEY7WUFDNUYsbUZBQW1GO1lBQ25GLHVEQUF1RDtZQUNqRCxJQUFBLEtBQ0YsZUFBZSxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsUUFBUSxFQUFFLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxFQUQxRCxVQUFVLGdCQUFBLEVBQUUsUUFBUSxjQUFBLEVBQUUsVUFBVSxnQkFDMEIsQ0FBQztZQUNsRSwrRkFBK0Y7WUFDL0Ysc0ZBQXNGO1lBQ2hGLElBQUEsS0FDRixjQUFjLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxRQUFRLEVBQUUsS0FBSyxDQUFD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QzVCLGdCQUFnQixDQUFDLEdBQUcsQ0FBQyxRQUFRLEVBQUUsSUFBSSxHQUFHLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBRSxDQUFDLENBQUMsQ0FBQzthQUM1RDs7Ozs7Ozs7O1FBQ0QsT0FBTyxnQkFBZ0IsQ0FBQztJQUMxQixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7QVNULCBCaW5kaW5nUGlwZSwgSW1wbGljaXRSZWNlaXZlciwgTWV0aG9kQ2FsbCwgUHJvcGVydHlSZWFkLCBQcm9wZXJ0eVdyaXRlLCBSZWN1cnNpdmVBc3RWaXNpdG9yLCBTYWZlTWV0aG9kQ2FsbCwgU2FmZVByb3BlcnR5UmVhZH0gZnJvbSAnLi4vLi4vZXhwcmVzc2lvbl9wYXJzZXIvYXN0JztcbmltcG9ydCB7U2VsZWN0b3JNYXRjaGVyfSBmcm9tICcuLi8uLi9zZWxlY3Rvcic7XG5pbXBvcnQge0JvdW5kQXR0cmlidXRlLCBCb3VuZEV2ZW50LCBCb3VuZFRleHQsIENvbnRlbnQsIEVsZW1lbnQsIEljdSwgTm9kZSwgUmVmZXJlbmNlLCBUZW1wbGF0ZSwgVGV4dCwgVGV4dEF0dHJpYnV0ZSwgVmFyaWFibGUsIFZpc2l0b3J9IGZyb20gJy4uL3IzX2FzdCc7XG5cbmltcG9ydCB7Qm91bmRUYXJnZXQsIERpcmVjdGl2ZU1ldGEsIFRhcmdldCwgVGFyZ2V0QmluZGVyfSBmcm9tICcuL3QyX2FwaSc7XG5pbXBvcnQge2NyZWF0ZUNzc1NlbGVjdG9yfSBmcm9tICcuL3RlbXBsYXRlJztcbmltcG9ydCB7Z2V0QXR0cnNGb3JEaXJlY3RpdmVNYXRjaGluZ30gZnJvbSAnLi91dGlsJztcblxuXG4vKipcbiAqIFByb2Nlc3NlcyBgVGFyZ2V0YHMgd2l0aCBhIGdpdmVuIHNldCBvZiBkaXJlY3RpdmVzIGFuZCBwZXJmb3JtcyBhIGJpbmRpbmcgb3BlcmF0aW9uLCB3aGljaFxuICogcmV0dXJucyBhbiBvYmplY3Qgc2ltaWxhciB0byBUeXBlU2NyaXB0J3MgYHRzLlR5cGVDaGVja2VyYCB0aGF0IGNvbnRhaW5zIGtub3dsZWRnZSBhYm91dCB0aGVcbiAqIHRhcmdldC5cbiAqL1xuZXhwb3J0IGNsYXNzIFIzVGFyZ2V0QmluZGVyPERpcmVjdGl2ZVQgZXh0ZW5kcyBEaXJlY3RpdmVNZXRhPiBpbXBsZW1lbnRzIFRhcmdldEJpbmRlcjxEaXJlY3RpdmVUPiB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgZGlyZWN0aXZlTWF0Y2hlcjogU2VsZWN0b3JNYXRjaGVyPERpcmVjdGl2ZVQ+KSB7fVxuXG4gIC8qKlxuICAgKiBQZXJmb3JtIGEgYmluZGluZyBvcGVyYXRpb24gb24gdGhlIGdpdmVuIGBUYXJnZXRgIGFuZCByZXR1cm4gYSBgQm91bmRUYXJnZXRgIHdoaWNoIGNvbnRhaW5zXG4gICAqIG1ldGFkYXRhIGFib3V0IHRoZSB0eXBlcyByZWZlcmVuY2VkIGluIHRoZSB0ZW1wbGF0ZS5cbiAgICovXG4gIGJpbmQodGFyZ2V0OiBUYXJnZXQpOiBCb3VuZFRhcmdldDxEaXJlY3RpdmVUPiB7XG4gICAgaWYgKCF0YXJnZXQudGVtcGxhdGUpIHtcbiAgICAgIC8vIFRPRE8oYWx4aHViKTogaGFuZGxlIHRhcmdldHMgd2hpY2ggY29udGFpbiB0aGluZ3MgbGlrZSBIb3N0QmluZGluZ3MsIGV0Yy5cbiAgICAgIHRocm93IG5ldyBFcnJvcignQmluZGluZyB3aXRob3V0IGEgdGVtcGxhdGUgbm90IHlldCBzdXBwb3J0ZWQnKTtcbiAgICB9XG5cbiAgICAvLyBGaXJzdCwgcGFyc2UgdGhlIHRlbXBsYXRlIGludG8gYSBgU2NvcGVgIHN0cnVjdHVyZS4gVGhpcyBvcGVyYXRpb24gY2FwdHVyZXMgdGhlIHN5bnRhY3RpY1xuICAgIC8vIHNjb3BlcyBpbiB0aGUgdGVtcGxhdGUgYW5kIG1ha2VzIHRoZW0gYXZhaWxhYmxlIGZvciBsYXRlciB1c2UuXG4gICAgY29uc3Qgc2NvcGUgPSBTY29wZS5hcHBseSh0YXJnZXQudGVtcGxhdGUpO1xuXG5cbiAgICAvLyBVc2UgdGhlIGBTY29wZWAgdG8gZXh0cmFjdCB0aGUgZW50aXRpZXMgcHJlc2VudCBhdCBldmVyeSBsZXZlbCBvZiB0aGUgdGVtcGxhdGUuXG4gICAgY29uc3QgdGVtcGxhdGVFbnRpdGllcyA9IGV4dHJhY3RUZW1wbGF0ZUVudGl0aWVzKHNjb3BlKTtcblxuICAgIC8vIE5leHQsIHBlcmZvcm0gZGlyZWN0aXZlIG1hdGNoaW5nIG9uIHRoZSB0ZW1wbGF0ZSB1c2luZyB0aGUgYERpcmVjdGl2ZUJpbmRlcmAuIFRoaXMgcmV0dXJuczpcbiAgICAvLyAgIC0gZGlyZWN0aXZlczogTWFwIG9mIG5vZGVzIChlbGVtZW50cyAmIG5nLXRlbXBsYXRlcykgdG8gdGhlIGRpcmVjdGl2ZXMgb24gdGhlbS5cbiAgICAvLyAgIC0gYmluZGluZ3M6IE1hcCBvZiBpbnB1dHMsIG91dHB1dHMsIGFuZCBhdHRyaWJ1dGVzIHRvIHRoZSBkaXJlY3RpdmUvZWxlbWVudCB0aGF0IGNsYWltc1xuICAgIC8vICAgICB0aGVtLiBUT0RPKGFseGh1Yik6IGhhbmRsZSBtdWx0aXBsZSBkaXJlY3RpdmVzIGNsYWltaW5nIGFuIGlucHV0L291dHB1dC9ldGMuXG4gICAgLy8gICAtIHJlZmVyZW5jZXM6IE1hcCBvZiAjcmVmZXJlbmNlcyB0byB0aGVpciB0YXJnZXRzLlxuICAgIGNvbnN0IHtkaXJlY3RpdmVzLCBiaW5kaW5ncywgcmVmZXJlbmNlc30gPVxuICAgICAgICBEaXJlY3RpdmVCaW5kZXIuYXBwbHkodGFyZ2V0LnRlbXBsYXRlLCB0aGlzLmRpcmVjdGl2ZU1hdGNoZXIpO1xuICAgIC8vIEZpbmFsbHksIHJ1biB0aGUgVGVtcGxhdGVCaW5kZXIgdG8gYmluZCByZWZlcmVuY2VzLCB2YXJpYWJsZXMsIGFuZCBvdGhlciBlbnRpdGllcyB3aXRoaW4gdGhlXG4gICAgLy8gdGVtcGxhdGUuIFRoaXMgZXh0cmFjdHMgYWxsIHRoZSBtZXRhZGF0YSB0aGF0IGRvZXNuJ3QgZGVwZW5kIG9uIGRpcmVjdGl2ZSBtYXRjaGluZy5cbiAgICBjb25zdCB7ZXhwcmVzc2lvbnMsIHN5bWJvbHMsIG5lc3RpbmdMZXZlbCwgdXNlZFBpcGVzfSA9XG4gICAgICAgIFRlbXBsYXRlQmluZGVyLmFwcGx5KHRhcmdldC50ZW1wbGF0ZSwgc2NvcGUpO1xuICAgIHJldHVybiBuZXcgUjNCb3VuZFRhcmdldChcbiAgICAgICAgdGFyZ2V0LCBkaXJlY3RpdmVzLCBiaW5kaW5ncywgcmVmZXJlbmNlcywgZXhwcmVzc2lvbnMsIHN5bWJvbHMsIG5lc3RpbmdMZXZlbCxcbiAgICAgICAgdGVtcGxhdGVFbnRpdGllcywgdXNlZFBpcGVzKTtcbiAgfVxufVxuXG4vKipcbiAqIFJlcHJlc2VudHMgYSBiaW5kaW5nIHNjb3BlIHdpdGhpbiBhIHRlbXBsYXRlLlxuICpcbiAqIEFueSB2YXJpYWJsZXMsIHJlZmVyZW5jZXMsIG9yIG90aGVyIG5hbWVkIGVudGl0aWVzIGRlY2xhcmVkIHdpdGhpbiB0aGUgdGVtcGxhdGUgd2lsbFxuICogYmUgY2FwdHVyZWQgYW5kIGF2YWlsYWJsZSBieSBuYW1lIGluIGBuYW1lZEVudGl0aWVzYC4gQWRkaXRpb25hbGx5LCBjaGlsZCB0ZW1wbGF0ZXMgd2lsbFxuICogYmUgYW5hbHl6ZWQgYW5kIGhhdmUgdGhlaXIgY2hpbGQgYFNjb3BlYHMgYXZhaWxhYmxlIGluIGBjaGlsZFNjb3Blc2AuXG4gKi9cbmNsYXNzIFNjb3BlIGltcGxlbWVudHMgVmlzaXRvciB7XG4gIC8qKlxuICAgKiBOYW1lZCBtZW1iZXJzIG9mIHRoZSBgU2NvcGVgLCBzdWNoIGFzIGBSZWZlcmVuY2VgcyBvciBgVmFyaWFibGVgcy5cbiAgICovXG4gIHJlYWRvbmx5IG5hbWVkRW50aXRpZXMgPSBuZXcgTWFwPHN0cmluZywgUmVmZXJlbmNlfFZhcmlhYmxlPigpO1xuXG4gIC8qKlxuICAgKiBDaGlsZCBgU2NvcGVgcyBmb3IgaW1tZWRpYXRlbHkgbmVzdGVkIGBUZW1wbGF0ZWBzLlxuICAgKi9cbiAgcmVhZG9ubHkgY2hpbGRTY29wZXMgPSBuZXcgTWFwPFRlbXBsYXRlLCBTY29wZT4oKTtcblxuICBwcml2YXRlIGNvbnN0cnVjdG9yKHJlYWRvbmx5IHBhcmVudFNjb3BlOiBTY29wZXxudWxsLCByZWFkb25seSB0ZW1wbGF0ZTogVGVtcGxhdGV8bnVsbCkge31cblxuICBzdGF0aWMgbmV3Um9vdFNjb3BlKCk6IFNjb3BlIHtcbiAgICByZXR1cm4gbmV3IFNjb3BlKG51bGwsIG51bGwpO1xuICB9XG5cbiAgLyoqXG4gICAqIFByb2Nlc3MgYSB0ZW1wbGF0ZSAoZWl0aGVyIGFzIGEgYFRlbXBsYXRlYCBzdWItdGVtcGxhdGUgd2l0aCB2YXJpYWJsZXMsIG9yIGEgcGxhaW4gYXJyYXkgb2ZcbiAgICogdGVtcGxhdGUgYE5vZGVgcykgYW5kIGNvbnN0cnVjdCBpdHMgYFNjb3BlYC5cbiAgICovXG4gIHN0YXRpYyBhcHBseSh0ZW1wbGF0ZTogTm9kZVtdKTogU2NvcGUge1xuICAgIGNvbnN0IHNjb3BlID0gU2NvcGUubmV3Um9vdFNjb3BlKCk7XG4gICAgc2NvcGUuaW5nZXN0KHRlbXBsYXRlKTtcbiAgICByZXR1cm4gc2NvcGU7XG4gIH1cblxuICAvKipcbiAgICogSW50ZXJuYWwgbWV0aG9kIHRvIHByb2Nlc3MgdGhlIHRlbXBsYXRlIGFuZCBwb3B1bGF0ZSB0aGUgYFNjb3BlYC5cbiAgICovXG4gIHByaXZhdGUgaW5nZXN0KHRlbXBsYXRlOiBUZW1wbGF0ZXxOb2RlW10pOiB2b2lkIHtcbiAgICBpZiAodGVtcGxhdGUgaW5zdGFuY2VvZiBUZW1wbGF0ZSkge1xuICAgICAgLy8gVmFyaWFibGVzIG9uIGFuIDxuZy10ZW1wbGF0ZT4gYXJlIGRlZmluZWQgaW4gdGhlIGlubmVyIHNjb3BlLlxuICAgICAgdGVtcGxhdGUudmFyaWFibGVzLmZvckVhY2gobm9kZSA9PiB0aGlzLnZpc2l0VmFyaWFibGUobm9kZSkpO1xuXG4gICAgICAvLyBQcm9jZXNzIHRoZSBub2RlcyBvZiB0aGUgdGVtcGxhdGUuXG4gICAgICB0ZW1wbGF0ZS5jaGlsZHJlbi5mb3JFYWNoKG5vZGUgPT4gbm9kZS52aXNpdCh0aGlzKSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIC8vIE5vIG92ZXJhcmNoaW5nIGBUZW1wbGF0ZWAgaW5zdGFuY2UsIHNvIHByb2Nlc3MgdGhlIG5vZGVzIGRpcmVjdGx5LlxuICAgICAgdGVtcGxhdGUuZm9yRWFjaChub2RlID0+IG5vZGUudmlzaXQodGhpcykpO1xuICAgIH1cbiAgfVxuXG4gIHZpc2l0RWxlbWVudChlbGVtZW50OiBFbGVtZW50KSB7XG4gICAgLy8gYEVsZW1lbnRgcyBpbiB0aGUgdGVtcGxhdGUgbWF5IGhhdmUgYFJlZmVyZW5jZWBzIHdoaWNoIGFyZSBjYXB0dXJlZCBpbiB0aGUgc2NvcGUuXG4gICAgZWxlbWVudC5yZWZlcmVuY2VzLmZvckVhY2gobm9kZSA9PiB0aGlzLnZpc2l0UmVmZXJlbmNlKG5vZGUpKTtcblxuICAgIC8vIFJlY3Vyc2UgaW50byB0aGUgYEVsZW1lbnRgJ3MgY2hpbGRyZW4uXG4gICAgZWxlbWVudC5jaGlsZHJlbi5mb3JFYWNoKG5vZGUgPT4gbm9kZS52aXNpdCh0aGlzKSk7XG4gIH1cblxuICB2aXNpdFRlbXBsYXRlKHRlbXBsYXRlOiBUZW1wbGF0ZSkge1xuICAgIC8vIFJlZmVyZW5jZXMgb24gYSA8bmctdGVtcGxhdGU+IGFyZSBkZWZpbmVkIGluIHRoZSBvdXRlciBzY29wZSwgc28gY2FwdHVyZSB0aGVtIGJlZm9yZVxuICAgIC8vIHByb2Nlc3NpbmcgdGhlIHRlbXBsYXRlJ3MgY2hpbGQgc2NvcGUuXG4gICAgdGVtcGxhdGUucmVmZXJlbmNlcy5mb3JFYWNoKG5vZGUgPT4gdGhpcy52aXNpdFJlZmVyZW5jZShub2RlKSk7XG5cbiAgICAvLyBOZXh0LCBjcmVhdGUgYW4gaW5uZXIgc2NvcGUgYW5kIHByb2Nlc3MgdGhlIHRlbXBsYXRlIHdpdGhpbiBpdC5cbiAgICBjb25zdCBzY29wZSA9IG5ldyBTY29wZSh0aGlzLCB0ZW1wbGF0ZSk7XG4gICAgc2NvcGUuaW5nZXN0KHRlbXBsYXRlKTtcbiAgICB0aGlzLmNoaWxkU2NvcGVzLnNldCh0ZW1wbGF0ZSwgc2NvcGUpO1xuICB9XG5cbiAgdmlzaXRWYXJpYWJsZSh2YXJpYWJsZTogVmFyaWFibGUpIHtcbiAgICAvLyBEZWNsYXJlIHRoZSB2YXJpYWJsZSBpZiBpdCdzIG5vdCBhbHJlYWR5LlxuICAgIHRoaXMubWF5YmVEZWNsYXJlKHZhcmlhYmxlKTtcbiAgfVxuXG4gIHZpc2l0UmVmZXJlbmNlKHJlZmVyZW5jZTogUmVmZXJlbmNlKSB7XG4gICAgLy8gRGVjbGFyZSB0aGUgdmFyaWFibGUgaWYgaXQncyBub3QgYWxyZWFkeS5cbiAgICB0aGlzLm1heWJlRGVjbGFyZShyZWZlcmVuY2UpO1xuICB9XG5cbiAgLy8gVW51c2VkIHZpc2l0b3JzLlxuICB2aXNpdENvbnRlbnQoY29udGVudDogQ29udGVudCkge31cbiAgdmlzaXRCb3VuZEF0dHJpYnV0ZShhdHRyOiBCb3VuZEF0dHJpYnV0ZSkge31cbiAgdmlzaXRCb3VuZEV2ZW50KGV2ZW50OiBCb3VuZEV2ZW50KSB7fVxuICB2aXNpdEJvdW5kVGV4dCh0ZXh0OiBCb3VuZFRleHQpIHt9XG4gIHZpc2l0VGV4dCh0ZXh0OiBUZXh0KSB7fVxuICB2aXNpdFRleHRBdHRyaWJ1dGUoYXR0cjogVGV4dEF0dHJpYnV0ZSkge31cbiAgdmlzaXRJY3UoaWN1OiBJY3UpIHt9XG5cbiAgcHJpdmF0ZSBtYXliZURlY2xhcmUodGhpbmc6IFJlZmVyZW5jZXxWYXJpYWJsZSkge1xuICAgIC8vIERlY2xhcmUgc29tZXRoaW5nIHdpdGggYSBuYW1lLCBhcyBsb25nIGFzIHRoYXQgbmFtZSBpc24ndCB0YWtlbi5cbiAgICBpZiAoIXRoaXMubmFtZWRFbnRpdGllcy5oYXModGhpbmcubmFtZSkpIHtcbiAgICAgIHRoaXMubmFtZWRFbnRpdGllcy5zZXQodGhpbmcubmFtZSwgdGhpbmcpO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBMb29rIHVwIGEgdmFyaWFibGUgd2l0aGluIHRoaXMgYFNjb3BlYC5cbiAgICpcbiAgICogVGhpcyBjYW4gcmVjdXJzZSBpbnRvIGEgcGFyZW50IGBTY29wZWAgaWYgaXQncyBhdmFpbGFibGUuXG4gICAqL1xuICBsb29rdXAobmFtZTogc3RyaW5nKTogUmVmZXJlbmNlfFZhcmlhYmxlfG51bGwge1xuICAgIGlmICh0aGlzLm5hbWVkRW50aXRpZXMuaGFzKG5hbWUpKSB7XG4gICAgICAvLyBGb3VuZCBpbiB0aGUgbG9jYWwgc2NvcGUuXG4gICAgICByZXR1cm4gdGhpcy5uYW1lZEVudGl0aWVzLmdldChuYW1lKSE7XG4gICAgfSBlbHNlIGlmICh0aGlzLnBhcmVudFNjb3BlICE9PSBudWxsKSB7XG4gICAgICAvLyBOb3QgaW4gdGhlIGxvY2FsIHNjb3BlLCBidXQgdGhlcmUncyBhIHBhcmVudCBzY29wZSBzbyBjaGVjayB0aGVyZS5cbiAgICAgIHJldHVybiB0aGlzLnBhcmVudFNjb3BlLmxvb2t1cChuYW1lKTtcbiAgICB9IGVsc2Uge1xuICAgICAgLy8gQXQgdGhlIHRvcCBsZXZlbCBhbmQgaXQgd2Fzbid0IGZvdW5kLlxuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuICB9XG5cbiAgLyoqXG4gICAqIEdldCB0aGUgY2hpbGQgc2NvcGUgZm9yIGEgYFRlbXBsYXRlYC5cbiAgICpcbiAgICogVGhpcyBzaG91bGQgYWx3YXlzIGJlIGRlZmluZWQuXG4gICAqL1xuICBnZXRDaGlsZFNjb3BlKHRlbXBsYXRlOiBUZW1wbGF0ZSk6IFNjb3BlIHtcbiAgICBjb25zdCByZXMgPSB0aGlzLmNoaWxkU2NvcGVzLmdldCh0ZW1wbGF0ZSk7XG4gICAgaWYgKHJlcyA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYEFzc2VydGlvbiBlcnJvcjogY2hpbGQgc2NvcGUgZm9yICR7dGVtcGxhdGV9IG5vdCBmb3VuZGApO1xuICAgIH1cbiAgICByZXR1cm4gcmVzO1xuICB9XG59XG5cbi8qKlxuICogUHJvY2Vzc2VzIGEgdGVtcGxhdGUgYW5kIG1hdGNoZXMgZGlyZWN0aXZlcyBvbiBub2RlcyAoZWxlbWVudHMgYW5kIHRlbXBsYXRlcykuXG4gKlxuICogVXN1YWxseSB1c2VkIHZpYSB0aGUgc3RhdGljIGBhcHBseSgpYCBtZXRob2QuXG4gKi9cbmNsYXNzIERpcmVjdGl2ZUJpbmRlcjxEaXJlY3RpdmVUIGV4dGVuZHMgRGlyZWN0aXZlTWV0YT4gaW1wbGVtZW50cyBWaXNpdG9yIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIG1hdGNoZXI6IFNlbGVjdG9yTWF0Y2hlcjxEaXJlY3RpdmVUPixcbiAgICAgIHByaXZhdGUgZGlyZWN0aXZlczogTWFwPEVsZW1lbnR8VGVtcGxhdGUsIERpcmVjdGl2ZVRbXT4sXG4gICAgICBwcml2YXRlIGJpbmRpbmdzOiBNYXA8Qm91bmRBdHRyaWJ1dGV8Qm91bmRFdmVudHxUZXh0QXR0cmlidXRlLCBEaXJlY3RpdmVUfEVsZW1lbnR8VGVtcGxhdGU+LFxuICAgICAgcHJpdmF0ZSByZWZlcmVuY2VzOlxuICAgICAgICAgIE1hcDxSZWZlcmVuY2UsIHtkaXJlY3RpdmU6IERpcmVjdGl2ZVQsIG5vZGU6IEVsZW1lbnR8VGVtcGxhdGV9fEVsZW1lbnR8VGVtcGxhdGU+KSB7fVxuXG4gIC8qKlxuICAgKiBQcm9jZXNzIGEgdGVtcGxhdGUgKGxpc3Qgb2YgYE5vZGVgcykgYW5kIHBlcmZvcm0gZGlyZWN0aXZlIG1hdGNoaW5nIGFnYWluc3QgZWFjaCBub2RlLlxuICAgKlxuICAgKiBAcGFyYW0gdGVtcGxhdGUgdGhlIGxpc3Qgb2YgdGVtcGxhdGUgYE5vZGVgcyB0byBtYXRjaCAocmVjdXJzaXZlbHkpLlxuICAgKiBAcGFyYW0gc2VsZWN0b3JNYXRjaGVyIGEgYFNlbGVjdG9yTWF0Y2hlcmAgY29udGFpbmluZyB0aGUgZGlyZWN0aXZlcyB0aGF0IGFyZSBpbiBzY29wZSBmb3JcbiAgICogdGhpcyB0ZW1wbGF0ZS5cbiAgICogQHJldHVybnMgdGhyZWUgbWFwcyB3aGljaCBjb250YWluIGluZm9ybWF0aW9uIGFib3V0IGRpcmVjdGl2ZXMgaW4gdGhlIHRlbXBsYXRlOiB0aGVcbiAgICogYGRpcmVjdGl2ZXNgIG1hcCB3aGljaCBsaXN0cyBkaXJlY3RpdmVzIG1hdGNoZWQgb24gZWFjaCBub2RlLCB0aGUgYGJpbmRpbmdzYCBtYXAgd2hpY2hcbiAgICogaW5kaWNhdGVzIHdoaWNoIGRpcmVjdGl2ZXMgY2xhaW1lZCB3aGljaCBiaW5kaW5ncyAoaW5wdXRzLCBvdXRwdXRzLCBldGMpLCBhbmQgdGhlIGByZWZlcmVuY2VzYFxuICAgKiBtYXAgd2hpY2ggcmVzb2x2ZXMgI3JlZmVyZW5jZXMgKGBSZWZlcmVuY2Vgcykgd2l0aGluIHRoZSB0ZW1wbGF0ZSB0byB0aGUgbmFtZWQgZGlyZWN0aXZlIG9yXG4gICAqIHRlbXBsYXRlIG5vZGUuXG4gICAqL1xuICBzdGF0aWMgYXBwbHk8RGlyZWN0aXZlVCBleHRlbmRzIERpcmVjdGl2ZU1ldGE+KFxuICAgICAgdGVtcGxhdGU6IE5vZGVbXSwgc2VsZWN0b3JNYXRjaGVyOiBTZWxlY3Rvck1hdGNoZXI8RGlyZWN0aXZlVD4pOiB7XG4gICAgZGlyZWN0aXZlczogTWFwPEVsZW1lbnR8VGVtcGxhdGUsIERpcmVjdGl2ZVRbXT4sXG4gICAgYmluZGluZ3M6IE1hcDxCb3VuZEF0dHJpYnV0ZXxCb3VuZEV2ZW50fFRleHRBdHRyaWJ1dGUsIERpcmVjdGl2ZVR8RWxlbWVudHxUZW1wbGF0ZT4sXG4gICAgcmVmZXJlbmNlczogTWFwPFJlZmVyZW5jZSwge2RpcmVjdGl2ZTogRGlyZWN0aXZlVCwgbm9kZTogRWxlbWVudHxUZW1wbGF0ZX18RWxlbWVudHxUZW1wbGF0ZT4sXG4gIH0ge1xuICAgIGNvbnN0IGRpcmVjdGl2ZXMgPSBuZXcgTWFwPEVsZW1lbnR8VGVtcGxhdGUsIERpcmVjdGl2ZVRbXT4oKTtcbiAgICBjb25zdCBiaW5kaW5ncyA9XG4gICAgICAgIG5ldyBNYXA8Qm91bmRBdHRyaWJ1dGV8Qm91bmRFdmVudHxUZXh0QXR0cmlidXRlLCBEaXJlY3RpdmVUfEVsZW1lbnR8VGVtcGxhdGU+KCk7XG4gICAgY29uc3QgcmVmZXJlbmNlcyA9XG4gICAgICAgIG5ldyBNYXA8UmVmZXJlbmNlLCB7ZGlyZWN0aXZlOiBEaXJlY3RpdmVULCBub2RlOiBFbGVtZW50IHwgVGVtcGxhdGV9fEVsZW1lbnR8VGVtcGxhdGU+KCk7XG4gICAgY29uc3QgbWF0Y2hlciA9IG5ldyBEaXJlY3RpdmVCaW5kZXIoc2VsZWN0b3JNYXRjaGVyLCBkaXJlY3RpdmVzLCBiaW5kaW5ncywgcmVmZXJlbmNlcyk7XG4gICAgbWF0Y2hlci5pbmdlc3QodGVtcGxhdGUpO1xuICAgIHJldHVybiB7ZGlyZWN0aXZlcywgYmluZGluZ3MsIHJlZmVyZW5jZXN9O1xuICB9XG5cbiAgcHJpdmF0ZSBpbmdlc3QodGVtcGxhdGU6IE5vZGVbXSk6IHZvaWQge1xuICAgIHRlbXBsYXRlLmZvckVhY2gobm9kZSA9PiBub2RlLnZpc2l0KHRoaXMpKTtcbiAgfVxuXG4gIHZpc2l0RWxlbWVudChlbGVtZW50OiBFbGVtZW50KTogdm9pZCB7XG4gICAgdGhpcy52aXNpdEVsZW1lbnRPclRlbXBsYXRlKGVsZW1lbnQubmFtZSwgZWxlbWVudCk7XG4gIH1cblxuICB2aXNpdFRlbXBsYXRlKHRlbXBsYXRlOiBUZW1wbGF0ZSk6IHZvaWQge1xuICAgIHRoaXMudmlzaXRFbGVtZW50T3JUZW1wbGF0ZSgnbmctdGVtcGxhdGUnLCB0ZW1wbGF0ZSk7XG4gIH1cblxuICB2aXNpdEVsZW1lbnRPclRlbXBsYXRlKGVsZW1lbnROYW1lOiBzdHJpbmcsIG5vZGU6IEVsZW1lbnR8VGVtcGxhdGUpOiB2b2lkIHtcbiAgICAvLyBGaXJzdCwgZGV0ZXJtaW5lIHRoZSBIVE1MIHNoYXBlIG9mIHRoZSBub2RlIGZvciB0aGUgcHVycG9zZSBvZiBkaXJlY3RpdmUgbWF0Y2hpbmcuXG4gICAgLy8gRG8gdGhpcyBieSBidWlsZGluZyB1cCBhIGBDc3NTZWxlY3RvcmAgZm9yIHRoZSBub2RlLlxuICAgIGNvbnN0IGNzc1NlbGVjdG9yID0gY3JlYXRlQ3NzU2VsZWN0b3IoZWxlbWVudE5hbWUsIGdldEF0dHJzRm9yRGlyZWN0aXZlTWF0Y2hpbmcobm9kZSkpO1xuXG4gICAgLy8gTmV4dCwgdXNlIHRoZSBgU2VsZWN0b3JNYXRjaGVyYCB0byBnZXQgdGhlIGxpc3Qgb2YgZGlyZWN0aXZlcyBvbiB0aGUgbm9kZS5cbiAgICBjb25zdCBkaXJlY3RpdmVzOiBEaXJlY3RpdmVUW10gPSBbXTtcbiAgICB0aGlzLm1hdGNoZXIubWF0Y2goY3NzU2VsZWN0b3IsIChfLCBkaXJlY3RpdmUpID0+IGRpcmVjdGl2ZXMucHVzaChkaXJlY3RpdmUpKTtcbiAgICBpZiAoZGlyZWN0aXZlcy5sZW5ndGggPiAwKSB7XG4gICAgICB0aGlzLmRpcmVjdGl2ZXMuc2V0KG5vZGUsIGRpcmVjdGl2ZXMpO1xuICAgIH1cblxuICAgIC8vIFJlc29sdmUgYW55IHJlZmVyZW5jZXMgdGhhdCBhcmUgY3JlYXRlZCBvbiB0aGlzIG5vZGUuXG4gICAgbm9kZS5yZWZlcmVuY2VzLmZvckVhY2gocmVmID0+IHtcbiAgICAgIGxldCBkaXJUYXJnZXQ6IERpcmVjdGl2ZVR8bnVsbCA9IG51bGw7XG5cbiAgICAgIC8vIElmIHRoZSByZWZlcmVuY2UgZXhwcmVzc2lvbiBpcyBlbXB0eSwgdGhlbiBpdCBtYXRjaGVzIHRoZSBcInByaW1hcnlcIiBkaXJlY3RpdmUgb24gdGhlIG5vZGVcbiAgICAgIC8vIChpZiB0aGVyZSBpcyBvbmUpLiBPdGhlcndpc2UgaXQgbWF0Y2hlcyB0aGUgaG9zdCBub2RlIGl0c2VsZiAoZWl0aGVyIGFuIGVsZW1lbnQgb3JcbiAgICAgIC8vIDxuZy10ZW1wbGF0ZT4gbm9kZSkuXG4gICAgICBpZiAocmVmLnZhbHVlLnRyaW0oKSA9PT0gJycpIHtcbiAgICAgICAgLy8gVGhpcyBjb3VsZCBiZSBhIHJlZmVyZW5jZSB0byBhIGNvbXBvbmVudCBpZiB0aGVyZSBpcyBvbmUuXG4gICAgICAgIGRpclRhcmdldCA9IGRpcmVjdGl2ZXMuZmluZChkaXIgPT4gZGlyLmlzQ29tcG9uZW50KSB8fCBudWxsO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgLy8gVGhpcyBzaG91bGQgYmUgYSByZWZlcmVuY2UgdG8gYSBkaXJlY3RpdmUgZXhwb3J0ZWQgdmlhIGV4cG9ydEFzLlxuICAgICAgICBkaXJUYXJnZXQgPVxuICAgICAgICAgICAgZGlyZWN0aXZlcy5maW5kKFxuICAgICAgICAgICAgICAgIGRpciA9PiBkaXIuZXhwb3J0QXMgIT09IG51bGwgJiYgZGlyLmV4cG9ydEFzLnNvbWUodmFsdWUgPT4gdmFsdWUgPT09IHJlZi52YWx1ZSkpIHx8XG4gICAgICAgICAgICBudWxsO1xuICAgICAgICAvLyBDaGVjayBpZiBhIG1hdGNoaW5nIGRpcmVjdGl2ZSB3YXMgZm91bmQuXG4gICAgICAgIGlmIChkaXJUYXJnZXQgPT09IG51bGwpIHtcbiAgICAgICAgICAvLyBObyBtYXRjaGluZyBkaXJlY3RpdmUgd2FzIGZvdW5kIC0gdGhpcyByZWZlcmVuY2UgcG9pbnRzIHRvIGFuIHVua25vd24gdGFyZ2V0LiBMZWF2ZSBpdFxuICAgICAgICAgIC8vIHVubWFwcGVkLlxuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICBpZiAoZGlyVGFyZ2V0ICE9PSBudWxsKSB7XG4gICAgICAgIC8vIFRoaXMgcmVmZXJlbmNlIHBvaW50cyB0byBhIGRpcmVjdGl2ZS5cbiAgICAgICAgdGhpcy5yZWZlcmVuY2VzLnNldChyZWYsIHtkaXJlY3RpdmU6IGRpclRhcmdldCwgbm9kZX0pO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgLy8gVGhpcyByZWZlcmVuY2UgcG9pbnRzIHRvIHRoZSBub2RlIGl0c2VsZi5cbiAgICAgICAgdGhpcy5yZWZlcmVuY2VzLnNldChyZWYsIG5vZGUpO1xuICAgICAgfVxuICAgIH0pO1xuXG4gICAgLy8gQXNzb2NpYXRlIGF0dHJpYnV0ZXMvYmluZGluZ3Mgb24gdGhlIG5vZGUgd2l0aCBkaXJlY3RpdmVzIG9yIHdpdGggdGhlIG5vZGUgaXRzZWxmLlxuICAgIHR5cGUgQm91bmROb2RlID0gQm91bmRBdHRyaWJ1dGV8Qm91bmRFdmVudHxUZXh0QXR0cmlidXRlO1xuICAgIGNvbnN0IHNldEF0dHJpYnV0ZUJpbmRpbmcgPVxuICAgICAgICAoYXR0cmlidXRlOiBCb3VuZE5vZGUsIGlvVHlwZToga2V5b2YgUGljazxEaXJlY3RpdmVNZXRhLCAnaW5wdXRzJ3wnb3V0cHV0cyc+KSA9PiB7XG4gICAgICAgICAgY29uc3QgZGlyID0gZGlyZWN0aXZlcy5maW5kKGRpciA9PiBkaXJbaW9UeXBlXS5oYXNCaW5kaW5nUHJvcGVydHlOYW1lKGF0dHJpYnV0ZS5uYW1lKSk7XG4gICAgICAgICAgY29uc3QgYmluZGluZyA9IGRpciAhPT0gdW5kZWZpbmVkID8gZGlyIDogbm9kZTtcbiAgICAgICAgICB0aGlzLmJpbmRpbmdzLnNldChhdHRyaWJ1dGUsIGJpbmRpbmcpO1xuICAgICAgICB9O1xuXG4gICAgLy8gTm9kZSBpbnB1dHMgKGJvdW5kIGF0dHJpYnV0ZXMpIGFuZCB0ZXh0IGF0dHJpYnV0ZXMgY2FuIGJlIGJvdW5kIHRvIGFuXG4gICAgLy8gaW5wdXQgb24gYSBkaXJlY3RpdmUuXG4gICAgbm9kZS5pbnB1dHMuZm9yRWFjaChpbnB1dCA9PiBzZXRBdHRyaWJ1dGVCaW5kaW5nKGlucHV0LCAnaW5wdXRzJykpO1xuICAgIG5vZGUuYXR0cmlidXRlcy5mb3JFYWNoKGF0dHIgPT4gc2V0QXR0cmlidXRlQmluZGluZyhhdHRyLCAnaW5wdXRzJykpO1xuICAgIGlmIChub2RlIGluc3RhbmNlb2YgVGVtcGxhdGUpIHtcbiAgICAgIG5vZGUudGVtcGxhdGVBdHRycy5mb3JFYWNoKGF0dHIgPT4gc2V0QXR0cmlidXRlQmluZGluZyhhdHRyLCAnaW5wdXRzJykpO1xuICAgIH1cbiAgICAvLyBOb2RlIG91dHB1dHMgKGJvdW5kIGV2ZW50cykgY2FuIGJlIGJvdW5kIHRvIGFuIG91dHB1dCBvbiBhIGRpcmVjdGl2ZS5cbiAgICBub2RlLm91dHB1dHMuZm9yRWFjaChvdXRwdXQgPT4gc2V0QXR0cmlidXRlQmluZGluZyhvdXRwdXQsICdvdXRwdXRzJykpO1xuXG4gICAgLy8gUmVjdXJzZSBpbnRvIHRoZSBub2RlJ3MgY2hpbGRyZW4uXG4gICAgbm9kZS5jaGlsZHJlbi5mb3JFYWNoKGNoaWxkID0+IGNoaWxkLnZpc2l0KHRoaXMpKTtcbiAgfVxuXG4gIC8vIFVudXNlZCB2aXNpdG9ycy5cbiAgdmlzaXRDb250ZW50KGNvbnRlbnQ6IENvbnRlbnQpOiB2b2lkIHt9XG4gIHZpc2l0VmFyaWFibGUodmFyaWFibGU6IFZhcmlhYmxlKTogdm9pZCB7fVxuICB2aXNpdFJlZmVyZW5jZShyZWZlcmVuY2U6IFJlZmVyZW5jZSk6IHZvaWQge31cbiAgdmlzaXRUZXh0QXR0cmlidXRlKGF0dHJpYnV0ZTogVGV4dEF0dHJpYnV0ZSk6IHZvaWQge31cbiAgdmlzaXRCb3VuZEF0dHJpYnV0ZShhdHRyaWJ1dGU6IEJvdW5kQXR0cmlidXRlKTogdm9pZCB7fVxuICB2aXNpdEJvdW5kRXZlbnQoYXR0cmlidXRlOiBCb3VuZEV2ZW50KTogdm9pZCB7fVxuICB2aXNpdEJvdW5kQXR0cmlidXRlT3JFdmVudChub2RlOiBCb3VuZEF0dHJpYnV0ZXxCb3VuZEV2ZW50KSB7fVxuICB2aXNpdFRleHQodGV4dDogVGV4dCk6IHZvaWQge31cbiAgdmlzaXRCb3VuZFRleHQodGV4dDogQm91bmRUZXh0KTogdm9pZCB7fVxuICB2aXNpdEljdShpY3U6IEljdSk6IHZvaWQge31cbn1cblxuLyoqXG4gKiBQcm9jZXNzZXMgYSB0ZW1wbGF0ZSBhbmQgZXh0cmFjdCBtZXRhZGF0YSBhYm91dCBleHByZXNzaW9ucyBhbmQgc3ltYm9scyB3aXRoaW4uXG4gKlxuICogVGhpcyBpcyBhIGNvbXBhbmlvbiB0byB0aGUgYERpcmVjdGl2ZUJpbmRlcmAgdGhhdCBkb2Vzbid0IHJlcXVpcmUga25vd2xlZGdlIG9mIGRpcmVjdGl2ZXMgbWF0Y2hlZFxuICogd2l0aGluIHRoZSB0ZW1wbGF0ZSBpbiBvcmRlciB0byBvcGVyYXRlLlxuICpcbiAqIEV4cHJlc3Npb25zIGFyZSB2aXNpdGVkIGJ5IHRoZSBzdXBlcmNsYXNzIGBSZWN1cnNpdmVBc3RWaXNpdG9yYCwgd2l0aCBjdXN0b20gbG9naWMgcHJvdmlkZWRcbiAqIGJ5IG92ZXJyaWRkZW4gbWV0aG9kcyBmcm9tIHRoYXQgdmlzaXRvci5cbiAqL1xuY2xhc3MgVGVtcGxhdGVCaW5kZXIgZXh0ZW5kcyBSZWN1cnNpdmVBc3RWaXNpdG9yIGltcGxlbWVudHMgVmlzaXRvciB7XG4gIHByaXZhdGUgdmlzaXROb2RlOiAobm9kZTogTm9kZSkgPT4gdm9pZDtcblxuICBwcml2YXRlIHBpcGVzVXNlZDogc3RyaW5nW10gPSBbXTtcblxuICBwcml2YXRlIGNvbnN0cnVjdG9yKFxuICAgICAgcHJpdmF0ZSBiaW5kaW5nczogTWFwPEFTVCwgUmVmZXJlbmNlfFZhcmlhYmxlPixcbiAgICAgIHByaXZhdGUgc3ltYm9sczogTWFwPFJlZmVyZW5jZXxWYXJpYWJsZSwgVGVtcGxhdGU+LCBwcml2YXRlIHVzZWRQaXBlczogU2V0PHN0cmluZz4sXG4gICAgICBwcml2YXRlIG5lc3RpbmdMZXZlbDogTWFwPFRlbXBsYXRlLCBudW1iZXI+LCBwcml2YXRlIHNjb3BlOiBTY29wZSxcbiAgICAgIHByaXZhdGUgdGVtcGxhdGU6IFRlbXBsYXRlfG51bGwsIHByaXZhdGUgbGV2ZWw6IG51bWJlcikge1xuICAgIHN1cGVyKCk7XG5cbiAgICAvLyBTYXZlIGEgYml0IG9mIHByb2Nlc3NpbmcgdGltZSBieSBjb25zdHJ1Y3RpbmcgdGhpcyBjbG9zdXJlIGluIGFkdmFuY2UuXG4gICAgdGhpcy52aXNpdE5vZGUgPSAobm9kZTogTm9kZSkgPT4gbm9kZS52aXNpdCh0aGlzKTtcbiAgfVxuXG4gIC8vIFRoaXMgbWV0aG9kIGlzIGRlZmluZWQgdG8gcmVjb25jaWxlIHRoZSB0eXBlIG9mIFRlbXBsYXRlQmluZGVyIHNpbmNlIGJvdGhcbiAgLy8gUmVjdXJzaXZlQXN0VmlzaXRvciBhbmQgVmlzaXRvciBkZWZpbmUgdGhlIHZpc2l0KCkgbWV0aG9kIGluIHRoZWlyXG4gIC8vIGludGVyZmFjZXMuXG4gIHZpc2l0KG5vZGU6IEFTVHxOb2RlLCBjb250ZXh0PzogYW55KSB7XG4gICAgaWYgKG5vZGUgaW5zdGFuY2VvZiBBU1QpIHtcbiAgICAgIG5vZGUudmlzaXQodGhpcywgY29udGV4dCk7XG4gICAgfSBlbHNlIHtcbiAgICAgIG5vZGUudmlzaXQodGhpcyk7XG4gICAgfVxuICB9XG5cbiAgLyoqXG4gICAqIFByb2Nlc3MgYSB0ZW1wbGF0ZSBhbmQgZXh0cmFjdCBtZXRhZGF0YSBhYm91dCBleHByZXNzaW9ucyBhbmQgc3ltYm9scyB3aXRoaW4uXG4gICAqXG4gICAqIEBwYXJhbSB0ZW1wbGF0ZSB0aGUgbm9kZXMgb2YgdGhlIHRlbXBsYXRlIHRvIHByb2Nlc3NcbiAgICogQHBhcmFtIHNjb3BlIHRoZSBgU2NvcGVgIG9mIHRoZSB0ZW1wbGF0ZSBiZWluZyBwcm9jZXNzZWQuXG4gICAqIEByZXR1cm5zIHRocmVlIG1hcHMgd2hpY2ggY29udGFpbiBtZXRhZGF0YSBhYm91dCB0aGUgdGVtcGxhdGU6IGBleHByZXNzaW9uc2Agd2hpY2ggaW50ZXJwcmV0c1xuICAgKiBzcGVjaWFsIGBBU1RgIG5vZGVzIGluIGV4cHJlc3Npb25zIGFzIHBvaW50aW5nIHRvIHJlZmVyZW5jZXMgb3IgdmFyaWFibGVzIGRlY2xhcmVkIHdpdGhpbiB0aGVcbiAgICogdGVtcGxhdGUsIGBzeW1ib2xzYCB3aGljaCBtYXBzIHRob3NlIHZhcmlhYmxlcyBhbmQgcmVmZXJlbmNlcyB0byB0aGUgbmVzdGVkIGBUZW1wbGF0ZWAgd2hpY2hcbiAgICogZGVjbGFyZXMgdGhlbSwgaWYgYW55LCBhbmQgYG5lc3RpbmdMZXZlbGAgd2hpY2ggYXNzb2NpYXRlcyBlYWNoIGBUZW1wbGF0ZWAgd2l0aCBhIGludGVnZXJcbiAgICogbmVzdGluZyBsZXZlbCAoaG93IG1hbnkgbGV2ZWxzIGRlZXAgd2l0aGluIHRoZSB0ZW1wbGF0ZSBzdHJ1Y3R1cmUgdGhlIGBUZW1wbGF0ZWAgaXMpLCBzdGFydGluZ1xuICAgKiBhdCAxLlxuICAgKi9cbiAgc3RhdGljIGFwcGx5KHRlbXBsYXRlOiBOb2RlW10sIHNjb3BlOiBTY29wZSk6IHtcbiAgICBleHByZXNzaW9uczogTWFwPEFTVCwgUmVmZXJlbmNlfFZhcmlhYmxlPixcbiAgICBzeW1ib2xzOiBNYXA8VmFyaWFibGV8UmVmZXJlbmNlLCBUZW1wbGF0ZT4sXG4gICAgbmVzdGluZ0xldmVsOiBNYXA8VGVtcGxhdGUsIG51bWJlcj4sXG4gICAgdXNlZFBpcGVzOiBTZXQ8c3RyaW5nPixcbiAgfSB7XG4gICAgY29uc3QgZXhwcmVzc2lvbnMgPSBuZXcgTWFwPEFTVCwgUmVmZXJlbmNlfFZhcmlhYmxlPigpO1xuICAgIGNvbnN0IHN5bWJvbHMgPSBuZXcgTWFwPFZhcmlhYmxlfFJlZmVyZW5jZSwgVGVtcGxhdGU+KCk7XG4gICAgY29uc3QgbmVzdGluZ0xldmVsID0gbmV3IE1hcDxUZW1wbGF0ZSwgbnVtYmVyPigpO1xuICAgIGNvbnN0IHVzZWRQaXBlcyA9IG5ldyBTZXQ8c3RyaW5nPigpO1xuICAgIC8vIFRoZSB0b3AtbGV2ZWwgdGVtcGxhdGUgaGFzIG5lc3RpbmcgbGV2ZWwgMC5cbiAgICBjb25zdCBiaW5kZXIgPSBuZXcgVGVtcGxhdGVCaW5kZXIoXG4gICAgICAgIGV4cHJlc3Npb25zLCBzeW1ib2xzLCB1c2VkUGlwZXMsIG5lc3RpbmdMZXZlbCwgc2NvcGUsXG4gICAgICAgIHRlbXBsYXRlIGluc3RhbmNlb2YgVGVtcGxhdGUgPyB0ZW1wbGF0ZSA6IG51bGwsIDApO1xuICAgIGJpbmRlci5pbmdlc3QodGVtcGxhdGUpO1xuICAgIHJldHVybiB7ZXhwcmVzc2lvbnMsIHN5bWJvbHMsIG5lc3RpbmdMZXZlbCwgdXNlZFBpcGVzfTtcbiAgfVxuXG4gIHByaXZhdGUgaW5nZXN0KHRlbXBsYXRlOiBUZW1wbGF0ZXxOb2RlW10pOiB2b2lkIHtcbiAgICBpZiAodGVtcGxhdGUgaW5zdGFuY2VvZiBUZW1wbGF0ZSkge1xuICAgICAgLy8gRm9yIDxuZy10ZW1wbGF0ZT5zLCBwcm9jZXNzIG9ubHkgdmFyaWFibGVzIGFuZCBjaGlsZCBub2Rlcy4gSW5wdXRzLCBvdXRwdXRzLCB0ZW1wbGF0ZUF0dHJzLFxuICAgICAgLy8gYW5kIHJlZmVyZW5jZXMgd2VyZSBhbGwgcHJvY2Vzc2VkIGluIHRoZSBzY29wZSBvZiB0aGUgY29udGFpbmluZyB0ZW1wbGF0ZS5cbiAgICAgIHRlbXBsYXRlLnZhcmlhYmxlcy5mb3JFYWNoKHRoaXMudmlzaXROb2RlKTtcbiAgICAgIHRlbXBsYXRlLmNoaWxkcmVuLmZvckVhY2godGhpcy52aXNpdE5vZGUpO1xuXG4gICAgICAvLyBTZXQgdGhlIG5lc3RpbmcgbGV2ZWwuXG4gICAgICB0aGlzLm5lc3RpbmdMZXZlbC5zZXQodGVtcGxhdGUsIHRoaXMubGV2ZWwpO1xuICAgIH0gZWxzZSB7XG4gICAgICAvLyBWaXNpdCBlYWNoIG5vZGUgZnJvbSB0aGUgdG9wLWxldmVsIHRlbXBsYXRlLlxuICAgICAgdGVtcGxhdGUuZm9yRWFjaCh0aGlzLnZpc2l0Tm9kZSk7XG4gICAgfVxuICB9XG5cbiAgdmlzaXRFbGVtZW50KGVsZW1lbnQ6IEVsZW1lbnQpIHtcbiAgICAvLyBWaXNpdCB0aGUgaW5wdXRzLCBvdXRwdXRzLCBhbmQgY2hpbGRyZW4gb2YgdGhlIGVsZW1lbnQuXG4gICAgZWxlbWVudC5pbnB1dHMuZm9yRWFjaCh0aGlzLnZpc2l0Tm9kZSk7XG4gICAgZWxlbWVudC5vdXRwdXRzLmZvckVhY2godGhpcy52aXNpdE5vZGUpO1xuICAgIGVsZW1lbnQuY2hpbGRyZW4uZm9yRWFjaCh0aGlzLnZpc2l0Tm9kZSk7XG4gIH1cblxuICB2aXNpdFRlbXBsYXRlKHRlbXBsYXRlOiBUZW1wbGF0ZSkge1xuICAgIC8vIEZpcnN0LCB2aXNpdCBpbnB1dHMsIG91dHB1dHMgYW5kIHRlbXBsYXRlIGF0dHJpYnV0ZXMgb2YgdGhlIHRlbXBsYXRlIG5vZGUuXG4gICAgdGVtcGxhdGUuaW5wdXRzLmZvckVhY2godGhpcy52aXNpdE5vZGUpO1xuICAgIHRlbXBsYXRlLm91dHB1dHMuZm9yRWFjaCh0aGlzLnZpc2l0Tm9kZSk7XG4gICAgdGVtcGxhdGUudGVtcGxhdGVBdHRycy5mb3JFYWNoKHRoaXMudmlzaXROb2RlKTtcblxuICAgIC8vIFJlZmVyZW5jZXMgYXJlIGFsc28gZXZhbHVhdGVkIGluIHRoZSBvdXRlciBjb250ZXh0LlxuICAgIHRlbXBsYXRlLnJlZmVyZW5jZXMuZm9yRWFjaCh0aGlzLnZpc2l0Tm9kZSk7XG5cbiAgICAvLyBOZXh0LCByZWN1cnNlIGludG8gdGhlIHRlbXBsYXRlIHVzaW5nIGl0cyBzY29wZSwgYW5kIGJ1bXBpbmcgdGhlIG5lc3RpbmcgbGV2ZWwgdXAgYnkgb25lLlxuICAgIGNvbnN0IGNoaWxkU2NvcGUgPSB0aGlzLnNjb3BlLmdldENoaWxkU2NvcGUodGVtcGxhdGUpO1xuICAgIGNvbnN0IGJpbmRlciA9IG5ldyBUZW1wbGF0ZUJpbmRlcihcbiAgICAgICAgdGhpcy5iaW5kaW5ncywgdGhpcy5zeW1ib2xzLCB0aGlzLnVzZWRQaXBlcywgdGhpcy5uZXN0aW5nTGV2ZWwsIGNoaWxkU2NvcGUsIHRlbXBsYXRlLFxuICAgICAgICB0aGlzLmxldmVsICsgMSk7XG4gICAgYmluZGVyLmluZ2VzdCh0ZW1wbGF0ZSk7XG4gIH1cblxuICB2aXNpdFZhcmlhYmxlKHZhcmlhYmxlOiBWYXJpYWJsZSkge1xuICAgIC8vIFJlZ2lzdGVyIHRoZSBgVmFyaWFibGVgIGFzIGEgc3ltYm9sIGluIHRoZSBjdXJyZW50IGBUZW1wbGF0ZWAuXG4gICAgaWYgKHRoaXMudGVtcGxhdGUgIT09IG51bGwpIHtcbiAgICAgIHRoaXMuc3ltYm9scy5zZXQodmFyaWFibGUsIHRoaXMudGVtcGxhdGUpO1xuICAgIH1cbiAgfVxuXG4gIHZpc2l0UmVmZXJlbmNlKHJlZmVyZW5jZTogUmVmZXJlbmNlKSB7XG4gICAgLy8gUmVnaXN0ZXIgdGhlIGBSZWZlcmVuY2VgIGFzIGEgc3ltYm9sIGluIHRoZSBjdXJyZW50IGBUZW1wbGF0ZWAuXG4gICAgaWYgKHRoaXMudGVtcGxhdGUgIT09IG51bGwpIHtcbiAgICAgIHRoaXMuc3ltYm9scy5zZXQocmVmZXJlbmNlLCB0aGlzLnRlbXBsYXRlKTtcbiAgICB9XG4gIH1cblxuICAvLyBVbnVzZWQgdGVtcGxhdGUgdmlzaXRvcnNcblxuICB2aXNpdFRleHQodGV4dDogVGV4dCkge31cbiAgdmlzaXRDb250ZW50KGNvbnRlbnQ6IENvbnRlbnQpIHt9XG4gIHZpc2l0VGV4dEF0dHJpYnV0ZShhdHRyaWJ1dGU6IFRleHRBdHRyaWJ1dGUpIHt9XG4gIHZpc2l0SWN1KGljdTogSWN1KTogdm9pZCB7XG4gICAgT2JqZWN0LmtleXMoaWN1LnZhcnMpLmZvckVhY2goa2V5ID0+IGljdS52YXJzW2tleV0udmlzaXQodGhpcykpO1xuICAgIE9iamVjdC5rZXlzKGljdS5wbGFjZWhvbGRlcnMpLmZvckVhY2goa2V5ID0+IGljdS5wbGFjZWhvbGRlcnNba2V5XS52aXNpdCh0aGlzKSk7XG4gIH1cblxuICAvLyBUaGUgcmVtYWluaW5nIHZpc2l0b3JzIGFyZSBjb25jZXJuZWQgd2l0aCBwcm9jZXNzaW5nIEFTVCBleHByZXNzaW9ucyB3aXRoaW4gdGVtcGxhdGUgYmluZGluZ3NcblxuICB2aXNpdEJvdW5kQXR0cmlidXRlKGF0dHJpYnV0ZTogQm91bmRBdHRyaWJ1dGUpIHtcbiAgICBhdHRyaWJ1dGUudmFsdWUudmlzaXQodGhpcyk7XG4gIH1cblxuICB2aXNpdEJvdW5kRXZlbnQoZXZlbnQ6IEJvdW5kRXZlbnQpIHtcbiAgICBldmVudC5oYW5kbGVyLnZpc2l0KHRoaXMpO1xuICB9XG5cbiAgdmlzaXRCb3VuZFRleHQodGV4dDogQm91bmRUZXh0KSB7XG4gICAgdGV4dC52YWx1ZS52aXNpdCh0aGlzKTtcbiAgfVxuICB2aXNpdFBpcGUoYXN0OiBCaW5kaW5nUGlwZSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnVzZWRQaXBlcy5hZGQoYXN0Lm5hbWUpO1xuICAgIHJldHVybiBzdXBlci52aXNpdFBpcGUoYXN0LCBjb250ZXh0KTtcbiAgfVxuXG4gIC8vIFRoZXNlIGZpdmUgdHlwZXMgb2YgQVNUIGV4cHJlc3Npb25zIGNhbiByZWZlciB0byBleHByZXNzaW9uIHJvb3RzLCB3aGljaCBjb3VsZCBiZSB2YXJpYWJsZXNcbiAgLy8gb3IgcmVmZXJlbmNlcyBpbiB0aGUgY3VycmVudCBzY29wZS5cblxuICB2aXNpdFByb3BlcnR5UmVhZChhc3Q6IFByb3BlcnR5UmVhZCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLm1heWJlTWFwKGNvbnRleHQsIGFzdCwgYXN0Lm5hbWUpO1xuICAgIHJldHVybiBzdXBlci52aXNpdFByb3BlcnR5UmVhZChhc3QsIGNvbnRleHQpO1xuICB9XG5cbiAgdmlzaXRTYWZlUHJvcGVydHlSZWFkKGFzdDogU2FmZVByb3BlcnR5UmVhZCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLm1heWJlTWFwKGNvbnRleHQsIGFzdCwgYXN0Lm5hbWUpO1xuICAgIHJldHVybiBzdXBlci52aXNpdFNhZmVQcm9wZXJ0eVJlYWQoYXN0LCBjb250ZXh0KTtcbiAgfVxuXG4gIHZpc2l0UHJvcGVydHlXcml0ZShhc3Q6IFByb3BlcnR5V3JpdGUsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy5tYXliZU1hcChjb250ZXh0LCBhc3QsIGFzdC5uYW1lKTtcbiAgICByZXR1cm4gc3VwZXIudmlzaXRQcm9wZXJ0eVdyaXRlKGFzdCwgY29udGV4dCk7XG4gIH1cblxuICB2aXNpdE1ldGhvZENhbGwoYXN0OiBNZXRob2RDYWxsLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMubWF5YmVNYXAoY29udGV4dCwgYXN0LCBhc3QubmFtZSk7XG4gICAgcmV0dXJuIHN1cGVyLnZpc2l0TWV0aG9kQ2FsbChhc3QsIGNvbnRleHQpO1xuICB9XG5cbiAgdmlzaXRTYWZlTWV0aG9kQ2FsbChhc3Q6IFNhZmVNZXRob2RDYWxsLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMubWF5YmVNYXAoY29udGV4dCwgYXN0LCBhc3QubmFtZSk7XG4gICAgcmV0dXJuIHN1cGVyLnZpc2l0U2FmZU1ldGhvZENhbGwoYXN0LCBjb250ZXh0KTtcbiAgfVxuXG4gIHByaXZhdGUgbWF5YmVNYXAoXG4gICAgICBzY29wZTogU2NvcGUsIGFzdDogUHJvcGVydHlSZWFkfFNhZmVQcm9wZXJ0eVJlYWR8UHJvcGVydHlXcml0ZXxNZXRob2RDYWxsfFNhZmVNZXRob2RDYWxsLFxuICAgICAgbmFtZTogc3RyaW5nKTogdm9pZCB7XG4gICAgLy8gSWYgdGhlIHJlY2VpdmVyIG9mIHRoZSBleHByZXNzaW9uIGlzbid0IHRoZSBgSW1wbGljaXRSZWNlaXZlcmAsIHRoaXMgaXNuJ3QgdGhlIHJvb3Qgb2YgYW5cbiAgICAvLyBgQVNUYCBleHByZXNzaW9uIHRoYXQgbWFwcyB0byBhIGBWYXJpYWJsZWAgb3IgYFJlZmVyZW5jZWAuXG4gICAgaWYgKCEoYXN0LnJlY2VpdmVyIGluc3RhbmNlb2YgSW1wbGljaXRSZWNlaXZlcikpIHtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICAvLyBDaGVjayB3aGV0aGVyIHRoZSBuYW1lIGV4aXN0cyBpbiB0aGUgY3VycmVudCBzY29wZS4gSWYgc28sIG1hcCBpdC4gT3RoZXJ3aXNlLCB0aGUgbmFtZSBpc1xuICAgIC8vIHByb2JhYmx5IGEgcHJvcGVydHkgb24gdGhlIHRvcC1sZXZlbCBjb21wb25lbnQgY29udGV4dC5cbiAgICBsZXQgdGFyZ2V0ID0gdGhpcy5zY29wZS5sb29rdXAobmFtZSk7XG4gICAgaWYgKHRhcmdldCAhPT0gbnVsbCkge1xuICAgICAgdGhpcy5iaW5kaW5ncy5zZXQoYXN0LCB0YXJnZXQpO1xuICAgIH1cbiAgfVxufVxuXG4vKipcbiAqIE1ldGFkYXRhIGNvbnRhaW5lciBmb3IgYSBgVGFyZ2V0YCB0aGF0IGFsbG93cyBxdWVyaWVzIGZvciBzcGVjaWZpYyBiaXRzIG9mIG1ldGFkYXRhLlxuICpcbiAqIFNlZSBgQm91bmRUYXJnZXRgIGZvciBkb2N1bWVudGF0aW9uIG9uIHRoZSBpbmRpdmlkdWFsIG1ldGhvZHMuXG4gKi9cbmV4cG9ydCBjbGFzcyBSM0JvdW5kVGFyZ2V0PERpcmVjdGl2ZVQgZXh0ZW5kcyBEaXJlY3RpdmVNZXRhPiBpbXBsZW1lbnRzIEJvdW5kVGFyZ2V0PERpcmVjdGl2ZVQ+IHtcbiAgY29uc3RydWN0b3IoXG4gICAgICByZWFkb25seSB0YXJnZXQ6IFRhcmdldCwgcHJpdmF0ZSBkaXJlY3RpdmVzOiBNYXA8RWxlbWVudHxUZW1wbGF0ZSwgRGlyZWN0aXZlVFtdPixcbiAgICAgIHByaXZhdGUgYmluZGluZ3M6IE1hcDxCb3VuZEF0dHJpYnV0ZXxCb3VuZEV2ZW50fFRleHRBdHRyaWJ1dGUsIERpcmVjdGl2ZVR8RWxlbWVudHxUZW1wbGF0ZT4sXG4gICAgICBwcml2YXRlIHJlZmVyZW5jZXM6XG4gICAgICAgICAgTWFwPEJvdW5kQXR0cmlidXRlfEJvdW5kRXZlbnR8UmVmZXJlbmNlfFRleHRBdHRyaWJ1dGUsXG4gICAgICAgICAgICAgIHtkaXJlY3RpdmU6IERpcmVjdGl2ZVQsIG5vZGU6IEVsZW1lbnR8VGVtcGxhdGV9fEVsZW1lbnR8VGVtcGxhdGU+LFxuICAgICAgcHJpdmF0ZSBleHByVGFyZ2V0czogTWFwPEFTVCwgUmVmZXJlbmNlfFZhcmlhYmxlPixcbiAgICAgIHByaXZhdGUgc3ltYm9sczogTWFwPFJlZmVyZW5jZXxWYXJpYWJsZSwgVGVtcGxhdGU+LFxuICAgICAgcHJpdmF0ZSBuZXN0aW5nTGV2ZWw6IE1hcDxUZW1wbGF0ZSwgbnVtYmVyPixcbiAgICAgIHByaXZhdGUgdGVtcGxhdGVFbnRpdGllczogTWFwPFRlbXBsYXRlfG51bGwsIFJlYWRvbmx5U2V0PFJlZmVyZW5jZXxWYXJpYWJsZT4+LFxuICAgICAgcHJpdmF0ZSB1c2VkUGlwZXM6IFNldDxzdHJpbmc+KSB7fVxuXG4gIGdldEVudGl0aWVzSW5UZW1wbGF0ZVNjb3BlKHRlbXBsYXRlOiBUZW1wbGF0ZXxudWxsKTogUmVhZG9ubHlTZXQ8UmVmZXJlbmNlfFZhcmlhYmxlPiB7XG4gICAgcmV0dXJuIHRoaXMudGVtcGxhdGVFbnRpdGllcy5nZXQodGVtcGxhdGUpID8/IG5ldyBTZXQoKTtcbiAgfVxuXG4gIGdldERpcmVjdGl2ZXNPZk5vZGUobm9kZTogRWxlbWVudHxUZW1wbGF0ZSk6IERpcmVjdGl2ZVRbXXxudWxsIHtcbiAgICByZXR1cm4gdGhpcy5kaXJlY3RpdmVzLmdldChub2RlKSB8fCBudWxsO1xuICB9XG5cbiAgZ2V0UmVmZXJlbmNlVGFyZ2V0KHJlZjogUmVmZXJlbmNlKToge2RpcmVjdGl2ZTogRGlyZWN0aXZlVCwgbm9kZTogRWxlbWVudHxUZW1wbGF0ZX18RWxlbWVudFxuICAgICAgfFRlbXBsYXRlfG51bGwge1xuICAgIHJldHVybiB0aGlzLnJlZmVyZW5jZXMuZ2V0KHJlZikgfHwgbnVsbDtcbiAgfVxuXG4gIGdldENvbnN1bWVyT2ZCaW5kaW5nKGJpbmRpbmc6IEJvdW5kQXR0cmlidXRlfEJvdW5kRXZlbnR8VGV4dEF0dHJpYnV0ZSk6IERpcmVjdGl2ZVR8RWxlbWVudFxuICAgICAgfFRlbXBsYXRlfG51bGwge1xuICAgIHJldHVybiB0aGlzLmJpbmRpbmdzLmdldChiaW5kaW5nKSB8fCBudWxsO1xuICB9XG5cbiAgZ2V0RXhwcmVzc2lvblRhcmdldChleHByOiBBU1QpOiBSZWZlcmVuY2V8VmFyaWFibGV8bnVsbCB7XG4gICAgcmV0dXJuIHRoaXMuZXhwclRhcmdldHMuZ2V0KGV4cHIpIHx8IG51bGw7XG4gIH1cblxuICBnZXRUZW1wbGF0ZU9mU3ltYm9sKHN5bWJvbDogUmVmZXJlbmNlfFZhcmlhYmxlKTogVGVtcGxhdGV8bnVsbCB7XG4gICAgcmV0dXJuIHRoaXMuc3ltYm9scy5nZXQoc3ltYm9sKSB8fCBudWxsO1xuICB9XG5cbiAgZ2V0TmVzdGluZ0xldmVsKHRlbXBsYXRlOiBUZW1wbGF0ZSk6IG51bWJlciB7XG4gICAgcmV0dXJuIHRoaXMubmVzdGluZ0xldmVsLmdldCh0ZW1wbGF0ZSkgfHwgMDtcbiAgfVxuXG4gIGdldFVzZWREaXJlY3RpdmVzKCk6IERpcmVjdGl2ZVRbXSB7XG4gICAgY29uc3Qgc2V0ID0gbmV3IFNldDxEaXJlY3RpdmVUPigpO1xuICAgIHRoaXMuZGlyZWN0aXZlcy5mb3JFYWNoKGRpcnMgPT4gZGlycy5mb3JFYWNoKGRpciA9PiBzZXQuYWRkKGRpcikpKTtcbiAgICByZXR1cm4gQXJyYXkuZnJvbShzZXQudmFsdWVzKCkpO1xuICB9XG5cbiAgZ2V0VXNlZFBpcGVzKCk6IHN0cmluZ1tdIHtcbiAgICByZXR1cm4gQXJyYXkuZnJvbSh0aGlzLnVzZWRQaXBlcyk7XG4gIH1cbn1cblxuZnVuY3Rpb24gZXh0cmFjdFRlbXBsYXRlRW50aXRpZXMocm9vdFNjb3BlOiBTY29wZSk6IE1hcDxUZW1wbGF0ZXxudWxsLCBTZXQ8UmVmZXJlbmNlfFZhcmlhYmxlPj4ge1xuICBjb25zdCBlbnRpdHlNYXAgPSBuZXcgTWFwPFRlbXBsYXRlfG51bGwsIE1hcDxzdHJpbmcsIFJlZmVyZW5jZXxWYXJpYWJsZT4+KCk7XG5cbiAgZnVuY3Rpb24gZXh0cmFjdFNjb3BlRW50aXRpZXMoc2NvcGU6IFNjb3BlKTogTWFwPHN0cmluZywgUmVmZXJlbmNlfFZhcmlhYmxlPiB7XG4gICAgaWYgKGVudGl0eU1hcC5oYXMoc2NvcGUudGVtcGxhdGUpKSB7XG4gICAgICByZXR1cm4gZW50aXR5TWFwLmdldChzY29wZS50ZW1wbGF0ZSkhO1xuICAgIH1cblxuICAgIGNvbnN0IGN1cnJlbnRFbnRpdGllcyA9IHNjb3BlLm5hbWVkRW50aXRpZXM7XG5cbiAgICBsZXQgdGVtcGxhdGVFbnRpdGllczogTWFwPHN0cmluZywgUmVmZXJlbmNlfFZhcmlhYmxlPjtcbiAgICBpZiAoc2NvcGUucGFyZW50U2NvcGUgIT09IG51bGwpIHtcbiAgICAgIHRlbXBsYXRlRW50aXRpZXMgPSBuZXcgTWFwKFsuLi5leHRyYWN0U2NvcGVFbnRpdGllcyhzY29wZS5wYXJlbnRTY29wZSksIC4uLmN1cnJlbnRFbnRpdGllc10pO1xuICAgIH0gZWxzZSB7XG4gICAgICB0ZW1wbGF0ZUVudGl0aWVzID0gbmV3IE1hcChjdXJyZW50RW50aXRpZXMpO1xuICAgIH1cblxuICAgIGVudGl0eU1hcC5zZXQoc2NvcGUudGVtcGxhdGUsIHRlbXBsYXRlRW50aXRpZXMpO1xuICAgIHJldHVybiB0ZW1wbGF0ZUVudGl0aWVzO1xuICB9XG5cbiAgY29uc3Qgc2NvcGVzVG9Qcm9jZXNzOiBTY29wZVtdID0gW3Jvb3RTY29wZV07XG4gIHdoaWxlIChzY29wZXNUb1Byb2Nlc3MubGVuZ3RoID4gMCkge1xuICAgIGNvbnN0IHNjb3BlID0gc2NvcGVzVG9Qcm9jZXNzLnBvcCgpITtcbiAgICBmb3IgKGNvbnN0IGNoaWxkU2NvcGUgb2Ygc2NvcGUuY2hpbGRTY29wZXMudmFsdWVzKCkpIHtcbiAgICAgIHNjb3Blc1RvUHJvY2Vzcy5wdXNoKGNoaWxkU2NvcGUpO1xuICAgIH1cbiAgICBleHRyYWN0U2NvcGVFbnRpdGllcyhzY29wZSk7XG4gIH1cblxuICBjb25zdCB0ZW1wbGF0ZUVudGl0aWVzID0gbmV3IE1hcDxUZW1wbGF0ZXxudWxsLCBTZXQ8UmVmZXJlbmNlfFZhcmlhYmxlPj4oKTtcbiAgZm9yIChjb25zdCBbdGVtcGxhdGUsIGVudGl0aWVzXSBvZiBlbnRpdHlNYXApIHtcbiAgICB0ZW1wbGF0ZUVudGl0aWVzLnNldCh0ZW1wbGF0ZSwgbmV3IFNldChlbnRpdGllcy52YWx1ZXMoKSkpO1xuICB9XG4gIHJldHVybiB0ZW1wbGF0ZUVudGl0aWVzO1xufVxuIl19