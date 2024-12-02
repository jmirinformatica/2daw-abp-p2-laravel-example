<x-app-layout :box=true>
    @php
        $cols = [
            "id",
            "title",
            "body",
            "author.name",
            "comments_count",
            "created_at",
            "updated_at"
        ];
    @endphp
    <!-- Results -->
    <x-table-index :cols=$cols :rows=$posts 
        :enableActions=true parentRoute='posts' 
        :enableSearch=true :search=$search />
    <!-- Pagination -->
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
    <!-- Buttons -->
    <div class="mt-8">
        @can('create', App\Models\Post::class)
        <x-primary-button href="{{ route('posts.create') }}">
            {{ __('Add new post') }}
        </x-primary-button>
        @endcan
        <x-secondary-button href="{{ route('dashboard') }}">
            {{ __('Back to dashboard') }}
        </x-secondary-button>
    </div>
</x-app-layout>