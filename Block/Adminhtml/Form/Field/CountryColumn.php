<?php

namespace SapientPro\GeoIP\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class CountryColumn extends Select
{
    public function __construct(
        Context $context,
        CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): CountryColumn
    {
        return $this->setData('name', $value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value): CountryColumn
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
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection->addFieldToSelect('*');

        $countries = [
            [
                'label' => __('Default for other countries'),
                'value' => 'any'
            ]
        ];
        foreach ($countryCollection as $country) {
            $countries[] = [
                'label' => $country->getName(),
                'value' => $country->getId()
            ];
        }

        return $countries;
    }
}
