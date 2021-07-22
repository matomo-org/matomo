(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("axios"), require("classNames"), require("React"), require("ReactDOM"));
	else if(typeof define === 'function' && define.amd)
		define(["axios", "classNames", "React", "ReactDOM"], factory);
	else if(typeof exports === 'object')
		exports["@matomo/core-home"] = factory(require("axios"), require("classNames"), require("React"), require("ReactDOM"));
	else
		root["@matomo/core-home"] = factory(root["axios"], root["classNames"], root["React"], root["ReactDOM"]);
})(this, function(__WEBPACK_EXTERNAL_MODULE_axios__, __WEBPACK_EXTERNAL_MODULE_classnames__, __WEBPACK_EXTERNAL_MODULE_react__, __WEBPACK_EXTERNAL_MODULE_react_dom__) {
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/bind.js":
/*!*******************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/bind.js ***!
  \*******************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/bind */ "../../../node_modules/core-js-pure/stable/instance/bind.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/concat.js":
/*!*********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/concat.js ***!
  \*********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/concat */ "../../../node_modules/core-js-pure/stable/instance/concat.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/for-each.js":
/*!***********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/for-each.js ***!
  \***********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/for-each */ "../../../node_modules/core-js-pure/stable/instance/for-each.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/index-of.js":
/*!***********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/index-of.js ***!
  \***********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/index-of */ "../../../node_modules/core-js-pure/stable/instance/index-of.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/map.js":
/*!******************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/map.js ***!
  \******************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/map */ "../../../node_modules/core-js-pure/stable/instance/map.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/sort.js":
/*!*******************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/instance/sort.js ***!
  \*******************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/instance/sort */ "../../../node_modules/core-js-pure/stable/instance/sort.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js":
/*!*******************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js ***!
  \*******************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/object/assign */ "../../../node_modules/core-js-pure/stable/object/assign.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/url-search-params.js":
/*!***********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/core-js-stable/url-search-params.js ***!
  \***********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! core-js-pure/stable/url-search-params */ "../../../node_modules/core-js-pure/stable/url-search-params/index.js");

/***/ }),

/***/ "../../../node_modules/@babel/runtime-corejs3/regenerator/index.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/@babel/runtime-corejs3/regenerator/index.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! regenerator-runtime */ "../../../node_modules/regenerator-runtime/runtime.js");


/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js":
/*!****************************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js ***!
  \****************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _assertThisInitialized; });
function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!***********************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \***********************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _asyncToGenerator; });
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*********************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*********************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _classCallCheck; });
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js":
/*!******************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js ***!
  \******************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _createClass; });
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper.js":
/*!******************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper.js ***!
  \******************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _createSuper; });
/* harmony import */ var _babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/getPrototypeOf */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_esm_isNativeReflectConstruct__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/esm/isNativeReflectConstruct */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/isNativeReflectConstruct.js");
/* harmony import */ var _babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/esm/possibleConstructorReturn */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js");



function _createSuper(Derived) {
  var hasNativeReflectConstruct = Object(_babel_runtime_helpers_esm_isNativeReflectConstruct__WEBPACK_IMPORTED_MODULE_1__["default"])();
  return function _createSuperInternal() {
    var Super = Object(_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_0__["default"])(Derived),
        result;

    if (hasNativeReflectConstruct) {
      var NewTarget = Object(_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_0__["default"])(this).constructor;
      result = Reflect.construct(Super, arguments, NewTarget);
    } else {
      result = Super.apply(this, arguments);
    }

    return Object(_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__["default"])(this, result);
  };
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js":
/*!*********************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js ***!
  \*********************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _getPrototypeOf; });
function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js":
/*!***************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js ***!
  \***************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _inherits; });
/* harmony import */ var _babel_runtime_helpers_esm_setPrototypeOf__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/setPrototypeOf */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) Object(_babel_runtime_helpers_esm_setPrototypeOf__WEBPACK_IMPORTED_MODULE_0__["default"])(subClass, superClass);
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/isNativeReflectConstruct.js":
/*!*******************************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/isNativeReflectConstruct.js ***!
  \*******************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _isNativeReflectConstruct; });
function _isNativeReflectConstruct() {
  if (typeof Reflect === "undefined" || !Reflect.construct) return false;
  if (Reflect.construct.sham) return false;
  if (typeof Proxy === "function") return true;

  try {
    Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
    return true;
  } catch (e) {
    return false;
  }
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js":
/*!********************************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js ***!
  \********************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _possibleConstructorReturn; });
/* harmony import */ var _babel_runtime_helpers_esm_typeof__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/typeof */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/esm/assertThisInitialized */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js");


function _possibleConstructorReturn(self, call) {
  if (call && (Object(_babel_runtime_helpers_esm_typeof__WEBPACK_IMPORTED_MODULE_0__["default"])(call) === "object" || typeof call === "function")) {
    return call;
  }

  return Object(_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_1__["default"])(self);
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*********************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*********************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _setPrototypeOf; });
function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

/***/ }),

/***/ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!*************************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \*************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _typeof; });
function _typeof(obj) {
  "@babel/helpers - typeof";

  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    _typeof = function _typeof(obj) {
      return typeof obj;
    };
  } else {
    _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/array/virtual/concat.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/array/virtual/concat.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.array.concat */ "../../../node_modules/core-js-pure/modules/es.array.concat.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Array').concat;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/array/virtual/for-each.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/array/virtual/for-each.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.array.for-each */ "../../../node_modules/core-js-pure/modules/es.array.for-each.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Array').forEach;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/array/virtual/index-of.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/array/virtual/index-of.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.array.index-of */ "../../../node_modules/core-js-pure/modules/es.array.index-of.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Array').indexOf;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/array/virtual/map.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/array/virtual/map.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.array.map */ "../../../node_modules/core-js-pure/modules/es.array.map.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Array').map;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/array/virtual/sort.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/array/virtual/sort.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.array.sort */ "../../../node_modules/core-js-pure/modules/es.array.sort.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Array').sort;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/function/virtual/bind.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/function/virtual/bind.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../../modules/es.function.bind */ "../../../node_modules/core-js-pure/modules/es.function.bind.js");

var entryVirtual = __webpack_require__(/*! ../../../internals/entry-virtual */ "../../../node_modules/core-js-pure/internals/entry-virtual.js");

module.exports = entryVirtual('Function').bind;

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/instance/bind.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/instance/bind.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var bind = __webpack_require__(/*! ../function/virtual/bind */ "../../../node_modules/core-js-pure/es/function/virtual/bind.js");

var FunctionPrototype = Function.prototype;

module.exports = function (it) {
  var own = it.bind;
  return it === FunctionPrototype || it instanceof Function && own === FunctionPrototype.bind ? bind : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/instance/concat.js":
/*!***********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/instance/concat.js ***!
  \***********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var concat = __webpack_require__(/*! ../array/virtual/concat */ "../../../node_modules/core-js-pure/es/array/virtual/concat.js");

var ArrayPrototype = Array.prototype;

module.exports = function (it) {
  var own = it.concat;
  return it === ArrayPrototype || it instanceof Array && own === ArrayPrototype.concat ? concat : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/instance/index-of.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/instance/index-of.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var indexOf = __webpack_require__(/*! ../array/virtual/index-of */ "../../../node_modules/core-js-pure/es/array/virtual/index-of.js");

var ArrayPrototype = Array.prototype;

module.exports = function (it) {
  var own = it.indexOf;
  return it === ArrayPrototype || it instanceof Array && own === ArrayPrototype.indexOf ? indexOf : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/instance/map.js":
/*!********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/instance/map.js ***!
  \********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var map = __webpack_require__(/*! ../array/virtual/map */ "../../../node_modules/core-js-pure/es/array/virtual/map.js");

var ArrayPrototype = Array.prototype;

module.exports = function (it) {
  var own = it.map;
  return it === ArrayPrototype || it instanceof Array && own === ArrayPrototype.map ? map : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/instance/sort.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/instance/sort.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var sort = __webpack_require__(/*! ../array/virtual/sort */ "../../../node_modules/core-js-pure/es/array/virtual/sort.js");

var ArrayPrototype = Array.prototype;

module.exports = function (it) {
  var own = it.sort;
  return it === ArrayPrototype || it instanceof Array && own === ArrayPrototype.sort ? sort : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/es/object/assign.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/es/object/assign.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../modules/es.object.assign */ "../../../node_modules/core-js-pure/modules/es.object.assign.js");

var path = __webpack_require__(/*! ../../internals/path */ "../../../node_modules/core-js-pure/internals/path.js");

module.exports = path.Object.assign;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/a-function.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/a-function.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') {
    throw TypeError(String(it) + ' is not a function');
  }

  return it;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/a-possible-prototype.js":
/*!***********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/a-possible-prototype.js ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

module.exports = function (it) {
  if (!isObject(it) && it !== null) {
    throw TypeError("Can't set " + String(it) + ' as a prototype');
  }

  return it;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/add-to-unscopables.js":
/*!*********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/add-to-unscopables.js ***!
  \*********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function () {
  /* empty */
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/an-instance.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/an-instance.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (it, Constructor, name) {
  if (!(it instanceof Constructor)) {
    throw TypeError('Incorrect ' + (name ? name + ' ' : '') + 'invocation');
  }

  return it;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/an-object.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/an-object.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

module.exports = function (it) {
  if (!isObject(it)) {
    throw TypeError(String(it) + ' is not an object');
  }

  return it;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-for-each.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-for-each.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $forEach = __webpack_require__(/*! ../internals/array-iteration */ "../../../node_modules/core-js-pure/internals/array-iteration.js").forEach;

var arrayMethodIsStrict = __webpack_require__(/*! ../internals/array-method-is-strict */ "../../../node_modules/core-js-pure/internals/array-method-is-strict.js");

var STRICT_METHOD = arrayMethodIsStrict('forEach'); // `Array.prototype.forEach` method implementation
// https://tc39.es/ecma262/#sec-array.prototype.foreach

module.exports = !STRICT_METHOD ? function forEach(callbackfn
/* , thisArg */
) {
  return $forEach(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined); // eslint-disable-next-line es/no-array-prototype-foreach -- safe
} : [].forEach;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-includes.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-includes.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "../../../node_modules/core-js-pure/internals/to-indexed-object.js");

var toLength = __webpack_require__(/*! ../internals/to-length */ "../../../node_modules/core-js-pure/internals/to-length.js");

var toAbsoluteIndex = __webpack_require__(/*! ../internals/to-absolute-index */ "../../../node_modules/core-js-pure/internals/to-absolute-index.js"); // `Array.prototype.{ indexOf, includes }` methods implementation


var createMethod = function createMethod(IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIndexedObject($this);
    var length = toLength(O.length);
    var index = toAbsoluteIndex(fromIndex, length);
    var value; // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare -- NaN check

    if (IS_INCLUDES && el != el) while (length > index) {
      value = O[index++]; // eslint-disable-next-line no-self-compare -- NaN check

      if (value != value) return true; // Array#indexOf ignores holes, Array#includes - not
    } else for (; length > index; index++) {
      if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
    }
    return !IS_INCLUDES && -1;
  };
};

module.exports = {
  // `Array.prototype.includes` method
  // https://tc39.es/ecma262/#sec-array.prototype.includes
  includes: createMethod(true),
  // `Array.prototype.indexOf` method
  // https://tc39.es/ecma262/#sec-array.prototype.indexof
  indexOf: createMethod(false)
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-iteration.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-iteration.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var bind = __webpack_require__(/*! ../internals/function-bind-context */ "../../../node_modules/core-js-pure/internals/function-bind-context.js");

var IndexedObject = __webpack_require__(/*! ../internals/indexed-object */ "../../../node_modules/core-js-pure/internals/indexed-object.js");

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var toLength = __webpack_require__(/*! ../internals/to-length */ "../../../node_modules/core-js-pure/internals/to-length.js");

var arraySpeciesCreate = __webpack_require__(/*! ../internals/array-species-create */ "../../../node_modules/core-js-pure/internals/array-species-create.js");

var push = [].push; // `Array.prototype.{ forEach, map, filter, some, every, find, findIndex, filterOut }` methods implementation

var createMethod = function createMethod(TYPE) {
  var IS_MAP = TYPE == 1;
  var IS_FILTER = TYPE == 2;
  var IS_SOME = TYPE == 3;
  var IS_EVERY = TYPE == 4;
  var IS_FIND_INDEX = TYPE == 6;
  var IS_FILTER_OUT = TYPE == 7;
  var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
  return function ($this, callbackfn, that, specificCreate) {
    var O = toObject($this);
    var self = IndexedObject(O);
    var boundFunction = bind(callbackfn, that, 3);
    var length = toLength(self.length);
    var index = 0;
    var create = specificCreate || arraySpeciesCreate;
    var target = IS_MAP ? create($this, length) : IS_FILTER || IS_FILTER_OUT ? create($this, 0) : undefined;
    var value, result;

    for (; length > index; index++) {
      if (NO_HOLES || index in self) {
        value = self[index];
        result = boundFunction(value, index, O);

        if (TYPE) {
          if (IS_MAP) target[index] = result; // map
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
                push.call(target, value);
              // filter
            } else switch (TYPE) {
              case 4:
                return false;
              // every

              case 7:
                push.call(target, value);
              // filterOut
            }
        }
      }
    }

    return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
  };
};

module.exports = {
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
  // `Array.prototype.filterOut` method
  // https://github.com/tc39/proposal-array-filtering
  filterOut: createMethod(7)
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-method-has-species-support.js":
/*!***********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-method-has-species-support.js ***!
  \***********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var V8_VERSION = __webpack_require__(/*! ../internals/engine-v8-version */ "../../../node_modules/core-js-pure/internals/engine-v8-version.js");

var SPECIES = wellKnownSymbol('species');

module.exports = function (METHOD_NAME) {
  // We can't use this feature detection in V8 since it causes
  // deoptimization and serious performance degradation
  // https://github.com/zloirock/core-js/issues/677
  return V8_VERSION >= 51 || !fails(function () {
    var array = [];
    var constructor = array.constructor = {};

    constructor[SPECIES] = function () {
      return {
        foo: 1
      };
    };

    return array[METHOD_NAME](Boolean).foo !== 1;
  });
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-method-is-strict.js":
/*!*************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-method-is-strict.js ***!
  \*************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

module.exports = function (METHOD_NAME, argument) {
  var method = [][METHOD_NAME];
  return !!method && fails(function () {
    // eslint-disable-next-line no-useless-call,no-throw-literal -- required for testing
    method.call(null, argument || function () {
      throw 1;
    }, 1);
  });
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-sort.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-sort.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// TODO: use something more complex like timsort?
var floor = Math.floor;

var mergeSort = function mergeSort(array, comparefn) {
  var length = array.length;
  var middle = floor(length / 2);
  return length < 8 ? insertionSort(array, comparefn) : merge(mergeSort(array.slice(0, middle), comparefn), mergeSort(array.slice(middle), comparefn), comparefn);
};

var insertionSort = function insertionSort(array, comparefn) {
  var length = array.length;
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

  return array;
};

var merge = function merge(left, right, comparefn) {
  var llength = left.length;
  var rlength = right.length;
  var lindex = 0;
  var rindex = 0;
  var result = [];

  while (lindex < llength || rindex < rlength) {
    if (lindex < llength && rindex < rlength) {
      result.push(comparefn(left[lindex], right[rindex]) <= 0 ? left[lindex++] : right[rindex++]);
    } else {
      result.push(lindex < llength ? left[lindex++] : right[rindex++]);
    }
  }

  return result;
};

module.exports = mergeSort;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/array-species-create.js":
/*!***********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/array-species-create.js ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var isArray = __webpack_require__(/*! ../internals/is-array */ "../../../node_modules/core-js-pure/internals/is-array.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var SPECIES = wellKnownSymbol('species'); // `ArraySpeciesCreate` abstract operation
// https://tc39.es/ecma262/#sec-arrayspeciescreate

module.exports = function (originalArray, length) {
  var C;

  if (isArray(originalArray)) {
    C = originalArray.constructor; // cross-realm fallback

    if (typeof C == 'function' && (C === Array || isArray(C.prototype))) C = undefined;else if (isObject(C)) {
      C = C[SPECIES];
      if (C === null) C = undefined;
    }
  }

  return new (C === undefined ? Array : C)(length === 0 ? 0 : length);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/classof-raw.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/classof-raw.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/classof.js":
/*!**********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/classof.js ***!
  \**********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var TO_STRING_TAG_SUPPORT = __webpack_require__(/*! ../internals/to-string-tag-support */ "../../../node_modules/core-js-pure/internals/to-string-tag-support.js");

var classofRaw = __webpack_require__(/*! ../internals/classof-raw */ "../../../node_modules/core-js-pure/internals/classof-raw.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag'); // ES3 wrong here

var CORRECT_ARGUMENTS = classofRaw(function () {
  return arguments;
}()) == 'Arguments'; // fallback for IE11 Script Access Denied error

var tryGet = function tryGet(it, key) {
  try {
    return it[key];
  } catch (error) {
    /* empty */
  }
}; // getting tag from ES6+ `Object.prototype.toString`


module.exports = TO_STRING_TAG_SUPPORT ? classofRaw : function (it) {
  var O, tag, result;
  return it === undefined ? 'Undefined' : it === null ? 'Null' // @@toStringTag case
  : typeof (tag = tryGet(O = Object(it), TO_STRING_TAG)) == 'string' ? tag // builtinTag case
  : CORRECT_ARGUMENTS ? classofRaw(O) // ES3 arguments fallback
  : (result = classofRaw(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : result;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/correct-prototype-getter.js":
/*!***************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/correct-prototype-getter.js ***!
  \***************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

module.exports = !fails(function () {
  function F() {
    /* empty */
  }

  F.prototype.constructor = null; // eslint-disable-next-line es/no-object-getprototypeof -- required for testing

  return Object.getPrototypeOf(new F()) !== F.prototype;
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/create-iterator-constructor.js":
/*!******************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/create-iterator-constructor.js ***!
  \******************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var IteratorPrototype = __webpack_require__(/*! ../internals/iterators-core */ "../../../node_modules/core-js-pure/internals/iterators-core.js").IteratorPrototype;

var create = __webpack_require__(/*! ../internals/object-create */ "../../../node_modules/core-js-pure/internals/object-create.js");

var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js");

var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "../../../node_modules/core-js-pure/internals/set-to-string-tag.js");

var Iterators = __webpack_require__(/*! ../internals/iterators */ "../../../node_modules/core-js-pure/internals/iterators.js");

var returnThis = function returnThis() {
  return this;
};

module.exports = function (IteratorConstructor, NAME, next) {
  var TO_STRING_TAG = NAME + ' Iterator';
  IteratorConstructor.prototype = create(IteratorPrototype, {
    next: createPropertyDescriptor(1, next)
  });
  setToStringTag(IteratorConstructor, TO_STRING_TAG, false, true);
  Iterators[TO_STRING_TAG] = returnThis;
  return IteratorConstructor;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js":
/*!*********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/create-non-enumerable-property.js ***!
  \*********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "../../../node_modules/core-js-pure/internals/object-define-property.js");

var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js");

module.exports = DESCRIPTORS ? function (object, key, value) {
  return definePropertyModule.f(object, key, createPropertyDescriptor(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js":
/*!*****************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/create-property-descriptor.js ***!
  \*****************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/create-property.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/create-property.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var toPrimitive = __webpack_require__(/*! ../internals/to-primitive */ "../../../node_modules/core-js-pure/internals/to-primitive.js");

var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "../../../node_modules/core-js-pure/internals/object-define-property.js");

var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js");

module.exports = function (object, key, value) {
  var propertyKey = toPrimitive(key);
  if (propertyKey in object) definePropertyModule.f(object, propertyKey, createPropertyDescriptor(0, value));else object[propertyKey] = value;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/define-iterator.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/define-iterator.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var createIteratorConstructor = __webpack_require__(/*! ../internals/create-iterator-constructor */ "../../../node_modules/core-js-pure/internals/create-iterator-constructor.js");

var getPrototypeOf = __webpack_require__(/*! ../internals/object-get-prototype-of */ "../../../node_modules/core-js-pure/internals/object-get-prototype-of.js");

var setPrototypeOf = __webpack_require__(/*! ../internals/object-set-prototype-of */ "../../../node_modules/core-js-pure/internals/object-set-prototype-of.js");

var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "../../../node_modules/core-js-pure/internals/set-to-string-tag.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var redefine = __webpack_require__(/*! ../internals/redefine */ "../../../node_modules/core-js-pure/internals/redefine.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "../../../node_modules/core-js-pure/internals/is-pure.js");

var Iterators = __webpack_require__(/*! ../internals/iterators */ "../../../node_modules/core-js-pure/internals/iterators.js");

var IteratorsCore = __webpack_require__(/*! ../internals/iterators-core */ "../../../node_modules/core-js-pure/internals/iterators-core.js");

var IteratorPrototype = IteratorsCore.IteratorPrototype;
var BUGGY_SAFARI_ITERATORS = IteratorsCore.BUGGY_SAFARI_ITERATORS;
var ITERATOR = wellKnownSymbol('iterator');
var KEYS = 'keys';
var VALUES = 'values';
var ENTRIES = 'entries';

var returnThis = function returnThis() {
  return this;
};

module.exports = function (Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
  createIteratorConstructor(IteratorConstructor, NAME, next);

  var getIterationMethod = function getIterationMethod(KIND) {
    if (KIND === DEFAULT && defaultIterator) return defaultIterator;
    if (!BUGGY_SAFARI_ITERATORS && KIND in IterablePrototype) return IterablePrototype[KIND];

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
        return function entries() {
          return new IteratorConstructor(this, KIND);
        };
    }

    return function () {
      return new IteratorConstructor(this);
    };
  };

  var TO_STRING_TAG = NAME + ' Iterator';
  var INCORRECT_VALUES_NAME = false;
  var IterablePrototype = Iterable.prototype;
  var nativeIterator = IterablePrototype[ITERATOR] || IterablePrototype['@@iterator'] || DEFAULT && IterablePrototype[DEFAULT];
  var defaultIterator = !BUGGY_SAFARI_ITERATORS && nativeIterator || getIterationMethod(DEFAULT);
  var anyNativeIterator = NAME == 'Array' ? IterablePrototype.entries || nativeIterator : nativeIterator;
  var CurrentIteratorPrototype, methods, KEY; // fix native

  if (anyNativeIterator) {
    CurrentIteratorPrototype = getPrototypeOf(anyNativeIterator.call(new Iterable()));

    if (IteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
      if (!IS_PURE && getPrototypeOf(CurrentIteratorPrototype) !== IteratorPrototype) {
        if (setPrototypeOf) {
          setPrototypeOf(CurrentIteratorPrototype, IteratorPrototype);
        } else if (typeof CurrentIteratorPrototype[ITERATOR] != 'function') {
          createNonEnumerableProperty(CurrentIteratorPrototype, ITERATOR, returnThis);
        }
      } // Set @@toStringTag to native iterators


      setToStringTag(CurrentIteratorPrototype, TO_STRING_TAG, true, true);
      if (IS_PURE) Iterators[TO_STRING_TAG] = returnThis;
    }
  } // fix Array.prototype.{ values, @@iterator }.name in V8 / FF


  if (DEFAULT == VALUES && nativeIterator && nativeIterator.name !== VALUES) {
    INCORRECT_VALUES_NAME = true;

    defaultIterator = function values() {
      return nativeIterator.call(this);
    };
  } // define iterator


  if ((!IS_PURE || FORCED) && IterablePrototype[ITERATOR] !== defaultIterator) {
    createNonEnumerableProperty(IterablePrototype, ITERATOR, defaultIterator);
  }

  Iterators[NAME] = defaultIterator; // export additional methods

  if (DEFAULT) {
    methods = {
      values: getIterationMethod(VALUES),
      keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
      entries: getIterationMethod(ENTRIES)
    };
    if (FORCED) for (KEY in methods) {
      if (BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
        redefine(IterablePrototype, KEY, methods[KEY]);
      }
    } else $({
      target: NAME,
      proto: true,
      forced: BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME
    }, methods);
  }

  return methods;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/descriptors.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/descriptors.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js"); // Detect IE8's incomplete defineProperty implementation


module.exports = !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty({}, 1, {
    get: function get() {
      return 7;
    }
  })[1] != 7;
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/document-create-element.js":
/*!**************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/document-create-element.js ***!
  \**************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var document = global.document; // typeof document.createElement is 'object' in old IE

var EXISTS = isObject(document) && isObject(document.createElement);

module.exports = function (it) {
  return EXISTS ? document.createElement(it) : {};
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/dom-iterables.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/dom-iterables.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// iterable DOM collections
// flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
module.exports = {
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

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/engine-ff-version.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/engine-ff-version.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var userAgent = __webpack_require__(/*! ../internals/engine-user-agent */ "../../../node_modules/core-js-pure/internals/engine-user-agent.js");

var firefox = userAgent.match(/firefox\/(\d+)/i);
module.exports = !!firefox && +firefox[1];

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/engine-is-ie-or-edge.js":
/*!***********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/engine-is-ie-or-edge.js ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var UA = __webpack_require__(/*! ../internals/engine-user-agent */ "../../../node_modules/core-js-pure/internals/engine-user-agent.js");

