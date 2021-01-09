<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2020-2021 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace WebPush\Tests\Bundle\FakeApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebPush\Subscription as BaseSubscription;

/**
 * @ORM\Table(name="subscriptions")
 * @ORM\Entity(repositoryClass="WebPush\Tests\Bundle\FakeApp\Repository\SubscriptionRepository")
 */
class Subscription extends BaseSubscription
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
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
        foreach ($base->getKeys()->all() as $k => $v) {
            $object->getKeys()->set($k, $v);
        }

        return $object;
    }
}
