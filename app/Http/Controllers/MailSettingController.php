<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use App\Support\MailConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailSettingController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->can('config-mail'), 403);

        return view('master-data.config-mail.edit', [
            'mailSetting' => MailSetting::query()->latest('id')->first(),
            'mailConfigured' => MailConfiguration::isConfigured(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('config-mail'), 403);

        $validated = $request->validate([
            'mailer' => ['required', 'string', 'max:50'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'encryption' => ['nullable', 'string', 'max:20'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $mailSetting = MailSetting::query()->latest('id')->first();

        if ($mailSetting) {
            $mailSetting->update($validated);
        } else {
            MailSetting::query()->create($validated);
        }

        return redirect()
            ->route('master-data.config-mail.edit')
            ->with('success', 'Config mail berhasil diperbarui.');
    }
}
