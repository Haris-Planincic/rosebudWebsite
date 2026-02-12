console.log("paymentService loaded");
const API_BASE = "http://localhost/webprogramming2025-milestone2/backend";
const paymentService = {
  async getAllPayments() {
    try {
      const token = localStorage.getItem("jwt_token");

      const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/payments", {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });

      if (!response.ok) throw new Error("Failed to fetch payments");
      return await response.json();
    } catch (err) {
      console.error("Error fetching payments:", err);
      return [];
    }
  },

  async createPayment(productId) {
    try {
      const token = localStorage.getItem("jwt_token");

      const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/payments", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify({ productId })
      });

      if (!response.ok) throw new Error("Failed to create payment");
      return await response.json();
    } catch (err) {
      console.error("Error creating payment:", err);
      return null;
    }
  },
  async createStripeIntent(productId) {
    const token = localStorage.getItem("jwt_token");
    if (!token) throw new Error("Not logged in");

    const res = await fetch(`${API_BASE}/stripe/create-intent`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token}`
      },
      body: JSON.stringify({ productId: Number(productId) })
    });

    if (!res.ok) {
      const msg = await res.text();
      throw new Error(msg || "Failed to create Stripe intent");
    }

    return await res.json(); // { clientSecret, paymentId, productId, amount }
  },
  async createStripeScreeningIntent(screeningId) {
  const token = localStorage.getItem("jwt_token");
  if (!token) throw new Error("Not logged in");

  const res = await fetch(`${API_BASE}/stripe/create-screening-intent`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${token}`
    },
    body: JSON.stringify({ screeningId: Number(screeningId) })
  });

  if (!res.ok) {
    const msg = await res.text();
    throw new Error(msg || "Failed to create Stripe screening intent");
  }

  return await res.json(); // { clientSecret, paymentId, bookingId, screeningId, amount }
}

};
window.paymentService = paymentService;
console.log("✅ paymentService attached to window", window.paymentService);
