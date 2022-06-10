<?php
namespace SwitcherCore\Dev;

use IPv4\SubnetCalculator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetDescrByNetwork extends AbstractCommand
{
    function configure()
    {
        $this->setName('snmp:scan')
            ->addArgument("network")
            ->addArgument("community")
            ->setDescription("Scan network over snmp");
    }
    function exec(InputInterface $input, OutputInterface $output)
    {
        list($ip, $network) = explode("/", $this->input->getArgument('network'));
        $generator = (new SubnetCalculator($ip, $network))->getAllIPAddresses();
        while($ip = $generator->current()) {
            $descr = @snmpget($ip, $input->getArgument('community'), ".1.3.6.1.2.1.1.1.0", 100000, 2);
           if(!$descr) {
                $generator->next();
                continue;
            }
            $descr = trim(str_replace(["STRING: ", "\"", "\n", "\r"], ['', '', ' ', ''], $descr));
            $output->writeln("{$ip}: {$descr}");
            $generator->next();
        }
        return 0;
    }
}
