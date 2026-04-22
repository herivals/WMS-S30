<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('gtin', TextType::class, ['label' => 'GTIN', 'required' => false, 'attr' => ['placeholder' => '03760178690049', 'maxlength' => 14]])
            ->add('refClient', TextType::class, ['label' => 'Ref Client', 'required' => false])
            ->add('famille', TextType::class, ['label' => 'Famille', 'required' => false])
            ->add('deposant', TextType::class, ['label' => 'Déposant', 'required' => false])
            ->add('controleUnicite', TextType::class, ['label' => 'Contrôle unicité', 'required' => false])
            ->add('dateCreation', DateType::class, ['label' => 'Date création', 'required' => false, 'widget' => 'single_text'])
            ->add('datePrevueInvent', DateType::class, ['label' => 'Date prévue inventaire', 'required' => false, 'widget' => 'single_text'])
            ->add('dernierInvent', DateType::class, ['label' => 'Dernier inventaire', 'required' => false, 'widget' => 'single_text'])
            ->add('dernierePrep', DateType::class, ['label' => 'Dernière prep', 'required' => false, 'widget' => 'single_text'])
            ->add('derniereRecep', DateType::class, ['label' => 'Dernière réception', 'required' => false, 'widget' => 'single_text'])
            ->add('delaiDluo', NumberType::class, ['label' => 'Délai DLUO', 'required' => false, 'scale' => 2])
            ->add('delaiFournisseur', NumberType::class, ['label' => 'Délai fournisseur', 'required' => false, 'scale' => 2])
            ->add('delaiReappro', NumberType::class, ['label' => 'Délai réappro', 'required' => false, 'scale' => 2])
            ->add('prixUnitHt', NumberType::class, ['label' => 'Prix unitaire HT', 'required' => false, 'scale' => 2])
            ->add('qteDispoGood', IntegerType::class, ['label' => 'Qté dispo good', 'required' => false])
            ->add('qteDispoBad', IntegerType::class, ['label' => 'Qté dispo bad', 'required' => false])
            ->add('qteStockee001', IntegerType::class, ['label' => 'Qté stockée 001', 'required' => false])
            ->add('qteReservee001', IntegerType::class, ['label' => 'Qté réservée 001', 'required' => false])
            ->add('etat', TextType::class, ['label' => 'Etat', 'required' => false])
            ->add('statut', TextType::class, ['label' => 'Statut', 'required' => false])
            ->add('tendance', TextType::class, ['label' => 'Tendance', 'required' => false])
            ->add('typeEdition', TextType::class, ['label' => 'Type édition', 'required' => false])
            ->add('uniteDeMesure', TextType::class, ['label' => 'Unité de mesure', 'required' => false])
            ->add('fournisseur', TextType::class, ['label' => 'Fournisseur', 'required' => false])
            ->add('desigLongue', TextareaType::class, ['label' => 'Désignation longue', 'required' => false, 'attr' => ['rows' => 3]])
            ->add('choixLotEnPrep', CheckboxType::class, ['label' => 'Choix lot en prep', 'required' => false])
            ->add('consommable', CheckboxType::class, ['label' => 'Consommable', 'required' => false])
            ->add('gestionDluo', CheckboxType::class, ['label' => 'Gestion DLUO', 'required' => false])
            ->add('gestionLot', CheckboxType::class, ['label' => 'Gestion LOT', 'required' => false])
            ->add('estUnKit', CheckboxType::class, ['label' => 'Est un kit', 'required' => false])
            ->add('screenable', CheckboxType::class, ['label' => 'Screenable', 'required' => false])
            ->add('releveNumeroParc', CheckboxType::class, ['label' => 'Relevé numéro parc', 'required' => false])
            ->add('releveNumeroSerie', CheckboxType::class, ['label' => 'Relevé numéro série', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
