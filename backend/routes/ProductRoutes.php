<?php
require_once __DIR__ . "/../data/roles.php";
/**
 * @OA\Get(
 *     path="/products",
 *     tags={"products"},
 *     summary="Get all products",
 *     @OA\Response(
 *         response=200,
 *         description="List of all products"
 *     )
 * )
 */
Flight::route('GET /products', function() {
    Flight::json(Flight::productService()->getAll());
});
/**
 * @OA\Get(
 *     path="/products/{productId}",
 *     tags={"products"},
 *     summary="Get product by ID",
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         required=true,
 *         description="ID of the product",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Details of a specific product"
 *     )
 * )
 */
Flight::route('GET /products/@id', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json(Flight::productService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/products",
 *     tags={"products"},
 *     summary="Add a new product",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "price", "description"},
 *             @OA\Property(property="name", type="string", example="Product Name"),
 *             @OA\Property(property="price", type="number", format="float", example=19.99),
 *             @OA\Property(property="description", type="string", example="Product description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New product created"
 *     )
 * )
 */
Flight::route('POST /products', function() {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::productService()->create($data));
});
/**
 * @OA\Put(
 *     path="/products/{productId}",
 *     tags={"products"},
 *     summary="Update an existing product",
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         required=true,
 *         description="ID of the product",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Product Name"),
 *             @OA\Property(property="price", type="number", format="float", example=29.99),
 *             @OA\Property(property="description", type="string", example="Updated product description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated"
 *     )
 * )
 */
Flight::route('PUT /products/@id', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::productService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/products/{productId}",
 *     tags={"products"},
 *     summary="Delete a product",
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         required=true,
 *         description="ID of the product",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product deleted"
 *     )
 * )
 */
Flight::route('DELETE /products/@id', function($id) {
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::productService()->delete($id));
});