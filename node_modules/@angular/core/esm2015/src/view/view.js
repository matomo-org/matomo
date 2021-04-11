/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { checkAndUpdateElementDynamic, checkAndUpdateElementInline, createElement, listenToElementOutputs } from './element';
import { expressionChangedAfterItHasBeenCheckedError } from './errors';
import { appendNgContent } from './ng_content';
import { callLifecycleHooksChildrenFirst, checkAndUpdateDirectiveDynamic, checkAndUpdateDirectiveInline, createDirectiveInstance, createPipeInstance, createProviderInstance } from './provider';
import { checkAndUpdatePureExpressionDynamic, checkAndUpdatePureExpressionInline, createPureExpression } from './pure_expression';
import { checkAndUpdateQuery, createQuery } from './query';
import { createTemplateData, createViewContainerData } from './refs';
import { checkAndUpdateTextDynamic, checkAndUpdateTextInline, createText } from './text';
import { asElementData, asQueryList, asTextData, Services, shiftInitState } from './types';
import { checkBindingNoChanges, isComponentView, markParentViewsForCheckProjectedViews, NOOP, resolveDefinition, tokenKey } from './util';
import { detachProjectedView } from './view_attach';
export function viewDef(flags, nodes, updateDirectives, updateRenderer) {
    // clone nodes and set auto calculated values
    let viewBindingCount = 0;
    let viewDisposableCount = 0;
    let viewNodeFlags = 0;
    let viewRootNodeFlags = 0;
    let viewMatchedQueries = 0;
    let currentParent = null;
    let currentRenderParent = null;
    let currentElementHasPublicProviders = false;
    let currentElementHasPrivateProviders = false;
    let lastRenderRootNode = null;
    for (let i = 0; i < nodes.length; i++) {
        const node = nodes[i];
        node.nodeIndex = i;
        node.parent = currentParent;
        node.bindingIndex = viewBindingCount;
        node.outputIndex = viewDisposableCount;
        node.renderParent = currentRenderParent;
        viewNodeFlags |= node.flags;
        viewMatchedQueries |= node.matchedQueryIds;
        if (node.element) {
            const elDef = node.element;
            elDef.publicProviders =
                currentParent ? currentParent.element.publicProviders : Object.create(null);
            elDef.allProviders = elDef.publicProviders;
            // Note: We assume that all providers of an element are before any child element!
            currentElementHasPublicProviders = false;
            currentElementHasPrivateProviders = false;
            if (node.element.template) {
                viewMatchedQueries |= node.element.template.nodeMatchedQueries;
            }
        }
        validateNode(currentParent, node, nodes.length);
        viewBindingCount += node.bindings.length;
        viewDisposableCount += node.outputs.length;
        if (!currentRenderParent && (node.flags & 3 /* CatRenderNode */)) {
            lastRenderRootNode = node;
        }
        if (node.flags & 20224 /* CatProvider */) {
            if (!currentElementHasPublicProviders) {
                currentElementHasPublicProviders = true;
                // Use prototypical inheritance to not get O(n^2) complexity...
                currentParent.element.publicProviders =
                    Object.create(currentParent.element.publicProviders);
                currentParent.element.allProviders = currentParent.element.publicProviders;
            }
            const isPrivateService = (node.flags & 8192 /* PrivateProvider */) !== 0;
            const isComponent = (node.flags & 32768 /* Component */) !== 0;
            if (!isPrivateService || isComponent) {
                currentParent.element.publicProviders[tokenKey(node.provider.token)] = node;
            }
            else {
                if (!currentElementHasPrivateProviders) {
                    currentElementHasPrivateProviders = true;
                    // Use prototypical inheritance to not get O(n^2) complexity...
                    currentParent.element.allProviders =
                        Object.create(currentParent.element.publicProviders);
                }
                currentParent.element.allProviders[tokenKey(node.provider.token)] = node;
            }
            if (isComponent) {
                currentParent.element.componentProvider = node;
            }
        }
        if (currentParent) {
            currentParent.childFlags |= node.flags;
            currentParent.directChildFlags |= node.flags;
            currentParent.childMatchedQueries |= node.matchedQueryIds;
            if (node.element && node.element.template) {
                currentParent.childMatchedQueries |= node.element.template.nodeMatchedQueries;
            }
        }
        else {
            viewRootNodeFlags |= node.flags;
        }
        if (node.childCount > 0) {
            currentParent = node;
            if (!isNgContainer(node)) {
                currentRenderParent = node;
            }
        }
        else {
            // When the current node has no children, check if it is the last children of its parent.
            // When it is, propagate the flags up.
            // The loop is required because an element could be the last transitive children of several
            // elements. We loop to either the root or the highest opened element (= with remaining
            // children)
            while (currentParent && i === currentParent.nodeIndex + currentParent.childCount) {
                const newParent = currentParent.parent;
                if (newParent) {
                    newParent.childFlags |= currentParent.childFlags;
                    newParent.childMatchedQueries |= currentParent.childMatchedQueries;
                }
                currentParent = newParent;
                // We also need to update the render parent & account for ng-container
                if (currentParent && isNgContainer(currentParent)) {
                    currentRenderParent = currentParent.renderParent;
                }
                else {
                    currentRenderParent = currentParent;
                }
            }
        }
    }
    const handleEvent = (view, nodeIndex, eventName, event) => nodes[nodeIndex].element.handleEvent(view, eventName, event);
    return {
        // Will be filled later...
        factory: null,
        nodeFlags: viewNodeFlags,
        rootNodeFlags: viewRootNodeFlags,
        nodeMatchedQueries: viewMatchedQueries,
        flags,
        nodes: nodes,
        updateDirectives: updateDirectives || NOOP,
        updateRenderer: updateRenderer || NOOP,
        handleEvent,
        bindingCount: viewBindingCount,
        outputCount: viewDisposableCount,
        lastRenderRootNode
    };
}
function isNgContainer(node) {
    return (node.flags & 1 /* TypeElement */) !== 0 && node.element.name === null;
}
function validateNode(parent, node, nodeCount) {
    const template = node.element && node.element.template;
    if (template) {
        if (!template.lastRenderRootNode) {
            throw new Error(`Illegal State: Embedded templates without nodes are not allowed!`);
        }
        if (template.lastRenderRootNode &&
            template.lastRenderRootNode.flags & 16777216 /* EmbeddedViews */) {
            throw new Error(`Illegal State: Last root node of a template can't have embedded views, at index ${node.nodeIndex}!`);
        }
    }
    if (node.flags & 20224 /* CatProvider */) {
        const parentFlags = parent ? parent.flags : 0;
        if ((parentFlags & 1 /* TypeElement */) === 0) {
            throw new Error(`Illegal State: StaticProvider/Directive nodes need to be children of elements or anchors, at index ${node.nodeIndex}!`);
        }
    }
    if (node.query) {
        if (node.flags & 67108864 /* TypeContentQuery */ &&
            (!parent || (parent.flags & 16384 /* TypeDirective */) === 0)) {
            throw new Error(`Illegal State: Content Query nodes need to be children of directives, at index ${node.nodeIndex}!`);
        }
        if (node.flags & 134217728 /* TypeViewQuery */ && parent) {
            throw new Error(`Illegal State: View Query nodes have to be top level nodes, at index ${node.nodeIndex}!`);
        }
    }
    if (node.childCount) {
        const parentEnd = parent ? parent.nodeIndex + parent.childCount : nodeCount - 1;
        if (node.nodeIndex <= parentEnd && node.nodeIndex + node.childCount > parentEnd) {
            throw new Error(`Illegal State: childCount of node leads outside of parent, at index ${node.nodeIndex}!`);
        }
    }
}
export function createEmbeddedView(parent, anchorDef, viewDef, context) {
    // embedded views are seen as siblings to the anchor, so we need
    // to get the parent of the anchor and use it as parentIndex.
    const view = createView(parent.root, parent.renderer, parent, anchorDef, viewDef);
    initView(view, parent.component, context);
    createViewNodes(view);
    return view;
}
export function createRootView(root, def, context) {
    const view = createView(root, root.renderer, null, null, def);
    initView(view, context, context);
    createViewNodes(view);
    return view;
}
export function createComponentView(parentView, nodeDef, viewDef, hostElement) {
    const rendererType = nodeDef.element.componentRendererType;
    let compRenderer;
    if (!rendererType) {
        compRenderer = parentView.root.renderer;
    }
    else {
        compRenderer = parentView.root.rendererFactory.createRenderer(hostElement, rendererType);
    }
    return createView(parentView.root, compRenderer, parentView, nodeDef.element.componentProvider, viewDef);
}
function createView(root, renderer, parent, parentNodeDef, def) {
    const nodes = new Array(def.nodes.length);
    const disposables = def.outputCount ? new Array(def.outputCount) : null;
    const view = {
        def,
        parent,
        viewContainerParent: null,
        parentNodeDef,
        context: null,
        component: null,
        nodes,
        state: 13 /* CatInit */,
        root,
        renderer,
        oldValues: new Array(def.bindingCount),
        disposables,
        initIndex: -1
    };
    return view;
}
function initView(view, component, context) {
    view.component = component;
    view.context = context;
}
function createViewNodes(view) {
    let renderHost;
    if (isComponentView(view)) {
        const hostDef = view.parentNodeDef;
        renderHost = asElementData(view.parent, hostDef.parent.nodeIndex).renderElement;
    }
    const def = view.def;
    const nodes = view.nodes;
    for (let i = 0; i < def.nodes.length; i++) {
        const nodeDef = def.nodes[i];
        Services.setCurrentNode(view, i);
        let nodeData;
        switch (nodeDef.flags & 201347067 /* Types */) {
            case 1 /* TypeElement */:
                const el = createElement(view, renderHost, nodeDef);
                let componentView = undefined;
                if (nodeDef.flags & 33554432 /* ComponentView */) {
                    const compViewDef = resolveDefinition(nodeDef.element.componentView);
                    componentView = Services.createComponentView(view, nodeDef, compViewDef, el);
                }
                listenToElementOutputs(view, componentView, nodeDef, el);
                nodeData = {
                    renderElement: el,
                    componentView,
                    viewContainer: null,
                    template: nodeDef.element.template ? createTemplateData(view, nodeDef) : undefined
                };
                if (nodeDef.flags & 16777216 /* EmbeddedViews */) {
                    nodeData.viewContainer = createViewContainerData(view, nodeDef, nodeData);
                }
                break;
            case 2 /* TypeText */:
                nodeData = createText(view, renderHost, nodeDef);
                break;
            case 512 /* TypeClassProvider */:
            case 1024 /* TypeFactoryProvider */:
            case 2048 /* TypeUseExistingProvider */:
            case 256 /* TypeValueProvider */: {
                nodeData = nodes[i];
                if (!nodeData && !(nodeDef.flags & 4096 /* LazyProvider */)) {
                    const instance = createProviderInstance(view, nodeDef);
                    nodeData = { instance };
                }
                break;
            }
            case 16 /* TypePipe */: {
                const instance = createPipeInstance(view, nodeDef);
                nodeData = { instance };
                break;
            }
            case 16384 /* TypeDirective */: {
                nodeData = nodes[i];
                if (!nodeData) {
                    const instance = createDirectiveInstance(view, nodeDef);
                    nodeData = { instance };
                }
                if (nodeDef.flags & 32768 /* Component */) {
                    const compView = asElementData(view, nodeDef.parent.nodeIndex).componentView;
                    initView(compView, nodeData.instance, nodeData.instance);
                }
                break;
            }
            case 32 /* TypePureArray */:
            case 64 /* TypePureObject */:
            case 128 /* TypePurePipe */:
                nodeData = createPureExpression(view, nodeDef);
                break;
            case 67108864 /* TypeContentQuery */:
            case 134217728 /* TypeViewQuery */:
                nodeData = createQuery((nodeDef.flags & -2147483648 /* EmitDistinctChangesOnly */) ===
                    -2147483648 /* EmitDistinctChangesOnly */);
                break;
            case 8 /* TypeNgContent */:
                appendNgContent(view, renderHost, nodeDef);
                // no runtime data needed for NgContent...
                nodeData = undefined;
                break;
        }
        nodes[i] = nodeData;
    }
    // Create the ViewData.nodes of component views after we created everything else,
    // so that e.g. ng-content works
    execComponentViewsAction(view, ViewAction.CreateViewNodes);
    // fill static content and view queries
    execQueriesAction(view, 67108864 /* TypeContentQuery */ | 134217728 /* TypeViewQuery */, 268435456 /* StaticQuery */, 0 /* CheckAndUpdate */);
}
export function checkNoChangesView(view) {
    markProjectedViewsForCheck(view);
    Services.updateDirectives(view, 1 /* CheckNoChanges */);
    execEmbeddedViewsAction(view, ViewAction.CheckNoChanges);
    Services.updateRenderer(view, 1 /* CheckNoChanges */);
    execComponentViewsAction(view, ViewAction.CheckNoChanges);
    // Note: We don't check queries for changes as we didn't do this in v2.x.
    // TODO(tbosch): investigate if we can enable the check again in v5.x with a nicer error message.
    view.state &= ~(64 /* CheckProjectedViews */ | 32 /* CheckProjectedView */);
}
export function checkAndUpdateView(view) {
    if (view.state & 1 /* BeforeFirstCheck */) {
        view.state &= ~1 /* BeforeFirstCheck */;
        view.state |= 2 /* FirstCheck */;
    }
    else {
        view.state &= ~2 /* FirstCheck */;
    }
    shiftInitState(view, 0 /* InitState_BeforeInit */, 256 /* InitState_CallingOnInit */);
    markProjectedViewsForCheck(view);
    Services.updateDirectives(view, 0 /* CheckAndUpdate */);
    execEmbeddedViewsAction(view, ViewAction.CheckAndUpdate);
    execQueriesAction(view, 67108864 /* TypeContentQuery */, 536870912 /* DynamicQuery */, 0 /* CheckAndUpdate */);
    let callInit = shiftInitState(view, 256 /* InitState_CallingOnInit */, 512 /* InitState_CallingAfterContentInit */);
    callLifecycleHooksChildrenFirst(view, 2097152 /* AfterContentChecked */ | (callInit ? 1048576 /* AfterContentInit */ : 0));
    Services.updateRenderer(view, 0 /* CheckAndUpdate */);
    execComponentViewsAction(view, ViewAction.CheckAndUpdate);
    execQueriesAction(view, 134217728 /* TypeViewQuery */, 536870912 /* DynamicQuery */, 0 /* CheckAndUpdate */);
    callInit = shiftInitState(view, 512 /* InitState_CallingAfterContentInit */, 768 /* InitState_CallingAfterViewInit */);
    callLifecycleHooksChildrenFirst(view, 8388608 /* AfterViewChecked */ | (callInit ? 4194304 /* AfterViewInit */ : 0));
    if (view.def.flags & 2 /* OnPush */) {
        view.state &= ~8 /* ChecksEnabled */;
    }
    view.state &= ~(64 /* CheckProjectedViews */ | 32 /* CheckProjectedView */);
    shiftInitState(view, 768 /* InitState_CallingAfterViewInit */, 1024 /* InitState_AfterInit */);
}
export function checkAndUpdateNode(view, nodeDef, argStyle, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9) {
    if (argStyle === 0 /* Inline */) {
        return checkAndUpdateNodeInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
    }
    else {
        return checkAndUpdateNodeDynamic(view, nodeDef, v0);
    }
}
function markProjectedViewsForCheck(view) {
    const def = view.def;
    if (!(def.nodeFlags & 4 /* ProjectedTemplate */)) {
        return;
    }
    for (let i = 0; i < def.nodes.length; i++) {
        const nodeDef = def.nodes[i];
        if (nodeDef.flags & 4 /* ProjectedTemplate */) {
            const projectedViews = asElementData(view, i).template._projectedViews;
            if (projectedViews) {
                for (let i = 0; i < projectedViews.length; i++) {
                    const projectedView = projectedViews[i];
                    projectedView.state |= 32 /* CheckProjectedView */;
                    markParentViewsForCheckProjectedViews(projectedView, view);
                }
            }
        }
        else if ((nodeDef.childFlags & 4 /* ProjectedTemplate */) === 0) {
            // a parent with leafs
            // no child is a component,
            // then skip the children
            i += nodeDef.childCount;
        }
    }
}
function checkAndUpdateNodeInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9) {
    switch (nodeDef.flags & 201347067 /* Types */) {
        case 1 /* TypeElement */:
            return checkAndUpdateElementInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
        case 2 /* TypeText */:
            return checkAndUpdateTextInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
        case 16384 /* TypeDirective */:
            return checkAndUpdateDirectiveInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
        case 32 /* TypePureArray */:
        case 64 /* TypePureObject */:
        case 128 /* TypePurePipe */:
            return checkAndUpdatePureExpressionInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
        default:
            throw 'unreachable';
    }
}
function checkAndUpdateNodeDynamic(view, nodeDef, values) {
    switch (nodeDef.flags & 201347067 /* Types */) {
        case 1 /* TypeElement */:
            return checkAndUpdateElementDynamic(view, nodeDef, values);
        case 2 /* TypeText */:
            return checkAndUpdateTextDynamic(view, nodeDef, values);
        case 16384 /* TypeDirective */:
            return checkAndUpdateDirectiveDynamic(view, nodeDef, values);
        case 32 /* TypePureArray */:
        case 64 /* TypePureObject */:
        case 128 /* TypePurePipe */:
            return checkAndUpdatePureExpressionDynamic(view, nodeDef, values);
        default:
            throw 'unreachable';
    }
}
export function checkNoChangesNode(view, nodeDef, argStyle, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9) {
    if (argStyle === 0 /* Inline */) {
        checkNoChangesNodeInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9);
    }
    else {
        checkNoChangesNodeDynamic(view, nodeDef, v0);
    }
    // Returning false is ok here as we would have thrown in case of a change.
    return false;
}
function checkNoChangesNodeInline(view, nodeDef, v0, v1, v2, v3, v4, v5, v6, v7, v8, v9) {
    const bindLen = nodeDef.bindings.length;
    if (bindLen > 0)
        checkBindingNoChanges(view, nodeDef, 0, v0);
    if (bindLen > 1)
        checkBindingNoChanges(view, nodeDef, 1, v1);
    if (bindLen > 2)
        checkBindingNoChanges(view, nodeDef, 2, v2);
    if (bindLen > 3)
        checkBindingNoChanges(view, nodeDef, 3, v3);
    if (bindLen > 4)
        checkBindingNoChanges(view, nodeDef, 4, v4);
    if (bindLen > 5)
        checkBindingNoChanges(view, nodeDef, 5, v5);
    if (bindLen > 6)
        checkBindingNoChanges(view, nodeDef, 6, v6);
    if (bindLen > 7)
        checkBindingNoChanges(view, nodeDef, 7, v7);
    if (bindLen > 8)
        checkBindingNoChanges(view, nodeDef, 8, v8);
    if (bindLen > 9)
        checkBindingNoChanges(view, nodeDef, 9, v9);
}
function checkNoChangesNodeDynamic(view, nodeDef, values) {
    for (let i = 0; i < values.length; i++) {
        checkBindingNoChanges(view, nodeDef, i, values[i]);
    }
}
/**
 * Workaround https://github.com/angular/tsickle/issues/497
 * @suppress {misplacedTypeAnnotation}
 */
