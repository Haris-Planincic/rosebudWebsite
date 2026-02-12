console.log("screeningService loaded");


const screeningService = {
    async getAllScreenings() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/screenings", {
                method: "GET"
            });
            if (!response.ok) throw new Error("Failed to fetch screenings");
            return await response.json();
        } catch (err) {
            console.error("Error fetching screenings:", err);
            return [];
        }
    },

    async createScreening(screeningData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/screenings", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(screeningData)
            });
            if (!response.ok) throw new Error("Failed to create screening");
            return await response.json();
        } catch (err) {
            console.error("Error creating screening:", err);
        }
    },

    async updateScreening(screeningId, screeningData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/screenings/${screeningId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(screeningData)
            });
            if (!response.ok) throw new Error("Failed to update screening");
            return await response.json();
        } catch (err) {
            console.error("Error updating screening:", err);
        }
    },

    async deleteScreening(screeningId) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/screenings/${screeningId}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${token}`
                }
            });
            if (!response.ok) throw new Error("Failed to delete screening");
            return await response.json();
        } catch (err) {
            console.error("Error deleting screening:", err);
        }
    },
    async createScreeningWithImage(formData) {
  try {
    const token = localStorage.getItem("jwt_token");

    const response = await fetch(
      "http://localhost/webprogramming2025-milestone2/backend/screenings/upload",
      {
        method: "POST",
        headers: {
          // IMPORTANT: do NOT set Content-Type when using FormData
          "Authorization": `Bearer ${token}`
        },
        body: formData
      }
    );

    if (!response.ok) {
      const txt = await response.text();
      throw new Error(txt || "Failed to create screening (upload)");
    }
    return await response.json();
  } catch (err) {
    console.error("Error creating screening (upload):", err);
    throw err;
  }
},

async updateScreeningWithImage(screeningId, formData) {
  try {
    const token = localStorage.getItem("jwt_token");

    const response = await fetch(
      `http://localhost/webprogramming2025-milestone2/backend/screenings/${screeningId}/upload-edit`,
      {
        method: "POST",
        headers: {
          "Authorization": `Bearer ${token}`
        },
        body: formData
      }
    );

    if (!response.ok) {
      const txt = await response.text();
      throw new Error(txt || "Failed to update screening (upload)");
    }
    return await response.json();
  } catch (err) {
    console.error("Error updating screening (upload):", err);
    throw err;
  }
},
async getMyBookings() {
  const token = localStorage.getItem("jwt_token");
  if (!token) throw new Error("Not logged in");

  const res = await fetch(`${API_BASE}/screenings/bookings/me`, {
    method: "GET",
    headers: { "Authorization": `Bearer ${token}` }
  });

  if (!res.ok) {
    const msg = await res.text();
    throw new Error(msg || "Failed to fetch my bookings");
  }

  return await res.json();
},
async searchScreenings(q) {
  try {
    const url = new URL("http://localhost/webprogramming2025-milestone2/backend/screenings");
    if (q && q.trim() !== "") url.searchParams.set("q", q.trim());



    const response = await fetch(url.toString(), {
      method: "GET"
    });

    if (!response.ok) {
      const msg = await response.text();
      throw new Error(msg || "Failed to fetch screenings");
    }

    return await response.json();
  } catch (err) {
    console.error("Error searching screenings:", err);
    return [];
  }
}





};
window.screeningService = screeningService;
console.log("✅ screeningService attached to window", window.screeningService);
