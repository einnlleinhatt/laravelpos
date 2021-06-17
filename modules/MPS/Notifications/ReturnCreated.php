<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Modules\MPS\Models\ReturnOrder;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReturnCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $return_order;

    public function __construct(ReturnOrder $return_order, $attach = true)
    {
        $this->return_order = $return_order;
        $this->attach       = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('return', ['act' => 'return_order', 'hash' => $this->return_order->hash]);

        $mail = (new MailMessage)
            ->greeting(__('mps::mail.hello', ['name' => $this->return_order->customer->name]))
            ->subject(__('mps::mail.return_order_created_subject'))
            ->line(__('mps::mail.return_order_opening_line', ['reference' => $this->return_order->reference]))
            ->action(__('mps::mail.return_order_button_text'), $url)
            ->line(__('mps::mail.return_order_closing_line'));

        if ($this->attach) {
            $pdf = $this->pdf();
            if ($pdf) {
                $mail->attachData(File::get($pdf), 'Return-Order.pdf', [
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
            $file  = storage_path('app/pdfs/' . $this->return_order->id . '.pdf');
            Pdf::save(url($route . '#/views/return_order/' . $this->return_order->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->return_order->id);
        }
        return $file;
    }
}
