<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;

class ProfileInformationUpdatedResponse implements ProfileInformationUpdatedResponseContract
{
    public function toResponse($request)
    {
        $locale = $request->user()?->locale;
        $message = __('admin.profile.updated', [], in_array($locale, ['es', 'ca'], true) ? $locale : 'es');
        $redirect = route('profile.edit');

        $request->session()->flash('success', $message);

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => $redirect])
            : redirect($redirect);
    }
}
