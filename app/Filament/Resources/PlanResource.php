<?php

namespace App\Filament\Resources;

use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = "heroicon-o-credit-card";

    protected static ?string $navigationGroup = "SaaS";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Plan Information")->schema([
                Forms\Components\TextInput::make("name")->required()->maxLength(255),
                Forms\Components\TextInput::make("slug")->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make("price_monthly")->numeric()->required()->prefix("IDR"),
                Forms\Components\TextInput::make("price_yearly")->numeric()->required()->prefix("IDR"),
                Forms\Components\TextInput::make("max_users")->numeric()->required()->minValue(1),
                Forms\Components\TextInput::make("max_regulations")->numeric()->required()->minValue(1),
                Forms\Components\TextInput::make("max_ai_queries")->numeric()->required()->minValue(1),
                Forms\Components\Toggle::make("is_active")->required()->default(true),
            ])->columns(2),
            Forms\Components\Section::make("Features")->schema([
                Forms\Components\KeyValue::make("features")->keyLabel("Feature")->valueLabel("Description"),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("slug")->searchable(),
                Tables\Columns\TextColumn::make("price_monthly")->money("IDR")->sortable(),
                Tables\Columns\TextColumn::make("price_yearly")->money("IDR")->sortable(),
                Tables\Columns\TextColumn::make("max_users")->numeric()->sortable(),
                Tables\Columns\TextColumn::make("max_regulations")->numeric()->sortable(),
                Tables\Columns\TextColumn::make("max_ai_queries")->numeric()->sortable(),
                Tables\Columns\IconColumn::make("is_active")->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make("is_active"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => \App\Filament\Resources\PlanResource\Pages\ListPlans::route("/"),
            "create" => \App\Filament\Resources\PlanResource\Pages\CreatePlan::route("/create"),
            "edit" => \App\Filament\Resources\PlanResource\Pages\EditPlan::route("/{record}/edit"),
        ];
    }
}
