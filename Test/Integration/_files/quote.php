<?php
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->setData(['store_id' => 1, 'is_active' => 0, 'is_multi_shipping' => 0]);
/**
 * or
 *  $quote->setStoreId(1)
->setIsActive(true)
->setIsMultiShipping(false)
->setReservedOrderId('reserved_order_id')
->collectTotals()
->save();
 *
 */
$quote->save();
