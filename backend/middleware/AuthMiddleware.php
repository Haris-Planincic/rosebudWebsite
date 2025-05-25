<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    /**
     * Verify JWT token and set user context.
     *
     * @param string|null $token
     * @return bool
     */
    public function verifyToken(?string $token): bool {
        if (!$token) {
            Flight::halt(401, "Missing authentication header");
        }
        try {
            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
        } catch (\Exception $e) {
            Flight::halt(401, 'Invalid or expired token: ' . $e->getMessage());
        }

        Flight::set('user', $decoded_token->user);
        Flight::set('jwt_token', $token);
        return true;
    }

    /**
     * Authorize user with exact role.
     *
     * @param string $requiredRole
     * @return void
     */
    public function authorizeRole(string $requiredRole): void {
        $user = Flight::get('user');
        if (!$user || !isset($user->role) || $user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    /**
     * Authorize user if their role is in allowed roles.
     *
     * @param array $roles
     * @return void
     */
    public function authorizeRoles(array $roles): void {
        $user = Flight::get('user');
        if (!$user || !isset($user->role) || !in_array($user->role, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }

    /**
     * Authorize user if they have a specific permission.
     *
     * @param string $permission
     * @return void
     */
    public function authorizePermission(string $permission): void {
        $user = Flight::get('user');
        if (
            !$user ||
            !isset($user->permissions) ||
            !is_array($user->permissions) ||
            !in_array($permission, $user->permissions)
        ) {
            Flight::halt(403, 'Access denied: permission missing');
        }
    }
}
