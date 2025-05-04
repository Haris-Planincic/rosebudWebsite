<?php
require_once __DIR__ . '/../dao/ProductDao.php';

class ProductService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        if (!is_numeric($data['productPrice']) || $data['productPrice'] <= 0) {
            throw new Exception("Product price must be a positive number.");
        }

        if (empty($data['productImage'])) {
            throw new Exception("Product image must not be empty.");
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
