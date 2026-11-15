@props([
    'name' => 'image',
    'label' => null,
    'help' => null,
    'currentUrl' => null,
    'accept' => 'image/jpeg,image/png,image/webp',
    'formVar' => null,
    'field' => null,
])

@php
    $currentUrl ??= asset('img/image-placeholder.svg');

    $changeHandler = 'preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null';

    if ($formVar && $field) {
        $changeHandler .= "; {$formVar}.{$field} = \$event.target.files[0] ?? null; {$formVar}.validateFiles().validate('{$field}')";
    }
@endphp

<fieldset class="fieldset w-full">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <div x-data="{ preview: null }" class="space-y-3">
        <div class="w-full max-w-sm overflow-hidden rounded-box border border-base-300 bg-base-200">
            <img x-show="!preview" src="{{ $currentUrl }}" alt=""
                class="h-48 w-full object-contain">
            <img x-show="preview" x-cloak :src="preview" alt=""
                class="h-48 w-full object-contain">
        </div>

        <input type="file" name="{{ $name }}" accept="{{ $accept }}"
            class="file-input file-input-bordered w-full max-w-sm" @change="{{ $changeHandler }}">

        @if ($help)
            <p class="fieldset-label">{{ $help }}</p>
        @endif

        @error($name)
            <p class="fieldset-label text-error flex items-center gap-1">
                <i class="icofont-warning-alt"></i> {{ $message }}
            </p>
        @enderror

        @if ($formVar && $field)
            <p x-cloak x-show="{{ $formVar }}.invalid('{{ $field }}')"
                x-text="{{ $formVar }}.errors?.['{{ $field }}'] ?? ''"
                class="fieldset-label text-error flex items-center gap-1"></p>
        @endif
    </div>
</fieldset>
