<?php

namespace App\Filament\Resources\RegulationContentResource\Pages;

use App\Filament\Resources\RegulationContentResource;
use Filament\Resources\Pages\ListRecords;

class ListRegulationContents extends ListRecords
{
    protected static string $resource = RegulationContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
