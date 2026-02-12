// playwright.config.cjs
const { defineConfig } = require("@playwright/test");

module.exports = defineConfig({
  testDir: "./tests-e2e",
  use: {
    baseURL: "http://localhost/webprogramming2025-milestone2/spapp/index.html",
    headless: true,
  },
});
