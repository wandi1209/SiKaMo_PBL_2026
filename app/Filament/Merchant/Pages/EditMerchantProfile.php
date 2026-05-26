<?php

namespace App\Filament\Merchant\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
                        ->helperText('Upload gambar QR Code untuk toko Anda.')
                        ->image()
                        ->directory('merchants/qr-codes')
                        ->nullable()
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
