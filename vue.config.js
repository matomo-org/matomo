const fs = require('fs');
const path = require('path');

const pluginExternals = scanPluginExternals();

function scanPluginExternals() {
  const pluginExternals = {};

  const pluginsDir = path.join(__dirname, '..', '..');
  for (let pluginName of fs.readdirSync(pluginsDir)) {
    const vuePackageFolder = path.join(pluginsDir, pluginName, 'vue', 'src');
    if (!fs.existsSync(vuePackageFolder)) {
      continue;
    }

    pluginExternals[pluginName] = pluginName;
  }

  return pluginExternals;
}

module.exports = {
  publicPath: "",
  chainWebpack: config => {
    config.externals({
      'tslib': 'tslib',
      'vue-class-component': 'VueClassComponent',
      ...pluginExternals,
    });
  },
};
