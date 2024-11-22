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

    protected static ?string $navigationIcon = 'heroicon-c-user-group';
    protected static ?string $pluralModelLabel = 'Vendedores';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->label('Nombre')
                ->placeholder('Ingrese su nombre completo')
                ->maxLength(255),
            Forms\Components\TextInput::make('apellido')
                ->required()
                ->label('Apellido')
                ->placeholder('Ingrese su apellido completo')
                ->maxLength(255),
            Forms\Components\TextInput::make('cedula')
                ->label('Cédula')
                ->placeholder('Ingrese su número de cédula')
                ->maxLength(20),
            Forms\Components\DatePicker::make('fecha_nacimiento')
                ->label('Fecha de Nacimiento')
                ->nullable()
                ->placeholder('Seleccione su fecha de nacimiento'),
            Forms\Components\Select::make('sexo')
                ->options([
                    'masculino' => 'Masculino',
                    'femenino' => 'Femenino',
                    'otro' => 'Otro',
                ])
                ->label('Sexo')
                ->nullable()
                ->placeholder('Seleccione su sexo'),
            Forms\Components\TextInput::make('telefono')
                ->label('Teléfono')
                ->tel()
                ->placeholder('Ingrese su número de teléfono'),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->placeholder('Ingrese su dirección de correo electrónico')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_activo')
                ->label('Activo')
                ->default(true),
            Forms\Components\Toggle::make('is_visible')
                ->label('Visible')
                ->default(true),
            Forms\Components\Textarea::make('direccion')
                ->label('Dirección')
                ->nullable()
                ->placeholder('Ingrese su dirección completa'),
            Forms\Components\Textarea::make('descripcion')
                ->label('Descripción')
                ->nullable()
                ->placeholder('Proporcione una descripción adicional sobre el vendedor'),
        ]);
        


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->label('Apellido')
                    ->sortable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\BooleanColumn::make('is_activo')
                    ->label('Activo'),
                Tables\Columns\BooleanColumn::make('is_visible')
                    ->label('Visible'),
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
