<?php

declare(strict_types=1);

namespace Scue\LiqPay\Gateway\Request;

/**
 * Class Encoder
 */
class Encoder
{
    /**
     * @param string $data
     * @return string
     */
    public function encode(string $data): string
    {
        return base64_encode($data);
    }
}
