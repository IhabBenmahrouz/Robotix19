const test = require('node:test');
const assert = require('node:assert');
const C = require('../../public/js/calendrier.js');

test('la grille d\'octobre 2026 commence le lundi 28 septembre', () => {
    const cases = C.grilleMois(2026, 10);
    assert.strictEqual(cases.length, 42);
    assert.deepStrictEqual(cases[0], { date: '2026-09-28', jour: 28, dansLeMois: false });
    assert.deepStrictEqual(cases[3], { date: '2026-10-01', jour: 1, dansLeMois: true });
    assert.strictEqual(cases.filter((c) => c.dansLeMois).length, 31);
});

test('changement d\'année dans les deux sens', () => {
    assert.deepStrictEqual(C.decalerMois(2026, 12, 1), { annee: 2027, mois: 1 });
    assert.deepStrictEqual(C.decalerMois(2026, 1, -1), { annee: 2025, mois: 12 });
});

const evenements = [
    { id: 1, type: 'demo', idShowroom: 1, debut: '2026-10-08T14:00:00', fin: '2026-10-08T17:00:00' },
    { id: 2, type: 'salon', idShowroom: null, debut: '2026-10-24T09:00:00', fin: '2026-10-25T19:00:00' },
];

test('un événement sur deux jours apparaît les deux jours', () => {
    assert.deepStrictEqual(C.evenementsDuJour(evenements, '2026-10-25').map((e) => e.id), [2]);
    assert.deepStrictEqual(C.evenementsDuJour(evenements, '2026-10-26'), []);
});

test('filtres par type et par showroom', () => {
    assert.deepStrictEqual(C.filtrer(evenements, 'salon', '').map((e) => e.id), [2]);
    assert.deepStrictEqual(C.filtrer(evenements, '', '1').map((e) => e.id), [1]);
    assert.strictEqual(C.filtrer(evenements, '', '').length, 2);
});

test('clé et titre du mois', () => {
    assert.strictEqual(C.cleMois(2026, 3), '2026-03');
    assert.strictEqual(C.titreMois(2026, 10), 'Octobre 2026');
});
