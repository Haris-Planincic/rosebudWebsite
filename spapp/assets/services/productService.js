console.log("productService loaded");

const productService = {
    async getAllProducts() {
        try {
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/products", {
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
            const response = await fetch("http://localhost/webprogramming2025-milestone2/backend/products", {
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
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/products/${productId}`, {
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
            const response = await fetch(`http://localhost/webprogramming2025-milestone2/backend/products/${productId}`, {
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
    },
    sellProduct: async function(formData) {
  const token = localStorage.getItem("jwt_token");
  if (!token) return null;

  return new Promise((resolve, reject) => {
    $.ajax({
      url: "http://localhost/webprogramming2025-milestone2/backend/products/sell",
      type: "POST",
      headers: {
        "Authorization": "Bearer " + token
      },
      data: formData,
      processData: false,  
      contentType: false, 
      success: function(resp) { resolve(resp); },
      error: function(xhr) {
        console.log("Sell error:", xhr.responseText);
        reject(xhr);
      }
    });
  });
},
getProductById: async function (id) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: "http://localhost/webprogramming2025-milestone2/backend/products/" + id,
      type: "GET",
      success: function (data) { resolve(data); },
      error: function (xhr) {
        console.log("Get product error:", xhr.responseText);
        reject(xhr);
      }
    });
  });
},
editMyProduct: async function(productId, formData) {
  const token = localStorage.getItem("jwt_token");
  if (!token) return null;

  return new Promise((resolve, reject) => {
    $.ajax({
      url: `http://localhost/webprogramming2025-milestone2/backend/products/sell/${productId}/edit`,
      type: "POST",
      headers: { "Authorization": "Bearer " + token },
      data: formData,
      processData: false,
      contentType: false,
      success: function(resp) { resolve(resp); },
      error: function(xhr) {
        console.log("Edit product error:", xhr.responseText);
        reject(xhr);
      }
    });
  });
},
deleteMyProduct: async function(productId) {
  const token = localStorage.getItem("jwt_token");
  if (!token) return null;

  return new Promise((resolve, reject) => {
    $.ajax({
      url: `http://localhost/webprogramming2025-milestone2/backend/products/sell/${productId}/delete`,
      type: "POST",
      headers: { "Authorization": "Bearer " + token },
      success: function(resp) { resolve(resp); },
      error: function(xhr) {
        console.log("Delete listing error:", xhr.responseText);
        reject(xhr);
      }
    });
  });
},
async searchProducts(q) {
  try {
    const url = new URL("http://localhost/webprogramming2025-milestone2/backend/products");
    if (q && q.trim() !== "") url.searchParams.set("q", q.trim());



    const response = await fetch(url.toString(), {
      method: "GET"
    });

    if (!response.ok) {
      const msg = await response.text();
      throw new Error(msg || "Failed to fetch products");
    }

    return await response.json();
  } catch (err) {
    console.error("Error searching products:", err);
    return [];
  }
}



};
window.productService = productService;
console.log("productService attached to window", window.productService);
