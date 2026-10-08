const test = require('node:test');
const assert = require('node:assert');
const { valider } = require('../../public/js/validation.js');

const cas = {
    nom: [['Hélène', true], ["N'Diaye-Martin", true], ['A', false], ['R2D2', false]],
    email: [['camille@exemple.fr', true], ['camille@exemple', false], ['camille exemple.fr', false]],
    telephone: [['0612345678', true], ['06 12 34 56 78', true], ['06.12.34.56.78', true], ['0012345678', false], ['06123', false]],
    codePostal: [['69002', true], ['6900', false], ['69 002', false]],
    motDePasse: [['Robotix2026!', true], ['robotix2026', false], ['ROBOTIX2026', false], ['Robotix', false], ['Rob1', false]],
    message: [['Bonjour, je voudrais une démonstration.', true], ['Trop court', false]],
};

for (const [regle, exemples] of Object.entries(cas)) {
    test('règle ' + regle, () => {
        for (const [valeur, attendu] of exemples) {
            assert.strictEqual(valider(regle, valeur), attendu, regle + ' : « ' + valeur + ' »');
        }
    });
}

test('une règle inconnue ne bloque pas', () => {
    assert.strictEqual(valider('inexistante', 'x'), true);
});
