<?php

namespace App\Filament\Clusters\Settings;

use App\Filament\Clusters\Settings\Pages\CreateSkill;
use App\Filament\Clusters\Settings\Pages\EditSkill;
use App\Filament\Clusters\Settings\Pages\ListSkills;
use App\Filament\Clusters\Settings\Pages\ViewSkill;
use App\Filament\Clusters\Settings\Schemas\SkillForm;
use App\Filament\Clusters\Settings\Schemas\SkillInfolist;
use App\Filament\Clusters\Settings\Tables\SkillsTable;
use App\Models\Skill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SkillForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SkillInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkills::route('/'),
            'create' => CreateSkill::route('/create'),
            'view' => ViewSkill::route('/{record}'),
            'edit' => EditSkill::route('/{record}/edit'),
        ];
    }
}
