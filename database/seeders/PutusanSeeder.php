<?php

namespace Database\Seeders;

use App\Models\Putusan;
use Illuminate\Database\Seeder;

class PutusanSeeder extends Seeder
{
    public function run(): void
    {
        Putusan::updateOrCreate(
            ['nomor_putusan' => 'Putusan MA No. 99 K/Pdt/2019'],
            [
                'panitera' => 'MA RI',
                'jenis_pengadilan' => 'MA',
                'nama_pengadilan' => 'Mahkamah Agung RI',
                'tingkat_pengadilan' => 'Kasasi',
                'golongan_perkara' => 'Perdata',
                'tanggal_putusan' => '2019-05-20',
                'ringkasan_putusan' => 'Prinsip hukum mengenai itikad baik dalam perjanjian jual beli tanah.',
                'isi_putusan' => 'Mahkamah Agung berpendapat bahwa pembeli yang beritikad baik harus dilindungi oleh hukum...',
                'status_putusan' => 'Berlaku',
            ]
        );

        Putusan::updateOrCreate(
            ['nomor_putusan' => 'Putusan MA No. 123 K/Pid/2021'],
            [
                'panitera' => 'MA RI',
                'jenis_pengadilan' => 'MA',
                'nama_pengadilan' => 'Mahkamah Agung RI',
                'tingkat_pengadilan' => 'Kasasi',
                'golongan_perkara' => 'Pidana',
                'tanggal_putusan' => '2021-11-10',
                'ringkasan_putusan' => 'Penerapan pasal penyertaan dalam tindak pidana korupsi.',
                'isi_putusan' => 'Dalam tindak pidana korupsi, setiap pelaku yang turut serta melakukan perbuatan melawan hukum...',
                'status_putusan' => 'Berlaku',
            ]
        );
    }
}