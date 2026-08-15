<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeService
{
    public function makeToken(string $prefix = 'IC'): string
    {
        return strtoupper($prefix).'-'.Str::upper(Str::random(10));
    }

    public function pngDataUri(string $payload): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($payload)
            ->size(280)
            ->margin(10)
            ->build();

        return $result->getDataUri();
    }

    public function storePng(string $path, string $payload, string $disk = 'local'): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($payload)
            ->size(320)
            ->margin(10)
            ->build();

        Storage::disk($disk)->put($path, $result->getString());

        return $path;
    }
}
