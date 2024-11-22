<?php

namespace App\Filament\Resources\StockProductoResource\Pages;

use App\Filament\Resources\StockProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStockProductos extends ManageRecords
{
    protected static string $resource = StockProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