function checkNoChangesQuery(view, nodeDef) {
    const queryList = asQueryList(view, nodeDef.nodeIndex);
    if (queryList.dirty) {
        throw expressionChangedAfterItHasBeenCheckedError(Services.createDebugContext(view, nodeDef.nodeIndex), `Query ${nodeDef.query.id} not dirty`, `Query ${nodeDef.query.id} dirty`, (view.state & 1 /* BeforeFirstCheck */) !== 0);
    }
}
export function destroyView(view) {
    if (view.state & 128 /* Destroyed */) {
        return;
    }
    execEmbeddedViewsAction(view, ViewAction.Destroy);
    execComponentViewsAction(view, ViewAction.Destroy);
    callLifecycleHooksChildrenFirst(view, 131072 /* OnDestroy */);
    if (view.disposables) {
        for (let i = 0; i < view.disposables.length; i++) {
            view.disposables[i]();
        }
    }
    detachProjectedView(view);
    if (view.renderer.destroyNode) {
        destroyViewNodes(view);
    }
    if (isComponentView(view)) {
        view.renderer.destroy();
    }
    view.state |= 128 /* Destroyed */;
}
function destroyViewNodes(view) {
    const len = view.def.nodes.length;
    for (let i = 0; i < len; i++) {
        const def = view.def.nodes[i];
        if (def.flags & 1 /* TypeElement */) {
            view.renderer.destroyNode(asElementData(view, i).renderElement);
        }
        else if (def.flags & 2 /* TypeText */) {
            view.renderer.destroyNode(asTextData(view, i).renderText);
        }
        else if (def.flags & 67108864 /* TypeContentQuery */ || def.flags & 134217728 /* TypeViewQuery */) {
            asQueryList(view, i).destroy();
        }
    }
}
var ViewAction;
(function (ViewAction) {
    ViewAction[ViewAction["CreateViewNodes"] = 0] = "CreateViewNodes";
    ViewAction[ViewAction["CheckNoChanges"] = 1] = "CheckNoChanges";
    ViewAction[ViewAction["CheckNoChangesProjectedViews"] = 2] = "CheckNoChangesProjectedViews";
    ViewAction[ViewAction["CheckAndUpdate"] = 3] = "CheckAndUpdate";
    ViewAction[ViewAction["CheckAndUpdateProjectedViews"] = 4] = "CheckAndUpdateProjectedViews";
    ViewAction[ViewAction["Destroy"] = 5] = "Destroy";
})(ViewAction || (ViewAction = {}));
function execComponentViewsAction(view, action) {
    const def = view.def;
    if (!(def.nodeFlags & 33554432 /* ComponentView */)) {
        return;
    }
    for (let i = 0; i < def.nodes.length; i++) {
        const nodeDef = def.nodes[i];
        if (nodeDef.flags & 33554432 /* ComponentView */) {
            // a leaf
            callViewAction(asElementData(view, i).componentView, action);
        }
        else if ((nodeDef.childFlags & 33554432 /* ComponentView */) === 0) {
            // a parent with leafs
            // no child is a component,
            // then skip the children
            i += nodeDef.childCount;
        }
    }
}
function execEmbeddedViewsAction(view, action) {
    const def = view.def;
    if (!(def.nodeFlags & 16777216 /* EmbeddedViews */)) {
        return;
    }
    for (let i = 0; i < def.nodes.length; i++) {
        const nodeDef = def.nodes[i];
        if (nodeDef.flags & 16777216 /* EmbeddedViews */) {
            // a leaf
            const embeddedViews = asElementData(view, i).viewContainer._embeddedViews;
            for (let k = 0; k < embeddedViews.length; k++) {
                callViewAction(embeddedViews[k], action);
            }
        }
        else if ((nodeDef.childFlags & 16777216 /* EmbeddedViews */) === 0) {
            // a parent with leafs
            // no child is a component,
            // then skip the children
            i += nodeDef.childCount;
        }
    }
}
function callViewAction(view, action) {
    const viewState = view.state;
    switch (action) {
        case ViewAction.CheckNoChanges:
            if ((viewState & 128 /* Destroyed */) === 0) {
                if ((viewState & 12 /* CatDetectChanges */) === 12 /* CatDetectChanges */) {
                    checkNoChangesView(view);
                }
                else if (viewState & 64 /* CheckProjectedViews */) {
                    execProjectedViewsAction(view, ViewAction.CheckNoChangesProjectedViews);
                }
            }
            break;
        case ViewAction.CheckNoChangesProjectedViews:
            if ((viewState & 128 /* Destroyed */) === 0) {
                if (viewState & 32 /* CheckProjectedView */) {
                    checkNoChangesView(view);
                }
                else if (viewState & 64 /* CheckProjectedViews */) {
                    execProjectedViewsAction(view, action);
                }
            }
            break;
        case ViewAction.CheckAndUpdate:
            if ((viewState & 128 /* Destroyed */) === 0) {
                if ((viewState & 12 /* CatDetectChanges */) === 12 /* CatDetectChanges */) {
                    checkAndUpdateView(view);
                }
                else if (viewState & 64 /* CheckProjectedViews */) {
                    execProjectedViewsAction(view, ViewAction.CheckAndUpdateProjectedViews);
                }
            }
            break;
        case ViewAction.CheckAndUpdateProjectedViews:
            if ((viewState & 128 /* Destroyed */) === 0) {
                if (viewState & 32 /* CheckProjectedView */) {
                    checkAndUpdateView(view);
                }
                else if (viewState & 64 /* CheckProjectedViews */) {
                    execProjectedViewsAction(view, action);
                }
            }
            break;
        case ViewAction.Destroy:
            // Note: destroyView recurses over all views,
            // so we don't need to special case projected views here.
            destroyView(view);
            break;
        case ViewAction.CreateViewNodes:
            createViewNodes(view);
            break;
    }
}
function execProjectedViewsAction(view, action) {
    execEmbeddedViewsAction(view, action);
    execComponentViewsAction(view, action);
}
function execQueriesAction(view, queryFlags, staticDynamicQueryFlag, checkType) {
    if (!(view.def.nodeFlags & queryFlags) || !(view.def.nodeFlags & staticDynamicQueryFlag)) {
        return;
    }
    const nodeCount = view.def.nodes.length;
    for (let i = 0; i < nodeCount; i++) {
        const nodeDef = view.def.nodes[i];
        if ((nodeDef.flags & queryFlags) && (nodeDef.flags & staticDynamicQueryFlag)) {
            Services.setCurrentNode(view, nodeDef.nodeIndex);
            switch (checkType) {
                case 0 /* CheckAndUpdate */:
                    checkAndUpdateQuery(view, nodeDef);
                    break;
                case 1 /* CheckNoChanges */:
                    checkNoChangesQuery(view, nodeDef);
                    break;
            }
        }
        if (!(nodeDef.childFlags & queryFlags) || !(nodeDef.childFlags & staticDynamicQueryFlag)) {
            // no child has a matching query
            // then skip the children
            i += nodeDef.childCount;
        }
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidmlldy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3ZpZXcvdmlldy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFJSCxPQUFPLEVBQUMsNEJBQTRCLEVBQUUsMkJBQTJCLEVBQUUsYUFBYSxFQUFFLHNCQUFzQixFQUFDLE1BQU0sV0FBVyxDQUFDO0FBQzNILE9BQU8sRUFBQywyQ0FBMkMsRUFBQyxNQUFNLFVBQVUsQ0FBQztBQUNyRSxPQUFPLEVBQUMsZUFBZSxFQUFDLE1BQU0sY0FBYyxDQUFDO0FBQzdDLE9BQU8sRUFBQywrQkFBK0IsRUFBRSw4QkFBOEIsRUFBRSw2QkFBNkIsRUFBRSx1QkFBdUIsRUFBRSxrQkFBa0IsRUFBRSxzQkFBc0IsRUFBQyxNQUFNLFlBQVksQ0FBQztBQUMvTCxPQUFPLEVBQUMsbUNBQW1DLEVBQUUsa0NBQWtDLEVBQUUsb0JBQW9CLEVBQUMsTUFBTSxtQkFBbUIsQ0FBQztBQUNoSSxPQUFPLEVBQUMsbUJBQW1CLEVBQUUsV0FBVyxFQUFDLE1BQU0sU0FBUyxDQUFDO0FBQ3pELE9BQU8sRUFBQyxrQkFBa0IsRUFBRSx1QkFBdUIsRUFBQyxNQUFNLFFBQVEsQ0FBQztBQUNuRSxPQUFPLEVBQUMseUJBQXlCLEVBQUUsd0JBQXdCLEVBQUUsVUFBVSxFQUFDLE1BQU0sUUFBUSxDQUFDO0FBQ3ZGLE9BQU8sRUFBZSxhQUFhLEVBQUUsV0FBVyxFQUFFLFVBQVUsRUFBZ0YsUUFBUSxFQUFFLGNBQWMsRUFBa0YsTUFBTSxTQUFTLENBQUM7QUFDdFEsT0FBTyxFQUFDLHFCQUFxQixFQUFFLGVBQWUsRUFBRSxxQ0FBcUMsRUFBRSxJQUFJLEVBQUUsaUJBQWlCLEVBQUUsUUFBUSxFQUFDLE1BQU0sUUFBUSxDQUFDO0FBQ3hJLE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUVsRCxNQUFNLFVBQVUsT0FBTyxD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QyxTQUFTLEdBQUcsc0JBQXNCLENBQUMsRUFBRTtRQUN4RixPQUFPO0tBQ1I7SUFDRCxNQUFNLFNBQVMsR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUM7SUFDeEMsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLFNBQVMsRUFBRSxDQUFDLEVBQUUsRUFBRTtRQUNsQyxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUNsQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssR0FBRyxVQUFVLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxLQUFLLEdBQUcsc0JBQXNCLENBQUMsRUFBRTtZQUM1RSxRQUFRLENBQUMsY0FBYyxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsU0FBUyxDQUFDLENBQUM7WUFDakQsUUFBUSxTQUFTLEVBQUU7Z0JBQ2pCO29CQUNFLG1CQUFtQixDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsQ0FBQztvQkFDbkMsTUFBTTtnQkFDUjtvQkFDRSxtQkFBbUIsQ0FBQyxJQUFJLEVBQUUsT0FBTyxDQUFDLENBQUM7b0JBQ25DLE1BQU07YUFDVDtTQUNGO1FBQ0QsSUFBSSxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQVUsR0FBRyxVQUFVLENBQUMsSUFBSSxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQVUsR0FBRyxzQkFBc0IsQ0FBQyxFQUFFO1lBQ3hGLGdDQUFnQztZQUNoQyx5QkFBeUI7WUFDekIsQ0FBQyxJQUFJLE9BQU8sQ0FBQyxVQUFVLENBQUM7U0FDekI7S0FDRjtBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtSZW5kZXJlcjJ9IGZyb20gJy4uL3JlbmRlci9hcGknO1xuXG5pbXBvcnQge2NoZWNrQW5kVXBkYXRlRWxlbWVudER5bmFtaWMsIGNoZWNrQW5kVXBkYXRlRWxlbWVudElubGluZSwgY3JlYXRlRWxlbWVudCwgbGlzdGVuVG9FbGVtZW50T3V0cHV0c30gZnJvbSAnLi9lbGVtZW50JztcbmltcG9ydCB7ZXhwcmVzc2lvbkNoYW5nZWRBZnRlckl0SGFzQmVlbkNoZWNrZWRFcnJvcn0gZnJvbSAnLi9lcnJvcnMnO1xuaW1wb3J0IHthcHBlbmROZ0NvbnRlbnR9IGZyb20gJy4vbmdfY29udGVudCc7XG5pbXBvcnQge2NhbGxMaWZlY3ljbGVIb29rc0NoaWxkcmVuRmlyc3QsIGNoZWNrQW5kVXBkYXRlRGlyZWN0aXZlRHluYW1pYywgY2hlY2tBbmRVcGRhdGVEaXJlY3RpdmVJbmxpbmUsIGNyZWF0ZURpcmVjdGl2ZUluc3RhbmNlLCBjcmVhdGVQaXBlSW5zdGFuY2UsIGNyZWF0ZVByb3ZpZGVySW5zdGFuY2V9IGZyb20gJy4vcHJvdmlkZXInO1xuaW1wb3J0IHtjaGVja0FuZFVwZGF0ZVB1cmVFeHByZXNzaW9uRHluYW1pYywgY2hlY2tBbmRVcGRhdGVQdXJlRXhwcmVzc2lvbklubGluZSwgY3JlYXRlUHVyZUV4cHJlc3Npb259IGZyb20gJy4vcHVyZV9leHByZXNzaW9uJztcbmltcG9ydCB7Y2hlY2tBbmRVcGRhdGVRdWVyeSwgY3JlYXRlUXVlcnl9IGZyb20gJy4vcXVlcnknO1xuaW1wb3J0IHtjcmVhdGVUZW1wbGF0ZURhdGEsIGNyZWF0ZVZpZXdDb250YWluZXJEYXRhfSBmcm9tICcuL3JlZnMnO1xuaW1wb3J0IHtjaGVja0FuZFVwZGF0ZVRleHREeW5hbWljLCBjaGVja0FuZFVwZGF0ZVRleHRJbmxpbmUsIGNyZWF0ZVRleHR9IGZyb20gJy4vdGV4dCc7XG5pbXBvcnQge0FyZ3VtZW50VHlwZSwgYXNFbGVtZW50RGF0YSwgYXNRdWVyeUxpc3QsIGFzVGV4dERhdGEsIENoZWNrVHlwZSwgRWxlbWVudERhdGEsIE5vZGVEYXRhLCBOb2RlRGVmLCBOb2RlRmxhZ3MsIFByb3ZpZGVyRGF0YSwgUm9vdERhdGEsIFNlcnZpY2VzLCBzaGlmdEluaXRTdGF0ZSwgVmlld0RhdGEsIFZpZXdEZWZpbml0aW9uLCBWaWV3RmxhZ3MsIFZpZXdIYW5kbGVFdmVudEZuLCBWaWV3U3RhdGUsIFZpZXdVcGRhdGVGbn0gZnJvbSAnLi90eXBlcyc7XG5pbXBvcnQge2NoZWNrQmluZGluZ05vQ2hhbmdlcywgaXNDb21wb25lbnRWaWV3LCBtYXJrUGFyZW50Vmlld3NGb3JDaGVja1Byb2plY3RlZFZpZXdzLCBOT09QLCByZXNvbHZlRGVmaW5pdGlvbiwgdG9rZW5LZXl9IGZyb20gJy4vdXRpbCc7XG5pbXBvcnQge2RldGFjaFByb2plY3RlZFZpZXd9IGZyb20gJy4vdmlld19hdHRhY2gnO1xuXG5leHBvcnQgZnVuY3Rpb24gdmlld0RlZihcbiAgICBmbGFnczogVmlld0ZsYWdzLCBub2RlczogTm9kZURlZltdLCB1cGRhdGVEaXJlY3RpdmVzPzogbnVsbHxWaWV3VXBkYXRlRm4sXG4gICAgdXBkYXRlUmVuZGVyZXI/OiBudWxsfFZpZXdVcGRhdGVGbik6IFZpZXdEZWZpbml0aW9uIHtcbiAgLy8gY2xvbmUgbm9kZXMgYW5kIHNldCBhdXRvIGNhbGN1bGF0ZWQgdmFsdWVzXG4gIGxldCB2aWV3QmluZGluZ0NvdW50ID0gMDtcbiAgbGV0IHZpZXdEaXNwb3NhYmxlQ291bnQgPSAwO1xuICBsZXQgdmlld05vZGVGbGFncyA9IDA7XG4gIGxldCB2aWV3Um9vdE5vZGVGbGFncyA9IDA7XG4gIGxldCB2aWV3TWF0Y2hlZFF1ZXJpZXMgPSAwO1xuICBsZXQgY3VycmVudFBhcmVudDogTm9kZURlZnxudWxsID0gbnVsbDtcbiAgbGV0IGN1cnJlbnRSZW5kZXJQYXJlbnQ6IE5vZGVEZWZ8bnVsbCA9IG51bGw7XG4gIGxldCBjdXJyZW50RWxlbWVudEhhc1B1YmxpY1Byb3ZpZGVycyA9IGZhbHNlO1xuICBsZXQgY3VycmVudEVsZW1lbnRIYXNQcml2YXRlUHJvdmlkZXJzID0gZmFsc2U7XG4gIGxldCBsYXN0UmVuZGVyUm9vdE5vZGU6IE5vZGVEZWZ8bnVsbCA9IG51bGw7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgbm9kZXMubGVuZ3RoOyBpKyspIHtcbiAgICBjb25zdCBub2RlID0gbm9kZXNbaV07XG4gICAgbm9kZS5ub2RlSW5kZXggPSBpO1xuICAgIG5vZGUucGFyZW50ID0gY3VycmVudFBhcmVudDtcbiAgICBub2RlLmJpbmRpbmdJbmRleCA9IHZpZXdCaW5kaW5nQ291bnQ7XG4gICAgbm9kZS5vdXRwdXRJbmRleCA9IHZpZXdEaXNwb3NhYmxlQ291bnQ7XG4gICAgbm9kZS5yZW5kZXJQYXJlbnQgPSBjdXJyZW50UmVuZGVyUGFyZW50O1xuXG4gICAgdmlld05vZGVGbGFncyB8PSBub2RlLmZsYWdzO1xuICAgIHZpZXdNYXRjaGVkUXVlcmllcyB8PSBub2RlLm1hdGNoZWRRdWVyeUlkcztcblxuICAgIGlmIChub2RlLmVsZW1lbnQpIHtcbiAgICAgIGNvbnN0IGVsRGVmID0gbm9kZS5lbGVtZW50O1xuICAgICAgZWxEZWYucHVibGljUHJvdmlkZXJzID1cbiAgICAgICAgICBjdXJyZW50UGFyZW50ID8gY3VycmVudFBhcmVudC5lbGVtZW50IS5wdWJsaWNQcm92aWRlcnMgOiBPYmplY3QuY3JlYXRlKG51bGwpO1xuICAgICAgZWxEZWYuYWxsUHJvdmlkZXJzID0gZWxEZWYucHVibGljUHJvdmlkZXJzO1xuICAgICAgLy8gTm90ZTogV2UgYXNzdW1lIHRoYXQgYWxsIHByb3ZpZGVycyBvZiBhbiBlbGVtZW50IGFyZSBiZWZvcmUgYW55IGNoaWxkIGVsZW1lbnQhXG4gICAgICBjdXJyZW50RWxlbWVudEhhc1B1YmxpY1Byb3ZpZGVycyA9IGZhbHNlO1xuICAgICAgY3VycmVudEVsZW1lbnRIYXNQcml2YXRlUHJvdmlkZXJzID0gZmFsc2U7XG5cbiAgICAgIGlmIChub2RlLmVsZW1lbnQudGVtcGxhdGUpIHtcbiAgICAgICAgdmlld01hdGNoZWRRdWVyaWVzIHw9IG5vZGUuZWxlbWVudC50ZW1wbGF0ZS5ub2RlTWF0Y2hlZFF1ZXJpZXM7XG4gICAgICB9XG4gICAgfVxuICAgIHZhbGlkYXRlTm9kZShjdXJyZW50UGFyZW50LCBub2RlLCBub2Rlcy5sZW5ndGgpO1xuXG5cbiAgICB2aWV3QmluZGluZ0NvdW50ICs9IG5vZGUuYmluZGluZ3MubGVuZ3RoO1xuICAgIHZpZXdEaXNwb3NhYmxlQ291bnQgKz0gbm9kZS5vdXRwdXRzLmxlbmd0aDtcblxuICAgIGlmICghY3VycmVudFJlbmRlclBhcmVudCAmJiAobm9kZS5mbGFncyAmIE5vZGVGbGFncy5DYXRSZW5kZXJOb2RlKSkge1xuICAgICAgbGFzdFJlbmRlclJvb3ROb2RlID0gbm9kZTtcbiAgICB9XG5cbiAgICBpZiAobm9kZS5mbGFncyAmIE5vZGVGbGFncy5DYXRQcm92aWRlcikge1xuICAgICAgaWYgKCFjdXJyZW50RWxlbWVudEhhc1B1YmxpY1Byb3ZpZGVycykge1xuICAgICAgICBjdXJyZW50RWxlbWVudEhhc1B1YmxpY1Byb3ZpZGVycyA9IHRydWU7XG4gICAgICAgIC8vIFVzZSBwcm90b3R5cGljYWwgaW5oZXJpdGFuY2UgdG8gbm90IGdldCBPKG5eMikgY29tcGxleGl0eS4uLlxuICAgICAgICBjdXJyZW50UGFyZW50IS5lbGVtZW50IS5wdWJsaWNQcm92aWRlcnMgPVxuICAgICAgICAgICAgT2JqZWN0LmNyZWF0ZShjdXJyZW50UGFyZW50IS5lbGVtZW50IS5wdWJsaWNQcm92aWRlcnMpO1xuICAgICAgICBjdXJyZW50UGFyZW50IS5lbGVtZW50IS5hbGxQcm92aWRlcnMgPSBjdXJyZW50UGFyZW50IS5lbGVtZW50IS5wdWJsaWNQcm92aWRlcnM7XG4gICAgICB9XG4gICAgICBjb25zdCBpc1ByaXZhdGVTZXJ2aWNlID0gKG5vZGUuZmxhZ3MgJiBOb2RlRmxhZ3MuUHJpdmF0ZVByb3ZpZGVyKSAhPT0gMDtcbiAgICAgIGNvbnN0IGlzQ29tcG9uZW50ID0gKG5vZGUuZmxhZ3MgJiBOb2RlRmxhZ3MuQ29tcG9uZW50KSAhPT0gMDtcbiAgICAgIGlmICghaXNQcml2YXRlU2VydmljZSB8fCBpc0NvbXBvbmVudCkge1xuICAgICAgICBjdXJyZW50UGFyZW50IS5lbGVtZW50IS5wdWJsaWNQcm92aWRlcnMhW3Rva2VuS2V5KG5vZGUucHJvdmlkZXIhLnRva2VuKV0gPSBub2RlO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKCFjdXJyZW50RWxlbWVudEhhc1ByaXZhdGVQcm92aWRlcnMpIHtcbiAgICAgICAgICBjdXJyZW50RWxlbWVudEhhc1ByaXZhdGVQcm92aWRlcnMgPSB0cnVlO1xuICAgICAgICAgIC8vIFVzZSBwcm90b3R5cGljYWwgaW5oZXJpdGFuY2UgdG8gbm90IGdldCBPKG5eMikgY29tcGxleGl0eS4uLlxuICAgICAgICAgIGN1cnJlbnRQYXJlbnQhLmVsZW1lbnQhLmFsbFByb3ZpZGVycyA9XG4gICAgICAgICAgICAgIE9iamVjdC5jcmVhdGUoY3VycmVudFBhcmVudCEuZWxlbWVudCEucHVibGljUHJvdmlkZXJzKTtcbiAgICAgICAgfVxuICAgICAgICBjdXJyZW50UGFyZW50IS5lbGVtZW50IS5hbGxQcm92aWRlcnMhW3Rva2VuS2V5KG5vZGUucHJvdmlkZXIhLnRva2VuKV0gPSBub2RlO1xuICAgICAgfVxuICAgICAgaWYgKGlzQ29tcG9uZW50KSB7XG4gICAgICAgIGN1cnJlbnRQYXJlbnQhLmVsZW1lbnQhLmNvbXBvbmVudFByb3ZpZGVyID0gbm9kZTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAoY3VycmVudFBhcmVudCkge1xuICAgICAgY3VycmVudFBhcmVudC5jaGlsZEZsYWdzIHw9IG5vZGUuZmxhZ3M7XG4gICAgICBjdXJyZW50UGFyZW50LmRpcmVjdENoaWxkRmxhZ3MgfD0gbm9kZS5mbGFncztcbiAgICAgIGN1cnJlbnRQYXJlbnQuY2hpbGRNYXRjaGVkUXVlcmllcyB8PSBub2RlLm1hdGNoZWRRdWVyeUlkcztcbiAgICAgIGlmIChub2RlLmVsZW1lbnQgJiYgbm9kZS5lbGVtZW50LnRlbXBsYXRlKSB7XG4gICAgICAgIGN1cnJlbnRQYXJlbnQuY2hpbGRNYXRjaGVkUXVlcmllcyB8PSBub2RlLmVsZW1lbnQudGVtcGxhdGUubm9kZU1hdGNoZWRRdWVyaWVzO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICB2aWV3Um9vdE5vZGVGbGFncyB8PSBub2RlLmZsYWdzO1xuICAgIH1cblxuICAgIGlmIChub2RlLmNoaWxkQ291bnQgPiAwKSB7XG4gICAgICBjdXJyZW50UGFyZW50ID0gbm9kZTtcblxuICAgICAgaWYgKCFpc05nQ29udGFpbmVyKG5vZGUpKSB7XG4gICAgICAgIGN1cnJlbnRSZW5kZXJQYXJlbnQgPSBub2RlO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAvLyBXaGVuIHRoZSBjdXJyZW50IG5vZGUgaGFzIG5vIGNoaWxkcmVuLCBjaGVjayBpZiBpdCBpcyB0aGUgbGFzdCBjaGlsZHJlbiBvZiBpdHMgcGFyZW50LlxuICAgICAgLy8gV2hlbiBpdCBpcywgcHJvcGFnYXRlIHRoZSBmbGFncyB1cC5cbiAgICAgIC8vIFRoZSBsb29wIGlzIHJlcXVpcmVkIGJlY2F1c2UgYW4gZWxlbWVudCBjb3VsZCBiZSB0aGUgbGFzdCB0cmFuc2l0aXZlIGNoaWxkcmVuIG9mIHNldmVyYWxcbiAgICAgIC8vIGVsZW1lbnRzLiBXZSBsb29wIHRvIGVpdGhlciB0aGUgcm9vdCBvciB0aGUgaGlnaGVzdCBvcGVuZWQgZWxlbWVudCAoPSB3aXRoIHJlbWFpbmluZ1xuICAgICAgLy8gY2hpbGRyZW4pXG4gICAgICB3aGlsZSAoY3VycmVudFBhcmVudCAmJiBpID09PSBjdXJyZW50UGFyZW50Lm5vZGVJbmRleCArIGN1cnJlbnRQYXJlbnQuY2hpbGRDb3VudCkge1xuICAgICAgICBjb25zdCBuZXdQYXJlbnQ6IE5vZGVEZWZ8bnVsbCA9IGN1cnJlbnRQYXJlbnQucGFyZW50O1xuICAgICAgICBpZiAobmV3UGFyZW50KSB7XG4gICAgICAgICAgbmV3UGFyZW50LmNoaWxkRmxhZ3MgfD0gY3VycmVudFBhcmVudC5jaGlsZEZsYWdzO1xuICAgICAgICAgIG5ld1BhcmVudC5jaGlsZE1hdGNoZWRRdWVyaWVzIHw9IGN1cnJlbnRQYXJlbnQuY2hpbGRNYXRjaGVkUXVlcmllcztcbiAgICAgICAgfVxuICAgICAgICBjdXJyZW50UGFyZW50ID0gbmV3UGFyZW50O1xuICAgICAgICAvLyBXZSBhbHNvIG5lZWQgdG8gdXBkYXRlIHRoZSByZW5kZXIgcGFyZW50ICYgYWNjb3VudCBmb3IgbmctY29udGFpbmVyXG4gICAgICAgIGlmIChjdXJyZW50UGFyZW50ICYmIGlzTmdDb250YWluZXIoY3VycmVudFBhcmVudCkpIHtcbiAgICAgICAgICBjdXJyZW50UmVuZGVyUGFyZW50ID0gY3VycmVudFBhcmVudC5yZW5kZXJQYXJlbnQ7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgY3VycmVudFJlbmRlclBhcmVudCA9IGN1cnJlbnRQYXJlbnQ7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICBjb25zdCBoYW5kbGVFdmVudDogVmlld0hhbmRsZUV2ZW50Rm4gPSAodmlldywgbm9kZUluZGV4LCBldmVudE5hbWUsIGV2ZW50KSA9PlxuICAgICAgbm9kZXNbbm9kZUluZGV4XS5lbGVtZW50IS5oYW5kbGVFdmVudCEodmlldywgZXZlbnROYW1lLCBldmVudCk7XG5cbiAgcmV0dXJuIHtcbiAgICAvLyBXaWxsIGJlIGZpbGxlZCBsYXRlci4uLlxuICAgIGZhY3Rvcnk6IG51bGwsXG4gICAgbm9kZUZsYWdzOiB2aWV3Tm9kZUZsYWdzLFxuICAgIHJvb3ROb2RlRmxhZ3M6IHZpZXdSb290Tm9kZUZsYWdzLFxuICAgIG5vZGVNYXRjaGVkUXVlcmllczogdmlld01hdGNoZWRRdWVyaWVzLFxuICAgIGZsYWdzLFxuICAgIG5vZGVzOiBub2RlcyxcbiAgICB1cGRhdGVEaXJlY3RpdmVzOiB1cGRhdGVEaXJlY3RpdmVzIHx8IE5PT1AsXG4gICAgdXBkYXRlUmVuZGVyZXI6IHVwZGF0ZVJlbmRlcmVyIHx8IE5PT1AsXG4gICAgaGFuZGxlRXZlbnQsXG4gICAgYmluZGluZ0NvdW50OiB2aWV3QmluZGluZ0NvdW50LFxuICAgIG91dHB1dENvdW50OiB2aWV3RGlzcG9zYWJsZUNvdW50LFxuICAgIGxhc3RSZW5kZXJSb290Tm9kZVxuICB9O1xufVxuXG5mdW5jdGlvbiBpc05nQ29udGFpbmVyKG5vZGU6IE5vZGVEZWYpOiBib29sZWFuIHtcbiAgcmV0dXJuIChub2RlLmZsYWdzICYgTm9kZUZsYWdzLlR5cGVFbGVtZW50KSAhPT0gMCAmJiBub2RlLmVsZW1lbnQhLm5hbWUgPT09IG51bGw7XG59XG5cbmZ1bmN0aW9uIHZhbGlkYXRlTm9kZShwYXJlbnQ6IE5vZGVEZWZ8bnVsbCwgbm9kZTogTm9kZURlZiwgbm9kZUNvdW50OiBudW1iZXIpIHtcbiAgY29uc3QgdGVtcGxhdGUgPSBub2RlLmVsZW1lbnQgJiYgbm9kZS5lbGVtZW50LnRlbXBsYXRlO1xuICBpZiAodGVtcGxhdGUpIHtcbiAgICBpZiAoIXRlbXBsYXRlLmxhc3RSZW5kZXJSb290Tm9kZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBJbGxlZ2FsIFN0YXRlOiBFbWJlZGRlZCB0ZW1wbGF0ZXMgd2l0aG91dCBub2RlcyBhcmUgbm90IGFsbG93ZWQhYCk7XG4gICAgfVxuICAgIGlmICh0ZW1wbGF0ZS5sYXN0UmVuZGVyUm9vdE5vZGUgJiZcbiAgICAgICAgdGVtcGxhdGUubGFzdFJlbmRlclJvb3ROb2RlLmZsYWdzICYgTm9kZUZsYWdzLkVtYmVkZGVkVmlld3MpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihcbiAgICAgICAgICBgSWxsZWdhbCBTdGF0ZTogTGFzdCByb290IG5vZGUgb2YgYSB0ZW1wbGF0ZSBjYW4ndCBoYXZlIGVtYmVkZGVkIHZpZXdzLCBhdCBpbmRleCAke1xuICAgICAgICAgICAgICBub2RlLm5vZGVJbmRleH0hYCk7XG4gICAgfVxuICB9XG4gIGlmIChub2RlLmZsYWdzICYgTm9kZUZsYWdzLkNhdFByb3ZpZGVyKSB7XG4gICAgY29uc3QgcGFyZW50RmxhZ3MgPSBwYXJlbnQgPyBwYXJlbnQuZmxhZ3MgOiAwO1xuICAgIGlmICgocGFyZW50RmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZUVsZW1lbnQpID09PSAwKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICAgYElsbGVnYWwgU3RhdGU6IFN0YXRpY1Byb3ZpZGVyL0RpcmVjdGl2ZSBub2RlcyBuZWVkIHRvIGJlIGNoaWxkcmVuIG9mIGVsZW1lbnRzIG9yIGFuY2hvcnMsIGF0IGluZGV4ICR7XG4gICAgICAgICAgICAgIG5vZGUubm9kZUluZGV4fSFgKTtcbiAgICB9XG4gIH1cbiAgaWYgKG5vZGUucXVlcnkpIHtcbiAgICBpZiAobm9kZS5mbGFncyAmIE5vZGVGbGFncy5UeXBlQ29udGVudFF1ZXJ5ICYmXG4gICAgICAgICghcGFyZW50IHx8IChwYXJlbnQuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZURpcmVjdGl2ZSkgPT09IDApKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICAgYElsbGVnYWwgU3RhdGU6IENvbnRlbnQgUXVlcnkgbm9kZXMgbmVlZCB0byBiZSBjaGlsZHJlbiBvZiBkaXJlY3RpdmVzLCBhdCBpbmRleCAke1xuICAgICAgICAgICAgICBub2RlLm5vZGVJbmRleH0hYCk7XG4gICAgfVxuICAgIGlmIChub2RlLmZsYWdzICYgTm9kZUZsYWdzLlR5cGVWaWV3UXVlcnkgJiYgcGFyZW50KSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYElsbGVnYWwgU3RhdGU6IFZpZXcgUXVlcnkgbm9kZXMgaGF2ZSB0byBiZSB0b3AgbGV2ZWwgbm9kZXMsIGF0IGluZGV4ICR7XG4gICAgICAgICAgbm9kZS5ub2RlSW5kZXh9IWApO1xuICAgIH1cbiAgfVxuICBpZiAobm9kZS5jaGlsZENvdW50KSB7XG4gICAgY29uc3QgcGFyZW50RW5kID0gcGFyZW50ID8gcGFyZW50Lm5vZGVJbmRleCArIHBhcmVudC5jaGlsZENvdW50IDogbm9kZUNvdW50IC0gMTtcbiAgICBpZiAobm9kZS5ub2RlSW5kZXggPD0gcGFyZW50RW5kICYmIG5vZGUubm9kZUluZGV4ICsgbm9kZS5jaGlsZENvdW50ID4gcGFyZW50RW5kKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICAgYElsbGVnYWwgU3RhdGU6IGNoaWxkQ291bnQgb2Ygbm9kZSBsZWFkcyBvdXRzaWRlIG9mIHBhcmVudCwgYXQgaW5kZXggJHtub2RlLm5vZGVJbmRleH0hYCk7XG4gICAgfVxuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVFbWJlZGRlZFZpZXcoXG4gICAgcGFyZW50OiBWaWV3RGF0YSwgYW5jaG9yRGVmOiBOb2RlRGVmLCB2aWV3RGVmOiBWaWV3RGVmaW5pdGlvbiwgY29udGV4dD86IGFueSk6IFZpZXdEYXRhIHtcbiAgLy8gZW1iZWRkZWQgdmlld3MgYXJlIHNlZW4gYXMgc2libGluZ3MgdG8gdGhlIGFuY2hvciwgc28gd2UgbmVlZFxuICAvLyB0byBnZXQgdGhlIHBhcmVudCBvZiB0aGUgYW5jaG9yIGFuZCB1c2UgaXQgYXMgcGFyZW50SW5kZXguXG4gIGNvbnN0IHZpZXcgPSBjcmVhdGVWaWV3KHBhcmVudC5yb290LCBwYXJlbnQucmVuZGVyZXIsIHBhcmVudCwgYW5jaG9yRGVmLCB2aWV3RGVmKTtcbiAgaW5pdFZpZXcodmlldywgcGFyZW50LmNvbXBvbmVudCwgY29udGV4dCk7XG4gIGNyZWF0ZVZpZXdOb2Rlcyh2aWV3KTtcbiAgcmV0dXJuIHZpZXc7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVSb290Vmlldyhyb290OiBSb290RGF0YSwgZGVmOiBWaWV3RGVmaW5pdGlvbiwgY29udGV4dD86IGFueSk6IFZpZXdEYXRhIHtcbiAgY29uc3QgdmlldyA9IGNyZWF0ZVZpZXcocm9vdCwgcm9vdC5yZW5kZXJlciwgbnVsbCwgbnVsbCwgZGVmKTtcbiAgaW5pdFZpZXcodmlldywgY29udGV4dCwgY29udGV4dCk7XG4gIGNyZWF0ZVZpZXdOb2Rlcyh2aWV3KTtcbiAgcmV0dXJuIHZpZXc7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVDb21wb25lbnRWaWV3KFxuICAgIHBhcmVudFZpZXc6IFZpZXdEYXRhLCBub2RlRGVmOiBOb2RlRGVmLCB2aWV3RGVmOiBWaWV3RGVmaW5pdGlvbiwgaG9zdEVsZW1lbnQ6IGFueSk6IFZpZXdEYXRhIHtcbiAgY29uc3QgcmVuZGVyZXJUeXBlID0gbm9kZURlZi5lbGVtZW50IS5jb21wb25lbnRSZW5kZXJlclR5cGU7XG4gIGxldCBjb21wUmVuZGVyZXI6IFJlbmRlcmVyMjtcbiAgaWYgKCFyZW5kZXJlclR5cGUpIHtcbiAgICBjb21wUmVuZGVyZXIgPSBwYXJlbnRWaWV3LnJvb3QucmVuZGVyZXI7XG4gIH0gZWxzZSB7XG4gICAgY29tcFJlbmRlcmVyID0gcGFyZW50Vmlldy5yb290LnJlbmRlcmVyRmFjdG9yeS5jcmVhdGVSZW5kZXJlcihob3N0RWxlbWVudCwgcmVuZGVyZXJUeXBlKTtcbiAgfVxuICByZXR1cm4gY3JlYXRlVmlldyhcbiAgICAgIHBhcmVudFZpZXcucm9vdCwgY29tcFJlbmRlcmVyLCBwYXJlbnRWaWV3LCBub2RlRGVmLmVsZW1lbnQhLmNvbXBvbmVudFByb3ZpZGVyLCB2aWV3RGVmKTtcbn1cblxuZnVuY3Rpb24gY3JlYXRlVmlldyhcbiAgICByb290OiBSb290RGF0YSwgcmVuZGVyZXI6IFJlbmRlcmVyMiwgcGFyZW50OiBWaWV3RGF0YXxudWxsLCBwYXJlbnROb2RlRGVmOiBOb2RlRGVmfG51bGwsXG4gICAgZGVmOiBWaWV3RGVmaW5pdGlvbik6IFZpZXdEYXRhIHtcbiAgY29uc3Qgbm9kZXM6IE5vZGVEYXRhW10gPSBuZXcgQXJyYXkoZGVmLm5vZGVzLmxlbmd0aCk7XG4gIGNvbnN0IGRpc3Bvc2FibGVzID0gZGVmLm91dHB1dENvdW50ID8gbmV3IEFycmF5KGRlZi5vdXRwdXRDb3VudCkgOiBudWxsO1xuICBjb25zdCB2aWV3OiBWaWV3RGF0YSA9IHtcbiAgICBkZWYsXG4gICAgcGFyZW50LFxuICAgIHZpZXdDb250YWluZXJQYXJlbnQ6IG51bGwsXG4gICAgcGFyZW50Tm9kZURlZixcbiAgICBjb250ZXh0OiBudWxsLFxuICAgIGNvbXBvbmVudDogbnVsbCxcbiAgICBub2RlcyxcbiAgICBzdGF0ZTogVmlld1N0YXRlLkNhdEluaXQsXG4gICAgcm9vdCxcbiAgICByZW5kZXJlcixcbiAgICBvbGRWYWx1ZXM6IG5ldyBBcnJheShkZWYuYmluZGluZ0NvdW50KSxcbiAgICBkaXNwb3NhYmxlcyxcbiAgICBpbml0SW5kZXg6IC0xXG4gIH07XG4gIHJldHVybiB2aWV3O1xufVxuXG5mdW5jdGlvbiBpbml0Vmlldyh2aWV3OiBWaWV3RGF0YSwgY29tcG9uZW50OiBhbnksIGNvbnRleHQ6IGFueSkge1xuICB2aWV3LmNvbXBvbmVudCA9IGNvbXBvbmVudDtcbiAgdmlldy5jb250ZXh0ID0gY29udGV4dDtcbn1cblxuZnVuY3Rpb24gY3JlYXRlVmlld05vZGVzKHZpZXc6IFZpZXdEYXRhKSB7XG4gIGxldCByZW5kZXJIb3N0OiBhbnk7XG4gIGlmIChpc0NvbXBvbmVudFZpZXcodmlldykpIHtcbiAgICBjb25zdCBob3N0RGVmID0gdmlldy5wYXJlbnROb2RlRGVmO1xuICAgIHJlbmRlckhvc3QgPSBhc0VsZW1lbnREYXRhKHZpZXcucGFyZW50ISwgaG9zdERlZiEucGFyZW50IS5ub2RlSW5kZXgpLnJlbmRlckVsZW1lbnQ7XG4gIH1cbiAgY29uc3QgZGVmID0gdmlldy5kZWY7XG4gIGNvbnN0IG5vZGVzID0gdmlldy5ub2RlcztcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBkZWYubm9kZXMubGVuZ3RoOyBpKyspIHtcbiAgICBjb25zdCBub2RlRGVmID0gZGVmLm5vZGVzW2ldO1xuICAgIFNlcnZpY2VzLnNldEN1cnJlbnROb2RlKHZpZXcsIGkpO1xuICAgIGxldCBub2RlRGF0YTogYW55O1xuICAgIHN3aXRjaCAobm9kZURlZi5mbGFncyAmIE5vZGVGbGFncy5UeXBlcykge1xuICAgICAgY2FzZSBOb2RlRmxhZ3MuVHlwZUVsZW1lbnQ6XG4gICAgICAgIGNvbnN0IGVsID0gY3JlYXRlRWxlbWVudCh2aWV3LCByZW5kZXJIb3N0LCBub2RlRGVmKSBhcyBhbnk7XG4gICAgICAgIGxldCBjb21wb25lbnRWaWV3OiBWaWV3RGF0YSA9IHVuZGVmaW5lZCE7XG4gICAgICAgIGlmIChub2RlRGVmLmZsYWdzICYgTm9kZUZsYWdzLkNvbXBvbmVudFZpZXcpIHtcbiAgICAgICAgICBjb25zdCBjb21wVmlld0RlZiA9IHJlc29sdmVEZWZpbml0aW9uKG5vZGVEZWYuZWxlbWVudCEuY29tcG9uZW50VmlldyEpO1xuICAgICAgICAgIGNvbXBvbmVudFZpZXcgPSBTZXJ2aWNlcy5jcmVhdGVDb21wb25lbnRWaWV3KHZpZXcsIG5vZGVEZWYsIGNvbXBWaWV3RGVmLCBlbCk7XG4gICAgICAgIH1cbiAgICAgICAgbGlzdGVuVG9FbGVtZW50T3V0cHV0cyh2aWV3LCBjb21wb25lbnRWaWV3LCBub2RlRGVmLCBlbCk7XG4gICAgICAgIG5vZGVEYXRhID0gPEVsZW1lbnREYXRhPntcbiAgICAgICAgICByZW5kZXJFbGVtZW50OiBlbCxcbiAgICAgICAgICBjb21wb25lbnRWaWV3LFxuICAgICAgICAgIHZpZXdDb250YWluZXI6IG51bGwsXG4gICAgICAgICAgdGVtcGxhdGU6IG5vZGVEZWYuZWxlbWVudCEudGVtcGxhdGUgPyBjcmVhdGVUZW1wbGF0ZURhdGEodmlldywgbm9kZURlZikgOiB1bmRlZmluZWRcbiAgICAgICAgfTtcbiAgICAgICAgaWYgKG5vZGVEZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuRW1iZWRkZWRWaWV3cykge1xuICAgICAgICAgIG5vZGVEYXRhLnZpZXdDb250YWluZXIgPSBjcmVhdGVWaWV3Q29udGFpbmVyRGF0YSh2aWV3LCBub2RlRGVmLCBub2RlRGF0YSk7XG4gICAgICAgIH1cbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIE5vZGVGbGFncy5UeXBlVGV4dDpcbiAgICAgICAgbm9kZURhdGEgPSBjcmVhdGVUZXh0KHZpZXcsIHJlbmRlckhvc3QsIG5vZGVEZWYpIGFzIGFueTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIE5vZGVGbGFncy5UeXBlQ2xhc3NQcm92aWRlcjpcbiAgICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVGYWN0b3J5UHJvdmlkZXI6XG4gICAgICBjYXNlIE5vZGVGbGFncy5UeXBlVXNlRXhpc3RpbmdQcm92aWRlcjpcbiAgICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVWYWx1ZVByb3ZpZGVyOiB7XG4gICAgICAgIG5vZGVEYXRhID0gbm9kZXNbaV07XG4gICAgICAgIGlmICghbm9kZURhdGEgJiYgIShub2RlRGVmLmZsYWdzICYgTm9kZUZsYWdzLkxhenlQcm92aWRlcikpIHtcbiAgICAgICAgICBjb25zdCBpbnN0YW5jZSA9IGNyZWF0ZVByb3ZpZGVySW5zdGFuY2Uodmlldywgbm9kZURlZik7XG4gICAgICAgICAgbm9kZURhdGEgPSA8UHJvdmlkZXJEYXRhPntpbnN0YW5jZX07XG4gICAgICAgIH1cbiAgICAgICAgYnJlYWs7XG4gICAgICB9XG4gICAgICBjYXNlIE5vZGVGbGFncy5UeXBlUGlwZToge1xuICAgICAgICBjb25zdCBpbnN0YW5jZSA9IGNyZWF0ZVBpcGVJbnN0YW5jZSh2aWV3LCBub2RlRGVmKTtcbiAgICAgICAgbm9kZURhdGEgPSA8UHJvdmlkZXJEYXRhPntpbnN0YW5jZX07XG4gICAgICAgIGJyZWFrO1xuICAgICAgfVxuICAgICAgY2FzZSBOb2RlRmxhZ3MuVHlwZURpcmVjdGl2ZToge1xuICAgICAgICBub2RlRGF0YSA9IG5vZGVzW2ldO1xuICAgICAgICBpZiAoIW5vZGVEYXRhKSB7XG4gICAgICAgICAgY29uc3QgaW5zdGFuY2UgPSBjcmVhdGVEaXJlY3RpdmVJbnN0YW5jZSh2aWV3LCBub2RlRGVmKTtcbiAgICAgICAgICBub2RlRGF0YSA9IDxQcm92aWRlckRhdGE+e2luc3RhbmNlfTtcbiAgICAgICAgfVxuICAgICAgICBpZiAobm9kZURlZi5mbGFncyAmIE5vZGVGbGFncy5Db21wb25lbnQpIHtcbiAgICAgICAgICBjb25zdCBjb21wVmlldyA9IGFzRWxlbWVudERhdGEodmlldywgbm9kZURlZi5wYXJlbnQhLm5vZGVJbmRleCkuY29tcG9uZW50VmlldztcbiAgICAgICAgICBpbml0Vmlldyhjb21wVmlldywgbm9kZURhdGEuaW5zdGFuY2UsIG5vZGVEYXRhLmluc3RhbmNlKTtcbiAgICAgICAgfVxuICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVQdXJlQXJyYXk6XG4gICAgICBjYXNlIE5vZGVGbGFncy5UeXBlUHVyZU9iamVjdDpcbiAgICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVQdXJlUGlwZTpcbiAgICAgICAgbm9kZURhdGEgPSBjcmVhdGVQdXJlRXhwcmVzc2lvbih2aWV3LCBub2RlRGVmKSBhcyBhbnk7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBOb2RlRmxhZ3MuVHlwZUNvbnRlbnRRdWVyeTpcbiAgICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVWaWV3UXVlcnk6XG4gICAgICAgIG5vZGVEYXRhID0gY3JlYXRlUXVlcnkoXG4gICAgICAgICAgICAgICAgICAgICAgIChub2RlRGVmLmZsYWdzICYgTm9kZUZsYWdzLkVtaXREaXN0aW5jdENoYW5nZXNPbmx5KSA9PT1cbiAgICAgICAgICAgICAgICAgICAgICAgTm9kZUZsYWdzLkVtaXREaXN0aW5jdENoYW5nZXNPbmx5KSBhcyBhbnk7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBOb2RlRmxhZ3MuVHlwZU5nQ29udGVudDpcbiAgICAgICAgYXBwZW5kTmdDb250ZW50KHZpZXcsIHJlbmRlckhvc3QsIG5vZGVEZWYpO1xuICAgICAgICAvLyBubyBydW50aW1lIGRhdGEgbmVlZGVkIGZvciBOZ0NvbnRlbnQuLi5cbiAgICAgICAgbm9kZURhdGEgPSB1bmRlZmluZWQ7XG4gICAgICAgIGJyZWFrO1xuICAgIH1cbiAgICBub2Rlc1tpXSA9IG5vZGVEYXRhO1xuICB9XG4gIC8vIENyZWF0ZSB0aGUgVmlld0RhdGEubm9kZXMgb2YgY29tcG9uZW50IHZpZXdzIGFmdGVyIHdlIGNyZWF0ZWQgZXZlcnl0aGluZyBlbHNlLFxuICAvLyBzbyB0aGF0IGUuZy4gbmctY29udGVudCB3b3Jrc1xuICBleGVjQ29tcG9uZW50Vmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5DcmVhdGVWaWV3Tm9kZXMpO1xuXG4gIC8vIGZpbGwgc3RhdGljIGNvbnRlbnQgYW5kIHZpZXcgcXVlcmllc1xuICBleGVjUXVlcmllc0FjdGlvbihcbiAgICAgIHZpZXcsIE5vZGVGbGFncy5UeXBlQ29udGVudFF1ZXJ5IHwgTm9kZUZsYWdzLlR5cGVWaWV3UXVlcnksIE5vZGVGbGFncy5TdGF0aWNRdWVyeSxcbiAgICAgIENoZWNrVHlwZS5DaGVja0FuZFVwZGF0ZSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjaGVja05vQ2hhbmdlc1ZpZXcodmlldzogVmlld0RhdGEpIHtcbiAgbWFya1Byb2plY3RlZFZpZXdzRm9yQ2hlY2sodmlldyk7XG4gIFNlcnZpY2VzLnVwZGF0ZURpcmVjdGl2ZXModmlldywgQ2hlY2tUeXBlLkNoZWNrTm9DaGFuZ2VzKTtcbiAgZXhlY0VtYmVkZGVkVmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5DaGVja05vQ2hhbmdlcyk7XG4gIFNlcnZpY2VzLnVwZGF0ZVJlbmRlcmVyKHZpZXcsIENoZWNrVHlwZS5DaGVja05vQ2hhbmdlcyk7XG4gIGV4ZWNDb21wb25lbnRWaWV3c0FjdGlvbih2aWV3LCBWaWV3QWN0aW9uLkNoZWNrTm9DaGFuZ2VzKTtcbiAgLy8gTm90ZTogV2UgZG9uJ3QgY2hlY2sgcXVlcmllcyBmb3IgY2hhbmdlcyBhcyB3ZSBkaWRuJ3QgZG8gdGhpcyBpbiB2Mi54LlxuICAvLyBUT0RPKHRib3NjaCk6IGludmVzdGlnYXRlIGlmIHdlIGNhbiBlbmFibGUgdGhlIGNoZWNrIGFnYWluIGluIHY1Lnggd2l0aCBhIG5pY2VyIGVycm9yIG1lc3NhZ2UuXG4gIHZpZXcuc3RhdGUgJj0gfihWaWV3U3RhdGUuQ2hlY2tQcm9qZWN0ZWRWaWV3cyB8IFZpZXdTdGF0ZS5DaGVja1Byb2plY3RlZFZpZXcpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gY2hlY2tBbmRVcGRhdGVWaWV3KHZpZXc6IFZpZXdEYXRhKSB7XG4gIGlmICh2aWV3LnN0YXRlICYgVmlld1N0YXRlLkJlZm9yZUZpcnN0Q2hlY2spIHtcbiAgICB2aWV3LnN0YXRlICY9IH5WaWV3U3RhdGUuQmVmb3JlRmlyc3RDaGVjaztcbiAgICB2aWV3LnN0YXRlIHw9IFZpZXdTdGF0ZS5GaXJzdENoZWNrO1xuICB9IGVsc2Uge1xuICAgIHZpZXcuc3RhdGUgJj0gflZpZXdTdGF0ZS5GaXJzdENoZWNrO1xuICB9XG4gIHNoaWZ0SW5pdFN0YXRlKHZpZXcsIFZpZXdTdGF0ZS5Jbml0U3RhdGVfQmVmb3JlSW5pdCwgVmlld1N0YXRlLkluaXRTdGF0ZV9DYWxsaW5nT25Jbml0KTtcbiAgbWFya1Byb2plY3RlZFZpZXdzRm9yQ2hlY2sodmlldyk7XG4gIFNlcnZpY2VzLnVwZGF0ZURpcmVjdGl2ZXModmlldywgQ2hlY2tUeXBlLkNoZWNrQW5kVXBkYXRlKTtcbiAgZXhlY0VtYmVkZGVkVmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5DaGVja0FuZFVwZGF0ZSk7XG4gIGV4ZWNRdWVyaWVzQWN0aW9uKFxuICAgICAgdmlldywgTm9kZUZsYWdzLlR5cGVDb250ZW50UXVlcnksIE5vZGVGbGFncy5EeW5hbWljUXVlcnksIENoZWNrVHlwZS5DaGVja0FuZFVwZGF0ZSk7XG4gIGxldCBjYWxsSW5pdCA9IHNoaWZ0SW5pdFN0YXRlKFxuICAgICAgdmlldywgVmlld1N0YXRlLkluaXRTdGF0ZV9DYWxsaW5nT25Jbml0LCBWaWV3U3RhdGUuSW5pdFN0YXRlX0NhbGxpbmdBZnRlckNvbnRlbnRJbml0KTtcbiAgY2FsbExpZmVjeWNsZUhvb2tzQ2hpbGRyZW5GaXJzdChcbiAgICAgIHZpZXcsIE5vZGVGbGFncy5BZnRlckNvbnRlbnRDaGVja2VkIHwgKGNhbGxJbml0ID8gTm9kZUZsYWdzLkFmdGVyQ29udGVudEluaXQgOiAwKSk7XG5cbiAgU2VydmljZXMudXBkYXRlUmVuZGVyZXIodmlldywgQ2hlY2tUeXBlLkNoZWNrQW5kVXBkYXRlKTtcblxuICBleGVjQ29tcG9uZW50Vmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5DaGVja0FuZFVwZGF0ZSk7XG4gIGV4ZWNRdWVyaWVzQWN0aW9uKFxuICAgICAgdmlldywgTm9kZUZsYWdzLlR5cGVWaWV3UXVlcnksIE5vZGVGbGFncy5EeW5hbWljUXVlcnksIENoZWNrVHlwZS5DaGVja0FuZFVwZGF0ZSk7XG4gIGNhbGxJbml0ID0gc2hpZnRJbml0U3RhdGUoXG4gICAgICB2aWV3LCBWaWV3U3RhdGUuSW5pdFN0YXRlX0NhbGxpbmdBZnRlckNvbnRlbnRJbml0LCBWaWV3U3RhdGUuSW5pdFN0YXRlX0NhbGxpbmdBZnRlclZpZXdJbml0KTtcbiAgY2FsbExpZmVjeWNsZUhvb2tzQ2hpbGRyZW5GaXJzdChcbiAgICAgIHZpZXcsIE5vZGVGbGFncy5BZnRlclZpZXdDaGVja2VkIHwgKGNhbGxJbml0ID8gTm9kZUZsYWdzLkFmdGVyVmlld0luaXQgOiAwKSk7XG5cbiAgaWYgKHZpZXcuZGVmLmZsYWdzICYgVmlld0ZsYWdzLk9uUHVzaCkge1xuICAgIHZpZXcuc3RhdGUgJj0gflZpZXdTdGF0ZS5DaGVja3NFbmFibGVkO1xuICB9XG4gIHZpZXcuc3RhdGUgJj0gfihWaWV3U3RhdGUuQ2hlY2tQcm9qZWN0ZWRWaWV3cyB8IFZpZXdTdGF0ZS5DaGVja1Byb2plY3RlZFZpZXcpO1xuICBzaGlmdEluaXRTdGF0ZSh2aWV3LCBWaWV3U3RhdGUuSW5pdFN0YXRlX0NhbGxpbmdBZnRlclZpZXdJbml0LCBWaWV3U3RhdGUuSW5pdFN0YXRlX0FmdGVySW5pdCk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjaGVja0FuZFVwZGF0ZU5vZGUoXG4gICAgdmlldzogVmlld0RhdGEsIG5vZGVEZWY6IE5vZGVEZWYsIGFyZ1N0eWxlOiBBcmd1bWVudFR5cGUsIHYwPzogYW55LCB2MT86IGFueSwgdjI/OiBhbnksXG4gICAgdjM/OiBhbnksIHY0PzogYW55LCB2NT86IGFueSwgdjY/OiBhbnksIHY3PzogYW55LCB2OD86IGFueSwgdjk/OiBhbnkpOiBib29sZWFuIHtcbiAgaWYgKGFyZ1N0eWxlID09PSBBcmd1bWVudFR5cGUuSW5saW5lKSB7XG4gICAgcmV0dXJuIGNoZWNrQW5kVXBkYXRlTm9kZUlubGluZSh2aWV3LCBub2RlRGVmLCB2MCwgdjEsIHYyLCB2MywgdjQsIHY1LCB2NiwgdjcsIHY4LCB2OSk7XG4gIH0gZWxzZSB7XG4gICAgcmV0dXJuIGNoZWNrQW5kVXBkYXRlTm9kZUR5bmFtaWModmlldywgbm9kZURlZiwgdjApO1xuICB9XG59XG5cbmZ1bmN0aW9uIG1hcmtQcm9qZWN0ZWRWaWV3c0ZvckNoZWNrKHZpZXc6IFZpZXdEYXRhKSB7XG4gIGNvbnN0IGRlZiA9IHZpZXcuZGVmO1xuICBpZiAoIShkZWYubm9kZUZsYWdzICYgTm9kZUZsYWdzLlByb2plY3RlZFRlbXBsYXRlKSkge1xuICAgIHJldHVybjtcbiAgfVxuICBmb3IgKGxldCBpID0gMDsgaSA8IGRlZi5ub2Rlcy5sZW5ndGg7IGkrKykge1xuICAgIGNvbnN0IG5vZGVEZWYgPSBkZWYubm9kZXNbaV07XG4gICAgaWYgKG5vZGVEZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuUHJvamVjdGVkVGVtcGxhdGUpIHtcbiAgICAgIGNvbnN0IHByb2plY3RlZFZpZXdzID0gYXNFbGVtZW50RGF0YSh2aWV3LCBpKS50ZW1wbGF0ZS5fcHJvamVjdGVkVmlld3M7XG4gICAgICBpZiAocHJvamVjdGVkVmlld3MpIHtcbiAgICAgICAgZm9yIChsZXQgaSA9IDA7IGkgPCBwcm9qZWN0ZWRWaWV3cy5sZW5ndGg7IGkrKykge1xuICAgICAgICAgIGNvbnN0IHByb2plY3RlZFZpZXcgPSBwcm9qZWN0ZWRWaWV3c1tpXTtcbiAgICAgICAgICBwcm9qZWN0ZWRWaWV3LnN0YXRlIHw9IFZpZXdTdGF0ZS5DaGVja1Byb2plY3RlZFZpZXc7XG4gICAgICAgICAgbWFya1BhcmVudFZpZXdzRm9yQ2hlY2tQcm9qZWN0ZWRWaWV3cyhwcm9qZWN0ZWRWaWV3LCB2aWV3KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0gZWxzZSBpZiAoKG5vZGVEZWYuY2hpbGRGbGFncyAmIE5vZGVGbGFncy5Qcm9qZWN0ZWRUZW1wbGF0ZSkgPT09IDApIHtcbiAgICAgIC8vIGEgcGFyZW50IHdpdGggbGVhZnNcbiAgICAgIC8vIG5vIGNoaWxkIGlzIGEgY29tcG9uZW50LFxuICAgICAgLy8gdGhlbiBza2lwIHRoZSBjaGlsZHJlblxuICAgICAgaSArPSBub2RlRGVmLmNoaWxkQ291bnQ7XG4gICAgfVxuICB9XG59XG5cbmZ1bmN0aW9uIGNoZWNrQW5kVXBkYXRlTm9kZUlubGluZShcbiAgICB2aWV3OiBWaWV3RGF0YSwgbm9kZURlZjogTm9kZURlZiwgdjA/OiBhbnksIHYxPzogYW55LCB2Mj86IGFueSwgdjM/OiBhbnksIHY0PzogYW55LCB2NT86IGFueSxcbiAgICB2Nj86IGFueSwgdjc/OiBhbnksIHY4PzogYW55LCB2OT86IGFueSk6IGJvb2xlYW4ge1xuICBzd2l0Y2ggKG5vZGVEZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZXMpIHtcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlRWxlbWVudDpcbiAgICAgIHJldHVybiBjaGVja0FuZFVwZGF0ZUVsZW1lbnRJbmxpbmUodmlldywgbm9kZURlZiwgdjAsIHYxLCB2MiwgdjMsIHY0LCB2NSwgdjYsIHY3LCB2OCwgdjkpO1xuICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVUZXh0OlxuICAgICAgcmV0dXJuIGNoZWNrQW5kVXBkYXRlVGV4dElubGluZSh2aWV3LCBub2RlRGVmLCB2MCwgdjEsIHYyLCB2MywgdjQsIHY1LCB2NiwgdjcsIHY4LCB2OSk7XG4gICAgY2FzZSBOb2RlRmxhZ3MuVHlwZURpcmVjdGl2ZTpcbiAgICAgIHJldHVybiBjaGVja0FuZFVwZGF0ZURpcmVjdGl2ZUlubGluZSh2aWV3LCBub2RlRGVmLCB2MCwgdjEsIHYyLCB2MywgdjQsIHY1LCB2NiwgdjcsIHY4LCB2OSk7XG4gICAgY2FzZSBOb2RlRmxhZ3MuVHlwZVB1cmVBcnJheTpcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlUHVyZU9iamVjdDpcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlUHVyZVBpcGU6XG4gICAgICByZXR1cm4gY2hlY2tBbmRVcGRhdGVQdXJlRXhwcmVzc2lvbklubGluZShcbiAgICAgICAgICB2aWV3LCBub2RlRGVmLCB2MCwgdjEsIHYyLCB2MywgdjQsIHY1LCB2NiwgdjcsIHY4LCB2OSk7XG4gICAgZGVmYXVsdDpcbiAgICAgIHRocm93ICd1bnJlYWNoYWJsZSc7XG4gIH1cbn1cblxuZnVuY3Rpb24gY2hlY2tBbmRVcGRhdGVOb2RlRHluYW1pYyh2aWV3OiBWaWV3RGF0YSwgbm9kZURlZjogTm9kZURlZiwgdmFsdWVzOiBhbnlbXSk6IGJvb2xlYW4ge1xuICBzd2l0Y2ggKG5vZGVEZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZXMpIHtcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlRWxlbWVudDpcbiAgICAgIHJldHVybiBjaGVja0FuZFVwZGF0ZUVsZW1lbnREeW5hbWljKHZpZXcsIG5vZGVEZWYsIHZhbHVlcyk7XG4gICAgY2FzZSBOb2RlRmxhZ3MuVHlwZVRleHQ6XG4gICAgICByZXR1cm4gY2hlY2tBbmRVcGRhdGVUZXh0RHluYW1pYyh2aWV3LCBub2RlRGVmLCB2YWx1ZXMpO1xuICAgIGNhc2UgTm9kZUZsYWdzLlR5cGVEaXJlY3RpdmU6XG4gICAgICByZXR1cm4gY2hlY2tBbmRVcGRhdGVEaXJlY3RpdmVEeW5hbWljKHZpZXcsIG5vZGVEZWYsIHZhbHVlcyk7XG4gICAgY2FzZSBOb2RlRmxhZ3MuVHlwZVB1cmVBcnJheTpcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlUHVyZU9iamVjdDpcbiAgICBjYXNlIE5vZGVGbGFncy5UeXBlUHVyZVBpcGU6XG4gICAgICByZXR1cm4gY2hlY2tBbmRVcGRhdGVQdXJlRXhwcmVzc2lvbkR5bmFtaWModmlldywgbm9kZURlZiwgdmFsdWVzKTtcbiAgICBkZWZhdWx0OlxuICAgICAgdGhyb3cgJ3VucmVhY2hhYmxlJztcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gY2hlY2tOb0NoYW5nZXNOb2RlKFxuICAgIHZpZXc6IFZpZXdEYXRhLCBub2RlRGVmOiBOb2RlRGVmLCBhcmdTdHlsZTogQXJndW1lbnRUeXBlLCB2MD86IGFueSwgdjE/OiBhbnksIHYyPzogYW55LFxuICAgIHYzPzogYW55LCB2ND86IGFueSwgdjU/OiBhbnksIHY2PzogYW55LCB2Nz86IGFueSwgdjg/OiBhbnksIHY5PzogYW55KTogYW55IHtcbiAgaWYgKGFyZ1N0eWxlID09PSBBcmd1bWVudFR5cGUuSW5saW5lKSB7XG4gICAgY2hlY2tOb0NoYW5nZXNOb2RlSW5saW5lKHZpZXcsIG5vZGVEZWYsIHYwLCB2MSwgdjIsIHYzLCB2NCwgdjUsIHY2LCB2NywgdjgsIHY5KTtcbiAgfSBlbHNlIHtcbiAgICBjaGVja05vQ2hhbmdlc05vZGVEeW5hbWljKHZpZXcsIG5vZGVEZWYsIHYwKTtcbiAgfVxuICAvLyBSZXR1cm5pbmcgZmFsc2UgaXMgb2sgaGVyZSBhcyB3ZSB3b3VsZCBoYXZlIHRocm93biBpbiBjYXNlIG9mIGEgY2hhbmdlLlxuICByZXR1cm4gZmFsc2U7XG59XG5cbmZ1bmN0aW9uIGNoZWNrTm9DaGFuZ2VzTm9kZUlubGluZShcbiAgICB2aWV3OiBWaWV3RGF0YSwgbm9kZURlZjogTm9kZURlZiwgdjA6IGFueSwgdjE6IGFueSwgdjI6IGFueSwgdjM6IGFueSwgdjQ6IGFueSwgdjU6IGFueSwgdjY6IGFueSxcbiAgICB2NzogYW55LCB2ODogYW55LCB2OTogYW55KTogdm9pZCB7XG4gIGNvbnN0IGJpbmRMZW4gPSBub2RlRGVmLmJpbmRpbmdzLmxlbmd0aDtcbiAgaWYgKGJpbmRMZW4gPiAwKSBjaGVja0JpbmRpbmdOb0NoYW5nZXModmlldywgbm9kZURlZiwgMCwgdjApO1xuICBpZiAoYmluZExlbiA+IDEpIGNoZWNrQmluZGluZ05vQ2hhbmdlcyh2aWV3LCBub2RlRGVmLCAxLCB2MSk7XG4gIGlmIChiaW5kTGVuID4gMikgY2hlY2tCaW5kaW5nTm9DaGFuZ2VzKHZpZXcsIG5vZGVEZWYsIDIsIHYyKTtcbiAgaWYgKGJpbmRMZW4gPiAzKSBjaGVja0JpbmRpbmdOb0NoYW5nZXModmlldywgbm9kZURlZiwgMywgdjMpO1xuICBpZiAoYmluZExlbiA+IDQpIGNoZWNrQmluZGluZ05vQ2hhbmdlcyh2aWV3LCBub2RlRGVmLCA0LCB2NCk7XG4gIGlmIChiaW5kTGVuID4gNSkgY2hlY2tCaW5kaW5nTm9DaGFuZ2VzKHZpZXcsIG5vZGVEZWYsIDUsIHY1KTtcbiAgaWYgKGJpbmRMZW4gPiA2KSBjaGVja0JpbmRpbmdOb0NoYW5nZXModmlldywgbm9kZURlZiwgNiwgdjYpO1xuICBpZiAoYmluZExlbiA+IDcpIGNoZWNrQmluZGluZ05vQ2hhbmdlcyh2aWV3LCBub2RlRGVmLCA3LCB2Nyk7XG4gIGlmIChiaW5kTGVuID4gOCkgY2hlY2tCaW5kaW5nTm9DaGFuZ2VzKHZpZXcsIG5vZGVEZWYsIDgsIHY4KTtcbiAgaWYgKGJpbmRMZW4gPiA5KSBjaGVja0JpbmRpbmdOb0NoYW5nZXModmlldywgbm9kZURlZiwgOSwgdjkpO1xufVxuXG5mdW5jdGlvbiBjaGVja05vQ2hhbmdlc05vZGVEeW5hbWljKHZpZXc6IFZpZXdEYXRhLCBub2RlRGVmOiBOb2RlRGVmLCB2YWx1ZXM6IGFueVtdKTogdm9pZCB7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgdmFsdWVzLmxlbmd0aDsgaSsrKSB7XG4gICAgY2hlY2tCaW5kaW5nTm9DaGFuZ2VzKHZpZXcsIG5vZGVEZWYsIGksIHZhbHVlc1tpXSk7XG4gIH1cbn1cblxuLyoqXG4gKiBXb3JrYXJvdW5kIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL3RzaWNrbGUvaXNzdWVzLzQ5N1xuICogQHN1cHByZXNzIHttaXNwbGFjZWRUeXBlQW5ub3RhdGlvbn1cbiAqL1xuZnVuY3Rpb24gY2hlY2tOb0NoYW5nZXNRdWVyeSh2aWV3OiBWaWV3RGF0YSwgbm9kZURlZjogTm9kZURlZikge1xuICBjb25zdCBxdWVyeUxpc3QgPSBhc1F1ZXJ5TGlzdCh2aWV3LCBub2RlRGVmLm5vZGVJbmRleCk7XG4gIGlmIChxdWVyeUxpc3QuZGlydHkpIHtcbiAgICB0aHJvdyBleHByZXNzaW9uQ2hhbmdlZEFmdGVySXRIYXNCZWVuQ2hlY2tlZEVycm9yKFxuICAgICAgICBTZXJ2aWNlcy5jcmVhdGVEZWJ1Z0NvbnRleHQodmlldywgbm9kZURlZi5ub2RlSW5kZXgpLFxuICAgICAgICBgUXVlcnkgJHtub2RlRGVmLnF1ZXJ5IS5pZH0gbm90IGRpcnR5YCwgYFF1ZXJ5ICR7bm9kZURlZi5xdWVyeSEuaWR9IGRpcnR5YCxcbiAgICAgICAgKHZpZXcuc3RhdGUgJiBWaWV3U3RhdGUuQmVmb3JlRmlyc3RDaGVjaykgIT09IDApO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBkZXN0cm95Vmlldyh2aWV3OiBWaWV3RGF0YSkge1xuICBpZiAodmlldy5zdGF0ZSAmIFZpZXdTdGF0ZS5EZXN0cm95ZWQpIHtcbiAgICByZXR1cm47XG4gIH1cbiAgZXhlY0VtYmVkZGVkVmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5EZXN0cm95KTtcbiAgZXhlY0NvbXBvbmVudFZpZXdzQWN0aW9uKHZpZXcsIFZpZXdBY3Rpb24uRGVzdHJveSk7XG4gIGNhbGxMaWZlY3ljbGVIb29rc0NoaWxkcmVuRmlyc3QodmlldywgTm9kZUZsYWdzLk9uRGVzdHJveSk7XG4gIGlmICh2aWV3LmRpc3Bvc2FibGVzKSB7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCB2aWV3LmRpc3Bvc2FibGVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICB2aWV3LmRpc3Bvc2FibGVzW2ldKCk7XG4gICAgfVxuICB9XG4gIGRldGFjaFByb2plY3RlZFZpZXcodmlldyk7XG4gIGlmICh2aWV3LnJlbmRlcmVyLmRlc3Ryb3lOb2RlKSB7XG4gICAgZGVzdHJveVZpZXdOb2Rlcyh2aWV3KTtcbiAgfVxuICBpZiAoaXNDb21wb25lbnRWaWV3KHZpZXcpKSB7XG4gICAgdmlldy5yZW5kZXJlci5kZXN0cm95KCk7XG4gIH1cbiAgdmlldy5zdGF0ZSB8PSBWaWV3U3RhdGUuRGVzdHJveWVkO1xufVxuXG5mdW5jdGlvbiBkZXN0cm95Vmlld05vZGVzKHZpZXc6IFZpZXdEYXRhKSB7XG4gIGNvbnN0IGxlbiA9IHZpZXcuZGVmLm5vZGVzLmxlbmd0aDtcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBsZW47IGkrKykge1xuICAgIGNvbnN0IGRlZiA9IHZpZXcuZGVmLm5vZGVzW2ldO1xuICAgIGlmIChkZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZUVsZW1lbnQpIHtcbiAgICAgIHZpZXcucmVuZGVyZXIuZGVzdHJveU5vZGUhKGFzRWxlbWVudERhdGEodmlldywgaSkucmVuZGVyRWxlbWVudCk7XG4gICAgfSBlbHNlIGlmIChkZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZVRleHQpIHtcbiAgICAgIHZpZXcucmVuZGVyZXIuZGVzdHJveU5vZGUhKGFzVGV4dERhdGEodmlldywgaSkucmVuZGVyVGV4dCk7XG4gICAgfSBlbHNlIGlmIChkZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZUNvbnRlbnRRdWVyeSB8fCBkZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuVHlwZVZpZXdRdWVyeSkge1xuICAgICAgYXNRdWVyeUxpc3QodmlldywgaSkuZGVzdHJveSgpO1xuICAgIH1cbiAgfVxufVxuXG5lbnVtIFZpZXdBY3Rpb24ge1xuICBDcmVhdGVWaWV3Tm9kZXMsXG4gIENoZWNrTm9DaGFuZ2VzLFxuICBDaGVja05vQ2hhbmdlc1Byb2plY3RlZFZpZXdzLFxuICBDaGVja0FuZFVwZGF0ZSxcbiAgQ2hlY2tBbmRVcGRhdGVQcm9qZWN0ZWRWaWV3cyxcbiAgRGVzdHJveVxufVxuXG5mdW5jdGlvbiBleGVjQ29tcG9uZW50Vmlld3NBY3Rpb24odmlldzogVmlld0RhdGEsIGFjdGlvbjogVmlld0FjdGlvbikge1xuICBjb25zdCBkZWYgPSB2aWV3LmRlZjtcbiAgaWYgKCEoZGVmLm5vZGVGbGFncyAmIE5vZGVGbGFncy5Db21wb25lbnRWaWV3KSkge1xuICAgIHJldHVybjtcbiAgfVxuICBmb3IgKGxldCBpID0gMDsgaSA8IGRlZi5ub2Rlcy5sZW5ndGg7IGkrKykge1xuICAgIGNvbnN0IG5vZGVEZWYgPSBkZWYubm9kZXNbaV07XG4gICAgaWYgKG5vZGVEZWYuZmxhZ3MgJiBOb2RlRmxhZ3MuQ29tcG9uZW50Vmlldykge1xuICAgICAgLy8gYSBsZWFmXG4gICAgICBjYWxsVmlld0FjdGlvbihhc0VsZW1lbnREYXRhKHZpZXcsIGkpLmNvbXBvbmVudFZpZXcsIGFjdGlvbik7XG4gICAgfSBlbHNlIGlmICgobm9kZURlZi5jaGlsZEZsYWdzICYgTm9kZUZsYWdzLkNvbXBvbmVudFZpZXcpID09PSAwKSB7XG4gICAgICAvLyBhIHBhcmVudCB3aXRoIGxlYWZzXG4gICAgICAvLyBubyBjaGlsZCBpcyBhIGNvbXBvbmVudCxcbiAgICAgIC8vIHRoZW4gc2tpcCB0aGUgY2hpbGRyZW5cbiAgICAgIGkgKz0gbm9kZURlZi5jaGlsZENvdW50O1xuICAgIH1cbiAgfVxufVxuXG5mdW5jdGlvbiBleGVjRW1iZWRkZWRWaWV3c0FjdGlvbih2aWV3OiBWaWV3RGF0YSwgYWN0aW9uOiBWaWV3QWN0aW9uKSB7XG4gIGNvbnN0IGRlZiA9IHZpZXcuZGVmO1xuICBpZiAoIShkZWYubm9kZUZsYWdzICYgTm9kZUZsYWdzLkVtYmVkZGVkVmlld3MpKSB7XG4gICAgcmV0dXJuO1xuICB9XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgZGVmLm5vZGVzLmxlbmd0aDsgaSsrKSB7XG4gICAgY29uc3Qgbm9kZURlZiA9IGRlZi5ub2Rlc1tpXTtcbiAgICBpZiAobm9kZURlZi5mbGFncyAmIE5vZGVGbGFncy5FbWJlZGRlZFZpZXdzKSB7XG4gICAgICAvLyBhIGxlYWZcbiAgICAgIGNvbnN0IGVtYmVkZGVkVmlld3MgPSBhc0VsZW1lbnREYXRhKHZpZXcsIGkpLnZpZXdDb250YWluZXIhLl9lbWJlZGRlZFZpZXdzO1xuICAgICAgZm9yIChsZXQgayA9IDA7IGsgPCBlbWJlZGRlZFZpZXdzLmxlbmd0aDsgaysrKSB7XG4gICAgICAgIGNhbGxWaWV3QWN0aW9uKGVtYmVkZGVkVmlld3Nba10sIGFjdGlvbik7XG4gICAgICB9XG4gICAgfSBlbHNlIGlmICgobm9kZURlZi5jaGlsZEZsYWdzICYgTm9kZUZsYWdzLkVtYmVkZGVkVmlld3MpID09PSAwKSB7XG4gICAgICAvLyBhIHBhcmVudCB3aXRoIGxlYWZzXG4gICAgICAvLyBubyBjaGlsZCBpcyBhIGNvbXBvbmVudCxcbiAgICAgIC8vIHRoZW4gc2tpcCB0aGUgY2hpbGRyZW5cbiAgICAgIGkgKz0gbm9kZURlZi5jaGlsZENvdW50O1xuICAgIH1cbiAgfVxufVxuXG5mdW5jdGlvbiBjYWxsVmlld0FjdGlvbih2aWV3OiBWaWV3RGF0YSwgYWN0aW9uOiBWaWV3QWN0aW9uKSB7XG4gIGNvbnN0IHZpZXdTdGF0ZSA9IHZpZXcuc3RhdGU7XG4gIHN3aXRjaCAoYWN0aW9uKSB7XG4gICAgY2FzZSBWaWV3QWN0aW9uLkNoZWNrTm9DaGFuZ2VzOlxuICAgICAgaWYgKCh2aWV3U3RhdGUgJiBWaWV3U3RhdGUuRGVzdHJveWVkKSA9PT0gMCkge1xuICAgICAgICBpZiAoKHZpZXdTdGF0ZSAmIFZpZXdTdGF0ZS5DYXREZXRlY3RDaGFuZ2VzKSA9PT0gVmlld1N0YXRlLkNhdERldGVjdENoYW5nZXMpIHtcbiAgICAgICAgICBjaGVja05vQ2hhbmdlc1ZpZXcodmlldyk7XG4gICAgICAgIH0gZWxzZSBpZiAodmlld1N0YXRlICYgVmlld1N0YXRlLkNoZWNrUHJvamVjdGVkVmlld3MpIHtcbiAgICAgICAgICBleGVjUHJvamVjdGVkVmlld3NBY3Rpb24odmlldywgVmlld0FjdGlvbi5DaGVja05vQ2hhbmdlc1Byb2plY3RlZFZpZXdzKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgYnJlYWs7XG4gICAgY2FzZSBWaWV3QWN0aW9uLkNoZWNrTm9DaGFuZ2VzUHJvamVjdGVkVmlld3M6XG4gICAgICBpZiAoKHZpZXdTdGF0ZSAmIFZpZXdTdGF0ZS5EZXN0cm95ZWQpID09PSAwKSB7XG4gICAgICAgIGlmICh2aWV3U3RhdGUgJiBWaWV3U3RhdGUuQ2hlY2tQcm9qZWN0ZWRWaWV3KSB7XG4gICAgICAgICAgY2hlY2tOb0NoYW5nZXNWaWV3KHZpZXcpO1xuICAgICAgICB9IGVsc2UgaWYgKHZpZXdTdGF0ZSAmIFZpZXdTdGF0ZS5DaGVja1Byb2plY3RlZFZpZXdzKSB7XG4gICAgICAgICAgZXhlY1Byb2plY3RlZFZpZXdzQWN0aW9uKHZpZXcsIGFjdGlvbik7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgVmlld0FjdGlvbi5DaGVja0FuZFVwZGF0ZTpcbiAgICAgIGlmICgodmlld1N0YXRlICYgVmlld1N0YXRlLkRlc3Ryb3llZCkgPT09IDApIHtcbiAgICAgICAgaWYgKCh2aWV3U3RhdGUgJiBWaWV3U3RhdGUuQ2F0RGV0ZWN0Q2hhbmdlcykgPT09IFZpZXdTdGF0ZS5DYXREZXRlY3RDaGFuZ2VzKSB7XG4gICAgICAgICAgY2hlY2tBbmRVcGRhdGVWaWV3KHZpZXcpO1xuICAgICAgICB9IGVsc2UgaWYgKHZpZXdTdGF0ZSAmIFZpZXdTdGF0ZS5DaGVja1Byb2plY3RlZFZpZXdzKSB7XG4gICAgICAgICAgZXhlY1Byb2plY3RlZFZpZXdzQWN0aW9uKHZpZXcsIFZpZXdBY3Rpb24uQ2hlY2tBbmRVcGRhdGVQcm9qZWN0ZWRWaWV3cyk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgVmlld0FjdGlvbi5DaGVja0FuZFVwZGF0ZVByb2plY3RlZFZpZXdzOlxuICAgICAgaWYgKCh2aWV3U3RhdGUgJiBWaWV3U3RhdGUuRGVzdHJveWVkKSA9PT0gMCkge1xuICAgICAgICBpZiAodmlld1N0YXRlICYgVmlld1N0YXRlLkNoZWNrUHJvamVjdGVkVmlldykge1xuICAgICAgICAgIGNoZWNrQW5kVXBkYXRlVmlldyh2aWV3KTtcbiAgICAgICAgfSBlbHNlIGlmICh2aWV3U3RhdGUgJiBWaWV3U3RhdGUuQ2hlY2tQcm9qZWN0ZWRWaWV3cykge1xuICAgICAgICAgIGV4ZWNQcm9qZWN0ZWRWaWV3c0FjdGlvbih2aWV3LCBhY3Rpb24pO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBicmVhaztcbiAgICBjYXNlIFZpZXdBY3Rpb24uRGVzdHJveTpcbiAgICAgIC8vIE5vdGU6IGRlc3Ryb3lWaWV3IHJlY3Vyc2VzIG92ZXIgYWxsIHZpZXdzLFxuICAgICAgLy8gc28gd2UgZG9uJ3QgbmVlZCB0byBzcGVjaWFsIGNhc2UgcHJvamVjdGVkIHZpZXdzIGhlcmUuXG4gICAgICBkZXN0cm95Vmlldyh2aWV3KTtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgVmlld0FjdGlvbi5DcmVhdGVWaWV3Tm9kZXM6XG4gICAgICBjcmVhdGVWaWV3Tm9kZXModmlldyk7XG4gICAgICBicmVhaztcbiAgfVxufVxuXG5mdW5jdGlvbiBleGVjUHJvamVjdGVkVmlld3NBY3Rpb24odmlldzogVmlld0RhdGEsIGFjdGlvbjogVmlld0FjdGlvbikge1xuICBleGVjRW1iZWRkZWRWaWV3c0FjdGlvbih2aWV3LCBhY3Rpb24pO1xuICBleGVjQ29tcG9uZW50Vmlld3NBY3Rpb24odmlldywgYWN0aW9uKTtcbn1cblxuZnVuY3Rpb24gZXhlY1F1ZXJpZXNBY3Rpb24oXG4gICAgdmlldzogVmlld0RhdGEsIHF1ZXJ5RmxhZ3M6IE5vZGVGbGFncywgc3RhdGljRHluYW1pY1F1ZXJ5RmxhZzogTm9kZUZsYWdzLFxuICAgIGNoZWNrVHlwZTogQ2hlY2tUeXBlKSB7XG4gIGlmICghKHZpZXcuZGVmLm5vZGVGbGFncyAmIHF1ZXJ5RmxhZ3MpIHx8ICEodmlldy5kZWYubm9kZUZsYWdzICYgc3RhdGljRHluYW1pY1F1ZXJ5RmxhZykpIHtcbiAgICByZXR1cm47XG4gIH1cbiAgY29uc3Qgbm9kZUNvdW50ID0gdmlldy5kZWYubm9kZXMubGVuZ3RoO1xuICBmb3IgKGxldCBpID0gMDsgaSA8IG5vZGVDb3VudDsgaSsrKSB7XG4gICAgY29uc3Qgbm9kZURlZiA9IHZpZXcuZGVmLm5vZGVzW2ldO1xuICAgIGlmICgobm9kZURlZi5mbGFncyAmIHF1ZXJ5RmxhZ3MpICYmIChub2RlRGVmLmZsYWdzICYgc3RhdGljRHluYW1pY1F1ZXJ5RmxhZykpIHtcbiAgICAgIFNlcnZpY2VzLnNldEN1cnJlbnROb2RlKHZpZXcsIG5vZGVEZWYubm9kZUluZGV4KTtcbiAgICAgIHN3aXRjaCAoY2hlY2tUeXBlKSB7XG4gICAgICAgIGNhc2UgQ2hlY2tUeXBlLkNoZWNrQW5kVXBkYXRlOlxuICAgICAgICAgIGNoZWNrQW5kVXBkYXRlUXVlcnkodmlldywgbm9kZURlZik7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2UgQ2hlY2tUeXBlLkNoZWNrTm9DaGFuZ2VzOlxuICAgICAgICAgIGNoZWNrTm9DaGFuZ2VzUXVlcnkodmlldywgbm9kZURlZik7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICB9XG4gICAgfVxuICAgIGlmICghKG5vZGVEZWYuY2hpbGRGbGFncyAmIHF1ZXJ5RmxhZ3MpIHx8ICEobm9kZURlZi5jaGlsZEZsYWdzICYgc3RhdGljRHluYW1pY1F1ZXJ5RmxhZykpIHtcbiAgICAgIC8vIG5vIGNoaWxkIGhhcyBhIG1hdGNoaW5nIHF1ZXJ5XG4gICAgICAvLyB0aGVuIHNraXAgdGhlIGNoaWxkcmVuXG4gICAgICBpICs9IG5vZGVEZWYuY2hpbGRDb3VudDtcbiAgICB9XG4gIH1cbn1cbiJdfQ==