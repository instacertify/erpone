<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Instacertify ERP
    |--------------------------------------------------------------------------
    |
    | Module registry and branding for the Instacertify ERP platform.
    | Hosted at instacertify.in — toggle modules via env during install.
    |
    */

    'name' => env('ERP_NAME', 'Instacertify ERP'),

    'company' => env('ERP_COMPANY', 'Instacertify'),

    'domain' => env('ERP_DOMAIN', 'instacertify.in'),

    'version' => '0.1.0',

    'brand' => [
        'primary' => '#065175',
        'highlight' => '#ec6820',
    ],

    'currency' => [
        'primary' => 'INR',
        'quote' => ['INR', 'USD', 'EUR'],
    ],

    'modules' => [
        'crm' => [
            'enabled' => (bool) env('ERP_MODULE_CRM', true),
            'label' => 'Customers',
            'navigation_group' => 'CRM',
            'description' => 'Customer and contact management',
        ],
        'sales' => [
            'enabled' => (bool) env('ERP_MODULE_SALES', true),
            'label' => 'Sales',
            'navigation_group' => 'Sales',
            'description' => 'Leads, opportunities, and quotations',
        ],
        'projects' => [
            'enabled' => (bool) env('ERP_MODULE_PROJECTS', true),
            'label' => 'Projects',
            'navigation_group' => 'Projects',
            'description' => 'Project delivery and task tracking',
        ],
        'calendar' => [
            'enabled' => (bool) env('ERP_MODULE_CALENDAR', true),
            'label' => 'Calendar',
            'navigation_group' => 'Calendar',
            'description' => 'Team calendar and scheduling',
        ],
        'chat' => [
            'enabled' => (bool) env('ERP_MODULE_CHAT', true),
            'label' => 'Team Chat',
            'navigation_group' => 'Collaboration',
            'description' => 'Internal team messaging',
        ],
        'storage' => [
            'enabled' => (bool) env('ERP_MODULE_STORAGE', true),
            'label' => 'Storage',
            'navigation_group' => 'Storage',
            'description' => 'Document and file storage (MinIO)',
        ],
        'testing' => [
            'enabled' => (bool) env('ERP_MODULE_TESTING', true),
            'label' => 'Testing Tracker',
            'navigation_group' => 'Quality',
            'description' => 'Customer project and testing tracker',
        ],
        'samples' => [
            'enabled' => (bool) env('ERP_MODULE_SAMPLES', true),
            'label' => 'Sample Management',
            'navigation_group' => 'Quality',
            'description' => 'Sample intake, custody, and disposition',
        ],
        'hrms' => [
            'enabled' => (bool) env('ERP_MODULE_HRMS', true),
            'label' => 'HRMS',
            'navigation_group' => 'HR',
            'description' => 'Employees, departments, and leave',
        ],
        'billing' => [
            'enabled' => (bool) env('ERP_MODULE_BILLING', true),
            'label' => 'Billing',
            'navigation_group' => 'Finance',
            'description' => 'Invoices, payments, and receivables',
        ],
    ],

];
