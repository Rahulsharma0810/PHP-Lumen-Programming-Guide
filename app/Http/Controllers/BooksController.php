<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class BooksController
 * @package App\Http\Controllers
 */
class BooksController {
	/**
	 * GET /books
	 * @return array
	 */
	public function index() {
		return ['data' => Book::all()->toArray()];
	}

	/**
	 * GET /books/{id}
	 * @param integer $id
	 * @return mixed
	 */
	public function show($id) {
		return ['data' => Book::findOrFail($id)->toArray()];
	}

	/**
	 * POST /books
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function store(Request $request) {
		$book = Book::create($request->all());

		return response()->json(['data' => $book->toArray()], 201, [
			'Location' => route('books.show', ['id' => $book->id]),
		]);
	}

	/**
	 * PUT /books/{id}
	 * @param Request $request
	 * @param $id
	 * @return mixed
	 */
	public function update(Request $request, $id) {
		try {
			$book = Book::findOrFail($id);
		} catch (ModelNotFoundException $e) {
			return response()->json([
				'error' => [
					'message' => 'Book not found',
				],
			], 404);
		}

		$book->fill($request->all());
		$book->save();

		return ['data' => $book->toArray()];
	}

	/**
	 * DELETE /books/{id}
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id) {
		try {
			$book = Book::findOrFail($id);
		} catch (ModelNotFoundException $e) {
			return response()->json([
				'error' => [
					'message' => 'Book not found',
				],
			], 404);
		}

		$book->delete();

		return response(null, 204);
	}
}