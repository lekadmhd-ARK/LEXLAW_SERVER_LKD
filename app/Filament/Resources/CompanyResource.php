<?php

namespace App\Filament\Resources;

use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationIcon = "heroicon-o-building-office";
    protected static ?string $navigationGroup = "Tenant Management";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Company Information")->schema([
                Forms\Components\TextInput::make("name")->required()->maxLength(255),
                Forms\Components\TextInput::make("slug")->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make("address")->maxLength(255),
                Forms\Components\TextInput::make("phone")->tel()->maxLength(50),
                Forms\Components\FileUpload::make("logo_url")->image()->directory("company-logos")->disk('r2'),
            ])->columns(2),
            Forms\Components\Section::make("Subscription")->schema([
                Forms\Components\Select::make("plan_id")->relationship("plan", "name"),
                Forms\Components\Select::make("subscription_status")
                    ->options([
                        "trialing"  => "Trialing",
                        "active"    => "Active",
                        "suspended" => "Suspended",
                        "inactive"  => "Inactive",
                        "rejected"  => "Rejected",
                    ])
                    ->default("trialing"),
                Forms\Components\DateTimePicker::make("trial_ends_at"),
                Forms\Components\DateTimePicker::make("subscribed_until"),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
            Tables\Columns\TextColumn::make("plan.name")->label("Plan")->badge(),
            Tables\Columns\TextColumn::make("subscription_status")
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    "active"    => "success",
                    "trialing"  => "info",
                    "suspended" => "danger",
                    "inactive"  => "gray",
                    "rejected"  => "danger",
                    default     => "gray",
                }),
            Tables\Columns\TextColumn::make("trial_ends_at")->dateTime()->sortable(),
            Tables\Columns\TextColumn::make("subscribed_until")->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make("subscription_status")->options([
                "trialing"  => "Trialing",
                "active"    => "Active",
                "suspended" => "Suspended",
                "inactive"  => "Inactive",
                "rejected"  => "Rejected",
            ]),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make("name"),
                Infolists\Components\TextEntry::make("plan.name")->label("Plan"),
                Infolists\Components\TextEntry::make("subscription_status")->badge(),
                Infolists\Components\TextEntry::make("trial_ends_at")->dateTime(),
                Infolists\Components\TextEntry::make("subscribed_until")->dateTime(),
            ])->columns(2),
        ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            "index"  => \App\Filament\Resources\CompanyResource\Pages\ListCompanies::route("/"),
            "create" => \App\Filament\Resources\CompanyResource\Pages\CreateCompany::route("/create"),
            "view"   => \App\Filament\Resources\CompanyResource\Pages\ViewCompany::route("/{record}"),
            "edit"   => \App\Filament\Resources\CompanyResource\Pages\EditCompany::route("/{record}/edit"),
        ];
    }
}
