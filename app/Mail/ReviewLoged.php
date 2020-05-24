<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReviewLoged extends Mailable
{
    use Queueable, SerializesModels;

    protected $userId;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('[MailTest]用戶 #'.$this->userId.' 的測試')
            ->view('emails.review')
            ->with([
                'userId' => $this->userId
            ]);
    }
}
