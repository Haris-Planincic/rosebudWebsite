<?php
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(response=200, description="List of all users")
 * )
 */
Flight::route('GET /users', function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::userService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Details of a specific user")
 * )
 */
Flight::route('GET /users/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::userService()->getById($id));
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"users"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"firstName", "lastName", "email", "password"},
 *             @OA\Property(property="firstName", type="string"),
 *             @OA\Property(property="lastName", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="role", type="string", default="user")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User successfully registered")
 * )
 */
Flight::route('POST /users', function () {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN); 

    $data = Flight::request()->data->getData();
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    Flight::json(Flight::userService()->create($data));
});
/**
 * @OA\Post(
 *     path="/auth/register",
 *     tags={"auth"},
 *     summary="Public user registration",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"firstName", "lastName", "email", "password"},
 *             @OA\Property(property="firstName", type="string"),
 *             @OA\Property(property="lastName", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User registered")
 * )
 */
Flight::route('POST /auth/register', function () {
    $data = Flight::request()->data->getData();

    $response = Flight::auth_service()->register($data);

    if ($response['success']) {
        Flight::json(['message' => 'User registered successfully', 'data' => $response['data']]);
    } else {
        Flight::halt(400, $response['error']);
    }
});

/**
 * @OA\Put(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Update a user",
 *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="firstName", type="string"),
 *             @OA\Property(property="lastName", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="role", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User updated")
 * )
 */
Flight::route('PUT /users/@id', function ($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    $data = Flight::request()->data->getData();
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::userService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Delete a user",
 *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="User deleted")
 * )
 */
Flight::route('DELETE /users/@id', function ($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(["success" => Flight::userService()->delete($id)]);
});
