<?php
require_once __DIR__ . '/../dao/ConversationDao.php';
require_once __DIR__ . '/../dao/MessageDao.php';

class MessageService {
    private $conversationDao;
    private $messageDao;

    public function __construct() {
        $this->conversationDao = new ConversationDao();
        $this->messageDao = new MessageDao();
    }

    // Create or reuse a direct chat between 2 users
    public function getOrCreateDirectConversation($userA, $userB) {
        $existing = $this->conversationDao->findDirectConversation($userA, $userB);
        if ($existing) return $existing['conversationId'];

        $conversationId = $this->conversationDao->createConversation();
        $this->conversationDao->addParticipant($conversationId, $userA);
        $this->conversationDao->addParticipant($conversationId, $userB);

        return $conversationId;
    }

    public function sendMessage($conversationId, $senderId, $body) {
        return $this->messageDao->insert([
            "conversationId" => $conversationId,
            "senderId" => $senderId,
            "body" => $body
        ]);
    }

    public function getMessages($conversationId) {
        return $this->messageDao->getByConversation($conversationId);
    }

    public function ensureParticipant($conversationId, $userId) {
        if (!$this->conversationDao->isParticipant($conversationId, $userId)) {
            Flight::halt(403, "You are not allowed to access this conversation.");
        }
    }
    public function listMyConversations($userId) {
        return $this->conversationDao->getMyConversations($userId);
    }

    public function markConversationRead($conversationId, $userId) {
        return $this->messageDao->markAsRead($conversationId, $userId);
    }
}
