<?php
require_once __DIR__ . '/../dao/AuthDao.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private $dao;

    public function __construct() {
        $this->dao = new AuthDao();
    }

    public function get_user_by_email($email) {
        return $this->dao->get_user_by_email($email);
    }

    public function register($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        if ($this->dao->get_user_by_email($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $newUser = $this->dao->insert($data);

        unset($newUser['password']);

        return ['success' => true, 'data' => $newUser];
    }

    public function login($data) {
    if (empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'error' => 'Email and password are required.'];
    }

    $user = $this->dao->get_user_by_email($data['email']);

    if (!$user) {
        return ['success' => false, 'error' => 'User not found.'];
    }

    // Debug print
    error_log("User fetched for login: " . json_encode($user));

    if (!password_verify($data['password'], $user['password'])) {
        return ['success' => false, 'error' => 'Invalid password.'];
    }

    unset($user['password']);

    $payload = [
        'user' => $user,
        'iat' => time(),
        'exp' => time() + 86400,
    ];

    $token = JWT::encode($payload, Config::JWT_SECRET(), 'HS256');

    // Debug print token
    error_log("JWT token generated: " . $token);

    return ['success' => true, 'data' => array_merge($user, ['token' => $token])];
}
}


