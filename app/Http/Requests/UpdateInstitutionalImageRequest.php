<?php

namespace App\Http\Requests;

use App\Models\SiteImage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionalImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteImage = $this->route('siteImage');

        if ($siteImage instanceof SiteImage && $siteImage->key === 'header_logo') {
            return [
                'image' => ['nullable', 'file', 'mimetypes:image/svg+xml,image/png,image/jpeg,image/webp', 'max:2048'],
            ];
        }

        return [
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
