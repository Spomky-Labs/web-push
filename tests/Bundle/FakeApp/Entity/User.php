<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\FakeApp\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="WebPush\Tests\Bundle\FakeApp\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    public readonly string $id;

    public function __construct(
        /**
         * @ORM\OneToOne(targetEntity=\WebPush\Tests\Bundle\FakeApp\Entity\Subscription::class, cascade={"all"})
         */
        public readonly Subscription $subscription
    ) {
        $this->id = bin2hex(random_bytes(16));
    }
}
