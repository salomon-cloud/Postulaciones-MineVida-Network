<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Support\ApplicationCatalog;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total' => Application::query()->count(),
        ];

        foreach (ApplicationStatus::cases() as $status) {
            $stats[$status->value] = Application::query()->where('status', $status->value)->count();
        }

        $statusColors = [
            ApplicationStatus::Pending->value => '#cbd5e1',
            ApplicationStatus::InReview->value => '#facc15',
            ApplicationStatus::Interview->value => '#38bdf8',
            ApplicationStatus::Accepted->value => '#34d399',
            ApplicationStatus::Rejected->value => '#fb7185',
            ApplicationStatus::Cancelled->value => '#a1a1aa',
        ];

        $totalApplications = max((int) $stats['total'], 1);
        $statusChart = collect(ApplicationStatus::cases())
            ->map(function (ApplicationStatus $status) use ($stats, $totalApplications, $statusColors) {
                $count = (int) ($stats[$status->value] ?? 0);

                return [
                    'label' => $status->label(),
                    'value' => $status->value,
                    'count' => $count,
                    'percent' => round(($count / $totalApplications) * 100, 2),
                    'color' => $statusColors[$status->value],
                ];
            });

        $catalog = ApplicationCatalog::types(true);
        $typeRows = Application::query()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $maxTypeCount = max((int) $typeRows->max('total'), 1);
        $typeChart = $typeRows->map(function ($row) use ($catalog, $maxTypeCount) {
            $categoryFinals = Application::query()
                ->where('type', $row->type)
                ->whereIn('status', [ApplicationStatus::Accepted->value, ApplicationStatus::Rejected->value])
                ->count();
            $categoryAccepted = Application::query()
                ->where('type', $row->type)
                ->where('status', ApplicationStatus::Accepted->value)
                ->count();

            return [
                'label' => $catalog[$row->type] ?? str($row->type)->replace(['-', '_'], ' ')->title()->toString(),
                'count' => (int) $row->total,
                'percent' => round(((int) $row->total / $maxTypeCount) * 100),
                'accepted' => $categoryAccepted,
                'acceptance_rate' => $categoryFinals > 0 ? round(($categoryAccepted / $categoryFinals) * 100) : null,
            ];
        });

        $dailyRows = Application::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $activityChart = collect(range(13, 0))
            ->map(function (int $daysAgo) use ($dailyRows) {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->format('d/m'),
                    'count' => (int) ($dailyRows[$date->toDateString()] ?? 0),
                ];
            });

        $maxDailyCount = max((int) $activityChart->max('count'), 1);
        $activityChart = $activityChart->map(fn (array $item) => [
            ...$item,
            'height' => max(10, round(($item['count'] / $maxDailyCount) * 100)),
        ]);

        $weeklyRows = Application::query()
            ->where('created_at', '>=', now()->subWeeks(7)->startOfWeek())
            ->get(['created_at'])
            ->groupBy(fn (Application $application) => $application->created_at->copy()->startOfWeek()->toDateString());

        $weeklyChart = collect(range(7, 0))
            ->map(function (int $weeksAgo) use ($weeklyRows) {
                $week = now()->subWeeks($weeksAgo)->startOfWeek();

                return [
                    'label' => $week->format('d/m'),
                    'count' => $weeklyRows->get($week->toDateString(), collect())->count(),
                ];
            });

        $maxWeeklyCount = max((int) $weeklyChart->max('count'), 1);
        $weeklyChart = $weeklyChart->map(fn (array $item) => [
            ...$item,
            'height' => max(8, round(($item['count'] / $maxWeeklyCount) * 100)),
        ]);

        $reviewDurations = Application::query()
            ->whereNotNull('reviewed_at')
            ->get(['created_at', 'reviewed_at'])
            ->map(fn (Application $application) => abs($application->created_at->diffInMinutes($application->reviewed_at)));

        $topReviewers = User::query()
            ->whereIn('role', [
                UserRole::Reviewer->value,
                UserRole::Admin->value,
                UserRole::Owner->value,
            ])
            ->withCount('reviewedApplications')
            ->orderByDesc('reviewed_applications_count')
            ->limit(5)
            ->get()
            ->filter(fn (User $user) => $user->reviewed_applications_count > 0)
            ->values();

        $finalCount = max(($stats[ApplicationStatus::Accepted->value] ?? 0) + ($stats[ApplicationStatus::Rejected->value] ?? 0), 1);
        $insights = [
            'today' => Application::query()->whereDate('created_at', today())->count(),
            'week' => Application::query()->where('created_at', '>=', now()->subDays(6)->startOfDay())->count(),
            'active' => ($stats[ApplicationStatus::Pending->value] ?? 0)
                + ($stats[ApplicationStatus::InReview->value] ?? 0)
                + ($stats[ApplicationStatus::Interview->value] ?? 0),
            'acceptance_rate' => round((($stats[ApplicationStatus::Accepted->value] ?? 0) / $finalCount) * 100),
            'avg_review_time' => $this->humanDuration((int) round($reviewDurations->avg() ?? 0)),
        ];

        $latest = Application::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'latest',
            'statusChart',
            'typeChart',
            'activityChart',
            'weeklyChart',
            'topReviewers',
            'insights',
        ));
    }

    private function humanDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'Sin datos';
        }

        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = intdiv($minutes, 60);

        if ($hours < 48) {
            return $hours.' h';
        }

        return intdiv($hours, 24).' dias';
    }
}
