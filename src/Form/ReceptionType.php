<?php

namespace App\Form;

use App\Entity\Reception;
use App\Enum\TypeReception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReceptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, ['label' => 'Référence', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'REC-2026-001']])
            ->add('date', DateType::class, ['label' => 'Date', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('typeReception', EnumType::class, ['label' => 'Type de réception', 'class' => TypeReception::class, 'choice_label' => fn(TypeReception $e) => $e->label()]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Reception::class]);
    }
}
