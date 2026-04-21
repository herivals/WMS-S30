<?php

namespace App\DataFixtures;

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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ChargeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['stock'];
    }

    public function load(ObjectManager $manager): void
    {
        // ── Clients ──
        $clients = [];
        foreach ([
            ['ACME', 'ACM', 'acme@acme.com'],
            ['LogiPartner', 'LGP', 'contact@logipartner.fr'],
            ['RetailCo', 'RTC', null],
        ] as [$nom, $code, $email]) {
            $c = (new Client())->setNom($nom)->setCode($code)->setEmail($email);
            $manager->persist($c);
            $clients[] = $c;
        }

        // ── Products ──
        $products = [];
        foreach ([
            ['REF-001', 'Écran 27" 4K', '3760178690049', null],
            ['REF-002', 'Clavier mécanique RGB', '3760178690056', null],
            ['REF-003', 'Souris ergonomique', '3760178690063', null],
            ['REF-004', 'Casque audio BT', '3760178690070', null],
            ['REF-005', 'Webcam HD 1080p', '3760178690087', null],
        ] as [$ref, $des, $ean, $gtin]) {
            $p = (new Product())->setReference($ref)->setDesignation($des)->setEan13($ean)->setGtin($gtin);
            $manager->persist($p);
            $products[] = $p;
        }

        // ── Locations ──
        $locations = [];
        foreach ([
            ['A-01-01-01', 'A', '01', '01', '01'],
            ['A-01-01-02', 'A', '01', '01', '02'],
            ['A-02-02-01', 'A', '02', '02', '01'],
            ['B-01-01-01', 'B', '01', '01', '01'],
            ['B-02-03-01', 'B', '02', '03', '01'],
        ] as [$code, $allee, $rack, $niv, $pos]) {
            $l = (new Location())->setCode($code)->setAllee($allee)->setRack($rack)->setNiveau($niv)->setPosition($pos);
            $manager->persist($l);
            $locations[] = $l;
        }

        // ── Receptions ──
        $receptions = [];
        foreach ([
            ['REC-2026-001', TypeReception::STANDARD],
            ['REC-2026-002', TypeReception::RETOUR],
            ['REC-2026-003', TypeReception::TRANSFERT],
        ] as [$ref, $type]) {
            $r = (new Reception())->setReference($ref)->setTypeReception($type);
            $manager->persist($r);
            $receptions[] = $r;
        }

        $manager->flush();

        // ── Charges (10) ──
        $chargesData = [
            ['CH-2026-00001', $products[0], $clients[0], $locations[0], $receptions[0], TypeUnite::PALETTE, StatutUL::DISPONIBLE, 10.0, 2.0, 'LOT-A001', TypeFlux::CF, EtatUL::GOOD, 850.00],
            ['CH-2026-00002', $products[1], $clients[0], $locations[1], $receptions[0], TypeUnite::COLIS,   StatutUL::DISPONIBLE, 25.0, 5.0, 'LOT-A002', TypeFlux::CF, EtatUL::GOOD, 120.00],
            ['CH-2026-00003', $products[2], $clients[1], $locations[2], $receptions[1], TypeUnite::COLIS,   StatutUL::RESERVE,    15.0, 15.0,'LOT-B001', TypeFlux::RT, EtatUL::GOOD, 65.00],
            ['CH-2026-00004', $products[3], $clients[1], $locations[3], $receptions[1], TypeUnite::UNITE,   StatutUL::DISPONIBLE, 8.0,  0.0, 'LOT-B002', TypeFlux::RT, EtatUL::GOOD, 195.00],
            ['CH-2026-00005', $products[4], $clients[2], $locations[4], $receptions[2], TypeUnite::COLIS,   StatutUL::BLOQUE,     20.0, 0.0, 'LOT-C001', TypeFlux::CF, EtatUL::HS,   45.00],
            ['CH-2026-00006', $products[0], $clients[2], $locations[0], $receptions[0], TypeUnite::PALETTE, StatutUL::DISPONIBLE, 5.0,  1.0, 'LOT-A003', TypeFlux::CF, EtatUL::GOOD, 850.00],
            ['CH-2026-00007', $products[1], $clients[0], $locations[1], $receptions[0], TypeUnite::COLIS,   StatutUL::DISPONIBLE, 30.0, 0.0, 'LOT-A004', TypeFlux::CF, EtatUL::GOOD, 120.00],
            ['CH-2026-00008', $products[2], $clients[1], $locations[2], $receptions[1], TypeUnite::UNITE,   StatutUL::REBUT,      3.0,  0.0, 'LOT-B003', TypeFlux::RT, EtatUL::HS,   65.00],
            ['CH-2026-00009', $products[3], $clients[2], $locations[3], $receptions[2], TypeUnite::COLIS,   StatutUL::DISPONIBLE, 12.0, 3.0, 'LOT-C002', TypeFlux::CF, EtatUL::GOOD, 195.00],
            ['CH-2026-00010', $products[4], $clients[0], $locations[4], $receptions[2], TypeUnite::PALETTE, StatutUL::DISPONIBLE, 6.0,  0.0, 'LOT-C003', TypeFlux::CF, EtatUL::GOOD, 45.00],
        ];

        foreach ($chargesData as [$code, $product, $client, $location, $reception, $typeUnite, $statut, $qte, $qteRes, $lot, $flux, $etat, $prix]) {
            $charge = new Charge();
            $charge->setCodeCharge($code)
                ->setProduct($product)
                ->setOwner($client)
                ->setEmplacement($location)
                ->setReception($reception)
                ->setTypeUnite($typeUnite)
                ->setStatut($statut)
                ->setQuantite($qte)
                ->setQuantiteReservee($qteRes)
                ->setLot($lot)
                ->setTypeFlux($flux)
                ->setEtat($etat)
                ->setPrixAchat($prix)
                ->setTypeReception($reception->getTypeReception())
                ->setCreatedBy('fixtures')
                ->setPoids(random_int(5, 50) / 10)
                ->setDluo(new \DateTimeImmutable('+' . random_int(60, 365) . ' days'));

            if ($statut === StatutUL::REBUT) {
                $charge->marquerRebut();
            } elseif ($statut === StatutUL::BLOQUE) {
                $charge->bloquer();
            }

            $manager->persist($charge);
        }

        $manager->flush();
    }
}
