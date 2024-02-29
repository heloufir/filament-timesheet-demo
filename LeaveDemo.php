<?php

namespace App\Filament\Pages;

use App\Models\Leave;
use App\Models\Task;
use App\Models\TimeRegistration;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Heloufir\FilamentTimesheet\Filament\TimeField;
use Heloufir\FilamentTimesheet\Livewire\TimesheetBoard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeaveDemo extends TimesheetBoard
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $title = 'Leave Demo';

    protected ?string $subheading = 'Every hour the data will be restored';

    protected ?string $model = TimeRegistration::class;

    protected ?string $leaveModel = Leave::class;

    public bool $enableLeaveManagement = true;

    protected function getActions(): array
    {
        return array_merge([
            Action::make('source')
                ->color('gray')
                ->icon('heroicon-m-code-bracket')
                ->label('View on Github')
                ->url('https://github.com/heloufir/filament-timesheet-demo/blob/main/LeaveDemo.php')
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

    protected function leaveQuery(Carbon $start, Carbon $end): Builder
    {
        $query = Leave::query();
        $query->where(function ($query) use ($start, $end) {
            $query->where('from', '<=', $start)
                ->where('to', '>=', $end);
        })->orWhere(function ($query) use ($start, $end) {
            $query->whereBetween('from', [$start, $end])
                ->orWhereBetween('to', [$start, $end]);
        })->orWhere(function ($query) use ($start, $end) {
            $query->where('from', '>=', $start)
                ->where('to', '<=', $end);
        });
        $query->where('user_id', auth()->user()->id);
        return $query;
    }

    protected function leaveMapper(Model $item): array
    {
        return [
            'id' => $item->id,
            'from' => $item->from,
            'from_am_pm' => $item->from_am_pm,
            'to' => $item->to,
            'to_am_pm' => $item->to_am_pm,
            'description' => $item->description
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

    protected function leaveFormSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    DatePicker::make('from')
                        ->label('Start date')
                        ->beforeOrEqual('to')
                        ->required(),

                    ToggleButtons::make('from_am_pm')
                        ->label('Start date AM/PM')
                        ->helperText('Choose PM to take only half start day')
                        ->options([
                            'am' => 'AM',
                            'pm' => 'PM'
                        ])
                        ->icons([
                            'am' => 'heroicon-o-sun',
                            'pm' => 'heroicon-o-moon'
                        ])
                        ->grouped(),
                ]),

            Grid::make()
                ->schema([
                DatePicker::make('to')
                    ->label('End date')
                    ->afterOrEqual('from')
                    ->required(),

                ToggleButtons::make('to_am_pm')
                    ->label('End date AM/PM')
                    ->helperText('Choose AM to take only half end day')
                    ->options([
                        'am' => 'AM',
                        'pm' => 'PM'
                    ])
                    ->icons([
                        'am' => 'heroicon-o-sun',
                        'pm' => 'heroicon-o-moon'
                    ])
                    ->grouped(),
            ]),

            Textarea::make('description')
                ->label('Description')
                ->required(),
        ];
    }
}
