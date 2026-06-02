<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cafe_profiles')->update([
            'name' => 'Cafe Amiko',
            'description' => 'Creative coffee space yang hangat untuk kopi, musik, obrolan, dan pengalaman komunitas.',
            'address' => 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221',
            'phone_number' => '0411-889900',
            'reservation_rules' => 'Reservasi dilakukan sesuai slot yang aktif. Pembatalan sebaiknya diinformasikan secepat mungkin kepada tim.',
        ]);
    }

    public function down(): void
    {
        //
    }
};
