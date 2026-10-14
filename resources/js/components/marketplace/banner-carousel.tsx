import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

const slides = [
    {
        id: 1,
        title: 'Preview Product Terbaru',
        subtitle: 'Dapatkan penawaran eksklusif untuk civitas Universitas Negeri Malang',
        bg: 'from-[#012060] to-[#023EA6]',
        pattern: '#ffffff',
    },
    {
        id: 2,
        title: 'Guideline Seller',
        subtitle: 'Temukan produk terbaik dari penjual terpercaya di kampus',
        bg: 'from-[#0369A1] to-[#0284C7]',
        pattern: '#ffffff',
    },
    {
        id: 3,
        title: 'Cash Back',
        subtitle: 'Ikuti berbagai event dan pameran yang diselenggarakan di UM',
        bg: 'from-[#065F46] to-[#059669]',
        pattern: '#ffffff',
    },
];

const AUTOPLAY_INTERVAL = 4000;

export default function BannerCarousel() {
    const [current, setCurrent] = useState(0);
    const [isHovered, setIsHovered] = useState(false);
    const [touchStart, setTouchStart] = useState<number | null>(null);
    const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const goTo = useCallback((index: number) => {
        setCurrent((index + slides.length) % slides.length);
    }, []);

    const prev = useCallback(() => goTo(current - 1), [current, goTo]);
    const next = useCallback(() => goTo(current + 1), [current, goTo]);

    // Autoplay
    useEffect(() => {
        if (isHovered) return;
        timerRef.current = setInterval(next, AUTOPLAY_INTERVAL);
        return () => {
            if (timerRef.current) clearInterval(timerRef.current);
        };
    }, [isHovered, next]);

    // Touch swipe
    const onTouchStart = (e: React.TouchEvent) => setTouchStart(e.touches[0].clientX);
    const onTouchEnd = (e: React.TouchEvent) => {
        if (touchStart === null) return;
        const diff = touchStart - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) diff > 0 ? next() : prev();
        setTouchStart(null);
    };

    return (
        <section
            // Menerapkan trik full-bleed di Tailwind untuk memaksa 100% viewport width
            className="relative left-1/2 w-[100vw] -translate-x-1/2 overflow-hidden"
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            onTouchStart={onTouchStart}
            onTouchEnd={onTouchEnd}
            aria-label="Banner promosi"
        >
            {/* Slides */}
            <div
                className="flex transition-transform duration-500 ease-in-out"
                style={{ transform: `translateX(-${current * 100}%)` }}
            >
                {slides.map((slide) => (
                    <div
                        key={slide.id}
                        className={`relative min-w-full bg-gradient-to-r ${slide.bg}`}
                        style={{ height: 'clamp(160px, 28vw, 340px)' }}
                    >
                        {/* Checkerboard pattern overlay */}
                        <svg
                            className="absolute inset-0 size-full opacity-10"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <defs>
                                <pattern id={`checker-${slide.id}`} width="32" height="32" patternUnits="userSpaceOnUse">
                                    <rect width="16" height="16" fill={slide.pattern} />
                                    <rect x="16" y="16" width="16" height="16" fill={slide.pattern} />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill={`url(#checker-${slide.id})`} />
                        </svg>

                        {/* Content */}
                        {/* Ditambahkan max-w-7xl mx-auto agar teks rata kiri mengikuti padding Navbar desktop */}
                        <div className="relative mx-auto flex h-full max-w-7xl items-center px-4 sm:px-6">
                            <div className="max-w-lg">
                                <h2 className="text-2xl font-bold text-white sm:text-3xl md:text-4xl">
                                    {slide.title}
                                </h2>
                                <p className="mt-2 text-sm text-white/80 sm:text-base">
                                    {slide.subtitle}
                                </p>
                                <button className="mt-4 rounded-full bg-white px-5 py-2 text-sm font-semibold text-[#012060] transition hover:bg-white/90 active:scale-95">
                                    Lihat Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Chevron: prev */}
            <button
                onClick={prev}
                aria-label="Slide sebelumnya"
                className="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-black/25 p-1.5 text-white backdrop-blur-sm transition hover:bg-black/40 sm:left-6 sm:p-2"
            >
                <ChevronLeft className="size-5 sm:size-6" />
            </button>

            {/* Chevron: next */}
            <button
                onClick={next}
                aria-label="Slide berikutnya"
                className="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-black/25 p-1.5 text-white backdrop-blur-sm transition hover:bg-black/40 sm:right-6 sm:p-2"
            >
                <ChevronRight className="size-5 sm:size-6" />
            </button>

            {/* Dot pagination */}
            <div className="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5" role="tablist" aria-label="Slide navigation">
                {slides.map((slide, i) => (
                    <button
                        key={slide.id}
                        role="tab"
                        aria-selected={i === current}
                        aria-label={`Slide ${i + 1}`}
                        onClick={() => goTo(i)}
                        className={`h-2 rounded-full transition-all duration-300 ${
                            i === current
                                ? 'w-6 bg-[#0284C7]'
                                : 'w-2 bg-white/50 hover:bg-white/80'
                        }`}
                    />
                ))}
            </div>
        </section>
    );
}
