<?php

declare(strict_types=1);

namespace WebPush\Payload;

use Psr\Clock\ClockInterface;
use WebPush\Base64Url;
use WebPush\Exception\OperationException;
use WebPush\RequestData;
use function pack;
use function sprintf;
use const STR_PAD_LEFT;

final class AESGCM extends AbstractAESGCM
{
    public const PADDING_MAX = 4078;

    private const ENCODING = 'aesgcm';

    public static function create(ClockInterface $clock): self
    {
        return new self($clock);
    }

    public function customPadding(int $padding): self
    {
        ($padding >= self::PADDING_NONE && $padding <= self::PADDING_MAX) || throw new OperationException(
            'Invalid padding size'
        );
        $this->padding = $padding;

        return $this;
    }

    public function maxPadding(): self
    {
        $this->padding = self::PADDING_MAX;

        return $this;
    }

    public function name(): string
    {
        return self::ENCODING;
    }

    protected function getKeyInfo(string $userAgentPublicKey, ServerKey $serverKey): string
    {
        return "Content-Encoding: auth\0";
    }

    protected function getContext(string $userAgentPublicKey, ServerKey $serverKey): string
    {
        return sprintf('%s%s%s%s', "P-256\0\0A", $userAgentPublicKey, "\0A", $serverKey->getPublicKey());
    }

    protected function addPadding(string $payload): string
    {
        $payloadLength = mb_strlen($payload, '8bit');
        $paddingLength = max(self::PADDING_NONE, $this->padding - $payloadLength);

        return pack('n*', $paddingLength) . str_pad($payload, $this->padding, "\0", STR_PAD_LEFT);
    }

    protected function prepareHeaders(RequestData $requestData, ServerKey $serverKey, string $salt): void
    {
        $requestData
            ->addHeader('Crypto-Key', sprintf('dh=%s', Base64Url::encode($serverKey->getPublicKey())))
            ->addHeader('Encryption', 'salt=' . Base64Url::encode($salt))
        ;
    }

    protected function prepareBody(string $encryptedText, ServerKey $serverKey, string $tag, string $salt): string
    {
        return $encryptedText . $tag;
    }
}
