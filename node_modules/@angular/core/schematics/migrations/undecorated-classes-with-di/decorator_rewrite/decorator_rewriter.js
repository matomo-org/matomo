(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/decorator_rewriter", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/functions", "@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/import_rewrite_visitor"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DecoratorRewriter = void 0;
    const ts = require("typescript");
    const functions_1 = require("@angular/core/schematics/utils/typescript/functions");
    const import_rewrite_visitor_1 = require("@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/import_rewrite_visitor");
    /**
     * Class that can be used to copy decorators to a new location. The rewriter ensures that
     * identifiers and imports are rewritten to work in the new file location. Fields in a
     * decorator that cannot be cleanly copied will be copied with a comment explaining that
     * imports and identifiers need to be adjusted manually.
     */
    class DecoratorRewriter {
        constructor(importManager, typeChecker, evaluator, compiler) {
            this.importManager = importManager;
            this.typeChecker = typeChecker;
            this.evaluator = evaluator;
            this.compiler = compiler;
            this.previousSourceFile = null;
            this.newSourceFile = null;
            this.newProperties = [];
            this.nonCopyableProperties = [];
            this.importRewriterFactory = new import_rewrite_visitor_1.ImportRewriteTransformerFactory(this.importManager, this.typeChecker, this.compiler['_host']);
        }
        rewrite(ngDecorator, newSourceFile) {
            const decorator = ngDecorator.node;
            // Reset the previous state of the decorator rewriter.
            this.newProperties = [];
            this.nonCopyableProperties = [];
            this.newSourceFile = newSourceFile;
            this.previousSourceFile = decorator.getSourceFile();
            // If the decorator will be added to the same source file it currently
            // exists in, we don't need to rewrite any paths or add new imports.
            if (this.previousSourceFile === newSourceFile) {
                return this._createDecorator(decorator.expression);
            }
            const oldCallExpr = decorator.expression;
            if (!oldCallExpr.arguments.length) {
                // Re-use the original decorator if there are no arguments and nothing needs
                // to be sanitized or rewritten.
                return this._createDecorator(decorator.expression);
            }
            const metadata = functions_1.unwrapExpression(oldCallExpr.arguments[0]);
            if (!ts.isObjectLiteralExpression(metadata)) {
                // Re-use the original decorator as there is no metadata that can be sanitized.
                return this._createDecorator(decorator.expression);
            }
            metadata.properties.forEach(prop => {
                // We don't handle spread assignments, accessors or method declarations automatically
                // as it involves more advanced static analysis and these type of properties are not
                // picked up by ngc either.
                if (ts.isSpreadAssignment(prop) || ts.isAccessor(prop) || ts.isMethodDeclaration(prop)) {
                    this.nonCopyableProperties.push(prop);
                    return;
                }
                const sanitizedProp = this._sanitizeMetadataProperty(prop);
                if (sanitizedProp !== null) {
                    this.newProperties.push(sanitizedProp);
                }
                else {
                    this.nonCopyableProperties.push(prop);
                }
            });
            // In case there is at least one non-copyable property, we add a leading comment to
            // the first property assignment in order to ask the developer to manually manage
            // imports and do path rewriting for these properties.
            if (this.nonCopyableProperties.length !== 0) {
                ['The following fields were copied from the base class,',
                    'but could not be updated automatically to work in the',
                    'new file location. Please add any required imports for', 'the properties below:']
                    .forEach(text => ts.addSyntheticLeadingComment(this.nonCopyableProperties[0], ts.SyntaxKind.SingleLineCommentTrivia, ` ${text}`, true));
            }
            // Note that we don't update the decorator as we don't want to copy potential leading
            // comments of the decorator. This is necessary because otherwise comments from the
            // copied decorator end up describing the new class (which is not always correct).
            return this._createDecorator(ts.createCall(this.importManager.addImportToSourceFile(newSourceFile, ngDecorator.name, ngDecorator.moduleName), undefined, [ts.updateObjectLiteral(metadata, [...this.newProperties, ...this.nonCopyableProperties])]));
        }
        /** Creates a new decorator with the given expression. */
        _createDecorator(expr) {
            // Note that we don't update the decorator as we don't want to copy potential leading
            // comments of the decorator. This is necessary because otherwise comments from the
            // copied decorator end up describing the new class (which is not always correct).
            return ts.createDecorator(expr);
        }
        /**
         * Sanitizes a metadata property by ensuring that all contained identifiers
         * are imported in the target source file.
         */
        _sanitizeMetadataProperty(prop) {
            try {
                return ts
                    .transform(prop, [ctx => this.importRewriterFactory.create(ctx, this.newSourceFile)])
                    .transformed[0];
            }
            catch (e) {
                // If the error is for an unresolved identifier, we want to return "null" because
                // such object literal elements could be added to the non-copyable properties.
                if (e instanceof import_rewrite_visitor_1.UnresolvedIdentifierError) {
                    return null;
                }
                throw e;
            }
        }
    }
    exports.DecoratorRewriter = DecoratorRewriter;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGVjb3JhdG9yX3Jld3JpdGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRpL2RlY29yYXRvcl9yZXdyaXRlL2RlY29yYXRvcl9yZXdyaXRlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7SUFVQSxpQ0FBaUM7SUFJakMsbUZBQXFFO0lBRXJFLHFKQUFvRztJQUdwRzs7Ozs7T0FLRztJQUNILE1BQWEsaUJBQWlCO1FBVTVCLFlBQ1ksYUFBNEIsRUFBVSxXQUEyQixFQUNqRSxTQUEyQixFQUFVLFFBQXFCO1lBRDFELGtCQUFhLEdBQWIsYUFBYSxDQUFlO1lBQVUsZ0JBQVcsR0FBWCxXQUFXLENBQWdCO1lBQ2pFLGNBQVMsR0FBVCxTQUFTLENBQWtCO1lBQVUsYUFBUSxHQUFSLFFBQVEsQ0FBYTtZQVh0RSx1QkFBa0IsR0FBdUIsSUFBSSxDQUFDO1lBQzlDLGtCQUFhLEdBQXVCLElBQUksQ0FBQztZQUV6QyxrQkFBYSxHQUFrQyxFQUFFLENBQUM7WUFDbEQsMEJBQXFCLEdBQWtDLEVBQUUsQ0FBQztZQUVsRCwwQkFBcUIsR0FBRyxJQUFJLHdEQUErQixDQUMvRCxJQUFJLENBQUMsYUFBYSxFQUFFLElBQUksQ0FBQyxXQUFXLEVBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBSU8sQ0FBQztRQUUxRSxPQUFPLENBQUMsV0FBd0IsRUFBRSxhQUE0QjtZQUM1RCxNQUFNLFNBQVMsR0FBRyxXQUFXLENBQUMsSUFBSSxDQUFDO1lBRW5DLHNEQUFzRDtZQUN0RCxJQUFJLENBQUMsYUFBYSxHQUFHLEVBQUUsQ0FBQztZQUN4QixJQUFJLENBQUMscUJBQXFCLEdBQUcsRUFBRSxDQUFDO1lBQ2hDLElBQUksQ0FBQyxhQUFhLEdBQUcsYUFBYSxD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RixtRkFBbUY7WUFDbkYsa0ZBQWtGO1lBQ2xGLE9BQU8sRUFBRSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNsQyxDQUFDO1FBRUQ7OztXQUdHO1FBQ0sseUJBQXlCLENBQUMsSUFBaUM7WUFFakUsSUFBSTtnQkFDRixPQUFPLEVBQUU7cUJBQ0osU0FBUyxDQUFDLElBQUksRUFBRSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLHFCQUFxQixDQUFDLE1BQU0sQ0FBQyxHQUFHLEVBQUUsSUFBSSxDQUFDLGFBQWMsQ0FBQyxDQUFDLENBQUM7cUJBQ3JGLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQzthQUNyQjtZQUFDLE9BQU8sQ0FBQyxFQUFFO2dCQUNWLGlGQUFpRjtnQkFDakYsOEVBQThFO2dCQUM5RSxJQUFJLENBQUMsWUFBWSxrREFBeUIsRUFBRTtvQkFDMUMsT0FBTyxJQUFJLENBQUM7aUJBQ2I7Z0JBQ0QsTUFBTSxDQUFDLENBQUM7YUFDVDtRQUNILENBQUM7S0FDRjtJQTlHRCw4Q0E4R0MiLCJzb3VyY2VzQ29udGVudCI6WyJcbi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuaW1wb3J0IHtBb3RDb21waWxlcn0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXInO1xuaW1wb3J0IHtQYXJ0aWFsRXZhbHVhdG9yfSBmcm9tICdAYW5ndWxhci9jb21waWxlci1jbGkvc3JjL25ndHNjL3BhcnRpYWxfZXZhbHVhdG9yJztcbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge0ltcG9ydE1hbmFnZXJ9IGZyb20gJy4uLy4uLy4uL3V0aWxzL2ltcG9ydF9tYW5hZ2VyJztcbmltcG9ydCB7TmdEZWNvcmF0b3J9IGZyb20gJy4uLy4uLy4uL3V0aWxzL25nX2RlY29yYXRvcnMnO1xuaW1wb3J0IHt1bndyYXBFeHByZXNzaW9ufSBmcm9tICcuLi8uLi8uLi91dGlscy90eXBlc2NyaXB0L2Z1bmN0aW9ucyc7XG5cbmltcG9ydCB7SW1wb3J0UmV3cml0ZVRyYW5zZm9ybWVyRmFjdG9yeSwgVW5yZXNvbHZlZElkZW50aWZpZXJFcnJvcn0gZnJvbSAnLi9pbXBvcnRfcmV3cml0ZV92aXNpdG9yJztcblxuXG4vKipcbiAqIENsYXNzIHRoYXQgY2FuIGJlIHVzZWQgdG8gY29weSBkZWNvcmF0b3JzIHRvIGEgbmV3IGxvY2F0aW9uLiBUaGUgcmV3cml0ZXIgZW5zdXJlcyB0aGF0XG4gKiBpZGVudGlmaWVycyBhbmQgaW1wb3J0cyBhcmUgcmV3cml0dGVuIHRvIHdvcmsgaW4gdGhlIG5ldyBmaWxlIGxvY2F0aW9uLiBGaWVsZHMgaW4gYVxuICogZGVjb3JhdG9yIHRoYXQgY2Fubm90IGJlIGNsZWFubHkgY29waWVkIHdpbGwgYmUgY29waWVkIHdpdGggYSBjb21tZW50IGV4cGxhaW5pbmcgdGhhdFxuICogaW1wb3J0cyBhbmQgaWRlbnRpZmllcnMgbmVlZCB0byBiZSBhZGp1c3RlZCBtYW51YWxseS5cbiAqL1xuZXhwb3J0IGNsYXNzIERlY29yYXRvclJld3JpdGVyIHtcbiAgcHJldmlvdXNTb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlfG51bGwgPSBudWxsO1xuICBuZXdTb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlfG51bGwgPSBudWxsO1xuXG4gIG5ld1Byb3BlcnRpZXM6IHRzLk9iamVjdExpdGVyYWxFbGVtZW50TGlrZVtdID0gW107XG4gIG5vbkNvcHlhYmxlUHJvcGVydGllczogdHMuT2JqZWN0TGl0ZXJhbEVsZW1lbnRMaWtlW10gPSBbXTtcblxuICBwcml2YXRlIGltcG9ydFJld3JpdGVyRmFjdG9yeSA9IG5ldyBJbXBvcnRSZXdyaXRlVHJhbnNmb3JtZXJGYWN0b3J5KFxuICAgICAgdGhpcy5pbXBvcnRNYW5hZ2VyLCB0aGlzLnR5cGVDaGVja2VyLCB0aGlzLmNvbXBpbGVyWydfaG9zdCddKTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgaW1wb3J0TWFuYWdlcjogSW1wb3J0TWFuYWdlciwgcHJpdmF0ZSB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsXG4gICAgICBwcml2YXRlIGV2YWx1YXRvcjogUGFydGlhbEV2YWx1YXRvciwgcHJpdmF0ZSBjb21waWxlcjogQW90Q29tcGlsZXIpIHt9XG5cbiAgcmV3cml0ZShuZ0RlY29yYXRvcjogTmdEZWNvcmF0b3IsIG5ld1NvdXJjZUZpbGU6IHRzLlNvdXJjZUZpbGUpOiB0cy5EZWNvcmF0b3Ige1xuICAgIGNvbnN0IGRlY29yYXRvciA9IG5nRGVjb3JhdG9yLm5vZGU7XG5cbiAgICAvLyBSZXNldCB0aGUgcHJldmlvdXMgc3RhdGUgb2YgdGhlIGRlY29yYXRvciByZXdyaXRlci5cbiAgICB0aGlzLm5ld1Byb3BlcnRpZXMgPSBbXTtcbiAgICB0aGlzLm5vbkNvcHlhYmxlUHJvcGVydGllcyA9IFtdO1xuICAgIHRoaXMubmV3U291cmNlRmlsZSA9IG5ld1NvdXJjZUZpbGU7XG4gICAgdGhpcy5wcmV2aW91c1NvdXJjZUZpbGUgPSBkZWNvcmF0b3IuZ2V0U291cmNlRmlsZSgpO1xuXG4gICAgLy8gSWYgdGhlIGRlY29yYXRvciB3aWxsIGJlIGFkZGVkIHRvIHRoZSBzYW1lIHNvdXJjZSBmaWxlIGl0IGN1cnJlbnRseVxuICAgIC8vIGV4aXN0cyBpbiwgd2UgZG9uJ3QgbmVlZCB0byByZXdyaXRlIGFueSBwYXRocyBvciBhZGQgbmV3IGltcG9ydHMuXG4gICAgaWYgKHRoaXMucHJldmlvdXNTb3VyY2VGaWxlID09PSBuZXdTb3VyY2VGaWxlKSB7XG4gICAgICByZXR1cm4gdGhpcy5fY3JlYXRlRGVjb3JhdG9yKGRlY29yYXRvci5leHByZXNzaW9uKTtcbiAgICB9XG5cbiAgICBjb25zdCBvbGRDYWxsRXhwciA9IGRlY29yYXRvci5leHByZXNzaW9uO1xuXG4gICAgaWYgKCFvbGRDYWxsRXhwci5hcmd1bWVudHMubGVuZ3RoKSB7XG4gICAgICAvLyBSZS11c2UgdGhlIG9yaWdpbmFsIGRlY29yYXRvciBpZiB0aGVyZSBhcmUgbm8gYXJndW1lbnRzIGFuZCBub3RoaW5nIG5lZWRzXG4gICAgICAvLyB0byBiZSBzYW5pdGl6ZWQgb3IgcmV3cml0dGVuLlxuICAgICAgcmV0dXJuIHRoaXMuX2NyZWF0ZURlY29yYXRvcihkZWNvcmF0b3IuZXhwcmVzc2lvbik7XG4gICAgfVxuXG4gICAgY29uc3QgbWV0YWRhdGEgPSB1bndyYXBFeHByZXNzaW9uKG9sZENhbGxFeHByLmFyZ3VtZW50c1swXSk7XG4gICAgaWYgKCF0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG1ldGFkYXRhKSkge1xuICAgICAgLy8gUmUtdXNlIHRoZSBvcmlnaW5hbCBkZWNvcmF0b3IgYXMgdGhlcmUgaXMgbm8gbWV0YWRhdGEgdGhhdCBjYW4gYmUgc2FuaXRpemVkLlxuICAgICAgcmV0dXJuIHRoaXMuX2NyZWF0ZURlY29yYXRvcihkZWNvcmF0b3IuZXhwcmVzc2lvbik7XG4gICAgfVxuXG4gICAgbWV0YWRhdGEucHJvcGVydGllcy5mb3JFYWNoKHByb3AgPT4ge1xuICAgICAgLy8gV2UgZG9uJ3QgaGFuZGxlIHNwcmVhZCBhc3NpZ25tZW50cywgYWNjZXNzb3JzIG9yIG1ldGhvZCBkZWNsYXJhdGlvbnMgYXV0b21hdGljYWxseVxuICAgICAgLy8gYXMgaXQgaW52b2x2ZXMgbW9yZSBhZHZhbmNlZCBzdGF0aWMgYW5hbHlzaXMgYW5kIHRoZXNlIHR5cGUgb2YgcHJvcGVydGllcyBhcmUgbm90XG4gICAgICAvLyBwaWNrZWQgdXAgYnkgbmdjIGVpdGhlci5cbiAgICAgIGlmICh0cy5pc1NwcmVhZEFzc2lnbm1lbnQocHJvcCkgfHwgdHMuaXNBY2Nlc3Nvcihwcm9wKSB8fCB0cy5pc01ldGhvZERlY2xhcmF0aW9uKHByb3ApKSB7XG4gICAgICAgIHRoaXMubm9uQ29weWFibGVQcm9wZXJ0aWVzLnB1c2gocHJvcCk7XG4gICAgICAgIHJldHVybjtcbiAgICAgIH1cblxuICAgICAgY29uc3Qgc2FuaXRpemVkUHJvcCA9IHRoaXMuX3Nhbml0aXplTWV0YWRhdGFQcm9wZXJ0eShwcm9wKTtcbiAgICAgIGlmIChzYW5pdGl6ZWRQcm9wICE9PSBudWxsKSB7XG4gICAgICAgIHRoaXMubmV3UHJvcGVydGllcy5wdXNoKHNhbml0aXplZFByb3ApO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgdGhpcy5ub25Db3B5YWJsZVByb3BlcnRpZXMucHVzaChwcm9wKTtcbiAgICAgIH1cbiAgICB9KTtcblxuICAgIC8vIEluIGNhc2UgdGhlcmUgaXMgYXQgbGVhc3Qgb25lIG5vbi1jb3B5YWJsZSBwcm9wZXJ0eSwgd2UgYWRkIGEgbGVhZGluZyBjb21tZW50IHRvXG4gICAgLy8gdGhlIGZpcnN0IHByb3BlcnR5IGFzc2lnbm1lbnQgaW4gb3JkZXIgdG8gYXNrIHRoZSBkZXZlbG9wZXIgdG8gbWFudWFsbHkgbWFuYWdlXG4gICAgLy8gaW1wb3J0cyBhbmQgZG8gcGF0aCByZXdyaXRpbmcgZm9yIHRoZXNlIHByb3BlcnRpZXMuXG4gICAgaWYgKHRoaXMubm9uQ29weWFibGVQcm9wZXJ0aWVzLmxlbmd0aCAhPT0gMCkge1xuICAgICAgWydUaGUgZm9sbG93aW5nIGZpZWxkcyB3ZXJlIGNvcGllZCBmcm9tIHRoZSBiYXNlIGNsYXNzLCcsXG4gICAgICAgJ2J1dCBjb3VsZCBub3QgYmUgdXBkYXRlZCBhdXRvbWF0aWNhbGx5IHRvIHdvcmsgaW4gdGhlJyxcbiAgICAgICAnbmV3IGZpbGUgbG9jYXRpb24uIFBsZWFzZSBhZGQgYW55IHJlcXVpcmVkIGltcG9ydHMgZm9yJywgJ3RoZSBwcm9wZXJ0aWVzIGJlbG93OiddXG4gICAgICAgICAgLmZvckVhY2goXG4gICAgICAgICAgICAgIHRleHQgPT4gdHMuYWRkU3ludGhldGljTGVhZGluZ0NvbW1lbnQoXG4gICAgICAgICAgICAgICAgICB0aGlzLm5vbkNvcHlhYmxlUHJvcGVydGllc1swXSwgdHMuU3ludGF4S2luZC5TaW5nbGVMaW5lQ29tbWVudFRyaXZpYSwgYCAke3RleHR9YCxcbiAgICAgICAgICAgICAgICAgIHRydWUpKTtcbiAgICB9XG5cbiAgICAvLyBOb3RlIHRoYXQgd2UgZG9uJ3QgdXBkYXRlIHRoZSBkZWNvcmF0b3IgYXMgd2UgZG9uJ3Qgd2FudCB0byBjb3B5IHBvdGVudGlhbCBsZWFkaW5nXG4gICAgLy8gY29tbWVudHMgb2YgdGhlIGRlY29yYXRvci4gVGhpcyBpcyBuZWNlc3NhcnkgYmVjYXVzZSBvdGhlcndpc2UgY29tbWVudHMgZnJvbSB0aGVcbiAgICAvLyBjb3BpZWQgZGVjb3JhdG9yIGVuZCB1cCBkZXNjcmliaW5nIHRoZSBuZXcgY2xhc3MgKHdoaWNoIGlzIG5vdCBhbHdheXMgY29ycmVjdCkuXG4gICAgcmV0dXJuIHRoaXMuX2NyZWF0ZURlY29yYXRvcih0cy5jcmVhdGVDYWxsKFxuICAgICAgICB0aGlzLmltcG9ydE1hbmFnZXIuYWRkSW1wb3J0VG9Tb3VyY2VGaWxlKFxuICAgICAgICAgICAgbmV3U291cmNlRmlsZSwgbmdEZWNvcmF0b3IubmFtZSwgbmdEZWNvcmF0b3IubW9kdWxlTmFtZSksXG4gICAgICAgIHVuZGVmaW5lZCwgW3RzLnVwZGF0ZU9iamVjdExpdGVyYWwoXG4gICAgICAgICAgICAgICAgICAgICAgIG1ldGFkYXRhLCBbLi4udGhpcy5uZXdQcm9wZXJ0aWVzLCAuLi50aGlzLm5vbkNvcHlhYmxlUHJvcGVydGllc10pXSkpO1xuICB9XG5cbiAgLyoqIENyZWF0ZXMgYSBuZXcgZGVjb3JhdG9yIHdpdGggdGhlIGdpdmVuIGV4cHJlc3Npb24uICovXG4gIHByaXZhdGUgX2NyZWF0ZURlY29yYXRvcihleHByOiB0cy5FeHByZXNzaW9uKTogdHMuRGVjb3JhdG9yIHtcbiAgICAvLyBOb3RlIHRoYXQgd2UgZG9uJ3QgdXBkYXRlIHRoZSBkZWNvcmF0b3IgYXMgd2UgZG9uJ3Qgd2FudCB0byBjb3B5IHBvdGVudGlhbCBsZWFkaW5nXG4gICAgLy8gY29tbWVudHMgb2YgdGhlIGRlY29yYXRvci4gVGhpcyBpcyBuZWNlc3NhcnkgYmVjYXVzZSBvdGhlcndpc2UgY29tbWVudHMgZnJvbSB0aGVcbiAgICAvLyBjb3BpZWQgZGVjb3JhdG9yIGVuZCB1cCBkZXNjcmliaW5nIHRoZSBuZXcgY2xhc3MgKHdoaWNoIGlzIG5vdCBhbHdheXMgY29ycmVjdCkuXG4gICAgcmV0dXJuIHRzLmNyZWF0ZURlY29yYXRvcihleHByKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBTYW5pdGl6ZXMgYSBtZXRhZGF0YSBwcm9wZXJ0eSBieSBlbnN1cmluZyB0aGF0IGFsbCBjb250YWluZWQgaWRlbnRpZmllcnNcbiAgICogYXJlIGltcG9ydGVkIGluIHRoZSB0YXJnZXQgc291cmNlIGZpbGUuXG4gICAqL1xuICBwcml2YXRlIF9zYW5pdGl6ZU1ldGFkYXRhUHJvcGVydHkocHJvcDogdHMuT2JqZWN0TGl0ZXJhbEVsZW1lbnRMaWtlKTogdHMuT2JqZWN0TGl0ZXJhbEVsZW1lbnRMaWtlXG4gICAgICB8bnVsbCB7XG4gICAgdHJ5IHtcbiAgICAgIHJldHVybiB0c1xuICAgICAgICAgIC50cmFuc2Zvcm0ocHJvcCwgW2N0eCA9PiB0aGlzLmltcG9ydFJld3JpdGVyRmFjdG9yeS5jcmVhdGUoY3R4LCB0aGlzLm5ld1NvdXJjZUZpbGUhKV0pXG4gICAgICAgICAgLnRyYW5zZm9ybWVkWzBdO1xuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgIC8vIElmIHRoZSBlcnJvciBpcyBmb3IgYW4gdW5yZXNvbHZlZCBpZGVudGlmaWVyLCB3ZSB3YW50IHRvIHJldHVybiBcIm51bGxcIiBiZWNhdXNlXG4gICAgICAvLyBzdWNoIG9iamVjdCBsaXRlcmFsIGVsZW1lbnRzIGNvdWxkIGJlIGFkZGVkIHRvIHRoZSBub24tY29weWFibGUgcHJvcGVydGllcy5cbiAgICAgIGlmIChlIGluc3RhbmNlb2YgVW5yZXNvbHZlZElkZW50aWZpZXJFcnJvcikge1xuICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgIH1cbiAgICAgIHRocm93IGU7XG4gICAgfVxuICB9XG59XG4iXX0=