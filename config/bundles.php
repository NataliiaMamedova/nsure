<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sentry\SentryBundle\SentryBundle::class => ['prod' => true, 'staging' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    PayBis\HealthCheckBundle\HealthCheckBundle::class => ['all' => true],
    Aws\Symfony\AwsBundle::class => ['all' => true],
    Bref\Symfony\Messenger\BrefMessengerBundle::class => ['all' => true],
    Http\HttplugBundle\HttplugBundle::class => ['all' => true],
    PayBis\XrayIntegration\XrayIntegrationBundle::class => ['all' => true],
];
