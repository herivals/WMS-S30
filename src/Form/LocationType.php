<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, ['label' => 'Code', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'A-01-01-01']])
            ->add('allee', TextType::class, ['label' => 'Allée', 'required' => false, 'attr' => ['placeholder' => 'A']])
            ->add('rack', TextType::class, ['label' => 'Rack', 'required' => false, 'attr' => ['placeholder' => '01']])
            ->add('niveau', TextType::class, ['label' => 'Niveau', 'required' => false, 'attr' => ['placeholder' => '01']])
            ->add('position', TextType::class, ['label' => 'Position', 'required' => false, 'attr' => ['placeholder' => '01']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Location::class]);
    }
}
