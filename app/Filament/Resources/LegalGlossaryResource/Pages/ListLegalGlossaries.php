<?php

namespace App\Filament\Resources\LegalGlossaryResource\Pages;

use App\Filament\Resources\LegalGlossaryResource;
use Filament\Resources\Pages\ListRecords;

class ListLegalGlossaries extends ListRecords
{
    protected static string $resource = LegalGlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
