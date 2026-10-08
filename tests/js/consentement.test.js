const test = require('node:test');
const assert = require('node:assert');
const C = require('../../public/js/consentement.js');

test('pas de cookie : pas de consentement', () => {
    assert.strictEqual(C.lire(''), null);
    assert.strictEqual(C.lire('ci_session=abc; autre=1'), null);
});

test('aller-retour : sérialiser puis relire', () => {
    const choix = C.choix(true, false, true, '2026-10-05');
    const ligne = C.serialiser(choix);
    assert.match(ligne, /Max-Age=\d+; Path=\/; SameSite=Lax$/);
    assert.deepStrictEqual(C.lire('ci_session=abc; ' + ligne.split(';')[0]), choix);
});

test('tout accepter / tout refuser', () => {
    assert.deepStrictEqual(C.toutAccepter('2026-10-05'), { v: 1, preferences: true, statistiques: true, tiers: true, date: '2026-10-05' });
    assert.deepStrictEqual(C.toutRefuser('2026-10-05'), { v: 1, preferences: false, statistiques: false, tiers: false, date: '2026-10-05' });
});

test('cookie corrompu ou ancienne version : traité comme absent', () => {
    assert.strictEqual(C.lire('robotix_consentement=%7Bpas-du-json'), null);
    assert.strictEqual(C.lire('robotix_consentement=' + encodeURIComponent('{"v":0,"tiers":true}')), null);
    assert.strictEqual(C.lire('robotix_consentement=' + encodeURIComponent('{"v":1,"tiers":"oui"}')), null);
});
