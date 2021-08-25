module.exports = {
  presets: [
    ['@vue/cli-plugin-babel/preset', {
      useBuiltIns: false,
      // TODO: I can't find a way to exclude the regenerator runtime from compiled UMDs, so for now
      // disabling generators, async/await
      exclude: [
        'transform-async-to-generator',
        'transform-regenerator',
        'proposal-async-generator-functions',
      ]
    }],
  ],
};
