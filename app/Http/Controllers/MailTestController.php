<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MailTestController extends Controller
{
    /**
     * @var array<string, array{mailer: string, from: string, label: string}>
     */
    private const CHANNELS = [
        'noreply' => [
            'mailer' => 'smtp',
            'from' => 'noreply',
            'label' => 'Auth / noreply mailbox',
        ],
        'info' => [
            'mailer' => 'support',
            'from' => 'info',
            'label' => 'Info alias (via support mailbox)',
        ],
        'admin' => [
            'mailer' => 'support',
            'from' => 'admin',
            'label' => 'Admin alias (via support mailbox)',
        ],
        'support' => [
            'mailer' => 'support',
            'from' => 'support',
            'label' => 'Support mailbox',
        ],
    ];

    public function __invoke(Request $request, string $channel): JsonResponse
    {
        if (! isset(self::CHANNELS[$channel])) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown channel. Use: '.implode(', ', array_keys(self::CHANNELS)),
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'to' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Query param "to" must be a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $to = (string) $request->query('to');
        $config = self::CHANNELS[$channel];
        $fromAddress = (string) config('mail.addresses.'.$config['from']);
        $fromName = (string) config('mail.from.name');

        try {
            Mail::mailer($config['mailer'])->raw(
                "AutoServe mail test\n\n"
                ."Channel: {$channel}\n"
                ."Label: {$config['label']}\n"
                ."From: {$fromAddress}\n"
                ."Mailer: {$config['mailer']}\n"
                .'Sent at: '.now()->toIso8601String()."\n",
                function ($message) use ($to, $fromAddress, $fromName, $channel): void {
                    $message->to($to)
                        ->from($fromAddress, $fromName)
                        ->subject("AutoServe test email — {$channel}");
                }
            );
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'channel' => $channel,
                'from' => $fromAddress,
                'mailer' => $config['mailer'],
                'to' => $to,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'channel' => $channel,
            'label' => $config['label'],
            'from' => $fromAddress,
            'mailer' => $config['mailer'],
            'to' => $to,
            'message' => 'Test email sent.',
        ]);
    }
}
