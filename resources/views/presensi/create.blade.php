<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Kehadiran</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 text-center">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Presensi Digital</h2>
        <p class="text-slate-500 mb-6 text-sm">Nomor: <span class="font-semibold">{{ $wa }}</span></p>

        <div id="status-container" class="py-6">
            <div id="loading-spinner" class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-600 mx-auto mb-4"></div>
            <p id="status-text" class="text-slate-600 font-medium animate-pulse">Sedang mencari lokasi Anda...</p>
        </div>

        <button id="btn-retry" onclick="getLocation()" class="hidden w-full mt-4 bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
            Coba Ambil Lokasi Ulang
        </button>

        <p class="mt-6 text-xs text-slate-400">Sistem menggunakan GPS bawaan perangkat. Pastikan Anda mengizinkan akses lokasi pada browser.</p>
    </div>

   <script>
        const waNumber = "{{ $wa }}";
        const statusText = document.getElementById('status-text');
        const spinner = document.getElementById('loading-spinner');
        const btnRetry = document.getElementById('btn-retry');

        // Jalankan saat halaman dibuka
        window.onload = function() {
            // Beri sedikit jeda 1 detik agar UI selesai me-render sebelum meminta GPS
            setTimeout(getLocation, 1000);
        };

        function getLocation() {
            statusText.innerText = "Membaca koordinat GPS...";
            statusText.className = "text-slate-600 font-medium animate-pulse";
            spinner.classList.remove('hidden');
            btnRetry.classList.add('hidden');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(sendPosition, showError, {
                    enableHighAccuracy: false, // Ubah ke FALSE agar jauh lebih cepat (mengizinkan deteksi via WiFi/Tower Seluler)
                    timeout: 15000,            // Perpanjang waktu tunggu maksimal jadi 15 detik
                    maximumAge: 0
                });
            } else {
                showError({ code: 0, message: "Browser Anda tidak mendukung fitur Geolocation." });
            }
        }

        function sendPosition(position) {
            statusText.innerText = "Mencocokkan lokasi dengan area presensi...";

            let data = {
                nomor_wa: waNumber,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };

            fetch("{{ route('presensi.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                spinner.classList.add('hidden');
                statusText.classList.remove('animate-pulse');

                if (result.status === 'success') {
                    statusText.innerText = result.message;
                    statusText.className = "text-emerald-600 font-bold text-lg";
                } else {
                    statusText.innerText = result.message;
                    statusText.className = "text-red-600 font-bold";
                    btnRetry.classList.remove('hidden');
                }
            })
            .catch(error => {
                // Perbaikan: Mematikan loading jika koneksi ke Laravel gagal
                spinner.classList.add('hidden');
                statusText.classList.remove('animate-pulse');
                statusText.className = "text-red-600 font-semibold";
                statusText.innerText = "Gagal terhubung ke server. Pastikan internet lancar dan coba lagi.";
                btnRetry.classList.remove('hidden');
            });
        }

        function showError(error) {
            spinner.classList.add('hidden');
            statusText.classList.remove('animate-pulse');
            statusText.className = "text-red-600 font-semibold";
            btnRetry.classList.remove('hidden');

            switch(error.code) {
                case error.PERMISSION_DENIED:
                    statusText.innerText = "Akses ditolak! Anda harus mengizinkan akses lokasi (GPS) pada browser/HP Anda.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    statusText.innerText = "Sinyal GPS tidak tersedia. Nyalakan GPS HP Anda atau keluarlah dari ruangan tertutup.";
                    break;
                case error.TIMEOUT:
                    statusText.innerText = "Pencarian lokasi terlalu lama (Timeout). Sinyal GPS lemah, silakan coba lagi.";
                    break;
                default:
                    statusText.innerText = "Terjadi kesalahan: " + error.message;
                    break;
            }
        }
    </script>
</body>
</html>
