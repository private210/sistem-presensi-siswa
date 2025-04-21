<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaliMuridResource\Pages;
use App\Models\WaliMurid;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class WaliMuridResource extends Resource
{
    protected static ?string $model = WaliMurid::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('siswa_id')
                    ->relationship('siswa', 'nama')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Akun Wali Murid')
                    ->options(function () {
                        return User::where('role', 'wali_murid')
                            ->whereDoesntHave('waliMurid')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('role')
                            ->default('wali_murid'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password']),
                            'role' => 'wali_murid',
                        ])->id;
                    })
                    ->required(),
                Forms\Components\TextInput::make('hubungan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_telp')
                    ->tel()
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Wali Murid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hubungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_telp')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListWaliMurids::route('/'),
            'create' => Pages\CreateWaliMurid::route('/create'),
            'edit' => Pages\EditWaliMurid::route('/{record}/edit'),
        ];
    }
}