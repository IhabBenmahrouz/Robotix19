<?php

namespace App\Libraries;

use InvalidArgumentException;

/**
 * Génère les visuels SVG des robots et des marques (libres de droits,
 * vectoriels donc nets à toutes les tailles d'écran).
 */
final class IllustrationRobot
{
    public static function robot(string $couleur, int $variante): string
    {
        self::verifierCouleur($couleur);

        $brasDroit = $variante === 2
            ? '<rect x="273" y="60" width="32" height="125" rx="16" fill="#cbd5e1"/><circle cx="289" cy="55" r="18" fill="' . $couleur . '"/>'
            : '<rect x="273" y="175" width="32" height="130" rx="16" fill="#cbd5e1"/><circle cx="289" cy="315" r="18" fill="' . $couleur . '"/>';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="400" height="500">'
            . '<defs><linearGradient id="fond" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0" stop-color="#0b1020"/><stop offset="1" stop-color="#1e293b"/></linearGradient></defs>'
            . '<rect width="400" height="500" fill="url(#fond)"/>'
            . '<circle cx="200" cy="250" r="170" fill="' . $couleur . '" opacity=".15"/>'
            // Tête
            . '<rect x="150" y="60" width="100" height="90" rx="40" fill="#e2e8f0"/>'
            . '<rect x="165" y="90" width="70" height="30" rx="15" fill="#0b1020"/>'
            . '<circle cx="185" cy="105" r="7" fill="' . $couleur . '"/><circle cx="215" cy="105" r="7" fill="' . $couleur . '"/>'
            . '<rect x="190" y="150" width="20" height="15" fill="#94a3b8"/>'
            // Torse
            . '<rect x="135" y="165" width="130" height="150" rx="30" fill="#e2e8f0"/>'
            . '<circle cx="200" cy="225" r="18" fill="' . $couleur . '"/>'
            // Bras
            . '<rect x="95" y="175" width="32" height="130" rx="16" fill="#cbd5e1"/><circle cx="111" cy="315" r="18" fill="' . $couleur . '"/>'
            . $brasDroit
            // Jambes
            . '<rect x="148" y="320" width="45" height="140" rx="20" fill="#cbd5e1"/>'
            . '<rect x="207" y="320" width="45" height="140" rx="20" fill="#cbd5e1"/>'
            . '<rect x="140" y="455" width="60" height="18" rx="9" fill="' . $couleur . '"/>'
            . '<rect x="200" y="455" width="60" height="18" rx="9" fill="' . $couleur . '"/>'
            . '</svg>';
    }

    public static function logo(string $initiales, string $couleur): string
    {
        self::verifierCouleur($couleur);
        $initiales = htmlspecialchars($initiales, ENT_XML1);

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" width="200" height="80">'
            . '<rect width="200" height="80" rx="16" fill="#0b1020"/>'
            . '<text x="100" y="52" font-family="Arial, sans-serif" font-size="34" font-weight="700" text-anchor="middle" fill="' . $couleur . '">' . $initiales . '</text>'
            . '</svg>';
    }

    private static function verifierCouleur(string $couleur): void
    {
        if (! preg_match('/^#[0-9a-f]{6}$/i', $couleur)) {
            throw new InvalidArgumentException("Couleur invalide : $couleur");
        }
    }
}
