<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use Illuminate\Http\Request;

class SmtpSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = SmtpSetting::all();
        return view('smtp.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('smtp.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mailer' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        // deactivate previous if active
        if ($request->has('is_active')) {
            SmtpSetting::query()->update(['is_active' => false]);
            $validated['is_active'] = true;
        }

        SmtpSetting::create($validated);

        return redirect()->route('smtp.index')->with('success', 'SMTP server saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmtpSetting $smtp)
    {
        return view('smtp.edit', compact('smtp'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SmtpSetting $smtp)
    {
        $validated = $request->validate([
            'mailer' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        if ($request->has('is_active')) {
            SmtpSetting::query()->update(['is_active' => false]);
            $validated['is_active'] = true;
        }

        $smtp->update($validated);

        return redirect()->route('smtp.index')->with('success', 'SMTP server updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmtpSetting $smtpSetting)
    {
        $smtpSetting->delete();
        return back()->with('success', 'SMTP server deleted.');
    }
}
