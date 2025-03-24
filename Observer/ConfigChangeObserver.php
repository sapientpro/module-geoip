<?php

declare(strict_types=1);

namespace SapientPro\GeoIP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SapientPro\GeoIP\Model\Config;

class ConfigChangeObserver implements ObserverInterface
{
    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param WriterInterface $configWriter
     * @param DateTime $dateTime
     */
    public function __construct(
        WriterInterface $configWriter,
        DateTime $dateTime
    ) {
        $this->configWriter = $configWriter;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $changedPaths = $observer->getEvent()->getChangedPaths();
        if (count($changedPaths ?? [])) {
            $this->configWriter->save(Config::XML_PATH_LAST_CONFIG_CHANGE, $this->dateTime->gmtDate());
        }
    }
}
