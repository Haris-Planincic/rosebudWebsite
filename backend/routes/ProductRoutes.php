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
    $q = Flight::request()->query['q'] ?? null;  
    Flight::json(Flight::productService()->getAll($q));
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

    $me = Flight::get('user')->userId;

    $data = Flight::request()->data->getData();
    $data['sellerId'] = (int)$me;   
    $data['isSold'] = 0;       

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
/**
 * @OA\Post(
 *     path="/products/sell",
 *     tags={"products"},
 *     summary="User creates a product listing (sell)",
 *     @OA\Response(response=200, description="Product created")
 * )
 */
Flight::route('POST /products/sell', function() {

    // Require login
    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);

    // Allow normal users too
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $me = Flight::get('user')->userId;

    // IMPORTANT: must be multipart/form-data for image upload
    $name = trim($_POST['productName'] ?? '');
    $price = $_POST['productPrice'] ?? null;
    $desc = trim($_POST['productDescription'] ?? '');

    if ($name === '' || $price === null || !is_numeric($price) || (float)$price <= 0) {
        Flight::halt(400, json_encode(["error" => "Invalid productName or productPrice."]));
    }

    if (!isset($_FILES['productImage']) || $_FILES['productImage']['error'] !== UPLOAD_ERR_OK) {
        Flight::halt(400, json_encode(["error" => "productImage file is required."]));
    }

    // Save file
    $uploadDir = __DIR__ . '/../uploads/products';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['productImage']['name'];
    $tmpPath = $_FILES['productImage']['tmp_name'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed, true)) {
        Flight::halt(400, json_encode(["error" => "Only jpg, jpeg, png, webp allowed."]));
    }

    $filename = "p_{$me}_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $destPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        Flight::halt(500, json_encode(["error" => "Failed to store uploaded image."]));
    }

    // This is what gets stored in DB (relative path)
    $imagePath = "uploads/products/" . $filename;

    $data = [
        "productName" => $name,
        "productPrice" => (float)$price,
        "productDescription" => $desc,
        "productImage" => $imagePath,
        "sellerId" => (int)$me,
        // if you want to use column isSold:
        "isSold" => 0
    ];

    $ok = Flight::productService()->create($data);

    // since BaseDao::insert returns boolean
    if (!$ok) {
        Flight::halt(500, json_encode(["error" => "DB insert failed."]));
    }

    Flight::json(["message" => "Product created", "productImage" => $imagePath]);
});
/**
 * User edits their own product listing (optional new image).
 * POST /products/sell/{id}/edit
 */
Flight::route('POST /products/sell/@id/edit', function($id) {

    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $me = (int)Flight::get('user')->userId;
    $pid = (int)$id;

    $existing = Flight::productService()->getById($pid);
    if (!$existing) {
        Flight::halt(404, json_encode(["error" => "Product not found."]));
    }
    if ((int)$existing['sellerId'] !== $me) {
        Flight::halt(403, json_encode(["error" => "You can only edit your own product."]));
    }
    if (isset($existing['isSold']) && (int)$existing['isSold'] === 1) {
        Flight::halt(400, json_encode(["error" => "Sold products cannot be edited."]));
    }

    $updates = [];

    if (isset($_POST['productName'])) $updates['productName'] = trim($_POST['productName']);
    if (isset($_POST['productPrice'])) $updates['productPrice'] = $_POST['productPrice'];
    if (isset($_POST['productDescription'])) $updates['productDescription'] = trim($_POST['productDescription']);

    $newImagePath = null;
    $replaceImage = (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK);

    if ($replaceImage) {
        $uploadDir = __DIR__ . '/../uploads/products';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $originalName = $_FILES['productImage']['name'];
        $tmpPath = $_FILES['productImage']['tmp_name'];

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed, true)) {
            Flight::halt(400, json_encode(["error" => "Only jpg, jpeg, png, webp allowed."]));
        }

        $filename = "p_{$me}_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
        $destPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            Flight::halt(500, json_encode(["error" => "Failed to store uploaded image."]));
        }

        $newImagePath = "uploads/products/" . $filename;
        $updates['productImage'] = $newImagePath;
    }

    if (count($updates) === 0) {
        Flight::halt(400, json_encode(["error" => "No fields provided to update."]));
    }

    try {
        $affected = Flight::productService()->updateOwned($pid, $me, $updates);
        if ($affected === 0) {
            Flight::halt(400, json_encode(["error" => "Update failed (maybe sold, no change, or not allowed)."]));
        }
    } catch (Exception $e) {
        Flight::halt(400, json_encode(["error" => $e->getMessage()]));
    }

    if ($replaceImage && !empty($existing['productImage'])) {
        $oldAbs = __DIR__ . '/../' . $existing['productImage'];
        if (file_exists($oldAbs)) @unlink($oldAbs);
    }

    Flight::json([
        "message" => "Product updated",
        "productId" => $pid,
        "productImage" => $newImagePath ?? $existing['productImage']
    ]);
});


/**
 * Owner deletes their own product listing
 * POST /products/sell/{id}/delete
 */
Flight::route('POST /products/sell/@id/delete', function($id) {

    $authHeader = Flight::request()->getHeader("Authorization");
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;

    Flight::auth_middleware()->verifyToken($token);
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $me = (int)Flight::get('user')->userId;
    $pid = (int)$id;

    $existing = Flight::productService()->getById($pid);
    if (!$existing) {
        Flight::halt(404, json_encode(["error" => "Product not found."]));
    }
    if ((int)$existing['sellerId'] !== $me) {
        Flight::halt(403, json_encode(["error" => "You can only delete your own product."]));
    }
    if (isset($existing['isSold']) && (int)$existing['isSold'] === 1) {
        Flight::halt(400, json_encode(["error" => "Sold products cannot be deleted."]));
    }

    $ok = Flight::productService()->delete($pid);
    if (!$ok) {
        Flight::halt(500, json_encode(["error" => "Failed to delete product."]));
    }

    if (!empty($existing['productImage'])) {
        $oldAbs = __DIR__ . '/../' . $existing['productImage'];
        if (file_exists($oldAbs)) @unlink($oldAbs);
    }

    Flight::json(["message" => "Product deleted", "productId" => $pid]);
});