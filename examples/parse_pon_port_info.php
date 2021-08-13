<?php
//@TODO Закончить парсить вывод
$output = <<<OUTPUT
gpon-olt_1/2/1 is activate,line protocol is up.
  Description is none.
  The port is activate.
  The port link up/down notification is trap disable.
  The port has 128 onus, the number of registered onus is 15.

Current channel num : 1

OLT statistic:
Input rate :              74036 Bps              556 pps
Output rate:            1754398 Bps             1325 pps
Input Instantaneous bandwidth throughput : 0.0%
Output Instantaneous bandwidth throughput: 0.6%
Input Average bandwidth throughput : 0.2%
Output Average bandwidth throughput: 0.7%
Output Multicast Instantaneous rate:               0 Bps                0 pps
Interface peak rate:
   Input peak rate :           42439165 Bps            25242 pps
   Output peak rate:           39318799 Bps            25700 pps
Total statistic:
 Input :
   PassPackets   :3923186752           DropPackets   :5
   PassBytes     :1478091104010        UnicastsPkts  :3921755369
   MulticastsPkts:493614               BroadcastsPkts:937769
   CRCAlignErrors:5                    OversizePkts  :41919490
   UndersizePkts :0                    CollisionPkts : N/A
   Fragments     : N/A                 Jabbers       : N/A
   64B       :143646445                65-127B   :2593322006
   128-255B  :221775846                256-511B  :52067214
   512-1023B :45505515                 1024-1518B:824950236
 Output :
   PassPackets   :7227007330           DropPackets   :4701744
   PassBytes     :8753870340706        UnicastsPkts  :6944496615
   MulticastsPkts:0                    BroadcastsPkts:281596175
   64B       :335994827                65-127B   :617231126
   128-255B  :198752363                256-511B  :117397864
   512-1023B :115684822                1024-1518B:3852547630
OUTPUT;

$data = [
    'status' => null,
    'line_protocol_status' => null,
    'channel_number' => null,
    'ont_stat' => [
        'has' => null,
        'registered' => null,
    ],
    'olt_statistic' => null,
];



$lines = explode("\n", $output);
foreach ($lines as $num => $line) {
    $line = trim($line);
    if(!$data['status'] && preg_match('/^(gpon|epon).*? is (.*?),line protocol is (.*)\.$/', $output, $match)) {
        $data['status'] = $match[2];
        $data['line_protocol_status'] = $match[3];
        continue;
    }
    if(!$data['channel_number'] && preg_match('/^Current channel num : ([0-9]{1,})$/', $output, $match)) {
        $data['channel_number'] =  (int)$match[1];
        continue;
    }
    if($data['ont_stat']['has'] === null && preg_match('/^The port has ([0-9]{1,}) onus, the number of registered onus is ([0-9]{1,})\.$/', $line, $match)) {
        $data['ont_stat'] = [
            'has' => (int)$match[1],
            'registered' =>  (int)$match[2]
        ];
        continue;
    }
    /**
     * Input rate :              74036 Bps              556 pps
    Output rate:            1754398 Bps             1325 pps
    Input Instantaneous bandwidth throughput : 0.0%
    Output Instantaneous bandwidth throughput: 0.6%
    Input Average bandwidth throughput : 0.2%
    Output Average bandwidth throughput: 0.7%
    Output Multicast Instantaneous rate:               0 Bps                0 pps
     */
    if(preg_match('/^OLT statistic:$/', $line)) {
        $data['olt_statistic'] = [
            'input_rate' => [
                'bps' => null,
                'pps' => null,
            ],
            'output_rate' => [
                'bps' => null,
                'pps' => null,
            ],
            'input_curr_bandwidth_throughput' => null,
            'output_curr_bandwidth_throughput' => null,
            'input_avg_bandwidth_throughput' => null,
            'output_avg_bandwidth_throughput' => null,
        ];
        if(preg_match('/^.*([0-9]{1,}) Bps.*?([0-9]{1,}) pps$/', $lines[$num+1], $match)) {
            $data['olt_statistic']['input_rate']['bps'] = (int)$match[1];
            $data['olt_statistic']['input_rate']['pps'] = (int)$match[1];
        }
        if(preg_match('/^.*([0-9]{1,}) Bps.*?([0-9]{1,}) pps$/', $lines[$num+2], $match)) {
            $data['olt_statistic']['output_rate']['bps'] = (int)$match[1];
            $data['olt_statistic']['output_rate']['pps'] = (int)$match[1];
        }
        if(preg_match('/^.*([0-9]{1,}) Bps.*?([0-9]{1,}) pps$/', $lines[$num+2], $match)) {
            $data['olt_statistic']['output_rate']['bps'] = (int)$match[1];
            $data['olt_statistic']['output_rate']['pps'] = (int)$match[1];
        }
    }

}
