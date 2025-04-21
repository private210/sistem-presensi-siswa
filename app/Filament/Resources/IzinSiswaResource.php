<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IzinSiswaResource\Pages;
use App\Models\IzinSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IzinSiswaResource extends Resource
{
    protected static ?string $model = IzinSiswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Presensi';
    protected static ?string $navigationLabel = 'Izin Siswa';

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
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->required()
                    ->after('tanggal_mulai'),
                Forms\Components\Textarea::make('keterangan')
                    ->required(),
                Forms\Components\FileUpload::make('bukti')
                    ->directory('bukti-izin')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required()
                    ->visible(fn() => Auth::user()->role === 'admin' || Auth::user()->role === 'wali_kelas'),
                Forms\Components\Hidden::make('disetujui_oleh')
                    ->default(fn() => Auth::id())
                    ->visible(false),
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
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(30),
                Tables\Columns\TextColumn::make('disetujuiOleh.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_selesai', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) => ($record->status === 'pending') &&
                            (Auth::user()->role === 'admin' || Auth::user()->role === 'wali_kelas')
                    )
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'disetujui',
                            'disetujui_oleh' => Auth::id(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) => ($record->status === 'pending') &&
                            (Auth::user()->role === 'admin' || Auth::user()->role === 'wali_kelas')
                    )
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'ditolak',
                            'disetujui_oleh' => Auth::id(),
                        ]);
                    }),
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
            'index' => Pages\ListIzinSiswas::route('/'),
            'create' => Pages\CreateIzinSiswa::route('/create'),
            'edit' => Pages\EditIzinSiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role === 'wali_kelas') {
            return $query->whereHas('siswa', function ($q) {
                $q->whereHas('kelas', function ($kq) {
                    $kq->where('wali_kelas_id', Auth::id());
                });
            });
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