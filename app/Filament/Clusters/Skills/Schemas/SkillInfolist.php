<?php

namespace App\Filament\Clusters\Skills\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SkillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Main info')->schema([
                    TextEntry::make('name'),
                    TextEntry::make('category'),
                    TextEntry::make('proficiency_level')
                        ->visible(fn ($record) => strtolower($record->category) === 'technical')
                        ->numeric(),
                    IconEntry::make('is_active')
                        ->boolean(),
                    TextEntry::make('tags')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('notes')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpan(['lg' => fn ($record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created at')
                            ->state(fn ($record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn ($record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn ($record) => $record === null),

                Section::make('Description')->schema([
                    TextEntry::make('description')
                        ->hiddenLabel()
                        ->markdown()
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpan(['lg' => fn ($record) => $record === null ? 3 : 2]),

                Section::make('Attachments')->schema([
                    SpatieMediaLibraryImageEntry::make('attachments')
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columnSpan(['lg' => fn ($record) => $record === null ? 3 : 2]),
            ])
            ->columns(3);
    }
}
