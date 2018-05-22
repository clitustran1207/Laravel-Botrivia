<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BotHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //the messaging instance sent to our bothandler
    protected $messaging;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->messaging->getType() == "message") {
            $bot = new Bot($this->messaging);
            $custom = $bot->extractDataFromMessage();
            //a request for a new question
            if ($custom["type"] == Trivia::$NEW_QUESTION) {
                $bot->reply(Trivia::getNew($custom['user_id']));
            } else if ($custom["type"] == Trivia::$ANSWER) {
                $bot->reply(Trivia::checkAnswer($custom["data"]["answer"], $custom['user_id']));
            } else {
                $bot->reply("I don't understand. Try \"new\" for a new question");
            }
        }
    }
}
