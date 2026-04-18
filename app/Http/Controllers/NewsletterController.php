<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Store a new subscriber (public AJAX endpoint).
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        if (NewsletterSubscriber::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already subscribed.',
            ], 409);
        }

        NewsletterSubscriber::create([
            'email'         => $email,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscribed successfully!',
        ]);
    }

    /**
     * Return all subscribers (admin only).
     */
    public function index()
    {
        $subscribers = NewsletterSubscriber::latest()->get();
        return response()->json($subscribers);
    }

    /**
     * Delete a subscriber (admin only).
     */
    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();

        return response()->json(['success' => true]);
    }
}
