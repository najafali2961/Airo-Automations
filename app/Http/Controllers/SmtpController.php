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
        $config = $user->smtpConfig()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'host' => $request->host,
                'port' => $request->port,
                'username' => $request->username,
                'password' => $request->password, // Model will encrypt automatically
                'encryption' => $request->encryption,
                'from_address' => $request->from_address,
                'from_name' => $request->from_name,
            ]
        );

        return redirect()->back()->with('success', 'SMTP configuration saved successfully.');
    }

    /**
     * Delete the SMTP configuration.
     */
    public function disconnect()
    {
        $user = Auth::user();
        
        if ($user->smtpConfig) {
            $user->smtpConfig->delete();
        }

        return redirect()->back()->with('success', 'SMTP disconnected successfully.');
    }
    
    /**
     * Get the current SMTP configuration (without password).
     */
     public function show()
     {
         $config = Auth::user()->smtpConfig;
         if ($config) {
             return response()->json([
                 'host' => $config->host,
                 'port' => $config->port,
                 'username' => $config->username,
                 'encryption' => $config->encryption,
                 'from_address' => $config->from_address,
                 'from_name' => $config->from_name,
                 // Never return password
             ]);
         }
         return response()->json(null);
     }
}
