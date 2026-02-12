import { test, expect } from "@playwright/test";

const APP = "http://localhost/webprogramming2025-milestone2/spapp/index.html";

// Create a JWT-looking token that your getLoggedInUserIdFromToken() can decode:
// payload JSON must include: { user: { userId: ... } }
function makeFakeJwt(userId = 1) {
  const header = Buffer.from(JSON.stringify({ alg: "HS256", typ: "JWT" })).toString("base64");
  const payload = Buffer.from(JSON.stringify({ user: { userId } })).toString("base64");
  return `${header}.${payload}.sig`;
}


test.beforeEach(async ({ page }) => {
  page.on("console", (msg) => console.log("BROWSER:", msg.type(), msg.text()));

  
  await page.route("**/backend/users/search**", async (route) => {
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify([
        { userId: 2, firstName: "Jane", lastName: "Doe", email: "jane@example.com" },
      ]),
    });
  });

  // GET /messages/conversations
 
  await page.route("**/backend/messages/conversations", async (route) => {
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify([
        {
          conversationId: 123,
          otherUserId: 2,
          otherFirstName: "Jane",
          otherLastName: "Doe",
          lastMessageBody: "Hey!",
          unreadCount: 0,
        },
      ]),
    });
  });

  // POST /messages/direct/:otherUserId  -> { conversationId }
  await page.route(/.*\/backend\/messages\/direct\/\d+$/, async (route) => {
    if (route.request().method() !== "POST") return route.fallback();
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({ conversationId: 123 }),
    });
  });

  // POST /messages/:conversationId/read
  await page.route(/.*\/backend\/messages\/\d+\/read$/, async (route) => {
    if (route.request().method() !== "POST") return route.fallback();
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({ ok: true }),
    });
  });

  // GET /messages/:conversationId  -> list of messages
  await page.route(/.*\/backend\/messages\/\d+$/, async (route) => {
    if (route.request().method() !== "GET") return route.fallback();
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify([
        { senderId: 2, firstName: "Jane", lastName: "Doe", body: "Hey!" },
      ]),
    });
  });

  // POST /messages/:conversationId (send message)
  await page.route(/.*\/backend\/messages\/\d+$/, async (route) => {
    if (route.request().method() !== "POST") return route.fallback();

    // Return anything; your UI will call refreshMessages() after send
    return route.fulfill({
      status: 200,
      contentType: "application/json",
      body: JSON.stringify({ ok: true }),
    });
  });

  await page.goto(`${APP}#messages`);
  await page.waitForSelector("h2", { timeout: 15000 });
});

test("Messages: logged out shows auth warning and hides layout", async ({ page }) => {
  await page.evaluate(() => localStorage.removeItem("jwt_token"));
  await page.goto(`${APP}#messages`);

  await expect(page.locator("#messagesAuthWarning")).toBeVisible();
  await expect(page.locator("#messagesLayout")).toBeHidden();
});



