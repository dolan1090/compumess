const { join, resolve } = require('path');

process.env.ADMIN_PATH = process.env.ADMIN_PATH || resolve('../../../../../../../src/Administration/Resources/app/administration');

module.exports = {
    preset: '@shopware-ag/jest-preset-sw6-admin',
    globals: {
        adminPath: process.env.ADMIN_PATH,
    },

    setupFilesAfterEnv: [
        resolve(join(process.env.ADMIN_PATH, '/test/_setup/prepare_environment.js')),
    ],

    moduleDirectories:[
        '<rootDir>/node_modules',
        resolve(join(process.env.ADMIN_PATH, '/node_modules')),
    ],

    moduleNameMapper: {
        '^uuid$': require.resolve('uuid'),
        '^\@shopware-ag\/admin-extension-sdk\/es\/(.*)': resolve(join(process.env.ADMIN_PATH, '/node_modules')) + '/@shopware-ag/admin-extension-sdk/umd/$1',
        '^test(.*)$': '<rootDir>/test$1',
        vue$: 'vue/dist/vue.common.dev.js',
    },

    testMatch: [
        '<rootDir>/test/e2e/cypress/integration/*.spec.js',
        '<rootDir>/test/**/*.spec.js',
        '<rootDir>/test/module/sw-order/component/*.spec.js',
    ],

    transformIgnorePatterns: [
        '/node_modules/(?!(@shopware-ag/meteor-icon-kit|uuidv7|other)/)',
    ],
};
