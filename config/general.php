<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 */

return [
    // Global settings
    '*' => [
        'autosaveDrafts' => false,
        'enableGql' => false,
        'defaultWeekStartDay' => 1,
        'defaultTokenDuration' => 'P10D',
        'useEmailAsUsername' => true,
        'enableCsrfProtection' => true,
        'omitScriptNameInUrls' => true,
        'defaultCpLanguage' => 'en_GB',
        'securityKey' => getenv('SECURITY_KEY'),
        'elevatedSessionDuration' => 360000,
        'verificationCodeDuration' => 'P3W',
        'defaultSearchTermOptions' => array(
            'subLeft' => true,
            'subRight' => true,
        ),
//        'postCpLoginRedirect' => 'entries',
        'aliases' => [
            'basePath' => $_SERVER['DOCUMENT_ROOT'],
            'baseUrl' => strtolower((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME']),
        ],
        //  Registration & account settings
        'setPasswordPath' => 'account/set-password',
        'activateAccountSuccessPath' => [
            'nl' => '/registratie-voltooid',
            'fr' => '/inscription-terminee',
            'en' => 'registration-completed'
        ],

        'setPasswordSuccessPath' => '/account/password-set-successfully'
    ],

    // Production environment settings
    'production' => [
        'enableTemplateCaching' => true,
        'backupOnUpdate' => true,
        'allowAdminChanges' => (php_sapi_name() === 'cli'),
        'aliases' => [
            'basePath' => $_SERVER['DOCUMENT_ROOT'],
            'baseUrl' => 'https://www.sharepair.org',
            'baseUrl3D' => 'https://3d.repcit.live.statik.be',
            'baseUrlApeldoorn' => 'https://www.heelapeldoornrepareert.nl',
            'baseUrlLeuven' => 'https://www.leuvenfixt.be',
            'baseUrlLouvainLaNeuve' => 'https://www.repairstudio.be',
            'baseUrlRoeselare' => 'https://www.roeselarerepareert.be',
        ],
    ],
    // Staging environment settings
    'staging' => [
        'testToEmailAddress' => getenv("DEBUG_EMAIL"),
        'enableTemplateCaching' => false,
        'backupOnUpdate' => false,
        'allowAdminChanges' => (php_sapi_name() === 'cli'),
        'aliases' => [
            'basePath' => $_SERVER['DOCUMENT_ROOT'],
            'baseUrl' => 'https://repcit.staging.be',
            'baseUrl3D' => 'https://3d.repcit.staging.be',
            'baseUrlApeldoorn' => 'https://apeldoorn.repcit.staging.be',
            'baseUrlLeuven' => 'https://leuven.repcit.staging.be',
            'baseUrlLouvainLaNeuve' => 'https://louvain-la-neuve.repcit.staging.be',
            'baseUrlRoeselare' => 'https://roeselare.repcit.staging.be',
        ],
    ],

    // Dev environment settings
    'dev' => [
        'enableTemplateCaching' => false,
        'backupOnUpdate' => false,
        'devMode' => true,
        'aliases' => [
            'basePath' => $_SERVER['DOCUMENT_ROOT'],
            'baseUrl' => 'https://repcit.local.be',
            'baseUrl3D' => 'https://3d.repcit.local.be',
            'baseUrlApeldoorn' => 'https://apeldoorn.repcit.local.be',
            'baseUrlLeuven' => 'https://leuven.repcit.local.be',
            'baseUrlLouvainLaNeuve' => 'https://louvain-la-neuve.repcit.local.be',
            'baseUrlRoeselare' => 'https://roeselare.repcit.local.be',
        ],
    ],
];
