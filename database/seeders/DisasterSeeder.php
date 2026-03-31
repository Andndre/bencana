<?php

namespace Database\Seeders;

use App\Models\Disaster;
use Illuminate\Database\Seeder;

class DisasterSeeder extends Seeder
{
    public function run(): void
    {
        $disasters = [
            [
                'slug' => 'banjir',
                'name' => 'BANJIR',
                'description' => 'Banjir adalah peristiwa terendamnya suatu daerah atau daratan yang biasanya kering karena volume air yang meningkat. Banjir terjadi ketika air meluap dari saluran (sungai, danau, atau selokan) atau ketika air hujan tidak dapat meresap ke dalam tanah dengan cukup cepat.',
            ],
            [
                'slug' => 'tanah-longsor',
                'name' => 'TANAH LONGSOR',
                'description' => 'Tanah longsor adalah suatu peristiwa geologi di mana terjadi pergerakan massa tanah atau batuan yang meluncur keluar dan ke bawah lereng. Secara sederhana, tanah longsor terjadi karena gangguan kestabilan pada tanah atau batuan penyusun lereng tersebut.',
            ],
            [
                'slug' => 'gempa-bumi',
                'name' => 'GEMPA BUMI',
                'description' => 'Gempa bumi adalah getaran atau guncangan yang terjadi di permukaan bumi akibat pelepasan energi dari dalam secara tiba-tiba. Pelepasan energi ini menciptakan gelombang seismik yang merambat ke seluruh bagian bumi dan merusak struktur bangunan di atasnya.',
            ],
            [
                'slug' => 'tsunami',
                'name' => 'TSUNAMI',
                'description' => 'Tsunami berasal dari bahasa Jepang: tsu (pelabuhan) dan nami (gelombang). Secara ilmiah, tsunami adalah serangkaian gelombang laut raksasa yang timbul karena adanya pergeseran massa air laut dalam skala besar secara mendadak.',
            ],
            [
                'slug' => 'angin-puting-beliung',
                'name' => 'ANGIN PUTING BELIUNG',
                'description' => 'Angin Puting Beliung adalah pusaran angin kencang yang keluar dari awan badai (Cumulonimbus) dan menyentuh permukaan bumi. Di luar negeri, fenomena yang serupa namun dalam skala jauh lebih besar dikenal dengan sebutan Tornado.',
            ],
        ];

        foreach ($disasters as $disaster) {
            Disaster::updateOrCreate(['slug' => $disaster['slug']], $disaster);
        }
    }
}
