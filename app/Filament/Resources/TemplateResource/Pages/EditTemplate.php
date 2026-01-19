<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('open_visual_editor')
                ->label('Open Visual Editor')
                ->url(fn () => route('admin.template.editor', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
