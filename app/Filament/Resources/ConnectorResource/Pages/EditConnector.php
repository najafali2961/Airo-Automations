<?php

namespace App\Filament\Resources\ConnectorResource\Pages;

use App\Filament\Resources\ConnectorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConnector extends EditRecord
{
    protected static string $resource = ConnectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
