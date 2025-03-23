$(document).ready(function() {

  $("main#spapp > section").height($(document).height() - 60);

  var app = $.spapp({pageNotFound : 'error_404'});

  app.route({
    view: 'home',
    onCreate: function() { $("#home"); },
    onReady: function() { $("#home"); }
  });
  app.route({view: 'locations', load: '../assets/html/locations.html' });
  app.route({view: 'films', load: '../assets/html/films.html' });
  app.route({view: 'products', load: '../assets/html/products.html' });
  app.route({view: 'screenings', load: '../assets/html/screenings.html' });
  app.route({view: 'admin', load: '../assets/html/admin.html' });
  app.route({view: 'login', load: '../assets/html/login.html' });
  app.route({view: 'register', load: '../assets/html/register.html' });
  app.route({view: 'product1', load: '../assets/html/product1.html' });
  app.route({view: 'product2', load: '../assets/html/product2.html' });
  app.route({view: 'product3', load: '../assets/html/product3.html' });
  app.route({view: 'product4', load: '../assets/html/product4.html' });
  app.route({view: 'product5', load: '../assets/html/product5.html' });
  app.route({view: 'product6', load: '../assets/html/product6.html' });
  app.route({view: 'product7', load: '../assets/html/product7.html' });
  app.route({view: 'product8', load: '../assets/html/product8.html' });
  app.route({
    view: 'view_3', 
    onCreate: function() { $("#view_3").append("I'm the third view"); }
  });

  // run app
  app.run();

});