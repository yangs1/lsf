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

    /**
     * Contains a list of what to also include in the output.
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Setter for includes.
     *
     * @param array $includes
     *
     * @return $this
     */
    public function setIncludes(array $includes)
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * Getter for includes.
     *
     * @return array
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * Determine whether or not the include has been 'included'.
     *
     * @param $include
     * @return bool
     */
    public function hasInclude($include)
    {
        return in_array($include, $this->includes);
    }
}
