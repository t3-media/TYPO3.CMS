<?php

// Exit early if php requirement is not satisfied.
if (PHP_VERSION_ID < 70200) {
    die('This version of TYPO3 CMS requires PHP 7.2 or above');
}

// This is a stub file for redirecting the user to the proper Install Tool URL

call_user_func(function () {

    // We leverage the class loader here to get the static functionality of GeneralUtility and HttpUtility.
    // This way we do not need to copy all the code here to cope with correct location header URL generation correctly
    // as those two classes can already correctly deal with all known edge cases.

    $classLoader = require __DIR__ . '/../../vendor/autoload.php';

    // We ensure that possible notices from Core code do not kill our redirect due to PHP output
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));

    // @deprecated in 9.x will be removed in 10.0
    \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::run(2, \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_INSTALL);
    \TYPO3\CMS\Core\Core\Bootstrap::init($classLoader, true);
    \TYPO3\CMS\Core\Utility\HttpUtility::redirect('../install.php', \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_307);
});
