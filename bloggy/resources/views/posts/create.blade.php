<x-app-layout :box=true>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create post') }}
        </h2>
    </x-slot>
    <form method="POST" action="{{ route('posts.store') }}" class="mt-6 space-y-6">
        @csrf
        <div>
            <x-input-label for="title" :value="__('fields.title')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus autocomplete="title" />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>
        <div>
            <x-input-label for="body" :value="__('fields.body')" />
            <x-textarea id="body" name="body" class="mt-1 block w-full" :value="old('body')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('body')" />
        </div>
        <div>
            <x-primary-button type="submit">
                {{ __('Create') }}
            </x-primary-button>
            <x-secondary-button type="reset">
                {{ __('Reset') }}
            </x-secondary-button>
            <x-secondary-button href="{{ route('posts.myIndex') }}">
                {{ __('Back to list') }}
            </x-secondary-button>
        </div>
    </form>
</x-app-layout>