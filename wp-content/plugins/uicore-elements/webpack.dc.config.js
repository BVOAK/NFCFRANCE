const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'design-cloud': path.resolve(__dirname, 'assets/src/design-cloud/index.js'),
    },
    output: {
        path: path.resolve(__dirname, 'assets/dc/'),
        filename: '[name].js',
    },
    // resolve: {
    //     alias: {
    //         '@uicore/design-cloud': path.resolve(__dirname, '../../../../../../design-cloud-ui')
    //     }
    // },
};