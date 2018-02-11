<?php

namespace App\Http\Requests;

use Foundation\Http\ValidateRequests;

class UpdateUserRequest extends ValidateRequests
{
    public function rules()
    {
        return [
            'account' => 'required|string|max:20|unique:users',
        ];
    }

    public function sceneRules()
    {
        return [
            'A'=> [
                'name' => 'required|string|max:20',
            ],
            'B'=> [
                'password' => 'required|string|min:6|confirmed',
            ]
        ];
    }

}
