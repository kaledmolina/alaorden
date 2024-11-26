<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource\RelationManagers;
use App\Models\Categoria;
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





class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;

    protected static ?string $navigationIcon = 'heroicon-s-folder';
    protected static ?int $navigationSort = 0;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('is_visible')
                    ->label('Visible ?')
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Activo ?')
                    ->sortable(),    
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_visible')
                    ->label('Categorias visibles')
                    ->options([
                        '1' => 'Visible',
                        '0' => 'No Visible',
                    ]),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Categorias activas')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'No Activo',
                    ]),   

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Categoria $record) {
                        if ($record->productos()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('No se puede eliminar la categoría')
                                ->body('Esta categoría tiene productos asociados.')
                                ->send();
                                
                            return;
                        }
                        
                        $record->delete();

                        // Notificación de éxito al borrar
                        Notification::make()
                            ->success()
                            ->title('Categoría eliminada')
                            ->body('La categoría fue eliminada exitosamente.')
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            // Filtramos las categorías que no tienen productos asociados
                            $categoriesWithoutProducts = $records->filter(function ($record) {
                                return !$record->productos()->exists();
                            });
            
                            // Verificamos si hay alguna categoría sin productos asociados para borrar
                            if ($categoriesWithoutProducts->isNotEmpty()) {
                                // Eliminamos solo las categorías sin productos asociados
                                $categoriesWithoutProducts->each->delete();
            
                                Notification::make()
                                    ->success()
                                    ->title('Categorías eliminadas')
                                    ->body($categoriesWithoutProducts->count() . ' categoría(s) eliminada(s) exitosamente.')
                                    ->send();
                            }
            
                            // Si existen categorías con productos asociados, mostramos una notificación de advertencia
                            if ($categoriesWithoutProducts->count() < $records->count()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Algunas categorías no se pueden eliminar')
                                    ->body('Algunas categorías tienen productos asociados y no se eliminaron.')
                                    ->send();
                            }
                        }),
                ]),
            ]);
            
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategorias::route('/'),
        ];
    }
}
