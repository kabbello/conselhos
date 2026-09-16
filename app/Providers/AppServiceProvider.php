<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Lab404\Impersonate\Events\TakeImpersonation;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Illuminate\Support\Facades\Event;
use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Observers\ComposicaoObserver;
use Modules\Reunioes\Models\Reuniao;
use Modules\Reunioes\Observers\ReuniaoObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Regra: PRESIDENTE ativo = gestor natural → formaliza vínculo em user_conselho_gestores
        Composicao::observe(ComposicaoObserver::class);

        // Notificações automáticas ao criar/alterar reunião
        Reuniao::observe(ReuniaoObserver::class);

        // P0.4 — Observer de e-mail automático suspenso.
        // O envio síncrono ao criar reunião foi desativado enquanto não existe fluxo
        // formal de convocação, fila transacional e idempotência.
        // Quando o fluxo for implementado, reativar via evento ReuniaoConvocada + Job.
        // \Modules\Reunioes\Models\Reuniao::observe(\Modules\Reunioes\Observers\ReuniaoObserver::class);

        // A09 — Trilha de auditoria para impersonation
        Event::listen(TakeImpersonation::class, function (TakeImpersonation $event) {
            activity('impersonation')
                ->causedBy($event->impersonator)
                ->withProperties([
                    'impersonated_id'    => $event->impersonated->id,
                    'impersonated_email' => $event->impersonated->email,
                    'impersonated_name'  => $event->impersonated->name,
                    'impersonator_email' => $event->impersonator->email,
                ])
                ->log("Iniciou impersonation de {$event->impersonated->email}");
        });

        Event::listen(LeaveImpersonation::class, function (LeaveImpersonation $event) {
            activity('impersonation')
                ->causedBy($event->impersonator)
                ->withProperties([
                    'impersonated_id'    => $event->impersonated->id,
                    'impersonated_email' => $event->impersonated->email,
                    'impersonator_email' => $event->impersonator->email,
                ])
                ->log("Encerrou impersonation de {$event->impersonated->email}");
        });

        // Resolve factories para models em módulos (Modules\*\Models\Foo → Database\Factories\FooFactory)
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            if (str_starts_with($modelName, 'Modules\\')) {
                return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
            }

            // Comportamento padrão do Laravel para App\Models\*
            $appNamespace = 'App\\';
            $modelName = str_starts_with($modelName, $appNamespace . 'Models\\')
                ? substr($modelName, strlen($appNamespace . 'Models\\'))
                : substr($modelName, strlen($appNamespace));

            return 'Database\\Factories\\' . $modelName . 'Factory';
        });
    }
}
