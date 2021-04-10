<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Extensions\CustomerStatus\Controller\Index;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;

class Save implements CsrfAwareActionInterface, HttpPostActionInterface, AccountInterface
{
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var RequestInterface
     */
    private $_request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $_customerRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Save constructor.
     * @param RedirectFactory $resultRedirectFactory
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RedirectFactory $resultRedirectFactory,
        Session $customerSession,
        Validator $formKeyValidator,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->_request = $request;
        $this->_customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customerstatus/index/index');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Change customer status action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $validFormKey = $this->formKeyValidator->validate($this->_request);

        if ($validFormKey && $this->_request->isPost()) {
            $customerStatus = $this->_request->getParam('customer_status');
            $customerId = $this->session->getCustomerId();
            try {
                $customer = $this->_customerRepository->getById($customerId);
                $customer->setCustomAttribute('customer_status', $customerStatus);
                $this->_customerRepository->save($customer);
                $this->messageManager->addSuccessMessage(__('Update save customer success'));
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getTraceAsString());
            } catch (InputMismatchException $e) {
                $this->messageManager->addErrorMessage($e->getTraceAsString());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getTraceAsString());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customerstatus/index/index');

        return $resultRedirect;
    }
}
