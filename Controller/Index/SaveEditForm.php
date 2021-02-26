<?php

namespace RockLab\CheckoutEditAddress\Controller\Index;

use Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\Session;
use \Magento\Customer\Model\Data\RegionFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;


/**
 * Class SaveEditForm
 * @package RockLab\CheckoutEditAddress\Controller\Index
 */
class SaveEditForm extends Action
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    protected $dataAddressFactory;

    /**
     * SaveEditForm constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param RegionFactory $regionFactory
     * @param AddressInterfaceFactory $dataAddressFactory
     */
    public function __construct(Context $context,
                                Session $customerSession,
                                \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
                                RegionFactory $regionFactory,
                                AddressInterfaceFactory $dataAddressFactory)
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
        $this->dataAddressFactory = $dataAddressFactory;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultPage = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $formData = explode('&', $params['form']);
        if (array_key_exists('customerAddressId',$params)) {
        $addressId = $params['customerAddressId'];
        $data = [];
        foreach ($formData as $key => $value) {
            $tmp = explode('=', $value);
            $tmp2 = explode('%', $tmp[0]);
            if ($tmp[0] != 'teletphone') {
                $tmp[1] = str_replace('+', ' ', $tmp[1]);
            }
            if (count($tmp2) > 1) {
                $tmp[1] = str_replace('+', ' ', $tmp[1]);
                $data['street'][] = $tmp[1];
                continue;
            }
            $data[$tmp[0]] = $tmp[1];
        }

        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = $this->addressRepository->getById($addressId);
        $address->setFirstname($data['firstname']);
        $address->setLastname($data['lastname']);
        $address->setCompany($data['company']);
        $address->setStreet($data['street']);
        $address->setCity($data['city']); // Update city
        $address->setCountryId($data['country_id']); // Update country id

        $region = $this->regionFactory->create();
        if (array_key_exists('region_id',$data) && $data['region_id'] != "") {
            $region->setRegionId($data['region_id']);
            $address->setRegionId($data['region_id']);
        } else $region->setRegion($data['region']);

        $address->setRegion($region);
        $address->setPostcode($data['postcode']);
        $address->setTelephone($data['telephone']);

        $this->addressRepository->save($address);
        if (array_key_exists('region_id',$data) && $data['region_id'] != "") {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $region = $objectManager->create('Magento\Directory\Model\Region')
                ->load($data['region_id']);
            $data['region_code'] = $region->getCode();
            $data['region']= $region->getName();
        }

        $resultPage->setData($data);
        return $resultPage;
    }
//        else {
//            $session = $this->customerSession;
//            $customerId = $this->customerSession->getCustomer()->getId();
//            $data = [];
//            foreach ($formData as $key => $value) {
//
//                $tmp = explode('=', $value);
//                $tmp2 = explode('%', $tmp[0]);
//                if ($tmp[0] != 'teletphone') {
//                    $tmp[1] = str_replace('+', ' ', $tmp[1]);
//                }
//                if (count($tmp2) > 1) {
//                    $tmp[1] = str_replace('+', ' ', $tmp[1]);
//                    $data['street'][] = $tmp[1];
//                    continue;
//                }
//                $data[$tmp[0]] = $tmp[1];
//            }
//
//            /**
//             * @var
//             */
//            $addressAlt = $this->dataAddressFactory->create();
//            $addressAlt->setFirstname($data['firstname']);
//            $addressAlt->setLastname($data['lastname']);
//            $addressAlt->setCompany($data['company']);
//            $addressAlt->setStreet($data['street']);
//            $addressAlt->setCity($data['city']); // Update city
//            $addressAlt->setCountryId($data['country_id']); // Update country id
//            $region = $this->regionFactory->create();
//            if (array_key_exists('region_id',$data) && $data['region_id'] != "") {
//                $region->setRegionId($data['region_id']);
//            } else $region->setRegion($data['region']);
//
//            $addressAlt->setRegion($region);
//            $addressAlt->setPostcode($data['postcode']);
//            $addressAlt->setTelephone($data['telephone']);
//            $addressAlt->setCustomerId($customerId);
//
//            $this->addressRepository->save($addressAlt);
//            $data['customerId'] = $customerId;
//            if (array_key_exists('region_id',$data) && $data['region_id'] != "") {
//                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//                $region = $objectManager->create('Magento\Directory\Model\Region')
//                    ->load($data['region_id']);
//                $data['region_code'] = $region->getCode();
//                $data['region']= $region->getName();
//            }
//            $resultPage->setData($data);
//            return $resultPage;
//        }
    }
}
