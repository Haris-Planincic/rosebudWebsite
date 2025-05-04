<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/./services/FilmService.php';
require_once __DIR__ . '/./services/LocationService.php';
require_once __DIR__ . '/./services/PaymentService.php';
require_once __DIR__ . '/./services/ProductService.php';
require_once __DIR__ . '/./services/ScreeningService.php';
require_once __DIR__ . '/./services/UserPurchaseService.php';
require_once __DIR__ . '/./services/UserService.php';

Flight::register('filmService', 'FilmService');
Flight::register('locationService', 'LocationService');
Flight::register('paymentService', 'PaymentService');
Flight::register('productService', 'ProductService');
Flight::register('screeningService', 'ScreeningService');
Flight::register('purchaseService', 'UserPurchaseService');
Flight::register('userService', 'UserService');

require_once __DIR__ . '/./routes/FilmRoutes.php';
require_once __DIR__ . '/./routes/LocationRoutes.php';
require_once __DIR__ . '/./routes/PaymentRoutes.php';
require_once __DIR__ . '/./routes/ProductRoutes.php';
require_once __DIR__ . '/./routes/ScreeningRoutes.php';
require_once __DIR__ . '/./routes/UserPurchaseRoutes.php';
require_once __DIR__ . '/./routes/UserRoutes.php';

Flight::route('/', function() {
    echo 'API is running';
});
Flight::start();

