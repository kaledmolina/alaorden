<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class ListVentas extends ListRecords
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vendedor.nombre')
                    ->label('Vendedor')
                    ->searchable(),

                TextColumn::make('orden.numero_orden')
                    ->label('Número de Orden')
                    ->searchable(),

                TextColumn::make('total_precio')
                    ->label('Total Venta')
                    ->money('COP')
                    ->sortable(),

                TextColumn::make('profit_vendedor')
                    ->label('Comisión Vendedor')
                    ->money('COP')
                    ->sortable(),

                TextColumn::make('pending_value')
                    ->label('Valor Pendiente')
                    ->money('COP')
                    ->sortable()
                    ->color(fn($record) => $record->pending_value > 0 ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label('Fecha de Venta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('vendedor')
                    ->relationship('vendedor', 'nombre')
                    ->label('Filtrar por Vendedor'),

                SelectFilter::make('orden')
                    ->relationship('orden', 'numero_orden')
                    ->label('Filtrar por Orden'),

                Filter::make('ordenes_pendientes')
                    ->label('Órdenes Pendientes')
                    ->query(fn($query) => $query->where('pending_value', '>', 0))
                    ->indicator('Pendientes'), // Muestra un indicador cuando el filtro está activo
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\ViewAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
