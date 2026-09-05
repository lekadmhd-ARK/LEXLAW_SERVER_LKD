<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $navigationGroup = "System";
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("created_at")->dateTime()->sortable(),
                Tables\Columns\TextColumn::make("user_name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("action")->badge()->color(fn (string $state): string => match (strtolower($state)) {
                    "create", "created" => "success",
                    "update", "updated" => "info",
                    "delete", "deleted" => "danger",
                    default => "gray",
                }),
                Tables\Columns\TextColumn::make("subject_type")->searchable(),
                Tables\Columns\TextColumn::make("subject_id")->searchable(),
                Tables\Columns\TextColumn::make("ip_address")->toggleable(),
            ])
            ->defaultSort("created_at", "desc")
            ->filters([
                Tables\Filters\SelectFilter::make("action"),
                Tables\Filters\SelectFilter::make("subject_type"),
                Tables\Filters\Filter::make("created_at")
                    ->form([
                        Forms\Components\DatePicker::make("from"),
                        Forms\Components\DatePicker::make("until"),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data["from"] ?? null, fn ($q, $date) => $q->whereDate("created_at", ">=", $date))
                        ->when($data["until"] ?? null, fn ($q, $date) => $q->whereDate("created_at", "<=", $date))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make("Audit Details")->schema([
                Infolists\Components\TextEntry::make("created_at")->dateTime(),
                Infolists\Components\TextEntry::make("user_name"),
                Infolists\Components\TextEntry::make("action")->badge(),
                Infolists\Components\TextEntry::make("subject_type"),
                Infolists\Components\TextEntry::make("subject_id"),
                Infolists\Components\TextEntry::make("ip_address"),
                Infolists\Components\TextEntry::make("old_values")->json()->columnSpanFull(),
                Infolists\Components\TextEntry::make("new_values")->json()->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            "index" => \App\Filament\Resources\AuditLogResource\Pages\ListAuditLogs::route("/"),
            "view" => \App\Filament\Resources\AuditLogResource\Pages\ViewAuditLog::route("/{record}"),
        ];
    }
}
