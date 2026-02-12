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
require_once __DIR__ . '/./dao/MessageDao.php';
require_once __DIR__ . '/./dao/ConversationDao.php';
require_once __DIR__ . '/./services/MessageService.php';
require_once __DIR__ . '/./services/ScreeningBookingService.php';
require_once __DIR__ . '/./services/MailerService.php';


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
Flight::register('messageService', 'MessageService');
Flight::register('screeningBookingService', 'ScreeningBookingService');
Flight::register('mailerService', 'MailerService');



// Middleware for JWT verification
Flight::route('/*', function () {
    $url = Flight::request()->url;
    $method = Flight::request()->method;
    error_log("MIDDLEWARE HIT: $method $url");
    
    // Publicly accessible routes
     if (
        // auth
        strpos($url, '/auth/login') === 0 ||
        strpos($url, '/auth/register') === 0 ||
        strpos($url, '/stripe/webhook') === 0 ||
  
        // public GETs
        ($method === 'GET' && (
            preg_match('#^/films(/\d+)?$#', $url) ||
            preg_match('#^/locations(/\d+)?$#', $url) ||
            preg_match('#^/screenings(/\d+)?(\?.*)?$#', $url) ||
            preg_match('#^/products(/\d+)?(\?.*)?$#', $url)

        ))
    ) {
        return true;
    }


    // All other routes require a valid JWT token
    Flight::auth_middleware()->verifyToken();
    return true;

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
require_once __DIR__ . '/./routes/MessageRoutes.php';
require_once __DIR__ . '/./routes/StripeRoutes.php';



// Default route
Flight::route('/', function () {
    echo 'API is running';
});

Flight::start();
