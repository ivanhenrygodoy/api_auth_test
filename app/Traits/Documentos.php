<?php

namespace App\Traits;

use App\Http\Responses\ApiResponse;

trait Documentos
{
    /**
     * @param $path
     * @return bool
     */
    public function existsPathDocument($path): bool
    {
        if (!file_exists($path))
            mkdir($path, 0777, true);

        return true;
    }

    public function validID($number): bool
    {
        $regex = '/^[0-9]+$/';
        return !!preg_match($regex, $number);
    }
}