const test = require('node:test');
const assert = require('node:assert');
const { mettreAEchelle } = require('../../public/js/image-map.js');

test('réduit les coordonnées de moitié', () => {
    assert.strictEqual(mettreAEchelle('100,200,300,400', 0.5), '50,100,150,200');
});

test('arrondit au pixel le plus proche', () => {
    assert.strictEqual(mettreAEchelle('230,40,370,180', 0.55), '127,22,204,99');
});

test('ratio 1 : coordonnées inchangées', () => {
    assert.strictEqual(mettreAEchelle('210,460,390,780', 1), '210,460,390,780');
});
