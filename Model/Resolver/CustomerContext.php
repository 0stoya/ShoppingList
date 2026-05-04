<?php
declare(strict_types=1);

namespace Ostoya\ShoppingList\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Phrase;

class CustomerContext
{
    public function getCustomerId(ContextInterface $context): int
    {
        $userId = (int)$context->getUserId();
        $userType = (int)$context->getUserType();

        if ($userId <= 0 || $userType !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new GraphQlAuthorizationException(new Phrase('The current customer is not authorized.'));
        }

        return $userId;
    }
}
