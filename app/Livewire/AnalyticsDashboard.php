<?php

namespace App\Livewire;

use App\Models\ClickEvent;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AnalyticsDashboard extends Component
{
    public Profile $profile;

    public int $rangeDays = 30;

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile;
        $this->authorize('view', $this->profile);
    }

    public function render()
    {
        $from = Carbon::now()->subDays($this->rangeDays)->startOfDay();

        $dateSql = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            default => 'DATE(created_at)',
        };

        $perDay = ClickEvent::query()
            ->selectRaw($dateSql.' as d, COUNT(*) as c')
            ->where('profile_id', $this->profile->id)
            ->where('created_at', '>=', $from)
            ->groupBy(DB::raw($dateSql))
            ->orderBy('d')
            ->get();

        $total = ClickEvent::query()
            ->where('profile_id', $this->profile->id)
            ->where('created_at', '>=', $from)
            ->count();

        $topLinks = ClickEvent::query()
            ->select(['links.title', DB::raw('COUNT(*) as clicks')])
            ->join('links', 'links.id', '=', 'click_events.link_id')
            ->where('click_events.profile_id', $this->profile->id)
            ->where('click_events.created_at', '>=', $from)
            ->groupBy('links.id', 'links.title')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        return view('livewire.analytics-dashboard', [
            'perDay' => $perDay,
            'total' => $total,
            'topLinks' => $topLinks,
        ]);
    }
}
