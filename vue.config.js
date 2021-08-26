const fs = require('fs');
const path = require('path');

const pluginExternals = scanPluginExternals();

function scanPluginExternals() {
  const pluginExternals = {};

  const pluginsDir = path.join(__dirname, '..', '..');
  for (let pluginName of fs.readdirSync(pluginsDir)) {
    const vuePackageJsonPath = path.join(pluginsDir, pluginName, 'vue', 'package.json');
    if (!fs.existsSync(vuePackageJsonPath)) {
      continue;
    }

    const vuePackageJson = require(vuePackageJsonPath);
    pluginExternals[vuePackageJson.name] = vuePackageJson.name;
  }

  return pluginExternals;
}

module.exports = {
  chainWebpack: config => {
    config.externals({
      'vue-class-component': 'VueClassComponent',
      ...pluginExternals,
    });
  },
};
