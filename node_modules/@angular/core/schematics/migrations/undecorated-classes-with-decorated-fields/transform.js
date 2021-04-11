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
        define("@angular/core/schematics/migrations/undecorated-classes-with-decorated-fields/transform", ["require", "exports", "@angular/compiler-cli/src/ngtsc/partial_evaluator", "@angular/compiler-cli/src/ngtsc/reflection", "typescript", "@angular/core/schematics/utils/import_manager", "@angular/core/schematics/utils/ng_decorators", "@angular/core/schematics/utils/typescript/find_base_classes", "@angular/core/schematics/utils/typescript/functions", "@angular/core/schematics/utils/typescript/property_name"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UndecoratedClassesWithDecoratedFieldsTransform = void 0;
    const partial_evaluator_1 = require("@angular/compiler-cli/src/ngtsc/partial_evaluator");
    const reflection_1 = require("@angular/compiler-cli/src/ngtsc/reflection");
    const ts = require("typescript");
    const import_manager_1 = require("@angular/core/schematics/utils/import_manager");
    const ng_decorators_1 = require("@angular/core/schematics/utils/ng_decorators");
    const find_base_classes_1 = require("@angular/core/schematics/utils/typescript/find_base_classes");
    const functions_1 = require("@angular/core/schematics/utils/typescript/functions");
    const property_name_1 = require("@angular/core/schematics/utils/typescript/property_name");
    /**
     * Set of known decorators that indicate that the current class needs a directive
     * definition. These decorators are always specific to directives.
     */
    const DIRECTIVE_FIELD_DECORATORS = new Set([
        'Input', 'Output', 'ViewChild', 'ViewChildren', 'ContentChild', 'ContentChildren', 'HostBinding',
        'HostListener'
    ]);
    /**
     * Set of known lifecycle hooks that indicate that the current class needs a directive
     * definition. These lifecycle hooks are always specific to directives.
     */
    const DIRECTIVE_LIFECYCLE_HOOKS = new Set([
        'ngOnChanges', 'ngOnInit', 'ngDoCheck', 'ngAfterViewInit', 'ngAfterViewChecked',
        'ngAfterContentInit', 'ngAfterContentChecked'
    ]);
    /**
     * Set of known lifecycle hooks that indicate that a given class uses Angular
     * features, but it's ambiguous whether it is a directive or service.
     */
    const AMBIGUOUS_LIFECYCLE_HOOKS = new Set(['ngOnDestroy']);
    /** Describes how a given class is used in the context of Angular. */
    var InferredKind;
    (function (InferredKind) {
        InferredKind[InferredKind["DIRECTIVE"] = 0] = "DIRECTIVE";
        InferredKind[InferredKind["AMBIGUOUS"] = 1] = "AMBIGUOUS";
        InferredKind[InferredKind["UNKNOWN"] = 2] = "UNKNOWN";
    })(InferredKind || (InferredKind = {}));
    /** Describes possible types of Angular declarations. */
    var DeclarationType;
    (function (DeclarationType) {
        DeclarationType[DeclarationType["DIRECTIVE"] = 0] = "DIRECTIVE";
        DeclarationType[DeclarationType["COMPONENT"] = 1] = "COMPONENT";
        DeclarationType[DeclarationType["ABSTRACT_DIRECTIVE"] = 2] = "ABSTRACT_DIRECTIVE";
        DeclarationType[DeclarationType["PIPE"] = 3] = "PIPE";
        DeclarationType[DeclarationType["INJECTABLE"] = 4] = "INJECTABLE";
    })(DeclarationType || (DeclarationType = {}));
    /** TODO message that is added to ambiguous classes using Angular features. */
    const AMBIGUOUS_CLASS_TODO = 'Add Angular decorator.';
    class UndecoratedClassesWithDecoratedFieldsTransform {
        constructor(typeChecker, getUpdateRecorder) {
            this.typeChecker = typeChecker;
            this.getUpdateRecorder = getUpdateRecorder;
            this.printer = ts.createPrinter();
            this.importManager = new import_manager_1.ImportManager(this.getUpdateRecorder, this.printer);
            this.reflectionHost = new reflection_1.TypeScriptReflectionHost(this.typeChecker);
            this.partialEvaluator = new partial_evaluator_1.PartialEvaluator(this.reflectionHost, this.typeChecker, null);
        }
        /**
         * Migrates the specified source files. The transform adds the abstract `@Directive`
         * decorator to undecorated classes that use Angular features. Class members which
         * are decorated with any Angular decorator, or class members for lifecycle hooks are
         * indicating that a given class uses Angular features. https://hackmd.io/vuQfavzfRG6KUCtU7oK_EA
         */
        migrate(sourceFiles) {
            const { detectedAbstractDirectives, ambiguousClasses } = this._findUndecoratedAbstractDirectives(sourceFiles);
            detectedAbstractDirectives.forEach(node => {
                const sourceFile = node.getSourceFile();
                const recorder = this.getUpdateRecorder(sourceFile);
                const directiveExpr = this.importManager.addImportToSourceFile(sourceFile, 'Directive', '@angular/core');
                const decoratorExpr = ts.createDecorator(ts.createCall(directiveExpr, undefined, undefined));
                recorder.addClassDecorator(node, this.printer.printNode(ts.EmitHint.Unspecified, decoratorExpr, sourceFile));
            });
            // Ambiguous classes clearly use Angular features, but the migration is unable to
            // determine whether the class is used as directive, service or pipe. The migration
            // could potentially determine the type by checking NgModule definitions or inheritance
            // of other known declarations, but this is out of scope and a TODO/failure is sufficient.
            return Array.from(ambiguousClasses).reduce((failures, node) => {
                // If the class has been reported as ambiguous before, skip adding a TODO and
                // printing an error. A class could be visited multiple times when it's part
                // of multiple build targets in the CLI project.
                if (this._hasBeenReportedAsAmbiguous(node)) {
                    return failures;
                }
                const sourceFile = node.getSourceFile();
                const recorder = this.getUpdateRecorder(sourceFile);
                // Add a TODO to the class that uses Angular features but is not decorated.
                recorder.addClassTodo(node, AMBIGUOUS_CLASS_TODO);
                // Add an error for the class that will be printed in the `ng update` output.
                return failures.concat({
                    node,
                    message: 'Class uses Angular features but cannot be migrated automatically. Please ' +
                        'add an appropriate Angular decorator.'
                });
            }, []);
        }
        /** Records all changes that were made in the import manager. */
        recordChanges() {
            this.importManager.recordChanges();
        }
        /**
         * Finds undecorated abstract directives in the specified source files. Also returns
         * a set of undecorated classes which could not be detected as guaranteed abstract
         * directives. Those are ambiguous and could be either Directive, Pipe or service.
         */
        _findUndecoratedAbstractDirectives(sourceFiles) {
            const ambiguousClasses = new Set();
            const declarations = new WeakMap();
            const detectedAbstractDirectives = new Set();
            const undecoratedClasses = new Set();
            const visitNode = (node) => {
                node.forEachChild(visitNode);
                if (!ts.isClassDeclaration(node)) {
                    return;
                }
                const { inferredKind, decoratedType } = this._analyzeClassDeclaration(node);
                if (decoratedType !== null) {
                    declarations.set(node, decoratedType);
                    return;
                }
                if (inferredKind === InferredKind.DIRECTIVE) {
                    detectedAbstractDirectives.add(node);
                }
                else if (inferredKind === InferredKind.AMBIGUOUS) {
                    ambiguousClasses.add(node);
                }
                else {
                    undecoratedClasses.add(node);
                }
            };
            sourceFiles.forEach(sourceFile => sourceFile.forEachChild(visitNode));
            /**
             * Checks the inheritance of the given set of classes. It removes classes from the
             * detected abstract directives set when they inherit from a non-abstract Angular
             * declaration. e.g. an abstract directive can never extend from a component.
             *
             * If a class inherits from an abstract directive though, we will migrate them too
             * as derived classes also need to be decorated. This has been done for a simpler mental
             * model and reduced complexity in the Angular compiler. See migration plan document.
             */
            const checkInheritanceOfClasses = (classes) => {
                classes.forEach(node => {
                    for (const { node: baseClass } of find_base_classes_1.findBaseClassDeclarations(node, this.typeChecker)) {
                        if (!declarations.has(baseClass)) {
                            continue;
                        }
                        // If the undecorated class inherits from an abstract directive, always migrate it.
                        // Derived undecorated classes of abstract directives are always also considered
                        // abstract directives and need to be decorated too. This is necessary as otherwise
                        // the inheritance chain cannot be resolved by the Angular compiler. e.g. when it
                        // flattens directive metadata for type checking. In the other case, we never want
                        // to migrate a class if it extends from a non-abstract Angular declaration. That
                        // is an unsupported pattern as of v9 and was previously handled with the
                        // `undecorated-classes-with-di` migration (which copied the inherited decorator).
                        if (declarations.get(baseClass) === DeclarationType.ABSTRACT_DIRECTIVE) {
                            detectedAbstractDirectives.add(node);
                        }
                        else {
                            detectedAbstractDirectives.delete(node);
                        }
                        ambiguousClasses.delete(node);
                        break;
                    }
                });
            };
            // Check inheritance of any detected abstract directive. We want to remove
            // classes that are not eligible abstract directives due to inheritance. i.e.
            // if a class extends from a component, it cannot be a derived abstract directive.
            checkInheritanceOfClasses(detectedAbstractDirectives);
            // Update the class declarations to reflect the detected abstract directives. This is
            // then used later when we check for undecorated classes that inherit from an abstract
            // directive and need to be decorated.
            detectedAbstractDirectives.forEach(n => declarations.set(n, DeclarationType.ABSTRACT_DIRECTIVE));
            // Check ambiguous and undecorated classes if they inherit from an abstract directive.
            // If they do, we want to migrate them too. See function definition for more details.
            checkInheritanceOfClasses(ambiguousClasses);
            checkInheritanceOfClasses(undecoratedClasses);
            return { detectedAbstractDirectives, ambiguousClasses };
        }
        /**
         * Analyzes the given class declaration by determining whether the class
         * is a directive, is an abstract directive, or uses Angular features.
         */
        _analyzeClassDeclaration(node) {
            const ngDecorators = node.decorators && ng_decorators_1.getAngularDecorators(this.typeChecker, node.decorators);
            const inferredKind = this._determineClassKind(node);
            if (ngDecorators === undefined || ngDecorators.length === 0) {
                return { decoratedType: null, inferredKind };
            }
            const directiveDecorator = ngDecorators.find(({ name }) => name === 'Directive');
            const componentDecorator = ngDecorators.find(({ name }) => name === 'Component');
            const pipeDecorator = ngDecorators.find(({ name }) => name === 'Pipe');
            const injectableDecorator = ngDecorators.find(({ name }) => name === 'Injectable');
            const isAbstractDirective = directiveDecorator !== undefined && this._isAbstractDirective(directiveDecorator);
            let decoratedType = null;
            if (isAbstractDirective) {
                decoratedType = DeclarationType.ABSTRACT_DIRECTIVE;
            }
            else if (componentDecorator !== undefined) {
                decoratedType = DeclarationType.COMPONENT;
            }
            else if (directiveDecorator !== undefined) {
                decoratedType = DeclarationType.DIRECTIVE;
            }
            else if (pipeDecorator !== undefined) {
                decoratedType = DeclarationType.PIPE;
            }
            else if (injectableDecorator !== undefined) {
                decoratedType = DeclarationType.INJECTABLE;
            }
            return { decoratedType, inferredKind };
        }
        /**
         * Checks whether the given decorator resolves to an abstract directive. An directive is
         * considered "abstract" if there is no selector specified.
         */
        _isAbstractDirective({ node }) {
            const metadataArgs = node.expression.arguments;
            if (metadataArgs.length === 0) {
                return true;
            }
            const metadataExpr = functions_1.unwrapExpression(metadataArgs[0]);
            if (!ts.isObjectLiteralExpression(metadataExpr)) {
                return false;
            }
            const metadata = reflection_1.reflectObjectLiteral(metadataExpr);
            if (!metadata.has('selector')) {
                return false;
            }
            const selector = this.partialEvaluator.evaluate(metadata.get('selector'));
            return selector == null;
        }
        /**
         * Determines the kind of a given class in terms of Angular. The method checks
         * whether the given class has members that indicate the use of Angular features.
         * e.g. lifecycle hooks or decorated members like `@Input` or `@Output` are
         * considered Angular features..
         */
        _determineClassKind(node) {
            let usage = InferredKind.UNKNOWN;
            for (const member of node.members) {
                const propertyName = member.name !== undefined ? property_name_1.getPropertyNameText(member.name) : null;
                // If the class declares any of the known directive lifecycle hooks, we can
                // immediately exit the loop as the class is guaranteed to be a directive.
                if (propertyName !== null && DIRECTIVE_LIFECYCLE_HOOKS.has(propertyName)) {
                    return InferredKind.DIRECTIVE;
                }
                const ngDecorators = member.decorators !== undefined ?
                    ng_decorators_1.getAngularDecorators(this.typeChecker, member.decorators) :
                    [];
                for (const { name } of ngDecorators) {
                    if (DIRECTIVE_FIELD_DECORATORS.has(name)) {
                        return InferredKind.DIRECTIVE;
                    }
                }
                // If the class declares any of the lifecycle hooks that do not guarantee that
                // the given class is a directive, update the kind and continue looking for other
                // members that would unveil a more specific kind (i.e. being a directive).
                if (propertyName !== null && AMBIGUOUS_LIFECYCLE_HOOKS.has(propertyName)) {
                    usage = InferredKind.AMBIGUOUS;
                }
            }
            return usage;
        }
        /**
         * Checks whether a given class has been reported as ambiguous in previous
         * migration run. e.g. when build targets are migrated first, and then test
         * targets that have an overlap with build source files, the same class
         * could be detected as ambiguous.
         */
        _hasBeenReportedAsAmbiguous(node) {
            const sourceFile = node.getSourceFile();
            const leadingComments = ts.getLeadingCommentRanges(sourceFile.text, node.pos);
            if (leadingComments === undefined) {
                return false;
            }
            return leadingComments.some(({ kind, pos, end }) => kind === ts.SyntaxKind.SingleLineCommentTrivia &&
                sourceFile.text.substring(pos, end).includes(`TODO: ${AMBIGUOUS_CLASS_TODO}`));
        }
    }
    exports.UndecoratedClassesWithDecoratedFieldsTransform = UndecoratedClassesWithDecoratedFieldsTransform;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJhbnNmb3JtLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRlY29yYXRlZC1maWVsZHMvdHJhbnNmb3JtLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILHlGQUFtRjtJQUNuRiwyRUFBMEc7SUFDMUcsaUNBQWlDO0lBRWpDLGtGQUF5RDtJQUN6RCxnRkFBNEU7SUFDNUUsbUdBQW1GO0lBQ25GLG1GQUFrRTtJQUNsRSwyRkFBeUU7SUFJekU7OztPQUdHO0lBQ0gsTUFBTSwwQkFBMEIsR0FBRyxJQUFJLEdBQUcsQ0FBQztRQUN6QyxPQUFPLEVBQUUsUUFBUSxFQUFFLFdBQVcsRUFBRSxjQUFjLEVBQUUsY0FBYyxFQUFFLGlCQUFpQixFQUFFLGFBQWE7UUFDaEcsY0FBYztLQUNmLENBQUMsQ0FBQztJQUVIOzs7T0FHRztJQUNILE1BQU0seUJBQXlCLEdBQUcsSUFBSSxHQUFHLENBQUM7UUFDeEMsYUFBYSxFQUFFLFVBQVUsRUFBRSxXQUFXLEVBQUUsaUJBQWlCLEVBQUUsb0JBQW9CO1FBQy9FLG9CQUFvQixFQUFFLHVCQUF1QjtLQUM5QyxDQUFDLENBQUM7SUFFSDs7O09BR0c7SUFDSCxNQUFNLHlCQUF5QixHQUFHLElBQUksR0FBRyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQztJQUUzRCxxRUFBcUU7SUFDckUsSUFBSyxZQUlKO0lBSkQsV0FBSyxZQUFZO1FBQ2YseURBQVMsQ0FBQTtRQUNULHlEQUFTLENBQUE7UUFDVCxxREFBTyxDQUFBO0lBQ1QsQ0FBQyxFQUpJLFlBQVksS0FBWixZQUFZLFFBSWhCO0lBRUQsd0RBQXdEO0lBQ3hELElBQUssZUFNSjtJQU5ELFdBQUssZUFBZTtRQUNsQiwrREFBUyxDQUFBO1FBQ1QsK0RBQVMs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b0JBQ3hFLEtBQUssR0FBRyxZQUFZLENBQUMsU0FBUyxDQUFDO2lCQUNoQzthQUNGO1lBRUQsT0FBTyxLQUFLLENBQUM7UUFDZixDQUFDO1FBRUQ7Ozs7O1dBS0c7UUFDSywyQkFBMkIsQ0FBQyxJQUF5QjtZQUMzRCxNQUFNLFVBQVUsR0FBRyxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUM7WUFDeEMsTUFBTSxlQUFlLEdBQUcsRUFBRSxDQUFDLHVCQUF1QixDQUFDLFVBQVUsQ0FBQyxJQUFJLEVBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO1lBQzlFLElBQUksZUFBZSxLQUFLLFNBQVMsRUFBRTtnQkFDakMsT0FBTyxLQUFLLENBQUM7YUFDZDtZQUNELE9BQU8sZUFBZSxDQUFDLElBQUksQ0FDdkIsQ0FBQyxFQUFDLElBQUksRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFDLEVBQUUsRUFBRSxDQUFDLElBQUksS0FBSyxFQUFFLENBQUMsVUFBVSxDQUFDLHVCQUF1QjtnQkFDaEUsVUFBVSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxTQUFTLG9CQUFvQixFQUFFLENBQUMsQ0FBQyxDQUFDO1FBQ3pGLENBQUM7S0FDRjtJQTlQRCx3R0E4UEMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtQYXJ0aWFsRXZhbHVhdG9yfSBmcm9tICdAYW5ndWxhci9jb21waWxlci1jbGkvc3JjL25ndHNjL3BhcnRpYWxfZXZhbHVhdG9yJztcbmltcG9ydCB7cmVmbGVjdE9iamVjdExpdGVyYWwsIFR5cGVTY3JpcHRSZWZsZWN0aW9uSG9zdH0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXItY2xpL3NyYy9uZ3RzYy9yZWZsZWN0aW9uJztcbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge0ltcG9ydE1hbmFnZXJ9IGZyb20gJy4uLy4uL3V0aWxzL2ltcG9ydF9tYW5hZ2VyJztcbmltcG9ydCB7Z2V0QW5ndWxhckRlY29yYXRvcnMsIE5nRGVjb3JhdG9yfSBmcm9tICcuLi8uLi91dGlscy9uZ19kZWNvcmF0b3JzJztcbmltcG9ydCB7ZmluZEJhc2VDbGFzc0RlY2xhcmF0aW9uc30gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9maW5kX2Jhc2VfY2xhc3Nlcyc7XG5pbXBvcnQge3Vud3JhcEV4cHJlc3Npb259IGZyb20gJy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvZnVuY3Rpb25zJztcbmltcG9ydCB7Z2V0UHJvcGVydHlOYW1lVGV4dH0gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9wcm9wZXJ0eV9uYW1lJztcblxuaW1wb3J0IHtVcGRhdGVSZWNvcmRlcn0gZnJvbSAnLi91cGRhdGVfcmVjb3JkZXInO1xuXG4vKipcbiAqIFNldCBvZiBrbm93biBkZWNvcmF0b3JzIHRoYXQgaW5kaWNhdGUgdGhhdCB0aGUgY3VycmVudCBjbGFzcyBuZWVkcyBhIGRpcmVjdGl2ZVxuICogZGVmaW5pdGlvbi4gVGhlc2UgZGVjb3JhdG9ycyBhcmUgYWx3YXlzIHNwZWNpZmljIHRvIGRpcmVjdGl2ZXMuXG4gKi9cbmNvbnN0IERJUkVDVElWRV9GSUVMRF9ERUNPUkFUT1JTID0gbmV3IFNldChbXG4gICdJbnB1dCcsICdPdXRwdXQnLCAnVmlld0NoaWxkJywgJ1ZpZXdDaGlsZHJlbicsICdDb250ZW50Q2hpbGQnLCAnQ29udGVudENoaWxkcmVuJywgJ0hvc3RCaW5kaW5nJyxcbiAgJ0hvc3RMaXN0ZW5lcidcbl0pO1xuXG4vKipcbiAqIFNldCBvZiBrbm93biBsaWZlY3ljbGUgaG9va3MgdGhhdCBpbmRpY2F0ZSB0aGF0IHRoZSBjdXJyZW50IGNsYXNzIG5lZWRzIGEgZGlyZWN0aXZlXG4gKiBkZWZpbml0aW9uLiBUaGVzZSBsaWZlY3ljbGUgaG9va3MgYXJlIGFsd2F5cyBzcGVjaWZpYyB0byBkaXJlY3RpdmVzLlxuICovXG5jb25zdCBESVJFQ1RJVkVfTElGRUNZQ0xFX0hPT0tTID0gbmV3IFNldChbXG4gICduZ09uQ2hhbmdlcycsICduZ09uSW5pdCcsICduZ0RvQ2hlY2snLCAnbmdBZnRlclZpZXdJbml0JywgJ25nQWZ0ZXJWaWV3Q2hlY2tlZCcsXG4gICduZ0FmdGVyQ29udGVudEluaXQnLCAnbmdBZnRlckNvbnRlbnRDaGVja2VkJ1xuXSk7XG5cbi8qKlxuICogU2V0IG9mIGtub3duIGxpZmVjeWNsZSBob29rcyB0aGF0IGluZGljYXRlIHRoYXQgYSBnaXZlbiBjbGFzcyB1c2VzIEFuZ3VsYXJcbiAqIGZlYXR1cmVzLCBidXQgaXQncyBhbWJpZ3VvdXMgd2hldGhlciBpdCBpcyBhIGRpcmVjdGl2ZSBvciBzZXJ2aWNlLlxuICovXG5jb25zdCBBTUJJR1VPVVNfTElGRUNZQ0xFX0hPT0tTID0gbmV3IFNldChbJ25nT25EZXN0cm95J10pO1xuXG4vKiogRGVzY3JpYmVzIGhvdyBhIGdpdmVuIGNsYXNzIGlzIHVzZWQgaW4gdGhlIGNvbnRleHQgb2YgQW5ndWxhci4gKi9cbmVudW0gSW5mZXJyZWRLaW5kIHtcbiAgRElSRUNUSVZFLFxuICBBTUJJR1VPVVMsXG4gIFVOS05PV04sXG59XG5cbi8qKiBEZXNjcmliZXMgcG9zc2libGUgdHlwZXMgb2YgQW5ndWxhciBkZWNsYXJhdGlvbnMuICovXG5lbnVtIERlY2xhcmF0aW9uVHlwZSB7XG4gIERJUkVDVElWRSxcbiAgQ09NUE9ORU5ULFxuICBBQlNUUkFDVF9ESVJFQ1RJVkUsXG4gIFBJUEUsXG4gIElOSkVDVEFCTEUsXG59XG5cbi8qKiBBbmFseXplZCBjbGFzcyBkZWNsYXJhdGlvbi4gKi9cbmludGVyZmFjZSBBbmFseXplZENsYXNzIHtcbiAgLyoqIFR5cGUgb2YgZGVjbGFyYXRpb24gdGhhdCBpcyBkZXRlcm1pbmVkIHRocm91Z2ggYW4gYXBwbGllZCBkZWNvcmF0b3IuICovXG4gIGRlY29yYXRlZFR5cGU6IERlY2xhcmF0aW9uVHlwZXxudWxsO1xuICAvKiogSW5mZXJyZWQgY2xhc3Mga2luZCBpbiB0ZXJtcyBvZiBBbmd1bGFyLiAqL1xuICBpbmZlcnJlZEtpbmQ6IEluZmVycmVkS2luZDtcbn1cblxuaW50ZXJmYWNlIEFuYWx5c2lzRmFpbHVyZSB7XG4gIG5vZGU6IHRzLk5vZGU7XG4gIG1lc3NhZ2U6IHN0cmluZztcbn1cblxuLyoqIFRPRE8gbWVzc2FnZSB0aGF0IGlzIGFkZGVkIHRvIGFtYmlndW91cyBjbGFzc2VzIHVzaW5nIEFuZ3VsYXIgZmVhdHVyZXMuICovXG5jb25zdCBBTUJJR1VPVVNfQ0xBU1NfVE9ETyA9ICdBZGQgQW5ndWxhciBkZWNvcmF0b3IuJztcblxuZXhwb3J0IGNsYXNzIFVuZGVjb3JhdGVkQ2xhc3Nlc1dpdGhEZWNvcmF0ZWRGaWVsZHNUcmFuc2Zvcm0ge1xuICBwcml2YXRlIHByaW50ZXIgPSB0cy5jcmVhdGVQcmludGVyKCk7XG4gIHByaXZhdGUgaW1wb3J0TWFuYWdlciA9IG5ldyBJbXBvcnRNYW5hZ2VyKHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIsIHRoaXMucHJpbnRlcik7XG4gIHByaXZhdGUgcmVmbGVjdGlvbkhvc3QgPSBuZXcgVHlwZVNjcmlwdFJlZmxlY3Rpb25Ib3N0KHRoaXMudHlwZUNoZWNrZXIpO1xuICBwcml2YXRlIHBhcnRpYWxFdmFsdWF0b3IgPSBuZXcgUGFydGlhbEV2YWx1YXRvcih0aGlzLnJlZmxlY3Rpb25Ib3N0LCB0aGlzLnR5cGVDaGVja2VyLCBudWxsKTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLFxuICAgICAgcHJpdmF0ZSBnZXRVcGRhdGVSZWNvcmRlcjogKHNmOiB0cy5Tb3VyY2VGaWxlKSA9PiBVcGRhdGVSZWNvcmRlcikge31cblxuICAvKipcbiAgICogTWlncmF0ZXMgdGhlIHNwZWNpZmllZCBzb3VyY2UgZmlsZXMuIFRoZSB0cmFuc2Zvcm0gYWRkcyB0aGUgYWJzdHJhY3QgYEBEaXJlY3RpdmVgXG4gICAqIGRlY29yYXRvciB0byB1bmRlY29yYXRlZCBjbGFzc2VzIHRoYXQgdXNlIEFuZ3VsYXIgZmVhdHVyZXMuIENsYXNzIG1lbWJlcnMgd2hpY2hcbiAgICogYXJlIGRlY29yYXRlZCB3aXRoIGFueSBBbmd1bGFyIGRlY29yYXRvciwgb3IgY2xhc3MgbWVtYmVycyBmb3IgbGlmZWN5Y2xlIGhvb2tzIGFyZVxuICAgKiBpbmRpY2F0aW5nIHRoYXQgYSBnaXZlbiBjbGFzcyB1c2VzIEFuZ3VsYXIgZmVhdHVyZXMuIGh0dHBzOi8vaGFja21kLmlvL3Z1UWZhdnpmUkc2S1VDdFU3b0tfRUFcbiAgICovXG4gIG1pZ3JhdGUoc291cmNlRmlsZXM6IHRzLlNvdXJjZUZpbGVbXSk6IEFuYWx5c2lzRmFpbHVyZVtdIHtcbiAgICBjb25zdCB7ZGV0ZWN0ZWRBYnN0cmFjdERpcmVjdGl2ZXMsIGFtYmlndW91c0NsYXNzZXN9ID1cbiAgICAgICAgdGhpcy5fZmluZFVuZGVjb3JhdGVkQWJzdHJhY3REaXJlY3RpdmVzKHNvdXJjZUZpbGVzKTtcblxuICAgIGRldGVjdGVkQWJzdHJhY3REaXJlY3RpdmVzLmZvckVhY2gobm9kZSA9PiB7XG4gICAgICBjb25zdCBzb3VyY2VGaWxlID0gbm9kZS5nZXRTb3VyY2VGaWxlKCk7XG4gICAgICBjb25zdCByZWNvcmRlciA9IHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIoc291cmNlRmlsZSk7XG4gICAgICBjb25zdCBkaXJlY3RpdmVFeHByID1cbiAgICAgICAgICB0aGlzLmltcG9ydE1hbmFnZXIuYWRkSW1wb3J0VG9Tb3VyY2VGaWxlKHNvdXJjZUZpbGUsICdEaXJlY3RpdmUnLCAnQGFuZ3VsYXIvY29yZScpO1xuICAgICAgY29uc3QgZGVjb3JhdG9yRXhwciA9IHRzLmNyZWF0ZURlY29yYXRvcih0cy5jcmVhdGVDYWxsKGRpcmVjdGl2ZUV4cHIsIHVuZGVmaW5lZCwgdW5kZWZpbmVkKSk7XG4gICAgICByZWNvcmRlci5hZGRDbGFzc0RlY29yYXRvcihcbiAgICAgICAgICBub2RlLCB0aGlzLnByaW50ZXIucHJpbnROb2RlKHRzLkVtaXRIaW50LlVuc3BlY2lmaWVkLCBkZWNvcmF0b3JFeHByLCBzb3VyY2VGaWxlKSk7XG4gICAgfSk7XG5cbiAgICAvLyBBbWJpZ3VvdXMgY2xhc3NlcyBjbGVhcmx5IHVzZSBBbmd1bGFyIGZlYXR1cmVzLCBidXQgdGhlIG1pZ3JhdGlvbiBpcyB1bmFibGUgdG9cbiAgICAvLyBkZXRlcm1pbmUgd2hldGhlciB0aGUgY2xhc3MgaXMgdXNlZCBhcyBkaXJlY3RpdmUsIHNlcnZpY2Ugb3IgcGlwZS4gVGhlIG1pZ3JhdGlvblxuICAgIC8vIGNvdWxkIHBvdGVudGlhbGx5IGRldGVybWluZSB0aGUgdHlwZSBieSBjaGVja2luZyBOZ01vZHVsZSBkZWZpbml0aW9ucyBvciBpbmhlcml0YW5jZVxuICAgIC8vIG9mIG90aGVyIGtub3duIGRlY2xhcmF0aW9ucywgYnV0IHRoaXMgaXMgb3V0IG9mIHNjb3BlIGFuZCBhIFRPRE8vZmFpbHVyZSBpcyBzdWZmaWNpZW50LlxuICAgIHJldHVybiBBcnJheS5mcm9tKGFtYmlndW91c0NsYXNzZXMpLnJlZHVjZSgoZmFpbHVyZXMsIG5vZGUpID0+IHtcbiAgICAgIC8vIElmIHRoZSBjbGFzcyBoYXMgYmVlbiByZXBvcnRlZCBhcyBhbWJpZ3VvdXMgYmVmb3JlLCBza2lwIGFkZGluZyBhIFRPRE8gYW5kXG4gICAgICAvLyBwcmludGluZyBhbiBlcnJvci4gQSBjbGFzcyBjb3VsZCBiZSB2aXNpdGVkIG11bHRpcGxlIHRpbWVzIHdoZW4gaXQncyBwYXJ0XG4gICAgICAvLyBvZiBtdWx0aXBsZSBidWlsZCB0YXJnZXRzIGluIHRoZSBDTEkgcHJvamVjdC5cbiAgICAgIGlmICh0aGlzLl9oYXNCZWVuUmVwb3J0ZWRBc0FtYmlndW91cyhub2RlKSkge1xuICAgICAgICByZXR1cm4gZmFpbHVyZXM7XG4gICAgICB9XG5cbiAgICAgIGNvbnN0IHNvdXJjZUZpbGUgPSBub2RlLmdldFNvdXJjZUZpbGUoKTtcbiAgICAgIGNvbnN0IHJlY29yZGVyID0gdGhpcy5nZXRVcGRhdGVSZWNvcmRlcihzb3VyY2VGaWxlKTtcblxuICAgICAgLy8gQWRkIGEgVE9ETyB0byB0aGUgY2xhc3MgdGhhdCB1c2VzIEFuZ3VsYXIgZmVhdHVyZXMgYnV0IGlzIG5vdCBkZWNvcmF0ZWQuXG4gICAgICByZWNvcmRlci5hZGRDbGFzc1RvZG8obm9kZSwgQU1CSUdVT1VTX0NMQVNTX1RPRE8pO1xuXG4gICAgICAvLyBBZGQgYW4gZXJyb3IgZm9yIHRoZSBjbGFzcyB0aGF0IHdpbGwgYmUgcHJpbnRlZCBpbiB0aGUgYG5nIHVwZGF0ZWAgb3V0cHV0LlxuICAgICAgcmV0dXJuIGZhaWx1cmVzLmNvbmNhdCh7XG4gICAgICAgIG5vZGUsXG4gICAgICAgIG1lc3NhZ2U6ICdDbGFzcyB1c2VzIEFuZ3VsYXIgZmVhdHVyZXMgYnV0IGNhbm5vdCBiZSBtaWdyYXRlZCBhdXRvbWF0aWNhbGx5LiBQbGVhc2UgJyArXG4gICAgICAgICAgICAnYWRkIGFuIGFwcHJvcHJpYXRlIEFuZ3VsYXIgZGVjb3JhdG9yLidcbiAgICAgIH0pO1xuICAgIH0sIFtdIGFzIEFuYWx5c2lzRmFpbHVyZVtdKTtcbiAgfVxuXG4gIC8qKiBSZWNvcmRzIGFsbCBjaGFuZ2VzIHRoYXQgd2VyZSBtYWRlIGluIHRoZSBpbXBvcnQgbWFuYWdlci4gKi9cbiAgcmVjb3JkQ2hhbmdlcygpIHtcbiAgICB0aGlzLmltcG9ydE1hbmFnZXIucmVjb3JkQ2hhbmdlcygpO1xuICB9XG5cbiAgLyoqXG4gICAqIEZpbmRzIHVuZGVjb3JhdGVkIGFic3RyYWN0IGRpcmVjdGl2ZXMgaW4gdGhlIHNwZWNpZmllZCBzb3VyY2UgZmlsZXMuIEFsc28gcmV0dXJuc1xuICAgKiBhIHNldCBvZiB1bmRlY29yYXRlZCBjbGFzc2VzIHdoaWNoIGNvdWxkIG5vdCBiZSBkZXRlY3RlZCBhcyBndWFyYW50ZWVkIGFic3RyYWN0XG4gICAqIGRpcmVjdGl2ZXMuIFRob3NlIGFyZSBhbWJpZ3VvdXMgYW5kIGNvdWxkIGJlIGVpdGhlciBEaXJlY3RpdmUsIFBpcGUgb3Igc2VydmljZS5cbiAgICovXG4gIHByaXZhdGUgX2ZpbmRVbmRlY29yYXRlZEFic3RyYWN0RGlyZWN0aXZlcyhzb3VyY2VGaWxlczogdHMuU291cmNlRmlsZVtdKSB7XG4gICAgY29uc3QgYW1iaWd1b3VzQ2xhc3NlcyA9IG5ldyBTZXQ8dHMuQ2xhc3NEZWNsYXJhdGlvbj4oKTtcbiAgICBjb25zdCBkZWNsYXJhdGlvbnMgPSBuZXcgV2Vha01hcDx0cy5DbGFzc0RlY2xhcmF0aW9uLCBEZWNsYXJhdGlvblR5cGU+KCk7XG4gICAgY29uc3QgZGV0ZWN0ZWRBYnN0cmFjdERpcmVjdGl2ZXMgPSBuZXcgU2V0PHRzLkNsYXNzRGVjbGFyYXRpb24+KCk7XG4gICAgY29uc3QgdW5kZWNvcmF0ZWRDbGFzc2VzID0gbmV3IFNldDx0cy5DbGFzc0RlY2xhcmF0aW9uPigpO1xuXG4gICAgY29uc3QgdmlzaXROb2RlID0gKG5vZGU6IHRzLk5vZGUpID0+IHtcbiAgICAgIG5vZGUuZm9yRWFjaENoaWxkKHZpc2l0Tm9kZSk7XG4gICAgICBpZiAoIXRzLmlzQ2xhc3NEZWNsYXJhdGlvbihub2RlKSkge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG4gICAgICBjb25zdCB7aW5mZXJyZWRLaW5kLCBkZWNvcmF0ZWRUeXBlfSA9IHRoaXMuX2FuYWx5emVDbGFzc0RlY2xhcmF0aW9uKG5vZGUpO1xuXG4gICAgICBpZiAoZGVjb3JhdGVkVHlwZSAhPT0gbnVsbCkge1xuICAgICAgICBkZWNsYXJhdGlvbnMuc2V0KG5vZGUsIGRlY29yYXRlZFR5cGUpO1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG5cbiAgICAgIGlmIChpbmZlcnJlZEtpbmQgPT09IEluZmVycmVkS2luZC5ESVJFQ1RJVkUpIHtcbiAgICAgICAgZGV0ZWN0ZWRBYnN0cmFjdERpcmVjdGl2ZXMuYWRkKG5vZGUpO1xuICAgICAgfSBlbHNlIGlmIChpbmZlcnJlZEtpbmQgPT09IEluZmVycmVkS2luZC5BTUJJR1VPVVMpIHtcbiAgICAgICAgYW1iaWd1b3VzQ2xhc3Nlcy5hZGQobm9kZSk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICB1bmRlY29yYXRlZENsYXNzZXMuYWRkKG5vZGUpO1xuICAgICAgfVxuICAgIH07XG5cbiAgICBzb3VyY2VGaWxlcy5mb3JFYWNoKHNvdXJjZUZpbGUgPT4gc291cmNlRmlsZS5mb3JFYWNoQ2hpbGQodmlzaXROb2RlKSk7XG5cbiAgICAvKipcbiAgICAgKiBDaGVja3MgdGhlIGluaGVyaXRhbmNlIG9mIHRoZSBnaXZlbiBzZXQgb2YgY2xhc3Nlcy4gSXQgcmVtb3ZlcyBjbGFzc2VzIGZyb20gdGhlXG4gICAgICogZGV0ZWN0ZWQgYWJzdHJhY3QgZGlyZWN0aXZlcyBzZXQgd2hlbiB0aGV5IGluaGVyaXQgZnJvbSBhIG5vbi1hYnN0cmFjdCBBbmd1bGFyXG4gICAgICogZGVjbGFyYXRpb24uIGUuZy4gYW4gYWJzdHJhY3QgZGlyZWN0aXZlIGNhbiBuZXZlciBleHRlbmQgZnJvbSBhIGNvbXBvbmVudC5cbiAgICAgKlxuICAgICAqIElmIGEgY2xhc3MgaW5oZXJpdHMgZnJvbSBhbiBhYnN0cmFjdCBkaXJlY3RpdmUgdGhvdWdoLCB3ZSB3aWxsIG1pZ3JhdGUgdGhlbSB0b29cbiAgICAgKiBhcyBkZXJpdmVkIGNsYXNzZXMgYWxzbyBuZWVkIHRvIGJlIGRlY29yYXRlZC4gVGhpcyBoYXMgYmVlbiBkb25lIGZvciBhIHNpbXBsZXIgbWVudGFsXG4gICAgICogbW9kZWwgYW5kIHJlZHVjZWQgY29tcGxleGl0eSBpbiB0aGUgQW5ndWxhciBjb21waWxlci4gU2VlIG1pZ3JhdGlvbiBwbGFuIGRvY3VtZW50LlxuICAgICAqL1xuICAgIGNvbnN0IGNoZWNrSW5oZXJpdGFuY2VPZkNsYXNzZXMgPSAoY2xhc3NlczogU2V0PHRzLkNsYXNzRGVjbGFyYXRpb24+KSA9PiB7XG4gICAgICBjbGFzc2VzLmZvckVhY2gobm9kZSA9PiB7XG4gICAgICAgIGZvciAoY29uc3Qge25vZGU6IGJhc2VDbGFzc30gb2YgZmluZEJhc2VDbGFzc0RlY2xhcmF0aW9ucyhub2RlLCB0aGlzLnR5cGVDaGVja2VyKSkge1xuICAgICAgICAgIGlmICghZGVjbGFyYXRpb25zLmhhcyhiYXNlQ2xhc3MpKSB7XG4gICAgICAgICAgICBjb250aW51ZTtcbiAgICAgICAgICB9XG4gICAgICAgICAgLy8gSWYgdGhlIHVuZGVjb3JhdGVkIGNsYXNzIGluaGVyaXRzIGZyb20gYW4gYWJzdHJhY3QgZGlyZWN0aXZlLCBhbHdheXMgbWlncmF0ZSBpdC5cbiAgICAgICAgICAvLyBEZXJpdmVkIHVuZGVjb3JhdGVkIGNsYXNzZXMgb2YgYWJzdHJhY3QgZGlyZWN0aXZlcyBhcmUgYWx3YXlzIGFsc28gY29uc2lkZXJlZFxuICAgICAgICAgIC8vIGFic3RyYWN0IGRpcmVjdGl2ZXMgYW5kIG5lZWQgdG8gYmUgZGVjb3JhdGVkIHRvby4gVGhpcyBpcyBuZWNlc3NhcnkgYXMgb3RoZXJ3aXNlXG4gICAgICAgICAgLy8gdGhlIGluaGVyaXRhbmNlIGNoYWluIGNhbm5vdCBiZSByZXNvbHZlZCBieSB0aGUgQW5ndWxhciBjb21waWxlci4gZS5nLiB3aGVuIGl0XG4gICAgICAgICAgLy8gZmxhdHRlbnMgZGlyZWN0aXZlIG1ldGFkYXRhIGZvciB0eXBlIGNoZWNraW5nLiBJbiB0aGUgb3RoZXIgY2FzZSwgd2UgbmV2ZXIgd2FudFxuICAgICAgICAgIC8vIHRvIG1pZ3JhdGUgYSBjbGFzcyBpZiBpdCBleHRlbmRzIGZyb20gYSBub24tYWJzdHJhY3QgQW5ndWxhciBkZWNsYXJhdGlvbi4gVGhhdFxuICAgICAgICAgIC8vIGlzIGFuIHVuc3VwcG9ydGVkIHBhdHRlcm4gYXMgb2YgdjkgYW5kIHdhcyBwcmV2aW91c2x5IGhhbmRsZWQgd2l0aCB0aGVcbiAgICAgICAgICAvLyBgdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRpYCBtaWdyYXRpb24gKHdoaWNoIGNvcGllZCB0aGUgaW5oZXJpdGVkIGRlY29yYXRvcikuXG4gICAgICAgICAgaWYgKGRlY2xhcmF0aW9ucy5nZXQoYmFzZUNsYXNzKSA9PT0gRGVjbGFyYXRpb25UeXBlLkFCU1RSQUNUX0RJUkVDVElWRSkge1xuICAgICAgICAgICAgZGV0ZWN0ZWRBYnN0cmFjdERpcmVjdGl2ZXMuYWRkKG5vZGUpO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBkZXRlY3RlZEFic3RyYWN0RGlyZWN0aXZlcy5kZWxldGUobm9kZSk7XG4gICAgICAgICAgfVxuICAgICAgICAgIGFtYmlndW91c0NsYXNzZXMuZGVsZXRlKG5vZGUpO1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9O1xuXG4gICAgLy8gQ2hlY2sgaW5oZXJpdGFuY2Ugb2YgYW55IGRldGVjdGVkIGFic3RyYWN0IGRpcmVjdGl2ZS4gV2Ugd2FudCB0byByZW1vdmVcbiAgICAvLyBjbGFzc2VzIHRoYXQgYXJlIG5vdCBlbGlnaWJsZSBhYnN0cmFjdCBkaXJlY3RpdmVzIGR1ZSB0byBpbmhlcml0YW5jZS4gaS5lLlxuICAgIC8vIGlmIGEgY2xhc3MgZXh0ZW5kcyBmcm9tIGEgY29tcG9uZW50LCBpdCBjYW5ub3QgYmUgYSBkZXJpdmVkIGFic3RyYWN0IGRpcmVjdGl2ZS5cbiAgICBjaGVja0luaGVyaXRhbmNlT2ZDbGFzc2VzKGRldGVjdGVkQWJzdHJhY3REaXJlY3RpdmVzKTtcbiAgICAvLyBVcGRhdGUgdGhlIGNsYXNzIGRlY2xhcmF0aW9ucyB0byByZWZsZWN0IHRoZSBkZXRlY3RlZCBhYnN0cmFjdCBkaXJlY3RpdmVzLiBUaGlzIGlzXG4gICAgLy8gdGhlbiB1c2VkIGxhdGVyIHdoZW4gd2UgY2hlY2sgZm9yIHVuZGVjb3JhdGVkIGNsYXNzZXMgdGhhdCBpbmhlcml0IGZyb20gYW4gYWJzdHJhY3RcbiAgICAvLyBkaXJlY3RpdmUgYW5kIG5lZWQgdG8gYmUgZGVjb3JhdGVkLlxuICAgIGRldGVjdGVkQWJzdHJhY3REaXJlY3RpdmVzLmZvckVhY2goXG4gICAgICAgIG4gPT4gZGVjbGFyYXRpb25zLnNldChuLCBEZWNsYXJhdGlvblR5cGUuQUJTVFJBQ1RfRElSRUNUSVZFKSk7XG4gICAgLy8gQ2hlY2sgYW1iaWd1b3VzIGFuZCB1bmRlY29yYXRlZCBjbGFzc2VzIGlmIHRoZXkgaW5oZXJpdCBmcm9tIGFuIGFic3RyYWN0IGRpcmVjdGl2ZS5cbiAgICAvLyBJZiB0aGV5IGRvLCB3ZSB3YW50IHRvIG1pZ3JhdGUgdGhlbSB0b28uIFNlZSBmdW5jdGlvbiBkZWZpbml0aW9uIGZvciBtb3JlIGRldGFpbHMuXG4gICAgY2hlY2tJbmhlcml0YW5jZU9mQ2xhc3NlcyhhbWJpZ3VvdXNDbGFzc2VzKTtcbiAgICBjaGVja0luaGVyaXRhbmNlT2ZDbGFzc2VzKHVuZGVjb3JhdGVkQ2xhc3Nlcyk7XG5cbiAgICByZXR1cm4ge2RldGVjdGVkQWJzdHJhY3REaXJlY3RpdmVzLCBhbWJpZ3VvdXNDbGFzc2VzfTtcbiAgfVxuXG4gIC8qKlxuICAgKiBBbmFseXplcyB0aGUgZ2l2ZW4gY2xhc3MgZGVjbGFyYXRpb24gYnkgZGV0ZXJtaW5pbmcgd2hldGhlciB0aGUgY2xhc3NcbiAgICogaXMgYSBkaXJlY3RpdmUsIGlzIGFuIGFic3RyYWN0IGRpcmVjdGl2ZSwgb3IgdXNlcyBBbmd1bGFyIGZlYXR1cmVzLlxuICAgKi9cbiAgcHJpdmF0ZSBfYW5hbHl6ZUNsYXNzRGVjbGFyYXRpb24obm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbik6IEFuYWx5emVkQ2xhc3Mge1xuICAgIGNvbnN0IG5nRGVjb3JhdG9ycyA9IG5vZGUuZGVjb3JhdG9ycyAmJiBnZXRBbmd1bGFyRGVjb3JhdG9ycyh0aGlzLnR5cGVDaGVja2VyLCBub2RlLmRlY29yYXRvcnMpO1xuICAgIGNvbnN0IGluZmVycmVkS2luZCA9IHRoaXMuX2RldGVybWluZUNsYXNzS2luZChub2RlKTtcbiAgICBpZiAobmdEZWNvcmF0b3JzID09PSB1bmRlZmluZWQgfHwgbmdEZWNvcmF0b3JzLmxlbmd0aCA9PT0gMCkge1xuICAgICAgcmV0dXJuIHtkZWNvcmF0ZWRUeXBlOiBudWxsLCBpbmZlcnJlZEtpbmR9O1xuICAgIH1cbiAgICBjb25zdCBkaXJlY3RpdmVEZWNvcmF0b3IgPSBuZ0RlY29yYXRvcnMuZmluZCgoe25hbWV9KSA9PiBuYW1lID09PSAnRGlyZWN0aXZlJyk7XG4gICAgY29uc3QgY29tcG9uZW50RGVjb3JhdG9yID0gbmdEZWNvcmF0b3JzLmZpbmQoKHtuYW1lfSkgPT4gbmFtZSA9PT0gJ0NvbXBvbmVudCcpO1xuICAgIGNvbnN0IHBpcGVEZWNvcmF0b3IgPSBuZ0RlY29yYXRvcnMuZmluZCgoe25hbWV9KSA9PiBuYW1lID09PSAnUGlwZScpO1xuICAgIGNvbnN0IGluamVjdGFibGVEZWNvcmF0b3IgPSBuZ0RlY29yYXRvcnMuZmluZCgoe25hbWV9KSA9PiBuYW1lID09PSAnSW5qZWN0YWJsZScpO1xuICAgIGNvbnN0IGlzQWJzdHJhY3REaXJlY3RpdmUgPVxuICAgICAgICBkaXJlY3RpdmVEZWNvcmF0b3IgIT09IHVuZGVmaW5lZCAmJiB0aGlzLl9pc0Fic3RyYWN0RGlyZWN0aXZlKGRpcmVjdGl2ZURlY29yYXRvcik7XG5cbiAgICBsZXQgZGVjb3JhdGVkVHlwZTogRGVjbGFyYXRpb25UeXBlfG51bGwgPSBudWxsO1xuICAgIGlmIChpc0Fic3RyYWN0RGlyZWN0aXZlKSB7XG4gICAgICBkZWNvcmF0ZWRUeXBlID0gRGVjbGFyYXRpb25UeXBlLkFCU1RSQUNUX0RJUkVDVElWRTtcbiAgICB9IGVsc2UgaWYgKGNvbXBvbmVudERlY29yYXRvciAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBkZWNvcmF0ZWRUeXBlID0gRGVjbGFyYXRpb25UeXBlLkNPTVBPTkVOVDtcbiAgICB9IGVsc2UgaWYgKGRpcmVjdGl2ZURlY29yYXRvciAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBkZWNvcmF0ZWRUeXBlID0gRGVjbGFyYXRpb25UeXBlLkRJUkVDVElWRTtcbiAgICB9IGVsc2UgaWYgKHBpcGVEZWNvcmF0b3IgIT09IHVuZGVmaW5lZCkge1xuICAgICAgZGVjb3JhdGVkVHlwZSA9IERlY2xhcmF0aW9uVHlwZS5QSVBFO1xuICAgIH0gZWxzZSBpZiAoaW5qZWN0YWJsZURlY29yYXRvciAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBkZWNvcmF0ZWRUeXBlID0gRGVjbGFyYXRpb25UeXBlLklOSkVDVEFCTEU7XG4gICAgfVxuICAgIHJldHVybiB7ZGVjb3JhdGVkVHlwZSwgaW5mZXJyZWRLaW5kfTtcbiAgfVxuXG4gIC8qKlxuICAgKiBDaGVja3Mgd2hldGhlciB0aGUgZ2l2ZW4gZGVjb3JhdG9yIHJlc29sdmVzIHRvIGFuIGFic3RyYWN0IGRpcmVjdGl2ZS4gQW4gZGlyZWN0aXZlIGlzXG4gICAqIGNvbnNpZGVyZWQgXCJhYnN0cmFjdFwiIGlmIHRoZXJlIGlzIG5vIHNlbGVjdG9yIHNwZWNpZmllZC5cbiAgICovXG4gIHByaXZhdGUgX2lzQWJzdHJhY3REaXJlY3RpdmUoe25vZGV9OiBOZ0RlY29yYXRvcik6IGJvb2xlYW4ge1xuICAgIGNvbnN0IG1ldGFkYXRhQXJncyA9IG5vZGUuZXhwcmVzc2lvbi5hcmd1bWVudHM7XG4gICAgaWYgKG1ldGFkYXRhQXJncy5sZW5ndGggPT09IDApIHtcbiAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cbiAgICBjb25zdCBtZXRhZGF0YUV4cHIgPSB1bndyYXBFeHByZXNzaW9uKG1ldGFkYXRhQXJnc1swXSk7XG4gICAgaWYgKCF0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG1ldGFkYXRhRXhwcikpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgY29uc3QgbWV0YWRhdGEgPSByZWZsZWN0T2JqZWN0TGl0ZXJhbChtZXRhZGF0YUV4cHIpO1xuICAgIGlmICghbWV0YWRhdGEuaGFzKCdzZWxlY3RvcicpKSB7XG4gICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuICAgIGNvbnN0IHNlbGVjdG9yID0gdGhpcy5wYXJ0aWFsRXZhbHVhdG9yLmV2YWx1YXRlKG1ldGFkYXRhLmdldCgnc2VsZWN0b3InKSEpO1xuICAgIHJldHVybiBzZWxlY3RvciA9PSBudWxsO1xuICB9XG5cbiAgLyoqXG4gICAqIERldGVybWluZXMgdGhlIGtpbmQgb2YgYSBnaXZlbiBjbGFzcyBpbiB0ZXJtcyBvZiBBbmd1bGFyLiBUaGUgbWV0aG9kIGNoZWNrc1xuICAgKiB3aGV0aGVyIHRoZSBnaXZlbiBjbGFzcyBoYXMgbWVtYmVycyB0aGF0IGluZGljYXRlIHRoZSB1c2Ugb2YgQW5ndWxhciBmZWF0dXJlcy5cbiAgICogZS5nLiBsaWZlY3ljbGUgaG9va3Mgb3IgZGVjb3JhdGVkIG1lbWJlcnMgbGlrZSBgQElucHV0YCBvciBgQE91dHB1dGAgYXJlXG4gICAqIGNvbnNpZGVyZWQgQW5ndWxhciBmZWF0dXJlcy4uXG4gICAqL1xuICBwcml2YXRlIF9kZXRlcm1pbmVDbGFzc0tpbmQobm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbik6IEluZmVycmVkS2luZCB7XG4gICAgbGV0IHVzYWdlID0gSW5mZXJyZWRLaW5kLlVOS05PV047XG5cbiAgICBmb3IgKGNvbnN0IG1lbWJlciBvZiBub2RlLm1lbWJlcnMpIHtcbiAgICAgIGNvbnN0IHByb3BlcnR5TmFtZSA9IG1lbWJlci5uYW1lICE9PSB1bmRlZmluZWQgPyBnZXRQcm9wZXJ0eU5hbWVUZXh0KG1lbWJlci5uYW1lKSA6IG51bGw7XG5cbiAgICAgIC8vIElmIHRoZSBjbGFzcyBkZWNsYXJlcyBhbnkgb2YgdGhlIGtub3duIGRpcmVjdGl2ZSBsaWZlY3ljbGUgaG9va3MsIHdlIGNhblxuICAgICAgLy8gaW1tZWRpYXRlbHkgZXhpdCB0aGUgbG9vcCBhcyB0aGUgY2xhc3MgaXMgZ3VhcmFudGVlZCB0byBiZSBhIGRpcmVjdGl2ZS5cbiAgICAgIGlmIChwcm9wZXJ0eU5hbWUgIT09IG51bGwgJiYgRElSRUNUSVZFX0xJRkVDWUNMRV9IT09LUy5oYXMocHJvcGVydHlOYW1lKSkge1xuICAgICAgICByZXR1cm4gSW5mZXJyZWRLaW5kLkRJUkVDVElWRTtcbiAgICAgIH1cblxuICAgICAgY29uc3QgbmdEZWNvcmF0b3JzID0gbWVtYmVyLmRlY29yYXRvcnMgIT09IHVuZGVmaW5lZCA/XG4gICAgICAgICAgZ2V0QW5ndWxhckRlY29yYXRvcnModGhpcy50eXBlQ2hlY2tlciwgbWVtYmVyLmRlY29yYXRvcnMpIDpcbiAgICAgICAgICBbXTtcbiAgICAgIGZvciAoY29uc3Qge25hbWV9IG9mIG5nRGVjb3JhdG9ycykge1xuICAgICAgICBpZiAoRElSRUNUSVZFX0ZJRUxEX0RFQ09SQVRPUlMuaGFzKG5hbWUpKSB7XG4gICAgICAgICAgcmV0dXJuIEluZmVycmVkS2luZC5ESVJFQ1RJVkU7XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgLy8gSWYgdGhlIGNsYXNzIGRlY2xhcmVzIGFueSBvZiB0aGUgbGlmZWN5Y2xlIGhvb2tzIHRoYXQgZG8gbm90IGd1YXJhbnRlZSB0aGF0XG4gICAgICAvLyB0aGUgZ2l2ZW4gY2xhc3MgaXMgYSBkaXJlY3RpdmUsIHVwZGF0ZSB0aGUga2luZCBhbmQgY29udGludWUgbG9va2luZyBmb3Igb3RoZXJcbiAgICAgIC8vIG1lbWJlcnMgdGhhdCB3b3VsZCB1bnZlaWwgYSBtb3JlIHNwZWNpZmljIGtpbmQgKGkuZS4gYmVpbmcgYSBkaXJlY3RpdmUpLlxuICAgICAgaWYgKHByb3BlcnR5TmFtZSAhPT0gbnVsbCAmJiBBTUJJR1VPVVNfTElGRUNZQ0xFX0hPT0tTLmhhcyhwcm9wZXJ0eU5hbWUpKSB7XG4gICAgICAgIHVzYWdlID0gSW5mZXJyZWRLaW5kLkFNQklHVU9VUztcbiAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gdXNhZ2U7XG4gIH1cblxuICAvKipcbiAgICogQ2hlY2tzIHdoZXRoZXIgYSBnaXZlbiBjbGFzcyBoYXMgYmVlbiByZXBvcnRlZCBhcyBhbWJpZ3VvdXMgaW4gcHJldmlvdXNcbiAgICogbWlncmF0aW9uIHJ1bi4gZS5nLiB3aGVuIGJ1aWxkIHRhcmdldHMgYXJlIG1pZ3JhdGVkIGZpcnN0LCBhbmQgdGhlbiB0ZXN0XG4gICAqIHRhcmdldHMgdGhhdCBoYXZlIGFuIG92ZXJsYXAgd2l0aCBidWlsZCBzb3VyY2UgZmlsZXMsIHRoZSBzYW1lIGNsYXNzXG4gICAqIGNvdWxkIGJlIGRldGVjdGVkIGFzIGFtYmlndW91cy5cbiAgICovXG4gIHByaXZhdGUgX2hhc0JlZW5SZXBvcnRlZEFzQW1iaWd1b3VzKG5vZGU6IHRzLkNsYXNzRGVjbGFyYXRpb24pOiBib29sZWFuIHtcbiAgICBjb25zdCBzb3VyY2VGaWxlID0gbm9kZS5nZXRTb3VyY2VGaWxlKCk7XG4gICAgY29uc3QgbGVhZGluZ0NvbW1lbnRzID0gdHMuZ2V0TGVhZGluZ0NvbW1lbnRSYW5nZXMoc291cmNlRmlsZS50ZXh0LCBub2RlLnBvcyk7XG4gICAgaWYgKGxlYWRpbmdDb21tZW50cyA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuICAgIHJldHVybiBsZWFkaW5nQ29tbWVudHMuc29tZShcbiAgICAgICAgKHtraW5kLCBwb3MsIGVuZH0pID0+IGtpbmQgPT09IHRzLlN5bnRheEtpbmQuU2luZ2xlTGluZUNvbW1lbnRUcml2aWEgJiZcbiAgICAgICAgICAgIHNvdXJjZUZpbGUudGV4dC5zdWJzdHJpbmcocG9zLCBlbmQpLmluY2x1ZGVzKGBUT0RPOiAke0FNQklHVU9VU19DTEFTU19UT0RPfWApKTtcbiAgfVxufVxuIl19