<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseScheduleResource\Pages;
use App\Models\CourseSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseScheduleResource extends Resource
{
    protected static ?string $model = CourseSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Kuliah';

    protected static ?string $modelLabel = 'Jadwal Kuliah';

    protected static ?string $pluralModelLabel = 'Jadwal Kuliah';

    protected static ?string $navigationGroup = 'Akademik';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('study_class_id')
                ->label('Kelas')
                ->relationship('studyClass', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('course_id')
                ->label('Mata Kuliah')
                ->relationship('course', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('lecturer_name')
                ->label('Nama Dosen')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('day_of_week')
                ->label('Hari')
                ->options([
                    'Senin' => 'Senin',
                    'Selasa' => 'Selasa',
                    'Rabu' => 'Rabu',
                    'Kamis' => 'Kamis',
                    'Jumat' => 'Jumat',
                    'Sabtu' => 'Sabtu',
                    'Minggu' => 'Minggu',
                ])
                ->required(),

            Forms\Components\TimePicker::make('start_time')
                ->label('Jam Mulai')
                ->seconds(false)
                ->required(),

            Forms\Components\TimePicker::make('end_time')
                ->label('Jam Selesai')
                ->seconds(false)
                ->after('start_time')
                ->required(),

            Forms\Components\TextInput::make('room')
                ->label('Ruangan')
                ->maxLength(255),

            Forms\Components\Toggle::make('is_active')
                ->label('Jadwal Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('studyClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lecturer_name')
                    ->label('Dosen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('room')
                    ->label('Ruangan'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('day_of_week')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Terpilih'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSchedules::route('/'),
            'create' => Pages\CreateCourseSchedule::route('/create'),
            'edit' => Pages\EditCourseSchedule::route('/{record}/edit'),
        ];
    }
}
