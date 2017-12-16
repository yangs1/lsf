<?php

namespace App\Http\Requests;

use Foundation\Concerns\FromRequests;

class UpdateUserRequest extends FromRequests
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
