<?php

namespace Database\Seeders;

use App\Models\Place;
use Illuminate\Database\Seeder;

class AfricanDestinationSeeder extends Seeder
{
    /** @var list<array{id:string,name:string,region:string,country:string,emoji:string,lat:float,lng:float}> */
    private const CITIES = [
        ['id' => 'cotonou', 'name' => 'Cotonou', 'region' => 'Littoral', 'country' => 'Bénin', 'emoji' => '🌆', 'lat' => 6.3654, 'lng' => 2.4183],
        ['id' => 'porto-novo', 'name' => 'Porto-Novo', 'region' => 'Ouémé', 'country' => 'Bénin', 'emoji' => '🏛️', 'lat' => 6.4969, 'lng' => 2.6289],
        ['id' => 'ouidah', 'name' => 'Ouidah', 'region' => 'Atlantique', 'country' => 'Bénin', 'emoji' => '🏛️', 'lat' => 6.3631, 'lng' => 2.0851],
        ['id' => 'grand-popo', 'name' => 'Grand-Popo', 'region' => 'Mono', 'country' => 'Bénin', 'emoji' => '🏝️', 'lat' => 6.2804, 'lng' => 1.8225],
        ['id' => 'abomey', 'name' => 'Abomey', 'region' => 'Zou', 'country' => 'Bénin', 'emoji' => '🏺', 'lat' => 7.1829, 'lng' => 1.9912],
        ['id' => 'ganvie', 'name' => 'Ganvié', 'region' => 'Atlantique', 'country' => 'Bénin', 'emoji' => '🛶', 'lat' => 6.4667, 'lng' => 2.4167],
        ['id' => 'lome', 'name' => 'Lomé', 'region' => 'Maritime', 'country' => 'Togo', 'emoji' => '🌴', 'lat' => 6.1319, 'lng' => 1.2228],
        ['id' => 'kpalime', 'name' => 'Kpalimé', 'region' => 'Plateaux', 'country' => 'Togo', 'emoji' => '🌿', 'lat' => 6.9, 'lng' => 0.6333],
        ['id' => 'accra', 'name' => 'Accra', 'region' => 'Greater Accra', 'country' => 'Ghana', 'emoji' => '🎨', 'lat' => 5.6037, 'lng' => -0.187],
        ['id' => 'cape-coast', 'name' => 'Cape Coast', 'region' => 'Central', 'country' => 'Ghana', 'emoji' => '🏰', 'lat' => 5.1053, 'lng' => -1.2466],
        ['id' => 'dakar', 'name' => 'Dakar', 'region' => 'Dakar', 'country' => 'Sénégal', 'emoji' => '🌊', 'lat' => 14.7167, 'lng' => -17.4677],
        ['id' => 'saint-louis', 'name' => 'Saint-Louis', 'region' => 'Saint-Louis', 'country' => 'Sénégal', 'emoji' => '🏘️', 'lat' => 16.0326, 'lng' => -16.4818],
        ['id' => 'abidjan', 'name' => 'Abidjan', 'region' => 'Lagunes', 'country' => 'Côte d’Ivoire', 'emoji' => '🌇', 'lat' => 5.36, 'lng' => -4.0083],
        ['id' => 'yamoussoukro', 'name' => 'Yamoussoukro', 'region' => 'Lacs', 'country' => 'Côte d’Ivoire', 'emoji' => '🏛️', 'lat' => 6.8276, 'lng' => -5.2893],
        ['id' => 'marrakech', 'name' => 'Marrakech', 'region' => 'Marrakech-Safi', 'country' => 'Maroc', 'emoji' => '🕌', 'lat' => 31.6295, 'lng' => -7.9811],
        ['id' => 'nairobi', 'name' => 'Nairobi', 'region' => 'Nairobi', 'country' => 'Kenya', 'emoji' => '🦒', 'lat' => -1.2921, 'lng' => 36.8219],
        ['id' => 'kigali', 'name' => 'Kigali', 'region' => 'Kigali', 'country' => 'Rwanda', 'emoji' => '🌿', 'lat' => -1.9441, 'lng' => 30.0619],
        ['id' => 'cape-town', 'name' => 'Le Cap', 'region' => 'Cap-Occidental', 'country' => 'Afrique du Sud', 'emoji' => '⛰️', 'lat' => -33.9249, 'lng' => 18.4241],
    ];

