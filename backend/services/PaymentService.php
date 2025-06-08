<?php
require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/../dao/ProductDao.php'; 

class PaymentService {
    private $dao;
    private $productDao;

    public function __construct() {
        $this->dao = new PaymentDao();
        $this->productDao = new ProductDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
    if (!is_numeric($data['productId'])) {
        throw new Exception("Invalid product ID.");
    }

    // Fetch product price
    $product = (new ProductDao())->getById($data['productId']);
    if (!$product) throw new Exception("Product not found.");

    $data['amount'] = $product['productPrice'];

    // Insert payment
    $paymentId = $this->dao->insert($data);

    // Mark product as sold
    $pdo = Database::connect();
    $stmt = $pdo->prepare("UPDATE Products SET isSold = 1 WHERE productId = :productId");
    $stmt->execute([':productId' => $data['productId']]);

    return $paymentId;
}


    public function update($id, $data) {
        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
}
?>
