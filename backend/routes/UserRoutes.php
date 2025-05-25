<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php'; // ensure this is loaded

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="List of all users"
 *     )
 * )
 */
Flight::route('GET /users', function(){
    $auth = new AuthMiddleware();
    $auth->verifyToken(Flight::request()->getHeader('Authorization') ? preg_replace('/Bearer\s/', '', Flight::request()->getHeader('Authorization')) : null);
    $auth->authorizeRole('admin'); // Only admin can get all users

    Flight::json(Flight::userService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Details of a specific user"
 *     )
 * )
 */
Flight::route('GET /users/@id', function($id){
    $auth = new AuthMiddleware();
    $auth->verifyToken(Flight::request()->getHeader('Authorization') ? preg_replace('/Bearer\s/', '', Flight::request()->getHeader('Authorization')) : null);
    $user = Flight::get('user');

    // Users can get their own info, admins can get anyone's info
    if ($user->role !== 'admin' && $user->userId != $id) {
        Flight::halt(403, 'Access denied');
    }

    Flight::json(Flight::userService()->getById($id));
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"users"},
 *     summary="Add a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"firstName", "lastName", "email", "password"},
 *             @OA\Property(property="firstName", type="string", example="John"),
 *             @OA\Property(property="lastName", type="string", example="Doe"),
 *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", example="securepassword"),
 *             @OA\Property(property="role", type="string", example="user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New user created"
 *     )
 * )
 */
Flight::route('POST /users', function(){
    $auth = new AuthMiddleware();
    $auth->verifyToken(Flight::request()->getHeader('Authorization') ? preg_replace('/Bearer\s/', '', Flight::request()->getHeader('Authorization')) : null);
    $user = Flight::get('user');

    $data = Flight::request()->data->getData();

    // If role is set, only allow admin to create users with custom roles
    if (isset($data['role']) && $user->role !== 'admin') {
        unset($data['role']); // default role assignment handled in service/dao
    }

    // Validate required fields
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
        Flight::halt(400, "Missing required user fields");
    }

    Flight::json(Flight::userService()->create($data));
});

/**
 * @OA\Put(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Update an existing user",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="firstName", type="string", example="Jane"),
 *             @OA\Property(property="lastName", type="string", example="Doe"),
 *             @OA\Property(property="email", type="string", example="jane.doe@example.com"),
 *             @OA\Property(property="password", type="string", example="newsecurepassword"),
 *             @OA\Property(property="role", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated"
 *     )
 * )
 */
Flight::route('PUT /users/@id', function($id){
    $auth = new AuthMiddleware();
    $auth->verifyToken(Flight::request()->getHeader('Authorization') ? preg_replace('/Bearer\s/', '', Flight::request()->getHeader('Authorization')) : null);
    $user = Flight::get('user');

    // Only admin or the user themselves can update
    if ($user->role !== 'admin' && $user->userId != $id) {
        Flight::halt(403, 'Access denied');
    }

    $data = Flight::request()->data->getData();

    // Only admin can change role
    if (isset($data['role']) && $user->role !== 'admin') {
        unset($data['role']);
    }

    Flight::json(Flight::userService()->update($id, $data));
});

/**
 * @OA\Delete(
 *     path="/users/{userId}",
 *     tags={"users"},
 *     summary="Delete a user",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted"
 *     )
 * )
 */
Flight::route('DELETE /users/@id', function($id){
    $auth = new AuthMiddleware();
    $auth->verifyToken(Flight::request()->getHeader('Authorization') ? preg_replace('/Bearer\s/', '', Flight::request()->getHeader('Authorization')) : null);
    $user = Flight::get('user');

    // Only admins can delete users
    if ($user->role !== 'admin') {
        Flight::halt(403, 'Access denied');
    }

    Flight::json(Flight::userService()->delete($id));
});

