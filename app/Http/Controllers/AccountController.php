<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAddressRequest;
use App\Http\Requests\Account\UpdateAddressRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $orders = $user
            ->orders()
            ->with('items')
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'orders' => $user->orders()->count(),

            'processing_orders' => $user
                ->orders()
                ->whereIn('status', [
                    'pending',
                    'processing',
                ])
                ->count(),

            'wishlist' => $user
                ->wishlists()
                ->count(),
        ];

        return view('account.index', [
            'user' => $user,
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function editProfile(): View
    {
        return view('account.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(
        UpdateProfileRequest $request
    ): RedirectResponse {
        $user = Auth::user();

        $user->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'اطلاعات حساب با موفقیت به‌روزرسانی شد.'
        );
    }

    public function addresses(): View
    {
        $addresses = Auth::user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return view('account.addresses.index', [
            'addresses' => $addresses,
        ]);
    }

    public function editAddress(Address $address): View
    {
        $this->authorize('update', $address);

        return view('account.addresses.edit', [
            'address' => $address,
        ]);
    }

    public function storeAddress(
        StoreAddressRequest $request
    ): RedirectResponse {
        $user = Auth::user();

        $data = $request->validated();

        DB::transaction(function () use ($user, &$data) {
            // Serialise default-address changes per customer to avoid two
            // concurrent requests leaving multiple default addresses behind.
            User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $mustBeDefault = (bool) ($data['is_default'] ?? false)
                || ! $user->addresses()->exists();

            if ($mustBeDefault) {
                $user->addresses()->update(['is_default' => false]);
                $data['is_default'] = true;
            }

            $user->addresses()->create($data);
        });

        return back()->with(
            'success',
            'آدرس با موفقیت ثبت شد.'
        );
    }

    public function updateAddress(
        UpdateAddressRequest $request,
        Address $address
    ): RedirectResponse {
        $this->authorize('update', $address);

        $data = $request->validated();

        $ownerId = (int) $address->user_id;

        DB::transaction(function () use ($address, &$data, $ownerId) {
            $user = User::query()
                ->whereKey($ownerId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = Address::query()
                ->whereKey($address->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['is_default'] ?? false) {
                $user->addresses()
                    ->whereKeyNot($lockedAddress->id)
                    ->update(['is_default' => false]);
            }

            $lockedAddress->update($data);
        });

        return back()->with(
            'success',
            'آدرس با موفقیت به‌روزرسانی شد.'
        );
    }

    public function deleteAddress(
        Address $address
    ): RedirectResponse {
        $this->authorize('delete', $address);

        $ownerId = (int) $address->user_id;

        DB::transaction(function () use ($address, $ownerId) {
            $user = User::query()
                ->whereKey($ownerId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = Address::query()
                ->whereKey($address->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $wasDefault = (bool) $lockedAddress->is_default;
            $lockedAddress->delete();

            if ($wasDefault) {
                $user->addresses()
                    ->latest('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });

        return back()->with(
            'success',
            'آدرس حذف شد.'
        );
    }

    public function setDefaultAddress(
        Address $address
    ): RedirectResponse {
        $this->authorize('update', $address);

        $ownerId = (int) $address->user_id;

        DB::transaction(function () use ($address, $ownerId) {
            $user = User::query()
                ->whereKey($ownerId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = Address::query()
                ->whereKey($address->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->addresses()
                ->whereKeyNot($lockedAddress->id)
                ->update(['is_default' => false]);

            $lockedAddress->update(['is_default' => true]);
        });

        return back()->with(
            'success',
            'آدرس پیش‌فرض تغییر کرد.'
        );
    }
}
