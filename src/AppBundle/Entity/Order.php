<?php

namespace AppBundle\Entity;

use Sylius\Component\Order\Model\Order as SyliusOrder;

class Order extends SyliusOrder
{
    /**
     * @var string
     */
    private $email;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return null|Download
     */
    public function getDownload()
    {
        return $this->items->first() ? $this->items->first()->getDownload() : null;
    }
}
