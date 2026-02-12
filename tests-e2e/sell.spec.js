import { test, expect } from "@playwright/test";

function makeDummyPng() {
  // A minimal PNG header; enough for file input selection in most cases
  return Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]);
}

function makeFakeJwt(userId = 1, role = "user") {
  // atob-friendly base64 (not base64url)
  const header = Buffer.from(JSON.stringify({ alg: "HS256", typ: "JWT" })).toString("base64");
  const payload = Buffer.from(JSON.stringify({ user: { userId, role } })).toString("base64");
  return `${header}.${payload}.sig`;
}

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  // ✅ single global dialog handler to avoid double-accept
  page.removeAllListeners("dialog");
  page.on("dialog", async (d) => await d.accept());

  await page.goto("#sell");
  await page.waitForSelector("#sellForm", { timeout: 15000 });
});


test("Sell: logged out submit redirects to #login", async ({ page }) => {
  await page.evaluate(() => localStorage.removeItem("jwt_token"));
  await page.goto("#sell");

  // Fill fields
  await page.fill('input[name="productName"]', "Test Product");
  await page.fill('input[name="productPrice"]', "9.99");
  await page.fill('textarea[name="productDescription"]', "Test description");

  // ✅ Required file input MUST be set or submit handler won't run
  await page.setInputFiles('input[name="productImage"]', {
    name: "test.png",
    mimeType: "image/png",
    buffer: makeDummyPng(),
  });

  await page.click('#sellForm button[type="submit"]');

  // Your handler: if no token => window.location.hash = "#login"
  await expect(page).toHaveURL(/#login$/);
});

test("Sell: logged in can post product and redirects to #products", async ({ page }) => {
  // Mock ANY sell endpoint
  await page.route("**/*products/sell*", async (route) => {
    const method = route.request().method();

    if (method === "OPTIONS") return route.fulfill({ status: 204, body: "" });

    if (method === "POST") {
      return route.fulfill({
        status: 200,
        contentType: "application/json",
        body: JSON.stringify({ ok: true, productId: 777 }),
      });
    }

    return route.fallback();
  });

  // 1) Load sell route first
  await page.goto("#sell");
  await page.waitForSelector("#sellForm");

  // 2) Set token AFTER the page is loaded (this is the key fix)
  const token = makeFakeJwt(1, "user");
  await page.evaluate((t) => localStorage.setItem("jwt_token", t), token);

  // 3) Reload sell route so any auth-gating logic re-runs (safe)
  await page.goto("#sell");
  await page.waitForSelector("#sellForm");

  // Verify token exists now
  const storedToken = await page.evaluate(() => localStorage.getItem("jwt_token"));
  expect(storedToken).toBeTruthy();


  // Fill and attach file (required)
  await page.fill('input[name="productName"]', "Playwright Listing");
  await page.fill('input[name="productPrice"]', "12.50");
  await page.fill('textarea[name="productDescription"]', "Listed from an E2E test.");
  await page.setInputFiles('input[name="productImage"]', {
    name: "test.png",
    mimeType: "image/png",
    buffer: makeDummyPng(),
  });

  // Wait for POST request (now it should happen)
  const sellResp = page.waitForResponse((r) =>
    r.request().method() === "POST" && r.url().includes("products/sell")
  );

  await page.click('#sellForm button[type="submit"]');
  await sellResp;

  await expect(page).toHaveURL(/#products$/);
});
