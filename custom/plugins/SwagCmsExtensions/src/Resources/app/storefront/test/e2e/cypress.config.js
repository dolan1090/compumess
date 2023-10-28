const { defineConfig } = require("cypress");

module.exports = defineConfig({
  viewportHeight: 1080,
  viewportWidth: 1920,
  watchForFileChanges: true,
  requestTimeout: 60000,
  responseTimeout: 80000,
  defaultCommandTimeout: 30000,
  salesChannelName: "Storefront",
  useDarkTheme: false,
  video: false,
  useShopwareTheme: true,
  theme: "dark",
  screenshotsFolder: "./../app/build/artifacts/e2e/screenshots",
  modifyObstructiveCode: false,
  reporter: "cypress-multi-reporters",

  env: {
    apiPath: "api",
  },

  reporterOptions: {
    configFile: "reporter-config.json",
  },

  e2e: {
    setupNodeEvents(on, config) {
    },
  },
});
