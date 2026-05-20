<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendDailyWa extends Command
{
    // Nama perintah untuk dipanggil di jadwal
    protected $signature = 'wa:send-daily';

    // Deskripsi perintah
    protected $description = 'Kirim notifikasi WA otomatis untuk mahasiswa baru';

    public function handle()
    {
        $token = env('FONNTE_TOKEN');
        $target = env('FONNTE_GROUP_TARGET');

        $pesan = "Selamat! Anda dinyatakan LULUS dan diterima sebagai mahasiswa baru. Silakan segera melengkapi dokumen daftar ulang pada sistem akademik kami. 🎓";

        $response = Http::withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target' => $target,
            'message' => $pesan,
        ]);

        // ---- KODE UNTUK BEDAH ERROR ----
        $this->info("=== HASIL DIAGNOSA API === ");
        $this->line("Token yang dibaca Laravel: " . $token);
        $this->line("Target yang dibaca Laravel: " . $target);
        $this->line("Respon Mentah Fonnte: " . $response->body());
        $this->info("========================== ");

        if ($response->successful() && isset($response->json()['status']) && $response->json()['status'] == true) {
            $this->info('Pesan pengumuman berhasil dikirim!');
        } else {
            $this->error('Gagal mengirim pesan pengumuman.');
        }
    }
}
