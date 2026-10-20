<?php

/*
| Bad-word filter (App\Support\BadWordFilter) — chat at comments.
|
| Buong SALITA ang tsine-check, hindi substring — kaya hindi nahaharang ang
| "gagawin" (gaga), "computation" (puta), "tangan" (tanga).
| Nahuhuli pa rin: g@g0, p.u.t.a, p u t a, putaaaa, at -ng (gagong, tangang).
*/

return [
    // Eksaktong salita (puwedeng may "-ng" na linker: gagong, tangang)
    'words' => [
        // Filipino
        'putangina', 'tangina', 'kingina', 'puta', 'gago', 'gaga', 'tanga', 'bobo',
        'ulol', 'tarantado', 'bwisit', 'leche', 'pakshet', 'pakyu', 'tae', 'inutil', 'engot',
        // English
        'dick', 'slut', 'whore', 'bastard', 'cunt', 'asshole',
    ],

    // Simula ng salita (fuck → fucking, fucked; shit → shitty)
    'stems' => [
        'fuck', 'motherfuck', 'shit', 'bitch', 'retard',
    ],

    // Parirala (may espasyo)
    'phrases' => [
        'hayop ka',
    ],

    // Hindi mura kahit may masamang salita sa loob
    'allow' => [
        'leche flan',
        'shiitake',
    ],
];
