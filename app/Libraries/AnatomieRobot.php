<?php

namespace App\Libraries;

/**
 * Zones cliquables de l'image réactive « robot-anatomie.svg » (600×800).
 * Coordonnées au format <area shape="rect"> : x1,y1,x2,y2.
 */
final class AnatomieRobot
{
    public static function zones(): array
    {
        return [
            'tete' => [
                'titre'  => 'Tête — vision et dialogue',
                'coords' => '230,40,370,180',
                'resume' => 'Caméras stéréo, micros et haut-parleur.',
                'detail' => 'Deux caméras mesurent la profondeur pour reconnaître les visages et les objets. Quatre micros repèrent d\'où vient la voix et le robot vous répond en français.',
            ],
            'torse' => [
                'titre'  => 'Torse — énergie et intelligence',
                'coords' => '200,190,400,450',
                'resume' => 'Batterie et ordinateur embarqué.',
                'detail' => 'Une batterie d\'environ 2 kWh offre près de 4 heures d\'autonomie, avec retour automatique à la station de charge. L\'ordinateur embarqué traite vos demandes sans envoyer vos données dans le cloud.',
            ],
            'main-gauche' => [
                'titre'  => 'Main gauche — préhension',
                'coords' => '100,460,200,550',
                'resume' => 'Saisir et porter des objets.',
                'detail' => 'Des doigts articulés et des capteurs de force permettent de saisir un verre sans le briser ou de porter un panier de linge.',
            ],
            'main-droite' => [
                'titre'  => 'Main droite — précision',
                'coords' => '400,460,500,550',
                'resume' => 'Gestes fins et outils.',
                'detail' => 'La main droite manipule les petits objets : boutons, poignées, couverts. Elle apprend de nouveaux gestes quand vous les lui montrez.',
            ],
            'jambes' => [
                'titre'  => 'Jambes — motricité',
                'coords' => '210,460,390,780',
                'resume' => 'Marche stable et escaliers.',
                'detail' => 'Des moteurs à couple élevé et une centrale inertielle assurent l\'équilibre : marche à 1,5 m/s, montée d\'escaliers et rattrapage en cas de bousculade.',
            ],
        ];
    }
}
