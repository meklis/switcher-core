<?php
/**
 * Функция для декодирования VLAN ID из IVID.
 *
 * @param int $ivid Internal VLAN ID.
 * @return int Декодированный VLAN ID.
 */
function decodeVlanId($ivid) {
    return $ivid & 0xFFF;
}

// Массив IVID для теста
$ividList = [131072, 196608, 262144, 327680, 393216, 458752, 524288];

foreach ($ividList as $ivid) {
    $vlanId = decodeVlanId($ivid);
    echo "IVID: $ivid -> VLAN ID: $vlanId" . PHP_EOL;
}