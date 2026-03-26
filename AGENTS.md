# AGENTS.md

## Описание проекта

`switcher-core` это PHP-библиотека для работы с сетевым оборудованием через единый интерфейс. Основная идея репозитория: определять модель устройства по SNMP, подмешивать YAML-конфигурации OID/модулей и вызывать нужный модуль без отдельного враппера под каждого вендора.

Проект ориентирован на коммутаторы, OLT и роутеры. Поддерживаются входы через `SNMP v2c`, `telnet`, `ssh` и `RouterOS API`. Библиотека используется как embeddable core, а не как самостоятельный сервис.

## Подтвержденные параметры проекта

- Язык и формат: PHP library, автозагрузка `PSR-4` через namespace `SwitcherCore\\`.
- Минимальная версия PHP по `composer.json`: `>=7.2.0`.
- Ключевые runtime-зависимости: `meklis/snmp-wrapper`, `meklis/console-client`, `meklis/routeros-api`, `php-di/php-di`, `monolog/monolog`, `phpseclib/phpseclib`, `ext-yaml`, `ext-json`.
- Dev-зависимости: `phpunit/phpunit`, `symfony/console`.
- Базовая команда тестов: `composer tests`.
- В репозитории сейчас есть 40 файлов моделей в `configs/models`, 31 vendor/module namespace в `src/Modules`, 4 trap-конфига и 1 тестовый файл.

## Архитектура

- Точка сборки ядра: `src/Switcher/CoreConnector.php`.
- Основной runtime-объект: `src/Switcher/Core.php`.
- Описание устройства и connection-параметров: `src/Switcher/Device.php`.
- Встроенный путь к конфигам возвращает `SwitcherCore\\Modules\\Helper::getBuildInConfig()`.
- Конфигурация модели/модулей/OID читается из `configs/` через `src/Config/*Collector.php`.
- Реальные реализации модулей лежат в `src/Modules/<Vendor>/`.
- Общий реестр модулей и их аргументов: `configs/modules.yml`.
- Device detection завязан на `sys.Descr`, `sys.ObjId`, `sys.IfacesCount`.

## Карта каталогов

- `src/Switcher/`: ядро, коннекторы, console wrappers, cache objects.
- `src/Config/`: чтение YAML и сборщики моделей, OID, trap и модулей.
- `src/Modules/`: модульные реализации по вендорам и типам устройств.
- `configs/models/`: YAML-описания поддерживаемых моделей и mapping модулей.
- `configs/oids/`: OID-базы по вендорам и платформам.
- `configs/traps/`: trap-конфиги.
- `docs/`: список устройств, модулей и примеры ответов.
- `src/Dev/` и `bin/console`: локальная dev-консоль для вызова модулей и просмотра поддержки.
- `tests/`: сейчас покрытие минимальное и в основном проверяет консистентность OID-конфигов.

## Как работать с этим репозиторием

- Сначала проверять YAML-конфиги и mapping модели, потом уже PHP-классы модулей: значительная часть поведения задается не кодом, а `configs/models/*.yml` и `configs/modules.yml`.
- Для новых устройств обычно нужно:
  1. добавить или поправить OID-файлы в `configs/oids/`;
  2. описать модель в `configs/models/`;
  3. привязать существующие модули или добавить новые классы в `src/Modules/...`;
  4. проверить, что detection и аргументы модуля совпадают с конфигом.
- Для локальной ручной проверки использовать `bin/console`, особенно команды `modules`, `call`, `devices-by-module`.
- Не полагаться на широкое тестовое покрытие: автоматические тесты здесь не гарантируют корректность runtime-поведения на реальном оборудовании.

## Практические замечания для будущих проходов

- В коде много vendor-specific логики и исторического стиля PHP; перед рефакторингом нужно отдельно проверять обратную совместимость.
- `CoreConnector` умеет кешировать собранные collector-объекты через serialize/unserialize; изменения конфигов могут требовать пересборки этого кеша.
- `bin/console` читает параметры подключения из `bin/connection.conf.yml`.
- Для тестирования модулей на реальном железе можно использовать `wildcoreDMS`, если он установлен в окружении.
- `wildcoreDMS` может быть установлен локально или на удаленном сервере; во втором случае команды нужно выполнять через `ssh` на этот сервер.
- В документации и внутренних заметках не хранить реальные IP-адреса оборудования; использовать плейсхолдеры вроде `DEVICE_IP` и `REMOTE_HOST`.
- Базовые примеры вызова модуля через `wildcoreDMS`:
  - `wca switcher-core:call DEVICE_IP pon_onts_status`
  - `ssh REMOTE_HOST 'wca switcher-core:call DEVICE_IP pon_onts_status'`
  - `ssh REMOTE_HOST 'docker exec -i wca wca switcher-core:call DEVICE_IP pon_onts_status'`
- Для SNMP-проверки через `wca` использовать команду `test:snmpwalk`, а не `test:snmp`:
  - `wca test:snmpwalk DEVICE_IP if.Descr`
  - `wca test:snmpwalk DEVICE_IP ont.opStatus`
  - `ssh REMOTE_HOST 'docker exec -i wca wca test:snmpwalk DEVICE_IP ont.opStatus'`
- Для детализированного вывода при диагностике добавлять `-v --telnet` или короткую форму `-v -t`.
- При запуске `wca` из automation/PTY-less окружения учитывать, что команда может требовать TTY; типичный симптом: `the input device is not a TTY`.
- Проверенный локальный пример:
  - `wca switcher-core:call DEVICE_IP pon_onts_status -v --telnet`
- Проверенный успешный пример:
  - `wca switcher-core:call DEVICE_IP system -v --telnet`
- Проверенный успешный пример после фикса декодирования ONU index:
  - `wca switcher-core:call DEVICE_IP pon_onts_status -v --telnet`
- Проверенные SNMP-примеры для этого устройства:
  - `wca test:snmpwalk DEVICE_IP if.Descr`
  - `wca test:snmpwalk DEVICE_IP ont.opStatus`
  - `wca test:snmpwalk DEVICE_IP ont.serialNum`
- Что дает подробный режим на практике:
  - печатает стартовый блок с IP, именем модуля и аргументами;
  - при ошибке показывает текст исключения и stack trace;
  - печатает секцию `Telnet output`, даже если telnet-буфер пустой.
- На успешном вызове `system` для одного из проверенных C-Data FD17xx устройств команда вернула JSON c общей информацией об устройстве, включая `meta.key = c_data_fd1700s_fw3`, версию ПО `V3.3.48`, версию железа `V1.0` и список доступных модулей.
- Исторически на вызове `pon_onts_status` для одного из проверенных FD17xx устройств была ошибка `Unable to decode ONU index`; после фикса FD17xx модуль отрабатывает успешно, а этот кейс полезен как пример диагностики SNMP index-related проблем.
- В рабочем дереве могут встречаться служебные и временные файлы вроде `logs`, `mock`, `.phpunit.result.cache`, `.save`; не удалять их автоматически без прямого запроса.
- Не трогать `vendor/`, если задача не требует обновления зависимостей.
- Если меняется сигнатура или поведение модуля, нужно сверять код с `configs/modules.yml`, иначе dev-console и внешние клиенты будут расходиться с реальностью.
