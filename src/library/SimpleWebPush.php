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

namespace WebPush;

use Assert\Assertion;
use InvalidArgumentException;
use Jose\Component\Signature\Algorithm\ES256;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\LcobucciProvider;
use WebPush\VAPID\VAPIDExtension;
use WebPush\VAPID\WebTokenProvider;

class SimpleWebPush implements WebPushService
{
    private WebPush $service;
    private ExtensionManager $extensionManager;
    private bool $vapidEnabled = false;

    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $payloadExtension = PayloadExtension::create()
            ->addContentEncoding(AESGCM::create())
            ->addContentEncoding(AES128GCM::create())
        ;
        $this->extensionManager = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->add(TopicExtension::create())
            ->add(UrgencyExtension::create())
            ->add(PreferAsyncExtension::create())
            ->add($payloadExtension)
        ;
        $this->service = WebPush::create($client, $requestFactory, $this->extensionManager);
    }

    public static function create(ClientInterface $client, RequestFactoryInterface $requestFactory): self
    {
        return new self($client, $requestFactory);
    }

    public function enableVapid(string $subject, string $publicKey, string $privateKey): self
    {
        $jwsProvider = $this->getJwsProvider($publicKey, $privateKey);
        Assertion::false($this->vapidEnabled, 'VAPID has already been enabled');
        $this->extensionManager->add(
            VAPIDExtension::create($subject, $jwsProvider)
        );
        $this->vapidEnabled = true;

        return $this;
    }

    public function send(Notification $notification, Subscription $subscription): StatusReport
    {
        return $this->service->send($notification, $subscription);
    }

    private function getJwsProvider(string $publicKey, string $privateKey): JWSProvider
    {
        switch (true) {
            case class_exists(ES256::class):
                return WebTokenProvider::create($publicKey, $privateKey);
            case class_exists(Sha256::class):
                return LcobucciProvider::create($publicKey, $privateKey);
            default:
                throw new InvalidArgumentException('Please install "web-token/jwt-signature-algorithm-ecdsa" or "lcobucci/jwt" to use this feature');
        }
    }
}
