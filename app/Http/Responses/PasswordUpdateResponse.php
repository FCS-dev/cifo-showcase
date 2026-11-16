<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;

class PasswordUpdateResponse implements PasswordUpdateResponseContract
{
    public function toResponse($request)
    {
        $locale = $request->user()?->locale;
        $message = __('admin.profile.password_updated', [], in_array($locale, ['es', 'ca'], true) ? $locale : 'es');
        $redirect = route('dashboard');

        $request->session()->flash('success', $message);

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => $redirect])
            : redirect($redirect);
    }
}
