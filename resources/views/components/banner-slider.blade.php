@props(['banners'])

@if ($banners->isNotEmpty())
    <section
        class="relative w-full overflow-hidden bg-forest-900"
        x-data="bannerSlider({{ $banners->count() }})"
        @mouseenter="pause()"
        @mouseleave="resume()"
        @touchstart.passive="onTouchStart($event)"
        @touchend.passive="onTouchEnd($event)"
        aria-roledescription="carousel"
        aria-label="Featured banners"
    >
        <div class="relative h-[220px] md:h-[320px] lg:h-[420px]">
            @foreach ($banners as $index => $banner)
                <article
                    class="absolute inset-0 transition duration-700 motion-reduce:transition-none"
                    :class="index === {{ $index }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    :aria-hidden="index === {{ $index }} ? 'false' : 'true'"
                    aria-roledescription="slide"
                    aria-label="{{ $index + 1 }} of {{ $banners->count() }}"
                >
                    @if ($banner->image_path)
                        <img
                            src="{{ asset($banner->image_path) }}"
                            alt=""
                            class="h-full w-full object-cover"
                            width="1600"
                            height="700"
                            @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif
                            decoding="async"
                        >
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-forest-900 via-forest-800 to-gold-700"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/25 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 mx-auto flex w-full max-w-6xl flex-col gap-3 px-4 pb-14 text-white sm:pb-16">
                        @if ($banner->title)
                            <h2 class="max-w-3xl font-serif text-3xl font-semibold leading-tight sm:text-5xl">{{ $banner->title }}</h2>
                        @endif
                        @if ($banner->subtitle)
                            <p class="max-w-2xl text-sm text-white/90 sm:text-lg">{{ $banner->subtitle }}</p>
                        @endif
                        @if ($href = safe_url($banner->link_url))
                            <a href="{{ $href }}" class="btn-gold w-fit" rel="noopener noreferrer">Learn more</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if ($banners->count() > 1)
            <div class="pointer-events-none absolute inset-x-0 top-1/2 flex -translate-y-1/2 justify-between px-2 sm:px-4">
                <button type="button" class="pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-forest-900 shadow" @click="prev()" aria-label="Previous banner">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12 4L6 10l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-forest-900 shadow" @click="next()" aria-label="Next banner">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M8 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <div class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-2" role="tablist" aria-label="Choose a banner">
                @foreach ($banners as $index => $banner)
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center"
                        role="tab"
                        :aria-selected="index === {{ $index }} ? 'true' : 'false'"
                        @click="go({{ $index }})"
                        aria-label="Show banner {{ $index + 1 }}"
                    >
                        <span class="h-2.5 w-2.5 rounded-full" :class="index === {{ $index }} ? 'bg-white' : 'bg-white/50'"></span>
                    </button>
                @endforeach
                <button type="button" class="inline-flex h-11 items-center rounded-full bg-white/90 px-3 text-sm font-semibold text-forest-900" @click="toggle()" x-text="running ? 'Pause' : 'Play'" :aria-pressed="(!running).toString()"></button>
            </div>
        @endif
    </section>
@endif
