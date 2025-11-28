<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalSkills = Skill::count();
        $averageProficiency = Skill::avg('proficiency_level');
        $totalActiveSkills = Skill::where('is_active', true)->count();

        return [
            Stat::make('Total skills', $totalSkills)
                ->description('Number of skills in the system'),
            Stat::make('Average proficiency', number_format($averageProficiency, 2))
                ->description('Average proficiency level across all skills'),
            Stat::make('Active skills', $totalActiveSkills)
                ->description('Number of active skills'),
        ];
    }
}
