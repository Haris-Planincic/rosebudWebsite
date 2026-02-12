import { test, expect } from "@playwright/test";

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));
  await page.route("**/backend/auth/register", async (route) => {
    if (route.request().method() !== "POST") return route.fallback();
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({ ok: true, message: "Registered" }),
    });
  });

  await page.goto("#register");
  await page.waitForSelector("#registerForm", { timeout: 15000 });
});

test("Register page loads", async ({ page }) => {
  await expect(page.locator("#registerForm")).toBeVisible();
  await expect(page.locator("#exampleFirstName")).toBeVisible();
  await expect(page.locator("#exampleLastName")).toBeVisible();
  await expect(page.locator("#exampleInputEmail")).toBeVisible();
  await expect(page.locator("#exampleInputPassword")).toBeVisible();
  await expect(page.locator("#exampleRepeatPassword")).toBeVisible();
});

test("Register: password mismatch should not submit (basic)", async ({ page }) => {
  await page.fill("#exampleFirstName", "Test");
  await page.fill("#exampleLastName", "User");
  await page.fill("#exampleInputEmail", "test@example.com");
  await page.fill("#exampleInputPassword", "pass1234");
  await page.fill("#exampleRepeatPassword", "DIFFERENT");

  let alertText = null;
  page.once("dialog", async (dialog) => {
    alertText = dialog.message();
    await dialog.dismiss();
  });

  await page.click('#registerForm button[type="submit"]');

  await page.waitForTimeout(300);

  await expect(page).toHaveURL(/#register$/);
});

test("Register: successful submission calls backend and then goes to login (or stays but succeeds)", async ({ page }) => {
  const registerResp = page.waitForResponse((r) =>
    r.url().includes("/backend/auth/register") && r.request().method() === "POST"
  );

  await page.fill("#exampleFirstName", "Test");
  await page.fill("#exampleLastName", "User");
  await page.fill("#exampleInputEmail", "good@example.com");
  await page.fill("#exampleInputPassword", "pass1234");
  await page.fill("#exampleRepeatPassword", "pass1234");

  await page.click('#registerForm button[type="submit"]');
  await registerResp;
  await page.waitForTimeout(300);

  const url = page.url();

  if (url.endsWith("#login")) {
    await expect(page.locator("#loginForm")).toBeVisible();
  } else {
    await expect(page.locator('a[href="#login"]')).toBeVisible();
  }
});
