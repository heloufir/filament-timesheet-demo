<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\TimeRegistration;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Heloufir\FilamentTimesheet\Filament\TimeField;
use Heloufir\FilamentTimesheet\Livewire\TimesheetBoard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExportDemo extends TimesheetBoard
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Export Demo';

    protected ?string $subheading = 'Every hour the data will be restored';

    protected ?string $model = TimeRegistration::class;

    public bool $enableExportAction = true;

    protected function getActions(): array
    {
        return array_merge([
            Action::make('source')
                ->color('gray')
                ->icon('heroicon-m-code-bracket')
                ->label('View on Github')
                ->url('https://github.com/heloufir/filament-timesheet-demo/blob/main/BasicBoardDemo.php')
        ], Parent::getActions());
    }

    protected function query(Carbon $start, Carbon $end): Builder
    {
        $query = TimeRegistration::query();
        $query->whereBetween('date', [$start, $end]);
        $query->where('user_id', auth()->user()->id);
        $query->with(['task']);
        return $query;
    }

    protected function mapper(Model $item): array
    {
        return [
            'id' => $item->id,
            'date' => $item->date,
            'task' => $item->task->name,
            'description' => $item->description,
            'time' => $item->time,
        ];
    }

    public function boardHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('submit')
                ->size(ActionSize::ExtraSmall)
                ->action(
                    fn() => Notification::make('submitted')
                        ->success()
                        ->title('Submitted')
                        ->body('This is a fake Submit action')
                        ->send()
                )
        ];
    }

    public function formSchema(): array
    {
        return [
            Select::make('task_id')
                ->label('Task')
                ->searchable()
                ->options(Task::all()->pluck('name', 'id'))
                ->preload()
                ->required(),

            DatePicker::make('date')
                ->label('Date')
                ->required(),

            Textarea::make('description')
                ->label('Description')
                ->required(),

            TimeField::make('time')
                ->label('Time')
                ->placeholder('Eg. 1h 15m')
                ->required(),
        ];
    }

    protected function exportHeadings(): array
    {
        return [
            '#',
            'Date',
            'Task',
            'Description',
            'Time'
        ];
    }
}
