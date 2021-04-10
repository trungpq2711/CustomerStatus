<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Extensions\CustomerStatus\Block\Customer;

use Magento\Customer\Block\Account\Dashboard;

class Status extends Dashboard
{
    /**
     * @return string|null
     */
    public function getCustomerStatus()
    {
        $customerStatus = $this->getCustomer()->getCustomAttribute('customer_status');
        if ($customerStatus) {
            return $customerStatus->getValue();
        }
        return null;
    }
}
