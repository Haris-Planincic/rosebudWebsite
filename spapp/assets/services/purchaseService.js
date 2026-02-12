console.log("purchaseService loaded");

const purchaseService = {
  async getMyPurchases() {
    const token = localStorage.getItem("jwt_token");
    if (!token) throw new Error("Not logged in");

    const res = await fetch(`${API_BASE}/purchases/me`, {
      method: "GET",
      headers: { "Authorization": `Bearer ${token}` }
    });

    if (!res.ok) throw new Error(await res.text());
    return await res.json(); 
  }
};

window.purchaseService = purchaseService;
console.log("purchaseService attached to window", window.purchaseService);
