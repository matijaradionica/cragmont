<div class="flex space-x-3 {{ $level > 0 ? 'ml-12 mt-4' : '' }}">
    <div class="flex-shrink-0">
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">
            {{ substr($comment->user->name, 0, 1) }}
        </div>
    </div>

    <div class="flex-1 min-w-0">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                    <span class="text-sm text-gray-500 ml-2">
                        {{ $comment->created_at->diffForHumans() }}
                        @if($comment->edited_at)
                            <span class="text-xs">(edited)</span>
                        @endif
                    </span>
                </div>
            </div>

            <p class="text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>

            <!-- Comment Actions -->
            <div class="mt-3 flex items-center space-x-4 text-sm">
                @auth
                    <!-- Vote Buttons -->
                    <form action="{{ route('comments.vote', $comment) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="vote_type" value="upvote">
                        <button type="submit" class="text-gray-600 hover:text-green-600 transition">
                            <span class="{{ $comment->isUpvotedBy(auth()->user()) ? 'text-green-600 font-semibold' : '' }}">
                                ↑ {{ $comment->upvote_count }}
                            </span>
                        </button>
                    </form>

                    <form action="{{ route('comments.vote', $comment) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="vote_type" value="downvote">
                        <button type="submit" class="text-gray-600 hover:text-red-600 transition">
                            <span class="{{ $comment->isDownvotedBy(auth()->user()) ? 'text-red-600 font-semibold' : '' }}">
                                ↓ {{ $comment->downvote_count }}
                            </span>
                        </button>
                    </form>

                    <form action="{{ route('comments.vote', $comment) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="vote_type" value="helpful">
                        <button type="submit" class="text-gray-600 hover:text-blue-600 transition">
                            <span class="{{ $comment->isMarkedHelpfulBy(auth()->user()) ? 'text-blue-600 font-semibold' : '' }}">
                                ⭐ Helpful {{ $comment->helpful_count > 0 ? '(' . $comment->helpful_count . ')' : '' }}
                            </span>
                        </button>
                    </form>

                    <!-- Reply Button -->
                    <button onclick="toggleReplyForm('reply-form-{{ $comment->id }}')"
                        class="text-indigo-600 hover:text-indigo-900">
                        Reply
                    </button>

                    @if($comment->canBeEditedBy(auth()->user()))
                        <button onclick="toggleEditForm('edit-form-{{ $comment->id }}')"
                            class="text-gray-600 hover:text-gray-900">
                            Edit
                        </button>
                    @endif

                    @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                        <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                            onsubmit="return confirm('Delete this comment?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    @endif
                @endauth
            </div>

            <!-- Edit Form (Hidden by default) -->
            @auth
                @if($comment->canBeEditedBy(auth()->user()))
                    <form id="edit-form-{{ $comment->id }}" style="display: none;"
                        action="{{ route('comments.update', $comment) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <textarea name="content" rows="3" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $comment->content }}</textarea>
                        <div class="mt-2 flex space-x-2">
                            <button type="submit" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                Save
                            </button>
                            <button type="button" onclick="toggleEditForm('edit-form-{{ $comment->id }}')"
                                class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                @endif
            @endauth
        </div>

        <!-- Reply Form (Hidden by default) -->
        @auth
            <form id="reply-form-{{ $comment->id }}" style="display: none;"
                action="{{ route('routes.comments.store', $comment->route_id) }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="content" rows="2" required placeholder="Write a reply..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                <div class="mt-2 flex space-x-2">
                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Post Reply
                    </button>
                    <button type="button" onclick="toggleReplyForm('reply-form-{{ $comment->id }}')"
                        class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </form>
        @endauth

        <!-- Nested Replies -->
        @if($comment->replies->isNotEmpty())
            <div class="mt-4 space-y-4">
                @foreach($comment->replies as $reply)
                    @include('routes.partials.comment', ['comment' => $reply, 'level' => $level + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
<script>
function toggleReplyForm(formId) {
    const form = document.getElementById(formId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleEditForm(formId) {
    const form = document.getElementById(formId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endonce
