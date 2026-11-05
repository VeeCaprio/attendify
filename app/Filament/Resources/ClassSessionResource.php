<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSessionResource\Pages;
use App\Models\ClassSession;
use App\Models\CourseSchedule;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Sesi Perkuliahan';

    protected static ?string $modelLabel = 'Sesi Perkuliahan';

    protected static ?string $pluralModelLabel = 'Sesi Perkuliahan';

    protected static ?string $navigationGroup = 'Akademik';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_schedule_id')
                ->label('Jadwal Kuliah')
                ->relationship('courseSchedule', 'id')
                ->getOptionLabelFromRecordUsing(
                    fn (CourseSchedule $record): string => ($record->studyClass?->name ?? 'Kelas')
                        .' - '
                        .($record->course?->name ?? 'Mata Kuliah')
                        .' ('.$record->day_of_week.')'
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('session_number')
                ->label('Pertemuan Ke-')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->required()
                ->rule(function (Get $get, ?ClassSession $record): Closure {
                    return function (string $attribute, $value, Closure $fail) use ($get, $record): void {
                        if (blank($value) || blank($get('course_schedule_id'))) {
                            return;
                        }

                        $query = ClassSession::query()
                            ->where('course_schedule_id', $get('course_schedule_id'))
                            ->where('session_number', $value);

                        if ($record?->exists) {
                            $query->whereKeyNot($record->getKey());
                        }

                        if ($query->exists()) {
                            $fail('Pertemuan ke-'.$value.' sudah ada pada jadwal kuliah tersebut.');
                        }
                    };
                }),

            Forms\Components\DatePicker::make('session_date')
                ->label('Tanggal Perkuliahan')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required(),

            Forms\Components\Textarea::make('topic')
                ->label('Materi Perkuliahan')
                ->placeholder('Contoh: Pengenalan basis data')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courseSchedule.studyClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseSchedule.course.name')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('session_number')
                    ->label('Pertemuan Ke-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('session_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('Materi')
                    ->limit(40)
                    ->placeholder('-'),
            ])
            ->defaultSort('session_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSessions::route('/'),
            'create' => Pages\CreateClassSession::route('/create'),
            'edit' => Pages\EditClassSession::route('/{record}/edit'),
        ];
    }
}
