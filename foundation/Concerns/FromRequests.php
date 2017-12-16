<?php

namespace Foundation\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidatesWhenResolvedTrait;

class FromRequests //extends Request
{
    use ValidatesWhenResolvedTrait;

    protected $data;

    public function __construct(  array $data = [] )
    {
        $this->data = $data;
    }

    /**
     * Get the validator instance for the request.
     *
     * @param $data
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidatorInstance( array $data = [] )
    {
        $factory = app( Factory::class );

        if (method_exists($this, 'validator')) {
            return app()->call([$this, 'validator'], compact('factory','data'));
        }

        if (empty($data)){
            $data = $this->data;
        };
        return $factory->make(
            $data, app()->call([$this, 'rules']), $this->messages(), $this->attributes()
        );
    }

    /**
     * Validate the class instance.
     * @param $data
     * @return void
     */
    protected function validate(array $data = [])
    {
        $this->prepareForValidation();

        $instance = $this->getValidatorInstance($data);

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        } elseif (! $instance->passes()) {
            $this->failedValidation($instance);
        }
    }


    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->response(
            $this->formatErrors($validator)
        ));
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        return new JsonResponse($errors, 422);
        /*return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);*/
    }


    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Set custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    public function all()
    {
        return $this->data;
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
