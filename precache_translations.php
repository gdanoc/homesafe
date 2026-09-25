<?php
require_once 'translator.php';

$common_phrases = [
    'Iniciar Sesión',
    'Registrarse',
    'Cerrar Sesión',
    'Propiedades',
    'Muebles',
    'Sobre nosotros',
    'Inicio',
    'Contraseña',
    'E-mail',
];

echo "<h1>Pre-cacheando traducciones</h1>";

foreach ($common_phrases as $phrase) {
    $translated = tr($phrase);
    echo "Español: '$phrase' → Inglés: '$translated'<br>";
}

echo "<p>¡Traducciones pre-cacheadas con éxito!</p>";