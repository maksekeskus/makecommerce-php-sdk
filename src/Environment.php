<?php

declare(strict_types=1);

namespace MakeCommerce;

class Environment
{
    public const API_VERSION = '/v1/';
    public const TEST_BASEURI = 'https://api.test.maksekeskus.ee';
    public const LIVE_BASEURI = 'https://api.maksekeskus.ee';

    // Public test credentials — safe to use for development and testing
    public const TEST_SHOP_ID         = '3425d8b7-0225-4367-8c6f-16b1aba8d766';
    public const TEST_SECRET_KEY      = 'J5S4lcVjC1QfJec8IQPhHSKeAiEf10bPV7KrHPx9AmIl9nCoEtNtJo63SF0YKpFQ';
    public const TEST_PUBLISHABLE_KEY = '79p15UvwBLlZfqmoMY8D8LAjq4CwI8Tn';
}
