<?php
Flight::route('GET /films', function(){
    Flight::json(Flight::filmService()->getAll());
});

Flight::route('GET /films/@filmId', function($id){
    Flight::json(Flight::filmService()->getById($id));
});

Flight::route('POST /films', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::filmService()->create($data));
});

Flight::route('PUT /films/@filmId', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::filmService()->update($id, $data));
});

Flight::route('DELETE /films/@filmId', function($id){
    Flight::json(Flight::filmService()->delete($id));
});
