<?php

namespace Utopia\Messaging\Adapter\Email;

use Utopia\Messaging\Adapter\Email as EmailAdapter;
use Utopia\Messaging\Messages\Email as EmailMessage;
use Utopia\Messaging\Response;

class Mailgun extends EmailAdapter
{
    protected const NAME = 'Mailgun';

    /**
     * @param  string  $apiKey Your Mailgun API key to authenticate with the API.
     * @param  string  $domain Your Mailgun domain to send messages from.
     */
    public function __construct(
        private string $apiKey,
        private string $domain,
        private bool $isEU = false
    ) {
    }

    /**
     * Get adapter name.
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * Get adapter description.
     */
    public function getMaxMessagesPerRequest(): int
    {
        return 1000;
    }

    /**
     * {@inheritdoc}
     */
    protected function process(EmailMessage $message): array
    {
        $usDomain = 'api.mailgun.net';
        $euDomain = 'api.eu.mailgun.net';

        $domain = $this->isEU ? $euDomain : $usDomain;

        $body = [
            'to' => \implode(',', $message->getTo()),
            'from' => "{$message->getFromName()}<{$message->getFromEmail()}>",
            'subject' => $message->getSubject(),
            'text' => $message->isHtml() ? null : $message->getContent(),
            'html' => $message->isHtml() ? $message->getContent() : null,
            'h:Reply-To: '."{$message->getReplyToName()}<{$message->getReplyToEmail()}>",
        ];

        if (!\is_null($message->getCC())) {
            foreach ($message->getCC() as $cc) {
                if (!empty($cc['name'])) {
                    $body['cc'] = "{$body['cc']},{$cc['name']}<{$cc['email']}>";
                } else {
                    $body['cc'] = "{$body['cc']}, <{$cc['email']}>";
                }
            }
        }

        if (!\is_null($message->getBCC())) {
            foreach ($message->getBCC() as $bcc) {
                if (!empty($bcc['name'])) {
                    $body['bcc'] = "{$body['bcc']},{$bcc['name']}<{$bcc['email']}>";
                } else {
                    $body['bcc'] = "{$body['bcc']}, <{$bcc['email']}>";
                }
            }
        }

        $isMultipart = false;

        if (!\is_null($message->getAttachments())) {
            $size = 0;

            foreach ($message->getAttachments() as $attachment) {
                $size += \filesize($attachment->getPath());
            }

            if ($size > self::MAX_ATTACHMENT_BYTES) {
                throw new \Exception('Attachments size exceeds the maximum allowed size of ');
            }

            foreach ($message->getAttachments() as $index => $attachment) {
                $isMultipart = true;

                $body["attachment[$index]"] = \curl_file_create(
                    $attachment->getPath(),
                    $attachment->getType(),
                    $attachment->getName(),
                );
            }
        }

        $response = new Response($this->getType());

        $headers = [
            'Authorization: Basic ' . \base64_encode("api:$this->apiKey"),
        ];

        if ($isMultipart) {
            $headers[] = 'Content-Type: multipart/form-data';
        } else {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $result = $this->request(
            method: 'POST',
            url: "https://$domain/v3/$this->domain/messages",
            headers: $headers,
            body: $body,
        );

        $statusCode = $result['statusCode'];

        if ($statusCode >= 200 && $statusCode < 300) {
            $response->setDeliveredTo(\count($message->getTo()));
            foreach ($message->getTo() as $to) {
                $response->addResult($to);
            }
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            foreach ($message->getTo() as $to) {
                if (\is_string($result['response'])) {
                    $response->addResult($to, $result['response']);
                } elseif (isset($result['response']['message'])) {
                    $response->addResult($to, $result['response']['message']);
                } else {
                    $response->addResult($to, 'Unknown error');
                }
            }
        }

        return $response->toArray();
    }
}
