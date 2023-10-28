const { defineConfig } = require('cypress')

module.exports = defineConfig({
  viewportHeight: 1080,
  viewportWidth: 1920,
  watchForFileChanges: false,
  requestTimeout: 30000,
  responseTimeout: 60000,
  defaultCommandTimeout: 30000,
  salesChannelName: 'Storefront',
  useDarkTheme: false,
  video: false,
  useShopwareTheme: true,
  theme: 'dark',
  screenshotsFolder: './../app/build/artifacts/e2e/screenshots',
  reporter: 'cypress-multi-reporters',
  reporterOptions: {
    configFile: 'reporter-config.json',
  },
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
    baseUrl: 'http://localhost:8000',
  },
})
