<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentLeave;
use App\Models\User;
use App\Notifications\LeaveEndingNotification;

class CheckLeavesEnding extends Command
{
    protected $signature = 'sigef:check-leaves-ending {--days=3 : Dias de antecedência para alerta}';
    protected $description = 'Verifica dispensas que estão a terminar e envia notificações';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days);

        $this->info("A verificar dispensas que terminam até {$targetDate->format('d/m/Y')}...");

        $leaves = StudentLeave::with(['student.candidate', 'institution'])
            ->where('status', 'approved')
            ->whereBetween('end_date', [now(), $targetDate])
            ->get();

        if ($leaves->isEmpty()) {
            $this->info('Nenhuma dispensa a terminar nos próximos ' . $days . ' dias.');
            return Command::SUCCESS;
        }

        $this->info("Encontradas {$leaves->count()} dispensas a terminar.");

        foreach ($leaves as $leave) {
            // Notificar utilizadores da instituição
            $users = User::where('institution_id', $leave->institution_id)
                ->where('is_active', true)
                ->get();

            foreach ($users as $user) {
                $user->notify(new LeaveEndingNotification($leave));
            }

            // Também notificar administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new LeaveEndingNotification($leave));
            }

            $this->line(" - {$leave->student->candidate->full_name}: termina em {$leave->end_date->format('d/m/Y')}");
        }

        $this->info('✅ Notificações enviadas com sucesso!');
        return Command::SUCCESS;
    }
}
