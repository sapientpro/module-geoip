<?php

namespace SapientPro\GeoIP\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\StoreManagerInterface;

class StoreViewColumn extends Select
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * StoreViewColumn constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): StoreViewColumn
    {
        return $this->setData('name', $value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value): StoreViewColumn
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    /**
     * Get the options for the select
     *
     * @return array[]
     */
    private function getSourceOptions(): array
    {
        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            $stores[] = [
                'label' => $store->getName(),
                'value' => $store->getId(),
            ];
        }

        return $stores;
    }
}
