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




class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('nombre')
                ->label('Categoría General')
                ->required()
                ->placeholder('Ingrese el nombre de la categoría')
                ->helperText('Este campo es obligatorio.')
                ->columnSpan(2), // Se ajusta el tamaño en el formulario, útil para hacer columnas más grandes
            ToggleButtons::make('is_visible')
                ->label('visible?')
                ->default(true)
                ->boolean()
                ->grouped()
                ->helperText('Indique si esta categoría es visible para los usuarios'), 
            ToggleButtons::make('is_active')
                ->label('activo?')
                ->default(true)
                ->boolean() // Valor predeterminado en 'true'
                ->grouped()
                ->helperText('Indique si esta categoría está activa en el sistema'),

            MarkdownEditor::make('descripcion')
                ->label('Descripción')
                ->placeholder('Ingrese una descripción detallada...')
                ->helperText('Esta descripción será visible en la interfaz del usuario.')
                ->columnSpan(2), // Similar al 'TextInput', se usa para más espacio
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
            'index' => Pages\ManageCategorias::route('/'),
        ];
    }
}
