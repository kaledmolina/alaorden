<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Filament\Resources\VentaResource\RelationManagers;
use App\Models\Venta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use App\Models\OrdenProducto;


class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('vendedor_id')
                ->label('Vendedor')
                ->options(\App\Models\Vendedor::pluck('nombre', 'id'))
                ->searchable()
                ->reactive()
                ->required()
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    if ($state) {
                        $ordenes = \App\Models\Orden::where('vendedor_id', $state)
                            ->pluck('numero_orden', 'id')
                            ->toArray();
                        $set('orden_options', $ordenes);
                        $set('orden_id', null);
                        $set('Productos', []); // Limpiar productos al cambiar vendedor
                    }
                }),

            Forms\Components\Select::make('orden_id')
                ->label('Orden')
                ->options(function (callable $get) {
                    return $get('orden_options') ?? [];
                })
                ->searchable()
                ->reactive()
                ->required()
                ->afterStateUpdated(function (callable $set, $state) {
                    if ($state) {
                        // Obtener los productos de la orden
                        $ordenProductos = OrdenProducto::where('orden_id', $state)->get();
                        
                        // Preparar los productos para el repeater
                        $productos = $ordenProductos->map(function ($ordenProducto) {
                            return [
                                'producto_id' => $ordenProducto->producto_id,
                                'nombre' => $ordenProducto->nombre,
                                'code' => $ordenProducto->code,
                                'bar_code' => $ordenProducto->bar_code,
                                'referencia' => $ordenProducto->referencia,
                                'description' => $ordenProducto->description,
                                'precio_venta' => $ordenProducto->precio_venta,
                                'cantidad_vendida' => $ordenProducto->cantidad_asignada, // Inicialmente igual a la cantidad asignada
                                'comision' => 0, // Valor por defecto para la comisión
                            ];
                        })->toArray();

                        $set('Productos', $productos);
                    }
                }),

            Forms\Components\Hidden::make('orden_options')
                ->default([]),

            Repeater::make('Productos')
                ->relationship('VentaProducto')
                ->schema([
                    Forms\Components\Hidden::make('producto_id'),
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre del Producto'),
                    Forms\Components\TextInput::make('code')
                        ->label('Código'),
                    Forms\Components\TextInput::make('referencia')
                        ->label('Referencia'),
                    Forms\Components\TextInput::make('precio_venta')
                        ->label('Precio de Venta')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('cantidad_vendida')
                        ->label('Cantidad Vendida')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            // Calcular el total de la venta
                            $productos = $get('../../Productos');
                            $total = collect($productos)->sum(function ($producto) {
                                return ($producto['precio_venta'] ?? 0) * ($producto['cantidad_vendida'] ?? 0);
                            });
                            $set('../../total_precio', $total);
                        }),
                    Forms\Components\TextInput::make('comision')
                        ->label('Comisión (%)')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            // Calcular la ganancia del vendedor
                            $productos = $get('../../Productos');
                            $totalComision = collect($productos)->sum(function ($producto) {
                                $subtotal = ($producto['precio_venta'] ?? 0) * ($producto['cantidad_vendida'] ?? 0);
                                return $subtotal * (($producto['comision'] ?? 0) / 100);
                            });
                            $set('../../profit_vendedor', $totalComision);
                        }),
                ])
                ->defaultItems(0)
                ->addActionLabel('Añadir Productos'), // No permitir eliminar productos

            Forms\Components\TextInput::make('total_precio')
                ->label('Precio Total')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('profit_vendedor')
                ->label('Ganancia del Vendedor')
                ->numeric()
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
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
        ];
    }
}
