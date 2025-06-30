<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $jwt_secret;

    public function __construct($jwt_secret)
    {
        $this->jwt_secret = $jwt_secret;
    }

    public function handle()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $jwt = $_COOKIE['jwt_token'] ?? null;

        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key($this->jwt_secret, 'HS256'));

                if (isset($decoded->sub) && isset($decoded->role)) {
                    if ($decoded->role === 'patient') {
                        $_SESSION['patient_id'] = $decoded->sub;
                    } elseif ($decoded->role === 'doctor') {
                        $_SESSION['medic_id'] = $decoded->sub;
                    }
                }

                return $decoded;
            } catch (Exception $e) {
                // Invalid token: delete cookie
                setcookie('jwt_token', '', time() - 3600, '/', '', true, true);
                return null;
            }
        }

        return null;
    }
}
