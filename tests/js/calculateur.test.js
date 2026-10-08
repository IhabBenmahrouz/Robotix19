const test = require('node:test');
const assert = require('node:assert');
const { calculer, mensualiteCredit, normaliserQuantite, quantiteValide } = require('../../public/js/calculateur.js');

const g1 = { prixHt: 13900, tauxTva: 20, quantite: 1, options: [], mois: 1, tauxAnnuel: 0 };

test('robot seul, payé comptant', () => {
    const r = calculer(g1);
    assert.deepStrictEqual(
        [r.totalHt, r.tva, r.totalTtc, r.mensualite, r.coutCredit],
        [13900, 2780, 16680, 16680, 0],
    );
});

test('garantie (12 % du prix HT) et livraison (290 € HT)', () => {
    const r = calculer({ ...g1, options: [{ type: 'pourcentage', valeur: 12 }, { type: 'fixe', valeur: 290 }] });
    assert.strictEqual(r.optionsHt, 1958);
    assert.strictEqual(r.totalHt, 15858);
    assert.strictEqual(r.tva, 3171.6);
    assert.strictEqual(r.totalTtc, 19029.6);
});

test('la quantité multiplie robot et options', () => {
    const r = calculer({ ...g1, quantite: 2, options: [{ type: 'pourcentage', valeur: 12 }, { type: 'fixe', valeur: 290 }] });
    assert.strictEqual(r.totalHt, 31716);
    assert.strictEqual(r.totalTtc, 38059.2);
});

test('12 mois sans frais : capital divisé par 12', () => {
    assert.strictEqual(mensualiteCredit(12000, 0, 12), 1000);
});

test('24 mois à 3,9 % : mensualité d\'un prêt amortissable', () => {
    assert.ok(Math.abs(mensualiteCredit(10000, 3.9, 24) - 433.80) < 0.05);
});

test('le coût du crédit est positif avec intérêts', () => {
    const r = calculer({ ...g1, mois: 36, tauxAnnuel: 5.9 });
    assert.ok(r.coutCredit > 0);
    assert.strictEqual(r.totalTtc, 16680);
});

test('quantités absurdes ramenées entre 1 et 5', () => {
    assert.deepStrictEqual(['0', '-3', 'abc', '99', '3', ''].map(normaliserQuantite), [1, 1, 1, 5, 3, 1]);
    assert.deepStrictEqual(['0', '-3', 'abc', '99', '3', '2.5'].map(quantiteValide), [false, false, false, false, true, false]);
});
