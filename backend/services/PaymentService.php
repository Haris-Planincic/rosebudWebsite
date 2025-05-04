<?php
require_once __DIR__ . '/../dao/PaymentDao.php';

class PaymentService {
    private $dao;

    public function __construct() {
        $this->dao = new PaymentDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception("Amount must be a positive number.");
        }
        return $this->dao->insert($data);
    }

    public function update($id, $data) {
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
}
?>
