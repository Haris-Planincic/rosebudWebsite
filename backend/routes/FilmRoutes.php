<?php
require_once __DIR__ . "/../data/roles.php";
/**
 * @OA\Get(
 *     path="/films",
 *     tags={"films"},
 *     summary="Get all films",
 *     @OA\Response(
 *         response=200,
 *         description="Array of all films in the database"
 *     )
 * )
 */
Flight::route('GET /films', function() {
    Flight::json(Flight::filmService()->getAll());
});
/**
 * @OA\Get(
 *     path="/films/{filmId}",
 *     tags={"films"},
 *     summary="Get a film by ID",
 *     @OA\Parameter(
 *         name="filmId",
 *         in="path",
 *         required=true,
 *         description="ID of the film",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the film with the given ID"
 *     )
 * )
 */
Flight::route('GET /films/@filmId', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json(Flight::filmService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/films",
 *     tags={"films"},
 *     summary="Create a new film",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "director", "genre", "year"},
 *             @OA\Property(property="title", type="string", example="Inception"),
 *             @OA\Property(property="director", type="string", example="Christopher Nolan"),
 *             @OA\Property(property="genre", type="string", example="Sci-Fi"),
 *             @OA\Property(property="year", type="integer", example=2010)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Film created successfully"
 *     )
 * )
 */
Flight::route('POST /films', function() {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN); 

    $data = Flight::request()->data->getData();
    Flight::json(Flight::filmService()->create($data));
});

/**
 * @OA\Put(
 *     path="/films/{filmId}",
 *     tags={"films"},
 *     summary="Update a film by ID",
 *     @OA\Parameter(
 *         name="filmId",
 *         in="path",
 *         required=true,
 *         description="ID of the film to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "director", "genre", "year"},
 *             @OA\Property(property="title", type="string", example="The Dark Knight"),
 *             @OA\Property(property="director", type="string", example="Christopher Nolan"),
 *             @OA\Property(property="genre", type="string", example="Action"),
 *             @OA\Property(property="year", type="integer", example=2008)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Film updated successfully"
 *     )
 * )
 */
Flight::route('PUT /films/@filmId', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::filmService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/films/{filmId}",
 *     tags={"films"},
 *     summary="Delete a film by ID",
 *     @OA\Parameter(
 *         name="filmId",
 *         in="path",
 *         required=true,
 *         description="ID of the film to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Film deleted successfully"
 *     )
 * )
 */
Flight::route('DELETE /films/@filmId', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::filmService()->delete($id));
});
