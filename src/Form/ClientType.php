<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => false, 'attr' => ['placeholder' => 'contact@deposant.com']])
            ->add('deposant', TextType::class, ['label' => 'Deposant', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'DEP-001']])
            ->add('nomDeposant', TextType::class, ['label' => 'Nom deposant', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'Nom du deposant']])
            ->add('batRetour', TextType::class, ['label' => 'Bat retour', 'required' => false])
            ->add('villeSoc', TextType::class, ['label' => 'Ville soc', 'required' => false])
            ->add('telSoc', TextType::class, ['label' => 'Tel soc', 'required' => false])
            ->add('adresse2Soc', TextType::class, ['label' => 'Adresse2 soc', 'required' => false])
            ->add('paysSoc', TextType::class, ['label' => 'Pays soc', 'required' => false])
            ->add('commentaireSoc', TextareaType::class, ['label' => 'Commentaire soc', 'required' => false, 'attr' => ['rows' => 3]])
            ->add('releveNumeroParc', CheckboxType::class, ['label' => 'Releve numero parc', 'required' => false])
            ->add('contactSoc', TextType::class, ['label' => 'Contact soc', 'required' => false])
            ->add('releveNumeroSerie', CheckboxType::class, ['label' => 'Releve numero serie', 'required' => false])
            ->add('codePaysSoc', TextType::class, ['label' => 'Code pays soc', 'required' => false])
            ->add('adresse1Soc', TextType::class, ['label' => 'Adresse1 soc', 'required' => false])
            ->add('cpSoc', TextType::class, ['label' => 'CP soc', 'required' => false])
            ->add('faxSoc', TextType::class, ['label' => 'Fax soc', 'required' => false])
            ->add('quartierRetour', TextType::class, ['label' => 'Quartier retour', 'required' => false])
            ->add('responsableCompte', TextType::class, ['label' => 'Responsable compte', 'required' => false])
            ->add('nbJoursTravaillesSur3Mois', IntegerType::class, ['label' => 'Nb jours travailles sur 3 mois', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Client::class]);
    }
}
