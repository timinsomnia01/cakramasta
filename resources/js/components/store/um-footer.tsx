import { Calendar, GraduationCap, Mail, MapPin, Phone } from 'lucide-react';

export function UmFooter() {
    return (
        <footer className="bg-[#012060] pb-8">

            {/* ============================================================== */}
            {/* 0. NEW TOP BANNER (LOGO BAR) */}
            {/* ============================================================== */}
            <div className="bg-[#00153F]">

                {/* 👇👇 TEMPAT MENGATUR PADDING BISA DIEDIT DI BARIS INI 👇👇 */}
                {/* - py-6 adalah padding atas-bawah default (24px). */}
                {/* - md:py-8 adalah padding atas-bawah untuk desktop (32px). */}
                {/* - px-4 dan sm:px-6 adalah padding kiri-kanan. */}
                <div className="mx-auto flex max-w-7xl flex-col items-center justify-center gap-12 px-4 py-6 sm:px-6 md:flex-row md:py-8">

                    {/* Logo Kiri (UM) */}
                    <img
                        src="/images/um-excellence.webp"
                        alt="UM Excellence"
                        loading="lazy"
                        className="h-10 w-auto sm:h-12 md:h-14"
                    />

                    {/* Grup Logo Kanan */}
                    <div className="flex flex-wrap justify-center items-center gap-6 sm:gap-8 md:gap-12">
                        <img
                            src="/images/diktisaintek.webp"
                            alt="Diktisaintek"
                            loading="lazy"
                            className="h-8 w-auto sm:h-10 md:h-11"
                        />
                        <img
                            src="/images/ppid.webp"
                            alt="PPID"
                            loading="lazy"
                            className="h-8 w-auto sm:h-10 md:h-11"
                        />
                        <img
                            src="/images/sustainable.webp"
                            alt="Sustainable Development Goals"
                            loading="lazy"
                            className="h-8 w-auto sm:h-10 md:h-11"
                        />
                    </div>
                </div>
            </div>

            {/* ============================================================== */}
            {/* KONTEN FOOTER BAWAHNYA (DIBUNGKUS CONTAINER TERPISAH) */}
            {/* ============================================================== */}
            <div className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">

                {/* 1. TOPBAR / 4 DATA INFO */}
                <div className="mb-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-white sm:text-base">
                    <div className="flex items-center gap-2">
                        <Mail className="size-4 text-[#fff500]" />
                        <a href="mailto:ppid@um.ac.id" className="transition hover:text-white/80">ppid@um.ac.id</a>
                        <span className="opacity-50">|</span>
                        <a href="mailto:humas@um.ac.id" className="transition hover:text-white/80">humas@um.ac.id</a>
                    </div>
                    <div className="flex items-center gap-2">
                        <Phone className="size-4 text-[#fff500]" />
                        <a href="tel:+62341551312" className="transition hover:text-white/80">(0341) 551312</a>
                    </div>
                    <div className="flex items-center gap-2">
                        <GraduationCap className="size-4 text-[#fff500]" />
                        <span>Prof. Dr. Hariyono, M.Pd</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Calendar className="size-4 text-[#fff500]" />
                        <span>18 Oktober 1954</span>
                    </div>
                </div>

                {/* 2. KAMPUS CARDS */}
                <div className="mb-14 grid grid-cols-1 gap-8 md:grid-cols-3 lg:gap-11">
                    {/* Kampus 1 */}
                    <div className="group flex flex-col overflow-hidden rounded-[10px]">
                        <div className="aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-[#22345f] to-[#16264a]">
                            <img src="/images/kampus-1.webp" alt="Kampus 01" loading="lazy" className="size-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        </div>
                        <div className="relative flex flex-1 items-start gap-3 overflow-hidden p-5 transition-colors duration-500 group-hover:bg-[#1168db]">
                            <MapPin className="mt-1 size-5 shrink-0 text-white" />
                            <div className="mt-1 h-[40%] min-h-[40px] w-0.5 shrink-0 border border-white bg-white"></div>
                            <div className="relative z-10 text-white">
                                <p className="mb-1 text-[22px] font-bold">Kampus 01</p>
                                <p className="text-sm font-normal leading-relaxed text-white/90">
                                    Jl. Cakrawala No.5, Sumbersari,<br />
                                    Kec. Lowokwaru, Kota Malang,<br />
                                    Jawa Timur 65145
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Kampus 2 */}
                    <div className="group flex flex-col overflow-hidden rounded-[10px]">
                        <div className="aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-[#22345f] to-[#16264a]">
                            <img src="/images/kampus-2-1.webp" alt="Kampus 02" loading="lazy" className="size-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        </div>
                        <div className="relative flex flex-1 items-start gap-3 overflow-hidden p-5 transition-colors duration-500 group-hover:bg-[#1168db]">
                            <MapPin className="mt-1 size-5 shrink-0 text-white" />
                            <div className="mt-1 h-[40%] min-h-[40px] w-0.5 shrink-0 border border-white bg-white"></div>
                            <div className="relative z-10 text-white">
                                <p className="mb-1 text-[22px] font-bold">Kampus 02</p>
                                <p className="text-sm font-normal leading-relaxed text-white/90">
                                    Jl. Ki Ageng Gribig No.45, Madyopuro,<br />
                                    Kec. Kedungkandang, Kota Malang,<br />
                                    Jawa Timur 65139
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Kampus 3 */}
                    <div className="group flex flex-col overflow-hidden rounded-[10px]">
                        <div className="aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-[#22345f] to-[#16264a]">
                            <img src="/images/kampus-3.webp" alt="Kampus 03" loading="lazy" className="size-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        </div>
                        <div className="relative flex flex-1 items-start gap-3 overflow-hidden p-5 transition-colors duration-500 group-hover:bg-[#1168db]">
                            <MapPin className="mt-1 size-5 shrink-0 text-white" />
                            <div className="mt-1 h-[40%] min-h-[40px] w-0.5 shrink-0 border border-white bg-white"></div>
                            <div className="relative z-10 text-white">
                                <p className="mb-1 text-[22px] font-bold">Kampus 03</p>
                                <p className="text-sm font-normal leading-relaxed text-white/90">
                                    Jl. Ir. Soekarno No.1, Kepanjen Lor,<br />
                                    Kec. Kepanjenkidul, Kota Blitar,<br />
                                    Jawa Timur 65117
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 3. BIG MENU CARD */}
                <div className="relative mb-10 overflow-hidden rounded-[20px] p-8 md:p-14">
                    {/* Layer 1: Gambar Maskot */}
                    <div
                        className="absolute inset-0 z-0 bg-cover bg-bottom bg-no-repeat"
                        style={{ backgroundImage: "url('https://um.ac.id/wp-content/uploads/2026/03/9c1b5816c00d6e3cd04854792237040393f74333-scaled-e1776176101516.png')" }}
                    ></div>

                    {/* Layer 2: Overlay Gradien */}
                    <div className="absolute inset-0 z-0 bg-gradient-to-b from-[#012060] to-[#0b1529]/80"></div>

                    <div className="relative z-10 grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="flex flex-col gap-3">
                            <div className="mb-2 h-0.5 w-full bg-[#8d5d76]"></div>
                            <a href="https://sekretariat.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Sekretariat Universitas</a>
                            <a href="https://wbs.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Satgas PPKS</a>
                            <a href="https://spi.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Badan Pengawas Internal</a>
                            <a href="https://bpm.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Badan Pengembangan Usaha dan Dana Abadi</a>
                            <a href="https://lp2m.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Lembaga Penelitian dan Pengabdian Kepada Masyarakat</a>
                            <a href="https://lppp.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Lembaga Pengembangan Pendidikan dan Pengajaran</a>
                        </div>
                        <div className="flex flex-col gap-3">
                            <div className="mb-2 h-0.5 w-full bg-[#8d5d76]"></div>
                            <a href="https://akademik.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Pendidikan</a>
                            <a href="https://kemahasiswaan.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Kemahasiswaan & Alumni</a>
                            <a href="https://psdmk.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Sumber Daya Manusia & Keuangan</a>
                            <a href="https://dspa.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Sarana, Prasarana, & Aset</a>
                            <a href="https://inovasi.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Inovasi</a>
                            <a href="https://ppdh.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">Direktorat Perencanaan, Pemeringkatan, Data & Humas</a>
                        </div>
                        <div className="flex flex-col gap-3">
                            <div className="mb-2 h-0.5 w-full bg-[#8d5d76]"></div>
                            <a href="https://lib.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Perpustakaan</a>
                            <a href="https://lab-pancasila.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Laboratorium Pancasila</a>
                            <a href="https://ulpbj.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Layanan Pengadaan</a>
                            <a href="https://oia.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Kantor Urusan Internasional</a>
                            <a href="https://lsp.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Lembaga Sertifikasi Profesi</a>
                            <a href="https://psbbi.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Pusat Studi Bahasa dan Budaya Indonesia</a>
                            <a href="https://integratedlab.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Laboratorium Terpadu</a>
                        </div>
                        <div className="flex flex-col gap-3">
                            <div className="mb-2 h-0.5 w-full bg-[#8d5d76]"></div>
                            <a href="https://psl.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Pengelola Sekolah Laboratorium UM</a>
                            <a href="https://pakb.um.ac.id/" className="text-sm leading-relaxed text-white underline">UPT Pelaksana Akademik di Kampus Blitar</a>
                            <a href="https://lpa.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Laboratorium Pendidikan Agama</a>
                            <a href="https://publika.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Publikasi Ilmiah</a>
                            <a href="https://layanan-kesehatan.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Layanan Kesehatan</a>
                            <a href="https://ptik.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Pusat Teknologi Informasi dan Komunikasi</a>
                            <a href="https://bb.um.ac.id/" className="text-sm leading-relaxed text-white hover:underline">UPT Balai Bahasa</a>
                        </div>
                    </div>
                </div>

                {/* 4. BOTTOM / COPYRIGHT */}
                <div className="flex flex-col items-center justify-between gap-4 rounded-none bg-[#00153F] px-6 py-4 sm:flex-row sm:rounded-full md:px-8">
                    <p className="text-center text-[15px] text-white">© 2026 Universitas Negeri Malang</p>
                    <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        <a href="#" className="text-[15px] text-white hover:underline">Kebijakan Privasi</a>
                        <a href="#" className="text-[15px] text-white hover:underline">Syarat Penggunaan</a>
                        <a href="#" className="text-[15px] text-white hover:underline">Peta Situs</a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
