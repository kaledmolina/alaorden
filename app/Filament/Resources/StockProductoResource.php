<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockProductoResource\Pages;
use App\Filament\Resources\StockProductoResource\RelationManagers;
use App\Models\StockProducto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Producto;
use Filament\Forms\Components\Hidden;
use Carbon\Carbon;


class StockProductoResource extends Resource
{
    protected static ?string $model = StockProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('producto_id')
                ->label('Producto')
                ->relationship('producto', 'nombre') // Ajusta 'nombre' al campo correcto del modelo Producto
                ->required()
                ->options(Producto::where('is_activo', true)
                    ->doesntHave('stockProducto') // Filtra productos que no tienen stock asociado
                    ->pluck('nombre', 'id'))
                ->searchable(),        

            Forms\Components\TextInput::make('cantidad_inicial')
                ->label('Cantidad Inicial')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('cantidad_actual')
                ->label('Cantidad Actual')
                ->numeric()
                ->disabled(), // Deshabilitado porque se calcula automáticamente,

            Forms\Components\TextInput::make('cantidad_vendida')
                ->label('Cantidad Vendida')
                ->numeric()
                ->disabled(), // Deshabilitado porque se calcula automáticamente.

            Forms\Components\TextInput::make('cantidad_ajuste')
                ->label('Cantidad Ajuste')
                ->default(0)
                ->numeric()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $adjustment = $get('cantidad_ajuste');
                    if ($adjustment > 0) {
                        Notification::make()
                            ->title('Cantidad ajustada')
                            ->body("Se ha añadido {$adjustment} a la cantidad actual.")
                            ->success()
                            ->send();
                    }
                }),

            Forms\Components\TextInput::make('cantidad_advertencia')
                ->label('Advertencia')
                ->numeric()
                ->required(), // Se actualiza automáticamente en el modelo.
            Hidden::make('fecha_ultimo_ajuste')
            ->default(Carbon::now())  
                
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto.nombre')
                ->label('Producto')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('cantidad_inicial')
                ->label('Cantidad Inicial')
                ->sortable(),

            Tables\Columns\TextColumn::make('cantidad_actual')
                ->label('Cantidad Actual')
                ->sortable(),

            Tables\Columns\TextColumn::make('cantidad_vendida')
                ->label('Cantidad Vendida')
                ->sortable(),
            Tables\Columns\TextColumn::make('cantidad_advertencia')
                ->label('Advertencia')
                ->sortable(),    

            Tables\Columns\TextColumn::make('fecha_ultimo_ajuste')
                ->label('Fecha Último Ajuste')
                ->dateTime()
                ->sortable(),
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
    public static function getNavigationBadge(): ?string
    {
        // Contar los productos donde la cantidad actual es menor o igual al valor de advertencia
        return static::getModel()::whereColumn('cantidad_actual', '<=', 'cantidad_advertencia')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Cambiar el color del badge si hay productos que cumplen la condición
        return static::getModel()::whereColumn('cantidad_actual', '<=', 'cantidad_advertencia')->count() > 0 
            ? 'danger' // Cambiado a 'danger' para el color rojo
            : 'primary';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStockProductos::route('/'),
        ];
    }
}
