<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalGlossary;

class LegalGlossarySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'tenant_id' => 1,
                'term' => 'PTUN',
                'singkatan' => 'Pengadilan Tata Usaha Negara',
                'kategori_hukum' => 'Tata Negara',
                'definisi_singkat' => 'Lembaga peradilan yang memeriksa sengketa tata usaha negara.',
                'penjelasan_lengkap' => 'PTUN adalah salah satu pelaksana kekuasaan kehakiman bagi rakyat pencari keadilan terhadap sengketa Tata Usaha Negara.',
                'dasar_hukum_terkait' => [['type' => 'uu', 'number' => '5 Tahun 1986']],
                'contoh_implementasi' => 'Menggugat SK pencabutan izin usaha yang dikeluarkan instansi pemerintah.',
            ],
            [
                'tenant_id' => 1,
                'term' => 'Force Majeure',
                'singkatan' => 'Keadaan Memaksa',
                'kategori_hukum' => 'Perdata',
                'definisi_singkat' => 'Keadaan di luar kemampuan manusia yang membuat kontrak tidak dapat dipenuhi.',
                'penjelasan_lengkap' => 'Overmacht atau keadaan memaksa adalah suatu kejadian yang terjadi di luar kekuasaan debitur yang tidak dapat diduga sebelumnya.',
                'dasar_hukum_terkait' => [['type' => 'pasal', 'number' => 'Pasal 1244 KUHPerdata']],
                'contoh_implementasi' => 'Pabrik gagal kirim barang karena wilayah dilanda banjir bandang.',
            ],
            [
                'tenant_id' => 1,
                'term' => 'KUHPerdata',
                'singkatan' => 'Kitab Undang-Undang Hukum Perdata',
                'kategori_hukum' => 'Perdata',
                'definisi_singkat' => 'Induk hukum privat dan perdata yang berlaku di Indonesia.',
                'penjelasan_lengkap' => 'Burgerlijk Wetboek (BW) mengatur hukum kebendaan, perikatan, keluarga, dan waris.',
                'dasar_hukum_terkait' => [['type' => 'uu', 'number' => 'Staatblad 1847 No. 23']],
                'contoh_implementasi' => 'Dasar hukum penyusunan akta perjanjian jual beli properti.',
            ],
            [
                'tenant_id' => 1,
                'term' => 'PMH',
                'singkatan' => 'Perbuatan Melawan Hukum',
                'kategori_hukum' => 'Perdata',
                'definisi_singkat' => 'Perbuatan yang melanggar hukum dan menimbulkan kerugian bagi pihak lain.',
                'penjelasan_lengkap' => 'Onrechtmatige daad adalah perbuatan yang melanggar hak orang lain, bertentangan dengan kewajiban hukum si pelaku, atau melanggar kesusilaan.',
                'dasar_hukum_terkait' => [['type' => 'pasal', 'number' => 'Pasal 1365 KUHPerdata']],
                'contoh_implementasi' => 'Menuntut ganti rugi karena pabrik tetangga mencemari air sumur warga.',
            ],
            [
                'tenant_id' => 1,
                'term' => 'PKPU',
                'singkatan' => 'Penundaan Kewajiban Pembayaran Utang',
                'kategori_hukum' => 'Bisnis',
                'definisi_singkat' => 'Proses hukum agar debitur dapat merestrukturisasi utangnya.',
                'penjelasan_lengkap' => 'PKPU adalah masa yang diberikan oleh undang-undang melalui putusan pengadilan niaga untuk tercapainya rencana perdamaian.',
                'dasar_hukum_terkait' => [['type' => 'uu', 'number' => 'UU No. 37 Tahun 2004']],
                'contoh_implementasi' => 'Perusahaan mengajukan PKPU agar tidak langsung dipailitkan oleh para supplier.',
            ],
        ];

        foreach ($items as $item) {
            LegalGlossary::create($item);
        }
    }
}
