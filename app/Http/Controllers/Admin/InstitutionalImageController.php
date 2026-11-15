<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInstitutionalImageRequest;
use App\Models\SiteImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InstitutionalImageController extends Controller
{
    public function index(): View
    {
        Gate::authorize('institutional.view');

        $slots = SiteImage::SLOTS;

        $images = collect(array_keys($slots))
            ->mapWithKeys(fn (string $key) => [
                $key => SiteImage::with('media')->firstOrCreate(['key' => $key]),
            ]);

        return view('admin.institutional.index', compact('images', 'slots'));
    }

    public function edit(SiteImage $siteImage): View
    {
        Gate::authorize('institutional.update');

        abort_unless(array_key_exists($siteImage->key, SiteImage::SLOTS), 404);

        $slot = SiteImage::SLOTS[$siteImage->key];

        return view('admin.institutional.edit', compact('siteImage', 'slot'));
    }

    public function update(UpdateInstitutionalImageRequest $request, SiteImage $siteImage): RedirectResponse
    {
        Gate::authorize('institutional.update');

        abort_unless(array_key_exists($siteImage->key, SiteImage::SLOTS), 404);

        if (! $request->hasFile('image')) {
            return redirect()
                ->route('institutional.index')
                ->with('info', __('admin.institutional.no_changes'));
        }

        $siteImage->clearMediaCollection('image');
        $siteImage->addMedia($request->file('image'))->toMediaCollection('image');

        return redirect()
            ->route('institutional.index')
            ->with('success', __('admin.institutional.updated'));
    }

    public function destroyImage(SiteImage $siteImage): RedirectResponse
    {
        Gate::authorize('institutional.update');

        abort_unless(array_key_exists($siteImage->key, SiteImage::SLOTS), 404);

        $siteImage->clearMediaCollection('image');

        return redirect()
            ->route('institutional.edit', ['siteImage' => $siteImage->key])
            ->with('success', __('admin.institutional.image_deleted'));
    }
}
