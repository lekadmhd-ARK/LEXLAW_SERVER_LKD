<?php

namespace App\Filament\Resources;

use App\Models\RegulationContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegulationContentResource extends Resource
{
    protected static ?string $model = RegulationContent::class;
    protected static ?string $navigationIcon = "heroicon-o-document-text";
    protected static ?string $navigationGroup = "Legal";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make("regulation_id")->relationship("regulation", "title")->searchable()->preload()->required(),
            Forms\Components\TextInput::make("article_number")->required()->maxLength(100),
            Forms\Components\TextInput::make("article_title")->maxLength(255),
            Forms\Components\RichEditor::make("content")->required()->columnSpanFull(),
            Forms\Components\KeyValue::make("sub_articles")->keyLabel("Article")->valueLabel("Content")->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("regulation.title")->label("Regulation")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("article_number")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("article_title")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("updated_at")->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("regulation_id")->relationship("regulation", "title"),
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
            "index" => \App\Filament\Resources\RegulationContentResource\Pages\ListRegulationContents::route("/"),
            "create" => \App\Filament\Resources\RegulationContentResource\Pages\CreateRegulationContent::route("/create"),
            "edit" => \App\Filament\Resources\RegulationContentResource\Pages\EditRegulationContent::route("/{record}/edit"),
        ];
    }
}
