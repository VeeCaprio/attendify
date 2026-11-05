<?php

namespace App\Filament\Resources\StudyClassResource\Pages;

use App\Filament\Resources\StudyClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudyClass extends EditRecord
{
    protected static string $resource = StudyClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }
}
