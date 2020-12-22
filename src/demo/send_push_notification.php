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

require_once __DIR__.'/vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;
use WebPush\Action;
use WebPush\ExtensionManager;
use WebPush\Message;
use WebPush\Notification;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\PreferAsyncExtension;
use WebPush\Subscription;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\VAPID\VAPIDExtension;
use WebPush\VAPID\WebTokenProvider;
use WebPush\WebPush;

// PSR-17 Request Factory
$psr17Factory = new Psr17Factory();

// PSR-18 Client
$psr18Client = new Psr18Client();

// VAPID extension
$publicKey = 'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ';
$privateKey = 'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU';
$jwsProvider = WebTokenProvider::create($publicKey, $privateKey);
$vapidExtension = VAPIDExtension::create('http://localhost:9000', $jwsProvider);

// Payload extension
$payloadExtension = PayloadExtension::create()
    ->addContentEncoding(AESGCM::create())
    ->addContentEncoding(AES128GCM::create())
;

// All extension
$extensionManager = ExtensionManager::create()
    ->add(TTLExtension::create())
    ->add(TopicExtension::create())
    ->add(UrgencyExtension::create())
    ->add(PreferAsyncExtension::create())
    ->add($payloadExtension)
    ->add($vapidExtension)
;
$service = WebPush::create($psr18Client, $psr17Factory, $extensionManager);

$message = Message::create('Hello World!')
    ->withLang('en-GB')
    ->interactionRequired()
    ->withTimestamp(time())
    ->addAction(Action::create('accept', 'Accept'))
    ->addAction(Action::create('cancel', 'Cancel'))
;

$notification = Notification::create()->withPayload($message->toString());
$subscription = Subscription::createFromString(file_get_contents('php://input'));

$report = $service->send($notification, $subscription);

dump($report->getRequest()->getHeaders());
dump($report->getResponse()->getStatusCode());
dump($report->getResponse()->getHeaders());
dump($report->getResponse()->getBody()->getContents());
