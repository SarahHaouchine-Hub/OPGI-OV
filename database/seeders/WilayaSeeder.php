<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wilaya;

class WilayaSeeder extends Seeder
{
    public function run(): void
    {
        Wilaya::insert([
            ['nom' => 'Adrar',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Chlef',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Laghouat',           'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Oum El Bouaghi',     'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Batna',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Béjaïa',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Biskra',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Béchar',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Blida',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Bouira',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tamanrasset',        'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tébessa',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tlemcen',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tiaret',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tizi Ouzou',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Alger',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Djelfa',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Jijel',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Sétif',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Saïda',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Skikda',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Sidi Bel Abbès',     'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Annaba',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Guelma',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Constantine',        'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Médéa',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Mostaganem',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => "M'Sila",             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Mascara',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Ouargla',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Oran',               'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'El Bayadh',          'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Illizi',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Bordj Bou Arréridj', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Boumerdès',          'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'El Tarf',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tindouf',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tissemsilt',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'El Oued',            'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Khenchela',          'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Souk Ahras',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Tipaza',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Mila',               'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Aïn Defla',          'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Naâma',              'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Aïn Témouchent',     'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Ghardaïa',           'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Relizane',           'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Timimoun',           'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Bordj Badji Mokhtar','created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Ouled Djellal',      'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Béni Abbès',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'In Salah',           'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'In Guezzam',         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Touggourt',          'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'Djanet',             'created_at' => now(), 'updated_at' => now()],
            ['nom' => "El M'Ghair",         'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'El Meniaa',          'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}