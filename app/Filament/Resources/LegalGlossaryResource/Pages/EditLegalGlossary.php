<?php

namespace App\Filament\Resources\LegalGlossaryResource\Pages;

use App\Filament\Resources\LegalGlossaryResource;
use Filament\Resources\Pages\EditRecord;

class EditLegalGlossary extends EditRecord
{
    protected static string $resource = LegalGlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
