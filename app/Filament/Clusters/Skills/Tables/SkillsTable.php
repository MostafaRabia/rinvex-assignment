<?php

namespace App\Filament\Clusters\Skills\Tables;

use App\Models\Skill;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class SkillsTable
{
    public static function seedSkills(): void
    {
        $data = Http::get('https://dummyjson.com/products?limit=10&select=title,category,description');

        if ($data->successful()) {
            $skills = $data->json('products');

            foreach ($skills as $skill) {
                if (Skill::where('name', $skill['title'])->exists()) {
                    continue;
                }

                Skill::create([
                    'name' => $skill['title'],
                    'category' => $skill['category'],
                    'description' => $skill['description'],
                    'proficiency_level' => strtolower($skill['category']) === 'technical' ? rand(1, 5) : null,
                    'is_active' => true,
                ]);
            }

            Notification::make()
                ->title('10 skills seeded successfully.')
                ->success()
                ->send()
                ->sendToDatabase(auth()->user());
        } else {
            Notification::make()
                ->title('Failed to fetch skills from external source.')
                ->danger()
                ->send()
                ->sendToDatabase(auth()->user());
        }
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('proficiency_level')
                    ->summarize(Average::make())
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->summarize([
                        Count::make()->query(fn ($query) => $query->where('is_active', true))->label('Total Active Skills'),
                        Count::make()->query(fn ($query) => $query->where('is_active', false))->label('Total Not Active Skills')
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(function () {
                        return \App\Models\Skill::query()
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray();
                    })
                    ->label('Category'),

                Filter::make('proficiency_level')
                    ->schema([
                        TextInput::make('proficiency_level_from')
                            ->label('Proficiency Level From')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['proficiency_level_from'],
                            fn ($query, $value) => $query->where('proficiency_level', '>=', $value),
                        );
                    }),

                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('Archive')
                    ->action(function ($record) {
                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->title('Skill archived successfully.')
                            ->success()
                            ->send()
                            ->sendToDatabase(auth()->user());
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon(Heroicon::ArchiveBox)
                    ->visible(fn ($record) => $record->is_active),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('Archive')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));

                            Notification::make()
                                ->title('Selected skills archived successfully.')
                                ->success()
                                ->send()
                                ->sendToDatabase(auth()->user());
                        })
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon(Heroicon::ArchiveBox),
                ]),
            ])
            ->headerActions([
                Action::make('Seed skills')
                    ->action(fn () => self::seedSkills())
                    ->icon(Heroicon::Sparkles),
            ])
            ->groups(['category', 'proficiency_level']);
    }
}
