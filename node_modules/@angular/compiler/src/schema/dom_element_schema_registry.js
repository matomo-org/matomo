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
        define("@angular/compiler/src/schema/dom_element_schema_registry", ["require", "exports", "tslib", "@angular/compiler/src/core", "@angular/compiler/src/ml_parser/tags", "@angular/compiler/src/util", "@angular/compiler/src/schema/dom_security_schema", "@angular/compiler/src/schema/element_schema_registry"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DomElementSchemaRegistry = void 0;
    var tslib_1 = require("tslib");
    var core_1 = require("@angular/compiler/src/core");
    var tags_1 = require("@angular/compiler/src/ml_parser/tags");
    var util_1 = require("@angular/compiler/src/util");
    var dom_security_schema_1 = require("@angular/compiler/src/schema/dom_security_schema");
    var element_schema_registry_1 = require("@angular/compiler/src/schema/element_schema_registry");
    var BOOLEAN = 'boolean';
    var NUMBER = 'number';
    var STRING = 'string';
    var OBJECT = 'object';
    /**
     * This array represents the DOM schema. It encodes inheritance, properties, and events.
     *
     * ## Overview
     *
     * Each line represents one kind of element. The `element_inheritance` and properties are joined
     * using `element_inheritance|properties` syntax.
     *
     * ## Element Inheritance
     *
     * The `element_inheritance` can be further subdivided as `element1,element2,...^parentElement`.
     * Here the individual elements are separated by `,` (commas). Every element in the list
     * has identical properties.
     *
     * An `element` may inherit additional properties from `parentElement` If no `^parentElement` is
     * specified then `""` (blank) element is assumed.
     *
     * NOTE: The blank element inherits from root `[Element]` element, the super element of all
     * elements.
     *
     * NOTE an element prefix such as `:svg:` has no special meaning to the schema.
     *
     * ## Properties
     *
     * Each element has a set of properties separated by `,` (commas). Each property can be prefixed
     * by a special character designating its type:
     *
     * - (no prefix): property is a string.
     * - `*`: property represents an event.
     * - `!`: property is a boolean.
     * - `#`: property is a number.
     * - `%`: property is an object.
     *
     * ## Query
     *
     * The class creates an internal squas representation which allows to easily answer the query of
     * if a given property exist on a given element.
     *
     * NOTE: We don't yet support querying for types or events.
     * NOTE: This schema is auto extracted from `schema_extractor.ts` located in the test folder,
     *       see dom_element_schema_registry_spec.ts
     */
    // =================================================================================================
    // =================================================================================================
    // =========== S T O P   -  S T O P   -  S T O P   -  S T O P   -  S T O P   -  S T O P  ===========
    // =================================================================================================
    // =================================================================================================
    //
    //                       DO NOT EDIT THIS DOM SCHEMA WITHOUT A SECURITY REVIEW!
    //
    // Newly added properties must be security reviewed and assigned an appropriate SecurityContext in
    // dom_security_schema.ts. Reach out to mprobst & rjamet for details.
    //
    // =================================================================================================
    var SCHEMA = [
        '[Element]|textContent,%classList,className,id,innerHTML,*beforecopy,*beforecut,*beforepaste,*copy,*cut,*paste,*search,*selectstart,*webkitfullscreenchange,*webkitfullscreenerror,*wheel,outerHTML,#scrollLeft,#scrollTop,slot' +
            /* added manually to avoid breaking changes */
            ',*message,*mozfullscreenchange,*mozfullscreenerror,*mozpointerlockchange,*mozpointerlockerror,*webglcontextcreationerror,*webglcontextlost,*webglcontextrestored',
        '[HTMLElement]^[Element]|accessKey,contentEditable,dir,!draggable,!hidden,innerText,lang,*abort,*auxclick,*blur,*cancel,*canplay,*canplaythrough,*change,*click,*close,*contextmenu,*cuechange,*dblclick,*drag,*dragend,*dragenter,*dragleave,*dragover,*dragstart,*drop,*durationchange,*emptied,*ended,*error,*focus,*gotpointercapture,*input,*invalid,*keydown,*keypress,*keyup,*load,*loadeddata,*loadedmetadata,*loadstart,*lostpointercapture,*mousedown,*mouseenter,*mouseleave,*mousemove,*mouseout,*mouseover,*mouseup,*mousewheel,*pause,*play,*playing,*pointercancel,*pointerdown,*pointerenter,*pointerleave,*pointermove,*pointerout,*pointerover,*pointerup,*progress,*ratechange,*reset,*resize,*scroll,*seeked,*seeking,*select,*show,*stalled,*submit,*suspend,*timeupdate,*toggle,*volumechange,*waiting,outerText,!spellcheck,%style,#tabIndex,title,!translate',
        'abbr,address,article,aside,b,bdi,bdo,cite,code,dd,dfn,dt,em,figcaption,figure,footer,header,i,kbd,main,mark,nav,noscript,rb,rp,rt,rtc,ruby,s,samp,section,small,strong,sub,sup,u,var,wbr^[HTMLElement]|accessKey,contentEditable,dir,!draggable,!hidden,innerText,lang,*abort,*auxclick,*blur,*cancel,*canplay,*canplaythrough,*change,*click,*close,*contextmenu,*cuechange,*dblclick,*drag,*dragend,*dragenter,*dragleave,*dragover,*dragstart,*drop,*durationchange,*emptied,*ended,*error,*focus,*gotpointercapture,*input,*invalid,*keydown,*keypress,*keyup,*load,*loadeddata,*loadedmetadata,*loadstart,*lostpointercapture,*mousedown,*mouseenter,*mouseleave,*mousemove,*mouseout,*mouseover,*mouseup,*mousewheel,*pause,*play,*playing,*pointercancel,*pointerdown,*pointerenter,*pointerleave,*pointermove,*pointerout,*pointerover,*pointerup,*progress,*ratechange,*reset,*resize,*scroll,*seeked,*seeking,*select,*show,*stalled,*submit,*suspend,*timeupdate,*toggle,*volumechange,*waiting,outerText,!spellcheck,%style,#tabIndex,title,!translate',
        'media^[HTMLElement]|!autoplay,!controls,%controlsList,%crossOrigin,#currentTime,!defaultMuted,#defaultPlaybackRate,!disableRemotePlayback,!loop,!muted,*encrypted,*waitingforkey,#playbackRate,preload,src,%srcObject,#volume',
        ':svg:^[HTMLElement]|*abort,*auxclick,*blur,*cancel,*canplay,*canplaythrough,*change,*click,*close,*contextmenu,*cuechange,*dblclick,*drag,*dragend,*dragenter,*dragleave,*dragover,*dragstart,*drop,*durationchange,*emptied,*ended,*error,*focus,*gotpointercapture,*input,*invalid,*keydown,*keypress,*keyup,*load,*loadeddata,*loadedmetadata,*loadstart,*lostpointercapture,*mousedown,*mouseenter,*mouseleave,*mousemove,*mouseout,*mouseover,*mouseup,*mousewheel,*pause,*play,*playing,*pointercancel,*pointerdown,*pointerenter,*pointerleave,*pointermove,*pointerout,*pointerover,*pointerup,*progress,*ratechange,*reset,*resize,*scroll,*seeked,*seeking,*select,*show,*stalled,*submit,*suspend,*timeupdate,*toggle,*volumechange,*waiting,%style,#tabIndex',
        ':svg:graphics^:svg:|',
        ':svg:animation^:svg:|*begin,*end,*repeat',
        ':svg:geometry^:svg:|',
        ':svg:componentTransferFunction^:svg:|',
        ':svg:gradient^:svg:|',
        ':svg:textContent^:svg:graphics|',
        ':svg:textPositioning^:svg:textContent|',
        'a^[HTMLElement]|charset,coords,download,hash,host,hostname,href,hreflang,name,password,pathname,ping,port,protocol,referrerPolicy,rel,rev,search,shape,target,text,type,username',
        'area^[HTMLElement]|alt,coords,download,hash,host,hostname,href,!noHref,password,pathname,ping,port,protocol,referrerPolicy,rel,search,shape,target,username',
        'audio^media|',
        'br^[HTMLElement]|clear',
        'base^[HTMLElement]|href,target',
        'body^[HTMLElement]|aLink,background,bgColor,link,*beforeunload,*blur,*error,*focus,*hashchange,*languagechange,*load,*message,*offline,*online,*pagehide,*pageshow,*popstate,*rejectionhandled,*resize,*scroll,*storage,*unhandledrejection,*unload,text,vLink',
        'button^[HTMLElement]|!autofocus,!disabled,formAction,formEnctype,formMethod,!formNoValidate,formTarget,name,type,value',
        'canvas^[HTMLElement]|#height,#width',
        'content^[HTMLElement]|select',
        'dl^[HTMLElement]|!compact',
        'datalist^[HTMLElement]|',
        'details^[HTMLElement]|!open',
        'dialog^[HTMLElement]|!open,returnValue',
        'dir^[HTMLElement]|!compact',
        'div^[HTMLElement]|align',
        'embed^[HTMLElement]|align,height,name,src,type,width',
        'fieldset^[HTMLElement]|!disabled,name',
        'font^[HTMLElement]|color,face,size',
        'form^[HTMLElement]|acceptCharset,action,autocomplete,encoding,enctype,method,name,!noValidate,target',
        'frame^[HTMLElement]|frameBorder,longDesc,marginHeight,marginWidth,name,!noResize,scrolling,src',
        'frameset^[HTMLElement]|cols,*beforeunload,*blur,*error,*focus,*hashchange,*languagechange,*load,*message,*offline,*online,*pagehide,*pageshow,*popstate,*rejectionhandled,*resize,*scroll,*storage,*unhandledrejection,*unload,rows',
        'hr^[HTMLElement]|align,color,!noShade,size,width',
        'head^[HTMLElement]|',
        'h1,h2,h3,h4,h5,h6^[HTMLElement]|align',
        'html^[HTMLElement]|version',
        'iframe^[HTMLElement]|align,!allowFullscreen,frameBorder,height,longDesc,marginHeight,marginWidth,name,referrerPolicy,%sandbox,scrolling,src,srcdoc,width',
        'img^[HTMLElement]|align,alt,border,%crossOrigin,#height,#hspace,!isMap,longDesc,lowsrc,name,referrerPolicy,sizes,src,srcset,useMap,#vspace,#width',
        'input^[HTMLElement]|accept,align,alt,autocapitalize,autocomplete,!autofocus,!checked,!defaultChecked,defaultValue,dirName,!disabled,%files,formAction,formEnctype,formMethod,!formNoValidate,formTarget,#height,!incremental,!indeterminate,max,#maxLength,min,#minLength,!multiple,name,pattern,placeholder,!readOnly,!required,selectionDirection,#selectionEnd,#selectionStart,#size,src,step,type,useMap,value,%valueAsDate,#valueAsNumber,#width',
        'li^[HTMLElement]|type,#value',
        'label^[HTMLElement]|htmlFor',
        'legend^[HTMLElement]|align',
        'link^[HTMLElement]|as,charset,%crossOrigin,!disabled,href,hreflang,integrity,media,referrerPolicy,rel,%relList,rev,%sizes,target,type',
        'map^[HTMLElement]|name',
        'marquee^[HTMLElement]|behavior,bgColor,direction,height,#hspace,#loop,#scrollAmount,#scrollDelay,!trueSpeed,#vspace,width',
        'menu^[HTMLElement]|!compact',
        'meta^[HTMLElement]|content,httpEquiv,name,scheme',
        'meter^[HTMLElement]|#high,#low,#max,#min,#optimum,#value',
        'ins,del^[HTMLElement]|cite,dateTime',
        'ol^[HTMLElement]|!compact,!reversed,#start,type',
        'object^[HTMLElement]|align,archive,border,code,codeBase,codeType,data,!declare,height,#hspace,name,standby,type,useMap,#vspace,width',
        'optgroup^[HTMLElement]|!disabled,label',
        'option^[HTMLElement]|!defaultSelected,!disabled,label,!selected,text,value',
        'output^[HTMLElement]|defaultValue,%htmlFor,name,value',
        'p^[HTMLElement]|align',
        'param^[HTMLElement]|name,type,value,valueType',
        'picture^[HTMLElement]|',
        'pre^[HTMLElement]|#width',
        'progress^[HTMLElement]|#max,#value',
        'q,blockquote,cite^[HTMLElement]|',
        'script^[HTMLElement]|!async,charset,%crossOrigin,!defer,event,htmlFor,integrity,src,text,type',
        'select^[HTMLElement]|autocomplete,!autofocus,!disabled,#length,!multiple,name,!required,#selectedIndex,#size,value',
        'shadow^[HTMLElement]|',
        'slot^[HTMLElement]|name',
        'source^[HTMLElement]|media,sizes,src,srcset,type',
        'span^[HTMLElement]|',
        'style^[HTMLElement]|!disabled,media,type',
        'caption^[HTMLElement]|align',
        'th,td^[HTMLElement]|abbr,align,axis,bgColor,ch,chOff,#colSpan,headers,height,!noWrap,#rowSpan,scope,vAlign,width',
        'col,colgroup^[HTMLElement]|align,ch,chOff,#span,vAlign,width',
        'table^[HTMLElement]|align,bgColor,border,%caption,cellPadding,cellSpacing,frame,rules,summary,%tFoot,%tHead,width',
        'tr^[HTMLElement]|align,bgColor,ch,chOff,vAlign',
        'tfoot,thead,tbody^[HTMLElement]|align,ch,chOff,vAlign',
        'template^[HTMLElement]|',
        'textarea^[HTMLElement]|autocapitalize,autocomplete,!autofocus,#cols,defaultValue,dirName,!disabled,#maxLength,#minLength,name,placeholder,!readOnly,!required,#rows,selectionDirection,#selectionEnd,#selectionStart,value,wrap',
        'title^[HTMLElement]|text',
        'track^[HTMLElement]|!default,kind,label,src,srclang',
        'ul^[HTMLElement]|!compact,type',
        'unknown^[HTMLElement]|',
        'video^media|#height,poster,#width',
        ':svg:a^:svg:graphics|',
        ':svg:animate^:svg:animation|',
        ':svg:animateMotion^:svg:animation|',
        ':svg:animateTransform^:svg:animation|',
        ':svg:circle^:svg:geometry|',
        ':svg:clipPath^:svg:graphics|',
        ':svg:defs^:svg:graphics|',
        ':svg:desc^:svg:|',
        ':svg:discard^:svg:|',
        ':svg:ellipse^:svg:geometry|',
        ':svg:feBlend^:svg:|',
        ':svg:feColorMatrix^:svg:|',
        ':svg:feComponentTransfer^:svg:|',
        ':svg:feComposite^:svg:|',
        ':svg:feConvolveMatrix^:svg:|',
        ':svg:feDiffuseLighting^:svg:|',
        ':svg:feDisplacementMap^:svg:|',
        ':svg:feDistantLight^:svg:|',
        ':svg:feDropShadow^:svg:|',
        ':svg:feFlood^:svg:|',
        ':svg:feFuncA^:svg:componentTransferFunction|',
        ':svg:feFuncB^:svg:componentTransferFunction|',
        ':svg:feFuncG^:svg:componentTransferFunction|',
        ':svg:feFuncR^:svg:componentTransferFunction|',
        ':svg:feGaussianBlur^:svg:|',
        ':svg:feImage^:svg:|',
        ':svg:feMerge^:svg:|',
        ':svg:feMergeNode^:svg:|',
        ':svg:feMorphology^:svg:|',
        ':svg:feOffset^:svg:|',
        ':svg:fePointLight^:svg:|',
        ':svg:feSpecularLighting^:svg:|',
        ':svg:feSpotLight^:svg:|',
        ':svg:feTile^:svg:|',
        ':svg:feTurbulence^:svg:|',
        ':svg:filter^:svg:|',
        ':svg:foreignObject^:svg:graphics|',
        ':svg:g^:svg:graphics|',
        ':svg:image^:svg:graphics|',
        ':svg:line^:svg:geometry|',
        ':svg:linearGradient^:svg:gradient|',
        ':svg:mpath^:svg:|',
        ':svg:marker^:svg:|',
        ':svg:mask^:svg:|',
        ':svg:metadata^:svg:|',
        ':svg:path^:svg:geometry|',
        ':svg:pattern^:svg:|',
        ':svg:polygon^:svg:geometry|',
        ':svg:polyline^:svg:geometry|',
        ':svg:radialGradient^:svg:gradient|',
        ':svg:rect^:svg:geometry|',
        ':svg:svg^:svg:graphics|#currentScale,#zoomAndPan',
        ':svg:script^:svg:|type',
        ':svg:set^:svg:animation|',
        ':svg:stop^:svg:|',
        ':svg:style^:svg:|!disabled,media,title,type',
        ':svg:switch^:svg:graphics|',
        ':svg:symbol^:svg:|',
        ':svg:tspan^:svg:textPositioning|',
        ':svg:text^:svg:textPositioning|',
        ':svg:textPath^:svg:textContent|',
        ':svg:title^:svg:|',
        ':svg:use^:svg:graphics|',
        ':svg:view^:svg:|#zoomAndPan',
        'data^[HTMLElement]|value',
        'keygen^[HTMLElement]|!autofocus,challenge,!disabled,form,keytype,name',
        'menuitem^[HTMLElement]|type,label,icon,!disabled,!checked,radiogroup,!default',
        'summary^[HTMLElement]|',
        'time^[HTMLElement]|dateTime',
        ':svg:cursor^:svg:|',
    ];
    var _ATTR_TO_PROP = {
        'class': 'className',
        'for': 'htmlFor',
        'formaction': 'formAction',
        'innerHtml': 'innerHTML',
        'readonly': 'readOnly',
        'tabindex': 'tabIndex',
    };
    // Invert _ATTR_TO_PROP.
    var _PROP_TO_ATTR = Object.keys(_ATTR_TO_PROP).reduce(function (inverted, attr) {
        inverted[_ATTR_TO_PROP[attr]] = attr;
        return inverted;
    }, {});
    var DomElementSchemaRegistry = /** @class */ (function (_super) {
        tslib_1.__extends(DomElementSchemaRegistry, _super);
        function DomElementSchemaRegistry() {
            var _this = _super.call(this) || this;
            _this._schema = {};
            SCHEMA.forEach(function (encodedType) {
                var type = {};
                var _a = tslib_1.__read(encodedType.split('|'), 2), strType = _a[0], strProperties = _a[1];
                var properties = strProperties.split(',');
                var _b = tslib_1.__read(strType.split('^'), 2), typeNames = _b[0], superName = _b[1];
                typeNames.split(',').forEach(function (tag) { return _this._schema[tag.toLowerCase()] = type; });
                var superType = superName && _this._schema[superName.toLowerCase()];
                if (superType) {
                    Object.keys(superType).forEach(function (prop) {
                        type[prop] = superType[prop];
                    });
                }
                properties.forEach(function (property) {
                    if (property.length > 0) {
                        switch (property[0]) {
                            case '*':
                                // We don't yet support events.
                                // If ever allowing to bind to events, GO THROUGH A SECURITY REVIEW, allowing events
                                // will
                                // almost certainly introduce bad XSS vulnerabilities.
                                // type[property.substring(1)] = EVENT;
                                break;
                            case '!':
                                type[property.substring(1)] = BOOLEAN;
                                break;
                            case '#':
                                type[property.substring(1)] = NUMBER;
                                break;
                            case '%':
                                type[property.substring(1)] = OBJECT;
                                break;
                            default:
                                type[property] = STRING;
                        }
                    }
                });
            });
            return _this;
        }
        DomElementSchemaRegistry.prototype.hasProperty = function (tagName, propName, schemaMetas) {
            if (schemaMetas.some(function (schema) { return schema.name === core_1.NO_ERRORS_SCHEMA.name; })) {
                return true;
            }
            if (tagName.indexOf('-') > -1) {
                if (tags_1.isNgContainer(tagName) || tags_1.isNgContent(tagName)) {
                    return false;
                }
                if (schemaMetas.some(function (schema) { return schema.name === core_1.CUSTOM_ELEMENTS_SCHEMA.name; })) {
                    // Can't tell now as we don't know which properties a custom element will get
                    // once it is instantiated
                    return true;
                }
            }
            var elementProperties = this._schema[tagName.toLowerCase()] || this._schema['unknown'];
            return !!elementProperties[propName];
        };
        DomElementSchemaRegistry.prototype.hasElement = function (tagName, schemaMetas) {
            if (schemaMetas.some(function (schema) { return schema.name === core_1.NO_ERRORS_SCHEMA.name; })) {
                return true;
            }
            if (tagName.indexOf('-') > -1) {
                if (tags_1.isNgContainer(tagName) || tags_1.isNgContent(tagName)) {
                    return true;
                }
                if (schemaMetas.some(function (schema) { return schema.name === core_1.CUSTOM_ELEMENTS_SCHEMA.name; })) {
                    // Allow any custom elements
                    return true;
                }
            }
            return !!this._schema[tagName.toLowerCase()];
        };
        /**
         * securityContext returns the security context for the given property on the given DOM tag.
         *
         * Tag and property name are statically known and cannot change at runtime, i.e. it is not
         * possible to bind a value into a changing attribute or tag name.
         *
         * The filtering is based on a list of allowed tags|attributes. All attributes in the schema
         * above are assumed to have the 'NONE' security context, i.e. that they are safe inert
         * string values. Only specific well known attack vectors are assigned their appropriate context.
         */
        DomElementSchemaRegistry.prototype.securityContext = function (tagName, propName, isAttribute) {
            if (isAttribute) {
                // NB: For security purposes, use the mapped property name, not the attribute name.
                propName = this.getMappedPropName(propName);
            }
            // Make sure comparisons are case insensitive, so that case differences between attribute and
            // property names do not have a security impact.
            tagName = tagName.toLowerCase();
            propName = propName.toLowerCase();
            var ctx = dom_security_schema_1.SECURITY_SCHEMA()[tagName + '|' + propName];
            if (ctx) {
                return ctx;
            }
            ctx = dom_security_schema_1.SECURITY_SCHEMA()['*|' + propName];
            return ctx ? ctx : core_1.SecurityContext.NONE;
        };
        DomElementSchemaRegistry.prototype.getMappedPropName = function (propName) {
            return _ATTR_TO_PROP[propName] || propName;
        };
        DomElementSchemaRegistry.prototype.getDefaultComponentElementName = function () {
            return 'ng-component';
        };
        DomElementSchemaRegistry.prototype.validateProperty = function (name) {
            if (name.toLowerCase().startsWith('on')) {
                var msg = "Binding to event property '" + name + "' is disallowed for security reasons, " +
                    ("please use (" + name.slice(2) + ")=...") +
                    ("\nIf '" + name + "' is a directive input, make sure the directive is imported by the") +
                    " current module.";
                return { error: true, msg: msg };
            }
            else {
                return { error: false };
            }
        };
        DomElementSchemaRegistry.prototype.validateAttribute = function (name) {
            if (name.toLowerCase().startsWith('on')) {
                var msg = "Binding to event attribute '" + name + "' is disallowed for security reasons, " +
                    ("please use (" + name.slice(2) + ")=...");
                return { error: true, msg: msg };
            }
            else {
                return { error: false };
            }
        };
        DomElementSchemaRegistry.prototype.allKnownElementNames = function () {
            return Object.keys(this._schema);
        };
        DomElementSchemaRegistry.prototype.allKnownAttributesOfElement = function (tagName) {
            var elementProperties = this._schema[tagName.toLowerCase()] || this._schema['unknown'];
            // Convert properties to attributes.
            return Object.keys(elementProperties).map(function (prop) { var _a; return (_a = _PROP_TO_ATTR[prop]) !== null && _a !== void 0 ? _a : prop; });
        };
        DomElementSchemaRegistry.prototype.normalizeAnimationStyleProperty = function (propName) {
            return util_1.dashCaseToCamelCase(propName);
        };
        DomElementSchemaRegistry.prototype.normalizeAnimationStyleValue = function (camelCaseProp, userProvidedProp, val) {
            var unit = '';
            var strVal = val.toString().trim();
            var errorMsg = null;
            if (_isPixelDimensionStyle(camelCaseProp) && val !== 0 && val !== '0') {
                if (typeof val === 'number') {
                    unit = 'px';
                }
                else {
                    var valAndSuffixMatch = val.match(/^[+-]?[\d\.]+([a-z]*)$/);
                    if (valAndSuffixMatch && valAndSuffixMatch[1].length == 0) {
                        errorMsg = "Please provide a CSS unit value for " + userProvidedProp + ":" + val;
                    }
                }
            }
            return { error: errorMsg, value: strVal + unit };
        };
        return DomElementSchemaRegistry;
    }(element_schema_registry_1.ElementSchemaRegistry));
    exports.DomElementSchemaRegistry = DomElementSchemaRegistry;
    function _isPixelDimensionStyle(prop) {
        switch (prop) {
            case 'width':
            case 'height':
            case 'minWidth':
            case 'minHeight':
            case 'maxWidth':
            case 'maxHeight':
            case 'left':
            case 'top':
            case 'bottom':
            case 'right':
            case 'fontSize':
            case 'outlineWidth':
            case 'outlineOffset':
            case 'paddingTop':
            case 'paddingLeft':
            case 'paddingBottom':
            case 'paddingRight':
            case 'marginTop':
            case 'marginLeft':
            case 'marginBottom':
            case 'marginRight':
            case 'borderRadius':
            case 'borderWidth':
            case 'borderTopWidth':
            case 'borderLeftWidth':
            case 'borderRightWidth':
            case 'borderBottomWidth':
            case 'textIndent':
                return true;
            default:
                return false;
        }
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG9tX2VsZW1lbnRfc2NoZW1hX3JlZ2lzdHJ5LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3NjaGVtYS9kb21fZWxlbWVudF9zY2hlbWFfcmVnaXN0cnkudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVILG1EQUFrRztJQUVsRyw2REFBNkQ7SUFDN0QsbURBQTRDO0lBRTVDLHdGQUFzRDtJQUN0RCxnR0FBZ0U7SUFFaEUsSUFBTSxPQUFPLEdBQUcsU0FBUyxDQUFDO0lBQzFCLElBQU0sTUFBTSxHQUFHLFFBQVEsQ0FBQztJQUN4QixJQUFNLE1BQU0sR0FBRyxRQUFRLENBQUM7SUFDeEIsSUFBTSxNQUFNLEdBQUcsUUFBUSxDQUFDO0lBRXhCOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztPQXlDRztJQUVILG9HQUFvRztJQUNwRyxvR0FBb0c7SUFDcEcsb0dBQW9HO0lBQ3BHLG9HQUFvRztJQUNwRyxvR0FBb0c7SUFDcEcsRUFBRTtJQUNGLCtFQUErRTtJQUMvRSxFQUFFO0lBQ0Ysa0dBQWtHO0lBQ2xHLHFFQUFxRTtJQUNyRSxFQUFFO0lBQ0Ysb0dBQW9HO0lBRXBHLElBQU0sTUFBTSxHQUFhO1FBQ3ZCLGdPQUFnTztZQUM1Tiw4Q0FBOEM7WUFDOUMsa0tBQWtLO1FBQ3RLLHExQkFBcTFCO1FBQ3IxQixvZ0NBQW9nQztRQUNwZ0MsK05BQStOO1FBQy9OLDB1QkFBMHVCO1FBQzF1QixzQkFBc0I7UUFDdEIsMENBQTBDO1FBQzFDLHNCQUFzQjtRQUN0Qix1Q0FBdUM7UUFDdkMsc0JBQXNCO1FBQ3RCLGlDQUFpQztRQUNqQyx3Q0FBd0M7UUFDeEMsa0xBQWtMO1FBQ2xMLDZKQUE2SjtRQUM3SixjQUFjO1FBQ2Qsd0JBQXdCO1FBQ3hCLGdDQUFnQztRQUNoQyxnUUFBZ1E7UUFDaFEsd0hBQXdI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QUFDLElBQVk7UUFDMUMsUUFBUSxJQUFJLEVBQUU7WUFDWixLQUFLLE9BQU8sQ0FBQztZQUNiLEtBQUssUUFBUSxDQUFDO1lBQ2QsS0FBSyxVQUFVLENBQUM7WUFDaEIsS0FBSyxXQUFXLENBQUM7WUFDakIsS0FBSyxVQUFVLENBQUM7WUFDaEIsS0FBSyxXQUFXLENBQUM7WUFDakIsS0FBSyxNQUFNLENBQUM7WUFDWixLQUFLLEtBQUssQ0FBQztZQUNYLEtBQUssUUFBUSxDQUFDO1lBQ2QsS0FBSyxPQUFPLENBQUM7WUFDYixLQUFLLFVBQVUsQ0FBQztZQUNoQixLQUFLLGNBQWMsQ0FBQztZQUNwQixLQUFLLGVBQWUsQ0FBQztZQUNyQixLQUFLLFlBQVksQ0FBQztZQUNsQixLQUFLLGFBQWEsQ0FBQztZQUNuQixLQUFLLGVBQWUsQ0FBQztZQUNyQixLQUFLLGNBQWMsQ0FBQztZQUNwQixLQUFLLFdBQVcsQ0FBQztZQUNqQixLQUFLLFlBQVksQ0FBQztZQUNsQixLQUFLLGNBQWMsQ0FBQztZQUNwQixLQUFLLGFBQWEsQ0FBQztZQUNuQixLQUFLLGNBQWMsQ0FBQztZQUNwQixLQUFLLGFBQWEsQ0FBQztZQUNuQixLQUFLLGdCQUFnQixDQUFDO1lBQ3RCLEtBQUssaUJBQWlCLENBQUM7WUFDdkIsS0FBSyxrQkFBa0IsQ0FBQztZQUN4QixLQUFLLG1CQUFtQixDQUFDO1lBQ3pCLEtBQUssWUFBWTtnQkFDZixPQUFPLElBQUksQ0FBQztZQUVkO2dCQUNFLE9BQU8sS0FBSyxDQUFDO1NBQ2hCO0lBQ0gsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0NVU1RPTV9FTEVNRU5UU19TQ0hFTUEsIE5PX0VSUk9SU19TQ0hFTUEsIFNjaGVtYU1ldGFkYXRhLCBTZWN1cml0eUNvbnRleHR9IGZyb20gJy4uL2NvcmUnO1xuXG5pbXBvcnQge2lzTmdDb250YWluZXIsIGlzTmdDb250ZW50fSBmcm9tICcuLi9tbF9wYXJzZXIvdGFncyc7XG5pbXBvcnQge2Rhc2hDYXNlVG9DYW1lbENhc2V9IGZyb20gJy4uL3V0aWwnO1xuXG5pbXBvcnQge1NFQ1VSSVRZX1NDSEVNQX0gZnJvbSAnLi9kb21fc2VjdXJpdHlfc2NoZW1hJztcbmltcG9ydCB7RWxlbWVudFNjaGVtYVJlZ2lzdHJ5fSBmcm9tICcuL2VsZW1lbnRfc2NoZW1hX3JlZ2lzdHJ5JztcblxuY29uc3QgQk9PTEVBTiA9ICdib29sZWFuJztcbmNvbnN0IE5VTUJFUiA9ICdudW1iZXInO1xuY29uc3QgU1RSSU5HID0gJ3N0cmluZyc7XG5jb25zdCBPQkpFQ1QgPSAnb2JqZWN0JztcblxuLyoqXG4gKiBUaGlzIGFycmF5IHJlcHJlc2VudHMgdGhlIERPTSBzY2hlbWEuIEl0IGVuY29kZXMgaW5oZXJpdGFuY2UsIHByb3BlcnRpZXMsIGFuZCBldmVudHMuXG4gKlxuICogIyMgT3ZlcnZpZXdcbiAqXG4gKiBFYWNoIGxpbmUgcmVwcmVzZW50cyBvbmUga2luZCBvZiBlbGVtZW50LiBUaGUgYGVsZW1lbnRfaW5oZXJpdGFuY2VgIGFuZCBwcm9wZXJ0aWVzIGFyZSBqb2luZWRcbiAqIHVzaW5nIGBlbGVtZW50X2luaGVyaXRhbmNlfHByb3BlcnRpZXNgIHN5bnRheC5cbiAqXG4gKiAjIyBFbGVtZW50IEluaGVyaXRhbmNlXG4gKlxuICogVGhlIGBlbGVtZW50X2luaGVyaXRhbmNlYCBjYW4gYmUgZnVydGhlciBzdWJkaXZpZGVkIGFzIGBlbGVtZW50MSxlbGVtZW50MiwuLi5ecGFyZW50RWxlbWVudGAuXG4gKiBIZXJlIHRoZSBpbmRpdmlkdWFsIGVsZW1lbnRzIGFyZSBzZXBhcmF0ZWQgYnkgYCxgIChjb21tYXMpLiBFdmVyeSBlbGVtZW50IGluIHRoZSBsaXN0XG4gKiBoYXMgaWRlbnRpY2FsIHByb3BlcnRpZXMuXG4gKlxuICogQW4gYGVsZW1lbnRgIG1heSBpbmhlcml0IGFkZGl0aW9uYWwgcHJvcGVydGllcyBmcm9tIGBwYXJlbnRFbGVtZW50YCBJZiBubyBgXnBhcmVudEVsZW1lbnRgIGlzXG4gKiBzcGVjaWZpZWQgdGhlbiBgXCJcImAgKGJsYW5rKSBlbGVtZW50IGlzIGFzc3VtZWQuXG4gKlxuICogTk9URTogVGhlIGJsYW5rIGVsZW1lbnQgaW5oZXJpdHMgZnJvbSByb290IGBbRWxlbWVudF1gIGVsZW1lbnQsIHRoZSBzdXBlciBlbGVtZW50IG9mIGFsbFxuICogZWxlbWVudHMuXG4gKlxuICogTk9URSBhbiBlbGVtZW50IHByZWZpeCBzdWNoIGFzIGA6c3ZnOmAgaGFzIG5vIHNwZWNpYWwgbWVhbmluZyB0byB0aGUgc2NoZW1hLlxuICpcbiAqICMjIFByb3BlcnRpZXNcbiAqXG4gKiBFYWNoIGVsZW1lbnQgaGFzIGEgc2V0IG9mIHByb3BlcnRpZXMgc2VwYXJhdGVkIGJ5IGAsYCAoY29tbWFzKS4gRWFjaCBwcm9wZXJ0eSBjYW4gYmUgcHJlZml4ZWRcbiAqIGJ5IGEgc3BlY2lhbCBjaGFyYWN0ZXIgZGVzaWduYXRpbmcgaXRzIHR5cGU6XG4gKlxuICogLSAobm8gcHJlZml4KTogcHJvcGVydHkgaXMgYSBzdHJpbmcuXG4gKiAtIGAqYDogcHJvcGVydHkgcmVwcmVzZW50cyBhbiBldmVudC5cbiAqIC0gYCFgOiBwcm9wZXJ0eSBpcyBhIGJvb2xlYW4uXG4gKiAtIGAjYDogcHJvcGVydHkgaXMgYSBudW1iZXIuXG4gKiAtIGAlYDogcHJvcGVydHkgaXMgYW4gb2JqZWN0LlxuICpcbiAqICMjIFF1ZXJ5XG4gKlxuICogVGhlIGNsYXNzIGNyZWF0ZXMgYW4gaW50ZXJuYWwgc3F1YXMgcmVwcmVzZW50YXRpb24gd2hpY2ggYWxsb3dzIHRvIGVhc2lseSBhbnN3ZXIgdGhlIHF1ZXJ5IG9mXG4gKiBpZiBhIGdpdmVuIHByb3BlcnR5IGV4aXN0IG9uIGEgZ2l2ZW4gZWxlbWVudC5cbiAqXG4gKiBOT1RFOiBXZSBkb24ndCB5ZXQgc3VwcG9ydCBxdWVyeWluZyBmb3IgdHlwZXMgb3IgZXZlbnRzLlxuICogTk9URTogVGhpcyBzY2hlbWEgaXMgYXV0byBleHRyYWN0ZWQgZnJvbSBgc2NoZW1hX2V4dHJhY3Rvci50c2AgbG9jYXRlZCBpbiB0aGUgdGVzdCBmb2xkZXIsXG4gKiAgICAgICBzZWUgZG9tX2VsZW1lbnRfc2NoZW1hX3JlZ2lzdHJ5X3NwZWMudHNcbiAqL1xuXG4vLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4vLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4vLyA9PT09PT09PT09PSBTIFQgTyBQICAgLSAgUyBUIE8gUCAgIC0gIFMgVCBPIFAgICAtICBTIFQgTyBQICAgLSAgUyBUIE8gUCAgIC0gIFMgVCBPIFAgID09PT09PT09PT09XG4vLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4vLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4vL1xuLy8gICAgICAgICAgICAgICAgICAgICAgIERPIE5PVCBFRElUIFRISVMgRE9NIFNDSEVNQSBXSVRIT1VUIEEgU0VDVVJJVFkgUkVWSUVXIVxuLy9cbi8vIE5ld2x5IGFkZGVkIHByb3BlcnRpZXMgbXVzdCBiZSBzZWN1cml0eSByZXZpZXdlZCBhbmQgYXNzaWduZWQgYW4gYXBwcm9wcmlhdGUgU2VjdXJpdHlDb250ZXh0IGluXG4vLyBkb21fc2VjdXJpdHlfc2NoZW1hLnRzLiBSZWFjaCBvdXQgdG8gbXByb2JzdCAmIHJqYW1ldCBmb3IgZGV0YWlscy5cbi8vXG4vLyA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cbmNvbnN0IFNDSEVNQTogc3RyaW5nW10gPSBbXG4gICdbRWxlbWVudF18dGV4dENvbnRlbnQsJWNsYXNzTGlzdCxjbGFzc05hbWUsaWQsaW5uZXJIVE1MLCpiZWZvcmVjb3B5LCpiZWZvcmVjdXQsKmJlZm9yZXBhc3RlLCpjb3B5LCpjdXQsKnBhc3RlLCpzZWFyY2gsKnNlbGVjdHN0YXJ0LCp3ZWJraXRmdWxsc2NyZWVuY2hhbmdlLCp3ZWJraXRmdWxsc2NyZWVuZXJyb3IsKndoZWVsLG91dGVySFRNTCwjc2Nyb2xsTGVmdCwjc2Nyb2xsVG9wLHNsb3QnICtcbiAgICAgIC8qIGFkZGVkIG1hbnVhbGx5IHRvIGF2b2lkIGJyZWFraW5nIGNoYW5nZXMgKi9cbiAgICAgICcsKm1lc3NhZ2UsKm1vemZ1bGxzY3JlZW5jaGFuZ2UsKm1vemZ1bGxzY3JlZW5lcnJvciwqbW96cG9pbnRlcmxvY2tjaGFuZ2UsKm1venBvaW50ZXJsb2NrZXJyb3IsKndlYmdsY29udGV4dGNyZWF0aW9uZXJyb3IsKndlYmdsY29udGV4dGxvc3QsKndlYmdsY29udGV4dHJlc3RvcmVkJyxcbiAgJ1tIVE1MRWxlbWVudF1eW0VsZW1lbnRdfGFjY2Vzc0tleSxjb250ZW50RWRpdGFibGUsZGlyLCFkcmFnZ2FibGUsIWhpZGRlbixpbm5lclRleHQsbGFuZywqYWJvcnQsKmF1eGNsaWNrLCpibHVyLCpjYW5jZWwsKmNhbnBsYXksKmNhbnBsYXl0aHJvdWdoLCpjaGFuZ2UsKmNsaWNrLCpjbG9zZSwqY29udGV4dG1lbnUsKmN1ZWNoYW5nZSwqZGJsY2xpY2ssKmRyYWcsKmRyYWdlbmQsKmRyYWdlbnRlciwqZHJhZ2xlYXZlLCpkcmFnb3ZlciwqZHJhZ3N0YXJ0LCpkcm9wLCpkdXJhdGlvbmNoYW5nZSwqZW1wdGllZCwqZW5kZWQsKmVycm9yLCpmb2N1cywqZ290cG9pbnRlcmNhcHR1cmUsKmlucHV0LCppbnZhbGlkLCprZXlkb3duLCprZXlwcmVzcywqa2V5dXAsKmxvYWQsKmxvYWRlZGRhdGEsKmxvYWRlZG1ldGFkYXRhLCpsb2Fkc3RhcnQsKmxvc3Rwb2ludGVyY2FwdHVyZSwqbW91c2Vkb3duLCptb3VzZWVudGVyLCptb3VzZWxlYXZlLCptb3VzZW1vdmUsKm1vdXNlb3V0LCptb3VzZW92ZXIsKm1vdXNldXAsKm1vdXNld2hlZWwsKnBhdXNlLCpwbGF5LCpwbGF5aW5nLCpwb2ludGVyY2FuY2VsLCpwb2ludGVyZG93biwqcG9pbnRlcmVudGVyLCpwb2ludGVybGVhdmUsKnBvaW50ZXJtb3ZlLCpwb2ludGVyb3V0LCpwb2ludGVyb3ZlciwqcG9pbnRlcnVwLCpwcm9ncmVzcywqcmF0ZWNoYW5nZSwqcmVzZXQsKnJlc2l6ZSwqc2Nyb2xsLCpzZWVrZWQsKnNlZWtpbmcsKnNlbGVjdCwqc2hvdywqc3RhbGxlZCwqc3VibWl0LCpzdXNwZW5kLCp0aW1ldXBkYXRlLCp0b2dnbGUsKnZvbHVtZWNoYW5nZSwqd2FpdGluZyxvdXRlclRleHQsIXNwZWxsY2hlY2ssJXN0eWxlLCN0YWJJbmRleCx0aXRsZSwhdHJhbnNsYXRlJyxcbiAgJ2FiYnIsYWRkcmVzcyxhcnRpY2xlLGFzaWRlLGIsYmRpLGJkbyxjaXRlLGNvZGUsZGQsZGZuLGR0LGVtLGZpZ2NhcHRpb24sZmlndXJlLGZvb3RlcixoZWFkZXIsaSxrYmQsbWFpbixtYXJrLG5hdixub3NjcmlwdCxyYixycCxydCxydGMscnVieSxzLHNhbXAsc2VjdGlvbixzbWFsbCxzdHJvbmcsc3ViLHN1cCx1LHZhcix3YnJeW0hUTUxFbGVtZW50XXxhY2Nlc3NLZXksY29udGVudEVkaXRhYmxlLGRpciwhZHJhZ2dhYmxlLCFoaWRkZW4saW5uZXJUZXh0LGxhbmcsKmFib3J0LCphdXhjbGljaywqYmx1ciwqY2FuY2VsLCpjYW5wbGF5LCpjYW5wbGF5dGhyb3VnaCwqY2hhbmdlLCpjbGljaywqY2xvc2UsKmNvbnRleHRtZW51LCpjdWVjaGFuZ2UsKmRibGNsaWNrLCpkcmFnLCpkcmFnZW5kLCpkcmFnZW50ZXIsKmRyYWdsZWF2ZSwqZHJhZ292ZXIsKmRyYWdzdGFydCwqZHJvcCwqZHVyYXRpb25jaGFuZ2UsKmVtcHRpZWQsKmVuZGVkLCplcnJvciwqZm9jdXMsKmdvdHBvaW50ZXJjYXB0dXJlLCppbnB1dCwqaW52YWxpZCwqa2V5ZG93biwqa2V5cHJlc3MsKmtleXVwLCpsb2FkLCpsb2FkZWRkYXRhLCpsb2FkZWRtZXRhZGF0YSwqbG9hZHN0YXJ0LCpsb3N0cG9pbnRlcmNhcHR1cmUsKm1vdXNlZG93biwqbW91c2VlbnRlciwqbW91c2VsZWF2ZSwqbW91c2Vtb3ZlLCptb3VzZW91dCwqbW91c2VvdmVyLCptb3VzZXVwLCptb3VzZXdoZWVsLCpwYXVzZSwqcGxheSwqcGxheWluZywqcG9pbnRlcmNhbmNlbCwqcG9pbnRlcmRvd24sKnBvaW50ZXJlbnRlciwqcG9pbnRlcmxlYXZlLCpwb2ludGVybW92ZSwqcG9pbnRlcm91dCwqcG9pbnRlcm92ZXIsKnBvaW50ZXJ1cCwqcHJvZ3Jlc3MsKnJhdGVjaGFuZ2UsKnJlc2V0LCpyZXNpemUsKnNjcm9sbCwqc2Vla2VkLCpzZWVraW5nLCpzZWxlY3QsKnNob3csKnN0YWxsZWQsKnN1Ym1pdCwqc3VzcGVuZCwqdGltZXVwZGF0ZSwqdG9nZ2xlLCp2b2x1bWVjaGFuZ2UsKndhaXRpbmcsb3V0ZXJUZXh0LCFzcGVsbGNoZWNrLCVzdHlsZSwjdGFiSW5kZXgsdGl0bGUsIXRyYW5zbGF0ZScsXG4gICdtZWRpYV5bSFRNTEVsZW1lbnRdfCFhdXRvcGxheSwhY29udHJvbHMsJWNvbnRyb2xzTGlzdCwlY3Jvc3NPcmlnaW4sI2N1cnJlbnRUaW1lLCFkZWZhdWx0TXV0ZWQsI2RlZmF1bHRQbGF5YmFja1JhdGUsIWRpc2FibGVSZW1vdGVQbGF5YmFjaywhbG9vcCwhbXV0ZWQsKmVuY3J5cHRlZCwqd2FpdGluZ2ZvcmtleSwjcGxheWJhY2tSYXRlLHByZWxvYWQsc3JjLCVzcmNPYmplY3QsI3ZvbHVtZScsXG4gICc6c3ZnOl5bSFRNTEVsZW1lbnRdfCphYm9ydCwqYXV4Y2xpY2ssKmJsdXIsKmNhbmNlbCwqY2FucGxheSwqY2FucGxheXRocm91Z2gsKmNoYW5nZSwqY2xpY2ssKmNsb3NlLCpjb250ZXh0bWVudSwqY3VlY2hhbmdlLCpkYmxjbGljaywqZHJhZywqZHJhZ2VuZCwqZHJhZ2VudGVyLCpkcmFnbGVhdmUsKmRyYWdvdmVyLCpkcmFnc3RhcnQsKmRyb3AsKmR1cmF0aW9uY2hhbmdlLCplbXB0aWVkLCplbmRlZCwqZXJyb3IsKmZvY3VzLCpnb3Rwb2ludGVyY2FwdHVyZSwqaW5wdXQsKmludmFsaWQsKmtleWRvd24sKmtleXByZXNzLCprZXl1cCwqbG9hZCwqbG9hZGVkZGF0YSwqbG9hZGVkbWV0YWRhdGEsKmxvYWRzdGFydCwqbG9zdHBvaW50ZXJjYXB0dXJlLCptb3VzZWRvd24sKm1vdXNlZW50ZXIsKm1vdXNlbGVhdmUsKm1vdXNlbW92ZSwqbW91c2VvdXQsKm1vdXNlb3ZlciwqbW91c2V1cCwqbW91c2V3aGVlbCwqcGF1c2UsKnBsYXksKnBsYXlpbmcsKnBvaW50ZXJjYW5jZWwsKnBvaW50ZXJkb3duLCpwb2ludGVyZW50ZXIsKnBvaW50ZXJsZWF2ZSwqcG9pbnRlcm1vdmUsKnBvaW50ZXJvdXQsKnBvaW50ZXJvdmVyLCpwb2ludGVydXAsKnByb2dyZXNzLCpyYXRlY2hhbmdlLCpyZXNldCwqcmVzaXplLCpzY3JvbGwsKnNlZWtlZCwqc2Vla2luZywqc2VsZWN0LCpzaG93LCpzdGFsbGVkLCpzdWJtaXQsKnN1c3BlbmQsKnRpbWV1cGRhdGUsKnRvZ2dsZSwqdm9sdW1lY2hhbmdlLCp3YWl0aW5nLCVzdHlsZSwjdGFiSW5kZXgnLFxuICAnOnN2ZzpncmFwaGljc146c3ZnOnwnLFxuICAnOnN2ZzphbmltYXRpb25eOnN2Zzp8KmJlZ2luLCplbmQsKnJlcGVhdCcsXG4gICc6c3ZnOmdlb21ldHJ5Xjpzdmc6fCcsXG4gICc6c3ZnOmNvbXBvbmVudFRyYW5zZmVyRnVuY3Rpb25eOnN2Zzp8JyxcbiAgJzpzdmc6Z3JhZGllbnReOnN2Zzp8JyxcbiAgJzpzdmc6dGV4dENvbnRlbnReOnN2ZzpncmFwaGljc3wnLFxuICAnOnN2Zzp0ZXh0UG9zaXRpb25pbmdeOnN2Zzp0ZXh0Q29udGVudHwnLFxuICAnYV5bSFRNTEVsZW1lbnRdfGNoYXJzZXQsY29vcmRzLGRvd25sb2FkLGhhc2gsaG9zdCxob3N0bmFtZSxocmVmLGhyZWZsYW5nLG5hbWUscGFzc3dvcmQscGF0aG5hbWUscGluZyxwb3J0LHByb3RvY29sLHJlZmVycmVyUG9saWN5LHJlbCxyZXYsc2VhcmNoLHNoYXBlLHRhcmdldCx0ZXh0LHR5cGUsdXNlcm5hbWUnLFxuICAnYXJlYV5bSFRNTEVsZW1lbnRdfGFsdCxjb29yZHMsZG93bmxvYWQsaGFzaCxob3N0LGhvc3RuYW1lLGhyZWYsIW5vSHJlZixwYXNzd29yZCxwYXRobmFtZSxwaW5nLHBvcnQscHJvdG9jb2wscmVmZXJyZXJQb2xpY3kscmVsLHNlYXJjaCxzaGFwZSx0YXJnZXQsdXNlcm5hbWUnLFxuICAnYXVkaW9ebWVkaWF8JyxcbiAgJ2JyXltIVE1MRWxlbWVudF18Y2xlYXInLFxuICAnYmFzZV5bSFRNTEVsZW1lbnRdfGhyZWYsdGFyZ2V0JyxcbiAgJ2JvZHleW0hUTUxFbGVtZW50XXxhTGluayxiYWNrZ3JvdW5kLGJnQ29sb3IsbGluaywqYmVmb3JldW5sb2FkLCpibHVyLCplcnJvciwqZm9jdXMsKmhhc2hjaGFuZ2UsKmxhbmd1YWdlY2hhbmdlLCpsb2FkLCptZXNzYWdlLCpvZmZsaW5lLCpvbmxpbmUsKnBhZ2VoaWRlLCpwYWdlc2hvdywqcG9wc3RhdGUsKnJlamVjdGlvbmhhbmRsZWQsKnJlc2l6ZSwqc2Nyb2xsLCpzdG9yYWdlLCp1bmhhbmRsZWRyZWplY3Rpb24sKnVubG9hZCx0ZXh0LHZMaW5rJyxcbiAgJ2J1dHRvbl5bSFRNTEVsZW1lbnRdfCFhdXRvZm9jdXMsIWRpc2FibGVkLGZvcm1BY3Rpb24sZm9ybUVuY3R5cGUsZm9ybU1ldGhvZCwhZm9ybU5vVmFsaWRhdGUsZm9ybVRhcmdldCxuYW1lLHR5cGUsdmFsdWUnLFxuICAnY2FudmFzXltIVE1MRWxlbWVudF18I2hlaWdodCwjd2lkdGgnLFxuICAnY29udGVudF5bSFRNTEVsZW1lbnRdfHNlbGVjdCcsXG4gICdkbF5bSFRNTEVsZW1lbnRdfCFjb21wYWN0JyxcbiAgJ2RhdGFsaXN0XltIVE1MRWxlbWVudF18JyxcbiAgJ2RldGFpbHNeW0hUTUxFbGVtZW50XXwhb3BlbicsXG4gICdkaWFsb2deW0hUTUxFbGVtZW50XXwhb3BlbixyZXR1cm5WYWx1ZScsXG4gICdkaXJeW0hUTUxFbGVtZW50XXwhY29tcGFjdCcsXG4gICdkaXZeW0hUTUxFbGVtZW50XXxhbGlnbicsXG4gICdlbWJlZF5bSFRNTEVsZW1lbnRdfGFsaWduLGhlaWdodCxuYW1lLHNyYyx0eXBlLHdpZHRoJyxcbiAgJ2ZpZWxkc2V0XltIVE1MRWxlbWVudF18IWRpc2FibGVkLG5hbWUnLFxuICAnZm9udF5bSFRNTEVsZW1lbnRdfGNvbG9yLGZhY2Usc2l6ZScsXG4gICdmb3JtXltIVE1MRWxlbWVudF18YWNjZXB0Q2hhcnNldCxhY3Rpb24sYXV0b2NvbXBsZXRlLGVuY29kaW5nLGVuY3R5cGUsbWV0aG9kLG5hbWUsIW5vVmFsaWRhdGUsdGFyZ2V0JyxcbiAgJ2ZyYW1lXltIVE1MRWxlbWVudF18ZnJhbWVCb3JkZXIsbG9uZ0Rlc2MsbWFyZ2luSGVpZ2h0LG1hcmdpbldpZHRoLG5hbWUsIW5vUmVzaXplLHNjcm9sbGluZyxzcmMnLFxuICAnZnJhbWVzZXReW0hUTUxFbGVtZW50XXxjb2xzLCpiZWZvcmV1bmxvYWQsKmJsdXIsKmVycm9yLCpmb2N1cywqaGFzaGNoYW5nZSwqbGFuZ3VhZ2VjaGFuZ2UsKmxvYWQsKm1lc3NhZ2UsKm9mZmxpbmUsKm9ubGluZSwqcGFnZWhpZGUsKnBhZ2VzaG93LCpwb3BzdGF0ZSwqcmVqZWN0aW9uaGFuZGxlZCwqcmVzaXplLCpzY3JvbGwsKnN0b3JhZ2UsKnVuaGFuZGxlZHJlamVjdGlvbiwqdW5sb2FkLHJvd3MnLFxuICAnaHJeW0hUTUxFbGVtZW50XXxhbGlnbixjb2xvciwhbm9TaGFkZSxzaXplLHdpZHRoJyxcbiAgJ2hlYWReW0hUTUxFbGVtZW50XXwnLFxuICAnaDEsaDIsaDMsaDQsaDUsaDZeW0hUTUxFbGVtZW50XXxhbGlnbicsXG4gICdodG1sXltIVE1MRWxlbWVudF18dmVyc2lvbicsXG4gICdpZnJhbWVeW0hUTUxFbGVtZW50XXxhbGlnbiwhYWxsb3dGdWxsc2NyZWVuLGZyYW1lQm9yZGVyLGhlaWdodCxsb25nRGVzYyxtYXJnaW5IZWlnaHQsbWFyZ2luV2lkdGgsbmFtZSxyZWZlcnJlclBvbGljeSwlc2FuZGJveCxzY3JvbGxpbmcsc3JjLHNyY2RvYyx3aWR0aCcsXG4gICdpbWdeW0hUTUxFbGVtZW50XXxhbGlnbixhbHQsYm9yZGVyLCVjcm9zc09yaWdpbiwjaGVpZ2h0LCNoc3BhY2UsIWlzTWFwLGxvbmdEZXNjLGxvd3NyYyxuYW1lLHJlZmVycmVyUG9saWN5LHNpemVzLHNyYyxzcmNzZXQsdXNlTWFwLCN2c3BhY2UsI3dpZHRoJyxcbiAgJ2lucHV0XltIVE1MRWxlbWVudF18YWNjZXB0LGFsaWduLGFsdCxhdXRvY2FwaXRhbGl6ZSxhdXRvY29tcGxldGUsIWF1dG9mb2N1cywhY2hlY2tlZCwhZGVmYXVsdENoZWNrZWQsZGVmYXVsdFZhbHVlLGRpck5hbWUsIWRpc2FibGVkLCVmaWxlcyxmb3JtQWN0aW9uLGZvcm1FbmN0eXBlLGZvcm1NZXRob2QsIWZvcm1Ob1ZhbGlkYXRlLGZvcm1UYXJnZXQsI2hlaWdodCwhaW5jcmVtZW50YWwsIWluZGV0ZXJtaW5hdGUsbWF4LCNtYXhMZW5ndGgsbWluLCNtaW5MZW5ndGgsIW11bHRpcGxlLG5hbWUscGF0dGVybixwbGFjZWhvbGRlciwhcmVhZE9ubHksIXJlcXVpcmVkLHNlbGVjdGlvbkRpcmVjdGlvbiwjc2VsZWN0aW9uRW5kLCNzZWxlY3Rpb25TdGFydCwjc2l6ZSxzcmMsc3RlcCx0eXBlLHVzZU1hcCx2YWx1ZSwldmFsdWVBc0RhdGUsI3ZhbHVlQXNOdW1iZXIsI3dpZHRoJyxcbiAgJ2xpXltIVE1MRWxlbWVudF18dHlwZSwjdmFsdWUnLFxuICAnbGFiZWxeW0hUTUxFbGVtZW50XXxodG1sRm9yJyxcbiAgJ2xlZ2VuZF5bSFRNTEVsZW1lbnRdfGFsaWduJyxcbiAgJ2xpbmteW0hUTUxFbGVtZW50XXxhcyxjaGFyc2V0LCVjcm9zc09yaWdpbiwhZGlzYWJsZWQsaHJlZixocmVmbGFuZyxpbnRlZ3JpdHksbWVkaWEscmVmZXJyZXJQb2xpY3kscmVsLCVyZWxMaXN0LHJldiwlc2l6ZXMsdGFyZ2V0LHR5cGUnLFxuICAnbWFwXltIVE1MRWxlbWVudF18bmFtZScsXG4gICdtYXJxdWVlXltIVE1MRWxlbWVudF18YmVoYXZpb3IsYmdDb2xvcixkaXJlY3Rpb24saGVpZ2h0LCNoc3BhY2UsI2xvb3AsI3Njcm9sbEFtb3VudCwjc2Nyb2xsRGVsYXksIXRydWVTcGVlZCwjdnNwYWNlLHdpZHRoJyxcbiAgJ21lbnVeW0hUTUxFbGVtZW50XXwhY29tcGFjdCcsXG4gICdtZXRhXltIVE1MRWxlbWVudF18Y29udGVudCxodHRwRXF1aXYsbmFtZSxzY2hlbWUnLFxuICAnbWV0ZXJeW0hUTUxFbGVtZW50XXwjaGlnaCwjbG93LCNtYXgsI21pbiwjb3B0aW11bSwjdmFsdWUnLFxuICAnaW5zLGRlbF5bSFRNTEVsZW1lbnRdfGNpdGUsZGF0ZVRpbWUnLFxuICAnb2xeW0hUTUxFbGVtZW50XXwhY29tcGFjdCwhcmV2ZXJzZWQsI3N0YXJ0LHR5cGUnLFxuICAnb2JqZWN0XltIVE1MRWxlbWVudF18YWxpZ24sYXJjaGl2ZSxib3JkZXIsY29kZSxjb2RlQmFzZSxjb2RlVHlwZSxkYXRhLCFkZWNsYXJlLGhlaWdodCwjaHNwYWNlLG5hbWUsc3RhbmRieSx0eXBlLHVzZU1hcCwjdnNwYWNlLHdpZHRoJyxcbiAgJ29wdGdyb3VwXltIVE1MRWxlbWVudF18IWRpc2FibGVkLGxhYmVsJyxcbiAgJ29wdGlvbl5bSFRNTEVsZW1lbnRdfCFkZWZhdWx0U2VsZWN0ZWQsIWRpc2FibGVkLGxhYmVsLCFzZWxlY3RlZCx0ZXh0LHZhbHVlJyxcbiAgJ291dHB1dF5bSFRNTEVsZW1lbnRdfGRlZmF1bHRWYWx1ZSwlaHRtbEZvcixuYW1lLHZhbHVlJyxcbiAgJ3BeW0hUTUxFbGVtZW50XXxhbGlnbicsXG4gICdwYXJhbV5bSFRNTEVsZW1lbnRdfG5hbWUsdHlwZSx2YWx1ZSx2YWx1ZVR5cGUnLFxuICAncGljdHVyZV5bSFRNTEVsZW1lbnRdfCcsXG4gICdwcmVeW0hUTUxFbGVtZW50XXwjd2lkdGgnLFxuICAncHJvZ3Jlc3NeW0hUTUxFbGVtZW50XXwjbWF4LCN2YWx1ZScsXG4gICdxLGJsb2NrcXVvdGUsY2l0ZV5bSFRNTEVsZW1lbnRdfCcsXG4gICdzY3JpcHReW0hUTUxFbGVtZW50XXwhYXN5bmMsY2hhcnNldCwlY3Jvc3NPcmlnaW4sIWRlZmVyLGV2ZW50LGh0bWxGb3IsaW50ZWdyaXR5LHNyYyx0ZXh0LHR5cGUnLFxuICAnc2VsZWN0XltIVE1MRWxlbWVudF18YXV0b2NvbXBsZXRlLCFhdXRvZm9jdXMsIWRpc2FibGVkLCNsZW5ndGgsIW11bHRpcGxlLG5hbWUsIXJlcXVpcmVkLCNzZWxlY3RlZEluZGV4LCNzaXplLHZhbHVlJyxcbiAgJ3NoYWRvd15bSFRNTEVsZW1lbnRdfCcsXG4gICdzbG90XltIVE1MRWxlbWVudF18bmFtZScsXG4gICdzb3VyY2VeW0hUTUxFbGVtZW50XXxtZWRpYSxzaXplcyxzcmMsc3Jjc2V0LHR5cGUnLFxuICAnc3Bhbl5bSFRNTEVsZW1lbnRdfCcsXG4gICdzdHlsZV5bSFRNTEVsZW1lbnRdfCFkaXNhYmxlZCxtZWRpYSx0eXBlJyxcbiAgJ2NhcHRpb25eW0hUTUxFbGVtZW50XXxhbGlnbicsXG4gICd0aCx0ZF5bSFRNTEVsZW1lbnRdfGFiYnIsYWxpZ24sYXhpcyxiZ0NvbG9yLGNoLGNoT2ZmLCNjb2xTcGFuLGhlYWRlcnMsaGVpZ2h0LCFub1dyYXAsI3Jvd1NwYW4sc2NvcGUsdkFsaWduLHdpZHRoJyxcbiAgJ2NvbCxjb2xncm91cF5bSFRNTEVsZW1lbnRdfGFsaWduLGNoLGNoT2ZmLCNzcGFuLHZBbGlnbix3aWR0aCcsXG4gICd0YWJsZV5bSFRNTEVsZW1lbnRdfGFsaWduLGJnQ29sb3IsYm9yZGVyLCVjYXB0aW9uLGNlbGxQYWRkaW5nLGNlbGxTcGFjaW5nLGZyYW1lLHJ1bGVzLHN1bW1hcnksJXRGb290LCV0SGVhZCx3aWR0aCcsXG4gICd0cl5bSFRNTEVsZW1lbnRdfGFsaWduLGJnQ29sb3IsY2gsY2hPZmYsdkFsaWduJyxcbiAgJ3Rmb290LHRoZWFkLHRib2R5XltIVE1MRWxlbWVudF18YWxpZ24sY2gsY2hPZmYsdkFsaWduJyxcbiAgJ3RlbXBsYXRlXltIVE1MRWxlbWVudF18JyxcbiAgJ3RleHRhcmVhXltIVE1MRWxlbWVudF18YXV0b2NhcGl0YWxpemUsYXV0b2NvbXBsZXRlLCFhdXRvZm9jdXMsI2NvbHMsZGVmYXVsdFZhbHVlLGRpck5hbWUsIWRpc2FibGVkLCNtYXhMZW5ndGgsI21pbkxlbmd0aCxuYW1lLHBsYWNlaG9sZGVyLCFyZWFkT25seSwhcmVxdWlyZWQsI3Jvd3Msc2VsZWN0aW9uRGlyZWN0aW9uLCNzZWxlY3Rpb25FbmQsI3NlbGVjdGlvblN0YXJ0LHZhbHVlLHdyYXAnLFxuICAndGl0bGVeW0hUTUxFbGVtZW50XXx0ZXh0JyxcbiAgJ3RyYWNrXltIVE1MRWxlbWVudF18IWRlZmF1bHQsa2luZCxsYWJlbCxzcmMsc3JjbGFuZycsXG4gICd1bF5bSFRNTEVsZW1lbnRdfCFjb21wYWN0LHR5cGUnLFxuICAndW5rbm93bl5bSFRNTEVsZW1lbnRdfCcsXG4gICd2aWRlb15tZWRpYXwjaGVpZ2h0LHBvc3Rlciwjd2lkdGgnLFxuICAnOnN2ZzphXjpzdmc6Z3JhcGhpY3N8JyxcbiAgJzpzdmc6YW5pbWF0ZV46c3ZnOmFuaW1hdGlvbnwnLFxuICAnOnN2ZzphbmltYXRlTW90aW9uXjpzdmc6YW5pbWF0aW9ufCcsXG4gICc6c3ZnOmFuaW1hdGVUcmFuc2Zvcm1eOnN2ZzphbmltYXRpb258JyxcbiAgJzpzdmc6Y2lyY2xlXjpzdmc6Z2VvbWV0cnl8JyxcbiAgJzpzdmc6Y2xpcFBhdGheOnN2ZzpncmFwaGljc3wnLFxuICAnOnN2ZzpkZWZzXjpzdmc6Z3JhcGhpY3N8JyxcbiAgJzpzdmc6ZGVzY146c3ZnOnwnLFxuICAnOnN2ZzpkaXNjYXJkXjpzdmc6fCcsXG4gICc6c3ZnOmVsbGlwc2VeOnN2ZzpnZW9tZXRyeXwnLFxuICAnOnN2ZzpmZUJsZW5kXjpzdmc6fCcsXG4gICc6c3ZnOmZlQ29sb3JNYXRyaXheOnN2Zzp8JyxcbiAgJzpzdmc6ZmVDb21wb25lbnRUcmFuc2Zlcl46c3ZnOnwnLFxuICAnOnN2ZzpmZUNvbXBvc2l0ZV46c3ZnOnwnLFxuICAnOnN2ZzpmZUNvbnZvbHZlTWF0cml4Xjpzdmc6fCcsXG4gICc6c3ZnOmZlRGlmZnVzZUxpZ2h0aW5nXjpzdmc6fCcsXG4gICc6c3ZnOmZlRGlzcGxhY2VtZW50TWFwXjpzdmc6fCcsXG4gICc6c3ZnOmZlRGlzdGFudExpZ2h0Xjpzdmc6fCcsXG4gICc6c3ZnOmZlRHJvcFNoYWRvd146c3ZnOnwnLFxuICAnOnN2ZzpmZUZsb29kXjpzdmc6fCcsXG4gICc6c3ZnOmZlRnVuY0FeOnN2Zzpjb21wb25lbnRUcmFuc2ZlckZ1bmN0aW9ufCcsXG4gICc6c3ZnOmZlRnVuY0JeOnN2Zzpjb21wb25lbnRUcmFuc2ZlckZ1bmN0aW9ufCcsXG4gICc6c3ZnOmZlRnVuY0deOnN2Zzpjb21wb25lbnRUcmFuc2ZlckZ1bmN0aW9ufCcsXG4gICc6c3ZnOmZlRnVuY1JeOnN2Zzpjb21wb25lbnRUcmFuc2ZlckZ1bmN0aW9ufCcsXG4gICc6c3ZnOmZlR2F1c3NpYW5CbHVyXjpzdmc6fCcsXG4gICc6c3ZnOmZlSW1hZ2VeOnN2Zzp8JyxcbiAgJzpzdmc6ZmVNZXJnZV46c3ZnOnwnLFxuICAnOnN2ZzpmZU1lcmdlTm9kZV46c3ZnOnwnLFxuICAnOnN2ZzpmZU1vcnBob2xvZ3leOnN2Zzp8JyxcbiAgJzpzdmc6ZmVPZmZzZXReOnN2Zzp8JyxcbiAgJzpzdmc6ZmVQb2ludExpZ2h0Xjpzdmc6fCcsXG4gICc6c3ZnOmZlU3BlY3VsYXJMaWdodGluZ146c3ZnOnwnLFxuICAnOnN2ZzpmZVNwb3RMaWdodF46c3ZnOnwnLFxuICAnOnN2ZzpmZVRpbGVeOnN2Zzp8JyxcbiAgJzpzdmc6ZmVUdXJidWxlbmNlXjpzdmc6fCcsXG4gICc6c3ZnOmZpbHRlcl46c3ZnOnwnLFxuICAnOnN2Zzpmb3JlaWduT2JqZWN0Xjpzdmc6Z3JhcGhpY3N8JyxcbiAgJzpzdmc6Z146c3ZnOmdyYXBoaWNzfCcsXG4gICc6c3ZnOmltYWdlXjpzdmc6Z3JhcGhpY3N8JyxcbiAgJzpzdmc6bGluZV46c3ZnOmdlb21ldHJ5fCcsXG4gICc6c3ZnOmxpbmVhckdyYWRpZW50Xjpzdmc6Z3JhZGllbnR8JyxcbiAgJzpzdmc6bXBhdGheOnN2Zzp8JyxcbiAgJzpzdmc6bWFya2VyXjpzdmc6fCcsXG4gICc6c3ZnOm1hc2teOnN2Zzp8JyxcbiAgJzpzdmc6bWV0YWRhdGFeOnN2Zzp8JyxcbiAgJzpzdmc6cGF0aF46c3ZnOmdlb21ldHJ5fCcsXG4gICc6c3ZnOnBhdHRlcm5eOnN2Zzp8JyxcbiAgJzpzdmc6cG9seWdvbl46c3ZnOmdlb21ldHJ5fCcsXG4gICc6c3ZnOnBvbHlsaW5lXjpzdmc6Z2VvbWV0cnl8JyxcbiAgJzpzdmc6cmFkaWFsR3JhZGllbnReOnN2ZzpncmFkaWVudHwnLFxuICAnOnN2ZzpyZWN0Xjpzdmc6Z2VvbWV0cnl8JyxcbiAgJzpzdmc6c3ZnXjpzdmc6Z3JhcGhpY3N8I2N1cnJlbnRTY2FsZSwjem9vbUFuZFBhbicsXG4gICc6c3ZnOnNjcmlwdF46c3ZnOnx0eXBlJyxcbiAgJzpzdmc6c2V0Xjpzdmc6YW5pbWF0aW9ufCcsXG4gICc6c3ZnOnN0b3BeOnN2Zzp8JyxcbiAgJzpzdmc6c3R5bGVeOnN2Zzp8IWRpc2FibGVkLG1lZGlhLHRpdGxlLHR5cGUnLFxuICAnOnN2Zzpzd2l0Y2heOnN2ZzpncmFwaGljc3wnLFxuICAnOnN2ZzpzeW1ib2xeOnN2Zzp8JyxcbiAgJzpzdmc6dHNwYW5eOnN2Zzp0ZXh0UG9zaXRpb25pbmd8JyxcbiAgJzpzdmc6dGV4dF46c3ZnOnRleHRQb3NpdGlvbmluZ3wnLFxuICAnOnN2Zzp0ZXh0UGF0aF46c3ZnOnRleHRDb250ZW50fCcsXG4gICc6c3ZnOnRpdGxlXjpzdmc6fCcsXG4gICc6c3ZnOnVzZV46c3ZnOmdyYXBoaWNzfCcsXG4gICc6c3ZnOnZpZXdeOnN2Zzp8I3pvb21BbmRQYW4nLFxuICAnZGF0YV5bSFRNTEVsZW1lbnRdfHZhbHVlJyxcbiAgJ2tleWdlbl5bSFRNTEVsZW1lbnRdfCFhdXRvZm9jdXMsY2hhbGxlbmdlLCFkaXNhYmxlZCxmb3JtLGtleXR5cGUsbmFtZScsXG4gICdtZW51aXRlbV5bSFRNTEVsZW1lbnRdfHR5cGUsbGFiZWwsaWNvbiwhZGlzYWJsZWQsIWNoZWNrZWQscmFkaW9ncm91cCwhZGVmYXVsdCcsXG4gICdzdW1tYXJ5XltIVE1MRWxlbWVudF18JyxcbiAgJ3RpbWVeW0hUTUxFbGVtZW50XXxkYXRlVGltZScsXG4gICc6c3ZnOmN1cnNvcl46c3ZnOnwnLFxuXTtcblxuY29uc3QgX0FUVFJfVE9fUFJPUDoge1tuYW1lOiBzdHJpbmddOiBzdHJpbmd9ID0ge1xuICAnY2xhc3MnOiAnY2xhc3NOYW1lJyxcbiAgJ2Zvcic6ICdodG1sRm9yJyxcbiAgJ2Zvcm1hY3Rpb24nOiAnZm9ybUFjdGlvbicsXG4gICdpbm5lckh0bWwnOiAnaW5uZXJIVE1MJyxcbiAgJ3JlYWRvbmx5JzogJ3JlYWRPbmx5JyxcbiAgJ3RhYmluZGV4JzogJ3RhYkluZGV4Jyxcbn07XG5cbi8vIEludmVydCBfQVRUUl9UT19QUk9QLlxuY29uc3QgX1BST1BfVE9fQVRUUjoge1tuYW1lOiBzdHJpbmddOiBzdHJpbmd9ID1cbiAgICBPYmplY3Qua2V5cyhfQVRUUl9UT19QUk9QKS5yZWR1Y2UoKGludmVydGVkLCBhdHRyKSA9PiB7XG4gICAgICBpbnZlcnRlZFtfQVRUUl9UT19QUk9QW2F0dHJdXSA9IGF0dHI7XG4gICAgICByZXR1cm4gaW52ZXJ0ZWQ7XG4gICAgfSwge30gYXMge1twcm9wOiBzdHJpbmddOiBzdHJpbmd9KTtcblxuZXhwb3J0IGNsYXNzIERvbUVsZW1lbnRTY2hlbWFSZWdpc3RyeSBleHRlbmRzIEVsZW1lbnRTY2hlbWFSZWdpc3RyeSB7XG4gIHByaXZhdGUgX3NjaGVtYToge1tlbGVtZW50OiBzdHJpbmddOiB7W3Byb3BlcnR5OiBzdHJpbmddOiBzdHJpbmd9fSA9IHt9O1xuXG4gIGNvbnN0cnVjdG9yKCkge1xuICAgIHN1cGVyKCk7XG4gICAgU0NIRU1BLmZvckVhY2goZW5jb2RlZFR5cGUgPT4ge1xuICAgICAgY29uc3QgdHlwZToge1twcm9wZXJ0eTogc3RyaW5nXTogc3RyaW5nfSA9IHt9O1xuICAgICAgY29uc3QgW3N0clR5cGUsIHN0clByb3BlcnRpZXNdID0gZW5jb2RlZFR5cGUuc3BsaXQoJ3wnKTtcbiAgICAgIGNvbnN0IHByb3BlcnRpZXMgPSBzdHJQcm9wZXJ0aWVzLnNwbGl0KCcsJyk7XG4gICAgICBjb25zdCBbdHlwZU5hbWVzLCBzdXBlck5hbWVdID0gc3RyVHlwZS5zcGxpdCgnXicpO1xuICAgICAgdHlwZU5hbWVzLnNwbGl0KCcsJykuZm9yRWFjaCh0YWcgPT4gdGhpcy5fc2NoZW1hW3RhZy50b0xvd2VyQ2FzZSgpXSA9IHR5cGUpO1xuICAgICAgY29uc3Qgc3VwZXJUeXBlID0gc3VwZXJOYW1lICYmIHRoaXMuX3NjaGVtYVtzdXBlck5hbWUudG9Mb3dlckNhc2UoKV07XG4gICAgICBpZiAoc3VwZXJUeXBlKSB7XG4gICAgICAgIE9iamVjdC5rZXlzKHN1cGVyVHlwZSkuZm9yRWFjaCgocHJvcDogc3RyaW5nKSA9PiB7XG4gICAgICAgICAgdHlwZVtwcm9wXSA9IHN1cGVyVHlwZVtwcm9wXTtcbiAgICAgICAgfSk7XG4gICAgICB9XG4gICAgICBwcm9wZXJ0aWVzLmZvckVhY2goKHByb3BlcnR5OiBzdHJpbmcpID0+IHtcbiAgICAgICAgaWYgKHByb3BlcnR5Lmxlbmd0aCA+IDApIHtcbiAgICAgICAgICBzd2l0Y2ggKHByb3BlcnR5WzBdKSB7XG4gICAgICAgICAgICBjYXNlICcqJzpcbiAgICAgICAgICAgICAgLy8gV2UgZG9uJ3QgeWV0IHN1cHBvcnQgZXZlbnRzLlxuICAgICAgICAgICAgICAvLyBJZiBldmVyIGFsbG93aW5nIHRvIGJpbmQgdG8gZXZlbnRzLCBHTyBUSFJPVUdIIEEgU0VDVVJJVFkgUkVWSUVXLCBhbGxvd2luZyBldmVudHNcbiAgICAgICAgICAgICAgLy8gd2lsbFxuICAgICAgICAgICAgICAvLyBhbG1vc3QgY2VydGFpbmx5IGludHJvZHVjZSBiYWQgWFNTIHZ1bG5lcmFiaWxpdGllcy5cbiAgICAgICAgICAgICAgLy8gdHlwZVtwcm9wZXJ0eS5zdWJzdHJpbmcoMSldID0gRVZFTlQ7XG4gICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgY2FzZSAnISc6XG4gICAgICAgICAgICAgIHR5cGVbcHJvcGVydHkuc3Vic3RyaW5nKDEpXSA9IEJPT0xFQU47XG4gICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgY2FzZSAnIyc6XG4gICAgICAgICAgICAgIHR5cGVbcHJvcGVydHkuc3Vic3RyaW5nKDEpXSA9IE5VTUJFUjtcbiAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICBjYXNlICclJzpcbiAgICAgICAgICAgICAgdHlwZVtwcm9wZXJ0eS5zdWJzdHJpbmcoMSldID0gT0JKRUNUO1xuICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICAgIHR5cGVbcHJvcGVydHldID0gU1RSSU5HO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfSk7XG4gICAgfSk7XG4gIH1cblxuICBoYXNQcm9wZXJ0eSh0YWdOYW1lOiBzdHJpbmcsIHByb3BOYW1lOiBzdHJpbmcsIHNjaGVtYU1ldGFzOiBTY2hlbWFNZXRhZGF0YVtdKTogYm9vbGVhbiB7XG4gICAgaWYgKHNjaGVtYU1ldGFzLnNvbWUoKHNjaGVtYSkgPT4gc2NoZW1hLm5hbWUgPT09IE5PX0VSUk9SU19TQ0hFTUEubmFtZSkpIHtcbiAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cblxuICAgIGlmICh0YWdOYW1lLmluZGV4T2YoJy0nKSA+IC0xKSB7XG4gICAgICBpZiAoaXNOZ0NvbnRhaW5lcih0YWdOYW1lKSB8fCBpc05nQ29udGVudCh0YWdOYW1lKSkge1xuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICB9XG5cbiAgICAgIGlmIChzY2hlbWFNZXRhcy5zb21lKChzY2hlbWEpID0+IHNjaGVtYS5uYW1lID09PSBDVVNUT01fRUxFTUVOVFNfU0NIRU1BLm5hbWUpKSB7XG4gICAgICAgIC8vIENhbid0IHRlbGwgbm93IGFzIHdlIGRvbid0IGtub3cgd2hpY2ggcHJvcGVydGllcyBhIGN1c3RvbSBlbGVtZW50IHdpbGwgZ2V0XG4gICAgICAgIC8vIG9uY2UgaXQgaXMgaW5zdGFudGlhdGVkXG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgfVxuICAgIH1cblxuICAgIGNvbnN0IGVsZW1lbnRQcm9wZXJ0aWVzID0gdGhpcy5fc2NoZW1hW3RhZ05hbWUudG9Mb3dlckNhc2UoKV0gfHwgdGhpcy5fc2NoZW1hWyd1bmtub3duJ107XG4gICAgcmV0dXJuICEhZWxlbWVudFByb3BlcnRpZXNbcHJvcE5hbWVdO1xuICB9XG5cbiAgaGFzRWxlbWVudCh0YWdOYW1lOiBzdHJpbmcsIHNjaGVtYU1ldGFzOiBTY2hlbWFNZXRhZGF0YVtdKTogYm9vbGVhbiB7XG4gICAgaWYgKHNjaGVtYU1ldGFzLnNvbWUoKHNjaGVtYSkgPT4gc2NoZW1hLm5hbWUgPT09IE5PX0VSUk9SU19TQ0hFTUEubmFtZSkpIHtcbiAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cblxuICAgIGlmICh0YWdOYW1lLmluZGV4T2YoJy0nKSA+IC0xKSB7XG4gICAgICBpZiAoaXNOZ0NvbnRhaW5lcih0YWdOYW1lKSB8fCBpc05nQ29udGVudCh0YWdOYW1lKSkge1xuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgIH1cblxuICAgICAgaWYgKHNjaGVtYU1ldGFzLnNvbWUoKHNjaGVtYSkgPT4gc2NoZW1hLm5hbWUgPT09IENVU1RPTV9FTEVNRU5UU19TQ0hFTUEubmFtZSkpIHtcbiAgICAgICAgLy8gQWxsb3cgYW55IGN1c3RvbSBlbGVtZW50c1xuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gISF0aGlzLl9zY2hlbWFbdGFnTmFtZS50b0xvd2VyQ2FzZSgpXTtcbiAgfVxuXG4gIC8qKlxuICAgKiBzZWN1cml0eUNvbnRleHQgcmV0dXJucyB0aGUgc2VjdXJpdHkgY29udGV4dCBmb3IgdGhlIGdpdmVuIHByb3BlcnR5IG9uIHRoZSBnaXZlbiBET00gdGFnLlxuICAgKlxuICAgKiBUYWcgYW5kIHByb3BlcnR5IG5hbWUgYXJlIHN0YXRpY2FsbHkga25vd24gYW5kIGNhbm5vdCBjaGFuZ2UgYXQgcnVudGltZSwgaS5lLiBpdCBpcyBub3RcbiAgICogcG9zc2libGUgdG8gYmluZCBhIHZhbHVlIGludG8gYSBjaGFuZ2luZyBhdHRyaWJ1dGUgb3IgdGFnIG5hbWUuXG4gICAqXG4gICAqIFRoZSBmaWx0ZXJpbmcgaXMgYmFzZWQgb24gYSBsaXN0IG9mIGFsbG93ZWQgdGFnc3xhdHRyaWJ1dGVzLiBBbGwgYXR0cmlidXRlcyBpbiB0aGUgc2NoZW1hXG4gICAqIGFib3ZlIGFyZSBhc3N1bWVkIHRvIGhhdmUgdGhlICdOT05FJyBzZWN1cml0eSBjb250ZXh0LCBpLmUuIHRoYXQgdGhleSBhcmUgc2FmZSBpbmVydFxuICAgKiBzdHJpbmcgdmFsdWVzLiBPbmx5IHNwZWNpZmljIHdlbGwga25vd24gYXR0YWNrIHZlY3RvcnMgYXJlIGFzc2lnbmVkIHRoZWlyIGFwcHJvcHJpYXRlIGNvbnRleHQuXG4gICAqL1xuICBzZWN1cml0eUNvbnRleHQodGFnTmFtZTogc3RyaW5nLCBwcm9wTmFtZTogc3RyaW5nLCBpc0F0dHJpYnV0ZTogYm9vbGVhbik6IFNlY3VyaXR5Q29udGV4dCB7XG4gICAgaWYgKGlzQXR0cmlidXRlKSB7XG4gICAgICAvLyBOQjogRm9yIHNlY3VyaXR5IHB1cnBvc2VzLCB1c2UgdGhlIG1hcHBlZCBwcm9wZXJ0eSBuYW1lLCBub3QgdGhlIGF0dHJpYnV0ZSBuYW1lLlxuICAgICAgcHJvcE5hbWUgPSB0aGlzLmdldE1hcHBlZFByb3BOYW1lKHByb3BOYW1lKTtcbiAgICB9XG5cbiAgICAvLyBNYWtlIHN1cmUgY29tcGFyaXNvbnMgYXJlIGNhc2UgaW5zZW5zaXRpdmUsIHNvIHRoYXQgY2FzZSBkaWZmZXJlbmNlcyBiZXR3ZWVuIGF0dHJpYnV0ZSBhbmRcbiAgICAvLyBwcm9wZXJ0eSBuYW1lcyBkbyBub3QgaGF2ZSBhIHNlY3VyaXR5IGltcGFjdC5cbiAgICB0YWdOYW1lID0gdGFnTmFtZS50b0xvd2VyQ2FzZSgpO1xuICAgIHByb3BOYW1lID0gcHJvcE5hbWUudG9Mb3dlckNhc2UoKTtcbiAgICBsZXQgY3R4ID0gU0VDVVJJVFlfU0NIRU1BKClbdGFnTmFtZSArICd8JyArIHByb3BOYW1lXTtcbiAgICBpZiAoY3R4KSB7XG4gICAgICByZXR1cm4gY3R4O1xuICAgIH1cbiAgICBjdHggPSBTRUNVUklUWV9TQ0hFTUEoKVsnKnwnICsgcHJvcE5hbWVdO1xuICAgIHJldHVybiBjdHggPyBjdHggOiBTZWN1cml0eUNvbnRleHQuTk9ORTtcbiAgfVxuXG4gIGdldE1hcHBlZFByb3BOYW1lKHByb3BOYW1lOiBzdHJpbmcpOiBzdHJpbmcge1xuICAgIHJldHVybiBfQVRUUl9UT19QUk9QW3Byb3BOYW1lXSB8fCBwcm9wTmFtZTtcbiAgfVxuXG4gIGdldERlZmF1bHRDb21wb25lbnRFbGVtZW50TmFtZSgpOiBzdHJpbmcge1xuICAgIHJldHVybiAnbmctY29tcG9uZW50JztcbiAgfVxuXG4gIHZhbGlkYXRlUHJvcGVydHkobmFtZTogc3RyaW5nKToge2Vycm9yOiBib29sZWFuLCBtc2c/OiBzdHJpbmd9IHtcbiAgICBpZiAobmFtZS50b0xvd2VyQ2FzZSgpLnN0YXJ0c1dpdGgoJ29uJykpIHtcbiAgICAgIGNvbnN0IG1zZyA9IGBCaW5kaW5nIHRvIGV2ZW50IHByb3BlcnR5ICcke25hbWV9JyBpcyBkaXNhbGxvd2VkIGZvciBzZWN1cml0eSByZWFzb25zLCBgICtcbiAgICAgICAgICBgcGxlYXNlIHVzZSAoJHtuYW1lLnNsaWNlKDIpfSk9Li4uYCArXG4gICAgICAgICAgYFxcbklmICcke25hbWV9JyBpcyBhIGRpcmVjdGl2ZSBpbnB1dCwgbWFrZSBzdXJlIHRoZSBkaXJlY3RpdmUgaXMgaW1wb3J0ZWQgYnkgdGhlYCArXG4gICAgICAgICAgYCBjdXJyZW50IG1vZHVsZS5gO1xuICAgICAgcmV0dXJuIHtlcnJvcjogdHJ1ZSwgbXNnOiBtc2d9O1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4ge2Vycm9yOiBmYWxzZX07XG4gICAgfVxuICB9XG5cbiAgdmFsaWRhdGVBdHRyaWJ1dGUobmFtZTogc3RyaW5nKToge2Vycm9yOiBib29sZWFuLCBtc2c/OiBzdHJpbmd9IHtcbiAgICBpZiAobmFtZS50b0xvd2VyQ2FzZSgpLnN0YXJ0c1dpdGgoJ29uJykpIHtcbiAgICAgIGNvbnN0IG1zZyA9IGBCaW5kaW5nIHRvIGV2ZW50IGF0dHJpYnV0ZSAnJHtuYW1lfScgaXMgZGlzYWxsb3dlZCBmb3Igc2VjdXJpdHkgcmVhc29ucywgYCArXG4gICAgICAgICAgYHBsZWFzZSB1c2UgKCR7bmFtZS5zbGljZSgyKX0pPS4uLmA7XG4gICAgICByZXR1cm4ge2Vycm9yOiB0cnVlLCBtc2c6IG1zZ307XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiB7ZXJyb3I6IGZhbHNlfTtcbiAgICB9XG4gIH1cblxuICBhbGxLbm93bkVsZW1lbnROYW1lcygpOiBzdHJpbmdbXSB7XG4gICAgcmV0dXJuIE9iamVjdC5rZXlzKHRoaXMuX3NjaGVtYSk7XG4gIH1cblxuICBhbGxLbm93bkF0dHJpYnV0ZXNPZkVsZW1lbnQodGFnTmFtZTogc3RyaW5nKTogc3RyaW5nW10ge1xuICAgIGNvbnN0IGVsZW1lbnRQcm9wZXJ0aWVzID0gdGhpcy5fc2NoZW1hW3RhZ05hbWUudG9Mb3dlckNhc2UoKV0gfHwgdGhpcy5fc2NoZW1hWyd1bmtub3duJ107XG4gICAgLy8gQ29udmVydCBwcm9wZXJ0aWVzIHRvIGF0dHJpYnV0ZXMuXG4gICAgcmV0dXJuIE9iamVjdC5rZXlzKGVsZW1lbnRQcm9wZXJ0aWVzKS5tYXAocHJvcCA9PiBfUFJPUF9UT19BVFRSW3Byb3BdID8/IHByb3ApO1xuICB9XG5cbiAgbm9ybWFsaXplQW5pbWF0aW9uU3R5bGVQcm9wZXJ0eShwcm9wTmFtZTogc3RyaW5nKTogc3RyaW5nIHtcbiAgICByZXR1cm4gZGFzaENhc2VUb0NhbWVsQ2FzZShwcm9wTmFtZSk7XG4gIH1cblxuICBub3JtYWxpemVBbmltYXRpb25TdHlsZVZhbHVlKGNhbWVsQ2FzZVByb3A6IHN0cmluZywgdXNlclByb3ZpZGVkUHJvcDogc3RyaW5nLCB2YWw6IHN0cmluZ3xudW1iZXIpOlxuICAgICAge2Vycm9yOiBzdHJpbmcsIHZhbHVlOiBzdHJpbmd9IHtcbiAgICBsZXQgdW5pdDogc3RyaW5nID0gJyc7XG4gICAgY29uc3Qgc3RyVmFsID0gdmFsLnRvU3RyaW5nKCkudHJpbSgpO1xuICAgIGxldCBlcnJvck1zZzogc3RyaW5nID0gbnVsbCE7XG5cbiAgICBpZiAoX2lzUGl4ZWxEaW1lbnNpb25TdHlsZShjYW1lbENhc2VQcm9wKSAmJiB2YWwgIT09IDAgJiYgdmFsICE9PSAnMCcpIHtcbiAgICAgIGlmICh0eXBlb2YgdmFsID09PSAnbnVtYmVyJykge1xuICAgICAgICB1bml0ID0gJ3B4JztcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIGNvbnN0IHZhbEFuZFN1ZmZpeE1hdGNoID0gdmFsLm1hdGNoKC9eWystXT9bXFxkXFwuXSsoW2Etel0qKSQvKTtcbiAgICAgICAgaWYgKHZhbEFuZFN1ZmZpeE1hdGNoICYmIHZhbEFuZFN1ZmZpeE1hdGNoWzFdLmxlbmd0aCA9PSAwKSB7XG4gICAgICAgICAgZXJyb3JNc2cgPSBgUGxlYXNlIHByb3ZpZGUgYSBDU1MgdW5pdCB2YWx1ZSBmb3IgJHt1c2VyUHJvdmlkZWRQcm9wfToke3ZhbH1gO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiB7ZXJyb3I6IGVycm9yTXNnLCB2YWx1ZTogc3RyVmFsICsgdW5pdH07XG4gIH1cbn1cblxuZnVuY3Rpb24gX2lzUGl4ZWxEaW1lbnNpb25TdHlsZShwcm9wOiBzdHJpbmcpOiBib29sZWFuIHtcbiAgc3dpdGNoIChwcm9wKSB7XG4gICAgY2FzZSAnd2lkdGgnOlxuICAgIGNhc2UgJ2hlaWdodCc6XG4gICAgY2FzZSAnbWluV2lkdGgnOlxuICAgIGNhc2UgJ21pbkhlaWdodCc6XG4gICAgY2FzZSAnbWF4V2lkdGgnOlxuICAgIGNhc2UgJ21heEhlaWdodCc6XG4gICAgY2FzZSAnbGVmdCc6XG4gICAgY2FzZSAndG9wJzpcbiAgICBjYXNlICdib3R0b20nOlxuICAgIGNhc2UgJ3JpZ2h0JzpcbiAgICBjYXNlICdmb250U2l6ZSc6XG4gICAgY2FzZSAnb3V0bGluZVdpZHRoJzpcbiAgICBjYXNlICdvdXRsaW5lT2Zmc2V0JzpcbiAgICBjYXNlICdwYWRkaW5nVG9wJzpcbiAgICBjYXNlICdwYWRkaW5nTGVmdCc6XG4gICAgY2FzZSAncGFkZGluZ0JvdHRvbSc6XG4gICAgY2FzZSAncGFkZGluZ1JpZ2h0JzpcbiAgICBjYXNlICdtYXJnaW5Ub3AnOlxuICAgIGNhc2UgJ21hcmdpbkxlZnQnOlxuICAgIGNhc2UgJ21hcmdpbkJvdHRvbSc6XG4gICAgY2FzZSAnbWFyZ2luUmlnaHQnOlxuICAgIGNhc2UgJ2JvcmRlclJhZGl1cyc6XG4gICAgY2FzZSAnYm9yZGVyV2lkdGgnOlxuICAgIGNhc2UgJ2JvcmRlclRvcFdpZHRoJzpcbiAgICBjYXNlICdib3JkZXJMZWZ0V2lkdGgnOlxuICAgIGNhc2UgJ2JvcmRlclJpZ2h0V2lkdGgnOlxuICAgIGNhc2UgJ2JvcmRlckJvdHRvbVdpZHRoJzpcbiAgICBjYXNlICd0ZXh0SW5kZW50JzpcbiAgICAgIHJldHVybiB0cnVlO1xuXG4gICAgZGVmYXVsdDpcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgfVxufVxuIl19