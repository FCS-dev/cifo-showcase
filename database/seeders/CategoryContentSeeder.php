<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            'programacion-y-desarrollo' => [
                'badge' => ['es' => 'P&D', 'ca' => 'P&D'],
                'short_description' => [
                    'es' => 'Diseño y desarrollo de aplicaciones web y backend. Laravel, React, Vue, Java, Python, bases de datos y metodologías ágiles.',
                    'ca' => "Disseny i desenvolupament d'aplicacions web i backend. Laravel, React, Vue, Java, Python, bases de dades i metodologies àgils.",
                ],
            ],
            'ciberseguridad' => [
                'badge' => ['es' => 'CS', 'ca' => 'CS'],
                'short_description' => [
                    'es' => 'Seguridad en redes, hacking ético, análisis de vulnerabilidades y protección de sistemas e infraestructuras digitales.',
                    'ca' => 'Seguretat en xarxes, hacking ètic, anàlisi de vulnerabilitats i protecció de sistemes i infraestructures digitals.',
                ],
            ],
            'tecnologias-emergentes' => [
                'badge' => ['es' => 'TE', 'ca' => 'TE'],
                'short_description' => [
                    'es' => 'Inteligencia artificial, IoT, automatización y nuevas tendencias tecnológicas aplicadas a entornos profesionales.',
                    'ca' => 'Intel·ligència artificial, IoT, automatització i noves tendències tecnològiques aplicades a entorns professionals.',
                ],
            ],
            'competencias-digitales' => [
                'badge' => ['es' => 'CD', 'ca' => 'CD'],
                'short_description' => [
                    'es' => 'Herramientas colaborativas, UX/UI, productividad digital y alfabetización tecnológica para el entorno laboral.',
                    'ca' => "Eines col·laboratives, UX/UI, productivitat digital i alfabetització tecnològica per a l'entorn laboral.",
                ],
            ],
        ];

        foreach ($contents as $slug => $data) {
            $category = Category::where('slug->es', $slug)->first();

            if (! $category) {
                continue;
            }

            $updates = [];

            if (! $category->getTranslation('badge', 'es', false) || ! $category->getTranslation('badge', 'ca', false)) {
                $updates['badge'] = $data['badge'];
            }

            if (! $category->getTranslation('short_description', 'es', false) || ! $category->getTranslation('short_description', 'ca', false)) {
                $updates['short_description'] = $data['short_description'];
            }

            if ($updates) {
                $category->update($updates);
            }
        }
    }
}
