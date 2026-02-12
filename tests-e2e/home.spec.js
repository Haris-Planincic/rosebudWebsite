import { test, expect } from "@playwright/test";

const fakeProducts = Array.from({ length: 10 }).map((_, i) => ({
  productId: i + 1,
  productName: `Product ${i + 1}`,
  productPrice: (i + 1) * 10,
  productImage: `uploads/p${i + 1}.jpg`,
  isSold: 0,
}));

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  // Mock the exact endpoint your productService uses:
  // fetch("http://localhost/webprogramming2025-milestone2/backend/products")
  await page.route("**/backend/products", async (route) => {
    if (route.request().method() !== "GET") return route.fallback();

    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify(fakeProducts),
    });
  });

  // Start at home
  await page.goto("#home");

  // Wait until home injected products into #home-product-list
  await page.waitForSelector("#home #home-product-list .product", { timeout: 15000 });
});

test("Home: renders up to 8 featured products", async ({ page }) => {
  const cards = page.locator("#home #home-product-list .product");
  await expect(cards).toHaveCount(8); // default limit=8

  // Sanity check first/last visible featured items
  await expect(page.locator("#home h5.fw-bolder", { hasText: "Product 1" })).toBeVisible();
  await expect(page.locator("#home h5.fw-bolder", { hasText: "Product 8" })).toBeVisible();

  // 9 and 10 should NOT be in featured list
  await expect(page.locator("#home h5.fw-bolder", { hasText: "Product 9" })).toHaveCount(0);
});

test("Home: clicking View stores selected_product_id and goes to #product", async ({ page }) => {
  await page.evaluate(() => localStorage.removeItem("selected_product_id"));

  // Click first View button
  await page.locator("#home .view-btn").first().click();

  await expect(page).toHaveURL(/#product$/);

  const stored = await page.evaluate(() => localStorage.getItem("selected_product_id"));
  expect(stored).toBe("1");
});
