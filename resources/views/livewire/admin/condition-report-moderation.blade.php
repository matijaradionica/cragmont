<?php

use App\Models\ConditionReport;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Gate::allows('moderateConditions'), 403);
    }

    public function approve(int $reportId): void
    {
        abort_unless(Gate::allows('moderateConditions'), 403);

        $report = ConditionReport::query()->where('type', 'safety_concern')->findOrFail($reportId);

        $report->update([
            'is_approved' => true,
            'moderator_id' => auth()->id(),
        ]);

        session()->flash('moderation_status', 'Safety concern approved.');
        $this->resetPage();
    }

    public function reject(int $reportId): void
    {
        abort_unless(Gate::allows('moderateConditions'), 403);

        $report = ConditionReport::query()->where('type', 'safety_concern')->findOrFail($reportId);
        $report->delete();

        session()->flash('moderation_status', 'Safety concern rejected and deleted.');
        $this->resetPage();
    }

    public function getPendingReportsProperty()
    {
        return ConditionReport::query()
            ->with(['route.location', 'user'])
            ->where('type', 'safety_concern')
            ->where('is_approved', false)
            ->orderByDesc('created_at')
            ->paginate(20);
    }
}; ?>

<div class="space-y-4">
    @if (session('moderation_status'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded">
            {{ session('moderation_status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pending Safety Concerns</h3>
            <p class="text-sm text-gray-600">Reports submitted by users that require moderator review.</p>
        </div>

        @if($this->pendingReports->isEmpty())
            <div class="p-6 text-sm text-gray-500">No pending safety concerns.</div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($this->pendingReports as $report)
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">
                                    <a href="{{ route('routes.show', $report->route) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $report->route->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $report->route->location->getFullPath() }} • Submitted {{ $report->created_at->diffForHumans() }} by {{ $report->user?->name ?? 'Unknown' }}
                                </div>
                                <div class="mt-3 text-sm text-gray-800 whitespace-pre-wrap">{{ $report->content }}</div>
                            </div>

                            <div class="flex flex-col gap-2 flex-shrink-0">
                                <button wire:click="approve({{ $report->id }})"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-green-600 text-white rounded-md text-xs uppercase tracking-widest hover:bg-green-700">
                                    Approve
                                </button>
                                <button wire:click="reject({{ $report->id }})"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white rounded-md text-xs uppercase tracking-widest hover:bg-red-700"
                                    onclick="return confirm('Reject and delete this report?')">
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $this->pendingReports->links() }}
            </div>
        @endif
    </div>
</div>

