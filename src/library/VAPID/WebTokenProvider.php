<?php

declare(strict_types=1);

namespace WebPush\VAPID;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\Loggable;
use function hex2bin;
use function is_string;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class WebTokenProvider implements JWSProvider, Loggable
{
    public const PUBLIC_KEY_LENGTH = 65;

    public const PRIVATE_KEY_LENGTH = 32;

    private readonly JWK $signatureKey;

    private readonly CompactSerializer $serializer;

    private readonly JWSBuilder $jwsBuilder;

    private LoggerInterface $logger;

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
        $x = mb_substr($publicKeyBin, 1, self::PRIVATE_KEY_LENGTH, '8bit');
        $y = mb_substr($publicKeyBin, -self::PRIVATE_KEY_LENGTH, null, '8bit');

        $this->signatureKey = new JWK([
            'kty' => 'EC',
            'crv' => 'P-256',
            'd' => $privateKey,
            'x' => Base64Url::encode($x),
            'y' => Base64Url::encode($y),
        ]);
        $algorithmManager = new AlgorithmManager([new ES256()]);
        $this->serializer = new CompactSerializer();
        $this->jwsBuilder = new JWSBuilder($algorithmManager);
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
        $payload = json_encode($claims, JSON_THROW_ON_ERROR);
        $jws = $this->jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($this->signatureKey, [
                'typ' => 'JWT',
                'alg' => 'ES256',
            ])
            ->build()
        ;
        $token = $this->serializer->serialize($jws);
        $key = $this->serializePublicKey();
        $this->logger->debug('JWS computed', [
            'token' => $token,
            'key' => $key,
        ]);

        return Header::create($token, $key);
    }

    private function serializePublicKey(): string
    {
        $x = $this->signatureKey->get('x');
        is_string($x) || throw new OperationException('Invalid key');
        $y = $this->signatureKey->get('y');
        is_string($y) || throw new OperationException('Invalid key');

        $hexString = '04';
        $hexString .= bin2hex(Base64Url::decode($x));
        $hexString .= bin2hex(Base64Url::decode($y));
        $bin = hex2bin($hexString);
        if ($bin === false) {
            throw new OperationException('Unable to encode the public key');
        }

        return Base64Url::encode($bin);
    }
}
