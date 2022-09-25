<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{


    // On génère un token
    /**
     *Génération du json web token
     *
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param integer $validity
     * @return string
     */
    public function generateToken(array $header, array $payload, string $secret, int $validity = 10800): string
    {

        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        // On encode en base 64
        $header = base64_encode(json_encode($header));
        $payload = base64_encode(json_encode($payload));

        // On nettoie les valeurs pour les utiliser dans la signature (on supprime les = et les +)
        $header = str_replace(['+', '/', '='], ['-', '_', ''], $header);
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

        // On génère la signature
        $signature = hash_hmac('sha256', $header . '.' . $payload, $secret, true);
        $signature = base64_encode($signature);
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature);

        return $header . '.' . $payload . '.' . $signature;
    }





    // On vérifie q'un token est valide
    public function isValid(string $token): bool
    {

        return preg_match('/^[a-zA-Z0-9\_\-\=]+\.[a-zA-Z0-9\_\-\=]+\.[a-zA-Z0-9\_\-\=]+$/', $token) === 1;
    }

    // On recupère le payload d'un token
    public function getPayload(string $token): array
    {
        // On demonte le token
        $data = explode('.', $token);

        // On decode le payload
        $payload = json_decode(base64_decode($data[1]), true);

        return $payload;
    }

    // On recupère le header d'un token
    public function getHeader(string $token): array
    {
        // On demonte le token
        $data = explode('.', $token);

        // On decode le payload
        $header = json_decode(base64_decode($data[0]), true);

        return $header;
    }


    // On vérifie qu'un token est valide et qu'il n'a pas expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        // On verifie que le token n'est pas expiré
        return $payload['exp'] < $now->getTimestamp();
    }

    // On vérifie la signature d'un token
    public function check(string $token, string $secret): bool
    {
        // On recupère le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // On regenère un token avec le header et le payload
        $verifToken = $this->generateToken($header, $payload, $secret,0);
        return $verifToken === $token;
    }
}
