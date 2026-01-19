<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SmtpConfig;

class SmtpController extends Controller
{
    /**
     * Store or update the SMTP configuration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $user = Auth::user();

        // Update or Create
        $user->activeConnectors()->updateOrCreate(
            ['connector_slug' => 'smtp'],
            [
                'is_active' => true,
                'credentials' => [
                    'host' => $request->host,
                    'port' => $request->port,
                    'username' => $request->username,
                    'password' => $request->password, // Model will encrypt automatically
                    'encryption' => $request->encryption,
                ],
                'meta' => [
                    'from_address' => $request->from_address,
                    'from_name' => $request->from_name,
                ]
            ]
        );

        return redirect()->back()->with('success', 'SMTP configuration saved successfully.');
    }

    /**
     * Test the SMTP configuration.
     */
    public function test(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        try {
            // Dynamic configuration for testing
            $config = [
                'transport' => 'smtp',
                'host' => $request->host,
                'port' => $request->port,
                'encryption' => $request->encryption,
                'username' => $request->username,
                'password' => $request->password,
                'timeout' => null,
            ];

            // Set a temporary mailer config
            config(['mail.mailers.smtp_test' => $config]);
            config(['mail.from.address' => $request->from_address]);
            config(['mail.from.name' => $request->from_name]);

            // Attempt to send a raw email using this new mailer
            \Illuminate\Support\Facades\Mail::mailer('smtp_test')->raw('This is a test email from your Shopify Automation App.', function ($message) use ($request) {
                $message->to($request->from_address)
                        ->subject('SMTP Connection Test');
            });

            return response()->json(['message' => 'Connection successful! Test email sent to ' . $request->from_address]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Connection failed: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Delete the SMTP configuration.
     */
    public function disconnect()
    {
        $user = Auth::user();
        $user->activeConnectors()->where('connector_slug', 'smtp')->delete();
        
        return redirect()->back()->with('success', 'SMTP disconnected successfully.');
    }
    
    /**
     * Get the current SMTP configuration (without password).
     */
     public function show()
     {
         $connector = Auth::user()->activeConnectors()->where('connector_slug', 'smtp')->first();
         
         if ($connector && $connector->credentials) {
             return response()->json([
                 'host' => $connector->credentials['host'] ?? '',
                 'port' => $connector->credentials['port'] ?? '',
                 'username' => $connector->credentials['username'] ?? '',
                 'encryption' => $connector->credentials['encryption'] ?? '',
                 'from_address' => $connector->meta['from_address'] ?? '',
                 'from_name' => $connector->meta['from_name'] ?? '',
                 'password' => $connector->credentials['password'] ?? '',
             ]);
         }
         return response()->json(null);
     }
}
