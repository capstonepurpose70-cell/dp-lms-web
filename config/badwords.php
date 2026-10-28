<?php

/*
| Bad-word filter (App\Support\BadWordFilter) — chat and comments.
|
| Checks whole WORDS, not substrings — so "gagawin" (gaga), "computation" (puta)
| and "tangan" (tanga) are not blocked.
| Still caught: g@g0, p.u.t.a, p u t a, putaaaa, and -ng (gagong, tangang).
*/

return [
    // Whole words (a "-ng" linker is also caught: gagong, tangang)
    'words' => [
        // Filipino
        'putangina', 'tangina', 'kingina', 'puta', 'gago', 'gaga', 'tanga', 'bobo',
        'ulol', 'tarantado', 'bwisit', 'leche', 'pakshet', 'pakyu', 'tae', 'inutil', 'engot',
        // English
        'dick', 'slut', 'whore', 'bastard', 'cunt', 'asshole',
    ],

    // Word starts (fuck → fucking, fucked; shit → shitty)
    'stems' => [
        'fuck', 'motherfuck', 'shit', 'bitch', 'retard',
    ],

    // Parirala (may espasyo)
    'phrases' => [
        'hayop ka',
    ],

    // Not profanity even though a bad word is inside
    'allow' => [
        'leche flan',
        'shiitake',
    ],
];
