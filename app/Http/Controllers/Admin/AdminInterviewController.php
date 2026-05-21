<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationInterview;
use Illuminate\View\View;

class AdminInterviewController extends Controller
{
    public function index(): View
    {
        $upcoming = ApplicationInterview::query()
            ->with(['application.user', 'interviewer'])
            ->where('status', ApplicationInterview::STATUS_SCHEDULED)
            ->orderByRaw('scheduled_at IS NULL')
            ->orderBy('scheduled_at')
            ->limit(20)
            ->get();

        $completed = ApplicationInterview::query()
            ->with(['application.user', 'interviewer'])
            ->whereIn('status', [
                ApplicationInterview::STATUS_COMPLETED,
                ApplicationInterview::STATUS_CANCELLED,
            ])
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return view('admin.interviews.index', compact('upcoming', 'completed'));
    }
}
