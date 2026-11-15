<x-layouts.admin :title="__('admin.categories.edit')">

    <x-admin.ui.breadcrumb :items="[
        ['label' => __('admin.nav.dashboard'), 'href' => route('dashboard')],
        ['label' => __('admin.categories.title'), 'href' => route('categories.index')],
        ['label' => $category->getTranslation('name', 'es', false)],
        ['label' => __('admin.common.edit')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.categories.edit') }}</h1>
        <p class="text-sm opacity-70">
            <code
                class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $category->getTranslation('name', 'es', false) }}</code>
        </p>
    </div>

    <x-admin.ui.card class="max-w-lg" x-data="{}">
        <form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data" x-data="{}">
            @csrf
            @method('PATCH')

            <x-admin.ui.translatable-input name="name" :label="__('admin.categories.col_name')" icon="icofont-sub-listing"
                :required="true" :value-es="old('name.es', $category->getTranslation('name', 'es', false))" :value-ca="old('name.ca', $category->getTranslation('name', 'ca', false))" />

            <div class="mt-6">
                <x-admin.media.single-uploader name="image" :label="__('admin.categories.image')" :help="__('admin.categories.image_help')"
                    :current-url="$category->imageUrl(asset('img/image-placeholder.svg'), 'thumb')" accept="image/jpeg,image/png,image/webp" />
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs opacity-50">
                <span><i class="icofont-calendar"></i> {{ __('admin.common.created_at') }}:
                    {{ $category->created_at->format('d/m/Y H:i') }}</span>
                <span><i class="icofont-clock-time"></i> {{ __('admin.common.updated_at') }}:
                    {{ $category->updated_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="flex justify-end gap-2 mt-8 pt-4 border-t border-base-300">
                <x-admin.ui.button variant="ghost" :href="route('categories.index')">
                    {{ __('admin.common.cancel') }}
                </x-admin.ui.button>
                <x-admin.ui.button type="submit" icon="icofont-check-circled">
                    {{ __('admin.common.save_changes') }}
                </x-admin.ui.button>
            </div>
        </form>

        @if ($category->getFirstMedia('image'))
            <div class="mt-6 flex justify-end border-t border-base-300 pt-4">
                <x-admin.ui.button type="button" variant="error" :outline="true" icon="icofont-trash"
                    onclick="document.getElementById('delete-category-image').showModal()">
                    {{ __('admin.categories.delete_image') }}
                </x-admin.ui.button>
            </div>

            <x-admin.ui.modal id="delete-category-image" :title="__('admin.categories.delete_image_modal_title')" size="sm">
                <p class="text-sm">{{ __('admin.categories.delete_image_help') }}</p>
                <x-slot name="actions">
                    <form method="dialog">
                        <button class="btn btn-ghost">{{ __('admin.common.cancel') }}</button>
                    </form>
                    <form method="POST" action="{{ route('categories.image.destroy', $category) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">{{ __('admin.common.delete') }}</button>
                    </form>
                </x-slot>
            </x-admin.ui.modal>
        @endif
    </x-admin.ui.card>

</x-layouts.admin>
