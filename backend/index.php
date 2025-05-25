<?php

require '../vendor/autoload.php';

require_once __DIR__ . '/./services/AuthService.php';
require_once __DIR__ . '/./services/FilmService.php';
require_once __DIR__ . '/./services/LocationService.php';
require_once __DIR__ . '/./services/PaymentService.php';
require_once __DIR__ . '/./services/ProductService.php';
require_once __DIR__ . '/./services/ScreeningService.php';
require_once __DIR__ . '/./services/UserPurchaseService.php';
require_once __DIR__ . '/./services/UserService.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';  // Add middleware require here

use Flight;
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

// Register middleware service
Flight::register('auth_middleware', 'AuthMiddleware');

Flight::set('flight.log_errors', true);

// JWT Middleware to protect routes except /auth/login and /auth/register
Flight::route('/*', function () {
    $url = Flight::request()->url;

    // Skip auth check for login and register routes
    if (strpos($url, '/auth/login') === 0 || strpos($url, '/auth/register') === 0) {
        return; // allow through
    }

    // Get Authorization header (should be "Bearer <token>")
    $authHeader = Flight::request()->getHeader('Authorization');
    if (!$authHeader) {
        Flight::json(['error' => 'Missing Authorization header'], 401);
        Flight::stop();
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        Flight::json(['error' => 'Malformed Authorization header'], 401);
        Flight::stop();
    }

    try {
        // Use your AuthMiddleware to verify token instead of direct JWT decode
        if (!Flight::auth_middleware()->verifyToken($token)) {
            Flight::json(['error' => 'Invalid or expired token'], 401);
            Flight::stop();
        }

        // Optionally decode token and store user info in Flight
        $decoded = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
        Flight::set('user', $decoded->user);
        Flight::set('jwt_token', $token);
    } catch (Exception $e) {
        Flight::json(['error' => 'Token verification failed: ' . $e->getMessage()], 401);
        Flight::stop();
    }
});

// Load routes
require_once __DIR__ . '/./routes/FilmRoutes.php';
require_once __DIR__ . '/./routes/LocationRoutes.php';
require_once __DIR__ . '/./routes/PaymentRoutes.php';
require_once __DIR__ . '/./routes/ProductRoutes.php';
require_once __DIR__ . '/./routes/ScreeningRoutes.php';
require_once __DIR__ . '/./routes/UserPurchaseRoutes.php';
require_once __DIR__ . '/./routes/UserRoutes.php';
require_once __DIR__ . '/./routes/AuthRoutes.php';

Flight::route('/', function () {
    echo 'API is running';
});

Flight::start();
