$(document).ready(function () {

  // Set section height
  $("main#spapp > section").height($(document).height() - 60);

  // SPApp initialization
  var app = $.spapp({ pageNotFound: 'error_404' });

  // Routing
  app.route({ view: 'home', onCreate: () => $("#home"), onReady: updateNavbar });
  app.route({ view: 'locations', load: '../assets/html/locations.html', onReady: updateNavbar });
  app.route({ view: 'films', load: '../assets/html/films.html', onReady: updateNavbar });
  app.route({ view: 'products', load: '../assets/html/products.html', onReady: updateNavbar });
  app.route({ view: 'screenings', load: '../assets/html/screenings.html', onReady: updateNavbar });
  app.route({
  view: 'admin',
  load: '../assets/html/admin.html',
  onReady: () => {
    updateNavbar();

    if (typeof loadFilmsAdmin === "function") {
      loadFilmsAdmin();
    } else {
      console.warn("loadFilmsAdmin is not defined yet.");
    }

    if (typeof loadLocationsAdmin === "function") {
      loadLocationsAdmin();
    } else {
      console.warn("loadLocationsAdmin is not defined yet.");
    }
    if (typeof loadScreeningsAdmin === "function") {
      loadScreeningsAdmin();
    } else {
      console.warn("loadScreeningsAdmin is not defined yet.");
    }
    if (typeof loadProductsAdmin === "function") {
      loadProductsAdmin();
    } else {
      console.warn("loadProductsAdmin is not defined yet.");
    }
    if (typeof loadUsersAdmin === "function") {
      loadUsersAdmin();
    } else {
      console.warn("loadUsersAdmin is not defined yet.");
    }
    if (typeof loadPaymentsAdmin === "function") {
      loadPaymentsAdmin();
    } else {
      console.warn("loadPaymentsAdmin is not defined yet.");
    }
  }
});



  app.route({ view: 'login', load: '../assets/html/login.html', onReady: setupLoginForm });
  app.route({ view: 'register', load: '../assets/html/register.html', onReady: setupRegisterForm });


  app.route({
    view: 'view_3',
    onCreate: () => { $("#view_3").append("I'm the third view"); },
    onReady: updateNavbar
  });

  // Run app
  app.run();

  // Event handler for logout button
  $(document).on('click', '#logoutBtn a', function (e) {
    e.preventDefault();
    localStorage.removeItem('jwt_token');
    updateNavbar();
    window.location.hash = '#home';
  });

  // ----------------------
  // FUNCTIONS
  // ----------------------

  function updateNavbar() {
  const token = localStorage.getItem('jwt_token');
  const adminLink = $('a[href="#admin"]').closest('li');
  const logoutBtn = $('#logoutBtn');
  const loginBtn = $('a[href="#login"]').closest('li');
  const registerBtn = $('a[href="#register"]').closest('li');
  const userInfo = $('#userInfo');

  if (!adminLink.length || !logoutBtn.length || !loginBtn.length || !registerBtn.length || !userInfo.length) return;

  if (token) {
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      const { role, firstName, lastName } = payload.user;

      logoutBtn.show();
      loginBtn.hide();
      registerBtn.hide();
      userInfo.show().find('span').text(`Logged in as ${firstName} ${lastName}`);

      if (role === 'admin') {
        adminLink.show();
      } else {
        adminLink.hide();
      }
    } catch (e) {
      console.error("Invalid token", e);
      localStorage.removeItem('jwt_token');
    }
  } else {
    logoutBtn.hide();
    loginBtn.show();
    registerBtn.show();
    adminLink.hide();
    userInfo.hide().find('span').text('');
  }
}


  async function loginUser(email, password) {
  const errorDiv = $('#loginError');
  errorDiv.hide();

  try {
    const response = await fetch('http://localhost/webprogramming2025-milestone1/backend/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });

    if (response.ok) {
      const data = await response.json();
      localStorage.setItem('jwt_token', data.token);
      updateNavbar();
      setTimeout(() => {
        window.location.hash = '#home';
      }, 100);
    } else {
      // Try to parse error message if it's JSON
      let errorText = "Incorrect email or password.";
      try {
        const errorData = await response.json();
        if (errorData && errorData.message) {
          errorText = errorData.message;
        }
      } catch (e) {
        // It's not JSON, just use generic message
      }
      errorDiv.text(errorText).fadeIn();
      setTimeout(() => errorDiv.fadeOut(), 3000);
    }
  } catch (err) {
    console.error("Unexpected error during login:", err);
    $('#loginError').text("An unexpected error occurred.").fadeIn();
  }
}



  function setupLoginForm() {
    console.log("Register form loaded");
    updateNavbar(); // make sure navbar updates even on login page
    $('#loginForm').on('submit', function (e) {
      e.preventDefault();
      const email = $('#exampleInputEmail').val();
      const password = $('#exampleInputPassword').val();
      loginUser(email, password);
    });
  }
  function setupRegisterForm() {
  updateNavbar();

  $('#registerForm').on('submit', async function (e) {
    e.preventDefault();

    const firstName = $('#exampleFirstName').val().trim();
    const lastName = $('#exampleLastName').val().trim();
    const email = $('#exampleInputEmail').val().trim();
    const password = $('#exampleInputPassword').val().trim();
    const repeatPassword = $('#exampleRepeatPassword').val().trim();

    // ✅ Only validate on frontend
    if (password !== repeatPassword) {
      alert("Passwords do not match!");
      return;
    }

    // ✅ Backend expects: firstName, lastName, email, password
    const response = await fetch('http://localhost/webprogramming2025-milestone1/backend/auth/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        firstName,
        lastName,
        email,
        password
      })
    });

    const result = await response.json();

    if (response.ok) {
      alert("Account created! You can now log in.");
      window.location.hash = '#login';
    } else {
      alert(result.error || "Registration failed.");
    }
  });
}




});
// Film admin functions
async function loadFilmsAdmin() {
  const tableBody = document.getElementById("filmTableBody");
  if (!tableBody) {
    console.warn("filmTableBody not found.");
    return;
  }

  const films = await filmService.getAllFilms();
  tableBody.innerHTML = "";

  films.forEach(film => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${film.filmId}</td>
      <td>${film.filmTitle}</td>
      <td>${film.yearOfRelease}</td>
      <td>${film.director}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick='editFilm(${JSON.stringify(film)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick='deleteFilm(${film.filmId})'>Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });
}

function showAddFilmForm() {
  document.getElementById("filmFormTitle").innerText = "Add New Film";
  document.getElementById("filmFormContainer").style.display = "block";
  document.getElementById("filmForm").reset();
  document.getElementById("filmId").value = "";
}

function cancelFilmForm() {
  document.getElementById("filmFormContainer").style.display = "none";
}

function editFilm(film) {
  document.getElementById("filmFormTitle").innerText = "Edit Film";
  document.getElementById("filmFormContainer").style.display = "block";

  document.getElementById("filmId").value = film.filmId;
  document.getElementById("filmTitle").value = film.filmTitle;
  document.getElementById("yearOfRelease").value = film.yearOfRelease;
  document.getElementById("director").value = film.director;
  document.getElementById("posterImage").value = film.posterImage;
}

async function deleteFilm(filmId) {
  if (confirm("Are you sure you want to delete this film?")) {
    await filmService.deleteFilm(filmId);
    loadFilmsAdmin();
  }
}
async function loadLocationsAdmin() {
  const tableBody = document.getElementById("locationTableBody");
  if (!tableBody) {
    console.warn("locationTableBody not found.");
    return;
  }

  const locations = await locationService.getAllLocations();
  tableBody.innerHTML = "";

  locations.forEach(location => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${location.locationId}</td>
      <td>${location.locationName}</td>
      <td>${location.locationAddress}</td>
      <td><img src="${location.locationImage}" alt="Image" width="60" onerror="this.src='https://via.placeholder.com/60'"></td>
      <td>
        <button class="btn btn-sm btn-warning" onclick='editLocation(${JSON.stringify(location)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick='deleteLocation(${location.locationId})'>Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  // Bind form submission handler here
  const locationForm = document.getElementById("locationForm");
  if (locationForm && !locationForm.dataset.listenerAttached) {
    locationForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const locationData = {
        locationName: document.getElementById("locationName").value,
        locationAddress: document.getElementById("locationAddress").value,
        locationImage: document.getElementById("locationImage").value
      };

      const locationId = document.getElementById("locationId").value;

      if (locationId) {
        await locationService.updateLocation(locationId, locationData);
      } else {
        await locationService.createLocation(locationData);
      }

      cancelLocationForm();
      loadLocationsAdmin();
    });

    // Prevent multiple bindings
    locationForm.dataset.listenerAttached = "true";
  }
}


