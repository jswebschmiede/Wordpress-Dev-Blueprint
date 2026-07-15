<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Support;

use CompanyName\BoilerplatePlugin\Ramsey\Uuid\Uuid;

\defined( 'ABSPATH' ) || exit;

/**
 * Demonstrates prefixed Composer dependencies via Strauss.
 */
class StraussDemo {

	/**
	 * Generates a UUID v4 string via ramsey/uuid.
	 *
	 * @return string Random UUID v4.
	 */
	public function get_sample_uuid(): string {
		return Uuid::uuid4()->toString();
	}
}
