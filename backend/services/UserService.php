<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $dao;

    public function __construct() {
        $this->dao = new UserDao();
    }

    public function getAll() {
        $users = $this->dao->getAll();
        return array_map(function($user) {
            unset($user['password']);
            return $user;
        }, $users);
    }

    public function getById($id) {
        $user = $this->dao->getById($id);
        if ($user) {
            unset($user['password']);
        }
        return $user;
    }

    public function create($data) {
        // Basic validation
        if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception("Missing required fields");
        }

      
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Set default role if not provided
        if (empty($data['role'])) {
            $data['role'] = 'user';
        }

        return $this->dao->insert($data);
    }

    public function update($id, $data) {
      
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        

        return $this->dao->update($id, $data);
    }

    public function delete($id) {
        return $this->dao->delete($id);
    }
}
?>
