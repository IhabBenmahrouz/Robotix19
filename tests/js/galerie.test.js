const test = require('node:test');
const assert = require('node:assert');
const { indexSuivant, indexPrecedent } = require('../../public/js/galerie.js');

test('passe à l\'image suivante puis revient au début', () => {
    assert.strictEqual(indexSuivant(0, 3), 1);
    assert.strictEqual(indexSuivant(2, 3), 0);
});

test('revient à la dernière image depuis la première', () => {
    assert.strictEqual(indexPrecedent(0, 3), 2);
    assert.strictEqual(indexPrecedent(1, 3), 0);
});
