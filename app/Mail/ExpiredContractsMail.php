<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredContractsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expiredContracts;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($expiredContracts)
    {
        $this->expiredContracts = $expiredContracts;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.expired_contracts')
                    ->with('expiredContracts', $this->expiredContracts);
    }
}

