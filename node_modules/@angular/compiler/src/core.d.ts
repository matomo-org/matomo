/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export interface Inject {
    token: any;
}
export declare const createInject: MetadataFactory<Inject>;
export declare const createInjectionToken: MetadataFactory<object>;
export interface Attribute {
    attributeName: string;
}
export declare const createAttribute: MetadataFactory<Attribute>;
export declare const emitDistinctChangesOnlyDefaultValue = false;
export interface Query {
    descendants: boolean;
    first: boolean;
    read: any;
    isViewQuery: boolean;
    selector: any;
    static?: boolean;
    emitDistinctChangesOnly: boolean;
}
export declare const createContentChildren: MetadataFactory<Query>;
export declare const createContentChild: MetadataFactory<Query>;
export declare const createViewChildren: MetadataFactory<Query>;
export declare const createViewChild: MetadataFactory<Query>;
export interface Directive {
    selector?: string;
    inputs?: string[];
    outputs?: string[];
    host?: {
        [key: string]: string;
    };
    providers?: Provider[];
    exportAs?: string;
    queries?: {
        [key: string]: any;
    };
    guards?: {
        [key: string]: any;
    };
}
export declare const createDirective: MetadataFactory<Directive>;
export interface Component extends Directive {
    changeDetection?: ChangeDetectionStrategy;
    viewProviders?: Provider[];
    moduleId?: string;
    templateUrl?: string;
    template?: string;
    styleUrls?: string[];
    styles?: string[];
    animations?: any[];
    encapsulation?: ViewEncapsulation;
    interpolation?: [string, string];
    entryComponents?: Array<Type | any[]>;
    preserveWhitespaces?: boolean;
}
export declare enum ViewEncapsulation {
    Emulated = 0,
    None = 2,
    ShadowDom = 3
}
export declare enum ChangeDetectionStrategy {
    OnPush = 0,
    Default = 1
}
export declare const createComponent: MetadataFactory<Component>;
export interface Pipe {
    name: string;
    pure?: boolean;
}
export declare const createPipe: MetadataFactory<Pipe>;
export interface Input {
    bindingPropertyName?: string;
}
export declare const createInput: MetadataFactory<Input>;
export interface Output {
    bindingPropertyName?: string;
}
export declare const createOutput: MetadataFactory<Output>;
export interface HostBinding {
    hostPropertyName?: string;
}
export declare const createHostBinding: MetadataFactory<HostBinding>;
export interface HostListener {
    eventName?: string;
    args?: string[];
}
export declare const createHostListener: MetadataFactory<HostListener>;
export interface NgModule {
    providers?: Provider[];
    declarations?: Array<Type | any[]>;
    imports?: Array<Type | ModuleWithProviders | any[]>;
    exports?: Array<Type | any[]>;
    entryComponents?: Array<Type | any[]>;
    bootstrap?: Array<Type | any[]>;
    schemas?: Array<SchemaMetadata | any[]>;
    id?: string;
}
export declare const createNgModule: MetadataFactory<NgModule>;
export interface ModuleWithProviders {
    ngModule: Type;
    providers?: Provider[];
}
export interface Injectable {
    providedIn?: Type | 'root' | any;
    useClass?: Type | any;
    useExisting?: Type | any;
    useValue?: any;
    useFactory?: Type | any;
    deps?: Array<Type | any[]>;
}
export declare const createInjectable: MetadataFactory<Injectable>;
export interface SchemaMetadata {
    name: string;
}
export declare const CUSTOM_ELEMENTS_SCHEMA: SchemaMetadata;
export declare const NO_ERRORS_SCHEMA: SchemaMetadata;
export declare const createOptional: MetadataFactory<unknown>;
export declare const createSelf: MetadataFactory<unknown>;
export declare const createSkipSelf: MetadataFactory<unknown>;
export declare const createHost: MetadataFactory<unknown>;
export interface Type extends Function {
    new (...args: any[]): any;
}
export declare const Type: FunctionConstructor;
export declare enum SecurityContext {
    NONE = 0,
    HTML = 1,
    STYLE = 2,
    SCRIPT = 3,
    URL = 4,
    RESOURCE_URL = 5
}
export declare type Provider = any;
export declare const enum NodeFlags {
    None = 0,
    TypeElement = 1,
    TypeText = 2,
    ProjectedTemplate = 4,
    CatRenderNode = 3,
    TypeNgContent = 8,
    TypePipe = 16,
    TypePureArray = 32,
    TypePureObject = 64,
    TypePurePipe = 128,
    CatPureExpression = 224,
    TypeValueProvider = 256,
    TypeClassProvider = 512,
    TypeFactoryProvider = 1024,
    TypeUseExistingProvider = 2048,
    LazyProvider = 4096,
    PrivateProvider = 8192,
    TypeDirective = 16384,
    Component = 32768,
    CatProviderNoDirective = 3840,
    CatProvider = 20224,
    OnInit = 65536,
    OnDestroy = 131072,
    DoCheck = 262144,
    OnChanges = 524288,
    AfterContentInit = 1048576,
    AfterContentChecked = 2097152,
    AfterViewInit = 4194304,
    AfterViewChecked = 8388608,
    EmbeddedViews = 16777216,
    ComponentView = 33554432,
    TypeContentQuery = 67108864,
    TypeViewQuery = 134217728,
    StaticQuery = 268435456,
    DynamicQuery = 536870912,
    TypeModuleProvider = 1073741824,
    EmitDistinctChangesOnly = -2147483648,
    CatQuery = 201326592,
    Types = 201347067
}
export declare const enum DepFlags {
    None = 0,
    SkipSelf = 1,
    Optional = 2,
    Self = 4,
    Value = 8
}
/**
 * Injection flags for DI.
 */
