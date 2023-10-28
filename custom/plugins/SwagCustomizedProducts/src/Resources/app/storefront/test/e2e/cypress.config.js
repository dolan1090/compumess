const { defineConfig } = require('cypress')

module.exports = defineConfig({
    viewportHeight: 1080,
    viewportWidth: 1920,
    watchForFileChanges: true,
    requestTimeout: 60000,
    responseTimeout: 80000,
    defaultCommandTimeout: 30000,
    salesChannelName: 'Storefront',
    useDarkTheme: false,
    video: false,
    useShopwareTheme: true,
    theme: 'dark',
    screenshotsFolder: './../../var/log/e2e/screenshots',
    fixturesFolder: 'cypress/fixtures',
    modifyObstructiveCode: false,
    env: {
        user: 'admin',
        pass: 'shopware',
        salesChannelName: 'Storefront',
        admin: '/admin',
        apiPath: 'api',
        locale: 'en-GB',
        shopwareRoot: '/app',
        localUsage: false,
        usePercy: false,
        minAuthTokenLifetime: 60,
        acceptLanguage: 'en-GB,en;q=0.5',
        dbUser: 'root',
        dbPassword: 'root',
        dbHost: 'mysql',
        dbName: 'shopware_e2e',
        expectedVersion: '6.4.',
    },
    retries: {
        runMode: 2,
        openMode: 0,
    },
    reporter: 'cypress-multi-reporters',
    reporterOptions: {
        reporterEnabled: 'mochawesome, mocha-junit-reporter',
        mochawesomeReporterOptions: {
            reportDir: './../../var/log/e2e/results/mocha',
            quite: true,
            overwrite: false,
            html: false,
            json: true,
        },
        mochaJunitReporterReporterOptions: {
            mochaFile: "cypress/results/single-reports/results-[hash].junit.xml"
        },
    },
    e2e: {
        baseUrl: 'http://localhost:8000',
        supportFile: 'cypress/support/index.{js,jsx,ts,tsx}',
        specPattern: '**/*.spec.{js,jsx,ts,tsx}',
    },
})
