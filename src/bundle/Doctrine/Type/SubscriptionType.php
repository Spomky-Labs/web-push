<?php

declare(strict_types=1);

namespace WebPush\Bundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use Throwable;
use WebPush\Subscription;

final class SubscriptionType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Subscription) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                ['null',
                Subscription::class,
                
            ]);
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Subscription
    {
        if ($value === null || $value instanceof Subscription) {
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
