<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\FakeApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebPush\Subscription;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="WebPush\Tests\Bundle\FakeApp\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    public function __construct(
        /**
         * @ORM\Column(type="webpush_subscription")
         */
        private Subscription $subscription
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
