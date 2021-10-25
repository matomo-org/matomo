const path = require('path');

const context = path.join(__dirname, '..', '..', '..');

module.exports = {
  filenameHashing: false,
  chainWebpack: config => {
    config.context(context);
    config.optimization.delete('splitChunks')
    config.output.filename(process.env.NODE_ENV === 'production' ? 'MatomoPolyfills.min.js' : 'MatomoPolyfills.js');
    // see https://github.com/webpack/webpack/issues/3603#issuecomment-357664819 for this workaround
    config.output.devtoolModuleFilenameTemplate(function (info) {
      const rel = path.relative(context, info.absoluteResourcePath)
      return `webpack:///${rel}`
    });
  }
};
