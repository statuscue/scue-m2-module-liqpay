<?php

declare(strict_types=1);

namespace Scue\LiqPay\Gateway\Response;

/**
 * Class Decoder.
 */
class Decoder
{
    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string
    {
        return base64_decode($data);
    }
}
