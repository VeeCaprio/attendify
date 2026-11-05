<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Student;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Data Absensi';

    protected static ?string $navigationGroup = 'Akademik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_session_id')
                    ->label('Sesi Perkuliahan')
                    ->relationship('classSession', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn (ClassSession $record): string => ($record->courseSchedule?->course?->name ?? 'Mata Kuliah')
                            .' - Pertemuan '.$record->session_number
                            .' ('.($record->session_date?->format('d/m/Y') ?? '-').')'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('student_id')
                    ->label('Mahasiswa')
                    ->relationship('student', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Student $record): string => $record->nim.' - '.$record->name
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rule(function (Get $get, ?Attendance $record): Closure {
                        return function (string $attribute, $value, Closure $fail) use ($get, $record): void {
                            if (blank($value) || blank($get('class_session_id'))) {
                                return;
                            }

                            $query = Attendance::query()
                                ->where('class_session_id', $get('class_session_id'))
                                ->where('student_id', $value);

                            if ($record?->exists) {
                                $query->whereKeyNot($record->getKey());
                            }

                            if ($query->exists()) {
                                $fail('Mahasiswa ini sudah memiliki absensi pada sesi tersebut.');
                            }
                        };
                    }),

                Forms\Components\Select::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Alpa' => 'Alpa',
                    ])
                    ->default('Hadir')
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Opsional')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('classSession.courseSchedule.course.name')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('classSession.session_date')
                    ->label('Tanggal Sesi')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'warning',
                        'Sakit' => 'warning',
                        'Alpa' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),

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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
