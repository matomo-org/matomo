(function() {
  "use strict";
  function _arrayLikeToArray$1(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles$1(r) {
    if (Array.isArray(r)) return r;
  }
  function _iterableToArrayLimit$1(r, l) {
    var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
    if (null != t) {
      var e, n, i, u, a = [], f = true, o = false;
      try {
        if (i = (t = t.call(r)).next, 0 === l) ;
        else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = true) ;
      } catch (r2) {
        o = true, n = r2;
      } finally {
        try {
          if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return;
        } finally {
          if (o) throw n;
        }
      }
      return a;
    }
  }
  function _nonIterableRest$1() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  function _slicedToArray$1(r, e) {
    return _arrayWithHoles$1(r) || _iterableToArrayLimit$1(r, e) || _unsupportedIterableToArray$1(r, e) || _nonIterableRest$1();
  }
  function _unsupportedIterableToArray$1(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray$1(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray$1(r, a) : void 0;
    }
  }
  var commonjsGlobal = typeof globalThis !== "undefined" ? globalThis : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {};
  var es_symbol_description = {};
  var globalThis_1;
  var hasRequiredGlobalThis;
  function requireGlobalThis() {
    if (hasRequiredGlobalThis) return globalThis_1;
    hasRequiredGlobalThis = 1;
    var check = function(it) {
      return it && it.Math === Math && it;
    };
    globalThis_1 = // eslint-disable-next-line es/no-global-this -- safe
    check(typeof globalThis == "object" && globalThis) || check(typeof window == "object" && window) || // eslint-disable-next-line no-restricted-globals -- safe
    check(typeof self == "object" && self) || check(typeof commonjsGlobal == "object" && commonjsGlobal) || check(typeof globalThis_1 == "object" && globalThis_1) || // eslint-disable-next-line no-new-func -- fallback
    /* @__PURE__ */ (function() {
      return this;
    })() || Function("return this")();
    return globalThis_1;
  }
  var objectGetOwnPropertyDescriptor = {};
  var fails;
  var hasRequiredFails;
  function requireFails() {
    if (hasRequiredFails) return fails;
    hasRequiredFails = 1;
    fails = function(exec) {
      try {
        return !!exec();
      } catch (error) {
        return true;
      }
    };
    return fails;
  }
  var descriptors;
  var hasRequiredDescriptors;
  function requireDescriptors() {
    if (hasRequiredDescriptors) return descriptors;
    hasRequiredDescriptors = 1;
    var fails2 = requireFails();
    descriptors = !fails2(function() {
      return Object.defineProperty({}, 1, { get: function() {
        return 7;
      } })[1] !== 7;
    });
    return descriptors;
  }
  var functionBindNative;
  var hasRequiredFunctionBindNative;
  function requireFunctionBindNative() {
    if (hasRequiredFunctionBindNative) return functionBindNative;
    hasRequiredFunctionBindNative = 1;
    var fails2 = requireFails();
    functionBindNative = !fails2(function() {
      var test = function() {
      }.bind();
      return typeof test != "function" || test.hasOwnProperty("prototype");
    });
    return functionBindNative;
  }
  var functionCall;
  var hasRequiredFunctionCall;
  function requireFunctionCall() {
    if (hasRequiredFunctionCall) return functionCall;
    hasRequiredFunctionCall = 1;
    var NATIVE_BIND = requireFunctionBindNative();
    var call = Function.prototype.call;
    functionCall = NATIVE_BIND ? call.bind(call) : function() {
      return call.apply(call, arguments);
    };
    return functionCall;
  }
  var objectPropertyIsEnumerable = {};
  var hasRequiredObjectPropertyIsEnumerable;
  function requireObjectPropertyIsEnumerable() {
    if (hasRequiredObjectPropertyIsEnumerable) return objectPropertyIsEnumerable;
    hasRequiredObjectPropertyIsEnumerable = 1;
    var $propertyIsEnumerable = {}.propertyIsEnumerable;
    var getOwnPropertyDescriptor2 = Object.getOwnPropertyDescriptor;
    var NASHORN_BUG = getOwnPropertyDescriptor2 && !$propertyIsEnumerable.call({ 1: 2 }, 1);
    objectPropertyIsEnumerable.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
      var descriptor = getOwnPropertyDescriptor2(this, V);
      return !!descriptor && descriptor.enumerable;
    } : $propertyIsEnumerable;
    return objectPropertyIsEnumerable;
  }
  var createPropertyDescriptor;
  var hasRequiredCreatePropertyDescriptor;
  function requireCreatePropertyDescriptor() {
    if (hasRequiredCreatePropertyDescriptor) return createPropertyDescriptor;
    hasRequiredCreatePropertyDescriptor = 1;
    createPropertyDescriptor = function(bitmap, value) {
      return {
        enumerable: !(bitmap & 1),
        configurable: !(bitmap & 2),
        writable: !(bitmap & 4),
        value
      };
    };
    return createPropertyDescriptor;
  }
  var functionUncurryThis;
  var hasRequiredFunctionUncurryThis;
  function requireFunctionUncurryThis() {
    if (hasRequiredFunctionUncurryThis) return functionUncurryThis;
    hasRequiredFunctionUncurryThis = 1;
    var NATIVE_BIND = requireFunctionBindNative();
    var FunctionPrototype = Function.prototype;
    var call = FunctionPrototype.call;
    var uncurryThisWithBind = NATIVE_BIND && FunctionPrototype.bind.bind(call, call);
    functionUncurryThis = NATIVE_BIND ? uncurryThisWithBind : function(fn) {
      return function() {
        return call.apply(fn, arguments);
      };
    };
    return functionUncurryThis;
  }
  var classofRaw;
  var hasRequiredClassofRaw;
  function requireClassofRaw() {
    if (hasRequiredClassofRaw) return classofRaw;
    hasRequiredClassofRaw = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var toString2 = uncurryThis({}.toString);
    var stringSlice = uncurryThis("".slice);
    classofRaw = function(it) {
      return stringSlice(toString2(it), 8, -1);
    };
    return classofRaw;
  }
  var indexedObject;
  var hasRequiredIndexedObject;
  function requireIndexedObject() {
    if (hasRequiredIndexedObject) return indexedObject;
    hasRequiredIndexedObject = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var classof2 = requireClassofRaw();
    var $Object = Object;
    var split = uncurryThis("".split);
    indexedObject = fails2(function() {
      return !$Object("z").propertyIsEnumerable(0);
    }) ? function(it) {
      return classof2(it) === "String" ? split(it, "") : $Object(it);
    } : $Object;
    return indexedObject;
  }
  var isNullOrUndefined;
  var hasRequiredIsNullOrUndefined;
  function requireIsNullOrUndefined() {
    if (hasRequiredIsNullOrUndefined) return isNullOrUndefined;
    hasRequiredIsNullOrUndefined = 1;
    isNullOrUndefined = function(it) {
      return it === null || it === void 0;
    };
    return isNullOrUndefined;
  }
  var requireObjectCoercible;
  var hasRequiredRequireObjectCoercible;
  function requireRequireObjectCoercible() {
    if (hasRequiredRequireObjectCoercible) return requireObjectCoercible;
    hasRequiredRequireObjectCoercible = 1;
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    var $TypeError = TypeError;
    requireObjectCoercible = function(it) {
      if (isNullOrUndefined2(it)) throw new $TypeError("Can't call method on " + it);
      return it;
    };
    return requireObjectCoercible;
  }
  var toIndexedObject;
  var hasRequiredToIndexedObject;
  function requireToIndexedObject() {
    if (hasRequiredToIndexedObject) return toIndexedObject;
    hasRequiredToIndexedObject = 1;
    var IndexedObject = requireIndexedObject();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    toIndexedObject = function(it) {
      return IndexedObject(requireObjectCoercible2(it));
    };
    return toIndexedObject;
  }
  var isCallable;
  var hasRequiredIsCallable;
  function requireIsCallable() {
    if (hasRequiredIsCallable) return isCallable;
    hasRequiredIsCallable = 1;
    var documentAll = typeof document == "object" && document.all;
    isCallable = typeof documentAll == "undefined" && documentAll !== void 0 ? function(argument) {
      return typeof argument == "function" || argument === documentAll;
    } : function(argument) {
      return typeof argument == "function";
    };
    return isCallable;
  }
  var isObject;
  var hasRequiredIsObject;
  function requireIsObject() {
    if (hasRequiredIsObject) return isObject;
    hasRequiredIsObject = 1;
    var isCallable2 = requireIsCallable();
    isObject = function(it) {
      return typeof it == "object" ? it !== null : isCallable2(it);
    };
    return isObject;
  }
  var getBuiltIn;
  var hasRequiredGetBuiltIn;
  function requireGetBuiltIn() {
    if (hasRequiredGetBuiltIn) return getBuiltIn;
    hasRequiredGetBuiltIn = 1;
    var globalThis2 = requireGlobalThis();
    var isCallable2 = requireIsCallable();
    var aFunction = function(argument) {
      return isCallable2(argument) ? argument : void 0;
    };
    getBuiltIn = function(namespace, method) {
      return arguments.length < 2 ? aFunction(globalThis2[namespace]) : globalThis2[namespace] && globalThis2[namespace][method];
    };
    return getBuiltIn;
  }
  var objectIsPrototypeOf;
  var hasRequiredObjectIsPrototypeOf;
  function requireObjectIsPrototypeOf() {
    if (hasRequiredObjectIsPrototypeOf) return objectIsPrototypeOf;
    hasRequiredObjectIsPrototypeOf = 1;
    var uncurryThis = requireFunctionUncurryThis();
    objectIsPrototypeOf = uncurryThis({}.isPrototypeOf);
    return objectIsPrototypeOf;
  }
  var environmentUserAgent;
  var hasRequiredEnvironmentUserAgent;
  function requireEnvironmentUserAgent() {
    if (hasRequiredEnvironmentUserAgent) return environmentUserAgent;
    hasRequiredEnvironmentUserAgent = 1;
    var globalThis2 = requireGlobalThis();
    var navigator = globalThis2.navigator;
    var userAgent = navigator && navigator.userAgent;
    environmentUserAgent = userAgent ? String(userAgent) : "";
    return environmentUserAgent;
  }
  var environmentV8Version;
  var hasRequiredEnvironmentV8Version;
  function requireEnvironmentV8Version() {
    if (hasRequiredEnvironmentV8Version) return environmentV8Version;
    hasRequiredEnvironmentV8Version = 1;
    var globalThis2 = requireGlobalThis();
    var userAgent = requireEnvironmentUserAgent();
    var process = globalThis2.process;
    var Deno2 = globalThis2.Deno;
    var versions = process && process.versions || Deno2 && Deno2.version;
    var v8 = versions && versions.v8;
    var match, version;
    if (v8) {
      match = v8.split(".");
      version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
    }
    if (!version && userAgent) {
      match = userAgent.match(/Edge\/(\d+)/);
      if (!match || match[1] >= 74) {
        match = userAgent.match(/Chrome\/(\d+)/);
        if (match) version = +match[1];
      }
    }
    environmentV8Version = version;
    return environmentV8Version;
  }
  var symbolConstructorDetection;
  var hasRequiredSymbolConstructorDetection;
  function requireSymbolConstructorDetection() {
    if (hasRequiredSymbolConstructorDetection) return symbolConstructorDetection;
    hasRequiredSymbolConstructorDetection = 1;
    var V8_VERSION = requireEnvironmentV8Version();
    var fails2 = requireFails();
    var globalThis2 = requireGlobalThis();
    var $String = globalThis2.String;
    symbolConstructorDetection = !!Object.getOwnPropertySymbols && !fails2(function() {
      var symbol = Symbol("symbol detection");
      return !$String(symbol) || !(Object(symbol) instanceof Symbol) || // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
      !Symbol.sham && V8_VERSION && V8_VERSION < 41;
    });
    return symbolConstructorDetection;
  }
  var useSymbolAsUid;
  var hasRequiredUseSymbolAsUid;
  function requireUseSymbolAsUid() {
    if (hasRequiredUseSymbolAsUid) return useSymbolAsUid;
    hasRequiredUseSymbolAsUid = 1;
    var NATIVE_SYMBOL = requireSymbolConstructorDetection();
    useSymbolAsUid = NATIVE_SYMBOL && !Symbol.sham && typeof Symbol.iterator == "symbol";
    return useSymbolAsUid;
  }
  var isSymbol;
  var hasRequiredIsSymbol;
  function requireIsSymbol() {
    if (hasRequiredIsSymbol) return isSymbol;
    hasRequiredIsSymbol = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var isCallable2 = requireIsCallable();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var USE_SYMBOL_AS_UID = requireUseSymbolAsUid();
    var $Object = Object;
    isSymbol = USE_SYMBOL_AS_UID ? function(it) {
      return typeof it == "symbol";
    } : function(it) {
      var $Symbol = getBuiltIn2("Symbol");
      return isCallable2($Symbol) && isPrototypeOf($Symbol.prototype, $Object(it));
    };
    return isSymbol;
  }
  var tryToString;
  var hasRequiredTryToString;
  function requireTryToString() {
    if (hasRequiredTryToString) return tryToString;
    hasRequiredTryToString = 1;
    var $String = String;
    tryToString = function(argument) {
      try {
        return $String(argument);
      } catch (error) {
        return "Object";
      }
    };
    return tryToString;
  }
  var aCallable;
  var hasRequiredACallable;
  function requireACallable() {
    if (hasRequiredACallable) return aCallable;
    hasRequiredACallable = 1;
    var isCallable2 = requireIsCallable();
    var tryToString2 = requireTryToString();
    var $TypeError = TypeError;
    aCallable = function(argument) {
      if (isCallable2(argument)) return argument;
      throw new $TypeError(tryToString2(argument) + " is not a function");
    };
    return aCallable;
  }
  var getMethod;
  var hasRequiredGetMethod;
  function requireGetMethod() {
    if (hasRequiredGetMethod) return getMethod;
    hasRequiredGetMethod = 1;
    var aCallable2 = requireACallable();
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    getMethod = function(V, P) {
      var func = V[P];
      return isNullOrUndefined2(func) ? void 0 : aCallable2(func);
    };
    return getMethod;
  }
  var ordinaryToPrimitive;
  var hasRequiredOrdinaryToPrimitive;
  function requireOrdinaryToPrimitive() {
    if (hasRequiredOrdinaryToPrimitive) return ordinaryToPrimitive;
    hasRequiredOrdinaryToPrimitive = 1;
    var call = requireFunctionCall();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var $TypeError = TypeError;
    ordinaryToPrimitive = function(input, pref) {
      var fn, val;
      if (pref === "string" && isCallable2(fn = input.toString) && !isObject2(val = call(fn, input))) return val;
      if (isCallable2(fn = input.valueOf) && !isObject2(val = call(fn, input))) return val;
      if (pref !== "string" && isCallable2(fn = input.toString) && !isObject2(val = call(fn, input))) return val;
      throw new $TypeError("Can't convert object to primitive value");
    };
    return ordinaryToPrimitive;
  }
  var sharedStore = { exports: {} };
  var isPure;
  var hasRequiredIsPure;
  function requireIsPure() {
    if (hasRequiredIsPure) return isPure;
    hasRequiredIsPure = 1;
    isPure = false;
    return isPure;
  }
  var defineGlobalProperty;
  var hasRequiredDefineGlobalProperty;
  function requireDefineGlobalProperty() {
    if (hasRequiredDefineGlobalProperty) return defineGlobalProperty;
    hasRequiredDefineGlobalProperty = 1;
    var globalThis2 = requireGlobalThis();
    var defineProperty = Object.defineProperty;
    defineGlobalProperty = function(key, value) {
      try {
        defineProperty(globalThis2, key, { value, configurable: true, writable: true });
      } catch (error) {
        globalThis2[key] = value;
      }
      return value;
    };
    return defineGlobalProperty;
  }
  var hasRequiredSharedStore;
  function requireSharedStore() {
    if (hasRequiredSharedStore) return sharedStore.exports;
    hasRequiredSharedStore = 1;
    var IS_PURE = requireIsPure();
    var globalThis2 = requireGlobalThis();
    var defineGlobalProperty2 = requireDefineGlobalProperty();
    var SHARED = "__core-js_shared__";
    var store = sharedStore.exports = globalThis2[SHARED] || defineGlobalProperty2(SHARED, {});
    (store.versions || (store.versions = [])).push({
      version: "3.50.0",
      mode: IS_PURE ? "pure" : "global",
      copyright: "\xA9 2013\u20132025 Denis Pushkarev (zloirock.ru), 2025\u20132026 CoreJS Company (core-js.io). All rights reserved.",
      license: "https://github.com/zloirock/core-js/blob/v3.50.0/LICENSE",
      source: "https://github.com/zloirock/core-js"
    });
    return sharedStore.exports;
  }
  var shared;
  var hasRequiredShared;
  function requireShared() {
    if (hasRequiredShared) return shared;
    hasRequiredShared = 1;
    var store = requireSharedStore();
    var create2 = Object.create || Object;
    shared = function(key, value) {
      return store[key] || (store[key] = value || create2(null));
    };
    return shared;
  }
  var toObject;
  var hasRequiredToObject;
  function requireToObject() {
    if (hasRequiredToObject) return toObject;
    hasRequiredToObject = 1;
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var $Object = Object;
    toObject = function(argument) {
      return $Object(requireObjectCoercible2(argument));
    };
    return toObject;
  }
  var hasOwnProperty_1;
  var hasRequiredHasOwnProperty;
  function requireHasOwnProperty() {
    if (hasRequiredHasOwnProperty) return hasOwnProperty_1;
    hasRequiredHasOwnProperty = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var toObject2 = requireToObject();
    var hasOwnProperty = uncurryThis({}.hasOwnProperty);
    hasOwnProperty_1 = Object.hasOwn || function hasOwn(it, key) {
      return hasOwnProperty(toObject2(it), key);
    };
    return hasOwnProperty_1;
  }
  var uid;
  var hasRequiredUid;
  function requireUid() {
    if (hasRequiredUid) return uid;
    hasRequiredUid = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var id = 0;
    var postfix = Math.random();
    var toString2 = uncurryThis(1.1.toString);
    uid = function(key) {
      return "Symbol(" + (key === void 0 ? "" : key) + ")_" + toString2(++id + postfix, 36);
    };
    return uid;
  }
  var wellKnownSymbol;
  var hasRequiredWellKnownSymbol;
  function requireWellKnownSymbol() {
    if (hasRequiredWellKnownSymbol) return wellKnownSymbol;
    hasRequiredWellKnownSymbol = 1;
    var globalThis2 = requireGlobalThis();
    var shared2 = requireShared();
    var hasOwn = requireHasOwnProperty();
    var uid2 = requireUid();
    var NATIVE_SYMBOL = requireSymbolConstructorDetection();
    var USE_SYMBOL_AS_UID = requireUseSymbolAsUid();
    var Symbol2 = globalThis2.Symbol;
    var WellKnownSymbolsStore = shared2("wks");
    var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol2["for"] || Symbol2 : Symbol2 && Symbol2.withoutSetter || uid2;
    wellKnownSymbol = function(name) {
      if (!hasOwn(WellKnownSymbolsStore, name)) {
        WellKnownSymbolsStore[name] = NATIVE_SYMBOL && hasOwn(Symbol2, name) ? Symbol2[name] : createWellKnownSymbol("Symbol." + name);
      }
      return WellKnownSymbolsStore[name];
    };
    return wellKnownSymbol;
  }
  var toPrimitive;
  var hasRequiredToPrimitive;
  function requireToPrimitive() {
    if (hasRequiredToPrimitive) return toPrimitive;
    hasRequiredToPrimitive = 1;
    var call = requireFunctionCall();
    var isObject2 = requireIsObject();
    var isSymbol2 = requireIsSymbol();
    var getMethod2 = requireGetMethod();
    var ordinaryToPrimitive2 = requireOrdinaryToPrimitive();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var $TypeError = TypeError;
    var TO_PRIMITIVE = wellKnownSymbol2("toPrimitive");
    toPrimitive = function(input, pref) {
      if (!isObject2(input) || isSymbol2(input)) return input;
      var exoticToPrim = getMethod2(input, TO_PRIMITIVE);
      var result;
      if (exoticToPrim) {
        if (pref === void 0) pref = "default";
        result = call(exoticToPrim, input, pref);
        if (!isObject2(result) || isSymbol2(result)) return result;
        throw new $TypeError("Can't convert object to primitive value");
      }
      if (pref === void 0) pref = "number";
      return ordinaryToPrimitive2(input, pref);
    };
    return toPrimitive;
  }
  var toPropertyKey;
  var hasRequiredToPropertyKey;
  function requireToPropertyKey() {
    if (hasRequiredToPropertyKey) return toPropertyKey;
    hasRequiredToPropertyKey = 1;
    var toPrimitive2 = requireToPrimitive();
    var isSymbol2 = requireIsSymbol();
    toPropertyKey = function(argument) {
      var key = toPrimitive2(argument, "string");
      return isSymbol2(key) ? key : key + "";
    };
    return toPropertyKey;
  }
  var documentCreateElement;
  var hasRequiredDocumentCreateElement;
  function requireDocumentCreateElement() {
    if (hasRequiredDocumentCreateElement) return documentCreateElement;
    hasRequiredDocumentCreateElement = 1;
    var globalThis2 = requireGlobalThis();
    var isObject2 = requireIsObject();
    var document2 = globalThis2.document;
    var EXISTS = isObject2(document2) && isObject2(document2.createElement);
    documentCreateElement = function(it) {
      return EXISTS ? document2.createElement(it) : {};
    };
    return documentCreateElement;
  }
  var ie8DomDefine;
  var hasRequiredIe8DomDefine;
  function requireIe8DomDefine() {
    if (hasRequiredIe8DomDefine) return ie8DomDefine;
    hasRequiredIe8DomDefine = 1;
    var DESCRIPTORS = requireDescriptors();
    var fails2 = requireFails();
    var createElement = requireDocumentCreateElement();
    ie8DomDefine = !DESCRIPTORS && !fails2(function() {
      return Object.defineProperty(createElement("div"), "a", {
        get: function() {
          return 7;
        }
      }).a !== 7;
    });
    return ie8DomDefine;
  }
  var hasRequiredObjectGetOwnPropertyDescriptor;
  function requireObjectGetOwnPropertyDescriptor() {
    if (hasRequiredObjectGetOwnPropertyDescriptor) return objectGetOwnPropertyDescriptor;
    hasRequiredObjectGetOwnPropertyDescriptor = 1;
    var DESCRIPTORS = requireDescriptors();
    var call = requireFunctionCall();
    var propertyIsEnumerableModule = requireObjectPropertyIsEnumerable();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var toIndexedObject2 = requireToIndexedObject();
    var toPropertyKey2 = requireToPropertyKey();
    var hasOwn = requireHasOwnProperty();
    var IE8_DOM_DEFINE = requireIe8DomDefine();
    var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
    objectGetOwnPropertyDescriptor.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor2(O, P) {
      O = toIndexedObject2(O);
      P = toPropertyKey2(P);
      if (IE8_DOM_DEFINE) try {
        return $getOwnPropertyDescriptor(O, P);
      } catch (error) {
      }
      if (hasOwn(O, P)) return createPropertyDescriptor2(!call(propertyIsEnumerableModule.f, O, P), O[P]);
    };
    return objectGetOwnPropertyDescriptor;
  }
  var objectDefineProperty = {};
  var v8PrototypeDefineBug;
  var hasRequiredV8PrototypeDefineBug;
  function requireV8PrototypeDefineBug() {
    if (hasRequiredV8PrototypeDefineBug) return v8PrototypeDefineBug;
    hasRequiredV8PrototypeDefineBug = 1;
    var DESCRIPTORS = requireDescriptors();
    var fails2 = requireFails();
    v8PrototypeDefineBug = DESCRIPTORS && fails2(function() {
      return Object.defineProperty(function() {
      }, "prototype", {
        value: 42,
        writable: false
      }).prototype !== 42;
    });
    return v8PrototypeDefineBug;
  }
  var anObject;
  var hasRequiredAnObject;
  function requireAnObject() {
    if (hasRequiredAnObject) return anObject;
    hasRequiredAnObject = 1;
    var isObject2 = requireIsObject();
    var $String = String;
    var $TypeError = TypeError;
    anObject = function(argument) {
      if (isObject2(argument)) return argument;
      throw new $TypeError($String(argument) + " is not an object");
    };
    return anObject;
  }
  var hasRequiredObjectDefineProperty;
  function requireObjectDefineProperty() {
    if (hasRequiredObjectDefineProperty) return objectDefineProperty;
    hasRequiredObjectDefineProperty = 1;
    var DESCRIPTORS = requireDescriptors();
    var IE8_DOM_DEFINE = requireIe8DomDefine();
    var V8_PROTOTYPE_DEFINE_BUG = requireV8PrototypeDefineBug();
    var anObject2 = requireAnObject();
    var toPropertyKey2 = requireToPropertyKey();
    var $TypeError = TypeError;
    var $defineProperty = Object.defineProperty;
    var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
    var ENUMERABLE = "enumerable";
    var CONFIGURABLE = "configurable";
    var WRITABLE = "writable";
    objectDefineProperty.f = DESCRIPTORS ? V8_PROTOTYPE_DEFINE_BUG ? function defineProperty(O, P, Attributes) {
      anObject2(O);
      P = toPropertyKey2(P);
      anObject2(Attributes);
      if (typeof O === "function" && P === "prototype" && "value" in Attributes && WRITABLE in Attributes && !Attributes[WRITABLE]) {
        var current = $getOwnPropertyDescriptor(O, P);
        if (current && current[WRITABLE]) {
          O[P] = Attributes.value;
          Attributes = {
            configurable: CONFIGURABLE in Attributes ? Attributes[CONFIGURABLE] : current[CONFIGURABLE],
            enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],
            writable: false
          };
        }
      }
      return $defineProperty(O, P, Attributes);
    } : $defineProperty : function defineProperty(O, P, Attributes) {
      anObject2(O);
      P = toPropertyKey2(P);
      anObject2(Attributes);
      if (IE8_DOM_DEFINE) try {
        return $defineProperty(O, P, Attributes);
      } catch (error) {
      }
      if ("get" in Attributes || "set" in Attributes) throw new $TypeError("Accessors not supported");
      if ("value" in Attributes) O[P] = Attributes.value;
      return O;
    };
    return objectDefineProperty;
  }
  var createNonEnumerableProperty;
  var hasRequiredCreateNonEnumerableProperty;
  function requireCreateNonEnumerableProperty() {
    if (hasRequiredCreateNonEnumerableProperty) return createNonEnumerableProperty;
    hasRequiredCreateNonEnumerableProperty = 1;
    var DESCRIPTORS = requireDescriptors();
    var definePropertyModule = requireObjectDefineProperty();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    createNonEnumerableProperty = DESCRIPTORS ? function(object, key, value) {
      return definePropertyModule.f(object, key, createPropertyDescriptor2(1, value));
    } : function(object, key, value) {
      object[key] = value;
      return object;
    };
    return createNonEnumerableProperty;
  }
  var makeBuiltIn = { exports: {} };
  var functionName;
  var hasRequiredFunctionName;
  function requireFunctionName() {
    if (hasRequiredFunctionName) return functionName;
    hasRequiredFunctionName = 1;
    var DESCRIPTORS = requireDescriptors();
    var hasOwn = requireHasOwnProperty();
    var FunctionPrototype = Function.prototype;
    var getDescriptor = DESCRIPTORS && Object.getOwnPropertyDescriptor;
    var EXISTS = hasOwn(FunctionPrototype, "name");
    var PROPER = EXISTS && function something() {
    }.name === "something";
    var CONFIGURABLE = EXISTS && (!DESCRIPTORS || DESCRIPTORS && getDescriptor(FunctionPrototype, "name").configurable);
    functionName = {
      EXISTS,
      PROPER,
      CONFIGURABLE
    };
    return functionName;
  }
  var inspectSource;
  var hasRequiredInspectSource;
  function requireInspectSource() {
    if (hasRequiredInspectSource) return inspectSource;
    hasRequiredInspectSource = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var isCallable2 = requireIsCallable();
    var store = requireSharedStore();
    var functionToString = uncurryThis(Function.toString);
    if (!isCallable2(store.inspectSource)) {
      store.inspectSource = function(it) {
        return functionToString(it);
      };
    }
    inspectSource = store.inspectSource;
    return inspectSource;
  }
  var weakMapBasicDetection;
  var hasRequiredWeakMapBasicDetection;
  function requireWeakMapBasicDetection() {
    if (hasRequiredWeakMapBasicDetection) return weakMapBasicDetection;
    hasRequiredWeakMapBasicDetection = 1;
    var globalThis2 = requireGlobalThis();
    var isCallable2 = requireIsCallable();
    var WeakMap2 = globalThis2.WeakMap;
    weakMapBasicDetection = isCallable2(WeakMap2) && /native code/.test(String(WeakMap2));
    return weakMapBasicDetection;
  }
  var sharedKey;
  var hasRequiredSharedKey;
  function requireSharedKey() {
    if (hasRequiredSharedKey) return sharedKey;
    hasRequiredSharedKey = 1;
    var shared2 = requireShared();
    var uid2 = requireUid();
    var keys = shared2("keys");
    sharedKey = function(key) {
      return keys[key] || (keys[key] = uid2(key));
    };
    return sharedKey;
  }
  var hiddenKeys;
  var hasRequiredHiddenKeys;
  function requireHiddenKeys() {
    if (hasRequiredHiddenKeys) return hiddenKeys;
    hasRequiredHiddenKeys = 1;
    hiddenKeys = {};
    return hiddenKeys;
  }
  var internalState;
  var hasRequiredInternalState;
  function requireInternalState() {
    if (hasRequiredInternalState) return internalState;
    hasRequiredInternalState = 1;
    var NATIVE_WEAK_MAP = requireWeakMapBasicDetection();
    var globalThis2 = requireGlobalThis();
    var isObject2 = requireIsObject();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var hasOwn = requireHasOwnProperty();
    var shared2 = requireSharedStore();
    var sharedKey2 = requireSharedKey();
    var hiddenKeys2 = requireHiddenKeys();
    var OBJECT_ALREADY_INITIALIZED = "Object already initialized";
    var TypeError2 = globalThis2.TypeError;
    var WeakMap2 = globalThis2.WeakMap;
    var set, get, has;
    var enforce = function(it) {
      return has(it) ? get(it) : set(it, {});
    };
    var getterFor = function(TYPE) {
      return function(it) {
        var state;
        if (!isObject2(it) || (state = get(it)).type !== TYPE) {
          throw new TypeError2("Incompatible receiver, " + TYPE + " required");
        }
        return state;
      };
    };
    if (NATIVE_WEAK_MAP || shared2.state) {
      var store = shared2.state || (shared2.state = new WeakMap2());
      store.get = store.get;
      store.has = store.has;
      store.set = store.set;
      set = function(it, metadata) {
        if (store.has(it)) throw new TypeError2(OBJECT_ALREADY_INITIALIZED);
        metadata.facade = it;
        store.set(it, metadata);
        return metadata;
      };
      get = function(it) {
        return store.get(it) || {};
      };
      has = function(it) {
        return store.has(it);
      };
    } else {
      var STATE = sharedKey2("state");
      hiddenKeys2[STATE] = true;
      set = function(it, metadata) {
        if (hasOwn(it, STATE)) throw new TypeError2(OBJECT_ALREADY_INITIALIZED);
        metadata.facade = it;
        createNonEnumerableProperty2(it, STATE, metadata);
        return metadata;
      };
      get = function(it) {
        return hasOwn(it, STATE) ? it[STATE] : {};
      };
      has = function(it) {
        return hasOwn(it, STATE);
      };
    }
    internalState = {
      set,
      get,
      has,
      enforce,
      getterFor
    };
    return internalState;
  }
  var hasRequiredMakeBuiltIn;
  function requireMakeBuiltIn() {
    if (hasRequiredMakeBuiltIn) return makeBuiltIn.exports;
    hasRequiredMakeBuiltIn = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var isCallable2 = requireIsCallable();
    var hasOwn = requireHasOwnProperty();
    var DESCRIPTORS = requireDescriptors();
    var CONFIGURABLE_FUNCTION_NAME = requireFunctionName().CONFIGURABLE;
    var inspectSource2 = requireInspectSource();
    var InternalStateModule = requireInternalState();
    var enforceInternalState = InternalStateModule.enforce;
    var getInternalState = InternalStateModule.get;
    var $String = String;
    var defineProperty = Object.defineProperty;
    var stringSlice = uncurryThis("".slice);
    var replace = uncurryThis("".replace);
    var join = uncurryThis([].join);
    var CONFIGURABLE_LENGTH = DESCRIPTORS && !fails2(function() {
      return defineProperty(function() {
      }, "length", { value: 8 }).length !== 8;
    });
    var TEMPLATE = String(String).split("String");
    var makeBuiltIn$1 = makeBuiltIn.exports = function(value, name, options) {
      if (stringSlice($String(name), 0, 7) === "Symbol(") {
        name = "[" + replace($String(name), /^Symbol\(([^)]*)\).*$/, "$1") + "]";
      }
      if (options && options.getter) name = "get " + name;
      if (options && options.setter) name = "set " + name;
      if (!hasOwn(value, "name") || CONFIGURABLE_FUNCTION_NAME && value.name !== name) {
        if (DESCRIPTORS) defineProperty(value, "name", { value: name, configurable: true });
        else value.name = name;
      }
      if (CONFIGURABLE_LENGTH && options && hasOwn(options, "arity") && value.length !== options.arity) {
        defineProperty(value, "length", { value: options.arity });
      }
      try {
        if (options && hasOwn(options, "constructor") && options.constructor) {
          if (DESCRIPTORS) defineProperty(value, "prototype", { writable: false });
        } else if (value.prototype) value.prototype = void 0;
      } catch (error) {
      }
      var state = enforceInternalState(value);
      if (!hasOwn(state, "source")) {
        state.source = join(TEMPLATE, typeof name == "string" ? name : "");
      }
      return value;
    };
    Function.prototype.toString = makeBuiltIn$1(function toString2() {
      return isCallable2(this) && getInternalState(this).source || inspectSource2(this);
    }, "toString");
    return makeBuiltIn.exports;
  }
  var defineBuiltIn;
  var hasRequiredDefineBuiltIn;
  function requireDefineBuiltIn() {
    if (hasRequiredDefineBuiltIn) return defineBuiltIn;
    hasRequiredDefineBuiltIn = 1;
    var isCallable2 = requireIsCallable();
    var definePropertyModule = requireObjectDefineProperty();
    var makeBuiltIn2 = requireMakeBuiltIn();
    var defineGlobalProperty2 = requireDefineGlobalProperty();
    defineBuiltIn = function(O, key, value, options) {
      if (!options) options = {};
      var simple = options.enumerable;
      var name = options.name !== void 0 ? options.name : key;
      if (isCallable2(value)) makeBuiltIn2(value, name, options);
      if (options.global) {
        if (simple) O[key] = value;
        else defineGlobalProperty2(key, value);
      } else {
        try {
          if (!options.unsafe) delete O[key];
          else if (O[key]) simple = true;
        } catch (error) {
        }
        if (simple) O[key] = value;
        else definePropertyModule.f(O, key, {
          value,
          enumerable: false,
          configurable: !options.nonConfigurable,
          writable: !options.nonWritable
        });
      }
      return O;
    };
    return defineBuiltIn;
  }
  var objectGetOwnPropertyNames = {};
  var mathTrunc;
  var hasRequiredMathTrunc;
  function requireMathTrunc() {
    if (hasRequiredMathTrunc) return mathTrunc;
    hasRequiredMathTrunc = 1;
    var ceil = Math.ceil;
    var floor = Math.floor;
    mathTrunc = Math.trunc || function trunc(x) {
      var n = +x;
      return (n > 0 ? floor : ceil)(n);
    };
    return mathTrunc;
  }
  var toIntegerOrInfinity;
  var hasRequiredToIntegerOrInfinity;
  function requireToIntegerOrInfinity() {
    if (hasRequiredToIntegerOrInfinity) return toIntegerOrInfinity;
    hasRequiredToIntegerOrInfinity = 1;
    var trunc = requireMathTrunc();
    toIntegerOrInfinity = function(argument) {
      var number = +argument;
      return number !== number || number === 0 ? 0 : trunc(number);
    };
    return toIntegerOrInfinity;
  }
  var toAbsoluteIndex;
  var hasRequiredToAbsoluteIndex;
  function requireToAbsoluteIndex() {
    if (hasRequiredToAbsoluteIndex) return toAbsoluteIndex;
    hasRequiredToAbsoluteIndex = 1;
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var max = Math.max;
    var min = Math.min;
    toAbsoluteIndex = function(index, length) {
      var integer = toIntegerOrInfinity2(index);
      return integer < 0 ? max(integer + length, 0) : min(integer, length);
    };
    return toAbsoluteIndex;
  }
  var toLength;
  var hasRequiredToLength;
  function requireToLength() {
    if (hasRequiredToLength) return toLength;
    hasRequiredToLength = 1;
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var min = Math.min;
    toLength = function(argument) {
      var len = toIntegerOrInfinity2(argument);
      return len > 0 ? min(len, 9007199254740991) : 0;
    };
    return toLength;
  }
  var lengthOfArrayLike;
  var hasRequiredLengthOfArrayLike;
  function requireLengthOfArrayLike() {
    if (hasRequiredLengthOfArrayLike) return lengthOfArrayLike;
    hasRequiredLengthOfArrayLike = 1;
    var toLength2 = requireToLength();
    lengthOfArrayLike = function(obj) {
      return toLength2(obj.length);
    };
    return lengthOfArrayLike;
  }
  var arrayIncludes;
  var hasRequiredArrayIncludes;
  function requireArrayIncludes() {
    if (hasRequiredArrayIncludes) return arrayIncludes;
    hasRequiredArrayIncludes = 1;
    var toIndexedObject2 = requireToIndexedObject();
    var toAbsoluteIndex2 = requireToAbsoluteIndex();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var createMethod = function(IS_INCLUDES) {
      return function($this, el, fromIndex) {
        var O = toIndexedObject2($this);
        var length = lengthOfArrayLike2(O);
        if (length === 0) return !IS_INCLUDES && -1;
        var index = toAbsoluteIndex2(fromIndex, length);
        var value;
        if (IS_INCLUDES && el !== el) while (length > index) {
          value = O[index++];
          if (value !== value) return true;
        }
        else for (; length > index; index++) {
          if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
        }
        return !IS_INCLUDES && -1;
      };
    };
    arrayIncludes = {
      // `Array.prototype.includes` method
      // https://tc39.es/ecma262/#sec-array.prototype.includes
      includes: createMethod(true),
      // `Array.prototype.indexOf` method
      // https://tc39.es/ecma262/#sec-array.prototype.indexof
      indexOf: createMethod(false)
    };
    return arrayIncludes;
  }
  var objectKeysInternal;
  var hasRequiredObjectKeysInternal;
  function requireObjectKeysInternal() {
    if (hasRequiredObjectKeysInternal) return objectKeysInternal;
    hasRequiredObjectKeysInternal = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var hasOwn = requireHasOwnProperty();
    var toIndexedObject2 = requireToIndexedObject();
    var indexOf = requireArrayIncludes().indexOf;
    var hiddenKeys2 = requireHiddenKeys();
    var push = uncurryThis([].push);
    objectKeysInternal = function(object, names) {
      var O = toIndexedObject2(object);
      var i = 0;
      var result = [];
      var key;
      for (key in O) !hasOwn(hiddenKeys2, key) && hasOwn(O, key) && push(result, key);
      while (names.length > i) if (hasOwn(O, key = names[i++])) {
        ~indexOf(result, key) || push(result, key);
      }
      return result;
    };
    return objectKeysInternal;
  }
  var enumBugKeys;
  var hasRequiredEnumBugKeys;
  function requireEnumBugKeys() {
    if (hasRequiredEnumBugKeys) return enumBugKeys;
    hasRequiredEnumBugKeys = 1;
    enumBugKeys = [
      "constructor",
      "hasOwnProperty",
      "isPrototypeOf",
      "propertyIsEnumerable",
      "toLocaleString",
      "toString",
      "valueOf"
    ];
    return enumBugKeys;
  }
  var hasRequiredObjectGetOwnPropertyNames;
  function requireObjectGetOwnPropertyNames() {
    if (hasRequiredObjectGetOwnPropertyNames) return objectGetOwnPropertyNames;
    hasRequiredObjectGetOwnPropertyNames = 1;
    var internalObjectKeys = requireObjectKeysInternal();
    var enumBugKeys2 = requireEnumBugKeys();
    var hiddenKeys2 = enumBugKeys2.concat("length", "prototype");
    objectGetOwnPropertyNames.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
      return internalObjectKeys(O, hiddenKeys2);
    };
    return objectGetOwnPropertyNames;
  }
  var objectGetOwnPropertySymbols = {};
  var hasRequiredObjectGetOwnPropertySymbols;
  function requireObjectGetOwnPropertySymbols() {
    if (hasRequiredObjectGetOwnPropertySymbols) return objectGetOwnPropertySymbols;
    hasRequiredObjectGetOwnPropertySymbols = 1;
    objectGetOwnPropertySymbols.f = Object.getOwnPropertySymbols;
    return objectGetOwnPropertySymbols;
  }
  var ownKeys$1;
  var hasRequiredOwnKeys;
  function requireOwnKeys() {
    if (hasRequiredOwnKeys) return ownKeys$1;
    hasRequiredOwnKeys = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var getOwnPropertyNamesModule = requireObjectGetOwnPropertyNames();
    var getOwnPropertySymbolsModule = requireObjectGetOwnPropertySymbols();
    var anObject2 = requireAnObject();
    var concat = uncurryThis([].concat);
    ownKeys$1 = getBuiltIn2("Reflect", "ownKeys") || function ownKeys2(it) {
      var keys = getOwnPropertyNamesModule.f(anObject2(it));
      var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
      return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;
    };
    return ownKeys$1;
  }
  var copyConstructorProperties;
  var hasRequiredCopyConstructorProperties;
  function requireCopyConstructorProperties() {
    if (hasRequiredCopyConstructorProperties) return copyConstructorProperties;
    hasRequiredCopyConstructorProperties = 1;
    var hasOwn = requireHasOwnProperty();
    var ownKeys2 = requireOwnKeys();
    var getOwnPropertyDescriptorModule = requireObjectGetOwnPropertyDescriptor();
    var definePropertyModule = requireObjectDefineProperty();
    copyConstructorProperties = function(target, source, exceptions) {
      var keys = ownKeys2(source);
      var defineProperty = definePropertyModule.f;
      var getOwnPropertyDescriptor2 = getOwnPropertyDescriptorModule.f;
      for (var i = 0; i < keys.length; i++) {
        var key = keys[i];
        if (!hasOwn(target, key) && !(exceptions && hasOwn(exceptions, key))) {
          defineProperty(target, key, getOwnPropertyDescriptor2(source, key));
        }
      }
    };
    return copyConstructorProperties;
  }
  var isForced_1;
  var hasRequiredIsForced;
  function requireIsForced() {
    if (hasRequiredIsForced) return isForced_1;
    hasRequiredIsForced = 1;
    var fails2 = requireFails();
    var isCallable2 = requireIsCallable();
    var replacement = /#|\.prototype\./;
    var isForced = function(feature, detection) {
      var value = data[normalize(feature)];
      return value === POLYFILL ? true : value === NATIVE ? false : isCallable2(detection) ? fails2(detection) : !!detection;
    };
    var normalize = isForced.normalize = function(string) {
      return String(string).replace(replacement, ".").toLowerCase();
    };
    var data = isForced.data = {};
    var NATIVE = isForced.NATIVE = "N";
    var POLYFILL = isForced.POLYFILL = "P";
    isForced_1 = isForced;
    return isForced_1;
  }
  var _export;
  var hasRequired_export;
  function require_export() {
    if (hasRequired_export) return _export;
    hasRequired_export = 1;
    var globalThis2 = requireGlobalThis();
    var getOwnPropertyDescriptor2 = requireObjectGetOwnPropertyDescriptor().f;
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineGlobalProperty2 = requireDefineGlobalProperty();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var isForced = requireIsForced();
    _export = function(options, source) {
      var TARGET = options.target;
      var GLOBAL = options.global;
      var STATIC = options.stat;
      var FORCED, target, key, targetProperty, sourceProperty, descriptor;
      if (GLOBAL) {
        target = globalThis2;
      } else if (STATIC) {
        target = globalThis2[TARGET] || defineGlobalProperty2(TARGET, {});
      } else {
        target = globalThis2[TARGET] && globalThis2[TARGET].prototype;
      }
      if (target) for (key in source) {
        sourceProperty = source[key];
        if (options.dontCallGetSet) {
          descriptor = getOwnPropertyDescriptor2(target, key);
          targetProperty = descriptor && descriptor.value;
        } else targetProperty = target[key];
        FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? "." : "#") + key, options.forced);
        if (!FORCED && targetProperty !== void 0) {
          if (typeof sourceProperty == typeof targetProperty) continue;
          copyConstructorProperties2(sourceProperty, targetProperty);
        }
        if (options.sham || targetProperty && targetProperty.sham) {
          createNonEnumerableProperty2(sourceProperty, "sham", true);
        }
        defineBuiltIn2(target, key, sourceProperty, options);
      }
    };
    return _export;
  }
  var toStringTagSupport;
  var hasRequiredToStringTagSupport;
  function requireToStringTagSupport() {
    if (hasRequiredToStringTagSupport) return toStringTagSupport;
    hasRequiredToStringTagSupport = 1;
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var test = {};
    test[TO_STRING_TAG] = "z";
    toStringTagSupport = String(test) === "[object z]";
    return toStringTagSupport;
  }
  var classof;
  var hasRequiredClassof;
  function requireClassof() {
    if (hasRequiredClassof) return classof;
    hasRequiredClassof = 1;
    var TO_STRING_TAG_SUPPORT = requireToStringTagSupport();
    var isCallable2 = requireIsCallable();
    var classofRaw2 = requireClassofRaw();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var $Object = Object;
    var CORRECT_ARGUMENTS = classofRaw2(/* @__PURE__ */ (function() {
      return arguments;
    })()) === "Arguments";
    var tryGet = function(it, key) {
      try {
        return it[key];
      } catch (error) {
      }
    };
    classof = TO_STRING_TAG_SUPPORT ? classofRaw2 : function(it) {
      var O, tag, result;
      return it === void 0 ? "Undefined" : it === null ? "Null" : typeof (tag = tryGet(O = $Object(it), TO_STRING_TAG)) == "string" ? tag : CORRECT_ARGUMENTS ? classofRaw2(O) : (result = classofRaw2(O)) === "Object" && isCallable2(O.callee) ? "Arguments" : result;
    };
    return classof;
  }
  var toString;
  var hasRequiredToString;
  function requireToString() {
    if (hasRequiredToString) return toString;
    hasRequiredToString = 1;
    var classof2 = requireClassof();
    var $String = String;
    toString = function(argument) {
      if (classof2(argument) === "Symbol") throw new TypeError("Cannot convert a Symbol value to a string");
      return $String(argument);
    };
    return toString;
  }
  var defineBuiltInAccessor;
  var hasRequiredDefineBuiltInAccessor;
  function requireDefineBuiltInAccessor() {
    if (hasRequiredDefineBuiltInAccessor) return defineBuiltInAccessor;
    hasRequiredDefineBuiltInAccessor = 1;
    var makeBuiltIn2 = requireMakeBuiltIn();
    var defineProperty = requireObjectDefineProperty();
    defineBuiltInAccessor = function(target, name, descriptor) {
      if (descriptor.get) makeBuiltIn2(descriptor.get, name, { getter: true });
      if (descriptor.set) makeBuiltIn2(descriptor.set, name, { setter: true });
      return defineProperty.f(target, name, descriptor);
    };
    return defineBuiltInAccessor;
  }
  var hasRequiredEs_symbol_description;
  function requireEs_symbol_description() {
    if (hasRequiredEs_symbol_description) return es_symbol_description;
    hasRequiredEs_symbol_description = 1;
    var $ = require_export();
    var DESCRIPTORS = requireDescriptors();
    var globalThis2 = requireGlobalThis();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var hasOwn = requireHasOwnProperty();
    var isCallable2 = requireIsCallable();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var toString2 = requireToString();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var NativeSymbol = globalThis2.Symbol;
    var SymbolPrototype = NativeSymbol && NativeSymbol.prototype;
    if (DESCRIPTORS && isCallable2(NativeSymbol) && (!("description" in SymbolPrototype) || // Safari 12 bug
    NativeSymbol().description !== void 0)) {
      var EmptyStringDescriptionStore = {};
      var SymbolWrapper = function Symbol2() {
        var description = arguments.length < 1 || arguments[0] === void 0 ? void 0 : toString2(arguments[0]);
        var result = isPrototypeOf(SymbolPrototype, this) ? new NativeSymbol(description) : description === void 0 ? NativeSymbol() : NativeSymbol(description);
        if (description === "") EmptyStringDescriptionStore[result] = true;
        return result;
      };
      copyConstructorProperties2(SymbolWrapper, NativeSymbol);
      var nativeFor = SymbolWrapper["for"];
      SymbolWrapper["for"] = { "for": function(key) {
        var stringKey = toString2(key);
        var symbol = call(nativeFor, this, stringKey);
        if (stringKey === "") EmptyStringDescriptionStore[symbol] = true;
        return symbol;
      } }["for"];
      SymbolWrapper.prototype = SymbolPrototype;
      SymbolPrototype.constructor = SymbolWrapper;
      var NATIVE_SYMBOL = String(NativeSymbol("description detection")) === "Symbol(description detection)";
      var thisSymbolValue = uncurryThis(SymbolPrototype.valueOf);
      var symbolDescriptiveString = uncurryThis(SymbolPrototype.toString);
      var regexp = /^Symbol\((.*)\)[^)]+$/;
      var replace = uncurryThis("".replace);
      var stringSlice = uncurryThis("".slice);
      defineBuiltInAccessor2(SymbolPrototype, "description", {
        configurable: true,
        get: function description() {
          var symbol = thisSymbolValue(this);
          if (hasOwn(EmptyStringDescriptionStore, symbol)) return "";
          var string = symbolDescriptiveString(symbol);
          var desc = NATIVE_SYMBOL ? stringSlice(string, 7, -1) : replace(string, regexp, "$1");
          return desc === "" ? void 0 : desc;
        }
      });
      $({ global: true, constructor: true, forced: true }, {
        Symbol: SymbolWrapper
      });
    }
    return es_symbol_description;
  }
  requireEs_symbol_description();
  var es_symbol_asyncDispose = {};
  var path;
  var hasRequiredPath;
  function requirePath() {
    if (hasRequiredPath) return path;
    hasRequiredPath = 1;
    var globalThis2 = requireGlobalThis();
    path = globalThis2;
    return path;
  }
  var wellKnownSymbolWrapped = {};
  var hasRequiredWellKnownSymbolWrapped;
  function requireWellKnownSymbolWrapped() {
    if (hasRequiredWellKnownSymbolWrapped) return wellKnownSymbolWrapped;
    hasRequiredWellKnownSymbolWrapped = 1;
    var wellKnownSymbol2 = requireWellKnownSymbol();
    wellKnownSymbolWrapped.f = wellKnownSymbol2;
    return wellKnownSymbolWrapped;
  }
  var wellKnownSymbolDefine;
  var hasRequiredWellKnownSymbolDefine;
  function requireWellKnownSymbolDefine() {
    if (hasRequiredWellKnownSymbolDefine) return wellKnownSymbolDefine;
    hasRequiredWellKnownSymbolDefine = 1;
    var path2 = requirePath();
    var hasOwn = requireHasOwnProperty();
    var wrappedWellKnownSymbolModule = requireWellKnownSymbolWrapped();
    var defineProperty = requireObjectDefineProperty().f;
    wellKnownSymbolDefine = function(NAME) {
      var Symbol2 = path2.Symbol || (path2.Symbol = {});
      if (!hasOwn(Symbol2, NAME)) defineProperty(Symbol2, NAME, {
        value: wrappedWellKnownSymbolModule.f(NAME)
      });
    };
    return wellKnownSymbolDefine;
  }
  var hasRequiredEs_symbol_asyncDispose;
  function requireEs_symbol_asyncDispose() {
    if (hasRequiredEs_symbol_asyncDispose) return es_symbol_asyncDispose;
    hasRequiredEs_symbol_asyncDispose = 1;
    var globalThis2 = requireGlobalThis();
    var defineWellKnownSymbol = requireWellKnownSymbolDefine();
    var defineProperty = requireObjectDefineProperty().f;
    var getOwnPropertyDescriptor2 = requireObjectGetOwnPropertyDescriptor().f;
    var Symbol2 = globalThis2.Symbol;
    defineWellKnownSymbol("asyncDispose");
    if (Symbol2) {
      var descriptor = getOwnPropertyDescriptor2(Symbol2, "asyncDispose");
      if (descriptor.enumerable && descriptor.configurable && descriptor.writable) {
        defineProperty(Symbol2, "asyncDispose", { value: descriptor.value, enumerable: false, configurable: false, writable: false });
      }
    }
    return es_symbol_asyncDispose;
  }
  requireEs_symbol_asyncDispose();
  var es_symbol_asyncIterator = {};
  var hasRequiredEs_symbol_asyncIterator;
  function requireEs_symbol_asyncIterator() {
    if (hasRequiredEs_symbol_asyncIterator) return es_symbol_asyncIterator;
    hasRequiredEs_symbol_asyncIterator = 1;
    var defineWellKnownSymbol = requireWellKnownSymbolDefine();
    defineWellKnownSymbol("asyncIterator");
    return es_symbol_asyncIterator;
  }
  requireEs_symbol_asyncIterator();
  var es_symbol_dispose = {};
  var hasRequiredEs_symbol_dispose;
  function requireEs_symbol_dispose() {
    if (hasRequiredEs_symbol_dispose) return es_symbol_dispose;
    hasRequiredEs_symbol_dispose = 1;
    var globalThis2 = requireGlobalThis();
    var defineWellKnownSymbol = requireWellKnownSymbolDefine();
    var defineProperty = requireObjectDefineProperty().f;
    var getOwnPropertyDescriptor2 = requireObjectGetOwnPropertyDescriptor().f;
    var Symbol2 = globalThis2.Symbol;
    defineWellKnownSymbol("dispose");
    if (Symbol2) {
      var descriptor = getOwnPropertyDescriptor2(Symbol2, "dispose");
      if (descriptor.enumerable && descriptor.configurable && descriptor.writable) {
        defineProperty(Symbol2, "dispose", { value: descriptor.value, enumerable: false, configurable: false, writable: false });
      }
    }
    return es_symbol_dispose;
  }
  requireEs_symbol_dispose();
  var es_symbol_matchAll = {};
  var hasRequiredEs_symbol_matchAll;
  function requireEs_symbol_matchAll() {
    if (hasRequiredEs_symbol_matchAll) return es_symbol_matchAll;
    hasRequiredEs_symbol_matchAll = 1;
    var defineWellKnownSymbol = requireWellKnownSymbolDefine();
    defineWellKnownSymbol("matchAll");
    return es_symbol_matchAll;
  }
  requireEs_symbol_matchAll();
  var es_error_cause = {};
  var functionApply;
  var hasRequiredFunctionApply;
  function requireFunctionApply() {
    if (hasRequiredFunctionApply) return functionApply;
    hasRequiredFunctionApply = 1;
    var NATIVE_BIND = requireFunctionBindNative();
    var FunctionPrototype = Function.prototype;
    var apply2 = FunctionPrototype.apply;
    var call = FunctionPrototype.call;
    functionApply = typeof Reflect == "object" && Reflect.apply || (NATIVE_BIND ? call.bind(apply2) : function() {
      return call.apply(apply2, arguments);
    });
    return functionApply;
  }
  var functionUncurryThisAccessor;
  var hasRequiredFunctionUncurryThisAccessor;
  function requireFunctionUncurryThisAccessor() {
    if (hasRequiredFunctionUncurryThisAccessor) return functionUncurryThisAccessor;
    hasRequiredFunctionUncurryThisAccessor = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    functionUncurryThisAccessor = function(object, key, method) {
      try {
        return uncurryThis(aCallable2(Object.getOwnPropertyDescriptor(object, key)[method]));
      } catch (error) {
      }
    };
    return functionUncurryThisAccessor;
  }
  var isPossiblePrototype;
  var hasRequiredIsPossiblePrototype;
  function requireIsPossiblePrototype() {
    if (hasRequiredIsPossiblePrototype) return isPossiblePrototype;
    hasRequiredIsPossiblePrototype = 1;
    var isObject2 = requireIsObject();
    isPossiblePrototype = function(argument) {
      return isObject2(argument) || argument === null;
    };
    return isPossiblePrototype;
  }
  var aPossiblePrototype;
  var hasRequiredAPossiblePrototype;
  function requireAPossiblePrototype() {
    if (hasRequiredAPossiblePrototype) return aPossiblePrototype;
    hasRequiredAPossiblePrototype = 1;
    var isPossiblePrototype2 = requireIsPossiblePrototype();
    var $String = String;
    var $TypeError = TypeError;
    aPossiblePrototype = function(argument) {
      if (isPossiblePrototype2(argument)) return argument;
      throw new $TypeError("Can't set " + $String(argument) + " as a prototype");
    };
    return aPossiblePrototype;
  }
  var objectSetPrototypeOf;
  var hasRequiredObjectSetPrototypeOf;
  function requireObjectSetPrototypeOf() {
    if (hasRequiredObjectSetPrototypeOf) return objectSetPrototypeOf;
    hasRequiredObjectSetPrototypeOf = 1;
    var uncurryThisAccessor = requireFunctionUncurryThisAccessor();
    var isObject2 = requireIsObject();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var aPossiblePrototype2 = requireAPossiblePrototype();
    objectSetPrototypeOf = Object.setPrototypeOf || ("__proto__" in {} ? (function() {
      var CORRECT_SETTER = false;
      var test = {};
      var setter;
      try {
        setter = uncurryThisAccessor(Object.prototype, "__proto__", "set");
        setter(test, []);
        CORRECT_SETTER = test instanceof Array;
      } catch (error) {
      }
      return function setPrototypeOf2(O, proto) {
        requireObjectCoercible2(O);
        aPossiblePrototype2(proto);
        if (!isObject2(O)) return O;
        if (CORRECT_SETTER) setter(O, proto);
        else O.__proto__ = proto;
        return O;
      };
    })() : void 0);
    return objectSetPrototypeOf;
  }
  var proxyAccessor;
  var hasRequiredProxyAccessor;
  function requireProxyAccessor() {
    if (hasRequiredProxyAccessor) return proxyAccessor;
    hasRequiredProxyAccessor = 1;
    var defineProperty = requireObjectDefineProperty().f;
    proxyAccessor = function(Target, Source, key) {
      key in Target || defineProperty(Target, key, {
        configurable: true,
        get: function() {
          return Source[key];
        },
        set: function(it) {
          Source[key] = it;
        }
      });
    };
    return proxyAccessor;
  }
  var inheritIfRequired;
  var hasRequiredInheritIfRequired;
  function requireInheritIfRequired() {
    if (hasRequiredInheritIfRequired) return inheritIfRequired;
    hasRequiredInheritIfRequired = 1;
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    inheritIfRequired = function($this, dummy, Wrapper) {
      var NewTarget, NewTargetPrototype;
      if (
        // it can work only with native `setPrototypeOf`
        setPrototypeOf2 && // we haven't completely correct pre-ES6 way for getting `new.target`, so use this
        isCallable2(NewTarget = dummy.constructor) && NewTarget !== Wrapper && isObject2(NewTargetPrototype = NewTarget.prototype) && NewTargetPrototype !== Wrapper.prototype
      ) setPrototypeOf2($this, NewTargetPrototype);
      return $this;
    };
    return inheritIfRequired;
  }
  var normalizeStringArgument;
  var hasRequiredNormalizeStringArgument;
  function requireNormalizeStringArgument() {
    if (hasRequiredNormalizeStringArgument) return normalizeStringArgument;
    hasRequiredNormalizeStringArgument = 1;
    var toString2 = requireToString();
    normalizeStringArgument = function(argument, $default) {
      return argument === void 0 ? arguments.length < 2 ? "" : $default : toString2(argument);
    };
    return normalizeStringArgument;
  }
  var installErrorCause;
  var hasRequiredInstallErrorCause;
  function requireInstallErrorCause() {
    if (hasRequiredInstallErrorCause) return installErrorCause;
    hasRequiredInstallErrorCause = 1;
    var isObject2 = requireIsObject();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    installErrorCause = function(O, options) {
      if (isObject2(options) && "cause" in options) {
        createNonEnumerableProperty2(O, "cause", options.cause);
      }
    };
    return installErrorCause;
  }
  var errorStackClear;
  var hasRequiredErrorStackClear;
  function requireErrorStackClear() {
    if (hasRequiredErrorStackClear) return errorStackClear;
    hasRequiredErrorStackClear = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var $Error = Error;
    var replace = uncurryThis("".replace);
    var TEST = (function(arg) {
      return String(new $Error(arg).stack);
    })("zxcasd");
    var V8_OR_CHAKRA_STACK_ENTRY = /\n\s*at [^:]*:[^\n]*/;
    var IS_V8_OR_CHAKRA_STACK = V8_OR_CHAKRA_STACK_ENTRY.test(TEST);
    errorStackClear = function(stack, dropEntries) {
      if (IS_V8_OR_CHAKRA_STACK && typeof stack == "string" && !$Error.prepareStackTrace) {
        while (dropEntries--) stack = replace(stack, V8_OR_CHAKRA_STACK_ENTRY, "");
      }
      return stack;
    };
    return errorStackClear;
  }
  var errorStackInstallable;
  var hasRequiredErrorStackInstallable;
  function requireErrorStackInstallable() {
    if (hasRequiredErrorStackInstallable) return errorStackInstallable;
    hasRequiredErrorStackInstallable = 1;
    var fails2 = requireFails();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    errorStackInstallable = !fails2(function() {
      var error = new Error("a");
      if (!("stack" in error)) return true;
      Object.defineProperty(error, "stack", createPropertyDescriptor2(1, 7));
      return error.stack !== 7;
    });
    return errorStackInstallable;
  }
  var errorStackInstall;
  var hasRequiredErrorStackInstall;
  function requireErrorStackInstall() {
    if (hasRequiredErrorStackInstall) return errorStackInstall;
    hasRequiredErrorStackInstall = 1;
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var clearErrorStack = requireErrorStackClear();
    var ERROR_STACK_INSTALLABLE = requireErrorStackInstallable();
    var captureStackTrace = Error.captureStackTrace;
    errorStackInstall = function(error, C, stack, dropEntries) {
      if (ERROR_STACK_INSTALLABLE) {
        if (captureStackTrace) captureStackTrace(error, C);
        else createNonEnumerableProperty2(error, "stack", clearErrorStack(stack, dropEntries));
      }
    };
    return errorStackInstall;
  }
  var wrapErrorConstructorWithCause;
  var hasRequiredWrapErrorConstructorWithCause;
  function requireWrapErrorConstructorWithCause() {
    if (hasRequiredWrapErrorConstructorWithCause) return wrapErrorConstructorWithCause;
    hasRequiredWrapErrorConstructorWithCause = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var hasOwn = requireHasOwnProperty();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var proxyAccessor2 = requireProxyAccessor();
    var inheritIfRequired2 = requireInheritIfRequired();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var installErrorCause2 = requireInstallErrorCause();
    var installErrorStack = requireErrorStackInstall();
    var DESCRIPTORS = requireDescriptors();
    var IS_PURE = requireIsPure();
    wrapErrorConstructorWithCause = function(FULL_NAME, wrapper, FORCED, IS_AGGREGATE_ERROR) {
      var STACK_TRACE_LIMIT = "stackTraceLimit";
      var OPTIONS_POSITION = IS_AGGREGATE_ERROR ? 2 : 1;
      var path2 = FULL_NAME.split(".");
      var ERROR_NAME = path2[path2.length - 1];
      var OriginalError = getBuiltIn2.apply(null, path2);
      if (!OriginalError) return;
      var OriginalErrorPrototype = OriginalError.prototype;
      if (!IS_PURE && hasOwn(OriginalErrorPrototype, "cause")) delete OriginalErrorPrototype.cause;
      if (!FORCED) return OriginalError;
      var BaseError = getBuiltIn2("Error");
      var WrappedError = wrapper(function(a, b) {
        var message = normalizeStringArgument2(IS_AGGREGATE_ERROR ? b : a, void 0);
        var result = IS_AGGREGATE_ERROR ? new OriginalError(a) : new OriginalError();
        if (message !== void 0) createNonEnumerableProperty2(result, "message", message);
        installErrorStack(result, WrappedError, result.stack, 2);
        if (this && isPrototypeOf(OriginalErrorPrototype, this)) inheritIfRequired2(result, this, WrappedError);
        if (arguments.length > OPTIONS_POSITION) installErrorCause2(result, arguments[OPTIONS_POSITION]);
        return result;
      });
      WrappedError.prototype = OriginalErrorPrototype;
      if (ERROR_NAME !== "Error") {
        if (setPrototypeOf2) setPrototypeOf2(WrappedError, BaseError);
        else copyConstructorProperties2(WrappedError, BaseError, { name: true });
      } else if (DESCRIPTORS && STACK_TRACE_LIMIT in OriginalError) {
        proxyAccessor2(WrappedError, OriginalError, STACK_TRACE_LIMIT);
        proxyAccessor2(WrappedError, OriginalError, "prepareStackTrace");
      }
      copyConstructorProperties2(WrappedError, OriginalError);
      if (!IS_PURE) try {
        if (OriginalErrorPrototype.name !== ERROR_NAME) {
          createNonEnumerableProperty2(OriginalErrorPrototype, "name", ERROR_NAME);
        }
        OriginalErrorPrototype.constructor = WrappedError;
      } catch (error) {
      }
      return WrappedError;
    };
    return wrapErrorConstructorWithCause;
  }
  var hasRequiredEs_error_cause;
  function requireEs_error_cause() {
    if (hasRequiredEs_error_cause) return es_error_cause;
    hasRequiredEs_error_cause = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var apply2 = requireFunctionApply();
    var wrapErrorConstructorWithCause2 = requireWrapErrorConstructorWithCause();
    var WEB_ASSEMBLY = "WebAssembly";
    var WebAssembly = globalThis2[WEB_ASSEMBLY];
    var FORCED = new Error("e", { cause: 7 }).cause !== 7;
    var exportGlobalErrorCauseWrapper = function(ERROR_NAME, wrapper) {
      var O = {};
      O[ERROR_NAME] = wrapErrorConstructorWithCause2(ERROR_NAME, wrapper, FORCED);
      $({ global: true, constructor: true, arity: 1, forced: FORCED }, O);
    };
    var exportWebAssemblyErrorCauseWrapper = function(ERROR_NAME, wrapper) {
      if (WebAssembly && WebAssembly[ERROR_NAME]) {
        var O = {};
        O[ERROR_NAME] = wrapErrorConstructorWithCause2(WEB_ASSEMBLY + "." + ERROR_NAME, wrapper, FORCED);
        $({ target: WEB_ASSEMBLY, stat: true, constructor: true, arity: 1, forced: FORCED }, O);
      }
    };
    exportGlobalErrorCauseWrapper("Error", function(init) {
      return function Error2(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("EvalError", function(init) {
      return function EvalError(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("RangeError", function(init) {
      return function RangeError2(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("ReferenceError", function(init) {
      return function ReferenceError2(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("SyntaxError", function(init) {
      return function SyntaxError2(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("TypeError", function(init) {
      return function TypeError2(message) {
        return apply2(init, this, arguments);
      };
    });
    exportGlobalErrorCauseWrapper("URIError", function(init) {
      return function URIError(message) {
        return apply2(init, this, arguments);
      };
    });
    exportWebAssemblyErrorCauseWrapper("CompileError", function(init) {
      return function CompileError(message) {
        return apply2(init, this, arguments);
      };
    });
    exportWebAssemblyErrorCauseWrapper("LinkError", function(init) {
      return function LinkError(message) {
        return apply2(init, this, arguments);
      };
    });
    exportWebAssemblyErrorCauseWrapper("RuntimeError", function(init) {
      return function RuntimeError(message) {
        return apply2(init, this, arguments);
      };
    });
    return es_error_cause;
  }
  requireEs_error_cause();
  var es_error_isError = {};
  var hasRequiredEs_error_isError;
  function requireEs_error_isError() {
    if (hasRequiredEs_error_isError) return es_error_isError;
    hasRequiredEs_error_isError = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var isObject2 = requireIsObject();
    var classof2 = requireClassof();
    var fails2 = requireFails();
    var ERROR = "Error";
    var DOM_EXCEPTION = "DOMException";
    var PROTOTYPE_SETTING_AVAILABLE = Object.setPrototypeOf || {}.__proto__;
    var DOMException2 = getBuiltIn2(DOM_EXCEPTION);
    var $Error = Error;
    var $isError = $Error.isError;
    var FORCED = !$isError || !PROTOTYPE_SETTING_AVAILABLE || fails2(function() {
      return DOMException2 && !$isError(new DOMException2(DOM_EXCEPTION)) || // structuredClone-based implementations
      // eslint-disable-next-line es/no-error-cause -- detection
      !$isError(new $Error(ERROR, { cause: function() {
      } })) || // instanceof-based and FF Error#stack-based implementations
      $isError(getBuiltIn2("Object", "create")($Error.prototype));
    });
    $({ target: "Error", stat: true, sham: true, forced: FORCED }, {
      isError: function isError(arg) {
        if (!isObject2(arg)) return false;
        var tag = classof2(arg);
        return tag === ERROR || tag === DOM_EXCEPTION;
      }
    });
    return es_error_isError;
  }
  requireEs_error_isError();
  var es_aggregateError = {};
  var es_aggregateError_constructor = {};
  var correctPrototypeGetter;
  var hasRequiredCorrectPrototypeGetter;
  function requireCorrectPrototypeGetter() {
    if (hasRequiredCorrectPrototypeGetter) return correctPrototypeGetter;
    hasRequiredCorrectPrototypeGetter = 1;
    var fails2 = requireFails();
    correctPrototypeGetter = !fails2(function() {
      function F() {
      }
      F.prototype.constructor = null;
      return Object.getPrototypeOf(new F()) !== F.prototype;
    });
    return correctPrototypeGetter;
  }
  var objectGetPrototypeOf;
  var hasRequiredObjectGetPrototypeOf;
  function requireObjectGetPrototypeOf() {
    if (hasRequiredObjectGetPrototypeOf) return objectGetPrototypeOf;
    hasRequiredObjectGetPrototypeOf = 1;
    var hasOwn = requireHasOwnProperty();
    var isCallable2 = requireIsCallable();
    var toObject2 = requireToObject();
    var sharedKey2 = requireSharedKey();
    var CORRECT_PROTOTYPE_GETTER = requireCorrectPrototypeGetter();
    var IE_PROTO = sharedKey2("IE_PROTO");
    var $Object = Object;
    var ObjectPrototype = $Object.prototype;
    objectGetPrototypeOf = CORRECT_PROTOTYPE_GETTER ? $Object.getPrototypeOf : function(O) {
      var object = toObject2(O);
      if (hasOwn(object, IE_PROTO)) return object[IE_PROTO];
      var constructor = object.constructor;
      if (isCallable2(constructor) && object instanceof constructor) {
        return constructor.prototype;
      }
      return object instanceof $Object ? ObjectPrototype : null;
    };
    return objectGetPrototypeOf;
  }
  var objectDefineProperties = {};
  var objectKeys;
  var hasRequiredObjectKeys;
  function requireObjectKeys() {
    if (hasRequiredObjectKeys) return objectKeys;
    hasRequiredObjectKeys = 1;
    var internalObjectKeys = requireObjectKeysInternal();
    var enumBugKeys2 = requireEnumBugKeys();
    objectKeys = Object.keys || function keys(O) {
      return internalObjectKeys(O, enumBugKeys2);
    };
    return objectKeys;
  }
  var hasRequiredObjectDefineProperties;
  function requireObjectDefineProperties() {
    if (hasRequiredObjectDefineProperties) return objectDefineProperties;
    hasRequiredObjectDefineProperties = 1;
    var DESCRIPTORS = requireDescriptors();
    var V8_PROTOTYPE_DEFINE_BUG = requireV8PrototypeDefineBug();
    var definePropertyModule = requireObjectDefineProperty();
    var anObject2 = requireAnObject();
    var toIndexedObject2 = requireToIndexedObject();
    var objectKeys2 = requireObjectKeys();
    objectDefineProperties.f = DESCRIPTORS && !V8_PROTOTYPE_DEFINE_BUG ? Object.defineProperties : function defineProperties(O, Properties) {
      anObject2(O);
      var props = toIndexedObject2(Properties);
      var keys = objectKeys2(Properties);
      var length = keys.length;
      var index = 0;
      var key;
      while (length > index) definePropertyModule.f(O, key = keys[index++], props[key]);
      return O;
    };
    return objectDefineProperties;
  }
  var html$2;
  var hasRequiredHtml;
  function requireHtml() {
    if (hasRequiredHtml) return html$2;
    hasRequiredHtml = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    html$2 = getBuiltIn2("document", "documentElement");
    return html$2;
  }
  var objectCreate;
  var hasRequiredObjectCreate;
  function requireObjectCreate() {
    if (hasRequiredObjectCreate) return objectCreate;
    hasRequiredObjectCreate = 1;
    var anObject2 = requireAnObject();
    var definePropertiesModule = requireObjectDefineProperties();
    var enumBugKeys2 = requireEnumBugKeys();
    var hiddenKeys2 = requireHiddenKeys();
    var html2 = requireHtml();
    var documentCreateElement2 = requireDocumentCreateElement();
    var sharedKey2 = requireSharedKey();
    var GT = ">";
    var LT = "<";
    var PROTOTYPE = "prototype";
    var SCRIPT = "script";
    var IE_PROTO = sharedKey2("IE_PROTO");
    var EmptyConstructor = function() {
    };
    var scriptTag = function(content) {
      return LT + SCRIPT + GT + content + LT + "/" + SCRIPT + GT;
    };
    var NullProtoObjectViaActiveX = function(activeXDocument2) {
      activeXDocument2.write(scriptTag(""));
      activeXDocument2.close();
      var temp = activeXDocument2.parentWindow.Object;
      activeXDocument2 = null;
      return temp;
    };
    var NullProtoObjectViaIFrame = function() {
      var iframe = documentCreateElement2("iframe");
      var JS = "java" + SCRIPT + ":";
      var iframeDocument;
      iframe.style.display = "none";
      html2.appendChild(iframe);
      iframe.src = String(JS);
      iframeDocument = iframe.contentWindow.document;
      iframeDocument.open();
      iframeDocument.write(scriptTag("document.F=Object"));
      iframeDocument.close();
      return iframeDocument.F;
    };
    var activeXDocument;
    var NullProtoObject = function() {
      try {
        activeXDocument = new ActiveXObject("htmlfile");
      } catch (error) {
      }
      NullProtoObject = typeof document != "undefined" ? document.domain && activeXDocument ? NullProtoObjectViaActiveX(activeXDocument) : NullProtoObjectViaIFrame() : NullProtoObjectViaActiveX(activeXDocument);
      var length = enumBugKeys2.length;
      while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys2[length]];
      return NullProtoObject();
    };
    hiddenKeys2[IE_PROTO] = true;
    objectCreate = Object.create || function create2(O, Properties) {
      var result;
      if (O !== null) {
        EmptyConstructor[PROTOTYPE] = anObject2(O);
        result = new EmptyConstructor();
        EmptyConstructor[PROTOTYPE] = null;
        result[IE_PROTO] = O;
      } else result = NullProtoObject();
      return Properties === void 0 ? result : definePropertiesModule.f(result, Properties);
    };
    return objectCreate;
  }
  var functionUncurryThisClause;
  var hasRequiredFunctionUncurryThisClause;
  function requireFunctionUncurryThisClause() {
    if (hasRequiredFunctionUncurryThisClause) return functionUncurryThisClause;
    hasRequiredFunctionUncurryThisClause = 1;
    var classofRaw2 = requireClassofRaw();
    var uncurryThis = requireFunctionUncurryThis();
    functionUncurryThisClause = function(fn) {
      if (classofRaw2(fn) === "Function") return uncurryThis(fn);
    };
    return functionUncurryThisClause;
  }
  var functionBindContext;
  var hasRequiredFunctionBindContext;
  function requireFunctionBindContext() {
    if (hasRequiredFunctionBindContext) return functionBindContext;
    hasRequiredFunctionBindContext = 1;
    var uncurryThis = requireFunctionUncurryThisClause();
    var aCallable2 = requireACallable();
    var NATIVE_BIND = requireFunctionBindNative();
    var bind = uncurryThis(uncurryThis.bind);
    functionBindContext = function(fn, that) {
      aCallable2(fn);
      return that === void 0 ? fn : NATIVE_BIND ? bind(fn, that) : function() {
        return fn.apply(that, arguments);
      };
    };
    return functionBindContext;
  }
  var iterators;
  var hasRequiredIterators;
  function requireIterators() {
    if (hasRequiredIterators) return iterators;
    hasRequiredIterators = 1;
    iterators = Object.create ? /* @__PURE__ */ Object.create(null) : {};
    return iterators;
  }
  var isArrayIteratorMethod;
  var hasRequiredIsArrayIteratorMethod;
  function requireIsArrayIteratorMethod() {
    if (hasRequiredIsArrayIteratorMethod) return isArrayIteratorMethod;
    hasRequiredIsArrayIteratorMethod = 1;
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var Iterators = requireIterators();
    var ITERATOR = wellKnownSymbol2("iterator");
    var ArrayPrototype = Array.prototype;
    isArrayIteratorMethod = function(it) {
      return it !== void 0 && (Iterators.Array === it || ArrayPrototype[ITERATOR] === it);
    };
    return isArrayIteratorMethod;
  }
  var getIteratorMethodInternal;
  var hasRequiredGetIteratorMethodInternal;
  function requireGetIteratorMethodInternal() {
    if (hasRequiredGetIteratorMethodInternal) return getIteratorMethodInternal;
    hasRequiredGetIteratorMethodInternal = 1;
    var classof2 = requireClassofRaw();
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    var getMethod2 = requireGetMethod();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ITERATOR = wellKnownSymbol2("iterator");
    var ArrayPrototype = Array.prototype;
    getIteratorMethodInternal = function(it) {
      if (!isNullOrUndefined2(it)) return getMethod2(it, ITERATOR) || getMethod2(it, "@@iterator") || (classof2(it) === "Arguments" ? ArrayPrototype[ITERATOR] : void 0);
    };
    return getIteratorMethodInternal;
  }
  var getIteratorInternal;
  var hasRequiredGetIteratorInternal;
  function requireGetIteratorInternal() {
    if (hasRequiredGetIteratorInternal) return getIteratorInternal;
    hasRequiredGetIteratorInternal = 1;
    var call = requireFunctionCall();
    var isCallable2 = requireIsCallable();
    var anObject2 = requireAnObject();
    var tryToString2 = requireTryToString();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var $TypeError = TypeError;
    getIteratorInternal = function(argument, usingIterator) {
      var iteratorMethod = arguments.length < 2 ? getIteratorMethod(argument) : usingIterator;
      if (isCallable2(iteratorMethod)) return anObject2(call(iteratorMethod, argument));
      throw new $TypeError(tryToString2(argument) + " is not iterable");
    };
    return getIteratorInternal;
  }
  var iteratorClose;
  var hasRequiredIteratorClose;
  function requireIteratorClose() {
    if (hasRequiredIteratorClose) return iteratorClose;
    hasRequiredIteratorClose = 1;
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var getMethod2 = requireGetMethod();
    iteratorClose = function(iterator, kind, value) {
      var innerResult, innerError;
      anObject2(iterator);
      try {
        innerResult = getMethod2(iterator, "return");
        if (!innerResult) {
          if (kind === "throw") throw value;
          return value;
        }
        innerResult = call(innerResult, iterator);
      } catch (error) {
        innerError = true;
        innerResult = error;
      }
      if (kind === "throw") throw value;
      if (innerError) throw innerResult;
      anObject2(innerResult);
      return value;
    };
    return iteratorClose;
  }
  var iterate;
  var hasRequiredIterate;
  function requireIterate() {
    if (hasRequiredIterate) return iterate;
    hasRequiredIterate = 1;
    var bind = requireFunctionBindContext();
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var tryToString2 = requireTryToString();
    var isArrayIteratorMethod2 = requireIsArrayIteratorMethod();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var getIterator = requireGetIteratorInternal();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var iteratorClose2 = requireIteratorClose();
    var $TypeError = TypeError;
    var Result = function(stopped, result) {
      this.stopped = stopped;
      this.result = result;
    };
    var ResultPrototype = Result.prototype;
    iterate = function(iterable, unboundFunction, options) {
      var that = options && options.that;
      var AS_ENTRIES = !!(options && options.AS_ENTRIES);
      var IS_RECORD = !!(options && options.IS_RECORD);
      var IS_ITERATOR = !!(options && options.IS_ITERATOR);
      var INTERRUPTED = !!(options && options.INTERRUPTED);
      var fn = bind(unboundFunction, that);
      var iterator, iterFn, index, length, result, next, step;
      var stop = function(condition) {
        var $iterator = iterator;
        iterator = void 0;
        if ($iterator) iteratorClose2($iterator, "normal");
        return new Result(true, condition);
      };
      var callFn = function(value2) {
        if (AS_ENTRIES) {
          anObject2(value2);
          return INTERRUPTED ? fn(value2[0], value2[1], stop) : fn(value2[0], value2[1]);
        }
        return INTERRUPTED ? fn(value2, stop) : fn(value2);
      };
      if (IS_RECORD) {
        iterator = iterable.iterator;
      } else if (IS_ITERATOR) {
        iterator = iterable;
      } else {
        iterFn = getIteratorMethod(iterable);
        if (!iterFn) throw new $TypeError(tryToString2(iterable) + " is not iterable");
        if (isArrayIteratorMethod2(iterFn)) {
          for (index = 0, length = lengthOfArrayLike2(iterable); length > index; index++) {
            result = callFn(iterable[index]);
            if (result && isPrototypeOf(ResultPrototype, result)) return result;
          }
          return new Result(false);
        }
        iterator = getIterator(iterable, iterFn);
      }
      next = IS_RECORD ? iterable.next : iterator.next;
      while (!(step = call(next, iterator)).done) {
        var value = step.value;
        try {
          result = callFn(value);
        } catch (error) {
          if (iterator) iteratorClose2(iterator, "throw", error);
          else throw error;
        }
        if (typeof result == "object" && result && isPrototypeOf(ResultPrototype, result)) return result;
      }
      return new Result(false);
    };
    return iterate;
  }
  var hasRequiredEs_aggregateError_constructor;
  function requireEs_aggregateError_constructor() {
    if (hasRequiredEs_aggregateError_constructor) return es_aggregateError_constructor;
    hasRequiredEs_aggregateError_constructor = 1;
    var $ = require_export();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var create2 = requireObjectCreate();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var installErrorCause2 = requireInstallErrorCause();
    var installErrorStack = requireErrorStackInstall();
    var iterate2 = requireIterate();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var $Error = Error;
    var push = [].push;
    var $AggregateError = function AggregateError(errors, message) {
      var isInstance = isPrototypeOf(AggregateErrorPrototype, this);
      var that;
      if (setPrototypeOf2) {
        that = setPrototypeOf2(new $Error(), isInstance ? getPrototypeOf2(this) : AggregateErrorPrototype);
      } else {
        that = isInstance ? this : create2(AggregateErrorPrototype);
        createNonEnumerableProperty2(that, TO_STRING_TAG, "Error");
      }
      if (message !== void 0) createNonEnumerableProperty2(that, "message", normalizeStringArgument2(message));
      installErrorStack(that, $AggregateError, that.stack, 1);
      if (arguments.length > 2) installErrorCause2(that, arguments[2]);
      var errorsArray = [];
      iterate2(errors, push, { that: errorsArray });
      createNonEnumerableProperty2(that, "errors", errorsArray);
      return that;
    };
    if (setPrototypeOf2) setPrototypeOf2($AggregateError, $Error);
    else copyConstructorProperties2($AggregateError, $Error, { name: true });
    var AggregateErrorPrototype = $AggregateError.prototype = create2($Error.prototype, {
      constructor: createPropertyDescriptor2(1, $AggregateError),
      message: createPropertyDescriptor2(1, ""),
      name: createPropertyDescriptor2(1, "AggregateError")
    });
    $({ global: true, constructor: true, arity: 2 }, {
      AggregateError: $AggregateError
    });
    return es_aggregateError_constructor;
  }
  var hasRequiredEs_aggregateError;
  function requireEs_aggregateError() {
    if (hasRequiredEs_aggregateError) return es_aggregateError;
    hasRequiredEs_aggregateError = 1;
    requireEs_aggregateError_constructor();
    return es_aggregateError;
  }
  requireEs_aggregateError();
  var es_aggregateError_cause = {};
  var hasRequiredEs_aggregateError_cause;
  function requireEs_aggregateError_cause() {
    if (hasRequiredEs_aggregateError_cause) return es_aggregateError_cause;
    hasRequiredEs_aggregateError_cause = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var apply2 = requireFunctionApply();
    var fails2 = requireFails();
    var wrapErrorConstructorWithCause2 = requireWrapErrorConstructorWithCause();
    var AGGREGATE_ERROR = "AggregateError";
    var $AggregateError = getBuiltIn2(AGGREGATE_ERROR);
    var FORCED = !fails2(function() {
      return $AggregateError([1]).errors[0] !== 1;
    }) && fails2(function() {
      return $AggregateError([1], AGGREGATE_ERROR, { cause: 7 }).cause !== 7;
    });
    $({ global: true, constructor: true, arity: 2, forced: FORCED }, {
      AggregateError: wrapErrorConstructorWithCause2(AGGREGATE_ERROR, function(init) {
        return function AggregateError(errors, message) {
          return apply2(init, this, arguments);
        };
      }, FORCED, true)
    });
    return es_aggregateError_cause;
  }
  requireEs_aggregateError_cause();
  var es_suppressedError_constructor = {};
  var hasRequiredEs_suppressedError_constructor;
  function requireEs_suppressedError_constructor() {
    if (hasRequiredEs_suppressedError_constructor) return es_suppressedError_constructor;
    hasRequiredEs_suppressedError_constructor = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var create2 = requireObjectCreate();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var installErrorStack = requireErrorStackInstall();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var fails2 = requireFails();
    var IS_PURE = requireIsPure();
    var NativeSuppressedError = globalThis2.SuppressedError;
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var $Error = Error;
    var WRONG_ARITY = !!NativeSuppressedError && NativeSuppressedError.length !== 3;
    var EXTRA_ARGS_SUPPORT = !!NativeSuppressedError && fails2(function() {
      return new NativeSuppressedError(1, 2, 3, { cause: 4 }).cause === 4;
    });
    var PATCH = WRONG_ARITY || EXTRA_ARGS_SUPPORT;
    var $SuppressedError = function SuppressedError2(error, suppressed, message) {
      var isInstance = isPrototypeOf(SuppressedErrorPrototype, this);
      var that;
      if (setPrototypeOf2) {
        that = PATCH && (!isInstance || getPrototypeOf2(this) === SuppressedErrorPrototype) ? new NativeSuppressedError() : setPrototypeOf2(new $Error(), isInstance ? getPrototypeOf2(this) : SuppressedErrorPrototype);
      } else {
        that = isInstance ? this : create2(SuppressedErrorPrototype);
        createNonEnumerableProperty2(that, TO_STRING_TAG, "Error");
      }
      if (message !== void 0) createNonEnumerableProperty2(that, "message", normalizeStringArgument2(message));
      installErrorStack(that, $SuppressedError, that.stack, 1);
      createNonEnumerableProperty2(that, "error", error);
      createNonEnumerableProperty2(that, "suppressed", suppressed);
      return that;
    };
    if (setPrototypeOf2) setPrototypeOf2($SuppressedError, $Error);
    else copyConstructorProperties2($SuppressedError, $Error, { name: true });
    var SuppressedErrorPrototype = $SuppressedError.prototype = PATCH ? NativeSuppressedError.prototype : create2($Error.prototype, {
      constructor: createPropertyDescriptor2(1, $SuppressedError),
      message: createPropertyDescriptor2(1, ""),
      name: createPropertyDescriptor2(1, "SuppressedError")
    });
    if (PATCH && !IS_PURE) SuppressedErrorPrototype.constructor = $SuppressedError;
    $({ global: true, constructor: true, arity: 3, forced: PATCH }, {
      SuppressedError: $SuppressedError
    });
    return es_suppressedError_constructor;
  }
  requireEs_suppressedError_constructor();
  var es_array_at = {};
  var addToUnscopables;
  var hasRequiredAddToUnscopables;
  function requireAddToUnscopables() {
    if (hasRequiredAddToUnscopables) return addToUnscopables;
    hasRequiredAddToUnscopables = 1;
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var create2 = requireObjectCreate();
    var defineProperty = requireObjectDefineProperty().f;
    var UNSCOPABLES = wellKnownSymbol2("unscopables");
    var ArrayPrototype = Array.prototype;
    if (ArrayPrototype[UNSCOPABLES] === void 0) {
      defineProperty(ArrayPrototype, UNSCOPABLES, {
        configurable: true,
        value: create2(null)
      });
    }
    addToUnscopables = function(key) {
      ArrayPrototype[UNSCOPABLES][key] = true;
    };
    return addToUnscopables;
  }
  var hasRequiredEs_array_at;
  function requireEs_array_at() {
    if (hasRequiredEs_array_at) return es_array_at;
    hasRequiredEs_array_at = 1;
    var $ = require_export();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var addToUnscopables2 = requireAddToUnscopables();
    $({ target: "Array", proto: true }, {
      at: function at(index) {
        var O = toObject2(this);
        var len = lengthOfArrayLike2(O);
        var relativeIndex = toIntegerOrInfinity2(index);
        var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
        return k < 0 || k >= len ? void 0 : O[k];
      }
    });
    addToUnscopables2("at");
    return es_array_at;
  }
  requireEs_array_at();
  var es_array_findLast = {};
  var arrayIterationFromLast;
  var hasRequiredArrayIterationFromLast;
  function requireArrayIterationFromLast() {
    if (hasRequiredArrayIterationFromLast) return arrayIterationFromLast;
    hasRequiredArrayIterationFromLast = 1;
    var bind = requireFunctionBindContext();
    var IndexedObject = requireIndexedObject();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var createMethod = function(TYPE) {
      var IS_FIND_LAST_INDEX = TYPE === 1;
      return function($this, callbackfn, that) {
        var O = toObject2($this);
        var self2 = IndexedObject(O);
        var index = lengthOfArrayLike2(self2);
        var boundFunction = bind(callbackfn, that);
        var value, result;
        while (index-- > 0) {
          value = self2[index];
          result = boundFunction(value, index, O);
          if (result) switch (TYPE) {
            case 0:
              return value;
            // findLast
            case 1:
              return index;
          }
        }
        return IS_FIND_LAST_INDEX ? -1 : void 0;
      };
    };
    arrayIterationFromLast = {
      // `Array.prototype.findLast` method
      // https://github.com/tc39/proposal-array-find-from-last
      findLast: createMethod(0),
      // `Array.prototype.findLastIndex` method
      // https://github.com/tc39/proposal-array-find-from-last
      findLastIndex: createMethod(1)
    };
    return arrayIterationFromLast;
  }
  var hasRequiredEs_array_findLast;
  function requireEs_array_findLast() {
    if (hasRequiredEs_array_findLast) return es_array_findLast;
    hasRequiredEs_array_findLast = 1;
    var $ = require_export();
    var $findLast = requireArrayIterationFromLast().findLast;
    var addToUnscopables2 = requireAddToUnscopables();
    $({ target: "Array", proto: true }, {
      findLast: function findLast(callbackfn) {
        return $findLast(this, callbackfn, arguments.length > 1 ? arguments[1] : void 0);
      }
    });
    addToUnscopables2("findLast");
    return es_array_findLast;
  }
  requireEs_array_findLast();
  var es_array_findLastIndex = {};
  var hasRequiredEs_array_findLastIndex;
  function requireEs_array_findLastIndex() {
    if (hasRequiredEs_array_findLastIndex) return es_array_findLastIndex;
    hasRequiredEs_array_findLastIndex = 1;
    var $ = require_export();
    var $findLastIndex = requireArrayIterationFromLast().findLastIndex;
    var addToUnscopables2 = requireAddToUnscopables();
    $({ target: "Array", proto: true }, {
      findLastIndex: function findLastIndex(callbackfn) {
        return $findLastIndex(this, callbackfn, arguments.length > 1 ? arguments[1] : void 0);
      }
    });
    addToUnscopables2("findLastIndex");
    return es_array_findLastIndex;
  }
  requireEs_array_findLastIndex();
  var es_array_flat = {};
  var isArray;
  var hasRequiredIsArray;
  function requireIsArray() {
    if (hasRequiredIsArray) return isArray;
    hasRequiredIsArray = 1;
    var classof2 = requireClassofRaw();
    isArray = Array.isArray || function isArray2(argument) {
      return classof2(argument) === "Array";
    };
    return isArray;
  }
  var doesNotExceedSafeInteger;
  var hasRequiredDoesNotExceedSafeInteger;
  function requireDoesNotExceedSafeInteger() {
    if (hasRequiredDoesNotExceedSafeInteger) return doesNotExceedSafeInteger;
    hasRequiredDoesNotExceedSafeInteger = 1;
    var $TypeError = TypeError;
    var MAX_SAFE_INTEGER = 9007199254740991;
    doesNotExceedSafeInteger = function(it) {
      if (it > MAX_SAFE_INTEGER) throw new $TypeError("Maximum allowed index exceeded");
      return it;
    };
    return doesNotExceedSafeInteger;
  }
  var createProperty;
  var hasRequiredCreateProperty;
  function requireCreateProperty() {
    if (hasRequiredCreateProperty) return createProperty;
    hasRequiredCreateProperty = 1;
    var DESCRIPTORS = requireDescriptors();
    var definePropertyModule = requireObjectDefineProperty();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    createProperty = function(object, key, value) {
      if (DESCRIPTORS) definePropertyModule.f(object, key, createPropertyDescriptor2(0, value));
      else object[key] = value;
    };
    return createProperty;
  }
  var flattenIntoArray_1;
  var hasRequiredFlattenIntoArray;
  function requireFlattenIntoArray() {
    if (hasRequiredFlattenIntoArray) return flattenIntoArray_1;
    hasRequiredFlattenIntoArray = 1;
    var isArray2 = requireIsArray();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var bind = requireFunctionBindContext();
    var createProperty2 = requireCreateProperty();
    var flattenIntoArray = function(target, original, source, sourceLen, start, depth, mapper, thisArg) {
      var targetIndex = start;
      var sourceIndex = 0;
      var mapFn = mapper ? bind(mapper, thisArg) : false;
      var element, elementLen;
      while (sourceIndex < sourceLen) {
        if (sourceIndex in source) {
          element = mapFn ? mapFn(source[sourceIndex], sourceIndex, original) : source[sourceIndex];
          if (depth > 0 && isArray2(element)) {
            elementLen = lengthOfArrayLike2(element);
            targetIndex = flattenIntoArray(target, original, element, elementLen, targetIndex, depth - 1) - 1;
          } else {
            doesNotExceedSafeInteger2(targetIndex + 1);
            createProperty2(target, targetIndex, element);
          }
          targetIndex++;
        }
        sourceIndex++;
      }
      return targetIndex;
    };
    flattenIntoArray_1 = flattenIntoArray;
    return flattenIntoArray_1;
  }
  var isConstructor;
  var hasRequiredIsConstructor;
  function requireIsConstructor() {
    if (hasRequiredIsConstructor) return isConstructor;
    hasRequiredIsConstructor = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var isCallable2 = requireIsCallable();
    var classof2 = requireClassof();
    var getBuiltIn2 = requireGetBuiltIn();
    var inspectSource2 = requireInspectSource();
    var noop = function() {
    };
    var construct2 = getBuiltIn2("Reflect", "construct");
    var constructorRegExp = /^\s*(?:class|function)\b/;
    var exec = uncurryThis(constructorRegExp.exec);
    var INCORRECT_TO_STRING = !constructorRegExp.test(noop);
    var isConstructorModern = function isConstructor2(argument) {
      if (!isCallable2(argument)) return false;
      try {
        construct2(noop, [], argument);
        return true;
      } catch (error) {
        return false;
      }
    };
    var isConstructorLegacy = function isConstructor2(argument) {
      if (!isCallable2(argument)) return false;
      switch (classof2(argument)) {
        case "AsyncFunction":
        case "GeneratorFunction":
        case "AsyncGeneratorFunction":
          return false;
      }
      try {
        return INCORRECT_TO_STRING || !!exec(constructorRegExp, inspectSource2(argument));
      } catch (error) {
        return true;
      }
    };
    isConstructorLegacy.sham = true;
    isConstructor = !construct2 || fails2(function() {
      var called;
      return isConstructorModern(isConstructorModern.call) || !isConstructorModern(Object) || !isConstructorModern(function() {
        called = true;
      }) || called;
    }) ? isConstructorLegacy : isConstructorModern;
    return isConstructor;
  }
  var arraySpeciesConstructor;
  var hasRequiredArraySpeciesConstructor;
  function requireArraySpeciesConstructor() {
    if (hasRequiredArraySpeciesConstructor) return arraySpeciesConstructor;
    hasRequiredArraySpeciesConstructor = 1;
    var isArray2 = requireIsArray();
    var isConstructor2 = requireIsConstructor();
    var isObject2 = requireIsObject();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var SPECIES = wellKnownSymbol2("species");
    var $Array = Array;
    arraySpeciesConstructor = function(originalArray) {
      var C;
      if (isArray2(originalArray)) {
        C = originalArray.constructor;
        if (isConstructor2(C) && (C === $Array || isArray2(C.prototype))) C = void 0;
        else if (isObject2(C)) {
          C = C[SPECIES];
          if (C === null) C = void 0;
        }
      }
      return C === void 0 ? $Array : C;
    };
    return arraySpeciesConstructor;
  }
  var arraySpeciesCreate;
  var hasRequiredArraySpeciesCreate;
  function requireArraySpeciesCreate() {
    if (hasRequiredArraySpeciesCreate) return arraySpeciesCreate;
    hasRequiredArraySpeciesCreate = 1;
    var arraySpeciesConstructor2 = requireArraySpeciesConstructor();
    arraySpeciesCreate = function(originalArray, length) {
      return new (arraySpeciesConstructor2(originalArray))(length === 0 ? 0 : length);
    };
    return arraySpeciesCreate;
  }
  var hasRequiredEs_array_flat;
  function requireEs_array_flat() {
    if (hasRequiredEs_array_flat) return es_array_flat;
    hasRequiredEs_array_flat = 1;
    var $ = require_export();
    var flattenIntoArray = requireFlattenIntoArray();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var arraySpeciesCreate2 = requireArraySpeciesCreate();
    $({ target: "Array", proto: true }, {
      flat: function flat() {
        var depthArg = arguments.length ? arguments[0] : void 0;
        var O = toObject2(this);
        var sourceLen = lengthOfArrayLike2(O);
        var depthNum = depthArg === void 0 ? 1 : toIntegerOrInfinity2(depthArg);
        var A = arraySpeciesCreate2(O, 0);
        flattenIntoArray(A, O, O, sourceLen, 0, depthNum);
        return A;
      }
    });
    return es_array_flat;
  }
  requireEs_array_flat();
  var es_array_flatMap = {};
  var hasRequiredEs_array_flatMap;
  function requireEs_array_flatMap() {
    if (hasRequiredEs_array_flatMap) return es_array_flatMap;
    hasRequiredEs_array_flatMap = 1;
    var $ = require_export();
    var flattenIntoArray = requireFlattenIntoArray();
    var aCallable2 = requireACallable();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var arraySpeciesCreate2 = requireArraySpeciesCreate();
    $({ target: "Array", proto: true }, {
      flatMap: function flatMap(callbackfn) {
        var O = toObject2(this);
        var sourceLen = lengthOfArrayLike2(O);
        var A;
        aCallable2(callbackfn);
        A = arraySpeciesCreate2(O, 0);
        flattenIntoArray(A, O, O, sourceLen, 0, 1, callbackfn, arguments.length > 1 ? arguments[1] : void 0);
        return A;
      }
    });
    return es_array_flatMap;
  }
  requireEs_array_flatMap();
  var es_array_includes = {};
  var hasRequiredEs_array_includes;
  function requireEs_array_includes() {
    if (hasRequiredEs_array_includes) return es_array_includes;
    hasRequiredEs_array_includes = 1;
    var $ = require_export();
    var $includes = requireArrayIncludes().includes;
    var fails2 = requireFails();
    var addToUnscopables2 = requireAddToUnscopables();
    var BROKEN_ON_SPARSE = fails2(function() {
      return !Array(1).includes();
    });
    var BROKEN_ON_SPARSE_WITH_FROM_INDEX = fails2(function() {
      return [, 1].includes(void 0, 1);
    });
    $({ target: "Array", proto: true, forced: BROKEN_ON_SPARSE || BROKEN_ON_SPARSE_WITH_FROM_INDEX }, {
      includes: function includes(el) {
        return $includes(this, el, arguments.length > 1 ? arguments[1] : void 0);
      }
    });
    addToUnscopables2("includes");
    return es_array_includes;
  }
  requireEs_array_includes();
  var es_array_push = {};
  var arraySetLength;
  var hasRequiredArraySetLength;
  function requireArraySetLength() {
    if (hasRequiredArraySetLength) return arraySetLength;
    hasRequiredArraySetLength = 1;
    var DESCRIPTORS = requireDescriptors();
    var isArray2 = requireIsArray();
    var $TypeError = TypeError;
    var getOwnPropertyDescriptor2 = Object.getOwnPropertyDescriptor;
    var SILENT_ON_NON_WRITABLE_LENGTH_SET = DESCRIPTORS && !(function() {
      if (this !== void 0) return true;
      try {
        Object.defineProperty([], "length", { writable: false }).length = 1;
      } catch (error) {
        return error instanceof TypeError;
      }
    })();
    arraySetLength = SILENT_ON_NON_WRITABLE_LENGTH_SET ? function(O, length) {
      if (isArray2(O) && !getOwnPropertyDescriptor2(O, "length").writable) {
        throw new $TypeError("Cannot set read only .length");
      }
      return O.length = length;
    } : function(O, length) {
      return O.length = length;
    };
    return arraySetLength;
  }
  var hasRequiredEs_array_push;
  function requireEs_array_push() {
    if (hasRequiredEs_array_push) return es_array_push;
    hasRequiredEs_array_push = 1;
    var $ = require_export();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var setArrayLength = requireArraySetLength();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var fails2 = requireFails();
    var INCORRECT_TO_LENGTH = fails2(function() {
      return [].push.call({ length: 4294967296 }, 1) !== 4294967297;
    });
    var properErrorOnNonWritableLength = function() {
      try {
        Object.defineProperty([], "length", { writable: false }).push();
      } catch (error) {
        return error instanceof TypeError;
      }
    };
    var FORCED = INCORRECT_TO_LENGTH || !properErrorOnNonWritableLength();
    $({ target: "Array", proto: true, arity: 1, forced: FORCED }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      push: function push(item) {
        var O = toObject2(this);
        var len = lengthOfArrayLike2(O);
        var argCount = arguments.length;
        doesNotExceedSafeInteger2(len + argCount);
        for (var i = 0; i < argCount; i++) {
          O[len] = arguments[i];
          len++;
        }
        setArrayLength(O, len);
        return len;
      }
    });
    return es_array_push;
  }
  requireEs_array_push();
  var es_array_reduce = {};
  var arrayReduce;
  var hasRequiredArrayReduce;
  function requireArrayReduce() {
    if (hasRequiredArrayReduce) return arrayReduce;
    hasRequiredArrayReduce = 1;
    var aCallable2 = requireACallable();
    var toObject2 = requireToObject();
    var IndexedObject = requireIndexedObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var $TypeError = TypeError;
    var REDUCE_EMPTY = "Reduce of empty array with no initial value";
    var createMethod = function(IS_RIGHT) {
      return function(that, callbackfn, argumentsLength, memo) {
        var O = toObject2(that);
        var self2 = IndexedObject(O);
        var length = lengthOfArrayLike2(O);
        aCallable2(callbackfn);
        if (length === 0 && argumentsLength < 2) throw new $TypeError(REDUCE_EMPTY);
        var index = IS_RIGHT ? length - 1 : 0;
        var i = IS_RIGHT ? -1 : 1;
        if (argumentsLength < 2) while (true) {
          if (index in self2) {
            memo = self2[index];
            index += i;
            break;
          }
          index += i;
          if (IS_RIGHT ? index < 0 : length <= index) {
            throw new $TypeError(REDUCE_EMPTY);
          }
        }
        for (; IS_RIGHT ? index >= 0 : length > index; index += i) if (index in self2) {
          memo = callbackfn(memo, self2[index], index, O);
        }
        return memo;
      };
    };
    arrayReduce = {
      // `Array.prototype.reduce` method
      // https://tc39.es/ecma262/#sec-array.prototype.reduce
      left: createMethod(false),
      // `Array.prototype.reduceRight` method
      // https://tc39.es/ecma262/#sec-array.prototype.reduceright
      right: createMethod(true)
    };
    return arrayReduce;
  }
  var arrayMethodIsStrict;
  var hasRequiredArrayMethodIsStrict;
  function requireArrayMethodIsStrict() {
    if (hasRequiredArrayMethodIsStrict) return arrayMethodIsStrict;
    hasRequiredArrayMethodIsStrict = 1;
    var fails2 = requireFails();
    arrayMethodIsStrict = function(METHOD_NAME, argument) {
      var method = [][METHOD_NAME];
      return !!method && fails2(function() {
        method.call(null, argument || function() {
          return 1;
        }, 1);
      });
    };
    return arrayMethodIsStrict;
  }
  var environment;
  var hasRequiredEnvironment;
  function requireEnvironment() {
    if (hasRequiredEnvironment) return environment;
    hasRequiredEnvironment = 1;
    var globalThis2 = requireGlobalThis();
    var userAgent = requireEnvironmentUserAgent();
    var classof2 = requireClassofRaw();
    var userAgentStartsWith = function(string) {
      return userAgent.slice(0, string.length) === string;
    };
    environment = (function() {
      if (userAgentStartsWith("Bun/")) return "BUN";
      if (userAgentStartsWith("Cloudflare-Workers")) return "CLOUDFLARE";
      if (userAgentStartsWith("Deno/")) return "DENO";
      if (userAgentStartsWith("Node.js/")) return "NODE";
      if (globalThis2.Bun && typeof Bun.version == "string") return "BUN";
      if (globalThis2.Deno && typeof Deno.version == "object") return "DENO";
      if (classof2(globalThis2.process) === "process") return "NODE";
      if (globalThis2.window && globalThis2.document) return "BROWSER";
      return "REST";
    })();
    return environment;
  }
  var environmentIsNode;
  var hasRequiredEnvironmentIsNode;
  function requireEnvironmentIsNode() {
    if (hasRequiredEnvironmentIsNode) return environmentIsNode;
    hasRequiredEnvironmentIsNode = 1;
    var ENVIRONMENT = requireEnvironment();
    environmentIsNode = ENVIRONMENT === "NODE";
    return environmentIsNode;
  }
  var hasRequiredEs_array_reduce;
  function requireEs_array_reduce() {
    if (hasRequiredEs_array_reduce) return es_array_reduce;
    hasRequiredEs_array_reduce = 1;
    var $ = require_export();
    var $reduce = requireArrayReduce().left;
    var arrayMethodIsStrict2 = requireArrayMethodIsStrict();
    var CHROME_VERSION = requireEnvironmentV8Version();
    var IS_NODE = requireEnvironmentIsNode();
    var CHROME_BUG = !IS_NODE && CHROME_VERSION > 79 && CHROME_VERSION < 83;
    var FORCED = CHROME_BUG || !arrayMethodIsStrict2("reduce");
    $({ target: "Array", proto: true, forced: FORCED }, {
      reduce: function reduce(callbackfn) {
        var length = arguments.length;
        return $reduce(this, callbackfn, length, length > 1 ? arguments[1] : void 0);
      }
    });
    return es_array_reduce;
  }
  requireEs_array_reduce();
  var es_array_reduceRight = {};
  var hasRequiredEs_array_reduceRight;
  function requireEs_array_reduceRight() {
    if (hasRequiredEs_array_reduceRight) return es_array_reduceRight;
    hasRequiredEs_array_reduceRight = 1;
    var $ = require_export();
    var $reduceRight = requireArrayReduce().right;
    var arrayMethodIsStrict2 = requireArrayMethodIsStrict();
    var CHROME_VERSION = requireEnvironmentV8Version();
    var IS_NODE = requireEnvironmentIsNode();
    var CHROME_BUG = !IS_NODE && CHROME_VERSION > 79 && CHROME_VERSION < 83;
    var FORCED = CHROME_BUG || !arrayMethodIsStrict2("reduceRight");
    $({ target: "Array", proto: true, forced: FORCED }, {
      reduceRight: function reduceRight(callbackfn) {
        return $reduceRight(this, callbackfn, arguments.length, arguments.length > 1 ? arguments[1] : void 0);
      }
    });
    return es_array_reduceRight;
  }
  requireEs_array_reduceRight();
  var es_array_reverse = {};
  var hasRequiredEs_array_reverse;
  function requireEs_array_reverse() {
    if (hasRequiredEs_array_reverse) return es_array_reverse;
    hasRequiredEs_array_reverse = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var isArray2 = requireIsArray();
    var nativeReverse = uncurryThis([].reverse);
    var test = [1, 2];
    $({ target: "Array", proto: true, forced: String(test) === String(test.reverse()) }, {
      reverse: function reverse() {
        if (isArray2(this)) this.length = this.length;
        return nativeReverse(this);
      }
    });
    return es_array_reverse;
  }
  requireEs_array_reverse();
  var es_array_sort = {};
  var deletePropertyOrThrow;
  var hasRequiredDeletePropertyOrThrow;
  function requireDeletePropertyOrThrow() {
    if (hasRequiredDeletePropertyOrThrow) return deletePropertyOrThrow;
    hasRequiredDeletePropertyOrThrow = 1;
    var tryToString2 = requireTryToString();
    var $TypeError = TypeError;
    deletePropertyOrThrow = function(O, P) {
      if (!delete O[P]) throw new $TypeError("Cannot delete property " + tryToString2(P) + " of " + tryToString2(O));
    };
    return deletePropertyOrThrow;
  }
  var arraySlice;
  var hasRequiredArraySlice;
  function requireArraySlice() {
    if (hasRequiredArraySlice) return arraySlice;
    hasRequiredArraySlice = 1;
    var uncurryThis = requireFunctionUncurryThis();
    arraySlice = uncurryThis([].slice);
    return arraySlice;
  }
  var arraySort;
  var hasRequiredArraySort;
  function requireArraySort() {
    if (hasRequiredArraySort) return arraySort;
    hasRequiredArraySort = 1;
    var arraySlice2 = requireArraySlice();
    var floor = Math.floor;
    var sort = function(array, comparefn) {
      var length = array.length;
      if (length < 8) {
        var i = 1;
        var element, j;
        while (i < length) {
          j = i;
          element = array[i];
          while (j && comparefn(array[j - 1], element) > 0) {
            array[j] = array[--j];
          }
          if (j !== i++) array[j] = element;
        }
      } else {
        var middle = floor(length / 2);
        var left = sort(arraySlice2(array, 0, middle), comparefn);
        var right = sort(arraySlice2(array, middle), comparefn);
        var llength = left.length;
        var rlength = right.length;
        var lindex = 0;
        var rindex = 0;
        while (lindex < llength || rindex < rlength) {
          array[lindex + rindex] = lindex < llength && rindex < rlength ? comparefn(left[lindex], right[rindex]) <= 0 ? left[lindex++] : right[rindex++] : lindex < llength ? left[lindex++] : right[rindex++];
        }
      }
      return array;
    };
    arraySort = sort;
    return arraySort;
  }
  var environmentFfVersion;
  var hasRequiredEnvironmentFfVersion;
  function requireEnvironmentFfVersion() {
    if (hasRequiredEnvironmentFfVersion) return environmentFfVersion;
    hasRequiredEnvironmentFfVersion = 1;
    var userAgent = requireEnvironmentUserAgent();
    var firefox = userAgent.match(/firefox\/(\d+)/i);
    environmentFfVersion = !!firefox && +firefox[1];
    return environmentFfVersion;
  }
  var environmentIsIeOrEdge;
  var hasRequiredEnvironmentIsIeOrEdge;
  function requireEnvironmentIsIeOrEdge() {
    if (hasRequiredEnvironmentIsIeOrEdge) return environmentIsIeOrEdge;
    hasRequiredEnvironmentIsIeOrEdge = 1;
    var UA = requireEnvironmentUserAgent();
    environmentIsIeOrEdge = /MSIE|Trident/.test(UA);
    return environmentIsIeOrEdge;
  }
  var environmentWebkitVersion;
  var hasRequiredEnvironmentWebkitVersion;
  function requireEnvironmentWebkitVersion() {
    if (hasRequiredEnvironmentWebkitVersion) return environmentWebkitVersion;
    hasRequiredEnvironmentWebkitVersion = 1;
    var userAgent = requireEnvironmentUserAgent();
    var webkit = userAgent.match(/AppleWebKit\/(\d+)\./);
    environmentWebkitVersion = !!webkit && +webkit[1];
    return environmentWebkitVersion;
  }
  var hasRequiredEs_array_sort;
  function requireEs_array_sort() {
    if (hasRequiredEs_array_sort) return es_array_sort;
    hasRequiredEs_array_sort = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var deletePropertyOrThrow2 = requireDeletePropertyOrThrow();
    var toString2 = requireToString();
    var fails2 = requireFails();
    var internalSort = requireArraySort();
    var arrayMethodIsStrict2 = requireArrayMethodIsStrict();
    var FF = requireEnvironmentFfVersion();
    var IE_OR_EDGE = requireEnvironmentIsIeOrEdge();
    var V8 = requireEnvironmentV8Version();
    var WEBKIT = requireEnvironmentWebkitVersion();
    var test = [];
    var nativeSort = uncurryThis(test.sort);
    var push = uncurryThis(test.push);
    var FAILS_ON_UNDEFINED = fails2(function() {
      test.sort(void 0);
    });
    var FAILS_ON_NULL = fails2(function() {
      test.sort(null);
    });
    var STRICT_METHOD = arrayMethodIsStrict2("sort");
    var STABLE_SORT = !fails2(function() {
      if (V8) return V8 < 70;
      if (FF && FF > 3) return;
      if (IE_OR_EDGE) return true;
      if (WEBKIT) return WEBKIT < 603;
      var result = "";
      var code, chr, value, index;
      for (code = 65; code < 76; code++) {
        chr = String.fromCharCode(code);
        switch (code) {
          case 66:
          case 69:
          case 70:
          case 72:
            value = 3;
            break;
          case 68:
          case 71:
            value = 4;
            break;
          default:
            value = 2;
        }
        for (index = 0; index < 47; index++) {
          test.push({ k: chr + index, v: value });
        }
      }
      test.sort(function(a, b) {
        return b.v - a.v;
      });
      for (index = 0; index < test.length; index++) {
        chr = test[index].k.charAt(0);
        if (result.charAt(result.length - 1) !== chr) result += chr;
      }
      return result !== "DGBEFHACIJK";
    });
    var FORCED = FAILS_ON_UNDEFINED || !FAILS_ON_NULL || !STRICT_METHOD || !STABLE_SORT;
    var getSortCompare = function(comparefn) {
      return function(x, y) {
        if (y === void 0) return -1;
        if (x === void 0) return 1;
        if (comparefn !== void 0) return +comparefn(x, y) || 0;
        var xString = toString2(x);
        var yString = toString2(y);
        return xString === yString ? 0 : xString > yString ? 1 : -1;
      };
    };
    $({ target: "Array", proto: true, forced: FORCED }, {
      sort: function sort(comparefn) {
        if (comparefn !== void 0) aCallable2(comparefn);
        var array = toObject2(this);
        if (STABLE_SORT) return comparefn === void 0 ? nativeSort(array) : nativeSort(array, comparefn);
        var items = [];
        var arrayLength = lengthOfArrayLike2(array);
        var itemsLength, index;
        for (index = 0; index < arrayLength; index++) {
          if (index in array) push(items, array[index]);
        }
        internalSort(items, getSortCompare(comparefn));
        itemsLength = lengthOfArrayLike2(items);
        index = 0;
        while (index < itemsLength) array[index] = items[index++];
        while (index < arrayLength) deletePropertyOrThrow2(array, index++);
        return array;
      }
    });
    return es_array_sort;
  }
  requireEs_array_sort();
  var es_array_toReversed = {};
  var hasRequiredEs_array_toReversed;
  function requireEs_array_toReversed() {
    if (hasRequiredEs_array_toReversed) return es_array_toReversed;
    hasRequiredEs_array_toReversed = 1;
    var $ = require_export();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIndexedObject2 = requireToIndexedObject();
    var createProperty2 = requireCreateProperty();
    var addToUnscopables2 = requireAddToUnscopables();
    var $Array = Array;
    $({ target: "Array", proto: true }, {
      toReversed: function toReversed() {
        var O = toIndexedObject2(this);
        var len = lengthOfArrayLike2(O);
        var A = new $Array(len);
        var k = 0;
        for (; k < len; k++) createProperty2(A, k, O[len - k - 1]);
        return A;
      }
    });
    addToUnscopables2("toReversed");
    return es_array_toReversed;
  }
  requireEs_array_toReversed();
  var es_array_toSorted = {};
  var arrayFromConstructorAndList;
  var hasRequiredArrayFromConstructorAndList;
  function requireArrayFromConstructorAndList() {
    if (hasRequiredArrayFromConstructorAndList) return arrayFromConstructorAndList;
    hasRequiredArrayFromConstructorAndList = 1;
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    arrayFromConstructorAndList = function(Constructor, list, $length) {
      var index = 0;
      var length = arguments.length > 2 ? $length : lengthOfArrayLike2(list);
      var result = new Constructor(length);
      while (length > index) result[index] = list[index++];
      return result;
    };
    return arrayFromConstructorAndList;
  }
  var getBuiltInPrototypeMethod;
  var hasRequiredGetBuiltInPrototypeMethod;
  function requireGetBuiltInPrototypeMethod() {
    if (hasRequiredGetBuiltInPrototypeMethod) return getBuiltInPrototypeMethod;
    hasRequiredGetBuiltInPrototypeMethod = 1;
    var globalThis2 = requireGlobalThis();
    getBuiltInPrototypeMethod = function(CONSTRUCTOR, METHOD) {
      var Constructor = globalThis2[CONSTRUCTOR];
      var Prototype = Constructor && Constructor.prototype;
      return Prototype && Prototype[METHOD];
    };
    return getBuiltInPrototypeMethod;
  }
  var hasRequiredEs_array_toSorted;
  function requireEs_array_toSorted() {
    if (hasRequiredEs_array_toSorted) return es_array_toSorted;
    hasRequiredEs_array_toSorted = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    var toIndexedObject2 = requireToIndexedObject();
    var arrayFromConstructorAndList2 = requireArrayFromConstructorAndList();
    var getBuiltInPrototypeMethod2 = requireGetBuiltInPrototypeMethod();
    var addToUnscopables2 = requireAddToUnscopables();
    var $Array = Array;
    var sort = uncurryThis(getBuiltInPrototypeMethod2("Array", "sort"));
    $({ target: "Array", proto: true }, {
      toSorted: function toSorted(compareFn) {
        if (compareFn !== void 0) aCallable2(compareFn);
        var O = toIndexedObject2(this);
        var A = arrayFromConstructorAndList2($Array, O);
        return sort(A, compareFn);
      }
    });
    addToUnscopables2("toSorted");
    return es_array_toSorted;
  }
  requireEs_array_toSorted();
  var es_array_toSpliced = {};
  var hasRequiredEs_array_toSpliced;
  function requireEs_array_toSpliced() {
    if (hasRequiredEs_array_toSpliced) return es_array_toSpliced;
    hasRequiredEs_array_toSpliced = 1;
    var $ = require_export();
    var addToUnscopables2 = requireAddToUnscopables();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toAbsoluteIndex2 = requireToAbsoluteIndex();
    var toIndexedObject2 = requireToIndexedObject();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var createProperty2 = requireCreateProperty();
    var $Array = Array;
    var max = Math.max;
    var min = Math.min;
    $({ target: "Array", proto: true }, {
      toSpliced: function toSpliced(start, deleteCount) {
        var O = toIndexedObject2(this);
        var len = lengthOfArrayLike2(O);
        var actualStart = toAbsoluteIndex2(start, len);
        var argumentsLength = arguments.length;
        var k = 0;
        var insertCount, actualDeleteCount, newLen, A;
        if (argumentsLength === 0) {
          insertCount = actualDeleteCount = 0;
        } else if (argumentsLength === 1) {
          insertCount = 0;
          actualDeleteCount = len - actualStart;
        } else {
          insertCount = argumentsLength - 2;
          actualDeleteCount = min(max(toIntegerOrInfinity2(deleteCount), 0), len - actualStart);
        }
        newLen = doesNotExceedSafeInteger2(len + insertCount - actualDeleteCount);
        A = $Array(newLen);
        for (; k < actualStart; k++) createProperty2(A, k, O[k]);
        for (; k < actualStart + insertCount; k++) createProperty2(A, k, arguments[k - actualStart + 2]);
        for (; k < newLen; k++) createProperty2(A, k, O[k + actualDeleteCount - insertCount]);
        return A;
      }
    });
    addToUnscopables2("toSpliced");
    return es_array_toSpliced;
  }
  requireEs_array_toSpliced();
  var es_array_unscopables_flat = {};
  var hasRequiredEs_array_unscopables_flat;
  function requireEs_array_unscopables_flat() {
    if (hasRequiredEs_array_unscopables_flat) return es_array_unscopables_flat;
    hasRequiredEs_array_unscopables_flat = 1;
    var addToUnscopables2 = requireAddToUnscopables();
    addToUnscopables2("flat");
    return es_array_unscopables_flat;
  }
  requireEs_array_unscopables_flat();
  var es_array_unscopables_flatMap = {};
  var hasRequiredEs_array_unscopables_flatMap;
  function requireEs_array_unscopables_flatMap() {
    if (hasRequiredEs_array_unscopables_flatMap) return es_array_unscopables_flatMap;
    hasRequiredEs_array_unscopables_flatMap = 1;
    var addToUnscopables2 = requireAddToUnscopables();
    addToUnscopables2("flatMap");
    return es_array_unscopables_flatMap;
  }
  requireEs_array_unscopables_flatMap();
  var es_array_unshift = {};
  var hasRequiredEs_array_unshift;
  function requireEs_array_unshift() {
    if (hasRequiredEs_array_unshift) return es_array_unshift;
    hasRequiredEs_array_unshift = 1;
    var $ = require_export();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var setArrayLength = requireArraySetLength();
    var deletePropertyOrThrow2 = requireDeletePropertyOrThrow();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var INCORRECT_RESULT = [].unshift(0) !== 1;
    var properErrorOnNonWritableLength = function() {
      try {
        Object.defineProperty([], "length", { writable: false }).unshift();
      } catch (error) {
        return error instanceof TypeError;
      }
    };
    var FORCED = INCORRECT_RESULT || !properErrorOnNonWritableLength();
    $({ target: "Array", proto: true, arity: 1, forced: FORCED }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      unshift: function unshift(item) {
        var O = toObject2(this);
        var len = lengthOfArrayLike2(O);
        var argCount = arguments.length;
        if (argCount) {
          doesNotExceedSafeInteger2(len + argCount);
          var k = len;
          while (k--) {
            var to = k + argCount;
            if (k in O) O[to] = O[k];
            else deletePropertyOrThrow2(O, to);
          }
          for (var j = 0; j < argCount; j++) {
            O[j] = arguments[j];
          }
        }
        return setArrayLength(O, len + argCount);
      }
    });
    return es_array_unshift;
  }
  requireEs_array_unshift();
  var es_array_with = {};
  var hasRequiredEs_array_with;
  function requireEs_array_with() {
    if (hasRequiredEs_array_with) return es_array_with;
    hasRequiredEs_array_with = 1;
    var $ = require_export();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toIndexedObject2 = requireToIndexedObject();
    var createProperty2 = requireCreateProperty();
    var $Array = Array;
    var $RangeError = RangeError;
    var INCORRECT_EXCEPTION_ON_COERCION_FAIL = (function() {
      try {
        []["with"]({ valueOf: function() {
          throw 4;
        } }, null);
      } catch (error) {
        return error !== 4;
      }
    })();
    $({ target: "Array", proto: true, forced: INCORRECT_EXCEPTION_ON_COERCION_FAIL }, {
      "with": function(index, value) {
        var O = toIndexedObject2(this);
        var len = lengthOfArrayLike2(O);
        var relativeIndex = toIntegerOrInfinity2(index);
        var actualIndex = relativeIndex < 0 ? len + relativeIndex : relativeIndex;
        if (actualIndex >= len || actualIndex < 0) throw new $RangeError("Incorrect index");
        var A = new $Array(len);
        var k = 0;
        for (; k < len; k++) createProperty2(A, k, k === actualIndex ? value : O[k]);
        return A;
      }
    });
    return es_array_with;
  }
  requireEs_array_with();
  var es_arrayBuffer_constructor = {};
  var arrayBufferBasicDetection;
  var hasRequiredArrayBufferBasicDetection;
  function requireArrayBufferBasicDetection() {
    if (hasRequiredArrayBufferBasicDetection) return arrayBufferBasicDetection;
    hasRequiredArrayBufferBasicDetection = 1;
    arrayBufferBasicDetection = typeof ArrayBuffer != "undefined" && typeof DataView != "undefined";
    return arrayBufferBasicDetection;
  }
  var defineBuiltIns;
  var hasRequiredDefineBuiltIns;
  function requireDefineBuiltIns() {
    if (hasRequiredDefineBuiltIns) return defineBuiltIns;
    hasRequiredDefineBuiltIns = 1;
    var defineBuiltIn2 = requireDefineBuiltIn();
    defineBuiltIns = function(target, src, options) {
      for (var key in src) defineBuiltIn2(target, key, src[key], options);
      return target;
    };
    return defineBuiltIns;
  }
  var anInstance;
  var hasRequiredAnInstance;
  function requireAnInstance() {
    if (hasRequiredAnInstance) return anInstance;
    hasRequiredAnInstance = 1;
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var $TypeError = TypeError;
    anInstance = function(it, Prototype) {
      if (isPrototypeOf(Prototype, it)) return it;
      throw new $TypeError("Incorrect invocation");
    };
    return anInstance;
  }
  var toIndex;
  var hasRequiredToIndex;
  function requireToIndex() {
    if (hasRequiredToIndex) return toIndex;
    hasRequiredToIndex = 1;
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toLength2 = requireToLength();
    var $RangeError = RangeError;
    toIndex = function(it) {
      if (it === void 0) return 0;
      var number = toIntegerOrInfinity2(it);
      var length = toLength2(number);
      if (number !== length) throw new $RangeError("Wrong length or index");
      return length;
    };
    return toIndex;
  }
  var mathSign;
  var hasRequiredMathSign;
  function requireMathSign() {
    if (hasRequiredMathSign) return mathSign;
    hasRequiredMathSign = 1;
    mathSign = Math.sign || function sign(x) {
      var n = +x;
      return n === 0 || n !== n ? n : n < 0 ? -1 : 1;
    };
    return mathSign;
  }
  var mathRoundTiesToEven;
  var hasRequiredMathRoundTiesToEven;
  function requireMathRoundTiesToEven() {
    if (hasRequiredMathRoundTiesToEven) return mathRoundTiesToEven;
    hasRequiredMathRoundTiesToEven = 1;
    var EPSILON = 2220446049250313e-31;
    var INVERSE_EPSILON = 1 / EPSILON;
    mathRoundTiesToEven = function(n) {
      return n + INVERSE_EPSILON - INVERSE_EPSILON;
    };
    return mathRoundTiesToEven;
  }
  var mathFloatRound;
  var hasRequiredMathFloatRound;
  function requireMathFloatRound() {
    if (hasRequiredMathFloatRound) return mathFloatRound;
    hasRequiredMathFloatRound = 1;
    var sign = requireMathSign();
    var roundTiesToEven = requireMathRoundTiesToEven();
    var abs = Math.abs;
    var EPSILON = 2220446049250313e-31;
    mathFloatRound = function(x, FLOAT_EPSILON, FLOAT_MAX_VALUE, FLOAT_MIN_VALUE) {
      var n = +x;
      var absolute = abs(n);
      var s = sign(n);
      if (absolute < FLOAT_MIN_VALUE) return s * roundTiesToEven(absolute / FLOAT_MIN_VALUE / FLOAT_EPSILON) * FLOAT_MIN_VALUE * FLOAT_EPSILON;
      var a = (1 + FLOAT_EPSILON / EPSILON) * absolute;
      var result = a - (a - absolute);
      if (result > FLOAT_MAX_VALUE || result !== result) return s * Infinity;
      return s * result;
    };
    return mathFloatRound;
  }
  var mathFround;
  var hasRequiredMathFround;
  function requireMathFround() {
    if (hasRequiredMathFround) return mathFround;
    hasRequiredMathFround = 1;
    var floatRound = requireMathFloatRound();
    var FLOAT32_EPSILON = 11920928955078125e-23;
    var FLOAT32_MAX_VALUE = 34028234663852886e22;
    var FLOAT32_MIN_VALUE = 11754943508222875e-54;
    mathFround = Math.fround || function fround(x) {
      return floatRound(x, FLOAT32_EPSILON, FLOAT32_MAX_VALUE, FLOAT32_MIN_VALUE);
    };
    return mathFround;
  }
  var ieee754;
  var hasRequiredIeee754;
  function requireIeee754() {
    if (hasRequiredIeee754) return ieee754;
    hasRequiredIeee754 = 1;
    var $Array = Array;
    var abs = Math.abs;
    var pow = Math.pow;
    var floor = Math.floor;
    var log = Math.log;
    var LN2 = Math.LN2;
    var pack = function(number, mantissaLength, bytes) {
      var buffer = $Array(bytes);
      var exponentLength = bytes * 8 - mantissaLength - 1;
      var eMax = (1 << exponentLength) - 1;
      var eBias = eMax >> 1;
      var rt = mantissaLength === 23 ? pow(2, -24) - pow(2, -77) : 0;
      var sign = number < 0 || number === 0 && 1 / number < 0 ? 1 : 0;
      var index = 0;
      var exponent, mantissa, c;
      number = abs(number);
      if (number !== number || number === Infinity) {
        mantissa = number !== number ? 1 : 0;
        exponent = eMax;
      } else {
        exponent = floor(log(number) / LN2);
        c = pow(2, -exponent);
        if (number * c < 1) {
          exponent--;
          c *= 2;
        }
        if (exponent + eBias >= 1) {
          number += rt / c;
        } else {
          number += rt * pow(2, 1 - eBias);
        }
        if (number * c >= 2) {
          exponent++;
          c /= 2;
        }
        if (exponent + eBias >= eMax) {
          mantissa = 0;
          exponent = eMax;
        } else if (exponent + eBias >= 1) {
          mantissa = (number * c - 1) * pow(2, mantissaLength);
          exponent += eBias;
        } else {
          mantissa = number * pow(2, eBias - 1) * pow(2, mantissaLength);
          exponent = 0;
        }
      }
      while (mantissaLength >= 8) {
        buffer[index++] = mantissa & 255;
        mantissa /= 256;
        mantissaLength -= 8;
      }
      exponent = exponent << mantissaLength | mantissa;
      exponentLength += mantissaLength;
      while (exponentLength > 0) {
        buffer[index++] = exponent & 255;
        exponent /= 256;
        exponentLength -= 8;
      }
      buffer[index - 1] |= sign * 128;
      return buffer;
    };
    var unpack = function(buffer, mantissaLength) {
      var bytes = buffer.length;
      var exponentLength = bytes * 8 - mantissaLength - 1;
      var eMax = (1 << exponentLength) - 1;
      var eBias = eMax >> 1;
      var nBits = exponentLength - 7;
      var index = bytes - 1;
      var sign = buffer[index--];
      var exponent = sign & 127;
      var mantissa;
      sign >>= 7;
      while (nBits > 0) {
        exponent = exponent * 256 + buffer[index--];
        nBits -= 8;
      }
      mantissa = exponent & (1 << -nBits) - 1;
      exponent >>= -nBits;
      nBits += mantissaLength;
      while (nBits > 0) {
        mantissa = mantissa * 256 + buffer[index--];
        nBits -= 8;
      }
      if (exponent === 0) {
        exponent = 1 - eBias;
      } else if (exponent === eMax) {
        return mantissa ? NaN : sign ? -Infinity : Infinity;
      } else {
        mantissa += pow(2, mantissaLength);
        exponent -= eBias;
      }
      return (sign ? -1 : 1) * mantissa * pow(2, exponent - mantissaLength);
    };
    ieee754 = {
      pack,
      unpack
    };
    return ieee754;
  }
  var arrayFill;
  var hasRequiredArrayFill;
  function requireArrayFill() {
    if (hasRequiredArrayFill) return arrayFill;
    hasRequiredArrayFill = 1;
    var toObject2 = requireToObject();
    var toAbsoluteIndex2 = requireToAbsoluteIndex();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    arrayFill = [].fill || function fill(value) {
      var O = toObject2(this);
      var length = lengthOfArrayLike2(O);
      var argumentsLength = arguments.length;
      var index = toAbsoluteIndex2(argumentsLength > 1 ? arguments[1] : void 0, length);
      var end = argumentsLength > 2 ? arguments[2] : void 0;
      var endPos = end === void 0 ? length : toAbsoluteIndex2(end, length);
      while (endPos > index) O[index++] = value;
      return O;
    };
    return arrayFill;
  }
  var setToStringTag;
  var hasRequiredSetToStringTag;
  function requireSetToStringTag() {
    if (hasRequiredSetToStringTag) return setToStringTag;
    hasRequiredSetToStringTag = 1;
    var defineProperty = requireObjectDefineProperty().f;
    var hasOwn = requireHasOwnProperty();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    setToStringTag = function(target, TAG, STATIC) {
      if (target && !STATIC) target = target.prototype;
      if (target && !hasOwn(target, TO_STRING_TAG)) {
        defineProperty(target, TO_STRING_TAG, { configurable: true, value: TAG });
      }
    };
    return setToStringTag;
  }
  var arrayBuffer;
  var hasRequiredArrayBuffer;
  function requireArrayBuffer() {
    if (hasRequiredArrayBuffer) return arrayBuffer;
    hasRequiredArrayBuffer = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var DESCRIPTORS = requireDescriptors();
    var NATIVE_ARRAY_BUFFER = requireArrayBufferBasicDetection();
    var FunctionName = requireFunctionName();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var fails2 = requireFails();
    var anInstance2 = requireAnInstance();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toIndex2 = requireToIndex();
    var fround = requireMathFround();
    var IEEE754 = requireIeee754();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var arrayFill2 = requireArrayFill();
    var arraySlice2 = requireArraySlice();
    var inheritIfRequired2 = requireInheritIfRequired();
    var copyConstructorProperties2 = requireCopyConstructorProperties();
    var setToStringTag2 = requireSetToStringTag();
    var InternalStateModule = requireInternalState();
    var PROPER_FUNCTION_NAME = FunctionName.PROPER;
    var CONFIGURABLE_FUNCTION_NAME = FunctionName.CONFIGURABLE;
    var ARRAY_BUFFER = "ArrayBuffer";
    var DATA_VIEW = "DataView";
    var PROTOTYPE = "prototype";
    var WRONG_LENGTH = "Wrong length";
    var WRONG_INDEX = "Wrong index";
    var getInternalArrayBufferState = InternalStateModule.getterFor(ARRAY_BUFFER);
    var getInternalDataViewState = InternalStateModule.getterFor(DATA_VIEW);
    var setInternalState = InternalStateModule.set;
    var NativeArrayBuffer = globalThis2[ARRAY_BUFFER];
    var $ArrayBuffer = NativeArrayBuffer;
    var ArrayBufferPrototype = $ArrayBuffer && $ArrayBuffer[PROTOTYPE];
    var $DataView = globalThis2[DATA_VIEW];
    var DataViewPrototype = $DataView && $DataView[PROTOTYPE];
    var ObjectPrototype = Object.prototype;
    var Array2 = globalThis2.Array;
    var RangeError2 = globalThis2.RangeError;
    var fill = uncurryThis(arrayFill2);
    var reverse = uncurryThis([].reverse);
    var packIEEE754 = IEEE754.pack;
    var unpackIEEE754 = IEEE754.unpack;
    var packInt8 = function(number) {
      return [number & 255];
    };
    var packInt16 = function(number) {
      return [number & 255, number >> 8 & 255];
    };
    var packInt32 = function(number) {
      return [number & 255, number >> 8 & 255, number >> 16 & 255, number >> 24 & 255];
    };
    var unpackInt32 = function(buffer) {
      return buffer[3] << 24 | buffer[2] << 16 | buffer[1] << 8 | buffer[0];
    };
    var packFloat32 = function(number) {
      return packIEEE754(fround(number), 23, 4);
    };
    var packFloat64 = function(number) {
      return packIEEE754(number, 52, 8);
    };
    var addGetter = function(Constructor, key, getInternalState) {
      defineBuiltInAccessor2(Constructor[PROTOTYPE], key, {
        configurable: true,
        get: function() {
          return getInternalState(this)[key];
        }
      });
    };
    var get = function(view, count, index, isLittleEndian) {
      var store = getInternalDataViewState(view);
      var intIndex = toIndex2(index);
      var boolIsLittleEndian = !!isLittleEndian;
      if (intIndex + count > store.byteLength) throw new RangeError2(WRONG_INDEX);
      var bytes = store.bytes;
      var start = intIndex + store.byteOffset;
      var pack = arraySlice2(bytes, start, start + count);
      return boolIsLittleEndian ? pack : reverse(pack);
    };
    var set = function(view, count, index, conversion, value, isLittleEndian) {
      var store = getInternalDataViewState(view);
      var intIndex = toIndex2(index);
      var pack = conversion(+value);
      var boolIsLittleEndian = !!isLittleEndian;
      if (intIndex + count > store.byteLength) throw new RangeError2(WRONG_INDEX);
      var bytes = store.bytes;
      var start = intIndex + store.byteOffset;
      for (var i = 0; i < count; i++) bytes[start + i] = pack[boolIsLittleEndian ? i : count - i - 1];
    };
    if (!NATIVE_ARRAY_BUFFER) {
      $ArrayBuffer = function ArrayBuffer2(length) {
        anInstance2(this, ArrayBufferPrototype);
        var byteLength = toIndex2(length);
        setInternalState(this, {
          type: ARRAY_BUFFER,
          bytes: fill(Array2(byteLength), 0),
          byteLength
        });
        if (!DESCRIPTORS) {
          this.byteLength = byteLength;
          this.detached = false;
        }
      };
      ArrayBufferPrototype = $ArrayBuffer[PROTOTYPE];
      $DataView = function DataView2(buffer, byteOffset, byteLength) {
        anInstance2(this, DataViewPrototype);
        anInstance2(buffer, ArrayBufferPrototype);
        var bufferState = getInternalArrayBufferState(buffer);
        var bufferLength = bufferState.byteLength;
        var offset = toIntegerOrInfinity2(byteOffset);
        if (offset < 0 || offset > bufferLength) throw new RangeError2("Wrong offset");
        byteLength = byteLength === void 0 ? bufferLength - offset : toIndex2(byteLength);
        if (offset + byteLength > bufferLength) throw new RangeError2(WRONG_LENGTH);
        setInternalState(this, {
          type: DATA_VIEW,
          buffer,
          byteLength,
          byteOffset: offset,
          bytes: bufferState.bytes
        });
        if (!DESCRIPTORS) {
          this.buffer = buffer;
          this.byteLength = byteLength;
          this.byteOffset = offset;
        }
      };
      DataViewPrototype = $DataView[PROTOTYPE];
      if (DESCRIPTORS) {
        addGetter($ArrayBuffer, "byteLength", getInternalArrayBufferState);
        addGetter($DataView, "buffer", getInternalDataViewState);
        addGetter($DataView, "byteLength", getInternalDataViewState);
        addGetter($DataView, "byteOffset", getInternalDataViewState);
      }
      defineBuiltIns2(DataViewPrototype, {
        getInt8: function getInt8(byteOffset) {
          return get(this, 1, byteOffset)[0] << 24 >> 24;
        },
        getUint8: function getUint8(byteOffset) {
          return get(this, 1, byteOffset)[0];
        },
        getInt16: function getInt16(byteOffset) {
          var bytes = get(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : false);
          return (bytes[1] << 8 | bytes[0]) << 16 >> 16;
        },
        getUint16: function getUint16(byteOffset) {
          var bytes = get(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : false);
          return bytes[1] << 8 | bytes[0];
        },
        getInt32: function getInt32(byteOffset) {
          return unpackInt32(get(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : false));
        },
        getUint32: function getUint32(byteOffset) {
          return unpackInt32(get(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : false)) >>> 0;
        },
        getFloat32: function getFloat32(byteOffset) {
          return unpackIEEE754(get(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : false), 23);
        },
        getFloat64: function getFloat64(byteOffset) {
          return unpackIEEE754(get(this, 8, byteOffset, arguments.length > 1 ? arguments[1] : false), 52);
        },
        setInt8: function setInt8(byteOffset, value) {
          set(this, 1, byteOffset, packInt8, value);
        },
        setUint8: function setUint8(byteOffset, value) {
          set(this, 1, byteOffset, packInt8, value);
        },
        setInt16: function setInt16(byteOffset, value) {
          set(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : false);
        },
        setUint16: function setUint16(byteOffset, value) {
          set(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : false);
        },
        setInt32: function setInt32(byteOffset, value) {
          set(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : false);
        },
        setUint32: function setUint32(byteOffset, value) {
          set(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : false);
        },
        setFloat32: function setFloat32(byteOffset, value) {
          set(this, 4, byteOffset, packFloat32, value, arguments.length > 2 ? arguments[2] : false);
        },
        setFloat64: function setFloat64(byteOffset, value) {
          set(this, 8, byteOffset, packFloat64, value, arguments.length > 2 ? arguments[2] : false);
        }
      });
    } else {
      var INCORRECT_ARRAY_BUFFER_NAME = PROPER_FUNCTION_NAME && NativeArrayBuffer.name !== ARRAY_BUFFER;
      if (!fails2(function() {
        NativeArrayBuffer(1);
      }) || !fails2(function() {
        new NativeArrayBuffer(-1);
      }) || fails2(function() {
        new NativeArrayBuffer();
        new NativeArrayBuffer(1.5);
        new NativeArrayBuffer(NaN);
        return NativeArrayBuffer.length !== 1 || INCORRECT_ARRAY_BUFFER_NAME && !CONFIGURABLE_FUNCTION_NAME;
      })) {
        $ArrayBuffer = function ArrayBuffer2(length) {
          anInstance2(this, ArrayBufferPrototype);
          return inheritIfRequired2(new NativeArrayBuffer(toIndex2(length)), this, $ArrayBuffer);
        };
        $ArrayBuffer[PROTOTYPE] = ArrayBufferPrototype;
        ArrayBufferPrototype.constructor = $ArrayBuffer;
        copyConstructorProperties2($ArrayBuffer, NativeArrayBuffer);
      } else if (INCORRECT_ARRAY_BUFFER_NAME && CONFIGURABLE_FUNCTION_NAME) {
        createNonEnumerableProperty2(NativeArrayBuffer, "name", ARRAY_BUFFER);
      }
      if (setPrototypeOf2 && getPrototypeOf2(DataViewPrototype) !== ObjectPrototype) {
        setPrototypeOf2(DataViewPrototype, ObjectPrototype);
      }
      var testView = new $DataView(new $ArrayBuffer(2));
      var $setInt8 = uncurryThis(DataViewPrototype.setInt8);
      testView.setInt8(0, 2147483648);
      testView.setInt8(1, 2147483649);
      if (testView.getInt8(0) || !testView.getInt8(1)) defineBuiltIns2(DataViewPrototype, {
        setInt8: function setInt8(byteOffset, value) {
          $setInt8(this, byteOffset, value << 24 >> 24);
        },
        setUint8: function setUint8(byteOffset, value) {
          $setInt8(this, byteOffset, value << 24 >> 24);
        }
      }, { unsafe: true });
    }
    setToStringTag2($ArrayBuffer, ARRAY_BUFFER);
    setToStringTag2($DataView, DATA_VIEW);
    arrayBuffer = {
      ArrayBuffer: $ArrayBuffer,
      DataView: $DataView
    };
    return arrayBuffer;
  }
  var setSpecies;
  var hasRequiredSetSpecies;
  function requireSetSpecies() {
    if (hasRequiredSetSpecies) return setSpecies;
    hasRequiredSetSpecies = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var DESCRIPTORS = requireDescriptors();
    var SPECIES = wellKnownSymbol2("species");
    setSpecies = function(CONSTRUCTOR_NAME) {
      var Constructor = getBuiltIn2(CONSTRUCTOR_NAME);
      if (DESCRIPTORS && Constructor && !Constructor[SPECIES]) {
        defineBuiltInAccessor2(Constructor, SPECIES, {
          configurable: true,
          get: function() {
            return this;
          }
        });
      }
    };
    return setSpecies;
  }
  var hasRequiredEs_arrayBuffer_constructor;
  function requireEs_arrayBuffer_constructor() {
    if (hasRequiredEs_arrayBuffer_constructor) return es_arrayBuffer_constructor;
    hasRequiredEs_arrayBuffer_constructor = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var arrayBufferModule = requireArrayBuffer();
    var setSpecies2 = requireSetSpecies();
    var ARRAY_BUFFER = "ArrayBuffer";
    var ArrayBuffer2 = arrayBufferModule[ARRAY_BUFFER];
    var NativeArrayBuffer = globalThis2[ARRAY_BUFFER];
    $({ global: true, constructor: true, forced: NativeArrayBuffer !== ArrayBuffer2 }, {
      ArrayBuffer: ArrayBuffer2
    });
    setSpecies2(ARRAY_BUFFER);
    return es_arrayBuffer_constructor;
  }
  requireEs_arrayBuffer_constructor();
  var es_arrayBuffer_slice = {};
  var hasRequiredEs_arrayBuffer_slice;
  function requireEs_arrayBuffer_slice() {
    if (hasRequiredEs_arrayBuffer_slice) return es_arrayBuffer_slice;
    hasRequiredEs_arrayBuffer_slice = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThisClause();
    var fails2 = requireFails();
    var ArrayBufferModule = requireArrayBuffer();
    var anObject2 = requireAnObject();
    var toAbsoluteIndex2 = requireToAbsoluteIndex();
    var toLength2 = requireToLength();
    var ArrayBuffer2 = ArrayBufferModule.ArrayBuffer;
    var DataView2 = ArrayBufferModule.DataView;
    var DataViewPrototype = DataView2.prototype;
    var nativeArrayBufferSlice = uncurryThis(ArrayBuffer2.prototype.slice);
    var getUint8 = uncurryThis(DataViewPrototype.getUint8);
    var setUint8 = uncurryThis(DataViewPrototype.setUint8);
    var INCORRECT_SLICE = fails2(function() {
      return !new ArrayBuffer2(2).slice(1, void 0).byteLength;
    });
    $({ target: "ArrayBuffer", proto: true, unsafe: true, forced: INCORRECT_SLICE }, {
      slice: function slice(start, end) {
        if (nativeArrayBufferSlice && end === void 0) {
          return nativeArrayBufferSlice(anObject2(this), start);
        }
        var length = anObject2(this).byteLength;
        var first = toAbsoluteIndex2(start, length);
        var fin = toAbsoluteIndex2(end === void 0 ? length : end, length);
        var result = new ArrayBuffer2(toLength2(fin - first));
        var viewSource = new DataView2(this);
        var viewTarget = new DataView2(result);
        var index = 0;
        while (first < fin) {
          setUint8(viewTarget, index++, getUint8(viewSource, first++));
        }
        return result;
      }
    });
    return es_arrayBuffer_slice;
  }
  requireEs_arrayBuffer_slice();
  var es_dataView_getFloat16 = {};
  var hasRequiredEs_dataView_getFloat16;
  function requireEs_dataView_getFloat16() {
    if (hasRequiredEs_dataView_getFloat16) return es_dataView_getFloat16;
    hasRequiredEs_dataView_getFloat16 = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var pow = Math.pow;
    var EXP_MASK16 = 31;
    var SIGNIFICAND_MASK16 = 1023;
    var MIN_SUBNORMAL16 = pow(2, -24);
    var SIGNIFICAND_DENOM16 = 9765625e-10;
    var unpackFloat16 = function(bytes) {
      var sign = bytes >>> 15;
      var exponent = bytes >>> 10 & EXP_MASK16;
      var significand = bytes & SIGNIFICAND_MASK16;
      if (exponent === EXP_MASK16) return significand === 0 ? sign === 0 ? Infinity : -Infinity : NaN;
      if (exponent === 0) return significand * (sign === 0 ? MIN_SUBNORMAL16 : -MIN_SUBNORMAL16);
      return pow(2, exponent - 15) * (sign === 0 ? 1 + significand * SIGNIFICAND_DENOM16 : -1 - significand * SIGNIFICAND_DENOM16);
    };
    var getUint16 = uncurryThis(DataView.prototype.getUint16);
    $({ target: "DataView", proto: true }, {
      getFloat16: function getFloat16(byteOffset) {
        return unpackFloat16(getUint16(this, byteOffset, arguments.length > 1 ? arguments[1] : false));
      }
    });
    return es_dataView_getFloat16;
  }
  requireEs_dataView_getFloat16();
  var es_dataView_setFloat16 = {};
  var aDataView;
  var hasRequiredADataView;
  function requireADataView() {
    if (hasRequiredADataView) return aDataView;
    hasRequiredADataView = 1;
    var classof2 = requireClassof();
    var $TypeError = TypeError;
    aDataView = function(argument) {
      if (classof2(argument) === "DataView") return argument;
      throw new $TypeError("Argument is not a DataView");
    };
    return aDataView;
  }
  var mathLog2;
  var hasRequiredMathLog2;
  function requireMathLog2() {
    if (hasRequiredMathLog2) return mathLog2;
    hasRequiredMathLog2 = 1;
    var log = Math.log;
    var LN2 = Math.LN2;
    mathLog2 = Math.log2 || function log2(x) {
      return log(x) / LN2;
    };
    return mathLog2;
  }
  var hasRequiredEs_dataView_setFloat16;
  function requireEs_dataView_setFloat16() {
    if (hasRequiredEs_dataView_setFloat16) return es_dataView_setFloat16;
    hasRequiredEs_dataView_setFloat16 = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var aDataView2 = requireADataView();
    var toIndex2 = requireToIndex();
    var log2 = requireMathLog2();
    var roundTiesToEven = requireMathRoundTiesToEven();
    var floor = Math.floor;
    var pow = Math.pow;
    var MIN_INFINITY16 = 65520;
    var MIN_NORMAL16 = 61005353927612305e-21;
    var REC_MIN_SUBNORMAL16 = 16777216;
    var REC_SIGNIFICAND_DENOM16 = 1024;
    var packFloat16 = function(value) {
      if (value !== value) return 32256;
      if (value === 0) return (1 / value === -Infinity) << 15;
      var neg = value < 0;
      if (neg) value = -value;
      if (value >= MIN_INFINITY16) return neg << 15 | 31744;
      if (value < MIN_NORMAL16) return neg << 15 | roundTiesToEven(value * REC_MIN_SUBNORMAL16);
      var exponent = floor(log2(value));
      if (exponent === -15) {
        return neg << 15 | REC_SIGNIFICAND_DENOM16;
      }
      var significand = roundTiesToEven((value * pow(2, -exponent) - 1) * REC_SIGNIFICAND_DENOM16);
      if (significand === REC_SIGNIFICAND_DENOM16) {
        return neg << 15 | exponent + 16 << 10;
      }
      return neg << 15 | exponent + 15 << 10 | significand;
    };
    var setUint16 = uncurryThis(DataView.prototype.setUint16);
    $({ target: "DataView", proto: true }, {
      setFloat16: function setFloat16(byteOffset, value) {
        setUint16(
          aDataView2(this),
          toIndex2(byteOffset),
          packFloat16(+value),
          arguments.length > 2 ? arguments[2] : false
        );
      }
    });
    return es_dataView_setFloat16;
  }
  requireEs_dataView_setFloat16();
  var es_arrayBuffer_detached = {};
  var arrayBufferByteLength;
  var hasRequiredArrayBufferByteLength;
  function requireArrayBufferByteLength() {
    if (hasRequiredArrayBufferByteLength) return arrayBufferByteLength;
    hasRequiredArrayBufferByteLength = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThisAccessor = requireFunctionUncurryThisAccessor();
    var classof2 = requireClassofRaw();
    var ArrayBuffer2 = globalThis2.ArrayBuffer;
    var TypeError2 = globalThis2.TypeError;
    arrayBufferByteLength = ArrayBuffer2 && uncurryThisAccessor(ArrayBuffer2.prototype, "byteLength", "get") || function(O) {
      if (classof2(O) !== "ArrayBuffer") throw new TypeError2("ArrayBuffer expected");
      return O.byteLength;
    };
    return arrayBufferByteLength;
  }
  var arrayBufferIsDetached;
  var hasRequiredArrayBufferIsDetached;
  function requireArrayBufferIsDetached() {
    if (hasRequiredArrayBufferIsDetached) return arrayBufferIsDetached;
    hasRequiredArrayBufferIsDetached = 1;
    var globalThis2 = requireGlobalThis();
    var NATIVE_ARRAY_BUFFER = requireArrayBufferBasicDetection();
    var arrayBufferByteLength2 = requireArrayBufferByteLength();
    var DataView2 = globalThis2.DataView;
    arrayBufferIsDetached = function(O) {
      if (!NATIVE_ARRAY_BUFFER || arrayBufferByteLength2(O) !== 0) return false;
      try {
        new DataView2(O);
        return false;
      } catch (error) {
        return true;
      }
    };
    return arrayBufferIsDetached;
  }
  var hasRequiredEs_arrayBuffer_detached;
  function requireEs_arrayBuffer_detached() {
    if (hasRequiredEs_arrayBuffer_detached) return es_arrayBuffer_detached;
    hasRequiredEs_arrayBuffer_detached = 1;
    var DESCRIPTORS = requireDescriptors();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var isDetached = requireArrayBufferIsDetached();
    var ArrayBufferPrototype = ArrayBuffer.prototype;
    if (DESCRIPTORS && !("detached" in ArrayBufferPrototype)) {
      defineBuiltInAccessor2(ArrayBufferPrototype, "detached", {
        configurable: true,
        get: function detached() {
          return isDetached(this);
        }
      });
    }
    return es_arrayBuffer_detached;
  }
  requireEs_arrayBuffer_detached();
  var es_arrayBuffer_transfer = {};
  var arrayBufferNotDetached;
  var hasRequiredArrayBufferNotDetached;
  function requireArrayBufferNotDetached() {
    if (hasRequiredArrayBufferNotDetached) return arrayBufferNotDetached;
    hasRequiredArrayBufferNotDetached = 1;
    var isDetached = requireArrayBufferIsDetached();
    var $TypeError = TypeError;
    arrayBufferNotDetached = function(it) {
      if (isDetached(it)) throw new $TypeError("ArrayBuffer is detached");
      return it;
    };
    return arrayBufferNotDetached;
  }
  var getBuiltInNodeModule;
  var hasRequiredGetBuiltInNodeModule;
  function requireGetBuiltInNodeModule() {
    if (hasRequiredGetBuiltInNodeModule) return getBuiltInNodeModule;
    hasRequiredGetBuiltInNodeModule = 1;
    var globalThis2 = requireGlobalThis();
    var IS_NODE = requireEnvironmentIsNode();
    getBuiltInNodeModule = function(name) {
      if (IS_NODE) {
        try {
          return globalThis2.process.getBuiltinModule(name);
        } catch (error) {
        }
        try {
          return Function('return require("' + name + '")')();
        } catch (error) {
        }
      }
    };
    return getBuiltInNodeModule;
  }
  var structuredCloneProperTransfer;
  var hasRequiredStructuredCloneProperTransfer;
  function requireStructuredCloneProperTransfer() {
    if (hasRequiredStructuredCloneProperTransfer) return structuredCloneProperTransfer;
    hasRequiredStructuredCloneProperTransfer = 1;
    var globalThis2 = requireGlobalThis();
    var fails2 = requireFails();
    var V8 = requireEnvironmentV8Version();
    var ENVIRONMENT = requireEnvironment();
    var structuredClone = globalThis2.structuredClone;
    structuredCloneProperTransfer = !!structuredClone && !fails2(function() {
      if (ENVIRONMENT === "DENO" && V8 > 92 || ENVIRONMENT === "NODE" && V8 > 94 || ENVIRONMENT === "BROWSER" && V8 > 97) return false;
      var buffer = new ArrayBuffer(8);
      var clone2 = structuredClone(buffer, { transfer: [buffer] });
      return buffer.byteLength !== 0 || clone2.byteLength !== 8;
    });
    return structuredCloneProperTransfer;
  }
  var detachTransferable;
  var hasRequiredDetachTransferable;
  function requireDetachTransferable() {
    if (hasRequiredDetachTransferable) return detachTransferable;
    hasRequiredDetachTransferable = 1;
    var globalThis2 = requireGlobalThis();
    var getBuiltInNodeModule2 = requireGetBuiltInNodeModule();
    var PROPER_STRUCTURED_CLONE_TRANSFER = requireStructuredCloneProperTransfer();
    var structuredClone = globalThis2.structuredClone;
    var $ArrayBuffer = globalThis2.ArrayBuffer;
    var $MessageChannel = globalThis2.MessageChannel;
    var detach = false;
    var WorkerThreads, channel, buffer, $detach;
    if (PROPER_STRUCTURED_CLONE_TRANSFER) {
      detach = function(transferable) {
        structuredClone(transferable, { transfer: [transferable] });
      };
    } else if ($ArrayBuffer) try {
      if (!$MessageChannel) {
        WorkerThreads = getBuiltInNodeModule2("worker_threads");
        if (WorkerThreads) $MessageChannel = WorkerThreads.MessageChannel;
      }
      if ($MessageChannel) {
        channel = new $MessageChannel();
        buffer = new $ArrayBuffer(2);
        $detach = function(transferable) {
          channel.port1.postMessage(null, [transferable]);
        };
        if (buffer.byteLength === 2) {
          $detach(buffer);
          if (buffer.byteLength === 0) detach = $detach;
        }
      }
    } catch (error) {
    }
    detachTransferable = detach;
    return detachTransferable;
  }
  var arrayBufferTransfer;
  var hasRequiredArrayBufferTransfer;
  function requireArrayBufferTransfer() {
    if (hasRequiredArrayBufferTransfer) return arrayBufferTransfer;
    hasRequiredArrayBufferTransfer = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var uncurryThisAccessor = requireFunctionUncurryThisAccessor();
    var toIndex2 = requireToIndex();
    var notDetached = requireArrayBufferNotDetached();
    var arrayBufferByteLength2 = requireArrayBufferByteLength();
    var detachTransferable2 = requireDetachTransferable();
    var PROPER_STRUCTURED_CLONE_TRANSFER = requireStructuredCloneProperTransfer();
    var structuredClone = globalThis2.structuredClone;
    var ArrayBuffer2 = globalThis2.ArrayBuffer;
    var DataView2 = globalThis2.DataView;
    var max = Math.max;
    var min = Math.min;
    var ArrayBufferPrototype = ArrayBuffer2.prototype;
    var DataViewPrototype = DataView2.prototype;
    var slice = uncurryThis(ArrayBufferPrototype.slice);
    var isResizable = uncurryThisAccessor(ArrayBufferPrototype, "resizable", "get");
    var maxByteLength = uncurryThisAccessor(ArrayBufferPrototype, "maxByteLength", "get");
    var getInt8 = uncurryThis(DataViewPrototype.getInt8);
    var setInt8 = uncurryThis(DataViewPrototype.setInt8);
    arrayBufferTransfer = (PROPER_STRUCTURED_CLONE_TRANSFER || detachTransferable2) && function(arrayBuffer2, newLength, preserveResizability) {
      var byteLength = arrayBufferByteLength2(arrayBuffer2);
      var newByteLength = newLength === void 0 ? byteLength : toIndex2(newLength);
      var fixedLength = !isResizable || !isResizable(arrayBuffer2);
      var newBuffer;
      notDetached(arrayBuffer2);
      if (PROPER_STRUCTURED_CLONE_TRANSFER) {
        arrayBuffer2 = structuredClone(arrayBuffer2, { transfer: [arrayBuffer2] });
        if (byteLength === newByteLength && (preserveResizability || fixedLength)) return arrayBuffer2;
      }
      if (byteLength >= newByteLength && (!preserveResizability || fixedLength)) {
        newBuffer = slice(arrayBuffer2, 0, newByteLength);
      } else {
        var options = preserveResizability && !fixedLength && maxByteLength ? { maxByteLength: max(newByteLength, maxByteLength(arrayBuffer2)) } : void 0;
        newBuffer = new ArrayBuffer2(newByteLength, options);
        var a = new DataView2(arrayBuffer2);
        var b = new DataView2(newBuffer);
        var copyLength = min(newByteLength, byteLength);
        for (var i = 0; i < copyLength; i++) setInt8(b, i, getInt8(a, i));
      }
      if (!PROPER_STRUCTURED_CLONE_TRANSFER) detachTransferable2(arrayBuffer2);
      return newBuffer;
    };
    return arrayBufferTransfer;
  }
  var hasRequiredEs_arrayBuffer_transfer;
  function requireEs_arrayBuffer_transfer() {
    if (hasRequiredEs_arrayBuffer_transfer) return es_arrayBuffer_transfer;
    hasRequiredEs_arrayBuffer_transfer = 1;
    var $ = require_export();
    var $transfer = requireArrayBufferTransfer();
    if ($transfer) $({ target: "ArrayBuffer", proto: true }, {
      transfer: function transfer() {
        return $transfer(this, arguments.length ? arguments[0] : void 0, true);
      }
    });
    return es_arrayBuffer_transfer;
  }
  requireEs_arrayBuffer_transfer();
  var es_arrayBuffer_transferToFixedLength = {};
  var hasRequiredEs_arrayBuffer_transferToFixedLength;
  function requireEs_arrayBuffer_transferToFixedLength() {
    if (hasRequiredEs_arrayBuffer_transferToFixedLength) return es_arrayBuffer_transferToFixedLength;
    hasRequiredEs_arrayBuffer_transferToFixedLength = 1;
    var $ = require_export();
    var $transfer = requireArrayBufferTransfer();
    if ($transfer) $({ target: "ArrayBuffer", proto: true }, {
      transferToFixedLength: function transferToFixedLength() {
        return $transfer(this, arguments.length ? arguments[0] : void 0, false);
      }
    });
    return es_arrayBuffer_transferToFixedLength;
  }
  requireEs_arrayBuffer_transferToFixedLength();
  var es_disposableStack_constructor = {};
  var addDisposableResource;
  var hasRequiredAddDisposableResource;
  function requireAddDisposableResource() {
    if (hasRequiredAddDisposableResource) return addDisposableResource;
    hasRequiredAddDisposableResource = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var bind = requireFunctionBindContext();
    var anObject2 = requireAnObject();
    var aCallable2 = requireACallable();
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    var getMethod2 = requireGetMethod();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ASYNC_DISPOSE = wellKnownSymbol2("asyncDispose");
    var DISPOSE = wellKnownSymbol2("dispose");
    var push = uncurryThis([].push);
    var getDisposeMethod = function(V, hint) {
      if (hint === "async-dispose") {
        var method = getMethod2(V, ASYNC_DISPOSE);
        if (method !== void 0) return method;
        method = getMethod2(V, DISPOSE);
        if (method === void 0) return method;
        return function() {
          var O = this;
          var Promise2 = getBuiltIn2("Promise");
          return new Promise2(function(resolve) {
            call(method, O);
            resolve(void 0);
          });
        };
      }
      return getMethod2(V, DISPOSE);
    };
    var createDisposableResource = function(V, hint, method) {
      if (arguments.length < 3 && !isNullOrUndefined2(V)) {
        method = aCallable2(getDisposeMethod(anObject2(V), hint));
      }
      return method === void 0 ? function() {
        return void 0;
      } : bind(method, V);
    };
    addDisposableResource = function(disposable, V, hint, method) {
      var resource;
      if (arguments.length < 4) {
        if (isNullOrUndefined2(V) && hint === "sync-dispose") return;
        resource = createDisposableResource(V, hint);
      } else {
        resource = createDisposableResource(void 0, hint, method);
      }
      push(disposable.stack, resource);
    };
    return addDisposableResource;
  }
  var hasRequiredEs_disposableStack_constructor;
  function requireEs_disposableStack_constructor() {
    if (hasRequiredEs_disposableStack_constructor) return es_disposableStack_constructor;
    hasRequiredEs_disposableStack_constructor = 1;
    var $ = require_export();
    var DESCRIPTORS = requireDescriptors();
    var getBuiltIn2 = requireGetBuiltIn();
    var aCallable2 = requireACallable();
    var anInstance2 = requireAnInstance();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var InternalStateModule = requireInternalState();
    var addDisposableResource2 = requireAddDisposableResource();
    var SuppressedError2 = getBuiltIn2("SuppressedError");
    var $ReferenceError = ReferenceError;
    var DISPOSE = wellKnownSymbol2("dispose");
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var DISPOSABLE_STACK = "DisposableStack";
    var setInternalState = InternalStateModule.set;
    var getDisposableStackInternalState = InternalStateModule.getterFor(DISPOSABLE_STACK);
    var HINT = "sync-dispose";
    var DISPOSED = "disposed";
    var PENDING = "pending";
    var getPendingDisposableStackInternalState = function(stack) {
      var internalState2 = getDisposableStackInternalState(stack);
      if (internalState2.state === DISPOSED) throw new $ReferenceError(DISPOSABLE_STACK + " already disposed");
      return internalState2;
    };
    var $DisposableStack = function DisposableStack() {
      setInternalState(anInstance2(this, DisposableStackPrototype), {
        type: DISPOSABLE_STACK,
        state: PENDING,
        stack: []
      });
      if (!DESCRIPTORS) this.disposed = false;
    };
    var DisposableStackPrototype = $DisposableStack.prototype;
    defineBuiltIns2(DisposableStackPrototype, {
      dispose: function dispose() {
        var internalState2 = getDisposableStackInternalState(this);
        if (internalState2.state === DISPOSED) return;
        internalState2.state = DISPOSED;
        if (!DESCRIPTORS) this.disposed = true;
        var stack = internalState2.stack;
        var i = stack.length;
        var thrown = false;
        var suppressed;
        while (i) {
          var disposeMethod = stack[--i];
          stack[i] = null;
          try {
            disposeMethod();
          } catch (errorResult) {
            if (thrown) {
              suppressed = new SuppressedError2(errorResult, suppressed);
            } else {
              thrown = true;
              suppressed = errorResult;
            }
          }
        }
        internalState2.stack = null;
        if (thrown) throw suppressed;
      },
      use: function use(value) {
        addDisposableResource2(getPendingDisposableStackInternalState(this), value, HINT);
        return value;
      },
      adopt: function adopt(value, onDispose) {
        var internalState2 = getPendingDisposableStackInternalState(this);
        aCallable2(onDispose);
        addDisposableResource2(internalState2, void 0, HINT, function() {
          onDispose(value);
        });
        return value;
      },
      defer: function defer(onDispose) {
        var internalState2 = getPendingDisposableStackInternalState(this);
        aCallable2(onDispose);
        addDisposableResource2(internalState2, void 0, HINT, onDispose);
      },
      move: function move() {
        var internalState2 = getPendingDisposableStackInternalState(this);
        var newDisposableStack = new $DisposableStack();
        getDisposableStackInternalState(newDisposableStack).stack = internalState2.stack;
        internalState2.stack = [];
        internalState2.state = DISPOSED;
        if (!DESCRIPTORS) this.disposed = true;
        return newDisposableStack;
      }
    });
    if (DESCRIPTORS) defineBuiltInAccessor2(DisposableStackPrototype, "disposed", {
      configurable: true,
      get: function disposed() {
        return getDisposableStackInternalState(this).state === DISPOSED;
      }
    });
    defineBuiltIn2(DisposableStackPrototype, DISPOSE, DisposableStackPrototype.dispose, { name: "dispose" });
    defineBuiltIn2(DisposableStackPrototype, TO_STRING_TAG, DISPOSABLE_STACK, { nonWritable: true });
    $({ global: true, constructor: true }, {
      DisposableStack: $DisposableStack
    });
    return es_disposableStack_constructor;
  }
  requireEs_disposableStack_constructor();
  var es_globalThis = {};
  var hasRequiredEs_globalThis;
  function requireEs_globalThis() {
    if (hasRequiredEs_globalThis) return es_globalThis;
    hasRequiredEs_globalThis = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    $({ global: true, forced: globalThis2.globalThis !== globalThis2 }, {
      globalThis: globalThis2
    });
    return es_globalThis;
  }
  requireEs_globalThis();
  var es_iterator_constructor = {};
  var iteratorsCore;
  var hasRequiredIteratorsCore;
  function requireIteratorsCore() {
    if (hasRequiredIteratorsCore) return iteratorsCore;
    hasRequiredIteratorsCore = 1;
    var fails2 = requireFails();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var create2 = requireObjectCreate();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var IS_PURE = requireIsPure();
    var ITERATOR = wellKnownSymbol2("iterator");
    var BUGGY_SAFARI_ITERATORS = false;
    var IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;
    if ([].keys) {
      arrayIterator = [].keys();
      if (!("next" in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;
      else {
        PrototypeOfArrayIteratorPrototype = getPrototypeOf2(getPrototypeOf2(arrayIterator));
        if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;
      }
    }
    var NEW_ITERATOR_PROTOTYPE = !isObject2(IteratorPrototype) || fails2(function() {
      var test = {};
      return IteratorPrototype[ITERATOR].call(test) !== test;
    });
    if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype = {};
    else if (IS_PURE) IteratorPrototype = create2(IteratorPrototype);
    if (!isCallable2(IteratorPrototype[ITERATOR])) {
      defineBuiltIn2(IteratorPrototype, ITERATOR, function() {
        return this;
      });
    }
    iteratorsCore = {
      IteratorPrototype,
      BUGGY_SAFARI_ITERATORS
    };
    return iteratorsCore;
  }
  var hasRequiredEs_iterator_constructor;
  function requireEs_iterator_constructor() {
    if (hasRequiredEs_iterator_constructor) return es_iterator_constructor;
    hasRequiredEs_iterator_constructor = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var anInstance2 = requireAnInstance();
    var anObject2 = requireAnObject();
    var isCallable2 = requireIsCallable();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var createProperty2 = requireCreateProperty();
    var fails2 = requireFails();
    var hasOwn = requireHasOwnProperty();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var IteratorPrototype = requireIteratorsCore().IteratorPrototype;
    var DESCRIPTORS = requireDescriptors();
    var IS_PURE = requireIsPure();
    var CONSTRUCTOR = "constructor";
    var ITERATOR = "Iterator";
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var $TypeError = TypeError;
    var NativeIterator = globalThis2[ITERATOR];
    var FORCED = IS_PURE || !isCallable2(NativeIterator) || NativeIterator.prototype !== IteratorPrototype || !fails2(function() {
      NativeIterator({});
    });
    var IteratorConstructor = function Iterator2() {
      anInstance2(this, IteratorPrototype);
      if (getPrototypeOf2(this) === IteratorPrototype) throw new $TypeError("Abstract class Iterator not directly constructable");
    };
    var defineIteratorPrototypeAccessor = function(key, value) {
      if (DESCRIPTORS) {
        defineBuiltInAccessor2(IteratorPrototype, key, {
          configurable: true,
          get: function() {
            return value;
          },
          set: function(replacement) {
            anObject2(this);
            if (this === IteratorPrototype) throw new $TypeError("You can't redefine this property");
            if (hasOwn(this, key)) this[key] = replacement;
            else createProperty2(this, key, replacement);
          }
        });
      } else IteratorPrototype[key] = value;
    };
    if (!hasOwn(IteratorPrototype, TO_STRING_TAG)) defineIteratorPrototypeAccessor(TO_STRING_TAG, ITERATOR);
    if (FORCED || !hasOwn(IteratorPrototype, CONSTRUCTOR) || IteratorPrototype[CONSTRUCTOR] === Object) {
      defineIteratorPrototypeAccessor(CONSTRUCTOR, IteratorConstructor);
    }
    IteratorConstructor.prototype = IteratorPrototype;
    $({ global: true, constructor: true, forced: FORCED }, {
      Iterator: IteratorConstructor
    });
    return es_iterator_constructor;
  }
  requireEs_iterator_constructor();
  var es_iterator_concat = {};
  var createIterResultObject;
  var hasRequiredCreateIterResultObject;
  function requireCreateIterResultObject() {
    if (hasRequiredCreateIterResultObject) return createIterResultObject;
    hasRequiredCreateIterResultObject = 1;
    createIterResultObject = function(value, done) {
      return { value, done };
    };
    return createIterResultObject;
  }
  var iteratorCloseAll;
  var hasRequiredIteratorCloseAll;
  function requireIteratorCloseAll() {
    if (hasRequiredIteratorCloseAll) return iteratorCloseAll;
    hasRequiredIteratorCloseAll = 1;
    var iteratorClose2 = requireIteratorClose();
    iteratorCloseAll = function(iters, kind, value) {
      for (var i = iters.length - 1; i >= 0; i--) {
        if (iters[i] === void 0) continue;
        try {
          value = iteratorClose2(iters[i].iterator, kind, value);
        } catch (error) {
          kind = "throw";
          value = error;
        }
      }
      if (kind === "throw") throw value;
      return value;
    };
    return iteratorCloseAll;
  }
  var iteratorCleanupState;
  var hasRequiredIteratorCleanupState;
  function requireIteratorCleanupState() {
    if (hasRequiredIteratorCleanupState) return iteratorCleanupState;
    hasRequiredIteratorCleanupState = 1;
    iteratorCleanupState = function(state) {
      state.iterator = state.next = state.nextHandler = state.mapper = state.predicate = state.inner = state.iterables = state.iters = state.openIters = state.padding = state.finishResults = state.buffer = null;
    };
    return iteratorCleanupState;
  }
  var iteratorCreateProxy;
  var hasRequiredIteratorCreateProxy;
  function requireIteratorCreateProxy() {
    if (hasRequiredIteratorCreateProxy) return iteratorCreateProxy;
    hasRequiredIteratorCreateProxy = 1;
    var call = requireFunctionCall();
    var create2 = requireObjectCreate();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var InternalStateModule = requireInternalState();
    var getMethod2 = requireGetMethod();
    var IteratorPrototype = requireIteratorsCore().IteratorPrototype;
    var createIterResultObject2 = requireCreateIterResultObject();
    var iteratorClose2 = requireIteratorClose();
    var iteratorCloseAll2 = requireIteratorCloseAll();
    var cleanupState = requireIteratorCleanupState();
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var ITERATOR_HELPER = "IteratorHelper";
    var WRAP_FOR_VALID_ITERATOR = "WrapForValidIterator";
    var NORMAL = "normal";
    var THROW = "throw";
    var setInternalState = InternalStateModule.set;
    var createIteratorProxyPrototype = function(IS_ITERATOR) {
      var getInternalState = InternalStateModule.getterFor(IS_ITERATOR ? WRAP_FOR_VALID_ITERATOR : ITERATOR_HELPER);
      return defineBuiltIns2(create2(IteratorPrototype), {
        next: function next() {
          var state = getInternalState(this);
          if (IS_ITERATOR) return state.nextHandler();
          if (state.done) return createIterResultObject2(void 0, true);
          try {
            var result = state.nextHandler();
            if (state.done) cleanupState(state);
            return state.returnHandlerResult ? result : createIterResultObject2(result, state.done);
          } catch (error) {
            state.done = true;
            cleanupState(state);
            throw error;
          }
        },
        "return": function() {
          var state = getInternalState(this);
          var iterator = state.iterator;
          var inner = state.inner;
          var openIters = state.openIters;
          var done = state.done;
          state.done = true;
          if (IS_ITERATOR) {
            var returnMethod = getMethod2(iterator, "return");
            return returnMethod ? call(returnMethod, iterator) : createIterResultObject2(void 0, true);
          }
          cleanupState(state);
          if (done) return createIterResultObject2(void 0, true);
          if (inner) try {
            iteratorClose2(inner.iterator, NORMAL);
          } catch (error) {
            return iteratorClose2(iterator, THROW, error);
          }
          if (openIters) try {
            iteratorCloseAll2(openIters, NORMAL);
          } catch (error) {
            if (iterator) return iteratorClose2(iterator, THROW, error);
            throw error;
          }
          if (iterator) iteratorClose2(iterator, NORMAL);
          return createIterResultObject2(void 0, true);
        }
      });
    };
    var WrapForValidIteratorPrototype = createIteratorProxyPrototype(true);
    var IteratorHelperPrototype = createIteratorProxyPrototype(false);
    createNonEnumerableProperty2(IteratorHelperPrototype, TO_STRING_TAG, "Iterator Helper");
    iteratorCreateProxy = function(nextHandler, IS_ITERATOR, RETURN_HANDLER_RESULT) {
      var IteratorProxy = function Iterator2(record, state) {
        if (state) {
          state.iterator = record.iterator;
          state.next = record.next;
        } else state = record;
        state.type = IS_ITERATOR ? WRAP_FOR_VALID_ITERATOR : ITERATOR_HELPER;
        state.returnHandlerResult = !!RETURN_HANDLER_RESULT;
        state.nextHandler = nextHandler;
        state.counter = 0;
        state.done = false;
        setInternalState(this, state);
      };
      IteratorProxy.prototype = IS_ITERATOR ? WrapForValidIteratorPrototype : IteratorHelperPrototype;
      return IteratorProxy;
    };
    return iteratorCreateProxy;
  }
  var hasRequiredEs_iterator_concat;
  function requireEs_iterator_concat() {
    if (hasRequiredEs_iterator_concat) return es_iterator_concat;
    hasRequiredEs_iterator_concat = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var createIteratorProxy = requireIteratorCreateProxy();
    var IS_PURE = requireIsPure();
    var $Array = Array;
    var IteratorProxy = createIteratorProxy(function() {
      while (true) {
        var iterator = this.iterator;
        if (!iterator) {
          var iterableIndex = this.nextIterableIndex++;
          var iterables = this.iterables;
          if (iterableIndex >= iterables.length) {
            this.done = true;
            return;
          }
          var entry = iterables[iterableIndex];
          this.iterables[iterableIndex] = null;
          iterator = this.iterator = anObject2(call(entry.method, entry.iterable));
          this.next = iterator.next;
        }
        var result = anObject2(call(this.next, iterator));
        if (result.done) {
          this.iterator = null;
          this.next = null;
          continue;
        }
        return result.value;
      }
    });
    $({ target: "Iterator", stat: true, forced: IS_PURE }, {
      concat: function concat() {
        var length = arguments.length;
        var iterables = $Array(length);
        for (var index = 0; index < length; index++) {
          var item = anObject2(arguments[index]);
          iterables[index] = {
            iterable: item,
            method: aCallable2(getIteratorMethod(item))
          };
        }
        return new IteratorProxy({
          iterables,
          nextIterableIndex: 0,
          iterator: null,
          next: null
        });
      }
    });
    return es_iterator_concat;
  }
  requireEs_iterator_concat();
  var es_iterator_dispose = {};
  var hasRequiredEs_iterator_dispose;
  function requireEs_iterator_dispose() {
    if (hasRequiredEs_iterator_dispose) return es_iterator_dispose;
    hasRequiredEs_iterator_dispose = 1;
    var call = requireFunctionCall();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var getMethod2 = requireGetMethod();
    var hasOwn = requireHasOwnProperty();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var IteratorPrototype = requireIteratorsCore().IteratorPrototype;
    var DISPOSE = wellKnownSymbol2("dispose");
    if (!hasOwn(IteratorPrototype, DISPOSE)) {
      defineBuiltIn2(IteratorPrototype, DISPOSE, function() {
        var $return = getMethod2(this, "return");
        if ($return) call($return, this);
      });
    }
    return es_iterator_dispose;
  }
  requireEs_iterator_dispose();
  var es_iterator_drop = {};
  var getIteratorDirect;
  var hasRequiredGetIteratorDirect;
  function requireGetIteratorDirect() {
    if (hasRequiredGetIteratorDirect) return getIteratorDirect;
    hasRequiredGetIteratorDirect = 1;
    getIteratorDirect = function(obj) {
      return {
        iterator: obj,
        next: obj.next,
        done: false
      };
    };
    return getIteratorDirect;
  }
  var notANan;
  var hasRequiredNotANan;
  function requireNotANan() {
    if (hasRequiredNotANan) return notANan;
    hasRequiredNotANan = 1;
    var $RangeError = RangeError;
    notANan = function(it) {
      if (it === it) return it;
      throw new $RangeError("NaN is not allowed");
    };
    return notANan;
  }
  var toPositiveInteger;
  var hasRequiredToPositiveInteger;
  function requireToPositiveInteger() {
    if (hasRequiredToPositiveInteger) return toPositiveInteger;
    hasRequiredToPositiveInteger = 1;
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var $RangeError = RangeError;
    toPositiveInteger = function(it) {
      var result = toIntegerOrInfinity2(it);
      if (result < 0) throw new $RangeError("The argument can't be less than 0");
      return result;
    };
    return toPositiveInteger;
  }
  var iteratorHelperThrowsOnInvalidIterator;
  var hasRequiredIteratorHelperThrowsOnInvalidIterator;
  function requireIteratorHelperThrowsOnInvalidIterator() {
    if (hasRequiredIteratorHelperThrowsOnInvalidIterator) return iteratorHelperThrowsOnInvalidIterator;
    hasRequiredIteratorHelperThrowsOnInvalidIterator = 1;
    iteratorHelperThrowsOnInvalidIterator = function(methodName, argument) {
      var method = typeof Iterator == "function" && Iterator.prototype[methodName];
      if (method) try {
        method.call({ next: null }, argument).next();
      } catch (error) {
        return true;
      }
    };
    return iteratorHelperThrowsOnInvalidIterator;
  }
  var iteratorHelperWithoutClosingOnEarlyError;
  var hasRequiredIteratorHelperWithoutClosingOnEarlyError;
  function requireIteratorHelperWithoutClosingOnEarlyError() {
    if (hasRequiredIteratorHelperWithoutClosingOnEarlyError) return iteratorHelperWithoutClosingOnEarlyError;
    hasRequiredIteratorHelperWithoutClosingOnEarlyError = 1;
    var globalThis2 = requireGlobalThis();
    iteratorHelperWithoutClosingOnEarlyError = function(METHOD_NAME, ExpectedError) {
      var Iterator2 = globalThis2.Iterator;
      var IteratorPrototype = Iterator2 && Iterator2.prototype;
      var method = IteratorPrototype && IteratorPrototype[METHOD_NAME];
      var CLOSED = false;
      if (method) try {
        method.call({
          next: function() {
            return { done: true };
          },
          "return": function() {
            CLOSED = true;
          }
        }, -1);
      } catch (error) {
        if (!(error instanceof ExpectedError)) CLOSED = false;
      }
      if (!CLOSED) return method;
    };
    return iteratorHelperWithoutClosingOnEarlyError;
  }
  var hasRequiredEs_iterator_drop;
  function requireEs_iterator_drop() {
    if (hasRequiredEs_iterator_drop) return es_iterator_drop;
    hasRequiredEs_iterator_drop = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var notANaN = requireNotANan();
    var toPositiveInteger2 = requireToPositiveInteger();
    var iteratorClose2 = requireIteratorClose();
    var createIteratorProxy = requireIteratorCreateProxy();
    var iteratorHelperThrowsOnInvalidIterator2 = requireIteratorHelperThrowsOnInvalidIterator();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var IS_PURE = requireIsPure();
    var $RangeError = RangeError;
    var $Infinity = Infinity;
    var DROP_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator2("drop", 0);
    var dropWithoutClosingOnEarlyError = !IS_PURE && !DROP_WITHOUT_THROWING_ON_INVALID_ITERATOR && iteratorHelperWithoutClosingOnEarlyError2("drop", RangeError);
    var FORCED = IS_PURE || DROP_WITHOUT_THROWING_ON_INVALID_ITERATOR || dropWithoutClosingOnEarlyError || !(function() {
      try {
        Iterator.prototype.drop.call({
          next: function() {
            return { done: true };
          }
        }, 9007199254740992);
      } catch (error) {
        return error instanceof $RangeError;
      }
    })();
    var IteratorProxy = createIteratorProxy(function() {
      var iterator = this.iterator;
      var next = this.next;
      var result, done;
      while (this.remaining) {
        this.remaining--;
        result = anObject2(call(next, iterator));
        done = this.done = !!result.done;
        if (done) return;
      }
      result = anObject2(call(next, iterator));
      done = this.done = !!result.done;
      if (!done) return result.value;
    });
    $({ target: "Iterator", proto: true, real: true, forced: FORCED }, {
      drop: function drop(limit) {
        anObject2(this);
        var remaining;
        try {
          remaining = toPositiveInteger2(notANaN(+limit));
          if (remaining > 9007199254740991 && remaining !== $Infinity) {
            throw new $RangeError("The argument should be a safe integer");
          }
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (dropWithoutClosingOnEarlyError) return call(dropWithoutClosingOnEarlyError, this, remaining);
        return new IteratorProxy(getIteratorDirect2(this), {
          remaining
        });
      }
    });
    return es_iterator_drop;
  }
  requireEs_iterator_drop();
  var es_iterator_every = {};
  var hasRequiredEs_iterator_every;
  function requireEs_iterator_every() {
    if (hasRequiredEs_iterator_every) return es_iterator_every;
    hasRequiredEs_iterator_every = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var iterate2 = requireIterate();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var everyWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError2("every", TypeError);
    $({ target: "Iterator", proto: true, real: true, forced: everyWithoutClosingOnEarlyError }, {
      every: function every(predicate) {
        anObject2(this);
        try {
          aCallable2(predicate);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (everyWithoutClosingOnEarlyError) return call(everyWithoutClosingOnEarlyError, this, predicate);
        var record = getIteratorDirect2(this);
        var counter = 0;
        return !iterate2(record, function(value, stop) {
          if (!predicate(value, counter++)) return stop();
        }, { IS_RECORD: true, INTERRUPTED: true }).stopped;
      }
    });
    return es_iterator_every;
  }
  requireEs_iterator_every();
  var es_iterator_filter = {};
  var callWithSafeIterationClosing;
  var hasRequiredCallWithSafeIterationClosing;
  function requireCallWithSafeIterationClosing() {
    if (hasRequiredCallWithSafeIterationClosing) return callWithSafeIterationClosing;
    hasRequiredCallWithSafeIterationClosing = 1;
    var anObject2 = requireAnObject();
    var iteratorClose2 = requireIteratorClose();
    callWithSafeIterationClosing = function(iterator, fn, value, ENTRIES) {
      try {
        return ENTRIES ? fn(anObject2(value)[0], value[1]) : fn(value);
      } catch (error) {
        iteratorClose2(iterator, "throw", error);
      }
    };
    return callWithSafeIterationClosing;
  }
  var hasRequiredEs_iterator_filter;
  function requireEs_iterator_filter() {
    if (hasRequiredEs_iterator_filter) return es_iterator_filter;
    hasRequiredEs_iterator_filter = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var createIteratorProxy = requireIteratorCreateProxy();
    var callWithSafeIterationClosing2 = requireCallWithSafeIterationClosing();
    var IS_PURE = requireIsPure();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperThrowsOnInvalidIterator2 = requireIteratorHelperThrowsOnInvalidIterator();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator2("filter", function() {
    });
    var filterWithoutClosingOnEarlyError = !IS_PURE && !FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR && iteratorHelperWithoutClosingOnEarlyError2("filter", TypeError);
    var FORCED = IS_PURE || FILTER_WITHOUT_THROWING_ON_INVALID_ITERATOR || filterWithoutClosingOnEarlyError;
    var IteratorProxy = createIteratorProxy(function() {
      var iterator = this.iterator;
      var predicate = this.predicate;
      var next = this.next;
      var result, done, value;
      while (true) {
        result = anObject2(call(next, iterator));
        done = this.done = !!result.done;
        if (done) return;
        value = result.value;
        if (callWithSafeIterationClosing2(iterator, predicate, [value, this.counter++], true)) return value;
      }
    });
    $({ target: "Iterator", proto: true, real: true, forced: FORCED }, {
      filter: function filter(predicate) {
        anObject2(this);
        try {
          aCallable2(predicate);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (filterWithoutClosingOnEarlyError) return call(filterWithoutClosingOnEarlyError, this, predicate);
        return new IteratorProxy(getIteratorDirect2(this), {
          predicate
        });
      }
    });
    return es_iterator_filter;
  }
  requireEs_iterator_filter();
  var es_iterator_find = {};
  var hasRequiredEs_iterator_find;
  function requireEs_iterator_find() {
    if (hasRequiredEs_iterator_find) return es_iterator_find;
    hasRequiredEs_iterator_find = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var iterate2 = requireIterate();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var findWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError2("find", TypeError);
    $({ target: "Iterator", proto: true, real: true, forced: findWithoutClosingOnEarlyError }, {
      find: function find(predicate) {
        anObject2(this);
        try {
          aCallable2(predicate);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (findWithoutClosingOnEarlyError) return call(findWithoutClosingOnEarlyError, this, predicate);
        var record = getIteratorDirect2(this);
        var counter = 0;
        return iterate2(record, function(value, stop) {
          if (predicate(value, counter++)) return stop(value);
        }, { IS_RECORD: true, INTERRUPTED: true }).result;
      }
    });
    return es_iterator_find;
  }
  requireEs_iterator_find();
  var es_iterator_flatMap = {};
  var getIteratorFlattenable;
  var hasRequiredGetIteratorFlattenable;
  function requireGetIteratorFlattenable() {
    if (hasRequiredGetIteratorFlattenable) return getIteratorFlattenable;
    hasRequiredGetIteratorFlattenable = 1;
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    getIteratorFlattenable = function(obj, stringHandling) {
      if (!stringHandling || typeof obj !== "string") anObject2(obj);
      var method = getIteratorMethod(obj);
      return getIteratorDirect2(anObject2(method !== void 0 ? call(method, obj) : obj));
    };
    return getIteratorFlattenable;
  }
  var hasRequiredEs_iterator_flatMap;
  function requireEs_iterator_flatMap() {
    if (hasRequiredEs_iterator_flatMap) return es_iterator_flatMap;
    hasRequiredEs_iterator_flatMap = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var getIteratorFlattenable2 = requireGetIteratorFlattenable();
    var createIteratorProxy = requireIteratorCreateProxy();
    var iteratorClose2 = requireIteratorClose();
    var fails2 = requireFails();
    var IS_PURE = requireIsPure();
    var iteratorHelperThrowsOnInvalidIterator2 = requireIteratorHelperThrowsOnInvalidIterator();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var THROWS_ON_ITERATOR_WITHOUT_RETURN = !IS_PURE && fails2(function() {
      return [1].values().flatMap(function() {
        return [1];
      }).find(function() {
        return true;
      }) !== 1;
    });
    var FLAT_MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !THROWS_ON_ITERATOR_WITHOUT_RETURN && !iteratorHelperThrowsOnInvalidIterator2("flatMap", function() {
    });
    var flatMapWithoutClosingOnEarlyError = !IS_PURE && !THROWS_ON_ITERATOR_WITHOUT_RETURN && !FLAT_MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR && iteratorHelperWithoutClosingOnEarlyError2("flatMap", TypeError);
    var FORCED = IS_PURE || THROWS_ON_ITERATOR_WITHOUT_RETURN || FLAT_MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR || flatMapWithoutClosingOnEarlyError;
    var IteratorProxy = createIteratorProxy(function() {
      var iterator = this.iterator;
      var mapper = this.mapper;
      var result, inner;
      while (true) {
        if (inner = this.inner) try {
          result = anObject2(call(inner.next, inner.iterator));
          if (!result.done) return result.value;
          this.inner = null;
        } catch (error) {
          iteratorClose2(iterator, "throw", error);
        }
        result = anObject2(call(this.next, iterator));
        if (this.done = !!result.done) return;
        try {
          this.inner = getIteratorFlattenable2(mapper(result.value, this.counter++), false);
        } catch (error) {
          iteratorClose2(iterator, "throw", error);
        }
      }
    });
    $({ target: "Iterator", proto: true, real: true, forced: FORCED }, {
      flatMap: function flatMap(mapper) {
        anObject2(this);
        try {
          aCallable2(mapper);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (flatMapWithoutClosingOnEarlyError) return call(flatMapWithoutClosingOnEarlyError, this, mapper);
        return new IteratorProxy(getIteratorDirect2(this), {
          mapper,
          inner: null
        });
      }
    });
    return es_iterator_flatMap;
  }
  requireEs_iterator_flatMap();
  var es_iterator_forEach = {};
  var hasRequiredEs_iterator_forEach;
  function requireEs_iterator_forEach() {
    if (hasRequiredEs_iterator_forEach) return es_iterator_forEach;
    hasRequiredEs_iterator_forEach = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var iterate2 = requireIterate();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var forEachWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError2("forEach", TypeError);
    $({ target: "Iterator", proto: true, real: true, forced: forEachWithoutClosingOnEarlyError }, {
      forEach: function forEach(fn) {
        anObject2(this);
        try {
          aCallable2(fn);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (forEachWithoutClosingOnEarlyError) return call(forEachWithoutClosingOnEarlyError, this, fn);
        var record = getIteratorDirect2(this);
        var counter = 0;
        iterate2(record, function(value) {
          fn(value, counter++);
        }, { IS_RECORD: true });
      }
    });
    return es_iterator_forEach;
  }
  requireEs_iterator_forEach();
  var es_iterator_from = {};
  var hasRequiredEs_iterator_from;
  function requireEs_iterator_from() {
    if (hasRequiredEs_iterator_from) return es_iterator_from;
    hasRequiredEs_iterator_from = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var toObject2 = requireToObject();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var IteratorPrototype = requireIteratorsCore().IteratorPrototype;
    var createIteratorProxy = requireIteratorCreateProxy();
    var getIteratorFlattenable2 = requireGetIteratorFlattenable();
    var IS_PURE = requireIsPure();
    var FORCED = IS_PURE || (function() {
      try {
        Iterator.from({ "return": null })["return"]();
      } catch (error) {
        return true;
      }
    })();
    var IteratorProxy = createIteratorProxy(function() {
      return call(this.next, this.iterator);
    }, true);
    $({ target: "Iterator", stat: true, forced: FORCED }, {
      from: function from(O) {
        var iteratorRecord = getIteratorFlattenable2(typeof O == "string" ? toObject2(O) : O, true);
        return isPrototypeOf(IteratorPrototype, iteratorRecord.iterator) ? iteratorRecord.iterator : new IteratorProxy(iteratorRecord);
      }
    });
    return es_iterator_from;
  }
  requireEs_iterator_from();
  var es_iterator_map = {};
  var hasRequiredEs_iterator_map;
  function requireEs_iterator_map() {
    if (hasRequiredEs_iterator_map) return es_iterator_map;
    hasRequiredEs_iterator_map = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var createIteratorProxy = requireIteratorCreateProxy();
    var callWithSafeIterationClosing2 = requireCallWithSafeIterationClosing();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperThrowsOnInvalidIterator2 = requireIteratorHelperThrowsOnInvalidIterator();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var IS_PURE = requireIsPure();
    var MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator2("map", function() {
    });
    var mapWithoutClosingOnEarlyError = !IS_PURE && !MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR && iteratorHelperWithoutClosingOnEarlyError2("map", TypeError);
    var FORCED = IS_PURE || MAP_WITHOUT_THROWING_ON_INVALID_ITERATOR || mapWithoutClosingOnEarlyError;
    var IteratorProxy = createIteratorProxy(function() {
      var iterator = this.iterator;
      var result = anObject2(call(this.next, iterator));
      var done = this.done = !!result.done;
      if (!done) return callWithSafeIterationClosing2(iterator, this.mapper, [result.value, this.counter++], true);
    });
    $({ target: "Iterator", proto: true, real: true, forced: FORCED }, {
      map: function map(mapper) {
        anObject2(this);
        try {
          aCallable2(mapper);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (mapWithoutClosingOnEarlyError) return call(mapWithoutClosingOnEarlyError, this, mapper);
        return new IteratorProxy(getIteratorDirect2(this), {
          mapper
        });
      }
    });
    return es_iterator_map;
  }
  requireEs_iterator_map();
  var es_iterator_reduce = {};
  var hasRequiredEs_iterator_reduce;
  function requireEs_iterator_reduce() {
    if (hasRequiredEs_iterator_reduce) return es_iterator_reduce;
    hasRequiredEs_iterator_reduce = 1;
    var $ = require_export();
    var iterate2 = requireIterate();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var apply2 = requireFunctionApply();
    var fails2 = requireFails();
    var $TypeError = TypeError;
    var FAILS_ON_INITIAL_UNDEFINED = fails2(function() {
      [].keys().reduce(function() {
      }, void 0);
    });
    var reduceWithoutClosingOnEarlyError = !FAILS_ON_INITIAL_UNDEFINED && iteratorHelperWithoutClosingOnEarlyError2("reduce", $TypeError);
    $({ target: "Iterator", proto: true, real: true, forced: FAILS_ON_INITIAL_UNDEFINED || reduceWithoutClosingOnEarlyError }, {
      reduce: function reduce(reducer) {
        anObject2(this);
        try {
          aCallable2(reducer);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        var noInitial = arguments.length < 2;
        var accumulator = noInitial ? void 0 : arguments[1];
        if (reduceWithoutClosingOnEarlyError) {
          return apply2(reduceWithoutClosingOnEarlyError, this, noInitial ? [reducer] : [reducer, accumulator]);
        }
        var record = getIteratorDirect2(this);
        var counter = 0;
        iterate2(record, function(value) {
          if (noInitial) {
            noInitial = false;
            accumulator = value;
          } else {
            accumulator = reducer(accumulator, value, counter);
          }
          counter++;
        }, { IS_RECORD: true });
        if (noInitial) throw new $TypeError("Reduce of empty iterator with no initial value");
        return accumulator;
      }
    });
    return es_iterator_reduce;
  }
  requireEs_iterator_reduce();
  var es_iterator_some = {};
  var hasRequiredEs_iterator_some;
  function requireEs_iterator_some() {
    if (hasRequiredEs_iterator_some) return es_iterator_some;
    hasRequiredEs_iterator_some = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var iterate2 = requireIterate();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var someWithoutClosingOnEarlyError = iteratorHelperWithoutClosingOnEarlyError2("some", TypeError);
    $({ target: "Iterator", proto: true, real: true, forced: someWithoutClosingOnEarlyError }, {
      some: function some(predicate) {
        anObject2(this);
        try {
          aCallable2(predicate);
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (someWithoutClosingOnEarlyError) return call(someWithoutClosingOnEarlyError, this, predicate);
        var record = getIteratorDirect2(this);
        var counter = 0;
        return iterate2(record, function(value, stop) {
          if (predicate(value, counter++)) return stop();
        }, { IS_RECORD: true, INTERRUPTED: true }).stopped;
      }
    });
    return es_iterator_some;
  }
  requireEs_iterator_some();
  var es_iterator_take = {};
  var hasRequiredEs_iterator_take;
  function requireEs_iterator_take() {
    if (hasRequiredEs_iterator_take) return es_iterator_take;
    hasRequiredEs_iterator_take = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var notANaN = requireNotANan();
    var toPositiveInteger2 = requireToPositiveInteger();
    var createIteratorProxy = requireIteratorCreateProxy();
    var iteratorClose2 = requireIteratorClose();
    var iteratorHelperThrowsOnInvalidIterator2 = requireIteratorHelperThrowsOnInvalidIterator();
    var iteratorHelperWithoutClosingOnEarlyError2 = requireIteratorHelperWithoutClosingOnEarlyError();
    var IS_PURE = requireIsPure();
    var $RangeError = RangeError;
    var $Infinity = Infinity;
    var TAKE_WITHOUT_THROWING_ON_INVALID_ITERATOR = !IS_PURE && !iteratorHelperThrowsOnInvalidIterator2("take", 1);
    var takeWithoutClosingOnEarlyError = !IS_PURE && !TAKE_WITHOUT_THROWING_ON_INVALID_ITERATOR && iteratorHelperWithoutClosingOnEarlyError2("take", RangeError);
    var FORCED = IS_PURE || TAKE_WITHOUT_THROWING_ON_INVALID_ITERATOR || takeWithoutClosingOnEarlyError || !(function() {
      try {
        Iterator.prototype.take.call({
          next: function() {
            return { done: true };
          }
        }, 9007199254740992);
      } catch (error) {
        return error instanceof $RangeError;
      }
    })();
    var IteratorProxy = createIteratorProxy(function() {
      var iterator = this.iterator;
      if (!this.remaining--) {
        this.done = true;
        return iteratorClose2(iterator, "normal", void 0);
      }
      var result = anObject2(call(this.next, iterator));
      var done = this.done = !!result.done;
      if (!done) return result.value;
    });
    $({ target: "Iterator", proto: true, real: true, forced: FORCED }, {
      take: function take(limit) {
        anObject2(this);
        var remaining;
        try {
          remaining = toPositiveInteger2(notANaN(+limit));
          if (remaining > 9007199254740991 && remaining !== $Infinity) {
            throw new $RangeError("The argument should be a safe integer");
          }
        } catch (error) {
          iteratorClose2(this, "throw", error);
        }
        if (takeWithoutClosingOnEarlyError) return call(takeWithoutClosingOnEarlyError, this, remaining);
        return new IteratorProxy(getIteratorDirect2(this), {
          remaining
        });
      }
    });
    return es_iterator_take;
  }
  requireEs_iterator_take();
  var es_iterator_toArray = {};
  var hasRequiredEs_iterator_toArray;
  function requireEs_iterator_toArray() {
    if (hasRequiredEs_iterator_toArray) return es_iterator_toArray;
    hasRequiredEs_iterator_toArray = 1;
    var $ = require_export();
    var anObject2 = requireAnObject();
    var createProperty2 = requireCreateProperty();
    var iterate2 = requireIterate();
    var getIteratorDirect2 = requireGetIteratorDirect();
    $({ target: "Iterator", proto: true, real: true }, {
      toArray: function toArray() {
        var result = [];
        var index = 0;
        iterate2(getIteratorDirect2(anObject2(this)), function(element) {
          createProperty2(result, index++, element);
        }, { IS_RECORD: true });
        return result;
      }
    });
    return es_iterator_toArray;
  }
  requireEs_iterator_toArray();
  var es_json_isRawJson = {};
  var nativeRawJson;
  var hasRequiredNativeRawJson;
  function requireNativeRawJson() {
    if (hasRequiredNativeRawJson) return nativeRawJson;
    hasRequiredNativeRawJson = 1;
    var fails2 = requireFails();
    nativeRawJson = !fails2(function() {
      var unsafeInt = "9007199254740993";
      var raw = JSON.rawJSON(unsafeInt);
      return !JSON.isRawJSON(raw) || JSON.stringify(raw) !== unsafeInt;
    });
    return nativeRawJson;
  }
  var isRawJson;
  var hasRequiredIsRawJson;
  function requireIsRawJson() {
    if (hasRequiredIsRawJson) return isRawJson;
    hasRequiredIsRawJson = 1;
    var isObject2 = requireIsObject();
    var getInternalState = requireInternalState().get;
    isRawJson = function isRawJSON(O) {
      if (!isObject2(O)) return false;
      var state = getInternalState(O);
      return !!state && state.type === "RawJSON";
    };
    return isRawJson;
  }
  var hasRequiredEs_json_isRawJson;
  function requireEs_json_isRawJson() {
    if (hasRequiredEs_json_isRawJson) return es_json_isRawJson;
    hasRequiredEs_json_isRawJson = 1;
    var $ = require_export();
    var NATIVE_RAW_JSON = requireNativeRawJson();
    var isRawJSON = requireIsRawJson();
    $({ target: "JSON", stat: true, forced: !NATIVE_RAW_JSON }, {
      isRawJSON
    });
    return es_json_isRawJson;
  }
  requireEs_json_isRawJson();
  var es_json_parse = {};
  var parseJsonString;
  var hasRequiredParseJsonString;
  function requireParseJsonString() {
    if (hasRequiredParseJsonString) return parseJsonString;
    hasRequiredParseJsonString = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var hasOwn = requireHasOwnProperty();
    var $SyntaxError = SyntaxError;
    var $parseInt = parseInt;
    var fromCharCode = String.fromCharCode;
    var at = uncurryThis("".charAt);
    var slice = uncurryThis("".slice);
    var exec = uncurryThis(/./.exec);
    var codePoints = {
      '\\"': '"',
      "\\\\": "\\",
      "\\/": "/",
      "\\b": "\b",
      "\\f": "\f",
      "\\n": "\n",
      "\\r": "\r",
      "\\t": "	"
    };
    var IS_4_HEX_DIGITS = /^[\da-f]{4}$/i;
    var IS_C0_CONTROL_CODE = /^[\u0000-\u001F]$/;
    parseJsonString = function(source, i) {
      var unterminated = true;
      var value = "";
      while (i < source.length) {
        var chr = at(source, i);
        if (chr === "\\") {
          var twoChars = slice(source, i, i + 2);
          if (hasOwn(codePoints, twoChars)) {
            value += codePoints[twoChars];
            i += 2;
          } else if (twoChars === "\\u") {
            i += 2;
            var fourHexDigits = slice(source, i, i + 4);
            if (!exec(IS_4_HEX_DIGITS, fourHexDigits)) throw new $SyntaxError("Bad Unicode escape at: " + i);
            value += fromCharCode($parseInt(fourHexDigits, 16));
            i += 4;
          } else throw new $SyntaxError('Unknown escape sequence: "' + twoChars + '"');
        } else if (chr === '"') {
          unterminated = false;
          i++;
          break;
        } else {
          if (exec(IS_C0_CONTROL_CODE, chr)) throw new $SyntaxError("Bad control character in string literal at: " + i);
          value += chr;
          i++;
        }
      }
      if (unterminated) throw new $SyntaxError("Unterminated string at: " + i);
      return { value, end: i };
    };
    return parseJsonString;
  }
  var hasRequiredEs_json_parse;
  function requireEs_json_parse() {
    if (hasRequiredEs_json_parse) return es_json_parse;
    hasRequiredEs_json_parse = 1;
    var $ = require_export();
    var DESCRIPTORS = requireDescriptors();
    var globalThis2 = requireGlobalThis();
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var call = requireFunctionCall();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var isArray2 = requireIsArray();
    var hasOwn = requireHasOwnProperty();
    var toString2 = requireToString();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var createProperty2 = requireCreateProperty();
    var fails2 = requireFails();
    var parseJSONString = requireParseJsonString();
    var NATIVE_SYMBOL = requireSymbolConstructorDetection();
    var JSON2 = globalThis2.JSON;
    var Number2 = globalThis2.Number;
    var SyntaxError2 = globalThis2.SyntaxError;
    var nativeParse = JSON2 && JSON2.parse;
    var enumerableOwnProperties = getBuiltIn2("Object", "keys");
    var getOwnPropertyDescriptor2 = Object.getOwnPropertyDescriptor;
    var at = uncurryThis("".charAt);
    var slice = uncurryThis("".slice);
    var exec = uncurryThis(/./.exec);
    var push = uncurryThis([].push);
    var IS_DIGIT = /^\d$/;
    var IS_NON_ZERO_DIGIT = /^[1-9]$/;
    var IS_NUMBER_START = /^[\d-]$/;
    var IS_WHITESPACE = /^[\t\n\r ]$/;
    var PRIMITIVE = 0;
    var OBJECT = 1;
    var $parse = function(source, reviver) {
      source = toString2(source);
      var context = new Context(source, 0);
      var root = context.parse();
      var value = root.value;
      var endIndex = context.skip(IS_WHITESPACE, root.end);
      if (endIndex < source.length) {
        throw new SyntaxError2('Unexpected extra character: "' + at(source, endIndex) + '" after the parsed data at: ' + endIndex);
      }
      return isCallable2(reviver) ? internalize({ "": value }, "", reviver, root) : value;
    };
    var internalize = function(holder, name, reviver, node) {
      var val = holder[name];
      var unmodified = node && val === node.value;
      var context = unmodified && typeof node.source == "string" ? { source: node.source } : {};
      var elementRecordsLen, keys, len, i, P;
      if (isObject2(val)) {
        var nodeIsArray = isArray2(val);
        var nodes = unmodified ? node.nodes : nodeIsArray ? [] : {};
        if (nodeIsArray) {
          elementRecordsLen = nodes.length;
          len = lengthOfArrayLike2(val);
          for (i = 0; i < len; i++) {
            internalizeProperty(val, i, internalize(val, "" + i, reviver, i < elementRecordsLen ? nodes[i] : void 0));
          }
        } else {
          keys = enumerableOwnProperties(val);
          len = lengthOfArrayLike2(keys);
          for (i = 0; i < len; i++) {
            P = keys[i];
            internalizeProperty(val, P, internalize(val, P, reviver, hasOwn(nodes, P) ? nodes[P] : void 0));
          }
        }
      }
      return call(reviver, holder, name, val, context);
    };
    var internalizeProperty = function(object, key, value) {
      if (DESCRIPTORS) {
        var descriptor = getOwnPropertyDescriptor2(object, key);
        if (descriptor && !descriptor.configurable) return;
      }
      if (value === void 0) delete object[key];
      else createProperty2(object, key, value);
    };
    var Node = function(value, end, source, nodes) {
      this.value = value;
      this.end = end;
      this.source = source;
      this.nodes = nodes;
    };
    var Context = function(source, index) {
      this.source = source;
      this.index = index;
    };
    Context.prototype = {
      fork: function(nextIndex) {
        return new Context(this.source, nextIndex);
      },
      parse: function() {
        var source = this.source;
        var i = this.skip(IS_WHITESPACE, this.index);
        var fork = this.fork(i);
        var chr = at(source, i);
        if (exec(IS_NUMBER_START, chr)) return fork.number();
        switch (chr) {
          case "{":
            return fork.object();
          case "[":
            return fork.array();
          case '"':
            return fork.string();
          case "t":
            return fork.keyword(true);
          case "f":
            return fork.keyword(false);
          case "n":
            return fork.keyword(null);
        }
        throw new SyntaxError2('Unexpected character: "' + chr + '" at: ' + i);
      },
      node: function(type, value, start, end, nodes) {
        return new Node(value, end, type ? null : slice(this.source, start, end), nodes);
      },
      object: function() {
        var source = this.source;
        var i = this.index + 1;
        var expectKeypair = false;
        var object = {};
        var nodes = {};
        var closed = false;
        while (i < source.length) {
          i = this.until(['"', "}"], i);
          if (at(source, i) === "}" && !expectKeypair) {
            i++;
            closed = true;
            break;
          }
          var result = this.fork(i).string();
          var key = result.value;
          i = result.end;
          i = this.until([":"], i) + 1;
          i = this.skip(IS_WHITESPACE, i);
          result = this.fork(i).parse();
          createProperty2(nodes, key, result);
          createProperty2(object, key, result.value);
          i = this.until([",", "}"], result.end);
          var chr = at(source, i);
          if (chr === ",") {
            expectKeypair = true;
            i++;
          } else if (chr === "}") {
            i++;
            closed = true;
            break;
          }
        }
        if (!closed) throw new SyntaxError2("Unterminated object at: " + i);
        return this.node(OBJECT, object, this.index, i, nodes);
      },
      array: function() {
        var source = this.source;
        var i = this.index + 1;
        var expectElement = false;
        var array = [];
        var nodes = [];
        var closed = false;
        while (i < source.length) {
          i = this.skip(IS_WHITESPACE, i);
          if (at(source, i) === "]" && !expectElement) {
            i++;
            closed = true;
            break;
          }
          var result = this.fork(i).parse();
          push(nodes, result);
          push(array, result.value);
          i = this.until([",", "]"], result.end);
          if (at(source, i) === ",") {
            expectElement = true;
            i++;
          } else if (at(source, i) === "]") {
            i++;
            closed = true;
            break;
          }
        }
        if (!closed) throw new SyntaxError2("Unterminated array at: " + i);
        return this.node(OBJECT, array, this.index, i, nodes);
      },
      string: function() {
        var index = this.index;
        var parsed = parseJSONString(this.source, this.index + 1);
        return this.node(PRIMITIVE, parsed.value, index, parsed.end);
      },
      number: function() {
        var source = this.source;
        var startIndex = this.index;
        var i = startIndex;
        if (at(source, i) === "-") i++;
        if (at(source, i) === "0") i++;
        else if (exec(IS_NON_ZERO_DIGIT, at(source, i))) i = this.skip(IS_DIGIT, i + 1);
        else throw new SyntaxError2("Failed to parse number at: " + i);
        if (at(source, i) === ".") {
          var fractionStartIndex = i + 1;
          i = this.skip(IS_DIGIT, fractionStartIndex);
          if (fractionStartIndex === i) throw new SyntaxError2("Failed to parse number's fraction at: " + i);
        }
        if (at(source, i) === "e" || at(source, i) === "E") {
          i++;
          if (at(source, i) === "+" || at(source, i) === "-") i++;
          var exponentStartIndex = i;
          i = this.skip(IS_DIGIT, i);
          if (exponentStartIndex === i) throw new SyntaxError2("Failed to parse number's exponent value at: " + i);
        }
        return this.node(PRIMITIVE, Number2(slice(source, startIndex, i)), startIndex, i);
      },
      keyword: function(value) {
        var keyword = "" + value;
        var index = this.index;
        var endIndex = index + keyword.length;
        if (slice(this.source, index, endIndex) !== keyword) throw new SyntaxError2("Failed to parse value at: " + index);
        return this.node(PRIMITIVE, value, index, endIndex);
      },
      skip: function(regex, i) {
        var source = this.source;
        for (; i < source.length; i++) if (!exec(regex, at(source, i))) break;
        return i;
      },
      until: function(array, i) {
        i = this.skip(IS_WHITESPACE, i);
        var chr = at(this.source, i);
        for (var j = 0; j < array.length; j++) if (array[j] === chr) return i;
        throw new SyntaxError2('Unexpected character: "' + chr + '" at: ' + i);
      }
    };
    var NO_SOURCE_SUPPORT = fails2(function() {
      var unsafeInt = "9007199254740993";
      var source;
      nativeParse(unsafeInt, function(key, value, context) {
        source = context.source;
      });
      return source !== unsafeInt;
    });
    var PROPER_BASE_PARSE = NATIVE_SYMBOL && !fails2(function() {
      return 1 / nativeParse("-0 	") !== -Infinity;
    });
    $({ target: "JSON", stat: true, forced: NO_SOURCE_SUPPORT }, {
      parse: function parse(text2, reviver) {
        return PROPER_BASE_PARSE && !isCallable2(reviver) ? nativeParse(text2) : $parse(text2, reviver);
      }
    });
    return es_json_parse;
  }
  requireEs_json_parse();
  var es_json_rawJson = {};
  var freezing;
  var hasRequiredFreezing;
  function requireFreezing() {
    if (hasRequiredFreezing) return freezing;
    hasRequiredFreezing = 1;
    var fails2 = requireFails();
    freezing = !fails2(function() {
      return Object.isExtensible(Object.preventExtensions({}));
    });
    return freezing;
  }
  var hasRequiredEs_json_rawJson;
  function requireEs_json_rawJson() {
    if (hasRequiredEs_json_rawJson) return es_json_rawJson;
    hasRequiredEs_json_rawJson = 1;
    var $ = require_export();
    var FREEZING = requireFreezing();
    var NATIVE_RAW_JSON = requireNativeRawJson();
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var toString2 = requireToString();
    var createProperty2 = requireCreateProperty();
    var setInternalState = requireInternalState().set;
    var $SyntaxError = SyntaxError;
    var parse = getBuiltIn2("JSON", "parse");
    var create2 = getBuiltIn2("Object", "create");
    var freeze2 = getBuiltIn2("Object", "freeze");
    var at = uncurryThis("".charAt);
    var ERROR_MESSAGE = "Unacceptable as raw JSON";
    var isWhitespace = function(it) {
      return it === " " || it === "	" || it === "\n" || it === "\r";
    };
    $({ target: "JSON", stat: true, forced: !NATIVE_RAW_JSON }, {
      rawJSON: function rawJSON(text2) {
        var jsonString = toString2(text2);
        if (jsonString === "" || isWhitespace(at(jsonString, 0)) || isWhitespace(at(jsonString, jsonString.length - 1))) {
          throw new $SyntaxError(ERROR_MESSAGE);
        }
        var parsed = parse(jsonString);
        if (typeof parsed == "object" && parsed !== null) throw new $SyntaxError(ERROR_MESSAGE);
        var obj = create2(null);
        setInternalState(obj, { type: "RawJSON" });
        createProperty2(obj, "rawJSON", jsonString);
        return FREEZING ? freeze2(obj) : obj;
      }
    });
    return es_json_rawJson;
  }
  requireEs_json_rawJson();
  var es_json_stringify = {};
  var thisNumberValue;
  var hasRequiredThisNumberValue;
  function requireThisNumberValue() {
    if (hasRequiredThisNumberValue) return thisNumberValue;
    hasRequiredThisNumberValue = 1;
    var uncurryThis = requireFunctionUncurryThis();
    thisNumberValue = uncurryThis(1.1.valueOf);
    return thisNumberValue;
  }
  var hasRequiredEs_json_stringify;
  function requireEs_json_stringify() {
    if (hasRequiredEs_json_stringify) return es_json_stringify;
    hasRequiredEs_json_stringify = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var isArray2 = requireIsArray();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var create2 = requireObjectCreate();
    var isRawJSON = requireIsRawJson();
    var isSymbol2 = requireIsSymbol();
    var classof2 = requireClassofRaw();
    var thisNumberValue2 = requireThisNumberValue();
    var includes = requireArrayIncludes().includes;
    var hasOwn = requireHasOwnProperty();
    var toString2 = requireToString();
    var parseJSONString = requireParseJsonString();
    var uid2 = requireUid();
    var NATIVE_SYMBOL = requireSymbolConstructorDetection();
    var NATIVE_RAW_JSON = requireNativeRawJson();
    var $String = String;
    var $TypeError = TypeError;
    var $stringify = getBuiltIn2("JSON", "stringify");
    var $BigInt = getBuiltIn2("BigInt");
    var stringValueOf = uncurryThis("".valueOf);
    var booleanValueOf = uncurryThis(true.valueOf);
    var bigIntValueOf = $BigInt && uncurryThis($BigInt.prototype.valueOf);
    var exec = uncurryThis(/./.exec);
    var charAt = uncurryThis("".charAt);
    var charCodeAt = uncurryThis("".charCodeAt);
    var replace = uncurryThis("".replace);
    var slice = uncurryThis("".slice);
    var push = uncurryThis([].push);
    var pop = uncurryThis([].pop);
    var numberToString2 = uncurryThis(1.1.toString);
    var surrogates = /[\uD800-\uDFFF]/g;
    var leadingSurrogates = /^[\uD800-\uDBFF]$/;
    var trailingSurrogates = /^[\uDC00-\uDFFF]$/;
    var digits = /^\d+$/;
    var RAW_MARK = uid2();
    var KEY_MARK = uid2();
    var END_MARK = uid2();
    var RAW_MARK_LENGTH = RAW_MARK.length;
    var KEY_MARK_LENGTH = KEY_MARK.length;
    var WRONG_SYMBOLS_CONVERSION = !NATIVE_SYMBOL || fails2(function() {
      var symbol = getBuiltIn2("Symbol")("stringify detection");
      return $stringify([symbol]) !== "[null]" || $stringify({ a: symbol }) !== "{}" || $stringify(Object(symbol)) !== "{}";
    });
    var ILL_FORMED_UNICODE = fails2(function() {
      return $stringify("\uDF06\uD834") !== '"\\udf06\\ud834"' || $stringify("\uDEAD") !== '"\\udead"';
    });
    var isRawJSONValue = NATIVE_RAW_JSON ? getBuiltIn2("JSON", "isRawJSON") : isRawJSON;
    var stringifyWithProperSymbolsConversion = WRONG_SYMBOLS_CONVERSION ? function(it, replacer, space) {
      return $stringify(it, function(key, value) {
        var replaced = call(replacer, this, key, value);
        if (!isSymbol2(replaced)) return replaced;
      }, space);
    } : $stringify;
    var fixIllFormedJSON = function(match, offset, string) {
      var prev = charAt(string, offset - 1);
      var next = charAt(string, offset + 1);
      if (exec(leadingSurrogates, match) && !exec(trailingSurrogates, next) || exec(trailingSurrogates, match) && !exec(leadingSurrogates, prev)) {
        return "\\u" + numberToString2(charCodeAt(match, 0), 16);
      }
      return match;
    };
    var getPropertyList = function(replacer) {
      if (!isArray2(replacer)) return;
      var rawLength = replacer.length;
      var propertyList = [];
      var addedKeys = create2(null);
      for (var i = 0; i < rawLength; i++) {
        var element = replacer[i];
        var key;
        if (typeof element == "string") key = element;
        else if (typeof element == "number" || classof2(element) === "Number" || classof2(element) === "String") key = toString2(element);
        else continue;
        if (!hasOwn(addedKeys, key)) {
          addedKeys[key] = true;
          push(propertyList, key);
        }
      }
      return propertyList;
    };
    var hasInternalSlot = function(valueOf, it) {
      try {
        valueOf(it);
        return true;
      } catch (error) {
        return false;
      }
    };
    var isBoxedPrimitive = function(it) {
      var kind = classof2(it);
      return kind === "Number" && hasInternalSlot(thisNumberValue2, it) || kind === "String" && hasInternalSlot(stringValueOf, it) || kind === "Boolean" && hasInternalSlot(booleanValueOf, it) || !!bigIntValueOf && kind === "BigInt" && hasInternalSlot(bigIntValueOf, it);
    };
    var isSerializedAsObject = function(it) {
      if (!isObject2(it) || isCallable2(it) || isArray2(it)) return false;
      try {
        return !isBoxedPrimitive(it);
      } catch (error) {
        return true;
      }
    };
    var createElementHolder = function(holder, key) {
      return {
        toJSON: function() {
          var element = holder[key];
          if (isObject2(element) || typeof element == "bigint") {
            var elementToJSON = element.toJSON;
            if (isCallable2(elementToJSON)) element = call(elementToJSON, element, key);
          }
          return element;
        }
      };
    };
    var getKeyPrefix = function(propertyList) {
      for (var i = 0, length = propertyList.length; i < length; i++) {
        if (exec(digits, propertyList[i])) return KEY_MARK;
      }
      return "";
    };
    var createOrderedObject = function(value, propertyList, keyPrefix) {
      var ordered = create2(null);
      for (var i = 0, length = propertyList.length; i < length; i++) {
        var key = propertyList[i];
        ordered[keyPrefix + key] = createElementHolder(value, key);
      }
      ordered[END_MARK] = null;
      return ordered;
    };
    if ($stringify) $({ target: "JSON", stat: true, arity: 3, forced: WRONG_SYMBOLS_CONVERSION || ILL_FORMED_UNICODE || !NATIVE_RAW_JSON }, {
      stringify: function stringify(text2, replacer, space) {
        var replacerFunction = isCallable2(replacer) ? replacer : void 0;
        var propertyList = replacerFunction ? void 0 : getPropertyList(replacer);
        var keyPrefix = propertyList && getKeyPrefix(propertyList);
        var rawStrings = [];
        var openObjects = [];
        var parentOrdered = [];
        var currentOrdered;
        var marked = false;
        var root = true;
        var json = stringifyWithProperSymbolsConversion(text2, function(key, value) {
          key = $String(key);
          if (propertyList) {
            if (key === END_MARK) {
              pop(openObjects);
              currentOrdered = pop(parentOrdered);
              return;
            }
            if (root) root = false;
            else if (this !== currentOrdered && !isArray2(this) && !includes(propertyList, key)) return;
          } else if (replacerFunction) value = call(replacerFunction, this, key, value);
          if (isRawJSONValue(value)) {
            if (NATIVE_RAW_JSON) return value;
            marked = true;
            return RAW_MARK + (push(rawStrings, value.rawJSON) - 1);
          }
          if (propertyList && isSerializedAsObject(value)) {
            if (includes(openObjects, value)) throw new $TypeError("Converting circular structure to JSON");
            var ordered = createOrderedObject(value, propertyList, keyPrefix);
            push(openObjects, value);
            push(parentOrdered, currentOrdered);
            currentOrdered = ordered;
            if (keyPrefix) marked = true;
            return ordered;
          }
          return value;
        }, space);
        if (typeof json != "string") return json;
        if (ILL_FORMED_UNICODE) json = replace(json, surrogates, fixIllFormedJSON);
        if (!marked) return json;
        var result = "";
        var length = json.length;
        for (var i = 0; i < length; i++) {
          var chr = charAt(json, i);
          if (chr === '"') {
            var end = parseJSONString(json, ++i).end - 1;
            var string = slice(json, i, end);
            if (slice(string, 0, RAW_MARK_LENGTH) === RAW_MARK) result += rawStrings[slice(string, RAW_MARK_LENGTH)];
            else if (slice(string, 0, KEY_MARK_LENGTH) === KEY_MARK) result += '"' + slice(string, KEY_MARK_LENGTH) + '"';
            else result += '"' + string + '"';
            i = end;
          } else result += chr;
        }
        return result;
      }
    });
    return es_json_stringify;
  }
  requireEs_json_stringify();
  var es_map_groupBy = {};
  var mapHelpers;
  var hasRequiredMapHelpers;
  function requireMapHelpers() {
    if (hasRequiredMapHelpers) return mapHelpers;
    hasRequiredMapHelpers = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var MapPrototype = Map.prototype;
    mapHelpers = {
      // eslint-disable-next-line es/no-map -- safe
      Map,
      set: uncurryThis(MapPrototype.set),
      get: uncurryThis(MapPrototype.get),
      has: uncurryThis(MapPrototype.has),
      remove: uncurryThis(MapPrototype["delete"]),
      proto: MapPrototype
    };
    return mapHelpers;
  }
  var hasRequiredEs_map_groupBy;
  function requireEs_map_groupBy() {
    if (hasRequiredEs_map_groupBy) return es_map_groupBy;
    hasRequiredEs_map_groupBy = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var iterate2 = requireIterate();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var MapHelpers = requireMapHelpers();
    var IS_PURE = requireIsPure();
    var fails2 = requireFails();
    var Map2 = MapHelpers.Map;
    var has = MapHelpers.has;
    var get = MapHelpers.get;
    var set = MapHelpers.set;
    var push = uncurryThis([].push);
    var DOES_NOT_WORK_WITH_PRIMITIVES = IS_PURE || fails2(function() {
      return Map2.groupBy("ab", function(it) {
        return it;
      }).get("a").length !== 1;
    });
    $({ target: "Map", stat: true, forced: IS_PURE || DOES_NOT_WORK_WITH_PRIMITIVES }, {
      groupBy: function groupBy(items, callbackfn) {
        requireObjectCoercible2(items);
        aCallable2(callbackfn);
        var map = new Map2();
        var k = 0;
        iterate2(items, function(value) {
          doesNotExceedSafeInteger2(k);
          var key = callbackfn(value, k++);
          if (!has(map, key)) set(map, key, [value]);
          else push(get(map, key), value);
        });
        return map;
      }
    });
    return es_map_groupBy;
  }
  requireEs_map_groupBy();
  var es_map_getOrInsert = {};
  var hasRequiredEs_map_getOrInsert;
  function requireEs_map_getOrInsert() {
    if (hasRequiredEs_map_getOrInsert) return es_map_getOrInsert;
    hasRequiredEs_map_getOrInsert = 1;
    var $ = require_export();
    var MapHelpers = requireMapHelpers();
    var IS_PURE = requireIsPure();
    var get = MapHelpers.get;
    var has = MapHelpers.has;
    var set = MapHelpers.set;
    $({ target: "Map", proto: true, real: true, forced: IS_PURE }, {
      getOrInsert: function getOrInsert(key, value) {
        if (has(this, key)) return get(this, key);
        set(this, key, value);
        return value;
      }
    });
    return es_map_getOrInsert;
  }
  requireEs_map_getOrInsert();
  var es_map_getOrInsertComputed = {};
  var hasRequiredEs_map_getOrInsertComputed;
  function requireEs_map_getOrInsertComputed() {
    if (hasRequiredEs_map_getOrInsertComputed) return es_map_getOrInsertComputed;
    hasRequiredEs_map_getOrInsertComputed = 1;
    var $ = require_export();
    var aCallable2 = requireACallable();
    var MapHelpers = requireMapHelpers();
    var IS_PURE = requireIsPure();
    var get = MapHelpers.get;
    var has = MapHelpers.has;
    var set = MapHelpers.set;
    $({ target: "Map", proto: true, real: true, forced: IS_PURE }, {
      getOrInsertComputed: function getOrInsertComputed(key, callbackfn) {
        var hasKey = has(this, key);
        aCallable2(callbackfn);
        if (hasKey) return get(this, key);
        if (key === 0 && 1 / key === -Infinity) key = 0;
        var value = callbackfn(key);
        set(this, key, value);
        return value;
      }
    });
    return es_map_getOrInsertComputed;
  }
  requireEs_map_getOrInsertComputed();
  var es_math_f16round = {};
  var hasRequiredEs_math_f16round;
  function requireEs_math_f16round() {
    if (hasRequiredEs_math_f16round) return es_math_f16round;
    hasRequiredEs_math_f16round = 1;
    var $ = require_export();
    var floatRound = requireMathFloatRound();
    var FLOAT16_EPSILON = 9765625e-10;
    var FLOAT16_MAX_VALUE = 65504;
    var FLOAT16_MIN_VALUE = 6103515625e-14;
    $({ target: "Math", stat: true }, {
      f16round: function f16round(x) {
        return floatRound(x, FLOAT16_EPSILON, FLOAT16_MAX_VALUE, FLOAT16_MIN_VALUE);
      }
    });
    return es_math_f16round;
  }
  requireEs_math_f16round();
  var es_math_hypot = {};
  var hasRequiredEs_math_hypot;
  function requireEs_math_hypot() {
    if (hasRequiredEs_math_hypot) return es_math_hypot;
    hasRequiredEs_math_hypot = 1;
    var $ = require_export();
    var $hypot = Math.hypot;
    var abs = Math.abs;
    var sqrt = Math.sqrt;
    var FORCED = !!$hypot && $hypot(Infinity, NaN) !== Infinity;
    $({ target: "Math", stat: true, arity: 2, forced: FORCED }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      hypot: function hypot(value1, value2) {
        var sum = 0;
        var i = 0;
        var aLen = arguments.length;
        var larg = 0;
        var arg, div;
        while (i < aLen) {
          arg = abs(arguments[i++]);
          if (larg < arg) {
            div = larg / arg;
            sum = sum * div * div + 1;
            larg = arg;
          } else if (arg > 0) {
            div = arg / larg;
            sum += div * div;
          } else sum += arg;
        }
        return larg === Infinity ? Infinity : larg * sqrt(sum);
      }
    });
    return es_math_hypot;
  }
  requireEs_math_hypot();
  var es_math_sumPrecise = {};
  var hasRequiredEs_math_sumPrecise;
  function requireEs_math_sumPrecise() {
    if (hasRequiredEs_math_sumPrecise) return es_math_sumPrecise;
    hasRequiredEs_math_sumPrecise = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var iterate2 = requireIterate();
    var $RangeError = RangeError;
    var $TypeError = TypeError;
    var $Infinity = Infinity;
    var $NaN = NaN;
    var abs = Math.abs;
    var pow = Math.pow;
    var push = uncurryThis([].push);
    var POW_2_1023 = pow(2, 1023);
    var MAX_SAFE_INTEGER = pow(2, 53) - 1;
    var MAX_DOUBLE = Number.MAX_VALUE;
    var MAX_ULP = pow(2, 971);
    var NOT_A_NUMBER = {};
    var MINUS_INFINITY = {};
    var PLUS_INFINITY = {};
    var MINUS_ZERO = {};
    var FINITE = {};
    var twosum = function(x, y) {
      var hi = x + y;
      var lo = y - (hi - x);
      return { hi, lo };
    };
    $({ target: "Math", stat: true }, {
      // eslint-disable-next-line max-statements -- ok
      sumPrecise: function sumPrecise(items) {
        var numbers = [];
        var count = 0;
        var state = MINUS_ZERO;
        iterate2(items, function(n2) {
          if (++count > MAX_SAFE_INTEGER) throw new $RangeError("Maximum allowed index exceeded");
          if (typeof n2 != "number") throw new $TypeError("Value is not a number");
          if (state !== NOT_A_NUMBER) {
            if (n2 !== n2) state = NOT_A_NUMBER;
            else if (n2 === $Infinity) state = state === MINUS_INFINITY ? NOT_A_NUMBER : PLUS_INFINITY;
            else if (n2 === -$Infinity) state = state === PLUS_INFINITY ? NOT_A_NUMBER : MINUS_INFINITY;
            else if ((n2 !== 0 || 1 / n2 === $Infinity) && (state === MINUS_ZERO || state === FINITE)) {
              state = FINITE;
              push(numbers, n2);
            }
          }
        });
        switch (state) {
          case NOT_A_NUMBER:
            return $NaN;
          case MINUS_INFINITY:
            return -$Infinity;
          case PLUS_INFINITY:
            return $Infinity;
          case MINUS_ZERO:
            return -0;
        }
        var partials = [];
        var overflow = 0;
        var x, y, sum, hi, lo, tmp;
        for (var i = 0; i < numbers.length; i++) {
          x = numbers[i];
          var actuallyUsedPartials = 0;
          for (var j = 0; j < partials.length; j++) {
            y = partials[j];
            if (abs(x) < abs(y)) {
              tmp = x;
              x = y;
              y = tmp;
            }
            sum = twosum(x, y);
            hi = sum.hi;
            lo = sum.lo;
            if (abs(hi) === $Infinity) {
              var sign = hi === $Infinity ? 1 : -1;
              overflow += sign;
              x = x - sign * POW_2_1023 - sign * POW_2_1023;
              if (abs(x) < abs(y)) {
                tmp = x;
                x = y;
                y = tmp;
              }
              sum = twosum(x, y);
              hi = sum.hi;
              lo = sum.lo;
            }
            if (lo !== 0) partials[actuallyUsedPartials++] = lo;
            x = hi;
          }
          partials.length = actuallyUsedPartials;
          if (x !== 0) push(partials, x);
        }
        var n = partials.length - 1;
        hi = 0;
        lo = 0;
        if (overflow !== 0) {
          var next = n >= 0 ? partials[n] : 0;
          n--;
          if (abs(overflow) > 1 || overflow > 0 && next > 0 || overflow < 0 && next < 0) {
            return overflow > 0 ? $Infinity : -$Infinity;
          }
          sum = twosum(overflow * POW_2_1023, next / 2);
          hi = sum.hi;
          lo = sum.lo;
          lo *= 2;
          if (abs(2 * hi) === $Infinity) {
            if (hi > 0) {
              return hi === POW_2_1023 && lo === -(MAX_ULP / 2) && n >= 0 && partials[n] < 0 ? MAX_DOUBLE : $Infinity;
            }
            return hi === -POW_2_1023 && lo === MAX_ULP / 2 && n >= 0 && partials[n] > 0 ? -MAX_DOUBLE : -$Infinity;
          }
          if (lo !== 0) {
            partials[++n] = lo;
            lo = 0;
          }
          hi *= 2;
        }
        while (n >= 0) {
          sum = twosum(hi, partials[n--]);
          hi = sum.hi;
          lo = sum.lo;
          if (lo !== 0) break;
        }
        if (n >= 0 && (lo < 0 && partials[n] < 0 || lo > 0 && partials[n] > 0)) {
          y = lo * 2;
          x = hi + y;
          if (y === x - hi) hi = x;
        }
        return hi;
      }
    });
    return es_math_sumPrecise;
  }
  requireEs_math_sumPrecise();
  var es_object_fromEntries = {};
  var hasRequiredEs_object_fromEntries;
  function requireEs_object_fromEntries() {
    if (hasRequiredEs_object_fromEntries) return es_object_fromEntries;
    hasRequiredEs_object_fromEntries = 1;
    var $ = require_export();
    var iterate2 = requireIterate();
    var createProperty2 = requireCreateProperty();
    $({ target: "Object", stat: true }, {
      fromEntries: function fromEntries(iterable) {
        var obj = {};
        iterate2(iterable, function(k, v) {
          createProperty2(obj, k, v);
        }, { AS_ENTRIES: true });
        return obj;
      }
    });
    return es_object_fromEntries;
  }
  requireEs_object_fromEntries();
  var es_object_groupBy = {};
  var hasRequiredEs_object_groupBy;
  function requireEs_object_groupBy() {
    if (hasRequiredEs_object_groupBy) return es_object_groupBy;
    hasRequiredEs_object_groupBy = 1;
    var $ = require_export();
    var createProperty2 = requireCreateProperty();
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toPropertyKey2 = requireToPropertyKey();
    var iterate2 = requireIterate();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var fails2 = requireFails();
    var nativeGroupBy = Object.groupBy;
    var create2 = getBuiltIn2("Object", "create");
    var push = uncurryThis([].push);
    var DOES_NOT_WORK_WITH_PRIMITIVES = !nativeGroupBy || fails2(function() {
      return nativeGroupBy("ab", function(it) {
        return it;
      }).a.length !== 1;
    });
    $({ target: "Object", stat: true, forced: DOES_NOT_WORK_WITH_PRIMITIVES }, {
      groupBy: function groupBy(items, callbackfn) {
        requireObjectCoercible2(items);
        aCallable2(callbackfn);
        var obj = create2(null);
        var k = 0;
        iterate2(items, function(value) {
          doesNotExceedSafeInteger2(k);
          var key = toPropertyKey2(callbackfn(value, k++));
          if (key in obj) push(obj[key], value);
          else createProperty2(obj, key, [value]);
        });
        return obj;
      }
    });
    return es_object_groupBy;
  }
  requireEs_object_groupBy();
  var es_object_hasOwn = {};
  var hasRequiredEs_object_hasOwn;
  function requireEs_object_hasOwn() {
    if (hasRequiredEs_object_hasOwn) return es_object_hasOwn;
    hasRequiredEs_object_hasOwn = 1;
    var $ = require_export();
    var hasOwn = requireHasOwnProperty();
    $({ target: "Object", stat: true }, {
      hasOwn
    });
    return es_object_hasOwn;
  }
  requireEs_object_hasOwn();
  var es_promise_allSettled = {};
  var newPromiseCapability = {};
  var hasRequiredNewPromiseCapability;
  function requireNewPromiseCapability() {
    if (hasRequiredNewPromiseCapability) return newPromiseCapability;
    hasRequiredNewPromiseCapability = 1;
    var aCallable2 = requireACallable();
    var $TypeError = TypeError;
    var PromiseCapability = function(C) {
      var resolve, reject;
      this.promise = new C(function($$resolve, $$reject) {
        if (resolve !== void 0 || reject !== void 0) throw new $TypeError("Bad Promise constructor");
        resolve = $$resolve;
        reject = $$reject;
      });
      this.resolve = aCallable2(resolve);
      this.reject = aCallable2(reject);
    };
    newPromiseCapability.f = function(C) {
      return new PromiseCapability(C);
    };
    return newPromiseCapability;
  }
  var perform;
  var hasRequiredPerform;
  function requirePerform() {
    if (hasRequiredPerform) return perform;
    hasRequiredPerform = 1;
    perform = function(exec) {
      try {
        return { error: false, value: exec() };
      } catch (error) {
        return { error: true, value: error };
      }
    };
    return perform;
  }
  var promiseNativeConstructor;
  var hasRequiredPromiseNativeConstructor;
  function requirePromiseNativeConstructor() {
    if (hasRequiredPromiseNativeConstructor) return promiseNativeConstructor;
    hasRequiredPromiseNativeConstructor = 1;
    var globalThis2 = requireGlobalThis();
    promiseNativeConstructor = globalThis2.Promise;
    return promiseNativeConstructor;
  }
  var checkCorrectnessOfIteration;
  var hasRequiredCheckCorrectnessOfIteration;
  function requireCheckCorrectnessOfIteration() {
    if (hasRequiredCheckCorrectnessOfIteration) return checkCorrectnessOfIteration;
    hasRequiredCheckCorrectnessOfIteration = 1;
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ITERATOR = wellKnownSymbol2("iterator");
    var SAFE_CLOSING = false;
    try {
      var called = 0;
      var iteratorWithReturn = {
        next: function() {
          return { done: !!called++ };
        },
        "return": function() {
          SAFE_CLOSING = true;
        }
      };
      iteratorWithReturn[ITERATOR] = function() {
        return this;
      };
      Array.from(iteratorWithReturn, function() {
        throw 2;
      });
    } catch (error) {
    }
    checkCorrectnessOfIteration = function(exec, SKIP_CLOSING) {
      try {
        if (!SKIP_CLOSING && !SAFE_CLOSING) return false;
      } catch (error) {
        return false;
      }
      var ITERATION_SUPPORT = false;
      try {
        var object = {};
        object[ITERATOR] = function() {
          return {
            next: function() {
              return { done: ITERATION_SUPPORT = true };
            }
          };
        };
        exec(object);
      } catch (error) {
      }
      return ITERATION_SUPPORT;
    };
    return checkCorrectnessOfIteration;
  }
  var promiseConstructorDetection;
  var hasRequiredPromiseConstructorDetection;
  function requirePromiseConstructorDetection() {
    if (hasRequiredPromiseConstructorDetection) return promiseConstructorDetection;
    hasRequiredPromiseConstructorDetection = 1;
    var globalThis2 = requireGlobalThis();
    var NativePromiseConstructor = requirePromiseNativeConstructor();
    var isCallable2 = requireIsCallable();
    var isForced = requireIsForced();
    var inspectSource2 = requireInspectSource();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ENVIRONMENT = requireEnvironment();
    var IS_PURE = requireIsPure();
    var V8_VERSION = requireEnvironmentV8Version();
    var NativePromisePrototype = NativePromiseConstructor && NativePromiseConstructor.prototype;
    var SPECIES = wellKnownSymbol2("species");
    var SUBCLASSING = false;
    var NATIVE_PROMISE_REJECTION_EVENT = isCallable2(globalThis2.PromiseRejectionEvent);
    var FORCED_PROMISE_CONSTRUCTOR = isForced("Promise", function() {
      var PROMISE_CONSTRUCTOR_SOURCE = inspectSource2(NativePromiseConstructor);
      var GLOBAL_CORE_JS_PROMISE = PROMISE_CONSTRUCTOR_SOURCE !== String(NativePromiseConstructor);
      if (!GLOBAL_CORE_JS_PROMISE && V8_VERSION === 66) return true;
      if (IS_PURE && !(NativePromisePrototype["catch"] && NativePromisePrototype["finally"])) return true;
      if (!V8_VERSION || V8_VERSION < 51 || !/native code/.test(PROMISE_CONSTRUCTOR_SOURCE)) {
        var promise = new NativePromiseConstructor(function(resolve) {
          resolve(1);
        });
        var FakePromise = function(exec) {
          exec(function() {
          }, function() {
          });
        };
        var constructor = promise.constructor = {};
        constructor[SPECIES] = FakePromise;
        SUBCLASSING = promise.then(function() {
        }) instanceof FakePromise;
        if (!SUBCLASSING) return true;
      }
      return !GLOBAL_CORE_JS_PROMISE && (ENVIRONMENT === "BROWSER" || ENVIRONMENT === "DENO") && !NATIVE_PROMISE_REJECTION_EVENT;
    });
    promiseConstructorDetection = {
      CONSTRUCTOR: FORCED_PROMISE_CONSTRUCTOR,
      REJECTION_EVENT: NATIVE_PROMISE_REJECTION_EVENT,
      SUBCLASSING
    };
    return promiseConstructorDetection;
  }
  var promiseStaticsIncorrectIteration;
  var hasRequiredPromiseStaticsIncorrectIteration;
  function requirePromiseStaticsIncorrectIteration() {
    if (hasRequiredPromiseStaticsIncorrectIteration) return promiseStaticsIncorrectIteration;
    hasRequiredPromiseStaticsIncorrectIteration = 1;
    var NativePromiseConstructor = requirePromiseNativeConstructor();
    var checkCorrectnessOfIteration2 = requireCheckCorrectnessOfIteration();
    var FORCED_PROMISE_CONSTRUCTOR = requirePromiseConstructorDetection().CONSTRUCTOR;
    promiseStaticsIncorrectIteration = FORCED_PROMISE_CONSTRUCTOR || !checkCorrectnessOfIteration2(function(iterable) {
      NativePromiseConstructor.all(iterable).then(void 0, function() {
      });
    });
    return promiseStaticsIncorrectIteration;
  }
  var hasRequiredEs_promise_allSettled;
  function requireEs_promise_allSettled() {
    if (hasRequiredEs_promise_allSettled) return es_promise_allSettled;
    hasRequiredEs_promise_allSettled = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var newPromiseCapabilityModule = requireNewPromiseCapability();
    var perform2 = requirePerform();
    var iterate2 = requireIterate();
    var PROMISE_STATICS_INCORRECT_ITERATION = requirePromiseStaticsIncorrectIteration();
    $({ target: "Promise", stat: true, forced: PROMISE_STATICS_INCORRECT_ITERATION }, {
      allSettled: function allSettled(iterable) {
        var C = this;
        var capability = newPromiseCapabilityModule.f(C);
        var resolve = capability.resolve;
        var reject = capability.reject;
        var result = perform2(function() {
          var promiseResolve2 = aCallable2(C.resolve);
          var values = [];
          var counter = 0;
          var remaining = 1;
          iterate2(iterable, function(promise) {
            var index = counter++;
            var alreadyCalled = false;
            remaining++;
            call(promiseResolve2, C, promise).then(function(value) {
              if (alreadyCalled) return;
              alreadyCalled = true;
              values[index] = { status: "fulfilled", value };
              --remaining || resolve(values);
            }, function(error) {
              if (alreadyCalled) return;
              alreadyCalled = true;
              values[index] = { status: "rejected", reason: error };
              --remaining || resolve(values);
            });
          });
          --remaining || resolve(values);
        });
        if (result.error) reject(result.value);
        return capability.promise;
      }
    });
    return es_promise_allSettled;
  }
  requireEs_promise_allSettled();
  var es_promise_any = {};
  var hasRequiredEs_promise_any;
  function requireEs_promise_any() {
    if (hasRequiredEs_promise_any) return es_promise_any;
    hasRequiredEs_promise_any = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var getBuiltIn2 = requireGetBuiltIn();
    var newPromiseCapabilityModule = requireNewPromiseCapability();
    var perform2 = requirePerform();
    var iterate2 = requireIterate();
    var PROMISE_STATICS_INCORRECT_ITERATION = requirePromiseStaticsIncorrectIteration();
    var PROMISE_ANY_ERROR = "No one promise resolved";
    $({ target: "Promise", stat: true, forced: PROMISE_STATICS_INCORRECT_ITERATION }, {
      any: function any(iterable) {
        var C = this;
        var AggregateError = getBuiltIn2("AggregateError");
        var capability = newPromiseCapabilityModule.f(C);
        var resolve = capability.resolve;
        var reject = capability.reject;
        var result = perform2(function() {
          var promiseResolve2 = aCallable2(C.resolve);
          var errors = [];
          var counter = 0;
          var remaining = 1;
          var alreadyResolved = false;
          iterate2(iterable, function(promise) {
            var index = counter++;
            var alreadyRejected = false;
            remaining++;
            call(promiseResolve2, C, promise).then(function(value) {
              if (alreadyRejected || alreadyResolved) return;
              alreadyResolved = true;
              resolve(value);
            }, function(error) {
              if (alreadyRejected || alreadyResolved) return;
              alreadyRejected = true;
              errors[index] = error;
              --remaining || reject(new AggregateError(errors, PROMISE_ANY_ERROR));
            });
          });
          --remaining || reject(new AggregateError(errors, PROMISE_ANY_ERROR));
        });
        if (result.error) reject(result.value);
        return capability.promise;
      }
    });
    return es_promise_any;
  }
  requireEs_promise_any();
  var es_promise_finally = {};
  var aConstructor;
  var hasRequiredAConstructor;
  function requireAConstructor() {
    if (hasRequiredAConstructor) return aConstructor;
    hasRequiredAConstructor = 1;
    var isConstructor2 = requireIsConstructor();
    var tryToString2 = requireTryToString();
    var $TypeError = TypeError;
    aConstructor = function(argument) {
      if (isConstructor2(argument)) return argument;
      throw new $TypeError(tryToString2(argument) + " is not a constructor");
    };
    return aConstructor;
  }
  var speciesConstructor;
  var hasRequiredSpeciesConstructor;
  function requireSpeciesConstructor() {
    if (hasRequiredSpeciesConstructor) return speciesConstructor;
    hasRequiredSpeciesConstructor = 1;
    var anObject2 = requireAnObject();
    var aConstructor2 = requireAConstructor();
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var SPECIES = wellKnownSymbol2("species");
    speciesConstructor = function(O, defaultConstructor) {
      var C = anObject2(O).constructor;
      var S;
      return C === void 0 || isNullOrUndefined2(S = anObject2(C)[SPECIES]) ? defaultConstructor : aConstructor2(S);
    };
    return speciesConstructor;
  }
  var promiseResolve;
  var hasRequiredPromiseResolve;
  function requirePromiseResolve() {
    if (hasRequiredPromiseResolve) return promiseResolve;
    hasRequiredPromiseResolve = 1;
    var anObject2 = requireAnObject();
    var isObject2 = requireIsObject();
    var newPromiseCapability2 = requireNewPromiseCapability();
    promiseResolve = function(C, x) {
      anObject2(C);
      if (isObject2(x) && x.constructor === C) return x;
      var promiseCapability = newPromiseCapability2.f(C);
      var resolve = promiseCapability.resolve;
      resolve(x);
      return promiseCapability.promise;
    };
    return promiseResolve;
  }
  var hasRequiredEs_promise_finally;
  function requireEs_promise_finally() {
    if (hasRequiredEs_promise_finally) return es_promise_finally;
    hasRequiredEs_promise_finally = 1;
    var $ = require_export();
    var IS_PURE = requireIsPure();
    var NativePromiseConstructor = requirePromiseNativeConstructor();
    var fails2 = requireFails();
    var getBuiltIn2 = requireGetBuiltIn();
    var isCallable2 = requireIsCallable();
    var speciesConstructor2 = requireSpeciesConstructor();
    var promiseResolve2 = requirePromiseResolve();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var NativePromisePrototype = NativePromiseConstructor && NativePromiseConstructor.prototype;
    var NON_GENERIC = !!NativePromiseConstructor && fails2(function() {
      NativePromisePrototype["finally"].call({ then: function() {
      } }, function() {
      });
    });
    $({ target: "Promise", proto: true, real: true, forced: NON_GENERIC }, {
      "finally": function(onFinally) {
        var C = speciesConstructor2(this, getBuiltIn2("Promise"));
        var isFunction = isCallable2(onFinally);
        return this.then(
          isFunction ? function(x) {
            return promiseResolve2(C, onFinally()).then(function() {
              return x;
            });
          } : onFinally,
          isFunction ? function(e) {
            return promiseResolve2(C, onFinally()).then(function() {
              throw e;
            });
          } : onFinally
        );
      }
    });
    if (!IS_PURE && isCallable2(NativePromiseConstructor)) {
      var method = getBuiltIn2("Promise").prototype["finally"];
      if (NativePromisePrototype["finally"] !== method) {
        defineBuiltIn2(NativePromisePrototype, "finally", method, { unsafe: true });
      }
    }
    return es_promise_finally;
  }
  requireEs_promise_finally();
  var es_promise_try = {};
  var hasRequiredEs_promise_try;
  function requireEs_promise_try() {
    if (hasRequiredEs_promise_try) return es_promise_try;
    hasRequiredEs_promise_try = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var apply2 = requireFunctionApply();
    var slice = requireArraySlice();
    var promiseResolve2 = requirePromiseResolve();
    var newPromiseCapabilityModule = requireNewPromiseCapability();
    var aCallable2 = requireACallable();
    var perform2 = requirePerform();
    var fails2 = requireFails();
    var Promise2 = globalThis2.Promise;
    var ACCEPT_ARGUMENTS = false;
    var FORCED = !Promise2 || !Promise2["try"] || fails2(function() {
      var p = Promise2.resolve();
      return Promise2["try"](function(argument) {
        ACCEPT_ARGUMENTS = argument === 8;
        return p;
      }, 8) !== p;
    }) || !ACCEPT_ARGUMENTS;
    $({ target: "Promise", stat: true, forced: FORCED }, {
      "try": function(callbackfn) {
        var args = arguments.length > 1 ? slice(arguments, 1) : [];
        var result = perform2(function() {
          return apply2(aCallable2(callbackfn), void 0, args);
        });
        if (!result.error) return promiseResolve2(this, result.value);
        var promiseCapability = newPromiseCapabilityModule.f(this);
        var reject = promiseCapability.reject;
        reject(result.value);
        return promiseCapability.promise;
      }
    });
    return es_promise_try;
  }
  requireEs_promise_try();
  var es_promise_withResolvers = {};
  var hasRequiredEs_promise_withResolvers;
  function requireEs_promise_withResolvers() {
    if (hasRequiredEs_promise_withResolvers) return es_promise_withResolvers;
    hasRequiredEs_promise_withResolvers = 1;
    var $ = require_export();
    var newPromiseCapabilityModule = requireNewPromiseCapability();
    $({ target: "Promise", stat: true }, {
      withResolvers: function withResolvers() {
        var promiseCapability = newPromiseCapabilityModule.f(this);
        return {
          promise: promiseCapability.promise,
          resolve: promiseCapability.resolve,
          reject: promiseCapability.reject
        };
      }
    });
    return es_promise_withResolvers;
  }
  requireEs_promise_withResolvers();
  var es_array_fromAsync = {};
  var asyncIteratorPrototype;
  var hasRequiredAsyncIteratorPrototype;
  function requireAsyncIteratorPrototype() {
    if (hasRequiredAsyncIteratorPrototype) return asyncIteratorPrototype;
    hasRequiredAsyncIteratorPrototype = 1;
    var globalThis2 = requireGlobalThis();
    var shared2 = requireSharedStore();
    var isCallable2 = requireIsCallable();
    var create2 = requireObjectCreate();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var IS_PURE = requireIsPure();
    var USE_FUNCTION_CONSTRUCTOR = "USE_FUNCTION_CONSTRUCTOR";
    var ASYNC_ITERATOR = wellKnownSymbol2("asyncIterator");
    var AsyncIterator2 = globalThis2.AsyncIterator;
    var PassedAsyncIteratorPrototype = shared2.AsyncIteratorPrototype;
    var AsyncIteratorPrototype, prototype;
    if (PassedAsyncIteratorPrototype) {
      AsyncIteratorPrototype = PassedAsyncIteratorPrototype;
    } else if (isCallable2(AsyncIterator2)) {
      AsyncIteratorPrototype = AsyncIterator2.prototype;
    } else if (shared2[USE_FUNCTION_CONSTRUCTOR] || globalThis2[USE_FUNCTION_CONSTRUCTOR]) {
      try {
        prototype = getPrototypeOf2(getPrototypeOf2(getPrototypeOf2(Function("return async function*(){}()")())));
        if (getPrototypeOf2(prototype) === Object.prototype) AsyncIteratorPrototype = prototype;
      } catch (error) {
      }
    }
    if (!AsyncIteratorPrototype) AsyncIteratorPrototype = {};
    else if (IS_PURE) AsyncIteratorPrototype = create2(AsyncIteratorPrototype);
    if (!isCallable2(AsyncIteratorPrototype[ASYNC_ITERATOR])) {
      defineBuiltIn2(AsyncIteratorPrototype, ASYNC_ITERATOR, function() {
        return this;
      });
    }
    asyncIteratorPrototype = AsyncIteratorPrototype;
    return asyncIteratorPrototype;
  }
  var asyncFromSyncIterator;
  var hasRequiredAsyncFromSyncIterator;
  function requireAsyncFromSyncIterator() {
    if (hasRequiredAsyncFromSyncIterator) return asyncFromSyncIterator;
    hasRequiredAsyncFromSyncIterator = 1;
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var create2 = requireObjectCreate();
    var getMethod2 = requireGetMethod();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var InternalStateModule = requireInternalState();
    var iteratorClose2 = requireIteratorClose();
    var getBuiltIn2 = requireGetBuiltIn();
    var AsyncIteratorPrototype = requireAsyncIteratorPrototype();
    var createIterResultObject2 = requireCreateIterResultObject();
    var Promise2 = getBuiltIn2("Promise");
    var ASYNC_FROM_SYNC_ITERATOR = "AsyncFromSyncIterator";
    var setInternalState = InternalStateModule.set;
    var getInternalState = InternalStateModule.getterFor(ASYNC_FROM_SYNC_ITERATOR);
    var asyncFromSyncIteratorContinuation = function(result, resolve, reject, syncIterator, closeOnRejection) {
      var done = result.done;
      Promise2.resolve(result.value).then(function(value) {
        resolve(createIterResultObject2(value, done));
      }, function(error) {
        if (!done && closeOnRejection) {
          try {
            iteratorClose2(syncIterator, "throw", error);
          } catch (error2) {
            error = error2;
          }
        }
        reject(error);
      });
    };
    var AsyncFromSyncIterator = function AsyncIterator2(iteratorRecord) {
      iteratorRecord.type = ASYNC_FROM_SYNC_ITERATOR;
      setInternalState(this, iteratorRecord);
    };
    AsyncFromSyncIterator.prototype = defineBuiltIns2(create2(AsyncIteratorPrototype), {
      next: function next() {
        var state = getInternalState(this);
        var hasValue = arguments.length > 0;
        var value = hasValue ? arguments[0] : void 0;
        return new Promise2(function(resolve, reject) {
          var result = anObject2(hasValue ? call(state.next, state.iterator, value) : call(state.next, state.iterator));
          asyncFromSyncIteratorContinuation(result, resolve, reject, state.iterator, true);
        });
      },
      "return": function() {
        var state = getInternalState(this);
        var iterator = state.iterator;
        var hasValue = arguments.length > 0;
        var value = hasValue ? arguments[0] : void 0;
        return new Promise2(function(resolve, reject) {
          var $return = getMethod2(iterator, "return");
          if ($return === void 0) return resolve(createIterResultObject2(value, true));
          var result = anObject2(hasValue ? call($return, iterator, value) : call($return, iterator));
          asyncFromSyncIteratorContinuation(result, resolve, reject, iterator);
        });
      },
      "throw": function() {
        var state = getInternalState(this);
        var iterator = state.iterator;
        var hasValue = arguments.length > 0;
        var value = hasValue ? arguments[0] : void 0;
        return new Promise2(function(resolve, reject) {
          var $throw = getMethod2(iterator, "throw");
          if ($throw === void 0) {
            try {
              iteratorClose2(iterator, "normal");
            } catch (error) {
              return reject(error);
            }
            return reject(new TypeError("The iterator does not provide a throw method"));
          }
          var result = anObject2(hasValue ? call($throw, iterator, value) : call($throw, iterator));
          asyncFromSyncIteratorContinuation(result, resolve, reject, iterator, true);
        });
      }
    });
    asyncFromSyncIterator = AsyncFromSyncIterator;
    return asyncFromSyncIterator;
  }
  var getAsyncIterator;
  var hasRequiredGetAsyncIterator;
  function requireGetAsyncIterator() {
    if (hasRequiredGetAsyncIterator) return getAsyncIterator;
    hasRequiredGetAsyncIterator = 1;
    var call = requireFunctionCall();
    var AsyncFromSyncIterator = requireAsyncFromSyncIterator();
    var anObject2 = requireAnObject();
    var getIterator = requireGetIteratorInternal();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var getMethod2 = requireGetMethod();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ASYNC_ITERATOR = wellKnownSymbol2("asyncIterator");
    getAsyncIterator = function(it, usingIterator) {
      var method = arguments.length < 2 ? getMethod2(it, ASYNC_ITERATOR) : usingIterator;
      return method ? anObject2(call(method, it)) : new AsyncFromSyncIterator(getIteratorDirect2(getIterator(it)));
    };
    return getAsyncIterator;
  }
  var asyncIteratorClose;
  var hasRequiredAsyncIteratorClose;
  function requireAsyncIteratorClose() {
    if (hasRequiredAsyncIteratorClose) return asyncIteratorClose;
    hasRequiredAsyncIteratorClose = 1;
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var getBuiltIn2 = requireGetBuiltIn();
    var getMethod2 = requireGetMethod();
    asyncIteratorClose = function(iterator, method, argument, reject) {
      try {
        var returnMethod = getMethod2(iterator, "return");
        if (returnMethod) {
          return getBuiltIn2("Promise").resolve(call(returnMethod, iterator)).then(function(result) {
            try {
              if (method !== reject) anObject2(result);
            } catch (error3) {
              reject(error3);
              return;
            }
            method(argument);
          }, function(error) {
            method === reject ? method(argument) : reject(error);
          });
        }
      } catch (error2) {
        return method === reject ? reject(argument) : reject(error2);
      }
      method(argument);
    };
    return asyncIteratorClose;
  }
  var asyncIteratorIteration;
  var hasRequiredAsyncIteratorIteration;
  function requireAsyncIteratorIteration() {
    if (hasRequiredAsyncIteratorIteration) return asyncIteratorIteration;
    hasRequiredAsyncIteratorIteration = 1;
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var isObject2 = requireIsObject();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var getBuiltIn2 = requireGetBuiltIn();
    var createProperty2 = requireCreateProperty();
    var setArrayLength = requireArraySetLength();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var closeAsyncIteration = requireAsyncIteratorClose();
    var createMethod = function(TYPE) {
      var IS_TO_ARRAY = TYPE === 0;
      var IS_FOR_EACH = TYPE === 1;
      var IS_EVERY = TYPE === 2;
      var IS_SOME = TYPE === 3;
      return function(object, fn, target) {
        anObject2(object);
        var MAPPING = fn !== void 0;
        if (MAPPING || !IS_TO_ARRAY) aCallable2(fn);
        var record = getIteratorDirect2(object);
        var Promise2 = getBuiltIn2("Promise");
        var iterator = record.iterator;
        var next = record.next;
        var counter = 0;
        return new Promise2(function(resolve, reject) {
          var ifAbruptCloseAsyncIterator = function(error) {
            closeAsyncIteration(iterator, reject, error, reject);
          };
          var loop = function() {
            try {
              try {
                doesNotExceedSafeInteger2(counter);
              } catch (error5) {
                return ifAbruptCloseAsyncIterator(error5);
              }
              Promise2.resolve(anObject2(call(next, iterator))).then(function(step) {
                try {
                  if (anObject2(step).done) {
                    if (IS_TO_ARRAY) {
                      setArrayLength(target, counter);
                      resolve(target);
                    } else resolve(IS_SOME ? false : IS_EVERY || void 0);
                  } else {
                    var value = step.value;
                    try {
                      if (MAPPING) {
                        var index = counter++;
                        var result = fn(value, index);
                        var handler = function($result) {
                          if (IS_FOR_EACH) {
                            loop();
                          } else if (IS_EVERY) {
                            $result ? loop() : closeAsyncIteration(iterator, resolve, false, reject);
                          } else if (IS_TO_ARRAY) {
                            try {
                              createProperty2(target, index, $result);
                              loop();
                            } catch (error4) {
                              ifAbruptCloseAsyncIterator(error4);
                            }
                          } else {
                            $result ? closeAsyncIteration(iterator, resolve, IS_SOME || value, reject) : loop();
                          }
                        };
                        if (isObject2(result)) Promise2.resolve(result).then(handler, ifAbruptCloseAsyncIterator);
                        else handler(result);
                      } else {
                        createProperty2(target, counter++, value);
                        loop();
                      }
                    } catch (error3) {
                      ifAbruptCloseAsyncIterator(error3);
                    }
                  }
                } catch (error2) {
                  reject(error2);
                }
              }, reject);
            } catch (error) {
              reject(error);
            }
          };
          loop();
        });
      };
    };
    asyncIteratorIteration = {
      // `AsyncIterator.prototype.toArray` / `Array.fromAsync` methods
      toArray: createMethod(0),
      // `AsyncIterator.prototype.forEach` method
      forEach: createMethod(1),
      // `AsyncIterator.prototype.every` method
      every: createMethod(2),
      // `AsyncIterator.prototype.some` method
      some: createMethod(3),
      // `AsyncIterator.prototype.find` method
      find: createMethod(4)
    };
    return asyncIteratorIteration;
  }
  var arrayFromAsync;
  var hasRequiredArrayFromAsync;
  function requireArrayFromAsync() {
    if (hasRequiredArrayFromAsync) return arrayFromAsync;
    hasRequiredArrayFromAsync = 1;
    var bind = requireFunctionBindContext();
    var uncurryThis = requireFunctionUncurryThis();
    var isConstructor2 = requireIsConstructor();
    var getAsyncIterator2 = requireGetAsyncIterator();
    var getIterator = requireGetIteratorInternal();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var getMethod2 = requireGetMethod();
    var getBuiltIn2 = requireGetBuiltIn();
    var getBuiltInPrototypeMethod2 = requireGetBuiltInPrototypeMethod();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var AsyncFromSyncIterator = requireAsyncFromSyncIterator();
    var toArray = requireAsyncIteratorIteration().toArray;
    var ASYNC_ITERATOR = wellKnownSymbol2("asyncIterator");
    var arrayIterator = uncurryThis(getBuiltInPrototypeMethod2("Array", "values"));
    var arrayIteratorNext = uncurryThis(arrayIterator([]).next);
    var safeArrayIterator = function() {
      return new SafeArrayIterator(this);
    };
    var SafeArrayIterator = function(O) {
      this.iterator = arrayIterator(O);
    };
    SafeArrayIterator.prototype.next = function() {
      return arrayIteratorNext(this.iterator);
    };
    arrayFromAsync = function fromAsync(items) {
      var C = this;
      var argumentsLength = arguments.length;
      var mapfn = argumentsLength > 1 ? arguments[1] : void 0;
      var thisArg = argumentsLength > 2 ? arguments[2] : void 0;
      return new (getBuiltIn2("Promise"))(function(resolve) {
        if (mapfn !== void 0) mapfn = bind(mapfn, thisArg);
        var usingAsyncIterator = getMethod2(items, ASYNC_ITERATOR);
        var usingSyncIterator = usingAsyncIterator ? void 0 : getIteratorMethod(items) || safeArrayIterator;
        var A = isConstructor2(C) ? new C() : [];
        var iterator = usingAsyncIterator ? getAsyncIterator2(items, usingAsyncIterator) : new AsyncFromSyncIterator(getIteratorDirect2(getIterator(items, usingSyncIterator)));
        resolve(toArray(iterator, mapfn, A));
      });
    };
    return arrayFromAsync;
  }
  var hasRequiredEs_array_fromAsync;
  function requireEs_array_fromAsync() {
    if (hasRequiredEs_array_fromAsync) return es_array_fromAsync;
    hasRequiredEs_array_fromAsync = 1;
    var $ = require_export();
    var fromAsync = requireArrayFromAsync();
    var fails2 = requireFails();
    var nativeFromAsync = Array.fromAsync;
    var INCORRECT_CONSTRUCTURING = !nativeFromAsync || fails2(function() {
      var counter = 0;
      nativeFromAsync.call(function() {
        counter++;
        return [];
      }, { length: 0 });
      return counter !== 1;
    });
    $({ target: "Array", stat: true, forced: INCORRECT_CONSTRUCTURING }, {
      fromAsync
    });
    return es_array_fromAsync;
  }
  requireEs_array_fromAsync();
  var es_asyncDisposableStack_constructor = {};
  var hasRequiredEs_asyncDisposableStack_constructor;
  function requireEs_asyncDisposableStack_constructor() {
    if (hasRequiredEs_asyncDisposableStack_constructor) return es_asyncDisposableStack_constructor;
    hasRequiredEs_asyncDisposableStack_constructor = 1;
    var $ = require_export();
    var DESCRIPTORS = requireDescriptors();
    var getBuiltIn2 = requireGetBuiltIn();
    var aCallable2 = requireACallable();
    var anInstance2 = requireAnInstance();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var InternalStateModule = requireInternalState();
    var addDisposableResource2 = requireAddDisposableResource();
    var V8_VERSION = requireEnvironmentV8Version();
    var Promise2 = getBuiltIn2("Promise");
    var SuppressedError2 = getBuiltIn2("SuppressedError");
    var $ReferenceError = ReferenceError;
    var ASYNC_DISPOSE = wellKnownSymbol2("asyncDispose");
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var ASYNC_DISPOSABLE_STACK = "AsyncDisposableStack";
    var setInternalState = InternalStateModule.set;
    var getAsyncDisposableStackInternalState = InternalStateModule.getterFor(ASYNC_DISPOSABLE_STACK);
    var HINT = "async-dispose";
    var DISPOSED = "disposed";
    var PENDING = "pending";
    var getPendingAsyncDisposableStackInternalState = function(stack) {
      var internalState2 = getAsyncDisposableStackInternalState(stack);
      if (internalState2.state === DISPOSED) throw new $ReferenceError(ASYNC_DISPOSABLE_STACK + " already disposed");
      return internalState2;
    };
    var $AsyncDisposableStack = function AsyncDisposableStack() {
      setInternalState(anInstance2(this, AsyncDisposableStackPrototype), {
        type: ASYNC_DISPOSABLE_STACK,
        state: PENDING,
        stack: []
      });
      if (!DESCRIPTORS) this.disposed = false;
    };
    var AsyncDisposableStackPrototype = $AsyncDisposableStack.prototype;
    defineBuiltIns2(AsyncDisposableStackPrototype, {
      disposeAsync: function disposeAsync() {
        var asyncDisposableStack = this;
        return new Promise2(function(resolve, reject) {
          var internalState2 = getAsyncDisposableStackInternalState(asyncDisposableStack);
          if (internalState2.state === DISPOSED) return resolve(void 0);
          internalState2.state = DISPOSED;
          if (!DESCRIPTORS) asyncDisposableStack.disposed = true;
          var stack = internalState2.stack;
          var i = stack.length;
          var thrown = false;
          var suppressed;
          var handleError = function(result) {
            if (thrown) {
              suppressed = new SuppressedError2(result, suppressed);
            } else {
              thrown = true;
              suppressed = result;
            }
            loop();
          };
          var loop = function() {
            if (i) {
              var disposeMethod = stack[--i];
              stack[i] = null;
              try {
                Promise2.resolve(disposeMethod()).then(loop, handleError);
              } catch (error) {
                handleError(error);
              }
            } else {
              internalState2.stack = null;
              thrown ? reject(suppressed) : resolve(void 0);
            }
          };
          loop();
        });
      },
      use: function use(value) {
        addDisposableResource2(getPendingAsyncDisposableStackInternalState(this), value, HINT);
        return value;
      },
      adopt: function adopt(value, onDispose) {
        var internalState2 = getPendingAsyncDisposableStackInternalState(this);
        aCallable2(onDispose);
        addDisposableResource2(internalState2, void 0, HINT, function() {
          return onDispose(value);
        });
        return value;
      },
      defer: function defer(onDispose) {
        var internalState2 = getPendingAsyncDisposableStackInternalState(this);
        aCallable2(onDispose);
        addDisposableResource2(internalState2, void 0, HINT, onDispose);
      },
      move: function move() {
        var internalState2 = getPendingAsyncDisposableStackInternalState(this);
        var newAsyncDisposableStack = new $AsyncDisposableStack();
        getAsyncDisposableStackInternalState(newAsyncDisposableStack).stack = internalState2.stack;
        internalState2.stack = [];
        internalState2.state = DISPOSED;
        if (!DESCRIPTORS) this.disposed = true;
        return newAsyncDisposableStack;
      }
    });
    if (DESCRIPTORS) defineBuiltInAccessor2(AsyncDisposableStackPrototype, "disposed", {
      configurable: true,
      get: function disposed() {
        return getAsyncDisposableStackInternalState(this).state === DISPOSED;
      }
    });
    defineBuiltIn2(AsyncDisposableStackPrototype, ASYNC_DISPOSE, AsyncDisposableStackPrototype.disposeAsync, { name: "disposeAsync" });
    defineBuiltIn2(AsyncDisposableStackPrototype, TO_STRING_TAG, ASYNC_DISPOSABLE_STACK, { nonWritable: true });
    var SYNC_DISPOSE_RETURNING_PROMISE_RESOLUTION_BUG = V8_VERSION && V8_VERSION < 136;
    $({ global: true, constructor: true, forced: SYNC_DISPOSE_RETURNING_PROMISE_RESOLUTION_BUG }, {
      AsyncDisposableStack: $AsyncDisposableStack
    });
    return es_asyncDisposableStack_constructor;
  }
  requireEs_asyncDisposableStack_constructor();
  var es_asyncIterator_asyncDispose = {};
  var hasRequiredEs_asyncIterator_asyncDispose;
  function requireEs_asyncIterator_asyncDispose() {
    if (hasRequiredEs_asyncIterator_asyncDispose) return es_asyncIterator_asyncDispose;
    hasRequiredEs_asyncIterator_asyncDispose = 1;
    var call = requireFunctionCall();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var getBuiltIn2 = requireGetBuiltIn();
    var getMethod2 = requireGetMethod();
    var hasOwn = requireHasOwnProperty();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var AsyncIteratorPrototype = requireAsyncIteratorPrototype();
    var ASYNC_DISPOSE = wellKnownSymbol2("asyncDispose");
    var Promise2 = getBuiltIn2("Promise");
    if (!hasOwn(AsyncIteratorPrototype, ASYNC_DISPOSE)) {
      defineBuiltIn2(AsyncIteratorPrototype, ASYNC_DISPOSE, function() {
        var O = this;
        return new Promise2(function(resolve, reject) {
          var $return = getMethod2(O, "return");
          if ($return) {
            Promise2.resolve(call($return, O)).then(function() {
              resolve(void 0);
            }, reject);
          } else resolve(void 0);
        });
      });
    }
    return es_asyncIterator_asyncDispose;
  }
  requireEs_asyncIterator_asyncDispose();
  var es_reflect_toStringTag = {};
  var hasRequiredEs_reflect_toStringTag;
  function requireEs_reflect_toStringTag() {
    if (hasRequiredEs_reflect_toStringTag) return es_reflect_toStringTag;
    hasRequiredEs_reflect_toStringTag = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var setToStringTag2 = requireSetToStringTag();
    $({ global: true }, { Reflect: {} });
    setToStringTag2(globalThis2.Reflect, "Reflect", true);
    return es_reflect_toStringTag;
  }
  requireEs_reflect_toStringTag();
  var es_regexp_constructor = {};
  var isRegexp;
  var hasRequiredIsRegexp;
  function requireIsRegexp() {
    if (hasRequiredIsRegexp) return isRegexp;
    hasRequiredIsRegexp = 1;
    var isObject2 = requireIsObject();
    var classof2 = requireClassofRaw();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var MATCH = wellKnownSymbol2("match");
    isRegexp = function(it) {
      var isRegExp;
      return isObject2(it) && ((isRegExp = it[MATCH]) !== void 0 ? !!isRegExp : classof2(it) === "RegExp");
    };
    return isRegexp;
  }
  var regexpFlagsDetection;
  var hasRequiredRegexpFlagsDetection;
  function requireRegexpFlagsDetection() {
    if (hasRequiredRegexpFlagsDetection) return regexpFlagsDetection;
    hasRequiredRegexpFlagsDetection = 1;
    var globalThis2 = requireGlobalThis();
    var fails2 = requireFails();
    var RegExp2 = globalThis2.RegExp;
    var FLAGS_GETTER_IS_CORRECT = !fails2(function() {
      var INDICES_SUPPORT = true;
      try {
        RegExp2(".", "d");
      } catch (error) {
        INDICES_SUPPORT = false;
      }
      var O = {};
      var calls = "";
      var expected = INDICES_SUPPORT ? "dgimsy" : "gimsy";
      var addGetter = function(key2, chr) {
        Object.defineProperty(O, key2, { get: function() {
          calls += chr;
          return true;
        } });
      };
      var pairs = {
        dotAll: "s",
        global: "g",
        ignoreCase: "i",
        multiline: "m",
        sticky: "y"
      };
      if (INDICES_SUPPORT) pairs.hasIndices = "d";
      for (var key in pairs) addGetter(key, pairs[key]);
      var result = Object.getOwnPropertyDescriptor(RegExp2.prototype, "flags").get.call(O);
      return result !== expected || calls !== expected;
    });
    regexpFlagsDetection = { correct: FLAGS_GETTER_IS_CORRECT };
    return regexpFlagsDetection;
  }
  var regexpFlags;
  var hasRequiredRegexpFlags;
  function requireRegexpFlags() {
    if (hasRequiredRegexpFlags) return regexpFlags;
    hasRequiredRegexpFlags = 1;
    var anObject2 = requireAnObject();
    regexpFlags = function() {
      var that = anObject2(this);
      var result = "";
      if (that.hasIndices) result += "d";
      if (that.global) result += "g";
      if (that.ignoreCase) result += "i";
      if (that.multiline) result += "m";
      if (that.dotAll) result += "s";
      if (that.unicode) result += "u";
      if (that.unicodeSets) result += "v";
      if (that.sticky) result += "y";
      return result;
    };
    return regexpFlags;
  }
  var regexpGetFlags;
  var hasRequiredRegexpGetFlags;
  function requireRegexpGetFlags() {
    if (hasRequiredRegexpGetFlags) return regexpGetFlags;
    hasRequiredRegexpGetFlags = 1;
    var call = requireFunctionCall();
    var hasOwn = requireHasOwnProperty();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var regExpFlagsDetection = requireRegexpFlagsDetection();
    var regExpFlagsGetterImplementation = requireRegexpFlags();
    var RegExpPrototype = RegExp.prototype;
    regexpGetFlags = regExpFlagsDetection.correct ? function(it) {
      return it.flags;
    } : function(it) {
      return !regExpFlagsDetection.correct && isPrototypeOf(RegExpPrototype, it) && !hasOwn(it, "flags") ? call(regExpFlagsGetterImplementation, it) : it.flags;
    };
    return regexpGetFlags;
  }
  var regexpStickyHelpers;
  var hasRequiredRegexpStickyHelpers;
  function requireRegexpStickyHelpers() {
    if (hasRequiredRegexpStickyHelpers) return regexpStickyHelpers;
    hasRequiredRegexpStickyHelpers = 1;
    var fails2 = requireFails();
    var globalThis2 = requireGlobalThis();
    var $RegExp = globalThis2.RegExp;
    var UNSUPPORTED_Y = fails2(function() {
      var re = $RegExp("a", "y");
      re.lastIndex = 2;
      return re.exec("abcd") !== null;
    });
    var MISSED_STICKY = UNSUPPORTED_Y || fails2(function() {
      return !$RegExp("a", "y").sticky;
    });
    var BROKEN_CARET = UNSUPPORTED_Y || fails2(function() {
      var re = $RegExp("^r", "gy");
      re.lastIndex = 2;
      return re.exec("str") !== null;
    });
    regexpStickyHelpers = {
      BROKEN_CARET,
      MISSED_STICKY,
      UNSUPPORTED_Y
    };
    return regexpStickyHelpers;
  }
  var regexpUnsupportedDotAll;
  var hasRequiredRegexpUnsupportedDotAll;
  function requireRegexpUnsupportedDotAll() {
    if (hasRequiredRegexpUnsupportedDotAll) return regexpUnsupportedDotAll;
    hasRequiredRegexpUnsupportedDotAll = 1;
    var fails2 = requireFails();
    var globalThis2 = requireGlobalThis();
    var $RegExp = globalThis2.RegExp;
    regexpUnsupportedDotAll = fails2(function() {
      var re = $RegExp(".", "s");
      return !(re.dotAll && re.test("\n") && re.flags === "s");
    });
    return regexpUnsupportedDotAll;
  }
  var regexpUnsupportedNcg;
  var hasRequiredRegexpUnsupportedNcg;
  function requireRegexpUnsupportedNcg() {
    if (hasRequiredRegexpUnsupportedNcg) return regexpUnsupportedNcg;
    hasRequiredRegexpUnsupportedNcg = 1;
    var fails2 = requireFails();
    var globalThis2 = requireGlobalThis();
    var $RegExp = globalThis2.RegExp;
    regexpUnsupportedNcg = fails2(function() {
      var re = $RegExp("(?<a>b)", "g");
      return re.exec("b").groups.a !== "b" || "b".replace(re, "$<a>c") !== "bc";
    });
    return regexpUnsupportedNcg;
  }
  var hasRequiredEs_regexp_constructor;
  function requireEs_regexp_constructor() {
    if (hasRequiredEs_regexp_constructor) return es_regexp_constructor;
    hasRequiredEs_regexp_constructor = 1;
    var DESCRIPTORS = requireDescriptors();
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var isForced = requireIsForced();
    var inheritIfRequired2 = requireInheritIfRequired();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var create2 = requireObjectCreate();
    var getOwnPropertyNames = requireObjectGetOwnPropertyNames().f;
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var isRegExp = requireIsRegexp();
    var toString2 = requireToString();
    var getRegExpFlags = requireRegexpGetFlags();
    var stickyHelpers = requireRegexpStickyHelpers();
    var proxyAccessor2 = requireProxyAccessor();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var fails2 = requireFails();
    var hasOwn = requireHasOwnProperty();
    var enforceInternalState = requireInternalState().enforce;
    var setSpecies2 = requireSetSpecies();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var UNSUPPORTED_DOT_ALL = requireRegexpUnsupportedDotAll();
    var UNSUPPORTED_NCG = requireRegexpUnsupportedNcg();
    var MATCH = wellKnownSymbol2("match");
    var NativeRegExp = globalThis2.RegExp;
    var RegExpPrototype = NativeRegExp.prototype;
    var SyntaxError2 = globalThis2.SyntaxError;
    var exec = uncurryThis(RegExpPrototype.exec);
    var charAt = uncurryThis("".charAt);
    var replace = uncurryThis("".replace);
    var stringIndexOf2 = uncurryThis("".indexOf);
    var stringSlice = uncurryThis("".slice);
    var IS_NCG = /^\?<[^\s\d!#%&*+<=>@^][^\s!#%&*+<=>@^]*>/;
    var re1 = /a/g;
    var re2 = /a/g;
    var CORRECT_NEW = new NativeRegExp(re1) !== re1;
    var MISSED_STICKY = stickyHelpers.MISSED_STICKY;
    var UNSUPPORTED_Y = stickyHelpers.UNSUPPORTED_Y;
    var BASE_FORCED = DESCRIPTORS && (!CORRECT_NEW || MISSED_STICKY || UNSUPPORTED_DOT_ALL || UNSUPPORTED_NCG || fails2(function() {
      re2[MATCH] = false;
      return NativeRegExp(re1) !== re1 || NativeRegExp(re2) === re2 || String(NativeRegExp(re1, "i")) !== "/a/i";
    }));
    var handleDotAll = function(string) {
      var length = string.length;
      var index2 = 0;
      var result = "";
      var brackets = false;
      var chr;
      for (; index2 < length; index2++) {
        chr = charAt(string, index2);
        if (chr === "\\") {
          result += chr + charAt(string, ++index2);
          continue;
        }
        if (!brackets && chr === ".") {
          result += "[\\s\\S]";
        } else {
          if (chr === "[") {
            brackets = true;
          } else if (chr === "]") {
            brackets = false;
          }
          result += chr;
        }
      }
      return result;
    };
    var handleNCG = function(string) {
      var length = string.length;
      var index2 = 0;
      var result = "";
      var named = [];
      var names = create2(null);
      var brackets = false;
      var ncg = false;
      var groupid = 0;
      var groupname = "";
      var chr;
      for (; index2 < length; index2++) {
        chr = charAt(string, index2);
        if (chr === "\\") {
          chr += charAt(string, ++index2);
          if (!ncg && charAt(chr, 1) === "\\") {
            result += "\\x5c";
            continue;
          }
        } else if (chr === "]") {
          brackets = false;
        } else if (!brackets) switch (true) {
          case chr === "[":
            brackets = true;
            break;
          case chr === "(":
            result += chr;
            if (exec(IS_NCG, stringSlice(string, index2 + 1))) {
              index2 += 2;
              ncg = true;
              groupid++;
            } else if (charAt(string, index2 + 1) !== "?") {
              groupid++;
            }
            continue;
          case (chr === ">" && ncg):
            if (groupname === "" || hasOwn(names, groupname)) {
              throw new SyntaxError2("Invalid capture group name");
            }
            names[groupname] = true;
            named[named.length] = [groupname, groupid];
            ncg = false;
            groupname = "";
            continue;
        }
        if (ncg) groupname += chr;
        else result += chr;
      }
      for (var ni = 0; ni < named.length; ni++) {
        var backref = "\\k<" + named[ni][0] + ">";
        var numRef = "\\" + named[ni][1];
        while (stringIndexOf2(result, backref) > -1) {
          result = replace(result, backref, numRef);
        }
      }
      return [result, named];
    };
    if (isForced("RegExp", BASE_FORCED)) {
      var RegExpWrapper = function RegExp2(pattern, flags) {
        var thisIsRegExp = isPrototypeOf(RegExpPrototype, this);
        var patternIsRegExp = isRegExp(pattern);
        var flagsAreUndefined = flags === void 0;
        var groups = [];
        var rawPattern = pattern;
        var rawFlags, dotAll, sticky, handled, result, state;
        if (!thisIsRegExp && patternIsRegExp && flagsAreUndefined && pattern.constructor === RegExpWrapper) {
          return pattern;
        }
        if (patternIsRegExp || isPrototypeOf(RegExpPrototype, pattern)) {
          pattern = pattern.source;
          if (flagsAreUndefined) flags = getRegExpFlags(rawPattern);
        }
        pattern = pattern === void 0 ? "" : toString2(pattern);
        flags = flags === void 0 ? "" : toString2(flags);
        rawPattern = pattern;
        if (UNSUPPORTED_DOT_ALL && "dotAll" in re1) {
          dotAll = !!flags && stringIndexOf2(flags, "s") > -1;
          if (dotAll) flags = replace(flags, /s/g, "");
        }
        rawFlags = flags;
        if (MISSED_STICKY && "sticky" in re1) {
          sticky = !!flags && stringIndexOf2(flags, "y") > -1;
          if (sticky && UNSUPPORTED_Y) flags = replace(flags, /y/g, "");
        }
        if (UNSUPPORTED_NCG) {
          handled = handleNCG(pattern);
          pattern = handled[0];
          groups = handled[1];
        }
        result = inheritIfRequired2(NativeRegExp(pattern, flags), thisIsRegExp ? this : RegExpPrototype, RegExpWrapper);
        if (dotAll || sticky || groups.length) {
          state = enforceInternalState(result);
          if (dotAll) {
            state.dotAll = true;
            state.raw = RegExpWrapper(handleDotAll(pattern), rawFlags);
          }
          if (sticky) state.sticky = true;
          if (groups.length) state.groups = groups;
        }
        if (pattern !== rawPattern) try {
          createNonEnumerableProperty2(result, "source", rawPattern === "" ? "(?:)" : rawPattern);
        } catch (error) {
        }
        return result;
      };
      for (var keys = getOwnPropertyNames(NativeRegExp), index = 0; keys.length > index; ) {
        proxyAccessor2(RegExpWrapper, NativeRegExp, keys[index++]);
      }
      RegExpPrototype.constructor = RegExpWrapper;
      RegExpWrapper.prototype = RegExpPrototype;
      defineBuiltIn2(globalThis2, "RegExp", RegExpWrapper, { constructor: true });
    }
    setSpecies2("RegExp");
    return es_regexp_constructor;
  }
  requireEs_regexp_constructor();
  var es_regexp_escape = {};
  var aString;
  var hasRequiredAString;
  function requireAString() {
    if (hasRequiredAString) return aString;
    hasRequiredAString = 1;
    var $TypeError = TypeError;
    aString = function(argument) {
      if (typeof argument == "string") return argument;
      throw new $TypeError("Argument is not a string");
    };
    return aString;
  }
  var stringRepeat;
  var hasRequiredStringRepeat;
  function requireStringRepeat() {
    if (hasRequiredStringRepeat) return stringRepeat;
    hasRequiredStringRepeat = 1;
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toString2 = requireToString();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var $RangeError = RangeError;
    var floor = Math.floor;
    stringRepeat = function repeat(count) {
      var str = toString2(requireObjectCoercible2(this));
      var result = "";
      var n = toIntegerOrInfinity2(count);
      if (n < 0 || n === Infinity) throw new $RangeError("Wrong number of repetitions");
      for (; n > 0; (n = floor(n / 2)) && (str += str)) if (n % 2) result += str;
      return result;
    };
    return stringRepeat;
  }
  var stringPad;
  var hasRequiredStringPad;
  function requireStringPad() {
    if (hasRequiredStringPad) return stringPad;
    hasRequiredStringPad = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var toLength2 = requireToLength();
    var toString2 = requireToString();
    var $repeat = requireStringRepeat();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var repeat = uncurryThis($repeat);
    var stringSlice = uncurryThis("".slice);
    var ceil = Math.ceil;
    var createMethod = function(IS_END) {
      return function($this, maxLength, fillString) {
        var S = toString2(requireObjectCoercible2($this));
        var intMaxLength = toLength2(maxLength);
        var stringLength = S.length;
        if (intMaxLength <= stringLength) return S;
        var fillStr = fillString === void 0 ? " " : toString2(fillString);
        var fillLen, stringFiller;
        if (fillStr === "") return S;
        fillLen = intMaxLength - stringLength;
        stringFiller = repeat(fillStr, ceil(fillLen / fillStr.length));
        if (stringFiller.length > fillLen) stringFiller = stringSlice(stringFiller, 0, fillLen);
        return IS_END ? S + stringFiller : stringFiller + S;
      };
    };
    stringPad = {
      // `String.prototype.padStart` method
      // https://tc39.es/ecma262/#sec-string.prototype.padstart
      start: createMethod(false),
      // `String.prototype.padEnd` method
      // https://tc39.es/ecma262/#sec-string.prototype.padend
      end: createMethod(true)
    };
    return stringPad;
  }
  var whitespaces;
  var hasRequiredWhitespaces;
  function requireWhitespaces() {
    if (hasRequiredWhitespaces) return whitespaces;
    hasRequiredWhitespaces = 1;
    whitespaces = "	\n\v\f\r \xA0\u1680\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028\u2029\uFEFF";
    return whitespaces;
  }
  var hasRequiredEs_regexp_escape;
  function requireEs_regexp_escape() {
    if (hasRequiredEs_regexp_escape) return es_regexp_escape;
    hasRequiredEs_regexp_escape = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var aString2 = requireAString();
    var hasOwn = requireHasOwnProperty();
    var padStart = requireStringPad().start;
    var WHITESPACES = requireWhitespaces();
    var $Array = Array;
    var $escape = RegExp.escape;
    var charAt = uncurryThis("".charAt);
    var charCodeAt = uncurryThis("".charCodeAt);
    var numberToString2 = uncurryThis(1.1.toString);
    var join = uncurryThis([].join);
    var FIRST_DIGIT_OR_ASCII = /^[0-9a-z]/i;
    var SYNTAX_SOLIDUS = /^[$()*+./?[\\\]^{|}]/;
    var OTHER_PUNCTUATORS_AND_WHITESPACES = RegExp("^[!\"#%&',\\-:;<=>@`~" + WHITESPACES + "]");
    var exec = uncurryThis(FIRST_DIGIT_OR_ASCII.exec);
    var ControlEscape = {
      "	": "t",
      "\n": "n",
      "\v": "v",
      "\f": "f",
      "\r": "r"
    };
    var escapeChar = function(chr) {
      var hex = numberToString2(charCodeAt(chr, 0), 16);
      return hex.length < 3 ? "\\x" + padStart(hex, 2, "0") : "\\u" + padStart(hex, 4, "0");
    };
    var FORCED = !$escape || $escape("ab") !== "\\x61b";
    $({ target: "RegExp", stat: true, forced: FORCED }, {
      escape: function escape(S) {
        aString2(S);
        var length = S.length;
        var result = $Array(length);
        for (var i = 0; i < length; i++) {
          var chr = charAt(S, i);
          if (i === 0 && exec(FIRST_DIGIT_OR_ASCII, chr)) {
            result[i] = escapeChar(chr);
          } else if (hasOwn(ControlEscape, chr)) {
            result[i] = "\\" + ControlEscape[chr];
          } else if (exec(SYNTAX_SOLIDUS, chr)) {
            result[i] = "\\" + chr;
          } else if (exec(OTHER_PUNCTUATORS_AND_WHITESPACES, chr)) {
            result[i] = escapeChar(chr);
          } else {
            var charCode = charCodeAt(chr, 0);
            if ((charCode & 63488) !== 55296) result[i] = chr;
            else if (charCode >= 56320 || i + 1 >= length || (charCodeAt(S, i + 1) & 64512) !== 56320) result[i] = escapeChar(chr);
            else {
              result[i] = chr;
              result[++i] = charAt(S, i);
            }
          }
        }
        return join(result, "");
      }
    });
    return es_regexp_escape;
  }
  requireEs_regexp_escape();
  var es_regexp_dotAll = {};
  var hasRequiredEs_regexp_dotAll;
  function requireEs_regexp_dotAll() {
    if (hasRequiredEs_regexp_dotAll) return es_regexp_dotAll;
    hasRequiredEs_regexp_dotAll = 1;
    var DESCRIPTORS = requireDescriptors();
    var UNSUPPORTED_DOT_ALL = requireRegexpUnsupportedDotAll();
    var classof2 = requireClassofRaw();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var getInternalState = requireInternalState().get;
    var RegExpPrototype = RegExp.prototype;
    var $TypeError = TypeError;
    if (DESCRIPTORS && UNSUPPORTED_DOT_ALL) {
      defineBuiltInAccessor2(RegExpPrototype, "dotAll", {
        configurable: true,
        get: function dotAll() {
          if (this === RegExpPrototype) return;
          if (classof2(this) === "RegExp") {
            return !!getInternalState(this).dotAll;
          }
          throw new $TypeError("Incompatible receiver, RegExp required");
        }
      });
    }
    return es_regexp_dotAll;
  }
  requireEs_regexp_dotAll();
  var es_regexp_exec = {};
  var regexpExec;
  var hasRequiredRegexpExec;
  function requireRegexpExec() {
    if (hasRequiredRegexpExec) return regexpExec;
    hasRequiredRegexpExec = 1;
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var toString2 = requireToString();
    var regexpFlags2 = requireRegexpFlags();
    var stickyHelpers = requireRegexpStickyHelpers();
    var shared2 = requireShared();
    var create2 = requireObjectCreate();
    var getInternalState = requireInternalState().get;
    var UNSUPPORTED_DOT_ALL = requireRegexpUnsupportedDotAll();
    var UNSUPPORTED_NCG = requireRegexpUnsupportedNcg();
    var nativeReplace = shared2("native-string-replace", String.prototype.replace);
    var nativeExec = RegExp.prototype.exec;
    var patchedExec = nativeExec;
    var charAt = uncurryThis("".charAt);
    var indexOf = uncurryThis("".indexOf);
    var replace = uncurryThis("".replace);
    var stringSlice = uncurryThis("".slice);
    var UPDATES_LAST_INDEX_WRONG = (function() {
      var re1 = /a/;
      var re2 = /b*/g;
      call(nativeExec, re1, "a");
      call(nativeExec, re2, "a");
      return re1.lastIndex !== 0 || re2.lastIndex !== 0;
    })();
    var UNSUPPORTED_Y = stickyHelpers.BROKEN_CARET;
    var NPCG_INCLUDED = /()??/.exec("")[1] !== void 0;
    var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED || UNSUPPORTED_Y || UNSUPPORTED_DOT_ALL || UNSUPPORTED_NCG;
    var setGroups = function(re, groups) {
      var object = re.groups = create2(null);
      for (var i = 0; i < groups.length; i++) {
        var group = groups[i];
        object[group[0]] = re[group[1]];
      }
    };
    if (PATCH) {
      patchedExec = function exec(string) {
        var re = this;
        var state = getInternalState(re);
        var str = toString2(string);
        var raw = state.raw;
        var result, reCopy, lastIndex;
        if (raw) {
          raw.lastIndex = re.lastIndex;
          result = call(patchedExec, raw, str);
          re.lastIndex = raw.lastIndex;
          if (result && state.groups) setGroups(result, state.groups);
          return result;
        }
        var groups = state.groups;
        var sticky = UNSUPPORTED_Y && re.sticky;
        var flags = call(regexpFlags2, re);
        var source = re.source;
        var charsAdded = 0;
        var strCopy = str;
        if (sticky) {
          flags = replace(flags, "y", "");
          if (indexOf(flags, "g") === -1) {
            flags += "g";
          }
          strCopy = stringSlice(str, re.lastIndex);
          var prevChar = re.lastIndex > 0 && charAt(str, re.lastIndex - 1);
          if (re.lastIndex > 0 && (!re.multiline || re.multiline && prevChar !== "\n" && prevChar !== "\r" && prevChar !== "\u2028" && prevChar !== "\u2029")) {
            source = "(?: (?:" + source + "))";
            strCopy = " " + strCopy;
            charsAdded++;
          }
          reCopy = new RegExp("^(?:" + source + ")", flags);
        }
        if (NPCG_INCLUDED) {
          reCopy = new RegExp("^" + source + "$(?!\\s)", flags);
        }
        if (UPDATES_LAST_INDEX_WRONG) lastIndex = re.lastIndex;
        var match = call(nativeExec, sticky ? reCopy : re, strCopy);
        if (sticky) {
          if (match) {
            match.input = str;
            match[0] = stringSlice(match[0], charsAdded);
            match.index = re.lastIndex;
            re.lastIndex += match[0].length;
          } else re.lastIndex = 0;
        } else if (UPDATES_LAST_INDEX_WRONG && match) {
          re.lastIndex = re.global ? match.index + match[0].length : lastIndex;
        }
        if (NPCG_INCLUDED && match && match.length > 1) {
          call(nativeReplace, match[0], reCopy, function() {
            for (var i = 1; i < arguments.length - 2; i++) {
              if (arguments[i] === void 0) match[i] = void 0;
            }
          });
        }
        if (match && groups) setGroups(match, groups);
        return match;
      };
    }
    regexpExec = patchedExec;
    return regexpExec;
  }
  var hasRequiredEs_regexp_exec;
  function requireEs_regexp_exec() {
    if (hasRequiredEs_regexp_exec) return es_regexp_exec;
    hasRequiredEs_regexp_exec = 1;
    var $ = require_export();
    var exec = requireRegexpExec();
    $({ target: "RegExp", proto: true, forced: /./.exec !== exec }, {
      exec
    });
    return es_regexp_exec;
  }
  requireEs_regexp_exec();
  var es_regexp_flags = {};
  var hasRequiredEs_regexp_flags;
  function requireEs_regexp_flags() {
    if (hasRequiredEs_regexp_flags) return es_regexp_flags;
    hasRequiredEs_regexp_flags = 1;
    var DESCRIPTORS = requireDescriptors();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var regExpFlagsDetection = requireRegexpFlagsDetection();
    var regExpFlagsGetterImplementation = requireRegexpFlags();
    if (DESCRIPTORS && !regExpFlagsDetection.correct) {
      defineBuiltInAccessor2(RegExp.prototype, "flags", {
        configurable: true,
        get: regExpFlagsGetterImplementation
      });
      regExpFlagsDetection.correct = true;
    }
    return es_regexp_flags;
  }
  requireEs_regexp_flags();
  var es_set_difference_v2 = {};
  var setHelpers;
  var hasRequiredSetHelpers;
  function requireSetHelpers() {
    if (hasRequiredSetHelpers) return setHelpers;
    hasRequiredSetHelpers = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var SetPrototype = Set.prototype;
    setHelpers = {
      // eslint-disable-next-line es/no-set -- safe
      Set,
      add: uncurryThis(SetPrototype.add),
      has: uncurryThis(SetPrototype.has),
      remove: uncurryThis(SetPrototype["delete"]),
      proto: SetPrototype
    };
    return setHelpers;
  }
  var aSet;
  var hasRequiredASet;
  function requireASet() {
    if (hasRequiredASet) return aSet;
    hasRequiredASet = 1;
    var has = requireSetHelpers().has;
    aSet = function(it) {
      has(it);
      return it;
    };
    return aSet;
  }
  var iterateSimple;
  var hasRequiredIterateSimple;
  function requireIterateSimple() {
    if (hasRequiredIterateSimple) return iterateSimple;
    hasRequiredIterateSimple = 1;
    var call = requireFunctionCall();
    iterateSimple = function(record, fn, ITERATOR_INSTEAD_OF_RECORD) {
      var iterator = ITERATOR_INSTEAD_OF_RECORD ? record : record.iterator;
      var next = record.next;
      var step, result;
      while (!(step = call(next, iterator)).done) {
        result = fn(step.value);
        if (result !== void 0) return result;
      }
    };
    return iterateSimple;
  }
  var setIterate;
  var hasRequiredSetIterate;
  function requireSetIterate() {
    if (hasRequiredSetIterate) return setIterate;
    hasRequiredSetIterate = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var iterateSimple2 = requireIterateSimple();
    var SetHelpers = requireSetHelpers();
    var Set2 = SetHelpers.Set;
    var SetPrototype = SetHelpers.proto;
    var forEach = uncurryThis(SetPrototype.forEach);
    var keys = uncurryThis(SetPrototype.keys);
    var next = keys(new Set2()).next;
    setIterate = function(set, fn, interruptible) {
      return interruptible ? iterateSimple2({ iterator: keys(set), next }, fn) : forEach(set, fn);
    };
    return setIterate;
  }
  var setClone;
  var hasRequiredSetClone;
  function requireSetClone() {
    if (hasRequiredSetClone) return setClone;
    hasRequiredSetClone = 1;
    var SetHelpers = requireSetHelpers();
    var iterate2 = requireSetIterate();
    var Set2 = SetHelpers.Set;
    var add = SetHelpers.add;
    setClone = function(set) {
      var result = new Set2();
      iterate2(set, function(it) {
        add(result, it);
      });
      return result;
    };
    return setClone;
  }
  var setSize;
  var hasRequiredSetSize;
  function requireSetSize() {
    if (hasRequiredSetSize) return setSize;
    hasRequiredSetSize = 1;
    var uncurryThisAccessor = requireFunctionUncurryThisAccessor();
    var SetHelpers = requireSetHelpers();
    setSize = uncurryThisAccessor(SetHelpers.proto, "size", "get") || function(set) {
      return set.size;
    };
    return setSize;
  }
  var getSetRecord;
  var hasRequiredGetSetRecord;
  function requireGetSetRecord() {
    if (hasRequiredGetSetRecord) return getSetRecord;
    hasRequiredGetSetRecord = 1;
    var aCallable2 = requireACallable();
    var anObject2 = requireAnObject();
    var call = requireFunctionCall();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var getIteratorDirect2 = requireGetIteratorDirect();
    var INVALID_SIZE = "Invalid size";
    var $RangeError = RangeError;
    var $TypeError = TypeError;
    var max = Math.max;
    var SetRecord = function(set, intSize) {
      this.set = set;
      this.size = max(intSize, 0);
      this.has = aCallable2(set.has);
      this.keys = aCallable2(set.keys);
    };
    SetRecord.prototype = {
      getIterator: function() {
        return getIteratorDirect2(anObject2(call(this.keys, this.set)));
      },
      includes: function(it) {
        return call(this.has, this.set, it);
      }
    };
    getSetRecord = function(obj) {
      anObject2(obj);
      var numSize = +obj.size;
      if (numSize !== numSize) throw new $TypeError(INVALID_SIZE);
      var intSize = toIntegerOrInfinity2(numSize);
      if (intSize < 0) throw new $RangeError(INVALID_SIZE);
      return new SetRecord(obj, intSize);
    };
    return getSetRecord;
  }
  var setDifference;
  var hasRequiredSetDifference;
  function requireSetDifference() {
    if (hasRequiredSetDifference) return setDifference;
    hasRequiredSetDifference = 1;
    var aSet2 = requireASet();
    var SetHelpers = requireSetHelpers();
    var clone2 = requireSetClone();
    var size = requireSetSize();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSet = requireSetIterate();
    var iterateSimple2 = requireIterateSimple();
    var has = SetHelpers.has;
    var remove = SetHelpers.remove;
    setDifference = function difference(other) {
      var O = aSet2(this);
      var otherRec = getSetRecord2(other);
      var result = clone2(O);
      if (size(result) <= otherRec.size) iterateSet(result, function(e) {
        if (otherRec.includes(e)) remove(result, e);
      });
      else iterateSimple2(otherRec.getIterator(), function(e) {
        if (has(result, e)) remove(result, e);
      });
      return result;
    };
    return setDifference;
  }
  var setMethodAcceptSetLike;
  var hasRequiredSetMethodAcceptSetLike;
  function requireSetMethodAcceptSetLike() {
    if (hasRequiredSetMethodAcceptSetLike) return setMethodAcceptSetLike;
    hasRequiredSetMethodAcceptSetLike = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var createSetLike = function(size) {
      return {
        size,
        has: function() {
          return false;
        },
        keys: function() {
          return {
            next: function() {
              return { done: true };
            }
          };
        }
      };
    };
    var createSetLikeWithInfinitySize = function(size) {
      return {
        size,
        has: function() {
          return true;
        },
        keys: function() {
          throw new Error("e");
        }
      };
    };
    setMethodAcceptSetLike = function(name, callback) {
      var Set2 = getBuiltIn2("Set");
      try {
        new Set2()[name](createSetLike(0));
        try {
          new Set2()[name](createSetLike(-1));
          return false;
        } catch (error2) {
          if (!callback) return true;
          try {
            new Set2()[name](createSetLikeWithInfinitySize(-Infinity));
            return false;
          } catch (error) {
            var set = new Set2([1, 2]);
            return callback(set[name](createSetLikeWithInfinitySize(Infinity)));
          }
        }
      } catch (error) {
        return false;
      }
    };
    return setMethodAcceptSetLike;
  }
  var hasRequiredEs_set_difference_v2;
  function requireEs_set_difference_v2() {
    if (hasRequiredEs_set_difference_v2) return es_set_difference_v2;
    hasRequiredEs_set_difference_v2 = 1;
    var $ = require_export();
    var difference = requireSetDifference();
    var fails2 = requireFails();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var SET_LIKE_INCORRECT_BEHAVIOR = !setMethodAcceptSetLike2("difference", function(result) {
      return result.size === 0;
    });
    var FORCED = SET_LIKE_INCORRECT_BEHAVIOR || fails2(function() {
      var setLike = {
        size: 1,
        has: function() {
          return true;
        },
        keys: function() {
          var index = 0;
          return {
            next: function() {
              var done = index++ > 1;
              if (baseSet.has(1)) baseSet.clear();
              return { done, value: 2 };
            }
          };
        }
      };
      var baseSet = /* @__PURE__ */ new Set([1, 2, 3, 4]);
      return baseSet.difference(setLike).size !== 3;
    });
    $({ target: "Set", proto: true, real: true, forced: FORCED }, {
      difference
    });
    return es_set_difference_v2;
  }
  requireEs_set_difference_v2();
  var es_set_intersection_v2 = {};
  var setIntersection;
  var hasRequiredSetIntersection;
  function requireSetIntersection() {
    if (hasRequiredSetIntersection) return setIntersection;
    hasRequiredSetIntersection = 1;
    var aSet2 = requireASet();
    var SetHelpers = requireSetHelpers();
    var size = requireSetSize();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSet = requireSetIterate();
    var iterateSimple2 = requireIterateSimple();
    var Set2 = SetHelpers.Set;
    var add = SetHelpers.add;
    var has = SetHelpers.has;
    setIntersection = function intersection(other) {
      var O = aSet2(this);
      var otherRec = getSetRecord2(other);
      var result = new Set2();
      if (size(O) > otherRec.size) {
        iterateSimple2(otherRec.getIterator(), function(e) {
          if (has(O, e)) add(result, e);
        });
      } else {
        iterateSet(O, function(e) {
          if (otherRec.includes(e)) add(result, e);
        });
      }
      return result;
    };
    return setIntersection;
  }
  var hasRequiredEs_set_intersection_v2;
  function requireEs_set_intersection_v2() {
    if (hasRequiredEs_set_intersection_v2) return es_set_intersection_v2;
    hasRequiredEs_set_intersection_v2 = 1;
    var $ = require_export();
    var fails2 = requireFails();
    var intersection = requireSetIntersection();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var INCORRECT = !setMethodAcceptSetLike2("intersection", function(result) {
      return result.size === 2 && result.has(1) && result.has(2);
    }) || fails2(function() {
      return String(Array.from((/* @__PURE__ */ new Set([1, 2, 3])).intersection(/* @__PURE__ */ new Set([3, 2])))) !== "3,2";
    });
    $({ target: "Set", proto: true, real: true, forced: INCORRECT }, {
      intersection
    });
    return es_set_intersection_v2;
  }
  requireEs_set_intersection_v2();
  var es_set_isDisjointFrom_v2 = {};
  var setIsDisjointFrom;
  var hasRequiredSetIsDisjointFrom;
  function requireSetIsDisjointFrom() {
    if (hasRequiredSetIsDisjointFrom) return setIsDisjointFrom;
    hasRequiredSetIsDisjointFrom = 1;
    var aSet2 = requireASet();
    var has = requireSetHelpers().has;
    var size = requireSetSize();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSet = requireSetIterate();
    var iterateSimple2 = requireIterateSimple();
    var iteratorClose2 = requireIteratorClose();
    setIsDisjointFrom = function isDisjointFrom(other) {
      var O = aSet2(this);
      var otherRec = getSetRecord2(other);
      if (size(O) <= otherRec.size) return iterateSet(O, function(e) {
        if (otherRec.includes(e)) return false;
      }, true) !== false;
      var iterator = otherRec.getIterator();
      return iterateSimple2(iterator, function(e) {
        if (has(O, e)) return iteratorClose2(iterator.iterator, "normal", false);
      }) !== false;
    };
    return setIsDisjointFrom;
  }
  var hasRequiredEs_set_isDisjointFrom_v2;
  function requireEs_set_isDisjointFrom_v2() {
    if (hasRequiredEs_set_isDisjointFrom_v2) return es_set_isDisjointFrom_v2;
    hasRequiredEs_set_isDisjointFrom_v2 = 1;
    var $ = require_export();
    var isDisjointFrom = requireSetIsDisjointFrom();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var INCORRECT = !setMethodAcceptSetLike2("isDisjointFrom", function(result) {
      return !result;
    });
    $({ target: "Set", proto: true, real: true, forced: INCORRECT }, {
      isDisjointFrom
    });
    return es_set_isDisjointFrom_v2;
  }
  requireEs_set_isDisjointFrom_v2();
  var es_set_isSubsetOf_v2 = {};
  var setIsSubsetOf;
  var hasRequiredSetIsSubsetOf;
  function requireSetIsSubsetOf() {
    if (hasRequiredSetIsSubsetOf) return setIsSubsetOf;
    hasRequiredSetIsSubsetOf = 1;
    var aSet2 = requireASet();
    var size = requireSetSize();
    var iterate2 = requireSetIterate();
    var getSetRecord2 = requireGetSetRecord();
    setIsSubsetOf = function isSubsetOf(other) {
      var O = aSet2(this);
      var otherRec = getSetRecord2(other);
      if (size(O) > otherRec.size) return false;
      return iterate2(O, function(e) {
        if (!otherRec.includes(e)) return false;
      }, true) !== false;
    };
    return setIsSubsetOf;
  }
  var hasRequiredEs_set_isSubsetOf_v2;
  function requireEs_set_isSubsetOf_v2() {
    if (hasRequiredEs_set_isSubsetOf_v2) return es_set_isSubsetOf_v2;
    hasRequiredEs_set_isSubsetOf_v2 = 1;
    var $ = require_export();
    var isSubsetOf = requireSetIsSubsetOf();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var INCORRECT = !setMethodAcceptSetLike2("isSubsetOf", function(result) {
      return result;
    });
    $({ target: "Set", proto: true, real: true, forced: INCORRECT }, {
      isSubsetOf
    });
    return es_set_isSubsetOf_v2;
  }
  requireEs_set_isSubsetOf_v2();
  var es_set_isSupersetOf_v2 = {};
  var setIsSupersetOf;
  var hasRequiredSetIsSupersetOf;
  function requireSetIsSupersetOf() {
    if (hasRequiredSetIsSupersetOf) return setIsSupersetOf;
    hasRequiredSetIsSupersetOf = 1;
    var aSet2 = requireASet();
    var has = requireSetHelpers().has;
    var size = requireSetSize();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSimple2 = requireIterateSimple();
    var iteratorClose2 = requireIteratorClose();
    setIsSupersetOf = function isSupersetOf(other) {
      var O = aSet2(this);
      var otherRec = getSetRecord2(other);
      if (size(O) < otherRec.size) return false;
      var iterator = otherRec.getIterator();
      return iterateSimple2(iterator, function(e) {
        if (!has(O, e)) return iteratorClose2(iterator.iterator, "normal", false);
      }) !== false;
    };
    return setIsSupersetOf;
  }
  var hasRequiredEs_set_isSupersetOf_v2;
  function requireEs_set_isSupersetOf_v2() {
    if (hasRequiredEs_set_isSupersetOf_v2) return es_set_isSupersetOf_v2;
    hasRequiredEs_set_isSupersetOf_v2 = 1;
    var $ = require_export();
    var isSupersetOf = requireSetIsSupersetOf();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var INCORRECT = !setMethodAcceptSetLike2("isSupersetOf", function(result) {
      return !result;
    });
    $({ target: "Set", proto: true, real: true, forced: INCORRECT }, {
      isSupersetOf
    });
    return es_set_isSupersetOf_v2;
  }
  requireEs_set_isSupersetOf_v2();
  var es_set_symmetricDifference_v2 = {};
  var setSymmetricDifference;
  var hasRequiredSetSymmetricDifference;
  function requireSetSymmetricDifference() {
    if (hasRequiredSetSymmetricDifference) return setSymmetricDifference;
    hasRequiredSetSymmetricDifference = 1;
    var aSet2 = requireASet();
    var SetHelpers = requireSetHelpers();
    var clone2 = requireSetClone();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSimple2 = requireIterateSimple();
    var add = SetHelpers.add;
    var has = SetHelpers.has;
    var remove = SetHelpers.remove;
    setSymmetricDifference = function symmetricDifference(other) {
      var O = aSet2(this);
      var keysIter = getSetRecord2(other).getIterator();
      var result = clone2(O);
      iterateSimple2(keysIter, function(e) {
        if (has(O, e)) remove(result, e);
        else add(result, e);
      });
      return result;
    };
    return setSymmetricDifference;
  }
  var setMethodGetKeysBeforeCloningDetection;
  var hasRequiredSetMethodGetKeysBeforeCloningDetection;
  function requireSetMethodGetKeysBeforeCloningDetection() {
    if (hasRequiredSetMethodGetKeysBeforeCloningDetection) return setMethodGetKeysBeforeCloningDetection;
    hasRequiredSetMethodGetKeysBeforeCloningDetection = 1;
    setMethodGetKeysBeforeCloningDetection = function(METHOD_NAME) {
      try {
        var baseSet = /* @__PURE__ */ new Set();
        var setLike = {
          size: 0,
          has: function() {
            return true;
          },
          keys: function() {
            return Object.defineProperty({}, "next", {
              get: function() {
                baseSet.clear();
                baseSet.add(4);
                return function() {
                  return { done: true };
                };
              }
            });
          }
        };
        var result = baseSet[METHOD_NAME](setLike);
        return result.size === 1 && result.values().next().value === 4;
      } catch (error) {
        return false;
      }
    };
    return setMethodGetKeysBeforeCloningDetection;
  }
  var hasRequiredEs_set_symmetricDifference_v2;
  function requireEs_set_symmetricDifference_v2() {
    if (hasRequiredEs_set_symmetricDifference_v2) return es_set_symmetricDifference_v2;
    hasRequiredEs_set_symmetricDifference_v2 = 1;
    var $ = require_export();
    var symmetricDifference = requireSetSymmetricDifference();
    var setMethodGetKeysBeforeCloning = requireSetMethodGetKeysBeforeCloningDetection();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var FORCED = !setMethodAcceptSetLike2("symmetricDifference") || !setMethodGetKeysBeforeCloning("symmetricDifference");
    $({ target: "Set", proto: true, real: true, forced: FORCED }, {
      symmetricDifference
    });
    return es_set_symmetricDifference_v2;
  }
  requireEs_set_symmetricDifference_v2();
  var es_set_union_v2 = {};
  var setUnion;
  var hasRequiredSetUnion;
  function requireSetUnion() {
    if (hasRequiredSetUnion) return setUnion;
    hasRequiredSetUnion = 1;
    var aSet2 = requireASet();
    var add = requireSetHelpers().add;
    var clone2 = requireSetClone();
    var getSetRecord2 = requireGetSetRecord();
    var iterateSimple2 = requireIterateSimple();
    setUnion = function union(other) {
      var O = aSet2(this);
      var keysIter = getSetRecord2(other).getIterator();
      var result = clone2(O);
      iterateSimple2(keysIter, function(it) {
        add(result, it);
      });
      return result;
    };
    return setUnion;
  }
  var hasRequiredEs_set_union_v2;
  function requireEs_set_union_v2() {
    if (hasRequiredEs_set_union_v2) return es_set_union_v2;
    hasRequiredEs_set_union_v2 = 1;
    var $ = require_export();
    var union = requireSetUnion();
    var setMethodGetKeysBeforeCloning = requireSetMethodGetKeysBeforeCloningDetection();
    var setMethodAcceptSetLike2 = requireSetMethodAcceptSetLike();
    var FORCED = !setMethodAcceptSetLike2("union") || !setMethodGetKeysBeforeCloning("union");
    $({ target: "Set", proto: true, real: true, forced: FORCED }, {
      union
    });
    return es_set_union_v2;
  }
  requireEs_set_union_v2();
  var es_string_atAlternative = {};
  var hasRequiredEs_string_atAlternative;
  function requireEs_string_atAlternative() {
    if (hasRequiredEs_string_atAlternative) return es_string_atAlternative;
    hasRequiredEs_string_atAlternative = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toString2 = requireToString();
    var fails2 = requireFails();
    var charAt = uncurryThis("".charAt);
    var FORCED = fails2(function() {
      return "\u{20BB7}".at(-2) !== "\uD842";
    });
    $({ target: "String", proto: true, forced: FORCED }, {
      at: function at(index) {
        var S = toString2(requireObjectCoercible2(this));
        var len = S.length;
        var relativeIndex = toIntegerOrInfinity2(index);
        var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
        return k < 0 || k >= len ? void 0 : charAt(S, k);
      }
    });
    return es_string_atAlternative;
  }
  requireEs_string_atAlternative();
  var es_string_isWellFormed = {};
  var hasRequiredEs_string_isWellFormed;
  function requireEs_string_isWellFormed() {
    if (hasRequiredEs_string_isWellFormed) return es_string_isWellFormed;
    hasRequiredEs_string_isWellFormed = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toString2 = requireToString();
    var charCodeAt = uncurryThis("".charCodeAt);
    $({ target: "String", proto: true }, {
      isWellFormed: function isWellFormed() {
        var S = toString2(requireObjectCoercible2(this));
        var length = S.length;
        for (var i = 0; i < length; i++) {
          var charCode = charCodeAt(S, i);
          if ((charCode & 63488) !== 55296) continue;
          if (charCode >= 56320 || ++i >= length || (charCodeAt(S, i) & 64512) !== 56320) return false;
        }
        return true;
      }
    });
    return es_string_isWellFormed;
  }
  requireEs_string_isWellFormed();
  var es_string_matchAll = {};
  var iteratorCreateConstructor;
  var hasRequiredIteratorCreateConstructor;
  function requireIteratorCreateConstructor() {
    if (hasRequiredIteratorCreateConstructor) return iteratorCreateConstructor;
    hasRequiredIteratorCreateConstructor = 1;
    var IteratorPrototype = requireIteratorsCore().IteratorPrototype;
    var create2 = requireObjectCreate();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var setToStringTag2 = requireSetToStringTag();
    var Iterators = requireIterators();
    var returnThis = function() {
      return this;
    };
    iteratorCreateConstructor = function(IteratorConstructor, NAME, next, ENUMERABLE_NEXT) {
      var TO_STRING_TAG = NAME + " Iterator";
      IteratorConstructor.prototype = create2(IteratorPrototype, { next: createPropertyDescriptor2(+!ENUMERABLE_NEXT, next) });
      setToStringTag2(IteratorConstructor, TO_STRING_TAG, false, true);
      Iterators[TO_STRING_TAG] = returnThis;
      return IteratorConstructor;
    };
    return iteratorCreateConstructor;
  }
  var stringMultibyte;
  var hasRequiredStringMultibyte;
  function requireStringMultibyte() {
    if (hasRequiredStringMultibyte) return stringMultibyte;
    hasRequiredStringMultibyte = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toString2 = requireToString();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var charAt = uncurryThis("".charAt);
    var charCodeAt = uncurryThis("".charCodeAt);
    var stringSlice = uncurryThis("".slice);
    var createMethod = function(CONVERT_TO_STRING) {
      return function($this, pos) {
        var S = toString2(requireObjectCoercible2($this));
        var position = toIntegerOrInfinity2(pos);
        var size = S.length;
        var first, second;
        if (position < 0 || position >= size) return CONVERT_TO_STRING ? "" : void 0;
        first = charCodeAt(S, position);
        return first < 55296 || first > 56319 || position + 1 === size || (second = charCodeAt(S, position + 1)) < 56320 || second > 57343 ? CONVERT_TO_STRING ? charAt(S, position) : first : CONVERT_TO_STRING ? stringSlice(S, position, position + 2) : (first - 55296 << 10) + (second - 56320) + 65536;
      };
    };
    stringMultibyte = {
      // `String.prototype.codePointAt` method
      // https://tc39.es/ecma262/#sec-string.prototype.codepointat
      codeAt: createMethod(false),
      // `String.prototype.at` method
      // https://github.com/mathiasbynens/String.prototype.at
      charAt: createMethod(true)
    };
    return stringMultibyte;
  }
  var advanceStringIndex;
  var hasRequiredAdvanceStringIndex;
  function requireAdvanceStringIndex() {
    if (hasRequiredAdvanceStringIndex) return advanceStringIndex;
    hasRequiredAdvanceStringIndex = 1;
    var charAt = requireStringMultibyte().charAt;
    advanceStringIndex = function(S, index, unicode) {
      return index + (unicode ? charAt(S, index).length || 1 : 1);
    };
    return advanceStringIndex;
  }
  var regexpExecAbstract;
  var hasRequiredRegexpExecAbstract;
  function requireRegexpExecAbstract() {
    if (hasRequiredRegexpExecAbstract) return regexpExecAbstract;
    hasRequiredRegexpExecAbstract = 1;
    var call = requireFunctionCall();
    var anObject2 = requireAnObject();
    var isCallable2 = requireIsCallable();
    var classof2 = requireClassofRaw();
    var regexpExec2 = requireRegexpExec();
    var $TypeError = TypeError;
    regexpExecAbstract = function(R, S) {
      var exec = R.exec;
      if (isCallable2(exec)) {
        var result = call(exec, R, S);
        if (result !== null) anObject2(result);
        return result;
      }
      if (classof2(R) === "RegExp") return call(regexpExec2, R, S);
      throw new $TypeError("RegExp#exec called on incompatible receiver");
    };
    return regexpExecAbstract;
  }
  var hasRequiredEs_string_matchAll;
  function requireEs_string_matchAll() {
    if (hasRequiredEs_string_matchAll) return es_string_matchAll;
    hasRequiredEs_string_matchAll = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThisClause();
    var createIteratorConstructor = requireIteratorCreateConstructor();
    var createIterResultObject2 = requireCreateIterResultObject();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toLength2 = requireToLength();
    var toString2 = requireToString();
    var anObject2 = requireAnObject();
    var isObject2 = requireIsObject();
    var classof2 = requireClassofRaw();
    var isRegExp = requireIsRegexp();
    var getRegExpFlags = requireRegexpGetFlags();
    var getMethod2 = requireGetMethod();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var fails2 = requireFails();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var speciesConstructor2 = requireSpeciesConstructor();
    var advanceStringIndex2 = requireAdvanceStringIndex();
    var regExpExec = requireRegexpExecAbstract();
    var InternalStateModule = requireInternalState();
    var IS_PURE = requireIsPure();
    var MATCH_ALL = wellKnownSymbol2("matchAll");
    var REGEXP_STRING = "RegExp String";
    var REGEXP_STRING_ITERATOR = REGEXP_STRING + " Iterator";
    var setInternalState = InternalStateModule.set;
    var getInternalState = InternalStateModule.getterFor(REGEXP_STRING_ITERATOR);
    var RegExpPrototype = RegExp.prototype;
    var $TypeError = TypeError;
    var stringIndexOf2 = uncurryThis("".indexOf);
    var nativeMatchAll = uncurryThis("".matchAll);
    var WORKS_WITH_NON_GLOBAL_REGEX = !!nativeMatchAll && !fails2(function() {
      nativeMatchAll("a", /./);
    });
    var $RegExpStringIterator = createIteratorConstructor(function RegExpStringIterator(regexp, string, $global, fullUnicode) {
      setInternalState(this, {
        type: REGEXP_STRING_ITERATOR,
        regexp,
        string,
        global: $global,
        unicode: fullUnicode,
        done: false
      });
    }, REGEXP_STRING, function next() {
      var state = getInternalState(this);
      if (state.done) return createIterResultObject2(void 0, true);
      var R = state.regexp;
      var S = state.string;
      var match = regExpExec(R, S);
      if (match === null) {
        state.done = true;
        return createIterResultObject2(void 0, true);
      }
      if (state.global) {
        if (toString2(match[0]) === "") R.lastIndex = advanceStringIndex2(S, toLength2(R.lastIndex), state.unicode);
        return createIterResultObject2(match, false);
      }
      state.done = true;
      return createIterResultObject2(match, false);
    });
    var $matchAll = function(string) {
      var R = anObject2(this);
      var S = toString2(string);
      var C = speciesConstructor2(R, RegExp);
      var flags = toString2(getRegExpFlags(R));
      var matcher, $global, fullUnicode;
      matcher = new C(C === RegExp ? R.source : R, flags);
      $global = !!~stringIndexOf2(flags, "g");
      fullUnicode = !!~stringIndexOf2(flags, "u") || !!~stringIndexOf2(flags, "v");
      matcher.lastIndex = toLength2(R.lastIndex);
      return new $RegExpStringIterator(matcher, S, $global, fullUnicode);
    };
    $({ target: "String", proto: true, forced: WORKS_WITH_NON_GLOBAL_REGEX }, {
      matchAll: function matchAll(regexp) {
        var O = requireObjectCoercible2(this);
        var flags, S, matcher, rx;
        if (isObject2(regexp)) {
          if (isRegExp(regexp)) {
            flags = toString2(requireObjectCoercible2(getRegExpFlags(regexp)));
            if (!~stringIndexOf2(flags, "g")) throw new $TypeError("`.matchAll` does not allow non-global regexes");
          }
          if (WORKS_WITH_NON_GLOBAL_REGEX) return nativeMatchAll(O, regexp);
          matcher = getMethod2(regexp, MATCH_ALL);
          if (matcher === void 0 && IS_PURE && classof2(regexp) === "RegExp") matcher = $matchAll;
          if (matcher) return call(matcher, regexp, O);
        } else if (WORKS_WITH_NON_GLOBAL_REGEX) return nativeMatchAll(O, regexp);
        S = toString2(O);
        rx = new RegExp(regexp, "g");
        return IS_PURE ? call($matchAll, rx, S) : rx[MATCH_ALL](S);
      }
    });
    IS_PURE || MATCH_ALL in RegExpPrototype || defineBuiltIn2(RegExpPrototype, MATCH_ALL, $matchAll);
    return es_string_matchAll;
  }
  requireEs_string_matchAll();
  var es_string_replace = {};
  var fixRegexpWellKnownSymbolLogic;
  var hasRequiredFixRegexpWellKnownSymbolLogic;
  function requireFixRegexpWellKnownSymbolLogic() {
    if (hasRequiredFixRegexpWellKnownSymbolLogic) return fixRegexpWellKnownSymbolLogic;
    hasRequiredFixRegexpWellKnownSymbolLogic = 1;
    requireEs_regexp_exec();
    var call = requireFunctionCall();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var regexpExec2 = requireRegexpExec();
    var fails2 = requireFails();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var SPECIES = wellKnownSymbol2("species");
    var RegExpPrototype = RegExp.prototype;
    fixRegexpWellKnownSymbolLogic = function(KEY, exec, FORCED, SHAM) {
      var SYMBOL = wellKnownSymbol2(KEY);
      var DELEGATES_TO_SYMBOL = !fails2(function() {
        var O = {};
        O[SYMBOL] = function() {
          return 7;
        };
        return ""[KEY](O) !== 7;
      });
      var DELEGATES_TO_EXEC = DELEGATES_TO_SYMBOL && !fails2(function() {
        var execCalled = false;
        var re = /a/;
        if (KEY === "split") {
          var constructor = {};
          constructor[SPECIES] = function() {
            return re;
          };
          re = { constructor, flags: "" };
          re[SYMBOL] = /./[SYMBOL];
        }
        re.exec = function() {
          execCalled = true;
          return null;
        };
        re[SYMBOL]("");
        return !execCalled;
      });
      if (!DELEGATES_TO_SYMBOL || !DELEGATES_TO_EXEC || FORCED) {
        var nativeRegExpMethod = /./[SYMBOL];
        var methods = exec(SYMBOL, ""[KEY], function(nativeMethod, regexp, str, arg2, forceStringMethod) {
          var $exec = regexp.exec;
          if ($exec === regexpExec2 || $exec === RegExpPrototype.exec) {
            if (DELEGATES_TO_SYMBOL && !forceStringMethod) {
              return { done: true, value: call(nativeRegExpMethod, regexp, str, arg2) };
            }
            return { done: true, value: call(nativeMethod, str, regexp, arg2) };
          }
          return { done: false };
        });
        defineBuiltIn2(String.prototype, KEY, methods[0]);
        defineBuiltIn2(RegExpPrototype, SYMBOL, methods[1]);
      }
      if (SHAM) createNonEnumerableProperty2(RegExpPrototype[SYMBOL], "sham", true);
    };
    return fixRegexpWellKnownSymbolLogic;
  }
  var getSubstitution;
  var hasRequiredGetSubstitution;
  function requireGetSubstitution() {
    if (hasRequiredGetSubstitution) return getSubstitution;
    hasRequiredGetSubstitution = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var toObject2 = requireToObject();
    var floor = Math.floor;
    var charAt = uncurryThis("".charAt);
    var replace = uncurryThis("".replace);
    var stringSlice = uncurryThis("".slice);
    var SUBSTITUTION_SYMBOLS = /\$([$&'`]|\d{1,2}|<[^>]*>)/g;
    var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&'`]|\d{1,2})/g;
    getSubstitution = function(matched, str, position, captures, namedCaptures, replacement) {
      var tailPos = position + matched.length;
      var m = captures.length;
      var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;
      if (namedCaptures !== void 0) {
        namedCaptures = toObject2(namedCaptures);
        symbols = SUBSTITUTION_SYMBOLS;
      }
      return replace(replacement, symbols, function(match, ch) {
        var capture;
        switch (charAt(ch, 0)) {
          case "$":
            return "$";
          case "&":
            return matched;
          case "`":
            return stringSlice(str, 0, position);
          case "'":
            return stringSlice(str, tailPos);
          case "<":
            capture = namedCaptures[stringSlice(ch, 1, -1)];
            break;
          default:
            var n = +ch;
            if (n === 0) return match;
            if (n > m) {
              var f = floor(n / 10);
              if (f === 0) return match;
              if (f <= m) return captures[f - 1] === void 0 ? charAt(ch, 1) : captures[f - 1] + charAt(ch, 1);
              return match;
            }
            capture = captures[n - 1];
        }
        return capture === void 0 ? "" : capture;
      });
    };
    return getSubstitution;
  }
  var hasRequiredEs_string_replace;
  function requireEs_string_replace() {
    if (hasRequiredEs_string_replace) return es_string_replace;
    hasRequiredEs_string_replace = 1;
    var apply2 = requireFunctionApply();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var fixRegExpWellKnownSymbolLogic = requireFixRegexpWellKnownSymbolLogic();
    var fails2 = requireFails();
    var anObject2 = requireAnObject();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toLength2 = requireToLength();
    var toString2 = requireToString();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var advanceStringIndex2 = requireAdvanceStringIndex();
    var getMethod2 = requireGetMethod();
    var getSubstitution2 = requireGetSubstitution();
    var getRegExpFlags = requireRegexpGetFlags();
    var regExpExec = requireRegexpExecAbstract();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var REPLACE = wellKnownSymbol2("replace");
    var max = Math.max;
    var min = Math.min;
    var concat = uncurryThis([].concat);
    var push = uncurryThis([].push);
    var stringIndexOf2 = uncurryThis("".indexOf);
    var stringSlice = uncurryThis("".slice);
    var maybeToString = function(it) {
      return it === void 0 ? it : String(it);
    };
    var REPLACE_KEEPS_$0 = (function() {
      return "a".replace(/./, "$0") === "$0";
    })();
    var REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE = (function() {
      if (/./[REPLACE]) {
        return /./[REPLACE]("a", "$0") === "";
      }
      return false;
    })();
    var REPLACE_SUPPORTS_NAMED_GROUPS = !fails2(function() {
      var re = /./;
      re.exec = function() {
        var result = [];
        result.groups = { a: "7" };
        return result;
      };
      return "".replace(re, "$<a>") !== "7";
    });
    fixRegExpWellKnownSymbolLogic("replace", function(_, nativeReplace, maybeCallNative) {
      var UNSAFE_SUBSTITUTE = REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE ? "$" : "$0";
      return [
        // `String.prototype.replace` method
        // https://tc39.es/ecma262/#sec-string.prototype.replace
        function replace(searchValue, replaceValue) {
          var O = requireObjectCoercible2(this);
          var replacer = isObject2(searchValue) ? getMethod2(searchValue, REPLACE) : void 0;
          return replacer ? call(replacer, searchValue, O, replaceValue) : call(nativeReplace, toString2(O), searchValue, replaceValue);
        },
        // `RegExp.prototype[@@replace]` method
        // https://tc39.es/ecma262/#sec-regexp.prototype-@@replace
        function(string, replaceValue) {
          var rx = anObject2(this);
          var S = toString2(string);
          var functionalReplace = isCallable2(replaceValue);
          if (!functionalReplace) replaceValue = toString2(replaceValue);
          var flags = toString2(getRegExpFlags(rx));
          if (typeof replaceValue == "string" && !~stringIndexOf2(replaceValue, UNSAFE_SUBSTITUTE) && !~stringIndexOf2(replaceValue, "$<") && !~stringIndexOf2(flags, "y")) {
            var res = maybeCallNative(nativeReplace, rx, S, replaceValue);
            if (res.done) return res.value;
          }
          var global2 = !!~stringIndexOf2(flags, "g");
          var fullUnicode;
          if (global2) {
            fullUnicode = !!~stringIndexOf2(flags, "u") || !!~stringIndexOf2(flags, "v");
            rx.lastIndex = 0;
          }
          var results = [];
          var result;
          while (true) {
            result = regExpExec(rx, S);
            if (result === null) break;
            push(results, result);
            if (!global2) break;
            var matchStr = toString2(result[0]);
            if (matchStr === "") rx.lastIndex = advanceStringIndex2(S, toLength2(rx.lastIndex), fullUnicode);
          }
          var accumulatedResult = "";
          var nextSourcePosition = 0;
          for (var i = 0; i < results.length; i++) {
            result = results[i];
            var matched = toString2(result[0]);
            var position = max(min(toIntegerOrInfinity2(result.index), S.length), 0);
            var captures = [];
            var replacement;
            for (var j = 1; j < result.length; j++) push(captures, maybeToString(result[j]));
            var namedCaptures = result.groups;
            if (functionalReplace) {
              var replacerArgs = concat([matched], captures, position, S);
              if (namedCaptures !== void 0) push(replacerArgs, namedCaptures);
              replacement = toString2(apply2(replaceValue, void 0, replacerArgs));
            } else {
              replacement = getSubstitution2(matched, S, position, captures, namedCaptures, replaceValue);
            }
            if (position >= nextSourcePosition) {
              accumulatedResult += stringSlice(S, nextSourcePosition, position) + replacement;
              nextSourcePosition = position + matched.length;
            }
          }
          return accumulatedResult + stringSlice(S, nextSourcePosition);
        }
      ];
    }, !REPLACE_SUPPORTS_NAMED_GROUPS || !REPLACE_KEEPS_$0 || REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE);
    return es_string_replace;
  }
  requireEs_string_replace();
  var es_string_replaceAll = {};
  var hasRequiredEs_string_replaceAll;
  function requireEs_string_replaceAll() {
    if (hasRequiredEs_string_replaceAll) return es_string_replaceAll;
    hasRequiredEs_string_replaceAll = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var isRegExp = requireIsRegexp();
    var toString2 = requireToString();
    var getMethod2 = requireGetMethod();
    var getRegExpFlags = requireRegexpGetFlags();
    var getSubstitution2 = requireGetSubstitution();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var IS_PURE = requireIsPure();
    var REPLACE = wellKnownSymbol2("replace");
    var $TypeError = TypeError;
    var indexOf = uncurryThis("".indexOf);
    var replace = uncurryThis("".replace);
    var stringSlice = uncurryThis("".slice);
    var max = Math.max;
    $({ target: "String", proto: true }, {
      replaceAll: function replaceAll(searchValue, replaceValue) {
        var O = requireObjectCoercible2(this);
        var IS_REG_EXP, flags, replacer, string, searchString, functionalReplace, searchLength, advanceBy, position, replacement;
        var endOfLastMatch = 0;
        var result = "";
        if (isObject2(searchValue)) {
          IS_REG_EXP = isRegExp(searchValue);
          if (IS_REG_EXP) {
            flags = toString2(requireObjectCoercible2(getRegExpFlags(searchValue)));
            if (!~indexOf(flags, "g")) throw new $TypeError("`.replaceAll` does not allow non-global regexes");
          }
          replacer = getMethod2(searchValue, REPLACE);
          if (replacer) return call(replacer, searchValue, O, replaceValue);
          if (IS_PURE && IS_REG_EXP) return replace(toString2(O), searchValue, replaceValue);
        }
        string = toString2(O);
        searchString = toString2(searchValue);
        functionalReplace = isCallable2(replaceValue);
        if (!functionalReplace) replaceValue = toString2(replaceValue);
        searchLength = searchString.length;
        advanceBy = max(1, searchLength);
        position = indexOf(string, searchString);
        while (position !== -1) {
          replacement = functionalReplace ? toString2(replaceValue(searchString, position, string)) : getSubstitution2(searchString, string, position, [], void 0, replaceValue);
          result += stringSlice(string, endOfLastMatch, position) + replacement;
          endOfLastMatch = position + searchLength;
          position = position + advanceBy > string.length ? -1 : indexOf(string, searchString, position + advanceBy);
        }
        if (endOfLastMatch < string.length) {
          result += stringSlice(string, endOfLastMatch);
        }
        return result;
      }
    });
    return es_string_replaceAll;
  }
  requireEs_string_replaceAll();
  var es_string_toWellFormed = {};
  var hasRequiredEs_string_toWellFormed;
  function requireEs_string_toWellFormed() {
    if (hasRequiredEs_string_toWellFormed) return es_string_toWellFormed;
    hasRequiredEs_string_toWellFormed = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toString2 = requireToString();
    var fails2 = requireFails();
    var $Array = Array;
    var charAt = uncurryThis("".charAt);
    var charCodeAt = uncurryThis("".charCodeAt);
    var join = uncurryThis([].join);
    var $toWellFormed = "".toWellFormed;
    var REPLACEMENT_CHARACTER = "\uFFFD";
    var TO_STRING_CONVERSION_BUG = $toWellFormed && fails2(function() {
      return call($toWellFormed, 1) !== "1";
    });
    $({ target: "String", proto: true, forced: TO_STRING_CONVERSION_BUG }, {
      toWellFormed: function toWellFormed() {
        var S = toString2(requireObjectCoercible2(this));
        if (TO_STRING_CONVERSION_BUG) return call($toWellFormed, S);
        var length = S.length;
        var result = $Array(length);
        for (var i = 0; i < length; i++) {
          var charCode = charCodeAt(S, i);
          if ((charCode & 63488) !== 55296) result[i] = charAt(S, i);
          else if (charCode >= 56320 || i + 1 >= length || (charCodeAt(S, i + 1) & 64512) !== 56320) result[i] = REPLACEMENT_CHARACTER;
          else {
            result[i] = charAt(S, i);
            result[++i] = charAt(S, i);
          }
        }
        return join(result, "");
      }
    });
    return es_string_toWellFormed;
  }
  requireEs_string_toWellFormed();
  var es_string_trim = {};
  var stringTrim$1;
  var hasRequiredStringTrim;
  function requireStringTrim() {
    if (hasRequiredStringTrim) return stringTrim$1;
    hasRequiredStringTrim = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var requireObjectCoercible2 = requireRequireObjectCoercible();
    var toString2 = requireToString();
    var whitespaces2 = requireWhitespaces();
    var replace = uncurryThis("".replace);
    var ltrim = RegExp("^[" + whitespaces2 + "]+");
    var rtrim = RegExp("(^|[^" + whitespaces2 + "])[" + whitespaces2 + "]+$");
    var createMethod = function(TYPE) {
      return function($this) {
        var string = toString2(requireObjectCoercible2($this));
        if (TYPE & 1) string = replace(string, ltrim, "");
        if (TYPE & 2) string = replace(string, rtrim, "$1");
        return string;
      };
    };
    stringTrim$1 = {
      // `String.prototype.{ trimLeft, trimStart }` methods
      // https://tc39.es/ecma262/#sec-string.prototype.trimstart
      start: createMethod(1),
      // `String.prototype.{ trimRight, trimEnd }` methods
      // https://tc39.es/ecma262/#sec-string.prototype.trimend
      end: createMethod(2),
      // `String.prototype.trim` method
      // https://tc39.es/ecma262/#sec-string.prototype.trim
      trim: createMethod(3)
    };
    return stringTrim$1;
  }
  var stringTrimForced;
  var hasRequiredStringTrimForced;
  function requireStringTrimForced() {
    if (hasRequiredStringTrimForced) return stringTrimForced;
    hasRequiredStringTrimForced = 1;
    var PROPER_FUNCTION_NAME = requireFunctionName().PROPER;
    var fails2 = requireFails();
    var whitespaces2 = requireWhitespaces();
    var non = "\u200B\x85\u180E";
    stringTrimForced = function(METHOD_NAME) {
      return fails2(function() {
        return !!whitespaces2[METHOD_NAME]() || non[METHOD_NAME]() !== non || PROPER_FUNCTION_NAME && whitespaces2[METHOD_NAME].name !== METHOD_NAME;
      });
    };
    return stringTrimForced;
  }
  var hasRequiredEs_string_trim;
  function requireEs_string_trim() {
    if (hasRequiredEs_string_trim) return es_string_trim;
    hasRequiredEs_string_trim = 1;
    var $ = require_export();
    var $trim = requireStringTrim().trim;
    var forcedStringTrimMethod = requireStringTrimForced();
    $({ target: "String", proto: true, forced: forcedStringTrimMethod("trim") }, {
      trim: function trim() {
        return $trim(this);
      }
    });
    return es_string_trim;
  }
  requireEs_string_trim();
  var es_string_trimEnd = {};
  var es_string_trimRight = {};
  var stringTrimEnd;
  var hasRequiredStringTrimEnd;
  function requireStringTrimEnd() {
    if (hasRequiredStringTrimEnd) return stringTrimEnd;
    hasRequiredStringTrimEnd = 1;
    var $trimEnd = requireStringTrim().end;
    var forcedStringTrimMethod = requireStringTrimForced();
    stringTrimEnd = forcedStringTrimMethod("trimEnd") ? function trimEnd() {
      return $trimEnd(this);
    } : "".trimEnd;
    return stringTrimEnd;
  }
  var hasRequiredEs_string_trimRight;
  function requireEs_string_trimRight() {
    if (hasRequiredEs_string_trimRight) return es_string_trimRight;
    hasRequiredEs_string_trimRight = 1;
    var $ = require_export();
    var trimEnd = requireStringTrimEnd();
    $({ target: "String", proto: true, name: "trimEnd", forced: "".trimRight !== trimEnd }, {
      trimRight: trimEnd
    });
    return es_string_trimRight;
  }
  var hasRequiredEs_string_trimEnd;
  function requireEs_string_trimEnd() {
    if (hasRequiredEs_string_trimEnd) return es_string_trimEnd;
    hasRequiredEs_string_trimEnd = 1;
    requireEs_string_trimRight();
    var $ = require_export();
    var trimEnd = requireStringTrimEnd();
    $({ target: "String", proto: true, name: "trimEnd", forced: "".trimEnd !== trimEnd }, {
      trimEnd
    });
    return es_string_trimEnd;
  }
  requireEs_string_trimEnd();
  var es_string_trimStart = {};
  var es_string_trimLeft = {};
  var stringTrimStart;
  var hasRequiredStringTrimStart;
  function requireStringTrimStart() {
    if (hasRequiredStringTrimStart) return stringTrimStart;
    hasRequiredStringTrimStart = 1;
    var $trimStart = requireStringTrim().start;
    var forcedStringTrimMethod = requireStringTrimForced();
    stringTrimStart = forcedStringTrimMethod("trimStart") ? function trimStart() {
      return $trimStart(this);
    } : "".trimStart;
    return stringTrimStart;
  }
  var hasRequiredEs_string_trimLeft;
  function requireEs_string_trimLeft() {
    if (hasRequiredEs_string_trimLeft) return es_string_trimLeft;
    hasRequiredEs_string_trimLeft = 1;
    var $ = require_export();
    var trimStart = requireStringTrimStart();
    $({ target: "String", proto: true, name: "trimStart", forced: "".trimLeft !== trimStart }, {
      trimLeft: trimStart
    });
    return es_string_trimLeft;
  }
  var hasRequiredEs_string_trimStart;
  function requireEs_string_trimStart() {
    if (hasRequiredEs_string_trimStart) return es_string_trimStart;
    hasRequiredEs_string_trimStart = 1;
    requireEs_string_trimLeft();
    var $ = require_export();
    var trimStart = requireStringTrimStart();
    $({ target: "String", proto: true, name: "trimStart", forced: "".trimStart !== trimStart }, {
      trimStart
    });
    return es_string_trimStart;
  }
  requireEs_string_trimStart();
  var es_typedArray_float32Array = {};
  var typedArrayConstructor = { exports: {} };
  var arrayBufferViewCore;
  var hasRequiredArrayBufferViewCore;
  function requireArrayBufferViewCore() {
    if (hasRequiredArrayBufferViewCore) return arrayBufferViewCore;
    hasRequiredArrayBufferViewCore = 1;
    var NATIVE_ARRAY_BUFFER = requireArrayBufferBasicDetection();
    var DESCRIPTORS = requireDescriptors();
    var globalThis2 = requireGlobalThis();
    var isCallable2 = requireIsCallable();
    var isObject2 = requireIsObject();
    var hasOwn = requireHasOwnProperty();
    var classof2 = requireClassof();
    var tryToString2 = requireTryToString();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var uid2 = requireUid();
    var InternalStateModule = requireInternalState();
    var enforceInternalState = InternalStateModule.enforce;
    var getInternalState = InternalStateModule.get;
    var Int8Array2 = globalThis2.Int8Array;
    var Int8ArrayPrototype = Int8Array2 && Int8Array2.prototype;
    var Uint8ClampedArray2 = globalThis2.Uint8ClampedArray;
    var Uint8ClampedArrayPrototype = Uint8ClampedArray2 && Uint8ClampedArray2.prototype;
    var TypedArray = Int8Array2 && getPrototypeOf2(Int8Array2);
    var TypedArrayPrototype = Int8ArrayPrototype && getPrototypeOf2(Int8ArrayPrototype);
    var ObjectPrototype = Object.prototype;
    var TypeError2 = globalThis2.TypeError;
    var TO_STRING_TAG = wellKnownSymbol2("toStringTag");
    var TYPED_ARRAY_TAG = uid2("TYPED_ARRAY_TAG");
    var TYPED_ARRAY_CONSTRUCTOR = "TypedArrayConstructor";
    var NATIVE_ARRAY_BUFFER_VIEWS = NATIVE_ARRAY_BUFFER && !!setPrototypeOf2 && classof2(globalThis2.opera) !== "Opera";
    var TYPED_ARRAY_TAG_REQUIRED = false;
    var NAME, Constructor, Prototype;
    var TypedArrayConstructorsList = {
      Int8Array: 1,
      Uint8Array: 1,
      Uint8ClampedArray: 1,
      Int16Array: 2,
      Uint16Array: 2,
      Int32Array: 4,
      Uint32Array: 4,
      Float32Array: 4,
      Float64Array: 8
    };
    var BigIntArrayConstructorsList = {
      BigInt64Array: 8,
      BigUint64Array: 8
    };
    var isView = function isView2(it) {
      if (!isObject2(it)) return false;
      var klass = classof2(it);
      return klass === "DataView" || hasOwn(TypedArrayConstructorsList, klass) || hasOwn(BigIntArrayConstructorsList, klass);
    };
    var getTypedArrayConstructor = function(it) {
      var proto = getPrototypeOf2(it);
      if (!isObject2(proto)) return;
      var state = getInternalState(proto);
      return state && hasOwn(state, TYPED_ARRAY_CONSTRUCTOR) ? state[TYPED_ARRAY_CONSTRUCTOR] : getTypedArrayConstructor(proto);
    };
    var isTypedArray = function(it) {
      if (!isObject2(it)) return false;
      var klass = classof2(it);
      return hasOwn(TypedArrayConstructorsList, klass) || hasOwn(BigIntArrayConstructorsList, klass);
    };
    var aTypedArray = function(it) {
      if (isTypedArray(it)) return it;
      throw new TypeError2("Target is not a typed array");
    };
    var aTypedArrayConstructor = function(C) {
      if (isCallable2(C) && (!setPrototypeOf2 || isPrototypeOf(TypedArray, C))) return C;
      throw new TypeError2(tryToString2(C) + " is not a typed array constructor");
    };
    var exportTypedArrayMethod = function(KEY, property, forced, options) {
      if (!DESCRIPTORS) return;
      if (forced) for (var ARRAY in TypedArrayConstructorsList) {
        var TypedArrayConstructor = globalThis2[ARRAY];
        if (TypedArrayConstructor && hasOwn(TypedArrayConstructor.prototype, KEY)) try {
          delete TypedArrayConstructor.prototype[KEY];
        } catch (error) {
          try {
            TypedArrayConstructor.prototype[KEY] = property;
          } catch (error2) {
          }
        }
      }
      if (!TypedArrayPrototype[KEY] || forced) {
        defineBuiltIn2(TypedArrayPrototype, KEY, forced ? property : NATIVE_ARRAY_BUFFER_VIEWS && Int8ArrayPrototype[KEY] || property, options);
      }
    };
    var exportTypedArrayStaticMethod = function(KEY, property, forced) {
      var ARRAY, TypedArrayConstructor;
      if (!DESCRIPTORS) return;
      if (setPrototypeOf2) {
        if (forced) for (ARRAY in TypedArrayConstructorsList) {
          TypedArrayConstructor = globalThis2[ARRAY];
          if (TypedArrayConstructor && hasOwn(TypedArrayConstructor, KEY)) try {
            delete TypedArrayConstructor[KEY];
          } catch (error) {
          }
        }
        if (!TypedArray[KEY] || forced) {
          try {
            return defineBuiltIn2(TypedArray, KEY, forced ? property : NATIVE_ARRAY_BUFFER_VIEWS && TypedArray[KEY] || property);
          } catch (error) {
          }
        } else return;
      }
      for (ARRAY in TypedArrayConstructorsList) {
        TypedArrayConstructor = globalThis2[ARRAY];
        if (TypedArrayConstructor && (!TypedArrayConstructor[KEY] || forced)) {
          defineBuiltIn2(TypedArrayConstructor, KEY, property);
        }
      }
    };
    for (NAME in TypedArrayConstructorsList) {
      Constructor = globalThis2[NAME];
      Prototype = Constructor && Constructor.prototype;
      if (Prototype) enforceInternalState(Prototype)[TYPED_ARRAY_CONSTRUCTOR] = Constructor;
      else NATIVE_ARRAY_BUFFER_VIEWS = false;
    }
    for (NAME in BigIntArrayConstructorsList) {
      Constructor = globalThis2[NAME];
      Prototype = Constructor && Constructor.prototype;
      if (Prototype) enforceInternalState(Prototype)[TYPED_ARRAY_CONSTRUCTOR] = Constructor;
    }
    if (!NATIVE_ARRAY_BUFFER_VIEWS || !isCallable2(TypedArray) || TypedArray === Function.prototype) {
      TypedArray = function TypedArray2() {
        throw new TypeError2("Incorrect invocation");
      };
      if (NATIVE_ARRAY_BUFFER_VIEWS) for (NAME in TypedArrayConstructorsList) {
        if (globalThis2[NAME]) setPrototypeOf2(globalThis2[NAME], TypedArray);
      }
    }
    if (!NATIVE_ARRAY_BUFFER_VIEWS || !TypedArrayPrototype || TypedArrayPrototype === ObjectPrototype) {
      TypedArrayPrototype = TypedArray.prototype;
      if (NATIVE_ARRAY_BUFFER_VIEWS) for (NAME in TypedArrayConstructorsList) {
        if (globalThis2[NAME]) setPrototypeOf2(globalThis2[NAME].prototype, TypedArrayPrototype);
      }
    }
    if (NATIVE_ARRAY_BUFFER_VIEWS && getPrototypeOf2(Uint8ClampedArrayPrototype) !== TypedArrayPrototype) {
      setPrototypeOf2(Uint8ClampedArrayPrototype, TypedArrayPrototype);
    }
    if (DESCRIPTORS && !hasOwn(TypedArrayPrototype, TO_STRING_TAG)) {
      TYPED_ARRAY_TAG_REQUIRED = true;
      defineBuiltInAccessor2(TypedArrayPrototype, TO_STRING_TAG, {
        configurable: true,
        get: function() {
          return isObject2(this) ? this[TYPED_ARRAY_TAG] : void 0;
        }
      });
      for (NAME in TypedArrayConstructorsList) if (globalThis2[NAME]) {
        createNonEnumerableProperty2(globalThis2[NAME].prototype, TYPED_ARRAY_TAG, NAME);
      }
    }
    arrayBufferViewCore = {
      NATIVE_ARRAY_BUFFER_VIEWS,
      TYPED_ARRAY_TAG: TYPED_ARRAY_TAG_REQUIRED && TYPED_ARRAY_TAG,
      aTypedArray,
      aTypedArrayConstructor,
      exportTypedArrayMethod,
      exportTypedArrayStaticMethod,
      getTypedArrayConstructor,
      isView,
      isTypedArray,
      TypedArray,
      TypedArrayPrototype
    };
    return arrayBufferViewCore;
  }
  var typedArrayConstructorsRequireWrappers;
  var hasRequiredTypedArrayConstructorsRequireWrappers;
  function requireTypedArrayConstructorsRequireWrappers() {
    if (hasRequiredTypedArrayConstructorsRequireWrappers) return typedArrayConstructorsRequireWrappers;
    hasRequiredTypedArrayConstructorsRequireWrappers = 1;
    var globalThis2 = requireGlobalThis();
    var fails2 = requireFails();
    var checkCorrectnessOfIteration2 = requireCheckCorrectnessOfIteration();
    var NATIVE_ARRAY_BUFFER_VIEWS = requireArrayBufferViewCore().NATIVE_ARRAY_BUFFER_VIEWS;
    var ArrayBuffer2 = globalThis2.ArrayBuffer;
    var Int8Array2 = globalThis2.Int8Array;
    typedArrayConstructorsRequireWrappers = !NATIVE_ARRAY_BUFFER_VIEWS || !fails2(function() {
      Int8Array2(1);
    }) || !fails2(function() {
      new Int8Array2(-1);
    }) || !checkCorrectnessOfIteration2(function(iterable) {
      new Int8Array2();
      new Int8Array2(null);
      new Int8Array2(1.5);
      new Int8Array2(iterable);
    }, true) || fails2(function() {
      return new Int8Array2(new ArrayBuffer2(2), 1, void 0).length !== 1;
    });
    return typedArrayConstructorsRequireWrappers;
  }
  var isIntegralNumber;
  var hasRequiredIsIntegralNumber;
  function requireIsIntegralNumber() {
    if (hasRequiredIsIntegralNumber) return isIntegralNumber;
    hasRequiredIsIntegralNumber = 1;
    var isObject2 = requireIsObject();
    var floor = Math.floor;
    isIntegralNumber = Number.isInteger || function isInteger(it) {
      return !isObject2(it) && isFinite(it) && floor(it) === it;
    };
    return isIntegralNumber;
  }
  var toOffset;
  var hasRequiredToOffset;
  function requireToOffset() {
    if (hasRequiredToOffset) return toOffset;
    hasRequiredToOffset = 1;
    var toPositiveInteger2 = requireToPositiveInteger();
    var $RangeError = RangeError;
    toOffset = function(it, BYTES) {
      var offset = toPositiveInteger2(it);
      if (offset % BYTES) throw new $RangeError("Wrong offset");
      return offset;
    };
    return toOffset;
  }
  var toUint8Clamped;
  var hasRequiredToUint8Clamped;
  function requireToUint8Clamped() {
    if (hasRequiredToUint8Clamped) return toUint8Clamped;
    hasRequiredToUint8Clamped = 1;
    var floor = Math.floor;
    toUint8Clamped = function(it) {
      var number = +it;
      if (number !== number || number <= 0) return 0;
      if (number >= 255) return 255;
      var f = floor(number);
      if (f + 0.5 < number) return f + 1;
      if (number < f + 0.5) return f;
      return f % 2 === 0 ? f : f + 1;
    };
    return toUint8Clamped;
  }
  var isBigIntArray;
  var hasRequiredIsBigIntArray;
  function requireIsBigIntArray() {
    if (hasRequiredIsBigIntArray) return isBigIntArray;
    hasRequiredIsBigIntArray = 1;
    var classof2 = requireClassof();
    isBigIntArray = function(it) {
      var klass = classof2(it);
      return klass === "BigInt64Array" || klass === "BigUint64Array";
    };
    return isBigIntArray;
  }
  var toBigInt;
  var hasRequiredToBigInt;
  function requireToBigInt() {
    if (hasRequiredToBigInt) return toBigInt;
    hasRequiredToBigInt = 1;
    var toPrimitive2 = requireToPrimitive();
    var $TypeError = TypeError;
    toBigInt = function(argument) {
      var prim = toPrimitive2(argument, "number");
      if (typeof prim == "number") throw new $TypeError("Can't convert number to bigint");
      return BigInt(prim);
    };
    return toBigInt;
  }
  var typedArrayFrom;
  var hasRequiredTypedArrayFrom;
  function requireTypedArrayFrom() {
    if (hasRequiredTypedArrayFrom) return typedArrayFrom;
    hasRequiredTypedArrayFrom = 1;
    var bind = requireFunctionBindContext();
    var call = requireFunctionCall();
    var aCallable2 = requireACallable();
    var aConstructor2 = requireAConstructor();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var getIterator = requireGetIteratorInternal();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var isArrayIteratorMethod2 = requireIsArrayIteratorMethod();
    var isBigIntArray2 = requireIsBigIntArray();
    var aTypedArrayConstructor = requireArrayBufferViewCore().aTypedArrayConstructor;
    var toBigInt2 = requireToBigInt();
    typedArrayFrom = function from(source) {
      var C = aConstructor2(this);
      var argumentsLength = arguments.length;
      var mapfn = argumentsLength > 1 ? arguments[1] : void 0;
      var mapping = mapfn !== void 0;
      if (mapping) aCallable2(mapfn);
      var O = toObject2(source);
      var iteratorMethod = getIteratorMethod(O);
      var i, length, result, thisIsBigIntArray, value, step, iterator, next;
      if (iteratorMethod && !isArrayIteratorMethod2(iteratorMethod)) {
        iterator = getIterator(O, iteratorMethod);
        next = iterator.next;
        O = [];
        while (!(step = call(next, iterator)).done) {
          O.push(step.value);
        }
      }
      if (mapping && argumentsLength > 2) {
        mapfn = bind(mapfn, arguments[2]);
      }
      length = lengthOfArrayLike2(O);
      result = new (aTypedArrayConstructor(C))(length);
      thisIsBigIntArray = isBigIntArray2(result);
      for (i = 0; length > i; i++) {
        value = mapping ? mapfn(O[i], i) : O[i];
        result[i] = thisIsBigIntArray ? toBigInt2(value) : +value;
      }
      return result;
    };
    return typedArrayFrom;
  }
  var arrayIteration;
  var hasRequiredArrayIteration;
  function requireArrayIteration() {
    if (hasRequiredArrayIteration) return arrayIteration;
    hasRequiredArrayIteration = 1;
    var bind = requireFunctionBindContext();
    var IndexedObject = requireIndexedObject();
    var toObject2 = requireToObject();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var arraySpeciesCreate2 = requireArraySpeciesCreate();
    var createProperty2 = requireCreateProperty();
    var createMethod = function(TYPE) {
      var IS_MAP = TYPE === 1;
      var IS_FILTER = TYPE === 2;
      var IS_SOME = TYPE === 3;
      var IS_EVERY = TYPE === 4;
      var IS_FIND_INDEX = TYPE === 6;
      var IS_FILTER_REJECT = TYPE === 7;
      var NO_HOLES = TYPE === 5 || IS_FIND_INDEX;
      return function($this, callbackfn, that) {
        var O = toObject2($this);
        var self2 = IndexedObject(O);
        var length = lengthOfArrayLike2(self2);
        var boundFunction = bind(callbackfn, that);
        var index = 0;
        var resIndex = 0;
        var target = IS_MAP ? arraySpeciesCreate2($this, length) : IS_FILTER || IS_FILTER_REJECT ? arraySpeciesCreate2($this, 0) : void 0;
        var value, result;
        for (; length > index; index++) if (NO_HOLES || index in self2) {
          value = self2[index];
          result = boundFunction(value, index, O);
          if (TYPE) {
            if (IS_MAP) createProperty2(target, index, result);
            else if (result) switch (TYPE) {
              case 3:
                return true;
              // some
              case 5:
                return value;
              // find
              case 6:
                return index;
              // findIndex
              case 2:
                createProperty2(target, resIndex++, value);
            }
            else switch (TYPE) {
              case 4:
                return false;
              // every
              case 7:
                createProperty2(target, resIndex++, value);
            }
          }
        }
        return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
      };
    };
    arrayIteration = {
      // `Array.prototype.forEach` method
      // https://tc39.es/ecma262/#sec-array.prototype.foreach
      forEach: createMethod(0),
      // `Array.prototype.map` method
      // https://tc39.es/ecma262/#sec-array.prototype.map
      map: createMethod(1),
      // `Array.prototype.filter` method
      // https://tc39.es/ecma262/#sec-array.prototype.filter
      filter: createMethod(2),
      // `Array.prototype.some` method
      // https://tc39.es/ecma262/#sec-array.prototype.some
      some: createMethod(3),
      // `Array.prototype.every` method
      // https://tc39.es/ecma262/#sec-array.prototype.every
      every: createMethod(4),
      // `Array.prototype.find` method
      // https://tc39.es/ecma262/#sec-array.prototype.find
      find: createMethod(5),
      // `Array.prototype.findIndex` method
      // https://tc39.es/ecma262/#sec-array.prototype.findIndex
      findIndex: createMethod(6),
      // `Array.prototype.filterReject` method
      // https://github.com/tc39/proposal-array-filtering
      filterReject: createMethod(7)
    };
    return arrayIteration;
  }
  var hasRequiredTypedArrayConstructor;
  function requireTypedArrayConstructor() {
    if (hasRequiredTypedArrayConstructor) return typedArrayConstructor.exports;
    hasRequiredTypedArrayConstructor = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var call = requireFunctionCall();
    var DESCRIPTORS = requireDescriptors();
    var TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS = requireTypedArrayConstructorsRequireWrappers();
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var ArrayBufferModule = requireArrayBuffer();
    var anInstance2 = requireAnInstance();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var isIntegralNumber2 = requireIsIntegralNumber();
    var toIndex2 = requireToIndex();
    var toOffset2 = requireToOffset();
    var toUint8Clamped2 = requireToUint8Clamped();
    var toPropertyKey2 = requireToPropertyKey();
    var hasOwn = requireHasOwnProperty();
    var classof2 = requireClassof();
    var isObject2 = requireIsObject();
    var isSymbol2 = requireIsSymbol();
    var create2 = requireObjectCreate();
    var isPrototypeOf = requireObjectIsPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var getOwnPropertyNames = requireObjectGetOwnPropertyNames().f;
    var typedArrayFrom2 = requireTypedArrayFrom();
    var forEach = requireArrayIteration().forEach;
    var setSpecies2 = requireSetSpecies();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var definePropertyModule = requireObjectDefineProperty();
    var getOwnPropertyDescriptorModule = requireObjectGetOwnPropertyDescriptor();
    var arrayFromConstructorAndList2 = requireArrayFromConstructorAndList();
    var InternalStateModule = requireInternalState();
    var inheritIfRequired2 = requireInheritIfRequired();
    var getInternalState = InternalStateModule.get;
    var setInternalState = InternalStateModule.set;
    var enforceInternalState = InternalStateModule.enforce;
    var nativeDefineProperty = definePropertyModule.f;
    var nativeGetOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
    var RangeError2 = globalThis2.RangeError;
    var ArrayBuffer2 = ArrayBufferModule.ArrayBuffer;
    var ArrayBufferPrototype = ArrayBuffer2.prototype;
    var DataView2 = ArrayBufferModule.DataView;
    var NATIVE_ARRAY_BUFFER_VIEWS = ArrayBufferViewCore.NATIVE_ARRAY_BUFFER_VIEWS;
    var TYPED_ARRAY_TAG = ArrayBufferViewCore.TYPED_ARRAY_TAG;
    var TypedArray = ArrayBufferViewCore.TypedArray;
    var TypedArrayPrototype = ArrayBufferViewCore.TypedArrayPrototype;
    var isTypedArray = ArrayBufferViewCore.isTypedArray;
    var BYTES_PER_ELEMENT = "BYTES_PER_ELEMENT";
    var WRONG_LENGTH = "Wrong length";
    var addGetter = function(it, key) {
      defineBuiltInAccessor2(it, key, {
        configurable: true,
        get: function() {
          return getInternalState(this)[key];
        }
      });
    };
    var isArrayBuffer = function(it) {
      var klass;
      return isPrototypeOf(ArrayBufferPrototype, it) || (klass = classof2(it)) === "ArrayBuffer" || klass === "SharedArrayBuffer";
    };
    var isTypedArrayIndex = function(target, key) {
      return isTypedArray(target) && !isSymbol2(key) && key in target && isIntegralNumber2(+key) && key >= 0;
    };
    var wrappedGetOwnPropertyDescriptor = function getOwnPropertyDescriptor2(target, key) {
      key = toPropertyKey2(key);
      return isTypedArrayIndex(target, key) ? createPropertyDescriptor2(2, target[key]) : nativeGetOwnPropertyDescriptor(target, key);
    };
    var wrappedDefineProperty = function defineProperty(target, key, descriptor) {
      key = toPropertyKey2(key);
      if (isTypedArrayIndex(target, key) && isObject2(descriptor) && hasOwn(descriptor, "value") && !hasOwn(descriptor, "get") && !hasOwn(descriptor, "set") && !descriptor.configurable && (!hasOwn(descriptor, "writable") || descriptor.writable) && (!hasOwn(descriptor, "enumerable") || descriptor.enumerable)) {
        target[key] = descriptor.value;
        return target;
      }
      return nativeDefineProperty(target, key, descriptor);
    };
    if (DESCRIPTORS) {
      if (!NATIVE_ARRAY_BUFFER_VIEWS) {
        getOwnPropertyDescriptorModule.f = wrappedGetOwnPropertyDescriptor;
        definePropertyModule.f = wrappedDefineProperty;
        addGetter(TypedArrayPrototype, "buffer");
        addGetter(TypedArrayPrototype, "byteOffset");
        addGetter(TypedArrayPrototype, "byteLength");
        addGetter(TypedArrayPrototype, "length");
      }
      $({ target: "Object", stat: true, forced: !NATIVE_ARRAY_BUFFER_VIEWS }, {
        getOwnPropertyDescriptor: wrappedGetOwnPropertyDescriptor,
        defineProperty: wrappedDefineProperty
      });
      typedArrayConstructor.exports = function(TYPE, wrapper, CLAMPED) {
        var BYTES = TYPE.match(/\d+/)[0] / 8;
        var CONSTRUCTOR_NAME = TYPE + (CLAMPED ? "Clamped" : "") + "Array";
        var GETTER = "get" + TYPE;
        var SETTER = "set" + TYPE;
        var NativeTypedArrayConstructor = globalThis2[CONSTRUCTOR_NAME];
        var TypedArrayConstructor = NativeTypedArrayConstructor;
        var TypedArrayConstructorPrototype = TypedArrayConstructor && TypedArrayConstructor.prototype;
        var exported = {};
        var getter = function(that, index) {
          var data = getInternalState(that);
          return data.view[GETTER](index * BYTES + data.byteOffset, true);
        };
        var setter = function(that, index, value) {
          var data = getInternalState(that);
          data.view[SETTER](index * BYTES + data.byteOffset, CLAMPED ? toUint8Clamped2(value) : value, true);
        };
        var addElement = function(that, index) {
          nativeDefineProperty(that, index, {
            get: function() {
              return getter(this, index);
            },
            set: function(value) {
              return setter(this, index, value);
            },
            enumerable: true
          });
        };
        if (!NATIVE_ARRAY_BUFFER_VIEWS) {
          TypedArrayConstructor = wrapper(function(that, data, offset, $length) {
            anInstance2(that, TypedArrayConstructorPrototype);
            var index = 0;
            var byteOffset = 0;
            var buffer, byteLength, length;
            if (!isObject2(data)) {
              length = toIndex2(data);
              byteLength = length * BYTES;
              buffer = new ArrayBuffer2(byteLength);
            } else if (isArrayBuffer(data)) {
              buffer = data;
              byteOffset = toOffset2(offset, BYTES);
              var $len = data.byteLength;
              if ($length === void 0) {
                if ($len % BYTES) throw new RangeError2(WRONG_LENGTH);
                byteLength = $len - byteOffset;
                if (byteLength < 0) throw new RangeError2(WRONG_LENGTH);
              } else {
                byteLength = toIndex2($length) * BYTES;
                if (byteLength + byteOffset > $len) throw new RangeError2(WRONG_LENGTH);
              }
              length = byteLength / BYTES;
            } else if (isTypedArray(data)) {
              return arrayFromConstructorAndList2(TypedArrayConstructor, data);
            } else {
              return call(typedArrayFrom2, TypedArrayConstructor, data);
            }
            setInternalState(that, {
              buffer,
              byteOffset,
              byteLength,
              length,
              view: new DataView2(buffer)
            });
            while (index < length) addElement(that, index++);
          });
          if (setPrototypeOf2) setPrototypeOf2(TypedArrayConstructor, TypedArray);
          TypedArrayConstructorPrototype = TypedArrayConstructor.prototype = create2(TypedArrayPrototype);
        } else if (TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS) {
          TypedArrayConstructor = wrapper(function(dummy, data, typedArrayOffset, $length) {
            anInstance2(dummy, TypedArrayConstructorPrototype);
            return inheritIfRequired2((function() {
              if (!isObject2(data)) return new NativeTypedArrayConstructor(toIndex2(data));
              if (isArrayBuffer(data)) return $length !== void 0 ? new NativeTypedArrayConstructor(data, toOffset2(typedArrayOffset, BYTES), $length) : typedArrayOffset !== void 0 ? new NativeTypedArrayConstructor(data, toOffset2(typedArrayOffset, BYTES)) : new NativeTypedArrayConstructor(data);
              if (isTypedArray(data)) return arrayFromConstructorAndList2(TypedArrayConstructor, data);
              return call(typedArrayFrom2, TypedArrayConstructor, data);
            })(), dummy, TypedArrayConstructor);
          });
          if (setPrototypeOf2) setPrototypeOf2(TypedArrayConstructor, TypedArray);
          forEach(getOwnPropertyNames(NativeTypedArrayConstructor), function(key) {
            if (!(key in TypedArrayConstructor)) {
              createNonEnumerableProperty2(TypedArrayConstructor, key, NativeTypedArrayConstructor[key]);
            }
          });
          TypedArrayConstructor.prototype = TypedArrayConstructorPrototype;
        }
        if (TypedArrayConstructorPrototype.constructor !== TypedArrayConstructor) {
          createNonEnumerableProperty2(TypedArrayConstructorPrototype, "constructor", TypedArrayConstructor);
        }
        enforceInternalState(TypedArrayConstructorPrototype).TypedArrayConstructor = TypedArrayConstructor;
        if (TYPED_ARRAY_TAG) {
          createNonEnumerableProperty2(TypedArrayConstructorPrototype, TYPED_ARRAY_TAG, CONSTRUCTOR_NAME);
        }
        var FORCED = TypedArrayConstructor !== NativeTypedArrayConstructor;
        exported[CONSTRUCTOR_NAME] = TypedArrayConstructor;
        $({ global: true, constructor: true, forced: FORCED, sham: !NATIVE_ARRAY_BUFFER_VIEWS }, exported);
        if (!(BYTES_PER_ELEMENT in TypedArrayConstructor)) {
          createNonEnumerableProperty2(TypedArrayConstructor, BYTES_PER_ELEMENT, BYTES);
        }
        if (!(BYTES_PER_ELEMENT in TypedArrayConstructorPrototype)) {
          createNonEnumerableProperty2(TypedArrayConstructorPrototype, BYTES_PER_ELEMENT, BYTES);
        }
        setSpecies2(CONSTRUCTOR_NAME);
      };
    } else typedArrayConstructor.exports = function() {
    };
    return typedArrayConstructor.exports;
  }
  var hasRequiredEs_typedArray_float32Array;
  function requireEs_typedArray_float32Array() {
    if (hasRequiredEs_typedArray_float32Array) return es_typedArray_float32Array;
    hasRequiredEs_typedArray_float32Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Float32", function(init) {
      return function Float32Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_float32Array;
  }
  requireEs_typedArray_float32Array();
  var es_typedArray_float64Array = {};
  var hasRequiredEs_typedArray_float64Array;
  function requireEs_typedArray_float64Array() {
    if (hasRequiredEs_typedArray_float64Array) return es_typedArray_float64Array;
    hasRequiredEs_typedArray_float64Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Float64", function(init) {
      return function Float64Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_float64Array;
  }
  requireEs_typedArray_float64Array();
  var es_typedArray_int8Array = {};
  var hasRequiredEs_typedArray_int8Array;
  function requireEs_typedArray_int8Array() {
    if (hasRequiredEs_typedArray_int8Array) return es_typedArray_int8Array;
    hasRequiredEs_typedArray_int8Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Int8", function(init) {
      return function Int8Array2(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_int8Array;
  }
  requireEs_typedArray_int8Array();
  var es_typedArray_int16Array = {};
  var hasRequiredEs_typedArray_int16Array;
  function requireEs_typedArray_int16Array() {
    if (hasRequiredEs_typedArray_int16Array) return es_typedArray_int16Array;
    hasRequiredEs_typedArray_int16Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Int16", function(init) {
      return function Int16Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_int16Array;
  }
  requireEs_typedArray_int16Array();
  var es_typedArray_int32Array = {};
  var hasRequiredEs_typedArray_int32Array;
  function requireEs_typedArray_int32Array() {
    if (hasRequiredEs_typedArray_int32Array) return es_typedArray_int32Array;
    hasRequiredEs_typedArray_int32Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Int32", function(init) {
      return function Int32Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_int32Array;
  }
  requireEs_typedArray_int32Array();
  var es_typedArray_uint8Array = {};
  var hasRequiredEs_typedArray_uint8Array;
  function requireEs_typedArray_uint8Array() {
    if (hasRequiredEs_typedArray_uint8Array) return es_typedArray_uint8Array;
    hasRequiredEs_typedArray_uint8Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Uint8", function(init) {
      return function Uint8Array2(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_uint8Array;
  }
  requireEs_typedArray_uint8Array();
  var es_typedArray_uint8ClampedArray = {};
  var hasRequiredEs_typedArray_uint8ClampedArray;
  function requireEs_typedArray_uint8ClampedArray() {
    if (hasRequiredEs_typedArray_uint8ClampedArray) return es_typedArray_uint8ClampedArray;
    hasRequiredEs_typedArray_uint8ClampedArray = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Uint8", function(init) {
      return function Uint8ClampedArray2(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    }, true);
    return es_typedArray_uint8ClampedArray;
  }
  requireEs_typedArray_uint8ClampedArray();
  var es_typedArray_uint16Array = {};
  var hasRequiredEs_typedArray_uint16Array;
  function requireEs_typedArray_uint16Array() {
    if (hasRequiredEs_typedArray_uint16Array) return es_typedArray_uint16Array;
    hasRequiredEs_typedArray_uint16Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Uint16", function(init) {
      return function Uint16Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_uint16Array;
  }
  requireEs_typedArray_uint16Array();
  var es_typedArray_uint32Array = {};
  var hasRequiredEs_typedArray_uint32Array;
  function requireEs_typedArray_uint32Array() {
    if (hasRequiredEs_typedArray_uint32Array) return es_typedArray_uint32Array;
    hasRequiredEs_typedArray_uint32Array = 1;
    var createTypedArrayConstructor = requireTypedArrayConstructor();
    createTypedArrayConstructor("Uint32", function(init) {
      return function Uint32Array(data, byteOffset, length) {
        return init(this, data, byteOffset, length);
      };
    });
    return es_typedArray_uint32Array;
  }
  requireEs_typedArray_uint32Array();
  var es_typedArray_at = {};
  var hasRequiredEs_typedArray_at;
  function requireEs_typedArray_at() {
    if (hasRequiredEs_typedArray_at) return es_typedArray_at;
    hasRequiredEs_typedArray_at = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    exportTypedArrayMethod("at", function at(index) {
      var O = aTypedArray(this);
      var len = lengthOfArrayLike2(O);
      var relativeIndex = toIntegerOrInfinity2(index);
      var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
      return k < 0 || k >= len ? void 0 : O[k];
    });
    return es_typedArray_at;
  }
  requireEs_typedArray_at();
  var es_typedArray_fill = {};
  var hasRequiredEs_typedArray_fill;
  function requireEs_typedArray_fill() {
    if (hasRequiredEs_typedArray_fill) return es_typedArray_fill;
    hasRequiredEs_typedArray_fill = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var $fill = requireArrayFill();
    var toBigInt2 = requireToBigInt();
    var classof2 = requireClassof();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var slice = uncurryThis("".slice);
    var CONVERSION_BUG = fails2(function() {
      var count = 0;
      new Int8Array(2).fill({ valueOf: function() {
        return count++;
      } });
      return count !== 1;
    });
    exportTypedArrayMethod("fill", function fill(value) {
      var length = arguments.length;
      aTypedArray(this);
      var actualValue = slice(classof2(this), 0, 3) === "Big" ? toBigInt2(value) : +value;
      return call($fill, this, actualValue, length > 1 ? arguments[1] : void 0, length > 2 ? arguments[2] : void 0);
    }, CONVERSION_BUG);
    return es_typedArray_fill;
  }
  requireEs_typedArray_fill();
  var es_typedArray_findLast = {};
  var hasRequiredEs_typedArray_findLast;
  function requireEs_typedArray_findLast() {
    if (hasRequiredEs_typedArray_findLast) return es_typedArray_findLast;
    hasRequiredEs_typedArray_findLast = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var $findLast = requireArrayIterationFromLast().findLast;
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    exportTypedArrayMethod("findLast", function findLast(predicate) {
      return $findLast(aTypedArray(this), predicate, arguments.length > 1 ? arguments[1] : void 0);
    });
    return es_typedArray_findLast;
  }
  requireEs_typedArray_findLast();
  var es_typedArray_findLastIndex = {};
  var hasRequiredEs_typedArray_findLastIndex;
  function requireEs_typedArray_findLastIndex() {
    if (hasRequiredEs_typedArray_findLastIndex) return es_typedArray_findLastIndex;
    hasRequiredEs_typedArray_findLastIndex = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var $findLastIndex = requireArrayIterationFromLast().findLastIndex;
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    exportTypedArrayMethod("findLastIndex", function findLastIndex(predicate) {
      return $findLastIndex(aTypedArray(this), predicate, arguments.length > 1 ? arguments[1] : void 0);
    });
    return es_typedArray_findLastIndex;
  }
  requireEs_typedArray_findLastIndex();
  var es_typedArray_from = {};
  var hasRequiredEs_typedArray_from;
  function requireEs_typedArray_from() {
    if (hasRequiredEs_typedArray_from) return es_typedArray_from;
    hasRequiredEs_typedArray_from = 1;
    var TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS = requireTypedArrayConstructorsRequireWrappers();
    var exportTypedArrayStaticMethod = requireArrayBufferViewCore().exportTypedArrayStaticMethod;
    var typedArrayFrom2 = requireTypedArrayFrom();
    exportTypedArrayStaticMethod("from", typedArrayFrom2, TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS);
    return es_typedArray_from;
  }
  requireEs_typedArray_from();
  var es_typedArray_of = {};
  var hasRequiredEs_typedArray_of;
  function requireEs_typedArray_of() {
    if (hasRequiredEs_typedArray_of) return es_typedArray_of;
    hasRequiredEs_typedArray_of = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS = requireTypedArrayConstructorsRequireWrappers();
    var aTypedArrayConstructor = ArrayBufferViewCore.aTypedArrayConstructor;
    var exportTypedArrayStaticMethod = ArrayBufferViewCore.exportTypedArrayStaticMethod;
    exportTypedArrayStaticMethod("of", function of() {
      var index = 0;
      var length = arguments.length;
      var result = new (aTypedArrayConstructor(this))(length);
      while (length > index) result[index] = arguments[index++];
      return result;
    }, TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS);
    return es_typedArray_of;
  }
  requireEs_typedArray_of();
  var es_typedArray_set = {};
  var hasRequiredEs_typedArray_set;
  function requireEs_typedArray_set() {
    if (hasRequiredEs_typedArray_set) return es_typedArray_set;
    hasRequiredEs_typedArray_set = 1;
    var globalThis2 = requireGlobalThis();
    var call = requireFunctionCall();
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toOffset2 = requireToOffset();
    var toIndexedObject2 = requireToObject();
    var fails2 = requireFails();
    var RangeError2 = globalThis2.RangeError;
    var Int8Array2 = globalThis2.Int8Array;
    var Int8ArrayPrototype = Int8Array2 && Int8Array2.prototype;
    var $set = Int8ArrayPrototype && Int8ArrayPrototype.set;
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var WORKS_WITH_OBJECTS_AND_GENERIC_ON_TYPED_ARRAYS = !fails2(function() {
      var array = new Uint8ClampedArray(2);
      call($set, array, { length: 1, 0: 3 }, 1);
      return array[1] !== 3;
    });
    var TO_OBJECT_BUG = WORKS_WITH_OBJECTS_AND_GENERIC_ON_TYPED_ARRAYS && ArrayBufferViewCore.NATIVE_ARRAY_BUFFER_VIEWS && fails2(function() {
      var array = new Int8Array2(2);
      array.set(1);
      array.set("2", 1);
      return array[0] !== 0 || array[1] !== 2;
    });
    exportTypedArrayMethod("set", function set(arrayLike) {
      aTypedArray(this);
      var offset = toOffset2(arguments.length > 1 ? arguments[1] : void 0, 1);
      var src = toIndexedObject2(arrayLike);
      if (WORKS_WITH_OBJECTS_AND_GENERIC_ON_TYPED_ARRAYS) return call($set, this, src, offset);
      var length = this.length;
      var len = lengthOfArrayLike2(src);
      var index = 0;
      if (len + offset > length) throw new RangeError2("Wrong length");
      while (index < len) this[offset + index] = src[index++];
    }, !WORKS_WITH_OBJECTS_AND_GENERIC_ON_TYPED_ARRAYS || TO_OBJECT_BUG);
    return es_typedArray_set;
  }
  requireEs_typedArray_set();
  var es_typedArray_sort = {};
  var hasRequiredEs_typedArray_sort;
  function requireEs_typedArray_sort() {
    if (hasRequiredEs_typedArray_sort) return es_typedArray_sort;
    hasRequiredEs_typedArray_sort = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThisClause();
    var fails2 = requireFails();
    var aCallable2 = requireACallable();
    var internalSort = requireArraySort();
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var FF = requireEnvironmentFfVersion();
    var IE_OR_EDGE = requireEnvironmentIsIeOrEdge();
    var V8 = requireEnvironmentV8Version();
    var WEBKIT = requireEnvironmentWebkitVersion();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var Uint16Array = globalThis2.Uint16Array;
    var nativeSort = Uint16Array && uncurryThis(Uint16Array.prototype.sort);
    var ACCEPT_INCORRECT_ARGUMENTS = !!nativeSort && !(fails2(function() {
      nativeSort(new Uint16Array(2), null);
    }) && fails2(function() {
      nativeSort(new Uint16Array(2), {});
    }));
    var STABLE_SORT = !!nativeSort && !fails2(function() {
      if (V8) return V8 < 74;
      if (FF) return FF < 67;
      if (IE_OR_EDGE) return true;
      if (WEBKIT) return WEBKIT < 602;
      var array = new Uint16Array(516);
      var expected = Array(516);
      var index, mod;
      for (index = 0; index < 516; index++) {
        mod = index % 4;
        array[index] = 515 - index;
        expected[index] = index - 2 * mod + 3;
      }
      nativeSort(array, function(a, b) {
        return (a / 4 | 0) - (b / 4 | 0);
      });
      for (index = 0; index < 516; index++) {
        if (array[index] !== expected[index]) return true;
      }
    });
    var getSortCompare = function(comparefn) {
      return function(x, y) {
        if (comparefn !== void 0) return +comparefn(x, y) || 0;
        if (y !== y) return x !== x ? 0 : -1;
        if (x !== x) return 1;
        if (x === 0 && y === 0) return 1 / x > 0 ? 1 / y > 0 ? 0 : 1 : 1 / y > 0 ? -1 : 0;
        return x > y ? 1 : x < y ? -1 : 0;
      };
    };
    exportTypedArrayMethod("sort", function sort(comparefn) {
      if (comparefn !== void 0) aCallable2(comparefn);
      if (STABLE_SORT) return nativeSort(this, comparefn);
      return internalSort(aTypedArray(this), getSortCompare(comparefn));
    }, !STABLE_SORT || ACCEPT_INCORRECT_ARGUMENTS);
    return es_typedArray_sort;
  }
  requireEs_typedArray_sort();
  var es_typedArray_toReversed = {};
  var hasRequiredEs_typedArray_toReversed;
  function requireEs_typedArray_toReversed() {
    if (hasRequiredEs_typedArray_toReversed) return es_typedArray_toReversed;
    hasRequiredEs_typedArray_toReversed = 1;
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var getTypedArrayConstructor = ArrayBufferViewCore.getTypedArrayConstructor;
    exportTypedArrayMethod("toReversed", function toReversed() {
      var O = aTypedArray(this);
      var len = lengthOfArrayLike2(O);
      var A = new (getTypedArrayConstructor(O))(len);
      var k = 0;
      for (; k < len; k++) A[k] = O[len - k - 1];
      return A;
    });
    return es_typedArray_toReversed;
  }
  requireEs_typedArray_toReversed();
  var es_typedArray_toSorted = {};
  var hasRequiredEs_typedArray_toSorted;
  function requireEs_typedArray_toSorted() {
    if (hasRequiredEs_typedArray_toSorted) return es_typedArray_toSorted;
    hasRequiredEs_typedArray_toSorted = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var uncurryThis = requireFunctionUncurryThis();
    var aCallable2 = requireACallable();
    var arrayFromConstructorAndList2 = requireArrayFromConstructorAndList();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var getTypedArrayConstructor = ArrayBufferViewCore.getTypedArrayConstructor;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var sort = uncurryThis(ArrayBufferViewCore.TypedArrayPrototype.sort);
    exportTypedArrayMethod("toSorted", function toSorted(compareFn) {
      if (compareFn !== void 0) aCallable2(compareFn);
      var O = aTypedArray(this);
      var A = arrayFromConstructorAndList2(getTypedArrayConstructor(O), O);
      return sort(A, compareFn);
    });
    return es_typedArray_toSorted;
  }
  requireEs_typedArray_toSorted();
  var es_typedArray_with = {};
  var hasRequiredEs_typedArray_with;
  function requireEs_typedArray_with() {
    if (hasRequiredEs_typedArray_with) return es_typedArray_with;
    hasRequiredEs_typedArray_with = 1;
    var ArrayBufferViewCore = requireArrayBufferViewCore();
    var isBigIntArray2 = requireIsBigIntArray();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var toIntegerOrInfinity2 = requireToIntegerOrInfinity();
    var toBigInt2 = requireToBigInt();
    var aTypedArray = ArrayBufferViewCore.aTypedArray;
    var getTypedArrayConstructor = ArrayBufferViewCore.getTypedArrayConstructor;
    var exportTypedArrayMethod = ArrayBufferViewCore.exportTypedArrayMethod;
    var $RangeError = RangeError;
    var PROPER_ORDER = (function() {
      try {
        new Int8Array(1)["with"](2, { valueOf: function() {
          throw 8;
        } });
      } catch (error) {
        return error === 8;
      }
    })();
    var THROW_ON_NEGATIVE_FRACTIONAL_INDEX = PROPER_ORDER && (function() {
      try {
        new Int8Array(1)["with"](-0.5, 1);
      } catch (error) {
        return true;
      }
    })();
    exportTypedArrayMethod("with", { "with": function(index, value) {
      var O = aTypedArray(this);
      var len = lengthOfArrayLike2(O);
      var relativeIndex = toIntegerOrInfinity2(index);
      var actualIndex = relativeIndex < 0 ? len + relativeIndex : relativeIndex;
      var numericValue = isBigIntArray2(O) ? toBigInt2(value) : +value;
      if (actualIndex >= len || actualIndex < 0) throw new $RangeError("Incorrect index");
      var A = new (getTypedArrayConstructor(O))(len);
      var k = 0;
      for (; k < len; k++) A[k] = k === actualIndex ? numericValue : O[k];
      return A;
    } }["with"], !PROPER_ORDER || THROW_ON_NEGATIVE_FRACTIONAL_INDEX);
    return es_typedArray_with;
  }
  requireEs_typedArray_with();
  var es_uint8Array_fromBase64 = {};
  var anObjectOrUndefined;
  var hasRequiredAnObjectOrUndefined;
  function requireAnObjectOrUndefined() {
    if (hasRequiredAnObjectOrUndefined) return anObjectOrUndefined;
    hasRequiredAnObjectOrUndefined = 1;
    var isObject2 = requireIsObject();
    var $String = String;
    var $TypeError = TypeError;
    anObjectOrUndefined = function(argument) {
      if (argument === void 0 || isObject2(argument)) return argument;
      throw new $TypeError($String(argument) + " is not an object or undefined");
    };
    return anObjectOrUndefined;
  }
  var base64Map;
  var hasRequiredBase64Map;
  function requireBase64Map() {
    if (hasRequiredBase64Map) return base64Map;
    hasRequiredBase64Map = 1;
    var commonAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var base64Alphabet = commonAlphabet + "+/";
    var base64UrlAlphabet = commonAlphabet + "-_";
    var inverse = function(characters) {
      var result = {};
      var index = 0;
      for (; index < 64; index++) result[characters.charAt(index)] = index;
      return result;
    };
    base64Map = {
      i2c: base64Alphabet,
      c2i: inverse(base64Alphabet),
      i2cUrl: base64UrlAlphabet,
      c2iUrl: inverse(base64UrlAlphabet)
    };
    return base64Map;
  }
  var getAlphabetOption;
  var hasRequiredGetAlphabetOption;
  function requireGetAlphabetOption() {
    if (hasRequiredGetAlphabetOption) return getAlphabetOption;
    hasRequiredGetAlphabetOption = 1;
    var $TypeError = TypeError;
    getAlphabetOption = function(options) {
      var alphabet = options && options.alphabet;
      if (alphabet === void 0 || alphabet === "base64" || alphabet === "base64url") return alphabet || "base64";
      throw new $TypeError("Incorrect `alphabet` option");
    };
    return getAlphabetOption;
  }
  var uint8FromBase64;
  var hasRequiredUint8FromBase64;
  function requireUint8FromBase64() {
    if (hasRequiredUint8FromBase64) return uint8FromBase64;
    hasRequiredUint8FromBase64 = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var anObjectOrUndefined2 = requireAnObjectOrUndefined();
    var aString2 = requireAString();
    var hasOwn = requireHasOwnProperty();
    var base64Map2 = requireBase64Map();
    var getAlphabetOption2 = requireGetAlphabetOption();
    var notDetached = requireArrayBufferNotDetached();
    var base64Alphabet = base64Map2.c2i;
    var base64UrlAlphabet = base64Map2.c2iUrl;
    var SyntaxError2 = globalThis2.SyntaxError;
    var TypeError2 = globalThis2.TypeError;
    var $Array = globalThis2.Array;
    var at = uncurryThis("".charAt);
    var floor = Math.floor;
    var skipAsciiWhitespace = function(string, index) {
      var length = string.length;
      for (; index < length; index++) {
        var chr = at(string, index);
        if (chr !== " " && chr !== "	" && chr !== "\n" && chr !== "\f" && chr !== "\r") break;
      }
      return index;
    };
    var decodeBase64Chunk = function(chunk, alphabet, throwOnExtraBits) {
      var chunkLength = chunk.length;
      if (chunkLength < 4) {
        chunk += chunkLength === 2 ? "AA" : "A";
      }
      var triplet = (alphabet[at(chunk, 0)] << 18) + (alphabet[at(chunk, 1)] << 12) + (alphabet[at(chunk, 2)] << 6) + alphabet[at(chunk, 3)];
      var chunkBytes = [
        triplet >> 16 & 255,
        triplet >> 8 & 255,
        triplet & 255
      ];
      if (chunkLength === 2) {
        if (throwOnExtraBits && chunkBytes[1] !== 0) {
          throw new SyntaxError2("Extra bits");
        }
        return [chunkBytes[0]];
      }
      if (chunkLength === 3) {
        if (throwOnExtraBits && chunkBytes[2] !== 0) {
          throw new SyntaxError2("Extra bits");
        }
        return [chunkBytes[0], chunkBytes[1]];
      }
      return chunkBytes;
    };
    var writeBytes = function(bytes, elements, written) {
      var elementsLength = elements.length;
      for (var index = 0; index < elementsLength; index++) {
        bytes[written + index] = elements[index];
      }
      return written + elementsLength;
    };
    uint8FromBase64 = function(string, options, into, maxLength) {
      aString2(string);
      anObjectOrUndefined2(options);
      var alphabet = getAlphabetOption2(options) === "base64" ? base64Alphabet : base64UrlAlphabet;
      var lastChunkHandling = options ? options.lastChunkHandling : void 0;
      if (lastChunkHandling === void 0) lastChunkHandling = "loose";
      if (lastChunkHandling !== "loose" && lastChunkHandling !== "strict" && lastChunkHandling !== "stop-before-partial") {
        throw new TypeError2("Incorrect `lastChunkHandling` option");
      }
      if (into) notDetached(into.buffer);
      var stringLength = string.length;
      var bytes = into || $Array(floor(stringLength * 3 / 4));
      var written = 0;
      var read = 0;
      var chunk = "";
      var index = 0;
      if (maxLength) while (true) {
        index = skipAsciiWhitespace(string, index);
        if (index === stringLength) {
          if (chunk.length > 0) {
            if (lastChunkHandling === "stop-before-partial") {
              break;
            }
            if (lastChunkHandling === "loose") {
              if (chunk.length === 1) {
                throw new SyntaxError2("Malformed padding: exactly one additional character");
              }
              written = writeBytes(bytes, decodeBase64Chunk(chunk, alphabet, false), written);
            } else {
              throw new SyntaxError2("Missing padding");
            }
          }
          read = stringLength;
          break;
        }
        var chr = at(string, index);
        ++index;
        if (chr === "=") {
          if (chunk.length < 2) {
            throw new SyntaxError2("Padding is too early");
          }
          index = skipAsciiWhitespace(string, index);
          if (chunk.length === 2) {
            if (index === stringLength) {
              if (lastChunkHandling === "stop-before-partial") {
                break;
              }
              throw new SyntaxError2("Malformed padding: only one =");
            }
            if (at(string, index) === "=") {
              ++index;
              index = skipAsciiWhitespace(string, index);
            }
          }
          if (index < stringLength) {
            throw new SyntaxError2("Unexpected character after padding");
          }
          written = writeBytes(bytes, decodeBase64Chunk(chunk, alphabet, lastChunkHandling === "strict"), written);
          read = stringLength;
          break;
        }
        if (!hasOwn(alphabet, chr)) {
          throw new SyntaxError2("Unexpected character");
        }
        var remainingBytes = maxLength - written;
        if (remainingBytes === 1 && chunk.length === 2 || remainingBytes === 2 && chunk.length === 3) {
          break;
        }
        chunk += chr;
        if (chunk.length === 4) {
          written = writeBytes(bytes, decodeBase64Chunk(chunk, alphabet, false), written);
          chunk = "";
          read = index;
          if (written === maxLength) {
            break;
          }
        }
      }
      if (!into) bytes.length = written;
      return { bytes, read, written };
    };
    return uint8FromBase64;
  }
  var hasRequiredEs_uint8Array_fromBase64;
  function requireEs_uint8Array_fromBase64() {
    if (hasRequiredEs_uint8Array_fromBase64) return es_uint8Array_fromBase64;
    hasRequiredEs_uint8Array_fromBase64 = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var arrayFromConstructorAndList2 = requireArrayFromConstructorAndList();
    var $fromBase64 = requireUint8FromBase64();
    var Uint8Array2 = globalThis2.Uint8Array;
    var INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS = !Uint8Array2 || !Uint8Array2.fromBase64 || !(function() {
      try {
        Uint8Array2.fromBase64("a");
        return;
      } catch (error) {
      }
      try {
        Uint8Array2.fromBase64("", null);
      } catch (error) {
        return true;
      }
    })();
    if (Uint8Array2) $({ target: "Uint8Array", stat: true, forced: INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS }, {
      fromBase64: function fromBase64(string) {
        var result = $fromBase64(string, arguments.length > 1 ? arguments[1] : void 0, null, 9007199254740991);
        return arrayFromConstructorAndList2(Uint8Array2, result.bytes);
      }
    });
    return es_uint8Array_fromBase64;
  }
  requireEs_uint8Array_fromBase64();
  var es_uint8Array_fromHex = {};
  var uint8FromHex;
  var hasRequiredUint8FromHex;
  function requireUint8FromHex() {
    if (hasRequiredUint8FromHex) return uint8FromHex;
    hasRequiredUint8FromHex = 1;
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var Uint8Array2 = globalThis2.Uint8Array;
    var SyntaxError2 = globalThis2.SyntaxError;
    var min = Math.min;
    var stringMatch2 = uncurryThis("".match);
    uint8FromHex = function(string, into) {
      var stringLength = string.length;
      if (stringLength % 2 !== 0) throw new SyntaxError2("String should be an even number of characters");
      var maxLength = into ? min(into.length, stringLength / 2) : stringLength / 2;
      var bytes = into || new Uint8Array2(maxLength);
      var segments = stringMatch2(string, /[\S\s]{2}/g);
      var written = 0;
      for (; written < maxLength; written++) {
        var result = +("0x" + segments[written] + "0");
        if (result !== result) {
          throw new SyntaxError2("String should only contain hex characters");
        }
        bytes[written] = result >> 4;
      }
      return { bytes, read: written << 1 };
    };
    return uint8FromHex;
  }
  var hasRequiredEs_uint8Array_fromHex;
  function requireEs_uint8Array_fromHex() {
    if (hasRequiredEs_uint8Array_fromHex) return es_uint8Array_fromHex;
    hasRequiredEs_uint8Array_fromHex = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var aString2 = requireAString();
    var $fromHex = requireUint8FromHex();
    if (globalThis2.Uint8Array) $({ target: "Uint8Array", stat: true }, {
      fromHex: function fromHex(string) {
        return $fromHex(aString2(string)).bytes;
      }
    });
    return es_uint8Array_fromHex;
  }
  requireEs_uint8Array_fromHex();
  var es_uint8Array_setFromBase64 = {};
  var anUint8Array;
  var hasRequiredAnUint8Array;
  function requireAnUint8Array() {
    if (hasRequiredAnUint8Array) return anUint8Array;
    hasRequiredAnUint8Array = 1;
    var classof2 = requireClassof();
    var $TypeError = TypeError;
    anUint8Array = function(argument) {
      if (classof2(argument) === "Uint8Array") return argument;
      throw new $TypeError("Argument is not an Uint8Array");
    };
    return anUint8Array;
  }
  var hasRequiredEs_uint8Array_setFromBase64;
  function requireEs_uint8Array_setFromBase64() {
    if (hasRequiredEs_uint8Array_setFromBase64) return es_uint8Array_setFromBase64;
    hasRequiredEs_uint8Array_setFromBase64 = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var $fromBase64 = requireUint8FromBase64();
    var anUint8Array2 = requireAnUint8Array();
    var Uint8Array2 = globalThis2.Uint8Array;
    var INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS = !Uint8Array2 || !Uint8Array2.prototype.setFromBase64 || !(function() {
      var target = new Uint8Array2([255, 255, 255, 255, 255]);
      try {
        target.setFromBase64("", null);
        return;
      } catch (error) {
      }
      try {
        target.setFromBase64("a");
        return;
      } catch (error) {
      }
      try {
        target.setFromBase64("MjYyZg===");
      } catch (error) {
        return target[0] === 50 && target[1] === 54 && target[2] === 50 && target[3] === 255 && target[4] === 255;
      }
    })();
    if (Uint8Array2) $({ target: "Uint8Array", proto: true, forced: INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS }, {
      setFromBase64: function setFromBase64(string) {
        anUint8Array2(this);
        var result = $fromBase64(string, arguments.length > 1 ? arguments[1] : void 0, this, this.length);
        return { read: result.read, written: result.written };
      }
    });
    return es_uint8Array_setFromBase64;
  }
  requireEs_uint8Array_setFromBase64();
  var es_uint8Array_setFromHex = {};
  var hasRequiredEs_uint8Array_setFromHex;
  function requireEs_uint8Array_setFromHex() {
    if (hasRequiredEs_uint8Array_setFromHex) return es_uint8Array_setFromHex;
    hasRequiredEs_uint8Array_setFromHex = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var aString2 = requireAString();
    var anUint8Array2 = requireAnUint8Array();
    var notDetached = requireArrayBufferNotDetached();
    var $fromHex = requireUint8FromHex();
    function throwsOnLengthTrackingView() {
      try {
        var rab = new ArrayBuffer(16, { maxByteLength: 1024 });
        new Uint8Array(rab).setFromHex("cafed00d");
      } catch (error) {
        return true;
      }
    }
    if (globalThis2.Uint8Array) $({ target: "Uint8Array", proto: true, forced: throwsOnLengthTrackingView() }, {
      setFromHex: function setFromHex(string) {
        anUint8Array2(this);
        aString2(string);
        notDetached(this.buffer);
        var read = $fromHex(string, this).read;
        return { read, written: read / 2 };
      }
    });
    return es_uint8Array_setFromHex;
  }
  requireEs_uint8Array_setFromHex();
  var es_uint8Array_toBase64 = {};
  var hasRequiredEs_uint8Array_toBase64;
  function requireEs_uint8Array_toBase64() {
    if (hasRequiredEs_uint8Array_toBase64) return es_uint8Array_toBase64;
    hasRequiredEs_uint8Array_toBase64 = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var anObjectOrUndefined2 = requireAnObjectOrUndefined();
    var anUint8Array2 = requireAnUint8Array();
    var notDetached = requireArrayBufferNotDetached();
    var base64Map2 = requireBase64Map();
    var getAlphabetOption2 = requireGetAlphabetOption();
    var base64Alphabet = base64Map2.i2c;
    var base64UrlAlphabet = base64Map2.i2cUrl;
    var $floor = Math.floor;
    var $ceil = Math.ceil;
    var charAt = uncurryThis("".charAt);
    var Uint8Array2 = globalThis2.Uint8Array;
    var $Array = globalThis2.Array;
    var join = uncurryThis([].join);
    var INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS = !Uint8Array2 || !Uint8Array2.prototype.toBase64 || !(function() {
      try {
        var target = new Uint8Array2();
        target.toBase64(null);
      } catch (error) {
        return true;
      }
    })();
    if (Uint8Array2) $({ target: "Uint8Array", proto: true, forced: INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS }, {
      toBase64: function toBase64() {
        var array = anUint8Array2(this);
        var options = arguments.length ? anObjectOrUndefined2(arguments[0]) : void 0;
        var alphabet = getAlphabetOption2(options) === "base64" ? base64Alphabet : base64UrlAlphabet;
        var omitPadding = !!options && !!options.omitPadding;
        notDetached(this.buffer);
        var i = 0;
        var length = array.length;
        var result = $Array(omitPadding ? $floor(length / 3) * 4 + (length % 3 ? length % 3 + 1 : 0) : $ceil(length / 3) * 4);
        var written = 0;
        var triplet;
        var at = function(shift) {
          return charAt(alphabet, triplet >> 6 * shift & 63);
        };
        for (; i + 2 < length; i += 3) {
          triplet = (array[i] << 16) + (array[i + 1] << 8) + array[i + 2];
          result[written++] = at(3);
          result[written++] = at(2);
          result[written++] = at(1);
          result[written++] = at(0);
        }
        if (i + 2 === length) {
          triplet = (array[i] << 16) + (array[i + 1] << 8);
          result[written++] = at(3);
          result[written++] = at(2);
          result[written++] = at(1);
          if (!omitPadding) result[written++] = "=";
        } else if (i + 1 === length) {
          triplet = array[i] << 16;
          result[written++] = at(3);
          result[written++] = at(2);
          if (!omitPadding) {
            result[written++] = "=";
            result[written++] = "=";
          }
        }
        return join(result, "");
      }
    });
    return es_uint8Array_toBase64;
  }
  requireEs_uint8Array_toBase64();
  var es_uint8Array_toHex = {};
  var hasRequiredEs_uint8Array_toHex;
  function requireEs_uint8Array_toHex() {
    if (hasRequiredEs_uint8Array_toHex) return es_uint8Array_toHex;
    hasRequiredEs_uint8Array_toHex = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var uncurryThis = requireFunctionUncurryThis();
    var anUint8Array2 = requireAnUint8Array();
    var notDetached = requireArrayBufferNotDetached();
    var numberToString2 = uncurryThis(1.1.toString);
    var join = uncurryThis([].join);
    var $Array = Array;
    var Uint8Array2 = globalThis2.Uint8Array;
    var INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS = !Uint8Array2 || !Uint8Array2.prototype.toHex || !(function() {
      try {
        var target = new Uint8Array2([255, 255, 255, 255, 255, 255, 255, 255]);
        return target.toHex() === "ffffffffffffffff";
      } catch (error) {
        return false;
      }
    })();
    if (Uint8Array2) $({ target: "Uint8Array", proto: true, forced: INCORRECT_BEHAVIOR_OR_DOESNT_EXISTS }, {
      toHex: function toHex() {
        anUint8Array2(this);
        notDetached(this.buffer);
        var result = $Array(this.length);
        for (var i = 0, length = this.length; i < length; i++) {
          var hex = numberToString2(this[i], 16);
          result[i] = hex.length === 1 ? "0" + hex : hex;
        }
        return join(result, "");
      }
    });
    return es_uint8Array_toHex;
  }
  requireEs_uint8Array_toHex();
  var es_weakMap_getOrInsert = {};
  var weakMapHelpers;
  var hasRequiredWeakMapHelpers;
  function requireWeakMapHelpers() {
    if (hasRequiredWeakMapHelpers) return weakMapHelpers;
    hasRequiredWeakMapHelpers = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var WeakMapPrototype = WeakMap.prototype;
    weakMapHelpers = {
      // eslint-disable-next-line es/no-weak-map -- safe
      WeakMap,
      set: uncurryThis(WeakMapPrototype.set),
      get: uncurryThis(WeakMapPrototype.get),
      has: uncurryThis(WeakMapPrototype.has),
      remove: uncurryThis(WeakMapPrototype["delete"])
    };
    return weakMapHelpers;
  }
  var hasRequiredEs_weakMap_getOrInsert;
  function requireEs_weakMap_getOrInsert() {
    if (hasRequiredEs_weakMap_getOrInsert) return es_weakMap_getOrInsert;
    hasRequiredEs_weakMap_getOrInsert = 1;
    var $ = require_export();
    var WeakMapHelpers = requireWeakMapHelpers();
    var IS_PURE = requireIsPure();
    var get = WeakMapHelpers.get;
    var has = WeakMapHelpers.has;
    var set = WeakMapHelpers.set;
    $({ target: "WeakMap", proto: true, real: true, forced: IS_PURE }, {
      getOrInsert: function getOrInsert(key, value) {
        if (has(this, key)) return get(this, key);
        set(this, key, value);
        return value;
      }
    });
    return es_weakMap_getOrInsert;
  }
  requireEs_weakMap_getOrInsert();
  var es_weakMap_getOrInsertComputed = {};
  var aWeakMap;
  var hasRequiredAWeakMap;
  function requireAWeakMap() {
    if (hasRequiredAWeakMap) return aWeakMap;
    hasRequiredAWeakMap = 1;
    var has = requireWeakMapHelpers().has;
    aWeakMap = function(it) {
      has(it);
      return it;
    };
    return aWeakMap;
  }
  var aWeakKey;
  var hasRequiredAWeakKey;
  function requireAWeakKey() {
    if (hasRequiredAWeakKey) return aWeakKey;
    hasRequiredAWeakKey = 1;
    var WeakMapHelpers = requireWeakMapHelpers();
    var weakmap = new WeakMapHelpers.WeakMap();
    var set = WeakMapHelpers.set;
    var remove = WeakMapHelpers.remove;
    aWeakKey = function(key) {
      set(weakmap, key, 1);
      remove(weakmap, key);
      return key;
    };
    return aWeakKey;
  }
  var hasRequiredEs_weakMap_getOrInsertComputed;
  function requireEs_weakMap_getOrInsertComputed() {
    if (hasRequiredEs_weakMap_getOrInsertComputed) return es_weakMap_getOrInsertComputed;
    hasRequiredEs_weakMap_getOrInsertComputed = 1;
    var $ = require_export();
    var aCallable2 = requireACallable();
    var aWeakMap2 = requireAWeakMap();
    var aWeakKey2 = requireAWeakKey();
    var WeakMapHelpers = requireWeakMapHelpers();
    var IS_PURE = requireIsPure();
    var get = WeakMapHelpers.get;
    var has = WeakMapHelpers.has;
    var set = WeakMapHelpers.set;
    var FORCED = IS_PURE || !(function() {
      try {
        if (WeakMap.prototype.getOrInsertComputed) (/* @__PURE__ */ new WeakMap()).getOrInsertComputed(1, function() {
          throw 1;
        });
      } catch (error) {
        return error instanceof TypeError;
      }
    })();
    $({ target: "WeakMap", proto: true, real: true, forced: FORCED }, {
      getOrInsertComputed: function getOrInsertComputed(key, callbackfn) {
        if (!IS_PURE) aWeakMap2(this);
        aWeakKey2(key);
        aCallable2(callbackfn);
        if (has(this, key)) return get(this, key);
        var value = callbackfn(key);
        set(this, key, value);
        return value;
      }
    });
    return es_weakMap_getOrInsertComputed;
  }
  requireEs_weakMap_getOrInsertComputed();
  var web_domCollections_iterator = {};
  var domIterables;
  var hasRequiredDomIterables;
  function requireDomIterables() {
    if (hasRequiredDomIterables) return domIterables;
    hasRequiredDomIterables = 1;
    domIterables = {
      CSSRuleList: 0,
      CSSStyleDeclaration: 0,
      CSSValueList: 0,
      ClientRectList: 0,
      DOMRectList: 0,
      DOMStringList: 0,
      DOMTokenList: 1,
      DataTransferItemList: 0,
      FileList: 0,
      HTMLAllCollection: 0,
      HTMLCollection: 0,
      HTMLFormElement: 0,
      HTMLSelectElement: 0,
      MediaList: 0,
      MimeTypeArray: 0,
      NamedNodeMap: 0,
      NodeList: 1,
      PaintRequestList: 0,
      Plugin: 0,
      PluginArray: 0,
      SVGLengthList: 0,
      SVGNumberList: 0,
      SVGPathSegList: 0,
      SVGPointList: 0,
      SVGStringList: 0,
      SVGTransformList: 0,
      SourceBufferList: 0,
      StyleSheetList: 0,
      TextTrackCueList: 0,
      TextTrackList: 0,
      TouchList: 0
    };
    return domIterables;
  }
  var domTokenListPrototype;
  var hasRequiredDomTokenListPrototype;
  function requireDomTokenListPrototype() {
    if (hasRequiredDomTokenListPrototype) return domTokenListPrototype;
    hasRequiredDomTokenListPrototype = 1;
    var documentCreateElement2 = requireDocumentCreateElement();
    var classList = documentCreateElement2("span").classList;
    var DOMTokenListPrototype = classList && classList.constructor && classList.constructor.prototype;
    domTokenListPrototype = DOMTokenListPrototype === Object.prototype ? void 0 : DOMTokenListPrototype;
    return domTokenListPrototype;
  }
  var iteratorDefine;
  var hasRequiredIteratorDefine;
  function requireIteratorDefine() {
    if (hasRequiredIteratorDefine) return iteratorDefine;
    hasRequiredIteratorDefine = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var IS_PURE = requireIsPure();
    var FunctionName = requireFunctionName();
    var isCallable2 = requireIsCallable();
    var createIteratorConstructor = requireIteratorCreateConstructor();
    var getPrototypeOf2 = requireObjectGetPrototypeOf();
    var setPrototypeOf2 = requireObjectSetPrototypeOf();
    var setToStringTag2 = requireSetToStringTag();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var Iterators = requireIterators();
    var IteratorsCore = requireIteratorsCore();
    var PROPER_FUNCTION_NAME = FunctionName.PROPER;
    var CONFIGURABLE_FUNCTION_NAME = FunctionName.CONFIGURABLE;
    var IteratorPrototype = IteratorsCore.IteratorPrototype;
    var BUGGY_SAFARI_ITERATORS = IteratorsCore.BUGGY_SAFARI_ITERATORS;
    var ITERATOR = wellKnownSymbol2("iterator");
    var KEYS = "keys";
    var VALUES = "values";
    var ENTRIES = "entries";
    var returnThis = function() {
      return this;
    };
    iteratorDefine = function(Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
      createIteratorConstructor(IteratorConstructor, NAME, next);
      var getIterationMethod = function(KIND) {
        if (KIND === DEFAULT && defaultIterator) return defaultIterator;
        if (!BUGGY_SAFARI_ITERATORS && KIND && KIND in IterablePrototype) return IterablePrototype[KIND];
        switch (KIND) {
          case KEYS:
            return function keys() {
              return new IteratorConstructor(this, KIND);
            };
          case VALUES:
            return function values() {
              return new IteratorConstructor(this, KIND);
            };
          case ENTRIES:
            return function entries2() {
              return new IteratorConstructor(this, KIND);
            };
        }
        return function() {
          return new IteratorConstructor(this);
        };
      };
      var TO_STRING_TAG = NAME + " Iterator";
      var INCORRECT_VALUES_NAME = false;
      var IterablePrototype = Iterable.prototype;
      var nativeIterator = IterablePrototype[ITERATOR] || IterablePrototype["@@iterator"] || DEFAULT && IterablePrototype[DEFAULT];
      var defaultIterator = !BUGGY_SAFARI_ITERATORS && nativeIterator || getIterationMethod(DEFAULT);
      var anyNativeIterator = NAME === "Array" ? IterablePrototype.entries || nativeIterator : nativeIterator;
      var CurrentIteratorPrototype, methods, KEY;
      if (anyNativeIterator) {
        CurrentIteratorPrototype = getPrototypeOf2(anyNativeIterator.call(new Iterable()));
        if (CurrentIteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
          if (!IS_PURE && getPrototypeOf2(CurrentIteratorPrototype) !== IteratorPrototype) {
            if (setPrototypeOf2) {
              setPrototypeOf2(CurrentIteratorPrototype, IteratorPrototype);
            } else if (!isCallable2(CurrentIteratorPrototype[ITERATOR])) {
              defineBuiltIn2(CurrentIteratorPrototype, ITERATOR, returnThis);
            }
          }
          setToStringTag2(CurrentIteratorPrototype, TO_STRING_TAG, true, true);
          if (IS_PURE) Iterators[TO_STRING_TAG] = returnThis;
        }
      }
      if (PROPER_FUNCTION_NAME && DEFAULT === VALUES && nativeIterator && nativeIterator.name !== VALUES) {
        if (!IS_PURE && CONFIGURABLE_FUNCTION_NAME) {
          createNonEnumerableProperty2(IterablePrototype, "name", VALUES);
        } else {
          INCORRECT_VALUES_NAME = true;
          defaultIterator = function values() {
            return call(nativeIterator, this);
          };
        }
      }
      if (DEFAULT) {
        methods = {
          values: getIterationMethod(VALUES),
          keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
          entries: getIterationMethod(ENTRIES)
        };
        if (FORCED) for (KEY in methods) {
          if (BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
            defineBuiltIn2(IterablePrototype, KEY, methods[KEY]);
          }
        }
        else $({ target: NAME, proto: true, forced: BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME }, methods);
      }
      if ((!IS_PURE || FORCED) && IterablePrototype[ITERATOR] !== defaultIterator) {
        defineBuiltIn2(IterablePrototype, ITERATOR, defaultIterator, { name: DEFAULT });
      }
      Iterators[NAME] = defaultIterator;
      return methods;
    };
    return iteratorDefine;
  }
  var es_array_iterator;
  var hasRequiredEs_array_iterator;
  function requireEs_array_iterator() {
    if (hasRequiredEs_array_iterator) return es_array_iterator;
    hasRequiredEs_array_iterator = 1;
    var toIndexedObject2 = requireToIndexedObject();
    var addToUnscopables2 = requireAddToUnscopables();
    var Iterators = requireIterators();
    var InternalStateModule = requireInternalState();
    var defineProperty = requireObjectDefineProperty().f;
    var defineIterator = requireIteratorDefine();
    var createIterResultObject2 = requireCreateIterResultObject();
    var IS_PURE = requireIsPure();
    var DESCRIPTORS = requireDescriptors();
    var ARRAY_ITERATOR = "Array Iterator";
    var setInternalState = InternalStateModule.set;
    var getInternalState = InternalStateModule.getterFor(ARRAY_ITERATOR);
    es_array_iterator = defineIterator(Array, "Array", function(iterated, kind) {
      setInternalState(this, {
        type: ARRAY_ITERATOR,
        target: toIndexedObject2(iterated),
        // target
        index: 0,
        // next index
        kind
        // kind
      });
    }, function() {
      var state = getInternalState(this);
      var target = state.target;
      var index = state.index++;
      if (!target || index >= target.length) {
        state.target = null;
        return createIterResultObject2(void 0, true);
      }
      switch (state.kind) {
        case "keys":
          return createIterResultObject2(index, false);
        case "values":
          return createIterResultObject2(target[index], false);
      }
      return createIterResultObject2([index, target[index]], false);
    }, "values");
    var values = Iterators.Arguments = Iterators.Array;
    addToUnscopables2("keys");
    addToUnscopables2("values");
    addToUnscopables2("entries");
    if (!IS_PURE && DESCRIPTORS && values.name !== "values") try {
      defineProperty(values, "name", { value: "values" });
    } catch (error) {
    }
    return es_array_iterator;
  }
  var hasRequiredWeb_domCollections_iterator;
  function requireWeb_domCollections_iterator() {
    if (hasRequiredWeb_domCollections_iterator) return web_domCollections_iterator;
    hasRequiredWeb_domCollections_iterator = 1;
    var globalThis2 = requireGlobalThis();
    var DOMIterables = requireDomIterables();
    var DOMTokenListPrototype = requireDomTokenListPrototype();
    var ArrayIteratorMethods = requireEs_array_iterator();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var setToStringTag2 = requireSetToStringTag();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var ITERATOR = wellKnownSymbol2("iterator");
    var ArrayValues = ArrayIteratorMethods.values;
    var handlePrototype = function(CollectionPrototype, COLLECTION_NAME2) {
      if (CollectionPrototype) {
        if (CollectionPrototype[ITERATOR] !== ArrayValues) try {
          createNonEnumerableProperty2(CollectionPrototype, ITERATOR, ArrayValues);
        } catch (error) {
          CollectionPrototype[ITERATOR] = ArrayValues;
        }
        setToStringTag2(CollectionPrototype, COLLECTION_NAME2, true);
        if (DOMIterables[COLLECTION_NAME2]) for (var METHOD_NAME in ArrayIteratorMethods) {
          if (CollectionPrototype[METHOD_NAME] !== ArrayIteratorMethods[METHOD_NAME]) try {
            createNonEnumerableProperty2(CollectionPrototype, METHOD_NAME, ArrayIteratorMethods[METHOD_NAME]);
          } catch (error) {
            CollectionPrototype[METHOD_NAME] = ArrayIteratorMethods[METHOD_NAME];
          }
        }
      }
    };
    for (var COLLECTION_NAME in DOMIterables) {
      handlePrototype(globalThis2[COLLECTION_NAME] && globalThis2[COLLECTION_NAME].prototype, COLLECTION_NAME);
    }
    handlePrototype(DOMTokenListPrototype, "DOMTokenList");
    return web_domCollections_iterator;
  }
  requireWeb_domCollections_iterator();
  var web_domException_constructor = {};
  var errorToString;
  var hasRequiredErrorToString;
  function requireErrorToString() {
    if (hasRequiredErrorToString) return errorToString;
    hasRequiredErrorToString = 1;
    var DESCRIPTORS = requireDescriptors();
    var fails2 = requireFails();
    var anObject2 = requireAnObject();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var nativeErrorToString = Error.prototype.toString;
    var INCORRECT_TO_STRING = fails2(function() {
      if (DESCRIPTORS) {
        var object = Object.create(Object.defineProperty({}, "name", { get: function() {
          return this === object;
        } }));
        if (nativeErrorToString.call(object) !== "true") return true;
      }
      return nativeErrorToString.call({ message: 1, name: 2 }) !== "2: 1" || nativeErrorToString.call({}) !== "Error";
    });
    errorToString = INCORRECT_TO_STRING ? function toString2() {
      var O = anObject2(this);
      var name = normalizeStringArgument2(O.name, "Error");
      var message = normalizeStringArgument2(O.message);
      return !name ? message : !message ? name : name + ": " + message;
    } : nativeErrorToString;
    return errorToString;
  }
  var domExceptionConstants;
  var hasRequiredDomExceptionConstants;
  function requireDomExceptionConstants() {
    if (hasRequiredDomExceptionConstants) return domExceptionConstants;
    hasRequiredDomExceptionConstants = 1;
    domExceptionConstants = {
      IndexSizeError: { s: "INDEX_SIZE_ERR", c: 1, m: 1 },
      DOMStringSizeError: { s: "DOMSTRING_SIZE_ERR", c: 2, m: 0 },
      HierarchyRequestError: { s: "HIERARCHY_REQUEST_ERR", c: 3, m: 1 },
      WrongDocumentError: { s: "WRONG_DOCUMENT_ERR", c: 4, m: 1 },
      InvalidCharacterError: { s: "INVALID_CHARACTER_ERR", c: 5, m: 1 },
      NoDataAllowedError: { s: "NO_DATA_ALLOWED_ERR", c: 6, m: 0 },
      NoModificationAllowedError: { s: "NO_MODIFICATION_ALLOWED_ERR", c: 7, m: 1 },
      NotFoundError: { s: "NOT_FOUND_ERR", c: 8, m: 1 },
      NotSupportedError: { s: "NOT_SUPPORTED_ERR", c: 9, m: 1 },
      InUseAttributeError: { s: "INUSE_ATTRIBUTE_ERR", c: 10, m: 1 },
      InvalidStateError: { s: "INVALID_STATE_ERR", c: 11, m: 1 },
      SyntaxError: { s: "SYNTAX_ERR", c: 12, m: 1 },
      InvalidModificationError: { s: "INVALID_MODIFICATION_ERR", c: 13, m: 1 },
      NamespaceError: { s: "NAMESPACE_ERR", c: 14, m: 1 },
      InvalidAccessError: { s: "INVALID_ACCESS_ERR", c: 15, m: 1 },
      ValidationError: { s: "VALIDATION_ERR", c: 16, m: 0 },
      TypeMismatchError: { s: "TYPE_MISMATCH_ERR", c: 17, m: 1 },
      SecurityError: { s: "SECURITY_ERR", c: 18, m: 1 },
      NetworkError: { s: "NETWORK_ERR", c: 19, m: 1 },
      AbortError: { s: "ABORT_ERR", c: 20, m: 1 },
      URLMismatchError: { s: "URL_MISMATCH_ERR", c: 21, m: 1 },
      QuotaExceededError: { s: "QUOTA_EXCEEDED_ERR", c: 22, m: 1 },
      TimeoutError: { s: "TIMEOUT_ERR", c: 23, m: 1 },
      InvalidNodeTypeError: { s: "INVALID_NODE_TYPE_ERR", c: 24, m: 1 },
      DataCloneError: { s: "DATA_CLONE_ERR", c: 25, m: 1 }
    };
    return domExceptionConstants;
  }
  var hasRequiredWeb_domException_constructor;
  function requireWeb_domException_constructor() {
    if (hasRequiredWeb_domException_constructor) return web_domException_constructor;
    hasRequiredWeb_domException_constructor = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var getBuiltInNodeModule2 = requireGetBuiltInNodeModule();
    var fails2 = requireFails();
    var create2 = requireObjectCreate();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var defineProperty = requireObjectDefineProperty().f;
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var hasOwn = requireHasOwnProperty();
    var anInstance2 = requireAnInstance();
    var anObject2 = requireAnObject();
    var errorToString2 = requireErrorToString();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var DOMExceptionConstants = requireDomExceptionConstants();
    var clearErrorStack = requireErrorStackClear();
    var InternalStateModule = requireInternalState();
    var DESCRIPTORS = requireDescriptors();
    var IS_PURE = requireIsPure();
    var DOM_EXCEPTION = "DOMException";
    var DATA_CLONE_ERR = "DATA_CLONE_ERR";
    var Error2 = getBuiltIn2("Error");
    var NativeDOMException = getBuiltIn2(DOM_EXCEPTION) || (function() {
      try {
        var MessageChannel = getBuiltIn2("MessageChannel") || getBuiltInNodeModule2("worker_threads").MessageChannel;
        new MessageChannel().port1.postMessage(/* @__PURE__ */ new WeakMap());
      } catch (error) {
        if (error.name === DATA_CLONE_ERR && error.code === 25) return error.constructor;
      }
    })();
    var NativeDOMExceptionPrototype = NativeDOMException && NativeDOMException.prototype;
    var ErrorPrototype = Error2.prototype;
    var setInternalState = InternalStateModule.set;
    var getInternalState = InternalStateModule.getterFor(DOM_EXCEPTION);
    var HAS_STACK = "stack" in new Error2(DOM_EXCEPTION);
    var codeFor = function(name) {
      return hasOwn(DOMExceptionConstants, name) && DOMExceptionConstants[name].m ? DOMExceptionConstants[name].c : 0;
    };
    var $DOMException = function DOMException2() {
      anInstance2(this, DOMExceptionPrototype);
      var argumentsLength = arguments.length;
      var message = normalizeStringArgument2(argumentsLength < 1 ? void 0 : arguments[0]);
      var name = normalizeStringArgument2(argumentsLength < 2 ? void 0 : arguments[1], "Error");
      var code = codeFor(name);
      setInternalState(this, {
        type: DOM_EXCEPTION,
        name,
        message,
        code
      });
      if (!DESCRIPTORS) {
        this.name = name;
        this.message = message;
        this.code = code;
      }
      if (HAS_STACK) {
        var error = new Error2(message);
        error.name = DOM_EXCEPTION;
        defineProperty(this, "stack", createPropertyDescriptor2(1, clearErrorStack(error.stack, 1)));
      }
    };
    var DOMExceptionPrototype = $DOMException.prototype = create2(ErrorPrototype);
    var createGetterDescriptor = function(get) {
      return { enumerable: true, configurable: true, get };
    };
    var getterFor = function(key2) {
      return createGetterDescriptor(function() {
        return getInternalState(this)[key2];
      });
    };
    if (DESCRIPTORS) {
      defineBuiltInAccessor2(DOMExceptionPrototype, "code", getterFor("code"));
      defineBuiltInAccessor2(DOMExceptionPrototype, "message", getterFor("message"));
      defineBuiltInAccessor2(DOMExceptionPrototype, "name", getterFor("name"));
    }
    defineProperty(DOMExceptionPrototype, "constructor", createPropertyDescriptor2(1, $DOMException));
    var INCORRECT_CONSTRUCTOR = fails2(function() {
      return !(new NativeDOMException() instanceof Error2);
    });
    var INCORRECT_TO_STRING = INCORRECT_CONSTRUCTOR || fails2(function() {
      return ErrorPrototype.toString !== errorToString2 || String(new NativeDOMException(1, 2)) !== "2: 1";
    });
    var INCORRECT_CODE = INCORRECT_CONSTRUCTOR || fails2(function() {
      return new NativeDOMException(1, "DataCloneError").code !== 25;
    });
    var MISSED_CONSTANTS = INCORRECT_CONSTRUCTOR || NativeDOMException[DATA_CLONE_ERR] !== 25 || NativeDOMExceptionPrototype[DATA_CLONE_ERR] !== 25;
    var FORCED_CONSTRUCTOR = IS_PURE ? INCORRECT_TO_STRING || INCORRECT_CODE || MISSED_CONSTANTS : INCORRECT_CONSTRUCTOR;
    $({ global: true, constructor: true, forced: FORCED_CONSTRUCTOR }, {
      DOMException: FORCED_CONSTRUCTOR ? $DOMException : NativeDOMException
    });
    var PolyfilledDOMException = getBuiltIn2(DOM_EXCEPTION);
    var PolyfilledDOMExceptionPrototype = PolyfilledDOMException.prototype;
    if (INCORRECT_TO_STRING && (IS_PURE || NativeDOMException === PolyfilledDOMException)) {
      defineBuiltIn2(PolyfilledDOMExceptionPrototype, "toString", errorToString2);
    }
    if (INCORRECT_CODE && DESCRIPTORS && NativeDOMException === PolyfilledDOMException) {
      defineBuiltInAccessor2(PolyfilledDOMExceptionPrototype, "code", createGetterDescriptor(function() {
        return codeFor(anObject2(this).name);
      }));
    }
    for (var key in DOMExceptionConstants) if (hasOwn(DOMExceptionConstants, key)) {
      var constant = DOMExceptionConstants[key];
      var constantName = constant.s;
      var descriptor = createPropertyDescriptor2(6, constant.c);
      if (!hasOwn(PolyfilledDOMException, constantName)) {
        defineProperty(PolyfilledDOMException, constantName, descriptor);
      }
      if (!hasOwn(PolyfilledDOMExceptionPrototype, constantName)) {
        defineProperty(PolyfilledDOMExceptionPrototype, constantName, descriptor);
      }
    }
    return web_domException_constructor;
  }
  requireWeb_domException_constructor();
  var web_domException_stack = {};
  var hasRequiredWeb_domException_stack;
  function requireWeb_domException_stack() {
    if (hasRequiredWeb_domException_stack) return web_domException_stack;
    hasRequiredWeb_domException_stack = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var getBuiltIn2 = requireGetBuiltIn();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var defineProperty = requireObjectDefineProperty().f;
    var hasOwn = requireHasOwnProperty();
    var anInstance2 = requireAnInstance();
    var inheritIfRequired2 = requireInheritIfRequired();
    var normalizeStringArgument2 = requireNormalizeStringArgument();
    var DOMExceptionConstants = requireDomExceptionConstants();
    var clearErrorStack = requireErrorStackClear();
    var DESCRIPTORS = requireDescriptors();
    var IS_PURE = requireIsPure();
    var DOM_EXCEPTION = "DOMException";
    var Error2 = getBuiltIn2("Error");
    var NativeDOMException = getBuiltIn2(DOM_EXCEPTION);
    var $DOMException = function DOMException2() {
      anInstance2(this, DOMExceptionPrototype);
      var argumentsLength = arguments.length;
      var message = normalizeStringArgument2(argumentsLength < 1 ? void 0 : arguments[0]);
      var name = normalizeStringArgument2(argumentsLength < 2 ? void 0 : arguments[1], "Error");
      var that = new NativeDOMException(message, name);
      var error = new Error2(message);
      error.name = DOM_EXCEPTION;
      defineProperty(that, "stack", createPropertyDescriptor2(1, clearErrorStack(error.stack, 1)));
      inheritIfRequired2(that, this, $DOMException);
      return that;
    };
    var DOMExceptionPrototype = $DOMException.prototype = NativeDOMException.prototype;
    var ERROR_HAS_STACK = "stack" in new Error2(DOM_EXCEPTION);
    var DOM_EXCEPTION_HAS_STACK = "stack" in new NativeDOMException(1, 2);
    var descriptor = NativeDOMException && DESCRIPTORS && Object.getOwnPropertyDescriptor(globalThis2, DOM_EXCEPTION);
    var BUGGY_DESCRIPTOR = !!descriptor && !(descriptor.writable && descriptor.configurable);
    var FORCED_CONSTRUCTOR = ERROR_HAS_STACK && !BUGGY_DESCRIPTOR && !DOM_EXCEPTION_HAS_STACK;
    $({ global: true, constructor: true, forced: IS_PURE || FORCED_CONSTRUCTOR }, {
      // TODO: fix export logic
      DOMException: FORCED_CONSTRUCTOR ? $DOMException : NativeDOMException
    });
    var PolyfilledDOMException = getBuiltIn2(DOM_EXCEPTION);
    var PolyfilledDOMExceptionPrototype = PolyfilledDOMException.prototype;
    if (PolyfilledDOMExceptionPrototype.constructor !== PolyfilledDOMException) {
      if (!IS_PURE) {
        defineProperty(PolyfilledDOMExceptionPrototype, "constructor", createPropertyDescriptor2(1, PolyfilledDOMException));
      }
      for (var key in DOMExceptionConstants) if (hasOwn(DOMExceptionConstants, key)) {
        var constant = DOMExceptionConstants[key];
        var constantName = constant.s;
        if (!hasOwn(PolyfilledDOMException, constantName)) {
          defineProperty(PolyfilledDOMException, constantName, createPropertyDescriptor2(6, constant.c));
        }
      }
    }
    return web_domException_stack;
  }
  requireWeb_domException_stack();
  var web_domException_toStringTag = {};
  var hasRequiredWeb_domException_toStringTag;
  function requireWeb_domException_toStringTag() {
    if (hasRequiredWeb_domException_toStringTag) return web_domException_toStringTag;
    hasRequiredWeb_domException_toStringTag = 1;
    var getBuiltIn2 = requireGetBuiltIn();
    var setToStringTag2 = requireSetToStringTag();
    var DOM_EXCEPTION = "DOMException";
    setToStringTag2(getBuiltIn2(DOM_EXCEPTION), DOM_EXCEPTION);
    return web_domException_toStringTag;
  }
  requireWeb_domException_toStringTag();
  var web_immediate = {};
  var web_clearImmediate = {};
  var validateArgumentsLength;
  var hasRequiredValidateArgumentsLength;
  function requireValidateArgumentsLength() {
    if (hasRequiredValidateArgumentsLength) return validateArgumentsLength;
    hasRequiredValidateArgumentsLength = 1;
    var $TypeError = TypeError;
    validateArgumentsLength = function(passed, required) {
      if (passed < required) throw new $TypeError("Not enough arguments");
      return passed;
    };
    return validateArgumentsLength;
  }
  var environmentIsIos;
  var hasRequiredEnvironmentIsIos;
  function requireEnvironmentIsIos() {
    if (hasRequiredEnvironmentIsIos) return environmentIsIos;
    hasRequiredEnvironmentIsIos = 1;
    var userAgent = requireEnvironmentUserAgent();
    environmentIsIos = /ipad|iphone|ipod/i.test(userAgent) && /applewebkit/i.test(userAgent);
    return environmentIsIos;
  }
  var task;
  var hasRequiredTask;
  function requireTask() {
    if (hasRequiredTask) return task;
    hasRequiredTask = 1;
    var globalThis2 = requireGlobalThis();
    var apply2 = requireFunctionApply();
    var bind = requireFunctionBindContext();
    var isCallable2 = requireIsCallable();
    var hasOwn = requireHasOwnProperty();
    var fails2 = requireFails();
    var html2 = requireHtml();
    var arraySlice2 = requireArraySlice();
    var createElement = requireDocumentCreateElement();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var IS_IOS = requireEnvironmentIsIos();
    var IS_NODE = requireEnvironmentIsNode();
    var set = globalThis2.setImmediate;
    var clear = globalThis2.clearImmediate;
    var process = globalThis2.process;
    var Dispatch = globalThis2.Dispatch;
    var Function2 = globalThis2.Function;
    var MessageChannel = globalThis2.MessageChannel;
    var String2 = globalThis2.String;
    var counter = 0;
    var queue2 = {};
    var ONREADYSTATECHANGE = "onreadystatechange";
    var $location, defer, channel, port;
    fails2(function() {
      $location = globalThis2.location;
    });
    var run = function(id) {
      if (hasOwn(queue2, id)) {
        var fn = queue2[id];
        delete queue2[id];
        fn();
      }
    };
    var runner = function(id) {
      return function() {
        run(id);
      };
    };
    var eventListener = function(event) {
      run(event.data);
    };
    var globalPostMessageDefer = function(id) {
      globalThis2.postMessage(String2(id), $location.protocol + "//" + $location.host);
    };
    if (!set || !clear) {
      set = function setImmediate(handler) {
        validateArgumentsLength2(arguments.length, 1);
        var fn = isCallable2(handler) ? handler : Function2(handler);
        var args = arraySlice2(arguments, 1);
        queue2[++counter] = function() {
          apply2(fn, void 0, args);
        };
        defer(counter);
        return counter;
      };
      clear = function clearImmediate(id) {
        delete queue2[id];
      };
      if (IS_NODE) {
        defer = function(id) {
          process.nextTick(runner(id));
        };
      } else if (Dispatch && Dispatch.now) {
        defer = function(id) {
          Dispatch.now(runner(id));
        };
      } else if (MessageChannel && !IS_IOS) {
        channel = new MessageChannel();
        port = channel.port2;
        channel.port1.onmessage = eventListener;
        defer = bind(port.postMessage, port);
      } else if (globalThis2.addEventListener && isCallable2(globalThis2.postMessage) && !globalThis2.importScripts && $location && $location.protocol !== "file:" && !fails2(globalPostMessageDefer)) {
        defer = globalPostMessageDefer;
        globalThis2.addEventListener("message", eventListener, false);
      } else if (ONREADYSTATECHANGE in createElement("script")) {
        defer = function(id) {
          html2.appendChild(createElement("script"))[ONREADYSTATECHANGE] = function() {
            html2.removeChild(this);
            run(id);
          };
        };
      } else {
        defer = function(id) {
          setTimeout(runner(id), 0);
        };
      }
    }
    task = {
      set,
      clear
    };
    return task;
  }
  var hasRequiredWeb_clearImmediate;
  function requireWeb_clearImmediate() {
    if (hasRequiredWeb_clearImmediate) return web_clearImmediate;
    hasRequiredWeb_clearImmediate = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var clearImmediate = requireTask().clear;
    $({ global: true, bind: true, enumerable: true, forced: globalThis2.clearImmediate !== clearImmediate }, {
      clearImmediate
    });
    return web_clearImmediate;
  }
  var web_setImmediate = {};
  var schedulersFix;
  var hasRequiredSchedulersFix;
  function requireSchedulersFix() {
    if (hasRequiredSchedulersFix) return schedulersFix;
    hasRequiredSchedulersFix = 1;
    var globalThis2 = requireGlobalThis();
    var apply2 = requireFunctionApply();
    var isCallable2 = requireIsCallable();
    var ENVIRONMENT = requireEnvironment();
    var USER_AGENT = requireEnvironmentUserAgent();
    var arraySlice2 = requireArraySlice();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var Function2 = globalThis2.Function;
    var WRAP = /MSIE .\./.test(USER_AGENT) || ENVIRONMENT === "BUN" && (function() {
      var version = globalThis2.Bun.version.split(".");
      return version.length < 3 || version[0] === "0" && (version[1] < 3 || version[1] === "3" && version[2] === "0");
    })();
    schedulersFix = function(scheduler, hasTimeArg) {
      var firstParamIndex = hasTimeArg ? 2 : 1;
      return WRAP ? function(handler, timeout) {
        var boundArgs = validateArgumentsLength2(arguments.length, 1) > firstParamIndex;
        var fn = isCallable2(handler) ? handler : Function2(handler);
        var params = boundArgs ? arraySlice2(arguments, firstParamIndex) : [];
        var callback = boundArgs ? function() {
          apply2(fn, this, params);
        } : fn;
        return hasTimeArg ? scheduler(callback, timeout) : scheduler(callback);
      } : scheduler;
    };
    return schedulersFix;
  }
  var hasRequiredWeb_setImmediate;
  function requireWeb_setImmediate() {
    if (hasRequiredWeb_setImmediate) return web_setImmediate;
    hasRequiredWeb_setImmediate = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var setTask = requireTask().set;
    var schedulersFix2 = requireSchedulersFix();
    var setImmediate = globalThis2.setImmediate ? schedulersFix2(setTask, false) : setTask;
    $({ global: true, bind: true, enumerable: true, forced: globalThis2.setImmediate !== setImmediate }, {
      setImmediate
    });
    return web_setImmediate;
  }
  var hasRequiredWeb_immediate;
  function requireWeb_immediate() {
    if (hasRequiredWeb_immediate) return web_immediate;
    hasRequiredWeb_immediate = 1;
    requireWeb_clearImmediate();
    requireWeb_setImmediate();
    return web_immediate;
  }
  requireWeb_immediate();
  var web_queueMicrotask = {};
  var safeGetBuiltIn;
  var hasRequiredSafeGetBuiltIn;
  function requireSafeGetBuiltIn() {
    if (hasRequiredSafeGetBuiltIn) return safeGetBuiltIn;
    hasRequiredSafeGetBuiltIn = 1;
    var globalThis2 = requireGlobalThis();
    var DESCRIPTORS = requireDescriptors();
    var getOwnPropertyDescriptor2 = Object.getOwnPropertyDescriptor;
    safeGetBuiltIn = function(name) {
      if (!DESCRIPTORS) return globalThis2[name];
      var descriptor = getOwnPropertyDescriptor2(globalThis2, name);
      return descriptor && descriptor.value;
    };
    return safeGetBuiltIn;
  }
  var queue;
  var hasRequiredQueue;
  function requireQueue() {
    if (hasRequiredQueue) return queue;
    hasRequiredQueue = 1;
    var Queue = function() {
      this.head = null;
      this.tail = null;
    };
    Queue.prototype = {
      add: function(item) {
        var entry = { item, next: null };
        var tail = this.tail;
        if (tail) tail.next = entry;
        else this.head = entry;
        this.tail = entry;
      },
      get: function() {
        var entry = this.head;
        if (entry) {
          var next = this.head = entry.next;
          if (next === null) this.tail = null;
          return entry.item;
        }
      }
    };
    queue = Queue;
    return queue;
  }
  var environmentIsIosPebble;
  var hasRequiredEnvironmentIsIosPebble;
  function requireEnvironmentIsIosPebble() {
    if (hasRequiredEnvironmentIsIosPebble) return environmentIsIosPebble;
    hasRequiredEnvironmentIsIosPebble = 1;
    var userAgent = requireEnvironmentUserAgent();
    environmentIsIosPebble = /ipad|iphone|ipod/i.test(userAgent) && typeof Pebble != "undefined";
    return environmentIsIosPebble;
  }
  var environmentIsWebosWebkit;
  var hasRequiredEnvironmentIsWebosWebkit;
  function requireEnvironmentIsWebosWebkit() {
    if (hasRequiredEnvironmentIsWebosWebkit) return environmentIsWebosWebkit;
    hasRequiredEnvironmentIsWebosWebkit = 1;
    var userAgent = requireEnvironmentUserAgent();
    environmentIsWebosWebkit = /web0s(?!.*chrome)/i.test(userAgent);
    return environmentIsWebosWebkit;
  }
  var microtask_1;
  var hasRequiredMicrotask;
  function requireMicrotask() {
    if (hasRequiredMicrotask) return microtask_1;
    hasRequiredMicrotask = 1;
    var globalThis2 = requireGlobalThis();
    var safeGetBuiltIn2 = requireSafeGetBuiltIn();
    var bind = requireFunctionBindContext();
    var macrotask = requireTask().set;
    var Queue = requireQueue();
    var IS_IOS = requireEnvironmentIsIos();
    var IS_IOS_PEBBLE = requireEnvironmentIsIosPebble();
    var IS_WEBOS_WEBKIT = requireEnvironmentIsWebosWebkit();
    var IS_NODE = requireEnvironmentIsNode();
    var MutationObserver = globalThis2.MutationObserver || globalThis2.WebKitMutationObserver;
    var document2 = globalThis2.document;
    var process = globalThis2.process;
    var Promise2 = globalThis2.Promise;
    var microtask = safeGetBuiltIn2("queueMicrotask");
    var notify, toggle, node, promise, then;
    if (!microtask) {
      var queue2 = new Queue();
      var flush = function() {
        var parent, fn;
        if (IS_NODE && (parent = process.domain)) parent.exit();
        while (fn = queue2.get()) try {
          fn();
        } catch (error) {
          if (queue2.head) notify();
          throw error;
        }
        if (parent) parent.enter();
      };
      if (!IS_IOS && !IS_NODE && !IS_WEBOS_WEBKIT && MutationObserver && document2) {
        toggle = true;
        node = document2.createTextNode("");
        new MutationObserver(flush).observe(node, { characterData: true });
        notify = function() {
          node.data = toggle = !toggle;
        };
      } else if (!IS_IOS_PEBBLE && Promise2 && Promise2.resolve) {
        promise = Promise2.resolve(void 0);
        promise.constructor = Promise2;
        then = bind(promise.then, promise);
        notify = function() {
          then(flush);
        };
      } else if (IS_NODE) {
        notify = function() {
          process.nextTick(flush);
        };
      } else {
        macrotask = bind(macrotask, globalThis2);
        notify = function() {
          macrotask(flush);
        };
      }
      microtask = function(fn) {
        if (!queue2.head) notify();
        queue2.add(fn);
      };
    }
    microtask_1 = microtask;
    return microtask_1;
  }
  var hasRequiredWeb_queueMicrotask;
  function requireWeb_queueMicrotask() {
    if (hasRequiredWeb_queueMicrotask) return web_queueMicrotask;
    hasRequiredWeb_queueMicrotask = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var microtask = requireMicrotask();
    var aCallable2 = requireACallable();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var fails2 = requireFails();
    var DESCRIPTORS = requireDescriptors();
    var WRONG_ARITY = fails2(function() {
      return DESCRIPTORS && Object.getOwnPropertyDescriptor(globalThis2, "queueMicrotask").value.length !== 1;
    });
    $({ global: true, enumerable: true, dontCallGetSet: true, forced: WRONG_ARITY }, {
      queueMicrotask: function queueMicrotask(fn) {
        validateArgumentsLength2(arguments.length, 1);
        microtask(aCallable2(fn));
      }
    });
    return web_queueMicrotask;
  }
  requireWeb_queueMicrotask();
  var web_self = {};
  var hasRequiredWeb_self;
  function requireWeb_self() {
    if (hasRequiredWeb_self) return web_self;
    hasRequiredWeb_self = 1;
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var DESCRIPTORS = requireDescriptors();
    var $TypeError = TypeError;
    var defineProperty = Object.defineProperty;
    var INCORRECT_VALUE = globalThis2.self !== globalThis2;
    try {
      if (DESCRIPTORS) {
        var descriptor = Object.getOwnPropertyDescriptor(globalThis2, "self");
        if (INCORRECT_VALUE || !descriptor || !descriptor.get || !descriptor.enumerable) {
          defineBuiltInAccessor2(globalThis2, "self", {
            get: function self2() {
              return globalThis2;
            },
            set: function self2(value) {
              if (this !== globalThis2) throw new $TypeError("Illegal invocation");
              defineProperty(globalThis2, "self", {
                value,
                writable: true,
                configurable: true,
                enumerable: true
              });
            },
            configurable: true,
            enumerable: true
          });
        }
      } else $({ global: true, simple: true, forced: INCORRECT_VALUE }, {
        self: globalThis2
      });
    } catch (error) {
    }
    return web_self;
  }
  requireWeb_self();
  var web_structuredClone = {};
  var hasRequiredWeb_structuredClone;
  function requireWeb_structuredClone() {
    if (hasRequiredWeb_structuredClone) return web_structuredClone;
    hasRequiredWeb_structuredClone = 1;
    var IS_PURE = requireIsPure();
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var fails2 = requireFails();
    var uid2 = requireUid();
    var isCallable2 = requireIsCallable();
    var isConstructor2 = requireIsConstructor();
    var isNullOrUndefined2 = requireIsNullOrUndefined();
    var isObject2 = requireIsObject();
    var isSymbol2 = requireIsSymbol();
    var iterate2 = requireIterate();
    var anObject2 = requireAnObject();
    var classof2 = requireClassof();
    var hasOwn = requireHasOwnProperty();
    var createProperty2 = requireCreateProperty();
    var createNonEnumerableProperty2 = requireCreateNonEnumerableProperty();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var getRegExpFlags = requireRegexpGetFlags();
    var MapHelpers = requireMapHelpers();
    var SetHelpers = requireSetHelpers();
    var setIterate2 = requireSetIterate();
    var detachTransferable2 = requireDetachTransferable();
    var ERROR_STACK_INSTALLABLE = requireErrorStackInstallable();
    var PROPER_STRUCTURED_CLONE_TRANSFER = requireStructuredCloneProperTransfer();
    var Object2 = globalThis2.Object;
    var Array2 = globalThis2.Array;
    var Date = globalThis2.Date;
    var Error2 = globalThis2.Error;
    var TypeError2 = globalThis2.TypeError;
    var PerformanceMark = globalThis2.PerformanceMark;
    var DOMException2 = getBuiltIn2("DOMException");
    var Map2 = MapHelpers.Map;
    var mapHas = MapHelpers.has;
    var mapGet = MapHelpers.get;
    var mapSet = MapHelpers.set;
    var Set2 = SetHelpers.Set;
    var setAdd = SetHelpers.add;
    var setHas = SetHelpers.has;
    var objectKeys2 = getBuiltIn2("Object", "keys");
    var push = uncurryThis([].push);
    var thisBooleanValue = uncurryThis(true.valueOf);
    var thisNumberValue2 = uncurryThis(1.1.valueOf);
    var thisStringValue = uncurryThis("".valueOf);
    var thisTimeValue = uncurryThis(Date.prototype.getTime);
    var PERFORMANCE_MARK = uid2("structuredClone");
    var DATA_CLONE_ERROR = "DataCloneError";
    var TRANSFERRING = "Transferring";
    var checkBasicSemantic = function(structuredCloneImplementation) {
      return !fails2(function() {
        var set1 = new globalThis2.Set([7]);
        var set2 = structuredCloneImplementation(set1);
        var number = structuredCloneImplementation(Object2(7));
        return set2 === set1 || !set2.has(7) || !isObject2(number) || +number !== 7;
      }) && structuredCloneImplementation;
    };
    var checkErrorsCloning = function(structuredCloneImplementation, $Error) {
      return !fails2(function() {
        var error = new $Error();
        var test = structuredCloneImplementation({ a: error, b: error });
        return !(test && test.a === test.b && test.a instanceof $Error && test.a.stack === error.stack);
      });
    };
    var checkNewErrorsCloningSemantic = function(structuredCloneImplementation) {
      return !fails2(function() {
        var test = structuredCloneImplementation(new globalThis2.AggregateError([1], PERFORMANCE_MARK, { cause: 3 }));
        return test.name !== "AggregateError" || test.errors[0] !== 1 || test.message !== PERFORMANCE_MARK || test.cause !== 3;
      });
    };
    var nativeStructuredClone = globalThis2.structuredClone;
    var FORCED_REPLACEMENT = IS_PURE || !checkErrorsCloning(nativeStructuredClone, Error2) || !checkErrorsCloning(nativeStructuredClone, DOMException2) || !checkNewErrorsCloningSemantic(nativeStructuredClone);
    var structuredCloneFromMark = !nativeStructuredClone && checkBasicSemantic(function(value) {
      return new PerformanceMark(PERFORMANCE_MARK, { detail: value }).detail;
    });
    var nativeRestrictedStructuredClone = checkBasicSemantic(nativeStructuredClone) || structuredCloneFromMark;
    var throwUncloneable = function(type) {
      throw new DOMException2("Uncloneable type: " + type, DATA_CLONE_ERROR);
    };
    var throwUnpolyfillable = function(type, action) {
      throw new DOMException2((action || "Cloning") + " of " + type + " cannot be properly polyfilled in this engine", DATA_CLONE_ERROR);
    };
    var tryNativeRestrictedStructuredClone = function(value, type) {
      if (!nativeRestrictedStructuredClone) throwUnpolyfillable(type);
      return nativeRestrictedStructuredClone(value);
    };
    var createDataTransfer = function() {
      var dataTransfer;
      try {
        dataTransfer = new globalThis2.DataTransfer();
      } catch (error) {
        try {
          dataTransfer = new globalThis2.ClipboardEvent("").clipboardData;
        } catch (error2) {
        }
      }
      return dataTransfer && dataTransfer.items && dataTransfer.files ? dataTransfer : null;
    };
    var cloneBuffer = function(value, map, $type) {
      if (mapHas(map, value)) return mapGet(map, value);
      var type = $type || classof2(value);
      var clone2, length, options, source, target, i;
      if (type === "SharedArrayBuffer") {
        if (nativeRestrictedStructuredClone) clone2 = nativeRestrictedStructuredClone(value);
        else clone2 = value;
      } else {
        var DataView2 = globalThis2.DataView;
        if (!DataView2 && !isCallable2(value.slice)) throwUnpolyfillable("ArrayBuffer");
        try {
          if (isCallable2(value.slice) && !value.resizable) {
            clone2 = value.slice(0);
          } else {
            length = value.byteLength;
            options = "maxByteLength" in value ? { maxByteLength: value.maxByteLength } : void 0;
            clone2 = new ArrayBuffer(length, options);
            source = new DataView2(value);
            target = new DataView2(clone2);
            for (i = 0; i < length; i++) {
              target.setUint8(i, source.getUint8(i));
            }
          }
        } catch (error) {
          throw new DOMException2("ArrayBuffer is detached", DATA_CLONE_ERROR);
        }
      }
      mapSet(map, value, clone2);
      return clone2;
    };
    var cloneView = function(value, type, offset, length, map) {
      var C = globalThis2[type];
      if (!isObject2(C)) throwUnpolyfillable(type);
      return new C(cloneBuffer(value.buffer, map), offset, length);
    };
    var structuredCloneInternal = function(value, map) {
      if (isSymbol2(value)) throwUncloneable("Symbol");
      if (!isObject2(value)) return value;
      if (map) {
        if (mapHas(map, value)) return mapGet(map, value);
      } else map = new Map2();
      var type = classof2(value);
      var C, name, cloned, dataTransfer, i, length, keys, key;
      switch (type) {
        case "Array":
          cloned = Array2(lengthOfArrayLike2(value));
          break;
        case "Object":
          cloned = {};
          break;
        case "Map":
          cloned = new Map2();
          break;
        case "Set":
          cloned = new Set2();
          break;
        case "RegExp":
          cloned = new RegExp(value.source, getRegExpFlags(value));
          break;
        case "Error":
          name = value.name;
          switch (name) {
            case "AggregateError":
              cloned = new (getBuiltIn2(name))([]);
              break;
            case "EvalError":
            case "RangeError":
            case "ReferenceError":
            case "SuppressedError":
            case "SyntaxError":
            case "TypeError":
            case "URIError":
              cloned = new (getBuiltIn2(name))();
              break;
            case "CompileError":
            case "LinkError":
            case "RuntimeError":
              cloned = new (getBuiltIn2("WebAssembly", name))();
              break;
            default:
              cloned = new Error2();
          }
          break;
        case "DOMException":
          cloned = new DOMException2(value.message, value.name);
          break;
        case "ArrayBuffer":
        case "SharedArrayBuffer":
          cloned = cloneBuffer(value, map, type);
          break;
        case "DataView":
        case "Int8Array":
        case "Uint8Array":
        case "Uint8ClampedArray":
        case "Int16Array":
        case "Uint16Array":
        case "Int32Array":
        case "Uint32Array":
        case "Float16Array":
        case "Float32Array":
        case "Float64Array":
        case "BigInt64Array":
        case "BigUint64Array":
          length = type === "DataView" ? value.byteLength : value.length;
          cloned = cloneView(value, type, value.byteOffset, length, map);
          break;
        case "DOMQuad":
          try {
            cloned = new DOMQuad(
              structuredCloneInternal(value.p1, map),
              structuredCloneInternal(value.p2, map),
              structuredCloneInternal(value.p3, map),
              structuredCloneInternal(value.p4, map)
            );
          } catch (error) {
            cloned = tryNativeRestrictedStructuredClone(value, type);
          }
          break;
        case "File":
          if (nativeRestrictedStructuredClone) try {
            cloned = nativeRestrictedStructuredClone(value);
            if (classof2(cloned) !== type) cloned = void 0;
          } catch (error) {
          }
          if (!cloned) try {
            cloned = new File([value], value.name, value);
          } catch (error) {
          }
          if (!cloned) throwUnpolyfillable(type);
          break;
        case "FileList":
          dataTransfer = createDataTransfer();
          if (dataTransfer) {
            for (i = 0, length = lengthOfArrayLike2(value); i < length; i++) {
              dataTransfer.items.add(structuredCloneInternal(value[i], map));
            }
            cloned = dataTransfer.files;
          } else cloned = tryNativeRestrictedStructuredClone(value, type);
          break;
        case "ImageData":
          try {
            cloned = new ImageData(
              structuredCloneInternal(value.data, map),
              value.width,
              value.height,
              { colorSpace: value.colorSpace }
            );
          } catch (error) {
            cloned = tryNativeRestrictedStructuredClone(value, type);
          }
          break;
        default:
          if (nativeRestrictedStructuredClone) {
            cloned = nativeRestrictedStructuredClone(value);
          } else switch (type) {
            case "BigInt":
              cloned = Object2(value.valueOf());
              break;
            case "Boolean":
              cloned = Object2(thisBooleanValue(value));
              break;
            case "Number":
              cloned = Object2(thisNumberValue2(value));
              break;
            case "String":
              cloned = Object2(thisStringValue(value));
              break;
            case "Date":
              cloned = new Date(thisTimeValue(value));
              break;
            case "Blob":
              try {
                cloned = value.slice(0, value.size, value.type);
              } catch (error) {
                throwUnpolyfillable(type);
              }
              break;
            case "DOMPoint":
            case "DOMPointReadOnly":
              C = globalThis2[type];
              try {
                cloned = C.fromPoint ? C.fromPoint(value) : new C(value.x, value.y, value.z, value.w);
              } catch (error) {
                throwUnpolyfillable(type);
              }
              break;
            case "DOMRect":
            case "DOMRectReadOnly":
              C = globalThis2[type];
              try {
                cloned = C.fromRect ? C.fromRect(value) : new C(value.x, value.y, value.width, value.height);
              } catch (error) {
                throwUnpolyfillable(type);
              }
              break;
            case "DOMMatrix":
            case "DOMMatrixReadOnly":
              C = globalThis2[type];
              try {
                cloned = C.fromMatrix ? C.fromMatrix(value) : new C(value);
              } catch (error) {
                throwUnpolyfillable(type);
              }
              break;
            case "AudioData":
            case "VideoFrame":
              if (!isCallable2(value.clone)) throwUnpolyfillable(type);
              try {
                cloned = value.clone();
              } catch (error) {
                throwUncloneable(type);
              }
              break;
            case "CropTarget":
            case "CryptoKey":
            case "FileSystemDirectoryHandle":
            case "FileSystemFileHandle":
            case "FileSystemHandle":
            case "GPUCompilationInfo":
            case "GPUCompilationMessage":
            case "ImageBitmap":
            case "RTCCertificate":
            case "WebAssembly.Module":
              throwUnpolyfillable(type);
            // break omitted
            default:
              throwUncloneable(type);
          }
      }
      mapSet(map, value, cloned);
      switch (type) {
        case "Array":
        case "Object":
          keys = objectKeys2(value);
          for (i = 0, length = lengthOfArrayLike2(keys); i < length; i++) {
            key = keys[i];
            createProperty2(cloned, key, structuredCloneInternal(value[key], map));
          }
          break;
        case "Map":
          value.forEach(function(v, k) {
            mapSet(cloned, structuredCloneInternal(k, map), structuredCloneInternal(v, map));
          });
          break;
        case "Set":
          value.forEach(function(v) {
            setAdd(cloned, structuredCloneInternal(v, map));
          });
          break;
        case "Error":
          createNonEnumerableProperty2(cloned, "message", structuredCloneInternal(value.message, map));
          if (hasOwn(value, "cause")) {
            createNonEnumerableProperty2(cloned, "cause", structuredCloneInternal(value.cause, map));
          }
          if (name === "AggregateError") {
            cloned.errors = structuredCloneInternal(value.errors, map);
          } else if (name === "SuppressedError") {
            cloned.error = structuredCloneInternal(value.error, map);
            cloned.suppressed = structuredCloneInternal(value.suppressed, map);
          }
        // break omitted
        case "DOMException":
          if (ERROR_STACK_INSTALLABLE) {
            createNonEnumerableProperty2(cloned, "stack", structuredCloneInternal(value.stack, map));
          }
      }
      return cloned;
    };
    var tryToTransfer = function(rawTransfer, map) {
      if (!isObject2(rawTransfer)) throw new TypeError2("Transfer option cannot be converted to a sequence");
      var transfer = [];
      iterate2(rawTransfer, function(value2) {
        push(transfer, anObject2(value2));
      });
      var i = 0;
      var length = lengthOfArrayLike2(transfer);
      var buffers = new Set2();
      var value, type, C, transferred, canvas, context;
      while (i < length) {
        value = transfer[i++];
        type = classof2(value);
        transferred = void 0;
        if (type === "ArrayBuffer" ? setHas(buffers, value) : mapHas(map, value)) {
          throw new DOMException2("Duplicate transferable", DATA_CLONE_ERROR);
        }
        if (type === "ArrayBuffer") {
          setAdd(buffers, value);
          continue;
        }
        if (PROPER_STRUCTURED_CLONE_TRANSFER) {
          transferred = nativeStructuredClone(value, { transfer: [value] });
        } else switch (type) {
          case "ImageBitmap":
            C = globalThis2.OffscreenCanvas;
            if (!isConstructor2(C)) throwUnpolyfillable(type, TRANSFERRING);
            try {
              canvas = new C(value.width, value.height);
              context = canvas.getContext("bitmaprenderer");
              context.transferFromImageBitmap(value);
              transferred = canvas.transferToImageBitmap();
            } catch (error) {
            }
            break;
          case "AudioData":
          case "VideoFrame":
            if (!isCallable2(value.clone) || !isCallable2(value.close)) throwUnpolyfillable(type, TRANSFERRING);
            try {
              transferred = value.clone();
              value.close();
            } catch (error) {
            }
            break;
          case "MediaSourceHandle":
          case "MessagePort":
          case "MIDIAccess":
          case "OffscreenCanvas":
          case "ReadableStream":
          case "RTCDataChannel":
          case "TransformStream":
          case "WebTransportReceiveStream":
          case "WebTransportSendStream":
          case "WritableStream":
            throwUnpolyfillable(type, TRANSFERRING);
        }
        if (transferred === void 0) throw new DOMException2("This object cannot be transferred: " + type, DATA_CLONE_ERROR);
        mapSet(map, value, transferred);
      }
      return buffers;
    };
    var detachBuffers = function(buffers) {
      setIterate2(buffers, function(buffer) {
        if (PROPER_STRUCTURED_CLONE_TRANSFER) {
          nativeStructuredClone(buffer, { transfer: [buffer] });
        } else if (isCallable2(buffer.transfer)) {
          buffer.transfer();
        } else if (detachTransferable2) {
          detachTransferable2(buffer);
        } else {
          throwUnpolyfillable("ArrayBuffer", TRANSFERRING);
        }
      });
    };
    $({ global: true, enumerable: true, sham: !PROPER_STRUCTURED_CLONE_TRANSFER, forced: FORCED_REPLACEMENT }, {
      structuredClone: function structuredClone(value) {
        var options = validateArgumentsLength2(arguments.length, 1) > 1 && !isNullOrUndefined2(arguments[1]) ? anObject2(arguments[1]) : void 0;
        var transfer = options ? options.transfer : void 0;
        var map, buffers;
        if (transfer !== void 0) {
          map = new Map2();
          buffers = tryToTransfer(transfer, map);
        }
        var clone2 = structuredCloneInternal(value, map);
        if (buffers) detachBuffers(buffers);
        return clone2;
      }
    });
    return web_structuredClone;
  }
  requireWeb_structuredClone();
  var web_url = {};
  var web_url_constructor = {};
  var es_string_iterator = {};
  var hasRequiredEs_string_iterator;
  function requireEs_string_iterator() {
    if (hasRequiredEs_string_iterator) return es_string_iterator;
    hasRequiredEs_string_iterator = 1;
    var charAt = requireStringMultibyte().charAt;
    var toString2 = requireToString();
    var InternalStateModule = requireInternalState();
    var defineIterator = requireIteratorDefine();
    var createIterResultObject2 = requireCreateIterResultObject();
    var STRING_ITERATOR = "String Iterator";
    var setInternalState = InternalStateModule.set;
    var getInternalState = InternalStateModule.getterFor(STRING_ITERATOR);
    defineIterator(String, "String", function(iterated) {
      setInternalState(this, {
        type: STRING_ITERATOR,
        string: toString2(iterated),
        index: 0
      });
    }, function next() {
      var state = getInternalState(this);
      var string = state.string;
      var index = state.index;
      var point;
      if (index >= string.length) return createIterResultObject2(void 0, true);
      point = charAt(string, index);
      state.index += point.length;
      return createIterResultObject2(point, false);
    });
    return es_string_iterator;
  }
  var urlConstructorDetection;
  var hasRequiredUrlConstructorDetection;
  function requireUrlConstructorDetection() {
    if (hasRequiredUrlConstructorDetection) return urlConstructorDetection;
    hasRequiredUrlConstructorDetection = 1;
    var fails2 = requireFails();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var DESCRIPTORS = requireDescriptors();
    var IS_PURE = requireIsPure();
    var ITERATOR = wellKnownSymbol2("iterator");
    urlConstructorDetection = !fails2(function() {
      var url = new URL("b?a=1&b=2&c=3", "https://a");
      var params = url.searchParams;
      var params2 = new URLSearchParams("a=1&a=2&b=3");
      var result = "";
      url.pathname = "c%20d";
      params.forEach(function(value, key) {
        params["delete"]("b");
        result += key + value;
      });
      params2["delete"]("a", 2);
      params2["delete"]("b", void 0);
      return IS_PURE && (!url.toJSON || !params2.has("a", 1) || params2.has("a", 2) || !params2.has("a", void 0) || params2.has("b")) || !params.size && (IS_PURE || !DESCRIPTORS) || !params.sort || url.href !== "https://a/c%20d?a=1&c=3" || params.get("c") !== "3" || String(new URLSearchParams("?a=1")) !== "a=1" || !params[ITERATOR] || new URL("https://a@b").username !== "a" || new URLSearchParams(new URLSearchParams("a=b")).get("a") !== "b" || new URL("https://\u0442\u0435\u0441\u0442").host !== "xn--e1aybc" || new URL("https://a#\u0431").hash !== "#%D0%B1" || result !== "a1c3" || new URL("https://x", void 0).host !== "x";
    });
    return urlConstructorDetection;
  }
  var objectAssign;
  var hasRequiredObjectAssign;
  function requireObjectAssign() {
    if (hasRequiredObjectAssign) return objectAssign;
    hasRequiredObjectAssign = 1;
    var DESCRIPTORS = requireDescriptors();
    var uncurryThis = requireFunctionUncurryThis();
    var call = requireFunctionCall();
    var fails2 = requireFails();
    var objectKeys2 = requireObjectKeys();
    var getOwnPropertySymbolsModule = requireObjectGetOwnPropertySymbols();
    var propertyIsEnumerableModule = requireObjectPropertyIsEnumerable();
    var toObject2 = requireToObject();
    var IndexedObject = requireIndexedObject();
    var $assign = Object.assign;
    var defineProperty = Object.defineProperty;
    var concat = uncurryThis([].concat);
    objectAssign = !$assign || fails2(function() {
      if (DESCRIPTORS && $assign({ b: 1 }, $assign(defineProperty({}, "a", {
        enumerable: true,
        get: function() {
          defineProperty(this, "b", {
            value: 3,
            enumerable: false
          });
        }
      }), { b: 2 })).b !== 1) return true;
      var A = {};
      var B = {};
      var symbol = Symbol("assign detection");
      var alphabet = "abcdefghijklmnopqrst";
      A[symbol] = 7;
      alphabet.split("").forEach(function(chr) {
        B[chr] = chr;
      });
      return $assign({}, A)[symbol] !== 7 || objectKeys2($assign({}, B)).join("") !== alphabet;
    }) ? function assign(target, source) {
      var T = toObject2(target);
      var argumentsLength = arguments.length;
      var index = 1;
      var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
      var propertyIsEnumerable = propertyIsEnumerableModule.f;
      while (argumentsLength > index) {
        var S = IndexedObject(arguments[index++]);
        var keys = getOwnPropertySymbols ? concat(objectKeys2(S), getOwnPropertySymbols(S)) : objectKeys2(S);
        var length = keys.length;
        var j = 0;
        var key;
        while (length > j) {
          key = keys[j++];
          if (!DESCRIPTORS || call(propertyIsEnumerable, S, key)) T[key] = S[key];
        }
      }
      return T;
    } : $assign;
    return objectAssign;
  }
  var arrayFrom;
  var hasRequiredArrayFrom;
  function requireArrayFrom() {
    if (hasRequiredArrayFrom) return arrayFrom;
    hasRequiredArrayFrom = 1;
    var bind = requireFunctionBindContext();
    var call = requireFunctionCall();
    var toObject2 = requireToObject();
    var callWithSafeIterationClosing2 = requireCallWithSafeIterationClosing();
    var isArrayIteratorMethod2 = requireIsArrayIteratorMethod();
    var isConstructor2 = requireIsConstructor();
    var lengthOfArrayLike2 = requireLengthOfArrayLike();
    var createProperty2 = requireCreateProperty();
    var setArrayLength = requireArraySetLength();
    var getIterator = requireGetIteratorInternal();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var iteratorClose2 = requireIteratorClose();
    var doesNotExceedSafeInteger2 = requireDoesNotExceedSafeInteger();
    var $Array = Array;
    arrayFrom = function from(arrayLike) {
      var IS_CONSTRUCTOR = isConstructor2(this);
      var argumentsLength = arguments.length;
      var mapfn = argumentsLength > 1 ? arguments[1] : void 0;
      var mapping = mapfn !== void 0;
      if (mapping) mapfn = bind(mapfn, argumentsLength > 2 ? arguments[2] : void 0);
      var O = toObject2(arrayLike);
      var iteratorMethod = getIteratorMethod(O);
      var index = 0;
      var length, result, step, iterator, next, value;
      if (iteratorMethod && !(this === $Array && isArrayIteratorMethod2(iteratorMethod))) {
        result = IS_CONSTRUCTOR ? new this() : [];
        iterator = getIterator(O, iteratorMethod);
        next = iterator.next;
        for (; !(step = call(next, iterator)).done; index++) {
          try {
            doesNotExceedSafeInteger2(index);
          } catch (error) {
            iteratorClose2(iterator, "throw", error);
          }
          value = mapping ? callWithSafeIterationClosing2(iterator, mapfn, [step.value, index], true) : step.value;
          try {
            createProperty2(result, index, value);
          } catch (error) {
            iteratorClose2(iterator, "throw", error);
          }
        }
      } else {
        length = lengthOfArrayLike2(O);
        result = IS_CONSTRUCTOR ? new this(length) : $Array(length);
        for (; length > index; index++) {
          value = mapping ? mapfn(O[index], index) : O[index];
          createProperty2(result, index, value);
        }
      }
      setArrayLength(result, index);
      return result;
    };
    return arrayFrom;
  }
  var stringPunycodeToAscii;
  var hasRequiredStringPunycodeToAscii;
  function requireStringPunycodeToAscii() {
    if (hasRequiredStringPunycodeToAscii) return stringPunycodeToAscii;
    hasRequiredStringPunycodeToAscii = 1;
    var uncurryThis = requireFunctionUncurryThis();
    var maxInt = 2147483647;
    var base = 36;
    var tMin = 1;
    var tMax = 26;
    var skew = 38;
    var damp = 700;
    var initialBias = 72;
    var initialN = 128;
    var delimiter = "-";
    var regexNonASCII = /[^\0-\u007E]/;
    var regexSeparators = /[.\u3002\uFF0E\uFF61]/g;
    var OVERFLOW_ERROR = "Overflow: input needs wider integers to process";
    var baseMinusTMin = base - tMin;
    var $RangeError = RangeError;
    var exec = uncurryThis(regexSeparators.exec);
    var floor = Math.floor;
    var fromCharCode = String.fromCharCode;
    var charCodeAt = uncurryThis("".charCodeAt);
    var join = uncurryThis([].join);
    var push = uncurryThis([].push);
    var replace = uncurryThis("".replace);
    var split = uncurryThis("".split);
    var toLowerCase = uncurryThis("".toLowerCase);
    var ucs2decode = function(string) {
      var output = [];
      var counter = 0;
      var length = string.length;
      while (counter < length) {
        var value = charCodeAt(string, counter++);
        if (value >= 55296 && value <= 56319 && counter < length) {
          var extra = charCodeAt(string, counter++);
          if ((extra & 64512) === 56320) {
            push(output, ((value & 1023) << 10) + (extra & 1023) + 65536);
          } else {
            push(output, value);
            counter--;
          }
        } else {
          push(output, value);
        }
      }
      return output;
    };
    var digitToBasic = function(digit) {
      return digit + 22 + 75 * (digit < 26);
    };
    var adapt = function(delta, numPoints, firstTime) {
      var k = 0;
      delta = firstTime ? floor(delta / damp) : delta >> 1;
      delta += floor(delta / numPoints);
      while (delta > baseMinusTMin * tMax >> 1) {
        delta = floor(delta / baseMinusTMin);
        k += base;
      }
      return floor(k + (baseMinusTMin + 1) * delta / (delta + skew));
    };
    var encode = function(input) {
      var output = [];
      input = ucs2decode(input);
      var inputLength = input.length;
      var n = initialN;
      var delta = 0;
      var bias = initialBias;
      var i, currentValue;
      for (i = 0; i < input.length; i++) {
        currentValue = input[i];
        if (currentValue < 128) {
          push(output, fromCharCode(currentValue));
        }
      }
      var basicLength = output.length;
      var handledCPCount = basicLength;
      if (basicLength) {
        push(output, delimiter);
      }
      while (handledCPCount < inputLength) {
        var m = maxInt;
        for (i = 0; i < input.length; i++) {
          currentValue = input[i];
          if (currentValue >= n && currentValue < m) {
            m = currentValue;
          }
        }
        var handledCPCountPlusOne = handledCPCount + 1;
        if (m - n > floor((maxInt - delta) / handledCPCountPlusOne)) {
          throw new $RangeError(OVERFLOW_ERROR);
        }
        delta += (m - n) * handledCPCountPlusOne;
        n = m;
        for (i = 0; i < input.length; i++) {
          currentValue = input[i];
          if (currentValue < n && ++delta > maxInt) {
            throw new $RangeError(OVERFLOW_ERROR);
          }
          if (currentValue === n) {
            var q = delta;
            var k = base;
            while (true) {
              var t = k <= bias ? tMin : k >= bias + tMax ? tMax : k - bias;
              if (q < t) break;
              var qMinusT = q - t;
              var baseMinusT = base - t;
              push(output, fromCharCode(digitToBasic(t + qMinusT % baseMinusT)));
              q = floor(qMinusT / baseMinusT);
              k += base;
            }
            push(output, fromCharCode(digitToBasic(q)));
            bias = adapt(delta, handledCPCountPlusOne, handledCPCount === basicLength);
            delta = 0;
            handledCPCount++;
          }
        }
        delta++;
        n++;
      }
      return join(output, "");
    };
    stringPunycodeToAscii = function(input) {
      var encoded = [];
      var labels = split(replace(toLowerCase(input), regexSeparators, "."), ".");
      var i, label;
      for (i = 0; i < labels.length; i++) {
        label = labels[i];
        push(encoded, exec(regexNonASCII, label) ? "xn--" + encode(label) : label);
      }
      return join(encoded, ".");
    };
    return stringPunycodeToAscii;
  }
  var es_string_fromCodePoint = {};
  var hasRequiredEs_string_fromCodePoint;
  function requireEs_string_fromCodePoint() {
    if (hasRequiredEs_string_fromCodePoint) return es_string_fromCodePoint;
    hasRequiredEs_string_fromCodePoint = 1;
    var $ = require_export();
    var uncurryThis = requireFunctionUncurryThis();
    var toAbsoluteIndex2 = requireToAbsoluteIndex();
    var $RangeError = RangeError;
    var fromCharCode = String.fromCharCode;
    var $fromCodePoint = String.fromCodePoint;
    var join = uncurryThis([].join);
    var INCORRECT_LENGTH = !!$fromCodePoint && $fromCodePoint.length !== 1;
    $({ target: "String", stat: true, arity: 1, forced: INCORRECT_LENGTH }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      fromCodePoint: function fromCodePoint(x) {
        var elements = [];
        var length = arguments.length;
        var i = 0;
        var code;
        while (length > i) {
          code = +arguments[i];
          if (toAbsoluteIndex2(code, 1114111) !== code) throw new $RangeError(code + " is not a valid code point");
          elements[i++] = code < 65536 ? fromCharCode(code) : fromCharCode(((code -= 65536) >> 10) + 55296, code % 1024 + 56320);
        }
        return join(elements, "");
      }
    });
    return es_string_fromCodePoint;
  }
  var urlPercentCoding;
  var hasRequiredUrlPercentCoding;
  function requireUrlPercentCoding() {
    if (hasRequiredUrlPercentCoding) return urlPercentCoding;
    hasRequiredUrlPercentCoding = 1;
    requireEs_string_fromCodePoint();
    var getBuiltIn2 = requireGetBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var fromCharCode = String.fromCharCode;
    var fromCodePoint = getBuiltIn2("String", "fromCodePoint");
    var $encodeURIComponent = encodeURIComponent;
    var $parseInt = parseInt;
    var charAt = uncurryThis("".charAt);
    var push = uncurryThis([].push);
    var replace = uncurryThis("".replace);
    var stringSlice = uncurryThis("".slice);
    var exec = uncurryThis(/./.exec);
    var FALLBACK_REPLACER = "\uFFFD";
    var VALID_HEX = /^[0-9a-f]+$/i;
    var SURROGATE = /[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDFFF]/g;
    var parseHexOctet = function(string, start) {
      var substr = stringSlice(string, start, start + 2);
      if (!exec(VALID_HEX, substr)) return NaN;
      return $parseInt(substr, 16);
    };
    var getLeadingOnes = function(octet) {
      var count = 0;
      for (var mask = 128; mask > 0 && (octet & mask) !== 0; mask >>= 1) {
        count++;
      }
      return count;
    };
    var utf8Decode = function(octets) {
      var codePoint = null;
      var length = octets.length;
      switch (length) {
        case 1:
          codePoint = octets[0];
          break;
        case 2:
          codePoint = (octets[0] & 31) << 6 | octets[1] & 63;
          break;
        case 3:
          codePoint = (octets[0] & 15) << 12 | (octets[1] & 63) << 6 | octets[2] & 63;
          break;
        case 4:
          codePoint = (octets[0] & 7) << 18 | (octets[1] & 63) << 12 | (octets[2] & 63) << 6 | octets[3] & 63;
          break;
      }
      if (codePoint === null || codePoint > 1114111 || codePoint >= 55296 && codePoint <= 57343 || codePoint < (length > 3 ? 65536 : length > 2 ? 2048 : length > 1 ? 128 : 0)) return null;
      return codePoint;
    };
    var replaceLoneSurrogate = function(chunk) {
      return chunk.length === 2 ? chunk : FALLBACK_REPLACER;
    };
    var decode = function(input) {
      var length = input.length;
      var result = "";
      var i = 0;
      while (i < length) {
        var decodedChar = charAt(input, i);
        if (decodedChar === "%") {
          if (charAt(input, i + 1) === "%" || i + 3 > length) {
            result += "%";
            i++;
            continue;
          }
          var octet = parseHexOctet(input, i + 1);
          if (octet !== octet) {
            result += decodedChar;
            i++;
            continue;
          }
          i += 2;
          var byteSequenceLength = getLeadingOnes(octet);
          if (byteSequenceLength === 0) {
            decodedChar = fromCharCode(octet);
          } else {
            if (byteSequenceLength === 1 || byteSequenceLength > 4) {
              result += FALLBACK_REPLACER;
              i++;
              continue;
            }
            var octets = [octet];
            var sequenceIndex = 1;
            while (sequenceIndex < byteSequenceLength) {
              i++;
              if (i + 3 > length || charAt(input, i) !== "%") break;
              var nextByte = parseHexOctet(input, i + 1);
              if (nextByte !== nextByte || nextByte > 191 || nextByte < 128) break;
              if (sequenceIndex === 1) {
                if (octet === 224 && nextByte < 160) break;
                if (octet === 237 && nextByte > 159) break;
                if (octet === 240 && nextByte < 144) break;
                if (octet === 244 && nextByte > 143) break;
              }
              push(octets, nextByte);
              i += 2;
              sequenceIndex++;
            }
            if (octets.length !== byteSequenceLength) {
              result += FALLBACK_REPLACER;
              continue;
            }
            var codePoint = utf8Decode(octets);
            if (codePoint === null) {
              for (var replacement = 0; replacement < byteSequenceLength; replacement++) result += FALLBACK_REPLACER;
              i++;
              continue;
            } else {
              decodedChar = fromCodePoint(codePoint);
            }
          }
        }
        result += decodedChar;
        i++;
      }
      return result;
    };
    var encode = function(input) {
      try {
        return $encodeURIComponent(input);
      } catch (error) {
        return $encodeURIComponent(replace(input, SURROGATE, replaceLoneSurrogate));
      }
    };
    urlPercentCoding = {
      decode,
      encode
    };
    return urlPercentCoding;
  }
  var web_urlSearchParams_constructor;
  var hasRequiredWeb_urlSearchParams_constructor;
  function requireWeb_urlSearchParams_constructor() {
    if (hasRequiredWeb_urlSearchParams_constructor) return web_urlSearchParams_constructor;
    hasRequiredWeb_urlSearchParams_constructor = 1;
    requireEs_array_iterator();
    var $ = require_export();
    var globalThis2 = requireGlobalThis();
    var safeGetBuiltIn2 = requireSafeGetBuiltIn();
    var call = requireFunctionCall();
    var uncurryThis = requireFunctionUncurryThis();
    var DESCRIPTORS = requireDescriptors();
    var USE_NATIVE_URL = requireUrlConstructorDetection();
    var percentCoding = requireUrlPercentCoding();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var defineBuiltIns2 = requireDefineBuiltIns();
    var setToStringTag2 = requireSetToStringTag();
    var createIteratorConstructor = requireIteratorCreateConstructor();
    var InternalStateModule = requireInternalState();
    var anInstance2 = requireAnInstance();
    var isCallable2 = requireIsCallable();
    var hasOwn = requireHasOwnProperty();
    var bind = requireFunctionBindContext();
    var classof2 = requireClassof();
    var anObject2 = requireAnObject();
    var isObject2 = requireIsObject();
    var $toString = requireToString();
    var create2 = requireObjectCreate();
    var createPropertyDescriptor2 = requireCreatePropertyDescriptor();
    var getIterator = requireGetIteratorInternal();
    var getIteratorMethod = requireGetIteratorMethodInternal();
    var createIterResultObject2 = requireCreateIterResultObject();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var wellKnownSymbol2 = requireWellKnownSymbol();
    var arraySort2 = requireArraySort();
    var ITERATOR = wellKnownSymbol2("iterator");
    var URL_SEARCH_PARAMS = "URLSearchParams";
    var URL_SEARCH_PARAMS_ITERATOR = URL_SEARCH_PARAMS + "Iterator";
    var setInternalState = InternalStateModule.set;
    var getInternalParamsState = InternalStateModule.getterFor(URL_SEARCH_PARAMS);
    var getInternalIteratorState = InternalStateModule.getterFor(URL_SEARCH_PARAMS_ITERATOR);
    var percentDecode = percentCoding.decode;
    var percentEncode = percentCoding.encode;
    var nativeFetch = safeGetBuiltIn2("fetch");
    var NativeRequest = safeGetBuiltIn2("Request");
    var Headers = safeGetBuiltIn2("Headers");
    var RequestPrototype = NativeRequest && NativeRequest.prototype;
    var HeadersPrototype = Headers && Headers.prototype;
    var TypeError2 = globalThis2.TypeError;
    var charAt = uncurryThis("".charAt);
    var join = uncurryThis([].join);
    var push = uncurryThis([].push);
    var replace = uncurryThis("".replace);
    var shift = uncurryThis([].shift);
    var splice = uncurryThis([].splice);
    var split = uncurryThis("".split);
    var stringSlice = uncurryThis("".slice);
    var plus = /\+/g;
    var decodeQueryComponent = function(input) {
      return percentDecode(replace(input, plus, " "));
    };
    var find = /[!'()~]|%20/g;
    var replacements = {
      "!": "%21",
      "'": "%27",
      "(": "%28",
      ")": "%29",
      "~": "%7E",
      "%20": "+"
    };
    var replacer = function(match) {
      return replacements[match];
    };
    var serialize = function(it) {
      return replace(percentEncode(it), find, replacer);
    };
    var URLSearchParamsIterator = createIteratorConstructor(function Iterator2(params, kind) {
      setInternalState(this, {
        type: URL_SEARCH_PARAMS_ITERATOR,
        target: getInternalParamsState(params).entries,
        index: 0,
        kind
      });
    }, URL_SEARCH_PARAMS, function next() {
      var state = getInternalIteratorState(this);
      var target = state.target;
      var index = state.index++;
      if (!target || index >= target.length) {
        state.target = null;
        return createIterResultObject2(void 0, true);
      }
      var entry = target[index];
      switch (state.kind) {
        case "keys":
          return createIterResultObject2(entry.key, false);
        case "values":
          return createIterResultObject2(entry.value, false);
      }
      return createIterResultObject2([entry.key, entry.value], false);
    }, true);
    var URLSearchParamsState = function(init) {
      this.entries = [];
      this.url = null;
      if (init !== void 0) {
        if (isObject2(init)) this.parseObject(init);
        else this.parseQuery(typeof init == "string" ? charAt(init, 0) === "?" ? stringSlice(init, 1) : init : $toString(init));
      }
    };
    URLSearchParamsState.prototype = {
      type: URL_SEARCH_PARAMS,
      bindURL: function(url) {
        this.url = url;
        this.update();
      },
      parseObject: function(object) {
        var entries2 = this.entries;
        var iteratorMethod = getIteratorMethod(object);
        var iterator, next, step, entryIterator, entryNext, first, second;
        if (iteratorMethod) {
          iterator = getIterator(object, iteratorMethod);
          next = iterator.next;
          while (!(step = call(next, iterator)).done) {
            entryIterator = getIterator(anObject2(step.value));
            entryNext = entryIterator.next;
            if ((first = call(entryNext, entryIterator)).done || (second = call(entryNext, entryIterator)).done || !call(entryNext, entryIterator).done) throw new TypeError2("Expected sequence with length 2");
            push(entries2, { key: $toString(first.value), value: $toString(second.value) });
          }
        } else for (var key in object) if (hasOwn(object, key)) {
          push(entries2, { key, value: $toString(object[key]) });
        }
      },
      parseQuery: function(query) {
        if (query) {
          var entries2 = this.entries;
          var attributes = split(query, "&");
          var index = 0;
          var attribute, entry;
          while (index < attributes.length) {
            attribute = attributes[index++];
            if (attribute.length) {
              entry = split(attribute, "=");
              push(entries2, {
                key: decodeQueryComponent(shift(entry)),
                value: decodeQueryComponent(join(entry, "="))
              });
            }
          }
        }
      },
      serialize: function() {
        var entries2 = this.entries;
        var result = [];
        var index = 0;
        var entry;
        while (index < entries2.length) {
          entry = entries2[index++];
          push(result, serialize(entry.key) + "=" + serialize(entry.value));
        }
        return join(result, "&");
      },
      update: function() {
        this.entries.length = 0;
        this.parseQuery(this.url.query);
      },
      updateURL: function() {
        if (this.url) this.url.update();
      }
    };
    var URLSearchParamsConstructor = function URLSearchParams2() {
      anInstance2(this, URLSearchParamsPrototype);
      var init = arguments.length > 0 ? arguments[0] : void 0;
      var state = setInternalState(this, new URLSearchParamsState(init));
      if (!DESCRIPTORS) this.size = state.entries.length;
    };
    var URLSearchParamsPrototype = URLSearchParamsConstructor.prototype;
    defineBuiltIns2(URLSearchParamsPrototype, {
      // `URLSearchParams.prototype.append` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-append
      append: function append(name, value) {
        var state = getInternalParamsState(this);
        validateArgumentsLength2(arguments.length, 2);
        push(state.entries, { key: $toString(name), value: $toString(value) });
        if (!DESCRIPTORS) this.size++;
        state.updateURL();
      },
      // `URLSearchParams.prototype.delete` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-delete
      "delete": function(name) {
        var state = getInternalParamsState(this);
        var length = validateArgumentsLength2(arguments.length, 1);
        var entries2 = state.entries;
        var key = $toString(name);
        var $value = length < 2 ? void 0 : arguments[1];
        var value = $value === void 0 ? $value : $toString($value);
        var index = 0;
        while (index < entries2.length) {
          var entry = entries2[index];
          if (entry.key === key && (value === void 0 || entry.value === value)) {
            splice(entries2, index, 1);
          } else index++;
        }
        if (!DESCRIPTORS) this.size = entries2.length;
        state.updateURL();
      },
      // `URLSearchParams.prototype.get` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-get
      get: function get(name) {
        var entries2 = getInternalParamsState(this).entries;
        validateArgumentsLength2(arguments.length, 1);
        var key = $toString(name);
        var index = 0;
        for (; index < entries2.length; index++) {
          if (entries2[index].key === key) return entries2[index].value;
        }
        return null;
      },
      // `URLSearchParams.prototype.getAll` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-getall
      getAll: function getAll(name) {
        var entries2 = getInternalParamsState(this).entries;
        validateArgumentsLength2(arguments.length, 1);
        var key = $toString(name);
        var result = [];
        var index = 0;
        for (; index < entries2.length; index++) {
          if (entries2[index].key === key) push(result, entries2[index].value);
        }
        return result;
      },
      // `URLSearchParams.prototype.has` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-has
      has: function has(name) {
        var entries2 = getInternalParamsState(this).entries;
        var length = validateArgumentsLength2(arguments.length, 1);
        var key = $toString(name);
        var $value = length < 2 ? void 0 : arguments[1];
        var value = $value === void 0 ? $value : $toString($value);
        var index = 0;
        while (index < entries2.length) {
          var entry = entries2[index++];
          if (entry.key === key && (value === void 0 || entry.value === value)) return true;
        }
        return false;
      },
      // `URLSearchParams.prototype.set` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-set
      set: function set(name, value) {
        var state = getInternalParamsState(this);
        validateArgumentsLength2(arguments.length, 2);
        var entries2 = state.entries;
        var found = false;
        var key = $toString(name);
        var val = $toString(value);
        var index = 0;
        var entry;
        for (; index < entries2.length; index++) {
          entry = entries2[index];
          if (entry.key === key) {
            if (found) splice(entries2, index--, 1);
            else {
              found = true;
              entry.value = val;
            }
          }
        }
        if (!found) push(entries2, { key, value: val });
        if (!DESCRIPTORS) this.size = entries2.length;
        state.updateURL();
      },
      // `URLSearchParams.prototype.sort` method
      // https://url.spec.whatwg.org/#dom-urlsearchparams-sort
      sort: function sort() {
        var state = getInternalParamsState(this);
        arraySort2(state.entries, function(a, b) {
          return a.key > b.key ? 1 : -1;
        });
        state.updateURL();
      },
      // `URLSearchParams.prototype.forEach` method
      forEach: function forEach(callback) {
        var entries2 = getInternalParamsState(this).entries;
        var boundFunction = bind(callback, arguments.length > 1 ? arguments[1] : void 0);
        var index = 0;
        var entry;
        while (index < entries2.length) {
          entry = entries2[index++];
          boundFunction(entry.value, entry.key, this);
        }
      },
      // `URLSearchParams.prototype.keys` method
      keys: function keys() {
        return new URLSearchParamsIterator(this, "keys");
      },
      // `URLSearchParams.prototype.values` method
      values: function values() {
        return new URLSearchParamsIterator(this, "values");
      },
      // `URLSearchParams.prototype.entries` method
      entries: function entries2() {
        return new URLSearchParamsIterator(this, "entries");
      }
    }, { enumerable: true });
    defineBuiltIn2(URLSearchParamsPrototype, ITERATOR, URLSearchParamsPrototype.entries, { name: "entries" });
    defineBuiltIn2(URLSearchParamsPrototype, "toString", function toString2() {
      return getInternalParamsState(this).serialize();
    }, { enumerable: true });
    if (DESCRIPTORS) defineBuiltInAccessor2(URLSearchParamsPrototype, "size", {
      get: function size() {
        return getInternalParamsState(this).entries.length;
      },
      configurable: true,
      enumerable: true
    });
    setToStringTag2(URLSearchParamsConstructor, URL_SEARCH_PARAMS);
    $({ global: true, constructor: true, forced: !USE_NATIVE_URL }, {
      URLSearchParams: URLSearchParamsConstructor
    });
    if (!USE_NATIVE_URL && isCallable2(Headers)) {
      var headersHas = uncurryThis(HeadersPrototype.has);
      var headersSet = uncurryThis(HeadersPrototype.set);
      var wrapRequestOptions = function(init) {
        if (isObject2(init)) {
          var body = init.body;
          var headers;
          if (classof2(body) === URL_SEARCH_PARAMS) {
            headers = init.headers ? new Headers(init.headers) : new Headers();
            if (!headersHas(headers, "content-type")) {
              headersSet(headers, "content-type", "application/x-www-form-urlencoded;charset=UTF-8");
            }
            return create2(init, {
              body: createPropertyDescriptor2(0, $toString(body)),
              headers: createPropertyDescriptor2(0, headers)
            });
          }
        }
        return init;
      };
      if (isCallable2(nativeFetch)) {
        $({ global: true, enumerable: true, dontCallGetSet: true, forced: true }, {
          fetch: function fetch(input) {
            return nativeFetch(input, arguments.length > 1 ? wrapRequestOptions(arguments[1]) : {});
          }
        });
      }
      if (isCallable2(NativeRequest)) {
        var RequestConstructor = function Request(input) {
          anInstance2(this, RequestPrototype);
          return new NativeRequest(input, arguments.length > 1 ? wrapRequestOptions(arguments[1]) : {});
        };
        RequestPrototype.constructor = RequestConstructor;
        RequestConstructor.prototype = RequestPrototype;
        $({ global: true, constructor: true, dontCallGetSet: true, forced: true }, {
          Request: RequestConstructor
        });
      }
    }
    web_urlSearchParams_constructor = {
      URLSearchParams: URLSearchParamsConstructor,
      getState: getInternalParamsState
    };
    return web_urlSearchParams_constructor;
  }
  var hasRequiredWeb_url_constructor;
  function requireWeb_url_constructor() {
    if (hasRequiredWeb_url_constructor) return web_url_constructor;
    hasRequiredWeb_url_constructor = 1;
    requireEs_string_iterator();
    var $ = require_export();
    var DESCRIPTORS = requireDescriptors();
    var USE_NATIVE_URL = requireUrlConstructorDetection();
    var globalThis2 = requireGlobalThis();
    var bind = requireFunctionBindContext();
    var uncurryThis = requireFunctionUncurryThis();
    var defineBuiltIn2 = requireDefineBuiltIn();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var anInstance2 = requireAnInstance();
    var hasOwn = requireHasOwnProperty();
    var assign = requireObjectAssign();
    var arrayFrom2 = requireArrayFrom();
    var arraySlice2 = requireArraySlice();
    var codeAt = requireStringMultibyte().codeAt;
    var toASCII = requireStringPunycodeToAscii();
    var percentCoding = requireUrlPercentCoding();
    var $toString = requireToString();
    var setToStringTag2 = requireSetToStringTag();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var URLSearchParamsModule = requireWeb_urlSearchParams_constructor();
    var InternalStateModule = requireInternalState();
    var setInternalState = InternalStateModule.set;
    var getInternalURLState = InternalStateModule.getterFor("URL");
    var URLSearchParams2 = URLSearchParamsModule.URLSearchParams;
    var getInternalSearchParamsState = URLSearchParamsModule.getState;
    var percentDecode = percentCoding.decode;
    var percentEncode = percentCoding.encode;
    var NativeURL = globalThis2.URL;
    var TypeError2 = globalThis2.TypeError;
    var parseInt2 = globalThis2.parseInt;
    var floor = Math.floor;
    var pow = Math.pow;
    var fromCharCode = String.fromCharCode;
    var charAt = uncurryThis("".charAt);
    var exec = uncurryThis(/./.exec);
    var join = uncurryThis([].join);
    var numberToString2 = uncurryThis(1.1.toString);
    var pop = uncurryThis([].pop);
    var push = uncurryThis([].push);
    var replace = uncurryThis("".replace);
    var shift = uncurryThis([].shift);
    var split = uncurryThis("".split);
    var stringIndexOf2 = uncurryThis("".indexOf);
    var stringSlice = uncurryThis("".slice);
    var toLowerCase = uncurryThis("".toLowerCase);
    var unshift = uncurryThis([].unshift);
    var INVALID_AUTHORITY = "Invalid authority";
    var INVALID_SCHEME = "Invalid scheme";
    var INVALID_HOST = "Invalid host";
    var INVALID_PORT = "Invalid port";
    var ALPHA = /[a-z]/i;
    var ALPHANUMERIC_PLUS_MINUS_DOT = /[\d+\-.a-z]/i;
    var DIGIT = /\d/;
    var HEX_START = /^0x/i;
    var OCT = /^[0-7]+$/;
    var DEC = /^\d+$/;
    var HEX = /^[\da-f]+$/i;
    var FORBIDDEN_DOMAIN_CODE_POINT = /[\u0000-\u0020#%/:<>?@[\\\]^|\u007F]/;
    var FORBIDDEN_HOST_CODE_POINT = /[\0\t\n\r #/:<>?@[\\\]^|]/;
    var LEADING_C0_CONTROL_OR_SPACE = /^[\u0000-\u0020]+/;
    var TRAILING_C0_CONTROL_OR_SPACE = /(^|[^\u0000-\u0020])[\u0000-\u0020]+$/;
    var TAB_AND_NEW_LINE = /[\t\n\r]/g;
    var NON_ASCII = /[^\u0000-\u007F]/;
    var MAPPED_ONTO_FORBIDDEN = "\xA8\xAF\xB4\xB8\u02D8\u02D9\u02DA\u02DB\u02DC\u02DD\u037A\u0384\u0385\u1FBD\u1FBF\u1FC0\u1FC1\u1FCD\u1FCE\u1FCF\u1FDD\u1FDE\u1FDF\u1FED\u1FEE\u1FFD\u1FFE\u2017\u203E\u2047\u2048\u2049\u2100\u2101\u2105\u2106\u2A74\u309B\u309C\uFC5E\uFC5F\uFC60\uFC61\uFC62\uFC63\uFDFA\uFDFB\uFE13\uFE16\uFE47\uFE48\uFE49\uFE4A\uFE4B\uFE4C\uFE55\uFE56\uFE5F\uFE64\uFE65\uFE68\uFE6A\uFE6B\uFE70\uFE72\uFE74\uFE76\uFE78\uFE7A\uFE7C\uFE7E\uFFE3";
    var EOF;
    var isIgnoredCodePoint = function(code) {
      return code === 173 || code === 847 || code === 8203 || code === 12644 || code === 65279 || code === 65440 || code >= 4447 && code <= 4448 || code >= 6068 && code <= 6069 || code >= 6155 && code <= 6159 || code >= 8288 && code <= 8292 || code >= 8298 && code <= 8303 || code >= 65024 && code <= 65039 || code >= 917760 && code <= 917999;
    };
    var isDisallowedCodePoint = function(code) {
      return code === 65533 || code >= 55296 && code <= 57343 || code >= 128 && code <= 159 || code >= 8206 && code <= 8207 || code >= 8232 && code <= 8233 || code >= 8234 && code <= 8238 || code >= 8293 && code <= 8297 || code >= 64976 && code <= 65007 || (code & 65534) === 65534 || code >= 57344 && code <= 63743 || code >= 983040 && code <= 1114109;
    };
    var mapCodePoint = function(code) {
      if (code >= 65281 && code <= 65374) return code - 65248;
      if (code === 160 || code === 5760 || code === 8239 || code === 8287 || code === 12288) return 32;
      if (code >= 8192 && code <= 8202) return 32;
      if (code <= 65535 && stringIndexOf2(MAPPED_ONTO_FORBIDDEN, fromCharCode(code)) > -1) return 32;
      return code;
    };
    var domainToASCII = function(domain) {
      if (!exec(NON_ASCII, domain)) return domain === "" ? null : toASCII(domain);
      var codePoints = arrayFrom2(domain);
      var result = "";
      var index, code, mapped;
      for (index = 0; index < codePoints.length; index++) {
        code = codeAt(codePoints[index], 0);
        if (isDisallowedCodePoint(code)) return null;
        if (isIgnoredCodePoint(code)) continue;
        mapped = mapCodePoint(code);
        result += mapped === code ? codePoints[index] : fromCharCode(mapped);
      }
      return result === "" ? null : toASCII(result);
    };
    var endsInNumber = function(input) {
      var parts = split(input, ".");
      var last, hexPart;
      if (parts[parts.length - 1] === "") {
        if (parts.length === 1) return false;
        parts.length--;
      }
      last = parts[parts.length - 1];
      if (exec(DEC, last)) return true;
      if (exec(HEX_START, last)) {
        hexPart = stringSlice(last, 2);
        return hexPart === "" || !!exec(HEX, hexPart);
      }
      return false;
    };
    var parseIPv4 = function(input) {
      var parts = split(input, ".");
      var partsLength, numbers, index, part, radix, number, ipv4;
      if (parts.length && parts[parts.length - 1] === "") {
        parts.length--;
      }
      partsLength = parts.length;
      if (partsLength > 4) return null;
      numbers = [];
      for (index = 0; index < partsLength; index++) {
        part = parts[index];
        if (part === "") return null;
        radix = 10;
        if (part.length > 1 && charAt(part, 0) === "0") {
          radix = exec(HEX_START, part) ? 16 : 8;
          part = stringSlice(part, radix === 8 ? 1 : 2);
        }
        if (part === "") {
          number = 0;
        } else {
          if (!exec(radix === 10 ? DEC : radix === 8 ? OCT : HEX, part)) return null;
          number = parseInt2(part, radix);
        }
        push(numbers, number);
      }
      for (index = 0; index < partsLength; index++) {
        number = numbers[index];
        if (index === partsLength - 1) {
          if (number >= pow(256, 5 - partsLength)) return null;
        } else if (number > 255) return null;
      }
      ipv4 = pop(numbers);
      for (index = 0; index < numbers.length; index++) {
        ipv4 += numbers[index] * pow(256, 3 - index);
      }
      return ipv4;
    };
    var parseIPv6 = function(input) {
      var address = [0, 0, 0, 0, 0, 0, 0, 0];
      var pieceIndex = 0;
      var compress = null;
      var pointer = 0;
      var value, length, numbersSeen, ipv4Piece, number, swaps, swap;
      var chr = function() {
        return charAt(input, pointer);
      };
      if (chr() === ":") {
        if (charAt(input, 1) !== ":") return;
        pointer += 2;
        pieceIndex++;
        compress = pieceIndex;
      }
      while (chr()) {
        if (pieceIndex === 8) return;
        if (chr() === ":") {
          if (compress !== null) return;
          pointer++;
          pieceIndex++;
          compress = pieceIndex;
          continue;
        }
        value = length = 0;
        while (length < 4 && exec(HEX, chr())) {
          value = value * 16 + parseInt2(chr(), 16);
          pointer++;
          length++;
        }
        if (chr() === ".") {
          if (length === 0) return;
          pointer -= length;
          if (pieceIndex > 6) return;
          numbersSeen = 0;
          while (chr()) {
            ipv4Piece = null;
            if (numbersSeen > 0) {
              if (chr() === "." && numbersSeen < 4) pointer++;
              else return;
            }
            if (!exec(DIGIT, chr())) return;
            while (exec(DIGIT, chr())) {
              number = parseInt2(chr(), 10);
              if (ipv4Piece === null) ipv4Piece = number;
              else if (ipv4Piece === 0) return;
              else ipv4Piece = ipv4Piece * 10 + number;
              if (ipv4Piece > 255) return;
              pointer++;
            }
            address[pieceIndex] = address[pieceIndex] * 256 + ipv4Piece;
            numbersSeen++;
            if (numbersSeen === 2 || numbersSeen === 4) pieceIndex++;
          }
          if (numbersSeen !== 4) return;
          break;
        } else if (chr() === ":") {
          pointer++;
          if (!chr()) return;
        } else if (chr()) return;
        address[pieceIndex++] = value;
      }
      if (compress !== null) {
        swaps = pieceIndex - compress;
        pieceIndex = 7;
        while (pieceIndex !== 0 && swaps > 0) {
          swap = address[pieceIndex];
          address[pieceIndex--] = address[compress + swaps - 1];
          address[compress + --swaps] = swap;
        }
      } else if (pieceIndex !== 8) return;
      return address;
    };
    var findLongestZeroSequence = function(ipv6) {
      var maxIndex = null;
      var maxLength = 1;
      var currStart = null;
      var currLength = 0;
      var index = 0;
      for (; index < 8; index++) {
        if (ipv6[index] !== 0) {
          if (currLength > maxLength) {
            maxIndex = currStart;
            maxLength = currLength;
          }
          currStart = null;
          currLength = 0;
        } else {
          if (currStart === null) currStart = index;
          ++currLength;
        }
      }
      return currLength > maxLength ? currStart : maxIndex;
    };
    var serializeHost = function(host) {
      var result, index, compress, ignore0;
      if (typeof host == "number") {
        result = [];
        for (index = 0; index < 4; index++) {
          unshift(result, host % 256);
          host = floor(host / 256);
        }
        return join(result, ".");
      }
      if (typeof host == "object") {
        result = "";
        compress = findLongestZeroSequence(host);
        for (index = 0; index < 8; index++) {
          if (ignore0 && host[index] === 0) continue;
          if (ignore0) ignore0 = false;
          if (compress === index) {
            result += index ? ":" : "::";
            ignore0 = true;
          } else {
            result += numberToString2(host[index], 16);
            if (index < 7) result += ":";
          }
        }
        return "[" + result + "]";
      }
      return host;
    };
    var C0ControlPercentEncodeSet = {};
    var queryPercentEncodeSet = assign({}, C0ControlPercentEncodeSet, {
      " ": 1,
      '"': 1,
      "#": 1,
      "<": 1,
      ">": 1
    });
    var specialQueryPercentEncodeSet = assign({}, queryPercentEncodeSet, {
      "'": 1
    });
    var fragmentPercentEncodeSet = assign({}, C0ControlPercentEncodeSet, {
      " ": 1,
      '"': 1,
      "<": 1,
      ">": 1,
      "`": 1
    });
    var pathPercentEncodeSet = assign({}, fragmentPercentEncodeSet, {
      "#": 1,
      "?": 1,
      "{": 1,
      "}": 1,
      "^": 1
    });
    var userinfoPercentEncodeSet = assign({}, pathPercentEncodeSet, {
      "/": 1,
      ":": 1,
      ";": 1,
      "=": 1,
      "@": 1,
      "[": 1,
      "\\": 1,
      "]": 1,
      "^": 1,
      "|": 1
    });
    var utf8PercentEncode = function(chr, set) {
      var code = codeAt(chr, 0);
      return code >= 32 && code < 127 && !hasOwn(set, chr) ? chr : chr === "'" && hasOwn(set, chr) ? "%27" : percentEncode(chr);
    };
    var specialSchemes = {
      ftp: 21,
      file: null,
      http: 80,
      https: 443,
      ws: 80,
      wss: 443
    };
    var isWindowsDriveLetter = function(string, normalized) {
      var second;
      return string.length === 2 && exec(ALPHA, charAt(string, 0)) && ((second = charAt(string, 1)) === ":" || !normalized && second === "|");
    };
    var startsWithWindowsDriveLetter = function(string) {
      var third;
      return string.length > 1 && isWindowsDriveLetter(stringSlice(string, 0, 2)) && (string.length === 2 || ((third = charAt(string, 2)) === "/" || third === "\\" || third === "?" || third === "#"));
    };
    var isSingleDot = function(segment) {
      return segment === "." || toLowerCase(segment) === "%2e";
    };
    var isDoubleDot = function(segment) {
      segment = toLowerCase(segment);
      return segment === ".." || segment === "%2e." || segment === ".%2e" || segment === "%2e%2e";
    };
    var SCHEME_START = {};
    var SCHEME = {};
    var NO_SCHEME = {};
    var SPECIAL_RELATIVE_OR_AUTHORITY = {};
    var PATH_OR_AUTHORITY = {};
    var RELATIVE = {};
    var RELATIVE_SLASH = {};
    var SPECIAL_AUTHORITY_SLASHES = {};
    var SPECIAL_AUTHORITY_IGNORE_SLASHES = {};
    var AUTHORITY = {};
    var HOST = {};
    var HOSTNAME = {};
    var PORT = {};
    var FILE = {};
    var FILE_SLASH = {};
    var FILE_HOST = {};
    var PATH_START = {};
    var PATH = {};
    var CANNOT_BE_A_BASE_URL_PATH = {};
    var QUERY = {};
    var FRAGMENT = {};
    var URLState = function(url, isBase, base) {
      var urlString = $toString(url);
      var baseState, failure, searchParams;
      if (isBase) {
        failure = this.parse(urlString);
        if (failure) throw new TypeError2(failure);
        this.searchParams = null;
      } else {
        if (base !== void 0) baseState = new URLState(base, true);
        failure = this.parse(urlString, null, baseState);
        if (failure) throw new TypeError2(failure);
        searchParams = getInternalSearchParamsState(new URLSearchParams2());
        searchParams.bindURL(this);
        this.searchParams = searchParams;
      }
    };
    URLState.prototype = {
      type: "URL",
      // https://url.spec.whatwg.org/#url-parsing
      // eslint-disable-next-line max-statements -- TODO
      parse: function(input, stateOverride, base) {
        var url = this;
        var state = stateOverride || SCHEME_START;
        var pointer = 0;
        var buffer = "";
        var seenAt = false;
        var seenBracket = false;
        var seenPasswordToken = false;
        var codePoints, chr, bufferCodePoints, failure;
        input = $toString(input);
        if (!stateOverride) {
          url.scheme = "";
          url.username = "";
          url.password = "";
          url.host = null;
          url.port = null;
          url.path = [];
          url.query = null;
          url.fragment = null;
          url.cannotBeABaseURL = false;
          input = replace(input, LEADING_C0_CONTROL_OR_SPACE, "");
          input = replace(input, TRAILING_C0_CONTROL_OR_SPACE, "$1");
        }
        input = replace(input, TAB_AND_NEW_LINE, "");
        codePoints = arrayFrom2(input);
        while (pointer <= codePoints.length) {
          chr = codePoints[pointer];
          switch (state) {
            case SCHEME_START:
              if (chr && exec(ALPHA, chr)) {
                buffer += toLowerCase(chr);
                state = SCHEME;
              } else if (!stateOverride) {
                state = NO_SCHEME;
                continue;
              } else return INVALID_SCHEME;
              break;
            case SCHEME:
              if (chr && exec(ALPHANUMERIC_PLUS_MINUS_DOT, chr)) {
                buffer += toLowerCase(chr);
              } else if (chr === ":") {
                if (stateOverride && (url.isSpecial() !== hasOwn(specialSchemes, buffer) || buffer === "file" && (url.includesCredentials() || url.port !== null) || url.scheme === "file" && url.host === "")) return;
                url.scheme = buffer;
                if (stateOverride) {
                  if (url.isSpecial() && specialSchemes[url.scheme] === url.port) url.port = null;
                  return;
                }
                buffer = "";
                if (url.scheme === "file") {
                  state = FILE;
                } else if (url.isSpecial() && base && base.scheme === url.scheme) {
                  state = SPECIAL_RELATIVE_OR_AUTHORITY;
                } else if (url.isSpecial()) {
                  state = SPECIAL_AUTHORITY_SLASHES;
                } else if (codePoints[pointer + 1] === "/") {
                  state = PATH_OR_AUTHORITY;
                  pointer++;
                } else {
                  url.cannotBeABaseURL = true;
                  push(url.path, "");
                  state = CANNOT_BE_A_BASE_URL_PATH;
                }
              } else if (!stateOverride) {
                buffer = "";
                state = NO_SCHEME;
                pointer = 0;
                continue;
              } else return INVALID_SCHEME;
              break;
            case NO_SCHEME:
              if (!base || base.cannotBeABaseURL && chr !== "#") return INVALID_SCHEME;
              if (base.cannotBeABaseURL && chr === "#") {
                url.scheme = base.scheme;
                url.path = arraySlice2(base.path);
                url.query = base.query;
                url.fragment = "";
                url.cannotBeABaseURL = true;
                state = FRAGMENT;
                break;
              }
              state = base.scheme === "file" ? FILE : RELATIVE;
              continue;
            case SPECIAL_RELATIVE_OR_AUTHORITY:
              if (chr === "/" && codePoints[pointer + 1] === "/") {
                state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
                pointer++;
              } else {
                state = RELATIVE;
                continue;
              }
              break;
            case PATH_OR_AUTHORITY:
              if (chr === "/") {
                state = AUTHORITY;
                break;
              } else {
                state = PATH;
                continue;
              }
            case RELATIVE:
              url.scheme = base.scheme;
              if (chr === EOF) {
                url.username = base.username;
                url.password = base.password;
                url.host = base.host;
                url.port = base.port;
                url.path = arraySlice2(base.path);
                url.query = base.query;
              } else if (chr === "/" || chr === "\\" && url.isSpecial()) {
                state = RELATIVE_SLASH;
              } else if (chr === "?") {
                url.username = base.username;
                url.password = base.password;
                url.host = base.host;
                url.port = base.port;
                url.path = arraySlice2(base.path);
                url.query = "";
                state = QUERY;
              } else if (chr === "#") {
                url.username = base.username;
                url.password = base.password;
                url.host = base.host;
                url.port = base.port;
                url.path = arraySlice2(base.path);
                url.query = base.query;
                url.fragment = "";
                state = FRAGMENT;
              } else {
                url.username = base.username;
                url.password = base.password;
                url.host = base.host;
                url.port = base.port;
                url.path = arraySlice2(base.path);
                if (url.path.length) url.path.length--;
                state = PATH;
                continue;
              }
              break;
            case RELATIVE_SLASH:
              if (url.isSpecial() && (chr === "/" || chr === "\\")) {
                state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
              } else if (chr === "/") {
                state = AUTHORITY;
              } else {
                url.username = base.username;
                url.password = base.password;
                url.host = base.host;
                url.port = base.port;
                state = PATH;
                continue;
              }
              break;
            case SPECIAL_AUTHORITY_SLASHES:
              state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
              if (chr !== "/" || codePoints[pointer + 1] !== "/") continue;
              pointer++;
              break;
            case SPECIAL_AUTHORITY_IGNORE_SLASHES:
              if (chr !== "/" && chr !== "\\") {
                state = AUTHORITY;
                continue;
              }
              break;
            case AUTHORITY:
              if (chr === "@") {
                if (seenAt) buffer = "%40" + buffer;
                seenAt = true;
                bufferCodePoints = arrayFrom2(buffer);
                for (var i = 0; i < bufferCodePoints.length; i++) {
                  var codePoint = bufferCodePoints[i];
                  if (codePoint === ":" && !seenPasswordToken) {
                    seenPasswordToken = true;
                    continue;
                  }
                  var encodedCodePoints = utf8PercentEncode(codePoint, userinfoPercentEncodeSet);
                  if (seenPasswordToken) url.password += encodedCodePoints;
                  else url.username += encodedCodePoints;
                }
                buffer = "";
              } else if (chr === EOF || chr === "/" || chr === "?" || chr === "#" || chr === "\\" && url.isSpecial()) {
                if (seenAt && buffer === "") return INVALID_AUTHORITY;
                pointer -= arrayFrom2(buffer).length + 1;
                buffer = "";
                state = HOST;
              } else buffer += chr;
              break;
            case HOST:
            case HOSTNAME:
              if (stateOverride && url.scheme === "file") {
                state = FILE_HOST;
                continue;
              } else if (chr === ":" && !seenBracket) {
                if (buffer === "") return INVALID_HOST;
                if (stateOverride === HOSTNAME) return;
                failure = url.parseHost(buffer);
                if (failure) return failure;
                buffer = "";
                state = PORT;
              } else if (chr === EOF || chr === "/" || chr === "?" || chr === "#" || chr === "\\" && url.isSpecial()) {
                if (url.isSpecial() && buffer === "") return INVALID_HOST;
                if (stateOverride && buffer === "" && (url.includesCredentials() || url.port !== null)) return;
                failure = url.parseHost(buffer);
                if (failure) return failure;
                buffer = "";
                state = PATH_START;
                if (stateOverride) return;
                continue;
              } else {
                if (chr === "[") seenBracket = true;
                else if (chr === "]") seenBracket = false;
                buffer += chr;
              }
              break;
            case PORT:
              if (exec(DIGIT, chr)) {
                buffer += chr;
              } else if (chr === EOF || chr === "/" || chr === "?" || chr === "#" || chr === "\\" && url.isSpecial() || stateOverride) {
                if (buffer !== "") {
                  var port = parseInt2(buffer, 10);
                  if (port > 65535) return INVALID_PORT;
                  url.port = url.isSpecial() && port === specialSchemes[url.scheme] ? null : port;
                  buffer = "";
                }
                if (stateOverride) return;
                state = PATH_START;
                continue;
              } else return INVALID_PORT;
              break;
            case FILE:
              url.scheme = "file";
              url.host = "";
              if (chr === "/" || chr === "\\") state = FILE_SLASH;
              else if (base && base.scheme === "file") {
                switch (chr) {
                  case EOF:
                    url.host = base.host;
                    url.path = arraySlice2(base.path);
                    url.query = base.query;
                    break;
                  case "?":
                    url.host = base.host;
                    url.path = arraySlice2(base.path);
                    url.query = "";
                    state = QUERY;
                    break;
                  case "#":
                    url.host = base.host;
                    url.path = arraySlice2(base.path);
                    url.query = base.query;
                    url.fragment = "";
                    state = FRAGMENT;
                    break;
                  default:
                    url.host = base.host;
                    if (!startsWithWindowsDriveLetter(join(arraySlice2(codePoints, pointer), ""))) {
                      url.path = arraySlice2(base.path);
                      url.shortenPath();
                    }
                    state = PATH;
                    continue;
                }
              } else {
                state = PATH;
                continue;
              }
              break;
            case FILE_SLASH:
              if (chr === "/" || chr === "\\") {
                state = FILE_HOST;
                break;
              }
              if (base && base.scheme === "file") {
                url.host = base.host;
                if (!startsWithWindowsDriveLetter(join(arraySlice2(codePoints, pointer), "")) && isWindowsDriveLetter(base.path[0], true)) push(url.path, base.path[0]);
              }
              state = PATH;
              continue;
            case FILE_HOST:
              if (chr === EOF || chr === "/" || chr === "\\" || chr === "?" || chr === "#") {
                if (!stateOverride && isWindowsDriveLetter(buffer)) {
                  state = PATH;
                } else if (buffer === "") {
                  url.host = "";
                  if (stateOverride) return;
                  state = PATH_START;
                } else {
                  failure = url.parseHost(buffer);
                  if (failure) return failure;
                  if (url.host === "localhost") url.host = "";
                  if (stateOverride) return;
                  buffer = "";
                  state = PATH_START;
                }
                continue;
              } else buffer += chr;
              break;
            case PATH_START:
              if (url.isSpecial()) {
                state = PATH;
                if (chr !== "/" && chr !== "\\") continue;
              } else if (!stateOverride && chr === "?") {
                url.query = "";
                state = QUERY;
              } else if (!stateOverride && chr === "#") {
                url.fragment = "";
                state = FRAGMENT;
              } else if (chr !== EOF) {
                state = PATH;
                if (chr !== "/") continue;
              }
              break;
            case PATH:
              if (chr === EOF || chr === "/" || chr === "\\" && url.isSpecial() || !stateOverride && (chr === "?" || chr === "#")) {
                if (isDoubleDot(buffer)) {
                  url.shortenPath();
                  if (chr !== "/" && !(chr === "\\" && url.isSpecial())) {
                    push(url.path, "");
                  }
                } else if (isSingleDot(buffer)) {
                  if (chr !== "/" && !(chr === "\\" && url.isSpecial())) {
                    push(url.path, "");
                  }
                } else {
                  if (url.scheme === "file" && !url.path.length && isWindowsDriveLetter(buffer)) {
                    if (url.host !== null && url.host !== "") url.host = "";
                    buffer = charAt(buffer, 0) + ":";
                  }
                  push(url.path, buffer);
                }
                buffer = "";
                if (url.scheme === "file" && (chr === EOF || chr === "?" || chr === "#")) {
                  while (url.path.length > 1 && url.path[0] === "") {
                    shift(url.path);
                  }
                }
                if (chr === "?") {
                  url.query = "";
                  state = QUERY;
                } else if (chr === "#") {
                  url.fragment = "";
                  state = FRAGMENT;
                }
              } else {
                buffer += utf8PercentEncode(chr, pathPercentEncodeSet);
              }
              break;
            case CANNOT_BE_A_BASE_URL_PATH:
              if (chr === "?") {
                url.query = "";
                state = QUERY;
              } else if (chr === "#") {
                url.fragment = "";
                state = FRAGMENT;
              } else if (chr !== EOF) {
                if (chr === " ") {
                  url.path[0] += codePoints[pointer + 1] === "?" || codePoints[pointer + 1] === "#" ? "%20" : " ";
                } else {
                  url.path[0] += utf8PercentEncode(chr, C0ControlPercentEncodeSet);
                }
              }
              break;
            case QUERY:
              if (!stateOverride && chr === "#") {
                url.fragment = "";
                state = FRAGMENT;
              } else if (chr !== EOF) {
                url.query += utf8PercentEncode(chr, url.isSpecial() ? specialQueryPercentEncodeSet : queryPercentEncodeSet);
              }
              break;
            case FRAGMENT:
              if (chr !== EOF) url.fragment += utf8PercentEncode(chr, fragmentPercentEncodeSet);
              break;
          }
          pointer++;
        }
      },
      // https://url.spec.whatwg.org/#host-parsing
      parseHost: function(input) {
        var result, codePoints, index;
        if (charAt(input, 0) === "[") {
          if (charAt(input, input.length - 1) !== "]") return INVALID_HOST;
          result = parseIPv6(stringSlice(input, 1, -1));
          if (!result) return INVALID_HOST;
          this.host = result;
        } else if (!this.isSpecial()) {
          if (exec(FORBIDDEN_HOST_CODE_POINT, input)) return INVALID_HOST;
          result = "";
          codePoints = arrayFrom2(input);
          for (index = 0; index < codePoints.length; index++) {
            result += utf8PercentEncode(codePoints[index], C0ControlPercentEncodeSet);
          }
          this.host = result;
        } else {
          input = domainToASCII(percentDecode(input));
          if (input === null || exec(FORBIDDEN_DOMAIN_CODE_POINT, input)) return INVALID_HOST;
          if (endsInNumber(input)) {
            result = parseIPv4(input);
            if (result === null) return INVALID_HOST;
            this.host = result;
          } else {
            this.host = input;
          }
        }
      },
      // https://url.spec.whatwg.org/#cannot-have-a-username-password-port
      cannotHaveUsernamePasswordPort: function() {
        return this.host === null || this.host === "" || this.cannotBeABaseURL || this.scheme === "file";
      },
      // https://url.spec.whatwg.org/#include-credentials
      includesCredentials: function() {
        return this.username !== "" || this.password !== "";
      },
      // https://url.spec.whatwg.org/#is-special
      isSpecial: function() {
        return hasOwn(specialSchemes, this.scheme);
      },
      // https://url.spec.whatwg.org/#shorten-a-urls-path
      shortenPath: function() {
        var path2 = this.path;
        var pathSize = path2.length;
        if (pathSize && (this.scheme !== "file" || pathSize !== 1 || !isWindowsDriveLetter(path2[0], true))) {
          path2.length--;
        }
      },
      // https://url.spec.whatwg.org/#concept-url-serializer
      serialize: function() {
        var url = this;
        var scheme = url.scheme;
        var username = url.username;
        var password = url.password;
        var host = url.host;
        var port = url.port;
        var path2 = url.path;
        var query = url.query;
        var fragment = url.fragment;
        var output = scheme + ":";
        if (host !== null) {
          output += "//";
          if (url.includesCredentials()) {
            output += username + (password ? ":" + password : "") + "@";
          }
          output += serializeHost(host);
          if (port !== null) output += ":" + port;
        } else if (scheme === "file") output += "//";
        if (host === null && !url.cannotBeABaseURL && path2.length > 1 && path2[0] === "") output += "/.";
        output += url.cannotBeABaseURL ? path2[0] : path2.length ? "/" + join(path2, "/") : "";
        if (query !== null) output += "?" + query;
        if (fragment !== null) output += "#" + fragment;
        return output;
      },
      // https://url.spec.whatwg.org/#dom-url-href
      setHref: function(href) {
        var failure = this.parse(href);
        if (failure) throw new TypeError2(failure);
        this.searchParams.update();
      },
      // https://url.spec.whatwg.org/#dom-url-origin
      getOrigin: function() {
        var scheme = this.scheme;
        var port = this.port;
        if (scheme === "blob") try {
          return new URLConstructor(this.path[0]).origin;
        } catch (error) {
          return "null";
        }
        if (scheme === "file" || !this.isSpecial()) return "null";
        return scheme + "://" + serializeHost(this.host) + (port !== null ? ":" + port : "");
      },
      // https://url.spec.whatwg.org/#dom-url-protocol
      getProtocol: function() {
        return this.scheme + ":";
      },
      setProtocol: function(protocol) {
        this.parse($toString(protocol) + ":", SCHEME_START);
      },
      // https://url.spec.whatwg.org/#dom-url-username
      getUsername: function() {
        return this.username;
      },
      setUsername: function(username) {
        var codePoints = arrayFrom2($toString(username));
        if (this.cannotHaveUsernamePasswordPort()) return;
        this.username = "";
        for (var i = 0; i < codePoints.length; i++) {
          this.username += utf8PercentEncode(codePoints[i], userinfoPercentEncodeSet);
        }
      },
      // https://url.spec.whatwg.org/#dom-url-password
      getPassword: function() {
        return this.password;
      },
      setPassword: function(password) {
        var codePoints = arrayFrom2($toString(password));
        if (this.cannotHaveUsernamePasswordPort()) return;
        this.password = "";
        for (var i = 0; i < codePoints.length; i++) {
          this.password += utf8PercentEncode(codePoints[i], userinfoPercentEncodeSet);
        }
      },
      // https://url.spec.whatwg.org/#dom-url-host
      getHost: function() {
        var host = this.host;
        var port = this.port;
        return host === null ? "" : port === null ? serializeHost(host) : serializeHost(host) + ":" + port;
      },
      setHost: function(host) {
        if (this.cannotBeABaseURL) return;
        this.parse(host, HOST);
      },
      // https://url.spec.whatwg.org/#dom-url-hostname
      getHostname: function() {
        var host = this.host;
        return host === null ? "" : serializeHost(host);
      },
      setHostname: function(hostname) {
        if (this.cannotBeABaseURL) return;
        this.parse(hostname, HOSTNAME);
      },
      // https://url.spec.whatwg.org/#dom-url-port
      getPort: function() {
        var port = this.port;
        return port === null ? "" : $toString(port);
      },
      setPort: function(port) {
        if (this.cannotHaveUsernamePasswordPort()) return;
        port = $toString(port);
        if (port === "") this.port = null;
        else this.parse(port, PORT);
      },
      // https://url.spec.whatwg.org/#dom-url-pathname
      getPathname: function() {
        var path2 = this.path;
        return this.cannotBeABaseURL ? path2[0] : path2.length ? "/" + join(path2, "/") : "";
      },
      setPathname: function(pathname) {
        if (this.cannotBeABaseURL) return;
        this.path = [];
        this.parse(pathname, PATH_START);
      },
      // https://url.spec.whatwg.org/#dom-url-search
      getSearch: function() {
        var query = this.query;
        return query ? "?" + query : "";
      },
      setSearch: function(search) {
        search = $toString(search);
        if (search === "") {
          this.query = null;
        } else {
          if (charAt(search, 0) === "?") search = stringSlice(search, 1);
          this.query = "";
          this.parse(search, QUERY);
        }
        this.searchParams.update();
      },
      // https://url.spec.whatwg.org/#dom-url-searchparams
      getSearchParams: function() {
        return this.searchParams.facade;
      },
      // https://url.spec.whatwg.org/#dom-url-hash
      getHash: function() {
        var fragment = this.fragment;
        return fragment ? "#" + fragment : "";
      },
      setHash: function(hash) {
        hash = $toString(hash);
        if (hash === "") {
          this.fragment = null;
          return;
        }
        if (charAt(hash, 0) === "#") hash = stringSlice(hash, 1);
        this.fragment = "";
        this.parse(hash, FRAGMENT);
      },
      update: function() {
        this.query = this.searchParams.serialize() || null;
      }
    };
    var URLConstructor = function URL2(url) {
      var that = anInstance2(this, URLPrototype);
      var base = validateArgumentsLength2(arguments.length, 1) > 1 ? arguments[1] : void 0;
      var state = setInternalState(that, new URLState(url, false, base));
      if (!DESCRIPTORS) {
        that.href = state.serialize();
        that.origin = state.getOrigin();
        that.protocol = state.getProtocol();
        that.username = state.getUsername();
        that.password = state.getPassword();
        that.host = state.getHost();
        that.hostname = state.getHostname();
        that.port = state.getPort();
        that.pathname = state.getPathname();
        that.search = state.getSearch();
        that.searchParams = state.getSearchParams();
        that.hash = state.getHash();
      }
    };
    var URLPrototype = URLConstructor.prototype;
    var accessorDescriptor = function(getter, setter) {
      return {
        get: function() {
          return getInternalURLState(this)[getter]();
        },
        set: setter && function(value) {
          return getInternalURLState(this)[setter](value);
        },
        configurable: true,
        enumerable: true
      };
    };
    if (DESCRIPTORS) {
      defineBuiltInAccessor2(URLPrototype, "href", accessorDescriptor("serialize", "setHref"));
      defineBuiltInAccessor2(URLPrototype, "origin", accessorDescriptor("getOrigin"));
      defineBuiltInAccessor2(URLPrototype, "protocol", accessorDescriptor("getProtocol", "setProtocol"));
      defineBuiltInAccessor2(URLPrototype, "username", accessorDescriptor("getUsername", "setUsername"));
      defineBuiltInAccessor2(URLPrototype, "password", accessorDescriptor("getPassword", "setPassword"));
      defineBuiltInAccessor2(URLPrototype, "host", accessorDescriptor("getHost", "setHost"));
      defineBuiltInAccessor2(URLPrototype, "hostname", accessorDescriptor("getHostname", "setHostname"));
      defineBuiltInAccessor2(URLPrototype, "port", accessorDescriptor("getPort", "setPort"));
      defineBuiltInAccessor2(URLPrototype, "pathname", accessorDescriptor("getPathname", "setPathname"));
      defineBuiltInAccessor2(URLPrototype, "search", accessorDescriptor("getSearch", "setSearch"));
      defineBuiltInAccessor2(URLPrototype, "searchParams", accessorDescriptor("getSearchParams"));
      defineBuiltInAccessor2(URLPrototype, "hash", accessorDescriptor("getHash", "setHash"));
    }
    defineBuiltIn2(URLPrototype, "toJSON", function toJSON() {
      return getInternalURLState(this).serialize();
    }, { enumerable: true });
    defineBuiltIn2(URLPrototype, "toString", function toString2() {
      return getInternalURLState(this).serialize();
    }, { enumerable: true });
    if (NativeURL) {
      var nativeCreateObjectURL = NativeURL.createObjectURL;
      var nativeRevokeObjectURL = NativeURL.revokeObjectURL;
      if (nativeCreateObjectURL) defineBuiltIn2(URLConstructor, "createObjectURL", bind(nativeCreateObjectURL, NativeURL));
      if (nativeRevokeObjectURL) defineBuiltIn2(URLConstructor, "revokeObjectURL", bind(nativeRevokeObjectURL, NativeURL));
    }
    setToStringTag2(URLConstructor, "URL");
    $({ global: true, constructor: true, forced: !USE_NATIVE_URL, sham: !DESCRIPTORS }, {
      URL: URLConstructor
    });
    return web_url_constructor;
  }
  var hasRequiredWeb_url;
  function requireWeb_url() {
    if (hasRequiredWeb_url) return web_url;
    hasRequiredWeb_url = 1;
    requireWeb_url_constructor();
    return web_url;
  }
  requireWeb_url();
  var web_url_canParse = {};
  var hasRequiredWeb_url_canParse;
  function requireWeb_url_canParse() {
    if (hasRequiredWeb_url_canParse) return web_url_canParse;
    hasRequiredWeb_url_canParse = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var fails2 = requireFails();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var toString2 = requireToString();
    var USE_NATIVE_URL = requireUrlConstructorDetection();
    var URL2 = getBuiltIn2("URL");
    var THROWS_WITHOUT_ARGUMENTS = USE_NATIVE_URL && fails2(function() {
      URL2.canParse();
    });
    var WRONG_ARITY = fails2(function() {
      return URL2.canParse.length !== 1;
    });
    $({ target: "URL", stat: true, forced: !THROWS_WITHOUT_ARGUMENTS || WRONG_ARITY }, {
      canParse: function canParse(url) {
        var length = validateArgumentsLength2(arguments.length, 1);
        var urlString = toString2(url);
        var base = length < 2 || arguments[1] === void 0 ? void 0 : toString2(arguments[1]);
        try {
          return !!new URL2(urlString, base);
        } catch (error) {
          return false;
        }
      }
    });
    return web_url_canParse;
  }
  requireWeb_url_canParse();
  var web_url_parse = {};
  var hasRequiredWeb_url_parse;
  function requireWeb_url_parse() {
    if (hasRequiredWeb_url_parse) return web_url_parse;
    hasRequiredWeb_url_parse = 1;
    var $ = require_export();
    var getBuiltIn2 = requireGetBuiltIn();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var toString2 = requireToString();
    var USE_NATIVE_URL = requireUrlConstructorDetection();
    var URL2 = getBuiltIn2("URL");
    $({ target: "URL", stat: true, forced: !USE_NATIVE_URL }, {
      parse: function parse(url) {
        var length = validateArgumentsLength2(arguments.length, 1);
        var urlString = toString2(url);
        var base = length < 2 || arguments[1] === void 0 ? void 0 : toString2(arguments[1]);
        try {
          return new URL2(urlString, base);
        } catch (error) {
          return null;
        }
      }
    });
    return web_url_parse;
  }
  requireWeb_url_parse();
  var web_url_toJson = {};
  var hasRequiredWeb_url_toJson;
  function requireWeb_url_toJson() {
    if (hasRequiredWeb_url_toJson) return web_url_toJson;
    hasRequiredWeb_url_toJson = 1;
    var $ = require_export();
    var call = requireFunctionCall();
    var getBuiltInPrototypeMethod2 = requireGetBuiltInPrototypeMethod();
    var toString2 = getBuiltInPrototypeMethod2("URL", "toString");
    $({ target: "URL", proto: true, enumerable: true }, {
      toJSON: function toJSON() {
        return call(toString2, this);
      }
    });
    return web_url_toJson;
  }
  requireWeb_url_toJson();
  var web_urlSearchParams = {};
  var hasRequiredWeb_urlSearchParams;
  function requireWeb_urlSearchParams() {
    if (hasRequiredWeb_urlSearchParams) return web_urlSearchParams;
    hasRequiredWeb_urlSearchParams = 1;
    requireWeb_urlSearchParams_constructor();
    return web_urlSearchParams;
  }
  requireWeb_urlSearchParams();
  var web_urlSearchParams_delete = {};
  var hasRequiredWeb_urlSearchParams_delete;
  function requireWeb_urlSearchParams_delete() {
    if (hasRequiredWeb_urlSearchParams_delete) return web_urlSearchParams_delete;
    hasRequiredWeb_urlSearchParams_delete = 1;
    var defineBuiltIn2 = requireDefineBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var toString2 = requireToString();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var $URLSearchParams = URLSearchParams;
    var URLSearchParamsPrototype = $URLSearchParams.prototype;
    var append = uncurryThis(URLSearchParamsPrototype.append);
    var $delete = uncurryThis(URLSearchParamsPrototype["delete"]);
    var forEach = uncurryThis(URLSearchParamsPrototype.forEach);
    var push = uncurryThis([].push);
    var params = new $URLSearchParams("a=1&a=2&b=3");
    params["delete"]("a", 1);
    params["delete"]("b", void 0);
    if (params + "" !== "a=2") {
      defineBuiltIn2(URLSearchParamsPrototype, "delete", function(name) {
        var length = arguments.length;
        var $value = length < 2 ? void 0 : arguments[1];
        if (length && $value === void 0) return $delete(this, name);
        var entries2 = [];
        forEach(this, function(v, k) {
          push(entries2, { key: k, value: v });
        });
        validateArgumentsLength2(length, 1);
        var key = toString2(name);
        var value = toString2($value);
        var index = 0;
        var entriesLength = entries2.length;
        var entry;
        while (index < entriesLength) {
          entry = entries2[index];
          $delete(this, entry.key);
          index++;
        }
        index = 0;
        while (index < entriesLength) {
          entry = entries2[index++];
          if (!(entry.key === key && entry.value === value)) append(this, entry.key, entry.value);
        }
      }, { enumerable: true, unsafe: true });
    }
    return web_urlSearchParams_delete;
  }
  requireWeb_urlSearchParams_delete();
  var web_urlSearchParams_has = {};
  var hasRequiredWeb_urlSearchParams_has;
  function requireWeb_urlSearchParams_has() {
    if (hasRequiredWeb_urlSearchParams_has) return web_urlSearchParams_has;
    hasRequiredWeb_urlSearchParams_has = 1;
    var defineBuiltIn2 = requireDefineBuiltIn();
    var uncurryThis = requireFunctionUncurryThis();
    var toString2 = requireToString();
    var validateArgumentsLength2 = requireValidateArgumentsLength();
    var $URLSearchParams = URLSearchParams;
    var URLSearchParamsPrototype = $URLSearchParams.prototype;
    var getAll = uncurryThis(URLSearchParamsPrototype.getAll);
    var $has = uncurryThis(URLSearchParamsPrototype.has);
    var params = new $URLSearchParams("a=1");
    if (params.has("a", 2) || !params.has("a", void 0)) {
      defineBuiltIn2(URLSearchParamsPrototype, "has", function has(name) {
        var length = arguments.length;
        var $value = length < 2 ? void 0 : arguments[1];
        if (length && $value === void 0) return $has(this, name);
        var values = getAll(this, name);
        validateArgumentsLength2(length, 1);
        var value = toString2($value);
        var index = 0;
        while (index < values.length) {
          if (values[index++] === value) return true;
        }
        return false;
      }, { enumerable: true, unsafe: true });
    }
    return web_urlSearchParams_has;
  }
  requireWeb_urlSearchParams_has();
  var web_urlSearchParams_size = {};
  var hasRequiredWeb_urlSearchParams_size;
  function requireWeb_urlSearchParams_size() {
    if (hasRequiredWeb_urlSearchParams_size) return web_urlSearchParams_size;
    hasRequiredWeb_urlSearchParams_size = 1;
    var DESCRIPTORS = requireDescriptors();
    var uncurryThis = requireFunctionUncurryThis();
    var defineBuiltInAccessor2 = requireDefineBuiltInAccessor();
    var URLSearchParamsPrototype = URLSearchParams.prototype;
    var forEach = uncurryThis(URLSearchParamsPrototype.forEach);
    if (DESCRIPTORS && !("size" in URLSearchParamsPrototype)) {
      defineBuiltInAccessor2(URLSearchParamsPrototype, "size", {
        get: function size() {
          var count = 0;
          forEach(this, function() {
            count++;
          });
          return count;
        },
        configurable: true,
        enumerable: true
      });
    }
    return web_urlSearchParams_size;
  }
  requireWeb_urlSearchParams_size();
  /*! @license DOMPurify 3.4.14 | (c) Cure53 and other contributors | Released under the Apache license 2.0 and Mozilla Public License 2.0 | github.com/cure53/DOMPurify/blob/3.4.14/LICENSE */
  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles(r) {
    if (Array.isArray(r)) return r;
  }
  function _iterableToArrayLimit(r, l) {
    var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
    if (null != t) {
      var e, n, i, u, a = [], f = true, o = false;
      try {
        if (i = (t = t.call(r)).next, 0 === l) ;
        else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = true) ;
      } catch (r2) {
        o = true, n = r2;
      } finally {
        try {
          if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return;
        } finally {
          if (o) throw n;
        }
      }
      return a;
    }
  }
  function _nonIterableRest() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }
  const entries = Object.entries, setPrototypeOf = Object.setPrototypeOf, isFrozen = Object.isFrozen, getPrototypeOf = Object.getPrototypeOf, getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
  let freeze = Object.freeze, seal = Object.seal, create = Object.create;
  let _ref = typeof Reflect !== "undefined" && Reflect, apply = _ref.apply, construct = _ref.construct;
  if (!freeze) {
    freeze = function freeze2(x) {
      return x;
    };
  }
  if (!seal) {
    seal = function seal2(x) {
      return x;
    };
  }
  if (!apply) {
    apply = function apply2(func, thisArg) {
      for (var _len = arguments.length, args = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
        args[_key - 2] = arguments[_key];
      }
      return func.apply(thisArg, args);
    };
  }
  if (!construct) {
    construct = function construct2(Func) {
      for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }
      return new Func(...args);
    };
  }
  const arrayForEach = unapply(Array.prototype.forEach);
  const arrayLastIndexOf = unapply(Array.prototype.lastIndexOf);
  const arrayPop = unapply(Array.prototype.pop);
  const arrayPush = unapply(Array.prototype.push);
  const arraySplice = unapply(Array.prototype.splice);
  const arrayIsArray = Array.isArray;
  const stringToLowerCase = unapply(String.prototype.toLowerCase);
  const stringToString = unapply(String.prototype.toString);
  const stringMatch = unapply(String.prototype.match);
  const stringReplace = unapply(String.prototype.replace);
  const stringIndexOf = unapply(String.prototype.indexOf);
  const stringTrim = unapply(String.prototype.trim);
  const numberToString = unapply(Number.prototype.toString);
  const booleanToString = unapply(Boolean.prototype.toString);
  const bigintToString = typeof BigInt === "undefined" ? null : unapply(BigInt.prototype.toString);
  const symbolToString = typeof Symbol === "undefined" ? null : unapply(Symbol.prototype.toString);
  const objectHasOwnProperty = unapply(Object.prototype.hasOwnProperty);
  const objectToString = unapply(Object.prototype.toString);
  const regExpTest = unapply(RegExp.prototype.test);
  const typeErrorCreate = unconstruct(TypeError);
  function unapply(func) {
    return function(thisArg) {
      if (thisArg instanceof RegExp) {
        thisArg.lastIndex = 0;
      }
      for (var _len3 = arguments.length, args = new Array(_len3 > 1 ? _len3 - 1 : 0), _key3 = 1; _key3 < _len3; _key3++) {
        args[_key3 - 1] = arguments[_key3];
      }
      return apply(func, thisArg, args);
    };
  }
  function unconstruct(Func) {
    return function() {
      for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
        args[_key4] = arguments[_key4];
      }
      return construct(Func, args);
    };
  }
  function addToSet(set, array) {
    let transformCaseFunc = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : stringToLowerCase;
    if (setPrototypeOf) {
      setPrototypeOf(set, null);
    }
    if (!arrayIsArray(array)) {
      return set;
    }
    let l = array.length;
    while (l--) {
      let element = array[l];
      if (typeof element === "string") {
        const lcElement = transformCaseFunc(element);
        if (lcElement !== element) {
          if (!isFrozen(array)) {
            array[l] = lcElement;
          }
          element = lcElement;
        }
      }
      set[element] = true;
    }
    return set;
  }
  function cleanArray(array) {
    for (let index = 0; index < array.length; index++) {
      const isPropertyExist = objectHasOwnProperty(array, index);
      if (!isPropertyExist) {
        array[index] = null;
      }
    }
    return array;
  }
  function clone(object) {
    const newObject = create(null);
    for (const _ref2 of entries(object)) {
      var _ref3 = _slicedToArray(_ref2, 2);
      const property = _ref3[0];
      const value = _ref3[1];
      const isPropertyExist = objectHasOwnProperty(object, property);
      if (isPropertyExist) {
        if (arrayIsArray(value)) {
          newObject[property] = cleanArray(value);
        } else if (value && typeof value === "object" && value.constructor === Object) {
          newObject[property] = clone(value);
        } else {
          newObject[property] = value;
        }
      }
    }
    return newObject;
  }
  function stringifyValue(value) {
    switch (typeof value) {
      case "string": {
        return value;
      }
      case "number": {
        return numberToString(value);
      }
      case "boolean": {
        return booleanToString(value);
      }
      case "bigint": {
        return bigintToString ? bigintToString(value) : "0";
      }
      case "symbol": {
        return symbolToString ? symbolToString(value) : "Symbol()";
      }
      case "undefined": {
        return objectToString(value);
      }
      case "function":
      case "object": {
        if (value === null) {
          return objectToString(value);
        }
        const valueAsRecord = value;
        const valueToString = lookupGetter(valueAsRecord, "toString");
        if (typeof valueToString === "function") {
          const stringified = valueToString(valueAsRecord);
          return typeof stringified === "string" ? stringified : objectToString(stringified);
        }
        return objectToString(value);
      }
      default: {
        return objectToString(value);
      }
    }
  }
  function lookupGetter(object, prop) {
    while (object !== null) {
      const desc = getOwnPropertyDescriptor(object, prop);
      if (desc) {
        if (desc.get) {
          return unapply(desc.get);
        }
        if (typeof desc.value === "function") {
          return unapply(desc.value);
        }
      }
      object = getPrototypeOf(object);
    }
    function fallbackValue() {
      return null;
    }
    return fallbackValue;
  }
  function isRegex(value) {
    try {
      regExpTest(value, "");
      return true;
    } catch (_unused) {
      return false;
    }
  }
  const html$1 = freeze(["a", "abbr", "acronym", "address", "area", "article", "aside", "audio", "b", "bdi", "bdo", "big", "blink", "blockquote", "body", "br", "button", "canvas", "caption", "center", "cite", "code", "col", "colgroup", "content", "data", "datalist", "dd", "decorator", "del", "details", "dfn", "dialog", "dir", "div", "dl", "dt", "element", "em", "fieldset", "figcaption", "figure", "font", "footer", "form", "h1", "h2", "h3", "h4", "h5", "h6", "head", "header", "hgroup", "hr", "html", "i", "img", "input", "ins", "kbd", "label", "legend", "li", "main", "map", "mark", "marquee", "menu", "menuitem", "meter", "nav", "nobr", "ol", "optgroup", "option", "output", "p", "picture", "pre", "progress", "q", "rp", "rt", "ruby", "s", "samp", "search", "section", "select", "shadow", "slot", "small", "source", "spacer", "span", "strike", "strong", "style", "sub", "summary", "sup", "table", "tbody", "td", "template", "textarea", "tfoot", "th", "thead", "time", "tr", "track", "tt", "u", "ul", "var", "video", "wbr"]);
  const svg$1 = freeze(["svg", "a", "altglyph", "altglyphdef", "altglyphitem", "animatecolor", "animatemotion", "animatetransform", "circle", "clippath", "defs", "desc", "ellipse", "enterkeyhint", "exportparts", "filter", "font", "g", "glyph", "glyphref", "hkern", "image", "inputmode", "line", "lineargradient", "marker", "mask", "metadata", "mpath", "part", "path", "pattern", "polygon", "polyline", "radialgradient", "rect", "stop", "style", "switch", "symbol", "text", "textpath", "title", "tref", "tspan", "view", "vkern"]);
  const svgFilters = freeze(["feBlend", "feColorMatrix", "feComponentTransfer", "feComposite", "feConvolveMatrix", "feDiffuseLighting", "feDisplacementMap", "feDistantLight", "feDropShadow", "feFlood", "feFuncA", "feFuncB", "feFuncG", "feFuncR", "feGaussianBlur", "feImage", "feMerge", "feMergeNode", "feMorphology", "feOffset", "fePointLight", "feSpecularLighting", "feSpotLight", "feTile", "feTurbulence"]);
  const svgDisallowed = freeze(["animate", "color-profile", "cursor", "discard", "font-face", "font-face-format", "font-face-name", "font-face-src", "font-face-uri", "foreignobject", "hatch", "hatchpath", "mesh", "meshgradient", "meshpatch", "meshrow", "missing-glyph", "script", "set", "solidcolor", "unknown", "use"]);
  const mathMl$1 = freeze(["math", "menclose", "merror", "mfenced", "mfrac", "mglyph", "mi", "mlabeledtr", "mmultiscripts", "mn", "mo", "mover", "mpadded", "mphantom", "mroot", "mrow", "ms", "mspace", "msqrt", "mstyle", "msub", "msup", "msubsup", "mtable", "mtd", "mtext", "mtr", "munder", "munderover", "mprescripts"]);
  const mathMlDisallowed = freeze(["maction", "maligngroup", "malignmark", "mlongdiv", "mscarries", "mscarry", "msgroup", "mstack", "msline", "msrow", "semantics", "annotation", "annotation-xml", "mprescripts", "none"]);
  const text = freeze(["#text"]);
  const html = freeze(["accept", "action", "align", "alt", "autocapitalize", "autocomplete", "autopictureinpicture", "autoplay", "background", "bgcolor", "border", "capture", "cellpadding", "cellspacing", "checked", "cite", "class", "clear", "color", "cols", "colspan", "command", "commandfor", "controls", "controlslist", "coords", "crossorigin", "datetime", "decoding", "default", "dir", "disabled", "disablepictureinpicture", "disableremoteplayback", "download", "draggable", "enctype", "enterkeyhint", "exportparts", "face", "for", "headers", "height", "hidden", "high", "href", "hreflang", "id", "inert", "inputmode", "integrity", "ismap", "kind", "label", "lang", "list", "loading", "loop", "low", "max", "maxlength", "media", "method", "min", "minlength", "multiple", "muted", "name", "nonce", "noshade", "novalidate", "nowrap", "open", "optimum", "part", "pattern", "placeholder", "playsinline", "popover", "popovertarget", "popovertargetaction", "poster", "preload", "pubdate", "radiogroup", "readonly", "rel", "required", "rev", "reversed", "role", "rows", "rowspan", "spellcheck", "scope", "selected", "shape", "size", "sizes", "slot", "span", "srclang", "start", "src", "srcset", "step", "style", "summary", "tabindex", "title", "translate", "type", "usemap", "valign", "value", "width", "wrap", "xmlns"]);
  const svg = freeze(["accent-height", "accumulate", "additive", "alignment-baseline", "amplitude", "ascent", "attributename", "attributetype", "azimuth", "basefrequency", "baseline-shift", "begin", "bias", "by", "class", "clip", "clippathunits", "clip-path", "clip-rule", "color", "color-interpolation", "color-interpolation-filters", "color-profile", "color-rendering", "cx", "cy", "d", "dx", "dy", "diffuseconstant", "direction", "display", "divisor", "dominant-baseline", "dur", "edgemode", "elevation", "end", "exponent", "fill", "fill-opacity", "fill-rule", "filter", "filterunits", "flood-color", "flood-opacity", "font-family", "font-size", "font-size-adjust", "font-stretch", "font-style", "font-variant", "font-weight", "fx", "fy", "g1", "g2", "glyph-name", "glyphref", "gradientunits", "gradienttransform", "height", "href", "id", "image-rendering", "in", "in2", "intercept", "k", "k1", "k2", "k3", "k4", "kerning", "keypoints", "keysplines", "keytimes", "lang", "lengthadjust", "letter-spacing", "kernelmatrix", "kernelunitlength", "lighting-color", "local", "marker-end", "marker-mid", "marker-start", "markerheight", "markerunits", "markerwidth", "maskcontentunits", "maskunits", "max", "mask", "mask-type", "media", "method", "mode", "min", "name", "numoctaves", "offset", "operator", "opacity", "order", "orient", "orientation", "origin", "overflow", "paint-order", "path", "pathlength", "patterncontentunits", "patterntransform", "patternunits", "pointer-events", "points", "preservealpha", "preserveaspectratio", "primitiveunits", "r", "rx", "ry", "radius", "refx", "refy", "repeatcount", "repeatdur", "restart", "result", "rotate", "scale", "seed", "shape-rendering", "slope", "specularconstant", "specularexponent", "spreadmethod", "startoffset", "stddeviation", "stitchtiles", "stop-color", "stop-opacity", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke", "stroke-width", "style", "surfacescale", "systemlanguage", "tabindex", "tablevalues", "targetx", "targety", "transform", "transform-origin", "text-anchor", "text-decoration", "text-orientation", "text-rendering", "textlength", "type", "u1", "u2", "unicode", "values", "vector-effect", "viewbox", "visibility", "version", "vert-adv-y", "vert-origin-x", "vert-origin-y", "width", "word-spacing", "wrap", "writing-mode", "xchannelselector", "ychannelselector", "x", "x1", "x2", "xmlns", "y", "y1", "y2", "z", "zoomandpan"]);
  const mathMl = freeze(["accent", "accentunder", "align", "bevelled", "close", "columnalign", "columnlines", "columnspacing", "columnspan", "denomalign", "depth", "dir", "display", "displaystyle", "encoding", "fence", "frame", "height", "href", "id", "largeop", "length", "linethickness", "lquote", "lspace", "mathbackground", "mathcolor", "mathsize", "mathvariant", "maxsize", "minsize", "movablelimits", "notation", "numalign", "open", "rowalign", "rowlines", "rowspacing", "rowspan", "rspace", "rquote", "scriptlevel", "scriptminsize", "scriptsizemultiplier", "selection", "separator", "separators", "stretchy", "subscriptshift", "supscriptshift", "symmetric", "voffset", "width", "xmlns"]);
  const xml = freeze(["xlink:href", "xml:id", "xlink:title", "xml:space", "xmlns:xlink"]);
  const MUSTACHE_EXPR = seal(/{{[\w\W]*|^[\w\W]*}}/g);
  const ERB_EXPR = seal(/<%[\w\W]*|^[\w\W]*%>/g);
  const TMPLIT_EXPR = seal(/\${[\w\W]*/g);
  const DATA_ATTR = seal(/^data-[\-\w.\u00B7-\uFFFF]+$/);
  const ARIA_ATTR = seal(/^aria-[\-\w]+$/);
  const IS_ALLOWED_URI = seal(
    /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|sms|cid|xmpp|matrix):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i
    // eslint-disable-line no-useless-escape
  );
  const IS_SCRIPT_OR_DATA = seal(/^(?:\w+script|data):/i);
  const ATTR_WHITESPACE = seal(
    /[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g
    // eslint-disable-line no-control-regex
  );
  const DOCTYPE_NAME = seal(/^html$/i);
  const CUSTOM_ELEMENT = seal(/^[a-z][.\w]*(-[.\w]+)+$/i);
  const ELEMENT_MARKUP_PROBE = seal(/<[/\w!]/g);
  const COMMENT_MARKUP_PROBE = seal(/<[/\w]/g);
  const FALLBACK_TAG_CLOSE = seal(/<\/no(script|embed|frames)/i);
  const SELF_CLOSING_TAG = seal(/\/>/i);
  const NODE_TYPE = {
    element: 1,
    attribute: 2,
    text: 3,
    cdataSection: 4,
    entityReference: 5,
    // Deprecated
    entityNode: 6,
    // Deprecated
    processingInstruction: 7,
    comment: 8,
    document: 9,
    documentType: 10,
    documentFragment: 11,
    notation: 12
    // Deprecated
  };
  const LITERAL_TEXT_ELEMENT_NAMES = ["style", "script", "xmp", "iframe", "noembed", "noframes", "plaintext", "noscript"];
  const LITERAL_TEXT_ELEMENTS = freeze(addToSet({}, LITERAL_TEXT_ELEMENT_NAMES));
  const LITERAL_TEXT_CLOSE = (function() {
    const map = {};
    arrayForEach(LITERAL_TEXT_ELEMENT_NAMES, (name) => {
      map[name] = seal(new RegExp("</" + name + "(?=[\\t\\n\\f\\r />])", "i"));
    });
    return freeze(map);
  })();
  const getGlobal = function getGlobal2() {
    return typeof window === "undefined" ? null : window;
  };
  const _createTrustedTypesPolicy = function _createTrustedTypesPolicy2(trustedTypes, purifyHostElement) {
    if (typeof trustedTypes !== "object" || typeof trustedTypes.createPolicy !== "function") {
      return null;
    }
    let suffix = null;
    const ATTR_NAME = "data-tt-policy-suffix";
    if (purifyHostElement && purifyHostElement.hasAttribute(ATTR_NAME)) {
      suffix = purifyHostElement.getAttribute(ATTR_NAME);
    }
    const policyName = "dompurify" + (suffix ? "#" + suffix : "");
    try {
      return trustedTypes.createPolicy(policyName, {
        createHTML(html2) {
          return html2;
        },
        createScriptURL(scriptUrl) {
          return scriptUrl;
        }
      });
    } catch (_) {
      console.warn("TrustedTypes policy " + policyName + " could not be created.");
      return null;
    }
  };
  const _createHooksMap = function _createHooksMap2() {
    return {
      afterSanitizeAttributes: [],
      afterSanitizeElements: [],
      afterSanitizeShadowDOM: [],
      beforeSanitizeAttributes: [],
      beforeSanitizeElements: [],
      beforeSanitizeShadowDOM: [],
      uponSanitizeAttribute: [],
      uponSanitizeElement: [],
      uponSanitizeShadowNode: []
    };
  };
  const _resolveSetOption = function _resolveSetOption2(cfg, key, fallback, options) {
    return objectHasOwnProperty(cfg, key) && arrayIsArray(cfg[key]) ? addToSet(options.base ? clone(options.base) : {}, cfg[key], options.transform) : fallback;
  };
  const _resolveObjectOption = function _resolveObjectOption2(cfg, key, makeFallback) {
    const value = objectHasOwnProperty(cfg, key) ? cfg[key] : void 0;
    return value && typeof value === "object" ? clone(value) : makeFallback();
  };
  function createDOMPurify() {
    let window2 = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : getGlobal();
    const DOMPurify = (root) => createDOMPurify(root);
    DOMPurify.version = "3.4.14";
    DOMPurify.removed = [];
    if (!window2 || !window2.document || window2.document.nodeType !== NODE_TYPE.document || !window2.Element) {
      DOMPurify.isSupported = false;
      return DOMPurify;
    }
    let document2 = window2.document;
    const originalDocument = document2;
    const currentScript = originalDocument.currentScript;
    window2.DocumentFragment;
    const HTMLTemplateElement = window2.HTMLTemplateElement, Node = window2.Node, Element = window2.Element, NodeFilter = window2.NodeFilter, _window$NamedNodeMap = window2.NamedNodeMap;
    _window$NamedNodeMap === void 0 ? window2.NamedNodeMap || window2.MozNamedAttrMap : _window$NamedNodeMap;
    window2.HTMLFormElement;
    const DOMParser = window2.DOMParser, trustedTypes = window2.trustedTypes;
    const ElementPrototype = Element.prototype;
    const cloneNode = lookupGetter(ElementPrototype, "cloneNode");
    const remove = lookupGetter(ElementPrototype, "remove");
    const getNextSibling = lookupGetter(ElementPrototype, "nextSibling");
    const getChildNodes = lookupGetter(ElementPrototype, "childNodes");
    const getParentNode = lookupGetter(ElementPrototype, "parentNode");
    const getShadowRoot = lookupGetter(ElementPrototype, "shadowRoot");
    const getAttributes = lookupGetter(ElementPrototype, "attributes");
    const getNodeType = Node && Node.prototype ? lookupGetter(Node.prototype, "nodeType") : null;
    const getNodeName = Node && Node.prototype ? lookupGetter(Node.prototype, "nodeName") : null;
    const getOwnerDocument = Node && Node.prototype ? lookupGetter(Node.prototype, "ownerDocument") : null;
    const _readNodeType = function _readNodeType2(node) {
      return getNodeType ? getNodeType(node) : node.nodeType;
    };
    const _readNodeName = function _readNodeName2(node) {
      return getNodeName ? getNodeName(node) : node.nodeName;
    };
    if (typeof HTMLTemplateElement === "function") {
      const template = document2.createElement("template");
      if (template.content && template.content.ownerDocument) {
        document2 = template.content.ownerDocument;
      }
    }
    let trustedTypesPolicy;
    let emptyHTML = "";
    let defaultTrustedTypesPolicy;
    let defaultTrustedTypesPolicyResolved = false;
    let IN_TRUSTED_TYPES_POLICY = 0;
    const _assertNotInTrustedTypesPolicy = function _assertNotInTrustedTypesPolicy2() {
      if (IN_TRUSTED_TYPES_POLICY > 0) {
        throw typeErrorCreate('A configured TRUSTED_TYPES_POLICY callback (createHTML or createScriptURL) must not call DOMPurify.sanitize, as that causes infinite recursion. Do not pass a policy whose callbacks wrap DOMPurify as TRUSTED_TYPES_POLICY; see the "DOMPurify and Trusted Types" section of the README.');
      }
    };
    const _createTrustedHTML = function _createTrustedHTML2(html2) {
      _assertNotInTrustedTypesPolicy();
      IN_TRUSTED_TYPES_POLICY++;
      try {
        return trustedTypesPolicy.createHTML(html2);
      } finally {
        IN_TRUSTED_TYPES_POLICY--;
      }
    };
    const _createTrustedScriptURL = function _createTrustedScriptURL2(scriptUrl) {
      _assertNotInTrustedTypesPolicy();
      IN_TRUSTED_TYPES_POLICY++;
      try {
        return trustedTypesPolicy.createScriptURL(scriptUrl);
      } finally {
        IN_TRUSTED_TYPES_POLICY--;
      }
    };
    const _getDefaultTrustedTypesPolicy = function _getDefaultTrustedTypesPolicy2() {
      if (!defaultTrustedTypesPolicyResolved) {
        defaultTrustedTypesPolicy = _createTrustedTypesPolicy(trustedTypes, currentScript);
        defaultTrustedTypesPolicyResolved = true;
      }
      return defaultTrustedTypesPolicy;
    };
    const _document = document2, implementation = _document.implementation, createNodeIterator = _document.createNodeIterator, createDocumentFragment = _document.createDocumentFragment, getElementsByTagName = _document.getElementsByTagName;
    const importNode = originalDocument.importNode;
    let hooks = _createHooksMap();
    DOMPurify.isSupported = typeof entries === "function" && typeof getParentNode === "function" && implementation && implementation.createHTMLDocument !== void 0;
    const MUSTACHE_EXPR$1 = MUSTACHE_EXPR, ERB_EXPR$1 = ERB_EXPR, TMPLIT_EXPR$1 = TMPLIT_EXPR, DATA_ATTR$1 = DATA_ATTR, ARIA_ATTR$1 = ARIA_ATTR, IS_SCRIPT_OR_DATA$1 = IS_SCRIPT_OR_DATA, ATTR_WHITESPACE$1 = ATTR_WHITESPACE, CUSTOM_ELEMENT$1 = CUSTOM_ELEMENT;
    let IS_ALLOWED_URI$1 = IS_ALLOWED_URI;
    let ALLOWED_TAGS = null;
    const DEFAULT_ALLOWED_TAGS = addToSet({}, [...html$1, ...svg$1, ...svgFilters, ...mathMl$1, ...text]);
    let ALLOWED_ATTR = null;
    const DEFAULT_ALLOWED_ATTR = addToSet({}, [...html, ...svg, ...mathMl, ...xml]);
    let CUSTOM_ELEMENT_HANDLING = Object.seal(create(null, {
      tagNameCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      },
      attributeNameCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      },
      allowCustomizedBuiltInElements: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: false
      }
    }));
    let FORBID_TAGS = null;
    let FORBID_ATTR = null;
    const EXTRA_ELEMENT_HANDLING = Object.seal(create(null, {
      tagCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      },
      attributeCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      }
    }));
    let ALLOW_ARIA_ATTR = true;
    let ALLOW_DATA_ATTR = true;
    let ALLOW_UNKNOWN_PROTOCOLS = false;
    let ALLOW_SELF_CLOSE_IN_ATTR = true;
    let SAFE_FOR_TEMPLATES = false;
    let SAFE_FOR_XML = true;
    let WHOLE_DOCUMENT = false;
    let SET_CONFIG = false;
    let SET_CONFIG_ALLOWED_TAGS = null;
    let SET_CONFIG_ALLOWED_ATTR = null;
    let FORCE_BODY = false;
    let RETURN_DOM = false;
    let RETURN_DOM_FRAGMENT = false;
    let RETURN_TRUSTED_TYPE = false;
    let SANITIZE_DOM = true;
    let SANITIZE_NAMED_PROPS = false;
    const SANITIZE_NAMED_PROPS_PREFIX = "user-content-";
    let KEEP_CONTENT = true;
    let IN_PLACE = false;
    let USE_PROFILES = {};
    let FORBID_CONTENTS = null;
    const DEFAULT_FORBID_CONTENTS = addToSet({}, [
      "annotation-xml",
      "audio",
      "colgroup",
      "desc",
      "foreignobject",
      "head",
      "iframe",
      "math",
      "mi",
      "mn",
      "mo",
      "ms",
      "mtext",
      "noembed",
      "noframes",
      "noscript",
      "plaintext",
      "script",
      // <selectedcontent> mirrors the selected <option>'s subtree, cloned by
      // the UA (customizable <select>) — including any on* handlers — and the
      // engine re-mirrors synchronously whenever a removal changes which
      // option/selectedcontent is current, even inside DOMPurify's inert
      // DOMParser document. Hoisting its children on removal re-inserts a fresh
      // mirror target ahead of the walk, which the engine refills, looping
      // forever (DoS) and amplifying output. Dropping its content on removal
      // (rather than hoisting) breaks that cascade; the content is a duplicate
      // of the option, which is sanitized on its own. See campaign-3 F1/F6.
      "selectedcontent",
      "style",
      "svg",
      "template",
      "thead",
      "title",
      "video",
      "xmp"
    ]);
    let DATA_URI_TAGS = null;
    const DEFAULT_DATA_URI_TAGS = addToSet({}, ["audio", "video", "img", "source", "image", "track"]);
    let URI_SAFE_ATTRIBUTES = null;
    const DEFAULT_URI_SAFE_ATTRIBUTES = addToSet({}, ["alt", "class", "for", "id", "label", "name", "pattern", "placeholder", "role", "summary", "title", "value", "style", "xmlns"]);
    const MATHML_NAMESPACE = "http://www.w3.org/1998/Math/MathML";
    const SVG_NAMESPACE = "http://www.w3.org/2000/svg";
    const HTML_NAMESPACE = "http://www.w3.org/1999/xhtml";
    let NAMESPACE = HTML_NAMESPACE;
    let IS_EMPTY_INPUT = false;
    let ALLOWED_NAMESPACES = null;
    const DEFAULT_ALLOWED_NAMESPACES = addToSet({}, [MATHML_NAMESPACE, SVG_NAMESPACE, HTML_NAMESPACE], stringToString);
    const DEFAULT_MATHML_TEXT_INTEGRATION_POINTS = freeze(["mi", "mo", "mn", "ms", "mtext"]);
    let MATHML_TEXT_INTEGRATION_POINTS = addToSet({}, DEFAULT_MATHML_TEXT_INTEGRATION_POINTS);
    const DEFAULT_HTML_INTEGRATION_POINTS = freeze(["annotation-xml"]);
    let HTML_INTEGRATION_POINTS = addToSet({}, DEFAULT_HTML_INTEGRATION_POINTS);
    const COMMON_SVG_AND_HTML_ELEMENTS = addToSet({}, ["title", "style", "font", "a", "script"]);
    let PARSER_MEDIA_TYPE = null;
    const SUPPORTED_PARSER_MEDIA_TYPES = ["application/xhtml+xml", "text/html"];
    const DEFAULT_PARSER_MEDIA_TYPE = "text/html";
    let transformCaseFunc = null;
    let CONFIG = null;
    const formElement = document2.createElement("form");
    const isRegexOrFunction = function isRegexOrFunction2(testValue) {
      return testValue instanceof RegExp || testValue instanceof Function;
    };
    const _parseConfig = function _parseConfig2() {
      let cfg = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
      if (CONFIG && CONFIG === cfg) {
        return;
      }
      if (!cfg || typeof cfg !== "object") {
        cfg = {};
      }
      cfg = clone(cfg);
      PARSER_MEDIA_TYPE = // eslint-disable-next-line unicorn/prefer-includes
      SUPPORTED_PARSER_MEDIA_TYPES.indexOf(cfg.PARSER_MEDIA_TYPE) === -1 ? DEFAULT_PARSER_MEDIA_TYPE : cfg.PARSER_MEDIA_TYPE;
      transformCaseFunc = PARSER_MEDIA_TYPE === "application/xhtml+xml" ? stringToString : stringToLowerCase;
      ALLOWED_TAGS = _resolveSetOption(cfg, "ALLOWED_TAGS", DEFAULT_ALLOWED_TAGS, {
        transform: transformCaseFunc
      });
      ALLOWED_ATTR = _resolveSetOption(cfg, "ALLOWED_ATTR", DEFAULT_ALLOWED_ATTR, {
        transform: transformCaseFunc
      });
      ALLOWED_NAMESPACES = _resolveSetOption(cfg, "ALLOWED_NAMESPACES", DEFAULT_ALLOWED_NAMESPACES, {
        transform: stringToString
      });
      URI_SAFE_ATTRIBUTES = _resolveSetOption(cfg, "ADD_URI_SAFE_ATTR", DEFAULT_URI_SAFE_ATTRIBUTES, {
        transform: transformCaseFunc,
        base: DEFAULT_URI_SAFE_ATTRIBUTES
      });
      DATA_URI_TAGS = _resolveSetOption(cfg, "ADD_DATA_URI_TAGS", DEFAULT_DATA_URI_TAGS, {
        transform: transformCaseFunc,
        base: DEFAULT_DATA_URI_TAGS
      });
      FORBID_CONTENTS = _resolveSetOption(cfg, "FORBID_CONTENTS", DEFAULT_FORBID_CONTENTS, {
        transform: transformCaseFunc
      });
      FORBID_TAGS = _resolveSetOption(cfg, "FORBID_TAGS", clone({}), {
        transform: transformCaseFunc
      });
      FORBID_ATTR = _resolveSetOption(cfg, "FORBID_ATTR", clone({}), {
        transform: transformCaseFunc
      });
      USE_PROFILES = objectHasOwnProperty(cfg, "USE_PROFILES") ? cfg.USE_PROFILES && typeof cfg.USE_PROFILES === "object" ? clone(cfg.USE_PROFILES) : cfg.USE_PROFILES : false;
      ALLOW_ARIA_ATTR = cfg.ALLOW_ARIA_ATTR !== false;
      ALLOW_DATA_ATTR = cfg.ALLOW_DATA_ATTR !== false;
      ALLOW_UNKNOWN_PROTOCOLS = cfg.ALLOW_UNKNOWN_PROTOCOLS || false;
      ALLOW_SELF_CLOSE_IN_ATTR = cfg.ALLOW_SELF_CLOSE_IN_ATTR !== false;
      SAFE_FOR_TEMPLATES = cfg.SAFE_FOR_TEMPLATES || false;
      SAFE_FOR_XML = cfg.SAFE_FOR_XML !== false;
      WHOLE_DOCUMENT = cfg.WHOLE_DOCUMENT || false;
      RETURN_DOM = cfg.RETURN_DOM || false;
      RETURN_DOM_FRAGMENT = cfg.RETURN_DOM_FRAGMENT || false;
      RETURN_TRUSTED_TYPE = cfg.RETURN_TRUSTED_TYPE || false;
      FORCE_BODY = cfg.FORCE_BODY || false;
      SANITIZE_DOM = cfg.SANITIZE_DOM !== false;
      SANITIZE_NAMED_PROPS = cfg.SANITIZE_NAMED_PROPS || false;
      KEEP_CONTENT = cfg.KEEP_CONTENT !== false;
      IN_PLACE = cfg.IN_PLACE || false;
      IS_ALLOWED_URI$1 = isRegex(cfg.ALLOWED_URI_REGEXP) ? cfg.ALLOWED_URI_REGEXP : IS_ALLOWED_URI;
      NAMESPACE = typeof cfg.NAMESPACE === "string" ? cfg.NAMESPACE : HTML_NAMESPACE;
      MATHML_TEXT_INTEGRATION_POINTS = _resolveObjectOption(
        cfg,
        "MATHML_TEXT_INTEGRATION_POINTS",
        () => addToSet({}, DEFAULT_MATHML_TEXT_INTEGRATION_POINTS)
        // Default built-in map
      );
      HTML_INTEGRATION_POINTS = _resolveObjectOption(
        cfg,
        "HTML_INTEGRATION_POINTS",
        () => addToSet({}, DEFAULT_HTML_INTEGRATION_POINTS)
        // Default built-in map
      );
      const customElementHandling = _resolveObjectOption(cfg, "CUSTOM_ELEMENT_HANDLING", () => create(null));
      CUSTOM_ELEMENT_HANDLING = create(null);
      if (objectHasOwnProperty(customElementHandling, "tagNameCheck") && isRegexOrFunction(customElementHandling.tagNameCheck)) {
        CUSTOM_ELEMENT_HANDLING.tagNameCheck = customElementHandling.tagNameCheck;
      }
      if (objectHasOwnProperty(customElementHandling, "attributeNameCheck") && isRegexOrFunction(customElementHandling.attributeNameCheck)) {
        CUSTOM_ELEMENT_HANDLING.attributeNameCheck = customElementHandling.attributeNameCheck;
      }
      if (objectHasOwnProperty(customElementHandling, "allowCustomizedBuiltInElements") && typeof customElementHandling.allowCustomizedBuiltInElements === "boolean") {
        CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements = customElementHandling.allowCustomizedBuiltInElements;
      }
      seal(CUSTOM_ELEMENT_HANDLING);
      if (SAFE_FOR_TEMPLATES) {
        ALLOW_DATA_ATTR = false;
      }
      if (RETURN_DOM_FRAGMENT) {
        RETURN_DOM = true;
      }
      if (USE_PROFILES) {
        ALLOWED_TAGS = addToSet({}, text);
        ALLOWED_ATTR = create(null);
        if (USE_PROFILES.html === true) {
          addToSet(ALLOWED_TAGS, html$1);
          addToSet(ALLOWED_ATTR, html);
        }
        if (USE_PROFILES.svg === true) {
          addToSet(ALLOWED_TAGS, svg$1);
          addToSet(ALLOWED_ATTR, svg);
          addToSet(ALLOWED_ATTR, xml);
        }
        if (USE_PROFILES.svgFilters === true) {
          addToSet(ALLOWED_TAGS, svgFilters);
          addToSet(ALLOWED_ATTR, svg);
          addToSet(ALLOWED_ATTR, xml);
        }
        if (USE_PROFILES.mathMl === true) {
          addToSet(ALLOWED_TAGS, mathMl$1);
          addToSet(ALLOWED_ATTR, mathMl);
          addToSet(ALLOWED_ATTR, xml);
        }
      }
      EXTRA_ELEMENT_HANDLING.tagCheck = null;
      EXTRA_ELEMENT_HANDLING.attributeCheck = null;
      if (objectHasOwnProperty(cfg, "ADD_TAGS")) {
        if (typeof cfg.ADD_TAGS === "function") {
          EXTRA_ELEMENT_HANDLING.tagCheck = cfg.ADD_TAGS;
        } else if (arrayIsArray(cfg.ADD_TAGS)) {
          if (ALLOWED_TAGS === DEFAULT_ALLOWED_TAGS) {
            ALLOWED_TAGS = clone(ALLOWED_TAGS);
          }
          addToSet(ALLOWED_TAGS, cfg.ADD_TAGS, transformCaseFunc);
        }
      }
      if (objectHasOwnProperty(cfg, "ADD_ATTR")) {
        if (typeof cfg.ADD_ATTR === "function") {
          EXTRA_ELEMENT_HANDLING.attributeCheck = cfg.ADD_ATTR;
        } else if (arrayIsArray(cfg.ADD_ATTR)) {
          if (ALLOWED_ATTR === DEFAULT_ALLOWED_ATTR) {
            ALLOWED_ATTR = clone(ALLOWED_ATTR);
          }
          addToSet(ALLOWED_ATTR, cfg.ADD_ATTR, transformCaseFunc);
        }
      }
      if (objectHasOwnProperty(cfg, "ADD_FORBID_CONTENTS") && arrayIsArray(cfg.ADD_FORBID_CONTENTS)) {
        if (FORBID_CONTENTS === DEFAULT_FORBID_CONTENTS) {
          FORBID_CONTENTS = clone(FORBID_CONTENTS);
        }
        addToSet(FORBID_CONTENTS, cfg.ADD_FORBID_CONTENTS, transformCaseFunc);
      }
      if (KEEP_CONTENT) {
        ALLOWED_TAGS["#text"] = true;
      }
      if (WHOLE_DOCUMENT) {
        addToSet(ALLOWED_TAGS, ["html", "head", "body"]);
      }
      if (ALLOWED_TAGS.table) {
        addToSet(ALLOWED_TAGS, ["tbody"]);
        delete FORBID_TAGS.tbody;
      }
      if (cfg.TRUSTED_TYPES_POLICY) {
        if (typeof cfg.TRUSTED_TYPES_POLICY.createHTML !== "function") {
          throw typeErrorCreate('TRUSTED_TYPES_POLICY configuration option must provide a "createHTML" hook.');
        }
        if (typeof cfg.TRUSTED_TYPES_POLICY.createScriptURL !== "function") {
          throw typeErrorCreate('TRUSTED_TYPES_POLICY configuration option must provide a "createScriptURL" hook.');
        }
        const previousTrustedTypesPolicy = trustedTypesPolicy;
        trustedTypesPolicy = cfg.TRUSTED_TYPES_POLICY;
        try {
          emptyHTML = _createTrustedHTML("");
        } catch (error) {
          trustedTypesPolicy = previousTrustedTypesPolicy;
          throw error;
        }
      } else if (cfg.TRUSTED_TYPES_POLICY === null) {
        trustedTypesPolicy = void 0;
        emptyHTML = "";
      } else {
        if (trustedTypesPolicy === void 0) {
          trustedTypesPolicy = _getDefaultTrustedTypesPolicy();
        }
        if (trustedTypesPolicy && typeof emptyHTML === "string") {
          emptyHTML = _createTrustedHTML("");
        }
      }
      if (freeze) {
        freeze(cfg);
      }
      CONFIG = cfg;
    };
    const ALL_SVG_TAGS = addToSet({}, [...svg$1, ...svgFilters, ...svgDisallowed]);
    const ALL_MATHML_TAGS = addToSet({}, [...mathMl$1, ...mathMlDisallowed]);
    const _checkSvgNamespace = function _checkSvgNamespace2(tagName, parent, parentTagName) {
      if (parent.namespaceURI === HTML_NAMESPACE) {
        return tagName === "svg";
      }
      if (parent.namespaceURI === MATHML_NAMESPACE) {
        return tagName === "svg" && (parentTagName === "annotation-xml" || MATHML_TEXT_INTEGRATION_POINTS[parentTagName]);
      }
      return Boolean(ALL_SVG_TAGS[tagName]);
    };
    const _checkMathMlNamespace = function _checkMathMlNamespace2(tagName, parent, parentTagName) {
      if (parent.namespaceURI === HTML_NAMESPACE) {
        return tagName === "math";
      }
      if (parent.namespaceURI === SVG_NAMESPACE) {
        return tagName === "math" && HTML_INTEGRATION_POINTS[parentTagName];
      }
      return Boolean(ALL_MATHML_TAGS[tagName]);
    };
    const _checkHtmlNamespace = function _checkHtmlNamespace2(tagName, parent, parentTagName) {
      if (parent.namespaceURI === SVG_NAMESPACE && !HTML_INTEGRATION_POINTS[parentTagName]) {
        return false;
      }
      if (parent.namespaceURI === MATHML_NAMESPACE && !MATHML_TEXT_INTEGRATION_POINTS[parentTagName]) {
        return false;
      }
      return !ALL_MATHML_TAGS[tagName] && (COMMON_SVG_AND_HTML_ELEMENTS[tagName] || !ALL_SVG_TAGS[tagName]);
    };
    const _checkValidNamespace = function _checkValidNamespace2(element) {
      let parent = getParentNode(element);
      if (!parent || !parent.tagName) {
        parent = {
          namespaceURI: NAMESPACE,
          tagName: "template"
        };
      }
      const tagName = stringToLowerCase(element.tagName);
      const parentTagName = stringToLowerCase(parent.tagName);
      if (!ALLOWED_NAMESPACES[element.namespaceURI]) {
        return false;
      }
      if (element.namespaceURI === SVG_NAMESPACE) {
        return _checkSvgNamespace(tagName, parent, parentTagName);
      }
      if (element.namespaceURI === MATHML_NAMESPACE) {
        return _checkMathMlNamespace(tagName, parent, parentTagName);
      }
      if (element.namespaceURI === HTML_NAMESPACE) {
        return _checkHtmlNamespace(tagName, parent, parentTagName);
      }
      if (PARSER_MEDIA_TYPE === "application/xhtml+xml" && ALLOWED_NAMESPACES[element.namespaceURI]) {
        return true;
      }
      return false;
    };
    const _forceRemove = function _forceRemove2(node) {
      arrayPush(DOMPurify.removed, {
        element: node
      });
      try {
        getParentNode(node).removeChild(node);
      } catch (_) {
        remove(node);
        if (!getParentNode(node)) {
          throw typeErrorCreate("a node selected for removal could not be detached from its tree and cannot be safely returned; refusing to sanitize in place");
        }
      }
    };
    const _stripAttributeNode = function _stripAttributeNode2(element, attribute, name) {
      try {
        element.removeAttributeNode(attribute);
      } catch (_) {
        try {
          element.removeAttribute(name);
        } catch (_2) {
        }
      }
    };
    const _neutralizeRoot = function _neutralizeRoot2(root) {
      _neutralizeSubtree(root);
      const childNodes = getChildNodes(root);
      if (childNodes) {
        const snapshot = [];
        arrayForEach(childNodes, (child) => {
          arrayPush(snapshot, child);
        });
        arrayForEach(snapshot, (child) => {
          try {
            remove(child);
          } catch (_) {
          }
        });
      }
      const attributes = getAttributes(root);
      if (attributes) {
        for (let i = attributes.length - 1; i >= 0; --i) {
          const attribute = attributes[i];
          const name = attribute && attribute.name;
          if (typeof name === "string") {
            _stripAttributeNode(root, attribute, name);
          }
        }
      }
    };
    const _removeAttribute = function _removeAttribute2(name, element, attr) {
      if (!attr) {
        try {
          attr = element.getAttributeNode(name);
        } catch (_) {
          attr = null;
        }
      }
      arrayPush(DOMPurify.removed, {
        attribute: attr || null,
        from: element
      });
      try {
        if (attr) {
          element.removeAttributeNode(attr);
        } else {
          element.removeAttribute(name);
        }
      } catch (_) {
        try {
          element.removeAttribute(name);
        } catch (_2) {
        }
      }
      if (name === "is") {
        if (RETURN_DOM || RETURN_DOM_FRAGMENT) {
          try {
            _forceRemove(element);
          } catch (_) {
          }
        } else {
          try {
            element.setAttribute(name, "");
          } catch (_) {
          }
        }
      }
    };
    const _stripDisallowedAttributes = function _stripDisallowedAttributes2(element) {
      const attributes = getAttributes(element);
      if (!attributes) {
        return;
      }
      for (let i = attributes.length - 1; i >= 0; --i) {
        const attribute = attributes[i];
        const name = attribute && attribute.name;
        if (typeof name !== "string" || ALLOWED_ATTR[transformCaseFunc(name)]) {
          continue;
        }
        _stripAttributeNode(element, attribute, name);
      }
    };
    const _neutralizeSubtree = function _neutralizeSubtree2(root) {
      const stack = [root];
      while (stack.length > 0) {
        const node = stack.pop();
        const nodeType = _readNodeType(node);
        if (nodeType === NODE_TYPE.element) {
          _stripDisallowedAttributes(node);
        }
        const childNodes = getChildNodes(node);
        if (childNodes) {
          for (let i = childNodes.length - 1; i >= 0; --i) {
            stack.push(childNodes[i]);
          }
        }
      }
    };
    const _isPatchLinkageAttribute = function _isPatchLinkageAttribute2(lcName, lcTag) {
      if (!SAFE_FOR_XML) {
        return false;
      }
      if (lcName === "patchsrc") {
        return true;
      }
      return lcName === "for" && lcTag !== "label" && lcTag !== "output";
    };
    const _neutralizePatchLinkage = function _neutralizePatchLinkage2(root) {
      if (!SAFE_FOR_XML) {
        return;
      }
      const stack = [root];
      while (stack.length > 0) {
        const node = stack.pop();
        const nodeType = _readNodeType(node);
        if (nodeType === NODE_TYPE.processingInstruction || nodeType === NODE_TYPE.comment && regExpTest(COMMENT_MARKUP_PROBE, node.data)) {
          try {
            remove(node);
          } catch (_) {
          }
          continue;
        }
        if (nodeType === NODE_TYPE.element) {
          const element = node;
          const lcTag = transformCaseFunc(_readNodeName(node));
          try {
            if (element.hasAttribute && element.hasAttribute("patchsrc")) {
              element.removeAttribute("patchsrc");
            }
            if (element.hasAttribute && element.hasAttribute("for") && _isPatchLinkageAttribute("for", lcTag)) {
              element.removeAttribute("for");
            }
          } catch (_) {
          }
        }
        const childNodes = getChildNodes(node);
        if (childNodes) {
          for (let i = childNodes.length - 1; i >= 0; --i) {
            stack.push(childNodes[i]);
          }
        }
      }
    };
    const _initDocument = function _initDocument2(dirty) {
      let doc = null;
      let leadingWhitespace = null;
      if (FORCE_BODY) {
        dirty = "<remove></remove>" + dirty;
      } else {
        const matches = stringMatch(dirty, /^[\r\n\t ]+/);
        leadingWhitespace = matches && matches[0];
      }
      if (PARSER_MEDIA_TYPE === "application/xhtml+xml" && NAMESPACE === HTML_NAMESPACE) {
        dirty = '<html xmlns="http://www.w3.org/1999/xhtml"><head></head><body>' + dirty + "</body></html>";
      }
      const dirtyPayload = trustedTypesPolicy ? _createTrustedHTML(dirty) : dirty;
      if (NAMESPACE === HTML_NAMESPACE) {
        try {
          doc = new DOMParser().parseFromString(dirtyPayload, PARSER_MEDIA_TYPE);
        } catch (_) {
        }
      }
      if (!doc || !doc.documentElement) {
        doc = implementation.createDocument(NAMESPACE, "template", null);
        try {
          doc.documentElement.innerHTML = IS_EMPTY_INPUT ? emptyHTML : dirtyPayload;
        } catch (_) {
        }
      }
      const body = doc.body || doc.documentElement;
      if (dirty && leadingWhitespace) {
        body.insertBefore(document2.createTextNode(leadingWhitespace), body.childNodes[0] || null);
      }
      if (NAMESPACE === HTML_NAMESPACE) {
        return getElementsByTagName.call(doc, WHOLE_DOCUMENT ? "html" : "body")[0];
      }
      return WHOLE_DOCUMENT ? doc.documentElement : body;
    };
    const _createNodeIterator = function _createNodeIterator2(root) {
      const doc = getOwnerDocument ? getOwnerDocument(root) : root.ownerDocument;
      return createNodeIterator.call(
        doc || root,
        root,
        // eslint-disable-next-line no-bitwise
        NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_COMMENT | NodeFilter.SHOW_TEXT | NodeFilter.SHOW_PROCESSING_INSTRUCTION | NodeFilter.SHOW_CDATA_SECTION,
        null
      );
    };
    const _stripTemplateExpressions = function _stripTemplateExpressions2(value) {
      value = stringReplace(value, MUSTACHE_EXPR$1, " ");
      value = stringReplace(value, ERB_EXPR$1, " ");
      value = stringReplace(value, TMPLIT_EXPR$1, " ");
      return value;
    };
    const _scrubTemplateExpressions2 = function _scrubTemplateExpressions(node) {
      var _node$querySelectorAl;
      node.normalize();
      const doc = getOwnerDocument ? getOwnerDocument(node) : node.ownerDocument;
      const walker = createNodeIterator.call(
        doc || node,
        node,
        // eslint-disable-next-line no-bitwise
        NodeFilter.SHOW_TEXT | NodeFilter.SHOW_COMMENT | NodeFilter.SHOW_CDATA_SECTION | NodeFilter.SHOW_PROCESSING_INSTRUCTION,
        null
      );
      let currentNode = walker.nextNode();
      while (currentNode) {
        currentNode.data = _stripTemplateExpressions(currentNode.data);
        currentNode = walker.nextNode();
      }
      const templates = (_node$querySelectorAl = node.querySelectorAll) === null || _node$querySelectorAl === void 0 ? void 0 : _node$querySelectorAl.call(node, "template");
      if (templates) {
        arrayForEach(templates, (tmpl) => {
          if (_isDocumentFragment(tmpl.content)) {
            _scrubTemplateExpressions2(tmpl.content);
          }
        });
      }
    };
    const _isClobbered = function _isClobbered2(element) {
      const realTagName = getNodeName ? getNodeName(element) : null;
      if (typeof realTagName !== "string") {
        return false;
      }
      if (transformCaseFunc(realTagName) !== "form") {
        return false;
      }
      return typeof element.nodeName !== "string" || typeof element.textContent !== "string" || typeof element.removeChild !== "function" || // Realm-safe NamedNodeMap detection: equality against the cached
      // prototype getter. Clobbered .attributes (e.g. <input name="attributes">)
      // makes the direct read diverge from the cached read; a clean form
      // (same-realm OR foreign-realm) has both reads pointing at the same
      // canonical NamedNodeMap.
      element.attributes !== getAttributes(element) || typeof element.removeAttribute !== "function" || typeof element.setAttribute !== "function" || typeof element.namespaceURI !== "string" || typeof element.insertBefore !== "function" || typeof element.hasChildNodes !== "function" || // NodeType clobbering probe. Cached Node.prototype.nodeType getter
      // returns the integer 1 for any Element regardless of realm; direct
      // read on a clobbered form (e.g. <input name="nodeType">) returns
      // the named child element. Cheap addition — nodeType is read from
      // an internal slot, no serialization cost — and removes a residual
      // clobbering surface used by several mXSS / PI / comment branches
      // in _sanitizeElements that compare currentNode.nodeType directly.
      element.nodeType !== getNodeType(element) || // HTMLFormElement has [LegacyOverrideBuiltIns]: a descendant named
      // "childNodes" shadows the prototype getter. Direct reads of
      // form.childNodes from a clobbered form return the named child
      // instead of the real NodeList, so any walk that reads it directly
      // skips the form's real children. Compare the direct read to the
      // cached Node.prototype getter — when the form's named-property
      // getter intercepts the read, the two values differ and we flag
      // the form. This catches every clobbering child type (input,
      // select, etc.) regardless of whether the named child happens to
      // carry a numeric .length, which a typeof-based probe would miss
      // (e.g. HTMLSelectElement.length is a defined unsigned-long).
      element.childNodes !== getChildNodes(element);
    };
    const _isDocumentFragment = function _isDocumentFragment2(value) {
      if (!getNodeType || typeof value !== "object" || value === null) {
        return false;
      }
      try {
        return getNodeType(value) === NODE_TYPE.documentFragment;
      } catch (_) {
        return false;
      }
    };
    const _isNode = function _isNode2(value) {
      if (!getNodeType || typeof value !== "object" || value === null) {
        return false;
      }
      try {
        return typeof getNodeType(value) === "number";
      } catch (_) {
        return false;
      }
    };
    function _executeHooks(hooks2, currentNode, data) {
      if (hooks2.length === 0) {
        return;
      }
      arrayForEach(hooks2, (hook) => {
        hook.call(DOMPurify, currentNode, data, CONFIG);
      });
    }
    const _isUnsafeNode = function _isUnsafeNode2(currentNode, tagName) {
      if (SAFE_FOR_XML && currentNode.hasChildNodes() && !_isNode(currentNode.firstElementChild) && regExpTest(ELEMENT_MARKUP_PROBE, currentNode.textContent) && regExpTest(ELEMENT_MARKUP_PROBE, currentNode.innerHTML)) {
        return true;
      }
      if (SAFE_FOR_XML && currentNode.namespaceURI === HTML_NAMESPACE && LITERAL_TEXT_ELEMENTS[tagName] && (_isNode(currentNode.firstElementChild) || typeof currentNode.textContent === "string" && regExpTest(LITERAL_TEXT_CLOSE[tagName], currentNode.textContent))) {
        return true;
      }
      if (currentNode.nodeType === NODE_TYPE.processingInstruction) {
        return true;
      }
      if (SAFE_FOR_XML && currentNode.nodeType === NODE_TYPE.comment && regExpTest(COMMENT_MARKUP_PROBE, currentNode.data)) {
        return true;
      }
      return false;
    };
    const _matchesNameCheck = function _matchesNameCheck2(check, name) {
      if (check instanceof RegExp) {
        return regExpTest(check, name);
      }
      if (check instanceof Function) {
        for (var _len = arguments.length, args = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
          args[_key - 2] = arguments[_key];
        }
        return Boolean(check(name, ...args));
      }
      return false;
    };
    const _sanitizeDisallowedNode = function _sanitizeDisallowedNode2(currentNode, tagName, root) {
      if (!FORBID_TAGS[tagName] && _isBasicCustomElement(tagName) && _matchesNameCheck(CUSTOM_ELEMENT_HANDLING.tagNameCheck, tagName)) {
        return false;
      }
      if (KEEP_CONTENT && !FORBID_CONTENTS[tagName]) {
        const parentNode = getParentNode(currentNode);
        const childNodes = getChildNodes(currentNode);
        if (childNodes && parentNode) {
          const childCount = childNodes.length;
          for (let i = childCount - 1; i >= 0; --i) {
            const hoisted = currentNode === root ? cloneNode(childNodes[i], true) : childNodes[i];
            parentNode.insertBefore(hoisted, getNextSibling(currentNode));
          }
        }
      }
      _forceRemove(currentNode);
      return true;
    };
    const _forkSharedAllowlist = function _forkSharedAllowlist2(hookList, set, defaultSet, setConfigSet) {
      if (hookList.length === 0) {
        return set;
      }
      return set === defaultSet || set === setConfigSet ? clone(set) : set;
    };
    const _handleHookDetachedNode = function _handleHookDetachedNode2(currentNode, root) {
      if (currentNode === root || getParentNode(currentNode) !== null) {
        return false;
      }
      if (IN_PLACE) {
        _neutralizeSubtree(currentNode);
      }
      return true;
    };
    const _sanitizeElements = function _sanitizeElements2(currentNode, root) {
      _executeHooks(hooks.beforeSanitizeElements, currentNode, null);
      if (_handleHookDetachedNode(currentNode, root)) {
        return true;
      }
      if (_isClobbered(currentNode)) {
        _forceRemove(currentNode);
        return true;
      }
      const tagName = transformCaseFunc(_readNodeName(currentNode));
      ALLOWED_TAGS = _forkSharedAllowlist(hooks.uponSanitizeElement, ALLOWED_TAGS, DEFAULT_ALLOWED_TAGS, SET_CONFIG_ALLOWED_TAGS);
      _executeHooks(hooks.uponSanitizeElement, currentNode, {
        tagName,
        allowedTags: ALLOWED_TAGS
      });
      if (_handleHookDetachedNode(currentNode, root)) {
        return true;
      }
      if (_isUnsafeNode(currentNode, tagName)) {
        _forceRemove(currentNode);
        return true;
      }
      if (FORBID_TAGS[tagName] || !(EXTRA_ELEMENT_HANDLING.tagCheck instanceof Function && EXTRA_ELEMENT_HANDLING.tagCheck(tagName)) && !ALLOWED_TAGS[tagName]) {
        const removed = _sanitizeDisallowedNode(currentNode, tagName, root);
        if (removed === false) {
          _executeHooks(hooks.afterSanitizeElements, currentNode, null);
        }
        return removed;
      }
      const nt = _readNodeType(currentNode);
      if (nt === NODE_TYPE.element && !_checkValidNamespace(currentNode)) {
        _forceRemove(currentNode);
        return true;
      }
      if ((tagName === "noscript" || tagName === "noembed" || tagName === "noframes") && regExpTest(FALLBACK_TAG_CLOSE, currentNode.innerHTML)) {
        _forceRemove(currentNode);
        return true;
      }
      if (SAFE_FOR_TEMPLATES && currentNode.nodeType === NODE_TYPE.text) {
        const content = _stripTemplateExpressions(currentNode.textContent);
        if (currentNode.textContent !== content) {
          arrayPush(DOMPurify.removed, {
            element: currentNode.cloneNode()
          });
          currentNode.textContent = content;
        }
      }
      _executeHooks(hooks.afterSanitizeElements, currentNode, null);
      return false;
    };
    const _isValidAttribute = function _isValidAttribute2(lcTag, lcName, value) {
      if (FORBID_ATTR[lcName]) {
        return false;
      }
      if (_isPatchLinkageAttribute(lcName, lcTag)) {
        return false;
      }
      if (SANITIZE_DOM && (lcName === "id" || lcName === "name") && (value in document2 || value in formElement)) {
        return false;
      }
      const nameIsPermitted = ALLOWED_ATTR[lcName] || EXTRA_ELEMENT_HANDLING.attributeCheck instanceof Function && EXTRA_ELEMENT_HANDLING.attributeCheck(lcName, lcTag);
      if (ALLOW_DATA_ATTR && regExpTest(DATA_ATTR$1, lcName)) {
        return true;
      }
      if (ALLOW_ARIA_ATTR && regExpTest(ARIA_ATTR$1, lcName)) {
        return true;
      }
      if (!nameIsPermitted) {
        return (
          // Condition a) covers a basically valid custom element tag name whose
          // tag passes the configured tagNameCheck and whose attribute name
          // passes the configured attributeNameCheck ...
          _isBasicCustomElement(lcTag) && _matchesNameCheck(CUSTOM_ELEMENT_HANDLING.tagNameCheck, lcTag) && _matchesNameCheck(CUSTOM_ELEMENT_HANDLING.attributeNameCheck, lcName, lcTag) || // Condition b) covers an `is` attribute whose value passes the
          // configured tagNameCheck while customized built-in elements are
          // allowed.
          lcName === "is" && CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements && _matchesNameCheck(CUSTOM_ELEMENT_HANDLING.tagNameCheck, value)
        );
      }
      if (URI_SAFE_ATTRIBUTES[lcName]) {
        return true;
      }
      if (regExpTest(IS_ALLOWED_URI$1, stringReplace(value, ATTR_WHITESPACE$1, ""))) {
        return true;
      }
      if ((lcName === "src" || lcName === "xlink:href" || lcName === "href") && lcTag !== "script" && stringIndexOf(value, "data:") === 0 && DATA_URI_TAGS[lcTag]) {
        return true;
      }
      if (ALLOW_UNKNOWN_PROTOCOLS && !regExpTest(IS_SCRIPT_OR_DATA$1, stringReplace(value, ATTR_WHITESPACE$1, ""))) {
        return true;
      }
      return !value;
    };
    const RESERVED_CUSTOM_ELEMENT_NAMES = addToSet({}, ["annotation-xml", "color-profile", "font-face", "font-face-format", "font-face-name", "font-face-src", "font-face-uri", "missing-glyph"]);
    const _isBasicCustomElement = function _isBasicCustomElement2(tagName) {
      return !RESERVED_CUSTOM_ELEMENT_NAMES[stringToLowerCase(tagName)] && regExpTest(CUSTOM_ELEMENT$1, tagName);
    };
    const _applyTrustedTypesToAttribute = function _applyTrustedTypesToAttribute2(lcTag, lcName, namespaceURI, value) {
      if (trustedTypesPolicy && typeof trustedTypes === "object" && typeof trustedTypes.getAttributeType === "function" && !namespaceURI) {
        switch (trustedTypes.getAttributeType(lcTag, lcName)) {
          case "TrustedHTML": {
            return _createTrustedHTML(value);
          }
          case "TrustedScriptURL": {
            return _createTrustedScriptURL(value);
          }
        }
      }
      return value;
    };
    const _setAttributeValue = function _setAttributeValue2(currentNode, name, namespaceURI, value) {
      try {
        if (namespaceURI) {
          currentNode.setAttributeNS(namespaceURI, name, value);
        } else {
          currentNode.setAttribute(name, value);
        }
        if (_isClobbered(currentNode)) {
          _forceRemove(currentNode);
        } else {
          arrayPop(DOMPurify.removed);
        }
      } catch (_) {
        _removeAttribute(name, currentNode);
      }
    };
    const _sanitizeAttributes = function _sanitizeAttributes2(currentNode) {
      _executeHooks(hooks.beforeSanitizeAttributes, currentNode, null);
      const attributes = currentNode.attributes;
      if (!attributes || _isClobbered(currentNode)) {
        return;
      }
      ALLOWED_ATTR = _forkSharedAllowlist(hooks.uponSanitizeAttribute, ALLOWED_ATTR, DEFAULT_ALLOWED_ATTR, SET_CONFIG_ALLOWED_ATTR);
      const hookEvent = {
        attrName: "",
        attrValue: "",
        keepAttr: true,
        allowedAttributes: ALLOWED_ATTR,
        forceKeepAttr: void 0
      };
      let l = attributes.length;
      const lcTag = transformCaseFunc(currentNode.nodeName);
      while (l--) {
        const attr = attributes[l];
        const name = attr.name, namespaceURI = attr.namespaceURI, attrValue = attr.value;
        const lcName = transformCaseFunc(name);
        const initValue = attrValue;
        let value = name === "value" ? initValue : stringTrim(initValue);
        hookEvent.attrName = lcName;
        hookEvent.attrValue = value;
        hookEvent.keepAttr = true;
        hookEvent.forceKeepAttr = void 0;
        _executeHooks(hooks.uponSanitizeAttribute, currentNode, hookEvent);
        value = hookEvent.attrValue;
        if (SANITIZE_NAMED_PROPS && (lcName === "id" || lcName === "name") && stringIndexOf(value, SANITIZE_NAMED_PROPS_PREFIX) !== 0) {
          _removeAttribute(name, currentNode, attr);
          value = SANITIZE_NAMED_PROPS_PREFIX + value;
        }
        if (SAFE_FOR_XML && regExpTest(/((--!?|])>)|<\/(style|script|title|xmp|textarea|noscript|iframe|noembed|noframes)/i, value)) {
          _removeAttribute(name, currentNode, attr);
          continue;
        }
        if (lcName === "attributename" && stringMatch(value, "href")) {
          _removeAttribute(name, currentNode, attr);
          continue;
        }
        if (hookEvent.forceKeepAttr) {
          continue;
        }
        if (!hookEvent.keepAttr) {
          _removeAttribute(name, currentNode, attr);
          continue;
        }
        if (!ALLOW_SELF_CLOSE_IN_ATTR && regExpTest(SELF_CLOSING_TAG, value)) {
          _removeAttribute(name, currentNode, attr);
          continue;
        }
        if (SAFE_FOR_TEMPLATES) {
          value = _stripTemplateExpressions(value);
        }
        if (!_isValidAttribute(lcTag, lcName, value)) {
          _removeAttribute(name, currentNode, attr);
          continue;
        }
        value = _applyTrustedTypesToAttribute(lcTag, lcName, namespaceURI, value);
        if (value !== initValue) {
          _setAttributeValue(currentNode, name, namespaceURI, value);
        }
      }
      _executeHooks(hooks.afterSanitizeAttributes, currentNode, null);
    };
    const _sanitizeShadowDOM2 = function _sanitizeShadowDOM(fragment) {
      let shadowNode = null;
      const shadowIterator = _createNodeIterator(fragment);
      _executeHooks(hooks.beforeSanitizeShadowDOM, fragment, null);
      while (shadowNode = shadowIterator.nextNode()) {
        _executeHooks(hooks.uponSanitizeShadowNode, shadowNode, null);
        _sanitizeElements(shadowNode, fragment);
        _sanitizeAttributes(shadowNode);
        if (_isDocumentFragment(shadowNode.content)) {
          _sanitizeShadowDOM2(shadowNode.content);
        }
        if (_readNodeType(shadowNode) === NODE_TYPE.element) {
          const innerSr = getShadowRoot(shadowNode);
          if (_isDocumentFragment(innerSr)) {
            _sanitizeAttachedShadowRoots(innerSr);
            _sanitizeShadowDOM2(innerSr);
          }
        }
      }
      _executeHooks(hooks.afterSanitizeShadowDOM, fragment, null);
    };
    const _sanitizeAttachedShadowRoots = function _sanitizeAttachedShadowRoots2(root) {
      const stack = [{
        node: root,
        shadow: null
      }];
      while (stack.length > 0) {
        const item = stack.pop();
        if (item.shadow) {
          _sanitizeShadowDOM2(item.shadow);
          continue;
        }
        const node = item.node;
        const nodeType = _readNodeType(node);
        const isElement = nodeType === NODE_TYPE.element;
        const childNodes = getChildNodes(node);
        if (childNodes) {
          for (let i = childNodes.length - 1; i >= 0; --i) {
            stack.push({
              node: childNodes[i],
              shadow: null
            });
          }
        }
        if (isElement) {
          const rootName = getNodeName ? getNodeName(node) : null;
          if (typeof rootName === "string" && transformCaseFunc(rootName) === "template") {
            const content = node.content;
            if (_isDocumentFragment(content)) {
              stack.push({
                node: content,
                shadow: null
              });
            }
          }
        }
        if (isElement) {
          const sr = getShadowRoot(node);
          if (_isDocumentFragment(sr)) {
            stack.push({
              node: null,
              shadow: sr
            }, {
              node: sr,
              shadow: null
            });
          }
        }
      }
    };
    DOMPurify.sanitize = function(dirty) {
      let cfg = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
      let body = null;
      let importedNode = null;
      let currentNode = null;
      let returnNode = null;
      IS_EMPTY_INPUT = !dirty;
      if (IS_EMPTY_INPUT) {
        dirty = "<!-->";
      }
      if (typeof dirty !== "string" && !_isNode(dirty)) {
        dirty = stringifyValue(dirty);
        if (typeof dirty !== "string") {
          throw typeErrorCreate("dirty is not a string, aborting");
        }
      }
      if (!DOMPurify.isSupported) {
        return dirty;
      }
      if (SET_CONFIG) {
        ALLOWED_TAGS = SET_CONFIG_ALLOWED_TAGS;
        ALLOWED_ATTR = SET_CONFIG_ALLOWED_ATTR;
      } else {
        _parseConfig(cfg);
      }
      if (hooks.uponSanitizeElement.length > 0 || hooks.uponSanitizeAttribute.length > 0) {
        ALLOWED_TAGS = clone(ALLOWED_TAGS);
      }
      if (hooks.uponSanitizeAttribute.length > 0) {
        ALLOWED_ATTR = clone(ALLOWED_ATTR);
      }
      DOMPurify.removed = [];
      const inPlace = IN_PLACE && typeof dirty !== "string" && _isNode(dirty);
      if (inPlace) {
        _neutralizePatchLinkage(dirty);
        const nn = _readNodeName(dirty);
        if (typeof nn === "string") {
          const tagName = transformCaseFunc(nn);
          if (!ALLOWED_TAGS[tagName] || FORBID_TAGS[tagName]) {
            _neutralizeRoot(dirty);
            throw typeErrorCreate("root node is forbidden and cannot be sanitized in-place");
          }
        }
        if (_isClobbered(dirty)) {
          _neutralizeRoot(dirty);
          throw typeErrorCreate("root node is clobbered and cannot be sanitized in-place");
        }
        try {
          _sanitizeAttachedShadowRoots(dirty);
        } catch (error) {
          _neutralizeRoot(dirty);
          throw error;
        }
      } else if (_isNode(dirty)) {
        body = _initDocument("<!---->");
        importedNode = body.ownerDocument.importNode(dirty, true);
        if (importedNode.nodeType === NODE_TYPE.element && importedNode.nodeName === "BODY") {
          body = importedNode;
        } else if (importedNode.nodeName === "HTML") {
          body = importedNode;
        } else {
          body.appendChild(importedNode);
        }
        _sanitizeAttachedShadowRoots(importedNode);
      } else {
        if (!RETURN_DOM && !SAFE_FOR_TEMPLATES && !WHOLE_DOCUMENT && // eslint-disable-next-line unicorn/prefer-includes
        dirty.indexOf("<") === -1) {
          return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? _createTrustedHTML(dirty) : dirty;
        }
        body = _initDocument(dirty);
        if (!body) {
          return RETURN_DOM ? null : RETURN_TRUSTED_TYPE ? emptyHTML : "";
        }
      }
      if (body && FORCE_BODY) {
        _forceRemove(body.firstChild);
      }
      const walkRoot = inPlace ? dirty : body;
      try {
        const nodeIterator = _createNodeIterator(walkRoot);
        while (currentNode = nodeIterator.nextNode()) {
          _sanitizeElements(currentNode, walkRoot);
          _sanitizeAttributes(currentNode);
          if (_isDocumentFragment(currentNode.content)) {
            _sanitizeShadowDOM2(currentNode.content);
          }
        }
      } catch (error) {
        if (inPlace) {
          _neutralizeRoot(dirty);
          arrayForEach(DOMPurify.removed, (entry) => {
            if (entry.element) {
              _neutralizeSubtree(entry.element);
            }
          });
        }
        throw error;
      }
      if (inPlace) {
        arrayForEach(DOMPurify.removed, (entry) => {
          if (entry.element) {
            _neutralizeSubtree(entry.element);
          }
        });
        if (SAFE_FOR_TEMPLATES) {
          _scrubTemplateExpressions2(dirty);
        }
        return dirty;
      }
      if (RETURN_DOM) {
        if (SAFE_FOR_TEMPLATES) {
          _scrubTemplateExpressions2(body);
        }
        if (RETURN_DOM_FRAGMENT) {
          returnNode = createDocumentFragment.call(body.ownerDocument);
          while (body.firstChild) {
            returnNode.appendChild(body.firstChild);
          }
        } else {
          returnNode = body;
        }
        if (ALLOWED_ATTR.shadowroot || ALLOWED_ATTR.shadowrootmode) {
          returnNode = importNode.call(originalDocument, returnNode, true);
        }
        return returnNode;
      }
      let serializedHTML = WHOLE_DOCUMENT ? body.outerHTML : body.innerHTML;
      if (WHOLE_DOCUMENT && ALLOWED_TAGS["!doctype"] && body.ownerDocument && body.ownerDocument.doctype && body.ownerDocument.doctype.name && regExpTest(DOCTYPE_NAME, body.ownerDocument.doctype.name)) {
        serializedHTML = "<!DOCTYPE " + body.ownerDocument.doctype.name + ">\n" + serializedHTML;
      }
      if (SAFE_FOR_TEMPLATES) {
        serializedHTML = _stripTemplateExpressions(serializedHTML);
      }
      return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? _createTrustedHTML(serializedHTML) : serializedHTML;
    };
    DOMPurify.setConfig = function() {
      let cfg = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
      _parseConfig(cfg);
      SET_CONFIG = true;
      SET_CONFIG_ALLOWED_TAGS = ALLOWED_TAGS;
      SET_CONFIG_ALLOWED_ATTR = ALLOWED_ATTR;
    };
    DOMPurify.clearConfig = function() {
      CONFIG = null;
      SET_CONFIG = false;
      SET_CONFIG_ALLOWED_TAGS = null;
      SET_CONFIG_ALLOWED_ATTR = null;
      trustedTypesPolicy = defaultTrustedTypesPolicy;
      emptyHTML = "";
    };
    DOMPurify.isValidAttribute = function(tag, attr, value) {
      if (!CONFIG) {
        _parseConfig({});
      }
      const lcTag = transformCaseFunc(tag);
      const lcName = transformCaseFunc(attr);
      return _isValidAttribute(lcTag, lcName, value);
    };
    DOMPurify.addHook = function(entryPoint, hookFunction) {
      if (typeof hookFunction !== "function") {
        return;
      }
      if (!objectHasOwnProperty(hooks, entryPoint)) {
        return;
      }
      arrayPush(hooks[entryPoint], hookFunction);
    };
    DOMPurify.removeHook = function(entryPoint, hookFunction) {
      if (!objectHasOwnProperty(hooks, entryPoint)) {
        return void 0;
      }
      if (hookFunction !== void 0) {
        const index = arrayLastIndexOf(hooks[entryPoint], hookFunction);
        return index === -1 ? void 0 : arraySplice(hooks[entryPoint], index, 1)[0];
      }
      return arrayPop(hooks[entryPoint]);
    };
    DOMPurify.removeHooks = function(entryPoint) {
      if (!objectHasOwnProperty(hooks, entryPoint)) {
        return;
      }
      hooks[entryPoint] = [];
    };
    DOMPurify.removeAllHooks = function() {
      hooks = _createHooksMap();
    };
    return DOMPurify;
  }
  var purify = createDOMPurify();
  var extendStatics = function(d, b) {
    extendStatics = Object.setPrototypeOf || { __proto__: [] } instanceof Array && function(d2, b2) {
      d2.__proto__ = b2;
    } || function(d2, b2) {
      for (var p in b2) if (Object.prototype.hasOwnProperty.call(b2, p)) d2[p] = b2[p];
    };
    return extendStatics(d, b);
  };
  function __extends(d, b) {
    if (typeof b !== "function" && b !== null)
      throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
    extendStatics(d, b);
    function __() {
      this.constructor = d;
    }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
  }
  var __assign = function() {
    __assign = Object.assign || function __assign2(t) {
      for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
      }
      return t;
    };
    return __assign.apply(this, arguments);
  };
  function __rest(s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
      t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
      for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
        if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
          t[p[i]] = s[p[i]];
      }
    return t;
  }
  function __decorate(decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
  }
  function __param(paramIndex, decorator) {
    return function(target, key) {
      decorator(target, key, paramIndex);
    };
  }
  function __esDecorate(ctor, descriptorIn, decorators, contextIn, initializers, extraInitializers) {
    function accept(f) {
      if (f !== void 0 && typeof f !== "function") throw new TypeError("Function expected");
      return f;
    }
    var kind = contextIn.kind, key = kind === "getter" ? "get" : kind === "setter" ? "set" : "value";
    var target = !descriptorIn && ctor ? contextIn["static"] ? ctor : ctor.prototype : null;
    var descriptor = descriptorIn || (target ? Object.getOwnPropertyDescriptor(target, contextIn.name) : {});
    var _, done = false;
    for (var i = decorators.length - 1; i >= 0; i--) {
      var context = {};
      for (var p in contextIn) context[p] = p === "access" ? {} : contextIn[p];
      for (var p in contextIn.access) context.access[p] = contextIn.access[p];
      context.addInitializer = function(f) {
        if (done) throw new TypeError("Cannot add initializers after decoration has completed");
        extraInitializers.push(accept(f || null));
      };
      var result = (0, decorators[i])(kind === "accessor" ? { get: descriptor.get, set: descriptor.set } : descriptor[key], context);
      if (kind === "accessor") {
        if (result === void 0) continue;
        if (result === null || typeof result !== "object") throw new TypeError("Object expected");
        if (_ = accept(result.get)) descriptor.get = _;
        if (_ = accept(result.set)) descriptor.set = _;
        if (_ = accept(result.init)) initializers.unshift(_);
      } else if (_ = accept(result)) {
        if (kind === "field") initializers.unshift(_);
        else descriptor[key] = _;
      }
    }
    if (target) Object.defineProperty(target, contextIn.name, descriptor);
    done = true;
  }
  function __runInitializers(thisArg, initializers, value) {
    var useValue = arguments.length > 2;
    for (var i = 0; i < initializers.length; i++) {
      value = useValue ? initializers[i].call(thisArg, value) : initializers[i].call(thisArg);
    }
    return useValue ? value : void 0;
  }
  function __propKey(x) {
    return typeof x === "symbol" ? x : "".concat(x);
  }
  function __setFunctionName(f, name, prefix) {
    if (typeof name === "symbol") name = name.description ? "[".concat(name.description, "]") : "";
    return Object.defineProperty(f, "name", { configurable: true, value: prefix ? "".concat(prefix, " ", name) : name });
  }
  function __metadata(metadataKey, metadataValue) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(metadataKey, metadataValue);
  }
  function __awaiter(thisArg, _arguments, P, generator) {
    function adopt(value) {
      return value instanceof P ? value : new P(function(resolve) {
        resolve(value);
      });
    }
    return new (P || (P = Promise))(function(resolve, reject) {
      function fulfilled(value) {
        try {
          step(generator.next(value));
        } catch (e) {
          reject(e);
        }
      }
      function rejected(value) {
        try {
          step(generator["throw"](value));
        } catch (e) {
          reject(e);
        }
      }
      function step(result) {
        result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
      }
      step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
  }
  function __generator(thisArg, body) {
    var _ = { label: 0, sent: function() {
      if (t[0] & 1) throw t[1];
      return t[1];
    }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() {
      return this;
    }), g;
    function verb(n) {
      return function(v) {
        return step([n, v]);
      };
    }
    function step(op) {
      if (f) throw new TypeError("Generator is already executing.");
      while (g && (g = 0, op[0] && (_ = 0)), _) try {
        if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
        if (y = 0, t) op = [op[0] & 2, t.value];
        switch (op[0]) {
          case 0:
          case 1:
            t = op;
            break;
          case 4:
            _.label++;
            return { value: op[1], done: false };
          case 5:
            _.label++;
            y = op[1];
            op = [0];
            continue;
          case 7:
            op = _.ops.pop();
            _.trys.pop();
            continue;
          default:
            if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) {
              _ = 0;
              continue;
            }
            if (op[0] === 3 && (!t || op[1] > t[0] && op[1] < t[3])) {
              _.label = op[1];
              break;
            }
            if (op[0] === 6 && _.label < t[1]) {
              _.label = t[1];
              t = op;
              break;
            }
            if (t && _.label < t[2]) {
              _.label = t[2];
              _.ops.push(op);
              break;
            }
            if (t[2]) _.ops.pop();
            _.trys.pop();
            continue;
        }
        op = body.call(thisArg, _);
      } catch (e) {
        op = [6, e];
        y = 0;
      } finally {
        f = t = 0;
      }
      if (op[0] & 5) throw op[1];
      return { value: op[0] ? op[1] : void 0, done: true };
    }
  }
  var __createBinding = Object.create ? (function(o, m, k, k2) {
    if (k2 === void 0) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() {
        return m[k];
      } };
    }
    Object.defineProperty(o, k2, desc);
  }) : (function(o, m, k, k2) {
    if (k2 === void 0) k2 = k;
    o[k2] = m[k];
  });
  function __exportStar(m, o) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(o, p)) __createBinding(o, m, p);
  }
  function __values(o) {
    var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
    if (m) return m.call(o);
    if (o && typeof o.length === "number") return {
      next: function() {
        if (o && i >= o.length) o = void 0;
        return { value: o && o[i++], done: !o };
      }
    };
    throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
  }
  function __read(o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
      while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    } catch (error) {
      e = { error };
    } finally {
      try {
        if (r && !r.done && (m = i["return"])) m.call(i);
      } finally {
        if (e) throw e.error;
      }
    }
    return ar;
  }
  function __spread() {
    for (var ar = [], i = 0; i < arguments.length; i++)
      ar = ar.concat(__read(arguments[i]));
    return ar;
  }
  function __spreadArrays() {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++)
      for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
        r[k] = a[j];
    return r;
  }
  function __spreadArray(to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
      if (ar || !(i in from)) {
        if (!ar) ar = Array.prototype.slice.call(from, 0, i);
        ar[i] = from[i];
      }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
  }
  function __await(v) {
    return this instanceof __await ? (this.v = v, this) : new __await(v);
  }
  function __asyncGenerator(thisArg, _arguments, generator) {
    if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
    var g = generator.apply(thisArg, _arguments || []), i, q = [];
    return i = Object.create((typeof AsyncIterator === "function" ? AsyncIterator : Object).prototype), verb("next"), verb("throw"), verb("return", awaitReturn), i[Symbol.asyncIterator] = function() {
      return this;
    }, i;
    function awaitReturn(f) {
      return function(v) {
        return Promise.resolve(v).then(f, reject);
      };
    }
    function verb(n, f) {
      if (g[n]) {
        i[n] = function(v) {
          return new Promise(function(a, b) {
            q.push([n, v, a, b]) > 1 || resume(n, v);
          });
        };
        if (f) i[n] = f(i[n]);
      }
    }
    function resume(n, v) {
      try {
        step(g[n](v));
      } catch (e) {
        settle(q[0][3], e);
      }
    }
    function step(r) {
      r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r);
    }
    function fulfill(value) {
      resume("next", value);
    }
    function reject(value) {
      resume("throw", value);
    }
    function settle(f, v) {
      if (f(v), q.shift(), q.length) resume(q[0][0], q[0][1]);
    }
  }
  function __asyncDelegator(o) {
    var i, p;
    return i = {}, verb("next"), verb("throw", function(e) {
      throw e;
    }), verb("return"), i[Symbol.iterator] = function() {
      return this;
    }, i;
    function verb(n, f) {
      i[n] = o[n] ? function(v) {
        return (p = !p) ? { value: __await(o[n](v)), done: false } : f ? f(v) : v;
      } : f;
    }
  }
  function __asyncValues(o) {
    if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
    var m = o[Symbol.asyncIterator], i;
    return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function() {
      return this;
    }, i);
    function verb(n) {
      i[n] = o[n] && function(v) {
        return new Promise(function(resolve, reject) {
          v = o[n](v), settle(resolve, reject, v.done, v.value);
        });
      };
    }
    function settle(resolve, reject, d, v) {
      Promise.resolve(v).then(function(v2) {
        resolve({ value: v2, done: d });
      }, reject);
    }
  }
  function __makeTemplateObject(cooked, raw) {
    if (Object.defineProperty) {
      Object.defineProperty(cooked, "raw", { value: raw });
    } else {
      cooked.raw = raw;
    }
    return cooked;
  }
  var __setModuleDefault = Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
  }) : function(o, v) {
    o["default"] = v;
  };
  var ownKeys = function(o) {
    ownKeys = Object.getOwnPropertyNames || function(o2) {
      var ar = [];
      for (var k in o2) if (Object.prototype.hasOwnProperty.call(o2, k)) ar[ar.length] = k;
      return ar;
    };
    return ownKeys(o);
  };
  function __importStar(mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) {
      for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
    }
    __setModuleDefault(result, mod);
    return result;
  }
  function __importDefault(mod) {
    return mod && mod.__esModule ? mod : { default: mod };
  }
  function __classPrivateFieldGet(receiver, state, kind, f) {
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a getter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot read private member from an object whose class did not declare it");
    return kind === "m" ? f : kind === "a" ? f.call(receiver) : f ? f.value : state.get(receiver);
  }
  function __classPrivateFieldSet(receiver, state, value, kind, f) {
    if (kind === "m") throw new TypeError("Private method is not writable");
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a setter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot write private member to an object whose class did not declare it");
    return kind === "a" ? f.call(receiver, value) : f ? f.value = value : state.set(receiver, value), value;
  }
  function __classPrivateFieldIn(state, receiver) {
    if (receiver === null || typeof receiver !== "object" && typeof receiver !== "function") throw new TypeError("Cannot use 'in' operator on non-object");
    return typeof state === "function" ? receiver === state : state.has(receiver);
  }
  function __addDisposableResource(env, value, async) {
    if (value !== null && value !== void 0) {
      if (typeof value !== "object" && typeof value !== "function") throw new TypeError("Object expected.");
      var dispose, inner;
      if (async) {
        if (!Symbol.asyncDispose) throw new TypeError("Symbol.asyncDispose is not defined.");
        dispose = value[Symbol.asyncDispose];
      }
      if (dispose === void 0) {
        if (!Symbol.dispose) throw new TypeError("Symbol.dispose is not defined.");
        dispose = value[Symbol.dispose];
        if (async) inner = dispose;
      }
      if (typeof dispose !== "function") throw new TypeError("Object not disposable.");
      if (inner) dispose = function() {
        try {
          inner.call(this);
        } catch (e) {
          return Promise.reject(e);
        }
      };
      env.stack.push({ value, dispose, async });
    } else if (async) {
      env.stack.push({ async: true });
    }
    return value;
  }
  var _SuppressedError = typeof SuppressedError === "function" ? SuppressedError : function(error, suppressed, message) {
    var e = new Error(message);
    return e.name = "SuppressedError", e.error = error, e.suppressed = suppressed, e;
  };
  function __disposeResources(env) {
    function fail(e) {
      env.error = env.hasError ? new _SuppressedError(e, env.error, "An error was suppressed during disposal.") : e;
      env.hasError = true;
    }
    var r, s = 0;
    function next() {
      while (r = env.stack.pop()) {
        try {
          if (!r.async && s === 1) return s = 0, env.stack.push(r), Promise.resolve().then(next);
          if (r.dispose) {
            var result = r.dispose.call(r.value);
            if (r.async) return s |= 2, Promise.resolve(result).then(next, function(e) {
              fail(e);
              return next();
            });
          } else s |= 1;
        } catch (e) {
          fail(e);
        }
      }
      if (s === 1) return env.hasError ? Promise.reject(env.error) : Promise.resolve();
      if (env.hasError) throw env.error;
    }
    return next();
  }
  function __rewriteRelativeImportExtension(path2, preserveJsx) {
    if (typeof path2 === "string" && /^\.\.?\//.test(path2)) {
      return path2.replace(/\.(tsx)$|((?:\.d)?)((?:\.[^./]+?)?)\.([cm]?)ts$/i, function(m, tsx, d, ext, cm) {
        return tsx ? preserveJsx ? ".jsx" : ".js" : d && (!ext || !cm) ? m : d + ext + "." + cm.toLowerCase() + "js";
      });
    }
    return path2;
  }
  const tslib_es6 = {
    __extends,
    __assign,
    __rest,
    __decorate,
    __param,
    __esDecorate,
    __runInitializers,
    __propKey,
    __setFunctionName,
    __metadata,
    __awaiter,
    __generator,
    __createBinding,
    __exportStar,
    __values,
    __read,
    __spread,
    __spreadArrays,
    __spreadArray,
    __await,
    __asyncGenerator,
    __asyncDelegator,
    __asyncValues,
    __makeTemplateObject,
    __importStar,
    __importDefault,
    __classPrivateFieldGet,
    __classPrivateFieldSet,
    __classPrivateFieldIn,
    __addDisposableResource,
    __disposeResources,
    __rewriteRelativeImportExtension
  };
  const tslib = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
    __proto__: null,
    __addDisposableResource,
    get __assign() {
      return __assign;
    },
    __asyncDelegator,
    __asyncGenerator,
    __asyncValues,
    __await,
    __awaiter,
    __classPrivateFieldGet,
    __classPrivateFieldIn,
    __classPrivateFieldSet,
    __createBinding,
    __decorate,
    __disposeResources,
    __esDecorate,
    __exportStar,
    __extends,
    __generator,
    __importDefault,
    __importStar,
    __makeTemplateObject,
    __metadata,
    __param,
    __propKey,
    __read,
    __rest,
    __rewriteRelativeImportExtension,
    __runInitializers,
    __setFunctionName,
    __spread,
    __spreadArray,
    __spreadArrays,
    __values,
    default: tslib_es6
  }, Symbol.toStringTag, { value: "Module" }));
  var abortcontrollerPolyfillOnly = {};
  var hasRequiredAbortcontrollerPolyfillOnly;
  function requireAbortcontrollerPolyfillOnly() {
    if (hasRequiredAbortcontrollerPolyfillOnly) return abortcontrollerPolyfillOnly;
    hasRequiredAbortcontrollerPolyfillOnly = 1;
    (function(factory) {
      factory();
    })((function() {
      function _arrayLikeToArray2(r, a) {
        (null == a || a > r.length) && (a = r.length);
        for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
        return n;
      }
      function _assertThisInitialized(e) {
        if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        return e;
      }
      function _callSuper(t, o, e) {
        return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, [], _getPrototypeOf(t).constructor) : o.apply(t, e));
      }
      function _classCallCheck(a, n) {
        if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
      }
      function _defineProperties(e, r) {
        for (var t = 0; t < r.length; t++) {
          var o = r[t];
          o.enumerable = o.enumerable || false, o.configurable = true, "value" in o && (o.writable = true), Object.defineProperty(e, _toPropertyKey(o.key), o);
        }
      }
      function _createClass(e, r, t) {
        return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", {
          writable: false
        }), e;
      }
      function _createForOfIteratorHelper(r, e) {
        var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
        if (!t) {
          if (Array.isArray(r) || (t = _unsupportedIterableToArray2(r)) || e) {
            t && (r = t);
            var n = 0, F = function() {
            };
            return {
              s: F,
              n: function() {
                return n >= r.length ? {
                  done: true
                } : {
                  done: false,
                  value: r[n++]
                };
              },
              e: function(r2) {
                throw r2;
              },
              f: F
            };
          }
          throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
        }
        var o, a = true, u = false;
        return {
          s: function() {
            t = t.call(r);
          },
          n: function() {
            var r2 = t.next();
            return a = r2.done, r2;
          },
          e: function(r2) {
            u = true, o = r2;
          },
          f: function() {
            try {
              a || null == t.return || t.return();
            } finally {
              if (u) throw o;
            }
          }
        };
      }
      function _get() {
        return _get = "undefined" != typeof Reflect && Reflect.get ? Reflect.get.bind() : function(e, t, r) {
          var p = _superPropBase(e, t);
          if (p) {
            var n = Object.getOwnPropertyDescriptor(p, t);
            return n.get ? n.get.call(arguments.length < 3 ? e : r) : n.value;
          }
        }, _get.apply(null, arguments);
      }
      function _getPrototypeOf(t) {
        return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(t2) {
          return t2.__proto__ || Object.getPrototypeOf(t2);
        }, _getPrototypeOf(t);
      }
      function _inherits(t, e) {
        if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function");
        t.prototype = Object.create(e && e.prototype, {
          constructor: {
            value: t,
            writable: true,
            configurable: true
          }
        }), Object.defineProperty(t, "prototype", {
          writable: false
        }), e && _setPrototypeOf(t, e);
      }
      function _isNativeReflectConstruct() {
        try {
          var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
          }));
        } catch (t2) {
        }
        return (_isNativeReflectConstruct = function() {
          return !!t;
        })();
      }
      function _possibleConstructorReturn(t, e) {
        if (e && ("object" == typeof e || "function" == typeof e)) return e;
        if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined");
        return _assertThisInitialized(t);
      }
      function _setPrototypeOf(t, e) {
        return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(t2, e2) {
          return t2.__proto__ = e2, t2;
        }, _setPrototypeOf(t, e);
      }
      function _superPropBase(t, o) {
        for (; !{}.hasOwnProperty.call(t, o) && null !== (t = _getPrototypeOf(t)); ) ;
        return t;
      }
      function _superPropGet(t, o, e, r) {
        var p = _get(_getPrototypeOf(t.prototype), o, e);
        return "function" == typeof p ? function(t2) {
          return p.apply(e, t2);
        } : p;
      }
      function _toPrimitive(t, r) {
        if ("object" != typeof t || !t) return t;
        var e = t[Symbol.toPrimitive];
        if (void 0 !== e) {
          var i = e.call(t, r);
          if ("object" != typeof i) return i;
          throw new TypeError("@@toPrimitive must return a primitive value.");
        }
        return String(t);
      }
      function _toPropertyKey(t) {
        var i = _toPrimitive(t, "string");
        return "symbol" == typeof i ? i : i + "";
      }
      function _unsupportedIterableToArray2(r, a) {
        if (r) {
          if ("string" == typeof r) return _arrayLikeToArray2(r, a);
          var t = {}.toString.call(r).slice(8, -1);
          return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray2(r, a) : void 0;
        }
      }
      (function(self2) {
        return {
          NativeAbortSignal: self2.AbortSignal,
          NativeAbortController: self2.AbortController
        };
      })(typeof self !== "undefined" ? self : commonjsGlobal);
      function createAbortEvent(reason) {
        var event;
        try {
          event = new Event("abort");
        } catch (e) {
          if (typeof document !== "undefined") {
            if (!document.createEvent) {
              event = document.createEventObject();
              event.type = "abort";
            } else {
              event = document.createEvent("Event");
              event.initEvent("abort", false, false);
            }
          } else {
            event = {
              type: "abort",
              bubbles: false,
              cancelable: false
            };
          }
        }
        event.reason = reason;
        return event;
      }
      function normalizeAbortReason(reason) {
        if (reason === void 0) {
          if (typeof document === "undefined") {
            reason = new Error("This operation was aborted");
            reason.name = "AbortError";
          } else {
            try {
              reason = new DOMException("signal is aborted without reason");
              Object.defineProperty(reason, "name", {
                value: "AbortError"
              });
            } catch (err) {
              reason = new Error("This operation was aborted");
              reason.name = "AbortError";
            }
          }
        }
        return reason;
      }
      var Emitter = /* @__PURE__ */ (function() {
        function Emitter2() {
          _classCallCheck(this, Emitter2);
          Object.defineProperty(this, "listeners", {
            value: {},
            writable: true,
            configurable: true
          });
        }
        return _createClass(Emitter2, [{
          key: "addEventListener",
          value: function addEventListener(type, callback, options) {
            if (!(type in this.listeners)) {
              this.listeners[type] = [];
            }
            this.listeners[type].push({
              callback,
              options
            });
          }
        }, {
          key: "removeEventListener",
          value: function removeEventListener(type, callback) {
            if (!(type in this.listeners)) {
              return;
            }
            var stack = this.listeners[type];
            for (var i = 0, l = stack.length; i < l; i++) {
              if (stack[i].callback === callback) {
                stack.splice(i, 1);
                return;
              }
            }
          }
        }, {
          key: "dispatchEvent",
          value: function dispatchEvent(event) {
            var _this = this;
            if (!(event.type in this.listeners)) {
              return;
            }
            var stack = this.listeners[event.type];
            var stackToCall = stack.slice();
            var _loop = function _loop2() {
              var listener = stackToCall[i];
              try {
                listener.callback.call(_this, event);
              } catch (e) {
                Promise.resolve().then(function() {
                  throw e;
                });
              }
              if (listener.options && listener.options.once) {
                _this.removeEventListener(event.type, listener.callback);
              }
            };
            for (var i = 0, l = stackToCall.length; i < l; i++) {
              _loop();
            }
            return !event.defaultPrevented;
          }
        }]);
      })();
      var AbortSignal = /* @__PURE__ */ (function(_Emitter) {
        function AbortSignal2() {
          var _this2;
          _classCallCheck(this, AbortSignal2);
          _this2 = _callSuper(this, AbortSignal2);
          if (!_this2.listeners) {
            Emitter.call(_this2);
          }
          Object.defineProperty(_this2, "aborted", {
            value: false,
            writable: true,
            configurable: true
          });
          Object.defineProperty(_this2, "onabort", {
            value: null,
            writable: true,
            configurable: true
          });
          Object.defineProperty(_this2, "reason", {
            value: void 0,
            writable: true,
            configurable: true
          });
          return _this2;
        }
        _inherits(AbortSignal2, _Emitter);
        return _createClass(AbortSignal2, [{
          key: "toString",
          value: function toString2() {
            return "[object AbortSignal]";
          }
        }, {
          key: "dispatchEvent",
          value: function dispatchEvent(event) {
            if (event.type === "abort") {
              this.aborted = true;
              if (typeof this.onabort === "function") {
                this.onabort.call(this, event);
              }
            }
            _superPropGet(AbortSignal2, "dispatchEvent", this)([event]);
          }
          /**
           * @see {@link https://developer.mozilla.org/zh-CN/docs/Web/API/AbortSignal/throwIfAborted}
           */
        }, {
          key: "throwIfAborted",
          value: function throwIfAborted() {
            var aborted = this.aborted, _this$reason = this.reason, reason = _this$reason === void 0 ? "Aborted" : _this$reason;
            if (!aborted) return;
            throw reason;
          }
          /**
           * @see {@link https://developer.mozilla.org/zh-CN/docs/Web/API/AbortSignal/timeout_static}
           * @param {number} time The "active" time in milliseconds before the returned {@link AbortSignal} will abort.
           *                      The value must be within range of 0 and {@link Number.MAX_SAFE_INTEGER}.
           * @returns {AbortSignal} The signal will abort with its {@link AbortSignal.reason} property set to a `TimeoutError` {@link DOMException} on timeout,
           *                        or an `AbortError` {@link DOMException} if the operation was user-triggered.
           */
        }], [{
          key: "timeout",
          value: function timeout(time) {
            var controller = new AbortController();
            setTimeout(function() {
              return controller.abort(new DOMException("This signal is timeout in ".concat(time, "ms"), "TimeoutError"));
            }, time);
            return controller.signal;
          }
          /**
           * @see {@link https://developer.mozilla.org/en-US/docs/Web/API/AbortSignal/any_static}
           * @param {Iterable<AbortSignal>} iterable An {@link Iterable} (such as an {@link Array}) of abort signals.
           * @returns {AbortSignal} - **Already aborted**, if any of the abort signals given is already aborted.
           *                          The returned {@link AbortSignal}'s reason will be already set to the `reason` of the first abort signal that was already aborted.
           *                        - **Asynchronously aborted**, when any abort signal in `iterable` aborts.
           *                          The `reason` will be set to the reason of the first abort signal that is aborted.
           */
        }, {
          key: "any",
          value: function any(iterable) {
            var controller = new AbortController();
            function abort() {
              controller.abort(this.reason);
              clean();
            }
            function clean() {
              var _iterator = _createForOfIteratorHelper(iterable), _step;
              try {
                for (_iterator.s(); !(_step = _iterator.n()).done; ) {
                  var signal2 = _step.value;
                  signal2.removeEventListener("abort", abort);
                }
              } catch (err) {
                _iterator.e(err);
              } finally {
                _iterator.f();
              }
            }
            var _iterator2 = _createForOfIteratorHelper(iterable), _step2;
            try {
              for (_iterator2.s(); !(_step2 = _iterator2.n()).done; ) {
                var signal = _step2.value;
                if (signal.aborted) {
                  controller.abort(signal.reason);
                  break;
                } else signal.addEventListener("abort", abort);
              }
            } catch (err) {
              _iterator2.e(err);
            } finally {
              _iterator2.f();
            }
            return controller.signal;
          }
        }]);
      })(Emitter);
      var AbortController = /* @__PURE__ */ (function() {
        function AbortController2() {
          _classCallCheck(this, AbortController2);
          Object.defineProperty(this, "signal", {
            value: new AbortSignal(),
            writable: true,
            configurable: true
          });
        }
        return _createClass(AbortController2, [{
          key: "abort",
          value: function abort(reason) {
            var signalReason = normalizeAbortReason(reason);
            var event = createAbortEvent(signalReason);
            this.signal.reason = signalReason;
            this.signal.dispatchEvent(event);
          }
        }, {
          key: "toString",
          value: function toString2() {
            return "[object AbortController]";
          }
        }]);
      })();
      if (typeof Symbol !== "undefined" && Symbol.toStringTag) {
        AbortController.prototype[Symbol.toStringTag] = "AbortController";
        AbortSignal.prototype[Symbol.toStringTag] = "AbortSignal";
      }
      function polyfillNeeded(self2) {
        if (self2.__FORCE_INSTALL_ABORTCONTROLLER_POLYFILL) {
          console.log("__FORCE_INSTALL_ABORTCONTROLLER_POLYFILL=true is set, will force install polyfill");
          return true;
        }
        return typeof self2.Request === "function" && !self2.Request.prototype.hasOwnProperty("signal") || !self2.AbortController;
      }
      (function(self2) {
        if (!polyfillNeeded(self2)) {
          return;
        }
        self2.AbortController = AbortController;
        self2.AbortSignal = AbortSignal;
      })(typeof self !== "undefined" ? self : commonjsGlobal);
    }));
    return abortcontrollerPolyfillOnly;
  }
  requireAbortcontrollerPolyfillOnly();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const oldTrigger = window.$.fn.trigger;
  function triggerWithNativeEventDispatch(jqEventOrType, data) {
    let isFirstElementOnPath = true;
    const type = jqEventOrType.type || jqEventOrType;
    const onEventAttributeName = "on".concat(type);
    function nativeDispatchSingleElement(element) {
      if (isFirstElementOnPath) {
        isFirstElementOnPath = false;
        if (element[onEventAttributeName] || element[type] instanceof Function && !(type === "click" && element.tagName.toUpperCase() === "A")) {
          return;
        }
      }
      if ((window.$._data(element, "events") || {})[type] && window.$._data(element, "handle")) {
        return;
      }
      if (element.dispatchEvent) {
        const event = new Event(type, {
          // do not rely on browser bubbling so we can keep checking for the on... attribute
          bubbles: false,
          cancelable: true
        });
        element.dispatchEvent(event);
      }
    }
    function nativeDispatch(element) {
      nativeDispatchSingleElement(element);
      const parent = element.parentElement;
      if (parent) {
        nativeDispatch(parent);
      }
    }
    const result = oldTrigger.call(this, jqEventOrType, data);
    if (type === "focus" || type === "blur") {
      return result;
    }
    this.each(function onEach() {
      nativeDispatch(this);
    });
    return result;
  }
  window.$.fn.trigger = triggerWithNativeEventDispatch;
  window.tslib = tslib;
  Object.fromEntries = function fromEntries(it) {
    return [...it].reduce((result, _ref2) => {
      let _ref22 = _slicedToArray$1(_ref2, 2), key = _ref22[0], value = _ref22[1];
      result[key] = value;
      return result;
    }, {});
  };
  function hasSafeRel(rel) {
    const parts = rel.split(/\s+/);
    return parts.includes("noopener") && parts.includes("noreferrer");
  }
  purify.addHook("afterSanitizeAttributes", (node) => {
    if (node.hasAttribute("target") && node.getAttribute("target") === "_blank" && (!node.hasAttribute("rel") || !hasSafeRel(node.getAttribute("rel")))) {
      node.removeAttribute("target");
    }
  });
  window.vueSanitize = function vueSanitize(val) {
    return purify.sanitize(val, {
      ADD_ATTR: ["target"],
      FORBID_TAGS: ["style"]
    });
  };
  window.vueSanitizeUrl = function vueSanitizeUrl(url) {
    return purify.isValidAttribute("a", "href", url) ? url : "";
  };
})();