module.exports = /MSIE|Trident/.test(UA);

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/engine-user-agent.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/engine-user-agent.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "../../../node_modules/core-js-pure/internals/get-built-in.js");

module.exports = getBuiltIn('navigator', 'userAgent') || '';

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/engine-v8-version.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/engine-v8-version.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var userAgent = __webpack_require__(/*! ../internals/engine-user-agent */ "../../../node_modules/core-js-pure/internals/engine-user-agent.js");

var process = global.process;
var versions = process && process.versions;
var v8 = versions && versions.v8;
var match, version;

if (v8) {
  match = v8.split('.');
  version = match[0] < 4 ? 1 : match[0] + match[1];
} else if (userAgent) {
  match = userAgent.match(/Edge\/(\d+)/);

  if (!match || match[1] >= 74) {
    match = userAgent.match(/Chrome\/(\d+)/);
    if (match) version = match[1];
  }
}

module.exports = version && +version;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/engine-webkit-version.js":
/*!************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/engine-webkit-version.js ***!
  \************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var userAgent = __webpack_require__(/*! ../internals/engine-user-agent */ "../../../node_modules/core-js-pure/internals/engine-user-agent.js");

var webkit = userAgent.match(/AppleWebKit\/(\d+)\./);
module.exports = !!webkit && +webkit[1];

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/entry-virtual.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/entry-virtual.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var path = __webpack_require__(/*! ../internals/path */ "../../../node_modules/core-js-pure/internals/path.js");

module.exports = function (CONSTRUCTOR) {
  return path[CONSTRUCTOR + 'Prototype'];
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/enum-bug-keys.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/enum-bug-keys.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// IE8- don't enum bug keys
module.exports = ['constructor', 'hasOwnProperty', 'isPrototypeOf', 'propertyIsEnumerable', 'toLocaleString', 'toString', 'valueOf'];

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/export.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/export.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var getOwnPropertyDescriptor = __webpack_require__(/*! ../internals/object-get-own-property-descriptor */ "../../../node_modules/core-js-pure/internals/object-get-own-property-descriptor.js").f;

var isForced = __webpack_require__(/*! ../internals/is-forced */ "../../../node_modules/core-js-pure/internals/is-forced.js");

var path = __webpack_require__(/*! ../internals/path */ "../../../node_modules/core-js-pure/internals/path.js");

var bind = __webpack_require__(/*! ../internals/function-bind-context */ "../../../node_modules/core-js-pure/internals/function-bind-context.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var wrapConstructor = function wrapConstructor(NativeConstructor) {
  var Wrapper = function Wrapper(a, b, c) {
    if (this instanceof NativeConstructor) {
      switch (arguments.length) {
        case 0:
          return new NativeConstructor();

        case 1:
          return new NativeConstructor(a);

        case 2:
          return new NativeConstructor(a, b);
      }

      return new NativeConstructor(a, b, c);
    }

    return NativeConstructor.apply(this, arguments);
  };

  Wrapper.prototype = NativeConstructor.prototype;
  return Wrapper;
};
/*
  options.target      - name of the target object
  options.global      - target is the global object
  options.stat        - export as static methods of target
  options.proto       - export as prototype methods of target
  options.real        - real prototype method for the `pure` version
  options.forced      - export even if the native feature is available
  options.bind        - bind methods to the target, required for the `pure` version
  options.wrap        - wrap constructors to preventing global pollution, required for the `pure` version
  options.unsafe      - use the simple assignment of property instead of delete + defineProperty
  options.sham        - add a flag to not completely full polyfills
  options.enumerable  - export as enumerable property
  options.noTargetGet - prevent calling a getter on target
*/


module.exports = function (options, source) {
  var TARGET = options.target;
  var GLOBAL = options.global;
  var STATIC = options.stat;
  var PROTO = options.proto;
  var nativeSource = GLOBAL ? global : STATIC ? global[TARGET] : (global[TARGET] || {}).prototype;
  var target = GLOBAL ? path : path[TARGET] || (path[TARGET] = {});
  var targetPrototype = target.prototype;
  var FORCED, USE_NATIVE, VIRTUAL_PROTOTYPE;
  var key, sourceProperty, targetProperty, nativeProperty, resultProperty, descriptor;

  for (key in source) {
    FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced); // contains in native

    USE_NATIVE = !FORCED && nativeSource && has(nativeSource, key);
    targetProperty = target[key];
    if (USE_NATIVE) if (options.noTargetGet) {
      descriptor = getOwnPropertyDescriptor(nativeSource, key);
      nativeProperty = descriptor && descriptor.value;
    } else nativeProperty = nativeSource[key]; // export native or implementation

    sourceProperty = USE_NATIVE && nativeProperty ? nativeProperty : source[key];
    if (USE_NATIVE && typeof targetProperty === typeof sourceProperty) continue; // bind timers to global for call from export context

    if (options.bind && USE_NATIVE) resultProperty = bind(sourceProperty, global); // wrap global constructors for prevent changs in this version
    else if (options.wrap && USE_NATIVE) resultProperty = wrapConstructor(sourceProperty); // make static versions for prototype methods
      else if (PROTO && typeof sourceProperty == 'function') resultProperty = bind(Function.call, sourceProperty); // default case
        else resultProperty = sourceProperty; // add a flag to not completely full polyfills

    if (options.sham || sourceProperty && sourceProperty.sham || targetProperty && targetProperty.sham) {
      createNonEnumerableProperty(resultProperty, 'sham', true);
    }

    target[key] = resultProperty;

    if (PROTO) {
      VIRTUAL_PROTOTYPE = TARGET + 'Prototype';

      if (!has(path, VIRTUAL_PROTOTYPE)) {
        createNonEnumerableProperty(path, VIRTUAL_PROTOTYPE, {});
      } // export virtual prototype methods


      path[VIRTUAL_PROTOTYPE][key] = sourceProperty; // export real prototype methods

      if (options.real && targetPrototype && !targetPrototype[key]) {
        createNonEnumerableProperty(targetPrototype, key, sourceProperty);
      }
    }
  }
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/fails.js":
/*!********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/fails.js ***!
  \********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (error) {
    return true;
  }
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/function-bind-context.js":
/*!************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/function-bind-context.js ***!
  \************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var aFunction = __webpack_require__(/*! ../internals/a-function */ "../../../node_modules/core-js-pure/internals/a-function.js"); // optional / simple context binding


module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;

  switch (length) {
    case 0:
      return function () {
        return fn.call(that);
      };

    case 1:
      return function (a) {
        return fn.call(that, a);
      };

    case 2:
      return function (a, b) {
        return fn.call(that, a, b);
      };

    case 3:
      return function (a, b, c) {
        return fn.call(that, a, b, c);
      };
  }

  return function ()
  /* ...args */
  {
    return fn.apply(that, arguments);
  };
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/function-bind.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/function-bind.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var aFunction = __webpack_require__(/*! ../internals/a-function */ "../../../node_modules/core-js-pure/internals/a-function.js");

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var slice = [].slice;
var factories = {};

var construct = function construct(C, argsLength, args) {
  if (!(argsLength in factories)) {
    for (var list = [], i = 0; i < argsLength; i++) {
      list[i] = 'a[' + i + ']';
    } // eslint-disable-next-line no-new-func -- we have no proper alternatives, IE8- only


    factories[argsLength] = Function('C,a', 'return new C(' + list.join(',') + ')');
  }

  return factories[argsLength](C, args);
}; // `Function.prototype.bind` method implementation
// https://tc39.es/ecma262/#sec-function.prototype.bind


