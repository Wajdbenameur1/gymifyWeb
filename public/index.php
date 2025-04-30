<?php

putenv('TWILIO_ACCOUNT_SID=AC1e2bc45ae066e10085b99aaf905f8176');
putenv('TWILIO_AUTH_TOKEN=4e27c1647af0af5780557b26347bea7d');
putenv('TWILIO_PHONE_NUMBER=+17755425298');

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
