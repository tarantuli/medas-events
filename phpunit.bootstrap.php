<?php

declare(strict_types=1);

use Medas\Events\EventsPackage;
use Medas\ServiceManager\ServiceManager;

chdir(__DIR__);

ServiceManager::get()
    ->addPackage(EventsPackage::instance());
