<?php

namespace Tests\App\Http\Controllers;
use App\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TestCase;
use \Mockery as m;

class BooksControllerTest extends TestCase {
	/** @test **/
	public function index_status_code_should_be_200() {
		$this->get('/books')->seeStatusCode(200);
	}

	/** @test **/
	public function index_should_return_a_collection_of_records() {
		$books = factory('App\Book', 2)->create();
		$this->get('/books');
		foreach ($books as $book) {
			$this->seeJson(['title' => $book->title]);
		}
	}

	/** @test **/
	public function show_should_return_a_valid_book() {
		$book = factory('App\Book')->create();
		$this
			->get("/books/{$book->id}")
			->seeStatusCode(200)
			->seeJson([
				'id' => $book->id,
				'title' => $book->title,
				'description' => $book->description,
				'author' => $book->author,
			]);

		$data = json_decode($this->response->getContent(), true);
		$this->assertArrayHasKey('created_at', $data);
		$this->assertArrayHasKey('updated_at', $data);
	}
	/** @test **/
	public function show_should_fail_when_the_book_id_does_not_exist() {
		$this->markTestIncomplete('Pending test');
		$this
			->get('/books/99999', ['Accept' => 'application/json'])
			->seeStatusCode(404)
			->seeJson([
				'error' => [
					'message' => 'Not Found',
					'status' => 404,
				],
			]);
	}

	/** @test **/
	public function show_route_should_not_match_an_invalid_route() {
		$this->get('/books/this-is-invalid');

		$this->assertNotRegExp(
			'/Book not found/',
			$this->response->getContent(),
			'BooksController@show route matching when it should not.'
		);
	}

	/** @test **/
	public function store_should_save_new_book_in_the_database() {
		$this->post('/books', [
			'title' => 'Manual Entered Book BY Test',
			'description' => '2An invisible man is trapped in the terror of his own creation',
			'author' => 'PHpUNit',
		]);

		$this
			->seeJson(['created' => true])
			->seeInDatabase('books', ['title' => 'Manual Entered Book BY Test']);
	}

	/** @test **/
	public function store_should_respond_with_a_201_and_location_header_when_successful() {

		$this->post('/books', [
			'title' => 'The Invisible Man',
			'description' => 'An invisible man is trapped in the terror of his own creation',
			'author' => 'H. G. Wells',
		]);
		$this
			->seeStatusCode(201)
			->seeHeaderWithRegExp('Location', '#/books/[\d]+$#');
	}

	/** @test **/
	public function update_should_only_change_fillable_fields() {
		$book = factory('App\Book')->create([
			'title' => 'War of the Worlds',
			'description' => 'A science fiction masterpiece about Martians invading London',
			'author' => 'H. G. Wells',
		]);

		$this->put("/books/{$book->id}", [
			'id' => 5,
			'title' => 'The War of the Worlds',
			'description' => 'The book is way better than the movie.',
			'author' => 'Wells, H. G.',
		]);

		$this
			->seeStatusCode(200)
			->seeJson([
				'id' => 1,
				'title' => 'The War of the Worlds',
				'description' => 'The book is way better than the movie.',
				'author' => 'Wells, H. G.',
			])
			->seeInDatabase('books', [
				'title' => 'The War of the Worlds',
			]);
	}

	/** @test **/
	public function update_should_fail_with_an_invalid_id() {
		$this
			->put('/books/999999999999999')
			->seeStatusCode(404)
			->seeJsonEquals([
				'error' => [
					'message' => 'Book Not Found',
				],
			]);

	}

	/** @test **/
	public function update_should_not_match_an_invalid_route() {
		$this->put('/books/this-is-invalid')
			->seeStatusCode(404);
	}

	/** @test **/
	public function destroy_should_remove_a_valid_book() {
		$book = factory('App\Book')->create();
		$this
			->delete("/books/{$book->id}")
			->seeStatusCode(204)
			->isEmpty();

		$this->notSeeInDatabase('books', ['id' => $book->id]);
	}

/** @test **/
	public function destroy_should_return_a_404_with_an_invalid_id() {
		$this
			->delete('/books/99999')
			->seeStatusCode(404)
			->seeJsonEquals([
				'error' => [
					'message' => 'Book Not Found',
				],
			]);

	}

/** @test **/
	public function destroy_should_not_match_an_invalid_route() {
		$this
			->delete('/books/this-is-invalid')
			->seeStatusCode(404);
	}

	/** @test **/
	public function it_responds_with_html_when_json_is_not_accepted() {
		// Make the mock a partial, you only want to mock the `isDebugMode` method
		$subject = m::mock(Handler::class)->makePartial();
		$subject->shouldNotReceive('isDebugMode');

// Mock the interaction with the Request
		$request = m::mock(Request::class);
		$request->shouldReceive('wantsJson')->andReturn(false);

// Mock the interaction with the exception
		$exception = m::mock(\Exception::class, ['Error!']);
		$exception->shouldNotReceive('getStatusCode');
		$exception->shouldNotReceive('getTrace');
		$exception->shouldNotReceive('getMessage');

// Call the method under test, this is not a mocked method.
		$result = $subject->render($request, $exception);

// Assert that `render` does not return a JsonResponse
		$this->assertNotInstanceOf(JsonResponse::class, $result);
	}

	/** @test */
	public function it_responds_with_json_for_json_consumers() {
		$subject = m::mock(Handler::class)->makePartial();
		$subject
			->shouldReceive('isDebugMode')
			->andReturn(false);

		$request = m::mock(Request::class);
		$request
			->shouldReceive('wantsJson')
			->andReturn(true);

		$exception = m::mock(\Exception::class, ['Doh!']);
		$exception
			->shouldReceive('getMessage')
			->andReturn('Doh!');

		/** @var JsonResponse $result */
		$result = $subject->render($request, $exception);
		$data = $result->getData();

		$this->assertInstanceOf(JsonResponse::class, $result);
		$this->assertObjectHasAttribute('error', $data);
		$this->assertAttributeEquals('Doh!', 'message', $data->error);
		$this->assertAttributeEquals(400, 'status', $data->error);
	}

	/** @test */
	public function it_provides_json_responses_for_http_exceptions() {
		$subject = m::mock(Handler::class)->makePartial();
		$subject
			->shouldReceive('isDebugMode')
			->andReturn(false);

		$request = m::mock(Request::class);
		$request->shouldReceive('wantsJson')->andReturn(true);

		$examples = [
			[
				'mock' => NotFoundHttpException::class,
				'status' => 404,
				'message' => 'Not Found',
			],
			[
				'mock' => AccessDeniedHttpException::class,
				'status' => 403,
				'message' => 'Forbidden',
			],
			[
				'mock' => ModelNotFoundException::class,
				'status' => 404,
				'message' => 'Not Found',
			],
		];

		foreach ($examples as $e) {
			$exception = m::mock($e['mock']);
			$exception->shouldReceive('getMessage')->andReturn(null);
			$exception->shouldReceive('getStatusCode')->andReturn($e['status']);

			/** @var JsonResponse $result */
			$result = $subject->render($request, $exception);
			$data = $result->getData();

			$this->assertEquals($e['status'], $result->getStatusCode());
			$this->assertEquals($e['message'], $data->error->message);
			$this->assertEquals($e['status'], $data->error->status);
		}
	}
}