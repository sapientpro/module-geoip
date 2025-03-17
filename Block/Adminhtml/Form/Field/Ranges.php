<?php

namespace SapientPro\GeoIP\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

class Ranges extends AbstractFieldArray
{
    /**
     * @var CountryColumn|null
     */
    private ?CountryColumn $countryRenderer = null;

    /**
     * @var StoreViewColumn|null
     */
    private ?StoreViewColumn $storeViewColumn = null;

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * Prepare rendering the new field by adding all the needed columns
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('from_country', [
            'label' => __('Country'),
            'renderer' => $this->getCountryRenderer()
        ]);

        $this->addColumn('redirect_to', [
            'label' => __('Country'),
            'renderer' => $this->getStoreViewRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $fromCountry = $row->getData('from_country');
        if ($fromCountry !== null) {
            $options['option_' . $this->getCountryRenderer()->calcOptionHash($fromCountry)] = 'selected="selected"';
        }

        $redirectTo = $row->getData('redirect_to');
        if ($redirectTo !== null) {
            $options['option_' . $this->getStoreViewRenderer()->calcOptionHash($redirectTo)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get tax renderer
     *
     * @return CountryColumn
     * @throws LocalizedException
     */
    private function getCountryRenderer(): CountryColumn
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                CountryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->countryRenderer;
    }

    public function getStoreViewRenderer(): StoreViewColumn
    {
        if (!$this->storeViewColumn) {
            $this->storeViewColumn = $this->getLayout()->createBlock(
                StoreViewColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->storeViewColumn;
    }
}
