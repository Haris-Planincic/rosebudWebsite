console.log("locationService loaded");

const locationService = {
    async getAllLocations() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/locations", {
                method: "GET"
            });
            if (!response.ok) throw new Error("Failed to fetch locations");
            return await response.json();
        } catch (err) {
            console.error("Error fetching locations:", err);
            return [];
        }
    },

    async createLocation(locationData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/locations", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(locationData)
            });
            if (!response.ok) throw new Error("Failed to create location");
            return await response.json();
        } catch (err) {
            console.error("Error creating location:", err);
        }
    },

    async updateLocation(locationId, locationData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/locations/${locationId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(locationData)
            });
            if (!response.ok) throw new Error("Failed to update location");
            return await response.json();
        } catch (err) {
            console.error("Error updating location:", err);
        }
    },

    async deleteLocation(locationId) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/locations/${locationId}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${token}`
                }
            });
            if (!response.ok) throw new Error("Failed to delete location");
            return await response.json();
        } catch (err) {
            console.error("Error deleting location:", err);
        }
    }
};
