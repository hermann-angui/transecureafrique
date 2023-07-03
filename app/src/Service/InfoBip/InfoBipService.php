<?php

namespace App\Service\InfoBip;

use Infobip\Api\SmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Throwable;

/**
 *
 */
class InfoBipService
{
    const BASE_URL = "https://gym46r.api.infobip.com";
    const API_KEY = "1e5f1a99cc26cdd96dedbfb0cd00638c-22f16343-a599-4959-8b4f-785b0746936f";
    const SENDER = "TranSecure";

    /**
     * @param $message
     * @param $recipient
     * @return array|null
     */
    public function sendMessageTo(?string $message, ?string $recipient): ?array{

        $configuration = new Configuration(host: InfoBipService::BASE_URL, apiKey: InfoBipService::API_KEY);

        $sendSmsApi = new SmsApi(config: $configuration);

        $destination = new SmsDestination(
            to: '+225' . $recipient
        );

        $message = new SmsTextualMessage(destinations: [$destination], from: InfoBipService::SENDER, text: $message);

        $request = new SmsAdvancedTextualRequest(messages: [$message]);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);

            $response["bulkId"] = $smsResponse->getBulkId();

            foreach ($smsResponse->getMessages() ?? [] as $message) {
                $response ["messageId"] = $message->getMessageId();
                $response ["status"] = $message->getStatus()?->getName();
            }

            return $response;

        } catch (Throwable $apiException) {
            echo("HTTP Code: " . $apiException->getCode() . "\n");
            return null;
        }
    }

}
