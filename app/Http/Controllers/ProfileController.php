<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Card;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function telegram(Request $request): View
    {
        $user = $request->user();
        $categories = Category::where('user_id', $user->id)->get();
        $cards = Card::where('user_id', $user->id)->get();

        return view('profile.telegram', compact('user', 'categories', 'cards'));
    }

    public function updateTelegram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_enabled' => 'boolean',
            'telegram_chat_id' => 'nullable|string',
            'telegram_default_category_id' => 'nullable|exists:categories,id',
            'telegram_default_card_id' => 'nullable|exists:cards,id',
        ]);

        $user = $request->user();

        $user->update([
            'telegram_enabled' => $validated['telegram_enabled'] ?? false,
            'telegram_chat_id' => $validated['telegram_chat_id'] ?: null,
            'telegram_default_category_id' => $validated['telegram_default_category_id'] ?: null,
            'telegram_default_card_id' => $validated['telegram_default_card_id'] ?: null,
        ]);

        return Redirect::route('profile.telegram')->with('status', 'telegram-updated');
    }
}
