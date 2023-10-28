const { defineConfig } = require("cypress");

module.exports = defineConfig({
  viewportHeight: 1080,
  viewportWidth: 1920,
  watchForFileChanges: true,
  requestTimeout: 30000,
  responseTimeout: 60000,
  defaultCommandTimeout: 30000,
  salesChannelName: "Storefront",
  useDarkTheme: false,
  video: false,
  useShopwareTheme: true,
  theme: "dark",
  screenshotsFolder: "./../app/build/artifacts/e2e/screenshots",
  reporter: "cypress-multi-reporters",

  reporterOptions: {
    configFile: "reporter-config.json",
  },

  env: {
    user: "admin",
    pass: "shopware",
    salesChannelName: "Storefront",
    admin: "/admin",
    apiPath: "/api",
    locale: "en-GB",
    projectRoot: "/app",
    localUsage: false,
  },

  e2e: {
    setupNodeEvents(on, config) {
    },
  },
});
