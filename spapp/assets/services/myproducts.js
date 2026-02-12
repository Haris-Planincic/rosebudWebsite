console.log("myproducts.js loaded");

function resolveImage(path) {
  if (!path) return "";
  return `${API_BASE}/${path}`;
}

function formatDate(ts) {
  if (!ts) return "";
  const d = new Date(String(ts).replace(" ", "T"));
  return isNaN(d.getTime()) ? ts : d.toLocaleString();
}

async function loadMyProducts() {
  const listEl = document.getElementById("my-products-list");
  const emptyEl = document.getElementById("my-products-empty");

  if (!listEl || !emptyEl) {
    console.warn("myproducts.html not loaded yet (missing containers).");
    return;
  }

  try {
    const purchases = await purchaseService.getMyPurchases();

    if (!Array.isArray(purchases) || purchases.length === 0) {
      emptyEl.style.display = "block";
      listEl.innerHTML = "";
      return;
    }

    emptyEl.style.display = "none";

    listEl.innerHTML = purchases.map(item => {
      const img = resolveImage(item.productImage);

      return `
        <div class="col mb-5">
          <div class="card h-100 shadow-sm">
            ${img ? `<img class="card-img-top" src="${img}" alt="">` : ""}
            <div class="card-body p-4">
              <div class="text-center">
                <h5 class="fw-bolder">${item.productName ?? "Product"}</h5>
                <div class="text-muted mb-2">£${item.productPrice ?? ""}</div>
                <div class="small text-muted">Seller: ${item.sellerName ?? "-"}</div>
                <div class="small text-muted">Purchased: ${formatDate(item.purchaseDate)}</div>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join("");

  } catch (err) {
    console.error("Failed to load My Products:", err);
    emptyEl.style.display = "block";
    emptyEl.textContent = "Failed to load purchases. Please log in again.";
    listEl.innerHTML = "";
  }
}

window.loadMyProducts = loadMyProducts;
