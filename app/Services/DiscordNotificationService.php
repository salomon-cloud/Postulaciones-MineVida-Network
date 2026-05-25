<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Jobs\SendDiscordChannelMessageJob;
use App\Jobs\SendDiscordNotificationJob;
use App\Models\Application;
use App\Models\DiscordNotification;
use App\Models\Setting;
use App\Support\ApplicationCatalog;
use Illuminate\Support\Collection;

class DiscordNotificationService
{
    public function __construct(private DiscordSystemLogService $systemLogs)
    {
    }

    public function queueStaffApplication(Application $application): void
    {
        $staffNotification = DiscordNotification::query()->create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'discord_id' => (string) (config('services.lumoryx_bot.staff_channel_id') ?: 'staff-channel'),
            'type' => 'staff_new_application',
            'status' => 'queued',
        ]);

        SendDiscordNotificationJob::dispatch($staffNotification->id, $this->staffMessage($application));

        $this->queueStatusDm($application);
    }

    public function queueStatusDm(Application $application): void
    {
        if (! in_array($application->status, [
            ApplicationStatus::Pending,
            ApplicationStatus::InReview,
            ApplicationStatus::Interview,
            ApplicationStatus::Accepted,
            ApplicationStatus::Rejected,
        ], true)) {
            return;
        }

        $type = 'dm_'.$application->status->value;

        $notification = DiscordNotification::query()->create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'discord_id' => (string) $application->user->discord_id,
            'type' => $type,
            'status' => 'queued',
        ]);

        SendDiscordNotificationJob::dispatch(
            $notification->id,
            '',
            $this->statusDmMessage($application),
        );

        $this->systemLogs->logApplicationEvent(
            'discord',
            'DM de estado encolado',
            'Se encolo un mensaje privado de Discord para informar el estado de una postulacion.',
            $application,
            'discord',
            extraFields: ['Tipo' => $type],
        );
    }

    public function queueApplicationsWindowAnnouncement(bool $isOpen): void
    {
        if (! Setting::bool('discord_announce_applications_window', false)) {
            return;
        }

        $channelIds = $this->channelIds('discord_announcement_channel_id');

        if ($channelIds === []) {
            return;
        }

        $roleId = trim((string) Setting::value('discord_announcement_role_id', ''));
        $content = $roleId !== '' ? '<@&'.$roleId.'>' : '';

        foreach ($channelIds as $channelId) {
            SendDiscordChannelMessageJob::dispatch(
                $channelId,
                $content,
                $this->applicationsWindowAnnouncementMessage($isOpen),
            );
        }

        $this->systemLogs->queue(
            'discord',
            $isOpen ? 'Anuncio de apertura encolado' : 'Anuncio de cierre encolado',
            'Se encolo un anuncio automatico para canales de Discord.',
            [
                'Canales' => implode("\n", $channelIds),
                'Mencion' => $roleId !== '' ? '<@&'.$roleId.'>' : 'Sin mencion',
            ],
            $isOpen ? 'success' : 'danger',
        );
    }

    public function queueSelectedApplicantsAnnouncement(iterable $applications): bool
    {
        $applications = collect($applications)->values();

        if ($applications->isEmpty()) {
            return false;
        }

        $channelIds = $this->channelIds('discord_selected_channel_id');

        if ($channelIds === []) {
            $channelIds = $this->channelIds('discord_announcement_channel_id');
        }

        if ($channelIds === []) {
            return false;
        }

        $roleId = trim((string) Setting::value('discord_selected_role_id', ''));
        $content = $roleId !== '' ? '<@&'.$roleId.'>' : '';

        foreach ($channelIds as $channelId) {
            SendDiscordChannelMessageJob::dispatch(
                $channelId,
                $content,
                $this->selectedApplicantsAnnouncementMessage($applications),
            );
        }

        $this->systemLogs->queue(
            'discord',
            'Anuncio de seleccionados encolado',
            'Se encolo el anuncio de seleccionados para Discord.',
            [
                'Canales' => implode("\n", $channelIds),
                'Seleccionados' => $applications->count(),
                'Mencion' => $roleId !== '' ? '<@&'.$roleId.'>' : 'Sin mencion',
            ],
            'success',
        );

        return true;
    }

    private function channelIds(string $settingKey): array
    {
        $raw = trim((string) Setting::value($settingKey, ''));

        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/[\s,;]+/', $raw) ?: [])
            ->map(fn (string $channelId) => preg_replace('/\D+/', '', $channelId))
            ->filter(fn (?string $channelId) => $channelId !== null && preg_match('/^\d{16,25}$/', $channelId))
            ->unique()
            ->values()
            ->all();
    }

    private function staffMessage(Application $application): string
    {
        $adminUrl = route('admin.applications.show', $application, absolute: true);
        $networkName = config('app.name', 'MineVida Network');

        return implode("\n", [
            '**Nueva postulacion en '.$networkName.'**',
            'Usuario Discord: '.$application->user->discord_username.' ('.$application->user->discord_id.')',
            'Nick Minecraft: '.$application->minecraft_nick,
            'Tipo: '.$application->typeLabel(),
            'Fecha: '.$application->created_at->format('Y-m-d H:i'),
            'Panel: '.$adminUrl,
        ]);
    }

    private function statusDmMessage(Application $application): array
    {
        $statusData = $this->statusData($application->status);
        $networkName = config('app.name', 'MineVida Network');
        $botIcon = $this->embedIconUrl();
        $applicationUrl = route('applications.show', $application, absolute: true);
        $avatarUrl = $application->user->discordAvatarUrl();

        $embed = [
            'color' => $statusData['color'],
            'author' => [
                'name' => $networkName.' - Sistema de Postulaciones',
                'icon_url' => $botIcon,
            ],
            'title' => $statusData['title'],
            'description' => $statusData['text'],
            'fields' => [
                [
                    'name' => 'Estado actual',
                    'value' => '> `'.$statusData['label'].'`',
                    'inline' => true,
                ],
                [
                    'name' => 'Tipo de solicitud',
                    'value' => '> `Postulacion '.$application->typeLabel().'`',
                    'inline' => true,
                ],
                [
                    'name' => 'Proceso',
                    'value' => $statusData['steps'],
                    'inline' => false,
                ],
                [
                    'name' => 'Recomendacion',
                    'value' => implode("\n", [
                        '> Mantente atento a tus mensajes privados.',
                        '> Evita insistir constantemente por el resultado.',
                        '> Respeta los tiempos de revision del equipo.',
                    ]),
                    'inline' => false,
                ],
            ],
            'image' => [
                'url' => $statusData['image'],
            ],
            'footer' => [
                'text' => $networkName.' - Sistema oficial',
                'icon_url' => $botIcon,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        if ($avatarUrl) {
            $embed['thumbnail'] = ['url' => $avatarUrl];
        }

        if ($application->admin_response) {
            $embed['fields'][] = [
                'name' => 'Mensaje del equipo',
                'value' => str($application->admin_response)->limit(900)->toString(),
                'inline' => false,
            ];
        }

        return [
            'embeds' => [$embed],
            'components' => [[
                'type' => 1,
                'components' => [[
                    'type' => 2,
                    'style' => 5,
                    'label' => 'Revisar postulacion',
                    'url' => $applicationUrl,
                ]],
            ]],
        ];
    }

    private function applicationsWindowAnnouncementMessage(bool $isOpen): array
    {
        $networkName = config('app.name', 'MineVida Network');
        $applicationUrl = route('applications.create', absolute: true);
        $botIcon = $this->embedIconUrl();
        $minimumAge = Setting::integer('minimum_age', 15);
        $customMessage = trim((string) Setting::value(
            $isOpen ? 'discord_open_message' : 'discord_closed_message',
            '',
        ));

        $defaultDescription = $isOpen
            ? $networkName.' ha abierto postulaciones para nuevos miembros del equipo. Si quieres formar parte del staff, revisa los requisitos y completa tu solicitud desde el sistema oficial.'
            : 'Las postulaciones de '.$networkName.' han sido cerradas por ahora. Las solicitudes enviadas seguiran siendo revisadas por el equipo.';

        $embed = [
            'color' => $isOpen ? 0xfacc15 : 0xef4444,
            'author' => [
                'name' => $networkName.' - Sistema de Postulaciones',
                'icon_url' => $botIcon,
            ],
            'title' => $isOpen ? 'POSTULACIONES ABIERTAS' : 'POSTULACIONES CERRADAS',
            'description' => $customMessage !== '' ? $customMessage : $defaultDescription,
            'fields' => [
                [
                    'name' => 'Estado',
                    'value' => $isOpen ? '> `Abiertas`' : '> `Cerradas`',
                    'inline' => true,
                ],
                [
                    'name' => 'Edad minima',
                    'value' => '> `'.$minimumAge.' anos`',
                    'inline' => true,
                ],
                [
                    'name' => 'Areas disponibles',
                    'value' => collect(ApplicationCatalog::types())
                        ->values()
                        ->map(fn (string $label) => '> '.$label)
                        ->implode("\n"),
                    'inline' => false,
                ],
                [
                    'name' => $isOpen ? 'Antes de postularte' : 'Que pasa con las solicitudes enviadas',
                    'value' => $isOpen
                        ? implode("\n", [
                            '> Lee las reglas del servidor.',
                            '> Responde con calma y de forma clara.',
                            '> Mantente atento a tus mensajes privados de Discord.',
                        ])
                        : implode("\n", [
                            '> El equipo seguira revisando las postulaciones existentes.',
                            '> Las nuevas solicitudes quedan pausadas hasta la proxima apertura.',
                            '> Mantente atento a futuros anuncios.',
                        ]),
                    'inline' => false,
                ],
            ],
            'footer' => [
                'text' => $networkName.' - Sistema oficial',
                'icon_url' => $botIcon,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $message = ['embeds' => [$embed]];

        if ($isOpen) {
            $message['components'] = [[
                'type' => 1,
                'components' => [[
                    'type' => 2,
                    'style' => 5,
                    'label' => 'Enviar postulacion',
                    'url' => $applicationUrl,
                ]],
            ]];
        }

        return $message;
    }

    private function selectedApplicantsAnnouncementMessage(Collection $applications): array
    {
        $networkName = config('app.name', 'MineVida Network');
        $botIcon = $this->embedIconUrl();
        $customMessage = trim((string) Setting::value('discord_selected_message', ''));
        $description = $customMessage !== ''
            ? $customMessage
            : 'El equipo de '.$networkName.' felicita a las personas que fueron seleccionadas para continuar como parte del equipo.';

        $fields = $applications
            ->groupBy(fn (Application $application) => $application->typeLabel())
            ->map(function (Collection $group, string $label) {
                $lines = $group
                    ->sortBy('minecraft_nick')
                    ->take(20)
                    ->map(function (Application $application) {
                        $discordMention = $application->user?->discord_id
                            ? ' - <@'.$application->user->discord_id.'>'
                            : ($application->user?->discord_username ? ' - '.$application->user->discord_username : '');
                        return '> **'.$application->minecraft_nick.'**'.$discordMention;
                    });

                if ($group->count() > 20) {
                    $lines->push('> Y '.($group->count() - 20).' mas.');
                }

                return [
                    'name' => $label,
                    'value' => $lines->implode("\n"),
                    'inline' => false,
                ];
            })
            ->values()
            ->all();

        $fields[] = [
            'name' => 'Siguiente paso',
            'value' => implode("\n", [
                '> Mantente atento a los mensajes del equipo.',
                '> Revisa las indicaciones que recibas por Discord.',
                '> Actua con respeto y compromiso desde este momento.',
            ]),
            'inline' => false,
        ];

        return [
            'embeds' => [[
                'color' => 0x22c55e,
                'author' => [
                    'name' => $networkName.' - Sistema de Postulaciones',
                    'icon_url' => $botIcon,
                ],
                'title' => 'NUEVOS SELECCIONADOS',
                'description' => $description,
                'fields' => $fields,
                'footer' => [
                    'text' => $networkName.' - Sistema oficial',
                    'icon_url' => $botIcon,
                ],
                'timestamp' => now()->toIso8601String(),
            ]],
        ];
    }

    private function embedIconUrl(): string
    {
        $configuredUrl = trim((string) config('services.lumoryx_bot.embed_icon_url', ''));

        if ($configuredUrl !== '') {
            return $configuredUrl;
        }

        return 'attachment://minevida-logo.png';
    }

    private function statusData(ApplicationStatus $status): array
    {
        $networkName = config('app.name', 'MineVida Network');

        return match ($status) {
            ApplicationStatus::Pending => [
                'color' => 0xfacc15,
                'title' => 'POSTULACION RECIBIDA',
                'label' => 'Pendiente',
                'image' => 'https://media.discordapp.net/attachments/1474622121228632178/1506471581780807680/postulaciones.png?ex=6a0e627d&is=6a0d10fd&hm=c4bccc8c7c5aecf4780cfb779d9e910a6b58a75fbb7b0a4d5677b432dc1e45c7&=&format=webp&quality=lossless&width=939&height=939',
                'text' => implode("\n", [
                    '**Tu postulacion fue enviada correctamente.**',
                    '',
                    'Gracias por querer formar parte del equipo de **'.$networkName.'**. Hemos recibido tu solicitud y sera revisada por el equipo encargado.',
                    '',
                    'Durante este proceso evaluaremos tus respuestas, disponibilidad, experiencia y actitud dentro de la comunidad.',
                    '',
                    '> No es necesario enviar mensajes preguntando por el resultado. Cuando exista una actualizacion, te avisaremos por este mismo medio.',
                ]),
                'steps' => implode("\n", [
                    '> Solicitud recibida correctamente.',
                    '> Revision inicial por parte del staff.',
                    '> Evaluacion de respuestas y perfil.',
                    '> Notificacion del siguiente estado.',
                ]),
            ],
            ApplicationStatus::InReview => [
                'color' => 0xeab308,
                'title' => 'POSTULACION EN REVISION',
                'label' => 'En revision',
                'image' => 'https://media.discordapp.net/attachments/1474622121228632178/1506471581780807680/postulaciones.png?ex=6a0e627d&is=6a0d10fd&hm=c4bccc8c7c5aecf4780cfb779d9e910a6b58a75fbb7b0a4d5677b432dc1e45c7&=&format=webp&quality=lossless&width=939&height=939',
                'text' => implode("\n", [
                    '**Tu postulacion paso a revision del equipo.**',
                    '',
                    'El equipo de **'.$networkName.'** ya esta evaluando tus respuestas y tu perfil dentro de la comunidad.',
                    '',
                    'Este paso puede tardar un poco dependiendo de la cantidad de solicitudes activas.',
                    '',
                    '> Mantente atento. Si hay novedades importantes, te avisaremos por este mismo medio.',
                ]),
                'steps' => implode("\n", [
                    '> Solicitud recibida correctamente.',
                    '> Revision inicial en curso.',
                    '> Evaluacion de respuestas y perfil.',
                    '> Notificacion del siguiente estado.',
                ]),
            ],
            ApplicationStatus::Interview => [
                'color' => 0x38bdf8,
                'title' => 'SIGUIENTE ETAPA: ENTREVISTA',
                'label' => 'Entrevista',
                'image' => 'https://media.discordapp.net/attachments/1474622121228632178/1506471581780807680/postulaciones.png?ex=6a0e627d&is=6a0d10fd&hm=c4bccc8c7c5aecf4780cfb779d9e910a6b58a75fbb7b0a4d5677b432dc1e45c7&=&format=webp&quality=lossless&width=939&height=939',
                'text' => implode("\n", [
                    '**Tu postulacion avanzo a la etapa de entrevista.**',
                    '',
                    'Despues de revisar tu solicitud, el equipo de **'.$networkName.'** considera que puedes continuar con el proceso.',
                    '',
                    'Un administrador o encargado se pondra en contacto contigo para coordinar la entrevista y resolver algunas preguntas adicionales.',
                    '',
                    '> Manten tus mensajes privados abiertos y responde con calma cuando el staff te contacte.',
                ]),
                'steps' => implode("\n", [
                    '> Revision inicial completada.',
                    '> Tu perfil fue seleccionado para entrevista.',
                    '> Se coordinara una conversacion con el staff.',
                    '> Despues de la entrevista se dara una resolucion final.',
                ]),
            ],
            ApplicationStatus::Accepted => [
                'color' => 0x22c55e,
                'title' => 'POSTULACION ACEPTADA',
                'label' => 'Aceptada',
                'image' => 'https://media.discordapp.net/attachments/1474622121228632178/1506471581780807680/postulaciones.png?ex=6a0e627d&is=6a0d10fd&hm=c4bccc8c7c5aecf4780cfb779d9e910a6b58a75fbb7b0a4d5677b432dc1e45c7&=&format=webp&quality=lossless&width=939&height=939',
                'text' => implode("\n", [
                    '**Felicidades. Tu postulacion fue aceptada.**',
                    '',
                    'El equipo de **'.$networkName.'** reviso tu solicitud y decidio darte la oportunidad de integrarte al equipo.',
                    '',
                    'Pronto un administrador se pondra en contacto contigo para explicarte los siguientes pasos, indicarte tus responsabilidades y ayudarte con tu integracion.',
                    '',
                    '> Recuerda actuar con madurez, respeto y compromiso desde este momento.',
                ]),
                'steps' => implode("\n", [
                    '> Postulacion aprobada por el equipo.',
                    '> Contacto por parte de un administrador.',
                    '> Asignacion o explicacion del rol correspondiente.',
                    '> Inicio del proceso de integracion al staff.',
                ]),
            ],
            ApplicationStatus::Rejected => [
                'color' => 0xef4444,
                'title' => 'POSTULACION NO ACEPTADA',
                'label' => 'Rechazada',
                'image' => 'https://media.discordapp.net/attachments/1474622121228632178/1506471581780807680/postulaciones.png?ex=6a0e627d&is=6a0d10fd&hm=c4bccc8c7c5aecf4780cfb779d9e910a6b58a75fbb7b0a4d5677b432dc1e45c7&=&format=webp&quality=lossless&width=939&height=939',
                'text' => implode("\n", [
                    '**Gracias por tu interes en formar parte del equipo.**',
                    '',
                    'Despues de revisar tu solicitud, el equipo de **'.$networkName.'** decidio no continuar con tu postulacion en esta ocasion.',
                    '',
                    'Esto no significa que no puedas volver a intentarlo mas adelante. Puedes seguir participando en la comunidad, mejorar tu actividad y postularte nuevamente cuando sea posible.',
                    '',
                    '> Agradecemos el tiempo que dedicaste a completar tu solicitud.',
                ]),
                'steps' => implode("\n", [
                    '> Solicitud revisada por el equipo.',
                    '> Resolucion tomada por administracion.',
                    '> Podras intentarlo nuevamente en futuras convocatorias.',
                    '> Sigue participando de forma positiva en la comunidad.',
                ]),
            ],
            default => [
                'color' => 0x94a3b8,
                'title' => 'POSTULACION ACTUALIZADA',
                'label' => $status->label(),
                'image' => 'https://dummyimage.com/900x220/111827/94a3b8&text=POSTULACION+ACTUALIZADA',
                'text' => 'Tu postulacion tuvo una actualizacion.',
                'steps' => '> Revisa el panel para ver mas detalles.',
            ],
        };
    }
}
