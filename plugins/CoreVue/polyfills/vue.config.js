module.exports = {
  filenameHashing: false,
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
    config.output.filename(process.env.NODE_ENV === 'production' ? 'MatomoPolyfills.min.js' : 'MatomoPolyfills.js');
  }
};
