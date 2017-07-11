<?php

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder {
/**
 * Run the database seeds.
 *
 * @return void
 */
	public function run() {
		$faker = Faker::create('hi_IN');
		foreach (range(1, 10) as $index) {
			DB::table('books')->insert([
				'title' => $faker->company,
				'description' => $faker->sentence,
				'author' => $faker->name,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}
	}
}