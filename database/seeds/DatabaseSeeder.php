<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CategoryTableSeeder::class);

        Model::reguard();
    }
}

class CategoryTableSeeder extends Seeder {

    public function run() {
        //App\Category::truncate();

        $lists = array('Fashions', 'Electronics','Cameras','Laptop & Computers', 'Books', 'Toys & Kids', 'Movies, DVD ');
        foreach ($lists as $key => $name) {
            App\Category::create([
                'name' => $name
            ]);
        }
    }

}