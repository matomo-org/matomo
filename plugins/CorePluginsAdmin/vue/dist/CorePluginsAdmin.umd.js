(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.CorePluginsAdmin = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __pow = Math.pow;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
    }
  return a;
};
var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));
var __async = (__this, __arguments, generator) => {
  return new Promise((resolve, reject) => {
    var fulfilled = (value) => {
      try {
        step(generator.next(value));
      } catch (e) {
        reject(e);
      }
    };
    var rejected = (value) => {
      try {
        step(generator.throw(value));
      } catch (e) {
        reject(e);
      }
    };
    var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
    step((generator = generator.apply(__this, __arguments)).next());
  });
};

  function _extends() {
    return _extends = Object.assign ? Object.assign.bind() : function(n) {
      for (var e = 1; e < arguments.length; e++) {
        var t = arguments[e];
        for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]);
      }
      return n;
    }, _extends.apply(null, arguments);
  }
  var DEFAULT_CONFIG = {
    // minimum relative difference between two compared values,
    // used by all comparison functions
    epsilon: 1e-12,
    // type of default matrix output. Choose 'matrix' (default) or 'array'
    matrix: "Matrix",
    // type of default number output. Choose 'number' (default) 'BigNumber', or 'Fraction
    number: "number",
    // number of significant digits in BigNumbers
    precision: 64,
    // predictable output type of functions. When true, output type depends only
    // on the input types. When false (default), output type can vary depending
    // on input values. For example `math.sqrt(-4)` returns `complex('2i')` when
    // predictable is false, and returns `NaN` when true.
    predictable: false,
    // random seed for seeded pseudo random number generation
    // null = randomly seed
    randomSeed: null
  };
  function isNumber(x) {
    return typeof x === "number";
  }
  function isBigNumber(x) {
    if (!x || typeof x !== "object" || typeof x.constructor !== "function") {
      return false;
    }
    if (x.isBigNumber === true && typeof x.constructor.prototype === "object" && x.constructor.prototype.isBigNumber === true) {
      return true;
    }
    if (typeof x.constructor.isDecimal === "function" && x.constructor.isDecimal(x) === true) {
      return true;
    }
    return false;
  }
  function isComplex(x) {
    return x && typeof x === "object" && Object.getPrototypeOf(x).isComplex === true || false;
  }
  function isFraction(x) {
    return x && typeof x === "object" && Object.getPrototypeOf(x).isFraction === true || false;
  }
  function isUnit(x) {
    return x && x.constructor.prototype.isUnit === true || false;
  }
  function isString(x) {
    return typeof x === "string";
  }
  var isArray = Array.isArray;
  function isMatrix(x) {
    return x && x.constructor.prototype.isMatrix === true || false;
  }
  function isCollection(x) {
    return Array.isArray(x) || isMatrix(x);
  }
  function isDenseMatrix(x) {
    return x && x.isDenseMatrix && x.constructor.prototype.isMatrix === true || false;
  }
  function isSparseMatrix(x) {
    return x && x.isSparseMatrix && x.constructor.prototype.isMatrix === true || false;
  }
  function isRange(x) {
    return x && x.constructor.prototype.isRange === true || false;
  }
  function isIndex(x) {
    return x && x.constructor.prototype.isIndex === true || false;
  }
  function isBoolean(x) {
    return typeof x === "boolean";
  }
  function isResultSet(x) {
    return x && x.constructor.prototype.isResultSet === true || false;
  }
  function isHelp(x) {
    return x && x.constructor.prototype.isHelp === true || false;
  }
  function isFunction(x) {
    return typeof x === "function";
  }
  function isDate(x) {
    return x instanceof Date;
  }
  function isRegExp(x) {
    return x instanceof RegExp;
  }
  function isObject(x) {
    return !!(x && typeof x === "object" && x.constructor === Object && !isComplex(x) && !isFraction(x));
  }
  function isNull(x) {
    return x === null;
  }
  function isUndefined(x) {
    return x === void 0;
  }
  function isAccessorNode(x) {
    return x && x.isAccessorNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isArrayNode(x) {
    return x && x.isArrayNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isAssignmentNode(x) {
    return x && x.isAssignmentNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isBlockNode(x) {
    return x && x.isBlockNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isConditionalNode(x) {
    return x && x.isConditionalNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isConstantNode(x) {
    return x && x.isConstantNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isFunctionAssignmentNode(x) {
    return x && x.isFunctionAssignmentNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isFunctionNode(x) {
    return x && x.isFunctionNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isIndexNode(x) {
    return x && x.isIndexNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isNode(x) {
    return x && x.isNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isObjectNode(x) {
    return x && x.isObjectNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isOperatorNode(x) {
    return x && x.isOperatorNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isParenthesisNode(x) {
    return x && x.isParenthesisNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isRangeNode(x) {
    return x && x.isRangeNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isSymbolNode(x) {
    return x && x.isSymbolNode === true && x.constructor.prototype.isNode === true || false;
  }
  function isChain(x) {
    return x && x.constructor.prototype.isChain === true || false;
  }
  function typeOf(x) {
    var t = typeof x;
    if (t === "object") {
      if (x === null) return "null";
      if (Array.isArray(x)) return "Array";
      if (x instanceof Date) return "Date";
      if (x instanceof RegExp) return "RegExp";
      if (isBigNumber(x)) return "BigNumber";
      if (isComplex(x)) return "Complex";
      if (isFraction(x)) return "Fraction";
      if (isMatrix(x)) return "Matrix";
      if (isUnit(x)) return "Unit";
      if (isIndex(x)) return "Index";
      if (isRange(x)) return "Range";
      if (isResultSet(x)) return "ResultSet";
      if (isNode(x)) return x.type;
      if (isChain(x)) return "Chain";
      if (isHelp(x)) return "Help";
      return "Object";
    }
    if (t === "function") return "Function";
    return t;
  }
  function clone(x) {
    var type = typeof x;
    if (type === "number" || type === "string" || type === "boolean" || x === null || x === void 0) {
      return x;
    }
    if (typeof x.clone === "function") {
      return x.clone();
    }
    if (Array.isArray(x)) {
      return x.map(function(value) {
        return clone(value);
      });
    }
    if (x instanceof Date) return new Date(x.valueOf());
    if (isBigNumber(x)) return x;
    if (x instanceof RegExp) throw new TypeError("Cannot clone " + x);
    return mapObject(x, clone);
  }
  function mapObject(object, callback) {
    var clone2 = {};
    for (var key in object) {
      if (hasOwnProperty(object, key)) {
        clone2[key] = callback(object[key]);
      }
    }
    return clone2;
  }
  function deepExtend(a, b) {
    if (Array.isArray(b)) {
      throw new TypeError("Arrays are not supported by deepExtend");
    }
    for (var prop in b) {
      if (hasOwnProperty(b, prop) && !(prop in Object.prototype) && !(prop in Function.prototype)) {
        if (b[prop] && b[prop].constructor === Object) {
          if (a[prop] === void 0) {
            a[prop] = {};
          }
          if (a[prop] && a[prop].constructor === Object) {
            deepExtend(a[prop], b[prop]);
          } else {
            a[prop] = b[prop];
          }
        } else if (Array.isArray(b[prop])) {
          throw new TypeError("Arrays are not supported by deepExtend");
        } else {
          a[prop] = b[prop];
        }
      }
    }
    return a;
  }
  function deepStrictEqual(a, b) {
    var prop, i, len;
    if (Array.isArray(a)) {
      if (!Array.isArray(b)) {
        return false;
      }
      if (a.length !== b.length) {
        return false;
      }
      for (i = 0, len = a.length; i < len; i++) {
        if (!deepStrictEqual(a[i], b[i])) {
          return false;
        }
      }
      return true;
    } else if (typeof a === "function") {
      return a === b;
    } else if (a instanceof Object) {
      if (Array.isArray(b) || !(b instanceof Object)) {
        return false;
      }
      for (prop in a) {
        if (!(prop in b) || !deepStrictEqual(a[prop], b[prop])) {
          return false;
        }
      }
      for (prop in b) {
        if (!(prop in a)) {
          return false;
        }
      }
      return true;
    } else {
      return a === b;
    }
  }
  function deepFlatten(nestedObject) {
    var flattenedObject = {};
    _deepFlatten(nestedObject, flattenedObject);
    return flattenedObject;
  }
  function _deepFlatten(nestedObject, flattenedObject) {
    for (var prop in nestedObject) {
      if (hasOwnProperty(nestedObject, prop)) {
        var value = nestedObject[prop];
        if (typeof value === "object" && value !== null) {
          _deepFlatten(value, flattenedObject);
        } else {
          flattenedObject[prop] = value;
        }
      }
    }
  }
  function lazy(object, prop, valueResolver) {
    var _uninitialized = true;
    var _value;
    Object.defineProperty(object, prop, {
      get: function get() {
        if (_uninitialized) {
          _value = valueResolver();
          _uninitialized = false;
        }
        return _value;
      },
      set: function set(value) {
        _value = value;
        _uninitialized = false;
      },
      configurable: true,
      enumerable: true
    });
  }
  function hasOwnProperty(object, property) {
    return object && Object.hasOwnProperty.call(object, property);
  }
  function isLegacyFactory(object) {
    return object && typeof object.factory === "function";
  }
  function pickShallow(object, properties2) {
    var copy = {};
    for (var i = 0; i < properties2.length; i++) {
      var key = properties2[i];
      var value = object[key];
      if (value !== void 0) {
        copy[key] = value;
      }
    }
    return copy;
  }
  function values(object) {
    return Object.keys(object).map((key) => object[key]);
  }
  var MATRIX_OPTIONS = ["Matrix", "Array"];
  var NUMBER_OPTIONS = ["number", "BigNumber", "Fraction"];
  function configFactory(config, emit) {
    function _config(options) {
      if (options) {
        var prev = mapObject(config, clone);
        validateOption(options, "matrix", MATRIX_OPTIONS);
        validateOption(options, "number", NUMBER_OPTIONS);
        deepExtend(config, options);
        var curr = mapObject(config, clone);
        var changes = mapObject(options, clone);
        emit("config", curr, prev, changes);
        return curr;
      } else {
        return mapObject(config, clone);
      }
    }
    _config.MATRIX_OPTIONS = MATRIX_OPTIONS;
    _config.NUMBER_OPTIONS = NUMBER_OPTIONS;
    Object.keys(DEFAULT_CONFIG).forEach((key) => {
      Object.defineProperty(_config, key, {
        get: () => config[key],
        enumerable: true,
        configurable: true
      });
    });
    return _config;
  }
  function contains$1(array, item) {
    return array.indexOf(item) !== -1;
  }
  function validateOption(options, name2, values2) {
    if (options[name2] !== void 0 && !contains$1(values2, options[name2])) {
      console.warn('Warning: Unknown value "' + options[name2] + '" for configuration option "' + name2 + '". Available options: ' + values2.map((value) => JSON.stringify(value)).join(", ") + ".");
    }
  }
  function isInteger(value) {
    if (typeof value === "boolean") {
      return true;
    }
    return isFinite(value) ? value === Math.round(value) : false;
  }
  var sign = Math.sign || function(x) {
    if (x > 0) {
      return 1;
    } else if (x < 0) {
      return -1;
    } else {
      return 0;
    }
  };
  var log2 = Math.log2 || function log22(x) {
    return Math.log(x) / Math.LN2;
  };
  var log10 = Math.log10 || function log102(x) {
    return Math.log(x) / Math.LN10;
  };
  var log1p = Math.log1p || function(x) {
    return Math.log(x + 1);
  };
  var cbrt = Math.cbrt || function cbrt2(x) {
    if (x === 0) {
      return x;
    }
    var negate = x < 0;
    var result;
    if (negate) {
      x = -x;
    }
    if (isFinite(x)) {
      result = Math.exp(Math.log(x) / 3);
      result = (x / (result * result) + 2 * result) / 3;
    } else {
      result = x;
    }
    return negate ? -result : result;
  };
  var expm1 = Math.expm1 || function expm12(x) {
    return x >= 2e-4 || x <= -2e-4 ? Math.exp(x) - 1 : x + x * x / 2 + x * x * x / 6;
  };
  function formatNumberToBase(n, base, size) {
    var prefixes = {
      2: "0b",
      8: "0o",
      16: "0x"
    };
    var prefix = prefixes[base];
    var suffix = "";
    if (size) {
      if (size < 1) {
        throw new Error("size must be in greater than 0");
      }
      if (!isInteger(size)) {
        throw new Error("size must be an integer");
      }
      if (n > __pow(2, size - 1) - 1 || n < -__pow(2, size - 1)) {
        throw new Error("Value must be in range [-2^".concat(size - 1, ", 2^").concat(size - 1, "-1]"));
      }
      if (!isInteger(n)) {
        throw new Error("Value must be an integer");
      }
      if (n < 0) {
        n = n + __pow(2, size);
      }
      suffix = "i".concat(size);
    }
    var sign2 = "";
    if (n < 0) {
      n = -n;
      sign2 = "-";
    }
    return "".concat(sign2).concat(prefix).concat(n.toString(base)).concat(suffix);
  }
  function format$2(value, options) {
    if (typeof options === "function") {
      return options(value);
    }
    if (value === Infinity) {
      return "Infinity";
    } else if (value === -Infinity) {
      return "-Infinity";
    } else if (isNaN(value)) {
      return "NaN";
    }
    var notation = "auto";
    var precision;
    var wordSize;
    if (options) {
      if (options.notation) {
        notation = options.notation;
      }
      if (isNumber(options)) {
        precision = options;
      } else if (isNumber(options.precision)) {
        precision = options.precision;
      }
      if (options.wordSize) {
        wordSize = options.wordSize;
        if (typeof wordSize !== "number") {
          throw new Error('Option "wordSize" must be a number');
        }
      }
    }
    switch (notation) {
      case "fixed":
        return toFixed$1(value, precision);
      case "exponential":
        return toExponential$1(value, precision);
      case "engineering":
        return toEngineering$1(value, precision);
      case "bin":
        return formatNumberToBase(value, 2, wordSize);
      case "oct":
        return formatNumberToBase(value, 8, wordSize);
      case "hex":
        return formatNumberToBase(value, 16, wordSize);
      case "auto":
        return toPrecision(value, precision, options && options).replace(/((\.\d*?)(0+))($|e)/, function() {
          var digits2 = arguments[2];
          var e = arguments[4];
          return digits2 !== "." ? digits2 + e : e;
        });
      default:
        throw new Error('Unknown notation "' + notation + '". Choose "auto", "exponential", "fixed", "bin", "oct", or "hex.');
    }
  }
  function splitNumber(value) {
    var match = String(value).toLowerCase().match(/^(-?)(\d+\.?\d*)(e([+-]?\d+))?$/);
    if (!match) {
      throw new SyntaxError("Invalid number " + value);
    }
    var sign2 = match[1];
    var digits2 = match[2];
    var exponent = parseFloat(match[4] || "0");
    var dot = digits2.indexOf(".");
    exponent += dot !== -1 ? dot - 1 : digits2.length - 1;
    var coefficients = digits2.replace(".", "").replace(/^0*/, function(zeros2) {
      exponent -= zeros2.length;
      return "";
    }).replace(/0*$/, "").split("").map(function(d) {
      return parseInt(d);
    });
    if (coefficients.length === 0) {
      coefficients.push(0);
      exponent++;
    }
    return {
      sign: sign2,
      coefficients,
      exponent
    };
  }
  function toEngineering$1(value, precision) {
    if (isNaN(value) || !isFinite(value)) {
      return String(value);
    }
    var split = splitNumber(value);
    var rounded = roundDigits(split, precision);
    var e = rounded.exponent;
    var c = rounded.coefficients;
    var newExp = e % 3 === 0 ? e : e < 0 ? e - 3 - e % 3 : e - e % 3;
    if (isNumber(precision)) {
      while (precision > c.length || e - newExp + 1 > c.length) {
        c.push(0);
      }
    } else {
      var missingZeros = Math.abs(e - newExp) - (c.length - 1);
      for (var i = 0; i < missingZeros; i++) {
        c.push(0);
      }
    }
    var expDiff = Math.abs(e - newExp);
    var decimalIdx = 1;
    while (expDiff > 0) {
      decimalIdx++;
      expDiff--;
    }
    var decimals = c.slice(decimalIdx).join("");
    var decimalVal = isNumber(precision) && decimals.length || decimals.match(/[1-9]/) ? "." + decimals : "";
    var str = c.slice(0, decimalIdx).join("") + decimalVal + "e" + (e >= 0 ? "+" : "") + newExp.toString();
    return rounded.sign + str;
  }
  function toFixed$1(value, precision) {
    if (isNaN(value) || !isFinite(value)) {
      return String(value);
    }
    var splitValue = splitNumber(value);
    var rounded = typeof precision === "number" ? roundDigits(splitValue, splitValue.exponent + 1 + precision) : splitValue;
    var c = rounded.coefficients;
    var p = rounded.exponent + 1;
    var pp = p + (precision || 0);
    if (c.length < pp) {
      c = c.concat(zeros(pp - c.length));
    }
    if (p < 0) {
      c = zeros(-p + 1).concat(c);
      p = 1;
    }
    if (p < c.length) {
      c.splice(p, 0, p === 0 ? "0." : ".");
    }
    return rounded.sign + c.join("");
  }
  function toExponential$1(value, precision) {
    if (isNaN(value) || !isFinite(value)) {
      return String(value);
    }
    var split = splitNumber(value);
    var rounded = precision ? roundDigits(split, precision) : split;
    var c = rounded.coefficients;
    var e = rounded.exponent;
    if (c.length < precision) {
      c = c.concat(zeros(precision - c.length));
    }
    var first = c.shift();
    return rounded.sign + first + (c.length > 0 ? "." + c.join("") : "") + "e" + (e >= 0 ? "+" : "") + e;
  }
  function toPrecision(value, precision, options) {
    if (isNaN(value) || !isFinite(value)) {
      return String(value);
    }
    var lowerExp = options && options.lowerExp !== void 0 ? options.lowerExp : -3;
    var upperExp = options && options.upperExp !== void 0 ? options.upperExp : 5;
    var split = splitNumber(value);
    var rounded = precision ? roundDigits(split, precision) : split;
    if (rounded.exponent < lowerExp || rounded.exponent >= upperExp) {
      return toExponential$1(value, precision);
    } else {
      var c = rounded.coefficients;
      var e = rounded.exponent;
      if (c.length < precision) {
        c = c.concat(zeros(precision - c.length));
      }
      c = c.concat(zeros(e - c.length + 1 + (c.length < precision ? precision - c.length : 0)));
      c = zeros(-e).concat(c);
      var dot = e > 0 ? e : 0;
      if (dot < c.length - 1) {
        c.splice(dot + 1, 0, ".");
      }
      return rounded.sign + c.join("");
    }
  }
  function roundDigits(split, precision) {
    var rounded = {
      sign: split.sign,
      coefficients: split.coefficients,
      exponent: split.exponent
    };
    var c = rounded.coefficients;
    while (precision <= 0) {
      c.unshift(0);
      rounded.exponent++;
      precision++;
    }
    if (c.length > precision) {
      var removed = c.splice(precision, c.length - precision);
      if (removed[0] >= 5) {
        var i = precision - 1;
        c[i]++;
        while (c[i] === 10) {
          c.pop();
          if (i === 0) {
            c.unshift(0);
            rounded.exponent++;
            i++;
          }
          i--;
          c[i]++;
        }
      }
    }
    return rounded;
  }
  function zeros(length) {
    var arr = [];
    for (var i = 0; i < length; i++) {
      arr.push(0);
    }
    return arr;
  }
  function digits(value) {
    return value.toExponential().replace(/e.*$/, "").replace(/^0\.?0*|\./, "").length;
  }
  var acosh = Math.acosh || function(x) {
    return Math.log(Math.sqrt(x * x - 1) + x);
  };
  var asinh = Math.asinh || function(x) {
    return Math.log(Math.sqrt(x * x + 1) + x);
  };
  var atanh = Math.atanh || function(x) {
    return Math.log((1 + x) / (1 - x)) / 2;
  };
  var cosh = Math.cosh || function(x) {
    return (Math.exp(x) + Math.exp(-x)) / 2;
  };
  var sinh = Math.sinh || function(x) {
    return (Math.exp(x) - Math.exp(-x)) / 2;
  };
  var tanh = Math.tanh || function(x) {
    var e = Math.exp(2 * x);
    return (e - 1) / (e + 1);
  };
  var n1$4 = "number";
  var n2$3 = "number, number";
  function absNumber(a) {
    return Math.abs(a);
  }
  absNumber.signature = n1$4;
  function addNumber(a, b) {
    return a + b;
  }
  addNumber.signature = n2$3;
  function subtractNumber(a, b) {
    return a - b;
  }
  subtractNumber.signature = n2$3;
  function multiplyNumber(a, b) {
    return a * b;
  }
  multiplyNumber.signature = n2$3;
  function divideNumber(a, b) {
    return a / b;
  }
  divideNumber.signature = n2$3;
  function unaryMinusNumber(x) {
    return -x;
  }
  unaryMinusNumber.signature = n1$4;
  function unaryPlusNumber(x) {
    return x;
  }
  unaryPlusNumber.signature = n1$4;
  function cbrtNumber(x) {
    return cbrt(x);
  }
  cbrtNumber.signature = n1$4;
  function cubeNumber(x) {
    return x * x * x;
  }
  cubeNumber.signature = n1$4;
  function expNumber(x) {
    return Math.exp(x);
  }
  expNumber.signature = n1$4;
  function expm1Number(x) {
    return expm1(x);
  }
  expm1Number.signature = n1$4;
  function gcdNumber(a, b) {
    if (!isInteger(a) || !isInteger(b)) {
      throw new Error("Parameters in function gcd must be integer numbers");
    }
    var r;
    while (b !== 0) {
      r = a % b;
      a = b;
      b = r;
    }
    return a < 0 ? -a : a;
  }
  gcdNumber.signature = n2$3;
  function lcmNumber(a, b) {
    if (!isInteger(a) || !isInteger(b)) {
      throw new Error("Parameters in function lcm must be integer numbers");
    }
    if (a === 0 || b === 0) {
      return 0;
    }
    var t;
    var prod = a * b;
    while (b !== 0) {
      t = b;
      b = a % t;
      a = t;
    }
    return Math.abs(prod / a);
  }
  lcmNumber.signature = n2$3;
  function log10Number(x) {
    return log10(x);
  }
  log10Number.signature = n1$4;
  function log2Number(x) {
    return log2(x);
  }
  log2Number.signature = n1$4;
  function log1pNumber(x) {
    return log1p(x);
  }
  log1pNumber.signature = n1$4;
  function modNumber(x, y) {
    if (y > 0) {
      return x - y * Math.floor(x / y);
    } else if (y === 0) {
      return x;
    } else {
      throw new Error("Cannot calculate mod for a negative divisor");
    }
  }
  modNumber.signature = n2$3;
  function signNumber(x) {
    return sign(x);
  }
  signNumber.signature = n1$4;
  function sqrtNumber(x) {
    return Math.sqrt(x);
  }
  sqrtNumber.signature = n1$4;
  function squareNumber(x) {
    return x * x;
  }
  squareNumber.signature = n1$4;
  function xgcdNumber(a, b) {
    var t;
    var q;
    var r;
    var x = 0;
    var lastx = 1;
    var y = 1;
    var lasty = 0;
    if (!isInteger(a) || !isInteger(b)) {
      throw new Error("Parameters in function xgcd must be integer numbers");
    }
    while (b) {
      q = Math.floor(a / b);
      r = a - q * b;
      t = x;
      x = lastx - q * x;
      lastx = t;
      t = y;
      y = lasty - q * y;
      lasty = t;
      a = b;
      b = r;
    }
    var res;
    if (a < 0) {
      res = [-a, -lastx, -lasty];
    } else {
      res = [a, a ? lastx : 0, lasty];
    }
    return res;
  }
  xgcdNumber.signature = n2$3;
  function powNumber(x, y) {
    if (x * x < 1 && y === Infinity || x * x > 1 && y === -Infinity) {
      return 0;
    }
    return Math.pow(x, y);
  }
  powNumber.signature = n2$3;
  function normNumber(x) {
    return Math.abs(x);
  }
  normNumber.signature = n1$4;
  var n1$3 = "number";
  var n2$2 = "number, number";
  function bitAndNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function bitAnd");
    }
    return x & y;
  }
  bitAndNumber.signature = n2$2;
  function bitNotNumber(x) {
    if (!isInteger(x)) {
      throw new Error("Integer expected in function bitNot");
    }
    return ~x;
  }
  bitNotNumber.signature = n1$3;
  function bitOrNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function bitOr");
    }
    return x | y;
  }
  bitOrNumber.signature = n2$2;
  function bitXorNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function bitXor");
    }
    return x ^ y;
  }
  bitXorNumber.signature = n2$2;
  function leftShiftNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function leftShift");
    }
    return x << y;
  }
  leftShiftNumber.signature = n2$2;
  function rightArithShiftNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function rightArithShift");
    }
    return x >> y;
  }
  rightArithShiftNumber.signature = n2$2;
  function rightLogShiftNumber(x, y) {
    if (!isInteger(x) || !isInteger(y)) {
      throw new Error("Integers expected in function rightLogShift");
    }
    return x >>> y;
  }
  rightLogShiftNumber.signature = n2$2;
  function product(i, n) {
    if (n < i) {
      return 1;
    }
    if (n === i) {
      return n;
    }
    var half = n + i >> 1;
    return product(i, half) * product(half + 1, n);
  }
  function combinationsNumber(n, k) {
    if (!isInteger(n) || n < 0) {
      throw new TypeError("Positive integer value expected in function combinations");
    }
    if (!isInteger(k) || k < 0) {
      throw new TypeError("Positive integer value expected in function combinations");
    }
    if (k > n) {
      throw new TypeError("k must be less than or equal to n");
    }
    var nMinusk = n - k;
    var answer = 1;
    var firstnumerator = k < nMinusk ? nMinusk + 1 : k + 1;
    var nextdivisor = 2;
    var lastdivisor = k < nMinusk ? k : nMinusk;
    for (var nextnumerator = firstnumerator; nextnumerator <= n; ++nextnumerator) {
      answer *= nextnumerator;
      while (nextdivisor <= lastdivisor && answer % nextdivisor === 0) {
        answer /= nextdivisor;
        ++nextdivisor;
      }
    }
    if (nextdivisor <= lastdivisor) {
      answer /= product(nextdivisor, lastdivisor);
    }
    return answer;
  }
  combinationsNumber.signature = "number, number";
  var n1$2 = "number";
  var n2$1 = "number, number";
  function notNumber(x) {
    return !x;
  }
  notNumber.signature = n1$2;
  function orNumber(x, y) {
    return !!(x || y);
  }
  orNumber.signature = n2$1;
  function xorNumber(x, y) {
    return !!x !== !!y;
  }
  xorNumber.signature = n2$1;
  function andNumber(x, y) {
    return !!(x && y);
  }
  andNumber.signature = n2$1;
  function gammaNumber(n) {
    var x;
    if (isInteger(n)) {
      if (n <= 0) {
        return isFinite(n) ? Infinity : NaN;
      }
      if (n > 171) {
        return Infinity;
      }
      return product(1, n - 1);
    }
    if (n < 0.5) {
      return Math.PI / (Math.sin(Math.PI * n) * gammaNumber(1 - n));
    }
    if (n >= 171.35) {
      return Infinity;
    }
    if (n > 85) {
      var twoN = n * n;
      var threeN = twoN * n;
      var fourN = threeN * n;
      var fiveN = fourN * n;
      return Math.sqrt(2 * Math.PI / n) * Math.pow(n / Math.E, n) * (1 + 1 / (12 * n) + 1 / (288 * twoN) - 139 / (51840 * threeN) - 571 / (2488320 * fourN) + 163879 / (209018880 * fiveN) + 5246819 / (75246796800 * fiveN * n));
    }
    --n;
    x = gammaP[0];
    for (var i = 1; i < gammaP.length; ++i) {
      x += gammaP[i] / (n + i);
    }
    var t = n + gammaG + 0.5;
    return Math.sqrt(2 * Math.PI) * Math.pow(t, n + 0.5) * Math.exp(-t) * x;
  }
  gammaNumber.signature = "number";
  var gammaG = 4.7421875;
  var gammaP = [0.9999999999999971, 57.15623566586292, -59.59796035547549, 14.136097974741746, -0.4919138160976202, 3399464998481189e-20, 4652362892704858e-20, -9837447530487956e-20, 1580887032249125e-19, -21026444172410488e-20, 21743961811521265e-20, -1643181065367639e-19, 8441822398385275e-20, -26190838401581408e-21, 36899182659531625e-22];
  var lnSqrt2PI = 0.9189385332046728;
  var lgammaG = 5;
  var lgammaN = 7;
  var lgammaSeries = [1.000000000190015, 76.18009172947146, -86.50532032941678, 24.01409824083091, -1.231739572450155, 0.001208650973866179, -5395239384953e-18];
  function lgammaNumber(n) {
    if (n < 0) return NaN;
    if (n === 0) return Infinity;
    if (!isFinite(n)) return n;
    if (n < 0.5) {
      return Math.log(Math.PI / Math.sin(Math.PI * n)) - lgammaNumber(1 - n);
    }
    n = n - 1;
    var base = n + lgammaG + 0.5;
    var sum = lgammaSeries[0];
    for (var i = lgammaN - 1; i >= 1; i--) {
      sum += lgammaSeries[i] / (n + i);
    }
    return lnSqrt2PI + (n + 0.5) * Math.log(base) - base + Math.log(sum);
  }
  lgammaNumber.signature = "number";
  var n1$1 = "number";
  var n2 = "number, number";
  function acosNumber(x) {
    return Math.acos(x);
  }
  acosNumber.signature = n1$1;
  function acoshNumber(x) {
    return acosh(x);
  }
  acoshNumber.signature = n1$1;
  function acotNumber(x) {
    return Math.atan(1 / x);
  }
  acotNumber.signature = n1$1;
  function acothNumber(x) {
    return isFinite(x) ? (Math.log((x + 1) / x) + Math.log(x / (x - 1))) / 2 : 0;
  }
  acothNumber.signature = n1$1;
  function acscNumber(x) {
    return Math.asin(1 / x);
  }
  acscNumber.signature = n1$1;
  function acschNumber(x) {
    var xInv = 1 / x;
    return Math.log(xInv + Math.sqrt(xInv * xInv + 1));
  }
  acschNumber.signature = n1$1;
  function asecNumber(x) {
    return Math.acos(1 / x);
  }
  asecNumber.signature = n1$1;
  function asechNumber(x) {
    var xInv = 1 / x;
    var ret = Math.sqrt(xInv * xInv - 1);
    return Math.log(ret + xInv);
  }
  asechNumber.signature = n1$1;
  function asinNumber(x) {
    return Math.asin(x);
  }
  asinNumber.signature = n1$1;
  function asinhNumber(x) {
    return asinh(x);
  }
  asinhNumber.signature = n1$1;
  function atanNumber(x) {
    return Math.atan(x);
  }
  atanNumber.signature = n1$1;
  function atan2Number(y, x) {
    return Math.atan2(y, x);
  }
  atan2Number.signature = n2;
  function atanhNumber(x) {
    return atanh(x);
  }
  atanhNumber.signature = n1$1;
  function cosNumber(x) {
    return Math.cos(x);
  }
  cosNumber.signature = n1$1;
  function coshNumber(x) {
    return cosh(x);
  }
  coshNumber.signature = n1$1;
  function cotNumber(x) {
    return 1 / Math.tan(x);
  }
  cotNumber.signature = n1$1;
  function cothNumber(x) {
    var e = Math.exp(2 * x);
    return (e + 1) / (e - 1);
  }
  cothNumber.signature = n1$1;
  function cscNumber(x) {
    return 1 / Math.sin(x);
  }
  cscNumber.signature = n1$1;
  function cschNumber(x) {
    if (x === 0) {
      return Number.POSITIVE_INFINITY;
    } else {
      return Math.abs(2 / (Math.exp(x) - Math.exp(-x))) * sign(x);
    }
  }
  cschNumber.signature = n1$1;
  function secNumber(x) {
    return 1 / Math.cos(x);
  }
  secNumber.signature = n1$1;
  function sechNumber(x) {
    return 2 / (Math.exp(x) + Math.exp(-x));
  }
  sechNumber.signature = n1$1;
  function sinNumber(x) {
    return Math.sin(x);
  }
  sinNumber.signature = n1$1;
  function sinhNumber(x) {
    return sinh(x);
  }
  sinhNumber.signature = n1$1;
  function tanNumber(x) {
    return Math.tan(x);
  }
  tanNumber.signature = n1$1;
  function tanhNumber(x) {
    return tanh(x);
  }
  tanhNumber.signature = n1$1;
  var n1 = "number";
  function isIntegerNumber(x) {
    return isInteger(x);
  }
  isIntegerNumber.signature = n1;
  function isNegativeNumber(x) {
    return x < 0;
  }
  isNegativeNumber.signature = n1;
  function isPositiveNumber(x) {
    return x > 0;
  }
  isPositiveNumber.signature = n1;
  function isZeroNumber(x) {
    return x === 0;
  }
  isZeroNumber.signature = n1;
  function isNaNNumber(x) {
    return Number.isNaN(x);
  }
  isNaNNumber.signature = n1;
  function formatBigNumberToBase(n, base, size) {
    var BigNumberCtor = n.constructor;
    var big2 = new BigNumberCtor(2);
    var suffix = "";
    if (size) {
      if (size < 1) {
        throw new Error("size must be in greater than 0");
      }
      if (!isInteger(size)) {
        throw new Error("size must be an integer");
      }
      if (n.greaterThan(big2.pow(size - 1).sub(1)) || n.lessThan(big2.pow(size - 1).mul(-1))) {
        throw new Error("Value must be in range [-2^".concat(size - 1, ", 2^").concat(size - 1, "-1]"));
      }
      if (!n.isInteger()) {
        throw new Error("Value must be an integer");
      }
      if (n.lessThan(0)) {
        n = n.add(big2.pow(size));
      }
      suffix = "i".concat(size);
    }
    switch (base) {
      case 2:
        return "".concat(n.toBinary()).concat(suffix);
      case 8:
        return "".concat(n.toOctal()).concat(suffix);
      case 16:
        return "".concat(n.toHexadecimal()).concat(suffix);
      default:
        throw new Error("Base ".concat(base, " not supported "));
    }
  }
  function format$1(value, options) {
    if (typeof options === "function") {
      return options(value);
    }
    if (!value.isFinite()) {
      return value.isNaN() ? "NaN" : value.gt(0) ? "Infinity" : "-Infinity";
    }
    var notation = "auto";
    var precision;
    var wordSize;
    if (options !== void 0) {
      if (options.notation) {
        notation = options.notation;
      }
      if (typeof options === "number") {
        precision = options;
      } else if (options.precision) {
        precision = options.precision;
      }
      if (options.wordSize) {
        wordSize = options.wordSize;
        if (typeof wordSize !== "number") {
          throw new Error('Option "wordSize" must be a number');
        }
      }
    }
    switch (notation) {
      case "fixed":
        return toFixed(value, precision);
      case "exponential":
        return toExponential(value, precision);
      case "engineering":
        return toEngineering(value, precision);
      case "bin":
        return formatBigNumberToBase(value, 2, wordSize);
      case "oct":
        return formatBigNumberToBase(value, 8, wordSize);
      case "hex":
        return formatBigNumberToBase(value, 16, wordSize);
      case "auto": {
        var lowerExp = options && options.lowerExp !== void 0 ? options.lowerExp : -3;
        var upperExp = options && options.upperExp !== void 0 ? options.upperExp : 5;
        if (value.isZero()) return "0";
        var str;
        var rounded = value.toSignificantDigits(precision);
        var exp = rounded.e;
        if (exp >= lowerExp && exp < upperExp) {
          str = rounded.toFixed();
        } else {
          str = toExponential(value, precision);
        }
        return str.replace(/((\.\d*?)(0+))($|e)/, function() {
          var digits2 = arguments[2];
          var e = arguments[4];
          return digits2 !== "." ? digits2 + e : e;
        });
      }
      default:
        throw new Error('Unknown notation "' + notation + '". Choose "auto", "exponential", "fixed", "bin", "oct", or "hex.');
    }
  }
  function toEngineering(value, precision) {
    var e = value.e;
    var newExp = e % 3 === 0 ? e : e < 0 ? e - 3 - e % 3 : e - e % 3;
    var valueWithoutExp = value.mul(Math.pow(10, -newExp));
    var valueStr = valueWithoutExp.toPrecision(precision);
    if (valueStr.indexOf("e") !== -1) {
      valueStr = valueWithoutExp.toString();
    }
    return valueStr + "e" + (e >= 0 ? "+" : "") + newExp.toString();
  }
  function toExponential(value, precision) {
    if (precision !== void 0) {
      return value.toExponential(precision - 1);
    } else {
      return value.toExponential();
    }
  }
  function toFixed(value, precision) {
    return value.toFixed(precision);
  }
  function format(value, options) {
    var result = _format(value, options);
    if (options && typeof options === "object" && "truncate" in options && result.length > options.truncate) {
      return result.substring(0, options.truncate - 3) + "...";
    }
    return result;
  }
  function _format(value, options) {
    if (typeof value === "number") {
      return format$2(value, options);
    }
    if (isBigNumber(value)) {
      return format$1(value, options);
    }
    if (looksLikeFraction(value)) {
      if (!options || options.fraction !== "decimal") {
        return value.s * value.n + "/" + value.d;
      } else {
        return value.toString();
      }
    }
    if (Array.isArray(value)) {
      return formatArray(value, options);
    }
    if (isString(value)) {
      return '"' + value + '"';
    }
    if (typeof value === "function") {
      return value.syntax ? String(value.syntax) : "function";
    }
    if (value && typeof value === "object") {
      if (typeof value.format === "function") {
        return value.format(options);
      } else if (value && value.toString(options) !== {}.toString()) {
        return value.toString(options);
      } else {
        var entries = Object.keys(value).map((key) => {
          return '"' + key + '": ' + format(value[key], options);
        });
        return "{" + entries.join(", ") + "}";
      }
    }
    return String(value);
  }
  function stringify(value) {
    var text = String(value);
    var escaped = "";
    var i = 0;
    while (i < text.length) {
      var c = text.charAt(i);
      if (c === "\\") {
        escaped += c;
        i++;
        c = text.charAt(i);
        if (c === "" || '"\\/bfnrtu'.indexOf(c) === -1) {
          escaped += "\\";
        }
        escaped += c;
      } else if (c === '"') {
        escaped += '\\"';
      } else {
        escaped += c;
      }
      i++;
    }
    return '"' + escaped + '"';
  }
  function escape(value) {
    var text = String(value);
    text = text.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    return text;
  }
  function formatArray(array, options) {
    if (Array.isArray(array)) {
      var str = "[";
      var len = array.length;
      for (var i = 0; i < len; i++) {
        if (i !== 0) {
          str += ", ";
        }
        str += formatArray(array[i], options);
      }
      str += "]";
      return str;
    } else {
      return format(array, options);
    }
  }
  function looksLikeFraction(value) {
    return value && typeof value === "object" && typeof value.s === "number" && typeof value.n === "number" && typeof value.d === "number" || false;
  }
  function DimensionError(actual, expected, relation) {
    if (!(this instanceof DimensionError)) {
      throw new SyntaxError("Constructor must be called with the new operator");
    }
    this.actual = actual;
    this.expected = expected;
    this.relation = relation;
    this.message = "Dimension mismatch (" + (Array.isArray(actual) ? "[" + actual.join(", ") + "]" : actual) + " " + (this.relation || "!=") + " " + (Array.isArray(expected) ? "[" + expected.join(", ") + "]" : expected) + ")";
    this.stack = new Error().stack;
  }
  DimensionError.prototype = new RangeError();
  DimensionError.prototype.constructor = RangeError;
  DimensionError.prototype.name = "DimensionError";
  DimensionError.prototype.isDimensionError = true;
  function IndexError(index, min, max) {
    if (!(this instanceof IndexError)) {
      throw new SyntaxError("Constructor must be called with the new operator");
    }
    this.index = index;
    if (arguments.length < 3) {
      this.min = 0;
      this.max = min;
    } else {
      this.min = min;
      this.max = max;
    }
    if (this.min !== void 0 && this.index < this.min) {
      this.message = "Index out of range (" + this.index + " < " + this.min + ")";
    } else if (this.max !== void 0 && this.index >= this.max) {
      this.message = "Index out of range (" + this.index + " > " + (this.max - 1) + ")";
    } else {
      this.message = "Index out of range (" + this.index + ")";
    }
    this.stack = new Error().stack;
  }
  IndexError.prototype = new RangeError();
  IndexError.prototype.constructor = RangeError;
  IndexError.prototype.name = "IndexError";
  IndexError.prototype.isIndexError = true;
  function arraySize(x) {
    var s = [];
    while (Array.isArray(x)) {
      s.push(x.length);
      x = x[0];
    }
    return s;
  }
  function map(array, callback) {
    return Array.prototype.map.call(array, callback);
  }
  function forEach(array, callback) {
    Array.prototype.forEach.call(array, callback);
  }
  function join(array, separator) {
    return Array.prototype.join.call(array, separator);
  }
  function contains(array, item) {
    return array.indexOf(item) !== -1;
  }
  function factory(name2, dependencies2, create2, meta) {
    function assertAndCreate(scope) {
      var deps = pickShallow(scope, dependencies2.map(stripOptionalNotation));
      assertDependencies(name2, dependencies2, scope);
      return create2(deps);
    }
    assertAndCreate.isFactory = true;
    assertAndCreate.fn = name2;
    assertAndCreate.dependencies = dependencies2.slice().sort();
    if (meta) {
      assertAndCreate.meta = meta;
    }
    return assertAndCreate;
  }
  function isFactory(obj) {
    return typeof obj === "function" && typeof obj.fn === "string" && Array.isArray(obj.dependencies);
  }
  function assertDependencies(name2, dependencies2, scope) {
    var allDefined = dependencies2.filter((dependency) => !isOptionalDependency(dependency)).every((dependency) => scope[dependency] !== void 0);
    if (!allDefined) {
      var missingDependencies = dependencies2.filter((dependency) => scope[dependency] === void 0);
      throw new Error('Cannot create function "'.concat(name2, '", ') + "some dependencies are missing: ".concat(missingDependencies.map((d) => '"'.concat(d, '"')).join(", "), "."));
    }
  }
  function isOptionalDependency(dependency) {
    return dependency && dependency[0] === "?";
  }
  function stripOptionalNotation(dependency) {
    return dependency && dependency[0] === "?" ? dependency.slice(1) : dependency;
  }
  function noBignumber() {
    throw new Error('No "bignumber" implementation available');
  }
  function noFraction() {
    throw new Error('No "fraction" implementation available');
  }
  function noMatrix() {
    throw new Error('No "matrix" implementation available');
  }
  function noSubset() {
    throw new Error('No "matrix" implementation available');
  }
  function getDefaultExportFromCjs(x) {
    return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, "default") ? x["default"] : x;
  }
  var typedFunction$2 = { exports: {} };
  var typedFunction$1 = typedFunction$2.exports;
  var hasRequiredTypedFunction;
  function requireTypedFunction() {
    if (hasRequiredTypedFunction) return typedFunction$2.exports;
    hasRequiredTypedFunction = 1;
    (function(module2, exports3) {
      (function(root, factory2) {
        {
          module2.exports = factory2();
        }
      })(typedFunction$1, function() {
        function ok() {
          return true;
        }
        function notOk() {
          return false;
        }
        function undef() {
          return void 0;
        }
        function create2() {
          var _types = [
            { name: "number", test: function(x) {
              return typeof x === "number";
            } },
            { name: "string", test: function(x) {
              return typeof x === "string";
            } },
            { name: "boolean", test: function(x) {
              return typeof x === "boolean";
            } },
            { name: "Function", test: function(x) {
              return typeof x === "function";
            } },
            { name: "Array", test: Array.isArray },
            { name: "Date", test: function(x) {
              return x instanceof Date;
            } },
            { name: "RegExp", test: function(x) {
              return x instanceof RegExp;
            } },
            { name: "Object", test: function(x) {
              return typeof x === "object" && x !== null && x.constructor === Object;
            } },
            { name: "null", test: function(x) {
              return x === null;
            } },
            { name: "undefined", test: function(x) {
              return x === void 0;
            } }
          ];
          var anyType = {
            name: "any",
            test: ok
          };
          var _ignore = [];
          var _conversions = [];
          var typed = {
            types: _types,
            conversions: _conversions,
            ignore: _ignore
          };
          function findTypeByName(typeName) {
            var entry = findInArray(typed.types, function(entry2) {
              return entry2.name === typeName;
            });
            if (entry) {
              return entry;
            }
            if (typeName === "any") {
              return anyType;
            }
            var hint = findInArray(typed.types, function(entry2) {
              return entry2.name.toLowerCase() === typeName.toLowerCase();
            });
            throw new TypeError('Unknown type "' + typeName + '"' + (hint ? '. Did you mean "' + hint.name + '"?' : ""));
          }
          function findTypeIndex(type) {
            if (type === anyType) {
              return 999;
            }
            return typed.types.indexOf(type);
          }
          function findTypeName(value) {
            var entry = findInArray(typed.types, function(entry2) {
              return entry2.test(value);
            });
            if (entry) {
              return entry.name;
            }
            throw new TypeError("Value has unknown type. Value: " + value);
          }
          function find(fn, signature) {
            if (!fn.signatures) {
              throw new TypeError("Function is no typed-function");
            }
            var arr;
            if (typeof signature === "string") {
              arr = signature.split(",");
              for (var i = 0; i < arr.length; i++) {
                arr[i] = arr[i].trim();
              }
            } else if (Array.isArray(signature)) {
              arr = signature;
            } else {
              throw new TypeError("String array or a comma separated string expected");
            }
            var str = arr.join(",");
            var match = fn.signatures[str];
            if (match) {
              return match;
            }
            throw new TypeError("Signature not found (signature: " + (fn.name || "unnamed") + "(" + arr.join(", ") + "))");
          }
          function convert(value, type) {
            var from = findTypeName(value);
            if (type === from) {
              return value;
            }
            for (var i = 0; i < typed.conversions.length; i++) {
              var conversion = typed.conversions[i];
              if (conversion.from === from && conversion.to === type) {
                return conversion.convert(value);
              }
            }
            throw new Error("Cannot convert from " + from + " to " + type);
          }
          function stringifyParams(params) {
            return params.map(function(param) {
              var typeNames = param.types.map(getTypeName);
              return (param.restParam ? "..." : "") + typeNames.join("|");
            }).join(",");
          }
          function parseParam(param, conversions) {
            var restParam = param.indexOf("...") === 0;
            var types = !restParam ? param : param.length > 3 ? param.slice(3) : "any";
            var typeNames = types.split("|").map(trim).filter(notEmpty).filter(notIgnore);
            var matchingConversions = filterConversions(conversions, typeNames);
            var exactTypes = typeNames.map(function(typeName) {
              var type = findTypeByName(typeName);
              return {
                name: typeName,
                typeIndex: findTypeIndex(type),
                test: type.test,
                conversion: null,
                conversionIndex: -1
              };
            });
            var convertibleTypes = matchingConversions.map(function(conversion) {
              var type = findTypeByName(conversion.from);
              return {
                name: conversion.from,
                typeIndex: findTypeIndex(type),
                test: type.test,
                conversion,
                conversionIndex: conversions.indexOf(conversion)
              };
            });
            return {
              types: exactTypes.concat(convertibleTypes),
              restParam
            };
          }
          function parseSignature(signature, fn, conversions) {
            var params = [];
            if (signature.trim() !== "") {
              params = signature.split(",").map(trim).map(function(param, index, array) {
                var parsedParam = parseParam(param, conversions);
                if (parsedParam.restParam && index !== array.length - 1) {
                  throw new SyntaxError('Unexpected rest parameter "' + param + '": only allowed for the last parameter');
                }
                return parsedParam;
              });
            }
            if (params.some(isInvalidParam)) {
              return null;
            }
            return {
              params,
              fn
            };
          }
          function hasRestParam(params) {
            var param = last(params);
            return param ? param.restParam : false;
          }
          function hasConversions(param) {
            return param.types.some(function(type) {
              return type.conversion != null;
            });
          }
          function compileTest(param) {
            if (!param || param.types.length === 0) {
              return ok;
            } else if (param.types.length === 1) {
              return findTypeByName(param.types[0].name).test;
            } else if (param.types.length === 2) {
              var test0 = findTypeByName(param.types[0].name).test;
              var test1 = findTypeByName(param.types[1].name).test;
              return function or(x) {
                return test0(x) || test1(x);
              };
            } else {
              var tests = param.types.map(function(type) {
                return findTypeByName(type.name).test;
              });
              return function or(x) {
                for (var i = 0; i < tests.length; i++) {
                  if (tests[i](x)) {
                    return true;
                  }
                }
                return false;
              };
            }
          }
          function compileTests(params) {
            var tests, test0, test1;
            if (hasRestParam(params)) {
              tests = initial(params).map(compileTest);
              var varIndex = tests.length;
              var lastTest = compileTest(last(params));
              var testRestParam = function(args) {
                for (var i = varIndex; i < args.length; i++) {
                  if (!lastTest(args[i])) {
                    return false;
                  }
                }
                return true;
              };
              return function testArgs(args) {
                for (var i = 0; i < tests.length; i++) {
                  if (!tests[i](args[i])) {
                    return false;
                  }
                }
                return testRestParam(args) && args.length >= varIndex + 1;
              };
            } else {
              if (params.length === 0) {
                return function testArgs(args) {
                  return args.length === 0;
                };
              } else if (params.length === 1) {
                test0 = compileTest(params[0]);
                return function testArgs(args) {
                  return test0(args[0]) && args.length === 1;
                };
              } else if (params.length === 2) {
                test0 = compileTest(params[0]);
                test1 = compileTest(params[1]);
                return function testArgs(args) {
                  return test0(args[0]) && test1(args[1]) && args.length === 2;
                };
              } else {
                tests = params.map(compileTest);
                return function testArgs(args) {
                  for (var i = 0; i < tests.length; i++) {
                    if (!tests[i](args[i])) {
                      return false;
                    }
                  }
                  return args.length === tests.length;
                };
              }
            }
          }
          function getParamAtIndex(signature, index) {
            return index < signature.params.length ? signature.params[index] : hasRestParam(signature.params) ? last(signature.params) : null;
          }
          function getExpectedTypeNames(signature, index, excludeConversions) {
            var param = getParamAtIndex(signature, index);
            var types = param ? excludeConversions ? param.types.filter(isExactType) : param.types : [];
            return types.map(getTypeName);
          }
          function getTypeName(type) {
            return type.name;
          }
          function isExactType(type) {
            return type.conversion === null || type.conversion === void 0;
          }
          function mergeExpectedParams(signatures, index) {
            var typeNames = uniq(flatMap(signatures, function(signature) {
              return getExpectedTypeNames(signature, index, false);
            }));
            return typeNames.indexOf("any") !== -1 ? ["any"] : typeNames;
          }
          function createError(name2, args, signatures) {
            var err, expected;
            var _name = name2 || "unnamed";
            var matchingSignatures = signatures;
            var index;
            for (index = 0; index < args.length; index++) {
              var nextMatchingDefs = matchingSignatures.filter(function(signature) {
                var test = compileTest(getParamAtIndex(signature, index));
                return (index < signature.params.length || hasRestParam(signature.params)) && test(args[index]);
              });
              if (nextMatchingDefs.length === 0) {
                expected = mergeExpectedParams(matchingSignatures, index);
                if (expected.length > 0) {
                  var actualType = findTypeName(args[index]);
                  err = new TypeError("Unexpected type of argument in function " + _name + " (expected: " + expected.join(" or ") + ", actual: " + actualType + ", index: " + index + ")");
                  err.data = {
                    category: "wrongType",
                    fn: _name,
                    index,
                    actual: actualType,
                    expected
                  };
                  return err;
                }
              } else {
                matchingSignatures = nextMatchingDefs;
              }
            }
            var lengths = matchingSignatures.map(function(signature) {
              return hasRestParam(signature.params) ? Infinity : signature.params.length;
            });
            if (args.length < Math.min.apply(null, lengths)) {
              expected = mergeExpectedParams(matchingSignatures, index);
              err = new TypeError("Too few arguments in function " + _name + " (expected: " + expected.join(" or ") + ", index: " + args.length + ")");
              err.data = {
                category: "tooFewArgs",
                fn: _name,
                index: args.length,
                expected
              };
              return err;
            }
            var maxLength = Math.max.apply(null, lengths);
            if (args.length > maxLength) {
              err = new TypeError("Too many arguments in function " + _name + " (expected: " + maxLength + ", actual: " + args.length + ")");
              err.data = {
                category: "tooManyArgs",
                fn: _name,
                index: args.length,
                expectedLength: maxLength
              };
              return err;
            }
            err = new TypeError('Arguments of type "' + args.join(", ") + '" do not match any of the defined signatures of function ' + _name + ".");
            err.data = {
              category: "mismatch",
              actual: args.map(findTypeName)
            };
            return err;
          }
          function getLowestTypeIndex(param) {
            var min = 999;
            for (var i = 0; i < param.types.length; i++) {
              if (isExactType(param.types[i])) {
                min = Math.min(min, param.types[i].typeIndex);
              }
            }
            return min;
          }
          function getLowestConversionIndex(param) {
            var min = 999;
            for (var i = 0; i < param.types.length; i++) {
              if (!isExactType(param.types[i])) {
                min = Math.min(min, param.types[i].conversionIndex);
              }
            }
            return min;
          }
          function compareParams(param1, param2) {
            var c;
            c = param1.restParam - param2.restParam;
            if (c !== 0) {
              return c;
            }
            c = hasConversions(param1) - hasConversions(param2);
            if (c !== 0) {
              return c;
            }
            c = getLowestTypeIndex(param1) - getLowestTypeIndex(param2);
            if (c !== 0) {
              return c;
            }
            return getLowestConversionIndex(param1) - getLowestConversionIndex(param2);
          }
          function compareSignatures(signature1, signature2) {
            var len = Math.min(signature1.params.length, signature2.params.length);
            var i;
            var c;
            c = signature1.params.some(hasConversions) - signature2.params.some(hasConversions);
            if (c !== 0) {
              return c;
            }
            for (i = 0; i < len; i++) {
              c = hasConversions(signature1.params[i]) - hasConversions(signature2.params[i]);
              if (c !== 0) {
                return c;
              }
            }
            for (i = 0; i < len; i++) {
              c = compareParams(signature1.params[i], signature2.params[i]);
              if (c !== 0) {
                return c;
              }
            }
            return signature1.params.length - signature2.params.length;
          }
          function filterConversions(conversions, typeNames) {
            var matches = {};
            conversions.forEach(function(conversion) {
              if (typeNames.indexOf(conversion.from) === -1 && typeNames.indexOf(conversion.to) !== -1 && !matches[conversion.from]) {
                matches[conversion.from] = conversion;
              }
            });
            return Object.keys(matches).map(function(from) {
              return matches[from];
            });
          }
          function compileArgsPreprocessing(params, fn) {
            var fnConvert = fn;
            if (params.some(hasConversions)) {
              var restParam = hasRestParam(params);
              var compiledConversions = params.map(compileArgConversion);
              fnConvert = function convertArgs() {
                var args = [];
                var last2 = restParam ? arguments.length - 1 : arguments.length;
                for (var i = 0; i < last2; i++) {
                  args[i] = compiledConversions[i](arguments[i]);
                }
                if (restParam) {
                  args[last2] = arguments[last2].map(compiledConversions[last2]);
                }
                return fn.apply(this, args);
              };
            }
            var fnPreprocess = fnConvert;
            if (hasRestParam(params)) {
              var offset = params.length - 1;
              fnPreprocess = function preprocessRestParams() {
                return fnConvert.apply(
                  this,
                  slice(arguments, 0, offset).concat([slice(arguments, offset)])
                );
              };
            }
            return fnPreprocess;
          }
          function compileArgConversion(param) {
            var test0, test1, conversion0, conversion1;
            var tests = [];
            var conversions = [];
            param.types.forEach(function(type) {
              if (type.conversion) {
                tests.push(findTypeByName(type.conversion.from).test);
                conversions.push(type.conversion.convert);
              }
            });
            switch (conversions.length) {
              case 0:
                return function convertArg(arg) {
                  return arg;
                };
              case 1:
                test0 = tests[0];
                conversion0 = conversions[0];
                return function convertArg(arg) {
                  if (test0(arg)) {
                    return conversion0(arg);
                  }
                  return arg;
                };
              case 2:
                test0 = tests[0];
                test1 = tests[1];
                conversion0 = conversions[0];
                conversion1 = conversions[1];
                return function convertArg(arg) {
                  if (test0(arg)) {
                    return conversion0(arg);
                  }
                  if (test1(arg)) {
                    return conversion1(arg);
                  }
                  return arg;
                };
              default:
                return function convertArg(arg) {
                  for (var i = 0; i < conversions.length; i++) {
                    if (tests[i](arg)) {
                      return conversions[i](arg);
                    }
                  }
                  return arg;
                };
            }
          }
          function createSignaturesMap(signatures) {
            var signaturesMap = {};
            signatures.forEach(function(signature) {
              if (!signature.params.some(hasConversions)) {
                splitParams(signature.params, true).forEach(function(params) {
                  signaturesMap[stringifyParams(params)] = signature.fn;
                });
              }
            });
            return signaturesMap;
          }
          function splitParams(params, ignoreConversionTypes) {
            function _splitParams(params2, index, types) {
              if (index < params2.length) {
                var param = params2[index];
                var filteredTypes = ignoreConversionTypes ? param.types.filter(isExactType) : param.types;
                var typeGroups;
                if (param.restParam) {
                  var exactTypes = filteredTypes.filter(isExactType);
                  typeGroups = exactTypes.length < filteredTypes.length ? [exactTypes, filteredTypes] : [filteredTypes];
                } else {
                  typeGroups = filteredTypes.map(function(type) {
                    return [type];
                  });
                }
                return flatMap(typeGroups, function(typeGroup) {
                  return _splitParams(params2, index + 1, types.concat([typeGroup]));
                });
              } else {
                var splittedParams = types.map(function(type, typeIndex) {
                  return {
                    types: type,
                    restParam: typeIndex === params2.length - 1 && hasRestParam(params2)
                  };
                });
                return [splittedParams];
              }
            }
            return _splitParams(params, 0, []);
          }
          function hasConflictingParams(signature1, signature2) {
            var ii = Math.max(signature1.params.length, signature2.params.length);
            for (var i = 0; i < ii; i++) {
              var typesNames1 = getExpectedTypeNames(signature1, i, true);
              var typesNames2 = getExpectedTypeNames(signature2, i, true);
              if (!hasOverlap(typesNames1, typesNames2)) {
                return false;
              }
            }
            var len1 = signature1.params.length;
            var len2 = signature2.params.length;
            var restParam1 = hasRestParam(signature1.params);
            var restParam2 = hasRestParam(signature2.params);
            return restParam1 ? restParam2 ? len1 === len2 : len2 >= len1 : restParam2 ? len1 >= len2 : len1 === len2;
          }
          function createTypedFunction(name2, signaturesMap) {
            if (Object.keys(signaturesMap).length === 0) {
              throw new SyntaxError("No signatures provided");
            }
            var parsedSignatures = [];
            Object.keys(signaturesMap).map(function(signature) {
              return parseSignature(signature, signaturesMap[signature], typed.conversions);
            }).filter(notNull).forEach(function(parsedSignature) {
              var conflictingSignature = findInArray(parsedSignatures, function(s) {
                return hasConflictingParams(s, parsedSignature);
              });
              if (conflictingSignature) {
                throw new TypeError('Conflicting signatures "' + stringifyParams(conflictingSignature.params) + '" and "' + stringifyParams(parsedSignature.params) + '".');
              }
              parsedSignatures.push(parsedSignature);
            });
            var signatures = flatMap(parsedSignatures, function(parsedSignature) {
              var params = parsedSignature ? splitParams(parsedSignature.params, false) : [];
              return params.map(function(params2) {
                return {
                  params: params2,
                  fn: parsedSignature.fn
                };
              });
            }).filter(notNull);
            signatures.sort(compareSignatures);
            var ok0 = signatures[0] && signatures[0].params.length <= 2 && !hasRestParam(signatures[0].params);
            var ok1 = signatures[1] && signatures[1].params.length <= 2 && !hasRestParam(signatures[1].params);
            var ok2 = signatures[2] && signatures[2].params.length <= 2 && !hasRestParam(signatures[2].params);
            var ok3 = signatures[3] && signatures[3].params.length <= 2 && !hasRestParam(signatures[3].params);
            var ok4 = signatures[4] && signatures[4].params.length <= 2 && !hasRestParam(signatures[4].params);
            var ok5 = signatures[5] && signatures[5].params.length <= 2 && !hasRestParam(signatures[5].params);
            var allOk = ok0 && ok1 && ok2 && ok3 && ok4 && ok5;
            var tests = signatures.map(function(signature) {
              return compileTests(signature.params);
            });
            var test00 = ok0 ? compileTest(signatures[0].params[0]) : notOk;
            var test10 = ok1 ? compileTest(signatures[1].params[0]) : notOk;
            var test20 = ok2 ? compileTest(signatures[2].params[0]) : notOk;
            var test30 = ok3 ? compileTest(signatures[3].params[0]) : notOk;
            var test40 = ok4 ? compileTest(signatures[4].params[0]) : notOk;
            var test50 = ok5 ? compileTest(signatures[5].params[0]) : notOk;
            var test01 = ok0 ? compileTest(signatures[0].params[1]) : notOk;
            var test11 = ok1 ? compileTest(signatures[1].params[1]) : notOk;
            var test21 = ok2 ? compileTest(signatures[2].params[1]) : notOk;
            var test31 = ok3 ? compileTest(signatures[3].params[1]) : notOk;
            var test41 = ok4 ? compileTest(signatures[4].params[1]) : notOk;
            var test51 = ok5 ? compileTest(signatures[5].params[1]) : notOk;
            var fns = signatures.map(function(signature) {
              return compileArgsPreprocessing(signature.params, signature.fn);
            });
            var fn0 = ok0 ? fns[0] : undef;
            var fn1 = ok1 ? fns[1] : undef;
            var fn2 = ok2 ? fns[2] : undef;
            var fn3 = ok3 ? fns[3] : undef;
            var fn4 = ok4 ? fns[4] : undef;
            var fn5 = ok5 ? fns[5] : undef;
            var len0 = ok0 ? signatures[0].params.length : -1;
            var len1 = ok1 ? signatures[1].params.length : -1;
            var len2 = ok2 ? signatures[2].params.length : -1;
            var len3 = ok3 ? signatures[3].params.length : -1;
            var len4 = ok4 ? signatures[4].params.length : -1;
            var len5 = ok5 ? signatures[5].params.length : -1;
            var iStart = allOk ? 6 : 0;
            var iEnd = signatures.length;
            var generic = function generic2() {
              for (var i = iStart; i < iEnd; i++) {
                if (tests[i](arguments)) {
                  return fns[i].apply(this, arguments);
                }
              }
              return typed.onMismatch(name2, arguments, signatures);
            };
            var fn = function fn6(arg0, arg1) {
              if (arguments.length === len0 && test00(arg0) && test01(arg1)) {
                return fn0.apply(fn6, arguments);
              }
              if (arguments.length === len1 && test10(arg0) && test11(arg1)) {
                return fn1.apply(fn6, arguments);
              }
              if (arguments.length === len2 && test20(arg0) && test21(arg1)) {
                return fn2.apply(fn6, arguments);
              }
              if (arguments.length === len3 && test30(arg0) && test31(arg1)) {
                return fn3.apply(fn6, arguments);
              }
              if (arguments.length === len4 && test40(arg0) && test41(arg1)) {
                return fn4.apply(fn6, arguments);
              }
              if (arguments.length === len5 && test50(arg0) && test51(arg1)) {
                return fn5.apply(fn6, arguments);
              }
              return generic.apply(fn6, arguments);
            };
            try {
              Object.defineProperty(fn, "name", { value: name2 });
            } catch (err) {
            }
            fn.signatures = createSignaturesMap(signatures);
            return fn;
          }
          function _onMismatch(name2, args, signatures) {
            throw createError(name2, args, signatures);
          }
          function notIgnore(typeName) {
            return typed.ignore.indexOf(typeName) === -1;
          }
          function trim(str) {
            return str.trim();
          }
          function notEmpty(str) {
            return !!str;
          }
          function notNull(value) {
            return value !== null;
          }
          function isInvalidParam(param) {
            return param.types.length === 0;
          }
          function initial(arr) {
            return arr.slice(0, arr.length - 1);
          }
          function last(arr) {
            return arr[arr.length - 1];
          }
          function slice(arr, start, end) {
            return Array.prototype.slice.call(arr, start, end);
          }
          function contains2(array, item) {
            return array.indexOf(item) !== -1;
          }
          function hasOverlap(array1, array2) {
            for (var i = 0; i < array1.length; i++) {
              if (contains2(array2, array1[i])) {
                return true;
              }
            }
            return false;
          }
          function findInArray(arr, test) {
            for (var i = 0; i < arr.length; i++) {
              if (test(arr[i])) {
                return arr[i];
              }
            }
            return void 0;
          }
          function uniq(arr) {
            var entries = {};
            for (var i = 0; i < arr.length; i++) {
              entries[arr[i]] = true;
            }
            return Object.keys(entries);
          }
          function flatMap(arr, callback) {
            return Array.prototype.concat.apply([], arr.map(callback));
          }
          function getName(fns) {
            var name2 = "";
            for (var i = 0; i < fns.length; i++) {
              var fn = fns[i];
              if ((typeof fn.signatures === "object" || typeof fn.signature === "string") && fn.name !== "") {
                if (name2 === "") {
                  name2 = fn.name;
                } else if (name2 !== fn.name) {
                  var err = new Error("Function names do not match (expected: " + name2 + ", actual: " + fn.name + ")");
                  err.data = {
                    actual: fn.name,
                    expected: name2
                  };
                  throw err;
                }
              }
            }
            return name2;
          }
          function extractSignatures(fns) {
            var err;
            var signaturesMap = {};
            function validateUnique(_signature, _fn) {
              if (signaturesMap.hasOwnProperty(_signature) && _fn !== signaturesMap[_signature]) {
                err = new Error('Signature "' + _signature + '" is defined twice');
                err.data = { signature: _signature };
                throw err;
              }
            }
            for (var i = 0; i < fns.length; i++) {
              var fn = fns[i];
              if (typeof fn.signatures === "object") {
                for (var signature in fn.signatures) {
                  if (fn.signatures.hasOwnProperty(signature)) {
                    validateUnique(signature, fn.signatures[signature]);
                    signaturesMap[signature] = fn.signatures[signature];
                  }
                }
              } else if (typeof fn.signature === "string") {
                validateUnique(fn.signature, fn);
                signaturesMap[fn.signature] = fn;
              } else {
                err = new TypeError("Function is no typed-function (index: " + i + ")");
                err.data = { index: i };
                throw err;
              }
            }
            return signaturesMap;
          }
          typed = createTypedFunction("typed", {
            "string, Object": createTypedFunction,
            "Object": function(signaturesMap) {
              var fns = [];
              for (var signature in signaturesMap) {
                if (signaturesMap.hasOwnProperty(signature)) {
                  fns.push(signaturesMap[signature]);
                }
              }
              var name2 = getName(fns);
              return createTypedFunction(name2, signaturesMap);
            },
            "...Function": function(fns) {
              return createTypedFunction(getName(fns), extractSignatures(fns));
            },
            "string, ...Function": function(name2, fns) {
              return createTypedFunction(name2, extractSignatures(fns));
            }
          });
          typed.create = create2;
          typed.types = _types;
          typed.conversions = _conversions;
          typed.ignore = _ignore;
          typed.onMismatch = _onMismatch;
          typed.throwMismatchError = _onMismatch;
          typed.createError = createError;
          typed.convert = convert;
          typed.find = find;
          typed.addType = function(type, beforeObjectTest) {
            if (!type || typeof type.name !== "string" || typeof type.test !== "function") {
              throw new TypeError("Object with properties {name: string, test: function} expected");
            }
            if (beforeObjectTest !== false) {
              for (var i = 0; i < typed.types.length; i++) {
                if (typed.types[i].name === "Object") {
                  typed.types.splice(i, 0, type);
                  return;
                }
              }
            }
            typed.types.push(type);
          };
          typed.addConversion = function(conversion) {
            if (!conversion || typeof conversion.from !== "string" || typeof conversion.to !== "string" || typeof conversion.convert !== "function") {
              throw new TypeError("Object with properties {from: string, to: string, convert: function} expected");
            }
            typed.conversions.push(conversion);
          };
          return typed;
        }
        return create2();
      });
    })(typedFunction$2);
    return typedFunction$2.exports;
  }
  var typedFunctionExports = requireTypedFunction();
  const typedFunction = /* @__PURE__ */ getDefaultExportFromCjs(typedFunctionExports);
  function getSafeProperty(object, prop) {
    if (isPlainObject(object) && isSafeProperty(object, prop)) {
      return object[prop];
    }
    if (typeof object[prop] === "function" && isSafeMethod(object, prop)) {
      throw new Error('Cannot access method "' + prop + '" as a property');
    }
    throw new Error('No access to property "' + prop + '"');
  }
  function setSafeProperty(object, prop, value) {
    if (isPlainObject(object) && isSafeProperty(object, prop)) {
      object[prop] = value;
      return value;
    }
    throw new Error('No access to property "' + prop + '"');
  }
  function hasSafeProperty(object, prop) {
    return prop in object;
  }
  function isSafeProperty(object, prop) {
    if (!object || typeof object !== "object") {
      return false;
    }
    if (hasOwnProperty(safeNativeProperties, prop)) {
      return true;
    }
    if (prop in Object.prototype) {
      return false;
    }
    if (prop in Function.prototype) {
      return false;
    }
    return true;
  }
  function validateSafeMethod(object, method) {
    if (!isSafeMethod(object, method)) {
      throw new Error('No access to method "' + method + '"');
    }
  }
  function isSafeMethod(object, method) {
    if (object === null || object === void 0 || typeof object[method] !== "function") {
      return false;
    }
    if (hasOwnProperty(object, method) && Object.getPrototypeOf && method in Object.getPrototypeOf(object)) {
      return false;
    }
    if (hasOwnProperty(safeNativeMethods, method)) {
      return true;
    }
    if (method in Object.prototype) {
      return false;
    }
    if (method in Function.prototype) {
      return false;
    }
    return true;
  }
  function isPlainObject(object) {
    return typeof object === "object" && object && object.constructor === Object;
  }
  var safeNativeProperties = {
    length: true,
    name: true
  };
  var safeNativeMethods = {
    toString: true,
    valueOf: true,
    toLocaleString: true
  };
  class ObjectWrappingMap {
    constructor(object) {
      this.wrappedObject = object;
    }
    keys() {
      return Object.keys(this.wrappedObject);
    }
    get(key) {
      return getSafeProperty(this.wrappedObject, key);
    }
    set(key, value) {
      setSafeProperty(this.wrappedObject, key, value);
      return this;
    }
    has(key) {
      return hasSafeProperty(this.wrappedObject, key);
    }
  }
  function createEmptyMap() {
    return /* @__PURE__ */ new Map();
  }
  function createMap(mapOrObject) {
    if (!mapOrObject) {
      return createEmptyMap();
    }
    if (isMap(mapOrObject)) {
      return mapOrObject;
    }
    if (isObject(mapOrObject)) {
      return new ObjectWrappingMap(mapOrObject);
    }
    throw new Error("createMap can create maps from objects or Maps");
  }
  function isMap(object) {
    if (!object) {
      return false;
    }
    return object instanceof Map || object instanceof ObjectWrappingMap || typeof object.set === "function" && typeof object.get === "function" && typeof object.keys === "function" && typeof object.has === "function";
  }
  function assign(map2) {
    for (var _len = arguments.length, objects = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      objects[_key - 1] = arguments[_key];
    }
    for (var args of objects) {
      if (!args) {
        continue;
      }
      if (isMap(args)) {
        for (var key of args.keys()) {
          map2.set(key, args.get(key));
        }
      } else if (isObject(args)) {
        for (var _key2 of Object.keys(args)) {
          map2.set(_key2, args[_key2]);
        }
      }
    }
    return map2;
  }
  var _createTyped2 = function _createTyped() {
    _createTyped2 = typedFunction.create;
    return typedFunction;
  };
  var dependencies$m = ["?BigNumber", "?Complex", "?DenseMatrix", "?Fraction"];
  var createTyped = /* @__PURE__ */ factory("typed", dependencies$m, function createTyped2(_ref) {
    var {
      BigNumber,
      Complex,
      DenseMatrix,
      Fraction
    } = _ref;
    var typed = _createTyped2();
    typed.types = [
      {
        name: "number",
        test: isNumber
      },
      {
        name: "Complex",
        test: isComplex
      },
      {
        name: "BigNumber",
        test: isBigNumber
      },
      {
        name: "Fraction",
        test: isFraction
      },
      {
        name: "Unit",
        test: isUnit
      },
      {
        name: "string",
        test: isString
      },
      {
        name: "Chain",
        test: isChain
      },
      {
        name: "Array",
        test: isArray
      },
      {
        name: "Matrix",
        test: isMatrix
      },
      {
        name: "DenseMatrix",
        test: isDenseMatrix
      },
      {
        name: "SparseMatrix",
        test: isSparseMatrix
      },
      {
        name: "Range",
        test: isRange
      },
      {
        name: "Index",
        test: isIndex
      },
      {
        name: "boolean",
        test: isBoolean
      },
      {
        name: "ResultSet",
        test: isResultSet
      },
      {
        name: "Help",
        test: isHelp
      },
      {
        name: "function",
        test: isFunction
      },
      {
        name: "Date",
        test: isDate
      },
      {
        name: "RegExp",
        test: isRegExp
      },
      {
        name: "null",
        test: isNull
      },
      {
        name: "undefined",
        test: isUndefined
      },
      {
        name: "AccessorNode",
        test: isAccessorNode
      },
      {
        name: "ArrayNode",
        test: isArrayNode
      },
      {
        name: "AssignmentNode",
        test: isAssignmentNode
      },
      {
        name: "BlockNode",
        test: isBlockNode
      },
      {
        name: "ConditionalNode",
        test: isConditionalNode
      },
      {
        name: "ConstantNode",
        test: isConstantNode
      },
      {
        name: "FunctionNode",
        test: isFunctionNode
      },
      {
        name: "FunctionAssignmentNode",
        test: isFunctionAssignmentNode
      },
      {
        name: "IndexNode",
        test: isIndexNode
      },
      {
        name: "Node",
        test: isNode
      },
      {
        name: "ObjectNode",
        test: isObjectNode
      },
      {
        name: "OperatorNode",
        test: isOperatorNode
      },
      {
        name: "ParenthesisNode",
        test: isParenthesisNode
      },
      {
        name: "RangeNode",
        test: isRangeNode
      },
      {
        name: "SymbolNode",
        test: isSymbolNode
      },
      {
        name: "Map",
        test: isMap
      },
      {
        name: "Object",
        test: isObject
      }
      // order 'Object' last, it matches on other classes too
    ];
    typed.conversions = [{
      from: "number",
      to: "BigNumber",
      convert: function convert(x) {
        if (!BigNumber) {
          throwNoBignumber(x);
        }
        if (digits(x) > 15) {
          throw new TypeError("Cannot implicitly convert a number with >15 significant digits to BigNumber (value: " + x + "). Use function bignumber(x) to convert to BigNumber.");
        }
        return new BigNumber(x);
      }
    }, {
      from: "number",
      to: "Complex",
      convert: function convert(x) {
        if (!Complex) {
          throwNoComplex(x);
        }
        return new Complex(x, 0);
      }
    }, {
      from: "number",
      to: "string",
      convert: function convert(x) {
        return x + "";
      }
    }, {
      from: "BigNumber",
      to: "Complex",
      convert: function convert(x) {
        if (!Complex) {
          throwNoComplex(x);
        }
        return new Complex(x.toNumber(), 0);
      }
    }, {
      from: "Fraction",
      to: "BigNumber",
      convert: function convert(x) {
        throw new TypeError("Cannot implicitly convert a Fraction to BigNumber or vice versa. Use function bignumber(x) to convert to BigNumber or fraction(x) to convert to Fraction.");
      }
    }, {
      from: "Fraction",
      to: "Complex",
      convert: function convert(x) {
        if (!Complex) {
          throwNoComplex(x);
        }
        return new Complex(x.valueOf(), 0);
      }
    }, {
      from: "number",
      to: "Fraction",
      convert: function convert(x) {
        if (!Fraction) {
          throwNoFraction(x);
        }
        var f = new Fraction(x);
        if (f.valueOf() !== x) {
          throw new TypeError("Cannot implicitly convert a number to a Fraction when there will be a loss of precision (value: " + x + "). Use function fraction(x) to convert to Fraction.");
        }
        return f;
      }
    }, {
      // FIXME: add conversion from Fraction to number, for example for `sqrt(fraction(1,3))`
      //  from: 'Fraction',
      //  to: 'number',
      //  convert: function (x) {
      //    return x.valueOf()
      //  }
      // }, {
      from: "string",
      to: "number",
      convert: function convert(x) {
        var n = Number(x);
        if (isNaN(n)) {
          throw new Error('Cannot convert "' + x + '" to a number');
        }
        return n;
      }
    }, {
      from: "string",
      to: "BigNumber",
      convert: function convert(x) {
        if (!BigNumber) {
          throwNoBignumber(x);
        }
        try {
          return new BigNumber(x);
        } catch (err) {
          throw new Error('Cannot convert "' + x + '" to BigNumber');
        }
      }
    }, {
      from: "string",
      to: "Fraction",
      convert: function convert(x) {
        if (!Fraction) {
          throwNoFraction(x);
        }
        try {
          return new Fraction(x);
        } catch (err) {
          throw new Error('Cannot convert "' + x + '" to Fraction');
        }
      }
    }, {
      from: "string",
      to: "Complex",
      convert: function convert(x) {
        if (!Complex) {
          throwNoComplex(x);
        }
        try {
          return new Complex(x);
        } catch (err) {
          throw new Error('Cannot convert "' + x + '" to Complex');
        }
      }
    }, {
      from: "boolean",
      to: "number",
      convert: function convert(x) {
        return +x;
      }
    }, {
      from: "boolean",
      to: "BigNumber",
      convert: function convert(x) {
        if (!BigNumber) {
          throwNoBignumber(x);
        }
        return new BigNumber(+x);
      }
    }, {
      from: "boolean",
      to: "Fraction",
      convert: function convert(x) {
        if (!Fraction) {
          throwNoFraction(x);
        }
        return new Fraction(+x);
      }
    }, {
      from: "boolean",
      to: "string",
      convert: function convert(x) {
        return String(x);
      }
    }, {
      from: "Array",
      to: "Matrix",
      convert: function convert(array) {
        if (!DenseMatrix) {
          throwNoMatrix();
        }
        return new DenseMatrix(array);
      }
    }, {
      from: "Matrix",
      to: "Array",
      convert: function convert(matrix) {
        return matrix.valueOf();
      }
    }];
    return typed;
  });
  function throwNoBignumber(x) {
    throw new Error("Cannot convert value ".concat(x, " into a BigNumber: no class 'BigNumber' provided"));
  }
  function throwNoComplex(x) {
    throw new Error("Cannot convert value ".concat(x, " into a Complex number: no class 'Complex' provided"));
  }
  function throwNoMatrix() {
    throw new Error("Cannot convert array into a Matrix: no class 'DenseMatrix' provided");
  }
  function throwNoFraction(x) {
    throw new Error("Cannot convert value ".concat(x, " into a Fraction, no class 'Fraction' provided."));
  }
  var name$l = "ResultSet";
  var dependencies$l = [];
  var createResultSet = /* @__PURE__ */ factory(name$l, dependencies$l, () => {
    function ResultSet(entries) {
      if (!(this instanceof ResultSet)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.entries = entries || [];
    }
    ResultSet.prototype.type = "ResultSet";
    ResultSet.prototype.isResultSet = true;
    ResultSet.prototype.valueOf = function() {
      return this.entries;
    };
    ResultSet.prototype.toString = function() {
      return "[" + this.entries.join(", ") + "]";
    };
    ResultSet.prototype.toJSON = function() {
      return {
        mathjs: "ResultSet",
        entries: this.entries
      };
    };
    ResultSet.fromJSON = function(json) {
      return new ResultSet(json.entries);
    };
    return ResultSet;
  }, {
    isClass: true
  });
  function deepMap(array, callback, skipZeros) {
    if (array && typeof array.map === "function") {
      return array.map(function(x) {
        return deepMap(x, callback);
      });
    } else {
      return callback(array);
    }
  }
  var name$k = "number";
  var dependencies$k = ["typed"];
  function getNonDecimalNumberParts(input) {
    var nonDecimalWithRadixMatch = input.match(/(0[box])([0-9a-fA-F]*)\.([0-9a-fA-F]*)/);
    if (nonDecimalWithRadixMatch) {
      var radix = {
        "0b": 2,
        "0o": 8,
        "0x": 16
      }[nonDecimalWithRadixMatch[1]];
      var integerPart = nonDecimalWithRadixMatch[2];
      var fractionalPart = nonDecimalWithRadixMatch[3];
      return {
        input,
        radix,
        integerPart,
        fractionalPart
      };
    } else {
      return null;
    }
  }
  function makeNumberFromNonDecimalParts(parts) {
    var n = parseInt(parts.integerPart, parts.radix);
    var f = 0;
    for (var i = 0; i < parts.fractionalPart.length; i++) {
      var digitValue = parseInt(parts.fractionalPart[i], parts.radix);
      f += digitValue / Math.pow(parts.radix, i + 1);
    }
    var result = n + f;
    if (isNaN(result)) {
      throw new SyntaxError('String "' + parts.input + '" is no valid number');
    }
    return result;
  }
  var createNumber = /* @__PURE__ */ factory(name$k, dependencies$k, (_ref) => {
    var {
      typed
    } = _ref;
    var number = typed("number", {
      "": function _() {
        return 0;
      },
      number: function number2(x) {
        return x;
      },
      string: function string(x) {
        if (x === "NaN") return NaN;
        var nonDecimalNumberParts = getNonDecimalNumberParts(x);
        if (nonDecimalNumberParts) {
          return makeNumberFromNonDecimalParts(nonDecimalNumberParts);
        }
        var size = 0;
        var wordSizeSuffixMatch = x.match(/(0[box][0-9a-fA-F]*)i([0-9]*)/);
        if (wordSizeSuffixMatch) {
          size = Number(wordSizeSuffixMatch[2]);
          x = wordSizeSuffixMatch[1];
        }
        var num = Number(x);
        if (isNaN(num)) {
          throw new SyntaxError('String "' + x + '" is no valid number');
        }
        if (wordSizeSuffixMatch) {
          if (num > __pow(2, size) - 1) {
            throw new SyntaxError('String "'.concat(x, '" is out of range'));
          }
          if (num >= __pow(2, size - 1)) {
            num = num - __pow(2, size);
          }
        }
        return num;
      },
      BigNumber: function BigNumber(x) {
        return x.toNumber();
      },
      Fraction: function Fraction(x) {
        return x.valueOf();
      },
      Unit: function Unit(x) {
        throw new Error("Second argument with valueless unit expected");
      },
      null: function _null(x) {
        return 0;
      },
      "Unit, string | Unit": function UnitStringUnit(unit, valuelessUnit) {
        return unit.toNumber(valuelessUnit);
      },
      "Array | Matrix": function ArrayMatrix(x) {
        return deepMap(x, this);
      }
    });
    number.fromJSON = function(json) {
      return parseFloat(json.value);
    };
    return number;
  });
  var keywords = /* @__PURE__ */ new Set(["end"]);
  var name$j = "Node";
  var dependencies$j = ["mathWithTransform"];
  var createNode = /* @__PURE__ */ factory(name$j, dependencies$j, (_ref) => {
    var {
      mathWithTransform
    } = _ref;
    function Node() {
      if (!(this instanceof Node)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
    }
    Node.prototype.evaluate = function(scope) {
      return this.compile().evaluate(scope);
    };
    Node.prototype.type = "Node";
    Node.prototype.isNode = true;
    Node.prototype.comment = "";
    Node.prototype.compile = function() {
      var expr = this._compile(mathWithTransform, {});
      var args = {};
      var context = null;
      function evaluate(scope) {
        var s = createMap(scope);
        _validateScope(s);
        return expr(s, args, context);
      }
      return {
        evaluate
      };
    };
    Node.prototype._compile = function(math2, argNames) {
      throw new Error("Method _compile should be implemented by type " + this.type);
    };
    Node.prototype.forEach = function(callback) {
      throw new Error("Cannot run forEach on a Node interface");
    };
    Node.prototype.map = function(callback) {
      throw new Error("Cannot run map on a Node interface");
    };
    Node.prototype._ifNode = function(node) {
      if (!isNode(node)) {
        throw new TypeError("Callback function must return a Node");
      }
      return node;
    };
    Node.prototype.traverse = function(callback) {
      callback(this, null, null);
      function _traverse(node, callback2) {
        node.forEach(function(child, path, parent) {
          callback2(child, path, parent);
          _traverse(child, callback2);
        });
      }
      _traverse(this, callback);
    };
    Node.prototype.transform = function(callback) {
      function _transform(child, path, parent) {
        var replacement = callback(child, path, parent);
        if (replacement !== child) {
          return replacement;
        }
        return child.map(_transform);
      }
      return _transform(this, null, null);
    };
    Node.prototype.filter = function(callback) {
      var nodes = [];
      this.traverse(function(node, path, parent) {
        if (callback(node, path, parent)) {
          nodes.push(node);
        }
      });
      return nodes;
    };
    Node.prototype.clone = function() {
      throw new Error("Cannot clone a Node interface");
    };
    Node.prototype.cloneDeep = function() {
      return this.map(function(node) {
        return node.cloneDeep();
      });
    };
    Node.prototype.equals = function(other) {
      return other ? deepStrictEqual(this, other) : false;
    };
    Node.prototype.toString = function(options) {
      var customString = this._getCustomString(options);
      if (typeof customString !== "undefined") {
        return customString;
      }
      return this._toString(options);
    };
    Node.prototype.toJSON = function() {
      throw new Error("Cannot serialize object: toJSON not implemented by " + this.type);
    };
    Node.prototype.toHTML = function(options) {
      var customString = this._getCustomString(options);
      if (typeof customString !== "undefined") {
        return customString;
      }
      return this.toHTML(options);
    };
    Node.prototype._toString = function() {
      throw new Error("_toString not implemented for " + this.type);
    };
    Node.prototype.toTex = function(options) {
      var customString = this._getCustomString(options);
      if (typeof customString !== "undefined") {
        return customString;
      }
      return this._toTex(options);
    };
    Node.prototype._toTex = function(options) {
      throw new Error("_toTex not implemented for " + this.type);
    };
    Node.prototype._getCustomString = function(options) {
      if (options && typeof options === "object") {
        switch (typeof options.handler) {
          case "object":
          case "undefined":
            return;
          case "function":
            return options.handler(this, options);
          default:
            throw new TypeError("Object or function expected as callback");
        }
      }
    };
    Node.prototype.getIdentifier = function() {
      return this.type;
    };
    Node.prototype.getContent = function() {
      return this;
    };
    function _validateScope(scope) {
      for (var symbol of [...keywords]) {
        if (scope.has(symbol)) {
          throw new Error('Scope contains an illegal symbol, "' + symbol + '" is a reserved keyword');
        }
      }
    }
    return Node;
  }, {
    isClass: true,
    isNode: true
  });
  function errorTransform(err) {
    if (err && err.isIndexError) {
      return new IndexError(err.index + 1, err.min + 1, err.max !== void 0 ? err.max + 1 : void 0);
    }
    return err;
  }
  function accessFactory(_ref) {
    var {
      subset
    } = _ref;
    return function access(object, index) {
      try {
        if (Array.isArray(object)) {
          return subset(object, index);
        } else if (object && typeof object.subset === "function") {
          return object.subset(index);
        } else if (typeof object === "string") {
          return subset(object, index);
        } else if (typeof object === "object") {
          if (!index.isObjectProperty()) {
            throw new TypeError("Cannot apply a numeric index as object property");
          }
          return getSafeProperty(object, index.getObjectProperty());
        } else {
          throw new TypeError("Cannot apply index: unsupported type of object");
        }
      } catch (err) {
        throw errorTransform(err);
      }
    };
  }
  var name$i = "AccessorNode";
  var dependencies$i = ["subset", "Node"];
  var createAccessorNode = /* @__PURE__ */ factory(name$i, dependencies$i, (_ref) => {
    var {
      subset,
      Node
    } = _ref;
    var access = accessFactory({
      subset
    });
    function AccessorNode(object, index) {
      if (!(this instanceof AccessorNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!isNode(object)) {
        throw new TypeError('Node expected for parameter "object"');
      }
      if (!isIndexNode(index)) {
        throw new TypeError('IndexNode expected for parameter "index"');
      }
      this.object = object || null;
      this.index = index;
      Object.defineProperty(this, "name", {
        get: function() {
          if (this.index) {
            return this.index.isObjectProperty() ? this.index.getObjectProperty() : "";
          } else {
            return this.object.name || "";
          }
        }.bind(this),
        set: function set() {
          throw new Error("Cannot assign a new name, name is read-only");
        }
      });
    }
    AccessorNode.prototype = new Node();
    AccessorNode.prototype.type = "AccessorNode";
    AccessorNode.prototype.isAccessorNode = true;
    AccessorNode.prototype._compile = function(math2, argNames) {
      var evalObject = this.object._compile(math2, argNames);
      var evalIndex = this.index._compile(math2, argNames);
      if (this.index.isObjectProperty()) {
        var prop = this.index.getObjectProperty();
        return function evalAccessorNode(scope, args, context) {
          return getSafeProperty(evalObject(scope, args, context), prop);
        };
      } else {
        return function evalAccessorNode(scope, args, context) {
          var object = evalObject(scope, args, context);
          var index = evalIndex(scope, args, object);
          return access(object, index);
        };
      }
    };
    AccessorNode.prototype.forEach = function(callback) {
      callback(this.object, "object", this);
      callback(this.index, "index", this);
    };
    AccessorNode.prototype.map = function(callback) {
      return new AccessorNode(this._ifNode(callback(this.object, "object", this)), this._ifNode(callback(this.index, "index", this)));
    };
    AccessorNode.prototype.clone = function() {
      return new AccessorNode(this.object, this.index);
    };
    AccessorNode.prototype._toString = function(options) {
      var object = this.object.toString(options);
      if (needParenthesis(this.object)) {
        object = "(" + object + ")";
      }
      return object + this.index.toString(options);
    };
    AccessorNode.prototype.toHTML = function(options) {
      var object = this.object.toHTML(options);
      if (needParenthesis(this.object)) {
        object = '<span class="math-parenthesis math-round-parenthesis">(</span>' + object + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      return object + this.index.toHTML(options);
    };
    AccessorNode.prototype._toTex = function(options) {
      var object = this.object.toTex(options);
      if (needParenthesis(this.object)) {
        object = "\\left(' + object + '\\right)";
      }
      return object + this.index.toTex(options);
    };
    AccessorNode.prototype.toJSON = function() {
      return {
        mathjs: "AccessorNode",
        object: this.object,
        index: this.index
      };
    };
    AccessorNode.fromJSON = function(json) {
      return new AccessorNode(json.object, json.index);
    };
    function needParenthesis(node) {
      return !(isAccessorNode(node) || isArrayNode(node) || isConstantNode(node) || isFunctionNode(node) || isObjectNode(node) || isParenthesisNode(node) || isSymbolNode(node));
    }
    return AccessorNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$h = "ArrayNode";
  var dependencies$h = ["Node"];
  var createArrayNode = /* @__PURE__ */ factory(name$h, dependencies$h, (_ref) => {
    var {
      Node
    } = _ref;
    function ArrayNode(items) {
      if (!(this instanceof ArrayNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.items = items || [];
      if (!Array.isArray(this.items) || !this.items.every(isNode)) {
        throw new TypeError("Array containing Nodes expected");
      }
    }
    ArrayNode.prototype = new Node();
    ArrayNode.prototype.type = "ArrayNode";
    ArrayNode.prototype.isArrayNode = true;
    ArrayNode.prototype._compile = function(math2, argNames) {
      var evalItems = map(this.items, function(item) {
        return item._compile(math2, argNames);
      });
      var asMatrix = math2.config.matrix !== "Array";
      if (asMatrix) {
        var matrix = math2.matrix;
        return function evalArrayNode(scope, args, context) {
          return matrix(map(evalItems, function(evalItem) {
            return evalItem(scope, args, context);
          }));
        };
      } else {
        return function evalArrayNode(scope, args, context) {
          return map(evalItems, function(evalItem) {
            return evalItem(scope, args, context);
          });
        };
      }
    };
    ArrayNode.prototype.forEach = function(callback) {
      for (var i = 0; i < this.items.length; i++) {
        var node = this.items[i];
        callback(node, "items[" + i + "]", this);
      }
    };
    ArrayNode.prototype.map = function(callback) {
      var items = [];
      for (var i = 0; i < this.items.length; i++) {
        items[i] = this._ifNode(callback(this.items[i], "items[" + i + "]", this));
      }
      return new ArrayNode(items);
    };
    ArrayNode.prototype.clone = function() {
      return new ArrayNode(this.items.slice(0));
    };
    ArrayNode.prototype._toString = function(options) {
      var items = this.items.map(function(node) {
        return node.toString(options);
      });
      return "[" + items.join(", ") + "]";
    };
    ArrayNode.prototype.toJSON = function() {
      return {
        mathjs: "ArrayNode",
        items: this.items
      };
    };
    ArrayNode.fromJSON = function(json) {
      return new ArrayNode(json.items);
    };
    ArrayNode.prototype.toHTML = function(options) {
      var items = this.items.map(function(node) {
        return node.toHTML(options);
      });
      return '<span class="math-parenthesis math-square-parenthesis">[</span>' + items.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-square-parenthesis">]</span>';
    };
    ArrayNode.prototype._toTex = function(options) {
      function itemsToTex(items, nested) {
        var mixedItems = items.some(isArrayNode) && !items.every(isArrayNode);
        var itemsFormRow = nested || mixedItems;
        var itemSep = itemsFormRow ? "&" : "\\\\";
        var itemsTex = items.map(function(node) {
          if (node.items) {
            return itemsToTex(node.items, !nested);
          } else {
            return node.toTex(options);
          }
        }).join(itemSep);
        return mixedItems || !itemsFormRow || itemsFormRow && !nested ? "\\begin{bmatrix}" + itemsTex + "\\end{bmatrix}" : itemsTex;
      }
      return itemsToTex(this.items, false);
    };
    return ArrayNode;
  }, {
    isClass: true,
    isNode: true
  });
  function assignFactory(_ref) {
    var {
      subset,
      matrix
    } = _ref;
    return function assign2(object, index, value) {
      try {
        if (Array.isArray(object)) {
          return matrix(object).subset(index, value).valueOf();
        } else if (object && typeof object.subset === "function") {
          return object.subset(index, value);
        } else if (typeof object === "string") {
          return subset(object, index, value);
        } else if (typeof object === "object") {
          if (!index.isObjectProperty()) {
            throw TypeError("Cannot apply a numeric index as object property");
          }
          setSafeProperty(object, index.getObjectProperty(), value);
          return object;
        } else {
          throw new TypeError("Cannot apply index: unsupported type of object");
        }
      } catch (err) {
        throw errorTransform(err);
      }
    };
  }
  var properties = [{
    // assignment
    AssignmentNode: {},
    FunctionAssignmentNode: {}
  }, {
    // conditional expression
    ConditionalNode: {
      latexLeftParens: false,
      latexRightParens: false,
      latexParens: false
      // conditionals don't need parentheses in LaTeX because
      // they are 2 dimensional
    }
  }, {
    // logical or
    "OperatorNode:or": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // logical xor
    "OperatorNode:xor": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // logical and
    "OperatorNode:and": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // bitwise or
    "OperatorNode:bitOr": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // bitwise xor
    "OperatorNode:bitXor": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // bitwise and
    "OperatorNode:bitAnd": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // relational operators
    "OperatorNode:equal": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:unequal": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:smaller": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:larger": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:smallerEq": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:largerEq": {
      associativity: "left",
      associativeWith: []
    },
    RelationalNode: {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // bitshift operators
    "OperatorNode:leftShift": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:rightArithShift": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:rightLogShift": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // unit conversion
    "OperatorNode:to": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // range
    RangeNode: {}
  }, {
    // addition, subtraction
    "OperatorNode:add": {
      associativity: "left",
      associativeWith: ["OperatorNode:add", "OperatorNode:subtract"]
    },
    "OperatorNode:subtract": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // multiply, divide, modulus
    "OperatorNode:multiply": {
      associativity: "left",
      associativeWith: ["OperatorNode:multiply", "OperatorNode:divide", "Operator:dotMultiply", "Operator:dotDivide"]
    },
    "OperatorNode:divide": {
      associativity: "left",
      associativeWith: [],
      latexLeftParens: false,
      latexRightParens: false,
      latexParens: false
      // fractions don't require parentheses because
      // they're 2 dimensional, so parens aren't needed
      // in LaTeX
    },
    "OperatorNode:dotMultiply": {
      associativity: "left",
      associativeWith: ["OperatorNode:multiply", "OperatorNode:divide", "OperatorNode:dotMultiply", "OperatorNode:doDivide"]
    },
    "OperatorNode:dotDivide": {
      associativity: "left",
      associativeWith: []
    },
    "OperatorNode:mod": {
      associativity: "left",
      associativeWith: []
    }
  }, {
    // unary prefix operators
    "OperatorNode:unaryPlus": {
      associativity: "right"
    },
    "OperatorNode:unaryMinus": {
      associativity: "right"
    },
    "OperatorNode:bitNot": {
      associativity: "right"
    },
    "OperatorNode:not": {
      associativity: "right"
    }
  }, {
    // exponentiation
    "OperatorNode:pow": {
      associativity: "right",
      associativeWith: [],
      latexRightParens: false
      // the exponent doesn't need parentheses in
      // LaTeX because it's 2 dimensional
      // (it's on top)
    },
    "OperatorNode:dotPow": {
      associativity: "right",
      associativeWith: []
    }
  }, {
    // factorial
    "OperatorNode:factorial": {
      associativity: "left"
    }
  }, {
    // matrix transpose
    "OperatorNode:transpose": {
      associativity: "left"
    }
  }];
  function getPrecedence(_node, parenthesis) {
    var node = _node;
    if (parenthesis !== "keep") {
      node = _node.getContent();
    }
    var identifier = node.getIdentifier();
    for (var i = 0; i < properties.length; i++) {
      if (identifier in properties[i]) {
        return i;
      }
    }
    return null;
  }
  function getAssociativity(_node, parenthesis) {
    var node = _node;
    if (parenthesis !== "keep") {
      node = _node.getContent();
    }
    var identifier = node.getIdentifier();
    var index = getPrecedence(node, parenthesis);
    if (index === null) {
      return null;
    }
    var property = properties[index][identifier];
    if (hasOwnProperty(property, "associativity")) {
      if (property.associativity === "left") {
        return "left";
      }
      if (property.associativity === "right") {
        return "right";
      }
      throw Error("'" + identifier + "' has the invalid associativity '" + property.associativity + "'.");
    }
    return null;
  }
  function isAssociativeWith(nodeA, nodeB, parenthesis) {
    var a = parenthesis !== "keep" ? nodeA.getContent() : nodeA;
    var b = parenthesis !== "keep" ? nodeA.getContent() : nodeB;
    var identifierA = a.getIdentifier();
    var identifierB = b.getIdentifier();
    var index = getPrecedence(a, parenthesis);
    if (index === null) {
      return null;
    }
    var property = properties[index][identifierA];
    if (hasOwnProperty(property, "associativeWith") && property.associativeWith instanceof Array) {
      for (var i = 0; i < property.associativeWith.length; i++) {
        if (property.associativeWith[i] === identifierB) {
          return true;
        }
      }
      return false;
    }
    return null;
  }
  var name$g = "AssignmentNode";
  var dependencies$g = [
    "subset",
    "?matrix",
    // FIXME: should not be needed at all, should be handled by subset
    "Node"
  ];
  var createAssignmentNode = /* @__PURE__ */ factory(name$g, dependencies$g, (_ref) => {
    var {
      subset,
      matrix,
      Node
    } = _ref;
    var access = accessFactory({
      subset
    });
    var assign2 = assignFactory({
      subset,
      matrix
    });
    function AssignmentNode(object, index, value) {
      if (!(this instanceof AssignmentNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.object = object;
      this.index = value ? index : null;
      this.value = value || index;
      if (!isSymbolNode(object) && !isAccessorNode(object)) {
        throw new TypeError('SymbolNode or AccessorNode expected as "object"');
      }
      if (isSymbolNode(object) && object.name === "end") {
        throw new Error('Cannot assign to symbol "end"');
      }
      if (this.index && !isIndexNode(this.index)) {
        throw new TypeError('IndexNode expected as "index"');
      }
      if (!isNode(this.value)) {
        throw new TypeError('Node expected as "value"');
      }
      Object.defineProperty(this, "name", {
        get: function() {
          if (this.index) {
            return this.index.isObjectProperty() ? this.index.getObjectProperty() : "";
          } else {
            return this.object.name || "";
          }
        }.bind(this),
        set: function set() {
          throw new Error("Cannot assign a new name, name is read-only");
        }
      });
    }
    AssignmentNode.prototype = new Node();
    AssignmentNode.prototype.type = "AssignmentNode";
    AssignmentNode.prototype.isAssignmentNode = true;
    AssignmentNode.prototype._compile = function(math2, argNames) {
      var evalObject = this.object._compile(math2, argNames);
      var evalIndex = this.index ? this.index._compile(math2, argNames) : null;
      var evalValue = this.value._compile(math2, argNames);
      var name2 = this.object.name;
      if (!this.index) {
        if (!isSymbolNode(this.object)) {
          throw new TypeError("SymbolNode expected as object");
        }
        return function evalAssignmentNode(scope, args, context) {
          var value = evalValue(scope, args, context);
          scope.set(name2, value);
          return value;
        };
      } else if (this.index.isObjectProperty()) {
        var prop = this.index.getObjectProperty();
        return function evalAssignmentNode(scope, args, context) {
          var object = evalObject(scope, args, context);
          var value = evalValue(scope, args, context);
          setSafeProperty(object, prop, value);
          return value;
        };
      } else if (isSymbolNode(this.object)) {
        return function evalAssignmentNode(scope, args, context) {
          var childObject = evalObject(scope, args, context);
          var value = evalValue(scope, args, context);
          var index = evalIndex(scope, args, childObject);
          scope.set(name2, assign2(childObject, index, value));
          return value;
        };
      } else {
        var evalParentObject = this.object.object._compile(math2, argNames);
        if (this.object.index.isObjectProperty()) {
          var parentProp = this.object.index.getObjectProperty();
          return function evalAssignmentNode(scope, args, context) {
            var parent = evalParentObject(scope, args, context);
            var childObject = getSafeProperty(parent, parentProp);
            var index = evalIndex(scope, args, childObject);
            var value = evalValue(scope, args, context);
            setSafeProperty(parent, parentProp, assign2(childObject, index, value));
            return value;
          };
        } else {
          var evalParentIndex = this.object.index._compile(math2, argNames);
          return function evalAssignmentNode(scope, args, context) {
            var parent = evalParentObject(scope, args, context);
            var parentIndex = evalParentIndex(scope, args, parent);
            var childObject = access(parent, parentIndex);
            var index = evalIndex(scope, args, childObject);
            var value = evalValue(scope, args, context);
            assign2(parent, parentIndex, assign2(childObject, index, value));
            return value;
          };
        }
      }
    };
    AssignmentNode.prototype.forEach = function(callback) {
      callback(this.object, "object", this);
      if (this.index) {
        callback(this.index, "index", this);
      }
      callback(this.value, "value", this);
    };
    AssignmentNode.prototype.map = function(callback) {
      var object = this._ifNode(callback(this.object, "object", this));
      var index = this.index ? this._ifNode(callback(this.index, "index", this)) : null;
      var value = this._ifNode(callback(this.value, "value", this));
      return new AssignmentNode(object, index, value);
    };
    AssignmentNode.prototype.clone = function() {
      return new AssignmentNode(this.object, this.index, this.value);
    };
    function needParenthesis(node, parenthesis) {
      if (!parenthesis) {
        parenthesis = "keep";
      }
      var precedence = getPrecedence(node, parenthesis);
      var exprPrecedence = getPrecedence(node.value, parenthesis);
      return parenthesis === "all" || exprPrecedence !== null && exprPrecedence <= precedence;
    }
    AssignmentNode.prototype._toString = function(options) {
      var object = this.object.toString(options);
      var index = this.index ? this.index.toString(options) : "";
      var value = this.value.toString(options);
      if (needParenthesis(this, options && options.parenthesis)) {
        value = "(" + value + ")";
      }
      return object + index + " = " + value;
    };
    AssignmentNode.prototype.toJSON = function() {
      return {
        mathjs: "AssignmentNode",
        object: this.object,
        index: this.index,
        value: this.value
      };
    };
    AssignmentNode.fromJSON = function(json) {
      return new AssignmentNode(json.object, json.index, json.value);
    };
    AssignmentNode.prototype.toHTML = function(options) {
      var object = this.object.toHTML(options);
      var index = this.index ? this.index.toHTML(options) : "";
      var value = this.value.toHTML(options);
      if (needParenthesis(this, options && options.parenthesis)) {
        value = '<span class="math-paranthesis math-round-parenthesis">(</span>' + value + '<span class="math-paranthesis math-round-parenthesis">)</span>';
      }
      return object + index + '<span class="math-operator math-assignment-operator math-variable-assignment-operator math-binary-operator">=</span>' + value;
    };
    AssignmentNode.prototype._toTex = function(options) {
      var object = this.object.toTex(options);
      var index = this.index ? this.index.toTex(options) : "";
      var value = this.value.toTex(options);
      if (needParenthesis(this, options && options.parenthesis)) {
        value = "\\left(".concat(value, "\\right)");
      }
      return object + index + ":=" + value;
    };
    return AssignmentNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$f = "BlockNode";
  var dependencies$f = ["ResultSet", "Node"];
  var createBlockNode = /* @__PURE__ */ factory(name$f, dependencies$f, (_ref) => {
    var {
      ResultSet,
      Node
    } = _ref;
    function BlockNode(blocks) {
      if (!(this instanceof BlockNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!Array.isArray(blocks)) throw new Error("Array expected");
      this.blocks = blocks.map(function(block) {
        var node = block && block.node;
        var visible = block && block.visible !== void 0 ? block.visible : true;
        if (!isNode(node)) throw new TypeError('Property "node" must be a Node');
        if (typeof visible !== "boolean") throw new TypeError('Property "visible" must be a boolean');
        return {
          node,
          visible
        };
      });
    }
    BlockNode.prototype = new Node();
    BlockNode.prototype.type = "BlockNode";
    BlockNode.prototype.isBlockNode = true;
    BlockNode.prototype._compile = function(math2, argNames) {
      var evalBlocks = map(this.blocks, function(block) {
        return {
          evaluate: block.node._compile(math2, argNames),
          visible: block.visible
        };
      });
      return function evalBlockNodes(scope, args, context) {
        var results = [];
        forEach(evalBlocks, function evalBlockNode(block) {
          var result = block.evaluate(scope, args, context);
          if (block.visible) {
            results.push(result);
          }
        });
        return new ResultSet(results);
      };
    };
    BlockNode.prototype.forEach = function(callback) {
      for (var i = 0; i < this.blocks.length; i++) {
        callback(this.blocks[i].node, "blocks[" + i + "].node", this);
      }
    };
    BlockNode.prototype.map = function(callback) {
      var blocks = [];
      for (var i = 0; i < this.blocks.length; i++) {
        var block = this.blocks[i];
        var node = this._ifNode(callback(block.node, "blocks[" + i + "].node", this));
        blocks[i] = {
          node,
          visible: block.visible
        };
      }
      return new BlockNode(blocks);
    };
    BlockNode.prototype.clone = function() {
      var blocks = this.blocks.map(function(block) {
        return {
          node: block.node,
          visible: block.visible
        };
      });
      return new BlockNode(blocks);
    };
    BlockNode.prototype._toString = function(options) {
      return this.blocks.map(function(param) {
        return param.node.toString(options) + (param.visible ? "" : ";");
      }).join("\n");
    };
    BlockNode.prototype.toJSON = function() {
      return {
        mathjs: "BlockNode",
        blocks: this.blocks
      };
    };
    BlockNode.fromJSON = function(json) {
      return new BlockNode(json.blocks);
    };
    BlockNode.prototype.toHTML = function(options) {
      return this.blocks.map(function(param) {
        return param.node.toHTML(options) + (param.visible ? "" : '<span class="math-separator">;</span>');
      }).join('<span class="math-separator"><br /></span>');
    };
    BlockNode.prototype._toTex = function(options) {
      return this.blocks.map(function(param) {
        return param.node.toTex(options) + (param.visible ? "" : ";");
      }).join("\\;\\;\n");
    };
    return BlockNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$e = "ConditionalNode";
  var dependencies$e = ["Node"];
  var createConditionalNode = /* @__PURE__ */ factory(name$e, dependencies$e, (_ref) => {
    var {
      Node
    } = _ref;
    function ConditionalNode(condition, trueExpr, falseExpr) {
      if (!(this instanceof ConditionalNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!isNode(condition)) throw new TypeError("Parameter condition must be a Node");
      if (!isNode(trueExpr)) throw new TypeError("Parameter trueExpr must be a Node");
      if (!isNode(falseExpr)) throw new TypeError("Parameter falseExpr must be a Node");
      this.condition = condition;
      this.trueExpr = trueExpr;
      this.falseExpr = falseExpr;
    }
    ConditionalNode.prototype = new Node();
    ConditionalNode.prototype.type = "ConditionalNode";
    ConditionalNode.prototype.isConditionalNode = true;
    ConditionalNode.prototype._compile = function(math2, argNames) {
      var evalCondition = this.condition._compile(math2, argNames);
      var evalTrueExpr = this.trueExpr._compile(math2, argNames);
      var evalFalseExpr = this.falseExpr._compile(math2, argNames);
      return function evalConditionalNode(scope, args, context) {
        return testCondition(evalCondition(scope, args, context)) ? evalTrueExpr(scope, args, context) : evalFalseExpr(scope, args, context);
      };
    };
    ConditionalNode.prototype.forEach = function(callback) {
      callback(this.condition, "condition", this);
      callback(this.trueExpr, "trueExpr", this);
      callback(this.falseExpr, "falseExpr", this);
    };
    ConditionalNode.prototype.map = function(callback) {
      return new ConditionalNode(this._ifNode(callback(this.condition, "condition", this)), this._ifNode(callback(this.trueExpr, "trueExpr", this)), this._ifNode(callback(this.falseExpr, "falseExpr", this)));
    };
    ConditionalNode.prototype.clone = function() {
      return new ConditionalNode(this.condition, this.trueExpr, this.falseExpr);
    };
    ConditionalNode.prototype._toString = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var precedence = getPrecedence(this, parenthesis);
      var condition = this.condition.toString(options);
      var conditionPrecedence = getPrecedence(this.condition, parenthesis);
      if (parenthesis === "all" || this.condition.type === "OperatorNode" || conditionPrecedence !== null && conditionPrecedence <= precedence) {
        condition = "(" + condition + ")";
      }
      var trueExpr = this.trueExpr.toString(options);
      var truePrecedence = getPrecedence(this.trueExpr, parenthesis);
      if (parenthesis === "all" || this.trueExpr.type === "OperatorNode" || truePrecedence !== null && truePrecedence <= precedence) {
        trueExpr = "(" + trueExpr + ")";
      }
      var falseExpr = this.falseExpr.toString(options);
      var falsePrecedence = getPrecedence(this.falseExpr, parenthesis);
      if (parenthesis === "all" || this.falseExpr.type === "OperatorNode" || falsePrecedence !== null && falsePrecedence <= precedence) {
        falseExpr = "(" + falseExpr + ")";
      }
      return condition + " ? " + trueExpr + " : " + falseExpr;
    };
    ConditionalNode.prototype.toJSON = function() {
      return {
        mathjs: "ConditionalNode",
        condition: this.condition,
        trueExpr: this.trueExpr,
        falseExpr: this.falseExpr
      };
    };
    ConditionalNode.fromJSON = function(json) {
      return new ConditionalNode(json.condition, json.trueExpr, json.falseExpr);
    };
    ConditionalNode.prototype.toHTML = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var precedence = getPrecedence(this, parenthesis);
      var condition = this.condition.toHTML(options);
      var conditionPrecedence = getPrecedence(this.condition, parenthesis);
      if (parenthesis === "all" || this.condition.type === "OperatorNode" || conditionPrecedence !== null && conditionPrecedence <= precedence) {
        condition = '<span class="math-parenthesis math-round-parenthesis">(</span>' + condition + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      var trueExpr = this.trueExpr.toHTML(options);
      var truePrecedence = getPrecedence(this.trueExpr, parenthesis);
      if (parenthesis === "all" || this.trueExpr.type === "OperatorNode" || truePrecedence !== null && truePrecedence <= precedence) {
        trueExpr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + trueExpr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      var falseExpr = this.falseExpr.toHTML(options);
      var falsePrecedence = getPrecedence(this.falseExpr, parenthesis);
      if (parenthesis === "all" || this.falseExpr.type === "OperatorNode" || falsePrecedence !== null && falsePrecedence <= precedence) {
        falseExpr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + falseExpr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      return condition + '<span class="math-operator math-conditional-operator">?</span>' + trueExpr + '<span class="math-operator math-conditional-operator">:</span>' + falseExpr;
    };
    ConditionalNode.prototype._toTex = function(options) {
      return "\\begin{cases} {" + this.trueExpr.toTex(options) + "}, &\\quad{\\text{if }\\;" + this.condition.toTex(options) + "}\\\\{" + this.falseExpr.toTex(options) + "}, &\\quad{\\text{otherwise}}\\end{cases}";
    };
    function testCondition(condition) {
      if (typeof condition === "number" || typeof condition === "boolean" || typeof condition === "string") {
        return !!condition;
      }
      if (condition) {
        if (isBigNumber(condition)) {
          return !condition.isZero();
        }
        if (isComplex(condition)) {
          return !!(condition.re || condition.im);
        }
        if (isUnit(condition)) {
          return !!condition.value;
        }
      }
      if (condition === null || condition === void 0) {
        return false;
      }
      throw new TypeError('Unsupported type of condition "' + typeOf(condition) + '"');
    }
    return ConditionalNode;
  }, {
    isClass: true,
    isNode: true
  });
  var dist;
  var hasRequiredDist;
  function requireDist() {
    if (hasRequiredDist) return dist;
    hasRequiredDist = 1;
    var _extends2 = Object.assign || function(target) {
      for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }
      return target;
    };
    var defaultEscapes = {
      "{": "\\{",
      "}": "\\}",
      "\\": "\\textbackslash{}",
      "#": "\\#",
      $: "\\$",
      "%": "\\%",
      "&": "\\&",
      "^": "\\textasciicircum{}",
      _: "\\_",
      "~": "\\textasciitilde{}"
    };
    var formatEscapes = {
      "–": "\\--",
      "—": "\\---",
      " ": "~",
      "	": "\\qquad{}",
      "\r\n": "\\newline{}",
      "\n": "\\newline{}"
    };
    var defaultEscapeMapFn = function defaultEscapeMapFn2(defaultEscapes2, formatEscapes2) {
      return _extends2({}, defaultEscapes2, formatEscapes2);
    };
    dist = function(str) {
      var _ref = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, _ref$preserveFormatti = _ref.preserveFormatting, preserveFormatting = _ref$preserveFormatti === void 0 ? false : _ref$preserveFormatti, _ref$escapeMapFn = _ref.escapeMapFn, escapeMapFn = _ref$escapeMapFn === void 0 ? defaultEscapeMapFn : _ref$escapeMapFn;
      var runningStr = String(str);
      var result = "";
      var escapes = escapeMapFn(_extends2({}, defaultEscapes), preserveFormatting ? _extends2({}, formatEscapes) : {});
      var escapeKeys = Object.keys(escapes);
      var _loop = function _loop2() {
        var specialCharFound = false;
        escapeKeys.forEach(function(key, index) {
          if (specialCharFound) {
            return;
          }
          if (runningStr.length >= key.length && runningStr.slice(0, key.length) === key) {
            result += escapes[escapeKeys[index]];
            runningStr = runningStr.slice(key.length, runningStr.length);
            specialCharFound = true;
          }
        });
        if (!specialCharFound) {
          result += runningStr.slice(0, 1);
          runningStr = runningStr.slice(1, runningStr.length);
        }
      };
      while (runningStr) {
        _loop();
      }
      return result;
    };
    return dist;
  }
  var distExports = requireDist();
  const escapeLatexLib = /* @__PURE__ */ getDefaultExportFromCjs(distExports);
  var latexSymbols = {
    // GREEK LETTERS
    Alpha: "A",
    alpha: "\\alpha",
    Beta: "B",
    beta: "\\beta",
    Gamma: "\\Gamma",
    gamma: "\\gamma",
    Delta: "\\Delta",
    delta: "\\delta",
    Epsilon: "E",
    epsilon: "\\epsilon",
    varepsilon: "\\varepsilon",
    Zeta: "Z",
    zeta: "\\zeta",
    Eta: "H",
    eta: "\\eta",
    Theta: "\\Theta",
    theta: "\\theta",
    vartheta: "\\vartheta",
    Iota: "I",
    iota: "\\iota",
    Kappa: "K",
    kappa: "\\kappa",
    varkappa: "\\varkappa",
    Lambda: "\\Lambda",
    lambda: "\\lambda",
    Mu: "M",
    mu: "\\mu",
    Nu: "N",
    nu: "\\nu",
    Xi: "\\Xi",
    xi: "\\xi",
    Omicron: "O",
    omicron: "o",
    Pi: "\\Pi",
    pi: "\\pi",
    varpi: "\\varpi",
    Rho: "P",
    rho: "\\rho",
    varrho: "\\varrho",
    Sigma: "\\Sigma",
    sigma: "\\sigma",
    varsigma: "\\varsigma",
    Tau: "T",
    tau: "\\tau",
    Upsilon: "\\Upsilon",
    upsilon: "\\upsilon",
    Phi: "\\Phi",
    phi: "\\phi",
    varphi: "\\varphi",
    Chi: "X",
    chi: "\\chi",
    Psi: "\\Psi",
    psi: "\\psi",
    Omega: "\\Omega",
    omega: "\\omega",
    // logic
    true: "\\mathrm{True}",
    false: "\\mathrm{False}",
    // other
    i: "i",
    // TODO use \i ??
    inf: "\\infty",
    Inf: "\\infty",
    infinity: "\\infty",
    Infinity: "\\infty",
    oo: "\\infty",
    lim: "\\lim",
    undefined: "\\mathbf{?}"
  };
  var latexOperators = {
    transpose: "^\\top",
    ctranspose: "^H",
    factorial: "!",
    pow: "^",
    dotPow: ".^\\wedge",
    // TODO find ideal solution
    unaryPlus: "+",
    unaryMinus: "-",
    bitNot: "\\~",
    // TODO find ideal solution
    not: "\\neg",
    multiply: "\\cdot",
    divide: "\\frac",
    // TODO how to handle that properly?
    dotMultiply: ".\\cdot",
    // TODO find ideal solution
    dotDivide: ".:",
    // TODO find ideal solution
    mod: "\\mod",
    add: "+",
    subtract: "-",
    to: "\\rightarrow",
    leftShift: "<<",
    rightArithShift: ">>",
    rightLogShift: ">>>",
    equal: "=",
    unequal: "\\neq",
    smaller: "<",
    larger: ">",
    smallerEq: "\\leq",
    largerEq: "\\geq",
    bitAnd: "\\&",
    bitXor: "\\underline{|}",
    bitOr: "|",
    and: "\\wedge",
    xor: "\\veebar",
    or: "\\vee"
  };
  var latexFunctions = {
    // arithmetic
    abs: {
      1: "\\left|${args[0]}\\right|"
    },
    add: {
      2: "\\left(${args[0]}".concat(latexOperators.add, "${args[1]}\\right)")
    },
    cbrt: {
      1: "\\sqrt[3]{${args[0]}}"
    },
    ceil: {
      1: "\\left\\lceil${args[0]}\\right\\rceil"
    },
    cube: {
      1: "\\left(${args[0]}\\right)^3"
    },
    divide: {
      2: "\\frac{${args[0]}}{${args[1]}}"
    },
    dotDivide: {
      2: "\\left(${args[0]}".concat(latexOperators.dotDivide, "${args[1]}\\right)")
    },
    dotMultiply: {
      2: "\\left(${args[0]}".concat(latexOperators.dotMultiply, "${args[1]}\\right)")
    },
    dotPow: {
      2: "\\left(${args[0]}".concat(latexOperators.dotPow, "${args[1]}\\right)")
    },
    exp: {
      1: "\\exp\\left(${args[0]}\\right)"
    },
    expm1: "\\left(e".concat(latexOperators.pow, "{${args[0]}}-1\\right)"),
    fix: {
      1: "\\mathrm{${name}}\\left(${args[0]}\\right)"
    },
    floor: {
      1: "\\left\\lfloor${args[0]}\\right\\rfloor"
    },
    gcd: "\\gcd\\left(${args}\\right)",
    hypot: "\\hypot\\left(${args}\\right)",
    log: {
      1: "\\ln\\left(${args[0]}\\right)",
      2: "\\log_{${args[1]}}\\left(${args[0]}\\right)"
    },
    log10: {
      1: "\\log_{10}\\left(${args[0]}\\right)"
    },
    log1p: {
      1: "\\ln\\left(${args[0]}+1\\right)",
      2: "\\log_{${args[1]}}\\left(${args[0]}+1\\right)"
    },
    log2: "\\log_{2}\\left(${args[0]}\\right)",
    mod: {
      2: "\\left(${args[0]}".concat(latexOperators.mod, "${args[1]}\\right)")
    },
    multiply: {
      2: "\\left(${args[0]}".concat(latexOperators.multiply, "${args[1]}\\right)")
    },
    norm: {
      1: "\\left\\|${args[0]}\\right\\|",
      2: void 0
      // use default template
    },
    nthRoot: {
      2: "\\sqrt[${args[1]}]{${args[0]}}"
    },
    nthRoots: {
      2: "\\{y : $y^{args[1]} = {${args[0]}}\\}"
    },
    pow: {
      2: "\\left(${args[0]}\\right)".concat(latexOperators.pow, "{${args[1]}}")
    },
    round: {
      1: "\\left\\lfloor${args[0]}\\right\\rceil",
      2: void 0
      // use default template
    },
    sign: {
      1: "\\mathrm{${name}}\\left(${args[0]}\\right)"
    },
    sqrt: {
      1: "\\sqrt{${args[0]}}"
    },
    square: {
      1: "\\left(${args[0]}\\right)^2"
    },
    subtract: {
      2: "\\left(${args[0]}".concat(latexOperators.subtract, "${args[1]}\\right)")
    },
    unaryMinus: {
      1: "".concat(latexOperators.unaryMinus, "\\left(${args[0]}\\right)")
    },
    unaryPlus: {
      1: "".concat(latexOperators.unaryPlus, "\\left(${args[0]}\\right)")
    },
    // bitwise
    bitAnd: {
      2: "\\left(${args[0]}".concat(latexOperators.bitAnd, "${args[1]}\\right)")
    },
    bitNot: {
      1: latexOperators.bitNot + "\\left(${args[0]}\\right)"
    },
    bitOr: {
      2: "\\left(${args[0]}".concat(latexOperators.bitOr, "${args[1]}\\right)")
    },
    bitXor: {
      2: "\\left(${args[0]}".concat(latexOperators.bitXor, "${args[1]}\\right)")
    },
    leftShift: {
      2: "\\left(${args[0]}".concat(latexOperators.leftShift, "${args[1]}\\right)")
    },
    rightArithShift: {
      2: "\\left(${args[0]}".concat(latexOperators.rightArithShift, "${args[1]}\\right)")
    },
    rightLogShift: {
      2: "\\left(${args[0]}".concat(latexOperators.rightLogShift, "${args[1]}\\right)")
    },
    // combinatorics
    bellNumbers: {
      1: "\\mathrm{B}_{${args[0]}}"
    },
    catalan: {
      1: "\\mathrm{C}_{${args[0]}}"
    },
    stirlingS2: {
      2: "\\mathrm{S}\\left(${args}\\right)"
    },
    // complex
    arg: {
      1: "\\arg\\left(${args[0]}\\right)"
    },
    conj: {
      1: "\\left(${args[0]}\\right)^*"
    },
    im: {
      1: "\\Im\\left\\lbrace${args[0]}\\right\\rbrace"
    },
    re: {
      1: "\\Re\\left\\lbrace${args[0]}\\right\\rbrace"
    },
    // logical
    and: {
      2: "\\left(${args[0]}".concat(latexOperators.and, "${args[1]}\\right)")
    },
    not: {
      1: latexOperators.not + "\\left(${args[0]}\\right)"
    },
    or: {
      2: "\\left(${args[0]}".concat(latexOperators.or, "${args[1]}\\right)")
    },
    xor: {
      2: "\\left(${args[0]}".concat(latexOperators.xor, "${args[1]}\\right)")
    },
    // matrix
    cross: {
      2: "\\left(${args[0]}\\right)\\times\\left(${args[1]}\\right)"
    },
    ctranspose: {
      1: "\\left(${args[0]}\\right)".concat(latexOperators.ctranspose)
    },
    det: {
      1: "\\det\\left(${args[0]}\\right)"
    },
    dot: {
      2: "\\left(${args[0]}\\cdot${args[1]}\\right)"
    },
    expm: {
      1: "\\exp\\left(${args[0]}\\right)"
    },
    inv: {
      1: "\\left(${args[0]}\\right)^{-1}"
    },
    pinv: {
      1: "\\left(${args[0]}\\right)^{+}"
    },
    sqrtm: {
      1: "{${args[0]}}".concat(latexOperators.pow, "{\\frac{1}{2}}")
    },
    trace: {
      1: "\\mathrm{tr}\\left(${args[0]}\\right)"
    },
    transpose: {
      1: "\\left(${args[0]}\\right)".concat(latexOperators.transpose)
    },
    // probability
    combinations: {
      2: "\\binom{${args[0]}}{${args[1]}}"
    },
    combinationsWithRep: {
      2: "\\left(\\!\\!{\\binom{${args[0]}}{${args[1]}}}\\!\\!\\right)"
    },
    factorial: {
      1: "\\left(${args[0]}\\right)".concat(latexOperators.factorial)
    },
    gamma: {
      1: "\\Gamma\\left(${args[0]}\\right)"
    },
    lgamma: {
      1: "\\ln\\Gamma\\left(${args[0]}\\right)"
    },
    // relational
    equal: {
      2: "\\left(${args[0]}".concat(latexOperators.equal, "${args[1]}\\right)")
    },
    larger: {
      2: "\\left(${args[0]}".concat(latexOperators.larger, "${args[1]}\\right)")
    },
    largerEq: {
      2: "\\left(${args[0]}".concat(latexOperators.largerEq, "${args[1]}\\right)")
    },
    smaller: {
      2: "\\left(${args[0]}".concat(latexOperators.smaller, "${args[1]}\\right)")
    },
    smallerEq: {
      2: "\\left(${args[0]}".concat(latexOperators.smallerEq, "${args[1]}\\right)")
    },
    unequal: {
      2: "\\left(${args[0]}".concat(latexOperators.unequal, "${args[1]}\\right)")
    },
    // special
    erf: {
      1: "erf\\left(${args[0]}\\right)"
    },
    // statistics
    max: "\\max\\left(${args}\\right)",
    min: "\\min\\left(${args}\\right)",
    variance: "\\mathrm{Var}\\left(${args}\\right)",
    // trigonometry
    acos: {
      1: "\\cos^{-1}\\left(${args[0]}\\right)"
    },
    acosh: {
      1: "\\cosh^{-1}\\left(${args[0]}\\right)"
    },
    acot: {
      1: "\\cot^{-1}\\left(${args[0]}\\right)"
    },
    acoth: {
      1: "\\coth^{-1}\\left(${args[0]}\\right)"
    },
    acsc: {
      1: "\\csc^{-1}\\left(${args[0]}\\right)"
    },
    acsch: {
      1: "\\mathrm{csch}^{-1}\\left(${args[0]}\\right)"
    },
    asec: {
      1: "\\sec^{-1}\\left(${args[0]}\\right)"
    },
    asech: {
      1: "\\mathrm{sech}^{-1}\\left(${args[0]}\\right)"
    },
    asin: {
      1: "\\sin^{-1}\\left(${args[0]}\\right)"
    },
    asinh: {
      1: "\\sinh^{-1}\\left(${args[0]}\\right)"
    },
    atan: {
      1: "\\tan^{-1}\\left(${args[0]}\\right)"
    },
    atan2: {
      2: "\\mathrm{atan2}\\left(${args}\\right)"
    },
    atanh: {
      1: "\\tanh^{-1}\\left(${args[0]}\\right)"
    },
    cos: {
      1: "\\cos\\left(${args[0]}\\right)"
    },
    cosh: {
      1: "\\cosh\\left(${args[0]}\\right)"
    },
    cot: {
      1: "\\cot\\left(${args[0]}\\right)"
    },
    coth: {
      1: "\\coth\\left(${args[0]}\\right)"
    },
    csc: {
      1: "\\csc\\left(${args[0]}\\right)"
    },
    csch: {
      1: "\\mathrm{csch}\\left(${args[0]}\\right)"
    },
    sec: {
      1: "\\sec\\left(${args[0]}\\right)"
    },
    sech: {
      1: "\\mathrm{sech}\\left(${args[0]}\\right)"
    },
    sin: {
      1: "\\sin\\left(${args[0]}\\right)"
    },
    sinh: {
      1: "\\sinh\\left(${args[0]}\\right)"
    },
    tan: {
      1: "\\tan\\left(${args[0]}\\right)"
    },
    tanh: {
      1: "\\tanh\\left(${args[0]}\\right)"
    },
    // unit
    to: {
      2: "\\left(${args[0]}".concat(latexOperators.to, "${args[1]}\\right)")
    },
    // utils
    numeric: function numeric(node, options) {
      return node.args[0].toTex();
    },
    // type
    number: {
      0: "0",
      1: "\\left(${args[0]}\\right)",
      2: "\\left(\\left(${args[0]}\\right)${args[1]}\\right)"
    },
    string: {
      0: '\\mathtt{""}',
      1: "\\mathrm{string}\\left(${args[0]}\\right)"
    },
    bignumber: {
      0: "0",
      1: "\\left(${args[0]}\\right)"
    },
    complex: {
      0: "0",
      1: "\\left(${args[0]}\\right)",
      2: "\\left(\\left(${args[0]}\\right)+".concat(latexSymbols.i, "\\cdot\\left(${args[1]}\\right)\\right)")
    },
    matrix: {
      0: "\\begin{bmatrix}\\end{bmatrix}",
      1: "\\left(${args[0]}\\right)",
      2: "\\left(${args[0]}\\right)"
    },
    sparse: {
      0: "\\begin{bsparse}\\end{bsparse}",
      1: "\\left(${args[0]}\\right)"
    },
    unit: {
      1: "\\left(${args[0]}\\right)",
      2: "\\left(\\left(${args[0]}\\right)${args[1]}\\right)"
    }
  };
  var defaultTemplate = "\\mathrm{${name}}\\left(${args}\\right)";
  var latexUnits = {
    deg: "^\\circ"
  };
  function escapeLatex(string) {
    return escapeLatexLib(string, {
      preserveFormatting: true
    });
  }
  function toSymbol(name2, isUnit2) {
    isUnit2 = typeof isUnit2 === "undefined" ? false : isUnit2;
    if (isUnit2) {
      if (hasOwnProperty(latexUnits, name2)) {
        return latexUnits[name2];
      }
      return "\\mathrm{" + escapeLatex(name2) + "}";
    }
    if (hasOwnProperty(latexSymbols, name2)) {
      return latexSymbols[name2];
    }
    return escapeLatex(name2);
  }
  var name$d = "ConstantNode";
  var dependencies$d = ["Node"];
  var createConstantNode = /* @__PURE__ */ factory(name$d, dependencies$d, (_ref) => {
    var {
      Node
    } = _ref;
    function ConstantNode(value) {
      if (!(this instanceof ConstantNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.value = value;
    }
    ConstantNode.prototype = new Node();
    ConstantNode.prototype.type = "ConstantNode";
    ConstantNode.prototype.isConstantNode = true;
    ConstantNode.prototype._compile = function(math2, argNames) {
      var value = this.value;
      return function evalConstantNode() {
        return value;
      };
    };
    ConstantNode.prototype.forEach = function(callback) {
    };
    ConstantNode.prototype.map = function(callback) {
      return this.clone();
    };
    ConstantNode.prototype.clone = function() {
      return new ConstantNode(this.value);
    };
    ConstantNode.prototype._toString = function(options) {
      return format(this.value, options);
    };
    ConstantNode.prototype.toHTML = function(options) {
      var value = this._toString(options);
      switch (typeOf(this.value)) {
        case "number":
        case "BigNumber":
        case "Fraction":
          return '<span class="math-number">' + value + "</span>";
        case "string":
          return '<span class="math-string">' + value + "</span>";
        case "boolean":
          return '<span class="math-boolean">' + value + "</span>";
        case "null":
          return '<span class="math-null-symbol">' + value + "</span>";
        case "undefined":
          return '<span class="math-undefined">' + value + "</span>";
        default:
          return '<span class="math-symbol">' + value + "</span>";
      }
    };
    ConstantNode.prototype.toJSON = function() {
      return {
        mathjs: "ConstantNode",
        value: this.value
      };
    };
    ConstantNode.fromJSON = function(json) {
      return new ConstantNode(json.value);
    };
    ConstantNode.prototype._toTex = function(options) {
      var value = this._toString(options);
      switch (typeOf(this.value)) {
        case "string":
          return "\\mathtt{" + escapeLatex(value) + "}";
        case "number":
        case "BigNumber":
          {
            if (!isFinite(this.value)) {
              return this.value.valueOf() < 0 ? "-\\infty" : "\\infty";
            }
            var index = value.toLowerCase().indexOf("e");
            if (index !== -1) {
              return value.substring(0, index) + "\\cdot10^{" + value.substring(index + 1) + "}";
            }
          }
          return value;
        case "Fraction":
          return this.value.toLatex();
        default:
          return value;
      }
    };
    return ConstantNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$c = "FunctionAssignmentNode";
  var dependencies$c = ["typed", "Node"];
  var createFunctionAssignmentNode = /* @__PURE__ */ factory(name$c, dependencies$c, (_ref) => {
    var {
      typed,
      Node
    } = _ref;
    function FunctionAssignmentNode(name2, params, expr) {
      if (!(this instanceof FunctionAssignmentNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (typeof name2 !== "string") throw new TypeError('String expected for parameter "name"');
      if (!Array.isArray(params)) throw new TypeError('Array containing strings or objects expected for parameter "params"');
      if (!isNode(expr)) throw new TypeError('Node expected for parameter "expr"');
      if (keywords.has(name2)) throw new Error('Illegal function name, "' + name2 + '" is a reserved keyword');
      this.name = name2;
      this.params = params.map(function(param) {
        return param && param.name || param;
      });
      this.types = params.map(function(param) {
        return param && param.type || "any";
      });
      this.expr = expr;
    }
    FunctionAssignmentNode.prototype = new Node();
    FunctionAssignmentNode.prototype.type = "FunctionAssignmentNode";
    FunctionAssignmentNode.prototype.isFunctionAssignmentNode = true;
    FunctionAssignmentNode.prototype._compile = function(math2, argNames) {
      var childArgNames = Object.create(argNames);
      forEach(this.params, function(param) {
        childArgNames[param] = true;
      });
      var evalExpr = this.expr._compile(math2, childArgNames);
      var name2 = this.name;
      var params = this.params;
      var signature = join(this.types, ",");
      var syntax = name2 + "(" + join(this.params, ", ") + ")";
      return function evalFunctionAssignmentNode(scope, args, context) {
        var signatures = {};
        signatures[signature] = function() {
          var childArgs = Object.create(args);
          for (var i = 0; i < params.length; i++) {
            childArgs[params[i]] = arguments[i];
          }
          return evalExpr(scope, childArgs, context);
        };
        var fn = typed(name2, signatures);
        fn.syntax = syntax;
        scope.set(name2, fn);
        return fn;
      };
    };
    FunctionAssignmentNode.prototype.forEach = function(callback) {
      callback(this.expr, "expr", this);
    };
    FunctionAssignmentNode.prototype.map = function(callback) {
      var expr = this._ifNode(callback(this.expr, "expr", this));
      return new FunctionAssignmentNode(this.name, this.params.slice(0), expr);
    };
    FunctionAssignmentNode.prototype.clone = function() {
      return new FunctionAssignmentNode(this.name, this.params.slice(0), this.expr);
    };
    function needParenthesis(node, parenthesis) {
      var precedence = getPrecedence(node, parenthesis);
      var exprPrecedence = getPrecedence(node.expr, parenthesis);
      return parenthesis === "all" || exprPrecedence !== null && exprPrecedence <= precedence;
    }
    FunctionAssignmentNode.prototype._toString = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var expr = this.expr.toString(options);
      if (needParenthesis(this, parenthesis)) {
        expr = "(" + expr + ")";
      }
      return this.name + "(" + this.params.join(", ") + ") = " + expr;
    };
    FunctionAssignmentNode.prototype.toJSON = function() {
      var types = this.types;
      return {
        mathjs: "FunctionAssignmentNode",
        name: this.name,
        params: this.params.map(function(param, index) {
          return {
            name: param,
            type: types[index]
          };
        }),
        expr: this.expr
      };
    };
    FunctionAssignmentNode.fromJSON = function(json) {
      return new FunctionAssignmentNode(json.name, json.params, json.expr);
    };
    FunctionAssignmentNode.prototype.toHTML = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var params = [];
      for (var i = 0; i < this.params.length; i++) {
        params.push('<span class="math-symbol math-parameter">' + escape(this.params[i]) + "</span>");
      }
      var expr = this.expr.toHTML(options);
      if (needParenthesis(this, parenthesis)) {
        expr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + expr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      return '<span class="math-function">' + escape(this.name) + '</span><span class="math-parenthesis math-round-parenthesis">(</span>' + params.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-round-parenthesis">)</span><span class="math-operator math-assignment-operator math-variable-assignment-operator math-binary-operator">=</span>' + expr;
    };
    FunctionAssignmentNode.prototype._toTex = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var expr = this.expr.toTex(options);
      if (needParenthesis(this, parenthesis)) {
        expr = "\\left(".concat(expr, "\\right)");
      }
      return "\\mathrm{" + this.name + "}\\left(" + this.params.map(toSymbol).join(",") + "\\right):=" + expr;
    };
    return FunctionAssignmentNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$b = "IndexNode";
  var dependencies$b = ["Node", "size"];
  var createIndexNode = /* @__PURE__ */ factory(name$b, dependencies$b, (_ref) => {
    var {
      Node,
      size
    } = _ref;
    function IndexNode(dimensions, dotNotation) {
      if (!(this instanceof IndexNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.dimensions = dimensions;
      this.dotNotation = dotNotation || false;
      if (!Array.isArray(dimensions) || !dimensions.every(isNode)) {
        throw new TypeError('Array containing Nodes expected for parameter "dimensions"');
      }
      if (this.dotNotation && !this.isObjectProperty()) {
        throw new Error("dotNotation only applicable for object properties");
      }
    }
    IndexNode.prototype = new Node();
    IndexNode.prototype.type = "IndexNode";
    IndexNode.prototype.isIndexNode = true;
    IndexNode.prototype._compile = function(math2, argNames) {
      var evalDimensions = map(this.dimensions, function(dimension, i) {
        var needsEnd = dimension.filter((node) => node.isSymbolNode && node.name === "end").length > 0;
        if (needsEnd) {
          var childArgNames = Object.create(argNames);
          childArgNames.end = true;
          var _evalDimension = dimension._compile(math2, childArgNames);
          return function evalDimension(scope, args, context) {
            if (!isMatrix(context) && !isArray(context) && !isString(context)) {
              throw new TypeError('Cannot resolve "end": context must be a Matrix, Array, or string but is ' + typeOf(context));
            }
            var s = size(context).valueOf();
            var childArgs = Object.create(args);
            childArgs.end = s[i];
            return _evalDimension(scope, childArgs, context);
          };
        } else {
          return dimension._compile(math2, argNames);
        }
      });
      var index = getSafeProperty(math2, "index");
      return function evalIndexNode(scope, args, context) {
        var dimensions = map(evalDimensions, function(evalDimension) {
          return evalDimension(scope, args, context);
        });
        return index(...dimensions);
      };
    };
    IndexNode.prototype.forEach = function(callback) {
      for (var i = 0; i < this.dimensions.length; i++) {
        callback(this.dimensions[i], "dimensions[" + i + "]", this);
      }
    };
    IndexNode.prototype.map = function(callback) {
      var dimensions = [];
      for (var i = 0; i < this.dimensions.length; i++) {
        dimensions[i] = this._ifNode(callback(this.dimensions[i], "dimensions[" + i + "]", this));
      }
      return new IndexNode(dimensions, this.dotNotation);
    };
    IndexNode.prototype.clone = function() {
      return new IndexNode(this.dimensions.slice(0), this.dotNotation);
    };
    IndexNode.prototype.isObjectProperty = function() {
      return this.dimensions.length === 1 && isConstantNode(this.dimensions[0]) && typeof this.dimensions[0].value === "string";
    };
    IndexNode.prototype.getObjectProperty = function() {
      return this.isObjectProperty() ? this.dimensions[0].value : null;
    };
    IndexNode.prototype._toString = function(options) {
      return this.dotNotation ? "." + this.getObjectProperty() : "[" + this.dimensions.join(", ") + "]";
    };
    IndexNode.prototype.toJSON = function() {
      return {
        mathjs: "IndexNode",
        dimensions: this.dimensions,
        dotNotation: this.dotNotation
      };
    };
    IndexNode.fromJSON = function(json) {
      return new IndexNode(json.dimensions, json.dotNotation);
    };
    IndexNode.prototype.toHTML = function(options) {
      var dimensions = [];
      for (var i = 0; i < this.dimensions.length; i++) {
        dimensions[i] = this.dimensions[i].toHTML();
      }
      if (this.dotNotation) {
        return '<span class="math-operator math-accessor-operator">.</span><span class="math-symbol math-property">' + escape(this.getObjectProperty()) + "</span>";
      } else {
        return '<span class="math-parenthesis math-square-parenthesis">[</span>' + dimensions.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-square-parenthesis">]</span>';
      }
    };
    IndexNode.prototype._toTex = function(options) {
      var dimensions = this.dimensions.map(function(range) {
        return range.toTex(options);
      });
      return this.dotNotation ? "." + this.getObjectProperty() : "_{" + dimensions.join(",") + "}";
    };
    return IndexNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$a = "ObjectNode";
  var dependencies$a = ["Node"];
  var createObjectNode = /* @__PURE__ */ factory(name$a, dependencies$a, (_ref) => {
    var {
      Node
    } = _ref;
    function ObjectNode(properties2) {
      if (!(this instanceof ObjectNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      this.properties = properties2 || {};
      if (properties2) {
        if (!(typeof properties2 === "object") || !Object.keys(properties2).every(function(key) {
          return isNode(properties2[key]);
        })) {
          throw new TypeError("Object containing Nodes expected");
        }
      }
    }
    ObjectNode.prototype = new Node();
    ObjectNode.prototype.type = "ObjectNode";
    ObjectNode.prototype.isObjectNode = true;
    ObjectNode.prototype._compile = function(math2, argNames) {
      var evalEntries = {};
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          var stringifiedKey = stringify(key);
          var parsedKey = JSON.parse(stringifiedKey);
          if (!isSafeProperty(this.properties, parsedKey)) {
            throw new Error('No access to property "' + parsedKey + '"');
          }
          evalEntries[parsedKey] = this.properties[key]._compile(math2, argNames);
        }
      }
      return function evalObjectNode(scope, args, context) {
        var obj = {};
        for (var _key in evalEntries) {
          if (hasOwnProperty(evalEntries, _key)) {
            obj[_key] = evalEntries[_key](scope, args, context);
          }
        }
        return obj;
      };
    };
    ObjectNode.prototype.forEach = function(callback) {
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          callback(this.properties[key], "properties[" + stringify(key) + "]", this);
        }
      }
    };
    ObjectNode.prototype.map = function(callback) {
      var properties2 = {};
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          properties2[key] = this._ifNode(callback(this.properties[key], "properties[" + stringify(key) + "]", this));
        }
      }
      return new ObjectNode(properties2);
    };
    ObjectNode.prototype.clone = function() {
      var properties2 = {};
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          properties2[key] = this.properties[key];
        }
      }
      return new ObjectNode(properties2);
    };
    ObjectNode.prototype._toString = function(options) {
      var entries = [];
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          entries.push(stringify(key) + ": " + this.properties[key].toString(options));
        }
      }
      return "{" + entries.join(", ") + "}";
    };
    ObjectNode.prototype.toJSON = function() {
      return {
        mathjs: "ObjectNode",
        properties: this.properties
      };
    };
    ObjectNode.fromJSON = function(json) {
      return new ObjectNode(json.properties);
    };
    ObjectNode.prototype.toHTML = function(options) {
      var entries = [];
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          entries.push('<span class="math-symbol math-property">' + escape(key) + '</span><span class="math-operator math-assignment-operator math-property-assignment-operator math-binary-operator">:</span>' + this.properties[key].toHTML(options));
        }
      }
      return '<span class="math-parenthesis math-curly-parenthesis">{</span>' + entries.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-curly-parenthesis">}</span>';
    };
    ObjectNode.prototype._toTex = function(options) {
      var entries = [];
      for (var key in this.properties) {
        if (hasOwnProperty(this.properties, key)) {
          entries.push("\\mathbf{" + key + ":} & " + this.properties[key].toTex(options) + "\\\\");
        }
      }
      return "\\left\\{\\begin{array}{ll}".concat(entries.join("\n"), "\\end{array}\\right\\}");
    };
    return ObjectNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$9 = "OperatorNode";
  var dependencies$9 = ["Node"];
  var createOperatorNode = /* @__PURE__ */ factory(name$9, dependencies$9, (_ref) => {
    var {
      Node
    } = _ref;
    function OperatorNode(op, fn, args, implicit, isPercentage) {
      if (!(this instanceof OperatorNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (typeof op !== "string") {
        throw new TypeError('string expected for parameter "op"');
      }
      if (typeof fn !== "string") {
        throw new TypeError('string expected for parameter "fn"');
      }
      if (!Array.isArray(args) || !args.every(isNode)) {
        throw new TypeError('Array containing Nodes expected for parameter "args"');
      }
      this.implicit = implicit === true;
      this.isPercentage = isPercentage === true;
      this.op = op;
      this.fn = fn;
      this.args = args || [];
    }
    OperatorNode.prototype = new Node();
    OperatorNode.prototype.type = "OperatorNode";
    OperatorNode.prototype.isOperatorNode = true;
    OperatorNode.prototype._compile = function(math2, argNames) {
      if (typeof this.fn !== "string" || !isSafeMethod(math2, this.fn)) {
        if (!math2[this.fn]) {
          throw new Error("Function " + this.fn + ' missing in provided namespace "math"');
        } else {
          throw new Error('No access to function "' + this.fn + '"');
        }
      }
      var fn = getSafeProperty(math2, this.fn);
      var evalArgs = map(this.args, function(arg) {
        return arg._compile(math2, argNames);
      });
      if (evalArgs.length === 1) {
        var evalArg0 = evalArgs[0];
        return function evalOperatorNode(scope, args, context) {
          return fn(evalArg0(scope, args, context));
        };
      } else if (evalArgs.length === 2) {
        var _evalArg = evalArgs[0];
        var evalArg1 = evalArgs[1];
        return function evalOperatorNode(scope, args, context) {
          return fn(_evalArg(scope, args, context), evalArg1(scope, args, context));
        };
      } else {
        return function evalOperatorNode(scope, args, context) {
          return fn.apply(null, map(evalArgs, function(evalArg) {
            return evalArg(scope, args, context);
          }));
        };
      }
    };
    OperatorNode.prototype.forEach = function(callback) {
      for (var i = 0; i < this.args.length; i++) {
        callback(this.args[i], "args[" + i + "]", this);
      }
    };
    OperatorNode.prototype.map = function(callback) {
      var args = [];
      for (var i = 0; i < this.args.length; i++) {
        args[i] = this._ifNode(callback(this.args[i], "args[" + i + "]", this));
      }
      return new OperatorNode(this.op, this.fn, args, this.implicit, this.isPercentage);
    };
    OperatorNode.prototype.clone = function() {
      return new OperatorNode(this.op, this.fn, this.args.slice(0), this.implicit, this.isPercentage);
    };
    OperatorNode.prototype.isUnary = function() {
      return this.args.length === 1;
    };
    OperatorNode.prototype.isBinary = function() {
      return this.args.length === 2;
    };
    function calculateNecessaryParentheses(root, parenthesis, implicit, args, latex) {
      var precedence = getPrecedence(root, parenthesis);
      var associativity = getAssociativity(root, parenthesis);
      if (parenthesis === "all" || args.length > 2 && root.getIdentifier() !== "OperatorNode:add" && root.getIdentifier() !== "OperatorNode:multiply") {
        return args.map(function(arg) {
          switch (arg.getContent().type) {
            // Nodes that don't need extra parentheses
            case "ArrayNode":
            case "ConstantNode":
            case "SymbolNode":
            case "ParenthesisNode":
              return false;
            default:
              return true;
          }
        });
      }
      var result;
      switch (args.length) {
        case 0:
          result = [];
          break;
        case 1:
          {
            var operandPrecedence = getPrecedence(args[0], parenthesis);
            if (latex && operandPrecedence !== null) {
              var operandIdentifier;
              var rootIdentifier;
              if (parenthesis === "keep") {
                operandIdentifier = args[0].getIdentifier();
                rootIdentifier = root.getIdentifier();
              } else {
                operandIdentifier = args[0].getContent().getIdentifier();
                rootIdentifier = root.getContent().getIdentifier();
              }
              if (properties[precedence][rootIdentifier].latexLeftParens === false) {
                result = [false];
                break;
              }
              if (properties[operandPrecedence][operandIdentifier].latexParens === false) {
                result = [false];
                break;
              }
            }
            if (operandPrecedence === null) {
              result = [false];
              break;
            }
            if (operandPrecedence <= precedence) {
              result = [true];
              break;
            }
            result = [false];
          }
          break;
        case 2:
          {
            var lhsParens;
            var lhsPrecedence = getPrecedence(args[0], parenthesis);
            var assocWithLhs = isAssociativeWith(root, args[0], parenthesis);
            if (lhsPrecedence === null) {
              lhsParens = false;
            } else if (lhsPrecedence === precedence && associativity === "right" && !assocWithLhs) {
              lhsParens = true;
            } else if (lhsPrecedence < precedence) {
              lhsParens = true;
            } else {
              lhsParens = false;
            }
            var rhsParens;
            var rhsPrecedence = getPrecedence(args[1], parenthesis);
            var assocWithRhs = isAssociativeWith(root, args[1], parenthesis);
            if (rhsPrecedence === null) {
              rhsParens = false;
            } else if (rhsPrecedence === precedence && associativity === "left" && !assocWithRhs) {
              rhsParens = true;
            } else if (rhsPrecedence < precedence) {
              rhsParens = true;
            } else {
              rhsParens = false;
            }
            if (latex) {
              var _rootIdentifier;
              var lhsIdentifier;
              var rhsIdentifier;
              if (parenthesis === "keep") {
                _rootIdentifier = root.getIdentifier();
                lhsIdentifier = root.args[0].getIdentifier();
                rhsIdentifier = root.args[1].getIdentifier();
              } else {
                _rootIdentifier = root.getContent().getIdentifier();
                lhsIdentifier = root.args[0].getContent().getIdentifier();
                rhsIdentifier = root.args[1].getContent().getIdentifier();
              }
              if (lhsPrecedence !== null) {
                if (properties[precedence][_rootIdentifier].latexLeftParens === false) {
                  lhsParens = false;
                }
                if (properties[lhsPrecedence][lhsIdentifier].latexParens === false) {
                  lhsParens = false;
                }
              }
              if (rhsPrecedence !== null) {
                if (properties[precedence][_rootIdentifier].latexRightParens === false) {
                  rhsParens = false;
                }
                if (properties[rhsPrecedence][rhsIdentifier].latexParens === false) {
                  rhsParens = false;
                }
              }
            }
            result = [lhsParens, rhsParens];
          }
          break;
        default:
          if (root.getIdentifier() === "OperatorNode:add" || root.getIdentifier() === "OperatorNode:multiply") {
            result = args.map(function(arg) {
              var argPrecedence = getPrecedence(arg, parenthesis);
              var assocWithArg = isAssociativeWith(root, arg, parenthesis);
              var argAssociativity = getAssociativity(arg, parenthesis);
              if (argPrecedence === null) {
                return false;
              } else if (precedence === argPrecedence && associativity === argAssociativity && !assocWithArg) {
                return true;
              } else if (argPrecedence < precedence) {
                return true;
              }
              return false;
            });
          }
          break;
      }
      if (args.length >= 2 && root.getIdentifier() === "OperatorNode:multiply" && root.implicit && parenthesis === "auto" && implicit === "hide") {
        result = args.map(function(arg, index) {
          var isParenthesisNode2 = arg.getIdentifier() === "ParenthesisNode";
          if (result[index] || isParenthesisNode2) {
            return true;
          }
          return false;
        });
      }
      return result;
    }
    OperatorNode.prototype._toString = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var implicit = options && options.implicit ? options.implicit : "hide";
      var args = this.args;
      var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, false);
      if (args.length === 1) {
        var assoc = getAssociativity(this, parenthesis);
        var operand = args[0].toString(options);
        if (parens[0]) {
          operand = "(" + operand + ")";
        }
        var opIsNamed = /[a-zA-Z]+/.test(this.op);
        if (assoc === "right") {
          return this.op + (opIsNamed ? " " : "") + operand;
        } else if (assoc === "left") {
          return operand + (opIsNamed ? " " : "") + this.op;
        }
        return operand + this.op;
      } else if (args.length === 2) {
        var lhs = args[0].toString(options);
        var rhs = args[1].toString(options);
        if (parens[0]) {
          lhs = "(" + lhs + ")";
        }
        if (parens[1]) {
          rhs = "(" + rhs + ")";
        }
        if (this.implicit && this.getIdentifier() === "OperatorNode:multiply" && implicit === "hide") {
          return lhs + " " + rhs;
        }
        return lhs + " " + this.op + " " + rhs;
      } else if (args.length > 2 && (this.getIdentifier() === "OperatorNode:add" || this.getIdentifier() === "OperatorNode:multiply")) {
        var stringifiedArgs = args.map(function(arg, index) {
          arg = arg.toString(options);
          if (parens[index]) {
            arg = "(" + arg + ")";
          }
          return arg;
        });
        if (this.implicit && this.getIdentifier() === "OperatorNode:multiply" && implicit === "hide") {
          return stringifiedArgs.join(" ");
        }
        return stringifiedArgs.join(" " + this.op + " ");
      } else {
        return this.fn + "(" + this.args.join(", ") + ")";
      }
    };
    OperatorNode.prototype.toJSON = function() {
      return {
        mathjs: "OperatorNode",
        op: this.op,
        fn: this.fn,
        args: this.args,
        implicit: this.implicit,
        isPercentage: this.isPercentage
      };
    };
    OperatorNode.fromJSON = function(json) {
      return new OperatorNode(json.op, json.fn, json.args, json.implicit, json.isPercentage);
    };
    OperatorNode.prototype.toHTML = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var implicit = options && options.implicit ? options.implicit : "hide";
      var args = this.args;
      var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, false);
      if (args.length === 1) {
        var assoc = getAssociativity(this, parenthesis);
        var operand = args[0].toHTML(options);
        if (parens[0]) {
          operand = '<span class="math-parenthesis math-round-parenthesis">(</span>' + operand + '<span class="math-parenthesis math-round-parenthesis">)</span>';
        }
        if (assoc === "right") {
          return '<span class="math-operator math-unary-operator math-lefthand-unary-operator">' + escape(this.op) + "</span>" + operand;
        } else {
          return operand + '<span class="math-operator math-unary-operator math-righthand-unary-operator">' + escape(this.op) + "</span>";
        }
      } else if (args.length === 2) {
        var lhs = args[0].toHTML(options);
        var rhs = args[1].toHTML(options);
        if (parens[0]) {
          lhs = '<span class="math-parenthesis math-round-parenthesis">(</span>' + lhs + '<span class="math-parenthesis math-round-parenthesis">)</span>';
        }
        if (parens[1]) {
          rhs = '<span class="math-parenthesis math-round-parenthesis">(</span>' + rhs + '<span class="math-parenthesis math-round-parenthesis">)</span>';
        }
        if (this.implicit && this.getIdentifier() === "OperatorNode:multiply" && implicit === "hide") {
          return lhs + '<span class="math-operator math-binary-operator math-implicit-binary-operator"></span>' + rhs;
        }
        return lhs + '<span class="math-operator math-binary-operator math-explicit-binary-operator">' + escape(this.op) + "</span>" + rhs;
      } else {
        var stringifiedArgs = args.map(function(arg, index) {
          arg = arg.toHTML(options);
          if (parens[index]) {
            arg = '<span class="math-parenthesis math-round-parenthesis">(</span>' + arg + '<span class="math-parenthesis math-round-parenthesis">)</span>';
          }
          return arg;
        });
        if (args.length > 2 && (this.getIdentifier() === "OperatorNode:add" || this.getIdentifier() === "OperatorNode:multiply")) {
          if (this.implicit && this.getIdentifier() === "OperatorNode:multiply" && implicit === "hide") {
            return stringifiedArgs.join('<span class="math-operator math-binary-operator math-implicit-binary-operator"></span>');
          }
          return stringifiedArgs.join('<span class="math-operator math-binary-operator math-explicit-binary-operator">' + escape(this.op) + "</span>");
        } else {
          return '<span class="math-function">' + escape(this.fn) + '</span><span class="math-paranthesis math-round-parenthesis">(</span>' + stringifiedArgs.join('<span class="math-separator">,</span>') + '<span class="math-paranthesis math-round-parenthesis">)</span>';
        }
      }
    };
    OperatorNode.prototype._toTex = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var implicit = options && options.implicit ? options.implicit : "hide";
      var args = this.args;
      var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, true);
      var op = latexOperators[this.fn];
      op = typeof op === "undefined" ? this.op : op;
      if (args.length === 1) {
        var assoc = getAssociativity(this, parenthesis);
        var operand = args[0].toTex(options);
        if (parens[0]) {
          operand = "\\left(".concat(operand, "\\right)");
        }
        if (assoc === "right") {
          return op + operand;
        } else if (assoc === "left") {
          return operand + op;
        }
        return operand + op;
      } else if (args.length === 2) {
        var lhs = args[0];
        var lhsTex = lhs.toTex(options);
        if (parens[0]) {
          lhsTex = "\\left(".concat(lhsTex, "\\right)");
        }
        var rhs = args[1];
        var rhsTex = rhs.toTex(options);
        if (parens[1]) {
          rhsTex = "\\left(".concat(rhsTex, "\\right)");
        }
        var lhsIdentifier;
        if (parenthesis === "keep") {
          lhsIdentifier = lhs.getIdentifier();
        } else {
          lhsIdentifier = lhs.getContent().getIdentifier();
        }
        switch (this.getIdentifier()) {
          case "OperatorNode:divide":
            return op + "{" + lhsTex + "}{" + rhsTex + "}";
          case "OperatorNode:pow":
            lhsTex = "{" + lhsTex + "}";
            rhsTex = "{" + rhsTex + "}";
            switch (lhsIdentifier) {
              case "ConditionalNode":
              //
              case "OperatorNode:divide":
                lhsTex = "\\left(".concat(lhsTex, "\\right)");
            }
            break;
          case "OperatorNode:multiply":
            if (this.implicit && implicit === "hide") {
              return lhsTex + "~" + rhsTex;
            }
        }
        return lhsTex + op + rhsTex;
      } else if (args.length > 2 && (this.getIdentifier() === "OperatorNode:add" || this.getIdentifier() === "OperatorNode:multiply")) {
        var texifiedArgs = args.map(function(arg, index) {
          arg = arg.toTex(options);
          if (parens[index]) {
            arg = "\\left(".concat(arg, "\\right)");
          }
          return arg;
        });
        if (this.getIdentifier() === "OperatorNode:multiply" && this.implicit) {
          return texifiedArgs.join("~");
        }
        return texifiedArgs.join(op);
      } else {
        return "\\mathrm{" + this.fn + "}\\left(" + args.map(function(arg) {
          return arg.toTex(options);
        }).join(",") + "\\right)";
      }
    };
    OperatorNode.prototype.getIdentifier = function() {
      return this.type + ":" + this.fn;
    };
    return OperatorNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$8 = "ParenthesisNode";
  var dependencies$8 = ["Node"];
  var createParenthesisNode = /* @__PURE__ */ factory(name$8, dependencies$8, (_ref) => {
    var {
      Node
    } = _ref;
    function ParenthesisNode(content) {
      if (!(this instanceof ParenthesisNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!isNode(content)) {
        throw new TypeError('Node expected for parameter "content"');
      }
      this.content = content;
    }
    ParenthesisNode.prototype = new Node();
    ParenthesisNode.prototype.type = "ParenthesisNode";
    ParenthesisNode.prototype.isParenthesisNode = true;
    ParenthesisNode.prototype._compile = function(math2, argNames) {
      return this.content._compile(math2, argNames);
    };
    ParenthesisNode.prototype.getContent = function() {
      return this.content.getContent();
    };
    ParenthesisNode.prototype.forEach = function(callback) {
      callback(this.content, "content", this);
    };
    ParenthesisNode.prototype.map = function(callback) {
      var content = callback(this.content, "content", this);
      return new ParenthesisNode(content);
    };
    ParenthesisNode.prototype.clone = function() {
      return new ParenthesisNode(this.content);
    };
    ParenthesisNode.prototype._toString = function(options) {
      if (!options || options && !options.parenthesis || options && options.parenthesis === "keep") {
        return "(" + this.content.toString(options) + ")";
      }
      return this.content.toString(options);
    };
    ParenthesisNode.prototype.toJSON = function() {
      return {
        mathjs: "ParenthesisNode",
        content: this.content
      };
    };
    ParenthesisNode.fromJSON = function(json) {
      return new ParenthesisNode(json.content);
    };
    ParenthesisNode.prototype.toHTML = function(options) {
      if (!options || options && !options.parenthesis || options && options.parenthesis === "keep") {
        return '<span class="math-parenthesis math-round-parenthesis">(</span>' + this.content.toHTML(options) + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      return this.content.toHTML(options);
    };
    ParenthesisNode.prototype._toTex = function(options) {
      if (!options || options && !options.parenthesis || options && options.parenthesis === "keep") {
        return "\\left(".concat(this.content.toTex(options), "\\right)");
      }
      return this.content.toTex(options);
    };
    return ParenthesisNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$7 = "RangeNode";
  var dependencies$7 = ["Node"];
  var createRangeNode = /* @__PURE__ */ factory(name$7, dependencies$7, (_ref) => {
    var {
      Node
    } = _ref;
    function RangeNode(start, end, step) {
      if (!(this instanceof RangeNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!isNode(start)) throw new TypeError("Node expected");
      if (!isNode(end)) throw new TypeError("Node expected");
      if (step && !isNode(step)) throw new TypeError("Node expected");
      if (arguments.length > 3) throw new Error("Too many arguments");
      this.start = start;
      this.end = end;
      this.step = step || null;
    }
    RangeNode.prototype = new Node();
    RangeNode.prototype.type = "RangeNode";
    RangeNode.prototype.isRangeNode = true;
    RangeNode.prototype.needsEnd = function() {
      var endSymbols = this.filter(function(node) {
        return isSymbolNode(node) && node.name === "end";
      });
      return endSymbols.length > 0;
    };
    RangeNode.prototype._compile = function(math2, argNames) {
      var range = math2.range;
      var evalStart = this.start._compile(math2, argNames);
      var evalEnd = this.end._compile(math2, argNames);
      if (this.step) {
        var evalStep = this.step._compile(math2, argNames);
        return function evalRangeNode(scope, args, context) {
          return range(evalStart(scope, args, context), evalEnd(scope, args, context), evalStep(scope, args, context));
        };
      } else {
        return function evalRangeNode(scope, args, context) {
          return range(evalStart(scope, args, context), evalEnd(scope, args, context));
        };
      }
    };
    RangeNode.prototype.forEach = function(callback) {
      callback(this.start, "start", this);
      callback(this.end, "end", this);
      if (this.step) {
        callback(this.step, "step", this);
      }
    };
    RangeNode.prototype.map = function(callback) {
      return new RangeNode(this._ifNode(callback(this.start, "start", this)), this._ifNode(callback(this.end, "end", this)), this.step && this._ifNode(callback(this.step, "step", this)));
    };
    RangeNode.prototype.clone = function() {
      return new RangeNode(this.start, this.end, this.step && this.step);
    };
    function calculateNecessaryParentheses(node, parenthesis) {
      var precedence = getPrecedence(node, parenthesis);
      var parens = {};
      var startPrecedence = getPrecedence(node.start, parenthesis);
      parens.start = startPrecedence !== null && startPrecedence <= precedence || parenthesis === "all";
      if (node.step) {
        var stepPrecedence = getPrecedence(node.step, parenthesis);
        parens.step = stepPrecedence !== null && stepPrecedence <= precedence || parenthesis === "all";
      }
      var endPrecedence = getPrecedence(node.end, parenthesis);
      parens.end = endPrecedence !== null && endPrecedence <= precedence || parenthesis === "all";
      return parens;
    }
    RangeNode.prototype._toString = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var parens = calculateNecessaryParentheses(this, parenthesis);
      var str;
      var start = this.start.toString(options);
      if (parens.start) {
        start = "(" + start + ")";
      }
      str = start;
      if (this.step) {
        var step = this.step.toString(options);
        if (parens.step) {
          step = "(" + step + ")";
        }
        str += ":" + step;
      }
      var end = this.end.toString(options);
      if (parens.end) {
        end = "(" + end + ")";
      }
      str += ":" + end;
      return str;
    };
    RangeNode.prototype.toJSON = function() {
      return {
        mathjs: "RangeNode",
        start: this.start,
        end: this.end,
        step: this.step
      };
    };
    RangeNode.fromJSON = function(json) {
      return new RangeNode(json.start, json.end, json.step);
    };
    RangeNode.prototype.toHTML = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var parens = calculateNecessaryParentheses(this, parenthesis);
      var str;
      var start = this.start.toHTML(options);
      if (parens.start) {
        start = '<span class="math-parenthesis math-round-parenthesis">(</span>' + start + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      str = start;
      if (this.step) {
        var step = this.step.toHTML(options);
        if (parens.step) {
          step = '<span class="math-parenthesis math-round-parenthesis">(</span>' + step + '<span class="math-parenthesis math-round-parenthesis">)</span>';
        }
        str += '<span class="math-operator math-range-operator">:</span>' + step;
      }
      var end = this.end.toHTML(options);
      if (parens.end) {
        end = '<span class="math-parenthesis math-round-parenthesis">(</span>' + end + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }
      str += '<span class="math-operator math-range-operator">:</span>' + end;
      return str;
    };
    RangeNode.prototype._toTex = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var parens = calculateNecessaryParentheses(this, parenthesis);
      var str = this.start.toTex(options);
      if (parens.start) {
        str = "\\left(".concat(str, "\\right)");
      }
      if (this.step) {
        var step = this.step.toTex(options);
        if (parens.step) {
          step = "\\left(".concat(step, "\\right)");
        }
        str += ":" + step;
      }
      var end = this.end.toTex(options);
      if (parens.end) {
        end = "\\left(".concat(end, "\\right)");
      }
      str += ":" + end;
      return str;
    };
    return RangeNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$6 = "RelationalNode";
  var dependencies$6 = ["Node"];
  var createRelationalNode = /* @__PURE__ */ factory(name$6, dependencies$6, (_ref) => {
    var {
      Node
    } = _ref;
    function RelationalNode(conditionals, params) {
      if (!(this instanceof RelationalNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (!Array.isArray(conditionals)) throw new TypeError("Parameter conditionals must be an array");
      if (!Array.isArray(params)) throw new TypeError("Parameter params must be an array");
      if (conditionals.length !== params.length - 1) throw new TypeError("Parameter params must contain exactly one more element than parameter conditionals");
      this.conditionals = conditionals;
      this.params = params;
    }
    RelationalNode.prototype = new Node();
    RelationalNode.prototype.type = "RelationalNode";
    RelationalNode.prototype.isRelationalNode = true;
    RelationalNode.prototype._compile = function(math2, argNames) {
      var self2 = this;
      var compiled = this.params.map((p) => p._compile(math2, argNames));
      return function evalRelationalNode(scope, args, context) {
        var evalLhs;
        var evalRhs = compiled[0](scope, args, context);
        for (var i = 0; i < self2.conditionals.length; i++) {
          evalLhs = evalRhs;
          evalRhs = compiled[i + 1](scope, args, context);
          var condFn = getSafeProperty(math2, self2.conditionals[i]);
          if (!condFn(evalLhs, evalRhs)) {
            return false;
          }
        }
        return true;
      };
    };
    RelationalNode.prototype.forEach = function(callback) {
      this.params.forEach((n, i) => callback(n, "params[" + i + "]", this), this);
    };
    RelationalNode.prototype.map = function(callback) {
      return new RelationalNode(this.conditionals.slice(), this.params.map((n, i) => this._ifNode(callback(n, "params[" + i + "]", this)), this));
    };
    RelationalNode.prototype.clone = function() {
      return new RelationalNode(this.conditionals, this.params);
    };
    RelationalNode.prototype._toString = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var precedence = getPrecedence(this, parenthesis);
      var paramStrings = this.params.map(function(p, index) {
        var paramPrecedence = getPrecedence(p, parenthesis);
        return parenthesis === "all" || paramPrecedence !== null && paramPrecedence <= precedence ? "(" + p.toString(options) + ")" : p.toString(options);
      });
      var operatorMap = {
        equal: "==",
        unequal: "!=",
        smaller: "<",
        larger: ">",
        smallerEq: "<=",
        largerEq: ">="
      };
      var ret = paramStrings[0];
      for (var i = 0; i < this.conditionals.length; i++) {
        ret += " " + operatorMap[this.conditionals[i]] + " " + paramStrings[i + 1];
      }
      return ret;
    };
    RelationalNode.prototype.toJSON = function() {
      return {
        mathjs: "RelationalNode",
        conditionals: this.conditionals,
        params: this.params
      };
    };
    RelationalNode.fromJSON = function(json) {
      return new RelationalNode(json.conditionals, json.params);
    };
    RelationalNode.prototype.toHTML = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var precedence = getPrecedence(this, parenthesis);
      var paramStrings = this.params.map(function(p, index) {
        var paramPrecedence = getPrecedence(p, parenthesis);
        return parenthesis === "all" || paramPrecedence !== null && paramPrecedence <= precedence ? '<span class="math-parenthesis math-round-parenthesis">(</span>' + p.toHTML(options) + '<span class="math-parenthesis math-round-parenthesis">)</span>' : p.toHTML(options);
      });
      var operatorMap = {
        equal: "==",
        unequal: "!=",
        smaller: "<",
        larger: ">",
        smallerEq: "<=",
        largerEq: ">="
      };
      var ret = paramStrings[0];
      for (var i = 0; i < this.conditionals.length; i++) {
        ret += '<span class="math-operator math-binary-operator math-explicit-binary-operator">' + escape(operatorMap[this.conditionals[i]]) + "</span>" + paramStrings[i + 1];
      }
      return ret;
    };
    RelationalNode.prototype._toTex = function(options) {
      var parenthesis = options && options.parenthesis ? options.parenthesis : "keep";
      var precedence = getPrecedence(this, parenthesis);
      var paramStrings = this.params.map(function(p, index) {
        var paramPrecedence = getPrecedence(p, parenthesis);
        return parenthesis === "all" || paramPrecedence !== null && paramPrecedence <= precedence ? "\\left(" + p.toTex(options) + "\right)" : p.toTex(options);
      });
      var ret = paramStrings[0];
      for (var i = 0; i < this.conditionals.length; i++) {
        ret += latexOperators[this.conditionals[i]] + paramStrings[i + 1];
      }
      return ret;
    };
    return RelationalNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$5 = "SymbolNode";
  var dependencies$5 = ["math", "?Unit", "Node"];
  var createSymbolNode = /* @__PURE__ */ factory(name$5, dependencies$5, (_ref) => {
    var {
      math: math2,
      Unit,
      Node
    } = _ref;
    function isValuelessUnit(name2) {
      return Unit ? Unit.isValuelessUnit(name2) : false;
    }
    function SymbolNode(name2) {
      if (!(this instanceof SymbolNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (typeof name2 !== "string") throw new TypeError('String expected for parameter "name"');
      this.name = name2;
    }
    SymbolNode.prototype = new Node();
    SymbolNode.prototype.type = "SymbolNode";
    SymbolNode.prototype.isSymbolNode = true;
    SymbolNode.prototype._compile = function(math3, argNames) {
      var name2 = this.name;
      if (argNames[name2] === true) {
        return function(scope, args, context) {
          return args[name2];
        };
      } else if (name2 in math3) {
        return function(scope, args, context) {
          return scope.has(name2) ? scope.get(name2) : getSafeProperty(math3, name2);
        };
      } else {
        var isUnit2 = isValuelessUnit(name2);
        return function(scope, args, context) {
          return scope.has(name2) ? scope.get(name2) : isUnit2 ? new Unit(null, name2) : SymbolNode.onUndefinedSymbol(name2);
        };
      }
    };
    SymbolNode.prototype.forEach = function(callback) {
    };
    SymbolNode.prototype.map = function(callback) {
      return this.clone();
    };
    SymbolNode.onUndefinedSymbol = function(name2) {
      throw new Error("Undefined symbol " + name2);
    };
    SymbolNode.prototype.clone = function() {
      return new SymbolNode(this.name);
    };
    SymbolNode.prototype._toString = function(options) {
      return this.name;
    };
    SymbolNode.prototype.toHTML = function(options) {
      var name2 = escape(this.name);
      if (name2 === "true" || name2 === "false") {
        return '<span class="math-symbol math-boolean">' + name2 + "</span>";
      } else if (name2 === "i") {
        return '<span class="math-symbol math-imaginary-symbol">' + name2 + "</span>";
      } else if (name2 === "Infinity") {
        return '<span class="math-symbol math-infinity-symbol">' + name2 + "</span>";
      } else if (name2 === "NaN") {
        return '<span class="math-symbol math-nan-symbol">' + name2 + "</span>";
      } else if (name2 === "null") {
        return '<span class="math-symbol math-null-symbol">' + name2 + "</span>";
      } else if (name2 === "undefined") {
        return '<span class="math-symbol math-undefined-symbol">' + name2 + "</span>";
      }
      return '<span class="math-symbol">' + name2 + "</span>";
    };
    SymbolNode.prototype.toJSON = function() {
      return {
        mathjs: "SymbolNode",
        name: this.name
      };
    };
    SymbolNode.fromJSON = function(json) {
      return new SymbolNode(json.name);
    };
    SymbolNode.prototype._toTex = function(options) {
      var isUnit2 = false;
      if (typeof math2[this.name] === "undefined" && isValuelessUnit(this.name)) {
        isUnit2 = true;
      }
      var symbol = toSymbol(this.name, isUnit2);
      if (symbol[0] === "\\") {
        return symbol;
      }
      return " " + symbol;
    };
    return SymbolNode;
  }, {
    isClass: true,
    isNode: true
  });
  function createSubScope(parentScope) {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }
    if (typeof parentScope.createSubScope === "function") {
      return assign(parentScope.createSubScope(), ...args);
    }
    return assign(createEmptyMap(), parentScope, ...args);
  }
  var name$4 = "FunctionNode";
  var dependencies$4 = ["math", "Node", "SymbolNode"];
  var createFunctionNode = /* @__PURE__ */ factory(name$4, dependencies$4, (_ref) => {
    var {
      math: math2,
      Node,
      SymbolNode
    } = _ref;
    function FunctionNode(fn, args) {
      if (!(this instanceof FunctionNode)) {
        throw new SyntaxError("Constructor must be called with the new operator");
      }
      if (typeof fn === "string") {
        fn = new SymbolNode(fn);
      }
      if (!isNode(fn)) throw new TypeError('Node expected as parameter "fn"');
      if (!Array.isArray(args) || !args.every(isNode)) {
        throw new TypeError('Array containing Nodes expected for parameter "args"');
      }
      this.fn = fn;
      this.args = args || [];
      Object.defineProperty(this, "name", {
        get: function() {
          return this.fn.name || "";
        }.bind(this),
        set: function set() {
          throw new Error("Cannot assign a new name, name is read-only");
        }
      });
    }
    FunctionNode.prototype = new Node();
    FunctionNode.prototype.type = "FunctionNode";
    FunctionNode.prototype.isFunctionNode = true;
    var strin = (entity) => format(entity, {
      truncate: 78
    });
    FunctionNode.prototype._compile = function(math3, argNames) {
      if (!(this instanceof FunctionNode)) {
        throw new TypeError("No valid FunctionNode");
      }
      var evalArgs = this.args.map((arg) => arg._compile(math3, argNames));
      if (isSymbolNode(this.fn)) {
        var _name = this.fn.name;
        if (!argNames[_name]) {
          var fn = _name in math3 ? getSafeProperty(math3, _name) : void 0;
          var isRaw = typeof fn === "function" && fn.rawArgs === true;
          var resolveFn = (scope) => {
            var value;
            if (scope.has(_name)) {
              value = scope.get(_name);
            } else if (_name in math3) {
              value = getSafeProperty(math3, _name);
            } else {
              return FunctionNode.onUndefinedFunction(_name);
            }
            if (typeof value === "function") {
              return value;
            }
            throw new TypeError("'".concat(_name, "' is not a function; its value is:\n  ").concat(strin(value)));
          };
          if (isRaw) {
            var rawArgs = this.args;
            return function evalFunctionNode(scope, args, context) {
              var fn2 = resolveFn(scope);
              return fn2(rawArgs, math3, createSubScope(scope, args), scope);
            };
          } else {
            switch (evalArgs.length) {
              case 0:
                return function evalFunctionNode(scope, args, context) {
                  var fn2 = resolveFn(scope);
                  return fn2();
                };
              case 1:
                return function evalFunctionNode(scope, args, context) {
                  var fn2 = resolveFn(scope);
                  var evalArg0 = evalArgs[0];
                  return fn2(evalArg0(scope, args, context));
                };
              case 2:
                return function evalFunctionNode(scope, args, context) {
                  var fn2 = resolveFn(scope);
                  var evalArg0 = evalArgs[0];
                  var evalArg1 = evalArgs[1];
                  return fn2(evalArg0(scope, args, context), evalArg1(scope, args, context));
                };
              default:
                return function evalFunctionNode(scope, args, context) {
                  var fn2 = resolveFn(scope);
                  var values2 = evalArgs.map((evalArg) => evalArg(scope, args, context));
                  return fn2(...values2);
                };
            }
          }
        } else {
          var _rawArgs = this.args;
          return function evalFunctionNode(scope, args, context) {
            var fn2 = args[_name];
            if (typeof fn2 !== "function") {
              throw new TypeError("Argument '".concat(_name, "' was not a function; received: ").concat(strin(fn2)));
            }
            if (fn2.rawArgs) {
              return fn2(_rawArgs, math3, createSubScope(scope, args), scope);
            } else {
              var values2 = evalArgs.map((evalArg) => evalArg(scope, args, context));
              return fn2.apply(fn2, values2);
            }
          };
        }
      } else if (isAccessorNode(this.fn) && isIndexNode(this.fn.index) && this.fn.index.isObjectProperty()) {
        var evalObject = this.fn.object._compile(math3, argNames);
        var prop = this.fn.index.getObjectProperty();
        var _rawArgs2 = this.args;
        return function evalFunctionNode(scope, args, context) {
          var object = evalObject(scope, args, context);
          validateSafeMethod(object, prop);
          var isRaw2 = object[prop] && object[prop].rawArgs;
          if (isRaw2) {
            return object[prop](_rawArgs2, math3, createSubScope(scope, args), scope);
          } else {
            var values2 = evalArgs.map((evalArg) => evalArg(scope, args, context));
            return object[prop].apply(object, values2);
          }
        };
      } else {
        var fnExpr = this.fn.toString();
        var evalFn = this.fn._compile(math3, argNames);
        var _rawArgs3 = this.args;
        return function evalFunctionNode(scope, args, context) {
          var fn2 = evalFn(scope, args, context);
          if (typeof fn2 !== "function") {
            throw new TypeError("Expression '".concat(fnExpr, "' did not evaluate to a function; value is:") + "\n  ".concat(strin(fn2)));
          }
          if (fn2.rawArgs) {
            return fn2(_rawArgs3, math3, createSubScope(scope, args), scope);
          } else {
            var values2 = evalArgs.map((evalArg) => evalArg(scope, args, context));
            return fn2.apply(fn2, values2);
          }
        };
      }
    };
    FunctionNode.prototype.forEach = function(callback) {
      callback(this.fn, "fn", this);
      for (var i = 0; i < this.args.length; i++) {
        callback(this.args[i], "args[" + i + "]", this);
      }
    };
    FunctionNode.prototype.map = function(callback) {
      var fn = this._ifNode(callback(this.fn, "fn", this));
      var args = [];
      for (var i = 0; i < this.args.length; i++) {
        args[i] = this._ifNode(callback(this.args[i], "args[" + i + "]", this));
      }
      return new FunctionNode(fn, args);
    };
    FunctionNode.prototype.clone = function() {
      return new FunctionNode(this.fn, this.args.slice(0));
    };
    FunctionNode.onUndefinedFunction = function(name2) {
      throw new Error("Undefined function " + name2);
    };
    var nodeToString = FunctionNode.prototype.toString;
    FunctionNode.prototype.toString = function(options) {
      var customString;
      var name2 = this.fn.toString(options);
      if (options && typeof options.handler === "object" && hasOwnProperty(options.handler, name2)) {
        customString = options.handler[name2](this, options);
      }
      if (typeof customString !== "undefined") {
        return customString;
      }
      return nodeToString.call(this, options);
    };
    FunctionNode.prototype._toString = function(options) {
      var args = this.args.map(function(arg) {
        return arg.toString(options);
      });
      var fn = isFunctionAssignmentNode(this.fn) ? "(" + this.fn.toString(options) + ")" : this.fn.toString(options);
      return fn + "(" + args.join(", ") + ")";
    };
    FunctionNode.prototype.toJSON = function() {
      return {
        mathjs: "FunctionNode",
        fn: this.fn,
        args: this.args
      };
    };
    FunctionNode.fromJSON = function(json) {
      return new FunctionNode(json.fn, json.args);
    };
    FunctionNode.prototype.toHTML = function(options) {
      var args = this.args.map(function(arg) {
        return arg.toHTML(options);
      });
      return '<span class="math-function">' + escape(this.fn) + '</span><span class="math-paranthesis math-round-parenthesis">(</span>' + args.join('<span class="math-separator">,</span>') + '<span class="math-paranthesis math-round-parenthesis">)</span>';
    };
    function expandTemplate(template, node, options) {
      var latex = "";
      var regex = /\$(?:\{([a-z_][a-z_0-9]*)(?:\[([0-9]+)\])?\}|\$)/gi;
      var inputPos = 0;
      var match;
      while ((match = regex.exec(template)) !== null) {
        latex += template.substring(inputPos, match.index);
        inputPos = match.index;
        if (match[0] === "$$") {
          latex += "$";
          inputPos++;
        } else {
          inputPos += match[0].length;
          var property = node[match[1]];
          if (!property) {
            throw new ReferenceError("Template: Property " + match[1] + " does not exist.");
          }
          if (match[2] === void 0) {
            switch (typeof property) {
              case "string":
                latex += property;
                break;
              case "object":
                if (isNode(property)) {
                  latex += property.toTex(options);
                } else if (Array.isArray(property)) {
                  latex += property.map(function(arg, index) {
                    if (isNode(arg)) {
                      return arg.toTex(options);
                    }
                    throw new TypeError("Template: " + match[1] + "[" + index + "] is not a Node.");
                  }).join(",");
                } else {
                  throw new TypeError("Template: " + match[1] + " has to be a Node, String or array of Nodes");
                }
                break;
              default:
                throw new TypeError("Template: " + match[1] + " has to be a Node, String or array of Nodes");
            }
          } else {
            if (isNode(property[match[2]] && property[match[2]])) {
              latex += property[match[2]].toTex(options);
            } else {
              throw new TypeError("Template: " + match[1] + "[" + match[2] + "] is not a Node.");
            }
          }
        }
      }
      latex += template.slice(inputPos);
      return latex;
    }
    var nodeToTex = FunctionNode.prototype.toTex;
    FunctionNode.prototype.toTex = function(options) {
      var customTex;
      if (options && typeof options.handler === "object" && hasOwnProperty(options.handler, this.name)) {
        customTex = options.handler[this.name](this, options);
      }
      if (typeof customTex !== "undefined") {
        return customTex;
      }
      return nodeToTex.call(this, options);
    };
    FunctionNode.prototype._toTex = function(options) {
      var args = this.args.map(function(arg) {
        return arg.toTex(options);
      });
      var latexConverter;
      if (latexFunctions[this.name]) {
        latexConverter = latexFunctions[this.name];
      }
      if (math2[this.name] && (typeof math2[this.name].toTex === "function" || typeof math2[this.name].toTex === "object" || typeof math2[this.name].toTex === "string")) {
        latexConverter = math2[this.name].toTex;
      }
      var customToTex;
      switch (typeof latexConverter) {
        case "function":
          customToTex = latexConverter(this, options);
          break;
        case "string":
          customToTex = expandTemplate(latexConverter, this, options);
          break;
        case "object":
          switch (typeof latexConverter[args.length]) {
            case "function":
              customToTex = latexConverter[args.length](this, options);
              break;
            case "string":
              customToTex = expandTemplate(latexConverter[args.length], this, options);
              break;
          }
      }
      if (typeof customToTex !== "undefined") {
        return customToTex;
      }
      return expandTemplate(defaultTemplate, this, options);
    };
    FunctionNode.prototype.getIdentifier = function() {
      return this.type + ":" + this.name;
    };
    return FunctionNode;
  }, {
    isClass: true,
    isNode: true
  });
  var name$3 = "parse";
  var dependencies$3 = ["typed", "numeric", "config", "AccessorNode", "ArrayNode", "AssignmentNode", "BlockNode", "ConditionalNode", "ConstantNode", "FunctionAssignmentNode", "FunctionNode", "IndexNode", "ObjectNode", "OperatorNode", "ParenthesisNode", "RangeNode", "RelationalNode", "SymbolNode"];
  var createParse = /* @__PURE__ */ factory(name$3, dependencies$3, (_ref) => {
    var {
      typed,
      numeric,
      config,
      AccessorNode,
      ArrayNode,
      AssignmentNode,
      BlockNode,
      ConditionalNode,
      ConstantNode,
      FunctionAssignmentNode,
      FunctionNode,
      IndexNode,
      ObjectNode,
      OperatorNode,
      ParenthesisNode,
      RangeNode,
      RelationalNode,
      SymbolNode
    } = _ref;
    var parse = typed(name$3, {
      string: function string(expression) {
        return parseStart(expression, {});
      },
      "Array | Matrix": function ArrayMatrix(expressions) {
        return parseMultiple(expressions, {});
      },
      "string, Object": function stringObject(expression, options) {
        var extraNodes = options.nodes !== void 0 ? options.nodes : {};
        return parseStart(expression, extraNodes);
      },
      "Array | Matrix, Object": parseMultiple
    });
    function parseMultiple(expressions) {
      var options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
      var extraNodes = options.nodes !== void 0 ? options.nodes : {};
      return deepMap(expressions, function(elem) {
        if (typeof elem !== "string") throw new TypeError("String expected");
        return parseStart(elem, extraNodes);
      });
    }
    var TOKENTYPE = {
      NULL: 0,
      DELIMITER: 1,
      NUMBER: 2,
      SYMBOL: 3,
      UNKNOWN: 4
    };
    var DELIMITERS = {
      ",": true,
      "(": true,
      ")": true,
      "[": true,
      "]": true,
      "{": true,
      "}": true,
      '"': true,
      "'": true,
      ";": true,
      "+": true,
      "-": true,
      "*": true,
      ".*": true,
      "/": true,
      "./": true,
      "%": true,
      "^": true,
      ".^": true,
      "~": true,
      "!": true,
      "&": true,
      "|": true,
      "^|": true,
      "=": true,
      ":": true,
      "?": true,
      "==": true,
      "!=": true,
      "<": true,
      ">": true,
      "<=": true,
      ">=": true,
      "<<": true,
      ">>": true,
      ">>>": true
    };
    var NAMED_DELIMITERS = {
      mod: true,
      to: true,
      in: true,
      and: true,
      xor: true,
      or: true,
      not: true
    };
    var CONSTANTS = {
      true: true,
      false: false,
      null: null,
      undefined: void 0
    };
    var NUMERIC_CONSTANTS = ["NaN", "Infinity"];
    function initialState() {
      return {
        extraNodes: {},
        // current extra nodes, must be careful not to mutate
        expression: "",
        // current expression
        comment: "",
        // last parsed comment
        index: 0,
        // current index in expr
        token: "",
        // current token
        tokenType: TOKENTYPE.NULL,
        // type of the token
        nestingLevel: 0,
        // level of nesting inside parameters, used to ignore newline characters
        conditionalLevel: null
        // when a conditional is being parsed, the level of the conditional is stored here
      };
    }
    function currentString(state, length) {
      return state.expression.substr(state.index, length);
    }
    function currentCharacter(state) {
      return currentString(state, 1);
    }
    function next(state) {
      state.index++;
    }
    function prevCharacter(state) {
      return state.expression.charAt(state.index - 1);
    }
    function nextCharacter(state) {
      return state.expression.charAt(state.index + 1);
    }
    function getToken(state) {
      state.tokenType = TOKENTYPE.NULL;
      state.token = "";
      state.comment = "";
      while (true) {
        if (currentCharacter(state) === "#") {
          while (currentCharacter(state) !== "\n" && currentCharacter(state) !== "") {
            state.comment += currentCharacter(state);
            next(state);
          }
        }
        if (parse.isWhitespace(currentCharacter(state), state.nestingLevel)) {
          next(state);
        } else {
          break;
        }
      }
      if (currentCharacter(state) === "") {
        state.tokenType = TOKENTYPE.DELIMITER;
        return;
      }
      if (currentCharacter(state) === "\n" && !state.nestingLevel) {
        state.tokenType = TOKENTYPE.DELIMITER;
        state.token = currentCharacter(state);
        next(state);
        return;
      }
      var c1 = currentCharacter(state);
      var c2 = currentString(state, 2);
      var c3 = currentString(state, 3);
      if (c3.length === 3 && DELIMITERS[c3]) {
        state.tokenType = TOKENTYPE.DELIMITER;
        state.token = c3;
        next(state);
        next(state);
        next(state);
        return;
      }
      if (c2.length === 2 && DELIMITERS[c2]) {
        state.tokenType = TOKENTYPE.DELIMITER;
        state.token = c2;
        next(state);
        next(state);
        return;
      }
      if (DELIMITERS[c1]) {
        state.tokenType = TOKENTYPE.DELIMITER;
        state.token = c1;
        next(state);
        return;
      }
      if (parse.isDigitDot(c1)) {
        state.tokenType = TOKENTYPE.NUMBER;
        var _c = currentString(state, 2);
        if (_c === "0b" || _c === "0o" || _c === "0x") {
          state.token += currentCharacter(state);
          next(state);
          state.token += currentCharacter(state);
          next(state);
          while (parse.isHexDigit(currentCharacter(state))) {
            state.token += currentCharacter(state);
            next(state);
          }
          if (currentCharacter(state) === ".") {
            state.token += ".";
            next(state);
            while (parse.isHexDigit(currentCharacter(state))) {
              state.token += currentCharacter(state);
              next(state);
            }
          } else if (currentCharacter(state) === "i") {
            state.token += "i";
            next(state);
            while (parse.isDigit(currentCharacter(state))) {
              state.token += currentCharacter(state);
              next(state);
            }
          }
          return;
        }
        if (currentCharacter(state) === ".") {
          state.token += currentCharacter(state);
          next(state);
          if (!parse.isDigit(currentCharacter(state))) {
            state.tokenType = TOKENTYPE.DELIMITER;
            return;
          }
        } else {
          while (parse.isDigit(currentCharacter(state))) {
            state.token += currentCharacter(state);
            next(state);
          }
          if (parse.isDecimalMark(currentCharacter(state), nextCharacter(state))) {
            state.token += currentCharacter(state);
            next(state);
          }
        }
        while (parse.isDigit(currentCharacter(state))) {
          state.token += currentCharacter(state);
          next(state);
        }
        if (currentCharacter(state) === "E" || currentCharacter(state) === "e") {
          if (parse.isDigit(nextCharacter(state)) || nextCharacter(state) === "-" || nextCharacter(state) === "+") {
            state.token += currentCharacter(state);
            next(state);
            if (currentCharacter(state) === "+" || currentCharacter(state) === "-") {
              state.token += currentCharacter(state);
              next(state);
            }
            if (!parse.isDigit(currentCharacter(state))) {
              throw createSyntaxError(state, 'Digit expected, got "' + currentCharacter(state) + '"');
            }
            while (parse.isDigit(currentCharacter(state))) {
              state.token += currentCharacter(state);
              next(state);
            }
            if (parse.isDecimalMark(currentCharacter(state), nextCharacter(state))) {
              throw createSyntaxError(state, 'Digit expected, got "' + currentCharacter(state) + '"');
            }
          } else if (nextCharacter(state) === ".") {
            next(state);
            throw createSyntaxError(state, 'Digit expected, got "' + currentCharacter(state) + '"');
          }
        }
        return;
      }
      if (parse.isAlpha(currentCharacter(state), prevCharacter(state), nextCharacter(state))) {
        while (parse.isAlpha(currentCharacter(state), prevCharacter(state), nextCharacter(state)) || parse.isDigit(currentCharacter(state))) {
          state.token += currentCharacter(state);
          next(state);
        }
        if (hasOwnProperty(NAMED_DELIMITERS, state.token)) {
          state.tokenType = TOKENTYPE.DELIMITER;
        } else {
          state.tokenType = TOKENTYPE.SYMBOL;
        }
        return;
      }
      state.tokenType = TOKENTYPE.UNKNOWN;
      while (currentCharacter(state) !== "") {
        state.token += currentCharacter(state);
        next(state);
      }
      throw createSyntaxError(state, 'Syntax error in part "' + state.token + '"');
    }
    function getTokenSkipNewline(state) {
      do {
        getToken(state);
      } while (state.token === "\n");
    }
    function openParams(state) {
      state.nestingLevel++;
    }
    function closeParams(state) {
      state.nestingLevel--;
    }
    parse.isAlpha = function isAlpha(c, cPrev, cNext) {
      return parse.isValidLatinOrGreek(c) || parse.isValidMathSymbol(c, cNext) || parse.isValidMathSymbol(cPrev, c);
    };
    parse.isValidLatinOrGreek = function isValidLatinOrGreek(c) {
      return /^[a-zA-Z_$\u00C0-\u02AF\u0370-\u03FF\u2100-\u214F]$/.test(c);
    };
    parse.isValidMathSymbol = function isValidMathSymbol(high, low) {
      return /^[\uD835]$/.test(high) && /^[\uDC00-\uDFFF]$/.test(low) && /^[^\uDC55\uDC9D\uDCA0\uDCA1\uDCA3\uDCA4\uDCA7\uDCA8\uDCAD\uDCBA\uDCBC\uDCC4\uDD06\uDD0B\uDD0C\uDD15\uDD1D\uDD3A\uDD3F\uDD45\uDD47-\uDD49\uDD51\uDEA6\uDEA7\uDFCC\uDFCD]$/.test(low);
    };
    parse.isWhitespace = function isWhitespace(c, nestingLevel) {
      return c === " " || c === "	" || c === "\n" && nestingLevel > 0;
    };
    parse.isDecimalMark = function isDecimalMark(c, cNext) {
      return c === "." && cNext !== "/" && cNext !== "*" && cNext !== "^";
    };
    parse.isDigitDot = function isDigitDot(c) {
      return c >= "0" && c <= "9" || c === ".";
    };
    parse.isDigit = function isDigit(c) {
      return c >= "0" && c <= "9";
    };
    parse.isHexDigit = function isHexDigit(c) {
      return c >= "0" && c <= "9" || c >= "a" && c <= "f" || c >= "A" && c <= "F";
    };
    function parseStart(expression, extraNodes) {
      var state = initialState();
      _extends(state, {
        expression,
        extraNodes
      });
      getToken(state);
      var node = parseBlock(state);
      if (state.token !== "") {
        if (state.tokenType === TOKENTYPE.DELIMITER) {
          throw createError(state, "Unexpected operator " + state.token);
        } else {
          throw createSyntaxError(state, 'Unexpected part "' + state.token + '"');
        }
      }
      return node;
    }
    function parseBlock(state) {
      var node;
      var blocks = [];
      var visible;
      if (state.token !== "" && state.token !== "\n" && state.token !== ";") {
        node = parseAssignment(state);
        node.comment = state.comment;
      }
      while (state.token === "\n" || state.token === ";") {
        if (blocks.length === 0 && node) {
          visible = state.token !== ";";
          blocks.push({
            node,
            visible
          });
        }
        getToken(state);
        if (state.token !== "\n" && state.token !== ";" && state.token !== "") {
          node = parseAssignment(state);
          node.comment = state.comment;
          visible = state.token !== ";";
          blocks.push({
            node,
            visible
          });
        }
      }
      if (blocks.length > 0) {
        return new BlockNode(blocks);
      } else {
        if (!node) {
          node = new ConstantNode(void 0);
          node.comment = state.comment;
        }
        return node;
      }
    }
    function parseAssignment(state) {
      var name2, args, value, valid;
      var node = parseConditional(state);
      if (state.token === "=") {
        if (isSymbolNode(node)) {
          name2 = node.name;
          getTokenSkipNewline(state);
          value = parseAssignment(state);
          return new AssignmentNode(new SymbolNode(name2), value);
        } else if (isAccessorNode(node)) {
          getTokenSkipNewline(state);
          value = parseAssignment(state);
          return new AssignmentNode(node.object, node.index, value);
        } else if (isFunctionNode(node) && isSymbolNode(node.fn)) {
          valid = true;
          args = [];
          name2 = node.name;
          node.args.forEach(function(arg, index) {
            if (isSymbolNode(arg)) {
              args[index] = arg.name;
            } else {
              valid = false;
            }
          });
          if (valid) {
            getTokenSkipNewline(state);
            value = parseAssignment(state);
            return new FunctionAssignmentNode(name2, args, value);
          }
        }
        throw createSyntaxError(state, "Invalid left hand side of assignment operator =");
      }
      return node;
    }
    function parseConditional(state) {
      var node = parseLogicalOr(state);
      while (state.token === "?") {
        var prev = state.conditionalLevel;
        state.conditionalLevel = state.nestingLevel;
        getTokenSkipNewline(state);
        var condition = node;
        var trueExpr = parseAssignment(state);
        if (state.token !== ":") throw createSyntaxError(state, "False part of conditional expression expected");
        state.conditionalLevel = null;
        getTokenSkipNewline(state);
        var falseExpr = parseAssignment(state);
        node = new ConditionalNode(condition, trueExpr, falseExpr);
        state.conditionalLevel = prev;
      }
      return node;
    }
    function parseLogicalOr(state) {
      var node = parseLogicalXor(state);
      while (state.token === "or") {
        getTokenSkipNewline(state);
        node = new OperatorNode("or", "or", [node, parseLogicalXor(state)]);
      }
      return node;
    }
    function parseLogicalXor(state) {
      var node = parseLogicalAnd(state);
      while (state.token === "xor") {
        getTokenSkipNewline(state);
        node = new OperatorNode("xor", "xor", [node, parseLogicalAnd(state)]);
      }
      return node;
    }
    function parseLogicalAnd(state) {
      var node = parseBitwiseOr(state);
      while (state.token === "and") {
        getTokenSkipNewline(state);
        node = new OperatorNode("and", "and", [node, parseBitwiseOr(state)]);
      }
      return node;
    }
    function parseBitwiseOr(state) {
      var node = parseBitwiseXor(state);
      while (state.token === "|") {
        getTokenSkipNewline(state);
        node = new OperatorNode("|", "bitOr", [node, parseBitwiseXor(state)]);
      }
      return node;
    }
    function parseBitwiseXor(state) {
      var node = parseBitwiseAnd(state);
      while (state.token === "^|") {
        getTokenSkipNewline(state);
        node = new OperatorNode("^|", "bitXor", [node, parseBitwiseAnd(state)]);
      }
      return node;
    }
    function parseBitwiseAnd(state) {
      var node = parseRelational(state);
      while (state.token === "&") {
        getTokenSkipNewline(state);
        node = new OperatorNode("&", "bitAnd", [node, parseRelational(state)]);
      }
      return node;
    }
    function parseRelational(state) {
      var params = [parseShift(state)];
      var conditionals = [];
      var operators = {
        "==": "equal",
        "!=": "unequal",
        "<": "smaller",
        ">": "larger",
        "<=": "smallerEq",
        ">=": "largerEq"
      };
      while (hasOwnProperty(operators, state.token)) {
        var cond = {
          name: state.token,
          fn: operators[state.token]
        };
        conditionals.push(cond);
        getTokenSkipNewline(state);
        params.push(parseShift(state));
      }
      if (params.length === 1) {
        return params[0];
      } else if (params.length === 2) {
        return new OperatorNode(conditionals[0].name, conditionals[0].fn, params);
      } else {
        return new RelationalNode(conditionals.map((c) => c.fn), params);
      }
    }
    function parseShift(state) {
      var node, name2, fn, params;
      node = parseConversion(state);
      var operators = {
        "<<": "leftShift",
        ">>": "rightArithShift",
        ">>>": "rightLogShift"
      };
      while (hasOwnProperty(operators, state.token)) {
        name2 = state.token;
        fn = operators[name2];
        getTokenSkipNewline(state);
        params = [node, parseConversion(state)];
        node = new OperatorNode(name2, fn, params);
      }
      return node;
    }
    function parseConversion(state) {
      var node, name2, fn, params;
      node = parseRange(state);
      var operators = {
        to: "to",
        in: "to"
        // alias of 'to'
      };
      while (hasOwnProperty(operators, state.token)) {
        name2 = state.token;
        fn = operators[name2];
        getTokenSkipNewline(state);
        if (name2 === "in" && state.token === "") {
          node = new OperatorNode("*", "multiply", [node, new SymbolNode("in")], true);
        } else {
          params = [node, parseRange(state)];
          node = new OperatorNode(name2, fn, params);
        }
      }
      return node;
    }
    function parseRange(state) {
      var node;
      var params = [];
      if (state.token === ":") {
        node = new ConstantNode(1);
      } else {
        node = parseAddSubtract(state);
      }
      if (state.token === ":" && state.conditionalLevel !== state.nestingLevel) {
        params.push(node);
        while (state.token === ":" && params.length < 3) {
          getTokenSkipNewline(state);
          if (state.token === ")" || state.token === "]" || state.token === "," || state.token === "") {
            params.push(new SymbolNode("end"));
          } else {
            params.push(parseAddSubtract(state));
          }
        }
        if (params.length === 3) {
          node = new RangeNode(params[0], params[2], params[1]);
        } else {
          node = new RangeNode(params[0], params[1]);
        }
      }
      return node;
    }
    function parseAddSubtract(state) {
      var node, name2, fn, params;
      node = parseMultiplyDivide(state);
      var operators = {
        "+": "add",
        "-": "subtract"
      };
      while (hasOwnProperty(operators, state.token)) {
        name2 = state.token;
        fn = operators[name2];
        getTokenSkipNewline(state);
        var rightNode = parseMultiplyDivide(state);
        if (rightNode.isPercentage) {
          params = [node, new OperatorNode("*", "multiply", [node, rightNode])];
        } else {
          params = [node, rightNode];
        }
        node = new OperatorNode(name2, fn, params);
      }
      return node;
    }
    function parseMultiplyDivide(state) {
      var node, last, name2, fn;
      node = parseImplicitMultiplication(state);
      last = node;
      var operators = {
        "*": "multiply",
        ".*": "dotMultiply",
        "/": "divide",
        "./": "dotDivide"
      };
      while (true) {
        if (hasOwnProperty(operators, state.token)) {
          name2 = state.token;
          fn = operators[name2];
          getTokenSkipNewline(state);
          last = parseImplicitMultiplication(state);
          node = new OperatorNode(name2, fn, [node, last]);
        } else {
          break;
        }
      }
      return node;
    }
    function parseImplicitMultiplication(state) {
      var node, last;
      node = parseRule2(state);
      last = node;
      while (true) {
        if (state.tokenType === TOKENTYPE.SYMBOL || state.token === "in" && isConstantNode(node) || state.tokenType === TOKENTYPE.NUMBER && !isConstantNode(last) && (!isOperatorNode(last) || last.op === "!") || state.token === "(") {
          last = parseRule2(state);
          node = new OperatorNode(
            "*",
            "multiply",
            [node, last],
            true
            /* implicit */
          );
        } else {
          break;
        }
      }
      return node;
    }
    function parseRule2(state) {
      var node = parsePercentage(state);
      var last = node;
      var tokenStates = [];
      while (true) {
        if (state.token === "/" && isConstantNode(last)) {
          tokenStates.push(_extends({}, state));
          getTokenSkipNewline(state);
          if (state.tokenType === TOKENTYPE.NUMBER) {
            tokenStates.push(_extends({}, state));
            getTokenSkipNewline(state);
            if (state.tokenType === TOKENTYPE.SYMBOL || state.token === "(") {
              _extends(state, tokenStates.pop());
              tokenStates.pop();
              last = parsePercentage(state);
              node = new OperatorNode("/", "divide", [node, last]);
            } else {
              tokenStates.pop();
              _extends(state, tokenStates.pop());
              break;
            }
          } else {
            _extends(state, tokenStates.pop());
            break;
          }
        } else {
          break;
        }
      }
      return node;
    }
    function parsePercentage(state) {
      var node, name2, fn, params;
      node = parseUnary(state);
      var operators = {
        "%": "mod",
        mod: "mod"
      };
      while (hasOwnProperty(operators, state.token)) {
        name2 = state.token;
        fn = operators[name2];
        getTokenSkipNewline(state);
        if (name2 === "%" && state.tokenType === TOKENTYPE.DELIMITER && state.token !== "(") {
          node = new OperatorNode("/", "divide", [node, new ConstantNode(100)], false, true);
        } else {
          params = [node, parseUnary(state)];
          node = new OperatorNode(name2, fn, params);
        }
      }
      return node;
    }
    function parseUnary(state) {
      var name2, params, fn;
      var operators = {
        "-": "unaryMinus",
        "+": "unaryPlus",
        "~": "bitNot",
        not: "not"
      };
      if (hasOwnProperty(operators, state.token)) {
        fn = operators[state.token];
        name2 = state.token;
        getTokenSkipNewline(state);
        params = [parseUnary(state)];
        return new OperatorNode(name2, fn, params);
      }
      return parsePow(state);
    }
    function parsePow(state) {
      var node, name2, fn, params;
      node = parseLeftHandOperators(state);
      if (state.token === "^" || state.token === ".^") {
        name2 = state.token;
        fn = name2 === "^" ? "pow" : "dotPow";
        getTokenSkipNewline(state);
        params = [node, parseUnary(state)];
        node = new OperatorNode(name2, fn, params);
      }
      return node;
    }
    function parseLeftHandOperators(state) {
      var node, name2, fn, params;
      node = parseCustomNodes(state);
      var operators = {
        "!": "factorial",
        "'": "ctranspose"
      };
      while (hasOwnProperty(operators, state.token)) {
        name2 = state.token;
        fn = operators[name2];
        getToken(state);
        params = [node];
        node = new OperatorNode(name2, fn, params);
        node = parseAccessors(state, node);
      }
      return node;
    }
    function parseCustomNodes(state) {
      var params = [];
      if (state.tokenType === TOKENTYPE.SYMBOL && hasOwnProperty(state.extraNodes, state.token)) {
        var CustomNode = state.extraNodes[state.token];
        getToken(state);
        if (state.token === "(") {
          params = [];
          openParams(state);
          getToken(state);
          if (state.token !== ")") {
            params.push(parseAssignment(state));
            while (state.token === ",") {
              getToken(state);
              params.push(parseAssignment(state));
            }
          }
          if (state.token !== ")") {
            throw createSyntaxError(state, "Parenthesis ) expected");
          }
          closeParams(state);
          getToken(state);
        }
        return new CustomNode(params);
      }
      return parseSymbol(state);
    }
    function parseSymbol(state) {
      var node, name2;
      if (state.tokenType === TOKENTYPE.SYMBOL || state.tokenType === TOKENTYPE.DELIMITER && state.token in NAMED_DELIMITERS) {
        name2 = state.token;
        getToken(state);
        if (hasOwnProperty(CONSTANTS, name2)) {
          node = new ConstantNode(CONSTANTS[name2]);
        } else if (NUMERIC_CONSTANTS.indexOf(name2) !== -1) {
          node = new ConstantNode(numeric(name2, "number"));
        } else {
          node = new SymbolNode(name2);
        }
        node = parseAccessors(state, node);
        return node;
      }
      return parseDoubleQuotesString(state);
    }
    function parseAccessors(state, node, types) {
      var params;
      while ((state.token === "(" || state.token === "[" || state.token === ".") && true) {
        params = [];
        if (state.token === "(") {
          if (isSymbolNode(node) || isAccessorNode(node)) {
            openParams(state);
            getToken(state);
            if (state.token !== ")") {
              params.push(parseAssignment(state));
              while (state.token === ",") {
                getToken(state);
                params.push(parseAssignment(state));
              }
            }
            if (state.token !== ")") {
              throw createSyntaxError(state, "Parenthesis ) expected");
            }
            closeParams(state);
            getToken(state);
            node = new FunctionNode(node, params);
          } else {
            return node;
          }
        } else if (state.token === "[") {
          openParams(state);
          getToken(state);
          if (state.token !== "]") {
            params.push(parseAssignment(state));
            while (state.token === ",") {
              getToken(state);
              params.push(parseAssignment(state));
            }
          }
          if (state.token !== "]") {
            throw createSyntaxError(state, "Parenthesis ] expected");
          }
          closeParams(state);
          getToken(state);
          node = new AccessorNode(node, new IndexNode(params));
        } else {
          getToken(state);
          if (state.tokenType !== TOKENTYPE.SYMBOL) {
            throw createSyntaxError(state, "Property name expected after dot");
          }
          params.push(new ConstantNode(state.token));
          getToken(state);
          var dotNotation = true;
          node = new AccessorNode(node, new IndexNode(params, dotNotation));
        }
      }
      return node;
    }
    function parseDoubleQuotesString(state) {
      var node, str;
      if (state.token === '"') {
        str = parseDoubleQuotesStringToken(state);
        node = new ConstantNode(str);
        node = parseAccessors(state, node);
        return node;
      }
      return parseSingleQuotesString(state);
    }
    function parseDoubleQuotesStringToken(state) {
      var str = "";
      while (currentCharacter(state) !== "" && currentCharacter(state) !== '"') {
        if (currentCharacter(state) === "\\") {
          str += currentCharacter(state);
          next(state);
        }
        str += currentCharacter(state);
        next(state);
      }
      getToken(state);
      if (state.token !== '"') {
        throw createSyntaxError(state, 'End of string " expected');
      }
      getToken(state);
      return JSON.parse('"' + str + '"');
    }
    function parseSingleQuotesString(state) {
      var node, str;
      if (state.token === "'") {
        str = parseSingleQuotesStringToken(state);
        node = new ConstantNode(str);
        node = parseAccessors(state, node);
        return node;
      }
      return parseMatrix(state);
    }
    function parseSingleQuotesStringToken(state) {
      var str = "";
      while (currentCharacter(state) !== "" && currentCharacter(state) !== "'") {
        if (currentCharacter(state) === "\\") {
          str += currentCharacter(state);
          next(state);
        }
        str += currentCharacter(state);
        next(state);
      }
      getToken(state);
      if (state.token !== "'") {
        throw createSyntaxError(state, "End of string ' expected");
      }
      getToken(state);
      return JSON.parse('"' + str + '"');
    }
    function parseMatrix(state) {
      var array, params, rows, cols;
      if (state.token === "[") {
        openParams(state);
        getToken(state);
        if (state.token !== "]") {
          var row = parseRow(state);
          if (state.token === ";") {
            rows = 1;
            params = [row];
            while (state.token === ";") {
              getToken(state);
              params[rows] = parseRow(state);
              rows++;
            }
            if (state.token !== "]") {
              throw createSyntaxError(state, "End of matrix ] expected");
            }
            closeParams(state);
            getToken(state);
            cols = params[0].items.length;
            for (var r = 1; r < rows; r++) {
              if (params[r].items.length !== cols) {
                throw createError(state, "Column dimensions mismatch (" + params[r].items.length + " !== " + cols + ")");
              }
            }
            array = new ArrayNode(params);
          } else {
            if (state.token !== "]") {
              throw createSyntaxError(state, "End of matrix ] expected");
            }
            closeParams(state);
            getToken(state);
            array = row;
          }
        } else {
          closeParams(state);
          getToken(state);
          array = new ArrayNode([]);
        }
        return parseAccessors(state, array);
      }
      return parseObject(state);
    }
    function parseRow(state) {
      var params = [parseAssignment(state)];
      var len = 1;
      while (state.token === ",") {
        getToken(state);
        params[len] = parseAssignment(state);
        len++;
      }
      return new ArrayNode(params);
    }
    function parseObject(state) {
      if (state.token === "{") {
        openParams(state);
        var key;
        var properties2 = {};
        do {
          getToken(state);
          if (state.token !== "}") {
            if (state.token === '"') {
              key = parseDoubleQuotesStringToken(state);
            } else if (state.token === "'") {
              key = parseSingleQuotesStringToken(state);
            } else if (state.tokenType === TOKENTYPE.SYMBOL || state.tokenType === TOKENTYPE.DELIMITER && state.token in NAMED_DELIMITERS) {
              key = state.token;
              getToken(state);
            } else {
              throw createSyntaxError(state, "Symbol or string expected as object key");
            }
            if (state.token !== ":") {
              throw createSyntaxError(state, "Colon : expected after object key");
            }
            getToken(state);
            properties2[key] = parseAssignment(state);
          }
        } while (state.token === ",");
        if (state.token !== "}") {
          throw createSyntaxError(state, "Comma , or bracket } expected after object value");
        }
        closeParams(state);
        getToken(state);
        var node = new ObjectNode(properties2);
        node = parseAccessors(state, node);
        return node;
      }
      return parseNumber(state);
    }
    function parseNumber(state) {
      var numberStr;
      if (state.tokenType === TOKENTYPE.NUMBER) {
        numberStr = state.token;
        getToken(state);
        return new ConstantNode(numeric(numberStr, config.number));
      }
      return parseParentheses(state);
    }
    function parseParentheses(state) {
      var node;
      if (state.token === "(") {
        openParams(state);
        getToken(state);
        node = parseAssignment(state);
        if (state.token !== ")") {
          throw createSyntaxError(state, "Parenthesis ) expected");
        }
        closeParams(state);
        getToken(state);
        node = new ParenthesisNode(node);
        node = parseAccessors(state, node);
        return node;
      }
      return parseEnd(state);
    }
    function parseEnd(state) {
      if (state.token === "") {
        throw createSyntaxError(state, "Unexpected end of expression");
      } else {
        throw createSyntaxError(state, "Value expected");
      }
    }
    function col(state) {
      return state.index - state.token.length + 1;
    }
    function createSyntaxError(state, message) {
      var c = col(state);
      var error = new SyntaxError(message + " (char " + c + ")");
      error.char = c;
      return error;
    }
    function createError(state, message) {
      var c = col(state);
      var error = new SyntaxError(message + " (char " + c + ")");
      error.char = c;
      return error;
    }
    return parse;
  });
  var name$2 = "evaluate";
  var dependencies$2 = ["typed", "parse"];
  var createEvaluate = /* @__PURE__ */ factory(name$2, dependencies$2, (_ref) => {
    var {
      typed,
      parse
    } = _ref;
    return typed(name$2, {
      string: function string(expr) {
        var scope = createEmptyMap();
        return parse(expr).compile().evaluate(scope);
      },
      "string, Map | Object": function stringMapObject(expr, scope) {
        return parse(expr).compile().evaluate(scope);
      },
      "Array | Matrix": function ArrayMatrix(expr) {
        var scope = createEmptyMap();
        return deepMap(expr, function(entry) {
          return parse(entry).compile().evaluate(scope);
        });
      },
      "Array | Matrix, Map | Object": function ArrayMatrixMapObject(expr, scope) {
        return deepMap(expr, function(entry) {
          return parse(entry).compile().evaluate(scope);
        });
      }
    });
  });
  var name$1 = "size";
  var dependencies$1 = ["typed", "config", "?matrix"];
  var createSize = /* @__PURE__ */ factory(name$1, dependencies$1, (_ref) => {
    var {
      typed,
      config,
      matrix
    } = _ref;
    return typed(name$1, {
      Matrix: function Matrix(x) {
        return x.create(x.size());
      },
      Array: arraySize,
      string: function string(x) {
        return config.matrix === "Array" ? [x.length] : matrix([x.length]);
      },
      "number | Complex | BigNumber | Unit | boolean | null": function numberComplexBigNumberUnitBooleanNull(x) {
        return config.matrix === "Array" ? [] : matrix ? matrix([]) : noMatrix();
      }
    });
  });
  var name = "numeric";
  var dependencies = ["number", "?bignumber", "?fraction"];
  var createNumeric = /* @__PURE__ */ factory(name, dependencies, (_ref) => {
    var {
      number: _number,
      bignumber,
      fraction
    } = _ref;
    var validInputTypes = {
      string: true,
      number: true,
      BigNumber: true,
      Fraction: true
    };
    var validOutputTypes = {
      number: (x) => _number(x),
      BigNumber: bignumber ? (x) => bignumber(x) : noBignumber,
      Fraction: fraction ? (x) => fraction(x) : noFraction
    };
    return function numeric(value, outputType) {
      var inputType = typeOf(value);
      if (!(inputType in validInputTypes)) {
        throw new TypeError("Cannot convert " + value + ' of type "' + inputType + '"; valid input types are ' + Object.keys(validInputTypes).join(", "));
      }
      if (!(outputType in validOutputTypes)) {
        throw new TypeError("Cannot convert " + value + ' to type "' + outputType + '"; valid output types are ' + Object.keys(validOutputTypes).join(", "));
      }
      if (outputType === inputType) {
        return value;
      } else {
        return validOutputTypes[outputType](value);
      }
    };
  });
  var createMatrix = /* @__PURE__ */ factory("matrix", [], () => noMatrix);
  var createSubset = /* @__PURE__ */ factory("subset", [], () => noSubset);
  createNumberFactory("combinations", combinationsNumber);
  createNumberFactory("gamma", gammaNumber);
  createNumberFactory("lgamma", lgammaNumber);
  function createNumberFactory(name2, fn) {
    return factory(name2, ["typed"], (_ref) => {
      var {
        typed
      } = _ref;
      return typed(fn);
    });
  }
  function ArgumentsError(fn, count, min, max) {
    if (!(this instanceof ArgumentsError)) {
      throw new SyntaxError("Constructor must be called with the new operator");
    }
    this.fn = fn;
    this.count = count;
    this.min = min;
    this.max = max;
    this.message = "Wrong number of arguments in function " + fn + " (" + count + " provided, " + min + (max !== void 0 && max !== null ? "-" + max : "") + " expected)";
    this.stack = new Error().stack;
  }
  ArgumentsError.prototype = new Error();
  ArgumentsError.prototype.constructor = Error;
  ArgumentsError.prototype.name = "ArgumentsError";
  ArgumentsError.prototype.isArgumentsError = true;
  var typedDependencies = {
    createTyped
  };
  var NodeDependencies = {
    createNode
  };
  var subsetDependencies = {
    createSubset
  };
  var AccessorNodeDependencies = {
    NodeDependencies,
    subsetDependencies,
    createAccessorNode
  };
  var ArrayNodeDependencies = {
    NodeDependencies,
    createArrayNode
  };
  var matrixDependencies = {
    createMatrix
  };
  var AssignmentNodeDependencies = {
    matrixDependencies,
    NodeDependencies,
    subsetDependencies,
    createAssignmentNode
  };
  var numberDependencies = {
    typedDependencies,
    createNumber
  };
  var ResultSetDependencies = {
    createResultSet
  };
  var BlockNodeDependencies = {
    NodeDependencies,
    ResultSetDependencies,
    createBlockNode
  };
  var ConditionalNodeDependencies = {
    NodeDependencies,
    createConditionalNode
  };
  var ConstantNodeDependencies = {
    NodeDependencies,
    createConstantNode
  };
  var FunctionAssignmentNodeDependencies = {
    NodeDependencies,
    typedDependencies,
    createFunctionAssignmentNode
  };
  var SymbolNodeDependencies = {
    NodeDependencies,
    createSymbolNode
  };
  var FunctionNodeDependencies = {
    NodeDependencies,
    SymbolNodeDependencies,
    createFunctionNode
  };
  var sizeDependencies = {
    matrixDependencies,
    typedDependencies,
    createSize
  };
  var IndexNodeDependencies = {
    NodeDependencies,
    sizeDependencies,
    createIndexNode
  };
  var ObjectNodeDependencies = {
    NodeDependencies,
    createObjectNode
  };
  var OperatorNodeDependencies = {
    NodeDependencies,
    createOperatorNode
  };
  var ParenthesisNodeDependencies = {
    NodeDependencies,
    createParenthesisNode
  };
  var RangeNodeDependencies = {
    NodeDependencies,
    createRangeNode
  };
  var RelationalNodeDependencies = {
    NodeDependencies,
    createRelationalNode
  };
  var numericDependencies = {
    numberDependencies,
    createNumeric
  };
  var parseDependencies = {
    AccessorNodeDependencies,
    ArrayNodeDependencies,
    AssignmentNodeDependencies,
    BlockNodeDependencies,
    ConditionalNodeDependencies,
    ConstantNodeDependencies,
    FunctionAssignmentNodeDependencies,
    FunctionNodeDependencies,
    IndexNodeDependencies,
    ObjectNodeDependencies,
    OperatorNodeDependencies,
    ParenthesisNodeDependencies,
    RangeNodeDependencies,
    RelationalNodeDependencies,
    SymbolNodeDependencies,
    numericDependencies,
    typedDependencies,
    createParse
  };
  var evaluateDependencies = {
    parseDependencies,
    typedDependencies,
    createEvaluate
  };
  var tinyEmitter = { exports: {} };
  var hasRequiredTinyEmitter;
  function requireTinyEmitter() {
    if (hasRequiredTinyEmitter) return tinyEmitter.exports;
    hasRequiredTinyEmitter = 1;
    function E() {
    }
    E.prototype = {
      on: function(name2, callback, ctx) {
        var e = this.e || (this.e = {});
        (e[name2] || (e[name2] = [])).push({
          fn: callback,
          ctx
        });
        return this;
      },
      once: function(name2, callback, ctx) {
        var self2 = this;
        function listener() {
          self2.off(name2, listener);
          callback.apply(ctx, arguments);
        }
        listener._ = callback;
        return this.on(name2, listener, ctx);
      },
      emit: function(name2) {
        var data = [].slice.call(arguments, 1);
        var evtArr = ((this.e || (this.e = {}))[name2] || []).slice();
        var i = 0;
        var len = evtArr.length;
        for (i; i < len; i++) {
          evtArr[i].fn.apply(evtArr[i].ctx, data);
        }
        return this;
      },
      off: function(name2, callback) {
        var e = this.e || (this.e = {});
        var evts = e[name2];
        var liveEvents = [];
        if (evts && callback) {
          for (var i = 0, len = evts.length; i < len; i++) {
            if (evts[i].fn !== callback && evts[i].fn._ !== callback)
              liveEvents.push(evts[i]);
          }
        }
        liveEvents.length ? e[name2] = liveEvents : delete e[name2];
        return this;
      }
    };
    tinyEmitter.exports = E;
    tinyEmitter.exports.TinyEmitter = E;
    return tinyEmitter.exports;
  }
  var tinyEmitterExports = requireTinyEmitter();
  const Emitter = /* @__PURE__ */ getDefaultExportFromCjs(tinyEmitterExports);
  function mixin(obj) {
    var emitter = new Emitter();
    obj.on = emitter.on.bind(emitter);
    obj.off = emitter.off.bind(emitter);
    obj.once = emitter.once.bind(emitter);
    obj.emit = emitter.emit.bind(emitter);
    return obj;
  }
  function importFactory(typed, load, math2, importedFactories) {
    function mathImport(functions, options) {
      var num = arguments.length;
      if (num !== 1 && num !== 2) {
        throw new ArgumentsError("import", num, 1, 2);
      }
      if (!options) {
        options = {};
      }
      function flattenImports(flatValues2, value2, name3) {
        if (Array.isArray(value2)) {
          value2.forEach((item) => flattenImports(flatValues2, item));
        } else if (typeof value2 === "object") {
          for (var _name in value2) {
            if (hasOwnProperty(value2, _name)) {
              flattenImports(flatValues2, value2[_name], _name);
            }
          }
        } else if (isFactory(value2) || name3 !== void 0) {
          var flatName = isFactory(value2) ? isTransformFunctionFactory(value2) ? value2.fn + ".transform" : value2.fn : name3;
          if (hasOwnProperty(flatValues2, flatName) && flatValues2[flatName] !== value2 && !options.silent) {
            throw new Error('Cannot import "' + flatName + '" twice');
          }
          flatValues2[flatName] = value2;
        } else {
          if (!options.silent) {
            throw new TypeError("Factory, Object, or Array expected");
          }
        }
      }
      var flatValues = {};
      flattenImports(flatValues, functions);
      for (var name2 in flatValues) {
        if (hasOwnProperty(flatValues, name2)) {
          var value = flatValues[name2];
          if (isFactory(value)) {
            _importFactory(value, options);
          } else if (isSupportedType(value)) {
            _import(name2, value, options);
          } else {
            if (!options.silent) {
              throw new TypeError("Factory, Object, or Array expected");
            }
          }
        }
      }
    }
    function _import(name2, value, options) {
      if (options.wrap && typeof value === "function") {
        value = _wrap(value);
      }
      if (hasTypedFunctionSignature(value)) {
        value = typed(name2, {
          [value.signature]: value
        });
      }
      if (isTypedFunction(math2[name2]) && isTypedFunction(value)) {
        if (options.override) {
          value = typed(name2, value.signatures);
        } else {
          value = typed(math2[name2], value);
        }
        math2[name2] = value;
        delete importedFactories[name2];
        _importTransform(name2, value);
        math2.emit("import", name2, function resolver() {
          return value;
        });
        return;
      }
      if (math2[name2] === void 0 || options.override) {
        math2[name2] = value;
        delete importedFactories[name2];
        _importTransform(name2, value);
        math2.emit("import", name2, function resolver() {
          return value;
        });
        return;
      }
      if (!options.silent) {
        throw new Error('Cannot import "' + name2 + '": already exists');
      }
    }
    function _importTransform(name2, value) {
      if (value && typeof value.transform === "function") {
        math2.expression.transform[name2] = value.transform;
        if (allowedInExpressions(name2)) {
          math2.expression.mathWithTransform[name2] = value.transform;
        }
      } else {
        delete math2.expression.transform[name2];
        if (allowedInExpressions(name2)) {
          math2.expression.mathWithTransform[name2] = value;
        }
      }
    }
    function _deleteTransform(name2) {
      delete math2.expression.transform[name2];
      if (allowedInExpressions(name2)) {
        math2.expression.mathWithTransform[name2] = math2[name2];
      } else {
        delete math2.expression.mathWithTransform[name2];
      }
    }
    function _wrap(fn) {
      var wrapper = function wrapper2() {
        var args = [];
        for (var i = 0, len = arguments.length; i < len; i++) {
          var arg = arguments[i];
          args[i] = arg && arg.valueOf();
        }
        return fn.apply(math2, args);
      };
      if (fn.transform) {
        wrapper.transform = fn.transform;
      }
      return wrapper;
    }
    function _importFactory(factory2, options) {
      var name2 = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : factory2.fn;
      if (contains(name2, ".")) {
        throw new Error("Factory name should not contain a nested path. Name: " + JSON.stringify(name2));
      }
      var namespace = isTransformFunctionFactory(factory2) ? math2.expression.transform : math2;
      var existingTransform = name2 in math2.expression.transform;
      var existing = hasOwnProperty(namespace, name2) ? namespace[name2] : void 0;
      var resolver = function resolver2() {
        var dependencies2 = {};
        factory2.dependencies.map(stripOptionalNotation).forEach((dependency) => {
          if (contains(dependency, ".")) {
            throw new Error("Factory dependency should not contain a nested path. Name: " + JSON.stringify(dependency));
          }
          if (dependency === "math") {
            dependencies2.math = math2;
          } else if (dependency === "mathWithTransform") {
            dependencies2.mathWithTransform = math2.expression.mathWithTransform;
          } else if (dependency === "classes") {
            dependencies2.classes = math2;
          } else {
            dependencies2[dependency] = math2[dependency];
          }
        });
        var instance = /* @__PURE__ */ factory2(dependencies2);
        if (instance && typeof instance.transform === "function") {
          throw new Error('Transforms cannot be attached to factory functions. Please create a separate function for it with exports.path="expression.transform"');
        }
        if (existing === void 0 || options.override) {
          return instance;
        }
        if (isTypedFunction(existing) && isTypedFunction(instance)) {
          return typed(existing, instance);
        }
        if (options.silent) {
          return existing;
        } else {
          throw new Error('Cannot import "' + name2 + '": already exists');
        }
      };
      if (!factory2.meta || factory2.meta.lazy !== false) {
        lazy(namespace, name2, resolver);
        if (existing && existingTransform) {
          _deleteTransform(name2);
        } else {
          if (isTransformFunctionFactory(factory2) || factoryAllowedInExpressions(factory2)) {
            lazy(math2.expression.mathWithTransform, name2, () => namespace[name2]);
          }
        }
      } else {
        namespace[name2] = resolver();
        if (existing && existingTransform) {
          _deleteTransform(name2);
        } else {
          if (isTransformFunctionFactory(factory2) || factoryAllowedInExpressions(factory2)) {
            lazy(math2.expression.mathWithTransform, name2, () => namespace[name2]);
          }
        }
      }
      importedFactories[name2] = factory2;
      math2.emit("import", name2, resolver);
    }
    function isSupportedType(object) {
      return typeof object === "function" || typeof object === "number" || typeof object === "string" || typeof object === "boolean" || object === null || isUnit(object) || isComplex(object) || isBigNumber(object) || isFraction(object) || isMatrix(object) || Array.isArray(object);
    }
    function isTypedFunction(fn) {
      return typeof fn === "function" && typeof fn.signatures === "object";
    }
    function hasTypedFunctionSignature(fn) {
      return typeof fn === "function" && typeof fn.signature === "string";
    }
    function allowedInExpressions(name2) {
      return !hasOwnProperty(unsafe, name2);
    }
    function factoryAllowedInExpressions(factory2) {
      return factory2.fn.indexOf(".") === -1 && // FIXME: make checking on path redundant, check on meta data instead
      !hasOwnProperty(unsafe, factory2.fn) && (!factory2.meta || !factory2.meta.isClass);
    }
    function isTransformFunctionFactory(factory2) {
      return factory2 !== void 0 && factory2.meta !== void 0 && factory2.meta.isTransformFunction === true || false;
    }
    var unsafe = {
      expression: true,
      type: true,
      docs: true,
      error: true,
      json: true,
      chain: true
      // chain method not supported. Note that there is a unit chain too.
    };
    return mathImport;
  }
  function create(factories, config) {
    var configInternal = _extends({}, DEFAULT_CONFIG, config);
    if (typeof Object.create !== "function") {
      throw new Error("ES5 not supported by this JavaScript engine. Please load the es5-shim and es5-sham library for compatibility.");
    }
    var math2 = mixin({
      // only here for backward compatibility for legacy factory functions
      isNumber,
      isComplex,
      isBigNumber,
      isFraction,
      isUnit,
      isString,
      isArray,
      isMatrix,
      isCollection,
      isDenseMatrix,
      isSparseMatrix,
      isRange,
      isIndex,
      isBoolean,
      isResultSet,
      isHelp,
      isFunction,
      isDate,
      isRegExp,
      isObject,
      isNull,
      isUndefined,
      isAccessorNode,
      isArrayNode,
      isAssignmentNode,
      isBlockNode,
      isConditionalNode,
      isConstantNode,
      isFunctionAssignmentNode,
      isFunctionNode,
      isIndexNode,
      isNode,
      isObjectNode,
      isOperatorNode,
      isParenthesisNode,
      isRangeNode,
      isSymbolNode,
      isChain
    });
    math2.config = configFactory(configInternal, math2.emit);
    math2.expression = {
      transform: {},
      mathWithTransform: {
        config: math2.config
      }
    };
    var legacyFactories = [];
    var legacyInstances = [];
    function load(factory2) {
      if (isFactory(factory2)) {
        return factory2(math2);
      }
      var firstProperty = factory2[Object.keys(factory2)[0]];
      if (isFactory(firstProperty)) {
        return firstProperty(math2);
      }
      if (!isLegacyFactory(factory2)) {
        console.warn("Factory object with properties `type`, `name`, and `factory` expected", factory2);
        throw new Error("Factory object with properties `type`, `name`, and `factory` expected");
      }
      var index = legacyFactories.indexOf(factory2);
      var instance;
      if (index === -1) {
        if (factory2.math === true) {
          instance = factory2.factory(math2.type, configInternal, load, math2.typed, math2);
        } else {
          instance = factory2.factory(math2.type, configInternal, load, math2.typed);
        }
        legacyFactories.push(factory2);
        legacyInstances.push(instance);
      } else {
        instance = legacyInstances[index];
      }
      return instance;
    }
    var importedFactories = {};
    function lazyTyped() {
      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }
      return math2.typed.apply(math2.typed, args);
    }
    var internalImport = importFactory(lazyTyped, load, math2, importedFactories);
    math2.import = internalImport;
    math2.on("config", () => {
      values(importedFactories).forEach((factory2) => {
        if (factory2 && factory2.meta && factory2.meta.recreateOnConfigChange) {
          internalImport(factory2, {
            override: true
          });
        }
      });
    });
    math2.create = create.bind(null, factories);
    math2.factory = factory;
    math2.import(values(deepFlatten(factories)));
    math2.ArgumentsError = ArgumentsError;
    math2.DimensionError = DimensionError;
    math2.IndexError = IndexError;
    return math2;
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
  */
  const math = create({
    evaluateDependencies
  });
  math.import(
    {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      add: (a, b) => a + b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      subtract: (a, b) => a - b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      multiply: (a, b) => a * b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      divide: (a, b) => a / b,
      // eslint-disable-next-line
      equal: (a, b) => a == b,
      // eslint-disable-next-line
      unequal: (a, b) => a != b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      not: (a) => !a,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      and: (a, b) => a && b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      or: (a, b) => a || b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      largerEq: (a, b) => a >= b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      larger: (a, b) => a > b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      smallerEq: (a, b) => a <= b,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      smaller: (a, b) => a < b
    },
    {
      override: true
    }
  );
  const _sfc_main$t = vue.defineComponent({
    props: {
      modelValue: [Boolean, Number, String],
      modelModifiers: Object,
      uiControlAttributes: Object,
      name: String,
      title: String,
      id: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    methods: {
      onChange(event) {
        var _a2;
        const newValue = event.target.checked;
        if (this.modelValue !== newValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", newValue);
            return;
          }
          const emitEventData = {
            value: newValue,
            abort() {
              event.target.checked = !newValue;
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    },
    computed: {
      isChecked() {
        return !!this.modelValue && this.modelValue !== "0";
      }
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$p = { class: "checkbox" };
  const _hoisted_2$n = ["checked", "id", "name"];
  const _hoisted_3$c = ["innerHTML"];
  function _sfc_render$s(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$p, [
      vue.createElementVNode("label", null, [
        vue.createElementVNode("input", vue.mergeProps({
          onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event))
        }, _ctx.uiControlAttributes, {
          value: 1,
          checked: _ctx.isChecked,
          type: "checkbox",
          id: _ctx.id,
          name: _ctx.name
        }), null, 16, _hoisted_2$n),
        vue.createElementVNode("span", {
          innerHTML: _ctx.$sanitize(_ctx.title)
        }, null, 8, _hoisted_3$c)
      ])
    ]);
  }
  const FieldCheckbox = /* @__PURE__ */ _export_sfc(_sfc_main$t, [["render", _sfc_render$s]]);
  function getCheckboxStates(availableOptions, modelValue) {
    return (availableOptions || []).map((o) => modelValue && modelValue.indexOf(o.key) !== -1);
  }
  const _sfc_main$s = vue.defineComponent({
    props: {
      modelValue: Array,
      modelModifiers: Object,
      name: String,
      title: String,
      id: String,
      availableOptions: Array,
      uiControlAttributes: Object,
      type: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    computed: {
      checkboxStates() {
        return getCheckboxStates(this.availableOptions, this.modelValue);
      }
    },
    mounted() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
    methods: {
      onChange(changedIndex) {
        var _a2;
        const checkboxStates = [...this.checkboxStates];
        checkboxStates[changedIndex] = !checkboxStates[changedIndex];
        const availableOptions = this.availableOptions || {};
        const newValue = [];
        Object.values(availableOptions).forEach((option, index) => {
          if (checkboxStates[index]) {
            newValue.push(option.key);
          }
        });
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", newValue);
          return;
        }
        const emitEventData = {
          value: newValue,
          abort: () => {
            const item = this.$refs.root.querySelectorAll("input").item(changedIndex);
            item.checked = !item.checked;
          }
        };
        this.$emit("update:modelValue", emitEventData);
      }
    }
  });
  const _hoisted_1$o = { ref: "root" };
  const _hoisted_2$m = ["value", "checked", "onChange", "id", "name"];
  function _sfc_render$r(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$o, [
      vue.withDirectives(vue.createElementVNode("label", { class: "fieldRadioTitle" }, vue.toDisplayString(_ctx.title), 513), [
        [vue.vShow, _ctx.title]
      ]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.availableOptions, (checkboxModel, $index) => {
        return vue.openBlock(), vue.createElementBlock("p", {
          key: $index,
          class: "checkbox"
        }, [
          vue.createElementVNode("label", null, [
            vue.createElementVNode("input", vue.mergeProps({
              value: checkboxModel.key,
              checked: !!_ctx.checkboxStates[$index],
              onChange: ($event) => _ctx.onChange($index),
              ref_for: true
            }, _ctx.uiControlAttributes, {
              type: "checkbox",
              id: `${_ctx.id}${checkboxModel.key}`,
              name: checkboxModel.name
            }), null, 16, _hoisted_2$m),
            vue.createElementVNode("span", null, vue.toDisplayString(checkboxModel.value), 1),
            vue.withDirectives(vue.createElementVNode("span", { class: "form-description" }, vue.toDisplayString(checkboxModel.description), 513), [
              [vue.vShow, checkboxModel.description]
            ])
          ])
        ]);
      }), 128))
    ], 512);
  }
  const FieldCheckboxArray = /* @__PURE__ */ _export_sfc(_sfc_main$s, [["render", _sfc_render$r]]);
  function getAvailableOptions$1(availableValues) {
    const flatValues = [];
    if (!availableValues) {
      return flatValues;
    }
    const groups = {};
    Object.values(availableValues).forEach((uncastedValue) => {
      const value = uncastedValue;
      const group = value.group || "";
      if (!(group in groups) || !groups[group]) {
        groups[group] = { values: [], group };
      }
      const formatted = { key: value.key, value: value.value };
      if ("tooltip" in value && value.tooltip) {
        formatted.tooltip = value.tooltip;
      }
      groups[group].values.push(formatted);
    });
    Object.values(groups).forEach((group) => {
      if (group.values.length) {
        flatValues.push(group);
      }
    });
    return flatValues;
  }
  const _sfc_main$r = vue.defineComponent({
    props: {
      modelValue: [Number, String],
      modelModifiers: Object,
      availableOptions: Array,
      title: String,
      searchOnGroup: {
        type: Boolean,
        default: false
      }
    },
    directives: {
      FocusAnywhereButHere: CoreHome.FocusAnywhereButHere,
      FocusIf: CoreHome.FocusIf
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    data() {
      return {
        showSelect: false,
        searchTerm: "",
        showCategory: ""
      };
    },
    computed: {
      searchTermLowercase() {
        return this.searchTerm.toLowerCase();
      },
      searchTermNormalized() {
        return this.normalize(this.searchTerm);
      },
      modelValueText() {
        if (this.title) {
          return this.title;
        }
        const key = this.modelValue;
        const availableOptions = this.availableOptions || [];
        let keyItem;
        availableOptions.some((option) => {
          keyItem = option.values.find((item) => item.key === key);
          return keyItem;
        });
        if (keyItem) {
          return keyItem.value ? `${keyItem.value}` : "";
        }
        return key ? `${key}` : "";
      }
    },
    methods: {
      normalize(value) {
        return CoreHome.Matomo.helper.normalize(value);
      },
      isSearchMatch(value) {
        const stringValue = `${value != null ? value : ""}`;
        return this.normalize(stringValue).indexOf(this.searchTermNormalized) !== -1 || stringValue.toLowerCase().indexOf(this.searchTermLowercase) !== -1;
      },
      visibleChildren(options) {
        if (this.searchOnGroup && this.isSearchMatch(options.group)) {
          return options.values;
        }
        return options.values.filter((x) => this.isSearchMatch(x.value));
      },
      onBlur() {
        this.showSelect = false;
      },
      onCategoryClicked(options) {
        if (this.showCategory === options.group) {
          this.showCategory = "";
        } else {
          this.showCategory = options.group;
        }
      },
      onValueClicked(selectedValue) {
        var _a2;
        this.showSelect = false;
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", selectedValue.key);
          return;
        }
        const emitEventData = {
          value: selectedValue.key,
          abort() {
          }
        };
        this.$emit("update:modelValue", emitEventData);
      }
    }
  });
  const _hoisted_1$n = { class: "expandableSelector" };
  const _hoisted_2$l = /* @__PURE__ */ vue.createElementVNode("svg", {
    class: "caret",
    height: "24",
    viewBox: "0 0 24 24",
    width: "24",
    xmlns: "http://www.w3.org/2000/svg"
  }, [
    /* @__PURE__ */ vue.createElementVNode("path", { d: "M7 10l5 5 5-5z" }),
    /* @__PURE__ */ vue.createElementVNode("path", {
      d: "M0 0h24v24H0z",
      fill: "none"
    })
  ], -1);
  const _hoisted_3$b = ["value"];
  const _hoisted_4$b = { class: "expandableList z-depth-2" };
  const _hoisted_5$a = { class: "searchContainer" };
  const _hoisted_6$6 = { class: "collection firstLevel" };
  const _hoisted_7$5 = ["onClick"];
  const _hoisted_8$5 = { class: "collection secondLevel" };
  const _hoisted_9$5 = ["onClick"];
  const _hoisted_10$4 = { class: "primary-content" };
  const _hoisted_11$3 = ["title"];
  function _sfc_render$q(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_focus_if = vue.resolveDirective("focus-if");
    const _directive_focus_anywhere_but_here = vue.resolveDirective("focus-anywhere-but-here");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$n, [
      vue.createElementVNode("div", {
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.showSelect = !_ctx.showSelect),
        class: "select-wrapper"
      }, [
        _hoisted_2$l,
        vue.createElementVNode("input", {
          type: "text",
          class: "select-dropdown",
          readonly: "readonly",
          value: _ctx.modelValueText
        }, null, 8, _hoisted_3$b)
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_4$b, [
        vue.createElementVNode("div", _hoisted_5$a, [
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            placeholder: "Search",
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.searchTerm = $event),
            class: "expandableSearch browser-default"
          }, null, 512), [
            [vue.vModelText, _ctx.searchTerm],
            [_directive_focus_if, { focused: _ctx.showSelect }]
          ])
        ]),
        vue.createElementVNode("ul", _hoisted_6$6, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.availableOptions, (options, index) => {
            return vue.withDirectives((vue.openBlock(), vue.createElementBlock("li", {
              class: "collection-item",
              key: index
            }, [
              vue.createElementVNode("h4", {
                class: "expandableListCategory",
                onClick: ($event) => _ctx.onCategoryClicked(options)
              }, [
                vue.createTextVNode(vue.toDisplayString(options.group) + " ", 1),
                vue.createElementVNode("span", {
                  class: vue.normalizeClass(["secondary-content", {
                    "icon-chevron-right": _ctx.showCategory !== options.group,
                    "icon-chevron-down": _ctx.showCategory === options.group
                  }])
                }, null, 2)
              ], 8, _hoisted_7$5),
              vue.withDirectives(vue.createElementVNode("ul", _hoisted_8$5, [
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.visibleChildren(options), (children) => {
                  return vue.openBlock(), vue.createElementBlock("li", {
                    class: "expandableListItem collection-item valign-wrapper",
                    key: children.key,
                    onClick: ($event) => _ctx.onValueClicked(children)
                  }, [
                    vue.createElementVNode("span", _hoisted_10$4, vue.toDisplayString(children.value), 1),
                    vue.withDirectives(vue.createElementVNode("span", {
                      title: children.tooltip,
                      class: "secondary-content icon-help"
                    }, null, 8, _hoisted_11$3), [
                      [vue.vShow, children.tooltip]
                    ])
                  ], 8, _hoisted_9$5);
                }), 128))
              ], 512), [
                [vue.vShow, _ctx.showCategory === options.group || _ctx.searchTerm]
              ])
            ])), [
              [vue.vShow, _ctx.visibleChildren(options).length]
            ]);
          }), 128))
        ])
      ], 512), [
        [vue.vShow, _ctx.showSelect]
      ])
    ])), [
      [_directive_focus_anywhere_but_here, { blur: _ctx.onBlur }]
    ]);
  }
  const FieldExpandableSelect = /* @__PURE__ */ _export_sfc(_sfc_main$r, [["render", _sfc_render$q]]);
  const _sfc_main$q = vue.defineComponent({
    components: {
      FieldArray: CoreHome.FieldArray
    },
    props: {
      name: String,
      title: String,
      id: String,
      modelValue: null,
      modelModifiers: Object,
      uiControlAttributes: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    methods: {
      onValueUpdate(newValue) {
        this.$emit("update:modelValue", newValue);
      }
    }
  });
  const _hoisted_1$m = ["for", "innerHTML"];
  function _sfc_render$p(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FieldArray = vue.resolveComponent("FieldArray");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_1$m),
      vue.createVNode(_component_FieldArray, {
        name: _ctx.name,
        id: _ctx.id,
        "model-value": _ctx.modelValue,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.onValueUpdate($event)),
        "model-modifiers": _ctx.modelModifiers,
        field: _ctx.uiControlAttributes.field,
        rows: _ctx.uiControlAttributes.rows
      }, null, 8, ["name", "id", "model-value", "model-modifiers", "field", "rows"])
    ]);
  }
  const FieldFieldArray = /* @__PURE__ */ _export_sfc(_sfc_main$q, [["render", _sfc_render$p]]);
  const _sfc_main$p = vue.defineComponent({
    props: {
      name: String,
      title: String,
      id: String,
      modelValue: [String, File],
      modelModifiers: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    watch: {
      modelValue(v) {
        if (!v || v === "") {
          const fileInputElement = this.$refs.fileInput;
          fileInputElement.value = "";
        }
      }
    },
    methods: {
      onChange(event) {
        var _a2;
        const { files } = event.target;
        if (!files) {
          return;
        }
        const file = files.item(0);
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", file);
          return;
        }
        const emitEventData = {
          value: file,
          abort() {
          }
        };
        this.$emit("update:modelValue", emitEventData);
      }
    },
    computed: {
      filePath() {
        if (this.modelValue instanceof File) {
          return this.$refs.fileInput.value;
        }
        return void 0;
      }
    }
  });
  const _hoisted_1$l = { class: "btn" };
  const _hoisted_2$k = ["for", "innerHTML"];
  const _hoisted_3$a = ["name", "id"];
  const _hoisted_4$a = { class: "file-path-wrapper" };
  const _hoisted_5$9 = ["value"];
  function _sfc_render$o(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("div", _hoisted_1$l, [
        vue.createElementVNode("span", {
          for: _ctx.id,
          innerHTML: _ctx.$sanitize(_ctx.title)
        }, null, 8, _hoisted_2$k),
        vue.createElementVNode("input", {
          ref: "fileInput",
          name: _ctx.name,
          type: "file",
          id: _ctx.id,
          onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event))
        }, null, 40, _hoisted_3$a)
      ]),
      vue.createElementVNode("div", _hoisted_4$a, [
        vue.createElementVNode("input", {
          class: "file-path validate",
          value: _ctx.filePath,
          type: "text"
        }, null, 8, _hoisted_5$9)
      ])
    ]);
  }
  const FieldFile = /* @__PURE__ */ _export_sfc(_sfc_main$p, [["render", _sfc_render$o]]);
  const _sfc_main$o = vue.defineComponent({
    props: {
      modelValue: null,
      modelModifiers: Object,
      // not actually supported
      uiControl: String,
      name: String,
      id: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    methods: {
      onChange(event) {
        this.$emit("update:modelValue", event.target.value);
      }
    }
  });
  const _hoisted_1$k = ["type", "name", "id", "value"];
  function _sfc_render$n(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("input", {
        type: _ctx.uiControl,
        name: _ctx.name,
        id: _ctx.id,
        value: _ctx.modelValue,
        onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event))
      }, null, 40, _hoisted_1$k)
    ]);
  }
  const FieldHidden = /* @__PURE__ */ _export_sfc(_sfc_main$o, [["render", _sfc_render$n]]);
  const _sfc_main$n = vue.defineComponent({
    props: {
      name: String,
      title: String,
      id: String,
      modelValue: null,
      modelModifiers: Object,
      uiControlAttributes: Object
    },
    inheritAttrs: false,
    components: {
      MultiPairField: CoreHome.MultiPairField
    },
    emits: ["update:modelValue"],
    methods: {
      onUpdateValue(newValue) {
        this.$emit("update:modelValue", newValue);
      }
    }
  });
  const _hoisted_1$j = { class: "fieldMultiTuple" };
  const _hoisted_2$j = ["for", "innerHTML"];
  function _sfc_render$m(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MultiPairField = vue.resolveComponent("MultiPairField");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$j, [
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_2$j),
      vue.createVNode(_component_MultiPairField, {
        name: _ctx.name,
        id: _ctx.id,
        "model-value": _ctx.modelValue,
        "onUpdate:modelValue": _ctx.onUpdateValue,
        "model-modifiers": _ctx.modelModifiers,
        field1: _ctx.uiControlAttributes.field1,
        field2: _ctx.uiControlAttributes.field2,
        field3: _ctx.uiControlAttributes.field3,
        field4: _ctx.uiControlAttributes.field4,
        rows: _ctx.uiControlAttributes.rows
      }, null, 8, ["name", "id", "model-value", "onUpdate:modelValue", "model-modifiers", "field1", "field2", "field3", "field4", "rows"])
    ]);
  }
  const FieldMultituple = /* @__PURE__ */ _export_sfc(_sfc_main$n, [["render", _sfc_render$m]]);
  const _sfc_main$m = vue.defineComponent({
    props: {
      uiControl: String,
      name: String,
      title: String,
      id: String,
      modelValue: [Number, String],
      modelModifiers: Object,
      uiControlAttributes: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    created() {
      this.onChange = CoreHome.debounce(this.onChange.bind(this), 50);
    },
    methods: {
      onChange(event) {
        var _a2;
        const value = parseFloat(event.target.value);
        if (value !== this.modelValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", value);
            return;
          }
          const emitEventData = {
            value,
            abort: () => {
              if (event.target.value !== this.modelValueFormatted) {
                event.target.value = this.modelValueFormatted;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    },
    mounted() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
    watch: {
      modelValue() {
        setTimeout(() => {
          window.Materialize.updateTextFields();
        });
      }
    },
    computed: {
      modelValueFormatted() {
        return (this.modelValue || "").toString();
      }
    }
  });
  const _hoisted_1$i = ["type", "id", "name", "value"];
  const _hoisted_2$i = ["for", "innerHTML"];
  function _sfc_render$l(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("input", vue.mergeProps({
        class: `control_${_ctx.uiControl}`,
        type: _ctx.uiControl,
        id: _ctx.id,
        name: _ctx.name,
        value: _ctx.modelValueFormatted,
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onChange($event))
      }, _ctx.uiControlAttributes), null, 16, _hoisted_1$i),
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_2$i)
    ], 64);
  }
  const FieldNumber = /* @__PURE__ */ _export_sfc(_sfc_main$m, [["render", _sfc_render$l]]);
  const _sfc_main$l = vue.defineComponent({
    props: {
      title: String,
      availableOptions: Array,
      name: String,
      id: String,
      disabled: Boolean,
      uiControlAttributes: Object,
      modelValue: [String, Number],
      modelModifiers: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    methods: {
      onChange(event) {
        var _a2;
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", event.target.value);
          return;
        }
        const reset = () => {
          this.$refs.root.querySelectorAll("input").forEach((inp, i) => {
            var _a3;
            if (!((_a3 = this.availableOptions) == null ? void 0 : _a3[i])) {
              return;
            }
            const { key } = this.availableOptions[i];
            inp.checked = this.modelValue === key || `${this.modelValue}` === key;
          });
        };
        const emitEventData = {
          value: event.target.value,
          abort: () => {
            reset();
          }
        };
        this.$emit("update:modelValue", emitEventData);
      }
    }
  });
  const _hoisted_1$h = { ref: "root" };
  const _hoisted_2$h = ["value", "id", "name", "disabled", "checked"];
  function _sfc_render$k(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$h, [
      vue.withDirectives(vue.createElementVNode("label", { class: "fieldRadioTitle" }, vue.toDisplayString(_ctx.title), 513), [
        [vue.vShow, _ctx.title]
      ]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.availableOptions || [], (radioModel) => {
        return vue.openBlock(), vue.createElementBlock("p", {
          key: radioModel.key,
          class: "radio"
        }, [
          vue.createElementVNode("label", null, [
            vue.createElementVNode("input", vue.mergeProps({
              value: radioModel.key,
              onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event)),
              type: "radio",
              id: `${_ctx.id}${radioModel.key}`,
              name: _ctx.name,
              disabled: radioModel.disabled || _ctx.disabled,
              ref_for: true
            }, _ctx.uiControlAttributes, {
              checked: _ctx.modelValue === radioModel.key || `${_ctx.modelValue}` === radioModel.key
            }), null, 16, _hoisted_2$h),
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(radioModel.value) + " ", 1),
              vue.withDirectives(vue.createElementVNode("span", { class: "form-description" }, vue.toDisplayString(radioModel.description), 513), [
                [vue.vShow, radioModel.description]
              ])
            ])
          ])
        ]);
      }), 128))
    ], 512);
  }
  const FieldRadio = /* @__PURE__ */ _export_sfc(_sfc_main$l, [["render", _sfc_render$k]]);
  function initMaterialSelect(select, modelValue, placeholder, uiControlOptions = {}, multiple) {
    if (!select) {
      return;
    }
    const $select = window.$(select);
    Array.from(select.options).forEach((opt) => {
      if (multiple) {
        opt.selected = !!modelValue && modelValue.indexOf(opt.value.replace(/^string:/, "")) !== -1;
      } else {
        opt.selected = `string:${modelValue}` === opt.value;
      }
    });
    $select.formSelect(uiControlOptions);
    if (placeholder) {
      const $materialInput = $select.closest(".select-wrapper").find("input");
      $materialInput.attr("placeholder", placeholder);
    }
  }
  function hasGroupedValues(availableValues) {
    if (Array.isArray(availableValues) || !(typeof availableValues === "object")) {
      return false;
    }
    return Object.values(availableValues).some(
      (v) => typeof v === "object"
    );
  }
  function hasOption(flatValues, key) {
    return flatValues.some((f) => f.key === key);
  }
  function getAvailableOptions(givenAvailableValues, type, uiControlAttributes) {
    if (!givenAvailableValues) {
      return [];
    }
    let hasGroups = true;
    let availableValues = givenAvailableValues;
    if (!hasGroupedValues(availableValues)) {
      availableValues = { "": givenAvailableValues };
      hasGroups = false;
    }
    const flatValues = [];
    Object.entries(availableValues).forEach(([group, values2]) => {
      Object.entries(values2).forEach(([valueObjKey, value]) => {
        if (value && typeof value === "object" && typeof value.key !== "undefined") {
          flatValues.push(value);
          return;
        }
        let key = valueObjKey;
        if (type === "integer" && typeof valueObjKey === "string") {
          key = parseInt(valueObjKey, 10);
        }
        flatValues.push({ group: hasGroups ? group : void 0, key, value });
      });
    });
    if ((uiControlAttributes == null ? void 0 : uiControlAttributes.placeholder) && !hasOption(flatValues, "")) {
      return [{ key: "", value: "" }, ...flatValues];
    }
    return flatValues;
  }
  function handleOldAngularJsValues(value) {
    if (typeof value === "string") {
      return value.replace(/^string:/, "");
    }
    return value;
  }
  const _sfc_main$k = vue.defineComponent({
    props: {
      modelValue: null,
      modelModifiers: Object,
      multiple: Boolean,
      name: String,
      title: String,
      id: String,
      availableOptions: Array,
      uiControlAttributes: Object,
      uiControlOptions: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    computed: {
      options() {
        const availableOptions = this.availableOptions;
        if (availableOptions && !hasOption(availableOptions, "") && (typeof this.modelValue === "undefined" || this.modelValue === null || this.modelValue === "")) {
          return [
            { key: "", value: this.modelValue, group: this.hasGroups ? "" : void 0 },
            ...availableOptions
          ];
        }
        return availableOptions;
      },
      hasGroups() {
        const availableOptions = this.availableOptions;
        return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== "undefined";
      },
      groupedOptions() {
        const { options } = this;
        if (!this.hasGroups || !options) {
          return null;
        }
        const groups = {};
        options.forEach((entry) => {
          const group = entry.group;
          groups[group] = groups[group] || [];
          groups[group].push(entry);
        });
        const result = Object.entries(groups);
        result.sort((lhs, rhs) => {
          if (lhs[0] < rhs[0]) {
            return -1;
          }
          if (lhs[0] > rhs[0]) {
            return 1;
          }
          return 0;
        });
        return result;
      }
    },
    methods: {
      onChange(event) {
        var _a2;
        const element = event.target;
        let newValue;
        if (this.multiple) {
          newValue = Array.from(element.options).filter((e) => e.selected).map((e) => e.value);
          newValue = newValue.map((x) => handleOldAngularJsValues(x));
        } else {
          newValue = element.value;
          newValue = handleOldAngularJsValues(newValue);
        }
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", newValue);
          return;
        }
        const emitEventData = {
          value: newValue,
          abort: () => {
            this.onModelValueChange(this.modelValue);
          }
        };
        this.$emit("update:modelValue", emitEventData);
      },
      onModelValueChange(newVal) {
        window.$(this.$refs.select).val(newVal);
        setTimeout(() => {
          var _a2;
          initMaterialSelect(
            this.$refs.select,
            newVal,
            (_a2 = this.uiControlAttributes) == null ? void 0 : _a2.placeholder,
            this.uiControlOptions,
            this.multiple
          );
        });
      }
    },
    watch: {
      modelValue(newVal) {
        this.onModelValueChange(newVal);
      },
      "uiControlAttributes.disabled": {
        handler(newVal, oldVal) {
          setTimeout(() => {
            var _a2;
            if (newVal !== oldVal) {
              initMaterialSelect(
                this.$refs.select,
                this.modelValue,
                (_a2 = this.uiControlAttributes) == null ? void 0 : _a2.placeholder,
                this.uiControlOptions,
                this.multiple
              );
            }
          });
        }
      },
      availableOptions(newVal, oldVal) {
        if (newVal !== oldVal) {
          setTimeout(() => {
            var _a2;
            initMaterialSelect(
              this.$refs.select,
              this.modelValue,
              (_a2 = this.uiControlAttributes) == null ? void 0 : _a2.placeholder,
              this.uiControlOptions,
              this.multiple
            );
          });
        }
      }
    },
    mounted() {
      setTimeout(() => {
        var _a2;
        initMaterialSelect(
          this.$refs.select,
          this.modelValue,
          (_a2 = this.uiControlAttributes) == null ? void 0 : _a2.placeholder,
          this.uiControlOptions,
          this.multiple
        );
      });
    }
  });
  const _hoisted_1$g = {
    key: 0,
    class: "matomo-field-select"
  };
  const _hoisted_2$g = ["multiple", "name", "id"];
  const _hoisted_3$9 = ["label"];
  const _hoisted_4$9 = ["value", "selected", "disabled"];
  const _hoisted_5$8 = ["for", "innerHTML"];
  const _hoisted_6$5 = {
    key: 1,
    class: "matomo-field-select"
  };
  const _hoisted_7$4 = ["multiple", "name", "id"];
  const _hoisted_8$4 = ["value", "selected", "disabled"];
  const _hoisted_9$4 = ["for", "innerHTML"];
  function _sfc_render$j(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      _ctx.groupedOptions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$g, [
        vue.createElementVNode("select", vue.mergeProps({
          ref: "select",
          class: "grouped",
          multiple: _ctx.multiple,
          name: _ctx.name,
          id: _ctx.id,
          onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event))
        }, _ctx.uiControlAttributes), [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.groupedOptions, ([group, options]) => {
            return vue.openBlock(), vue.createElementBlock("optgroup", {
              key: group,
              label: group
            }, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(options, (option) => {
                return vue.openBlock(), vue.createElementBlock("option", {
                  key: option.key,
                  value: `string:${option.key}`,
                  selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
                  disabled: option.disabled
                }, vue.toDisplayString(option.value), 9, _hoisted_4$9);
              }), 128))
            ], 8, _hoisted_3$9);
          }), 128))
        ], 16, _hoisted_2$g),
        vue.createElementVNode("label", {
          for: _ctx.id,
          innerHTML: _ctx.$sanitize(_ctx.title)
        }, null, 8, _hoisted_5$8)
      ])) : vue.createCommentVNode("", true),
      !_ctx.groupedOptions && _ctx.options ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$5, [
        vue.createElementVNode("select", vue.mergeProps({
          class: "ungrouped",
          ref: "select",
          multiple: _ctx.multiple,
          name: _ctx.name,
          id: _ctx.id,
          onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onChange($event))
        }, _ctx.uiControlAttributes), [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.options, (option) => {
            return vue.openBlock(), vue.createElementBlock("option", {
              key: option.key,
              value: `string:${option.key}`,
              selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
              disabled: option.disabled
            }, vue.toDisplayString(option.value), 9, _hoisted_8$4);
          }), 128))
        ], 16, _hoisted_7$4),
        vue.createElementVNode("label", {
          for: _ctx.id,
          innerHTML: _ctx.$sanitize(_ctx.title)
        }, null, 8, _hoisted_9$4)
      ])) : vue.createCommentVNode("", true)
    ], 64);
  }
  const FieldSelect = /* @__PURE__ */ _export_sfc(_sfc_main$k, [["render", _sfc_render$j]]);
  const _sfc_main$j = vue.defineComponent({
    props: {
      name: String,
      title: String,
      id: String,
      modelValue: Object,
      modelModifiers: Object,
      uiControlAttributes: Object
    },
    inheritAttrs: false,
    components: {
      SiteSelector: CoreHome.SiteSelector
    },
    emits: ["update:modelValue"],
    methods: {
      onChange(newValue) {
        var _a2;
        if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
          this.$emit("update:modelValue", newValue);
          return;
        }
        const emitEventData = {
          value: newValue,
          abort() {
          }
        };
        this.$emit("update:modelValue", emitEventData);
      }
    }
  });
  const _hoisted_1$f = ["for", "innerHTML"];
  const _hoisted_2$f = { class: "sites_autocomplete" };
  function _sfc_render$i(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SiteSelector = vue.resolveComponent("SiteSelector");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("label", {
        for: _ctx.id,
        class: "siteSelectorLabel",
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_1$f),
      vue.createElementVNode("div", _hoisted_2$f, [
        vue.createVNode(_component_SiteSelector, vue.mergeProps({
          "model-value": _ctx.modelValue,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event)),
          id: _ctx.id,
          "show-all-sites-item": _ctx.uiControlAttributes.showAllSitesItem || false,
          "switch-site-on-select": false,
          "show-selected-site": true,
          "only-sites-with-admin-access": _ctx.uiControlAttributes.onlySitesWithAdminAccess || false,
          "only-sites-with-at-least-write-access": _ctx.uiControlAttributes.onlySitesWithAtLeastWriteAccess || false
        }, _ctx.uiControlAttributes), null, 16, ["model-value", "id", "show-all-sites-item", "only-sites-with-admin-access", "only-sites-with-at-least-write-access"])
      ])
    ]);
  }
  const FieldSite = /* @__PURE__ */ _export_sfc(_sfc_main$j, [["render", _sfc_render$i]]);
  const _sfc_main$i = vue.defineComponent({
    props: {
      title: String,
      name: String,
      id: String,
      uiControlAttributes: Object,
      modelValue: [String, Number],
      modelModifiers: Object,
      uiControl: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    computed: {
      modelValueText() {
        if (typeof this.modelValue === "undefined" || this.modelValue === null) {
          return "";
        }
        return this.modelValue.toString();
      }
    },
    created() {
      this.onKeydown = CoreHome.debounce(this.onKeydown.bind(this), 50);
    },
    mounted() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
    watch: {
      modelValue() {
        setTimeout(() => {
          window.Materialize.updateTextFields();
        });
      }
    },
    methods: {
      onKeydown(event) {
        var _a2;
        const newValue = event.target.value;
        if (this.modelValue !== newValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", newValue);
            return;
          }
          const emitEventData = {
            value: newValue,
            abort: () => {
              if (event.target.value !== this.modelValueText) {
                event.target.value = this.modelValueText;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    }
  });
  const _hoisted_1$e = ["type", "id", "name", "value", "spellcheck"];
  const _hoisted_2$e = ["for", "innerHTML"];
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("input", vue.mergeProps({
        class: `control_${_ctx.uiControl}`,
        type: _ctx.uiControl,
        id: _ctx.id,
        name: _ctx.name,
        value: _ctx.modelValueText,
        spellcheck: _ctx.uiControl === "password" ? false : null,
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydown($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event))
      }, _ctx.uiControlAttributes), null, 16, _hoisted_1$e),
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_2$e)
    ], 64);
  }
  const FieldText = /* @__PURE__ */ _export_sfc(_sfc_main$i, [["render", _sfc_render$h]]);
  const _sfc_main$h = vue.defineComponent({
    props: {
      name: String,
      title: String,
      id: String,
      uiControl: String,
      modelValue: Array,
      modelModifiers: Object,
      uiControlAttributes: Object
    },
    inheritAttrs: false,
    computed: {
      concattedValues() {
        if (typeof this.modelValue === "string") {
          return this.modelValue;
        }
        return (this.modelValue || []).join(", ");
      }
    },
    emits: ["update:modelValue"],
    created() {
      this.onKeydown = CoreHome.debounce(this.onKeydown.bind(this), 50);
    },
    methods: {
      onKeydown(event) {
        var _a2;
        const values2 = event.target.value.split(",").map((v) => v.trim());
        if (values2.join(", ") !== this.concattedValues) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", values2);
            return;
          }
          const emitEventData = {
            value: values2,
            abort: () => {
              if (event.target.value !== this.concattedValues) {
                event.target.value = this.concattedValues;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    }
  });
  const _hoisted_1$d = ["for", "innerHTML"];
  const _hoisted_2$d = ["type", "name", "id", "value"];
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_1$d),
      vue.createElementVNode("input", vue.mergeProps({
        class: `control_${_ctx.uiControl}`,
        type: _ctx.uiControl,
        name: _ctx.name,
        id: _ctx.id,
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydown($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event)),
        value: _ctx.concattedValues
      }, _ctx.uiControlAttributes), null, 16, _hoisted_2$d)
    ]);
  }
  const FieldTextArray = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$g]]);
  const _sfc_main$g = vue.defineComponent({
    props: {
      name: String,
      uiControlAttributes: Object,
      modelValue: String,
      modelModifiers: Object,
      title: String,
      id: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    created() {
      this.onKeydown = CoreHome.debounce(this.onKeydown.bind(this), 50);
    },
    methods: {
      onKeydown(event) {
        var _a2;
        const newValue = event.target.value;
        if (newValue !== this.modelValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", newValue);
            return;
          }
          const emitEventData = {
            value: newValue,
            abort: () => {
              if (event.target.value !== this.modelValue) {
                event.target.value = this.modelValueText;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    },
    computed: {
      modelValueText() {
        return this.modelValue || "";
      }
    },
    watch: {
      modelValue() {
        setTimeout(() => {
          window.Materialize.textareaAutoResize(this.$refs.textarea);
          window.Materialize.updateTextFields();
        });
      }
    },
    mounted() {
      setTimeout(() => {
        window.Materialize.textareaAutoResize(this.$refs.textarea);
        window.Materialize.updateTextFields();
      });
    }
  });
  const _hoisted_1$c = ["name", "id", "value"];
  const _hoisted_2$c = ["for", "innerHTML"];
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("textarea", vue.mergeProps({ name: _ctx.name }, _ctx.uiControlAttributes, {
        id: _ctx.id,
        value: _ctx.modelValueText,
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydown($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event)),
        class: "materialize-textarea",
        ref: "textarea"
      }), null, 16, _hoisted_1$c),
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_2$c)
    ], 64);
  }
  const FieldTextarea = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$f]]);
  const SEPARATOR = "\n";
  const _sfc_main$f = vue.defineComponent({
    props: {
      name: String,
      title: String,
      id: String,
      uiControlAttributes: Object,
      modelValue: [Array, String],
      modelModifiers: Object
    },
    inheritAttrs: false,
    emits: ["update:modelValue"],
    computed: {
      concattedValue() {
        if (typeof this.modelValue === "string") {
          return this.modelValue;
        }
        if (typeof this.modelValue === "object") {
          return Object.values(this.modelValue).join(SEPARATOR);
        }
        try {
          return (this.modelValue || []).join(SEPARATOR);
        } catch (e) {
          console.error(e);
          return "";
        }
      }
    },
    created() {
      this.onKeydown = CoreHome.debounce(this.onKeydown.bind(this), 50);
    },
    methods: {
      onKeydown(event) {
        var _a2;
        const value = event.target.value.split(SEPARATOR);
        if (value.join(SEPARATOR) !== this.concattedValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", value);
            return;
          }
          const emitEventData = {
            value,
            abort: () => {
              if (event.target.value !== this.concattedValue) {
                event.target.value = this.concattedValue;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      }
    },
    watch: {
      modelValue(newVal, oldVal) {
        if (newVal !== oldVal) {
          setTimeout(() => {
            if (this.$refs.textarea) {
              window.Materialize.textareaAutoResize(this.$refs.textarea);
            }
            window.Materialize.updateTextFields();
          });
        }
      }
    },
    mounted() {
      setTimeout(() => {
        if (this.$refs.textarea) {
          window.Materialize.textareaAutoResize(this.$refs.textarea);
        }
        window.Materialize.updateTextFields();
      });
    }
  });
  const _hoisted_1$b = ["for", "innerHTML"];
  const _hoisted_2$b = ["name", "id", "value"];
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_1$b),
      vue.createElementVNode("textarea", vue.mergeProps({
        ref: "textarea",
        name: _ctx.name,
        id: _ctx.id
      }, _ctx.uiControlAttributes, {
        value: _ctx.concattedValue,
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydown($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event)),
        class: "materialize-textarea"
      }), null, 16, _hoisted_2$b)
    ]);
  }
  const FieldTextareaArray = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$e]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function processCheckboxAndRadioAvailableValues(availableValues, type) {
    if (!availableValues) {
      return [];
    }
    const flatValues = [];
    Object.entries(availableValues).forEach(([valueObjKey, value]) => {
      if (value && typeof value === "object" && typeof value.key !== "undefined") {
        flatValues.push(value);
        return;
      }
      let key = valueObjKey;
      if (type === "integer" && typeof valueObjKey === "string") {
        key = parseInt(key, 10);
      }
      flatValues.push({ key, value });
    });
    return flatValues;
  }
  const _sfc_main$e = vue.defineComponent({
    components: { PasswordStrength: CoreHome.PasswordStrength },
    props: {
      title: String,
      name: String,
      id: String,
      uiControlAttributes: Object,
      modelValue: [String, Number],
      modelModifiers: Object,
      uiControl: String
    },
    inheritAttrs: false,
    emits: ["update:modelValue", "check:isValid"],
    computed: {
      modelValueText() {
        if (typeof this.modelValue === "undefined" || this.modelValue === null) {
          return "";
        }
        return this.modelValue.toString();
      },
      passwordStrengthValidationRules() {
        var _a2, _b;
        return (_b = (_a2 = this.uiControlAttributes) == null ? void 0 : _a2.passwordStrengthValidationRules) != null ? _b : [];
      }
    },
    created() {
      this.onKeydown = CoreHome.debounce(this.onKeydown.bind(this), 50);
    },
    mounted() {
      setTimeout(() => {
        window.Materialize.updateTextFields();
      });
    },
    watch: {
      modelValue() {
        setTimeout(() => {
          window.Materialize.updateTextFields();
        });
      }
    },
    methods: {
      onKeydown(event) {
        var _a2;
        const newValue = event.target.value;
        if (this.modelValue !== newValue) {
          if (!((_a2 = this.modelModifiers) == null ? void 0 : _a2.abortable)) {
            this.$emit("update:modelValue", newValue);
            return;
          }
          const emitEventData = {
            value: newValue,
            abort: () => {
              if (event.target.value !== this.modelValueText) {
                event.target.value = this.modelValueText;
              }
            }
          };
          this.$emit("update:modelValue", emitEventData);
        }
      },
      onCheckIsValid(isValid) {
        this.$emit("check:isValid", isValid);
      }
    }
  });
  const _hoisted_1$a = ["type", "id", "name", "value"];
  const _hoisted_2$a = ["for", "innerHTML"];
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PasswordStrength = vue.resolveComponent("PasswordStrength");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("input", vue.mergeProps({
        class: `control_${_ctx.uiControl}`,
        type: _ctx.uiControl,
        id: _ctx.id,
        name: _ctx.name,
        value: _ctx.modelValueText,
        spellcheck: "false",
        autocomplete: "current-password",
        autocorrect: "off",
        autocapitalize: "none",
        onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydown($event)),
        onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event))
      }, _ctx.uiControlAttributes), null, 16, _hoisted_1$a),
      vue.createElementVNode("label", {
        for: _ctx.id,
        innerHTML: _ctx.$sanitize(_ctx.title)
      }, null, 8, _hoisted_2$a),
      vue.createVNode(_component_PasswordStrength, {
        password: _ctx.modelValueText,
        "validation-rules": _ctx.passwordStrengthValidationRules,
        "onCheck:isValid": _cache[2] || (_cache[2] = ($event) => _ctx.onCheckIsValid($event))
      }, null, 8, ["password", "validation-rules"])
    ], 64);
  }
  const FieldPassword = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$d]]);
  const TEXT_CONTROLS = ["url", "search", "email"];
  const CONTROLS_SUPPORTING_ARRAY = ["textarea", "checkbox", "text"];
  const CONTROL_TO_COMPONENT_MAP = {
    checkbox: "FieldCheckbox",
    "expandable-select": "FieldExpandableSelect",
    "field-array": "FieldFieldArray",
    file: "FieldFile",
    hidden: "FieldHidden",
    multiselect: "FieldSelect",
    multituple: "FieldMultituple",
    number: "FieldNumber",
    password: "FieldPassword",
    radio: "FieldRadio",
    select: "FieldSelect",
    site: "FieldSite",
    text: "FieldText",
    textarea: "FieldTextarea"
  };
  const CONTROL_TO_AVAILABLE_OPTION_PROCESSOR = {
    FieldSelect: getAvailableOptions,
    FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
    FieldRadio: processCheckboxAndRadioAvailableValues,
    FieldExpandableSelect: getAvailableOptions$1
  };
  const _sfc_main$d = vue.defineComponent({
    props: {
      modelValue: null,
      modelModifiers: Object,
      formField: {
        type: Object,
        required: true
      }
    },
    emits: ["update:modelValue", "check:isValid"],
    components: {
      Notification: CoreHome.Notification,
      FieldCheckbox,
      FieldCheckboxArray,
      FieldExpandableSelect,
      FieldFieldArray,
      FieldFile,
      FieldHidden,
      FieldMultituple,
      FieldNumber,
      FieldRadio,
      FieldSelect,
      FieldSite,
      FieldText,
      FieldTextArray,
      FieldTextarea,
      FieldTextareaArray,
      FieldPassword
    },
    setup(props) {
      const inlineHelpNode = vue.ref(null);
      const setInlineHelp = (newVal) => {
        let toAppend;
        if (!newVal || !inlineHelpNode.value || typeof newVal.render === "function") {
          return;
        }
        if (typeof newVal === "string") {
          if (newVal.indexOf("#") === 0) {
            toAppend = window.$(newVal);
          } else {
            toAppend = window.vueSanitize(newVal);
          }
        } else {
          toAppend = newVal;
        }
        window.$(inlineHelpNode.value).html("").append(toAppend);
      };
      vue.watch(() => props.formField.inlineHelp, setInlineHelp);
      vue.onMounted(() => {
        setInlineHelp(props.formField.inlineHelp);
      });
      return {
        inlineHelp: inlineHelpNode
      };
    },
    computed: {
      inlineHelpComponent() {
        const formField = this.formField;
        const inlineHelpRecord = formField.inlineHelp;
        if (inlineHelpRecord && typeof inlineHelpRecord.render === "function") {
          return formField.inlineHelp;
        }
        return void 0;
      },
      inlineHelpBind() {
        return this.inlineHelpComponent ? this.formField.inlineHelpBind : void 0;
      },
      childComponent() {
        const formField = this.formField;
        if (formField.component) {
          let component = formField.component;
          if (formField.component.plugin) {
            const { plugin, name: name2 } = formField.component;
            if (!plugin || !name2) {
              throw new Error("Invalid component property given to FormField directive, must be {plugin: '...',name: '...'}");
            }
            component = CoreHome.useExternalPluginComponent(plugin, name2);
          }
          return vue.markRaw(component);
        }
        const { uiControl } = formField;
        let control = CONTROL_TO_COMPONENT_MAP[uiControl];
        if (TEXT_CONTROLS.indexOf(uiControl) !== -1) {
          control = "FieldText";
        }
        if (this.formField.type === "array" && CONTROLS_SUPPORTING_ARRAY.indexOf(uiControl) !== -1) {
          control = `${control}Array`;
        }
        return control;
      },
      extraChildComponentParams() {
        if (this.formField.uiControl === "multiselect") {
          return { multiple: true };
        }
        return {};
      },
      showFormHelp() {
        return this.formField.description || this.formField.inlineHelp || this.showDefaultValue || this.hasInlineHelpSlot;
      },
      showDefaultValue() {
        return this.defaultValuePretty && this.formField.uiControl !== "checkbox" && this.formField.uiControl !== "radio";
      },
      processedModelValue() {
        const field = this.formField;
        if (field.type === "boolean") {
          const valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== "0";
          if (field.uiControl === "checkbox") {
            return valueIsTruthy;
          }
          if (field.uiControl === "radio") {
            return valueIsTruthy ? "1" : "0";
          }
        }
        return this.modelValue;
      },
      defaultValue() {
        const { defaultValue } = this.formField;
        if (Array.isArray(defaultValue)) {
          return defaultValue.join(",");
        }
        return defaultValue;
      },
      availableOptions() {
        const { childComponent } = this;
        if (typeof childComponent !== "string") {
          return null;
        }
        const formField = this.formField;
        if (!formField.availableValues || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]) {
          return null;
        }
        return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](
          formField.availableValues,
          formField.type,
          formField.uiControlAttributes
        );
      },
      defaultValuePretty() {
        const formField = this.formField;
        let { defaultValue } = formField;
        const { availableOptions } = this;
        if (typeof defaultValue === "string" && defaultValue) {
          let defaultParsed = null;
          try {
            defaultParsed = JSON.parse(defaultValue);
          } catch (e) {
          }
          if (defaultParsed !== null && typeof defaultParsed === "object") {
            return "";
          }
        }
        if (!Array.isArray(availableOptions)) {
          if (Array.isArray(defaultValue)) {
            return "";
          }
          return defaultValue ? `${defaultValue}` : "";
        }
        const prettyValues = [];
        if (!Array.isArray(defaultValue)) {
          defaultValue = [defaultValue];
        }
        (availableOptions || []).forEach((value) => {
          if (typeof value.value !== "undefined" && defaultValue.indexOf(value.key) !== -1) {
            prettyValues.push(value.value);
          }
        });
        return prettyValues.join(", ");
      },
      defaultValuePrettyTruncated() {
        return this.defaultValuePretty.substring(0, 50);
      },
      hasInlineHelpSlot() {
        var _a2, _b;
        if (!this.$slots["inline-help"]) {
          return false;
        }
        const inlineHelpSlot = this.$slots["inline-help"]();
        return !!((_b = (_a2 = inlineHelpSlot == null ? void 0 : inlineHelpSlot[0]) == null ? void 0 : _a2.children) == null ? void 0 : _b.length);
      },
      fieldId() {
        return this.formField.id ? this.formField.id : this.formField.name;
      },
      getExtraMetadataIdSite() {
        var _a2;
        return (_a2 = this.formField.extraMetadata) == null ? void 0 : _a2.idSite;
      },
      isPrivacyPolicyControlled() {
        var _a2;
        return ((_a2 = this.formField.extraMetadata) == null ? void 0 : _a2.compliancePolicyControlled) !== void 0;
      },
      privacyPolicyLink() {
        var _a2;
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "PrivacyManager",
          action: "compliance",
          idSite: (_a2 = this.getExtraMetadataIdSite) != null ? _a2 : "all"
        }))}`;
      }
    },
    methods: {
      onChange(newValue) {
        this.$emit("update:modelValue", newValue);
      },
      onCheckIsValid(isValid) {
        this.$emit("check:isValid", isValid);
      }
    }
  });
  const _hoisted_1$9 = {
    key: 0,
    class: "col s12"
  };
  const _hoisted_2$9 = {
    key: 0,
    class: "form-group__error-message"
  };
  const _hoisted_3$8 = {
    key: 0,
    class: "form-help"
  };
  const _hoisted_4$8 = {
    key: 0,
    class: "inline-help",
    ref: "inlineHelp"
  };
  const _hoisted_5$7 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_6$4 = ["href"];
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Notification = vue.resolveComponent("Notification");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["form-group row matomo-form-field", { "form-group--error": _ctx.formField.errorMessage }])
    }, [
      _ctx.formField.introduction ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_1$9, vue.toDisplayString(_ctx.formField.introduction), 1)) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", {
        class: vue.normalizeClass(["col s12", {
          "input-field": _ctx.formField.uiControl !== "checkbox" && _ctx.formField.uiControl !== "radio",
          "file-field": _ctx.formField.uiControl === "file",
          "m6": !_ctx.formField.fullWidth
        }])
      }, [
        (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.childComponent), vue.mergeProps(__spreadValues(__spreadProps(__spreadValues({
          formField: _ctx.formField
        }, _ctx.formField), {
          id: _ctx.fieldId,
          modelValue: _ctx.processedModelValue,
          modelModifiers: _ctx.modelModifiers,
          availableOptions: _ctx.availableOptions
        }), _ctx.extraChildComponentParams), {
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event)),
          "onCheck:isValid": _cache[1] || (_cache[1] = ($event) => _ctx.onCheckIsValid($event))
        }), null, 16)),
        _ctx.formField.errorMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$9, vue.toDisplayString(_ctx.formField.errorMessage), 1)) : vue.createCommentVNode("", true)
      ], 2),
      vue.createElementVNode("div", {
        class: vue.normalizeClass(["col s12", { "m6": !_ctx.formField.fullWidth }])
      }, [
        _ctx.showFormHelp ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$8, [
          vue.withDirectives(vue.createElementVNode("div", { class: "form-description" }, vue.toDisplayString(_ctx.formField.description), 513), [
            [vue.vShow, _ctx.formField.description]
          ]),
          _ctx.formField.inlineHelp || _ctx.hasInlineHelpSlot ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$8, [
            _ctx.inlineHelpComponent ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.inlineHelpComponent), vue.normalizeProps(vue.mergeProps({ key: 0 }, _ctx.inlineHelpBind)), null, 16)) : vue.createCommentVNode("", true),
            vue.renderSlot(_ctx.$slots, "inline-help")
          ], 512)) : vue.createCommentVNode("", true),
          vue.withDirectives(vue.createElementVNode("span", null, [
            _hoisted_5$7,
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Default")) + ": ", 1),
            vue.createElementVNode("span", null, vue.toDisplayString(_ctx.defaultValuePrettyTruncated), 1)
          ], 512), [
            [vue.vShow, _ctx.showDefaultValue]
          ])
        ])) : vue.createCommentVNode("", true),
        _ctx.isPrivacyPolicyControlled ? (vue.openBlock(), vue.createBlock(_component_Notification, {
          key: 1,
          noclear: true,
          context: "info"
        }, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_PolicyControlledSetting")) + " ", 1),
            vue.createElementVNode("a", { href: _ctx.privacyPolicyLink }, vue.toDisplayString(_ctx.translate("PrivacyManager_ViewPrivacyComplianceOverview")), 9, _hoisted_6$4)
          ]),
          _: 1
        })) : vue.createCommentVNode("", true)
      ], 2)
    ], 2);
  }
  const FormField = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$c]]);
  const UI_CONTROLS_TO_TYPE = {
    multiselect: "array",
    checkbox: "boolean",
    site: "object",
    number: "integer"
  };
  const _sfc_main$c = vue.defineComponent({
    props: {
      modelValue: null,
      modelModifiers: Object,
      errorMessage: String,
      uicontrol: String,
      name: String,
      id: {
        type: String,
        default: () => ""
      },
      defaultValue: null,
      options: [Object, Array],
      description: String,
      introduction: String,
      title: String,
      searchOnGroup: Boolean,
      inlineHelp: [String, Object],
      inlineHelpBind: Object,
      disabled: Boolean,
      uiControlAttributes: {
        type: Object,
        default: () => ({})
      },
      uiControlOptions: {
        type: Object,
        default: () => ({})
      },
      autocomplete: String,
      varType: String,
      autofocus: Boolean,
      tabindex: Number,
      fullWidth: Boolean,
      maxlength: Number,
      required: Boolean,
      placeholder: String,
      rows: Number,
      min: Number,
      max: Number,
      component: null,
      extraMetadata: {
        type: Object,
        default: () => ({})
      }
    },
    emits: ["update:modelValue", "check:isValid"],
    components: {
      FormField
    },
    computed: {
      type() {
        if (this.varType) {
          return this.varType;
        }
        const uicontrol = this.uicontrol;
        if (uicontrol && UI_CONTROLS_TO_TYPE[uicontrol]) {
          return UI_CONTROLS_TO_TYPE[uicontrol];
        }
        return "string";
      },
      field() {
        return {
          uiControl: this.uicontrol,
          type: this.type,
          name: this.name,
          id: this.id ? this.id : this.name,
          defaultValue: this.defaultValue,
          availableValues: this.options,
          description: this.description,
          introduction: this.introduction,
          inlineHelp: this.inlineHelp,
          inlineHelpBind: this.inlineHelpBind,
          errorMessage: this.errorMessage,
          title: this.title,
          searchOnGroup: this.searchOnGroup,
          component: this.component,
          uiControlAttributes: __spreadProps(__spreadValues({}, this.uiControlAttributes), {
            disabled: this.disabled,
            autocomplete: this.autocomplete,
            tabindex: this.tabindex,
            autofocus: this.autofocus,
            rows: this.rows,
            required: this.required,
            maxlength: this.maxlength,
            placeholder: this.placeholder,
            min: this.min,
            max: this.max
          }),
          fullWidth: this.fullWidth,
          uiControlOptions: this.uiControlOptions,
          extraMetadata: this.extraMetadata
        };
      }
    },
    methods: {
      onChange(newValue) {
        this.$emit("update:modelValue", newValue);
      },
      onCheckIsValid(isValid) {
        this.$emit("check:isValid", isValid);
      }
    }
  });
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FormField = vue.resolveComponent("FormField");
    return vue.openBlock(), vue.createBlock(_component_FormField, {
      "form-field": _ctx.field,
      "model-value": _ctx.modelValue,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.onChange($event)),
      "onCheck:isValid": _cache[1] || (_cache[1] = ($event) => _ctx.onCheckIsValid($event)),
      "model-modifiers": _ctx.modelModifiers
    }, {
      "inline-help": vue.withCtx(() => [
        vue.renderSlot(_ctx.$slots, "inline-help")
      ]),
      _: 3
    }, 8, ["form-field", "model-value", "model-modifiers"]);
  }
  const Field = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$b]]);
  const _sfc_main$b = vue.defineComponent({
    props: {
      setting: {
        type: Object,
        required: true
      },
      modelValue: null,
      conditionValues: {
        type: Object,
        required: true
      }
    },
    components: {
      FormField
    },
    emits: ["update:modelValue"],
    computed: {
      showField() {
        let condition = this.setting.condition;
        if (!condition) {
          return true;
        }
        condition = condition.replace(/&&/g, " and ");
        condition = condition.replace(/\|\|/g, " or ");
        condition = condition.replace(/!/g, " not ");
        try {
          return math.evaluate(condition, this.conditionValues);
        } catch (e) {
          console.log(`failed to parse setting condition '${condition}': ${e.message}`);
          console.log(this.conditionValues);
          return false;
        }
      }
    },
    methods: {
      changeValue(newValue) {
        this.$emit("update:modelValue", newValue);
      }
    }
  });
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FormField = vue.resolveComponent("FormField");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createVNode(_component_FormField, {
        "model-value": _ctx.modelValue,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.changeValue($event)),
        "form-field": _ctx.setting
      }, null, 8, ["model-value", "form-field"])
    ], 512)), [
      [vue.vShow, _ctx.showField]
    ]);
  }
  const GroupedSetting = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$a]]);
  const _sfc_main$a = vue.defineComponent({
    props: {
      groupName: String,
      settings: {
        type: Array,
        required: true
      },
      allSettingValues: {
        type: Object,
        required: true
      }
    },
    emits: ["change"],
    components: {
      GroupedSetting
    },
    computed: {
      settingValues() {
        const entries = Object.entries(this.allSettingValues).filter(([key]) => {
          if (this.groupName) {
            const [groupName] = key.split(".");
            if (groupName !== this.groupName) {
              return false;
            }
          }
          return true;
        }).map(([key, value]) => this.groupName ? [key.split(".")[1], value] : [key, value]);
        return Object.fromEntries(entries);
      },
      groupPrefix() {
        if (!this.groupName) {
          return "";
        }
        return `${this.groupName}.`;
      }
    }
  });
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_GroupedSetting = vue.resolveComponent("GroupedSetting");
    return vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.settings, (setting) => {
      return vue.openBlock(), vue.createElementBlock("div", {
        key: `${_ctx.groupPrefix}${setting.name}`
      }, [
        vue.createVNode(_component_GroupedSetting, {
          "model-value": _ctx.allSettingValues[`${_ctx.groupPrefix}${setting.name}`],
          "onUpdate:modelValue": ($event) => _ctx.$emit("change", { name: setting.name, value: $event }),
          setting,
          "condition-values": _ctx.settingValues
        }, null, 8, ["model-value", "onUpdate:modelValue", "setting", "condition-values"])
      ]);
    }), 128);
  }
  const GroupedSettings = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$9]]);
  const { $: $$6 } = window;
  const _sfc_main$9 = vue.defineComponent({
    props: {
      /**
       * Whether the confirmation is displayed or not;
       */
      modelValue: {
        type: Boolean,
        required: true
      },
      passwordFieldId: {
        type: String,
        default: () => "currentUserPassword"
      }
    },
    data() {
      return {
        passwordConfirmation: "",
        slotHasContent: true,
        altIdConfirmComponent: { plugin: "", component: "" }
      };
    },
    emits: ["confirmed", "aborted", "update:modelValue"],
    directives: {
      AutoClearPassword: CoreHome.AutoClearPassword
    },
    components: {
      Field
    },
    activated() {
      this.$emit("update:modelValue", false);
    },
    methods: {
      onClickConfirm(event) {
        event.preventDefault();
        this.onConfirm(this.passwordConfirmation);
        this.passwordConfirmation = "";
      },
      onConfirm(passwordConfirmation) {
        const root = this.$refs.root;
        const $root = $$6(root);
        $root.modal("close");
        this.$emit("confirmed", passwordConfirmation);
      },
      onClickCancel(event) {
        event.preventDefault();
        const root = this.$refs.root;
        const $root = $$6(root);
        $root.modal("close");
        this.$emit("aborted");
        this.passwordConfirmation = "";
      },
      showPasswordConfirmModal() {
        CoreHome.Matomo.postEvent("PasswordConfirmation.altIdComponent", this.altIdConfirmComponent);
        this.slotHasContent = !this.$refs.content.matches(":empty");
        const root = this.$refs.root;
        const $root = $$6(root);
        const onEnter = (event) => {
          const keycode = event.keyCode ? event.keyCode : event.which;
          if (keycode === 13) {
            $root.modal("close");
            this.$emit("confirmed", this.passwordConfirmation);
            this.passwordConfirmation = "";
          }
        };
        $root.modal({
          dismissible: false,
          onOpenEnd: () => {
            const passwordField = `.modal.open #${this.passwordFieldId}`;
            $$6(passwordField).focus();
            $$6(passwordField).off("keypress").keypress(onEnter);
          },
          onCloseEnd: () => {
            this.$emit("update:modelValue", false);
          }
        }).modal("open");
      }
    },
    computed: {
      requiresPasswordConfirmation() {
        return !!CoreHome.Matomo.requiresPasswordConfirmation;
      },
      alternativeIdentityConfirmationComponent() {
        if (this.altIdConfirmComponent.plugin && this.altIdConfirmComponent.component) {
          return CoreHome.useExternalPluginComponent(
            this.altIdConfirmComponent.plugin,
            this.altIdConfirmComponent.component
          );
        }
        return null;
      }
    },
    watch: {
      modelValue(newValue) {
        if (newValue) {
          this.showPasswordConfirmModal();
        }
      }
    }
  });
  const _hoisted_1$8 = {
    class: "confirm-password-modal modal",
    ref: "root"
  };
  const _hoisted_2$8 = { class: "modal-content" };
  const _hoisted_3$7 = { class: "modal-text" };
  const _hoisted_4$7 = { ref: "content" };
  const _hoisted_5$6 = { key: 0 };
  const _hoisted_6$3 = { key: 1 };
  const _hoisted_7$3 = { key: 2 };
  const _hoisted_8$3 = { class: "password-confirmation-div" };
  const _hoisted_9$3 = { class: "modal-footer" };
  const _hoisted_10$3 = ["disabled"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _directive_auto_clear_password = vue.resolveDirective("auto-clear-password");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
      vue.createElementVNode("div", _hoisted_2$8, [
        vue.createElementVNode("div", _hoisted_3$7, [
          vue.createElementVNode("div", _hoisted_4$7, [
            vue.renderSlot(_ctx.$slots, "default")
          ], 512),
          !_ctx.requiresPasswordConfirmation && !_ctx.slotHasContent ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_5$6, vue.toDisplayString(_ctx.translate("UsersManager_ConfirmThisChange")), 1)) : vue.createCommentVNode("", true),
          _ctx.requiresPasswordConfirmation && !_ctx.slotHasContent ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_6$3, vue.toDisplayString(_ctx.translate("UsersManager_ConfirmWithReAuthentication")), 1)) : vue.createCommentVNode("", true),
          _ctx.requiresPasswordConfirmation && _ctx.slotHasContent ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$3, vue.toDisplayString(_ctx.translate("UsersManager_ConfirmWithReAuthentication")), 1)) : vue.createCommentVNode("", true)
        ]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_8$3, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            modelValue: _ctx.passwordConfirmation,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.passwordConfirmation = $event),
            uicontrol: "password",
            disabled: !_ctx.requiresPasswordConfirmation ? "disabled" : void 0,
            name: "currentUserPassword",
            id: _ctx.passwordFieldId,
            autocomplete: "off",
            "full-width": true,
            title: _ctx.translate("UsersManager_YourCurrentPassword")
          }, null, 8, ["modelValue", "disabled", "id", "title"]), [
            [_directive_auto_clear_password]
          ])
        ], 512), [
          [vue.vShow, _ctx.requiresPasswordConfirmation]
        ])
      ]),
      vue.createElementVNode("div", _hoisted_9$3, [
        !!_ctx.alternativeIdentityConfirmationComponent ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.alternativeIdentityConfirmationComponent), {
          key: 0,
          onConfirmed: _ctx.onConfirm
        }, null, 40, ["onConfirmed"])) : vue.createCommentVNode("", true),
        vue.createElementVNode("a", {
          href: "",
          class: "modal-action modal-close btn confirm-password-btn",
          disabled: _ctx.requiresPasswordConfirmation && !_ctx.passwordConfirmation ? "disabled" : void 0,
          onClick: _cache[1] || (_cache[1] = ($event) => _ctx.onClickConfirm($event))
        }, vue.toDisplayString(_ctx.translate("General_Confirm")), 9, _hoisted_10$3),
        vue.createElementVNode("a", {
          href: "",
          class: "modal-action modal-close modal-no btn-flat",
          onClick: _cache[2] || (_cache[2] = ($event) => _ctx.onClickCancel($event))
        }, vue.toDisplayString(_ctx.translate("General_Cancel")), 1)
      ])
    ], 512);
  }
  const PasswordConfirmation = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$8]]);
  const { $: $$5 } = window;
  const _sfc_main$8 = vue.defineComponent({
    props: {
      mode: String
    },
    components: {
      PasswordConfirmation,
      ActivityIndicator: CoreHome.ActivityIndicator,
      GroupedSettings
    },
    data() {
      return {
        isLoading: true,
        isSaving: {},
        showPasswordConfirmModal: false,
        settingsToSave: null,
        settingsPerPlugin: [],
        settingValues: {}
      };
    },
    created() {
      CoreHome.AjaxHelper.fetch({
        method: this.apiMethod
      }).then((settingsPerPlugin) => {
        this.isLoading = false;
        this.settingsPerPlugin = settingsPerPlugin;
        settingsPerPlugin.forEach((settings) => {
          settings.settings.forEach((setting) => {
            this.settingValues[`${settings.pluginName}.${setting.name}`] = setting.value;
          });
        });
        CoreHome.scrollToAnchorInUrl();
        this.addSectionsToTableOfContents();
      }).catch(() => {
        this.isLoading = false;
      });
    },
    computed: {
      apiMethod() {
        return this.mode === "admin" ? "CorePluginsAdmin.getSystemSettings" : "CorePluginsAdmin.getUserSettings";
      },
      saveApiMethod() {
        return this.mode === "admin" ? "CorePluginsAdmin.setSystemSettings" : "CorePluginsAdmin.setUserSettings";
      }
    },
    methods: {
      addSectionsToTableOfContents() {
        const $toc = $$5("#generalSettingsTOC");
        if (!$toc.length) {
          return;
        }
        const settingsPerPlugin = this.settingsPerPlugin;
        settingsPerPlugin.forEach((settingsForPlugin) => {
          const { pluginName, settings } = settingsForPlugin;
          if (!pluginName) {
            return;
          }
          if (pluginName === "CoreAdminHome" && settings) {
            settings.filter((s) => s.introduction).forEach((s) => {
              $toc.append(`<a href="#/${pluginName}PluginSettings">${s.introduction}</a> `);
            });
          } else {
            $toc.append(`<a href="#/${pluginName}">${pluginName.replace(/([A-Z])/g, " $1").trim()}</a> `);
          }
        });
      },
      confirmPassword(password) {
        this.showPasswordConfirmModal = false;
        this.save(this.settingsToSave, password);
      },
      saveSetting(requestedPlugin) {
        if (this.mode === "admin") {
          this.settingsToSave = requestedPlugin;
          this.showPasswordConfirmModal = true;
        } else {
          this.save(requestedPlugin);
        }
      },
      save(requestedPlugin, password) {
        const { saveApiMethod } = this;
        this.isSaving[requestedPlugin] = true;
        const settingValuesPayload = this.getValuesForPlugin(requestedPlugin);
        CoreHome.AjaxHelper.post(
          { method: saveApiMethod },
          { settingValues: settingValuesPayload, passwordConfirmation: password }
        ).then(() => {
          this.isSaving[requestedPlugin] = false;
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_PluginSettingsSaveSuccess"),
            id: "generalSettings",
            context: "success",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).catch(() => {
          this.isSaving[requestedPlugin] = false;
        });
        this.settingsToSave = null;
      },
      getValuesForPlugin(requestedPlugin) {
        const values2 = {};
        if (!values2[requestedPlugin]) {
          values2[requestedPlugin] = [];
        }
        Object.entries(this.settingValues).forEach(([key, value]) => {
          const [pluginName, settingName] = key.split(".");
          if (pluginName !== requestedPlugin) {
            return;
          }
          let postValue = value;
          if (postValue === false) {
            postValue = "0";
          } else if (postValue === true) {
            postValue = "1";
          }
          if (Array.isArray(postValue) && postValue.length === 0) {
            postValue = "__empty__";
          }
          values2[pluginName].push({
            name: settingName,
            value: postValue
          });
        });
        return values2;
      }
    }
  });
  const _hoisted_1$7 = {
    class: "pluginSettings",
    ref: "root"
  };
  const _hoisted_2$7 = ["id"];
  const _hoisted_3$6 = { class: "card-content" };
  const _hoisted_4$6 = ["id"];
  const _hoisted_5$5 = ["onClick", "disabled", "value"];
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_GroupedSettings = vue.resolveComponent("GroupedSettings");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.settingsPerPlugin, (settings) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          class: "card",
          id: `${settings.pluginName}PluginSettings`,
          key: `${settings.pluginName}PluginSettings`
        }, [
          vue.createElementVNode("div", _hoisted_3$6, [
            vue.createElementVNode("h2", {
              class: "card-title",
              id: settings.pluginName
            }, vue.toDisplayString(settings.title), 9, _hoisted_4$6),
            vue.createVNode(_component_GroupedSettings, {
              "group-name": settings.pluginName,
              settings: settings.settings,
              "all-setting-values": _ctx.settingValues,
              onChange: ($event) => _ctx.settingValues[`${settings.pluginName}.${$event.name}`] = $event.value
            }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"]),
            vue.createElementVNode("input", {
              type: "button",
              onClick: ($event) => _ctx.saveSetting(settings.pluginName),
              disabled: _ctx.isLoading,
              class: "pluginsSettingsSubmit btn",
              value: _ctx.translate("General_Save")
            }, null, 8, _hoisted_5$5),
            vue.createVNode(_component_ActivityIndicator, {
              loading: _ctx.isLoading || _ctx.isSaving[settings.pluginName]
            }, null, 8, ["loading"])
          ])
        ], 8, _hoisted_2$7);
      }), 128)),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModal,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.showPasswordConfirmModal = $event),
        onConfirmed: _ctx.confirmPassword
      }, null, 8, ["modelValue", "onConfirmed"])
    ], 512);
  }
  const PluginSettings = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$7]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$4 } = window;
  function getCurrentFilterOrigin(element) {
    return element.find(".origin a.active").data("filter-origin");
  }
  function getCurrentFilterStatus(element) {
    return element.find(".status a.active").data("filter-status");
  }
  function getMatchingNodes(filterOrigin, filterStatus) {
    let query = "#plugins tr";
    if (filterOrigin === "all") {
      query += "[data-filter-origin]";
    } else {
      query += `[data-filter-origin=${filterOrigin}]`;
    }
    if (filterStatus === "all") {
      query += "[data-filter-status]";
    } else {
      query += `[data-filter-status=${filterStatus}]`;
    }
    return $$4(query);
  }
  function updateNumberOfMatchingPluginsInFilter(element, selectorFilterToUpdate, filterOrigin, filterStatus) {
    const numMatchingNodes = getMatchingNodes(filterOrigin, filterStatus).length;
    const updatedCounterText = ` (${numMatchingNodes})`;
    element.find(`${selectorFilterToUpdate} .counter`).text(updatedCounterText);
  }
  function updateAllNumbersOfMatchingPluginsInFilter(element) {
    const filterOrigin = getCurrentFilterOrigin(element);
    const filterStatus = getCurrentFilterStatus(element);
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-status="all"]',
      filterOrigin,
      "all"
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-status="active"]',
      filterOrigin,
      "active"
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-status="inactive"]',
      filterOrigin,
      "inactive"
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-origin="all"]',
      "all",
      filterStatus
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-origin="core"]',
      "core",
      filterStatus
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-origin="official"]',
      "official",
      filterStatus
    );
    updateNumberOfMatchingPluginsInFilter(
      element,
      '[data-filter-origin="thirdparty"]',
      "thirdparty",
      filterStatus
    );
  }
  function filterPlugins(element) {
    const filterOrigin = getCurrentFilterOrigin(element);
    const filterStatus = getCurrentFilterStatus(element);
    const $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);
    $$4("#plugins tr[data-filter-origin][data-filter-status]").css("display", "none");
    $nodesToEnable.css("display", "table-row");
    updateAllNumbersOfMatchingPluginsInFilter(element);
  }
  function onClickStatus(element, event) {
    event.preventDefault();
    $$4(event.target).siblings().removeClass("active");
    $$4(event.target).addClass("active");
    filterPlugins(element);
  }
  function onClickOrigin(element, event) {
    event.preventDefault();
    $$4(event.target).siblings().removeClass("active");
    $$4(event.target).addClass("active");
    filterPlugins(element);
  }
  const PluginFilter = {
    mounted(el) {
      setTimeout(() => {
        updateAllNumbersOfMatchingPluginsInFilter($$4(el));
        $$4(el).find(".status").on("click", "a", onClickStatus.bind(null, $$4(el)));
        $$4(el).find(".origin").on("click", "a", onClickOrigin.bind(null, $$4(el)));
      });
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$3 } = window;
  function onClickUninstall(binding, event) {
    event.preventDefault();
    const link = $$3(event.target).attr("href");
    const pluginName = $$3(event.target).attr("data-plugin-name");
    if (!link || !pluginName) {
      return;
    }
    if (!binding.value.uninstallConfirmMessage) {
      binding.value.uninstallConfirmMessage = $$3("#uninstallPluginConfirm").text();
    }
    const messageToDisplay = (binding.value.uninstallConfirmMessage || "").replace("%s", pluginName);
    $$3("#uninstallPluginConfirm").text(messageToDisplay);
    CoreHome.Matomo.helper.modalConfirm("#confirmUninstallPlugin", {
      yes: () => {
        window.location.href = link;
      }
    });
  }
  function onDonateLinkClick(event) {
    event.preventDefault();
    const overlayId = $$3(event.target).data("overlay-id");
    CoreHome.Matomo.helper.modalConfirm(`#${overlayId}`, {});
  }
  const PluginManagement = {
    mounted(el, binding) {
      setTimeout(() => {
        binding.value.uninstallConfirmMessage = "";
        $$3(el).find(".uninstall").click(onClickUninstall.bind(null, binding));
        $$3(el).find(".plugin-donation-link").click(onDonateLinkClick);
      });
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$2 } = window;
  function onUploadPlugin(event) {
    event.preventDefault();
    CoreHome.Matomo.helper.modalConfirm("#installPluginByUpload", {});
  }
  function onSubmitPlugin(event) {
    const $zipFile = $$2("[name=pluginZip]");
    const fileName = $zipFile.val();
    if (!fileName || fileName.slice(-4) !== ".zip") {
      event.preventDefault();
      alert(CoreHome.translate("CorePluginsAdmin_NoZipFileSelected"));
    } else if ($zipFile.data("maxSize") > 0 && $zipFile[0].files[0].size > $zipFile.data("maxSize") * 1048576) {
      event.preventDefault();
      alert(CoreHome.translate("CorePluginsAdmin_FileExceedsUploadLimit"));
    }
  }
  const PluginUpload = {
    mounted() {
      setTimeout(() => {
        $$2(".uploadPlugin").click(onUploadPlugin);
        $$2("#uploadPluginForm").submit(onSubmitPlugin);
      });
    }
  };
  const _sfc_main$7 = vue.defineComponent({
    props: {
      saving: Boolean,
      value: String,
      disabled: Boolean
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator
    },
    emits: ["confirm"],
    methods: {
      onConfirm(event) {
        this.$emit("confirm", event);
      }
    }
  });
  const _hoisted_1$6 = {
    class: "matomo-save-button",
    style: { "display": "inline-block" }
  };
  const _hoisted_2$6 = ["disabled", "value"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, [
      vue.createElementVNode("input", {
        type: "button",
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onConfirm($event)),
        disabled: _ctx.saving || _ctx.disabled,
        class: "btn",
        value: _ctx.value ? _ctx.value : _ctx.translate("General_Save")
      }, null, 8, _hoisted_2$6),
      vue.createVNode(_component_ActivityIndicator, { loading: _ctx.saving }, null, 8, ["loading"])
    ]);
  }
  const SaveButton = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$6]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$1 } = window;
  const Form = {
    mounted(el) {
      setTimeout(() => {
        $$1(el).find("input[type=text]").keypress((e) => {
          const key = e.keyCode || e.which;
          if (key === 13) {
            $$1(el).find(".matomo-save-button input").triggerHandler("click");
          }
        });
      });
    }
  };
  const _sfc_main$6 = vue.defineComponent({
    components: { MatomoLoader: CoreHome.MatomoLoader },
    props: {
      disabled: {
        type: Boolean,
        required: false,
        default: false
      }
    },
    data() {
      return {
        paidPluginsToInstallAtOnce: [],
        installNonce: "",
        loading: false
      };
    },
    created() {
      this.fetchPluginsToInstallAtOnce();
    },
    watch: {
      disabled(newValue, oldValue) {
        if (newValue === false && oldValue === true) {
          this.fetchPluginsToInstallAtOnce();
        }
      }
    },
    methods: {
      onInstallAllPaidPlugins() {
        CoreHome.Matomo.helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce);
      },
      fetchPluginsToInstallAtOnce() {
        this.loading = true;
        if (CoreHome.Matomo.hasSuperUserAccess) {
          CoreHome.AjaxHelper.fetch({
            module: "Marketplace",
            action: "getPaidPluginsToInstallAtOnceParams"
          }).then((response) => {
            var _a2, _b;
            if (response) {
              this.paidPluginsToInstallAtOnce = (_a2 = response.paidPluginsToInstallAtOnce) != null ? _a2 : [];
              this.installNonce = (_b = response.installAllPluginsNonce) != null ? _b : "";
            }
            this.loading = false;
          });
        }
      }
    },
    computed: {
      installAllPaidPluginsLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "installAllPaidPlugins",
          nonce: this.installNonce
        }))}`;
      }
    }
  });
  const _hoisted_1$5 = { key: 0 };
  const _hoisted_2$5 = ["disabled"];
  const _hoisted_3$5 = {
    class: "ui-confirm",
    id: "installAllPaidPluginsAtOnce",
    ref: "installAllPaidPluginsAtOnce"
  };
  const _hoisted_4$5 = ["data-href", "value"];
  const _hoisted_5$4 = ["value"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return _ctx.paidPluginsToInstallAtOnce.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createElementVNode("button", {
        class: "btn installAllPaidPluginsAtOnceButton",
        onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.onInstallAllPaidPlugins(), ["prevent"])),
        disabled: _ctx.disabled || _ctx.loading
      }, [
        _ctx.loading ? (vue.openBlock(), vue.createBlock(_component_MatomoLoader, { key: 0 })) : vue.createCommentVNode("", true),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_InstallPurchasedPlugins")), 1)
      ], 8, _hoisted_2$5),
      vue.createElementVNode("div", _hoisted_3$5, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_InstallAllPurchasedPlugins")), 1),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Marketplace_InstallThesePlugins")), 1),
        vue.createElementVNode("ul", null, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.paidPluginsToInstallAtOnce, (pluginDisplayName) => {
            return vue.openBlock(), vue.createElementBlock("li", { key: pluginDisplayName }, vue.toDisplayString(pluginDisplayName), 1);
          }), 128))
        ]),
        vue.createElementVNode("p", null, [
          vue.createElementVNode("input", {
            role: "install",
            type: "button",
            "data-href": _ctx.installAllPaidPluginsLink,
            value: _ctx.translate(
              "Marketplace_InstallAllPurchasedPluginsAction",
              _ctx.paidPluginsToInstallAtOnce.length
            )
          }, null, 8, _hoisted_4$5),
          vue.createElementVNode("input", {
            role: "cancel",
            type: "button",
            value: _ctx.translate("General_Cancel")
          }, null, 8, _hoisted_5$4)
        ])
      ], 512)
    ])) : vue.createCommentVNode("", true);
  }
  const InstallAllPaidPluginsButton = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$5]]);
  const _sfc_main$5 = vue.defineComponent({
    props: {
      isMarketplaceEnabled: Boolean,
      isPluginUploadEnabled: Boolean,
      isPluginsAdminEnabled: Boolean
    },
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      InstallAllPaidPluginsButton
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      teaserExtendMatomoByPluginText() {
        const link = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "overview",
          sort: null,
          activated: null
        }))}`;
        return CoreHome.translate(
          "CorePluginsAdmin_TeaserExtendPiwikByPlugin",
          `<a href="${link}">`,
          "</a>",
          '<a href="#" class="uploadPlugin">',
          "</a>"
        );
      },
      changeLookByManageThemesText() {
        const link = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          action: "themes",
          activated: null
        }))}`;
        return CoreHome.translate(
          "CorePluginsAdmin_ChangeLookByManageThemes",
          `<a href="${link}">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$4 = ["innerHTML"];
  const _hoisted_2$4 = {
    key: 1,
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_3$4 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_4$4 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_InstallAllPaidPluginsButton = vue.resolveComponent("InstallAllPaidPluginsButton");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, [
          vue.createVNode(_component_EnrichedHeadline, null, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_PluginsManagement")), 1)
            ]),
            _: 1
          })
        ]),
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_PluginsExtendPiwik")) + " " + vue.toDisplayString(_ctx.translate("CorePluginsAdmin_OncePluginIsInstalledYouMayActivateHere")) + " ", 1),
          _ctx.isMarketplaceEnabled || _ctx.isPluginUploadEnabled ? (vue.openBlock(), vue.createElementBlock("span", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.teaserExtendMatomoByPluginText),
            style: { "margin-right": "3.5px" }
          }, null, 8, _hoisted_1$4)) : vue.createCommentVNode("", true),
          !_ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$4, [
            _hoisted_3$4,
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_DoMoreContactPiwikAdmins")), 1)
          ])) : vue.createCommentVNode("", true),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.changeLookByManageThemesText)
          }, null, 8, _hoisted_4$4)
        ])
      ])), [
        [_directive_content_intro]
      ]),
      _ctx.isMarketplaceEnabled ? (vue.openBlock(), vue.createBlock(_component_InstallAllPaidPluginsButton, { key: 0 })) : vue.createCommentVNode("", true)
    ], 64);
  }
  const PluginsIntro = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$4]]);
  const _sfc_main$4 = vue.defineComponent({
    props: {
      isMarketplaceEnabled: Boolean,
      otherUsersCount: Number,
      themeEnabled: Boolean,
      isPluginsAdminEnabled: Boolean
    },
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      teaserExtendByThemeText() {
        const query = CoreHome.MatomoUrl.stringify({ module: "Marketplace", action: "overview" });
        const hash = CoreHome.MatomoUrl.stringify({ pluginType: "themes" });
        const link = `?${query}#?${hash}`;
        return CoreHome.translate(
          "CorePluginsAdmin_TeaserExtendPiwikByTheme",
          `<a href="${link}">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$3 = ["innerHTML"];
  const _hoisted_2$3 = { key: 1 };
  const _hoisted_3$3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_4$3 = { key: 2 };
  const _hoisted_5$3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("h2", null, [
        vue.createVNode(_component_EnrichedHeadline, null, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ThemesManagement")), 1)
          ]),
          _: 1
        })
      ]),
      vue.createElementVNode("p", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ThemesDescription")) + " ", 1),
        _ctx.isMarketplaceEnabled ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          innerHTML: _ctx.$sanitize(_ctx.teaserExtendByThemeText)
        }, null, 8, _hoisted_1$3)) : vue.createCommentVNode("", true),
        _ctx.otherUsersCount > 0 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$3, [
          _hoisted_3$3,
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(
            "CorePluginsAdmin_InfoThemeIsUsedByOtherUsersAsWell",
            _ctx.otherUsersCount,
            _ctx.themeEnabled
          )), 1)
        ])) : vue.createCommentVNode("", true),
        !_ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$3, [
          _hoisted_5$3,
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_DoMoreContactPiwikAdmins")), 1)
        ])) : vue.createCommentVNode("", true)
      ])
    ])), [
      [_directive_content_intro]
    ]);
  }
  const ThemesIntro = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$3]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $ } = window;
  window.broadcast.addPopoverHandler("browsePluginDetail", (value) => {
    let pluginName = value;
    let activeTab = null;
    if (value.indexOf("!") !== -1) {
      activeTab = value.slice(value.indexOf("!") + 1);
      pluginName = value.slice(0, value.indexOf("!"));
    }
    if (CoreHome.MatomoUrl.urlParsed.value.module === "Marketplace" && CoreHome.MatomoUrl.urlParsed.value.action === "overview") {
      window.broadcast.propagateNewPopoverParameter("");
      CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
        showPlugin: pluginName,
        popover: null
      }));
      return;
    }
    let url = `module=Marketplace&action=pluginDetails&pluginName=${encodeURIComponent(pluginName)}`;
    if (activeTab) {
      url += `&activeTab=${encodeURIComponent(activeTab)}`;
    }
    window.Piwik_Popover.createPopupAndLoadUrl(url, "details");
  });
  function onClickPluginNameLink(binding, event) {
    let { pluginName } = binding.value;
    const { activePluginTab } = binding.value;
    event.preventDefault();
    if (activePluginTab) {
      pluginName += `!${activePluginTab}`;
    }
    window.broadcast.propagateNewPopoverParameter("browsePluginDetail", pluginName);
  }
  const PluginName = {
    mounted(element, binding) {
      const { pluginName } = binding.value;
      if (!pluginName) {
        return;
      }
      binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);
      $(element).on("click", binding.value.onClickHandler).attr("matomo-plugin-name", pluginName);
    },
    unmounted(element, binding) {
      $(element).off("click", binding.value.onClickHandler);
    }
  };
  const _sfc_main$3 = vue.defineComponent({
    props: {
      isTheme: Boolean,
      displayAdminLinks: Boolean,
      pluginsInfo: {
        type: Object,
        required: true
      },
      uninstallNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      activateNonce: {
        type: String,
        required: true
      },
      marketplacePluginNames: {
        type: Array,
        required: true
      },
      pluginNamesHavingSettings: {
        type: Array,
        required: true
      },
      title: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      PluginManagement,
      PluginFilter,
      ContentTable: CoreHome.ContentTable,
      PluginName
    },
    methods: {
      getPluginOrigin(plugin) {
        if (plugin.isCorePlugin) {
          return "core";
        }
        if (plugin.isOfficialPlugin) {
          return "official";
        }
        return "thirdparty";
      },
      getPluginDonateLink(pluginName, business) {
        return `https://www.paypal.com/cgi-bin/webscr?${CoreHome.MatomoUrl.stringify({
          cmd: "_donations",
          item_name: `Matomo Plugin ${pluginName}`,
          bn: "PP-DonationsBF:btn_donateCC_LG.gif:NonHosted",
          business
        })}`;
      },
      getUninstallLink(pluginName) {
        return `?${CoreHome.MatomoUrl.stringify({
          module: "CorePluginsAdmin",
          action: "uninstall",
          pluginName,
          nonce: this.uninstallNonce
        })}`;
      },
      isDefaultTheme(pluginName) {
        return this.isTheme && pluginName === "Morpheus";
      },
      getDeactivateLink(pluginName) {
        return `?${CoreHome.MatomoUrl.stringify({
          module: "CorePluginsAdmin",
          action: "deactivate",
          pluginName,
          nonce: this.deactivateNonce,
          redirectTo: "referrer"
        })}`;
      },
      getActivateLink(pluginName) {
        return `?${CoreHome.MatomoUrl.stringify({
          module: "CorePluginsAdmin",
          action: "activate",
          pluginName,
          nonce: this.activateNonce,
          redirectTo: "referrer"
        })}`;
      },
      isMatomoUrl(url) {
        try {
          const pluginHost = new URL(url).host;
          return this.matomoHosts.indexOf(pluginHost) !== -1;
        } catch (error) {
          return false;
        }
      }
    },
    computed: {
      pluginsToDisplay() {
        const pluginsInfo = this.pluginsInfo;
        return Object.fromEntries(
          Object.entries(pluginsInfo).filter(([, info]) => {
            if (this.isTheme) {
              return true;
            }
            const { alwaysActivated } = info;
            return typeof alwaysActivated !== "undefined" && alwaysActivated !== null && !alwaysActivated;
          })
        );
      },
      generalSettingsLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "CoreAdminHome",
          action: "generalSettings"
        }))}`;
      },
      matomoHosts() {
        return [
          "piwik.org",
          "www.piwik.org",
          "matomo.org",
          "www.matomo.org"
        ];
      },
      themeOverviewLink() {
        const query = CoreHome.MatomoUrl.stringify({ module: "Marketplace", action: "overview" });
        const hash = CoreHome.MatomoUrl.stringify({ pluginType: "themes" });
        return `?${query}#?${hash}`;
      },
      overviewLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "overview",
          sort: ""
        }))}`;
      },
      pluginsAlwaysActivated() {
        const pluginsInfo = this.pluginsInfo;
        return Object.entries(pluginsInfo).filter(([, plugin]) => plugin.alwaysActivated).map(([name2]) => name2).join(", ");
      }
    }
  });
  const _hoisted_1$2 = { class: "row pluginsFilter" };
  const _hoisted_2$2 = { class: "origin" };
  const _hoisted_3$2 = { style: { "margin-right": "3.5px" } };
  const _hoisted_4$2 = {
    "data-filter-origin": "all",
    href: "#",
    class: "active"
  };
  const _hoisted_5$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_6$2 = {
    "data-filter-origin": "core",
    href: "#"
  };
  const _hoisted_7$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_8$2 = {
    "data-filter-origin": "official",
    href: "#"
  };
  const _hoisted_9$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_10$2 = {
    "data-filter-origin": "thirdparty",
    href: "#"
  };
  const _hoisted_11$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_12$1 = { class: "status" };
  const _hoisted_13$1 = { style: { "margin-right": "3.5px" } };
  const _hoisted_14$1 = {
    "data-filter-status": "all",
    href: "#",
    class: "active"
  };
  const _hoisted_15$1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_16$1 = {
    "data-filter-status": "active",
    href: "#"
  };
  const _hoisted_17$1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_18$1 = {
    "data-filter-status": "inactive",
    href: "#"
  };
  const _hoisted_19$1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "counter" }, null, -1);
  const _hoisted_20$1 = {
    id: "confirmUninstallPlugin",
    class: "ui-confirm"
  };
  const _hoisted_21$1 = { id: "uninstallPluginConfirm" };
  const _hoisted_22$1 = ["value"];
  const _hoisted_23$1 = ["value"];
  const _hoisted_24 = { class: "status" };
  const _hoisted_25 = {
    key: 0,
    class: "action-links"
  };
  const _hoisted_26 = { id: "plugins" };
  const _hoisted_27 = ["data-filter-status", "data-filter-origin"];
  const _hoisted_28 = { class: "name" };
  const _hoisted_29 = ["name"];
  const _hoisted_30 = { key: 0 };
  const _hoisted_31 = { key: 1 };
  const _hoisted_32 = ["title"];
  const _hoisted_33 = { key: 2 };
  const _hoisted_34 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_35 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_36 = ["href"];
  const _hoisted_37 = { class: "desc" };
  const _hoisted_38 = { class: "plugin-desc-missingrequirements" };
  const _hoisted_39 = { key: 0 };
  const _hoisted_40 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_41 = { class: "plugin-desc-text" };
  const _hoisted_42 = {
    key: 0,
    class: "plugin-homepage"
  };
  const _hoisted_43 = ["href"];
  const _hoisted_44 = {
    key: 1,
    class: "plugin-donation"
  };
  const _hoisted_45 = ["data-overlay-id"];
  const _hoisted_46 = ["id", "title"];
  const _hoisted_47 = ["innerHTML"];
  const _hoisted_48 = { class: "donation-links" };
  const _hoisted_49 = ["href"];
  const _hoisted_50 = /* @__PURE__ */ vue.createElementVNode("img", {
    src: "plugins/CorePluginsAdmin/images/paypal_donate.png",
    height: "30"
  }, null, -1);
  const _hoisted_51 = [
    _hoisted_50
  ];
  const _hoisted_52 = {
    key: 1,
    class: "donation-link bitcoin"
  };
  const _hoisted_53 = /* @__PURE__ */ vue.createElementVNode("span", null, "Donate Bitcoins to:", -1);
  const _hoisted_54 = ["href"];
  const _hoisted_55 = ["value"];
  const _hoisted_56 = {
    key: 0,
    class: "plugin-license"
  };
  const _hoisted_57 = ["title", "href"];
  const _hoisted_58 = { key: 1 };
  const _hoisted_59 = {
    key: 1,
    class: "plugin-author"
  };
  const _hoisted_60 = ["title", "href"];
  const _hoisted_61 = { key: 1 };
  const _hoisted_62 = {
    key: 2,
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_63 = { key: 0 };
  const _hoisted_64 = { key: 0 };
  const _hoisted_65 = { key: 1 };
  const _hoisted_66 = { key: 0 };
  const _hoisted_67 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_68 = ["data-plugin-name", "href"];
  const _hoisted_69 = { key: 0 };
  const _hoisted_70 = { key: 0 };
  const _hoisted_71 = { key: 1 };
  const _hoisted_72 = ["href"];
  const _hoisted_73 = { key: 1 };
  const _hoisted_74 = ["href"];
  const _hoisted_75 = {
    key: 0,
    class: "tableActionBar"
  };
  const _hoisted_76 = ["href"];
  const _hoisted_77 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-add" }, null, -1);
  const _hoisted_78 = ["href"];
  const _hoisted_79 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-add" }, null, -1);
  const _hoisted_80 = { class: "footer-message" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_plugin_filter = vue.resolveDirective("plugin-filter");
    const _directive_plugin_name = vue.resolveDirective("plugin-name");
    const _directive_content_table = vue.resolveDirective("content-table");
    const _directive_plugin_management = vue.resolveDirective("plugin-management");
    return vue.withDirectives((vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.title,
      class: "pluginsManagement"
    }, {
      default: vue.withCtx(() => [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("p", _hoisted_1$2, [
          vue.createElementVNode("span", _hoisted_2$2, [
            vue.createElementVNode("strong", _hoisted_3$2, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Origin")), 1),
            vue.createElementVNode("a", _hoisted_4$2, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_All")), 1),
              _hoisted_5$2
            ]),
            vue.createTextVNode(" | "),
            vue.createElementVNode("a", _hoisted_6$2, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_OriginCore")), 1),
              _hoisted_7$2
            ]),
            vue.createTextVNode(" | "),
            vue.createElementVNode("a", _hoisted_8$2, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_OriginOfficial")), 1),
              _hoisted_9$2
            ]),
            vue.createTextVNode(" | "),
            vue.createElementVNode("a", _hoisted_10$2, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_OriginThirdParty")), 1),
              _hoisted_11$2
            ])
          ]),
          vue.createElementVNode("span", _hoisted_12$1, [
            vue.createElementVNode("strong", _hoisted_13$1, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Status")), 1),
            vue.createElementVNode("a", _hoisted_14$1, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_All")), 1),
              _hoisted_15$1
            ]),
            vue.createTextVNode(" | "),
            vue.createElementVNode("a", _hoisted_16$1, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Active")), 1),
              _hoisted_17$1
            ]),
            vue.createTextVNode(" | "),
            vue.createElementVNode("a", _hoisted_18$1, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Inactive")), 1),
              _hoisted_19$1
            ])
          ])
        ])), [
          [_directive_plugin_filter]
        ]),
        vue.createElementVNode("div", _hoisted_20$1, [
          vue.createElementVNode("h2", _hoisted_21$1, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_UninstallConfirm")), 1),
          vue.createElementVNode("input", {
            role: "yes",
            type: "button",
            value: _ctx.translate("General_Yes")
          }, null, 8, _hoisted_22$1),
          vue.createElementVNode("input", {
            role: "no",
            type: "button",
            value: _ctx.translate("General_No")
          }, null, 8, _hoisted_23$1)
        ]),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
          vue.createElementVNode("thead", null, [
            vue.createElementVNode("tr", null, [
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.isTheme ? _ctx.translate("CorePluginsAdmin_Theme") : _ctx.translate("General_Plugin")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Description")), 1),
              vue.createElementVNode("th", _hoisted_24, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Status")), 1),
              _ctx.displayAdminLinks ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_25, vue.toDisplayString(_ctx.translate("General_Action")), 1)) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("tbody", _hoisted_26, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginsToDisplay, (plugin, name2) => {
              var _a2, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k;
              return vue.openBlock(), vue.createElementBlock("tr", {
                key: name2,
                class: vue.normalizeClass(plugin.activated ? "active-plugin" : "inactive-plugin"),
                "data-filter-status": plugin.activated ? "active" : "inactive",
                "data-filter-origin": _ctx.getPluginOrigin(plugin)
              }, [
                vue.createElementVNode("td", _hoisted_28, [
                  vue.createElementVNode("a", { name: name2 }, null, 8, _hoisted_29),
                  !plugin.isCorePlugin && _ctx.marketplacePluginNames.indexOf(name2) !== -1 ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_30, [
                    vue.createTextVNode(vue.toDisplayString(name2), 1)
                  ])), [
                    [_directive_plugin_name, { pluginName: name2 }]
                  ]) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_31, vue.toDisplayString(name2), 1)),
                  vue.createElementVNode("span", {
                    class: "plugin-version",
                    title: plugin.isCorePlugin ? _ctx.translate("CorePluginsAdmin_CorePluginTooltip") : void 0
                  }, " (" + vue.toDisplayString(plugin.isCorePlugin ? _ctx.translate("CorePluginsAdmin_OriginCore") : plugin.info.version) + ") ", 9, _hoisted_32),
                  _ctx.pluginNamesHavingSettings.indexOf(name2) !== -1 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_33, [
                    _hoisted_34,
                    _hoisted_35,
                    vue.createElementVNode("a", {
                      href: `${_ctx.generalSettingsLink}#/${name2}`,
                      class: "settingsLink"
                    }, vue.toDisplayString(_ctx.translate("General_Settings")), 9, _hoisted_36)
                  ])) : vue.createCommentVNode("", true)
                ]),
                vue.createElementVNode("td", _hoisted_37, [
                  vue.createElementVNode("div", _hoisted_38, [
                    plugin.missingRequirements ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_39, [
                      vue.createTextVNode(vue.toDisplayString(plugin.missingRequirements) + " ", 1),
                      _hoisted_40
                    ])) : vue.createCommentVNode("", true)
                  ]),
                  vue.createElementVNode("div", _hoisted_41, [
                    vue.createTextVNode(vue.toDisplayString(plugin.info.description.replaceAll("\n", "<br/>")) + " ", 1),
                    ((_a2 = plugin.info) == null ? void 0 : _a2.homepage) && !_ctx.isMatomoUrl((_b = plugin.info) == null ? void 0 : _b.homepage) ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_42, [
                      vue.createElementVNode("a", {
                        target: "_blank",
                        rel: "noreferrer noopener",
                        href: plugin.info.homepage
                      }, " (" + vue.toDisplayString(_ctx.translate("CorePluginsAdmin_PluginHomepage").replaceAll(" ", " ")) + ") ", 9, _hoisted_43)
                    ])) : vue.createCommentVNode("", true),
                    ((_d = (_c = plugin.info) == null ? void 0 : _c.donate) == null ? void 0 : _d.length) ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_44, [
                      vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_LikeThisPlugin")) + " ", 1),
                      vue.createElementVNode("a", {
                        onClick: _cache[0] || (_cache[0] = vue.withModifiers(() => {
                        }, ["prevent"])),
                        class: "plugin-donation-link",
                        "data-overlay-id": `overlay-${name2}`
                      }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ConsiderDonating")), 9, _hoisted_45),
                      vue.createElementVNode("div", {
                        id: `overlay-${name2}`,
                        class: "donation-overlay ui-confirm",
                        title: _ctx.translate("CorePluginsAdmin_LikeThisPlugin")
                      }, [
                        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_CommunityContributedPlugin")), 1),
                        vue.createElementVNode("p", {
                          innerHTML: _ctx.$sanitize(_ctx.translate(
                            "CorePluginsAdmin_ConsiderDonatingCreatorOf",
                            `<b>${name2}</b>`
                          ))
                        }, null, 8, _hoisted_47),
                        vue.createElementVNode("div", _hoisted_48, [
                          ((_f = (_e = plugin.info) == null ? void 0 : _e.donate) == null ? void 0 : _f.paypal) ? (vue.openBlock(), vue.createElementBlock("a", {
                            key: 0,
                            class: "donation-link paypal",
                            target: "_blank",
                            rel: "noreferrer noopener",
                            href: _ctx.getPluginDonateLink(name2, plugin.info.donate.paypal)
                          }, _hoisted_51, 8, _hoisted_49)) : vue.createCommentVNode("", true),
                          ((_h = (_g = plugin.info) == null ? void 0 : _g.donate) == null ? void 0 : _h.bitcoin) ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_52, [
                            _hoisted_53,
                            vue.createElementVNode("a", {
                              href: `bitcoin:${encodeURIComponent(plugin.info.donate.bitcoin)}`
                            }, vue.toDisplayString(plugin.info.donate.bitcoin), 9, _hoisted_54)
                          ])) : vue.createCommentVNode("", true)
                        ]),
                        vue.createElementVNode("input", {
                          role: "no",
                          type: "button",
                          value: _ctx.translate("General_Close")
                        }, null, 8, _hoisted_55)
                      ], 8, _hoisted_46)
                    ])) : vue.createCommentVNode("", true)
                  ]),
                  ((_i = plugin.info) == null ? void 0 : _i.license) ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_56, [
                    ((_j = plugin.info) == null ? void 0 : _j.license_file) ? (vue.openBlock(), vue.createElementBlock("a", {
                      key: 0,
                      title: _ctx.translate("CorePluginsAdmin_LicenseHomepage"),
                      rel: "noreferrer noopener",
                      target: "_blank",
                      href: `index.php?module=CorePluginsAdmin&action=showLicense&pluginName=${name2}`
                    }, vue.toDisplayString(plugin.info.license), 9, _hoisted_57)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_58, vue.toDisplayString(plugin.info.license), 1))
                  ])) : vue.createCommentVNode("", true),
                  ((_k = plugin.info) == null ? void 0 : _k.authors) ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_59, [
                    vue.createTextVNode(" By "),
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(plugin.info.authors.filter((a) => a.name), (author, index) => {
                      return vue.openBlock(), vue.createElementBlock("span", { key: index }, [
                        author.homepage ? (vue.openBlock(), vue.createElementBlock("a", {
                          key: 0,
                          title: _ctx.translate("CorePluginsAdmin_AuthorHomepage"),
                          href: author.homepage,
                          rel: "noreferrer noopener",
                          target: "_blank"
                        }, vue.toDisplayString(author.name), 9, _hoisted_60)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_61, vue.toDisplayString(author.name), 1)),
                        plugin.info.authors.length - 1 > index ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_62, ",")) : vue.createCommentVNode("", true)
                      ]);
                    }), 128)),
                    vue.createTextVNode(". ")
                  ])) : vue.createCommentVNode("", true)
                ]),
                vue.createElementVNode("td", {
                  class: "status",
                  style: vue.normalizeStyle({ "border-left-width": _ctx.isDefaultTheme(name2) ? "0" : void 0 })
                }, [
                  !_ctx.isDefaultTheme(name2) ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_63, [
                    plugin.activated ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_64, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Active")), 1)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_65, [
                      vue.createTextVNode(vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Inactive")) + " ", 1),
                      plugin.uninstallable && _ctx.displayAdminLinks ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_66, [
                        _hoisted_67,
                        vue.createTextVNode(" - "),
                        vue.createElementVNode("a", {
                          "data-plugin-name": name2,
                          class: "uninstall",
                          href: _ctx.getUninstallLink(name2)
                        }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ActionUninstall")), 9, _hoisted_68)
                      ])) : vue.createCommentVNode("", true)
                    ]))
                  ])) : vue.createCommentVNode("", true)
                ], 4),
                _ctx.displayAdminLinks ? (vue.openBlock(), vue.createElementBlock("td", {
                  key: 0,
                  class: "togl action-links",
                  style: vue.normalizeStyle({ "border-left-width": _ctx.isDefaultTheme(name2) ? 0 : void 0 })
                }, [
                  !_ctx.isDefaultTheme(name2) ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_69, [
                    plugin.invalid && plugin.alwaysActivated ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_70, "-")) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_71, [
                      plugin.activated ? (vue.openBlock(), vue.createElementBlock("a", {
                        key: 0,
                        href: _ctx.getDeactivateLink(name2)
                      }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Deactivate")), 9, _hoisted_72)) : plugin.missingRequirements ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_73, "-")) : (vue.openBlock(), vue.createElementBlock("a", {
                        key: 2,
                        href: _ctx.getActivateLink(name2)
                      }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Activate")), 9, _hoisted_74))
                    ]))
                  ])) : vue.createCommentVNode("", true)
                ], 4)) : vue.createCommentVNode("", true)
              ], 10, _hoisted_27);
            }), 128))
          ])
        ])), [
          [_directive_content_table]
        ]),
        _ctx.displayAdminLinks ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_75, [
          _ctx.isTheme ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            href: _ctx.themeOverviewLink
          }, [
            _hoisted_77,
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CorePluginsAdmin_InstallNewThemes")), 1)
          ], 8, _hoisted_76)) : (vue.openBlock(), vue.createElementBlock("a", {
            key: 1,
            href: _ctx.overviewLink
          }, [
            _hoisted_79,
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CorePluginsAdmin_InstallNewPlugins")), 1)
          ], 8, _hoisted_78))
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_80, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_AlwaysActivatedPluginsList", _ctx.pluginsAlwaysActivated)), 1)
      ]),
      _: 1
    }, 8, ["content-title"])), [
      [_directive_plugin_management, {}]
    ]);
  }
  const PluginsTable = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$2]]);
  const MissingReqsNotice = CoreHome.useExternalPluginComponent("Marketplace", "MissingReqsNotice");
  const _sfc_main$2 = vue.defineComponent({
    props: {
      pluginsHavingUpdate: {
        type: Object,
        required: true
      },
      pluginUpdateNonces: {
        type: Object,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      isMultiServerEnvironment: Boolean,
      isPluginsAdminEnabled: Boolean
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      MissingReqsNotice
    },
    directives: {
      ContentTable: CoreHome.ContentTable,
      PluginName
    },
    data() {
      return {
        isUpdating: false,
        isPluginDownloadLinkClicked: false,
        pluginsSelected: {}
      };
    },
    computed: {
      isUpdateLinkDisabled() {
        return this.isUpdating || !Object.keys(this.pluginsSelected).length || !Object.values(this.pluginsSelected).some((s) => !!s);
      }
    },
    methods: {
      selectAll(checked) {
        const plugins = this.pluginsHavingUpdate;
        Object.entries(plugins).forEach(([name2, plugin]) => {
          if (plugin.isDownloadable !== null && typeof plugin.isDownloadable !== "undefined" && !plugin.isDownloadable) {
            return;
          }
          this.pluginsSelected[name2] = checked;
        });
      },
      downloadPluginLink(plugin) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "download",
          pluginName: plugin.name,
          nonce: this.pluginUpdateNonces[plugin.name]
        }))}`;
      },
      updatePluginLink(plugin) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "updatePlugin",
          pluginName: plugin.name,
          nonce: this.updateNonce
        }))}`;
      },
      updateSelectedPlugins() {
        this.isUpdating = true;
        const pluginsToUpdate = Object.entries(this.pluginsSelected).filter(([, selected]) => selected).map(([name2]) => name2);
        CoreHome.MatomoUrl.updateUrl(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "Marketplace",
          action: "updatePlugin",
          nonce: this.updateNonce,
          pluginName: pluginsToUpdate.join(",")
        }));
      }
    }
  });
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = { key: 0 };
  const _hoisted_3$1 = { class: "checkbox-container" };
  const _hoisted_4$1 = /* @__PURE__ */ vue.createElementVNode("span", null, null, -1);
  const _hoisted_5$1 = { class: "num" };
  const _hoisted_6$1 = { class: "status" };
  const _hoisted_7$1 = {
    key: 1,
    class: "action-links"
  };
  const _hoisted_8$1 = { id: "plugins" };
  const _hoisted_9$1 = {
    key: 0,
    class: "select-cell"
  };
  const _hoisted_10$1 = { class: "checkbox-container" };
  const _hoisted_11$1 = ["id", "disabled", "onUpdate:modelValue"];
  const _hoisted_12 = /* @__PURE__ */ vue.createElementVNode("span", null, null, -1);
  const _hoisted_13 = { class: "name" };
  const _hoisted_14 = { class: "vers" };
  const _hoisted_15 = ["href", "title"];
  const _hoisted_16 = { key: 1 };
  const _hoisted_17 = { class: "desc" };
  const _hoisted_18 = { class: "status" };
  const _hoisted_19 = {
    key: 1,
    class: "togl action-links"
  };
  const _hoisted_20 = ["title"];
  const _hoisted_21 = ["href"];
  const _hoisted_22 = ["href"];
  const _hoisted_23 = { key: 3 };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MissingReqsNotice = vue.resolveComponent("MissingReqsNotice");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_plugin_name = vue.resolveDirective("plugin-name");
    const _directive_content_table = vue.resolveDirective("content-table");
    return Object.keys(_ctx.pluginsHavingUpdate).length ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      key: 0,
      "content-title": _ctx.translate(
        "CorePluginsAdmin_NUpdatesAvailable",
        Object.keys(_ctx.pluginsHavingUpdate).length
      )
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_InfoPluginUpdateIsRecommended")), 1),
        _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
          vue.createElementVNode("a", {
            id: "update-selected-plugins",
            onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.updateSelectedPlugins(), ["prevent"])),
            class: vue.normalizeClass({ btn: true, disabled: _ctx.isUpdateLinkDisabled })
          }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_UpdateSelected")), 3)
        ])) : vue.createCommentVNode("", true),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
          vue.createElementVNode("thead", null, [
            vue.createElementVNode("tr", null, [
              _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_2$1, [
                vue.createElementVNode("span", _hoisted_3$1, [
                  vue.createElementVNode("label", null, [
                    vue.createElementVNode("input", {
                      type: "checkbox",
                      id: "select-plugin-all",
                      onChange: _cache[1] || (_cache[1] = ($event) => _ctx.selectAll($event.target.checked))
                    }, null, 32),
                    _hoisted_4$1
                  ])
                ])
              ])) : vue.createCommentVNode("", true),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Plugin")), 1),
              vue.createElementVNode("th", _hoisted_5$1, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Version")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Description")), 1),
              vue.createElementVNode("th", _hoisted_6$1, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Status")), 1),
              _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_7$1, vue.toDisplayString(_ctx.translate("General_Action")), 1)) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("tbody", _hoisted_8$1, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginsHavingUpdate, (plugin, name2) => {
              var _a2;
              return vue.openBlock(), vue.createElementBlock("tr", {
                key: name2,
                class: vue.normalizeClass(plugin.isActivated ? "active-plugin" : "inactive-plugin")
              }, [
                _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_9$1, [
                  vue.createElementVNode("span", _hoisted_10$1, [
                    vue.createElementVNode("label", null, [
                      vue.withDirectives(vue.createElementVNode("input", {
                        type: "checkbox",
                        id: `select-plugin-${plugin.name}`,
                        disabled: typeof plugin.isDownloadable !== "undefined" && plugin.isDownloadable !== null && !plugin.isDownloadable,
                        "onUpdate:modelValue": ($event) => _ctx.pluginsSelected[name2] = $event
                      }, null, 8, _hoisted_11$1), [
                        [vue.vModelCheckbox, _ctx.pluginsSelected[name2]]
                      ]),
                      _hoisted_12
                    ])
                  ])
                ])) : vue.createCommentVNode("", true),
                vue.createElementVNode("td", _hoisted_13, [
                  vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
                    onClick: _cache[2] || (_cache[2] = vue.withModifiers(() => {
                    }, ["prevent"])),
                    class: "plugin-details"
                  }, [
                    vue.createTextVNode(vue.toDisplayString(plugin.name), 1)
                  ])), [
                    [_directive_plugin_name, { pluginName: plugin.name }]
                  ])
                ]),
                vue.createElementVNode("td", _hoisted_14, [
                  ((_a2 = plugin.changelog) == null ? void 0 : _a2.url) ? (vue.openBlock(), vue.createElementBlock("a", {
                    key: 0,
                    href: plugin.changelog.url,
                    title: _ctx.translate("CorePluginsAdmin_Changelog"),
                    target: "_blank",
                    rel: "noreferrer noopener"
                  }, vue.toDisplayString(plugin.currentVersion) + " => " + vue.toDisplayString(plugin.latestVersion), 9, _hoisted_15)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_16, vue.toDisplayString(plugin.currentVersion) + " => " + vue.toDisplayString(plugin.latestVersion), 1))
                ]),
                vue.createElementVNode("td", _hoisted_17, [
                  vue.createTextVNode(vue.toDisplayString(plugin.description) + " ", 1),
                  vue.createVNode(_component_MissingReqsNotice, { plugin }, null, 8, ["plugin"])
                ]),
                vue.createElementVNode("td", _hoisted_18, vue.toDisplayString(plugin.isActivated ? _ctx.translate("CorePluginsAdmin_Active") : _ctx.translate("CorePluginsAdmin_Inactive")), 1),
                _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_19, [
                  typeof plugin.isDownloadable !== "undefined" && plugin.isDownloadable !== null && !plugin.isDownloadable ? (vue.openBlock(), vue.createElementBlock("span", {
                    key: 0,
                    title: `${_ctx.translate("CorePluginsAdmin_PluginNotDownloadable")} ${plugin.isPaid ? _ctx.translate("CorePluginsAdmin_PluginNotDownloadablePaidReason") : ""}`
                  }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_NotDownloadable")), 9, _hoisted_20)) : _ctx.isMultiServerEnvironment ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
                    key: 1,
                    onClick: _cache[3] || (_cache[3] = ($event) => _ctx.isPluginDownloadLinkClicked = true),
                    href: _ctx.downloadPluginLink(plugin)
                  }, vue.toDisplayString(_ctx.translate("General_Download")), 9, _hoisted_21)), [
                    [vue.vShow, !_ctx.isPluginDownloadLinkClicked]
                  ]) : plugin.missingRequirements.length === 0 ? (vue.openBlock(), vue.createElementBlock("a", {
                    key: 2,
                    href: _ctx.updatePluginLink(plugin)
                  }, vue.toDisplayString(_ctx.translate("CoreUpdater_UpdateTitle")), 9, _hoisted_22)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_23, "-"))
                ])) : vue.createCommentVNode("", true)
              ], 2);
            }), 128))
          ])
        ])), [
          [_directive_content_table]
        ])
      ]),
      _: 1
    }, 8, ["content-title"])) : vue.createCommentVNode("", true);
  }
  const PluginsTableWithUpdates = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$1]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      isPluginUploadEnabled: Boolean,
      uploadLimit: [String, Number],
      installNonce: String
    },
    components: {
      Field
    },
    directives: {
      PluginUpload,
      AutoClearPassword: CoreHome.AutoClearPassword
    },
    data() {
      return {
        confirmPassword: ""
      };
    },
    computed: {
      uploadPluginAction() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "CorePluginsAdmin",
          action: "uploadPlugin",
          nonce: this.installNonce
        }))}`;
      }
    }
  });
  const _hoisted_1 = {
    class: "ui-confirm",
    id: "installPluginByUpload"
  };
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = { class: "description" };
  const _hoisted_4 = ["action"];
  const _hoisted_5 = ["data-max-size"];
  const _hoisted_6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7 = ["value"];
  const _hoisted_8 = { key: 1 };
  const _hoisted_9 = ["innerHTML"];
  const _hoisted_10 = /* @__PURE__ */ vue.createElementVNode("pre", null, "[General]\n  enable_plugin_upload = 1", -1);
  const _hoisted_11 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _directive_auto_clear_password = vue.resolveDirective("auto-clear-password");
    const _directive_plugin_upload = vue.resolveDirective("plugin-upload");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_TeaserExtendPiwikByUpload")), 1),
      _ctx.isPluginUploadEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
        vue.createElementVNode("p", _hoisted_3, vue.toDisplayString(_ctx.translate("Marketplace_AllowedUploadFormats")), 1),
        vue.createElementVNode("form", {
          enctype: "multipart/form-data",
          method: "post",
          id: "uploadPluginForm",
          action: _ctx.uploadPluginAction
        }, [
          vue.createElementVNode("input", {
            type: "file",
            name: "pluginZip",
            "data-max-size": _ctx.uploadLimit
          }, null, 8, _hoisted_5),
          _hoisted_6,
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "password",
            name: "confirmPassword",
            autocomplete: "off",
            title: _ctx.translate("Login_ConfirmPasswordToContinue"),
            modelValue: _ctx.confirmPassword,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.confirmPassword = $event)
          }, null, 8, ["title", "modelValue"]), [
            [_directive_auto_clear_password]
          ]),
          vue.createElementVNode("input", {
            class: "startUpload btn",
            type: "submit",
            value: _ctx.translate("Marketplace_UploadZipFile")
          }, null, 8, _hoisted_7)
        ], 8, _hoisted_4)
      ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_8, [
        vue.createElementVNode("p", {
          class: "description",
          innerHTML: _ctx.$sanitize(_ctx.translate("Marketplace_PluginUploadDisabled"))
        }, null, 8, _hoisted_9),
        _hoisted_10,
        vue.createElementVNode("input", {
          role: "yes",
          type: "button",
          value: _ctx.translate("General_Ok")
        }, null, 8, _hoisted_11)
      ]))
    ])), [
      [_directive_plugin_upload]
    ]);
  }
  const UploadPluginDialog = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render]]);
  const _sfc_main = vue.defineComponent({
    props: {
      selector: {
        type: String,
        required: true
      },
      observerSelector: {
        type: String,
        default: "#secondNavBar"
      }
    },
    data() {
      return {
        observer: null,
        pending: false
      };
    },
    methods: {
      fetchAndUpdate(el) {
        return __async(this, null, function* () {
          yield CoreHome.AjaxHelper.fetch({
            module: "API",
            method: "CorePluginsAdmin.getNumberOfPluginUpdates"
          }).then((response) => {
            var _a2, _b;
            const count = response.value || 0;
            if (count) {
              const originalText = (_b = (_a2 = el.textContent) == null ? void 0 : _a2.trim()) != null ? _b : "";
              el.textContent = `${originalText} (${count})`;
            }
          }).catch((error) => {
            console.error("Failed to fetch number of plugin updates:", error.message || error);
          });
        });
      },
      maybeUpdate() {
        const el = document.querySelector(this.selector);
        if (!el) return;
        if (this.observer) {
          this.observer.disconnect();
          this.observer = null;
        }
        if (this.pending) return;
        this.pending = true;
        this.fetchAndUpdate(el).finally(() => {
          this.pending = false;
        });
      }
    },
    mounted() {
      const root = document.querySelector(this.observerSelector);
      if (!root) {
        return;
      }
      this.maybeUpdate();
      this.observer = new MutationObserver(() => this.maybeUpdate());
      this.observer.observe(root, { childList: true, subtree: true });
    },
    beforeUnmount() {
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }
    },
    render() {
      return null;
    }
  });
  exports2.Field = Field;
  exports2.FieldPassword = FieldPassword;
  exports2.Form = Form;
  exports2.FormField = FormField;
  exports2.GroupedSettings = GroupedSettings;
  exports2.InstallAllPaidPluginsButton = InstallAllPaidPluginsButton;
  exports2.PasswordConfirmation = PasswordConfirmation;
  exports2.PluginFilter = PluginFilter;
  exports2.PluginManagement = PluginManagement;
  exports2.PluginName = PluginName;
  exports2.PluginSettings = PluginSettings;
  exports2.PluginUpload = PluginUpload;
  exports2.PluginsIntro = PluginsIntro;
  exports2.PluginsTable = PluginsTable;
  exports2.PluginsTableWithUpdates = PluginsTableWithUpdates;
  exports2.PluginsUpdateCount = _sfc_main;
  exports2.SaveButton = SaveButton;
  exports2.ThemesIntro = ThemesIntro;
  exports2.UploadPluginDialog = UploadPluginDialog;
  exports2.expressions = math;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
