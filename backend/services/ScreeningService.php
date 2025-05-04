<?php
require_once __DIR__ . '/../dao/ScreeningDao.php';

class ScreeningService {
    private $dao;

    public function __construct() {
        $this->dao = new ScreeningDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        if (!isset($data['filmId'], $data['locationId'], $data['screeningTime'])) {
            throw new Exception("Missing screening data.");
        }

        if (empty($data['screeningImage'])) {
            throw new Exception("Screening image must not be empty.");
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

