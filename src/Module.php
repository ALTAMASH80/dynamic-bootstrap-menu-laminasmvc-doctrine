<?php 
/**
 * @author shah.mubashir@gmail.com
 * @version 1.0.0
 */
declare(strict_types=1);

namespace LRPHPT\MenuTree;

class Module{
    public function getConfig() : array{
        return include __DIR__ . '/../config/module.config.php';
    }
}