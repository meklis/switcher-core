# Module Development

This document describes how Switcher-Core is structured internally, and how to add support for a new device model or a new module. It assumes you have read the [README](../README.md).

## Contents

* [Core concepts](#core-concepts)
* [Request flow](#request-flow)
* [Configuration files](#configuration-files)
  * [`configs/modules.yml`](#configsmodulesyml)
  * [`configs/models/*.yml`](#configsmodelsyml)
  * [`configs/oids/**/*.yml`](#configsoidsyml)
* [The module contract (`AbstractModule`)](#the-module-contract-abstractmodule)
* [Adding support for a new device model](#adding-support-for-a-new-device-model)
* [Adding a new module implementation](#adding-a-new-module-implementation)
* [Console (Telnet/SSH) modules](#console-telnetssh-modules)
* [Response field naming convention](#response-field-naming-convention)
* [Testing against real devices](#testing-against-real-devices)
* [Style and conventions](#style-and-conventions)

## Core concepts

* **Device** (`src/Switcher/Device.php`) — connection parameters for one physical device: IP, SNMP community, credentials, transport settings.
* **Model** (`src/Config/Objects/Model.php`, defined in `configs/models/*.yml`) — describes one device model: how to detect it, which OID files it needs, and which module implementation class handles each module for it.
* **Module** — a named capability (`system`, `fdb`, `vlans`, `pon_onts_status`, ...) registered in `configs/modules.yml` and implemented by one or more PHP classes under `src/Modules/<Vendor>/`.
* **Core** (`src/Switcher/Core.php`) — the runtime object bound to one detected device; `$core->action('module_name', $params)` is the entry point applications use.
* **CoreConnector** (`src/Switcher/CoreConnector.php`) — builds a `Core` for a given `Device`: runs detection, loads the model's config, wires up the DI container.

## Request flow

1. `CoreConnector::init(Device $device)` connects (SNMP by default) and reads `sysDescr` / `sysObjectID` / interface count.
2. `ModelCollector::getModelByDetect(...)` matches these against the `detect` block of every model in `configs/models/`, and returns the matching `Model`.
3. `OidCollector` loads the global OID set plus every file listed under the model's `oids:` key.
4. A DI container is built with the model, OID collector, SNMP client, console client, cache, and logger injected into modules via `@Inject`.
5. `$core->action('module_name', $params)` resolves the module class registered for `module_name` under this model's `modules:` map (see [`configs/models/*.yml`](#configsmodelsyml)), instantiates it through the container, calls `run($params)`, then `getPretty()` or `getPrettyFiltered($filter)`.

## Configuration files

### `configs/modules.yml`

The global registry of modules. Every module callable via `$core->action(...)` must be listed here, even if a given model does not implement it (the model simply won't map it).

```yaml
- name: fdb
  arguments:
    - {name: interface, pattern: '^.*$', required: no}
    - {name: mac, pattern: '.*', required: no}
    - {name: vlan_id, pattern: '^[0-9]{1,4}$', required: no}
  descr: FDB-table (user mac addresses)
```

* `name` — the module key used in `$core->action('name')` and in a model's `modules:` map.
* `arguments` — optional, validated by regex `pattern`; `required: yes` fails fast if the caller omits it.
* `depends` — optional list of other module names this module relies on (see `vlans_by_port` depending on `vlans`).
* `descr` — one-line description, surfaced in generated docs and `bin/console modules`.

If you add a new module (a new capability, not just a new vendor implementation of an existing one), register it here first.

### `configs/models/*.yml`

One YAML file per vendor family, containing a list of models. Example (EdgeCore ECS4120-28F):

```yaml
- name: Edge-core ECS4120-28F
  key: edgecore_ecs4120_28f   # must be globally unique
  ports: 26                   # optional
  device_type: SWITCH         # optional
  inputs:                     # transports this model uses; also: console, routeros_api
    - snmp
  detect: {description: ^ECS4120-28F, objid: .1.3.6.1.4.1.259.10.1.45.103}
  oids:
    - ./oids/edgecore/ecs4120.yml
  modules:
    system: \SwitcherCore\Modules\Edgecore\System
    fdb: \SwitcherCore\Modules\Edgecore\Fdb
    vlans: \SwitcherCore\Modules\Edgecore\VlansDot1q
    # ...
```

Key fields:

* `key` — unique model identifier. Used for explicit model selection (`Device::setModelKey()`) and caching.
* `detect` — `description` (regex against `sysDescr`) and `objid` (regex against `sysObjectID`); both must match for auto-detection. An optional `ifaces_count` can narrow detection further for ambiguous vendors.
* `inputs` — which transports the model needs. `snmp` is assumed for most modules; add `console` when any module for this model requires Telnet/SSH, and `routeros_api` for Mikrotik's API.
* `oids` — OID files to load for this model, resolved relative to `configs/models/`.
* `modules` — maps a module name (must exist in `configs/modules.yml`) to the PHP class implementing it for this model. A model only supports the modules listed here.

### `configs/oids/**/*.yml`

OID databases, one file (or a small set of files) per device family, referenced from `configs/models/*.yml`. Each entry:

```yaml
- {name: sys.serialNum, oid: .1.3.6.1.4.1.259.10.1.45.1.1.3.1.10, access: read}
- {name: cable_diag.resultPair1Status, oid: .1.3.6.1.4.1.259.10.1.45.1.2.3.2.1.2,
   values: {1: NotTested, 2: OK, 3: Open, 4: Short}}
```

* `name` — the symbolic name modules use via `$this->oids->getOidByName('sys.serialNum')` or `$this->getResponseByName('sys.serialNum')`. Reuse existing names (`sys.*`, `if.*`, `port.*`, ...) where the semantics match, instead of inventing new ones, so shared/general modules keep working.
* `oid` — the numeric OID, without a trailing `.0` (index/table handling is done by the SNMP layer).
* `values` — optional integer-to-label map, used to decode enumerations.

Global, vendor-independent OIDs (`sys.*`, `if.*`, etc.) are loaded for every device regardless of model; vendor files only need to add OIDs that are specific to that platform.

## The module contract (`AbstractModule`)

Every module class extends `SwitcherCore\Modules\AbstractModule` (`src/Modules/AbstractModule.php`) directly or through a shared base class such as `SwitcherCore\Modules\General\Switches\System`. It must implement:

```php
abstract class AbstractModule
{
    public abstract function run($params = []);
    public abstract function getPretty();
    public abstract function getPrettyFiltered($filter = []);
}
```

* **`run($params)`** — performs the actual SNMP walk / console command / RouterOS API call, and stores the raw result on `$this->response`. Return `$this` to allow chaining (`->run($p)->getPretty()`).
* **`getPretty()`** — transforms `$this->response` into the module's public output shape (plain array, JSON-serializable).
* **`getPrettyFiltered($filter)`** — same as `getPretty()`, but applies caller-supplied filtering (e.g. by `interface`); when a module has nothing to filter, it may simply call `getPretty()`.

Available injected dependencies (via `@Inject` PHPDoc annotations, resolved by PHP-DI):

| Property | Type | Purpose |
|---|---|---|
| `$this->oids` | `OidCollector` | Look up OIDs by name (`getOidByName`), or by regex (`getOidsByRegex`). |
| `$this->snmp` | `MultiWalkerInterface` | Issue SNMP walk/get calls. |
| `$this->model` | `Model` | The detected device model (key, type, ports, module list, ...). |
| `$this->device` | `Device` | Connection parameters for the current device. |
| `$this->container` | `DI\Container` | Resolve other modules (`$this->getModule('vlans')`) or services. |
| `$this->logger` | `Monolog\Logger` | Structured logging. |

Useful helpers already on `AbstractModule`:

* `formatResponse($snmpResponse)` — turns raw SNMP poller responses into `WrappedResponse` objects keyed by OID name; use this at the end of `run()`.
* `getResponseByName($name)` — fetch a `WrappedResponse` by OID name from `$this->response`, throwing `IncompleteResponseException` if missing (typically caught to treat a field as optional).
* `getModule($name)` — resolve and reuse another module through the DI container (e.g. `vlans_by_port` reusing `vlans`).
* `getCache($key)` / `setCache($key, $value, $timeout)` — per-device, per-module cache, backed by the configured `CacheInterface`.
* `convertHexToString(...)` / `convertHexToStringWithoutDelimiter(...)` — decode SNMP hex-string values (e.g. serials, MACs).
* `rawConsoleCommandRun($params)` / `multiRawConsoleCommandRun($params)` — for console-driven modules, see [below](#console-telnetssh-modules).

## Adding support for a new device model

1. **Confirm detection.** Get `sysDescr` and `sysObjectID` from the device (see [Testing against real devices](#testing-against-real-devices)) and check they don't already collide with an existing model's `detect` block.
2. **Add or extend an OID file** under `configs/oids/<vendor>/`, reusing standard names (`sys.*`, `if.*`, ...) wherever the semantics match a global OID, and adding platform-specific OIDs under a sensibly namespaced name.
3. **Add a model entry** to `configs/models/<Vendor>.yml` (or create the file) with a unique `key`, correct `detect`, `oids`, and `inputs`.
4. **Bind modules.** For each module you want this model to support, either reuse an existing implementation class (if the vendor's SNMP layout is compatible) or point it at a new class you write (next section).
5. **Regenerate docs** (optional but recommended for PRs): see `docs/generate-device-list-with-modules.php` and `docs/generate-modules-list.php`, which produce `docs/DEVICES.md` and `docs/MODULES.md`.
6. **Test against the real device** — see below. Do not consider the model done based on `php -l` or unit tests alone.

## Adding a new module implementation

1. Check `configs/modules.yml` for an existing module name that fits; register a new one there only if this is genuinely a new capability.
2. Look at an existing implementation of the same module for a similar vendor (e.g. another SNMP-based switch) and match its structure — class layout, method names, response shape — rather than inventing a new style. Shared logic across a vendor family usually lives in a base class (see `src/Modules/General/Switches/System.php`, extended by `src/Modules/Edgecore/System.php`).
3. Implement `run()`:
   * SNMP: build a list of `Oid::init(...)` from `$this->oids`, call `$this->snmp->walk(...)` or `->get(...)`, and store `$this->formatResponse($result)`.
   * Console: see [Console modules](#console-telnetssh-modules).
4. Implement `getPretty()` / `getPrettyFiltered()` to shape the response, following the [field naming convention](#response-field-naming-convention).
5. Add the class to the model's `modules:` map in `configs/models/*.yml`.
6. Run it against a real device with `bin/console` and confirm the output.

## Console (Telnet/SSH) modules

SNMP is the default and preferred transport, but it is not a fallback of last resort — console access is a first-class option, used whenever SNMP cannot expose the needed data (for example, per-ONU detail on some OLT platforms).

For models that need console access, set `inputs: [snmp, console]` (or just `console`) in the model definition, and `\Device::CONSOLE_TELNET` / `\Device::CONSOLE_SSH` as the connection type. Inside a module, `$this->console` (when the module opts into console access) exposes the connected client; `AbstractModule` provides:

* `rawConsoleCommandRun($params)` — runs `$params['command']`, with inline directives supported in the command string: `<cr>` (send a bare carriage return after), `<prompt="...">` (wait for a custom prompt), `<confirm="yes">` (answer a Y/N prompt), `<confirm-if="prompt","confirm">`, `<stream_timeout=N>`.
* `multiRawConsoleCommandRun($params)` — runs `$params['commands']` (array or newline-separated string) sequentially via the `console_command` module, supporting `<sleep=N>`, `<exception="...">` to abort, `<f>` to force success, and inline `<prompt="...">` per command. Stops on the first failure unless `break_on_error: no`.

Reuse these instead of talking to the console client directly, so timeout/prompt handling stays consistent across vendors.

## Response field naming convention

Applies to `getPretty()` / `getPrettyFiltered()` output for any module implemented differently across vendors/platforms (interface objects have their own established convention, noted below).

* **No leading underscore** — the field is part of the module's common contract: same name, same meaning, in every platform's implementation of that module.
* **Leading `_`** — the field is specific to one platform/vendor and not part of the common contract.
* **Same value, different vendor names** — introduce one common, neutral field name for the shared meaning, and keep each vendor's original name alongside it, prefixed with `_`, for backward compatibility and precision.

Worked example — `pon_onts_blacklist`, implemented differently for GPON (`FD16xxV3\OntBlacklist`, SN-based) and EPON (`CData\OntBlacklist`, port-based):

```php
// Common to both platforms:
'index'      => $index,
'interfaces' => $interfaces,
'enabled'    => $enabled,
'ident'      => $serialOrMac,   // same meaning, different source per platform

// GPON-only:
'_mask'      => $mask,
'_hit_count' => $hitCount,
'_serial'    => $serial,        // vendor-native name for `ident`

// EPON-only:
'_frame_slot' => $frameSlot,
'_port'       => $port,
'_mac'        => $mac,          // vendor-native name for `ident`
```

Before adding a new platform implementation of an existing module, check the field names used by other vendors' implementations of that same module and match them — don't invent a parallel naming scheme.

Interface objects (`interface` / `interfaces`) have their own already-established convention, independent of the rule above: `id`, `name`, `type`, `parent` are common; `_port`, `_slot`, `_onu`, `_snmp_id`, `_technology`, `_type`, etc. are platform-specific.

## Testing against real devices

Automated tests (`composer tests`) check configuration consistency (OID/model/module files), not runtime behavior — they will not tell you whether a new module actually works on hardware. After any change, run it against a real (or lab) device before considering the work done.

Use the bundled dev console:

```bash
php bin/console modules                                  # list modules known to the system
php bin/console call <device-ip> <module> [--arg=value]  # call a module against a device
php bin/console devices-by-module <module>                # which models support a module
```

Connection defaults (community, credentials) are read from `bin/connection.conf.yml`.

If your environment has `wildcoreDMS` installed, its `wca` CLI wraps the same core and is often faster to iterate with:

```bash
wca switcher-core:call <device-ip> <module> -v --telnet
wca test:snmpwalk <device-ip> if.Descr
```

Add `-v` for a verbose run (prints the request, console/SNMP transcript, and, on failure, the exception and stack trace).

Do not put real device IP addresses in committed documentation, tests, or example code — use placeholders such as `DEVICE_IP`.

## Style and conventions

* Match the structure and naming of existing modules written for the same or a similar vendor; don't introduce a new style for a new module.
* PHPDoc and commit messages: English. Inline code comments, when needed, should be short and explain non-obvious *why*, not restate the code.
* Don't add error handling for cases that can't occur, and don't add defensive fallbacks beyond what the SNMP/console layer already requires — trust the framework's guarantees.
* Keep `configs/modules.yml` in sync with any change to a module's arguments or behavior; the dev console and external clients rely on it matching reality.
* Treat `vendor/` as untouched unless the task explicitly requires a dependency change.
