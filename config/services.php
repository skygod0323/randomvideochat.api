<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
        'client_id' => '138588286763688',
        'client_secret' => '6490fe32f9418446786411a66941e519',
        'redirect' => env('APP_URL').'/api/login/facebook/callback',
    ],

    'google' => [
        'client_id' => '555656944393-og0bnd1ppshgbc5fv3icqt5s95gn4ikh.apps.googleusercontent.com',
        'client_secret' => 't6K8i_tJEuTrfLtqayXdmBjL',
        'redirect' => env('APP_URL').'/api/login/google/callback',
    ],

    'paypal' => [
        'client_id' => 'AYSEcGNHsQ2reZChT7Wxg9-AiAdkPbVywEndCMJEMBZBT7mWt6WeZSCIXx6-y6w9dekbgDiDoYwRnObP',
        'secret' => 'ENbCYOmdoet1_UU2kTLyR4e_FS9blqeD6t8MzTgSxsn1jadcNzk4UX1M-lYoEX4Ui6z3Wby7z_nAtEMA'
    ],

//    'paypal' => [
//        'client_id' => 'ATOh0f116rlKR8Ok6qX_oHWc-Nx2QX5qyU9eFkk16ib5lxOjB66f18COolkELGk5hTWhlMBCUH2b0S4q',
//        'secret' => 'EJAoKBiUGLAyY2WhYhVlsD_M5MoXBLgeKdFjH5hDO8eFF9ZY4OoiBmqM177YqpMIB23yD0IsZdL_8gxk'
//    ],

];
