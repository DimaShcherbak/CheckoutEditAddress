<?php

namespace RockLab\CheckoutEditAddress\Plugin;

use Amasty\Checkout\Helper\Onepage;
use Amasty\Checkout\Model\Config;
use Magento\Customer\Model\Session;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor
{
    /**
     * @var array
     */
    protected $orderFixes = [];

    /**
     * @var Onepage
     */
    private $onepageHelper;

    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * LayoutProcessor constructor.
     * @param Onepage $onepageHelper
     * @param Config $checkoutConfig
     * @param Session $customerSession
     */
    public function __construct(
        Onepage $onepageHelper,
        Config $checkoutConfig,
        Session $customerSession

    ) {
        $this->onepageHelper = $onepageHelper;
        $this->checkoutConfig = $checkoutConfig;
        $this->customerSession = $customerSession;

    }

    /**
     * @param $field
     * @param $order
     */
    public function setOrder($field, $order)
    {
        $this->orderFixes[$field] = $order;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {

        if ($this->checkoutConfig->isEnabled() && $this->customerSession->isLoggedIn() == true) {
            $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]
            ["children"]["shippingAddress"]["children"]["address-list-additional-addresses"]["children"]['custom-form'] = [
                'component' => 'RockLab_CheckoutEditAddress/js/edit-address',
                'config' => [
                    'template' => 'RockLab_CheckoutEditAddress/edit-address-button',
                ]
            ];
//            $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]
//            ["children"]["shippingAddress"]["popUpFormEdit"]= [
//              'element'=>'#opc-new-shipping-addres111',
//                'options'=>[
//                    'type'=>'popup',
//                    'responsive'=>true,
//                    'innerScroll'=>true,
//                    'title'=>'Edit Shipping Address',
//                    'trigger'=>'opc-new-shipping-address111',
//                    'buttons'=>[
//                        'save'=>[
//                            'text'=>'Edit Ship here',
//                            'class'=>'action primary action-save-address1'
//                        ],
//                        'cancel'=>[
//                            'text'=>'Cancel',
//                            'class'=>'action secondary action-hide-popup'
//                        ]
//                    ]
//                ]
//            ];
        }
        return $result;
    }
}
