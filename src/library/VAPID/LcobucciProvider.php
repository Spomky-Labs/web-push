<?php

declare(strict_types=1);

namespace WebPush\VAPID;

use function json_encode;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function sprintf;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\Loggable;
use WebPush\Utils;

final class LcobucciProvider implements JWSProvider, Loggable
{
    public const PUBLIC_KEY_LENGTH = 65;

    public const PRIVATE_KEY_LENGTH = 32;

    private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    private readonly string $publicKey;

    private LoggerInterface $logger;

    private readonly Key $key;

    public function __construct(string $publicKey, string $privateKey)
    {
        $privateKeyBin = Base64Url::decode($privateKey);
        mb_strlen($privateKeyBin, '8bit') === self::PRIVATE_KEY_LENGTH || throw new OperationException(
            'Invalid private key size'
        );

        $publicKeyBin = Base64Url::decode($publicKey);
        mb_strlen($publicKeyBin, '8bit') === self::PUBLIC_KEY_LENGTH || throw new OperationException(
            'Invalid public key size'
        );
        str_starts_with($publicKeyBin, "\4") || throw new OperationException('Invalid public key');

        $this->publicKey = $publicKey;
        $pem = Utils::privateKeyToPEM(Base64Url::decode($privateKey), Base64Url::decode($publicKey));
        $pem !== '' || throw new OperationException('Invalid PEM');
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

    public function computeHeader(array $claims): Header
    {
        $this->logger->debug('Computing the JWS');
        $signer = new Sha256();
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'ES256',
        ], JSON_THROW_ON_ERROR | self::JSON_OPTIONS);
        $payload = json_encode($claims, JSON_THROW_ON_ERROR | self::JSON_OPTIONS);
        $dataToSign = sprintf('%s.%s', Base64Url::encode($header), Base64Url::encode($payload));
        $signature = $signer->sign($dataToSign, $this->key);
        $token = sprintf('%s.%s', $dataToSign, Base64Url::encode($signature));

        $this->logger->debug('JWS computed', [
            'token' => $token,
            'key' => $this->publicKey,
        ]);

        return Header::create($token, $this->publicKey);
    }
}
