<x-app-layout :box=true>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>
    <div class="">
        {{ $post->body }}
    </div>
    <table class="table mt-8">
        <tbody>
            <tr>
                <td><strong>Author</strong></td>
                <td>{{ $post->author->name }}</td>
            </tr>
            <tr>
                <td><strong>Created</strong></td>
                <td>{{ $post->created_at }}</td>
            </tr>
            <tr>
                <td><strong>Updated</strong></td>
                <td>{{ $post->updated_at }}</td>
            </tr>
        </tbody>
    </table>
    <div class="mt-8">
        @can('update', $post)
        <x-primary-button href="{{ route('posts.edit', $post) }}">
            {{ __('Edit') }}
        </x-primary-button>
        @endcan
        @can('delete', $post)
        <x-danger-button href="{{ route('posts.destroy', $post) }}">
            {{ __('Delete') }}
        </x-danger-button>
        @endcan
        @can('viewAny', App\Models\Post::class)
        <x-secondary-button href="{{ route('posts.index') }}">
            {{ __('Back to list') }}
        </x-secondary-button>
        @endcan
    </div>
    <div class="mt-8">
        <p>{{ __(':num comments', ['num' => $post->comments_count]) }}</p>
        @include('posts.partials.comments')
    </div>
</x-app-layout>