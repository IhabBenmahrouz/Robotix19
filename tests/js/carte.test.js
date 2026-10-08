const test = require('node:test');
const assert = require('node:assert');
const C = require('../../public/js/carte.js');

const showrooms = [
    { id: 1, ville: 'Lyon', latitude: 45.7612, longitude: 4.8562 },
    { id: 2, ville: 'Marseille', latitude: 43.2965, longitude: 5.3698 },
    { id: 3, ville: 'Paris', latitude: 48.8734, longitude: 2.3335 },
];

test('URL de carte intégrée', () => {
    assert.strictEqual(C.urlCarte(48.8734, 2.3335, 15), 'https://maps.google.com/maps?q=48.8734,2.3335&z=15&output=embed');
});

test('URL d\'itinéraire', () => {
    assert.strictEqual(C.urlItineraire(43.2965, 5.3698), 'https://www.google.com/maps/dir/?api=1&destination=43.2965,5.3698');
});

test('distance Paris – Lyon d\'environ 390 km', () => {
    const d = C.distanceKm(48.8566, 2.3522, 45.764, 4.8357);
    assert.ok(d > 385 && d < 400, 'distance obtenue : ' + d);
});

test('le showroom le plus proche d\'Aix-en-Provence est Marseille', () => {
    const r = C.plusProche({ lat: 43.5297, lng: 5.4474 }, showrooms);
    assert.strictEqual(r.showroom.ville, 'Marseille');
    assert.ok(r.distance < 30);
});

test('le zoom reste entre 5 et 20', () => {
    assert.deepStrictEqual([C.bornerZoom(25), C.bornerZoom(2), C.bornerZoom(12)], [20, 5, 12]);
});