function showAddLocationForm() {
  document.getElementById("locationFormTitle").innerText = "Add New Location";
  document.getElementById("locationFormContainer").style.display = "block";
  document.getElementById("locationForm").reset();
  document.getElementById("locationId").value = "";
}

function cancelLocationForm() {
  document.getElementById("locationFormContainer").style.display = "none";
}

function editLocation(location) {
  document.getElementById("locationFormTitle").innerText = "Edit Location";
  document.getElementById("locationFormContainer").style.display = "block";

  document.getElementById("locationId").value = location.locationId;
  document.getElementById("locationName").value = location.locationName;
  document.getElementById("locationAddress").value = location.locationAddress;
  document.getElementById("locationImage").value = location.locationImage || "";
}

async function deleteLocation(locationId) {
  if (confirm("Are you sure you want to delete this location?")) {
    await locationService.deleteLocation(locationId);
    loadLocationsAdmin();
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const locationForm = document.getElementById("locationForm");
  if (locationForm) {
    locationForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const locationData = {
        locationName: document.getElementById("locationName").value,
        locationAddress: document.getElementById("locationAddress").value,
        locationImage: document.getElementById("locationImage").value
      };

      const locationId = document.getElementById("locationId").value;

      if (locationId) {
        await locationService.updateLocation(locationId, locationData);
      } else {
        await locationService.createLocation(locationData);
      }

      cancelLocationForm();
      loadLocationsAdmin();
    });
  }
});
async function loadScreeningsAdmin() {
  const tableBody = document.getElementById("screeningTableBody");
  if (!tableBody) {
    console.warn("screeningTableBody not found.");
    return;
  }

  const screenings = await screeningService.getAllScreenings();
  tableBody.innerHTML = "";

  screenings.forEach(screening => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${screening.screeningId}</td>
      <td>${screening.screeningTitle}</td>
      <td>${screening.yearOfRelease}</td>
      <td>${new Date(screening.screeningTime).toLocaleString()}</td>
      <td><img src="${screening.screeningImage}" alt="Image" width="60" onerror="this.src='https://via.placeholder.com/60'"></td>
      <td>
        <button class="btn btn-sm btn-warning" onclick='editScreening(${JSON.stringify(screening)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick='deleteScreening(${screening.screeningId})'>Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  const form = document.getElementById("screeningForm");
  if (form && !form.dataset.listenerAttached) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const screeningData = {
        screeningTitle: document.getElementById("screeningTitle").value,
        yearOfRelease: document.getElementById("yearOfReleaseScreening").value,
        screeningTime: document.getElementById("screeningTime").value,
        screeningImage: document.getElementById("screeningImage").value
      };

      const screeningId = document.getElementById("screeningId").value;

      if (screeningId) {
        await screeningService.updateScreening(screeningId, screeningData);
      } else {
        await screeningService.createScreening(screeningData);
      }

      cancelScreeningForm();
      loadScreeningsAdmin();
    });

    form.dataset.listenerAttached = "true";
  }
}

