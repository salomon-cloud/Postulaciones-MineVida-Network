<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Jobs\SendDiscordChannelMessageJob;
use App\Jobs\SendDiscordNotificationJob;
use App\Models\Application;
use App\Models\ApplicationCategory;
use App\Models\ApplicationInterview;
use App\Models\Setting;
use App\Models\User;
use App\Support\ApplicationCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::putValue('applications_open', true);
        Setting::putValue('minimum_age', 15);
        Setting::putValue('reapply_cooldown_days', 14);
        Setting::putValue('require_discord_guild', false);
        Setting::putValue('discord_selected_channel_id', '');
        Setting::putValue('discord_selected_role_id', '');
        Setting::putValue('discord_selected_message', '');

        config(['services.lumoryx_bot.embed_icon_url' => null]);
    }

    public function test_guest_cannot_submit_application(): void
    {
        $this->post(route('applications.store'), $this->staffPayload())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_application(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('applications.store'), $this->staffPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'type' => 'staff',
            'status' => ApplicationStatus::Pending->value,
            'minecraft_nick' => 'LumoryxTester',
        ]);

        $this->assertDatabaseHas('application_logs', [
            'action' => 'submitted',
            'new_status' => ApplicationStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('discord_notifications', [
            'type' => 'staff_new_application',
            'status' => 'queued',
        ]);

        Queue::assertPushed(
            SendDiscordNotificationJob::class,
            fn (SendDiscordNotificationJob $job) => data_get($job->message, 'embeds.0.author.icon_url') === 'attachment://minevida-logo.png'
                && data_get($job->message, 'embeds.0.footer.icon_url') === 'attachment://minevida-logo.png',
        );
    }

    public function test_empty_applications_page_does_not_show_fake_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Aun no tienes postulaciones')
            ->assertSee('Sin notificaciones')
            ->assertDontSee('Tu postulacion esta en revision')
            ->assertDontSee('Hace 2 horas')
            ->assertDontSee('Hace 1 dia');
    }

    public function test_authenticated_user_can_open_user_panel_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.notifications'))
            ->assertOk()
            ->assertSee('Notificaciones');

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Perfil');

        $this->actingAs($user)
            ->get(route('user.settings'))
            ->assertOk()
            ->assertSee('Ajustes');
    }

    public function test_user_cannot_view_another_users_application(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $application = $this->applicationFor($owner);

        $this->actingAs($other)
            ->get(route('applications.show', $application))
            ->assertForbidden();
    }

    public function test_normal_user_cannot_access_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_displays_graph_sections(): void
    {
        $admin = User::factory()->admin()->create();
        $application = $this->applicationFor(User::factory()->create());
        $application->update(['status' => ApplicationStatus::Accepted]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Actividad reciente')
            ->assertSee('Estados')
            ->assertSee('Tipos mas solicitados');
    }

    public function test_admin_can_change_status_and_create_log_and_discord_notification(): void
    {
        Queue::fake();
        $admin = User::factory()->admin()->create();
        $application = $this->applicationFor(User::factory()->create());

        $this->actingAs($admin)
            ->patch(route('admin.applications.status', $application), [
                'status' => ApplicationStatus::Accepted->value,
                'admin_response' => 'Bienvenido al equipo.',
                'confirmed' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => ApplicationStatus::Accepted->value,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'admin_id' => $admin->id,
            'action' => 'status_changed',
            'old_status' => ApplicationStatus::Pending->value,
            'new_status' => ApplicationStatus::Accepted->value,
        ]);

        $this->assertDatabaseHas('discord_notifications', [
            'application_id' => $application->id,
            'type' => 'dm_accepted',
            'status' => 'queued',
        ]);

        Queue::assertPushed(SendDiscordNotificationJob::class);
    }

    public function test_admin_can_schedule_and_complete_interview(): void
    {
        $admin = User::factory()->admin()->create();
        $interviewer = User::factory()->admin()->create(['name' => 'Entrevistador']);
        $application = $this->applicationFor(User::factory()->create());
        $scheduledAt = now()->addDay()->format('Y-m-d H:i:s');

        $this->actingAs($admin)
            ->post(route('admin.applications.interviews.store', $application), [
                'scheduled_at' => $scheduledAt,
                'interviewer_id' => $interviewer->id,
                'location' => 'Canal de entrevistas',
                'status' => ApplicationInterview::STATUS_SCHEDULED,
                'notes' => 'Revisar disponibilidad y criterio.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('application_interviews', [
            'application_id' => $application->id,
            'interviewer_id' => $interviewer->id,
            'status' => ApplicationInterview::STATUS_SCHEDULED,
            'location' => 'Canal de entrevistas',
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => ApplicationStatus::Interview->value,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'admin_id' => $admin->id,
            'action' => 'interview_scheduled',
        ]);

        $interview = ApplicationInterview::query()->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.applications.interviews.update', [$application, $interview]), [
                'scheduled_at' => $scheduledAt,
                'interviewer_id' => $interviewer->id,
                'location' => 'Canal de entrevistas',
                'status' => ApplicationInterview::STATUS_COMPLETED,
                'notes' => 'Revisar disponibilidad y criterio.',
                'result_notes' => 'Entrevista completada con buen resultado.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('application_interviews', [
            'id' => $interview->id,
            'status' => ApplicationInterview::STATUS_COMPLETED,
            'result_notes' => 'Entrevista completada con buen resultado.',
        ]);

        $this->assertNotNull($interview->refresh()->completed_at);
        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'admin_id' => $admin->id,
            'action' => 'interview_completed',
        ]);
    }

    public function test_user_cannot_create_duplicate_active_application_of_same_type(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->applicationFor($user);

        $this->actingAs($user)
            ->post(route('applications.store'), $this->staffPayload())
            ->assertSessionHasErrors('type');
    }

    public function test_validation_rejects_invalid_fields(): void
    {
        $user = User::factory()->create();
        $payload = $this->staffPayload([
            'minecraft_nick' => '<script>alert(1)</script>',
            'age' => 'abc',
            'motivation' => 'short',
        ]);

        $this->actingAs($user)
            ->post(route('applications.store'), $payload)
            ->assertSessionHasErrors(['minecraft_nick', 'age', 'motivation']);
    }

    public function test_owner_opening_applications_can_queue_discord_announcement(): void
    {
        Queue::fake();
        Setting::putValue('applications_open', false);
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->patch(route('admin.settings.update'), [
                'applications_open' => '1',
                'minimum_age' => '15',
                'reapply_cooldown_days' => '14',
                'require_discord_guild' => '0',
                'discord_announce_applications_window' => '1',
                'discord_announcement_channel_id' => "123456789012345678\n987654321098765432",
                'discord_announcement_role_id' => '234567890123456789',
                'discord_selected_channel_id' => '',
                'discord_selected_role_id' => '',
                'discord_open_message' => 'Abrimos postulaciones para el equipo.',
                'discord_closed_message' => '',
                'discord_selected_message' => '',
            ])
            ->assertRedirect();

        $this->assertTrue(Setting::bool('applications_open', false));
        $this->assertSame("123456789012345678\n987654321098765432", Setting::value('discord_announcement_channel_id'));

        Queue::assertPushed(
            SendDiscordChannelMessageJob::class,
            fn (SendDiscordChannelMessageJob $job) => $job->channelId === '123456789012345678'
                && $job->content === '<@&234567890123456789>'
                && ($job->message['embeds'][0]['title'] ?? null) === 'POSTULACIONES ABIERTAS',
        );
        Queue::assertPushed(
            SendDiscordChannelMessageJob::class,
            fn (SendDiscordChannelMessageJob $job) => $job->channelId === '987654321098765432'
                && $job->content === '<@&234567890123456789>'
                && ($job->message['embeds'][0]['title'] ?? null) === 'POSTULACIONES ABIERTAS',
        );
        Queue::assertPushed(SendDiscordChannelMessageJob::class, 2);
    }

    public function test_admin_can_publish_selected_applicants_announcement(): void
    {
        Queue::fake();
        Setting::putValue('discord_selected_channel_id', "345678901234567890\n567890123456789012");
        Setting::putValue('discord_selected_role_id', '456789012345678901');

        $admin = User::factory()->admin()->create();
        $application = $this->applicationFor(User::factory()->create());
        $application->update([
            'status' => ApplicationStatus::Accepted,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.selected.publish'), [
                'applications' => [$application->id],
            ])
            ->assertRedirect();

        $this->assertNotNull($application->refresh()->selected_announced_at);
        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'admin_id' => $admin->id,
            'action' => 'selected_announced',
        ]);

        Queue::assertPushed(
            SendDiscordChannelMessageJob::class,
            fn (SendDiscordChannelMessageJob $job) => $job->channelId === '345678901234567890'
                && $job->content === '<@&456789012345678901>'
                && ($job->message['embeds'][0]['title'] ?? null) === 'NUEVOS SELECCIONADOS',
        );
        Queue::assertPushed(
            SendDiscordChannelMessageJob::class,
            fn (SendDiscordChannelMessageJob $job) => $job->channelId === '567890123456789012'
                && $job->content === '<@&456789012345678901>'
                && ($job->message['embeds'][0]['title'] ?? null) === 'NUEVOS SELECCIONADOS',
        );
        Queue::assertPushed(SendDiscordChannelMessageJob::class, 2);
    }

    public function test_admin_can_create_dynamic_application_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Soporte',
                'slug' => 'soporte',
                'summary' => 'Atencion a usuarios y apoyo en tickets.',
                'description' => 'Categoria para soporte de la comunidad.',
                'icon' => 'SP',
                'accent_color' => '#facc15',
                'minimum_age' => '16',
                'sort_order' => '55',
                'is_open' => '1',
            ])
            ->assertRedirect();

        $category = ApplicationCategory::query()->where('slug', 'soporte')->firstOrFail();

        $this->assertTrue($category->is_open);
        $this->assertDatabaseHas('application_questions', [
            'category_id' => $category->id,
            'key' => 'minecraft_nick',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Constructor de formulario')
            ->assertSee('Informacion principal');

        $this->actingAs($admin)
            ->get(route('admin.categories.edit', ['category' => $category, 'tab' => 'questions']))
            ->assertOk()
            ->assertSee('Preguntas por fase')
            ->assertSee('Agregar pregunta');

        $this->actingAs($admin)
            ->patch(route('admin.categories.update', $category), [
                'name' => 'Soporte',
                'slug' => 'soporte',
                'summary' => 'Atencion a usuarios y apoyo en tickets.',
                'description' => 'Categoria para soporte de la comunidad.',
                'icon' => 'SP',
                'accent_color' => '#facc15',
                'minimum_age' => '16',
                'sort_order' => '55',
                'is_open' => '1',
                'steps' => [
                    ['title' => 'Datos generales', 'description' => 'Informacion basica del postulante.'],
                    ['title' => 'Preguntas generales', 'description' => 'Disponibilidad y experiencia general.'],
                    ['title' => 'Tickets', 'description' => 'Casos de soporte.'],
                    ['title' => 'Preguntas del staff', 'description' => 'Situaciones del equipo.'],
                    ['title' => 'Enviar', 'description' => 'Revision final.'],
                ],
            ])
            ->assertRedirect();

        $this->assertCount(5, $category->refresh()->steps);

        $this->actingAs($admin)
            ->post(route('admin.categories.questions.store', $category), [
                'key' => 'ticket_example',
                'label' => 'Como resolverias un ticket dificil?',
                'input_type' => 'textarea',
                'step' => '4',
                'sort_order' => '60',
                'is_required' => '1',
                'is_answer' => '1',
                'rules_text' => "required\nstring\nmin:20\nmax:1000",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('application_questions', [
            'category_id' => $category->id,
            'key' => 'ticket_example',
            'label' => 'Como resolverias un ticket dificil?',
            'step' => 4,
        ]);
    }

    public function test_admin_can_close_reopen_and_user_sees_closed_category_message(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $category = ApplicationCategory::query()->create([
            'name' => 'Soporte DC',
            'slug' => 'soporte-dc',
            'summary' => 'Soporte para Discord.',
            'description' => null,
            'icon' => 'DC',
            'accent_color' => '#facc15',
            'minimum_age' => 15,
            'is_open' => true,
            'sort_order' => 50,
            'steps' => ApplicationCatalog::defaultSteps(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.categories.availability', $category), [
                'is_open' => '0',
                'closed_until' => now()->addDays(7)->format('Y-m-d H:i'),
                'closed_message' => 'Cerrada por mantenimiento del equipo.',
            ])
            ->assertRedirect();

        $this->assertFalse($category->refresh()->is_open);

        $this->actingAs($user)
            ->get(route('applications.create'))
            ->assertOk()
            ->assertSee('Soporte DC')
            ->assertSee('No disponible por ahora')
            ->assertSee('Cerrada por mantenimiento del equipo.');

        $this->actingAs($user)
            ->get(route('applications.create.type', $category->slug))
            ->assertOk()
            ->assertSee('Soporte DC esta cerrada')
            ->assertSee('Cerrada por mantenimiento del equipo.');

        $this->actingAs($admin)
            ->patch(route('admin.categories.availability', $category), [
                'is_open' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue($category->refresh()->is_open);
        $this->assertNull($category->closed_until);
        $this->assertNull($category->closed_message);
    }

    public function test_admin_can_archive_and_restore_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ApplicationCategory::query()->create([
            'name' => 'Eventos',
            'slug' => 'eventos',
            'summary' => 'Apoyo en eventos.',
            'description' => null,
            'icon' => 'EV',
            'accent_color' => '#facc15',
            'minimum_age' => 15,
            'is_open' => true,
            'sort_order' => 60,
            'steps' => ApplicationCatalog::defaultSteps(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted('application_categories', ['id' => $category->id]);

        $this->actingAs($admin)
            ->patch(route('admin.categories.restore', $category->id))
            ->assertRedirect();

        $this->assertDatabaseHas('application_categories', [
            'id' => $category->id,
            'deleted_at' => null,
            'is_open' => true,
        ]);
    }

    private function applicationFor(User $user): Application
    {
        return Application::query()->create([
            'user_id' => $user->id,
            'type' => 'staff',
            'status' => ApplicationStatus::Pending,
            'minecraft_nick' => 'OtherTester',
            'age' => 18,
            'country' => 'Mexico',
            'timezone' => 'America/Mexico_City',
            'available_schedule' => 'Tardes entre semana.',
        ]);
    }

    private function staffPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'staff',
            'minecraft_nick' => 'LumoryxTester',
            'age' => 18,
            'country' => 'Mexico',
            'timezone' => 'America/Mexico_City',
            'available_schedule' => 'Puedo estar disponible por las tardes y fines de semana.',
            'staff_experience' => 'He moderado comunidades de Minecraft con reportes, sanciones y soporte.',
            'staff_servers' => 'Servidor Uno, Servidor Dos y comunidades privadas.',
            'hacks_response' => 'Revisaria evidencia, observaria el comportamiento, aplicaria el protocolo y registraria la sancion.',
            'insult_response' => 'Mantendria la calma, pediria respeto, documentaria el caso y escalaria si continua.',
            'motivation' => 'Quiero ayudar a mantener una comunidad ordenada, justa y agradable para los jugadores.',
            'contribution' => 'Puedo aportar criterio, disponibilidad, comunicacion clara y seguimiento de reportes.',
            'accept_rules' => '1',
        ], $overrides);
    }
}
