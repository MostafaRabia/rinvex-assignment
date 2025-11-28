<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SkillsChart extends ChartWidget
{
    protected ?string $heading = 'Skills Chart';

    public ?string $filter = 'today';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $dates = match ($this->filter) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->subWeek()->startOfDay(), now()->endOfDay()],
            'month' => [now()->subMonth()->startOfDay(), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfYear(), now()->endOfYear()],
        };

        $data = Trend::model(Skill::class)
            ->between(...$dates)
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Created skills',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }
}
