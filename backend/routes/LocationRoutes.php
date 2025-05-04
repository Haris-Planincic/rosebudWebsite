<?php
Flight::route('GET /locations', function(){
    Flight::json(Flight::locationService()->getAll());
});

Flight::route('GET /locations/@id', function($id){
    Flight::json(Flight::locationService()->getById($id));
});

Flight::route('POST /locations', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::locationService()->create($data));
});

Flight::route('PUT /locations/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::locationService()->update($id, $data));
});

Flight::route('DELETE /locations/@id', function($id){
    Flight::json(Flight::locationService()->delete($id));
});