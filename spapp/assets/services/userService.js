console.log("userService loaded");

const userService = {
  async getAllUsers() {
    try {
      const token = localStorage.getItem("jwt_token");
      const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/users", {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      if (!response.ok) {
        const message = await response.text();
        throw new Error(`Failed to fetch users: ${message}`);
      }
      return await response.json();
    } catch (err) {
      console.error("Error fetching users:", err);
      return [];
    }
  },

  async createUser(userData) {
    try {
      const token = localStorage.getItem("jwt_token");
      const response = await fetch("http://localhost/webprogramming2025-milestone1/backend/users", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify(userData)
      });

      if (!response.ok) {
        const error = await response.text();
        throw new Error(error || "Failed to create user");
      }

      return await response.json();
    } catch (err) {
      console.error("Error creating user:", err);
    }
  },

  async updateUser(userId, userData) {
    try {
      const token = localStorage.getItem("jwt_token");
      const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/users/${userId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify(userData)
      });

      if (!response.ok) {
        const message = await response.text();
        throw new Error(`Failed to update user: ${message}`);
      }
      return await response.json();
    } catch (err) {
      console.error("Error updating user:", err);
    }
  },

  async deleteUser(userId) {
    try {
      const token = localStorage.getItem("jwt_token");
      const response = await fetch(`http://localhost/webprogramming2025-milestone1/backend/users/${userId}`, {
        method: "DELETE",
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });

      if (!response.ok) {
        const message = await response.text();
        throw new Error(`Failed to delete user: ${message}`);
      }
      return await response.json();
    } catch (err) {
      console.error("Error deleting user:", err);
    }
  }
};
