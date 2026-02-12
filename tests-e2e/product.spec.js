import { test, expect } from "@playwright/test";

const fakeProduct = {
  productId: 9,
  productName: "Product Nine",
  productPrice: 19.99,
  productDescription: "Nice product",
  productImage: "uploads/p9.jpg",
  sellerName: "John Seller",
  sellerId: 2,
  isSold: 0,
};

function makeFakeJwt(userId = 1, role = "user") {
  // atob-friendly base64 (not base64url)
  const header = Buffer.from(JSON.stringify({ alg: "HS256", typ: "JWT" })).toString("base64");
  const payload = Buffer.from(JSON.stringify({ user: { userId, role } })).toString("base64");
  return `${header}.${payload}.sig`;
}

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  // Mock the product endpoint your productService.getProductById hits:
  // It calls: GET .../backend/products/{id}
  await page.route("**/backend/products/*", async (route) => {
    if (route.request().method() !== "GET") return route.fallback();

    const url = route.request().url();
    const idStr = url.split("/backend/products/")[1] || "";
    const requestedId = Number(idStr.split("?")[0]);

    if (requestedId === fakeProduct.productId) {
      return route.fulfill({
        status: 200,
        contentType: "application/json",
        body: JSON.stringify(fakeProduct),
      });
    }

    // If a different id is requested, return something reasonable
    return route.fulfill({
      status: 404,
      contentType: "text/plain",
      body: "Not found",
    });
  });
});

test("Product page: no selected_product_id shows fallback UI", async ({ page }) => {
  await page.goto("#home"); // ensure origin is set
  await page.evaluate(() => {
    localStorage.removeItem("selected_product_id");
    localStorage.removeItem("jwt_token");
  });

  await page.goto("#product");

  await expect(page.locator("#productName")).toHaveText("No product selected");
  await expect(page.locator('#purchaseArea a[href="#products"]')).toBeVisible();
});

test("Product page: buyer not logged in sees 'Login to purchase'", async ({ page }) => {
  await page.goto("#home");
  await page.evaluate((id) => {
    localStorage.setItem("selected_product_id", String(id));
    localStorage.removeItem("jwt_token");
  }, fakeProduct.productId);

  await page.goto("#product");

  // Basic details render
  await expect(page.locator("#productName")).toHaveText(fakeProduct.productName);
  await expect(page.locator("#productPrice")).toContainText("$");
  await expect(page.locator("#productDescription")).toHaveText(fakeProduct.productDescription);
  await expect(page.locator("#productSeller")).toHaveText(fakeProduct.sellerName);

  // Buyer not logged in -> login link
  await expect(page.locator('#purchaseArea a[href="#login"]')).toBeVisible();

  // Owner buttons should NOT exist
  await expect(page.locator("#editListingBtn")).toHaveCount(0);
  await expect(page.locator("#deleteListingBtn")).toHaveCount(0);
});

test("Product page: owner sees edit/delete buttons", async ({ page }) => {
  await page.goto("#home");

  // Set selected id + token for owner (sellerId=2)
  const token = makeFakeJwt(2, "user");
  await page.evaluate(
    ({ id, t }) => {
      localStorage.setItem("selected_product_id", String(id));
      localStorage.setItem("jwt_token", t);
    },
    { id: fakeProduct.productId, t: token }
  );

  await page.goto("#product");

  await expect(page.locator("#productName")).toHaveText(fakeProduct.productName);

  // Owner view
  await expect(page.locator("#purchaseArea")).toContainText("This is your listing.");
  await expect(page.locator("#editListingBtn")).toBeVisible();
  await expect(page.locator("#deleteListingBtn")).toBeVisible();

  // Buyer checkout button should not exist for owner
  await expect(page.locator("#checkoutBtn")).toHaveCount(0);
});

test("Product page: sold product shows Sold button", async ({ page }) => {
  // Override mock for sold case
  const soldProduct = { ...fakeProduct, isSold: 1 };

  await page.route("**/backend/products/*", async (route) => {
    if (route.request().method() !== "GET") return route.fallback();
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify(soldProduct),
    });
  });

  await page.goto("#home");
  await page.evaluate((id) => {
    localStorage.setItem("selected_product_id", String(id));
    localStorage.removeItem("jwt_token");
  }, soldProduct.productId);

  await page.goto("#product");

  await expect(page.locator("#purchaseArea button")).toHaveText("Sold");
  await expect(page.locator("#purchaseArea button")).toBeDisabled();
});
