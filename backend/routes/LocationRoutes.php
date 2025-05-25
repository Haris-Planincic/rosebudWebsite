<?php
/**
 * @OA\Get(
 *     path="/locations",
 *     tags={"locations"},
 *     summary="Get all locations",
 *     @OA\Response(
 *         response=200,
 *         description="List of all locations"
 *     )
 * )
 */
Flight::route('GET /locations', function(){
    Flight::json(Flight::locationService()->getAll());
});
/**
 * @OA\Get(
 *     path="/locations/{locationId}",
 *     tags={"locations"},
 *     summary="Get location by ID",
 *     @OA\Parameter(
 *         name="locationId",
 *         in="path",
 *         required=true,
 *         description="ID of the location",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Details of a specific location"
 *     )
 * )
 */
Flight::route('GET /locations/@id', function($id){
    Flight::json(Flight::locationService()->getById($id));
});
/**
 * @OA\Post(
 *     path="/locations",
 *     tags={"locations"},
 *     summary="Add a new location",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "address"},
 *             @OA\Property(property="name", type="string", example="Cinema Hall A"),
 *             @OA\Property(property="address", type="string", example="123 Movie St")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New location created"
 *     )
 * )
 */
Flight::route('POST /locations', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::locationService()->create($data));
});
/**
 * @OA\Put(
 *     path="/locations/{locationId}",
 *     tags={"locations"},
 *     summary="Update a location",
 *     @OA\Parameter(
 *         name="locationId",
 *         in="path",
 *         required=true,
 *         description="ID of the location",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Cinema Hall A"),
 *             @OA\Property(property="address", type="string", example="456 Updated St")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Location updated"
 *     )
 * )
 */
Flight::route('PUT /locations/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::locationService()->update($id, $data));
});
/**
 * @OA\Delete(
 *     path="/locations/{locationId}",
 *     tags={"locations"},
 *     summary="Delete a location",
 *     @OA\Parameter(
 *         name="locationId",
 *         in="path",
 *         required=true,
 *         description="ID of the location",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Location deleted"
 *     )
 * )
 */
Flight::route('DELETE /locations/@id', function($id){
    Flight::json(Flight::locationService()->delete($id));
});