export declare const enum InjectFlags {
    Default = 0,
    /**
     * Specifies that an injector should retrieve a dependency from any injector until reaching the
     * host element of the current component. (Only used with Element Injector)
     */
    Host = 1,
    /** Don't descend into ancestors of the node requesting injection. */
    Self = 2,
    /** Skip the node that is requesting injection. */
    SkipSelf = 4,
    /** Inject `defaultValue` instead if token not found. */
    Optional = 8
}
export declare const enum ArgumentType {
    Inline = 0,
    Dynamic = 1
}
export declare const enum BindingFlags {
    TypeElementAttribute = 1,
    TypeElementClass = 2,
    TypeElementStyle = 4,
    TypeProperty = 8,
    SyntheticProperty = 16,
    SyntheticHostProperty = 32,
    CatSyntheticProperty = 48,
    Types = 15
}
export declare const enum QueryBindingType {
    First = 0,
    All = 1
}
export declare const enum QueryValueType {
    ElementRef = 0,
    RenderElement = 1,
    TemplateRef = 2,
    ViewContainerRef = 3,
    Provider = 4
}
export declare const enum ViewFlags {
    None = 0,
    OnPush = 2
}
export declare enum MissingTranslationStrategy {
    Error = 0,
    Warning = 1,
    Ignore = 2
}
export interface MetadataFactory<T> {
    (...args: any[]): T;
    isTypeOf(obj: any): obj is T;
    ngMetadataName: string;
}
export interface Route {
    children?: Route[];
    loadChildren?: string | Type | any;
}
/**
 * Flags used to generate R3-style CSS Selectors. They are pasted from
 * core/src/render3/projection.ts because they cannot be referenced directly.
 */
