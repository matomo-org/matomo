const path = require('path')
const webpack = require('webpack')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const TerserPlugin = require("terser-webpack-plugin");
const fs = require('fs');

const cwd = process.cwd();
const libPackageJson = require(path.join(cwd, 'package.json'));

const isEnvDevelopment = process.env.NODE_ENV !== 'production';
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
    entry: './src/index.ts',
    output: {
        path: path.resolve(cwd, './dist'),
        publicPath: '/dist/',
        filename: isEnvDevelopment ? 'bundle.js' : 'bundle.min.js',
        pathinfo: isEnvDevelopment,
        // this defaults to 'window', but by setting it to 'this' then
        // module chunks which are built will work in web workers as well.
        globalObject: 'this',
        libraryTarget: 'umd',
        library: libPackageJson.name,
    },
    externals: [
        { vue: 'Vue' },
        pluginExternals,
    ],
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    loaders: {
                        // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
                        // the "scss" and "sass" values for the lang attribute to the right configs here.
                        // other preprocessors should work out of the box, no loader config like this necessary.
                        'scss': 'vue-style-loader!css-loader!sass-loader',
                        'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax',
                    }
                    // other vue-loader options go here
                }
            },
            {
                test: /\.tsx?$/,
                loader: 'ts-loader',
                exclude: /node_modules/,
                options: {
                    appendTsSuffixTo: [/\.vue$/],
                }
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]?[hash]'
                }
            },
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ]
            }
        ]
    },
    mode: isEnvDevelopment ? 'development' : 'production',
    resolve: {
        extensions: ['.ts', '.js', '.vue', '.json'],
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true
    },
    performance: {
        hints: false
    },
    devtool: 'eval-source-map',
    plugins: [
        // make sure to include the plugin for the magic
        new VueLoaderPlugin()
    ],
};

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = 'source-map';
    module.exports.optimization = {
        minimize: true,
            minimizer: [new TerserPlugin()],
    };
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ]);
}