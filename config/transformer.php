<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model <-> Transformer Binding Groups
    |--------------------------------------------------------------------------
    |
    | This allows you to specify which transformers should automatically be
    | used when the transform() function is provided a Eloquent model.
    |
    */
    // TransformerEngine::setGroup('v1');
    'groups' => [
        'default' => [
            // App\User::class => App\Transformers\UserTransformer::class,
        ],
        'v1' => [
           // App\Models\User::class                  => App\Transformers\v1\UserTransformer::class,
        ],
        'v2' => [
            //App\Models\User::class                  => App\Transformers\v2\UserTransformer::class,
        ],
    ],

];