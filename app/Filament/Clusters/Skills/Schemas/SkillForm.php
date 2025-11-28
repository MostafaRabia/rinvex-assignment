<?php

namespace App\Filament\Clusters\Skills\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class SkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Main information')
                        ->completedIcon(Heroicon::HandThumbUp)
                        ->columnSpanFull()
                        ->schema([
                            Group::make([
                                TextInput::make('name')
                                    ->unique('skills', 'name', ignoreRecord: true)
                                    ->required(),
                                TextInput::make('category')
                                    ->live()
                                    ->required(),
                                TextInput::make('proficiency_level')
                                    ->visible(fn ($get) => strtolower($get('category')) === 'technical')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5),
                            ])->columns(fn ($get) => strtolower($get('category')) === 'technical' ? 3 : 2),
                            MarkdownEditor::make('description')
                                ->columnSpanFull(),
                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                        ]),
                    Wizard\Step::make('Additional info')
                        ->completedIcon(Heroicon::HandThumbUp)
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('attachments')
                                ->multiple(),
//                            TagsInput::make('tags'),
                            Repeater::make('tags')
                                ->defaultItems(1)
                                ->simple(
                                    Textarea::make('tags'),
                                ),
                            Textarea::make('notes')
                                ->maxLength(200)
                                ->hint(function ($component) {
                                    return new HtmlString('
                                    <div x-data="{ state: $wire.$entangle(\'' . $component->getStatePath() . '\') }">
                                            <span 
                                                x-text="state?.length ?? 0" 
                                                :class="(state?.length ?? 0) > 200 ? \'text-danger-600 font-bold\' : \'text-primary-600\'"
                                            ></span> 
                                            <span class="text-gray-500">/ 200</span>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),
                ])
                ->skippable(fn ($livewire) => $livewire->getRecord() !== null)
                ->columnSpanFull()
            ]);
    }
}
