<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, ['label' => 'Référence', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'REF-001']])
            ->add('designation', TextType::class, ['label' => 'Désignation', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'Nom du produit']])
            ->add('ean13', TextType::class, ['label' => 'EAN13', 'required' => false, 'attr' => ['placeholder' => '3760178690049', 'maxlength' => 13]])
            ->add('gtin', TextType::class, ['label' => 'GTIN', 'required' => false, 'attr' => ['placeholder' => '03760178690049', 'maxlength' => 14]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
