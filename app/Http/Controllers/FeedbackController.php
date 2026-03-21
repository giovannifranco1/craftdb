<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * List all contact/feedback submissions. Only super root (user id 1) can access.
     */
    public function index(): View
    {
        $feedback = Feedback::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pages.feedback.index', compact('feedback'));
    }
}
