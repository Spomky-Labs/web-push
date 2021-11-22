<?php

declare(strict_types=1);

namespace WebPush\Tests\Benchmark;

use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use WebPush\ExtensionManager;
use WebPush\Notification;
use WebPush\Payload\AES128GCM;
use WebPush\Payload\AESGCM;
use WebPush\Payload\PayloadExtension;
use WebPush\Subscription;
use WebPush\TopicExtension;
use WebPush\TTLExtension;
use WebPush\UrgencyExtension;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\VAPIDExtension;
use WebPush\WebPush;

/**
 * @BeforeMethods({"init"})
 * @Revs(4096)
 */
abstract class AbstractBench
{
    private WebPush $webPush;

    private WebPush $webPushWithCache;

    private Subscription $subscription;

    public function init(): void
    {
        $client = new Client();
        $psr17Factory = new Psr17Factory();

        $jwsProvider = $this->jwtProvider();
        $vapidExtension = VAPIDExtension::create('mailto:foo@bar.com', $jwsProvider);

        $payloadExtension = PayloadExtension::create()
            ->addContentEncoding(AES128GCM::create()->maxPadding())
            ->addContentEncoding(AESGCM::create()->maxPadding())
        ;

        $aes128gcm = AES128GCM::create()
            ->setCache(new FilesystemAdapter())
            ->maxPadding()
        ;
        $aesgcm = AESGCM::create()
            ->setCache(new FilesystemAdapter())
            ->maxPadding()
        ;

        $payloadExtensionWithCache = PayloadExtension::create()
            ->addContentEncoding($aes128gcm)
            ->addContentEncoding($aesgcm)
        ;

        $extensionManager = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->add(TopicExtension::create())
            ->add(UrgencyExtension::create())
            ->add($vapidExtension)
            ->add($payloadExtension)
        ;

        $extensionManagerWithCache = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->add(TopicExtension::create())
            ->add(UrgencyExtension::create())
            ->add($vapidExtension)
            ->add($payloadExtensionWithCache)
        ;

        $this->webPush = WebPush::create($client, $psr17Factory, $extensionManager);
        $this->webPushWithCache = WebPush::create($client, $psr17Factory, $extensionManagerWithCache);

        $this->subscription = Subscription::createFromString(
            '{"endpoint":"https://updates.push.services.mozilla.com/wpush/v2/gAAAAABfcsdu1p9BdbYIByt9F76MHcSiuix-ZIiICzAkU9z_p0gnolYLMOi71rqss5pMOZuYJVZLa7rRN58uOgfdsux7k51Ph6KJRFEkf1LqTRMv2d8OhQaL2TR36WUR2d5twzYVwcQJAnTLrhVrWqKVo8ekAonuwyFHDUGzD8oUWpFTK9y2F68","keys":{"auth":"wSfP1pfACMwFesCEfJx4-w","p256dh":"BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU"},"contentEncoding":"aes128gcm"}'
        );
    }

    /**
     * @Subject
     */
    public function sendNotificationWithoutPayload(): void
    {
        $notification = Notification::create();
        $this->webPush->send($notification, $this->subscription);
    }

    /**
     * @Subject
     */
    public function sendNotificationWithoutPayloadWithCache(): void
    {
        $notification = Notification::create();
        $this->webPushWithCache->send($notification, $this->subscription);
    }

    /**
     * @Subject
     */
    public function sendNotificationWithPayload(): void
    {
        $notification = Notification::create()
            ->withPayload(
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas nisi justo, cursus sed fringilla at, mollis ac velit. Duis vulputate libero eget luctus posuere. Nam in ex turpis. Nullam commodo elit tortor. Phasellus ipsum sapien, venenatis non tellus et, ullamcorper faucibus felis. Nullam quis eleifend diam, ut tincidunt nibh. Ut massa lectus, imperdiet a mollis sed, tempor a arcu. Nulla facilisi.'
            )
        ;
        $this->webPush->send($notification, $this->subscription);
    }

    /**
     * @Subject
     */
    public function sendNotificationWithPayloadWithCache(): void
    {
        $notification = Notification::create()
            ->withPayload(
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas nisi justo, cursus sed fringilla at, mollis ac velit. Duis vulputate libero eget luctus posuere. Nam in ex turpis. Nullam commodo elit tortor. Phasellus ipsum sapien, venenatis non tellus et, ullamcorper faucibus felis. Nullam quis eleifend diam, ut tincidunt nibh. Ut massa lectus, imperdiet a mollis sed, tempor a arcu. Nulla facilisi.'
            )
        ;
        $this->webPushWithCache->send($notification, $this->subscription);
    }

    abstract protected function jwtProvider(): JWSProvider;
}