module.exports = Function.bind || function bind(that
/* , ...args */
) {
  var fn = aFunction(this);
  var partArgs = slice.call(arguments, 1);

  var boundFunction = function bound()
  /* args... */
  {
    var args = partArgs.concat(slice.call(arguments));
    return this instanceof boundFunction ? construct(fn, args.length, args) : fn.apply(that, args);
  };

  if (isObject(fn.prototype)) boundFunction.prototype = fn.prototype;
  return boundFunction;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/get-built-in.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/get-built-in.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var path = __webpack_require__(/*! ../internals/path */ "../../../node_modules/core-js-pure/internals/path.js");

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var aFunction = function aFunction(variable) {
  return typeof variable == 'function' ? variable : undefined;
};

module.exports = function (namespace, method) {
  return arguments.length < 2 ? aFunction(path[namespace]) || aFunction(global[namespace]) : path[namespace] && path[namespace][method] || global[namespace] && global[namespace][method];
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/get-iterator-method.js":
/*!**********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/get-iterator-method.js ***!
  \**********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__(/*! ../internals/classof */ "../../../node_modules/core-js-pure/internals/classof.js");

var Iterators = __webpack_require__(/*! ../internals/iterators */ "../../../node_modules/core-js-pure/internals/iterators.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var ITERATOR = wellKnownSymbol('iterator');

module.exports = function (it) {
  if (it != undefined) return it[ITERATOR] || it['@@iterator'] || Iterators[classof(it)];
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/get-iterator.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/get-iterator.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var getIteratorMethod = __webpack_require__(/*! ../internals/get-iterator-method */ "../../../node_modules/core-js-pure/internals/get-iterator-method.js");

module.exports = function (it) {
  var iteratorMethod = getIteratorMethod(it);

  if (typeof iteratorMethod != 'function') {
    throw TypeError(String(it) + ' is not iterable');
  }

  return anObject(iteratorMethod.call(it));
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/global.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/global.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var check = function check(it) {
  return it && it.Math == Math && it;
}; // https://github.com/zloirock/core-js/issues/86#issuecomment-115759028


module.exports = // eslint-disable-next-line es/no-global-this -- safe
check(typeof globalThis == 'object' && globalThis) || check(typeof window == 'object' && window) || // eslint-disable-next-line no-restricted-globals -- safe
check(typeof self == 'object' && self) || check(typeof global == 'object' && global) || // eslint-disable-next-line no-new-func -- fallback
function () {
  return this;
}() || Function('return this')();
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../webpack/buildin/global.js */ "../../../node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/has.js":
/*!******************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/has.js ***!
  \******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var hasOwnProperty = {}.hasOwnProperty;

module.exports = Object.hasOwn || function hasOwn(it, key) {
  return hasOwnProperty.call(toObject(it), key);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/hidden-keys.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/hidden-keys.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = {};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/html.js":
/*!*******************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/html.js ***!
  \*******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "../../../node_modules/core-js-pure/internals/get-built-in.js");

module.exports = getBuiltIn('document', 'documentElement');

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/ie8-dom-define.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/ie8-dom-define.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var createElement = __webpack_require__(/*! ../internals/document-create-element */ "../../../node_modules/core-js-pure/internals/document-create-element.js"); // Thank's IE8 for his funny defineProperty


module.exports = !DESCRIPTORS && !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- requied for testing
  return Object.defineProperty(createElement('div'), 'a', {
    get: function get() {
      return 7;
    }
  }).a != 7;
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/indexed-object.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/indexed-object.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var classof = __webpack_require__(/*! ../internals/classof-raw */ "../../../node_modules/core-js-pure/internals/classof-raw.js");

var split = ''.split; // fallback for non-array-like ES3 and non-enumerable old V8 strings

module.exports = fails(function () {
  // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
  // eslint-disable-next-line no-prototype-builtins -- safe
  return !Object('z').propertyIsEnumerable(0);
}) ? function (it) {
  return classof(it) == 'String' ? split.call(it, '') : Object(it);
} : Object;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/inspect-source.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/inspect-source.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__(/*! ../internals/shared-store */ "../../../node_modules/core-js-pure/internals/shared-store.js");

var functionToString = Function.toString; // this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper

if (typeof store.inspectSource != 'function') {
  store.inspectSource = function (it) {
    return functionToString.call(it);
  };
}

module.exports = store.inspectSource;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/internal-state.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/internal-state.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var NATIVE_WEAK_MAP = __webpack_require__(/*! ../internals/native-weak-map */ "../../../node_modules/core-js-pure/internals/native-weak-map.js");

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var objectHas = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var shared = __webpack_require__(/*! ../internals/shared-store */ "../../../node_modules/core-js-pure/internals/shared-store.js");

var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "../../../node_modules/core-js-pure/internals/shared-key.js");

var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "../../../node_modules/core-js-pure/internals/hidden-keys.js");

var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
var WeakMap = global.WeakMap;
var set, get, has;

var enforce = function enforce(it) {
  return has(it) ? get(it) : set(it, {});
};

var getterFor = function getterFor(TYPE) {
  return function (it) {
    var state;

    if (!isObject(it) || (state = get(it)).type !== TYPE) {
      throw TypeError('Incompatible receiver, ' + TYPE + ' required');
    }

    return state;
  };
};

if (NATIVE_WEAK_MAP || shared.state) {
  var store = shared.state || (shared.state = new WeakMap());
  var wmget = store.get;
  var wmhas = store.has;
  var wmset = store.set;

  set = function set(it, metadata) {
    if (wmhas.call(store, it)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    wmset.call(store, it, metadata);
    return metadata;
  };

  get = function get(it) {
    return wmget.call(store, it) || {};
  };

  has = function has(it) {
    return wmhas.call(store, it);
  };
} else {
  var STATE = sharedKey('state');
  hiddenKeys[STATE] = true;

  set = function set(it, metadata) {
    if (objectHas(it, STATE)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    createNonEnumerableProperty(it, STATE, metadata);
    return metadata;
  };

  get = function get(it) {
    return objectHas(it, STATE) ? it[STATE] : {};
  };

  has = function has(it) {
    return objectHas(it, STATE);
  };
}

module.exports = {
  set: set,
  get: get,
  has: has,
  enforce: enforce,
  getterFor: getterFor
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/is-array.js":
/*!***********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/is-array.js ***!
  \***********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__(/*! ../internals/classof-raw */ "../../../node_modules/core-js-pure/internals/classof-raw.js"); // `IsArray` abstract operation
// https://tc39.es/ecma262/#sec-isarray
// eslint-disable-next-line es/no-array-isarray -- safe


module.exports = Array.isArray || function isArray(arg) {
  return classof(arg) == 'Array';
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/is-forced.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/is-forced.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var replacement = /#|\.prototype\./;

var isForced = function isForced(feature, detection) {
  var value = data[normalize(feature)];
  return value == POLYFILL ? true : value == NATIVE ? false : typeof detection == 'function' ? fails(detection) : !!detection;
};

var normalize = isForced.normalize = function (string) {
  return String(string).replace(replacement, '.').toLowerCase();
};

var data = isForced.data = {};
var NATIVE = isForced.NATIVE = 'N';
var POLYFILL = isForced.POLYFILL = 'P';
module.exports = isForced;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/is-object.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/is-object.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/is-pure.js":
/*!**********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/is-pure.js ***!
  \**********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = true;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/iterators-core.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/iterators-core.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var getPrototypeOf = __webpack_require__(/*! ../internals/object-get-prototype-of */ "../../../node_modules/core-js-pure/internals/object-get-prototype-of.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "../../../node_modules/core-js-pure/internals/is-pure.js");

var ITERATOR = wellKnownSymbol('iterator');
var BUGGY_SAFARI_ITERATORS = false;

var returnThis = function returnThis() {
  return this;
}; // `%IteratorPrototype%` object
// https://tc39.es/ecma262/#sec-%iteratorprototype%-object


var IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;
/* eslint-disable es/no-array-prototype-keys -- safe */

if ([].keys) {
  arrayIterator = [].keys(); // Safari 8 has buggy iterators w/o `next`

  if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;else {
    PrototypeOfArrayIteratorPrototype = getPrototypeOf(getPrototypeOf(arrayIterator));
    if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;
  }
}

var NEW_ITERATOR_PROTOTYPE = IteratorPrototype == undefined || fails(function () {
  var test = {}; // FF44- legacy iterators case

  return IteratorPrototype[ITERATOR].call(test) !== test;
});
if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype = {}; // `%IteratorPrototype%[@@iterator]()` method
// https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator

if ((!IS_PURE || NEW_ITERATOR_PROTOTYPE) && !has(IteratorPrototype, ITERATOR)) {
  createNonEnumerableProperty(IteratorPrototype, ITERATOR, returnThis);
}

module.exports = {
  IteratorPrototype: IteratorPrototype,
  BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/iterators.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/iterators.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = {};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/native-symbol.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/native-symbol.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* eslint-disable es/no-symbol -- required for testing */
var V8_VERSION = __webpack_require__(/*! ../internals/engine-v8-version */ "../../../node_modules/core-js-pure/internals/engine-v8-version.js");

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js"); // eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing


module.exports = !!Object.getOwnPropertySymbols && !fails(function () {
  var symbol = Symbol(); // Chrome 38 Symbol has incorrect toString conversion
  // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances

  return !String(symbol) || !(Object(symbol) instanceof Symbol) || // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
  !Symbol.sham && V8_VERSION && V8_VERSION < 41;
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/native-url.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/native-url.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "../../../node_modules/core-js-pure/internals/is-pure.js");

var ITERATOR = wellKnownSymbol('iterator');
module.exports = !fails(function () {
  var url = new URL('b?a=1&b=2&c=3', 'http://a');
  var searchParams = url.searchParams;
  var result = '';
  url.pathname = 'c%20d';
  searchParams.forEach(function (value, key) {
    searchParams['delete']('b');
    result += key + value;
  });
  return IS_PURE && !url.toJSON || !searchParams.sort || url.href !== 'http://a/c%20d?a=1&c=3' || searchParams.get('c') !== '3' || String(new URLSearchParams('?a=1')) !== 'a=1' || !searchParams[ITERATOR] // throws in Edge
  || new URL('https://a@b').username !== 'a' || new URLSearchParams(new URLSearchParams('a=b')).get('a') !== 'b' // not punycoded in Edge
  || new URL('http://тест').host !== 'xn--e1aybc' // not escaped in Chrome 62-
  || new URL('http://a#б').hash !== '#%D0%B1' // fails in Chrome 66-
  || result !== 'a1c3' // throws in Safari
  || new URL('http://x', undefined).host !== 'x';
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/native-weak-map.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/native-weak-map.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var inspectSource = __webpack_require__(/*! ../internals/inspect-source */ "../../../node_modules/core-js-pure/internals/inspect-source.js");

var WeakMap = global.WeakMap;
module.exports = typeof WeakMap === 'function' && /native code/.test(inspectSource(WeakMap));

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-assign.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-assign.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var objectKeys = __webpack_require__(/*! ../internals/object-keys */ "../../../node_modules/core-js-pure/internals/object-keys.js");

var getOwnPropertySymbolsModule = __webpack_require__(/*! ../internals/object-get-own-property-symbols */ "../../../node_modules/core-js-pure/internals/object-get-own-property-symbols.js");

var propertyIsEnumerableModule = __webpack_require__(/*! ../internals/object-property-is-enumerable */ "../../../node_modules/core-js-pure/internals/object-property-is-enumerable.js");

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var IndexedObject = __webpack_require__(/*! ../internals/indexed-object */ "../../../node_modules/core-js-pure/internals/indexed-object.js"); // eslint-disable-next-line es/no-object-assign -- safe


var $assign = Object.assign; // eslint-disable-next-line es/no-object-defineproperty -- required for testing

var defineProperty = Object.defineProperty; // `Object.assign` method
// https://tc39.es/ecma262/#sec-object.assign

module.exports = !$assign || fails(function () {
  // should have correct order of operations (Edge bug)
  if (DESCRIPTORS && $assign({
    b: 1
  }, $assign(defineProperty({}, 'a', {
    enumerable: true,
    get: function get() {
      defineProperty(this, 'b', {
        value: 3,
        enumerable: false
      });
    }
  }), {
    b: 2
  })).b !== 1) return true; // should work with symbols and should have deterministic property order (V8 bug)

  var A = {};
  var B = {}; // eslint-disable-next-line es/no-symbol -- safe

  var symbol = Symbol();
  var alphabet = 'abcdefghijklmnopqrst';
  A[symbol] = 7;
  alphabet.split('').forEach(function (chr) {
    B[chr] = chr;
  });
  return $assign({}, A)[symbol] != 7 || objectKeys($assign({}, B)).join('') != alphabet;
}) ? function assign(target, source) {
  // eslint-disable-line no-unused-vars -- required for `.length`
  var T = toObject(target);
  var argumentsLength = arguments.length;
  var index = 1;
  var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
  var propertyIsEnumerable = propertyIsEnumerableModule.f;

  while (argumentsLength > index) {
    var S = IndexedObject(arguments[index++]);
    var keys = getOwnPropertySymbols ? objectKeys(S).concat(getOwnPropertySymbols(S)) : objectKeys(S);
    var length = keys.length;
    var j = 0;
    var key;

    while (length > j) {
      key = keys[j++];
      if (!DESCRIPTORS || propertyIsEnumerable.call(S, key)) T[key] = S[key];
    }
  }

  return T;
} : $assign;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-create.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-create.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var defineProperties = __webpack_require__(/*! ../internals/object-define-properties */ "../../../node_modules/core-js-pure/internals/object-define-properties.js");

var enumBugKeys = __webpack_require__(/*! ../internals/enum-bug-keys */ "../../../node_modules/core-js-pure/internals/enum-bug-keys.js");

var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "../../../node_modules/core-js-pure/internals/hidden-keys.js");

var html = __webpack_require__(/*! ../internals/html */ "../../../node_modules/core-js-pure/internals/html.js");

var documentCreateElement = __webpack_require__(/*! ../internals/document-create-element */ "../../../node_modules/core-js-pure/internals/document-create-element.js");

var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "../../../node_modules/core-js-pure/internals/shared-key.js");

var GT = '>';
var LT = '<';
var PROTOTYPE = 'prototype';
var SCRIPT = 'script';
var IE_PROTO = sharedKey('IE_PROTO');

var EmptyConstructor = function EmptyConstructor() {
  /* empty */
};

var scriptTag = function scriptTag(content) {
  return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
}; // Create object with fake `null` prototype: use ActiveX Object with cleared prototype


var NullProtoObjectViaActiveX = function NullProtoObjectViaActiveX(activeXDocument) {
  activeXDocument.write(scriptTag(''));
  activeXDocument.close();
  var temp = activeXDocument.parentWindow.Object;
  activeXDocument = null; // avoid memory leak

  return temp;
}; // Create object with fake `null` prototype: use iframe Object with cleared prototype


var NullProtoObjectViaIFrame = function NullProtoObjectViaIFrame() {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = documentCreateElement('iframe');
  var JS = 'java' + SCRIPT + ':';
  var iframeDocument;
  iframe.style.display = 'none';
  html.appendChild(iframe); // https://github.com/zloirock/core-js/issues/475

  iframe.src = String(JS);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(scriptTag('document.F=Object'));
  iframeDocument.close();
  return iframeDocument.F;
}; // Check for document.domain and active x support
// No need to use active x approach when document.domain is not set
// see https://github.com/es-shims/es5-shim/issues/150
// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
// avoid IE GC bug


var activeXDocument;

var _NullProtoObject = function NullProtoObject() {
  try {
    /* global ActiveXObject -- old IE */
    activeXDocument = document.domain && new ActiveXObject('htmlfile');
  } catch (error) {
    /* ignore */
  }

  _NullProtoObject = activeXDocument ? NullProtoObjectViaActiveX(activeXDocument) : NullProtoObjectViaIFrame();
  var length = enumBugKeys.length;

  while (length--) {
    delete _NullProtoObject[PROTOTYPE][enumBugKeys[length]];
  }

  return _NullProtoObject();
};

hiddenKeys[IE_PROTO] = true; // `Object.create` method
// https://tc39.es/ecma262/#sec-object.create

module.exports = Object.create || function create(O, Properties) {
  var result;

  if (O !== null) {
    EmptyConstructor[PROTOTYPE] = anObject(O);
    result = new EmptyConstructor();
    EmptyConstructor[PROTOTYPE] = null; // add "__proto__" for Object.getPrototypeOf polyfill

    result[IE_PROTO] = O;
  } else result = _NullProtoObject();

  return Properties === undefined ? result : defineProperties(result, Properties);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-define-properties.js":
/*!***************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-define-properties.js ***!
  \***************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "../../../node_modules/core-js-pure/internals/object-define-property.js");

var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var objectKeys = __webpack_require__(/*! ../internals/object-keys */ "../../../node_modules/core-js-pure/internals/object-keys.js"); // `Object.defineProperties` method
// https://tc39.es/ecma262/#sec-object.defineproperties
// eslint-disable-next-line es/no-object-defineproperties -- safe


module.exports = DESCRIPTORS ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var keys = objectKeys(Properties);
  var length = keys.length;
  var index = 0;
  var key;

  while (length > index) {
    definePropertyModule.f(O, key = keys[index++], Properties[key]);
  }

  return O;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-define-property.js":
/*!*************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-define-property.js ***!
  \*************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var IE8_DOM_DEFINE = __webpack_require__(/*! ../internals/ie8-dom-define */ "../../../node_modules/core-js-pure/internals/ie8-dom-define.js");

var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var toPrimitive = __webpack_require__(/*! ../internals/to-primitive */ "../../../node_modules/core-js-pure/internals/to-primitive.js"); // eslint-disable-next-line es/no-object-defineproperty -- safe


var $defineProperty = Object.defineProperty; // `Object.defineProperty` method
// https://tc39.es/ecma262/#sec-object.defineproperty

exports.f = DESCRIPTORS ? $defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return $defineProperty(O, P, Attributes);
  } catch (error) {
    /* empty */
  }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-get-own-property-descriptor.js":
/*!*************************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-get-own-property-descriptor.js ***!
  \*************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "../../../node_modules/core-js-pure/internals/descriptors.js");

var propertyIsEnumerableModule = __webpack_require__(/*! ../internals/object-property-is-enumerable */ "../../../node_modules/core-js-pure/internals/object-property-is-enumerable.js");

var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js");

var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "../../../node_modules/core-js-pure/internals/to-indexed-object.js");

var toPrimitive = __webpack_require__(/*! ../internals/to-primitive */ "../../../node_modules/core-js-pure/internals/to-primitive.js");

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var IE8_DOM_DEFINE = __webpack_require__(/*! ../internals/ie8-dom-define */ "../../../node_modules/core-js-pure/internals/ie8-dom-define.js"); // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe


var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor; // `Object.getOwnPropertyDescriptor` method
// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor

exports.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
  O = toIndexedObject(O);
  P = toPrimitive(P, true);
  if (IE8_DOM_DEFINE) try {
    return $getOwnPropertyDescriptor(O, P);
  } catch (error) {
    /* empty */
  }
  if (has(O, P)) return createPropertyDescriptor(!propertyIsEnumerableModule.f.call(O, P), O[P]);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-get-own-property-symbols.js":
/*!**********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-get-own-property-symbols.js ***!
  \**********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
exports.f = Object.getOwnPropertySymbols;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-get-prototype-of.js":
/*!**************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-get-prototype-of.js ***!
  \**************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "../../../node_modules/core-js-pure/internals/shared-key.js");

var CORRECT_PROTOTYPE_GETTER = __webpack_require__(/*! ../internals/correct-prototype-getter */ "../../../node_modules/core-js-pure/internals/correct-prototype-getter.js");

var IE_PROTO = sharedKey('IE_PROTO');
var ObjectPrototype = Object.prototype; // `Object.getPrototypeOf` method
// https://tc39.es/ecma262/#sec-object.getprototypeof
// eslint-disable-next-line es/no-object-getprototypeof -- safe

module.exports = CORRECT_PROTOTYPE_GETTER ? Object.getPrototypeOf : function (O) {
  O = toObject(O);
  if (has(O, IE_PROTO)) return O[IE_PROTO];

  if (typeof O.constructor == 'function' && O instanceof O.constructor) {
    return O.constructor.prototype;
  }

  return O instanceof Object ? ObjectPrototype : null;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-keys-internal.js":
/*!***********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-keys-internal.js ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "../../../node_modules/core-js-pure/internals/to-indexed-object.js");

var indexOf = __webpack_require__(/*! ../internals/array-includes */ "../../../node_modules/core-js-pure/internals/array-includes.js").indexOf;

var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "../../../node_modules/core-js-pure/internals/hidden-keys.js");

