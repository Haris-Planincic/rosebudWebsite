<?php
/**
 * @OA\Get(
 *     path="/purchases",
 *     tags={"purchases"},
 *     summary="Get all user purchases",
 *     @OA\Response(
 *         response=200,
 *         description="List of all purchases"
 *     )
 * )
 */
Flight::route('GET /purchases', function(){
    Flight::json(Flight::purchaseService()->getAll());
});
/**
 * @OA\Get(
 *     path="/purchases/{purchaseId}",
 *     tags={"purchases"},
 *     summary="Get purchase by ID",
 *     @OA\Parameter(
 *         name="purchaseId",
 *         in="path",
 *         required=true,
 *         description="ID of the purchase",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Details of a specific purchase"
 *     )
 * )
 */
Flight::route('GET /purchases/@id', function($id){
    Flight::json(Flight::purchaseService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/purchases",
 *     tags={"purchases"},
 *     summary="Create a new purchase",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "product_id", "quantity"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="product_id", type="integer", example=5),
 *             @OA\Property(property="quantity", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Purchase recorded"
 *     )
 * )
 */
Flight::route('POST /purchases', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::purchaseService()->create($data));
});
/**
 * @OA\Put(
 *     path="/purchases/{purchaseId}",
 *     tags={"purchases"},
 *     summary="Update a purchase",
 *     @OA\Parameter(
 *         name="purchaseId",
 *         in="path",
 *         required=true,
 *         description="ID of the purchase",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="product_id", type="integer", example=5),
 *             @OA\Property(property="quantity", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Purchase updated"
 *     )
 * )
 */
Flight::route('PUT /purchases/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::purchaseService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/purchases/{purchaseId}",
 *     tags={"purchases"},
 *     summary="Delete a purchase",
 *     @OA\Parameter(
 *         name="purchaseId",
 *         in="path",
 *         required=true,
 *         description="ID of the purchase",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Purchase deleted"
 *     )
 * )
 */
Flight::route('DELETE /purchases/@id', function($id){
    Flight::json(Flight::purchaseService()->delete($id));
});
