<?php

declare(strict_types=1);

namespace WebPush\Tests\Library\Functional;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WebPush\ExtensionManager;
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

/**
 * @internal
 */
final class WebPushTest extends TestCase
{
    #[Test]
    public function aNotificationCanBeSentWithAESGCMEncoding(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aesgcm'])
        ;
        $subscription->setKey('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->setKey(
            'p256dh',
            'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU'
        );

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('Topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new MockHttpClient();
        $client->setResponseFactory(function (string $method, string $url, array $options = []) {
            $this->assertContains('TTL: 3600', $options['headers']);
            $this->assertContains('Urgency: high', $options['headers']);
            $this->assertContains('Topic: Topic', $options['headers']);
            $this->assertContains('Content-Type: application/octet-stream', $options['headers']);
            $this->assertContains('Content-Encoding: aesgcm', $options['headers']);
            $this->assertContains('Content-Length: 4096', $options['headers']);

            return new MockResponse('OK', [
                'http_code' => 201,
            ]);
        });
        $service = $this->getService($client);

        $service->send($notification, $subscription);
        static::assertSame(1, $client->getRequestsCount());
    }

    #[Test]
    public function aNotificationCanBeSentWithAES128GCMEncoding(): void
    {
        $subscription = Subscription::create('https://foo.bar')
            ->withContentEncodings(['aes128gcm'])
        ;
        $subscription->setKey('auth', 'wSfP1pfACMwFesCEfJx4-w');
        $subscription->setKey(
            'p256dh',
            'BIlDpD05YLrVPXfANOKOCNSlTvjpb5vdFo-1e0jNcbGlFrP49LyOjYyIIAZIVCDAHEcX-135b859bdsse-PgosU'
        );

        $notification = Notification::create()
            ->sync()
            ->highUrgency()
            ->withTopic('Topic')
            ->withPayload('Hello World')
            ->withTTL(3600)
        ;

        $client = new MockHttpClient();
        $client->setResponseFactory(function (string $method, string $url, array $options = []) {
            $this->assertContains('TTL: 3600', $options['headers']);
            $this->assertContains('Urgency: high', $options['headers']);
            $this->assertContains('Topic: Topic', $options['headers']);
            $this->assertContains('Content-Type: application/octet-stream', $options['headers']);
            $this->assertContains('Content-Encoding: aes128gcm', $options['headers']);
            $this->assertContains('Content-Length: 4095', $options['headers']);

            return new MockResponse('OK', [
                'http_code' => 201,
            ]);
        });
        $service = $this->getService($client);

        $service->send($notification, $subscription);
        static::assertSame(1, $client->getRequestsCount());
    }

    private function getService(HttpClientInterface $client): WebPush
    {
        $extensionManager = ExtensionManager::create()
            ->add(TTLExtension::create())
            ->add(UrgencyExtension::create())
            ->add(TopicExtension::create())
            ->add(PreferAsyncExtension::create())
            ->add(
                PayloadExtension::create()
                    ->addContentEncoding(AESGCM::create(new NativeClock())->maxPadding())
                    ->addContentEncoding(AES128GCM::create(new NativeClock())->maxPadding())
            )
            ->add(VAPIDExtension::create(
                'http://localhost:8000',
                WebTokenProvider::create(
                    'BB4W1qfBi7MF_Lnrc6i2oL-glAuKF4kevy9T0k2vyKV4qvuBrN3T6o9-7-NR3mKHwzDXzD3fe7XvIqIU1iADpGQ',
                    'C40jLFSa5UWxstkFvdwzT3eHONE2FIJSEsVIncSCAqU'
                ),
                new NativeClock()
            ))
        ;

        return WebPush::create($client, $extensionManager);
    }
}
