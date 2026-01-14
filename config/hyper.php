<?php

return [
    'menu' => [
        [
            'title' => 'Navigation',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'mdi mdi-home',
                ],
                [
                    'label' => 'Eröffnungen',
                    'url' => '/eroeffnungen',
                    'icon' => 'mdi mdi-account-plus',
                ],
                [
                    'label' => 'Mutationen',
                    'url' => '/mutationen',
                    'icon' => 'mdi mdi-account-edit',
                ],
            ],
        ],
        [
            'title' => 'Admin',
            'items' => [
                [
                    'label' => 'Verarbeitung Anträge',
                    'icon'  => 'mdi mdi-hammer-screwdriver',
                    'children' => [
                        [
                            'label' => 'Eröffnungen',
                            'url'   => '/admin/eroeffnungen',
                        ],
                        [
                            'label' => 'Mutationen',
                            'url'   => '/admin/mutationen',
                        ],
                        [
                            'label' => 'Austritte',
                            'url'   => '/admin/austritte',
                        ],
                    ],
                ],
                [
                    'label' => 'Active Directory Benutzer',
                    'url' => '/admin/ad-users',
                    'icon' => 'mdi mdi-account-multiple',
                ],
                [
                    'label' => 'Systemsteuerung',
                    'url' => '/admin',
                    'icon' => 'mdi mdi-apps',
                ],
            ],
        ],
    ]
];
