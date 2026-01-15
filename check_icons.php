<?php
use App\Models\Connector;
foreach (Connector::all() as $c) {
    echo "{$c->slug}: {$c->icon}\n";
}
