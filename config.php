<?php

return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'Sec!ReT423SlmApi',
    'jwt_issuer' => getenv('JWT_ISSUER') ?: 'Projekt-Online-shop-api',
    'jwt_ttl' => (int) (getenv('JWT_TTL') ?: 3600),
];
