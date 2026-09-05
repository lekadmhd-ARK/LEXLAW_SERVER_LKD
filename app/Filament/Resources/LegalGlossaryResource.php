<?php

namespace App\Filament\Resources;

use App\Models\LegalGlossary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegalGlossaryResource extends Resource
{
    protected static ?string $model = LegalGlossary::class;
    protected static ?string $navigationIcon = "heroicon-o-book-open";
    protected static ?string $navigationGroup = "Legal";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("tenant_id")->numeric(),
            Forms\Components\TextInput::make("term")->required()->maxLength(255),
            Forms\Components\TextInput::make("category")->maxLength(255),
            Forms\Components\RichEditor::make("definition")->required()->columnSpanFull(),
            Forms\Components\TagsInput::make("cross_references")->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("term")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("category")->searchable()->sortable()->badge(),
                Tables\Columns\TextColumn::make("definition")->limit(60)->html(),
                Tables\Columns\TextColumn::make("updated_at")->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("category"),
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
            "index" => \App\Filament\Resources\LegalGlossaryResource\Pages\ListLegalGlossaries::route("/"),
            "create" => \App\Filament\Resources\LegalGlossaryResource\Pages\CreateLegalGlossary::route("/create"),
            "edit" => \App\Filament\Resources\LegalGlossaryResource\Pages\EditLegalGlossary::route("/{record}/edit"),
        ];
    }
}
