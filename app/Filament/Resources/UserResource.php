<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = "heroicon-o-users";
    protected static ?string $navigationGroup = "Tenant Management";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("User Details")->schema([
                Forms\Components\TextInput::make("name")->required()->maxLength(255),
                Forms\Components\TextInput::make("email")->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\Select::make("company_id")->relationship("company", "name")->searchable()->preload(),
                Forms\Components\Select::make("role")->options([
                    "super_admin" => "Super Admin",
                    "admin" => "Admin",
                    "user" => "User",
                ])->required(),
                Forms\Components\TextInput::make("password")
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === "create"),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("email")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("company.name")->label("Company")->badge(),
                Tables\Columns\TextColumn::make("role")->badge(),
                Tables\Columns\TextColumn::make("created_at")->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("role")->options([
                    "super_admin" => "Super Admin",
                    "admin" => "Admin",
                    "user" => "User",
                ]),
                Tables\Filters\SelectFilter::make("company_id")->relationship("company", "name"),
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
            "index" => \App\Filament\Resources\UserResource\Pages\ListUsers::route("/"),
            "create" => \App\Filament\Resources\UserResource\Pages\CreateUser::route("/create"),
            "edit" => \App\Filament\Resources\UserResource\Pages\EditUser::route("/{record}/edit"),
        ];
    }
}
