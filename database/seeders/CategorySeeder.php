<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['es' => 'Programación y desarrollo', 'ca' => 'Programació i desenvolupament'],
                'badge' => ['es' => 'P&D', 'ca' => 'P&D'],
                'short_description' => [
                    'es' => 'Diseño y desarrollo de aplicaciones web y backend. Laravel, React, Vue, Java, Python, bases de datos y metodologías ágiles.',
                    'ca' => "Disseny i desenvolupament d'aplicacions web i backend. Laravel, React, Vue, Java, Python, bases de dades i metodologies àgils.",
                ],
            ],
            [
                'name' => ['es' => 'Ciberseguridad', 'ca' => 'Ciberseguretat'],
                'badge' => ['es' => 'CS', 'ca' => 'CS'],
                'short_description' => [
                    'es' => 'Seguridad en redes, hacking ético, análisis de vulnerabilidades y protección de sistemas e infraestructuras digitales.',
                    'ca' => 'Seguretat en xarxes, hacking ètic, anàlisi de vulnerabilitats i protecció de sistemes i infraestructures digitals.',
                ],
            ],
            [
                'name' => ['es' => 'Tecnologías emergentes', 'ca' => 'Tecnologies emergents'],
                'badge' => ['es' => 'TE', 'ca' => 'TE'],
                'short_description' => [
                    'es' => 'Inteligencia artificial, IoT, automatización y nuevas tendencias tecnológicas aplicadas a entornos profesionales.',
                    'ca' => 'Intel·ligència artificial, IoT, automatització i noves tendències tecnològiques aplicades a entorns professionals.',
                ],
            ],
            [
                'name' => ['es' => 'Competencias digitales', 'ca' => 'Competències digitals'],
                'badge' => ['es' => 'CD', 'ca' => 'CD'],
                'short_description' => [
                    'es' => 'Herramientas colaborativas, UX/UI, productividad digital y alfabetización tecnológica para el entorno laboral.',
                    'ca' => "Eines col·laboratives, UX/UI, productivitat digital i alfabetització tecnològica per a l'entorn laboral.",
                ],
            ],
        ];

        foreach ($categories as $data) {
            $slug = Str::slug($data['name']['es']);

            if (Category::where('slug->es', $slug)->exists()) {
                continue;
            }

            Category::create([
                'name' => $data['name'],
                'badge' => $data['badge'],
                'short_description' => $data['short_description'],
                'slug' => [
                    'es' => $slug,
                    'ca' => Str::slug($data['name']['ca']),
                ],
            ]);
        }
    }
}
