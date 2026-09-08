<?php

declare(strict_types=1);

namespace WebPush;

use WebPush\Exception\OperationException;
use function is_array;
use function is_string;
use function pack;
use function unpack;
use const PHP_EOL;
use const STR_PAD_LEFT;

abstract class Utils
{
    private const PART_SIZE = 32;

    private const HASH_SIZE = 256;

    public static function privateKeyToPEM(string $privateKey, string $publicKey): string
    {
        $d = unpack('H*', str_pad($privateKey, self::PART_SIZE, "\0", STR_PAD_LEFT));
        if (! is_array($d) || ! isset($d[1])) {
            throw new OperationException('Unable to convert the private key');
        }

        $der = pack(
            'H*',
            '3077' // SEQUENCE, length 87+length($d)=32
                . '020101' // INTEGER, 1
                    . '0420'   // OCTET STRING, length($d) = 32
                        . $d[1]
                    . 'a00a' // TAGGED OBJECT #0, length 10
                        . '0608' // OID, length 8
                            . '2a8648ce3d030107' // 1.3.132.0.34 = P-256 Curve
                    . 'a144' //  TAGGED OBJECT #1, length 68
                        . '0342' // BIT STRING, length 66
                            . '00' // prepend with NUL - pubkey will follow
        );
        $der .= $publicKey;

        $pem = '-----BEGIN EC PRIVATE KEY-----' . PHP_EOL;
        $pem .= chunk_split(base64_encode($der), 64, PHP_EOL);

        return $pem . ('-----END EC PRIVATE KEY-----' . PHP_EOL);
    }

    public static function publicKeyToPEM(string $publicKey): string
    {
        $der = pack(
            'H*',
            '3059' // SEQUENCE, length 89
                . '3013' // SEQUENCE, length 19
                    . '0607' // OID, length 7
                        . '2a8648ce3d0201' // 1.2.840.10045.2.1 = EC Public Key
                    . '0608' // OID, length 8
                        . '2a8648ce3d030107' // 1.2.840.10045.3.1.7 = P-256 Curve
                . '0342' // BIT STRING, length 66
                    . '00' // prepend with NUL - pubkey will follow
        );
        $der .= $publicKey;

        $pem = '-----BEGIN PUBLIC KEY-----' . PHP_EOL;
        $pem .= chunk_split(base64_encode($der), 64, PHP_EOL);

        return $pem . ('-----END PUBLIC KEY-----' . PHP_EOL);
    }

    public static function computeIKM(
        string $keyInfo,
        string $userAgentAuthToken,
        string $userAgentPublicKey,
        string $serverPrivateKey,
        string $serverPublicKey
    ): string {
        $sharedSecret = self::computeAgreementKey($userAgentPublicKey, $serverPrivateKey, $serverPublicKey);

        return self::hkdf($userAgentAuthToken, $sharedSecret, $keyInfo, self::PART_SIZE);
    }

    private static function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        // Extract
        $prk = hash_hmac('sha256', $ikm, $salt, true);

        // Expand
        return mb_substr(hash_hmac('sha256', $info . "\1", $prk, true), 0, $length, '8bit');
    }

    private static function computeAgreementKey(
        string $userAgentPublicKey,
        string $serverPrivateKey,
        string $serverPublicKey
    ): string {
        $serverPrivateKeyPEM = self::privateKeyToPEM($serverPrivateKey, $serverPublicKey);
        $userAgentPublicKeyPEM = self::publicKeyToPEM($userAgentPublicKey);
        $result = openssl_pkey_derive($userAgentPublicKeyPEM, $serverPrivateKeyPEM, self::HASH_SIZE);
        is_string($result) || throw new OperationException('Unable to compute the agreement key');

        return str_pad($result, self::PART_SIZE, "\0", STR_PAD_LEFT);
    }
}
