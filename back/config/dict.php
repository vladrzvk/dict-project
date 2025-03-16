<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration globale DICT
    |--------------------------------------------------------------------------
    |
    | Configuration pour les composants DICT (Disponibilité, Intégrité,
    | Confidentialité, Traçabilité) de l'application.
    |
    */

    // Canal de journalisation
    'log_channel' => env('DICT_LOG_CHANNEL', 'dict'),
    
    // Stockage des requêtes dans la base de données
    'store_requests_in_db' => env('DICT_STORE_REQUESTS', false),
    
    // Configuration de la disponibilité
    'availability' => [
        'enabled' => true,
        'health_check_interval' => 300, // secondes
        'critical_response_time' => 1000, // millisecondes
        'warning_response_time' => 500, // millisecondes
    ],
    
    // Configuration de l'intégrité
    'integrity' => [
        'enabled' => true,
        'check_database_structure' => true,
        'check_data_consistency' => true,
    ],
    
    // Configuration de la confidentialité
    'confidentiality' => [
        'enabled' => true,
        'enforce_https' => env('APP_ENV') === 'production',
        'sensitive_fields' => [
            'password', 'token', 'secret', 'api_key', 'credit_card',
            'ssn', 'social_security', 'passport', 'license'
        ],
    ],
    
    // Configuration de la traçabilité
    'traceability' => [
        'enabled' => true,
        'log_requests' => true,
        'log_responses' => true,
        'log_sensitive_data' => false,
        'request_id_header' => 'X-Request-ID',
    ],
    
    // Tables de journalisation DICT (à utiliser si store_requests_in_db = true)
    'log_tables' => [
        'requests' => 'dict_request_logs',
        'errors' => 'dict_error_logs',
        'events' => 'dict_event_logs',
    ],
];