<x-app-layout :box=true>
    @php
        $cols = [
            "id",
            "title",
            "author.name",
            "comments_count",
            "created_at",
            "updated_at"
        ];
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Posts') }}
        </h2>
    </x-slot>
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