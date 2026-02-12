<?php
require_once 'BaseDao.php';

class ConversationDao extends BaseDao {
    public function __construct() {
        parent::__construct("Conversations", "conversationId");
    }

    public function createConversation() {
        $sql = "INSERT INTO Conversations () VALUES ()";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    public function addParticipant($conversationId, $userId) {
        $sql = "INSERT INTO ConversationParticipants (conversationId, userId)
                VALUES (:conversationId, :userId)";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            "conversationId" => $conversationId,
            "userId" => $userId
        ]);
    }

    public function findDirectConversation($userA, $userB) {
        $sql = "
        SELECT cp1.conversationId
        FROM ConversationParticipants cp1
        JOIN ConversationParticipants cp2
          ON cp1.conversationId = cp2.conversationId
        WHERE cp1.userId = :userA AND cp2.userId = :userB
        LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(["userA" => $userA, "userB" => $userB]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isParticipant($conversationId, $userId) {
        $sql = "SELECT 1 FROM ConversationParticipants
                WHERE conversationId = :cid AND userId = :uid
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(["cid" => $conversationId, "uid" => $userId]);
        return (bool)$stmt->fetchColumn();
    }
    public function getMyConversations($userId) {
  $sql = "
    SELECT 
      c.conversationId,
      c.createdAt,

      -- last message
      m.messageId AS lastMessageId,
      m.body AS lastMessageBody,
      m.createdAt AS lastMessageAt,
      m.senderId AS lastSenderId,

      -- other user (for direct chat)
      u.userId AS otherUserId,
      u.firstName AS otherFirstName,
      u.lastName AS otherLastName,

      -- unread count (messages not sent by me and not read)
      SUM(CASE WHEN m2.isRead = 0 AND m2.senderId != :me THEN 1 ELSE 0 END) AS unreadCount

    FROM Conversations c
    JOIN ConversationParticipants cp_me
      ON cp_me.conversationId = c.conversationId AND cp_me.userId = :me
    JOIN ConversationParticipants cp_other
      ON cp_other.conversationId = c.conversationId AND cp_other.userId != :me
    JOIN Users u
      ON u.userId = cp_other.userId

    LEFT JOIN Messages m
      ON m.messageId = (
        SELECT mm.messageId
        FROM Messages mm
        WHERE mm.conversationId = c.conversationId
        ORDER BY mm.createdAt DESC
        LIMIT 1
      )

    LEFT JOIN Messages m2
      ON m2.conversationId = c.conversationId

    GROUP BY c.conversationId, u.userId, m.messageId
    ORDER BY COALESCE(m.createdAt, c.createdAt) DESC
  ";

  $stmt = $this->connection->prepare($sql);
  $stmt->execute(["me" => $userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
