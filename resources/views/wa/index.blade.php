<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Geofencing Presensi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* 1. SETUP */
        * { box-sizing: border-box; }
        p { margin: 0 0 15px 0; }
        body {
            background-color: #0f172a;
            display: grid;
            gap: 25px;
            grid-template-columns: 1fr;
            margin: 0;
            min-height: 100vh;
            padding: 40px 0;
            place-items: center;
        }

        .slide-container {
            align-items: flex-start;
            background-color: #0f172a;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            font-family: 'Urbanist', sans-serif;
            height: 720px;
            justify-content: center;
            overflow: hidden;
            padding: 60px;
            position: relative;
            width: 1280px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Abstract Background Elements */
        .slide-container::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.15) 0%, transparent 70%);
            z-index: 0;
        }

        .slide-container > * { position: relative; z-index: 1; }

        /* 2. TYPOGRAPHY */
        h1, h2, h3 {
            color: #f8fafc;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        h1 { font-size: 82px; font-weight: 700; line-height: 1.1; }
        .slide-title { font-size: 42px; font-weight: 600; margin-bottom: 30px; border-left: 6px solid #22d3ee; padding-left: 20px; }
        h3 { font-size: 28px; margin-bottom: 15px; color: #22d3ee; }

        p, li, td, th { color: #94a3b8; font-size: 20px; line-height: 1.6; }
        strong { color: #f8fafc; }

        /* 3. LAYOUTS */
        .content-area { width: 100%; flex-grow: 1; }

        .title-layout { text-align: left; width: 100%; }
        .title-layout h1 span { color: #22d3ee; }
        .subtitle { font-size: 24px; color: #64748b; margin-top: 20px; max-width: 800px; }

        .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; width: 100%; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255,255,255,0.03); border-radius: 8px; overflow: hidden; }
        th, td { padding: 20px; text-anchor: start; border-bottom: 1px solid rgba(255,255,255,0.05); }
        th { background: rgba(34, 211, 238, 0.1); color: #22d3ee; font-weight: 600; text-align: left; }

        /* Bleed Image Layout */
        .slide-container.bleed-image-layout { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 0; align-items: start; }
        .bleed-text-side { padding: 80px 60px; display: flex; flex-direction: column; justify-content: center; height: 100%; }
        .image-container { height: 720px; width: 100%; overflow: hidden; }
        .bleed-image-side { width: 100%; height: 720px; object-fit: cover; }

        /* Tiled Icons */
        .tiled-content { display: flex; gap: 30px; width: 100%; }
        .tile { flex: 1; background: rgba(255,255,255,0.03); padding: 40px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s ease; }
        .tile .icon { font-size: 48px; color: #22d3ee; margin-bottom: 25px; }

        /* Highlight Numbers */
        .highlight-numbers-layout { display: flex; align-items: center; gap: 80px; }
        .number-box { text-align: center; }
        .number { font-size: 140px; font-weight: 700; color: #22d3ee; line-height: 1; }
        .number-label { font-size: 24px; color: #94a3b8; font-weight: 500; }

        /* Styled Bullets */
        .bullet-list ul { list-style: none; padding: 0; }
        .bullet-list li { position: relative; padding-left: 50px; margin-bottom: 25px; }
        .bullet-list i { position: absolute; left: 0; top: 5px; color: #22d3ee; font-size: 24px; }

        /* Chart Styling */
        .chart-container { width: 100%; height: 350px; margin-top: 20px; }

        /* Area Chart SVG */
        .area-chart-svg { width: 100%; height: 100%; overflow: visible; }
        .line-stroke { fill: none; stroke: #22d3ee; stroke-width: 4; }
        .area-fill { fill: rgba(34, 211, 238, 0.1); }

        .image-wrapper { width: 100%; height: 380px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .image-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    </style>
</head>
<body>

<div class="slide-container" id="slide1">
    <div class="title-layout">
        <h1>Dashboard<br><span>Geofencing</span> Presensi</h1>
        <p class="subtitle">Otomatisasi pemantauan lokasi dan batas radius presensi berbasis peta interaktif untuk sistem informasi akademik Anda.</p>
    </div>
</div>

<div class="slide-container" id="slide2">
    <div style="text-align: center; width: 100%;">
        <div style="width: 80px; height: 4px; background: #22d3ee; margin: 0 auto 30px;"></div>
        <h2 style="font-size: 64px;">Implementasi Strategis</h2>
        <p style="font-size: 24px; margin-top: 20px;">Transformasi pengaturan statis menjadi dashboard dinamis.</p>
    </div>
</div>

<div class="slide-container" id="slide3">
    <h2 class="slide-title">Struktur Database Pengaturan</h2>
    <div class="content-area">
        <p>Memindahkan parameter lokasi dari file .env ke database untuk fleksibilitas pengaturan admin.</p>
        <table>
            <thead>
                <tr>
                    <th>Kolom</th>
                    <th>Tipe Data</th>
                    <th>Deskripsi</th>
                    <th>Contoh Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>key_setting</strong></td>
                    <td>String (Unique)</td>
                    <td>Identitas kunci pengaturan</td>
                    <td>'center_latitude'</td>
                </tr>
                <tr>
                    <td><strong>value_setting</strong></td>
                    <td>Text</td>
                    <td>Nilai koordinat atau radius</td>
                    <td>'-7.664751'</td>
                </tr>
                <tr>
                    <td><strong>label</strong></td>
                    <td>String</td>
                    <td>Nama tampilan di dashboard</td>
                    <td>'Titik Pusat Sekolah'</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="slide-container bleed-image-layout" id="slide4">
    <div class="bleed-text-side">
        <h2 class="slide-title">User Interface</h2>
        <p>Dashboard admin menggunakan integrasi <strong>Leaflet.js</strong> untuk memvisualisasikan radius yang diperbolehkan secara akurat.</p>
        <p>Admin dapat menggeser penanda (marker) di peta untuk menetapkan lokasi kantor pusat baru secara instan tanpa perlu bantuan developer.</p>
    </div>
    <div class="image-container">
        <img class="bleed-image-side" src="http://googleusercontent.com/image_collection/image_retrieval/6038904167004395721" alt="Professional web dashboard with maps">
    </div>
</div>

<div class="slide-container" id="slide5">
    <h2 class="slide-title">Konsep Radius Digital</h2>
    <div class="content-area">
        <div class="two-column" style="align-items: center;">
            <div>
                <h3>Geofencing Haversine</h3>
                <p>Sistem menggunakan kalkulasi jarak matematis berdasarkan kelengkungan bumi untuk menjamin presisi hingga satuan meter.</p>
                <div id="formula-container" style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                    <math xmlns="http://www.w3.org/1998/Math/MathML" display="block">
                      <mi>d</mi>
                      <mo>=</mo>
                      <mn>2</mn>
                      <mi>r</mi>
                      <mi>asin</mi>
                      <mfenced>
                        <msqrt>
                          <msup>
                            <mi>sin</mi>
                            <mn>2</mn>
                          </msup>
                          <mfenced>
                            <mfrac>
                              <mrow>
                                <mi>&#x394;</mi>
                                <mi>&#x3C6;</mi>
                              </mrow>
                              <mn>2</mn>
                            </mfrac>
                          </mfenced>
                          <mo>+</mo>
                          <mi>cos</mi>
                          <mo>&#x2061;</mo>
                          <mo>(</mo>
                          <msub>
                            <mi>&#x3C6;</mi>
                            <mn>1</mn>
                          </msub>
                          <mo>)</mo>
                          <mi>cos</mi>
                          <mo>&#x2061;</mo>
                          <mo>(</mo>
                          <msub>
                            <mi>&#x3C6;</mi>
                            <mn>2</mn>
                          </msub>
                          <mo>)</mo>
                          <msup>
                            <mi>sin</mi>
                            <mn>2</mn>
                          </msup>
                          <mfenced>
                            <mfrac>
                              <mrow>
                                <mi>&#x394;</mi>
                                <mi>&#x3BB;</mi>
                              </mrow>
                              <mn>2</mn>
                            </mfrac>
                          </mfenced>
                        </msqrt>
                      </mfenced>
                    </math>
                </div>
            </div>
            <div class="image-wrapper">
                <img src="http://googleusercontent.com/image_collection/image_retrieval/4459919163253103487" alt="Map with radius circle">
            </div>
        </div>
    </div>
</div>

<div class="slide-container" id="slide6">
    <h2 class="slide-title">Logika Pembaruan Dinamis</h2>
    <div class="content-area">
        <div class="two-column">
            <div style="background: rgba(255,255,255,0.03); padding: 30px; border-radius: 12px;">
                <h3>Pembaruan Lokasi</h3>
                <p>Metode POST dari dashboard akan memperbarui database <code>settings</code>, menggantikan ketergantungan pada <code>env()</code>.</p>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 30px; border-radius: 12px;">
                <h3>Validasi Webhook</h3>
                <p>Saat koordinat masuk dari Fonnte, sistem secara otomatis menarik koordinat pusat terbaru dari database untuk divalidasi.</p>
            </div>
        </div>
    </div>
</div>

<div class="slide-container" id="slide7">
    <h2 class="slide-title">Fitur Panel Kontrol</h2>
    <div class="content-area">
        <div class="tiled-content">
            <div class="tile">
                <div class="icon"><i class="fa-solid fa-map-location-dot"></i></div>
                <h3>Set Koordinat</h3>
                <p>Klik langsung pada peta untuk menentukan titik pusat sekolah atau kantor Anda.</p>
            </div>
            <div class="tile">
                <div class="icon"><i class="fa-solid fa-arrows-up-down-left-right"></i></div>
                <h3>Radius Custom</h3>
                <p>Atur batas toleransi jarak dalam satuan meter (misal: 50m, 100m, atau 500m).</p>
            </div>
            <div class="tile">
                <div class="icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Monitoring</h3>
                <p>Lihat secara real-time siapa saja yang berhasil absen atau yang gagal karena di luar area.</p>
            </div>
        </div>
    </div>
</div>

<div class="slide-container" id="slide8">
    <h2 class="slide-title">Riwayat Presensi Real-time</h2>
    <div class="content-area">
        <table>
            <thead>
                <tr>
                    <th>Nama/Nomor</th>
                    <th>Waktu Masuk</th>
                    <th>Status Lokasi</th>
                    <th>Jarak (m)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>628523026xxxx</td>
                    <td>08:10 WIB</td>
                    <td><span style="color:#22c55e;">✅ Dalam Radius</span></td>
                    <td>12 meter</td>
                </tr>
                <tr>
                    <td>628123456xxxx</td>
                    <td>08:15 WIB</td>
                    <td><span style="color:#ef4444;">❌ Luar Jangkauan</span></td>
                    <td>1.250 meter</td>
                </tr>
                <tr>
                    <td>628533590xxxx</td>
                    <td>08:17 WIB</td>
                    <td><span style="color:#22c55e;">✅ Dalam Radius</span></td>
                    <td>45 meter</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="slide-container" id="slide9">
    <h2 class="slide-title">Efektivitas Geofencing</h2>
    <div class="content-area">
        <div class="highlight-numbers-layout">
            <div class="number-box">
                <div class="number">100<span>m</span></div>
                <div class="number-label">Radius Standar</div>
            </div>
            <div style="flex-grow: 1;">
                <h3>Keamanan & Presisi</h3>
                <p>Dengan radius 100 meter, sistem memastikan integritas data kehadiran hingga 99%. Penggunaan integrasi GPS WhatsApp memberikan akurasi tinggi dibandingkan hanya menggunakan pelacakan berbasis IP Address.</p>
            </div>
        </div>
    </div>
</div>

<div class="slide-container" id="slide10">
    <h2 class="slide-title">Langkah Implementasi</h2>
    <div class="content-area">
        <div class="bullet-list">
            <ul>
                <li><i class="fa-solid fa-database"></i> <strong>Migrasi Tabel Setting:</strong> Buat tabel baru untuk menyimpan latitude, longitude, dan radius pusat.</li>
                <li><i class="fa-solid fa-code"></i> <strong>Backend Controller:</strong> Buat rute Admin untuk mengelola data koordinat melalui form Laravel.</li>
                <li><i class="fa-solid fa-earth-asia"></i> <strong>Peta Interaktif:</strong> Implementasikan Leaflet.js pada View untuk visualisasi area presensi.</li>
                <li><i class="fa-solid fa-shield-check"></i> <strong>Update Webhook:</strong> Sesuaikan logika Haversine agar membaca koordinat dari database bukan .env.</li>
            </ul>
        </div>
    </div>
</div>

<div class="slide-container" id="slide11" style="align-items: center; text-align: center;">
    <div style="width: 100px; height: 100px; background: #22d3ee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 30px;">
        <i class="fa-solid fa-question" style="font-size: 50px; color: #0f172a;"></i>
    </div>
    <h2 style="font-size: 60px;">Ada Pertanyaan?</h2>
    <p style="font-size: 24px;">Sistem presensi geofencing Anda kini siap dioperasikan secara profesional.</p>
    <p style="color: #22d3ee; margin-top: 40px;">admin@notifperpus.ac.id | 2026</p>
</div>

<div class="slide-container" id="slide12" style="background-image: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('http://googleusercontent.com/image_collection/image_retrieval/7969397109590000681'); background-size: cover; justify-content: center; align-items: center; text-align: center;">
    <h2 style="font-size: 72px;">Masa Depan<br>Presensi Digital</h2>
    <p style="font-size: 28px; max-width: 800px; margin-top: 20px;">Efisiensi, Akurasi, dan Kemudahan Pengelolaan dalam satu genggaman.</p>
</div>

</body>
</html>