    /** @var list<array{id:string,name:string,emoji:string,lat:float,lng:float}> */
    private const COUNTRIES = [
        ['id' => 'benin', 'name' => 'Bénin', 'emoji' => '🇧🇯', 'lat' => 9.5, 'lng' => 2.3, 'zoom' => 4],
        ['id' => 'togo', 'name' => 'Togo', 'emoji' => '🇹🇬', 'lat' => 8.6, 'lng' => 1.1, 'zoom' => 5],
        ['id' => 'ghana', 'name' => 'Ghana', 'emoji' => '🇬🇭', 'lat' => 7.9, 'lng' => -1.0, 'zoom' => 4],
        ['id' => 'senegal', 'name' => 'Sénégal', 'emoji' => '🇸🇳', 'lat' => 14.4, 'lng' => -14.5, 'zoom' => 4],
        ['id' => 'cote-ivoire', 'name' => 'Côte d’Ivoire', 'emoji' => '🇨🇮', 'lat' => 7.5, 'lng' => -5.5, 'zoom' => 4],
        ['id' => 'maroc', 'name' => 'Maroc', 'emoji' => '🇲🇦', 'lat' => 31.8, 'lng' => -6.0, 'zoom' => 4],
        ['id' => 'kenya', 'name' => 'Kenya', 'emoji' => '🇰🇪', 'lat' => 0.2, 'lng' => 37.9, 'zoom' => 4],
        ['id' => 'rwanda', 'name' => 'Rwanda', 'emoji' => '🇷🇼', 'lat' => -2.0, 'lng' => 29.9, 'zoom' => 6],
        ['id' => 'afrique-du-sud', 'name' => 'Afrique du Sud', 'emoji' => '🇿🇦', 'lat' => -29.0, 'lng' => 24.0, 'zoom' => 3],
    ];

    public function run(): void
    {
        foreach (self::CITIES as $city) {
            $place = Place::withTrashed()->firstOrNew([
                'provider' => 'amivoy-demo',
                'provider_place_id' => $city['id'],
            ]);
            $place->fill([
                'name' => $city['name'],
                'category' => 'destination',
                'place_type' => 'city',
                'country' => $city['country'],
                'region' => $city['region'],
                'lat' => $city['lat'],
                'lng' => $city['lng'],
                'address' => $city['name'].', '.$city['country'],
                'cached_data' => ['emoji' => $city['emoji'], 'source' => 'frontend-mock'],
            ]);
            $place->deleted_at = null;
            $place->save();
        }

        $featuredPlace = Place::withTrashed()->firstOrNew([
            'provider' => 'amivoy-demo',
            'provider_place_id' => 'place-etoile-cotonou',
        ]);
        $featuredPlace->fill([
            'name' => 'Place de l’Étoile',
            'category' => 'meeting-point',
            'place_type' => 'place',
            'country' => 'Bénin',
            'region' => 'Littoral',
            'lat' => 6.3702,
            'lng' => 2.4251,
            'address' => 'Cotonou, Bénin',
            'cached_data' => ['emoji' => '📍', 'source' => 'frontend-mock'],
        ]);
        $featuredPlace->deleted_at = null;
        $featuredPlace->save();

        foreach (self::COUNTRIES as $country) {
            $place = Place::withTrashed()->firstOrNew([
                'provider' => 'amivoy-demo',
                'provider_place_id' => 'country-'.$country['id'],
            ]);
            $place->fill([
                'name' => $country['name'],
                'category' => 'destination',
                'place_type' => 'country',
                'country' => $country['name'],
                'lat' => $country['lat'],
                'lng' => $country['lng'],
                'address' => 'Afrique',
                'cached_data' => ['emoji' => $country['emoji'], 'zoom' => $country['zoom'], 'source' => 'frontend-mock'],
            ]);
            $place->deleted_at = null;
            $place->save();
        }
    }
}
