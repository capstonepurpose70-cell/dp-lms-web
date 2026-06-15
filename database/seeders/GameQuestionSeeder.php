<?php

namespace Database\Seeders;

use App\Models\GameQuestion;
use Illuminate\Database\Seeder;

class GameQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Replace the curated set cleanly (removes any older/non-English rows).
        GameQuestion::query()->delete();

        $questions = [

            // ───────────── GRADE 11 — Physical Science (Formula Clash) ─────────────
            ['11', 'Chemistry', 'easy', 'What is the smallest unit of an element that still keeps its properties?',
                ['Molecule', 'Atom', 'Compound', 'Ion'], 1],
            ['11', 'Chemistry', 'easy', 'Which subatomic particle carries a negative charge?',
                ['Proton', 'Neutron', 'Electron', 'Nucleus'], 2],
            ['11', 'Physics', 'easy', 'What is the SI unit of force?',
                ['Joule', 'Watt', 'Newton', 'Pascal'], 2],
            ['11', 'Physics', 'medium', 'By Newton\'s Second Law, F = ma. If m = 2 kg and a = 3 m/s², what is F?',
                ['5 N', '6 N', '1.5 N', '8 N'], 1],
            ['11', 'Chemistry', 'medium', 'Which type of bond forms when atoms SHARE electrons?',
                ['Ionic bond', 'Covalent bond', 'Metallic bond', 'Hydrogen bond'], 1],
            ['11', 'Chemistry', 'medium', 'How many protons are in a Carbon atom (atomic number 6)?',
                ['12', '6', '8', '14'], 1],
            ['11', 'Physics', 'medium', 'What is the energy an object has due to its motion?',
                ['Potential energy', 'Kinetic energy', 'Thermal energy', 'Chemical energy'], 1],
            ['11', 'Physics', 'hard', 'A 10 kg object is raised to 5 m. What is its potential energy? (g = 9.8 m/s²)',
                ['49 J', '490 J', '50 J', '98 J'], 1],
            ['11', 'Chemistry', 'hard', 'In 2H₂ + O₂ → 2H₂O, how many water molecules form from 2 hydrogen molecules?',
                ['1', '2', '3', '4'], 1],
            ['11', 'Physics', 'hard', 'A car travels 100 m in 20 s. What is its average speed?',
                ['2 m/s', '5 m/s', '20 m/s', '120 m/s'], 1],

            // ───────────── GRADE 12 — Earth Science / Biology (Field Researcher) ─────────────
            ['12', 'Earth Science', 'easy', 'What do we call the preserved remains or traces of living things in rock?',
                ['Minerals', 'Fossils', 'Crystals', 'Sediments'], 1],
            ['12', 'Biology', 'easy', 'Which organelle is known as the "powerhouse of the cell"?',
                ['Nucleus', 'Ribosome', 'Mitochondria', 'Vacuole'], 2],
            ['12', 'Earth Science', 'easy', 'Which layer of the Earth do we live on?',
                ['Mantle', 'Outer core', 'Crust', 'Inner core'], 2],
            ['12', 'Earth Science', 'medium', 'Which rock type forms from the cooling and solidification of magma or lava?',
                ['Sedimentary', 'Metamorphic', 'Igneous', 'Fossil'], 2],
            ['12', 'Biology', 'medium', 'What is the process by which plants make food using sunlight?',
                ['Respiration', 'Photosynthesis', 'Digestion', 'Fermentation'], 1],
            ['12', 'Earth Science', 'medium', 'Which theory states that the Earth\'s continents move over time?',
                ['Big Bang Theory', 'Plate Tectonics', 'Evolution', 'Gravity Theory'], 1],
            ['12', 'Biology', 'medium', 'Which molecule carries genetic information in living organisms?',
                ['Protein', 'DNA', 'Lipid', 'Carbohydrate'], 1],
            ['12', 'Earth Science', 'hard', 'Which scale is used to measure the magnitude of an earthquake?',
                ['Beaufort Scale', 'Richter Scale', 'pH Scale', 'Kelvin Scale'], 1],
            ['12', 'Biology', 'hard', 'How many chromosomes does a normal human body cell have?',
                ['23', '46', '48', '24'], 1],
            ['12', 'Earth Science', 'hard', 'Which atmospheric layer contains the ozone layer that filters UV rays?',
                ['Troposphere', 'Stratosphere', 'Mesosphere', 'Thermosphere'], 1],

            // ───────────── SPACE / ASTRONOMY crossover ─────────────
            ['11', 'Physics',   'easy',   'Which force pulls the planets in their orbits around the Sun?',
                ['Friction', 'Gravity', 'Magnetism', 'Tension'], 1],
            ['11', 'Astronomy', 'easy',   'Which planet is closest to the Sun?',
                ['Venus', 'Mercury', 'Earth', 'Mars'], 1],
            ['11', 'Astronomy', 'medium', 'Which planet is known for its large ring system?',
                ['Jupiter', 'Saturn', 'Uranus', 'Neptune'], 1],
            ['11', 'Physics',   'medium', 'Approximately how fast does light travel?',
                ['300 km/s', '3,000 km/s', '300,000 km/s', '30 km/s'], 2],
            ['12', 'Astronomy', 'easy',   'What is the spinning of the Earth on its own axis called?',
                ['Revolution', 'Rotation', 'Orbit', 'Tilt'], 1],
            ['12', 'Astronomy', 'easy',   'Which planet is called the "Red Planet"?',
                ['Venus', 'Mars', 'Jupiter', 'Mercury'], 1],
            ['12', 'Astronomy', 'easy',   'What is the natural satellite of the Earth?',
                ['Sun', 'Moon', 'Mars', 'Venus'], 1],
            ['12', 'Astronomy', 'medium', 'What causes the seasons on Earth?',
                ['Distance from the Sun', 'Tilt of the axis', 'Speed of rotation', 'Size of the Moon'], 1],
            ['12', 'Astronomy', 'medium', 'Which is the largest planet in the solar system?',
                ['Saturn', 'Jupiter', 'Neptune', 'Earth'], 1],
            ['12', 'Astronomy', 'hard',   'How many planets are in our solar system?',
                ['7', '8', '9', '10'], 1],
        ];

        foreach ($questions as $q) {
            GameQuestion::create([
                'grade_level'   => $q[0],
                'topic'         => $q[1],
                'difficulty'    => $q[2],
                'question'      => $q[3],
                'options'       => $q[4],
                'correct_index' => $q[5],
            ]);
        }
    }
}