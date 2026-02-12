<?php
require_once __DIR__ . "/../data/roles.php";
/**
 * @OA\Get(
 *     path="/screenings",
 *     tags={"screenings"},
 *     summary="Get all screenings",
 *     @OA\Response(
 *         response=200,
 *         description="List of all screenings"
 *     )
 * )
 */
Flight::route('GET /screenings', function() {
    $q = Flight::request()->query['q'] ?? null;
    Flight::json(Flight::screeningService()->getAll($q));
});
Flight::route('GET /screenings/bookings/me', function() {
  Flight::auth_middleware()->verifyToken();
  Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

  $user = Flight::get('user');
  $rows = Flight::screeningBookingService()->getMyPaidBookings($user->userId);

  Flight::json($rows);
});

/**
 * @OA\Get(
 *     path="/screenings/{screeningId}",
 *     tags={"screenings"},
 *     summary="Get screening by ID",
 *     @OA\Parameter(
 *         name="screeningId",
 *         in="path",
 *         required=true,
 *         description="ID of the screening",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Details of a specific screening"
 *     )
 * )
 */
Flight::route('GET /screenings/@id', function($id) {
    Flight::json(Flight::screeningService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/screenings",
 *     tags={"screenings"},
 *     summary="Add a new screening",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"screeningTitle", "yearOfRelease", "screeningTime", "screeningImage"},
 *             @OA\Property(property="screeningTitle", type="string", example="Yojimbo"),
 *             @OA\Property(property="yearOfRelease", type="integer", example=1961),
 *             @OA\Property(property="screeningTime", type="string", format="date-time", example="2025-04-17T12:30:00Z"),
 *             @OA\Property(property="screeningImage", type="string", example="/assets/images/yojimbo.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New screening created"
 *     )
 * )
 */

Flight::route('POST /screenings', function() {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::screeningService()->create($data));
});
/**
 * @OA\Put(
 *     path="/screenings/{screeningId}",
 *     tags={"screenings"},
 *     summary="Update an existing screening",
 *     @OA\Parameter(
 *         name="screeningId",
 *         in="path",
 *         required=true,
 *         description="ID of the screening",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="film_id", type="integer", example=1),
 *             @OA\Property(property="location_id", type="integer", example=2),
 *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-05-25T21:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Screening updated"
 *     )
 * )
 */
Flight::route('PUT /screenings/@id', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::screeningService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/screenings/{screeningId}",
 *     tags={"screenings"},
 *     summary="Delete a screening",
 *     @OA\Parameter(
 *         name="screeningId",
 *         in="path",
 *         required=true,
 *         description="ID of the screening",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Screening deleted"
 *     )
 * )
 */
Flight::route('DELETE /screenings/@id', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::screeningService()->delete($id));
});
Flight::route('POST /screenings/@id/checkout', function($id) {
  Flight::auth_middleware()->verifyToken(); // use your existing middleware style
  Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

  $user = Flight::get('user');
  $screening = Flight::screeningService()->getById($id);
  if (!$screening) Flight::halt(404, "Screening not found");

  // 1) Create pending booking
  $booking = Flight::screeningBookingService()->createPending($user->userId, (int)$id);

  // 2) Create Stripe Checkout Session
  // (This assumes you already have Stripe initialized similarly to your product checkout)
  $amountCents = 500; // example: 5.00 BAM/EUR/USD -> replace with your pricing logic

  $session = \Stripe\Checkout\Session::create([
    'mode' => 'payment',
    'line_items' => [[
      'price_data' => [
        'currency' => 'eur', // change to your currency
        'unit_amount' => $amountCents,
        'product_data' => [
          'name' => 'Screening: ' . $screening['screeningTitle'],
        ],
      ],
      'quantity' => 1,
    ]],
    'success_url' => Config::FRONTEND_URL() . '/#screenings?success=1&session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => Config::FRONTEND_URL() . '/#screenings?canceled=1',

    // IMPORTANT: store booking id in metadata so webhook can update DB
    'metadata' => [
      'type' => 'screening_booking',
      'bookingId' => (string)$booking['bookingId'],
      'screeningId' => (string)$id,
      'userId' => (string)$user->userId
    ],
  ]);

  // Optionally: insert a Payments row now as "pending"
  Flight::paymentService()->create([
    'userId' => $user->userId,
    'amount' => $amountCents / 100.0,
    'status' => 'pending',
    'provider' => 'stripe',
    'providerRef' => $session->id,      // session id for now
    'bookingId' => $booking['bookingId']
  ]);

  Flight::json(['url' => $session->url]);
});
/**
 * ADMIN: Create screening with image upload (multipart/form-data)
 * POST /screenings/upload
 * Fields:
 * - screeningTitle
 * - yearOfRelease
 * - screeningTime   (string "YYYY-MM-DD HH:MM:SS" or similar)
 * - capacity
 * - screeningImage  (file)
 */
