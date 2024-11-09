<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Filament\Resources\OrdenResource\RelationManagers;
use App\Models\Orden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Vendedor;
use App\Models\Producto;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;



class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-list';
    protected static ?string $pluralModelLabel = 'Ordenes';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            // Campo para el número de orden
            Forms\Components\TextInput::make('numero_orden')
                ->label('Número de Orden')
                ->required()
                ->unique(),

            // Campo para seleccionar el vendedor
            Forms\Components\Select::make('vendedor_id')
                ->label('Vendedor')
                ->options(Vendedor::where('is_activo', true)
                ->selectRaw('id, CONCAT(nombre, " ", apellido) as full_name')
                ->pluck('full_name', 'id'))
                ->searchable()
                ->required(),

            // Campo para el total de la orden
            Forms\Components\TextInput::make('total_precio')
                ->label('Total Precio')
                ->numeric()
                ->required(),

            // Repeater para gestionar los productos asociados a la orden
            Forms\Components\Repeater::make('productos')
                ->label('Productos')
                ->schema([
                    // Campo para seleccionar un producto
                    Forms\Components\Select::make('producto_id')
                        ->label('Producto')
                        ->options(Producto::all()->pluck('nombre', 'id'))
                        ->searchable()
                        ->required(),

                    // Campo para ingresar la cantidad asignada de cada producto
                    Forms\Components\TextInput::make('cantidad_asignada')
                        ->label('Cantidad Asignada')
                        ->numeric()
                        ->required(),

                    // Campo para el precio de venta del producto
                    Forms\Components\TextInput::make('pivot.precio_venta')
                        ->label('Precio de Venta')
                        ->numeric()
                        ->required(),
                ])
                ->columnSpan(2) // Tres columnas para mostrar los campos de producto, cantidad y precio
                ->required(),
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
            'index' => Pages\ListOrdens::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            'edit' => Pages\EditOrden::route('/{record}/edit'),
        ];
    }
}
