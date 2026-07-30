<?php

return [
    'whatsapp_number' => env('WHATSAPP_NUMBER', '6281234567890'),
    'contact_phone' => env('CONTACT_PHONE', '+62 812 3456 7890'),
    'contact_email' => env('CONTACT_EMAIL', 'halo@ciptaoffice.com'),
    'contact_address' => [
        env('CONTACT_ADDRESS_LINE_1', 'Ruko Broadway No. 17'),
        env('CONTACT_ADDRESS_LINE_2', 'Galuh Mas, Karawang'),
    ],
    'notification_email' => env('CONTACT_NOTIFICATION_EMAIL'),
];
