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
    Flight::json(Flight::screeningService()->getAll());
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
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
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