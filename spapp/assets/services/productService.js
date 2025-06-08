console.log("productService loaded");

const productService = {
    async getAllProducts() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/products", {
                method: "GET"
            });
            if (!response.ok) throw new Error("Failed to fetch products");
            return await response.json();
        } catch (err) {
            console.error("Error fetching products:", err);
            return [];
        }
    },

    async createProduct(productData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/products", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(productData)
            });
            if (!response.ok) throw new Error("Failed to create product");
            return await response.json();
        } catch (err) {
            console.error("Error creating product:", err);
        }
    },

    async updateProduct(productId, productData) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/products/${productId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(productData)
            });
            if (!response.ok) throw new Error("Failed to update product");
            return await response.json();
        } catch (err) {
            console.error("Error updating product:", err);
        }
    },

    async deleteProduct(productId) {
        try {
            const token = localStorage.getItem("jwt_token");
            const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/products/${productId}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${token}`
                }
            });
            if (!response.ok) throw new Error("Failed to delete product");
            return await response.json();
        } catch (err) {
            console.error("Error deleting product:", err);
        }
    }
};
