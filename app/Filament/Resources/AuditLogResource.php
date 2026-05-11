<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Log & Monitoring';
    protected static ?string $navigationLabel = 'Audit Log';
    protected static ?string $modelLabel = 'Audit Log';
    protected static ?string $pluralModelLabel = 'Audit Logs';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Aktivitas')
                ->schema([
                    Forms\Components\TextInput::make('event')->label('Event')->disabled(),
                    Forms\Components\TextInput::make('user.name')->label('User')->disabled(),
                    Forms\Components\TextInput::make('auditable_type')->label('Model')->disabled(),
                    Forms\Components\TextInput::make('auditable_label')->label('Label')->disabled(),
                    Forms\Components\Textarea::make('old_values')->label('Sebelum')->disabled()->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
                    Forms\Components\Textarea::make('new_values')->label('Sesudah')->disabled()->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y, H:i')->sortable(),
                Tables\Columns\TextColumn::make('event')->label('Event')->badge()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->placeholder('System')->searchable(),
                Tables\Columns\TextColumn::make('auditable_type')->label('Model')->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('auditable_label')->label('Data')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