function showAddScreeningForm() {
  document.getElementById("screeningFormTitle").innerText = "Add New Screening";
  document.getElementById("screeningFormContainer").style.display = "block";
  document.getElementById("screeningForm").reset();
  document.getElementById("screeningId").value = "";
}

function cancelScreeningForm() {
  document.getElementById("screeningFormContainer").style.display = "none";
}

function editScreening(screening) {
  document.getElementById("screeningFormTitle").innerText = "Edit Screening";
  document.getElementById("screeningFormContainer").style.display = "block";

  document.getElementById("screeningId").value = screening.screeningId;
  document.getElementById("screeningTitle").value = screening.screeningTitle;
  document.getElementById("yearOfReleaseScreening").value = screening.yearOfRelease;
  document.getElementById("screeningTime").value = screening.screeningTime;
  document.getElementById("screeningImage").value = screening.screeningImage || "";
}

async function deleteScreening(screeningId) {
  if (confirm("Are you sure you want to delete this screening?")) {
    await screeningService.deleteScreening(screeningId);
    loadScreeningsAdmin();
  }
}
async function loadProductsAdmin() {
  const tableBody = document.getElementById("productTableBody");
  if (!tableBody) {
    console.warn("productTableBody not found.");
    return;
  }

  const products = await productService.getAllProducts();
  tableBody.innerHTML = "";

  products.forEach(product => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${product.productId}</td>
      <td>${product.productName}</td>
      <td>$${parseFloat(product.productPrice).toFixed(2)}</td>
      <td>${product.productDescription}</td>
      <td><img src="${product.productImage}" alt="Image" width="60" onerror="this.src='https://via.placeholder.com/60'"></td>
      <td>
        <button class="btn btn-sm btn-warning" onclick='editProduct(${JSON.stringify(product)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick='deleteProduct(${product.productId})'>Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  const form = document.getElementById("productForm");
  if (form && !form.dataset.listenerAttached) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const productData = {
        productName: document.getElementById("productName").value,
        productPrice: parseFloat(document.getElementById("productPrice").value),
        productDescription: document.getElementById("productDescription").value,
        productImage: document.getElementById("productImage").value
      };

      const productId = document.getElementById("productId").value;

      if (productId) {
        await productService.updateProduct(productId, productData);
      } else {
        await productService.createProduct(productData);
      }

      cancelProductForm();
      loadProductsAdmin();
    });

    form.dataset.listenerAttached = "true";
  }
}

