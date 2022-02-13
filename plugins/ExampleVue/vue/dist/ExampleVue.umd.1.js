((typeof self !== 'undefined' ? self : this)["webpackJsonpExampleVue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpExampleVue"] || []).push([[1],{

/***/ "2d21":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=template&id=5199cc7c&scoped=true


Object(external_commonjs_vue_commonjs2_vue_root_Vue_["pushScopeId"])("data-v-5199cc7c");

var _hoisted_1 = {
  class: "example-component"
};

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "ui-confirm exampleDialog"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, "Alert"), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, " The count is greater than 1 right now! "), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "button",
  value: "OK",
  role: "yes"
})], -1);

Object(external_commonjs_vue_commonjs2_vue_root_Vue_["popScopeId"])();

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[0] || (_cache[0] = function () {
      return _ctx.decrement && _ctx.decrement.apply(_ctx, arguments);
    })
  }, "-"), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.count) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[1] || (_cache[1] = function () {
      return _ctx.increment && _ctx.increment.apply(_ctx, arguments);
    })
  }, "+"), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showDialog,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.showDialog = $event;
    })
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_hoisted_2];
    }),
    _: 1
  }, 8, ["modelValue"])]);
}
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=template&id=5199cc7c&scoped=true

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=script&lang=ts


/* harmony default export */ var ExampleComponentvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoDialog: external_CoreHome_["MatomoDialog"]
  },
  data: function data() {
    return {
      count: 12,
      showDialog: false
    };
  },
  methods: {
    increment: function increment() {
      this.count += 1;
      this.showDialog = this.count > 15;
    },
    decrement: function decrement() {
      this.count -= 1;
      this.showDialog = this.count > 15;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=style&index=0&id=5199cc7c&lang=less&scoped=true
var ExampleComponentvue_type_style_index_0_id_5199cc7c_lang_less_scoped_true = __webpack_require__("4044");

// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue





ExampleComponentvue_type_script_lang_ts.render = render
ExampleComponentvue_type_script_lang_ts.__scopeId = "data-v-5199cc7c"

/* harmony default export */ var ExampleComponent = __webpack_exports__["default"] = (ExampleComponentvue_type_script_lang_ts);

/***/ }),

/***/ "4044":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_10_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_10_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_10_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_10_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExampleComponent_vue_vue_type_style_index_0_id_5199cc7c_lang_less_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("9852");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_10_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_10_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_10_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_10_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExampleComponent_vue_vue_type_style_index_0_id_5199cc7c_lang_less_scoped_true__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_10_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_10_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_10_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_10_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExampleComponent_vue_vue_type_style_index_0_id_5199cc7c_lang_less_scoped_true__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "9852":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ })

}]);
//# sourceMappingURL=ExampleVue.umd.1.js.map