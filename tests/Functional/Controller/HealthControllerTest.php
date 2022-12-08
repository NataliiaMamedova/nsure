<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class HealthControllerTest extends WebTestCase
{
    public function testHealth(): void
    {
        $client = self::createClient();
        $client->request('GET', '/public/nsure-service/health');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
