<?php
$incomming="           OLT                  ONU              Attenuation
--------------------------------------------------------------------------
 up      Rx :-23.744(dbm)      Tx:2.497(dbm)        26.241(dB)

 down    Tx :6.679(dbm)        Rx:-20.400(dbm)      27.079(dB)";

$lines = explode("\n", $incomming);
$data = [];
foreach ($lines as $line) {
    if(preg_match('/^(up|down)[ ]{1,}(Rx|Tx)[ ]{1,}?:(-?[0-9]{1,3}\.?[0-9]{1,}?\(dbm\)|(N\/A))[ ]{1,}(Rx|Tx).*?:(-?[0-9]{1,3}\.?[0-9]{1,}?\(dbm\)|(N\/A)).*?(-?[0-9]{1,3}\.?[0-9]{1,}?\(dB\)|(N\/A))$/', trim($line), $match)) {
        $data[$match[1]]['olt_' . strtolower($match[2])] = is_numeric(str_replace('(dbm)','', $match[3])) ? (float)str_replace('(dbm)','', $match[3]) : null;
        $data[$match[1]]['onu_' .strtolower($match[5])] = is_numeric(str_replace('(dbm)','', $match[6])) ? (float)str_replace('(dbm)','', $match[6]) : null;
        $data[$match[1]]['attenuation'] = is_numeric(str_replace('(dB)', '', $match[8])) ? (float)str_replace('(dB)', '', $match[8]) : null;
    }
}