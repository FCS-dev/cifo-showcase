<x-layouts.admin :title="__('admin.institutional.title')">

    <x-admin.ui.breadcrumb :items="[
        ['label' => __('admin.nav.dashboard'), 'href' => route('dashboard')],
        ['label' => __('admin.institutional.title')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.institutional.title') }}</h1>
        <p class="text-sm opacity-70">{{ __('admin.institutional.subtitle') }}</p>
    </div>

    <x-admin.ui.card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('admin.institutional.col_image') }}</th>
                        <th>{{ __('admin.institutional.col_item') }}</th>
                        <th>{{ __('admin.institutional.col_path') }}</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slots as $key => $slot)
                        @php
                            $image = $images[$key];
                            $media = $image->getFirstMedia('image');
                        @endphp
                        <tr class="hover">
                            <td>
                                <img src="{{ $image->imageUrl() }}"
                                    alt="{{ __('admin.institutional.images.' . $key . '.label') }}"
                                    class="h-14 w-20 rounded-lg border border-base-300 bg-base-200 {{ $key === 'header_logo' ? 'object-contain p-1' : 'object-cover' }}">
                            </td>
                            <td class="font-medium">
                                {{ __('admin.institutional.images.' . $key . '.label') }}
                            </td>
                            <td class="text-sm opacity-70">
                                @if ($media)
                                    <code>{{ $media->getPathRelativeToRoot() }}</code>
                                @else
                                    <span class="badge badge-ghost badge-sm">
                                        {{ __('admin.institutional.using_default') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('institutional.update')
                                    <x-admin.ui.button :href="route('institutional.edit', ['siteImage' => $key])"
                                        icon="icofont-edit" variant="ghost" size="sm">
                                        {{ __('admin.common.edit') }}
                                    </x-admin.ui.button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.ui.card>

</x-layouts.admin>
