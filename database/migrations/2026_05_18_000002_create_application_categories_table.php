<?php

use App\Support\ApplicationCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 8)->nullable();
            $table->string('accent_color', 24)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->boolean('is_open')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->json('steps')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('application_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('application_categories')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('input_type', 32)->default('text');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->json('options')->nullable();
            $table->json('rules')->nullable();
            $table->unsignedTinyInteger('step')->default(1)->index();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_answer')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['category_id', 'key']);
        });

        $now = now();

        foreach (ApplicationCatalog::defaultDefinitions() as $slug => $definition) {
            $categoryId = DB::table('application_categories')->insertGetId([
                'name' => $definition['label'],
                'slug' => $slug,
                'summary' => $definition['summary'],
                'description' => $definition['description'] ?? null,
                'icon' => $definition['icon'] ?? null,
                'accent_color' => $definition['accent_color'] ?? null,
                'minimum_age' => $definition['minimum_age'] ?? null,
                'is_open' => true,
                'sort_order' => $definition['sort_order'] ?? 0,
                'steps' => json_encode($definition['steps']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($definition['fields'] as $key => $field) {
                DB::table('application_questions')->insert([
                    'category_id' => $categoryId,
                    'key' => $key,
                    'label' => $field['label'],
                    'input_type' => $field['type'] ?? 'text',
                    'placeholder' => $field['placeholder'] ?? null,
                    'help_text' => $field['help_text'] ?? null,
                    'options' => isset($field['options']) ? json_encode($field['options']) : null,
                    'rules' => json_encode($field['rules'] ?? []),
                    'step' => $field['step'] ?? 1,
                    'is_required' => $field['required'] ?? true,
                    'is_answer' => $field['is_answer'] ?? ! in_array($key, ApplicationCatalog::columnFields(), true),
                    'sort_order' => $field['sort_order'] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_questions');
        Schema::dropIfExists('application_categories');
    }
};
