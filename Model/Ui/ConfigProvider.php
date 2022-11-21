<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Reach\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Reach\Payment\Gateway\Http\Client\ClientMock;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'reach_dropin';
    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ]
                ]
            ]
        ];
    }
}
