<x-layouts.admin :title="__('admin.institutional.edit') . ' · ' . __('admin.institutional.images.' . $siteImage->key . '.label')">

    <x-admin.ui.breadcrumb :items="[
        ['label' => __('admin.nav.dashboard'), 'href' => route('dashboard')],
        ['label' => __('admin.institutional.title'), 'href' => route('institutional.index')],
        ['label' => __('admin.institutional.images.' . $siteImage->key . '.label')],
        ['label' => __('admin.common.edit')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.institutional.edit') }}</h1>
        <p class="text-sm opacity-70">{{ __('admin.institutional.edit_sub') }}</p>
    </div>

    <x-admin.ui.card class="max-w-xl" x-data="{}">
        <form method="POST" action="{{ route('institutional.update', ['siteImage' => $siteImage->key]) }}"
            enctype="multipart/form-data" x-data="{}">
            @csrf
            @method('PUT')

            @php
                $media = $siteImage->getFirstMedia('image');
            @endphp

            <div class="mb-4 text-sm">
                <span class="opacity-60">{{ __('admin.institutional.current') }}:</span>
                @if ($media)
                    <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">
                        {{ $media->getPathRelativeToRoot() }}
                    </code>
                @else
                    <span class="badge badge-ghost badge-sm">
                        {{ __('admin.institutional.using_default') }}
                    </span>
                @endif
            </div>

            <x-admin.media.single-uploader name="image" :label="__('admin.institutional.replace')" :help="__('admin.institutional.images.' . $siteImage->key . '.help')"
                :current-url="$siteImage->imageUrl()" :accept="$siteImage->key === 'header_logo'
                    ? 'image/svg+xml,image/png,image/jpeg,image/webp'
                    : 'image/jpeg,image/png,image/webp'" />

            <div class="flex justify-end gap-2 mt-8 pt-4 border-t border-base-300">
                <x-admin.ui.button variant="ghost" :href="route('institutional.index')">
                    {{ __('admin.common.cancel') }}
                </x-admin.ui.button>
                <x-admin.ui.button type="submit" icon="icofont-check-circled">
                    {{ __('admin.institutional.save') }}
                </x-admin.ui.button>
            </div>
        </form>

        @if ($media)
            <div class="mt-6 flex justify-end border-t border-base-300 pt-4">
                <x-admin.ui.button type="button" variant="error" :outline="true" icon="icofont-trash"
                    onclick="document.getElementById('delete-institutional-image').showModal()">
                    {{ __('admin.institutional.delete_image') }}
                </x-admin.ui.button>
            </div>

            <x-admin.ui.modal id="delete-institutional-image" :title="__('admin.institutional.delete_image_modal_title')" size="sm">
                <p class="text-sm">{{ __('admin.institutional.delete_image_help') }}</p>
                <x-slot name="actions">
                    <form method="dialog">
                        <button class="btn btn-ghost">{{ __('admin.common.cancel') }}</button>
                    </form>
                    <form method="POST"
                        action="{{ route('institutional.image.destroy', ['siteImage' => $siteImage->key]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">{{ __('admin.common.delete') }}</button>
                    </form>
                </x-slot>
            </x-admin.ui.modal>
        @endif
    </x-admin.ui.card>

</x-layouts.admin>
