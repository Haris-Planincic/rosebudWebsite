<?php
require_once __DIR__ . "/../dao/LocationDao.php";

class LocationService {
    private $dao;

    public function __construct() {
        $this->dao = new LocationDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        if (empty($data['locationAddress']) || empty($data['locationName'])) {
            throw new Exception("Address and name must not be empty.");
        }

        if (empty($data['locationImage'])) {
            throw new Exception("Location image must not be empty.");
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
