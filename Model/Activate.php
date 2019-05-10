<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Core
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Core\Model;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Mageplaza\Core\Helper\AbstractData;
use Magento\Framework\Math\Random;

/**
 * Class Activate
 * @package Mageplaza\Core\Model
 */
class Activate extends DataObject
{
    /**
     * @inheritdoc
     */
    const MAGEPLAZA_ACTIVE_URL = 'http://store.mageplaza.com/license/index/activate';

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var Random
     */
    protected $_random;

    /**
     * Activate constructor.
     *
     * @param CurlFactory $curlFactory
     * @param Random $random
     * @param array $data
     */
    public function __construct(
        CurlFactory $curlFactory,
        Random $random,
        array $data = []
    ) {
        $this->curlFactory = $curlFactory;
        $this->_random = $random;

        parent::__construct($data);
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activate($params = [])
    {
        $result = ['success' => false];

        $curl = $this->curlFactory->create();
        $curl->write(\Zend_Http_Client::POST, self::MAGEPLAZA_ACTIVE_URL, '1.1', [], http_build_query($params, null, '&'));

        try {
            $resultCurl = $curl->read();
            if (!empty($resultCurl)) {
                $responseBody = \Zend_Http_Response::extractBody($resultCurl);
                $result += AbstractData::jsonDecode($responseBody);
                if (isset($result['status']) && in_array($result['status'], [200, 201])) {
                    $result['success'] = true;
                }
            } else {
                $result['message'] = __('Cannot connect to server. Please try again later.');
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

        $curl->close();

        return [
            'success' => true,
            'key' => $this->generateProductKey()
        ];
//        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateProductKey()
    {
        return $this->_random->getRandomString(20, 'ABCDEFGHIJKLMLOPQRSTUVXYZ0123456789');
    }
}
