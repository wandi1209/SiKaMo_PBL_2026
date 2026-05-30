<?php

namespace App\Filament\Merchant\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Support\Str;

class EditMerchantProfile extends EditTenantProfile
{
    protected static bool $shouldRegisterNavigation = true;
    
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Profil Toko';
    
    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return 'Profil Toko';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Toko')->schema([
                    TextInput::make('name')
                        ->label('Nama Toko')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, \Filament\Forms\Set $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ])->columns(2),

                Section::make('Kontak')->schema([
                    TextInput::make('phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->nullable(),

                    Textarea::make('address')
                        ->label('Alamat')
                        ->nullable()
                        ->rows(3),

                    FileUpload::make('logo')
                        ->label('Logo')
                        ->image()
                        ->directory('merchants/logos')
                        ->nullable(),
                ]),

                Section::make('Pengaturan Toko')->schema([
                    TimePicker::make('operational_start_time')
                        ->label('Jam operasional mulai')
                        ->seconds(false)
                        ->nullable(),

                    TimePicker::make('operational_end_time')
                        ->label('Jam operasional selesai')
                        ->seconds(false)
                        ->nullable(),

                    FileUpload::make('qr_code_image')
                        ->label('QR Code Toko (gambar)')
                        ->helperText('Opsional sebagai referensi. Untuk QRIS nominal otomatis, isi Payload QRIS di bawah.')
                        ->image()
                        ->disk('public')
                        ->directory('merchants/qr-codes')
                        ->nullable()
                        ->columnSpanFull(),

                    Textarea::make('qris_payload')
                        ->label('Payload QRIS')
                        ->helperText('Otomatis terisi saat gambar QRIS berhasil dibaca. Sistem akan membuat QRIS dinamis sesuai total transaksi.')
                        ->rows(5)
                        ->nullable()
                        ->columnSpanFull(),

                    Placeholder::make('qris_payload_auto_reader')
                        ->label('')
                        ->content(view('filament.merchant.pages.partials.qris-payload-auto-reader'))
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Pengaturan Member')->schema([
                    Toggle::make('member_discount_is_active')
                        ->label('Aktifkan diskon khusus member')
                        ->helperText('Jika aktif, kasir bisa memasukkan nomor HP member dan sistem akan memberi potongan otomatis.')
                        ->live(),

                    Select::make('member_discount_type')
                        ->label('Jenis Diskon')
                        ->options([
                            'percent' => 'Persen',
                            'fixed' => 'Nominal',
                        ])
                        ->default('percent')
                        ->required()
                        ->live(),

                    TextInput::make('member_discount_value')
                        ->label(fn (\Filament\Forms\Get $get): string => $get('member_discount_type') === 'fixed' ? 'Nominal Diskon' : 'Persentase Diskon')
                        ->numeric()
                        ->prefix(fn (\Filament\Forms\Get $get): ?string => $get('member_discount_type') === 'fixed' ? 'Rp' : null)
                        ->suffix(fn (\Filament\Forms\Get $get): ?string => $get('member_discount_type') === 'percent' ? '%' : null)
                        ->minValue(0)
                        ->maxValue(fn (\Filament\Forms\Get $get): ?int => $get('member_discount_type') === 'percent' ? 100 : null)
                        ->default(0)
                        ->required(),

                    TextInput::make('member_discount_max_amount')
                        ->label('Maksimal Potongan')
                        ->helperText('Contoh: diskon member 10% s/d Rp20.000. Kosongkan jika tidak dibatasi.')
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->nullable(),

                    TextInput::make('member_discount_min_purchase')
                        ->label('Minimal Belanja')
                        ->helperText('Contoh: minimal belanja Rp50.000 agar diskon member berlaku.')
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                ])->columns(2),
            ]);
    }
}
