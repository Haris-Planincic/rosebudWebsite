<?php

Flight::group('/messages', function() {

  Flight::route('GET /conversations', function () {
    Flight::auth_middleware()->verifyToken();
    $me = (int) Flight::get('user')->userId;

    $list = Flight::messageService()->listMyConversations($me);
    Flight::json($list);
  });

  Flight::route('POST /direct/@otherUserId', function($otherUserId) {
    Flight::auth_middleware()->verifyToken();
    $me = (int) Flight::get('user')->userId;

    $conversationId = Flight::messageService()
      ->getOrCreateDirectConversation($me, (int)$otherUserId);

    Flight::json(["conversationId" => (int)$conversationId]);
  });

  Flight::route('POST /@conversationId/read', function($conversationId) {
    Flight::auth_middleware()->verifyToken();
    $me = (int) Flight::get('user')->userId;

    Flight::messageService()->ensureParticipant((int)$conversationId, $me);

    $ok = Flight::messageService()->markConversationRead((int)$conversationId, $me);
    Flight::json(["success" => (bool)$ok]);
  });

  Flight::route('POST /@conversationId', function($conversationId) {
    Flight::auth_middleware()->verifyToken();
    $me = (int) Flight::get('user')->userId;

    $data = Flight::request()->data->getData();
    if (empty($data['body'])) {
      Flight::halt(400, "Message body is required.");
    }

    Flight::messageService()->ensureParticipant((int)$conversationId, $me);

    $ok = Flight::messageService()->sendMessage((int)$conversationId, $me, $data['body']);
    Flight::json(["success" => (bool)$ok]);
  });

  Flight::route('GET /@conversationId', function($conversationId) {
    Flight::auth_middleware()->verifyToken();
    $me = (int) Flight::get('user')->userId;

    Flight::messageService()->ensureParticipant((int)$conversationId, $me);

    $msgs = Flight::messageService()->getMessages((int)$conversationId);
    Flight::json($msgs);
  });

});
