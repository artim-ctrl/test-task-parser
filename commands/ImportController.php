<?php

namespace app\commands;

use app\commands\actions\import\HandleAction;
use yii\console\Controller;

class ImportController extends Controller
{
    public $defaultAction = 'handle';

    public function actions(): array
    {
        return [
            'handle' => HandleAction::class,
        ];
    }
}
