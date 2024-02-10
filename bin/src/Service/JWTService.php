<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // On génère le token
    /**
     * Generation JMT
     *
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param integer $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
    
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }


        // On encode en base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // On "nettoie" les valeurs encodées (retrait des +, / et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ""], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ""], $base64Payload);

        // On génère la signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ""], $base64Signature);

        // On crée le token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    // on vérifie si le token est valide et correctement formée

    public function isValid (string $token):bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }
    

    // le token a expiré, on réccupère le Header
    public function getHeader (string $token):array
    {
        // on démonte le token
        $array= explode('.',$token);
        // on décode le payload
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    // le token a expiré, on réccupère le payLoad
    public function getPayLoad (string $token):array
    {
        // on démonte le token
        $array= explode('.',$token);
        // on décode le payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    public function isExpired ($token):bool
    {
        $payload= $this->getPayLoad($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

        // On vérifie la signature du Token
    public function check (string $token, string $secret)
    {
        // on reccupère le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayLoad($token);

        // on regénère le token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        // le token n'a pas été corrompu et il est valide
        return $token === $verifToken;

    }
}