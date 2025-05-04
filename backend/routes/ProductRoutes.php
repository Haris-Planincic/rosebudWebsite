<?php
Flight::route('GET /products', function(){
    Flight::json(Flight::productService()->getAll());
});

Flight::route('GET /products/@id', function($id){
    Flight::json(Flight::productService()->getById($id));
});

Flight::route('POST /products', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::productService()->create($data));
});

Flight::route('PUT /products/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::productService()->update($id, $data));
});

Flight::route('DELETE /products/@id', function($id){
    Flight::json(Flight::productService()->delete($id));
});