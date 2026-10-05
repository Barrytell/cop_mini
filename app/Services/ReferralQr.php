<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class ReferralQr
{
    public function svg(string $contents): string
    {
        $options = new QROptions([
            'outputBase64' => false,
            'scale' => 6,
            'quietzoneSize' => 2,
            'drawLightModules' => true,
        ]);
        $options->outputType = QRCode::OUTPUT_MARKUP_SVG;

        return (new QRCode($options))->render($contents);
    }
}
