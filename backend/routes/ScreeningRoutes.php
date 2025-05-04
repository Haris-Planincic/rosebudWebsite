<?php
Flight::route('GET /screenings', function(){
    Flight::json(Flight::screeningService()->getAll());
});

Flight::route('GET /screenings/@id', function($id){
    Flight::json(Flight::screeningService()->getById($id));
});

Flight::route('POST /screenings', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::screeningService()->create($data));
});

Flight::route('PUT /screenings/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::screeningService()->update($id, $data));
});

Flight::route('DELETE /screenings/@id', function($id){
    Flight::json(Flight::screeningService()->delete($id));
});
