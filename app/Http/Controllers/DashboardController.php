<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Support\ApplicationCatalog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $applications = $request->user()
            ->applications()
            ->with([
                'interviews' => fn ($query) => $query->latest('scheduled_at'),
                'discordNotifications' => fn ($query) => $query->latest()->limit(5),
            ])
            ->latest()
            ->get();

        $cooldownApplication = $applications
            ->filter(fn ($application) => $application->cooldown_until && $application->cooldown_until->isFuture())
            ->sortByDesc('cooldown_until')
            ->first();

        return view('dashboard', [
            'applications' => $applications,
            'types' => ApplicationCatalog::types(),
            'activeCount' => $applications->filter(fn ($application) => $application->status->isActive())->count(),
            'acceptedCount' => $applications->where('status', ApplicationStatus::Accepted)->count(),
            'cooldownApplication' => $cooldownApplication,
            'nextInterview' => $applications
                ->flatMap(fn ($application) => $application->interviews)
                ->where('status', \App\Models\ApplicationInterview::STATUS_SCHEDULED)
                ->filter(fn ($interview) => $interview->scheduled_at && $interview->scheduled_at->isFuture())
                ->sortBy('scheduled_at')
                ->first(),
        ]);
    }
}
