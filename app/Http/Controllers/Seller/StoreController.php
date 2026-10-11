<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\UpdateStoreRequest;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    /**
     * Profil toko. Nama toko, KTP, dan selfie tidak dapat diubah setelah disetujui.
     */
    public function edit(Request $request): Response
    {
        $seller = $request->user()->seller;

        return Inertia::render('seller/store', [
            'store' => [
                'name' => $seller->store_name,
                'slug' => $seller->slug,
                'description' => $seller->description,
                'phone' => $seller->phone,
                'address' => $seller->address,
                'logo' => ProductPresenter::imageUrl($seller->logo),
                'bank_name' => $seller->bank_name,
                'bank_account_number' => $seller->bank_account_number,
                'bank_account_name' => $seller->bank_account_name,
                'qrisUrl' => route('files.seller-document', ['seller' => $seller, 'type' => 'qris'], absolute: false),
            ],
        ]);
    }

    public function update(UpdateStoreRequest $request): RedirectResponse
    {
        $seller = $request->user()->seller;
        $data = $request->safe()->except(['logo', 'qris_image']);

        if ($request->hasFile('logo')) {
            if ($seller->logo) {
                Storage::disk('public')->delete($seller->logo);
            }

            $data['logo'] = $request->file('logo')->store('stores', 'public');
        }

        if ($request->hasFile('qris_image')) {
            Storage::disk('local')->delete($seller->qris_image);
            $data['qris_image'] = $request->file('qris_image')->store('sellers/qris', 'local');
        }

        $seller->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profil toko diperbarui.')]);

        return back();
    }
}
