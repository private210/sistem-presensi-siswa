<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HariMasukResource\Pages;
use App\Models\HariMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HariMasukResource extends Resource
{
    protected static ?string $model = HariMasuk::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';
    protected static ?string $navigationLabel = 'Hari Masuk & Libur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'masuk' => 'Masuk',
                        'libur' => 'Libur',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255),
                Forms\Components\Hidden::make('bulan')
                    ->default(fn($get) => $get('tanggal') ? date('n', strtotime($get('tanggal'))) : date('n')),
                Forms\Components\Hidden::make('tahun')
                    ->default(fn($get) => $get('tanggal') ? date('Y', strtotime($get('tanggal'))) : date('Y')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'masuk' => 'success',
                        'libur' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('keterangan'),
            ])
            ->filters([
                Tables\Filters\Filter::make('bulan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(date('n')),
                        Forms\Components\Select::make('tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = (int) date('Y');
                                for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(date('Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bulan'],
                                fn(Builder $query, $bulan): Builder => $query->where('bulan', $bulan),
                            )
                            ->when(
                                $data['tahun'],
                                fn(Builder $query, $tahun): Builder => $query->where('tahun', $tahun),
                            );
                    }),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHariMasuks::route('/'),
            'create' => Pages\CreateHariMasuk::route('/create'),
            'edit' => Pages\EditHariMasuk::route('/{record}/edit'),
        ];
    }
}