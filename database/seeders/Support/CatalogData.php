<?php

namespace Database\Seeders\Support;

/**
 * Data katalog UM-Mart: kategori, sub kategori, produk, penjual, ulasan, dan halaman footer.
 *
 * Format produk: [nama, harga (Rp), berat (gram), deskripsi, varian|null]
 * Format varian: ['Nama varian' => harga]
 */
class CatalogData
{
    /**
     * @return list<array{name: string, slug: string, description: string, subs: list<array{name: string, description: string, products: list<array{0: string, 1: int, 2: int, 3: string, 4: array<string, int>|null}>}>}>
     */
    public static function categories(): array
    {
        return [
            [
                'name' => 'Kain',
                'slug' => 'kain',
                'description' => 'Batik, kain polos, dan tenun tradisional untuk busana maupun kerajinan.',
                'subs' => [
                    [
                        'name' => 'Batik',
                        'description' => 'Batik tulis, cap, dan printing dari berbagai daerah.',
                        'products' => [
                            ['Kain Batik Tulis Lasem Motif Sekar Jagad 2 Meter', 385000, 400, 'Batik tulis asli dikerjakan perajin Lasem dengan pewarna alami. Bahan katun primissima halus, adem, dan tidak mudah luntur setelah dicuci.', ['Coklat Soga' => 385000, 'Biru Indigo' => 405000]],
                            ['Kain Batik Cap Motif Parang Rusak 2,25 Meter', 145000, 350, 'Batik cap motif parang klasik dengan warna sogan. Cocok untuk kemeja, dress, atau seragam acara kantor.', null],
                            ['Kain Batik Printing Mega Mendung 2 Meter', 85000, 300, 'Batik printing motif mega mendung warna cerah. Bahan katun halus, harga terjangkau untuk seragam kelompok.', ['Biru' => 85000, 'Merah Maroon' => 85000, 'Hijau Tosca' => 85000]],
                            ['Sarimbit Batik Couple Kemeja dan Dress Motif Kawung', 265000, 600, 'Satu set sarimbit batik motif kawung untuk pasangan. Jahitan rapi, dilengkapi furing pada bagian dress.', ['Kemeja M + Dress M' => 265000, 'Kemeja L + Dress L' => 275000, 'Kemeja XL + Dress XL' => 285000]],
                        ],
                    ],
                    [
                        'name' => 'Kain Polos',
                        'description' => 'Kain polos katun, drill, satin, dan linen per meter.',
                        'products' => [
                            ['Kain Katun Jepang Polos Lebar 150 cm per Meter', 38000, 250, 'Katun Jepang tebal sedang, adem dan jatuh. Cocok untuk kemeja, gamis, tunik, dan sprei. Pembelian minimal 1 meter, dipotong menyambung.', ['Putih Tulang' => 38000, 'Hitam' => 38000, 'Navy' => 38000, 'Maroon' => 38000]],
                            ['Kain Drill Tebal untuk Seragam per Meter', 32000, 350, 'Kain drill tebal dan kuat, populer untuk seragam kerja, celana, dan jaket. Tidak mudah kusut.', ['Hitam' => 32000, 'Khaki' => 32000, 'Abu-abu' => 32000]],
                            ['Kain Satin Velvet Premium per Meter', 45000, 220, 'Satin velvet mengilap dengan jatuhan lembut. Pas untuk gaun pesta, kerudung, dan hiasan pelaminan.', null],
                            ['Kain Linen Rami Natural per Meter', 58000, 240, 'Linen rami tekstur natural, sejuk dipakai harian. Lebar kain 140 cm.', ['Krem' => 58000, 'Coklat Susu' => 58000]],
                        ],
                    ],
                    [
                        'name' => 'Tenun',
                        'description' => 'Tenun ikat dan sarung tenun hasil perajin Nusantara.',
                        'products' => [
                            ['Kain Tenun Ikat Troso Jepara Motif Daun 2 Meter', 275000, 450, 'Tenun ikat Troso dibuat dengan alat tenun bukan mesin (ATBM). Warna kuat dan cocok dijadikan blazer, outer, atau taplak.', null],
                            ['Selendang Tenun Gedog Tuban', 165000, 300, 'Selendang tenun gedog Tuban pewarna alam, ukuran 190 x 60 cm. Bisa untuk kebaya atau hiasan dinding.', null],
                            ['Sarung Tenun Goyor Gresik Dewasa', 210000, 500, 'Sarung tenun goyor khas Gresik, bahan halus dan tidak panas. Cocok untuk ibadah dan acara adat.', ['Hijau Lumut' => 210000, 'Biru Dongker' => 210000, 'Coklat Tua' => 215000]],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Kaos, kemeja, hijab, tas, dan aksesoris untuk kebutuhan harian mahasiswa.',
                'subs' => [
                    [
                        'name' => 'Kaos dan Kemeja',
                        'description' => 'Pakaian atasan untuk pria dan wanita.',
                        'products' => [
                            ['Kaos Polos Cotton Combed 30s Unisex', 59000, 200, 'Kaos polos cotton combed 30s, adem dan tidak mudah melar. Jahitan rantai di bahu agar awet.', ['S' => 59000, 'M' => 59000, 'L' => 59000, 'XL' => 62000, 'XXL' => 65000]],
                            ['Kaos Oversize Sablon Tugu Malang', 89000, 260, 'Kaos oversize dengan sablon plastisol bergambar Tugu Malang. Bahan cotton combed 24s, tebal dan nyaman.', ['M' => 89000, 'L' => 89000, 'XL' => 92000]],
                            ['Kemeja Flanel Kotak-kotak Lengan Panjang', 119000, 380, 'Kemeja flanel lembut, cocok dipakai sebagai outer. Kancing kuat dan potongan regular fit.', ['Merah Hitam' => 119000, 'Hijau Hitam' => 119000, 'Biru Navy' => 119000]],
                            ['Hoodie Fleece Polos Unisex', 135000, 550, 'Hoodie fleece tebal bagian dalam lembut, dilengkapi kantong kanguru dan tali serut.', ['Hitam' => 135000, 'Abu Misty' => 135000, 'Navy' => 135000]],
                        ],
                    ],
                    [
                        'name' => 'Hijab dan Aksesoris',
                        'description' => 'Hijab segi empat, pashmina, dan aksesoris pelengkap.',
                        'products' => [
                            ['Hijab Segi Empat Voal Ultrafine Laser Cut 110 cm', 49000, 120, 'Voal ultrafine dengan potongan laser cut rapi, tidak perlu dijahit tepi. Mudah dibentuk dan tidak menerawang.', ['Dusty Pink' => 49000, 'Mocca' => 49000, 'Sage' => 49000, 'Hitam' => 49000]],
                            ['Pashmina Instan Ceruty Baby Doll', 39000, 150, 'Pashmina instan dengan tali karet di bagian dalam sehingga mudah dipakai. Bahan ceruty baby doll jatuh dan adem.', null],
                            ['Bros Hijab Mutiara Set Isi 3', 25000, 50, 'Set 3 bros mutiara dengan peniti pengaman. Ringan dan tidak merusak kain.', null],
                        ],
                    ],
                    [
                        'name' => 'Tas dan Dompet',
                        'description' => 'Ransel, tote bag, dan dompet.',
                        'products' => [
                            ['Tas Ransel Laptop Anti Air 15 Inch', 189000, 750, 'Ransel dengan kompartemen laptop empuk, bahan waterproof, dan port USB untuk powerbank. Cocok untuk kuliah dan kerja.', ['Hitam' => 189000, 'Abu-abu' => 189000, 'Navy' => 189000]],
                            ['Tote Bag Kanvas Resleting', 55000, 280, 'Tote bag kanvas tebal dengan resleting dan kantong dalam. Muat buku A4 dan laptop 14 inch.', ['Krem' => 55000, 'Hitam' => 55000]],
                            ['Dompet Lipat Kulit Sapi Asli Pria', 165000, 150, 'Dompet kulit sapi asli dengan 8 slot kartu dan 2 ruang uang. Dijahit tangan oleh perajin Magetan.', null],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Makanan dan Minuman',
                'slug' => 'makanan-minuman',
                'description' => 'Camilan, kopi, dan oleh-oleh khas Malang dari UMKM lokal.',
                'subs' => [
                    [
                        'name' => 'Makanan Ringan',
                        'description' => 'Keripik dan kue kering untuk teman belajar dan berkumpul.',
                        'products' => [
                            ['Keripik Tempe Sanan Khas Malang', 28000, 270, 'Keripik tempe tipis renyah dari sentra tempe Sanan. Digoreng dengan minyak bersih, tanpa pengawet.', ['Original 250 gr' => 28000, 'Pedas Daun Jeruk 250 gr' => 29000, 'Original 500 gr' => 52000]],
                            ['Keripik Apel Malang Batu', 35000, 220, 'Keripik apel Manalagi asli Batu dengan proses vakum frying sehingga warna dan rasa tetap alami.', null],
                            ['Keripik Usus Ayam Renyah', 22000, 220, 'Keripik usus ayam gurih dan renyah, dikemas aluminium foil agar tetap garing.', ['Original' => 22000, 'Balado' => 23000]],
                            ['Kue Kering Nastar Premium Toples 500 gr', 85000, 650, 'Nastar selai nanas buatan sendiri dengan mentega premium. Cocok untuk hampers dan suguhan tamu.', null],
                        ],
                    ],
                    [
                        'name' => 'Minuman',
                        'description' => 'Kopi lokal, teh herbal, dan minuman tradisional.',
                        'products' => [
                            ['Kopi Arabika Gunung Kawi Roasted 200 gr', 95000, 230, 'Arabika petik merah dari lereng Gunung Kawi, medium roast dengan aroma cokelat dan karamel.', ['Biji' => 95000, 'Bubuk Kasar (French Press)' => 95000, 'Bubuk Halus (Tubruk)' => 95000]],
                            ['Kopi Robusta Dampit 250 gr', 55000, 280, 'Robusta Dampit full body dengan rasa pahit mantap, cocok untuk kopi susu dan tubruk.', null],
                            ['Teh Rosella Kering 100 gr', 30000, 120, 'Bunga rosella kering tanpa pemanis tambahan. Seduh dengan air panas, nikmat dingin maupun hangat.', null],
                            ['Wedang Uwuh Instan Isi 10 Sachet', 24000, 180, 'Racikan rempah jahe, secang, dan kayu manis dalam sachet siap seduh.', null],
                        ],
                    ],
                    [
                        'name' => 'Oleh-oleh Malang',
                        'description' => 'Buah tangan khas Kota Malang dan Batu.',
                        'products' => [
                            ['Sambal Pecel Khas Malang 250 gr', 28000, 280, 'Sambal pecel kacang dengan daun jeruk dan kencur. Siap diencerkan, awet 3 bulan.', null],
                            ['Dodol Apel Malang 250 gr', 32000, 270, 'Dodol apel lembut tanpa pengawet, dikemas satuan agar higienis.', null],
                            ['Paket Hampers Oleh-oleh Khas Malang', 175000, 1500, 'Hampers berisi keripik tempe, keripik apel, dodol apel, sambal pecel, dan kopi Gunung Kawi dalam kotak rapi.', null],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Elektronik',
                'slug' => 'elektronik',
                'description' => 'Aksesoris gadget, lampu LED, dan perlengkapan komputer.',
                'subs' => [
                    [
                        'name' => 'Aksesoris HP',
                        'description' => 'Kabel, powerbank, dan perlengkapan ponsel.',
                        'products' => [
                            ['Kabel Data USB-C Fast Charging 65W', 45000, 80, 'Kabel USB-C ke USB-C 65W dengan lapisan nilon anyaman. Mendukung transfer data 480 Mbps.', ['1 Meter' => 45000, '2 Meter' => 59000]],
                            ['Powerbank 10.000 mAh Slim Dual Output', 165000, 220, 'Powerbank tipis dengan dua port output dan indikator digital. Aman untuk dibawa naik pesawat.', ['Hitam' => 165000, 'Putih' => 165000]],
                            ['Phone Holder Tripod Mini Fleksibel', 59000, 180, 'Tripod mini dengan penjepit HP 360 derajat, cocok untuk konten dan video call.', null],
                        ],
                    ],
                    [
                        'name' => 'Lampu dan LED',
                        'description' => 'Lampu hias, lampu belajar, dan bohlam hemat energi.',
                        'products' => [
                            ['Lampu LED Strip RGB 5 Meter dengan Remote', 78000, 250, 'LED strip warna-warni 16 warna dengan 4 mode, mudah ditempel di dinding atau meja belajar.', null],
                            ['Lampu Tidur LED Sentuh USB', 42000, 150, 'Lampu meja mini dengan 3 tingkat kecerahan, cukup dicolokkan ke powerbank atau laptop.', ['Putih Hangat' => 42000, 'Putih Dingin' => 42000]],
                            ['Bohlam LED Hemat Energi 12 Watt Isi 3', 48000, 270, 'Bohlam LED 12 watt cahaya putih, setara lampu pijar 75 watt. Garansi 6 bulan.', null],
                        ],
                    ],
                    [
                        'name' => 'Komputer dan Periferal',
                        'description' => 'Mouse, flashdisk, hub USB, dan stand laptop.',
                        'products' => [
                            ['Mouse Wireless 2,4 GHz Silent Click', 69000, 100, 'Mouse nirkabel dengan klik senyap, cocok untuk perpustakaan dan ruang kelas. Baterai AA tahan hingga 12 bulan.', ['Hitam' => 69000, 'Abu-abu' => 69000]],
                            ['Flashdisk 64 GB USB 3.0', 75000, 20, 'Flashdisk kecepatan baca hingga 100 MB/s, casing logam tahan benturan.', null],
                            ['Hub USB 4 Port USB 3.0', 55000, 70, 'USB hub 4 port dengan kabel 30 cm. Plug and play, mendukung Windows dan macOS.', null],
                            ['Stand Laptop Aluminium Lipat', 98000, 280, 'Stand laptop 7 tingkat ketinggian, lipat ringkas, kompatibel hingga 17 inch.', null],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Alat Tulis dan Kuliah',
                'slug' => 'alat-tulis-kuliah',
                'description' => 'Perlengkapan menulis, kertas, dan peralatan praktikum.',
                'subs' => [
                    [
                        'name' => 'Alat Tulis',
                        'description' => 'Pulpen, stabilo, dan alat tulis harian.',
                        'products' => [
                            ['Pulpen Gel 0,5 mm Hitam Isi 12', 36000, 150, 'Pulpen gel dengan tinta hitam pekat dan cepat kering, ujung 0,5 mm untuk tulisan rapi.', null],
                            ['Highlighter Pastel Set 6 Warna', 29000, 100, 'Stabilo pastel dua ujung dengan warna lembut yang tidak tembus ke halaman belakang.', null],
                            ['Pensil Mekanik 0,5 mm Set dengan Isi Ulang', 24000, 90, 'Set 2 pensil mekanik, 2 tabung isi, dan penghapus cadangan.', null],
                        ],
                    ],
                    [
                        'name' => 'Kertas dan Buku Catatan',
                        'description' => 'Kertas HVS, buku tulis, dan binder.',
                        'products' => [
                            ['Kertas HVS A4 70 gsm 1 Rim', 52000, 2300, 'Kertas HVS 500 lembar, putih bersih, aman untuk printer laser maupun inkjet.', null],
                            ['Buku Tulis Folio Bergaris 100 Lembar Isi 5', 48000, 900, 'Buku tulis folio sampul tebal dan kertas 58 gsm yang tidak tembus.', null],
                            ['Binder Notebook A5 dengan Isi 100 Lembar', 55000, 320, 'Binder A5 dengan 6 ring, termasuk kertas bergaris dan pembatas halaman.', ['Hijau Sage' => 55000, 'Biru Dongker' => 55000, 'Pink Pastel' => 55000]],
                        ],
                    ],
                    [
                        'name' => 'Perlengkapan Praktikum',
                        'description' => 'Jas lab, kacamata safety, dan kalkulator.',
                        'products' => [
                            ['Jas Laboratorium Lengan Panjang Putih', 98000, 450, 'Jas lab bahan drill halus dengan 3 kantong dan kancing tekan. Standar praktikum kampus.', ['S' => 98000, 'M' => 98000, 'L' => 98000, 'XL' => 105000]],
                            ['Kacamata Safety Lab Anti Embun', 35000, 60, 'Kacamata pelindung bening anti gores dan anti embun, bisa dipakai di atas kacamata biasa.', null],
                            ['Kalkulator Scientific 240 Fungsi', 135000, 120, 'Kalkulator ilmiah dengan 240 fungsi, layar dua baris, dan penutup geser.', null],
                            ['Set Penggaris Teknik 4 Pcs', 27000, 130, 'Set penggaris segitiga 30/60, 45, busur derajat, dan penggaris 30 cm dari akrilik.', null],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Kerajinan',
                'slug' => 'kerajinan',
                'description' => 'Souvenir, dekorasi rumah, dan kerajinan kayu karya perajin lokal.',
                'subs' => [
                    [
                        'name' => 'Souvenir',
                        'description' => 'Cendera mata khas Malang untuk dibawa pulang.',
                        'products' => [
                            ['Gantungan Kunci Miniatur Tugu Malang Kayu', 12000, 30, 'Gantungan kunci dari kayu mahoni dengan ukiran laser. Ringan dan awet.', null],
                            ['Mug Keramik Motif Batik', 35000, 400, 'Mug keramik 350 ml dengan motif batik parang, aman untuk microwave dan mesin cuci piring.', ['Parang Biru' => 35000, 'Kawung Coklat' => 35000]],
                            ['Magnet Kulkas Topeng Malangan', 15000, 40, 'Magnet kulkas berbentuk topeng Malangan mini, dilukis tangan.', null],
                        ],
                    ],
                    [
                        'name' => 'Dekorasi Rumah',
                        'description' => 'Hiasan dinding, lampu hias, dan vas.',
                        'products' => [
                            ['Hiasan Dinding Macrame Diameter 40 cm', 85000, 250, 'Hiasan dinding macrame tali katun dengan ranting kayu. Dirajut manual oleh perajin Batu.', null],
                            ['Lampu Hias Anyaman Bambu 30 cm', 95000, 350, 'Kap lampu anyaman bambu dengan cahaya hangat. Kabel dan fitting sudah termasuk.', null],
                            ['Vas Bunga Keramik Minimalis', 68000, 600, 'Vas keramik glasir matte dengan tinggi 20 cm, cocok untuk bunga kering.', ['Putih' => 68000, 'Terakota' => 68000, 'Hijau Zaitun' => 68000]],
                        ],
                    ],
                    [
                        'name' => 'Kerajinan Kayu',
                        'description' => 'Topeng, talenan, dan nampan dari kayu pilihan.',
                        'products' => [
                            ['Topeng Malangan Kayu Sengon 20 cm', 225000, 450, 'Topeng Malangan dipahat dan dilukis tangan oleh perajin Tumpang. Cocok untuk hiasan dinding.', null],
                            ['Talenan Kayu Jati 30x20 cm', 79000, 650, 'Talenan jati tebal 2 cm, permukaan halus dan tahan pisau. Sudah dilapisi minyak food grade.', null],
                            ['Nampan Kayu Mahoni Ukir', 98000, 700, 'Nampan saji mahoni dengan ukiran sulur, ukuran 40 x 25 cm.', null],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Rumah dan Taman',
                'slug' => 'rumah-taman',
                'description' => 'Tanaman hias, perlengkapan berkebun, dan peralatan dapur.',
                'subs' => [
                    [
                        'name' => 'Tanaman Hias',
                        'description' => 'Tanaman hias indoor siap pajang.',
                        'products' => [
                            ['Monstera Deliciosa Pot 15 cm', 85000, 1200, 'Monstera sehat dengan 4 sampai 6 daun, ditanam di pot plastik dan media siap pakai. Dikemas aman untuk pengiriman.', null],
                            ['Paket Sukulen Mix 5 Pot Mini', 95000, 900, 'Lima jenis sukulen berbeda dalam pot mini, cocok untuk meja kerja.', null],
                            ['Lidah Mertua Sansevieria Pot Semen', 65000, 1500, 'Sansevieria tahan kering, penyaring udara alami, dalam pot semen estetik.', null],
                        ],
                    ],
                    [
                        'name' => 'Perlengkapan Berkebun',
                        'description' => 'Pot, media tanam, dan pupuk.',
                        'products' => [
                            ['Pot Plastik Tanaman Set Isi 10', 45000, 800, 'Set pot plastik diameter 15 cm dengan lubang drainase dan alas.', null],
                            ['Media Tanam Siap Pakai Campuran 5 kg', 25000, 5000, 'Campuran tanah, sekam bakar, dan kompos yang gembur dan siap pakai untuk tanaman hias.', null],
                            ['Pupuk Organik Cair 500 ml', 38000, 600, 'Pupuk organik cair untuk pertumbuhan daun dan akar. Dosis 5 ml per liter air.', null],
                        ],
                    ],
                    [
                        'name' => 'Peralatan Dapur',
                        'description' => 'Wadah makan, botol minum, dan rak.',
                        'products' => [
                            ['Set Wadah Makan Kedap Udara Isi 5', 89000, 900, 'Lima wadah dengan penutup kedap udara, aman untuk microwave dan freezer, bebas BPA.', null],
                            ['Botol Minum Tritan 1 Liter', 55000, 280, 'Botol minum tritan tahan jatuh dengan penanda jam minum dan tali tenteng.', ['Biru Langit' => 55000, 'Hijau Mint' => 55000, 'Abu-abu' => 55000]],
                            ['Rak Piring Stainless Susun 2', 175000, 2800, 'Rak piring dua tingkat stainless anti karat dengan penampung air dan tempat sendok.', null],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Penjual beserta kategori utama yang dijual. Penjual berstatus pending/rejected tidak punya produk.
     *
     * @return list<array{owner: string, store: string, description: string, category: string|null, district: string, status: string, reason?: string}>
     */
    public static function sellers(): array
    {
        return [
            ['owner' => 'Sri Wahyuningsih', 'store' => 'Batik Sekar Jagad Malang', 'description' => 'Pusat batik tulis dan cap dari perajin Lasem, Pekalongan, dan Solo. Melayani pesanan seragam dan sarimbit.', 'category' => 'kain', 'district' => 'Klojen', 'status' => 'approved'],
            ['owner' => 'Agus Santoso', 'store' => 'Toko Kain Barokah', 'description' => 'Menyediakan kain polos dan tenun per meter dengan harga grosir untuk penjahit dan mahasiswa tata busana.', 'category' => 'kain', 'district' => 'Blimbing', 'status' => 'approved'],
            ['owner' => 'Eko Prasetyo', 'store' => 'Arek Kaos Kreatif', 'description' => 'Kaos sablon dan polos berkualitas, terima pesanan custom untuk himpunan dan kepanitiaan.', 'category' => 'fashion', 'district' => 'Lowokwaru', 'status' => 'approved'],
            ['owner' => 'Zahra Nurmala', 'store' => 'Zahra Hijab Collection', 'description' => 'Hijab, pashmina, dan aksesoris muslimah dengan model terbaru setiap bulan.', 'category' => 'fashion', 'district' => 'Sukun', 'status' => 'approved'],
            ['owner' => 'Lastri Handayani', 'store' => 'Dapur Bu Lastri', 'description' => 'Camilan rumahan dan oleh-oleh khas Malang tanpa pengawet, dibuat segar setiap pesanan.', 'category' => 'makanan-minuman', 'district' => 'Kedungkandang', 'status' => 'approved'],
            ['owner' => 'Slamet Riyadi', 'store' => 'Kopi Gunung Kawi Slamet', 'description' => 'Roastery kecil kopi arabika dan robusta dari petani Gunung Kawi dan Dampit.', 'category' => 'makanan-minuman', 'district' => 'Lowokwaru', 'status' => 'approved'],
            ['owner' => 'Budi Hartono', 'store' => 'Malang Gadget Corner', 'description' => 'Aksesoris HP dan periferal komputer original dengan garansi toko.', 'category' => 'elektronik', 'district' => 'Klojen', 'status' => 'approved'],
            ['owner' => 'Wahyu Kurniawan', 'store' => 'Lampu Kreasi Nusantara', 'description' => 'Spesialis lampu LED dan lampu hias untuk kos, kafe, dan acara kampus.', 'category' => 'elektronik', 'district' => 'Blimbing', 'status' => 'approved'],
            ['owner' => 'Retno Dwi Astuti', 'store' => 'ATK Kampus Mandiri', 'description' => 'Alat tulis, kertas, dan perlengkapan praktikum lengkap, dekat kampus.', 'category' => 'alat-tulis-kuliah', 'district' => 'Lowokwaru', 'status' => 'approved'],
            ['owner' => 'Mulyadi', 'store' => 'Kriya Kayu Tumpang', 'description' => 'Perajin topeng Malangan dan kerajinan kayu dari Tumpang, menerima pesanan souvenir wisuda.', 'category' => 'kerajinan', 'district' => 'Kedungkandang', 'status' => 'approved'],
            ['owner' => 'Rina Marlina', 'store' => 'Rumah Hijau Batu', 'description' => 'Tanaman hias dan perlengkapan berkebun dari nursery Kota Batu.', 'category' => 'rumah-taman', 'district' => 'Sukun', 'status' => 'approved'],
            ['owner' => 'Joko Susilo', 'store' => 'Sepatu Bekas Layak Pakai Malang', 'description' => 'Menjual sepatu thrift pilihan dengan kondisi layak pakai.', 'category' => null, 'district' => 'Lowokwaru', 'status' => 'pending'],
            ['owner' => 'Dedi Kurniawan', 'store' => 'Murah Meriah Online', 'description' => 'Aneka barang murah.', 'category' => null, 'district' => 'Sukun', 'status' => 'rejected', 'reason' => 'Foto KTP buram dan nama pada KTP tidak sama dengan nama pemilik rekening. Mohon unggah ulang dokumen yang jelas.'],
        ];
    }

    /**
     * Nama pembeli (mahasiswa dan warga sekitar kampus).
     *
     * @return list<string>
     */
    public static function buyerNames(): array
    {
        return [
            'Rizky Aditya Pratama', 'Anisa Putri Rahmawati', 'Dimas Fajar Nugroho', 'Nabila Khairunnisa',
            'Bagas Setiawan', 'Fitria Ramadhani', 'Muhammad Irfan Hakim', 'Salsabila Azzahra',
            'Yoga Prasetyo', 'Dewi Lestari', 'Ahmad Zaki Mubarok', 'Citra Maharani',
            'Hendra Gunawan', 'Putri Ayu Wulandari', 'Farhan Maulana', 'Alya Safitri',
        ];
    }

    /**
     * Alasan penolakan produk.
     *
     * @return list<string>
     */
    public static function productRejectionReasons(): array
    {
        return [
            'Foto produk tidak jelas dan mengandung watermark toko lain. Mohon unggah foto asli produk.',
            'Deskripsi produk tidak sesuai dengan kategori yang dipilih. Silakan pindahkan ke kategori yang tepat.',
            'Harga tidak wajar dibandingkan produk sejenis. Mohon periksa kembali harga yang diinput.',
        ];
    }

    /**
     * Komentar ulasan menurut bintang. {produk} diganti nama singkat produk.
     *
     * @return array<int, list<string>>
     */
    public static function reviewComments(): array
    {
        return [
            5 => [
                'Barang sesuai deskripsi, packing rapi dan pengiriman cepat. Recommended seller!',
                'Kualitasnya bagus banget untuk harga segini. Pasti beli lagi.',
                'Alhamdulillah barang sampai dengan selamat, penjual responsif dan ramah.',
                '{produk} persis seperti di foto. Puas banget, terima kasih kak.',
                'Dipakai sudah seminggu, awet dan nyaman. Sangat membantu untuk kebutuhan kuliah.',
                'Pengiriman kilat, dibungkus bubble wrap tebal. Mantap!',
            ],
            4 => [
                'Barang bagus dan sesuai pesanan, hanya pengirimannya agak lama sehari.',
                'Kualitas oke sesuai harga. Packing bisa lebih rapi sedikit lagi.',
                '{produk} bagus, warna sedikit berbeda dari foto tapi masih wajar.',
                'Secara keseluruhan puas. Penjual fast response walaupun malam hari.',
            ],
            3 => [
                'Barang lumayan, tapi ada sedikit cacat kecil di bagian pinggir. Masih bisa dipakai.',
                'Pengiriman lama, barang sesuai tapi kemasan agak penyok.',
            ],
            2 => [
                'Ukuran kurang sesuai dengan keterangan, mohon dicek ulang sebelum dikirim.',
            ],
            1 => [
                'Barang tidak sesuai deskripsi dan respon penjual lambat.',
            ],
        ];
    }

    /**
     * Halaman footer. Konten berupa HTML sederhana agar bisa dirender dengan {!! !!}.
     *
     * @return list<array{title: string, slug: string, content: string}>
     */
    public static function pages(): array
    {
        return [
            [
                'title' => 'Guide',
                'slug' => 'guide',
                'content' => '<h2>Selamat datang di UM-Mart</h2>'
                    .'<p>UM-Mart adalah marketplace komunitas kampus yang mempertemukan pembeli dengan penjual UMKM, mahasiswa wirausaha, dan perajin lokal di sekitar Malang.</p>'
                    .'<h3>Mulai dari mana?</h3>'
                    .'<ol><li>Cari produk lewat kolom pencarian atau jelajahi kategori di beranda.</li>'
                    .'<li>Buka halaman produk untuk melihat foto, varian, harga, ulasan, dan informasi penjual.</li>'
                    .'<li>Masuk atau daftar akun untuk menyukai, menyimpan, dan membeli produk.</li>'
                    .'<li>Ingin berjualan? Daftarkan toko Anda dari dashboard dan tunggu verifikasi.</li></ol>'
                    .'<p>Butuh bantuan? Hubungi tim kami melalui email <strong>bantuan@um-mart.test</strong> pada hari kerja pukul 08.00 sampai 16.00 WIB.</p>',
            ],
            [
                'title' => 'Cara Jual Produk',
                'slug' => 'cara-jual-produk',
                'content' => '<h2>Cara Menjadi Penjual</h2>'
                    .'<ol><li><strong>Daftar akun</strong> dan lengkapi data diri di dashboard.</li>'
                    .'<li><strong>Registrasi penjual</strong>: isi data toko, unggah foto KTP, foto selfie dengan KTP, dan QRIS toko.</li>'
                    .'<li><strong>Tunggu verifikasi</strong>. Verifikator akan memeriksa data Anda paling lama 2 hari kerja.</li>'
                    .'<li><strong>Input produk</strong> lengkap dengan foto, varian, harga, stok, dan deskripsi.</li>'
                    .'<li><strong>Produk diverifikasi</strong>. Setelah disetujui, produk tampil di halaman kategori.</li>'
                    .'<li><strong>Kirim pesanan</strong> maksimal 2x24 jam setelah pembayaran terverifikasi, lalu isi nomor resi.</li>'
                    .'<li><strong>Cairkan hasil jual</strong> melalui menu Withdrawal setelah pesanan selesai.</li></ol>'
                    .'<h3>Tips foto produk</h3><p>Gunakan cahaya terang, latar polos, dan tampilkan produk dari beberapa sisi agar pembeli yakin.</p>',
            ],
            [
                'title' => 'Cara Beli Produk',
                'slug' => 'cara-beli-produk',
                'content' => '<h2>Cara Membeli di UM-Mart</h2>'
                    .'<ol><li>Pilih produk dan varian yang diinginkan, lalu klik <strong>Tambah ke Keranjang</strong>.</li>'
                    .'<li>Periksa keranjang, isi alamat pengiriman, dan lanjutkan ke pembayaran.</li>'
                    .'<li>Bayar melalui QRIS atau transfer sesuai nominal, lalu unggah bukti pembayaran.</li>'
                    .'<li>Verifikator akan memeriksa pembayaran Anda. Status pesanan berubah menjadi <em>Dibayar</em>.</li>'
                    .'<li>Penjual memproses dan mengirim pesanan. Pantau statusnya di menu <strong>Histori Pembelian</strong>.</li>'
                    .'<li>Setelah barang diterima, konfirmasi pesanan selesai dan berikan ulasan.</li></ol>'
                    .'<p>Pembayaran yang tidak dikonfirmasi dalam 24 jam akan dibatalkan otomatis.</p>',
            ],
            [
                'title' => 'Peraturan',
                'slug' => 'peraturan',
                'content' => '<h2>Peraturan Penggunaan UM-Mart</h2>'
                    .'<h3>Untuk semua pengguna</h3>'
                    .'<ul><li>Gunakan data diri yang benar dan jaga kerahasiaan akun.</li>'
                    .'<li>Dilarang melakukan penipuan, spam, atau menyalahgunakan sistem ulasan.</li></ul>'
                    .'<h3>Untuk penjual</h3>'
                    .'<ul><li>Dilarang menjual barang ilegal, berbahaya, palsu, atau melanggar hak cipta.</li>'
                    .'<li>Deskripsi, foto, dan harga harus sesuai kondisi barang sebenarnya.</li>'
                    .'<li>Pesanan wajib dikirim maksimal 2x24 jam setelah pembayaran terverifikasi.</li>'
                    .'<li>Pelanggaran dapat berakibat penurunan produk atau penonaktifan toko.</li></ul>'
                    .'<h3>Untuk pembeli</h3>'
                    .'<ul><li>Bayar sesuai nominal dan unggah bukti pembayaran yang asli.</li>'
                    .'<li>Ajukan komplain maksimal 2 hari setelah barang diterima.</li></ul>',
            ],
            [
                'title' => 'Kesepakatan Pajak',
                'slug' => 'kesepakatan-pajak',
                'content' => '<h2>Kesepakatan Pajak dan Biaya Layanan</h2>'
                    .'<p>Dengan berjualan di UM-Mart, penjual menyetujui ketentuan berikut:</p>'
                    .'<ul><li><strong>Biaya layanan platform</strong> sebesar 5% dari nilai produk pada setiap pesanan yang selesai.</li>'
                    .'<li><strong>Pajak penghasilan final UMKM</strong> sebesar 0,5% dari omzet dipotong otomatis dari hasil penjualan.</li>'
                    .'<li>Hasil penjualan bersih dapat dicairkan setelah pesanan berstatus selesai dengan minimum pencairan Rp50.000.</li>'
                    .'<li>Pencairan diproses oleh verifikator paling lama 3 hari kerja.</li></ul>'
                    .'<p>Ketentuan dapat berubah sewaktu-waktu dan akan diumumkan melalui halaman ini.</p>',
            ],
        ];
    }
}
