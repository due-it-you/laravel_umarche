<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Stock;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
            AdminSeeder::class,
            OwnerSeeder::class,
            ShopSeeder::class,
            ImageSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,
            // StockSeeder::class,
            UserSeeder::class,
            
        ]);
        
        //外部キーで繋いでいるものに関しては、先に主キーのデータの方を作らないといけないので、
        //主キーの内容を上にして、その下に外部キーのを書く必要がある。
        Product::factory(100)->create();
        Stock::factory(100)->create();

    }
}
