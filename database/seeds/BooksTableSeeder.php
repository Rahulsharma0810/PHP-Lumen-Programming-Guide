<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder {
/**
 * Run the database seeds.
 *
 * @return void
 */
	public function run() {
		DB::table('books')->insert([
			'title' => 'War of the Worlds',
			'description' => 'A science fiction masterpiece about Martians invading don',
			'author' => 'H. G. Wells',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);

		DB::table('books')->insert([
			'title' => 'A Wrinkle in Time',
			'description' => 'A young girl goes on a mission to save her father who has gone missing after working on a mysterious project called a tesseract.',
			'author' => 'Madeleine L\'Engle',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
	}
}