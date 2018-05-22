<?php

namespace App\Bot;

use App\Bot\Webhook\Messaging;
use Illuminate\Support\Facades\Log;

class Bot {
    private $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function extractDataFromMessage()
    {
        $matches = [];
        $text = $this->messaging->getMessage()->getText();
        //single letter message means an answer
        if (preg_match("/^(\\w)\$/i", $text, $matches)) {
            return [
                "type" => Trivia::ANSWER,
                "data" => [
                    "answer" => $matches[0]
                ],
                "user_id" => $this->messaging->getSenderId()
        ];
        } else if (preg_match("/^new|next\$/i", $text, $matches)) {
            //"new" or "next" requests a new question
            return [
                "type" => Trivia::NEW_QUESTION,
                "data" => [],
                "user_id" => $this->messaging->getSenderId()
            ];
        }
        //anything else, we dont care
        return [
            "type" => "unknown",
            "data" => [],
            "user_id" => $this->messaging->getSenderId()
        ];
    }

    public function reply($data)
    {
        if (method_exists($data, "toMessage")) {
            $data = $data->toMessage();
        } else if (is_string($data)) {
            $data = ["text" => $data];
        }
        $id = $this->messaging->getSenderId();
        $this->sendMessage($id, $data);
    }

    private function sendMessage($recipientId, $message)
    {
        $messageData = [
            "recipient" => [
                "id" => $recipientId
            ],
            "message" => $message
        ];
        $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token=' . env("PAGE_ACCESS_TOKEN"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        Log::info(print_r(curl_exec($ch), true));
    }
}


