<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\FirebaseService;
use App\Models\Certificate as DocumentRequest;

class NotifyAdminsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $requestId;

    public function __construct($requestId)
    {
        $this->requestId = $requestId;
    }

    public function handle()
    {
        $request = DocumentRequest::find($this->requestId);

        if (!$request) return;

        $admins = User::where('barangay', $request->barangay)
            ->where('role', 'bdrrmo_admin')
            ->get();

        $firebase = new FirebaseService();

        foreach ($admins as $admin) {

            if ($admin->web_fcm_token) {
                $firebase->sendDataOnlyNotification(
                    $admin->web_fcm_token,
                    [
                        'title' => 'New Certification Request',
                        'body' => "New request from {$request->full_name}",
                        'screen' => 'Requests',
                        'request_id' => (string) $request->id,
                        'url' => '/',
                    ]
                );
            }

            if ($admin->phone) {
                Http::withHeaders([
                    'X-API-KEY' => env('SMS_API_KEY')
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $admin->phone,
                    'message' => "[AlertoPH ALERT]\n{$request->full_name} requested {$request->document_type}"
                ]);
            }
        }
    }
}
