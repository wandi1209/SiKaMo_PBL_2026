<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Manajemen Akses';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Role')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(125)
                            ->placeholder('Contoh: admin, operator')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->description('Pilih permissions yang dimiliki role ini.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'name')
                            ->options(Permission::all()->pluck('name', 'id'))
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'operator' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Jumlah Permission')
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah User')
                    ->counts('users')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Role $record) {
                        // Prevent deleting admin role
                        if ($record->name === 'admin') {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak dapat menghapus role admin!')
                                ->danger()
                                ->send();
                            $this->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
