<?php
require_once __DIR__ . '/../dao/AuthDao.php';


use Firebase\JWT\JWT;

class AuthService {
    private $dao;

    public function __construct() {
        $this->dao = new AuthDao();
    }

    public function get_user_by_email($email) {
        return $this->dao->get_user_by_email($email);
    }

    public function register($data) {
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'error' => 'Email and password are required.'];
    }

    // Check if email already exists
    $existingUser = $this->dao->get_user_by_email($data['email']);
    if ($existingUser) {
        return ['success' => false, 'error' => 'Email already registered.'];
    }

    // Set default role
    $data['role'] = 'user'; 

    // Hash password
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insert new user
    $insertSuccess = $this->dao->insert($data);
    if (!$insertSuccess) {
        return ['success' => false, 'error' => 'Failed to register user.'];
    }

    unset($data['password']); 

    return ['success' => true, 'data' => ['email' => $data['email']]];
}


    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->dao->get_user_by_email($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        unset($user['password']);

        $payload = [
            'user' => $user,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24)
        ];

        $token = JWT::encode($payload, Config::JWT_SECRET(), 'HS256');

        return [
            'success' => true,
            'data' => array_merge($user, ['token' => $token])
        ];
    }
    public function issueTokenFromUser($user) {
    unset($user['password']);

    $payload = [
        'user' => $user,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) 
    ];

    return JWT::encode($payload, Config::JWT_SECRET(), 'HS256');
}

}
