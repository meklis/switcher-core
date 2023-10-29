<?php

use SwitcherCore\Modules\Helper;

class OidDatabaseTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \SwitcherCore\Config\ModelCollector
     */
    private $models;

    protected function setUp(): void
    {
        $this->expectNotToPerformAssertions();
        $this->models = \SwitcherCore\Config\ModelCollector::init(new \SwitcherCore\Config\Reader(Helper::getBuildInConfig()));
        parent::setUp();
    }


    /**
     * @test
     */
    public function oidIdsIsDuplicated()
    {
        foreach ($this->models->getAllModelKeys() as $key) {

            $collector = \SwitcherCore\Config\OidCollector::init(
                new \SwitcherCore\Config\Reader(Helper::getBuildInConfig())
            );
            $oids = $collector->readEnterpriceOids($this->models->getModelByKey($key));
            $duplited = [];
            foreach ($oids->getOids() as $oid) {
                $duplited[$oid->getOid()][] = $oid;
            }
            foreach ($duplited as $oid => $dupl) {
                if (count($dupl) > 1) {
                    $keys = join(", ", array_map(function ($m) {
                        return $m->getName();
                    }, $dupl));
                    $this->addWarning("Oid {$oid} in model {$key} duplicated with names - $keys");
                }
            }
        }
        return true;
    }

    /**
     * @test
     */
    public function oidNamesHasDuplicated()
    {
        $collector = \SwitcherCore\Config\OidCollector::init(
            new \SwitcherCore\Config\Reader(Helper::getBuildInConfig())
        );
        foreach ($this->models->getAllModelKeys() as $key) {
            $oids = $collector->readEnterpriceOids($this->models->getModelByKey($key));
            $duplited = [];
            foreach ($oids->getOids() as $oid) {
                $duplited[$oid->getName()][] = $oid;
            }
            foreach ($duplited as $name => $dupl) {
                $dupl = array_unique(array_map(function ($d) {
                    return $d->getOid();
                }, $dupl));
                if (count($dupl) > 1) {
                    $keys = join(", ", $dupl);
                    $this->addWarning("Oid with name {$name} duplicated in model {$key} with different oids - {$keys} ");
                }
            }
        }
         $this->setResult("Good");
    }


}