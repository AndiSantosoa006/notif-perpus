<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    // Menampilkan halaman web untuk mengambil GPS
    public function create($wa)
    {
        return view('presensi.create', compact('wa'));
    }

    // Memproses data GPS yang dikirim oleh Javascript browser
    public function store(Request $request)
    {
        $request->validate([
            'nomor_wa' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $userLat = $request->latitude;
        $userLong = $request->longitude;
        $nomorWa = $request->nomor_wa;
        $koordinat = "$userLat, $userLong";

        // Ambil pengaturan pusat dari database
        $centerLat = Setting::where('key', 'center_latitude')->value('value');
        $centerLong = Setting::where('key', 'center_longitude')->value('value');
        $maxRadius = Setting::where('key', 'max_radius')->value('value') ?? 100;

        // Hitung jarak
        $jarakUser = $this->calculateDistance($centerLat, $centerLong, $userLat, $userLong);

        if ($jarakUser > $maxRadius) {
            $jarakFormat = $jarakUser >= 1000
                ? number_format($jarakUser / 1000, 2) . " km"
                : round($jarakUser) . " meter";

            return response()->json([
                'status' => 'error',
                'message' => "Anda berada di luar area presensi (Jarak: $jarakFormat). Jarak maksimal adalah $maxRadius meter."
            ]);
        }

        try {
            // Jika masuk radius, simpan ke database
            Presensi::create([
                'nomor_wa' => $nomorWa,
                'koordinat' => $koordinat,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Presensi Berhasil! Jarak Anda: " . round($jarakUser) . " meter dari pusat."
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal simpan absen web: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan data.'
            ]);
        }
    }

    // Fungsi penghitung jarak matematis
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
