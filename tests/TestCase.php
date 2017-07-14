<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase {
	/**
	 * Creates the application.
	 *
	 * @return \Laravel\Lumen\Application
	 */
	public function createApplication() {
		return require __DIR__ . '/../bootstrap/app.php';
	}

	/**
	 * See if the response has a header. 17 *
	 * @param $header
	 * @return $this
	 */
	public function seeHasHeader($header) {
		$this->assertTrue(
			$this->response->headers->has($header),
			"Response Should Have the header '{$header}' but does not."
		);
		return $this;
	}

	/**
	 * Asserts that the response header matches a given regular expression 33 *
	 * @param $header
	 * @param $regexp
	 * @return $this
	 */
	public function seeHeaderWithRegExp($header, $regexp) {
		$this
			->seeHasHeader($header)
			->assertRegExp(
				$regexp,
				$this->response->headers->get($header)
			);

		return $this;
	}

}