<?php

declare(strict_types=1);

use Medas\Events\EventsPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();
    $config->addPackages([
        EventsPackage::instance(),
    ]);

    return $config;
});
