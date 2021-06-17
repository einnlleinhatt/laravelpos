<?php

namespace Tecdiary\Installer\Commands;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use Modules\MPS\Models\User;
use Illuminate\Console\Command;
use Modules\MPS\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\MPS\Notifications\AppNotification;

class UpdateMPS extends Command
{
    public $hidden = true;

    protected $description = 'Update module (MPS) and packages';

    protected $signature = 'update:mps';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(1200);
        return $this->update();
    }

    private function notifySuperAdmins(array $data)
    {
        $users = User::role('super')->get();
        if ($users && $users->isNotEmpty()) {
            foreach ($users as $user) {
                $user->notify(new AppNotification($data));
            }
        }
    }

    private function update()
    {
        $now  = now()->startOfDay();
        $time = mps_config('auto_update_time', true);
        $next = isset($time->checked_at) ? Carbon::parse($time->checked_at)->addDays(6)->startOfDay() : now()->subDay()->startOfDay();
        if ($now->greaterThan($next)) {
            $keys = json_decode(Storage::get('keys.json'), true);
            if ($keys['mps']) {
                Artisan::call('down');
                $time->checked_at = $next->toDateString();
                $this->line('Checking if update available, please wait...');
                try {
                    $comp = json_decode(file_get_contents(base_path('composer.json')), true);
                    $resp = Http::withHeaders(['Accept' => 'application/json'])
                    ->get('http://be.tecdiary.net/api/v1/updates/check', ['ver' => $comp['version'], 'key' => $keys['mps'], 'dom' => url('/')]);
                    // ->get('https://19a480b3c62a.ngrok.io/api/v1/updates/check', ['ver' => '1.0.5', 'key' => $keys['mps'], 'dom' => url('/')]);

                    $data = $resp->json();
                    if ($resp->successful() && $data && $data['success']) {
                        if (!empty($data['updates'])) {
                            $this->line('Update available, installing now...');
                            foreach ($resp['updates'] as $update) {
                                try {
                                    $this->line('Updating to version ' . $update['version'] . ', please wait...');
                                    $path = Storage::disk('local')->putFileAs('updates', $update['url'], $update['filename']);
                                    if (Storage::disk('local')->exists($path)) {
                                        $filepath = Storage::disk('local')->path($path);
                                        try {
                                            $zip = new ZipArchive;
                                            if ($zip->open($filepath) === true) {
                                                $zip->extractTo(base_path());
                                                $zip->close();
                                                Storage::disk('local')->delete($path);
                                                $this->info('Updated to version ' . $update['version']);
                                            } else {
                                                $this->error('Failed to extract the update file ' . $path);
                                            }
                                        } catch (Exception $e) {
                                            $this->error($e->getMessage());
                                            $this->notifySuperAdmins([
                                                'title'       => 'Application Update Failed!',
                                                'description' => 'Application failed to extract the download file',
                                            ]);
                                            Artisan::call('up');
                                        }
                                    } else {
                                        $this->error('Failed to copy the update file ' . $path);
                                        $this->notifySuperAdmins([
                                            'title'       => 'Application Update Failed!',
                                            'description' => 'Failed to copy the update file',
                                        ]);
                                    }
                                } catch (Exception $e) {
                                    $this->error($e->getMessage());
                                    $this->line('Exiting the update...');
                                    $this->line('Please try again and if still same, contact developer. Thank you');
                                    $this->notifySuperAdmins([
                                        'title'       => 'Application Update Failed!',
                                        'description' => 'Application update has been failed with error ' . $e->getMessage(),
                                    ]);
                                    Artisan::call('up');
                                    exit();
                                }
                            }
                            $this->line('Running migrations now...');
                            Artisan::call('migrate --force');
                            $this->line(Artisan::output());
                            $this->line('Updating the packages now, this could take few minutes. Please wait...');
                            Artisan::call('composer:update');
                            Setting::updateOrCreate(['mps_key' => 'auto_update_time'], ['mps_value' => json_encode($time)]);
                            $this->info('Update completed! you are using the latest version now.');
                            $this->notifySuperAdmins([
                                'title'       => 'Application Updated',
                                'description' => 'Application has been updated to latest version ' . ($update['version'] ?? ''),
                            ]);
                        } else {
                            $this->info($data['message'] ?? 'You are using the latest version.');
                        }
                    } else {
                        Setting::updateOrCreate(['mps_key' => 'auto_update_time'], ['mps_value' => json_encode($time)]);
                        if ($resp->status() == 422) {
                            Log::error($data['message'], ['errors' => $data['errors'] ?? []]);
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => $data['message'] . ' Please contact developer. Thank you',
                            ]);
                            $this->error($data['message'] . ' Please contact developer. Thank you');
                        } elseif ($resp->status() == 429) {
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => 'Too many requests, please try again tomorrow. Thank you',
                            ]);
                            $this->error('Too many requests, please try again tomorrow. Thank you');
                        } else {
                            Log::error('The update check request has been failed.');
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => 'The update check request has been failed.',
                            ]);
                            $this->error('The update check request has been failed.');
                        }
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    $this->line('Error ocurred!');
                    $this->line('Unable to connect to update server.');
                    $this->notifySuperAdmins([
                        'title'       => 'Application Update Failed!',
                        'description' => 'Unable to connect to update server.',
                    ]);
                    Artisan::call('up');
                    exit();
                }
            } else {
                $this->error('Application key is not set, please contact developer with your license key. Thank you');
            }
            Artisan::call('up');
        }
    }
}
