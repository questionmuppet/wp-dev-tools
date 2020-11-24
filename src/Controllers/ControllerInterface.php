<?php
/**
 * ControllerInterface
 * 
 * Interface for handling command-line requests
 */

namespace Wp_Dev_Tools\Controllers;

use Wp_Dev_Tools\Arguments\ArgumentParser;

interface ControllerInterface
{
    /**
     * Execute the requested action
     */
    public function execute(ArgumentParser $args): void;
}