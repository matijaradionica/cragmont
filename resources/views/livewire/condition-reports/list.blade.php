<?php

use App\Models\ConditionReport;
use App\Models\Route;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public int $routeId;

    public function getCategoryLabelMapProperty(): array
    {
        return [
            'wet_seepage' => 'Wet/Seepage',
            'damp_humid' => 'Damp/Humid',
            'dry_good' => 'Dry/Good',
            'icy_frozen' => 'Icy/Frozen',
            'snow_at_base' => 'Snow at Base',
            'loose_rock' => 'Loose Rock',
            'heavy_traffic' => 'Heavy Traffic/Crowded',
            'tidy_clean' => 'Tidy/Clean',
            'litter_waste' => 'Litter/Waste Present',
            'approach_clear' => 'Approach Trail Clear',
        ];
    }

    #[On('condition-reports-updated')]
    public function refresh(): void
    {
        // Re-render
    }

    public function getApprovedSafetyCountProperty(): int
    {
        return ConditionReport::query()
            ->where('route_id', $this->routeId)
            ->where('type', 'safety_concern')
            ->where('is_approved', true)
            ->count();
    }

    public function getReportsProperty()
    {
        $now = now();
        return ConditionReport::query()
            ->with(['user', 'moderator'])
            ->where('route_id', $this->routeId)
            ->where(function ($q) {
                $q->where('type', 'general')
                    ->orWhere(function ($q2) {
                        $q2->where('type', 'safety_concern')
                            ->where('is_approved', true);
                    });
            })
            ->where(function ($q) use ($now) {
                $q->where('type', 'safety_concern')
                    ->orWhere(function ($q2) use ($now) {
                        $q2->where('type', 'general')
                            ->whereNull('archived_at')
                            ->where(function ($q3) use ($now) {
                                $q3->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', $now);
                            });
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPastReportsProperty()
    {
        $now = now();
        return ConditionReport::query()
            ->with(['user'])
            ->where('route_id', $this->routeId)
            ->where('type', 'general')
            ->where(function ($q) use ($now) {
                $q->whereNotNull('archived_at')
                    ->orWhere('expires_at', '<=', $now);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }
}; ?>

<section class="space-y-4">
    @if($this->approvedSafetyCount > 0)
        <div class="sticky top-0 z-20 bg-yellow-50 border border-yellow-300 text-yellow-900 px-4 py-3 rounded">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Safety reports present</div>
                    <div class="text-sm">This route has {{ $this->approvedSafetyCount }} approved safety concern(s). Read below before climbing.</div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conditions</h3>

        @if($this->reports->isEmpty())
            <p class="text-sm text-gray-500">No condition reports yet.</p>
        @else
            <div class="space-y-4">
                @foreach($this->reports as $report)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    @if($report->type === 'safety_concern')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-900">
                                            Safety concern
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            General
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-600 truncate">
                                        {{ $report->user?->name ?? 'Unknown' }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ $report->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                @php
                                    $categoryKeys = [];
                                    if (is_array($report->categories) && count($report->categories) > 0) {
                                        $categoryKeys = $report->categories;
                                    } elseif ($report->category) {
                                        $categoryKeys = [$report->category];
                                    }

                                    $categoryLabels = collect($categoryKeys)
                                        ->map(fn ($key) => $this->categoryLabelMap[$key] ?? ucwords(str_replace('_', ' ', (string) $key)))
                                        ->values()
                                        ->all();
                                @endphp

                                @if(count($categoryLabels) > 0)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($categoryLabels as $label)
                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-800">
                                                {{ $label }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if($report->type === 'safety_concern' && $report->moderator)
                                <div class="text-xs text-gray-500">
                                    Approved by {{ $report->moderator->name }}
                                </div>
                            @endif
                        </div>

                        @if(filled($report->content))
                            <div class="mt-2 text-sm text-gray-800 whitespace-pre-wrap">{{ $report->content }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Past Conditions</h3>
        <p class="text-sm text-gray-600 mb-4">Expired general reports are archived here.</p>

        @if($this->pastReports->isEmpty())
            <p class="text-sm text-gray-500">No past condition reports.</p>
        @else
            <div class="space-y-3">
                @foreach($this->pastReports as $report)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs text-gray-500">
                                {{ $report->created_at->diffForHumans() }} • {{ $report->user?->name ?? 'Unknown' }}
                                @if($report->category)
                                    • {{ ucwords(str_replace('_', ' ', $report->category)) }}
                                @endif
                            </div>
                            @if($report->expires_at)
                                <div class="text-xs text-gray-400">
                                    expired {{ $report->expires_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>

                        @php
                            $categoryKeys = [];
                            if (is_array($report->categories) && count($report->categories) > 0) {
                                $categoryKeys = $report->categories;
                            } elseif ($report->category) {
                                $categoryKeys = [$report->category];
                            }

                            $categoryLabels = collect($categoryKeys)
                                ->map(fn ($key) => $this->categoryLabelMap[$key] ?? ucwords(str_replace('_', ' ', (string) $key)))
                                ->values()
                                ->all();
                        @endphp

                        @if(count($categoryLabels) > 0)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($categoryLabels as $label)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                        {{ $label }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if(filled($report->content))
                            <div class="mt-2 text-sm text-gray-800 whitespace-pre-wrap">{{ $report->content }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
