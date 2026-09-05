<?php namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Regulation;
use App\Models\RegulationContent;
use App\Models\Company;
use App\Models\User;

class RegulationsSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first();
        $tenantId = $company->tenant_id ?? 'tenant-lawlex-demo';
        $companyId = $company->id ?? 1;
        $userId = User::first()->id ?? 1;

        $regs = [
            ['number' => '1', 'year' => 1946, 'category' => 'UU', 'title' => 'KUHP', 'status' => 'berlaku', 'description' => 'KUHP kode pidana baku'],
            ['number' => '1', 'year' => 2023, 'category' => 'UU', 'title' => 'KUHP Baru', 'status' => 'berlaku', 'description' => 'KUHP baru 2023'],
            ['number' => '5', 'year' => 1960, 'category' => 'UU', 'title' => 'UU Pokok Tenaga Kerja', 'status' => 'berlaku', 'description' => 'Pokok tenaga kerja'],
            ['number' => '13', 'year' => 2003, 'category' => 'UU', 'title' => 'UU Ketenagakerjaan', 'status' => 'berlaku', 'description' => 'Perlindungan tenaga kerja'],
            ['number' => '7', 'year' => 2016, 'category' => 'UU', 'title' => 'UU Kebijakan Rokok', 'status' => 'berlaku', 'description' => 'Kebijakan merokok nasional'],
            ['number' => '39', 'year' => 1999, 'category' => 'UU', 'title' => 'UU HAM', 'status' => 'berlaku', 'description' => 'Hak asasi manusia'],
            ['number' => '24', 'year' => 2011, 'category' => 'PP', 'title' => 'PP PPN', 'status' => 'berlaku', 'description' => 'Pajak pertambahan nilai'],
            ['number' => '48', 'year' => 2009, 'category' => 'PP', 'title' => 'PP Kesehatan', 'status' => 'berlaku', 'description' => 'Jaminan kesehatan nasional'],
            ['number' => '1', 'year' => 1959, 'category' => 'Perpres', 'title' => 'Perpres Susunan Pemerintahan', 'status' => 'berlaku', 'description' => 'Struktur pemerintahan'],
            ['number' => '77', 'year' => 1997, 'category' => 'UU', 'title' => 'UU PPN', 'status' => 'berlaku', 'description' => 'Peraturan PPN'],
            ['number' => '11', 'year' => 2008, 'category' => 'UU', 'title' => 'UU ITE', 'status' => 'berlaku', 'description' => 'Informasi dan transaksi elektronik'],
            ['number' => '18', 'year' => 2004, 'category' => 'UU', 'title' => 'UU PT', 'status' => 'berlaku', 'description' => 'Perseroan terbatas'],
            ['number' => '6', 'year' => 1960, 'category' => 'UU', 'title' => 'UU Keluarga', 'status' => 'berlaku', 'description' => 'Ketentuan keluarga'],
            ['number' => '9', 'year' => 1962, 'category' => 'UU', 'title' => 'UU Perekonomian', 'status' => 'berlaku', 'description' => 'Pengawasan ekonomi'],
            ['number' => '32', 'year' => 2014, 'category' => 'UU', 'title' => 'UU Migas', 'status' => 'berlaku', 'description' => 'Minyak dan gas'],
            ['number' => '41', 'year' => 1999, 'category' => 'UU', 'title' => 'UU Kelautan', 'status' => 'berlaku', 'description' => 'Peraturan laut'],
            ['number' => '23', 'year' => 2014, 'category' => 'UU', 'title' => 'UU Kesehatan', 'status' => 'berlaku', 'description' => 'Pelayanan kesehatan'],
            ['number' => '23', 'year' => 2016, 'category' => 'UU', 'title' => 'UU PPh', 'status' => 'berlaku', 'description' => 'Pengenaan PPh'],
            ['number' => '2', 'year' => 1999, 'category' => 'UU', 'title' => 'UU Kependudukan', 'status' => 'berlaku', 'description' => 'Data kependudukan'],
            ['number' => '10', 'year' => 2004, 'category' => 'UU', 'title' => 'UU Pembentukan Peraturan', 'status' => 'berlaku', 'description' => 'Cara pembentukan peraturan'],
        ];

        $pasalCount = 0;
        foreach ($regs as $reg) {
            $regulation = Regulation::updateOrCreate(
                ['number' => $reg['number'], 'year' => $reg['year'], 'category' => $reg['category'], 'tenant_id' => $tenantId],
                [
                    'title' => $reg['title'],
                    'status' => $reg['status'],
                    'description' => $reg['description'],
                    'company_id' => $companyId,
                    'created_by' => $userId,
                ]
            );
            $n = rand(5, 25);
            for ($i = 1; $i <= $n; $i++) {
                RegulationContent::create([
                    'regulation_id' => $regulation->id,
                    'tenant_id' => $tenantId,
                    'article_number' => 'Pasal ' . $i,
                    'article_title' => 'Isi Pasal ' . $i,
                    'content' => 'Isi materi pasal nomor ' . $i . ' tentang ' . $reg['title'] . ' di Indonesia.',
                ]);
            }
            $pasalCount += $n;
        }

        $this->command->info('Seeded ' . count($regs) . ' regulations with ' . $pasalCount . ' pasal.');
    }
}