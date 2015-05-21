<?php
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('pb_social') . 'Resources/Private/Libs/';

return array(
    'facebook'                      => $extensionPath . 'facebook/src/facebook.php',
    'imgur'                         => $extensionPath . 'imgur/Imgur.php',
    'instagram'                     => $extensionPath . 'instagram/src/Instagram.php',
    'twitterAPIExchange'            => $extensionPath . 'twitter/TwitterAPIExchange.php',
    'youtube'                       => $extensionPath . 'youtube/src/Google/autoload.php',
    //'tumblr_autoload'               => $extensionPath . 'tumblr/vendor/autoload.php',
);
?>