module.exports = function (object, names) {
  var O = toIndexedObject(object);
  var i = 0;
  var result = [];
  var key;

  for (key in O) {
    !has(hiddenKeys, key) && has(O, key) && result.push(key);
  } // Don't enum bug & hidden keys


  while (names.length > i) {
    if (has(O, key = names[i++])) {
      ~indexOf(result, key) || result.push(key);
    }
  }

  return result;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-keys.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-keys.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var internalObjectKeys = __webpack_require__(/*! ../internals/object-keys-internal */ "../../../node_modules/core-js-pure/internals/object-keys-internal.js");

var enumBugKeys = __webpack_require__(/*! ../internals/enum-bug-keys */ "../../../node_modules/core-js-pure/internals/enum-bug-keys.js"); // `Object.keys` method
// https://tc39.es/ecma262/#sec-object.keys
// eslint-disable-next-line es/no-object-keys -- safe


module.exports = Object.keys || function keys(O) {
  return internalObjectKeys(O, enumBugKeys);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-property-is-enumerable.js":
/*!********************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-property-is-enumerable.js ***!
  \********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $propertyIsEnumerable = {}.propertyIsEnumerable; // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe

var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor; // Nashorn ~ JDK8 bug

var NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({
  1: 2
}, 1); // `Object.prototype.propertyIsEnumerable` method implementation
// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable

exports.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
  var descriptor = getOwnPropertyDescriptor(this, V);
  return !!descriptor && descriptor.enumerable;
} : $propertyIsEnumerable;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-set-prototype-of.js":
/*!**************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-set-prototype-of.js ***!
  \**************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* eslint-disable no-proto -- safe */
var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var aPossiblePrototype = __webpack_require__(/*! ../internals/a-possible-prototype */ "../../../node_modules/core-js-pure/internals/a-possible-prototype.js"); // `Object.setPrototypeOf` method
// https://tc39.es/ecma262/#sec-object.setprototypeof
// Works with __proto__ only. Old v8 can't work with null proto objects.
// eslint-disable-next-line es/no-object-setprototypeof -- safe


module.exports = Object.setPrototypeOf || ('__proto__' in {} ? function () {
  var CORRECT_SETTER = false;
  var test = {};
  var setter;

  try {
    // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
    setter = Object.getOwnPropertyDescriptor(Object.prototype, '__proto__').set;
    setter.call(test, []);
    CORRECT_SETTER = test instanceof Array;
  } catch (error) {
    /* empty */
  }

  return function setPrototypeOf(O, proto) {
    anObject(O);
    aPossiblePrototype(proto);
    if (CORRECT_SETTER) setter.call(O, proto);else O.__proto__ = proto;
    return O;
  };
}() : undefined);

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/object-to-string.js":
/*!*******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/object-to-string.js ***!
  \*******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var TO_STRING_TAG_SUPPORT = __webpack_require__(/*! ../internals/to-string-tag-support */ "../../../node_modules/core-js-pure/internals/to-string-tag-support.js");

var classof = __webpack_require__(/*! ../internals/classof */ "../../../node_modules/core-js-pure/internals/classof.js"); // `Object.prototype.toString` method implementation
// https://tc39.es/ecma262/#sec-object.prototype.tostring


module.exports = TO_STRING_TAG_SUPPORT ? {}.toString : function toString() {
  return '[object ' + classof(this) + ']';
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/path.js":
/*!*******************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/path.js ***!
  \*******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = {};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/redefine-all.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/redefine-all.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var redefine = __webpack_require__(/*! ../internals/redefine */ "../../../node_modules/core-js-pure/internals/redefine.js");

module.exports = function (target, src, options) {
  for (var key in src) {
    if (options && options.unsafe && target[key]) target[key] = src[key];else redefine(target, key, src[key], options);
  }

  return target;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/redefine.js":
/*!***********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/redefine.js ***!
  \***********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

module.exports = function (target, key, value, options) {
  if (options && options.enumerable) target[key] = value;else createNonEnumerableProperty(target, key, value);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/require-object-coercible.js":
/*!***************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/require-object-coercible.js ***!
  \***************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// `RequireObjectCoercible` abstract operation
// https://tc39.es/ecma262/#sec-requireobjectcoercible
module.exports = function (it) {
  if (it == undefined) throw TypeError("Can't call method on " + it);
  return it;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/set-global.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/set-global.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

module.exports = function (key, value) {
  try {
    createNonEnumerableProperty(global, key, value);
  } catch (error) {
    global[key] = value;
  }

  return value;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/set-to-string-tag.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/set-to-string-tag.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var TO_STRING_TAG_SUPPORT = __webpack_require__(/*! ../internals/to-string-tag-support */ "../../../node_modules/core-js-pure/internals/to-string-tag-support.js");

var defineProperty = __webpack_require__(/*! ../internals/object-define-property */ "../../../node_modules/core-js-pure/internals/object-define-property.js").f;

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var toString = __webpack_require__(/*! ../internals/object-to-string */ "../../../node_modules/core-js-pure/internals/object-to-string.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');

module.exports = function (it, TAG, STATIC, SET_METHOD) {
  if (it) {
    var target = STATIC ? it : it.prototype;

    if (!has(target, TO_STRING_TAG)) {
      defineProperty(target, TO_STRING_TAG, {
        configurable: true,
        value: TAG
      });
    }

    if (SET_METHOD && !TO_STRING_TAG_SUPPORT) {
      createNonEnumerableProperty(target, 'toString', toString);
    }
  }
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/shared-key.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/shared-key.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var shared = __webpack_require__(/*! ../internals/shared */ "../../../node_modules/core-js-pure/internals/shared.js");

var uid = __webpack_require__(/*! ../internals/uid */ "../../../node_modules/core-js-pure/internals/uid.js");

var keys = shared('keys');

module.exports = function (key) {
  return keys[key] || (keys[key] = uid(key));
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/shared-store.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/shared-store.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var setGlobal = __webpack_require__(/*! ../internals/set-global */ "../../../node_modules/core-js-pure/internals/set-global.js");

var SHARED = '__core-js_shared__';
var store = global[SHARED] || setGlobal(SHARED, {});
module.exports = store;

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/shared.js":
/*!*********************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/shared.js ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "../../../node_modules/core-js-pure/internals/is-pure.js");

var store = __webpack_require__(/*! ../internals/shared-store */ "../../../node_modules/core-js-pure/internals/shared-store.js");

(module.exports = function (key, value) {
  return store[key] || (store[key] = value !== undefined ? value : {});
})('versions', []).push({
  version: '3.15.2',
  mode: IS_PURE ? 'pure' : 'global',
  copyright: '© 2021 Denis Pushkarev (zloirock.ru)'
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-absolute-index.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-absolute-index.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(/*! ../internals/to-integer */ "../../../node_modules/core-js-pure/internals/to-integer.js");

var max = Math.max;
var min = Math.min; // Helper for a popular repeating case of the spec:
// Let integer be ? ToInteger(index).
// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).

module.exports = function (index, length) {
  var integer = toInteger(index);
  return integer < 0 ? max(integer + length, 0) : min(integer, length);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-indexed-object.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-indexed-object.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// toObject with fallback for non-array-like ES3 strings
var IndexedObject = __webpack_require__(/*! ../internals/indexed-object */ "../../../node_modules/core-js-pure/internals/indexed-object.js");

var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "../../../node_modules/core-js-pure/internals/require-object-coercible.js");

module.exports = function (it) {
  return IndexedObject(requireObjectCoercible(it));
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-integer.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-integer.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var ceil = Math.ceil;
var floor = Math.floor; // `ToInteger` abstract operation
// https://tc39.es/ecma262/#sec-tointeger

module.exports = function (argument) {
  return isNaN(argument = +argument) ? 0 : (argument > 0 ? floor : ceil)(argument);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-length.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-length.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(/*! ../internals/to-integer */ "../../../node_modules/core-js-pure/internals/to-integer.js");

var min = Math.min; // `ToLength` abstract operation
// https://tc39.es/ecma262/#sec-tolength

module.exports = function (argument) {
  return argument > 0 ? min(toInteger(argument), 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-object.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-object.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "../../../node_modules/core-js-pure/internals/require-object-coercible.js"); // `ToObject` abstract operation
// https://tc39.es/ecma262/#sec-toobject


module.exports = function (argument) {
  return Object(requireObjectCoercible(argument));
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-primitive.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-primitive.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js"); // `ToPrimitive` abstract operation
// https://tc39.es/ecma262/#sec-toprimitive
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string


module.exports = function (input, PREFERRED_STRING) {
  if (!isObject(input)) return input;
  var fn, val;
  if (PREFERRED_STRING && typeof (fn = input.toString) == 'function' && !isObject(val = fn.call(input))) return val;
  if (typeof (fn = input.valueOf) == 'function' && !isObject(val = fn.call(input))) return val;
  if (!PREFERRED_STRING && typeof (fn = input.toString) == 'function' && !isObject(val = fn.call(input))) return val;
  throw TypeError("Can't convert object to primitive value");
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/to-string-tag-support.js":
/*!************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/to-string-tag-support.js ***!
  \************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');
var test = {};
test[TO_STRING_TAG] = 'z';
module.exports = String(test) === '[object z]';

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/uid.js":
/*!******************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/uid.js ***!
  \******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var id = 0;
var postfix = Math.random();

module.exports = function (key) {
  return 'Symbol(' + String(key === undefined ? '' : key) + ')_' + (++id + postfix).toString(36);
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/use-symbol-as-uid.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/use-symbol-as-uid.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* eslint-disable es/no-symbol -- required for testing */
var NATIVE_SYMBOL = __webpack_require__(/*! ../internals/native-symbol */ "../../../node_modules/core-js-pure/internals/native-symbol.js");

module.exports = NATIVE_SYMBOL && !Symbol.sham && typeof Symbol.iterator == 'symbol';

/***/ }),

/***/ "../../../node_modules/core-js-pure/internals/well-known-symbol.js":
/*!********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/internals/well-known-symbol.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var shared = __webpack_require__(/*! ../internals/shared */ "../../../node_modules/core-js-pure/internals/shared.js");

var has = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var uid = __webpack_require__(/*! ../internals/uid */ "../../../node_modules/core-js-pure/internals/uid.js");

var NATIVE_SYMBOL = __webpack_require__(/*! ../internals/native-symbol */ "../../../node_modules/core-js-pure/internals/native-symbol.js");

var USE_SYMBOL_AS_UID = __webpack_require__(/*! ../internals/use-symbol-as-uid */ "../../../node_modules/core-js-pure/internals/use-symbol-as-uid.js");

var WellKnownSymbolsStore = shared('wks');
var Symbol = global.Symbol;
var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol : Symbol && Symbol.withoutSetter || uid;

module.exports = function (name) {
  if (!has(WellKnownSymbolsStore, name) || !(NATIVE_SYMBOL || typeof WellKnownSymbolsStore[name] == 'string')) {
    if (NATIVE_SYMBOL && has(Symbol, name)) {
      WellKnownSymbolsStore[name] = Symbol[name];
    } else {
      WellKnownSymbolsStore[name] = createWellKnownSymbol('Symbol.' + name);
    }
  }

  return WellKnownSymbolsStore[name];
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.concat.js":
/*!****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.concat.js ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var isArray = __webpack_require__(/*! ../internals/is-array */ "../../../node_modules/core-js-pure/internals/is-array.js");

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var toLength = __webpack_require__(/*! ../internals/to-length */ "../../../node_modules/core-js-pure/internals/to-length.js");

var createProperty = __webpack_require__(/*! ../internals/create-property */ "../../../node_modules/core-js-pure/internals/create-property.js");

var arraySpeciesCreate = __webpack_require__(/*! ../internals/array-species-create */ "../../../node_modules/core-js-pure/internals/array-species-create.js");

var arrayMethodHasSpeciesSupport = __webpack_require__(/*! ../internals/array-method-has-species-support */ "../../../node_modules/core-js-pure/internals/array-method-has-species-support.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var V8_VERSION = __webpack_require__(/*! ../internals/engine-v8-version */ "../../../node_modules/core-js-pure/internals/engine-v8-version.js");

var IS_CONCAT_SPREADABLE = wellKnownSymbol('isConcatSpreadable');
var MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF;
var MAXIMUM_ALLOWED_INDEX_EXCEEDED = 'Maximum allowed index exceeded'; // We can't use this feature detection in V8 since it causes
// deoptimization and serious performance degradation
// https://github.com/zloirock/core-js/issues/679

var IS_CONCAT_SPREADABLE_SUPPORT = V8_VERSION >= 51 || !fails(function () {
  var array = [];
  array[IS_CONCAT_SPREADABLE] = false;
  return array.concat()[0] !== array;
});
var SPECIES_SUPPORT = arrayMethodHasSpeciesSupport('concat');

var isConcatSpreadable = function isConcatSpreadable(O) {
  if (!isObject(O)) return false;
  var spreadable = O[IS_CONCAT_SPREADABLE];
  return spreadable !== undefined ? !!spreadable : isArray(O);
};

var FORCED = !IS_CONCAT_SPREADABLE_SUPPORT || !SPECIES_SUPPORT; // `Array.prototype.concat` method
// https://tc39.es/ecma262/#sec-array.prototype.concat
// with adding support of @@isConcatSpreadable and @@species

$({
  target: 'Array',
  proto: true,
  forced: FORCED
}, {
  // eslint-disable-next-line no-unused-vars -- required for `.length`
  concat: function concat(arg) {
    var O = toObject(this);
    var A = arraySpeciesCreate(O, 0);
    var n = 0;
    var i, k, length, len, E;

    for (i = -1, length = arguments.length; i < length; i++) {
      E = i === -1 ? O : arguments[i];

      if (isConcatSpreadable(E)) {
        len = toLength(E.length);
        if (n + len > MAX_SAFE_INTEGER) throw TypeError(MAXIMUM_ALLOWED_INDEX_EXCEEDED);

        for (k = 0; k < len; k++, n++) {
          if (k in E) createProperty(A, n, E[k]);
        }
      } else {
        if (n >= MAX_SAFE_INTEGER) throw TypeError(MAXIMUM_ALLOWED_INDEX_EXCEEDED);
        createProperty(A, n++, E);
      }
    }

    A.length = n;
    return A;
  }
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.for-each.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.for-each.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var forEach = __webpack_require__(/*! ../internals/array-for-each */ "../../../node_modules/core-js-pure/internals/array-for-each.js"); // `Array.prototype.forEach` method
// https://tc39.es/ecma262/#sec-array.prototype.foreach
// eslint-disable-next-line es/no-array-prototype-foreach -- safe


$({
  target: 'Array',
  proto: true,
  forced: [].forEach != forEach
}, {
  forEach: forEach
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.index-of.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.index-of.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/* eslint-disable es/no-array-prototype-indexof -- required for testing */

var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var $indexOf = __webpack_require__(/*! ../internals/array-includes */ "../../../node_modules/core-js-pure/internals/array-includes.js").indexOf;

var arrayMethodIsStrict = __webpack_require__(/*! ../internals/array-method-is-strict */ "../../../node_modules/core-js-pure/internals/array-method-is-strict.js");

var nativeIndexOf = [].indexOf;
var NEGATIVE_ZERO = !!nativeIndexOf && 1 / [1].indexOf(1, -0) < 0;
var STRICT_METHOD = arrayMethodIsStrict('indexOf'); // `Array.prototype.indexOf` method
// https://tc39.es/ecma262/#sec-array.prototype.indexof

$({
  target: 'Array',
  proto: true,
  forced: NEGATIVE_ZERO || !STRICT_METHOD
}, {
  indexOf: function indexOf(searchElement
  /* , fromIndex = 0 */
  ) {
    return NEGATIVE_ZERO // convert -0 to +0
    ? nativeIndexOf.apply(this, arguments) || 0 : $indexOf(this, searchElement, arguments.length > 1 ? arguments[1] : undefined);
  }
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.iterator.js":
/*!******************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.iterator.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "../../../node_modules/core-js-pure/internals/to-indexed-object.js");

var addToUnscopables = __webpack_require__(/*! ../internals/add-to-unscopables */ "../../../node_modules/core-js-pure/internals/add-to-unscopables.js");

var Iterators = __webpack_require__(/*! ../internals/iterators */ "../../../node_modules/core-js-pure/internals/iterators.js");

var InternalStateModule = __webpack_require__(/*! ../internals/internal-state */ "../../../node_modules/core-js-pure/internals/internal-state.js");

var defineIterator = __webpack_require__(/*! ../internals/define-iterator */ "../../../node_modules/core-js-pure/internals/define-iterator.js");

var ARRAY_ITERATOR = 'Array Iterator';
var setInternalState = InternalStateModule.set;
var getInternalState = InternalStateModule.getterFor(ARRAY_ITERATOR); // `Array.prototype.entries` method
// https://tc39.es/ecma262/#sec-array.prototype.entries
// `Array.prototype.keys` method
// https://tc39.es/ecma262/#sec-array.prototype.keys
// `Array.prototype.values` method
// https://tc39.es/ecma262/#sec-array.prototype.values
// `Array.prototype[@@iterator]` method
// https://tc39.es/ecma262/#sec-array.prototype-@@iterator
// `CreateArrayIterator` internal method
// https://tc39.es/ecma262/#sec-createarrayiterator

module.exports = defineIterator(Array, 'Array', function (iterated, kind) {
  setInternalState(this, {
    type: ARRAY_ITERATOR,
    target: toIndexedObject(iterated),
    // target
    index: 0,
    // next index
    kind: kind // kind

  }); // `%ArrayIteratorPrototype%.next` method
  // https://tc39.es/ecma262/#sec-%arrayiteratorprototype%.next
}, function () {
  var state = getInternalState(this);
  var target = state.target;
  var kind = state.kind;
  var index = state.index++;

  if (!target || index >= target.length) {
    state.target = undefined;
    return {
      value: undefined,
      done: true
    };
  }

  if (kind == 'keys') return {
    value: index,
    done: false
  };
  if (kind == 'values') return {
    value: target[index],
    done: false
  };
  return {
    value: [index, target[index]],
    done: false
  };
}, 'values'); // argumentsList[@@iterator] is %ArrayProto_values%
// https://tc39.es/ecma262/#sec-createunmappedargumentsobject
// https://tc39.es/ecma262/#sec-createmappedargumentsobject

Iterators.Arguments = Iterators.Array; // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables

addToUnscopables('keys');
addToUnscopables('values');
addToUnscopables('entries');

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.map.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.map.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var $map = __webpack_require__(/*! ../internals/array-iteration */ "../../../node_modules/core-js-pure/internals/array-iteration.js").map;

var arrayMethodHasSpeciesSupport = __webpack_require__(/*! ../internals/array-method-has-species-support */ "../../../node_modules/core-js-pure/internals/array-method-has-species-support.js");

var HAS_SPECIES_SUPPORT = arrayMethodHasSpeciesSupport('map'); // `Array.prototype.map` method
// https://tc39.es/ecma262/#sec-array.prototype.map
// with adding support of @@species

$({
  target: 'Array',
  proto: true,
  forced: !HAS_SPECIES_SUPPORT
}, {
  map: function map(callbackfn
  /* , thisArg */
  ) {
    return $map(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  }
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.array.sort.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.array.sort.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var aFunction = __webpack_require__(/*! ../internals/a-function */ "../../../node_modules/core-js-pure/internals/a-function.js");

var toObject = __webpack_require__(/*! ../internals/to-object */ "../../../node_modules/core-js-pure/internals/to-object.js");

var toLength = __webpack_require__(/*! ../internals/to-length */ "../../../node_modules/core-js-pure/internals/to-length.js");

var fails = __webpack_require__(/*! ../internals/fails */ "../../../node_modules/core-js-pure/internals/fails.js");

var internalSort = __webpack_require__(/*! ../internals/array-sort */ "../../../node_modules/core-js-pure/internals/array-sort.js");

var arrayMethodIsStrict = __webpack_require__(/*! ../internals/array-method-is-strict */ "../../../node_modules/core-js-pure/internals/array-method-is-strict.js");

var FF = __webpack_require__(/*! ../internals/engine-ff-version */ "../../../node_modules/core-js-pure/internals/engine-ff-version.js");

var IE_OR_EDGE = __webpack_require__(/*! ../internals/engine-is-ie-or-edge */ "../../../node_modules/core-js-pure/internals/engine-is-ie-or-edge.js");

var V8 = __webpack_require__(/*! ../internals/engine-v8-version */ "../../../node_modules/core-js-pure/internals/engine-v8-version.js");

var WEBKIT = __webpack_require__(/*! ../internals/engine-webkit-version */ "../../../node_modules/core-js-pure/internals/engine-webkit-version.js");

var test = [];
var nativeSort = test.sort; // IE8-

var FAILS_ON_UNDEFINED = fails(function () {
  test.sort(undefined);
}); // V8 bug

var FAILS_ON_NULL = fails(function () {
  test.sort(null);
}); // Old WebKit

var STRICT_METHOD = arrayMethodIsStrict('sort');
var STABLE_SORT = !fails(function () {
  // feature detection can be too slow, so check engines versions
  if (V8) return V8 < 70;
  if (FF && FF > 3) return;
  if (IE_OR_EDGE) return true;
  if (WEBKIT) return WEBKIT < 603;
  var result = '';
  var code, chr, value, index; // generate an array with more 512 elements (Chakra and old V8 fails only in this case)

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
      test.push({
        k: chr + index,
        v: value
      });
    }
  }

  test.sort(function (a, b) {
    return b.v - a.v;
  });

  for (index = 0; index < test.length; index++) {
    chr = test[index].k.charAt(0);
    if (result.charAt(result.length - 1) !== chr) result += chr;
  }

  return result !== 'DGBEFHACIJK';
});
var FORCED = FAILS_ON_UNDEFINED || !FAILS_ON_NULL || !STRICT_METHOD || !STABLE_SORT;

var getSortCompare = function getSortCompare(comparefn) {
  return function (x, y) {
    if (y === undefined) return -1;
    if (x === undefined) return 1;
    if (comparefn !== undefined) return +comparefn(x, y) || 0;
    return String(x) > String(y) ? 1 : -1;
  };
}; // `Array.prototype.sort` method
// https://tc39.es/ecma262/#sec-array.prototype.sort


$({
  target: 'Array',
  proto: true,
  forced: FORCED
}, {
  sort: function sort(comparefn) {
    if (comparefn !== undefined) aFunction(comparefn);
    var array = toObject(this);
    if (STABLE_SORT) return comparefn === undefined ? nativeSort.call(array) : nativeSort.call(array, comparefn);
    var items = [];
    var arrayLength = toLength(array.length);
    var itemsLength, index;

    for (index = 0; index < arrayLength; index++) {
      if (index in array) items.push(array[index]);
    }

    items = internalSort(items, getSortCompare(comparefn));
    itemsLength = items.length;
    index = 0;

    while (index < itemsLength) {
      array[index] = items[index++];
    }

    while (index < arrayLength) {
      delete array[index++];
    }

    return array;
  }
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.function.bind.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.function.bind.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var bind = __webpack_require__(/*! ../internals/function-bind */ "../../../node_modules/core-js-pure/internals/function-bind.js"); // `Function.prototype.bind` method
// https://tc39.es/ecma262/#sec-function.prototype.bind


$({
  target: 'Function',
  proto: true
}, {
  bind: bind
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/es.object.assign.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/es.object.assign.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var assign = __webpack_require__(/*! ../internals/object-assign */ "../../../node_modules/core-js-pure/internals/object-assign.js"); // `Object.assign` method
// https://tc39.es/ecma262/#sec-object.assign
// eslint-disable-next-line es/no-object-assign -- required for testing


$({
  target: 'Object',
  stat: true,
  forced: Object.assign !== assign
}, {
  assign: assign
});

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/web.dom-collections.iterator.js":
/*!*****************************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/web.dom-collections.iterator.js ***!
  \*****************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./es.array.iterator */ "../../../node_modules/core-js-pure/modules/es.array.iterator.js");

var DOMIterables = __webpack_require__(/*! ../internals/dom-iterables */ "../../../node_modules/core-js-pure/internals/dom-iterables.js");

var global = __webpack_require__(/*! ../internals/global */ "../../../node_modules/core-js-pure/internals/global.js");

var classof = __webpack_require__(/*! ../internals/classof */ "../../../node_modules/core-js-pure/internals/classof.js");

var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "../../../node_modules/core-js-pure/internals/create-non-enumerable-property.js");

var Iterators = __webpack_require__(/*! ../internals/iterators */ "../../../node_modules/core-js-pure/internals/iterators.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');

for (var COLLECTION_NAME in DOMIterables) {
  var Collection = global[COLLECTION_NAME];
  var CollectionPrototype = Collection && Collection.prototype;

  if (CollectionPrototype && classof(CollectionPrototype) !== TO_STRING_TAG) {
    createNonEnumerableProperty(CollectionPrototype, TO_STRING_TAG, COLLECTION_NAME);
  }

  Iterators[COLLECTION_NAME] = Iterators.Array;
}

/***/ }),

/***/ "../../../node_modules/core-js-pure/modules/web.url-search-params.js":
/*!**********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/modules/web.url-search-params.js ***!
  \**********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
 // TODO: in core-js@4, move /modules/ dependencies to public entries for better optimization by tools like `preset-env`

__webpack_require__(/*! ../modules/es.array.iterator */ "../../../node_modules/core-js-pure/modules/es.array.iterator.js");

var $ = __webpack_require__(/*! ../internals/export */ "../../../node_modules/core-js-pure/internals/export.js");

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "../../../node_modules/core-js-pure/internals/get-built-in.js");

var USE_NATIVE_URL = __webpack_require__(/*! ../internals/native-url */ "../../../node_modules/core-js-pure/internals/native-url.js");

var redefine = __webpack_require__(/*! ../internals/redefine */ "../../../node_modules/core-js-pure/internals/redefine.js");

var redefineAll = __webpack_require__(/*! ../internals/redefine-all */ "../../../node_modules/core-js-pure/internals/redefine-all.js");

var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "../../../node_modules/core-js-pure/internals/set-to-string-tag.js");

var createIteratorConstructor = __webpack_require__(/*! ../internals/create-iterator-constructor */ "../../../node_modules/core-js-pure/internals/create-iterator-constructor.js");

var InternalStateModule = __webpack_require__(/*! ../internals/internal-state */ "../../../node_modules/core-js-pure/internals/internal-state.js");

var anInstance = __webpack_require__(/*! ../internals/an-instance */ "../../../node_modules/core-js-pure/internals/an-instance.js");

var hasOwn = __webpack_require__(/*! ../internals/has */ "../../../node_modules/core-js-pure/internals/has.js");

var bind = __webpack_require__(/*! ../internals/function-bind-context */ "../../../node_modules/core-js-pure/internals/function-bind-context.js");

var classof = __webpack_require__(/*! ../internals/classof */ "../../../node_modules/core-js-pure/internals/classof.js");

var anObject = __webpack_require__(/*! ../internals/an-object */ "../../../node_modules/core-js-pure/internals/an-object.js");

var isObject = __webpack_require__(/*! ../internals/is-object */ "../../../node_modules/core-js-pure/internals/is-object.js");

var create = __webpack_require__(/*! ../internals/object-create */ "../../../node_modules/core-js-pure/internals/object-create.js");

var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "../../../node_modules/core-js-pure/internals/create-property-descriptor.js");

var getIterator = __webpack_require__(/*! ../internals/get-iterator */ "../../../node_modules/core-js-pure/internals/get-iterator.js");

var getIteratorMethod = __webpack_require__(/*! ../internals/get-iterator-method */ "../../../node_modules/core-js-pure/internals/get-iterator-method.js");

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "../../../node_modules/core-js-pure/internals/well-known-symbol.js");

var $fetch = getBuiltIn('fetch');
var Headers = getBuiltIn('Headers');
var ITERATOR = wellKnownSymbol('iterator');
var URL_SEARCH_PARAMS = 'URLSearchParams';
var URL_SEARCH_PARAMS_ITERATOR = URL_SEARCH_PARAMS + 'Iterator';
var setInternalState = InternalStateModule.set;
var getInternalParamsState = InternalStateModule.getterFor(URL_SEARCH_PARAMS);
var getInternalIteratorState = InternalStateModule.getterFor(URL_SEARCH_PARAMS_ITERATOR);
var plus = /\+/g;
var sequences = Array(4);

var percentSequence = function percentSequence(bytes) {
  return sequences[bytes - 1] || (sequences[bytes - 1] = RegExp('((?:%[\\da-f]{2}){' + bytes + '})', 'gi'));
};

var percentDecode = function percentDecode(sequence) {
  try {
    return decodeURIComponent(sequence);
  } catch (error) {
    return sequence;
  }
};

var deserialize = function deserialize(it) {
  var result = it.replace(plus, ' ');
  var bytes = 4;

  try {
    return decodeURIComponent(result);
  } catch (error) {
    while (bytes) {
      result = result.replace(percentSequence(bytes--), percentDecode);
    }

    return result;
  }
};

var find = /[!'()~]|%20/g;
var replace = {
  '!': '%21',
  "'": '%27',
  '(': '%28',
  ')': '%29',
  '~': '%7E',
  '%20': '+'
};

var replacer = function replacer(match) {
  return replace[match];
};

var serialize = function serialize(it) {
  return encodeURIComponent(it).replace(find, replacer);
};

var parseSearchParams = function parseSearchParams(result, query) {
  if (query) {
    var attributes = query.split('&');
    var index = 0;
    var attribute, entry;

    while (index < attributes.length) {
      attribute = attributes[index++];

      if (attribute.length) {
        entry = attribute.split('=');
        result.push({
          key: deserialize(entry.shift()),
          value: deserialize(entry.join('='))
        });
      }
    }
  }
};

var updateSearchParams = function updateSearchParams(query) {
  this.entries.length = 0;
  parseSearchParams(this.entries, query);
};

var validateArgumentsLength = function validateArgumentsLength(passed, required) {
  if (passed < required) throw TypeError('Not enough arguments');
};

var URLSearchParamsIterator = createIteratorConstructor(function Iterator(params, kind) {
  setInternalState(this, {
    type: URL_SEARCH_PARAMS_ITERATOR,
    iterator: getIterator(getInternalParamsState(params).entries),
    kind: kind
  });
}, 'Iterator', function next() {
  var state = getInternalIteratorState(this);
  var kind = state.kind;
  var step = state.iterator.next();
  var entry = step.value;

  if (!step.done) {
    step.value = kind === 'keys' ? entry.key : kind === 'values' ? entry.value : [entry.key, entry.value];
  }

  return step;
}); // `URLSearchParams` constructor
// https://url.spec.whatwg.org/#interface-urlsearchparams

var URLSearchParamsConstructor = function URLSearchParams()
/* init */
{
  anInstance(this, URLSearchParamsConstructor, URL_SEARCH_PARAMS);
  var init = arguments.length > 0 ? arguments[0] : undefined;
  var that = this;
  var entries = [];
  var iteratorMethod, iterator, next, step, entryIterator, entryNext, first, second, key;
  setInternalState(that, {
    type: URL_SEARCH_PARAMS,
    entries: entries,
    updateURL: function updateURL() {
      /* empty */
    },
    updateSearchParams: updateSearchParams
  });

  if (init !== undefined) {
    if (isObject(init)) {
      iteratorMethod = getIteratorMethod(init);

      if (typeof iteratorMethod === 'function') {
        iterator = iteratorMethod.call(init);
        next = iterator.next;

        while (!(step = next.call(iterator)).done) {
          entryIterator = getIterator(anObject(step.value));
          entryNext = entryIterator.next;
          if ((first = entryNext.call(entryIterator)).done || (second = entryNext.call(entryIterator)).done || !entryNext.call(entryIterator).done) throw TypeError('Expected sequence with length 2');
          entries.push({
            key: first.value + '',
            value: second.value + ''
          });
        }
      } else for (key in init) {
        if (hasOwn(init, key)) entries.push({
          key: key,
          value: init[key] + ''
        });
      }
    } else {
      parseSearchParams(entries, typeof init === 'string' ? init.charAt(0) === '?' ? init.slice(1) : init : init + '');
    }
  }
};

var URLSearchParamsPrototype = URLSearchParamsConstructor.prototype;
redefineAll(URLSearchParamsPrototype, {
  // `URLSearchParams.prototype.append` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-append
  append: function append(name, value) {
    validateArgumentsLength(arguments.length, 2);
    var state = getInternalParamsState(this);
    state.entries.push({
      key: name + '',
      value: value + ''
    });
    state.updateURL();
  },
  // `URLSearchParams.prototype.delete` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-delete
  'delete': function _delete(name) {
    validateArgumentsLength(arguments.length, 1);
    var state = getInternalParamsState(this);
    var entries = state.entries;
    var key = name + '';
    var index = 0;

    while (index < entries.length) {
      if (entries[index].key === key) entries.splice(index, 1);else index++;
    }

    state.updateURL();
  },
  // `URLSearchParams.prototype.get` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-get
  get: function get(name) {
    validateArgumentsLength(arguments.length, 1);
    var entries = getInternalParamsState(this).entries;
    var key = name + '';
    var index = 0;

    for (; index < entries.length; index++) {
      if (entries[index].key === key) return entries[index].value;
    }

    return null;
  },
  // `URLSearchParams.prototype.getAll` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-getall
  getAll: function getAll(name) {
    validateArgumentsLength(arguments.length, 1);
    var entries = getInternalParamsState(this).entries;
    var key = name + '';
    var result = [];
    var index = 0;

    for (; index < entries.length; index++) {
      if (entries[index].key === key) result.push(entries[index].value);
    }

    return result;
  },
  // `URLSearchParams.prototype.has` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-has
  has: function has(name) {
    validateArgumentsLength(arguments.length, 1);
    var entries = getInternalParamsState(this).entries;
    var key = name + '';
    var index = 0;

    while (index < entries.length) {
      if (entries[index++].key === key) return true;
    }

    return false;
  },
  // `URLSearchParams.prototype.set` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-set
  set: function set(name, value) {
    validateArgumentsLength(arguments.length, 1);
    var state = getInternalParamsState(this);
    var entries = state.entries;
    var found = false;
    var key = name + '';
    var val = value + '';
    var index = 0;
    var entry;

    for (; index < entries.length; index++) {
      entry = entries[index];

      if (entry.key === key) {
        if (found) entries.splice(index--, 1);else {
          found = true;
          entry.value = val;
        }
      }
    }

    if (!found) entries.push({
      key: key,
      value: val
    });
    state.updateURL();
  },
  // `URLSearchParams.prototype.sort` method
  // https://url.spec.whatwg.org/#dom-urlsearchparams-sort
  sort: function sort() {
    var state = getInternalParamsState(this);
    var entries = state.entries; // Array#sort is not stable in some engines

    var slice = entries.slice();
    var entry, entriesIndex, sliceIndex;
    entries.length = 0;

    for (sliceIndex = 0; sliceIndex < slice.length; sliceIndex++) {
      entry = slice[sliceIndex];

      for (entriesIndex = 0; entriesIndex < sliceIndex; entriesIndex++) {
        if (entries[entriesIndex].key > entry.key) {
          entries.splice(entriesIndex, 0, entry);
          break;
        }
      }

      if (entriesIndex === sliceIndex) entries.push(entry);
    }

    state.updateURL();
  },
  // `URLSearchParams.prototype.forEach` method
  forEach: function forEach(callback
  /* , thisArg */
  ) {
    var entries = getInternalParamsState(this).entries;
    var boundFunction = bind(callback, arguments.length > 1 ? arguments[1] : undefined, 3);
    var index = 0;
    var entry;

    while (index < entries.length) {
      entry = entries[index++];
      boundFunction(entry.value, entry.key, this);
    }
  },
  // `URLSearchParams.prototype.keys` method
  keys: function keys() {
    return new URLSearchParamsIterator(this, 'keys');
  },
  // `URLSearchParams.prototype.values` method
  values: function values() {
    return new URLSearchParamsIterator(this, 'values');
  },
  // `URLSearchParams.prototype.entries` method
  entries: function entries() {
    return new URLSearchParamsIterator(this, 'entries');
  }
}, {
  enumerable: true
}); // `URLSearchParams.prototype[@@iterator]` method

redefine(URLSearchParamsPrototype, ITERATOR, URLSearchParamsPrototype.entries); // `URLSearchParams.prototype.toString` method
// https://url.spec.whatwg.org/#urlsearchparams-stringification-behavior

redefine(URLSearchParamsPrototype, 'toString', function toString() {
  var entries = getInternalParamsState(this).entries;
  var result = [];
  var index = 0;
  var entry;

  while (index < entries.length) {
    entry = entries[index++];
    result.push(serialize(entry.key) + '=' + serialize(entry.value));
  }

  return result.join('&');
}, {
  enumerable: true
});
setToStringTag(URLSearchParamsConstructor, URL_SEARCH_PARAMS);
$({
  global: true,
  forced: !USE_NATIVE_URL
}, {
  URLSearchParams: URLSearchParamsConstructor
}); // Wrap `fetch` for correct work with polyfilled `URLSearchParams`
// https://github.com/zloirock/core-js/issues/674

if (!USE_NATIVE_URL && typeof $fetch == 'function' && typeof Headers == 'function') {
  $({
    global: true,
    enumerable: true,
    forced: true
  }, {
    fetch: function fetch(input
    /* , init */
    ) {
      var args = [input];
      var init, body, headers;

      if (arguments.length > 1) {
        init = arguments[1];

        if (isObject(init)) {
          body = init.body;

          if (classof(body) === URL_SEARCH_PARAMS) {
            headers = init.headers ? new Headers(init.headers) : new Headers();

            if (!headers.has('content-type')) {
              headers.set('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
            }

            init = create(init, {
              body: createPropertyDescriptor(0, String(body)),
              headers: createPropertyDescriptor(0, headers)
            });
          }
        }

        args.push(init);
      }

      return $fetch.apply(this, args);
    }
  });
}

module.exports = {
  URLSearchParams: URLSearchParamsConstructor,
  getState: getInternalParamsState
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/array/virtual/for-each.js":
/*!**********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/array/virtual/for-each.js ***!
  \**********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../../es/array/virtual/for-each */ "../../../node_modules/core-js-pure/es/array/virtual/for-each.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/bind.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/bind.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/instance/bind */ "../../../node_modules/core-js-pure/es/instance/bind.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/concat.js":
/*!***************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/concat.js ***!
  \***************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/instance/concat */ "../../../node_modules/core-js-pure/es/instance/concat.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/for-each.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/for-each.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../../modules/web.dom-collections.iterator */ "../../../node_modules/core-js-pure/modules/web.dom-collections.iterator.js");

var forEach = __webpack_require__(/*! ../array/virtual/for-each */ "../../../node_modules/core-js-pure/stable/array/virtual/for-each.js");

var classof = __webpack_require__(/*! ../../internals/classof */ "../../../node_modules/core-js-pure/internals/classof.js");

var ArrayPrototype = Array.prototype;
var DOMIterables = {
  DOMTokenList: true,
  NodeList: true
};

module.exports = function (it) {
  var own = it.forEach;
  return it === ArrayPrototype || it instanceof Array && own === ArrayPrototype.forEach // eslint-disable-next-line no-prototype-builtins -- safe
  || DOMIterables.hasOwnProperty(classof(it)) ? forEach : own;
};

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/index-of.js":
/*!*****************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/index-of.js ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/instance/index-of */ "../../../node_modules/core-js-pure/es/instance/index-of.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/map.js":
/*!************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/map.js ***!
  \************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/instance/map */ "../../../node_modules/core-js-pure/es/instance/map.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/instance/sort.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/instance/sort.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/instance/sort */ "../../../node_modules/core-js-pure/es/instance/sort.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/object/assign.js":
/*!*************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/object/assign.js ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../es/object/assign */ "../../../node_modules/core-js-pure/es/object/assign.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/stable/url-search-params/index.js":
/*!***********************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/stable/url-search-params/index.js ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var parent = __webpack_require__(/*! ../../web/url-search-params */ "../../../node_modules/core-js-pure/web/url-search-params.js");

module.exports = parent;

/***/ }),

/***/ "../../../node_modules/core-js-pure/web/url-search-params.js":
/*!**************************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/core-js-pure/web/url-search-params.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ../modules/web.url-search-params */ "../../../node_modules/core-js-pure/modules/web.url-search-params.js");

var path = __webpack_require__(/*! ../internals/path */ "../../../node_modules/core-js-pure/internals/path.js");

module.exports = path.URLSearchParams;

/***/ }),

/***/ "../../../node_modules/regenerator-runtime/runtime.js":
/*!*******************************************************************************!*\
  !*** /home/dizzy/Projects/matomo/node_modules/regenerator-runtime/runtime.js ***!
  \*******************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var runtime = function (exports) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined; // More compressible than void 0.

  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  function define(obj, key, value) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
    return obj[key];
  }

  try {
    // IE 8 has a broken Object.defineProperty that only works on DOM objects.
    define({}, "");
  } catch (err) {
    define = function define(obj, key, value) {
      return obj[key] = value;
    };
  }

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []); // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.

    generator._invoke = makeInvokeMethod(innerFn, self, context);
    return generator;
  }

  exports.wrap = wrap; // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.

  function tryCatch(fn, obj, arg) {
    try {
      return {
        type: "normal",
        arg: fn.call(obj, arg)
      };
    } catch (err) {
      return {
        type: "throw",
        arg: err
      };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed"; // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.

  var ContinueSentinel = {}; // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.

  function Generator() {}

  function GeneratorFunction() {}

  function GeneratorFunctionPrototype() {} // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.


  var IteratorPrototype = {};

  IteratorPrototype[iteratorSymbol] = function () {
    return this;
  };

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));

  if (NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
  GeneratorFunctionPrototype.constructor = GeneratorFunction;
  GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"); // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.

  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function (method) {
      define(prototype, method, function (arg) {
        return this._invoke(method, arg);
      });
    });
  }

  exports.isGeneratorFunction = function (genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor ? ctor === GeneratorFunction || // For the native GeneratorFunction constructor, the best we can
    // do is to check its .name property.
    (ctor.displayName || ctor.name) === "GeneratorFunction" : false;
  };

  exports.mark = function (genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      define(genFun, toStringTagSymbol, "GeneratorFunction");
    }

    genFun.prototype = Object.create(Gp);
    return genFun;
  }; // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.


  exports.awrap = function (arg) {
    return {
      __await: arg
    };
  };

  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);

      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;

        if (value && typeof value === "object" && hasOwn.call(value, "__await")) {
          return PromiseImpl.resolve(value.__await).then(function (value) {
            invoke("next", value, resolve, reject);
          }, function (err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return PromiseImpl.resolve(value).then(function (unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration.
          result.value = unwrapped;
          resolve(result);
        }, function (error) {
          // If a rejected Promise was yielded, throw the rejection back
          // into the async generator function so it can be handled there.
          return invoke("throw", error, resolve, reject);
        });
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new PromiseImpl(function (resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise = // If enqueue has been called before, then we want to wait until
      // all previous Promises have been resolved before calling invoke,
      // so that results are always delivered in the correct order. If
      // enqueue has not been called before, then it is important to
      // call invoke immediately, without waiting on a callback to fire,
      // so that the async generator function has the opportunity to do
      // any necessary setup in a predictable way. This predictability
      // is why the Promise constructor synchronously invokes its
      // executor callback, and why async functions synchronously
      // execute code before the first await. Since we implement simple
      // async functions in terms of async generators, it is especially
      // important to get this right, even though it requires care.
      previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, // Avoid propagating failures to Promises returned by later
      // invocations of the iterator.
      callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
    } // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).


    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);

  AsyncIterator.prototype[asyncIteratorSymbol] = function () {
    return this;
  };

  exports.AsyncIterator = AsyncIterator; // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.

  exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    if (PromiseImpl === void 0) PromiseImpl = Promise;
    var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl);
    return exports.isGeneratorFunction(outerFn) ? iter // If outerFn is a generator, return the full iterator.
    : iter.next().then(function (result) {
      return result.done ? result.value : iter.next();
    });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;
    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        } // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume


        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;

        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);

          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;
        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);
        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;
        var record = tryCatch(innerFn, self, context);

        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done ? GenStateCompleted : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };
        } else if (record.type === "throw") {
          state = GenStateCompleted; // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.

          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  } // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.


  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];

    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        // Note: ["return"] must be used for ES3 parsing compatibility.
        if (delegate.iterator["return"]) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError("The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (!info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value; // Resume execution at the desired location (see delegateYield).

      context.next = delegate.nextLoc; // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.

      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }
    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    } // The delegate iterator is finished, so forget it and continue with
    // the outer generator.


    context.delegate = null;
    return ContinueSentinel;
  } // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.


  defineIteratorMethods(Gp);
  define(Gp, toStringTagSymbol, "Generator"); // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.

  Gp[iteratorSymbol] = function () {
    return this;
  };

  Gp.toString = function () {
    return "[object Generator]";
  };

  function pushTryEntry(locs) {
    var entry = {
      tryLoc: locs[0]
    };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{
      tryLoc: "root"
    }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  exports.keys = function (object) {
    var keys = [];

    for (var key in object) {
      keys.push(key);
    }

    keys.reverse(); // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.

    return function next() {
      while (keys.length) {
        var key = keys.pop();

        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      } // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.


      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];

      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1,
            next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;
          return next;
        };

        return next.next = next;
      }
    } // Return an iterator with no values.


    return {
      next: doneResult
    };
  }

  exports.values = values;

  function doneResult() {
    return {
      value: undefined,
      done: true
    };
  }

  Context.prototype = {
    constructor: Context,
    reset: function reset(skipTempReset) {
      this.prev = 0;
      this.next = 0; // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.

      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;
      this.method = "next";
      this.arg = undefined;
      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" && hasOwn.call(this, name) && !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },
    stop: function stop() {
      this.done = true;
      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;

      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },
    dispatchException: function dispatchException(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;

      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !!caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }
          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }
          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }
          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },
    abrupt: function abrupt(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry && (type === "break" || type === "continue") && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },
    complete: function complete(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" || record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },
    finish: function finish(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },
    "catch": function _catch(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];

        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;

          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }

          return thrown;
        }
      } // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.


      throw new Error("illegal catch attempt");
    },
    delegateYield: function delegateYield(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  }; // Regardless of whether this script is executing as a CommonJS module
  // or not, return the runtime object so that we can declare the variable
  // regeneratorRuntime in the outer scope, which allows this module to be
  // injected easily by `bin/regenerator --include-runtime script.js`.

  return exports;
}( // If this script is executing as a CommonJS module, use module.exports
// as the regeneratorRuntime namespace. Otherwise create a new empty
// object. Either way, the resulting object will be used to initialize
// the regeneratorRuntime variable at the top of this file.
 true ? module.exports : undefined);

try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  // This module should not be running in strict mode, so the above
  // assignment should always work unless something is misconfigured. Just
  // in case runtime.js accidentally runs in strict mode, we can escape
  // strict mode using a global Function call. This could conceivably fail
  // if a Content Security Policy forbids using Function, but in that case
  // the proper solution is to fix the accidental strict mode problem. If
  // you've misconfigured your bundler to force strict mode and applied a
  // CSP to forbid Function, and you're not willing to fix either of those
  // problems, please detail your unique predicament in a GitHub issue.
  Function("r", "regeneratorRuntime = r")(runtime);
}

/***/ }),

/***/ "../../../node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var g; // This works in non-strict mode

g = function () {
  return this;
}();

try {
  // This works if eval is allowed (see CSP)
  g = g || new Function("return this")();
} catch (e) {
  // This works if the window reference is available
  if (typeof window === "object") g = window;
} // g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}


module.exports = g;

/***/ }),

/***/ "./src/common/FocusAnywhereButHere.js":
/*!********************************************!*\
  !*** ./src/common/FocusAnywhereButHere.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/object/assign */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/bind */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/bind.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/assertThisInitialized */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createSuper__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_7__);








var _this2 = undefined,
    _jsxFileName = "/home/dizzy/Projects/matomo/plugins/CoreHome/react/src/common/FocusAnywhereButHere.js";



var FocusAnywhereButHereComponent = /*#__PURE__*/function (_React$Component) {
  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_5__["default"])(FocusAnywhereButHereComponent, _React$Component);

  var _super = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createSuper__WEBPACK_IMPORTED_MODULE_6__["default"])(FocusAnywhereButHereComponent);

  function FocusAnywhereButHereComponent(props) {
    var _context, _context2, _context3, _context4;

    var _this;

    Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_2__["default"])(this, FocusAnywhereButHereComponent);

    _this = _super.call(this, props);
    _this.state = {
      isMouseDown: false,
      hasScrolled: false
    };
    _this.onEscapeHandler = _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1___default()(_context = _this.onEscapeHandler).call(_context, Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__["default"])(_this));
    _this.onMouseDown = _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1___default()(_context2 = _this.onMouseDown).call(_context2, Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__["default"])(_this));
    _this.onClickOutsideElement = _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1___default()(_context3 = _this.onClickOutsideElement).call(_context3, Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__["default"])(_this));
    _this.onScroll = _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_1___default()(_context4 = _this.onScroll).call(_context4, Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__["default"])(_this));
    return _this;
  }

  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_3__["default"])(FocusAnywhereButHereComponent, [{
    key: "onClickOutsideElement",
    value: function onClickOutsideElement(event) {
      var hadUsedScrollbar = this.state.isMouseDown && this.state.hasScrolled;
      this.setState({
        isMouseDown: false,
        hasScrolled: false
      });

      if (hadUsedScrollbar) {
        return;
      }

      if (this.props.element.current.contains(event.target).length === 0) {
        this.props.onLoseFocus && this.props.onLoseFocus();
      }
    }
  }, {
    key: "onScroll",
    value: function onScroll() {
      this.setState({
        hasScrolled: true
      });
    }
  }, {
    key: "onMouseDown",
    value: function onMouseDown() {
      this.setState({
        isMouseDown: true,
        hasScrolled: false
      });
    }
  }, {
    key: "onEscapeHandler",
    value: function onEscapeHandler(event) {
      if (event.which === 27) {
        this.setState({
          isMouseDown: false,
          hasScrolled: false
        });
        this.props.onLoseFocus && this.props.onLoseFocus();
      }
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      document.addEventListener('keyup', this.onEscapeHandler);
      document.addEventListener('mousedown', this.onMouseDown);
      document.addEventListener('mouseup', this.onClickOutsideElement);
      document.addEventListener('scroll', this.onScroll);
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      document.removeEventListener('keyup', this.onEscapeHandler);
      document.removeEventListener('mousedown', this.onMouseDown);
      document.removeEventListener('mouseup', this.onClickOutsideElement);
      document.removeEventListener('scroll', this.onScroll);
    }
  }, {
    key: "render",
    value: function render() {
      return null;
    }
  }]);

  return FocusAnywhereButHereComponent;
}(react__WEBPACK_IMPORTED_MODULE_7__["Component"]);

