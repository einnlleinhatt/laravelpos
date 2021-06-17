<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $payment;

    public function __construct(Payment $payment, $attach = true)
    {
        $this->payment = $payment;
        $this->attach  = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'payment', 'hash' => $this->payment->hash]);

        $mail = (new MailMessage)
            ->success()
            ->greeting(__('mps::mail.hello', ['name' => $this->payment->payable->name]))
            ->subject(__('mps::mail.payment_received_subject'))
            ->line(__('mps::mail.payment_received_opening_line', ['reference' => $this->payment->reference]))
            ->action(__('mps::mail.payment_received_button_text'), $url)
            ->line(__('mps::mail.payment_received_closing_line'));

        if ($this->attach) {
            $pdf = $this->pdf();
            if ($pdf) {
                $mail->attachData(File::get($pdf), 'Payment.pdf', [
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
            $file  = storage_path('app/pdfs/' . $this->payment->id . '.pdf');
            Pdf::save(url($route . '#/views/payment/' . $this->payment->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->payment->id);
        }
        return $file;
    }
}
