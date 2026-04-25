<?php

namespace App\Form;

use App\Entity\Charge;
use App\Entity\Client;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\Reception;
use App\Enum\EtatUL;
use App\Enum\StatutUL;
use App\Enum\TypeFlux;
use App\Enum\TypeReception;
use App\Enum\TypeUnite;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StockUnitType extends AbstractType
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Card 1 — Identification
            ->add('codeCharge', TextType::class, ['label' => 'Code charge', 'constraints' => [new NotBlank()], 'attr' => ['placeholder' => 'CH-2026-00001']])
            ->add('serialNumber', TextType::class, ['label' => 'N° de série', 'required' => false])
            ->add('designation', TextType::class, ['label' => 'Désignation', 'required' => false])
            ->add('typeUnite', EnumType::class, ['label' => 'Type UL', 'class' => TypeUnite::class, 'choice_label' => fn(TypeUnite $e) => $e->label()])
            ->add('statut', EnumType::class, ['label' => 'Statut', 'class' => StatutUL::class, 'choice_label' => fn(StatutUL $e) => $e->label()])
            // Card 2 — Relations (product ajouté dynamiquement via événements)
            ->add('reception', EntityType::class, ['label' => 'Réception', 'class' => Reception::class, 'choice_label' => fn(Reception $r) => (string) $r, 'required' => false, 'placeholder' => '— Aucune —', 'attr' => ['class' => 'form-select']])
            ->add('owner', EntityType::class, ['label' => 'Déposant', 'class' => Client::class, 'choice_label' => fn(Client $c) => (string) $c, 'required' => false, 'placeholder' => '— Aucun —', 'attr' => ['class' => 'form-select']])
            ->add('emplacement', EntityType::class, ['label' => 'Emplacement', 'class' => Location::class, 'choice_label' => fn(Location $l) => (string) $l, 'required' => false, 'placeholder' => '— Aucun —', 'attr' => ['class' => 'form-select']])
            // Card 3 — Quantités
            ->add('quantite', NumberType::class, ['label' => 'Quantité totale', 'scale' => 2])
            ->add('quantiteReservee', NumberType::class, ['label' => 'Quantité réservée', 'scale' => 2])
            ->add('quantiteARegrouper', NumberType::class, ['label' => 'Quantité à regrouper', 'scale' => 2])
            // Card 4 — Lot
            ->add('lot', TextType::class, ['label' => 'Lot', 'required' => false])
            ->add('lotFabrication', TextType::class, ['label' => 'Lot fabrication', 'required' => false])
            ->add('dateFabrication', DateType::class, ['label' => 'Date fabrication', 'required' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('dluo', DateType::class, ['label' => 'DLUO', 'required' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            // Card 5 — Dimensions
            ->add('poids', NumberType::class, ['label' => 'Poids (kg)', 'required' => false, 'scale' => 2])
            ->add('largeur', NumberType::class, ['label' => 'Largeur (cm)', 'required' => false, 'scale' => 2])
            ->add('hauteur', NumberType::class, ['label' => 'Hauteur (cm)', 'required' => false, 'scale' => 2])
            ->add('profondeur', NumberType::class, ['label' => 'Profondeur (cm)', 'required' => false, 'scale' => 2])
            // Card 6 — Logistique
            ->add('typeReception', EnumType::class, ['label' => 'Type réception', 'class' => TypeReception::class, 'choice_label' => fn(TypeReception $e) => $e->label(), 'required' => false, 'placeholder' => '— Aucun —'])
            ->add('typeFlux', EnumType::class, ['label' => 'Type flux', 'class' => TypeFlux::class, 'choice_label' => fn(TypeFlux $e) => $e->label(), 'required' => false, 'placeholder' => '— Aucun —'])
            ->add('etat', EnumType::class, ['label' => 'État', 'class' => EtatUL::class, 'choice_label' => fn(EtatUL $e) => $e->label()])
            ->add('familleLogistique', TextType::class, ['label' => 'Famille logistique', 'required' => false])
            // Card 7 — Atelier
            ->add('tempsAtelier', NumberType::class, ['label' => 'Temps atelier (h)', 'required' => false, 'scale' => 2])
            ->add('technicien', TextType::class, ['label' => 'Technicien', 'required' => false])
            ->add('etatFinal', TextType::class, ['label' => 'État final', 'required' => false])
            // Card 8 — Facturation
            ->add('prixAchat', NumberType::class, ['label' => 'Prix achat (€)', 'required' => false, 'scale' => 2])
            ->add('uniteFacturation', TextType::class, ['label' => 'Unité facturation', 'required' => false])
            // Card 9 — Flags
            ->add('multiReference', CheckboxType::class, ['label' => 'Multi-référence', 'required' => false])
            ->add('aInventorier', CheckboxType::class, ['label' => 'À inventorier', 'required' => false]);

        // Champ produit filtré dynamiquement selon le déposant sélectionné
        $addProductField = function (FormInterface $form, ?Client $owner, ?Product $currentProduct): void {
            $form->add('product', EntityType::class, [
                'label' => 'Produit',
                'class' => Product::class,
                'choice_label' => fn(Product $p) => (string) $p,
                'attr' => ['class' => 'form-select'],
                'placeholder' => $owner ? null : '— Sélectionnez d\'abord un déposant —',
                'query_builder' => function (ProductRepository $repo) use ($owner, $currentProduct) {
                    $qb = $repo->createQueryBuilder('p');
                    if ($owner !== null) {
                        $qb->andWhere('p.deposant = :dep')->setParameter('dep', $owner->getDeposant());
                    } elseif ($currentProduct !== null) {
                        // Charge existante sans déposant : on expose uniquement le produit actuel
                        $qb->andWhere('p.id = :pid')->setParameter('pid', $currentProduct->getId());
                    } else {
                        // Nouvelle charge sans déposant : liste vide jusqu'à sélection du déposant
                        $qb->andWhere('1 = 0');
                    }
                    return $qb->orderBy('p.reference', 'ASC');
                },
            ]);
        };

        // Rendu initial : filtrer selon le déposant actuel de la charge
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addProductField) {
                $charge = $event->getData();
                $owner = ($charge instanceof Charge) ? $charge->getOwner() : null;
                $currentProduct = ($charge instanceof Charge && $charge->getId() !== null)
                    ? $charge->getProduct()
                    : null;
                $addProductField($event->getForm(), $owner, $currentProduct);
            }
        );

        // Soumission : reconstruire le champ selon le déposant soumis
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($addProductField) {
                $data = $event->getData();
                $owner = null;
                if (!empty($data['owner'])) {
                    $owner = $this->em->find(Client::class, (int) $data['owner']);
                }
                // Lors de la soumission on n'a pas besoin de currentProduct
                // car le product soumis sera validé contre la liste filtrée par owner
                $addProductField($event->getForm(), $owner, null);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Charge::class]);
    }
}
