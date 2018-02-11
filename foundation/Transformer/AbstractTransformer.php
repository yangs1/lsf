<?php

namespace Foundation\Transformer;

use Illuminate\Support\Collection;

abstract class AbstractTransformer
{
    /**
     * Transform the supplied data.
     *
     * @param Collection $model | array
     * @return array
     */
    abstract public function transformer($model);
}
