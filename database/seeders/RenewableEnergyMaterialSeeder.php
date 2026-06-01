<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Module;
use Illuminate\Database\Seeder;

class RenewableEnergyMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'energi-terbarukan')->first();

        if (! $module) {
            return;
        }

        foreach ($module->learningUnits()->orderBy('order')->get() as $unit) {
            foreach ($this->materialsFor($unit->order) as $index => $material) {
                Material::updateOrCreate(
                    [
                        'learning_unit_id' => $unit->id,
                        'title' => $material['title'],
                    ],
                    [
                        'content' => $material['content'],
                        'material_type' => 'text',
                        'order' => $index + 1,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    private function materialsFor(int $order): array
    {
        return match ($order) {
            1 => [
                ['title' => 'Konsep Energi', 'content' => 'Energi adalah kemampuan untuk melakukan kerja atau menyebabkan perubahan. Dalam Projek IPAS, energi diamati dari aktivitas nyata seperti menyalakan lampu, menggerakkan kipas, memanaskan air, dan mengisi daya perangkat.'],
                ['title' => 'Bentuk Energi', 'content' => 'Bentuk energi yang sering ditemukan meliputi energi cahaya, panas, gerak, listrik, kimia, dan bunyi. Setiap alat di sekitar sekolah biasanya melibatkan lebih dari satu bentuk energi.'],
                ['title' => 'Perubahan Energi', 'content' => 'Energi tidak hilang, tetapi dapat berubah bentuk. Perubahan energi dicatat dengan menyebutkan energi masuk, alat yang digunakan, dan energi keluar agar pemborosan energi dapat dikenali.'],
                ['title' => 'Sumber Energi', 'content' => 'Sumber energi dapat berasal dari proses alam yang dapat diperbarui maupun sumber terbatas seperti energi fosil. Indonesia memiliki potensi surya, air, angin, biomassa, dan panas bumi.'],
            ],
            2 => [
                ['title' => 'Pengertian Energi Fosil', 'content' => 'Energi fosil berasal dari sisa makhluk hidup purba yang mengalami proses geologi sangat lama sehingga digolongkan sebagai sumber energi tidak terbarukan.'],
                ['title' => 'Jenis-Jenis Energi Fosil', 'content' => 'Jenis energi fosil utama adalah batu bara, minyak bumi, dan gas alam. Batu bara banyak dipakai untuk pembangkit listrik, minyak bumi untuk kendaraan, dan gas alam untuk industri.'],
                ['title' => 'Ketergantungan Indonesia terhadap Energi Fosil', 'content' => 'Aktivitas listrik, transportasi, dan industri masih banyak bergantung pada energi fosil sehingga transisi energi perlu dilakukan bertahap.'],
                ['title' => 'Masalah-Masalah Energi Fosil', 'content' => 'Pembakaran energi fosil menghasilkan emisi gas rumah kaca dan polutan udara yang berdampak pada pemanasan global, kesehatan, dan kualitas lingkungan.'],
                ['title' => 'Contoh Masalah Energi Fosil dalam Kehidupan Sehari-Hari', 'content' => 'Contoh masalah terlihat dari asap kendaraan, listrik boros, pemakaian genset, dan konsumsi bahan bakar yang tidak efisien.'],
            ],
            3 => [
                ['title' => 'Pengertian Energi Terbarukan', 'content' => 'Energi terbarukan berasal dari proses alam yang terus tersedia atau dapat diperbarui dalam skala waktu manusia.'],
                ['title' => 'Pentingnya Energi Terbarukan', 'content' => 'Energi terbarukan penting untuk mengurangi emisi, menjaga keberlanjutan sumber daya, dan memperluas akses energi bersih.'],
                ['title' => 'Jenis-Jenis Energi Terbarukan', 'content' => 'Jenis energi terbarukan meliputi surya, angin, air, panas bumi, biomassa, dan energi laut.'],
                ['title' => 'Contoh Pemanfaatan Energi Terbarukan', 'content' => 'Panel surya dapat digunakan untuk penerangan, mikrohidro untuk daerah dekat sungai, biogas dari limbah organik, dan turbin angin di wilayah berangin.'],
                ['title' => 'Tabel Jenis Energi Terbarukan', 'content' => 'Perbandingan energi terbarukan dapat dibuat dengan kolom sumber alam, contoh teknologi, kelebihan, keterbatasan, dan peluang penerapan.'],
            ],
            4 => [
                ['title' => 'Pengertian Teknologi Energi Terbarukan Berbasis STEM', 'content' => 'Teknologi energi terbarukan berbasis STEM menghubungkan sains, teknologi, rekayasa, dan matematika untuk mengubah energi alam menjadi energi yang dapat digunakan.'],
                ['title' => 'Hubungan STEM dengan Energi Terbarukan', 'content' => 'Sains menjelaskan fenomena energi, teknologi menyediakan alat, rekayasa merancang solusi, dan matematika menghitung daya, suhu, biaya, serta efisiensi.'],
                ['title' => 'Teknologi Panel Surya', 'content' => 'Panel surya mengubah cahaya matahari menjadi listrik melalui sel fotovoltaik. Kinerjanya dipengaruhi intensitas cahaya, sudut, kebersihan, dan durasi penyinaran.'],
                ['title' => 'Teknologi Penyimpanan Energi', 'content' => 'Penyimpanan energi seperti baterai membantu energi terbarukan tetap digunakan saat sumber utama berkurang.'],
                ['title' => 'Teknologi Sederhana Energi Terbarukan', 'content' => 'Teknologi sederhana dapat berupa kompor surya, briket biomassa, lampu taman surya mini, atau model mikrohidro.'],
            ],
            5 => [
                ['title' => 'Identifikasi Masalah Energi', 'content' => 'Proyek dimulai dengan mengamati masalah nyata seperti lampu menyala saat ruangan terang, limbah organik belum dimanfaatkan, atau kebutuhan penerangan hemat energi.'],
                ['title' => 'Pemilihan Solusi', 'content' => 'Solusi dipilih berdasarkan data, ketersediaan alat, biaya, keselamatan, dan potensi dampak.'],
                ['title' => 'Rancangan Proyek', 'content' => 'Rancangan proyek memuat judul, masalah, tujuan, alat bahan, langkah kerja, pembagian tugas, data, hasil yang diharapkan, dan risiko keselamatan.'],
                ['title' => 'Pengumpulan Data', 'content' => 'Data proyek dapat berupa waktu pemakaian listrik, jumlah alat, suhu, foto proses, catatan pengamatan, respons pengguna, atau hasil uji sederhana.'],
                ['title' => 'Komunikasi Hasil Proyek', 'content' => 'Hasil proyek dikomunikasikan melalui laporan, poster, presentasi, foto, atau video yang menjelaskan masalah, solusi, data, hasil, dan kesimpulan.'],
            ],
            default => [],
        };
    }
}
