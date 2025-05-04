<?php
Flight::route('GET /purchases', function(){
    Flight::json(Flight::purchaseService()->getAll());
});

Flight::route('GET /purchases/@id', function($id){
    Flight::json(Flight::purchaseService()->getById($id));
});

Flight::route('POST /purchases', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::purchaseService()->create($data));
});

Flight::route('PUT /purchases/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::purchaseService()->update($id, $data));
});

Flight::route('DELETE /purchases/@id', function($id){
    Flight::json(Flight::purchaseService()->delete($id));
});
