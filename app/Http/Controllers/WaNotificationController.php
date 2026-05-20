<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Presensi;
use App\Models\Setting;

class WaNotificationController extends Controller
{
    public function index()
    {
        return view('wa.index');
    }

    // Tampilkan Halaman Dashboard Geofencing
    public function geofencingDashboard()
    {
        $lat = Setting::where('key', 'center_latitude')->value('value');
        $lng = Setting::where('key', 'center_longitude')->value('value');
        $radius = Setting::where('key', 'max_radius')->value('value');

        $riwayatPresensi = Presensi::latest()->take(10)->get();

        return view('admin.geofencing', compact('lat', 'lng', 'radius', 'riwayatPresensi'));
    }

    // Proses Simpan Perubahan dari Map/Form Dashboard
    public function updateGeofencing(Request $request)
    {
        $request->validate([
            'center_latitude' => 'required|numeric',
            'center_longitude' => 'required|numeric',
            'max_radius' => 'required|numeric|min:5',
        ]);

        Setting::where('key', 'center_latitude')->update(['value' => $request->center_latitude]);
        Setting::where('key', 'center_longitude')->update(['value' => $request->center_longitude]);
        Setting::where('key', 'max_radius')->update(['value' => $request->max_radius]);

        return back()->with('success', 'Konfigurasi batas wilayah presensi berhasil diperbarui!');
    }

    // Webhook Fonnte (Metode Share Location WA + Anti Spam Ketat)
    public function webhook(Request $request)
    {
        $pesanMasuk = strtolower($request->message ?? '');
        $pengirim = $request->sender ?? '';
        $timestamp = $request->timestamp ?? 0; // Waktu pengiriman pesan (Unix Timestamp)

        // 1. Abaikan ping/koneksi awal Fonnte
        if ($request->isMethod('get') || empty($pengirim)) {
            return response('OK', 200);
        }

        // --- 2. FILTER PESAN BASI (EXPIRED) ---
        // Jika selisih waktu saat ini dengan waktu pesan dikirim > 180 detik (3 menit),
        // abaikan pesan ini agar tidak numpuk saat server baru di-restart.
        $waktuSekarang = time();
        if ($timestamp > 0 && ($waktuSekarang - $timestamp) > 180) {
            return response('OK', 200);
        }

        // --- 3. SISTEM ANTI SPAM (Mencegah Double-Reply Fonnte) ---
        $messageId = 'msg_' . $pengirim . '_' . $timestamp;

        if (Cache::has($messageId) && $timestamp != '') {
            return response('OK', 200);
        }

        if ($timestamp != '') {
            Cache::put($messageId, true, now()->addMinutes(10));
        }

        // --- 4. CATAT LOG PESAN BARU ---
        Log::info('PESAN BARU MASUK: ', ['pengirim' => $pengirim, 'pesan' => $pesanMasuk, 'lokasi' => $request->location]);

        $balasan = "";

        // --- 5. LOGIKA SKENARIO PRESENSI ---

        if ($request->location != null || str_contains($pesanMasuk, 'loc:')) {
            $koordinat = $request->location ?? $pesanMasuk;
            $waktu = now()->format('d-m-Y H:i');

            $userCoords = explode(',', $koordinat);
            $userLat = trim($userCoords[0] ?? 0);
            $userLong = trim($userCoords[1] ?? 0);

            $centerLat = Setting::where('key', 'center_latitude')->value('value');
            $centerLong = Setting::where('key', 'center_longitude')->value('value');
            $maxRadius = Setting::where('key', 'max_radius')->value('value') ?? 100;

            $jarakUser = $this->calculateDistance($centerLat, $centerLong, $userLat, $userLong);

            if ($jarakUser > $maxRadius) {
                $jarakFormat = $jarakUser >= 1000
                    ? number_format($jarakUser / 1000, 2) . " km"
                    : round($jarakUser) . " meter";

                $balasan = "❌ *Presensi Ditolak (Luar Jangkauan)!*\n\nAnda terdeteksi berada sejauh *$jarakFormat* dari area resmi.\n\nSilakan lakukan presensi kembali di dalam radius maksimal $maxRadius meter.";
            } else {
                try {
                    Presensi::create([
                        'nomor_wa' => $pengirim,
                        'koordinat' => $koordinat,
                    ]);

                    $balasan = "✅ *Presensi Berhasil!*\n\nData kehadiran Anda telah tercatat.\n\nNomor: $pengirim\nWaktu: $waktu\nJarak: " . round($jarakUser) . " meter dari pusat.";
                } catch (\Exception $e) {
                    Log::error('GAGAL SIMPAN ABSEN: ' . $e->getMessage());
                    $balasan = "❌ *Presensi Gagal!*\n\nMohon maaf, terjadi gangguan teknis saat menyimpan data absensi Anda.";
                }
            }
        } elseif ($pesanMasuk == 'absen') {
            $balasan = "📍 *Presensi Kehadiran*\n\nUntuk melakukan presensi hari ini, silakan gunakan fitur *Kirim Lokasi (Share Location)* di WhatsApp Anda sekarang.";
        } elseif ($pesanMasuk == 'halo' || $pesanMasuk == 'ping') {
            $balasan = "Halo! Silakan ketik *ABSEN* untuk melakukan presensi kehadiran.";
        }

        // --- 6. KIRIM BALASAN KE WA ---
        if ($balasan != "") {
            $token = env('FONNTE_TOKEN');
            Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target' => $pengirim,
                'message' => $balasan,
            ]);
        }

        // --- 7. LAPORKAN KEBERHASILAN KE FONNTE ---
        return response('OK', 200);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
