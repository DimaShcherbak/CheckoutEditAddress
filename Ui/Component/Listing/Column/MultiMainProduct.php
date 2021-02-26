<?php

namespace RockLab\CheckoutEditAddress\Ui\Component\Listing\Column;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Address\Collection;

/**
 * Class Coupon
 */
class MultiMainProduct implements OptionSourceInterface
{
    /**
     * @var
     */
    protected $criteria;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Collection
     */
    protected $addressCustomerCollection;

    /**
     * MultiMainProduct constructor.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @param Json $json
     * @param Collection $addressCustomerCollection
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria,
        Json $json,
        Collection $addressCustomerCollection

    ) {
        $this->searchCriteria = $criteria;
        $this->json = $json;
        $this->addressCustomerCollection = $addressCustomerCollection;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $collection = $this->addressCustomerCollection->create();
        $items = $collection->getItems();
        $data =[];
        foreach ($items as $item) {
            $shopCode = $item->getData('shop_id');
            $label = $item->getData('title'). ', '. $item->getData('adrus');
            $data[] = ['value' => $shopCode, 'label' => $label];
        }
        return $data;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return mixed
     */
    public function getJsonCollection()
    {
        return $this->collection->execute();
    }
}