/* harmony default export */ __webpack_exports__["default"] = (/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_7__["forwardRef"](function (props, ref) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_7__["createElement"](FocusAnywhereButHereComponent, _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default()({
    element: ref
  }, props, {
    __self: _this2,
    __source: {
      fileName: _jsxFileName,
      lineNumber: 77,
      columnNumber: 49
    }
  }));
}));

/***/ }),

/***/ "./src/common/MatomoApi.js":
/*!*********************************!*\
  !*** ./src/common/MatomoApi.js ***!
  \*********************************/
/*! exports provided: MatomoApi, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "MatomoApi", function() { return MatomoApi; });
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/for-each */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/for-each.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime-corejs3/regenerator */ "../../../node_modules/@babel/runtime-corejs3/regenerator/index.js");
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/object/assign */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/url-search-params */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/url-search-params.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! axios */ "axios");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_7__);








var piwik = window.piwik;
var MatomoApi = /*#__PURE__*/function () {
  function MatomoApi() {
    Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_5__["default"])(this, MatomoApi);
  }

  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_6__["default"])(MatomoApi, [{
    key: "fetch",
    value: function () {
      var _fetch = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_4__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_1___default.a.mark(function _callee(params) {
        var body, apiParams, paramsThatCanOverride, mergedParams, query, headers, response;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_1___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                body = new _babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3___default.a({
                  token_auth: piwik.token_auth,
                  force_api_session: piwik.broadcast.isWidgetizeRequestWithoutSession() ? '0' : '1'
                }).toString();
                apiParams = {
                  module: 'API',
                  action: 'index',
                  format: 'JSON'
                };
                paramsThatCanOverride = ['idSite', 'period', 'date', 'segment', 'comparePeriods', 'compareDates'];
                mergedParams = _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_2___default()({}, this.getCurrentUrlParams(paramsThatCanOverride), this.getCurrentHashParams(paramsThatCanOverride), apiParams, params);
                query = new _babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3___default.a(mergedParams).toString();
                headers = {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  // ie 8,9,10 caches ajax requests, prevent this
                  'cache-control': 'no-cache'
                };
                _context.next = 8;
                return axios__WEBPACK_IMPORTED_MODULE_7___default.a.post('index.php?' + query, body, {
                  headers: headers
                });

              case 8:
                response = _context.sent;
                return _context.abrupt("return", response.data);

              case 10:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function fetch(_x) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "getCurrentUrlParams",
    value: function getCurrentUrlParams(paramsThatCanOverride) {
      return this.getSomeUrlParams(window.location.search, paramsThatCanOverride);
    }
  }, {
    key: "getCurrentHashParams",
    value: function getCurrentHashParams(paramsThatCanOverride) {
      return this.getSomeUrlParams(window.location.hash.replace(/^[/#?]/g, ''), paramsThatCanOverride);
    } // TODO: may not handle array params correctly

  }, {
    key: "getSomeUrlParams",
    value: function getSomeUrlParams(search, paramsThatCanOverride) {
      var params = new _babel_runtime_corejs3_core_js_stable_url_search_params__WEBPACK_IMPORTED_MODULE_3___default.a(search);
      var result = {};

      _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_0___default()(paramsThatCanOverride).call(paramsThatCanOverride, function (param) {
        return result[param] = params.get(param);
      });

      return result;
    }
  }]);

  return MatomoApi;
}();
var matomoApiService = new MatomoApi();
/* harmony default export */ __webpack_exports__["default"] = (matomoApiService);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! exports provided: MatomoApi, SiteSelector */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _common_MatomoApi__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./common/MatomoApi */ "./src/common/MatomoApi.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "MatomoApi", function() { return _common_MatomoApi__WEBPACK_IMPORTED_MODULE_0__["MatomoApi"]; });

/* harmony import */ var _common_FocusAnywhereButHere__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./common/FocusAnywhereButHere */ "./src/common/FocusAnywhereButHere.js");
/* empty/unused harmony star reexport *//* harmony import */ var _site_selector_SiteSelector__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./site-selector/SiteSelector */ "./src/site-selector/SiteSelector.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "SiteSelector", function() { return _site_selector_SiteSelector__WEBPACK_IMPORTED_MODULE_2__["SiteSelector"]; });

/* harmony import */ var _site_selector_SiteSelectorAdapter__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./site-selector/SiteSelectorAdapter */ "./src/site-selector/SiteSelectorAdapter.js");
/* empty/unused harmony star reexport */




/***/ }),

/***/ "./src/site-selector/SiteSelector.js":
/*!*******************************************!*\
  !*** ./src/site-selector/SiteSelector.js ***!
  \*******************************************/
/*! exports provided: SiteSelector */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SiteSelector", function() { return SiteSelector; });
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/object/assign */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_concat__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/concat */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/concat.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_concat__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_concat__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_map__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/map */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/map.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_map__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_map__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/bind */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/bind.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_index_of__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/index-of */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/index-of.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_index_of__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_index_of__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime-corejs3/regenerator */ "../../../node_modules/@babel/runtime-corejs3/regenerator/index.js");
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createSuper__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createSuper.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! react-dom */ "react-dom");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! classnames */ "classnames");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_13___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_13__);
/* harmony import */ var _SiteSelectorService__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./SiteSelectorService */ "./src/site-selector/SiteSelectorService.js");
/* harmony import */ var _common_FocusAnywhereButHere__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ../common/FocusAnywhereButHere */ "./src/common/FocusAnywhereButHere.js");











