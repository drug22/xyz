<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistTemplate;
use App\Models\Question;

class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Workplace Health & Safety Audit',
                'description' => 'Comprehensive workplace health and safety assessment checklist',
                'prefix_numerotare' => 'HW-WAH',
                'category' => 'Health & Safety',
                'tags' => json_encode(['workplace', 'health', 'safety', 'audit']),
                'is_active' => true,
                'questions' => [
                    'Are all emergency exits clearly marked and unobstructed?',
                    'Is first aid equipment readily available and up to date?',
                    'Are fire extinguishers properly maintained and accessible?',
                    'Is personal protective equipment (PPE) available for all workers?',
                    'Are safety data sheets (SDS) available for all hazardous materials?',
                    'Are work areas well-lit and free from hazards?',
                    'Is electrical equipment properly maintained and safe?',
                    'Are spill kits available in areas where chemicals are used?',
                    'Are employees trained in emergency procedures?',
                    'Is housekeeping maintained to prevent slips and falls?'
                ]
            ],
            [
                'name' => 'Office Safety Checklist',
                'description' => 'Basic office environment safety checklist',
                'prefix_numerotare' => 'HW-OSC',
                'category' => 'Office Safety',
                'tags' => json_encode(['office', 'safety', 'workplace']),
                'is_active' => true,
                'questions' => [
                    'Are walkways clear of obstacles and hazards?',
                    'Are electrical cords in good condition and properly routed?',
                    'Is ergonomic equipment available for computer workstations?',
                    'Are filing cabinets and storage areas secure?',
                    'Is lighting adequate throughout the office?'
                ]
            ],
            [
                'name' => 'Construction Site Safety',
                'description' => 'Comprehensive construction site safety audit',
                'prefix_numerotare' => 'HW-CSS',
                'category' => 'Construction',
                'tags' => json_encode(['construction', 'safety', 'site']),
                'is_active' => true,
                'questions' => [
                    'Are all workers wearing appropriate PPE?',
                    'Are scaffolds properly erected and inspected?',
                    'Is fall protection equipment used where required?',
                    'Are tools and equipment in good working condition?',
                    'Are hazardous materials properly stored and labeled?',
                    'Is the site properly secured from unauthorized access?',
                    'Are emergency procedures posted and communicated?'
                ]
            ]
        ];

        foreach ($templates as $templateData) {
            $questions = $templateData['questions'];
            unset($templateData['questions']);

            $template = ChecklistTemplate::create($templateData);

            foreach ($questions as $index => $questionText) {
                Question::create([
                    'checklist_template_id' => $template->id,
                    'text_intrebare' => $questionText,
                    'requires_comment' => in_array($index, [2, 4, 6]), // Some questions require comments
                    'requires_image' => in_array($index, [0, 3, 5]), // Some questions require images
                    'order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Checklist templates and questions seeded successfully');
    }
}
