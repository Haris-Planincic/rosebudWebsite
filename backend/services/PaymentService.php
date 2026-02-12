<?php
require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/../dao/ProductDao.php'; 
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/ScreeningDao.php';

class PaymentService {
    private $dao;
    private $productDao;

    public function __construct() {
        $this->dao = new PaymentDao();
        $this->productDao = new ProductDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
    if (!isset($data['userId'])) {
        throw new Exception("Missing userId.");
    }
    if (!isset($data['productId']) || !is_numeric($data['productId'])) {
        throw new Exception("Invalid productId.");
    }

    $userId = (int)$data['userId'];
    $productId = (int)$data['productId'];

    // We use the PaymentDao connection for the whole transaction
    $pdo = $this->dao->getConnection();

    // You need this DAO to insert into UserPurchases
    require_once __DIR__ . '/../dao/UserPurchaseDao.php';
    $purchaseDao = new UserPurchaseDao();

    try {
        $pdo->beginTransaction();

        // 1) lock product row
        $productRow = $this->productDao->getByIdForUpdate($productId);
        if (!$productRow) {
            $pdo->rollBack();
            Flight::halt(404, "Product not found.");
        }

        // 2) stop if already sold (use the real column in Products)
        if ((int)$productRow['isSold'] === 1) {
            $pdo->rollBack();
            Flight::halt(409, "Product already sold.");
        }

        // optional: prevent buying your own product
        if ((int)$productRow['sellerId'] === $userId) {
            $pdo->rollBack();
            Flight::halt(400, "You cannot buy your own product.");
        }

        // 3) insert payment (return real paymentId)
        $paymentId = $this->dao->insertPayment([
            "userId" => $userId,
            "productId" => $productId,
            "amount" => $productRow['productPrice'],
            "paymentDate" => date('Y-m-d H:i:s')
        ]);

        // 4) insert purchase record
        $purchaseId = $purchaseDao->insertPurchase([
            "userId" => $userId,
            "productId" => $productId,
            "purchaseDate" => date('Y-m-d H:i:s')
        ]);

        // 5) mark product sold
        $this->productDao->markSold($productId);

        $pdo->commit();

        return [
            "paymentId" => $paymentId,
            "purchaseId" => $purchaseId,
            "productId" => $productId,
            "amount" => $productRow['productPrice']
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}



    public function update($id, $data) {
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
    public function createStripePaymentIntent($userId, $productId) {
    $pdo = $this->dao->getConnection();

    try {
        $pdo->beginTransaction();

        // lock product row
        $productRow = $this->productDao->getByIdForUpdate($productId);
        if (!$productRow) {
            $pdo->rollBack();
            Flight::halt(404, "Product not found");
        }

        if ((int)$productRow['isSold'] === 1) {
            $pdo->rollBack();
            Flight::halt(409, "Product already sold");
        }

        // Optional: block if another pending checkout exists
        if ($this->dao->hasActivePendingForProduct($productId)) {
            $pdo->rollBack();
            Flight::halt(409, "This product is currently in checkout. Try again in a few minutes.");
        }

        if ((int)$productRow['sellerId'] === (int)$userId) {
            $pdo->rollBack();
            Flight::halt(400, "You cannot buy your own product.");
        }

        $amountDecimal = $productRow['productPrice']; // e.g. "49.99"
        $amountCents = (int) round(((float)$amountDecimal) * 100);

        // create local pending payment row first
        $paymentId = $this->dao->insertPendingPayment([
            "userId" => $userId,
            "productId" => $productId,
            "amount" => $amountDecimal,
            "paymentDate" => date('Y-m-d H:i:s')
        ]);

        $pdo->commit();

        // create Stripe PaymentIntent OUTSIDE the DB transaction
        \Stripe\Stripe::setApiKey(Config::STRIPE_SECRET_KEY());

        $intent = \Stripe\PaymentIntent::create([
            "amount" => $amountCents,
            "currency" => "gbp", // choose your currency
            "payment_method_types" => ["card"],
            "metadata" => [
                "paymentId" => (string)$paymentId,
                "productId" => (string)$productId,
                "userId" => (string)$userId
            ]
        ]);

        // store providerRef
        $this->dao->setProviderRef($paymentId, $intent->id);

        return [
            "clientSecret" => $intent->client_secret,
            "paymentId" => $paymentId,
            "productId" => $productId,
            "amount" => $amountDecimal
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}


public function handleStripeSucceeded($intent) {
  $pdo = $this->dao->getConnection();

  // ✅ BEST: look up by paymentId in Stripe metadata (most reliable)
  $payment = null;
  $providerRef = $intent->id; // pi_...

  $metaPaymentId = 0;
  if (isset($intent->metadata) && isset($intent->metadata->paymentId)) {
    $metaPaymentId = (int)$intent->metadata->paymentId;
  }

  if ($metaPaymentId > 0) {
    $payment = $this->dao->getById($metaPaymentId);
  } else {
    // fallback: old behavior
    $payment = $this->dao->getByProviderRef($providerRef);
  }

  if (!$payment) {
    error_log("handleStripeSucceeded: payment not found. intent={$providerRef} metaPaymentId={$metaPaymentId}");
    return;
  }

  if (($payment['status'] ?? null) === 'succeeded') return;

  require_once __DIR__ . '/../dao/UserPurchaseDao.php';
  $purchaseDao = new UserPurchaseDao();

  require_once __DIR__ . '/../dao/ScreeningBookingDao.php';
  $bookingDao = new ScreeningBookingDao();

  // Only needed for email data lookup
  require_once __DIR__ . '/../dao/UserDao.php';
  require_once __DIR__ . '/../dao/ScreeningDao.php';
  $userDao = new UserDao();
  $screeningDao = new ScreeningDao();

  try {
    $pdo->beginTransaction();

    // ✅ PRODUCT FLOW
    if (!empty($payment['productId'])) {
      $productId = (int)$payment['productId'];

      $productRow = $this->productDao->getByIdForUpdate($productId);
      if ($productRow && (int)$productRow['isSold'] === 0) {
        $purchaseDao->insertPurchase([
          "userId" => (int)$payment['userId'],
          "productId" => $productId,
          "purchaseDate" => date('Y-m-d H:i:s')
        ]);
        $this->productDao->markSold($productId);
      }

      // ✅ update status (works whether we found by metadata or by providerRef)
      if ($metaPaymentId > 0) $this->dao->update((int)$payment['paymentId'], ["status" => "succeeded"]);
      else $this->dao->setStatusByProviderRef($providerRef, 'succeeded');

      $pdo->commit();
      return;
    }

    // ✅ SCREENING BOOKING FLOW
    if (!empty($payment['bookingId'])) {
      $bookingId = (int)$payment['bookingId'];

      $emailData = null;

      $bookingRow = $bookingDao->getByIdForUpdate($bookingId);
      if ($bookingRow && ($bookingRow['status'] ?? '') !== 'paid') {
        $bookingDao->markPaid($bookingId);
      }

      if ($bookingRow) {
        $user = $userDao->getById((int)$payment['userId']);
        $screening = $screeningDao->getById((int)$bookingRow['screeningId']);

        if ($user && $screening) {
          $emailData = [
            "toEmail" => $user['email'],
            "toName" => $user['firstName'] . ' ' . $user['lastName'],
            "booking" => $bookingRow,
            "screening" => $screening,
            "amount" => $payment['amount'] ?? null
          ];
        }
      }

      // ✅ update payment status
      if ($metaPaymentId > 0) $this->dao->update((int)$payment['paymentId'], ["status" => "succeeded"]);
      else $this->dao->setStatusByProviderRef($providerRef, 'succeeded');

      $pdo->commit();

      // Send email AFTER commit (good practice)
      if ($emailData) {
        try {
          Flight::mailerService()->sendBookingConfirmation(
            $emailData["toEmail"],
            $emailData["toName"],
            $emailData["booking"],
            $emailData["screening"],
            $emailData["amount"]
          );
        } catch (Exception $e) {
          error_log("Email send failed: " . $e->getMessage());
        }
      }

      return;
    }


    if ($metaPaymentId > 0) $this->dao->update((int)$payment['paymentId'], ["status" => "succeeded"]);
    else $this->dao->setStatusByProviderRef($providerRef, 'succeeded');

    $pdo->commit();
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("handleStripeSucceeded exception: " . $e->getMessage());
  }
}




public function handleStripeFailed($intent) {
    $providerRef = $intent->id;
    $payment = $this->dao->getByProviderRef($providerRef);
    if (!$payment) return;

    if ($payment['status'] !== 'pending') return;
    $this->dao->setStatusByProviderRef($providerRef, 'failed');
}





public function createStripeScreeningIntent($userId, $screeningId) {
  $pdo = $this->dao->getConnection();

  require_once __DIR__ . '/../dao/ScreeningDao.php';
  $screeningDao = new ScreeningDao();

  require_once __DIR__ . '/../dao/ScreeningBookingDao.php';
  $bookingDao = new ScreeningBookingDao();

  try {
    $pdo->beginTransaction();

    // 1) Lock screening row (prevents overselling)
    $screeningRow = $screeningDao->getCapacityForUpdate((int)$screeningId);
    if (!$screeningRow) {
      $pdo->rollBack();
      Flight::halt(404, "Screening not found");
    }

    $capacity = (int)$screeningRow['capacity'];
    if ($capacity <= 0) {
      $pdo->rollBack();
      Flight::halt(409, "This screening is not available for booking.");
    }

    // 2) Capacity check (count only PAID bookings)
    $paidCount = $bookingDao->countPaidBookingsForScreening((int)$screeningId);
    if ($paidCount >= $capacity) {
      $pdo->rollBack();
      Flight::halt(409, "This screening is fully booked.");
    }

    // 3) Prevent duplicate paid booking + pending spam (uses your ScreeningBookingService rules)
    $booking = Flight::screeningBookingService()->createPending((int)$userId, (int)$screeningId);

    if (!is_array($booking) || !isset($booking['bookingId'])) {
      $pdo->rollBack();
      Flight::halt(500, "Failed to create booking.");
    }
    $bookingId = (int)$booking['bookingId'];

    // 4) Ticket price (set your own logic)
    $amountDecimal = 5.00;
    $amountCents = (int) round(((float)$amountDecimal) * 100);

    // 5) Create pending payment linked to booking
    $paymentId = $this->dao->insertPendingBookingPayment([
      "userId" => (int)$userId,
      "bookingId" => (int)$bookingId,
      "amount" => $amountDecimal,
      "paymentDate" => date('Y-m-d H:i:s')
    ]);

    $pdo->commit();

    // 6) Create Stripe PaymentIntent OUTSIDE transaction
    \Stripe\Stripe::setApiKey(Config::STRIPE_SECRET_KEY());

    $intent = \Stripe\PaymentIntent::create([
      "amount" => $amountCents,
      "currency" => "gbp",
      "payment_method_types" => ["card"],
      "metadata" => [
        "paymentId" => (string)$paymentId,
        "bookingId" => (string)$bookingId,
        "screeningId" => (string)$screeningId,
        "userId" => (string)$userId,
        "type" => "screening"
      ]
    ]);

    // 7) Store providerRef so webhook can update Payments by intent id
    $this->dao->setProviderRef($paymentId, $intent->id);

    return [
      "clientSecret" => $intent->client_secret,
      "paymentId" => (int)$paymentId,
      "bookingId" => (int)$bookingId,
      "screeningId" => (int)$screeningId,
      "amount" => $amountDecimal,
      "capacity" => $capacity,
      "paidBookings" => $paidCount
    ];

  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}



}
?>
