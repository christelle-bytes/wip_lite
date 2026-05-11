<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) return;

        $notifications = [
            [
                'type' => 'App\Notifications\TestNotification',
                'data' => [
                    'title' => 'Nouvelle affectation',
                    'message' => 'Vous avez été affecté à la campagne "Vente Hiver".',
                    'url' => '/assignments'
                ],
                'created_at' => now()->subMinutes(10),
            ],
            [
                'type' => 'App\Notifications\TestNotification',
                'data' => [
                    'title' => 'Planning mis à jour',
                    'message' => 'Votre planning pour la semaine prochaine a été modifié.',
                    'url' => '/planning'
                ],
                'created_at' => now()->subHours(2),
            ],
            [
                'type' => 'App\Notifications\TestNotification',
                'data' => [
                    'title' => 'Rappel',
                    'message' => 'N\'oubliez pas de valider vos feuilles d\'heures.',
                    'url' => '/timesheet'
                ],
                'created_at' => now()->subDays(1),
                'read_at' => now()->subHours(5),
            ],
        ];

        foreach ($notifications as $notif) {
            $user->notifications()->create($notif);
        }
    }
}
