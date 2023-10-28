const { join, resolve } = require('path');

process.env.PROJECT_ROOT = process.env.PROJECT_ROOT || resolve(__dirname, '../../../../../../../');
process.env.ADMIN_PATH = process.env.ADMIN_PATH || resolve(process.env.PROJECT_ROOT, 'src/Administration/Resources/app/administration');

const artifactsPath = process.env.ARTIFACTS_PATH ? join(process.env.ARTIFACTS_PATH, '/build/artifacts/jest') : 'coverage';

module.exports = {
    preset: '@shopware-ag/jest-preset-sw6-admin',
    globals: {
        projectRoot: process.env.PROJECT_ROOT,
        adminPath: process.env.ADMIN_PATH,
    },
    displayName: {
        name: 'SocialShopping Administration',
        color: 'lime'
    },

    reporters: [
        'default', [
            'jest-junit',
            {
                'suiteName': 'SocialShopping Administration',
                'outputDirectory': artifactsPath,
                'outputName': 'social-shopping-administration-jest.xml',
                'uniqueOutputName': 'false'
            },
        ],
    ],

    setupFilesAfterEnv: [
        resolve(join(process.env.ADMIN_PATH, '/test/_setup/prepare_environment.js')),
    ],

    moduleNameMapper: {
        '^\@shopware-ag/admin-extension-sdk/es(.*)$': `${process.env.ADMIN_PATH}/node_modules/@shopware-ag/admin-extension-sdk/umd$1`,
        '^src(.*)$': `${process.env.ADMIN_PATH}/src$1`, // Required for imports inside the platform administration
        '^\@administration(.*)$': `${process.env.ADMIN_PATH}/src$1`,
        '^test(.*)$': '<rootDir>/test$1',
        vue$: 'vue/dist/vue.common.dev.js',
    },

    testMatch: [
        '<rootDir>/test/**/*.spec.(t|j)s',
    ],

    collectCoverage: true,
    collectCoverageFrom: ['src/**/*.(t|j)s'],
    coverageDirectory: artifactsPath,

    transformIgnorePatterns: [
        'node_modules/(?!uuidv7|other)',
    ],
};
