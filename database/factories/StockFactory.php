<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //product_idは外部キーで、Productと紐づいて
            //Productのファクトリーの情報と一致する必要があるため、データを持ってきて一致させる。
            'product_id' => Product::factory(),
            'type' => $this->faker->numberBetween(1,2),
            'quantity' => $this->faker->randomNumber(),
        ];
    }
}
