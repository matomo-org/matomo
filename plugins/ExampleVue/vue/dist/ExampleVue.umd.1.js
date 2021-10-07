((typeof self !== 'undefined' ? self : this)["webpackJsonpExampleVue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpExampleVue"] || []).push([[1],{

/***/ "2d21":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=template&id=81bb8936

function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.decrement && _ctx.decrement(...args))
  }, "-"), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.count) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.increment && _ctx.increment(...args))
  }, "+")]);
}
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=template&id=81bb8936

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=script&lang=ts

/* harmony default export */ var ExampleComponentvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  data() {
    return {
      count: 12
    };
  },

  setup() {
    return {
      increment() {
        this.count += 1;
      },

      decrement() {
        this.count -= 1;
      }

    };
  }

}));
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent/ExampleComponent.vue



ExampleComponentvue_type_script_lang_ts.render = render

/* harmony default export */ var ExampleComponent = __webpack_exports__["default"] = (ExampleComponentvue_type_script_lang_ts);

/***/ })

}]);
//# sourceMappingURL=ExampleVue.umd.1.js.map