function showAddProductForm() {
  document.getElementById("productFormTitle").innerText = "Add New Product";
  document.getElementById("productFormContainer").style.display = "block";
  document.getElementById("productForm").reset();
  document.getElementById("productId").value = "";
}

function cancelProductForm() {
  document.getElementById("productFormContainer").style.display = "none";
}

function editProduct(product) {
  document.getElementById("productFormTitle").innerText = "Edit Product";
  document.getElementById("productFormContainer").style.display = "block";

  document.getElementById("productId").value = product.productId;
  document.getElementById("productName").value = product.productName;
  document.getElementById("productPrice").value = product.productPrice;
  document.getElementById("productDescription").value = product.productDescription;
  document.getElementById("productImage").value = product.productImage || "";
}

async function deleteProduct(productId) {
  if (confirm("Are you sure you want to delete this product?")) {
    await productService.deleteProduct(productId);
    loadProductsAdmin();
  }
}
// Admin user management
async function loadUsersAdmin() {
  const tableBody = document.getElementById("userTableBody");
  if (!tableBody) {
    console.warn("userTableBody not found.");
    return;
  }

  const users = await userService.getAllUsers();
  tableBody.innerHTML = "";

  users.forEach(user => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${user.userId}</td>
      <td>${user.firstName}</td>
      <td>${user.lastName}</td>
      <td>${user.email}</td>
      <td>${new Date(user.accountCreated).toLocaleDateString()}</td>
      <td>${user.role}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick='editUser(${JSON.stringify(user)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick='deleteUser(${user.userId})'>Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });

  const form = document.getElementById("userForm");
  if (form && !form.dataset.listenerAttached) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const userData = {
        firstName: document.getElementById("userFirstName").value,
        lastName: document.getElementById("userLastName").value,
        email: document.getElementById("userEmail").value,
        password: document.getElementById("userPassword").value,
        role: document.getElementById("userRole").value
      };

      const userId = document.getElementById("userId").value;

      if (userId) {
        await userService.updateUser(userId, userData);
      } else {
        await userService.createUser(userData);
      }

      cancelUserForm();
      loadUsersAdmin();
    });
    form.dataset.listenerAttached = "true";
  }
}

function showAddUserForm() {
  document.getElementById("userFormTitle").innerText = "Add New User";
  document.getElementById("userFormContainer").style.display = "block";
  document.getElementById("userForm").reset();
  document.getElementById("userId").value = "";
}

function cancelUserForm() {
  document.getElementById("userFormContainer").style.display = "none";
}

function editUser(user) {
  document.getElementById("userFormTitle").innerText = "Edit User";
  document.getElementById("userFormContainer").style.display = "block";

  document.getElementById("userId").value = user.userId;
  document.getElementById("userFirstName").value = user.firstName;
  document.getElementById("userLastName").value = user.lastName;
  document.getElementById("userEmail").value = user.email;
  document.getElementById("userRole").value = user.role;
  document.getElementById("userPassword").value = ""; // blank by default
}

async function deleteUser(userId) {
  if (confirm("Are you sure you want to delete this user?")) {
    await userService.deleteUser(userId);
    loadUsersAdmin();
  }
}
async function loadPaymentsAdmin() {
  const tableBody = document.getElementById("paymentTableBody");
  if (!tableBody) {
    console.warn("paymentTableBody not found.");
    return;
  }

  const payments = await paymentService.getAllPayments();
  tableBody.innerHTML = "";

  payments.forEach(payment => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${payment.paymentId}</td>
      <td>${payment.userId}</td>
      <td>$${parseFloat(payment.amount).toFixed(2)}</td>
      <td>${new Date(payment.paymentDate).toLocaleDateString()}</td>
    `;
    tableBody.appendChild(row);
  });
}

