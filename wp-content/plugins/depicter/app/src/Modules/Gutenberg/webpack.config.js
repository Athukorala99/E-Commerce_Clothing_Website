const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');

module.exports = {
  ...defaultConfig,
  entry: path.resolve(__dirname, 'src/index.js'),
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'depicter-gutenberg-blocks.js',
    chunkFilename: '[name].js',
  },
};
