<?php

namespace App\Filament\Resources\TeamWorkspaceResource\Pages;

use App\Filament\Resources\TeamWorkspaceResource;
use Filament\Resources\Pages\EditRecord;

class EditTeamWorkspace extends EditRecord
{
    protected static string $resource = TeamWorkspaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
