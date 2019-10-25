
var path = require('path');
var webpack = require("webpack");

module.exports = {
  entry: path.join(__dirname, "data", "eccube.js"),
  //devtool: 'source-map',
  output: {
    path: path.join(__dirname, "html/js"),
    filename: "eccube.js"
  },
  resolve: {
      alias: {
          jquery: path.join(__dirname, 'node_modules', 'jquery')
      }
  },
  plugins: [
      new webpack.ProvidePlugin({
          $: "jquery",
          jQuery: "jquery",
          "window.jQuery": "jquery"
      })
  ]
};
