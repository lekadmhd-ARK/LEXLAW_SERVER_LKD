<?php

namespace App\Filament\Resources;

use App\Models\Regulation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegulationResource extends Resource
{
    protected static ?string $model = Regulation::class;

    protected static ?string $navigationIcon = "heroicon-o-scale";

    protected static ?string $navigationGroup = "Legal";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Regulation Details")->schema([
                Forms\Components\Select::make("company_id")->relationship("company", "name")->searchable()->preload()->required(),
                Forms\Components\TextInput::make("title")->required()->maxLength(255),
                Forms\Components\TextInput::make("number")->maxLength(100),
                Forms\Components\TextInput::make("year")->numeric()->minValue(1900)->maxValue((int) date("Y") + 1),
                Forms\Components\TextInput::make("category")->maxLength(255),
                Forms\Components\Select::make("status")->options([
                    "draft" => "Draft",
                    "active" => "Active",
                    "archived" => "Archived",
                ])->default("draft")->required(),
                Forms\Components\DatePicker::make("effective_date"),
                Forms\Components\Select::make("created_by")->relationship("creator", "name")->searchable()->preload(),
                Forms\Components\TextInput::make("source_url")->url()->maxLength(255),
            ])->columns(2),
            Forms\Components\Section::make("Content")->schema([
                Forms\Components\Textarea::make("description")->rows(4),
                Forms\Components\RichEditor::make("content_text")->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("title")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("number")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("year")->sortable(),
                Tables\Columns\TextColumn::make("company.name")->label("Company")->searchable()->badge(),
                Tables\Columns\TextColumn::make("category")->searchable()->toggleable(),
                Tables\Columns\TextColumn::make("status")->badge()->color(fn (string $state): string => match ($state) {
                    "active" => "success",
                    "draft" => "gray",
                    "archived" => "warning",
                    default => "gray",
                }),
                Tables\Columns\TextColumn::make("effective_date")->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("status")->options([
                    "draft" => "Draft",
                    "active" => "Active",
                    "archived" => "Archived",
                ]),
                Tables\Filters\SelectFilter::make("company_id")->relationship("company", "name"),
                Tables\Filters\SelectFilter::make("year"),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make("title"),
                Infolists\Components\TextEntry::make("number"),
                Infolists\Components\TextEntry::make("year"),
                Infolists\Components\TextEntry::make("company.name")->label("Company"),
                Infolists\Components\TextEntry::make("category"),
                Infolists\Components\TextEntry::make("status")->badge(),
                Infolists\Components\TextEntry::make("effective_date")->date(),
                Infolists\Components\TextEntry::make("source_url")->url(),
                Infolists\Components\TextEntry::make("description")->markdown()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => \App\Filament\Resources\RegulationResource\Pages\ListRegulations::route("/"),
            "create" => \App\Filament\Resources\RegulationResource\Pages\CreateRegulation::route("/create"),
            "view" => \App\Filament\Resources\RegulationResource\Pages\ViewRegulation::route("/{record}"),
            "edit" => \App\Filament\Resources\RegulationResource\Pages\EditRegulation::route("/{record}/edit"),
        ];
    }
}
