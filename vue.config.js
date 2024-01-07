const fs = require('fs');
const path = require('path');

const pluginExternals = getPluginExternals();

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

function getPluginExternals() {
  if (!process.env.MATOMO_ALL_PLUGINS) {
    return scanPluginExternals();
  }

  const allPluginsWithVueFolder = process.env.MATOMO_ALL_PLUGINS.split(',');
  return Object.fromEntries(allPluginsWithVueFolder.map((name) => [name, name]));
}

if (!process.env.MATOMO_CURRENT_PLUGIN) {
  console.log("The MATOMO_CURRENT_PLUGIN environment variable is not set!");
}

const pluginPath = process.env.MATOMO_CURRENT_PLUGIN || '';
const pluginName = path.basename(pluginPath);

const srcPath = `${pluginPath}/vue/src/`;
const publicPath = `${pluginPath}/vue/dist/`;

// hack to get publicPath working for lib build target (see https://github.com/vuejs/vue-cli/issues/4896#issuecomment-569001811)
function PublicPathWebpackPlugin() {}

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

const detectedDependentPlugins = [];

function OutputDetectedDependentPluginsPlugin() {}
OutputDetectedDependentPluginsPlugin.prototype.apply = function (compiler) {
  compiler.hooks.afterCompile.tap('OutputDetectedDependentPluginsPlugin', (context, entry) => {
    const metadataPath = path.join(publicPath, 'umd.metadata.json');
    const metadata = {
      dependsOn: detectedDependentPlugins,
    };
    if (fs.existsSync(srcPath)) {
      fs.mkdirSync(path.dirname(metadataPath), {recursive: true});
      fs.writeFileSync(metadataPath, JSON.stringify(metadata, null, 2));
    }
  });
};

module.exports = {
  publicPath,
  chainWebpack: config => {
    config.plugin('output-detected-dependent-plugins').use(OutputDetectedDependentPluginsPlugin);
    config.plugin('public-path-webpack').use(PublicPathWebpackPlugin);
    config.externals(function (context, request, callback) {
      if (request === 'tslib') {
        callback(null, 'tslib');
        return;
      }

      if (pluginExternals[request]) {
        if (detectedDependentPlugins.indexOf(request) === -1
          && request !== pluginName
        ) {
          detectedDependentPlugins.push(request);
        }
        callback(null, pluginExternals[request]);
        return;
      }

      callback();
    });

    // disable asset size warnings
    config.performance.hints(false);

    config.watchOptions({
      ignored: /node_modules/,
    });

    // override config so we can generate type definitions for plugin libraries
    // see https://github.com/vuejs/vue-cli/issues/6543
    if (process.env.NODE_ENV !== 'development') {
      config.module
        .rule('ts')
        .uses
        .delete('thread-loader');

      config.module
        .rule('ts')
        .use('ts-loader')
        .tap((options) => {
          options.transpileOnly = false;
          options.happyPackMode = false;
          options.compilerOptions = {
            declaration: true,
            noEmit: false,
            outDir: `${__dirname}/@types/${pluginName}`,
            paths: {
              '*': [`${__dirname}/@types/*`, `${__dirname}/node_modules/*`, '*']
            },
          };
          return options;
        });
    }
  },
};
