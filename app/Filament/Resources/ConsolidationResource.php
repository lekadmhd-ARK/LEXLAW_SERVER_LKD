<?php

namespace App\Filament\Resources;

use App\Models\Consolidation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsolidationResource extends Resource
{
    protected static ?string $model = Consolidation::class;
    protected static ?string $navigationIcon = "heroicon-o-document-duplicate";
    protected static ?string $navigationGroup = "Legal";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("tenant_id")->numeric(),
            Forms\Components\TextInput::make("title")->required()->maxLength(255),
            Forms\Components\TextInput::make("version")->required()->maxLength(100),
            Forms\Components\Select::make("regulation_ids")->label("Regulations")->multiple()->searchable()->preload(),
            Forms\Components\RichEditor::make("consolidated_text")->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("title")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("version")->searchable()->sortable()->badge(),
                Tables\Columns\TextColumn::make("updated_at")->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make("updated_at")
                    ->form([
                        Forms\Components\DatePicker::make("from"),
                        Forms\Components\DatePicker::make("until"),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data["from"] ?? null, fn ($q, $date) => $q->whereDate("updated_at", ">=", $date))
                        ->when($data["until"] ?? null, fn ($q, $date) => $q->whereDate("updated_at", "<=", $date))
                    ),
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
            "index" => \App\Filament\Resources\ConsolidationResource\Pages\ListConsolidations::route("/"),
            "create" => \App\Filament\Resources\ConsolidationResource\Pages\CreateConsolidation::route("/create"),
            "edit" => \App\Filament\Resources\ConsolidationResource\Pages\EditConsolidation::route("/{record}/edit"),
        ];
    }
}
