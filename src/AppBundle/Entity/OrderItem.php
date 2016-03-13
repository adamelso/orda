<?php

namespace AppBundle\Entity;

use Sylius\Component\Order\Model\OrderItem as SyliusOrderItem;

class OrderItem extends SyliusOrderItem
{
    /**
     * @var Download
     */
    private $download;

    /**
     * @return Download
     */
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * @param Download $download
     */
    public function setDownload(Download $download)
    {
        $this->download = $download;
    }
}