Flight::route('POST /screenings/upload', function() {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);

    $title = trim($_POST['screeningTitle'] ?? '');
    $year  = $_POST['yearOfRelease'] ?? null;
    $time  = trim($_POST['screeningTime'] ?? '');
    $cap   = $_POST['capacity'] ?? null;

    if ($title === '' || $year === null || !is_numeric($year)) {
        Flight::halt(400, json_encode(["error" => "Invalid screeningTitle or yearOfRelease."]));
    }
    if ($time === '') {
        Flight::halt(400, json_encode(["error" => "screeningTime is required."]));
    }
    if ($cap === null || !is_numeric($cap) || (int)$cap < 1) {
        Flight::halt(400, json_encode(["error" => "Invalid capacity."]));
    }

    if (!isset($_FILES['screeningImage']) || $_FILES['screeningImage']['error'] !== UPLOAD_ERR_OK) {
        Flight::halt(400, json_encode(["error" => "screeningImage file is required."]));
    }

    // Save file
    $uploadDir = __DIR__ . '/../uploads/screenings';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['screeningImage']['name'];
    $tmpPath = $_FILES['screeningImage']['tmp_name'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed, true)) {
        Flight::halt(400, json_encode(["error" => "Only jpg, jpeg, png, webp allowed."]));
    }

    $filename = "s_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $destPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        Flight::halt(500, json_encode(["error" => "Failed to store uploaded image."]));
    }

    $imagePath = "uploads/screenings/" . $filename;

    $data = [
        "screeningTitle" => $title,
        "yearOfRelease"  => (int)$year,
        "screeningTime"  => $time,
        "capacity"       => (int)$cap,
        "screeningImage" => $imagePath
    ];

    $created = Flight::screeningService()->create($data);

    if (!$created) {
        // If create fails, cleanup file
        $abs = __DIR__ . '/../' . $imagePath;
        if (file_exists($abs)) @unlink($abs);

        Flight::halt(500, json_encode(["error" => "DB insert failed."]));
    }

    Flight::json(["message" => "Screening created", "screeningImage" => $imagePath]);
});


/**
 * ADMIN: Update screening (optional new image upload)
 * POST /screenings/{id}/upload-edit
 *
 * Fields (optional):
 * - screeningTitle
 * - yearOfRelease
 * - screeningTime
 * - capacity
 * - screeningImage (file optional)
 */
Flight::route('POST /screenings/@id/upload-edit', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);

    $sid = (int)$id;

    $existing = Flight::screeningService()->getById($sid);
    if (!$existing) {
        Flight::halt(404, json_encode(["error" => "Screening not found."]));
    }

    $updates = [];

    if (isset($_POST['screeningTitle'])) $updates['screeningTitle'] = trim($_POST['screeningTitle']);
    if (isset($_POST['yearOfRelease']))  $updates['yearOfRelease']  = (int)$_POST['yearOfRelease'];
    if (isset($_POST['screeningTime']))  $updates['screeningTime']  = trim($_POST['screeningTime']);
    if (isset($_POST['capacity']))       $updates['capacity']       = (int)$_POST['capacity'];

    $replaceImage = (isset($_FILES['screeningImage']) && $_FILES['screeningImage']['error'] === UPLOAD_ERR_OK);
    $newImagePath = null;

    if ($replaceImage) {
        $uploadDir = __DIR__ . '/../uploads/screenings';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $originalName = $_FILES['screeningImage']['name'];
        $tmpPath = $_FILES['screeningImage']['tmp_name'];

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed, true)) {
            Flight::halt(400, json_encode(["error" => "Only jpg, jpeg, png, webp allowed."]));
        }

        $filename = "s_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
        $destPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            Flight::halt(500, json_encode(["error" => "Failed to store uploaded image."]));
        }

        $newImagePath = "uploads/screenings/" . $filename;
        $updates['screeningImage'] = $newImagePath;
    }

    if (count($updates) === 0) {
        Flight::halt(400, json_encode(["error" => "No fields provided to update."]));
    }

    $ok = Flight::screeningService()->update($sid, $updates);
    if (!$ok) {
        // Cleanup new upload if update fails
        if ($newImagePath) {
            $abs = __DIR__ . '/../' . $newImagePath;
            if (file_exists($abs)) @unlink($abs);
        }
        Flight::halt(500, json_encode(["error" => "Update failed."]));
    }

    // Delete old image if replaced
    if ($replaceImage && !empty($existing['screeningImage'])) {
        $oldAbs = __DIR__ . '/../' . $existing['screeningImage'];
        if (file_exists($oldAbs)) @unlink($oldAbs);
    }

    Flight::json([
        "message" => "Screening updated",
        "screeningId" => $sid,
        "screeningImage" => $newImagePath ?? $existing['screeningImage']
    ]);
});
