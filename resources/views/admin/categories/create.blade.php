<x-layouts.admin :title="__('admin.categories.create')">

    <x-admin.ui.breadcrumb :items="[
        ['label' => __('admin.nav.dashboard'), 'href' => route('dashboard')],
        ['label' => __('admin.categories.title'), 'href' => route('categories.index')],
        ['label' => __('admin.categories.create')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.categories.create') }}</h1>
        <p class="text-sm opacity-70">{{ __('admin.categories.create_sub') }}</p>
    </div>

    <x-admin.ui.card class="max-w-lg">
        <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" x-data="{}">
            @csrf

            <x-admin.ui.translatable-input name="name" :label="__('admin.categories.col_name')" icon="icofont-sub-listing"
                :placeholder="__('admin.categories.name_placeholder')" :value-es="old('name.es', '')" :value-ca="old('name.ca', '')"
                :required="true" />

            <div class="mt-6">
                <x-admin.media.single-uploader name="image" :label="__('admin.categories.image')" :help="__('admin.categories.image_help')"
                    :current-url="asset('img/image-placeholder.svg')" accept="image/jpeg,image/png,image/webp" />
            </div>

            <div class="flex justify-end gap-2 mt-8 pt-4 border-t border-base-300">
                <x-admin.ui.button variant="ghost" :href="route('categories.index')">
                    {{ __('admin.common.cancel') }}
                </x-admin.ui.button>
                <x-admin.ui.button type="submit" icon="icofont-check-circled">
                    {{ __('admin.categories.create_btn') }}
                </x-admin.ui.button>
            </div>
        </form>
    </x-admin.ui.card>

</x-layouts.admin>
