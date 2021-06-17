<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $purchase;

    public function __construct(Purchase $purchase, $attach = true)
    {
        $this->purchase = $purchase;
        $this->attach   = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'purchase', 'hash' => $this->purchase->hash]);

        $mail = (new MailMessage)
            ->greeting(__('mps::mail.hello', ['name' => $this->purchase->supplier->name]))
            ->subject(__('mps::mail.purchase_created_subject'))
            ->line(__('mps::mail.purchase_opening_line', ['reference' => $this->purchase->reference]))
            ->action(__('mps::mail.purchase_button_text'), $url)
            ->line(__('mps::mail.purchase_closing_line'));

        if ($this->attach) {
            $pdf = $this->pdf();
            if ($pdf) {
                $mail->attachData(File::get($pdf), 'Purchase.pdf', [
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
            $file  = storage_path('app/pdfs/' . $this->purchase->id . '.pdf');
            Pdf::save(url($route . '#/views/purchase/' . $this->purchase->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->purchase->id);
        }
        return $file;
    }
}
