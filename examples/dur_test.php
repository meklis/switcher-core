<?php

require __DIR__ . "/../vendor/autoload.php";

$duration = (new \Khill\Duration\Duration("5m20s"))->toSeconds();
