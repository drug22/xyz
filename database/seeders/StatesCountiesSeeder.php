<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StateCounty;

class StatesCountiesSeeder extends Seeder
{
    public function run(): void
    {
        // Județele României
        $romanianCounties = [
            ['AB', 'Alba'],
            ['AR', 'Arad'],
            ['AG', 'Argeș'],
            ['BC', 'Bacău'],
            ['BH', 'Bihor'],
            ['BN', 'Bistrița-Năsăud'],
            ['BT', 'Botoșani'],
            ['BR', 'Brăila'],
            ['BV', 'Brașov'],
            ['B', 'București'],
            ['BZ', 'Buzău'],
            ['CL', 'Călărași'],
            ['CS', 'Caraș-Severin'],
            ['CJ', 'Cluj'],
            ['CT', 'Constanța'],
            ['CV', 'Covasna'],
            ['DB', 'Dâmbovița'],
            ['DJ', 'Dolj'],
            ['GL', 'Galați'],
            ['GR', 'Giurgiu'],
            ['GJ', 'Gorj'],
            ['HR', 'Harghita'],
            ['HD', 'Hunedoara'],
            ['IL', 'Ialomița'],
            ['IS', 'Iași'],
            ['IF', 'Ilfov'],
            ['MM', 'Maramureș'],
            ['MH', 'Mehedinți'],
            ['MS', 'Mureș'],
            ['NT', 'Neamț'],
            ['OT', 'Olt'],
            ['PH', 'Prahova'],
            ['SJ', 'Sălaj'],
            ['SM', 'Satu Mare'],
            ['SB', 'Sibiu'],
            ['SV', 'Suceava'],
            ['TR', 'Teleorman'],
            ['TM', 'Timiș'],
            ['TL', 'Tulcea'],
            ['VL', 'Vâlcea'],
            ['VS', 'Vaslui'],
            ['VN', 'Vrancea'],
        ];

        foreach ($romanianCounties as $county) {
            StateCounty::create([
                'country_code' => 'RO',
                'code' => $county[0],
                'name' => $county[1],
                'type' => 'county',
                'is_active' => true,
            ]);
        }

        // UK Counties
        $ukCounties = [
            ['ENG', 'England'],
            ['SCT', 'Scotland'],
            ['WLS', 'Wales'],
            ['NIR', 'Northern Ireland'],
            // English Counties
            ['BED', 'Bedfordshire'],
            ['BRK', 'Berkshire'],
            ['BUK', 'Buckinghamshire'],
            ['CAM', 'Cambridgeshire'],
            ['CHE', 'Cheshire'],
            ['CON', 'Cornwall'],
            ['CUM', 'Cumbria'],
            ['DER', 'Derbyshire'],
            ['DEV', 'Devon'],
            ['DOR', 'Dorset'],
            ['DUR', 'Durham'],
            ['ESS', 'Essex'],
            ['GLO', 'Gloucestershire'],
            ['HAM', 'Hampshire'],
            ['HRT', 'Hertfordshire'],
            ['KEN', 'Kent'],
            ['LAN', 'Lancashire'],
            ['LEI', 'Leicestershire'],
            ['LIN', 'Lincolnshire'],
            ['LON', 'London'],
            ['NFK', 'Norfolk'],
            ['NTH', 'Northamptonshire'],
            ['NTT', 'Nottinghamshire'],
            ['OXF', 'Oxfordshire'],
            ['SOM', 'Somerset'],
            ['STF', 'Staffordshire'],
            ['SUF', 'Suffolk'],
            ['SUR', 'Surrey'],
            ['WSX', 'West Sussex'],
            ['WAR', 'Warwickshire'],
            ['WIL', 'Wiltshire'],
            ['WOR', 'Worcestershire'],
            ['YOR', 'Yorkshire'],
        ];

        foreach ($ukCounties as $county) {
            StateCounty::create([
                'country_code' => 'GB',
                'code' => $county[0],
                'name' => $county[1],
                'type' => 'county',
                'is_active' => true,
            ]);
        }
    }
}
