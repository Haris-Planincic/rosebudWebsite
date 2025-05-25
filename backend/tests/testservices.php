<?php
require_once __DIR__ . '/../dao/FilmDao.php';
require_once __DIR__ . '/../dao/LocationDao.php';
require_once __DIR__ . '/../dao/ScreeningDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/../dao/UserPurchaseDao.php';

require_once __DIR__ . '/../services/FilmService.php';
require_once __DIR__ . '/../services/LocationService.php';
require_once __DIR__ . '/../services/ScreeningService.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/UserPurchaseService.php';

function testService($service, $createData, $updateData) {
    echo "Testing " . get_class($service) . "...\n";

    try {
        $created = $service->create($createData);
        $id = $created['id'] ?? null;
        echo "Create: Success (ID: $id)\n";

        $all = $service->getAll();
        echo "Get All: " . count($all) . " entries found\n";

        $fetched = $service->getById($id); 
        echo "Get By ID: " . json_encode($fetched) . "\n";

        $service->update($id, $updateData);
        echo "Update: Success\n";

        $service->delete($id);
        echo "Delete: Success\n\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

$filmService = new FilmService();
$locationService = new LocationService();
$screeningService = new ScreeningService();
$productService = new ProductService();
$userService = new UserService();
$paymentService = new PaymentService();
$userPurchaseService = new UserPurchaseService();

testService($filmService,
    ['filmTitle' => 'Test Film', 'yearOfRelease' => '2022', 'director' => 'Sf', 'posterImage' => 'test.jpg'],
    ['genre' => 'Comedy']
);

testService($locationService,
    ['locationName' => 'Mains Cinema', 'locationAddress' => '12223 Main St', 'locationImage' => 'location.jpg'],
    ['locationAddress' => '456 Side St']
);

testService($productService,
    ['productName' => 'Fed', 'productPrice' => 25.99, 'productImage' => 'hat.jpg'],
    ['productPrice' => 19.99]
);

testService($userService,
    ['firstName' => 'Johna', 'lastName' => 'Doe', 'email' => 'testing222@example.com', 'password' => 'password1232'],
    ['email' => 'updated@example.com']
);

testService($paymentService,
    ['userId' => 1, 'amount' => 45.50],
    ['amount' => 50.00]
);

testService($screeningService,
    ['filmId' => 3, 'locationId' => 1, 'screeningTime' => '2025-05-05 20:00:00', 'screeningImage' => 'screen.jpg'],
    ['screeningTime' => '2025-05-06 18:00:00']
);


try {
    echo "Testing UserPurchaseService...\n";
    
    $purchaseData = ['userId' => 1, 'productId' => 1];  
    $purchase = $userPurchaseService->create($purchaseData);
    echo "Create: Success (Purchase ID: {$purchase['purchaseId']})\n";

    $allPurchases = $userPurchaseService->getAll();
    echo "Get All: " . count($allPurchases) . " entries found\n";

    $fetched = $userPurchaseService->getById($purchase['purchaseId']);
    echo "Get By ID: " . json_encode($fetched) . "\n";

    $userPurchaseService->update($purchase['purchaseId'], ['status' => 'delivered']);
    echo "Update: Success\n";

    $userPurchaseService->delete($purchase['purchaseId']);
    echo "Delete: Success\n\n";
} catch (Exception $e) {
    echo "UserPurchase Error: " . $e->getMessage() . "\n\n";
}
?>
