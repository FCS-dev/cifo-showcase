{{--
 * resources/views/front/privacy.blade.php
 * Privacy policy — static legal content.
 --}}

<x-layouts.app :title="__('privacy.page_title')" :description="__('privacy.page_desc')">

    <article class="legal-page">

        {{-- ── HERO ──────────────────────────────────────────── --}}
        <header class="legal-hero">
            <div class="container">

                <nav class="legal-breadcrumb" aria-label="{{ __('privacy.breadcrumb_aria') }}">
                    <ol>
                        <li><a href="{{ route('home') }}">{{ __('front.nav.home') }}</a></li>
                        <li aria-hidden="true"><span class="breadcrumb-sep">›</span></li>
                        <li><span class="breadcrumb-current" aria-current="page">{{ __('privacy.breadcrumb') }}</span></li>
                    </ol>
                </nav>

                <div class="legal-hero-content reveal">
                    <h1>{{ __('privacy.page_title') }}</h1>
                    <p>{{ __('privacy.hero_intro') }}</p>
                </div>

            </div>
        </header>

        {{-- ── CONTENT ───────────────────────────────────────── --}}
        <div class="legal-content">
            <div class="container">
                <div class="legal-content-inner">

                    <p class="legal-updated">{{ __('privacy.updated') }}</p>
                    <p class="legal-intro">{{ __('privacy.intro') }}</p>

                    @foreach (__('privacy.sections') as $section)
                        <section class="legal-section">
                            <h2>{{ $section['title'] }}</h2>

                            @foreach ($section['paragraphs'] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach

                            @if (!empty($section['items']))
                                <ul>
                                    @foreach ($section['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @foreach ($section['after'] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </section>
                    @endforeach

                </div>
            </div>
        </div>

    </article>

</x-layouts.app>
