<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\DailyReportResource\Pages;
use App\Models\DailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Harian';
    protected static ?string $modelLabel = 'Laporan Harian';
    protected static ?string $pluralModelLabel = 'Laporan Harian';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('report_date')
                ->label('Tanggal Laporan')
                ->required(),

            Forms\Components\TextInput::make('total_transactions')
                ->label('Total Transaksi')
                ->numeric()
                ->required()
                ->default(0),

            Forms\Components\TextInput::make('total_revenue')
                ->label('Total Pendapatan')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->default(0),

            Forms\Components\TextInput::make('total_items_sold')
                ->label('Barang Terjual')
                ->numeric()
                ->required()
                ->default(0),

            Forms\Components\Toggle::make('sent_to_whatsapp')
                ->label('Terkirim via WhatsApp')
                ->default(false),
                
            Forms\Components\DateTimePicker::make('sent_at')
                ->label('Waktu Terkirim')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal Laporan')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Total Transaksi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('sent_to_whatsapp')
                    ->label('WA Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListDailyReports::route('/'),
            'create' => Pages\CreateDailyReport::route('/create'),
            'edit' => Pages\EditDailyReport::route('/{record}/edit'),
        ];
    }
}
