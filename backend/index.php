<?php

require '../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/./services/FilmService.php';
require_once __DIR__ . '/./services/LocationService.php';
require_once __DIR__ . '/./services/PaymentService.php';
require_once __DIR__ . '/./services/ProductService.php';
require_once __DIR__ . '/./services/ScreeningService.php';
require_once __DIR__ . '/./services/UserPurchaseService.php';
require_once __DIR__ . '/./services/UserService.php';
require_once __DIR__ . '/./services/AuthService.php';
require_once __DIR__ . '/./dao/config.php';
require_once __DIR__ . '/./middleware/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Register services
Flight::register('filmService', 'FilmService');
Flight::register('locationService', 'LocationService');
Flight::register('paymentService', 'PaymentService');
Flight::register('productService', 'ProductService');
Flight::register('screeningService', 'ScreeningService');
Flight::register('purchaseService', 'UserPurchaseService');
Flight::register('userService', 'UserService');
Flight::register('auth_service', 'AuthService');
Flight::register('auth_middleware', 'AuthMiddleware');

// Middleware for JWT verification
Flight::route('/*', function () {
    $url = Flight::request()->url;
    $method = Flight::request()->method;

    // Publicly accessible routes
    if (
        ($method === 'POST' && $url === '/users') ||  // allow registration
        strpos($url, '/auth/login') === 0 ||
        strpos($url, '/auth/register') === 0 ||

        // Public GET routes
        ($method === 'GET' && (
            preg_match('#^/films(/\d+)?$#', $url) ||
            preg_match('#^/locations(/\d+)?$#', $url) ||
            preg_match('#^/screenings(/\d+)?$#', $url) ||
            preg_match('#^/products(/\d+)?$#', $url)
        ))
    ) {
        return true;
    }

    // All other routes require a valid JWT token
    try {
        $token = Flight::request()->getHeader("Authentication");
        if (Flight::auth_middleware()->verifyToken($token)) {
            return true;
        }
    } catch (Exception $e) {
        Flight::halt(401, $e->getMessage());
    }
});

// Load route files
require_once __DIR__ . '/./routes/FilmRoutes.php';
require_once __DIR__ . '/./routes/LocationRoutes.php';
require_once __DIR__ . '/./routes/PaymentRoutes.php';
require_once __DIR__ . '/./routes/ProductRoutes.php';
require_once __DIR__ . '/./routes/ScreeningRoutes.php';
require_once __DIR__ . '/./routes/UserPurchaseRoutes.php';
require_once __DIR__ . '/./routes/UserRoutes.php';
require_once __DIR__ . '/./routes/AuthRoutes.php';

// Default route
Flight::route('/', function () {
    echo 'API is running';
});

Flight::start();
