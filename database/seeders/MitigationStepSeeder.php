<?php

namespace Database\Seeders;

use App\Models\Disaster;
use App\Models\MitigationStep;
use Illuminate\Database\Seeder;

class MitigationStepSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            'banjir' => [
                'pra' => [
                    'Pembersihan Drainase: Pastikan selokan di depan rumah tidak tersumbat sampah atau sedimen tanah.',
                    'Penyediaan Daerah Resapan: Kurangi penggunaan semen/beton di halaman rumah. Gunakan biopori atau paving block agar air meresap ke tanah.',
                    'Tas Siaga Bencana (TSB): Siapkan tas berisi dokumen penting (ijazah, sertifikat), obat-obatan, senter, powerbank, dan pakaian dalam wadah kedap air.',
                    'Peninggian Instalasi Listrik: Posisikan stop kontak dan panel listrik lebih tinggi dari titik maksimal banjir yang pernah terjadi.',
                    'Simak Informasi Cuaca: Pantau informasi dari BMKG melalui aplikasi atau media sosial.',
                ],
                'saat' => [
                    'Matikan Listrik & Gas: Segera matikan aliran listrik dari meteran (MCB) dan lepas regulator gas untuk mencegah kebakaran atau sengatan listrik.',
                    'Evakuasi Dini: Jangan menunggu air setinggi dada. Jika air mulai masuk ke rumah, segera amankan anggota keluarga ke tempat yang lebih tinggi.',
                    'Hindari Berjalan di Arus Air: Arus setinggi 15 cm sudah mampu menjatuhkan orang dewasa. Waspadai juga lubang selokan yang tidak terlihat karena tertutup air.',
                    'Gunakan Alas Kaki: Selalu gunakan sepatu atau sandal untuk menghindari luka akibat benda tajam atau gigitan hewan yang keluar saat banjir.',
                ],
                'pasca' => [
                    'Pembersihan Segera: Bersihkan lantai dan dinding dari lumpur menggunakan disinfektan untuk membunuh kuman.',
                    'Periksa Instalasi Listrik: Jangan menyalakan listrik sebelum dipastikan benar-benar kering oleh teknisi atau pihak PLN.',
                    'Waspadai Sarang Hewan: Periksa celah-celah rumah, lemari, atau tumpukan kain yang mungkin menjadi tempat bersembunyi ular atau kalajengking setelah air surut.',
                    'Pengelolaan Sampah: Segera buang sampah basah agar tidak menjadi sarang lalat dan nyamuk.',
                ],
            ],
            'tanah-longsor' => [
                'pra' => [
                    'Tutup Retakan Tanah: Segera urug retakan tanah di lereng atau sekitar rumah dengan tanah liat/semen agar air hujan tidak masuk ke dalam tanah.',
                    'Kelola Aliran Air (Drainase): Pastikan air hujan mengalir lancar di selokan semen dan tidak merembes liar ke dalam tebing.',
                    'Tanam Pohon Berakar Kuat: Hijaukan lereng dengan tanaman seperti Vetiver, Bambu, atau Mahoni yang akarnya mampu mengikat tanah.',
                    'Hindari Beban Berlebih: Jangan membangun rumah, membuat kolam ikan, atau sawah di bagian atas lereng yang terjal.',
                    'Siapkan Tas Siaga Bencana (TSB): Siapkan tas berisi dokumen penting, senter, peluit, dan obat-obatan di dekat pintu keluar.',
                ],
                'saat' => [
                    'Segera Evakuasi: Jika terdengar suara gemuruh dari bukit atau terlihat pohon miring, segera lari keluar rumah menuju tanah lapang/datar.',
                    'Gunakan Alat Peringatan: Bunyikan kentongan atau sirine untuk memberi tahu tetangga agar segera menyelamatkan diri.',
                    'Lindungi Kepala: Jika terjebak di dalam ruangan, berlindunglah di bawah meja yang kuat atau tekuk tubuh seperti bola untuk melindungi kepala dari reruntuhan.',
                    'Jauhi Jalur Aliran: Jangan melintasi lembah, sungai, atau jalur bawah tebing karena material longsor bergerak sangat cepat.',
                    'Gunakan Peluit: Jika terjebak atau tertimbun, gunakan peluit atau ketukan benda keras untuk memberi tahu posisi Anda kepada tim penolong.',
                ],
                'pasca' => [
                    'Waspada Longsor Susulan: Jangan langsung kembali ke rumah meskipun hujan sudah reda. Longsor susulan sering terjadi saat tanah masih jenuh air.',
                    'Hindari Area Retakan: Jangan mendekati area yang baru saja longsor karena struktur tanahnya masih sangat labil.',
                    'Laporkan Kerusakan: Segera laporkan kepada pihak berwenang jika melihat adanya retakan baru atau pipa air/listrik yang terputus.',
                    'Bantu Tim SAR: Berikan informasi yang akurat mengenai jumlah anggota keluarga atau tetangga yang mungkin masih tertimbun.',
                    'Pembersihan Mandiri: Lakukan pembersihan material lumpur hanya jika sudah dinyatakan aman oleh petugas ahli geologi.',
                ],
            ],
            'gempa-bumi' => [
                'pra' => [
                    'Tata Letak Furnitur: Pastikan lemari tinggi atau benda berat dipaku ke dinding agar tidak tumbang saat guncangan.',
                    'Cek Struktur Bangunan: Pastikan atap dan dinding dalam kondisi kokoh. Gunakan material ringan untuk plafon.',
                    'Identifikasi Tempat Aman: Tentukan titik aman di dalam rumah (di bawah meja kuat) dan titik kumpul di luar rumah (lapangan terbuka jauh dari tiang listrik/pohon).',
                    'Siapkan Tas Siaga Bencana (TSB): Berisi air minum, makanan kering, senter, P3K, peluit, dan dokumen penting.',
                    'Matikan Potensi Bahaya: Pastikan seluruh anggota keluarga tahu cara mematikan gas elpiji dan aliran listrik pusat (sekring/MCB).',
                ],
                'saat' => [
                    'Berlutut (Drop): Jatuhkan badan ke tangan dan lutut sebelum gempa menjatuhkan Anda.',
                    'Lindungi Kepala (Cover): Masuk ke bawah meja yang kokoh. Jika tidak ada meja, lindungi kepala dengan lengan/bantal di pojok ruangan.',
                    'Bertahan (Hold On): Pegang kaki meja hingga guncangan berhenti.',
                    'Jangan Gunakan Lift: Jika berada di gedung bertingkat, gunakan tangga darurat. Jangan berlari keluar saat guncangan masih terjadi.',
                    'Jauhi Kaca & Benda Gantung: Hindari berdiri dekat jendela, cermin, atau lampu gantung.',
                    'Di Luar Ruangan: Cari lahan terbuka. Jauhi gedung, tiang listrik, pohon, dan papan reklame.',
                ],
                'pasca' => [
                    'Waspada Gempa Susulan: Biasanya terjadi beberapa kali setelah gempa utama. Tetaplah berada di titik kumpul luar ruangan.',
                    'Periksa Kebocoran: Cek bau gas, percikan kabel listrik, dan retakan bangunan sebelum masuk kembali ke rumah.',
                    'Jauhi Area Pantai: Jika gempa terasa sangat kuat dan Anda berada di pantai, segera lari ke tempat tinggi tanpa menunggu peringatan tsunami.',
                    'Gunakan Telepon Hanya untuk Darurat: Gunakan pesan teks/WhatsApp untuk memberi kabar.',
                    'Simak Informasi Resmi: Pantau update dari BMKG untuk mengetahui magnitudo dan potensi tsunami.',
                ],
            ],
            'tsunami' => [
                'pra' => [
                    'Pahami Geografi Lokal: Ketahui jarak rumah Anda dari bibir pantai dan ketinggian wilayah Anda di atas permukaan laut.',
                    'Kenali Jalur Evakuasi: Hafalkan jalur menuju tempat yang lebih tinggi (minimal 20 meter di atas permukaan laut) atau bangunan vertikal yang sudah ditentukan.',
                    'Prinsip 20-20-20: Jika gempa terasa selama 20 detik, segera evakuasi dalam waktu 20 menit, menuju ketinggian minimal 20 meter.',
                    'Simak Peringatan Dini: Pahami bunyi sirine tsunami di daerah Anda. Jika berbunyi tanpa henti, itu adalah perintah evakuasi segera.',
                    'Tanam Mangrove: Secara komunitas, menanam bakau di pesisir dapat membantu memecah energi gelombang sebelum mencapai daratan.',
                ],
                'saat' => [
                    'Tanda Alam: Jika setelah gempa air laut tiba-tiba surut drastis hingga ikan-ikan terdampar, jangan mendekat untuk mengambil ikan. Segera lari sejauh mungkin dari pantai.',
                    'Evakuasi ke Tempat Tinggi: Segera menuju perbukitan atau lantai atas bangunan beton yang kokoh (minimal lantai 3).',
                    'Jangan Gunakan Mobil: Jika memungkinkan, evakuasi dengan jalan kaki atau motor agar tidak terjebak macet.',
                    'Lepas Alas Kaki yang Menghambat: Jika harus berenang, lepaskan sepatu berat atau benda yang bisa menenggelamkan Anda.',
                    'Bertahan pada Benda Terapung: Jika terseret arus, carilah benda yang terapung kuat untuk dijadikan pelampung.',
                ],
                'pasca' => [
                    'Jangan Langsung Kembali: Tsunami biasanya terdiri dari beberapa gelombang. Tunggu arahan Status Aman dari BMKG.',
                    'Waspadai Kabel Listrik: Hindari genangan air yang bersentuhan dengan kabel listrik yang jatuh.',
                    'Hati-hati dengan Bangunan Rusak: Struktur bangunan yang terkena hantaman tsunami biasanya menjadi sangat rapuh dan mudah roboh.',
                    'Cek Sumber Air Bersih: Air sumur biasanya akan tercemar air asin dan bakteri setelah tsunami. Gunakan air kemasan untuk konsumsi.',
                ],
            ],
            'angin-puting-beliung' => [
                'pra' => [
                    'Pangkas Dahan Pohon: Tebang dahan pohon yang sudah rimbun atau rapuh di sekitar rumah agar tidak tumbang menimpa bangunan saat angin kencang.',
                    'Perkuat Atap: Pastikan paku atau baut pada atap seng/asbes terpasang kuat. Angin puting beliung sering kali mengangkat atap yang longgar.',
                    'Cek Kondisi Dinding: Pastikan tidak ada keretakan struktur yang bisa menyebabkan bangunan roboh saat terkena tekanan angin.',
                    'Edukasi Tanda Alam: Ajarkan keluarga mengenali awan Cumulonimbus (awan hitam pekat, berbentuk seperti bunga kol yang menjulang tinggi).',
                ],
                'saat' => [
                    'Jika Di Dalam Ruangan: Segera cari tempat perlindungan di bagian dalam bangunan (kamar mandi atau kolong meja yang kuat).',
                    'Jauhi Jendela & Pintu Kaca: Tekanan angin bisa memecahkan kaca dan serpihannya sangat berbahaya.',
                    'Berlindung di pojok ruangan dan lindungi kepala dengan bantal atau tangan (posisi meringkuk).',
                    'Jika Di Luar Ruangan: Hindari berteduh di bawah pohon, papan reklame, atau tiang listrik. Segera cari bangunan yang kokoh dan permanen.',
                    'Jika Di Dalam Kendaraan: Segera keluar dari mobil dan cari perlindungan di bangunan kokoh atau tiarap di tempat rendah.',
                ],
                'pasca' => [
                    'Waspadai Kabel Terputus: Jangan menyentuh atau mendekati kabel listrik yang jatuh ke tanah karena mungkin masih bertegangan.',
                    'Periksa Struktur Atap: Sebelum masuk kembali ke rumah, cek apakah atap masih stabil atau ada bagian yang hampir jatuh.',
                    'Gotong Royong Bersama: Bersihkan puing-puing bangunan atau pohon yang tumbang yang menghalangi jalan akses darurat.',
                    'Dokumentasi Kerusakan: Ambil foto kerusakan untuk keperluan pelaporan ke BPBD atau pihak terkait.',
                ],
            ],
        ];

        foreach ($steps as $slug => $phases) {
            $disaster = Disaster::where('slug', $slug)->first();
            if (! $disaster) {
                continue;
            }

            foreach ($phases as $phase => $contents) {
                foreach ($contents as $order => $content) {
                    MitigationStep::updateOrCreate(
                        ['disaster_id' => $disaster->id, 'phase' => $phase, 'order' => $order],
                        ['content' => $content]
                    );
                }
            }
        }
    }
}
