<?php
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('pb_social') . 'Resources/Private/Libs/';

return array(
    'facebook'                      => $extensionPath . 'facebook/src/facebook.php',
    'instagram'                     => $extensionPath . 'instagram/src/Instagram.php',
    'twitterAPIExchange'            => $extensionPath . 'twitter/TwitterAPIExchange.php',
    //'tumblr_autoload'               => $extensionPath . 'tumblr/vendor/autoload.php',
);
?>