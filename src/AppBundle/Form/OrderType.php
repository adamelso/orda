<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'repeated', [
            'type'            => 'email',
            'first_options'   => ['label' => 'Email'],
            'second_options'  => ['label' => 'Repeat Email'],
            'invalid_message' => 'The email fields must match.',
        ]);
        $builder->add('proceed', 'submit', ['attr' => ['class' => 'button']]);
    }
}
