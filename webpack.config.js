
var path = require('path');
var webpack = require("webpack");

const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
    mode: 'production',
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
                            presets: ['@babel/preset-env']
                        }
                    }
                ],
                exclude: /node_modules/
            },
            {
                test: require.resolve("jquery"),
                loader: "expose-loader",
                options: {
                    exposes: ["$", "jQuery"],
                },
            },
        ],
    },
    plugins: [new TerserPlugin()],
};
