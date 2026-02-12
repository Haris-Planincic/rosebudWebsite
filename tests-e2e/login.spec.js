import { test, expect } from "@playwright/test";

const APP = "http://localhost/webprogramming2025-milestone2/spapp/index.html";
const LOGIN_URL = "**/backend/auth/login";


test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  await page.route(LOGIN_URL, async (route) => {
    if (route.request().method() !== "POST") return route.fallback();
    let email = "";
    let password = "";

    try {
      const data = route.request().postDataJSON();
      email = data?.email ?? "";
      password = data?.password ?? "";
    } catch {
      const raw = route.request().postData() || "";
      email = raw.includes("wrong@example.com") ? "wrong@example.com" : "";
      password = raw.includes("wrongpass") ? "wrongpass" : "";
    }

    const isBad = email === "wrong@example.com" || password === "wrongpass";

    if (isBad) {
      return route.fulfill({
        status: 401,
        contentType: "text/plain",
        body: "Incorrect email or password",
      });
    }

    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({
        token: "FAKE.JWT.TOKEN",
        user: { userId: 1, role: "user", firstName: "Test", lastName: "User" },
      }),
    });
  });

  await page.goto(`${APP}#login`);
  await page.waitForSelector("#loginForm", { timeout: 15000 });
});

test("Login page loads", async ({ page }) => {
  await expect(page.locator("#loginForm")).toBeVisible();
  await expect(page.locator("#exampleInputEmail")).toBeVisible();
  await expect(page.locator("#exampleInputPassword")).toBeVisible();
});

test("Invalid login shows error banner", async ({ page }) => {
  await expect(page.locator("#loginError")).toBeHidden();

  await page.fill("#exampleInputEmail", "wrong@example.com");
  await page.fill("#exampleInputPassword", "wrongpass");

  await page.click('#loginForm button[type="submit"]');

  await expect(page.locator("#loginError")).toBeVisible();
});
