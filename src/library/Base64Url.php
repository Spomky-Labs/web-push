<?php

declare(strict_types=1);

namespace WebPush;

use function base64_decode;
use function base64_encode;
use function rtrim;
use function strtr;
use WebPush\Exception\OperationException;

/**
 * Encode and decode data into Base64 Url Safe.
 */
final class Base64Url
{
    public static function encode(string $data): string
    {
        $encoded = strtr(base64_encode($data), '+/', '-_');

        return rtrim($encoded, '=');
    }

    public static function decode(string $data): string
    {
        $encoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($encoded === false) {
            throw new OperationException('Unable to base64 encode the data');
        }

        return $encoded;
    }
}
