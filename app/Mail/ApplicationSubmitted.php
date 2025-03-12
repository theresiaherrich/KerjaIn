<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ApplicationSubmitted extends Mailable
{
    public $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function build()
{
    return $this->subject('Lamaran Kerja Berhasil Dikirim')
                ->html('<p>Lamaran kerja Anda telah berhasil dikirim. Kami akan segera meninjaunya.</p>')
                ->with(['application' => $this->application]);
}

}
