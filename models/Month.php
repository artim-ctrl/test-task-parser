<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "month".
 *
 * @property int $id
 * @property int $product_id
 * @property string $month
 * @property float|null $amount
 *
 * @property Product $product
 */
class Month extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'month';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'month'], 'required'],
            [['product_id'], 'integer'],
            [['amount'], 'number'],
            [['month'], 'string', 'max' => 255],
            [['month', 'product_id'], 'unique', 'targetAttribute' => ['month', 'product_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'month' => 'Month',
            'amount' => 'Amount',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
