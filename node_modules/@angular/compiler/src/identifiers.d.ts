/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileTokenMetadata } from './compile_metadata';
import { CompileReflector } from './compile_reflector';
import * as o from './output/output_ast';
export declare class Identifiers {
    static ANALYZE_FOR_ENTRY_COMPONENTS: o.ExternalReference;
    static ElementRef: o.ExternalReference;
    static NgModuleRef: o.ExternalReference;
    static ViewContainerRef: o.ExternalReference;
    static ChangeDetectorRef: o.ExternalReference;
    static QueryList: o.ExternalReference;
    static TemplateRef: o.ExternalReference;
    static Renderer2: o.ExternalReference;
    static CodegenComponentFactoryResolver: o.ExternalReference;
    static ComponentFactoryResolver: o.ExternalReference;
    static ComponentFactory: o.ExternalReference;
    static ComponentRef: o.ExternalReference;
    static NgModuleFactory: o.ExternalReference;
    static createModuleFactory: o.ExternalReference;
    static moduleDef: o.ExternalReference;
    static moduleProviderDef: o.ExternalReference;
    static RegisterModuleFactoryFn: o.ExternalReference;
    static inject: o.ExternalReference;
    static directiveInject: o.ExternalReference;
    static INJECTOR: o.ExternalReference;
    static Injector: o.ExternalReference;
    static ɵɵdefineInjectable: o.ExternalReference;
    static InjectableDef: o.ExternalReference;
    static ViewEncapsulation: o.ExternalReference;
    static ChangeDetectionStrategy: o.ExternalReference;
    static SecurityContext: o.ExternalReference;
    static LOCALE_ID: o.ExternalReference;
    static TRANSLATIONS_FORMAT: o.ExternalReference;
    static inlineInterpolate: o.ExternalReference;
    static interpolate: o.ExternalReference;
    static EMPTY_ARRAY: o.ExternalReference;
    static EMPTY_MAP: o.ExternalReference;
    static Renderer: o.ExternalReference;
    static viewDef: o.ExternalReference;
    static elementDef: o.ExternalReference;
    static anchorDef: o.ExternalReference;
    static textDef: o.ExternalReference;
    static directiveDef: o.ExternalReference;
    static providerDef: o.ExternalReference;
    static queryDef: o.ExternalReference;
    static pureArrayDef: o.ExternalReference;
    static pureObjectDef: o.ExternalReference;
    static purePipeDef: o.ExternalReference;
    static pipeDef: o.ExternalReference;
    static nodeValue: o.ExternalReference;
    static ngContentDef: o.ExternalReference;
    static unwrapValue: o.ExternalReference;
    static createRendererType2: o.ExternalReference;
    static RendererType2: o.ExternalReference;
    static ViewDefinition: o.ExternalReference;
    static createComponentFactory: o.ExternalReference;
    static setClassMetadata: o.ExternalReference;
}
export declare function createTokenForReference(reference: any): CompileTokenMetadata;
export declare function createTokenForExternalReference(reflector: CompileReflector, reference: o.ExternalReference): CompileTokenMetadata;
