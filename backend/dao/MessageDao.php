<?php
require_once 'BaseDao.php';

class MessageDao extends BaseDao {
    public function __construct() {
        parent::__construct("Messages", "messageId");
    }

    public function getByConversation($conversationId) {
        $sql = "SELECT m.*, u.firstName, u.lastName
                FROM Messages m
                JOIN Users u ON u.userId = m.senderId
                WHERE m.conversationId = :cid
                ORDER BY m.createdAt ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(["cid" => $conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function markAsRead($conversationId, $userId) {
  $sql = "UPDATE Messages
          SET isRead = 1
          WHERE conversationId = :cid
            AND senderId != :me
            AND isRead = 0";
  $stmt = $this->connection->prepare($sql);
  return $stmt->execute(["cid" => $conversationId, "me" => $userId]);
}

}
