<?php

namespace App\Http\Requests;

class UpdateUserRequest extends Request
{
    public function rules()
    {
        return [
            'github_id'       => 'unique:users',
            'github_name'     => 'string',
            'wechat_openid'   => 'string',
            'email'           => 'email|unique:users,email,',
            'github_url'      => 'url',
            'image_url'       => 'url',
            'wechat_unionid'  => 'string',
            'linkedin'        => 'url',
            'weibo_link'      => 'url',
            'payment_qrcode'  => 'image',
            'wechat_qrcode'  => 'image',
        ];
    }


}
