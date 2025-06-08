console.log("screeningService loaded");

const screeningService = {
    async getAllScreenings() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/screenings", {
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
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/screenings", {
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
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/screenings/${screeningId}`, {
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
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/screenings/${screeningId}`, {
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
    }
};
