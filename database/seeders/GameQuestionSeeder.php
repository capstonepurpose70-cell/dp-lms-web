<?php

namespace Database\Seeders;

use App\Models\GameQuestion;
use Illuminate\Database\Seeder;

class GameQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [

            // ───────────── GRADE 11 — Physical Science (Formula Clash) ─────────────
            ['11', 'Chemistry', 'easy', 'Ano ang pinakamaliit na unit ng isang element na nagtataglay pa rin ng properties nito?',
                ['Molecule', 'Atom', 'Compound', 'Ion'], 1],
            ['11', 'Chemistry', 'easy', 'Aling subatomic particle ang may negatibong charge?',
                ['Proton', 'Neutron', 'Electron', 'Nucleus'], 2],
            ['11', 'Physics', 'easy', 'Ano ang SI unit ng force?',
                ['Joule', 'Watt', 'Newton', 'Pascal'], 2],
            ['11', 'Physics', 'medium', 'Ayon sa Newton\'s Second Law, F = ma. Kung m = 2 kg at a = 3 m/s², ano ang F?',
                ['5 N', '6 N', '1.5 N', '8 N'], 1],
            ['11', 'Chemistry', 'medium', 'Anong uri ng bond ang nabubuo kapag ang mga atomo ay NAGBABAHAGI ng electrons?',
                ['Ionic bond', 'Covalent bond', 'Metallic bond', 'Hydrogen bond'], 1],
            ['11', 'Chemistry', 'medium', 'Ilan ang protons sa isang atom ng Carbon (atomic number 6)?',
                ['12', '6', '8', '14'], 1],
            ['11', 'Physics', 'medium', 'Ano ang tawag sa enerhiya dahil sa galaw (motion) ng isang bagay?',
                ['Potential energy', 'Kinetic energy', 'Thermal energy', 'Chemical energy'], 1],
            ['11', 'Physics', 'hard', 'Isang bagay na 10 kg ay tumataas sa 5 m. Ano ang potential energy nito? (g = 9.8 m/s²)',
                ['49 J', '490 J', '50 J', '98 J'], 1],
            ['11', 'Chemistry', 'hard', 'Sa chemical equation na 2H₂ + O₂ → 2H₂O, ilan ang water molecules na nabubuo mula sa 2 hydrogen molecules?',
                ['1', '2', '3', '4'], 1],
            ['11', 'Physics', 'hard', 'Kung ang isang sasakyan ay tumakbo ng 100 m sa loob ng 20 s, ano ang average speed nito?',
                ['2 m/s', '5 m/s', '20 m/s', '120 m/s'], 1],

            // ───────────── GRADE 12 — Earth Science / Biology (Field Researcher) ─────────────
            ['12', 'Earth Science', 'easy', 'Ano ang tawag sa mga buhay na bagay na nag-iiwan ng bakas (remains) sa mga bato?',
                ['Minerals', 'Fossils', 'Crystals', 'Sediments'], 1],
            ['12', 'Biology', 'easy', 'Ano ang tawag sa "powerhouse of the cell"?',
                ['Nucleus', 'Ribosome', 'Mitochondria', 'Vacuole'], 2],
            ['12', 'Earth Science', 'easy', 'Aling layer ng Earth ang ating tinitirhan?',
                ['Mantle', 'Outer core', 'Crust', 'Inner core'], 2],
            ['12', 'Earth Science', 'medium', 'Anong uri ng bato ang nabubuo mula sa cooling at solidification ng magma o lava?',
                ['Sedimentary', 'Metamorphic', 'Igneous', 'Fossil'], 2],
            ['12', 'Biology', 'medium', 'Ano ang proseso kung saan ang halaman ay gumagawa ng pagkain gamit ang sunlight?',
                ['Respiration', 'Photosynthesis', 'Digestion', 'Fermentation'], 1],
            ['12', 'Earth Science', 'medium', 'Ano ang tawag sa theory na nagsasabing ang mga continents ay gumagalaw?',
                ['Big Bang Theory', 'Plate Tectonics', 'Evolution', 'Gravity Theory'], 1],
            ['12', 'Biology', 'medium', 'Aling molecule ang nagdadala ng genetic information sa mga buhay na organismo?',
                ['Protein', 'DNA', 'Lipid', 'Carbohydrate'], 1],
            ['12', 'Earth Science', 'hard', 'Anong scale ang ginagamit upang sukatin ang lakas (magnitude) ng lindol?',
                ['Beaufort Scale', 'Richter Scale', 'pH Scale', 'Kelvin Scale'], 1],
            ['12', 'Biology', 'hard', 'Ilang chromosomes mayroon ang normal na human body cell?',
                ['23', '46', '48', '24'], 1],
            ['12', 'Earth Science', 'hard', 'Anong layer ng atmosphere ang naglalaman ng ozone layer na sumasala sa UV rays?',
                ['Troposphere', 'Stratosphere', 'Mesosphere', 'Thermosphere'], 1],

            // ───────────── SPACE / ASTRONOMY (para sa solar-system theme) ─────────────
            // Grade 11 (space-physics crossover)
            ['11', 'Physics',   'easy',   'Anong puwersa ang humahatak sa mga planeta paikot sa Araw?',
                ['Friction', 'Gravity', 'Magnetism', 'Tension'], 1],
            ['11', 'Astronomy', 'easy',   'Aling planeta ang pinakamalapit sa Araw?',
                ['Venus', 'Mercury', 'Earth', 'Mars'], 1],
            ['11', 'Astronomy', 'medium', 'Aling planeta ang kilala sa malalaking ring system?',
                ['Jupiter', 'Saturn', 'Uranus', 'Neptune'], 1],
            ['11', 'Physics',   'medium', 'Gaano kabilis ang liwanag (approximate)?',
                ['300 km/s', '3,000 km/s', '300,000 km/s', '30 km/s'], 2],
            // Grade 12 (astronomy / earth & space)
            ['12', 'Astronomy', 'easy',   'Ano ang tawag sa pag-ikot ng Earth sa sarili nitong axis?',
                ['Revolution', 'Rotation', 'Orbit', 'Tilt'], 1],
            ['12', 'Astronomy', 'easy',   'Aling planeta ang tinatawag na "Red Planet"?',
                ['Venus', 'Mars', 'Jupiter', 'Mercury'], 1],
            ['12', 'Astronomy', 'easy',   'Ano ang natural na satellite ng Earth?',
                ['Sun', 'Moon', 'Mars', 'Venus'], 1],
            ['12', 'Astronomy', 'medium', 'Ano ang sanhi ng pagkakaroon ng mga season sa Earth?',
                ['Layo sa Araw', 'Tilt ng axis', 'Bilis ng rotation', 'Laki ng Buwan'], 1],
            ['12', 'Astronomy', 'medium', 'Aling planeta ang pinakamalaki sa solar system?',
                ['Saturn', 'Jupiter', 'Neptune', 'Earth'], 1],
            ['12', 'Astronomy', 'hard',   'Ilang planeta ang nasa ating solar system?',
                ['7', '8', '9', '10'], 1],
        ];

        foreach ($questions as $q) {
            GameQuestion::updateOrCreate(
                ['grade_level' => $q[0], 'question' => $q[2 + 1]],
                [
                    'topic'         => $q[1],
                    'difficulty'    => $q[2],
                    'options'       => $q[4],
                    'correct_index' => $q[5],
                ]
            );
        }
    }
}