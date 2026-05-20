<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Geofencing Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 450px; width: 100%; border-radius: 0.5rem; z-index: 1; }
    </style>
</head>
<body class="background-slate-50 bg-slate-900 text-slate-100 font-sans antialiased">

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8 border-b border-slate-800 pb-4">
            <h1 class="text-3xl font-bold tracking-tight text-white">📍 Dashboard Batas Radius Presensi</h1>
            <p class="mt-2 text-sm text-slate-400">Atur lokasi titik pusat sekolah/kantor dan tentukan jarak maksimal jangkauan absensi WhatsApp.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-950 border border-emerald-500 text-emerald-200 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 h-fit">
                <h2 class="text-xl font-semibold mb-4 text-white border-b border-slate-700 pb-2">Konfigurasi Wilayah</h2>

                <form action="{{ route('admin.geofencing.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Latitude Pusat</label>
                        <input type="text" id="latitude" name="center_latitude" value="{{ $lat }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-cyan-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Longitude Pusat</label>
                        <input type="text" id="longitude" name="center_longitude" value="{{ $lng }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-cyan-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Radius Maksimal (Meter)</label>
                        <input type="number" id="radius" name="max_radius" value="{{ $radius }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-cyan-500 transition text-sm">
                    </div>

                    <div class="p-3 bg-slate-900/50 rounded-lg border border-slate-700/50 text-xs text-slate-400">
                        💡 <strong>Petunjuk:</strong> Anda bisa mengisi koordinat secara instan dengan cara mengklik langsung titik manapun di dalam peta interaktif di sebelah kanan.
                    </div>

                    <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-medium py-2.5 rounded-lg transition text-sm shadow-md">
                        Simpan Area Presensi
                    </button>
                </form>
            </div>

            <div class="lg:grid lg:col-span-2 bg-slate-800 p-6 rounded-xl border border-slate-700">
                <h2 class="text-xl font-semibold mb-4 text-white border-b border-slate-700 pb-2">Peta Jangkauan</h2>
                <div id="map" class="border border-slate-700"></div>
            </div>
        </div>

        <div class="mt-8 bg-slate-800 p-6 rounded-xl border border-slate-700">
            <h2 class="text-xl font-semibold mb-4 text-white border-b border-slate-700 pb-2">Log Aktif Presensi Terakhir</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Nomor WhatsApp</th>
                            <th class="px-4 py-3">Koordinat User</th>
                            <th class="px-4 py-3">Waktu Masuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($riwayatPresensi as $row)
                            <tr class="hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 font-medium text-white">{{ $row->nomor_wa }}</td>
                                <td class="px-4 py-3 text-slate-400 font-mono">{{ $row->koordinat }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $row->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada riwayat presensi yang terekam hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Ambil nilai koordinat awal dari database melalui blade
        let initialLat = parseFloat(document.getElementById('latitude').value);
        let initialLng = parseFloat(document.getElementById('longitude').value);
        let initialRadius = parseInt(document.getElementById('radius').value);

        // Inisialisasi Peta Leaflet
        const map = L.map('map').setView([initialLat, initialLng], 16);

        // Tambahkan desain peta OpenStreetMap standar
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Pasang Penanda (Marker) Pusat Koordinat
        let centerMarker = L.marker([initialLat, initialLng], { draggable: false }).addTo(map);

        // Buat Lingkaran Jangkauan Radius Geofencing
        let radiusCircle = L.circle([initialLat, initialLng], {
            color: '#22d3ee',       // Garis lingkaran (Cyan)
            fillColor: '#22d3ee',   // Isi lingkaran
            fillOpacity: 0.15,      // Transparansi isi lingkaran
            radius: initialRadius   // Ukuran radius dalam meter
        }).addTo(map);

        // Event saat peta diklik: pindahkan titik koordinat pusat dan perbarui form input
        map.on('click', function(e) {
            let clickedLat = e.latlng.lat;
            let clickedLng = e.latlng.lng;

            // Update form input teks
            document.getElementById('latitude').value = clickedLat;
            document.getElementById('longitude').value = clickedLng;

            // Pindahkan posisi marker dan lingkaran radius di peta
            centerMarker.setLatLng(e.latlng);
            radiusCircle.setLatLng(e.latlng);
        });

        // Event saat kolom input Radius diubah secara manual: perbarui lingkaran peta secara langsung
        document.getElementById('radius').addEventListener('input', function(e) {
            let currentRadius = parseInt(e.target.value);
            if (!isNaN(currentRadius) && currentRadius > 0) {
                radiusCircle.setRadius(currentRadius);
            }
        });
    </script>
</body>
</html>