export declare const enum SelectorFlags {
    /** Indicates this is the beginning of a new negative selector */
    NOT = 1,
    /** Mode for matching attributes */
    ATTRIBUTE = 2,
    /** Mode for matching tag names */
    ELEMENT = 4,
    /** Mode for matching class names */
    CLASS = 8
}
export declare type R3CssSelector = (string | SelectorFlags)[];
export declare type R3CssSelectorList = R3CssSelector[];
export declare function parseSelectorToR3Selector(selector: string | null): R3CssSelectorList;
/**
 * Flags passed into template functions to determine which blocks (i.e. creation, update)
 * should be executed.
 *
 * Typically, a template runs both the creation block and the update block on initialization and
 * subsequent runs only execute the update block. However, dynamically created views require that
 * the creation block be executed separately from the update block (for backwards compat).
 */
export declare const enum RenderFlags {
    Create = 1,
    Update = 2
}
/**
 * A set of marker values to be used in the attributes arrays. These markers indicate that some
 * items are not regular attributes and the processing should be adapted accordingly.
 */
export declare const enum AttributeMarker {
    /**
     * Marker indicates that the following 3 values in the attributes array are:
     * namespaceUri, attributeName, attributeValue
     * in that order.
     */
    NamespaceURI = 0,
    /**
     * Signals class declaration.
     *
     * Each value following `Classes` designates a class name to include on the element.
     * ## Example:
     *
     * Given:
     * ```
     * <div class="foo bar baz">...<d/vi>
     * ```
     *
     * the generated code is:
     * ```
     * var _c1 = [AttributeMarker.Classes, 'foo', 'bar', 'baz'];
     * ```
     */
    Classes = 1,
    /**
     * Signals style declaration.
     *
     * Each pair of values following `Styles` designates a style name and value to include on the
     * element.
     * ## Example:
     *
     * Given:
     * ```
     * <div style="width:100px; height:200px; color:red">...</div>
     * ```
     *
     * the generated code is:
     * ```
     * var _c1 = [AttributeMarker.Styles, 'width', '100px', 'height'. '200px', 'color', 'red'];
     * ```
     */
    Styles = 2,
    /**
     * Signals that the following attribute names were extracted from input or output bindings.
     *
     * For example, given the following HTML:
     *
     * ```
     * <div moo="car" [foo]="exp" (bar)="doSth()">
     * ```
     *
     * the generated code is:
     *
     * ```
     * var _c1 = ['moo', 'car', AttributeMarker.Bindings, 'foo', 'bar'];
     * ```
     */
    Bindings = 3,
    /**
     * Signals that the following attribute names were hoisted from an inline-template declaration.
     *
     * For example, given the following HTML:
     *
     * ```
     * <div *ngFor="let value of values; trackBy:trackBy" dirA [dirB]="value">
     * ```
     *
     * the generated code for the `template()` instruction would include:
     *
     * ```
     * ['dirA', '', AttributeMarker.Bindings, 'dirB', AttributeMarker.Template, 'ngFor', 'ngForOf',
     * 'ngForTrackBy', 'let-value']
     * ```
     *
     * while the generated code for the `element()` instruction inside the template function would
     * include:
     *
     * ```
     * ['dirA', '', AttributeMarker.Bindings, 'dirB']
     * ```
     */
    Template = 4,
    /**
     * Signals that the following attribute is `ngProjectAs` and its value is a parsed `CssSelector`.
     *
     * For example, given the following HTML:
     *
     * ```
     * <h1 attr="value" ngProjectAs="[title]">
     * ```
     *
     * the generated code for the `element()` instruction would include:
     *
     * ```
     * ['attr', 'value', AttributeMarker.ProjectAs, ['', 'title', '']]
     * ```
     */
    ProjectAs = 5,
    /**
     * Signals that the following attribute will be translated by runtime i18n
     *
     * For example, given the following HTML:
     *
     * ```
     * <div moo="car" foo="value" i18n-foo [bar]="binding" i18n-bar>
     * ```
     *
     * the generated code is:
     *
     * ```
     * var _c1 = ['moo', 'car', AttributeMarker.I18n, 'foo', 'bar'];
     */
    I18n = 6
}