var _jsxFileName = "/home/dizzy/Projects/matomo/plugins/CoreHome/react/src/site-selector/SiteSelector.js";





var _window = window,
    piwik = _window.piwik,
    _pk_translate = _window._pk_translate,
    piwikHelper = _window.piwikHelper,
    $ = _window.$; // TODO: note not using prop-types for validation
// TODO: note not using immutable.js

var shortcutRegistered = false;

var AllSitesLink = /*#__PURE__*/function (_React$PureComponent) {
  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_9__["default"])(AllSitesLink, _React$PureComponent);

  var _super = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createSuper__WEBPACK_IMPORTED_MODULE_10__["default"])(AllSitesLink);

  function AllSitesLink() {
    Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_7__["default"])(this, AllSitesLink);

    return _super.apply(this, arguments);
  }

  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_8__["default"])(AllSitesLink, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("div", {
        className: "custom_select_all",
        onClick: this.props.onClick,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 17,
          columnNumber: 13
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("a", {
        onClick: function onClick(event) {
          return event.preventDefault();
        },
        href: this.getUrlAllSites(),
        tabIndex: "4",
        dangerouslySetInnerHTML: {
          __html: this.props.allSitesText
        },
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 21,
          columnNumber: 17
        }
      }));
    }
  }, {
    key: "getUrlAllSites",
    value: function getUrlAllSites() {
      var newParameters = 'module=MultiSites&action=index';
      return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
    }
  }]);

  return AllSitesLink;
}(react__WEBPACK_IMPORTED_MODULE_11__["PureComponent"]);

