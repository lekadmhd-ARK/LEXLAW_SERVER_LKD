<?php

namespace App\Filament\Resources\RegulationContentResource\Pages;

use App\Filament\Resources\RegulationContentResource;
use Filament\Resources\Pages\EditRecord;

class EditRegulationContent extends EditRecord
{
    protected static string $resource = RegulationContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
