<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendedorResource\Pages;
use App\Filament\Resources\VendedorResource\RelationManagers;
use App\Models\Vendedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendedorResource extends Resource
{
    protected static ?string $model = Vendedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->label('Nombre'),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->label('Apellido'),
                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono'),
                Forms\Components\TextInput::make('cedula')
                    ->label('Cédula'),
                Forms\Components\Toggle::make('is_activo')
                    ->label('Activo')
                    ->default(true),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Visible')
                    ->default(true),
                Forms\Components\Textarea::make('direccion')
                    ->label('Dirección')
                    ->nullable(),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->nullable(),
                Forms\Components\Select::make('sexo')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                        'otro' => 'Otro',
                    ])
                    ->label('Sexo')
                    ->nullable(),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ManageVendedors::route('/'),
        ];
    }
}
