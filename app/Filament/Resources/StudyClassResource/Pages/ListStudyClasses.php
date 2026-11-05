<?php

namespace App\Filament\Resources\StudyClassResource\Pages;

use App\Filament\Resources\StudyClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudyClasses extends ListRecords
{
    protected static string $resource = StudyClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah'),
        ];
    }
}
