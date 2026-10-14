import { Link } from '@inertiajs/react';
import LabelSection from '@/components/marketplace/label-section';
import { ArrowRight } from 'lucide-react';

type CategoryItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    href: string;
};

type Props = {
    categories: CategoryItem[];
};

export function CategoriesSection({ categories }: Props) {
    // Fungsi bantuan untuk menentukan class grid per item berdasarkan urutan (index)
    // Desain ini dikhususkan untuk 6 item agar presisi seperti desain UI.
    const displayCategories = categories.slice(0, 6);
    const getGridItemClass = (index: number) => {
        switch (index) {
            case 0:
                // 1. Kartu Kiri Besar (Painting): Lebar 1 kolom, tinggi 2 baris
                return 'md:col-span-1 md:row-span-2 aspect-[3/4] md:aspect-auto';
            case 1:
            case 2:
                // 2 & 3. Kartu Kanan Atas (Cloth & Hand-painted shoes):
                // Masing-masing ambil 1 kolom (dari sisa 2 kolom di kanan)
                return 'md:col-span-1 aspect-[16/10]';
            case 3:
                // 4. Kartu Tengah Bawah (Traditional Printed Works):
                // Membentang 2 kolom penuh di bawah kartu 1 dan 2
                return 'md:col-span-2 aspect-[21/9] md:aspect-auto';
            case 4:
            case 5:
                // 5 & 6. Kartu Bawah Sendiri (Vacuum & Air Purifier):
                // Setengah-setengah layar membentang, karena dia masuk ke baris baru
                // (di luar area 3 kolom utama, kita buat col-span khusus jika gridnya membesar)
                // Namun karena grid utama kita cols-3, cara terbaik agar mereka rata adalah
                // membungkus index 4 dan 5 di luar grid utama, ATAU memodifikasi span-nya.
                // Untuk kesederhanaan di grid cols-3: kita buat yang ini membentang 1.5 kolom (sulit di tailwind)
                // Jadi lebih baik kita buat layout ini spesifik menggunakan grid campuran.
                return '';
            default:
                // Fallback untuk kategori ke-7 dan seterusnya
                return 'md:col-span-1 aspect-[16/10]';
        }
    };

    // Ambil 4 item pertama untuk Grid Atas (Blok asimetris utama)
    const topGridItems = categories.slice(0, 4);

    // Ambil sisanya (item ke-5 dan 6, dst) untuk Grid Bawah (Blok 2 kolom simetris)
    const bottomGridItems = categories.slice(4, 6);

    return (
        <section className="my-10">
            <div className="mb-8">
                <LabelSection title="Shop by Categories" />
            </div>

            {categories.length === 0 ? (
                <div className="py-12 text-center text-neutral-500">
                    Belum ada kategori yang tersedia.
                </div>
            ) : (
                <div className="flex flex-col gap-6">

                    {/* --- BAGIAN ATAS: Grid Asimetris (Kiri Besar, Kanan Atas 2, Kanan Bawah 1 Panjang) --- */}
                    {topGridItems.length > 0 && (
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-3 md:grid-rows-[1fr_1fr]">
                            {topGridItems.map((category, index) => {
                                const isFeatured = index === 0;
                                const className = getGridItemClass(index);

                                return (
                                    <Link
                                        key={category.id}
                                        href={category.href}
                                        className={`group relative flex flex-col justify-end overflow-hidden rounded-[16px] border border-[#cfd4df] bg-neutral-900 transition-all duration-300 hover:shadow-lg ${className}`}
                                    >
                                        {category.image ? (
                                            <img src={category.image} alt={category.name} className="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                        ) : (
                                            <div className="absolute inset-0 bg-gradient-to-br from-blue-900 to-indigo-950" />
                                        )}
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

                                        {/* Konten Teks */}
                                        <div className="relative z-10 p-5 sm:p-7">
                                            <h3 className={`font-bold text-white mb-3 ${isFeatured ? 'text-2xl sm:text-3xl' : 'text-lg sm:text-xl'}`}>
                                                {category.name}
                                            </h3>
                                            <div className="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-xs font-semibold text-[#0242C6] shadow transition group-hover:bg-[#0242C6] group-hover:text-white">
                                                <span>Lihat Selengkapnya</span>
                                                <ArrowRight className="size-3.5" />
                                            </div>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    )}

                    {/* --- BAGIAN BAWAH: Grid 2 Kolom Sejajar (Vacuum & Air Purifier) --- */}
                    {bottomGridItems.length > 0 && (
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {bottomGridItems.map((category) => (
                                <Link
                                    key={category.id}
                                    href={category.href}
                                    className="group relative flex flex-col justify-end overflow-hidden rounded-[16px] border border-[#cfd4df] bg-neutral-900 aspect-[16/8] md:aspect-[21/9] transition-all duration-300 hover:shadow-lg"
                                >
                                    {category.image ? (
                                        <img src={category.image} alt={category.name} className="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                    ) : (
                                        <div className="absolute inset-0 bg-gradient-to-br from-blue-900 to-indigo-950" />
                                    )}
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

                                    <div className="relative z-10 p-5 sm:p-7">
                                        <h3 className="mb-3 text-xl font-bold text-white sm:text-2xl">
                                            {category.name}
                                        </h3>
                                        <div className="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-xs font-semibold text-[#0242C6] shadow transition group-hover:bg-[#0242C6] group-hover:text-white">
                                            <span>Lihat Selengkapnya</span>
                                            <ArrowRight className="size-3.5" />
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}

                </div>
            )}
        </section>
    );
}
