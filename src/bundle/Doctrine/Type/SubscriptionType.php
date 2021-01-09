<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Bundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use function Safe\json_encode;
use Throwable;
use WebPush\Subscription;

final class SubscriptionType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof Subscription) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Subscription::class]);
        }

        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Subscription
    {
        if (null === $value || $value instanceof Subscription) {
            return $value;
        }
        try {
            return Subscription::createFromString($value);
        } catch (Throwable $e) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string'], $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'webpush_subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
