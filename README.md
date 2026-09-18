# Switcher-Core

PHP library providing a single, vendor-agnostic interface for network equipment: switches, OLTs, and routers.

[Русская версия](README.ru.md)

## Overview

Switcher-Core abstracts away vendor-specific protocols and command sets behind one API. Instead of writing a wrapper for every vendor, you call a named **module** (e.g. `system`, `fdb`, `vlans`) against a `Device`, and the library:

1. Detects the device model from SNMP `sysDescr` / `sysObjectID` (or an explicit model key).
2. Loads the matching YAML configuration (OIDs, module bindings) for that model.
3. Dispatches the call to the vendor-specific module implementation, or a shared/general one when no vendor-specific implementation exists.

The set of modules supported by a given device, and the exact shape of the response, depend on the device's capabilities and vendor. This is a deliberate trade-off: a single strict schema across all vendors is not achievable for heterogeneous network hardware. See the [field naming convention](#response-field-convention) below for how the library keeps responses as consistent as practical.

If your equipment is not in the supported list, implement the module classes and configuration described in [Module Development](docs/MODULE_DEVELOPMENT.md) and contribute it, or maintain it in a fork.

## Supported transports

* Telnet
* SSH
* SNMP (v2c only)
* RouterOS API (without SSL)

## Supported vendors

- [x] D-Link switches
- [x] Huawei switches
- [x] EdgeCore switches
- [x] Huawei OLTs
- [x] BDCOM OLTs
- [x] ZTE OLTs
- [x] C-Data OLTs
- [x] V-Solution OLTs
- [x] Mikrotik routers
- [x] Cisco switches (basic)
- [x] GCOM OLTs
- [x] Alcatel switches (basic)
- [x] Eltex switches (basic)
- [x] HP switches (basic)
- [x] Dell switches (basic)
- [x] Allied Telesis switches (basic)
- [x] TP-Link switches (basic)
- [x] Juniper switches (basic)
- [x] Raisecom switches (basic)
- [ ] Extreme routers
- [ ] Cisco routers

* [Full device and module compatibility matrix](docs/DEVICES.md)
* [Module reference](docs/MODULES.md)
* [Per-device module and response reference (HTML)](https://htmlpreview.github.io/?https://raw.githubusercontent.com/meklis/switcher-core/master/docs/device_info.html)

## Requirements

* PHP >= 7.2
* PHP extensions: `yaml`, `zip`, `curl`, `json`, `mbstring`, `snmp`, `sockets`, `ssl`

## Installation

```bash
composer require meklis/switcher-core
```

## Quick start

```php
<?php
require __DIR__ . "/vendor/autoload.php";

use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\PhpCache;

$deviceIp = '127.0.0.1';
$deviceCommunity = 'public';
$deviceLogin = 'login';
$devicePassword = 'password';

$coreConnector = new CoreConnector(
    // Path to the library's built-in configuration directory.
    // Copy configs/ out of vendor/meklis/switcher-core if you need to customize it.
    Helper::getBuildInConfig()
);

$connector = ($coreConnector)
    // Optional, but recommended. Use a shared cache (e.g. Memcached) in production.
    ->setCache(new PhpCache());

$core = $connector->init(
    // init() detects the model and returns a ready-to-use Device instance.
    Device::init($deviceIp, $deviceCommunity, $deviceLogin, $devicePassword)
        // Connection parameters, shown here with their defaults.
        ->set('consoleConnectionType', Device::CONSOLE_TELNET)
        ->set('consoleTimeout', 10)
        ->set('consolePort', 23)
        ->set('snmpRepeats', 3)
        ->set('snmpTimeoutSec', 2)
        ->set('mikrotikApiPort', 8728)
);

echo json_encode($core->action('system'), JSON_PRETTY_PRINT);
```

Example output of the `system` module (exact fields vary by vendor):

```json
{
    "descr": "RouterOS RB952Ui-5ac2nD",
    "uptime": "8d 9h 55min 32sec",
    "contact": "",
    "name": "G_OfficeMik",
    "location": "",
    "meta": {
        "name": "Mikrotik RB952Ui-5ac2nD",
        "detect": {
            "description": "^RouterOS RB952Ui-5ac2nD$",
            "objid": "^.1.3.6.1.4.1.14988.1$"
        },
        "ports": 0,
        "extra": [],
        "modules": [
            "system",
            "arp_info",
            "arp_ping",
            "interface_vlan_info",
            "dhcp_server_info",
            "lease_info",
            "ctrl_static_arp",
            "ctrl_static_lease"
        ]
    }
}
```

## Response field convention

Some modules (e.g. `fdb`, `pon_onts_status`) are implemented differently across vendors and platforms, so their output cannot be perfectly uniform. The library follows one rule consistently:

* A field with **no leading underscore** is part of the module's common contract: same name, same meaning, across every implementation of that module.
* A field **prefixed with `_`** is platform- or vendor-specific and not part of the common contract.
* When two platforms report semantically the same value under different names, the common field uses a neutral name, and each vendor's original field name is kept alongside it with a leading underscore (e.g. a common `ident` field, plus `_serial` on one platform and `_mac` on another).

Interface objects (`interface` / `interfaces`) follow their own, already-established convention: `id`, `name`, `type`, `parent` are common; `_port`, `_slot`, `_onu`, `_snmp_id`, `_technology`, `_type`, etc. are platform-specific.

See [Module Development](docs/MODULE_DEVELOPMENT.md#response-field-naming-convention) for details and examples.

## Repository layout

| Path | Purpose |
|---|---|
| `src/Switcher/` | Core, connectors, `Device`, console wrappers, cache. |
| `src/Config/` | YAML readers and collectors for models, OIDs, modules, traps. |
| `src/Modules/` | Module implementations, one namespace per vendor/platform. |
| `configs/modules.yml` | Registry of all modules and their arguments. |
| `configs/models/` | Device model definitions: detection rules and module bindings. |
| `configs/oids/` | SNMP OID databases, grouped by vendor/platform. |
| `configs/traps/` | SNMP trap configuration. |
| `docs/` | Generated device/module documentation and examples. |
| `src/Dev/`, `bin/console` | Local development console for calling modules and inspecting support. |
| `tests/` | Automated tests. Coverage is currently limited to config consistency checks, not real-device behavior. |

## Developing and extending modules

Adding support for a new device model, or a new module, is a configuration-plus-code task: OID files, a model definition, and (when no existing module fits) a PHP class. This is documented in full in [Module Development](docs/MODULE_DEVELOPMENT.md).

## Testing

```bash
composer tests
```

This runs the PHPUnit suite (`tests/`), which currently checks configuration consistency (OIDs, models, modules). It does **not** verify behavior against real hardware — automated tests here are not a substitute for testing against an actual device. See [Module Development](docs/MODULE_DEVELOPMENT.md#testing-against-real-devices) for how to do that with `bin/console`.

## Used in

[wildcore.tools](https://wildcore.tools)

## License

[MIT](LICENSE)
