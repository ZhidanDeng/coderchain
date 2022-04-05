const path = require('path');
const isDev = require('./src/utils/isDev')
const resolve = dir => path.join(__dirname, dir);

module.exports = {
  assetsDir: 'assets',
  // index.html中资源的前缀
  // publicPath: isDev ? './' : 'https://static.coderchain.cn/',
  chainWebpack: config => {
    config.resolve.alias
      .set('@', resolve('src'))
      .set('@assets', resolve('src/assets'))
      .set('@components', resolve('src/components'))
      .set('@api', resolve('src/api'))
      .set('@utils', resolve('src/utils'))
      .set('@config', resolve('src/config'));
  },
  productionSourceMap: false,
  devServer: {
      host: '127.0.0.1',
      port: 8081,
      proxy: {
          '/': {
              target: 'http://localhost:81/index.php',
              changeOrigin: true,
              ws: true,
              // pathRewrite: {
              //   '^/api': ''
              // }
          }
      }

    }
};
