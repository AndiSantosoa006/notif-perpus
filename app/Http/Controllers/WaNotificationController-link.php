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

    public function webhook(Request $request)
    {
        $pesanMasuk = strtolower($request->message ?? '');
        $pengirim = $request->sender ?? '';
        $timestamp = $request->timestamp ?? '';

        // 1. Abaikan jika ini hanya ping koneksi dari Fonnte
        if ($request->isMethod('get') || empty($pengirim)) {
            return response('OK', 200);
        }

        // --- 2. PINTU GERBANG ANTI SPAM ---
        $messageId = 'msg_' . $pengirim . '_' . $timestamp;

        // Jika pesan ini sudah pernah diproses sebelumnya, LANGSUNG TOLAK!
        // Jangan catat ke log, jangan balas chat WA, langsung kembalikan OK ke Fonnte.
        if (Cache::has($messageId) && $timestamp != '') {
            return response('OK', 200);
        }

        // Jika ini pesan baru, ingat ID-nya selama 10 menit
        if ($timestamp != '') {
            Cache::put($messageId, true, now()->addMinutes(10));
        }

        // --- 3. CATAT LOG HANYA UNTUK PESAN BARU ---
        Log::info('PESAN BARU MASUK: ', ['pengirim' => $pengirim, 'pesan' => $pesanMasuk]);

        $balasan = "";

        // --- 4. LOGIKA 1 BANDING 1 SANGAT KETAT ---
        if ($pesanMasuk == 'absen') {
            $baseUrl = rtrim(config('app.url'), '/');
            $linkPresensi = $baseUrl . "/presensi/" . $pengirim;

            $balasan = "📍 *Presensi Kehadiran*\n\nSilakan klik tautan khusus di bawah ini untuk mengonfirmasi kehadiran Anda:\n\n$linkPresensi\n\n_Catatan: Sistem akan meminta izin untuk membaca titik GPS asli dari perangkat Anda._";
        } elseif ($pesanMasuk == 'halo' || $pesanMasuk == 'ping') {
            $balasan = "Halo! Silakan ketik *ABSEN* untuk melakukan presensi kehadiran.";
        }

        // --- 5. KIRIM BALASAN WA ---
        if ($balasan != "") {
            $token = env('FONNTE_TOKEN');
            Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target' => $pengirim,
                'message' => $balasan,
            ]);
        }

        // --- 6. LAPORKAN KEBERHASILAN KE FONNTE ---
        // Menggunakan format Teks murni agar Fonnte berhenti melakukan Retry
        return response('OK', 200);
    }
}
