<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use App\Models\Venta;
use App\Models\Orden;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\HtmlString;

class ViewVenta extends ViewRecord
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Group::make()
                ->columnSpan(2)
                ->schema([
                    Section::make('Información de la Venta')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('vendedor')
                                ->label('Vendedor')
                                ->icon('heroicon-s-user')
                                ->formatStateUsing(fn ($record) => "{$record->vendedor->nombre} {$record->vendedor->apellido}"),

                            TextEntry::make('orden.numero_orden')
                                ->label('Número de Orden')
                                ->icon('heroicon-s-document-text'),

                            TextEntry::make('created_at')
                                ->label('Fecha de Venta')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-s-calendar'),
                        ]),

                    Section::make('Comparación de Productos')
                        ->schema([
                            RepeatableEntry::make('VentaProducto')
                                ->label('Productos de la Venta')
                                ->columns(6)
                                ->schema([
                                    TextEntry::make('nombre')
                                        ->label('Producto')
                                        ->icon('heroicon-s-shopping-bag'),

                                    TextEntry::make('cantidad_vendida')
                                        ->label('Cant. Vendida')
                                        ->formatStateUsing(function ($state, $record) {
                                            $ordenProducto = \App\Models\OrdenProducto::where('orden_id', $record->venta->orden_id)
                                                ->where('producto_id', $record->producto_id)
                                                ->first();
                                    
                                            $cantidadOrden = $ordenProducto ? $ordenProducto->cantidad_asignada : 0;
                                            $entregado = $state + $cantidadOrden;
                                    
                                            return new HtmlString(
                                                "Vendida: <strong>{$state}</strong><br>" .
                                                "Entregado: <strong>{$entregado}</strong>"
                                            );
                                        }),
                                    

                                    TextEntry::make('precio_venta')
                                        ->label('Precio')
                                        ->money('COP')
                                        ->icon('heroicon-s-currency-dollar'),

                                    TextEntry::make('subtotal')
                                        ->label('Subtotal')
                                        ->money('COP')
                                        ->icon('heroicon-s-currency-dollar'),

                                    TextEntry::make('comision')
                                        ->label('Comisión')
                                        ->formatStateUsing(fn($state) => "{$state}%"),
                                    TextEntry::make('profitunitario')
                                        ->label('Ganacia unit.')
                                        ->money('COP'),
                                ]),
                        ]),
                ]),

            Infolists\Components\Group::make()
                ->columnSpan(1)
                ->schema([
                    Section::make('Resumen Financiero')
                        ->schema([
                            TextEntry::make('total_precio')
                                ->label('Total Venta')
                                ->money('COP')
                                ->icon('heroicon-s-banknotes'),

                            TextEntry::make('profit_vendedor')
                                ->label('Comisión Total')
                                ->money('COP')
                                ->icon('heroicon-s-currency-dollar'),
                        ]),

                    Section::make('Información Adicional')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Fecha de Creación')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-s-clock'),

                            TextEntry::make('updated_at')
                                ->label('Última Actualización')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-s-pencil'),
                        ]),
                ]),
        ])->columns(3);
    }
}