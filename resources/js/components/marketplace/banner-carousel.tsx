import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

const slides = [
    {
        id: 1,
        imagePath: '/images/banner-carousel/banner-1.jpeg', // <--- Path gambar yang baru saja Anda tunjukkan
        alt: 'Banner Preview Product Terbaru',
    },
    {
        id: 2,
        imagePath: '/images/banner-carousel/banner-2.jpeg',
        alt: 'Banner Guideline Seller',
    },
    {
        id: 3,
        imagePath: '/images/banner-carousel/banner-3.jpeg',
        alt: 'Banner Cash Back',
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
            className="relative left-1/2 w-[100vw] -translate-x-1/2 overflow-hidden"
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            onTouchStart={onTouchStart}
            onTouchEnd={onTouchEnd}
            aria-label="Banner promosi"
        >
            {/* Slides Container */}
            <div
                className="flex transition-transform duration-500 ease-in-out"
                style={{ transform: `translateX(-${current * 100}%)` }}
            >
                {slides.map((slide) => (
                    <div
                        key={slide.id}
                        /*
                           PENJELASAN UKURAN:
                           - h-[160px]: Di HP (layar kecil), kita tahan tingginya minimal 160px agar teks di gambar masih terbaca.
                           - sm:h-auto: Di layar lebih besar, tinggi akan otomatis...
                           - sm:aspect-[4/1]: ...mengikuti rasio 4:1 (Lebar 4, Tinggi 1).
                             Jika gambar Anda misal resolusinya 1600x400, gunakan [1600/400] atau [4/1].
                             Jika 1920x600, gunakan [1920/600]. Silakan sesuaikan angkanya!
                        */
                        className="relative min-w-full bg-[#F8F9FA] aspect-[4/1]"
                    >
                        <img
                            src={slide.imagePath}
                            alt={slide.alt}
                            className="h-full w-full object-cover object-center"
                            loading={slide.id === 1 ? 'eager' : 'lazy'}
                        />
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
                                ? 'w-6 bg-white'
                                : 'w-2 bg-white/50 hover:bg-white/80'
                        }`}
                    />
                ))}
            </div>
        </section>
    );
}
