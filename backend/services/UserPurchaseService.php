<?php
require_once __DIR__ . '/../dao/UserPurchaseDao.php';

class UserPurchaseService {
    private $dao;

    public function __construct() {
        $this->dao = new UserPurchaseDao();
    }

    public function getAll() {
        return $this->dao->getAllUserPurchases();
    }

    public function getById($id) {
        return $this->dao->getUserPurchaseById($id);
    }
    public function getMine($userId) {
    return $this->dao->getPurchasesForUser($userId);
}


    public function create($data) {
        if (!isset($data['userId'], $data['productId'])) {
            throw new Exception("User ID and Product ID are required.");
        }
        return $this->dao->createUserPurchase($data['userId'], $data['productId']);
    }

    public function update($id, $data) {
        return $this->dao->updateUserPurchase($id, $data);
    }

    public function delete($id) {
        return $this->dao->deleteUserPurchase($id);
    }
}
?>
