<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Quotation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QuotationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $quotation;

    public function __construct(Quotation $quotation, $attach = true)
    {
        $this->quotation = $quotation;
        $this->attach    = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'quotation', 'hash' => $this->quotation->hash]);

        $mail = (new MailMessage)
            ->greeting(__('mps::mail.hello', ['name' => $this->quotation->customer->name]))
            ->subject(__('mps::mail.quotation_created_subject'))
            ->line(__('mps::mail.quotation_opening_line', ['reference' => $this->quotation->reference]))
            ->action(__('mps::mail.quotation_button_text'), $url)
            ->line(__('mps::mail.quotation_closing_line'));

        if ($this->attach) {
            $pdf = $this->pdf();
            if ($pdf) {
                $mail->attachData(File::get($pdf), 'Quotation.pdf', [
                    'mime' => 'application/pdf',
                ]);
                File::delete($pdf);
            }
        }

        return $mail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    private function pdf()
    {
        try {
            $route = module('route');
            $file  = storage_path('app/pdfs/' . $this->quotation->id . '.pdf');
            Pdf::save(url($route . '#/views/quotation/' . $this->quotation->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->quotation->id);
        }
        return $file;
    }
}
