<?php
require_once __DIR__ . '/../dao/ProductDao.php';

class ProductService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductDao();
    }

 public function getAll($q = null) {
    if ($q !== null && trim($q) !== '') {
        return $this->dao->searchPublic($q);
    }
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
    public function updateOwned($productId, $sellerId, $data) {
    if (isset($data['productPrice'])) {
        if (!is_numeric($data['productPrice']) || (float)$data['productPrice'] <= 0) {
            throw new Exception("Product price must be a positive number.");
        }
        $data['productPrice'] = (float)$data['productPrice'];
    }

    if (isset($data['productName'])) {
        $data['productName'] = trim($data['productName']);
        if ($data['productName'] === '') throw new Exception("Product name must not be empty.");
    }

    if (isset($data['productDescription'])) {
        $data['productDescription'] = trim($data['productDescription']);
    }

    $affected = $this->dao->updateOwned($productId, $sellerId, $data, true);

    if ($affected === 0) {
        // could be: not owner, not found, sold, or no changes
        return 0;
    }

    return $affected;
}

}
?>
