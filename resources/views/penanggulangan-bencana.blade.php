<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penanggulangan Bencana - BENCANA ALAM</title>

    @vite(['resources/css/app.css'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="flex h-dvh w-screen justify-center overflow-hidden bg-black font-sans">
    <div class="max-w-110 relative flex h-full w-full flex-col overflow-hidden shadow-2xl">

        <!-- Header -->
        <div class="relative z-10 flex w-full shrink-0 items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
            <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PENANGGULANGAN BENCANA</h1>
        </div>

        <!-- Cards Container -->
        <div id="cards-container" class="relative flex-1 overflow-hidden">
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

            <!-- Dots -->
            <div class="mb-2 flex h-4 items-center justify-center gap-2">
                @foreach ($disasters as $index => $disaster)
                    <button class="nav-dot rounded-full transition-all duration-200" data-index="{{ $index }}"
                        data-active="{{ $index === 0 ? 'true' : 'false' }}"
                        style="background-color: {{ $index === 0 ? '#800000' : 'rgba(128,0,0,0.4)' }}; width: {{ $index === 0 ? '12px' : '8px' }}; height: {{ $index === 0 ? '12px' : '8px' }};"
                        aria-label="{{ $disaster->name }}">
                    </button>
                @endforeach
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
            const dots = document.querySelectorAll('.nav-dot');
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

                // Update dots
                dots.forEach((dot, i) => {
                    if (i === currentIndex) {
                        dot.style.backgroundColor = '#800000';
                        dot.style.width = '12px';
                        dot.style.height = '12px';
                        dot.dataset.active = 'true';
                    } else {
                        dot.style.backgroundColor = 'rgba(128,0,0,0.4)';
                        dot.style.width = '8px';
                        dot.style.height = '8px';
                        dot.dataset.active = 'false';
                    }
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

            // Dot navigation
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    goTo(parseInt(dot.dataset.index));
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') goTo(currentIndex - 1);
                if (e.key === 'ArrowRight') goTo(currentIndex + 1);
            });

            // Touch swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            const container = document.getElementById('cards-container');
            container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, {
                passive: true
            });

            container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        goTo(currentIndex + 1);
                    } else {
                        goTo(currentIndex - 1);
                    }
                }
            }, {
                passive: true
            });
        })();
    </script>
</body>

</html>
