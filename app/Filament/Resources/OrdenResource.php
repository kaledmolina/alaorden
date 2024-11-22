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
                    ->columnSpan(2)
                    ->dehydrated(false)
                    ->placeholder('Ingresa nombre o referencia del producto')
                    ->searchable()
                    ->preload()
                    ->options(fn ($get) => self::getProductoOptions($get('producto_seleccionado')))
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Components\Select $component) {
                        if (!$state) return;

                        // Obtiene el producto seleccionado y actualiza la lista
                        $producto = Producto::find($state);
                        if ($producto) {
                            $livewire = $component->getLivewire();
                            $productos = $livewire->data['productos'] ?? [];

                            // Agrega el producto a la lista de productos
                            $productos[] = [
                                'producto_id' => $producto->id,
                                'nombre' => $producto->nombre,
                                'code' => $producto->code,
                                'bar_code' => $producto->bar_code,
                                'referencia' => $producto->referencia,
                                'description' => $producto->description,
                                'precio_venta' => $producto->precio_venta,
                                'cantidad_asignada' => 1,
                            ];

                            // Actualiza los productos en el estado de Livewire
                            $livewire->data['productos'] = $productos;

                            // Limpia la selección después de agregar
                            $component->state(null);
                        }
                    }),

                // Repeater para gestionar productos dentro de la orden
                Forms\Components\Repeater::make('productos')
                ->label('Productos de la orden')
                ->relationship('OrdenProducto')
                ->schema([
                    // Cambiar el diseño para mostrar productos horizontalmente
                    Forms\Components\Grid::make(4)
                        ->schema([
                            // Producto (Select) - Ancho 2 columnas
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(fn () => Producto::all()->pluck('nombre', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    $producto = Producto::find($state);
                                    if ($producto) {
                                        $set('nombre', $producto->nombre);
                                        $set('precio_venta', $producto->precio_venta);
                                        $set('referencia', $producto->referencia);
                                        $set('cantidad_asignada', 1);
                                        $set('code', $producto->code);
                                        $set('bar_code', $producto->bar_code);
                                        $set('description', $producto->description);
                                    }
                                }),

                            Forms\Components\TextInput::make('referencia')
                                ->label('Referencia')
                                ->numeric()
                                ->columnSpan(1),

                            // Cantidad - Ancho 1 columna
                            Forms\Components\TextInput::make('cantidad_asignada')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->columnSpan(1),

                            // Precio de Venta - Ancho 1 columna
                            Forms\Components\TextInput::make('precio_venta')
                                ->label('Precio')
                                ->numeric()
                                ->required()
                                ->columnSpan(1),
                        ]),

                    // Campos ocultos para mantener la funcionalidad
                    Forms\Components\Hidden::make('nombre'),
                    Forms\Components\Hidden::make('code'),
                    Forms\Components\Hidden::make('bar_code'),
                    //Forms\Components\Hidden::make('referencia'),
                    Forms\Components\Hidden::make('description'),
                ])
                ->required()
                ->defaultItems(0)
                ->addActionLabel('Añadir Productos')
                ->columnSpan(2),
                Forms\Components\TextInput::make('total_precio')
                    ->label('Total Precio')
                    ->numeric()
                    ->required()
                    ->default(0)
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
    protected static function getProductoOptions(?string $search = null): array
    {
        return Producto::query()
            ->whereNotNull('nombre')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('referencia', 'like', "%{$search}%");
            })
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
