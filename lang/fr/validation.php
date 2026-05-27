<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => ':attribute doit être une adresse e-mail valide.',
    'url' => ':attribute doit être une URL valide.',
    'regex' => 'Le format de :attribute est invalide.',
    'max' => [
        'file' => ':attribute ne doit pas dépasser :max kilo-octets.',
        'string' => ':attribute ne doit pas dépasser :max caractères.',
    ],
    'image' => ':attribute doit être une image.',
    'mimes' => ':attribute doit être un fichier de type : :values.',
    'uploaded' => ':attribute n’a pas pu être téléversé.',
    'unique' => ':attribute est déjà utilisé.',
    'confirmed' => ':attribute ne correspond pas à la confirmation.',
    'min' => [
        'string' => ':attribute doit contenir au moins :min caractères.',
    ],
    'attributes' => [],
];
