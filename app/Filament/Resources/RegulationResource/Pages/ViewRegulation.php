<?php
namespace App\Filament\Resources\RegulationResource\Pages;
use App\Filament\Resources\RegulationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
class ViewRegulation extends ViewRecord { protected static string $resource = RegulationResource::class; protected function getHeaderActions(): array { return [Actions\EditAction::make()]; } }