import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type BannerSlide = {
    id: number;
    title?: string;
    subtitle?: string;
    tag?: string;
    imageUrl?: string;
    linkUrl?: string;
    bgColor?: string;
};

const DEFAULT_SLIDES: BannerSlide[] = [
    {
        id: 1,
        title: 'Cakramasta Official Store',
        subtitle: 'Pusat produk & merchandise resmi Universitas Negeri Malang',
        tag: 'PROMO UM',
        linkUrl: '#',
    },
    {
        id: 2,
        title: 'Karya Inovatif Mahasiswa UM',
        subtitle: 'Dukung produk kreatif dan UMKM sivitas akademika',
        tag: 'PRODUK UNGGULAN',
        linkUrl: '#',
    },
    {
        id: 3,
        title: 'Diskon Spesial Semester Baru',
        subtitle: 'Nikmati penawaran khusus untuk seluruh mahasiswa & dosen',
        tag: 'EVENT KAMPUS',
        linkUrl: '#',
    },
];

type BannerCarouselProps = {
    slides?: BannerSlide[];
    autoPlayInterval?: number; // dalam milidetik (default 4000ms = 4 detik)
};

export default function BannerCarousel({
    slides = DEFAULT_SLIDES,
    autoPlayInterval = 4000,
}: BannerCarouselProps) {
    const [currentIndex, setCurrentIndex] = useState(1); // Mulai dari slide ke-2 (seperti di screenshot)
    const [isPaused, setIsPaused] = useState(false);
    const touchStartX = useRef<number | null>(null);

    // Auto-slide setiap beberapa detik
    useEffect(() => {
        if (isPaused || slides.length <= 1) return;

        const timer = setInterval(() => {
            setCurrentIndex((prev) => (prev + 1) % slides.length);
        }, autoPlayInterval);

        return () => clearInterval(timer);
    }, [isPaused, slides.length, autoPlayInterval]);

    const handlePrev = () => {
        setCurrentIndex((prev) => (prev === 0 ? slides.length - 1 : prev - 1));
    };

    const handleNext = () => {
        setCurrentIndex((prev) => (prev + 1) % slides.length);
    };

    const handleTouchStart = (e: React.TouchEvent) => {
        touchStartX.current = e.touches[0].clientX;
    };

    const handleTouchEnd = (e: React.TouchEvent) => {
        if (touchStartX.current === null) return;
        const diffX = touchStartX.current - e.changedTouches[0].clientX;
        if (diffX > 50) {
            handleNext();
        } else if (diffX < -50) {
            handlePrev();
        }
        touchStartX.current = null;
    };

    return (
        <section
            aria-label="Carousel Promosi"
            className="relative w-full overflow-hidden bg-[#F8F9FA] py-5 sm:py-7"
            onMouseEnter={() => setIsPaused(true)}
            onMouseLeave={() => setIsPaused(false)}
        >
            {/* Carousel Container (Center Focused with Peeking Sides) */}
            <div
                className="relative mx-auto w-full max-w-7xl px-2 sm:px-4"
                onTouchStart={handleTouchStart}
                onTouchEnd={handleTouchEnd}
            >
                {/* Track Wrapper */}
                <div className="relative overflow-hidden py-1">
                    <div
                        className="flex items-center transition-transform duration-700 ease-out"
                        style={{
                            // Responsive centering: pada mobile lebar 86% (offset 7%), pada md+ lebar 72% (offset 14%)
                            transform: `translateX(calc(var(--carousel-offset) - ${currentIndex} * (var(--carousel-card-width) + var(--carousel-gap))))`,
                        }}
                    >
                        {slides.map((slide, index) => {
                            const isActive = index === currentIndex;

                            return (
                                <div
                                    key={slide.id}
                                    onClick={() => setCurrentIndex(index)}
                                    className={`group relative shrink-0 cursor-pointer overflow-hidden rounded-2xl border border-neutral-200/80 transition-all duration-500 sm:rounded-3xl ${
                                        isActive
                                            ? 'scale-100 opacity-100 shadow-md ring-1 ring-black/5'
                                            : 'scale-95 opacity-60 hover:opacity-85'
                                    }`}
                                    style={{
                                        width: 'var(--carousel-card-width)',
                                        marginRight: 'var(--carousel-gap)',
                                    }}
                                >
                                    {/* Aspect Ratio Banner Box */}
                                    <div className="relative aspect-[21/9] min-h-[170px] w-full sm:min-h-[220px] md:min-h-[260px] lg:min-h-[300px]">
                                        {slide.imageUrl ? (
                                            <img
                                                src={slide.imageUrl}
                                                alt={slide.title || 'Banner Promosi'}
                                                className="size-full object-cover"
                                            />
                                        ) : (
                                            /* Checkerboard Mockup Pattern (Sesuai Desain Screenshot) */
                                            <div
                                                className="relative size-full"
                                                style={{
                                                    backgroundColor: '#FFFFFF',
                                                    backgroundImage:
                                                        'repeating-conic-gradient(#EDEFF2 0% 25%, #FFFFFF 0% 50%)',
                                                    backgroundSize: '36px 36px',
                                                }}
                                            >
                                                {/* Subtle Info Overlay */}
                                                <div className="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/50 via-transparent to-transparent p-4 sm:p-6 md:p-8">
                                                    {slide.tag && (
                                                        <span className="mb-1 w-fit rounded-full bg-[#012060] px-3 py-1 text-[10px] font-semibold tracking-wider text-white uppercase sm:text-xs">
                                                            {slide.tag}
                                                        </span>
                                                    )}
                                                    {slide.title && (
                                                        <h3 className="text-base font-bold text-white drop-shadow-sm sm:text-xl md:text-2xl">
                                                            {slide.title}
                                                        </h3>
                                                    )}
                                                    {slide.subtitle && (
                                                        <p className="line-clamp-1 text-xs text-white/90 drop-shadow-sm sm:text-sm">
                                                            {slide.subtitle}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Tombol Panah Navigasi Kiri & Kanan (Muncul saat hover di layar besar) */}
                <button
                    type="button"
                    onClick={handlePrev}
                    aria-label="Slide sebelumnya"
                    className="absolute top-1/2 left-3 -translate-y-1/2 rounded-full border border-neutral-200 bg-white/90 p-2 text-neutral-700 shadow-md backdrop-blur-xs transition hover:bg-white hover:text-black sm:left-6 sm:p-2.5"
                >
                    <ChevronLeft className="size-5" />
                </button>
                <button
                    type="button"
                    onClick={handleNext}
                    aria-label="Slide berikutnya"
                    className="absolute top-1/2 right-3 -translate-y-1/2 rounded-full border border-neutral-200 bg-white/90 p-2 text-neutral-700 shadow-md backdrop-blur-xs transition hover:bg-white hover:text-black sm:right-6 sm:p-2.5"
                >
                    <ChevronRight className="size-5" />
                </button>
            </div>

            {/* Pagination Dots (3 Dots di Bawah, Sesuai Screenshot) */}
            <div className="mt-4 flex items-center justify-center gap-2">
                {slides.map((_, index) => {
                    const isActive = index === currentIndex;

                    return (
                        <button
                            key={index}
                            type="button"
                            onClick={() => setCurrentIndex(index)}
                            aria-label={`Buka slide ${index + 1}`}
                            className={`size-2.5 rounded-full transition-all duration-300 ${
                                isActive
                                    ? 'bg-[#0284C7] ring-2 ring-[#0284C7]/30'
                                    : 'bg-neutral-300 hover:bg-neutral-400'
                            }`}
                        />
                    );
                })}
            </div>

            {/* CSS Variables untuk responsif centering dan gap peeking carousel */}
            <style>{`
                :root {
                    --carousel-card-width: 86%;
                    --carousel-offset: 7%;
                    --carousel-gap: 16px;
                }
                @media (min-width: 640px) {
                    :root {
                        --carousel-card-width: 76%;
                        --carousel-offset: 12%;
                        --carousel-gap: 20px;
                    }
                }
                @media (min-width: 1024px) {
                    :root {
                        --carousel-card-width: 70%;
                        --carousel-offset: 15%;
                        --carousel-gap: 28px;
                    }
                }
            `}</style>
        </section>
    );
}

