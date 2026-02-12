import { test, expect } from "@playwright/test";

const fakeProducts = [
  { productId: 1, productName: "Product One", productPrice: 10, productImage: "uploads/p1.jpg", isSold: 0 },
  { productId: 2, productName: "Another Item", productPrice: 25.5, productImage: "uploads/p2.jpg", isSold: 0 },
];

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  await page.route("**/backend/products", async (route) => {
    if (route.request().method() === "GET") {
      return route.fulfill({
        status: 200,
        contentType: "application/json",
        body: JSON.stringify(fakeProducts),
      });
    }
    return route.fallback();
  });

  await page.goto("http://localhost/webprogramming2025-milestone2/spapp/index.html#products");
  await page.waitForSelector("#products #product-list", { timeout: 15000 });
});


test("Products page loads and renders product cards", async ({ page }) => {
  const cards = page.locator("#products .product");
  await expect(cards).toHaveCount(2);

  await expect(page.locator("#products h5.fw-bolder", { hasText: "Product One" })).toBeVisible();
  await expect(page.locator("#products h5.fw-bolder", { hasText: "Another Item" })).toBeVisible();
});

test("Search filters products (keyup)", async ({ page }) => {
  const search = page.locator("#products #searchBar");
  await expect(search).toBeVisible();

  await search.click();
  await search.type("another"); 
  await page.waitForTimeout(100);


  const productOne = page.locator("#products .product", { hasText: "Product One" });
  const another = page.locator("#products .product", { hasText: "Another Item" });

  await expect(another).toBeVisible();
  await expect(productOne).toBeHidden();
});

test("Clicking View stores selected_product_id and navigates to #product", async ({ page }) => {
  await page.locator("#products .view-btn").first().click();
  await expect(page).toHaveURL(/#product$/);

  const stored = await page.evaluate(() => localStorage.getItem("selected_product_id"));
  expect(stored).toBe("1");
});
