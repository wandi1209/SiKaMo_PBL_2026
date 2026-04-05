<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappLogResource\Pages;
use App\Models\WhatsappLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class WhatsappLogResource extends Resource
{
    protected static ?string $model = WhatsappLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationGroup = 'Log & Monitoring';

    protected static ?string $navigationLabel = 'WhatsApp Log';

    protected static ?string $modelLabel = 'WhatsApp Log';

    protected static ?string $pluralModelLabel = 'WhatsApp Logs';

    protected static ?int $navigationSort = 3;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi WhatsApp Log')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->disabled(),

                        Forms\Components\Select::make('report_id')
                            ->label('Daily Report')
                            ->relationship('dailyReport', 'id')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled()
                            ->formatStateUsing(fn($state) => match ($state) {
                                'pending' => 'Pending',
                                'sent' => 'Terkirim',
                                'failed' => 'Gagal',
                                default => $state,
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pesan')
                    ->schema([
                        Forms\Components\Textarea::make('message_body')
                            ->label('Isi Pesan')
                            ->disabled()
                            ->rows(6),
                    ]),

                Forms\Components\Section::make('Waktu')
                    ->schema([
                        Forms\Components\TextInput::make('sent_at')
                            ->label('Waktu Terkirim')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? $state->format('d-m-Y H:i:s') : '-'),

                        Forms\Components\TextInput::make('created_at')
                            ->label('Dibuat Pada')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? $state->format('d-m-Y H:i:s') : '-'),

                        Forms\Components\TextInput::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? $state->format('d-m-Y H:i:s') : '-'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dailyReport.id')
                    ->label('Report ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('message_body')
                    ->label('Pesan')
                    ->limit(50)
                    ->tooltip(fn(WhatsappLog $record) => $record->message_body),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Pending',
                        'sent' => 'Terkirim',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Waktu Terkirim')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Terkirim',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListWhatsappLogs::route('/'),
            'view' => Pages\ViewWhatsappLog::route('/{record}'),
        ];
    }
}
