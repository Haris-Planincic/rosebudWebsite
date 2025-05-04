<?php
require_once 'config.php';

class BaseDao {
   protected $table;
   protected $connection;

   public function __construct($table) {
       $this->table = $table;
       $this->connection = Database::connect();
   }

   public function getAll() {
       $stmt = $this->connection->prepare("SELECT * FROM " . $this->table);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public function getById($id, $primaryKey = 'id') {
       $stmt = $this->connection->prepare("SELECT * FROM " . $this->table . " WHERE " . $primaryKey . " = :id");
       $stmt->bindParam(':id', $id, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetch(PDO::FETCH_ASSOC);
   }

   public function insert($data) {
       $columns = implode(", ", array_keys($data));
       $placeholders = ":" . implode(", :", array_keys($data));
       $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
       $stmt = $this->connection->prepare($sql);
       return $stmt->execute($data);
   }

   public function update($id, $data, $primaryKey = 'id') {
       $fields = "";
       foreach ($data as $key => $value) {
           $fields .= "$key = :$key, ";
       }
       $fields = rtrim($fields, ", ");
       $sql = "UPDATE " . $this->table . " SET $fields WHERE " . $primaryKey . " = :id";
       $stmt = $this->connection->prepare($sql);
       $data['id'] = $id;
       return $stmt->execute($data);
   }

   public function delete($id, $primaryKey = 'id') {
       $stmt = $this->connection->prepare("DELETE FROM " . $this->table . " WHERE " . $primaryKey . " = :id");
       $stmt->bindParam(':id', $id, PDO::PARAM_INT);
       return $stmt->execute();
   }
}
?>
