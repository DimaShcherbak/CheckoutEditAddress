<?php


namespace RockLab\CheckoutEditAddress\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;

/**
 * Class CustomConfigProvider
 * @package RockLab\CheckoutEditAddress\Model
 */
class CustomConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * CustomConfigProvider constructor.
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory, Json $json

    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['customData'] = $this->json->serialize($this->getAddress());
        return $config;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getAddress() {
        $collection = $this->productCollectionFactory->create();
        $items = $collection->getItems();
        $data = [];
        $i = 0;
        foreach ($items as $item) {
            $data[] = $item->getData();
            $data[$i]['street'] = explode(PHP_EOL, $data[$i]['street']);
            $data['countryID'] = $data[$i]['country_id'];
            $data['regionID'] = $data[$i]['region_id'];
            $i++;
        }
        return $data;
    }
}
