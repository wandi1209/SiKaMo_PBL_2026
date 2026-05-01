<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\DailyReportResource\Pages;
use App\Exports\DailyReportExport;
use App\Models\DailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $modelLabel = 'Riwayat Transaksi';
    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';
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
                Filter::make('report_date')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Dari: ' . \Carbon\Carbon::parse($data['start_date'])->format('d M Y');
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Sampai: ' . \Carbon\Carbon::parse($data['end_date'])->format('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
            // 'create' => Pages\CreateDailyReport::route('/create'),
            // 'edit' => Pages\EditDailyReport::route('/{record}/edit'),
        ];
    }
}
