<?php
namespace RentCar\Core;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT {
    private static $secretKey;
    private static $algorithm = 'HS256';

    public static function init($secretKey) {
        self::$secretKey = $secretKey;
    }

    public static function encode($payload) {
        $issuedAt = time();
        $expire = $issuedAt + (60 * 60 * 24); // 24 heures

        $tokenPayload = [
            'iss' => 'rentcar-api',
            'aud' => 'rentcar-app',
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $payload
        ];

        return FirebaseJWT::encode($tokenPayload, self::$secretKey, self::$algorithm);
    }

    public static function decode($token) {
        try {
            // NOTE: Key doit être instancié pour la fonction decode
            $decoded = FirebaseJWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            error_log("JWT decode error: " . $e->getMessage());
            return null;
        }
    }

    public static function validate($token) {
        return self::decode($token) !== null;
    }

    /**
     * Récupère le jeton JWT (Bearer) à partir de l'en-tête Authorization.
     * Utilise $_SERVER pour une meilleure compatibilité.
     */
    public static function getTokenFromHeader() {
        // L'en-tête Authorization est généralement dans $_SERVER['HTTP_AUTHORIZATION']
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}