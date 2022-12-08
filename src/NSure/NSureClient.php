<?php

declare(strict_types=1);

namespace App\NSure;

use App\Exception\NSureClientException;
use App\Exception\NSureImplementationException;
use App\NSure\Request\EmailVerificationEventRequest;
use App\NSure\Request\MerchantFinalDecisionEventRequest;
use App\NSure\Request\NSureRequestInterface;
use App\NSure\Request\PaymentMethodEventRequest;
use App\NSure\Request\PhoneVerificationEventRequest;
use App\NSure\Request\RecipientUpdateEventRequest;
use App\NSure\Request\SignInEventRequest;
use App\NSure\Request\SignOutEventRequest;
use App\NSure\Request\SignUpEventRequest;
use App\NSure\Request\TxCancelEventRequest;
use App\NSure\Request\TxFailureEventRequest;
use App\NSure\Response\NSureResponseInterface;
use App\NSure\Response\SendEventResponse;
use PayBis\ApiClient\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class NSureClient extends Client
{
    private const EVENTS_ENDPOINT = '/events';

    private const METHOD_POST = 'POST';

    /**
     * @var DenormalizerInterface
     */
    protected $serializer;

    private LoggerInterface $logger;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        UriFactoryInterface $uriFactory,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        parent::__construct($client, $requestFactory, $streamFactory, $uriFactory, $serializer);

        $this->logger = $logger;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#phone-verification
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendEventPhoneVerification(PhoneVerificationEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#merchant-final-decision
     *
     * @throws NSureClientException
     * @throws NSureImplementationException
     */
    public function sendMerchantFinalDecision(MerchantFinalDecisionEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#email-verification
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendEventEmailVerification(EmailVerificationEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#user-signed-up
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendSignUpEvent(SignUpEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#order-transaction---failed
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendTxFailureEvent(TxFailureEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#credit-card-example
     *
     * @throws NSureClientException|NSureImplementationException
     */
    public function sendPaymentMethodEvent(PaymentMethodEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#user-signed-in
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendSignInEvent(SignInEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#recipient-update-event
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendRecipientUpdateEvent(RecipientUpdateEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#user-signed-out
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendSignOutEvent(SignOutEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples#order-transaction---canceled
     *
     * @throws NSureImplementationException|NSureClientException
     */
    public function sendTxCancelEvent(TxCancelEventRequest $request): SendEventResponse
    {
        return $this->sendEventRequest($request);
    }

    /**
     * @see https://docs.nsure.ai/docs/getting-started/ZG9jOjExODI3MjE4-events-endpoint-examples
     *
     * @throws NSureImplementationException|NSureClientException
     */
    private function sendEventRequest(NSureRequestInterface $request): SendEventResponse
    {
        return $this->doRequest(self::METHOD_POST, self::EVENTS_ENDPOINT, $request, SendEventResponse::class);
    }

    /**
     * @template T of NSureResponseInterface
     *
     * @param class-string<T> $responseClass
     *
     * @throws NSureImplementationException|NSureClientException
     *
     * @phpstan-return T
     */
    private function doRequest(
        string $method,
        string $path,
        ?NSureRequestInterface $request,
        string $responseClass
    ): NSureResponseInterface {
        if (! class_exists($responseClass) || ! isset(class_implements($responseClass)[NSureResponseInterface::class])) {/** @phpstan-ignore-line */
            throw new NSureImplementationException('Implementation exception', 'IMPLEMENTATION_EXCEPTION');
        }
        try {
            $httpRequest = $this->createRequest($method, $path);

            if (null !== $request) {
                $httpRequest = $httpRequest->withBody($this->streamFactory->createStream($request->makeBody()));
            }

            $response = $this->send($httpRequest);
        } catch (NSureClientException $e) {
            $this->logger->error('NSure client exception: {message}', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('NSure client request exception: {message}', [
                'message' => $e->getMessage(),
            ]);

            throw new NSureClientException('NSure client request exception: ' . $e->getMessage(), 'REQUEST_EXCEPTION', $e);
        }

        $decodedBody = $this->decodeResponse($response);

        try {
            return $this->serializer->denormalize($decodedBody, $responseClass, null, [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]);
        } catch (\Throwable $ex) {
            $this->logger->error('Can not denormalize response: {message}', [
                'message' => $ex->getMessage(),
                'class' => $responseClass,
            ]);
            throw NSureClientException::badResponse('Can not denormalize response: ' . $ex->getMessage(), $response->getStatusCode());
        }
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        if ('' === $content) {
            return [];
        }
        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Can not decode response body: {message}', [
                'message' => $e->getMessage(),
                'content' => $content,
            ]);
            throw NSureClientException::badResponse('Can not decode response body: ' . $e->getMessage(), $response->getStatusCode());
        }
    }
}
