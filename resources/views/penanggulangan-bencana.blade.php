<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penanggulangan Bencana - BENCANA ALAM</title>

    @vite(['resources/css/app.css'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="phone-shell">
    <div class="phone-frame flex flex-col">

        <!-- Header -->
        <div class="relative z-10 flex w-full shrink-0 items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
            <a href="{{ route('home') }}" class="absolute left-4 flex items-center">
                <img src="{{ asset('images/left-arrow.webp') }}" alt="Back" class="w-4">
            </a>
            <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PENANGGULANGAN BENCANA</h1>
        </div>

        <!-- Tab pintasan jenis bencana -->
        <div class="relative z-10 shrink-0 bg-[#ffac00]/95 px-3 py-2">
            <div id="tabs" class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($disasters as $index => $disaster)
                    <button type="button" class="nav-tab shrink-0 rounded-full border-2 border-[#800000] px-3 py-1 text-xs font-extrabold whitespace-nowrap transition-colors"
                        data-index="{{ $index }}" data-slug="{{ $disaster->slug }}"
                        style="background-color: {{ $index === 0 ? '#800000' : '#fff' }}; color: {{ $index === 0 ? '#fff' : '#800000' }}">
                        {{ $disaster->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Cards Container -->
        <div id="cards-container" class="relative flex-1 overflow-hidden" style="touch-action: pan-y">
            <div id="cards-track" class="flex h-full transition-transform duration-300 ease-out"
                style="width: {{ $disasters->count() * 100 }}%">

                @foreach ($disasters as $index => $disaster)
                    <div class="disaster-card flex h-full flex-col items-stretch overflow-hidden"
                        style="width: calc(100% / {{ $disasters->count() }})" data-index="{{ $index }}"
                        data-name="{{ $disaster->name }}" data-slug="{{ $disaster->slug }}"
                        data-description="{{ $disaster->description ?? '' }}">

                        <!-- Background -->
                        <img src="{{ asset('images/marker bg.webp') }}" alt=""
                            class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
                        <div class="absolute inset-0 bg-black/30"></div>

                        <!-- Scrollable Content -->
                        <div class="relative z-10 flex-1 overflow-y-auto px-4 pb-24 pt-4">

                            <!-- Single White Card -->
                            <div class="rounded border-2 border-[#800000] bg-white/90 p-4">
                                <!-- Title -->
                                <h2 class="mb-3 text-center text-lg font-extrabold text-[#800000]">{{ $disaster->name }}
                                </h2>

                                <!-- Description -->
                                @if ($disaster->description)
                                    <p
                                        class="mb-4 border-b-2 border-[#800000]/30 pb-4 text-sm leading-relaxed text-[#2f0000]">
                                        {{ $disaster->description }}
                                    </p>
                                @endif

                                <!-- Mitigation Steps -->
                                @foreach (['pra' => 'Pra-Bencana', 'saat' => 'Saat Terjadi', 'pasca' => 'Pasca-Bencana'] as $phase => $label)
                                    @if ($disaster->mitigationSteps->where('phase', $phase)->isNotEmpty())
                                        <div class="mb-4 last:mb-0">
                                            <div class="mb-2 flex items-center gap-2">
                                                <h3
                                                    class="rounded bg-[#ffac00] px-3 py-1 text-sm font-extrabold text-[#800000]">
                                                    {{ $label }}</h3>
                                            </div>
                                            <ul class="flex flex-col gap-2">
                                                @foreach ($disaster->mitigationSteps->where('phase', $phase)->sortBy('order') as $step)
                                                    <li class="flex items-start gap-2">
                                                        <span
                                                            class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#800000]"></span>
                                                        <span
                                                            class="text-sm leading-relaxed text-[#2f0000]">{{ $step->content }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                        </div>

                        <!-- Navigation overlay at bottom of card -->
                        <div class="pointer-events-none absolute bottom-16 left-0 right-0 h-8"></div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bottom Navigation Bar -->
        <div class="relative z-20 shrink-0 bg-[#ffac00] px-4 py-3">
            <!-- Disaster title -->
            <div class="mb-2 flex items-center justify-center">
                <h3 id="nav-title" class="text-center text-base font-extrabold text-[#800000]">
                    {{ $disasters->first()->name ?? '' }}
                </h3>
            </div>

            <!-- Arrows -->
            <div class="flex items-center justify-between">
                <button id="nav-prev"
                    class="flex cursor-pointer items-center gap-1 px-2 py-1 text-sm font-extrabold text-[#800000] transition-transform hover:scale-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-30"
                    {{ $disasters->count() <= 1 ? 'disabled' : '' }}>
                    <img src="{{ asset('images/left-arrow.webp') }}" alt="" class="w-6">
                </button>

                <span class="text-sm font-bold text-[#800000]">
                    <span id="nav-current">1</span> / <span id="nav-total">{{ $disasters->count() }}</span>
                </span>

                <button id="nav-next"
                    class="flex cursor-pointer items-center gap-1 px-2 py-1 text-sm font-extrabold text-[#800000] transition-transform hover:scale-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-30"
                    {{ $disasters->count() <= 1 ? 'disabled' : '' }}>
                    <img src="{{ asset('images/left-arrow.webp') }}" alt="" class="w-6 rotate-180">
                </button>
            </div>
        </div>

    </div>

    <script>
        (function() {
            const track = document.getElementById('cards-track');
            const cards = document.querySelectorAll('.disaster-card');
            const tabs = document.querySelectorAll('.nav-tab');
            const title = document.getElementById('nav-title');
            const currentSpan = document.getElementById('nav-current');
            const prevBtn = document.getElementById('nav-prev');
            const nextBtn = document.getElementById('nav-next');
            const total = cards.length;
            let currentIndex = 0;

            function goTo(index) {
                if (index < 0 || index >= total) return;
                currentIndex = index;
                track.style.transform = 'translateX(-' + (currentIndex * (100 / total)) + '%)';

                // Update tab aktif + scroll tab ke tampilan
                tabs.forEach((tab, i) => {
                    const active = i === currentIndex;
                    tab.style.backgroundColor = active ? '#800000' : '#fff';
                    tab.style.color = active ? '#fff' : '#800000';
                    if (active) tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                });

                // Update title
                const card = cards[currentIndex];
                if (title && card) {
                    title.textContent = card.dataset.name;
                }

                // Update counter
                if (currentSpan) {
                    currentSpan.textContent = currentIndex + 1;
                }

                // Update buttons
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex === total - 1;
            }

            // Arrow buttons
            prevBtn.addEventListener('click', () => goTo(currentIndex - 1));
            nextBtn.addEventListener('click', () => goTo(currentIndex + 1));

            // Tab navigation
            tabs.forEach(tab => {
                tab.addEventListener('click', () => goTo(parseInt(tab.dataset.index)));
            });

            // Deep link #slug (dipakai tombol "Mitigasi" di peta bencana)
            if (location.hash) {
                const target = Array.from(tabs).find(t => t.dataset.slug === location.hash.slice(1));
                if (target) goTo(parseInt(target.dataset.index));
            }

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') goTo(currentIndex - 1);
                if (e.key === 'ArrowRight') goTo(currentIndex + 1);
            });

            // Touch swipe: hanya geser kartu bila gerakan lebih horizontal daripada vertikal,
            // supaya tidak bentrok dengan scroll isi kartu.
            let touchStartX = 0;
            let touchStartY = 0;

            const container = document.getElementById('cards-container');
            container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }, {
                passive: true
            });

            container.addEventListener('touchend', (e) => {
                const dx = touchStartX - e.changedTouches[0].screenX;
                const dy = touchStartY - e.changedTouches[0].screenY;

                if (Math.abs(dx) < 50 || Math.abs(dx) <= Math.abs(dy)) return;

                goTo(dx > 0 ? currentIndex + 1 : currentIndex - 1);
            }, {
                passive: true
            });
        })();
    </script>
</body>

</html>
