console.log("filmService loaded");

const filmService = {
    async getAllFilms() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/films", {
                method: "GET"
            });
            if (!response.ok) throw new Error("Failed to fetch films");
            return await response.json();
        } catch (err) {
            console.error("Error fetching films:", err);
            return [];
        }
    },

    async createFilm(filmData) {
        const token = localStorage.getItem("jwt_token");  
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/films", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}` 
                },
                body: JSON.stringify(filmData)
            });
            if (!response.ok) {
                const message = await response.text();
                throw new Error(`Failed to create film: ${message}`);
            }
            return await response.json();
        } catch (err) {
            console.error("Error creating film:", err);
        }
    },

    async updateFilm(filmId, filmData) {
        const token = localStorage.getItem("jwt_token");
        try {
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/films/${filmId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(filmData)
            });
            if (!response.ok) throw new Error("Failed to update film");
            return await response.json();
        } catch (err) {
            console.error("Error updating film:", err);
        }
    },

    async deleteFilm(filmId) {
        const token = localStorage.getItem("jwt_token");
        try {
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/films/${filmId}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${token}`
                }
            });
            if (!response.ok) throw new Error("Failed to delete film");
            return await response.json();
        } catch (err) {
            console.error("Error deleting film:", err);
        }
    }
};
