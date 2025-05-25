<?php
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
Flight::route('GET /screenings', function(){
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
Flight::route('GET /screenings/@id', function($id){
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
 *             required={"film_id", "location_id", "start_time"},
 *             @OA\Property(property="film_id", type="integer", example=1),
 *             @OA\Property(property="location_id", type="integer", example=2),
 *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-05-25T20:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New screening created"
 *     )
 * )
 */
Flight::route('POST /screenings', function(){
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
Flight::route('PUT /screenings/@id', function($id){
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
Flight::route('DELETE /screenings/@id', function($id){
    Flight::json(Flight::screeningService()->delete($id));
});
