<?php

namespace App\Support;

use App\Models\GroupActivity;

class GroupActivityWriter
{
    /**
     * @param  list<string>  $userIds
     * @param  array{category: string, group_id: string, group_name: string, title: string, description?: ?string, actor: string, icon?: ?string, href?: ?string}  $activity
     */
    public static function record(array $userIds, array $activity): void
    {
        $timeLabel = now()->locale('fr')->translatedFormat('d F · H:i');

        foreach (array_unique($userIds) as $userId) {
            GroupActivity::query()->create([
                ...$activity,
                'user_id' => $userId,
                'time_label' => $timeLabel,
            ]);
        }
    }
}
