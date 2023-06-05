<?php
/**
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Controller\Adminhtml\Download;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Tandym\Tandympay\Helper\Data;

/**
 * Class TandymLog
 * Enables custom client log file to be accessed via an admin link
 */
class TandymLog extends System
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * ClientLog constructor.
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $filePath = $this->getFilePath();

        $fileName = basename((string)$filePath);

        try {
            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => $filePath
                ]
            );
        } catch (\Exception $e) {
            throw new NotFoundException(__($e->getMessage()));
        }
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return Data::TANDYM_LOG_FILE_PATH;
    }
}
