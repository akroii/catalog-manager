<?php

$GLOBALS['BE_MOD']['system']['catalog-manager'] = [

    'name' => 'catalog-manager',
    'icon' => 'system/modules/catalog-manager/assets/icons/icon.svg',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
];

array_insert( $GLOBALS['FE_MOD'], 3, [

    'catalog-manager' => [

        'catalogTaxonomyTree' => 'ModuleCatalogTaxonomyTree',
        'catalogUniversalView' => 'ModuleUniversalView',
        'catalogMasterView' => 'ModuleMasterView',
        'catalogFilter' => 'ModuleCatalogFilter',
    ]
]);

if ( TL_MODE == 'BE' ) {

    $GLOBALS['TL_JAVASCRIPT']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/BackendExtension.js'
        : 'system/modules/catalog-manager/assets/BackendExtension.js';

    $GLOBALS['TL_CSS']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/widget.css'
        : 'system/modules/catalog-manager/assets/widget.css';
}

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\ActiveInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\MasterInsertTag', 'getInsertTagValue' ];

$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = [ 'CatalogManager\RoutingBuilder', 'initialize' ];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [ 'CatalogManager\SearchIndexBuilder', 'initialize' ];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'CatalogManager\UserPermissionExtension', 'initialize' ];
$GLOBALS['TL_HOOKS']['initializeSystem'][] = [ 'CatalogManager\CatalogManagerInitializer', 'initialize' ];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'CatalogManager\MemberPermissionExtension', 'initialize' ];

$GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] = [];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';

$GLOBALS['BE_FFL']['catalogOrderByWizard'] = 'CatalogManager\CatalogOrderByWizard';
$GLOBALS['BE_FFL']['catalogMessageWidget'] = 'CatalogManager\CatalogMessageWidget';
$GLOBALS['BE_FFL']['catalogTaxonomyWizard'] = 'CatalogManager\CatalogTaxonomyWizard';
$GLOBALS['BE_FFL']['catalogRelationRedirectWizard'] = 'CatalogManager\CatalogRelationRedirectWizard';
$GLOBALS['BE_FFL']['catalogFilterFieldSelectWizard'] = 'CatalogManager\CatalogFilterFieldSelectWizard';

$GLOBALS['TL_FFL']['catalogMessageForm'] = 'CatalogManager\CatalogMessageForm';

$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [ 'CatalogManager\ChangeLanguageExtension', 'translateUrlParameters' ];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['catalog_manager'] = [

    'ctlg_entity_status_insert'   => [

        'recipients' => [ 'admin_email', 'form_*' ],
        'email_subject' => [ 'form_*', 'admin_email' ],
        'email_text' => [ 'form_*', 'admin_email' ],
        'email_html' => [ 'form_*', 'admin_email' ],
        'file_name' => [ 'form_*', 'admin_email' ],
        'file_content' => [ 'form_*', 'admin_email' ],
        'email_sender_name' => [ 'admin_email', 'form_*' ],
        'email_sender_address' => [ 'admin_email', 'form_*' ],
        'email_recipient_cc' => [ 'admin_email', 'form_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'form_*' ],
        'email_replyTo' => [ 'admin_email', 'form_*' ],
        'attachment_tokens' => [ 'form_*' ]
    ],

    'ctlg_entity_status_update' => [

        'recipients' => [ 'admin_email', 'form_*' ],
        'email_subject' => [ 'form_*', 'admin_email' ],
        'email_text' => [ 'form_*', 'admin_email' ],
        'email_html' => [ 'form_*', 'admin_email' ],
        'file_name' => [ 'form_*', 'admin_email' ],
        'file_content' => [ 'form_*', 'admin_email' ],
        'email_sender_name' => [ 'admin_email', 'form_*' ],
        'email_sender_address' => [ 'admin_email', 'form_*' ],
        'email_recipient_cc' => [ 'admin_email', 'form_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'form_*' ],
        'email_replyTo' => [ 'admin_email', 'form_*' ],
        'attachment_tokens' => [ 'form_*' ]
    ],

    'ctlg_entity_status_delete' => [

        'recipients' => [ 'admin_email', 'form_*' ],
        'email_subject' => [ 'form_*', 'admin_email' ],
        'email_text' => [ 'form_*', 'admin_email' ],
        'email_html' => [ 'form_*', 'admin_email' ],
        'email_sender_name' => [ 'admin_email', 'form_*' ],
        'email_sender_address' => [ 'admin_email', 'form_*' ],
        'email_recipient_cc' => [ 'admin_email', 'form_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'form_*' ],
        'email_replyTo' => [ 'admin_email', 'form_*' ],
        'attachment_tokens' => [ 'form_*' ]
    ]
];