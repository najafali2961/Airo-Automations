<?php

namespace App\Filament\Resources\ConnectorResource\Pages;

use App\Filament\Resources\ConnectorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConnectors extends ListRecords
{
    protected static string $resource = ConnectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
