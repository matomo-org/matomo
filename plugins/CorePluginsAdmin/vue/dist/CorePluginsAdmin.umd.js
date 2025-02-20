(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["CorePluginsAdmin"] = factory(require("CoreHome"), require("vue"));
	else
		root["CorePluginsAdmin"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/CorePluginsAdmin/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "4788":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// Map the characters to escape to their escaped values. The list is derived
// from http://www.cespedes.org/blog/85/how-to-escape-latex-special-characters

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

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
  "\u2013": "\\--",
  "\u2014": "\\---",
  " ": "~",
  "\t": "\\qquad{}",
  "\r\n": "\\newline{}",
  "\n": "\\newline{}"
};

var defaultEscapeMapFn = function defaultEscapeMapFn(defaultEscapes, formatEscapes) {
  return _extends({}, defaultEscapes, formatEscapes);
};

/**
 * Escape a string to be used in LaTeX documents.
 * @param {string} str the string to be escaped.
 * @param {boolean} params.preserveFormatting whether formatting escapes should
 *  be performed (default: false).
 * @param {function} params.escapeMapFn the function to modify the escape maps.
 * @return {string} the escaped string, ready to be used in LaTeX.
 */
module.exports = function (str) {
  var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
      _ref$preserveFormatti = _ref.preserveFormatting,
      preserveFormatting = _ref$preserveFormatti === undefined ? false : _ref$preserveFormatti,
      _ref$escapeMapFn = _ref.escapeMapFn,
      escapeMapFn = _ref$escapeMapFn === undefined ? defaultEscapeMapFn : _ref$escapeMapFn;

  var runningStr = String(str);
  var result = "";

  var escapes = escapeMapFn(_extends({}, defaultEscapes), preserveFormatting ? _extends({}, formatEscapes) : {});
  var escapeKeys = Object.keys(escapes); // as it is reused later on

  // Algorithm: Go through the string character by character, if it matches
  // with one of the special characters then we'll replace it with the escaped
  // version.

  var _loop = function _loop() {
    var specialCharFound = false;
    escapeKeys.forEach(function (key, index) {
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

/***/ }),

/***/ "7634":
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/**
 * typed-function
 *
 * Type checking for JavaScript functions
 *
 * https://github.com/josdejong/typed-function
 */


(function (root, factory) {
  if (true) {
    // AMD. Register as an anonymous module.
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
}(this, function () {

  function ok () {
    return true;
  }

  function notOk () {
    return false;
  }

  function undef () {
    return undefined;
  }

  /**
   * @typedef {{
   *   params: Param[],
   *   fn: function
   * }} Signature
   *
   * @typedef {{
   *   types: Type[],
   *   restParam: boolean
   * }} Param
   *
   * @typedef {{
   *   name: string,
   *   typeIndex: number,
   *   test: function,
   *   conversion?: ConversionDef,
   *   conversionIndex: number,
   * }} Type
   *
   * @typedef {{
   *   from: string,
   *   to: string,
   *   convert: function (*) : *
   * }} ConversionDef
   *
   * @typedef {{
   *   name: string,
   *   test: function(*) : boolean
   * }} TypeDef
   */

  // create a new instance of typed-function
  function create () {
    // data type tests
    var _types = [
      { name: 'number',    test: function (x) { return typeof x === 'number' } },
      { name: 'string',    test: function (x) { return typeof x === 'string' } },
      { name: 'boolean',   test: function (x) { return typeof x === 'boolean' } },
      { name: 'Function',  test: function (x) { return typeof x === 'function'} },
      { name: 'Array',     test: Array.isArray },
      { name: 'Date',      test: function (x) { return x instanceof Date } },
      { name: 'RegExp',    test: function (x) { return x instanceof RegExp } },
      { name: 'Object',    test: function (x) {
        return typeof x === 'object' && x !== null && x.constructor === Object
      }},
      { name: 'null',      test: function (x) { return x === null } },
      { name: 'undefined', test: function (x) { return x === undefined } }
    ];

    var anyType = {
      name: 'any',
      test: ok
    }

    // types which need to be ignored
    var _ignore = [];

    // type conversions
    var _conversions = [];

    // This is a temporary object, will be replaced with a typed function at the end
    var typed = {
      types: _types,
      conversions: _conversions,
      ignore: _ignore
    };

    /**
     * Find the test function for a type
     * @param {String} typeName
     * @return {TypeDef} Returns the type definition when found,
     *                    Throws a TypeError otherwise
     */
    function findTypeByName (typeName) {
      var entry = findInArray(typed.types, function (entry) {
        return entry.name === typeName;
      });

      if (entry) {
        return entry;
      }

      if (typeName === 'any') { // special baked-in case 'any'
        return anyType;
      }

      var hint = findInArray(typed.types, function (entry) {
        return entry.name.toLowerCase() === typeName.toLowerCase();
      });

      throw new TypeError('Unknown type "' + typeName + '"' +
          (hint ? ('. Did you mean "' + hint.name + '"?') : ''));
    }

    /**
     * Find the index of a type definition. Handles special case 'any'
     * @param {TypeDef} type
     * @return {number}
     */
    function findTypeIndex(type) {
      if (type === anyType) {
        return 999;
      }

      return typed.types.indexOf(type);
    }

    /**
     * Find a type that matches a value.
     * @param {*} value
     * @return {string} Returns the name of the first type for which
     *                  the type test matches the value.
     */
    function findTypeName(value) {
      var entry = findInArray(typed.types, function (entry) {
        return entry.test(value);
      });

      if (entry) {
        return entry.name;
      }

      throw new TypeError('Value has unknown type. Value: ' + value);
    }

    /**
     * Find a specific signature from a (composed) typed function, for example:
     *
     *   typed.find(fn, ['number', 'string'])
     *   typed.find(fn, 'number, string')
     *
     * Function find only only works for exact matches.
     *
     * @param {Function} fn                   A typed-function
     * @param {string | string[]} signature   Signature to be found, can be
     *                                        an array or a comma separated string.
     * @return {Function}                     Returns the matching signature, or
     *                                        throws an error when no signature
     *                                        is found.
     */
    function find (fn, signature) {
      if (!fn.signatures) {
        throw new TypeError('Function is no typed-function');
      }

      // normalize input
      var arr;
      if (typeof signature === 'string') {
        arr = signature.split(',');
        for (var i = 0; i < arr.length; i++) {
          arr[i] = arr[i].trim();
        }
      }
      else if (Array.isArray(signature)) {
        arr = signature;
      }
      else {
        throw new TypeError('String array or a comma separated string expected');
      }

      var str = arr.join(',');

      // find an exact match
      var match = fn.signatures[str];
      if (match) {
        return match;
      }

      // TODO: extend find to match non-exact signatures

      throw new TypeError('Signature not found (signature: ' + (fn.name || 'unnamed') + '(' + arr.join(', ') + '))');
    }

    /**
     * Convert a given value to another data type.
     * @param {*} value
     * @param {string} type
     */
    function convert (value, type) {
      var from = findTypeName(value);

      // check conversion is needed
      if (type === from) {
        return value;
      }

      for (var i = 0; i < typed.conversions.length; i++) {
        var conversion = typed.conversions[i];
        if (conversion.from === from && conversion.to === type) {
          return conversion.convert(value);
        }
      }

      throw new Error('Cannot convert from ' + from + ' to ' + type);
    }
    
    /**
     * Stringify parameters in a normalized way
     * @param {Param[]} params
     * @return {string}
     */
    function stringifyParams (params) {
      return params
          .map(function (param) {
            var typeNames = param.types.map(getTypeName);

            return (param.restParam ? '...' : '') + typeNames.join('|');
          })
          .join(',');
    }

    /**
     * Parse a parameter, like "...number | boolean"
     * @param {string} param
     * @param {ConversionDef[]} conversions
     * @return {Param} param
     */
    function parseParam (param, conversions) {
      var restParam = param.indexOf('...') === 0;
      var types = (!restParam)
          ? param
          : (param.length > 3)
              ? param.slice(3)
              : 'any';

      var typeNames = types.split('|').map(trim)
          .filter(notEmpty)
          .filter(notIgnore);

      var matchingConversions = filterConversions(conversions, typeNames);

      var exactTypes = typeNames.map(function (typeName) {
        var type = findTypeByName(typeName);

        return {
          name: typeName,
          typeIndex: findTypeIndex(type),
          test: type.test,
          conversion: null,
          conversionIndex: -1
        };
      });

      var convertibleTypes = matchingConversions.map(function (conversion) {
        var type = findTypeByName(conversion.from);

        return {
          name: conversion.from,
          typeIndex: findTypeIndex(type),
          test: type.test,
          conversion: conversion,
          conversionIndex: conversions.indexOf(conversion)
        };
      });

      return {
        types: exactTypes.concat(convertibleTypes),
        restParam: restParam
      };
    }

    /**
     * Parse a signature with comma separated parameters,
     * like "number | boolean, ...string"
     * @param {string} signature
     * @param {function} fn
     * @param {ConversionDef[]} conversions
     * @return {Signature | null} signature
     */
    function parseSignature (signature, fn, conversions) {
      var params = [];

      if (signature.trim() !== '') {
        params = signature
            .split(',')
            .map(trim)
            .map(function (param, index, array) {
              var parsedParam = parseParam(param, conversions);

              if (parsedParam.restParam && (index !== array.length - 1)) {
                throw new SyntaxError('Unexpected rest parameter "' + param + '": ' +
                    'only allowed for the last parameter');
              }

              return parsedParam;
          });
      }

      if (params.some(isInvalidParam)) {
        // invalid signature: at least one parameter has no types
        // (they may have been filtered)
        return null;
      }

      return {
        params: params,
        fn: fn
      };
    }

    /**
     * Test whether a set of params contains a restParam
     * @param {Param[]} params
     * @return {boolean} Returns true when the last parameter is a restParam
     */
    function hasRestParam(params) {
      var param = last(params)
      return param ? param.restParam : false;
    }

    /**
     * Test whether a parameter contains conversions
     * @param {Param} param
     * @return {boolean} Returns true when at least one of the parameters
     *                   contains a conversion.
     */
    function hasConversions(param) {
      return param.types.some(function (type) {
        return type.conversion != null;
      });
    }

    /**
     * Create a type test for a single parameter, which can have one or multiple
     * types.
     * @param {Param} param
     * @return {function(x: *) : boolean} Returns a test function
     */
    function compileTest(param) {
      if (!param || param.types.length === 0) {
        // nothing to do
        return ok;
      }
      else if (param.types.length === 1) {
        return findTypeByName(param.types[0].name).test;
      }
      else if (param.types.length === 2) {
        var test0 = findTypeByName(param.types[0].name).test;
        var test1 = findTypeByName(param.types[1].name).test;
        return function or(x) {
          return test0(x) || test1(x);
        }
      }
      else { // param.types.length > 2
        var tests = param.types.map(function (type) {
          return findTypeByName(type.name).test;
        })
        return function or(x) {
          for (var i = 0; i < tests.length; i++) {
            if (tests[i](x)) {
              return true;
            }
          }
          return false;
        }
      }
    }

    /**
     * Create a test for all parameters of a signature
     * @param {Param[]} params
     * @return {function(args: Array<*>) : boolean}
     */
    function compileTests(params) {
      var tests, test0, test1;

      if (hasRestParam(params)) {
        // variable arguments like '...number'
        tests = initial(params).map(compileTest);
        var varIndex = tests.length;
        var lastTest = compileTest(last(params));
        var testRestParam = function (args) {
          for (var i = varIndex; i < args.length; i++) {
            if (!lastTest(args[i])) {
              return false;
            }
          }
          return true;
        }

        return function testArgs(args) {
          for (var i = 0; i < tests.length; i++) {
            if (!tests[i](args[i])) {
              return false;
            }
          }
          return testRestParam(args) && (args.length >= varIndex + 1);
        };
      }
      else {
        // no variable arguments
        if (params.length === 0) {
          return function testArgs(args) {
            return args.length === 0;
          };
        }
        else if (params.length === 1) {
          test0 = compileTest(params[0]);
          return function testArgs(args) {
            return test0(args[0]) && args.length === 1;
          };
        }
        else if (params.length === 2) {
          test0 = compileTest(params[0]);
          test1 = compileTest(params[1]);
          return function testArgs(args) {
            return test0(args[0]) && test1(args[1]) && args.length === 2;
          };
        }
        else { // arguments.length > 2
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

    /**
     * Find the parameter at a specific index of a signature.
     * Handles rest parameters.
     * @param {Signature} signature
     * @param {number} index
     * @return {Param | null} Returns the matching parameter when found,
     *                        null otherwise.
     */
    function getParamAtIndex(signature, index) {
      return index < signature.params.length
          ? signature.params[index]
          : hasRestParam(signature.params)
              ? last(signature.params)
              : null
    }

    /**
     * Get all type names of a parameter
     * @param {Signature} signature
     * @param {number} index
     * @param {boolean} excludeConversions
     * @return {string[]} Returns an array with type names
     */
    function getExpectedTypeNames (signature, index, excludeConversions) {
      var param = getParamAtIndex(signature, index);
      var types = param
          ? excludeConversions
                  ? param.types.filter(isExactType)
                  : param.types
          : [];

      return types.map(getTypeName);
    }

    /**
     * Returns the name of a type
     * @param {Type} type
     * @return {string} Returns the type name
     */
    function getTypeName(type) {
      return type.name;
    }

    /**
     * Test whether a type is an exact type or conversion
     * @param {Type} type
     * @return {boolean} Returns true when
     */
    function isExactType(type) {
      return type.conversion === null || type.conversion === undefined;
    }

    /**
     * Helper function for creating error messages: create an array with
     * all available types on a specific argument index.
     * @param {Signature[]} signatures
     * @param {number} index
     * @return {string[]} Returns an array with available types
     */
    function mergeExpectedParams(signatures, index) {
      var typeNames = uniq(flatMap(signatures, function (signature) {
        return getExpectedTypeNames(signature, index, false);
      }));

      return (typeNames.indexOf('any') !== -1) ? ['any'] : typeNames;
    }

    /**
     * Create
     * @param {string} name             The name of the function
     * @param {array.<*>} args          The actual arguments passed to the function
     * @param {Signature[]} signatures  A list with available signatures
     * @return {TypeError} Returns a type error with additional data
     *                     attached to it in the property `data`
     */
    function createError(name, args, signatures) {
      var err, expected;
      var _name = name || 'unnamed';

      // test for wrong type at some index
      var matchingSignatures = signatures;
      var index;
      for (index = 0; index < args.length; index++) {
        var nextMatchingDefs = matchingSignatures.filter(function (signature) {
          var test = compileTest(getParamAtIndex(signature, index));
          return (index < signature.params.length || hasRestParam(signature.params)) &&
              test(args[index]);
        });

        if (nextMatchingDefs.length === 0) {
          // no matching signatures anymore, throw error "wrong type"
          expected = mergeExpectedParams(matchingSignatures, index);
          if (expected.length > 0) {
            var actualType = findTypeName(args[index]);

            err = new TypeError('Unexpected type of argument in function ' + _name +
                ' (expected: ' + expected.join(' or ') +
                ', actual: ' + actualType + ', index: ' + index + ')');
            err.data = {
              category: 'wrongType',
              fn: _name,
              index: index,
              actual: actualType,
              expected: expected
            }
            return err;
          }
        }
        else {
          matchingSignatures = nextMatchingDefs;
        }
      }

      // test for too few arguments
      var lengths = matchingSignatures.map(function (signature) {
        return hasRestParam(signature.params) ? Infinity : signature.params.length;
      });
      if (args.length < Math.min.apply(null, lengths)) {
        expected = mergeExpectedParams(matchingSignatures, index);
        err = new TypeError('Too few arguments in function ' + _name +
            ' (expected: ' + expected.join(' or ') +
            ', index: ' + args.length + ')');
        err.data = {
          category: 'tooFewArgs',
          fn: _name,
          index: args.length,
          expected: expected
        }
        return err;
      }

      // test for too many arguments
      var maxLength = Math.max.apply(null, lengths);
      if (args.length > maxLength) {
        err = new TypeError('Too many arguments in function ' + _name +
            ' (expected: ' + maxLength + ', actual: ' + args.length + ')');
        err.data = {
          category: 'tooManyArgs',
          fn: _name,
          index: args.length,
          expectedLength: maxLength
        }
        return err;
      }

      err = new TypeError('Arguments of type "' + args.join(', ') +
          '" do not match any of the defined signatures of function ' + _name + '.');
      err.data = {
        category: 'mismatch',
        actual: args.map(findTypeName)
      }
      return err;
    }

    /**
     * Find the lowest index of all exact types of a parameter (no conversions)
     * @param {Param} param
     * @return {number} Returns the index of the lowest type in typed.types
     */
    function getLowestTypeIndex (param) {
      var min = 999;

      for (var i = 0; i < param.types.length; i++) {
        if (isExactType(param.types[i])) {
          min = Math.min(min, param.types[i].typeIndex);
        }
      }

      return min;
    }

    /**
     * Find the lowest index of the conversion of all types of the parameter
     * having a conversion
     * @param {Param} param
     * @return {number} Returns the lowest index of the conversions of this type
     */
    function getLowestConversionIndex (param) {
      var min = 999;

      for (var i = 0; i < param.types.length; i++) {
        if (!isExactType(param.types[i])) {
          min = Math.min(min, param.types[i].conversionIndex);
        }
      }

      return min;
    }

    /**
     * Compare two params
     * @param {Param} param1
     * @param {Param} param2
     * @return {number} returns a negative number when param1 must get a lower
     *                  index than param2, a positive number when the opposite,
     *                  or zero when both are equal
     */
    function compareParams (param1, param2) {
      var c;

      // compare having a rest parameter or not
      c = param1.restParam - param2.restParam;
      if (c !== 0) {
        return c;
      }

      // compare having conversions or not
      c = hasConversions(param1) - hasConversions(param2);
      if (c !== 0) {
        return c;
      }

      // compare the index of the types
      c = getLowestTypeIndex(param1) - getLowestTypeIndex(param2);
      if (c !== 0) {
        return c;
      }

      // compare the index of any conversion
      return getLowestConversionIndex(param1) - getLowestConversionIndex(param2);
    }

    /**
     * Compare two signatures
     * @param {Signature} signature1
     * @param {Signature} signature2
     * @return {number} returns a negative number when param1 must get a lower
     *                  index than param2, a positive number when the opposite,
     *                  or zero when both are equal
     */
    function compareSignatures (signature1, signature2) {
      var len = Math.min(signature1.params.length, signature2.params.length);
      var i;
      var c;

      // compare whether the params have conversions at all or not
      c = signature1.params.some(hasConversions) - signature2.params.some(hasConversions)
      if (c !== 0) {
        return c;
      }

      // next compare whether the params have conversions one by one
      for (i = 0; i < len; i++) {
        c = hasConversions(signature1.params[i]) - hasConversions(signature2.params[i]);
        if (c !== 0) {
          return c;
        }
      }

      // compare the types of the params one by one
      for (i = 0; i < len; i++) {
        c = compareParams(signature1.params[i], signature2.params[i]);
        if (c !== 0) {
          return c;
        }
      }

      // compare the number of params
      return signature1.params.length - signature2.params.length;
    }

    /**
     * Get params containing all types that can be converted to the defined types.
     *
     * @param {ConversionDef[]} conversions
     * @param {string[]} typeNames
     * @return {ConversionDef[]} Returns the conversions that are available
     *                        for every type (if any)
     */
    function filterConversions(conversions, typeNames) {
      var matches = {};

      conversions.forEach(function (conversion) {
        if (typeNames.indexOf(conversion.from) === -1 &&
            typeNames.indexOf(conversion.to) !== -1 &&
            !matches[conversion.from]) {
          matches[conversion.from] = conversion;
        }
      });

      return Object.keys(matches).map(function (from) {
        return matches[from];
      });
    }

    /**
     * Preprocess arguments before calling the original function:
     * - if needed convert the parameters
     * - in case of rest parameters, move the rest parameters into an Array
     * @param {Param[]} params
     * @param {function} fn
     * @return {function} Returns a wrapped function
     */
    function compileArgsPreprocessing(params, fn) {
      var fnConvert = fn;

      // TODO: can we make this wrapper function smarter/simpler?

      if (params.some(hasConversions)) {
        var restParam = hasRestParam(params);
        var compiledConversions = params.map(compileArgConversion)

        fnConvert = function convertArgs() {
          var args = [];
          var last = restParam ? arguments.length - 1 : arguments.length;
          for (var i = 0; i < last; i++) {
            args[i] = compiledConversions[i](arguments[i]);
          }
          if (restParam) {
            args[last] = arguments[last].map(compiledConversions[last]);
          }

          return fn.apply(this, args);
        }
      }

      var fnPreprocess = fnConvert;
      if (hasRestParam(params)) {
        var offset = params.length - 1;

        fnPreprocess = function preprocessRestParams () {
          return fnConvert.apply(this,
              slice(arguments, 0, offset).concat([slice(arguments, offset)]));
        }
      }

      return fnPreprocess;
    }

    /**
     * Compile conversion for a parameter to the right type
     * @param {Param} param
     * @return {function} Returns the wrapped function that will convert arguments
     *
     */
    function compileArgConversion(param) {
      var test0, test1, conversion0, conversion1;
      var tests = [];
      var conversions = [];

      param.types.forEach(function (type) {
        if (type.conversion) {
          tests.push(findTypeByName(type.conversion.from).test);
          conversions.push(type.conversion.convert);
        }
      });

      // create optimized conversion functions depending on the number of conversions
      switch (conversions.length) {
        case 0:
          return function convertArg(arg) {
            return arg;
          }

        case 1:
          test0 = tests[0]
          conversion0 = conversions[0];
          return function convertArg(arg) {
            if (test0(arg)) {
              return conversion0(arg)
            }
            return arg;
          }

        case 2:
          test0 = tests[0]
          test1 = tests[1]
          conversion0 = conversions[0];
          conversion1 = conversions[1];
          return function convertArg(arg) {
            if (test0(arg)) {
              return conversion0(arg)
            }
            if (test1(arg)) {
              return conversion1(arg)
            }
            return arg;
          }

        default:
          return function convertArg(arg) {
            for (var i = 0; i < conversions.length; i++) {
              if (tests[i](arg)) {
                return conversions[i](arg);
              }
            }
            return arg;
          }
      }
    }

    /**
     * Convert an array with signatures into a map with signatures,
     * where signatures with union types are split into separate signatures
     *
     * Throws an error when there are conflicting types
     *
     * @param {Signature[]} signatures
     * @return {Object.<string, function>}  Returns a map with signatures
     *                                      as key and the original function
     *                                      of this signature as value.
     */
    function createSignaturesMap(signatures) {
      var signaturesMap = {};
      signatures.forEach(function (signature) {
        if (!signature.params.some(hasConversions)) {
          splitParams(signature.params, true).forEach(function (params) {
            signaturesMap[stringifyParams(params)] = signature.fn;
          });
        }
      });

      return signaturesMap;
    }

    /**
     * Split params with union types in to separate params.
     *
     * For example:
     *
     *     splitParams([['Array', 'Object'], ['string', 'RegExp'])
     *     // returns:
     *     // [
     *     //   ['Array', 'string'],
     *     //   ['Array', 'RegExp'],
     *     //   ['Object', 'string'],
     *     //   ['Object', 'RegExp']
     *     // ]
     *
     * @param {Param[]} params
     * @param {boolean} ignoreConversionTypes
     * @return {Param[]}
     */
    function splitParams(params, ignoreConversionTypes) {
      function _splitParams(params, index, types) {
        if (index < params.length) {
          var param = params[index]
          var filteredTypes = ignoreConversionTypes
              ? param.types.filter(isExactType)
              : param.types;
          var typeGroups

          if (param.restParam) {
            // split the types of a rest parameter in two:
            // one with only exact types, and one with exact types and conversions
            var exactTypes = filteredTypes.filter(isExactType)
            typeGroups = exactTypes.length < filteredTypes.length
                ? [exactTypes, filteredTypes]
                : [filteredTypes]

          }
          else {
            // split all the types of a regular parameter into one type per group
            typeGroups = filteredTypes.map(function (type) {
              return [type]
            })
          }

          // recurse over the groups with types
          return flatMap(typeGroups, function (typeGroup) {
            return _splitParams(params, index + 1, types.concat([typeGroup]));
          });

        }
        else {
          // we've reached the end of the parameters. Now build a new Param
          var splittedParams = types.map(function (type, typeIndex) {
            return {
              types: type,
              restParam: (typeIndex === params.length - 1) && hasRestParam(params)
            }
          });

          return [splittedParams];
        }
      }

      return _splitParams(params, 0, []);
    }

    /**
     * Test whether two signatures have a conflicting signature
     * @param {Signature} signature1
     * @param {Signature} signature2
     * @return {boolean} Returns true when the signatures conflict, false otherwise.
     */
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

      return restParam1
          ? restParam2 ? (len1 === len2) : (len2 >= len1)
          : restParam2 ? (len1 >= len2)  : (len1 === len2)
    }

    /**
     * Create a typed function
     * @param {String} name               The name for the typed function
     * @param {Object.<string, function>} signaturesMap
     *                                    An object with one or
     *                                    multiple signatures as key, and the
     *                                    function corresponding to the
     *                                    signature as value.
     * @return {function}  Returns the created typed function.
     */
    function createTypedFunction(name, signaturesMap) {
      if (Object.keys(signaturesMap).length === 0) {
        throw new SyntaxError('No signatures provided');
      }

      // parse the signatures, and check for conflicts
      var parsedSignatures = [];
      Object.keys(signaturesMap)
          .map(function (signature) {
            return parseSignature(signature, signaturesMap[signature], typed.conversions);
          })
          .filter(notNull)
          .forEach(function (parsedSignature) {
            // check whether this parameter conflicts with already parsed signatures
            var conflictingSignature = findInArray(parsedSignatures, function (s) {
              return hasConflictingParams(s, parsedSignature)
            });
            if (conflictingSignature) {
              throw new TypeError('Conflicting signatures "' +
                  stringifyParams(conflictingSignature.params) + '" and "' +
                  stringifyParams(parsedSignature.params) + '".');
            }

            parsedSignatures.push(parsedSignature);
          });

      // split and filter the types of the signatures, and then order them
      var signatures = flatMap(parsedSignatures, function (parsedSignature) {
        var params = parsedSignature ? splitParams(parsedSignature.params, false) : []

        return params.map(function (params) {
          return {
            params: params,
            fn: parsedSignature.fn
          };
        });
      }).filter(notNull);

      signatures.sort(compareSignatures);

      // we create a highly optimized checks for the first couple of signatures with max 2 arguments
      var ok0 = signatures[0] && signatures[0].params.length <= 2 && !hasRestParam(signatures[0].params);
      var ok1 = signatures[1] && signatures[1].params.length <= 2 && !hasRestParam(signatures[1].params);
      var ok2 = signatures[2] && signatures[2].params.length <= 2 && !hasRestParam(signatures[2].params);
      var ok3 = signatures[3] && signatures[3].params.length <= 2 && !hasRestParam(signatures[3].params);
      var ok4 = signatures[4] && signatures[4].params.length <= 2 && !hasRestParam(signatures[4].params);
      var ok5 = signatures[5] && signatures[5].params.length <= 2 && !hasRestParam(signatures[5].params);
      var allOk = ok0 && ok1 && ok2 && ok3 && ok4 && ok5;

      // compile the tests
      var tests = signatures.map(function (signature) {
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

      // compile the functions
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

      // simple and generic, but also slow
      var iStart = allOk ? 6 : 0;
      var iEnd = signatures.length;
      var generic = function generic() {
        'use strict';

        for (var i = iStart; i < iEnd; i++) {
          if (tests[i](arguments)) {
            return fns[i].apply(this, arguments);
          }
        }

        return typed.onMismatch(name, arguments, signatures);
      }

      // create the typed function
      // fast, specialized version. Falls back to the slower, generic one if needed
      var fn = function fn(arg0, arg1) {
        'use strict';

        if (arguments.length === len0 && test00(arg0) && test01(arg1)) { return fn0.apply(fn, arguments); }
        if (arguments.length === len1 && test10(arg0) && test11(arg1)) { return fn1.apply(fn, arguments); }
        if (arguments.length === len2 && test20(arg0) && test21(arg1)) { return fn2.apply(fn, arguments); }
        if (arguments.length === len3 && test30(arg0) && test31(arg1)) { return fn3.apply(fn, arguments); }
        if (arguments.length === len4 && test40(arg0) && test41(arg1)) { return fn4.apply(fn, arguments); }
        if (arguments.length === len5 && test50(arg0) && test51(arg1)) { return fn5.apply(fn, arguments); }

        return generic.apply(fn, arguments);
      }

      // attach name the typed function
      try {
        Object.defineProperty(fn, 'name', {value: name});
      }
      catch (err) {
        // old browsers do not support Object.defineProperty and some don't support setting the name property
        // the function name is not essential for the functioning, it's mostly useful for debugging,
        // so it's fine to have unnamed functions.
      }

      // attach signatures to the function
      fn.signatures = createSignaturesMap(signatures);

      return fn;
    }

    /**
     * Action to take on mismatch
     * @param {string} name      Name of function that was attempted to be called
     * @param {Array} args       Actual arguments to the call
     * @param {Array} signatures Known signatures of the named typed-function
     */
    function _onMismatch(name, args, signatures) {
      throw createError(name, args, signatures);
    }

    /**
     * Test whether a type should be NOT be ignored
     * @param {string} typeName
     * @return {boolean}
     */
    function notIgnore(typeName) {
      return typed.ignore.indexOf(typeName) === -1;
    }

    /**
     * trim a string
     * @param {string} str
     * @return {string}
     */
    function trim(str) {
      return str.trim();
    }

    /**
     * Test whether a string is not empty
     * @param {string} str
     * @return {boolean}
     */
    function notEmpty(str) {
      return !!str;
    }

    /**
     * test whether a value is not strict equal to null
     * @param {*} value
     * @return {boolean}
     */
    function notNull(value) {
      return value !== null;
    }

    /**
     * Test whether a parameter has no types defined
     * @param {Param} param
     * @return {boolean}
     */
    function isInvalidParam (param) {
      return param.types.length === 0;
    }

    /**
     * Return all but the last items of an array
     * @param {Array} arr
     * @return {Array}
     */
    function initial(arr) {
      return arr.slice(0, arr.length - 1);
    }

    /**
     * return the last item of an array
     * @param {Array} arr
     * @return {*}
     */
    function last(arr) {
      return arr[arr.length - 1];
    }

    /**
     * Slice an array or function Arguments
     * @param {Array | Arguments | IArguments} arr
     * @param {number} start
     * @param {number} [end]
     * @return {Array}
     */
    function slice(arr, start, end) {
      return Array.prototype.slice.call(arr, start, end);
    }

    /**
     * Test whether an array contains some item
     * @param {Array} array
     * @param {*} item
     * @return {boolean} Returns true if array contains item, false if not.
     */
    function contains(array, item) {
      return array.indexOf(item) !== -1;
    }

    /**
     * Test whether two arrays have overlapping items
     * @param {Array} array1
     * @param {Array} array2
     * @return {boolean} Returns true when at least one item exists in both arrays
     */
    function hasOverlap(array1, array2) {
      for (var i = 0; i < array1.length; i++) {
        if (contains(array2, array1[i])) {
          return true;
        }
      }

      return false;
    }

    /**
     * Return the first item from an array for which test(arr[i]) returns true
     * @param {Array} arr
     * @param {function} test
     * @return {* | undefined} Returns the first matching item
     *                         or undefined when there is no match
     */
    function findInArray(arr, test) {
      for (var i = 0; i < arr.length; i++) {
        if (test(arr[i])) {
          return arr[i];
        }
      }
      return undefined;
    }

    /**
     * Filter unique items of an array with strings
     * @param {string[]} arr
     * @return {string[]}
     */
    function uniq(arr) {
      var entries = {}
      for (var i = 0; i < arr.length; i++) {
        entries[arr[i]] = true;
      }
      return Object.keys(entries);
    }

    /**
     * Flat map the result invoking a callback for every item in an array.
     * https://gist.github.com/samgiles/762ee337dff48623e729
     * @param {Array} arr
     * @param {function} callback
     * @return {Array}
     */
    function flatMap(arr, callback) {
      return Array.prototype.concat.apply([], arr.map(callback));
    }

    /**
     * Retrieve the function name from a set of typed functions,
     * and check whether the name of all functions match (if given)
     * @param {function[]} fns
     */
    function getName (fns) {
      var name = '';

      for (var i = 0; i < fns.length; i++) {
        var fn = fns[i];

        // check whether the names are the same when defined
        if ((typeof fn.signatures === 'object' || typeof fn.signature === 'string') && fn.name !== '') {
          if (name === '') {
            name = fn.name;
          }
          else if (name !== fn.name) {
            var err = new Error('Function names do not match (expected: ' + name + ', actual: ' + fn.name + ')');
            err.data = {
              actual: fn.name,
              expected: name
            };
            throw err;
          }
        }
      }

      return name;
    }

    // extract and merge all signatures of a list with typed functions
    function extractSignatures(fns) {
      var err;
      var signaturesMap = {};

      function validateUnique(_signature, _fn) {
        if (signaturesMap.hasOwnProperty(_signature) && _fn !== signaturesMap[_signature]) {
          err = new Error('Signature "' + _signature + '" is defined twice');
          err.data = {signature: _signature};
          throw err;
          // else: both signatures point to the same function, that's fine
        }
      }

      for (var i = 0; i < fns.length; i++) {
        var fn = fns[i];

        // test whether this is a typed-function
        if (typeof fn.signatures === 'object') {
          // merge the signatures
          for (var signature in fn.signatures) {
            if (fn.signatures.hasOwnProperty(signature)) {
              validateUnique(signature, fn.signatures[signature]);
              signaturesMap[signature] = fn.signatures[signature];
            }
          }
        }
        else if (typeof fn.signature === 'string') {
          validateUnique(fn.signature, fn);
          signaturesMap[fn.signature] = fn;
        }
        else {
          err = new TypeError('Function is no typed-function (index: ' + i + ')');
          err.data = {index: i};
          throw err;
        }
      }

      return signaturesMap;
    }

    typed = createTypedFunction('typed', {
      'string, Object': createTypedFunction,
      'Object': function (signaturesMap) {
        // find existing name
        var fns = [];
        for (var signature in signaturesMap) {
          if (signaturesMap.hasOwnProperty(signature)) {
            fns.push(signaturesMap[signature]);
          }
        }
        var name = getName(fns);
        return createTypedFunction(name, signaturesMap);
      },
      '...Function': function (fns) {
        return createTypedFunction(getName(fns), extractSignatures(fns));
      },
      'string, ...Function': function (name, fns) {
        return createTypedFunction(name, extractSignatures(fns));
      }
    });

    typed.create = create;
    typed.types = _types;
    typed.conversions = _conversions;
    typed.ignore = _ignore;
    typed.onMismatch = _onMismatch;
    typed.throwMismatchError = _onMismatch;
    typed.createError = createError;
    typed.convert = convert;
    typed.find = find;

    /**
     * add a type
     * @param {{name: string, test: function}} type
     * @param {boolean} [beforeObjectTest=true]
     *                          If true, the new test will be inserted before
     *                          the test with name 'Object' (if any), since
     *                          tests for Object match Array and classes too.
     */
    typed.addType = function (type, beforeObjectTest) {
      if (!type || typeof type.name !== 'string' || typeof type.test !== 'function') {
        throw new TypeError('Object with properties {name: string, test: function} expected');
      }

      if (beforeObjectTest !== false) {
        for (var i = 0; i < typed.types.length; i++) {
          if (typed.types[i].name === 'Object') {
            typed.types.splice(i, 0, type);
            return
          }
        }
      }

      typed.types.push(type);
    };

    // add a conversion
    typed.addConversion = function (conversion) {
      if (!conversion
          || typeof conversion.from !== 'string'
          || typeof conversion.to !== 'string'
          || typeof conversion.convert !== 'function') {
        throw new TypeError('Object with properties {from: string, to: string, convert: function} expected');
      }

      typed.conversions.push(conversion);
    };

    return typed;
  }

  return create();
}));


/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "a559":
/***/ (function(module, exports) {

function _extends() {
  module.exports = _extends = Object.assign ? Object.assign.bind() : function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  }, module.exports.__esModule = true, module.exports["default"] = module.exports;
  return _extends.apply(this, arguments);
}

module.exports = _extends, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "c0e2":
/***/ (function(module, exports) {

function E () {
  // Keep this empty so it's easier to inherit from
  // (via https://github.com/lipsmack from https://github.com/scottcorgan/tiny-emitter/issues/3)
}

E.prototype = {
  on: function (name, callback, ctx) {
    var e = this.e || (this.e = {});

    (e[name] || (e[name] = [])).push({
      fn: callback,
      ctx: ctx
    });

    return this;
  },

  once: function (name, callback, ctx) {
    var self = this;
    function listener () {
      self.off(name, listener);
      callback.apply(ctx, arguments);
    };

    listener._ = callback
    return this.on(name, listener, ctx);
  },

  emit: function (name) {
    var data = [].slice.call(arguments, 1);
    var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
    var i = 0;
    var len = evtArr.length;

    for (i; i < len; i++) {
      evtArr[i].fn.apply(evtArr[i].ctx, data);
    }

    return this;
  },

  off: function (name, callback) {
    var e = this.e || (this.e = {});
    var evts = e[name];
    var liveEvents = [];

    if (evts && callback) {
      for (var i = 0, len = evts.length; i < len; i++) {
        if (evts[i].fn !== callback && evts[i].fn._ !== callback)
          liveEvents.push(evts[i]);
      }
    }

    // Remove event from queue to prevent memory leak
    // Suggested by https://github.com/lazd
    // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

    (liveEvents.length)
      ? e[name] = liveEvents
      : delete e[name];

    return this;
  }
};

module.exports = E;
module.exports.TinyEmitter = E;


/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "expressions", function() { return /* reexport */ src_expressions; });
__webpack_require__.d(__webpack_exports__, "FormField", function() { return /* reexport */ FormField; });
__webpack_require__.d(__webpack_exports__, "Field", function() { return /* reexport */ Field; });
__webpack_require__.d(__webpack_exports__, "PluginSettings", function() { return /* reexport */ PluginSettings; });
__webpack_require__.d(__webpack_exports__, "PluginFilter", function() { return /* reexport */ PluginFilter; });
__webpack_require__.d(__webpack_exports__, "PluginManagement", function() { return /* reexport */ PluginManagement; });
__webpack_require__.d(__webpack_exports__, "PluginUpload", function() { return /* reexport */ PluginUpload; });
__webpack_require__.d(__webpack_exports__, "SaveButton", function() { return /* reexport */ SaveButton; });
__webpack_require__.d(__webpack_exports__, "Form", function() { return /* reexport */ Form; });
__webpack_require__.d(__webpack_exports__, "GroupedSettings", function() { return /* reexport */ GroupedSettings; });
__webpack_require__.d(__webpack_exports__, "PluginsIntro", function() { return /* reexport */ PluginsIntro; });
__webpack_require__.d(__webpack_exports__, "ThemesIntro", function() { return /* reexport */ ThemesIntro; });
__webpack_require__.d(__webpack_exports__, "PasswordConfirmation", function() { return /* reexport */ PasswordConfirmation; });
__webpack_require__.d(__webpack_exports__, "PluginName", function() { return /* reexport */ PluginName; });
__webpack_require__.d(__webpack_exports__, "PluginsTable", function() { return /* reexport */ PluginsTable; });
__webpack_require__.d(__webpack_exports__, "PluginsTableWithUpdates", function() { return /* reexport */ PluginsTableWithUpdates; });
__webpack_require__.d(__webpack_exports__, "UploadPluginDialog", function() { return /* reexport */ UploadPluginDialog; });
__webpack_require__.d(__webpack_exports__, "InstallAllPaidPluginsButton", function() { return /* reexport */ InstallAllPaidPluginsButton; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/extends.js
var helpers_extends = __webpack_require__("a559");
var extends_default = /*#__PURE__*/__webpack_require__.n(helpers_extends);

// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/is.js
// type checks for all known types
//
// note that:
//
// - check by duck-typing on a property like `isUnit`, instead of checking instanceof.
//   instanceof cannot be used because that would not allow to pass data from
//   one instance of math.js to another since each has it's own instance of Unit.
// - check the `isUnit` property via the constructor, so there will be no
//   matches for "fake" instances like plain objects with a property `isUnit`.
//   That is important for security reasons.
// - It must not be possible to override the type checks used internally,
//   for security reasons, so these functions are not exposed in the expression
//   parser.
function isNumber(x) {
  return typeof x === 'number';
}
function isBigNumber(x) {
  if (!x || typeof x !== 'object' || typeof x.constructor !== 'function') {
    return false;
  }

  if (x.isBigNumber === true && typeof x.constructor.prototype === 'object' && x.constructor.prototype.isBigNumber === true) {
    return true;
  }

  if (typeof x.constructor.isDecimal === 'function' && x.constructor.isDecimal(x) === true) {
    return true;
  }

  return false;
}
function isComplex(x) {
  return x && typeof x === 'object' && Object.getPrototypeOf(x).isComplex === true || false;
}
function isFraction(x) {
  return x && typeof x === 'object' && Object.getPrototypeOf(x).isFraction === true || false;
}
function is_isUnit(x) {
  return x && x.constructor.prototype.isUnit === true || false;
}
function isString(x) {
  return typeof x === 'string';
}
var isArray = Array.isArray;
function isMatrix(x) {
  return x && x.constructor.prototype.isMatrix === true || false;
}
/**
 * Test whether a value is a collection: an Array or Matrix
 * @param {*} x
 * @returns {boolean} isCollection
 */

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
  return typeof x === 'boolean';
}
function isResultSet(x) {
  return x && x.constructor.prototype.isResultSet === true || false;
}
function isHelp(x) {
  return x && x.constructor.prototype.isHelp === true || false;
}
function isFunction(x) {
  return typeof x === 'function';
}
function isDate(x) {
  return x instanceof Date;
}
function isRegExp(x) {
  return x instanceof RegExp;
}
function isObject(x) {
  return !!(x && typeof x === 'object' && x.constructor === Object && !isComplex(x) && !isFraction(x));
}
function isNull(x) {
  return x === null;
}
function isUndefined(x) {
  return x === undefined;
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

  if (t === 'object') {
    // JavaScript types
    if (x === null) return 'null';
    if (Array.isArray(x)) return 'Array';
    if (x instanceof Date) return 'Date';
    if (x instanceof RegExp) return 'RegExp'; // math.js types

    if (isBigNumber(x)) return 'BigNumber';
    if (isComplex(x)) return 'Complex';
    if (isFraction(x)) return 'Fraction';
    if (isMatrix(x)) return 'Matrix';
    if (is_isUnit(x)) return 'Unit';
    if (isIndex(x)) return 'Index';
    if (isRange(x)) return 'Range';
    if (isResultSet(x)) return 'ResultSet';
    if (isNode(x)) return x.type;
    if (isChain(x)) return 'Chain';
    if (isHelp(x)) return 'Help';
    return 'Object';
  }

  if (t === 'function') return 'Function';
  return t; // can be 'string', 'number', 'boolean', ...
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/object.js

/**
 * Clone an object
 *
 *     clone(x)
 *
 * Can clone any primitive type, array, and object.
 * If x has a function clone, this function will be invoked to clone the object.
 *
 * @param {*} x
 * @return {*} clone
 */

function clone(x) {
  var type = typeof x; // immutable primitive types

  if (type === 'number' || type === 'string' || type === 'boolean' || x === null || x === undefined) {
    return x;
  } // use clone function of the object when available


  if (typeof x.clone === 'function') {
    return x.clone();
  } // array


  if (Array.isArray(x)) {
    return x.map(function (value) {
      return clone(value);
    });
  }

  if (x instanceof Date) return new Date(x.valueOf());
  if (isBigNumber(x)) return x; // bignumbers are immutable

  if (x instanceof RegExp) throw new TypeError('Cannot clone ' + x); // TODO: clone a RegExp
  // object

  return mapObject(x, clone);
}
/**
 * Apply map to all properties of an object
 * @param {Object} object
 * @param {function} callback
 * @return {Object} Returns a copy of the object with mapped properties
 */

function mapObject(object, callback) {
  var clone = {};

  for (var key in object) {
    if (object_hasOwnProperty(object, key)) {
      clone[key] = callback(object[key]);
    }
  }

  return clone;
}
/**
 * Extend object a with the properties of object b
 * @param {Object} a
 * @param {Object} b
 * @return {Object} a
 */

function extend(a, b) {
  for (var prop in b) {
    if (object_hasOwnProperty(b, prop)) {
      a[prop] = b[prop];
    }
  }

  return a;
}
/**
 * Deep extend an object a with the properties of object b
 * @param {Object} a
 * @param {Object} b
 * @returns {Object}
 */

function deepExtend(a, b) {
  // TODO: add support for Arrays to deepExtend
  if (Array.isArray(b)) {
    throw new TypeError('Arrays are not supported by deepExtend');
  }

  for (var prop in b) {
    // We check against prop not being in Object.prototype or Function.prototype
    // to prevent polluting for example Object.__proto__.
    if (object_hasOwnProperty(b, prop) && !(prop in Object.prototype) && !(prop in Function.prototype)) {
      if (b[prop] && b[prop].constructor === Object) {
        if (a[prop] === undefined) {
          a[prop] = {};
        }

        if (a[prop] && a[prop].constructor === Object) {
          deepExtend(a[prop], b[prop]);
        } else {
          a[prop] = b[prop];
        }
      } else if (Array.isArray(b[prop])) {
        throw new TypeError('Arrays are not supported by deepExtend');
      } else {
        a[prop] = b[prop];
      }
    }
  }

  return a;
}
/**
 * Deep test equality of all fields in two pairs of arrays or objects.
 * Compares values and functions strictly (ie. 2 is not the same as '2').
 * @param {Array | Object} a
 * @param {Array | Object} b
 * @returns {boolean}
 */

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
  } else if (typeof a === 'function') {
    return a === b;
  } else if (a instanceof Object) {
    if (Array.isArray(b) || !(b instanceof Object)) {
      return false;
    }

    for (prop in a) {
      // noinspection JSUnfilteredForInLoop
      if (!(prop in b) || !deepStrictEqual(a[prop], b[prop])) {
        return false;
      }
    }

    for (prop in b) {
      // noinspection JSUnfilteredForInLoop
      if (!(prop in a)) {
        return false;
      }
    }

    return true;
  } else {
    return a === b;
  }
}
/**
 * Recursively flatten a nested object.
 * @param {Object} nestedObject
 * @return {Object} Returns the flattened object
 */

function deepFlatten(nestedObject) {
  var flattenedObject = {};

  _deepFlatten(nestedObject, flattenedObject);

  return flattenedObject;
} // helper function used by deepFlatten

function _deepFlatten(nestedObject, flattenedObject) {
  for (var prop in nestedObject) {
    if (object_hasOwnProperty(nestedObject, prop)) {
      var value = nestedObject[prop];

      if (typeof value === 'object' && value !== null) {
        _deepFlatten(value, flattenedObject);
      } else {
        flattenedObject[prop] = value;
      }
    }
  }
}
/**
 * Test whether the current JavaScript engine supports Object.defineProperty
 * @returns {boolean} returns true if supported
 */


function canDefineProperty() {
  // test needed for broken IE8 implementation
  try {
    if (Object.defineProperty) {
      Object.defineProperty({}, 'x', {
        get: function get() {
          return null;
        }
      });
      return true;
    }
  } catch (e) {}

  return false;
}
/**
 * Attach a lazy loading property to a constant.
 * The given function `fn` is called once when the property is first requested.
 *
 * @param {Object} object         Object where to add the property
 * @param {string} prop           Property name
 * @param {Function} valueResolver Function returning the property value. Called
 *                                without arguments.
 */

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
/**
 * Traverse a path into an object.
 * When a namespace is missing, it will be created
 * @param {Object} object
 * @param {string | string[]} path   A dot separated string like 'name.space'
 * @return {Object} Returns the object at the end of the path
 */

function traverse(object, path) {
  if (path && typeof path === 'string') {
    return traverse(object, path.split('.'));
  }

  var obj = object;

  if (path) {
    for (var i = 0; i < path.length; i++) {
      var key = path[i];

      if (!(key in obj)) {
        obj[key] = {};
      }

      obj = obj[key];
    }
  }

  return obj;
}
/**
 * A safe hasOwnProperty
 * @param {Object} object
 * @param {string} property
 */

function object_hasOwnProperty(object, property) {
  return object && Object.hasOwnProperty.call(object, property);
}
/**
 * Test whether an object is a factory. a factory has fields:
 *
 * - factory: function (type: Object, config: Object, load: function, typed: function [, math: Object])   (required)
 * - name: string (optional)
 * - path: string    A dot separated path (optional)
 * - math: boolean   If true (false by default), the math namespace is passed
 *                   as fifth argument of the factory function
 *
 * @param {*} object
 * @returns {boolean}
 */

function isLegacyFactory(object) {
  return object && typeof object.factory === 'function';
}
/**
 * Get a nested property from an object
 * @param {Object} object
 * @param {string | string[]} path
 * @returns {Object}
 */

function get(object, path) {
  if (typeof path === 'string') {
    if (isPath(path)) {
      return get(object, path.split('.'));
    } else {
      return object[path];
    }
  }

  var child = object;

  for (var i = 0; i < path.length; i++) {
    var key = path[i];
    child = child ? child[key] : undefined;
  }

  return child;
}
/**
 * Set a nested property in an object
 * Mutates the object itself
 * If the path doesn't exist, it will be created
 * @param {Object} object
 * @param {string | string[]} path
 * @param {*} value
 * @returns {Object}
 */

function set(object, path, value) {
  if (typeof path === 'string') {
    if (isPath(path)) {
      return set(object, path.split('.'), value);
    } else {
      object[path] = value;
      return object;
    }
  }

  var child = object;

  for (var i = 0; i < path.length - 1; i++) {
    var key = path[i];

    if (child[key] === undefined) {
      child[key] = {};
    }

    child = child[key];
  }

  if (path.length > 0) {
    var lastKey = path[path.length - 1];
    child[lastKey] = value;
  }

  return object;
}
/**
 * Create an object composed of the picked object properties
 * @param {Object} object
 * @param {string[]} properties
 * @param {function} [transform] Optional value to transform a value when picking it
 * @return {Object}
 */

function pick(object, properties, transform) {
  var copy = {};

  for (var i = 0; i < properties.length; i++) {
    var key = properties[i];
    var value = get(object, key);

    if (value !== undefined) {
      set(copy, key, transform ? transform(value, key) : value);
    }
  }

  return copy;
}
/**
 * Shallow version of pick, creating an object composed of the picked object properties
 * but not for nested properties
 * @param {Object} object
 * @param {string[]} properties
 * @return {Object}
 */

function pickShallow(object, properties) {
  var copy = {};

  for (var i = 0; i < properties.length; i++) {
    var key = properties[i];
    var value = object[key];

    if (value !== undefined) {
      copy[key] = value;
    }
  }

  return copy;
}
function object_values(object) {
  return Object.keys(object).map(key => object[key]);
} // helper function to test whether a string contains a path like 'user.name'

function isPath(str) {
  return str.indexOf('.') !== -1;
}
// EXTERNAL MODULE: ./node_modules/tiny-emitter/index.js
var tiny_emitter = __webpack_require__("c0e2");
var tiny_emitter_default = /*#__PURE__*/__webpack_require__.n(tiny_emitter);

// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/emitter.js

/**
 * Extend given object with emitter functions `on`, `off`, `once`, `emit`
 * @param {Object} obj
 * @return {Object} obj
 */

function mixin(obj) {
  // create event emitter
  var emitter = new tiny_emitter_default.a(); // bind methods to obj (we don't want to expose the emitter.e Array...)

  obj.on = emitter.on.bind(emitter);
  obj.off = emitter.off.bind(emitter);
  obj.once = emitter.once.bind(emitter);
  obj.emit = emitter.emit.bind(emitter);
  return obj;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/number.js

/**
 * @typedef {{sign: '+' | '-' | '', coefficients: number[], exponent: number}} SplitValue
 */

/**
 * Check if a number is integer
 * @param {number | boolean} value
 * @return {boolean} isInteger
 */

function isInteger(value) {
  if (typeof value === 'boolean') {
    return true;
  }

  return isFinite(value) ? value === Math.round(value) : false;
}
/**
 * Calculate the sign of a number
 * @param {number} x
 * @returns {number}
 */

var sign = /* #__PURE__ */Math.sign || function (x) {
  if (x > 0) {
    return 1;
  } else if (x < 0) {
    return -1;
  } else {
    return 0;
  }
};
/**
 * Calculate the base-2 logarithm of a number
 * @param {number} x
 * @returns {number}
 */

var log2 = /* #__PURE__ */Math.log2 || function log2(x) {
  return Math.log(x) / Math.LN2;
};
/**
 * Calculate the base-10 logarithm of a number
 * @param {number} x
 * @returns {number}
 */

var log10 = /* #__PURE__ */Math.log10 || function log10(x) {
  return Math.log(x) / Math.LN10;
};
/**
 * Calculate the natural logarithm of a number + 1
 * @param {number} x
 * @returns {number}
 */

var log1p = /* #__PURE__ */Math.log1p || function (x) {
  return Math.log(x + 1);
};
/**
 * Calculate cubic root for a number
 *
 * Code from es6-shim.js:
 *   https://github.com/paulmillr/es6-shim/blob/master/es6-shim.js#L1564-L1577
 *
 * @param {number} x
 * @returns {number} Returns the cubic root of x
 */

var cbrt = /* #__PURE__ */Math.cbrt || function cbrt(x) {
  if (x === 0) {
    return x;
  }

  var negate = x < 0;
  var result;

  if (negate) {
    x = -x;
  }

  if (isFinite(x)) {
    result = Math.exp(Math.log(x) / 3); // from https://en.wikipedia.org/wiki/Cube_root#Numerical_methods

    result = (x / (result * result) + 2 * result) / 3;
  } else {
    result = x;
  }

  return negate ? -result : result;
};
/**
 * Calculates exponentiation minus 1
 * @param {number} x
 * @return {number} res
 */

var expm1 = /* #__PURE__ */Math.expm1 || function expm1(x) {
  return x >= 2e-4 || x <= -2e-4 ? Math.exp(x) - 1 : x + x * x / 2 + x * x * x / 6;
};
/**
 * Formats a number in a given base
 * @param {number} n
 * @param {number} base
 * @param {number} size
 * @returns {string}
 */

function formatNumberToBase(n, base, size) {
  var prefixes = {
    2: '0b',
    8: '0o',
    16: '0x'
  };
  var prefix = prefixes[base];
  var suffix = '';

  if (size) {
    if (size < 1) {
      throw new Error('size must be in greater than 0');
    }

    if (!isInteger(size)) {
      throw new Error('size must be an integer');
    }

    if (n > 2 ** (size - 1) - 1 || n < -(2 ** (size - 1))) {
      throw new Error("Value must be in range [-2^".concat(size - 1, ", 2^").concat(size - 1, "-1]"));
    }

    if (!isInteger(n)) {
      throw new Error('Value must be an integer');
    }

    if (n < 0) {
      n = n + 2 ** size;
    }

    suffix = "i".concat(size);
  }

  var sign = '';

  if (n < 0) {
    n = -n;
    sign = '-';
  }

  return "".concat(sign).concat(prefix).concat(n.toString(base)).concat(suffix);
}
/**
 * Convert a number to a formatted string representation.
 *
 * Syntax:
 *
 *    format(value)
 *    format(value, options)
 *    format(value, precision)
 *    format(value, fn)
 *
 * Where:
 *
 *    {number} value   The value to be formatted
 *    {Object} options An object with formatting options. Available options:
 *                     {string} notation
 *                         Number notation. Choose from:
 *                         'fixed'          Always use regular number notation.
 *                                          For example '123.40' and '14000000'
 *                         'exponential'    Always use exponential notation.
 *                                          For example '1.234e+2' and '1.4e+7'
 *                         'engineering'    Always use engineering notation.
 *                                          For example '123.4e+0' and '14.0e+6'
 *                         'auto' (default) Regular number notation for numbers
 *                                          having an absolute value between
 *                                          `lowerExp` and `upperExp` bounds, and
 *                                          uses exponential notation elsewhere.
 *                                          Lower bound is included, upper bound
 *                                          is excluded.
 *                                          For example '123.4' and '1.4e7'.
 *                         'bin', 'oct, or
 *                         'hex'            Format the number using binary, octal,
 *                                          or hexadecimal notation.
 *                                          For example '0b1101' and '0x10fe'.
 *                     {number} wordSize    The word size in bits to use for formatting
 *                                          in binary, octal, or hexadecimal notation.
 *                                          To be used only with 'bin', 'oct', or 'hex'
 *                                          values for 'notation' option. When this option
 *                                          is defined the value is formatted as a signed
 *                                          twos complement integer of the given word size
 *                                          and the size suffix is appended to the output.
 *                                          For example
 *                                          format(-1, {notation: 'hex', wordSize: 8}) === '0xffi8'.
 *                                          Default value is undefined.
 *                     {number} precision   A number between 0 and 16 to round
 *                                          the digits of the number.
 *                                          In case of notations 'exponential',
 *                                          'engineering', and 'auto',
 *                                          `precision` defines the total
 *                                          number of significant digits returned.
 *                                          In case of notation 'fixed',
 *                                          `precision` defines the number of
 *                                          significant digits after the decimal
 *                                          point.
 *                                          `precision` is undefined by default,
 *                                          not rounding any digits.
 *                     {number} lowerExp    Exponent determining the lower boundary
 *                                          for formatting a value with an exponent
 *                                          when `notation='auto`.
 *                                          Default value is `-3`.
 *                     {number} upperExp    Exponent determining the upper boundary
 *                                          for formatting a value with an exponent
 *                                          when `notation='auto`.
 *                                          Default value is `5`.
 *    {Function} fn    A custom formatting function. Can be used to override the
 *                     built-in notations. Function `fn` is called with `value` as
 *                     parameter and must return a string. Is useful for example to
 *                     format all values inside a matrix in a particular way.
 *
 * Examples:
 *
 *    format(6.4)                                        // '6.4'
 *    format(1240000)                                    // '1.24e6'
 *    format(1/3)                                        // '0.3333333333333333'
 *    format(1/3, 3)                                     // '0.333'
 *    format(21385, 2)                                   // '21000'
 *    format(12.071, {notation: 'fixed'})                // '12'
 *    format(2.3,    {notation: 'fixed', precision: 2})  // '2.30'
 *    format(52.8,   {notation: 'exponential'})          // '5.28e+1'
 *    format(12345678, {notation: 'engineering'})        // '12.345678e+6'
 *
 * @param {number} value
 * @param {Object | Function | number} [options]
 * @return {string} str The formatted value
 */


function format(value, options) {
  if (typeof options === 'function') {
    // handle format(value, fn)
    return options(value);
  } // handle special cases


  if (value === Infinity) {
    return 'Infinity';
  } else if (value === -Infinity) {
    return '-Infinity';
  } else if (isNaN(value)) {
    return 'NaN';
  } // default values for options


  var notation = 'auto';
  var precision;
  var wordSize;

  if (options) {
    // determine notation from options
    if (options.notation) {
      notation = options.notation;
    } // determine precision from options


    if (isNumber(options)) {
      precision = options;
    } else if (isNumber(options.precision)) {
      precision = options.precision;
    }

    if (options.wordSize) {
      wordSize = options.wordSize;

      if (typeof wordSize !== 'number') {
        throw new Error('Option "wordSize" must be a number');
      }
    }
  } // handle the various notations


  switch (notation) {
    case 'fixed':
      return toFixed(value, precision);

    case 'exponential':
      return toExponential(value, precision);

    case 'engineering':
      return toEngineering(value, precision);

    case 'bin':
      return formatNumberToBase(value, 2, wordSize);

    case 'oct':
      return formatNumberToBase(value, 8, wordSize);

    case 'hex':
      return formatNumberToBase(value, 16, wordSize);

    case 'auto':
      // remove trailing zeros after the decimal point
      return toPrecision(value, precision, options && options).replace(/((\.\d*?)(0+))($|e)/, function () {
        var digits = arguments[2];
        var e = arguments[4];
        return digits !== '.' ? digits + e : e;
      });

    default:
      throw new Error('Unknown notation "' + notation + '". ' + 'Choose "auto", "exponential", "fixed", "bin", "oct", or "hex.');
  }
}
/**
 * Split a number into sign, coefficients, and exponent
 * @param {number | string} value
 * @return {SplitValue}
 *              Returns an object containing sign, coefficients, and exponent
 */

function splitNumber(value) {
  // parse the input value
  var match = String(value).toLowerCase().match(/^(-?)(\d+\.?\d*)(e([+-]?\d+))?$/);

  if (!match) {
    throw new SyntaxError('Invalid number ' + value);
  }

  var sign = match[1];
  var digits = match[2];
  var exponent = parseFloat(match[4] || '0');
  var dot = digits.indexOf('.');
  exponent += dot !== -1 ? dot - 1 : digits.length - 1;
  var coefficients = digits.replace('.', '') // remove the dot (must be removed before removing leading zeros)
  .replace(/^0*/, function (zeros) {
    // remove leading zeros, add their count to the exponent
    exponent -= zeros.length;
    return '';
  }).replace(/0*$/, '') // remove trailing zeros
  .split('').map(function (d) {
    return parseInt(d);
  });

  if (coefficients.length === 0) {
    coefficients.push(0);
    exponent++;
  }

  return {
    sign,
    coefficients,
    exponent
  };
}
/**
 * Format a number in engineering notation. Like '1.23e+6', '2.3e+0', '3.500e-3'
 * @param {number | string} value
 * @param {number} [precision]        Optional number of significant figures to return.
 */

function toEngineering(value, precision) {
  if (isNaN(value) || !isFinite(value)) {
    return String(value);
  }

  var split = splitNumber(value);
  var rounded = roundDigits(split, precision);
  var e = rounded.exponent;
  var c = rounded.coefficients; // find nearest lower multiple of 3 for exponent

  var newExp = e % 3 === 0 ? e : e < 0 ? e - 3 - e % 3 : e - e % 3;

  if (isNumber(precision)) {
    // add zeroes to give correct sig figs
    while (precision > c.length || e - newExp + 1 > c.length) {
      c.push(0);
    }
  } else {
    // concatenate coefficients with necessary zeros
    // add zeros if necessary (for example: 1e+8 -> 100e+6)
    var missingZeros = Math.abs(e - newExp) - (c.length - 1);

    for (var i = 0; i < missingZeros; i++) {
      c.push(0);
    }
  } // find difference in exponents


  var expDiff = Math.abs(e - newExp);
  var decimalIdx = 1; // push decimal index over by expDiff times

  while (expDiff > 0) {
    decimalIdx++;
    expDiff--;
  } // if all coefficient values are zero after the decimal point and precision is unset, don't add a decimal value.
  // otherwise concat with the rest of the coefficients


  var decimals = c.slice(decimalIdx).join('');
  var decimalVal = isNumber(precision) && decimals.length || decimals.match(/[1-9]/) ? '.' + decimals : '';
  var str = c.slice(0, decimalIdx).join('') + decimalVal + 'e' + (e >= 0 ? '+' : '') + newExp.toString();
  return rounded.sign + str;
}
/**
 * Format a number with fixed notation.
 * @param {number | string} value
 * @param {number} [precision=undefined]  Optional number of decimals after the
 *                                        decimal point. null by default.
 */

function toFixed(value, precision) {
  if (isNaN(value) || !isFinite(value)) {
    return String(value);
  }

  var splitValue = splitNumber(value);
  var rounded = typeof precision === 'number' ? roundDigits(splitValue, splitValue.exponent + 1 + precision) : splitValue;
  var c = rounded.coefficients;
  var p = rounded.exponent + 1; // exponent may have changed
  // append zeros if needed

  var pp = p + (precision || 0);

  if (c.length < pp) {
    c = c.concat(zeros(pp - c.length));
  } // prepend zeros if needed


  if (p < 0) {
    c = zeros(-p + 1).concat(c);
    p = 1;
  } // insert a dot if needed


  if (p < c.length) {
    c.splice(p, 0, p === 0 ? '0.' : '.');
  }

  return rounded.sign + c.join('');
}
/**
 * Format a number in exponential notation. Like '1.23e+5', '2.3e+0', '3.500e-3'
 * @param {number | string} value
 * @param {number} [precision]  Number of digits in formatted output.
 *                              If not provided, the maximum available digits
 *                              is used.
 */

function toExponential(value, precision) {
  if (isNaN(value) || !isFinite(value)) {
    return String(value);
  } // round if needed, else create a clone


  var split = splitNumber(value);
  var rounded = precision ? roundDigits(split, precision) : split;
  var c = rounded.coefficients;
  var e = rounded.exponent; // append zeros if needed

  if (c.length < precision) {
    c = c.concat(zeros(precision - c.length));
  } // format as `C.CCCe+EEE` or `C.CCCe-EEE`


  var first = c.shift();
  return rounded.sign + first + (c.length > 0 ? '.' + c.join('') : '') + 'e' + (e >= 0 ? '+' : '') + e;
}
/**
 * Format a number with a certain precision
 * @param {number | string} value
 * @param {number} [precision=undefined] Optional number of digits.
 * @param {{lowerExp: number | undefined, upperExp: number | undefined}} [options]
 *                                       By default:
 *                                         lowerExp = -3 (incl)
 *                                         upper = +5 (excl)
 * @return {string}
 */

function toPrecision(value, precision, options) {
  if (isNaN(value) || !isFinite(value)) {
    return String(value);
  } // determine lower and upper bound for exponential notation.


  var lowerExp = options && options.lowerExp !== undefined ? options.lowerExp : -3;
  var upperExp = options && options.upperExp !== undefined ? options.upperExp : 5;
  var split = splitNumber(value);
  var rounded = precision ? roundDigits(split, precision) : split;

  if (rounded.exponent < lowerExp || rounded.exponent >= upperExp) {
    // exponential notation
    return toExponential(value, precision);
  } else {
    var c = rounded.coefficients;
    var e = rounded.exponent; // append trailing zeros

    if (c.length < precision) {
      c = c.concat(zeros(precision - c.length));
    } // append trailing zeros
    // TODO: simplify the next statement


    c = c.concat(zeros(e - c.length + 1 + (c.length < precision ? precision - c.length : 0))); // prepend zeros

    c = zeros(-e).concat(c);
    var dot = e > 0 ? e : 0;

    if (dot < c.length - 1) {
      c.splice(dot + 1, 0, '.');
    }

    return rounded.sign + c.join('');
  }
}
/**
 * Round the number of digits of a number *
 * @param {SplitValue} split       A value split with .splitNumber(value)
 * @param {number} precision  A positive integer
 * @return {SplitValue}
 *              Returns an object containing sign, coefficients, and exponent
 *              with rounded digits
 */

function roundDigits(split, precision) {
  // create a clone
  var rounded = {
    sign: split.sign,
    coefficients: split.coefficients,
    exponent: split.exponent
  };
  var c = rounded.coefficients; // prepend zeros if needed

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
/**
 * Create an array filled with zeros.
 * @param {number} length
 * @return {Array}
 */

function zeros(length) {
  var arr = [];

  for (var i = 0; i < length; i++) {
    arr.push(0);
  }

  return arr;
}
/**
 * Count the number of significant digits of a number.
 *
 * For example:
 *   2.34 returns 3
 *   0.0034 returns 2
 *   120.5e+30 returns 4
 *
 * @param {number} value
 * @return {number} digits   Number of significant digits
 */


function digits(value) {
  return value.toExponential().replace(/e.*$/, '') // remove exponential notation
  .replace(/^0\.?0*|\./, '') // remove decimal point and leading zeros
  .length;
}
/**
 * Minimum number added to one that makes the result different than one
 */

var DBL_EPSILON = Number.EPSILON || 2.2204460492503130808472633361816E-16;
/**
 * Compares two floating point numbers.
 * @param {number} x          First value to compare
 * @param {number} y          Second value to compare
 * @param {number} [epsilon]  The maximum relative difference between x and y
 *                            If epsilon is undefined or null, the function will
 *                            test whether x and y are exactly equal.
 * @return {boolean} whether the two numbers are nearly equal
*/

function nearlyEqual(x, y, epsilon) {
  // if epsilon is null or undefined, test whether x and y are exactly equal
  if (epsilon === null || epsilon === undefined) {
    return x === y;
  }

  if (x === y) {
    return true;
  } // NaN


  if (isNaN(x) || isNaN(y)) {
    return false;
  } // at this point x and y should be finite


  if (isFinite(x) && isFinite(y)) {
    // check numbers are very close, needed when comparing numbers near zero
    var diff = Math.abs(x - y);

    if (diff < DBL_EPSILON) {
      return true;
    } else {
      // use relative error
      return diff <= Math.max(Math.abs(x), Math.abs(y)) * epsilon;
    }
  } // Infinite and Number or negative Infinite and positive Infinite cases


  return false;
}
/**
 * Calculate the hyperbolic arccos of a number
 * @param {number} x
 * @return {number}
 */

var acosh = Math.acosh || function (x) {
  return Math.log(Math.sqrt(x * x - 1) + x);
};
var asinh = Math.asinh || function (x) {
  return Math.log(Math.sqrt(x * x + 1) + x);
};
/**
 * Calculate the hyperbolic arctangent of a number
 * @param {number} x
 * @return {number}
 */

var atanh = Math.atanh || function (x) {
  return Math.log((1 + x) / (1 - x)) / 2;
};
/**
 * Calculate the hyperbolic cosine of a number
 * @param {number} x
 * @returns {number}
 */

var cosh = Math.cosh || function (x) {
  return (Math.exp(x) + Math.exp(-x)) / 2;
};
/**
 * Calculate the hyperbolic sine of a number
 * @param {number} x
 * @returns {number}
 */

var sinh = Math.sinh || function (x) {
  return (Math.exp(x) - Math.exp(-x)) / 2;
};
/**
 * Calculate the hyperbolic tangent of a number
 * @param {number} x
 * @returns {number}
 */

var tanh = Math.tanh || function (x) {
  var e = Math.exp(2 * x);
  return (e - 1) / (e + 1);
};
/**
 * Returns a value with the magnitude of x and the sign of y.
 * @param {number} x
 * @param {number} y
 * @returns {number}
 */

function copysign(x, y) {
  var signx = x > 0 ? true : x < 0 ? false : 1 / x === Infinity;
  var signy = y > 0 ? true : y < 0 ? false : 1 / y === Infinity;
  return signx ^ signy ? -x : x;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/bignumber/formatter.js

/**
 * Formats a BigNumber in a given base
 * @param {BigNumber} n
 * @param {number} base
 * @param {number} size
 * @returns {string}
 */

function formatBigNumberToBase(n, base, size) {
  var BigNumberCtor = n.constructor;
  var big2 = new BigNumberCtor(2);
  var suffix = '';

  if (size) {
    if (size < 1) {
      throw new Error('size must be in greater than 0');
    }

    if (!isInteger(size)) {
      throw new Error('size must be an integer');
    }

    if (n.greaterThan(big2.pow(size - 1).sub(1)) || n.lessThan(big2.pow(size - 1).mul(-1))) {
      throw new Error("Value must be in range [-2^".concat(size - 1, ", 2^").concat(size - 1, "-1]"));
    }

    if (!n.isInteger()) {
      throw new Error('Value must be an integer');
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
/**
 * Convert a BigNumber to a formatted string representation.
 *
 * Syntax:
 *
 *    format(value)
 *    format(value, options)
 *    format(value, precision)
 *    format(value, fn)
 *
 * Where:
 *
 *    {number} value   The value to be formatted
 *    {Object} options An object with formatting options. Available options:
 *                     {string} notation
 *                         Number notation. Choose from:
 *                         'fixed'          Always use regular number notation.
 *                                          For example '123.40' and '14000000'
 *                         'exponential'    Always use exponential notation.
 *                                          For example '1.234e+2' and '1.4e+7'
 *                         'auto' (default) Regular number notation for numbers
 *                                          having an absolute value between
 *                                          `lower` and `upper` bounds, and uses
 *                                          exponential notation elsewhere.
 *                                          Lower bound is included, upper bound
 *                                          is excluded.
 *                                          For example '123.4' and '1.4e7'.
 *                         'bin', 'oct, or
 *                         'hex'            Format the number using binary, octal,
 *                                          or hexadecimal notation.
 *                                          For example '0b1101' and '0x10fe'.
 *                     {number} wordSize    The word size in bits to use for formatting
 *                                          in binary, octal, or hexadecimal notation.
 *                                          To be used only with 'bin', 'oct', or 'hex'
 *                                          values for 'notation' option. When this option
 *                                          is defined the value is formatted as a signed
 *                                          twos complement integer of the given word size
 *                                          and the size suffix is appended to the output.
 *                                          For example
 *                                          format(-1, {notation: 'hex', wordSize: 8}) === '0xffi8'.
 *                                          Default value is undefined.
 *                     {number} precision   A number between 0 and 16 to round
 *                                          the digits of the number.
 *                                          In case of notations 'exponential',
 *                                          'engineering', and 'auto',
 *                                          `precision` defines the total
 *                                          number of significant digits returned.
 *                                          In case of notation 'fixed',
 *                                          `precision` defines the number of
 *                                          significant digits after the decimal
 *                                          point.
 *                                          `precision` is undefined by default.
 *                     {number} lowerExp    Exponent determining the lower boundary
 *                                          for formatting a value with an exponent
 *                                          when `notation='auto`.
 *                                          Default value is `-3`.
 *                     {number} upperExp    Exponent determining the upper boundary
 *                                          for formatting a value with an exponent
 *                                          when `notation='auto`.
 *                                          Default value is `5`.
 *    {Function} fn    A custom formatting function. Can be used to override the
 *                     built-in notations. Function `fn` is called with `value` as
 *                     parameter and must return a string. Is useful for example to
 *                     format all values inside a matrix in a particular way.
 *
 * Examples:
 *
 *    format(6.4)                                        // '6.4'
 *    format(1240000)                                    // '1.24e6'
 *    format(1/3)                                        // '0.3333333333333333'
 *    format(1/3, 3)                                     // '0.333'
 *    format(21385, 2)                                   // '21000'
 *    format(12e8, {notation: 'fixed'})                  // returns '1200000000'
 *    format(2.3,    {notation: 'fixed', precision: 4})  // returns '2.3000'
 *    format(52.8,   {notation: 'exponential'})          // returns '5.28e+1'
 *    format(12400,  {notation: 'engineering'})          // returns '12.400e+3'
 *
 * @param {BigNumber} value
 * @param {Object | Function | number} [options]
 * @return {string} str The formatted value
 */


function formatter_format(value, options) {
  if (typeof options === 'function') {
    // handle format(value, fn)
    return options(value);
  } // handle special cases


  if (!value.isFinite()) {
    return value.isNaN() ? 'NaN' : value.gt(0) ? 'Infinity' : '-Infinity';
  } // default values for options


  var notation = 'auto';
  var precision;
  var wordSize;

  if (options !== undefined) {
    // determine notation from options
    if (options.notation) {
      notation = options.notation;
    } // determine precision from options


    if (typeof options === 'number') {
      precision = options;
    } else if (options.precision) {
      precision = options.precision;
    }

    if (options.wordSize) {
      wordSize = options.wordSize;

      if (typeof wordSize !== 'number') {
        throw new Error('Option "wordSize" must be a number');
      }
    }
  } // handle the various notations


  switch (notation) {
    case 'fixed':
      return formatter_toFixed(value, precision);

    case 'exponential':
      return formatter_toExponential(value, precision);

    case 'engineering':
      return formatter_toEngineering(value, precision);

    case 'bin':
      return formatBigNumberToBase(value, 2, wordSize);

    case 'oct':
      return formatBigNumberToBase(value, 8, wordSize);

    case 'hex':
      return formatBigNumberToBase(value, 16, wordSize);

    case 'auto':
      {
        // determine lower and upper bound for exponential notation.
        // TODO: implement support for upper and lower to be BigNumbers themselves
        var lowerExp = options && options.lowerExp !== undefined ? options.lowerExp : -3;
        var upperExp = options && options.upperExp !== undefined ? options.upperExp : 5; // handle special case zero

        if (value.isZero()) return '0'; // determine whether or not to output exponential notation

        var str;
        var rounded = value.toSignificantDigits(precision);
        var exp = rounded.e;

        if (exp >= lowerExp && exp < upperExp) {
          // normal number notation
          str = rounded.toFixed();
        } else {
          // exponential notation
          str = formatter_toExponential(value, precision);
        } // remove trailing zeros after the decimal point


        return str.replace(/((\.\d*?)(0+))($|e)/, function () {
          var digits = arguments[2];
          var e = arguments[4];
          return digits !== '.' ? digits + e : e;
        });
      }

    default:
      throw new Error('Unknown notation "' + notation + '". ' + 'Choose "auto", "exponential", "fixed", "bin", "oct", or "hex.');
  }
}
/**
 * Format a BigNumber in engineering notation. Like '1.23e+6', '2.3e+0', '3.500e-3'
 * @param {BigNumber | string} value
 * @param {number} [precision]        Optional number of significant figures to return.
 */

function formatter_toEngineering(value, precision) {
  // find nearest lower multiple of 3 for exponent
  var e = value.e;
  var newExp = e % 3 === 0 ? e : e < 0 ? e - 3 - e % 3 : e - e % 3; // find difference in exponents, and calculate the value without exponent

  var valueWithoutExp = value.mul(Math.pow(10, -newExp));
  var valueStr = valueWithoutExp.toPrecision(precision);

  if (valueStr.indexOf('e') !== -1) {
    valueStr = valueWithoutExp.toString();
  }

  return valueStr + 'e' + (e >= 0 ? '+' : '') + newExp.toString();
}
/**
 * Format a number in exponential notation. Like '1.23e+5', '2.3e+0', '3.500e-3'
 * @param {BigNumber} value
 * @param {number} [precision]  Number of digits in formatted output.
 *                              If not provided, the maximum available digits
 *                              is used.
 * @returns {string} str
 */

function formatter_toExponential(value, precision) {
  if (precision !== undefined) {
    return value.toExponential(precision - 1); // Note the offset of one
  } else {
    return value.toExponential();
  }
}
/**
 * Format a number with fixed notation.
 * @param {BigNumber} value
 * @param {number} [precision=undefined] Optional number of decimals after the
 *                                       decimal point. Undefined by default.
 */

function formatter_toFixed(value, precision) {
  return value.toFixed(precision);
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/string.js



/**
 * Check if a text ends with a certain string.
 * @param {string} text
 * @param {string} search
 */

function endsWith(text, search) {
  var start = text.length - search.length;
  var end = text.length;
  return text.substring(start, end) === search;
}
/**
 * Format a value of any type into a string.
 *
 * Usage:
 *     math.format(value)
 *     math.format(value, precision)
 *     math.format(value, options)
 *
 * When value is a function:
 *
 * - When the function has a property `syntax`, it returns this
 *   syntax description.
 * - In other cases, a string `'function'` is returned.
 *
 * When `value` is an Object:
 *
 * - When the object contains a property `format` being a function, this
 *   function is invoked as `value.format(options)` and the result is returned.
 * - When the object has its own `toString` method, this method is invoked
 *   and the result is returned.
 * - In other cases the function will loop over all object properties and
 *   return JSON object notation like '{"a": 2, "b": 3}'.
 *
 * Example usage:
 *     math.format(2/7)                // '0.2857142857142857'
 *     math.format(math.pi, 3)         // '3.14'
 *     math.format(new Complex(2, 3))  // '2 + 3i'
 *     math.format('hello')            // '"hello"'
 *
 * @param {*} value             Value to be stringified
 * @param {Object | number | Function} [options]
 *     Formatting options. See src/utils/number.js:format for a
 *     description of the available options controlling number output.
 *     This generic "format" also supports the option property `truncate: NN`
 *     giving the maximum number NN of characters to return (if there would
 *     have been more, they are deleted and replaced by an ellipsis).
 * @return {string} str
 */

function string_format(value, options) {
  var result = _format(value, options);

  if (options && typeof options === 'object' && 'truncate' in options && result.length > options.truncate) {
    return result.substring(0, options.truncate - 3) + '...';
  }

  return result;
}

function _format(value, options) {
  if (typeof value === 'number') {
    return format(value, options);
  }

  if (isBigNumber(value)) {
    return formatter_format(value, options);
  } // note: we use unsafe duck-typing here to check for Fractions, this is
  // ok here since we're only invoking toString or concatenating its values


  if (looksLikeFraction(value)) {
    if (!options || options.fraction !== 'decimal') {
      // output as ratio, like '1/3'
      return value.s * value.n + '/' + value.d;
    } else {
      // output as decimal, like '0.(3)'
      return value.toString();
    }
  }

  if (Array.isArray(value)) {
    return formatArray(value, options);
  }

  if (isString(value)) {
    return '"' + value + '"';
  }

  if (typeof value === 'function') {
    return value.syntax ? String(value.syntax) : 'function';
  }

  if (value && typeof value === 'object') {
    if (typeof value.format === 'function') {
      return value.format(options);
    } else if (value && value.toString(options) !== {}.toString()) {
      // this object has a non-native toString method, use that one
      return value.toString(options);
    } else {
      var entries = Object.keys(value).map(key => {
        return '"' + key + '": ' + string_format(value[key], options);
      });
      return '{' + entries.join(', ') + '}';
    }
  }

  return String(value);
}
/**
 * Stringify a value into a string enclosed in double quotes.
 * Unescaped double quotes and backslashes inside the value are escaped.
 * @param {*} value
 * @return {string}
 */


function stringify(value) {
  var text = String(value);
  var escaped = '';
  var i = 0;

  while (i < text.length) {
    var c = text.charAt(i);

    if (c === '\\') {
      escaped += c;
      i++;
      c = text.charAt(i);

      if (c === '' || '"\\/bfnrtu'.indexOf(c) === -1) {
        escaped += '\\'; // no valid escape character -> escape it
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
/**
 * Escape special HTML characters
 * @param {*} value
 * @return {string}
 */

function string_escape(value) {
  var text = String(value);
  text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  return text;
}
/**
 * Recursively format an n-dimensional matrix
 * Example output: "[[1, 2], [3, 4]]"
 * @param {Array} array
 * @param {Object | number | Function} [options]  Formatting options. See
 *                                                lib/utils/number:format for a
 *                                                description of the available
 *                                                options.
 * @returns {string} str
 */

function formatArray(array, options) {
  if (Array.isArray(array)) {
    var str = '[';
    var len = array.length;

    for (var i = 0; i < len; i++) {
      if (i !== 0) {
        str += ', ';
      }

      str += formatArray(array[i], options);
    }

    str += ']';
    return str;
  } else {
    return string_format(array, options);
  }
}
/**
 * Check whether a value looks like a Fraction (unsafe duck-type check)
 * @param {*} value
 * @return {boolean}
 */


function looksLikeFraction(value) {
  return value && typeof value === 'object' && typeof value.s === 'number' && typeof value.n === 'number' && typeof value.d === 'number' || false;
}
/**
 * Compare two strings
 * @param {string} x
 * @param {string} y
 * @returns {number}
 */


function compareText(x, y) {
  // we don't want to convert numbers to string, only accept string input
  if (!isString(x)) {
    throw new TypeError('Unexpected type of argument in function compareText ' + '(expected: string or Array or Matrix, actual: ' + typeOf(x) + ', index: 0)');
  }

  if (!isString(y)) {
    throw new TypeError('Unexpected type of argument in function compareText ' + '(expected: string or Array or Matrix, actual: ' + typeOf(y) + ', index: 1)');
  }

  return x === y ? 0 : x > y ? 1 : -1;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/error/DimensionError.js
/**
 * Create a range error with the message:
 *     'Dimension mismatch (<actual size> != <expected size>)'
 * @param {number | number[]} actual        The actual size
 * @param {number | number[]} expected      The expected size
 * @param {string} [relation='!=']          Optional relation between actual
 *                                          and expected size: '!=', '<', etc.
 * @extends RangeError
 */
function DimensionError(actual, expected, relation) {
  if (!(this instanceof DimensionError)) {
    throw new SyntaxError('Constructor must be called with the new operator');
  }

  this.actual = actual;
  this.expected = expected;
  this.relation = relation;
  this.message = 'Dimension mismatch (' + (Array.isArray(actual) ? '[' + actual.join(', ') + ']' : actual) + ' ' + (this.relation || '!=') + ' ' + (Array.isArray(expected) ? '[' + expected.join(', ') + ']' : expected) + ')';
  this.stack = new Error().stack;
}
DimensionError.prototype = new RangeError();
DimensionError.prototype.constructor = RangeError;
DimensionError.prototype.name = 'DimensionError';
DimensionError.prototype.isDimensionError = true;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/error/IndexError.js
/**
 * Create a range error with the message:
 *     'Index out of range (index < min)'
 *     'Index out of range (index < max)'
 *
 * @param {number} index     The actual index
 * @param {number} [min=0]   Minimum index (included)
 * @param {number} [max]     Maximum index (excluded)
 * @extends RangeError
 */
function IndexError(index, min, max) {
  if (!(this instanceof IndexError)) {
    throw new SyntaxError('Constructor must be called with the new operator');
  }

  this.index = index;

  if (arguments.length < 3) {
    this.min = 0;
    this.max = min;
  } else {
    this.min = min;
    this.max = max;
  }

  if (this.min !== undefined && this.index < this.min) {
    this.message = 'Index out of range (' + this.index + ' < ' + this.min + ')';
  } else if (this.max !== undefined && this.index >= this.max) {
    this.message = 'Index out of range (' + this.index + ' > ' + (this.max - 1) + ')';
  } else {
    this.message = 'Index out of range (' + this.index + ')';
  }

  this.stack = new Error().stack;
}
IndexError.prototype = new RangeError();
IndexError.prototype.constructor = RangeError;
IndexError.prototype.name = 'IndexError';
IndexError.prototype.isIndexError = true;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/array.js





/**
 * Calculate the size of a multi dimensional array.
 * This function checks the size of the first entry, it does not validate
 * whether all dimensions match. (use function `validate` for that)
 * @param {Array} x
 * @Return {Number[]} size
 */

function arraySize(x) {
  var s = [];

  while (Array.isArray(x)) {
    s.push(x.length);
    x = x[0];
  }

  return s;
}
/**
 * Recursively validate whether each element in a multi dimensional array
 * has a size corresponding to the provided size array.
 * @param {Array} array    Array to be validated
 * @param {number[]} size  Array with the size of each dimension
 * @param {number} dim   Current dimension
 * @throws DimensionError
 * @private
 */

function _validate(array, size, dim) {
  var i;
  var len = array.length;

  if (len !== size[dim]) {
    throw new DimensionError(len, size[dim]);
  }

  if (dim < size.length - 1) {
    // recursively validate each child array
    var dimNext = dim + 1;

    for (i = 0; i < len; i++) {
      var child = array[i];

      if (!Array.isArray(child)) {
        throw new DimensionError(size.length - 1, size.length, '<');
      }

      _validate(array[i], size, dimNext);
    }
  } else {
    // last dimension. none of the childs may be an array
    for (i = 0; i < len; i++) {
      if (Array.isArray(array[i])) {
        throw new DimensionError(size.length + 1, size.length, '>');
      }
    }
  }
}
/**
 * Validate whether each element in a multi dimensional array has
 * a size corresponding to the provided size array.
 * @param {Array} array    Array to be validated
 * @param {number[]} size  Array with the size of each dimension
 * @throws DimensionError
 */


function validate(array, size) {
  var isScalar = size.length === 0;

  if (isScalar) {
    // scalar
    if (Array.isArray(array)) {
      throw new DimensionError(array.length, 0);
    }
  } else {
    // array
    _validate(array, size, 0);
  }
}
/**
 * Test whether index is an integer number with index >= 0 and index < length
 * when length is provided
 * @param {number} index    Zero-based index
 * @param {number} [length] Length of the array
 */

function validateIndex(index, length) {
  if (!isNumber(index) || !isInteger(index)) {
    throw new TypeError('Index must be an integer (value: ' + index + ')');
  }

  if (index < 0 || typeof length === 'number' && index >= length) {
    throw new IndexError(index, length);
  }
}
/**
 * Resize a multi dimensional array. The resized array is returned.
 * @param {Array} array         Array to be resized
 * @param {Array.<number>} size Array with the size of each dimension
 * @param {*} [defaultValue=0]  Value to be filled in in new entries,
 *                              zero by default. Specify for example `null`,
 *                              to clearly see entries that are not explicitly
 *                              set.
 * @return {Array} array         The resized array
 */

function resize(array, size, defaultValue) {
  // TODO: add support for scalars, having size=[] ?
  // check the type of the arguments
  if (!Array.isArray(array) || !Array.isArray(size)) {
    throw new TypeError('Array expected');
  }

  if (size.length === 0) {
    throw new Error('Resizing to scalar is not supported');
  } // check whether size contains positive integers


  size.forEach(function (value) {
    if (!isNumber(value) || !isInteger(value) || value < 0) {
      throw new TypeError('Invalid size, must contain positive integers ' + '(size: ' + string_format(size) + ')');
    }
  }); // recursively resize the array

  var _defaultValue = defaultValue !== undefined ? defaultValue : 0;

  _resize(array, size, 0, _defaultValue);

  return array;
}
/**
 * Recursively resize a multi dimensional array
 * @param {Array} array         Array to be resized
 * @param {number[]} size       Array with the size of each dimension
 * @param {number} dim          Current dimension
 * @param {*} [defaultValue]    Value to be filled in in new entries,
 *                              undefined by default.
 * @private
 */

function _resize(array, size, dim, defaultValue) {
  var i;
  var elem;
  var oldLen = array.length;
  var newLen = size[dim];
  var minLen = Math.min(oldLen, newLen); // apply new length

  array.length = newLen;

  if (dim < size.length - 1) {
    // non-last dimension
    var dimNext = dim + 1; // resize existing child arrays

    for (i = 0; i < minLen; i++) {
      // resize child array
      elem = array[i];

      if (!Array.isArray(elem)) {
        elem = [elem]; // add a dimension

        array[i] = elem;
      }

      _resize(elem, size, dimNext, defaultValue);
    } // create new child arrays


    for (i = minLen; i < newLen; i++) {
      // get child array
      elem = [];
      array[i] = elem; // resize new child array

      _resize(elem, size, dimNext, defaultValue);
    }
  } else {
    // last dimension
    // remove dimensions of existing values
    for (i = 0; i < minLen; i++) {
      while (Array.isArray(array[i])) {
        array[i] = array[i][0];
      }
    } // fill new elements with the default value


    for (i = minLen; i < newLen; i++) {
      array[i] = defaultValue;
    }
  }
}
/**
 * Re-shape a multi dimensional array to fit the specified dimensions
 * @param {Array} array           Array to be reshaped
 * @param {Array.<number>} sizes  List of sizes for each dimension
 * @returns {Array}               Array whose data has been formatted to fit the
 *                                specified dimensions
 *
 * @throws {DimensionError}       If the product of the new dimension sizes does
 *                                not equal that of the old ones
 */


function reshape(array, sizes) {
  var flatArray = flatten(array);
  var currentLength = flatArray.length;

  if (!Array.isArray(array) || !Array.isArray(sizes)) {
    throw new TypeError('Array expected');
  }

  if (sizes.length === 0) {
    throw new DimensionError(0, currentLength, '!=');
  }

  sizes = processSizesWildcard(sizes, currentLength);
  var newLength = product(sizes);

  if (currentLength !== newLength) {
    throw new DimensionError(newLength, currentLength, '!=');
  }

  try {
    return _reshape(flatArray, sizes);
  } catch (e) {
    if (e instanceof DimensionError) {
      throw new DimensionError(newLength, currentLength, '!=');
    }

    throw e;
  }
}
/**
 * Replaces the wildcard -1 in the sizes array.
 * @param {Array.<number>} sizes  List of sizes for each dimension. At most on wildcard.
 * @param {number} currentLength  Number of elements in the array.
 * @throws {Error}                If more than one wildcard or unable to replace it.
 * @returns {Array.<number>}      The sizes array with wildcard replaced.
 */

function processSizesWildcard(sizes, currentLength) {
  var newLength = product(sizes);
  var processedSizes = sizes.slice();
  var WILDCARD = -1;
  var wildCardIndex = sizes.indexOf(WILDCARD);
  var isMoreThanOneWildcard = sizes.indexOf(WILDCARD, wildCardIndex + 1) >= 0;

  if (isMoreThanOneWildcard) {
    throw new Error('More than one wildcard in sizes');
  }

  var hasWildcard = wildCardIndex >= 0;
  var canReplaceWildcard = currentLength % newLength === 0;

  if (hasWildcard) {
    if (canReplaceWildcard) {
      processedSizes[wildCardIndex] = -currentLength / newLength;
    } else {
      throw new Error('Could not replace wildcard, since ' + currentLength + ' is no multiple of ' + -newLength);
    }
  }

  return processedSizes;
}
/**
 * Computes the product of all array elements.
 * @param {Array<number>} array Array of factors
 * @returns {number}            Product of all elements
 */

function product(array) {
  return array.reduce((prev, curr) => prev * curr, 1);
}
/**
 * Iteratively re-shape a multi dimensional array to fit the specified dimensions
 * @param {Array} array           Array to be reshaped
 * @param {Array.<number>} sizes  List of sizes for each dimension
 * @returns {Array}               Array whose data has been formatted to fit the
 *                                specified dimensions
 */


function _reshape(array, sizes) {
  // testing if there are enough elements for the requested shape
  var tmpArray = array;
  var tmpArray2; // for each dimensions starting by the last one and ignoring the first one

  for (var sizeIndex = sizes.length - 1; sizeIndex > 0; sizeIndex--) {
    var size = sizes[sizeIndex];
    tmpArray2 = []; // aggregate the elements of the current tmpArray in elements of the requested size

    var length = tmpArray.length / size;

    for (var i = 0; i < length; i++) {
      tmpArray2.push(tmpArray.slice(i * size, (i + 1) * size));
    } // set it as the new tmpArray for the next loop turn or for return


    tmpArray = tmpArray2;
  }

  return tmpArray;
}
/**
 * Squeeze a multi dimensional array
 * @param {Array} array
 * @param {Array} [size]
 * @returns {Array} returns the array itself
 */


function squeeze(array, size) {
  var s = size || arraySize(array); // squeeze outer dimensions

  while (Array.isArray(array) && array.length === 1) {
    array = array[0];
    s.shift();
  } // find the first dimension to be squeezed


  var dims = s.length;

  while (s[dims - 1] === 1) {
    dims--;
  } // squeeze inner dimensions


  if (dims < s.length) {
    array = _squeeze(array, dims, 0);
    s.length = dims;
  }

  return array;
}
/**
 * Recursively squeeze a multi dimensional array
 * @param {Array} array
 * @param {number} dims Required number of dimensions
 * @param {number} dim  Current dimension
 * @returns {Array | *} Returns the squeezed array
 * @private
 */

function _squeeze(array, dims, dim) {
  var i, ii;

  if (dim < dims) {
    var next = dim + 1;

    for (i = 0, ii = array.length; i < ii; i++) {
      array[i] = _squeeze(array[i], dims, next);
    }
  } else {
    while (Array.isArray(array)) {
      array = array[0];
    }
  }

  return array;
}
/**
 * Unsqueeze a multi dimensional array: add dimensions when missing
 *
 * Paramter `size` will be mutated to match the new, unqueezed matrix size.
 *
 * @param {Array} array
 * @param {number} dims       Desired number of dimensions of the array
 * @param {number} [outer]    Number of outer dimensions to be added
 * @param {Array} [size] Current size of array.
 * @returns {Array} returns the array itself
 * @private
 */


function unsqueeze(array, dims, outer, size) {
  var s = size || arraySize(array); // unsqueeze outer dimensions

  if (outer) {
    for (var i = 0; i < outer; i++) {
      array = [array];
      s.unshift(1);
    }
  } // unsqueeze inner dimensions


  array = _unsqueeze(array, dims, 0);

  while (s.length < dims) {
    s.push(1);
  }

  return array;
}
/**
 * Recursively unsqueeze a multi dimensional array
 * @param {Array} array
 * @param {number} dims Required number of dimensions
 * @param {number} dim  Current dimension
 * @returns {Array | *} Returns the squeezed array
 * @private
 */

function _unsqueeze(array, dims, dim) {
  var i, ii;

  if (Array.isArray(array)) {
    var next = dim + 1;

    for (i = 0, ii = array.length; i < ii; i++) {
      array[i] = _unsqueeze(array[i], dims, next);
    }
  } else {
    for (var d = dim; d < dims; d++) {
      array = [array];
    }
  }

  return array;
}
/**
 * Flatten a multi dimensional array, put all elements in a one dimensional
 * array
 * @param {Array} array   A multi dimensional array
 * @return {Array}        The flattened array (1 dimensional)
 */


function flatten(array) {
  if (!Array.isArray(array)) {
    // if not an array, return as is
    return array;
  }

  var flat = [];
  array.forEach(function callback(value) {
    if (Array.isArray(value)) {
      value.forEach(callback); // traverse through sub-arrays recursively
    } else {
      flat.push(value);
    }
  });
  return flat;
}
/**
 * A safe map
 * @param {Array} array
 * @param {function} callback
 */

function array_map(array, callback) {
  return Array.prototype.map.call(array, callback);
}
/**
 * A safe forEach
 * @param {Array} array
 * @param {function} callback
 */

function forEach(array, callback) {
  Array.prototype.forEach.call(array, callback);
}
/**
 * A safe filter
 * @param {Array} array
 * @param {function} callback
 */

function filter(array, callback) {
  if (arraySize(array).length !== 1) {
    throw new Error('Only one dimensional matrices supported');
  }

  return Array.prototype.filter.call(array, callback);
}
/**
 * Filter values in a callback given a regular expression
 * @param {Array} array
 * @param {RegExp} regexp
 * @return {Array} Returns the filtered array
 * @private
 */

function filterRegExp(array, regexp) {
  if (arraySize(array).length !== 1) {
    throw new Error('Only one dimensional matrices supported');
  }

  return Array.prototype.filter.call(array, entry => regexp.test(entry));
}
/**
 * A safe join
 * @param {Array} array
 * @param {string} separator
 */

function join(array, separator) {
  return Array.prototype.join.call(array, separator);
}
/**
 * Assign a numeric identifier to every element of a sorted array
 * @param {Array} a  An array
 * @return {Array} An array of objects containing the original value and its identifier
 */

function identify(a) {
  if (!Array.isArray(a)) {
    throw new TypeError('Array input expected');
  }

  if (a.length === 0) {
    return a;
  }

  var b = [];
  var count = 0;
  b[0] = {
    value: a[0],
    identifier: 0
  };

  for (var i = 1; i < a.length; i++) {
    if (a[i] === a[i - 1]) {
      count++;
    } else {
      count = 0;
    }

    b.push({
      value: a[i],
      identifier: count
    });
  }

  return b;
}
/**
 * Remove the numeric identifier from the elements
 * @param {array} a  An array
 * @return {array} An array of values without identifiers
 */

function generalize(a) {
  if (!Array.isArray(a)) {
    throw new TypeError('Array input expected');
  }

  if (a.length === 0) {
    return a;
  }

  var b = [];

  for (var i = 0; i < a.length; i++) {
    b.push(a[i].value);
  }

  return b;
}
/**
 * Check the datatype of a given object
 * This is a low level implementation that should only be used by
 * parent Matrix classes such as SparseMatrix or DenseMatrix
 * This method does not validate Array Matrix shape
 * @param {Array} array
 * @param {function} typeOf   Callback function to use to determine the type of a value
 * @return {string}
 */

function getArrayDataType(array, typeOf) {
  var type; // to hold type info

  var length = 0; // to hold length value to ensure it has consistent sizes

  for (var i = 0; i < array.length; i++) {
    var item = array[i];
    var isArray = Array.isArray(item); // Saving the target matrix row size

    if (i === 0 && isArray) {
      length = item.length;
    } // If the current item is an array but the length does not equal the targetVectorSize


    if (isArray && item.length !== length) {
      return undefined;
    }

    var itemType = isArray ? getArrayDataType(item, typeOf) // recurse into a nested array
    : typeOf(item);

    if (type === undefined) {
      type = itemType; // first item
    } else if (type !== itemType) {
      return 'mixed';
    } else {// we're good, everything has the same type so far
    }
  }

  return type;
}
/**
 * Return the last item from an array
 * @param array
 * @returns {*}
 */

function array_last(array) {
  return array[array.length - 1];
}
/**
 * Get all but the last element of array.
 */

function initial(array) {
  return array.slice(0, array.length - 1);
}
/**
 * Test whether an array or string contains an item
 * @param {Array | string} array
 * @param {*} item
 * @return {boolean}
 */

function contains(array, item) {
  return array.indexOf(item) !== -1;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/factory.js


/**
 * Create a factory function, which can be used to inject dependencies.
 *
 * The created functions are memoized, a consecutive call of the factory
 * with the exact same inputs will return the same function instance.
 * The memoized cache is exposed on `factory.cache` and can be cleared
 * if needed.
 *
 * Example:
 *
 *     const name = 'log'
 *     const dependencies = ['config', 'typed', 'divideScalar', 'Complex']
 *
 *     export const createLog = factory(name, dependencies, ({ typed, config, divideScalar, Complex }) => {
 *       // ... create the function log here and return it
 *     }
 *
 * @param {string} name           Name of the function to be created
 * @param {string[]} dependencies The names of all required dependencies
 * @param {function} create       Callback function called with an object with all dependencies
 * @param {Object} [meta]         Optional object with meta information that will be attached
 *                                to the created factory function as property `meta`.
 * @returns {function}
 */

function factory_factory(name, dependencies, create, meta) {
  function assertAndCreate(scope) {
    // we only pass the requested dependencies to the factory function
    // to prevent functions to rely on dependencies that are not explicitly
    // requested.
    var deps = pickShallow(scope, dependencies.map(stripOptionalNotation));
    assertDependencies(name, dependencies, scope);
    return create(deps);
  }

  assertAndCreate.isFactory = true;
  assertAndCreate.fn = name;
  assertAndCreate.dependencies = dependencies.slice().sort();

  if (meta) {
    assertAndCreate.meta = meta;
  }

  return assertAndCreate;
}
/**
 * Sort all factories such that when loading in order, the dependencies are resolved.
 *
 * @param {Array} factories
 * @returns {Array} Returns a new array with the sorted factories.
 */

function sortFactories(factories) {
  var factoriesByName = {};
  factories.forEach(factory => {
    factoriesByName[factory.fn] = factory;
  });

  function containsDependency(factory, dependency) {
    // TODO: detect circular references
    if (isFactory(factory)) {
      if (contains(factory.dependencies, dependency.fn || dependency.name)) {
        return true;
      }

      if (factory.dependencies.some(d => containsDependency(factoriesByName[d], dependency))) {
        return true;
      }
    }

    return false;
  }

  var sorted = [];

  function addFactory(factory) {
    var index = 0;

    while (index < sorted.length && !containsDependency(sorted[index], factory)) {
      index++;
    }

    sorted.splice(index, 0, factory);
  } // sort regular factory functions


  factories.filter(isFactory).forEach(addFactory); // sort legacy factory functions AFTER the regular factory functions

  factories.filter(factory => !isFactory(factory)).forEach(addFactory);
  return sorted;
} // TODO: comment or cleanup if unused in the end

function factory_create(factories) {
  var scope = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  sortFactories(factories).forEach(factory => factory(scope));
  return scope;
}
/**
 * Test whether an object is a factory. This is the case when it has
 * properties name, dependencies, and a function create.
 * @param {*} obj
 * @returns {boolean}
 */

function isFactory(obj) {
  return typeof obj === 'function' && typeof obj.fn === 'string' && Array.isArray(obj.dependencies);
}
/**
 * Assert that all dependencies of a list with dependencies are available in the provided scope.
 *
 * Will throw an exception when there are dependencies missing.
 *
 * @param {string} name   Name for the function to be created. Used to generate a useful error message
 * @param {string[]} dependencies
 * @param {Object} scope
 */

function assertDependencies(name, dependencies, scope) {
  var allDefined = dependencies.filter(dependency => !isOptionalDependency(dependency)) // filter optionals
  .every(dependency => scope[dependency] !== undefined);

  if (!allDefined) {
    var missingDependencies = dependencies.filter(dependency => scope[dependency] === undefined); // TODO: create a custom error class for this, a MathjsError or something like that

    throw new Error("Cannot create function \"".concat(name, "\", ") + "some dependencies are missing: ".concat(missingDependencies.map(d => "\"".concat(d, "\"")).join(', '), "."));
  }
}
function isOptionalDependency(dependency) {
  return dependency && dependency[0] === '?';
}
function stripOptionalNotation(dependency) {
  return dependency && dependency[0] === '?' ? dependency.slice(1) : dependency;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/error/ArgumentsError.js
/**
 * Create a syntax error with the message:
 *     'Wrong number of arguments in function <fn> (<count> provided, <min>-<max> expected)'
 * @param {string} fn     Function name
 * @param {number} count  Actual argument count
 * @param {number} min    Minimum required argument count
 * @param {number} [max]  Maximum required argument count
 * @extends Error
 */
function ArgumentsError(fn, count, min, max) {
  if (!(this instanceof ArgumentsError)) {
    throw new SyntaxError('Constructor must be called with the new operator');
  }

  this.fn = fn;
  this.count = count;
  this.min = min;
  this.max = max;
  this.message = 'Wrong number of arguments in function ' + fn + ' (' + count + ' provided, ' + min + (max !== undefined && max !== null ? '-' + max : '') + ' expected)';
  this.stack = new Error().stack;
}
ArgumentsError.prototype = new Error();
ArgumentsError.prototype.constructor = Error;
ArgumentsError.prototype.name = 'ArgumentsError';
ArgumentsError.prototype.isArgumentsError = true;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/core/function/import.js





function importFactory(typed, load, math, importedFactories) {
  /**
   * Import functions from an object or a module.
   *
   * This function is only available on a mathjs instance created using `create`.
   *
   * Syntax:
   *
   *    math.import(functions)
   *    math.import(functions, options)
   *
   * Where:
   *
   * - `functions: Object`
   *   An object with functions or factories to be imported.
   * - `options: Object` An object with import options. Available options:
   *   - `override: boolean`
   *     If true, existing functions will be overwritten. False by default.
   *   - `silent: boolean`
   *     If true, the function will not throw errors on duplicates or invalid
   *     types. False by default.
   *   - `wrap: boolean`
   *     If true, the functions will be wrapped in a wrapper function
   *     which converts data types like Matrix to primitive data types like Array.
   *     The wrapper is needed when extending math.js with libraries which do not
   *     support these data type. False by default.
   *
   * Examples:
   *
   *    import { create, all } from 'mathjs'
   *    import * as numbers from 'numbers'
   *
   *    // create a mathjs instance
   *    const math = create(all)
   *
   *    // define new functions and variables
   *    math.import({
   *      myvalue: 42,
   *      hello: function (name) {
   *        return 'hello, ' + name + '!'
   *      }
   *    })
   *
   *    // use the imported function and variable
   *    math.myvalue * 2               // 84
   *    math.hello('user')             // 'hello, user!'
   *
   *    // import the npm module 'numbers'
   *    // (must be installed first with `npm install numbers`)
   *    math.import(numbers, {wrap: true})
   *
   *    math.fibonacci(7) // returns 13
   *
   * @param {Object | Array} functions  Object with functions to be imported.
   * @param {Object} [options]          Import options.
   */
  function mathImport(functions, options) {
    var num = arguments.length;

    if (num !== 1 && num !== 2) {
      throw new ArgumentsError('import', num, 1, 2);
    }

    if (!options) {
      options = {};
    }

    function flattenImports(flatValues, value, name) {
      if (Array.isArray(value)) {
        value.forEach(item => flattenImports(flatValues, item));
      } else if (typeof value === 'object') {
        for (var _name in value) {
          if (object_hasOwnProperty(value, _name)) {
            flattenImports(flatValues, value[_name], _name);
          }
        }
      } else if (isFactory(value) || name !== undefined) {
        var flatName = isFactory(value) ? isTransformFunctionFactory(value) ? value.fn + '.transform' // TODO: this is ugly
        : value.fn : name; // we allow importing the same function twice if it points to the same implementation

        if (object_hasOwnProperty(flatValues, flatName) && flatValues[flatName] !== value && !options.silent) {
          throw new Error('Cannot import "' + flatName + '" twice');
        }

        flatValues[flatName] = value;
      } else {
        if (!options.silent) {
          throw new TypeError('Factory, Object, or Array expected');
        }
      }
    }

    var flatValues = {};
    flattenImports(flatValues, functions);

    for (var name in flatValues) {
      if (object_hasOwnProperty(flatValues, name)) {
        // console.log('import', name)
        var value = flatValues[name];

        if (isFactory(value)) {
          // we ignore name here and enforce the name of the factory
          // maybe at some point we do want to allow overriding it
          // in that case we can implement an option overrideFactoryNames: true
          _importFactory(value, options);
        } else if (isSupportedType(value)) {
          _import(name, value, options);
        } else {
          if (!options.silent) {
            throw new TypeError('Factory, Object, or Array expected');
          }
        }
      }
    }
  }
  /**
   * Add a property to the math namespace
   * @param {string} name
   * @param {*} value
   * @param {Object} options  See import for a description of the options
   * @private
   */


  function _import(name, value, options) {
    // TODO: refactor this function, it's to complicated and contains duplicate code
    if (options.wrap && typeof value === 'function') {
      // create a wrapper around the function
      value = _wrap(value);
    } // turn a plain function with a typed-function signature into a typed-function


    if (hasTypedFunctionSignature(value)) {
      value = typed(name, {
        [value.signature]: value
      });
    }

    if (isTypedFunction(math[name]) && isTypedFunction(value)) {
      if (options.override) {
        // give the typed function the right name
        value = typed(name, value.signatures);
      } else {
        // merge the existing and typed function
        value = typed(math[name], value);
      }

      math[name] = value;
      delete importedFactories[name];

      _importTransform(name, value);

      math.emit('import', name, function resolver() {
        return value;
      });
      return;
    }

    if (math[name] === undefined || options.override) {
      math[name] = value;
      delete importedFactories[name];

      _importTransform(name, value);

      math.emit('import', name, function resolver() {
        return value;
      });
      return;
    }

    if (!options.silent) {
      throw new Error('Cannot import "' + name + '": already exists');
    }
  }

  function _importTransform(name, value) {
    if (value && typeof value.transform === 'function') {
      math.expression.transform[name] = value.transform;

      if (allowedInExpressions(name)) {
        math.expression.mathWithTransform[name] = value.transform;
      }
    } else {
      // remove existing transform
      delete math.expression.transform[name];

      if (allowedInExpressions(name)) {
        math.expression.mathWithTransform[name] = value;
      }
    }
  }

  function _deleteTransform(name) {
    delete math.expression.transform[name];

    if (allowedInExpressions(name)) {
      math.expression.mathWithTransform[name] = math[name];
    } else {
      delete math.expression.mathWithTransform[name];
    }
  }
  /**
   * Create a wrapper a round an function which converts the arguments
   * to their primitive values (like convert a Matrix to Array)
   * @param {Function} fn
   * @return {Function} Returns the wrapped function
   * @private
   */


  function _wrap(fn) {
    var wrapper = function wrapper() {
      var args = [];

      for (var i = 0, len = arguments.length; i < len; i++) {
        var arg = arguments[i];
        args[i] = arg && arg.valueOf();
      }

      return fn.apply(math, args);
    };

    if (fn.transform) {
      wrapper.transform = fn.transform;
    }

    return wrapper;
  }
  /**
   * Import an instance of a factory into math.js
   * @param {function(scope: object)} factory
   * @param {Object} options  See import for a description of the options
   * @param {string} [name=factory.name] Optional custom name
   * @private
   */


  function _importFactory(factory, options) {
    var name = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : factory.fn;

    if (contains(name, '.')) {
      throw new Error('Factory name should not contain a nested path. ' + 'Name: ' + JSON.stringify(name));
    }

    var namespace = isTransformFunctionFactory(factory) ? math.expression.transform : math;
    var existingTransform = (name in math.expression.transform);
    var existing = object_hasOwnProperty(namespace, name) ? namespace[name] : undefined;

    var resolver = function resolver() {
      // collect all dependencies, handle finding both functions and classes and other special cases
      var dependencies = {};
      factory.dependencies.map(stripOptionalNotation).forEach(dependency => {
        if (contains(dependency, '.')) {
          throw new Error('Factory dependency should not contain a nested path. ' + 'Name: ' + JSON.stringify(dependency));
        }

        if (dependency === 'math') {
          dependencies.math = math;
        } else if (dependency === 'mathWithTransform') {
          dependencies.mathWithTransform = math.expression.mathWithTransform;
        } else if (dependency === 'classes') {
          // special case for json reviver
          dependencies.classes = math;
        } else {
          dependencies[dependency] = math[dependency];
        }
      });
      var instance = /* #__PURE__ */factory(dependencies);

      if (instance && typeof instance.transform === 'function') {
        throw new Error('Transforms cannot be attached to factory functions. ' + 'Please create a separate function for it with exports.path="expression.transform"');
      }

      if (existing === undefined || options.override) {
        return instance;
      }

      if (isTypedFunction(existing) && isTypedFunction(instance)) {
        // merge the existing and new typed function
        return typed(existing, instance);
      }

      if (options.silent) {
        // keep existing, ignore imported function
        return existing;
      } else {
        throw new Error('Cannot import "' + name + '": already exists');
      }
    }; // TODO: add unit test with non-lazy factory


    if (!factory.meta || factory.meta.lazy !== false) {
      lazy(namespace, name, resolver); // FIXME: remove the `if (existing &&` condition again. Can we make sure subset is loaded before subset.transform? (Name collision, and no dependencies between the two)

      if (existing && existingTransform) {
        _deleteTransform(name);
      } else {
        if (isTransformFunctionFactory(factory) || factoryAllowedInExpressions(factory)) {
          lazy(math.expression.mathWithTransform, name, () => namespace[name]);
        }
      }
    } else {
      namespace[name] = resolver(); // FIXME: remove the `if (existing &&` condition again. Can we make sure subset is loaded before subset.transform? (Name collision, and no dependencies between the two)

      if (existing && existingTransform) {
        _deleteTransform(name);
      } else {
        if (isTransformFunctionFactory(factory) || factoryAllowedInExpressions(factory)) {
          lazy(math.expression.mathWithTransform, name, () => namespace[name]);
        }
      }
    } // TODO: improve factories, store a list with imports instead which can be re-played


    importedFactories[name] = factory;
    math.emit('import', name, resolver);
  }
  /**
   * Check whether given object is a type which can be imported
   * @param {Function | number | string | boolean | null | Unit | Complex} object
   * @return {boolean}
   * @private
   */


  function isSupportedType(object) {
    return typeof object === 'function' || typeof object === 'number' || typeof object === 'string' || typeof object === 'boolean' || object === null || is_isUnit(object) || isComplex(object) || isBigNumber(object) || isFraction(object) || isMatrix(object) || Array.isArray(object);
  }
  /**
   * Test whether a given thing is a typed-function
   * @param {*} fn
   * @return {boolean} Returns true when `fn` is a typed-function
   */


  function isTypedFunction(fn) {
    return typeof fn === 'function' && typeof fn.signatures === 'object';
  }

  function hasTypedFunctionSignature(fn) {
    return typeof fn === 'function' && typeof fn.signature === 'string';
  }

  function allowedInExpressions(name) {
    return !object_hasOwnProperty(unsafe, name);
  }

  function factoryAllowedInExpressions(factory) {
    return factory.fn.indexOf('.') === -1 && // FIXME: make checking on path redundant, check on meta data instead
    !object_hasOwnProperty(unsafe, factory.fn) && (!factory.meta || !factory.meta.isClass);
  }

  function isTransformFunctionFactory(factory) {
    return factory !== undefined && factory.meta !== undefined && factory.meta.isTransformFunction === true || false;
  } // namespaces and functions not available in the parser for safety reasons


  var unsafe = {
    expression: true,
    type: true,
    docs: true,
    error: true,
    json: true,
    chain: true // chain method not supported. Note that there is a unit chain too.

  };
  return mathImport;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/core/config.js
var DEFAULT_CONFIG = {
  // minimum relative difference between two compared values,
  // used by all comparison functions
  epsilon: 1e-12,
  // type of default matrix output. Choose 'matrix' (default) or 'array'
  matrix: 'Matrix',
  // type of default number output. Choose 'number' (default) 'BigNumber', or 'Fraction
  number: 'number',
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
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/core/function/config.js


var MATRIX_OPTIONS = ['Matrix', 'Array']; // valid values for option matrix

var NUMBER_OPTIONS = ['number', 'BigNumber', 'Fraction']; // valid values for option number

function configFactory(config, emit) {
  /**
   * Set configuration options for math.js, and get current options.
   * Will emit a 'config' event, with arguments (curr, prev, changes).
   *
   * This function is only available on a mathjs instance created using `create`.
   *
   * Syntax:
   *
   *     math.config(config: Object): Object
   *
   * Examples:
   *
   *
   *     import { create, all } from 'mathjs'
   *
   *     // create a mathjs instance
   *     const math = create(all)
   *
   *     math.config().number                // outputs 'number'
   *     math.evaluate('0.4')                // outputs number 0.4
   *     math.config({number: 'Fraction'})
   *     math.evaluate('0.4')                // outputs Fraction 2/5
   *
   * @param {Object} [options] Available options:
   *                            {number} epsilon
   *                              Minimum relative difference between two
   *                              compared values, used by all comparison functions.
   *                            {string} matrix
   *                              A string 'Matrix' (default) or 'Array'.
   *                            {string} number
   *                              A string 'number' (default), 'BigNumber', or 'Fraction'
   *                            {number} precision
   *                              The number of significant digits for BigNumbers.
   *                              Not applicable for Numbers.
   *                            {string} parenthesis
   *                              How to display parentheses in LaTeX and string
   *                              output.
   *                            {string} randomSeed
   *                              Random seed for seeded pseudo random number generator.
   *                              Set to null to randomly seed.
   * @return {Object} Returns the current configuration
   */
  function _config(options) {
    if (options) {
      var prev = mapObject(config, clone); // validate some of the options

      validateOption(options, 'matrix', MATRIX_OPTIONS);
      validateOption(options, 'number', NUMBER_OPTIONS); // merge options

      deepExtend(config, options);
      var curr = mapObject(config, clone);
      var changes = mapObject(options, clone); // emit 'config' event

      emit('config', curr, prev, changes);
      return curr;
    } else {
      return mapObject(config, clone);
    }
  } // attach the valid options to the function so they can be extended


  _config.MATRIX_OPTIONS = MATRIX_OPTIONS;
  _config.NUMBER_OPTIONS = NUMBER_OPTIONS; // attach the config properties as readonly properties to the config function

  Object.keys(DEFAULT_CONFIG).forEach(key => {
    Object.defineProperty(_config, key, {
      get: () => config[key],
      enumerable: true,
      configurable: true
    });
  });
  return _config;
}
/**
 * Test whether an Array contains a specific item.
 * @param {Array.<string>} array
 * @param {string} item
 * @return {boolean}
 */

function config_contains(array, item) {
  return array.indexOf(item) !== -1;
}
/**
 * Validate an option
 * @param {Object} options         Object with options
 * @param {string} name            Name of the option to validate
 * @param {Array.<string>} values  Array with valid values for this option
 */


function validateOption(options, name, values) {
  if (options[name] !== undefined && !config_contains(values, options[name])) {
    // unknown value
    console.warn('Warning: Unknown value "' + options[name] + '" for configuration option "' + name + '". ' + 'Available options: ' + values.map(value => JSON.stringify(value)).join(', ') + '.');
  }
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/core/create.js












/**
 * Create a mathjs instance from given factory functions and optionally config
 *
 * Usage:
 *
 *     const mathjs1 = create({ createAdd, createMultiply, ...})
 *     const config = { number: 'BigNumber' }
 *     const mathjs2 = create(all, config)
 *
 * @param {Object} [factories] An object with factory functions
 *                             The object can contain nested objects,
 *                             all nested objects will be flattened.
 * @param {Object} [config]    Available options:
 *                            {number} epsilon
 *                              Minimum relative difference between two
 *                              compared values, used by all comparison functions.
 *                            {string} matrix
 *                              A string 'Matrix' (default) or 'Array'.
 *                            {string} number
 *                              A string 'number' (default), 'BigNumber', or 'Fraction'
 *                            {number} precision
 *                              The number of significant digits for BigNumbers.
 *                              Not applicable for Numbers.
 *                            {boolean} predictable
 *                              Predictable output type of functions. When true,
 *                              output type depends only on the input types. When
 *                              false (default), output type can vary depending
 *                              on input values. For example `math.sqrt(-4)`
 *                              returns `complex('2i')` when predictable is false, and
 *                              returns `NaN` when true.
 *                            {string} randomSeed
 *                              Random seed for seeded pseudo random number generator.
 *                              Set to null to randomly seed.
 * @returns {Object} Returns a bare-bone math.js instance containing
 *                   functions:
 *                   - `import` to add new functions
 *                   - `config` to change configuration
 *                   - `on`, `off`, `once`, `emit` for events
 */

function create_create(factories, config) {
  var configInternal = extends_default()({}, DEFAULT_CONFIG, config); // simple test for ES5 support


  if (typeof Object.create !== 'function') {
    throw new Error('ES5 not supported by this JavaScript engine. ' + 'Please load the es5-shim and es5-sham library for compatibility.');
  } // create the mathjs instance


  var math = mixin({
    // only here for backward compatibility for legacy factory functions
    isNumber: isNumber,
    isComplex: isComplex,
    isBigNumber: isBigNumber,
    isFraction: isFraction,
    isUnit: is_isUnit,
    isString: isString,
    isArray: isArray,
    isMatrix: isMatrix,
    isCollection: isCollection,
    isDenseMatrix: isDenseMatrix,
    isSparseMatrix: isSparseMatrix,
    isRange: isRange,
    isIndex: isIndex,
    isBoolean: isBoolean,
    isResultSet: isResultSet,
    isHelp: isHelp,
    isFunction: isFunction,
    isDate: isDate,
    isRegExp: isRegExp,
    isObject: isObject,
    isNull: isNull,
    isUndefined: isUndefined,
    isAccessorNode: isAccessorNode,
    isArrayNode: isArrayNode,
    isAssignmentNode: isAssignmentNode,
    isBlockNode: isBlockNode,
    isConditionalNode: isConditionalNode,
    isConstantNode: isConstantNode,
    isFunctionAssignmentNode: isFunctionAssignmentNode,
    isFunctionNode: isFunctionNode,
    isIndexNode: isIndexNode,
    isNode: isNode,
    isObjectNode: isObjectNode,
    isOperatorNode: isOperatorNode,
    isParenthesisNode: isParenthesisNode,
    isRangeNode: isRangeNode,
    isSymbolNode: isSymbolNode,
    isChain: isChain
  }); // load config function and apply provided config

  math.config = configFactory(configInternal, math.emit);
  math.expression = {
    transform: {},
    mathWithTransform: {
      config: math.config
    }
  }; // cached factories and instances used by function load

  var legacyFactories = [];
  var legacyInstances = [];
  /**
   * Load a function or data type from a factory.
   * If the function or data type already exists, the existing instance is
   * returned.
   * @param {Function} factory
   * @returns {*}
   */

  function load(factory) {
    if (isFactory(factory)) {
      return factory(math);
    }

    var firstProperty = factory[Object.keys(factory)[0]];

    if (isFactory(firstProperty)) {
      return firstProperty(math);
    }

    if (!isLegacyFactory(factory)) {
      console.warn('Factory object with properties `type`, `name`, and `factory` expected', factory);
      throw new Error('Factory object with properties `type`, `name`, and `factory` expected');
    }

    var index = legacyFactories.indexOf(factory);
    var instance;

    if (index === -1) {
      // doesn't yet exist
      if (factory.math === true) {
        // pass with math namespace
        instance = factory.factory(math.type, configInternal, load, math.typed, math);
      } else {
        instance = factory.factory(math.type, configInternal, load, math.typed);
      } // append to the cache


      legacyFactories.push(factory);
      legacyInstances.push(instance);
    } else {
      // already existing function, return the cached instance
      instance = legacyInstances[index];
    }

    return instance;
  }

  var importedFactories = {}; // load the import function

  function lazyTyped() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    return math.typed.apply(math.typed, args);
  }

  var internalImport = importFactory(lazyTyped, load, math, importedFactories);
  math.import = internalImport; // listen for changes in config, import all functions again when changed
  // TODO: move this listener into the import function?

  math.on('config', () => {
    object_values(importedFactories).forEach(factory => {
      if (factory && factory.meta && factory.meta.recreateOnConfigChange) {
        // FIXME: only re-create when the current instance is the same as was initially created
        // FIXME: delete the functions/constants before importing them again?
        internalImport(factory, {
          override: true
        });
      }
    });
  }); // the create function exposed on the mathjs instance is bound to
  // the factory functions passed before

  math.create = create_create.bind(null, factories); // export factory function

  math.factory = factory_factory; // import the factory functions like createAdd as an array instead of object,
  // else they will get a different naming (`createAdd` instead of `add`).

  math.import(object_values(deepFlatten(factories)));
  math.ArgumentsError = ArgumentsError;
  math.DimensionError = DimensionError;
  math.IndexError = IndexError;
  return math;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/keywords.js
// Reserved keywords not allowed to use in the parser
var keywords = new Set(['end']);
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/customs.js

/**
 * Get a property of a plain object
 * Throws an error in case the object is not a plain object or the
 * property is not defined on the object itself
 * @param {Object} object
 * @param {string} prop
 * @return {*} Returns the property value when safe
 */

function getSafeProperty(object, prop) {
  // only allow getting safe properties of a plain object
  if (isPlainObject(object) && isSafeProperty(object, prop)) {
    return object[prop];
  }

  if (typeof object[prop] === 'function' && isSafeMethod(object, prop)) {
    throw new Error('Cannot access method "' + prop + '" as a property');
  }

  throw new Error('No access to property "' + prop + '"');
}
/**
 * Set a property on a plain object.
 * Throws an error in case the object is not a plain object or the
 * property would override an inherited property like .constructor or .toString
 * @param {Object} object
 * @param {string} prop
 * @param {*} value
 * @return {*} Returns the value
 */
// TODO: merge this function into access.js?


function setSafeProperty(object, prop, value) {
  // only allow setting safe properties of a plain object
  if (isPlainObject(object) && isSafeProperty(object, prop)) {
    object[prop] = value;
    return value;
  }

  throw new Error('No access to property "' + prop + '"');
}

function getSafeProperties(object) {
  return Object.keys(object).filter(prop => object_hasOwnProperty(object, prop));
}

function hasSafeProperty(object, prop) {
  return prop in object;
}
/**
 * Test whether a property is safe to use for an object.
 * For example .toString and .constructor are not safe
 * @param {string} prop
 * @return {boolean} Returns true when safe
 */


function isSafeProperty(object, prop) {
  if (!object || typeof object !== 'object') {
    return false;
  } // SAFE: whitelisted
  // e.g length


  if (object_hasOwnProperty(safeNativeProperties, prop)) {
    return true;
  } // UNSAFE: inherited from Object prototype
  // e.g constructor


  if (prop in Object.prototype) {
    // 'in' is used instead of hasOwnProperty for nodejs v0.10
    // which is inconsistent on root prototypes. It is safe
    // here because Object.prototype is a root object
    return false;
  } // UNSAFE: inherited from Function prototype
  // e.g call, apply


  if (prop in Function.prototype) {
    // 'in' is used instead of hasOwnProperty for nodejs v0.10
    // which is inconsistent on root prototypes. It is safe
    // here because Function.prototype is a root object
    return false;
  }

  return true;
}
/**
 * Validate whether a method is safe.
 * Throws an error when that's not the case.
 * @param {Object} object
 * @param {string} method
 */
// TODO: merge this function into assign.js?


function validateSafeMethod(object, method) {
  if (!isSafeMethod(object, method)) {
    throw new Error('No access to method "' + method + '"');
  }
}
/**
 * Check whether a method is safe.
 * Throws an error when that's not the case (for example for `constructor`).
 * @param {Object} object
 * @param {string} method
 * @return {boolean} Returns true when safe, false otherwise
 */


function isSafeMethod(object, method) {
  if (object === null || object === undefined || typeof object[method] !== 'function') {
    return false;
  } // UNSAFE: ghosted
  // e.g overridden toString
  // Note that IE10 doesn't support __proto__ and we can't do this check there.


  if (object_hasOwnProperty(object, method) && Object.getPrototypeOf && method in Object.getPrototypeOf(object)) {
    return false;
  } // SAFE: whitelisted
  // e.g toString


  if (object_hasOwnProperty(safeNativeMethods, method)) {
    return true;
  } // UNSAFE: inherited from Object prototype
  // e.g constructor


  if (method in Object.prototype) {
    // 'in' is used instead of hasOwnProperty for nodejs v0.10
    // which is inconsistent on root prototypes. It is safe
    // here because Object.prototype is a root object
    return false;
  } // UNSAFE: inherited from Function prototype
  // e.g call, apply


  if (method in Function.prototype) {
    // 'in' is used instead of hasOwnProperty for nodejs v0.10
    // which is inconsistent on root prototypes. It is safe
    // here because Function.prototype is a root object
    return false;
  }

  return true;
}

function isPlainObject(object) {
  return typeof object === 'object' && object && object.constructor === Object;
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








// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/map.js


/**
 * A map facade on a bare object.
 *
 * The small number of methods needed to implement a scope,
 * forwarding on to the SafeProperty functions. Over time, the codebase
 * will stop using this method, as all objects will be Maps, rather than
 * more security prone objects.
 */

class map_ObjectWrappingMap {
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
/**
 * Creates an empty map, or whatever your platform's polyfill is.
 *
 * @returns an empty Map or Map like object.
 */

function createEmptyMap() {
  return new Map();
}
/**
 * Creates a Map from the given object.
 *
 * @param { Map | { [key: string]: unknown } | undefined } mapOrObject
 * @returns
 */

function createMap(mapOrObject) {
  if (!mapOrObject) {
    return createEmptyMap();
  }

  if (isMap(mapOrObject)) {
    return mapOrObject;
  }

  if (isObject(mapOrObject)) {
    return new map_ObjectWrappingMap(mapOrObject);
  }

  throw new Error('createMap can create maps from objects or Maps');
}
/**
 * Unwraps a map into an object.
 *
 * @param {Map} map
 * @returns { [key: string]: unknown }
 */

function toObject(map) {
  if (map instanceof map_ObjectWrappingMap) {
    return map.wrappedObject;
  }

  var object = {};

  for (var key of map.keys()) {
    var value = map.get(key);
    setSafeProperty(object, key, value);
  }

  return object;
}
/**
 * Returns `true` if the passed object appears to be a Map (i.e. duck typing).
 *
 * Methods looked for are `get`, `set`, `keys` and `has`.
 *
 * @param {Map | object} object
 * @returns
 */

function isMap(object) {
  // We can use the fast instanceof, or a slower duck typing check.
  // The duck typing method needs to cover enough methods to not be confused with DenseMatrix.
  if (!object) {
    return false;
  }

  return object instanceof Map || object instanceof map_ObjectWrappingMap || typeof object.set === 'function' && typeof object.get === 'function' && typeof object.keys === 'function' && typeof object.has === 'function';
}
/**
 * Copies the contents of key-value pairs from each `objects` in to `map`.
 *
 * Object is `objects` can be a `Map` or object.
 *
 * This is the `Map` analog to `Object.assign`.
 */

function map_assign(map) {
  for (var _len = arguments.length, objects = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    objects[_key - 1] = arguments[_key];
  }

  for (var args of objects) {
    if (!args) {
      continue;
    }

    if (isMap(args)) {
      for (var key of args.keys()) {
        map.set(key, args.get(key));
      }
    } else if (isObject(args)) {
      for (var _key2 of Object.keys(args)) {
        map.set(_key2, args[_key2]);
      }
    }
  }

  return map;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/Node.js





var Node_name = 'Node';
var Node_dependencies = ['mathWithTransform'];
var createNode = /* #__PURE__ */factory_factory(Node_name, Node_dependencies, _ref => {
  var {
    mathWithTransform
  } = _ref;

  /**
   * Node
   */
  function Node() {
    if (!(this instanceof Node)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }
  }
  /**
   * Evaluate the node
   * @param {Object} [scope]  Scope to read/write variables
   * @return {*}              Returns the result
   */


  Node.prototype.evaluate = function (scope) {
    return this.compile().evaluate(scope);
  };

  Node.prototype.type = 'Node';
  Node.prototype.isNode = true;
  Node.prototype.comment = '';
  /**
   * Compile the node into an optimized, evauatable JavaScript function
   * @return {{evaluate: function([Object])}} object
   *                Returns an object with a function 'evaluate',
   *                which can be invoked as expr.evaluate([scope: Object]),
   *                where scope is an optional object with
   *                variables.
   */

  Node.prototype.compile = function () {
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
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */


  Node.prototype._compile = function (math, argNames) {
    throw new Error('Method _compile should be implemented by type ' + this.type);
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  Node.prototype.forEach = function (callback) {
    // must be implemented by each of the Node implementations
    throw new Error('Cannot run forEach on a Node interface');
  };
  /**
   * Create a new Node having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {OperatorNode} Returns a transformed copy of the node
   */


  Node.prototype.map = function (callback) {
    // must be implemented by each of the Node implementations
    throw new Error('Cannot run map on a Node interface');
  };
  /**
   * Validate whether an object is a Node, for use with map
   * @param {Node} node
   * @returns {Node} Returns the input if it's a node, else throws an Error
   * @protected
   */


  Node.prototype._ifNode = function (node) {
    if (!isNode(node)) {
      throw new TypeError('Callback function must return a Node');
    }

    return node;
  };
  /**
   * Recursively traverse all nodes in a node tree. Executes given callback for
   * this node and each of its child nodes.
   * @param {function(node: Node, path: string, parent: Node)} callback
   *          A callback called for every node in the node tree.
   */


  Node.prototype.traverse = function (callback) {
    // execute callback for itself
    // eslint-disable-next-line
    callback(this, null, null); // recursively traverse over all childs of a node

    function _traverse(node, callback) {
      node.forEach(function (child, path, parent) {
        callback(child, path, parent);

        _traverse(child, callback);
      });
    }

    _traverse(this, callback);
  };
  /**
   * Recursively transform a node tree via a transform function.
   *
   * For example, to replace all nodes of type SymbolNode having name 'x' with a
   * ConstantNode with value 2:
   *
   *     const res = Node.transform(function (node, path, parent) {
   *       if (node && node.isSymbolNode) && (node.name === 'x')) {
   *         return new ConstantNode(2)
   *       }
   *       else {
   *         return node
   *       }
   *     })
   *
   * @param {function(node: Node, path: string, parent: Node) : Node} callback
   *          A mapping function accepting a node, and returning
   *          a replacement for the node or the original node.
   *          Signature: callback(node: Node, index: string, parent: Node) : Node
   * @return {Node} Returns the original node or its replacement
   */


  Node.prototype.transform = function (callback) {
    function _transform(child, path, parent) {
      var replacement = callback(child, path, parent);

      if (replacement !== child) {
        // stop iterating when the node is replaced
        return replacement;
      }

      return child.map(_transform);
    }

    return _transform(this, null, null);
  };
  /**
   * Find any node in the node tree matching given filter function. For example, to
   * find all nodes of type SymbolNode having name 'x':
   *
   *     const results = Node.filter(function (node) {
   *       return (node && node.isSymbolNode) && (node.name === 'x')
   *     })
   *
   * @param {function(node: Node, path: string, parent: Node) : Node} callback
   *            A test function returning true when a node matches, and false
   *            otherwise. Function signature:
   *            callback(node: Node, index: string, parent: Node) : boolean
   * @return {Node[]} nodes       An array with nodes matching given filter criteria
   */


  Node.prototype.filter = function (callback) {
    var nodes = [];
    this.traverse(function (node, path, parent) {
      if (callback(node, path, parent)) {
        nodes.push(node);
      }
    });
    return nodes;
  };
  /**
   * Create a shallow clone of this node
   * @return {Node}
   */


  Node.prototype.clone = function () {
    // must be implemented by each of the Node implementations
    throw new Error('Cannot clone a Node interface');
  };
  /**
   * Create a deep clone of this node
   * @return {Node}
   */


  Node.prototype.cloneDeep = function () {
    return this.map(function (node) {
      return node.cloneDeep();
    });
  };
  /**
   * Deep compare this node with another node.
   * @param {Node} other
   * @return {boolean} Returns true when both nodes are of the same type and
   *                   contain the same values (as do their childs)
   */


  Node.prototype.equals = function (other) {
    return other ? deepStrictEqual(this, other) : false;
  };
  /**
   * Get string representation. (wrapper function)
   *
   * This function can get an object of the following form:
   * {
   *    handler: //This can be a callback function of the form
   *             // "function callback(node, options)"or
   *             // a map that maps function names (used in FunctionNodes)
   *             // to callbacks
   *    parenthesis: "keep" //the parenthesis option (This is optional)
   * }
   *
   * @param {Object} [options]
   * @return {string}
   */


  Node.prototype.toString = function (options) {
    var customString = this._getCustomString(options);

    if (typeof customString !== 'undefined') {
      return customString;
    }

    return this._toString(options);
  };
  /**
   * Get a JSON representation of the node
   * Both .toJSON() and the static .fromJSON(json) should be implemented by all
   * implementations of Node
   * @returns {Object}
   */


  Node.prototype.toJSON = function () {
    throw new Error('Cannot serialize object: toJSON not implemented by ' + this.type);
  };
  /**
   * Get HTML representation. (wrapper function)
   *
   * This function can get an object of the following form:
   * {
   *    handler: //This can be a callback function of the form
   *             // "function callback(node, options)" or
   *             // a map that maps function names (used in FunctionNodes)
   *             // to callbacks
   *    parenthesis: "keep" //the parenthesis option (This is optional)
   * }
   *
   * @param {Object} [options]
   * @return {string}
   */


  Node.prototype.toHTML = function (options) {
    var customString = this._getCustomString(options);

    if (typeof customString !== 'undefined') {
      return customString;
    }

    return this.toHTML(options);
  };
  /**
   * Internal function to generate the string output.
   * This has to be implemented by every Node
   *
   * @throws {Error}
   */


  Node.prototype._toString = function () {
    // must be implemented by each of the Node implementations
    throw new Error('_toString not implemented for ' + this.type);
  };
  /**
   * Get LaTeX representation. (wrapper function)
   *
   * This function can get an object of the following form:
   * {
   *    handler: //This can be a callback function of the form
   *             // "function callback(node, options)"or
   *             // a map that maps function names (used in FunctionNodes)
   *             // to callbacks
   *    parenthesis: "keep" //the parenthesis option (This is optional)
   * }
   *
   * @param {Object} [options]
   * @return {string}
   */


  Node.prototype.toTex = function (options) {
    var customString = this._getCustomString(options);

    if (typeof customString !== 'undefined') {
      return customString;
    }

    return this._toTex(options);
  };
  /**
   * Internal function to generate the LaTeX output.
   * This has to be implemented by every Node
   *
   * @param {Object} [options]
   * @throws {Error}
   */


  Node.prototype._toTex = function (options) {
    // must be implemented by each of the Node implementations
    throw new Error('_toTex not implemented for ' + this.type);
  };
  /**
   * Helper used by `to...` functions.
   */


  Node.prototype._getCustomString = function (options) {
    if (options && typeof options === 'object') {
      switch (typeof options.handler) {
        case 'object':
        case 'undefined':
          return;

        case 'function':
          return options.handler(this, options);

        default:
          throw new TypeError('Object or function expected as callback');
      }
    }
  };
  /**
   * Get identifier.
   * @return {string}
   */


  Node.prototype.getIdentifier = function () {
    return this.type;
  };
  /**
   * Get the content of the current Node.
   * @return {Node} node
   **/


  Node.prototype.getContent = function () {
    return this;
  };
  /**
   * Validate the symbol names of a scope.
   * Throws an error when the scope contains an illegal symbol.
   * @param {Object} scope
   */


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
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */

var NodeDependencies = {
  createNode: createNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/arithmetic.js

var n1 = 'number';
var n2 = 'number, number';
function absNumber(a) {
  return Math.abs(a);
}
absNumber.signature = n1;
function addNumber(a, b) {
  return a + b;
}
addNumber.signature = n2;
function subtractNumber(a, b) {
  return a - b;
}
subtractNumber.signature = n2;
function multiplyNumber(a, b) {
  return a * b;
}
multiplyNumber.signature = n2;
function divideNumber(a, b) {
  return a / b;
}
divideNumber.signature = n2;
function unaryMinusNumber(x) {
  return -x;
}
unaryMinusNumber.signature = n1;
function unaryPlusNumber(x) {
  return x;
}
unaryPlusNumber.signature = n1;
function cbrtNumber(x) {
  return cbrt(x);
}
cbrtNumber.signature = n1;
function cubeNumber(x) {
  return x * x * x;
}
cubeNumber.signature = n1;
function expNumber(x) {
  return Math.exp(x);
}
expNumber.signature = n1;
function expm1Number(x) {
  return expm1(x);
}
expm1Number.signature = n1;
/**
 * Calculate gcd for numbers
 * @param {number} a
 * @param {number} b
 * @returns {number} Returns the greatest common denominator of a and b
 */

function gcdNumber(a, b) {
  if (!isInteger(a) || !isInteger(b)) {
    throw new Error('Parameters in function gcd must be integer numbers');
  } // https://en.wikipedia.org/wiki/Euclidean_algorithm


  var r;

  while (b !== 0) {
    r = a % b;
    a = b;
    b = r;
  }

  return a < 0 ? -a : a;
}
gcdNumber.signature = n2;
/**
 * Calculate lcm for two numbers
 * @param {number} a
 * @param {number} b
 * @returns {number} Returns the least common multiple of a and b
 */

function lcmNumber(a, b) {
  if (!isInteger(a) || !isInteger(b)) {
    throw new Error('Parameters in function lcm must be integer numbers');
  }

  if (a === 0 || b === 0) {
    return 0;
  } // https://en.wikipedia.org/wiki/Euclidean_algorithm
  // evaluate lcm here inline to reduce overhead


  var t;
  var prod = a * b;

  while (b !== 0) {
    t = b;
    b = a % t;
    a = t;
  }

  return Math.abs(prod / a);
}
lcmNumber.signature = n2;
/**
 * Calculate the logarithm of a value, optionally to a given base.
 * @param {number} x
 * @param {number | null | undefined} base
 * @return {number}
 */

function logNumber(x, y) {
  if (y) {
    return Math.log(x) / Math.log(y);
  }

  return Math.log(x);
}
/**
 * Calculate the 10-base logarithm of a number
 * @param {number} x
 * @return {number}
 */

function log10Number(x) {
  return log10(x);
}
log10Number.signature = n1;
/**
 * Calculate the 2-base logarithm of a number
 * @param {number} x
 * @return {number}
 */

function log2Number(x) {
  return log2(x);
}
log2Number.signature = n1;
/**
 * Calculate the natural logarithm of a `number+1`
 * @param {number} x
 * @returns {number}
 */

function log1pNumber(x) {
  return log1p(x);
}
log1pNumber.signature = n1;
/**
 * Calculate the modulus of two numbers
 * @param {number} x
 * @param {number} y
 * @returns {number} res
 * @private
 */

function modNumber(x, y) {
  if (y > 0) {
    // We don't use JavaScript's % operator here as this doesn't work
    // correctly for x < 0 and x === 0
    // see https://en.wikipedia.org/wiki/Modulo_operation
    return x - y * Math.floor(x / y);
  } else if (y === 0) {
    return x;
  } else {
    // y < 0
    // TODO: implement mod for a negative divisor
    throw new Error('Cannot calculate mod for a negative divisor');
  }
}
modNumber.signature = n2;
/**
 * Calculate the nth root of a, solve x^root == a
 * http://rosettacode.org/wiki/Nth_root#JavaScript
 * @param {number} a
 * @param {number} [2] root
 * @private
 */

function nthRootNumber(a) {
  var root = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 2;
  var inv = root < 0;

  if (inv) {
    root = -root;
  }

  if (root === 0) {
    throw new Error('Root must be non-zero');
  }

  if (a < 0 && Math.abs(root) % 2 !== 1) {
    throw new Error('Root must be odd when a is negative.');
  } // edge cases zero and infinity


  if (a === 0) {
    return inv ? Infinity : 0;
  }

  if (!isFinite(a)) {
    return inv ? 0 : a;
  }

  var x = Math.pow(Math.abs(a), 1 / root); // If a < 0, we require that root is an odd integer,
  // so (-1) ^ (1/root) = -1

  x = a < 0 ? -x : x;
  return inv ? 1 / x : x; // Very nice algorithm, but fails with nthRoot(-2, 3).
  // Newton's method has some well-known problems at times:
  // https://en.wikipedia.org/wiki/Newton%27s_method#Failure_analysis

  /*
  let x = 1 // Initial guess
  let xPrev = 1
  let i = 0
  const iMax = 10000
  do {
    const delta = (a / Math.pow(x, root - 1) - x) / root
    xPrev = x
    x = x + delta
    i++
  }
  while (xPrev !== x && i < iMax)
   if (xPrev !== x) {
    throw new Error('Function nthRoot failed to converge')
  }
   return inv ? 1 / x : x
  */
}
function signNumber(x) {
  return sign(x);
}
signNumber.signature = n1;
function sqrtNumber(x) {
  return Math.sqrt(x);
}
sqrtNumber.signature = n1;
function squareNumber(x) {
  return x * x;
}
squareNumber.signature = n1;
/**
 * Calculate xgcd for two numbers
 * @param {number} a
 * @param {number} b
 * @return {number} result
 * @private
 */

function xgcdNumber(a, b) {
  // source: https://en.wikipedia.org/wiki/Extended_Euclidean_algorithm
  var t; // used to swap two variables

  var q; // quotient

  var r; // remainder

  var x = 0;
  var lastx = 1;
  var y = 1;
  var lasty = 0;

  if (!isInteger(a) || !isInteger(b)) {
    throw new Error('Parameters in function xgcd must be integer numbers');
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
xgcdNumber.signature = n2;
/**
 * Calculates the power of x to y, x^y, for two numbers.
 * @param {number} x
 * @param {number} y
 * @return {number} res
 */

function powNumber(x, y) {
  // x^Infinity === 0 if -1 < x < 1
  // A real number 0 is returned instead of complex(0)
  if (x * x < 1 && y === Infinity || x * x > 1 && y === -Infinity) {
    return 0;
  }

  return Math.pow(x, y);
}
powNumber.signature = n2;
/**
 * round a number to the given number of decimals, or to zero if decimals is
 * not provided
 * @param {number} value
 * @param {number} decimals       number of decimals, between 0 and 15 (0 by default)
 * @return {number} roundedValue
 */

function roundNumber(value) {
  var decimals = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

  if (!isInteger(decimals) || decimals < 0 || decimals > 15) {
    throw new Error('Number of decimals in function round must be an integer from 0 to 15 inclusive');
  }

  return parseFloat(toFixed(value, decimals));
}
/**
 * Calculate the norm of a number, the absolute value.
 * @param {number} x
 * @return {number}
 */

function normNumber(x) {
  return Math.abs(x);
}
normNumber.signature = n1;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/bitwise.js

var bitwise_n1 = 'number';
var bitwise_n2 = 'number, number';
function bitAndNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function bitAnd');
  }

  return x & y;
}
bitAndNumber.signature = bitwise_n2;
function bitNotNumber(x) {
  if (!isInteger(x)) {
    throw new Error('Integer expected in function bitNot');
  }

  return ~x;
}
bitNotNumber.signature = bitwise_n1;
function bitOrNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function bitOr');
  }

  return x | y;
}
bitOrNumber.signature = bitwise_n2;
function bitXorNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function bitXor');
  }

  return x ^ y;
}
bitXorNumber.signature = bitwise_n2;
function leftShiftNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function leftShift');
  }

  return x << y;
}
leftShiftNumber.signature = bitwise_n2;
function rightArithShiftNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function rightArithShift');
  }

  return x >> y;
}
rightArithShiftNumber.signature = bitwise_n2;
function rightLogShiftNumber(x, y) {
  if (!isInteger(x) || !isInteger(y)) {
    throw new Error('Integers expected in function rightLogShift');
  }

  return x >>> y;
}
rightLogShiftNumber.signature = bitwise_n2;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/logical.js
var logical_n1 = 'number';
var logical_n2 = 'number, number';
function notNumber(x) {
  return !x;
}
notNumber.signature = logical_n1;
function orNumber(x, y) {
  return !!(x || y);
}
orNumber.signature = logical_n2;
function xorNumber(x, y) {
  return !!x !== !!y;
}
xorNumber.signature = logical_n2;
function andNumber(x, y) {
  return !!(x && y);
}
andNumber.signature = logical_n2;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/product.js
/** @param {number} i
 *  @param {number} n
 *  @returns {number} product of i to n
 */
function product_product(i, n) {
  if (n < i) {
    return 1;
  }

  if (n === i) {
    return n;
  }

  var half = n + i >> 1; // divide (n + i) by 2 and truncate to integer

  return product_product(i, half) * product_product(half + 1, n);
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/combinations.js


function combinationsNumber(n, k) {
  if (!isInteger(n) || n < 0) {
    throw new TypeError('Positive integer value expected in function combinations');
  }

  if (!isInteger(k) || k < 0) {
    throw new TypeError('Positive integer value expected in function combinations');
  }

  if (k > n) {
    throw new TypeError('k must be less than or equal to n');
  }

  var nMinusk = n - k;
  var answer = 1;
  var firstnumerator = k < nMinusk ? nMinusk + 1 : k + 1;
  var nextdivisor = 2;
  var lastdivisor = k < nMinusk ? k : nMinusk; // balance multiplications and divisions to try to keep intermediate values
  // in exact-integer range as long as possible

  for (var nextnumerator = firstnumerator; nextnumerator <= n; ++nextnumerator) {
    answer *= nextnumerator;

    while (nextdivisor <= lastdivisor && answer % nextdivisor === 0) {
      answer /= nextdivisor;
      ++nextdivisor;
    }
  } // for big n, k, floating point may have caused weirdness in remainder


  if (nextdivisor <= lastdivisor) {
    answer /= product_product(nextdivisor, lastdivisor);
  }

  return answer;
}
combinationsNumber.signature = 'number, number';
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/probability.js
/* eslint-disable no-loss-of-precision */


function gammaNumber(n) {
  var x;

  if (isInteger(n)) {
    if (n <= 0) {
      return isFinite(n) ? Infinity : NaN;
    }

    if (n > 171) {
      return Infinity; // Will overflow
    }

    return product_product(1, n - 1);
  }

  if (n < 0.5) {
    return Math.PI / (Math.sin(Math.PI * n) * gammaNumber(1 - n));
  }

  if (n >= 171.35) {
    return Infinity; // will overflow
  }

  if (n > 85.0) {
    // Extended Stirling Approx
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
gammaNumber.signature = 'number'; // TODO: comment on the variables g and p

var gammaG = 4.7421875;
var gammaP = [0.99999999999999709182, 57.156235665862923517, -59.597960355475491248, 14.136097974741747174, -0.49191381609762019978, 0.33994649984811888699e-4, 0.46523628927048575665e-4, -0.98374475304879564677e-4, 0.15808870322491248884e-3, -0.21026444172410488319e-3, 0.21743961811521264320e-3, -0.16431810653676389022e-3, 0.84418223983852743293e-4, -0.26190838401581408670e-4, 0.36899182659531622704e-5]; // lgamma implementation ref: https://mrob.com/pub/ries/lanczos-gamma.html#code
// log(2 * pi) / 2

var lnSqrt2PI = 0.91893853320467274178;
var lgammaG = 5; // Lanczos parameter "g"

var lgammaN = 7; // Range of coefficients "n"

var lgammaSeries = [1.000000000190015, 76.18009172947146, -86.50532032941677, 24.01409824083091, -1.231739572450155, 0.1208650973866179e-2, -0.5395239384953e-5];
function lgammaNumber(n) {
  if (n < 0) return NaN;
  if (n === 0) return Infinity;
  if (!isFinite(n)) return n;

  if (n < 0.5) {
    // Use Euler's reflection formula:
    // gamma(z) = PI / (sin(PI * z) * gamma(1 - z))
    return Math.log(Math.PI / Math.sin(Math.PI * n)) - lgammaNumber(1 - n);
  } // Compute the logarithm of the Gamma function using the Lanczos method


  n = n - 1;
  var base = n + lgammaG + 0.5; // Base of the Lanczos exponential

  var sum = lgammaSeries[0]; // We start with the terms that have the smallest coefficients and largest denominator

  for (var i = lgammaN - 1; i >= 1; i--) {
    sum += lgammaSeries[i] / (n + i);
  }

  return lnSqrt2PI + (n + 0.5) * Math.log(base) - base + Math.log(sum);
}
lgammaNumber.signature = 'number';
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/trigonometry.js

var trigonometry_n1 = 'number';
var trigonometry_n2 = 'number, number';
function acosNumber(x) {
  return Math.acos(x);
}
acosNumber.signature = trigonometry_n1;
function acoshNumber(x) {
  return acosh(x);
}
acoshNumber.signature = trigonometry_n1;
function acotNumber(x) {
  return Math.atan(1 / x);
}
acotNumber.signature = trigonometry_n1;
function acothNumber(x) {
  return isFinite(x) ? (Math.log((x + 1) / x) + Math.log(x / (x - 1))) / 2 : 0;
}
acothNumber.signature = trigonometry_n1;
function acscNumber(x) {
  return Math.asin(1 / x);
}
acscNumber.signature = trigonometry_n1;
function acschNumber(x) {
  var xInv = 1 / x;
  return Math.log(xInv + Math.sqrt(xInv * xInv + 1));
}
acschNumber.signature = trigonometry_n1;
function asecNumber(x) {
  return Math.acos(1 / x);
}
asecNumber.signature = trigonometry_n1;
function asechNumber(x) {
  var xInv = 1 / x;
  var ret = Math.sqrt(xInv * xInv - 1);
  return Math.log(ret + xInv);
}
asechNumber.signature = trigonometry_n1;
function asinNumber(x) {
  return Math.asin(x);
}
asinNumber.signature = trigonometry_n1;
function asinhNumber(x) {
  return asinh(x);
}
asinhNumber.signature = trigonometry_n1;
function atanNumber(x) {
  return Math.atan(x);
}
atanNumber.signature = trigonometry_n1;
function atan2Number(y, x) {
  return Math.atan2(y, x);
}
atan2Number.signature = trigonometry_n2;
function atanhNumber(x) {
  return atanh(x);
}
atanhNumber.signature = trigonometry_n1;
function cosNumber(x) {
  return Math.cos(x);
}
cosNumber.signature = trigonometry_n1;
function coshNumber(x) {
  return cosh(x);
}
coshNumber.signature = trigonometry_n1;
function cotNumber(x) {
  return 1 / Math.tan(x);
}
cotNumber.signature = trigonometry_n1;
function cothNumber(x) {
  var e = Math.exp(2 * x);
  return (e + 1) / (e - 1);
}
cothNumber.signature = trigonometry_n1;
function cscNumber(x) {
  return 1 / Math.sin(x);
}
cscNumber.signature = trigonometry_n1;
function cschNumber(x) {
  // consider values close to zero (+/-)
  if (x === 0) {
    return Number.POSITIVE_INFINITY;
  } else {
    return Math.abs(2 / (Math.exp(x) - Math.exp(-x))) * sign(x);
  }
}
cschNumber.signature = trigonometry_n1;
function secNumber(x) {
  return 1 / Math.cos(x);
}
secNumber.signature = trigonometry_n1;
function sechNumber(x) {
  return 2 / (Math.exp(x) + Math.exp(-x));
}
sechNumber.signature = trigonometry_n1;
function sinNumber(x) {
  return Math.sin(x);
}
sinNumber.signature = trigonometry_n1;
function sinhNumber(x) {
  return sinh(x);
}
sinhNumber.signature = trigonometry_n1;
function tanNumber(x) {
  return Math.tan(x);
}
tanNumber.signature = trigonometry_n1;
function tanhNumber(x) {
  return tanh(x);
}
tanhNumber.signature = trigonometry_n1;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/plain/number/utils.js

var utils_n1 = 'number';
function isIntegerNumber(x) {
  return isInteger(x);
}
isIntegerNumber.signature = utils_n1;
function isNegativeNumber(x) {
  return x < 0;
}
isNegativeNumber.signature = utils_n1;
function isPositiveNumber(x) {
  return x > 0;
}
isPositiveNumber.signature = utils_n1;
function isZeroNumber(x) {
  return x === 0;
}
isZeroNumber.signature = utils_n1;
function isNaNNumber(x) {
  return Number.isNaN(x);
}
isNaNNumber.signature = utils_n1;
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/noop.js
function noBignumber() {
  throw new Error('No "bignumber" implementation available');
}
function noFraction() {
  throw new Error('No "fraction" implementation available');
}
function noMatrix() {
  throw new Error('No "matrix" implementation available');
}
function noIndex() {
  throw new Error('No "index" implementation available');
}
function noSubset() {
  throw new Error('No "matrix" implementation available');
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/factoriesNumber.js


 // ----------------------------------------------------------------------------
// classes and functions
// core

 // classes






 // algebra





 // arithmetic

var createUnaryMinus = /* #__PURE__ */createNumberFactory('unaryMinus', unaryMinusNumber);
var createUnaryPlus = /* #__PURE__ */createNumberFactory('unaryPlus', unaryPlusNumber);
var createAbs = /* #__PURE__ */createNumberFactory('abs', absNumber);
var createAddScalar = /* #__PURE__ */createNumberFactory('addScalar', addNumber);
var createCbrt = /* #__PURE__ */createNumberFactory('cbrt', cbrtNumber);

var createCube = /* #__PURE__ */createNumberFactory('cube', cubeNumber);
var createExp = /* #__PURE__ */createNumberFactory('exp', expNumber);
var createExpm1 = /* #__PURE__ */createNumberFactory('expm1', expm1Number);


var createGcd = /* #__PURE__ */createNumberFactory('gcd', gcdNumber);
var createLcm = /* #__PURE__ */createNumberFactory('lcm', lcmNumber);
var createLog10 = /* #__PURE__ */createNumberFactory('log10', log10Number);
var createLog2 = /* #__PURE__ */createNumberFactory('log2', log2Number);
var createMod = /* #__PURE__ */createNumberFactory('mod', modNumber);
var createMultiplyScalar = /* #__PURE__ */createNumberFactory('multiplyScalar', multiplyNumber);
var createMultiply = /* #__PURE__ */createNumberFactory('multiply', multiplyNumber);
var createNthRoot = /* #__PURE__ */createNumberOptionalSecondArgFactory('nthRoot', nthRootNumber);
var createSign = /* #__PURE__ */createNumberFactory('sign', signNumber);
var createSqrt = /* #__PURE__ */createNumberFactory('sqrt', sqrtNumber);
var createSquare = /* #__PURE__ */createNumberFactory('square', squareNumber);
var createSubtract = /* #__PURE__ */createNumberFactory('subtract', subtractNumber);
var createXgcd = /* #__PURE__ */createNumberFactory('xgcd', xgcdNumber);
var createDivideScalar = /* #__PURE__ */createNumberFactory('divideScalar', divideNumber);
var createPow = /* #__PURE__ */createNumberFactory('pow', powNumber);
var createRound = /* #__PURE__ */createNumberOptionalSecondArgFactory('round', roundNumber);
var createLog = /* #__PURE__ */createNumberOptionalSecondArgFactory('log', logNumber);
var createLog1p = /* #__PURE__ */createNumberFactory('log1p', log1pNumber);
var createAdd = /* #__PURE__ */createNumberFactory('add', addNumber);

var createNorm = /* #__PURE__ */createNumberFactory('norm', normNumber);
var createDivide = /* #__PURE__ */createNumberFactory('divide', divideNumber); // bitwise

var createBitAnd = /* #__PURE__ */createNumberFactory('bitAnd', bitAndNumber);
var createBitNot = /* #__PURE__ */createNumberFactory('bitNot', bitNotNumber);
var createBitOr = /* #__PURE__ */createNumberFactory('bitOr', bitOrNumber);
var createBitXor = /* #__PURE__ */createNumberFactory('bitXor', bitXorNumber);
var createLeftShift = /* #__PURE__ */createNumberFactory('leftShift', leftShiftNumber);
var createRightArithShift = /* #__PURE__ */createNumberFactory('rightArithShift', rightArithShiftNumber);
var createRightLogShift = /* #__PURE__ */createNumberFactory('rightLogShift', rightLogShiftNumber); // combinatorics




 // constants

 // create




 // expression




















 // logical

var createAnd = /* #__PURE__ */createNumberFactory('and', andNumber);
var createNot = /* #__PURE__ */createNumberFactory('not', notNumber);
var createOr = /* #__PURE__ */createNumberFactory('or', orNumber);
var createXor = /* #__PURE__ */createNumberFactory('xor', xorNumber); // matrix






 // FIXME: create a lightweight "number" implementation of subset only supporting plain objects/arrays

var createIndex = /* #__PURE__ */factory_factory('index', [], () => noIndex);
var createMatrix = /* #__PURE__ */factory_factory('matrix', [], () => noMatrix); // FIXME: needed now because subset transform needs it. Remove the need for it in subset

var createSubset = /* #__PURE__ */factory_factory('subset', [], () => noSubset); // TODO: provide number+array implementations for map, filter, forEach, zeros, ...?
// TODO: create range implementation for range?

 // probability

var createCombinations = createNumberFactory('combinations', combinationsNumber);
var createGamma = createNumberFactory('gamma', gammaNumber);
var createLgamma = createNumberFactory('lgamma', lgammaNumber);






 // relational












 // special

 // statistics












 // string


 // trigonometry

var createAcos = /* #__PURE__ */createNumberFactory('acos', acosNumber);
var createAcosh = /* #__PURE__ */createNumberFactory('acosh', acoshNumber);
var createAcot = /* #__PURE__ */createNumberFactory('acot', acotNumber);
var createAcoth = /* #__PURE__ */createNumberFactory('acoth', acothNumber);
var createAcsc = /* #__PURE__ */createNumberFactory('acsc', acscNumber);
var createAcsch = /* #__PURE__ */createNumberFactory('acsch', acschNumber);
var createAsec = /* #__PURE__ */createNumberFactory('asec', asecNumber);
var createAsech = /* #__PURE__ */createNumberFactory('asech', asechNumber);
var createAsin = /* #__PURE__ */createNumberFactory('asin', asinNumber);
var createAsinh = /* #__PURE__ */createNumberFactory('asinh', asinhNumber);
var createAtan = /* #__PURE__ */createNumberFactory('atan', atanNumber);
var createAtan2 = /* #__PURE__ */createNumberFactory('atan2', atan2Number);
var createAtanh = /* #__PURE__ */createNumberFactory('atanh', atanhNumber);
var createCos = /* #__PURE__ */createNumberFactory('cos', cosNumber);
var createCosh = /* #__PURE__ */createNumberFactory('cosh', coshNumber);
var createCot = /* #__PURE__ */createNumberFactory('cot', cotNumber);
var createCoth = /* #__PURE__ */createNumberFactory('coth', cothNumber);
var createCsc = /* #__PURE__ */createNumberFactory('csc', cscNumber);
var createCsch = /* #__PURE__ */createNumberFactory('csch', cschNumber);
var createSec = /* #__PURE__ */createNumberFactory('sec', secNumber);
var createSech = /* #__PURE__ */createNumberFactory('sech', sechNumber);
var createSin = /* #__PURE__ */createNumberFactory('sin', sinNumber);
var createSinh = /* #__PURE__ */createNumberFactory('sinh', sinhNumber);
var createTan = /* #__PURE__ */createNumberFactory('tan', tanNumber);
var createTanh = /* #__PURE__ */createNumberFactory('tanh', tanhNumber); // transforms









var createSubsetTransform = /* #__PURE__ */factory_factory('subset', [], () => noSubset, {
  isTransformFunction: true
});



 // utils


var createIsInteger = /* #__PURE__ */createNumberFactory('isInteger', isIntegerNumber);
var createIsNegative = /* #__PURE__ */createNumberFactory('isNegative', isNegativeNumber);


var createIsPositive = /* #__PURE__ */createNumberFactory('isPositive', isPositiveNumber);
var createIsZero = /* #__PURE__ */createNumberFactory('isZero', isZeroNumber);
var createIsNaN = /* #__PURE__ */createNumberFactory('isNaN', isNaNNumber);


 // json


 // helper functions to create a factory function for a function which only needs typed-function

function createNumberFactory(name, fn) {
  return factory_factory(name, ['typed'], _ref => {
    var {
      typed
    } = _ref;
    return typed(fn);
  });
}

function createNumberOptionalSecondArgFactory(name, fn) {
  return factory_factory(name, ['typed'], _ref2 => {
    var {
      typed
    } = _ref2;
    return typed({
      number: fn,
      'number,number': fn
    });
  });
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesSubset.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */

var subsetDependencies = {
  createSubset: createSubset
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/transform/utils/errorTransform.js

/**
 * Transform zero-based indices to one-based indices in errors
 * @param {Error} err
 * @returns {Error | IndexError} Returns the transformed error
 */

function errorTransform(err) {
  if (err && err.isIndexError) {
    return new IndexError(err.index + 1, err.min + 1, err.max !== undefined ? err.max + 1 : undefined);
  }

  return err;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/utils/access.js


function accessFactory(_ref) {
  var {
    subset
  } = _ref;

  /**
   * Retrieve part of an object:
   *
   * - Retrieve a property from an object
   * - Retrieve a part of a string
   * - Retrieve a matrix subset
   *
   * @param {Object | Array | Matrix | string} object
   * @param {Index} index
   * @return {Object | Array | Matrix | string} Returns the subset
   */
  return function access(object, index) {
    try {
      if (Array.isArray(object)) {
        return subset(object, index);
      } else if (object && typeof object.subset === 'function') {
        // Matrix
        return object.subset(index);
      } else if (typeof object === 'string') {
        // TODO: move getStringSubset into a separate util file, use that
        return subset(object, index);
      } else if (typeof object === 'object') {
        if (!index.isObjectProperty()) {
          throw new TypeError('Cannot apply a numeric index as object property');
        }

        return getSafeProperty(object, index.getObjectProperty());
      } else {
        throw new TypeError('Cannot apply index: unsupported type of object');
      }
    } catch (err) {
      throw errorTransform(err);
    }
  };
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/AccessorNode.js




var AccessorNode_name = 'AccessorNode';
var AccessorNode_dependencies = ['subset', 'Node'];
var createAccessorNode = /* #__PURE__ */factory_factory(AccessorNode_name, AccessorNode_dependencies, _ref => {
  var {
    subset,
    Node
  } = _ref;
  var access = accessFactory({
    subset
  });
  /**
   * @constructor AccessorNode
   * @extends {Node}
   * Access an object property or get a matrix subset
   *
   * @param {Node} object                 The object from which to retrieve
   *                                      a property or subset.
   * @param {IndexNode} index             IndexNode containing ranges
   */

  function AccessorNode(object, index) {
    if (!(this instanceof AccessorNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    if (!isNode(object)) {
      throw new TypeError('Node expected for parameter "object"');
    }

    if (!isIndexNode(index)) {
      throw new TypeError('IndexNode expected for parameter "index"');
    }

    this.object = object || null;
    this.index = index; // readonly property name

    Object.defineProperty(this, 'name', {
      get: function () {
        if (this.index) {
          return this.index.isObjectProperty() ? this.index.getObjectProperty() : '';
        } else {
          return this.object.name || '';
        }
      }.bind(this),
      set: function set() {
        throw new Error('Cannot assign a new name, name is read-only');
      }
    });
  }

  AccessorNode.prototype = new Node();
  AccessorNode.prototype.type = 'AccessorNode';
  AccessorNode.prototype.isAccessorNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  AccessorNode.prototype._compile = function (math, argNames) {
    var evalObject = this.object._compile(math, argNames);

    var evalIndex = this.index._compile(math, argNames);

    if (this.index.isObjectProperty()) {
      var prop = this.index.getObjectProperty();
      return function evalAccessorNode(scope, args, context) {
        // get a property from an object evaluated using the scope.
        return getSafeProperty(evalObject(scope, args, context), prop);
      };
    } else {
      return function evalAccessorNode(scope, args, context) {
        var object = evalObject(scope, args, context);
        var index = evalIndex(scope, args, object); // we pass object here instead of context

        return access(object, index);
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  AccessorNode.prototype.forEach = function (callback) {
    callback(this.object, 'object', this);
    callback(this.index, 'index', this);
  };
  /**
   * Create a new AccessorNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {AccessorNode} Returns a transformed copy of the node
   */


  AccessorNode.prototype.map = function (callback) {
    return new AccessorNode(this._ifNode(callback(this.object, 'object', this)), this._ifNode(callback(this.index, 'index', this)));
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {AccessorNode}
   */


  AccessorNode.prototype.clone = function () {
    return new AccessorNode(this.object, this.index);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string}
   */


  AccessorNode.prototype._toString = function (options) {
    var object = this.object.toString(options);

    if (needParenthesis(this.object)) {
      object = '(' + object + ')';
    }

    return object + this.index.toString(options);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string}
   */


  AccessorNode.prototype.toHTML = function (options) {
    var object = this.object.toHTML(options);

    if (needParenthesis(this.object)) {
      object = '<span class="math-parenthesis math-round-parenthesis">(</span>' + object + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    return object + this.index.toHTML(options);
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string}
   */


  AccessorNode.prototype._toTex = function (options) {
    var object = this.object.toTex(options);

    if (needParenthesis(this.object)) {
      object = '\\left(\' + object + \'\\right)';
    }

    return object + this.index.toTex(options);
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  AccessorNode.prototype.toJSON = function () {
    return {
      mathjs: 'AccessorNode',
      object: this.object,
      index: this.index
    };
  };
  /**
   * Instantiate an AccessorNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "AccessorNode", object: ..., index: ...}`,
   *                       where mathjs is optional
   * @returns {AccessorNode}
   */


  AccessorNode.fromJSON = function (json) {
    return new AccessorNode(json.object, json.index);
  };
  /**
   * Are parenthesis needed?
   * @private
   */


  function needParenthesis(node) {
    // TODO: maybe make a method on the nodes which tells whether they need parenthesis?
    return !(isAccessorNode(node) || isArrayNode(node) || isConstantNode(node) || isFunctionNode(node) || isObjectNode(node) || isParenthesisNode(node) || isSymbolNode(node));
  }

  return AccessorNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesAccessorNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var AccessorNodeDependencies = {
  NodeDependencies: NodeDependencies,
  subsetDependencies: subsetDependencies,
  createAccessorNode: createAccessorNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/ArrayNode.js



var ArrayNode_name = 'ArrayNode';
var ArrayNode_dependencies = ['Node'];
var createArrayNode = /* #__PURE__ */factory_factory(ArrayNode_name, ArrayNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * @constructor ArrayNode
   * @extends {Node}
   * Holds an 1-dimensional array with items
   * @param {Node[]} [items]   1 dimensional array with items
   */
  function ArrayNode(items) {
    if (!(this instanceof ArrayNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.items = items || []; // validate input

    if (!Array.isArray(this.items) || !this.items.every(isNode)) {
      throw new TypeError('Array containing Nodes expected');
    }
  }

  ArrayNode.prototype = new Node();
  ArrayNode.prototype.type = 'ArrayNode';
  ArrayNode.prototype.isArrayNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  ArrayNode.prototype._compile = function (math, argNames) {
    var evalItems = array_map(this.items, function (item) {
      return item._compile(math, argNames);
    });
    var asMatrix = math.config.matrix !== 'Array';

    if (asMatrix) {
      var matrix = math.matrix;
      return function evalArrayNode(scope, args, context) {
        return matrix(array_map(evalItems, function (evalItem) {
          return evalItem(scope, args, context);
        }));
      };
    } else {
      return function evalArrayNode(scope, args, context) {
        return array_map(evalItems, function (evalItem) {
          return evalItem(scope, args, context);
        });
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  ArrayNode.prototype.forEach = function (callback) {
    for (var i = 0; i < this.items.length; i++) {
      var node = this.items[i];
      callback(node, 'items[' + i + ']', this);
    }
  };
  /**
   * Create a new ArrayNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {ArrayNode} Returns a transformed copy of the node
   */


  ArrayNode.prototype.map = function (callback) {
    var items = [];

    for (var i = 0; i < this.items.length; i++) {
      items[i] = this._ifNode(callback(this.items[i], 'items[' + i + ']', this));
    }

    return new ArrayNode(items);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {ArrayNode}
   */


  ArrayNode.prototype.clone = function () {
    return new ArrayNode(this.items.slice(0));
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ArrayNode.prototype._toString = function (options) {
    var items = this.items.map(function (node) {
      return node.toString(options);
    });
    return '[' + items.join(', ') + ']';
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  ArrayNode.prototype.toJSON = function () {
    return {
      mathjs: 'ArrayNode',
      items: this.items
    };
  };
  /**
   * Instantiate an ArrayNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "ArrayNode", items: [...]}`,
   *                       where mathjs is optional
   * @returns {ArrayNode}
   */


  ArrayNode.fromJSON = function (json) {
    return new ArrayNode(json.items);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ArrayNode.prototype.toHTML = function (options) {
    var items = this.items.map(function (node) {
      return node.toHTML(options);
    });
    return '<span class="math-parenthesis math-square-parenthesis">[</span>' + items.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-square-parenthesis">]</span>';
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  ArrayNode.prototype._toTex = function (options) {
    function itemsToTex(items, nested) {
      var mixedItems = items.some(isArrayNode) && !items.every(isArrayNode);
      var itemsFormRow = nested || mixedItems;
      var itemSep = itemsFormRow ? '&' : '\\\\';
      var itemsTex = items.map(function (node) {
        if (node.items) {
          return itemsToTex(node.items, !nested);
        } else {
          return node.toTex(options);
        }
      }).join(itemSep);
      return mixedItems || !itemsFormRow || itemsFormRow && !nested ? '\\begin{bmatrix}' + itemsTex + '\\end{bmatrix}' : itemsTex;
    }

    return itemsToTex(this.items, false);
  };

  return ArrayNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesArrayNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var ArrayNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createArrayNode: createArrayNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesMatrix.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */

var matrixDependencies = {
  createMatrix: createMatrix
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/utils/assign.js


function assignFactory(_ref) {
  var {
    subset,
    matrix
  } = _ref;

  /**
   * Replace part of an object:
   *
   * - Assign a property to an object
   * - Replace a part of a string
   * - Replace a matrix subset
   *
   * @param {Object | Array | Matrix | string} object
   * @param {Index} index
   * @param {*} value
   * @return {Object | Array | Matrix | string} Returns the original object
   *                                            except in case of a string
   */
  // TODO: change assign to return the value instead of the object
  return function assign(object, index, value) {
    try {
      if (Array.isArray(object)) {
        // we use matrix.subset here instead of the function subset because we must not clone the contents
        return matrix(object).subset(index, value).valueOf();
      } else if (object && typeof object.subset === 'function') {
        // Matrix
        return object.subset(index, value);
      } else if (typeof object === 'string') {
        // TODO: move setStringSubset into a separate util file, use that
        return subset(object, index, value);
      } else if (typeof object === 'object') {
        if (!index.isObjectProperty()) {
          throw TypeError('Cannot apply a numeric index as object property');
        }

        setSafeProperty(object, index.getObjectProperty(), value);
        return object;
      } else {
        throw new TypeError('Cannot apply index: unsupported type of object');
      }
    } catch (err) {
      throw errorTransform(err);
    }
  };
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/operators.js
// list of identifiers of nodes in order of their precedence
// also contains information about left/right associativity
// and which other operator the operator is associative with
// Example:
// addition is associative with addition and subtraction, because:
// (a+b)+c=a+(b+c)
// (a+b)-c=a+(b-c)
//
// postfix operators are left associative, prefix operators
// are right associative
//
// It's also possible to set the following properties:
// latexParens: if set to false, this node doesn't need to be enclosed
//              in parentheses when using LaTeX
// latexLeftParens: if set to false, this !OperatorNode's!
//                  left argument doesn't need to be enclosed
//                  in parentheses
// latexRightParens: the same for the right argument

var operators_properties = [{
  // assignment
  AssignmentNode: {},
  FunctionAssignmentNode: {}
}, {
  // conditional expression
  ConditionalNode: {
    latexLeftParens: false,
    latexRightParens: false,
    latexParens: false // conditionals don't need parentheses in LaTeX because
    // they are 2 dimensional

  }
}, {
  // logical or
  'OperatorNode:or': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // logical xor
  'OperatorNode:xor': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // logical and
  'OperatorNode:and': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // bitwise or
  'OperatorNode:bitOr': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // bitwise xor
  'OperatorNode:bitXor': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // bitwise and
  'OperatorNode:bitAnd': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // relational operators
  'OperatorNode:equal': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:unequal': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:smaller': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:larger': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:smallerEq': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:largerEq': {
    associativity: 'left',
    associativeWith: []
  },
  RelationalNode: {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // bitshift operators
  'OperatorNode:leftShift': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:rightArithShift': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:rightLogShift': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // unit conversion
  'OperatorNode:to': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // range
  RangeNode: {}
}, {
  // addition, subtraction
  'OperatorNode:add': {
    associativity: 'left',
    associativeWith: ['OperatorNode:add', 'OperatorNode:subtract']
  },
  'OperatorNode:subtract': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // multiply, divide, modulus
  'OperatorNode:multiply': {
    associativity: 'left',
    associativeWith: ['OperatorNode:multiply', 'OperatorNode:divide', 'Operator:dotMultiply', 'Operator:dotDivide']
  },
  'OperatorNode:divide': {
    associativity: 'left',
    associativeWith: [],
    latexLeftParens: false,
    latexRightParens: false,
    latexParens: false // fractions don't require parentheses because
    // they're 2 dimensional, so parens aren't needed
    // in LaTeX

  },
  'OperatorNode:dotMultiply': {
    associativity: 'left',
    associativeWith: ['OperatorNode:multiply', 'OperatorNode:divide', 'OperatorNode:dotMultiply', 'OperatorNode:doDivide']
  },
  'OperatorNode:dotDivide': {
    associativity: 'left',
    associativeWith: []
  },
  'OperatorNode:mod': {
    associativity: 'left',
    associativeWith: []
  }
}, {
  // unary prefix operators
  'OperatorNode:unaryPlus': {
    associativity: 'right'
  },
  'OperatorNode:unaryMinus': {
    associativity: 'right'
  },
  'OperatorNode:bitNot': {
    associativity: 'right'
  },
  'OperatorNode:not': {
    associativity: 'right'
  }
}, {
  // exponentiation
  'OperatorNode:pow': {
    associativity: 'right',
    associativeWith: [],
    latexRightParens: false // the exponent doesn't need parentheses in
    // LaTeX because it's 2 dimensional
    // (it's on top)

  },
  'OperatorNode:dotPow': {
    associativity: 'right',
    associativeWith: []
  }
}, {
  // factorial
  'OperatorNode:factorial': {
    associativity: 'left'
  }
}, {
  // matrix transpose
  'OperatorNode:transpose': {
    associativity: 'left'
  }
}];
/**
 * Get the precedence of a Node.
 * Higher number for higher precedence, starting with 0.
 * Returns null if the precedence is undefined.
 *
 * @param {Node} _node
 * @param {string} parenthesis
 * @return {number | null}
 */

function getPrecedence(_node, parenthesis) {
  var node = _node;

  if (parenthesis !== 'keep') {
    // ParenthesisNodes are only ignored when not in 'keep' mode
    node = _node.getContent();
  }

  var identifier = node.getIdentifier();

  for (var i = 0; i < operators_properties.length; i++) {
    if (identifier in operators_properties[i]) {
      return i;
    }
  }

  return null;
}
/**
 * Get the associativity of an operator (left or right).
 * Returns a string containing 'left' or 'right' or null if
 * the associativity is not defined.
 *
 * @param {Node} _node
 * @param {string} parenthesis
 * @return {string|null}
 * @throws {Error}
 */

function getAssociativity(_node, parenthesis) {
  var node = _node;

  if (parenthesis !== 'keep') {
    // ParenthesisNodes are only ignored when not in 'keep' mode
    node = _node.getContent();
  }

  var identifier = node.getIdentifier();
  var index = getPrecedence(node, parenthesis);

  if (index === null) {
    // node isn't in the list
    return null;
  }

  var property = operators_properties[index][identifier];

  if (object_hasOwnProperty(property, 'associativity')) {
    if (property.associativity === 'left') {
      return 'left';
    }

    if (property.associativity === 'right') {
      return 'right';
    } // associativity is invalid


    throw Error('\'' + identifier + '\' has the invalid associativity \'' + property.associativity + '\'.');
  } // associativity is undefined


  return null;
}
/**
 * Check if an operator is associative with another operator.
 * Returns either true or false or null if not defined.
 *
 * @param {Node} nodeA
 * @param {Node} nodeB
 * @param {string} parenthesis
 * @return {boolean | null}
 */

function isAssociativeWith(nodeA, nodeB, parenthesis) {
  // ParenthesisNodes are only ignored when not in 'keep' mode
  var a = parenthesis !== 'keep' ? nodeA.getContent() : nodeA;
  var b = parenthesis !== 'keep' ? nodeA.getContent() : nodeB;
  var identifierA = a.getIdentifier();
  var identifierB = b.getIdentifier();
  var index = getPrecedence(a, parenthesis);

  if (index === null) {
    // node isn't in the list
    return null;
  }

  var property = operators_properties[index][identifierA];

  if (object_hasOwnProperty(property, 'associativeWith') && property.associativeWith instanceof Array) {
    for (var i = 0; i < property.associativeWith.length; i++) {
      if (property.associativeWith[i] === identifierB) {
        return true;
      }
    }

    return false;
  } // associativeWith is not defined


  return null;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/AssignmentNode.js






var AssignmentNode_name = 'AssignmentNode';
var AssignmentNode_dependencies = ['subset', '?matrix', // FIXME: should not be needed at all, should be handled by subset
'Node'];
var createAssignmentNode = /* #__PURE__ */factory_factory(AssignmentNode_name, AssignmentNode_dependencies, _ref => {
  var {
    subset,
    matrix,
    Node
  } = _ref;
  var access = accessFactory({
    subset
  });
  var assign = assignFactory({
    subset,
    matrix
  });
  /**
   * @constructor AssignmentNode
   * @extends {Node}
   *
   * Define a symbol, like `a=3.2`, update a property like `a.b=3.2`, or
   * replace a subset of a matrix like `A[2,2]=42`.
   *
   * Syntax:
   *
   *     new AssignmentNode(symbol, value)
   *     new AssignmentNode(object, index, value)
   *
   * Usage:
   *
   *    new AssignmentNode(new SymbolNode('a'), new ConstantNode(2))                       // a=2
   *    new AssignmentNode(new SymbolNode('a'), new IndexNode('b'), new ConstantNode(2))   // a.b=2
   *    new AssignmentNode(new SymbolNode('a'), new IndexNode(1, 2), new ConstantNode(3))  // a[1,2]=3
   *
   * @param {SymbolNode | AccessorNode} object  Object on which to assign a value
   * @param {IndexNode} [index=null]            Index, property name or matrix
   *                                            index. Optional. If not provided
   *                                            and `object` is a SymbolNode,
   *                                            the property is assigned to the
   *                                            global scope.
   * @param {Node} value                        The value to be assigned
   */

  function AssignmentNode(object, index, value) {
    if (!(this instanceof AssignmentNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.object = object;
    this.index = value ? index : null;
    this.value = value || index; // validate input

    if (!isSymbolNode(object) && !isAccessorNode(object)) {
      throw new TypeError('SymbolNode or AccessorNode expected as "object"');
    }

    if (isSymbolNode(object) && object.name === 'end') {
      throw new Error('Cannot assign to symbol "end"');
    }

    if (this.index && !isIndexNode(this.index)) {
      // index is optional
      throw new TypeError('IndexNode expected as "index"');
    }

    if (!isNode(this.value)) {
      throw new TypeError('Node expected as "value"');
    } // readonly property name


    Object.defineProperty(this, 'name', {
      get: function () {
        if (this.index) {
          return this.index.isObjectProperty() ? this.index.getObjectProperty() : '';
        } else {
          return this.object.name || '';
        }
      }.bind(this),
      set: function set() {
        throw new Error('Cannot assign a new name, name is read-only');
      }
    });
  }

  AssignmentNode.prototype = new Node();
  AssignmentNode.prototype.type = 'AssignmentNode';
  AssignmentNode.prototype.isAssignmentNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  AssignmentNode.prototype._compile = function (math, argNames) {
    var evalObject = this.object._compile(math, argNames);

    var evalIndex = this.index ? this.index._compile(math, argNames) : null;

    var evalValue = this.value._compile(math, argNames);

    var name = this.object.name;

    if (!this.index) {
      // apply a variable to the scope, for example `a=2`
      if (!isSymbolNode(this.object)) {
        throw new TypeError('SymbolNode expected as object');
      }

      return function evalAssignmentNode(scope, args, context) {
        var value = evalValue(scope, args, context);
        scope.set(name, value);
        return value;
      };
    } else if (this.index.isObjectProperty()) {
      // apply an object property for example `a.b=2`
      var prop = this.index.getObjectProperty();
      return function evalAssignmentNode(scope, args, context) {
        var object = evalObject(scope, args, context);
        var value = evalValue(scope, args, context);
        setSafeProperty(object, prop, value);
        return value;
      };
    } else if (isSymbolNode(this.object)) {
      // update a matrix subset, for example `a[2]=3`
      return function evalAssignmentNode(scope, args, context) {
        var childObject = evalObject(scope, args, context);
        var value = evalValue(scope, args, context);
        var index = evalIndex(scope, args, childObject); // Important:  we pass childObject instead of context

        scope.set(name, assign(childObject, index, value));
        return value;
      };
    } else {
      // isAccessorNode(node.object) === true
      // update a matrix subset, for example `a.b[2]=3`
      // we will not use the compile function of the AccessorNode, but compile it
      // ourselves here as we need the parent object of the AccessorNode:
      // wee need to apply the updated object to parent object
      var evalParentObject = this.object.object._compile(math, argNames);

      if (this.object.index.isObjectProperty()) {
        var parentProp = this.object.index.getObjectProperty();
        return function evalAssignmentNode(scope, args, context) {
          var parent = evalParentObject(scope, args, context);
          var childObject = getSafeProperty(parent, parentProp);
          var index = evalIndex(scope, args, childObject); // Important: we pass childObject instead of context

          var value = evalValue(scope, args, context);
          setSafeProperty(parent, parentProp, assign(childObject, index, value));
          return value;
        };
      } else {
        // if some parameters use the 'end' parameter, we need to calculate the size
        var evalParentIndex = this.object.index._compile(math, argNames);

        return function evalAssignmentNode(scope, args, context) {
          var parent = evalParentObject(scope, args, context);
          var parentIndex = evalParentIndex(scope, args, parent); // Important: we pass parent instead of context

          var childObject = access(parent, parentIndex);
          var index = evalIndex(scope, args, childObject); // Important:  we pass childObject instead of context

          var value = evalValue(scope, args, context);
          assign(parent, parentIndex, assign(childObject, index, value));
          return value;
        };
      }
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  AssignmentNode.prototype.forEach = function (callback) {
    callback(this.object, 'object', this);

    if (this.index) {
      callback(this.index, 'index', this);
    }

    callback(this.value, 'value', this);
  };
  /**
   * Create a new AssignmentNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {AssignmentNode} Returns a transformed copy of the node
   */


  AssignmentNode.prototype.map = function (callback) {
    var object = this._ifNode(callback(this.object, 'object', this));

    var index = this.index ? this._ifNode(callback(this.index, 'index', this)) : null;

    var value = this._ifNode(callback(this.value, 'value', this));

    return new AssignmentNode(object, index, value);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {AssignmentNode}
   */


  AssignmentNode.prototype.clone = function () {
    return new AssignmentNode(this.object, this.index, this.value);
  };
  /*
   * Is parenthesis needed?
   * @param {node} node
   * @param {string} [parenthesis='keep']
   * @private
   */


  function needParenthesis(node, parenthesis) {
    if (!parenthesis) {
      parenthesis = 'keep';
    }

    var precedence = getPrecedence(node, parenthesis);
    var exprPrecedence = getPrecedence(node.value, parenthesis);
    return parenthesis === 'all' || exprPrecedence !== null && exprPrecedence <= precedence;
  }
  /**
   * Get string representation
   * @param {Object} options
   * @return {string}
   */


  AssignmentNode.prototype._toString = function (options) {
    var object = this.object.toString(options);
    var index = this.index ? this.index.toString(options) : '';
    var value = this.value.toString(options);

    if (needParenthesis(this, options && options.parenthesis)) {
      value = '(' + value + ')';
    }

    return object + index + ' = ' + value;
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  AssignmentNode.prototype.toJSON = function () {
    return {
      mathjs: 'AssignmentNode',
      object: this.object,
      index: this.index,
      value: this.value
    };
  };
  /**
   * Instantiate an AssignmentNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "AssignmentNode", object: ..., index: ..., value: ...}`,
   *                       where mathjs is optional
   * @returns {AssignmentNode}
   */


  AssignmentNode.fromJSON = function (json) {
    return new AssignmentNode(json.object, json.index, json.value);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string}
   */


  AssignmentNode.prototype.toHTML = function (options) {
    var object = this.object.toHTML(options);
    var index = this.index ? this.index.toHTML(options) : '';
    var value = this.value.toHTML(options);

    if (needParenthesis(this, options && options.parenthesis)) {
      value = '<span class="math-paranthesis math-round-parenthesis">(</span>' + value + '<span class="math-paranthesis math-round-parenthesis">)</span>';
    }

    return object + index + '<span class="math-operator math-assignment-operator math-variable-assignment-operator math-binary-operator">=</span>' + value;
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string}
   */


  AssignmentNode.prototype._toTex = function (options) {
    var object = this.object.toTex(options);
    var index = this.index ? this.index.toTex(options) : '';
    var value = this.value.toTex(options);

    if (needParenthesis(this, options && options.parenthesis)) {
      value = "\\left(".concat(value, "\\right)");
    }

    return object + index + ':=' + value;
  };

  return AssignmentNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesAssignmentNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */




var AssignmentNodeDependencies = {
  matrixDependencies: matrixDependencies,
  NodeDependencies: NodeDependencies,
  subsetDependencies: subsetDependencies,
  createAssignmentNode: createAssignmentNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/type/resultset/ResultSet.js

var ResultSet_name = 'ResultSet';
var ResultSet_dependencies = [];
var createResultSet = /* #__PURE__ */factory_factory(ResultSet_name, ResultSet_dependencies, () => {
  /**
   * A ResultSet contains a list or results
   * @class ResultSet
   * @param {Array} entries
   * @constructor ResultSet
   */
  function ResultSet(entries) {
    if (!(this instanceof ResultSet)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.entries = entries || [];
  }
  /**
   * Attach type information
   */


  ResultSet.prototype.type = 'ResultSet';
  ResultSet.prototype.isResultSet = true;
  /**
   * Returns the array with results hold by this ResultSet
   * @memberof ResultSet
   * @returns {Array} entries
   */

  ResultSet.prototype.valueOf = function () {
    return this.entries;
  };
  /**
   * Returns the stringified results of the ResultSet
   * @memberof ResultSet
   * @returns {string} string
   */


  ResultSet.prototype.toString = function () {
    return '[' + this.entries.join(', ') + ']';
  };
  /**
   * Get a JSON representation of the ResultSet
   * @memberof ResultSet
   * @returns {Object} Returns a JSON object structured as:
   *                   `{"mathjs": "ResultSet", "entries": [...]}`
   */


  ResultSet.prototype.toJSON = function () {
    return {
      mathjs: 'ResultSet',
      entries: this.entries
    };
  };
  /**
   * Instantiate a ResultSet from a JSON object
   * @memberof ResultSet
   * @param {Object} json  A JSON object structured as:
   *                       `{"mathjs": "ResultSet", "entries": [...]}`
   * @return {ResultSet}
   */


  ResultSet.fromJSON = function (json) {
    return new ResultSet(json.entries);
  };

  return ResultSet;
}, {
  isClass: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesResultSet.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */

var ResultSetDependencies = {
  createResultSet: createResultSet
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/BlockNode.js



var BlockNode_name = 'BlockNode';
var BlockNode_dependencies = ['ResultSet', 'Node'];
var createBlockNode = /* #__PURE__ */factory_factory(BlockNode_name, BlockNode_dependencies, _ref => {
  var {
    ResultSet,
    Node
  } = _ref;

  /**
   * @constructor BlockNode
   * @extends {Node}
   * Holds a set with blocks
   * @param {Array.<{node: Node} | {node: Node, visible: boolean}>} blocks
   *            An array with blocks, where a block is constructed as an Object
   *            with properties block, which is a Node, and visible, which is
   *            a boolean. The property visible is optional and is true by default
   */
  function BlockNode(blocks) {
    if (!(this instanceof BlockNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate input, copy blocks


    if (!Array.isArray(blocks)) throw new Error('Array expected');
    this.blocks = blocks.map(function (block) {
      var node = block && block.node;
      var visible = block && block.visible !== undefined ? block.visible : true;
      if (!isNode(node)) throw new TypeError('Property "node" must be a Node');
      if (typeof visible !== 'boolean') throw new TypeError('Property "visible" must be a boolean');
      return {
        node,
        visible
      };
    });
  }

  BlockNode.prototype = new Node();
  BlockNode.prototype.type = 'BlockNode';
  BlockNode.prototype.isBlockNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  BlockNode.prototype._compile = function (math, argNames) {
    var evalBlocks = array_map(this.blocks, function (block) {
      return {
        evaluate: block.node._compile(math, argNames),
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
  /**
   * Execute a callback for each of the child blocks of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  BlockNode.prototype.forEach = function (callback) {
    for (var i = 0; i < this.blocks.length; i++) {
      callback(this.blocks[i].node, 'blocks[' + i + '].node', this);
    }
  };
  /**
   * Create a new BlockNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {BlockNode} Returns a transformed copy of the node
   */


  BlockNode.prototype.map = function (callback) {
    var blocks = [];

    for (var i = 0; i < this.blocks.length; i++) {
      var block = this.blocks[i];

      var node = this._ifNode(callback(block.node, 'blocks[' + i + '].node', this));

      blocks[i] = {
        node,
        visible: block.visible
      };
    }

    return new BlockNode(blocks);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {BlockNode}
   */


  BlockNode.prototype.clone = function () {
    var blocks = this.blocks.map(function (block) {
      return {
        node: block.node,
        visible: block.visible
      };
    });
    return new BlockNode(blocks);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  BlockNode.prototype._toString = function (options) {
    return this.blocks.map(function (param) {
      return param.node.toString(options) + (param.visible ? '' : ';');
    }).join('\n');
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  BlockNode.prototype.toJSON = function () {
    return {
      mathjs: 'BlockNode',
      blocks: this.blocks
    };
  };
  /**
   * Instantiate an BlockNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "BlockNode", blocks: [{node: ..., visible: false}, ...]}`,
   *                       where mathjs is optional
   * @returns {BlockNode}
   */


  BlockNode.fromJSON = function (json) {
    return new BlockNode(json.blocks);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  BlockNode.prototype.toHTML = function (options) {
    return this.blocks.map(function (param) {
      return param.node.toHTML(options) + (param.visible ? '' : '<span class="math-separator">;</span>');
    }).join('<span class="math-separator"><br /></span>');
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  BlockNode.prototype._toTex = function (options) {
    return this.blocks.map(function (param) {
      return param.node.toTex(options) + (param.visible ? '' : ';');
    }).join('\\;\\;\n');
  };

  return BlockNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesBlockNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var BlockNodeDependencies = {
  NodeDependencies: NodeDependencies,
  ResultSetDependencies: ResultSetDependencies,
  createBlockNode: createBlockNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/ConditionalNode.js



var ConditionalNode_name = 'ConditionalNode';
var ConditionalNode_dependencies = ['Node'];
var createConditionalNode = /* #__PURE__ */factory_factory(ConditionalNode_name, ConditionalNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * A lazy evaluating conditional operator: 'condition ? trueExpr : falseExpr'
   *
   * @param {Node} condition   Condition, must result in a boolean
   * @param {Node} trueExpr    Expression evaluated when condition is true
   * @param {Node} falseExpr   Expression evaluated when condition is true
   *
   * @constructor ConditionalNode
   * @extends {Node}
   */
  function ConditionalNode(condition, trueExpr, falseExpr) {
    if (!(this instanceof ConditionalNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    if (!isNode(condition)) throw new TypeError('Parameter condition must be a Node');
    if (!isNode(trueExpr)) throw new TypeError('Parameter trueExpr must be a Node');
    if (!isNode(falseExpr)) throw new TypeError('Parameter falseExpr must be a Node');
    this.condition = condition;
    this.trueExpr = trueExpr;
    this.falseExpr = falseExpr;
  }

  ConditionalNode.prototype = new Node();
  ConditionalNode.prototype.type = 'ConditionalNode';
  ConditionalNode.prototype.isConditionalNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  ConditionalNode.prototype._compile = function (math, argNames) {
    var evalCondition = this.condition._compile(math, argNames);

    var evalTrueExpr = this.trueExpr._compile(math, argNames);

    var evalFalseExpr = this.falseExpr._compile(math, argNames);

    return function evalConditionalNode(scope, args, context) {
      return testCondition(evalCondition(scope, args, context)) ? evalTrueExpr(scope, args, context) : evalFalseExpr(scope, args, context);
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  ConditionalNode.prototype.forEach = function (callback) {
    callback(this.condition, 'condition', this);
    callback(this.trueExpr, 'trueExpr', this);
    callback(this.falseExpr, 'falseExpr', this);
  };
  /**
   * Create a new ConditionalNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {ConditionalNode} Returns a transformed copy of the node
   */


  ConditionalNode.prototype.map = function (callback) {
    return new ConditionalNode(this._ifNode(callback(this.condition, 'condition', this)), this._ifNode(callback(this.trueExpr, 'trueExpr', this)), this._ifNode(callback(this.falseExpr, 'falseExpr', this)));
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {ConditionalNode}
   */


  ConditionalNode.prototype.clone = function () {
    return new ConditionalNode(this.condition, this.trueExpr, this.falseExpr);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   */


  ConditionalNode.prototype._toString = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var precedence = getPrecedence(this, parenthesis); // Enclose Arguments in parentheses if they are an OperatorNode
    // or have lower or equal precedence
    // NOTE: enclosing all OperatorNodes in parentheses is a decision
    // purely based on aesthetics and readability

    var condition = this.condition.toString(options);
    var conditionPrecedence = getPrecedence(this.condition, parenthesis);

    if (parenthesis === 'all' || this.condition.type === 'OperatorNode' || conditionPrecedence !== null && conditionPrecedence <= precedence) {
      condition = '(' + condition + ')';
    }

    var trueExpr = this.trueExpr.toString(options);
    var truePrecedence = getPrecedence(this.trueExpr, parenthesis);

    if (parenthesis === 'all' || this.trueExpr.type === 'OperatorNode' || truePrecedence !== null && truePrecedence <= precedence) {
      trueExpr = '(' + trueExpr + ')';
    }

    var falseExpr = this.falseExpr.toString(options);
    var falsePrecedence = getPrecedence(this.falseExpr, parenthesis);

    if (parenthesis === 'all' || this.falseExpr.type === 'OperatorNode' || falsePrecedence !== null && falsePrecedence <= precedence) {
      falseExpr = '(' + falseExpr + ')';
    }

    return condition + ' ? ' + trueExpr + ' : ' + falseExpr;
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  ConditionalNode.prototype.toJSON = function () {
    return {
      mathjs: 'ConditionalNode',
      condition: this.condition,
      trueExpr: this.trueExpr,
      falseExpr: this.falseExpr
    };
  };
  /**
   * Instantiate an ConditionalNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "ConditionalNode", "condition": ..., "trueExpr": ..., "falseExpr": ...}`,
   *                       where mathjs is optional
   * @returns {ConditionalNode}
   */


  ConditionalNode.fromJSON = function (json) {
    return new ConditionalNode(json.condition, json.trueExpr, json.falseExpr);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  ConditionalNode.prototype.toHTML = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var precedence = getPrecedence(this, parenthesis); // Enclose Arguments in parentheses if they are an OperatorNode
    // or have lower or equal precedence
    // NOTE: enclosing all OperatorNodes in parentheses is a decision
    // purely based on aesthetics and readability

    var condition = this.condition.toHTML(options);
    var conditionPrecedence = getPrecedence(this.condition, parenthesis);

    if (parenthesis === 'all' || this.condition.type === 'OperatorNode' || conditionPrecedence !== null && conditionPrecedence <= precedence) {
      condition = '<span class="math-parenthesis math-round-parenthesis">(</span>' + condition + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    var trueExpr = this.trueExpr.toHTML(options);
    var truePrecedence = getPrecedence(this.trueExpr, parenthesis);

    if (parenthesis === 'all' || this.trueExpr.type === 'OperatorNode' || truePrecedence !== null && truePrecedence <= precedence) {
      trueExpr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + trueExpr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    var falseExpr = this.falseExpr.toHTML(options);
    var falsePrecedence = getPrecedence(this.falseExpr, parenthesis);

    if (parenthesis === 'all' || this.falseExpr.type === 'OperatorNode' || falsePrecedence !== null && falsePrecedence <= precedence) {
      falseExpr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + falseExpr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    return condition + '<span class="math-operator math-conditional-operator">?</span>' + trueExpr + '<span class="math-operator math-conditional-operator">:</span>' + falseExpr;
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  ConditionalNode.prototype._toTex = function (options) {
    return '\\begin{cases} {' + this.trueExpr.toTex(options) + '}, &\\quad{\\text{if }\\;' + this.condition.toTex(options) + '}\\\\{' + this.falseExpr.toTex(options) + '}, &\\quad{\\text{otherwise}}\\end{cases}';
  };
  /**
   * Test whether a condition is met
   * @param {*} condition
   * @returns {boolean} true if condition is true or non-zero, else false
   */


  function testCondition(condition) {
    if (typeof condition === 'number' || typeof condition === 'boolean' || typeof condition === 'string') {
      return !!condition;
    }

    if (condition) {
      if (isBigNumber(condition)) {
        return !condition.isZero();
      }

      if (isComplex(condition)) {
        return !!(condition.re || condition.im);
      }

      if (is_isUnit(condition)) {
        return !!condition.value;
      }
    }

    if (condition === null || condition === undefined) {
      return false;
    }

    throw new TypeError('Unsupported type of condition "' + typeOf(condition) + '"');
  }

  return ConditionalNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesConditionalNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var ConditionalNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createConditionalNode: createConditionalNode
};
// EXTERNAL MODULE: ./node_modules/escape-latex/dist/index.js
var dist = __webpack_require__("4788");
var dist_default = /*#__PURE__*/__webpack_require__.n(dist);

// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/latex.js
/* eslint no-template-curly-in-string: "off" */


var latexSymbols = {
  // GREEK LETTERS
  Alpha: 'A',
  alpha: '\\alpha',
  Beta: 'B',
  beta: '\\beta',
  Gamma: '\\Gamma',
  gamma: '\\gamma',
  Delta: '\\Delta',
  delta: '\\delta',
  Epsilon: 'E',
  epsilon: '\\epsilon',
  varepsilon: '\\varepsilon',
  Zeta: 'Z',
  zeta: '\\zeta',
  Eta: 'H',
  eta: '\\eta',
  Theta: '\\Theta',
  theta: '\\theta',
  vartheta: '\\vartheta',
  Iota: 'I',
  iota: '\\iota',
  Kappa: 'K',
  kappa: '\\kappa',
  varkappa: '\\varkappa',
  Lambda: '\\Lambda',
  lambda: '\\lambda',
  Mu: 'M',
  mu: '\\mu',
  Nu: 'N',
  nu: '\\nu',
  Xi: '\\Xi',
  xi: '\\xi',
  Omicron: 'O',
  omicron: 'o',
  Pi: '\\Pi',
  pi: '\\pi',
  varpi: '\\varpi',
  Rho: 'P',
  rho: '\\rho',
  varrho: '\\varrho',
  Sigma: '\\Sigma',
  sigma: '\\sigma',
  varsigma: '\\varsigma',
  Tau: 'T',
  tau: '\\tau',
  Upsilon: '\\Upsilon',
  upsilon: '\\upsilon',
  Phi: '\\Phi',
  phi: '\\phi',
  varphi: '\\varphi',
  Chi: 'X',
  chi: '\\chi',
  Psi: '\\Psi',
  psi: '\\psi',
  Omega: '\\Omega',
  omega: '\\omega',
  // logic
  true: '\\mathrm{True}',
  false: '\\mathrm{False}',
  // other
  i: 'i',
  // TODO use \i ??
  inf: '\\infty',
  Inf: '\\infty',
  infinity: '\\infty',
  Infinity: '\\infty',
  oo: '\\infty',
  lim: '\\lim',
  undefined: '\\mathbf{?}'
};
var latexOperators = {
  transpose: '^\\top',
  ctranspose: '^H',
  factorial: '!',
  pow: '^',
  dotPow: '.^\\wedge',
  // TODO find ideal solution
  unaryPlus: '+',
  unaryMinus: '-',
  bitNot: '\\~',
  // TODO find ideal solution
  not: '\\neg',
  multiply: '\\cdot',
  divide: '\\frac',
  // TODO how to handle that properly?
  dotMultiply: '.\\cdot',
  // TODO find ideal solution
  dotDivide: '.:',
  // TODO find ideal solution
  mod: '\\mod',
  add: '+',
  subtract: '-',
  to: '\\rightarrow',
  leftShift: '<<',
  rightArithShift: '>>',
  rightLogShift: '>>>',
  equal: '=',
  unequal: '\\neq',
  smaller: '<',
  larger: '>',
  smallerEq: '\\leq',
  largerEq: '\\geq',
  bitAnd: '\\&',
  bitXor: '\\underline{|}',
  bitOr: '|',
  and: '\\wedge',
  xor: '\\veebar',
  or: '\\vee'
};
var latexFunctions = {
  // arithmetic
  abs: {
    1: '\\left|${args[0]}\\right|'
  },
  add: {
    2: "\\left(${args[0]}".concat(latexOperators.add, "${args[1]}\\right)")
  },
  cbrt: {
    1: '\\sqrt[3]{${args[0]}}'
  },
  ceil: {
    1: '\\left\\lceil${args[0]}\\right\\rceil'
  },
  cube: {
    1: '\\left(${args[0]}\\right)^3'
  },
  divide: {
    2: '\\frac{${args[0]}}{${args[1]}}'
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
    1: '\\exp\\left(${args[0]}\\right)'
  },
  expm1: "\\left(e".concat(latexOperators.pow, "{${args[0]}}-1\\right)"),
  fix: {
    1: '\\mathrm{${name}}\\left(${args[0]}\\right)'
  },
  floor: {
    1: '\\left\\lfloor${args[0]}\\right\\rfloor'
  },
  gcd: '\\gcd\\left(${args}\\right)',
  hypot: '\\hypot\\left(${args}\\right)',
  log: {
    1: '\\ln\\left(${args[0]}\\right)',
    2: '\\log_{${args[1]}}\\left(${args[0]}\\right)'
  },
  log10: {
    1: '\\log_{10}\\left(${args[0]}\\right)'
  },
  log1p: {
    1: '\\ln\\left(${args[0]}+1\\right)',
    2: '\\log_{${args[1]}}\\left(${args[0]}+1\\right)'
  },
  log2: '\\log_{2}\\left(${args[0]}\\right)',
  mod: {
    2: "\\left(${args[0]}".concat(latexOperators.mod, "${args[1]}\\right)")
  },
  multiply: {
    2: "\\left(${args[0]}".concat(latexOperators.multiply, "${args[1]}\\right)")
  },
  norm: {
    1: '\\left\\|${args[0]}\\right\\|',
    2: undefined // use default template

  },
  nthRoot: {
    2: '\\sqrt[${args[1]}]{${args[0]}}'
  },
  nthRoots: {
    2: '\\{y : $y^{args[1]} = {${args[0]}}\\}'
  },
  pow: {
    2: "\\left(${args[0]}\\right)".concat(latexOperators.pow, "{${args[1]}}")
  },
  round: {
    1: '\\left\\lfloor${args[0]}\\right\\rceil',
    2: undefined // use default template

  },
  sign: {
    1: '\\mathrm{${name}}\\left(${args[0]}\\right)'
  },
  sqrt: {
    1: '\\sqrt{${args[0]}}'
  },
  square: {
    1: '\\left(${args[0]}\\right)^2'
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
    1: latexOperators.bitNot + '\\left(${args[0]}\\right)'
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
    1: '\\mathrm{B}_{${args[0]}}'
  },
  catalan: {
    1: '\\mathrm{C}_{${args[0]}}'
  },
  stirlingS2: {
    2: '\\mathrm{S}\\left(${args}\\right)'
  },
  // complex
  arg: {
    1: '\\arg\\left(${args[0]}\\right)'
  },
  conj: {
    1: '\\left(${args[0]}\\right)^*'
  },
  im: {
    1: '\\Im\\left\\lbrace${args[0]}\\right\\rbrace'
  },
  re: {
    1: '\\Re\\left\\lbrace${args[0]}\\right\\rbrace'
  },
  // logical
  and: {
    2: "\\left(${args[0]}".concat(latexOperators.and, "${args[1]}\\right)")
  },
  not: {
    1: latexOperators.not + '\\left(${args[0]}\\right)'
  },
  or: {
    2: "\\left(${args[0]}".concat(latexOperators.or, "${args[1]}\\right)")
  },
  xor: {
    2: "\\left(${args[0]}".concat(latexOperators.xor, "${args[1]}\\right)")
  },
  // matrix
  cross: {
    2: '\\left(${args[0]}\\right)\\times\\left(${args[1]}\\right)'
  },
  ctranspose: {
    1: "\\left(${args[0]}\\right)".concat(latexOperators.ctranspose)
  },
  det: {
    1: '\\det\\left(${args[0]}\\right)'
  },
  dot: {
    2: '\\left(${args[0]}\\cdot${args[1]}\\right)'
  },
  expm: {
    1: '\\exp\\left(${args[0]}\\right)'
  },
  inv: {
    1: '\\left(${args[0]}\\right)^{-1}'
  },
  pinv: {
    1: '\\left(${args[0]}\\right)^{+}'
  },
  sqrtm: {
    1: "{${args[0]}}".concat(latexOperators.pow, "{\\frac{1}{2}}")
  },
  trace: {
    1: '\\mathrm{tr}\\left(${args[0]}\\right)'
  },
  transpose: {
    1: "\\left(${args[0]}\\right)".concat(latexOperators.transpose)
  },
  // probability
  combinations: {
    2: '\\binom{${args[0]}}{${args[1]}}'
  },
  combinationsWithRep: {
    2: '\\left(\\!\\!{\\binom{${args[0]}}{${args[1]}}}\\!\\!\\right)'
  },
  factorial: {
    1: "\\left(${args[0]}\\right)".concat(latexOperators.factorial)
  },
  gamma: {
    1: '\\Gamma\\left(${args[0]}\\right)'
  },
  lgamma: {
    1: '\\ln\\Gamma\\left(${args[0]}\\right)'
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
    1: 'erf\\left(${args[0]}\\right)'
  },
  // statistics
  max: '\\max\\left(${args}\\right)',
  min: '\\min\\left(${args}\\right)',
  variance: '\\mathrm{Var}\\left(${args}\\right)',
  // trigonometry
  acos: {
    1: '\\cos^{-1}\\left(${args[0]}\\right)'
  },
  acosh: {
    1: '\\cosh^{-1}\\left(${args[0]}\\right)'
  },
  acot: {
    1: '\\cot^{-1}\\left(${args[0]}\\right)'
  },
  acoth: {
    1: '\\coth^{-1}\\left(${args[0]}\\right)'
  },
  acsc: {
    1: '\\csc^{-1}\\left(${args[0]}\\right)'
  },
  acsch: {
    1: '\\mathrm{csch}^{-1}\\left(${args[0]}\\right)'
  },
  asec: {
    1: '\\sec^{-1}\\left(${args[0]}\\right)'
  },
  asech: {
    1: '\\mathrm{sech}^{-1}\\left(${args[0]}\\right)'
  },
  asin: {
    1: '\\sin^{-1}\\left(${args[0]}\\right)'
  },
  asinh: {
    1: '\\sinh^{-1}\\left(${args[0]}\\right)'
  },
  atan: {
    1: '\\tan^{-1}\\left(${args[0]}\\right)'
  },
  atan2: {
    2: '\\mathrm{atan2}\\left(${args}\\right)'
  },
  atanh: {
    1: '\\tanh^{-1}\\left(${args[0]}\\right)'
  },
  cos: {
    1: '\\cos\\left(${args[0]}\\right)'
  },
  cosh: {
    1: '\\cosh\\left(${args[0]}\\right)'
  },
  cot: {
    1: '\\cot\\left(${args[0]}\\right)'
  },
  coth: {
    1: '\\coth\\left(${args[0]}\\right)'
  },
  csc: {
    1: '\\csc\\left(${args[0]}\\right)'
  },
  csch: {
    1: '\\mathrm{csch}\\left(${args[0]}\\right)'
  },
  sec: {
    1: '\\sec\\left(${args[0]}\\right)'
  },
  sech: {
    1: '\\mathrm{sech}\\left(${args[0]}\\right)'
  },
  sin: {
    1: '\\sin\\left(${args[0]}\\right)'
  },
  sinh: {
    1: '\\sinh\\left(${args[0]}\\right)'
  },
  tan: {
    1: '\\tan\\left(${args[0]}\\right)'
  },
  tanh: {
    1: '\\tanh\\left(${args[0]}\\right)'
  },
  // unit
  to: {
    2: "\\left(${args[0]}".concat(latexOperators.to, "${args[1]}\\right)")
  },
  // utils
  numeric: function numeric(node, options) {
    // Not sure if this is strictly right but should work correctly for the vast majority of use cases.
    return node.args[0].toTex();
  },
  // type
  number: {
    0: '0',
    1: '\\left(${args[0]}\\right)',
    2: '\\left(\\left(${args[0]}\\right)${args[1]}\\right)'
  },
  string: {
    0: '\\mathtt{""}',
    1: '\\mathrm{string}\\left(${args[0]}\\right)'
  },
  bignumber: {
    0: '0',
    1: '\\left(${args[0]}\\right)'
  },
  complex: {
    0: '0',
    1: '\\left(${args[0]}\\right)',
    2: "\\left(\\left(${args[0]}\\right)+".concat(latexSymbols.i, "\\cdot\\left(${args[1]}\\right)\\right)")
  },
  matrix: {
    0: '\\begin{bmatrix}\\end{bmatrix}',
    1: '\\left(${args[0]}\\right)',
    2: '\\left(${args[0]}\\right)'
  },
  sparse: {
    0: '\\begin{bsparse}\\end{bsparse}',
    1: '\\left(${args[0]}\\right)'
  },
  unit: {
    1: '\\left(${args[0]}\\right)',
    2: '\\left(\\left(${args[0]}\\right)${args[1]}\\right)'
  }
};
var defaultTemplate = '\\mathrm{${name}}\\left(${args}\\right)';
var latexUnits = {
  deg: '^\\circ'
};
function escapeLatex(string) {
  return dist_default()(string, {
    preserveFormatting: true
  });
} // @param {string} name
// @param {boolean} isUnit

function toSymbol(name, isUnit) {
  isUnit = typeof isUnit === 'undefined' ? false : isUnit;

  if (isUnit) {
    if (object_hasOwnProperty(latexUnits, name)) {
      return latexUnits[name];
    }

    return '\\mathrm{' + escapeLatex(name) + '}';
  }

  if (object_hasOwnProperty(latexSymbols, name)) {
    return latexSymbols[name];
  }

  return escapeLatex(name);
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/ConstantNode.js




var ConstantNode_name = 'ConstantNode';
var ConstantNode_dependencies = ['Node'];
var createConstantNode = /* #__PURE__ */factory_factory(ConstantNode_name, ConstantNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * A ConstantNode holds a constant value like a number or string.
   *
   * Usage:
   *
   *     new ConstantNode(2.3)
   *     new ConstantNode('hello')
   *
   * @param {*} value    Value can be any type (number, BigNumber, string, ...)
   * @constructor ConstantNode
   * @extends {Node}
   */
  function ConstantNode(value) {
    if (!(this instanceof ConstantNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.value = value;
  }

  ConstantNode.prototype = new Node();
  ConstantNode.prototype.type = 'ConstantNode';
  ConstantNode.prototype.isConstantNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  ConstantNode.prototype._compile = function (math, argNames) {
    var value = this.value;
    return function evalConstantNode() {
      return value;
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  ConstantNode.prototype.forEach = function (callback) {// nothing to do, we don't have childs
  };
  /**
   * Create a new ConstantNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node) : Node} callback
   * @returns {ConstantNode} Returns a clone of the node
   */


  ConstantNode.prototype.map = function (callback) {
    return this.clone();
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {ConstantNode}
   */


  ConstantNode.prototype.clone = function () {
    return new ConstantNode(this.value);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   */


  ConstantNode.prototype._toString = function (options) {
    return string_format(this.value, options);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  ConstantNode.prototype.toHTML = function (options) {
    var value = this._toString(options);

    switch (typeOf(this.value)) {
      case 'number':
      case 'BigNumber':
      case 'Fraction':
        return '<span class="math-number">' + value + '</span>';

      case 'string':
        return '<span class="math-string">' + value + '</span>';

      case 'boolean':
        return '<span class="math-boolean">' + value + '</span>';

      case 'null':
        return '<span class="math-null-symbol">' + value + '</span>';

      case 'undefined':
        return '<span class="math-undefined">' + value + '</span>';

      default:
        return '<span class="math-symbol">' + value + '</span>';
    }
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  ConstantNode.prototype.toJSON = function () {
    return {
      mathjs: 'ConstantNode',
      value: this.value
    };
  };
  /**
   * Instantiate a ConstantNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "SymbolNode", value: 2.3}`,
   *                       where mathjs is optional
   * @returns {ConstantNode}
   */


  ConstantNode.fromJSON = function (json) {
    return new ConstantNode(json.value);
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  ConstantNode.prototype._toTex = function (options) {
    var value = this._toString(options);

    switch (typeOf(this.value)) {
      case 'string':
        return '\\mathtt{' + escapeLatex(value) + '}';

      case 'number':
      case 'BigNumber':
        {
          if (!isFinite(this.value)) {
            return this.value.valueOf() < 0 ? '-\\infty' : '\\infty';
          }

          var index = value.toLowerCase().indexOf('e');

          if (index !== -1) {
            return value.substring(0, index) + '\\cdot10^{' + value.substring(index + 1) + '}';
          }
        }
        return value;

      case 'Fraction':
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
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesConstantNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var ConstantNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createConstantNode: createConstantNode
};
// EXTERNAL MODULE: ./node_modules/typed-function/typed-function.js
var typed_function = __webpack_require__("7634");
var typed_function_default = /*#__PURE__*/__webpack_require__.n(typed_function);

// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/core/function/typed.js
/**
 * Create a typed-function which checks the types of the arguments and
 * can match them against multiple provided signatures. The typed-function
 * automatically converts inputs in order to find a matching signature.
 * Typed functions throw informative errors in case of wrong input arguments.
 *
 * See the library [typed-function](https://github.com/josdejong/typed-function)
 * for detailed documentation.
 *
 * Syntax:
 *
 *     math.typed(name, signatures) : function
 *     math.typed(signatures) : function
 *
 * Examples:
 *
 *     // create a typed function with multiple types per argument (type union)
 *     const fn2 = typed({
 *       'number | boolean': function (b) {
 *         return 'b is a number or boolean'
 *       },
 *       'string, number | boolean': function (a, b) {
 *         return 'a is a string, b is a number or boolean'
 *       }
 *     })
 *
 *     // create a typed function with an any type argument
 *     const log = typed({
 *       'string, any': function (event, data) {
 *         console.log('event: ' + event + ', data: ' + JSON.stringify(data))
 *       }
 *     })
 *
 * @param {string} [name]                          Optional name for the typed-function
 * @param {Object<string, function>} signatures   Object with one or multiple function signatures
 * @returns {function} The created typed-function.
 */




 // returns a new instance of typed-function

var _createTyped2 = function _createTyped() {
  // initially, return the original instance of typed-function
  // consecutively, return a new instance from typed.create.
  _createTyped2 = typed_function_default.a.create;
  return typed_function_default.a;
};

var typed_dependencies = ['?BigNumber', '?Complex', '?DenseMatrix', '?Fraction'];
/**
 * Factory function for creating a new typed instance
 * @param {Object} dependencies   Object with data types like Complex and BigNumber
 * @returns {Function}
 */

var typed_createTyped = /* #__PURE__ */factory_factory('typed', typed_dependencies, function createTyped(_ref) {
  var {
    BigNumber,
    Complex,
    DenseMatrix,
    Fraction
  } = _ref;

  // TODO: typed-function must be able to silently ignore signatures with unknown data types
  // get a new instance of typed-function
  var typed = _createTyped2(); // define all types. The order of the types determines in which order function
  // arguments are type-checked (so for performance it's important to put the
  // most used types first).


  typed.types = [{
    name: 'number',
    test: isNumber
  }, {
    name: 'Complex',
    test: isComplex
  }, {
    name: 'BigNumber',
    test: isBigNumber
  }, {
    name: 'Fraction',
    test: isFraction
  }, {
    name: 'Unit',
    test: is_isUnit
  }, {
    name: 'string',
    test: isString
  }, {
    name: 'Chain',
    test: isChain
  }, {
    name: 'Array',
    test: isArray
  }, {
    name: 'Matrix',
    test: isMatrix
  }, {
    name: 'DenseMatrix',
    test: isDenseMatrix
  }, {
    name: 'SparseMatrix',
    test: isSparseMatrix
  }, {
    name: 'Range',
    test: isRange
  }, {
    name: 'Index',
    test: isIndex
  }, {
    name: 'boolean',
    test: isBoolean
  }, {
    name: 'ResultSet',
    test: isResultSet
  }, {
    name: 'Help',
    test: isHelp
  }, {
    name: 'function',
    test: isFunction
  }, {
    name: 'Date',
    test: isDate
  }, {
    name: 'RegExp',
    test: isRegExp
  }, {
    name: 'null',
    test: isNull
  }, {
    name: 'undefined',
    test: isUndefined
  }, {
    name: 'AccessorNode',
    test: isAccessorNode
  }, {
    name: 'ArrayNode',
    test: isArrayNode
  }, {
    name: 'AssignmentNode',
    test: isAssignmentNode
  }, {
    name: 'BlockNode',
    test: isBlockNode
  }, {
    name: 'ConditionalNode',
    test: isConditionalNode
  }, {
    name: 'ConstantNode',
    test: isConstantNode
  }, {
    name: 'FunctionNode',
    test: isFunctionNode
  }, {
    name: 'FunctionAssignmentNode',
    test: isFunctionAssignmentNode
  }, {
    name: 'IndexNode',
    test: isIndexNode
  }, {
    name: 'Node',
    test: isNode
  }, {
    name: 'ObjectNode',
    test: isObjectNode
  }, {
    name: 'OperatorNode',
    test: isOperatorNode
  }, {
    name: 'ParenthesisNode',
    test: isParenthesisNode
  }, {
    name: 'RangeNode',
    test: isRangeNode
  }, {
    name: 'SymbolNode',
    test: isSymbolNode
  }, {
    name: 'Map',
    test: isMap
  }, {
    name: 'Object',
    test: isObject
  } // order 'Object' last, it matches on other classes too
  ];
  typed.conversions = [{
    from: 'number',
    to: 'BigNumber',
    convert: function convert(x) {
      if (!BigNumber) {
        throwNoBignumber(x);
      } // note: conversion from number to BigNumber can fail if x has >15 digits


      if (digits(x) > 15) {
        throw new TypeError('Cannot implicitly convert a number with >15 significant digits to BigNumber ' + '(value: ' + x + '). ' + 'Use function bignumber(x) to convert to BigNumber.');
      }

      return new BigNumber(x);
    }
  }, {
    from: 'number',
    to: 'Complex',
    convert: function convert(x) {
      if (!Complex) {
        throwNoComplex(x);
      }

      return new Complex(x, 0);
    }
  }, {
    from: 'number',
    to: 'string',
    convert: function convert(x) {
      return x + '';
    }
  }, {
    from: 'BigNumber',
    to: 'Complex',
    convert: function convert(x) {
      if (!Complex) {
        throwNoComplex(x);
      }

      return new Complex(x.toNumber(), 0);
    }
  }, {
    from: 'Fraction',
    to: 'BigNumber',
    convert: function convert(x) {
      throw new TypeError('Cannot implicitly convert a Fraction to BigNumber or vice versa. ' + 'Use function bignumber(x) to convert to BigNumber or fraction(x) to convert to Fraction.');
    }
  }, {
    from: 'Fraction',
    to: 'Complex',
    convert: function convert(x) {
      if (!Complex) {
        throwNoComplex(x);
      }

      return new Complex(x.valueOf(), 0);
    }
  }, {
    from: 'number',
    to: 'Fraction',
    convert: function convert(x) {
      if (!Fraction) {
        throwNoFraction(x);
      }

      var f = new Fraction(x);

      if (f.valueOf() !== x) {
        throw new TypeError('Cannot implicitly convert a number to a Fraction when there will be a loss of precision ' + '(value: ' + x + '). ' + 'Use function fraction(x) to convert to Fraction.');
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
    from: 'string',
    to: 'number',
    convert: function convert(x) {
      var n = Number(x);

      if (isNaN(n)) {
        throw new Error('Cannot convert "' + x + '" to a number');
      }

      return n;
    }
  }, {
    from: 'string',
    to: 'BigNumber',
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
    from: 'string',
    to: 'Fraction',
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
    from: 'string',
    to: 'Complex',
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
    from: 'boolean',
    to: 'number',
    convert: function convert(x) {
      return +x;
    }
  }, {
    from: 'boolean',
    to: 'BigNumber',
    convert: function convert(x) {
      if (!BigNumber) {
        throwNoBignumber(x);
      }

      return new BigNumber(+x);
    }
  }, {
    from: 'boolean',
    to: 'Fraction',
    convert: function convert(x) {
      if (!Fraction) {
        throwNoFraction(x);
      }

      return new Fraction(+x);
    }
  }, {
    from: 'boolean',
    to: 'string',
    convert: function convert(x) {
      return String(x);
    }
  }, {
    from: 'Array',
    to: 'Matrix',
    convert: function convert(array) {
      if (!DenseMatrix) {
        throwNoMatrix();
      }

      return new DenseMatrix(array);
    }
  }, {
    from: 'Matrix',
    to: 'Array',
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
  throw new Error('Cannot convert array into a Matrix: no class \'DenseMatrix\' provided');
}

function throwNoFraction(x) {
  throw new Error("Cannot convert value ".concat(x, " into a Fraction, no class 'Fraction' provided."));
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesTyped.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */

var typedDependencies = {
  createTyped: typed_createTyped
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/FunctionAssignmentNode.js







var FunctionAssignmentNode_name = 'FunctionAssignmentNode';
var FunctionAssignmentNode_dependencies = ['typed', 'Node'];
var createFunctionAssignmentNode = /* #__PURE__ */factory_factory(FunctionAssignmentNode_name, FunctionAssignmentNode_dependencies, _ref => {
  var {
    typed,
    Node
  } = _ref;

  /**
   * @constructor FunctionAssignmentNode
   * @extends {Node}
   * Function assignment
   *
   * @param {string} name           Function name
   * @param {string[] | Array.<{name: string, type: string}>} params
   *                                Array with function parameter names, or an
   *                                array with objects containing the name
   *                                and type of the parameter
   * @param {Node} expr             The function expression
   */
  function FunctionAssignmentNode(name, params, expr) {
    if (!(this instanceof FunctionAssignmentNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate input


    if (typeof name !== 'string') throw new TypeError('String expected for parameter "name"');
    if (!Array.isArray(params)) throw new TypeError('Array containing strings or objects expected for parameter "params"');
    if (!isNode(expr)) throw new TypeError('Node expected for parameter "expr"');
    if (keywords.has(name)) throw new Error('Illegal function name, "' + name + '" is a reserved keyword');
    this.name = name;
    this.params = params.map(function (param) {
      return param && param.name || param;
    });
    this.types = params.map(function (param) {
      return param && param.type || 'any';
    });
    this.expr = expr;
  }

  FunctionAssignmentNode.prototype = new Node();
  FunctionAssignmentNode.prototype.type = 'FunctionAssignmentNode';
  FunctionAssignmentNode.prototype.isFunctionAssignmentNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  FunctionAssignmentNode.prototype._compile = function (math, argNames) {
    var childArgNames = Object.create(argNames);
    forEach(this.params, function (param) {
      childArgNames[param] = true;
    }); // compile the function expression with the child args

    var evalExpr = this.expr._compile(math, childArgNames);

    var name = this.name;
    var params = this.params;
    var signature = join(this.types, ',');
    var syntax = name + '(' + join(this.params, ', ') + ')';
    return function evalFunctionAssignmentNode(scope, args, context) {
      var signatures = {};

      signatures[signature] = function () {
        var childArgs = Object.create(args);

        for (var i = 0; i < params.length; i++) {
          childArgs[params[i]] = arguments[i];
        }

        return evalExpr(scope, childArgs, context);
      };

      var fn = typed(name, signatures);
      fn.syntax = syntax;
      scope.set(name, fn);
      return fn;
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  FunctionAssignmentNode.prototype.forEach = function (callback) {
    callback(this.expr, 'expr', this);
  };
  /**
   * Create a new FunctionAssignmentNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {FunctionAssignmentNode} Returns a transformed copy of the node
   */


  FunctionAssignmentNode.prototype.map = function (callback) {
    var expr = this._ifNode(callback(this.expr, 'expr', this));

    return new FunctionAssignmentNode(this.name, this.params.slice(0), expr);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {FunctionAssignmentNode}
   */


  FunctionAssignmentNode.prototype.clone = function () {
    return new FunctionAssignmentNode(this.name, this.params.slice(0), this.expr);
  };
  /**
   * Is parenthesis needed?
   * @param {Node} node
   * @param {Object} parenthesis
   * @private
   */


  function needParenthesis(node, parenthesis) {
    var precedence = getPrecedence(node, parenthesis);
    var exprPrecedence = getPrecedence(node.expr, parenthesis);
    return parenthesis === 'all' || exprPrecedence !== null && exprPrecedence <= precedence;
  }
  /**
   * get string representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionAssignmentNode.prototype._toString = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var expr = this.expr.toString(options);

    if (needParenthesis(this, parenthesis)) {
      expr = '(' + expr + ')';
    }

    return this.name + '(' + this.params.join(', ') + ') = ' + expr;
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  FunctionAssignmentNode.prototype.toJSON = function () {
    var types = this.types;
    return {
      mathjs: 'FunctionAssignmentNode',
      name: this.name,
      params: this.params.map(function (param, index) {
        return {
          name: param,
          type: types[index]
        };
      }),
      expr: this.expr
    };
  };
  /**
   * Instantiate an FunctionAssignmentNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "FunctionAssignmentNode", name: ..., params: ..., expr: ...}`,
   *                       where mathjs is optional
   * @returns {FunctionAssignmentNode}
   */


  FunctionAssignmentNode.fromJSON = function (json) {
    return new FunctionAssignmentNode(json.name, json.params, json.expr);
  };
  /**
   * get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionAssignmentNode.prototype.toHTML = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var params = [];

    for (var i = 0; i < this.params.length; i++) {
      params.push('<span class="math-symbol math-parameter">' + string_escape(this.params[i]) + '</span>');
    }

    var expr = this.expr.toHTML(options);

    if (needParenthesis(this, parenthesis)) {
      expr = '<span class="math-parenthesis math-round-parenthesis">(</span>' + expr + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    return '<span class="math-function">' + string_escape(this.name) + '</span>' + '<span class="math-parenthesis math-round-parenthesis">(</span>' + params.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-round-parenthesis">)</span><span class="math-operator math-assignment-operator math-variable-assignment-operator math-binary-operator">=</span>' + expr;
  };
  /**
   * get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionAssignmentNode.prototype._toTex = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var expr = this.expr.toTex(options);

    if (needParenthesis(this, parenthesis)) {
      expr = "\\left(".concat(expr, "\\right)");
    }

    return '\\mathrm{' + this.name + '}\\left(' + this.params.map(toSymbol).join(',') + '\\right):=' + expr;
  };

  return FunctionAssignmentNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesFunctionAssignmentNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var FunctionAssignmentNodeDependencies = {
  NodeDependencies: NodeDependencies,
  typedDependencies: typedDependencies,
  createFunctionAssignmentNode: createFunctionAssignmentNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/SymbolNode.js




var SymbolNode_name = 'SymbolNode';
var SymbolNode_dependencies = ['math', '?Unit', 'Node'];
var createSymbolNode = /* #__PURE__ */factory_factory(SymbolNode_name, SymbolNode_dependencies, _ref => {
  var {
    math,
    Unit,
    Node
  } = _ref;

  /**
   * Check whether some name is a valueless unit like "inch".
   * @param {string} name
   * @return {boolean}
   */
  function isValuelessUnit(name) {
    return Unit ? Unit.isValuelessUnit(name) : false;
  }
  /**
   * @constructor SymbolNode
   * @extends {Node}
   * A symbol node can hold and resolve a symbol
   * @param {string} name
   * @extends {Node}
   */


  function SymbolNode(name) {
    if (!(this instanceof SymbolNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate input


    if (typeof name !== 'string') throw new TypeError('String expected for parameter "name"');
    this.name = name;
  }

  SymbolNode.prototype = new Node();
  SymbolNode.prototype.type = 'SymbolNode';
  SymbolNode.prototype.isSymbolNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  SymbolNode.prototype._compile = function (math, argNames) {
    var name = this.name;

    if (argNames[name] === true) {
      // this is a FunctionAssignment argument
      // (like an x when inside the expression of a function assignment `f(x) = ...`)
      return function (scope, args, context) {
        return args[name];
      };
    } else if (name in math) {
      return function (scope, args, context) {
        return scope.has(name) ? scope.get(name) : getSafeProperty(math, name);
      };
    } else {
      var isUnit = isValuelessUnit(name);
      return function (scope, args, context) {
        return scope.has(name) ? scope.get(name) : isUnit ? new Unit(null, name) : SymbolNode.onUndefinedSymbol(name);
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  SymbolNode.prototype.forEach = function (callback) {// nothing to do, we don't have childs
  };
  /**
   * Create a new SymbolNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node) : Node} callback
   * @returns {SymbolNode} Returns a clone of the node
   */


  SymbolNode.prototype.map = function (callback) {
    return this.clone();
  };
  /**
   * Throws an error 'Undefined symbol {name}'
   * @param {string} name
   */


  SymbolNode.onUndefinedSymbol = function (name) {
    throw new Error('Undefined symbol ' + name);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {SymbolNode}
   */


  SymbolNode.prototype.clone = function () {
    return new SymbolNode(this.name);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  SymbolNode.prototype._toString = function (options) {
    return this.name;
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  SymbolNode.prototype.toHTML = function (options) {
    var name = string_escape(this.name);

    if (name === 'true' || name === 'false') {
      return '<span class="math-symbol math-boolean">' + name + '</span>';
    } else if (name === 'i') {
      return '<span class="math-symbol math-imaginary-symbol">' + name + '</span>';
    } else if (name === 'Infinity') {
      return '<span class="math-symbol math-infinity-symbol">' + name + '</span>';
    } else if (name === 'NaN') {
      return '<span class="math-symbol math-nan-symbol">' + name + '</span>';
    } else if (name === 'null') {
      return '<span class="math-symbol math-null-symbol">' + name + '</span>';
    } else if (name === 'undefined') {
      return '<span class="math-symbol math-undefined-symbol">' + name + '</span>';
    }

    return '<span class="math-symbol">' + name + '</span>';
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  SymbolNode.prototype.toJSON = function () {
    return {
      mathjs: 'SymbolNode',
      name: this.name
    };
  };
  /**
   * Instantiate a SymbolNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "SymbolNode", name: "x"}`,
   *                       where mathjs is optional
   * @returns {SymbolNode}
   */


  SymbolNode.fromJSON = function (json) {
    return new SymbolNode(json.name);
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  SymbolNode.prototype._toTex = function (options) {
    var isUnit = false;

    if (typeof math[this.name] === 'undefined' && isValuelessUnit(this.name)) {
      isUnit = true;
    }

    var symbol = toSymbol(this.name, isUnit);

    if (symbol[0] === '\\') {
      // no space needed if the symbol starts with '\'
      return symbol;
    } // the space prevents symbols from breaking stuff like '\cdot' if it's written right before the symbol


    return ' ' + symbol;
  };

  return SymbolNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesSymbolNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var SymbolNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createSymbolNode: createSymbolNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/scope.js

/**
 * Create a new scope which can access the parent scope,
 * but does not affect it when written. This is suitable for variable definitions
 * within a block node, or function definition.
 *
 * If parent scope has a createSubScope method, it delegates to that. Otherwise,
 * creates an empty map, and copies the parent scope to it, adding in
 * the remaining `args`.
 *
 * @param {Map} parentScope
 * @param  {...any} args
 * @returns {Map}
 */

function createSubScope(parentScope) {
  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }

  if (typeof parentScope.createSubScope === 'function') {
    return map_assign(parentScope.createSubScope(), ...args);
  }

  return map_assign(createEmptyMap(), parentScope, ...args);
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/FunctionNode.js







var FunctionNode_name = 'FunctionNode';
var FunctionNode_dependencies = ['math', 'Node', 'SymbolNode'];
var createFunctionNode = /* #__PURE__ */factory_factory(FunctionNode_name, FunctionNode_dependencies, _ref => {
  var {
    math,
    Node,
    SymbolNode
  } = _ref;

  /**
   * @constructor FunctionNode
   * @extends {./Node}
   * invoke a list with arguments on a node
   * @param {./Node | string} fn Node resolving with a function on which to invoke
   *                             the arguments, typically a SymboNode or AccessorNode
   * @param {./Node[]} args
   */
  function FunctionNode(fn, args) {
    if (!(this instanceof FunctionNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    if (typeof fn === 'string') {
      fn = new SymbolNode(fn);
    } // validate input


    if (!isNode(fn)) throw new TypeError('Node expected as parameter "fn"');

    if (!Array.isArray(args) || !args.every(isNode)) {
      throw new TypeError('Array containing Nodes expected for parameter "args"');
    }

    this.fn = fn;
    this.args = args || []; // readonly property name

    Object.defineProperty(this, 'name', {
      get: function () {
        return this.fn.name || '';
      }.bind(this),
      set: function set() {
        throw new Error('Cannot assign a new name, name is read-only');
      }
    });
  }

  FunctionNode.prototype = new Node();
  FunctionNode.prototype.type = 'FunctionNode';
  FunctionNode.prototype.isFunctionNode = true;
  /* format to fixed length */

  var strin = entity => string_format(entity, {
    truncate: 78
  });
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */


  FunctionNode.prototype._compile = function (math, argNames) {
    if (!(this instanceof FunctionNode)) {
      throw new TypeError('No valid FunctionNode');
    } // compile arguments


    var evalArgs = this.args.map(arg => arg._compile(math, argNames));

    if (isSymbolNode(this.fn)) {
      var _name = this.fn.name;

      if (!argNames[_name]) {
        // we can statically determine whether the function has an rawArgs property
        var fn = _name in math ? getSafeProperty(math, _name) : undefined;
        var isRaw = typeof fn === 'function' && fn.rawArgs === true;

        var resolveFn = scope => {
          var value;

          if (scope.has(_name)) {
            value = scope.get(_name);
          } else if (_name in math) {
            value = getSafeProperty(math, _name);
          } else {
            return FunctionNode.onUndefinedFunction(_name);
          }

          if (typeof value === 'function') {
            return value;
          }

          throw new TypeError("'".concat(_name, "' is not a function; its value is:\n  ").concat(strin(value)));
        };

        if (isRaw) {
          // pass unevaluated parameters (nodes) to the function
          // "raw" evaluation
          var rawArgs = this.args;
          return function evalFunctionNode(scope, args, context) {
            var fn = resolveFn(scope);
            return fn(rawArgs, math, createSubScope(scope, args), scope);
          };
        } else {
          // "regular" evaluation
          switch (evalArgs.length) {
            case 0:
              return function evalFunctionNode(scope, args, context) {
                var fn = resolveFn(scope);
                return fn();
              };

            case 1:
              return function evalFunctionNode(scope, args, context) {
                var fn = resolveFn(scope);
                var evalArg0 = evalArgs[0];
                return fn(evalArg0(scope, args, context));
              };

            case 2:
              return function evalFunctionNode(scope, args, context) {
                var fn = resolveFn(scope);
                var evalArg0 = evalArgs[0];
                var evalArg1 = evalArgs[1];
                return fn(evalArg0(scope, args, context), evalArg1(scope, args, context));
              };

            default:
              return function evalFunctionNode(scope, args, context) {
                var fn = resolveFn(scope);
                var values = evalArgs.map(evalArg => evalArg(scope, args, context));
                return fn(...values);
              };
          }
        }
      } else {
        // the function symbol is an argName
        var _rawArgs = this.args;
        return function evalFunctionNode(scope, args, context) {
          var fn = args[_name];

          if (typeof fn !== 'function') {
            throw new TypeError("Argument '".concat(_name, "' was not a function; received: ").concat(strin(fn)));
          }

          if (fn.rawArgs) {
            return fn(_rawArgs, math, createSubScope(scope, args), scope); // "raw" evaluation
          } else {
            var values = evalArgs.map(evalArg => evalArg(scope, args, context));
            return fn.apply(fn, values);
          }
        };
      }
    } else if (isAccessorNode(this.fn) && isIndexNode(this.fn.index) && this.fn.index.isObjectProperty()) {
      // execute the function with the right context: the object of the AccessorNode
      var evalObject = this.fn.object._compile(math, argNames);

      var prop = this.fn.index.getObjectProperty();
      var _rawArgs2 = this.args;
      return function evalFunctionNode(scope, args, context) {
        var object = evalObject(scope, args, context);
        validateSafeMethod(object, prop);
        var isRaw = object[prop] && object[prop].rawArgs;

        if (isRaw) {
          return object[prop](_rawArgs2, math, createSubScope(scope, args), scope); // "raw" evaluation
        } else {
          // "regular" evaluation
          var values = evalArgs.map(evalArg => evalArg(scope, args, context));
          return object[prop].apply(object, values);
        }
      };
    } else {
      // node.fn.isAccessorNode && !node.fn.index.isObjectProperty()
      // we have to dynamically determine whether the function has a rawArgs property
      var fnExpr = this.fn.toString();

      var evalFn = this.fn._compile(math, argNames);

      var _rawArgs3 = this.args;
      return function evalFunctionNode(scope, args, context) {
        var fn = evalFn(scope, args, context);

        if (typeof fn !== 'function') {
          throw new TypeError("Expression '".concat(fnExpr, "' did not evaluate to a function; value is:") + "\n  ".concat(strin(fn)));
        }

        if (fn.rawArgs) {
          return fn(_rawArgs3, math, createSubScope(scope, args), scope); // "raw" evaluation
        } else {
          // "regular" evaluation
          var values = evalArgs.map(evalArg => evalArg(scope, args, context));
          return fn.apply(fn, values);
        }
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  FunctionNode.prototype.forEach = function (callback) {
    callback(this.fn, 'fn', this);

    for (var i = 0; i < this.args.length; i++) {
      callback(this.args[i], 'args[' + i + ']', this);
    }
  };
  /**
   * Create a new FunctionNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {FunctionNode} Returns a transformed copy of the node
   */


  FunctionNode.prototype.map = function (callback) {
    var fn = this._ifNode(callback(this.fn, 'fn', this));

    var args = [];

    for (var i = 0; i < this.args.length; i++) {
      args[i] = this._ifNode(callback(this.args[i], 'args[' + i + ']', this));
    }

    return new FunctionNode(fn, args);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {FunctionNode}
   */


  FunctionNode.prototype.clone = function () {
    return new FunctionNode(this.fn, this.args.slice(0));
  };
  /**
   * Throws an error 'Undefined function {name}'
   * @param {string} name
   */


  FunctionNode.onUndefinedFunction = function (name) {
    throw new Error('Undefined function ' + name);
  }; // backup Node's toString function
  // @private


  var nodeToString = FunctionNode.prototype.toString;
  /**
   * Get string representation. (wrapper function)
   * This overrides parts of Node's toString function.
   * If callback is an object containing callbacks, it
   * calls the correct callback for the current node,
   * otherwise it falls back to calling Node's toString
   * function.
   *
   * @param {Object} options
   * @return {string} str
   * @override
   */

  FunctionNode.prototype.toString = function (options) {
    var customString;
    var name = this.fn.toString(options);

    if (options && typeof options.handler === 'object' && object_hasOwnProperty(options.handler, name)) {
      // callback is a map of callback functions
      customString = options.handler[name](this, options);
    }

    if (typeof customString !== 'undefined') {
      return customString;
    } // fall back to Node's toString


    return nodeToString.call(this, options);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionNode.prototype._toString = function (options) {
    var args = this.args.map(function (arg) {
      return arg.toString(options);
    });
    var fn = isFunctionAssignmentNode(this.fn) ? '(' + this.fn.toString(options) + ')' : this.fn.toString(options); // format the arguments like "add(2, 4.2)"

    return fn + '(' + args.join(', ') + ')';
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  FunctionNode.prototype.toJSON = function () {
    return {
      mathjs: 'FunctionNode',
      fn: this.fn,
      args: this.args
    };
  };
  /**
   * Instantiate an AssignmentNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "FunctionNode", fn: ..., args: ...}`,
   *                       where mathjs is optional
   * @returns {FunctionNode}
   */


  FunctionNode.fromJSON = function (json) {
    return new FunctionNode(json.fn, json.args);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionNode.prototype.toHTML = function (options) {
    var args = this.args.map(function (arg) {
      return arg.toHTML(options);
    }); // format the arguments like "add(2, 4.2)"

    return '<span class="math-function">' + string_escape(this.fn) + '</span><span class="math-paranthesis math-round-parenthesis">(</span>' + args.join('<span class="math-separator">,</span>') + '<span class="math-paranthesis math-round-parenthesis">)</span>';
  };
  /*
   * Expand a LaTeX template
   *
   * @param {string} template
   * @param {Node} node
   * @param {Object} options
   * @private
   **/


  function expandTemplate(template, node, options) {
    var latex = ''; // Match everything of the form ${identifier} or ${identifier[2]} or $$
    // while submatching identifier and 2 (in the second case)

    var regex = /\$(?:\{([a-z_][a-z_0-9]*)(?:\[([0-9]+)\])?\}|\$)/gi;
    var inputPos = 0; // position in the input string

    var match;

    while ((match = regex.exec(template)) !== null) {
      // go through all matches
      // add everything in front of the match to the LaTeX string
      latex += template.substring(inputPos, match.index);
      inputPos = match.index;

      if (match[0] === '$$') {
        // escaped dollar sign
        latex += '$';
        inputPos++;
      } else {
        // template parameter
        inputPos += match[0].length;
        var property = node[match[1]];

        if (!property) {
          throw new ReferenceError('Template: Property ' + match[1] + ' does not exist.');
        }

        if (match[2] === undefined) {
          // no square brackets
          switch (typeof property) {
            case 'string':
              latex += property;
              break;

            case 'object':
              if (isNode(property)) {
                latex += property.toTex(options);
              } else if (Array.isArray(property)) {
                // make array of Nodes into comma separated list
                latex += property.map(function (arg, index) {
                  if (isNode(arg)) {
                    return arg.toTex(options);
                  }

                  throw new TypeError('Template: ' + match[1] + '[' + index + '] is not a Node.');
                }).join(',');
              } else {
                throw new TypeError('Template: ' + match[1] + ' has to be a Node, String or array of Nodes');
              }

              break;

            default:
              throw new TypeError('Template: ' + match[1] + ' has to be a Node, String or array of Nodes');
          }
        } else {
          // with square brackets
          if (isNode(property[match[2]] && property[match[2]])) {
            latex += property[match[2]].toTex(options);
          } else {
            throw new TypeError('Template: ' + match[1] + '[' + match[2] + '] is not a Node.');
          }
        }
      }
    }

    latex += template.slice(inputPos); // append rest of the template

    return latex;
  } // backup Node's toTex function
  // @private


  var nodeToTex = FunctionNode.prototype.toTex;
  /**
   * Get LaTeX representation. (wrapper function)
   * This overrides parts of Node's toTex function.
   * If callback is an object containing callbacks, it
   * calls the correct callback for the current node,
   * otherwise it falls back to calling Node's toTex
   * function.
   *
   * @param {Object} options
   * @return {string}
   */

  FunctionNode.prototype.toTex = function (options) {
    var customTex;

    if (options && typeof options.handler === 'object' && object_hasOwnProperty(options.handler, this.name)) {
      // callback is a map of callback functions
      customTex = options.handler[this.name](this, options);
    }

    if (typeof customTex !== 'undefined') {
      return customTex;
    } // fall back to Node's toTex


    return nodeToTex.call(this, options);
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  FunctionNode.prototype._toTex = function (options) {
    var args = this.args.map(function (arg) {
      // get LaTeX of the arguments
      return arg.toTex(options);
    });
    var latexConverter;

    if (latexFunctions[this.name]) {
      latexConverter = latexFunctions[this.name];
    } // toTex property on the function itself


    if (math[this.name] && (typeof math[this.name].toTex === 'function' || typeof math[this.name].toTex === 'object' || typeof math[this.name].toTex === 'string')) {
      // .toTex is a callback function
      latexConverter = math[this.name].toTex;
    }

    var customToTex;

    switch (typeof latexConverter) {
      case 'function':
        // a callback function
        customToTex = latexConverter(this, options);
        break;

      case 'string':
        // a template string
        customToTex = expandTemplate(latexConverter, this, options);
        break;

      case 'object':
        // an object with different "converters" for different numbers of arguments
        switch (typeof latexConverter[args.length]) {
          case 'function':
            customToTex = latexConverter[args.length](this, options);
            break;

          case 'string':
            customToTex = expandTemplate(latexConverter[args.length], this, options);
            break;
        }

    }

    if (typeof customToTex !== 'undefined') {
      return customToTex;
    }

    return expandTemplate(defaultTemplate, this, options);
  };
  /**
   * Get identifier.
   * @return {string}
   */


  FunctionNode.prototype.getIdentifier = function () {
    return this.type + ':' + this.name;
  };

  return FunctionNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesFunctionNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var FunctionNodeDependencies = {
  NodeDependencies: NodeDependencies,
  SymbolNodeDependencies: SymbolNodeDependencies,
  createFunctionNode: createFunctionNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/function/matrix/size.js



var size_name = 'size';
var size_dependencies = ['typed', 'config', '?matrix'];
var createSize = /* #__PURE__ */factory_factory(size_name, size_dependencies, _ref => {
  var {
    typed,
    config,
    matrix
  } = _ref;

  /**
   * Calculate the size of a matrix or scalar.
   *
   * Syntax:
   *
   *     math.size(x)
   *
   * Examples:
   *
   *     math.size(2.3)                  // returns []
   *     math.size('hello world')        // returns [11]
   *
   *     const A = [[1, 2, 3], [4, 5, 6]]
   *     math.size(A)                    // returns [2, 3]
   *     math.size(math.range(1,6))      // returns [5]
   *
   * See also:
   *
   *     count, resize, squeeze, subset
   *
   * @param {boolean | number | Complex | Unit | string | Array | Matrix} x  A matrix
   * @return {Array | Matrix} A vector with size of `x`.
   */
  return typed(size_name, {
    Matrix: function Matrix(x) {
      return x.create(x.size());
    },
    Array: arraySize,
    string: function string(x) {
      return config.matrix === 'Array' ? [x.length] : matrix([x.length]);
    },
    'number | Complex | BigNumber | Unit | boolean | null': function numberComplexBigNumberUnitBooleanNull(x) {
      // scalar
      return config.matrix === 'Array' ? [] : matrix ? matrix([]) : noMatrix();
    }
  });
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesSize.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var sizeDependencies = {
  matrixDependencies: matrixDependencies,
  typedDependencies: typedDependencies,
  createSize: createSize
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/IndexNode.js





var IndexNode_name = 'IndexNode';
var IndexNode_dependencies = ['Node', 'size'];
var createIndexNode = /* #__PURE__ */factory_factory(IndexNode_name, IndexNode_dependencies, _ref => {
  var {
    Node,
    size
  } = _ref;

  /**
   * @constructor IndexNode
   * @extends Node
   *
   * Describes a subset of a matrix or an object property.
   * Cannot be used on its own, needs to be used within an AccessorNode or
   * AssignmentNode.
   *
   * @param {Node[]} dimensions
   * @param {boolean} [dotNotation=false]  Optional property describing whether
   *                                       this index was written using dot
   *                                       notation like `a.b`, or using bracket
   *                                       notation like `a["b"]` (default).
   *                                       Used to stringify an IndexNode.
   */
  function IndexNode(dimensions, dotNotation) {
    if (!(this instanceof IndexNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.dimensions = dimensions;
    this.dotNotation = dotNotation || false; // validate input

    if (!Array.isArray(dimensions) || !dimensions.every(isNode)) {
      throw new TypeError('Array containing Nodes expected for parameter "dimensions"');
    }

    if (this.dotNotation && !this.isObjectProperty()) {
      throw new Error('dotNotation only applicable for object properties');
    }
  }

  IndexNode.prototype = new Node();
  IndexNode.prototype.type = 'IndexNode';
  IndexNode.prototype.isIndexNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  IndexNode.prototype._compile = function (math, argNames) {
    // TODO: implement support for bignumber (currently bignumbers are silently
    //       reduced to numbers when changing the value to zero-based)
    // TODO: Optimization: when the range values are ConstantNodes,
    //       we can beforehand resolve the zero-based value
    // optimization for a simple object property
    var evalDimensions = array_map(this.dimensions, function (dimension, i) {
      var needsEnd = dimension.filter(node => node.isSymbolNode && node.name === 'end').length > 0;

      if (needsEnd) {
        // SymbolNode 'end' is used inside the index,
        // like in `A[end]` or `A[end - 2]`
        var childArgNames = Object.create(argNames);
        childArgNames.end = true;

        var _evalDimension = dimension._compile(math, childArgNames);

        return function evalDimension(scope, args, context) {
          if (!isMatrix(context) && !isArray(context) && !isString(context)) {
            throw new TypeError('Cannot resolve "end": ' + 'context must be a Matrix, Array, or string but is ' + typeOf(context));
          }

          var s = size(context).valueOf();
          var childArgs = Object.create(args);
          childArgs.end = s[i];
          return _evalDimension(scope, childArgs, context);
        };
      } else {
        // SymbolNode `end` not used
        return dimension._compile(math, argNames);
      }
    });
    var index = getSafeProperty(math, 'index');
    return function evalIndexNode(scope, args, context) {
      var dimensions = array_map(evalDimensions, function (evalDimension) {
        return evalDimension(scope, args, context);
      });
      return index(...dimensions);
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  IndexNode.prototype.forEach = function (callback) {
    for (var i = 0; i < this.dimensions.length; i++) {
      callback(this.dimensions[i], 'dimensions[' + i + ']', this);
    }
  };
  /**
   * Create a new IndexNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {IndexNode} Returns a transformed copy of the node
   */


  IndexNode.prototype.map = function (callback) {
    var dimensions = [];

    for (var i = 0; i < this.dimensions.length; i++) {
      dimensions[i] = this._ifNode(callback(this.dimensions[i], 'dimensions[' + i + ']', this));
    }

    return new IndexNode(dimensions, this.dotNotation);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {IndexNode}
   */


  IndexNode.prototype.clone = function () {
    return new IndexNode(this.dimensions.slice(0), this.dotNotation);
  };
  /**
   * Test whether this IndexNode contains a single property name
   * @return {boolean}
   */


  IndexNode.prototype.isObjectProperty = function () {
    return this.dimensions.length === 1 && isConstantNode(this.dimensions[0]) && typeof this.dimensions[0].value === 'string';
  };
  /**
   * Returns the property name if IndexNode contains a property.
   * If not, returns null.
   * @return {string | null}
   */


  IndexNode.prototype.getObjectProperty = function () {
    return this.isObjectProperty() ? this.dimensions[0].value : null;
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   */


  IndexNode.prototype._toString = function (options) {
    // format the parameters like "[1, 0:5]"
    return this.dotNotation ? '.' + this.getObjectProperty() : '[' + this.dimensions.join(', ') + ']';
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  IndexNode.prototype.toJSON = function () {
    return {
      mathjs: 'IndexNode',
      dimensions: this.dimensions,
      dotNotation: this.dotNotation
    };
  };
  /**
   * Instantiate an IndexNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "IndexNode", dimensions: [...], dotNotation: false}`,
   *                       where mathjs is optional
   * @returns {IndexNode}
   */


  IndexNode.fromJSON = function (json) {
    return new IndexNode(json.dimensions, json.dotNotation);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  IndexNode.prototype.toHTML = function (options) {
    // format the parameters like "[1, 0:5]"
    var dimensions = [];

    for (var i = 0; i < this.dimensions.length; i++) {
      dimensions[i] = this.dimensions[i].toHTML();
    }

    if (this.dotNotation) {
      return '<span class="math-operator math-accessor-operator">.</span>' + '<span class="math-symbol math-property">' + string_escape(this.getObjectProperty()) + '</span>';
    } else {
      return '<span class="math-parenthesis math-square-parenthesis">[</span>' + dimensions.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-square-parenthesis">]</span>';
    }
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  IndexNode.prototype._toTex = function (options) {
    var dimensions = this.dimensions.map(function (range) {
      return range.toTex(options);
    });
    return this.dotNotation ? '.' + this.getObjectProperty() + '' : '_{' + dimensions.join(',') + '}';
  };

  return IndexNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesIndexNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var IndexNodeDependencies = {
  NodeDependencies: NodeDependencies,
  sizeDependencies: sizeDependencies,
  createIndexNode: createIndexNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/ObjectNode.js





var ObjectNode_name = 'ObjectNode';
var ObjectNode_dependencies = ['Node'];
var createObjectNode = /* #__PURE__ */factory_factory(ObjectNode_name, ObjectNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * @constructor ObjectNode
   * @extends {Node}
   * Holds an object with keys/values
   * @param {Object.<string, Node>} [properties]   object with key/value pairs
   */
  function ObjectNode(properties) {
    if (!(this instanceof ObjectNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    this.properties = properties || {}; // validate input

    if (properties) {
      if (!(typeof properties === 'object') || !Object.keys(properties).every(function (key) {
        return isNode(properties[key]);
      })) {
        throw new TypeError('Object containing Nodes expected');
      }
    }
  }

  ObjectNode.prototype = new Node();
  ObjectNode.prototype.type = 'ObjectNode';
  ObjectNode.prototype.isObjectNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  ObjectNode.prototype._compile = function (math, argNames) {
    var evalEntries = {};

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        // we stringify/parse the key here to resolve unicode characters,
        // so you cannot create a key like {"co\\u006Estructor": null}
        var stringifiedKey = stringify(key);
        var parsedKey = JSON.parse(stringifiedKey);

        if (!isSafeProperty(this.properties, parsedKey)) {
          throw new Error('No access to property "' + parsedKey + '"');
        }

        evalEntries[parsedKey] = this.properties[key]._compile(math, argNames);
      }
    }

    return function evalObjectNode(scope, args, context) {
      var obj = {};

      for (var _key in evalEntries) {
        if (object_hasOwnProperty(evalEntries, _key)) {
          obj[_key] = evalEntries[_key](scope, args, context);
        }
      }

      return obj;
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  ObjectNode.prototype.forEach = function (callback) {
    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        callback(this.properties[key], 'properties[' + stringify(key) + ']', this);
      }
    }
  };
  /**
   * Create a new ObjectNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {ObjectNode} Returns a transformed copy of the node
   */


  ObjectNode.prototype.map = function (callback) {
    var properties = {};

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        properties[key] = this._ifNode(callback(this.properties[key], 'properties[' + stringify(key) + ']', this));
      }
    }

    return new ObjectNode(properties);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {ObjectNode}
   */


  ObjectNode.prototype.clone = function () {
    var properties = {};

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        properties[key] = this.properties[key];
      }
    }

    return new ObjectNode(properties);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ObjectNode.prototype._toString = function (options) {
    var entries = [];

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        entries.push(stringify(key) + ': ' + this.properties[key].toString(options));
      }
    }

    return '{' + entries.join(', ') + '}';
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  ObjectNode.prototype.toJSON = function () {
    return {
      mathjs: 'ObjectNode',
      properties: this.properties
    };
  };
  /**
   * Instantiate an OperatorNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "ObjectNode", "properties": {...}}`,
   *                       where mathjs is optional
   * @returns {ObjectNode}
   */


  ObjectNode.fromJSON = function (json) {
    return new ObjectNode(json.properties);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ObjectNode.prototype.toHTML = function (options) {
    var entries = [];

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        entries.push('<span class="math-symbol math-property">' + string_escape(key) + '</span>' + '<span class="math-operator math-assignment-operator math-property-assignment-operator math-binary-operator">:</span>' + this.properties[key].toHTML(options));
      }
    }

    return '<span class="math-parenthesis math-curly-parenthesis">{</span>' + entries.join('<span class="math-separator">,</span>') + '<span class="math-parenthesis math-curly-parenthesis">}</span>';
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  ObjectNode.prototype._toTex = function (options) {
    var entries = [];

    for (var key in this.properties) {
      if (object_hasOwnProperty(this.properties, key)) {
        entries.push('\\mathbf{' + key + ':} & ' + this.properties[key].toTex(options) + '\\\\');
      }
    }

    return "\\left\\{\\begin{array}{ll}".concat(entries.join('\n'), "\\end{array}\\right\\}");
  };

  return ObjectNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesObjectNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var ObjectNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createObjectNode: createObjectNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/OperatorNode.js







var OperatorNode_name = 'OperatorNode';
var OperatorNode_dependencies = ['Node'];
var createOperatorNode = /* #__PURE__ */factory_factory(OperatorNode_name, OperatorNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * @constructor OperatorNode
   * @extends {Node}
   * An operator with two arguments, like 2+3
   *
   * @param {string} op           Operator name, for example '+'
   * @param {string} fn           Function name, for example 'add'
   * @param {Node[]} args         Operator arguments
   * @param {boolean} [implicit]  Is this an implicit multiplication?
   * @param {boolean} [isPercentage] Is this an percentage Operation?
   */
  function OperatorNode(op, fn, args, implicit, isPercentage) {
    if (!(this instanceof OperatorNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate input


    if (typeof op !== 'string') {
      throw new TypeError('string expected for parameter "op"');
    }

    if (typeof fn !== 'string') {
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
  OperatorNode.prototype.type = 'OperatorNode';
  OperatorNode.prototype.isOperatorNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  OperatorNode.prototype._compile = function (math, argNames) {
    // validate fn
    if (typeof this.fn !== 'string' || !isSafeMethod(math, this.fn)) {
      if (!math[this.fn]) {
        throw new Error('Function ' + this.fn + ' missing in provided namespace "math"');
      } else {
        throw new Error('No access to function "' + this.fn + '"');
      }
    }

    var fn = getSafeProperty(math, this.fn);
    var evalArgs = array_map(this.args, function (arg) {
      return arg._compile(math, argNames);
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
        return fn.apply(null, array_map(evalArgs, function (evalArg) {
          return evalArg(scope, args, context);
        }));
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  OperatorNode.prototype.forEach = function (callback) {
    for (var i = 0; i < this.args.length; i++) {
      callback(this.args[i], 'args[' + i + ']', this);
    }
  };
  /**
   * Create a new OperatorNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {OperatorNode} Returns a transformed copy of the node
   */


  OperatorNode.prototype.map = function (callback) {
    var args = [];

    for (var i = 0; i < this.args.length; i++) {
      args[i] = this._ifNode(callback(this.args[i], 'args[' + i + ']', this));
    }

    return new OperatorNode(this.op, this.fn, args, this.implicit, this.isPercentage);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {OperatorNode}
   */


  OperatorNode.prototype.clone = function () {
    return new OperatorNode(this.op, this.fn, this.args.slice(0), this.implicit, this.isPercentage);
  };
  /**
   * Check whether this is an unary OperatorNode:
   * has exactly one argument, like `-a`.
   * @return {boolean} Returns true when an unary operator node, false otherwise.
   */


  OperatorNode.prototype.isUnary = function () {
    return this.args.length === 1;
  };
  /**
   * Check whether this is a binary OperatorNode:
   * has exactly two arguments, like `a + b`.
   * @return {boolean} Returns true when a binary operator node, false otherwise.
   */


  OperatorNode.prototype.isBinary = function () {
    return this.args.length === 2;
  };
  /**
   * Calculate which parentheses are necessary. Gets an OperatorNode
   * (which is the root of the tree) and an Array of Nodes
   * (this.args) and returns an array where 'true' means that an argument
   * has to be enclosed in parentheses whereas 'false' means the opposite.
   *
   * @param {OperatorNode} root
   * @param {string} parenthesis
   * @param {Node[]} args
   * @param {boolean} latex
   * @return {boolean[]}
   * @private
   */


  function calculateNecessaryParentheses(root, parenthesis, implicit, args, latex) {
    // precedence of the root OperatorNode
    var precedence = getPrecedence(root, parenthesis);
    var associativity = getAssociativity(root, parenthesis);

    if (parenthesis === 'all' || args.length > 2 && root.getIdentifier() !== 'OperatorNode:add' && root.getIdentifier() !== 'OperatorNode:multiply') {
      return args.map(function (arg) {
        switch (arg.getContent().type) {
          // Nodes that don't need extra parentheses
          case 'ArrayNode':
          case 'ConstantNode':
          case 'SymbolNode':
          case 'ParenthesisNode':
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
        // unary operators
        {
          // precedence of the operand
          var operandPrecedence = getPrecedence(args[0], parenthesis); // handle special cases for LaTeX, where some of the parentheses aren't needed

          if (latex && operandPrecedence !== null) {
            var operandIdentifier;
            var rootIdentifier;

            if (parenthesis === 'keep') {
              operandIdentifier = args[0].getIdentifier();
              rootIdentifier = root.getIdentifier();
            } else {
              // Ignore Parenthesis Nodes when not in 'keep' mode
              operandIdentifier = args[0].getContent().getIdentifier();
              rootIdentifier = root.getContent().getIdentifier();
            }

            if (operators_properties[precedence][rootIdentifier].latexLeftParens === false) {
              result = [false];
              break;
            }

            if (operators_properties[operandPrecedence][operandIdentifier].latexParens === false) {
              result = [false];
              break;
            }
          }

          if (operandPrecedence === null) {
            // if the operand has no defined precedence, no parens are needed
            result = [false];
            break;
          }

          if (operandPrecedence <= precedence) {
            // if the operands precedence is lower, parens are needed
            result = [true];
            break;
          } // otherwise, no parens needed


          result = [false];
        }
        break;

      case 2:
        // binary operators
        {
          var lhsParens; // left hand side needs parenthesis?
          // precedence of the left hand side

          var lhsPrecedence = getPrecedence(args[0], parenthesis); // is the root node associative with the left hand side

          var assocWithLhs = isAssociativeWith(root, args[0], parenthesis);

          if (lhsPrecedence === null) {
            // if the left hand side has no defined precedence, no parens are needed
            // FunctionNode for example
            lhsParens = false;
          } else if (lhsPrecedence === precedence && associativity === 'right' && !assocWithLhs) {
            // In case of equal precedence, if the root node is left associative
            // parens are **never** necessary for the left hand side.
            // If it is right associative however, parens are necessary
            // if the root node isn't associative with the left hand side
            lhsParens = true;
          } else if (lhsPrecedence < precedence) {
            lhsParens = true;
          } else {
            lhsParens = false;
          }

          var rhsParens; // right hand side needs parenthesis?
          // precedence of the right hand side

          var rhsPrecedence = getPrecedence(args[1], parenthesis); // is the root node associative with the right hand side?

          var assocWithRhs = isAssociativeWith(root, args[1], parenthesis);

          if (rhsPrecedence === null) {
            // if the right hand side has no defined precedence, no parens are needed
            // FunctionNode for example
            rhsParens = false;
          } else if (rhsPrecedence === precedence && associativity === 'left' && !assocWithRhs) {
            // In case of equal precedence, if the root node is right associative
            // parens are **never** necessary for the right hand side.
            // If it is left associative however, parens are necessary
            // if the root node isn't associative with the right hand side
            rhsParens = true;
          } else if (rhsPrecedence < precedence) {
            rhsParens = true;
          } else {
            rhsParens = false;
          } // handle special cases for LaTeX, where some of the parentheses aren't needed


          if (latex) {
            var _rootIdentifier;

            var lhsIdentifier;
            var rhsIdentifier;

            if (parenthesis === 'keep') {
              _rootIdentifier = root.getIdentifier();
              lhsIdentifier = root.args[0].getIdentifier();
              rhsIdentifier = root.args[1].getIdentifier();
            } else {
              // Ignore ParenthesisNodes when not in 'keep' mode
              _rootIdentifier = root.getContent().getIdentifier();
              lhsIdentifier = root.args[0].getContent().getIdentifier();
              rhsIdentifier = root.args[1].getContent().getIdentifier();
            }

            if (lhsPrecedence !== null) {
              if (operators_properties[precedence][_rootIdentifier].latexLeftParens === false) {
                lhsParens = false;
              }

              if (operators_properties[lhsPrecedence][lhsIdentifier].latexParens === false) {
                lhsParens = false;
              }
            }

            if (rhsPrecedence !== null) {
              if (operators_properties[precedence][_rootIdentifier].latexRightParens === false) {
                rhsParens = false;
              }

              if (operators_properties[rhsPrecedence][rhsIdentifier].latexParens === false) {
                rhsParens = false;
              }
            }
          }

          result = [lhsParens, rhsParens];
        }
        break;

      default:
        if (root.getIdentifier() === 'OperatorNode:add' || root.getIdentifier() === 'OperatorNode:multiply') {
          result = args.map(function (arg) {
            var argPrecedence = getPrecedence(arg, parenthesis);
            var assocWithArg = isAssociativeWith(root, arg, parenthesis);
            var argAssociativity = getAssociativity(arg, parenthesis);

            if (argPrecedence === null) {
              // if the argument has no defined precedence, no parens are needed
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
    } // handles an edge case of 'auto' parentheses with implicit multiplication of ConstantNode
    // In that case print parentheses for ParenthesisNodes even though they normally wouldn't be
    // printed.


    if (args.length >= 2 && root.getIdentifier() === 'OperatorNode:multiply' && root.implicit && parenthesis === 'auto' && implicit === 'hide') {
      result = args.map(function (arg, index) {
        var isParenthesisNode = arg.getIdentifier() === 'ParenthesisNode';

        if (result[index] || isParenthesisNode) {
          // put in parenthesis?
          return true;
        }

        return false;
      });
    }

    return result;
  }
  /**
   * Get string representation.
   * @param {Object} options
   * @return {string} str
   */


  OperatorNode.prototype._toString = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var implicit = options && options.implicit ? options.implicit : 'hide';
    var args = this.args;
    var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, false);

    if (args.length === 1) {
      // unary operators
      var assoc = getAssociativity(this, parenthesis);
      var operand = args[0].toString(options);

      if (parens[0]) {
        operand = '(' + operand + ')';
      } // for example for "not", we want a space between operand and argument


      var opIsNamed = /[a-zA-Z]+/.test(this.op);

      if (assoc === 'right') {
        // prefix operator
        return this.op + (opIsNamed ? ' ' : '') + operand;
      } else if (assoc === 'left') {
        // postfix
        return operand + (opIsNamed ? ' ' : '') + this.op;
      } // fall back to postfix


      return operand + this.op;
    } else if (args.length === 2) {
      var lhs = args[0].toString(options); // left hand side

      var rhs = args[1].toString(options); // right hand side

      if (parens[0]) {
        // left hand side in parenthesis?
        lhs = '(' + lhs + ')';
      }

      if (parens[1]) {
        // right hand side in parenthesis?
        rhs = '(' + rhs + ')';
      }

      if (this.implicit && this.getIdentifier() === 'OperatorNode:multiply' && implicit === 'hide') {
        return lhs + ' ' + rhs;
      }

      return lhs + ' ' + this.op + ' ' + rhs;
    } else if (args.length > 2 && (this.getIdentifier() === 'OperatorNode:add' || this.getIdentifier() === 'OperatorNode:multiply')) {
      var stringifiedArgs = args.map(function (arg, index) {
        arg = arg.toString(options);

        if (parens[index]) {
          // put in parenthesis?
          arg = '(' + arg + ')';
        }

        return arg;
      });

      if (this.implicit && this.getIdentifier() === 'OperatorNode:multiply' && implicit === 'hide') {
        return stringifiedArgs.join(' ');
      }

      return stringifiedArgs.join(' ' + this.op + ' ');
    } else {
      // fallback to formatting as a function call
      return this.fn + '(' + this.args.join(', ') + ')';
    }
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  OperatorNode.prototype.toJSON = function () {
    return {
      mathjs: 'OperatorNode',
      op: this.op,
      fn: this.fn,
      args: this.args,
      implicit: this.implicit,
      isPercentage: this.isPercentage
    };
  };
  /**
   * Instantiate an OperatorNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "OperatorNode", "op": "+", "fn": "add", "args": [...], "implicit": false, "isPercentage":false}`,
   *                       where mathjs is optional
   * @returns {OperatorNode}
   */


  OperatorNode.fromJSON = function (json) {
    return new OperatorNode(json.op, json.fn, json.args, json.implicit, json.isPercentage);
  };
  /**
   * Get HTML representation.
   * @param {Object} options
   * @return {string} str
   */


  OperatorNode.prototype.toHTML = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var implicit = options && options.implicit ? options.implicit : 'hide';
    var args = this.args;
    var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, false);

    if (args.length === 1) {
      // unary operators
      var assoc = getAssociativity(this, parenthesis);
      var operand = args[0].toHTML(options);

      if (parens[0]) {
        operand = '<span class="math-parenthesis math-round-parenthesis">(</span>' + operand + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }

      if (assoc === 'right') {
        // prefix operator
        return '<span class="math-operator math-unary-operator math-lefthand-unary-operator">' + string_escape(this.op) + '</span>' + operand;
      } else {
        // postfix when assoc === 'left' or undefined
        return operand + '<span class="math-operator math-unary-operator math-righthand-unary-operator">' + string_escape(this.op) + '</span>';
      }
    } else if (args.length === 2) {
      // binary operatoes
      var lhs = args[0].toHTML(options); // left hand side

      var rhs = args[1].toHTML(options); // right hand side

      if (parens[0]) {
        // left hand side in parenthesis?
        lhs = '<span class="math-parenthesis math-round-parenthesis">(</span>' + lhs + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }

      if (parens[1]) {
        // right hand side in parenthesis?
        rhs = '<span class="math-parenthesis math-round-parenthesis">(</span>' + rhs + '<span class="math-parenthesis math-round-parenthesis">)</span>';
      }

      if (this.implicit && this.getIdentifier() === 'OperatorNode:multiply' && implicit === 'hide') {
        return lhs + '<span class="math-operator math-binary-operator math-implicit-binary-operator"></span>' + rhs;
      }

      return lhs + '<span class="math-operator math-binary-operator math-explicit-binary-operator">' + string_escape(this.op) + '</span>' + rhs;
    } else {
      var stringifiedArgs = args.map(function (arg, index) {
        arg = arg.toHTML(options);

        if (parens[index]) {
          // put in parenthesis?
          arg = '<span class="math-parenthesis math-round-parenthesis">(</span>' + arg + '<span class="math-parenthesis math-round-parenthesis">)</span>';
        }

        return arg;
      });

      if (args.length > 2 && (this.getIdentifier() === 'OperatorNode:add' || this.getIdentifier() === 'OperatorNode:multiply')) {
        if (this.implicit && this.getIdentifier() === 'OperatorNode:multiply' && implicit === 'hide') {
          return stringifiedArgs.join('<span class="math-operator math-binary-operator math-implicit-binary-operator"></span>');
        }

        return stringifiedArgs.join('<span class="math-operator math-binary-operator math-explicit-binary-operator">' + string_escape(this.op) + '</span>');
      } else {
        // fallback to formatting as a function call
        return '<span class="math-function">' + string_escape(this.fn) + '</span><span class="math-paranthesis math-round-parenthesis">(</span>' + stringifiedArgs.join('<span class="math-separator">,</span>') + '<span class="math-paranthesis math-round-parenthesis">)</span>';
      }
    }
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  OperatorNode.prototype._toTex = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var implicit = options && options.implicit ? options.implicit : 'hide';
    var args = this.args;
    var parens = calculateNecessaryParentheses(this, parenthesis, implicit, args, true);
    var op = latexOperators[this.fn];
    op = typeof op === 'undefined' ? this.op : op; // fall back to using this.op

    if (args.length === 1) {
      // unary operators
      var assoc = getAssociativity(this, parenthesis);
      var operand = args[0].toTex(options);

      if (parens[0]) {
        operand = "\\left(".concat(operand, "\\right)");
      }

      if (assoc === 'right') {
        // prefix operator
        return op + operand;
      } else if (assoc === 'left') {
        // postfix operator
        return operand + op;
      } // fall back to postfix


      return operand + op;
    } else if (args.length === 2) {
      // binary operators
      var lhs = args[0]; // left hand side

      var lhsTex = lhs.toTex(options);

      if (parens[0]) {
        lhsTex = "\\left(".concat(lhsTex, "\\right)");
      }

      var rhs = args[1]; // right hand side

      var rhsTex = rhs.toTex(options);

      if (parens[1]) {
        rhsTex = "\\left(".concat(rhsTex, "\\right)");
      } // handle some exceptions (due to the way LaTeX works)


      var lhsIdentifier;

      if (parenthesis === 'keep') {
        lhsIdentifier = lhs.getIdentifier();
      } else {
        // Ignore ParenthesisNodes if in 'keep' mode
        lhsIdentifier = lhs.getContent().getIdentifier();
      }

      switch (this.getIdentifier()) {
        case 'OperatorNode:divide':
          // op contains '\\frac' at this point
          return op + '{' + lhsTex + '}' + '{' + rhsTex + '}';

        case 'OperatorNode:pow':
          lhsTex = '{' + lhsTex + '}';
          rhsTex = '{' + rhsTex + '}';

          switch (lhsIdentifier) {
            case 'ConditionalNode': //

            case 'OperatorNode:divide':
              lhsTex = "\\left(".concat(lhsTex, "\\right)");
          }

          break;

        case 'OperatorNode:multiply':
          if (this.implicit && implicit === 'hide') {
            return lhsTex + '~' + rhsTex;
          }

      }

      return lhsTex + op + rhsTex;
    } else if (args.length > 2 && (this.getIdentifier() === 'OperatorNode:add' || this.getIdentifier() === 'OperatorNode:multiply')) {
      var texifiedArgs = args.map(function (arg, index) {
        arg = arg.toTex(options);

        if (parens[index]) {
          arg = "\\left(".concat(arg, "\\right)");
        }

        return arg;
      });

      if (this.getIdentifier() === 'OperatorNode:multiply' && this.implicit) {
        return texifiedArgs.join('~');
      }

      return texifiedArgs.join(op);
    } else {
      // fall back to formatting as a function call
      // as this is a fallback, it doesn't use
      // fancy function names
      return '\\mathrm{' + this.fn + '}\\left(' + args.map(function (arg) {
        return arg.toTex(options);
      }).join(',') + '\\right)';
    }
  };
  /**
   * Get identifier.
   * @return {string}
   */


  OperatorNode.prototype.getIdentifier = function () {
    return this.type + ':' + this.fn;
  };

  return OperatorNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesOperatorNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var OperatorNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createOperatorNode: createOperatorNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/ParenthesisNode.js


var ParenthesisNode_name = 'ParenthesisNode';
var ParenthesisNode_dependencies = ['Node'];
var createParenthesisNode = /* #__PURE__ */factory_factory(ParenthesisNode_name, ParenthesisNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * @constructor ParenthesisNode
   * @extends {Node}
   * A parenthesis node describes manual parenthesis from the user input
   * @param {Node} content
   * @extends {Node}
   */
  function ParenthesisNode(content) {
    if (!(this instanceof ParenthesisNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate input


    if (!isNode(content)) {
      throw new TypeError('Node expected for parameter "content"');
    }

    this.content = content;
  }

  ParenthesisNode.prototype = new Node();
  ParenthesisNode.prototype.type = 'ParenthesisNode';
  ParenthesisNode.prototype.isParenthesisNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  ParenthesisNode.prototype._compile = function (math, argNames) {
    return this.content._compile(math, argNames);
  };
  /**
   * Get the content of the current Node.
   * @return {Node} content
   * @override
   **/


  ParenthesisNode.prototype.getContent = function () {
    return this.content.getContent();
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  ParenthesisNode.prototype.forEach = function (callback) {
    callback(this.content, 'content', this);
  };
  /**
   * Create a new ParenthesisNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node) : Node} callback
   * @returns {ParenthesisNode} Returns a clone of the node
   */


  ParenthesisNode.prototype.map = function (callback) {
    var content = callback(this.content, 'content', this);
    return new ParenthesisNode(content);
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {ParenthesisNode}
   */


  ParenthesisNode.prototype.clone = function () {
    return new ParenthesisNode(this.content);
  };
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ParenthesisNode.prototype._toString = function (options) {
    if (!options || options && !options.parenthesis || options && options.parenthesis === 'keep') {
      return '(' + this.content.toString(options) + ')';
    }

    return this.content.toString(options);
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  ParenthesisNode.prototype.toJSON = function () {
    return {
      mathjs: 'ParenthesisNode',
      content: this.content
    };
  };
  /**
   * Instantiate an ParenthesisNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "ParenthesisNode", "content": ...}`,
   *                       where mathjs is optional
   * @returns {ParenthesisNode}
   */


  ParenthesisNode.fromJSON = function (json) {
    return new ParenthesisNode(json.content);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ParenthesisNode.prototype.toHTML = function (options) {
    if (!options || options && !options.parenthesis || options && options.parenthesis === 'keep') {
      return '<span class="math-parenthesis math-round-parenthesis">(</span>' + this.content.toHTML(options) + '<span class="math-parenthesis math-round-parenthesis">)</span>';
    }

    return this.content.toHTML(options);
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   * @override
   */


  ParenthesisNode.prototype._toTex = function (options) {
    if (!options || options && !options.parenthesis || options && options.parenthesis === 'keep') {
      return "\\left(".concat(this.content.toTex(options), "\\right)");
    }

    return this.content.toTex(options);
  };

  return ParenthesisNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesParenthesisNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var ParenthesisNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createParenthesisNode: createParenthesisNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/RangeNode.js



var RangeNode_name = 'RangeNode';
var RangeNode_dependencies = ['Node'];
var createRangeNode = /* #__PURE__ */factory_factory(RangeNode_name, RangeNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * @constructor RangeNode
   * @extends {Node}
   * create a range
   * @param {Node} start  included lower-bound
   * @param {Node} end    included upper-bound
   * @param {Node} [step] optional step
   */
  function RangeNode(start, end, step) {
    if (!(this instanceof RangeNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    } // validate inputs


    if (!isNode(start)) throw new TypeError('Node expected');
    if (!isNode(end)) throw new TypeError('Node expected');
    if (step && !isNode(step)) throw new TypeError('Node expected');
    if (arguments.length > 3) throw new Error('Too many arguments');
    this.start = start; // included lower-bound

    this.end = end; // included upper-bound

    this.step = step || null; // optional step
  }

  RangeNode.prototype = new Node();
  RangeNode.prototype.type = 'RangeNode';
  RangeNode.prototype.isRangeNode = true;
  /**
   * Check whether the RangeNode needs the `end` symbol to be defined.
   * This end is the size of the Matrix in current dimension.
   * @return {boolean}
   */

  RangeNode.prototype.needsEnd = function () {
    // find all `end` symbols in this RangeNode
    var endSymbols = this.filter(function (node) {
      return isSymbolNode(node) && node.name === 'end';
    });
    return endSymbols.length > 0;
  };
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */


  RangeNode.prototype._compile = function (math, argNames) {
    var range = math.range;

    var evalStart = this.start._compile(math, argNames);

    var evalEnd = this.end._compile(math, argNames);

    if (this.step) {
      var evalStep = this.step._compile(math, argNames);

      return function evalRangeNode(scope, args, context) {
        return range(evalStart(scope, args, context), evalEnd(scope, args, context), evalStep(scope, args, context));
      };
    } else {
      return function evalRangeNode(scope, args, context) {
        return range(evalStart(scope, args, context), evalEnd(scope, args, context));
      };
    }
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  RangeNode.prototype.forEach = function (callback) {
    callback(this.start, 'start', this);
    callback(this.end, 'end', this);

    if (this.step) {
      callback(this.step, 'step', this);
    }
  };
  /**
   * Create a new RangeNode having it's childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {RangeNode} Returns a transformed copy of the node
   */


  RangeNode.prototype.map = function (callback) {
    return new RangeNode(this._ifNode(callback(this.start, 'start', this)), this._ifNode(callback(this.end, 'end', this)), this.step && this._ifNode(callback(this.step, 'step', this)));
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {RangeNode}
   */


  RangeNode.prototype.clone = function () {
    return new RangeNode(this.start, this.end, this.step && this.step);
  };
  /**
   * Calculate the necessary parentheses
   * @param {Node} node
   * @param {string} parenthesis
   * @return {Object} parentheses
   * @private
   */


  function calculateNecessaryParentheses(node, parenthesis) {
    var precedence = getPrecedence(node, parenthesis);
    var parens = {};
    var startPrecedence = getPrecedence(node.start, parenthesis);
    parens.start = startPrecedence !== null && startPrecedence <= precedence || parenthesis === 'all';

    if (node.step) {
      var stepPrecedence = getPrecedence(node.step, parenthesis);
      parens.step = stepPrecedence !== null && stepPrecedence <= precedence || parenthesis === 'all';
    }

    var endPrecedence = getPrecedence(node.end, parenthesis);
    parens.end = endPrecedence !== null && endPrecedence <= precedence || parenthesis === 'all';
    return parens;
  }
  /**
   * Get string representation
   * @param {Object} options
   * @return {string} str
   */


  RangeNode.prototype._toString = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var parens = calculateNecessaryParentheses(this, parenthesis); // format string as start:step:stop

    var str;
    var start = this.start.toString(options);

    if (parens.start) {
      start = '(' + start + ')';
    }

    str = start;

    if (this.step) {
      var step = this.step.toString(options);

      if (parens.step) {
        step = '(' + step + ')';
      }

      str += ':' + step;
    }

    var end = this.end.toString(options);

    if (parens.end) {
      end = '(' + end + ')';
    }

    str += ':' + end;
    return str;
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  RangeNode.prototype.toJSON = function () {
    return {
      mathjs: 'RangeNode',
      start: this.start,
      end: this.end,
      step: this.step
    };
  };
  /**
   * Instantiate an RangeNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "RangeNode", "start": ..., "end": ..., "step": ...}`,
   *                       where mathjs is optional
   * @returns {RangeNode}
   */


  RangeNode.fromJSON = function (json) {
    return new RangeNode(json.start, json.end, json.step);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  RangeNode.prototype.toHTML = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var parens = calculateNecessaryParentheses(this, parenthesis); // format string as start:step:stop

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
  /**
   * Get LaTeX representation
   * @params {Object} options
   * @return {string} str
   */


  RangeNode.prototype._toTex = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
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

      str += ':' + step;
    }

    var end = this.end.toTex(options);

    if (parens.end) {
      end = "\\left(".concat(end, "\\right)");
    }

    str += ':' + end;
    return str;
  };

  return RangeNode;
}, {
  isClass: true,
  isNode: true
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesRangeNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var RangeNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createRangeNode: createRangeNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/node/RelationalNode.js





var RelationalNode_name = 'RelationalNode';
var RelationalNode_dependencies = ['Node'];
var createRelationalNode = /* #__PURE__ */factory_factory(RelationalNode_name, RelationalNode_dependencies, _ref => {
  var {
    Node
  } = _ref;

  /**
   * A node representing a chained conditional expression, such as 'x > y > z'
   *
   * @param {String[]} conditionals   An array of conditional operators used to compare the parameters
   * @param {Node[]} params   The parameters that will be compared
   *
   * @constructor RelationalNode
   * @extends {Node}
   */
  function RelationalNode(conditionals, params) {
    if (!(this instanceof RelationalNode)) {
      throw new SyntaxError('Constructor must be called with the new operator');
    }

    if (!Array.isArray(conditionals)) throw new TypeError('Parameter conditionals must be an array');
    if (!Array.isArray(params)) throw new TypeError('Parameter params must be an array');
    if (conditionals.length !== params.length - 1) throw new TypeError('Parameter params must contain exactly one more element than parameter conditionals');
    this.conditionals = conditionals;
    this.params = params;
  }

  RelationalNode.prototype = new Node();
  RelationalNode.prototype.type = 'RelationalNode';
  RelationalNode.prototype.isRelationalNode = true;
  /**
   * Compile a node into a JavaScript function.
   * This basically pre-calculates as much as possible and only leaves open
   * calculations which depend on a dynamic scope with variables.
   * @param {Object} math     Math.js namespace with functions and constants.
   * @param {Object} argNames An object with argument names as key and `true`
   *                          as value. Used in the SymbolNode to optimize
   *                          for arguments from user assigned functions
   *                          (see FunctionAssignmentNode) or special symbols
   *                          like `end` (see IndexNode).
   * @return {function} Returns a function which can be called like:
   *                        evalNode(scope: Object, args: Object, context: *)
   */

  RelationalNode.prototype._compile = function (math, argNames) {
    var self = this;
    var compiled = this.params.map(p => p._compile(math, argNames));
    return function evalRelationalNode(scope, args, context) {
      var evalLhs;
      var evalRhs = compiled[0](scope, args, context);

      for (var i = 0; i < self.conditionals.length; i++) {
        evalLhs = evalRhs;
        evalRhs = compiled[i + 1](scope, args, context);
        var condFn = getSafeProperty(math, self.conditionals[i]);

        if (!condFn(evalLhs, evalRhs)) {
          return false;
        }
      }

      return true;
    };
  };
  /**
   * Execute a callback for each of the child nodes of this node
   * @param {function(child: Node, path: string, parent: Node)} callback
   */


  RelationalNode.prototype.forEach = function (callback) {
    this.params.forEach((n, i) => callback(n, 'params[' + i + ']', this), this);
  };
  /**
   * Create a new RelationalNode having its childs be the results of calling
   * the provided callback function for each of the childs of the original node.
   * @param {function(child: Node, path: string, parent: Node): Node} callback
   * @returns {RelationalNode} Returns a transformed copy of the node
   */


  RelationalNode.prototype.map = function (callback) {
    return new RelationalNode(this.conditionals.slice(), this.params.map((n, i) => this._ifNode(callback(n, 'params[' + i + ']', this)), this));
  };
  /**
   * Create a clone of this node, a shallow copy
   * @return {RelationalNode}
   */


  RelationalNode.prototype.clone = function () {
    return new RelationalNode(this.conditionals, this.params);
  };
  /**
   * Get string representation.
   * @param {Object} options
   * @return {string} str
   */


  RelationalNode.prototype._toString = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var precedence = getPrecedence(this, parenthesis);
    var paramStrings = this.params.map(function (p, index) {
      var paramPrecedence = getPrecedence(p, parenthesis);
      return parenthesis === 'all' || paramPrecedence !== null && paramPrecedence <= precedence ? '(' + p.toString(options) + ')' : p.toString(options);
    });
    var operatorMap = {
      equal: '==',
      unequal: '!=',
      smaller: '<',
      larger: '>',
      smallerEq: '<=',
      largerEq: '>='
    };
    var ret = paramStrings[0];

    for (var i = 0; i < this.conditionals.length; i++) {
      ret += ' ' + operatorMap[this.conditionals[i]] + ' ' + paramStrings[i + 1];
    }

    return ret;
  };
  /**
   * Get a JSON representation of the node
   * @returns {Object}
   */


  RelationalNode.prototype.toJSON = function () {
    return {
      mathjs: 'RelationalNode',
      conditionals: this.conditionals,
      params: this.params
    };
  };
  /**
   * Instantiate a RelationalNode from its JSON representation
   * @param {Object} json  An object structured like
   *                       `{"mathjs": "RelationalNode", "condition": ..., "trueExpr": ..., "falseExpr": ...}`,
   *                       where mathjs is optional
   * @returns {RelationalNode}
   */


  RelationalNode.fromJSON = function (json) {
    return new RelationalNode(json.conditionals, json.params);
  };
  /**
   * Get HTML representation
   * @param {Object} options
   * @return {string} str
   */


  RelationalNode.prototype.toHTML = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var precedence = getPrecedence(this, parenthesis);
    var paramStrings = this.params.map(function (p, index) {
      var paramPrecedence = getPrecedence(p, parenthesis);
      return parenthesis === 'all' || paramPrecedence !== null && paramPrecedence <= precedence ? '<span class="math-parenthesis math-round-parenthesis">(</span>' + p.toHTML(options) + '<span class="math-parenthesis math-round-parenthesis">)</span>' : p.toHTML(options);
    });
    var operatorMap = {
      equal: '==',
      unequal: '!=',
      smaller: '<',
      larger: '>',
      smallerEq: '<=',
      largerEq: '>='
    };
    var ret = paramStrings[0];

    for (var i = 0; i < this.conditionals.length; i++) {
      ret += '<span class="math-operator math-binary-operator math-explicit-binary-operator">' + string_escape(operatorMap[this.conditionals[i]]) + '</span>' + paramStrings[i + 1];
    }

    return ret;
  };
  /**
   * Get LaTeX representation
   * @param {Object} options
   * @return {string} str
   */


  RelationalNode.prototype._toTex = function (options) {
    var parenthesis = options && options.parenthesis ? options.parenthesis : 'keep';
    var precedence = getPrecedence(this, parenthesis);
    var paramStrings = this.params.map(function (p, index) {
      var paramPrecedence = getPrecedence(p, parenthesis);
      return parenthesis === 'all' || paramPrecedence !== null && paramPrecedence <= precedence ? '\\left(' + p.toTex(options) + '\right)' : p.toTex(options);
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
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesRelationalNode.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var RelationalNodeDependencies = {
  NodeDependencies: NodeDependencies,
  createRelationalNode: createRelationalNode
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/switch.js
/**
 * Transpose a matrix
 * @param {Array} mat
 * @returns {Array} ret
 * @private
 */
function _switch(mat) {
  var I = mat.length;
  var J = mat[0].length;
  var i, j;
  var ret = [];

  for (j = 0; j < J; j++) {
    var tmp = [];

    for (i = 0; i < I; i++) {
      tmp.push(mat[i][j]);
    }

    ret.push(tmp);
  }

  return ret;
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/utils/collection.js




/**
 * Test whether an array contains collections
 * @param {Array} array
 * @returns {boolean} Returns true when the array contains one or multiple
 *                    collections (Arrays or Matrices). Returns false otherwise.
 */

function containsCollections(array) {
  for (var i = 0; i < array.length; i++) {
    if (isCollection(array[i])) {
      return true;
    }
  }

  return false;
}
/**
 * Recursively loop over all elements in a given multi dimensional array
 * and invoke the callback on each of the elements.
 * @param {Array | Matrix} array
 * @param {Function} callback     The callback method is invoked with one
 *                                parameter: the current element in the array
 */

function deepForEach(array, callback) {
  if (isMatrix(array)) {
    array = array.valueOf();
  }

  for (var i = 0, ii = array.length; i < ii; i++) {
    var value = array[i];

    if (Array.isArray(value)) {
      deepForEach(value, callback);
    } else {
      callback(value);
    }
  }
}
/**
 * Execute the callback function element wise for each element in array and any
 * nested array
 * Returns an array with the results
 * @param {Array | Matrix} array
 * @param {Function} callback   The callback is called with two parameters:
 *                              value1 and value2, which contain the current
 *                              element of both arrays.
 * @param {boolean} [skipZeros] Invoke callback function for non-zero values only.
 *
 * @return {Array | Matrix} res
 */

function deepMap(array, callback, skipZeros) {
  if (array && typeof array.map === 'function') {
    // TODO: replace array.map with a for loop to improve performance
    return array.map(function (x) {
      return deepMap(x, callback, skipZeros);
    });
  } else {
    return callback(array);
  }
}
/**
 * Reduce a given matrix or array to a new matrix or
 * array with one less dimension, applying the given
 * callback in the selected dimension.
 * @param {Array | Matrix} mat
 * @param {number} dim
 * @param {Function} callback
 * @return {Array | Matrix} res
 */

function reduce(mat, dim, callback) {
  var size = Array.isArray(mat) ? arraySize(mat) : mat.size();

  if (dim < 0 || dim >= size.length) {
    // TODO: would be more clear when throwing a DimensionError here
    throw new IndexError(dim, size.length);
  }

  if (isMatrix(mat)) {
    return mat.create(_reduce(mat.valueOf(), dim, callback));
  } else {
    return _reduce(mat, dim, callback);
  }
}
/**
 * Recursively reduce a matrix
 * @param {Array} mat
 * @param {number} dim
 * @param {Function} callback
 * @returns {Array} ret
 * @private
 */

function _reduce(mat, dim, callback) {
  var i, ret, val, tran;

  if (dim <= 0) {
    if (!Array.isArray(mat[0])) {
      val = mat[0];

      for (i = 1; i < mat.length; i++) {
        val = callback(val, mat[i]);
      }

      return val;
    } else {
      tran = _switch(mat);
      ret = [];

      for (i = 0; i < tran.length; i++) {
        ret[i] = _reduce(tran[i], dim - 1, callback);
      }

      return ret;
    }
  } else {
    ret = [];

    for (i = 0; i < mat.length; i++) {
      ret[i] = _reduce(mat[i], dim - 1, callback);
    }

    return ret;
  }
} // TODO: document function scatter


function scatter(a, j, w, x, u, mark, cindex, f, inverse, update, value) {
  // a arrays
  var avalues = a._values;
  var aindex = a._index;
  var aptr = a._ptr; // vars

  var k, k0, k1, i; // check we need to process values (pattern matrix)

  if (x) {
    // values in j
    for (k0 = aptr[j], k1 = aptr[j + 1], k = k0; k < k1; k++) {
      // row
      i = aindex[k]; // check value exists in current j

      if (w[i] !== mark) {
        // i is new entry in j
        w[i] = mark; // add i to pattern of C

        cindex.push(i); // x(i) = A, check we need to call function this time

        if (update) {
          // copy value to workspace calling callback function
          x[i] = inverse ? f(avalues[k], value) : f(value, avalues[k]); // function was called on current row

          u[i] = mark;
        } else {
          // copy value to workspace
          x[i] = avalues[k];
        }
      } else {
        // i exists in C already
        x[i] = inverse ? f(avalues[k], x[i]) : f(x[i], avalues[k]); // function was called on current row

        u[i] = mark;
      }
    }
  } else {
    // values in j
    for (k0 = aptr[j], k1 = aptr[j + 1], k = k0; k < k1; k++) {
      // row
      i = aindex[k]; // check value exists in current j

      if (w[i] !== mark) {
        // i is new entry in j
        w[i] = mark; // add i to pattern of C

        cindex.push(i);
      } else {
        // indicate function was called on current row
        u[i] = mark;
      }
    }
  }
}
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/type/number.js


var number_name = 'number';
var number_dependencies = ['typed'];
/**
 * Separates the radix, integer part, and fractional part of a non decimal number string
 * @param {string} input string to parse
 * @returns {object} the parts of the string or null if not a valid input
 */

function getNonDecimalNumberParts(input) {
  var nonDecimalWithRadixMatch = input.match(/(0[box])([0-9a-fA-F]*)\.([0-9a-fA-F]*)/);

  if (nonDecimalWithRadixMatch) {
    var radix = {
      '0b': 2,
      '0o': 8,
      '0x': 16
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
/**
 * Makes a number from a radix, and integer part, and a fractional part
 * @param {parts} [x] parts of the number string (from getNonDecimalNumberParts)
 * @returns {number} the number
 */


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

var createNumber = /* #__PURE__ */factory_factory(number_name, number_dependencies, _ref => {
  var {
    typed
  } = _ref;

  /**
   * Create a number or convert a string, boolean, or unit to a number.
   * When value is a matrix, all elements will be converted to number.
   *
   * Syntax:
   *
   *    math.number(value)
   *    math.number(unit, valuelessUnit)
   *
   * Examples:
   *
   *    math.number(2)                         // returns number 2
   *    math.number('7.2')                     // returns number 7.2
   *    math.number(true)                      // returns number 1
   *    math.number([true, false, true, true]) // returns [1, 0, 1, 1]
   *    math.number(math.unit('52cm'), 'm')    // returns 0.52
   *
   * See also:
   *
   *    bignumber, boolean, complex, index, matrix, string, unit
   *
   * @param {string | number | BigNumber | Fraction | boolean | Array | Matrix | Unit | null} [value]  Value to be converted
   * @param {Unit | string} [valuelessUnit] A valueless unit, used to convert a unit to a number
   * @return {number | Array | Matrix} The created number
   */
  var number = typed('number', {
    '': function _() {
      return 0;
    },
    number: function number(x) {
      return x;
    },
    string: function string(x) {
      if (x === 'NaN') return NaN;
      var nonDecimalNumberParts = getNonDecimalNumberParts(x);

      if (nonDecimalNumberParts) {
        return makeNumberFromNonDecimalParts(nonDecimalNumberParts);
      }

      var size = 0;
      var wordSizeSuffixMatch = x.match(/(0[box][0-9a-fA-F]*)i([0-9]*)/);

      if (wordSizeSuffixMatch) {
        // x includes a size suffix like 0xffffi32, so we extract
        // the suffix and remove it from x
        size = Number(wordSizeSuffixMatch[2]);
        x = wordSizeSuffixMatch[1];
      }

      var num = Number(x);

      if (isNaN(num)) {
        throw new SyntaxError('String "' + x + '" is no valid number');
      }

      if (wordSizeSuffixMatch) {
        // x is a signed bin, oct, or hex literal
        // num is the value of string x if x is interpreted as unsigned
        if (num > 2 ** size - 1) {
          // literal is too large for size suffix
          throw new SyntaxError("String \"".concat(x, "\" is out of range"));
        } // check if the bit at index size - 1 is set and if so do the twos complement


        if (num >= 2 ** (size - 1)) {
          num = num - 2 ** size;
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
      throw new Error('Second argument with valueless unit expected');
    },
    null: function _null(x) {
      return 0;
    },
    'Unit, string | Unit': function UnitStringUnit(unit, valuelessUnit) {
      return unit.toNumber(valuelessUnit);
    },
    'Array | Matrix': function ArrayMatrix(x) {
      return deepMap(x, this);
    }
  }); // reviver function to parse a JSON object like:
  //
  //     {"mathjs":"number","value":"2.3"}
  //
  // into a number 2.3

  number.fromJSON = function (json) {
    return parseFloat(json.value);
  };

  return number;
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesNumber.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var numberDependencies = {
  typedDependencies: typedDependencies,
  createNumber: createNumber
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/function/utils/numeric.js



var numeric_name = 'numeric';
var numeric_dependencies = ['number', '?bignumber', '?fraction'];
var createNumeric = /* #__PURE__ */factory_factory(numeric_name, numeric_dependencies, _ref => {
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
  }; // Load the conversion functions for each output type

  var validOutputTypes = {
    number: x => _number(x),
    BigNumber: bignumber ? x => bignumber(x) : noBignumber,
    Fraction: fraction ? x => fraction(x) : noFraction
  };
  /**
   * Convert a numeric input to a specific numeric type: number, BigNumber, or Fraction.
   *
   * Syntax:
   *
   *    math.numeric(x)
   *
   * Examples:
   *
   *    math.numeric('4')                           // returns number 4
   *    math.numeric('4', 'number')                 // returns number 4
   *    math.numeric('4', 'BigNumber')              // returns BigNumber 4
   *    math.numeric('4', 'Fraction')               // returns Fraction 4
   *    math.numeric(4, 'Fraction')                 // returns Fraction 4
   *    math.numeric(math.fraction(2, 5), 'number') // returns number 0.4
   *
   * See also:
   *
   *    number, fraction, bignumber, string, format
   *
   * @param {string | number | BigNumber | Fraction } value
   *              A numeric value or a string containing a numeric value
   * @param {string} outputType
   *              Desired numeric output type.
   *              Available values: 'number', 'BigNumber', or 'Fraction'
   * @return {number | BigNumber | Fraction}
   *              Returns an instance of the numeric in the requested type
   */

  return function numeric(value, outputType) {
    var inputType = typeOf(value);

    if (!(inputType in validInputTypes)) {
      throw new TypeError('Cannot convert ' + value + ' of type "' + inputType + '"; valid input types are ' + Object.keys(validInputTypes).join(', '));
    }

    if (!(outputType in validOutputTypes)) {
      throw new TypeError('Cannot convert ' + value + ' to type "' + outputType + '"; valid output types are ' + Object.keys(validOutputTypes).join(', '));
    }

    if (outputType === inputType) {
      return value;
    } else {
      return validOutputTypes[outputType](value);
    }
  };
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesNumeric.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


var numericDependencies = {
  numberDependencies: numberDependencies,
  createNumeric: createNumeric
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/parse.js





var parse_name = 'parse';
var parse_dependencies = ['typed', 'numeric', 'config', 'AccessorNode', 'ArrayNode', 'AssignmentNode', 'BlockNode', 'ConditionalNode', 'ConstantNode', 'FunctionAssignmentNode', 'FunctionNode', 'IndexNode', 'ObjectNode', 'OperatorNode', 'ParenthesisNode', 'RangeNode', 'RelationalNode', 'SymbolNode'];
var createParse = /* #__PURE__ */factory_factory(parse_name, parse_dependencies, _ref => {
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

  /**
   * Parse an expression. Returns a node tree, which can be evaluated by
   * invoking node.evaluate().
   *
   * Note the evaluating arbitrary expressions may involve security risks,
   * see [https://mathjs.org/docs/expressions/security.html](https://mathjs.org/docs/expressions/security.html) for more information.
   *
   * Syntax:
   *
   *     math.parse(expr)
   *     math.parse(expr, options)
   *     math.parse([expr1, expr2, expr3, ...])
   *     math.parse([expr1, expr2, expr3, ...], options)
   *
   * Example:
   *
   *     const node1 = math.parse('sqrt(3^2 + 4^2)')
   *     node1.compile().evaluate() // 5
   *
   *     let scope = {a:3, b:4}
   *     const node2 = math.parse('a * b') // 12
   *     const code2 = node2.compile()
   *     code2.evaluate(scope) // 12
   *     scope.a = 5
   *     code2.evaluate(scope) // 20
   *
   *     const nodes = math.parse(['a = 3', 'b = 4', 'a * b'])
   *     nodes[2].compile().evaluate() // 12
   *
   * See also:
   *
   *     evaluate, compile
   *
   * @param {string | string[] | Matrix} expr          Expression to be parsed
   * @param {{nodes: Object<string, Node>}} [options]  Available options:
   *                                                   - `nodes` a set of custom nodes
   * @return {Node | Node[]} node
   * @throws {Error}
   */
  var parse = typed(parse_name, {
    string: function string(expression) {
      return parseStart(expression, {});
    },
    'Array | Matrix': function ArrayMatrix(expressions) {
      return parseMultiple(expressions, {});
    },
    'string, Object': function stringObject(expression, options) {
      var extraNodes = options.nodes !== undefined ? options.nodes : {};
      return parseStart(expression, extraNodes);
    },
    'Array | Matrix, Object': parseMultiple
  });

  function parseMultiple(expressions) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    var extraNodes = options.nodes !== undefined ? options.nodes : {}; // parse an array or matrix with expressions

    return deepMap(expressions, function (elem) {
      if (typeof elem !== 'string') throw new TypeError('String expected');
      return parseStart(elem, extraNodes);
    });
  } // token types enumeration


  var TOKENTYPE = {
    NULL: 0,
    DELIMITER: 1,
    NUMBER: 2,
    SYMBOL: 3,
    UNKNOWN: 4
  }; // map with all delimiters

  var DELIMITERS = {
    ',': true,
    '(': true,
    ')': true,
    '[': true,
    ']': true,
    '{': true,
    '}': true,
    '"': true,
    '\'': true,
    ';': true,
    '+': true,
    '-': true,
    '*': true,
    '.*': true,
    '/': true,
    './': true,
    '%': true,
    '^': true,
    '.^': true,
    '~': true,
    '!': true,
    '&': true,
    '|': true,
    '^|': true,
    '=': true,
    ':': true,
    '?': true,
    '==': true,
    '!=': true,
    '<': true,
    '>': true,
    '<=': true,
    '>=': true,
    '<<': true,
    '>>': true,
    '>>>': true
  }; // map with all named delimiters

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
    undefined
  };
  var NUMERIC_CONSTANTS = ['NaN', 'Infinity'];

  function initialState() {
    return {
      extraNodes: {},
      // current extra nodes, must be careful not to mutate
      expression: '',
      // current expression
      comment: '',
      // last parsed comment
      index: 0,
      // current index in expr
      token: '',
      // current token
      tokenType: TOKENTYPE.NULL,
      // type of the token
      nestingLevel: 0,
      // level of nesting inside parameters, used to ignore newline characters
      conditionalLevel: null // when a conditional is being parsed, the level of the conditional is stored here

    };
  }
  /**
   * View upto `length` characters of the expression starting at the current character.
   *
   * @param {Object} state
   * @param {number} [length=1] Number of characters to view
   * @returns {string}
   * @private
   */


  function currentString(state, length) {
    return state.expression.substr(state.index, length);
  }
  /**
   * View the current character. Returns '' if end of expression is reached.
   *
   * @param {Object} state
   * @returns {string}
   * @private
   */


  function currentCharacter(state) {
    return currentString(state, 1);
  }
  /**
   * Get the next character from the expression.
   * The character is stored into the char c. If the end of the expression is
   * reached, the function puts an empty string in c.
   * @private
   */


  function next(state) {
    state.index++;
  }
  /**
   * Preview the previous character from the expression.
   * @return {string} cNext
   * @private
   */


  function prevCharacter(state) {
    return state.expression.charAt(state.index - 1);
  }
  /**
   * Preview the next character from the expression.
   * @return {string} cNext
   * @private
   */


  function nextCharacter(state) {
    return state.expression.charAt(state.index + 1);
  }
  /**
   * Get next token in the current string expr.
   * The token and token type are available as token and tokenType
   * @private
   */


  function getToken(state) {
    state.tokenType = TOKENTYPE.NULL;
    state.token = '';
    state.comment = ''; // skip over ignored characters:

    while (true) {
      // comments:
      if (currentCharacter(state) === '#') {
        while (currentCharacter(state) !== '\n' && currentCharacter(state) !== '') {
          state.comment += currentCharacter(state);
          next(state);
        }
      } // whitespace: space, tab, and newline when inside parameters


      if (parse.isWhitespace(currentCharacter(state), state.nestingLevel)) {
        next(state);
      } else {
        break;
      }
    } // check for end of expression


    if (currentCharacter(state) === '') {
      // token is still empty
      state.tokenType = TOKENTYPE.DELIMITER;
      return;
    } // check for new line character


    if (currentCharacter(state) === '\n' && !state.nestingLevel) {
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
    } // check for delimiters consisting of 2 characters


    if (c2.length === 2 && DELIMITERS[c2]) {
      state.tokenType = TOKENTYPE.DELIMITER;
      state.token = c2;
      next(state);
      next(state);
      return;
    } // check for delimiters consisting of 1 character


    if (DELIMITERS[c1]) {
      state.tokenType = TOKENTYPE.DELIMITER;
      state.token = c1;
      next(state);
      return;
    } // check for a number


    if (parse.isDigitDot(c1)) {
      state.tokenType = TOKENTYPE.NUMBER; // check for binary, octal, or hex

      var _c = currentString(state, 2);

      if (_c === '0b' || _c === '0o' || _c === '0x') {
        state.token += currentCharacter(state);
        next(state);
        state.token += currentCharacter(state);
        next(state);

        while (parse.isHexDigit(currentCharacter(state))) {
          state.token += currentCharacter(state);
          next(state);
        }

        if (currentCharacter(state) === '.') {
          // this number has a radix point
          state.token += '.';
          next(state); // get the digits after the radix

          while (parse.isHexDigit(currentCharacter(state))) {
            state.token += currentCharacter(state);
            next(state);
          }
        } else if (currentCharacter(state) === 'i') {
          // this number has a word size suffix
          state.token += 'i';
          next(state); // get the word size

          while (parse.isDigit(currentCharacter(state))) {
            state.token += currentCharacter(state);
            next(state);
          }
        }

        return;
      } // get number, can have a single dot


      if (currentCharacter(state) === '.') {
        state.token += currentCharacter(state);
        next(state);

        if (!parse.isDigit(currentCharacter(state))) {
          // this is no number, it is just a dot (can be dot notation)
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
      } // check for exponential notation like "2.3e-4", "1.23e50" or "2e+4"


      if (currentCharacter(state) === 'E' || currentCharacter(state) === 'e') {
        if (parse.isDigit(nextCharacter(state)) || nextCharacter(state) === '-' || nextCharacter(state) === '+') {
          state.token += currentCharacter(state);
          next(state);

          if (currentCharacter(state) === '+' || currentCharacter(state) === '-') {
            state.token += currentCharacter(state);
            next(state);
          } // Scientific notation MUST be followed by an exponent


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
        } else if (nextCharacter(state) === '.') {
          next(state);
          throw createSyntaxError(state, 'Digit expected, got "' + currentCharacter(state) + '"');
        }
      }

      return;
    } // check for variables, functions, named operators


    if (parse.isAlpha(currentCharacter(state), prevCharacter(state), nextCharacter(state))) {
      while (parse.isAlpha(currentCharacter(state), prevCharacter(state), nextCharacter(state)) || parse.isDigit(currentCharacter(state))) {
        state.token += currentCharacter(state);
        next(state);
      }

      if (object_hasOwnProperty(NAMED_DELIMITERS, state.token)) {
        state.tokenType = TOKENTYPE.DELIMITER;
      } else {
        state.tokenType = TOKENTYPE.SYMBOL;
      }

      return;
    } // something unknown is found, wrong characters -> a syntax error


    state.tokenType = TOKENTYPE.UNKNOWN;

    while (currentCharacter(state) !== '') {
      state.token += currentCharacter(state);
      next(state);
    }

    throw createSyntaxError(state, 'Syntax error in part "' + state.token + '"');
  }
  /**
   * Get next token and skip newline tokens
   */


  function getTokenSkipNewline(state) {
    do {
      getToken(state);
    } while (state.token === '\n'); // eslint-disable-line no-unmodified-loop-condition

  }
  /**
   * Open parameters.
   * New line characters will be ignored until closeParams(state) is called
   */


  function openParams(state) {
    state.nestingLevel++;
  }
  /**
   * Close parameters.
   * New line characters will no longer be ignored
   */


  function closeParams(state) {
    state.nestingLevel--;
  }
  /**
   * Checks whether the current character `c` is a valid alpha character:
   *
   * - A latin letter (upper or lower case) Ascii: a-z, A-Z
   * - An underscore                        Ascii: _
   * - A dollar sign                        Ascii: $
   * - A latin letter with accents          Unicode: \u00C0 - \u02AF
   * - A greek letter                       Unicode: \u0370 - \u03FF
   * - A mathematical alphanumeric symbol   Unicode: \u{1D400} - \u{1D7FF} excluding invalid code points
   *
   * The previous and next characters are needed to determine whether
   * this character is part of a unicode surrogate pair.
   *
   * @param {string} c      Current character in the expression
   * @param {string} cPrev  Previous character
   * @param {string} cNext  Next character
   * @return {boolean}
   */


  parse.isAlpha = function isAlpha(c, cPrev, cNext) {
    return parse.isValidLatinOrGreek(c) || parse.isValidMathSymbol(c, cNext) || parse.isValidMathSymbol(cPrev, c);
  };
  /**
   * Test whether a character is a valid latin, greek, or letter-like character
   * @param {string} c
   * @return {boolean}
   */


  parse.isValidLatinOrGreek = function isValidLatinOrGreek(c) {
    return /^[a-zA-Z_$\u00C0-\u02AF\u0370-\u03FF\u2100-\u214F]$/.test(c);
  };
  /**
   * Test whether two given 16 bit characters form a surrogate pair of a
   * unicode math symbol.
   *
   * https://unicode-table.com/en/
   * https://www.wikiwand.com/en/Mathematical_operators_and_symbols_in_Unicode
   *
   * Note: In ES6 will be unicode aware:
   * https://stackoverflow.com/questions/280712/javascript-unicode-regexes
   * https://mathiasbynens.be/notes/es6-unicode-regex
   *
   * @param {string} high
   * @param {string} low
   * @return {boolean}
   */


  parse.isValidMathSymbol = function isValidMathSymbol(high, low) {
    return /^[\uD835]$/.test(high) && /^[\uDC00-\uDFFF]$/.test(low) && /^[^\uDC55\uDC9D\uDCA0\uDCA1\uDCA3\uDCA4\uDCA7\uDCA8\uDCAD\uDCBA\uDCBC\uDCC4\uDD06\uDD0B\uDD0C\uDD15\uDD1D\uDD3A\uDD3F\uDD45\uDD47-\uDD49\uDD51\uDEA6\uDEA7\uDFCC\uDFCD]$/.test(low);
  };
  /**
   * Check whether given character c is a white space character: space, tab, or enter
   * @param {string} c
   * @param {number} nestingLevel
   * @return {boolean}
   */


  parse.isWhitespace = function isWhitespace(c, nestingLevel) {
    // TODO: also take '\r' carriage return as newline? Or does that give problems on mac?
    return c === ' ' || c === '\t' || c === '\n' && nestingLevel > 0;
  };
  /**
   * Test whether the character c is a decimal mark (dot).
   * This is the case when it's not the start of a delimiter '.*', './', or '.^'
   * @param {string} c
   * @param {string} cNext
   * @return {boolean}
   */


  parse.isDecimalMark = function isDecimalMark(c, cNext) {
    return c === '.' && cNext !== '/' && cNext !== '*' && cNext !== '^';
  };
  /**
   * checks if the given char c is a digit or dot
   * @param {string} c   a string with one character
   * @return {boolean}
   */


  parse.isDigitDot = function isDigitDot(c) {
    return c >= '0' && c <= '9' || c === '.';
  };
  /**
   * checks if the given char c is a digit
   * @param {string} c   a string with one character
   * @return {boolean}
   */


  parse.isDigit = function isDigit(c) {
    return c >= '0' && c <= '9';
  };
  /**
   * checks if the given char c is a hex digit
   * @param {string} c   a string with one character
   * @return {boolean}
   */


  parse.isHexDigit = function isHexDigit(c) {
    return c >= '0' && c <= '9' || c >= 'a' && c <= 'f' || c >= 'A' && c <= 'F';
  };
  /**
   * Start of the parse levels below, in order of precedence
   * @return {Node} node
   * @private
   */


  function parseStart(expression, extraNodes) {
    var state = initialState();

    extends_default()(state, {
      expression,
      extraNodes
    });

    getToken(state);
    var node = parseBlock(state); // check for garbage at the end of the expression
    // an expression ends with a empty character '' and tokenType DELIMITER

    if (state.token !== '') {
      if (state.tokenType === TOKENTYPE.DELIMITER) {
        // user entered a not existing operator like "//"
        // TODO: give hints for aliases, for example with "<>" give as hint " did you mean !== ?"
        throw createError(state, 'Unexpected operator ' + state.token);
      } else {
        throw createSyntaxError(state, 'Unexpected part "' + state.token + '"');
      }
    }

    return node;
  }
  /**
   * Parse a block with expressions. Expressions can be separated by a newline
   * character '\n', or by a semicolon ';'. In case of a semicolon, no output
   * of the preceding line is returned.
   * @return {Node} node
   * @private
   */


  function parseBlock(state) {
    var node;
    var blocks = [];
    var visible;

    if (state.token !== '' && state.token !== '\n' && state.token !== ';') {
      node = parseAssignment(state);
      node.comment = state.comment;
    } // TODO: simplify this loop


    while (state.token === '\n' || state.token === ';') {
      // eslint-disable-line no-unmodified-loop-condition
      if (blocks.length === 0 && node) {
        visible = state.token !== ';';
        blocks.push({
          node,
          visible
        });
      }

      getToken(state);

      if (state.token !== '\n' && state.token !== ';' && state.token !== '') {
        node = parseAssignment(state);
        node.comment = state.comment;
        visible = state.token !== ';';
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
        node = new ConstantNode(undefined);
        node.comment = state.comment;
      }

      return node;
    }
  }
  /**
   * Assignment of a function or variable,
   * - can be a variable like 'a=2.3'
   * - or a updating an existing variable like 'matrix(2,3:5)=[6,7,8]'
   * - defining a function like 'f(x) = x^2'
   * @return {Node} node
   * @private
   */


  function parseAssignment(state) {
    var name, args, value, valid;
    var node = parseConditional(state);

    if (state.token === '=') {
      if (isSymbolNode(node)) {
        // parse a variable assignment like 'a = 2/3'
        name = node.name;
        getTokenSkipNewline(state);
        value = parseAssignment(state);
        return new AssignmentNode(new SymbolNode(name), value);
      } else if (isAccessorNode(node)) {
        // parse a matrix subset assignment like 'A[1,2] = 4'
        getTokenSkipNewline(state);
        value = parseAssignment(state);
        return new AssignmentNode(node.object, node.index, value);
      } else if (isFunctionNode(node) && isSymbolNode(node.fn)) {
        // parse function assignment like 'f(x) = x^2'
        valid = true;
        args = [];
        name = node.name;
        node.args.forEach(function (arg, index) {
          if (isSymbolNode(arg)) {
            args[index] = arg.name;
          } else {
            valid = false;
          }
        });

        if (valid) {
          getTokenSkipNewline(state);
          value = parseAssignment(state);
          return new FunctionAssignmentNode(name, args, value);
        }
      }

      throw createSyntaxError(state, 'Invalid left hand side of assignment operator =');
    }

    return node;
  }
  /**
   * conditional operation
   *
   *     condition ? truePart : falsePart
   *
   * Note: conditional operator is right-associative
   *
   * @return {Node} node
   * @private
   */


  function parseConditional(state) {
    var node = parseLogicalOr(state);

    while (state.token === '?') {
      // eslint-disable-line no-unmodified-loop-condition
      // set a conditional level, the range operator will be ignored as long
      // as conditionalLevel === state.nestingLevel.
      var prev = state.conditionalLevel;
      state.conditionalLevel = state.nestingLevel;
      getTokenSkipNewline(state);
      var condition = node;
      var trueExpr = parseAssignment(state);
      if (state.token !== ':') throw createSyntaxError(state, 'False part of conditional expression expected');
      state.conditionalLevel = null;
      getTokenSkipNewline(state);
      var falseExpr = parseAssignment(state); // Note: check for conditional operator again, right associativity

      node = new ConditionalNode(condition, trueExpr, falseExpr); // restore the previous conditional level

      state.conditionalLevel = prev;
    }

    return node;
  }
  /**
   * logical or, 'x or y'
   * @return {Node} node
   * @private
   */


  function parseLogicalOr(state) {
    var node = parseLogicalXor(state);

    while (state.token === 'or') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('or', 'or', [node, parseLogicalXor(state)]);
    }

    return node;
  }
  /**
   * logical exclusive or, 'x xor y'
   * @return {Node} node
   * @private
   */


  function parseLogicalXor(state) {
    var node = parseLogicalAnd(state);

    while (state.token === 'xor') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('xor', 'xor', [node, parseLogicalAnd(state)]);
    }

    return node;
  }
  /**
   * logical and, 'x and y'
   * @return {Node} node
   * @private
   */


  function parseLogicalAnd(state) {
    var node = parseBitwiseOr(state);

    while (state.token === 'and') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('and', 'and', [node, parseBitwiseOr(state)]);
    }

    return node;
  }
  /**
   * bitwise or, 'x | y'
   * @return {Node} node
   * @private
   */


  function parseBitwiseOr(state) {
    var node = parseBitwiseXor(state);

    while (state.token === '|') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('|', 'bitOr', [node, parseBitwiseXor(state)]);
    }

    return node;
  }
  /**
   * bitwise exclusive or (xor), 'x ^| y'
   * @return {Node} node
   * @private
   */


  function parseBitwiseXor(state) {
    var node = parseBitwiseAnd(state);

    while (state.token === '^|') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('^|', 'bitXor', [node, parseBitwiseAnd(state)]);
    }

    return node;
  }
  /**
   * bitwise and, 'x & y'
   * @return {Node} node
   * @private
   */


  function parseBitwiseAnd(state) {
    var node = parseRelational(state);

    while (state.token === '&') {
      // eslint-disable-line no-unmodified-loop-condition
      getTokenSkipNewline(state);
      node = new OperatorNode('&', 'bitAnd', [node, parseRelational(state)]);
    }

    return node;
  }
  /**
   * Parse a chained conditional, like 'a > b >= c'
   * @return {Node} node
   */


  function parseRelational(state) {
    var params = [parseShift(state)];
    var conditionals = [];
    var operators = {
      '==': 'equal',
      '!=': 'unequal',
      '<': 'smaller',
      '>': 'larger',
      '<=': 'smallerEq',
      '>=': 'largerEq'
    };

    while (object_hasOwnProperty(operators, state.token)) {
      // eslint-disable-line no-unmodified-loop-condition
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
      return new RelationalNode(conditionals.map(c => c.fn), params);
    }
  }
  /**
   * Bitwise left shift, bitwise right arithmetic shift, bitwise right logical shift
   * @return {Node} node
   * @private
   */


  function parseShift(state) {
    var node, name, fn, params;
    node = parseConversion(state);
    var operators = {
      '<<': 'leftShift',
      '>>': 'rightArithShift',
      '>>>': 'rightLogShift'
    };

    while (object_hasOwnProperty(operators, state.token)) {
      name = state.token;
      fn = operators[name];
      getTokenSkipNewline(state);
      params = [node, parseConversion(state)];
      node = new OperatorNode(name, fn, params);
    }

    return node;
  }
  /**
   * conversion operators 'to' and 'in'
   * @return {Node} node
   * @private
   */


  function parseConversion(state) {
    var node, name, fn, params;
    node = parseRange(state);
    var operators = {
      to: 'to',
      in: 'to' // alias of 'to'

    };

    while (object_hasOwnProperty(operators, state.token)) {
      name = state.token;
      fn = operators[name];
      getTokenSkipNewline(state);

      if (name === 'in' && state.token === '') {
        // end of expression -> this is the unit 'in' ('inch')
        node = new OperatorNode('*', 'multiply', [node, new SymbolNode('in')], true);
      } else {
        // operator 'a to b' or 'a in b'
        params = [node, parseRange(state)];
        node = new OperatorNode(name, fn, params);
      }
    }

    return node;
  }
  /**
   * parse range, "start:end", "start:step:end", ":", "start:", ":end", etc
   * @return {Node} node
   * @private
   */


  function parseRange(state) {
    var node;
    var params = [];

    if (state.token === ':') {
      // implicit start=1 (one-based)
      node = new ConstantNode(1);
    } else {
      // explicit start
      node = parseAddSubtract(state);
    }

    if (state.token === ':' && state.conditionalLevel !== state.nestingLevel) {
      // we ignore the range operator when a conditional operator is being processed on the same level
      params.push(node); // parse step and end

      while (state.token === ':' && params.length < 3) {
        // eslint-disable-line no-unmodified-loop-condition
        getTokenSkipNewline(state);

        if (state.token === ')' || state.token === ']' || state.token === ',' || state.token === '') {
          // implicit end
          params.push(new SymbolNode('end'));
        } else {
          // explicit end
          params.push(parseAddSubtract(state));
        }
      }

      if (params.length === 3) {
        // params = [start, step, end]
        node = new RangeNode(params[0], params[2], params[1]); // start, end, step
      } else {
        // length === 2
        // params = [start, end]
        node = new RangeNode(params[0], params[1]); // start, end
      }
    }

    return node;
  }
  /**
   * add or subtract
   * @return {Node} node
   * @private
   */


  function parseAddSubtract(state) {
    var node, name, fn, params;
    node = parseMultiplyDivide(state);
    var operators = {
      '+': 'add',
      '-': 'subtract'
    };

    while (object_hasOwnProperty(operators, state.token)) {
      name = state.token;
      fn = operators[name];
      getTokenSkipNewline(state);
      var rightNode = parseMultiplyDivide(state);

      if (rightNode.isPercentage) {
        params = [node, new OperatorNode('*', 'multiply', [node, rightNode])];
      } else {
        params = [node, rightNode];
      }

      node = new OperatorNode(name, fn, params);
    }

    return node;
  }
  /**
   * multiply, divide, modulus
   * @return {Node} node
   * @private
   */


  function parseMultiplyDivide(state) {
    var node, last, name, fn;
    node = parseImplicitMultiplication(state);
    last = node;
    var operators = {
      '*': 'multiply',
      '.*': 'dotMultiply',
      '/': 'divide',
      './': 'dotDivide'
    };

    while (true) {
      if (object_hasOwnProperty(operators, state.token)) {
        // explicit operators
        name = state.token;
        fn = operators[name];
        getTokenSkipNewline(state);
        last = parseImplicitMultiplication(state);
        node = new OperatorNode(name, fn, [node, last]);
      } else {
        break;
      }
    }

    return node;
  }
  /**
   * implicit multiplication
   * @return {Node} node
   * @private
   */


  function parseImplicitMultiplication(state) {
    var node, last;
    node = parseRule2(state);
    last = node;

    while (true) {
      if (state.tokenType === TOKENTYPE.SYMBOL || state.token === 'in' && isConstantNode(node) || state.tokenType === TOKENTYPE.NUMBER && !isConstantNode(last) && (!isOperatorNode(last) || last.op === '!') || state.token === '(') {
        // parse implicit multiplication
        //
        // symbol:      implicit multiplication like '2a', '(2+3)a', 'a b'
        // number:      implicit multiplication like '(2+3)2'
        // parenthesis: implicit multiplication like '2(3+4)', '(3+4)(1+2)'
        last = parseRule2(state);
        node = new OperatorNode('*', 'multiply', [node, last], true
        /* implicit */
        );
      } else {
        break;
      }
    }

    return node;
  }
  /**
   * Infamous "rule 2" as described in https://github.com/josdejong/mathjs/issues/792#issuecomment-361065370
   * Explicit division gets higher precedence than implicit multiplication
   * when the division matches this pattern: [number] / [number] [symbol]
   * @return {Node} node
   * @private
   */


  function parseRule2(state) {
    var node = parsePercentage(state);
    var last = node;
    var tokenStates = [];

    while (true) {
      // Match the "number /" part of the pattern "number / number symbol"
      if (state.token === '/' && isConstantNode(last)) {
        // Look ahead to see if the next token is a number
        tokenStates.push(extends_default()({}, state));
        getTokenSkipNewline(state); // Match the "number / number" part of the pattern

        if (state.tokenType === TOKENTYPE.NUMBER) {
          // Look ahead again
          tokenStates.push(extends_default()({}, state));
          getTokenSkipNewline(state); // Match the "symbol" part of the pattern, or a left parenthesis

          if (state.tokenType === TOKENTYPE.SYMBOL || state.token === '(') {
            // We've matched the pattern "number / number symbol".
            // Rewind once and build the "number / number" node; the symbol will be consumed later
            extends_default()(state, tokenStates.pop());

            tokenStates.pop();
            last = parsePercentage(state);
            node = new OperatorNode('/', 'divide', [node, last]);
          } else {
            // Not a match, so rewind
            tokenStates.pop();

            extends_default()(state, tokenStates.pop());

            break;
          }
        } else {
          // Not a match, so rewind
          extends_default()(state, tokenStates.pop());

          break;
        }
      } else {
        break;
      }
    }

    return node;
  }
  /**
   * percentage or mod
   * @return {Node} node
   * @private
   */


  function parsePercentage(state) {
    var node, name, fn, params;
    node = parseUnary(state);
    var operators = {
      '%': 'mod',
      mod: 'mod'
    };

    while (object_hasOwnProperty(operators, state.token)) {
      name = state.token;
      fn = operators[name];
      getTokenSkipNewline(state);

      if (name === '%' && state.tokenType === TOKENTYPE.DELIMITER && state.token !== '(') {
        // If the expression contains only %, then treat that as /100
        node = new OperatorNode('/', 'divide', [node, new ConstantNode(100)], false, true);
      } else {
        params = [node, parseUnary(state)];
        node = new OperatorNode(name, fn, params);
      }
    }

    return node;
  }
  /**
   * Unary plus and minus, and logical and bitwise not
   * @return {Node} node
   * @private
   */


  function parseUnary(state) {
    var name, params, fn;
    var operators = {
      '-': 'unaryMinus',
      '+': 'unaryPlus',
      '~': 'bitNot',
      not: 'not'
    };

    if (object_hasOwnProperty(operators, state.token)) {
      fn = operators[state.token];
      name = state.token;
      getTokenSkipNewline(state);
      params = [parseUnary(state)];
      return new OperatorNode(name, fn, params);
    }

    return parsePow(state);
  }
  /**
   * power
   * Note: power operator is right associative
   * @return {Node} node
   * @private
   */


  function parsePow(state) {
    var node, name, fn, params;
    node = parseLeftHandOperators(state);

    if (state.token === '^' || state.token === '.^') {
      name = state.token;
      fn = name === '^' ? 'pow' : 'dotPow';
      getTokenSkipNewline(state);
      params = [node, parseUnary(state)]; // Go back to unary, we can have '2^-3'

      node = new OperatorNode(name, fn, params);
    }

    return node;
  }
  /**
   * Left hand operators: factorial x!, ctranspose x'
   * @return {Node} node
   * @private
   */


  function parseLeftHandOperators(state) {
    var node, name, fn, params;
    node = parseCustomNodes(state);
    var operators = {
      '!': 'factorial',
      '\'': 'ctranspose'
    };

    while (object_hasOwnProperty(operators, state.token)) {
      name = state.token;
      fn = operators[name];
      getToken(state);
      params = [node];
      node = new OperatorNode(name, fn, params);
      node = parseAccessors(state, node);
    }

    return node;
  }
  /**
   * Parse a custom node handler. A node handler can be used to process
   * nodes in a custom way, for example for handling a plot.
   *
   * A handler must be passed as second argument of the parse function.
   * - must extend math.Node
   * - must contain a function _compile(defs: Object) : string
   * - must contain a function find(filter: Object) : Node[]
   * - must contain a function toString() : string
   * - the constructor is called with a single argument containing all parameters
   *
   * For example:
   *
   *     nodes = {
   *       'plot': PlotHandler
   *     }
   *
   * The constructor of the handler is called as:
   *
   *     node = new PlotHandler(params)
   *
   * The handler will be invoked when evaluating an expression like:
   *
   *     node = math.parse('plot(sin(x), x)', nodes)
   *
   * @return {Node} node
   * @private
   */


  function parseCustomNodes(state) {
    var params = [];

    if (state.tokenType === TOKENTYPE.SYMBOL && object_hasOwnProperty(state.extraNodes, state.token)) {
      var CustomNode = state.extraNodes[state.token];
      getToken(state); // parse parameters

      if (state.token === '(') {
        params = [];
        openParams(state);
        getToken(state);

        if (state.token !== ')') {
          params.push(parseAssignment(state)); // parse a list with parameters

          while (state.token === ',') {
            // eslint-disable-line no-unmodified-loop-condition
            getToken(state);
            params.push(parseAssignment(state));
          }
        }

        if (state.token !== ')') {
          throw createSyntaxError(state, 'Parenthesis ) expected');
        }

        closeParams(state);
        getToken(state);
      } // create a new custom node
      // noinspection JSValidateTypes


      return new CustomNode(params);
    }

    return parseSymbol(state);
  }
  /**
   * parse symbols: functions, variables, constants, units
   * @return {Node} node
   * @private
   */


  function parseSymbol(state) {
    var node, name;

    if (state.tokenType === TOKENTYPE.SYMBOL || state.tokenType === TOKENTYPE.DELIMITER && state.token in NAMED_DELIMITERS) {
      name = state.token;
      getToken(state);

      if (object_hasOwnProperty(CONSTANTS, name)) {
        // true, false, null, ...
        node = new ConstantNode(CONSTANTS[name]);
      } else if (NUMERIC_CONSTANTS.indexOf(name) !== -1) {
        // NaN, Infinity
        node = new ConstantNode(numeric(name, 'number'));
      } else {
        node = new SymbolNode(name);
      } // parse function parameters and matrix index


      node = parseAccessors(state, node);
      return node;
    }

    return parseDoubleQuotesString(state);
  }
  /**
   * parse accessors:
   * - function invocation in round brackets (...), for example sqrt(2)
   * - index enclosed in square brackets [...], for example A[2,3]
   * - dot notation for properties, like foo.bar
   * @param {Object} state
   * @param {Node} node    Node on which to apply the parameters. If there
   *                       are no parameters in the expression, the node
   *                       itself is returned
   * @param {string[]} [types]  Filter the types of notations
   *                            can be ['(', '[', '.']
   * @return {Node} node
   * @private
   */


  function parseAccessors(state, node, types) {
    var params;

    while ((state.token === '(' || state.token === '[' || state.token === '.') && (!types || types.indexOf(state.token) !== -1)) {
      // eslint-disable-line no-unmodified-loop-condition
      params = [];

      if (state.token === '(') {
        if (isSymbolNode(node) || isAccessorNode(node)) {
          // function invocation like fn(2, 3) or obj.fn(2, 3)
          openParams(state);
          getToken(state);

          if (state.token !== ')') {
            params.push(parseAssignment(state)); // parse a list with parameters

            while (state.token === ',') {
              // eslint-disable-line no-unmodified-loop-condition
              getToken(state);
              params.push(parseAssignment(state));
            }
          }

          if (state.token !== ')') {
            throw createSyntaxError(state, 'Parenthesis ) expected');
          }

          closeParams(state);
          getToken(state);
          node = new FunctionNode(node, params);
        } else {
          // implicit multiplication like (2+3)(4+5) or sqrt(2)(1+2)
          // don't parse it here but let it be handled by parseImplicitMultiplication
          // with correct precedence
          return node;
        }
      } else if (state.token === '[') {
        // index notation like variable[2, 3]
        openParams(state);
        getToken(state);

        if (state.token !== ']') {
          params.push(parseAssignment(state)); // parse a list with parameters

          while (state.token === ',') {
            // eslint-disable-line no-unmodified-loop-condition
            getToken(state);
            params.push(parseAssignment(state));
          }
        }

        if (state.token !== ']') {
          throw createSyntaxError(state, 'Parenthesis ] expected');
        }

        closeParams(state);
        getToken(state);
        node = new AccessorNode(node, new IndexNode(params));
      } else {
        // dot notation like variable.prop
        getToken(state);

        if (state.tokenType !== TOKENTYPE.SYMBOL) {
          throw createSyntaxError(state, 'Property name expected after dot');
        }

        params.push(new ConstantNode(state.token));
        getToken(state);
        var dotNotation = true;
        node = new AccessorNode(node, new IndexNode(params, dotNotation));
      }
    }

    return node;
  }
  /**
   * Parse a double quotes string.
   * @return {Node} node
   * @private
   */


  function parseDoubleQuotesString(state) {
    var node, str;

    if (state.token === '"') {
      str = parseDoubleQuotesStringToken(state); // create constant

      node = new ConstantNode(str); // parse index parameters

      node = parseAccessors(state, node);
      return node;
    }

    return parseSingleQuotesString(state);
  }
  /**
   * Parse a string surrounded by double quotes "..."
   * @return {string}
   */


  function parseDoubleQuotesStringToken(state) {
    var str = '';

    while (currentCharacter(state) !== '' && currentCharacter(state) !== '"') {
      if (currentCharacter(state) === '\\') {
        // escape character, immediately process the next
        // character to prevent stopping at a next '\"'
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
    return JSON.parse('"' + str + '"'); // unescape escaped characters
  }
  /**
   * Parse a single quotes string.
   * @return {Node} node
   * @private
   */


  function parseSingleQuotesString(state) {
    var node, str;

    if (state.token === '\'') {
      str = parseSingleQuotesStringToken(state); // create constant

      node = new ConstantNode(str); // parse index parameters

      node = parseAccessors(state, node);
      return node;
    }

    return parseMatrix(state);
  }
  /**
   * Parse a string surrounded by single quotes '...'
   * @return {string}
   */


  function parseSingleQuotesStringToken(state) {
    var str = '';

    while (currentCharacter(state) !== '' && currentCharacter(state) !== '\'') {
      if (currentCharacter(state) === '\\') {
        // escape character, immediately process the next
        // character to prevent stopping at a next '\''
        str += currentCharacter(state);
        next(state);
      }

      str += currentCharacter(state);
      next(state);
    }

    getToken(state);

    if (state.token !== '\'') {
      throw createSyntaxError(state, 'End of string \' expected');
    }

    getToken(state);
    return JSON.parse('"' + str + '"'); // unescape escaped characters
  }
  /**
   * parse the matrix
   * @return {Node} node
   * @private
   */


  function parseMatrix(state) {
    var array, params, rows, cols;

    if (state.token === '[') {
      // matrix [...]
      openParams(state);
      getToken(state);

      if (state.token !== ']') {
        // this is a non-empty matrix
        var row = parseRow(state);

        if (state.token === ';') {
          // 2 dimensional array
          rows = 1;
          params = [row]; // the rows of the matrix are separated by dot-comma's

          while (state.token === ';') {
            // eslint-disable-line no-unmodified-loop-condition
            getToken(state);
            params[rows] = parseRow(state);
            rows++;
          }

          if (state.token !== ']') {
            throw createSyntaxError(state, 'End of matrix ] expected');
          }

          closeParams(state);
          getToken(state); // check if the number of columns matches in all rows

          cols = params[0].items.length;

          for (var r = 1; r < rows; r++) {
            if (params[r].items.length !== cols) {
              throw createError(state, 'Column dimensions mismatch ' + '(' + params[r].items.length + ' !== ' + cols + ')');
            }
          }

          array = new ArrayNode(params);
        } else {
          // 1 dimensional vector
          if (state.token !== ']') {
            throw createSyntaxError(state, 'End of matrix ] expected');
          }

          closeParams(state);
          getToken(state);
          array = row;
        }
      } else {
        // this is an empty matrix "[ ]"
        closeParams(state);
        getToken(state);
        array = new ArrayNode([]);
      }

      return parseAccessors(state, array);
    }

    return parseObject(state);
  }
  /**
   * Parse a single comma-separated row from a matrix, like 'a, b, c'
   * @return {ArrayNode} node
   */


  function parseRow(state) {
    var params = [parseAssignment(state)];
    var len = 1;

    while (state.token === ',') {
      // eslint-disable-line no-unmodified-loop-condition
      getToken(state); // parse expression

      params[len] = parseAssignment(state);
      len++;
    }

    return new ArrayNode(params);
  }
  /**
   * parse an object, enclosed in angle brackets{...}, for example {value: 2}
   * @return {Node} node
   * @private
   */


  function parseObject(state) {
    if (state.token === '{') {
      openParams(state);
      var key;
      var properties = {};

      do {
        getToken(state);

        if (state.token !== '}') {
          // parse key
          if (state.token === '"') {
            key = parseDoubleQuotesStringToken(state);
          } else if (state.token === '\'') {
            key = parseSingleQuotesStringToken(state);
          } else if (state.tokenType === TOKENTYPE.SYMBOL || state.tokenType === TOKENTYPE.DELIMITER && state.token in NAMED_DELIMITERS) {
            key = state.token;
            getToken(state);
          } else {
            throw createSyntaxError(state, 'Symbol or string expected as object key');
          } // parse key/value separator


          if (state.token !== ':') {
            throw createSyntaxError(state, 'Colon : expected after object key');
          }

          getToken(state); // parse key

          properties[key] = parseAssignment(state);
        }
      } while (state.token === ','); // eslint-disable-line no-unmodified-loop-condition


      if (state.token !== '}') {
        throw createSyntaxError(state, 'Comma , or bracket } expected after object value');
      }

      closeParams(state);
      getToken(state);
      var node = new ObjectNode(properties); // parse index parameters

      node = parseAccessors(state, node);
      return node;
    }

    return parseNumber(state);
  }
  /**
   * parse a number
   * @return {Node} node
   * @private
   */


  function parseNumber(state) {
    var numberStr;

    if (state.tokenType === TOKENTYPE.NUMBER) {
      // this is a number
      numberStr = state.token;
      getToken(state);
      return new ConstantNode(numeric(numberStr, config.number));
    }

    return parseParentheses(state);
  }
  /**
   * parentheses
   * @return {Node} node
   * @private
   */


  function parseParentheses(state) {
    var node; // check if it is a parenthesized expression

    if (state.token === '(') {
      // parentheses (...)
      openParams(state);
      getToken(state);
      node = parseAssignment(state); // start again

      if (state.token !== ')') {
        throw createSyntaxError(state, 'Parenthesis ) expected');
      }

      closeParams(state);
      getToken(state);
      node = new ParenthesisNode(node);
      node = parseAccessors(state, node);
      return node;
    }

    return parseEnd(state);
  }
  /**
   * Evaluated when the expression is not yet ended but expected to end
   * @return {Node} res
   * @private
   */


  function parseEnd(state) {
    if (state.token === '') {
      // syntax error or unexpected end of expression
      throw createSyntaxError(state, 'Unexpected end of expression');
    } else {
      throw createSyntaxError(state, 'Value expected');
    }
  }
  /**
   * Shortcut for getting the current row value (one based)
   * Returns the line of the currently handled expression
   * @private
   */

  /* TODO: implement keeping track on the row number
  function row () {
    return null
  }
  */

  /**
   * Shortcut for getting the current col value (one based)
   * Returns the column (position) where the last state.token starts
   * @private
   */


  function col(state) {
    return state.index - state.token.length + 1;
  }
  /**
   * Create an error
   * @param {Object} state
   * @param {string} message
   * @return {SyntaxError} instantiated error
   * @private
   */


  function createSyntaxError(state, message) {
    var c = col(state);
    var error = new SyntaxError(message + ' (char ' + c + ')');
    error.char = c;
    return error;
  }
  /**
   * Create an error
   * @param {Object} state
   * @param {string} message
   * @return {Error} instantiated error
   * @private
   */


  function createError(state, message) {
    var c = col(state);
    var error = new SyntaxError(message + ' (char ' + c + ')');
    error.char = c;
    return error;
  }

  return parse;
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesParse.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */


















var parseDependencies = {
  AccessorNodeDependencies: AccessorNodeDependencies,
  ArrayNodeDependencies: ArrayNodeDependencies,
  AssignmentNodeDependencies: AssignmentNodeDependencies,
  BlockNodeDependencies: BlockNodeDependencies,
  ConditionalNodeDependencies: ConditionalNodeDependencies,
  ConstantNodeDependencies: ConstantNodeDependencies,
  FunctionAssignmentNodeDependencies: FunctionAssignmentNodeDependencies,
  FunctionNodeDependencies: FunctionNodeDependencies,
  IndexNodeDependencies: IndexNodeDependencies,
  ObjectNodeDependencies: ObjectNodeDependencies,
  OperatorNodeDependencies: OperatorNodeDependencies,
  ParenthesisNodeDependencies: ParenthesisNodeDependencies,
  RangeNodeDependencies: RangeNodeDependencies,
  RelationalNodeDependencies: RelationalNodeDependencies,
  SymbolNodeDependencies: SymbolNodeDependencies,
  numericDependencies: numericDependencies,
  typedDependencies: typedDependencies,
  createParse: createParse
};
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/expression/function/evaluate.js



var evaluate_name = 'evaluate';
var evaluate_dependencies = ['typed', 'parse'];
var createEvaluate = /* #__PURE__ */factory_factory(evaluate_name, evaluate_dependencies, _ref => {
  var {
    typed,
    parse
  } = _ref;

  /**
   * Evaluate an expression.
   *
   * Note the evaluating arbitrary expressions may involve security risks,
   * see [https://mathjs.org/docs/expressions/security.html](https://mathjs.org/docs/expressions/security.html) for more information.
   *
   * Syntax:
   *
   *     math.evaluate(expr)
   *     math.evaluate(expr, scope)
   *     math.evaluate([expr1, expr2, expr3, ...])
   *     math.evaluate([expr1, expr2, expr3, ...], scope)
   *
   * Example:
   *
   *     math.evaluate('(2+3)/4')                // 1.25
   *     math.evaluate('sqrt(3^2 + 4^2)')        // 5
   *     math.evaluate('sqrt(-4)')               // 2i
   *     math.evaluate(['a=3', 'b=4', 'a*b'])    // [3, 4, 12]
   *
   *     let scope = {a:3, b:4}
   *     math.evaluate('a * b', scope)           // 12
   *
   * See also:
   *
   *    parse, compile
   *
   * @param {string | string[] | Matrix} expr   The expression to be evaluated
   * @param {Object} [scope]                    Scope to read/write variables
   * @return {*} The result of the expression
   * @throws {Error}
   */
  return typed(evaluate_name, {
    string: function string(expr) {
      var scope = createEmptyMap();
      return parse(expr).compile().evaluate(scope);
    },
    'string, Map | Object': function stringMapObject(expr, scope) {
      return parse(expr).compile().evaluate(scope);
    },
    'Array | Matrix': function ArrayMatrix(expr) {
      var scope = createEmptyMap();
      return deepMap(expr, function (entry) {
        return parse(entry).compile().evaluate(scope);
      });
    },
    'Array | Matrix, Map | Object': function ArrayMatrixMapObject(expr, scope) {
      return deepMap(expr, function (entry) {
        return parse(entry).compile().evaluate(scope);
      });
    }
  });
});
// CONCATENATED MODULE: ./node_modules/mathjs/lib/esm/entry/dependenciesNumber/dependenciesEvaluate.generated.js
/**
 * THIS FILE IS AUTO-GENERATED
 * DON'T MAKE CHANGES HERE
 */



var evaluateDependencies = {
  parseDependencies: parseDependencies,
  typedDependencies: typedDependencies,
  createEvaluate: createEvaluate
};
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/expressions.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

/* eslint-disable @typescript-eslint/ban-ts-comment */

var expressions_math = create_create({
  evaluateDependencies: evaluateDependencies
}); // add our own simple operators to avoid having to import math.js' ones, to keep the
// generated size down.

expressions_math.import({
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  add: function add(a, b) {
    return a + b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  subtract: function subtract(a, b) {
    return a - b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  multiply: function multiply(a, b) {
    return a * b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  divide: function divide(a, b) {
    return a / b;
  },
  // eslint-disable-next-line
  equal: function equal(a, b) {
    return a == b;
  },
  // eslint-disable-next-line
  unequal: function unequal(a, b) {
    return a != b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  not: function not(a) {
    return !a;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  and: function and(a, b) {
    return a && b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  or: function or(a, b) {
    return a || b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  largerEq: function largerEq(a, b) {
    return a >= b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  larger: function larger(a, b) {
    return a > b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  smallerEq: function smallerEq(a, b) {
    return a <= b;
  },
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  smaller: function smaller(a, b) {
    return a < b;
  }
}, {
  override: true
});
/* harmony default export */ var src_expressions = (expressions_math);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=512ab4ff

var _hoisted_1 = {
  class: "form-group row matomo-form-field"
};
var _hoisted_2 = {
  key: 0,
  class: "col s12"
};
var _hoisted_3 = {
  key: 0,
  class: "form-help"
};
var _hoisted_4 = {
  key: 0,
  class: "inline-help",
  ref: "inlineHelp"
};

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_ctx.formField.introduction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.introduction), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'input-field': _ctx.formField.uiControl !== 'checkbox' && _ctx.formField.uiControl !== 'radio',
      'file-field': _ctx.formField.uiControl === 'file',
      'm6': !_ctx.formField.fullWidth
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.childComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(Object.assign(Object.assign({
    formField: _ctx.formField
  }, _ctx.formField), {}, {
    modelValue: _ctx.processedModelValue,
    modelModifiers: _ctx.modelModifiers,
    availableOptions: _ctx.availableOptions
  }, _ctx.extraChildComponentParams), {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }), null, 16))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'm6': !_ctx.formField.fullWidth
    }])
  }, [_ctx.showFormHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "form-description"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.formField.description]]), _ctx.formField.inlineHelp || _ctx.hasInlineHelpSlot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_4, [_ctx.inlineHelpComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.inlineHelpComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeProps"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    key: 0
  }, _ctx.inlineHelpBind)), null, 16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.defaultValuePrettyTruncated), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showDefaultValue]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=512ab4ff

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=5e4c53f0

var FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_1 = {
  class: "checkbox"
};
var FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_2 = ["checked", "id", "name"];
var FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_3 = ["innerHTML"];
function FieldCheckboxvue_type_template_id_5e4c53f0_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes, {
    value: 1,
    checked: _ctx.isChecked,
    type: "checkbox",
    id: _ctx.name,
    name: _ctx.name
  }), null, 16, FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldCheckboxvue_type_template_id_5e4c53f0_hoisted_3)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=5e4c53f0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldCheckboxvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Boolean, Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object,
    name: String,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var newValue = event.target.checked;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            event.target.checked = !newValue;
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    isChecked: function isChecked() {
      return !!this.modelValue && this.modelValue !== '0';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue



FieldCheckboxvue_type_script_lang_ts.render = FieldCheckboxvue_type_template_id_5e4c53f0_render

/* harmony default export */ var FieldCheckbox = (FieldCheckboxvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=32c1d214

var FieldCheckboxArrayvue_type_template_id_32c1d214_hoisted_1 = {
  ref: "root"
};
var FieldCheckboxArrayvue_type_template_id_32c1d214_hoisted_2 = ["value", "checked", "onChange", "id", "name"];
function FieldCheckboxArrayvue_type_template_id_32c1d214_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxArrayvue_type_template_id_32c1d214_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (checkboxModel, $index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: $index,
      class: "checkbox"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: checkboxModel.key,
      checked: !!_ctx.checkboxStates[$index],
      onChange: function onChange($event) {
        return _ctx.onChange($index);
      }
    }, _ctx.uiControlAttributes, {
      type: "checkbox",
      id: "".concat(_ctx.name).concat(checkboxModel.key),
      name: checkboxModel.name
    }), null, 16, FieldCheckboxArrayvue_type_template_id_32c1d214_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], checkboxModel.description]])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=32c1d214

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



function getCheckboxStates(availableOptions, modelValue) {
  return (availableOptions || []).map(function (o) {
    return modelValue && modelValue.indexOf(o.key) !== -1;
  });
}

/* harmony default export */ var FieldCheckboxArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    modelModifiers: Object,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    type: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    checkboxStates: function checkboxStates() {
      return getCheckboxStates(this.availableOptions, this.modelValue);
    }
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  methods: {
    onChange: function onChange(changedIndex) {
      var _this$modelModifiers,
          _this = this;

      var checkboxStates = _toConsumableArray(this.checkboxStates);

      checkboxStates[changedIndex] = !checkboxStates[changedIndex];
      var availableOptions = this.availableOptions || {};
      var newValue = [];
      Object.values(availableOptions).forEach(function (option, index) {
        if (checkboxStates[index]) {
          newValue.push(option.key);
        }
      });

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          // undo checked changes since we want the parent component to decide if it should go
          // through
          var item = _this.$refs.root.querySelectorAll('input').item(changedIndex);

          item.checked = !item.checked;
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue



FieldCheckboxArrayvue_type_script_lang_ts.render = FieldCheckboxArrayvue_type_template_id_32c1d214_render

/* harmony default export */ var FieldCheckboxArray = (FieldCheckboxArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=9042f0ea

var FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_1 = {
  class: "expandableSelector"
};

var FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("svg", {
  class: "caret",
  height: "24",
  viewBox: "0 0 24 24",
  width: "24",
  xmlns: "http://www.w3.org/2000/svg"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M7 10l5 5 5-5z"
}), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M0 0h24v24H0z",
  fill: "none"
})], -1);

var FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_3 = ["value"];
var FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_4 = {
  class: "expandableList z-depth-2"
};
var FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_5 = {
  class: "searchContainer"
};
var _hoisted_6 = {
  class: "collection firstLevel"
};
var _hoisted_7 = ["onClick"];
var _hoisted_8 = {
  class: "collection secondLevel"
};
var _hoisted_9 = ["onClick"];
var _hoisted_10 = {
  class: "primary-content"
};
var _hoisted_11 = ["title"];
function FieldExpandableSelectvue_type_template_id_9042f0ea_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showSelect = !_ctx.showSelect;
    }),
    class: "select-wrapper"
  }, [FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "select-dropdown",
    readonly: "readonly",
    value: _ctx.modelValueText
  }, null, 8, FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_9042f0ea_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    placeholder: "Search",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    class: "expandableSearch browser-default"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.showSelect
  }]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (options, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "collection-item",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", {
      class: "expandableListCategory",
      onClick: function onClick($event) {
        return _ctx.onCategoryClicked(options);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(options.group) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["secondary-content", {
        "icon-chevron-right": _ctx.showCategory !== options.group,
        "icon-chevron-down": _ctx.showCategory === options.group
      }])
    }, null, 2)], 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options.values.filter(function (x) {
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
    }), function (children) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: "expandableListItem collection-item valign-wrapper",
        key: children.key,
        onClick: function onClick($event) {
          return _ctx.onValueClicked(children);
        }
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(children.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        title: children.tooltip,
        class: "secondary-content icon-help"
      }, null, 8, _hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], children.tooltip]])], 8, _hoisted_9);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showCategory === options.group || _ctx.searchTerm]])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], options.values.filter(function (x) {
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
    }).length]]);
  }), 128))])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSelect]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=9042f0ea

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts


function getAvailableOptions(availableValues) {
  var flatValues = [];

  if (!availableValues) {
    return flatValues;
  }

  var groups = {};
  Object.values(availableValues).forEach(function (uncastedValue) {
    var value = uncastedValue;
    var group = value.group || '';

    if (!(group in groups) || !groups[group]) {
      groups[group] = {
        values: [],
        group: group
      };
    }

    var formatted = {
      key: value.key,
      value: value.value
    };

    if ('tooltip' in value && value.tooltip) {
      formatted.tooltip = value.tooltip;
    }

    groups[group].values.push(formatted);
  });
  Object.values(groups).forEach(function (group) {
    if (group.values.length) {
      flatValues.push(group);
    }
  });
  return flatValues;
}
/* harmony default export */ var FieldExpandableSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Number, String],
    modelModifiers: Object,
    availableOptions: Array,
    title: String
  },
  directives: {
    FocusAnywhereButHere: external_CoreHome_["FocusAnywhereButHere"],
    FocusIf: external_CoreHome_["FocusIf"]
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  data: function data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: ''
    };
  },
  computed: {
    modelValueText: function modelValueText() {
      if (this.title) {
        return this.title;
      }

      var key = this.modelValue;
      var availableOptions = this.availableOptions || [];
      var keyItem;
      availableOptions.some(function (option) {
        keyItem = option.values.find(function (item) {
          return item.key === key;
        });
        return keyItem; // stop iterating if found
      });

      if (keyItem) {
        return keyItem.value ? "".concat(keyItem.value) : '';
      }

      return key ? "".concat(key) : '';
    }
  },
  methods: {
    onBlur: function onBlur() {
      this.showSelect = false;
    },
    onCategoryClicked: function onCategoryClicked(options) {
      if (this.showCategory === options.group) {
        this.showCategory = '';
      } else {
        this.showCategory = options.group;
      }
    },
    onValueClicked: function onValueClicked(selectedValue) {
      var _this$modelModifiers;

      this.showSelect = false;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', selectedValue.key);
        return;
      }

      var emitEventData = {
        value: selectedValue.key,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue



FieldExpandableSelectvue_type_script_lang_ts.render = FieldExpandableSelectvue_type_template_id_9042f0ea_render

/* harmony default export */ var FieldExpandableSelect = (FieldExpandableSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=5b71669f

var FieldFieldArrayvue_type_template_id_5b71669f_hoisted_1 = ["for", "innerHTML"];
function FieldFieldArrayvue_type_template_id_5b71669f_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FieldArray = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FieldArray");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFieldArrayvue_type_template_id_5b71669f_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FieldArray, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onValueUpdate($event);
    }),
    "model-modifiers": _ctx.modelModifiers,
    field: _ctx.uiControlAttributes.field,
    rows: _ctx.uiControlAttributes.rows
  }, null, 8, ["name", "model-value", "model-modifiers", "field", "rows"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=5b71669f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldFieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FieldArray: external_CoreHome_["FieldArray"]
  },
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onValueUpdate: function onValueUpdate(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue



FieldFieldArrayvue_type_script_lang_ts.render = FieldFieldArrayvue_type_template_id_5b71669f_render

/* harmony default export */ var FieldFieldArray = (FieldFieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2f36f604

var FieldFilevue_type_template_id_2f36f604_hoisted_1 = {
  class: "btn"
};
var FieldFilevue_type_template_id_2f36f604_hoisted_2 = ["for", "innerHTML"];
var FieldFilevue_type_template_id_2f36f604_hoisted_3 = ["name", "id"];
var FieldFilevue_type_template_id_2f36f604_hoisted_4 = {
  class: "file-path-wrapper"
};
var FieldFilevue_type_template_id_2f36f604_hoisted_5 = ["value"];
function FieldFilevue_type_template_id_2f36f604_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2f36f604_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFilevue_type_template_id_2f36f604_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    ref: "fileInput",
    name: _ctx.name,
    type: "file",
    id: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldFilevue_type_template_id_2f36f604_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2f36f604_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "file-path validate",
    value: _ctx.filePath,
    type: "text"
  }, null, 8, FieldFilevue_type_template_id_2f36f604_hoisted_5)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2f36f604

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldFilevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: [String, File],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  watch: {
    modelValue: function modelValue(v) {
      if (!v || v === '') {
        var fileInputElement = this.$refs.fileInput;
        fileInputElement.value = '';
      }
    }
  },
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers;

      var files = event.target.files;

      if (!files) {
        return;
      }

      var file = files.item(0);

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', file);
        return;
      }

      var emitEventData = {
        value: file,
        abort: function abort() {// not supported
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  },
  computed: {
    filePath: function filePath() {
      if (this.modelValue instanceof File) {
        return this.$refs.fileInput.value;
      }

      return undefined;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue



FieldFilevue_type_script_lang_ts.render = FieldFilevue_type_template_id_2f36f604_render

/* harmony default export */ var FieldFile = (FieldFilevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=2f9d3442

var FieldHiddenvue_type_template_id_2f9d3442_hoisted_1 = ["type", "name", "value"];
function FieldHiddenvue_type_template_id_2f9d3442_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: _ctx.uiControl,
    name: _ctx.name,
    value: _ctx.modelValue,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldHiddenvue_type_template_id_2f9d3442_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=2f9d3442

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldHiddenvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uiControl: String,
    name: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue



FieldHiddenvue_type_script_lang_ts.render = FieldHiddenvue_type_template_id_2f9d3442_render

/* harmony default export */ var FieldHidden = (FieldHiddenvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=26c2361c

var FieldMultituplevue_type_template_id_26c2361c_hoisted_1 = {
  class: "fieldMultiTuple"
};
var FieldMultituplevue_type_template_id_26c2361c_hoisted_2 = ["for", "innerHTML"];
function FieldMultituplevue_type_template_id_26c2361c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MultiPairField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultiPairField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldMultituplevue_type_template_id_26c2361c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldMultituplevue_type_template_id_26c2361c_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MultiPairField, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _ctx.onUpdateValue,
    "model-modifiers": _ctx.modelModifiers,
    field1: _ctx.uiControlAttributes.field1,
    field2: _ctx.uiControlAttributes.field2,
    field3: _ctx.uiControlAttributes.field3,
    field4: _ctx.uiControlAttributes.field4,
    rows: _ctx.uiControlAttributes.rows
  }, null, 8, ["name", "model-value", "onUpdate:modelValue", "model-modifiers", "field1", "field2", "field3", "field4", "rows"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=26c2361c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldMultituplevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    MultiPairField: external_CoreHome_["MultiPairField"]
  },
  emits: ['update:modelValue'],
  methods: {
    onUpdateValue: function onUpdateValue(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue



FieldMultituplevue_type_script_lang_ts.render = FieldMultituplevue_type_template_id_26c2361c_render

/* harmony default export */ var FieldMultituple = (FieldMultituplevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=78b71a69

var FieldNumbervue_type_template_id_78b71a69_hoisted_1 = ["type", "id", "name", "value"];
var FieldNumbervue_type_template_id_78b71a69_hoisted_2 = ["for", "innerHTML"];
function FieldNumbervue_type_template_id_78b71a69_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueFormatted,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldNumbervue_type_template_id_78b71a69_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldNumbervue_type_template_id_78b71a69_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=78b71a69

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldNumbervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    uiControl: String,
    name: String,
    title: String,
    modelValue: [Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onChange = Object(external_CoreHome_["debounce"])(this.onChange.bind(this), 50);
  },
  methods: {
    onChange: function onChange(event) {
      var _this = this;

      var value = parseFloat(event.target.value);

      if (value !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.modelValueFormatted) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.modelValueFormatted;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  computed: {
    modelValueFormatted: function modelValueFormatted() {
      return (this.modelValue || '').toString();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue



FieldNumbervue_type_script_lang_ts.render = FieldNumbervue_type_template_id_78b71a69_render

/* harmony default export */ var FieldNumber = (FieldNumbervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=47947a14

var FieldRadiovue_type_template_id_47947a14_hoisted_1 = {
  ref: "root"
};
var FieldRadiovue_type_template_id_47947a14_hoisted_2 = ["value", "id", "name", "disabled", "checked"];
function FieldRadiovue_type_template_id_47947a14_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldRadiovue_type_template_id_47947a14_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions || [], function (radioModel) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: radioModel.key,
      class: "radio"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: radioModel.key,
      onChange: _cache[0] || (_cache[0] = function ($event) {
        return _ctx.onChange($event);
      }),
      type: "radio",
      id: "".concat(_ctx.name).concat(radioModel.key),
      name: _ctx.name,
      disabled: radioModel.disabled || _ctx.disabled
    }, _ctx.uiControlAttributes, {
      checked: _ctx.modelValue === radioModel.key || "".concat(_ctx.modelValue) === radioModel.key
    }), null, 16, FieldRadiovue_type_template_id_47947a14_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.value) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], radioModel.description]])])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=47947a14

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldRadiovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers,
          _this = this;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', event.target.value);
        return;
      }

      var reset = function reset() {
        // change to previous value so the parent component can determine if this change should
        // go through
        _this.$refs.root.querySelectorAll('input').forEach(function (inp, i) {
          var _this$availableOption;

          if (!((_this$availableOption = _this.availableOptions) !== null && _this$availableOption !== void 0 && _this$availableOption[i])) {
            return;
          }

          var key = _this.availableOptions[i].key;
          inp.checked = _this.modelValue === key || "".concat(_this.modelValue) === key;
        });
      };

      var emitEventData = {
        value: event.target.value,
        abort: function abort() {
          reset();
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue



FieldRadiovue_type_script_lang_ts.render = FieldRadiovue_type_template_id_47947a14_render

/* harmony default export */ var FieldRadio = (FieldRadiovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=2254b68e
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || FieldSelectvue_type_template_id_2254b68e_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_template_id_2254b68e_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_template_id_2254b68e_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_template_id_2254b68e_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_template_id_2254b68e_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }


var FieldSelectvue_type_template_id_2254b68e_hoisted_1 = {
  key: 0,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_2254b68e_hoisted_2 = ["multiple", "name"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_3 = ["label"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_4 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_5 = ["for", "innerHTML"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_6 = {
  key: 1,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_2254b68e_hoisted_7 = ["multiple", "name"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_8 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_2254b68e_hoisted_9 = ["for", "innerHTML"];
function FieldSelectvue_type_template_id_2254b68e_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.groupedOptions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_2254b68e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "select",
    class: "grouped",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groupedOptions, function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        group = _ref2[0],
        options = _ref2[1];

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
      key: group,
      label: group
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options, function (option) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: option.key,
        value: "string:".concat(option.key),
        selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
        disabled: option.disabled
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_2254b68e_hoisted_4);
    }), 128))], 8, FieldSelectvue_type_template_id_2254b68e_hoisted_3);
  }), 128))], 16, FieldSelectvue_type_template_id_2254b68e_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_2254b68e_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.groupedOptions && _ctx.options ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_2254b68e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "ungrouped",
    ref: "select",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, function (option) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option.key,
      value: "string:".concat(option.key),
      selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
      disabled: option.disabled
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_2254b68e_hoisted_8);
  }), 128))], 16, FieldSelectvue_type_template_id_2254b68e_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_2254b68e_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=2254b68e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
function FieldSelectvue_type_script_lang_ts_toConsumableArray(arr) { return FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArray(arr) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr) || FieldSelectvue_type_script_lang_ts_nonIterableSpread(); }

function FieldSelectvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr); }

function FieldSelectvue_type_script_lang_ts_slicedToArray(arr, i) { return FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || FieldSelectvue_type_script_lang_ts_nonIterableRest(); }

function FieldSelectvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }



function initMaterialSelect(select, modelValue, placeholder) {
  var uiControlOptions = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
  var multiple = arguments.length > 4 ? arguments[4] : undefined;

  if (!select) {
    return;
  }

  var $select = window.$(select); // reset selected since materialize removes them

  Array.from(select.options).forEach(function (opt) {
    if (multiple) {
      opt.selected = !!modelValue && modelValue.indexOf(opt.value.replace(/^string:/, '')) !== -1;
    } else {
      opt.selected = "string:".concat(modelValue) === opt.value;
    }
  });
  $select.formSelect(uiControlOptions); // add placeholder to input

  if (placeholder) {
    var $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (Array.isArray(availableValues) || !(_typeof(availableValues) === 'object')) {
    return false;
  }

  return Object.values(availableValues).some(function (v) {
    return _typeof(v) === 'object';
  });
}

function hasOption(flatValues, key) {
  return flatValues.some(function (f) {
    return f.key === key;
  });
}

function FieldSelectvue_type_script_lang_ts_getAvailableOptions(givenAvailableValues, type, uiControlAttributes) {
  if (!givenAvailableValues) {
    return [];
  }

  var hasGroups = true;
  var availableValues = givenAvailableValues;

  if (!hasGroupedValues(availableValues)) {
    availableValues = {
      '': givenAvailableValues
    };
    hasGroups = false;
  }

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref, 2),
        group = _ref2[0],
        values = _ref2[1];

    Object.entries(values).forEach(function (_ref3) {
      var _ref4 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref3, 2),
          valueObjKey = _ref4[0],
          value = _ref4[1];

      if (value && _typeof(value) === 'object' && typeof value.key !== 'undefined') {
        flatValues.push(value);
        return;
      }

      var key = valueObjKey;

      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({
        group: hasGroups ? group : undefined,
        key: key,
        value: value
      });
    });
  }); // for selects w/ a placeholder, add an option to unset the select

  if (uiControlAttributes !== null && uiControlAttributes !== void 0 && uiControlAttributes.placeholder && !hasOption(flatValues, '')) {
    return [{
      key: '',
      value: ''
    }].concat(flatValues);
  }

  return flatValues;
}

function handleOldAngularJsValues(value) {
  if (typeof value === 'string') {
    return value.replace(/^string:/, '');
  }

  return value;
}

/* harmony default export */ var FieldSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    uiControlOptions: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    options: function options() {
      // if modelValue is empty, but there is no empty value allowed in availableOptions,
      // add one temporarily until something is set
      var availableOptions = this.availableOptions;

      if (availableOptions && !hasOption(availableOptions, '') && (typeof this.modelValue === 'undefined' || this.modelValue === null || this.modelValue === '')) {
        return [{
          key: '',
          value: this.modelValue,
          group: this.hasGroups ? '' : undefined
        }].concat(FieldSelectvue_type_script_lang_ts_toConsumableArray(availableOptions));
      }

      return availableOptions;
    },
    hasGroups: function hasGroups() {
      var availableOptions = this.availableOptions;
      return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== 'undefined';
    },
    groupedOptions: function groupedOptions() {
      var options = this.options;

      if (!this.hasGroups || !options) {
        return null;
      }

      var groups = {};
      options.forEach(function (entry) {
        var group = entry.group;
        groups[group] = groups[group] || [];
        groups[group].push(entry);
      });
      var result = Object.entries(groups);
      result.sort(function (lhs, rhs) {
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
    onChange: function onChange(event) {
      var _this$modelModifiers,
          _this = this;

      var element = event.target;
      var newValue;

      if (this.multiple) {
        newValue = Array.from(element.options).filter(function (e) {
          return e.selected;
        }).map(function (e) {
          return e.value;
        });
        newValue = newValue.map(function (x) {
          return handleOldAngularJsValues(x);
        });
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          _this.onModelValueChange(_this.modelValue);
        }
      };
      this.$emit('update:modelValue', emitEventData);
    },
    onModelValueChange: function onModelValueChange(newVal) {
      var _this2 = this;

      window.$(this.$refs.select).val(newVal);
      setTimeout(function () {
        var _this2$uiControlAttri;

        initMaterialSelect(_this2.$refs.select, newVal, (_this2$uiControlAttri = _this2.uiControlAttributes) === null || _this2$uiControlAttri === void 0 ? void 0 : _this2$uiControlAttri.placeholder, _this2.uiControlOptions, _this2.multiple);
      });
    }
  },
  watch: {
    modelValue: function modelValue(newVal) {
      this.onModelValueChange(newVal);
    },
    'uiControlAttributes.disabled': {
      handler: function handler(newVal, oldVal) {
        var _this3 = this;

        setTimeout(function () {
          if (newVal !== oldVal) {
            var _this3$uiControlAttri;

            initMaterialSelect(_this3.$refs.select, _this3.modelValue, (_this3$uiControlAttri = _this3.uiControlAttributes) === null || _this3$uiControlAttri === void 0 ? void 0 : _this3$uiControlAttri.placeholder, _this3.uiControlOptions, _this3.multiple);
          }
        });
      }
    },
    availableOptions: function availableOptions(newVal, oldVal) {
      var _this4 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          var _this4$uiControlAttri;

          initMaterialSelect(_this4.$refs.select, _this4.modelValue, (_this4$uiControlAttri = _this4.uiControlAttributes) === null || _this4$uiControlAttri === void 0 ? void 0 : _this4$uiControlAttri.placeholder, _this4.uiControlOptions, _this4.multiple);
        });
      }
    }
  },
  mounted: function mounted() {
    var _this5 = this;

    setTimeout(function () {
      var _this5$uiControlAttri;

      initMaterialSelect(_this5.$refs.select, _this5.modelValue, (_this5$uiControlAttri = _this5.uiControlAttributes) === null || _this5$uiControlAttri === void 0 ? void 0 : _this5$uiControlAttri.placeholder, _this5.uiControlOptions, _this5.multiple);
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue



FieldSelectvue_type_script_lang_ts.render = FieldSelectvue_type_template_id_2254b68e_render

/* harmony default export */ var FieldSelect = (FieldSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=054df803

var FieldSitevue_type_template_id_054df803_hoisted_1 = ["for", "innerHTML"];
var FieldSitevue_type_template_id_054df803_hoisted_2 = {
  class: "sites_autocomplete"
};
function FieldSitevue_type_template_id_054df803_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    class: "siteSelectorLabel",
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSitevue_type_template_id_054df803_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldSitevue_type_template_id_054df803_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    id: _ctx.name,
    "show-all-sites-item": _ctx.uiControlAttributes.showAllSitesItem || false,
    "switch-site-on-select": false,
    "show-selected-site": true,
    "only-sites-with-admin-access": _ctx.uiControlAttributes.onlySitesWithAdminAccess || false
  }, _ctx.uiControlAttributes), null, 16, ["model-value", "id", "show-all-sites-item", "only-sites-with-admin-access"])])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=054df803

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    SiteSelector: external_CoreHome_["SiteSelector"]
  },
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(newValue) {
      var _this$modelModifiers;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue



FieldSitevue_type_script_lang_ts.render = FieldSitevue_type_template_id_054df803_render

/* harmony default export */ var FieldSite = (FieldSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=b38e6a72

var FieldTextvue_type_template_id_b38e6a72_hoisted_1 = ["type", "id", "name", "value", "spellcheck"];
var FieldTextvue_type_template_id_b38e6a72_hoisted_2 = ["for", "innerHTML"];
function FieldTextvue_type_template_id_b38e6a72_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueText,
    spellcheck: _ctx.uiControl === 'password' ? false : null,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldTextvue_type_template_id_b38e6a72_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextvue_type_template_id_b38e6a72_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=b38e6a72

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    name: String,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object,
    uiControl: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    modelValueText: function modelValueText() {
      if (typeof this.modelValue === 'undefined' || this.modelValue === null) {
        return '';
      }

      return this.modelValue.toString();
    }
  },
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            // change to previous value if the parent component did not update the model value
            // (done manually because Vue will not notice if a value does NOT change)
            if (event.target.value !== _this.modelValueText) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue



FieldTextvue_type_script_lang_ts.render = FieldTextvue_type_template_id_b38e6a72_render

/* harmony default export */ var FieldText = (FieldTextvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=5c817a24

var FieldTextArrayvue_type_template_id_5c817a24_hoisted_1 = ["for", "innerHTML"];
var FieldTextArrayvue_type_template_id_5c817a24_hoisted_2 = ["type", "name", "value"];
function FieldTextArrayvue_type_template_id_5c817a24_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextArrayvue_type_template_id_5c817a24_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    name: _ctx.name,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    value: _ctx.concattedValues
  }, _ctx.uiControlAttributes), null, 16, FieldTextArrayvue_type_template_id_5c817a24_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=5c817a24

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControl: String,
    modelValue: Array,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  computed: {
    concattedValues: function concattedValues() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      }

      return (this.modelValue || []).join(', ');
    }
  },
  emits: ['update:modelValue'],
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var values = event.target.value.split(',').map(function (v) {
        return v.trim();
      });

      if (values.join(', ') !== this.concattedValues) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', values);
          return;
        }

        var emitEventData = {
          value: values,
          abort: function abort() {
            if (event.target.value !== _this.concattedValues) {
              event.target.value = _this.concattedValues;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue



FieldTextArrayvue_type_script_lang_ts.render = FieldTextArrayvue_type_template_id_5c817a24_render

/* harmony default export */ var FieldTextArray = (FieldTextArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=26601e85

var FieldTextareavue_type_template_id_26601e85_hoisted_1 = ["name", "id", "value"];
var FieldTextareavue_type_template_id_26601e85_hoisted_2 = ["for", "innerHTML"];
function FieldTextareavue_type_template_id_26601e85_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    id: _ctx.name,
    value: _ctx.modelValueText,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea",
    ref: "textarea"
  }), null, 16, FieldTextareavue_type_template_id_26601e85_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareavue_type_template_id_26601e85_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=26601e85

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextareavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    modelModifiers: Object,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (newValue !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            if (event.target.value !== _this.modelValue) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    modelValueText: function modelValueText() {
      return this.modelValue || '';
    }
  },
  watch: {
    modelValue: function modelValue() {
      var _this2 = this;

      setTimeout(function () {
        window.Materialize.textareaAutoResize(_this2.$refs.textarea);
        window.Materialize.updateTextFields();
      });
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue



FieldTextareavue_type_script_lang_ts.render = FieldTextareavue_type_template_id_26601e85_render

/* harmony default export */ var FieldTextarea = (FieldTextareavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=0b526a34

var FieldTextareaArrayvue_type_template_id_0b526a34_hoisted_1 = ["for", "innerHTML"];
var FieldTextareaArrayvue_type_template_id_0b526a34_hoisted_2 = ["name", "value"];
function FieldTextareaArrayvue_type_template_id_0b526a34_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareaArrayvue_type_template_id_0b526a34_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "textarea",
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    value: _ctx.concattedValue,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea"
  }), null, 16, FieldTextareaArrayvue_type_template_id_0b526a34_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=0b526a34

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
function FieldTextareaArrayvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FieldTextareaArrayvue_type_script_lang_ts_typeof(obj); }



var SEPARATOR = '\n';
/* harmony default export */ var FieldTextareaArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: [Array, String],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue: function concattedValue() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      } // Handle case when modelValues is like: {"0": "value0", "2": "value1"}


      if (FieldTextareaArrayvue_type_script_lang_ts_typeof(this.modelValue) === 'object') {
        return Object.values(this.modelValue).join(SEPARATOR);
      }

      try {
        return (this.modelValue || []).join(SEPARATOR);
      } catch (e) {
        // Prevent page breaking on unexpected modelValue type
        console.error(e);
        return '';
      }
    }
  },
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var value = event.target.value.split(SEPARATOR);

      if (value.join(SEPARATOR) !== this.concattedValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.concattedValue) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.concattedValue;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this2 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          if (_this2.$refs.textarea) {
            window.Materialize.textareaAutoResize(_this2.$refs.textarea);
          }

          window.Materialize.updateTextFields();
        });
      }
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      if (_this3.$refs.textarea) {
        window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      }

      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue



FieldTextareaArrayvue_type_script_lang_ts.render = FieldTextareaArrayvue_type_template_id_0b526a34_render

/* harmony default export */ var FieldTextareaArray = (FieldTextareaArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/utilities.ts
function utilities_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { utilities_typeof = function _typeof(obj) { return typeof obj; }; } else { utilities_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return utilities_typeof(obj); }

function utilities_slicedToArray(arr, i) { return utilities_arrayWithHoles(arr) || utilities_iterableToArrayLimit(arr, i) || utilities_unsupportedIterableToArray(arr, i) || utilities_nonIterableRest(); }

function utilities_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function utilities_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return utilities_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return utilities_arrayLikeToArray(o, minLen); }

function utilities_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function utilities_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function utilities_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

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

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = utilities_slicedToArray(_ref, 2),
        valueObjKey = _ref2[0],
        value = _ref2[1];

    if (value && utilities_typeof(value) === 'object' && typeof value.key !== 'undefined') {
      flatValues.push(value);
      return;
    }

    var key = valueObjKey;

    if (type === 'integer' && typeof valueObjKey === 'string') {
      key = parseInt(key, 10);
    }

    flatValues.push({
      key: key,
      value: value
    });
  });
  return flatValues;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
function FormFieldvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FormFieldvue_type_script_lang_ts_typeof(obj); }



















var TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
var CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
var CONTROL_TO_COMPONENT_MAP = {
  checkbox: 'FieldCheckbox',
  'expandable-select': 'FieldExpandableSelect',
  'field-array': 'FieldFieldArray',
  file: 'FieldFile',
  hidden: 'FieldHidden',
  multiselect: 'FieldSelect',
  multituple: 'FieldMultituple',
  number: 'FieldNumber',
  radio: 'FieldRadio',
  select: 'FieldSelect',
  site: 'FieldSite',
  text: 'FieldText',
  textarea: 'FieldTextarea'
};
var CONTROL_TO_AVAILABLE_OPTION_PROCESSOR = {
  FieldSelect: FieldSelectvue_type_script_lang_ts_getAvailableOptions,
  FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
  FieldRadio: processCheckboxAndRadioAvailableValues,
  FieldExpandableSelect: getAvailableOptions
};
/* harmony default export */ var FormFieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    formField: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  components: {
    FieldCheckbox: FieldCheckbox,
    FieldCheckboxArray: FieldCheckboxArray,
    FieldExpandableSelect: FieldExpandableSelect,
    FieldFieldArray: FieldFieldArray,
    FieldFile: FieldFile,
    FieldHidden: FieldHidden,
    FieldMultituple: FieldMultituple,
    FieldNumber: FieldNumber,
    FieldRadio: FieldRadio,
    FieldSelect: FieldSelect,
    FieldSite: FieldSite,
    FieldText: FieldText,
    FieldTextArray: FieldTextArray,
    FieldTextarea: FieldTextarea,
    FieldTextareaArray: FieldTextareaArray
  },
  setup: function setup(props) {
    var inlineHelpNode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    var setInlineHelp = function setInlineHelp(newVal) {
      var toAppend;

      if (!newVal || !inlineHelpNode.value || typeof newVal.render === 'function') {
        return;
      }

      if (typeof newVal === 'string') {
        if (newVal.indexOf('#') === 0) {
          toAppend = window.$(newVal);
        } else {
          toAppend = window.vueSanitize(newVal);
        }
      } else {
        toAppend = newVal;
      }

      window.$(inlineHelpNode.value).html('').append(toAppend);
    };

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.formField.inlineHelp;
    }, setInlineHelp);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      setInlineHelp(props.formField.inlineHelp);
    });
    return {
      inlineHelp: inlineHelpNode
    };
  },
  computed: {
    inlineHelpComponent: function inlineHelpComponent() {
      var formField = this.formField;
      var inlineHelpRecord = formField.inlineHelp;

      if (inlineHelpRecord && typeof inlineHelpRecord.render === 'function') {
        return formField.inlineHelp;
      }

      return undefined;
    },
    inlineHelpBind: function inlineHelpBind() {
      return this.inlineHelpComponent ? this.formField.inlineHelpBind : undefined;
    },
    childComponent: function childComponent() {
      var formField = this.formField;

      if (formField.component) {
        var component = formField.component;

        if (formField.component.plugin) {
          var _formField$component = formField.component,
              plugin = _formField$component.plugin,
              name = _formField$component.name;

          if (!plugin || !name) {
            throw new Error('Invalid component property given to FormField directive, must be ' + '{plugin: \'...\',name: \'...\'}');
          }

          component = Object(external_CoreHome_["useExternalPluginComponent"])(plugin, name);
        }

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(component);
      }

      var uiControl = formField.uiControl;
      var control = CONTROL_TO_COMPONENT_MAP[uiControl];

      if (TEXT_CONTROLS.indexOf(uiControl) !== -1) {
        control = 'FieldText'; // we use same template for text and password both
      }

      if (this.formField.type === 'array' && CONTROLS_SUPPORTING_ARRAY.indexOf(uiControl) !== -1) {
        control = "".concat(control, "Array");
      }

      return control;
    },
    extraChildComponentParams: function extraChildComponentParams() {
      if (this.formField.uiControl === 'multiselect') {
        return {
          multiple: true
        };
      }

      return {};
    },
    showFormHelp: function showFormHelp() {
      return this.formField.description || this.formField.inlineHelp || this.showDefaultValue || this.hasInlineHelpSlot;
    },
    showDefaultValue: function showDefaultValue() {
      return this.defaultValuePretty && this.formField.uiControl !== 'checkbox' && this.formField.uiControl !== 'radio';
    },
    processedModelValue: function processedModelValue() {
      var field = this.formField; // handle boolean field types

      if (field.type === 'boolean') {
        var valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0'; // for checkboxes, the value MUST be either true or false

        if (field.uiControl === 'checkbox') {
          return valueIsTruthy;
        }

        if (field.uiControl === 'radio') {
          return valueIsTruthy ? '1' : '0';
        }
      }

      return this.modelValue;
    },
    defaultValue: function defaultValue() {
      var defaultValue = this.formField.defaultValue;

      if (Array.isArray(defaultValue)) {
        return defaultValue.join(',');
      }

      return defaultValue;
    },
    availableOptions: function availableOptions() {
      var childComponent = this.childComponent;

      if (typeof childComponent !== 'string') {
        return null;
      }

      var formField = this.formField;

      if (!formField.availableValues || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]) {
        return null;
      }

      return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](formField.availableValues, formField.type, formField.uiControlAttributes);
    },
    defaultValuePretty: function defaultValuePretty() {
      var formField = this.formField;
      var defaultValue = formField.defaultValue;
      var availableOptions = this.availableOptions;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        var defaultParsed = null;

        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {// invalid JSON
        }

        if (defaultParsed !== null && FormFieldvue_type_script_lang_ts_typeof(defaultParsed) === 'object') {
          return '';
        }
      }

      if (!Array.isArray(availableOptions)) {
        if (Array.isArray(defaultValue)) {
          return '';
        }

        return defaultValue ? "".concat(defaultValue) : '';
      }

      var prettyValues = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      (availableOptions || []).forEach(function (value) {
        if (typeof value.value !== 'undefined' && defaultValue.indexOf(value.key) !== -1) {
          prettyValues.push(value.value);
        }
      });
      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated: function defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    },
    hasInlineHelpSlot: function hasInlineHelpSlot() {
      var _inlineHelpSlot$, _inlineHelpSlot$$chil;

      if (!this.$slots['inline-help']) {
        return false;
      }

      var inlineHelpSlot = this.$slots['inline-help']();
      return !!(inlineHelpSlot !== null && inlineHelpSlot !== void 0 && (_inlineHelpSlot$ = inlineHelpSlot[0]) !== null && _inlineHelpSlot$ !== void 0 && (_inlineHelpSlot$$chil = _inlineHelpSlot$.children) !== null && _inlineHelpSlot$$chil !== void 0 && _inlineHelpSlot$$chil.length);
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue



FormFieldvue_type_script_lang_ts.render = render

/* harmony default export */ var FormField = (FormFieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=5f883444

function Fieldvue_type_template_id_5f883444_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FormField, {
    "form-field": _ctx.field,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    "model-modifiers": _ctx.modelModifiers
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")];
    }),
    _: 3
  }, 8, ["form-field", "model-value", "model-modifiers"]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=5f883444

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts


var UI_CONTROLS_TO_TYPE = {
  multiselect: 'array',
  checkbox: 'boolean',
  site: 'object',
  number: 'integer'
};
/* harmony default export */ var Fieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uicontrol: String,
    name: String,
    defaultValue: null,
    options: [Object, Array],
    description: String,
    introduction: String,
    title: String,
    inlineHelp: [String, Object],
    inlineHelpBind: Object,
    disabled: Boolean,
    uiControlAttributes: {
      type: Object,
      default: function _default() {
        return {};
      }
    },
    uiControlOptions: {
      type: Object,
      default: function _default() {
        return {};
      }
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
    component: null
  },
  emits: ['update:modelValue'],
  components: {
    FormField: FormField
  },
  computed: {
    type: function type() {
      if (this.varType) {
        return this.varType;
      }

      var uicontrol = this.uicontrol;

      if (uicontrol && UI_CONTROLS_TO_TYPE[uicontrol]) {
        return UI_CONTROLS_TO_TYPE[uicontrol];
      }

      return 'string';
    },
    field: function field() {
      return {
        uiControl: this.uicontrol,
        type: this.type,
        name: this.name,
        defaultValue: this.defaultValue,
        availableValues: this.options,
        description: this.description,
        introduction: this.introduction,
        inlineHelp: this.inlineHelp,
        inlineHelpBind: this.inlineHelpBind,
        title: this.title,
        component: this.component,
        uiControlAttributes: Object.assign(Object.assign({}, this.uiControlAttributes), {}, {
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
        uiControlOptions: this.uiControlOptions
      };
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue



Fieldvue_type_script_lang_ts.render = Fieldvue_type_template_id_5f883444_render

/* harmony default export */ var Field = (Fieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=template&id=601e4fc6

var PluginSettingsvue_type_template_id_601e4fc6_hoisted_1 = {
  class: "pluginSettings",
  ref: "root"
};
var PluginSettingsvue_type_template_id_601e4fc6_hoisted_2 = ["id"];
var PluginSettingsvue_type_template_id_601e4fc6_hoisted_3 = {
  class: "card-content"
};
var PluginSettingsvue_type_template_id_601e4fc6_hoisted_4 = ["id"];
var PluginSettingsvue_type_template_id_601e4fc6_hoisted_5 = ["onClick", "disabled", "value"];
function PluginSettingsvue_type_template_id_601e4fc6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_GroupedSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSettings");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginSettingsvue_type_template_id_601e4fc6_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.settingsPerPlugin, function (settings) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "card",
      id: "".concat(settings.pluginName, "PluginSettings"),
      key: "".concat(settings.pluginName, "PluginSettings")
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginSettingsvue_type_template_id_601e4fc6_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
      class: "card-title",
      id: settings.pluginName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(settings.title), 9, PluginSettingsvue_type_template_id_601e4fc6_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSettings, {
      "group-name": settings.pluginName,
      settings: settings.settings,
      "all-setting-values": _ctx.settingValues,
      onChange: function onChange($event) {
        return _ctx.settingValues["".concat(settings.pluginName, ".").concat($event.name)] = $event.value;
      }
    }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      onClick: function onClick($event) {
        return _ctx.saveSetting(settings.pluginName);
      },
      disabled: _ctx.isLoading,
      class: "pluginsSettingsSubmit btn",
      value: _ctx.translate('General_Save')
    }, null, 8, PluginSettingsvue_type_template_id_601e4fc6_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading || _ctx.isSaving[settings.pluginName]
    }, null, 8, ["loading"])])], 8, PluginSettingsvue_type_template_id_601e4fc6_hoisted_2);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showPasswordConfirmModal = $event;
    }),
    onConfirmed: _ctx.confirmPassword
  }, null, 8, ["modelValue", "onConfirmed"])], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=template&id=601e4fc6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=template&id=566a93cc

function GroupedSettingsvue_type_template_id_566a93cc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_GroupedSetting = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSetting");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.settings, function (setting) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: "".concat(_ctx.groupPrefix).concat(setting.name)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSetting, {
      "model-value": _ctx.allSettingValues["".concat(_ctx.groupPrefix).concat(setting.name)],
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.$emit('change', {
          name: setting.name,
          value: $event
        });
      },
      setting: setting,
      "condition-values": _ctx.settingValues
    }, null, 8, ["model-value", "onUpdate:modelValue", "setting", "condition-values"])]);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=template&id=566a93cc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=template&id=10276746

function GroupedSettingvue_type_template_id_10276746_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FormField, {
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.changeValue($event);
    }),
    "form-field": _ctx.setting
  }, null, 8, ["model-value", "form-field"])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showField]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=template&id=10276746

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=script&lang=ts



/* harmony default export */ var GroupedSettingvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    FormField: FormField
  },
  emits: ['update:modelValue'],
  computed: {
    showField: function showField() {
      var condition = this.setting.condition;

      if (!condition) {
        return true;
      } // math.js does not currently support &&/||/! (https://github.com/josdejong/mathjs/issues/844)


      condition = condition.replace(/&&/g, ' and ');
      condition = condition.replace(/\|\|/g, ' or ');
      condition = condition.replace(/!/g, ' not ');

      try {
        return src_expressions.evaluate(condition, this.conditionValues);
      } catch (e) {
        console.log("failed to parse setting condition '".concat(condition, "': ").concat(e.message));
        console.log(this.conditionValues);
        return false;
      }
    }
  },
  methods: {
    changeValue: function changeValue(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue



GroupedSettingvue_type_script_lang_ts.render = GroupedSettingvue_type_template_id_10276746_render

/* harmony default export */ var GroupedSetting = (GroupedSettingvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=script&lang=ts
function GroupedSettingsvue_type_script_lang_ts_slicedToArray(arr, i) { return GroupedSettingsvue_type_script_lang_ts_arrayWithHoles(arr) || GroupedSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || GroupedSettingsvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || GroupedSettingsvue_type_script_lang_ts_nonIterableRest(); }

function GroupedSettingsvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function GroupedSettingsvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function GroupedSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function GroupedSettingsvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }



/* harmony default export */ var GroupedSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
  emits: ['change'],
  components: {
    GroupedSetting: GroupedSetting
  },
  computed: {
    settingValues: function settingValues() {
      var _this = this;

      var entries = Object.entries(this.allSettingValues).filter(function (_ref) {
        var _ref2 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_ref, 1),
            key = _ref2[0];

        if (_this.groupName) {
          var _key$split = key.split('.'),
              _key$split2 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_key$split, 1),
              groupName = _key$split2[0];

          if (groupName !== _this.groupName) {
            return false;
          }
        }

        return true;
      }).map(function (_ref3) {
        var _ref4 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_ref3, 2),
            key = _ref4[0],
            value = _ref4[1];

        return _this.groupName ? [key.split('.')[1], value] : [key, value];
      });
      return Object.fromEntries(entries);
    },
    groupPrefix: function groupPrefix() {
      if (!this.groupName) {
        return '';
      }

      return "".concat(this.groupName, ".");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue



GroupedSettingsvue_type_script_lang_ts.render = GroupedSettingsvue_type_template_id_566a93cc_render

/* harmony default export */ var GroupedSettings = (GroupedSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=template&id=667f93f0

var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_1 = {
  class: "confirm-password-modal modal",
  ref: "root"
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_2 = {
  class: "modal-content"
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_3 = {
  class: "modal-text"
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_4 = {
  ref: "content"
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_5 = {
  key: 0
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_6 = {
  key: 1
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_7 = {
  key: 2
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_8 = {
  class: "modal-footer"
};
var PasswordConfirmationvue_type_template_id_667f93f0_hoisted_9 = ["disabled"];
function PasswordConfirmationvue_type_template_id_667f93f0_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512), !_ctx.requiresPasswordConfirmation && !_ctx.slotHasContent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmThisChange')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.requiresPasswordConfirmation && !_ctx.slotHasContent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.requiresPasswordConfirmation && _ctx.slotHasContent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.passwordConfirmation,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.passwordConfirmation = $event;
    }),
    uicontrol: 'password',
    disabled: !_ctx.requiresPasswordConfirmation ? 'disabled' : undefined,
    name: 'currentUserPassword',
    autocomplete: 'off',
    "full-width": true,
    title: _ctx.translate('UsersManager_YourCurrentPassword')
  }, null, 8, ["modelValue", "disabled", "title"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.requiresPasswordConfirmation]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_667f93f0_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    disabled: _ctx.requiresPasswordConfirmation && !_ctx.passwordConfirmation ? 'disabled' : undefined,
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onClickConfirm($event);
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Confirm')), 9, PasswordConfirmationvue_type_template_id_667f93f0_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no btn-flat",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onClickCancel($event);
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)])], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=template&id=667f93f0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=script&lang=ts



var _window = window,
    $ = _window.$;
/* harmony default export */ var PasswordConfirmationvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /**
     * Whether the confirmation is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true
    }
  },
  data: function data() {
    return {
      passwordConfirmation: '',
      slotHasContent: true
    };
  },
  emits: ['confirmed', 'aborted', 'update:modelValue'],
  components: {
    Field: Field
  },
  activated: function activated() {
    this.$emit('update:modelValue', false);
  },
  methods: {
    onClickConfirm: function onClickConfirm(event) {
      event.preventDefault();
      this.$emit('confirmed', this.passwordConfirmation);
      this.passwordConfirmation = '';
    },
    onClickCancel: function onClickCancel(event) {
      event.preventDefault();
      this.$emit('aborted');
      this.passwordConfirmation = '';
    },
    showPasswordConfirmModal: function showPasswordConfirmModal() {
      var _this = this;

      this.slotHasContent = !this.$refs.content.matches(':empty');
      var root = this.$refs.root;
      var $root = $(root);

      var onEnter = function onEnter(event) {
        var keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode === 13) {
          $root.modal('close');

          _this.$emit('confirmed', _this.passwordConfirmation);

          _this.passwordConfirmation = '';
        }
      };

      $root.modal({
        dismissible: false,
        onOpenEnd: function onOpenEnd() {
          var passwordField = '.modal.open #currentUserPassword';
          $(passwordField).focus();
          $(passwordField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: function onCloseEnd() {
          _this.$emit('update:modelValue', false);
        }
      }).modal('open');
    }
  },
  computed: {
    requiresPasswordConfirmation: function requiresPasswordConfirmation() {
      return !!external_CoreHome_["Matomo"].requiresPasswordConfirmation;
    }
  },
  watch: {
    modelValue: function modelValue(newValue) {
      if (newValue) {
        this.showPasswordConfirmModal();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue



PasswordConfirmationvue_type_script_lang_ts.render = PasswordConfirmationvue_type_template_id_667f93f0_render

/* harmony default export */ var PasswordConfirmation = (PasswordConfirmationvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=script&lang=ts
function PluginSettingsvue_type_script_lang_ts_slicedToArray(arr, i) { return PluginSettingsvue_type_script_lang_ts_arrayWithHoles(arr) || PluginSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || PluginSettingsvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || PluginSettingsvue_type_script_lang_ts_nonIterableRest(); }

function PluginSettingsvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function PluginSettingsvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function PluginSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function PluginSettingsvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





var PluginSettingsvue_type_script_lang_ts_window = window,
    PluginSettingsvue_type_script_lang_ts_$ = PluginSettingsvue_type_script_lang_ts_window.$;
/* harmony default export */ var PluginSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    mode: String
  },
  components: {
    PasswordConfirmation: PasswordConfirmation,
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    GroupedSettings: GroupedSettings
  },
  data: function data() {
    return {
      isLoading: true,
      isSaving: {},
      showPasswordConfirmModal: false,
      settingsToSave: null,
      settingsPerPlugin: [],
      settingValues: {}
    };
  },
  created: function created() {
    var _this = this;

    external_CoreHome_["AjaxHelper"].fetch({
      method: this.apiMethod
    }).then(function (settingsPerPlugin) {
      _this.isLoading = false;
      _this.settingsPerPlugin = settingsPerPlugin;
      settingsPerPlugin.forEach(function (settings) {
        settings.settings.forEach(function (setting) {
          _this.settingValues["".concat(settings.pluginName, ".").concat(setting.name)] = setting.value;
        });
      });
      Object(external_CoreHome_["scrollToAnchorInUrl"])();

      _this.addSectionsToTableOfContents();
    }).catch(function () {
      _this.isLoading = false;
    });
  },
  computed: {
    apiMethod: function apiMethod() {
      return this.mode === 'admin' ? 'CorePluginsAdmin.getSystemSettings' : 'CorePluginsAdmin.getUserSettings';
    },
    saveApiMethod: function saveApiMethod() {
      return this.mode === 'admin' ? 'CorePluginsAdmin.setSystemSettings' : 'CorePluginsAdmin.setUserSettings';
    }
  },
  methods: {
    addSectionsToTableOfContents: function addSectionsToTableOfContents() {
      var $toc = PluginSettingsvue_type_script_lang_ts_$('#generalSettingsTOC');

      if (!$toc.length) {
        return;
      }

      var settingsPerPlugin = this.settingsPerPlugin;
      settingsPerPlugin.forEach(function (settingsForPlugin) {
        var pluginName = settingsForPlugin.pluginName,
            settings = settingsForPlugin.settings;

        if (!pluginName) {
          return;
        }

        if (pluginName === 'CoreAdminHome' && settings) {
          settings.filter(function (s) {
            return s.introduction;
          }).forEach(function (s) {
            $toc.append("<a href=\"#/".concat(pluginName, "PluginSettings\">").concat(s.introduction, "</a> "));
          });
        } else {
          $toc.append("<a href=\"#/".concat(pluginName, "\">").concat(pluginName.replace(/([A-Z])/g, ' $1').trim(), "</a> "));
        }
      });
    },
    confirmPassword: function confirmPassword(password) {
      this.showPasswordConfirmModal = false;
      this.save(this.settingsToSave, password);
    },
    saveSetting: function saveSetting(requestedPlugin) {
      if (this.mode === 'admin') {
        this.settingsToSave = requestedPlugin;
        this.showPasswordConfirmModal = true;
      } else {
        this.save(requestedPlugin);
      }
    },
    save: function save(requestedPlugin, password) {
      var _this2 = this;

      var saveApiMethod = this.saveApiMethod;
      this.isSaving[requestedPlugin] = true;
      var settingValuesPayload = this.getValuesForPlugin(requestedPlugin);
      external_CoreHome_["AjaxHelper"].post({
        method: saveApiMethod
      }, {
        settingValues: settingValuesPayload,
        passwordConfirmation: password
      }).then(function () {
        _this2.isSaving[requestedPlugin] = false;
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_PluginSettingsSaveSuccess'),
          id: 'generalSettings',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).catch(function () {
        _this2.isSaving[requestedPlugin] = false;
      });
      this.settingsToSave = null;
    },
    getValuesForPlugin: function getValuesForPlugin(requestedPlugin) {
      var values = {};

      if (!values[requestedPlugin]) {
        values[requestedPlugin] = [];
      }

      Object.entries(this.settingValues).forEach(function (_ref) {
        var _ref2 = PluginSettingsvue_type_script_lang_ts_slicedToArray(_ref, 2),
            key = _ref2[0],
            value = _ref2[1];

        var _key$split = key.split('.'),
            _key$split2 = PluginSettingsvue_type_script_lang_ts_slicedToArray(_key$split, 2),
            pluginName = _key$split2[0],
            settingName = _key$split2[1];

        if (pluginName !== requestedPlugin) {
          return;
        }

        var postValue = value;

        if (postValue === false) {
          postValue = '0';
        } else if (postValue === true) {
          postValue = '1';
        }

        if (Array.isArray(postValue) && postValue.length === 0) {
          postValue = '__empty__';
        }

        values[pluginName].push({
          name: settingName,
          value: postValue
        });
      });
      return values;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue



PluginSettingsvue_type_script_lang_ts.render = PluginSettingsvue_type_template_id_601e4fc6_render

/* harmony default export */ var PluginSettings = (PluginSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginFilter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var PluginFilter_window = window,
    PluginFilter_$ = PluginFilter_window.$;

function getCurrentFilterOrigin(element) {
  return element.find('.origin a.active').data('filter-origin');
}

function getCurrentFilterStatus(element) {
  return element.find('.status a.active').data('filter-status');
}

function getMatchingNodes(filterOrigin, filterStatus) {
  var query = '#plugins tr';

  if (filterOrigin === 'all') {
    query += '[data-filter-origin]';
  } else {
    query += "[data-filter-origin=".concat(filterOrigin, "]");
  }

  if (filterStatus === 'all') {
    query += '[data-filter-status]';
  } else {
    query += "[data-filter-status=".concat(filterStatus, "]");
  }

  return PluginFilter_$(query);
}

function updateNumberOfMatchingPluginsInFilter(element, selectorFilterToUpdate, filterOrigin, filterStatus) {
  var numMatchingNodes = getMatchingNodes(filterOrigin, filterStatus).length;
  var updatedCounterText = " (".concat(numMatchingNodes, ")");
  element.find("".concat(selectorFilterToUpdate, " .counter")).text(updatedCounterText);
}

function updateAllNumbersOfMatchingPluginsInFilter(element) {
  var filterOrigin = getCurrentFilterOrigin(element);
  var filterStatus = getCurrentFilterStatus(element);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="all"]', filterOrigin, 'all');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="active"]', filterOrigin, 'active');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="inactive"]', filterOrigin, 'inactive');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="all"]', 'all', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="core"]', 'core', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="official"]', 'official', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="thirdparty"]', 'thirdparty', filterStatus);
}

function filterPlugins(element) {
  var filterOrigin = getCurrentFilterOrigin(element);
  var filterStatus = getCurrentFilterStatus(element);
  var $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);
  PluginFilter_$('#plugins tr[data-filter-origin][data-filter-status]').css('display', 'none');
  $nodesToEnable.css('display', 'table-row');
  updateAllNumbersOfMatchingPluginsInFilter(element);
}

function onClickStatus(element, event) {
  event.preventDefault();
  PluginFilter_$(event.target).siblings().removeClass('active');
  PluginFilter_$(event.target).addClass('active');
  filterPlugins(element);
}

function onClickOrigin(element, event) {
  event.preventDefault();
  PluginFilter_$(event.target).siblings().removeClass('active');
  PluginFilter_$(event.target).addClass('active');
  filterPlugins(element);
}

/* harmony default export */ var PluginFilter = ({
  mounted: function mounted(el) {
    setTimeout(function () {
      updateAllNumbersOfMatchingPluginsInFilter(PluginFilter_$(el));
      PluginFilter_$(el).find('.status').on('click', 'a', onClickStatus.bind(null, PluginFilter_$(el)));
      PluginFilter_$(el).find('.origin').on('click', 'a', onClickOrigin.bind(null, PluginFilter_$(el)));
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginManagement.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var PluginManagement_window = window,
    PluginManagement_$ = PluginManagement_window.$;

function onClickUninstall(binding, event) {
  event.preventDefault();
  var link = PluginManagement_$(event.target).attr('href');
  var pluginName = PluginManagement_$(event.target).attr('data-plugin-name');

  if (!link || !pluginName) {
    return;
  }

  if (!binding.value.uninstallConfirmMessage) {
    binding.value.uninstallConfirmMessage = PluginManagement_$('#uninstallPluginConfirm').text();
  }

  var messageToDisplay = (binding.value.uninstallConfirmMessage || '').replace('%s', pluginName);
  PluginManagement_$('#uninstallPluginConfirm').text(messageToDisplay);
  external_CoreHome_["Matomo"].helper.modalConfirm('#confirmUninstallPlugin', {
    yes: function yes() {
      window.location.href = link;
    }
  });
}

function onDonateLinkClick(event) {
  event.preventDefault();
  var overlayId = PluginManagement_$(event.target).data('overlay-id');
  external_CoreHome_["Matomo"].helper.modalConfirm("#".concat(overlayId), {});
}

/* harmony default export */ var PluginManagement = ({
  mounted: function mounted(el, binding) {
    setTimeout(function () {
      binding.value.uninstallConfirmMessage = '';
      PluginManagement_$(el).find('.uninstall').click(onClickUninstall.bind(null, binding));
      PluginManagement_$(el).find('.plugin-donation-link').click(onDonateLinkClick);
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginUpload.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var PluginUpload_window = window,
    PluginUpload_$ = PluginUpload_window.$;

function onUploadPlugin(event) {
  event.preventDefault();
  external_CoreHome_["Matomo"].helper.modalConfirm('#installPluginByUpload', {});
}

function onSubmitPlugin(event) {
  var $zipFile = PluginUpload_$('[name=pluginZip]');
  var fileName = $zipFile.val();

  if (!fileName || fileName.slice(-4) !== '.zip') {
    event.preventDefault(); // eslint-disable-next-line no-alert

    alert(Object(external_CoreHome_["translate"])('CorePluginsAdmin_NoZipFileSelected'));
  } else if ($zipFile.data('maxSize') > 0 && $zipFile[0].files[0].size > $zipFile.data('maxSize') * 1048576) {
    event.preventDefault(); // eslint-disable-next-line no-alert

    alert(Object(external_CoreHome_["translate"])('CorePluginsAdmin_FileExceedsUploadLimit'));
  }
}

/* harmony default export */ var PluginUpload = ({
  mounted: function mounted() {
    setTimeout(function () {
      PluginUpload_$('.uploadPlugin').click(onUploadPlugin);
      PluginUpload_$('#uploadPluginForm').submit(onSubmitPlugin);
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=template&id=866b0298

var SaveButtonvue_type_template_id_866b0298_hoisted_1 = {
  class: "matomo-save-button",
  style: {
    "display": "inline-block"
  }
};
var SaveButtonvue_type_template_id_866b0298_hoisted_2 = ["disabled", "value"];
function SaveButtonvue_type_template_id_866b0298_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveButtonvue_type_template_id_866b0298_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onConfirm($event);
    }),
    disabled: _ctx.saving || _ctx.disabled,
    class: "btn",
    value: _ctx.value ? _ctx.value : _ctx.translate('General_Save')
  }, null, 8, SaveButtonvue_type_template_id_866b0298_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.saving
  }, null, 8, ["loading"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=template&id=866b0298

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=script&lang=ts


/* harmony default export */ var SaveButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    saving: Boolean,
    value: String,
    disabled: Boolean
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  emits: ['confirm'],
  methods: {
    onConfirm: function onConfirm(event) {
      this.$emit('confirm', event);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue



SaveButtonvue_type_script_lang_ts.render = SaveButtonvue_type_template_id_866b0298_render

/* harmony default export */ var SaveButton = (SaveButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Form/Form.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var Form_window = window,
    Form_$ = Form_window.$;
/* harmony default export */ var Form = ({
  mounted: function mounted(el) {
    setTimeout(function () {
      Form_$(el).find('input[type=text]').keypress(function (e) {
        var key = e.keyCode || e.which;

        if (key === 13) {
          Form_$(el).find('.matomo-save-button input').triggerHandler('click');
        }
      });
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsIntro/PluginsIntro.vue?vue&type=template&id=69670e6c

var PluginsIntrovue_type_template_id_69670e6c_hoisted_1 = ["innerHTML"];
var PluginsIntrovue_type_template_id_69670e6c_hoisted_2 = {
  key: 1,
  style: {
    "margin-right": "3.5px"
  }
};

var PluginsIntrovue_type_template_id_69670e6c_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var PluginsIntrovue_type_template_id_69670e6c_hoisted_4 = ["innerHTML"];
function PluginsIntrovue_type_template_id_69670e6c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_PluginsManagement')), 1)];
    }),
    _: 1
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_PluginsExtendPiwik')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_OncePluginIsInstalledYouMayActivateHere')) + " ", 1), _ctx.isMarketplaceEnabled || _ctx.isPluginUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.teaserExtendMatomoByPluginText),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, PluginsIntrovue_type_template_id_69670e6c_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginsIntrovue_type_template_id_69670e6c_hoisted_2, [PluginsIntrovue_type_template_id_69670e6c_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_DoMoreContactPiwikAdmins')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.changeLookByManageThemesText)
  }, null, 8, PluginsIntrovue_type_template_id_69670e6c_hoisted_4)])], 512), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsIntro/PluginsIntro.vue?vue&type=template&id=69670e6c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=template&id=cc5c17d4

var InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_1 = {
  key: 0
};
var InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_2 = ["disabled"];
var InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_3 = {
  class: "ui-confirm",
  id: "installAllPaidPluginsAtOnce",
  ref: "installAllPaidPluginsAtOnce"
};
var InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_4 = ["data-href", "value"];
var InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_5 = ["value"];
function InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");

  return _ctx.paidPluginsToInstallAtOnce.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn installAllPaidPluginsAtOnceButton",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.onInstallAllPaidPlugins();
    }, ["prevent"])),
    disabled: _ctx.disabled || _ctx.loading
  }, [_ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MatomoLoader, {
    key: 0
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallPurchasedPlugins')), 1)], 8, InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallAllPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallThesePlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.paidPluginsToInstallAtOnce, function (pluginDisplayName) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: pluginDisplayName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(pluginDisplayName), 1);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "install",
    type: "button",
    "data-href": _ctx.installAllPaidPluginsLink,
    value: _ctx.translate('Marketplace_InstallAllPurchasedPluginsAction', _ctx.paidPluginsToInstallAtOnce.length)
  }, null, 8, InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "cancel",
    type: "button",
    value: _ctx.translate('General_Cancel')
  }, null, 8, InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_hoisted_5)])], 512)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=template&id=cc5c17d4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=script&lang=ts


/* harmony default export */ var InstallAllPaidPluginsButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoLoader: external_CoreHome_["MatomoLoader"]
  },
  props: {
    disabled: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  data: function data() {
    return {
      paidPluginsToInstallAtOnce: [],
      installNonce: '',
      loading: false
    };
  },
  created: function created() {
    this.fetchPluginsToInstallAtOnce();
  },
  watch: {
    disabled: function disabled(newValue, oldValue) {
      if (newValue === false && oldValue === true) {
        this.fetchPluginsToInstallAtOnce();
      }
    }
  },
  methods: {
    onInstallAllPaidPlugins: function onInstallAllPaidPlugins() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce);
    },
    fetchPluginsToInstallAtOnce: function fetchPluginsToInstallAtOnce() {
      var _this = this;

      this.loading = true;

      if (external_CoreHome_["Matomo"].hasSuperUserAccess) {
        external_CoreHome_["AjaxHelper"].fetch({
          module: 'Marketplace',
          action: 'getPaidPluginsToInstallAtOnceParams'
        }).then(function (response) {
          if (response) {
            var _response$paidPlugins, _response$installAllP;

            _this.paidPluginsToInstallAtOnce = (_response$paidPlugins = response.paidPluginsToInstallAtOnce) !== null && _response$paidPlugins !== void 0 ? _response$paidPlugins : [];
            _this.installNonce = (_response$installAllP = response.installAllPluginsNonce) !== null && _response$installAllP !== void 0 ? _response$installAllP : '';
          }

          _this.loading = false;
        });
      }
    }
  },
  computed: {
    installAllPaidPluginsLink: function installAllPaidPluginsLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'installAllPaidPlugins',
        nonce: this.installNonce
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue



InstallAllPaidPluginsButtonvue_type_script_lang_ts.render = InstallAllPaidPluginsButtonvue_type_template_id_cc5c17d4_render

/* harmony default export */ var InstallAllPaidPluginsButton = (InstallAllPaidPluginsButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsIntro/PluginsIntro.vue?vue&type=script&lang=ts



/* harmony default export */ var PluginsIntrovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isMarketplaceEnabled: Boolean,
    isPluginUploadEnabled: Boolean,
    isPluginsAdminEnabled: Boolean
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    InstallAllPaidPluginsButton: InstallAllPaidPluginsButton
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  computed: {
    teaserExtendMatomoByPluginText: function teaserExtendMatomoByPluginText() {
      var link = "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview',
        sort: null,
        activated: null
      })));
      return Object(external_CoreHome_["translate"])('CorePluginsAdmin_TeaserExtendPiwikByPlugin', "<a href=\"".concat(link, "\">"), '</a>', '<a href="#" class="uploadPlugin">', '</a>');
    },
    changeLookByManageThemesText: function changeLookByManageThemesText() {
      var link = "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        action: 'themes',
        activated: null
      })));
      return Object(external_CoreHome_["translate"])('CorePluginsAdmin_ChangeLookByManageThemes', "<a href=\"".concat(link, "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsIntro/PluginsIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsIntro/PluginsIntro.vue



PluginsIntrovue_type_script_lang_ts.render = PluginsIntrovue_type_template_id_69670e6c_render

/* harmony default export */ var PluginsIntro = (PluginsIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/ThemesIntro/ThemesIntro.vue?vue&type=template&id=355bc09e

var ThemesIntrovue_type_template_id_355bc09e_hoisted_1 = ["innerHTML"];
var ThemesIntrovue_type_template_id_355bc09e_hoisted_2 = {
  key: 1
};

var ThemesIntrovue_type_template_id_355bc09e_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ThemesIntrovue_type_template_id_355bc09e_hoisted_4 = {
  key: 2
};

var ThemesIntrovue_type_template_id_355bc09e_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

function ThemesIntrovue_type_template_id_355bc09e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ThemesManagement')), 1)];
    }),
    _: 1
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ThemesDescription')) + " ", 1), _ctx.isMarketplaceEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.teaserExtendByThemeText)
  }, null, 8, ThemesIntrovue_type_template_id_355bc09e_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.otherUsersCount > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ThemesIntrovue_type_template_id_355bc09e_hoisted_2, [ThemesIntrovue_type_template_id_355bc09e_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_InfoThemeIsUsedByOtherUsersAsWell', _ctx.otherUsersCount, _ctx.themeEnabled)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ThemesIntrovue_type_template_id_355bc09e_hoisted_4, [ThemesIntrovue_type_template_id_355bc09e_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_DoMoreContactPiwikAdmins')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 512)), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/ThemesIntro/ThemesIntro.vue?vue&type=template&id=355bc09e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/ThemesIntro/ThemesIntro.vue?vue&type=script&lang=ts


/* harmony default export */ var ThemesIntrovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isMarketplaceEnabled: Boolean,
    otherUsersCount: Number,
    themeEnabled: Boolean,
    isPluginsAdminEnabled: Boolean
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  computed: {
    teaserExtendByThemeText: function teaserExtendByThemeText() {
      var query = external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      });
      var hash = external_CoreHome_["MatomoUrl"].stringify({
        pluginType: 'themes'
      });
      var link = "?".concat(query, "#?").concat(hash);
      return Object(external_CoreHome_["translate"])('CorePluginsAdmin_TeaserExtendPiwikByTheme', "<a href=\"".concat(link, "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/ThemesIntro/ThemesIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/ThemesIntro/ThemesIntro.vue



ThemesIntrovue_type_script_lang_ts.render = ThemesIntrovue_type_template_id_355bc09e_render

/* harmony default export */ var ThemesIntro = (ThemesIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginName.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var PluginName_window = window,
    PluginName_$ = PluginName_window.$;
window.broadcast.addPopoverHandler('browsePluginDetail', function (value) {
  var pluginName = value;
  var activeTab = null;

  if (value.indexOf('!') !== -1) {
    activeTab = value.slice(value.indexOf('!') + 1);
    pluginName = value.slice(0, value.indexOf('!'));
  } // use marketplace popover if marketplace is loaded


  if (external_CoreHome_["MatomoUrl"].urlParsed.value.module === 'Marketplace' && external_CoreHome_["MatomoUrl"].urlParsed.value.action === 'overview') {
    window.broadcast.propagateNewPopoverParameter('');
    external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
      showPlugin: pluginName,
      popover: null
    }));
    return;
  }

  var url = "module=Marketplace&action=pluginDetails&pluginName=".concat(encodeURIComponent(pluginName));

  if (activeTab) {
    url += "&activeTab=".concat(encodeURIComponent(activeTab));
  }

  window.Piwik_Popover.createPopupAndLoadUrl(url, 'details');
});

function onClickPluginNameLink(binding, event) {
  var pluginName = binding.value.pluginName;
  var activePluginTab = binding.value.activePluginTab;
  event.preventDefault();

  if (activePluginTab) {
    pluginName += "!".concat(activePluginTab);
  }

  window.broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
}

/* harmony default export */ var PluginName = ({
  mounted: function mounted(element, binding) {
    var pluginName = binding.value.pluginName;

    if (!pluginName) {
      return;
    }

    binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);
    PluginName_$(element).on('click', binding.value.onClickHandler) // attribute added for AnonymousPiwikUsageMeasurement
    .attr('matomo-plugin-name', pluginName);
  },
  unmounted: function unmounted(element, binding) {
    PluginName_$(element).off('click', binding.value.onClickHandler);
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTable.vue?vue&type=template&id=41c002f9

var PluginsTablevue_type_template_id_41c002f9_hoisted_1 = {
  class: "row pluginsFilter"
};
var PluginsTablevue_type_template_id_41c002f9_hoisted_2 = {
  class: "origin"
};
var PluginsTablevue_type_template_id_41c002f9_hoisted_3 = {
  style: {
    "margin-right": "3.5px"
  }
};
var PluginsTablevue_type_template_id_41c002f9_hoisted_4 = {
  "data-filter-origin": "all",
  href: "#",
  class: "active"
};

var PluginsTablevue_type_template_id_41c002f9_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var PluginsTablevue_type_template_id_41c002f9_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var PluginsTablevue_type_template_id_41c002f9_hoisted_7 = {
  "data-filter-origin": "core",
  href: "#"
};

var PluginsTablevue_type_template_id_41c002f9_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var PluginsTablevue_type_template_id_41c002f9_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var PluginsTablevue_type_template_id_41c002f9_hoisted_10 = {
  "data-filter-origin": "official",
  href: "#"
};

var PluginsTablevue_type_template_id_41c002f9_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_13 = {
  "data-filter-origin": "thirdparty",
  href: "#"
};

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var _hoisted_15 = {
  class: "status"
};
var _hoisted_16 = {
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_17 = {
  "data-filter-status": "all",
  href: "#",
  class: "active"
};

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_20 = {
  "data-filter-status": "active",
  href: "#"
};

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_23 = {
  "data-filter-status": "inactive",
  href: "#"
};

var _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "counter"
}, null, -1);

var _hoisted_25 = {
  id: "confirmUninstallPlugin",
  class: "ui-confirm"
};
var _hoisted_26 = {
  id: "uninstallPluginConfirm"
};
var _hoisted_27 = ["value"];
var _hoisted_28 = ["value"];
var _hoisted_29 = {
  class: "status"
};
var _hoisted_30 = {
  key: 0,
  class: "action-links"
};
var _hoisted_31 = {
  id: "plugins"
};
var _hoisted_32 = ["data-filter-status", "data-filter-origin"];
var _hoisted_33 = {
  class: "name"
};
var _hoisted_34 = ["name"];
var _hoisted_35 = {
  key: 0
};
var _hoisted_36 = {
  key: 1
};
var _hoisted_37 = ["title"];
var _hoisted_38 = {
  key: 2
};

var _hoisted_39 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_40 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_41 = ["href"];
var _hoisted_42 = {
  class: "desc"
};
var _hoisted_43 = {
  class: "plugin-desc-missingrequirements"
};
var _hoisted_44 = {
  key: 0
};

var _hoisted_45 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_46 = {
  class: "plugin-desc-text"
};
var _hoisted_47 = {
  key: 0,
  class: "plugin-homepage"
};
var _hoisted_48 = ["href"];
var _hoisted_49 = {
  key: 1,
  class: "plugin-donation"
};
var _hoisted_50 = ["data-overlay-id"];
var _hoisted_51 = ["id", "title"];
var _hoisted_52 = ["innerHTML"];
var _hoisted_53 = {
  class: "donation-links"
};
var _hoisted_54 = ["href"];

var _hoisted_55 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/CorePluginsAdmin/images/paypal_donate.png",
  height: "30"
}, null, -1);

var _hoisted_56 = [_hoisted_55];
var _hoisted_57 = ["href"];

var _hoisted_58 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "alignnone",
  title: "Flattr",
  alt: "",
  src: "plugins/CorePluginsAdmin/images/flattr.png",
  height: "29"
}, null, -1);

var _hoisted_59 = [_hoisted_58];
var _hoisted_60 = {
  key: 2,
  class: "donation-link bitcoin"
};

var _hoisted_61 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, "Donate Bitcoins to:", -1);

var _hoisted_62 = ["href"];
var _hoisted_63 = ["value"];
var _hoisted_64 = {
  key: 0,
  class: "plugin-license"
};
var _hoisted_65 = ["title", "href"];
var _hoisted_66 = {
  key: 1
};
var _hoisted_67 = {
  key: 1,
  class: "plugin-author"
};

var _hoisted_68 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" By ");

var _hoisted_69 = ["title", "href"];
var _hoisted_70 = {
  key: 1
};
var _hoisted_71 = {
  key: 2,
  style: {
    "margin-right": "3.5px"
  }
};

var _hoisted_72 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ");

var _hoisted_73 = {
  key: 0
};
var _hoisted_74 = {
  key: 0
};
var _hoisted_75 = {
  key: 1
};
var _hoisted_76 = {
  key: 0
};

var _hoisted_77 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_78 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ");

var _hoisted_79 = ["data-plugin-name", "href"];
var _hoisted_80 = {
  key: 0
};
var _hoisted_81 = {
  key: 0
};
var _hoisted_82 = {
  key: 1
};
var _hoisted_83 = ["href"];
var _hoisted_84 = {
  key: 1
};
var _hoisted_85 = ["href"];
var _hoisted_86 = {
  key: 0,
  class: "tableActionBar"
};
var _hoisted_87 = ["href"];

var _hoisted_88 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var _hoisted_89 = ["href"];

var _hoisted_90 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var _hoisted_91 = {
  class: "footer-message"
};
function PluginsTablevue_type_template_id_41c002f9_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_plugin_filter = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-filter");

  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  var _directive_plugin_management = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-management");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.title,
    class: "pluginsManagement"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", PluginsTablevue_type_template_id_41c002f9_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginsTablevue_type_template_id_41c002f9_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", PluginsTablevue_type_template_id_41c002f9_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Origin')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", PluginsTablevue_type_template_id_41c002f9_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_All')), 1), PluginsTablevue_type_template_id_41c002f9_hoisted_5]), PluginsTablevue_type_template_id_41c002f9_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", PluginsTablevue_type_template_id_41c002f9_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_OriginCore')), 1), PluginsTablevue_type_template_id_41c002f9_hoisted_8]), PluginsTablevue_type_template_id_41c002f9_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", PluginsTablevue_type_template_id_41c002f9_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_OriginOfficial')), 1), PluginsTablevue_type_template_id_41c002f9_hoisted_11]), _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_OriginThirdParty')), 1), _hoisted_14])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_All')), 1), _hoisted_18]), _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Active')), 1), _hoisted_21]), _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Inactive')), 1), _hoisted_24])])], 512), [[_directive_plugin_filter]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_UninstallConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        role: "yes",
        type: "button",
        value: _ctx.translate('General_Yes')
      }, null, 8, _hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        role: "no",
        type: "button",
        value: _ctx.translate('General_No')
      }, null, 8, _hoisted_28)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.isTheme ? _ctx.translate('CorePluginsAdmin_Theme') : _ctx.translate('General_Plugin')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), _ctx.displayAdminLinks ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", _hoisted_30, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", _hoisted_31, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginsToDisplay, function (plugin, name) {
        var _plugin$info, _plugin$info2, _plugin$info3, _plugin$info3$donate, _plugin$info4, _plugin$info4$donate, _plugin$info5, _plugin$info5$donate, _plugin$info$donate, _plugin$info6, _plugin$info6$donate, _plugin$info7, _plugin$info8, _plugin$info9;

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: name,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(plugin.activated ? 'active-plugin' : 'inactive-plugin'),
          "data-filter-status": plugin.activated ? 'active' : 'inactive',
          "data-filter-origin": _ctx.getPluginOrigin(plugin)
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          name: name
        }, null, 8, _hoisted_34), !plugin.isCorePlugin && _ctx.marketplacePluginNames.indexOf(name) !== -1 ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(name), 1)], 512)), [[_directive_plugin_name, {
          pluginName: name
        }]]) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_36, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(name), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: "plugin-version",
          title: plugin.isCorePlugin ? _ctx.translate('CorePluginsAdmin_CorePluginTooltip') : undefined
        }, " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isCorePlugin ? _ctx.translate('CorePluginsAdmin_OriginCore') : plugin.info.version) + ") ", 9, _hoisted_37), _ctx.pluginNamesHavingSettings.indexOf(name) !== -1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_38, [_hoisted_39, _hoisted_40, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          href: "".concat(_ctx.generalSettingsLink, "#").concat(name),
          class: "settingsLink"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Settings')), 9, _hoisted_41)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_43, [plugin.missingRequirements ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_44, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.missingRequirements) + " ", 1), _hoisted_45])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.info.description.replaceAll('\n', '<br/>')) + " ", 1), (_plugin$info = plugin.info) !== null && _plugin$info !== void 0 && _plugin$info.homepage && !_ctx.isMatomoUrl((_plugin$info2 = plugin.info) === null || _plugin$info2 === void 0 ? void 0 : _plugin$info2.homepage) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_47, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          target: "_blank",
          rel: "noreferrer noopener",
          href: plugin.info.homepage
        }, " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_PluginHomepage').replaceAll(' ', ' ')) + ") ", 9, _hoisted_48)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_plugin$info3 = plugin.info) !== null && _plugin$info3 !== void 0 && (_plugin$info3$donate = _plugin$info3.donate) !== null && _plugin$info3$donate !== void 0 && _plugin$info3$donate.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_49, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_LikeThisPlugin')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function () {}, ["prevent"])),
          class: "plugin-donation-link",
          "data-overlay-id": "overlay-".concat(name)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ConsiderDonating')), 9, _hoisted_50), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
          id: "overlay-".concat(name),
          class: "donation-overlay ui-confirm",
          title: _ctx.translate('CorePluginsAdmin_LikeThisPlugin')
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_CommunityContributedPlugin')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
          innerHTML: _ctx.$sanitize(_ctx.translate('CorePluginsAdmin_ConsiderDonatingCreatorOf', "<b>".concat(name, "</b>")))
        }, null, 8, _hoisted_52), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_53, [(_plugin$info4 = plugin.info) !== null && _plugin$info4 !== void 0 && (_plugin$info4$donate = _plugin$info4.donate) !== null && _plugin$info4$donate !== void 0 && _plugin$info4$donate.paypal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          class: "donation-link paypal",
          target: "_blank",
          rel: "noreferrer noopener",
          href: _ctx.getPluginDonateLink(name, plugin.info.donate.paypal)
        }, _hoisted_56, 8, _hoisted_54)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_plugin$info5 = plugin.info) !== null && _plugin$info5 !== void 0 && (_plugin$info5$donate = _plugin$info5.donate) !== null && _plugin$info5$donate !== void 0 && _plugin$info5$donate.flattr ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 1,
          class: "donation-link flattr",
          target: "_blank",
          rel: "noreferrer noopener",
          href: (_plugin$info$donate = plugin.info.donate) === null || _plugin$info$donate === void 0 ? void 0 : _plugin$info$donate.flattr
        }, _hoisted_59, 8, _hoisted_57)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_plugin$info6 = plugin.info) !== null && _plugin$info6 !== void 0 && (_plugin$info6$donate = _plugin$info6.donate) !== null && _plugin$info6$donate !== void 0 && _plugin$info6$donate.bitcoin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_60, [_hoisted_61, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          href: "bitcoin:".concat(encodeURIComponent(plugin.info.donate.bitcoin))
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.info.donate.bitcoin), 9, _hoisted_62)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          role: "no",
          type: "button",
          value: _ctx.translate('General_Close')
        }, null, 8, _hoisted_63)], 8, _hoisted_51)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), (_plugin$info7 = plugin.info) !== null && _plugin$info7 !== void 0 && _plugin$info7.license ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_64, [(_plugin$info8 = plugin.info) !== null && _plugin$info8 !== void 0 && _plugin$info8.license_file ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          title: _ctx.translate('CorePluginsAdmin_LicenseHomepage'),
          rel: "noreferrer noopener",
          target: "_blank",
          href: "index.php?module=CorePluginsAdmin&action=showLicense&pluginName=".concat(name)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.info.license), 9, _hoisted_65)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_66, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.info.license), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_plugin$info9 = plugin.info) !== null && _plugin$info9 !== void 0 && _plugin$info9.authors ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_67, [_hoisted_68, (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(plugin.info.authors.filter(function (a) {
          return a.name;
        }), function (author, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
            key: index
          }, [author.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
            key: 0,
            title: _ctx.translate('CorePluginsAdmin_AuthorHomepage'),
            href: author.homepage,
            rel: "noreferrer noopener",
            target: "_blank"
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, _hoisted_69)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_70, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 1)), plugin.info.authors.length - 1 > index ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_71, ",")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
        }), 128)), _hoisted_72])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          class: "status",
          style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
            'border-left-width': _ctx.isDefaultTheme(name) ? '0' : undefined
          })
        }, [!_ctx.isDefaultTheme(name) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_73, [plugin.activated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_74, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Active')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_75, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Inactive')) + " ", 1), plugin.uninstallable && _ctx.displayAdminLinks ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_76, [_hoisted_77, _hoisted_78, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          "data-plugin-name": name,
          class: "uninstall",
          href: _ctx.getUninstallLink(name)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ActionUninstall')), 9, _hoisted_79)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 4), _ctx.displayAdminLinks ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: 0,
          class: "togl action-links",
          style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
            'border-left-width': _ctx.isDefaultTheme(name) ? 0 : undefined
          })
        }, [!_ctx.isDefaultTheme(name) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_80, [plugin.invalid && plugin.alwaysActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_81, "-")) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_82, [plugin.activated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          href: _ctx.getDeactivateLink(name)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Deactivate')), 9, _hoisted_83)) : plugin.missingRequirements ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_84, "-")) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 2,
          href: _ctx.getActivateLink(name)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activate')), 9, _hoisted_85))]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, _hoisted_32);
      }), 128))])], 512), [[_directive_content_table]]), _ctx.displayAdminLinks ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_86, [_ctx.isTheme ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        href: _ctx.themeOverviewLink
      }, [_hoisted_88, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_InstallNewThemes')), 1)], 8, _hoisted_87)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        href: _ctx.overviewLink
      }, [_hoisted_90, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_InstallNewPlugins')), 1)], 8, _hoisted_89))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_91, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_AlwaysActivatedPluginsList', _ctx.pluginsAlwaysActivated)), 1)];
    }),
    _: 1
  }, 8, ["content-title"])), [[_directive_plugin_management, {}]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTable.vue?vue&type=template&id=41c002f9

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTable.vue?vue&type=script&lang=ts
function PluginsTablevue_type_script_lang_ts_slicedToArray(arr, i) { return PluginsTablevue_type_script_lang_ts_arrayWithHoles(arr) || PluginsTablevue_type_script_lang_ts_iterableToArrayLimit(arr, i) || PluginsTablevue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || PluginsTablevue_type_script_lang_ts_nonIterableRest(); }

function PluginsTablevue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function PluginsTablevue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return PluginsTablevue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return PluginsTablevue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function PluginsTablevue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function PluginsTablevue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function PluginsTablevue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }






/* harmony default export */ var PluginsTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    PluginManagement: PluginManagement,
    PluginFilter: PluginFilter,
    ContentTable: external_CoreHome_["ContentTable"],
    PluginName: PluginName
  },
  methods: {
    getPluginOrigin: function getPluginOrigin(plugin) {
      if (plugin.isCorePlugin) {
        return 'core';
      }

      if (plugin.isOfficialPlugin) {
        return 'official';
      }

      return 'thirdparty';
    },
    getPluginDonateLink: function getPluginDonateLink(pluginName, business) {
      return "https://www.paypal.com/cgi-bin/webscr?".concat(external_CoreHome_["MatomoUrl"].stringify({
        cmd: '_donations',
        item_name: "Matomo Plugin ".concat(pluginName),
        bn: 'PP-DonationsBF:btn_donateCC_LG.gif:NonHosted',
        business: business
      }));
    },
    getUninstallLink: function getUninstallLink(pluginName) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'CorePluginsAdmin',
        action: 'uninstall',
        pluginName: pluginName,
        nonce: this.uninstallNonce
      }));
    },
    isDefaultTheme: function isDefaultTheme(pluginName) {
      return this.isTheme && pluginName === 'Morpheus';
    },
    getDeactivateLink: function getDeactivateLink(pluginName) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        pluginName: pluginName,
        nonce: this.deactivateNonce,
        redirectTo: 'referrer'
      }));
    },
    getActivateLink: function getActivateLink(pluginName) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'CorePluginsAdmin',
        action: 'activate',
        pluginName: pluginName,
        nonce: this.activateNonce,
        redirectTo: 'referrer'
      }));
    },
    isMatomoUrl: function isMatomoUrl(url) {
      try {
        var pluginHost = new URL(url).host;
        return this.matomoHosts.indexOf(pluginHost) !== -1;
      } catch (error) {
        // the plugin may provide a broken/invalid url
        return false;
      }
    }
  },
  computed: {
    pluginsToDisplay: function pluginsToDisplay() {
      var _this = this;

      var pluginsInfo = this.pluginsInfo;
      return Object.fromEntries(Object.entries(pluginsInfo).filter(function (_ref) {
        var _ref2 = PluginsTablevue_type_script_lang_ts_slicedToArray(_ref, 2),
            info = _ref2[1];

        if (_this.isTheme) {
          return true;
        }

        var alwaysActivated = info.alwaysActivated;
        return typeof alwaysActivated !== 'undefined' && alwaysActivated !== null && !alwaysActivated;
      }));
    },
    generalSettingsLink: function generalSettingsLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'CoreAdminHome',
        action: 'generalSettings'
      })));
    },
    matomoHosts: function matomoHosts() {
      return ['piwik.org', 'www.piwik.org', 'matomo.org', 'www.matomo.org'];
    },
    themeOverviewLink: function themeOverviewLink() {
      var query = external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      });
      var hash = external_CoreHome_["MatomoUrl"].stringify({
        pluginType: 'themes'
      });
      return "?".concat(query, "#?").concat(hash);
    },
    overviewLink: function overviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview',
        sort: ''
      })));
    },
    pluginsAlwaysActivated: function pluginsAlwaysActivated() {
      var pluginsInfo = this.pluginsInfo;
      return Object.entries(pluginsInfo).filter(function (_ref3) {
        var _ref4 = PluginsTablevue_type_script_lang_ts_slicedToArray(_ref3, 2),
            plugin = _ref4[1];

        return plugin.alwaysActivated;
      }).map(function (_ref5) {
        var _ref6 = PluginsTablevue_type_script_lang_ts_slicedToArray(_ref5, 1),
            name = _ref6[0];

        return name;
      }).join(', ');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTable.vue



PluginsTablevue_type_script_lang_ts.render = PluginsTablevue_type_template_id_41c002f9_render

/* harmony default export */ var PluginsTable = (PluginsTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTableWithUpdates.vue?vue&type=template&id=3e5099d9

var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_1 = {
  key: 0
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_2 = {
  key: 0
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_3 = {
  class: "checkbox-container"
};

var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);

var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_5 = {
  class: "num"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_6 = {
  class: "status"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_7 = {
  key: 1,
  class: "action-links"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_8 = {
  id: "plugins"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_9 = {
  key: 0,
  class: "select-cell"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_10 = {
  class: "checkbox-container"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_11 = ["id", "disabled", "onUpdate:modelValue"];

var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);

var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_13 = {
  class: "name"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_14 = {
  class: "vers"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_15 = ["href", "title"];
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_16 = {
  key: 1
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_17 = {
  class: "desc"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_18 = {
  class: "status"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_19 = {
  key: 1,
  class: "togl action-links"
};
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_20 = ["title"];
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_21 = ["href"];
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_22 = ["href"];
var PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_23 = {
  key: 3
};
function PluginsTableWithUpdatesvue_type_template_id_3e5099d9_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MissingReqsNotice = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MissingReqsNotice");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object.keys(_ctx.pluginsHavingUpdate).length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    "content-title": _ctx.translate('CorePluginsAdmin_NUpdatesAvailable', Object.keys(_ctx.pluginsHavingUpdate).length)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_InfoPluginUpdateIsRecommended')), 1), _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        id: "update-selected-plugins",
        onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
          return _ctx.updateSelectedPlugins();
        }, ["prevent"])),
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          btn: true,
          disabled: _ctx.isUpdateLinkDisabled
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_UpdateSelected')), 3)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [_ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "checkbox",
        id: "select-plugin-all",
        onChange: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.selectAll($event.target.checked);
        })
      }, null, 32), PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_4])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Plugin')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginsHavingUpdate, function (plugin, name) {
        var _plugin$changelog;

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: name,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(plugin.isActivated ? 'active-plugin' : 'inactive-plugin')
        }, [_ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          type: "checkbox",
          id: "select-plugin-".concat(plugin.name),
          disabled: typeof plugin.isDownloadable !== 'undefined' && plugin.isDownloadable !== null && !plugin.isDownloadable,
          "onUpdate:modelValue": function onUpdateModelValue($event) {
            return _ctx.pluginsSelected[name] = $event;
          }
        }, null, 8, PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.pluginsSelected[name]]]), PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_12])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function () {}, ["prevent"])),
          class: "plugin-details"
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.name), 1)], 512), [[_directive_plugin_name, {
          pluginName: plugin.name
        }]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_14, [(_plugin$changelog = plugin.changelog) !== null && _plugin$changelog !== void 0 && _plugin$changelog.url ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          href: plugin.changelog.url,
          title: _ctx.translate('CorePluginsAdmin_Changelog'),
          target: "_blank",
          rel: "noreferrer noopener"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.currentVersion) + " => " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.latestVersion), 9, PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_15)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.currentVersion) + " => " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.latestVersion), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MissingReqsNotice, {
          plugin: plugin
        }, null, 8, ["plugin"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isActivated ? _ctx.translate('CorePluginsAdmin_Active') : _ctx.translate('CorePluginsAdmin_Inactive')), 1), _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_19, [typeof plugin.isDownloadable !== 'undefined' && plugin.isDownloadable !== null && !plugin.isDownloadable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 0,
          title: "".concat(_ctx.translate('CorePluginsAdmin_PluginNotDownloadable'), " ").concat(plugin.isPaid ? _ctx.translate('CorePluginsAdmin_PluginNotDownloadablePaidReason') : '')
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_NotDownloadable')), 9, PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_20)) : _ctx.isMultiServerEnvironment ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 1,
          onClick: _cache[3] || (_cache[3] = function ($event) {
            return _ctx.isPluginDownloadLinkClicked = true;
          }),
          href: _ctx.downloadPluginLink(plugin)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 9, PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_21)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isPluginDownloadLinkClicked]]) : plugin.missingRequirements.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 2,
          href: _ctx.updatePluginLink(plugin)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreUpdater_UpdateTitle')), 9, PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_22)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginsTableWithUpdatesvue_type_template_id_3e5099d9_hoisted_23, "-"))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
      }), 128))])], 512), [[_directive_content_table]])];
    }),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTableWithUpdates.vue?vue&type=template&id=3e5099d9

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTableWithUpdates.vue?vue&type=script&lang=ts
function PluginsTableWithUpdatesvue_type_script_lang_ts_slicedToArray(arr, i) { return PluginsTableWithUpdatesvue_type_script_lang_ts_arrayWithHoles(arr) || PluginsTableWithUpdatesvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || PluginsTableWithUpdatesvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || PluginsTableWithUpdatesvue_type_script_lang_ts_nonIterableRest(); }

function PluginsTableWithUpdatesvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function PluginsTableWithUpdatesvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return PluginsTableWithUpdatesvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return PluginsTableWithUpdatesvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function PluginsTableWithUpdatesvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function PluginsTableWithUpdatesvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function PluginsTableWithUpdatesvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }




var MissingReqsNotice = Object(external_CoreHome_["useExternalPluginComponent"])('Marketplace', 'MissingReqsNotice');
/* harmony default export */ var PluginsTableWithUpdatesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    ContentBlock: external_CoreHome_["ContentBlock"],
    MissingReqsNotice: MissingReqsNotice
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"],
    PluginName: PluginName
  },
  data: function data() {
    return {
      isUpdating: false,
      isPluginDownloadLinkClicked: false,
      pluginsSelected: {}
    };
  },
  computed: {
    isUpdateLinkDisabled: function isUpdateLinkDisabled() {
      return this.isUpdating || !Object.keys(this.pluginsSelected).length || !Object.values(this.pluginsSelected).some(function (s) {
        return !!s;
      });
    }
  },
  methods: {
    selectAll: function selectAll(checked) {
      var _this = this;

      var plugins = this.pluginsHavingUpdate;
      Object.entries(plugins).forEach(function (_ref) {
        var _ref2 = PluginsTableWithUpdatesvue_type_script_lang_ts_slicedToArray(_ref, 2),
            name = _ref2[0],
            plugin = _ref2[1];

        if (plugin.isDownloadable !== null && typeof plugin.isDownloadable !== 'undefined' && !plugin.isDownloadable) {
          return;
        }

        _this.pluginsSelected[name] = checked;
      });
    },
    downloadPluginLink: function downloadPluginLink(plugin) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'download',
        pluginName: plugin.name,
        nonce: this.pluginUpdateNonces[plugin.name]
      })));
    },
    updatePluginLink: function updatePluginLink(plugin) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'updatePlugin',
        pluginName: plugin.name,
        nonce: this.updateNonce
      })));
    },
    updateSelectedPlugins: function updateSelectedPlugins() {
      this.isUpdating = true;
      var pluginsToUpdate = Object.entries(this.pluginsSelected).filter(function (_ref3) {
        var _ref4 = PluginsTableWithUpdatesvue_type_script_lang_ts_slicedToArray(_ref3, 2),
            selected = _ref4[1];

        return selected;
      }).map(function (_ref5) {
        var _ref6 = PluginsTableWithUpdatesvue_type_script_lang_ts_slicedToArray(_ref5, 1),
            name = _ref6[0];

        return name;
      });
      external_CoreHome_["MatomoUrl"].updateUrl(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'updatePlugin',
        nonce: this.updateNonce,
        pluginName: pluginsToUpdate.join(',')
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTableWithUpdates.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginsTable/PluginsTableWithUpdates.vue



PluginsTableWithUpdatesvue_type_script_lang_ts.render = PluginsTableWithUpdatesvue_type_template_id_3e5099d9_render

/* harmony default export */ var PluginsTableWithUpdates = (PluginsTableWithUpdatesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/UploadPluginDialog/UploadPluginDialog.vue?vue&type=template&id=64bf4ede

var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_1 = {
  class: "ui-confirm",
  id: "installPluginByUpload"
};
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_2 = {
  key: 0
};
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_3 = {
  class: "description"
};
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_4 = ["action"];
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_5 = ["data-max-size"];

var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_7 = ["value"];
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_8 = {
  key: 1
};
var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_9 = ["innerHTML"];

var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, "[General]\n  enable_plugin_upload = 1", -1);

var UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_11 = ["value"];
function UploadPluginDialogvue_type_template_id_64bf4ede_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_plugin_upload = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-upload");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TeaserExtendPiwikByUpload')), 1), _ctx.isPluginUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AllowedUploadFormats')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
    enctype: "multipart/form-data",
    method: "post",
    id: "uploadPluginForm",
    action: _ctx.uploadPluginAction
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "file",
    name: "pluginZip",
    "data-max-size": _ctx.uploadLimit
  }, null, 8, UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_5), UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "password",
    name: "confirmPassword",
    autocomplete: "off",
    title: _ctx.translate('Login_ConfirmPasswordToContinue'),
    modelValue: _ctx.confirmPassword,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.confirmPassword = $event;
    })
  }, null, 8, ["title", "modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "startUpload btn",
    type: "submit",
    value: _ctx.translate('Marketplace_UploadZipFile')
  }, null, 8, UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_7)], 8, UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_4)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "description",
    innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_PluginUploadDisabled'))
  }, null, 8, UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_9), UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Ok')
  }, null, 8, UploadPluginDialogvue_type_template_id_64bf4ede_hoisted_11)]))], 512)), [[_directive_plugin_upload]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/UploadPluginDialog/UploadPluginDialog.vue?vue&type=template&id=64bf4ede

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/UploadPluginDialog/UploadPluginDialog.vue?vue&type=script&lang=ts




/* harmony default export */ var UploadPluginDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    installNonce: String
  },
  components: {
    Field: Field
  },
  directives: {
    PluginUpload: PluginUpload
  },
  data: function data() {
    return {
      confirmPassword: ''
    };
  },
  computed: {
    uploadPluginAction: function uploadPluginAction() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'CorePluginsAdmin',
        action: 'uploadPlugin',
        nonce: this.installNonce
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/UploadPluginDialog/UploadPluginDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/UploadPluginDialog/UploadPluginDialog.vue



UploadPluginDialogvue_type_script_lang_ts.render = UploadPluginDialogvue_type_template_id_64bf4ede_render

/* harmony default export */ var UploadPluginDialog = (UploadPluginDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/


















// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=CorePluginsAdmin.umd.js.map