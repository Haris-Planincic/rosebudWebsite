<?php
require_once __DIR__ . "/../dao/FilmDao.php";

class FilmService {
    private $dao;

    public function __construct() {
        $this->dao = new FilmDao();
    }

    public function getAll() {
        return $this->dao->getAll();
    }

    public function getById($id) {
        return $this->dao->getById($id);
    }

    public function create($data) {
        if (!preg_match('/^\d{4}$/', $data['yearOfRelease'])) {
            throw new Exception("Year of release must be 4 digits.");
        }

        if (empty($data['posterImage'])) {
            throw new Exception("Poster image must not be empty.");
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
