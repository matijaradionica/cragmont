<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Logbook') }}
            </h2>
            <a href="{{ route('ascents.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Log New Ascent
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    @if($ascents->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No ascents logged</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by logging your first climb!</p>
                            <div class="mt-6">
                                <a href="{{ route('ascents.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Log Your First Ascent
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($ascents as $ascent)
                                <div class="border-l-4 pl-4 py-3 hover:bg-gray-50 transition
                                    {{ $ascent->status === 'Success' ? 'border-green-500' : ($ascent->status === 'Failed' ? 'border-red-500' : 'border-yellow-500') }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <a href="{{ route('routes.show', $ascent->route) }}" class="hover:text-indigo-600">
                                                        {{ $ascent->route->name }}
                                                    </a>
                                                </h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ascent->getStatusBadgeClass() }}">
                                                    {{ $ascent->status }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $ascent->route->location->getFullPath() }} •
                                                {{ $ascent->route->grade_type }}: {{ $ascent->route->grade_value }}
                                            </p>
                                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $ascent->ascent_date->format('M d, Y') }}
                                                </span>
                                                @if($ascent->partners)
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                        </svg>
                                                        {{ $ascent->partners }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($ascent->notes)
                                                <p class="mt-2 text-sm text-gray-700 line-clamp-2">{{ $ascent->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <a href="{{ route('ascents.show', $ascent) }}"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                View
                                            </a>
                                            @can('update', $ascent)
                                                <a href="{{ route('ascents.edit', $ascent) }}"
                                                    class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                                    Edit
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $ascents->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            @if($ascents->total() > 0)
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Total Ascents</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $ascents->total() }}</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Successful Climbs</div>
                        <div class="mt-2 text-3xl font-semibold text-green-600">
                            {{ $successfulCount }}
                        </div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Unique Routes</div>
                        <div class="mt-2 text-3xl font-semibold text-indigo-600">
                            {{ $uniqueRouteCount }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Total Vertical Climbed</div>
                                <div class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ number_format($totalVerticalM) }} m
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($totalVerticalFt) }} ft (successful climbs with length set)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Ascents by Grade (Success)</div>

                        @if(empty($ascentsByGrade))
                            <p class="mt-3 text-sm text-gray-500">No successful climbs yet.</p>
                        @else
                            <div class="mt-4 space-y-2">
                                @foreach($ascentsByGrade as $row)
                                    @php
                                        $pct = $maxGradeCount > 0 ? (int) round(($row['count'] / $maxGradeCount) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="w-28 shrink-0 text-xs text-gray-700 truncate" title="{{ $row['label'] }}">
                                            {{ $row['label'] }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="h-2 w-full rounded bg-gray-100 overflow-hidden">
                                                <div class="h-2 bg-indigo-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                        <div class="w-10 text-right text-xs font-medium text-gray-700">{{ $row['count'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 bg-white shadow rounded-lg p-6">
                    <div class="flex items-baseline justify-between gap-3">
                        <div class="text-sm font-medium text-gray-500">Annual Activity ({{ now()->year }})</div>
                        <div class="text-xs text-gray-400">ascents per month</div>
                    </div>

                    @php
                        $monthLabels = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                    @endphp

                    <div class="mt-4 grid grid-cols-12 gap-2 items-end">
                        @foreach($activityByMonth as $row)
                            @php
                                $h = $maxMonthlyCount > 0 ? (int) round(($row['count'] / $maxMonthlyCount) * 100) : 0;
                                $h = $row['count'] > 0 ? max(4, $h) : 0;
                                $label = $monthLabels[$row['month']] ?? (string) $row['month'];
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-full h-28 flex items-end">
                                    <div class="w-full rounded bg-indigo-500/80" style="height: {{ $h }}%" title="{{ $label }}: {{ $row['count'] }}"></div>
                                </div>
                                <div class="text-[10px] text-gray-500">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
