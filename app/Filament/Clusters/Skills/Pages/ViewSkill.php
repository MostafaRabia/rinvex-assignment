<?php

namespace App\Filament\Clusters\Skills\Pages;

use App\Filament\Clusters\Skills\SkillResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSkill extends ViewRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
