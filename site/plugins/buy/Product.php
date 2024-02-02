<?php

namespace Buy;

enum Product: string
{
	case Basic 	    = 'basic';
	case Enterprise = 'enterprise';

	/**
	 * Generates a checkout link for the product
	 */
	public function checkout(
		string $type = 'buy',
		array $payload = []
	): string {
		$product = match ($type) {
			'buy'     => $this->productId(),
			'upgrade' => $this->upgradeId(),
			'convert' => $this->upgradeId('convert'),
			'free'    => $this->upgradeId('free'),
		};

		return Paddle::checkout($product, $payload);
	}

	/**
	 * Returns the human-readable label for the buy page
	 */
	public function label(): string
	{
		return match ($this) {
			static::Basic      => 'Basic',
			static::Enterprise => 'Enterprise',
			default            => null
		};
	}

	/**
	 * Returns the price object for the product
	 */
	public function price(string|null $currency = null, float|null $rate = null): Price
	{
		return new Price($this, $currency, $rate);
	}

	/**
	 * Returns the Paddle product ID
	 */
	public function productId(): int
	{
		return option('buy.' . $this->value . '.product');
	}

	/**
	 * Returns the unconverted EUR price for a regular purchase
	 */
	public function rawPrice(): float
	{
		return option('buy.' . $this->value . '.regular');
	}

	/**
	 * Returns a label for the revenue limit
	 */
	public function revenueLimit(): string|null
	{
		return match ($this) {
			static::Basic      => 'Revenue limit: €1M / year.',
			static::Enterprise => 'This license does not have a revenue limit.',
			default            => null
		};
	}

	/**
	 * Returns the Paddle upgrade product ID
	 */
	public function upgradeId(string|null $type = null): int
	{
		return match ($type) {
			'free'    => option('buy.' . $this->value . '.free'),
			'convert' => option('buy.' . $this->value . '.convert'),
			default   => option('buy.' . $this->value . '.upgrade')
		};
	}

	public function value(): string
	{
		return $this->value;
	}

}
