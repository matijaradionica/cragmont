<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Comment Reports
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Pending Reports ({{ $reports->total() }})
                    </h3>
                </div>

                @if($reports->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No pending reports. All clear!
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($reports as $report)
                            <div class="p-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $report->reason }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Reported by {{ $report->reportedBy->name }}
                                            </span>
                                            <span class="text-sm text-gray-400">
                                                {{ $report->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        @if($report->description)
                                            <p class="text-sm text-gray-700 mb-3">
                                                <strong>Details:</strong> {{ $report->description }}
                                            </p>
                                        @endif

                                        <div class="bg-gray-100 rounded-lg p-4 mb-3">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold text-sm">
                                                        {{ substr($report->comment->user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <span class="font-semibold text-gray-900">{{ $report->comment->user->name }}</span>
                                                        <span class="text-sm text-gray-500">
                                                            on
                                                            <a href="{{ route('routes.show', $report->comment->route) }}"
                                                               class="text-indigo-600 hover:text-indigo-900">
                                                                {{ $report->comment->route->name }}
                                                            </a>
                                                        </span>
                                                    </div>
                                                    <p class="text-gray-700 text-sm">{{ $report->comment->content }}</p>
                                                    @if($report->comment->photo_path)
                                                        <div class="mt-2">
                                                            <img src="{{ Storage::url($report->comment->photo_path) }}"
                                                                 alt="Comment photo"
                                                                 class="max-w-xs rounded border border-gray-300">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin.reports.approve', $report) }}" method="POST" class="flex items-center space-x-3">
                                            @csrf
                                            <label class="text-sm font-medium text-gray-700">Action:</label>
                                            <select name="action" required
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="delete_comment">Delete Comment</option>
                                                <option value="warn_user">Warn User</option>
                                                <option value="no_action">No Action</option>
                                            </select>
                                            <button type="submit"
                                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                                Resolve
                                            </button>
                                        </form>
                                    </div>

                                    <form action="{{ route('admin.reports.dismiss', $report) }}" method="POST" class="ml-4">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Dismiss this report?')"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                                            Dismiss
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
