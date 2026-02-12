import { test, expect } from "@playwright/test";

const APP = "http://localhost/webprogramming2025-milestone2/spapp/index.html";

const fakeScreenings = [
  {
    screeningId: 11,
    screeningTitle: "Interstellar",
    yearOfRelease: 2014,
    screeningTime: "2026-02-01 20:00",
    screeningImage: "uploads/s1.jpg",
    capacity: 50,
  },
  {
    screeningId: 22,
    screeningTitle: "Inception",
    yearOfRelease: 2010,
    screeningTime: "2026-02-02 18:30",
    screeningImage: "uploads/s2.jpg",
    capacity: 30,
  },
];

test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));
  await page.route("**/backend/screenings", async (route) => {
    if (route.request().method() === "GET") {
      return route.fulfill({
        status: 200,
        contentType: "application/json",
        body: JSON.stringify(fakeScreenings),
      });
    }
    return route.fallback();
  });

  await page.route("**/backend/stripe/payment-status/**", async (route) => {
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({ status: "pending" }),
    });
  });

  await page.addInitScript(() => {
    window.Stripe = function StripeMock() {
      return {
        elements: ({ clientSecret }) => ({
          create: () => {
            return {
              mount: () => {},
              on: (eventName, cb) => {
                if (eventName === "change") cb({ complete: true });
              },
            };
          },
        }),
        confirmPayment: async () => ({ paymentIntent: { status: "succeeded" } }),
      };
    };
  });

  await page.goto(`${APP}#screenings`);
  await page.waitForSelector("#screening-list", { timeout: 15000 });
});

test("Screenings page loads and renders screening cards", async ({ page }) => {
  const cards = page.locator("#screenings .screening");
  await expect(cards).toHaveCount(2);

  await expect(page.locator("#screenings h5.fw-bolder", { hasText: "Interstellar" })).toBeVisible();
  await expect(page.locator("#screenings h5.fw-bolder", { hasText: "Inception" })).toBeVisible();

  await expect(page.locator("#screenings .book-btn")).toHaveCount(2);
});

test("Clicking Book while logged out redirects to #login", async ({ page }) => {
  await page.evaluate(() => localStorage.removeItem("jwt_token"));

  await page.locator("#screenings .book-btn").first().click();

  await expect(page).toHaveURL(/#login$/);
});

test("Clicking Book while logged in shows checkout section and enables Pay", async ({ page }) => {
  await page.evaluate(() => localStorage.setItem("jwt_token", "TEST_TOKEN"));
  await page.evaluate(() => {
    if (!window.paymentService) window.paymentService = {};
    window.paymentService.createStripeScreeningIntent = async (screeningId) => {
      return {
        paymentId: 999,
        clientSecret: "cs_test_123",
      };
    };
  });

  const firstCard = page.locator("#screenings .card").first();
  await firstCard.locator(".book-btn").click();

  await expect(firstCard.locator(".checkoutSection")).toBeVisible();

  const payBtn = firstCard.locator(".pay-btn");
  await expect(payBtn).toBeEnabled();

  await payBtn.click();
  await expect(firstCard.locator(".paymentMessage")).toBeVisible();
});