var SiteSelector = /*#__PURE__*/function (_React$Component) {
  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_9__["default"])(SiteSelector, _React$Component);

  var _super2 = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createSuper__WEBPACK_IMPORTED_MODULE_10__["default"])(SiteSelector);

  function SiteSelector(props) {
    var _this;

    Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_7__["default"])(this, SiteSelector);

    _this = _super2.call(this, props);
    var selectedSite = {
      id: null,
      name: ''
    };

    if (_this.props.siteid && _this.props.sitename) {
      selectedSite = {
        id: _this.props.siteid,
        name: piwik.helper.htmlDecode(_this.props.sitename)
      };
      _this.initialSelectedSite = selectedSite;
    }

    _this.state = {
      hasMultipleSitesInitially: false,
      showSitesList: false,
      sites: [],
      selectedSite: selectedSite,
      isLoading: false,
      searchTerm: ''
    };
    _this.searchInput = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createRef"]();
    _this.root = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createRef"]();
    _this.siteSelectorService = new _SiteSelectorService__WEBPACK_IMPORTED_MODULE_14__["SiteSelectorService"]({
      onlySitesWithAdminAccess: _this.props.onlySitesWithAdminAccess
    });
    return _this;
  }

  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_8__["default"])(SiteSelector, [{
    key: "hasMultipleSites",
    value: function hasMultipleSites() {
      return this.state.hasMultipleSitesInitially;
    }
  }, {
    key: "onClickSelectorLink",
    value: function onClickSelectorLink(event) {
      event.preventDefault();

      if (!this.hasMultipleSites()) {
        return;
      }

      this.setState({
        showSitesList: !this.state.showSitesList
      });

      if (!this.state.isLoading) {
        this.loadInitialSites();
      }
    }
  }, {
    key: "onKeyUpLink",
    value: function onKeyUpLink(event) {
      if (event.key.toLowerCase() === 'enter') {
        this.onClickSelectorLink();
      }
    }
  }, {
    key: "getLinkTitle",
    value: function getLinkTitle() {
      if (!this.hasMultipleSites()) {
        return '';
      }

      return _pk_translate('CoreHome_ChangeCurrentWebsite', [this.state.selectedSite.name || this.getFirstSiteName()]);
    }
  }, {
    key: "getFirstSiteName",
    value: function getFirstSiteName() {
      if (!this.state.sites.length) {
        return null;
      }

      return this.state.sites[0].name;
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      // for the initial selected site (only needed for ngmodel binding in site selector, otherwise we shouldn't use it)
      this.props.onSiteSelected && this.props.onSiteSelected(this.state.selectedSite);
      this.loadInitialSites().then(function () {
        if (!_this2.initialSelectedSite && !_this2.hasMultipleSites() && _this2.state.sites[0]) {
          _this2.setState({
            selectedSite: {
              id: _this2.state.sites[0].idsite,
              name: _this2.state.sites[0].name
            }
          });
        }
      });
      this.registerShortcut();
    }
  }, {
    key: "registerShortcut",
    value: function registerShortcut() {
      if (shortcutRegistered) {
        return;
      } // done once per page


      piwikHelper.registerShortcut('w', _pk_translate('CoreHome_ShortcutWebsiteSelector'), function (event) {
        if (event.altKey) {
          return;
        }

        if (event.preventDefault) {
          event.preventDefault();
        } else {
          event.returnValue = false; // IE
        }

        $('.siteSelector .title').trigger('click').focus();
      });
      shortcutRegistered = true;
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState, snapshot) {
      this.focusInputIfNeeded();
    }
  }, {
    key: "focusInputIfNeeded",
    value: function focusInputIfNeeded() {
      if (this.state.showSitesList && (this.props.auto <= this.state.sites.length || this.state.searchTerm)) {
        this.searchInput.current.focus();
      }
    }
  }, {
    key: "onClickAllSitesLink",
    value: function onClickAllSitesLink(event) {
      this.switchSite({
        idsite: 'all',
        name: this.allSitesText
      }, event);
      this.setState({
        showSitesList: false
      });
    }
  }, {
    key: "getUrlForSiteId",
    value: function getUrlForSiteId(idSite) {
      var idSiteParam = 'idSite=' + idSite;
      var newParameters = 'segment=&' + idSiteParam;
      var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
      return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) + '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    }
  }, {
    key: "loadInitialSites",
    value: function () {
      var _loadInitialSites = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_6__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5___default.a.mark(function _callee() {
        var sites;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                this.setState({
                  isLoading: true
                });
                _context.next = 3;
                return this.siteSelectorService.loadInitialSites();

              case 3:
                sites = _context.sent;
                this.setState({
                  sites: sites,
                  hasMultipleSitesInitially: sites.length > 1,
                  isLoading: false
                });

              case 5:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function loadInitialSites() {
        return _loadInitialSites.apply(this, arguments);
      }

      return loadInitialSites;
    }()
  }, {
    key: "searchSite",
    value: function () {
      var _searchSite = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_6__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5___default.a.mark(function _callee2(newTerm) {
        var sites;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_5___default.a.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                this.setState({
                  isLoading: true
                });
                _context2.next = 3;
                return this.siteSelectorService.searchSite(newTerm);

              case 3:
                sites = _context2.sent;
                this.setState({
                  sites: sites,
                  isLoading: false
                });

              case 5:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, this);
      }));

      function searchSite(_x) {
        return _searchSite.apply(this, arguments);
      }

      return searchSite;
    }()
  }, {
    key: "switchSite",
    value: function switchSite(site, event) {
      var _context3;

      // for Mac OS cmd key needs to be pressed, ctrl key on other systems
      var controlKey = _babel_runtime_corejs3_core_js_stable_instance_index_of__WEBPACK_IMPORTED_MODULE_4___default()(_context3 = navigator.userAgent).call(_context3, "Mac OS X") !== -1 ? event.metaKey : event.ctrlKey;

      if (event && controlKey && event.target && event.target.href) {
        window.open(event.target.href, "_blank");
        return;
      }

      var selectedSite = {
        id: site.idsite,
        name: site.name
      };
      this.setState({
        selectedSite: selectedSite
      });
      this.props.onSiteSelected && this.props.onSiteSelected(selectedSite);

      if (!this.props.switchSiteOnSelect || this.props.activeSiteId === site.idsite) {
        return;
      }

      this.loadSite(site.idsite);
    }
  }, {
    key: "loadSite",
    value: function loadSite(idSite) {
      if (idSite === 'all') {
        document.location.href = piwikHelper.getCurrentQueryStringWithParametersModified(piwikHelper.getQueryStringFromParameters({
          module: 'MultiSites',
          action: 'index',
          date: piwik.currentDateString,
          period: piwik.period
        }));
      } else {
        piwik.broadcast.propagateNewPage('segment=&idSite=' + idSite, false);
      }
    }
  }, {
    key: "render",
    value: function render() {
      var _this3 = this,
          _context4,
          _context5,
          _context6,
          _context7,
          _context8,
          _context9;

      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("div", {
        ref: this.root,
        className: classnames__WEBPACK_IMPORTED_MODULE_13___default()("siteSelector", "piwikSelector", "borderedControl", {
          expanded: this.state.showSitesList,
          disabled: !this.hasMultipleSites()
        }),
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 225,
          columnNumber: 13
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"](_common_FocusAnywhereButHere__WEBPACK_IMPORTED_MODULE_15__["default"], {
        onLoseFocus: function onLoseFocus() {
          return _this3.setState({
            showSitesList: false
          });
        },
        element: this.root,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 232,
          columnNumber: 17
        }
      }), this.renderSelectedSiteInput(), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("a", {
        onClick: _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3___default()(_context4 = this.onClickSelectorLink).call(_context4, this),
        onKeyUp: _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3___default()(_context5 = this.onKeyUpLink).call(_context5, this),
        title: this.getLinkTitle(),
        className: classnames__WEBPACK_IMPORTED_MODULE_13___default()({
          title: true,
          loading: this.state.isLoading
        }),
        tabIndex: 4,
        href: "",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 236,
          columnNumber: 17
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("span", {
        className: classnames__WEBPACK_IMPORTED_MODULE_13___default()('icon', 'icon-arrow-bottom', {
          iconHidden: this.state.isLoading,
          collapsed: !this.state.showSitesList
        }),
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 244,
          columnNumber: 21
        }
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("span", {
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 245,
          columnNumber: 21
        }
      }, (this.state.selectedSite.name || !this.props.placeholder) && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("span", {
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 247,
          columnNumber: 29
        }
      }, this.state.selectedSite.name || this.getFirstSiteName()), !this.state.selectedSite.name && this.props.placeholder && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("span", {
        className: "placeholder",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 252,
          columnNumber: 29
        }
      }, this.props.placeholder))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("div", {
        style: {
          display: !this.state.showSitesList ? 'none' : undefined
        },
        className: "dropdown",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 259,
          columnNumber: 17
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("div", {
        className: "custom_select_search",
        style: {
          display: this.props.autocompleteMinSites <= this.state.sites.length || this.state.searchTerm ? 'block' : undefined
        },
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 260,
          columnNumber: 21
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("input", {
        type: "text",
        ref: this.searchInput,
        onClick: function onClick() {
          _this3.setState({
            searchTerm: ''
          });
        },
        onChange: function onChange(event) {
          _this3.setState({
            searchTerm: event.target.value
          });

          _this3.searchSite(event.target.value);
        },
        value: this.state.searchTerm,
        placeholder: _pk_translate('General_Search'),
        tabIndex: 4,
        className: "websiteSearch inp browser-default",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 261,
          columnNumber: 25
        }
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("img", {
        title: _pk_translate("General_Clear"),
        style: {
          display: !this.state.searchTerm ? 'none' : undefined
        },
        onClick: function onClick() {
          _this3.setState({
            searchTerm: ''
          });

          _this3.loadInitialSites();
        },
        className: "reset",
        src: "plugins/CoreHome/images/reset_search.png",
        alt: _pk_translate("General_Clear"),
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 276,
          columnNumber: 25
        }
      })), this.props.allSitesLocation === 'top' && this.props.showAllSitesItem && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"](AllSitesLink, {
        onClick: _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3___default()(_context6 = this.onClickAllSitesLink).call(_context6, this),
        allSitesText: this.props.allSitesText,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 290,
          columnNumber: 25
        }
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("div", {
        className: "custom_select_container",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 292,
          columnNumber: 21
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("ul", {
        className: "custom_select_ul_list",
        onClick: function onClick() {
          return _this3.setState({
            showSitesList: false
          });
        },
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 293,
          columnNumber: 25
        }
      }, _babel_runtime_corejs3_core_js_stable_instance_map__WEBPACK_IMPORTED_MODULE_2___default()(_context7 = this.state.sites).call(_context7, function (site) {
        return _this3.renderSiteRow(site);
      })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("ul", {
        style: {
          display: this.state.sites.length || !this.state.searchTerm ? 'none' : undefined
        },
        className: "ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 296,
          columnNumber: 25
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("li", {
        className: "ui-menu-item",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 300,
          columnNumber: 29
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("a", {
        onClick: function onClick(e) {
          return e.preventDefault();
        },
        className: "ui-corner-all",
        tabIndex: -1,
        href: "#",
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 301,
          columnNumber: 33
        }
      }, _babel_runtime_corejs3_core_js_stable_instance_concat__WEBPACK_IMPORTED_MODULE_1___default()(_context8 = "".concat(_pk_translate('SitesManager_NotFound'), " ")).call(_context8, this.state.searchTerm))))), this.props.allSitesLocation === 'bottom' && this.props.showAllSitesItem && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"](AllSitesLink, {
        onClick: _babel_runtime_corejs3_core_js_stable_instance_bind__WEBPACK_IMPORTED_MODULE_3___default()(_context9 = this.onClickAllSitesLink).call(_context9, this),
        allSitesText: this.props.allSitesText,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 309,
          columnNumber: 25
        }
      })));
    }
  }, {
    key: "renderSelectedSiteInput",
    value: function renderSelectedSiteInput() {
      if (!this.props.inputName) {
        return null;
      }

      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("input", {
        type: "hidden",
        name: this.props.inputName,
        value: this.state.selectedSite.id,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 320,
          columnNumber: 16
        }
      });
    }
  }, {
    key: "renderSiteRow",
    value: function renderSiteRow(site) {
      var _this4 = this;

      var parts = !this.state.searchTerm ? [site.name] : site.name.split(this.state.searchTerm);
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("li", {
        key: site.idsite,
        onClick: function onClick(event) {
          return _this4.switchSite(site, event);
        },
        style: {
          display: !this.state.showSelectedSite && this.props.activeSiteId === site.idsite ? 'none' : undefined
        },
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 327,
          columnNumber: 13
        }
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("a", {
        onClick: function onClick(event) {
          return event.preventDefault();
        },
        href: this.getUrlForSiteId(site.idsite),
        title: site.name,
        tabIndex: 4,
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 332,
          columnNumber: 17
        }
      }, _babel_runtime_corejs3_core_js_stable_instance_map__WEBPACK_IMPORTED_MODULE_2___default()(parts).call(parts, function (w, i) {
        if (i === 0) {
          return w;
        }

        return [/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"]("span", {
          key: i,
          className: "autocompleteMatched",
          __self: _this4,
          __source: {
            fileName: _jsxFileName,
            lineNumber: 344,
            columnNumber: 29
          }
        }, _this4.state.searchTerm), w];
      })));
    }
  }], [{
    key: "renderTo",
    value: function renderTo(element, props) {
      react_dom__WEBPACK_IMPORTED_MODULE_12___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_11__["createElement"](SiteSelector, _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default()({}, props, {
        __self: this,
        __source: {
          fileName: _jsxFileName,
          lineNumber: 354,
          columnNumber: 25
        }
      })), element);
    }
  }]);

  return SiteSelector;
}(react__WEBPACK_IMPORTED_MODULE_11__["Component"]);
SiteSelector.defaultProps = {
  autocompleteMinSites: piwik.config.autocomplete_min_sites,
  activeSiteId: piwik.idSite
};

