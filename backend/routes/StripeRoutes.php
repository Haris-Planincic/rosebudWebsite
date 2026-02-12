<?php
require_once __DIR__ . "/../data/roles.php";

// Create PaymentIntent (auth required)
Flight::route('POST /stripe/create-intent', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $user = Flight::get('user');
    $userId = (int)$user->userId;

    $data = Flight::request()->data->getData();
    if (!isset($data['productId'])) Flight::halt(400, "Missing productId");

    $result = Flight::paymentService()->createStripePaymentIntent($userId, (int)$data['productId']);
    Flight::json($result);
});
Flight::route('POST /stripe/create-screening-intent', function () {
  Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

  $user = Flight::get('user');
  $userId = (int)$user->userId;

  $data = Flight::request()->data->getData();
  if (!isset($data['screeningId'])) Flight::halt(400, "Missing screeningId");

  $result = Flight::paymentService()->createStripeScreeningIntent($userId, (int)$data['screeningId']);
  Flight::json($result);
});



// Stripe webhook (NO AUTH!)
Flight::route('POST /stripe/webhook', function () {
    error_log("✅ Stripe webhook HIT " . date('c'));

    $payload = file_get_contents('php://input'); // raw body
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    try {
        \Stripe\Stripe::setApiKey(Config::STRIPE_SECRET_KEY());

        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            Config::STRIPE_WEBHOOK_SECRET()
        );
    } catch (Exception $e) {
        Flight::halt(400, "Webhook error");
    }

    if ($event->type === 'payment_intent.succeeded') {
        $intent = $event->data->object;
        Flight::paymentService()->handleStripeSucceeded($intent);
    } elseif ($event->type === 'payment_intent.payment_failed') {
        $intent = $event->data->object;
        Flight::paymentService()->handleStripeFailed($intent);
    }

    Flight::json(["received" => true]);
});
Flight::route('GET /stripe/payment-status/@paymentId', function($paymentId) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $user = Flight::get('user');
    $userId = (int)$user->userId;

    $payment = Flight::paymentService()->getById((int)$paymentId);
    if (!$payment) Flight::halt(404, "Payment not found");

    // only allow user to see their own payment (or admin)
    if ((int)$payment['userId'] !== $userId) {
        Flight::halt(403, "Forbidden");
    }

    Flight::json([
        "status" => $payment["status"] ?? null
    ]);
});

