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
use App\Models\Producto;
use App\Models\StockProducto;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class VentaResource extends Resource
{  

    protected static ?string $model = Venta::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('vendedor_id')
                ->label('Vendedor')
                ->options(\App\Models\Vendedor::query()
                    ->selectRaw("id, CONCAT(nombre, ' ', apellido) AS full_name")
                    ->pluck('full_name', 'id'))
                ->searchable()
                ->live(onBlur: true)
                ->required()
                ->afterStateUpdated(function (callable $set) {
                    $set('orden_id', null);
                    $set('producto_seleccionado', null);
                    $set('Productos', []);
                }),


                Forms\Components\Select::make('orden_id')
                ->label('Orden')
                ->options(function (callable $get) {
                    $vendedorId = $get('vendedor_id');
                    return $vendedorId
                        ? \App\Models\Orden::where('vendedor_id', $vendedorId)->pluck('numero_orden', 'id')
                        : [];
                })
                ->searchable()
                ->live(onBlur: true)
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        // Obtener los productos de la orden
                        $ordenProductos = OrdenProducto::where('orden_id', $state)->get();
                        
                        // Preparar los productos para el repeater
                        $productos = $ordenProductos->map(function ($ordenProducto) {
                            return [
                                'producto_id' => $ordenProducto->producto_id,
                                'nombre' => $ordenProducto->nombre,
                                'code' => $ordenProducto->code,
                                'bar_code' => $ordenProducto->bar_code . '_' . uniqid(),
                                'referencia' => $ordenProducto->referencia,
                                'description' => $ordenProducto->description,
                                'precio_venta' => $ordenProducto->precio_venta,
                                'cantidad_vendida' => $ordenProducto->cantidad_asignada, // Inicialmente igual a la cantidad asignada
                                'comision' => 0, // Valor por defecto para la comisión
                            ];
                        })->toArray();
                
                        // Verificar el stock y la cantidad solicitada de los productos
                        $productosNoDisponibles = [];
                        foreach ($productos as $index => $producto) {
                            $stockProducto = StockProducto::where('producto_id', $producto['producto_id'])->first();
                            $cantidadDisponible = $stockProducto ? $stockProducto->cantidad_actual : 0;
                
                            if ($cantidadDisponible <= 0) {
                                // Si el producto no tiene stock, lo agregamos a la lista de productos no disponibles
                                $productosNoDisponibles[] = $producto['nombre'];
                                unset($productos[$index]); // Eliminar el producto de la lista si no tiene stock
                            } elseif ($producto['cantidad_vendida'] > $cantidadDisponible) {
                                // Si la cantidad asignada es mayor que la disponible
                                $productosNoDisponibles[] = $producto['nombre'] . ' (Cantidad asignada: ' . $producto['cantidad_vendida'] . ' > Stock disponible: ' . $cantidadDisponible . ')';
                                unset($productos[$index]); // Eliminar el producto de la lista si la cantidad asignada es mayor
                            }
                        }
                
                        if (!empty($productosNoDisponibles)) {
                            // Notificar los productos no disponibles o con cantidades inconsistentes
                            Notification::make()
                                ->title('Productos sin stock o cantidad excedida')
                                ->body('Los siguientes productos tienen problemas de stock o cantidad asignada: ' . implode(', ', $productosNoDisponibles))
                                ->warning()
                                ->send();
                        }
                
                        $set('Productos', $productos);
                
                        // Calcular total de precio
                        $total = collect($productos)->sum(function ($producto) {
                            return ($producto['precio_venta'] ?? 0) * ($producto['cantidad_vendida'] ?? 0);
                        });
                        $set('total_precio', $total);
                    }
                }),
            

            // Select para elegir un producto fuera del Repeater
            Forms\Components\Select::make('producto_seleccionado')
                ->label('Seleccionar Producto')
                ->columnSpan(2)
                ->dehydrated(false)
                ->placeholder('Ingresa nombre o referencia del producto')
                ->searchable()
                ->preload()
                ->options(fn () => self::getProductoOptions())
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Forms\Components\Select $component) {
                    if (!$state) return;
                
                    $producto = Producto::find($state);
                    if ($producto) {
                        $livewire = $component->getLivewire();
                        $productos = $livewire->data['Productos'] ?? [];
                
                        // Verificar si el producto ya está en la lista
                        $productoExistente = collect($productos)->firstWhere('producto_id', $producto->id);
                        if ($productoExistente) {
                            Notification::make()
                                ->title('Producto Duplicado')
                                ->body("El producto '{$producto->nombre}' ya está en la lista.")
                                ->warning()
                                ->send();
                            $component->state(null); // Restablecer el estado del select
                            return;
                        }
            
                        // Validar el stock disponible antes de agregar el producto
                        $stockProducto = StockProducto::where('producto_id', $producto->id)->first();
                        $cantidadDisponible = $stockProducto ? $stockProducto->cantidad_actual : 0;
            
                        // Si el stock es insuficiente, notificar y no agregar el producto
                        if ($cantidadDisponible <= 0) {
                            Notification::make()
                                ->title('Stock Insuficiente')
                                ->body("El producto '{$producto->nombre}' no tiene stock disponible.")
                                ->warning()
                                ->send();
                            $component->state(null); // Restablecer el estado del select
                            return;
                        }
            
                        // Agregar el producto al array de productos
                        $productos[] = [
                            'producto_id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'code' => $producto->code,
                            'bar_code' => $producto->bar_code,
                            'referencia' => $producto->referencia,
                            'description' => $producto->description,
                            'precio_venta' => $producto->precio_venta,
                            'cantidad_vendida' => 1, // Establecer cantidad inicial
                            'comision' => 0, // Comisión predeterminada
                        ];
            
                        $livewire->data['Productos'] = $productos; 
                        self::recalculateTotales(
                            fn($key) => $livewire->data[$key] ?? null,
                            fn($key, $value) => $livewire->data[$key] = $value
                        );
                        $component->state(null); // Restablecer el estado del select
                        
                    }
                }),

                Forms\Components\Repeater::make('Productos')
                ->relationship('VentaProducto')
                ->schema([
                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(fn () => Producto::all()->pluck('nombre', 'id'))
                                ->searchable()
                                ->disabled()
                                ->preload()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    $producto = Producto::find($state);
                                    if ($producto) {
                                        $set('nombre', $producto->nombre);
                                        $set('precio_venta', $producto->precio_venta);
                                        $set('referencia', $producto->referencia);
                                        $set('cantidad_vendida', 0);
                                        $set('code', $producto->code);
                                        $set('bar_code', $producto->bar_code);
                                        $set('description', $producto->description);
                                    }
                                }),


                                Forms\Components\TextInput::make('cantidad_vendida')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Set $set, $state, callable $get) {
                                    $productoId = $get('producto_id');
                                    $precioVenta = $get('precio_venta') ?? 0;
                                    $comision = $get('comision') ?? 0;
                            
                                    // Buscar la cantidad asignada originalmente
                                    $ordenProducto = OrdenProducto::where('producto_id', $productoId)
                                        ->where('orden_id', $get('../../orden_id'))
                                        ->first();
                            
                                    $stockProducto = StockProducto::where('producto_id', $productoId)->first();
                            
                                    $cantidadFinal = $state;
                            
                                    // Si el producto está en la orden original
                                    if ($ordenProducto) {
                                        $cantidadAsignada = $ordenProducto->cantidad_asignada;
                            
                                        if ($state > $cantidadAsignada) {
                                            Notification::make()
                                                ->title('Advertencia')
                                                ->body("La cantidad ingresada ({$state}) supera la cantidad asignada originalmente ({$cantidadAsignada}).")
                                                ->warning()
                                                ->send();
                            
                                            $cantidadFinal = $cantidadAsignada;
                                            $set('cantidad_vendida', $cantidadFinal); // Actualización síncrona
                                        }
                                    } 
                                    // Validar solo por stock si no está en la orden original
                                    else if ($stockProducto && $state > $stockProducto->cantidad_actual) {
                                        Notification::make()
                                            ->title('Advertencia')
                                            ->body("La cantidad ingresada excede el stock disponible ({$stockProducto->cantidad_actual}).")
                                            ->warning()
                                            ->send();
                            
                                        $cantidadFinal = $stockProducto->cantidad_actual;
                                        $set('cantidad_vendida', $cantidadFinal); // Actualización síncrona
                                    }
                            
                                    // Calcular subtotal con la cantidad corregida
                                    $subtotal = $cantidadFinal * $precioVenta;
                                    $set('subtotal', $subtotal);
                            
                                    // Calcular ganancia unitaria
                                    $gananciaUnitaria = $subtotal * ($comision / 100);
                                    $set('profitunitario', round($gananciaUnitaria, 2));
                            
                                    // Recalcular totales después de actualizar la cantidad corregida
                                    self::recalculateTotales(
                                        function($key) use ($get) { return $get($key); }, 
                                        $set
                                    );
                                }),
                            Forms\Components\TextInput::make('precio_venta')
                                ->label('Precio')
                                ->numeric()
                                ->required()
                                ->columnSpan(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $cantidad = $get('cantidad_vendida') ?? 0;
                                    $precioVenta = $get('precio_venta') ?? 0;
                                    $comision = $get('comision') ?? 0;
                            
                                    // Calcular subtotal
                                    $subtotal = $cantidad * $precioVenta;
                                    $set('subtotal', $subtotal);
                            
                                    // Calcular ganancia unitaria
                                    $gananciaUnitaria = $subtotal * ($comision / 100);
                                    $set('profitunitario', $gananciaUnitaria);
                            
                                    // Recalcular totales del formulario
                                    self::recalculateTotales($get, $set);
                                }),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('comision')
                                ->label('Comisión (%)')
                                ->numeric()
                                ->default(0)
                                ->columnSpan(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $cantidad = $get('cantidad_vendida') ?? 0;
                                    $precioVenta = $get('precio_venta') ?? 0;
                                    $comision = $get('comision') ?? 0;
                                    
                                    // Calcular subtotal
                                    $subtotal = $cantidad * $precioVenta;
                                    
                                    // Calcular ganancia unitaria
                                    $gananciaUnitaria = $subtotal * ($comision / 100);
                                    $set('profitunitario', round($gananciaUnitaria, 2));
                            
                                    // Recalcular totales del formulario
                                    self::recalculateTotales($get, $set);
                                }),

                            Forms\Components\TextInput::make('profitunitario')
                                ->label('Ganancia Unitaria')
                                ->numeric()
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $cantidad = $get('cantidad_vendida') ?? 0;
                                    $precioVenta = $get('precio_venta') ?? 0;
                                    
                                    // Calcular subtotal
                                    $subtotal = $cantidad * $precioVenta;
                                    
                                    // Calcular porcentaje de comisión
                                    if ($subtotal > 0) {
                                        $comision = ($get('profitunitario') / $subtotal) * 100;
                                        $set('comision', round($comision, 2));
                                    }
                            
                                    // Recalcular totales del formulario
                                    self::recalculateTotales($get, $set);
                                }),
                        ]),
                        Forms\Components\Hidden::make('producto_id'),
                        Forms\Components\Hidden::make('nombre'),
                        Forms\Components\Hidden::make('code'),
                        Forms\Components\Hidden::make('bar_code'),
                        Forms\Components\Hidden::make('referencia'),
                        Forms\Components\Hidden::make('description'),
                        Forms\Components\Hidden::make('subtotal'),
                        Forms\Components\Hidden::make('profitunitario'),
                ])
                ->afterStateUpdated(function (callable $set, callable $get) {
                    self::recalculateTotales($get, $set);
                })
                ->defaultItems(0)
                ->addable(false)
                ->columnSpan(2),

            Forms\Components\TextInput::make('total_precio')
                ->label('Total Precio')
                ->numeric()
                ->disabled()
                ->required()
                ->columnSpan(2),

            Forms\Components\TextInput::make('profit_vendedor')
                ->label('Ganancia del Vendedor')
                ->numeric()
                ->disabled()
                ->required()
                ->columnSpan(2),
            Forms\Components\Hidden::make('total_precio'),
            Forms\Components\Hidden::make('profit_vendedor'),     
        ]);
        
    }
    protected static function recalculateTotales(callable $get, callable $set)
{
    // Obtén los productos del formulario
    $productos = $get('Productos') ?? [];

    // Recalcula el total del precio y la ganancia total del vendedor
    $totalPrecio = 0;
    $gananciaVendedor = 0;

    foreach ($productos as $index => $producto) {
        $productoId = $producto['producto_id'];
        $cantidad = $producto['cantidad_vendida'] ?? 0;
        $precioVenta = $producto['precio_venta'] ?? 0;
        $comision = $producto['comision'] ?? 0;

        // Buscar la cantidad asignada originalmente
        $ordenProducto = OrdenProducto::where('producto_id', $productoId)
            ->where('orden_id', $get('orden_id'))
            ->first();

        // Ajustar cantidad si supera la cantidad asignada
        if ($ordenProducto && $cantidad > $ordenProducto->cantidad_asignada) {
            $cantidad = $ordenProducto->cantidad_asignada;
            
            // Actualizar la cantidad en el array de productos
            $productos[$index]['cantidad_vendida'] = $cantidad;
        }

        $subtotal = $cantidad * $precioVenta;
        $ganancia = $subtotal * ($comision / 100);

        $totalPrecio += $subtotal;
        $gananciaVendedor += $ganancia;
    }

    // Actualiza los campos totales
    $set('total_precio', round($totalPrecio, 2));
    $set('profit_vendedor', round($gananciaVendedor, 2));
    
    // Opcional: Actualizar el array de productos con las cantidades corregidas
    $set('Productos', $productos);
}


    protected static function getProductoOptions(): array
    {
        return Producto::query()
            ->whereNotNull('nombre')
            ->get()
            ->mapWithKeys(fn ($producto) => [
                $producto->id => "{$producto->nombre} - Ref: {$producto->referencia}",
            ])
            ->toArray();
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
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewVenta::route('/{record}'),
        ];
    }    
}
