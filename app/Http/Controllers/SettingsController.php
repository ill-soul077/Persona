<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'timezone' => 'nullable|string',
            'language' => 'nullable|string',
            'date_format' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update security settings.
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required_with:new_password',
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
            'two_factor_enabled' => 'boolean',
        ]);

        // Verify current password if new password is provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        if ($request->has('two_factor_enabled')) {
            $user->two_factor_enabled = $request->boolean('two_factor_enabled');
        }

        $user->save();

        return redirect()->back()->with('success', 'Security settings updated successfully!');
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'email_notifications' => $request->boolean('email_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'task_reminders' => $request->boolean('task_reminders'),
            'transaction_alerts' => $request->boolean('transaction_alerts'),
        ]);

        return redirect()->back()->with('success', 'Notification preferences updated!');
    }

    /**
     * Connect an external app.
     */
    public function connectApp(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'app_name' => 'required|string',
        ]);

        $connectedApps = $user->connected_apps ?? [];
        
        if (!in_array($validated['app_name'], $connectedApps)) {
            $connectedApps[] = [
                'name' => $validated['app_name'],
                'connected_at' => now()->toDateTimeString(),
            ];
            
            $user->connected_apps = $connectedApps;
            $user->save();
        }

        return redirect()->back()->with('success', ucfirst($validated['app_name']) . ' connected successfully!');
    }

    /**
     * Disconnect an external app.
     */
    public function disconnectApp(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'app_name' => 'required|string',
        ]);

        $connectedApps = $user->connected_apps ?? [];
        
        $connectedApps = array_filter($connectedApps, function($app) use ($validated) {
            return $app['name'] !== $validated['app_name'];
        });
        
        $user->connected_apps = array_values($connectedApps);
        $user->save();

        return redirect()->back()->with('success', ucfirst($validated['app_name']) . ' disconnected successfully!');
    }

    /**
     * Export user data.
     */
    public function exportData()
    {
        $user = Auth::user();
        
        $data = [
            'profile' => $user->only(['name', 'email', 'phone', 'bio', 'created_at']),
            'transactions' => $user->transactions()->get(),
            'tasks' => $user->tasks()->get(),
        ];

        $filename = 'persona_data_' . date('Y-m-d') . '.json';
        
        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
