<?php

namespace Tandym\Tandympay\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 * @package Tandym\Tandympay\Logger
 */
class Handler extends Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/tandympay.log';
}
