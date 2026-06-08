<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\CashierResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class CashierResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $modelLabel = 'Kasir';
    protected static ?string $pluralModelLabel = 'Kasir';
    protected static ?string $navigationGroup = 'Manajemen Toko';
    protected static ?string $slug = 'cashiers';
    protected static ?string $tenantRelationshipName = 'cashiers';
    protected static ?int $navigationSort = 3;
    protected static ?string $tenantOwnershipRelationshipName = 'employedAt';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    // ─── Scope: hanya tampilkan kasir milik merchant ini ─────────
    public static function getEloquentQuery(): Builder
    {
        $merchantId = filament()->getTenant()?->id;

        return parent::getEloquentQuery()
            ->where('merchant_id', $merchantId)
            ->where('role', 'cashier');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Kasir')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(table: User::class, ignorable: fn ($record) => $record)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => !empty($state))
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateCashier)
                    ->helperText('Kosongkan jika tidak ingin mengubah password (saat edit)')
                    ->minLength(6),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                // Hidden: auto-diisi dari tenant saat ini
                Forms\Components\Hidden::make('merchant_id')
                    ->default(fn () => filament()->getTenant()?->id),
                    
                Forms\Components\Hidden::make('role')
                    ->default('cashier'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCashiers::route('/'),
            'create' => Pages\CreateCashier::route('/create'),
            'edit'   => Pages\EditCashier::route('/{record}/edit'),
        ];
    }
}
