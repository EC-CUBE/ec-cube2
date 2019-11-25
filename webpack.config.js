
var path = require('path');
var webpack = require("webpack");

module.exports = {
    entry: {
        eccube: './data/eccube.js',
    },
    devtool: 'source-map',
    output: {
        path: path.join(__dirname, "html/js"),
        filename: '[name].js',
    },
    resolve: {
        alias: {
            jquery: path.join(__dirname, 'node_modules', 'jquery'),
        }
    },
    module: {
        rules: [
            {
                test: /\.css/,
                use: [
                    'style-loader',
                    {
                        loader: 'css-loader'
                    }
                ],
            },
            {
                test: /\.png|jpg|svg|gif|eot|wof|woff|ttf$/,
                use: ['url-loader']
            },
            {
                test: /\.js$/,
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['env', {'modules': false}]
                            ]
                        }
                    }
                ],
                exclude: /node_modules/
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            "window.jQuery": "jquery"
        })
    ]
};
