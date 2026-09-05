<?php
namespace App\Filament\Resources\RegulationResource\Pages;
use App\Filament\Resources\RegulationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditRegulation extends EditRecord { protected static string $resource = RegulationResource::class; protected function getHeaderActions(): array { return [Actions\ViewAction::make(), Actions\DeleteAction::make()]; } }