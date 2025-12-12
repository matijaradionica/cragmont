<?php

use App\Models\ConditionReport;
use App\Models\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public int $routeId;

    public string $type = 'general';
    public array $selectedCategories = [];
    public string $content = '';

    public function updatedType(string $value): void
    {
        if ($value !== 'general') {
            $this->selectedCategories = [];
        }
    }

    public function getCategoryOptionsProperty(): array
    {
        return [
            'Weather/Moisture' => [
                'wet_seepage' => 'Wet/Seepage',
                'damp_humid' => 'Damp/Humid',
                'dry_good' => 'Dry/Good',
                'icy_frozen' => 'Icy/Frozen',
                'snow_at_base' => 'Snow at Base',
            ],
            'Physical Environment (Base & Approach)' => [
                'loose_rock' => 'Loose Rock',
                'heavy_traffic' => 'Heavy Traffic/Crowded',
                'tidy_clean' => 'Tidy/Clean',
                'litter_waste' => 'Litter/Waste Present',
                'approach_clear' => 'Approach Trail Clear',
            ],
        ];
    }

    private function expiryHoursForCategory(string $category): ?int
    {
        return match ($category) {
            // 48–72h bucket: using 72 hours for simplicity
            'wet_seepage', 'damp_humid', 'icy_frozen', 'snow_at_base', 'dry_good' => 72,

            // 24h bucket
            'heavy_traffic' => 24,

            // 7 days bucket
            'loose_rock', 'tidy_clean', 'litter_waste', 'approach_clear' => 24 * 7,

            default => null,
        };
    }

    public function submit(): void
    {
        $user = Auth::user();

        $allowedCategoryKeys = collect($this->categoryOptions)->flatMap(fn ($items) => array_keys($items))->values()->all();

        $rules = [
            'type' => ['required', 'string', Rule::in(['general', 'safety_concern'])],
            'selectedCategories' => ['array'],
            'selectedCategories.*' => ['string', Rule::in($allowedCategoryKeys)],
            'content' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->type === 'general') {
            $rules['selectedCategories'] = ['required', 'array', 'min:1'];
        } else {
            $rules['selectedCategories'] = ['array', 'size:0'];
            $rules['content'] = ['required', 'string', 'max:2000'];
        }

        $validated = $this->validate($rules);

        $route = Route::findOrFail($this->routeId);
        $this->authorize('view', $route);

        $isModeratorTier = $user->isAdmin() || $user->isModerator() || $user->isClubEquipper();
        $isApproved = $isModeratorTier ? true : ($validated['type'] === 'general');

        $expiresAt = null;
        $selectedCategories = $validated['type'] === 'general' ? array_values($validated['selectedCategories']) : [];
        $category = $validated['type'] === 'general' ? ($selectedCategories[0] ?? null) : null;

        if ($validated['type'] === 'general' && count($selectedCategories) > 0) {
            $maxHours = collect($selectedCategories)
                ->map(fn ($c) => $this->expiryHoursForCategory($c))
                ->filter()
                ->max();

            if ($maxHours) {
                $expiresAt = now()->addHours($maxHours);
            }
        }

        ConditionReport::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'category' => $category,
            'categories' => $selectedCategories,
            'content' => $validated['type'] === 'safety_concern' ? $validated['content'] : null,
            'expires_at' => $expiresAt,
            'is_approved' => $isApproved,
            'moderator_id' => $isApproved && $validated['type'] === 'safety_concern' && $isModeratorTier
                ? $user->id
                : null,
        ]);

        $this->reset('type', 'selectedCategories', 'content');
        $this->type = 'general';

        if ($validated['type'] === 'safety_concern' && !$isApproved) {
            session()->flash('condition_report_status', 'Safety concern submitted and pending moderator review.');
        } else {
            session()->flash('condition_report_status', 'Condition report posted.');
        }

        $this->dispatch('condition-reports-updated');
    }
}; ?>

<section class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Report Conditions</h3>
    <p class="text-sm text-gray-600 mb-4">
        Share current conditions, access notes, or report a safety concern.
    </p>

    @if (session('condition_report_status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded">
            {{ session('condition_report_status') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4" x-data="{ type: @entangle('type').live }">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select x-model="type"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="general">General</option>
                <option value="safety_concern">Safety concern (requires approval)</option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-4">
            <div x-cloak x-show="type === 'general'" class="space-y-3">
                <div class="flex items-baseline justify-between gap-3">
                    <label class="block text-sm font-medium text-gray-700">Conditions</label>
                    <span class="text-xs text-gray-500">Select all that apply</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($this->categoryOptions as $group => $items)
                        <fieldset class="rounded-md border border-gray-200 p-3">
                            <legend class="px-1 text-xs font-semibold text-gray-700">{{ $group }}</legend>
                            <div class="mt-2 space-y-2">
                                @foreach($items as $value => $label)
                                    <label class="flex items-start gap-2 text-sm text-gray-700">
                                        <input type="checkbox"
                                            wire:model="selectedCategories"
                                            value="{{ $value }}"
                                            x-bind:disabled="type !== 'general'"
                                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500">
                    General reports expire automatically based on selected conditions.
                </p>

                @error('selectedCategories')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('selectedCategories.*')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-cloak x-show="type === 'safety_concern'" class="space-y-3">
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                    Safety concerns require moderator approval before they appear publicly.
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                    <textarea wire:model="content" rows="4"
                        x-bind:required="type === 'safety_concern'"
                        x-bind:disabled="type !== 'safety_concern'"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Describe the safety issue (hazard, closure, damaged bolts, rockfall, etc.)."></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                Submit
            </button>
        </div>
    </form>
</section>
