<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Diskon';
    protected static ?string $modelLabel = 'Diskon';
    protected static ?string $pluralModelLabel = 'Diskon';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 4;
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('merchant_id', filament()->getTenant()?->id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Kode Voucher')
                ->required()
                ->maxLength(50)
                ->dehydrateStateUsing(fn(?string $state): ?string => $state ? strtoupper(trim($state)) : null)
                ->unique(
                    table: Discount::class,
                    column: 'code',
                    ignorable: fn($record) => $record,
                    modifyRuleUsing: fn($rule) => $rule->where('merchant_id', filament()->getTenant()?->id),
                ),

            Forms\Components\TextInput::make('name')
                ->label('Nama Promo')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->label('Jenis Diskon')
                ->options([
                    'percent' => 'Persen',
                    'fixed' => 'Nominal',
                ])
                ->default('percent')
                ->required()
                ->live(),

            Forms\Components\TextInput::make('value')
                ->label(fn(Forms\Get $get): string => $get('type') === 'fixed' ? 'Nominal Diskon' : 'Persentase Diskon')
                ->numeric()
                ->suffix(fn(Forms\Get $get): ?string => $get('type') === 'percent' ? '%' : null)
                ->prefix(fn(Forms\Get $get): ?string => $get('type') === 'fixed' ? 'Rp' : null)
                ->minValue(0)
                ->maxValue(fn(Forms\Get $get): ?int => $get('type') === 'percent' ? 100 : null)
                ->required(),

            Forms\Components\TextInput::make('max_discount_amount')
                ->label('Maksimal Potongan')
                ->helperText('Contoh: diskon 15% s/d Rp30.000. Kosongkan jika tidak dibatasi.')
                ->numeric()
                ->prefix('Rp')
                ->minValue(0)
                ->nullable(),

            Forms\Components\TextInput::make('min_purchase_amount')
                ->label('Minimal Belanja')
                ->numeric()
                ->prefix('Rp')
                ->minValue(0)
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('quota')
                ->label('Kuota')
                ->helperText('Kosongkan jika tidak dibatasi.')
                ->numeric()
                ->minValue(1)
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),

            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Mulai Berlaku')
                ->seconds(false)
                ->nullable(),

            Forms\Components\DateTimePicker::make('ends_at')
                ->label('Berakhir')
                ->seconds(false)
                ->nullable(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Promo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Diskon')
                    ->state(fn(Discount $record): string => $record->type === 'percent'
                        ? number_format((float) $record->value, 0, ',', '.') . '%'
                        : 'Rp ' . number_format((float) $record->value, 0, ',', '.')),

                Tables\Columns\TextColumn::make('min_purchase_amount')
                    ->label('Min. Belanja')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_discount_amount')
                    ->label('Maks. Potongan')
                    ->money('IDR')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage')
                    ->label('Terpakai')
                    ->state(fn(Discount $record): string => $record->quota
                        ? "{$record->used_count}/{$record->quota}"
                        : (string) $record->used_count)
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
