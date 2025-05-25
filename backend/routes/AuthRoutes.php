<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
Flight::group('/auth', function() {
   /**
    * @OA\Post(
    *     path="/auth/register",
    *     summary="Register new user.",
    *     description="Add a new user to the database.",
    *     tags={"auth"},
    *     security={
    *         {"ApiKey": {}}
    *     },
    *     @OA\RequestBody(
    *         description="Add new user",
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 required={"password", "email"},
    *                 @OA\Property(
    *                     property="password",
    *                     type="string",
    *                     example="some_password",
    *                     description="User password"
    *                 ),
    *                 @OA\Property(
    *                     property="email",
    *                     type="string",
    *                     example="demo@gmail.com",
    *                     description="User email"
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User has been added."
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error."
    *     )
    * )
    */
   Flight::route("POST /register", function () {
       $data = Flight::request()->data->getData();


       $response = Flight::auth_service()->register($data);
  
       if ($response['success']) {
    Flight::json([
        'message' => 'User logged in successfully',
        'data' => $response['data']
    ]);
} else {
    Flight::json(['error' => $response['error']], 500);
}

   });
   /**
    * @OA\Post(
    *      path="/auth/login",
    *      tags={"auth"},
    *      summary="Login to system using email and password",
    *      @OA\Response(
    *           response=200,
    *           description="Student data and JWT"
    *      ),
    *      @OA\RequestBody(
    *          description="Credentials",
    *          @OA\JsonContent(
    *              required={"email","password"},
    *              @OA\Property(property="email", type="string", example="demo@gmail.com", description="Student email address"),
    *              @OA\Property(property="password", type="string", example="some_password", description="Student password")
    *          )
    *      )
    * )
    */
   Flight::route('POST /login', function() {
    // Get JSON data sent to this route
    $data = Flight::request()->data->getData();

    error_log("🟡 /login called. Data: " . json_encode($data));

    // Check if any data was sent
    if (empty($data)) {
        Flight::json(['error' => 'No input data received.'], 400);
        return;
    }

    // For demo, let's say you validate the user here (this is just a placeholder)
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if ($username !== 'testuser' || $password !== 'testpass') {
        Flight::json(['error' => 'Invalid username or password'], 401);
        return;
    }

    // Generate a JWT token (replace Config::JWT_SECRET() with your actual secret)
    $payload = [
        'user' => [
            'username' => $username,
            'id' => 1  // example user ID
        ],
        'iat' => time(),
        'exp' => time() + 3600 // token valid for 1 hour
    ];

    $jwtToken = Firebase\JWT\JWT::encode($payload, Config::JWT_SECRET(), 'HS256');

    error_log("Generated JWT token: " . $jwtToken);

    // Return the token in JSON response
    Flight::json([
        'message' => 'Login successful',
        'token' => $jwtToken
    ]);
});

});
?>
