<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePersonalDataRequest;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PersonalDataController extends Controller
{
    /**
     * Data diri (nama, nomor HP, alamat, foto). Email dan kata sandi dikelola oleh halaman pengaturan Fortify.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('account/personal-data', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => ProductPresenter::imageUrl($user->avatar),
            ],
        ]);
    }

    public function update(UpdatePersonalDataRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->only(['name', 'phone', 'address']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = null;
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Data diri diperbarui.')]);

        return back();
    }
}
