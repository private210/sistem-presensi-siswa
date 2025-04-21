<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresensiResource\Pages;
use App\Models\Presensi;
use App\Models\HariMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use App\Exports\PresensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PresensiResource extends Resource
{
    protected static ?string $model = Presensi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Presensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('siswa_id')
                    ->relationship('siswa', 'nama', function (Builder $query) {
                        if (Auth::user()->role === 'wali_kelas') {
                            return $query->whereHas('kelas', function ($q) {
                                $q->where('wali_kelas_id', Auth::id());
                            });
                        }
                        return $query;
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('tanggal')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->maxLength(255),
                Forms\Components\Hidden::make('wali_kelas_id')
                    ->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('siswa.kelas.nama_kelas')
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hadir' => 'success',
                        'izin' => 'warning',
                        'sakit' => 'info',
                        'alpha' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('waliKelas.name')
                    ->label('Diisi Oleh'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // BulkAction::make('export')
                    //     ->label('Export ke Excel')
                    //     ->icon('heroicon-o-document-arrow-down')
                    //     ->action(function (Collection $records) {
                    //         return Excel::download(new PresensiExport($records), 'presensi.xlsx');
                    //     })
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
            'index' => Pages\ListPresensis::route('/'),
            'create' => Pages\CreatePresensi::route('/create'),
            'edit' => Pages\EditPresensi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role === 'admin') {
            return $query; // Admin sees everything
        }

        if (Auth::user()->role === 'wali_kelas') {
            return $query->where('wali_kelas_id', Auth::id());
        }

        if (Auth::user()->role === 'wali_murid') {
            return $query->whereHas('siswa', function ($q) {
                $q->whereHas('waliMurid', function ($wq) {
                    $wq->where('user_id', Auth::id());
                });
            });
        }

        return $query;
    }
}
