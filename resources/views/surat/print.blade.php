<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengantar RT - {{ $surat->user->name }} - {{ $surat->nomor_surat ?? 'SIKAS-RT' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f1f5f9;
            padding: 30px 15px;
            font-size: 14pt;
            line-height: 1.6;
        }

        .paper {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 50px 60px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            border-radius: 4px;
            position: relative;
        }

        /* Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 14px;
            margin-bottom: 24px;
            position: relative;
        }

        .kop-surat h3 {
            font-size: 15pt;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .kop-surat h2 {
            font-size: 18pt;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .kop-surat p {
            font-size: 11pt;
            margin-top: 2px;
            font-style: italic;
        }

        .logo-garuda {
            position: absolute;
            left: 10px;
            top: 5px;
            width: 70px;
            height: auto;
        }

        /* Judul Surat */
        .judul-surat {
            text-align: center;
            margin-bottom: 30px;
        }

        .judul-surat h4 {
            font-size: 14pt;
            font-weight: 700;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .judul-surat .nomor {
            font-size: 12pt;
            margin-top: 4px;
        }

        /* Konten Surat */
        .konten-surat p {
            text-align: justify;
            margin-bottom: 14px;
            text-indent: 40px;
        }

        .data-pemohon {
            margin: 15px 0 20px 40px;
            width: calc(100% - 40px);
        }

        .data-pemohon tr td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 13pt;
        }

        .data-pemohon tr td:first-child {
            width: 190px;
        }

        .data-pemohon tr td:nth-child(2) {
            width: 15px;
        }

        /* Penutup */
        .penutup {
            margin-top: 20px;
        }

        /* Tanda Tangan */
        .ttd-container {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .ttd-box {
            text-align: center;
            width: 250px;
            font-size: 12pt;
        }

        .ttd-space {
            height: 85px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cap-stempel {
            width: 110px;
            opacity: 0.85;
            transform: rotate(-8deg);
            position: absolute;
            left: 15px;
        }

        .ttd-nama {
            font-weight: 700;
            text-decoration: underline;
        }

        /* Tombol Aksi */
        .action-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #e2e8f0;
            color: #334155;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 20px 30px;
                max-width: 100%;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="{{ route('surat.index') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> Cetak / Unduh Dokumen Resmi</button>
    </div>

    <div class="paper">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <h3>PENGURUS RUKUN TETANGGA 01 / RUKUN WARGA 05</h3>
            <h2>KELURAHAN SUKAMAJU KECAMATAN CILODONG</h2>
            <h2>KOTA DEPOK - JAWA BARAT</h2>
            <p>Sekretariat: Balai Pertemuan RT 01 / RW 05, Telp / WA: 0812-3456-7890, Kode Pos 16415</p>
        </div>

        <!-- Judul Surat -->
        <div class="judul-surat">
            <h4>{{ strtoupper($surat->jenis_surat) }}</h4>
            <div class="nomor">Nomor: {{ $surat->nomor_surat ?? '.../SK-RT05/XI/2026' }}</div>
        </div>

        <!-- Isi Surat -->
        <div class="konten-surat">
            <p>
                Yang bertanda tangan di bawah ini, Ketua Rukun Tetangga (RT) 01 / Rukun Warga (RW) 05 Kelurahan Sukamaju, Kecamatan Cilodong, Kota Depok, dengan ini menerangkan dengan sebenarnya bahwa:
            </p>

            <table class="data-pemohon">
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($surat->user->name) }}</strong></td>
                </tr>
                <tr>
                    <td>Alamat Domisili</td>
                    <td>:</td>
                    <td>{{ $surat->user->address }}</td>
                </tr>
                <tr>
                    <td>RT / RW</td>
                    <td>:</td>
                    <td>{{ $surat->user->rt_rw ?? 'RT 01 / RW 05' }}</td>
                </tr>
                <tr>
                    <td>Status Kependudukan</td>
                    <td>:</td>
                    <td>Warga {{ ucfirst($surat->user->status_warga ?? 'Tetap') }} Lingkungan RT 01</td>
                </tr>
                <tr>
                    <td>No. Telepon / WhatsApp</td>
                    <td>:</td>
                    <td>{{ $surat->user->phone ?? '0812-xxxx-xxxx' }}</td>
                </tr>
            </table>

            <p>
                Orang tersebut di atas adalah benar-benar warga yang bertempat tinggal dan berdomisili sah di wilayah hukum lingkungan RT 01 / RW 05 Kelurahan Sukamaju, Kecamatan Cilodong.
            </p>

            <p>
                Surat Pengantar ini kami berikan dan terbitkan atas permohonan yang bersangkutan untuk keperluan:
            </p>

            <div style="margin: 10px 0 16px 40px; padding: 12px 16px; background: #fafafa; border-left: 3px solid #000; font-style: italic;">
                "{{ $surat->keperluan }}"
            </div>

            @if($surat->keterangan)
            <p style="text-indent: 40px; font-size: 12pt; color: #333;">
                Keterangan tambahan berkas: {{ $surat->keterangan }}
            </p>
            @endif

            <p class="penutup">
                Demikian surat pengantar ini dibuat dengan sesungguhnya dan penuh tanggung jawab agar dapat dipergunakan sebagaimana mestinya oleh pihak yang berwenang.
            </p>
        </div>

        <!-- Tanda Tangan -->
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 700;">Pemohon / Warga Bersangkutan</div>
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $surat->user->name }}</div>
            </div>

            <div class="ttd-box">
                <div>Kota Depok, {{ ($surat->approved_at ?? now())->translatedFormat('d F Y') }}</div>
                <div style="font-weight: 700;">Ketua RT 01 / RW 05</div>
                <div class="ttd-space">
                    <!-- Stempel Digital Resmi RT -->
                    <svg class="cap-stempel" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="#2563eb" stroke-width="3" stroke-dasharray="4,2"/>
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#2563eb" stroke-width="1.5"/>
                        <path id="curve" fill="none" stroke="none" d="M 20,60 A 40,40 0 1,1 100,60" />
                        <text font-size="8.5" fill="#2563eb" font-weight="bold" letter-spacing="1">
                            <textPath href="#curve" startOffset="50%" text-anchor="middle">RUKUN TETANGGA 01</textPath>
                        </text>
                        <text x="60" y="58" font-size="9" fill="#2563eb" font-weight="900" text-anchor="middle">★ RESMI ★</text>
                        <text x="60" y="70" font-size="8.5" fill="#2563eb" font-weight="bold" text-anchor="middle">RW 05</text>
                    </svg>
                </div>
                <div class="ttd-nama">{{ $ketuaRt->name ?? 'Bpk. H. Bambang' }}</div>
                <div style="font-size: 10pt; color: #444;">Ketua RT 01</div>
            </div>
        </div>
    </div>

</body>
</html>