/***/ }),

/***/ "./src/site-selector/SiteSelectorAdapter.js":
/*!**************************************************!*\
  !*** ./src/site-selector/SiteSelectorAdapter.js ***!
  \**************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/object/assign */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/object/assign.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _SiteSelector__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SiteSelector */ "./src/site-selector/SiteSelector.js");


var _window = window,
    angular = _window.angular;
angular.module('piwikApp').directive('piwikSiteselector', piwikSiteselector);
piwikSiteselector.$inject = ['piwik', '$filter', '$timeout'];

function piwikSiteselector(piwik, $filter, $timeout) {
  var defaults = {
    name: '',
    siteid: piwik.idSite,
    sitename: piwik.helper.htmlDecode(piwik.siteName),
    allSitesLocation: 'bottom',
    allSitesText: $filter('translate')('General_MultiSitesSummary'),
    showSelectedSite: 'false',
    showAllSitesItem: 'true',
    switchSiteOnSelect: 'true',
    onlySitesWithAdminAccess: 'false'
  };
  return {
    restrict: 'A',
    scope: {
      showSelectedSite: '=',
      showAllSitesItem: '=',
      switchSiteOnSelect: '=',
      onlySitesWithAdminAccess: '=',
      inputName: '@name',
      allSitesText: '@',
      allSitesLocation: '@',
      placeholder: '@'
    },
    require: "?ngModel",
    compile: function compile(element, attrs) {
      for (var index in defaults) {
        if (attrs[index] === undefined) {
          attrs[index] = defaults[index];
        }
      }

      return {
        pre: function pre(scope, element, attrs, ngModel) {
          scope.siteid = attrs.siteid;
          scope.sitename = attrs.sitename;

          scope.onSiteSelected = function (selectedSite) {
            if (scope.selectedSite != selectedSite) {
              scope.selectedSite = _babel_runtime_corejs3_core_js_stable_object_assign__WEBPACK_IMPORTED_MODULE_0___default()({}, selectedSite);
              element.attr('siteid', selectedSite.id);
              element.trigger('change', scope.selectedSite);

              if (ngModel) {
                ngModel.$setViewValue(selectedSite);
              }
            }
          };

          if (ngModel) {
            ngModel.$render = function () {
              if (angular.isString(ngModel.$viewValue)) {
                scope.selectedSite = JSON.parse(ngModel.$viewValue);
              } else {
                scope.selectedSite = ngModel.$viewValue;
              }
            };
          }

          $timeout(function () {
            window.initTopControls();
          });
        },
        post: function postLink(scope, element, attrs) {
          $timeout(function () {
            _SiteSelector__WEBPACK_IMPORTED_MODULE_1__["SiteSelector"].renderTo(element[0], scope);
          });
        }
      };
    }
  };
}

/***/ }),

/***/ "./src/site-selector/SiteSelectorService.js":
/*!**************************************************!*\
  !*** ./src/site-selector/SiteSelectorService.js ***!
  \**************************************************/
/*! exports provided: SiteSelectorService */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SiteSelectorService", function() { return SiteSelectorService; });
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_sort__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/sort */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/sort.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_sort__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_sort__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime-corejs3/core-js-stable/instance/for-each */ "../../../node_modules/@babel/runtime-corejs3/core-js-stable/instance/for-each.js");
/* harmony import */ var _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime-corejs3/regenerator */ "../../../node_modules/@babel/runtime-corejs3/regenerator/index.js");
/* harmony import */ var _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! /home/dizzy/Projects/matomo/node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ "../../../node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _common_MatomoApi__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../common/MatomoApi */ "./src/common/MatomoApi.js");







var SiteSelectorService = /*#__PURE__*/function () {
  function SiteSelectorService(_ref) {
    var onlySitesWithAdminAccess = _ref.onlySitesWithAdminAccess;

    Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_4__["default"])(this, SiteSelectorService);

    this.initialSites = null;
    this.numWebsitesToDisplayPerPage = null;
    this.onlySitesWithAdminAccess = onlySitesWithAdminAccess;
  }

  Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_5__["default"])(SiteSelectorService, [{
    key: "loadInitialSites",
    value: function () {
      var _loadInitialSites = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.mark(function _callee() {
        var sites;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                if (!this.initialSites) {
                  _context.next = 2;
                  break;
                }

                return _context.abrupt("return", this.initialSites);

              case 2:
                _context.next = 4;
                return this.searchSite('%');

              case 4:
                sites = _context.sent;
                this.initialSites = sites;
                return _context.abrupt("return", sites);

              case 7:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function loadInitialSites() {
        return _loadInitialSites.apply(this, arguments);
      }

      return loadInitialSites;
    }() // TODO: request aborting not implemented

  }, {
    key: "searchSite",
    value: function () {
      var _searchSite = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.mark(function _callee2(term) {
        var limit, methodToCall, result;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                if (term) {
                  _context2.next = 4;
                  break;
                }

                _context2.next = 3;
                return this.loadInitialSites();

              case 3:
                return _context2.abrupt("return", _context2.sent);

              case 4:
                _context2.next = 6;
                return this.getNumWebsitesToDisplayPerPage();

              case 6:
                limit = _context2.sent;
                methodToCall = this.onlySitesWithAdminAccess ? 'SitesManager.getSitesWithAdminAccess' : 'SitesManager.getPatternMatchSites';
                _context2.next = 10;
                return _common_MatomoApi__WEBPACK_IMPORTED_MODULE_6__["default"].fetch({
                  method: methodToCall,
                  limit: limit,
                  pattern: term
                });

              case 10:
                result = _context2.sent;

                if (!result || !result.length) {
                  result = [];
                }

                _babel_runtime_corejs3_core_js_stable_instance_for_each__WEBPACK_IMPORTED_MODULE_1___default()(result).call(result, function (site) {
                  if (site.group) site.name = '[' + site.group + '] ' + site.name;
                });

                _babel_runtime_corejs3_core_js_stable_instance_sort__WEBPACK_IMPORTED_MODULE_0___default()(result).call(result, function (lhs, rhs) {
                  if (lhs < rhs) {
                    return -1;
                  }

                  return lhs > rhs ? 1 : 0;
                });

                return _context2.abrupt("return", result);

              case 15:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, this);
      }));

      function searchSite(_x) {
        return _searchSite.apply(this, arguments);
      }

      return searchSite;
    }()
  }, {
    key: "getNumWebsitesToDisplayPerPage",
    value: function () {
      var _getNumWebsitesToDisplayPerPage = Object(_home_dizzy_Projects_matomo_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])( /*#__PURE__*/_babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.mark(function _callee3() {
        var result;
        return _babel_runtime_corejs3_regenerator__WEBPACK_IMPORTED_MODULE_2___default.a.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                if (!(this.numWebsitesToDisplayPerPage !== null)) {
                  _context3.next = 2;
                  break;
                }

                return _context3.abrupt("return", this.numWebsitesToDisplayPerPage);

              case 2:
                _context3.next = 4;
                return _common_MatomoApi__WEBPACK_IMPORTED_MODULE_6__["default"].fetch({
                  method: 'SitesManager.getNumWebsitesToDisplayPerPage'
                });

              case 4:
                result = _context3.sent;
                this.numWebsitesToDisplayPerPage = result.value;
                return _context3.abrupt("return", result.value);

              case 7:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3, this);
      }));

      function getNumWebsitesToDisplayPerPage() {
        return _getNumWebsitesToDisplayPerPage.apply(this, arguments);
      }

      return getNumWebsitesToDisplayPerPage;
    }()
  }]);

  return SiteSelectorService;
}();

/***/ }),

/***/ "axios":
/*!************************!*\
  !*** external "axios" ***!
  \************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_axios__;

/***/ }),

/***/ "classnames":
/*!*****************************!*\
  !*** external "classNames" ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_classnames__;

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_react__;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_react_dom__;

/***/ })

/******/ });
});
//# sourceMappingURL=main.js.map