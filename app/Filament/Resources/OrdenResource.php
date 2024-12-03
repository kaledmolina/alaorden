<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Models\Orden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Vendedor;
use App\Models\Producto;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use App\Models\StockProducto;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class OrdenResource extends Resource
{
    // Especificamos el modelo asociado a este recurso
    protected static ?string $model = Orden::class;
    protected static ?int $navigationSort = 4;

    // Icono para la navegación en Filament
    protected static ?string $navigationIcon = 'heroicon-m-clipboard-document-list';

    // Nombre en plural para el modelo
    protected static ?string $pluralModelLabel = 'Órdenes';

    /**
     * Definición del formulario para crear y editar órdenes
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para el número de orden
                Forms\Components\TextInput::make('numero_orden')
                ->label('Número de Orden')
                ->required()
                ->readonly()
                ->default(function () {
                    do {
                        $numero_orden = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10));
                    } while (Orden::where('numero_orden', $numero_orden)->exists());
                    return $numero_orden;
                })
                ->unique(table: 'ordens', column: 'numero_orden', ignoreRecord: true), 

                // Select para elegir el vendedor
                Forms\Components\Select::make('vendedor_id')
                    ->label('Vendedor')
                    ->options(fn () => Vendedor::where('is_activo', true)
                        ->selectRaw('id, CONCAT(nombre, " ", apellido) as full_name')
                        ->pluck('full_name', 'id'))
                    ->searchable()
                    ->required(),

                // Select para elegir un producto fuera del Repeater
                Forms\Components\Select::make('producto_seleccionado')
                    ->label('Seleccionar Producto')
                    ->searchable()
                    ->placeholder('Busca un producto...')
                    ->options(fn () => self::getProductoOptions())
                    ->reactive()
                    ->columnSpan(2)
                    ->afterStateUpdated(function ($state, Forms\Components\Select $component) {
                        if (!$state) return;

                        $producto = Producto::find($state);
                        if ($producto) {
                            $livewire = $component->getLivewire();
                            $productos = $livewire->data['productos'] ?? [];

                            // Evita duplicados
                            if (collect($productos)->pluck('producto_id')->contains($producto->id)) {
                                Notification::make()
                                    ->title('Producto Duplicado')
                                    ->body("El producto '{$producto->nombre}' ya está en la lista.")
                                    ->warning()
                                    ->send();
                                $component->state(null);
                                return;
                            }

                            // Verifica el stock
                            $stockDisponible = StockProducto::where('producto_id', $producto->id)->value('cantidad_actual') ?? 0;
                            if ($stockDisponible <= 0) {
                                Notification::make()
                                    ->title('Stock Insuficiente')
                                    ->body("El producto '{$producto->nombre}' no tiene stock disponible.")
                                    ->warning()
                                    ->send();
                                $component->state(null);
                                return;
                            }

                            // Agrega el producto
                            $productos[] = [
                                'producto_id' => $producto->id,
                                'nombre' => $producto->nombre,
                                'code' => $producto->code,
                                'bar_code' => $producto->bar_code,
                                'referencia' => $producto->referencia,
                                'description' => $producto->description,
                                'precio_venta' => $producto->precio_venta,
                                'cantidad_asignada' => 1, // Establecer cantidad inicial
                                'comision' => $producto->comision,
                            ];

                            $livewire->data['productos'] = $productos;
                            $component->state(null); // Resetea el select
                        }
                    }),


                    Forms\Components\Repeater::make('productos')
                    ->label('Productos de la orden')
                    ->relationship('OrdenProducto')
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
                                            $set('cantidad_asignada', 0);
                                            $set('code', $producto->code);
                                            $set('bar_code', $producto->bar_code);
                                            $set('comision', $producto->comision);
                                            $set('description', $producto->description);
                                        }
                                    }),
                
                                Forms\Components\TextInput::make('referencia')
                                    ->label('Referencia')
                                    ->disabled()
                                    ->columnSpan(1),
                
                                    Forms\Components\TextInput::make('cantidad_asignada')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $productoId = $get('producto_id');
                                        $stockProducto = StockProducto::where('producto_id', $productoId)->first();
                                        
                                        // Convertir el estado a un número, por defecto 0 si no es un número válido
                                        $cantidad = is_numeric($state) ? (float)$state : 0;
                                        
                                        // Validar stock
                                        if ($stockProducto && $cantidad > $stockProducto->cantidad_actual) {
                                            Notification::make()
                                                ->title('Advertencia')
                                                ->body("La cantidad ingresada excede el stock disponible para este producto.")
                                                ->warning()
                                                ->send();
                                            $set('cantidad_asignada', $stockProducto->cantidad_actual);
                                            $cantidad = $stockProducto->cantidad_actual;
                                        }
                                        
                                        // Calcular el total
                                        $productos = $get('../../productos');
                                        $total = collect($productos)->sum(function ($producto) {
                                            $precio = is_numeric($producto['precio_venta'] ?? 0) ? (float)$producto['precio_venta'] : 0;
                                            $cantidad = is_numeric($producto['cantidad_asignada'] ?? 0) ? (float)$producto['cantidad_asignada'] : 0;
                                            return $precio * $cantidad;
                                        });
                                        $set('../../total_precio', $total);
                                    }),
                                
                                Forms\Components\TextInput::make('precio_venta')
                                    ->label('Precio')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $productos = $get('../../productos');
                                        $total = collect($productos)->sum(function ($producto) {
                                            $precio = is_numeric($producto['precio_venta'] ?? 0) ? (float)$producto['precio_venta'] : 0;
                                            $cantidad = is_numeric($producto['cantidad_asignada'] ?? 0) ? (float)$producto['cantidad_asignada'] : 0;
                                            return $precio * $cantidad;
                                        });
                                        $set('../../total_precio', $total);
                                    }),
                            ]),
                
                        Forms\Components\Hidden::make('producto_id'),
                        Forms\Components\Hidden::make('comision'),
                        Forms\Components\Hidden::make('nombre'),
                        Forms\Components\Hidden::make('code'),
                        Forms\Components\Hidden::make('bar_code'),
                        Forms\Components\Hidden::make('referencia'),
                        Forms\Components\Hidden::make('description'),
                    ])
                    ->defaultItems(0)
                    ->addable(false)
                    ->columnSpan(2),
            ]);
    }

    /**
     * Definición de la tabla para mostrar las órdenes
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden')
                    ->label('Número de Orden')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('vendedor.nombre')
                    ->label('Vendedor')
                    ->getStateUsing(fn ($record) => $record->vendedor->nombre . ' ' . $record->vendedor->apellido)
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->limit(30),
                    Tables\Columns\TextColumn::make('vendedor.telefono')
                    ->label('celular')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                    Tables\Columns\TextColumn::make('productos_count')
                    ->label('Cantidad de Productos')
                    ->getStateUsing(fn ($record) => $record->OrdenProducto()->sum('cantidad_asignada'))
                    ->sortable()
                
            ])
            ->filters([])
            ->actions([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Método para obtener las opciones de productos según búsqueda
     */
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

    public static function getRelations(): array
    {
        return [];
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
