<?php

declare(strict_types=1);

namespace WebPush\Payload;

use function pack;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\RequestInterface;
use const STR_PAD_RIGHT;
use WebPush\Exception\OperationException;

final class AES128GCM extends AbstractAESGCM
{
    public const PADDING_MAX = 3993; // as per RFC8291: 4096 -tag(16) -salt(16) -rs(4) -idlen(1) -keyid(65) -AEAD_AES_128_GCM expension(16) and 1 byte in case of

    private const ENCODING = 'aes128gcm';

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
        return 'WebPush: info' . "\0" . $userAgentPublicKey . $serverKey->getPublicKey();
    }

    protected function getContext(string $userAgentPublicKey, ServerKey $serverKey): string
    {
        return '';
    }

    protected function addPadding(string $payload): string
    {
        return str_pad($payload . "\2", $this->padding, "\0", STR_PAD_RIGHT);
    }

    protected function prepareHeaders(RequestInterface $request, ServerKey $serverKey, string $salt): RequestInterface
    {
        return $request;
    }

    protected function prepareBody(string $encryptedText, ServerKey $serverKey, string $tag, string $salt): string
    {
        return $salt .
            pack('N*', 4096) .
            pack('C*', mb_strlen($serverKey->getPublicKey(), '8bit')) .
            $serverKey->getPublicKey() .
            $encryptedText .
            $tag
        ;
    }
}
