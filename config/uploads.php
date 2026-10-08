<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import Word
    |--------------------------------------------------------------------------
    |
    | Taille maximale du .docx, en kilo-octets. La valeur réellement utilisée
    | est le minimum entre ce plafond et les limites PHP (upload_max_filesize
    | et post_max_size).
    |
    */
    'word_import_max_kilobytes' => (int) env('WORD_IMPORT_MAX_KB', 262144),
];
