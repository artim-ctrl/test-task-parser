<?php

namespace app\commands\actions\import;

use app\models\Budget;
use app\models\Category;
use app\models\Month;
use app\models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Yii;
use yii\base\Action;
use yii\helpers\Console;

class HandleAction extends Action
{
    private const MONTHS = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function run(string $filePath): void
    {
        $reader = IOFactory::createReader('Csv');
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load(Yii::getAlias('@app/' . $filePath));

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();

        [$budgetName, $rows] = $this->getNameAndFilteredRows($rows);

        $products = [];

        $currentCategory = null;
        foreach ($rows as $row) {
            // TODO: Исключения
            if (in_array($row[0], ['Internet Total', 'PVR on Internet'])) {
                continue;
            }

            // Если находим Total - прокручиваем дальше при этом обнуляя категорию
            if ('Total' == $row[0]) {
                $currentCategory = null;

                continue;
            }

            // Если на данный момент категории нет - заполняем (либо только начали, либо один шаг назад был Total)
            if (null === $currentCategory) {
                $currentCategory = $row[0];

                continue;
            }

            if (empty($row[0])) {
                continue;
            }

            $products[$currentCategory][] = [
                'name' => $row[0],
                'months' => $this->prepareMonths($row),
            ];
        }

        $this->import($budgetName, $products);

        dd(1);
    }

    /**
     * @throws \Exception
     */
    private function import(string $budgetName, array $products): void
    {
        $budget = $this->createOrGetBudget($budgetName);

        $categoriesIds = [];
        foreach ($products as $categoryName => $productsOfCategory) {
            $category = $this->createOrGetCategory($categoryName, $budget->id);
            $categoriesIds[] = $category->id;

            $productsIds = [];
            foreach ($productsOfCategory as $productData) {
                $product = $this->createOrGetProduct($productData['name'], $category->id);
                $productsIds[] = $product->id;

                foreach ($productData['months'] as $monthName => $amount) {
                    $this->createOrGetMonth($monthName, $amount, $product->id);
                }
            }

            Product::deleteAll(['and', ['category_id' => $category->id], ['not in', 'id', $productsIds]]);
        }

        Category::deleteAll(['and', ['budget_id' => $budget->id], ['not in', 'id', $categoriesIds]]);
    }

    /**
     * @throws \Exception
     */
    private function createOrGetBudget(string $name): Budget
    {
        /** @var Budget|null $budget */
        $budget = Budget::find()->where(['name' => $name])->one();
        if (null !== $budget) {
            return $budget;
        }

        $budget = new Budget();
        $budget->name = $name;
        if (! $budget->save()) {
            Console::stderr('Cannot save budget: ' . json_encode($budget->errors));

            exit;
        }

        return $budget;
    }

    private function createOrGetCategory(string $name, int $budgetId): Category
    {
        /** @var Category|null $category */
        $category = Category::find()->where(['name' => $name, 'budget_id' => $budgetId])->one();
        if (null !== $category) {
            return $category;
        }

        $category = new Category();
        $category->name = $name;
        $category->budget_id = $budgetId;
        if (! $category->save()) {
            Console::stderr('Cannot save category: ' . json_encode($category->errors));

            exit;
        }

        return $category;
    }

    private function createOrGetProduct(string $name, int $categoryId): Product
    {
        /** @var Product|null $product */
        $product = Product::find()->where(['name' => $name, 'category_id' => $categoryId])->one();
        if (null !== $product) {
            return $product;
        }

        $product = new Product();
        $product->name = $name;
        $product->category_id = $categoryId;
        if (! $product->save()) {
            Console::stderr('Cannot save product: ' . json_encode($product->errors));

            exit;
        }

        return $product;
    }

    private function createOrGetMonth(string $name, float $amount, int $productId): Month
    {
        /** @var Month|null $month */
        $month = Month::find()->where(['month' => $name, 'product_id' => $productId])->one();
        if (null !== $month) {
            if ($month->amount !== $amount) {
                $month->amount = $amount;
                if (false === $month->update()) {
                    Console::stderr('Cannot update month: ' . json_encode($month->errors));

                    exit;
                }
            }

            return $month;
        }

        $month = new Month();
        $month->month = $name;
        $month->amount = $amount;
        $month->product_id = $productId;
        if (! $month->save()) {
            Console::stderr('Cannot save month: ' . json_encode($month->errors));

            exit;
        }

        return $month;
    }

    private function getNameAndFilteredRows(array $rows): array
    {
        $budgetName = $rows[0][0];

        $filteredRows = [];
        unset($rows[0], $rows[1]);
        foreach ($rows as $row) {
            if (($row[0] ?? '') == 'CO-OP') {
                break;
            }

            $filteredRows[] = $row;
        }

        return [$budgetName, $filteredRows];
    }

    private function prepareMonths(array $row): array
    {
        $months = $this->arrOnly($row, range(1, 12));
        $months = array_map(function (?string $sum) {
            if (null === $sum) {
                return 0;
            }

            return (float) str_replace(',', '', ltrim($sum, '$'));
        }, $months);

        return array_combine(self::MONTHS, array_values($months));
    }

    private function arrOnly(array $array, array $columns): array
    {
        return array_intersect_key($array, array_flip($columns));
    }
}

function dd(...$data)
{
    var_dump(...[...$data, get_formatted_peak_memory_usage()]);die;
}

function get_formatted_peak_memory_usage(): string
{
    $bytes = memory_get_peak_usage();
    $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    if (0 === $bytes) {
        return '0 ' . $unit[0];
    }

    return round($bytes / pow(1000, ($i = floor(log($bytes, 1000)))), 2) . ' ' . ($unit[$i] ?? 'B');
}
