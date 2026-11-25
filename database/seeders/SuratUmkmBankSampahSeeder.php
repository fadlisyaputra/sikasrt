<?php

namespace Database\Seeders;

use App\Models\BankSampahCategory;
use App\Models\BankSampahTransaction;
use App\Models\SuratRequest;
use App\Models\UmkmProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SuratUmkmBankSampahSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'superadmin')->first();
        $rt = User::where('role', 'rt')->first() ?? $admin;
        $budi = User::where('email', 'budi@kasrt.local')->first() ?? User::where('role', 'warga')->first();
        $siti = User::where('email', 'siti@kasrt.local')->first() ?? User::where('role', 'warga')->skip(1)->first() ?? $budi;
        $joko = User::where('email', 'joko@kasrt.local')->first() ?? User::where('role', 'warga')->skip(2)->first() ?? $budi;

        // 1. DATA MASTER BANK SAMPAH
        $categories = [
            [
                'nama_kategori' => 'Plastik Botol PET Bersih',
                'kode' => 'PET',
                'harga_per_kg' => 4500,
                'satuan' => 'kg',
                'deskripsi' => 'Botol air mineral transparan tanpa tutup dan label',
            ],
            [
                'nama_kategori' => 'Kardus & Karton Tebal',
                'kode' => 'KARDUS',
                'harga_per_kg' => 2500,
                'satuan' => 'kg',
                'deskripsi' => 'Kardus bekas mie instan, packaging, dan box karton kering',
            ],
            [
                'nama_kategori' => 'Kertas HVS & Arsip / Buku',
                'kode' => 'HVS',
                'harga_per_kg' => 2000,
                'satuan' => 'kg',
                'deskripsi' => 'Kertas putih print, buku tulis, dan majalah tidak basah',
            ],
            [
                'nama_kategori' => 'Kaleng Minuman & Aluminium',
                'kode' => 'ALUM',
                'harga_per_kg' => 14000,
                'satuan' => 'kg',
                'deskripsi' => 'Kaleng soda, minuman kaleng, dan plat aluminium',
            ],
            [
                'nama_kategori' => 'Besi / Logam Campur',
                'kode' => 'BESI',
                'harga_per_kg' => 5000,
                'satuan' => 'kg',
                'deskripsi' => 'Potongan besi, pipa, kawat, dan paku bekas',
            ],
            [
                'nama_kategori' => 'Minyak Jelantah (UCO)',
                'kode' => 'JELANTAH',
                'harga_per_kg' => 7000,
                'satuan' => 'liter',
                'deskripsi' => 'Minyak goreng bekas pakai yang disaring dalam botol jerigen',
            ],
        ];

        foreach ($categories as $cat) {
            BankSampahCategory::updateOrCreate(['kode' => $cat['kode']], $cat);
        }

        // Transaksi Bank Sampah
        $catPet = BankSampahCategory::where('kode', 'PET')->first();
        $catKardus = BankSampahCategory::where('kode', 'KARDUS')->first();
        $catJelantah = BankSampahCategory::where('kode', 'JELANTAH')->first();

        if ($budi && $catPet) {
            BankSampahTransaction::create([
                'user_id' => $budi->id,
                'category_id' => $catPet->id,
                'petugas_id' => $rt->id,
                'tipe' => 'setor',
                'berat_kg' => 8.5,
                'harga_satuan' => $catPet->harga_per_kg,
                'total_rp' => 8.5 * $catPet->harga_per_kg,
                'status' => 'completed',
                'catatan' => 'Setoran 2 karung botol air mineral',
                'created_at' => Carbon::now()->subDays(5),
            ]);
        }

        if ($budi && $catKardus) {
            BankSampahTransaction::create([
                'user_id' => $budi->id,
                'category_id' => $catKardus->id,
                'petugas_id' => $rt->id,
                'tipe' => 'setor',
                'berat_kg' => 12.0,
                'harga_satuan' => $catKardus->harga_per_kg,
                'total_rp' => 12.0 * $catKardus->harga_per_kg,
                'status' => 'completed',
                'catatan' => 'Kardus pindahan rumah',
                'created_at' => Carbon::now()->subDays(2),
            ]);
        }

        if ($siti && $catJelantah) {
            BankSampahTransaction::create([
                'user_id' => $siti->id,
                'category_id' => $catJelantah->id,
                'petugas_id' => $rt->id,
                'tipe' => 'setor',
                'berat_kg' => 5.0,
                'harga_satuan' => $catJelantah->harga_per_kg,
                'total_rp' => 5.0 * $catJelantah->harga_per_kg,
                'status' => 'completed',
                'catatan' => 'Minyak jelantah dari usaha gorengan',
                'created_at' => Carbon::now()->subDays(3),
            ]);
        }

        // 2. DATA UMKM WARGA
        if ($siti) {
            UmkmProduct::create([
                'user_id' => $siti->id,
                'nama_usaha' => 'Dapur Bu Siti Berkah',
                'nama_produk' => 'Nasi Uduk Betawi Komplit & Sambal Terasi',
                'kategori' => 'Makanan & Minuman',
                'deskripsi' => 'Nasi uduk harum gurih dengan lauk semur tahu, tempe orek, telur balado, bihun goreng, dan sambal khas Betawi.',
                'harga' => 18000,
                'satuan' => 'porsi',
                'no_whatsapp' => '081298765432',
                'alamat_usaha' => 'Blok B No. 04',
                'status' => 'active',
                'is_verified' => true,
            ]);

            UmkmProduct::create([
                'user_id' => $siti->id,
                'nama_usaha' => 'Dapur Bu Siti Berkah',
                'nama_produk' => 'Aneka Gorengan Hangat (Tempe, Bakwan, Tahu)',
                'kategori' => 'Makanan & Minuman',
                'deskripsi' => 'Gorengan renyah higienis tanpa bahan pengawet, dibuat setiap sore jam 16.00 WIB.',
                'harga' => 2000,
                'satuan' => 'pcs',
                'no_whatsapp' => '081298765432',
                'alamat_usaha' => 'Blok B No. 04',
                'status' => 'active',
                'is_verified' => true,
            ]);
        }

        if ($budi) {
            UmkmProduct::create([
                'user_id' => $budi->id,
                'nama_usaha' => 'Budi Jaya Teknik AC & Listrik',
                'nama_produk' => 'Jasa Cuci AC & Tambah Freon Bergaransi',
                'kategori' => 'Jasa & Servis',
                'deskripsi' => 'Melayani servis cuci AC 0.5 - 2 PK untuk rumah warga perumahan RT 05. Bersih, rapi, dan bergaransi 30 hari.',
                'harga' => 75000,
                'satuan' => 'unit',
                'no_whatsapp' => '081311223344',
                'alamat_usaha' => 'Blok A No. 12',
                'status' => 'active',
                'is_verified' => true,
            ]);
        }

        if ($joko) {
            UmkmProduct::create([
                'user_id' => $joko->id,
                'nama_usaha' => 'Kios Sayur Segar Mas Joko',
                'nama_produk' => 'Paket Sayur Sop & Lodeh Siap Masak',
                'kategori' => 'Sembako',
                'deskripsi' => 'Sayuran segar langsung dari kebun, sudah dipotong dan dicuci bersih lengkap dengan bumbu dapur.',
                'harga' => 12000,
                'satuan' => 'paket',
                'no_whatsapp' => '081755667788',
                'alamat_usaha' => 'Blok C No. 08',
                'status' => 'active',
                'is_verified' => true,
            ]);
        }

        // 3. DATA SURAT MENYURAT
        if ($budi) {
            SuratRequest::create([
                'user_id' => $budi->id,
                'nomor_surat' => '024/SK-RT05/XI/2026',
                'jenis_surat' => 'Surat Pengantar Pembuatan KTP / KK',
                'keperluan' => 'Permohonan penggantian e-KTP baru karena rusak dan pembaruan data KK di Kelurahan',
                'keterangan' => 'Fotokopi KK lama sudah disiapkan',
                'status' => 'approved',
                'catatan_pengurus' => 'Surat pengantar telah disetujui dan ditandatangani Ketua RT. Silakan unduh/cetak dokumen.',
                'approved_by' => $rt->id,
                'approved_at' => Carbon::now()->subDays(1),
            ]);
        }

        if ($siti) {
            SuratRequest::create([
                'user_id' => $siti->id,
                'nomor_surat' => '025/SK-RT05/XI/2026',
                'jenis_surat' => 'Surat Keterangan Usaha (SKU)',
                'keperluan' => 'Pengajuan bantuan modal usaha mikro dan pembukaan rekening bank UMKM Dapur Bu Siti',
                'keterangan' => 'Usaha kuliner rumahan aktif sejak 2023',
                'status' => 'approved',
                'catatan_pengurus' => 'Telah diverifikasi sesuai domisili dan aktivitas usaha di lingkungan RT 05.',
                'approved_by' => $rt->id,
                'approved_at' => Carbon::now()->subHours(8),
            ]);
        }

        if ($joko) {
            SuratRequest::create([
                'user_id' => $joko->id,
                'nomor_surat' => null,
                'jenis_surat' => 'Surat Keterangan Domisili',
                'keperluan' => 'Kelengkapan administrasi pendaftaran sekolah anak tingkat SMP',
                'keterangan' => 'Membutuhkan surat keterangan bertempat tinggal tetap',
                'status' => 'pending',
                'catatan_pengurus' => null,
            ]);
        }
    }
}
