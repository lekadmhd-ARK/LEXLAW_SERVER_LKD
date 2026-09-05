<?php

namespace App\Filament\Resources\TeamWorkspaceResource\Pages;

use App\Filament\Resources\TeamWorkspaceResource;
use Filament\Resources\Pages\ListRecords;

class ListTeamWorkspaces extends ListRecords
{
    protected static string $resource = TeamWorkspaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
