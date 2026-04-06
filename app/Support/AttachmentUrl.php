<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class AttachmentUrl
{
    public static function preview(string $disk, string $path, string $name, ?int $ownerId = null): string
    {
        return URL::signedRoute('attachments.preview', [
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
            'owner' => $ownerId,
        ]);
    }

    public static function download(string $disk, string $path, string $name, ?int $ownerId = null): string
    {
        return URL::signedRoute('attachments.download', [
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
            'owner' => $ownerId,
        ]);
    }
}
