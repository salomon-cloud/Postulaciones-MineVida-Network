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

        $scheduled = ApplicationInterview::query()->where('status', ApplicationInterview::STATUS_SCHEDULED);

        $stats = [
            'scheduled' => (clone $scheduled)->count(),
            'today' => (clone $scheduled)->whereDate('scheduled_at', today())->count(),
            'week' => (clone $scheduled)->whereBetween('scheduled_at', [now(), now()->addWeek()])->count(),
            'unassigned' => (clone $scheduled)->whereNull('interviewer_id')->count(),
            'completed' => ApplicationInterview::query()->where('status', ApplicationInterview::STATUS_COMPLETED)->count(),
            'cancelled' => ApplicationInterview::query()->where('status', ApplicationInterview::STATUS_CANCELLED)->count(),
        ];

        return view('admin.interviews.index', compact('upcoming', 'completed', 'stats'));
    }
}
