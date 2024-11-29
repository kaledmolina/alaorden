<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\MarkdownEditor;




class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_id')
                    ->label('Categoría')
                    ->options(Categoria::where('is_active', true)->pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\Grid::make(2) // Configura un grid con 2 columnas
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Categoría General')
                                    ->required()
                                    ->placeholder('Ingrese el nombre de la categoría')
                                    ->helperText('Este campo es obligatorio.')
                                    ->columnSpan('full'), // Ocupa una fila completa en pantallas pequeñas
                                ToggleButtons::make('is_visible')
                                    ->label('¿Visible?')
                                    ->default(true)
                                    ->boolean()
                                    ->grouped()
                                    ->helperText('Indique si esta categoría es visible para los usuarios'),
                                ToggleButtons::make('is_active')
                                    ->label('¿Activo?')
                                    ->default(true)
                                    ->boolean()
                                    ->grouped()
                                    ->helperText('Indique si esta categoría está activa en el sistema'),
                                Forms\Components\MarkdownEditor::make('descripcion')
                                    ->label('Descripción')
                                    ->placeholder('Ingrese una descripción detallada...')
                                    ->helperText('Esta descripción será visible en la interfaz del usuario.')
                                    ->columnSpan('full'), // Ocupa toda la fila si hay poco espacio
                            ])
                    ])
                    ->createOptionUsing(function (array $data) {
                        $categoria = Categoria::create([
                            'nombre' => $data['nombre'],
                            'is_visible' => $data['is_visible'],
                            'is_active' => $data['is_active'],
                            'descripcion' => $data['descripcion'],
                        ]);
                        
                        return $categoria->id;
                    }),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'pendiente' => 'Pendiente',
                    ])
                    ->default('activo')
                    ->required(),
                Forms\Components\TextInput::make('nombre')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('bar_code')
                    ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('referencia')
                    ->label('Referencia')
                    ->default(function () {
                        $lastReference = \App\Models\Producto::latest('id')->value('referencia');
                
                        if ($lastReference) {
                            // Usar una expresión regular para separar el prefijo y la parte numérica
                            preg_match('/^(.*?)(\d*)$/', $lastReference, $matches);
                            $prefix = $matches[1] ?? ''; // Parte alfabética
                            $number = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0; // Parte numérica
                
                            // Incrementar el número y devolver la nueva referencia
                            return $prefix . ($number + 1);
                        }
                
                        // Si no hay referencias previas, asignar un valor inicial
                        return 'FA1';
                    })
                    ->required()
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('precio_compra')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('precio_venta')
                    ->numeric()
                    ->required(),
                MarkdownEditor::make('descripcion')
                    ->label('Descripción')
                    ->placeholder('Ingrese una descripción detallada...')
                    ->helperText('Esta descripción será visible en la interfaz del usuario.')
                    ->default('Aun no hay descripcion')
                    ->required()
                    ->columnSpanFull(),
                ToggleButtons::make('is_visible')
                    ->label('visible?')
                    ->default(true)
                    ->boolean()
                    ->grouped()
                    ->helperText('Indique si este producto es visible para los usuarios'), 
                ToggleButtons::make('is_activo')
                    ->label('activo?')
                    ->default(true)
                    ->boolean() // Valor predeterminado en 'true'
                    ->grouped()
                    ->helperText('Indique si este producto está activo en el sistema'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bar_code')
                    ->label('Código de barras')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('precio_compra')
                    ->label('Precio de compra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_venta')
                    ->label('Precio de venta')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()                    
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_activo')
                    ->label('Activo')
                    ->boolean()                    
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
