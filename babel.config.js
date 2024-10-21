module.exports = {
  assumptions: {
    setSpreadProperties: true,
  },
  presets: [
    ['@vue/cli-plugin-babel/preset', {
      useBuiltIns: false,
      // NOTE: we are disabling generator and async/await use due to
      // https://github.com/vuejs/vue-cli/blob/aad72cfa7880a0e327be06b3b9c3ac3d3b3c9abc/packages/%40vue/babel-preset-app/index.js#L250
      // which hardcodes the use of an inlined regenerator runtime polyfill. Using it means we have to include
      // 6kb extra minified code in every plugin's UMD file. We could use a shared, global runtime for regenerator,
      // but @vue/babel-preset-app won't allow us to only set the linked `regenerator` property to false while
      // keeping the rest of the settings the same.
      // TODO: create an issue in vue for this ^? Or maybe the final gzipped asset's size will not be affected?
      exclude: [
        'transform-async-to-generator',
        'transform-regenerator',
        'proposal-async-generator-functions',
      ]
    }],
  ],
};
