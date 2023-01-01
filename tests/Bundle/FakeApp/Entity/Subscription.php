<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\FakeApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebPush\Subscription as BaseSubscription;

/**
 * @ORM\Table(name="subscriptions")
 * @ORM\Entity(repositoryClass="WebPush\Tests\Bundle\FakeApp\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @var array<string, string>
     * @ORM\Column(type="json")
     */
    public array $keys;

    /**
     * @var string[]
     * @ORM\Column(type="simple_array")
     */
    public array $supportedContentEncodings = ['aesgcm'];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $expirationTime = null;

    public function __construct(
        /**
         * @ORM\Id
         * @ORM\Column(type="string")
         * @ORM\GeneratedValue(strategy="NONE")
         */
        public readonly string $id,
        /**
         * @ORM\Column(type="string")
         */
        public readonly string $endpoint
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public static function createFromBaseSubscription(string $input): self
    {
        $base = BaseSubscription::createFromString($input);
        $object = new self(bin2hex(random_bytes(16)), $base->getEndpoint());
        $object->supportedContentEncodings = $base->getSupportedContentEncodings();
        $object->keys = $base->getKeys();
        $object->expirationTime = $base->getExpirationTime();

        return $object;
    }
}
