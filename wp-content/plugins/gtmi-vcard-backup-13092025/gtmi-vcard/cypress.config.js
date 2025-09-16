const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
    baseUrl: "http://nfcfrance.fl",
    supportFile: "cypress/support/commands.js",
    pageLoadTimeout: 60000,
  },
  defaultCommandTimeout: 7000,
  chromeWebSecurity: false
});
