<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Setting;
use App\Support\ApplicationCatalog;

class LandingController extends Controller
{
    public function __invoke()
    {
        $recentAccepted = collect();
        $categories = collect();
        $totalAccepted = 0;

        try {
            $recentAccepted = Application::query()
                ->with('user')
                ->where('status', ApplicationStatus::Accepted->value)
                ->latest('reviewed_at')
                ->latest('updated_at')
                ->take(4)
                ->get();

            $totalAccepted = Application::query()
                ->where('status', ApplicationStatus::Accepted->value)
                ->count();
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            $categories = ApplicationCatalog::categories(true);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $discordEntry = collect(config('community.social_links', []))
            ->first(fn (array $link) => str($link['label'] ?? '')->lower()->contains('discord'));
        $discordUrl = filled($discordEntry['url'] ?? null) ? $discordEntry['url'] : null;

        $processSteps = collect([
            ApplicationStatus::Pending,
            ApplicationStatus::InReview,
            ApplicationStatus::Interview,
            ApplicationStatus::Accepted,
        ])->map(fn (ApplicationStatus $status) => [
            'label' => $status->label(),
            'summary' => $status->userSummary(),
        ]);

        return view('welcome', [
            'applicationsOpen' => Setting::bool('applications_open', true),
            'recentAccepted' => $recentAccepted,
            'totalAccepted' => $totalAccepted,
            'categories' => $categories,
            'processSteps' => $processSteps,
            'minimumAge' => Setting::integer('minimum_age', 15),
            'reapplyCooldownDays' => Setting::integer('reapply_cooldown_days', 14),
            'requireDiscordGuild' => Setting::bool('require_discord_guild', false),
            'discordUrl' => $discordUrl,
        ]);
    }
}
