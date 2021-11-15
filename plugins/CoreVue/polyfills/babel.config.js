module.exports = {
  presets: [
    ['@vue/cli-plugin-babel/preset', {
      polyfills: [
        'es.array.iterator',
        'es.promise',
        'es.object.assign',
        'es.promise.finally',
        'es.object.entries',

        // TODO: what else do we want included?
      ],
    }],
  ],
};
