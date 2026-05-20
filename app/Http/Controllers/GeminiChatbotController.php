<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiChatbotController extends Controller
{
    public function webhook(Request $request)
    {
        $pesanAsli = $request->message ?? '';
        $pengirim = $request->sender ?? '';
        $timestamp = $request->timestamp ?? 0;

        // 1. Abaikan ping dari Fonnte atau pesan kosong
        if ($request->isMethod('get') || empty($pengirim) || empty($pesanAsli)) {
            return response('OK', 200);
        }

        // --- 2. FILTER PESAN BASI ---
        $waktuSekarang = time();
        if ($timestamp > 0 && ($waktuSekarang - $timestamp) > 180) {
            return response('OK', 200);
        }

        // --- 3. PINTU ANTI SPAM & RETRY FONNTE ---
        $messageId = 'msg_ai_' . $pengirim . '_' . $timestamp;

        if (Cache::has($messageId) && $timestamp != '') {
            return response('OK', 200);
        }

        if ($timestamp != '') {
            Cache::put($messageId, true, now()->addMinutes(10));
        }

        // --- 4. CATAT LOG PESAN MASUK ---
        Log::info('TANYA GEMINI (Model 3.1): ', ['pengirim' => $pengirim, 'pesan' => $pesanAsli]);

        // --- 5. PROSES KE GEMINI AI ---
        $balasan = $this->tanyaGeminiAI($pesanAsli);

        // --- 6. KIRIM BALASAN KE WHATSAPP ---
        if ($balasan != "") {
            $token = env('FONNTE_TOKEN');
            Http::withHeaders([
                'Authorization' => $token
            ])->post('https://api.fonnte.com/send', [
                'target' => $pengirim,
                'message' => $balasan,
            ]);
        }

        // --- 7. LAPORAN SUKSES KE FONNTE ---
        return response('OK', 200);
    }

    /**
     * Fungsi Inti untuk Berkomunikasi dengan API Gemini (Model 3.1 Flash Lite)
     */
    private function tanyaGeminiAI($pertanyaanUser)
    {
        // Mengambil API Key dari .env (bisa disesuaikan jika Anda pakai config lain)
        $apiKey = env('GEMINI_API_KEY') ?? config('services.gemini.key');

        if (!$apiKey) {
            Log::error('Gemini API Key belum dipasang di file .env');
            return "Maaf, asisten virtual sedang dalam perbaikan (API Key belum diatur).";
        }

        // MENGGUNAKAN MODEL TERBARU: gemini-3.1-flash-lite-preview
        // (Menggunakan generateContent, BUKAN streamGenerateContent karena ini untuk WhatsApp)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $apiKey;

        // "Otak" Bot: Karakter dan Pengetahuan Dasar Asisten
        $pengetahuanDasar = "
            Anda adalah asisten virtual cerdas yang melayani informasi akademik dan Perpustakaan Ibrahimy.
            Gaya bahasa Anda: Ramah, profesional, ringkas, dan berbahasa Indonesia yang baik.

            Informasi operasional:
            - Jam layanan: Senin hingga Sabtu, pukul 07:00 - 14:00 WIB. Hari Minggu libur.
            - Pengunjung dapat mencari buku melalui sistem OPAC.

            Aturan menjawab:
            - Jawablah sesingkat dan sejelas mungkin, jangan bertele-tele.
            - Jika ditanya hal yang tidak pantas atau tidak terkait pendidikan/sekolah/perpustakaan, tolak dengan halus.
            - Jangan pernah menampilkan format kode atau markdown yang rumit ke pengguna WhatsApp.
        ";

        $promptSistem = "Instruksi Sistem:\n" . $pengetahuanDasar . "\n\nChat dari Pengguna:\n" . $pertanyaanUser;

        try {
            $response = Http::post($url, [
                'contents' => [
                    ['parts' => [['text' => $promptSistem]]]
                ]
            ]);

            if ($response->successful()) {
                $jawaban = $response->json('candidates.0.content.parts.0.text');
                return trim($jawaban);
            } else {
                Log::error('Gemini 3.1 Error Response: ' . $response->body());
                return "Mohon maaf, saya sedang kesulitan memproses pertanyaan Anda saat ini. Silakan coba lagi nanti.";
            }
        } catch (\Exception $e) {
            Log::error('Gemini Connection Error: ' . $e->getMessage());
            return "Mohon maaf, koneksi ke server pusat sedang terganggu. Silakan hubungi admin.";
        }
    }
}
