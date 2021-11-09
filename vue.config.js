const fs = require('fs');
const path = require('path');

const pluginExternals = scanPluginExternals();

function scanPluginExternals() {
  const pluginExternals = {};

  const pluginsDir = path.join(__dirname, 'plugins');
  for (let pluginName of fs.readdirSync(pluginsDir)) {
    const vuePackageFolder = path.join(pluginsDir, pluginName, 'vue', 'src');
    if (!fs.existsSync(vuePackageFolder)) {
      continue;
    }

    pluginExternals[pluginName] = pluginName;
  }

  return pluginExternals;
}

if (!process.env.MATOMO_CURRENT_PLUGIN) {
  console.log("The MATOMO_CURRENT_PLUGIN environment variable is not set!");
}

const publicPath = `plugins/${process.env.MATOMO_CURRENT_PLUGIN}/vue/dist/`;

// hack to get publicPath working for lib build target (see https://github.com/vuejs/vue-cli/issues/4896#issuecomment-569001811)
function PublicPathWebpackPlugin () {}

PublicPathWebpackPlugin.prototype.apply = function (compiler) {
  compiler.hooks.entryOption.tap('PublicPathWebpackPlugin', (context, entry) => {
    if (entry['module.common']) {
      entry['module.common'] = path.resolve(__dirname, './src/main.js');
    }
    if (entry['module.umd']) {
      entry['module.umd'] = path.resolve(__dirname, './src/main.js');
    }
    if  (entry['module.umd.min']) {
      entry['module.umd.min'] = path.resolve(__dirname, './src/main.js');
    }
  });
  compiler.hooks.beforeRun.tap('PublicPathWebpackPlugin', (compiler) => {
    compiler.options.output.publicPath = publicPath;
  });
};

module.exports = {
  publicPath,
  chainWebpack: config => {
    config.plugin().use(PublicPathWebpackPlugin);
    config.externals({
      'tslib': 'tslib',
      ...pluginExternals,
    });
  },
};
