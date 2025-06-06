<?php

const PINS_STORE_STATUS_VALUES = [
    'D' => 'Disponível',
    'P' => 'Publicado',
    'A' => 'Ativado',
    'U' => 'Utilizado',
    'B' => 'Bloqueado',
    'T' => 'Transaction',
    'C' => 'Cancelado'
];

const PINS_STORE_STATUS = PINS_STORE_STATUS_VALUES;

echo intval($PINS_STORE_STATUS_VALUES['U']);
