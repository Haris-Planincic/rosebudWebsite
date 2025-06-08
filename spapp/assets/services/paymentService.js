console.log("paymentService loaded");

const paymentService = {
  async getAllPayments() {
    try {
      const token = localStorage.getItem("jwt_token");

      const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/payments", {
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

      const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/payments", {
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
  }
};
