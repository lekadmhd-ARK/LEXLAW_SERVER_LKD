<?php

namespace App\Filament\Resources;

use App\Models\TeamWorkspace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamWorkspaceResource extends Resource
{
    protected static ?string $model = TeamWorkspace::class;
    protected static ?string $navigationIcon = "heroicon-o-user-group";
    protected static ?string $navigationGroup = "Tenant Management";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("tenant_id")->numeric(),
            Forms\Components\Select::make("company_id")->relationship("company", "name")->searchable()->preload()->required(),
            Forms\Components\TextInput::make("name")->required()->maxLength(255),
            Forms\Components\Textarea::make("description")->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make("is_active")->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("company.name")->label("Company")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("description")->limit(50),
                Tables\Columns\IconColumn::make("is_active")->boolean()->sortable(),
                Tables\Columns\TextColumn::make("created_at")->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("company_id")->relationship("company", "name"),
                Tables\Filters\TernaryFilter::make("is_active"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            "index" => \App\Filament\Resources\TeamWorkspaceResource\Pages\ListTeamWorkspaces::route("/"),
            "create" => \App\Filament\Resources\TeamWorkspaceResource\Pages\CreateTeamWorkspace::route("/create"),
            "edit" => \App\Filament\Resources\TeamWorkspaceResource\Pages\EditTeamWorkspace::route("/{record}/edit"),
        ];
    }
}
