<?php

require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");

$modules = $reader->readModulesConfig();
$moduleData = [];
foreach ($modules as $module) {
    $moduleData[$module->getName()] = [
        'descr' => $module->getDescr(), 
        'arguments' => $module->getArguments(),
    ];
}


ksort($moduleData);
$MARKDOWN = "### Список поддерживаемых модулей";
foreach ($moduleData as $name=>$data) {
    $arguments = "";
    if($data['arguments']) {
        $arguments .= "    \n";
        $arguments .= "**Аргументы:**    \n";
        foreach ($data['arguments'] as $argument) {
            if ($argument['required']) {
                $req = ", обязательный";
            } else {
                $req = "";
            }
            $arguments .= "- **{$argument['name']}**, проверка выражением: *{$argument['pattern']}*$req    \n";
        }
    }
    $responseExample = '';
    if(file_exists(__DIR__ . '/modules_example_output/' . $name . '.json')) {
        $respRaw = json_decode(file_get_contents(__DIR__ . '/modules_example_output/' . $name . '.json'), true);
        $parameters = "";
        foreach ($respRaw['arguments'] as $argumentName=>$argumentValue) {
            $parameters .= "**{$argumentName}**=$argumentValue, ";
        }
        $parameters = trim($parameters, ', ');
        if(!$parameters) $parameters = "без параметров";
        $resp = json_encode($respRaw['data'], JSON_PRETTY_PRINT);
        $responseExample = "<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: $parameters         

Ответ в JSON:          

```json             
{$resp}
```             
         
        
</p>
</details>
        ";
    }
    $MARKDOWN .= <<<MARKDOWN
    
    
### [{$name}](#{$name}) - {$data['descr']} 
$arguments      
$responseExample
MARKDOWN;
}
file_put_contents(__DIR__ . '/MODULES.md', $MARKDOWN);