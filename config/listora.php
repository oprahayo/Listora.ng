<?php

return [
    'otp_provider' => env('LISTORA_OTP_PROVIDER', 'log'),
    'invitation_delivery' => env('LISTORA_INVITATION_DELIVERY', 'log'),
    'notification_channels' => ['database'],
    'allow_provisional_listing' => (bool) env('LISTORA_ALLOW_PROVISIONAL_LISTING', false),
];
