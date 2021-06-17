<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Modules\MPS\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SaleCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $sale;

    public function __construct(Sale $sale, $attach = true)
    {
        $this->sale   = $sale;
        $this->attach = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'sale', 'hash' => $this->sale->hash]);

        $mail = (new MailMessage)
            ->greeting(__('mps::mail.hello', ['name' => $this->sale->customer->name]))
            ->subject(__('mps::mail.sale_created_subject'))
            ->line(__('mps::mail.sale_opening_line', ['reference' => $this->sale->reference]))
            ->action(__('mps::mail.sale_button_text'), $url)
            ->line(__('mps::mail.sale_closing_line'));

        if ($this->attach) {
            $pdf = $this->pdf();
            if ($pdf) {
                $mail->attachData(File::get($pdf), 'Sale.pdf', [
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
            $file  = storage_path('app/pdfs/' . $this->sale->id . '.pdf');
            Pdf::save(url($route . '#/views/sale/' . $this->sale->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->sale->id);
        }
        return $file;
    }
}
