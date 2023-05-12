<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\FakeApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebPush\Subscription as BaseSubscription;
use WebPush\Tests\Bundle\FakeApp\Repository\SubscriptionRepository;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
class Subscription extends BaseSubscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public static function createFromBaseSubscription(string $input): self
    {
        $base = BaseSubscription::createFromString($input);
        $object = new self($base->getEndpoint());
        $object->withContentEncodings($base->getSupportedContentEncodings());
        foreach ($base->getKeys() as $k => $v) {
            $object->setKey($k, $v);
        }

        return $object;
    }
}
