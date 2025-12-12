<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Notifications
            </h2>
            @if($warnings->where('is_read', false)->count() > 0)
                <form action="{{ route('warnings.mark-all-as-read') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if($warnings->isEmpty())
                <div class="bg-white shadow rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500">You don't have any warnings or notifications.</p>
                </div>
            @else
                <div class="bg-white shadow rounded-lg overflow-hidden divide-y divide-gray-200">
                    @foreach($warnings as $warning)
                        <div class="p-6 {{ $warning->is_read ? 'bg-white' : 'bg-blue-50' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            ⚠️ Warning
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $warning->created_at->diffForHumans() }}
                                        </span>
                                        @if(!$warning->is_read)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                New
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-base font-semibold text-gray-900 mb-2">
                                        Reason: {{ $warning->reason }}
                                    </h3>

                                    <p class="text-sm text-gray-700 mb-3">
                                        {{ $warning->message }}
                                    </p>

                                    @if($warning->commentReport && $warning->commentReport->comment)
                                        <div class="mt-3 p-3 bg-gray-100 rounded border border-gray-200">
                                            <p class="text-xs text-gray-500 mb-1">Related comment:</p>
                                            <p class="text-sm text-gray-700">"{{ Str::limit($warning->commentReport->comment->content, 150) }}"</p>
                                        </div>
                                    @endif

                                    <div class="mt-3 text-xs text-gray-500">
                                        Issued by: {{ $warning->warnedBy->name }}
                                    </div>
                                </div>

                                @if(!$warning->is_read)
                                    <form action="{{ route('warnings.mark-as-read', $warning) }}" method="POST" class="ml-4">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition">
                                            Mark as Read
                                        </button>
                                    </form>
                                @else
                                    <div class="ml-4 text-xs text-gray-500">
                                        Read {{ $warning->read_at->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $warnings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
