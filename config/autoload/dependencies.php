<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

use App\Kernel\Casbin\Factory;
use Casbin\Enforcer;
use Psr\Container\ContainerInterface;

return [
    Enforcer::class => Factory::class,
    \App\Kernel\Upload\UploadInterface::class   =>  \App\Kernel\Upload\Factory::class
];
