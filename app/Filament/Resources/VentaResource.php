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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\MarkdownEditor;


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
                            $precioVenta = $ordenProducto->precio_venta;
                            $cantidadVendida = $ordenProducto->cantidad_asignada;
                            
                            $profitunitario = $ordenProducto->comision ?? 0;
                            $comision = $precioVenta > 0 ? round(($profitunitario / $precioVenta) * 100, 2) : 0;
                        
                            // Calculate subtotal
                            $subtotal = $precioVenta * $cantidadVendida;
                            
                            // Calculate unit profit
                            $profitUnitario = $subtotal * ($comision / 100);
                        
                            // Calculate total product profit
                            $gananciaTotalProducto = $profitUnitario;
                        
                            return [
                                'producto_id' => $ordenProducto->producto_id,
                                'nombre' => $ordenProducto->nombre,
                                'code' => $ordenProducto->code,
                                'bar_code' => $ordenProducto->bar_code . '_' . uniqid(),
                                'referencia' => $ordenProducto->referencia,
                                'description' => $ordenProducto->description,
                                'precio_venta' => $precioVenta,
                                'cantidad_vendida' => $cantidadVendida, 
                                'comision' => $comision,
                                'subtotal' => round($subtotal, 2),
                                'profitunitario' => $ordenProducto->comision,
                                'ganancia_total_producto' => round($gananciaTotalProducto, 2)
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
                        self::recalculateTotales(
                            fn($key) => $productos, 
                            fn($key, $value) => $set($key, $value)
                        );
                        
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
                            $component->state(null);
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
                            $component->state(null);
                            return;
                        }
                
                        // Calcular subtotal y ganancia unitaria
                        $cantidadVendida = 1; // Cantidad inicial
                        $precioVenta = $producto->precio_venta;
                        
                        // Calcular comisión y ganancia unitaria
                        $profitunitario = $producto->comision ?? 0;
                        $comision = $precioVenta > 0 ? round(($profitunitario / $precioVenta) * 100, 2) : 0;
                
                        $subtotal = $cantidadVendida * $precioVenta;
                        $gananciaTotalProducto = $profitunitario * $cantidadVendida;
                
                        // Agregar el producto al array de productos
                        $productos[] = [
                            'producto_id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'code' => $producto->code,
                            'bar_code' => $producto->bar_code,
                            'referencia' => $producto->referencia,
                            'description' => $producto->description,
                            'precio_venta' => $precioVenta,
                            'cantidad_vendida' => $cantidadVendida,
                            'comision' => $comision,
                            'subtotal' => round($subtotal, 2),
                            'profitunitario' => $profitunitario,
                            'ganancia_total_producto' => round($gananciaTotalProducto, 2)
                        ];
                        $livewire->data['Productos'] = $productos; 
                        self::recalculateTotales(
                            fn($key) => $livewire->data[$key] ?? null,
                            fn($key, $value) => $livewire->data[$key] = $value
                        );
                        $component->state(null);
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
                                        $set('profitunitario', $producto->comision);
                                        $set('description', $producto->description);
                                    }
                                }),


                                Forms\Components\TextInput::make('cantidad_vendida')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->maxValue(function (callable $get) {
                                        $productoId = $get('producto_id');
                                        $ordenId = $get('../../orden_id');
                                        
                                        // Buscar la cantidad asignada originalmente en la orden
                                        $ordenProducto = OrdenProducto::where('producto_id', $productoId)
                                            ->where('orden_id', $ordenId)
                                            ->first();
                                        
                                        // Si existe la orden, limitar al valor original asignado
                                        return $ordenProducto ? $ordenProducto->cantidad_asignada : null;
                                    })
                                    ->afterStateUpdated(function (Forms\Set $set, $state, callable $get) {
                                        
                                        $cantidad = floatval($state);
                                        $precioVenta = floatval($get('precio_venta') ?? 0);
                                        $profitunitario = floatval($get('profitunitario') ?? 0);
                                
                                        // Calcular subtotal
                                        $subtotal = $state * $precioVenta;
                                        $set('subtotal', round($subtotal, 2));
                                
                                        // Mantener el valor existente de ganancia unitaria
                                        $gananciaTotalProducto = $profitunitario * $state;
                                        $set('ganancia_total_producto', round($gananciaTotalProducto, 2));
                                
                                        // Recalcular totales 
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
                                    $cantidad = floatval($get('cantidad_vendida') ?? 0);
                                    $precioVenta = floatval($get('precio_venta') ?? 0);
                                    $comision = floatval($get('comision') ?? 0);
                            
                                    // Calcular subtotal
                                    $subtotal = $cantidad * $precioVenta;
                                    $set('subtotal', $subtotal);
                            
                                     // Recalcular totales después de actualizar la cantidad corregida
                                    self::recalculateTotales(
                                        function($key) use ($get) { return $get($key); }, 
                                        $set
                                    );
                                }),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->columnSpan(1),

                                Forms\Components\TextInput::make('comision')
                                ->label('Comisión (%)')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->columnSpan(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $cantidad = $get('cantidad_vendida') ?? 0;
                                    $precioVenta = $get('precio_venta') ?? 0;
                                    $comision = $get('comision') ?? 0;
                                    
                                    // Calcular ganancia UNITARIA (por cada unidad)
                                    $gananciaUnitaria = $precioVenta * ($comision / 100);
                                    
                                    // Establecer el valor de ganancia unitaria con un formato de dos decimales
                                    $set('profitunitario', round($gananciaUnitaria, 2));
                                
                                    // Calcular ganancia total del producto
                                    $gananciaTotalProducto = $gananciaUnitaria * $cantidad;
                                    $set('ganancia_total_producto', round($gananciaTotalProducto, 2));
                                
                                    // Recalcular totales del formulario
                                    self::recalculateTotales($get, $set);
                                }),
                    
                                Forms\Components\TextInput::make('profitunitario')
                                ->label('Ganancia Unitaria')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    // Convertir valores a números flotantes, usando 0 si están vacíos o no son numéricos
                                    $cantidad = floatval($get('cantidad_vendida') ?? 0);
                                    $precioVenta = floatval($get('precio_venta') ?? 0);
                                    $profitunitario = floatval($get('profitunitario') ?? 0);
                                    
                                    // Calcular porcentaje de comisión por unidad
                                    if ($precioVenta > 0) {
                                        // Calcular el porcentaje de comisión basado en el valor de ganancia unitaria
                                        $comisionPorcentaje = $precioVenta > 0 ? ($profitunitario / $precioVenta) * 100 : 0;
                                        
                                        $set('comision', round($comisionPorcentaje, 2));
                                    }
                                
                                    // Calcular ganancia total del producto
                                    $gananciaTotalProducto = $profitunitario * $cantidad;
                                    $set('ganancia_total_producto', round($gananciaTotalProducto, 2));
                                
                                    // Recalcular totales del formulario
                                    self::recalculateTotales($get, $set);
                                }),

                            Forms\Components\TextInput::make('ganancia_total_producto')
                                ->label('Ganancia Total de la venta')
                                ->numeric()
                                ->disabled()
                                ->columnSpan(1),

                        ]),
                        Forms\Components\Hidden::make('comision'),
                        Forms\Components\Hidden::make('producto_id'),
                        Forms\Components\Hidden::make('nombre'),
                        Forms\Components\Hidden::make('code'),
                        Forms\Components\Hidden::make('bar_code'),
                        Forms\Components\Hidden::make('referencia'),
                        Forms\Components\Hidden::make('description'),
                        Forms\Components\Hidden::make('subtotal'),
                ])
                ->afterStateUpdated(function (callable $set, callable $get) {
                    self::recalculateTotales($get, $set);
                })
                ->defaultItems(0)
                ->addable(false)
                ->columnSpan(2),
            MarkdownEditor::make('descripcion')
                ->label('Descripción')
                ->placeholder('Ingrese nombre y precio detallado...')
                ->helperText('Ingrese nombre completo en caso de creditos y el valor ')
                ->columnSpanFull(),    

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
 
            Section::make('Pago Efectivo')
            ->schema([
                Forms\Components\TextInput::make('paid_amount')
                ->label('Valor pagado')
                ->required()
                ->numeric()
                ->translateLabel()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                    $totalPrice = $get('total_precio') ?? 0;
                    $paidAmount = $get('paid_amount') ?? 0;
            
                    // Calculate change
                    $change = $paidAmount > $totalPrice ? $paidAmount - $totalPrice : 0;
                    $set('change_value', round($change, 2));
            
                    // Calculate pending amount
                    $pending = $paidAmount < $totalPrice ? $totalPrice - $paidAmount : 0;
                    $set('pending_value', round($pending, 2));
                }),
            
                Forms\Components\Placeholder::make('change_value_display')
                ->label('Valor de cambio')
                ->translateLabel()
                ->content(fn(Forms\Get $get) => $get('change_value') ?? 0),
            
                Forms\Components\Placeholder::make('pending_value_display')
                ->label('Valor pendiente')
                ->translateLabel()
                ->content(fn(Forms\Get $get) => $get('pending_value') ?? 0),
            
                Forms\Components\Hidden::make('change_value'),
                Forms\Components\Hidden::make('pending_value'), 
            ])     
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
            $cantidad = floatval($producto['cantidad_vendida'] ?? 0);
            $precioVenta = floatval($producto['precio_venta'] ?? 0);
            $gananciaUnitaria = floatval($producto['profitunitario'] ?? 0);
    
            $subtotal = $cantidad * $precioVenta;
            $gananciaTotalProducto = $gananciaUnitaria * $cantidad;
    
            $totalPrecio += $subtotal;
            $gananciaVendedor += $gananciaTotalProducto;
    
            // Update the total product profit in the repeater
            $set("Productos.{$index}.ganancia_total_producto", round($gananciaTotalProducto, 2));
        }
    
        // Actualiza los campos totales
        $set('total_precio', round($totalPrecio, 2));
        $set('profit_vendedor', round($gananciaVendedor, 2));
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
