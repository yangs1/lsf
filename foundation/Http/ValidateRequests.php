<?php

namespace Foundation\Http;

use Illuminate\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ValidateRequests
{

    protected $scene;
    /**
     * Prepare the data for validation.
     * @param $scene
     * @return self
     */
    public function scene( $scene )
    {
        $this->scene = $scene;

        return $this;
    }

    /**
     * Get the validator instance for the request.
     * @param array $data
     * @param $rules
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidatorInstance( array $data, $rules )
    {
        $factory = app( Factory::class );

        $instance =  $factory->make(
            $data, $rules, $this->messages(), $this->attributes()
        );

        if (method_exists($this, 'validator')) {
            return app()->call([$this, 'validator'], compact('instance', 'rules'));
        }

        return $instance;
    }

    /**
     * Validate the class instance.
     *
     * @param array $data
     * @throws ValidationException
     */
    public function validate( array $data )
    {
        $this->prepareForValidation( $data );

        $rules = $this->scene ?
            array_merge($this->rules(), $this->sceneRules()[$this->scene] ?? []) : $this->rules();

        $instance = $this->getValidatorInstance( $data, $rules );

        if (! $instance->passes()) {
            $this->failedValidation($instance);
        }
    }

    /**
     * Handle a failed validation attempt.
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {   throw new ValidationException($validator);
        //throw new HttpResponseException( new JsonResponse( $this->formatErrors($validator), 422));
    }
    /**
     * Format the errors from the given Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        return $validator->getMessageBag()->toArray();
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

    public function sceneRules()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }

    public function prepareForValidation( array $data )
    {

    }
}
