const test = require('node:test');
const assert = require('node:assert');
const A = require('../../public/js/adhesion.js');

const categories = [
    { idCategorieAge: 1, libelle: 'Jeune', ageMin: 18, ageMax: 24, txReduction: 15 },
    { idCategorieAge: 2, libelle: 'Adulte', ageMin: 25, ageMax: 59, txReduction: 0 },
    { idCategorieAge: 3, libelle: 'Senior', ageMin: 60, ageMax: 120, txReduction: 10 },
];

test('âge en années révolues, anniversaire compris', () => {
    assert.strictEqual(A.age('2008-10-05', '2026-10-05'), 18);
    assert.strictEqual(A.age('2008-10-06', '2026-10-05'), 17);
    assert.strictEqual(A.age('2008-02-29', '2026-02-28'), 17);
});

test('catégorie selon l\'âge', () => {
    assert.strictEqual(A.categorie(17, categories), null);
    assert.strictEqual(A.categorie(24, categories).libelle, 'Jeune');
    assert.strictEqual(A.categorie(61, categories).libelle, 'Senior');
});

test('montant réduit arrondi au centime', () => {
    assert.strictEqual(A.montant(99, 15), 84.15);
    assert.strictEqual(A.montant(49, 10), 44.1);
    assert.strictEqual(A.montant(199, 0), 199);
});
