<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Member';
    protected static ?string $modelLabel = 'Member';
    protected static ?string $pluralModelLabel = 'Member';
    protected static ?string $navigationGroup = 'Manajemen Toko';
    protected static ?int $navigationSort = 5;
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
            Forms\Components\TextInput::make('name')
                ->label('Nama Member')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->label('Nomor HP')
                ->tel()
                ->required()
                ->maxLength(30)
                ->dehydrateStateUsing(fn(?string $state): ?string => static::normalizePhone($state))
                ->unique(
                    table: Member::class,
                    column: 'phone',
                    ignorable: fn($record) => $record,
                    modifyRuleUsing: fn($rule) => $rule->where('merchant_id', filament()->getTenant()?->id),
                ),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255)
                ->nullable(),

            Forms\Components\DatePicker::make('joined_at')
                ->label('Tanggal Bergabung')
                ->default(now())
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),

            Forms\Components\Textarea::make('address')
                ->label('Alamat')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->rows(3)
                ->nullable()
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable(),

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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    private static function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '+62')) {
            return '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }
}
