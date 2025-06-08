<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    public function verifyToken() {
        $headers = getallheaders(); 

        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            Flight::halt(401, "Missing authentication header");
        }

        $token = trim(str_replace('Bearer ', '', $authHeader));

        try {
            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
            Flight::set('user', $decoded_token->user);
            Flight::set('jwt_token', $token);
            return true;
        } catch (Exception $e) {
            Flight::halt(401, "Invalid or expired token");
        }
    }

    public function authorizeRole($requiredRole) {
        $this->verifyToken(); // 👈 ensure token is loaded
        $user = Flight::get('user');
        if (!isset($user->role) || $user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    public function authorizeRoles($roles) {
        $this->verifyToken(); // 👈 ensure token is loaded
        $user = Flight::get('user');
        if (!isset($user->role) || !in_array($user->role, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }

    public function authorizePermission($permission) {
        $this->verifyToken(); // 👈 ensure token is loaded
        $user = Flight::get('user');
        if (!isset($user->permissions) || !in_array($permission, $user->permissions)) {
            Flight::halt(403, 'Access denied: permission missing');
        }
    }
}

