<?php

declare(strict_types=1);

namespace WebPush\VAPID;

use Assert\Assertion;
use function json_encode;
use JsonException;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function sprintf;
use WebPush\Base64Url;
use WebPush\Loggable;
use WebPush\Utils;

final class LcobucciProvider implements JWSProvider, Loggable
{
    public const PUBLIC_KEY_LENGTH = 65;
    public const PRIVATE_KEY_LENGTH = 32;
    private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    private string $publicKey;
    private LoggerInterface $logger;
    private Key $key;

    public function __construct(string $publicKey, string $privateKey)
    {
        $privateKeyBin = Base64Url::decode($privateKey);
        Assertion::eq(mb_strlen($privateKeyBin, '8bit'), self::PRIVATE_KEY_LENGTH, 'Invalid private key size');

        $publicKeyBin = Base64Url::decode($publicKey);
        Assertion::eq(mb_strlen($publicKeyBin, '8bit'), self::PUBLIC_KEY_LENGTH, 'Invalid public key size', );
        Assertion::startsWith($publicKeyBin, "\4", 'Invalid public key', null, '8bit');

        $this->publicKey = $publicKey;
        $pem = Utils::privateKeyToPEM(
            Base64Url::decode($privateKey),
            Base64Url::decode($publicKey)
        );
        $this->key = InMemory::plainText($pem);
        $this->logger = new NullLogger();
    }

    public static function create(string $publicKey, string $privateKey): self
    {
        return new self($publicKey, $privateKey);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function computeHeader(array $claims): Header
    {
        $this->logger->debug('Computing the JWS');
        $signer = Sha256::create();
        $header = json_encode(['typ' => 'JWT', 'alg' => 'ES256'], JSON_THROW_ON_ERROR | self::JSON_OPTIONS);
        $payload = json_encode($claims, JSON_THROW_ON_ERROR | self::JSON_OPTIONS);
        $dataToSign = sprintf(
            '%s.%s',
            Base64Url::encode($header),
            Base64Url::encode($payload)
        );
        $signature = $signer->sign($dataToSign, $this->key);
        $token = sprintf(
            '%s.%s',
            $dataToSign,
            Base64Url::encode($signature)
        );

        $this->logger->debug('JWS computed', ['token' => $token, 'key' => $this->publicKey]);

        return new Header(
            $token,
            $this->publicKey
        );
    }
}
