/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { StaticSymbol } from '../aot/static_symbol';
import { BindingForm, convertActionBinding, convertPropertyBinding, convertPropertyBindingBuiltins, EventHandlerVars } from '../compiler_util/expression_converter';
import * as o from '../output/output_ast';
import { templateVisitAll } from '../template_parser/template_ast';
/**
 * Generates code that is used to type check templates.
 */
export class TypeCheckCompiler {
    constructor(options, reflector) {
        this.options = options;
        this.reflector = reflector;
    }
    /**
     * Important notes:
     * - This must not produce new `import` statements, but only refer to types outside
     *   of the file via the variables provided via externalReferenceVars.
     *   This allows Typescript to reuse the old program's structure as no imports have changed.
     * - This must not produce any exports, as this would pollute the .d.ts file
     *   and also violate the point above.
     */
    compileComponent(componentId, component, template, usedPipes, externalReferenceVars, ctx) {
        const pipes = new Map();
        usedPipes.forEach(p => pipes.set(p.name, p.type.reference));
        let embeddedViewCount = 0;
        const viewBuilderFactory = (parent, guards) => {
            const embeddedViewIndex = embeddedViewCount++;
            return new ViewBuilder(this.options, this.reflector, externalReferenceVars, parent, component.type.reference, component.isHost, embeddedViewIndex, pipes, guards, ctx, viewBuilderFactory);
        };
        const visitor = viewBuilderFactory(null, []);
        visitor.visitAll([], template);
        return visitor.build(componentId);
    }
}
const DYNAMIC_VAR_NAME = '_any';
class TypeCheckLocalResolver {
    notifyImplicitReceiverUse() { }
    getLocal(name) {
        if (name === EventHandlerVars.event.name) {
            // References to the event should not be type-checked.
            // TODO(chuckj): determine a better type for the event.
            return o.variable(DYNAMIC_VAR_NAME);
        }
        return null;
    }
}
const defaultResolver = new TypeCheckLocalResolver();
class ViewBuilder {
    constructor(options, reflector, externalReferenceVars, parent, component, isHostComponent, embeddedViewIndex, pipes, guards, ctx, viewBuilderFactory) {
        this.options = options;
        this.reflector = reflector;
        this.externalReferenceVars = externalReferenceVars;
        this.parent = parent;
        this.component = component;
        this.isHostComponent = isHostComponent;
        this.embeddedViewIndex = embeddedViewIndex;
        this.pipes = pipes;
        this.guards = guards;
        this.ctx = ctx;
        this.viewBuilderFactory = viewBuilderFactory;
        this.refOutputVars = new Map();
        this.variables = [];
        this.children = [];
        this.updates = [];
        this.actions = [];
    }
    getOutputVar(type) {
        let varName;
        if (type === this.component && this.isHostComponent) {
            varName = DYNAMIC_VAR_NAME;
        }
        else if (type instanceof StaticSymbol) {
            varName = this.externalReferenceVars.get(type);
        }
        else {
            varName = DYNAMIC_VAR_NAME;
        }
        if (!varName) {
            throw new Error(`Illegal State: referring to a type without a variable ${JSON.stringify(type)}`);
        }
        return varName;
    }
    getTypeGuardExpressions(ast) {
        const result = [...this.guards];
        for (let directive of ast.directives) {
            for (let input of directive.inputs) {
                const guard = directive.directive.guards[input.directiveName];
                if (guard) {
                    const useIf = guard === 'UseIf';
                    result.push({
                        guard,
                        useIf,
                        expression: {
                            context: this.component,
                            value: input.value,
                            sourceSpan: input.sourceSpan,
                        },
                    });
                }
            }
        }
        return result;
    }
    visitAll(variables, astNodes) {
        this.variables = variables;
        templateVisitAll(this, astNodes);
    }
    build(componentId, targetStatements = []) {
        this.children.forEach((child) => child.build(componentId, targetStatements));
        let viewStmts = [o.variable(DYNAMIC_VAR_NAME).set(o.NULL_EXPR).toDeclStmt(o.DYNAMIC_TYPE)];
        let bindingCount = 0;
        this.updates.forEach((expression) => {
            const { sourceSpan, context, value } = this.preprocessUpdateExpression(expression);
            const bindingId = `${bindingCount++}`;
            const nameResolver = context === this.component ? this : defaultResolver;
            const { stmts, currValExpr } = convertPropertyBinding(nameResolver, o.variable(this.getOutputVar(context)), value, bindingId, BindingForm.General);
            stmts.push(new o.ExpressionStatement(currValExpr));
            viewStmts.push(...stmts.map((stmt) => o.applySourceSpanToStatementIfNeeded(stmt, sourceSpan)));
        });
        this.actions.forEach(({ sourceSpan, context, value }) => {
            const bindingId = `${bindingCount++}`;
            const nameResolver = context === this.component ? this : defaultResolver;
            const { stmts } = convertActionBinding(nameResolver, o.variable(this.getOutputVar(context)), value, bindingId);
            viewStmts.push(...stmts.map((stmt) => o.applySourceSpanToStatementIfNeeded(stmt, sourceSpan)));
        });
        if (this.guards.length) {
            let guardExpression = undefined;
            for (const guard of this.guards) {
                const { context, value } = this.preprocessUpdateExpression(guard.expression);
                const bindingId = `${bindingCount++}`;
                const nameResolver = context === this.component ? this : defaultResolver;
                // We only support support simple expressions and ignore others as they
                // are unlikely to affect type narrowing.
                const { stmts, currValExpr } = convertPropertyBinding(nameResolver, o.variable(this.getOutputVar(context)), value, bindingId, BindingForm.TrySimple);
                if (stmts.length == 0) {
                    const guardClause = guard.useIf ? currValExpr : this.ctx.importExpr(guard.guard).callFn([currValExpr]);
                    guardExpression = guardExpression ? guardExpression.and(guardClause) : guardClause;
                }
            }
            if (guardExpression) {
                viewStmts = [new o.IfStmt(guardExpression, viewStmts)];
            }
        }
        const viewName = `_View_${componentId}_${this.embeddedViewIndex}`;
        const viewFactory = new o.DeclareFunctionStmt(viewName, [], viewStmts);
        targetStatements.push(viewFactory);
        return targetStatements;
    }
    visitBoundText(ast, context) {
        const astWithSource = ast.value;
        const inter = astWithSource.ast;
        inter.expressions.forEach((expr) => this.updates.push({ context: this.component, value: expr, sourceSpan: ast.sourceSpan }));
    }
    visitEmbeddedTemplate(ast, context) {
        this.visitElementOrTemplate(ast);
        // Note: The old view compiler used to use an `any` type
        // for the context in any embedded view.
        // We keep this behaivor behind a flag for now.
        if (this.options.fullTemplateTypeCheck) {
            // Find any applicable type guards. For example, NgIf has a type guard on ngIf
            // (see NgIf.ngIfTypeGuard) that can be used to indicate that a template is only
            // stamped out if ngIf is truthy so any bindings in the template can assume that,
            // if a nullable type is used for ngIf, that expression is not null or undefined.
            const guards = this.getTypeGuardExpressions(ast);
            const childVisitor = this.viewBuilderFactory(this, guards);
            this.children.push(childVisitor);
            childVisitor.visitAll(ast.variables, ast.children);
        }
    }
    visitElement(ast, context) {
        this.visitElementOrTemplate(ast);
        let inputDefs = [];
        let updateRendererExpressions = [];
        let outputDefs = [];
        ast.inputs.forEach((inputAst) => {
            this.updates.push({ context: this.component, value: inputAst.value, sourceSpan: inputAst.sourceSpan });
        });
        templateVisitAll(this, ast.children);
    }
    visitElementOrTemplate(ast) {
        ast.directives.forEach((dirAst) => {
            this.visitDirective(dirAst);
        });
        ast.references.forEach((ref) => {
            let outputVarType = null;
            // Note: The old view compiler used to use an `any` type
            // for directives exposed via `exportAs`.
            // We keep this behaivor behind a flag for now.
            if (ref.value && ref.value.identifier && this.options.fullTemplateTypeCheck) {
                outputVarType = ref.value.identifier.reference;
            }
            else {
                outputVarType = o.BuiltinTypeName.Dynamic;
            }
            this.refOutputVars.set(ref.name, outputVarType);
        });
        ast.outputs.forEach((outputAst) => {
            this.actions.push({ context: this.component, value: outputAst.handler, sourceSpan: outputAst.sourceSpan });
        });
    }
    visitDirective(dirAst) {
        const dirType = dirAst.directive.type.reference;
        dirAst.inputs.forEach((input) => this.updates.push({ context: this.component, value: input.value, sourceSpan: input.sourceSpan }));
        // Note: The old view compiler used to use an `any` type
        // for expressions in host properties / events.
        // We keep this behaivor behind a flag for now.
        if (this.options.fullTemplateTypeCheck) {
            dirAst.hostProperties.forEach((inputAst) => this.updates.push({ context: dirType, value: inputAst.value, sourceSpan: inputAst.sourceSpan }));
            dirAst.hostEvents.forEach((hostEventAst) => this.actions.push({
                context: dirType,
                value: hostEventAst.handler,
                sourceSpan: hostEventAst.sourceSpan
            }));
        }
    }
    notifyImplicitReceiverUse() { }
    getLocal(name) {
        if (name == EventHandlerVars.event.name) {
            return o.variable(this.getOutputVar(o.BuiltinTypeName.Dynamic));
        }
        for (let currBuilder = this; currBuilder; currBuilder = currBuilder.parent) {
            let outputVarType;
            // check references
            outputVarType = currBuilder.refOutputVars.get(name);
            if (outputVarType == null) {
                // check variables
                const varAst = currBuilder.variables.find((varAst) => varAst.name === name);
                if (varAst) {
                    outputVarType = o.BuiltinTypeName.Dynamic;
                }
            }
            if (outputVarType != null) {
                return o.variable(this.getOutputVar(outputVarType));
            }
        }
        return null;
    }
    pipeOutputVar(name) {
        const pipe = this.pipes.get(name);
        if (!pipe) {
            throw new Error(`Illegal State: Could not find pipe ${name} in template of ${this.component}`);
        }
        return this.getOutputVar(pipe);
    }
    preprocessUpdateExpression(expression) {
        return {
            sourceSpan: expression.sourceSpan,
            context: expression.context,
            value: convertPropertyBindingBuiltins({
                createLiteralArrayConverter: (argCount) => (args) => {
                    const arr = o.literalArr(args);
                    // Note: The old view compiler used to use an `any` type
                    // for arrays.
                    return this.options.fullTemplateTypeCheck ? arr : arr.cast(o.DYNAMIC_TYPE);
                },
                createLiteralMapConverter: (keys) => (values) => {
                    const entries = keys.map((k, i) => ({
                        key: k.key,
                        value: values[i],
                        quoted: k.quoted,
                    }));
                    const map = o.literalMap(entries);
                    // Note: The old view compiler used to use an `any` type
                    // for maps.
                    return this.options.fullTemplateTypeCheck ? map : map.cast(o.DYNAMIC_TYPE);
                },
                createPipeConverter: (name, argCount) => (args) => {
                    // Note: The old view compiler used to use an `any` type
                    // for pipes.
                    const pipeExpr = this.options.fullTemplateTypeCheck ?
                        o.variable(this.pipeOutputVar(name)) :
                        o.variable(this.getOutputVar(o.BuiltinTypeName.Dynamic));
                    return pipeExpr.callMethod('transform', args);
                },
            }, expression.value)
        };
    }
    visitNgContent(ast, context) { }
    visitText(ast, context) { }
    visitDirectiveProperty(ast, context) { }
    visitReference(ast, context) { }
    visitVariable(ast, context) { }
    visitEvent(ast, context) { }
    visitElementProperty(ast, context) { }
    visitAttr(ast, context) { }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHlwZV9jaGVja19jb21waWxlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy92aWV3X2NvbXBpbGVyL3R5cGVfY2hlY2tfY29tcGlsZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBSUgsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBRWxELE9BQU8sRUFBQyxXQUFXLEVBQUUsb0JBQW9CLEVBQUUsc0JBQXNCLEVBQUUsOEJBQThCLEVBQUUsZ0JBQWdCLEVBQWdCLE1BQU0sdUNBQXVDLENBQUM7QUFFakwsT0FBTyxLQUFLLENBQUMsTUFBTSxzQkFBc0IsQ0FBQztBQUUxQyxPQUFPLEVBQXVNLGdCQUFnQixFQUF1QixNQUFNLGlDQUFpQyxDQUFDO0FBSTdSOztHQUVHO0FBQ0gsTUFBTSxPQUFPLGlCQUFpQjtJQUM1QixZQUFvQixPQUEyQixFQUFVLFNBQTBCO1FBQS9ELFlBQU8sR0FBUCxPQUFPLENBQW9CO1FBQVUsY0FBUyxHQUFULFNBQVMsQ0FBaUI7SUFBRyxDQUFDO0lBRXZGOzs7Ozs7O09BT0c7SUFDSCxnQkFBZ0IsQ0FDWixXQUFtQixFQUFFLFNBQW1DLEVBQUUsUUFBdUIsRUFDakYsU0FBK0IsRUFBRSxxQkFBZ0QsRUFDakYsR0FBa0I7UUFDcEIsTUFBTSxLQUFLLEdBQUcsSUFBSSxHQUFHLEVBQXdCLENBQUM7UUFDOUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLElBQUksRUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUM7UUFDNUQsSUFBSSxpQkFBaUIsR0FBRyxDQUFDLENBQUM7UUFDMUIsTUFBTSxrQkFBa0IsR0FDcEIsQ0FBQyxNQUF3QixFQUFFLE1BQXlCLEVBQWUsRUFBRTtZQUNuRSxN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YUFBYTtvQkFDYixNQUFNLFFBQVEsR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLHFCQUFxQixDQUFDLENBQUM7d0JBQ2pELENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7d0JBQ3RDLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7b0JBQzdELE9BQU8sUUFBUSxDQUFDLFVBQVUsQ0FBQyxXQUFXLEVBQUUsSUFBSSxDQUFDLENBQUM7Z0JBQ2hELENBQUM7YUFDRixFQUNELFVBQVUsQ0FBQyxLQUFLLENBQUM7U0FDdEIsQ0FBQztJQUNKLENBQUM7SUFFRCxjQUFjLENBQUMsR0FBaUIsRUFBRSxPQUFZLElBQVEsQ0FBQztJQUN2RCxTQUFTLENBQUMsR0FBWSxFQUFFLE9BQVksSUFBUSxDQUFDO0lBQzdDLHNCQUFzQixDQUFDLEdBQThCLEVBQUUsT0FBWSxJQUFRLENBQUM7SUFDNUUsY0FBYyxDQUFDLEdBQWlCLEVBQUUsT0FBWSxJQUFRLENBQUM7SUFDdkQsYUFBYSxDQUFDLEdBQWdCLEVBQUUsT0FBWSxJQUFRLENBQUM7SUFDckQsVUFBVSxDQUFDLEdBQWtCLEVBQUUsT0FBWSxJQUFRLENBQUM7SUFDcEQsb0JBQW9CLENBQUMsR0FBNEIsRUFBRSxPQUFZLElBQVEsQ0FBQztJQUN4RSxTQUFTLENBQUMsR0FBWSxFQUFFLE9BQVksSUFBUSxDQUFDO0NBQzlDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7QW90Q29tcGlsZXJPcHRpb25zfSBmcm9tICcuLi9hb3QvY29tcGlsZXJfb3B0aW9ucyc7XG5pbXBvcnQge1N0YXRpY1JlZmxlY3Rvcn0gZnJvbSAnLi4vYW90L3N0YXRpY19yZWZsZWN0b3InO1xuaW1wb3J0IHtTdGF0aWNTeW1ib2x9IGZyb20gJy4uL2FvdC9zdGF0aWNfc3ltYm9sJztcbmltcG9ydCB7Q29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhLCBDb21waWxlUGlwZVN1bW1hcnl9IGZyb20gJy4uL2NvbXBpbGVfbWV0YWRhdGEnO1xuaW1wb3J0IHtCaW5kaW5nRm9ybSwgY29udmVydEFjdGlvbkJpbmRpbmcsIGNvbnZlcnRQcm9wZXJ0eUJpbmRpbmcsIGNvbnZlcnRQcm9wZXJ0eUJpbmRpbmdCdWlsdGlucywgRXZlbnRIYW5kbGVyVmFycywgTG9jYWxSZXNvbHZlcn0gZnJvbSAnLi4vY29tcGlsZXJfdXRpbC9leHByZXNzaW9uX2NvbnZlcnRlcic7XG5pbXBvcnQge0FTVCwgQVNUV2l0aFNvdXJjZSwgSW50ZXJwb2xhdGlvbn0gZnJvbSAnLi4vZXhwcmVzc2lvbl9wYXJzZXIvYXN0JztcbmltcG9ydCAqIGFzIG8gZnJvbSAnLi4vb3V0cHV0L291dHB1dF9hc3QnO1xuaW1wb3J0IHtQYXJzZVNvdXJjZVNwYW59IGZyb20gJy4uL3BhcnNlX3V0aWwnO1xuaW1wb3J0IHtBdHRyQXN0LCBCb3VuZERpcmVjdGl2ZVByb3BlcnR5QXN0LCBCb3VuZEVsZW1lbnRQcm9wZXJ0eUFzdCwgQm91bmRFdmVudEFzdCwgQm91bmRUZXh0QXN0LCBEaXJlY3RpdmVBc3QsIEVsZW1lbnRBc3QsIEVtYmVkZGVkVGVtcGxhdGVBc3QsIE5nQ29udGVudEFzdCwgUmVmZXJlbmNlQXN0LCBUZW1wbGF0ZUFzdCwgVGVtcGxhdGVBc3RWaXNpdG9yLCB0ZW1wbGF0ZVZpc2l0QWxsLCBUZXh0QXN0LCBWYXJpYWJsZUFzdH0gZnJvbSAnLi4vdGVtcGxhdGVfcGFyc2VyL3RlbXBsYXRlX2FzdCc7XG5pbXBvcnQge091dHB1dENvbnRleHR9IGZyb20gJy4uL3V0aWwnO1xuXG5cbi8qKlxuICogR2VuZXJhdGVzIGNvZGUgdGhhdCBpcyB1c2VkIHRvIHR5cGUgY2hlY2sgdGVtcGxhdGVzLlxuICovXG5leHBvcnQgY2xhc3MgVHlwZUNoZWNrQ29tcGlsZXIge1xuICBjb25zdHJ1Y3Rvcihwcml2YXRlIG9wdGlvbnM6IEFvdENvbXBpbGVyT3B0aW9ucywgcHJpdmF0ZSByZWZsZWN0b3I6IFN0YXRpY1JlZmxlY3Rvcikge31cblxuICAvKipcbiAgICogSW1wb3J0YW50IG5vdGVzOlxuICAgKiAtIFRoaXMgbXVzdCBub3QgcHJvZHVjZSBuZXcgYGltcG9ydGAgc3RhdGVtZW50cywgYnV0IG9ubHkgcmVmZXIgdG8gdHlwZXMgb3V0c2lkZVxuICAgKiAgIG9mIHRoZSBmaWxlIHZpYSB0aGUgdmFyaWFibGVzIHByb3ZpZGVkIHZpYSBleHRlcm5hbFJlZmVyZW5jZVZhcnMuXG4gICAqICAgVGhpcyBhbGxvd3MgVHlwZXNjcmlwdCB0byByZXVzZSB0aGUgb2xkIHByb2dyYW0ncyBzdHJ1Y3R1cmUgYXMgbm8gaW1wb3J0cyBoYXZlIGNoYW5nZWQuXG4gICAqIC0gVGhpcyBtdXN0IG5vdCBwcm9kdWNlIGFueSBleHBvcnRzLCBhcyB0aGlzIHdvdWxkIHBvbGx1dGUgdGhlIC5kLnRzIGZpbGVcbiAgICogICBhbmQgYWxzbyB2aW9sYXRlIHRoZSBwb2ludCBhYm92ZS5cbiAgICovXG4gIGNvbXBpbGVDb21wb25lbnQoXG4gICAgICBjb21wb25lbnRJZDogc3RyaW5nLCBjb21wb25lbnQ6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSwgdGVtcGxhdGU6IFRlbXBsYXRlQXN0W10sXG4gICAgICB1c2VkUGlwZXM6IENvbXBpbGVQaXBlU3VtbWFyeVtdLCBleHRlcm5hbFJlZmVyZW5jZVZhcnM6IE1hcDxTdGF0aWNTeW1ib2wsIHN0cmluZz4sXG4gICAgICBjdHg6IE91dHB1dENvbnRleHQpOiBvLlN0YXRlbWVudFtdIHtcbiAgICBjb25zdCBwaXBlcyA9IG5ldyBNYXA8c3RyaW5nLCBTdGF0aWNTeW1ib2w+KCk7XG4gICAgdXNlZFBpcGVzLmZvckVhY2gocCA9PiBwaXBlcy5zZXQocC5uYW1lLCBwLnR5cGUucmVmZXJlbmNlKSk7XG4gICAgbGV0IGVtYmVkZGVkVmlld0NvdW50ID0gMDtcbiAgICBjb25zdCB2aWV3QnVpbGRlckZhY3RvcnkgPVxuICAgICAgICAocGFyZW50OiBWaWV3QnVpbGRlcnxudWxsLCBndWFyZHM6IEd1YXJkRXhwcmVzc2lvbltdKTogVmlld0J1aWxkZXIgPT4ge1xuICAgICAgICAgIGNvbnN0IGVtYmVkZGVkVmlld0luZGV4ID0gZW1iZWRkZWRWaWV3Q291bnQrKztcbiAgICAgICAgICByZXR1cm4gbmV3IFZpZXdCdWlsZGVyKFxuICAgICAgICAgICAgICB0aGlzLm9wdGlvbnMsIHRoaXMucmVmbGVjdG9yLCBleHRlcm5hbFJlZmVyZW5jZVZhcnMsIHBhcmVudCwgY29tcG9uZW50LnR5cGUucmVmZXJlbmNlLFxuICAgICAgICAgICAgICBjb21wb25lbnQuaXNIb3N0LCBlbWJlZGRlZFZpZXdJbmRleCwgcGlwZXMsIGd1YXJkcywgY3R4LCB2aWV3QnVpbGRlckZhY3RvcnkpO1xuICAgICAgICB9O1xuXG4gICAgY29uc3QgdmlzaXRvciA9IHZpZXdCdWlsZGVyRmFjdG9yeShudWxsLCBbXSk7XG4gICAgdmlzaXRvci52aXNpdEFsbChbXSwgdGVtcGxhdGUpO1xuXG4gICAgcmV0dXJuIHZpc2l0b3IuYnVpbGQoY29tcG9uZW50SWQpO1xuICB9XG59XG5cbmludGVyZmFjZSBHdWFyZEV4cHJlc3Npb24ge1xuICBndWFyZDogU3RhdGljU3ltYm9sO1xuICB1c2VJZjogYm9vbGVhbjtcbiAgZXhwcmVzc2lvbjogRXhwcmVzc2lvbjtcbn1cblxuaW50ZXJmYWNlIFZpZXdCdWlsZGVyRmFjdG9yeSB7XG4gIChwYXJlbnQ6IFZpZXdCdWlsZGVyLCBndWFyZHM6IEd1YXJkRXhwcmVzc2lvbltdKTogVmlld0J1aWxkZXI7XG59XG5cbi8vIE5vdGU6IFRoaXMgaXMgdXNlZCBhcyBrZXkgaW4gTWFwIGFuZCBzaG91bGQgdGhlcmVmb3JlIGJlXG4vLyB1bmlxdWUgcGVyIHZhbHVlLlxudHlwZSBPdXRwdXRWYXJUeXBlID0gby5CdWlsdGluVHlwZU5hbWV8U3RhdGljU3ltYm9sO1xuXG5pbnRlcmZhY2UgRXhwcmVzc2lvbiB7XG4gIGNvbnRleHQ6IE91dHB1dFZhclR5cGU7XG4gIHNvdXJjZVNwYW46IFBhcnNlU291cmNlU3BhbjtcbiAgdmFsdWU6IEFTVDtcbn1cblxuY29uc3QgRFlOQU1JQ19WQVJfTkFNRSA9ICdfYW55JztcblxuY2xhc3MgVHlwZUNoZWNrTG9jYWxSZXNvbHZlciBpbXBsZW1lbnRzIExvY2FsUmVzb2x2ZXIge1xuICBub3RpZnlJbXBsaWNpdFJlY2VpdmVyVXNlKCk6IHZvaWQge31cbiAgZ2V0TG9jYWwobmFtZTogc3RyaW5nKTogby5FeHByZXNzaW9ufG51bGwge1xuICAgIGlmIChuYW1lID09PSBFdmVudEhhbmRsZXJWYXJzLmV2ZW50Lm5hbWUpIHtcbiAgICAgIC8vIFJlZmVyZW5jZXMgdG8gdGhlIGV2ZW50IHNob3VsZCBub3QgYmUgdHlwZS1jaGVja2VkLlxuICAgICAgLy8gVE9ETyhjaHVja2opOiBkZXRlcm1pbmUgYSBiZXR0ZXIgdHlwZSBmb3IgdGhlIGV2ZW50LlxuICAgICAgcmV0dXJuIG8udmFyaWFibGUoRFlOQU1JQ19WQVJfTkFNRSk7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG59XG5cbmNvbnN0IGRlZmF1bHRSZXNvbHZlciA9IG5ldyBUeXBlQ2hlY2tMb2NhbFJlc29sdmVyKCk7XG5cbmNsYXNzIFZpZXdCdWlsZGVyIGltcGxlbWVudHMgVGVtcGxhdGVBc3RWaXNpdG9yLCBMb2NhbFJlc29sdmVyIHtcbiAgcHJpdmF0ZSByZWZPdXRwdXRWYXJzID0gbmV3IE1hcDxzdHJpbmcsIE91dHB1dFZhclR5cGU+KCk7XG4gIHByaXZhdGUgdmFyaWFibGVzOiBWYXJpYWJsZUFzdFtdID0gW107XG4gIHByaXZhdGUgY2hpbGRyZW46IFZpZXdCdWlsZGVyW10gPSBbXTtcbiAgcHJpdmF0ZSB1cGRhdGVzOiBFeHByZXNzaW9uW10gPSBbXTtcbiAgcHJpdmF0ZSBhY3Rpb25zOiBFeHByZXNzaW9uW10gPSBbXTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgb3B0aW9uczogQW90Q29tcGlsZXJPcHRpb25zLCBwcml2YXRlIHJlZmxlY3RvcjogU3RhdGljUmVmbGVjdG9yLFxuICAgICAgcHJpdmF0ZSBleHRlcm5hbFJlZmVyZW5jZVZhcnM6IE1hcDxTdGF0aWNTeW1ib2wsIHN0cmluZz4sIHByaXZhdGUgcGFyZW50OiBWaWV3QnVpbGRlcnxudWxsLFxuICAgICAgcHJpdmF0ZSBjb21wb25lbnQ6IFN0YXRpY1N5bWJvbCwgcHJpdmF0ZSBpc0hvc3RDb21wb25lbnQ6IGJvb2xlYW4sXG4gICAgICBwcml2YXRlIGVtYmVkZGVkVmlld0luZGV4OiBudW1iZXIsIHByaXZhdGUgcGlwZXM6IE1hcDxzdHJpbmcsIFN0YXRpY1N5bWJvbD4sXG4gICAgICBwcml2YXRlIGd1YXJkczogR3VhcmRFeHByZXNzaW9uW10sIHByaXZhdGUgY3R4OiBPdXRwdXRDb250ZXh0LFxuICAgICAgcHJpdmF0ZSB2aWV3QnVpbGRlckZhY3Rvcnk6IFZpZXdCdWlsZGVyRmFjdG9yeSkge31cblxuICBwcml2YXRlIGdldE91dHB1dFZhcih0eXBlOiBvLkJ1aWx0aW5UeXBlTmFtZXxTdGF0aWNTeW1ib2wpOiBzdHJpbmcge1xuICAgIGxldCB2YXJOYW1lOiBzdHJpbmd8dW5kZWZpbmVkO1xuICAgIGlmICh0eXBlID09PSB0aGlzLmNvbXBvbmVudCAmJiB0aGlzLmlzSG9zdENvbXBvbmVudCkge1xuICAgICAgdmFyTmFtZSA9IERZTkFNSUNfVkFSX05BTUU7XG4gICAgfSBlbHNlIGlmICh0eXBlIGluc3RhbmNlb2YgU3RhdGljU3ltYm9sKSB7XG4gICAgICB2YXJOYW1lID0gdGhpcy5leHRlcm5hbFJlZmVyZW5jZVZhcnMuZ2V0KHR5cGUpO1xuICAgIH0gZWxzZSB7XG4gICAgICB2YXJOYW1lID0gRFlOQU1JQ19WQVJfTkFNRTtcbiAgICB9XG4gICAgaWYgKCF2YXJOYW1lKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICAgYElsbGVnYWwgU3RhdGU6IHJlZmVycmluZyB0byBhIHR5cGUgd2l0aG91dCBhIHZhcmlhYmxlICR7SlNPTi5zdHJpbmdpZnkodHlwZSl9YCk7XG4gICAgfVxuICAgIHJldHVybiB2YXJOYW1lO1xuICB9XG5cbiAgcHJpdmF0ZSBnZXRUeXBlR3VhcmRFeHByZXNzaW9ucyhhc3Q6IEVtYmVkZGVkVGVtcGxhdGVBc3QpOiBHdWFyZEV4cHJlc3Npb25bXSB7XG4gICAgY29uc3QgcmVzdWx0ID0gWy4uLnRoaXMuZ3VhcmRzXTtcbiAgICBmb3IgKGxldCBkaXJlY3RpdmUgb2YgYXN0LmRpcmVjdGl2ZXMpIHtcbiAgICAgIGZvciAobGV0IGlucHV0IG9mIGRpcmVjdGl2ZS5pbnB1dHMpIHtcbiAgICAgICAgY29uc3QgZ3VhcmQgPSBkaXJlY3RpdmUuZGlyZWN0aXZlLmd1YXJkc1tpbnB1dC5kaXJlY3RpdmVOYW1lXTtcbiAgICAgICAgaWYgKGd1YXJkKSB7XG4gICAgICAgICAgY29uc3QgdXNlSWYgPSBndWFyZCA9PT0gJ1VzZUlmJztcbiAgICAgICAgICByZXN1bHQucHVzaCh7XG4gICAgICAgICAgICBndWFyZCxcbiAgICAgICAgICAgIHVzZUlmLFxuICAgICAgICAgICAgZXhwcmVzc2lvbjoge1xuICAgICAgICAgICAgICBjb250ZXh0OiB0aGlzLmNvbXBvbmVudCxcbiAgICAgICAgICAgICAgdmFsdWU6IGlucHV0LnZhbHVlLFxuICAgICAgICAgICAgICBzb3VyY2VTcGFuOiBpbnB1dC5zb3VyY2VTcGFuLFxuICAgICAgICAgICAgfSxcbiAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gcmVzdWx0O1xuICB9XG5cbiAgdmlzaXRBbGwodmFyaWFibGVzOiBWYXJpYWJsZUFzdFtdLCBhc3ROb2RlczogVGVtcGxhdGVBc3RbXSkge1xuICAgIHRoaXMudmFyaWFibGVzID0gdmFyaWFibGVzO1xuICAgIHRlbXBsYXRlVmlzaXRBbGwodGhpcywgYXN0Tm9kZXMpO1xuICB9XG5cbiAgYnVpbGQoY29tcG9uZW50SWQ6IHN0cmluZywgdGFyZ2V0U3RhdGVtZW50czogby5TdGF0ZW1lbnRbXSA9IFtdKTogby5TdGF0ZW1lbnRbXSB7XG4gICAgdGhpcy5jaGlsZHJlbi5mb3JFYWNoKChjaGlsZCkgPT4gY2hpbGQuYnVpbGQoY29tcG9uZW50SWQsIHRhcmdldFN0YXRlbWVudHMpKTtcbiAgICBsZXQgdmlld1N0bXRzOiBvLlN0YXRlbWVudFtdID1cbiAgICAgICAgW28udmFyaWFibGUoRFlOQU1JQ19WQVJfTkFNRSkuc2V0KG8uTlVMTF9FWFBSKS50b0RlY2xTdG10KG8uRFlOQU1JQ19UWVBFKV07XG4gICAgbGV0IGJpbmRpbmdDb3VudCA9IDA7XG4gICAgdGhpcy51cGRhdGVzLmZvckVhY2goKGV4cHJlc3Npb24pID0+IHtcbiAgICAgIGNvbnN0IHtzb3VyY2VTcGFuLCBjb250ZXh0LCB2YWx1ZX0gPSB0aGlzLnByZXByb2Nlc3NVcGRhdGVFeHByZXNzaW9uKGV4cHJlc3Npb24pO1xuICAgICAgY29uc3QgYmluZGluZ0lkID0gYCR7YmluZGluZ0NvdW50Kyt9YDtcbiAgICAgIGNvbnN0IG5hbWVSZXNvbHZlciA9IGNvbnRleHQgPT09IHRoaXMuY29tcG9uZW50ID8gdGhpcyA6IGRlZmF1bHRSZXNvbHZlcjtcbiAgICAgIGNvbnN0IHtzdG10cywgY3VyclZhbEV4cHJ9ID0gY29udmVydFByb3BlcnR5QmluZGluZyhcbiAgICAgICAgICBuYW1lUmVzb2x2ZXIsIG8udmFyaWFibGUodGhpcy5nZXRPdXRwdXRWYXIoY29udGV4dCkpLCB2YWx1ZSwgYmluZGluZ0lkLFxuICAgICAgICAgIEJpbmRpbmdGb3JtLkdlbmVyYWwpO1xuICAgICAgc3RtdHMucHVzaChuZXcgby5FeHByZXNzaW9uU3RhdGVtZW50KGN1cnJWYWxFeHByKSk7XG4gICAgICB2aWV3U3RtdHMucHVzaCguLi5zdG10cy5tYXAoXG4gICAgICAgICAgKHN0bXQ6IG8uU3RhdGVtZW50KSA9PiBvLmFwcGx5U291cmNlU3BhblRvU3RhdGVtZW50SWZOZWVkZWQoc3RtdCwgc291cmNlU3BhbikpKTtcbiAgICB9KTtcblxuICAgIHRoaXMuYWN0aW9ucy5mb3JFYWNoKCh7c291cmNlU3BhbiwgY29udGV4dCwgdmFsdWV9KSA9PiB7XG4gICAgICBjb25zdCBiaW5kaW5nSWQgPSBgJHtiaW5kaW5nQ291bnQrK31gO1xuICAgICAgY29uc3QgbmFtZVJlc29sdmVyID0gY29udGV4dCA9PT0gdGhpcy5jb21wb25lbnQgPyB0aGlzIDogZGVmYXVsdFJlc29sdmVyO1xuICAgICAgY29uc3Qge3N0bXRzfSA9IGNvbnZlcnRBY3Rpb25CaW5kaW5nKFxuICAgICAgICAgIG5hbWVSZXNvbHZlciwgby52YXJpYWJsZSh0aGlzLmdldE91dHB1dFZhcihjb250ZXh0KSksIHZhbHVlLCBiaW5kaW5nSWQpO1xuICAgICAgdmlld1N0bXRzLnB1c2goLi4uc3RtdHMubWFwKFxuICAgICAgICAgIChzdG10OiBvLlN0YXRlbWVudCkgPT4gby5hcHBseVNvdXJjZVNwYW5Ub1N0YXRlbWVudElmTmVlZGVkKHN0bXQsIHNvdXJjZVNwYW4pKSk7XG4gICAgfSk7XG5cbiAgICBpZiAodGhpcy5ndWFyZHMubGVuZ3RoKSB7XG4gICAgICBsZXQgZ3VhcmRFeHByZXNzaW9uOiBvLkV4cHJlc3Npb258dW5kZWZpbmVkID0gdW5kZWZpbmVkO1xuICAgICAgZm9yIChjb25zdCBndWFyZCBvZiB0aGlzLmd1YXJkcykge1xuICAgICAgICBjb25zdCB7Y29udGV4dCwgdmFsdWV9ID0gdGhpcy5wcmVwcm9jZXNzVXBkYXRlRXhwcmVzc2lvbihndWFyZC5leHByZXNzaW9uKTtcbiAgICAgICAgY29uc3QgYmluZGluZ0lkID0gYCR7YmluZGluZ0NvdW50Kyt9YDtcbiAgICAgICAgY29uc3QgbmFtZVJlc29sdmVyID0gY29udGV4dCA9PT0gdGhpcy5jb21wb25lbnQgPyB0aGlzIDogZGVmYXVsdFJlc29sdmVyO1xuICAgICAgICAvLyBXZSBvbmx5IHN1cHBvcnQgc3VwcG9ydCBzaW1wbGUgZXhwcmVzc2lvbnMgYW5kIGlnbm9yZSBvdGhlcnMgYXMgdGhleVxuICAgICAgICAvLyBhcmUgdW5saWtlbHkgdG8gYWZmZWN0IHR5cGUgbmFycm93aW5nLlxuICAgICAgICBjb25zdCB7c3RtdHMsIGN1cnJWYWxFeHByfSA9IGNvbnZlcnRQcm9wZXJ0eUJpbmRpbmcoXG4gICAgICAgICAgICBuYW1lUmVzb2x2ZXIsIG8udmFyaWFibGUodGhpcy5nZXRPdXRwdXRWYXIoY29udGV4dCkpLCB2YWx1ZSwgYmluZGluZ0lkLFxuICAgICAgICAgICAgQmluZGluZ0Zvcm0uVHJ5U2ltcGxlKTtcbiAgICAgICAgaWYgKHN0bXRzLmxlbmd0aCA9PSAwKSB7XG4gICAgICAgICAgY29uc3QgZ3VhcmRDbGF1c2UgPVxuICAgICAgICAgICAgICBndWFyZC51c2VJZiA/IGN1cnJWYWxFeHByIDogdGhpcy5jdHguaW1wb3J0RXhwcihndWFyZC5ndWFyZCkuY2FsbEZuKFtjdXJyVmFsRXhwcl0pO1xuICAgICAgICAgIGd1YXJkRXhwcmVzc2lvbiA9IGd1YXJkRXhwcmVzc2lvbiA/IGd1YXJkRXhwcmVzc2lvbi5hbmQoZ3VhcmRDbGF1c2UpIDogZ3VhcmRDbGF1c2U7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGlmIChndWFyZEV4cHJlc3Npb24pIHtcbiAgICAgICAgdmlld1N0bXRzID0gW25ldyBvLklmU3RtdChndWFyZEV4cHJlc3Npb24sIHZpZXdTdG10cyldO1xuICAgICAgfVxuICAgIH1cblxuICAgIGNvbnN0IHZpZXdOYW1lID0gYF9WaWV3XyR7Y29tcG9uZW50SWR9XyR7dGhpcy5lbWJlZGRlZFZpZXdJbmRleH1gO1xuICAgIGNvbnN0IHZpZXdGYWN0b3J5ID0gbmV3IG8uRGVjbGFyZUZ1bmN0aW9uU3RtdCh2aWV3TmFtZSwgW10sIHZpZXdTdG10cyk7XG4gICAgdGFyZ2V0U3RhdGVtZW50cy5wdXNoKHZpZXdGYWN0b3J5KTtcbiAgICByZXR1cm4gdGFyZ2V0U3RhdGVtZW50cztcbiAgfVxuXG4gIHZpc2l0Qm91bmRUZXh0KGFzdDogQm91bmRUZXh0QXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIGNvbnN0IGFzdFdpdGhTb3VyY2UgPSA8QVNUV2l0aFNvdXJjZT5hc3QudmFsdWU7XG4gICAgY29uc3QgaW50ZXIgPSA8SW50ZXJwb2xhdGlvbj5hc3RXaXRoU291cmNlLmFzdDtcblxuICAgIGludGVyLmV4cHJlc3Npb25zLmZvckVhY2goXG4gICAgICAgIChleHByKSA9PlxuICAgICAgICAgICAgdGhpcy51cGRhdGVzLnB1c2goe2NvbnRleHQ6IHRoaXMuY29tcG9uZW50LCB2YWx1ZTogZXhwciwgc291cmNlU3BhbjogYXN0LnNvdXJjZVNwYW59KSk7XG4gIH1cblxuICB2aXNpdEVtYmVkZGVkVGVtcGxhdGUoYXN0OiBFbWJlZGRlZFRlbXBsYXRlQXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRFbGVtZW50T3JUZW1wbGF0ZShhc3QpO1xuICAgIC8vIE5vdGU6IFRoZSBvbGQgdmlldyBjb21waWxlciB1c2VkIHRvIHVzZSBhbiBgYW55YCB0eXBlXG4gICAgLy8gZm9yIHRoZSBjb250ZXh0IGluIGFueSBlbWJlZGRlZCB2aWV3LlxuICAgIC8vIFdlIGtlZXAgdGhpcyBiZWhhaXZvciBiZWhpbmQgYSBmbGFnIGZvciBub3cuXG4gICAgaWYgKHRoaXMub3B0aW9ucy5mdWxsVGVtcGxhdGVUeXBlQ2hlY2spIHtcbiAgICAgIC8vIEZpbmQgYW55IGFwcGxpY2FibGUgdHlwZSBndWFyZHMuIEZvciBleGFtcGxlLCBOZ0lmIGhhcyBhIHR5cGUgZ3VhcmQgb24gbmdJZlxuICAgICAgLy8gKHNlZSBOZ0lmLm5nSWZUeXBlR3VhcmQpIHRoYXQgY2FuIGJlIHVzZWQgdG8gaW5kaWNhdGUgdGhhdCBhIHRlbXBsYXRlIGlzIG9ubHlcbiAgICAgIC8vIHN0YW1wZWQgb3V0IGlmIG5nSWYgaXMgdHJ1dGh5IHNvIGFueSBiaW5kaW5ncyBpbiB0aGUgdGVtcGxhdGUgY2FuIGFzc3VtZSB0aGF0LFxuICAgICAgLy8gaWYgYSBudWxsYWJsZSB0eXBlIGlzIHVzZWQgZm9yIG5nSWYsIHRoYXQgZXhwcmVzc2lvbiBpcyBub3QgbnVsbCBvciB1bmRlZmluZWQuXG4gICAgICBjb25zdCBndWFyZHMgPSB0aGlzLmdldFR5cGVHdWFyZEV4cHJlc3Npb25zKGFzdCk7XG4gICAgICBjb25zdCBjaGlsZFZpc2l0b3IgPSB0aGlzLnZpZXdCdWlsZGVyRmFjdG9yeSh0aGlzLCBndWFyZHMpO1xuICAgICAgdGhpcy5jaGlsZHJlbi5wdXNoKGNoaWxkVmlzaXRvcik7XG4gICAgICBjaGlsZFZpc2l0b3IudmlzaXRBbGwoYXN0LnZhcmlhYmxlcywgYXN0LmNoaWxkcmVuKTtcbiAgICB9XG4gIH1cblxuICB2aXNpdEVsZW1lbnQoYXN0OiBFbGVtZW50QXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRFbGVtZW50T3JUZW1wbGF0ZShhc3QpO1xuXG4gICAgbGV0IGlucHV0RGVmczogby5FeHByZXNzaW9uW10gPSBbXTtcbiAgICBsZXQgdXBkYXRlUmVuZGVyZXJFeHByZXNzaW9uczogRXhwcmVzc2lvbltdID0gW107XG4gICAgbGV0IG91dHB1dERlZnM6IG8uRXhwcmVzc2lvbltdID0gW107XG4gICAgYXN0LmlucHV0cy5mb3JFYWNoKChpbnB1dEFzdCkgPT4ge1xuICAgICAgdGhpcy51cGRhdGVzLnB1c2goXG4gICAgICAgICAge2NvbnRleHQ6IHRoaXMuY29tcG9uZW50LCB2YWx1ZTogaW5wdXRBc3QudmFsdWUsIHNvdXJjZVNwYW46IGlucHV0QXN0LnNvdXJjZVNwYW59KTtcbiAgICB9KTtcblxuICAgIHRlbXBsYXRlVmlzaXRBbGwodGhpcywgYXN0LmNoaWxkcmVuKTtcbiAgfVxuXG4gIHByaXZhdGUgdmlzaXRFbGVtZW50T3JUZW1wbGF0ZShhc3Q6IHtcbiAgICBvdXRwdXRzOiBCb3VuZEV2ZW50QXN0W10sXG4gICAgZGlyZWN0aXZlczogRGlyZWN0aXZlQXN0W10sXG4gICAgcmVmZXJlbmNlczogUmVmZXJlbmNlQXN0W10sXG4gIH0pIHtcbiAgICBhc3QuZGlyZWN0aXZlcy5mb3JFYWNoKChkaXJBc3QpID0+IHtcbiAgICAgIHRoaXMudmlzaXREaXJlY3RpdmUoZGlyQXN0KTtcbiAgICB9KTtcblxuICAgIGFzdC5yZWZlcmVuY2VzLmZvckVhY2goKHJlZikgPT4ge1xuICAgICAgbGV0IG91dHB1dFZhclR5cGU6IE91dHB1dFZhclR5cGUgPSBudWxsITtcbiAgICAgIC8vIE5vdGU6IFRoZSBvbGQgdmlldyBjb21waWxlciB1c2VkIHRvIHVzZSBhbiBgYW55YCB0eXBlXG4gICAgICAvLyBmb3IgZGlyZWN0aXZlcyBleHBvc2VkIHZpYSBgZXhwb3J0QXNgLlxuICAgICAgLy8gV2Uga2VlcCB0aGlzIGJlaGFpdm9yIGJlaGluZCBhIGZsYWcgZm9yIG5vdy5cbiAgICAgIGlmIChyZWYudmFsdWUgJiYgcmVmLnZhbHVlLmlkZW50aWZpZXIgJiYgdGhpcy5vcHRpb25zLmZ1bGxUZW1wbGF0ZVR5cGVDaGVjaykge1xuICAgICAgICBvdXRwdXRWYXJUeXBlID0gcmVmLnZhbHVlLmlkZW50aWZpZXIucmVmZXJlbmNlO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgb3V0cHV0VmFyVHlwZSA9IG8uQnVpbHRpblR5cGVOYW1lLkR5bmFtaWM7XG4gICAgICB9XG4gICAgICB0aGlzLnJlZk91dHB1dFZhcnMuc2V0KHJlZi5uYW1lLCBvdXRwdXRWYXJUeXBlKTtcbiAgICB9KTtcbiAgICBhc3Qub3V0cHV0cy5mb3JFYWNoKChvdXRwdXRBc3QpID0+IHtcbiAgICAgIHRoaXMuYWN0aW9ucy5wdXNoKFxuICAgICAgICAgIHtjb250ZXh0OiB0aGlzLmNvbXBvbmVudCwgdmFsdWU6IG91dHB1dEFzdC5oYW5kbGVyLCBzb3VyY2VTcGFuOiBvdXRwdXRBc3Quc291cmNlU3Bhbn0pO1xuICAgIH0pO1xuICB9XG5cbiAgdmlzaXREaXJlY3RpdmUoZGlyQXN0OiBEaXJlY3RpdmVBc3QpIHtcbiAgICBjb25zdCBkaXJUeXBlID0gZGlyQXN0LmRpcmVjdGl2ZS50eXBlLnJlZmVyZW5jZTtcbiAgICBkaXJBc3QuaW5wdXRzLmZvckVhY2goXG4gICAgICAgIChpbnB1dCkgPT4gdGhpcy51cGRhdGVzLnB1c2goXG4gICAgICAgICAgICB7Y29udGV4dDogdGhpcy5jb21wb25lbnQsIHZhbHVlOiBpbnB1dC52YWx1ZSwgc291cmNlU3BhbjogaW5wdXQuc291cmNlU3Bhbn0pKTtcbiAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgIC8vIGZvciBleHByZXNzaW9ucyBpbiBob3N0IHByb3BlcnRpZXMgLyBldmVudHMuXG4gICAgLy8gV2Uga2VlcCB0aGlzIGJlaGFpdm9yIGJlaGluZCBhIGZsYWcgZm9yIG5vdy5cbiAgICBpZiAodGhpcy5vcHRpb25zLmZ1bGxUZW1wbGF0ZVR5cGVDaGVjaykge1xuICAgICAgZGlyQXN0Lmhvc3RQcm9wZXJ0aWVzLmZvckVhY2goXG4gICAgICAgICAgKGlucHV0QXN0KSA9PiB0aGlzLnVwZGF0ZXMucHVzaChcbiAgICAgICAgICAgICAge2NvbnRleHQ6IGRpclR5cGUsIHZhbHVlOiBpbnB1dEFzdC52YWx1ZSwgc291cmNlU3BhbjogaW5wdXRBc3Quc291cmNlU3Bhbn0pKTtcbiAgICAgIGRpckFzdC5ob3N0RXZlbnRzLmZvckVhY2goKGhvc3RFdmVudEFzdCkgPT4gdGhpcy5hY3Rpb25zLnB1c2goe1xuICAgICAgICBjb250ZXh0OiBkaXJUeXBlLFxuICAgICAgICB2YWx1ZTogaG9zdEV2ZW50QXN0LmhhbmRsZXIsXG4gICAgICAgIHNvdXJjZVNwYW46IGhvc3RFdmVudEFzdC5zb3VyY2VTcGFuXG4gICAgICB9KSk7XG4gICAgfVxuICB9XG5cbiAgbm90aWZ5SW1wbGljaXRSZWNlaXZlclVzZSgpOiB2b2lkIHt9XG4gIGdldExvY2FsKG5hbWU6IHN0cmluZyk6IG8uRXhwcmVzc2lvbnxudWxsIHtcbiAgICBpZiAobmFtZSA9PSBFdmVudEhhbmRsZXJWYXJzLmV2ZW50Lm5hbWUpIHtcbiAgICAgIHJldHVybiBvLnZhcmlhYmxlKHRoaXMuZ2V0T3V0cHV0VmFyKG8uQnVpbHRpblR5cGVOYW1lLkR5bmFtaWMpKTtcbiAgICB9XG4gICAgZm9yIChsZXQgY3VyckJ1aWxkZXI6IFZpZXdCdWlsZGVyfG51bGwgPSB0aGlzOyBjdXJyQnVpbGRlcjsgY3VyckJ1aWxkZXIgPSBjdXJyQnVpbGRlci5wYXJlbnQpIHtcbiAgICAgIGxldCBvdXRwdXRWYXJUeXBlOiBPdXRwdXRWYXJUeXBlfHVuZGVmaW5lZDtcbiAgICAgIC8vIGNoZWNrIHJlZmVyZW5jZXNcbiAgICAgIG91dHB1dFZhclR5cGUgPSBjdXJyQnVpbGRlci5yZWZPdXRwdXRWYXJzLmdldChuYW1lKTtcbiAgICAgIGlmIChvdXRwdXRWYXJUeXBlID09IG51bGwpIHtcbiAgICAgICAgLy8gY2hlY2sgdmFyaWFibGVzXG4gICAgICAgIGNvbnN0IHZhckFzdCA9IGN1cnJCdWlsZGVyLnZhcmlhYmxlcy5maW5kKCh2YXJBc3QpID0+IHZhckFzdC5uYW1lID09PSBuYW1lKTtcbiAgICAgICAgaWYgKHZhckFzdCkge1xuICAgICAgICAgIG91dHB1dFZhclR5cGUgPSBvLkJ1aWx0aW5UeXBlTmFtZS5EeW5hbWljO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBpZiAob3V0cHV0VmFyVHlwZSAhPSBudWxsKSB7XG4gICAgICAgIHJldHVybiBvLnZhcmlhYmxlKHRoaXMuZ2V0T3V0cHV0VmFyKG91dHB1dFZhclR5cGUpKTtcbiAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBwcml2YXRlIHBpcGVPdXRwdXRWYXIobmFtZTogc3RyaW5nKTogc3RyaW5nIHtcbiAgICBjb25zdCBwaXBlID0gdGhpcy5waXBlcy5nZXQobmFtZSk7XG4gICAgaWYgKCFwaXBlKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICAgYElsbGVnYWwgU3RhdGU6IENvdWxkIG5vdCBmaW5kIHBpcGUgJHtuYW1lfSBpbiB0ZW1wbGF0ZSBvZiAke3RoaXMuY29tcG9uZW50fWApO1xuICAgIH1cbiAgICByZXR1cm4gdGhpcy5nZXRPdXRwdXRWYXIocGlwZSk7XG4gIH1cblxuICBwcml2YXRlIHByZXByb2Nlc3NVcGRhdGVFeHByZXNzaW9uKGV4cHJlc3Npb246IEV4cHJlc3Npb24pOiBFeHByZXNzaW9uIHtcbiAgICByZXR1cm4ge1xuICAgICAgc291cmNlU3BhbjogZXhwcmVzc2lvbi5zb3VyY2VTcGFuLFxuICAgICAgY29udGV4dDogZXhwcmVzc2lvbi5jb250ZXh0LFxuICAgICAgdmFsdWU6IGNvbnZlcnRQcm9wZXJ0eUJpbmRpbmdCdWlsdGlucyhcbiAgICAgICAgICB7XG4gICAgICAgICAgICBjcmVhdGVMaXRlcmFsQXJyYXlDb252ZXJ0ZXI6IChhcmdDb3VudDogbnVtYmVyKSA9PiAoYXJnczogby5FeHByZXNzaW9uW10pID0+IHtcbiAgICAgICAgICAgICAgY29uc3QgYXJyID0gby5saXRlcmFsQXJyKGFyZ3MpO1xuICAgICAgICAgICAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgICAgICAgICAgICAvLyBmb3IgYXJyYXlzLlxuICAgICAgICAgICAgICByZXR1cm4gdGhpcy5vcHRpb25zLmZ1bGxUZW1wbGF0ZVR5cGVDaGVjayA/IGFyciA6IGFyci5jYXN0KG8uRFlOQU1JQ19UWVBFKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBjcmVhdGVMaXRlcmFsTWFwQ29udmVydGVyOiAoa2V5czoge2tleTogc3RyaW5nLCBxdW90ZWQ6IGJvb2xlYW59W10pID0+XG4gICAgICAgICAgICAgICAgKHZhbHVlczogby5FeHByZXNzaW9uW10pID0+IHtcbiAgICAgICAgICAgICAgICAgIGNvbnN0IGVudHJpZXMgPSBrZXlzLm1hcCgoaywgaSkgPT4gKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGtleTogay5rZXksXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZTogdmFsdWVzW2ldLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcXVvdGVkOiBrLnF1b3RlZCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KSk7XG4gICAgICAgICAgICAgICAgICBjb25zdCBtYXAgPSBvLmxpdGVyYWxNYXAoZW50cmllcyk7XG4gICAgICAgICAgICAgICAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgICAgICAgICAgICAgICAgLy8gZm9yIG1hcHMuXG4gICAgICAgICAgICAgICAgICByZXR1cm4gdGhpcy5vcHRpb25zLmZ1bGxUZW1wbGF0ZVR5cGVDaGVjayA/IG1hcCA6IG1hcC5jYXN0KG8uRFlOQU1JQ19UWVBFKTtcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgY3JlYXRlUGlwZUNvbnZlcnRlcjogKG5hbWU6IHN0cmluZywgYXJnQ291bnQ6IG51bWJlcikgPT4gKGFyZ3M6IG8uRXhwcmVzc2lvbltdKSA9PiB7XG4gICAgICAgICAgICAgIC8vIE5vdGU6IFRoZSBvbGQgdmlldyBjb21waWxlciB1c2VkIHRvIHVzZSBhbiBgYW55YCB0eXBlXG4gICAgICAgICAgICAgIC8vIGZvciBwaXBlcy5cbiAgICAgICAgICAgICAgY29uc3QgcGlwZUV4cHIgPSB0aGlzLm9wdGlvbnMuZnVsbFRlbXBsYXRlVHlwZUNoZWNrID9cbiAgICAgICAgICAgICAgICAgIG8udmFyaWFibGUodGhpcy5waXBlT3V0cHV0VmFyKG5hbWUpKSA6XG4gICAgICAgICAgICAgICAgICBvLnZhcmlhYmxlKHRoaXMuZ2V0T3V0cHV0VmFyKG8uQnVpbHRpblR5cGVOYW1lLkR5bmFtaWMpKTtcbiAgICAgICAgICAgICAgcmV0dXJuIHBpcGVFeHByLmNhbGxNZXRob2QoJ3RyYW5zZm9ybScsIGFyZ3MpO1xuICAgICAgICAgICAgfSxcbiAgICAgICAgICB9LFxuICAgICAgICAgIGV4cHJlc3Npb24udmFsdWUpXG4gICAgfTtcbiAgfVxuXG4gIHZpc2l0TmdDb250ZW50KGFzdDogTmdDb250ZW50QXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRUZXh0KGFzdDogVGV4dEFzdCwgY29udGV4dDogYW55KTogYW55IHt9XG4gIHZpc2l0RGlyZWN0aXZlUHJvcGVydHkoYXN0OiBCb3VuZERpcmVjdGl2ZVByb3BlcnR5QXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRSZWZlcmVuY2UoYXN0OiBSZWZlcmVuY2VBc3QsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdFZhcmlhYmxlKGFzdDogVmFyaWFibGVBc3QsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdEV2ZW50KGFzdDogQm91bmRFdmVudEFzdCwgY29udGV4dDogYW55KTogYW55IHt9XG4gIHZpc2l0RWxlbWVudFByb3BlcnR5KGFzdDogQm91bmRFbGVtZW50UHJvcGVydHlBc3QsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdEF0dHIoYXN0OiBBdHRyQXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbn1cbiJdfQ==