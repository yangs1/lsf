<?php

namespace Foundation\Transformer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\support\Collection;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Exception;

class TransformerEngine implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The data to transform.
     *
     * @var Model|Collection
     */
    private $data;

    /**
     * The transformer to transform.
     * @var AbstractTransformer
     */
    private $transformer;

    /**
     * The transformer group currently in use.
     *
     * @var string
     */
    private $group = 'default';

    /**
     * Create an instance of the transformer engine.
     *
     * TransformerEngine constructor.
     * @param $data
     * @param null $transformer
     * @param null $group
     * @throws Exception
     */
    public function __construct($data, $transformer = null, $group = null)
    {
        $group && $this->setGroup( $group );

        if (! is_a($data, Model::class) && ! is_a($data, Collection::class)) {
            throw new Exception('Only Eloquent models and collections are supported by the transformer.');
        }

        if (is_null($transformer)) {

            if ( ! $transformer = config('transformer.groups.'.$this->group.".".get_class($data)) ) {
                throw new Exception('A default transformer has not be supplied for ' . get_class($data) . '.');
            }
        }
        if ( is_string( $transformer ) && class_exists( $transformer )){
            $transformer = new $transformer();
        }
        if (! is_a($transformer, AbstractTransformer::class)) {
            throw new Exception('The supplied transformer is not supported by the transformer engine.');
        }

        $this->data        = $data;
        $this->transformer = $transformer;
    }

    /**
     * Transform the data into a JSON string.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the data into a JSON serializable structure.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Transform the data into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->run();
    }

    /**
     * This will run the transformer over the data.
     *
     * Situations where calling this method directly might be useful
     * include using a transform within another transformer.
     *
     * @return array
     */
    public function run()
    {
        if (is_a($this->data, Collection::class)) {
            return $this->data->map(function ($model) {
                return $this->transformer->transformer($model);
            })->toArray();
        }

        return $this->transformer->transformer($this->data);
    }

    /**
     * Set the group of transformers to use when attempting to
     * automatically resolve a transformer.
     *
     * @param $group
     * @return $this
     * @throws Exception
     */
    public function setGroup($group)
    {
        if (config('transformer.groups.'.$group)) {

            $this->group = $group;

            return $this;
        }
        throw new Exception('not find the group in this config file.');
    }
}
