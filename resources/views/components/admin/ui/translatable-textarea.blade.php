@php
    $hasErrorEs = $errors->has($name . '.es');
    $hasErrorCa = $errors->has($name . '.ca');
@endphp

<fieldset class="fieldset w-full" x-data="{ countEs: 0, countCa: 0 }"
    x-init="countEs = $refs.es.value.length; countCa = $refs.ca.value.length">
    @if ($label)
        <legend class="fieldset-legend">
            {{ $label }} @if ($required)
                <span class="text-error">*</span>
            @endif
        </legend>
    @endif

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        {{-- ES --}}
        <div>
            <label class="block">
                <span class="badge badge-xs badge-neutral mb-2">ES</span>
                <textarea name="{{ $name }}[es]" id="{{ $name }}_es" rows="{{ $rows }}" x-ref="es"
                    @if ($placeholder) placeholder="{{ $placeholder }}" @endif @required($required)
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif @disabled($disabled)
                    @input="countEs = $event.target.value.length"
                    aria-invalid="{{ $hasErrorEs ? 'true' : 'false' }}"
                    aria-describedby="{{ $name }}-es-help{{ $help ? ' ' . $name . '-help' : '' }}"
                    @if ($formVar) x-model="{{ $formVar }}.{{ $name }}.es"
                        @change="{{ $formVar }}.validate('{{ $name }}.es')"
                    @else
                        @change="$data.form?.validate?.('{{ $name }}.es')" @endif
                    class="textarea textarea-bordered w-full @error($name . '.es') textarea-error @enderror"
                    :class="{ 'textarea-error': $data.form?.invalid('{{ $name }}.es') }">{{ old($name . '.es', $valueEs) }}</textarea>
            </label>
            <p id="{{ $name }}-es-help" class="fieldset-label flex items-start justify-between gap-2">
                <span class="min-w-0">
                    @error($name . '.es')
                        <span class="text-error flex items-center gap-1">
                            <i class="icofont-warning-alt"></i> {{ $message }}
                        </span>
                    @else
                        <span x-cloak x-show="$data.form?.invalid('{{ $name }}.es')"
                            x-text="$data.form?.errors?.['{{ $name }}.es'] ?? ''"
                            class="text-error flex items-center gap-1"></span>
                    @enderror
                </span>
                @if ($maxlength)
                    <span class="ml-auto whitespace-nowrap text-xs opacity-60"
                        x-text="countEs + ' / {{ $maxlength }}'"></span>
                @endif
            </p>
        </div>

        {{-- CA --}}
        <div>
            <label class="block">
                <span class="badge badge-xs badge-neutral mb-2">CA</span>
                <textarea name="{{ $name }}[ca]" id="{{ $name }}_ca" rows="{{ $rows }}" x-ref="ca"
                    @if ($placeholder) placeholder="{{ $placeholder }}" @endif @required($required)
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif @disabled($disabled)
                    @input="countCa = $event.target.value.length"
                    aria-invalid="{{ $hasErrorCa ? 'true' : 'false' }}"
                    aria-describedby="{{ $name }}-ca-help{{ $help ? ' ' . $name . '-help' : '' }}"
                    @if ($formVar) x-model="{{ $formVar }}.{{ $name }}.ca"
                        @change="{{ $formVar }}.validate('{{ $name }}.ca')"
                    @else
                        @change="$data.form?.validate?.('{{ $name }}.ca')" @endif
                    class="textarea textarea-bordered w-full @error($name . '.ca') textarea-error @enderror"
                    :class="{ 'textarea-error': $data.form?.invalid('{{ $name }}.ca') }">{{ old($name . '.ca', $valueCa) }}</textarea>
            </label>
            <p id="{{ $name }}-ca-help" class="fieldset-label flex items-start justify-between gap-2">
                <span class="min-w-0">
                    @error($name . '.ca')
                        <span class="text-error flex items-center gap-1">
                            <i class="icofont-warning-alt"></i> {{ $message }}
                        </span>
                    @else
                        <span x-cloak x-show="$data.form?.invalid('{{ $name }}.ca')"
                            x-text="$data.form?.errors?.['{{ $name }}.ca'] ?? ''"
                            class="text-error flex items-center gap-1"></span>
                    @enderror
                </span>
                @if ($maxlength)
                    <span class="ml-auto whitespace-nowrap text-xs opacity-60"
                        x-text="countCa + ' / {{ $maxlength }}'"></span>
                @endif
            </p>
        </div>
    </div>

    @if ($help)
        <p id="{{ $name }}-help" class="fieldset-label">{{ $help }}</p>
    @endif
</fieldset>
