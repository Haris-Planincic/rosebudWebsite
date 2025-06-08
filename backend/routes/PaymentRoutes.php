<?php
require_once __DIR__ . "/../data/roles.php";
/**
 * @OA\Get(
 *     path="/payments",
 *     tags={"payments"},
 *     summary="Get all payments",
 *     @OA\Response(
 *         response=200,
 *         description="List of all payments"
 *     )
 * )
 */
Flight::route('GET /payments', function() {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::paymentService()->getAll());
});
/**
 * @OA\Get(
 *     path="/payments/{paymentId}",
 *     tags={"payments"},
 *     summary="Get payment by ID",
 *     @OA\Parameter(
 *         name="paymentId",
 *         in="path",
 *         required=true,
 *         description="ID of the payment",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment details"
 *     )
 * )
 */
Flight::route('GET /payments/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::paymentService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/payments",
 *     tags={"payments"},
 *     summary="Create a payment for a product",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"productId"},
 *             @OA\Property(property="productId", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment recorded"
 *     )
 * )
 */
Flight::route('POST /payments', function() {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $payload = Flight::get('user'); 
    $userId = $payload->userId;

    $data = Flight::request()->data->getData();

    if (!isset($data['productId'])) {
        Flight::halt(400, "Missing productId.");
    }

    $data['userId'] = $userId;

    Flight::json(Flight::paymentService()->create($data));
});

/**
 * @OA\Put(
 *     path="/payments/{paymentId}",
 *     tags={"payments"},
 *     summary="Update a payment",
 *     @OA\Parameter(
 *         name="paymentId",
 *         in="path",
 *         required=true,
 *         description="ID of the payment",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="amount", type="number", format="float", example=89.99),
 *             @OA\Property(property="method", type="string", example="paypal")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment updated"
 *     )
 * )
 */
Flight::route('PUT /payments/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::paymentService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/payments/{paymentId}",
 *     tags={"payments"},
 *     summary="Delete a payment",
 *     @OA\Parameter(
 *         name="paymentId",
 *         in="path",
 *         required=true,
 *         description="ID of the payment",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment deleted"
 *     )
 * )
 */
Flight::route('DELETE /payments/@id', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::paymentService()->delete($id));
});